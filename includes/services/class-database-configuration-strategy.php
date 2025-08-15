<?php
/**
 * ENNU Database Configuration Strategy
 *
 * Database-based configuration strategy implementation
 *
 * @package ENNU_Life_Assessments
 * @since 64.13.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Database Configuration Strategy Class
 *
 * @since 64.13.0
 */
class ENNU_Database_Configuration_Strategy implements ENNU_Configuration_Strategy_Interface {
	
	/**
	 * Load configuration from database
	 *
	 * @param string $config_key Configuration key to load
	 * @return array Configuration data
	 */
	public function load_configuration( $config_key ) {
		global $wpdb;
		
		$option_name = 'ennu_config_' . $config_key;
		$config = get_option( $option_name, array() );
		
		if ( empty( $config ) ) {
			// REMOVED: error_log( "ENNU Database Configuration Strategy: Configuration not found in database: {$config_key}" );
			return array();
		}
		
		return $config;
	}
	
	/**
	 * Save configuration to database
	 *
	 * @param string $config_key Configuration key
	 * @param array $data Configuration data
	 * @return bool Success status
	 */
	public function save_configuration( $config_key, $data ) {
		$option_name = 'ennu_config_' . $config_key;
		$result = update_option( $option_name, $data );
		
		if ( ! $result ) {
			// REMOVED: error_log( "ENNU Database Configuration Strategy: Failed to save configuration to database: {$config_key}" );
			return false;
		}
		
		return true;
	}
	
	/**
	 * Validate configuration data
	 *
	 * @param array $data Configuration data to validate
	 * @return array Validation result with errors
	 */
	public function validate_configuration( $data ) {
		$errors = array();
		
		// Basic validation - ensure data is array
		if ( ! is_array( $data ) ) {
			$errors[] = 'Configuration data must be an array';
		}
		
		return array(
			'valid' => empty( $errors ),
			'errors' => $errors,
		);
	}
} 