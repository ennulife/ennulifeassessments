<?php
/**
 * Test Instant Assessment Workflow
 * 
 * @package ENNU_Life
 * @version 64.2.4
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Only run for admins
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Access denied' );
}

$user_id = 1; // Admin user

echo "<h1>Instant Assessment Workflow Test</h1>";

// Step 1: Clear existing data
echo "<h2>1. Clearing Existing Data</h2>";
delete_user_meta($user_id, 'ennu_centralized_symptoms');
delete_user_meta($user_id, 'ennu_biomarker_flags');

// Clear weight loss assessment data
for ($i = 1; $i <= 13; $i++) {
    delete_user_meta($user_id, "ennu_weight-loss_wl_q{$i}");
}

echo "<p>✅ Cleared existing symptoms and biomarker flags</p>";

// Step 2: Populate test weight loss assessment data
echo "<h2>2. Populating Test Weight Loss Assessment Data</h2>";

$test_data = array(
    'ennu_weight-loss_wl_q9' => array('thyroid', 'insulin_resistance'),
    'ennu_weight-loss_wl_q10' => 'somewhat',
    'ennu_weight-loss_wl_q5' => 'less_than_5',
    'ennu_weight-loss_wl_q6' => 'high',
    'ennu_weight-loss_wl_q8' => 'often',
    'ennu_weight-loss_score_calculated_at' => current_time('mysql')
);

foreach ($test_data as $key => $value) {
    update_user_meta($user_id, $key, $value);
    echo "<p>✅ Set {$key} = " . (is_array($value) ? implode(', ', $value) : $value) . "</p>";
}

// Step 3: Manually trigger the assessment completion workflow
echo "<h2>3. Triggering Instant Assessment Completion Workflow</h2>";

// Clear debug log first
error_log("=== INSTANT WORKFLOW TEST START ===");

// Trigger the assessment completion action
do_action('ennu_assessment_completed', $user_id, 'weight_loss');

echo "<p>✅ Triggered assessment completion action</p>";

// Step 4: Check results
echo "<h2>4. Checking Results</h2>";

// Check centralized symptoms
$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
echo "<h3>Centralized Symptoms:</h3>";
echo "<pre>" . print_r($centralized_symptoms, true) . "</pre>";

// Check biomarker flags
$flag_manager = new ENNU_Biomarker_Flag_Manager();
$user_flags = $flag_manager->get_user_flags($user_id);
echo "<h3>Biomarker Flags:</h3>";
echo "<pre>" . print_r($user_flags, true) . "</pre>";

// Step 5: Check debug log
echo "<h2>5. Debug Log Analysis</h2>";
$debug_log = file_get_contents(WP_CONTENT_DIR . '/debug.log');
$recent_logs = array_slice(explode("\n", $debug_log), -50);

echo "<h3>Recent Debug Log Entries:</h3>";
echo "<div style='background: #f5f5f5; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px; max-height: 400px; overflow-y: auto;'>";
foreach ($recent_logs as $log_entry) {
    if (strpos($log_entry, 'ENNU Centralized Symptoms') !== false || 
        strpos($log_entry, 'ENNU Biomarker') !== false ||
        strpos($log_entry, 'INSTANT WORKFLOW TEST') !== false) {
        echo htmlspecialchars($log_entry) . "<br>";
    }
}
echo "</div>";

// Step 6: Summary
echo "<h2>6. Workflow Summary</h2>";

$symptoms_count = isset($centralized_symptoms['total_count']) ? $centralized_symptoms['total_count'] : 0;
$flags_count = is_array($user_flags) ? count($user_flags) : 0;

echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 8px; border-left: 4px solid #4caf50;'>";
echo "<h3>✅ Instant Workflow Results:</h3>";
echo "<ul>";
echo "<li><strong>Symptoms Extracted:</strong> {$symptoms_count}</li>";
echo "<li><strong>Biomarkers Flagged:</strong> {$flags_count}</li>";
echo "<li><strong>Workflow Status:</strong> " . ($symptoms_count > 0 && $flags_count > 0 ? 'SUCCESS' : 'NEEDS ATTENTION') . "</li>";
echo "</ul>";
echo "</div>";

// Step 7: Expected Results
echo "<h2>7. Expected Results</h2>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;'>";
echo "<h3>Expected Symptoms (from weight loss assessment):</h3>";
echo "<ul>";
echo "<li>Thyroid (Medical Condition)</li>";
echo "<li>Insulin Resistance (Medical Condition)</li>";
echo "<li>Low Energy Level (Energy)</li>";
echo "<li>Poor Sleep Quality (Sleep)</li>";
echo "<li>High Stress Level (Stress)</li>";
echo "<li>Frequent Food Cravings (Cravings)</li>";
echo "</ul>";

echo "<h3>Expected Biomarker Flags:</h3>";
echo "<ul>";
echo "<li>tsh, t3, t4, thyroid_antibodies (from Thyroid)</li>";
echo "<li>insulin, glucose, hba1c, homa_ir (from Insulin Resistance)</li>";
echo "<li>vitamin_d, vitamin_b12, ferritin, tsh, cortisol (from Low Energy)</li>";
echo "<li>melatonin, cortisol, magnesium, thyroid_tsh (from Poor Sleep)</li>";
echo "<li>cortisol, magnesium, vitamin_d, thyroid_tsh (from High Stress)</li>";
echo "<li>insulin, glucose, cortisol, serotonin (from Food Cravings)</li>";
echo "</ul>";
echo "</div>";

echo "<h2>8. Test Complete</h2>";
echo "<p>This test verifies that both symptoms and biomarker flagging happen instantly when an assessment is submitted.</p>";
echo "<p><strong>Next Steps:</strong> Submit a real weight loss assessment to see the instant workflow in action!</p>";
?> 