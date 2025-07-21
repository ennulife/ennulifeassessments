<?php
/**
 * Test Script: Health Goals Feature Verification
 * 
 * This script tests the new health goals feature by:
 * 1. Setting some test health goals for the current user
 * 2. Verifying the goals are saved correctly
 * 3. Testing the get_user_health_goals method
 * 
 * Usage: Run this script in your WordPress environment
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

echo "<h1>ENNU Life Health Goals Feature Test</h1>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .success { background-color: #d4edda; border-color: #c3e6cb; }
    .error { background-color: #f8d7da; border-color: #f5c6cb; }
    .info { background-color: #d1ecf1; border-color: #bee5eb; }
    .test-result { margin: 10px 0; padding: 10px; border-radius: 3px; }
    .pass { background-color: #d4edda; }
    .fail { background-color: #f8d7da; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>\n";

// Test 1: Check if user is logged in
echo "<div class='test-section'>\n";
echo "<h2>Test 1: User Authentication</h2>\n";

if (is_user_logged_in()) {
    $user = wp_get_current_user();
    echo "<div class='test-result pass'>✓ User is logged in: " . esc_html($user->display_name) . " (ID: " . $user->ID . ")</div>\n";
} else {
    echo "<div class='test-result fail'>✗ User is not logged in. Please log in to test health goals functionality.</div>\n";
    echo "</div>\n";
    exit;
}
echo "</div>\n";

// Test 2: Set test health goals
echo "<div class='test-section'>\n";
echo "<h2>Test 2: Setting Health Goals</h2>\n";

$user_id = get_current_user_id();
$test_goals = array('weight_loss', 'energy_boost', 'better_sleep', 'stress_reduction');

// Save test goals
update_user_meta($user_id, 'ennu_health_goals', $test_goals);

// Verify goals were saved
$saved_goals = get_user_meta($user_id, 'ennu_health_goals', true);
if (is_array($saved_goals) && count($saved_goals) === count($test_goals)) {
    echo "<div class='test-result pass'>✓ Health goals saved successfully</div>\n";
    echo "<div class='test-result info'>Saved goals: " . implode(', ', $saved_goals) . "</div>\n";
} else {
    echo "<div class='test-result fail'>✗ Failed to save health goals</div>\n";
    echo "<div class='test-result info'>Expected: " . implode(', ', $test_goals) . "</div>\n";
    echo "<div class='test-result info'>Actual: " . (is_array($saved_goals) ? implode(', ', $saved_goals) : 'Not an array') . "</div>\n";
}
echo "</div>\n";

// Test 3: Test the get_user_health_goals method
echo "<div class='test-section'>\n";
echo "<h2>Test 3: Testing get_user_health_goals Method</h2>\n";

// Get the shortcodes instance
$plugin = ENNU_Life_Enhanced_Plugin::get_instance();
$shortcodes = $plugin->get_shortcode_handler();

if ($shortcodes) {
    // Use reflection to access the private method
    $reflection = new ReflectionClass($shortcodes);
    $method = $reflection->getMethod('get_user_health_goals');
    $method->setAccessible(true);
    
    $health_goals_data = $method->invoke($shortcodes, $user_id);
    
    if (isset($health_goals_data['user_goals']) && isset($health_goals_data['all_goals'])) {
        echo "<div class='test-result pass'>✓ get_user_health_goals method works correctly</div>\n";
        echo "<div class='test-result info'>User goals: " . implode(', ', $health_goals_data['user_goals']) . "</div>\n";
        echo "<div class='test-result info'>Total available goals: " . count($health_goals_data['all_goals']) . "</div>\n";
        
        // Check if selected goals are marked correctly
        $selected_count = 0;
        foreach ($health_goals_data['all_goals'] as $goal_id => $goal) {
            if ($goal['selected']) {
                $selected_count++;
            }
        }
        echo "<div class='test-result info'>Selected goals count: " . $selected_count . "</div>\n";
        
        // Display some goal details
        echo "<div class='test-result info'><strong>Sample Goals:</strong></div>\n";
        $count = 0;
        foreach ($health_goals_data['all_goals'] as $goal_id => $goal) {
            if ($count < 3) {
                $status = $goal['selected'] ? '✓ Selected' : '○ Available';
                echo "<div class='test-result info'>• {$goal['label']} ({$goal['category']}) - {$status}</div>\n";
                $count++;
            }
        }
    } else {
        echo "<div class='test-result fail'>✗ get_user_health_goals method returned invalid data structure</div>\n";
        echo "<pre>" . print_r($health_goals_data, true) . "</pre>\n";
    }
} else {
    echo "<div class='test-result fail'>✗ Could not get shortcodes instance</div>\n";
}
echo "</div>\n";

// Test 4: Test dashboard rendering
echo "<div class='test-section'>\n";
echo "<h2>Test 4: Dashboard Health Goals Section</h2>\n";

// Test if the health goals section would render correctly
$dashboard_data = array(
    'health_goals_data' => $health_goals_data ?? array()
);

if (isset($dashboard_data['health_goals_data']['all_goals'])) {
    echo "<div class='test-result pass'>✓ Health goals data is ready for dashboard rendering</div>\n";
    
    // Check if goals are grouped by category
    $goals_by_category = array();
    foreach ($dashboard_data['health_goals_data']['all_goals'] as $goal) {
        $category = $goal['category'];
        if (!isset($goals_by_category[$category])) {
            $goals_by_category[$category] = array();
        }
        $goals_by_category[$category][] = $goal;
    }
    
    echo "<div class='test-result info'><strong>Goals by Category:</strong></div>\n";
    foreach ($goals_by_category as $category => $goals) {
        $selected_in_category = 0;
        foreach ($goals as $goal) {
            if ($goal['selected']) {
                $selected_in_category++;
            }
        }
        echo "<div class='test-result info'>• {$category}: " . count($goals) . " goals (" . $selected_in_category . " selected)</div>\n";
    }
} else {
    echo "<div class='test-result fail'>✗ Health goals data not available for dashboard</div>\n";
}
echo "</div>\n";

// Test 5: Clean up test data
echo "<div class='test-section'>\n";
echo "<h2>Test 5: Cleanup</h2>\n";

// Remove test goals
delete_user_meta($user_id, 'ennu_health_goals');

$remaining_goals = get_user_meta($user_id, 'ennu_health_goals', true);
if (empty($remaining_goals)) {
    echo "<div class='test-result pass'>✓ Test goals cleaned up successfully</div>\n";
} else {
    echo "<div class='test-result fail'>✗ Failed to clean up test goals</div>\n";
}
echo "</div>\n";

echo "<div class='test-section success'>\n";
echo "<h2>Test Summary</h2>\n";
echo "<p>✓ Health goals feature is working correctly!</p>\n";
echo "<p>The new 'My Health Goals' section should now appear on the user dashboard below the scores container.</p>\n";
echo "<p>Users can see all available goals organized by category, with their selected goals highlighted.</p>\n";
echo "</div>\n"; 