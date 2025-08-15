<?php
/**
 * ENNU Error Handler
 * Comprehensive error handling and logging system
 *
 * @package ENNU_Life_Assessments
 * @version 62.28.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced error handling system for ENNU Life Assessments
 */
class ENNU_Error_Handler {
	
	private static $instance = null;
	private $error_log = array();
	private $critical_errors = array();
	
	const ERROR_TYPES = array(
		'database_error' => 'Database operation failed',
		'validation_error' => 'Data validation failed',
		'security_error' => 'Security verification failed',
		'user_creation_error' => 'User creation failed',
		'form_processing_error' => 'Form processing failed',
		'biomarker_error' => 'Biomarker processing failed',
		'scoring_error' => 'Score calculation failed',
		'general_error' => 'General system error'
	);
	
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Handle form errors with comprehensive logging and user-friendly messages
	 */
	public function handle_form_error( $error, $context = array() ) {
		// Log detailed error information
		$this->log_detailed_error( $error, $context );
		
		// Determine error type and response
		$error_type = $this->classify_error( $error );
		
		// Generate user-friendly message
		$user_message = $this->get_user_friendly_message( $error_type );
		
		// Send notification if critical
		if ( $this->is_critical_error( $error_type ) ) {
			$this->send_critical_error_notification( $error, $context );
		}
		
		return array(
			'error_type' => $error_type,
			'user_message' => $user_message,
			'error_code' => $this->generate_error_code( $error ),
			'timestamp' => current_time( 'mysql' )
		);
	}
	
	/**
	 * Classify error type based on error content
	 */
	private function classify_error( $error ) {
		$error_message = is_string( $error ) ? $error : $error->getMessage();
		$error_lower = strtolower( $error_message );
		
		if ( strpos( $error_lower, 'database' ) !== false || strpos( $error_lower, 'mysql' ) !== false ) {
			return 'database_error';
		} elseif ( strpos( $error_lower, 'validation' ) !== false || strpos( $error_lower, 'invalid' ) !== false ) {
			return 'validation_error';
		} elseif ( strpos( $error_lower, 'security' ) !== false || strpos( $error_lower, 'nonce' ) !== false ) {
			return 'security_error';
		} elseif ( strpos( $error_lower, 'user' ) !== false && strpos( $error_lower, 'creation' ) !== false ) {
			return 'user_creation_error';
		} elseif ( strpos( $error_lower, 'form' ) !== false ) {
			return 'form_processing_error';
		} elseif ( strpos( $error_lower, 'biomarker' ) !== false ) {
			return 'biomarker_error';
		} elseif ( strpos( $error_lower, 'score' ) !== false ) {
			return 'scoring_error';
		} else {
			return 'general_error';
		}
	}
	
	/**
	 * Get user-friendly error message
	 */
	private function get_user_friendly_message( $error_type ) {
		$messages = array(
			'database_error' => 'We\'re experiencing technical difficulties. Please try again in a few minutes.',
			'validation_error' => 'Please check your information and try again.',
			'security_error' => 'Security verification failed. Please refresh the page and try again.',
			'user_creation_error' => 'There was an issue creating your account. Please try again or contact support.',
			'form_processing_error' => 'There was an issue processing your form. Please try again.',
			'biomarker_error' => 'There was an issue processing your lab results. Please try again.',
			'scoring_error' => 'There was an issue calculating your scores. Please try again.',
			'general_error' => 'An unexpected error occurred. Please try again.'
		);
		
		return $messages[$error_type] ?? $messages['general_error'];
	}
	
	/**
	 * Check if error is critical
	 */
	private function is_critical_error( $error_type ) {
		$critical_types = array( 'database_error', 'security_error', 'user_creation_error' );
		return in_array( $error_type, $critical_types );
	}
	
	/**
	 * Log detailed error information
	 */
	private function log_detailed_error( $error, $context = array() ) {
		$error_data = array(
			'message' => is_string( $error ) ? $error : $error->getMessage(),
			'type' => is_object( $error ) ? get_class( $error ) : 'string',
			'file' => is_object( $error ) ? $error->getFile() : '',
			'line' => is_object( $error ) ? $error->getLine() : '',
			'context' => $context,
			'timestamp' => current_time( 'mysql' ),
			'user_id' => get_current_user_id(),
			'url' => $_SERVER['REQUEST_URI'] ?? '',
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
		);
		
		$this->error_log[] = $error_data;
		
		// Log to WordPress error log
		error_log( 'ENNU Error: ' . json_encode( $error_data ) );
	}
	
	/**
	 * Send critical error notification
	 */
	private function send_critical_error_notification( $error, $context = array() ) {
		$admin_email = get_option( 'admin_email' );
		$site_name = get_bloginfo( 'name' );
		
		$subject = "Critical Error in {$site_name} - ENNU Life Assessments";
		$message = "A critical error occurred in the ENNU Life Assessments plugin:\n\n";
		$message .= "Error: " . ( is_string( $error ) ? $error : $error->getMessage() ) . "\n";
		$message .= "Context: " . json_encode( $context ) . "\n";
		$message .= "Time: " . current_time( 'mysql' ) . "\n";
		$message .= "User ID: " . get_current_user_id() . "\n";
		$message .= "URL: " . ( $_SERVER['REQUEST_URI'] ?? '' ) . "\n";
		
		wp_mail( $admin_email, $subject, $message );
	}
	
	/**
	 * Generate unique error code
	 */
	private function generate_error_code( $error ) {
		$error_string = is_string( $error ) ? $error : $error->getMessage();
		return 'ENNU-' . substr( md5( $error_string . time() ), 0, 8 );
	}
	
	/**
	 * Get error statistics
	 */
	public function get_error_statistics() {
		$stats = array();
		
		foreach ( self::ERROR_TYPES as $type => $description ) {
			$stats[$type] = 0;
		}
		
		foreach ( $this->error_log as $error ) {
			$error_type = $this->classify_error( $error['message'] );
			if ( isset( $stats[$error_type] ) ) {
				$stats[$error_type]++;
			}
		}
		
		return $stats;
	}
	
	/**
	 * Clear error log
	 */
	// REMOVED: public function clear_error_log() {
		$this->error_log = array();
	}
} 