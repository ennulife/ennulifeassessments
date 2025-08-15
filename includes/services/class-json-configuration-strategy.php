<?php
/**
 * ENNU JSON Configuration Strategy
 *
 * JSON file-based configuration strategy implementation
 *
 * @package ENNU_Life_Assessments
 * @since 64.13.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU JSON Configuration Strategy Class
 *
 * @since 64.13.0
 */
class ENNU_JSON_Configuration_Strategy implements ENNU_Configuration_Strategy_Interface {
	
	/**
	 * Load configuration from JSON file
	 *
	 * @param string $config_key Configuration key to load
	 * @return array Configuration data
	 */
	public function load_configuration( $config_key ) {
		$file_path = $this->get_config_file_path( $config_key );
		
		if ( ! file_exists( $file_path ) ) {
			// REMOVED: error_log( "ENNU JSON Configuration Strategy: Configuration file not found: {$file_path}" );
			return array();
		}
		
		$json_content = file_get_contents( $file_path );
		$config = json_decode( $json_content, true );
		
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			error_log( "ENNU JSON Configuration Strategy: JSON decode error: " . json_last_error_msg() );
			return array();
		}
		
		return $config ?? array();
	}
	
	/**
	 * Save configuration to JSON file
	 *
	 * @param string $config_key Configuration key
	 * @param array $data Configuration data
	 * @return bool Success status
	 */
	public function save_configuration( $config_key, $data ) {
		$file_path = $this->get_config_file_path( $config_key );
		
		// Ensure directory exists
		$dir = dirname( $file_path );
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		
		$json_content = json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			error_log( "ENNU JSON Configuration Strategy: JSON encode error: " . json_last_error_msg() );
			return false;
		}
		
		$result = file_put_contents( $file_path, $json_content );
		
		if ( $result === false ) {
			// REMOVED: error_log( "ENNU JSON Configuration Strategy: Failed to write configuration file: {$file_path}" );
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
	
	/**
	 * Get configuration file path
	 *
	 * @param string $config_key Configuration key
	 * @return string File path
	 */
	private function get_config_file_path( $config_key ) {
		return ENNU_LIFE_PLUGIN_PATH . 'config/' . $config_key . '.json';
	}
} 