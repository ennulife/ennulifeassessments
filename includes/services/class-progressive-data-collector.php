<?php
/**
 * ENNU Progressive Data Collector Service
 * Implements progressive data collection framework for assessments
 * 
 * @package ENNU_Life_Assessments
 * @version 3.37.16
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Progressive data collector service that implements the framework
 * for collecting assessment data progressively, replacing monolithic approaches
 */
class ENNU_Progressive_Data_Collector {

	private static $instance = null;
	private $logger;
	private $validation_service;
	private $scoring_service;
	private $session_data = array();
	private $collection_stages = array();

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
		$this->logger = class_exists( 'ENNU_Logger' ) ? new ENNU_Logger() : null;
		$this->validation_service = class_exists( 'ENNU_Data_Validation_Service' ) ? ENNU_Data_Validation_Service::get_instance() : null;
		$this->scoring_service = class_exists( 'ENNU_Unified_Scoring_Service' ) ? ENNU_Unified_Scoring_Service::get_instance() : null;
		$this->init_collection_stages();
	}

	/**
	 * Initialize collection stages for all assessment types
	 */
	private function init_collection_stages() {
		$this->collection_stages = array(
			'weight_loss' => array(
				'stages' => array(
					'basic_info' => array(
						'name' => 'Basic Information',
						'fields' => array( 'current_weight', 'target_weight', 'height', 'age', 'gender' ),
						'required' => true,
						'validation' => 'basic_metrics'
					),
					'activity_level' => array(
						'name' => 'Activity Level',
						'fields' => array( 'activity_level', 'workout_frequency', 'workout_duration' ),
						'required' => true,
						'validation' => 'activity_metrics'
					),
					'diet_preferences' => array(
						'name' => 'Diet Preferences',
						'fields' => array( 'diet_type', 'meal_frequency', 'dietary_restrictions' ),
						'required' => false,
						'validation' => 'diet_metrics'
					),
					'health_goals' => array(
						'name' => 'Health Goals',
						'fields' => array( 'primary_goal', 'timeline', 'motivation_level' ),
						'required' => true,
						'validation' => 'goal_metrics'
					),
					'medical_history' => array(
						'name' => 'Medical History',
						'fields' => array( 'medical_conditions', 'medications', 'previous_weight_loss' ),
						'required' => false,
						'validation' => 'medical_metrics'
					)
				),
				'min_stages' => 3,
				'max_stages' => 5
			),
			'sleep' => array(
				'stages' => array(
					'sleep_patterns' => array(
						'name' => 'Sleep Patterns',
						'fields' => array( 'sleep_hours', 'bedtime', 'wake_time', 'sleep_quality' ),
						'required' => true,
						'validation' => 'sleep_metrics'
					),
					'sleep_environment' => array(
						'name' => 'Sleep Environment',
						'fields' => array( 'room_temperature', 'noise_level', 'light_level', 'bed_comfort' ),
						'required' => false,
						'validation' => 'environment_metrics'
					),
					'sleep_issues' => array(
						'name' => 'Sleep Issues',
						'fields' => array( 'sleep_issues', 'stress_level', 'caffeine_intake' ),
						'required' => true,
						'validation' => 'issue_metrics'
					),
					'lifestyle_factors' => array(
						'name' => 'Lifestyle Factors',
						'fields' => array( 'exercise_timing', 'screen_time', 'meal_timing' ),
						'required' => false,
						'validation' => 'lifestyle_metrics'
					)
				),
				'min_stages' => 2,
				'max_stages' => 4
			),
			'hormone' => array(
				'stages' => array(
					'symptoms' => array(
						'name' => 'Hormone Symptoms',
						'fields' => array( 'hormone_symptoms', 'symptom_severity', 'symptom_duration' ),
						'required' => true,
						'validation' => 'symptom_metrics'
					),
					'lifestyle_factors' => array(
						'name' => 'Lifestyle Factors',
						'fields' => array( 'stress_level', 'sleep_quality', 'exercise_frequency', 'diet_type' ),
						'required' => true,
						'validation' => 'lifestyle_metrics'
					),
					'medical_history' => array(
						'name' => 'Medical History',
						'fields' => array( 'medical_conditions', 'medications', 'family_history' ),
						'required' => false,
						'validation' => 'medical_metrics'
					),
					'test_results' => array(
						'name' => 'Test Results',
						'fields' => array( 'testosterone_level', 'estrogen_level', 'cortisol_level', 'thyroid_level' ),
						'required' => false,
						'validation' => 'test_metrics'
					)
				),
				'min_stages' => 2,
				'max_stages' => 4
			),
			'nutrition' => array(
				'stages' => array(
					'current_diet' => array(
						'name' => 'Current Diet',
						'fields' => array( 'diet_type', 'meal_frequency', 'food_preferences', 'allergies' ),
						'required' => true,
						'validation' => 'diet_metrics'
					),
					'nutritional_goals' => array(
						'name' => 'Nutritional Goals',
						'fields' => array( 'primary_goal', 'calorie_target', 'macro_preferences' ),
						'required' => true,
						'validation' => 'goal_metrics'
					),
					'supplements' => array(
						'name' => 'Supplements',
						'fields' => array( 'current_supplements', 'supplement_goals', 'vitamin_levels' ),
						'required' => false,
						'validation' => 'supplement_metrics'
					),
					'lifestyle_factors' => array(
						'name' => 'Lifestyle Factors',
						'fields' => array( 'activity_level', 'stress_level', 'sleep_quality', 'meal_timing' ),
						'required' => true,
						'validation' => 'lifestyle_metrics'
					)
				),
				'min_stages' => 2,
				'max_stages' => 4
			),
			'fitness' => array(
				'stages' => array(
					'current_fitness' => array(
						'name' => 'Current Fitness',
						'fields' => array( 'fitness_level', 'workout_frequency', 'workout_duration', 'strength_level' ),
						'required' => true,
						'validation' => 'fitness_metrics'
					),
					'fitness_goals' => array(
						'name' => 'Fitness Goals',
						'fields' => array( 'primary_goal', 'target_metrics', 'timeline', 'motivation' ),
						'required' => true,
						'validation' => 'goal_metrics'
					),
					'equipment_access' => array(
						'name' => 'Equipment Access',
						'fields' => array( 'home_equipment', 'gym_access', 'preferred_activities' ),
						'required' => false,
						'validation' => 'equipment_metrics'
					),
					'injury_history' => array(
						'name' => 'Injury History',
						'fields' => array( 'previous_injuries', 'current_limitations', 'recovery_status' ),
						'required' => false,
						'validation' => 'injury_metrics'
					)
				),
				'min_stages' => 2,
				'max_stages' => 4
			),
			'stress' => array(
				'stages' => array(
					'stress_assessment' => array(
						'name' => 'Stress Assessment',
						'fields' => array( 'stress_level', 'stress_sources', 'stress_frequency', 'coping_methods' ),
						'required' => true,
						'validation' => 'stress_metrics'
					),
					'lifestyle_factors' => array(
						'name' => 'Lifestyle Factors',
						'fields' => array( 'work_schedule', 'sleep_quality', 'exercise_frequency', 'social_support' ),
						'required' => true,
						'validation' => 'lifestyle_metrics'
					),
					'mental_health' => array(
						'name' => 'Mental Health',
						'fields' => array( 'anxiety_level', 'depression_symptoms', 'mood_patterns' ),
						'required' => false,
						'validation' => 'mental_health_metrics'
					),
					'coping_strategies' => array(
						'name' => 'Coping Strategies',
						'fields' => array( 'current_coping', 'effectiveness', 'preferred_methods' ),
						'required' => true,
						'validation' => 'coping_metrics'
					)
				),
				'min_stages' => 2,
				'max_stages' => 4
			),
			'energy' => array(
				'stages' => array(
					'energy_patterns' => array(
						'name' => 'Energy Patterns',
						'fields' => array( 'energy_level', 'energy_pattern', 'peak_hours', 'low_hours' ),
						'required' => true,
						'validation' => 'energy_metrics'
					),
					'lifestyle_factors' => array(
						'name' => 'Lifestyle Factors',
						'fields' => array( 'sleep_quality', 'exercise_frequency', 'diet_type', 'stress_level' ),
						'required' => true,
						'validation' => 'lifestyle_metrics'
					),
					'energy_boosters' => array(
						'name' => 'Energy Boosters',
						'fields' => array( 'current_boosters', 'effectiveness', 'preferred_methods' ),
						'required' => false,
						'validation' => 'booster_metrics'
					),
					'health_conditions' => array(
						'name' => 'Health Conditions',
						'fields' => array( 'medical_conditions', 'medications', 'fatigue_causes' ),
						'required' => false,
						'validation' => 'health_metrics'
					)
				),
				'min_stages' => 2,
				'max_stages' => 4
			),
			'recovery' => array(
				'stages' => array(
					'recovery_assessment' => array(
						'name' => 'Recovery Assessment',
						'fields' => array( 'recovery_time', 'recovery_quality', 'soreness_level', 'fatigue_level' ),
						'required' => true,
						'validation' => 'recovery_metrics'
					),
					'training_load' => array(
						'name' => 'Training Load',
						'fields' => array( 'workout_intensity', 'workout_frequency', 'training_volume', 'progression_rate' ),
						'required' => true,
						'validation' => 'training_metrics'
					),
					'recovery_methods' => array(
						'name' => 'Recovery Methods',
						'fields' => array( 'current_methods', 'effectiveness', 'preferred_methods' ),
						'required' => false,
						'validation' => 'method_metrics'
					),
					'injury_history' => array(
						'name' => 'Injury History',
						'fields' => array( 'previous_injuries', 'current_limitations', 'recovery_status' ),
						'required' => false,
						'validation' => 'injury_metrics'
					)
				),
				'min_stages' => 2,
				'max_stages' => 4
			),
			'longevity' => array(
				'stages' => array(
					'basic_metrics' => array(
						'name' => 'Basic Metrics',
						'fields' => array( 'age', 'gender', 'height', 'weight', 'family_history' ),
						'required' => true,
						'validation' => 'basic_metrics'
					),
					'health_assessment' => array(
						'name' => 'Health Assessment',
						'fields' => array( 'medical_conditions', 'medications', 'lifestyle_factors', 'health_goals' ),
						'required' => true,
						'validation' => 'health_metrics'
					),
					'longevity_goals' => array(
						'name' => 'Longevity Goals',
						'fields' => array( 'primary_goals', 'timeline', 'motivation', 'preferred_methods' ),
						'required' => true,
						'validation' => 'goal_metrics'
					),
					'biomarkers' => array(
						'name' => 'Biomarkers',
						'fields' => array( 'test_results', 'target_levels', 'monitoring_frequency' ),
						'required' => false,
						'validation' => 'biomarker_metrics'
					)
				),
				'min_stages' => 2,
				'max_stages' => 4
			),
			'performance' => array(
				'stages' => array(
					'performance_metrics' => array(
						'name' => 'Performance Metrics',
						'fields' => array( 'strength_level', 'endurance_level', 'flexibility_level', 'balance_level' ),
						'required' => true,
						'validation' => 'performance_metrics'
					),
					'performance_goals' => array(
						'name' => 'Performance Goals',
						'fields' => array( 'primary_goals', 'target_metrics', 'timeline', 'motivation' ),
						'required' => true,
						'validation' => 'goal_metrics'
					),
					'training_history' => array(
						'name' => 'Training History',
						'fields' => array( 'training_experience', 'current_program', 'progression_rate' ),
						'required' => true,
						'validation' => 'training_metrics'
					),
					'performance_barriers' => array(
						'name' => 'Performance Barriers',
						'fields' => array( 'current_limitations', 'injury_history', 'recovery_issues' ),
						'required' => false,
						'validation' => 'barrier_metrics'
					)
				),
				'min_stages' => 2,
				'max_stages' => 4
			),
			'wellness' => array(
				'stages' => array(
					'wellness_assessment' => array(
						'name' => 'Wellness Assessment',
						'fields' => array( 'overall_health', 'wellness_dimensions', 'life_satisfaction', 'stress_level' ),
						'required' => true,
						'validation' => 'wellness_metrics'
					),
					'lifestyle_factors' => array(
						'name' => 'Lifestyle Factors',
						'fields' => array( 'sleep_quality', 'exercise_frequency', 'diet_type', 'social_connections' ),
						'required' => true,
						'validation' => 'lifestyle_metrics'
					),
					'wellness_goals' => array(
						'name' => 'Wellness Goals',
						'fields' => array( 'primary_goals', 'priority_areas', 'timeline', 'motivation' ),
						'required' => true,
						'validation' => 'goal_metrics'
					),
					'prevention_focus' => array(
						'name' => 'Prevention Focus',
						'fields' => array( 'prevention_goals', 'risk_factors', 'preventive_measures' ),
						'required' => false,
						'validation' => 'prevention_metrics'
					)
				),
				'min_stages' => 2,
				'max_stages' => 4
			)
		);
	}

	/**
	 * Start progressive data collection for an assessment
	 * 
	 * @param string $assessment_type Assessment type
	 * @param int $user_id User ID
	 * @return array Collection session data
	 */
	public function start_collection( $assessment_type, $user_id ) {
		if ( ! isset( $this->collection_stages[ $assessment_type ] ) ) {
			return array(
				'success' => false,
				'error' => 'Invalid assessment type: ' . $assessment_type
			);
		}

		$session_id = $this->generate_session_id();
		$stages = $this->collection_stages[ $assessment_type ];

		$session_data = array(
			'session_id' => $session_id,
			'assessment_type' => $assessment_type,
			'user_id' => $user_id,
			'current_stage' => 0,
			'completed_stages' => array(),
			'data' => array(),
			'start_time' => current_time( 'timestamp' ),
			'status' => 'active'
		);

		// Store session data
		$this->session_data[ $session_id ] = $session_data;
		update_user_meta( $user_id, "ennu_collection_session_{$session_id}", $session_data );

		// Log collection start
		if ( $this->logger ) {
			$this->logger->log( 'Progressive collection started', array(
				'session_id' => $session_id,
				'assessment_type' => $assessment_type,
				'user_id' => $user_id
			) );
		}

		return array(
			'success' => true,
			'session_id' => $session_id,
			'stages' => $stages,
			'current_stage_data' => $this->get_stage_data( $assessment_type, 0 )
		);
	}

	/**
	 * Submit data for current stage
	 * 
	 * @param string $session_id Session ID
	 * @param array $stage_data Stage data
	 * @return array Submission result
	 */
	public function submit_stage_data( $session_id, $stage_data ) {
		if ( ! isset( $this->session_data[ $session_id ] ) ) {
			return array(
				'success' => false,
				'error' => 'Invalid session ID'
			);
		}

		$session = $this->session_data[ $session_id ];
		$assessment_type = $session['assessment_type'];
		$current_stage = $session['current_stage'];

		// Validate stage data
		$validation_result = $this->validate_stage_data( $assessment_type, $current_stage, $stage_data );
		if ( ! $validation_result['success'] ) {
			return $validation_result;
		}

		// Store stage data
		$session['data'][ $current_stage ] = $stage_data;
		$session['completed_stages'][] = $current_stage;
		$session['current_stage']++;

		// Update session data
		$this->session_data[ $session_id ] = $session;
		update_user_meta( $session['user_id'], "ennu_collection_session_{$session_id}", $session );

		// Check if collection is complete
		$is_complete = $this->is_collection_complete( $session );
		if ( $is_complete ) {
			$final_result = $this->complete_collection( $session_id );
			return $final_result;
		}

		// Get next stage data
		$next_stage_data = $this->get_stage_data( $assessment_type, $session['current_stage'] );

		// Log stage completion
		if ( $this->logger ) {
			$this->logger->log( 'Stage completed', array(
				'session_id' => $session_id,
				'stage' => $current_stage,
				'assessment_type' => $assessment_type,
				'is_complete' => $is_complete
			) );
		}

		return array(
			'success' => true,
			'stage_completed' => $current_stage,
			'next_stage' => $session['current_stage'],
			'next_stage_data' => $next_stage_data,
			'progress' => $this->calculate_progress( $session ),
			'is_complete' => $is_complete
		);
	}

	/**
	 * Get stage data for an assessment
	 * 
	 * @param string $assessment_type Assessment type
	 * @param int $stage_index Stage index
	 * @return array Stage data
	 */
	private function get_stage_data( $assessment_type, $stage_index ) {
		$stages = $this->collection_stages[ $assessment_type ]['stages'];
		$stage_keys = array_keys( $stages );

		if ( $stage_index >= count( $stage_keys ) ) {
			return null;
		}

		$stage_key = $stage_keys[ $stage_index ];
		$stage = $stages[ $stage_key ];

		return array(
			'stage_key' => $stage_key,
			'stage_index' => $stage_index,
			'name' => $stage['name'],
			'fields' => $stage['fields'],
			'required' => $stage['required'],
			'validation' => $stage['validation']
		);
	}

	/**
	 * Validate stage data
	 * 
	 * @param string $assessment_type Assessment type
	 * @param int $stage_index Stage index
	 * @param array $stage_data Stage data
	 * @return array Validation result
	 */
	private function validate_stage_data( $assessment_type, $stage_index, $stage_data ) {
		$stage_info = $this->get_stage_data( $assessment_type, $stage_index );
		if ( ! $stage_info ) {
			return array(
				'success' => false,
				'error' => 'Invalid stage index'
			);
		}

		// Check required fields
		if ( $stage_info['required'] ) {
			foreach ( $stage_info['fields'] as $field ) {
				if ( ! isset( $stage_data[ $field ] ) || empty( $stage_data[ $field ] ) ) {
					return array(
						'success' => false,
						'error' => "Required field missing: {$field}"
					);
				}
			}
		}

		// Use validation service if available
		if ( $this->validation_service ) {
			$validation_result = $this->validation_service->validate_assessment_data( $assessment_type, $stage_data );
			if ( ! $validation_result['success'] ) {
				return array(
					'success' => false,
					'error' => implode( ', ', $validation_result['errors'] )
				);
			}
		}

		return array( 'success' => true );
	}

	/**
	 * Check if collection is complete
	 * 
	 * @param array $session Session data
	 * @return bool Is complete
	 */
	private function is_collection_complete( $session ) {
		$assessment_type = $session['assessment_type'];
		$stages = $this->collection_stages[ $assessment_type ]['stages'];
		$min_stages = $this->collection_stages[ $assessment_type ]['min_stages'];

		$completed_count = count( $session['completed_stages'] );
		$total_stages = count( $stages );

		// Check if minimum stages are completed
		if ( $completed_count >= $min_stages ) {
			return true;
		}

		// Check if all stages are completed
		if ( $completed_count >= $total_stages ) {
			return true;
		}

		return false;
	}

	/**
	 * Complete collection and save final data
	 * 
	 * @param string $session_id Session ID
	 * @return array Completion result
	 */
	private function complete_collection( $session_id ) {
		$session = $this->session_data[ $session_id ];
		$assessment_type = $session['assessment_type'];
		$user_id = $session['user_id'];

		// Merge all stage data
		$final_data = array();
		foreach ( $session['data'] as $stage_data ) {
			$final_data = array_merge( $final_data, $stage_data );
		}

		// Calculate scores if scoring service is available
		$scores = array();
		if ( $this->scoring_service ) {
			$scores = $this->scoring_service->calculate_scores( $user_id, $assessment_type, $final_data );
		}

		// Save final assessment data
		$assessment_data = array(
			'data' => $final_data,
			'scores' => $scores,
			'completed_stages' => $session['completed_stages'],
			'completion_time' => current_time( 'timestamp' ),
			'session_id' => $session_id
		);

		update_user_meta( $user_id, "ennu_{$assessment_type}_data", $assessment_data );

		// Mark session as complete
		$session['status'] = 'completed';
		$session['completion_time'] = current_time( 'timestamp' );
		$this->session_data[ $session_id ] = $session;
		update_user_meta( $user_id, "ennu_collection_session_{$session_id}", $session );

		// Log completion
		if ( $this->logger ) {
			$this->logger->log( 'Progressive collection completed', array(
				'session_id' => $session_id,
				'assessment_type' => $assessment_type,
				'user_id' => $user_id,
				'stages_completed' => count( $session['completed_stages'] ),
				'scores' => $scores
			) );
		}

		return array(
			'success' => true,
			'assessment_complete' => true,
			'final_data' => $final_data,
			'scores' => $scores,
			'session_id' => $session_id
		);
	}

	/**
	 * Calculate collection progress
	 * 
	 * @param array $session Session data
	 * @return array Progress data
	 */
	private function calculate_progress( $session ) {
		$assessment_type = $session['assessment_type'];
		$stages = $this->collection_stages[ $assessment_type ]['stages'];
		$total_stages = count( $stages );
		$completed_stages = count( $session['completed_stages'] );
		$min_stages = $this->collection_stages[ $assessment_type ]['min_stages'];

		$progress_percentage = ( $completed_stages / $total_stages ) * 100;
		$min_progress_percentage = ( $min_stages / $total_stages ) * 100;

		return array(
			'completed_stages' => $completed_stages,
			'total_stages' => $total_stages,
			'progress_percentage' => $progress_percentage,
			'min_stages' => $min_stages,
			'min_progress_percentage' => $min_progress_percentage,
			'can_complete' => $completed_stages >= $min_stages
		);
	}

	/**
	 * Get session data
	 * 
	 * @param string $session_id Session ID
	 * @return array Session data
	 */
	public function get_session_data( $session_id ) {
		if ( ! isset( $this->session_data[ $session_id ] ) ) {
			return null;
		}

		return $this->session_data[ $session_id ];
	}

	/**
	 * Resume collection session
	 * 
	 * @param string $session_id Session ID
	 * @return array Resume result
	 */
	public function resume_session( $session_id ) {
		$session = $this->get_session_data( $session_id );
		if ( ! $session ) {
			return array(
				'success' => false,
				'error' => 'Session not found'
			);
		}

		if ( $session['status'] === 'completed' ) {
			return array(
				'success' => false,
				'error' => 'Session already completed'
			);
		}

		$assessment_type = $session['assessment_type'];
		$current_stage_data = $this->get_stage_data( $assessment_type, $session['current_stage'] );

		return array(
			'success' => true,
			'session' => $session,
			'current_stage_data' => $current_stage_data,
			'progress' => $this->calculate_progress( $session )
		);
	}

	/**
	 * Get collection stages for an assessment
	 * 
	 * @param string $assessment_type Assessment type
	 * @return array Collection stages
	 */
	public function get_collection_stages( $assessment_type ) {
		return isset( $this->collection_stages[ $assessment_type ] ) ? $this->collection_stages[ $assessment_type ] : null;
	}

	/**
	 * Generate unique session ID
	 * 
	 * @return string Session ID
	 */
	private function generate_session_id() {
		return 'ennu_' . uniqid() . '_' . time();
	}

	/**
	 * Clean up expired sessions
	 * 
	 * @param int $expiry_hours Hours before session expires
	 * @return int Number of sessions cleaned up
	 */
	public function cleanup_expired_sessions( $expiry_hours = 24 ) {
		$expiry_time = current_time( 'timestamp' ) - ( $expiry_hours * 3600 );
		$cleaned_count = 0;

		foreach ( $this->session_data as $session_id => $session ) {
			if ( $session['start_time'] < $expiry_time && $session['status'] !== 'completed' ) {
				unset( $this->session_data[ $session_id ] );
				delete_user_meta( $session['user_id'], "ennu_collection_session_{$session_id}" );
				$cleaned_count++;
			}
		}

		return $cleaned_count;
	}
} 