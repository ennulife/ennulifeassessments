<?php
/**
 * ENNU HIPAA Compliance Class
 * Handles HIPAA compliance measures for medical data
 *
 * @package ENNU_Life
 * @version 64.48.0
 * @since 3.37.14
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HIPAA Compliance for PDF Processing
 * 
 * @package ENNU Life Assessments
 * @since 3.37.14
 */
class ENNU_HIPAA_Compliance {
	
	/**
	 * Encryption key for HIPAA compliance
	 *
	 * @var string
	 */
	private static $encryption_key = null;
	
	/**
	 * Initialize HIPAA compliance system
	 */
	public static function init() {
		// Generate encryption key if not exists
		if ( ! self::$encryption_key ) {
			self::$encryption_key = get_option( 'ennu_hipaa_encryption_key' );
			if ( ! self::$encryption_key ) {
				self::$encryption_key = wp_generate_password( 32, false );
				update_option( 'ennu_hipaa_encryption_key', self::$encryption_key );
			}
		}
		
		// REMOVED: // REMOVED DEBUG LOG: error_log( 'ENNU HIPAA Compliance: Initialized' );
	}
	
	/**
	 * Encrypt biomarker data for HIPAA compliance
	 *
	 * @param array $data Data to encrypt
	 * @return string Encrypted data
	 */
	public static function encrypt_biomarker_data( $data ) {
		if ( ! self::$encryption_key ) {
			self::init();
		}
		
		$json_data = json_encode( $data );
		$encrypted = openssl_encrypt(
			$json_data,
			'AES-256-CBC',
			self::$encryption_key,
			0,
			substr( hash( 'sha256', self::$encryption_key ), 0, 16 )
		);
		
		return base64_encode( $encrypted );
	}
	
	/**
	 * Decrypt biomarker data
	 *
	 * @param string $encrypted_data Encrypted data
	 * @return array|false Decrypted data or false on failure
	 */
	public static function decrypt_biomarker_data( $encrypted_data ) {
		if ( ! self::$encryption_key ) {
			self::init();
		}
		
		$encrypted = base64_decode( $encrypted_data );
		$decrypted = openssl_decrypt(
			$encrypted,
			'AES-256-CBC',
			self::$encryption_key,
			0,
			substr( hash( 'sha256', self::$encryption_key ), 0, 16 )
		);
		
		if ( $decrypted === false ) {
			return false;
		}
		
		return json_decode( $decrypted, true );
	}
	
	/**
	 * Log audit trail for HIPAA compliance
	 *
	 * @param int    $user_id User ID
	 * @param string $action  Action performed
	 * @param array  $details Action details
	 */
	public static function log_audit_trail( $user_id, $action, $details = array() ) {
		$audit_log = array(
			'timestamp' => current_time( 'mysql' ),
			'user_id' => $user_id,
			'action' => $action,
			'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
			'details' => $details,
		);
		
		$existing_logs = get_option( 'ennu_audit_log', array() );
		$existing_logs[] = $audit_log;
		
		// Keep only last 1000 entries for performance
		if ( count( $existing_logs ) > 1000 ) {
			$existing_logs = array_slice( $existing_logs, -1000 );
		}
		
		update_option( 'ennu_audit_log', $existing_logs );
		
		// Also log to WordPress error log for immediate visibility
		// REMOVED: error_log( 'ENNU HIPAA Audit: ' . $action . ' - User: ' . $user_id . ' - Details: ' . json_encode( $details ) );
	}
	
	/**
	 * Secure file upload with virus scanning
	 *
	 * @param array $file File data
	 * @return string|WP_Error Secure file path or error
	 */
	public static function secure_file_upload( $file ) {
		// Virus scanning
		$file_hash = hash_file( 'sha256', $file['tmp_name'] );
		
		// Check against known malicious hashes
		$malicious_hashes = get_option( 'ennu_malicious_file_hashes', array() );
		if ( in_array( $file_hash, $malicious_hashes, true ) ) {
			self::log_audit_trail( get_current_user_id(), 'malicious_file_detected', array(
				'file_hash' => $file_hash,
				'file_name' => $file['name'],
			) );
			return new WP_Error( 'malicious_file', 'File appears to be malicious' );
		}
		
		// Encrypt file content
		$file_content = file_get_contents( $file['tmp_name'] );
		if ( $file_content === false ) {
			return new WP_Error( 'file_read_error', 'Unable to read file for encryption' );
		}
		
		$encrypted_content = self::encrypt_file_content( $file_content );
		
		// Store encrypted version
		$upload_dir = wp_upload_dir();
		$secure_path = $upload_dir['basedir'] . '/ennu-secure/';
		wp_mkdir_p( $secure_path );
		
		$secure_file_path = $secure_path . $file_hash . '.enc';
		$result = file_put_contents( $secure_file_path, $encrypted_content );
		
		if ( $result === false ) {
			return new WP_Error( 'file_write_error', 'Failed to save encrypted file' );
		}
		
		// Log successful secure upload
		self::log_audit_trail( get_current_user_id(), 'secure_file_upload', array(
			'file_hash' => $file_hash,
			'file_name' => $file['name'],
			'file_size' => $file['size'],
			'secure_path' => $secure_file_path,
		) );
		
		return $secure_file_path;
	}
	
