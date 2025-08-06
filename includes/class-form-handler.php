<?php
/**
 * ENNU Form Handler
 * Dedicated class for handling assessment form processing
 * 
 * @package ENNU_Life_Assessments
 * @version 62.28.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all assessment form processing with clean separation of concerns
 */
class ENNU_Form_Handler {

	private $validator;
	private $sanitizer;
	private $persistence;
	private $notifications;
	private $logger;

	public function __construct() {
		$this->validator = new ENNU_Form_Validator();
		$this->sanitizer = new ENNU_Form_Sanitizer();
		$this->persistence = new ENNU_Data_Persistence();
		$this->notifications = new ENNU_Notification_Manager();
		$this->logger = new ENNU_Logger();
	}

	/**
	 * Process assessment form submission with full error handling
	 * Enhanced with legacy scoring and assessment engine logic
	 *
	 * @param array $form_data Raw form data
	 * @return ENNU_Form_Result Result object with success/error status
	 */
	public function process_submission( $form_data ) {
		$this->logger->log( 'Form submission started', $form_data );

		try {
			// 1. Validate input
			$validation_result = $this->validator->validate( $form_data );
			if ( ! $validation_result->is_valid() ) {
				$error_handler = ENNU_Error_Handler::get_instance();
				$error_response = $error_handler->handle_form_error( 'Validation failed', array( 'errors' => $validation_result->get_errors() ) );
				return ENNU_Form_Result::error( 'validation_failed', $error_response['user_message'] );
			}

			// 2. Sanitize data
			$sanitized_data = $this->sanitizer->sanitize( $form_data );

			// 3. Process user
			$user_result = $this->process_user( $sanitized_data );
			if ( ! $user_result->is_success() ) {
				$error_handler = ENNU_Error_Handler::get_instance();
				$error_response = $error_handler->handle_form_error( $user_result->get_error_message(), array( 'user_creation' => true ) );
				return ENNU_Form_Result::error( $user_result->get_error_code(), $error_response['user_message'] );
			}

			$user_id = $user_result->get_user_id();

			// 4. Save data using modern Data Manager
			$data_manager = new ENNU_Data_Manager();
			$save_result = $data_manager->save_assessment_data( 
				$user_id, 
				$sanitized_data['assessment_type'],
				$sanitized_data 
			);
			if ( ! $save_result->is_success() ) {
				$error_handler = ENNU_Error_Handler::get_instance();
				$error_response = $error_handler->handle_form_error( $save_result->get_error_message(), array( 'data_saving' => true ) );
				return ENNU_Form_Result::error( 'data_save_failed', $error_response['user_message'] );
			}

			// 5. Route to assessment engine (quantitative vs qualitative)
			$engine_result = $this->route_to_assessment_engine( $user_id, $sanitized_data );
			if ( ! $engine_result->is_success() ) {
				$error_handler = ENNU_Error_Handler::get_instance();
				$error_response = $error_handler->handle_form_error( $engine_result->get_error_message(), array( 'assessment_engine' => true ) );
				return ENNU_Form_Result::error( 'engine_failed', $error_response['user_message'] );
			}

			// 6. Send notifications
			$this->notifications->send_assessment_notification( $sanitized_data );

			// 7. Return success with engine-specific data
			return ENNU_Form_Result::success( array_merge(
				array(
					'user_id' => $user_id,
					'assessment_type' => $sanitized_data['assessment_type'],
					'saved_fields' => $save_result->get_saved_fields()
				),
				$engine_result->get_data()
			) );

		} catch ( Exception $e ) {
			$error_handler = ENNU_Error_Handler::get_instance();
			$error_response = $error_handler->handle_form_error( $e, array( 'form_processing' => true ) );
			$this->logger->log_error( 'Form processing failed', $e->getMessage() );
			return ENNU_Form_Result::error( 'processing_failed', $error_response['user_message'] );
		}
	}

	/**
	 * Route to appropriate assessment engine (quantitative vs qualitative)
	 * Migrated from legacy ENNU_Assessment_Shortcodes class
	 */
	private function route_to_assessment_engine( $user_id, $form_data ) {
		// Convert assessment type to match config file naming convention
		$config_assessment_type = $form_data['assessment_type'];
		if ( $form_data['assessment_type'] === 'health_optimization_assessment' ) {
			$config_assessment_type = 'health-optimization';
		}

		// Get assessment config from scoring system
		$all_definitions = array();
		if ( class_exists( 'ENNU_Scoring_System' ) ) {
			$all_definitions = ENNU_Scoring_System::get_all_definitions();
		}
		
		$assessment_config = $all_definitions[ $config_assessment_type ] ?? array();
		$assessment_engine = $assessment_config['assessment_engine'] ?? 'quantitative';

		if ( $assessment_engine === 'qualitative' ) {
			return $this->process_qualitative_assessment( $user_id, $form_data );
		} else {
			return $this->process_quantitative_assessment( $user_id, $form_data );
		}
	}

