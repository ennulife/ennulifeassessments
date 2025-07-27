<?php
/**
 * ENNU AJAX Handler
 * Dedicated class for handling all AJAX requests
 * 
 * @package ENNU_Life_Assessments
 * @version 62.28.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all AJAX requests with proper security and error handling
 */
class ENNU_AJAX_Handler {

	private $form_handler;
	private $security;
	private $logger;

	public function __construct() {
		$this->form_handler = new ENNU_Form_Handler();
		$this->security = new ENNU_AJAX_Security_Handler();
		$this->logger = new ENNU_Logger();
		
		$this->init_hooks();
	}

	/**
	 * Initialize AJAX hooks
	 */
	private function init_hooks() {
		add_action( 'wp_ajax_ennu_submit_assessment', array( $this, 'handle_assessment_submission' ) );
		add_action( 'wp_ajax_nopriv_ennu_submit_assessment', array( $this, 'handle_assessment_submission' ) );
		
		add_action( 'wp_ajax_ennu_get_assessment_results', array( $this, 'handle_get_results' ) );
		add_action( 'wp_ajax_nopriv_ennu_get_assessment_results', array( $this, 'handle_get_results' ) );
		
		add_action( 'wp_ajax_ennu_save_assessment_progress', array( $this, 'handle_save_progress' ) );
		add_action( 'wp_ajax_nopriv_ennu_save_assessment_progress', array( $this, 'handle_save_progress' ) );
	}

	/**
	 * Handle assessment form submission via AJAX
	 */
	public function handle_assessment_submission() {
		$this->logger->log( 'AJAX assessment submission started' );

		try {
			// 1. Security validation
			$security_result = $this->security->validate_request( 'ennu_submit_assessment' );
			if ( ! $security_result->is_valid() ) {
				wp_send_json_error( array(
					'message' => $security_result->get_error_message(),
					'code'    => $security_result->get_error_code()
				) );
			}

			// 2. Get and validate form data
			$form_data = $this->get_form_data();
			if ( empty( $form_data ) ) {
				wp_send_json_error( array(
					'message' => 'No form data received',
					'code'    => 'no_data'
				) );
			}

			// 3. Process submission
			$result = $this->form_handler->process_submission( $form_data );

			// 4. Handle result
			if ( $result->is_success() ) {
				$response_data = $result->get_data();
				
				// Generate results token for quantitative assessments
				if ( $this->is_quantitative_assessment( $form_data['assessment_type'] ) ) {
					$token = $this->generate_results_token( $response_data['user_id'], $form_data );
					$response_data['results_token'] = $token;
				}

				wp_send_json_success( array(
					'message' => 'Assessment submitted successfully',
					'data'    => $response_data
				) );
			} else {
				wp_send_json_error( array(
					'message' => $result->get_error_message(),
					'code'    => $result->get_error_code()
				) );
			}

		} catch ( Exception $e ) {
			$this->logger->log_error( 'AJAX submission failed', $e->getMessage() );
			wp_send_json_error( array(
				'message' => 'An unexpected error occurred',
				'code'    => 'unexpected_error'
			) );
		}
	}

	/**
	 * Handle getting assessment results
	 */
	public function handle_get_results() {
		$this->logger->log( 'AJAX get results started' );

		try {
			// Security validation
			$security_result = $this->security->validate_request( 'ennu_get_assessment_results' );
			if ( ! $security_result->is_valid() ) {
				wp_send_json_error( array(
					'message' => $security_result->get_error_message(),
					'code'    => $security_result->get_error_code()
				) );
			}

			// Get token from request
			$token = sanitize_text_field( $_POST['token'] ?? '' );
			if ( empty( $token ) ) {
				wp_send_json_error( array(
					'message' => 'Results token is required',
					'code'    => 'missing_token'
				) );
			}

			// Retrieve results
			$results = $this->get_results_by_token( $token );
			if ( ! $results ) {
				wp_send_json_error( array(
					'message' => 'Results not found or expired',
					'code'    => 'results_not_found'
				) );
			}

			wp_send_json_success( array(
				'results' => $results
			) );

		} catch ( Exception $e ) {
			$this->logger->log_error( 'AJAX get results failed', $e->getMessage() );
			wp_send_json_error( array(
				'message' => 'An unexpected error occurred',
				'code'    => 'unexpected_error'
			) );
		}
	}

	/**
	 * Handle saving assessment progress
	 */
	public function handle_save_progress() {
		$this->logger->log( 'AJAX save progress started' );

		try {
			// Security validation
			$security_result = $this->security->validate_request( 'ennu_save_assessment_progress' );
			if ( ! $security_result->is_valid() ) {
				wp_send_json_error( array(
					'message' => $security_result->get_error_message(),
					'code'    => $security_result->get_error_code()
				) );
			}

			// Get progress data
			$progress_data = $this->get_progress_data();
			if ( empty( $progress_data ) ) {
				wp_send_json_error( array(
					'message' => 'No progress data received',
					'code'    => 'no_data'
				) );
			}

			// Save progress
			$result = $this->save_progress( $progress_data );
			if ( $result->is_success() ) {
				wp_send_json_success( array(
					'message' => 'Progress saved successfully'
				) );
			} else {
				wp_send_json_error( array(
					'message' => $result->get_error_message(),
					'code'    => $result->get_error_code()
				) );
			}

		} catch ( Exception $e ) {
			$this->logger->log_error( 'AJAX save progress failed', $e->getMessage() );
			wp_send_json_error( array(
				'message' => 'An unexpected error occurred',
				'code'    => 'unexpected_error'
			) );
		}
	}

