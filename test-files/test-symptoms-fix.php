<?php
/**
 * Test Symptoms Fix Script
 *
 * This script manually triggers the symptoms update for user ID 1
 * to fix the empty symptoms display in the dashboard.
 */

// Load WordPress
require_once '../../../wp-load.php';

echo "<h1>ENNU Symptoms Fix Test</h1>\n";

// Check if centralized symptoms manager exists
if ( ! class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
	echo "<p style='color: red;'>ERROR: ENNU_Centralized_Symptoms_Manager class not found!</p>\n";
	exit;
}

$user_id = 1; // admin user

echo "<h2>Testing Symptoms Update for User ID: $user_id</h2>\n";

// Get current symptoms before update
$current_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $user_id );
echo "<h3>Current Symptoms (Before Update):</h3>\n";
echo '<pre>' . print_r( $current_symptoms, true ) . "</pre>\n";

// Force update symptoms
echo "<h3>Forcing Symptoms Update...</h3>\n";
$result = ENNU_Centralized_Symptoms_Manager::force_update_symptoms( $user_id );

if ( $result ) {
	echo "<p style='color: green;'>✅ Symptoms update completed successfully!</p>\n";
} else {
	echo "<p style='color: red;'>❌ Symptoms update failed!</p>\n";
}

// Get updated symptoms
$updated_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $user_id );
echo "<h3>Updated Symptoms (After Update):</h3>\n";
echo '<pre>' . print_r( $updated_symptoms, true ) . "</pre>\n";

// Get symptom history
$symptom_history = ENNU_Centralized_Symptoms_Manager::get_symptom_history( $user_id, 5 );
echo "<h3>Recent Symptom History:</h3>\n";
echo '<pre>' . print_r( $symptom_history, true ) . "</pre>\n";

// Get symptom analytics
$analytics = ENNU_Centralized_Symptoms_Manager::get_symptom_analytics( $user_id );
echo "<h3>Symptom Analytics:</h3>\n";
echo '<pre>' . print_r( $analytics, true ) . "</pre>\n";

echo "<h2>Test Complete!</h2>\n";
echo "<p>Check your dashboard now to see if symptoms are displaying correctly.</p>\n";


