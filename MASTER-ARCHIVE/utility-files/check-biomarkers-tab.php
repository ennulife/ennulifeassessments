<?php
// Debug script to check biomarkers tab implementation
require_once("../../../wp-config.php");

// Set up WordPress environment
wp_load_alloptions();

// Get current user (assuming user 12 for testing)
$user_id = 12;
wp_set_current_user($user_id);

echo "<h1>🔬 Biomarkers Tab Debug - User $user_id</h1>";

// Check if required classes exist
echo "<h2>Class Availability Check:</h2>";
$classes_to_check = [
    "ENNU_Biomarker_Flag_Manager",
    "ENNU_Recommended_Range_Manager",
    "ENNU_Lab_Data_Landing_System"
];

foreach ($classes_to_check as $class) {
    if (class_exists($class)) {
        echo "<p style=\"color: green;\">✅ $class: Available</p>";
    } else {
        echo "<p style=\"color: red;\">❌ $class: Not Available</p>";
    }
}

// Check user biomarker data
echo "<h2>User Biomarker Data Check:</h2>";
$biomarker_data = get_user_meta($user_id, "ennu_biomarker_data", true);
$doctor_targets = get_user_meta($user_id, "ennu_doctor_targets", true);

if (!empty($biomarker_data)) {
    echo "<p style=\"color: green;\">✅ Biomarker data found:</p>";
    echo "<pre>" . print_r($biomarker_data, true) . "</pre>";
} else {
    echo "<p style=\"color: orange;\">⚠️ No biomarker data found - will show fallback content</p>";
}

if (!empty($doctor_targets)) {
    echo "<p style=\"color: green;\">✅ Doctor targets found:</p>";
    echo "<pre>" . print_r($doctor_targets, true) . "</pre>";
} else {
    echo "<p style=\"color: orange;\">⚠️ No doctor targets found</p>";
}

// Simulate the biomarkers tab logic
echo "<h2>Biomarkers Tab Content Simulation:</h2>";

if (class_exists("ENNU_Biomarker_Flag_Manager") && class_exists("ENNU_Recommended_Range_Manager")) {
    echo "<p style=\"color: green;\">✅ Both required classes available - will show biomarker grid</p>";
    
    if (!empty($biomarker_data)) {
        echo "<p style=\"color: green;\">✅ Will display biomarker data grid</p>";
    } else {
        echo "<p style=\"color: blue;\">📋 Will display fallback content (lab panel info + CTA)</p>";
    }
} else {
    echo "<p style=\"color: red;\">❌ Required classes missing - will show fallback content</p>";
}

echo "<hr>";
echo "<h2>🔧 Analysis:</h2>";
if (empty($biomarker_data)) {
    echo "<p style=\"color: blue; font-weight: bold;\">📋 CURRENT STATE: Showing fallback content (lab panel info + CTA)</p>";
    echo "<p><strong>Reason:</strong> No biomarker data available for user</p>";
    echo "<p><strong>User Experience:</strong> Sees comprehensive lab panel information with call-to-action to order tests</p>";
} else {
    echo "<p style=\"color: green; font-weight: bold;\">✅ CURRENT STATE: Would show biomarker data grid</p>";
    echo "<p><strong>User Experience:</strong> Would see personalized biomarker results with doctor recommendations</p>";
}

echo "<p><em>Debug completed at: " . date("Y-m-d H:i:s") . "</em></p>";
?>
