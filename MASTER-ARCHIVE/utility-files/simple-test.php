<?php
// Simple test to verify instant workflow
echo "Testing instant workflow...<br>";

// Check if WordPress is loaded
if (!defined('ABSPATH')) {
    echo "WordPress not loaded<br>";
    exit;
}

echo "WordPress loaded successfully<br>";

// Check if user is admin
if (!current_user_can('manage_options')) {
    echo "Not admin user<br>";
    exit;
}

echo "Admin user confirmed<br>";

$user_id = 1;
echo "Testing with user ID: {$user_id}<br>";

// Test symptom extraction
if (class_exists('ENNU_Centralized_Symptoms_Manager')) {
    echo "Centralized Symptoms Manager exists<br>";
    
    // Trigger assessment completion
    do_action('ennu_assessment_completed', $user_id, 'weight_loss');
    echo "Assessment completion action triggered<br>";
    
    // Check symptoms
    $symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
    echo "Symptoms count: " . (isset($symptoms['total_count']) ? $symptoms['total_count'] : 0) . "<br>";
    
} else {
    echo "Centralized Symptoms Manager not found<br>";
}

echo "Test complete!";
?> 