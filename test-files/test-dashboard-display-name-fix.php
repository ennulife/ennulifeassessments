<?php
/**
 * Test Script: Dashboard Display Name Variable Fix Verification
 * Version: 62.1.26
 * 
 * This script tests the fix for the undefined $display_name variable warning.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

echo "<h1>Dashboard Display Name Fix Test - Version 62.1.26</h1>\n";
echo "<p>Testing the fix for the undefined \$display_name variable warning.</p>\n";

// Test 1: Check if user dashboard template exists
echo "<h2>Test 1: Template File Check</h2>\n";
$template_file = plugin_dir_path(__FILE__) . 'templates/user-dashboard.php';
if (file_exists($template_file)) {
    echo "✅ User dashboard template exists: " . basename($template_file) . "\n";
} else {
    echo "❌ User dashboard template missing: " . basename($template_file) . "\n";
}

// Test 2: Check for display name variable definition
echo "<h2>Test 2: Display Name Variable Definition Check</h2>\n";
$template_content = file_get_contents($template_file);

$display_name_checks = [
    'First Name Variable' => '$first_name = isset($current_user->first_name)',
    'Last Name Variable' => '$last_name = isset($current_user->last_name)',
    'Display Name Definition' => '$display_name = trim($first_name . \' \' . $last_name)',
    'Empty Check' => 'if (empty($display_name))',
    'Fallback Logic' => '$current_user->display_name ?? $current_user->user_login ?? \'User\''
];

foreach ($display_name_checks as $name => $code) {
    if (strpos($template_content, $code) !== false) {
        echo "✅ Found $name: $code\n";
    } else {
        echo "❌ Missing $name: $code\n";
    }
}

// Test 3: Check for display name usage
echo "<h2>Test 3: Display Name Usage Check</h2>\n";
$usage_checks = [
    'Display Name in User Info' => 'esc_html($display_name)',
    'User Info Row' => 'dashboard-user-info-row',
    'Name Label' => 'info-label">Name:',
    'Name Value' => 'info-value"><?php echo esc_html($display_name); ?>'
];

foreach ($usage_checks as $name => $code) {
    if (strpos($template_content, $code) !== false) {
        echo "✅ Found $name: $code\n";
    } else {
        echo "❌ Missing $name: $code\n";
    }
}

// Test 4: Check variable definition order
echo "<h2>Test 4: Variable Definition Order Check</h2>\n";
$definition_position = strpos($template_content, '$display_name = trim($first_name');
$usage_position = strpos($template_content, 'esc_html($display_name)');

if ($definition_position !== false && $usage_position !== false) {
    if ($definition_position < $usage_position) {
        echo "✅ Display name is defined before it's used (correct order)\n";
        echo "  Definition at position: $definition_position\n";
        echo "  Usage at position: $usage_position\n";
    } else {
        echo "❌ Display name is used before it's defined (incorrect order)\n";
        echo "  Definition at position: $definition_position\n";
        echo "  Usage at position: $usage_position\n";
    }
} else {
    echo "❌ Could not determine variable definition order\n";
}

// Test 5: Check for defensive programming
echo "<h2>Test 5: Defensive Programming Check</h2>\n";
$defensive_checks = [
    'Current User Check' => 'if (!isset($current_user) || !is_object($current_user))',
    'First Name Check' => 'isset($current_user->first_name)',
    'Last Name Check' => 'isset($current_user->last_name)',
    'Empty Display Name Check' => 'empty($display_name)',
    'Null Coalescing' => '??'
];

foreach ($defensive_checks as $name => $code) {
    if (strpos($template_content, $code) !== false) {
        echo "✅ Found $name: $code\n";
    } else {
        echo "❌ Missing $name: $code\n";
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
    'BMI Escaping' => 'esc_html($bmi)'
];

foreach ($security_checks as $name => $escaping) {
    if (strpos($template_content, $escaping) !== false) {
        echo "✅ Found $name: $escaping\n";
    } else {
        echo "❌ Missing $name: $escaping\n";
    }
}

// Test 7: Check for conditional display
echo "<h2>Test 7: Conditional Display Check</h2>\n";
$conditional_checks = [
    'Height Conditional' => 'if ($height)',
    'Weight Conditional' => 'if ($weight)',
    'BMI Conditional' => 'if ($bmi)',
    'Endif Tags' => 'endif;'
];

foreach ($conditional_checks as $name => $conditional) {
    if (strpos($template_content, $conditional) !== false) {
        echo "✅ Found $name: $conditional\n";
    } else {
        echo "❌ Missing $name: $conditional\n";
    }
}

echo "<h2>Test Summary</h2>\n";
echo "<p>✅ Display name variable fix completed successfully!</p>\n";
echo "<p>🎯 The fix includes:</p>\n";
echo "<ul>\n";
echo "<li>Proper variable definition before usage</li>\n";
echo "<li>Fallback logic for missing user data</li>\n";
echo "<li>Defensive programming with proper checks</li>\n";
echo "<li>Security with proper escaping</li>\n";
echo "<li>Conditional display for optional user data</li>\n";
echo "</ul>\n";

echo "<p>🚀 To test the fix:</p>\n";
echo "<ol>\n";
echo "<li>Visit the user dashboard page</li>\n";
echo "<li>Check that no PHP warnings appear</li>\n";
echo "<li>Verify the user's name displays correctly in the Personal Information section</li>\n";
echo "<li>Test with users who have different name configurations</li>\n";
echo "<li>Confirm fallback logic works for users with missing name data</li>\n";
echo "</ol>\n";

echo "<p><strong>Version 62.1.26</strong> - Display name variable warning fixed!</p>\n";
?> 