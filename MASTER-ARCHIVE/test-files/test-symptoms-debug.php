<?php
/**
 * ENNU Symptoms Debug & Fix Script
 * 
 * This script checks the current symptoms status and fixes any issues
 */

// Load WordPress
require_once '../../../wp-load.php';

$user_id = 1; // admin user

echo "<h1>🔍 ENNU Symptoms Debug & Fix - User {$user_id}</h1>\n";

// Check if user exists
$user = get_user_by('ID', $user_id);
if ( ! $user ) {
    echo "<p style='color: red;'>❌ ERROR: User {$user_id} not found!</p>\n";
    exit;
}

echo "<p style='color: green;'>✅ User found: " . esc_html( $user->display_name ) . "</p>\n";

// Check if centralized symptoms manager exists
if ( ! class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
    echo "<p style='color: red;'>❌ ENNU_Centralized_Symptoms_Manager class not found!</p>\n";
    exit;
}

echo "<h2>Step 1: Check Current User Meta for Symptoms</h2>\n";

// Check for existing symptom data in user meta
$all_meta = get_user_meta($user_id);
$symptom_meta = array();

foreach ($all_meta as $key => $values) {
    if (strpos($key, '_q1') !== false || strpos($key, 'symptom') !== false || strpos($key, 'ennu_') !== false) {
        $symptom_meta[$key] = $values[0];
    }
}

if (empty($symptom_meta)) {
    echo "<p style='color: orange;'>⚠️ No symptom meta found. Creating test data...</p>\n";
    
    // Create test symptom data
    $test_symptoms = array(
        'ennu_hormone_hormone_q1' => array('Fatigue', 'Brain Fog', 'Low Libido'),
        'ennu_skin_skin_q1' => array('Acne', 'Aging'),
        'ennu_sleep_sleep_q1' => array('Insomnia', 'Poor Sleep Quality'),
        'ennu_weight_loss_wl_q9' => array('Diabetes', 'High Blood Pressure'),
        'ennu_health_optimization_assessment_symptom_q1' => 'Chronic Fatigue',
        'ennu_health_optimization_assessment_symptom_q2' => 'Anxiety',
        'ennu_health_optimization_assessment_symptom_q3' => 'Depression',
        'ennu_health_optimization_assessment_symptom_q4' => 'Brain Fog',
        'ennu_health_optimization_assessment_symptom_q5' => 'Low Energy'
    );
    
    foreach ($test_symptoms as $key => $value) {
        update_user_meta($user_id, $key, $value);
        echo "<p style='color: green;'>✅ Created: {$key}</p>\n";
    }
    
    // Add timestamps
    update_user_meta($user_id, 'ennu_hormone_score_calculated_at', current_time('mysql'));
    update_user_meta($user_id, 'ennu_skin_score_calculated_at', current_time('mysql'));
    update_user_meta($user_id, 'ennu_sleep_score_calculated_at', current_time('mysql'));
    update_user_meta($user_id, 'ennu_weight_loss_score_calculated_at', current_time('mysql'));
    update_user_meta($user_id, 'ennu_health_optimization_assessment_score_calculated_at', current_time('mysql'));
    
} else {
    echo "<p style='color: green;'>✅ Found " . count($symptom_meta) . " symptom meta entries:</p>\n";
    echo "<pre>" . print_r($symptom_meta, true) . "</pre>\n";
}

echo "<h2>Step 2: Force Update Centralized Symptoms</h2>\n";

// Force update centralized symptoms
$result = ENNU_Centralized_Symptoms_Manager::force_update_symptoms($user_id);

if ($result) {
    echo "<p style='color: green;'>✅ Centralized symptoms updated successfully!</p>\n";
} else {
    echo "<p style='color: red;'>❌ Failed to update centralized symptoms!</p>\n";
}

echo "<h2>Step 3: Check Centralized Symptoms Result</h2>\n";

// Get centralized symptoms
$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
$symptom_analytics = ENNU_Centralized_Symptoms_Manager::get_symptom_analytics($user_id);

echo "<h3>Centralized Symptoms Data:</h3>\n";
if (!empty($centralized_symptoms['symptoms'])) {
    echo "<p style='color: green;'>✅ SYMPTOMS FOUND: " . count($centralized_symptoms['symptoms']) . " symptoms</p>\n";
    echo "<p><strong>Total Count:</strong> " . ($centralized_symptoms['total_count'] ?? 0) . "</p>\n";
    echo "<p><strong>Assessments:</strong> " . count($centralized_symptoms['by_assessment'] ?? array()) . "</p>\n";
    
    echo "<h4>Symptom Details:</h4>\n";
    foreach ($centralized_symptoms['symptoms'] as $symptom_name => $symptom_data) {
        echo "<p><strong>{$symptom_name}</strong> (Category: {$symptom_data['category']}, Assessments: " . implode(', ', $symptom_data['assessments']) . ")</p>\n";
    }
} else {
    echo "<p style='color: red;'>❌ NO SYMPTOMS FOUND in centralized data</p>\n";
}

echo "<h3>Symptom Analytics:</h3>\n";
echo "<pre>" . print_r($symptom_analytics, true) . "</pre>\n";

echo "<h2>Step 4: Test Dashboard Integration</h2>\n";

// Test the exact dashboard logic
$symptoms_exist = !empty($centralized_symptoms['symptoms']);
$total_symptoms = $centralized_symptoms['total_count'] ?? 0;
$unique_symptoms = count($centralized_symptoms['symptoms'] ?? array());

echo "<p><strong>Symptoms exist:</strong> " . ($symptoms_exist ? '✅ YES' : '❌ NO') . "</p>\n";
echo "<p><strong>Total symptoms:</strong> {$total_symptoms}</p>\n";
echo "<p><strong>Unique symptoms:</strong> {$unique_symptoms}</p>\n";

if ($symptoms_exist) {
    echo "<p style='color: green; font-weight: bold;'>🎉 SYMPTOMS SYSTEM IS WORKING CORRECTLY!</p>\n";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ SYMPTOMS SYSTEM STILL HAS ISSUES</p>\n";
}

echo "<h2>Test Complete!</h2>\n";
echo "<p>Check your dashboard now to see if symptoms are displaying correctly.</p>\n"; 