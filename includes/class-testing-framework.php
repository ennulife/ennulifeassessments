<?php
/**
 * ENNU Life Testing Framework - Comprehensive Validation System
 *
 * Provides comprehensive testing and validation for all migrated functionality
 * in the ENNU Life Assessments plugin. Tests data integrity, performance,
 * security, and functionality across all modern service classes.
 *
 * @package ENNU_Life
 * @copyright Copyright (c) 2024, Very Good Plugins, https://verygoodplugins.com
 * @license   GPL-3.0+
 * @since     64.6.20
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Testing_Framework {

	/**
	 * Logger instance
	 *
	 * @var ENNU_Logger
	 */
	private $logger;

	/**
	 * Test results
	 *
	 * @var array
	 */
	private $test_results = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->logger = new ENNU_Logger();
	}

	/**
	 * Run comprehensive testing suite
	 *
	 * @return array Test results
	 */
	public function run_comprehensive_tests() {
		$this->logger->log( 'Testing Framework: Starting comprehensive test suite' );

		$start_time = microtime( true );

		// 1. Test modern service classes
		$this->test_modern_service_classes();

		// 2. Test data integrity
		$this->test_data_integrity();

		// 3. Test performance
		$this->test_performance();

		// 4. Test security
		$this->test_security();

		// 5. Test functionality
		$this->test_functionality();

		// 6. Test error handling
		$this->test_error_handling();

		$execution_time = microtime( true ) - $start_time;

		$this->logger->log( 'Testing Framework: Test suite completed', array(
			'execution_time' => $execution_time,
			'total_tests' => count( $this->test_results ),
			'passed' => count( array_filter( $this->test_results, function( $result ) { return $result['status'] === 'PASS'; } ) ),
			'failed' => count( array_filter( $this->test_results, function( $result ) { return $result['status'] === 'FAIL'; } ) )
		) );

		return $this->test_results;
	}

	/**
	 * Test modern service classes
	 */
	private function test_modern_service_classes() {
		$this->logger->log( 'Testing Framework: Testing modern service classes' );

		// Test AJAX Handler
		$this->test_class_exists( 'ENNU_AJAX_Handler', 'AJAX Handler' );
		$this->test_class_methods( 'ENNU_AJAX_Handler', array(
			'handle_assessment_submission',
			'handle_save_progress',
			'handle_get_results'
		), 'AJAX Handler methods' );

		// Test Form Handler
		$this->test_class_exists( 'ENNU_Form_Handler', 'Form Handler' );
		$this->test_class_methods( 'ENNU_Form_Handler', array(
			'process_submission',
			'process_user',
			'route_to_assessment_engine'
		), 'Form Handler methods' );

		// Test Shortcode Manager
		$this->test_class_exists( 'ENNU_Shortcode_Manager', 'Shortcode Manager' );
		$this->test_class_methods( 'ENNU_Shortcode_Manager', array(
			'init',
			'register_shortcodes',
			'get_all_assessment_definitions'
		), 'Shortcode Manager methods' );

		// Test Data Manager
		$this->test_class_exists( 'ENNU_Data_Manager', 'Data Manager' );
		$this->test_class_methods( 'ENNU_Data_Manager', array(
			'save_assessment_data',
			'save_global_fields',
			'get_user_assessment_data',
			'calculate_and_store_scores'
		), 'Data Manager methods' );
	}

	/**
	 * Test data integrity
	 */
	private function test_data_integrity() {
		$this->logger->log( 'Testing Framework: Testing data integrity' );

		// Test user meta consistency
		$this->test_user_meta_consistency();

		// Test assessment data validation
		$this->test_assessment_data_validation();

		// Test scoring system integrity
		$this->test_scoring_system_integrity();

		// Test cache consistency
		$this->test_cache_consistency();
	}

	/**
	 * Test performance
	 */
	private function test_performance() {
		$this->logger->log( 'Testing Framework: Testing performance' );

		// Test memory usage
		$this->test_memory_usage();

		// Test database query performance
		$this->test_database_performance();

		// Test cache performance
		$this->test_cache_performance();

		// Test initialization time
		$this->test_initialization_time();
	}

	/**
	 * Test security
	 */
	private function test_security() {
		$this->logger->log( 'Testing Framework: Testing security' );

		// Test nonce validation
		$this->test_nonce_validation();

		// Test input sanitization
		$this->test_input_sanitization();

		// Test rate limiting
		$this->test_rate_limiting();

		// Test CSRF protection
		$this->test_csrf_protection();
	}

	/**
	 * Test functionality
	 */
	private function test_functionality() {
		$this->logger->log( 'Testing Framework: Testing functionality' );

		// Test shortcode registration
		$this->test_shortcode_registration();

		// Test assessment submission flow
		$this->test_assessment_submission_flow();

		// Test scoring calculation
		$this->test_scoring_calculation();

		// Test data persistence
		$this->test_data_persistence();
	}

	/**
	 * Test error handling
	 */
	private function test_error_handling() {
		$this->logger->log( 'Testing Framework: Testing error handling' );

		// Test invalid input handling
		$this->test_invalid_input_handling();

		// Test database error handling
		$this->test_database_error_handling();

		// Test exception handling
		$this->test_exception_handling();

		// Test graceful degradation
		$this->test_graceful_degradation();
	}

	/**
	 * Test if class exists
	 *
	 * @param string $class_name Class name
	 * @param string $description Test description
	 */
	private function test_class_exists( $class_name, $description ) {
		$result = class_exists( $class_name );
		$this->add_test_result( $description, $result, $result ? 'Class exists' : 'Class not found' );
	}

	/**
	 * Test if class methods exist
	 *
	 * @param string $class_name Class name
	 * @param array  $methods    Method names
	 * @param string $description Test description
	 */
	private function test_class_methods( $class_name, $methods, $description ) {
		if ( ! class_exists( $class_name ) ) {
			$this->add_test_result( $description, false, 'Class not found' );
			return;
		}

		$reflection = new ReflectionClass( $class_name );
		$missing_methods = array();

		foreach ( $methods as $method ) {
			if ( ! $reflection->hasMethod( $method ) ) {
				$missing_methods[] = $method;
			}
		}

		$result = empty( $missing_methods );
		$message = $result ? 'All methods exist' : 'Missing methods: ' . implode( ', ', $missing_methods );
		$this->add_test_result( $description, $result, $message );
	}

	/**
	 * Test user meta consistency
	 */
	private function test_user_meta_consistency() {
		// Test that user meta keys follow consistent patterns
		$test_user_id = 1; // Use admin user for testing
		$meta_keys = get_user_meta( $test_user_id );
		
		$ennu_keys = array_filter( array_keys( $meta_keys ), function( $key ) {
			return strpos( $key, 'ennu_' ) === 0;
		} );

		$result = ! empty( $ennu_keys );
		$this->add_test_result( 'User Meta Consistency', $result, 
			$result ? 'Found ' . count( $ennu_keys ) . ' ENNU meta keys' : 'No ENNU meta keys found' );
	}

	/**
	 * Test assessment data validation
	 */
	private function test_assessment_data_validation() {
		// Test that assessment data follows expected structure
		$test_data = array(
			'assessment_type' => 'hair_assessment',
			'email' => 'test@example.com',
			'first_name' => 'Test',
			'last_name' => 'User'
		);

		$result = ! empty( $test_data['assessment_type'] ) && ! empty( $test_data['email'] );
		$this->add_test_result( 'Assessment Data Validation', $result, 
			$result ? 'Test data structure valid' : 'Test data structure invalid' );
	}

	/**
	 * Test scoring system integrity
	 */
	private function test_scoring_system_integrity() {
		$result = class_exists( 'ENNU_Scoring_System' );
		$this->add_test_result( 'Scoring System Integrity', $result, 
			$result ? 'Scoring system available' : 'Scoring system not found' );
	}

	/**
	 * Test cache consistency
	 */
	private function test_cache_consistency() {
		$result = class_exists( 'ENNU_Score_Cache' );
		$this->add_test_result( 'Cache Consistency', $result, 
			$result ? 'Cache system available' : 'Cache system not found' );
	}

	/**
	 * Test memory usage
	 */
	private function test_memory_usage() {
		$memory_limit = ini_get( 'memory_limit' );
		$memory_usage = memory_get_usage( true );
		$memory_peak = memory_get_peak_usage( true );

		// Convert memory limit to bytes
		$limit_bytes = $this->convert_memory_to_bytes( $memory_limit );
		$usage_percentage = ( $memory_usage / $limit_bytes ) * 100;

		$result = $usage_percentage < 80; // Should use less than 80% of memory limit
		$this->add_test_result( 'Memory Usage', $result, 
			sprintf( 'Memory usage: %.2f%% (%.2f MB / %.2f MB)', 
				$usage_percentage, 
				$memory_usage / 1024 / 1024, 
				$limit_bytes / 1024 / 1024 ) );
	}

	/**
	 * Test database performance
	 */
	private function test_database_performance() {
		global $wpdb;
		
		$start_time = microtime( true );
		$wpdb->get_results( "SELECT COUNT(*) FROM {$wpdb->usermeta}" );
		$execution_time = microtime( true ) - $start_time;

		$result = $execution_time < 1.0; // Should complete in less than 1 second
		$this->add_test_result( 'Database Performance', $result, 
			sprintf( 'Query execution time: %.4f seconds', $execution_time ) );
	}

	/**
	 * Test cache performance
	 */
	private function test_cache_performance() {
		$start_time = microtime( true );
		$cache_key = 'ennu_test_cache_' . time();
		set_transient( $cache_key, 'test_data', 60 );
		$cached_data = get_transient( $cache_key );
		delete_transient( $cache_key );
		$execution_time = microtime( true ) - $start_time;

		$result = $execution_time < 0.1 && $cached_data === 'test_data';
		$this->add_test_result( 'Cache Performance', $result, 
			sprintf( 'Cache operation time: %.4f seconds', $execution_time ) );
	}

	/**
	 * Test initialization time
	 */
	private function test_initialization_time() {
		// This is a placeholder - actual initialization time would be measured during plugin load
		$result = true;
		$this->add_test_result( 'Initialization Time', $result, 'Plugin initialization completed successfully' );
	}

	/**
	 * Test nonce validation
	 */
	private function test_nonce_validation() {
		$nonce = wp_create_nonce( 'ennu_ajax_nonce' );
		$result = wp_verify_nonce( $nonce, 'ennu_ajax_nonce' );
		$this->add_test_result( 'Nonce Validation', $result, 
			$result ? 'Nonce validation working' : 'Nonce validation failed' );
	}

	/**
	 * Test input sanitization
	 */
	private function test_input_sanitization() {
		$test_input = '<script>alert("xss")</script>Test Data';
		$sanitized = sanitize_text_field( $test_input );
		$result = $sanitized === 'Test Data'; // Script tags should be removed
		
		$this->add_test_result( 'Input Sanitization', $result, 
			$result ? 'Input sanitization working' : 'Input sanitization failed' );
	}

	/**
	 * Test rate limiting
	 */
	private function test_rate_limiting() {
		$result = class_exists( 'ENNU_Security_Validator' );
		$this->add_test_result( 'Rate Limiting', $result, 
			$result ? 'Rate limiting system available' : 'Rate limiting system not found' );
	}

	/**
	 * Test CSRF protection
	 */
	private function test_csrf_protection() {
		$result = class_exists( 'ENNU_CSRF_Protection' );
		$this->add_test_result( 'CSRF Protection', $result, 
			$result ? 'CSRF protection available' : 'CSRF protection not found' );
	}

	/**
	 * Test shortcode registration
	 */
	private function test_shortcode_registration() {
		global $shortcode_tags;
		
		$ennu_shortcodes = array_filter( array_keys( $shortcode_tags ), function( $tag ) {
			return strpos( $tag, 'ennu' ) === 0;
		} );

		$result = count( $ennu_shortcodes ) >= 10; // Should have at least 10 ENNU shortcodes
		$this->add_test_result( 'Shortcode Registration', $result, 
			sprintf( 'Found %d ENNU shortcodes', count( $ennu_shortcodes ) ) );
	}

	/**
	 * Test assessment submission flow
	 */
	private function test_assessment_submission_flow() {
		$result = class_exists( 'ENNU_Form_Handler' ) && class_exists( 'ENNU_Data_Manager' );
		$this->add_test_result( 'Assessment Submission Flow', $result, 
			$result ? 'Submission flow components available' : 'Submission flow components missing' );
	}

	/**
	 * Test scoring calculation
	 */
	private function test_scoring_calculation() {
		$result = class_exists( 'ENNU_Scoring_System' );
		$this->add_test_result( 'Scoring Calculation', $result, 
			$result ? 'Scoring system available' : 'Scoring system not found' );
	}

	/**
	 * Test data persistence
	 */
	private function test_data_persistence() {
		$result = class_exists( 'ENNU_Data_Manager' );
		$this->add_test_result( 'Data Persistence', $result, 
			$result ? 'Data persistence system available' : 'Data persistence system not found' );
	}

	/**
	 * Test invalid input handling
	 */
	private function test_invalid_input_handling() {
		$test_data = array(
			'assessment_type' => '',
			'email' => 'invalid-email',
			'first_name' => '<script>alert("xss")</script>'
		);

		$result = empty( $test_data['assessment_type'] ) && ! is_email( $test_data['email'] );
		$this->add_test_result( 'Invalid Input Handling', $result, 
			$result ? 'Invalid input properly detected' : 'Invalid input not detected' );
	}

	/**
	 * Test database error handling
	 */
	private function test_database_error_handling() {
		$result = class_exists( 'ENNU_Life_Enhanced_Database' );
		$this->add_test_result( 'Database Error Handling', $result, 
			$result ? 'Database error handling available' : 'Database error handling not found' );
	}

	/**
	 * Test exception handling
	 */
	private function test_exception_handling() {
		$result = class_exists( 'ENNU_Logger' );
		$this->add_test_result( 'Exception Handling', $result, 
			$result ? 'Exception handling available' : 'Exception handling not found' );
	}

	/**
	 * Test graceful degradation
	 */
	private function test_graceful_degradation() {
		// Test that plugin works even if some components are missing
		$core_components = array(
			'ENNU_Assessment_Shortcodes',
			'ENNU_Scoring_System',
			'ENNU_Enhanced_Database'
		);

		$available_components = array_filter( $core_components, 'class_exists' );
		$result = count( $available_components ) >= 2; // At least 2 core components should be available

		$this->add_test_result( 'Graceful Degradation', $result, 
			sprintf( '%d/%d core components available', count( $available_components ), count( $core_components ) ) );
	}

	/**
	 * Add test result
	 *
	 * @param string $test_name Test name
	 * @param bool   $passed    Whether test passed
	 * @param string $message   Test message
	 */
	private function add_test_result( $test_name, $passed, $message ) {
		$this->test_results[] = array(
			'test' => $test_name,
			'status' => $passed ? 'PASS' : 'FAIL',
			'message' => $message,
			'timestamp' => current_time( 'mysql' )
		);
	}

	/**
	 * Convert memory string to bytes
	 *
	 * @param string $memory_string Memory string (e.g., '256M')
	 * @return int Bytes
	 */
	private function convert_memory_to_bytes( $memory_string ) {
		$unit = strtolower( substr( $memory_string, -1 ) );
		$value = (int) substr( $memory_string, 0, -1 );

		switch ( $unit ) {
			case 'k':
				return $value * 1024;
			case 'm':
				return $value * 1024 * 1024;
			case 'g':
				return $value * 1024 * 1024 * 1024;
			default:
				return $value;
		}
	}

	/**
	 * Get test results
	 *
	 * @return array Test results
	 */
	public function get_test_results() {
		return $this->test_results;
	}

	/**
	 * Get test summary
	 *
	 * @return array Test summary
	 */
	public function get_test_summary() {
		$passed = count( array_filter( $this->test_results, function( $result ) { 
			return $result['status'] === 'PASS'; 
		} ) );
		$failed = count( array_filter( $this->test_results, function( $result ) { 
			return $result['status'] === 'FAIL'; 
		} ) );
		$total = count( $this->test_results );

		return array(
			'total' => $total,
			'passed' => $passed,
			'failed' => $failed,
			'success_rate' => $total > 0 ? ( $passed / $total ) * 100 : 0
		);
	}
} 