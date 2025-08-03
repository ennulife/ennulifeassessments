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
 * 
 * @deprecated This class is deprecated. Use ENNU_AJAX_Service_Handler instead.
 */
class ENNU_AJAX_Handler_DISABLED {

	private $form_handler;
	private $security;
	private $logger;

	public function __construct() {
		// Prevent instantiation of deprecated class
		if ( class_exists( 'ENNU_AJAX_Service_Handler' ) ) {
			error_log( 'ENNU: Attempted to instantiate deprecated ENNU_AJAX_Handler. Use ENNU_AJAX_Service_Handler instead.' );
			return;
		}
		
		$this->form_handler = new ENNU_Form_Handler();
		$this->security = new ENNU_AJAX_Security_Handler();
		$this->logger = new ENNU_Logger();
		
		$this->init_hooks();
	}

	/**
	 * Initialize AJAX hooks
	 */
	private function init_hooks() {
			// DISABLED: Old AJAX handler - conflicts with new ENNU_AJAX_Service_Handler
	// add_action( 'wp_ajax_ennu_submit_assessment', array( $this, 'handle_assessment_submission' ) );
	// add_action( 'wp_ajax_nopriv_ennu_submit_assessment', array( $this, 'handle_assessment_submission' ) );
		
		add_action( 'wp_ajax_ennu_get_assessment_results', array( $this, 'handle_get_results' ) );
		add_action( 'wp_ajax_nopriv_ennu_get_assessment_results', array( $this, 'handle_get_results' ) );
		
		add_action( 'wp_ajax_ennu_save_assessment_progress', array( $this, 'handle_save_progress' ) );
		add_action( 'wp_ajax_nopriv_ennu_save_assessment_progress', array( $this, 'handle_save_progress' ) );
	}

