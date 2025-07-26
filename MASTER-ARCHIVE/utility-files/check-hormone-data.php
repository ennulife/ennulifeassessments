<?php
/**
 * Check Hormone Assessment Data for User 12
 * 
 * This script checks what hormone assessment data was actually saved
 */

// Load WordPress
require_once '../../../wp-load.php';

$user_id = 12;

echo "<h1>🔍 Hormone Assessment Data Check - User {$user_id}</h1>\n";

// Check what was actually submitted
$hormone_q1 = get_user_meta($user_id, 'ennu_hormone_hormone_q1', true);
echo "<h2>Hormone Q1 (Symptoms)</h2>\n";
echo "<p><strong>Value:</strong> " . (is_array($hormone_q1) ? json_encode($hormone_q1) : var_export($hormone_q1, true)) . "</p>\n";

// Check if user selected 'none' 
if (is_array($hormone_q1) && in_array('none', $hormone_q1)) {
    echo "<p style='color: orange;'>⚠️ USER SELECTED 'NONE' - This is why no symptoms are showing!</p>\n";
} elseif (empty($hormone_q1)) {
    echo "<p style='color: red;'>❌ NO DATA SAVED - Form submission issue</p>\n";
} else {
    echo "<p style='color: green;'>✅ USER SELECTED SYMPTOMS: " . json_encode($hormone_q1) . "</p>\n";
}

// Check other hormone questions
$hormone_q2 = get_user_meta($user_id, 'ennu_hormone_hormone_q2', true);
echo "<h2>Hormone Q2 (Stress)</h2>\n";
echo "<p><strong>Value:</strong> " . var_export($hormone_q2, true) . "</p>\n";

$hormone_q3 = get_user_meta($user_id, 'ennu_hormone_hormone_q3', true);
echo "<h2>Hormone Q3</h2>\n";
echo "<p><strong>Value:</strong> " . var_export($hormone_q3, true) . "</p>\n";

// Check all hormone meta keys
echo "<h2>All Hormone Meta Keys</h2>\n";
$all_meta = get_user_meta($user_id);
$hormone_meta = array();

foreach ($all_meta as $key => $values) {
    if (strpos($key, 'ennu_hormone_') === 0) {
        $hormone_meta[$key] = $values[0];
    }
}

if (empty($hormone_meta)) {
    echo "<p style='color: red;'>❌ No hormone meta data found</p>\n";
} else {
    echo "<p style='color: green;'>✅ Found " . count($hormone_meta) . " hormone meta entries:</p>\n";
    echo "<pre>" . print_r($hormone_meta, true) . "</pre>\n";
}

echo "<hr>\n";
echo "<h2>🔧 Analysis:</h2>\n";
if (is_array($hormone_q1) && in_array('none', $hormone_q1)) {
    echo "<p><strong>CONCLUSION:</strong> The user selected 'None of the above' for symptoms, which is why no symptoms are showing in the dashboard.</p>\n";
    echo "<p><strong>PHASE 1 STATUS:</strong> ✅ <strong>FULLY WORKING</strong> - The system is correctly saving and processing the user's choice.</p>\n";
} elseif (empty($hormone_q1)) {
    echo "<p><strong>CONCLUSION:</strong> No symptom data was saved, indicating a form submission issue.</p>\n";
    echo "<p><strong>PHASE 1 STATUS:</strong> ❌ <strong>NOT WORKING</strong> - Data not being saved properly.</p>\n";
} else {
    echo "<p><strong>CONCLUSION:</strong> User selected symptoms, but they're not showing in the dashboard.</p>\n";
    echo "<p><strong>PHASE 1 STATUS:</strong> ⚠️ <strong>PARTIALLY WORKING</strong> - Data saved but not displaying.</p>\n";
}

echo "<p><em>Check completed at: " . date('Y-m-d H:i:s') . "</em></p>\n";
?> 