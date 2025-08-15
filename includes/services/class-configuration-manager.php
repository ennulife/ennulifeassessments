<?php
/**
 * ENNU Configuration Manager Service
 *
 * Externalizes all business logic to configuration files using strategy pattern
 *
 * @package ENNU_Life_Assessments
 * @since 64.13.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configuration Strategy Interface
 *
 * @since 64.13.0
 */
interface ENNU_Configuration_Strategy_Interface {
	
	/**
	 * Load configuration from source
	 *
	 * @param string $config_key Configuration key to load
	 * @return array Configuration data
	 */
	public function load_configuration( $config_key );
	
	/**
	 * Save configuration to source
	 *
	 * @param string $config_key Configuration key
	 * @param array $data Configuration data
	 * @return bool Success status
	 */
	public function save_configuration( $config_key, $data );
	
	/**
	 * Validate configuration data
	 *
	 * @param array $data Configuration data to validate
	 * @return array Validation result with errors
	 */
	public function validate_configuration( $data );
}

/**
 * ENNU Configuration Manager Class
 *
 * @since 64.13.0
 */
class ENNU_Configuration_Manager {
	
	/**
	 * Configuration strategies
	 *
	 * @var array
	 */
	private $strategies = array();
	
	/**
	 * Current strategy
	 *
	 * @var ENNU_Configuration_Strategy_Interface
	 */
	private $current_strategy;
	
	/**
	 * Configuration cache
	 *
	 * @var array
	 */
	private $cache = array();
	
