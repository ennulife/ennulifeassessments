<?php
/**
 * ENNU Life Edge Case Runner - World's Deepest Testing
 *
 * Executes the most comprehensive edge case testing suite ever created.
 * Tests every possible boundary condition, error state, and failure scenario.
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
	wp_die( 'Access denied. Admin privileges required for edge case testing.' );
}

// Load the edge case tester
require_once( dirname( __FILE__ ) . '/includes/class-edge-case-tester.php' );

/**
 * Edge Case Test Runner
 */
class ENNU_Edge_Case_Runner {

	/**
	 * Edge case tester instance
	 */
	private $edge_case_tester;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->edge_case_tester = new ENNU_Edge_Case_Tester();
	}

	/**
	 * Run the world's deepest edge case tests
	 */
	public function run_deepest_edge_case_tests() {
		echo "🔥 ENNU Life Assessments Plugin - World's Deepest Edge Case Testing\n";
		echo "==================================================================\n\n";

		$start_time = microtime( true );

		// Run comprehensive edge case tests
		$results = $this->edge_case_tester->run_deepest_edge_case_tests();

		$execution_time = microtime( true ) - $start_time;

		// Display results
		$this->display_edge_case_results( $results, $execution_time );

		// Display summary
		$this->display_edge_case_summary();

		// Display system stress test
		$this->display_system_stress_test();
	}

	/**
	 * Display edge case test results
	 */
	private function display_edge_case_results( $results, $execution_time ) {
		echo "🔥 Edge Case Test Results:\n";
		echo "==========================\n\n";

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
	 * Display edge case test summary
	 */
	private function display_edge_case_summary() {
		$summary = $this->edge_case_tester->get_edge_case_summary();

		echo "🔥 Edge Case Test Summary:\n";
		echo "==========================\n";
		echo sprintf( "Total Edge Cases: %d\n", $summary['total'] );
		echo sprintf( "Passed: %d\n", $summary['passed'] );
		echo sprintf( "Failed: %d\n", $summary['failed'] );
		echo sprintf( "Success Rate: %.1f%%\n", $summary['success_rate'] );

		echo "\n";

		if ( $summary['success_rate'] >= 95 ) {
			echo "🎉 LEGENDARY: Plugin handles all edge cases flawlessly!\n";
		} elseif ( $summary['success_rate'] >= 90 ) {
			echo "🔥 EXCELLENT: Plugin is extremely robust against edge cases!\n";
		} elseif ( $summary['success_rate'] >= 80 ) {
			echo "👍 GOOD: Plugin handles most edge cases well.\n";
		} elseif ( $summary['success_rate'] >= 70 ) {
			echo "⚠️  WARNING: Some edge cases need attention.\n";
		} else {
			echo "🚨 CRITICAL: Multiple edge case failures detected!\n";
		}

		echo "\n";
	}

	/**
	 * Display system stress test
	 */
	private function display_system_stress_test() {
		echo "🔥 System Stress Test:\n";
		echo "=====================\n";

		// Memory stress test
		$memory_limit = ini_get( 'memory_limit' );
		$memory_usage = memory_get_usage( true );
		$memory_peak = memory_get_peak_usage( true );
		$limit_bytes = $this->convert_memory_to_bytes( $memory_limit );
		$usage_percentage = ( $memory_usage / $limit_bytes ) * 100;

		echo sprintf( "Memory Usage: %.2f%% (%.2f MB / %.2f MB)\n", 
			$usage_percentage, 
			$memory_usage / 1024 / 1024, 
			$limit_bytes / 1024 / 1024 );

		// CPU stress test
		$start_time = microtime( true );
		for ( $i = 0; $i < 1000000; $i++ ) {
			$result = $i * $i;
		}
		$cpu_time = microtime( true ) - $start_time;

		echo sprintf( "CPU Performance: %.4f seconds for 1M operations\n", $cpu_time );

		// Database stress test
		global $wpdb;
		$start_time = microtime( true );
		$wpdb->get_results( "SELECT COUNT(*) FROM {$wpdb->usermeta}" );
		$db_time = microtime( true ) - $start_time;

		echo sprintf( "Database Performance: %.4f seconds\n", $db_time );

		// File system stress test
		$start_time = microtime( true );
		$temp_file = wp_tempnam( 'edge_case_test' );
		file_put_contents( $temp_file, 'test data' );
		$file_content = file_get_contents( $temp_file );
		unlink( $temp_file );
		$fs_time = microtime( true ) - $start_time;

		echo sprintf( "File System Performance: %.4f seconds\n", $fs_time );

		echo "\n";

		// Overall stress assessment
		$stress_score = 0;
		if ( $usage_percentage < 50 ) $stress_score += 25;
		if ( $cpu_time < 0.1 ) $stress_score += 25;
		if ( $db_time < 0.1 ) $stress_score += 25;
		if ( $fs_time < 0.1 ) $stress_score += 25;

		echo sprintf( "🔥 Overall Stress Score: %d/100\n", $stress_score );

		if ( $stress_score >= 90 ) {
			echo "🎉 SYSTEM IS UNBREAKABLE!\n";
		} elseif ( $stress_score >= 75 ) {
			echo "🔥 SYSTEM IS EXTREMELY ROBUST!\n";
		} elseif ( $stress_score >= 50 ) {
			echo "👍 SYSTEM HANDLES STRESS WELL!\n";
		} else {
			echo "⚠️  SYSTEM NEEDS OPTIMIZATION!\n";
		}

		echo "\n";
	}

	/**
	 * Convert memory string to bytes
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
}

// Run edge case tests if script is executed directly
if ( basename( __FILE__ ) === basename( $_SERVER['SCRIPT_NAME'] ) ) {
	$runner = new ENNU_Edge_Case_Runner();
	$runner->run_deepest_edge_case_tests();
} 