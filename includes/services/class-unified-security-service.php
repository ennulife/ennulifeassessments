<?php
/**
 * ENNU Unified Security Service
 *
 * Consolidates all security implementations into a single, authoritative service
 *
 * @package ENNU_Life_Assessments
 * @since 64.14.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Strategy Interface
 *
 * @since 64.14.0
 */
interface ENNU_Security_Strategy_Interface {
	
	/**
	 * Validate request security
	 *
	 * @param array $request_data Request data to validate
	 * @return array Validation result with errors
	 */
	public function validate_request( $request_data );
	
	/**
	 * Generate security token
	 *
	 * @param string $action Action name for the token
	 * @param int $user_id User ID (optional)
	 * @return string Security token
	 */
	public function generate_token( $action, $user_id = null );
	
	/**
	 * Verify security token
	 *
	 * @param string $token Token to verify
	 * @param string $action Action name for the token
	 * @return bool Verification result
	 */
	public function verify_token( $token, $action );
	
	/**
	 * Sanitize input data
	 *
	 * @param mixed $data Data to sanitize
	 * @param string $type Data type for sanitization
	 * @return mixed Sanitized data
	 */
	public function sanitize_data( $data, $type = 'text' );
	
	/**
	 * Check user permissions
	 *
	 * @param string $capability Required capability
	 * @param int $user_id User ID (optional)
	 * @return bool Permission check result
	 */
	public function check_permissions( $capability, $user_id = null );
}

/**
 * ENNU Unified Security Service Class
 *
 * @since 64.14.0
 */
class ENNU_Unified_Security_Service {
	
	/**
	 * Security strategies
	 *
	 * @var array
	 */
	private $strategies = array();
	
	/**
	 * Current strategy
	 *
	 * @var ENNU_Security_Strategy_Interface
	 */
	private $current_strategy;
	
	/**
	 * Security configuration
	 *
	 * @var array
	 */
	private $security_config = array(
		'csrf_protection' => true,
		'rate_limiting' => true,
		'input_validation' => true,
		'output_escaping' => true,
		'permission_checking' => true,
		'token_expiry' => 3600, // 1 hour
		'max_requests_per_minute' => 60,
		'allowed_html_tags' => array(
			'a' => array( 'href' => array(), 'title' => array() ),
			'br' => array(),
			'em' => array(),
			'strong' => array(),
			'p' => array(),
		),
	);
	
