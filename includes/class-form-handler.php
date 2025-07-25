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
				return ENNU_Form_Result::error( 'validation_failed', $validation_result->get_errors() );
			}

			// 2. Sanitize data
			$sanitized_data = $this->sanitizer->sanitize( $form_data );

			// 3. Process user
			$user_result = $this->process_user( $sanitized_data );
			if ( ! $user_result->is_success() ) {
				return $user_result;
			}

			// 4. Save data
			$save_result = $this->persistence->save_assessment_data( 
				$user_result->get_user_id(), 
				$sanitized_data 
			);
			if ( ! $save_result->is_success() ) {
				return $save_result;
			}

			// 5. Send notifications
			$this->notifications->send_assessment_notification( $sanitized_data );

			// 6. Return success
			return ENNU_Form_Result::success( array(
				'user_id' => $user_result->get_user_id(),
				'assessment_type' => $sanitized_data['assessment_type'],
				'saved_fields' => $save_result->get_saved_fields()
			) );

		} catch ( Exception $e ) {
			$this->logger->log_error( 'Form processing failed', $e->getMessage() );
			return ENNU_Form_Result::error( 'processing_failed', $e->getMessage() );
		}
	}

	/**
	 * Process user creation or retrieval
	 */
	private function process_user( $form_data ) {
		$email = $form_data['email'];
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
	 * Create new user account
	 */
	private function create_user( $form_data ) {
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
			return ENNU_Form_Result::error( 'user_creation_failed', $user_id->get_error_message() );
		}

		// Log the new user in
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );

		$this->logger->log( 'New user created', array( 'user_id' => $user_id ) );
		return ENNU_Form_Result::success( array( 'user_id' => $user_id ) );
	}

	/**
	 * Handle existing user login
	 */
	private function handle_existing_user( $user_id, $form_data ) {
		// Check if user is logged in
		if ( ! is_user_logged_in() || get_current_user_id() != $user_id ) {
			$login_url = wp_login_url( get_permalink() );
			return ENNU_Form_Result::error( 'login_required', 
				'An account with this email already exists. Please <a href="' . esc_url( $login_url ) . '">log in</a> to continue.' 
			);
		}

		return ENNU_Form_Result::success( array( 'user_id' => $user_id ) );
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
			$valid_types = array( 'health', 'weight_loss', 'hormone', 'sleep', 'skin', 'hair' );
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