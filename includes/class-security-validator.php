<?php
/**
 * ENNU Security Validator - CRITICAL SECURITY FIX
 * 
 * This class provides comprehensive security validation to prevent:
 * - SQL Injection attacks
 * - Cross-Site Scripting (XSS) attacks
 * - Data type validation issues
 * - Rate limiting
 * 
 * @package ENNU_Life_Assessments
 * @since 1.0.0
 */

class ENNU_Security_Validator {
	
	private static $instance = null;
	private $rate_limit_data = array();
	
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Comprehensive input sanitization to prevent XSS
	 */
	public function sanitize_input( $input, $type = 'text' ) {
		if ( is_array( $input ) ) {
			$sanitized = array();
			foreach ( $input as $key => $value ) {
				$sanitized[ $this->sanitize_key( $key ) ] = $this->sanitize_input( $value, $type );
			}
			return $sanitized;
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
			case 'html':
				return wp_kses_post( $input );
			case 'textarea':
				return sanitize_textarea_field( $input );
			default:
				return sanitize_text_field( $input );
		}
	}
	
	/**
	 * Sanitize array keys to prevent injection
	 */
	private function sanitize_key( $key ) {
		return sanitize_key( $key );
	}
	
	/**
	 * Validate email format and prevent injection
	 */
	public function validate_email( $email ) {
		$email = sanitize_email( $email );
		return is_email( $email ) ? $email : false;
	}
	
	/**
	 * Validate and sanitize assessment type
	 */
	public function validate_assessment_type( $type ) {
		$allowed_types = array(
			'health-optimization',
			'testosterone',
			'hormone',
			'menopause',
			'ed-treatment',
			'skin',
			'hair',
			'sleep',
			'weight-loss',
			'welcome'
		);
		
		$type = sanitize_key( $type );
		return in_array( $type, $allowed_types ) ? $type : false;
	}
	
	/**
	 * Rate limiting to prevent abuse
	 */
	public function rate_limit_check( $action, $max_attempts = 5, $time_window = 300 ) {
		$ip = $this->get_client_ip();
		$key = "rate_limit_{$action}_{$ip}";
		
		$attempts = get_transient( $key );
		if ( false === $attempts ) {
			$attempts = 0;
		}
		
		if ( $attempts >= $max_attempts ) {
			return false;
		}
		
		set_transient( $key, $attempts + 1, $time_window );
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
	
	/**
	 * Validate form data structure
	 */
	public function validate_form_data( $data ) {
		$required_fields = array( 'email', 'assessment_type' );
		$errors = array();
		
		foreach ( $required_fields as $field ) {
			if ( empty( $data[ $field ] ) ) {
				$errors[] = "Missing required field: {$field}";
			}
		}
		
		// Validate email
		if ( ! empty( $data['email'] ) && ! $this->validate_email( $data['email'] ) ) {
			$errors[] = 'Invalid email format';
		}
		
		// Validate assessment type
		if ( ! empty( $data['assessment_type'] ) && ! $this->validate_assessment_type( $data['assessment_type'] ) ) {
			$errors[] = 'Invalid assessment type';
		}
		
		return empty( $errors ) ? true : $errors;
	}
	
	/**
	 * Sanitize all form data
	 */
	public function sanitize_form_data( $data ) {
		$sanitized = array();
		
		foreach ( $data as $key => $value ) {
			$sanitized_key = $this->sanitize_key( $key );
			
			if ( $sanitized_key === 'email' ) {
				$sanitized[ $sanitized_key ] = $this->validate_email( $value );
			} elseif ( $sanitized_key === 'assessment_type' ) {
				$sanitized[ $sanitized_key ] = $this->validate_assessment_type( $value );
			} elseif ( strpos( $sanitized_key, 'phone' ) !== false ) {
				$sanitized[ $sanitized_key ] = $this->sanitize_input( $value, 'text' );
			} elseif ( strpos( $sanitized_key, 'height' ) !== false || strpos( $sanitized_key, 'weight' ) !== false ) {
				$sanitized[ $sanitized_key ] = $this->sanitize_input( $value, 'int' );
			} else {
				$sanitized[ $sanitized_key ] = $this->sanitize_input( $value, 'text' );
			}
		}
		
		return $sanitized;
	}
}
