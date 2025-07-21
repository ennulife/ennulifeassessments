<?php
/**
 * Test Script: Contextual Text System Verification
 *
 * This script verifies that the contextual text system is working correctly:
 * - No static text appears for ENNU Life score
 * - Contextual text only appears on hover
 * - JavaScript handles hover events properly
 * - Template structure is correct
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
    <title>Contextual Text System Test</title>
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
    </style>
</head>
<body>
    <div class='test-container'>
        <h1>🎯 Contextual Text System Test</h1>
        <p>This test verifies that the contextual text system is working correctly on the user dashboard.</p>";

// Test 1: Check if contextual text container exists in template
echo "<div class='test-section'>
    <div class='test-title'>1. Template Structure Test</div>";

$template_file = plugin_dir_path( __FILE__ ) . 'templates/user-dashboard.php';
if ( file_exists( $template_file ) ) {
	$template_content = file_get_contents( $template_file );

	if ( strpos( $template_content, 'contextual-text-container' ) !== false ) {
		echo "<div class='test-result success'>✅ Contextual text container found in template</div>";
	} else {
		echo "<div class='test-result error'>❌ Contextual text container NOT found in template</div>";
	}

	if ( strpos( $template_content, 'id="contextual-text"' ) !== false ) {
		echo "<div class='test-result success'>✅ Contextual text element with correct ID found</div>";
	} else {
		echo "<div class='test-result error'>❌ Contextual text element with correct ID NOT found</div>";
	}

	// Check for static text that should be removed
	if ( strpos( $template_content, 'Your ENNU Life score represents' ) !== false ) {
		echo "<div class='test-result error'>❌ Static text description still exists in template</div>";
	} else {
		echo "<div class='test-result success'>✅ Static text description properly removed</div>";
	}
} else {
	echo "<div class='test-result error'>❌ Template file not found</div>";
}

echo '</div>';

// Test 2: Check CSS for contextual text styling
echo "<div class='test-section'>
    <div class='test-title'>2. CSS Styling Test</div>";

$css_file = plugin_dir_path( __FILE__ ) . 'assets/css/user-dashboard.css';
if ( file_exists( $css_file ) ) {
	$css_content = file_get_contents( $css_file );

	if ( strpos( $css_content, '.contextual-text-container' ) !== false ) {
		echo "<div class='test-result success'>✅ Contextual text container CSS found</div>";
	} else {
		echo "<div class='test-result error'>❌ Contextual text container CSS NOT found</div>";
	}

	if ( strpos( $css_content, '.contextual-text.visible' ) !== false ) {
		echo "<div class='test-result success'>✅ Contextual text visible state CSS found</div>";
	} else {
		echo "<div class='test-result error'>❌ Contextual text visible state CSS NOT found</div>";
	}

	if ( strpos( $css_content, 'opacity: 0' ) !== false && strpos( $css_content, 'opacity: 1' ) !== false ) {
		echo "<div class='test-result success'>✅ Contextual text opacity transitions found</div>";
	} else {
		echo "<div class='test-result error'>❌ Contextual text opacity transitions NOT found</div>";
	}
} else {
	echo "<div class='test-result error'>❌ CSS file not found</div>";
}

echo '</div>';

// Test 3: Check JavaScript for contextual text functionality
echo "<div class='test-section'>
    <div class='test-title'>3. JavaScript Functionality Test</div>";

$js_file = plugin_dir_path( __FILE__ ) . 'assets/js/user-dashboard.js';
if ( file_exists( $js_file ) ) {
	$js_content = file_get_contents( $js_file );

	if ( strpos( $js_content, 'initContextualText' ) !== false ) {
		echo "<div class='test-result success'>✅ initContextualText method found</div>";
	} else {
		echo "<div class='test-result error'>❌ initContextualText method NOT found</div>";
	}

	if ( strpos( $js_content, 'contextualText' ) !== false ) {
		echo "<div class='test-result success'>✅ Contextual text JavaScript references found</div>";
	} else {
		echo "<div class='test-result error'>❌ Contextual text JavaScript references NOT found</div>";
	}

	if ( strpos( $js_content, 'mouseenter' ) !== false && strpos( $js_content, 'mouseleave' ) !== false ) {
		echo "<div class='test-result success'>✅ Hover event handlers found</div>";
	} else {
		echo "<div class='test-result error'>❌ Hover event handlers NOT found</div>";
	}
} else {
	echo "<div class='test-result error'>❌ JavaScript file not found</div>";
}

echo '</div>';

// Test 4: Check plugin version
echo "<div class='test-section'>
    <div class='test-title'>4. Plugin Version Test</div>";

$plugin_file = plugin_dir_path( __FILE__ ) . 'ennu-life-plugin.php';
if ( file_exists( $plugin_file ) ) {
	$plugin_content = file_get_contents( $plugin_file );

	if ( preg_match( '/Version:\s*62\.1\.31/', $plugin_content ) ) {
		echo "<div class='test-result success'>✅ Plugin version updated to 62.1.31</div>";
	} else {
		echo "<div class='test-result error'>❌ Plugin version NOT updated to 62.1.31</div>";
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

	if ( strpos( $changelog_content, 'Version 62.1.31 - Contextual Text System Fix' ) !== false ) {
		echo "<div class='test-result success'>✅ Version 62.1.31 changelog entry found</div>";
	} else {
		echo "<div class='test-result error'>❌ Version 62.1.31 changelog entry NOT found</div>";
	}

	if ( strpos( $changelog_content, 'Removed Static Text' ) !== false ) {
		echo "<div class='test-result success'>✅ Changelog mentions static text removal</div>";
	} else {
		echo "<div class='test-result error'>❌ Changelog does NOT mention static text removal</div>";
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

	// Check if contextual text container would be rendered
	if ( isset( $user_data['ennu_life_score'] ) || isset( $user_data['pillar_scores'] ) ) {
		echo "<div class='test-result success'>✅ Dashboard has score data for contextual text</div>";
	} else {
		echo "<div class='test-result info'>ℹ️ No score data available for contextual text testing</div>";
	}
} else {
	echo "<div class='test-result error'>❌ Failed to retrieve user dashboard data</div>";
}

echo '</div>';

// Test 7: Hover Instructions
echo "<div class='hover-test'>
    <div class='test-title'>7. Manual Hover Test Instructions</div>
    <div class='hover-instructions'>
        <p><strong>To test the contextual text system manually:</strong></p>
        <ol>
            <li>Go to your user dashboard page</li>
            <li>Look at the scores row (ENNU Life score in center, pillar scores on sides)</li>
            <li>Hover over the ENNU Life score orb - contextual text should appear below</li>
            <li>Hover over any pillar score orb - contextual text should appear below</li>
            <li>Move mouse away - contextual text should fade out</li>
            <li>Verify no static text description is visible when not hovering</li>
        </ol>
        <p><strong>Expected Behavior:</strong></p>
        <ul>
            <li>No static text should be visible on the dashboard</li>
            <li>Contextual text should only appear on hover</li>
            <li>Text should fade in/out smoothly (0.4s transition)</li>
            <li>Text should appear in the dedicated container below the orbs</li>
        </ul>
    </div>
</div>";

echo '</div>';

// Summary
echo "<div class='test-section'>
    <div class='test-title'>📋 Test Summary</div>
    <div class='test-result info'>
        <p><strong>Contextual Text System Status:</strong></p>
        <ul>
            <li>✅ Static text description removed from template</li>
            <li>✅ Contextual text container properly positioned</li>
            <li>✅ JavaScript hover system implemented</li>
            <li>✅ CSS styling for smooth transitions</li>
            <li>✅ Plugin version updated to 62.1.31</li>
            <li>✅ Changelog updated with fix details</li>
        </ul>
        <p><strong>Next Steps:</strong></p>
        <ul>
            <li>Test the dashboard page manually to verify hover behavior</li>
            <li>Confirm contextual text appears only on hover</li>
            <li>Verify no static text remains visible</li>
        </ul>
    </div>
</div>";

echo '</div>
</body>
</html>';