	/**
	 * Encrypt file content
	 *
	 * @param string $content File content
	 * @return string Encrypted content
	 */
	private static function encrypt_file_content( $content ) {
		if ( ! self::$encryption_key ) {
			self::init();
		}
		
		return openssl_encrypt(
			$content,
			'AES-256-CBC',
			self::$encryption_key,
			0,
			substr( hash( 'sha256', self::$encryption_key ), 0, 16 )
		);
	}
	
	/**
	 * Decrypt file content
	 *
	 * @param string $encrypted_content Encrypted content
	 * @return string|false Decrypted content or false on failure
	 */
	public static function decrypt_file_content( $encrypted_content ) {
		if ( ! self::$encryption_key ) {
			self::init();
		}
		
		return openssl_decrypt(
			$encrypted_content,
			'AES-256-CBC',
			self::$encryption_key,
			0,
			substr( hash( 'sha256', self::$encryption_key ), 0, 16 )
		);
	}
	
	/**
	 * Get audit log entries
	 *
	 * @param int    $user_id User ID (optional)
	 * @param string $action  Action filter (optional)
	 * @param int    $limit   Number of entries to return
	 * @return array Audit log entries
	 */
	public static function get_audit_log( $user_id = null, $action = null, $limit = 100 ) {
		$audit_logs = get_option( 'ennu_audit_log', array() );
		
		// Filter by user ID if specified
		if ( $user_id ) {
			$audit_logs = array_filter( $audit_logs, function( $log ) use ( $user_id ) {
				return $log['user_id'] == $user_id;
			} );
		}
		
		// Filter by action if specified
		if ( $action ) {
			$audit_logs = array_filter( $audit_logs, function( $log ) use ( $action ) {
				return $log['action'] === $action;
			} );
		}
		
		// Sort by timestamp (newest first)
		usort( $audit_logs, function( $a, $b ) {
			return strtotime( $b['timestamp'] ) - strtotime( $a['timestamp'] );
		} );
		
		// Limit results
		return array_slice( $audit_logs, 0, $limit );
	}
	
	/**
	 * Check if user has access to medical data
	 *
	 * @param int $user_id User ID
	 * @return bool Whether user has access
	 */
	public static function check_medical_data_access( $user_id ) {
		// Check if user has medical director role
		if ( user_can( $user_id, 'ennu_medical_director' ) ) {
			return true;
		}
		
		// Check if user is accessing their own data
		if ( $user_id === get_current_user_id() ) {
			return true;
		}
		
		// Check if user has manage_options capability
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Log medical data access
	 *
	 * @param int    $user_id     User ID
	 * @param int    $target_user Target user ID
	 * @param string $action      Action performed
	 * @param array  $details     Action details
	 */
	public static function log_medical_data_access( $user_id, $target_user, $action, $details = array() ) {
		// Check access permissions
		if ( ! self::check_medical_data_access( $user_id ) ) {
			self::log_audit_trail( $user_id, 'unauthorized_access_attempt', array(
				'target_user' => $target_user,
				'action' => $action,
				'details' => $details,
			) );
			return false;
		}
		
		// Log authorized access
		self::log_audit_trail( $user_id, 'medical_data_access', array(
			'target_user' => $target_user,
			'action' => $action,
			'details' => $details,
		) );
		
		return true;
	}
	
	/**
	 * Clean up old audit logs
	 *
	 * @param int $days_to_keep Number of days to keep logs
	 */
	public static function cleanup_old_audit_logs( $days_to_keep = 90 ) {
		$audit_logs = get_option( 'ennu_audit_log', array() );
		$cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$days_to_keep} days" ) );
		
		$filtered_logs = array_filter( $audit_logs, function( $log ) use ( $cutoff_date ) {
			return strtotime( $log['timestamp'] ) >= strtotime( $cutoff_date );
		} );
		
		update_option( 'ennu_audit_log', $filtered_logs );
		
		// REMOVED: error_log( 'ENNU HIPAA Compliance: Cleaned up ' . ( count( $audit_logs ) - count( $filtered_logs ) ) . ' old audit log entries' );
	}
	
	/**
	 * Export audit log for compliance reporting
	 *
	 * @param string $start_date Start date (Y-m-d)
	 * @param string $end_date   End date (Y-m-d)
	 * @return array Audit log export
	 */
	public static function export_audit_log( $start_date, $end_date ) {
		$audit_logs = get_option( 'ennu_audit_log', array() );
		
		$filtered_logs = array_filter( $audit_logs, function( $log ) use ( $start_date, $end_date ) {
			$log_date = date( 'Y-m-d', strtotime( $log['timestamp'] ) );
			return $log_date >= $start_date && $log_date <= $end_date;
		} );
		
		return array(
			'export_date' => current_time( 'mysql' ),
			'date_range' => array(
				'start_date' => $start_date,
				'end_date' => $end_date,
			),
			'total_entries' => count( $filtered_logs ),
			'entries' => $filtered_logs,
		);
	}
} 