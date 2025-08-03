<?php
/**
 * ENNU Unified API Service
 * Provides a unified API layer for all assessment functionality
 * 
 * @package ENNU_Life_Assessments
 * @version 3.37.16
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Unified API service that provides a clean interface for all assessment functionality
 * Acts as the central hub for all assessment operations
 */
class ENNU_Unified_API_Service {

	private static $instance = null;
	private $logger;
	private $scoring_service;
	private $validation_service;
	private $rendering_service;
	private $data_collector;
	private $available_assessments = array();

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
		$this->scoring_service = class_exists( 'ENNU_Unified_Scoring_Service' ) ? ENNU_Unified_Scoring_Service::get_instance() : null;
		$this->validation_service = class_exists( 'ENNU_Data_Validation_Service' ) ? ENNU_Data_Validation_Service::get_instance() : null;
		$this->rendering_service = class_exists( 'ENNU_Assessment_Rendering_Service' ) ? ENNU_Assessment_Rendering_Service::get_instance() : null;
		$this->data_collector = class_exists( 'ENNU_Progressive_Data_Collector' ) ? ENNU_Progressive_Data_Collector::get_instance() : null;
		$this->init_available_assessments();
	}

	/**
	 * Initialize available assessments
	 */
	private function init_available_assessments() {
		$this->available_assessments = array(
			'weight_loss' => array(
				'name' => 'Weight Loss Assessment',
				'description' => 'Comprehensive weight loss and fitness assessment',
				'stages' => 5,
				'min_stages' => 3,
				'fields' => array( 'current_weight', 'target_weight', 'height', 'age', 'gender', 'activity_level', 'diet_type', 'health_goals' )
			),
			'sleep' => array(
				'name' => 'Sleep Assessment',
				'description' => 'Sleep quality and patterns assessment',
				'stages' => 4,
				'min_stages' => 2,
				'fields' => array( 'sleep_hours', 'sleep_quality', 'bedtime', 'wake_time', 'sleep_issues', 'stress_level' )
			),
			'hormone' => array(
				'name' => 'Hormone Assessment',
				'description' => 'Hormonal health and balance assessment',
				'stages' => 4,
				'min_stages' => 2,
				'fields' => array( 'hormone_symptoms', 'test_results', 'lifestyle_factors', 'medical_history' )
			),
			'nutrition' => array(
				'name' => 'Nutrition Assessment',
				'description' => 'Dietary habits and nutritional needs assessment',
				'stages' => 4,
				'min_stages' => 2,
				'fields' => array( 'diet_type', 'meal_frequency', 'supplements', 'nutritional_goals' )
			),
			'fitness' => array(
				'name' => 'Fitness Assessment',
				'description' => 'Physical fitness and performance assessment',
				'stages' => 4,
				'min_stages' => 2,
				'fields' => array( 'workout_frequency', 'workout_duration', 'fitness_goals', 'equipment_access' )
			),
			'stress' => array(
				'name' => 'Stress Assessment',
				'description' => 'Stress levels and coping mechanisms assessment',
				'stages' => 4,
				'min_stages' => 2,
				'fields' => array( 'stress_level', 'stress_sources', 'coping_methods', 'lifestyle_factors' )
			),
			'energy' => array(
				'name' => 'Energy Assessment',
				'description' => 'Energy levels and vitality assessment',
				'stages' => 4,
				'min_stages' => 2,
				'fields' => array( 'energy_level', 'energy_pattern', 'energy_boosters', 'health_conditions' )
			),
			'recovery' => array(
				'name' => 'Recovery Assessment',
				'description' => 'Recovery patterns and optimization assessment',
				'stages' => 4,
				'min_stages' => 2,
				'fields' => array( 'recovery_time', 'recovery_methods', 'training_load', 'injury_history' )
			),
			'longevity' => array(
				'name' => 'Longevity Assessment',
				'description' => 'Long-term health and longevity assessment',
				'stages' => 4,
				'min_stages' => 2,
				'fields' => array( 'age', 'family_history', 'longevity_goals', 'biomarkers' )
			),
			'performance' => array(
				'name' => 'Performance Assessment',
				'description' => 'Athletic performance and optimization assessment',
				'stages' => 4,
				'min_stages' => 2,
				'fields' => array( 'performance_metrics', 'performance_goals', 'training_history', 'performance_barriers' )
			),
			'wellness' => array(
				'name' => 'Wellness Assessment',
				'description' => 'Overall wellness and life satisfaction assessment',
				'stages' => 4,
				'min_stages' => 2,
				'fields' => array( 'overall_health', 'wellness_dimensions', 'wellness_goals', 'prevention_focus' )
			)
		);
	}

	/**
	 * Get available assessments
	 * 
	 * @return array Available assessments
	 */
	public function get_available_assessments() {
		return $this->available_assessments;
	}

	/**
	 * Start assessment
	 * 
	 * @param string $assessment_type Assessment type
	 * @param int $user_id User ID
	 * @return array Start result
	 */
	public function start_assessment( $assessment_type, $user_id ) {
		// Validate assessment type
		if ( ! isset( $this->available_assessments[ $assessment_type ] ) ) {
			return array(
				'success' => false,
				'error' => 'Invalid assessment type: ' . $assessment_type
			);
		}

		// Check if user already has an active session
		$existing_session = $this->get_user_active_session( $user_id, $assessment_type );
		if ( $existing_session ) {
			return array(
				'success' => true,
				'session_id' => $existing_session['session_id'],
				'resumed' => true,
				'progress' => $existing_session['progress']
			);
		}

		// Start progressive collection if available
		if ( $this->data_collector ) {
			$result = $this->data_collector->start_collection( $assessment_type, $user_id );
			if ( $result['success'] ) {
				return array(
					'success' => true,
					'session_id' => $result['session_id'],
					'stages' => $result['stages'],
					'current_stage_data' => $result['current_stage_data'],
					'resumed' => false
				);
			}
			return $result;
		}

		// Fallback to traditional assessment start
		return $this->start_traditional_assessment( $assessment_type, $user_id );
	}

	/**
	 * Submit assessment data
	 * 
	 * @param string $session_id Session ID
	 * @param array $data Assessment data
	 * @return array Submission result
	 */
	public function submit_assessment_data( $session_id, $data ) {
		// Try progressive collection first
		if ( $this->data_collector ) {
			$result = $this->data_collector->submit_stage_data( $session_id, $data );
			if ( $result['success'] ) {
				return $result;
			}
		}

		// Fallback to traditional submission
		return $this->submit_traditional_assessment( $session_id, $data );
	}

	/**
	 * Get assessment results
	 * 
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @return array Assessment results
	 */
	public function get_assessment_results( $user_id, $assessment_type ) {
		// Get user assessment data
		$assessment_data = get_user_meta( $user_id, "ennu_{$assessment_type}_data", true );
		if ( empty( $assessment_data ) ) {
			return array(
				'success' => false,
				'error' => 'No assessment data found'
			);
		}

		// Calculate scores if scoring service is available
		$scores = array();
		if ( $this->scoring_service ) {
			$scores = $this->scoring_service->calculate_scores( $user_id, $assessment_type, $assessment_data['data'] );
		}

		// Generate results using rendering service if available
		$results_html = '';
		if ( $this->rendering_service ) {
			$results_html = $this->rendering_service->render_assessment_results( $assessment_type, $assessment_data, $scores );
		}

		return array(
			'success' => true,
			'data' => $assessment_data['data'],
			'scores' => $scores,
			'results_html' => $results_html,
			'completion_time' => $assessment_data['completion_time'],
			'session_id' => $assessment_data['session_id']
		);
	}

	/**
	 * Validate assessment data
	 * 
	 * @param string $assessment_type Assessment type
	 * @param array $data Assessment data
	 * @return array Validation result
	 */
	public function validate_assessment_data( $assessment_type, $data ) {
		if ( $this->validation_service ) {
			return $this->validation_service->validate_assessment_data( $assessment_type, $data );
		}

		// Basic validation fallback
		return array(
			'success' => true,
			'errors' => array(),
			'warnings' => array(),
			'sanitized_data' => $data
		);
	}

	/**
	 * Render assessment form
	 * 
	 * @param string $assessment_type Assessment type
	 * @param array $session_data Session data
	 * @return string Form HTML
	 */
	public function render_assessment_form( $assessment_type, $session_data = null ) {
		if ( $this->rendering_service ) {
			return $this->rendering_service->render_assessment_form( $assessment_type, $session_data );
		}

		// Fallback form rendering
		return $this->render_fallback_form( $assessment_type, $session_data );
	}

	/**
	 * Get user assessment history
	 * 
	 * @param int $user_id User ID
	 * @return array Assessment history
	 */
	public function get_user_assessment_history( $user_id ) {
		$history = array();

		foreach ( $this->available_assessments as $type => $assessment ) {
			$data = get_user_meta( $user_id, "ennu_{$type}_data", true );
			if ( ! empty( $data ) ) {
				$history[ $type ] = array(
					'name' => $assessment['name'],
					'completion_time' => $data['completion_time'],
					'scores' => $data['scores'],
					'stages_completed' => $data['completed_stages']
				);
			}
		}

		return array(
			'success' => true,
			'history' => $history,
			'total_assessments' => count( $history )
		);
	}

	/**
	 * Get user active session
	 * 
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @return array|null Session data
	 */
	private function get_user_active_session( $user_id, $assessment_type ) {
		if ( ! $this->data_collector ) {
			return null;
		}

		// Get all user sessions
		$sessions = get_user_meta( $user_id, 'ennu_collection_sessions', true );
		if ( empty( $sessions ) ) {
			return null;
		}

		foreach ( $sessions as $session_id => $session ) {
			if ( $session['assessment_type'] === $assessment_type && $session['status'] === 'active' ) {
				return $session;
			}
		}

		return null;
	}

	/**
	 * Start traditional assessment (fallback)
	 * 
	 * @param string $assessment_type Assessment type
	 * @param int $user_id User ID
	 * @return array Start result
	 */
	private function start_traditional_assessment( $assessment_type, $user_id ) {
		$session_id = 'traditional_' . uniqid() . '_' . time();

		$session_data = array(
			'session_id' => $session_id,
			'assessment_type' => $assessment_type,
			'user_id' => $user_id,
			'start_time' => current_time( 'timestamp' ),
			'status' => 'active'
		);

		update_user_meta( $user_id, "ennu_traditional_session_{$session_id}", $session_data );

		return array(
			'success' => true,
			'session_id' => $session_id,
			'traditional' => true
		);
	}

	/**
	 * Submit traditional assessment (fallback)
	 * 
	 * @param string $session_id Session ID
	 * @param array $data Assessment data
	 * @return array Submission result
	 */
	private function submit_traditional_assessment( $session_id, $data ) {
		// Extract user ID from session
		$session_parts = explode( '_', $session_id );
		$user_id = get_current_user_id();

		// Validate data
		$validation_result = $this->validate_assessment_data( $session_parts[1], $data );
		if ( ! $validation_result['success'] ) {
			return $validation_result;
		}

		// Calculate scores
		$scores = array();
		if ( $this->scoring_service ) {
			$scores = $this->scoring_service->calculate_scores( $user_id, $session_parts[1], $data );
		}

		// Save assessment data
		$assessment_data = array(
			'data' => $data,
			'scores' => $scores,
			'completion_time' => current_time( 'timestamp' ),
			'session_id' => $session_id
		);

		update_user_meta( $user_id, "ennu_{$session_parts[1]}_data", $assessment_data );

		return array(
			'success' => true,
			'assessment_complete' => true,
			'data' => $data,
			'scores' => $scores,
			'session_id' => $session_id
		);
	}

	/**
	 * Render fallback form
	 * 
	 * @param string $assessment_type Assessment type
	 * @param array $session_data Session data
	 * @return string Form HTML
	 */
	private function render_fallback_form( $assessment_type, $session_data = null ) {
		$assessment = $this->available_assessments[ $assessment_type ];
		
		$html = '<div class="ennu-assessment-form">';
		$html .= '<h2>' . esc_html( $assessment['name'] ) . '</h2>';
		$html .= '<p>' . esc_html( $assessment['description'] ) . '</p>';
		$html .= '<form id="ennu-assessment-form" data-assessment="' . esc_attr( $assessment_type ) . '">';
		
		foreach ( $assessment['fields'] as $field ) {
			$html .= '<div class="form-field">';
			$html .= '<label for="' . esc_attr( $field ) . '">' . esc_html( ucwords( str_replace( '_', ' ', $field ) ) ) . '</label>';
			$html .= '<input type="text" id="' . esc_attr( $field ) . '" name="' . esc_attr( $field ) . '" required>';
			$html .= '</div>';
		}
		
		$html .= '<button type="submit" class="ennu-submit-btn">Submit Assessment</button>';
		$html .= '</form>';
		$html .= '</div>';
		
		return $html;
	}

	/**
	 * Get assessment statistics
	 * 
	 * @return array Assessment statistics
	 */
	public function get_assessment_statistics() {
		$stats = array();

		foreach ( $this->available_assessments as $type => $assessment ) {
			$stats[ $type ] = array(
				'name' => $assessment['name'],
				'stages' => $assessment['stages'],
				'min_stages' => $assessment['min_stages'],
				'fields' => count( $assessment['fields'] )
			);
		}

		return array(
			'success' => true,
			'statistics' => $stats,
			'total_assessments' => count( $stats )
		);
	}

	/**
	 * Clean up expired sessions
	 * 
	 * @return int Number of sessions cleaned up
	 */
	public function cleanup_expired_sessions() {
		$cleaned_count = 0;

		if ( $this->data_collector ) {
			$cleaned_count += $this->data_collector->cleanup_expired_sessions();
		}

		return $cleaned_count;
	}

	/**
	 * Get service status
	 * 
	 * @return array Service status
	 */
	public function get_service_status() {
		return array(
			'success' => true,
			'services' => array(
				'scoring_service' => $this->scoring_service ? 'active' : 'inactive',
				'validation_service' => $this->validation_service ? 'active' : 'inactive',
				'rendering_service' => $this->rendering_service ? 'active' : 'inactive',
				'data_collector' => $this->data_collector ? 'active' : 'inactive'
			),
			'available_assessments' => count( $this->available_assessments ),
			'api_version' => '3.37.16'
		);
	}
} 