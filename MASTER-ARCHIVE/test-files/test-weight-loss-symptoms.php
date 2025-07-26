<?php
/**
 * Test Weight Loss Symptoms Extraction
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

echo "<h1>Weight Loss Symptoms Test</h1>";

// Step 1: Check current weight loss data
echo "<h2>1. Current Weight Loss Data</h2>";
$weight_loss_data = array();
for ($i = 1; $i <= 13; $i++) {
    $meta_key = "ennu_weight-loss_wl_q{$i}";
    $value = get_user_meta($user_id, $meta_key, true);
    $weight_loss_data[$meta_key] = $value;
    echo "<p><strong>{$meta_key}:</strong> " . (is_array($value) ? implode(', ', $value) : ($value ?: 'empty')) . "</p>";
}

// Step 2: Manually populate test data
echo "<h2>2. Populating Test Data</h2>";
$test_data = array(
    'ennu_weight-loss_wl_q9' => array('thyroid', 'insulin_resistance'), // Medical conditions
    'ennu_weight-loss_wl_q10' => 'somewhat', // Low energy
    'ennu_weight-loss_wl_q5' => 'less_than_5', // Poor sleep
    'ennu_weight-loss_wl_q6' => 'high', // High stress
    'ennu_weight-loss_wl_q8' => 'often', // Frequent cravings
    'ennu_weight-loss_score_calculated_at' => current_time('mysql')
);

foreach ($test_data as $key => $value) {
    update_user_meta($user_id, $key, $value);
    echo "<p>✅ Set {$key} = " . (is_array($value) ? implode(', ', $value) : $value) . "</p>";
}

// Step 3: Test symptom extraction
echo "<h2>3. Testing Symptom Extraction</h2>";
if (class_exists('ENNU_Centralized_Symptoms_Manager')) {
    $result = ENNU_Centralized_Symptoms_Manager::force_update_symptoms($user_id);
    echo "<p>✅ Force update completed</p>";
    
    $symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
    echo "<p><strong>Total Symptoms:</strong> " . count($symptoms['symptoms'] ?? array()) . "</p>";
    
    if (!empty($symptoms['symptoms'])) {
        echo "<h3>Extracted Symptoms:</h3><ul>";
        foreach ($symptoms['symptoms'] as $symptom) {
            echo "<li><strong>{$symptom['name']}</strong> - {$symptom['category']} (Added: {$symptom['date']})</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>❌ No symptoms extracted</p>";
    }
} else {
    echo "<p>❌ ENNU_Centralized_Symptoms_Manager not available</p>";
}

// Step 4: Check database after update
echo "<h2>4. Database Check After Update</h2>";
$centralized_symptoms = get_user_meta($user_id, 'ennu_centralized_symptoms', true);
echo "<p><strong>Centralized Symptoms Data:</strong></p>";
echo "<pre>" . print_r($centralized_symptoms, true) . "</pre>";

echo "<h2>Test Complete</h2>";
?> 