<?php
/**
 * Unit Tests for ENNU Configuration Manager
 *
 * @package ENNU_Life_Assessments
 * @since 64.13.0
 */

/**
 * Test class for ENNU Configuration Manager
 */
class Test_ENNU_Configuration_Manager extends ENNU_TestCase {
	
	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();
		$this->reset_plugin_state();
	}
	
	/**
	 * Test configuration manager initialization
	 */
	public function test_configuration_manager_initialization() {
		// Test that the manager class exists
		$this->assertTrue( class_exists( 'ENNU_Configuration_Manager' ) );
		
		// Test manager instantiation
		$manager = new ENNU_Configuration_Manager();
		$this->assertInstanceOf( 'ENNU_Configuration_Manager', $manager );
	}
	
	/**
	 * Test supported configuration types
	 */
	public function test_supported_configuration_types() {
		$manager = new ENNU_Configuration_Manager();
		$types = $manager->get_supported_types();
		
		// Test that we have the expected configuration types
		$this->assertArrayHasKey( 'scoring_weights', $types );
		$this->assertArrayHasKey( 'biomarker_ranges', $types );
		$this->assertArrayHasKey( 'assessment_definitions', $types );
		$this->assertArrayHasKey( 'validation_rules', $types );
		
		// Test scoring_weights configuration
		$this->assertEquals( 'Scoring Weights', $types['scoring_weights']['name'] );
		$this->assertEquals( 'scoring-weights.json', $types['scoring_weights']['default_file'] );
		$this->assertArrayHasKey( 'validation_rules', $types['scoring_weights'] );
		
		// Test biomarker_ranges configuration
		$this->assertEquals( 'Biomarker Reference Ranges', $types['biomarker_ranges']['name'] );
		$this->assertEquals( 'biomarker-ranges.json', $types['biomarker_ranges']['default_file'] );
		$this->assertArrayHasKey( 'validation_rules', $types['biomarker_ranges'] );
	}
	
	/**
	 * Test configuration strategy setting
	 */
	public function test_set_configuration_strategy() {
		$manager = new ENNU_Configuration_Manager();
		
		// Test setting valid strategy
		$result = $manager->set_strategy( 'json' );
		$this->assertTrue( $result );
		
		// Test setting invalid strategy
		$result = $manager->set_strategy( 'invalid_strategy' );
		$this->assertFalse( $result );
	}
	
	/**
	 * Test configuration loading and saving
	 */
	public function test_configuration_loading_and_saving() {
		$manager = new ENNU_Configuration_Manager();
		
		// Test data
		$test_config = array(
			'biomarker_weight' => 0.7,
			'symptom_weight' => 0.3,
			'biomarkers' => array(
				'glucose' => 0.1,
				'hba1c' => 0.15,
			),
			'symptoms' => array(
				'fatigue' => 0.2,
				'anxiety' => 0.2,
			),
		);
		
		// Test saving configuration
		$result = $manager->save_configuration( 'scoring_weights', $test_config );
		$this->assertTrue( $result );
		
		// Test loading configuration
		$loaded_config = $manager->get_configuration( 'scoring_weights' );
		$this->assertEquals( $test_config, $loaded_config );
	}
	
	/**
	 * Test configuration validation
	 */
	public function test_configuration_validation() {
		$manager = new ENNU_Configuration_Manager();
		
		// Test valid configuration
		$valid_config = array(
			'biomarker_weight' => 0.7,
			'symptom_weight' => 0.3,
			'biomarkers' => array( 'glucose' => 0.1 ),
			'symptoms' => array( 'fatigue' => 0.2 ),
		);
		
		$result = $manager->save_configuration( 'scoring_weights', $valid_config );
		$this->assertTrue( $result );
		
		// Test invalid configuration (missing required fields)
		$invalid_config = array(
			'biomarker_weight' => 0.7,
			// Missing symptom_weight, biomarkers, symptoms
		);
		
		$result = $manager->save_configuration( 'scoring_weights', $invalid_config );
		$this->assertFalse( $result );
	}
	
	/**
	 * Test configuration cache
	 */
	public function test_configuration_cache() {
		$manager = new ENNU_Configuration_Manager();
		
		$test_config = array(
			'biomarker_weight' => 0.7,
			'symptom_weight' => 0.3,
			'biomarkers' => array( 'glucose' => 0.1 ),
			'symptoms' => array( 'fatigue' => 0.2 ),
		);
		
		// Save configuration
		$manager->save_configuration( 'scoring_weights', $test_config );
		
		// Load configuration (should use cache)
		$config1 = $manager->get_configuration( 'scoring_weights', true );
		$config2 = $manager->get_configuration( 'scoring_weights', true );
		
		$this->assertEquals( $config1, $config2 );
		
		// Clear cache
		$manager->clear_cache( 'scoring_weights' );
		
		// Load configuration (should not use cache)
		$config3 = $manager->get_configuration( 'scoring_weights', false );
		$this->assertEquals( $test_config, $config3 );
	}
	
	/**
	 * Test default configuration
	 */
	public function test_default_configuration() {
		$manager = new ENNU_Configuration_Manager();
		
		// Test getting default configuration for unknown type
		$config = $manager->get_configuration( 'unknown_type' );
		$this->assertEmpty( $config );
		
		// Test getting default configuration for known type
		$config = $manager->get_configuration( 'scoring_weights' );
		$this->assertNotEmpty( $config );
		$this->assertArrayHasKey( 'biomarker_weight', $config );
		$this->assertArrayHasKey( 'symptom_weight', $config );
		$this->assertArrayHasKey( 'biomarkers', $config );
		$this->assertArrayHasKey( 'symptoms', $config );
	}
	
	/**
	 * Test configuration file path
	 */
	public function test_config_file_path() {
		$manager = new ENNU_Configuration_Manager();
		
		$file_path = $manager->get_config_file_path( 'scoring_weights' );
		$this->assertStringContainsString( 'config/scoring-weights.json', $file_path );
		$this->assertStringContainsString( ENNU_LIFE_PLUGIN_PATH, $file_path );
	}
	
	/**
	 * Test configuration directory initialization
	 */
	public function test_config_directory_initialization() {
		$manager = new ENNU_Configuration_Manager();
		
		// Test directory initialization
		$manager->init_config_directory();
		
		// Check that config directory exists
		$config_dir = ENNU_LIFE_PLUGIN_PATH . 'config/';
		$this->assertTrue( is_dir( $config_dir ) );
		
		// Check that default configuration files were created
		$supported_types = $manager->get_supported_types();
		foreach ( $supported_types as $config_key => $type_config ) {
			$file_path = $manager->get_config_file_path( $config_key );
			$this->assertTrue( file_exists( $file_path ) );
		}
	}
	
	/**
	 * Test JSON configuration strategy
	 */
	public function test_json_configuration_strategy() {
		// Test that the strategy class exists
		$this->assertTrue( class_exists( 'ENNU_JSON_Configuration_Strategy' ) );
		
		// Test strategy instantiation
		$strategy = new ENNU_JSON_Configuration_Strategy();
		$this->assertInstanceOf( 'ENNU_Configuration_Strategy_Interface', $strategy );
	}
	
	/**
	 * Test database configuration strategy
	 */
	public function test_database_configuration_strategy() {
		// Test that the strategy class exists
		$this->assertTrue( class_exists( 'ENNU_Database_Configuration_Strategy' ) );
		
		// Test strategy instantiation
		$strategy = new ENNU_Database_Configuration_Strategy();
		$this->assertInstanceOf( 'ENNU_Configuration_Strategy_Interface', $strategy );
	}
	
	/**
	 * Test YAML configuration strategy
	 */
	public function test_yaml_configuration_strategy() {
		// Test that the strategy class exists
		$this->assertTrue( class_exists( 'ENNU_YAML_Configuration_Strategy' ) );
		
		// Test strategy instantiation
		$strategy = new ENNU_YAML_Configuration_Strategy();
		$this->assertInstanceOf( 'ENNU_Configuration_Strategy_Interface', $strategy );
	}
	
	/**
	 * Test options configuration strategy
	 */
	public function test_options_configuration_strategy() {
		// Test that the strategy class exists
		$this->assertTrue( class_exists( 'ENNU_Options_Configuration_Strategy' ) );
		
		// Test strategy instantiation
		$strategy = new ENNU_Options_Configuration_Strategy();
		$this->assertInstanceOf( 'ENNU_Configuration_Strategy_Interface', $strategy );
	}
	
	/**
	 * Test strategy pattern implementation
	 */
	public function test_strategy_pattern_implementation() {
		$manager = new ENNU_Configuration_Manager();
		
		// Test JSON strategy
		$manager->set_strategy( 'json' );
		$test_config = array( 'test' => 'value' );
		$result = $manager->save_configuration( 'test_config', $test_config );
		$this->assertTrue( $result );
		
		// Test database strategy
		$manager->set_strategy( 'database' );
		$result = $manager->save_configuration( 'test_config', $test_config );
		$this->assertTrue( $result );
		
		// Test options strategy
		$manager->set_strategy( 'options' );
		$result = $manager->save_configuration( 'test_config', $test_config );
		$this->assertTrue( $result );
	}
	
	/**
	 * Test configuration validation rules
	 */
	public function test_configuration_validation_rules() {
		$manager = new ENNU_Configuration_Manager();
		
		// Test required field validation
		$invalid_config = array(
			'biomarker_weight' => 0.7,
			// Missing required fields
		);
		
		$result = $manager->save_configuration( 'scoring_weights', $invalid_config );
		$this->assertFalse( $result );
		
		// Test numeric validation
		$invalid_config = array(
			'biomarker_weight' => 'not_numeric',
			'symptom_weight' => 0.3,
			'biomarkers' => array( 'glucose' => 0.1 ),
			'symptoms' => array( 'fatigue' => 0.2 ),
		);
		
		$result = $manager->save_configuration( 'scoring_weights', $invalid_config );
		$this->assertFalse( $result );
		
		// Test min/max validation
		$invalid_config = array(
			'biomarker_weight' => 1.5, // Exceeds max of 1
			'symptom_weight' => 0.3,
			'biomarkers' => array( 'glucose' => 0.1 ),
			'symptoms' => array( 'fatigue' => 0.2 ),
		);
		
		$result = $manager->save_configuration( 'scoring_weights', $invalid_config );
		$this->assertFalse( $result );
	}
	
	/**
	 * Test configuration error handling
	 */
	public function test_configuration_error_handling() {
		$manager = new ENNU_Configuration_Manager();
		
		// Test loading non-existent configuration
		$config = $manager->get_configuration( 'non_existent_config' );
		$this->assertEmpty( $config );
		
		// Test saving invalid configuration type
		$result = $manager->save_configuration( 'invalid_type', array( 'test' => 'value' ) );
		$this->assertFalse( $result );
	}
} 