<?php
/**
 * ENNU Unified Scoring Service
 * Single source of truth for all scoring calculations
 * 
 * @package ENNU_Life_Assessments
 * @version 3.37.16
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Unified scoring service that consolidates all scoring logic
 * Replaces multiple conflicting calculator classes
 */
class ENNU_Unified_Scoring_Service {

	private static $instance = null;
	private $logger;

	/**
	 * Get singleton instance
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		// Use existing logger if available, otherwise create simple logging
		if ( class_exists( 'ENNU_Logger' ) ) {
			$this->logger = new ENNU_Logger();
		} else {
			$this->logger = null;
		}
	}

	/**
	 * Calculate assessment scores from user data
	 * 
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @param array $form_data Form submission data
	 * @return array Calculated scores
	 */
	public function calculate_assessment_scores( $user_id, $assessment_type, $form_data = array() ) {
		// Validate inputs
		if ( empty( $user_id ) || empty( $assessment_type ) ) {
			if ( $this->logger ) {
				$this->logger->log_error( 'Invalid parameters for score calculation', array(
					'user_id' => $user_id,
					'assessment_type' => $assessment_type
				) );
			}
			return array();
		}

		// Get assessment definitions
		$definitions = $this->get_assessment_definitions( $assessment_type );
		if ( empty( $definitions ) ) {
			if ( $this->logger ) {
				$this->logger->log_error( 'No definitions found for assessment type', $assessment_type );
			}
			return array();
		}

		// Calculate base scores from form data
		$base_scores = $this->calculate_base_scores( $form_data, $definitions );

		// Apply qualitative adjustments (symptom penalties)
		$qualitative_scores = $this->apply_qualitative_adjustments( $user_id, $base_scores );

		// Apply objective adjustments (biomarker data)
		$objective_scores = $this->apply_objective_adjustments( $user_id, $qualitative_scores );

		// Apply intentionality adjustments (goal alignment)
		$final_scores = $this->apply_intentionality_adjustments( $user_id, $objective_scores );

		// Calculate overall score
		$overall_score = $this->calculate_overall_score( $final_scores );

		return array(
			'overall_score' => $overall_score,
			'pillar_scores' => $final_scores,
			'base_scores' => $base_scores,
			'qualitative_adjustments' => $qualitative_scores,
			'objective_adjustments' => $objective_scores,
			'calculation_timestamp' => current_time( 'timestamp' ),
			'assessment_type' => $assessment_type
		);
	}

	/**
	 * Calculate base scores from form data
	 * 
	 * @param array $form_data Form submission data
	 * @param array $definitions Assessment definitions
	 * @return array Base scores by pillar
	 */
	private function calculate_base_scores( $form_data, $definitions ) {
		$base_scores = array(
			'mind' => 0,
			'body' => 0,
			'lifestyle' => 0,
			'aesthetics' => 0
		);

		$total_questions = 0;
		$pillar_totals = array(
			'mind' => 0,
			'body' => 0,
			'lifestyle' => 0,
			'aesthetics' => 0
		);

		// Process each question
		foreach ( $definitions['questions'] as $question_key => $question ) {
			if ( ! isset( $form_data[ $question_key ] ) ) {
				continue;
			}

			$answer = $form_data[ $question_key ];
			$pillar = $question['pillar'] ?? 'lifestyle';
			$score = $this->calculate_question_score( $answer, $question );

			$pillar_totals[ $pillar ] += $score;
			$total_questions++;
		}

		// Calculate average scores
		foreach ( $base_scores as $pillar => $score ) {
			if ( $pillar_totals[ $pillar ] > 0 ) {
				$base_scores[ $pillar ] = round( $pillar_totals[ $pillar ] / count( $definitions['questions'] ), 1 );
			}
		}

		return $base_scores;
	}

	/**
	 * Calculate score for a single question
	 * 
	 * @param mixed $answer User's answer
	 * @param array $question Question definition
	 * @return float Question score
	 */
	private function calculate_question_score( $answer, $question ) {
		$question_type = $question['type'] ?? 'radio';
		$max_score = $question['max_score'] ?? 10;

		switch ( $question_type ) {
			case 'radio':
			case 'select':
				return $this->calculate_radio_score( $answer, $question, $max_score );

			case 'range':
			case 'slider':
				return $this->calculate_range_score( $answer, $question, $max_score );

			case 'checkbox':
			case 'multiselect':
				return $this->calculate_checkbox_score( $answer, $question, $max_score );

			default:
				return $max_score; // Default to max score for unknown types
		}
	}

