<?php
/**
 * Test Weight History Fix
 * Verifies historical weights remain unchanged when current weight is updated
 */

require_once('../../../wp-load.php');

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('Access denied. Please login as admin.');
}

$user_id = get_current_user_id();

// Include the weight ajax handler
require_once('includes/class-weight-ajax-handler.php');

echo "<!DOCTYPE html>";
echo "<html><head><title>Weight History Fix Test</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; padding: 20px; }";
echo ".success { color: green; font-weight: bold; }";
echo ".error { color: red; font-weight: bold; }";
echo ".box { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo "table { width: 100%; border-collapse: collapse; }";
echo "th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }";
echo "th { background: #2196F3; color: white; }";
echo "</style></head><body>";

echo "<h1>Weight History Fix Test</h1>";

// Get initial state
$initial_history = get_user_meta($user_id, 'weight_history', true);
$initial_weight = get_user_meta($user_id, 'weight', true);

echo "<div class='box'>";
echo "<h2>Initial State</h2>";
echo "<p>Current weight: <strong>" . ($initial_weight ?: 'Not set') . " lbs</strong></p>";

if (is_array($initial_history) && !empty($initial_history)) {
    echo "<h3>Initial History (" . count($initial_history) . " entries):</h3>";
    echo "<table>";
    echo "<tr><th>Date</th><th>Weight</th><th>BMI</th></tr>";
    foreach ($initial_history as $entry) {
        echo "<tr>";
        echo "<td>" . $entry['date'] . "</td>";
        echo "<td>" . $entry['weight'] . " lbs</td>";
        echo "<td>" . (isset($entry['bmi']) ? $entry['bmi'] : 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Store historical weights for comparison
    $historical_weights = array();
    foreach ($initial_history as $entry) {
        if ($entry['date'] !== date('Y-m-d')) {
            $historical_weights[$entry['date']] = $entry['weight'];
        }
    }
} else {
    echo "<p class='error'>No weight history found. Creating initial history...</p>";
    
    // Initialize history
    require_once('includes/class-weight-history-initializer.php');
    ENNU_Weight_History_Initializer::ensure_weight_history($user_id);
    
    $initial_history = get_user_meta($user_id, 'weight_history', true);
    if (is_array($initial_history)) {
        echo "<p class='success'>Created " . count($initial_history) . " history entries</p>";
        
        $historical_weights = array();
        foreach ($initial_history as $entry) {
            if ($entry['date'] !== date('Y-m-d')) {
                $historical_weights[$entry['date']] = $entry['weight'];
            }
        }
    }
}
echo "</div>";

// Simulate weight update
$new_weight = $initial_weight ? $initial_weight + 5 : 175; // Add 5 lbs or set to 175

echo "<div class='box'>";
echo "<h2>Simulating Weight Update</h2>";
echo "<p>Updating weight from <strong>" . ($initial_weight ?: 'unset') . "</strong> to <strong>$new_weight lbs</strong></p>";

// Create handler instance and update weight
$handler = new ENNU_Weight_Ajax_Handler();

// Update weight directly (simulating what AJAX would do)
update_user_meta($user_id, 'weight', $new_weight);
update_user_meta($user_id, 'ennu_global_weight', $new_weight);
update_user_meta($user_id, 'ennu_global_weight_lbs', $new_weight);

// Update composite field
$height_weight = get_user_meta($user_id, 'ennu_global_height_weight', true);
if (!is_array($height_weight)) {
    $height_weight = array();
}
$height_weight['weight'] = $new_weight;
$height_weight['lbs'] = $new_weight;
update_user_meta($user_id, 'ennu_global_height_weight', $height_weight);

// Call the private method using reflection (for testing)
$reflection = new ReflectionClass($handler);
$method = $reflection->getMethod('update_weight_history');
$method->setAccessible(true);
$method->invoke($handler, $user_id, $new_weight);

echo "<p class='success'>Weight updated!</p>";
echo "</div>";

// Check results
$updated_history = get_user_meta($user_id, 'weight_history', true);
$updated_weight = get_user_meta($user_id, 'weight', true);

echo "<div class='box'>";
echo "<h2>After Update</h2>";
echo "<p>Current weight: <strong>$updated_weight lbs</strong></p>";

if (is_array($updated_history) && !empty($updated_history)) {
    echo "<h3>Updated History (" . count($updated_history) . " entries):</h3>";
    echo "<table>";
    echo "<tr><th>Date</th><th>Weight</th><th>BMI</th><th>Status</th></tr>";
    
    $all_good = true;
    foreach ($updated_history as $entry) {
        $status = '';
        
        // Check if historical weights were preserved
        if (isset($historical_weights[$entry['date']])) {
            if ($historical_weights[$entry['date']] == $entry['weight']) {
                $status = '<span class="success">✓ Preserved</span>';
            } else {
                $status = '<span class="error">✗ Changed from ' . $historical_weights[$entry['date']] . '</span>';
                $all_good = false;
            }
        } else if ($entry['date'] === date('Y-m-d')) {
            if ($entry['weight'] == $new_weight) {
                $status = '<span class="success">✓ Today\'s weight</span>';
            } else {
                $status = '<span class="error">✗ Wrong value</span>';
                $all_good = false;
            }
        } else {
            $status = '<span style="color: gray;">New entry</span>';
        }
        
        echo "<tr>";
        echo "<td>" . $entry['date'] . "</td>";
        echo "<td>" . $entry['weight'] . " lbs</td>";
        echo "<td>" . (isset($entry['bmi']) ? $entry['bmi'] : 'N/A') . "</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($all_good) {
        echo "<h3 class='success'>✅ SUCCESS: Historical weights were preserved!</h3>";
        echo "<p>Only today's weight was updated. Historical entries remain unchanged.</p>";
    } else {
        echo "<h3 class='error'>❌ ISSUE: Some historical weights were modified!</h3>";
        echo "<p>Historical entries should not change when updating current weight.</p>";
    }
}
echo "</div>";

// Summary
echo "<div class='box'>";
echo "<h2>Summary</h2>";
echo "<ul>";
echo "<li>Initial weight: " . ($initial_weight ?: 'not set') . " lbs</li>";
echo "<li>New weight: $new_weight lbs</li>";
echo "<li>History entries: " . count($updated_history) . "</li>";
echo "<li>Today's date: " . date('Y-m-d') . "</li>";
echo "</ul>";

echo "<h3>Fix Applied:</h3>";
echo "<p>The weight history update method has been modified to:</p>";
echo "<ol>";
echo "<li>Keep all historical weight values unchanged</li>";
echo "<li>Only update/add today's weight entry</li>";
echo "<li>Recalculate BMI values based on current height</li>";
echo "<li>Preserve the integrity of historical data</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>