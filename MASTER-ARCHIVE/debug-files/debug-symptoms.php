<?php
/**
 * Debug Weight Loss Symptoms Extraction
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

echo "<h1>Weight Loss Symptoms Debug</h1>";

// Step 1: Check the weight loss symptoms method directly
echo "<h2>1. Testing Weight Loss Symptoms Method</h2>";

// Get the reflection of the method to call it
$reflection = new ReflectionClass('ENNU_Centralized_Symptoms_Manager');
$method = $reflection->getMethod('get_weight_loss_symptoms');
$method->setAccessible(true);

$symptoms = $method->invoke(null, $user_id);
echo "<p><strong>Direct Method Result:</strong></p>";
echo "<pre>" . print_r($symptoms, true) . "</pre>";

// Step 2: Check individual question data
echo "<h2>2. Individual Question Data</h2>";
$questions = array();
for ($i = 1; $i <= 13; $i++) {
    $meta_key = "ennu_weight-loss_wl_q{$i}";
    $value = get_user_meta($user_id, $meta_key, true);
    $questions[$meta_key] = $value;
}

echo "<p><strong>Question Data:</strong></p>";
echo "<pre>" . print_r($questions, true) . "</pre>";

// Step 3: Test specific conditions that should trigger symptoms
echo "<h2>3. Testing Specific Conditions</h2>";

// Test medical conditions (q9)
$medical_conditions = get_user_meta($user_id, 'ennu_weight-loss_wl_q9', true);
echo "<p><strong>Medical Conditions (Q9):</strong> ";
if (is_array($medical_conditions)) {
    echo implode(', ', $medical_conditions);
} else {
    echo "Not an array: " . var_export($medical_conditions, true);
}
echo "</p>";

// Test energy level (q10)
$energy_level = get_user_meta($user_id, 'ennu_weight-loss_wl_q10', true);
echo "<p><strong>Energy Level (Q10):</strong> " . $energy_level . "</p>";

// Test stress level (q6)
$stress_level = get_user_meta($user_id, 'ennu_weight-loss_wl_q6', true);
echo "<p><strong>Stress Level (Q6):</strong> " . $stress_level . "</p>";

// Test sleep quality (q5)
$sleep_quality = get_user_meta($user_id, 'ennu_weight-loss_wl_q5', true);
echo "<p><strong>Sleep Quality (Q5):</strong> " . $sleep_quality . "</p>";

// Test emotional eating (q8)
$emotional_eating = get_user_meta($user_id, 'ennu_weight-loss_wl_q8', true);
echo "<p><strong>Emotional Eating (Q8):</strong> " . $emotional_eating . "</p>";

// Step 4: Manually build expected symptoms
echo "<h2>4. Expected Symptoms Based on Data</h2>";
$expected_symptoms = array();

// Medical conditions
if (is_array($medical_conditions)) {
    foreach ($medical_conditions as $condition) {
        $expected_symptoms[] = array(
            'name' => $condition,
            'category' => 'Weight Loss - Medical Condition',
            'date' => current_time('mysql')
        );
    }
}

// Low energy
if ($energy_level === 'somewhat' || $energy_level === 'very') {
    $expected_symptoms[] = array(
        'name' => 'Low Energy',
        'category' => 'Weight Loss - Energy',
        'date' => current_time('mysql')
    );
}

// High stress
if ($stress_level === 'high' || $stress_level === 'very_high') {
    $expected_symptoms[] = array(
        'name' => 'High Stress',
        'category' => 'Weight Loss - Stress',
        'date' => current_time('mysql')
    );
}

// Poor sleep
if ($sleep_quality === 'less_than_5' || $sleep_quality === '5_to_6') {
    $expected_symptoms[] = array(
        'name' => 'Poor Sleep Quality',
        'category' => 'Weight Loss - Sleep',
        'date' => current_time('mysql')
    );
}

// Emotional eating
if ($emotional_eating === 'often' || $emotional_eating === 'very_often') {
    $expected_symptoms[] = array(
        'name' => 'Emotional Eating',
        'category' => 'Weight Loss - Behavior',
        'date' => current_time('mysql')
    );
}

echo "<p><strong>Expected Symptoms:</strong></p>";
echo "<pre>" . print_r($expected_symptoms, true) . "</pre>";

// Step 5: Force update centralized symptoms
echo "<h2>5. Forcing Centralized Symptoms Update</h2>";
$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
echo "<p><strong>Current Centralized Symptoms:</strong></p>";
echo "<pre>" . print_r($centralized_symptoms, true) . "</pre>";

// Step 6: Update centralized symptoms
$update_result = ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms($user_id);
echo "<p><strong>Update Result:</strong> " . ($update_result ? 'Success' : 'Failed') . "</p>";

// Step 7: Check updated symptoms
$updated_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
echo "<p><strong>Updated Centralized Symptoms:</strong></p>";
echo "<pre>" . print_r($updated_symptoms, true) . "</pre>";

echo "<h2>Debug Complete</h2>";
?> 