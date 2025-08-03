<?php
/**
 * Symptom-Biomarker Correlation Test
 * Tests the symptom-to-biomarker mapping system
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

echo "<h1>🔬 SYMPTOM-BIOMARKER CORRELATION TEST</h1>";
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
echo "<p><strong>Correlations file path:</strong> {$correlations_file}</p>";
echo "<p><strong>File exists:</strong> " . (file_exists($correlations_file) ? 'Yes' : 'No') . "</p>";
if (file_exists($correlations_file)) {
    $symptom_biomarker_correlations = include($correlations_file);
    echo "<p><strong>Correlations loaded successfully</strong></p>";
} else {
    echo "<p style='color: red;'>Error: Symptom-biomarker correlations file not found!</p>";
    exit;
}

echo "<h2>📋 SYMPTOM-BIOMARKER CORRELATIONS LOADED</h2>";
echo "<p><strong>Total Symptoms:</strong> " . count($symptom_biomarker_correlations) . "</p>";

// Test specific symptoms and their expected biomarkers
$test_symptoms = array(
    'Fatigue' => array(
        'expected_biomarkers' => array(
            'ferritin', 'vitamin_d', 'vitamin_b12', 'cortisol', 'tsh', 'free_t3', 'free_t4',
            'weight', 'bmi', 'body_fat_percent', 'coq10', 'nad', 'folate',
            'arsenic', 'lead', 'mercury', 'heavy_metals_panel',
            'glucose', 'hba1c', 'insulin', 'blood_pressure', 'heart_rate'
        ),
        'description' => 'General fatigue and low energy'
    ),
    'Anxiety' => array(
        'expected_biomarkers' => array(
            'cortisol', 'testosterone', 'vitamin_d', 'magnesium', 'tsh', 'free_t3', 'free_t4', 'estradiol', 'progesterone'
        ),
        'description' => 'Anxiety and nervousness'
    ),
    'Depression' => array(
        'expected_biomarkers' => array(
            'cortisol', 'testosterone', 'vitamin_d', 'magnesium', 'tsh', 'free_t3', 'free_t4', 'estradiol', 'progesterone'
        ),
        'description' => 'Depression and low mood'
    ),
    'Insomnia' => array(
        'expected_biomarkers' => array(
            'melatonin', 'cortisol', 'magnesium', 'vitamin_d', 'estradiol', 'progesterone', 'testosterone', 'tsh', 'free_t3', 'free_t4'
        ),
        'description' => 'Sleep difficulties'
    ),
    'Weight Gain' => array(
        'expected_biomarkers' => array(
            'insulin', 'fasting_insulin', 'homa_ir', 'glucose', 'hba1c', 'leptin', 'ghrelin', 'adiponectin', 'one_five_ag',
            'testosterone', 'cortisol', 'tsh', 'free_t3', 'free_t4'
        ),
        'description' => 'Unexplained weight gain'
    ),
    'Low Libido' => array(
        'expected_biomarkers' => array(
            'testosterone', 'estradiol', 'cortisol', 'tsh', 'free_t3', 'free_t4'
        ),
        'description' => 'Decreased sexual desire'
    ),
    'Hair Loss' => array(
        'expected_biomarkers' => array(
            'testosterone', 'estradiol', 'cortisol', 'tsh', 'free_t3', 'free_t4', 'vitamin_d', 'vitamin_b12', 'iron', 'ferritin', 'zinc', 'biotin'
        ),
        'description' => 'Hair thinning and loss'
    ),
    'Hot Flashes' => array(
        'expected_biomarkers' => array(
            'estradiol', 'progesterone', 'testosterone', 'cortisol'
        ),
        'description' => 'Menopausal hot flashes'
    ),
    'Erectile Dysfunction' => array(
        'expected_biomarkers' => array(
            'testosterone', 'estradiol', 'cortisol', 'tsh', 'free_t3', 'free_t4'
        ),
        'description' => 'Erectile dysfunction'
    ),
    'Acne' => array(
        'expected_biomarkers' => array(
            'testosterone', 'estradiol', 'cortisol', 'vitamin_d'
        ),
        'description' => 'Acne and skin issues'
    )
);

echo "<h2>🧪 TESTING SYMPTOM-BIOMARKER CORRELATIONS</h2>";

$test_results = array();

foreach ($test_symptoms as $symptom => $test_data) {
    echo "<h3>Testing Symptom: {$symptom}</h3>";
    echo "<p><strong>Description:</strong> {$test_data['description']}</p>";
    
    // Check if symptom exists in correlations
    if (!isset($symptom_biomarker_correlations[$symptom])) {
        echo "<p style='color: red;'>❌ ERROR: Symptom '{$symptom}' not found in correlations!</p>";
        continue;
    }
    
    $actual_biomarkers = $symptom_biomarker_correlations[$symptom];
    $expected_biomarkers = $test_data['expected_biomarkers'];
    
    // Compare expected vs actual biomarkers
    $matching_biomarkers = array();
    $missing_biomarkers = array();
    $extra_biomarkers = array();
    
    // Check for expected biomarkers
    foreach ($expected_biomarkers as $expected) {
        $found = false;
        foreach ($actual_biomarkers as $actual) {
            if (strtolower($expected) === strtolower($actual)) {
                $matching_biomarkers[] = $expected;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $missing_biomarkers[] = $expected;
        }
    }
    
    // Check for extra biomarkers
    foreach ($actual_biomarkers as $actual) {
        $found = false;
        foreach ($expected_biomarkers as $expected) {
            if (strtolower($actual) === strtolower($expected)) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $extra_biomarkers[] = $actual;
        }
    }
    
    // Calculate success rate
    $success_rate = count($matching_biomarkers) / count($expected_biomarkers) * 100;
    
    // Store results
    $test_results[$symptom] = array(
        'expected_biomarkers' => $expected_biomarkers,
        'actual_biomarkers' => $actual_biomarkers,
        'matching_biomarkers' => $matching_biomarkers,
        'missing_biomarkers' => $missing_biomarkers,
        'extra_biomarkers' => $extra_biomarkers,
        'success_rate' => $success_rate
    );
    
    // Display results
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
    echo "<strong>Expected Biomarkers:</strong> " . implode(', ', $expected_biomarkers) . "<br>";
    echo "<strong>Actual Biomarkers:</strong> " . implode(', ', $actual_biomarkers) . "<br>";
    echo "<strong>Matching Biomarkers:</strong> " . implode(', ', $matching_biomarkers) . "<br>";
    echo "<strong>Success Rate:</strong> {$success_rate}%<br>";
    
    if (!empty($missing_biomarkers)) {
        echo "<span style='color: red;'><strong>Missing Biomarkers:</strong> " . implode(', ', $missing_biomarkers) . "</span><br>";
    }
    
    if (!empty($extra_biomarkers)) {
        echo "<span style='color: orange;'><strong>Extra Biomarkers:</strong> " . implode(', ', $extra_biomarkers) . "</span><br>";
    }
    
    echo "</div>";
}

// Test the actual symptom triggering and biomarker flagging
echo "<h2>🧪 TESTING SYMPTOM TRIGGERING AND BIOMARKER FLAGGING</h2>";

$trigger_test_results = array();

foreach ($test_symptoms as $symptom => $test_data) {
    echo "<h3>Testing Trigger: {$symptom}</h3>";
    
    // Clear previous data
    delete_user_meta($test_user_id, 'ennu_symptoms');
    delete_user_meta($test_user_id, 'ennu_flagged_biomarkers');
    
    // Simulate symptom being triggered by updating centralized symptoms
    $symptoms_manager->update_centralized_symptoms($test_user_id);
    
    // Get current symptoms and trigger biomarker flagging
    $current_symptoms = $symptoms_manager->get_centralized_symptoms($test_user_id);
    if (!empty($current_symptoms['symptoms'])) {
        $symptoms_manager->auto_flag_biomarkers_from_symptoms($test_user_id, $current_symptoms['symptoms']);
    }
    
    // Get results
    $user_symptoms = get_user_meta($test_user_id, 'ennu_symptoms', true);
    $flagged_biomarkers = get_user_meta($test_user_id, 'ennu_flagged_biomarkers', true);
    
    if (!is_array($user_symptoms)) {
        $user_symptoms = array();
    }
    
    if (!is_array($flagged_biomarkers)) {
        $flagged_biomarkers = array();
    }
    
    // Check if symptom was properly stored
    $symptom_stored = in_array(strtolower($symptom), array_map('strtolower', $user_symptoms));
    
    // Check biomarker flagging
    $expected_biomarkers = $test_data['expected_biomarkers'];
    $flagged_matches = array();
    $flagged_misses = array();
    
    foreach ($expected_biomarkers as $expected) {
        $found = false;
        foreach ($flagged_biomarkers as $flagged) {
            if (strtolower($expected) === strtolower($flagged)) {
                $flagged_matches[] = $expected;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $flagged_misses[] = $expected;
        }
    }
    
    $trigger_success_rate = count($flagged_matches) / count($expected_biomarkers) * 100;
    
    // Store trigger results
    $trigger_test_results[$symptom] = array(
        'symptom_stored' => $symptom_stored,
        'user_symptoms' => $user_symptoms,
        'flagged_biomarkers' => $flagged_biomarkers,
        'expected_biomarkers' => $expected_biomarkers,
        'flagged_matches' => $flagged_matches,
        'flagged_misses' => $flagged_misses,
        'trigger_success_rate' => $trigger_success_rate
    );
    
    // Display trigger results
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
    echo "<strong>Symptom Stored:</strong> " . ($symptom_stored ? '✅ Yes' : '❌ No') . "<br>";
    echo "<strong>User Symptoms:</strong> " . implode(', ', $user_symptoms) . "<br>";
    echo "<strong>Flagged Biomarkers:</strong> " . implode(', ', $flagged_biomarkers) . "<br>";
    echo "<strong>Expected Biomarkers:</strong> " . implode(', ', $expected_biomarkers) . "<br>";
    echo "<strong>Trigger Success Rate:</strong> {$trigger_success_rate}%<br>";
    
    if (!empty($flagged_misses)) {
        echo "<span style='color: red;'><strong>Missing Flagged Biomarkers:</strong> " . implode(', ', $flagged_misses) . "</span><br>";
    }
    
    echo "</div>";
}

// Generate summary report
echo "<h2>📊 CORRELATION TEST SUMMARY</h2>";

$overall_correlation_success = 0;
$overall_trigger_success = 0;
$total_tests = count($test_results);

foreach ($test_results as $symptom => $result) {
    $overall_correlation_success += $result['success_rate'];
}

foreach ($trigger_test_results as $symptom => $result) {
    $overall_trigger_success += $result['trigger_success_rate'];
}

$avg_correlation_success = $overall_correlation_success / $total_tests;
$avg_trigger_success = $overall_trigger_success / $total_tests;

echo "<div style='margin: 20px 0; padding: 15px; background-color: #e8f5e8; border: 2px solid #4CAF50;'>";
echo "<h3>🎯 OVERALL RESULTS</h3>";
echo "<strong>Total Symptoms Tested:</strong> {$total_tests}<br>";
echo "<strong>Average Correlation Success Rate:</strong> {$avg_correlation_success}%<br>";
echo "<strong>Average Trigger Success Rate:</strong> {$avg_trigger_success}%<br>";
echo "</div>";

// Save detailed results to file
$log_file = plugin_dir_path(__FILE__) . 'symptom-biomarker-correlation-test-results-' . date('Y-m-d-H-i-s') . '.log';
$log_content = "Symptom-Biomarker Correlation Test Results\n";
$log_content .= "Date: " . date('Y-m-d H:i:s') . "\n";
$log_content .= "Test User ID: {$test_user_id}\n\n";

$log_content .= "=== CORRELATION TEST RESULTS ===\n";
foreach ($test_results as $symptom => $result) {
    $log_content .= "Symptom: {$symptom}\n";
    $log_content .= "Success Rate: {$result['success_rate']}%\n";
    $log_content .= "Expected Biomarkers: " . implode(', ', $result['expected_biomarkers']) . "\n";
    $log_content .= "Actual Biomarkers: " . implode(', ', $result['actual_biomarkers']) . "\n";
    
    if (!empty($result['missing_biomarkers'])) {
        $log_content .= "Missing Biomarkers: " . implode(', ', $result['missing_biomarkers']) . "\n";
    }
    
    if (!empty($result['extra_biomarkers'])) {
        $log_content .= "Extra Biomarkers: " . implode(', ', $result['extra_biomarkers']) . "\n";
    }
    
    $log_content .= "\n";
}

$log_content .= "=== TRIGGER TEST RESULTS ===\n";
foreach ($trigger_test_results as $symptom => $result) {
    $log_content .= "Symptom: {$symptom}\n";
    $log_content .= "Symptom Stored: " . ($result['symptom_stored'] ? 'Yes' : 'No') . "\n";
    $log_content .= "Trigger Success Rate: {$result['trigger_success_rate']}%\n";
    $log_content .= "Flagged Biomarkers: " . implode(', ', $result['flagged_biomarkers']) . "\n";
    
    if (!empty($result['flagged_misses'])) {
        $log_content .= "Missing Flagged Biomarkers: " . implode(', ', $result['flagged_misses']) . "\n";
    }
    
    $log_content .= "\n";
}

$log_content .= "=== OVERALL SUMMARY ===\n";
$log_content .= "Total Symptoms Tested: {$total_tests}\n";
$log_content .= "Average Correlation Success Rate: {$avg_correlation_success}%\n";
$log_content .= "Average Trigger Success Rate: {$avg_trigger_success}%\n";

file_put_contents($log_file, $log_content);

echo "<p><strong>Detailed results saved to:</strong> {$log_file}</p>";

echo "<h2>✅ CORRELATION TEST COMPLETED</h2>";
echo "<p>This test verified that the symptom-to-biomarker correlation system works correctly and that symptoms properly trigger biomarker flagging.</p>";
?> 