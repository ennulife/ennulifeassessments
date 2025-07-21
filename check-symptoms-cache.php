<?php
/**
 * Check Symptoms Cache Issue
 * 
 * This script checks if there's a cache issue preventing symptoms from showing
 */

// Load WordPress
require_once '../../../wp-load.php';

$user_id = 12;

echo "<h1>🔍 Symptoms Cache Debug - User {$user_id}</h1>\n";

// Check if user exists
$user = get_user_by('ID', $user_id);
if ( ! $user ) {
    echo "<p style='color: red;'>❌ ERROR: User {$user_id} not found!</p>\n";
    exit;
}

echo "<p style='color: green;'>✅ User found: " . esc_html( $user->display_name ) . "</p>\n";

// Check the raw user meta for symptoms
echo "<h2>Raw User Meta Check:</h2>\n";

$all_meta = get_user_meta($user_id);
$symptom_meta = array();

foreach ($all_meta as $key => $values) {
    if (strpos($key, '_q1') !== false || strpos($key, 'symptom') !== false) {
        $symptom_meta[$key] = $values[0];
    }
}

if (empty($symptom_meta)) {
    echo "<p style='color: red;'>❌ NO SYMPTOM META FOUND!</p>\n";
} else {
    echo "<p style='color: green;'>✅ Found " . count($symptom_meta) . " symptom meta entries:</p>\n";
    echo "<pre>" . print_r($symptom_meta, true) . "</pre>\n";
}

// Check centralized symptoms cache
echo "<h2>Centralized Symptoms Cache Check:</h2>\n";

$cache_key = 'ennu_centralized_symptoms_' . $user_id;
$cached_symptoms = get_transient($cache_key);

if ($cached_symptoms === false) {
    echo "<p style='color: orange;'>⚠️ No cached symptoms found</p>\n";
} else {
    echo "<p style='color: green;'>✅ Cached symptoms found:</p>\n";
    echo "<pre>" . print_r($cached_symptoms, true) . "</pre>\n";
}

// Check if the centralized symptoms manager class exists and has the right methods
echo "<h2>Class Method Check:</h2>\n";

if (class_exists('ENNU_Centralized_Symptoms_Manager')) {
    echo "<p style='color: green;'>✅ ENNU_Centralized_Symptoms_Manager class exists</p>\n";
    
    $methods = get_class_methods('ENNU_Centralized_Symptoms_Manager');
    echo "<p><strong>Available methods:</strong> " . implode(', ', $methods) . "</p>\n";
    
    // Check if update method exists
    if (method_exists('ENNU_Centralized_Symptoms_Manager', 'update_centralized_symptoms')) {
        echo "<p style='color: green;'>✅ update_centralized_symptoms method exists</p>\n";
    } else {
        echo "<p style='color: red;'>❌ update_centralized_symptoms method missing</p>\n";
    }
    
    if (method_exists('ENNU_Centralized_Symptoms_Manager', 'get_centralized_symptoms')) {
        echo "<p style='color: green;'>✅ get_centralized_symptoms method exists</p>\n";
    } else {
        echo "<p style='color: red;'>❌ get_centralized_symptoms method missing</p>\n";
    }
} else {
    echo "<p style='color: red;'>❌ ENNU_Centralized_Symptoms_Manager class not found</p>\n";
}

// Force clear cache and regenerate
echo "<h2>Force Cache Clear and Regenerate:</h2>\n";

// Clear the cache
delete_transient($cache_key);
echo "<p style='color: green;'>✅ Cleared symptoms cache</p>\n";

// Force regenerate
if (class_exists('ENNU_Centralized_Symptoms_Manager')) {
    $result = ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms($user_id);
    echo "<p><strong>Update result:</strong> " . ($result ? '✅ Success' : '❌ Failed') . "</p>\n";
    
    // Check again
    $new_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
    echo "<h3>Regenerated Symptoms:</h3>\n";
    echo "<pre>" . print_r($new_symptoms, true) . "</pre>\n";
    
    if (!empty($new_symptoms['symptoms'])) {
        echo "<p style='color: green; font-weight: bold;'>🎉 SUCCESS! Symptoms now available!</p>\n";
    } else {
        echo "<p style='color: red; font-weight: bold;'>❌ Still no symptoms after regeneration!</p>\n";
    }
}

echo "<hr>\n";
echo "<h2>🔧 Next Steps:</h2>\n";
echo "<p>1. <strong>Check User Dashboard:</strong> If symptoms now show, it was a cache issue</p>\n";
echo "<p>2. <strong>Check Assessment Data:</strong> If still no symptoms, the assessment data is missing</p>\n";

echo "<p><em>Debug completed at: " . date('Y-m-d H:i:s') . "</em></p>\n";
?> 