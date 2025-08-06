<?php
/**
 * ENNU Life Assessments - Dummy Data Validation Script
 * 
 * This script validates that all dummy/sample/test data has been removed
 * from the plugin and only real user data is displayed.
 * 
 * @package ENNU_Life_Assessments
 * @version 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	// For testing, allow direct access if WPINC is not defined
	if ( ! defined( 'WPINC' ) ) {
		define( 'WPINC', 'wp-includes' );
	}
}

class ENNU_Dummy_Data_Validator {
	
	private $errors = array();
	private $warnings = array();
	private $passed = array();
	
	public function __construct() {
		echo "<h1>ENNU Life Assessments - Dummy Data Validation</h1>\n";
		echo "<p>Checking for any remaining dummy/sample/test data...</p>\n";
	}
	
	/**
	 * Run all validation tests
	 */
	public function run_validation() {
		echo "<h2>Running Validation Tests...</h2>\n";
		
		$this->test_assessment_results_template();
		$this->test_dashboard_javascript();
		$this->test_biomarker_analytics();
		$this->test_assessment_shortcodes();
		$this->test_template_files();
		$this->test_javascript_files();
		
		$this->display_results();
	}
	
	/**
	 * Test Assessment Results Template
	 */
	private function test_assessment_results_template() {
		echo "<h3>Testing Assessment Results Template...</h3>\n";
		
		$file_path = ENNU_LIFE_PLUGIN_PATH . 'templates/assessment-results.php';
		
		if ( ! file_exists( $file_path ) ) {
			$this->errors[] = "Assessment results template not found: $file_path";
			return;
		}
		
		$content = file_get_contents( $file_path );
		
		// Check for removed sample data
		$dummy_patterns = array(
			'sample_pillars.*array.*Lifestyle.*8\.2.*Aesthetics.*7\.1' => 'Sample pillar data (8.2, 7.1)',
			'\$sample_pillars' => 'Sample pillars variable',
			'Show sample data if no scores available' => 'Sample data comment'
		);
		
		foreach ( $dummy_patterns as $pattern => $description ) {
			if ( preg_match( '/' . $pattern . '/i', $content ) ) {
				$this->errors[] = "Assessment Results: Found $description in template";
			} else {
				$this->passed[] = "Assessment Results: $description - REMOVED ✓";
			}
		}
		
		// Check for proper empty state
		if ( strpos( $content, 'ennu-empty-state' ) !== false ) {
			$this->passed[] = "Assessment Results: Empty state properly implemented ✓";
		} else {
			$this->warnings[] = "Assessment Results: Empty state implementation not found";
		}
	}
	
	/**
	 * Test Dashboard JavaScript
	 */
	private function test_dashboard_javascript() {
		echo "<h3>Testing Dashboard JavaScript...</h3>\n";
		
		$file_path = ENNU_LIFE_PLUGIN_PATH . 'assets/js/user-dashboard.js';
		
		if ( ! file_exists( $file_path ) ) {
			$this->errors[] = "Dashboard JavaScript not found: $file_path";
			return;
		}
		
		$content = file_get_contents( $file_path );
		
		// Check for removed sample data generation
		$dummy_patterns = array(
			'baseScore.*6\.5' => 'Base score 6.5 sample data',
			'baseBMI.*24\.5' => 'Base BMI 24.5 sample data',
			'creating sample data for demonstration' => 'Sample data generation comment',
			'sample data as fallback' => 'Sample data fallback',
			'isSample.*true' => 'Sample data flag'
		);
		
		foreach ( $dummy_patterns as $pattern => $description ) {
			if ( preg_match( '/' . $pattern . '/i', $content ) ) {
				$this->errors[] = "Dashboard JS: Found $description";
			} else {
				$this->passed[] = "Dashboard JS: $description - REMOVED ✓";
			}
		}
		
		// Check for proper empty state handling
		if ( strpos( $content, 'showEmptyScoreState' ) !== false ) {
			$this->passed[] = "Dashboard JS: Empty score state method implemented ✓";
		} else {
			$this->warnings[] = "Dashboard JS: Empty score state method not found";
		}
	}
	
	/**
	 * Test Biomarker Analytics
	 */
	private function test_biomarker_analytics() {
		echo "<h3>Testing Biomarker Analytics...</h3>\n";
		
		$file_path = ENNU_LIFE_PLUGIN_PATH . 'assets/js/biomarker-analytics.js';
		
		if ( ! file_exists( $file_path ) ) {
			$this->errors[] = "Biomarker Analytics JavaScript not found: $file_path";
			return;
		}
		
		$content = file_get_contents( $file_path );
		
		// Check for removed hardcoded data
		$dummy_patterns = array(
			'data.*\[85.*88.*92.*87.*90.*89\]' => 'Hardcoded glucose levels (85-89)',
			'data.*\[65.*25.*10\]' => 'Hardcoded distribution percentages (65, 25, 10)',
			'labels.*Jan.*Feb.*Mar.*Apr.*May.*Jun' => 'Hardcoded month labels'
		);
		
		foreach ( $dummy_patterns as $pattern => $description ) {
			if ( preg_match( '/' . $pattern . '/i', $content ) ) {
				$this->errors[] = "Biomarker Analytics: Found $description";
			} else {
				$this->passed[] = "Biomarker Analytics: $description - REMOVED ✓";
			}
		}
		
		// Check for proper data loading methods
		if ( strpos( $content, 'loadBiomarkerTrendData' ) !== false && 
			 strpos( $content, 'loadBiomarkerDistributionData' ) !== false ) {
			$this->passed[] = "Biomarker Analytics: Real data loading methods implemented ✓";
		} else {
			$this->warnings[] = "Biomarker Analytics: Real data loading methods not found";
		}
	}
	
	/**
	 * Test Assessment Shortcodes
	 */
	private function test_assessment_shortcodes() {
		echo "<h3>Testing Assessment Shortcodes...</h3>\n";
		
		$file_path = ENNU_LIFE_PLUGIN_PATH . 'includes/class-assessment-shortcodes.php';
		
		if ( ! file_exists( $file_path ) ) {
			$this->errors[] = "Assessment Shortcodes not found: $file_path";
			return;
		}
		
		$content = file_get_contents( $file_path );
		
		// Check for removed default scores
		if ( preg_match( '/\$score\s*=\s*7\.0.*Default score if not found/i', $content ) ) {
			$this->errors[] = "Assessment Shortcodes: Found default score 7.0";
		} else {
			$this->passed[] = "Assessment Shortcodes: Default score 7.0 - REMOVED ✓";
		}
		
		// Check for proper empty state handling
		if ( strpos( $content, 'ennu-no-assessment-notice' ) !== false ) {
			$this->passed[] = "Assessment Shortcodes: Empty assessment notice implemented ✓";
		} else {
			$this->warnings[] = "Assessment Shortcodes: Empty assessment notice not found";
		}
	}
	
	/**
	 * Test Template Files for Sample Data
	 */
	private function test_template_files() {
		echo "<h3>Testing Template Files...</h3>\n";
		
		$template_dir = ENNU_LIFE_PLUGIN_PATH . 'templates/';
		$templates = glob( $template_dir . '*.php' );
		
		foreach ( $templates as $template ) {
			$content = file_get_contents( $template );
			$filename = basename( $template );
			
			// Check for common dummy data patterns
			$patterns = array(
				'lorem ipsum' => 'Lorem ipsum placeholder text',
				'john doe|jane smith' => 'Placeholder names',
				'example\.com' => 'Example domain',
				'test.*user' => 'Test user references',
				'sample.*data' => 'Sample data references'
			);
			
			foreach ( $patterns as $pattern => $description ) {
				if ( preg_match( '/' . $pattern . '/i', $content ) ) {
					$this->warnings[] = "Template $filename: Found $description";
				}
			}
		}
		
		$this->passed[] = "Template Files: Scanned " . count( $templates ) . " templates for dummy data ✓";
	}
	
	/**
	 * Test JavaScript Files for Sample Data
	 */
	private function test_javascript_files() {
		echo "<h3>Testing JavaScript Files...</h3>\n";
		
		$js_dir = ENNU_LIFE_PLUGIN_PATH . 'assets/js/';
		$js_files = glob( $js_dir . '*.js' );
		
		foreach ( $js_files as $js_file ) {
			$content = file_get_contents( $js_file );
			$filename = basename( $js_file );
			
			// Skip files we've already tested
			if ( in_array( $filename, array( 'user-dashboard.js', 'biomarker-analytics.js' ) ) ) {
				continue;
			}
			
			// Check for common dummy data patterns
			$patterns = array(
				'sample.*data.*true' => 'Sample data flags',
				'test.*value' => 'Test values',
				'fake.*data' => 'Fake data references',
				'demo.*data' => 'Demo data references'
			);
			
			foreach ( $patterns as $pattern => $description ) {
				if ( preg_match( '/' . $pattern . '/i', $content ) ) {
					$this->warnings[] = "JS File $filename: Found $description";
				}
			}
		}
		
		$this->passed[] = "JavaScript Files: Scanned " . count( $js_files ) . " JS files for dummy data ✓";
	}
	
	/**
	 * Display validation results
	 */
	private function display_results() {
		echo "<h2>Validation Results</h2>\n";
		
		// Display errors (critical issues)
		if ( ! empty( $this->errors ) ) {
			echo "<h3 style='color: red;'>❌ CRITICAL ISSUES FOUND (" . count( $this->errors ) . ")</h3>\n";
			echo "<ul style='color: red;'>\n";
			foreach ( $this->errors as $error ) {
				echo "<li>$error</li>\n";
			}
			echo "</ul>\n";
		}
		
		// Display warnings (potential issues)
		if ( ! empty( $this->warnings ) ) {
			echo "<h3 style='color: orange;'>⚠️ WARNINGS (" . count( $this->warnings ) . ")</h3>\n";
			echo "<ul style='color: orange;'>\n";
			foreach ( $this->warnings as $warning ) {
				echo "<li>$warning</li>\n";
			}
			echo "</ul>\n";
		}
		
		// Display passed tests
		if ( ! empty( $this->passed ) ) {
			echo "<h3 style='color: green;'>✅ PASSED TESTS (" . count( $this->passed ) . ")</h3>\n";
			echo "<ul style='color: green;'>\n";
			foreach ( $this->passed as $passed ) {
				echo "<li>$passed</li>\n";
			}
			echo "</ul>\n";
		}
		
		// Summary
		echo "<h2>Summary</h2>\n";
		$total_tests = count( $this->errors ) + count( $this->warnings ) + count( $this->passed );
		$success_rate = round( ( count( $this->passed ) / $total_tests ) * 100, 1 );
		
		if ( empty( $this->errors ) ) {
			echo "<p style='color: green; font-weight: bold; font-size: 1.2em;'>🎉 NO CRITICAL ISSUES FOUND!</p>\n";
			echo "<p>All dummy data removal fixes have been successfully implemented.</p>\n";
		} else {
			echo "<p style='color: red; font-weight: bold; font-size: 1.2em;'>⚠️ CRITICAL ISSUES NEED ATTENTION</p>\n";
			echo "<p>Please fix the critical issues above before deploying to production.</p>\n";
		}
		
		echo "<p><strong>Test Results:</strong> $success_rate% success rate ($total_tests total tests)</p>\n";
		echo "<p><strong>Critical Issues:</strong> " . count( $this->errors ) . "</p>\n";
		echo "<p><strong>Warnings:</strong> " . count( $this->warnings ) . "</p>\n";
		echo "<p><strong>Passed:</strong> " . count( $this->passed ) . "</p>\n";
	}
}

// Run validation if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	// Define plugin path for testing
	define( 'ENNU_LIFE_PLUGIN_PATH', dirname( __FILE__ ) . '/' );
	
	$validator = new ENNU_Dummy_Data_Validator();
	$validator->run_validation();
} else {
	// WordPress integration for admin testing
	add_action( 'wp_ajax_ennu_validate_dummy_data', array( 'ENNU_Dummy_Data_Validator', 'ajax_validate' ) );
}