	/**
	 * Get and sanitize form data from POST
	 */
	private function get_form_data() {
		$form_data = array();

		// Get all POST data
		foreach ( $_POST as $key => $value ) {
			// Skip nonce and action fields
			if ( in_array( $key, array( 'nonce', 'action' ) ) ) {
				continue;
			}

			// Sanitize based on field type
			if ( is_array( $value ) ) {
				$form_data[ $key ] = array_map( 'sanitize_text_field', $value );
			} else {
				$form_data[ $key ] = sanitize_text_field( $value );
			}
		}

		return $form_data;
	}

	/**
	 * Get progress data from POST
	 */
	private function get_progress_data() {
		$progress_data = array();

		$progress_data['assessment_type'] = sanitize_text_field( $_POST['assessment_type'] ?? '' );
		$progress_data['current_step'] = absint( $_POST['current_step'] ?? 1 );
		$progress_data['form_data'] = $this->get_form_data();

		return $progress_data;
	}

	/**
	 * Check if assessment is quantitative (generates scores)
	 */
	private function is_quantitative_assessment( $assessment_type ) {
		$quantitative_types = array( 'health', 'weight_loss', 'hormone', 'sleep', 'skin', 'hair' );
		return in_array( $assessment_type, $quantitative_types );
	}

	/**
	 * Generate results token for quantitative assessments
	 */
	private function generate_results_token( $user_id, $form_data ) {
		$token = wp_generate_password( 32, false );
		
		// Store results in transient
		$results_data = array(
			'user_id' => $user_id,
			'assessment_type' => $form_data['assessment_type'],
			'form_data' => $form_data,
			'timestamp' => current_time( 'mysql' )
		);

		set_transient( 'ennu_results_' . $token, $results_data, HOUR_IN_SECONDS );

		return $token;
	}

	/**
	 * Get results by token
	 */
	private function get_results_by_token( $token ) {
		$results_data = get_transient( 'ennu_results_' . $token );
		
		if ( ! $results_data ) {
			return false;
		}

		// Calculate scores for quantitative assessments
		$scores = $this->calculate_assessment_scores( $results_data['form_data'] );
		$results_data['scores'] = $scores;

		return $results_data;
	}

	/**
	 * Calculate assessment scores
	 */
	private function calculate_assessment_scores( $form_data ) {
		// This would contain the scoring logic
		// For now, return placeholder
		return array(
			'overall_score' => 7.5,
			'level' => 'Good',
			'color' => '#10b981'
		);
	}

	/**
	 * Save assessment progress
	 */
	private function save_progress( $progress_data ) {
		try {
			$user_id = get_current_user_id();
			if ( ! $user_id ) {
				return ENNU_Form_Result::error( 'not_logged_in', 'User must be logged in to save progress' );
			}

			// Save progress to user meta
			$meta_key = 'ennu_' . $progress_data['assessment_type'] . '_progress';
			update_user_meta( $user_id, $meta_key, $progress_data );

			return ENNU_Form_Result::success();

		} catch ( Exception $e ) {
			return ENNU_Form_Result::error( 'save_failed', $e->getMessage() );
		}
	}
}

/**
 * AJAX Security handler
 */
class ENNU_AJAX_Security_Handler {

	/**
	 * Validate AJAX request with comprehensive security checks
	 */
	public function validate_request( $action ) {
		// Check if it's an AJAX request
		if ( ! wp_doing_ajax() ) {
			return ENNU_Security_Result::error( 'not_ajax', 'Request must be made via AJAX' );
		}

		// Verify nonce
		$nonce = $_POST['nonce'] ?? '';
		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			return ENNU_Security_Result::error( 'invalid_nonce', 'Security check failed' );
		}

		// Check user permissions for logged-in users
		if ( is_user_logged_in() ) {
			if ( ! current_user_can( 'read' ) ) {
				return ENNU_Security_Result::error( 'insufficient_permissions', 'Insufficient permissions' );
			}
		}

		// Rate limiting (basic implementation)
		if ( ! $this->check_rate_limit() ) {
			return ENNU_Security_Result::error( 'rate_limit_exceeded', 'Too many requests' );
		}

		return ENNU_Security_Result::valid();
	}

	/**
	 * Basic rate limiting check
	 */
	private function check_rate_limit() {
		$ip = $this->get_client_ip();
		$key = 'ennu_rate_limit_' . md5( $ip );
		
		$requests = get_transient( $key );
		if ( $requests === false ) {
			$requests = 0;
		}

		if ( $requests > 10 ) { // Max 10 requests per minute
			return false;
		}

		set_transient( $key, $requests + 1, MINUTE_IN_SECONDS );
		return true;
	}

	/**
	 * Get client IP address
	 */
	private function get_client_ip() {
		$ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
		
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
}

/**
 * Security validation result
 */
class ENNU_Security_Result {
	private $valid;
	private $error_code;
	private $error_message;

	public static function valid() {
		$result = new self();
		$result->valid = true;
		return $result;
	}

	public static function error( $code, $message ) {
		$result = new self();
		$result->valid = false;
		$result->error_code = $code;
		$result->error_message = $message;
		return $result;
	}

	public function is_valid() {
		return $this->valid;
	}

	public function get_error_code() {
		return $this->error_code;
	}

	public function get_error_message() {
		return $this->error_message;
	}
} 