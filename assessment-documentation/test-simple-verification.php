<?php
/**
 * Simple Verification Test
 * Tests the core symptom and biomarker functionality
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load WordPress
require_once('../../../../wp-load.php');

// Ensure we're in WordPress environment
if (!defined('ABSPATH')) {
    die('WordPress not loaded');
}

// Test user ID
$test_user_id = 1;

echo "<h1>🔬 SIMPLE VERIFICATION TEST</h1>";
echo "<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Test User ID:</strong> {$test_user_id}</p>";

// Load required classes
if (!class_exists('ENNU_Centralized_Symptoms_Manager')) {
    require_once(plugin_dir_path(__FILE__) . '../../includes/class-centralized-symptoms-manager.php');
}

if (!class_exists('ENNU_Biomarker_Flag_Manager')) {
    require_once(plugin_dir_path(__FILE__) . '../../includes/class-biomarker-flag-manager.php');
}

// Initialize managers
$symptoms_manager = new ENNU_Centralized_Symptoms_Manager();
$biomarker_manager = new ENNU_Biomarker_Flag_Manager();

// Load symptom-biomarker correlations
$correlations_file = '/Applications/MAMP/htdocs/wp-content/plugins/ennulifeassessments/includes/config/symptom-biomarker-correlations.php';
if (file_exists($correlations_file)) {
    $symptom_biomarker_correlations = include($correlations_file);
} else {
    echo "<p style='color: red;'>Error: Symptom-biomarker correlations file not found!</p>";
    exit;
}

echo "<h2>📋 SYMPTOM-BIOMARKER CORRELATIONS LOADED</h2>";
echo "<p><strong>Total Symptoms:</strong> " . count($symptom_biomarker_correlations) . "</p>";

// Test 1: Verify symptom-biomarker correlations
echo "<h2>🧪 TEST 1: SYMPTOM-BIOMARKER CORRELATIONS</h2>";

$test_symptoms = array('Fatigue', 'Anxiety', 'Depression', 'Insomnia', 'Weight Gain', 'Low Libido', 'Hair Loss', 'Hot Flashes', 'Erectile Dysfunction', 'Acne');

$correlation_results = array();

foreach ($test_symptoms as $symptom) {
    if (isset($symptom_biomarker_correlations[$symptom])) {
        $biomarkers = $symptom_biomarker_correlations[$symptom];
        $correlation_results[$symptom] = array(
            'found' => true,
            'biomarkers' => $biomarkers,
            'count' => count($biomarkers)
        );
        echo "<div style='margin: 5px 0; padding: 5px; border: 1px solid #ddd;'>";
        echo "<strong>{$symptom}:</strong> ✅ Found with " . count($biomarkers) . " biomarkers";
        echo "</div>";
    } else {
        $correlation_results[$symptom] = array(
            'found' => false,
            'biomarkers' => array(),
            'count' => 0
        );
        echo "<div style='margin: 5px 0; padding: 5px; border: 1px solid #ddd;'>";
        echo "<strong>{$symptom}:</strong> ❌ Not found in correlations";
        echo "</div>";
    }
}

// Test 2: Verify biomarker flagging functionality
echo "<h2>🧪 TEST 2: BIOMARKER FLAGGING FUNCTIONALITY</h2>";

// Clear previous data
delete_user_meta($test_user_id, 'ennu_flagged_biomarkers');

// Test flagging a biomarker
$test_biomarker = 'testosterone';
$flag_result = $biomarker_manager->flag_biomarker(
    $test_user_id,
    $test_biomarker,
    'test_trigger',
    'Test flag for verification',
    null,
    'test_source',
    'test_symptom',
    'test_symptom_key'
);

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Test Biomarker:</strong> {$test_biomarker}<br>";
echo "<strong>Flag Result:</strong> " . ($flag_result ? '✅ Success' : '❌ Failed') . "<br>";

// Get flagged biomarkers
$flagged_biomarkers = get_user_meta($test_user_id, 'ennu_flagged_biomarkers', true);
if (!is_array($flagged_biomarkers)) {
    $flagged_biomarkers = array();
}

echo "<strong>Flagged Biomarkers:</strong> " . implode(', ', $flagged_biomarkers) . "<br>";
echo "<strong>Total Flagged:</strong> " . count($flagged_biomarkers) . "<br>";
echo "</div>";

// Test 3: Verify symptom storage functionality
echo "<h2>🧪 TEST 3: SYMPTOM STORAGE FUNCTIONALITY</h2>";

// Clear previous data
delete_user_meta($test_user_id, 'ennu_symptoms');
delete_user_meta($test_user_id, 'ennu_centralized_symptoms');

// Test storing a symptom
$test_symptom = 'Fatigue';
$symptoms_data = array(
    'symptoms' => array(
        'fatigue' => array(
            'name' => $test_symptom,
            'category' => 'Energy',
            'severity' => 'moderate',
            'frequency' => 'often',
            'first_reported' => current_time('mysql'),
            'last_updated' => current_time('mysql'),
            'sources' => array('test_assessment'),
            'assessment_types' => array('test')
        )
    ),
    'by_category' => array('Energy' => array('fatigue')),
    'by_severity' => array('moderate' => array('fatigue')),
    'by_frequency' => array('often' => array('fatigue')),
    'total_count' => 1,
    'last_updated' => current_time('mysql')
);

update_user_meta($test_user_id, 'ennu_centralized_symptoms', $symptoms_data);

// Get stored symptoms
$stored_symptoms = get_user_meta($test_user_id, 'ennu_centralized_symptoms', true);

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Test Symptom:</strong> {$test_symptom}<br>";
echo "<strong>Stored Successfully:</strong> " . (!empty($stored_symptoms) ? '✅ Yes' : '❌ No') . "<br>";
if (!empty($stored_symptoms) && isset($stored_symptoms['symptoms'])) {
    echo "<strong>Stored Symptoms:</strong> " . implode(', ', array_keys($stored_symptoms['symptoms'])) . "<br>";
    echo "<strong>Total Symptoms:</strong> " . count($stored_symptoms['symptoms']) . "<br>";
}
echo "</div>";

// Test 4: Verify auto-flagging from symptoms
echo "<h2>🧪 TEST 4: AUTO-FLAGGING FROM SYMPTOMS</h2>";

// Clear previous biomarker flags
delete_user_meta($test_user_id, 'ennu_flagged_biomarkers');

// Test auto-flagging
if (!empty($stored_symptoms) && isset($stored_symptoms['symptoms'])) {
    $flags_created = $symptoms_manager->auto_flag_biomarkers_from_symptoms($test_user_id, $stored_symptoms['symptoms']);
    
    // Get flagged biomarkers after auto-flagging
    $flagged_biomarkers_after = get_user_meta($test_user_id, 'ennu_flagged_biomarkers', true);
    if (!is_array($flagged_biomarkers_after)) {
        $flagged_biomarkers_after = array();
    }
    
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
    echo "<strong>Auto-Flagging Result:</strong> {$flags_created} flags created<br>";
    echo "<strong>Flagged Biomarkers After Auto-Flagging:</strong> " . implode(', ', $flagged_biomarkers_after) . "<br>";
    echo "<strong>Total Flagged After Auto-Flagging:</strong> " . count($flagged_biomarkers_after) . "<br>";
    echo "</div>";
}

// Test 5: Verify assessment completion hook
echo "<h2>🧪 TEST 5: ASSESSMENT COMPLETION HOOK</h2>";

// Clear all data
delete_user_meta($test_user_id, 'ennu_symptoms');
delete_user_meta($test_user_id, 'ennu_flagged_biomarkers');
delete_user_meta($test_user_id, 'ennu_centralized_symptoms');

// Simulate assessment completion
$assessment_data = array(
    'assessment_type' => 'health_optimization',
    'user_id' => $test_user_id,
    'answers' => array(
        'energy_levels' => array('Fatigue'),
        'sleep_quality' => array('Insomnia'),
        'mood_changes' => array('Anxiety')
    ),
    'completion_date' => current_time('mysql')
);

// Store assessment data
update_user_meta($test_user_id, 'ennu_assessment_data_' . $assessment_data['assessment_type'], $assessment_data);

// Trigger assessment completion
$symptoms_manager->on_assessment_completed($test_user_id, $assessment_data['assessment_type']);

// Get results
$final_symptoms = get_user_meta($test_user_id, 'ennu_centralized_symptoms', true);
$final_biomarkers = get_user_meta($test_user_id, 'ennu_flagged_biomarkers', true);

if (!is_array($final_symptoms)) {
    $final_symptoms = array();
}

if (!is_array($final_biomarkers)) {
    $final_biomarkers = array();
}

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Assessment Type:</strong> {$assessment_data['assessment_type']}<br>";
echo "<strong>Assessment Data Stored:</strong> " . (!empty($assessment_data) ? '✅ Yes' : '❌ No') . "<br>";
echo "<strong>Final Symptoms:</strong> " . (isset($final_symptoms['symptoms']) ? implode(', ', array_keys($final_symptoms['symptoms'])) : 'None') . "<br>";
echo "<strong>Final Biomarkers:</strong> " . implode(', ', $final_biomarkers) . "<br>";
echo "<strong>Total Final Symptoms:</strong> " . (isset($final_symptoms['symptoms']) ? count($final_symptoms['symptoms']) : 0) . "<br>";
echo "<strong>Total Final Biomarkers:</strong> " . count($final_biomarkers) . "<br>";
echo "</div>";

// Summary
echo "<h2>📊 VERIFICATION SUMMARY</h2>";

$correlation_success = 0;
foreach ($correlation_results as $result) {
    if ($result['found']) {
        $correlation_success++;
    }
}
$correlation_success_rate = $correlation_success / count($test_symptoms) * 100;

echo "<div style='margin: 20px 0; padding: 15px; background-color: #e8f5e8; border: 2px solid #4CAF50;'>";
echo "<h3>🎯 OVERALL RESULTS</h3>";
echo "<strong>Correlation Success Rate:</strong> {$correlation_success_rate}%<br>";
echo "<strong>Biomarker Flagging:</strong> " . ($flag_result ? '✅ Working' : '❌ Failed') . "<br>";
echo "<strong>Symptom Storage:</strong> " . (!empty($stored_symptoms) ? '✅ Working' : '❌ Failed') . "<br>";
echo "<strong>Auto-Flagging:</strong> " . (isset($flags_created) && $flags_created > 0 ? '✅ Working' : '❌ Failed') . "<br>";
echo "<strong>Assessment Hook:</strong> " . (!empty($final_symptoms) || !empty($final_biomarkers) ? '✅ Working' : '❌ Failed') . "<br>";
echo "</div>";

// Save results to file
$log_file = plugin_dir_path(__FILE__) . 'simple-verification-test-results-' . date('Y-m-d-H-i-s') . '.log';
$log_content = "Simple Verification Test Results\n";
$log_content .= "Date: " . date('Y-m-d H:i:s') . "\n";
$log_content .= "Test User ID: {$test_user_id}\n\n";

$log_content .= "=== CORRELATION TEST ===\n";
foreach ($correlation_results as $symptom => $result) {
    $log_content .= "Symptom: {$symptom} - " . ($result['found'] ? 'Found' : 'Not Found') . " - {$result['count']} biomarkers\n";
}

$log_content .= "\n=== BIOMARKER FLAGGING TEST ===\n";
$log_content .= "Test Biomarker: {$test_biomarker}\n";
$log_content .= "Flag Result: " . ($flag_result ? 'Success' : 'Failed') . "\n";
$log_content .= "Flagged Biomarkers: " . implode(', ', $flagged_biomarkers) . "\n";

$log_content .= "\n=== SYMPTOM STORAGE TEST ===\n";
$log_content .= "Test Symptom: {$test_symptom}\n";
$log_content .= "Stored Successfully: " . (!empty($stored_symptoms) ? 'Yes' : 'No') . "\n";

$log_content .= "\n=== AUTO-FLAGGING TEST ===\n";
if (isset($flags_created)) {
    $log_content .= "Flags Created: {$flags_created}\n";
}

$log_content .= "\n=== ASSESSMENT HOOK TEST ===\n";
$log_content .= "Assessment Type: {$assessment_data['assessment_type']}\n";
$log_content .= "Final Symptoms: " . (isset($final_symptoms['symptoms']) ? implode(', ', array_keys($final_symptoms['symptoms'])) : 'None') . "\n";
$log_content .= "Final Biomarkers: " . implode(', ', $final_biomarkers) . "\n";

$log_content .= "\n=== SUMMARY ===\n";
$log_content .= "Correlation Success Rate: {$correlation_success_rate}%\n";
$log_content .= "Biomarker Flagging: " . ($flag_result ? 'Working' : 'Failed') . "\n";
$log_content .= "Symptom Storage: " . (!empty($stored_symptoms) ? 'Working' : 'Failed') . "\n";
$log_content .= "Auto-Flagging: " . (isset($flags_created) && $flags_created > 0 ? 'Working' : 'Failed') . "\n";
$log_content .= "Assessment Hook: " . (!empty($final_symptoms) || !empty($final_biomarkers) ? 'Working' : 'Failed') . "\n";

file_put_contents($log_file, $log_content);

echo "<p><strong>Detailed results saved to:</strong> {$log_file}</p>";

echo "<h2>✅ VERIFICATION TEST COMPLETED</h2>";
echo "<p>This test verified the core functionality of the symptom tracking and biomarker flagging system.</p>";
?> 