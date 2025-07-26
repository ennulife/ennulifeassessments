<?php
/**
 * Simple Test for Biomarker Flagging
 * Access via: http://localhost:8888/wp-content/plugins/ennulifeassessments/test-simple-flagging.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Security check
if (!current_user_can('manage_options')) {
    wp_die('Access denied');
}

$user_id = 1;

echo "<h1>🔬 Simple Biomarker Flagging Test</h1>";

// Test the auto-flagging function directly
$test_symptoms = array(
    array('name' => 'Fatigue'),
    array('name' => 'Low Libido'),
    array('name' => 'Brain Fog')
);

echo "<h2>🧪 Testing Auto-Flagging:</h2>";
echo "<p>Testing with symptoms: " . implode(', ', array_column($test_symptoms, 'name')) . "</p>";

// Call the auto-flagging function
$flags_created = ENNU_Centralized_Symptoms_Manager::auto_flag_biomarkers_from_symptoms($user_id, $test_symptoms);

echo "<p><strong>Flags Created:</strong> {$flags_created}</p>";

// Check the results
$flag_manager = new ENNU_Biomarker_Flag_Manager();
$flagged_biomarkers = $flag_manager->get_flagged_biomarkers($user_id, 'active');

echo "<h2>📊 Results:</h2>";
echo "<p><strong>Total Active Flags:</strong> " . count($flagged_biomarkers) . "</p>";

if (!empty($flagged_biomarkers)) {
    echo "<h3>🏷️ Flagged Biomarkers:</h3>";
    echo "<ul>";
    foreach ($flagged_biomarkers as $flag_id => $flag_data) {
        echo "<li><strong>" . esc_html($flag_data['biomarker_name']) . "</strong>";
        if (isset($flag_data['symptom'])) {
            echo " - Triggered by: " . esc_html($flag_data['symptom']);
        }
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "<p><strong>No biomarker flags found.</strong></p>";
}

echo "<h2>🔗 Test Dashboard</h2>";
echo "<p><a href='http://localhost/?page_id=2469' target='_blank' style='background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0;'>🏠 Check Dashboard</a></p>";

echo "<p><strong>Status:</strong> ✅ Test completed!</p>";
?> 