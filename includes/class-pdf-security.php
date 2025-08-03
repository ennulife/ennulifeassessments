<?php
/**
 * PDF Security Class for LabCorp Integration
 * 
 * @package ENNU Life Assessments
 * @since 3.37.14
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PDF Security Class for LabCorp Integration
 * 
 * @package ENNU Life Assessments
 * @since 3.37.14
 */
class ENNU_PDF_Security {
	
	/**
	 * PDF-specific security configuration
	 *
	 * @var array
	 */
	private static $pdf_config = array(
		'max_file_size' => 10 * 1024 * 1024, // 10MB
		'allowed_mime_types' => array( 'application/pdf' ),
		'enable_virus_scanning' => true,
		'enable_file_encryption' => true,
		'max_processing_time' => 30, // seconds
	);
	
	/**
	 * Validate PDF upload with existing security patterns
	 * 
	 * @param int $user_id User ID
	 * @return bool|WP_Error Validation result
	 */
	public static function validate_pdf_upload( $user_id ) {
		// Basic nonce validation
		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'ennu_biomarker_admin_nonce' ) ) {
			return new WP_Error( 'invalid_nonce', 'Security check failed' );
		}
		
		// Check user permissions
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'ennu_medical_director' ) ) {
			return new WP_Error( 'insufficient_permissions', 'Insufficient permissions' );
		}
		
		// Enhanced security validation using AJAX Security if available
		if ( class_exists( 'ENNU_AJAX_Security' ) ) {
			$security_check = ENNU_AJAX_Security::validate_ajax_request( 'pdf_upload', $user_id );
			if ( is_wp_error( $security_check ) ) {
				return $security_check;
			}
		} else {
			// Fallback security checks when AJAX Security is not available
			$fallback_check = self::perform_fallback_security_checks( $user_id );
			if ( is_wp_error( $fallback_check ) ) {
				return $fallback_check;
			}
		}
		
		// Additional PDF-specific security
		$file = $_FILES['labcorp_pdf'] ?? null;
		if ( ! $file ) {
			return new WP_Error( 'no_file', 'No PDF file uploaded' );
		}
		
		// File size validation (10MB max)
		if ( $file['size'] > self::$pdf_config['max_file_size'] ) {
			return new WP_Error( 'file_too_large', 'File size exceeds 10MB limit' );
		}
		
		// File type validation
		$finfo = finfo_open( FILEINFO_MIME_TYPE );
		$mime_type = finfo_file( $finfo, $file['tmp_name'] );
		finfo_close( $finfo );
		
		if ( ! in_array( $mime_type, self::$pdf_config['allowed_mime_types'], true ) ) {
			return new WP_Error( 'invalid_file_type', 'File is not a valid PDF' );
		}
		
		// Virus scanning (if enabled)
		if ( self::$pdf_config['enable_virus_scanning'] ) {
			$virus_check = self::scan_for_viruses( $file['tmp_name'] );
			if ( is_wp_error( $virus_check ) ) {
				return $virus_check;
			}
		}
		
		// File content validation
		$content_check = self::validate_pdf_content( $file['tmp_name'] );
		if ( is_wp_error( $content_check ) ) {
			return $content_check;
		}
		
		return true;
	}
	
	/**
	 * Perform fallback security checks when AJAX Security is not available
	 *
	 * @param int $user_id User ID
	 * @return bool|WP_Error Security check result
	 */
	private static function perform_fallback_security_checks( $user_id ) {
		// Rate limiting check
		$rate_limit_key = 'pdf_upload_' . $user_id;
		$current_time = time();
		$last_upload = get_transient( $rate_limit_key );
		
		if ( $last_upload && ( $current_time - $last_upload ) < 60 ) {
			return new WP_Error( 'rate_limit_exceeded', 'Upload rate limit exceeded. Please wait before uploading another file.' );
		}
		
		// Set rate limit
		set_transient( $rate_limit_key, $current_time, 60 );
		
		// IP validation
		$client_ip = self::get_client_ip();
		$blocked_ips = get_option( 'ennu_blocked_ips', array() );
		
		if ( in_array( $client_ip, $blocked_ips, true ) ) {
			return new WP_Error( 'ip_blocked', 'Your IP address is blocked from uploading files.' );
		}
		
		return true;
	}
	
	/**
	 * Get client IP address
	 *
	 * @return string Client IP
	 */
	private static function get_client_ip() {
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
	 * Scan file for viruses
	 *
	 * @param string $file_path File path
	 * @return bool|WP_Error Scan result
	 */
	private static function scan_for_viruses( $file_path ) {
		// Check file hash against known malicious hashes
		$file_hash = hash_file( 'sha256', $file_path );
		$malicious_hashes = get_option( 'ennu_malicious_file_hashes', array() );
		
		if ( in_array( $file_hash, $malicious_hashes, true ) ) {
			return new WP_Error( 'malicious_file', 'File appears to be malicious' );
		}
		
		// Additional virus scanning logic can be added here
		// For now, we'll rely on hash checking and MIME type validation
		
		return true;
	}
	
	/**
	 * Validate PDF content
	 *
	 * @param string $file_path File path
	 * @return bool|WP_Error Validation result
	 */
	private static function validate_pdf_content( $file_path ) {
		// Check if file starts with PDF signature
		$handle = fopen( $file_path, 'rb' );
		if ( ! $handle ) {
			return new WP_Error( 'file_read_error', 'Unable to read file' );
		}
		
		$header = fread( $handle, 8 );
		fclose( $handle );
		
		// PDF files should start with "%PDF-"
		if ( strpos( $header, '%PDF-' ) !== 0 ) {
			return new WP_Error( 'invalid_pdf_content', 'File does not appear to be a valid PDF' );
		}
		
		return true;
	}
	
	/**
	 * Encrypt PDF file for secure storage
	 *
	 * @param string $file_path Original file path
	 * @return string|WP_Error Encrypted file path or error
	 */
	public static function encrypt_pdf_file( $file_path ) {
		if ( ! self::$pdf_config['enable_file_encryption'] ) {
			return $file_path; // Return original if encryption disabled
		}
		
		try {
			$file_content = file_get_contents( $file_path );
			if ( $file_content === false ) {
				return new WP_Error( 'file_read_error', 'Unable to read file for encryption' );
			}
			
			// Generate encryption key
			$encryption_key = wp_generate_password( 32, false );
			
			// Encrypt file content
			$encrypted_content = openssl_encrypt(
				$file_content,
				'AES-256-CBC',
				$encryption_key,
				0,
				substr( hash( 'sha256', $encryption_key ), 0, 16 )
			);
			
			if ( $encrypted_content === false ) {
				return new WP_Error( 'encryption_failed', 'Failed to encrypt file' );
			}
			
			// Store encrypted file
			$upload_dir = wp_upload_dir();
			$secure_dir = $upload_dir['basedir'] . '/ennu-secure/';
			wp_mkdir_p( $secure_dir );
			
			$file_hash = hash_file( 'sha256', $file_path );
			$encrypted_path = $secure_dir . $file_hash . '.enc';
			
			$result = file_put_contents( $encrypted_path, $encrypted_content );
			if ( $result === false ) {
				return new WP_Error( 'file_write_error', 'Failed to save encrypted file' );
			}
			
			// Store encryption key securely (in practice, this would be in a secure key management system)
			update_option( 'ennu_pdf_encryption_key_' . $file_hash, $encryption_key );
			
			return $encrypted_path;
			
		} catch ( Exception $e ) {
			return new WP_Error( 'encryption_error', 'Encryption error: ' . $e->getMessage() );
		}
	}
	
	/**
	 * Decrypt PDF file
	 *
	 * @param string $encrypted_path Encrypted file path
	 * @return string|WP_Error Decrypted file path or error
	 */
	public static function decrypt_pdf_file( $encrypted_path ) {
		try {
			$encrypted_content = file_get_contents( $encrypted_path );
			if ( $encrypted_content === false ) {
				return new WP_Error( 'file_read_error', 'Unable to read encrypted file' );
			}
			
			// Extract file hash from path
			$file_hash = basename( $encrypted_path, '.enc' );
			
			// Retrieve encryption key
			$encryption_key = get_option( 'ennu_pdf_encryption_key_' . $file_hash );
			if ( ! $encryption_key ) {
				return new WP_Error( 'key_not_found', 'Encryption key not found' );
			}
			
			// Decrypt file content
			$decrypted_content = openssl_decrypt(
				$encrypted_content,
				'AES-256-CBC',
				$encryption_key,
				0,
				substr( hash( 'sha256', $encryption_key ), 0, 16 )
			);
			
			if ( $decrypted_content === false ) {
				return new WP_Error( 'decryption_failed', 'Failed to decrypt file' );
			}
			
			// Create temporary file for processing
			$temp_dir = get_temp_dir();
			$temp_path = $temp_dir . 'ennu_pdf_' . uniqid() . '.pdf';
			
			$result = file_put_contents( $temp_path, $decrypted_content );
			if ( $result === false ) {
				return new WP_Error( 'file_write_error', 'Failed to create temporary file' );
			}
			
			return $temp_path;
			
		} catch ( Exception $e ) {
			return new WP_Error( 'decryption_error', 'Decryption error: ' . $e->getMessage() );
		}
	}
	
	/**
	 * Log PDF security events
	 *
	 * @param string $event_type Event type
	 * @param array  $data       Event data
	 */
	public static function log_pdf_security_event( $event_type, $data = array() ) {
		$log_entry = array(
			'timestamp' => current_time( 'mysql' ),
			'event_type' => $event_type,
			'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
			'data' => $data,
		);
		
		// Add to existing security log
		$existing_log = get_option( 'ennu_pdf_security_log', array() );
		$existing_log[] = $log_entry;
		
		// Keep only last 1000 entries for performance
		if ( count( $existing_log ) > 1000 ) {
			$existing_log = array_slice( $existing_log, -1000 );
		}
		
		update_option( 'ennu_pdf_security_log', $existing_log );
		
		// Also log to WordPress error log
		error_log( 'ENNU PDF Security: ' . $event_type . ' - ' . json_encode( $data ) );
	}
	
	/**
	 * Configure PDF security settings
	 *
	 * @param array $config Configuration array
	 */
	public static function configure_pdf_security( $config ) {
		self::$pdf_config = array_merge( self::$pdf_config, $config );
	}
	
	/**
	 * Get PDF security configuration
	 *
	 * @return array Configuration array
	 */
	public static function get_pdf_security_config() {
		return self::$pdf_config;
	}
} 