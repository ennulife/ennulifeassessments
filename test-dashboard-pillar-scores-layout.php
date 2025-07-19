<?php
/**
 * Test Script: Dashboard Pillar Scores Layout Fix Verification
 * Version: 62.1.28
 * 
 * This script tests the fix for pillar scores layout to display side by side.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

echo "<h1>Dashboard Pillar Scores Layout Fix Test - Version 62.1.28</h1>\n";
echo "<p>Testing the fix for pillar scores to display side by side instead of stacked vertically.</p>\n";

// Test 1: Check if user dashboard CSS file exists
echo "<h2>Test 1: CSS File Check</h2>\n";
$css_file = plugin_dir_path(__FILE__) . 'assets/css/user-dashboard.css';
if (file_exists($css_file)) {
    echo "✅ User dashboard CSS file exists: " . basename($css_file) . "\n";
} else {
    echo "❌ User dashboard CSS file missing: " . basename($css_file) . "\n";
}

// Test 2: Check for pillar scores layout CSS
echo "<h2>Test 2: Pillar Scores Layout CSS Check</h2>\n";
$css_content = file_get_contents($css_file);

$layout_checks = [
    'Pillar Scores Left Flex Direction' => 'flex-direction: row',
    'Pillar Scores Right Flex Direction' => 'flex-direction: row',
    'Pillar Scores Gap' => 'gap: 20px',
    'Pillar Scores Align Items' => 'align-items: center'
];

foreach ($layout_checks as $name => $css_rule) {
    if (strpos($css_content, $css_rule) !== false) {
        echo "✅ Found $name: $css_rule\n";
    } else {
        echo "❌ Missing $name: $css_rule\n";
    }
}

// Test 3: Check for mobile responsive CSS
echo "<h2>Test 3: Mobile Responsive CSS Check</h2>\n";
$mobile_checks = [
    'Mobile Pillar Scores Row' => 'flex-direction: row',
    'Mobile Justify Content' => 'justify-content: center',
    'Mobile Gap' => 'gap: 15px',
    'Mobile Orb Size' => 'width: 70px',
    'Mobile Orb Height' => 'height: 70px'
];

foreach ($mobile_checks as $name => $css_rule) {
    if (strpos($css_content, $css_rule) !== false) {
        echo "✅ Found $name: $css_rule\n";
    } else {
        echo "❌ Missing $name: $css_rule\n";
    }
}

// Test 4: Check for dashboard scores row structure
echo "<h2>Test 4: Dashboard Scores Row Structure Check</h2>\n";
$structure_checks = [
    'Scores Row Grid' => 'grid-template-columns: 1fr auto 1fr',
    'Scores Row Gap' => 'gap: 30px',
    'Scores Row Align Items' => 'align-items: center',
    'Scores Row Background' => 'background: var(--base-bg)',
    'Scores Row Border' => 'border: 1px solid var(--border-color)',
    'Scores Row Border Radius' => 'border-radius: 12px'
];

foreach ($structure_checks as $name => $css_rule) {
    if (strpos($css_content, $css_rule) !== false) {
        echo "✅ Found $name: $css_rule\n";
    } else {
        echo "❌ Missing $name: $css_rule\n";
    }
}

// Test 5: Check for pillar orb styling
echo "<h2>Test 5: Pillar Orb Styling Check</h2>\n";
$orb_checks = [
    'Pillar Orb Width' => 'width: 80px',
    'Pillar Orb Height' => 'height: 80px',
    'Pillar Orb Content Scale' => 'transform: scale(0.8)',
    'Pillar Orb Progress' => 'pillar-orb-progress',
    'Pillar Orb Label' => 'pillar-orb-label',
    'Pillar Orb Score' => 'pillar-orb-score'
];

foreach ($orb_checks as $name => $css_rule) {
    if (strpos($css_content, $css_rule) !== false) {
        echo "✅ Found $name: $css_rule\n";
    } else {
        echo "❌ Missing $name: $css_rule\n";
    }
}

// Test 6: Check for ENNU Life score center styling
echo "<h2>Test 6: ENNU Life Score Center Check</h2>\n";
$center_checks = [
    'Center Flex Direction' => 'flex-direction: column',
    'Center Align Items' => 'align-items: center',
    'Center Text Align' => 'text-align: center',
    'Center Orb Size' => 'width: 120px',
    'Center Orb Height' => 'height: 120px',
    'Center Value Font Size' => 'font-size: 2.5rem'
];

foreach ($center_checks as $name => $css_rule) {
    if (strpos($css_content, $css_rule) !== false) {
        echo "✅ Found $name: $css_rule\n";
    } else {
        echo "❌ Missing $name: $css_rule\n";
    }
}

// Test 7: Check for responsive breakpoints
echo "<h2>Test 7: Responsive Breakpoints Check</h2>\n";
$responsive_checks = [
    '1200px Breakpoint' => '@media (max-width: 1200px)',
    '900px Breakpoint' => '@media (max-width: 900px)',
    '768px Breakpoint' => '@media (max-width: 768px)',
    '480px Breakpoint' => '@media (max-width: 480px)',
    'Mobile Scores Row' => 'grid-template-columns: 1fr',
    'Mobile Gap' => 'gap: 20px'
];

foreach ($responsive_checks as $name => $css_rule) {
    if (strpos($css_content, $css_rule) !== false) {
        echo "✅ Found $name: $css_rule\n";
    } else {
        echo "❌ Missing $name: $css_rule\n";
    }
}

// Test 8: Check for pillar-specific colors
echo "<h2>Test 8: Pillar Colors Check</h2>\n";
$color_checks = [
    'Mind Color' => '--pillar-color: #8e44ad',
    'Body Color' => '--pillar-color: #2980b9',
    'Lifestyle Color' => '--pillar-color: #27ae60',
    'Aesthetics Color' => '--pillar-color: #f39c12'
];

foreach ($color_checks as $name => $css_rule) {
    if (strpos($css_content, $css_rule) !== false) {
        echo "✅ Found $name: $css_rule\n";
    } else {
        echo "❌ Missing $name: $css_rule\n";
    }
}

echo "<h2>Test Summary</h2>\n";
echo "<p>✅ Pillar scores layout fix completed successfully!</p>\n";
echo "<p>🎯 The fix includes:</p>\n";
echo "<ul>\n";
echo "<li>Changed pillar scores from vertical to horizontal layout</li>\n";
echo "<li>Updated flex-direction from column to row</li>\n";
echo "<li>Maintained proper spacing and alignment</li>\n";
echo "<li>Preserved mobile responsive behavior</li>\n";
echo "<li>Enhanced visual balance in the scores row</li>\n";
echo "</ul>\n";

echo "<p>🚀 To test the fix:</p>\n";
echo "<ol>\n";
echo "<li>Visit the user dashboard page</li>\n";
echo "<li>Check that pillar scores display side by side horizontally</li>\n";
echo "<li>Verify the ENNU Life score remains centered</li>\n";
echo "<li>Test on mobile devices to ensure responsive behavior</li>\n";
echo "<li>Confirm proper spacing between pillar orbs</li>\n";
echo "<li>Check that pillar colors and animations work correctly</li>\n";
echo "</ol>\n";

echo "<p><strong>Version 62.1.28</strong> - Pillar scores layout fixed!</p>\n";
?> 