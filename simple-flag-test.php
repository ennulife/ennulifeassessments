<?php
// Simple biomarker flagging test
echo "Testing biomarker flagging...<br>";

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

// Test biomarker flagging directly
if (class_exists('ENNU_Biomarker_Flag_Manager')) {
    echo "Biomarker Flag Manager exists<br>";
    
    $flag_manager = new ENNU_Biomarker_Flag_Manager();
    echo "Flag manager created<br>";
    
    // Test flagging a biomarker
    $success = $flag_manager->flag_biomarker($user_id, 'tsh', 'test', 'Test flag', 'system');
    echo "Flag result: " . ($success ? 'SUCCESS' : 'FAILED') . "<br>";
    
    // Check if flag was created
    $flags = $flag_manager->get_flagged_biomarkers($user_id);
    echo "Flags count: " . count($flags) . "<br>";
    
    if (!empty($flags)) {
        echo "Flags found:<br>";
        foreach ($flags as $flag_id => $flag_data) {
            echo "- {$flag_data['biomarker_name']}: {$flag_data['reason']}<br>";
        }
    }
    
} else {
    echo "Biomarker Flag Manager not found<br>";
}

echo "Test complete!";
?> 