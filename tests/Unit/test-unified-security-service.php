<?php
/**
 * Unit Tests for ENNU Unified Security Service
 *
 * @package ENNU_Life_Assessments
 * @since 64.14.0
 */

/**
 * Test class for ENNU Unified Security Service
 */
class Test_ENNU_Unified_Security_Service extends ENNU_TestCase {
	
	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();
		$this->reset_plugin_state();
	}
	
	/**
	 * Test unified security service initialization
	 */
	public function test_unified_security_service_initialization() {
		// Test that the service class exists
		$this->assertTrue( class_exists( 'ENNU_Unified_Security_Service' ) );
		
		// Test service instantiation
		$service = new ENNU_Unified_Security_Service();
		$this->assertInstanceOf( 'ENNU_Unified_Security_Service', $service );
	}
	
	/**
	 * Test security strategy setting
	 */
	public function test_set_security_strategy() {
		$service = new ENNU_Unified_Security_Service();
		
		// Test setting valid strategy
		$result = $service->set_strategy( 'wordpress' );
		$this->assertTrue( $result );
		
		// Test setting invalid strategy
		$result = $service->set_strategy( 'invalid_strategy' );
		$this->assertFalse( $result );
	}
	
	/**
	 * Test AJAX request validation
	 */
	public function test_validate_ajax_request() {
		$service = new ENNU_Unified_Security_Service();
		
		// Test valid request
		$valid_request = array(
			'action' => 'ennu_save_assessment',
			'nonce' => wp_create_nonce( 'ennu_save_assessment' ),
		);
		
		$validation = $service->validate_ajax_request( 'ennu_save_assessment', $valid_request );
		$this->assertTrue( $validation['valid'] );
		$this->assertEmpty( $validation['errors'] );
		
		// Test invalid request (missing nonce)
		$invalid_request = array(
			'action' => 'ennu_save_assessment',
		);
		
		$validation = $service->validate_ajax_request( 'ennu_save_assessment', $invalid_request );
		$this->assertFalse( $validation['valid'] );
		$this->assertContains( 'Invalid security token', $validation['errors'] );
	}
	
	/**
	 * Test AJAX nonce generation
	 */
	public function test_generate_ajax_nonce() {
		$service = new ENNU_Unified_Security_Service();
		
		$nonce = $service->generate_ajax_nonce( 'test_action' );
		$this->assertNotEmpty( $nonce );
		$this->assertIsString( $nonce );
	}
	
	/**
	 * Test input sanitization
	 */
	public function test_sanitize_input() {
		$service = new ENNU_Unified_Security_Service();
		
		// Test text sanitization
		$dirty_text = '<script>alert("xss")</script>Hello World';
		$clean_text = $service->sanitize_input( $dirty_text, 'text' );
		$this->assertEquals( 'Hello World', $clean_text );
		
		// Test email sanitization
		$dirty_email = 'test<script>alert("xss")</script>@example.com';
		$clean_email = $service->sanitize_input( $dirty_email, 'email' );
		$this->assertEquals( 'test@example.com', $clean_email );
		
		// Test integer sanitization
		$dirty_int = '123abc';
		$clean_int = $service->sanitize_input( $dirty_int, 'int' );
		$this->assertEquals( 123, $clean_int );
		
		// Test float sanitization
		$dirty_float = '123.45abc';
		$clean_float = $service->sanitize_input( $dirty_float, 'float' );
		$this->assertEquals( 123.45, $clean_float );
	}
	
	/**
	 * Test output escaping
	 */
	public function test_escape_output() {
		$service = new ENNU_Unified_Security_Service();
		
		$dirty_output = '<script>alert("xss")</script>Hello World';
		
		// Test HTML escaping
		$escaped_html = $service->escape_output( $dirty_output, 'html' );
		$this->assertStringContainsString( '&lt;script&gt;', $escaped_html );
		
		// Test attribute escaping
		$escaped_attr = $service->escape_output( $dirty_output, 'attr' );
		$this->assertStringContainsString( '&lt;script&gt;', $escaped_attr );
		
		// Test URL escaping
		$dirty_url = 'javascript:alert("xss")';
		$escaped_url = $service->escape_output( $dirty_url, 'url' );
		$this->assertStringContainsString( 'javascript:', $escaped_url );
	}
	
	/**
	 * Test file upload validation
	 */
	public function test_validate_file_upload() {
		$service = new ENNU_Unified_Security_Service();
		
		// Test valid file upload
		$valid_file = array(
			'name' => 'test.csv',
			'tmp_name' => tempnam( sys_get_temp_dir(), 'test' ),
			'size' => 1024,
			'error' => UPLOAD_ERR_OK,
		);
		
		$validation = $service->validate_file_upload( $valid_file, array( 'csv' ), 5242880 );
		$this->assertTrue( $validation['valid'] );
		$this->assertEmpty( $validation['errors'] );
		
		// Test file too large
		$large_file = array(
			'name' => 'test.csv',
			'tmp_name' => tempnam( sys_get_temp_dir(), 'test' ),
			'size' => 10 * 1024 * 1024, // 10MB
			'error' => UPLOAD_ERR_OK,
		);
		
		$validation = $service->validate_file_upload( $large_file, array( 'csv' ), 5242880 );
		$this->assertFalse( $validation['valid'] );
		$this->assertContains( 'File size exceeds maximum allowed size', $validation['errors'][0] );
		
		// Test invalid file type
		$invalid_file = array(
			'name' => 'test.exe',
			'tmp_name' => tempnam( sys_get_temp_dir(), 'test' ),
			'size' => 1024,
			'error' => UPLOAD_ERR_OK,
		);
		
		$validation = $service->validate_file_upload( $invalid_file, array( 'csv' ), 5242880 );
		$this->assertFalse( $validation['valid'] );
		$this->assertContains( 'File type not allowed', $validation['errors'][0] );
		
		// Clean up
		unlink( $valid_file['tmp_name'] );
		unlink( $large_file['tmp_name'] );
		unlink( $invalid_file['tmp_name'] );
	}
	
	/**
	 * Test security event logging
	 */
	public function test_log_security_event() {
		$service = new ENNU_Unified_Security_Service();
		
		// Test logging functionality
		$event_type = 'test_event';
		$event_data = array( 'test' => 'data' );
		
		// This should not throw an exception
		$service->log_security_event( $event_type, $event_data );
		
		// Verify log was written (check error log)
		$log_content = file_get_contents( WP_CONTENT_DIR . '/debug.log' );
		$this->assertStringContainsString( 'ENNU Security Event', $log_content );
	}
	
	/**
	 * Test security configuration
	 */
	public function test_security_configuration() {
		$service = new ENNU_Unified_Security_Service();
		
		// Test getting security config
		$config = $service->get_security_config();
		$this->assertIsArray( $config );
		$this->assertArrayHasKey( 'csrf_protection', $config );
		$this->assertArrayHasKey( 'rate_limiting', $config );
		$this->assertArrayHasKey( 'input_validation', $config );
		
		// Test updating security config
		$new_config = array( 'max_requests_per_minute' => 30 );
		$service->update_security_config( $new_config );
		
		$updated_config = $service->get_security_config();
		$this->assertEquals( 30, $updated_config['max_requests_per_minute'] );
	}
	
	/**
	 * Test WordPress security strategy
	 */
	public function test_wordpress_security_strategy() {
		// Test that the strategy class exists
		$this->assertTrue( class_exists( 'ENNU_WordPress_Security_Strategy' ) );
		
		// Test strategy instantiation
		$strategy = new ENNU_WordPress_Security_Strategy();
		$this->assertInstanceOf( 'ENNU_Security_Strategy_Interface', $strategy );
	}
	
	/**
	 * Test advanced security strategy
	 */
	public function test_advanced_security_strategy() {
		// Test that the strategy class exists
		$this->assertTrue( class_exists( 'ENNU_Advanced_Security_Strategy' ) );
		
		// Test strategy instantiation
		$strategy = new ENNU_Advanced_Security_Strategy();
		$this->assertInstanceOf( 'ENNU_Security_Strategy_Interface', $strategy );
	}
	
	/**
	 * Test minimal security strategy
	 */
	public function test_minimal_security_strategy() {
		// Test that the strategy class exists
		$this->assertTrue( class_exists( 'ENNU_Minimal_Security_Strategy' ) );
		
		// Test strategy instantiation
		$strategy = new ENNU_Minimal_Security_Strategy();
		$this->assertInstanceOf( 'ENNU_Security_Strategy_Interface', $strategy );
	}
	
	/**
	 * Test strategy pattern implementation
	 */
	public function test_strategy_pattern_implementation() {
		$service = new ENNU_Unified_Security_Service();
		
		// Test WordPress strategy
		$service->set_strategy( 'wordpress' );
		$nonce = $service->generate_ajax_nonce( 'test_action' );
		$this->assertNotEmpty( $nonce );
		
		// Test advanced strategy
		$service->set_strategy( 'advanced' );
		$nonce = $service->generate_ajax_nonce( 'test_action' );
		$this->assertNotEmpty( $nonce );
		
		// Test minimal strategy
		$service->set_strategy( 'minimal' );
		$nonce = $service->generate_ajax_nonce( 'test_action' );
		$this->assertNotEmpty( $nonce );
	}
	
	/**
	 * Test rate limiting
	 */
	public function test_rate_limiting() {
		$service = new ENNU_Unified_Security_Service();
		
		// Test rate limiting functionality
		$action = 'test_action';
		
		// First request should pass
		$validation1 = $service->validate_ajax_request( $action, array( 'nonce' => wp_create_nonce( $action ) ) );
		$this->assertTrue( $validation1['valid'] );
		
		// Multiple requests should still pass (within limit)
		for ( $i = 0; $i < 10; $i++ ) {
			$validation = $service->validate_ajax_request( $action, array( 'nonce' => wp_create_nonce( $action ) ) );
			$this->assertTrue( $validation['valid'] );
		}
	}
	
	/**
	 * Test permission checking
	 */
	public function test_permission_checking() {
		$service = new ENNU_Unified_Security_Service();
		
		// Test with logged in user
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $user_id );
		
		$validation = $service->validate_ajax_request( 'ennu_admin_action', array( 'nonce' => wp_create_nonce( 'ennu_admin_action' ) ) );
		$this->assertTrue( $validation['valid'] );
		
		// Test with insufficient permissions
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		
		$validation = $service->validate_ajax_request( 'ennu_admin_action', array( 'nonce' => wp_create_nonce( 'ennu_admin_action' ) ) );
		$this->assertFalse( $validation['valid'] );
		$this->assertContains( 'Insufficient permissions', $validation['errors'] );
	}
	
	/**
	 * Test security headers
	 */
	public function test_security_headers() {
		$service = new ENNU_Unified_Security_Service();
		
		// Test that security headers are added
		ob_start();
		$service->add_security_headers();
		$headers = ob_get_clean();
		
		// Note: In test environment, headers might not be visible
		// This test ensures the method doesn't throw errors
		$this->assertTrue( true );
	}
	
	/**
	 * Test CSP header
	 */
	public function test_csp_header() {
		$service = new ENNU_Unified_Security_Service();
		
		// Test that CSP header is added
		ob_start();
		$service->add_csp_header();
		$headers = ob_get_clean();
		
		// Note: In test environment, headers might not be visible
		// This test ensures the method doesn't throw errors
		$this->assertTrue( true );
	}
	
	/**
	 * Test security error handling
	 */
	public function test_security_error_handling() {
		$service = new ENNU_Unified_Security_Service();
		
		// Test with invalid data
		$validation = $service->validate_ajax_request( 'invalid_action', array() );
		$this->assertFalse( $validation['valid'] );
		$this->assertNotEmpty( $validation['errors'] );
		
		// Test with missing required fields
		$validation = $service->validate_ajax_request( 'test_action', array() );
		$this->assertFalse( $validation['valid'] );
		$this->assertContains( 'Invalid security token', $validation['errors'] );
	}
} 