<?php
/**
 * ENNU Life Assessments Plugin - Test Runner
 *
 * Executes comprehensive testing suite for the ENNU Life Assessments plugin.
 * This script can be run independently to validate all migrated functionality.
 *
 * @package ENNU_Life
 * @copyright Copyright (c) 2024, Very Good Plugins, https://verygoodplugins.com
 * @license   GPL-3.0+
 * @since     64.6.20
 */

// Load WordPress
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) . '/wp-load.php' );

// Ensure we're in admin context for testing
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Access denied. Admin privileges required for testing.' );
}

// Load the testing framework
require_once( dirname( __FILE__ ) . '/includes/class-testing-framework.php' );

/**
 * Test Runner Class
 */
class ENNU_Test_Runner {

	/**
	 * Testing framework instance
	 *
	 * @var ENNU_Testing_Framework
	 */
	private $testing_framework;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->testing_framework = new ENNU_Testing_Framework();
	}

	/**
	 * Run all tests
	 */
	public function run_all_tests() {
		echo "🧪 ENNU Life Assessments Plugin - Comprehensive Test Suite\n";
		echo "========================================================\n\n";

		$start_time = microtime( true );

		// Run comprehensive tests
		$results = $this->testing_framework->run_comprehensive_tests();

		$execution_time = microtime( true ) - $start_time;

		// Display results
		$this->display_results( $results, $execution_time );

		// Display summary
		$this->display_summary();
	}

	/**
	 * Display test results
	 *
	 * @param array $results Test results
	 * @param float $execution_time Execution time
	 */
	private function display_results( $results, $execution_time ) {
		echo "📊 Test Results:\n";
		echo "----------------\n\n";

		$passed = 0;
		$failed = 0;

		foreach ( $results as $result ) {
			$status_icon = $result['status'] === 'PASS' ? '✅' : '❌';
			$status_color = $result['status'] === 'PASS' ? "\033[32m" : "\033[31m";
			$reset_color = "\033[0m";

			echo sprintf( "%s %s%s%s - %s\n", 
				$status_icon, 
				$status_color, 
				$result['test'], 
				$reset_color, 
				$result['message'] 
			);

			if ( $result['status'] === 'PASS' ) {
				$passed++;
			} else {
				$failed++;
			}
		}

		echo "\n";
		echo sprintf( "⏱️  Execution Time: %.4f seconds\n", $execution_time );
		echo "\n";
	}

	/**
	 * Display test summary
	 */
	private function display_summary() {
		$summary = $this->testing_framework->get_test_summary();

		echo "📈 Test Summary:\n";
		echo "================\n";
		echo sprintf( "Total Tests: %d\n", $summary['total'] );
		echo sprintf( "Passed: %d\n", $summary['passed'] );
		echo sprintf( "Failed: %d\n", $summary['failed'] );
		echo sprintf( "Success Rate: %.1f%%\n", $summary['success_rate'] );

		echo "\n";

		if ( $summary['success_rate'] >= 90 ) {
			echo "🎉 EXCELLENT: All critical functionality is working properly!\n";
		} elseif ( $summary['success_rate'] >= 80 ) {
			echo "👍 GOOD: Most functionality is working, minor issues detected.\n";
		} elseif ( $summary['success_rate'] >= 70 ) {
			echo "⚠️  WARNING: Some functionality has issues that need attention.\n";
		} else {
			echo "🚨 CRITICAL: Multiple failures detected. Immediate attention required.\n";
		}

		echo "\n";

		// Display system information
		$this->display_system_info();
	}

	/**
	 * Display system information
	 */
	private function display_system_info() {
		echo "🔧 System Information:\n";
		echo "=====================\n";
		echo sprintf( "WordPress Version: %s\n", get_bloginfo( 'version' ) );
		echo sprintf( "PHP Version: %s\n", phpversion() );
		echo sprintf( "Memory Limit: %s\n", ini_get( 'memory_limit' ) );
		echo sprintf( "Memory Usage: %.2f MB\n", memory_get_usage( true ) / 1024 / 1024 );
		echo sprintf( "Peak Memory: %.2f MB\n", memory_get_peak_usage( true ) / 1024 / 1024 );
		echo sprintf( "Plugin Version: %s\n", ENNU_LIFE_PLUGIN_VERSION );
		echo "\n";
	}

	/**
	 * Run specific test category
	 *
	 * @param string $category Test category
	 */
	public function run_category_tests( $category ) {
		echo "🧪 Running $category Tests...\n";
		echo "============================\n\n";

		$results = $this->testing_framework->run_comprehensive_tests();
		
		// Filter results by category (simplified)
		$filtered_results = array_filter( $results, function( $result ) use ( $category ) {
			return stripos( $result['test'], $category ) !== false;
		} );

		$this->display_results( $filtered_results, 0 );
	}
}

// Run tests if script is executed directly
if ( basename( __FILE__ ) === basename( $_SERVER['SCRIPT_NAME'] ) ) {
	$runner = new ENNU_Test_Runner();
	
	// Check for specific category
	$category = isset( $_GET['category'] ) ? sanitize_text_field( $_GET['category'] ) : '';
	
	if ( $category ) {
		$runner->run_category_tests( $category );
	} else {
		$runner->run_all_tests();
	}
} 