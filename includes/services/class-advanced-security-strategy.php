<?php
/**
 * ENNU Advanced Security Strategy
 *
 * Advanced security strategy implementation with enhanced protection
 *
 * @package ENNU_Life_Assessments
 * @since 64.14.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Advanced Security Strategy Class
 *
 * @since 64.14.0
 */
class ENNU_Advanced_Security_Strategy implements ENNU_Security_Strategy_Interface {
	
	/**
	 * Validate request security
	 *
	 * @param array $request_data Request data to validate
	 * @return array Validation result with errors
	 */
	public function validate_request( $request_data ) {
		$errors = array();
		
		// Enhanced WordPress security checks
		if ( ! is_user_logged_in() ) {
			$errors[] = 'User not logged in';
		}
		
		// Check for required fields
		if ( empty( $request_data['action'] ) ) {
			$errors[] = 'Action not specified';
		}
		
		// Check for suspicious patterns
		if ( $this->contains_suspicious_patterns( $request_data ) ) {
			$errors[] = 'Suspicious request patterns detected';
		}
		
		// Check for SQL injection attempts
		if ( $this->contains_sql_injection( $request_data ) ) {
			$errors[] = 'SQL injection attempt detected';
		}
		
		// Check for XSS attempts
		if ( $this->contains_xss_attempt( $request_data ) ) {
			$errors[] = 'XSS attempt detected';
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
		if ( $user_id === null ) {
			$user_id = get_current_user_id();
		}
		
		// Enhanced token with additional entropy
		$token_data = array(
			'action' => $action,
			'user_id' => $user_id,
			'timestamp' => time(),
			'random' => wp_generate_password( 32, false ),
		);
		
		$token = wp_create_nonce( $action );
		$enhanced_token = base64_encode( json_encode( $token_data ) );
		
		return $token . '.' . $enhanced_token;
	}
	
	/**
	 * Verify security token
	 *
	 * @param string $token Token to verify
	 * @param string $action Action name for the token
	 * @return bool Verification result
	 */
	public function verify_token( $token, $action ) {
		// Split token if it contains enhanced data
		$token_parts = explode( '.', $token );
		$base_token = $token_parts[0];
		
		// Verify base token
		if ( ! wp_verify_nonce( $base_token, $action ) ) {
			return false;
		}
		
		// Verify enhanced token if present
		if ( count( $token_parts ) > 1 ) {
			$enhanced_data = json_decode( base64_decode( $token_parts[1] ), true );
			
			if ( ! $enhanced_data || $enhanced_data['action'] !== $action ) {
				return false;
			}
			
			// Check token expiry (1 hour)
			if ( ( time() - $enhanced_data['timestamp'] ) > 3600 ) {
				return false;
			}
		}
		
		return true;
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
				$data = sanitize_text_field( $data );
				// Additional XSS protection
				$data = $this->remove_xss_patterns( $data );
				return $data;
			case 'email':
				return sanitize_email( $data );
			case 'url':
				return esc_url_raw( $data );
			case 'int':
				return absint( $data );
			case 'float':
				return floatval( $data );
			case 'html':
				$data = wp_kses_post( $data );
				// Additional HTML sanitization
				$data = $this->sanitize_html_content( $data );
				return $data;
			case 'textarea':
				$data = sanitize_textarea_field( $data );
				// Additional XSS protection
				$data = $this->remove_xss_patterns( $data );
				return $data;
			case 'array':
				if ( is_array( $data ) ) {
					return array_map( array( $this, 'sanitize_data' ), $data );
				}
				return array();
			default:
				$data = sanitize_text_field( $data );
				// Additional XSS protection
				$data = $this->remove_xss_patterns( $data );
				return $data;
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
		
		// Enhanced permission checking
		if ( ! user_can( $user_id, $capability ) ) {
			return false;
		}
		
		// Additional security checks
		if ( $this->is_user_suspended( $user_id ) ) {
			return false;
		}
		
		if ( $this->is_ip_blocked() ) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Check for suspicious patterns
	 *
	 * @param array $data Data to check
	 * @return bool True if suspicious patterns found
	 */
	private function contains_suspicious_patterns( $data ) {
		$suspicious_patterns = array(
			'<script',
			'javascript:',
			'onload=',
			'onerror=',
			'onclick=',
			'<iframe',
			'<object',
			'<embed',
		);
		
		$data_string = json_encode( $data );
		
		foreach ( $suspicious_patterns as $pattern ) {
			if ( stripos( $data_string, $pattern ) !== false ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Check for SQL injection attempts
	 *
	 * @param array $data Data to check
	 * @return bool True if SQL injection detected
	 */
	private function contains_sql_injection( $data ) {
		$sql_patterns = array(
			'UNION SELECT',
			'DROP TABLE',
			'DELETE FROM',
			'INSERT INTO',
			'UPDATE SET',
			'OR 1=1',
			'OR 1=0',
			'--',
			'/*',
			'*/',
		);
		
		$data_string = json_encode( $data );
		
		foreach ( $sql_patterns as $pattern ) {
			if ( stripos( $data_string, $pattern ) !== false ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Check for XSS attempts
	 *
	 * @param array $data Data to check
	 * @return bool True if XSS attempt detected
	 */
	private function contains_xss_attempt( $data ) {
		$xss_patterns = array(
			'<script',
			'javascript:',
			'vbscript:',
			'onload=',
			'onerror=',
			'onclick=',
			'onmouseover=',
			'onfocus=',
			'onblur=',
		);
		
		$data_string = json_encode( $data );
		
		foreach ( $xss_patterns as $pattern ) {
			if ( stripos( $data_string, $pattern ) !== false ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Remove XSS patterns from data
	 *
	 * @param string $data Data to clean
	 * @return string Cleaned data
	 */
	private function remove_xss_patterns( $data ) {
		$xss_patterns = array(
			'/<script[^>]*>.*?<\/script>/is',
			'/javascript:/i',
			'/vbscript:/i',
			'/onload\s*=/i',
			'/onerror\s*=/i',
			'/onclick\s*=/i',
			'/onmouseover\s*=/i',
			'/onfocus\s*=/i',
			'/onblur\s*=/i',
		);
		
		foreach ( $xss_patterns as $pattern ) {
			$data = preg_replace( $pattern, '', $data );
		}
		
		return $data;
	}
	
	/**
	 * Sanitize HTML content
	 *
	 * @param string $html HTML content to sanitize
	 * @return string Sanitized HTML
	 */
	private function sanitize_html_content( $html ) {
		// Remove potentially dangerous tags
		$dangerous_tags = array( 'script', 'iframe', 'object', 'embed', 'form', 'input' );
		
		foreach ( $dangerous_tags as $tag ) {
			$html = preg_replace( "/<{$tag}[^>]*>.*?<\/{$tag}>/is", '', $html );
			$html = preg_replace( "/<{$tag}[^>]*\/?>/i", '', $html );
		}
		
		return $html;
	}
	
	/**
	 * Check if user is suspended
	 *
	 * @param int $user_id User ID
	 * @return bool True if user is suspended
	 */
	private function is_user_suspended( $user_id ) {
		$suspended = get_user_meta( $user_id, 'ennu_suspended', true );
		return ! empty( $suspended );
	}
	
	/**
	 * Check if IP is blocked
	 *
	 * @return bool True if IP is blocked
	 */
	private function is_ip_blocked() {
		$ip = $this->get_client_ip();
		$blocked_ips = get_option( 'ennu_blocked_ips', array() );
		
		return in_array( $ip, $blocked_ips );
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
} 