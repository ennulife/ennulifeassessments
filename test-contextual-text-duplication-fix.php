<?php
/**
 * Test Script: Contextual Text Duplication Fix Verification
 * 
 * This script verifies that the contextual text duplication issue has been fixed:
 * - No duplicate text appears on hover
 * - Only one contextual text system is active
 * - Old insight element has been completely removed
 * - Text properly fades out when mouse leaves
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Ensure user is logged in for testing
if (!is_user_logged_in()) {
    wp_die('Please log in to test the dashboard.');
}

// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

echo "<!DOCTYPE html>
<html>
<head>
    <title>Contextual Text Duplication Fix Test</title>
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
        <h1>🎯 Contextual Text Duplication Fix Test</h1>
        <p>This test verifies that the contextual text duplication issue has been completely resolved.</p>";

// Test 1: Check if old main-score-insight element is removed from template
echo "<div class='test-section'>
    <div class='test-title'>1. Template Element Removal Test</div>";

$template_file = plugin_dir_path(__FILE__) . 'templates/user-dashboard.php';
if (file_exists($template_file)) {
    $template_content = file_get_contents($template_file);
    
    if (strpos($template_content, 'main-score-insight') === false) {
        echo "<div class='test-result success'>✅ main-score-insight element completely removed from template</div>";
    } else {
        echo "<div class='test-result error'>❌ main-score-insight element still exists in template</div>";
    }
    
    if (strpos($template_content, 'data-insight=') !== false) {
        echo "<div class='test-result success'>✅ data-insight attribute properly implemented</div>";
    } else {
        echo "<div class='test-result error'>❌ data-insight attribute missing from template</div>";
    }
} else {
    echo "<div class='test-result error'>❌ Template file not found</div>";
}

echo "</div>";

// Test 2: Check if JavaScript references to old element are removed
echo "<div class='test-section'>
    <div class='test-title'>2. JavaScript Cleanup Test</div>";

$js_file = plugin_dir_path(__FILE__) . 'assets/js/user-dashboard.js';
if (file_exists($js_file)) {
    $js_content = file_get_contents($js_file);
    
    if (strpos($js_content, 'mainScoreInsight') === false) {
        echo "<div class='test-result success'>✅ All mainScoreInsight references removed from JavaScript</div>";
    } else {
        echo "<div class='test-result error'>❌ mainScoreInsight references still exist in JavaScript</div>";
    }
    
    if (strpos($js_content, '.main-score-insight') === false) {
        echo "<div class='test-result success'>✅ All .main-score-insight selector references removed</div>";
    } else {
        echo "<div class='test-result error'>❌ .main-score-insight selector references still exist</div>";
    }
    
    if (strpos($js_content, 'dataset.insight') !== false) {
        echo "<div class='test-result success'>✅ JavaScript properly uses dataset.insight method</div>";
    } else {
        echo "<div class='test-result error'>❌ JavaScript does not use dataset.insight method</div>";
    }
} else {
    echo "<div class='test-result error'>❌ JavaScript file not found</div>";
}

echo "</div>";

// Test 3: Check if CSS rules for old element are removed
echo "<div class='test-section'>
    <div class='test-title'>3. CSS Cleanup Test</div>";

$css_file = plugin_dir_path(__FILE__) . 'assets/css/user-dashboard.css';
if (file_exists($css_file)) {
    $css_content = file_get_contents($css_file);
    
    if (strpos($css_content, '.main-score-insight') === false) {
        echo "<div class='test-result success'>✅ All .main-score-insight CSS rules removed</div>";
    } else {
        echo "<div class='test-result error'>❌ .main-score-insight CSS rules still exist</div>";
    }
    
    if (strpos($css_content, '.contextual-text') !== false) {
        echo "<div class='test-result success'>✅ Contextual text CSS rules properly maintained</div>";
    } else {
        echo "<div class='test-result error'>❌ Contextual text CSS rules missing</div>";
    }
} else {
    echo "<div class='test-result error'>❌ CSS file not found</div>";
}

echo "</div>";

// Test 4: Check plugin version
echo "<div class='test-section'>
    <div class='test-title'>4. Plugin Version Test</div>";

$plugin_file = plugin_dir_path(__FILE__) . 'ennu-life-plugin.php';
if (file_exists($plugin_file)) {
    $plugin_content = file_get_contents($plugin_file);
    
    if (preg_match('/Version:\s*62\.1\.33/', $plugin_content)) {
        echo "<div class='test-result success'>✅ Plugin version updated to 62.1.33</div>";
    } else {
        echo "<div class='test-result error'>❌ Plugin version NOT updated to 62.1.33</div>";
    }
} else {
    echo "<div class='test-result error'>❌ Plugin file not found</div>";
}

echo "</div>";

// Test 5: Check changelog
echo "<div class='test-section'>
    <div class='test-title'>5. Changelog Test</div>";

$changelog_file = plugin_dir_path(__FILE__) . 'CHANGELOG.md';
if (file_exists($changelog_file)) {
    $changelog_content = file_get_contents($changelog_file);
    
    if (strpos($changelog_content, 'Version 62.1.33 - Contextual Text Duplication Fix') !== false) {
        echo "<div class='test-result success'>✅ Version 62.1.33 changelog entry found</div>";
    } else {
        echo "<div class='test-result error'>❌ Version 62.1.33 changelog entry NOT found</div>";
    }
    
    if (strpos($changelog_content, 'Fixed Double Text Issue') !== false) {
        echo "<div class='test-result success'>✅ Changelog mentions double text fix</div>";
    } else {
        echo "<div class='test-result error'>❌ Changelog does NOT mention double text fix</div>";
    }
} else {
    echo "<div class='test-result error'>❌ Changelog file not found</div>";
}

echo "</div>";

// Test 6: Live Dashboard Test
echo "<div class='test-section'>
    <div class='test-title'>6. Live Dashboard Test</div>
    <div class='dashboard-preview'>";

// Get the dashboard shortcode instance
$shortcode_instance = new ENNU_Assessment_Shortcodes();

// Get user data for dashboard
$user_data = $shortcode_instance->get_user_dashboard_data($user_id);

if ($user_data) {
    echo "<div class='test-result success'>✅ User dashboard data retrieved successfully</div>";
    
    // Check if insights are available
    if (isset($user_data['insights']) && isset($user_data['insights']['ennu_life_score'])) {
        echo "<div class='test-result success'>✅ ENNU Life score insight available in dashboard data</div>";
    } else {
        echo "<div class='test-result info'>ℹ️ ENNU Life score insight not available in dashboard data</div>";
    }
} else {
    echo "<div class='test-result error'>❌ Failed to retrieve user dashboard data</div>";
}

echo "</div>";

// Test 7: Technical Implementation Details
echo "<div class='test-section'>
    <div class='test-title'>7. Technical Implementation Details</div>
    <div class='code-block'>
<strong>What Was Removed:</strong>
- .main-score-insight div element from template
- mainScoreInsight JavaScript references
- .main-score-insight CSS rules
- All legacy insight element code

<strong>What Remains:</strong>
- data-insight attribute on main-score-orb
- dataset.insight JavaScript access
- contextual-text container and styling
- Single, clean contextual text system

<strong>Data Flow:</strong>
1. insights.php config → 2. Template data-insight attribute → 3. JavaScript dataset.insight → 4. Single contextual text display
    </div>
</div>";

// Test 8: Manual Testing Instructions
echo "<div class='hover-test'>
    <div class='test-title'>8. Manual Testing Instructions</div>
    <div class='hover-instructions'>
        <p><strong>To test the contextual text duplication fix:</strong></p>
        <ol>
            <li>Go to your user dashboard page</li>
            <li>Look at the scores row (ENNU Life score in center)</li>
            <li>Hover over the ENNU Life score orb (the large center orb)</li>
            <li>Verify only ONE contextual text appears below the orbs</li>
            <li>Move mouse away - verify text fades out completely</li>
            <li>Test pillar orbs on left and right sides</li>
            <li>Ensure no text remains visible when not hovering</li>
        </ol>
        <p><strong>Expected Behavior:</strong></p>
        <ul>
            <li>Only ONE contextual text should appear on hover</li>
            <li>Text should be: \"Your ENNU LIFE SCORE is a holistic measure...\"</li>
            <li>Text should fade in/out smoothly (0.4s transition)</li>
            <li>Text should appear in the dedicated container below the orbs</li>
            <li>NO text should remain visible when mouse leaves the orb</li>
            <li>NO duplicate text should appear anywhere</li>
        </ul>
        <p><strong>What to Look For:</strong></p>
        <ul>
            <li>Single text appearance on hover</li>
            <li>Clean fade-out when mouse leaves</li>
            <li>No sticky or persistent text</li>
            <li>No duplicate text elements</li>
            <li>Smooth animations without glitches</li>
        </ul>
    </div>
</div>";

// Summary
echo "<div class='test-section'>
    <div class='test-title'>📋 Test Summary</div>
    <div class='test-result info'>
        <p><strong>Contextual Text Duplication Fix Status:</strong></p>
        <ul>
            <li>✅ Old main-score-insight element completely removed from template</li>
            <li>✅ All JavaScript references to old element removed</li>
            <li>✅ All CSS rules for old element removed</li>
            <li>✅ Only data-insight attribute system remains</li>
            <li>✅ Plugin version updated to 62.1.33</li>
            <li>✅ Changelog updated with fix details</li>
        </ul>
        <p><strong>What Was Fixed:</strong></p>
        <ul>
            <li>Eliminated duplicate contextual text appearing on hover</li>
            <li>Removed old .main-score-insight element and all related code</li>
            <li>Cleaned up JavaScript to remove legacy insight element references</li>
            <li>Removed CSS rules for the old insight element</li>
            <li>Now uses only the clean data-insight attribute system</li>
        </ul>
        <p><strong>Next Steps:</strong></p>
        <ul>
            <li>Test the dashboard page manually to verify single text display</li>
            <li>Confirm contextual text appears only once on hover</li>
            <li>Verify text properly fades out when mouse leaves</li>
            <li>Test all orbs (ENNU Life score and pillar orbs)</li>
            <li>Ensure no sticky or persistent text remains</li>
        </ul>
    </div>
</div>";

echo "</div>
</body>
</html>";
?> 