<?php
/**
 * Shortcode Registration Test
 *
 * Tests to verify that all ENNU Life assessment shortcodes are properly registered.
 *
 * @package ENNU_Life
 * @version 60.3.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Shortcode_Registration_Test {

	/**
	 * Test that all assessment shortcodes are registered
	 */
	public function test_assessment_shortcodes_registered() {
		global $shortcode_tags;

		$expected_shortcodes = array(
			'ennu-welcome-assessment',
			'ennu-hair-assessment',
			'ennu-ed-treatment-assessment',
			'ennu-weight-loss-assessment',
			'ennu-health-assessment',
			'ennu-skin-assessment',
			'ennu-sleep-assessment',
			'ennu-hormone-assessment',
			'ennu-menopause-assessment',
			'ennu-testosterone-assessment',
			'ennu-health-optimization-assessment',
		);

		$missing_shortcodes = array();
		foreach ( $expected_shortcodes as $shortcode ) {
			if ( ! isset( $shortcode_tags[ $shortcode ] ) ) {
				$missing_shortcodes[] = $shortcode;
			}
		}

		if ( ! empty( $missing_shortcodes ) ) {
			error_log( 'ENNU Test: Missing shortcodes: ' . implode( ', ', $missing_shortcodes ) );
			return false;
		}

		error_log( 'ENNU Test: All assessment shortcodes are registered successfully.' );
		return true;
	}

	/**
	 * Test that results shortcodes are registered
	 */
	public function test_results_shortcodes_registered() {
		global $shortcode_tags;

		$expected_shortcodes = array(
			'ennu-hair-results',
			'ennu-ed-treatment-results',
			'ennu-weight-loss-results',
			'ennu-health-results',
			'ennu-skin-results',
			'ennu-sleep-results',
			'ennu-hormone-results',
			'ennu-menopause-results',
			'ennu-testosterone-results',
			'ennu-health-optimization-results',
		);

		$missing_shortcodes = array();
		foreach ( $expected_shortcodes as $shortcode ) {
			if ( ! isset( $shortcode_tags[ $shortcode ] ) ) {
				$missing_shortcodes[] = $shortcode;
			}
		}

		if ( ! empty( $missing_shortcodes ) ) {
			error_log( 'ENNU Test: Missing results shortcodes: ' . implode( ', ', $missing_shortcodes ) );
			return false;
		}

		error_log( 'ENNU Test: All results shortcodes are registered successfully.' );
		return true;
	}

	/**
	 * Test that core shortcodes are registered
	 */
	public function test_core_shortcodes_registered() {
		global $shortcode_tags;

		$expected_shortcodes = array(
			'ennu-user-dashboard',
			'ennu-assessment-results',
		);

		$missing_shortcodes = array();
		foreach ( $expected_shortcodes as $shortcode ) {
			if ( ! isset( $shortcode_tags[ $shortcode ] ) ) {
				$missing_shortcodes[] = $shortcode;
			}
		}

		if ( ! empty( $missing_shortcodes ) ) {
			error_log( 'ENNU Test: Missing core shortcodes: ' . implode( ', ', $missing_shortcodes ) );
			return false;
		}

		error_log( 'ENNU Test: All core shortcodes are registered successfully.' );
		return true;
	}

	/**
	 * Run all shortcode registration tests
	 */
	public function run_all_tests() {
		error_log( 'ENNU Test: Starting shortcode registration tests...' );

		$tests_passed = 0;
		$total_tests  = 3;

		if ( $this->test_assessment_shortcodes_registered() ) {
			$tests_passed++;
		}

		if ( $this->test_results_shortcodes_registered() ) {
			$tests_passed++;
		}

		if ( $this->test_core_shortcodes_registered() ) {
			$tests_passed++;
		}

		error_log( "ENNU Test: Shortcode registration tests completed. {$tests_passed}/{$total_tests} tests passed." );

		return $tests_passed === $total_tests;
	}
}

// Run tests if this file is accessed directly
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	$test = new ENNU_Shortcode_Registration_Test();
	$test->run_all_tests();
}
