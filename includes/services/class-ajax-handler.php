<?php
/**
 * ENNU AJAX Handler Service
 *
 * Handles all AJAX operations for the ENNU Life Assessments plugin.
 *
 * @package ENNU_Life_Assessments
 * @since 64.11.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU AJAX Handler Service Class
 *
 * @since 64.11.0
 */
class ENNU_AJAX_Service_Handler {
	
	/**
	 * Assessment service instance
	 *
	 * @var ENNU_Assessment_Service
	 */
	private $assessment_service;
	
	/**
	 * Biomarker service instance
	 *
	 * @var ENNU_Biomarker_Service
	 */
	private $biomarker_service;
	
	/**
	 * Security validator instance
	 *
	 * @var ENNU_Security_Validator
	 */
	private $security_validator;
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->assessment_service = new ENNU_Assessment_Service();
		$this->biomarker_service = new ENNU_Biomarker_Service();
		
		// Initialize AJAX handlers
		$this->init();
	}
	
	/**
	 * Initialize AJAX handlers
	 */
	public function init() {
		// Register all AJAX actions
		$this->register_ajax_actions();
		
		// Initialize security
		$this->init_security();
		
		error_log( 'ENNU AJAX Service Handler: Initialized successfully' );
	}
	
	/**
	 * Initialize security measures
	 */
	private function init_security() {
		// Set up CSRF protection
		if ( class_exists( 'ENNU_CSRF_Protection' ) ) {
			ENNU_CSRF_Protection::get_instance();
		}
		
		// Set up rate limiting
		if ( class_exists( 'ENNU_Security_Validator' ) ) {
			$this->security_validator = ENNU_Security_Validator::get_instance();
		}
		
		error_log( 'ENNU AJAX Service Handler: Security initialized' );
	}
	
	/**
	 * Register all AJAX actions
	 */
	private function register_ajax_actions() {
		// DISABLED: Assessment submission - conflicts with main handler in class-assessment-shortcodes.php
		// This was causing duplicate data storage with assessment prefixes
		/*
		add_action( 'wp_ajax_ennu_submit_assessment', array( $this, 'handle_assessment_submission' ) );
		add_action( 'wp_ajax_nopriv_ennu_submit_assessment', array( $this, 'handle_assessment_submission' ) );
		*/
		
		// Assessment results
		add_action( 'wp_ajax_ennu_get_results', array( $this, 'handle_get_results' ) );
		add_action( 'wp_ajax_nopriv_ennu_get_results', array( $this, 'handle_get_results' ) );
		
		// Progress saving
		add_action( 'wp_ajax_ennu_save_assessment_progress', array( $this, 'handle_save_progress' ) );
		add_action( 'wp_ajax_nopriv_ennu_save_assessment_progress', array( $this, 'handle_save_progress' ) );

		// Debug test actions
		add_action( 'wp_ajax_test_manual_save', array( $this, 'test_manual_save' ) );
		add_action( 'wp_ajax_test_scoring_system', array( $this, 'test_scoring_system' ) );
		
		error_log( 'ENNU AJAX Service Handler: Registered AJAX actions (assessment submission disabled to prevent conflicts)' );
	}
	
	/**
	 * Create assessment AJAX handler
	 */
	public function create_assessment() {
		// Verify nonce
		if ( ! $this->verify_nonce( 'ennu_assessment_nonce' ) ) {
			$this->send_error( 'Invalid security token' );
		}
		
		// Get and sanitize data
		$assessment_data = array(
			'user_id' => $this->get_current_user_id(),
			'assessment_type' => sanitize_text_field( $_POST['assessment_type'] ?? '' ),
			'biomarkers' => isset( $_POST['biomarkers'] ) ? $this->sanitize_biomarkers( $_POST['biomarkers'] ) : array(),
			'symptoms' => isset( $_POST['symptoms'] ) ? $this->sanitize_symptoms( $_POST['symptoms'] ) : array(),
			'goals' => isset( $_POST['goals'] ) ? $this->sanitize_goals( $_POST['goals'] ) : array(),
		);
		
		// Create assessment
		$result = $this->assessment_service->create_assessment( $assessment_data );
		
		if ( $result['success'] ) {
			$this->send_success( array(
				'assessment_id' => $result['id'],
				'message' => 'Assessment created successfully',
			) );
		} else {
			$this->send_error( 'Failed to create assessment', $result['errors'] );
		}
	}
	
	/**
	 * Get assessment AJAX handler
	 */
	public function get_assessment() {
		// Verify nonce
		if ( ! $this->verify_nonce( 'ennu_assessment_nonce' ) ) {
			$this->send_error( 'Invalid security token' );
		}
		
		$assessment_id = intval( $_POST['assessment_id'] ?? 0 );
		
		if ( ! $assessment_id ) {
			$this->send_error( 'Invalid assessment ID' );
		}
		
		$assessment = $this->assessment_service->get_assessment( $assessment_id );
		
		if ( ! $assessment ) {
			$this->send_error( 'Assessment not found' );
		}
		
		// Check if user can access this assessment
		if ( $assessment['user_id'] !== $this->get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
			$this->send_error( 'Access denied' );
		}
		
		$this->send_success( $assessment );
	}
	
	/**
	 * Update assessment AJAX handler
	 */
	public function update_assessment() {
		// Verify nonce
		if ( ! $this->verify_nonce( 'ennu_assessment_nonce' ) ) {
			$this->send_error( 'Invalid security token' );
		}
		
		$assessment_id = intval( $_POST['assessment_id'] ?? 0 );
		
		if ( ! $assessment_id ) {
			$this->send_error( 'Invalid assessment ID' );
		}
		
		// Get update data
		$update_data = array();
		
		if ( isset( $_POST['biomarkers'] ) ) {
			$update_data['biomarkers'] = $this->sanitize_biomarkers( $_POST['biomarkers'] );
		}
		
		if ( isset( $_POST['symptoms'] ) ) {
			$update_data['symptoms'] = $this->sanitize_symptoms( $_POST['symptoms'] );
		}
		
		if ( isset( $_POST['goals'] ) ) {
			$update_data['goals'] = $this->sanitize_goals( $_POST['goals'] );
		}
		
		if ( isset( $_POST['status'] ) ) {
			$update_data['status'] = sanitize_text_field( $_POST['status'] );
		}
		
		$result = $this->assessment_service->update_assessment( $assessment_id, $update_data );
		
		if ( $result['success'] ) {
			$this->send_success( array(
				'message' => 'Assessment updated successfully',
			) );
		} else {
			$this->send_error( 'Failed to update assessment', $result['errors'] );
		}
	}
	
	/**
	 * Delete assessment AJAX handler
	 */
	public function delete_assessment() {
		// Verify nonce
		if ( ! $this->verify_nonce( 'ennu_assessment_nonce' ) ) {
			$this->send_error( 'Invalid security token' );
		}
		
		$assessment_id = intval( $_POST['assessment_id'] ?? 0 );
		
		if ( ! $assessment_id ) {
			$this->send_error( 'Invalid assessment ID' );
		}
		
		$result = $this->assessment_service->delete_assessment( $assessment_id );
		
		if ( $result['success'] ) {
			$this->send_success( array(
				'message' => 'Assessment deleted successfully',
			) );
		} else {
			$this->send_error( 'Failed to delete assessment', $result['errors'] );
		}
	}
	
	/**
	 * Get user assessments AJAX handler
	 */
	public function get_user_assessments() {
		// Verify nonce
		if ( ! $this->verify_nonce( 'ennu_assessment_nonce' ) ) {
			$this->send_error( 'Invalid security token' );
		}
		
		$user_id = $this->get_current_user_id();
		$assessments = $this->assessment_service->get_user_assessments( $user_id );
		
		$this->send_success( $assessments );
	}
	
	/**
	 * Save biomarker AJAX handler
	 */
	public function save_biomarker() {
		// Verify nonce
		if ( ! $this->verify_nonce( 'ennu_biomarker_nonce' ) ) {
			$this->send_error( 'Invalid security token' );
		}
		
		// Get and sanitize biomarker data
		$biomarker_data = array(
			'user_id' => $this->get_current_user_id(),
			'name' => sanitize_text_field( $_POST['name'] ?? '' ),
			'value' => sanitize_text_field( $_POST['value'] ?? '' ),
			'unit' => sanitize_text_field( $_POST['unit'] ?? '' ),
			'reference_range' => sanitize_text_field( $_POST['reference_range'] ?? '' ),
			'category' => sanitize_text_field( $_POST['category'] ?? '' ),
		);
		
		$result = $this->biomarker_service->save_biomarker( $biomarker_data );
		
		if ( $result['success'] ) {
			$this->send_success( array(
				'biomarker_id' => $result['id'],
				'message' => 'Biomarker saved successfully',
			) );
		} else {
			$this->send_error( 'Failed to save biomarker', $result['errors'] );
		}
	}
	
	/**
	 * Get biomarker AJAX handler
	 */
	public function get_biomarker() {
		// Verify nonce
		if ( ! $this->verify_nonce( 'ennu_biomarker_nonce' ) ) {
			$this->send_error( 'Invalid security token' );
		}
		
		$biomarker_id = intval( $_POST['biomarker_id'] ?? 0 );
		
		if ( ! $biomarker_id ) {
			$this->send_error( 'Invalid biomarker ID' );
		}
		
		$biomarker = $this->biomarker_service->get_biomarker( $biomarker_id );
		
		if ( ! $biomarker ) {
			$this->send_error( 'Biomarker not found' );
		}
		
		// Check if user can access this biomarker
		if ( $biomarker['user_id'] !== $this->get_current_user_id() && ! current_user_can( 'manage_options' ) ) {
			$this->send_error( 'Access denied' );
		}
		
		$this->send_success( $biomarker );
	}
	
	/**
	 * Update biomarker AJAX handler
	 */
	public function update_biomarker() {
		// Verify nonce
		if ( ! $this->verify_nonce( 'ennu_biomarker_nonce' ) ) {
			$this->send_error( 'Invalid security token' );
		}
		
		$biomarker_id = intval( $_POST['biomarker_id'] ?? 0 );
		
		if ( ! $biomarker_id ) {
			$this->send_error( 'Invalid biomarker ID' );
		}
		
		// Get update data
		$update_data = array();
		
		if ( isset( $_POST['name'] ) ) {
			$update_data['name'] = sanitize_text_field( $_POST['name'] );
		}
		
		if ( isset( $_POST['value'] ) ) {
			$update_data['value'] = sanitize_text_field( $_POST['value'] );
		}
		
		if ( isset( $_POST['unit'] ) ) {
			$update_data['unit'] = sanitize_text_field( $_POST['unit'] );
		}
		
		if ( isset( $_POST['reference_range'] ) ) {
			$update_data['reference_range'] = sanitize_text_field( $_POST['reference_range'] );
		}
		
		if ( isset( $_POST['category'] ) ) {
			$update_data['category'] = sanitize_text_field( $_POST['category'] );
		}
		
		$result = $this->biomarker_service->update_biomarker( $biomarker_id, $update_data );
		
		if ( $result['success'] ) {
			$this->send_success( array(
				'message' => 'Biomarker updated successfully',
			) );
		} else {
			$this->send_error( 'Failed to update biomarker', $result['errors'] );
		}
	}
	
	/**
	 * Delete biomarker AJAX handler
	 */
	public function delete_biomarker() {
		// Verify nonce
		if ( ! $this->verify_nonce( 'ennu_biomarker_nonce' ) ) {
			$this->send_error( 'Invalid security token' );
		}
		
		$biomarker_id = intval( $_POST['biomarker_id'] ?? 0 );
		
		if ( ! $biomarker_id ) {
			$this->send_error( 'Invalid biomarker ID' );
		}
		
		$result = $this->biomarker_service->delete_biomarker( $biomarker_id );
		
		if ( $result['success'] ) {
			$this->send_success( array(
				'message' => 'Biomarker deleted successfully',
			) );
		} else {
			$this->send_error( 'Failed to delete biomarker', $result['errors'] );
		}
	}
	
	/**
	 * Get user biomarkers AJAX handler
	 */
	public function get_user_biomarkers() {
		// Verify nonce
		if ( ! $this->verify_nonce( 'ennu_biomarker_nonce' ) ) {
			$this->send_error( 'Invalid security token' );
		}
		
		$user_id = $this->get_current_user_id();
		$biomarkers = $this->biomarker_service->get_user_biomarkers( $user_id );
		
		$this->send_success( $biomarkers );
	}
	
	/**
	 * Calculate scores AJAX handler
	 */
	public function calculate_scores() {
		// Verify nonce
		if ( ! $this->verify_nonce( 'ennu_assessment_nonce' ) ) {
			$this->send_error( 'Invalid security token' );
		}
		
		$assessment_id = intval( $_POST['assessment_id'] ?? 0 );
		
		if ( ! $assessment_id ) {
			$this->send_error( 'Invalid assessment ID' );
		}
		
		$result = $this->assessment_service->calculate_assessment_scores( $assessment_id );
		
		if ( $result['success'] ) {
			$this->send_success( array(
				'scores' => $result['scores'],
				'message' => 'Scores calculated successfully',
			) );
		} else {
			$this->send_error( 'Failed to calculate scores', $result['errors'] );
		}
	}
	
	/**
	 * Get assessment statistics AJAX handler
	 */
	public function get_assessment_statistics() {
		// Verify nonce
		if ( ! $this->verify_nonce( 'ennu_assessment_nonce' ) ) {
			$this->send_error( 'Invalid security token' );
		}
		
		$user_id = $this->get_current_user_id();
		$statistics = $this->assessment_service->get_assessment_statistics( $user_id );
		
		$this->send_success( $statistics );
	}
	
	/**
	 * Verify nonce
	 *
	 * @param string $nonce_key Nonce key.
	 * @return bool True if nonce is valid.
	 */
	private function verify_nonce( $nonce_key ) {
		$nonce = $_POST[ $nonce_key ] ?? '';
		return wp_verify_nonce( $nonce, $nonce_key );
	}
	
	/**
	 * Get current user ID
	 *
	 * @return int User ID.
	 */
	private function get_current_user_id() {
		return get_current_user_id();
	}
	
	/**
	 * Sanitize biomarkers data
	 *
	 * @param array $biomarkers Raw biomarkers data.
	 * @return array Sanitized biomarkers data.
	 */
	private function sanitize_biomarkers( $biomarkers ) {
		if ( ! is_array( $biomarkers ) ) {
			return array();
		}
		
		$sanitized = array();
		
		foreach ( $biomarkers as $biomarker ) {
			if ( is_array( $biomarker ) ) {
				$sanitized[] = array(
					'name' => sanitize_text_field( $biomarker['name'] ?? '' ),
					'value' => sanitize_text_field( $biomarker['value'] ?? '' ),
					'unit' => sanitize_text_field( $biomarker['unit'] ?? '' ),
					'reference_range' => sanitize_text_field( $biomarker['reference_range'] ?? '' ),
					'category' => sanitize_text_field( $biomarker['category'] ?? '' ),
				);
			}
		}
		
		return $sanitized;
	}
	
	/**
	 * Sanitize symptoms data
	 *
	 * @param array $symptoms Raw symptoms data.
	 * @return array Sanitized symptoms data.
	 */
	private function sanitize_symptoms( $symptoms ) {
		if ( ! is_array( $symptoms ) ) {
			return array();
		}
		
		$sanitized = array();
		
		foreach ( $symptoms as $symptom => $present ) {
			$sanitized[ sanitize_text_field( $symptom ) ] = (bool) $present;
		}
		
		return $sanitized;
	}
	
	/**
	 * Sanitize goals data
	 *
	 * @param array $goals Raw goals data.
	 * @return array Sanitized goals data.
	 */
	private function sanitize_goals( $goals ) {
		if ( ! is_array( $goals ) ) {
			return array();
		}
		
		$sanitized = array();
		
		foreach ( $goals as $goal ) {
			if ( is_array( $goal ) ) {
				$sanitized[] = array(
					'name' => sanitize_text_field( $goal['name'] ?? '' ),
					'description' => sanitize_textarea_field( $goal['description'] ?? '' ),
					'priority' => intval( $goal['priority'] ?? 1 ),
				);
			}
		}
		
		return $sanitized;
	}
	
	/**
	 * Send success response
	 *
	 * @param mixed $data Response data.
	 */
	private function send_success( $data ) {
		wp_send_json_success( $data );
	}
	
	/**
	 * Send error response
	 *
	 * @param string $message Error message.
	 * @param array  $errors Additional errors.
	 */
	private function send_error( $message, $errors = array() ) {
		$response = array(
			'message' => $message,
		);
		
		if ( ! empty( $errors ) ) {
			$response['errors'] = $errors;
		}
		
		wp_send_json_error( $response );
	}

	/**
	 * Handle assessment form submission via AJAX
	 */
	public function handle_assessment_submission() {
		error_log( 'ENNU AJAX Service Handler: Assessment submission started' );

		try {
			// 1. Verify nonce
			check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

			// 2. Get and sanitize form data
			$form_data = array();
			
			// Handle JSON responses from modern JavaScript form
			if ( isset( $_POST['responses'] ) && ! empty( $_POST['responses'] ) ) {
				$responses = json_decode( stripslashes( $_POST['responses'] ), true );
				if ( is_array( $responses ) ) {
					$form_data = $responses;
				}
			}
			
			// Also handle traditional form fields
			foreach ( $_POST as $key => $value ) {
				if ( $key !== 'action' && $key !== 'nonce' && $key !== 'responses' ) {
					$form_data[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
				}
			}

			if ( empty( $form_data ) ) {
				$this->send_error( 'No form data received' );
				return;
			}

			// 3. Validate assessment type
			if ( empty( $form_data['assessment_type'] ) ) {
				$this->send_error( 'Assessment type is required' );
				return;
			}

			// 4. Process user
			$user_id = get_current_user_id();
			
			if ( ! $user_id ) {
				$email = sanitize_email( $form_data['email'] ?? '' );
				
				if ( empty( $email ) ) {
					$this->send_error( 'Email is required for new users' );
					return;
				}

				// Check if user already exists
				$existing_user = get_user_by( 'email', $email );
				
				if ( $existing_user ) {
					// User exists, log them in
					wp_set_current_user( $existing_user->ID );
					wp_set_auth_cookie( $existing_user->ID );
					$user_id = $existing_user->ID;
				} else {
					// Create new user and log them in
					$password = wp_generate_password();
					$user_data = array(
						'user_login' => $email,
						'user_email' => $email,
						'user_pass'  => $password,
						'first_name' => $form_data['first_name'] ?? '',
						'last_name'  => $form_data['last_name'] ?? '',
					);
					
					$user_id = wp_insert_user( $user_data );
					
				if ( is_wp_error( $user_id ) ) {
					$this->send_error( $user_id->get_error_message() );
					return;
					}
					
					// Log the new user in
					wp_set_current_user( $user_id );
					wp_set_auth_cookie( $user_id );
				}
			}

			// 5. Save assessment data using the enhanced database system
			$data_manager = new ENNU_Data_Manager();
			$save_result = $data_manager->save_assessment_data( 
				$user_id, 
				$form_data['assessment_type'],
				$form_data 
			);
			
			if ( ! $save_result || ! $save_result->is_success() ) {
				$error_message = 'Failed to save assessment data';
				if ( $save_result && ! $save_result->is_success() ) {
					$error_message = $save_result->get_error_message();
				}
				$this->send_error( $error_message );
				return;
			}

			// 5.5. Process global fields
			if ( class_exists( 'ENNU_Global_Fields_Processor' ) ) {
				ENNU_Global_Fields_Processor::process_form_data( $user_id, $form_data );
			}

			// 6. Generate results token and store data
			$token = null;
			if ( $this->is_quantitative_assessment( $form_data['assessment_type'] ) ) {
				$token = $this->generate_results_token( $user_id, $form_data );
				error_log( 'ENNU REDIRECT DEBUG: Generated token: ' . $token );
			}

			// 7. Generate redirect URL using the correct logic from assessment shortcodes
			$assessment_shortcodes = new ENNU_Assessment_Shortcodes();
			$redirect_url = $assessment_shortcodes->get_thank_you_url( $form_data['assessment_type'], $token );

			// 8. Send success response
			$this->send_success( array(
				'message' => 'Assessment submitted successfully',
				'redirect_url' => $redirect_url,
				'user_id' => $user_id,
				'assessment_type' => $form_data['assessment_type'],
				'results_token' => $token
			) );

		} catch ( Exception $e ) {
			error_log( 'ENNU AJAX Service Handler Error: ' . $e->getMessage() );
			$this->send_error( 'An error occurred while processing your submission' );
		}
	}

	/**
	 * Handle getting assessment results
	 */
	public function handle_get_results() {
		$this->send_error( 'Method not implemented' );
	}

	/**
	 * Handle saving assessment progress
	 */
	public function handle_save_progress() {
		$this->send_error( 'Method not implemented' );
	}

	/**
	 * Test manual data save (for debugging)
	 */
	public function test_manual_save() {
		error_log( 'ENNU AJAX Service Handler: Testing manual data save' );

		try {
			// Get test data
			$user_id = absint( $_POST['user_id'] ?? 0 );
			$assessment_type = sanitize_text_field( $_POST['assessment_type'] ?? '' );
			$test_data = json_decode( stripslashes( $_POST['test_data'] ?? '{}' ), true );

			if ( ! $user_id || ! $assessment_type || empty( $test_data ) ) {
				$this->send_error( 'Missing required parameters' );
				return;
			}

			// Test data manager
			$data_manager = new ENNU_Data_Manager();
			$save_result = $data_manager->save_assessment_data( 
				$user_id, 
				$assessment_type,
				$test_data 
			);

			if ( ! $save_result || is_wp_error( $save_result ) ) {
				$this->send_error( 'Failed to save assessment data: ' . ( is_wp_error( $save_result ) ? $save_result->get_error_message() : 'Unknown error' ) );
				return;
			}

			// Check if data was actually saved
			$saved_score = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_calculated_score', true );
			$saved_data = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_q1', true );

			$this->send_success( array(
				'message' => 'Manual save test completed',
				'saved_score' => $saved_score,
				'saved_data' => $saved_data,
				'user_id' => $user_id,
				'assessment_type' => $assessment_type
			) );

		} catch ( Exception $e ) {
			error_log( 'ENNU AJAX Service Handler Error: ' . $e->getMessage() );
			$this->send_error( 'An error occurred during manual save test: ' . $e->getMessage() );
		}
	}

	/**
	 * Test scoring system (for debugging)
	 */
	public function test_scoring_system() {
		error_log( 'ENNU AJAX Service Handler: Testing scoring system' );

		try {
			$assessment_type = sanitize_text_field( $_POST['assessment_type'] ?? '' );
			$test_data = json_decode( stripslashes( $_POST['test_data'] ?? '{}' ), true );

			if ( ! $assessment_type || empty( $test_data ) ) {
				$this->send_error( 'Missing required parameters' );
				return;
			}

			// Test scoring system
			if ( ! class_exists( 'ENNU_Scoring_System' ) ) {
				$this->send_error( 'ENNU_Scoring_System class not found' );
				return;
			}

			$scores = ENNU_Scoring_System::calculate_scores_for_assessment( $assessment_type, $test_data );

			if ( ! $scores ) {
				$this->send_error( 'Scoring system returned null/empty result' );
				return;
			}

			$this->send_success( array(
				'message' => 'Scoring system test completed',
				'scores' => $scores,
				'assessment_type' => $assessment_type,
				'test_data' => $test_data
			) );

		} catch ( Exception $e ) {
			error_log( 'ENNU AJAX Service Handler Error: ' . $e->getMessage() );
			$this->send_error( 'An error occurred during scoring test: ' . $e->getMessage() );
		}
	}

	/**
	 * Check if assessment is quantitative (generates results)
	 */
	private function is_quantitative_assessment( $assessment_type ) {
		$quantitative_assessments = array(
			'hair',
			'weight-loss',
			'health-optimization',
			'hormone',
			'menopause',
			'testosterone',
			'sleep',
			'skin',
			'ed-treatment',
			'health'
		);
		
		return in_array( $assessment_type, $quantitative_assessments );
	}

	/**
	 * Generate results token and store assessment data
	 */
	private function generate_results_token( $user_id, $form_data ) {
		// Generate unique token
		$token = wp_generate_password( 32, false );
		
		// Calculate assessment scores
		$scores = $this->calculate_assessment_scores( $form_data );
		
		// Prepare results data
		$results_data = array(
			'user_id' => $user_id,
			'assessment_type' => $form_data['assessment_type'],
			'score' => $scores['total_score'],
			'category_scores' => $scores['category_scores'],
			'interpretation' => $scores['interpretation'],
			'answers' => $form_data,
			'timestamp' => current_time( 'timestamp' )
		);
		
		// Store in transient (expires in 1 hour)
		$transient_key = 'ennu_results_' . $token;
		set_transient( $transient_key, $results_data, 3600 );
		
		error_log( 'ENNU REDIRECT DEBUG: Stored results data with token: ' . $token );
		
		return $token;
	}

	/**
	 * Calculate assessment scores
	 */
	private function calculate_assessment_scores( $form_data ) {
		$assessment_type = $form_data['assessment_type'];
		$answers = $form_data;
		
		// Basic scoring logic - can be enhanced based on assessment type
		$total_score = 0;
		$category_scores = array();
		
		// Calculate scores based on answers
		foreach ( $answers as $key => $value ) {
			if ( strpos( $key, 'question_' ) === 0 || strpos( $key, '_' ) !== false ) {
				// Simple scoring: convert answers to numeric values
				$score = $this->convert_answer_to_score( $value );
				$total_score += $score;
				
				// Categorize scores
				$category = $this->get_answer_category( $key );
				if ( ! isset( $category_scores[ $category ] ) ) {
					$category_scores[ $category ] = 0;
				}
				$category_scores[ $category ] += $score;
			}
		}
		
		// Normalize total score to 0-100 range
		$total_score = min( 100, max( 0, $total_score ) );
		
		// Determine interpretation
		$interpretation = $this->get_score_interpretation( $total_score );
		
		return array(
			'total_score' => $total_score,
			'category_scores' => $category_scores,
			'interpretation' => $interpretation
		);
	}

	/**
	 * Convert answer to numeric score
	 */
	private function convert_answer_to_score( $answer ) {
		$score_map = array(
			'yes' => 10,
			'no' => 0,
			'good' => 8,
			'fair' => 5,
			'poor' => 2,
			'frequently' => 8,
			'sometimes' => 5,
			'rarely' => 2,
			'never' => 0,
			'high' => 8,
			'moderate' => 5,
			'low' => 2
		);
		
		return isset( $score_map[ strtolower( $answer ) ] ) ? $score_map[ strtolower( $answer ) ] : 5;
	}

	/**
	 * Get answer category
	 */
	private function get_answer_category( $question_key ) {
		if ( strpos( $question_key, 'stress' ) !== false ) return 'lifestyle';
		if ( strpos( $question_key, 'diet' ) !== false ) return 'nutrition';
		if ( strpos( $question_key, 'sleep' ) !== false ) return 'lifestyle';
		if ( strpos( $question_key, 'exercise' ) !== false ) return 'fitness';
		if ( strpos( $question_key, 'family' ) !== false ) return 'genetics';
		
		return 'general';
	}

	/**
	 * Get score interpretation
	 */
	private function get_score_interpretation( $score ) {
		if ( $score >= 80 ) {
			return array( 'level' => 'Excellent', 'color' => '#28a745' );
		} elseif ( $score >= 60 ) {
			return array( 'level' => 'Good', 'color' => '#17a2b8' );
		} elseif ( $score >= 40 ) {
			return array( 'level' => 'Fair', 'color' => '#ffc107' );
		} else {
			return array( 'level' => 'Poor', 'color' => '#dc3545' );
		}
	}
} 