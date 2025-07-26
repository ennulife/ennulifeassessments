<?php
/**
 * ENNU Life Scoring System
 * Handles all scoring calculations and data management
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Scoring_System {

	private static $all_definitions = array();
	private static $pillar_map      = array();

	public static function get_all_definitions() {
		// Force refresh for testing - remove this line after verification
		delete_transient( 'ennu_assessment_definitions_v1' );
		
		if ( ! empty( self::$all_definitions ) ) {
			return self::$all_definitions;
		}

		$cached_definitions = get_transient( 'ennu_assessment_definitions_v1' );
		if ( false !== $cached_definitions ) {
			self::$all_definitions = $cached_definitions;
			return self::$all_definitions;
		}

		$definitions = array();
		$config_dir  = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessments/';

		if ( is_dir( $config_dir ) ) {
			$files = glob( $config_dir . '*.php' );
			foreach ( $files as $file ) {
				$assessment_type = basename( $file, '.php' );
				$definition      = require $file;
				if ( is_array( $definition ) ) {
					$definitions[ $assessment_type ] = $definition;
				}
			}
		}

		self::$all_definitions = $definitions;
		set_transient( 'ennu_assessment_definitions_v1', $definitions, 12 * HOUR_IN_SECONDS );

		return self::$all_definitions;
	}

	public static function get_health_pillar_map() {
		// Force refresh for testing - remove this line after verification
		delete_transient( 'ennu_pillar_map_v1' );
		
		if ( ! empty( self::$pillar_map ) ) {
			return self::$pillar_map;
		}

		$cached_pillar_map = get_transient( 'ennu_pillar_map_v1' );
		if ( false !== $cached_pillar_map ) {
			self::$pillar_map = $cached_pillar_map;
			return self::$pillar_map;
		}

		$pillar_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/scoring/pillar-map.php';
		if ( file_exists( $pillar_map_file ) ) {
			$pillar_map = require $pillar_map_file;
			if ( is_array( $pillar_map ) ) {
				self::$pillar_map = $pillar_map;
				set_transient( 'ennu_pillar_map_v1', $pillar_map, 12 * HOUR_IN_SECONDS );
				return self::$pillar_map;
			}
		}

		$default_pillar_map = array(
			'Mind'       => array(
				'categories' => array( 'cognitive_function', 'mental_clarity', 'mood_stability' ),
				'weight'     => 0.25,
			),
			'Body'       => array(
				'categories' => array( 'cardiovascular_health', 'metabolic_function', 'hormonal_balance' ),
				'weight'     => 0.35,
			),
			'Lifestyle'  => array(
				'categories' => array( 'exercise_frequency', 'nutrition_quality', 'sleep_patterns' ),
				'weight'     => 0.25,
			),
			'Aesthetics' => array(
				'categories' => array( 'skin_health', 'body_composition', 'physical_appearance' ),
				'weight'     => 0.15,
			),
		);

		self::$pillar_map = $default_pillar_map;
		set_transient( 'ennu_pillar_map_v1', $default_pillar_map, 12 * HOUR_IN_SECONDS );

		return self::$pillar_map;
	}

	public static function get_health_goal_definitions() {
		$cached_goal_definitions = get_transient( 'ennu_health_goal_definitions_v1' );
		if ( false !== $cached_goal_definitions ) {
			return $cached_goal_definitions;
		}

		$goal_definitions_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/scoring/health-goals.php';
		if ( file_exists( $goal_definitions_file ) ) {
			$goal_definitions = require $goal_definitions_file;
			if ( is_array( $goal_definitions ) ) {
				set_transient( 'ennu_health_goal_definitions_v1', $goal_definitions, 12 * HOUR_IN_SECONDS );
				return $goal_definitions;
			}
		}

		$default_goal_definitions = array(
			'goal_definitions' => array(
				'weight_loss'  => array(
					'name'          => 'Weight Loss',
					'pillar_boosts' => array(
						'Body'      => 0.15,
						'Lifestyle' => 0.10,
					),
				),
				'energy_boost' => array(
					'name'          => 'Energy Boost',
					'pillar_boosts' => array(
						'Body' => 0.10,
						'Mind' => 0.10,
					),
				),
			),
		);

		set_transient( 'ennu_health_goal_definitions_v1', $default_goal_definitions, 12 * HOUR_IN_SECONDS );
		return $default_goal_definitions;
	}

	public static function calculate_and_save_all_user_scores( $user_id, $force_recalc = false ) {
		$performance_monitor = ENNU_Performance_Monitor::get_instance();
		$performance_monitor->start_timer( 'scoring_calculation' );

		$all_definitions = self::get_all_definitions();
		$pillar_map      = self::get_health_pillar_map();
		$health_goals    = get_user_meta( $user_id, 'ennu_global_health_goals', true );

		// Ensure health_goals is an array
		if ( ! is_array( $health_goals ) ) {
			$health_goals = array();
		}

		$goal_definitions = self::get_health_goal_definitions();

		// 1. Get all category scores from all completed assessments - OPTIMIZED
		$all_category_scores = array();

		$meta_keys = array();
		foreach ( array_keys( $all_definitions ) as $assessment_type ) {
			if ( 'health_optimization_assessment' === $assessment_type ) {
				continue;
			}
			$meta_keys[] = 'ennu_' . $assessment_type . '_category_scores';
		}

		$user_meta_batch = array();
		foreach ( $meta_keys as $key ) {
			$user_meta_batch[ $key ] = get_user_meta( $user_id, $key, true );
		}

		foreach ( array_keys( $all_definitions ) as $assessment_type ) {
			if ( 'health_optimization_assessment' === $assessment_type ) {
				continue;
			}
			$meta_key        = 'ennu_' . $assessment_type . '_category_scores';
			$category_scores = $user_meta_batch[ $meta_key ] ?? array();
			if ( is_array( $category_scores ) && ! empty( $category_scores ) ) {
				$all_category_scores = array_merge( $all_category_scores, $category_scores );
			}
		}

		// 2. Calculate Base Pillar Scores (Quantitative Engine)
		$pillar_calculator  = new ENNU_Pillar_Score_Calculator( $all_category_scores, $pillar_map );
		$base_pillar_scores = $pillar_calculator->calculate();

		// 3. Apply Qualitative Engine (Symptom Penalties) - NEW!
		$qualitative_adjusted_scores = $base_pillar_scores;
		$qualitative_data            = array();

		if ( class_exists( 'ENNU_Qualitative_Engine' ) ) {
			$user_symptoms = self::get_symptom_data_for_user( $user_id );
			$all_symptoms  = array();

			foreach ( $user_symptoms as $assessment_type => $symptoms ) {
				if ( is_array( $symptoms ) ) {
					$all_symptoms = array_merge( $all_symptoms, $symptoms );
				}
			}

			if ( ! empty( $all_symptoms ) ) {
				$qualitative_engine          = new ENNU_Qualitative_Engine( $all_symptoms );
				$qualitative_adjusted_scores = $qualitative_engine->apply_pillar_integrity_penalties( $base_pillar_scores );
				$qualitative_data            = array(
					'penalty_log'      => $qualitative_engine->get_penalty_log(),
					'penalty_summary'  => $qualitative_engine->get_penalty_summary(),
					'user_explanation' => $qualitative_engine->get_user_explanation(),
				);

				error_log( 'ENNU Scoring: Applied Qualitative Engine penalties for user ' . $user_id );
			}
		}

		$objective_adjusted_scores = $qualitative_adjusted_scores;
		$objective_data            = array();

		if ( class_exists( 'ENNU_Objective_Engine' ) ) {
			$user_biomarkers = get_user_meta( $user_id, 'ennu_biomarker_data', true );

			if ( ! empty( $user_biomarkers ) && is_array( $user_biomarkers ) ) {
				$objective_engine          = new ENNU_Objective_Engine( $user_biomarkers );
				$objective_adjusted_scores = $objective_engine->apply_biomarker_actuality_adjustments( $qualitative_adjusted_scores );
				$objective_data            = array(
					'adjustment_log'     => $objective_engine->get_adjustment_log(),
					'adjustment_summary' => $objective_engine->get_adjustment_summary(),
					'user_explanation'   => $objective_engine->get_user_explanation(),
				);

				error_log( 'ENNU Scoring: Applied Objective Engine adjustments for user ' . $user_id );
			}
		}

		$final_pillar_scores = $objective_adjusted_scores;
		$intentionality_data = array();

		if ( ! empty( $health_goals ) && ! empty( $goal_definitions ) && class_exists( 'ENNU_Intentionality_Engine' ) ) {
			$intentionality_engine = new ENNU_Intentionality_Engine( $health_goals, $goal_definitions, $objective_adjusted_scores );
			$final_pillar_scores   = $intentionality_engine->apply_goal_alignment_boost();
			$intentionality_data   = array(
				'boost_log'        => $intentionality_engine->get_boost_log(),
				'boost_summary'    => $intentionality_engine->get_boost_summary(),
				'user_explanation' => $intentionality_engine->get_user_explanation(),
			);

			error_log( 'ENNU Scoring: Applied Intentionality Engine boosts for user ' . $user_id );
		} else {
			error_log( 'ENNU Scoring: Skipped Intentionality Engine - missing goals, definitions, or class' );
		}

		// 6. Calculate Final ENNU Life Score with all engine adjustments
		$ennu_life_score_calculator = new ENNU_Life_Score_Calculator( $user_id, $final_pillar_scores, $all_definitions, $health_goals, $goal_definitions );
		$ennu_life_score_data       = $ennu_life_score_calculator->calculate();

		update_user_meta( $user_id, 'ennu_life_score_data', $ennu_life_score_data );
		update_user_meta( $user_id, 'ennu_pillar_scores', $final_pillar_scores );
		update_user_meta( $user_id, 'ennu_qualitative_data', $qualitative_data );
		update_user_meta( $user_id, 'ennu_objective_data', $objective_data );
		update_user_meta( $user_id, 'ennu_intentionality_data', $intentionality_data );

		$score_history = get_user_meta( $user_id, 'ennu_life_score_history', true );
		if ( ! is_array( $score_history ) ) {
			$score_history = array();
		}

		$score_history[] = array(
			'score'                         => is_array( $ennu_life_score_data ) && isset( $ennu_life_score_data['ennu_life_score'] ) ? $ennu_life_score_data['ennu_life_score'] : 0,
			'date'                          => current_time( 'mysql' ),
			'timestamp'                     => time(),
			'goal_boost_applied'            => ! empty( $intentionality_data['boost_summary']['boosts_applied'] ),
			'goals_count'                   => is_array( $health_goals ) ? count( $health_goals ) : 0,
			'symptom_penalties_applied'     => ! empty( $qualitative_data['penalty_summary']['penalties_applied'] ),
			'biomarker_adjustments_applied' => ! empty( $objective_data['adjustment_summary']['adjustments_applied'] ),
		);

		update_user_meta( $user_id, 'ennu_life_score_history', $score_history );

		$metrics = $performance_monitor->end_timer( 'scoring_calculation' );
		$final_score = is_array( $ennu_life_score_data ) && isset( $ennu_life_score_data['ennu_life_score'] ) ? $ennu_life_score_data['ennu_life_score'] : 'ERROR';
		error_log( 'ENNU Scoring: Complete scoring calculation finished for user ' . $user_id . ' with final score: ' . $final_score . ' (execution time: ' . round( $metrics['execution_time'] * 1000, 2 ) . 'ms, memory: ' . round( $metrics['memory_usage'] / 1024, 2 ) . 'KB)' );

		return $ennu_life_score_data;
	}

	public static function calculate_average_pillar_scores( $user_id ) {
		$pillar_scores = get_user_meta( $user_id, 'ennu_average_pillar_scores', true );
		if ( is_array( $pillar_scores ) && ! empty( $pillar_scores ) ) {
			return $pillar_scores;
		}

		$performance_monitor = ENNU_Performance_Monitor::get_instance();
		$performance_monitor->start_timer( 'pillar_calculation' );

		$all_definitions     = self::get_all_definitions();
		$pillar_map          = self::get_health_pillar_map();
		$all_category_scores = array();

		$meta_keys = array();
		foreach ( array_keys( $all_definitions ) as $assessment_type ) {
			if ( 'health_optimization_assessment' === $assessment_type ) {
				continue;
			}
			$meta_keys[] = 'ennu_' . $assessment_type . '_category_scores';
		}

		$user_meta_batch = array();
		foreach ( $meta_keys as $key ) {
			$user_meta_batch[ $key ] = get_user_meta( $user_id, $key, true );
		}

		foreach ( array_keys( $all_definitions ) as $assessment_type ) {
			if ( 'health_optimization_assessment' === $assessment_type ) {
				continue;
			}
			$meta_key        = 'ennu_' . $assessment_type . '_category_scores';
			$category_scores = $user_meta_batch[ $meta_key ] ?? array();
			if ( is_array( $category_scores ) && ! empty( $category_scores ) ) {
				$all_category_scores = array_merge( $all_category_scores, $category_scores );
			}
		}

		$pillar_calculator  = new ENNU_Pillar_Score_Calculator( $all_category_scores, $pillar_map );
		$base_pillar_scores = $pillar_calculator->calculate();

		$metrics = $performance_monitor->end_timer( 'pillar_calculation' );
		error_log( 'ENNU Scoring: Average pillar scores calculated for user ' . $user_id . ' (execution time: ' . round( $metrics['execution_time'] * 1000, 2 ) . 'ms, memory: ' . round( $metrics['memory_usage'] / 1024, 2 ) . 'KB)' );

		// Save for future use
		update_user_meta( $user_id, 'ennu_average_pillar_scores', $base_pillar_scores );
		return $base_pillar_scores;
	}

	public static function get_score_interpretation( $score ) {
		if ( $score >= 8.5 ) {
			return array(
				'level'       => 'Excellent',
				'color'       => '#10b981',
				'description' => 'Outstanding results expected with minimal intervention needed.',
			);
		} elseif ( $score >= 7.0 ) {
			return array(
				'level'       => 'Good',
				'color'       => '#3b82f6',
				'description' => 'Good foundation with some areas for optimization.',
			);
		} elseif ( $score >= 5.5 ) {
			return array(
				'level'       => 'Fair',
				'color'       => '#f59e0b',
				'description' => 'Moderate intervention recommended for best results.',
			);
		} elseif ( $score >= 3.5 ) {
			return array(
				'level'       => 'Needs Attention',
				'color'       => '#ef4444',
				'description' => 'Significant improvements recommended for optimal outcomes.',
			);
		} else {
			return array(
				'level'       => 'Critical',
				'color'       => '#dc2626',
				'description' => 'Immediate comprehensive intervention strongly recommended.',
			);
		}
	}

	/**
	 * Calculate age score from DOB for assessment scoring
	 *
	 * @param string $dob Date of birth in Y-m-d format
	 * @param array $age_scores Age score mapping
	 * @return int Age score
	 */
	public static function calculate_age_score_from_dob( $dob, $age_scores ) {
		if ( empty( $dob ) || empty( $age_scores ) ) {
			return 0;
		}

		// Use the age management system to calculate exact age
		if ( class_exists( 'ENNU_Age_Management_System' ) ) {
			$exact_age = ENNU_Age_Management_System::calculate_exact_age( $dob );
			if ( $exact_age === false ) {
				return 0;
			}

			$age_range = ENNU_Age_Management_System::get_age_range( $exact_age );
			if ( $age_range && isset( $age_scores[ $age_range ] ) ) {
				return $age_scores[ $age_range ];
			}
		}

		// Fallback calculation if age management system is not available
		try {
			$birth_date = new DateTime( $dob );
			$current_date = new DateTime();
			$age = $current_date->diff( $birth_date )->y;

			// Map age to range and get score
			if ( $age >= 18 && $age <= 25 && isset( $age_scores['18-25'] ) ) {
				return $age_scores['18-25'];
			} elseif ( $age >= 26 && $age <= 35 && isset( $age_scores['26-35'] ) ) {
				return $age_scores['26-35'];
			} elseif ( $age >= 36 && $age <= 45 && isset( $age_scores['36-45'] ) ) {
				return $age_scores['36-45'];
			} elseif ( $age >= 46 && $age <= 55 && isset( $age_scores['46-55'] ) ) {
				return $age_scores['46-55'];
			} elseif ( $age >= 56 && $age <= 65 && isset( $age_scores['56-65'] ) ) {
				return $age_scores['56-65'];
			} elseif ( $age >= 66 && $age <= 75 && isset( $age_scores['66-75'] ) ) {
				return $age_scores['66-75'];
			} elseif ( $age >= 76 && isset( $age_scores['76+'] ) ) {
				return $age_scores['76+'];
			}
		} catch ( Exception $e ) {
			error_log( "ENNU Scoring: Error calculating age from DOB: " . $e->getMessage() );
		}

		return 0;
	}

	/**
	 * Get symptom data for a specific user from qualitative assessments.
	 *
	 * @param int $user_id The user ID
	 * @return array Array of symptom data organized by assessment type
	 */
	public static function get_symptom_data_for_user( $user_id ) {
		// NEW: Use centralized symptoms system if available
		if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
			$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $user_id );

			// Convert centralized format to expected format for backward compatibility
			$symptom_data = array();

			if ( ! empty( $centralized_symptoms['by_assessment'] ) ) {
				foreach ( $centralized_symptoms['by_assessment'] as $assessment_type => $symptoms ) {
					$symptom_data[ $assessment_type ] = array();
					foreach ( $symptoms as $symptom ) {
						$symptom_data[ $assessment_type ][] = $symptom['name'];
					}
				}
			}

			return $symptom_data;
		}

		// FALLBACK: Original fragmented approach for backward compatibility
		$symptom_data = array();

		// Get symptom data from health optimization assessment
		$health_opt_symptoms = get_user_meta( $user_id, 'ennu_health_optimization_symptoms', true );
		if ( ! empty( $health_opt_symptoms ) && is_array( $health_opt_symptoms ) ) {
			$symptom_data['health_optimization'] = $health_opt_symptoms;
		}

		// Get symptom data from other qualitative assessments
		$qualitative_assessments = array(
			'testosterone' => 'ennu_testosterone_symptoms',
			'hormone'      => 'ennu_hormone_symptoms',
			'menopause'    => 'ennu_menopause_symptoms',
			'ed_treatment' => 'ennu_ed_treatment_symptoms',
			'skin'         => 'ennu_skin_symptoms',
			'hair'         => 'ennu_hair_symptoms',
			'sleep'        => 'ennu_sleep_symptoms',
			'weight_loss'  => 'ennu_weight_loss_symptoms',
		);

		foreach ( $qualitative_assessments as $assessment_type => $meta_key ) {
			$assessment_symptoms = get_user_meta( $user_id, $meta_key, true );
			if ( ! empty( $assessment_symptoms ) && is_array( $assessment_symptoms ) ) {
				$symptom_data[ $assessment_type ] = $assessment_symptoms;
			}
		}

		// Get qualitative question responses that might contain symptoms
		$qualitative_responses = get_user_meta( $user_id, 'ennu_qualitative_responses', true );
		if ( ! empty( $qualitative_responses ) && is_array( $qualitative_responses ) ) {
			$symptom_data['qualitative'] = $qualitative_responses;
		}

		return $symptom_data;
	}

	public static function clear_configuration_cache() {
		delete_transient( 'ennu_assessment_definitions_v1' );
		delete_transient( 'ennu_pillar_map_v1' );
		delete_transient( 'ennu_health_goal_definitions_v1' );

		self::$all_definitions = array();
		self::$pillar_map      = array();

		error_log( 'ENNU Caching: All configuration caches cleared' );

		return array(
			'success'        => true,
			'message'        => 'Configuration caches cleared successfully',
			'cleared_caches' => array(
				'assessment_definitions',
				'pillar_map',
				'health_goal_definitions',
			),
		);
	}

	/**
	 * Map assessment type to config file name
	 *
	 * @param string $assessment_type The assessment type from the system
	 * @return string The config file name
	 */
	private static function map_assessment_type_to_config( $assessment_type ) {
		$mapping = array(
			'hair_assessment' => 'hair',
			'weight_loss_assessment' => 'weight-loss',
			'ed_treatment_assessment' => 'ed-treatment',
			'health_assessment' => 'health',
			'skin_assessment' => 'skin',
			'hormone_assessment' => 'hormone',
			'sleep_assessment' => 'sleep',
			'menopause_assessment' => 'menopause',
			'testosterone_assessment' => 'testosterone',
			'welcome_assessment' => 'welcome',
			'health_optimization_assessment' => 'health-optimization',
		);
		
		return $mapping[ $assessment_type ] ?? $assessment_type;
	}
	
	/**
	 * Calculate scores for a specific assessment
	 *
	 * @param string $assessment_type The assessment type
	 * @param array $form_data The form data
	 * @return array The calculated scores
	 */
	public static function calculate_scores_for_assessment( $assessment_type, $form_data ) {
		// Clear cache to ensure fresh assessment definitions are loaded
		self::clear_configuration_cache();
		
		// Map assessment type to config file name
		$config_assessment_type = self::map_assessment_type_to_config( $assessment_type );
		
		// Get the assessment definition
		$all_definitions = self::get_all_definitions();
		$assessment_config = $all_definitions[ $config_assessment_type ] ?? null;
		
		if ( ! $assessment_config ) {
			error_log( "ENNU Scoring: Assessment config not found for {$assessment_type} (mapped to {$config_assessment_type})" );
			return null;
		}
		
		// Initialize scoring data
		$category_scores = array();
		$category_weights = array();
		$category_counts = array();
		
		// Process each question in the assessment
		foreach ( $assessment_config['questions'] ?? array() as $question_key => $question_config ) {
			// Skip if no scoring configuration
			if ( ! isset( $question_config['scoring'] ) ) {
				continue;
			}
			
			$category = $question_config['scoring']['category'] ?? 'general';
			$weight = $question_config['scoring']['weight'] ?? 1.0;
			$answers = $question_config['scoring']['answers'] ?? array();
			
			// Get user's answer for this question
			$user_answer = $form_data[ $question_key ] ?? null;
			
			// Handle multiselect questions (array) vs single select questions (string)
			if ( $user_answer !== null ) {
				$answer_scores = array();
				
				if ( is_array( $user_answer ) ) {
					// Multiselect question - calculate average score for selected options
					foreach ( $user_answer as $selected_option ) {
						if ( isset( $answers[ $selected_option ] ) ) {
							$answer_scores[] = $answers[ $selected_option ];
						}
					}
				} else {
					// Single select question - ensure $user_answer is a string
					if ( is_string( $user_answer ) && isset( $answers[ $user_answer ] ) ) {
						$answer_scores[] = $answers[ $user_answer ];
					}
				}
				
				// If we have valid scores, process them
				if ( ! empty( $answer_scores ) ) {
					$average_score = array_sum( $answer_scores ) / count( $answer_scores );
					
					// Initialize category if not exists
					if ( ! isset( $category_scores[ $category ] ) ) {
						$category_scores[ $category ] = 0;
						$category_weights[ $category ] = 0;
						$category_counts[ $category ] = 0;
					}
					
					// Add weighted score to category
					$category_scores[ $category ] += ( $average_score * $weight );
					$category_weights[ $category ] += $weight;
					$category_counts[ $category ]++;
				}
			}
		}
		
		// Calculate weighted averages for each category
		$final_category_scores = array();
		$total_category_score = 0;
		$total_weight = 0;
		
		foreach ( $category_scores as $category => $score ) {
			if ( $category_weights[ $category ] > 0 ) {
				$average_score = $score / $category_weights[ $category ];
				$final_category_scores[ $category ] = round( $average_score, 1 );
				$total_category_score += $average_score * $category_weights[ $category ];
				$total_weight += $category_weights[ $category ];
			} else {
				$final_category_scores[ $category ] = 0;
			}
		}
		
		// Calculate overall assessment score
		$overall_score = $total_weight > 0 ? round( $total_category_score / $total_weight, 1 ) : 0;
		
		// Map categories to pillars based on assessment type
		$pillar_scores = self::map_categories_to_pillars( $assessment_type, $final_category_scores );
		
		// Log the scoring calculation for debugging
		error_log( "ENNU Scoring: Calculated scores for {$assessment_type} - Overall: {$overall_score}, Categories: " . print_r( $final_category_scores, true ) );
		
		return array(
			'overall_score'   => $overall_score,
			'category_scores' => $final_category_scores,
			'pillar_scores'   => $pillar_scores,
		);
	}
	
	/**
	 * Map assessment categories to health pillars
	 *
	 * @param string $assessment_type The assessment type
	 * @param array $category_scores The category scores
	 * @return array The pillar scores
	 */
	private static function map_categories_to_pillars( $assessment_type, $category_scores ) {
		// Get pillar mapping
		$pillar_map = self::get_health_pillar_map();
		
		// Initialize pillar scores
		$pillar_scores = array(
			'Mind'       => 0,
			'Body'       => 0,
			'Lifestyle'  => 0,
			'Aesthetics' => 0,
		);
		
		$pillar_weights = array(
			'Mind'       => 0,
			'Body'       => 0,
			'Lifestyle'  => 0,
			'Aesthetics' => 0,
		);
		
		// Assessment-specific category to pillar mapping
		$category_pillar_mapping = array(
			'hair' => array(
				'Hair Health Status' => 'Aesthetics',
				'Progression Timeline' => 'Aesthetics',
				'Progression Rate' => 'Aesthetics',
				'Genetic Factors' => 'Body',
				'Lifestyle Factors' => 'Lifestyle',
				'Nutritional Support' => 'Lifestyle',
				'Treatment History' => 'Body',
				'Treatment Expectations' => 'Mind',
			),
			'weight_loss' => array(
				'Motivation & Goals' => 'Mind',
				'Current Status' => 'Body',
				'Physical Activity' => 'Lifestyle',
				'Nutrition' => 'Lifestyle',
				'Lifestyle Factors' => 'Lifestyle',
				'Psychological Factors' => 'Mind',
				'Behavioral Patterns' => 'Mind',
				'Medical Factors' => 'Body',
				'Weight Loss History' => 'Body',
				'Social Support' => 'Mind',
			),
			'testosterone' => array(
				'Symptom Severity' => 'Body',
				'Energy Levels' => 'Body',
				'Libido' => 'Body',
				'Mood' => 'Mind',
				'Physical Changes' => 'Body',
				'Medical History' => 'Body',
				'Lifestyle Factors' => 'Lifestyle',
				'Treatment Goals' => 'Mind',
			),
			'sleep' => array(
				'Sleep Quality' => 'Lifestyle',
				'Sleep Duration' => 'Lifestyle',
				'Sleep Problems' => 'Lifestyle',
				'Daytime Functioning' => 'Mind',
				'Stress Levels' => 'Mind',
				'Lifestyle Factors' => 'Lifestyle',
				'Medical Factors' => 'Body',
				'Treatment Goals' => 'Mind',
			),
			'health' => array(
				'General Health' => 'Body',
				'Energy Levels' => 'Body',
				'Mental Health' => 'Mind',
				'Lifestyle Factors' => 'Lifestyle',
				'Medical History' => 'Body',
				'Family History' => 'Body',
				'Health Goals' => 'Mind',
			),
		);
		
		// Get mapping for this assessment type
		$mapping = $category_pillar_mapping[ $assessment_type ] ?? array();
		
		// Map each category to its pillar
		foreach ( $category_scores as $category => $score ) {
			$pillar = $mapping[ $category ] ?? 'Body'; // Default to Body if no mapping
			
			$pillar_scores[ $pillar ] += $score;
			$pillar_weights[ $pillar ]++;
		}
		
		// Calculate average pillar scores
		foreach ( $pillar_scores as $pillar => $score ) {
			if ( $pillar_weights[ $pillar ] > 0 ) {
				$pillar_scores[ $pillar ] = round( $score / $pillar_weights[ $pillar ], 1 );
			} else {
				$pillar_scores[ $pillar ] = 0;
			}
		}
		
		return $pillar_scores;
	}

	/**
	 * Get health optimization report data
	 *
	 * @param array $symptom_data The symptom data
	 * @return array The report data
	 */
	public static function get_health_optimization_report_data( $symptom_data ) {
		// Return basic report structure - this should be enhanced with actual analysis
		return array(
			'user_id'         => get_current_user_id(),
			'symptom_data'    => $symptom_data,
			'assessment_type' => 'health_optimization',
			'overall_score'   => 7.5,
			'pillar_scores'   => array(
				'Mind'       => 7.5,
				'Body'       => 7.8,
				'Lifestyle'  => 7.3,
				'Aesthetics' => 7.0,
			),
			'recommendations' => array(
				'priority'  => 'Improve sleep quality and stress management',
				'secondary' => 'Focus on hormonal balance and energy optimization',
			),
			'next_steps'      => array(
				'consultation' => 'Schedule a consultation to discuss personalized recommendations',
				'biomarkers'   => 'Consider biomarker testing for deeper insights',
			),
		);
	}
}

