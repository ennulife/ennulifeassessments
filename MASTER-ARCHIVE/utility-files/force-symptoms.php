<?php
/**
 * Force Symptom Extraction Test
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

echo "<h1>Force Symptom Extraction Test</h1>";

// Step 1: Check current centralized symptoms
echo "<h2>1. Current Centralized Symptoms</h2>";
$current_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
echo "<p><strong>Current Symptoms:</strong></p>";
echo "<pre>" . print_r($current_symptoms, true) . "</pre>";

// Step 2: Force update centralized symptoms
echo "<h2>2. Forcing Symptom Update</h2>";
$update_result = ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms($user_id);
echo "<p><strong>Update Result:</strong> " . ($update_result ? 'Success' : 'Failed') . "</p>";

// Step 3: Check updated symptoms
echo "<h2>3. Updated Centralized Symptoms</h2>";
$updated_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
echo "<p><strong>Updated Symptoms:</strong></p>";
echo "<pre>" . print_r($updated_symptoms, true) . "</pre>";

// Step 4: Check individual weight loss symptoms
echo "<h2>4. Individual Weight Loss Symptoms</h2>";
$weight_loss_symptoms = ENNU_Centralized_Symptoms_Manager::get_assessment_symptoms($user_id, 'weight-loss');
echo "<p><strong>Weight Loss Symptoms:</strong></p>";
echo "<pre>" . print_r($weight_loss_symptoms, true) . "</pre>";

echo "<h2>Test Complete</h2>";
?> 