	/**
	 * Process quantitative assessment with scoring
	 * Migrated from legacy ENNU_Assessment_Shortcodes class
	 */
	private function process_quantitative_assessment( $user_id, $form_data ) {
		// Calculate scores using scoring system
		$scores = array();
		if ( class_exists( 'ENNU_Scoring_System' ) ) {
			$scores = ENNU_Scoring_System::calculate_scores_for_assessment( $form_data['assessment_type'], $form_data );
			
			// Calculate pillar scores from category scores
			if ( $scores && isset( $scores['category_scores'] ) ) {
				// Use reflection to call the private method map_categories_to_pillars
				$reflection = new ReflectionClass( 'ENNU_Scoring_System' );
				$method = $reflection->getMethod( 'map_categories_to_pillars' );
				$method->setAccessible( true );
				
				$pillar_scores = $method->invoke( null, $form_data['assessment_type'], $scores['category_scores'] );
				$scores['pillar_scores'] = $pillar_scores;
			}
		}

		if ( $scores ) {
			$completion_time = date( 'Y-m-d H:i:s.u' );

			// Save the scores for this specific assessment
			update_user_meta( $user_id, 'ennu_' . $form_data['assessment_type'] . '_score_calculated_at', $completion_time );
			$canonical_assessment_type = ENNU_Assessment_Constants::get_canonical_key( $form_data['assessment_type'] );
		update_user_meta( $user_id, ENNU_Assessment_Constants::get_full_meta_key( $canonical_assessment_type, 'calculated_score' ), $scores['overall_score'] );
			update_user_meta( $user_id, 'ennu_' . $form_data['assessment_type'] . '_score_interpretation', ENNU_Scoring_System::get_score_interpretation( $scores['overall_score'] ) );
			update_user_meta( $user_id, 'ennu_' . $form_data['assessment_type'] . '_category_scores', $scores['category_scores'] );
			update_user_meta( $user_id, 'ennu_' . $form_data['assessment_type'] . '_pillar_scores', $scores['pillar_scores'] );
			
			// Recalculate average pillar scores across all assessments
			ENNU_Scoring_System::calculate_average_pillar_scores( $user_id );

			// Save score history for progress tracking
			$score_history_key = 'ennu_' . $form_data['assessment_type'] . '_historical_scores';
			$score_history = get_user_meta( $user_id, $score_history_key, true );
			if ( ! is_array( $score_history ) ) {
				$score_history = array();
			}
			$score_history[] = array(
				'date'  => $completion_time,
				'score' => $scores['overall_score'],
				'level' => ENNU_Scoring_System::get_score_interpretation( $scores['overall_score'] ),
			);
			update_user_meta( $user_id, $score_history_key, $score_history );

			// Update centralized symptoms
			if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
				ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $user_id, $form_data['assessment_type'] );
			}

			// Trigger assessment completion hook
			do_action( 'ennu_assessment_completed', $user_id, $form_data['assessment_type'] );