	/**
	 * Calculate score for radio/select questions
	 * 
	 * @param string $answer User's answer
	 * @param array $question Question definition
	 * @param float $max_score Maximum possible score
	 * @return float Calculated score
	 */
	private function calculate_radio_score( $answer, $question, $max_score ) {
		$options = $question['options'] ?? array();
		
		foreach ( $options as $option ) {
			if ( $option['value'] === $answer ) {
				return floatval( $option['score'] ?? $max_score );
			}
		}

		return 0; // No matching option found
	}

	/**
	 * Calculate score for range/slider questions
	 * 
	 * @param float $answer User's answer
	 * @param array $question Question definition
	 * @param float $max_score Maximum possible score
	 * @return float Calculated score
	 */
	private function calculate_range_score( $answer, $question, $max_score ) {
		$min_value = $question['min_value'] ?? 0;
		$max_value = $question['max_value'] ?? 10;
		$optimal_min = $question['optimal_min'] ?? $min_value;
		$optimal_max = $question['optimal_max'] ?? $max_value;

		$answer = floatval( $answer );

		// If answer is in optimal range, give max score
		if ( $answer >= $optimal_min && $answer <= $optimal_max ) {
			return $max_score;
		}

		// Calculate score based on distance from optimal range
		$distance = 0;
		if ( $answer < $optimal_min ) {
			$distance = $optimal_min - $answer;
		} else {
			$distance = $answer - $optimal_max;
		}

		$max_distance = max( $optimal_min - $min_value, $max_value - $optimal_max );
		$score = $max_score * ( 1 - ( $distance / $max_distance ) );

		return max( 0, min( $max_score, $score ) );
	}

	/**
	 * Calculate score for checkbox/multiselect questions
	 * 
	 * @param array $answers User's answers
	 * @param array $question Question definition
	 * @param float $max_score Maximum possible score
	 * @return float Calculated score
	 */
	private function calculate_checkbox_score( $answers, $question, $max_score ) {
		if ( ! is_array( $answers ) ) {
			$answers = array( $answers );
		}

		$total_score = 0;
		$options = $question['options'] ?? array();

		foreach ( $answers as $answer ) {
			foreach ( $options as $option ) {
				if ( $option['value'] === $answer ) {
					$total_score += floatval( $option['score'] ?? 0 );
				}
			}
		}

		return min( $max_score, $total_score );
	}

	/**
	 * Apply qualitative adjustments based on symptoms
	 * 
	 * @param int $user_id User ID
	 * @param array $base_scores Base scores
	 * @return array Adjusted scores
	 */
	private function apply_qualitative_adjustments( $user_id, $base_scores ) {
		$adjusted_scores = $base_scores;

		// Get user symptoms
		if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
			$symptoms_data = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $user_id );
			$user_symptoms = $symptoms_data['symptoms'] ?? array();

