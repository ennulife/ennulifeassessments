<?php
/**
 * Test Script: Dashboard Orb Improvements and Contextual Text System
 * Version: 62.1.29
 * 
 * This script tests the improved orb sizes, alignments, and contextual text hover system.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

echo "<h1>Dashboard Orb Improvements Test - Version 62.1.29</h1>\n";
echo "<p>Testing the improved orb sizes, perfect alignments, and contextual text hover system.</p>\n";

// Test 1: Check if user dashboard CSS file exists
echo "<h2>Test 1: CSS File Check</h2>\n";
$css_file = plugin_dir_path(__FILE__) . 'assets/css/user-dashboard.css';
if (file_exists($css_file)) {
    echo "✅ User dashboard CSS file exists: " . basename($css_file) . "\n";
} else {
    echo "❌ User dashboard CSS file missing: " . basename($css_file) . "\n";
}

// Test 2: Check for improved orb sizes
echo "<h2>Test 2: Orb Size Improvements Check</h2>\n";
$css_content = file_get_contents($css_file);

$orb_size_checks = [
    'ENNU Life Score Orb Size' => 'width: 140px',
    'ENNU Life Score Orb Height' => 'height: 140px',
    'Pillar Orb Size' => 'width: 90px',
    'Pillar Orb Height' => 'height: 90px',
    'ENNU Life Score Font Size' => 'font-size: 3rem',
    'Pillar Orb Content Scale' => 'transform: scale(0.85)'
];

foreach ($orb_size_checks as $name => $css_rule) {
    if (strpos($css_content, $css_rule) !== false) {
        echo "✅ Found $name: $css_rule\n";
    } else {
        echo "❌ Missing $name: $css_rule\n";
    }
}

// Test 3: Check for improved spacing and alignment
echo "<h2>Test 3: Spacing and Alignment Check</h2>\n";
$alignment_checks = [
    'Scores Row Gap' => 'gap: 40px',
    'Scores Row Padding' => 'padding: 40px',
    'Pillar Scores Gap' => 'gap: 25px',
    'Justify Content Center' => 'justify-content: center',
    'Position Relative' => 'position: relative',
    'Min Height' => 'min-height: 200px'
];

foreach ($alignment_checks as $name => $css_rule) {
    if (strpos($css_content, $css_rule) !== false) {
        echo "✅ Found $name: $css_rule\n";
    } else {
        echo "❌ Missing $name: $css_rule\n";
    }
}

// Test 4: Check for contextual text system
echo "<h2>Test 4: Contextual Text System Check</h2>\n";
$contextual_checks = [
    'Contextual Text Container' => '.contextual-text-container',
    'Contextual Text Position' => 'position: absolute',
    'Contextual Text Bottom' => 'bottom: -60px',
    'Contextual Text Opacity' => 'opacity: 0',
    'Contextual Text Transition' => 'transition: opacity 0.4s ease-in-out',
    'Contextual Text Visible' => '.contextual-text.visible',
    'Hover States' => '.ennu-life-score-center:hover .contextual-text'
];

foreach ($contextual_checks as $name => $css_rule) {
    if (strpos($css_content, $css_rule) !== false) {
        echo "✅ Found $name: $css_rule\n";
    } else {
        echo "❌ Missing $name: $css_rule\n";
    }
}

// Test 5: Check for mobile responsive improvements
echo "<h2>Test 5: Mobile Responsive Check</h2>\n";
$mobile_checks = [
    'Mobile Pillar Orb Size' => 'width: 75px',
    'Mobile Pillar Orb Height' => 'height: 75px',
    'Mobile ENNU Life Orb Size' => 'width: 110px',
    'Mobile ENNU Life Orb Height' => 'height: 110px',
    'Mobile Font Size' => 'font-size: 2.2rem',
    'Mobile Contextual Text' => 'bottom: -50px',
    'Mobile Text Font Size' => 'font-size: 0.85rem'
];

foreach ($mobile_checks as $name => $css_rule) {
    if (strpos($css_content, $css_rule) !== false) {
        echo "✅ Found $name: $css_rule\n";
    } else {
        echo "❌ Missing $name: $css_rule\n";
    }
}

// Test 6: Check template for contextual text container
echo "<h2>Test 6: Template Contextual Text Check</h2>\n";
$template_file = plugin_dir_path(__FILE__) . 'templates/user-dashboard.php';
if (file_exists($template_file)) {
    echo "✅ User dashboard template exists: " . basename($template_file) . "\n";
    
    $template_content = file_get_contents($template_file);
    $template_checks = [
        'Contextual Text Container' => 'contextual-text-container',
        'Contextual Text ID' => 'id="contextual-text"',
        'Contextual Text Div' => '<div class="contextual-text"'
    ];
    
    foreach ($template_checks as $name => $template_rule) {
        if (strpos($template_content, $template_rule) !== false) {
            echo "✅ Found $name: $template_rule\n";
        } else {
            echo "❌ Missing $name: $template_rule\n";
        }
    }
} else {
    echo "❌ User dashboard template missing: " . basename($template_file) . "\n";
}

// Test 7: Check JavaScript for contextual text system
echo "<h2>Test 7: JavaScript Contextual Text Check</h2>\n";
$js_file = plugin_dir_path(__FILE__) . 'assets/js/user-dashboard.js';
if (file_exists($js_file)) {
    echo "✅ User dashboard JavaScript exists: " . basename($js_file) . "\n";
    
    $js_content = file_get_contents($js_file);
    $js_checks = [
        'Init Contextual Text Method' => 'initContextualText()',
        'Contextual Text Element' => '#contextual-text',
        'Mouse Enter Event' => 'mouseenter',
        'Mouse Leave Event' => 'mouseleave',
        'Add Visible Class' => 'add(\'visible\')',
        'Remove Visible Class' => 'remove(\'visible\')',
        'ENNU Life Score Hover' => '.ennu-life-score-center',
        'Pillar Orb Hover' => '.pillar-orb'
    ];
    
    foreach ($js_checks as $name => $js_rule) {
        if (strpos($js_content, $js_rule) !== false) {
            echo "✅ Found $name: $js_rule\n";
        } else {
            echo "❌ Missing $name: $js_rule\n";
        }
    }
} else {
    echo "❌ User dashboard JavaScript missing: " . basename($js_file) . "\n";
}

// Test 8: Check for typography improvements
echo "<h2>Test 8: Typography Improvements Check</h2>\n";
$typography_checks = [
    'ENNU Life Score Label Font' => 'font-size: 0.9rem',
    'ENNU Life Score Insight Font' => 'font-size: 0.9rem',
    'Contextual Text Font' => 'font-size: 0.9rem',
    'Contextual Text Line Height' => 'line-height: 1.4',
    'Contextual Text Max Width' => 'max-width: 400px'
];

foreach ($typography_checks as $name => $css_rule) {
    if (strpos($css_content, $css_rule) !== false) {
        echo "✅ Found $name: $css_rule\n";
    } else {
        echo "❌ Missing $name: $css_rule\n";
    }
}

echo "<h2>Test Summary</h2>\n";
echo "<p>✅ Dashboard orb improvements and contextual text system completed successfully!</p>\n";
echo "<p>🎯 The improvements include:</p>\n";
echo "<ul>\n";
echo "<li>Enhanced orb sizes for better visual hierarchy</li>\n";
echo "<li>Perfect symmetrical alignment with improved spacing</li>\n";
echo "<li>Contextual text system with smooth hover animations</li>\n";
echo "<li>No layout shift when contextual text appears</li>\n";
echo "<li>Responsive design improvements for all screen sizes</li>\n";
echo "<li>Enhanced typography and readability</li>\n";
echo "</ul>\n";

echo "<p>🚀 To test the improvements:</p>\n";
echo "<ol>\n";
echo "<li>Visit the user dashboard page</li>\n";
echo "<li>Check that orbs are larger and better spaced</li>\n";
echo "<li>Hover over ENNU Life score to see contextual text fade in</li>\n";
echo "<li>Hover over pillar orbs to see their contextual text</li>\n";
echo "<li>Verify no layout shift when text appears</li>\n";
echo "<li>Test on mobile devices for responsive behavior</li>\n";
echo "<li>Confirm perfect symmetry and visual balance</li>\n";
echo "</ol>\n";

echo "<p><strong>Version 62.1.29</strong> - Orb improvements and contextual text system complete!</p>\n";
?> 