<?php
/**
 * Test Instant Symptom Extraction
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

echo "<h1>Test Instant Symptom Extraction</h1>";

// Step 1: Check current symptoms
echo "<h2>1. Current Symptoms Before Test</h2>";
$current_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
echo "<p><strong>Current Symptoms Count:</strong> " . count($current_symptoms['symptoms'] ?? []) . "</p>";

// Step 2: Manually trigger assessment completion action
echo "<h2>2. Triggering Assessment Completion Action</h2>";
echo "<p>Triggering 'ennu_assessment_completed' action for weight-loss assessment...</p>";

// Trigger the action manually
do_action( 'ennu_assessment_completed', $user_id, 'weight_loss' );

echo "<p><strong>Action triggered!</strong></p>";

// Step 3: Check symptoms after action
echo "<h2>3. Symptoms After Action</h2>";
$updated_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
echo "<p><strong>Updated Symptoms Count:</strong> " . count($updated_symptoms['symptoms'] ?? []) . "</p>";

if ( ! empty( $updated_symptoms['symptoms'] ) ) {
    echo "<p><strong>Extracted Symptoms:</strong></p>";
    echo "<ul>";
    foreach ( $updated_symptoms['symptoms'] as $symptom_name => $symptom_data ) {
        echo "<li><strong>{$symptom_name}</strong> - {$symptom_data['category']}</li>";
    }
    echo "</ul>";
} else {
    echo "<p><strong>No symptoms extracted!</strong></p>";
}

// Step 4: Check debug log
echo "<h2>4. Recent Debug Log Entries</h2>";
$debug_log = file_get_contents( WP_CONTENT_DIR . '/debug.log' );
$lines = explode( "\n", $debug_log );
$recent_lines = array_slice( $lines, -20 );
$symptom_entries = array();

foreach ( $recent_lines as $line ) {
    if ( strpos( $line, 'ENNU Centralized Symptoms' ) !== false ) {
        $symptom_entries[] = $line;
    }
}

if ( ! empty( $symptom_entries ) ) {
    echo "<p><strong>Recent Symptom Debug Entries:</strong></p>";
    echo "<pre>";
    foreach ( $symptom_entries as $entry ) {
        echo htmlspecialchars( $entry ) . "\n";
    }
    echo "</pre>";
} else {
    echo "<p><strong>No symptom debug entries found!</strong></p>";
}

echo "<h2>5. Test Complete</h2>";
echo "<p>The instant symptom extraction test has been completed. Check the debug log for detailed information about the extraction process.</p>";
?> 