	/**
	 * Supported configuration types
	 *
	 * @var array
	 */
	private $supported_types = array(
		'scoring_weights' => array(
			'name' => 'Scoring Weights',
			'description' => 'Biomarker and symptom scoring weights',
			'default_file' => 'scoring-weights.json',
			'validation_rules' => array(
				'biomarker_weight' => 'required|numeric|min:0|max:1',
				'symptom_weight' => 'required|numeric|min:0|max:1',
				'biomarkers' => 'required|array',
				'symptoms' => 'required|array',
			),
		),
		'biomarker_ranges' => array(
			'name' => 'Biomarker Reference Ranges',
			'description' => 'Biomarker reference ranges and classifications',
			'default_file' => 'biomarker-ranges.json',
			'validation_rules' => array(
				'biomarkers' => 'required|array',
				'categories' => 'required|array',
			),
		),
		'assessment_definitions' => array(
			'name' => 'Assessment Definitions',
			'description' => 'Assessment type definitions and configurations',
			'default_file' => 'assessment-definitions.json',
			'validation_rules' => array(
				'assessments' => 'required|array',
				'types' => 'required|array',
			),
		),
		'validation_rules' => array(
			'name' => 'Validation Rules',
			'description' => 'Form validation rules and messages',
			'default_file' => 'validation-rules.json',
			'validation_rules' => array(
				'rules' => 'required|array',
				'messages' => 'required|array',
			),
		),
	);
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->register_strategies();
		$this->set_default_strategy();
	}
	
	/**
	 * Register configuration strategies
	 */
	private function register_strategies() {
		// JSON file strategy
		$this->strategies['json'] = new ENNU_JSON_Configuration_Strategy();
		
		// YAML file strategy
		$this->strategies['yaml'] = new ENNU_YAML_Configuration_Strategy();
		
		// Database strategy
		$this->strategies['database'] = new ENNU_Database_Configuration_Strategy();
		
		// WordPress options strategy
		$this->strategies['options'] = new ENNU_Options_Configuration_Strategy();
	}
	
	/**
	 * Set default strategy
	 */
	private function set_default_strategy() {
		$this->current_strategy = $this->strategies['json'];
	}
	
	/**
	 * Set configuration strategy
	 *
	 * @param string $strategy_name Strategy name
	 * @return bool Success status
	 */
	public function set_strategy( $strategy_name ) {
		if ( ! isset( $this->strategies[ $strategy_name ] ) ) {
			// REMOVED: error_log( "ENNU Configuration Manager: Unknown strategy '{$strategy_name}'" );
			return false;
		}
		
		$this->current_strategy = $this->strategies[ $strategy_name ];
		// REMOVED: error_log( "ENNU Configuration Manager: Strategy set to '{$strategy_name}'" );
		return true;
	}
	
	/**
	 * Get configuration
	 *
	 * @param string $config_key Configuration key
	 * @param bool $use_cache Whether to use cache
	 * @return array Configuration data
	 */
	public function get_configuration( $config_key, $use_cache = true ) {
		// Check cache first
		if ( $use_cache && isset( $this->cache[ $config_key ] ) ) {
			return $this->cache[ $config_key ];
		}
		
		// Load configuration
		$config = $this->current_strategy->load_configuration( $config_key );
		
		// Validate configuration
		$validation = $this->validate_configuration( $config_key, $config );
		if ( ! $validation['valid'] ) {
			// REMOVED: error_log( "ENNU Configuration Manager: Configuration validation failed for '{$config_key}': " . implode( ', ', $validation['errors'] ) );
			return $this->get_default_configuration( $config_key );
		}
		
		// Cache configuration
		$this->cache[ $config_key ] = $config;
		
		return $config;
	}
	
	/**
	 * Save configuration
	 *
	 * @param string $config_key Configuration key
	 * @param array $data Configuration data
	 * @return bool Success status
	 */
	public function save_configuration( $config_key, $data ) {
		// Validate configuration
		$validation = $this->validate_configuration( $config_key, $data );
		if ( ! $validation['valid'] ) {
			// REMOVED: error_log( "ENNU Configuration Manager: Configuration validation failed for '{$config_key}': " . implode( ', ', $validation['errors'] ) );
			return false;
		}
		
		// Save configuration
		$result = $this->current_strategy->save_configuration( $config_key, $data );
		
		if ( $result ) {
			// Update cache
			$this->cache[ $config_key ] = $data;
			// REMOVED: error_log( "ENNU Configuration Manager: Configuration saved for '{$config_key}'" );
		}
		
		return $result;
	}
	
	/**
	 * Validate configuration
	 *
	 * @param string $config_key Configuration key
	 * @param array $data Configuration data
	 * @return array Validation result
	 */
	private function validate_configuration( $config_key, $data ) {
		if ( ! isset( $this->supported_types[ $config_key ] ) ) {
			return array(
				'valid' => false,
				'errors' => array( "Unknown configuration type: {$config_key}" ),
			);
		}
		
		$type_config = $this->supported_types[ $config_key ];
		$validation_rules = $type_config['validation_rules'] ?? array();
		
		$errors = array();
		
		foreach ( $validation_rules as $field => $rules ) {
			$field_errors = $this->validate_field( $data[ $field ] ?? null, $rules );
			$errors = array_merge( $errors, $field_errors );
		}
		
		return array(
			'valid' => empty( $errors ),
			'errors' => $errors,
		);
	}
	
	/**
	 * Validate field
	 *
	 * @param mixed $value Field value
	 * @param string $rules Validation rules
	 * @return array Validation errors
	 */
	private function validate_field( $value, $rules ) {
		$errors = array();
		$rule_array = explode( '|', $rules );
		
		foreach ( $rule_array as $rule ) {
			$rule_parts = explode( ':', $rule );
			$rule_name = $rule_parts[0];
			$rule_value = $rule_parts[1] ?? null;
			
			switch ( $rule_name ) {
				case 'required':
					if ( empty( $value ) ) {
						$errors[] = "Field is required";
					}
					break;
					
				case 'numeric':
					if ( ! is_numeric( $value ) ) {
						$errors[] = "Field must be numeric";
					}
					break;
					
				case 'min':
					if ( is_numeric( $value ) && $value < $rule_value ) {
						$errors[] = "Field must be at least {$rule_value}";
					}
					break;
					
				case 'max':
					if ( is_numeric( $value ) && $value > $rule_value ) {
						$errors[] = "Field must be at most {$rule_value}";
					}
					break;
					
				case 'array':
					if ( ! is_array( $value ) ) {
						$errors[] = "Field must be an array";
					}
					break;
			}
		}
		
		return $errors;
	}
	
	/**
	 * Get default configuration
	 *
	 * @param string $config_key Configuration key
	 * @return array Default configuration
	 */
	private function get_default_configuration( $config_key ) {
		$defaults = array(
			'scoring_weights' => array(
				'biomarker_weight' => 0.7,
				'symptom_weight' => 0.3,
				'biomarkers' => array(
					'glucose' => 0.1,
					'hba1c' => 0.15,
					'testosterone' => 0.1,
					'cortisol' => 0.1,
					'vitamin_d' => 0.1,
					'tsh' => 0.1,
					'ldl' => 0.1,
					'hdl' => 0.1,
				),
				'symptoms' => array(
					'fatigue' => 0.2,
					'anxiety' => 0.2,
					'depression' => 0.2,
					'insomnia' => 0.2,
					'brain_fog' => 0.2,
				),
			),
			'biomarker_ranges' => array(
				'biomarkers' => array(
					'glucose' => array(
						'normal' => array( 70, 100 ),
						'unit' => 'mg/dL',
						'category' => 'metabolic',
					),
					'hba1c' => array(
						'normal' => array( 4.0, 5.7 ),
						'unit' => '%',
						'category' => 'metabolic',
					),
					'testosterone' => array(
						'normal' => array( 300, 1000 ),
						'unit' => 'ng/dL',
						'category' => 'hormones',
					),
				),
				'categories' => array(
					'metabolic' => 'Metabolic Health',
					'hormones' => 'Hormonal Health',
					'lipids' => 'Lipid Profile',
					'thyroid' => 'Thyroid Function',
					'vitamins' => 'Vitamin Status',
				),
			),
			'assessment_definitions' => array(
				'assessments' => array(
					'basic' => array(
						'name' => 'Basic Health Assessment',
						'description' => 'Comprehensive health evaluation',
						'biomarkers' => array( 'glucose', 'hba1c', 'testosterone' ),
						'symptoms' => array( 'fatigue', 'anxiety' ),
					),
					'comprehensive' => array(
						'name' => 'Comprehensive Health Assessment',
						'description' => 'Detailed health analysis',
						'biomarkers' => array( 'glucose', 'hba1c', 'testosterone', 'cortisol', 'vitamin_d' ),
						'symptoms' => array( 'fatigue', 'anxiety', 'depression', 'insomnia' ),
					),
				),
				'types' => array(
					'quantitative' => 'Biomarker-based assessment',
					'qualitative' => 'Symptom-based assessment',
					'mixed' => 'Combined assessment',
				),
			),
			'validation_rules' => array(
				'rules' => array(
					'biomarker_name' => 'required|string',
					'biomarker_value' => 'required|numeric',
					'biomarker_unit' => 'required|string',
					'biomarker_date' => 'required|date',
				),
				'messages' => array(
					'biomarker_name.required' => 'Biomarker name is required',
					'biomarker_value.required' => 'Biomarker value is required',
					'biomarker_value.numeric' => 'Biomarker value must be numeric',
					'biomarker_unit.required' => 'Biomarker unit is required',
					'biomarker_date.required' => 'Biomarker date is required',
					'biomarker_date.date' => 'Biomarker date must be a valid date',
				),
			),
		);
		
		return $defaults[ $config_key ] ?? array();
	}
	
	/**
	 * Get supported configuration types
	 *
	 * @return array Supported types
	 */
	public function get_supported_types() {
		return $this->supported_types;
	}
	
	/**
	 * Clear configuration cache
	 *
	 * @param string $config_key Optional specific key to clear
	 */
	public function clear_cache( $config_key = null ) {
		if ( $config_key ) {
			unset( $this->cache[ $config_key ] );
		} else {
			$this->cache = array();
		}
		
		// REMOVED: error_log( "ENNU Configuration Manager: Cache cleared" . ( $config_key ? " for '{$config_key}'" : '' ) );
	}
	
	/**
	 * Get configuration file path
	 *
	 * @param string $config_key Configuration key
	 * @return string File path
	 */
	public function get_config_file_path( $config_key ) {
		$type_config = $this->supported_types[ $config_key ] ?? array();
		$default_file = $type_config['default_file'] ?? "{$config_key}.json";
		
		return ENNU_LIFE_PLUGIN_PATH . 'config/' . $default_file;
	}
	
	/**
	 * Initialize configuration directory
	 */
	public function init_config_directory() {
		$config_dir = ENNU_LIFE_PLUGIN_PATH . 'config/';
		
		if ( ! is_dir( $config_dir ) ) {
			wp_mkdir_p( $config_dir );
		}
		
		// Create default configuration files
		foreach ( $this->supported_types as $config_key => $type_config ) {
			$file_path = $this->get_config_file_path( $config_key );
			
			if ( ! file_exists( $file_path ) ) {
				$default_config = $this->get_default_configuration( $config_key );
				file_put_contents( $file_path, json_encode( $default_config, JSON_PRETTY_PRINT ) );
			}
		}
		
		// REMOVED: // REMOVED DEBUG LOG: error_log( "ENNU Configuration Manager: Configuration directory initialized" );
	}
} 