<?php
/**
 * Populate Test Symptom Data for User 12
 * 
 * This script populates test symptom data to verify Phase 1 is working
 */

// Load WordPress
require_once '../../../wp-load.php';

$user_id = 12;

echo "<h1>🔧 Populate Test Symptom Data - User {$user_id}</h1>\n";

// Check if user exists
$user = get_user_by('ID', $user_id);
if ( ! $user ) {
    echo "<p style='color: red;'>❌ ERROR: User {$user_id} not found!</p>\n";
    exit;
}

echo "<p style='color: green;'>✅ User found: " . esc_html( $user->display_name ) . "</p>\n";

// Define test symptom data for different assessments
$test_symptom_data = array(
    // Hormone assessment symptoms
    'ennu_hormone_hormone_q1' => array(
        'fatigue',
        'mood_swings', 
        'low_libido',
        'sleep_issues'
    ),
    
    // Testosterone assessment symptoms
    'ennu_testosterone_testosterone_q1' => array(
        'low_energy',
        'decreased_muscle_mass',
        'mood_changes'
    ),
    
    // Skin assessment symptoms
    'ennu_skin_skin_q1' => array(
        'acne',
        'dryness',
        'aging_signs'
    ),
    
    // Hair assessment symptoms
    'ennu_hair_hair_q1' => array(
        'thinning',
        'hair_loss',
        'slow_growth'
    ),
    
    // Sleep assessment symptoms
    'ennu_sleep_sleep_q1' => array(
        'insomnia',
        'waking_frequently',
        'poor_quality_sleep'
    ),
    
    // Weight loss assessment symptoms
    'ennu_weight_loss_weight_q1' => array(
        'difficulty_losing_weight',
        'slow_metabolism',
        'cravings'
    )
);

echo "<h2>Populating Test Symptom Data:</h2>\n";

$success_count = 0;
$total_fields = count( $test_symptom_data );

foreach ( $test_symptom_data as $meta_key => $symptoms ) {
    $result = update_user_meta( $user_id, $meta_key, $symptoms );
    
    if ( $result ) {
        echo "<p style='color: green;'>✅ SUCCESS: {$meta_key}</p>\n";
        echo "<p>Symptoms: " . json_encode( $symptoms ) . "</p>\n";
        $success_count++;
    } else {
        echo "<p style='color: red;'>❌ FAILED: {$meta_key}</p>\n";
    }
}

echo "<h2>Results:</h2>\n";
echo "<p>Successfully populated: {$success_count}/{$total_fields} symptom fields</p>\n";

if ( $success_count === $total_fields ) {
    echo "<p style='color: green; font-weight: bold;'>🎉 ALL SYMPTOM DATA POPULATED SUCCESSFULLY!</p>\n";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ SOME FIELDS FAILED TO POPULATE</p>\n";
}

// Now trigger the centralized symptoms manager to aggregate the data
echo "<h2>Triggering Centralized Symptoms Manager:</h2>\n";

if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
    $result = ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $user_id );
    
    if ( $result ) {
        echo "<p style='color: green;'>✅ Centralized symptoms updated successfully</p>\n";
    } else {
        echo "<p style='color: red;'>❌ Failed to update centralized symptoms</p>\n";
    }
    
    // Check the centralized symptoms
    $centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $user_id );
    
    echo "<h3>Centralized Symptoms Result:</h3>\n";
    if ( ! empty( $centralized_symptoms['symptoms'] ) ) {
        echo "<p style='color: green;'>✅ SYMPTOMS FOUND: " . count( $centralized_symptoms['symptoms'] ) . " symptoms</p>\n";
        echo "<pre>" . print_r( $centralized_symptoms, true ) . "</pre>\n";
    } else {
        echo "<p style='color: red;'>❌ NO SYMPTOMS FOUND in centralized data</p>\n";
    }
} else {
    echo "<p style='color: red;'>❌ ENNU_Centralized_Symptoms_Manager class not found</p>\n";
}

echo "<hr>\n";
echo "<h2>🔧 Next Steps:</h2>\n";
echo "<p>1. <strong>Check User Dashboard:</strong> Go to the user dashboard and check the 'My Symptoms' tab</p>\n";
echo "<p>2. <strong>Verify Phase 1:</strong> If symptoms now show, Phase 1 is working correctly</p>\n";
echo "<p>3. <strong>Fix Form Submission:</strong> The real issue is that form data isn't being saved during assessment submission</p>\n";

echo "<p><em>Script completed at: " . date( 'Y-m-d H:i:s' ) . "</em></p>\n";
?> 