	/**
	 * Rate limiting cache
	 *
	 * @var array
	 */
	private $rate_limit_cache = array();
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->register_strategies();
		$this->set_default_strategy();
	}
	
	/**
	 * Register security strategies
	 */
	private function register_strategies() {
		// WordPress security strategy
		$this->strategies['wordpress'] = new ENNU_WordPress_Security_Strategy();
		
		// Advanced security strategy
		$this->strategies['advanced'] = new ENNU_Advanced_Security_Strategy();
		
		// Minimal security strategy
		$this->strategies['minimal'] = new ENNU_Minimal_Security_Strategy();
	}
	
	/**
	 * Set default strategy
	 */
	private function set_default_strategy() {
		$this->current_strategy = $this->strategies['wordpress'];
	}
	
	/**
	 * Set security strategy
	 *
	 * @param string $strategy_name Strategy name
	 * @return bool Success status
	 */
	public function set_strategy( $strategy_name ) {
		if ( ! isset( $this->strategies[ $strategy_name ] ) ) {
			error_log( "ENNU Unified Security Service: Unknown strategy '{$strategy_name}'" );
			return false;
		}
		
		$this->current_strategy = $this->strategies[ $strategy_name ];
		error_log( "ENNU Unified Security Service: Strategy set to '{$strategy_name}'" );
		return true;
	}
	
	/**
	 * Validate AJAX request
	 *
	 * @param string $action Action name
	 * @param array $request_data Request data
	 * @return array Validation result
	 */
	public function validate_ajax_request( $action, $request_data = array() ) {
		$errors = array();
		
		// Check if CSRF protection is enabled
		if ( $this->security_config['csrf_protection'] ) {
			$nonce = $request_data['nonce'] ?? '';
			if ( ! $this->current_strategy->verify_token( $nonce, $action ) ) {
				$errors[] = 'Invalid security token';
			}
		}
		
		// Check rate limiting
		if ( $this->security_config['rate_limiting'] ) {
			if ( ! $this->check_rate_limit( $action ) ) {
				$errors[] = 'Rate limit exceeded';
			}
		}
		
		// Check permissions
		if ( $this->security_config['permission_checking'] ) {
			$capability = $this->get_required_capability( $action );
			if ( ! $this->current_strategy->check_permissions( $capability ) ) {
				$errors[] = 'Insufficient permissions';
			}
		}
		
		return array(
			'valid' => empty( $errors ),
			'errors' => $errors,
		);
	}
	
	/**
	 * Generate AJAX nonce
	 *
	 * @param string $action Action name
	 * @return string Nonce
	 */
	public function generate_ajax_nonce( $action ) {
		return $this->current_strategy->generate_token( $action );
	}
	
	/**
	 * Sanitize input data
	 *
	 * @param mixed $data Data to sanitize
	 * @param string $type Data type
	 * @return mixed Sanitized data
	 */
	public function sanitize_input( $data, $type = 'text' ) {
		return $this->current_strategy->sanitize_data( $data, $type );
	}
	
	/**
	 * Escape output data
	 *
	 * @param mixed $data Data to escape
	 * @param string $type Data type
	 * @return string Escaped data
	 */
	public function escape_output( $data, $type = 'html' ) {
		switch ( $type ) {
			case 'html':
				return esc_html( $data );
			case 'attr':
				return esc_attr( $data );
			case 'url':
				return esc_url( $data );
			case 'js':
				return esc_js( $data );
			case 'textarea':
				return esc_textarea( $data );
			default:
				return esc_html( $data );
		}
	}
	
	/**
	 * Check rate limit
	 *
	 * @param string $action Action name
	 * @return bool Rate limit check result
	 */
	private function check_rate_limit( $action ) {
		$user_id = get_current_user_id();
		$ip_address = $this->get_client_ip();
		$key = "{$user_id}_{$ip_address}_{$action}";
		
		$current_time = time();
		$requests = $this->rate_limit_cache[ $key ] ?? array();
		
		// Remove old requests (older than 1 minute)
		$requests = array_filter( $requests, function( $timestamp ) use ( $current_time ) {
			return ( $current_time - $timestamp ) < 60;
		} );
		
		// Check if rate limit exceeded
		if ( count( $requests ) >= $this->security_config['max_requests_per_minute'] ) {
			return false;
		}
		
		// Add current request
		$requests[] = $current_time;
		$this->rate_limit_cache[ $key ] = $requests;
		
		return true;
	}
	
	/**
	 * Get client IP address
	 *
	 * @return string IP address
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
		
		return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
	}
	
	/**
	 * Get required capability for action
	 *
	 * @param string $action Action name
	 * @return string Required capability
	 */
	private function get_required_capability( $action ) {
		$capability_map = array(
			'ennu_save_assessment' => 'edit_posts',
			'ennu_get_biomarkers' => 'read',
			'ennu_save_biomarker' => 'edit_posts',
			'ennu_delete_biomarker' => 'delete_posts',
			'ennu_import_data' => 'manage_options',
			'ennu_export_data' => 'manage_options',
			'ennu_admin_action' => 'manage_options',
		);
		
		return $capability_map[ $action ] ?? 'read';
	}
	
	/**
	 * Validate file upload
	 *
	 * @param array $file File upload data
	 * @param array $allowed_types Allowed file types
	 * @param int $max_size Maximum file size in bytes
	 * @return array Validation result
	 */
	public function validate_file_upload( $file, $allowed_types = array(), $max_size = 5242880 ) {
		$errors = array();
		
		// Check if file exists
		if ( ! isset( $file['tmp_name'] ) || ! file_exists( $file['tmp_name'] ) ) {
			$errors[] = 'No file uploaded or file not found';
		}
		
		// Check file size
		if ( isset( $file['size'] ) && $file['size'] > $max_size ) {
			$errors[] = 'File size exceeds maximum allowed size';
		}
		
		// Check file type
		if ( ! empty( $allowed_types ) && isset( $file['name'] ) ) {
			$file_extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
			if ( ! in_array( $file_extension, $allowed_types ) ) {
				$errors[] = 'File type not allowed';
			}
		}
		
		// Check for upload errors
		if ( isset( $file['error'] ) && $file['error'] !== UPLOAD_ERR_OK ) {
			$errors[] = 'File upload error: ' . $file['error'];
		}
		
		return array(
			'valid' => empty( $errors ),
			'errors' => $errors,
		);
	}
	
	/**
	 * Log security event
	 *
	 * @param string $event_type Event type
	 * @param array $event_data Event data
	 */
	public function log_security_event( $event_type, $event_data = array() ) {
		$log_entry = array(
			'timestamp' => current_time( 'mysql' ),
			'event_type' => $event_type,
			'user_id' => get_current_user_id(),
			'ip_address' => $this->get_client_ip(),
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
			'event_data' => $event_data,
		);
		
		error_log( 'ENNU Security Event: ' . json_encode( $log_entry ) );
	}
	
	/**
	 * Get security configuration
	 *
	 * @return array Security configuration
	 */
	public function get_security_config() {
		return $this->security_config;
	}
	
	/**
	 * Update security configuration
	 *
	 * @param array $config New configuration
	 */
	public function update_security_config( $config ) {
		$this->security_config = array_merge( $this->security_config, $config );
		error_log( 'ENNU Unified Security Service: Security configuration updated' );
	}
	
	/**
	 * Initialize security service
	 */
	public function init() {
		// Register AJAX security hooks
		add_action( 'wp_ajax_ennu_security_check', array( $this, 'handle_security_check' ) );
		add_action( 'wp_ajax_nopriv_ennu_security_check', array( $this, 'handle_security_check' ) );
		
		// Register admin security hooks
		add_action( 'admin_init', array( $this, 'init_admin_security' ) );
		
		error_log( 'ENNU Unified Security Service: Initialized successfully' );
	}
	
	/**
	 * Handle security check AJAX request
	 */
	public function handle_security_check() {
		$action = sanitize_text_field( $_POST['action'] ?? '' );
		$request_data = $_POST;
		
		$validation = $this->validate_ajax_request( $action, $request_data );
		
		if ( $validation['valid'] ) {
			wp_send_json_success( array( 'message' => 'Security check passed' ) );
		} else {
			$this->log_security_event( 'security_check_failed', array(
				'action' => $action,
				'errors' => $validation['errors'],
			) );
			
			wp_send_json_error( array(
				'message' => 'Security check failed',
				'errors' => $validation['errors'],
			) );
		}
	}
	
	/**
	 * Initialize admin security
	 */
	public function init_admin_security() {
		// Add security headers
		add_action( 'send_headers', array( $this, 'add_security_headers' ) );
		
		// Add content security policy
		add_action( 'wp_head', array( $this, 'add_csp_header' ) );
	}
	
	/**
	 * Add security headers
	 */
	public function add_security_headers() {
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'X-XSS-Protection: 1; mode=block' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	}
	
	/**
	 * Add Content Security Policy header
	 */
	public function add_csp_header() {
		$csp = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:;";
		header( "Content-Security-Policy: {$csp}" );
	}
} 