			return ENNU_Form_Result::success( array(
				'engine_type' => 'quantitative',
				'scores' => $scores,
				'completion_time' => $completion_time
			) );
		}

		return ENNU_Form_Result::error( 'scoring_failed', 'Failed to calculate assessment scores' );
	}

	/**
	 * Process qualitative assessment (no scoring)
	 * Migrated from legacy ENNU_Assessment_Shortcodes class
	 */
	private function process_qualitative_assessment( $user_id, $form_data ) {
		// For qualitative assessments, store the raw form data
		$results_token = wp_generate_password( 32, false );
		set_transient( 'ennu_results_' . $results_token, $form_data, HOUR_IN_SECONDS );

		// Update centralized symptoms for qualitative assessments too
		if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
			ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $user_id, $form_data['assessment_type'] );
		}

		// Trigger assessment completion hook
		do_action( 'ennu_assessment_completed', $user_id, $form_data['assessment_type'] );

		return ENNU_Form_Result::success( array(
			'engine_type' => 'qualitative',
			'results_token' => $results_token
		) );
	}

	/**
	 * Process user creation or retrieval
	 */
	private function process_user( $form_data ) {
		$email = sanitize_email( $form_data['email'] );
		
		// Validate email format
		if ( empty( $email ) || ! is_email( $email ) ) {
			return ENNU_Form_Result::error( 'invalid_email', 'Please enter a valid email address.' );
		}
		
		$user_id = email_exists( $email );

		if ( ! $user_id ) {
			// Create new user
			$user_result = $this->create_user( $form_data );
			if ( ! $user_result->is_success() ) {
				return $user_result;
			}
			$user_id = $user_result->get_user_id();
		} else {
			// Handle existing user
			$user_result = $this->handle_existing_user( $user_id, $form_data );
			if ( ! $user_result->is_success() ) {
				return $user_result;
			}
		}

		return ENNU_Form_Result::success( array( 'user_id' => $user_id ) );
	}

	/**
	 * Create new user account with rate limiting and duplicate prevention
	 */
	private function create_user( $form_data ) {
		// Check rate limiting
		$database = ENNU_Life_Enhanced_Database::get_instance();
		$ip_address = $this->get_client_ip();
		
		if ( ! $database->check_user_creation_rate_limit( $ip_address ) ) {
			return ENNU_Form_Result::error( 'rate_limit_exceeded', 'Too many account creation attempts. Please try again in 1 hour.' );
		}
		
		// Create database constraints if they don't exist
		$database->create_database_constraints();
		
		$password = wp_generate_password();
		$user_data = array(
			'user_login' => $form_data['email'],
			'user_email' => $form_data['email'],
			'user_pass'  => $password,
			'first_name' => $form_data['first_name'] ?? '',
			'last_name'  => $form_data['last_name'] ?? '',
		);

		$user_id = wp_insert_user( $user_data );

		if ( is_wp_error( $user_id ) ) {
			$error_message = $user_id->get_error_message();
			
			// Handle duplicate email error specifically
			if ( strpos( $error_message, 'email' ) !== false ) {
				return ENNU_Form_Result::error( 'duplicate_email', 'An account with this email already exists. Please log in instead.' );
			}
			
			return ENNU_Form_Result::error( 'user_creation_failed', $error_message );
		}

		// Log the new user in with proper session management
		$this->set_user_session( $user_id );

		$this->logger->log( 'New user created', array( 'user_id' => $user_id ) );
		return ENNU_Form_Result::success( array( 'user_id' => $user_id ) );
	}
	
	/**
	 * Get client IP address
	 */
	private function get_client_ip() {
		$ip_keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' );
		
		foreach ( $ip_keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
					$ip = trim( $ip );
					if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
						return $ip;
					}
				}
			}
		}
		
		return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
	}

	/**
	 * Handle existing user login with session management
	 */
	private function handle_existing_user( $user_id, $form_data ) {
		// Check if user is logged in
		if ( ! is_user_logged_in() || get_current_user_id() != $user_id ) {
			$login_url = wp_login_url( get_permalink() );
			return ENNU_Form_Result::error( 'login_required', 
				'An account with this email already exists. Please <a href="' . esc_url( $login_url ) . '">log in</a> to continue.' 
			);
		}

		// Enforce session timeout
		$this->enforce_session_timeout( $user_id );

		return ENNU_Form_Result::success( array( 'user_id' => $user_id ) );
	}
	
	/**
	 * Enforce session timeout based on HIPAA compliance
	 */
	private function enforce_session_timeout( $user_id ) {
		$session_start = get_user_meta( $user_id, 'ennu_session_start', true );
		$current_time = time();
		$timeout_duration = 900; // 15 minutes from HIPAA compliance
		
		// Check if session has expired
		if ( $session_start && ( $current_time - $session_start ) > $timeout_duration ) {
			// Session expired, log user out
			wp_logout();
			$this->logger->log( 'Session expired', array( 'user_id' => $user_id ) );
			return false;
		}
		
		// Update last activity
		update_user_meta( $user_id, 'ennu_last_activity', $current_time );
		return true;
	}
	
	/**
	 * Set user session with proper timeout
	 */
	private function set_user_session( $user_id ) {
		$current_time = time();
		
		// Set session metadata
		update_user_meta( $user_id, 'ennu_session_start', $current_time );
		update_user_meta( $user_id, 'ennu_last_activity', $current_time );
		
		// Set WordPress session
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, false, is_ssl() );
		
		$this->logger->log( 'User session set', array( 'user_id' => $user_id ) );
	}
}

