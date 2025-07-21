<?php
/**
 * Security Validator Class
 *
 * Provides comprehensive security validation and sanitization functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Security_Validator {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'init_security_hooks' ) );
	}

	public function init_security_hooks() {
		add_filter( 'ennu_sanitize_input', array( $this, 'sanitize_input' ), 10, 2 );
		add_filter( 'ennu_validate_nonce', array( $this, 'validate_nonce' ), 10, 2 );
	}

	public function sanitize_input( $input, $type = 'text' ) {
		if ( is_array( $input ) ) {
			return array_map( array( $this, 'sanitize_input' ), $input );
		}

		switch ( $type ) {
			case 'email':
				return sanitize_email( $input );
			case 'url':
				return esc_url_raw( $input );
			case 'int':
				return intval( $input );
			case 'float':
				return floatval( $input );
			case 'textarea':
				return sanitize_textarea_field( $input );
			case 'html':
				return wp_kses_post( $input );
			case 'key':
				return sanitize_key( $input );
			default:
				return sanitize_text_field( $input );
		}
	}

	public function validate_nonce( $nonce, $action ) {
		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed.' ), 403 );
			wp_die();
		}
		return true;
	}

	public function validate_ajax_request( $nonce_action = 'ennu_ajax_nonce', $capability = null ) {
		check_ajax_referer( $nonce_action, 'nonce' );

		if ( $capability && ! current_user_can( $capability ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
			wp_die();
		}

		return true;
	}

	public function sanitize_assessment_data( $data ) {
		$sanitized = array();

		foreach ( $data as $key => $value ) {
			$sanitized_key = sanitize_key( $key );

			if ( is_array( $value ) ) {
				$sanitized[ $sanitized_key ] = $this->sanitize_assessment_data( $value );
			} else {
				switch ( $key ) {
					case 'email':
						$sanitized[ $sanitized_key ] = sanitize_email( $value );
						break;
					case 'first_name':
					case 'last_name':
					case 'name':
						$sanitized[ $sanitized_key ] = sanitize_text_field( $value );
						break;
					case 'notes':
					case 'comments':
						$sanitized[ $sanitized_key ] = wp_kses_post( $value );
						break;
					case 'phone':
						$sanitized[ $sanitized_key ] = preg_replace( '/[^0-9+\-\(\)\s]/', '', $value );
						break;
					default:
						if ( is_numeric( $value ) ) {
							$sanitized[ $sanitized_key ] = is_float( $value ) ? floatval( $value ) : intval( $value );
						} else {
							$sanitized[ $sanitized_key ] = sanitize_text_field( $value );
						}
						break;
				}
			}
		}

		return $sanitized;
	}

	public function validate_user_permissions( $user_id, $requested_user_id = null ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$current_user_id = get_current_user_id();

		if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_users' ) ) {
			return true;
		}

		if ( $requested_user_id && $current_user_id !== intval( $requested_user_id ) ) {
			return false;
		}

		if ( $user_id && $current_user_id !== intval( $user_id ) ) {
			return false;
		}

		return true;
	}

	public function log_security_event( $event_type, $details = array() ) {
		$log_entry = array(
			'timestamp'  => current_time( 'mysql' ),
			'event_type' => sanitize_text_field( $event_type ),
			'user_id'    => get_current_user_id(),
			'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
			'details'    => $details,
		);

		error_log( 'ENNU Security Event: ' . wp_json_encode( $log_entry ) );

		do_action( 'ennu_security_event', $log_entry );
	}

	public function rate_limit_check( $action, $limit = 10, $window = 300 ) {
		$user_id    = get_current_user_id();
		$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
		$cache_key  = 'ennu_rate_limit_' . md5( $action . '_' . $user_id . '_' . $ip_address );

		$attempts = get_transient( $cache_key );

		if ( false === $attempts ) {
			set_transient( $cache_key, 1, $window );
			return true;
		}

		if ( $attempts >= $limit ) {
			$this->log_security_event(
				'rate_limit_exceeded',
				array(
					'action'   => $action,
					'attempts' => $attempts,
					'limit'    => $limit,
				)
			);
			return false;
		}

		set_transient( $cache_key, $attempts + 1, $window );
		return true;
	}
}

ENNU_Security_Validator::get_instance();
