<?php
/**
 * ENNU Life Assessments - Assessment Filtering Fix Test Script
 *
 * This script tests that welcome and health optimization assessments
 * are properly excluded from the dashboard assessment cards.
 *
 * @package ENNU_Life
 * @version 62.1.12
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access forbidden.' );
}

// Only run in admin
if ( ! is_admin() ) {
	exit( 'Admin access required.' );
}

echo '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; max-width: 1200px; margin: 0 auto; padding: 2rem;">';
echo '<h1 style="color: #1a202c; border-bottom: 3px solid #667eea; padding-bottom: 1rem;">🔧 Assessment Filtering Fix Test</h1>';
echo '<p style="color: #4a5568; font-size: 1.1rem; margin-bottom: 2rem;">Testing that welcome and health optimization assessments are properly excluded from dashboard cards.</p>';

// Test 1: Check if the filtering logic is implemented
echo '<div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">';
echo '<h2 style="color: #2d3748; margin-top: 0;">✅ Test 1: Filtering Logic Implementation</h2>';

$shortcode_file = ENNU_LIFE_PLUGIN_PATH . 'includes/class-assessment-shortcodes.php';
if ( file_exists( $shortcode_file ) ) {
	$file_content = file_get_contents( $shortcode_file );

	// Check for welcome assessment exclusion
	if ( strpos( $file_content, "'welcome_assessment' === \$key" ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Welcome assessment exclusion is implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Welcome assessment exclusion is missing</p>';
	}

	// Check for health optimization assessment exclusion
	if ( strpos( $file_content, "'health_optimization_assessment' === \$key" ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Health optimization assessment exclusion is implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Health optimization assessment exclusion is missing</p>';
	}

	// Check for combined exclusion logic
	if ( strpos( $file_content, "'welcome_assessment' === \$key || 'health_optimization_assessment' === \$key" ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Combined exclusion logic is properly implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Combined exclusion logic is missing</p>';
	}
} else {
	echo '<p style="color: #e53e3e; margin: 0;">✗ Shortcode class file does not exist</p>';
}
echo '</div>';

// Test 2: Check what assessments should be included
echo '<div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">';
echo '<h2 style="color: #2d3748; margin-top: 0;">✅ Test 2: Expected Assessment List</h2>';

$expected_assessments = array(
	'hair_assessment'         => '🦱 Hair Assessment',
	'ed_treatment_assessment' => '❤️‍🩹 ED Treatment Assessment',
	'weight_loss_assessment'  => '⚖️ Weight Loss Assessment',
	'health_assessment'       => '❤️ Health Assessment',
	'skin_assessment'         => '✨ Skin Assessment',
	'sleep_assessment'        => '😴 Sleep Assessment',
	'hormone_assessment'      => '🔬 Hormone Assessment',
	'menopause_assessment'    => '🌡️ Menopause Assessment',
	'testosterone_assessment' => '💪 Testosterone Assessment',
);

echo '<p style="color: #2d3748; margin: 0;"><strong>Assessments that SHOULD appear in dashboard cards:</strong></p>';
echo '<ul style="color: #2d3748; margin: 10px 0;">';
foreach ( $expected_assessments as $key => $label ) {
	echo '<li>' . esc_html( $label ) . ' (' . esc_html( $key ) . ')</li>';
}
echo '</ul>';

echo '<p style="color: #2d3748; margin: 20px 0 10px 0;"><strong>Assessments that SHOULD be excluded:</strong></p>';
echo '<ul style="color: #2d3748; margin: 10px 0;">';
echo '<li>welcome_assessment (Welcome Assessment) - Handled separately</li>';
echo '<li>health_optimization_assessment (Health Optimization Assessment) - Handled in sidebar</li>';
echo '</ul>';
echo '</div>';

// Test 3: Check if the method exists and can be called
echo '<div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">';
echo '<h2 style="color: #2d3748; margin-top: 0;">✅ Test 3: Method Availability</h2>';

if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
	echo '<p style="color: #38a169; margin: 0;">✓ ENNU_Assessment_Shortcodes class exists</p>';

	$shortcode_instance = new ENNU_Assessment_Shortcodes();

	// Use reflection to check if the method exists (it's private)
	$reflection = new ReflectionClass( $shortcode_instance );
	if ( $reflection->hasMethod( 'get_user_assessments_data' ) ) {
		echo '<p style="color: #38a169; margin: 0;">✓ get_user_assessments_data method exists</p>';

		// Make the method accessible for testing
		$method = $reflection->getMethod( 'get_user_assessments_data' );
		$method->setAccessible( true );

		// Test with a sample user ID (1)
		try {
			$assessments = $method->invoke( $shortcode_instance, 1 );

			if ( is_array( $assessments ) ) {
				echo '<p style="color: #38a169; margin: 0;">✓ Method returns array successfully</p>';

				// Check for excluded assessments
				$excluded_found = array();
				foreach ( array( 'welcome_assessment', 'health_optimization_assessment' ) as $excluded_key ) {
					if ( isset( $assessments[ $excluded_key ] ) ) {
						$excluded_found[] = $excluded_key;
					}
				}

				if ( empty( $excluded_found ) ) {
					echo '<p style="color: #38a169; margin: 0;">✓ Excluded assessments are properly filtered out</p>';
				} else {
					echo '<p style="color: #e53e3e; margin: 0;">✗ Excluded assessments found: ' . implode( ', ', $excluded_found ) . '</p>';
				}

				// Check for expected assessments
				$expected_found = array();
				foreach ( array_keys( $expected_assessments ) as $expected_key ) {
					if ( isset( $assessments[ $expected_key ] ) ) {
						$expected_found[] = $expected_key;
					}
				}

				echo '<p style="color: #2d3748; margin: 10px 0 0 0;"><strong>Found ' . count( $expected_found ) . ' of ' . count( $expected_assessments ) . ' expected assessments:</strong></p>';
				if ( ! empty( $expected_found ) ) {
					echo '<ul style="color: #2d3748; margin: 5px 0;">';
					foreach ( $expected_found as $found_key ) {
						echo '<li>' . esc_html( $expected_assessments[ $found_key ] ) . '</li>';
					}
					echo '</ul>';
				}
			} else {
				echo '<p style="color: #e53e3e; margin: 0;">✗ Method does not return an array</p>';
			}
		} catch ( Exception $e ) {
			echo '<p style="color: #e53e3e; margin: 0;">✗ Error calling method: ' . esc_html( $e->getMessage() ) . '</p>';
		}
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ get_user_assessments_data method does not exist</p>';
	}
} else {
	echo '<p style="color: #e53e3e; margin: 0;">✗ ENNU_Assessment_Shortcodes class does not exist</p>';
}
echo '</div>';

// Test 4: Check dashboard template for proper handling
echo '<div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">';
echo '<h2 style="color: #2d3748; margin-top: 0;">✅ Test 4: Dashboard Template Integration</h2>';

$template_path = ENNU_LIFE_PLUGIN_PATH . 'templates/user-dashboard.php';
if ( file_exists( $template_path ) ) {
	$template_content = file_get_contents( $template_path );

	if ( strpos( $template_content, 'foreach ( $user_assessments as $assessment_key => $assessment )' ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Dashboard template properly loops through user assessments</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Dashboard template does not loop through user assessments</p>';
	}

	if ( strpos( $template_content, 'assessment-cards-grid' ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Assessment cards grid is implemented in template</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Assessment cards grid is missing from template</p>';
	}

	// Check for health optimization section (should be separate)
	if ( strpos( $template_content, 'health-optimization-report-card' ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Health optimization section is properly implemented in sidebar</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Health optimization section is missing from sidebar</p>';
	}
} else {
	echo '<p style="color: #e53e3e; margin: 0;">✗ Dashboard template does not exist</p>';
}
echo '</div>';

// Summary
echo '<div style="background: #e6fffa; border: 1px solid #81e6d9; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">';
echo '<h2 style="color: #2d3748; margin-top: 0;">📋 Test Summary</h2>';
echo '<p style="color: #2d3748; margin: 0;"><strong>Assessment Filtering Features Tested:</strong></p>';
echo '<ul style="color: #2d3748; margin: 10px 0;">';
echo '<li>Welcome assessment exclusion from dashboard cards</li>';
echo '<li>Health optimization assessment exclusion from dashboard cards</li>';
echo '<li>Proper filtering logic implementation</li>';
echo '<li>Method availability and functionality</li>';
echo '<li>Dashboard template integration</li>';
echo '<li>Health optimization sidebar section</li>';
echo '</ul>';
echo '</div>';

// Instructions
echo '<div style="background: #fff5f5; border: 1px solid #feb2b2; border-radius: 8px; padding: 1.5rem;">';
echo '<h2 style="color: #2d3748; margin-top: 0;">🚀 Next Steps</h2>';
echo '<p style="color: #2d3748; margin: 0;"><strong>To verify the fix:</strong></p>';
echo '<ol style="color: #2d3748; margin: 10px 0;">';
echo '<li>Visit the user dashboard page</li>';
echo '<li>Check that welcome assessment is not in the assessment cards</li>';
echo '<li>Check that health optimization assessment is not in the assessment cards</li>';
echo '<li>Verify that health optimization appears in the sidebar section</li>';
echo '<li>Confirm that only the 9 core health assessments appear in the cards</li>';
echo '<li>Test with different user accounts to ensure consistency</li>';
echo '</ol>';
echo '</div>';

echo '</div>';