			// Apply symptom penalties
			foreach ( $user_symptoms as $assessment_type => $symptoms ) {
				if ( is_array( $symptoms ) ) {
					foreach ( $symptoms as $symptom ) {
						$penalty = $this->get_symptom_penalty( $symptom );
						$pillar = $this->get_symptom_pillar( $symptom );
						
						if ( isset( $adjusted_scores[ $pillar ] ) ) {
							$adjusted_scores[ $pillar ] = max( 0, $adjusted_scores[ $pillar ] - $penalty );
						}
					}
				}
			}
		}

		return $adjusted_scores;
	}

	/**
	 * Apply objective adjustments based on biomarker data
	 * 
	 * @param int $user_id User ID
	 * @param array $qualitative_scores Qualitative scores
	 * @return array Adjusted scores
	 */
	private function apply_objective_adjustments( $user_id, $qualitative_scores ) {
		$adjusted_scores = $qualitative_scores;

		// Get user biomarkers
		if ( class_exists( 'ENNU_Biomarker_Manager' ) ) {
			$biomarkers = ENNU_Biomarker_Manager::get_user_biomarkers( $user_id );

			foreach ( $biomarkers as $biomarker_key => $biomarker_data ) {
				if ( isset( $biomarker_data['value'] ) && isset( $biomarker_data['status'] ) ) {
					$adjustment = $this->get_biomarker_adjustment( $biomarker_data );
					$pillar = $this->get_biomarker_pillar( $biomarker_key );
					
					if ( isset( $adjusted_scores[ $pillar ] ) ) {
						$adjusted_scores[ $pillar ] = max( 0, min( 10, $adjusted_scores[ $pillar ] + $adjustment ) );
					}
				}
			}
		}

		return $adjusted_scores;
	}

	/**
	 * Apply intentionality adjustments based on goal alignment
	 * 
	 * @param int $user_id User ID
	 * @param array $objective_scores Objective scores
	 * @return array Adjusted scores
	 */
	private function apply_intentionality_adjustments( $user_id, $objective_scores ) {
		$adjusted_scores = $objective_scores;

		// Get user health goals
		$health_goals = get_user_meta( $user_id, 'ennu_health_goals', true );
		if ( is_array( $health_goals ) ) {
			foreach ( $health_goals as $goal ) {
				$boost = $this->get_goal_alignment_boost( $goal );
				$pillar = $this->get_goal_pillar( $goal );
				
				if ( isset( $adjusted_scores[ $pillar ] ) ) {
					$adjusted_scores[ $pillar ] = max( 0, min( 10, $adjusted_scores[ $pillar ] + $boost ) );
				}
			}
		}

		return $adjusted_scores;
	}

	/**
	 * Calculate overall score from pillar scores
	 * 
	 * @param array $pillar_scores Pillar scores
	 * @return float Overall score
	 */
	private function calculate_overall_score( $pillar_scores ) {
		$total_score = 0;
		$pillar_count = 0;

		foreach ( $pillar_scores as $pillar => $score ) {
			if ( $score > 0 ) {
				$total_score += $score;
				$pillar_count++;
			}
		}

		if ( $pillar_count === 0 ) {
			return 0;
		}

		return round( $total_score / $pillar_count, 1 );
	}

	/**
	 * Get assessment definitions
	 * 
	 * @param string $assessment_type Assessment type
	 * @return array Assessment definitions
	 */
	private function get_assessment_definitions( $assessment_type ) {
		// Try to get from scoring system first
		if ( class_exists( 'ENNU_Scoring_System' ) ) {
			$all_definitions = ENNU_Scoring_System::get_all_definitions();
			if ( isset( $all_definitions[ $assessment_type ] ) ) {
				return $all_definitions[ $assessment_type ];
			}
		}

		// Fallback to config files
		$config_file = ENNU_LIFE_PLUGIN_PATH . "includes/config/assessments/{$assessment_type}.php";
		if ( file_exists( $config_file ) ) {
			return require $config_file;
		}

		return array();
	}

	/**
	 * Get symptom penalty
	 * 
	 * @param string $symptom Symptom name
	 * @return float Penalty amount
	 */
	private function get_symptom_penalty( $symptom ) {
		$penalties = array(
			'fatigue' => 0.5,
			'anxiety' => 0.3,
			'depression' => 0.4,
			'insomnia' => 0.3,
			'headaches' => 0.2,
			'joint_pain' => 0.3,
			'back_pain' => 0.3,
			'digestive_issues' => 0.2
		);

		return $penalties[ $symptom ] ?? 0.1;
	}

	/**
	 * Get symptom pillar mapping
	 * 
	 * @param string $symptom Symptom name
	 * @return string Pillar name
	 */
	private function get_symptom_pillar( $symptom ) {
		$pillar_mapping = array(
			'fatigue' => 'body',
			'anxiety' => 'mind',
			'depression' => 'mind',
			'insomnia' => 'lifestyle',
			'headaches' => 'mind',
			'joint_pain' => 'body',
			'back_pain' => 'body',
			'digestive_issues' => 'body'
		);

		return $pillar_mapping[ $symptom ] ?? 'lifestyle';
	}

	/**
	 * Get biomarker adjustment
	 * 
	 * @param array $biomarker_data Biomarker data
	 * @return float Adjustment amount
	 */
	private function get_biomarker_adjustment( $biomarker_data ) {
		$status = $biomarker_data['status'] ?? 'normal';
		$value = floatval( $biomarker_data['value'] ?? 0 );

		switch ( $status ) {
			case 'optimal':
				return 0.2;
			case 'suboptimal':
				return -0.1;
			case 'critical':
				return -0.3;
			default:
				return 0;
		}
	}

	/**
	 * Get biomarker pillar mapping
	 * 
	 * @param string $biomarker_key Biomarker key
	 * @return string Pillar name
	 */
	private function get_biomarker_pillar( $biomarker_key ) {
		$pillar_mapping = array(
			'testosterone' => 'body',
			'cortisol' => 'mind',
			'vitamin_d' => 'body',
			'glucose' => 'body',
			'cholesterol' => 'body',
			'blood_pressure' => 'body'
		);

		return $pillar_mapping[ $biomarker_key ] ?? 'body';
	}

	/**
	 * Get goal alignment boost
	 * 
	 * @param string $goal Goal name
	 * @return float Boost amount
	 */
	private function get_goal_alignment_boost( $goal ) {
		$boosts = array(
			'weight_loss' => 0.1,
			'muscle_gain' => 0.1,
			'energy_improvement' => 0.1,
			'stress_reduction' => 0.1,
			'sleep_improvement' => 0.1
		);

		return $boosts[ $goal ] ?? 0.05;
	}

	/**
	 * Get goal pillar mapping
	 * 
	 * @param string $goal Goal name
	 * @return string Pillar name
	 */
	private function get_goal_pillar( $goal ) {
		$pillar_mapping = array(
			'weight_loss' => 'body',
			'muscle_gain' => 'body',
			'energy_improvement' => 'body',
			'stress_reduction' => 'mind',
			'sleep_improvement' => 'lifestyle'
		);

		return $pillar_mapping[ $goal ] ?? 'lifestyle';
	}

	/**
	 * Save scores to user meta
	 * 
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @param array $scores Calculated scores
	 */
	public function save_scores( $user_id, $assessment_type, $scores ) {
		$meta_key = "ennu_{$assessment_type}_scores";
		update_user_meta( $user_id, $meta_key, $scores );

		// Update overall health score
		$this->update_overall_health_score( $user_id );

		if ( $this->logger ) {
			$this->logger->log( 'Scores saved successfully', array(
				'user_id' => $user_id,
				'assessment_type' => $assessment_type,
				'overall_score' => $scores['overall_score']
			) );
		}
	}

	/**
	 * Update overall health score
	 * 
	 * @param int $user_id User ID
	 */
	private function update_overall_health_score( $user_id ) {
		$all_scores = array();
		
		// Get all assessment scores
		$assessment_types = array( 'weight_loss', 'sleep', 'hormone', 'nutrition', 'fitness', 'stress', 'energy', 'recovery', 'longevity', 'performance', 'wellness' );
		
		foreach ( $assessment_types as $type ) {
			$scores = get_user_meta( $user_id, "ennu_{$type}_scores", true );
			if ( is_array( $scores ) && isset( $scores['overall_score'] ) ) {
				$all_scores[] = $scores['overall_score'];
			}
		}

		if ( ! empty( $all_scores ) ) {
			$overall_health_score = round( array_sum( $all_scores ) / count( $all_scores ), 1 );
			update_user_meta( $user_id, 'ennu_overall_health_score', $overall_health_score );
		}
	}

	/**
	 * Get user scores
	 * 
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @return array User scores
	 */
	public function get_user_scores( $user_id, $assessment_type ) {
		$meta_key = "ennu_{$assessment_type}_scores";
		$scores = get_user_meta( $user_id, $meta_key, true );

		if ( ! is_array( $scores ) ) {
			return array();
		}

		return $scores;
	}

	/**
	 * Get all user scores
	 * 
	 * @param int $user_id User ID
	 * @return array All user scores
	 */
	public function get_all_user_scores( $user_id ) {
		$all_scores = array();
		$assessment_types = array( 'weight_loss', 'sleep', 'hormone', 'nutrition', 'fitness', 'stress', 'energy', 'recovery', 'longevity', 'performance', 'wellness' );
		
		foreach ( $assessment_types as $type ) {
			$scores = $this->get_user_scores( $user_id, $type );
			if ( ! empty( $scores ) ) {
				$all_scores[ $type ] = $scores;
			}
		}

		return $all_scores;
	}
} 