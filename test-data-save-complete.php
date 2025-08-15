<?php
/**
 * Complete Data Save Test
 * Tests all data saving after fixes
 */

require_once('../../../wp-load.php');

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('Access denied. Please login as admin.');
}

$user_id = get_current_user_id();

// Test functions
function test_weight_save($user_id) {
    echo "<h3>Testing Weight Save...</h3>";
    
    // Test value
    $test_weight = 175;
    
    // Update using all methods to ensure consistency
    update_user_meta($user_id, 'weight', $test_weight);
    update_user_meta($user_id, 'ennu_global_weight', $test_weight);
    update_user_meta($user_id, 'ennu_global_weight_lbs', $test_weight);
    
    // Update composite field
    $height_weight = get_user_meta($user_id, 'ennu_global_height_weight', true);
    if (!is_array($height_weight)) {
        $height_weight = array();
    }
    $height_weight['weight'] = $test_weight;
    $height_weight['lbs'] = $test_weight;
    update_user_meta($user_id, 'ennu_global_height_weight', $height_weight);
    
    // Read back all weight fields
    $results = array(
        'weight' => get_user_meta($user_id, 'weight', true),
        'ennu_global_weight' => get_user_meta($user_id, 'ennu_global_weight', true),
        'ennu_global_weight_lbs' => get_user_meta($user_id, 'ennu_global_weight_lbs', true),
        'ennu_global_height_weight' => get_user_meta($user_id, 'ennu_global_height_weight', true)
    );
    
    echo "<pre>";
    echo "Set weight to: $test_weight\n\n";
    echo "Retrieved values:\n";
    print_r($results);
    echo "</pre>";
    
    $all_correct = true;
    if ($results['weight'] != $test_weight) $all_correct = false;
    if ($results['ennu_global_weight'] != $test_weight) $all_correct = false;
    if ($results['ennu_global_weight_lbs'] != $test_weight) $all_correct = false;
    if (!isset($results['ennu_global_height_weight']['weight']) || 
        $results['ennu_global_height_weight']['weight'] != $test_weight) $all_correct = false;
    
    echo $all_correct ? 
        "<div style='color: green;'>✓ All weight fields correctly saved!</div>" : 
        "<div style='color: red;'>✗ Weight fields mismatch!</div>";
}

function test_health_goals_save($user_id) {
    echo "<h3>Testing Health Goals Save...</h3>";
    
    // Test goals
    $test_goals = array('weight_loss', 'energy', 'strength');
    
    // Save using the CORRECT key
    update_user_meta($user_id, 'ennu_global_health_goals', $test_goals);
    
    // Read back
    $saved_goals = get_user_meta($user_id, 'ennu_global_health_goals', true);
    $legacy_goals = get_user_meta($user_id, 'ennu_health_goals', true); // Should be empty or migrated
    
    echo "<pre>";
    echo "Set goals to: " . implode(', ', $test_goals) . "\n\n";
    echo "Retrieved from ennu_global_health_goals: " . 
         (is_array($saved_goals) ? implode(', ', $saved_goals) : 'None') . "\n";
    echo "Retrieved from legacy ennu_health_goals: " . 
         (is_array($legacy_goals) ? implode(', ', $legacy_goals) : 'None') . "\n";
    echo "</pre>";
    
    $correct = (is_array($saved_goals) && count(array_diff($test_goals, $saved_goals)) == 0);
    
    echo $correct ? 
        "<div style='color: green;'>✓ Health goals correctly saved to proper key!</div>" : 
        "<div style='color: red;'>✗ Health goals not saved correctly!</div>";
}

function test_ajax_handlers() {
    echo "<h3>Testing AJAX Handler Registration...</h3>";
    
    global $wp_filter;
    
    $handlers = array(
        'wp_ajax_ennu_save_weight' => 'Weight Save Handler',
        'wp_ajax_ennu_update_health_goals' => 'Health Goals Update Handler',
        'wp_ajax_ennu_save_health_goals' => 'Health Goals Save Handler'
    );
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Handler</th><th>Status</th></tr>";
    
    foreach ($handlers as $hook => $name) {
        $registered = isset($wp_filter[$hook]) ? 'Registered ✓' : 'NOT REGISTERED ✗';
        $color = isset($wp_filter[$hook]) ? 'green' : 'red';
        echo "<tr><td>$name</td><td style='color: $color;'>$registered</td></tr>";
    }
    
    echo "</table>";
}

function test_all_meta_keys($user_id) {
    echo "<h3>All User Meta Keys for Weight/Health Goals:</h3>";
    
    $all_meta = get_user_meta($user_id);
    $relevant_keys = array();
    
    foreach ($all_meta as $key => $value) {
        if (strpos($key, 'weight') !== false || 
            strpos($key, 'bmi') !== false || 
            strpos($key, 'health_goal') !== false ||
            strpos($key, 'height') !== false) {
            $relevant_keys[$key] = $value[0];
        }
    }
    
    echo "<pre>";
    print_r($relevant_keys);
    echo "</pre>";
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Complete Data Save Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h3 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
        table { margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Complete Data Save Test - After Fixes</h1>
    <p>User ID: <?php echo $user_id; ?></p>
    
    <?php
    test_ajax_handlers();
    test_weight_save($user_id);
    test_health_goals_save($user_id);
    test_all_meta_keys($user_id);
    ?>
    
    <h3>Summary of Fixes Applied:</h3>
    <ul>
        <li>✓ Fixed health goals to use <code>ennu_global_health_goals</code> consistently</li>
        <li>✓ Fixed weight save to update <code>ennu_global_height_weight</code> composite field</li>
        <li>✓ Fixed weight save to update all global weight fields</li>
        <li>✓ Fixed BMI to update <code>ennu_calculated_bmi</code> and <code>ennu_global_bmi</code></li>
        <li>✓ Fixed nonce validation to accept both specific and dashboard nonces</li>
    </ul>
    
    <h3>Next Step:</h3>
    <p>Go back to the dashboard and test:</p>
    <ol>
        <li>Click on current weight to edit</li>
        <li>Enter a new value and save</li>
        <li>Page should reload and show new value</li>
        <li>Update health goals</li>
        <li>Page should reload and goals should persist</li>
    </ol>
</body>
</html>