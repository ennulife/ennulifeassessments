<?php
/**
 * ENNU Security Enhancements
 *
 * Provides comprehensive security enhancements for XSS, CSRF, and SQL injection protection.
 *
 * @package ENNU_Life
 * @version 62.2.8
 * @author ENNU Life Development Team
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Security_Enhancements {

	/**
	 * Initialize security enhancements
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'setup_security_headers' ) );
		add_filter( 'wp_kses_allowed_html', array( __CLASS__, 'extend_allowed_html' ), 10, 2 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_security_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_security_scripts' ) );
	}

	/**
	 * Setup security headers
	 */
	public static function setup_security_headers() {
		if ( ! headers_sent() ) {
			header( 'X-Content-Type-Options: nosniff' );
			header( 'X-Frame-Options: SAMEORIGIN' );
			header( 'X-XSS-Protection: 1; mode=block' );
		}
	}

	/**
	 * Sanitize output for display
	 *
	 * @param mixed $data Data to sanitize
	 * @param string $context Context for sanitization
	 * @return mixed Sanitized data
	 */
	public static function sanitize_output( $data, $context = 'text' ) {
		if ( is_array( $data ) ) {
			return array_map(
				function( $item ) use ( $context ) {
					return self::sanitize_output( $item, $context );
				},
				$data
			);
		}

		if ( ! is_string( $data ) ) {
			return $data;
		}

		switch ( $context ) {
			case 'html':
				return wp_kses_post( $data );
			case 'attribute':
				return esc_attr( $data );
			case 'url':
				return esc_url( $data );
			case 'js':
				return esc_js( $data );
			case 'textarea':
				return esc_textarea( $data );
			case 'text':
			default:
				return esc_html( $data );
		}
	}

	/**
	 * Sanitize input data
	 *
	 * @param mixed $data Input data
	 * @param string $type Data type
	 * @return mixed Sanitized data
	 */
	public static function sanitize_input( $data, $type = 'text' ) {
		if ( is_array( $data ) ) {
			return array_map(
				function( $item ) use ( $type ) {
					return self::sanitize_input( $item, $type );
				},
				$data
			);
		}

		if ( ! is_string( $data ) ) {
			return $data;
		}

		switch ( $type ) {
			case 'email':
				return sanitize_email( $data );
			case 'url':
				return esc_url_raw( $data );
			case 'int':
				return intval( $data );
			case 'float':
				return floatval( $data );
			case 'textarea':
				return sanitize_textarea_field( $data );
			case 'key':
				return sanitize_key( $data );
			case 'slug':
				return sanitize_title( $data );
			case 'text':
			default:
				return sanitize_text_field( $data );
		}
	}

	/**
	 * Generate and verify nonce for forms
	 *
	 * @param string $action Nonce action
	 * @param string $name Nonce name
	 * @return string Nonce field HTML
	 */
	public static function nonce_field( $action, $name = '_wpnonce' ) {
		return wp_nonce_field( $action, $name, true, false );
	}

	/**
	 * Verify nonce
	 *
	 * @param string $nonce Nonce value
	 * @param string $action Nonce action
	 * @return bool Verification result
	 */
	public static function verify_nonce( $nonce, $action ) {
		return wp_verify_nonce( $nonce, $action );
	}

	/**
	 * Secure AJAX handler wrapper
	 *
	 * @param callable $callback Callback function
	 * @param string $nonce_action Nonce action
	 * @param array $capabilities Required capabilities
	 * @return mixed Callback result or error
	 */
	public static function secure_ajax_handler( $callback, $nonce_action, $capabilities = array() ) {
		if ( ! wp_doing_ajax() ) {
			wp_die( 'Invalid request' );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verification is performed in the next line
		$nonce = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) : '';
		if ( ! self::verify_nonce( $nonce, $nonce_action ) ) {
			wp_die( 'Security check failed' );
		}

		foreach ( $capabilities as $capability ) {
			if ( ! current_user_can( $capability ) ) {
				wp_die( 'Insufficient permissions' );
			}
		}

		return call_user_func( $callback );
	}

	/**
	 * Prepare safe database query
	 *
	 * @param string $query SQL query with placeholders
	 * @param array $args Query arguments
	 * @return string Prepared query
	 */
	public static function prepare_query( $query, $args = array() ) {
		global $wpdb;

		if ( empty( $args ) ) {
			return $query;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query is prepared with placeholders
		return $wpdb->prepare( $query, ...$args );
	}

	/**
	 * Extend allowed HTML for wp_kses
	 *
	 * @param array $allowed_tags Allowed tags
	 * @param string $context Context
	 * @return array Extended allowed tags
	 */
	public static function extend_allowed_html( $allowed_tags, $context ) {
		if ( 'ennu_assessment' === $context ) {
			$allowed_tags['div']['data-*']    = true;
			$allowed_tags['span']['data-*']   = true;
			$allowed_tags['input']['data-*']  = true;
			$allowed_tags['select']['data-*'] = true;
			$allowed_tags['option']['data-*'] = true;
		}

		return $allowed_tags;
	}

	/**
	 * Enqueue security scripts for frontend
	 */
	public static function enqueue_security_scripts() {
		wp_localize_script(
			'jquery',
			'ennu_security',
			array(
				'nonce'    => wp_create_nonce( 'ennu_ajax_nonce' ),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	/**
	 * Enqueue security scripts for admin
	 */
	public static function enqueue_admin_security_scripts() {
		wp_localize_script(
			'jquery',
			'ennu_admin_security',
			array(
				'nonce'    => wp_create_nonce( 'ennu_admin_nonce' ),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
			)
		);
	}

	/**
	 * Validate file upload
	 *
	 * @param array $file File data from $_FILES
	 * @param array $allowed_types Allowed MIME types
	 * @param int $max_size Maximum file size in bytes
	 * @return bool|WP_Error Validation result
	 */
	public static function validate_file_upload( $file, $allowed_types = array(), $max_size = 2097152 ) {
		if ( ! isset( $file['error'] ) || is_array( $file['error'] ) ) {
			return new WP_Error( 'invalid_file', 'Invalid file upload' );
		}

		if ( UPLOAD_ERR_OK !== $file['error'] ) {
			return new WP_Error( 'upload_error', 'File upload error: ' . $file['error'] );
		}

		if ( $file['size'] > $max_size ) {
			return new WP_Error( 'file_too_large', 'File size exceeds maximum allowed size' );
		}

		$file_type = wp_check_filetype( $file['name'] );
		if ( ! empty( $allowed_types ) && ! in_array( $file_type['type'], $allowed_types, true ) ) {
			return new WP_Error( 'invalid_file_type', 'File type not allowed' );
		}

		return true;
	}

	/**
	 * Log security events
	 *
	 * @param string $event Event type
	 * @param string $message Event message
	 * @param array $context Additional context
	 */
	public static function log_security_event( $event, $message, $context = array() ) {
		$log_entry = array(
			'timestamp'  => current_time( 'mysql' ),
			'event'      => $event,
			'message'    => $message,
			'user_id'    => get_current_user_id(),
			'ip_address' => self::get_client_ip(),
			'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '',
			'context'    => $context,
		);

		error_log( 'ENNU Security Event: ' . wp_json_encode( $log_entry ) );

		if ( in_array( $event, array( 'failed_nonce', 'unauthorized_access', 'suspicious_activity' ), true ) ) {
			do_action( 'ennu_security_alert', $log_entry );
		}
	}

	/**
	 * Get client IP address
	 *
	 * @return string Client IP address
	 */
	private static function get_client_ip() {
		$ip_keys = array( 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR' );

		foreach ( $ip_keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = $_SERVER[ $key ];
				if ( strpos( $ip, ',' ) !== false ) {
					$ip = trim( explode( ',', $ip )[0] );
				}
				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
					return $ip;
				}
			}
		}

		return isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
	}
}

ENNU_Security_Enhancements::init();
