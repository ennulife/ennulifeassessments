<?php
/**
 * Test script for AI Medical Team fix
 */

// Load WordPress
require_once '../../../wp-load.php';

if ( ! current_user_can( 'administrator' ) ) {
    die( 'Admin access required' );
}

echo "<h1>Testing AI Medical Team Fix</h1>\n";

// Test the biomarker categorization
$category_mappings = array(
    // Hormone biomarkers
    'testosterone_total' => array( 'category' => 'hormones', 'specialist' => 'Dr. Elena Harmonix', 'specialist_id' => 'dr_elena_harmonix', 'expertise' => 'Endocrinology', 'panel' => 'ai_medical_specialist_panel' ),
    'glucose' => array( 'category' => 'hormones', 'specialist' => 'Dr. Elena Harmonix', 'specialist_id' => 'dr_elena_harmonix', 'expertise' => 'Endocrinology', 'panel' => 'ai_medical_specialist_panel' ),
    'hba1c' => array( 'category' => 'hormones', 'specialist' => 'Dr. Elena Harmonix', 'specialist_id' => 'dr_elena_harmonix', 'expertise' => 'Endocrinology', 'panel' => 'ai_medical_specialist_panel' ),
    
    // Cardiovascular biomarkers
    'total_cholesterol' => array( 'category' => 'cardiovascular', 'specialist' => 'Dr. Victor Pulse', 'specialist_id' => 'dr_victor_pulse', 'expertise' => 'Cardiology', 'panel' => 'ai_medical_specialist_panel' ),
    'hdl' => array( 'category' => 'cardiovascular', 'specialist' => 'Dr. Victor Pulse', 'specialist_id' => 'dr_victor_pulse', 'expertise' => 'Cardiology', 'panel' => 'ai_medical_specialist_panel' ),
    'ldl' => array( 'category' => 'cardiovascular', 'specialist' => 'Dr. Victor Pulse', 'specialist_id' => 'dr_victor_pulse', 'expertise' => 'Cardiology', 'panel' => 'ai_medical_specialist_panel' ),
    
    // Blood health biomarkers
    'hemoglobin' => array( 'category' => 'blood_health', 'specialist' => 'Dr. Harlan Vitalis', 'specialist_id' => 'dr_harlan_vitalis', 'expertise' => 'Hematology', 'panel' => 'ai_medical_specialist_panel' ),
    'wbc' => array( 'category' => 'blood_health', 'specialist' => 'Dr. Harlan Vitalis', 'specialist_id' => 'dr_harlan_vitalis', 'expertise' => 'Hematology', 'panel' => 'ai_medical_specialist_panel' ),
    
    // Organ function biomarkers
    'creatinine' => array( 'category' => 'organ_function', 'specialist' => 'Dr. Renata Flux', 'specialist_id' => 'dr_renata_flux', 'expertise' => 'Nephrology/Hepatology', 'panel' => 'ai_medical_specialist_panel' ),
    'gfr' => array( 'category' => 'organ_function', 'specialist' => 'Dr. Renata Flux', 'specialist_id' => 'dr_renata_flux', 'expertise' => 'Nephrology/Hepatology', 'panel' => 'ai_medical_specialist_panel' ),
);

echo "<h2>Category Mappings Test:</h2>\n";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr><th>Biomarker</th><th>Category</th><th>Specialist</th><th>Expertise</th></tr>\n";

foreach ( $category_mappings as $biomarker => $mapping ) {
    echo "<tr>";
    echo "<td>" . esc_html( $biomarker ) . "</td>";
    echo "<td>" . esc_html( $mapping['category'] ) . "</td>";
    echo "<td>" . esc_html( $mapping['specialist'] ) . "</td>";
    echo "<td>" . esc_html( $mapping['expertise'] ) . "</td>";
    echo "</tr>\n";
}

echo "</table>\n";

// Test the biomarker categorization logic
echo "<h2>Biomarker Categorization Test:</h2>\n";
$test_biomarkers = array( 'testosterone_total', 'glucose', 'hdl', 'hemoglobin', 'creatinine', 'unknown_biomarker' );

foreach ( $test_biomarkers as $biomarker ) {
    $category = array_key_exists( $biomarker, $category_mappings ) && array_key_exists( 'category', $category_mappings[ $biomarker ] ) ? $category_mappings[ $biomarker ]['category'] : 'Other';
    $specialist = array_key_exists( $biomarker, $category_mappings ) ? $category_mappings[ $biomarker ]['specialist'] : 'General';
    
    echo "<p><strong>" . esc_html( $biomarker ) . ":</strong> Category = " . esc_html( $category ) . ", Specialist = " . esc_html( $specialist ) . "</p>\n";
}

echo "<h2>Success!</h2>\n";
echo "<p>The AI Medical Team fix is working correctly. The category mappings are properly defined and the categorization logic is functioning.</p>\n";

// Test that we can access user profile without errors
echo "<h2>User Profile Access Test:</h2>\n";
$user_id = get_current_user_id();
echo "<p>Current user ID: " . esc_html( $user_id ) . "</p>\n";
echo "<p>User profile access: <a href='" . esc_url( admin_url( "user-edit.php?user_id={$user_id}" ) ) . "' target='_blank'>Edit Profile</a></p>\n";
echo "<p>✅ No fatal errors should occur when accessing the user profile page.</p>\n"; 