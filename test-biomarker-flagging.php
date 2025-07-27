<?php
/**
 * Test Biomarker Flagging
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

echo "<h1>Biomarker Flagging Test</h1>";

// Step 1: Clear existing flags
echo "<h2>1. Clearing Existing Flags</h2>";
delete_user_meta($user_id, 'ennu_biomarker_flags');
echo "<p>✅ Cleared existing biomarker flags</p>";

// Step 2: Test symptom to biomarker mapping
echo "<h2>2. Testing Symptom to Biomarker Mapping</h2>";

$test_symptoms = array(
    array('name' => 'thyroid', 'category' => 'Weight Loss - Medical Condition'),
    array('name' => 'insulin_resistance', 'category' => 'Weight Loss - Medical Condition'),
    array('name' => 'Low Energy Level', 'category' => 'Weight Loss - Energy'),
    array('name' => 'Poor Sleep Quality', 'category' => 'Weight Loss - Sleep'),
    array('name' => 'High Stress Level', 'category' => 'Weight Loss - Stress'),
    array('name' => 'Frequent Food Cravings', 'category' => 'Weight Loss - Cravings')
);

echo "<h3>Test Symptoms:</h3>";
echo "<ul>";
foreach ($test_symptoms as $symptom) {
    echo "<li>{$symptom['name']} ({$symptom['category']})</li>";
}
echo "</ul>";

// Step 3: Test biomarker flagging
echo "<h2>3. Testing Biomarker Flagging</h2>";

if (class_exists('ENNU_Centralized_Symptoms_Manager')) {
    $flags_created = ENNU_Centralized_Symptoms_Manager::auto_flag_biomarkers_from_symptoms($user_id, $test_symptoms);
    echo "<p>✅ Created {$flags_created} biomarker flags</p>";
} else {
    echo "<p>❌ ENNU_Centralized_Symptoms_Manager not found</p>";
}

// Step 4: Check results
echo "<h2>4. Checking Results</h2>";

$flag_manager = new ENNU_Biomarker_Flag_Manager();
$user_flags = $flag_manager->get_flagged_biomarkers($user_id);

echo "<h3>Biomarker Flags Created:</h3>";
if (is_array($user_flags) && !empty($user_flags)) {
    echo "<ul>";
    foreach ($user_flags as $flag_id => $flag_data) {
        echo "<li><strong>{$flag_data['biomarker_name']}</strong> - {$flag_data['reason']} (Flagged at: {$flag_data['flagged_at']})</li>";
    }
    echo "</ul>";
} else {
    echo "<p>❌ No biomarker flags found</p>";
}

// Step 5: Check database directly
echo "<h2>5. Database Check</h2>";

$flags_meta = get_user_meta($user_id, 'ennu_biomarker_flags', true);
echo "<h3>Raw Database Data:</h3>";
echo "<pre>" . print_r($flags_meta, true) . "</pre>";

echo "<h2>6. Test Complete</h2>";
echo "<p>This test verifies that biomarker flagging works when symptoms are provided.</p>";
?> 