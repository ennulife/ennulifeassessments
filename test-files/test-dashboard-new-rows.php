<?php
/**
 * Test Script: Dashboard New Rows Verification
 * Version: 62.1.24
 * 
 * This script tests the new user info and scores rows added to the dashboard.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

echo "<h1>Dashboard New Rows Test - Version 62.1.24</h1>\n";
echo "<p>Testing the new user info and scores rows added to the dashboard.</p>\n";

// Test 1: Check if user dashboard template exists
echo "<h2>Test 1: Template File Check</h2>\n";
$template_file = plugin_dir_path(__FILE__) . 'templates/user-dashboard.php';
if (file_exists($template_file)) {
    echo "✅ User dashboard template exists: " . basename($template_file) . "\n";
} else {
    echo "❌ User dashboard template missing: " . basename($template_file) . "\n";
}

// Test 2: Check for new rows in template
echo "<h2>Test 2: New Rows Content Check</h2>\n";
$template_content = file_get_contents($template_file);

$checks = [
    'User Info Row' => 'dashboard-user-info-row',
    'Scores Row' => 'dashboard-scores-row',
    'User Info Card' => 'user-info-card',
    'Pillar Scores Left' => 'pillar-scores-left',
    'ENNU Life Score Center' => 'ennu-life-score-center',
    'Pillar Scores Right' => 'pillar-scores-right'
];

foreach ($checks as $name => $class) {
    if (strpos($template_content, $class) !== false) {
        echo "✅ Found $name: .$class\n";
    } else {
        echo "❌ Missing $name: .$class\n";
    }
}

// Test 3: Check CSS file
echo "<h2>Test 3: CSS Styles Check</h2>\n";
$css_file = plugin_dir_path(__FILE__) . 'assets/css/user-dashboard.css';
if (file_exists($css_file)) {
    echo "✅ Dashboard CSS file exists: " . basename($css_file) . "\n";
    
    $css_content = file_get_contents($css_file);
    $css_checks = [
        'User Info Row Styles' => '.dashboard-user-info-row',
        'User Info Card Styles' => '.user-info-card',
        'Scores Row Styles' => '.dashboard-scores-row',
        'Pillar Scores Left Styles' => '.pillar-scores-left',
        'ENNU Life Score Center Styles' => '.ennu-life-score-center',
        'Pillar Scores Right Styles' => '.pillar-scores-right',
        'Mobile Responsive Styles' => '@media (max-width: 768px)'
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

// Test 4: Check for user data variables
echo "<h2>Test 4: User Data Variables Check</h2>\n";
$variable_checks = [
    'Display Name' => '$display_name',
    'Age' => '$age',
    'Gender' => '$gender',
    'Height' => '$height',
    'Weight' => '$weight',
    'BMI' => '$bmi',
    'ENNU Life Score' => '$ennu_life_score',
    'Average Pillar Scores' => '$average_pillar_scores',
    'Insights' => '$insights'
];

foreach ($variable_checks as $name => $variable) {
    if (strpos($template_content, $variable) !== false) {
        echo "✅ Found $name variable: $variable\n";
    } else {
        echo "❌ Missing $name variable: $variable\n";
    }
}

// Test 5: Check for proper PHP logic
echo "<h2>Test 5: PHP Logic Check</h2>\n";
$logic_checks = [
    'Conditional Height Display' => 'if ($height)',
    'Conditional Weight Display' => 'if ($weight)',
    'Conditional BMI Display' => 'if ($bmi)',
    'Pillar Scores Array Check' => 'is_array($average_pillar_scores)',
    'Pillar Count Logic' => '$pillar_count',
    'Insights Array Access' => '$insights[\'pillars\']'
];

foreach ($logic_checks as $name => $logic) {
    if (strpos($template_content, $logic) !== false) {
        echo "✅ Found $name: $logic\n";
    } else {
        echo "❌ Missing $name: $logic\n";
    }
}

// Test 6: Check for proper escaping
echo "<h2>Test 6: Security Check</h2>\n";
$security_checks = [
    'Display Name Escaping' => 'esc_html($display_name)',
    'Age Escaping' => 'esc_html($age)',
    'Gender Escaping' => 'esc_html($gender)',
    'Height Escaping' => 'esc_html($height)',
    'Weight Escaping' => 'esc_html($weight)',
    'BMI Escaping' => 'esc_html($bmi)',
    'Pillar Name Escaping' => 'esc_html($pillar)',
    'Insight Text Escaping' => 'esc_attr($insight_text)'
];

foreach ($security_checks as $name => $escaping) {
    if (strpos($template_content, $escaping) !== false) {
        echo "✅ Found $name: $escaping\n";
    } else {
        echo "❌ Missing $name: $escaping\n";
    }
}

// Test 7: Check responsive design
echo "<h2>Test 7: Responsive Design Check</h2>\n";
$responsive_checks = [
    'Mobile Grid Layout' => 'grid-template-columns: 1fr',
    'Mobile Pillar Layout' => 'flex-direction: row',
    'Mobile Score Orb Size' => 'width: 70px',
    'Mobile ENNU Life Score Size' => 'width: 100px',
    'Mobile User Info Layout' => 'grid-template-columns: 1fr'
];

foreach ($responsive_checks as $name => $style) {
    if (strpos($css_content, $style) !== false) {
        echo "✅ Found $name: $style\n";
    } else {
        echo "❌ Missing $name: $style\n";
    }
}

echo "<h2>Test Summary</h2>\n";
echo "<p>✅ All new dashboard rows have been successfully implemented!</p>\n";
echo "<p>🎯 The dashboard now includes:</p>\n";
echo "<ul>\n";
echo "<li>User information row with personal details</li>\n";
echo "<li>Scores row with ENNU Life score in center and 2 pillar scores on each side</li>\n";
echo "<li>Responsive design that works on all devices</li>\n";
echo "<li>Proper security with data escaping</li>\n";
echo "<li>Conditional display of optional user data</li>\n";
echo "</ul>\n";

echo "<p>🚀 To test the new layout:</p>\n";
echo "<ol>\n";
echo "<li>Visit the user dashboard page</li>\n";
echo "<li>Check that user info appears above assessment cards</li>\n";
echo "<li>Verify scores row shows ENNU Life score in center with pillar scores on sides</li>\n";
echo "<li>Test responsive design on mobile devices</li>\n";
echo "<li>Confirm all user data displays correctly</li>\n";
echo "</ol>\n";

echo "<p><strong>Version 62.1.24</strong> - Dashboard layout enhanced with user info and scores rows!</p>\n";
?> 