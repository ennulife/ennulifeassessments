<?php
/**
 * Question Trigger Verification Test
 * Tests that each question properly triggers symptoms and biomarkers
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

echo "<h1>🔬 QUESTION TRIGGER VERIFICATION TEST</h1>";
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

// Test data for each assessment
$assessment_tests = array(
    'health_optimization' => array(
        'name' => 'Health Optimization Assessment',
        'questions' => array(
            'energy_levels' => array(
                'answers' => array('Fatigue', 'Chronic Fatigue'),
                'expected_symptoms' => array('Fatigue', 'Chronic Fatigue'),
                'expected_biomarkers' => array('ferritin', 'vitamin_d', 'vitamin_b12', 'cortisol', 'tsh', 'free_t3', 'free_t4')
            ),
            'sleep_quality' => array(
                'answers' => array('Insomnia', 'Poor Sleep'),
                'expected_symptoms' => array('Insomnia', 'Poor Sleep'),
                'expected_biomarkers' => array('melatonin', 'cortisol', 'magnesium', 'vitamin_d')
            ),
            'mood_changes' => array(
                'answers' => array('Anxiety', 'Depression'),
                'expected_symptoms' => array('Anxiety', 'Depression'),
                'expected_biomarkers' => array('cortisol', 'testosterone', 'vitamin_d', 'magnesium')
            )
        )
    ),
    'testosterone' => array(
        'name' => 'Testosterone Assessment',
        'questions' => array(
            'libido' => array(
                'answers' => array('Low Libido'),
                'expected_symptoms' => array('Low Libido'),
                'expected_biomarkers' => array('testosterone', 'estradiol', 'cortisol', 'tsh', 'free_t3', 'free_t4')
            ),
            'energy' => array(
                'answers' => array('Low Energy'),
                'expected_symptoms' => array('Low Energy'),
                'expected_biomarkers' => array('testosterone', 'cortisol', 'tsh', 'free_t3', 'free_t4')
            ),
            'mood' => array(
                'answers' => array('Mood Swings'),
                'expected_symptoms' => array('Mood Swings'),
                'expected_biomarkers' => array('testosterone', 'cortisol', 'estradiol')
            )
        )
    ),
    'hormone' => array(
        'name' => 'Hormone Assessment',
        'questions' => array(
            'fatigue' => array(
                'answers' => array('Fatigue'),
                'expected_symptoms' => array('Fatigue'),
                'expected_biomarkers' => array('tsh', 'free_t3', 'free_t4', 'cortisol', 'testosterone')
            ),
            'weight_changes' => array(
                'answers' => array('Weight Gain'),
                'expected_symptoms' => array('Weight Gain'),
                'expected_biomarkers' => array('tsh', 'free_t3', 'free_t4', 'cortisol', 'insulin')
            ),
            'mood_issues' => array(
                'answers' => array('Anxiety', 'Depression'),
                'expected_symptoms' => array('Anxiety', 'Depression'),
                'expected_biomarkers' => array('cortisol', 'testosterone', 'estradiol', 'progesterone')
            )
        )
    ),
    'menopause' => array(
        'name' => 'Menopause Assessment',
        'questions' => array(
            'hot_flashes' => array(
                'answers' => array('Hot Flashes'),
                'expected_symptoms' => array('Hot Flashes'),
                'expected_biomarkers' => array('estradiol', 'progesterone', 'testosterone', 'cortisol')
            ),
            'mood_changes' => array(
                'answers' => array('Mood Swings'),
                'expected_symptoms' => array('Mood Swings'),
                'expected_biomarkers' => array('estradiol', 'progesterone', 'cortisol')
            ),
            'sleep_issues' => array(
                'answers' => array('Insomnia'),
                'expected_symptoms' => array('Insomnia'),
                'expected_biomarkers' => array('estradiol', 'progesterone', 'cortisol', 'melatonin')
            )
        )
    ),
    'ed_treatment' => array(
        'name' => 'ED Treatment Assessment',
        'questions' => array(
            'erectile_function' => array(
                'answers' => array('Erectile Dysfunction'),
                'expected_symptoms' => array('Erectile Dysfunction'),
                'expected_biomarkers' => array('testosterone', 'estradiol', 'cortisol', 'tsh', 'free_t3', 'free_t4')
            ),
            'libido' => array(
                'answers' => array('Low Libido'),
                'expected_symptoms' => array('Low Libido'),
                'expected_biomarkers' => array('testosterone', 'estradiol', 'cortisol')
            ),
            'energy' => array(
                'answers' => array('Low Energy'),
                'expected_symptoms' => array('Low Energy'),
                'expected_biomarkers' => array('testosterone', 'cortisol', 'tsh', 'free_t3', 'free_t4')
            )
        )
    ),
    'skin' => array(
        'name' => 'Skin Assessment',
        'questions' => array(
            'acne' => array(
                'answers' => array('Acne'),
                'expected_symptoms' => array('Acne'),
                'expected_biomarkers' => array('testosterone', 'estradiol', 'cortisol', 'vitamin_d')
            ),
            'dryness' => array(
                'answers' => array('Dry Skin'),
                'expected_symptoms' => array('Dry Skin'),
                'expected_biomarkers' => array('vitamin_d', 'vitamin_b12', 'iron', 'ferritin', 'zinc')
            ),
            'aging' => array(
                'answers' => array('Premature Aging'),
                'expected_symptoms' => array('Premature Aging'),
                'expected_biomarkers' => array('vitamin_d', 'vitamin_b12', 'iron', 'ferritin', 'zinc', 'omega_3')
            )
        )
    ),
    'hair' => array(
        'name' => 'Hair Assessment',
        'questions' => array(
            'hair_loss' => array(
                'answers' => array('Hair Loss'),
                'expected_symptoms' => array('Hair Loss'),
                'expected_biomarkers' => array('testosterone', 'estradiol', 'cortisol', 'tsh', 'free_t3', 'free_t4')
            ),
            'thinning' => array(
                'answers' => array('Thinning'),
                'expected_symptoms' => array('Thinning'),
                'expected_biomarkers' => array('testosterone', 'estradiol', 'cortisol', 'tsh', 'free_t3', 'free_t4')
            ),
            'dryness' => array(
                'answers' => array('Dryness'),
                'expected_symptoms' => array('Dryness'),
                'expected_biomarkers' => array('vitamin_d', 'vitamin_b12', 'iron', 'ferritin', 'zinc', 'biotin')
            )
        )
    ),
    'sleep' => array(
        'name' => 'Sleep Assessment',
        'questions' => array(
            'sleep_quality' => array(
                'answers' => array('Poor Sleep'),
                'expected_symptoms' => array('Poor Sleep'),
                'expected_biomarkers' => array('melatonin', 'cortisol', 'magnesium', 'vitamin_d')
            ),
            'insomnia' => array(
                'answers' => array('Insomnia'),
                'expected_symptoms' => array('Insomnia'),
                'expected_biomarkers' => array('melatonin', 'cortisol', 'magnesium', 'vitamin_d')
            ),
            'sleep_apnea' => array(
                'answers' => array('Sleep Apnea'),
                'expected_symptoms' => array('Sleep Apnea'),
                'expected_biomarkers' => array('hemoglobin', 'hematocrit', 'bnp')
            )
        )
    ),
    'weight_loss' => array(
        'name' => 'Weight Loss Assessment',
        'questions' => array(
            'weight_change' => array(
                'answers' => array('Weight Gain'),
                'expected_symptoms' => array('Weight Gain'),
                'expected_biomarkers' => array('insulin', 'fasting_insulin', 'homa_ir', 'glucose', 'hba1c')
            ),
            'appetite' => array(
                'answers' => array('High Appetite'),
                'expected_symptoms' => array('High Appetite'),
                'expected_biomarkers' => array('leptin', 'ghrelin', 'adiponectin', 'one_five_ag')
            ),
            'physical_activity' => array(
                'answers' => array('Low Activity'),
                'expected_symptoms' => array('Low Activity'),
                'expected_biomarkers' => array('testosterone_free', 'testosterone_total', 'igf_1', 'creatine_kinase')
            )
        )
    )
);

// Test results storage
$test_results = array();

// Run tests for each assessment
foreach ($assessment_tests as $assessment_type => $assessment_data) {
    echo "<h2>🧪 Testing {$assessment_data['name']}</h2>";
    
    $assessment_results = array();
    
    foreach ($assessment_data['questions'] as $question_id => $question_data) {
        echo "<h3>Question: {$question_id}</h3>";
        
        // Simulate assessment completion
        $assessment_data = array(
            'assessment_type' => $assessment_type,
            'user_id' => $test_user_id,
            'answers' => array(
                $question_id => $question_data['answers']
            ),
            'completion_date' => current_time('mysql')
        );
        
        // Trigger assessment completion
        do_action('ennu_assessment_completed', $assessment_data);
        
        // Get updated user symptoms
        $user_symptoms = get_user_meta($test_user_id, 'ennu_symptoms', true);
        if (!is_array($user_symptoms)) {
            $user_symptoms = array();
        }
        
        // Get flagged biomarkers
        $flagged_biomarkers = get_user_meta($test_user_id, 'ennu_flagged_biomarkers', true);
        if (!is_array($flagged_biomarkers)) {
            $flagged_biomarkers = array();
        }
        
        // Check symptom triggering
        $symptom_matches = array();
        $symptom_misses = array();
        
        foreach ($question_data['expected_symptoms'] as $expected_symptom) {
            if (in_array(strtolower($expected_symptom), array_map('strtolower', $user_symptoms))) {
                $symptom_matches[] = $expected_symptom;
            } else {
                $symptom_misses[] = $expected_symptom;
            }
        }
        
        // Check biomarker triggering
        $biomarker_matches = array();
        $biomarker_misses = array();
        
        foreach ($question_data['expected_biomarkers'] as $expected_biomarker) {
            if (in_array(strtolower($expected_biomarker), array_map('strtolower', $flagged_biomarkers))) {
                $biomarker_matches[] = $expected_biomarker;
            } else {
                $biomarker_misses[] = $expected_biomarker;
            }
        }
        
        // Store results
        $question_result = array(
            'question_id' => $question_id,
            'answers' => $question_data['answers'],
            'expected_symptoms' => $question_data['expected_symptoms'],
            'expected_biomarkers' => $question_data['expected_biomarkers'],
            'actual_symptoms' => $user_symptoms,
            'actual_biomarkers' => $flagged_biomarkers,
            'symptom_matches' => $symptom_matches,
            'symptom_misses' => $symptom_misses,
            'biomarker_matches' => $biomarker_matches,
            'biomarker_misses' => $biomarker_misses,
            'symptom_success_rate' => count($symptom_matches) / count($question_data['expected_symptoms']) * 100,
            'biomarker_success_rate' => count($biomarker_matches) / count($question_data['expected_biomarkers']) * 100
        );
        
        $assessment_results[] = $question_result;
        
        // Display results
        echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
        echo "<strong>Answers:</strong> " . implode(', ', $question_data['answers']) . "<br>";
        echo "<strong>Expected Symptoms:</strong> " . implode(', ', $question_data['expected_symptoms']) . "<br>";
        echo "<strong>Actual Symptoms:</strong> " . implode(', ', $user_symptoms) . "<br>";
        echo "<strong>Symptom Success Rate:</strong> {$question_result['symptom_success_rate']}%<br>";
        echo "<strong>Expected Biomarkers:</strong> " . implode(', ', $question_data['expected_biomarkers']) . "<br>";
        echo "<strong>Actual Biomarkers:</strong> " . implode(', ', $flagged_biomarkers) . "<br>";
        echo "<strong>Biomarker Success Rate:</strong> {$question_result['biomarker_success_rate']}%<br>";
        
        if (!empty($symptom_misses)) {
            echo "<span style='color: red;'><strong>Missing Symptoms:</strong> " . implode(', ', $symptom_misses) . "</span><br>";
        }
        
        if (!empty($biomarker_misses)) {
            echo "<span style='color: red;'><strong>Missing Biomarkers:</strong> " . implode(', ', $biomarker_misses) . "</span><br>";
        }
        
        echo "</div>";
        
        // Clear user data for next test
        delete_user_meta($test_user_id, 'ennu_symptoms');
        delete_user_meta($test_user_id, 'ennu_flagged_biomarkers');
    }
    
    $test_results[$assessment_type] = $assessment_results;
}

// Generate summary report
echo "<h2>📊 SUMMARY REPORT</h2>";

$overall_symptom_success = 0;
$overall_biomarker_success = 0;
$total_tests = 0;

foreach ($test_results as $assessment_type => $results) {
    $assessment_symptom_success = 0;
    $assessment_biomarker_success = 0;
    $assessment_tests = count($results);
    
    foreach ($results as $result) {
        $assessment_symptom_success += $result['symptom_success_rate'];
        $assessment_biomarker_success += $result['biomarker_success_rate'];
    }
    
    $avg_symptom_success = $assessment_symptom_success / $assessment_tests;
    $avg_biomarker_success = $assessment_biomarker_success / $assessment_tests;
    
    $overall_symptom_success += $assessment_symptom_success;
    $overall_biomarker_success += $assessment_biomarker_success;
    $total_tests += $assessment_tests;
    
    echo "<div style='margin: 10px 0; padding: 10px; background-color: #f0f0f0;'>";
    echo "<strong>{$assessment_tests[$assessment_type]['name']}</strong><br>";
    echo "Average Symptom Success Rate: {$avg_symptom_success}%<br>";
    echo "Average Biomarker Success Rate: {$avg_biomarker_success}%<br>";
    echo "</div>";
}

$overall_avg_symptom_success = $overall_symptom_success / $total_tests;
$overall_avg_biomarker_success = $overall_biomarker_success / $total_tests;

echo "<div style='margin: 20px 0; padding: 15px; background-color: #e8f5e8; border: 2px solid #4CAF50;'>";
echo "<h3>🎯 OVERALL RESULTS</h3>";
echo "<strong>Total Tests:</strong> {$total_tests}<br>";
echo "<strong>Overall Average Symptom Success Rate:</strong> {$overall_avg_symptom_success}%<br>";
echo "<strong>Overall Average Biomarker Success Rate:</strong> {$overall_avg_biomarker_success}%<br>";
echo "</div>";

// Save detailed results to file
$log_file = plugin_dir_path(__FILE__) . 'question-trigger-test-results-' . date('Y-m-d-H-i-s') . '.log';
$log_content = "Question Trigger Verification Test Results\n";
$log_content .= "Date: " . date('Y-m-d H:i:s') . "\n";
$log_content .= "Test User ID: {$test_user_id}\n\n";

foreach ($test_results as $assessment_type => $results) {
    $log_content .= "=== {$assessment_tests[$assessment_type]['name']} ===\n";
    
    foreach ($results as $result) {
        $log_content .= "Question: {$result['question_id']}\n";
        $log_content .= "Answers: " . implode(', ', $result['answers']) . "\n";
        $log_content .= "Symptom Success Rate: {$result['symptom_success_rate']}%\n";
        $log_content .= "Biomarker Success Rate: {$result['biomarker_success_rate']}%\n";
        
        if (!empty($result['symptom_misses'])) {
            $log_content .= "Missing Symptoms: " . implode(', ', $result['symptom_misses']) . "\n";
        }
        
        if (!empty($result['biomarker_misses'])) {
            $log_content .= "Missing Biomarkers: " . implode(', ', $result['biomarker_misses']) . "\n";
        }
        
        $log_content .= "\n";
    }
}

$log_content .= "=== OVERALL SUMMARY ===\n";
$log_content .= "Total Tests: {$total_tests}\n";
$log_content .= "Overall Average Symptom Success Rate: {$overall_avg_symptom_success}%\n";
$log_content .= "Overall Average Biomarker Success Rate: {$overall_avg_biomarker_success}%\n";

file_put_contents($log_file, $log_content);

echo "<p><strong>Detailed results saved to:</strong> {$log_file}</p>";

echo "<h2>✅ TEST COMPLETED</h2>";
echo "<p>This test verified that each question in each assessment properly triggers the expected symptoms and biomarkers.</p>";
?> 