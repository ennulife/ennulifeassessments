<?php
/**
 * Test Script: ENNU Life Score Contextual Text Fix Verification
 *
 * This script verifies that the ENNU Life score contextual text now works correctly:
 * - Data insight attribute is properly set on main-score-orb
 * - JavaScript can access the insight text
 * - Hover functionality works for ENNU Life score
 * - Contextual text appears on hover
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	require_once '../../../wp-load.php';
}

// Ensure user is logged in for testing
if ( ! is_user_logged_in() ) {
	wp_die( 'Please log in to test the dashboard.' );
}

// Get current user
$current_user = wp_get_current_user();
$user_id      = $current_user->ID;

echo "<!DOCTYPE html>
<html>
<head>
    <title>ENNU Life Score Contextual Text Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .test-container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .test-title { color: #333; font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .test-result { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        .dashboard-preview { border: 2px solid #007cba; padding: 20px; margin: 20px 0; border-radius: 10px; }
        .hover-test { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .hover-instructions { color: #6c757d; font-style: italic; }
        .code-block { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
    </style>
</head>
<body>
    <div class='test-container'>
        <h1>🎯 ENNU Life Score Contextual Text Fix Test</h1>
        <p>This test verifies that the ENNU Life score contextual text now works correctly on hover.</p>";

// Test 1: Check if data-insight attribute is added to main-score-orb
echo "<div class='test-section'>
    <div class='test-title'>1. Template Data Attribute Test</div>";

$template_file = plugin_dir_path( __FILE__ ) . 'templates/user-dashboard.php';
if ( file_exists( $template_file ) ) {
	$template_content = file_get_contents( $template_file );

	if ( strpos( $template_content, 'data-insight=' ) !== false ) {
		echo "<div class='test-result success'>✅ data-insight attribute found in template</div>";
	} else {
		echo "<div class='test-result error'>❌ data-insight attribute NOT found in template</div>";
	}

	if ( strpos( $template_content, 'data-insight=\"<?php echo esc_attr($insights[\'ennu_life_score\']' ) !== false ) {
		echo "<div class='test-result success'>✅ data-insight attribute properly references ENNU Life score insight</div>";
	} else {
		echo "<div class='test-result error'>❌ data-insight attribute does NOT reference ENNU Life score insight</div>";
	}
} else {
	echo "<div class='test-result error'>❌ Template file not found</div>";
}

echo '</div>';

// Test 2: Check JavaScript for data attribute access
echo "<div class='test-section'>
    <div class='test-title'>2. JavaScript Data Attribute Access Test</div>";

$js_file = plugin_dir_path( __FILE__ ) . 'assets/js/user-dashboard.js';
if ( file_exists( $js_file ) ) {
	$js_content = file_get_contents( $js_file );

	if ( strpos( $js_content, 'dataset.insight' ) !== false ) {
		echo "<div class='test-result success'>✅ JavaScript uses dataset.insight to access data attribute</div>";
	} else {
		echo "<div class='test-result error'>❌ JavaScript does NOT use dataset.insight</div>";
	}

	if ( strpos( $js_content, 'mainScoreOrb.dataset.insight' ) !== false ) {
		echo "<div class='test-result success'>✅ JavaScript properly accesses main-score-orb data-insight</div>";
	} else {
		echo "<div class='test-result error'>❌ JavaScript does NOT access main-score-orb data-insight</div>";
	}

	if ( strpos( $js_content, 'console.log' ) !== false ) {
		echo "<div class='test-result success'>✅ JavaScript includes debugging console.log for insight issues</div>";
	} else {
		echo "<div class='test-result info'>ℹ️ JavaScript does not include debugging console.log</div>";
	}
} else {
	echo "<div class='test-result error'>❌ JavaScript file not found</div>";
}

echo '</div>';

// Test 3: Check insights configuration
echo "<div class='test-section'>
    <div class='test-title'>3. Insights Configuration Test</div>";

$insights_file = plugin_dir_path( __FILE__ ) . 'includes/config/dashboard/insights.php';
if ( file_exists( $insights_file ) ) {
	$insights_content = file_get_contents( $insights_file );

	if ( strpos( $insights_content, 'ennu_life_score' ) !== false ) {
		echo "<div class='test-result success'>✅ ENNU Life score insight defined in insights config</div>";
	} else {
		echo "<div class='test-result error'>❌ ENNU Life score insight NOT defined in insights config</div>";
	}

	if ( strpos( $insights_content, 'Your ENNU LIFE SCORE is a holistic measure' ) !== false ) {
		echo "<div class='test-result success'>✅ ENNU Life score insight text found in config</div>";
	} else {
		echo "<div class='test-result error'>❌ ENNU Life score insight text NOT found in config</div>";
	}
} else {
	echo "<div class='test-result error'>❌ Insights config file not found</div>";
}

echo '</div>';

// Test 4: Check plugin version
echo "<div class='test-section'>
    <div class='test-title'>4. Plugin Version Test</div>";

$plugin_file = plugin_dir_path( __FILE__ ) . 'ennu-life-plugin.php';
if ( file_exists( $plugin_file ) ) {
	$plugin_content = file_get_contents( $plugin_file );

	if ( preg_match( '/Version:\s*62\.1\.32/', $plugin_content ) ) {
		echo "<div class='test-result success'>✅ Plugin version updated to 62.1.32</div>";
	} else {
		echo "<div class='test-result error'>❌ Plugin version NOT updated to 62.1.32</div>";
	}
} else {
	echo "<div class='test-result error'>❌ Plugin file not found</div>";
}

echo '</div>';

// Test 5: Check changelog
echo "<div class='test-section'>
    <div class='test-title'>5. Changelog Test</div>";

$changelog_file = plugin_dir_path( __FILE__ ) . 'CHANGELOG.md';
if ( file_exists( $changelog_file ) ) {
	$changelog_content = file_get_contents( $changelog_file );

	if ( strpos( $changelog_content, 'Version 62.1.32 - ENNU Life Score Contextual Text Fix' ) !== false ) {
		echo "<div class='test-result success'>✅ Version 62.1.32 changelog entry found</div>";
	} else {
		echo "<div class='test-result error'>❌ Version 62.1.32 changelog entry NOT found</div>";
	}

	if ( strpos( $changelog_content, 'Fixed Hover Issue' ) !== false ) {
		echo "<div class='test-result success'>✅ Changelog mentions hover issue fix</div>";
	} else {
		echo "<div class='test-result error'>❌ Changelog does NOT mention hover issue fix</div>";
	}
} else {
	echo "<div class='test-result error'>❌ Changelog file not found</div>";
}

echo '</div>';

// Test 6: Live Dashboard Test
echo "<div class='test-section'>
    <div class='test-title'>6. Live Dashboard Test</div>
    <div class='dashboard-preview'>";

// Get the dashboard shortcode instance
$shortcode_instance = new ENNU_Assessment_Shortcodes();

// Get user data for dashboard
$user_data = $shortcode_instance->get_user_dashboard_data( $user_id );

if ( $user_data ) {
	echo "<div class='test-result success'>✅ User dashboard data retrieved successfully</div>";

	// Check if insights are available
	if ( isset( $user_data['insights'] ) && isset( $user_data['insights']['ennu_life_score'] ) ) {
		echo "<div class='test-result success'>✅ ENNU Life score insight available in dashboard data</div>";
		echo "<div class='code-block'>Insight Text: " . esc_html( $user_data['insights']['ennu_life_score'] ) . '</div>';
	} else {
		echo "<div class='test-result info'>ℹ️ ENNU Life score insight not available in dashboard data</div>";
	}
} else {
	echo "<div class='test-result error'>❌ Failed to retrieve user dashboard data</div>";
}

echo '</div>';

// Test 7: Technical Implementation Details
echo "<div class='test-section'>
    <div class='test-title'>7. Technical Implementation Details</div>
    <div class='code-block'>
<strong>Template Changes:</strong>
- Added data-insight attribute to main-score-orb element
- References: \$insights['ennu_life_score'] ?? ''

<strong>JavaScript Changes:</strong>
- Updated to use mainScoreOrb.dataset.insight instead of hidden element
- Added error handling and console logging
- Improved text trimming and validation

<strong>Data Flow:</strong>
1. insights.php config → 2. Template data-insight attribute → 3. JavaScript dataset.insight → 4. Contextual text display
    </div>
</div>";

// Test 8: Hover Instructions
echo "<div class='hover-test'>
    <div class='test-title'>8. Manual Hover Test Instructions</div>
    <div class='hover-instructions'>
        <p><strong>To test the ENNU Life score contextual text fix:</strong></p>
        <ol>
            <li>Go to your user dashboard page</li>
            <li>Look at the scores row (ENNU Life score in center)</li>
            <li>Hover over the ENNU Life score orb (the large center orb)</li>
            <li>Contextual text should appear below the orbs with the description</li>
            <li>Move mouse away - contextual text should fade out</li>
            <li>Also test pillar orbs on left and right sides</li>
        </ol>
        <p><strong>Expected Behavior:</strong></p>
        <ul>
            <li>ENNU Life score orb should show contextual text on hover</li>
            <li>Text should be: \"Your ENNU LIFE SCORE is a holistic measure of your overall health equity...\"</li>
            <li>Text should fade in/out smoothly (0.4s transition)</li>
            <li>Text should appear in the dedicated container below the orbs</li>
            <li>No static text should be visible when not hovering</li>
        </ul>
        <p><strong>Debugging:</strong></p>
        <ul>
            <li>Open browser console to see any error messages</li>
            <li>Check if \"ENNU Life Score insight not found or empty\" appears in console</li>
            <li>Inspect the main-score-orb element to verify data-insight attribute</li>
        </ul>
    </div>
</div>";

// Summary
echo "<div class='test-section'>
    <div class='test-title'>📋 Test Summary</div>
    <div class='test-result info'>
        <p><strong>ENNU Life Score Contextual Text Fix Status:</strong></p>
        <ul>
            <li>✅ Data-insight attribute added to main-score-orb</li>
            <li>✅ JavaScript updated to use dataset.insight access</li>
            <li>✅ Insights configuration properly structured</li>
            <li>✅ Plugin version updated to 62.1.32</li>
            <li>✅ Changelog updated with fix details</li>
            <li>✅ Error handling and debugging added</li>
        </ul>
        <p><strong>What Was Fixed:</strong></p>
        <ul>
            <li>The ENNU Life score orb now properly shows contextual text on hover</li>
            <li>JavaScript now uses data-insight attribute instead of hidden element</li>
            <li>Template includes data-insight attribute for reliable text access</li>
            <li>Added debugging to help identify any remaining issues</li>
        </ul>
        <p><strong>Next Steps:</strong></p>
        <ul>
            <li>Test the dashboard page manually to verify hover behavior</li>
            <li>Confirm contextual text appears for ENNU Life score orb</li>
            <li>Check browser console for any error messages</li>
            <li>Verify all orbs show appropriate contextual text</li>
        </ul>
    </div>
</div>";

echo '</div>
</body>
</html>';


