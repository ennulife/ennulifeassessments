<?php
/**
 * ENNU YAML Configuration Strategy
 *
 * YAML file-based configuration strategy implementation
 *
 * @package ENNU_Life_Assessments
 * @since 64.13.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU YAML Configuration Strategy Class
 *
 * @since 64.13.0
 */
class ENNU_YAML_Configuration_Strategy implements ENNU_Configuration_Strategy_Interface {
	
	/**
	 * Load configuration from YAML file
	 *
	 * @param string $config_key Configuration key to load
	 * @return array Configuration data
	 */
	public function load_configuration( $config_key ) {
		$file_path = $this->get_config_file_path( $config_key );
		
		if ( ! file_exists( $file_path ) ) {
			error_log( "ENNU YAML Configuration Strategy: Configuration file not found: {$file_path}" );
			return array();
		}
		
		$yaml_content = file_get_contents( $file_path );
		
		// Use Symfony YAML parser if available, otherwise fallback to basic parsing
		if ( class_exists( 'Symfony\Component\Yaml\Yaml' ) ) {
			$config = \Symfony\Component\Yaml\Yaml::parse( $yaml_content );
		} else {
			$config = $this->parse_yaml_basic( $yaml_content );
		}
		
		return $config ?? array();
	}
	
	/**
	 * Save configuration to YAML file
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
		
		// Use Symfony YAML dumper if available, otherwise fallback to basic formatting
		if ( class_exists( 'Symfony\Component\Yaml\Yaml' ) ) {
			$yaml_content = \Symfony\Component\Yaml\Yaml::dump( $data, 4, 2 );
		} else {
			$yaml_content = $this->format_yaml_basic( $data );
		}
		
		$result = file_put_contents( $file_path, $yaml_content );
		
		if ( $result === false ) {
			error_log( "ENNU YAML Configuration Strategy: Failed to write configuration file: {$file_path}" );
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
	 * Basic YAML parser (fallback)
	 *
	 * @param string $yaml_content YAML content
	 * @return array Parsed data
	 */
	private function parse_yaml_basic( $yaml_content ) {
		// Basic YAML parsing for simple key-value pairs
		$lines = explode( "\n", $yaml_content );
		$config = array();
		
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( empty( $line ) || strpos( $line, '#' ) === 0 ) {
				continue;
			}
			
			if ( strpos( $line, ':' ) !== false ) {
				list( $key, $value ) = explode( ':', $line, 2 );
				$key = trim( $key );
				$value = trim( $value );
				
				// Remove quotes if present
				$value = trim( $value, '"\' ' );
				
				$config[ $key ] = $value;
			}
		}
		
		return $config;
	}
	
	/**
	 * Basic YAML formatter (fallback)
	 *
	 * @param array $data Data to format
	 * @return string Formatted YAML
	 */
	private function format_yaml_basic( $data ) {
		$yaml = '';
		
		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$yaml .= $key . ":\n";
				foreach ( $value as $sub_key => $sub_value ) {
					$yaml .= "  " . $sub_key . ": " . $sub_value . "\n";
				}
			} else {
				$yaml .= $key . ": " . $value . "\n";
			}
		}
		
		return $yaml;
	}
	
	/**
	 * Get configuration file path
	 *
	 * @param string $config_key Configuration key
	 * @return string File path
	 */
	private function get_config_file_path( $config_key ) {
		return ENNU_LIFE_PLUGIN_PATH . 'config/' . $config_key . '.yml';
	}
} 