	/**
	 * Handle assessment form submission via AJAX
	 * Migrated from legacy ENNU_Assessment_Shortcodes class
	 */
	public function handle_assessment_submission() {
		$this->logger->log( 'AJAX assessment submission started' );

		try {
			// 1. Security validation using legacy security classes
			if ( class_exists( 'ENNU_AJAX_Security' ) ) {
				$security_result = ENNU_AJAX_Security::validate_ajax_request( 'ennu_submit_assessment' );
				if ( is_wp_error( $security_result ) ) {
					wp_send_json_error( array(
						'message' => $security_result->get_error_message(),
						'code'    => $security_result->get_error_code()
					) );
				}
			}

			// 2. Verify nonce
			check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

			// 3. Rate limiting
			if ( class_exists( 'ENNU_Security_Validator' ) ) {
				$security_validator = ENNU_Security_Validator::get_instance();
				if ( ! $security_validator->rate_limit_check( 'assessment_submission', 5, 300 ) ) {
					wp_send_json_error( array( 'message' => 'Too many submission attempts. Please wait before trying again.' ), 429 );
					return;
				}
			}

			// 4. Get and sanitize form data
			$form_data = $this->get_form_data();
			if ( empty( $form_data ) ) {
				wp_send_json_error( array(
					'message' => 'No form data received',
					'code'    => 'no_data'
				) );
			}

			// 5. Validate form data
			$validation_result = $this->validate_assessment_data( $form_data );
			if ( is_wp_error( $validation_result ) ) {
				wp_send_json_error( array(
					'message' => $validation_result->get_error_message(),
					'code'    => $validation_result->get_error_code()
				), 400 );
			}

			// 6. Process user creation/retrieval
			$user_result = $this->process_user( $form_data );
			if ( is_wp_error( $user_result ) ) {
				wp_send_json_error( array(
					'message' => $user_result->get_error_message(),
					'code'    => $user_result->get_error_code()
				) );
			}
			$user_id = $user_result;

			// 7. Calculate and save BMI if applicable
			$this->calculate_and_save_bmi( $user_id, $form_data );

			// 8. Process submission using form handler
			$result = $this->form_handler->process_submission( $form_data );

			// 9. Handle result
			if ( $result->is_success() ) {
				$response_data = $result->get_data();
				
				// Generate results token for quantitative assessments
				if ( $this->is_quantitative_assessment( $form_data['assessment_type'] ) ) {
					$token = $this->generate_results_token( $response_data['user_id'], $form_data );
					$response_data['results_token'] = $token;
				}

				// Generate redirect URL based on assessment type
				$redirect_url = $this->generate_redirect_url( $form_data['assessment_type'], $response_data );
				if ( $redirect_url ) {
					$response_data['redirect_url'] = $redirect_url;
					$this->logger->log( 'Assessment submission successful, redirecting to: ' . $redirect_url );
				} else {
					$response_data['redirect_url'] = false;
					$this->logger->log( 'Assessment submission successful, but no redirect page configured' );
				}

				wp_send_json_success( array(
					'message' => 'Assessment submitted successfully',
					'data'    => $response_data,
					'redirect_url' => $redirect_url
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
		$quantitative_types = array( 'health', 'weight_loss', 'weight-loss', 'hormone', 'sleep', 'skin', 'hair', 'ed-treatment', 'menopause', 'testosterone', 'health-optimization' );
		return in_array( $assessment_type, $quantitative_types );
	}

	/**
	 * Generate results token for quantitative assessments
	 */
	private function generate_results_token( $user_id, $form_data ) {
		$token = wp_generate_password( 32, false );
		
		// Calculate assessment scores
		$scores = $this->calculate_assessment_scores( $form_data );
		
		// Store complete results data in transient
		$results_data = array(
			'user_id' => $user_id,
			'assessment_type' => $form_data['assessment_type'],
			'score' => $scores['overall_score'],
			'interpretation' => array(
				'level' => $scores['level'],
				'color' => $scores['color']
			),
			'category_scores' => $scores['category_scores'] ?? array(),
			'answers' => $form_data,
			'token_created' => time(),
			'timestamp' => current_time( 'mysql' )
		);

		// Store for 24 hours (86400 seconds) using manual transient storage
		$transient_key = 'ennu_results_' . $token;
		$this->_set_manual_transient( $transient_key, $results_data, 86400 );
		error_log( 'ENNU AJAX Debug: Stored manual transient with key: ' . $transient_key . ', Assessment type: ' . ( $results_data['assessment_type'] ?? 'unknown' ) );

		return $token;
	}

	/**
	 * Manually sets a transient-like value in the options table.
	 */
	private function _set_manual_transient( $key, $value, $expiration ) {
		$timeout = time() + $expiration;
		update_option( '_ennu_manual_transient_' . $key, $value );
		update_option( '_ennu_manual_transient_timeout_' . $key, $timeout );
	}

	/**
	 * Manually gets a transient-like value from the options table.
	 */
	private function _get_manual_transient( $key ) {
		$timeout = get_option( '_ennu_manual_transient_timeout_' . $key );
		if ( false === $timeout || $timeout < time() ) {
			$this->_delete_manual_transient( $key );
			return false;
		}
		return get_option( '_ennu_manual_transient_' . $key );
	}

	/**
	 * Manually deletes a transient-like value from the options table.
	 */
	private function _delete_manual_transient( $key ) {
		delete_option( '_ennu_manual_transient_' . $key );
		delete_option( '_ennu_manual_transient_timeout_' . $key );
	}

	/**
	 * Get results by token
	 */
	private function get_results_by_token( $token ) {
		$results_data = $this->_get_manual_transient( 'ennu_results_' . $token );
		
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
		// For now, return placeholder with category scores
		return array(
			'overall_score' => 7.5,
			'level' => 'Good',
			'color' => '#10b981',
			'category_scores' => array(
				'Overall Health' => 7.5,
				'Energy Levels' => 7.2,
				'Lifestyle' => 7.8,
				'Wellness' => 7.3
			)
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

	/**
	 * Validate assessment data
	 * Migrated from legacy ENNU_Assessment_Shortcodes class
	 */
	private function validate_assessment_data( $data ) {
		if ( empty( $data['assessment_type'] ) ) {
			return new WP_Error( 'missing_assessment_type', 'Assessment type is required' );
		}

		if ( empty( $data['email'] ) ) {
			return new WP_Error( 'missing_email', 'Email address is required' );
		}

		if ( ! is_email( $data['email'] ) ) {
			return new WP_Error( 'invalid_email', 'Invalid email address' );
		}

		if ( empty( $data['first_name'] ) ) {
			return new WP_Error( 'missing_first_name', 'First name is required' );
		}

		return true;
	}

	/**
	 * Process user creation or retrieval
	 * Migrated from legacy ENNU_Assessment_Shortcodes class
	 */
	private function process_user( $form_data ) {
		$email = $form_data['email'];
		$user_id = email_exists( $email );

		if ( ! $user_id ) {
			// Create new user
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
				return $user_id;
			}

			// Log the new user in
			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id );
		} else {
			// User already exists, check if they are logged in
			if ( ! is_user_logged_in() || get_current_user_id() != $user_id ) {
				$login_url = wp_login_url( get_permalink() );
				return new WP_Error( 
					'login_required', 
					'An account with this email already exists. Please <a href="' . esc_url( $login_url ) . '">log in</a> to continue.'
				);
			}
		}

		return $user_id;
	}

	/**
	 * Calculate and save BMI if applicable
	 * Migrated from legacy ENNU_Assessment_Shortcodes class
	 */
	private function calculate_and_save_bmi( $user_id, $form_data ) {
		if ( isset( $form_data['height_ft'] ) && isset( $form_data['height_in'] ) && isset( $form_data['weight_lbs'] ) ) {
			$height_in_total = ( intval( $form_data['height_ft'] ) * 12 ) + intval( $form_data['height_in'] );
			$weight_lbs = intval( $form_data['weight_lbs'] );
			
			if ( $height_in_total > 0 && $weight_lbs > 0 ) {
				$bmi = ( $weight_lbs / ( $height_in_total * $height_in_total ) ) * 703;
				update_user_meta( $user_id, 'ennu_calculated_bmi', round( $bmi, 1 ) );

				// Store historical BMI
				$bmi_history = get_user_meta( $user_id, 'ennu_bmi_history', true );
				if ( ! is_array( $bmi_history ) ) {
					$bmi_history = array();
				}
				$bmi_history[] = array(
					'date' => date( 'Y-m-d H:i:s.u' ),
					'bmi'  => round( $bmi, 1 ),
				);
				update_user_meta( $user_id, 'ennu_bmi_history', $bmi_history );
			}
		}
	}

	/**
	 * Generate redirect URL based on assessment type using admin-configured results pages
	 */
	private function generate_redirect_url( $assessment_type, $response_data ) {
		$base_url = home_url();
		
		// Get the page mappings from admin settings ONLY
		$settings = get_option( 'ennu_life_settings', array() );
		$page_mappings = $settings['page_mappings'] ?? array();
		
		// Map assessment types to their results page slugs (matching admin configuration)
		$assessment_results_map = array(
			'weight-loss' => 'assessments/weight-loss/results',
			'health-optimization' => 'assessments/health-optimization/results',
			'hormone' => 'assessments/hormone/results',
			'menopause' => 'assessments/menopause/results',
			'testosterone' => 'assessments/testosterone/results',
			'sleep' => 'assessments/sleep/results',
			'skin' => 'assessments/skin/results',
			'hair' => 'assessments/hair/results',
			'ed-treatment' => 'assessments/ed-treatment/results',
			'health' => 'assessments/health/results',
			'welcome' => 'welcome-assessment-details'
		);
		
		// Check if we have a specific results page for this assessment
		if ( isset( $assessment_results_map[ $assessment_type ] ) ) {
			$results_slug = $assessment_results_map[ $assessment_type ];
			
			// ONLY use admin-configured pages
			if ( isset( $page_mappings[ $results_slug ] ) && ! empty( $page_mappings[ $results_slug ] ) ) {
				$page_id = $page_mappings[ $results_slug ];
				
				// Use the ?page_id= format as requested
				$redirect_url = $base_url . '/?page_id=' . $page_id;
				
				// Add results token if available
				if ( isset( $response_data['results_token'] ) ) {
					$redirect_url .= '&token=' . urlencode( $response_data['results_token'] );
				}
				
				$this->logger->log( 'ENNU REDIRECT DEBUG: Using admin-configured results page for ' . $assessment_type . ' with page_id=' . $page_id . ' (slug: ' . $results_slug . ')' );
				return $redirect_url;
			} else {
				$this->logger->log( 'ENNU REDIRECT DEBUG: No admin-configured results page found for ' . $assessment_type . ' (slug: ' . $results_slug . ') - Redirect will fail' );
				return false;
			}
		}
		
		// NO FALLBACKS - Return false if no mapping found
		$this->logger->log( 'ENNU REDIRECT DEBUG: No assessment mapping found for ' . $assessment_type . ' - Redirect will fail' );
		return false;
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