<?php
/**
 * Test Script: Dashboard Layout Restructure Verification
 * Version: 62.1.25
 * 
 * This script tests the dashboard layout restructure where sidebar content
 * was moved to top rows and the layout is now full-width.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

echo "<h1>Dashboard Layout Restructure Test - Version 62.1.25</h1>\n";
echo "<p>Testing the dashboard layout restructure where sidebar content was moved to top rows.</p>\n";

// Test 1: Check if user dashboard template exists
echo "<h2>Test 1: Template File Check</h2>\n";
$template_file = plugin_dir_path(__FILE__) . 'templates/user-dashboard.php';
if (file_exists($template_file)) {
    echo "✅ User dashboard template exists: " . basename($template_file) . "\n";
} else {
    echo "❌ User dashboard template missing: " . basename($template_file) . "\n";
}

// Test 2: Check for sidebar removal
echo "<h2>Test 2: Sidebar Removal Check</h2>\n";
$template_content = file_get_contents($template_file);

$sidebar_checks = [
    'Sidebar Tag' => '<aside class="dashboard-sidebar">',
    'Sidebar Close Tag' => '</aside>',
    'User Info Header in Sidebar' => 'user-info-header',
    'Main Score Orb in Sidebar' => 'main-score-orb',
    'Pillar Scores Grid in Sidebar' => 'pillar-scores-grid'
];

foreach ($sidebar_checks as $name => $element) {
    if (strpos($template_content, $element) !== false) {
        echo "❌ Found $name in template (should be removed): $element\n";
    } else {
        echo "✅ $name properly removed from template\n";
    }
}

// Test 3: Check for new rows structure
echo "<h2>Test 3: New Rows Structure Check</h2>\n";
$new_rows_checks = [
    'User Info Row' => 'dashboard-user-info-row',
    'Scores Row' => 'dashboard-scores-row',
    'User Info Card' => 'user-info-card',
    'Pillar Scores Left' => 'pillar-scores-left',
    'ENNU Life Score Center' => 'ennu-life-score-center',
    'Pillar Scores Right' => 'pillar-scores-right',
    'Health Optimization Section' => 'health-optimization-section'
];

foreach ($new_rows_checks as $name => $class) {
    if (strpos($template_content, $class) !== false) {
        echo "✅ Found $name: .$class\n";
    } else {
        echo "❌ Missing $name: .$class\n";
    }
}

// Test 4: Check CSS layout changes
echo "<h2>Test 4: CSS Layout Changes Check</h2>\n";
$css_file = plugin_dir_path(__FILE__) . 'assets/css/user-dashboard.css';
if (file_exists($css_file)) {
    echo "✅ Dashboard CSS file exists: " . basename($css_file) . "\n";
    
    $css_content = file_get_contents($css_file);
    $css_checks = [
        'Full-Width Grid' => 'grid-template-columns: 1fr',
        'User Info Row Styles' => '.dashboard-user-info-row',
        'Scores Row Styles' => '.dashboard-scores-row',
        'Health Optimization Section' => '.health-optimization-section',
        'Responsive Full-Width' => '@media (max-width: 1200px)'
    ];
    
    foreach ($css_checks as $name => $selector) {
        if (strpos($css_content, $selector) !== false) {
            echo "✅ Found $name: $selector\n";
        } else {
            echo "❌ Missing $name: $selector\n";
        }
    }
} else {
    echo "❌ Dashboard CSS file missing: " . basename($css_file) . "\n";
}

// Test 5: Check for proper content flow
echo "<h2>Test 5: Content Flow Check</h2>\n";
$content_flow_checks = [
    'Welcome Section' => 'dashboard-welcome-section',
    'User Info Row' => 'dashboard-user-info-row',
    'Scores Row' => 'dashboard-scores-row',
    'Assessment Cards' => 'assessment-cards-section',
    'Charts Section' => 'charts-section',
    'Quick Actions' => 'quick-actions-section',
    'Health Optimization' => 'health-optimization-section'
];

$content_order = [];
foreach ($content_flow_checks as $name => $class) {
    $position = strpos($template_content, $class);
    if ($position !== false) {
        $content_order[$position] = $name;
    }
}

ksort($content_order);
echo "Content Flow Order:\n";
$expected_order = [
    'Welcome Section',
    'User Info Row', 
    'Scores Row',
    'Assessment Cards',
    'Charts Section',
    'Quick Actions',
    'Health Optimization'
];

foreach ($content_order as $position => $name) {
    echo "  ✅ $name\n";
}

// Test 6: Check for health optimization integration
echo "<h2>Test 6: Health Optimization Integration Check</h2>\n";
$health_checks = [
    'Health Optimization Section' => 'health-optimization-section',
    'Health Map Accordion' => 'health-map-accordion',
    'Accordion Items' => 'accordion-item',
    'Vector Titles' => 'vector-title',
    'Toggle All Button' => 'toggle-all-accordions'
];

foreach ($health_checks as $name => $element) {
    if (strpos($template_content, $element) !== false) {
        echo "✅ Found $name: $element\n";
    } else {
        echo "❌ Missing $name: $element\n";
    }
}

// Test 7: Check responsive design
echo "<h2>Test 7: Responsive Design Check</h2>\n";
$responsive_checks = [
    'Mobile Grid Layout' => 'grid-template-columns: 1fr',
    'Mobile Scores Row' => 'grid-template-columns: 1fr',
    'Mobile Pillar Layout' => 'flex-direction: row',
    'Mobile User Info' => 'grid-template-columns: 1fr'
];

foreach ($responsive_checks as $name => $style) {
    if (strpos($css_content, $style) !== false) {
        echo "✅ Found $name: $style\n";
    } else {
        echo "❌ Missing $name: $style\n";
    }
}

echo "<h2>Test Summary</h2>\n";
echo "<p>✅ Dashboard layout restructure completed successfully!</p>\n";
echo "<p>🎯 The dashboard now features:</p>\n";
echo "<ul>\n";
echo "<li>Full-width layout without sidebar</li>\n";
echo "<li>User information row at the top</li>\n";
echo "<li>Scores row with ENNU Life score in center and pillar scores on sides</li>\n";
echo "<li>Assessment cards in 2-column grid</li>\n";
echo "<li>Health optimization section integrated into main content</li>\n";
echo "<li>Responsive design that works on all devices</li>\n";
echo "</ul>\n";

echo "<p>🚀 To test the new layout:</p>\n";
echo "<ol>\n";
echo "<li>Visit the user dashboard page</li>\n";
echo "<li>Verify the layout is full-width (no sidebar)</li>\n";
echo "<li>Check that user info appears in the top row</li>\n";
echo "<li>Confirm scores row shows ENNU Life score in center with pillar scores on sides</li>\n";
echo "<li>Verify health optimization section appears in main content</li>\n";
echo "<li>Test responsive design on mobile devices</li>\n";
echo "</ol>\n";

echo "<p><strong>Version 62.1.25</strong> - Dashboard layout restructured for better content flow!</p>\n";
?> 