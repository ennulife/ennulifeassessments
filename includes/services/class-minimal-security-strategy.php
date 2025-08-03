<?php
/**
 * ENNU Minimal Security Strategy
 *
 * Minimal security strategy implementation for basic protection
 *
 * @package ENNU_Life_Assessments
 * @since 64.14.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Minimal Security Strategy Class
 *
 * @since 64.14.0
 */
class ENNU_Minimal_Security_Strategy implements ENNU_Security_Strategy_Interface {
	
	/**
	 * Validate request security
	 *
	 * @param array $request_data Request data to validate
	 * @return array Validation result with errors
	 */
	public function validate_request( $request_data ) {
		$errors = array();
		
		// Basic validation only
		if ( empty( $request_data['action'] ) ) {
			$errors[] = 'Action not specified';
		}
		
		return array(
			'valid' => empty( $errors ),
			'errors' => $errors,
		);
	}
	
	/**
	 * Generate security token
	 *
	 * @param string $action Action name for the token
	 * @param int $user_id User ID (optional)
	 * @return string Security token
	 */
	public function generate_token( $action, $user_id = null ) {
		// Simple token generation
		return wp_create_nonce( $action );
	}
	
	/**
	 * Verify security token
	 *
	 * @param string $token Token to verify
	 * @param string $action Action name for the token
	 * @return bool Verification result
	 */
	public function verify_token( $token, $action ) {
		// Simple token verification
		return wp_verify_nonce( $token, $action );
	}
	
	/**
	 * Sanitize input data
	 *
	 * @param mixed $data Data to sanitize
	 * @param string $type Data type for sanitization
	 * @return mixed Sanitized data
	 */
	public function sanitize_data( $data, $type = 'text' ) {
		switch ( $type ) {
			case 'text':
				return sanitize_text_field( $data );
			case 'email':
				return sanitize_email( $data );
			case 'url':
				return esc_url_raw( $data );
			case 'int':
				return absint( $data );
			case 'float':
				return floatval( $data );
			case 'html':
				return wp_kses_post( $data );
			case 'textarea':
				return sanitize_textarea_field( $data );
			case 'array':
				if ( is_array( $data ) ) {
					return array_map( array( $this, 'sanitize_data' ), $data );
				}
				return array();
			default:
				return sanitize_text_field( $data );
		}
	}
	
	/**
	 * Check user permissions
	 *
	 * @param string $capability Required capability
	 * @param int $user_id User ID (optional)
	 * @return bool Permission check result
	 */
	public function check_permissions( $capability, $user_id = null ) {
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}
		
		// Basic permission check only
		return user_can( $user_id, $capability );
	}
} 