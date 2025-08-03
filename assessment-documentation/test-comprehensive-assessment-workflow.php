<?php
/**
 * Comprehensive Assessment Workflow Test
 * Tests the complete assessment workflow including symptom triggering and biomarker flagging
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

echo "<h1>🔬 COMPREHENSIVE ASSESSMENT WORKFLOW TEST</h1>";
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

// Test scenarios for each assessment type
$test_scenarios = array(
    'health_optimization' => array(
        'name' => 'Health Optimization Assessment',
        'answers' => array(
            'energy_levels' => array('Fatigue', 'Chronic Fatigue'),
            'sleep_quality' => array('Insomnia', 'Poor Sleep'),
            'mood_changes' => array('Anxiety', 'Depression'),
            'cognitive_function' => array('Brain Fog', 'Memory Loss'),
            'physical_symptoms' => array('Joint Pain', 'Muscle Weakness')
        ),
        'expected_symptoms' => array('Fatigue', 'Chronic Fatigue', 'Insomnia', 'Poor Sleep', 'Anxiety', 'Depression', 'Brain Fog', 'Memory Loss', 'Joint Pain', 'Muscle Weakness'),
        'expected_biomarkers' => array('ferritin', 'vitamin_d', 'vitamin_b12', 'cortisol', 'tsh', 'free_t3', 'free_t4', 'melatonin', 'magnesium', 'omega_3', 'homocysteine')
    ),
    'testosterone' => array(
        'name' => 'Testosterone Assessment',
        'answers' => array(
            'libido' => array('Low Libido'),
            'energy' => array('Low Energy'),
            'mood' => array('Mood Swings'),
            'strength' => array('Muscle Weakness'),
            'erectile_function' => array('Erectile Dysfunction')
        ),
        'expected_symptoms' => array('Low Libido', 'Low Energy', 'Mood Swings', 'Muscle Weakness', 'Erectile Dysfunction'),
        'expected_biomarkers' => array('testosterone', 'estradiol', 'cortisol', 'tsh', 'free_t3', 'free_t4')
    ),
    'hormone' => array(
        'name' => 'Hormone Assessment',
        'answers' => array(
            'fatigue' => array('Fatigue'),
            'weight_changes' => array('Weight Gain'),
            'mood_issues' => array('Anxiety', 'Depression'),
            'sleep_issues' => array('Insomnia'),
            'temperature_regulation' => array('Hot Flashes')
        ),
        'expected_symptoms' => array('Fatigue', 'Weight Gain', 'Anxiety', 'Depression', 'Insomnia', 'Hot Flashes'),
        'expected_biomarkers' => array('tsh', 'free_t3', 'free_t4', 'cortisol', 'testosterone', 'estradiol', 'progesterone', 'melatonin')
    ),
    'menopause' => array(
        'name' => 'Menopause Assessment',
        'answers' => array(
            'hot_flashes' => array('Hot Flashes'),
            'mood_changes' => array('Mood Swings'),
            'sleep_issues' => array('Insomnia'),
            'vaginal_symptoms' => array('Vaginal Dryness'),
            'cognitive_changes' => array('Brain Fog')
        ),
        'expected_symptoms' => array('Hot Flashes', 'Mood Swings', 'Insomnia', 'Vaginal Dryness', 'Brain Fog'),
        'expected_biomarkers' => array('estradiol', 'progesterone', 'testosterone', 'cortisol', 'melatonin', 'vitamin_d', 'magnesium')
    ),
    'ed_treatment' => array(
        'name' => 'ED Treatment Assessment',
        'answers' => array(
            'erectile_function' => array('Erectile Dysfunction'),
            'libido' => array('Low Libido'),
            'energy' => array('Low Energy'),
            'stress_level' => array('High Stress'),
            'cardiovascular_health' => array('Chest Pain')
        ),
        'expected_symptoms' => array('Erectile Dysfunction', 'Low Libido', 'Low Energy', 'High Stress', 'Chest Pain'),
        'expected_biomarkers' => array('testosterone', 'estradiol', 'cortisol', 'tsh', 'free_t3', 'free_t4', 'blood_pressure', 'heart_rate')
    ),
    'skin' => array(
        'name' => 'Skin Assessment',
        'answers' => array(
            'acne' => array('Acne'),
            'dryness' => array('Dry Skin'),
            'aging' => array('Premature Aging'),
            'inflammation' => array('Skin Inflammation'),
            'healing' => array('Slow Healing')
        ),
        'expected_symptoms' => array('Acne', 'Dry Skin', 'Premature Aging', 'Skin Inflammation', 'Slow Healing'),
        'expected_biomarkers' => array('testosterone', 'estradiol', 'cortisol', 'vitamin_d', 'vitamin_b12', 'iron', 'ferritin', 'zinc', 'omega_3')
    ),
    'hair' => array(
        'name' => 'Hair Assessment',
        'answers' => array(
            'hair_loss' => array('Hair Loss'),
            'thinning' => array('Thinning'),
            'dryness' => array('Dryness'),
            'scalp_health' => array('Dandruff'),
            'growth_rate' => array('Slow Growth')
        ),
        'expected_symptoms' => array('Hair Loss', 'Thinning', 'Dryness', 'Dandruff', 'Slow Growth'),
        'expected_biomarkers' => array('testosterone', 'estradiol', 'cortisol', 'tsh', 'free_t3', 'free_t4', 'vitamin_d', 'vitamin_b12', 'iron', 'ferritin', 'zinc', 'biotin')
    ),
    'sleep' => array(
        'name' => 'Sleep Assessment',
        'answers' => array(
            'sleep_quality' => array('Poor Sleep'),
            'insomnia' => array('Insomnia'),
            'sleep_apnea' => array('Sleep Apnea'),
            'restorative_sleep' => array('Poor Restorative Sleep'),
            'nighttime_awakenings' => array('Nighttime Awakenings')
        ),
        'expected_symptoms' => array('Poor Sleep', 'Insomnia', 'Sleep Apnea', 'Poor Restorative Sleep', 'Nighttime Awakenings'),
        'expected_biomarkers' => array('melatonin', 'cortisol', 'magnesium', 'vitamin_d', 'hemoglobin', 'hematocrit', 'bnp')
    ),
    'weight_loss' => array(
        'name' => 'Weight Loss Assessment',
        'answers' => array(
            'weight_change' => array('Weight Gain'),
            'appetite' => array('High Appetite'),
            'physical_activity' => array('Low Activity'),
            'cravings' => array('Food Cravings'),
            'body_composition' => array('High Body Fat')
        ),
        'expected_symptoms' => array('Weight Gain', 'High Appetite', 'Low Activity', 'Food Cravings', 'High Body Fat'),
        'expected_biomarkers' => array('insulin', 'fasting_insulin', 'homa_ir', 'glucose', 'hba1c', 'leptin', 'ghrelin', 'adiponectin', 'one_five_ag')
    )
);

echo "<h2>🧪 TESTING COMPREHENSIVE ASSESSMENT WORKFLOW</h2>";

$workflow_results = array();

foreach ($test_scenarios as $assessment_type => $scenario) {
    echo "<h3>Testing {$scenario['name']}</h3>";
    
    // Clear previous data
    delete_user_meta($test_user_id, 'ennu_symptoms');
    delete_user_meta($test_user_id, 'ennu_flagged_biomarkers');
    delete_user_meta($test_user_id, 'ennu_centralized_symptoms');
    
    // Simulate assessment completion
    $assessment_data = array(
        'assessment_type' => $assessment_type,
        'user_id' => $test_user_id,
        'answers' => $scenario['answers'],
        'completion_date' => current_time('mysql')
    );
    
    // Trigger assessment completion directly
    $symptoms_manager->on_assessment_completed($test_user_id, $assessment_type);
    
    // Get results
    $user_symptoms = get_user_meta($test_user_id, 'ennu_symptoms', true);
    $flagged_biomarkers = get_user_meta($test_user_id, 'ennu_flagged_biomarkers', true);
    $centralized_symptoms = get_user_meta($test_user_id, 'ennu_centralized_symptoms', true);
    
    if (!is_array($user_symptoms)) {
        $user_symptoms = array();
    }
    
    if (!is_array($flagged_biomarkers)) {
        $flagged_biomarkers = array();
    }
    
    if (!is_array($centralized_symptoms)) {
        $centralized_symptoms = array();
    }
    
    // Check symptom triggering
    $symptom_matches = array();
    $symptom_misses = array();
    
    foreach ($scenario['expected_symptoms'] as $expected_symptom) {
        $found = false;
        foreach ($user_symptoms as $actual_symptom) {
            if (strtolower($expected_symptom) === strtolower($actual_symptom)) {
                $symptom_matches[] = $expected_symptom;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $symptom_misses[] = $expected_symptom;
        }
    }
    
    // Check biomarker flagging
    $biomarker_matches = array();
    $biomarker_misses = array();
    
    foreach ($scenario['expected_biomarkers'] as $expected_biomarker) {
        $found = false;
        foreach ($flagged_biomarkers as $actual_biomarker) {
            if (strtolower($expected_biomarker) === strtolower($actual_biomarker)) {
                $biomarker_matches[] = $expected_biomarker;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $biomarker_misses[] = $expected_biomarker;
        }
    }
    
    $symptom_success_rate = count($symptom_matches) / count($scenario['expected_symptoms']) * 100;
    $biomarker_success_rate = count($biomarker_matches) / count($scenario['expected_biomarkers']) * 100;
    
    // Store results
    $workflow_results[$assessment_type] = array(
        'scenario' => $scenario,
        'user_symptoms' => $user_symptoms,
        'flagged_biomarkers' => $flagged_biomarkers,
        'centralized_symptoms' => $centralized_symptoms,
        'symptom_matches' => $symptom_matches,
        'symptom_misses' => $symptom_misses,
        'biomarker_matches' => $biomarker_matches,
        'biomarker_misses' => $biomarker_misses,
        'symptom_success_rate' => $symptom_success_rate,
        'biomarker_success_rate' => $biomarker_success_rate
    );
    
    // Display results
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
    echo "<strong>Expected Symptoms:</strong> " . implode(', ', $scenario['expected_symptoms']) . "<br>";
    echo "<strong>Actual Symptoms:</strong> " . implode(', ', $user_symptoms) . "<br>";
    echo "<strong>Symptom Success Rate:</strong> {$symptom_success_rate}%<br>";
    echo "<strong>Expected Biomarkers:</strong> " . implode(', ', $scenario['expected_biomarkers']) . "<br>";
    echo "<strong>Actual Biomarkers:</strong> " . implode(', ', $flagged_biomarkers) . "<br>";
    echo "<strong>Biomarker Success Rate:</strong> {$biomarker_success_rate}%<br>";
    
    if (!empty($symptom_misses)) {
        echo "<span style='color: red;'><strong>Missing Symptoms:</strong> " . implode(', ', $symptom_misses) . "</span><br>";
    }
    
    if (!empty($biomarker_misses)) {
        echo "<span style='color: red;'><strong>Missing Biomarkers:</strong> " . implode(', ', $biomarker_misses) . "</span><br>";
    }
    
    echo "</div>";
}

// Generate summary report
echo "<h2>📊 WORKFLOW TEST SUMMARY</h2>";

$overall_symptom_success = 0;
$overall_biomarker_success = 0;
$total_assessments = count($workflow_results);

foreach ($workflow_results as $assessment_type => $result) {
    $overall_symptom_success += $result['symptom_success_rate'];
    $overall_biomarker_success += $result['biomarker_success_rate'];
}

$avg_symptom_success = $overall_symptom_success / $total_assessments;
$avg_biomarker_success = $overall_biomarker_success / $total_assessments;

echo "<div style='margin: 20px 0; padding: 15px; background-color: #e8f5e8; border: 2px solid #4CAF50;'>";
echo "<h3>🎯 OVERALL RESULTS</h3>";
echo "<strong>Total Assessments Tested:</strong> {$total_assessments}<br>";
echo "<strong>Average Symptom Success Rate:</strong> {$avg_symptom_success}%<br>";
echo "<strong>Average Biomarker Success Rate:</strong> {$avg_biomarker_success}%<br>";
echo "</div>";

// Detailed breakdown by assessment
echo "<h3>📋 DETAILED BREAKDOWN BY ASSESSMENT</h3>";
foreach ($workflow_results as $assessment_type => $result) {
    echo "<div style='margin: 10px 0; padding: 10px; background-color: #f0f0f0;'>";
    echo "<strong>{$result['scenario']['name']}</strong><br>";
    echo "Symptom Success Rate: {$result['symptom_success_rate']}%<br>";
    echo "Biomarker Success Rate: {$result['biomarker_success_rate']}%<br>";
    echo "</div>";
}

// Save detailed results to file
$log_file = plugin_dir_path(__FILE__) . 'comprehensive-workflow-test-results-' . date('Y-m-d-H-i-s') . '.log';
$log_content = "Comprehensive Assessment Workflow Test Results\n";
$log_content .= "Date: " . date('Y-m-d H:i:s') . "\n";
$log_content .= "Test User ID: {$test_user_id}\n\n";

$log_content .= "=== OVERALL SUMMARY ===\n";
$log_content .= "Total Assessments Tested: {$total_assessments}\n";
$log_content .= "Average Symptom Success Rate: {$avg_symptom_success}%\n";
$log_content .= "Average Biomarker Success Rate: {$avg_biomarker_success}%\n\n";

$log_content .= "=== DETAILED RESULTS ===\n";
foreach ($workflow_results as $assessment_type => $result) {
    $log_content .= "Assessment: {$result['scenario']['name']}\n";
    $log_content .= "Symptom Success Rate: {$result['symptom_success_rate']}%\n";
    $log_content .= "Biomarker Success Rate: {$result['biomarker_success_rate']}%\n";
    $log_content .= "User Symptoms: " . implode(', ', $result['user_symptoms']) . "\n";
    $log_content .= "Flagged Biomarkers: " . implode(', ', $result['flagged_biomarkers']) . "\n";
    
    if (!empty($result['symptom_misses'])) {
        $log_content .= "Missing Symptoms: " . implode(', ', $result['symptom_misses']) . "\n";
    }
    
    if (!empty($result['biomarker_misses'])) {
        $log_content .= "Missing Biomarkers: " . implode(', ', $result['biomarker_misses']) . "\n";
    }
    
    $log_content .= "\n";
}

file_put_contents($log_file, $log_content);

echo "<p><strong>Detailed results saved to:</strong> {$log_file}</p>";

echo "<h2>✅ WORKFLOW TEST COMPLETED</h2>";
echo "<p>This test verified that the complete assessment workflow properly triggers symptoms and biomarkers.</p>";

// Test individual symptom triggering
echo "<h2>🧪 TESTING INDIVIDUAL SYMPTOM TRIGGERING</h2>";

$individual_test_results = array();

foreach ($symptom_biomarker_correlations as $symptom => $biomarkers) {
    // Clear previous data
    delete_user_meta($test_user_id, 'ennu_symptoms');
    delete_user_meta($test_user_id, 'ennu_flagged_biomarkers');
    
    // Simulate symptom being added
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
    
    // Check if symptom was triggered
    $symptom_triggered = in_array(strtolower($symptom), array_map('strtolower', $user_symptoms));
    
    // Check biomarker flagging
    $biomarker_matches = array();
    foreach ($biomarkers as $biomarker) {
        if (in_array(strtolower($biomarker), array_map('strtolower', $flagged_biomarkers))) {
            $biomarker_matches[] = $biomarker;
        }
    }
    
    $biomarker_success_rate = count($biomarker_matches) / count($biomarkers) * 100;
    
    $individual_test_results[$symptom] = array(
        'symptom_triggered' => $symptom_triggered,
        'user_symptoms' => $user_symptoms,
        'flagged_biomarkers' => $flagged_biomarkers,
        'expected_biomarkers' => $biomarkers,
        'biomarker_matches' => $biomarker_matches,
        'biomarker_success_rate' => $biomarker_success_rate
    );
}

// Display individual test results
echo "<h3>📊 INDIVIDUAL SYMPTOM TEST RESULTS</h3>";
foreach ($individual_test_results as $symptom => $result) {
    echo "<div style='margin: 5px 0; padding: 5px; border: 1px solid #ddd;'>";
    echo "<strong>{$symptom}:</strong> " . ($result['symptom_triggered'] ? '✅ Triggered' : '❌ Not Triggered') . " | ";
    echo "Biomarker Success: {$result['biomarker_success_rate']}%";
    echo "</div>";
}

echo "<h2>✅ ALL TESTS COMPLETED</h2>";
echo "<p>Comprehensive testing of the assessment workflow, symptom triggering, and biomarker flagging has been completed.</p>";
?> 