/**
 * Form validation result object
 */
class ENNU_Form_Result {
	private $success;
	private $data;
	private $error_code;
	private $error_message;

	public static function success( $data = array() ) {
		$result = new self();
		$result->success = true;
		$result->data = $data;
		return $result;
	}

	public static function error( $code, $message ) {
		$result = new self();
		$result->success = false;
		$result->error_code = $code;
		$result->error_message = $message;
		return $result;
	}

	public function is_success() {
		return $this->success;
	}

	public function get_data() {
		return $this->data;
	}

	public function get_user_id() {
		return $this->data['user_id'] ?? null;
	}

	public function get_error_code() {
		return $this->error_code;
	}

	public function get_error_message() {
		return $this->error_message;
	}

	public function get_saved_fields() {
		return $this->data['saved_fields'] ?? array();
	}
}

/**
 * Form validator with comprehensive validation rules
 */
class ENNU_Form_Validator {
	
	public function validate( $form_data ) {
		$errors = array();

		// Required fields
		$required_fields = array( 'assessment_type', 'email' );
		foreach ( $required_fields as $field ) {
			if ( empty( $form_data[ $field ] ) ) {
				$errors[] = "Field '{$field}' is required.";
			}
		}

		// Email validation
		if ( ! empty( $form_data['email'] ) && ! is_email( $form_data['email'] ) ) {
			$errors[] = 'Please provide a valid email address.';
		}

		// Assessment type validation
		if ( ! empty( $form_data['assessment_type'] ) ) {
			$valid_types = array( 'health', 'weight_loss', 'weight-loss', 'hormone', 'sleep', 'skin', 'hair', 'ed-treatment', 'menopause', 'testosterone', 'health-optimization', 'welcome' );
			if ( ! in_array( $form_data['assessment_type'], $valid_types ) ) {
				$errors[] = 'Invalid assessment type.';
			}
		}

		if ( empty( $errors ) ) {
			return ENNU_Validation_Result::valid();
		}

		return ENNU_Validation_Result::invalid( $errors );
	}
}

/**
 * Validation result object
 */
class ENNU_Validation_Result {
	private $valid;
	private $errors;

	public static function valid() {
		$result = new self();
		$result->valid = true;
		$result->errors = array();
		return $result;
	}

	public static function invalid( $errors ) {
		$result = new self();
		$result->valid = false;
		$result->errors = $errors;
		return $result;
	}

	public function is_valid() {
		return $this->valid;
	}

	public function get_errors() {
		return $this->errors;
	}
}

/**
 * Form sanitizer for data cleaning
 */
class ENNU_Form_Sanitizer {
	
	public function sanitize( $form_data ) {
		$sanitized = array();

		foreach ( $form_data as $key => $value ) {
			$sanitized[ $key ] = $this->sanitize_field( $key, $value );
		}

		return $sanitized;
	}

	private function sanitize_field( $key, $value ) {
		// Email fields
		if ( $key === 'email' ) {
			return sanitize_email( $value );
		}

		// Text fields
		if ( in_array( $key, array( 'first_name', 'last_name', 'billing_phone' ) ) ) {
			return sanitize_text_field( $value );
		}

		// Arrays (multiselect fields)
		if ( is_array( $value ) ) {
			return array_map( 'sanitize_text_field', $value );
		}

		// Default sanitization
		return sanitize_text_field( $value );
	}
}

/**
 * Data persistence layer
 */
class ENNU_Data_Persistence {
	
	public function save_assessment_data( $user_id, $form_data ) {
		try {
			$saved_fields = array();

			// Save core user data
			$core_fields = $this->save_core_data( $user_id, $form_data );
			$saved_fields = array_merge( $saved_fields, $core_fields );

			// Save global fields
			$global_fields = $this->save_global_fields( $user_id, $form_data );
			$saved_fields = array_merge( $saved_fields, $global_fields );

			// Save assessment-specific fields
			$assessment_fields = $this->save_assessment_fields( $user_id, $form_data );
			$saved_fields = array_merge( $saved_fields, $assessment_fields );

			// Save completion timestamp
			$assessment_type = $form_data['assessment_type'];
			$completion_time = current_time( 'mysql' );
			update_user_meta( $user_id, 'ennu_' . $assessment_type . '_completed', $completion_time );
			update_user_meta( $user_id, 'ennu_' . $assessment_type . '_last_updated', $completion_time );

			return ENNU_Form_Result::success( array( 'saved_fields' => $saved_fields ) );

		} catch ( Exception $e ) {
			return ENNU_Form_Result::error( 'save_failed', $e->getMessage() );
		}
	}

