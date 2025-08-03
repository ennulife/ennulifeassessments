<?php
/**
 * Unit Tests for ENNU Unified Import Service
 *
 * @package ENNU_Life_Assessments
 * @since 64.13.0
 */

/**
 * Test class for ENNU Unified Import Service
 */
class Test_ENNU_Unified_Import_Service extends ENNU_TestCase {
	
	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();
		$this->reset_plugin_state();
	}
	
	/**
	 * Test unified import service initialization
	 */
	public function test_unified_import_service_initialization() {
		// Test that the service class exists
		$this->assertTrue( class_exists( 'ENNU_Unified_Import_Service' ) );
		
		// Test service instantiation
		$service = new ENNU_Unified_Import_Service();
		$this->assertInstanceOf( 'ENNU_Unified_Import_Service', $service );
	}
	
	/**
	 * Test supported import types
	 */
	public function test_supported_import_types() {
		$service = new ENNU_Unified_Import_Service();
		$types = $service->get_supported_import_types();
		
		// Test that we have the expected import types
		$this->assertArrayHasKey( 'lab_data', $types );
		$this->assertArrayHasKey( 'biomarker_csv', $types );
		$this->assertArrayHasKey( 'user_csv', $types );
		
		// Test lab_data configuration
		$this->assertEquals( 'Lab Data Import', $types['lab_data']['name'] );
		$this->assertContains( 'csv', $types['lab_data']['file_formats'] );
		$this->assertContains( 'pdf', $types['lab_data']['file_formats'] );
		$this->assertContains( 'labcorp', $types['lab_data']['providers'] );
		
		// Test biomarker_csv configuration
		$this->assertEquals( 'Biomarker CSV Import', $types['biomarker_csv']['name'] );
		$this->assertContains( 'csv', $types['biomarker_csv']['file_formats'] );
		$this->assertContains( 'generic', $types['biomarker_csv']['providers'] );
		
		// Test user_csv configuration
		$this->assertEquals( 'User CSV Import', $types['user_csv']['name'] );
		$this->assertContains( 'csv', $types['user_csv']['file_formats'] );
		$this->assertContains( 'generic', $types['user_csv']['providers'] );
	}
	
	/**
	 * Test import type configuration retrieval
	 */
	public function test_get_import_type_config() {
		$service = new ENNU_Unified_Import_Service();
		
		// Test valid import type
		$config = $service->get_import_type_config( 'lab_data' );
		$this->assertNotNull( $config );
		$this->assertEquals( 'Lab Data Import', $config['name'] );
		
		// Test invalid import type
		$config = $service->get_import_type_config( 'invalid_type' );
		$this->assertNull( $config );
	}
	
	/**
	 * Test CSV file parsing
	 */
	public function test_parse_csv_file() {
		$service = new ENNU_Unified_Import_Service();
		
		// Create a temporary CSV file for testing
		$temp_file = tempnam( sys_get_temp_dir(), 'test_csv' );
		$csv_content = "biomarker_name,value,unit,date\nglucose,95,mg/dL,2024-01-15\nhba1c,5.2,%,2024-01-15";
		file_put_contents( $temp_file, $csv_content );
		
		// Test parsing
		$data = $service->parse_csv_file( $temp_file );
		
		// Verify parsed data
		$this->assertCount( 2, $data );
		$this->assertEquals( 'glucose', $data[0]['biomarker_name'] );
		$this->assertEquals( '95', $data[0]['value'] );
		$this->assertEquals( 'mg/dL', $data[0]['unit'] );
		$this->assertEquals( '2024-01-15', $data[0]['date'] );
		
		// Clean up
		unlink( $temp_file );
	}
	
	/**
	 * Test biomarker row parsing
	 */
	public function test_parse_biomarker_row() {
		$service = new ENNU_Unified_Import_Service();
		
		// Test valid row
		$row = array(
			'biomarker_name' => 'glucose',
			'value' => '95',
			'unit' => 'mg/dL',
			'date' => '2024-01-15',
			'reference_range' => '70-100',
		);
		
		$biomarker_data = $service->parse_biomarker_row( $row );
		
		$this->assertEquals( 'glucose', $biomarker_data['name'] );
		$this->assertEquals( 95.0, $biomarker_data['value'] );
		$this->assertEquals( 'mg/dL', $biomarker_data['unit'] );
		$this->assertEquals( '2024-01-15', $biomarker_data['date'] );
		$this->assertEquals( '70-100', $biomarker_data['reference_range'] );
	}
	
	/**
	 * Test biomarker row parsing with missing required fields
	 */
	public function test_parse_biomarker_row_missing_fields() {
		$service = new ENNU_Unified_Import_Service();
		
		// Test missing biomarker name
		$row = array(
			'value' => '95',
			'unit' => 'mg/dL',
		);
		
		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Biomarker name is required' );
		$service->parse_biomarker_row( $row );
	}
	
	/**
	 * Test biomarker row parsing with missing value
	 */
	public function test_parse_biomarker_row_missing_value() {
		$service = new ENNU_Unified_Import_Service();
		
		// Test missing value
		$row = array(
			'biomarker_name' => 'glucose',
			'unit' => 'mg/dL',
		);
		
		$this->expectException( Exception::class );
		$this->expectExceptionMessage( 'Biomarker value is required' );
		$service->parse_biomarker_row( $row );
	}
	
	/**
	 * Test file validation
	 */
	public function test_validate_import_file() {
		$service = new ENNU_Unified_Import_Service();
		
		// Test valid file
		$valid_file = array(
			'name' => 'test.csv',
			'tmp_name' => tempnam( sys_get_temp_dir(), 'test' ),
			'size' => 1024,
			'error' => UPLOAD_ERR_OK,
		);
		
		$validation = $service->validate_import_file( $valid_file, 'biomarker_csv' );
		$this->assertTrue( $validation['valid'] );
		$this->assertEmpty( $validation['errors'] );
		
		// Test file too large
		$large_file = array(
			'name' => 'test.csv',
			'tmp_name' => tempnam( sys_get_temp_dir(), 'test' ),
			'size' => 10 * 1024 * 1024, // 10MB
			'error' => UPLOAD_ERR_OK,
		);
		
		$validation = $service->validate_import_file( $large_file, 'biomarker_csv' );
		$this->assertFalse( $validation['valid'] );
		$this->assertContains( 'File size exceeds maximum allowed size', $validation['errors'][0] );
		
		// Test unsupported file type
		$invalid_file = array(
			'name' => 'test.txt',
			'tmp_name' => tempnam( sys_get_temp_dir(), 'test' ),
			'size' => 1024,
			'error' => UPLOAD_ERR_OK,
		);
		
		$validation = $service->validate_import_file( $invalid_file, 'biomarker_csv' );
		$this->assertFalse( $validation['valid'] );
		$this->assertContains( 'File type not supported', $validation['errors'][0] );
		
		// Clean up
		unlink( $valid_file['tmp_name'] );
		unlink( $large_file['tmp_name'] );
		unlink( $invalid_file['tmp_name'] );
	}
	
	/**
	 * Test import logging
	 */
	public function test_log_import() {
		$service = new ENNU_Unified_Import_Service();
		
		// Test logging functionality
		$import_type = 'biomarker_csv';
		$user_id = 1;
		$result = array(
			'success' => true,
			'imported' => 5,
			'errors' => array(),
		);
		
		// This should not throw an exception
		$service->log_import( $import_type, $user_id, $result );
		
		// Verify log was written (check error log)
		$log_content = file_get_contents( WP_CONTENT_DIR . '/debug.log' );
		$this->assertStringContainsString( 'ENNU Unified Import Service', $log_content );
	}
	
	/**
	 * Test admin permissions verification
	 */
	public function test_verify_admin_permissions() {
		$service = new ENNU_Unified_Import_Service();
		
		// Test without proper permissions
		$_POST['nonce'] = wp_create_nonce( 'ennu_unified_import_nonce' );
		$result = $service->verify_admin_permissions();
		$this->assertFalse( $result );
		
		// Test with proper permissions
		wp_set_current_user( $this->factory->user->create( array( 'role' => 'administrator' ) ) );
		$result = $service->verify_admin_permissions();
		$this->assertTrue( $result );
	}
	
	/**
	 * Test shortcode rendering
	 */
	public function test_render_import_shortcode() {
		$service = new ENNU_Unified_Import_Service();
		
		// Test with logged in user
		$user_id = $this->factory->user->create();
		wp_set_current_user( $user_id );
		
		$output = $service->render_import_shortcode( array() );
		$this->assertStringContainsString( 'Import Your Biomarker Data', $output );
		
		// Test with custom attributes
		$output = $service->render_import_shortcode( array( 'type' => 'lab_data' ) );
		$this->assertStringContainsString( 'Import Your Biomarker Data', $output );
	}
	
	/**
	 * Test login message rendering
	 */
	public function test_render_login_message() {
		$service = new ENNU_Unified_Import_Service();
		
		$message = $service->render_login_message();
		$this->assertStringContainsString( 'Please log in', $message );
		$this->assertStringContainsString( 'ennu-login-required', $message );
	}
	
	/**
	 * Test AJAX handler registration
	 */
	public function test_ajax_handler_registration() {
		$service = new ENNU_Unified_Import_Service();
		
		// Test that AJAX handlers are registered
		global $wp_filter;
		
		// Check if our AJAX actions are registered
		$this->assertArrayHasKey( 'wp_ajax_ennu_unified_import_lab_data', $wp_filter );
		$this->assertArrayHasKey( 'wp_ajax_ennu_unified_import_biomarker_csv', $wp_filter );
		$this->assertArrayHasKey( 'wp_ajax_ennu_unified_import_user_csv', $wp_filter );
		$this->assertArrayHasKey( 'wp_ajax_nopriv_ennu_unified_import_user_csv', $wp_filter );
	}
	
	/**
	 * Test admin page registration
	 */
	public function test_admin_page_registration() {
		$service = new ENNU_Unified_Import_Service();
		
		// Test that admin menu is registered
		global $wp_filter;
		
		$this->assertArrayHasKey( 'admin_menu', $wp_filter );
	}
	
	/**
	 * Test shortcode registration
	 */
	public function test_shortcode_registration() {
		$service = new ENNU_Unified_Import_Service();
		
		// Test that shortcode is registered
		global $shortcode_tags;
		
		$this->assertArrayHasKey( 'ennu_unified_import', $shortcode_tags );
	}
	
	/**
	 * Test service integration with biomarker service
	 */
	public function test_biomarker_service_integration() {
		$service = new ENNU_Unified_Import_Service();
		
		// Test that biomarker service is available
		$this->assertInstanceOf( 'ENNU_Biomarker_Service', $service->biomarker_service );
	}
	
	/**
	 * Test database integration
	 */
	public function test_database_integration() {
		$service = new ENNU_Unified_Import_Service();
		
		// Test that database is available
		$this->assertNotNull( $service->database );
	}
} 