	private function save_core_data( $user_id, $form_data ) {
		$saved_fields = array();

		// Update WordPress user fields
		$user_data_update = array( 'ID' => $user_id );
		if ( isset( $form_data['first_name'] ) ) {
			$user_data_update['first_name'] = $form_data['first_name'];
			$saved_fields[] = 'first_name';
		}
		if ( isset( $form_data['last_name'] ) ) {
			$user_data_update['last_name'] = $form_data['last_name'];
			$saved_fields[] = 'last_name';
		}

		if ( count( $user_data_update ) > 1 ) {
			wp_update_user( $user_data_update );
		}

		// Save to user meta
		if ( isset( $form_data['email'] ) ) {
			update_user_meta( $user_id, 'ennu_global_email', $form_data['email'] );
			$saved_fields[] = 'email';
		}
		if ( isset( $form_data['billing_phone'] ) ) {
			update_user_meta( $user_id, 'ennu_global_billing_phone', $form_data['billing_phone'] );
			$saved_fields[] = 'phone';
		}

		return $saved_fields;
	}

	private function save_global_fields( $user_id, $form_data ) {
		$saved_fields = array();
		$assessment_type = $form_data['assessment_type'];

		// Get assessment questions
		$questions = $this->get_assessment_questions( $assessment_type );

		foreach ( $questions as $question_id => $question_def ) {
			if ( ! isset( $question_def['global_key'] ) ) {
				continue;
			}

			$global_key = $question_def['global_key'];
			$meta_key = 'ennu_global_' . $global_key;

			if ( isset( $form_data[ $question_id ] ) ) {
				update_user_meta( $user_id, $meta_key, $form_data[ $question_id ] );
				$saved_fields[] = $global_key;
			}
		}

		return $saved_fields;
	}

	private function save_assessment_fields( $user_id, $form_data ) {
		$saved_fields = array();
		$assessment_type = $form_data['assessment_type'];

		// Get assessment questions
		$questions = $this->get_assessment_questions( $assessment_type );

		foreach ( $questions as $question_id => $question_def ) {
			// Skip global fields
			if ( isset( $question_def['global_key'] ) ) {
				continue;
			}

			$meta_key = 'ennu_' . $assessment_type . '_' . $question_id;

			if ( isset( $form_data[ $question_id ] ) ) {
				update_user_meta( $user_id, $meta_key, $form_data[ $question_id ] );
				$saved_fields[] = $question_id;
			}
		}

		return $saved_fields;
	}

	private function get_assessment_questions( $assessment_type ) {
		// This would load from the assessment configuration
		// For now, return empty array
		return array();
	}
}

/**
 * Notification manager for emails
 */
class ENNU_Notification_Manager {
	
	public function send_assessment_notification( $form_data ) {
		try {
			$email_data = array(
				'assessment_type' => $form_data['assessment_type'],
				'contact_name'    => trim( ( $form_data['first_name'] ?? '' ) . ' ' . ( $form_data['last_name'] ?? '' ) ),
				'contact_email'   => $form_data['email'],
				'contact_phone'   => $form_data['billing_phone'] ?? 'N/A',
				'answers'         => $this->extract_answers( $form_data ),
			);

			// Send email notification
			$this->send_email( $email_data );

		} catch ( Exception $e ) {
			error_log( 'ENNU: Email notification failed: ' . $e->getMessage() );
		}
	}

	private function extract_answers( $form_data ) {
		$answers = array();
		foreach ( $form_data as $key => $value ) {
			if ( strpos( $key, '_q' ) !== false ) {
				$answers[ $key ] = is_array( $value ) ? implode( ', ', $value ) : $value;
			}
		}
		return $answers;
	}

	private function send_email( $email_data ) {
		// Email sending logic would go here
		// For now, just log
		error_log( 'ENNU: Email notification prepared for ' . $email_data['contact_email'] );
	}
}

/**
 * Simple logger for debugging
 */
class ENNU_Logger {
	
	public function log( $message, $data = null ) {
		$log_entry = date( 'Y-m-d H:i:s' ) . ' - ' . $message;
		if ( $data ) {
			$log_entry .= ' - ' . json_encode( $data );
		}
		error_log( 'ENNU: ' . $log_entry );
	}

	public function log_error( $message, $error ) {
		error_log( 'ENNU ERROR: ' . $message . ' - ' . $error );
	}
} 