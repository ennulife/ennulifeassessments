<?php
/**
 * Check Symptoms Data for User 12
 * 
 * This script checks what symptom data exists for user 12
 */

// Load WordPress
require_once '../../../wp-load.php';

$user_id = 12;

echo "<h1>🔍 Symptom Data Check - User {$user_id}</h1>\n";

// Check hormone symptoms
$hormone_symptoms = get_user_meta($user_id, 'ennu_hormone_hormone_q1', true);
echo "<h2>Hormone Symptoms (ennu_hormone_hormone_q1)</h2>\n";
if (is_array($hormone_symptoms) && !empty($hormone_symptoms)) {
    echo "<p style='color: green;'>✅ HAS SYMPTOMS:</p>\n";
    echo "<pre>" . print_r($hormone_symptoms, true) . "</pre>\n";
} else {
    echo "<p style='color: red;'>❌ NO SYMPTOMS FOUND</p>\n";
    echo "<p>Value: " . (is_array($hormone_symptoms) ? 'Empty Array' : var_export($hormone_symptoms, true)) . "</p>\n";
}

// Check testosterone symptoms  
$testosterone_symptoms = get_user_meta($user_id, 'ennu_testosterone_testosterone_q1', true);
echo "<h2>Testosterone Symptoms (ennu_testosterone_testosterone_q1)</h2>\n";
if (is_array($testosterone_symptoms) && !empty($testosterone_symptoms)) {
    echo "<p style='color: green;'>✅ HAS SYMPTOMS:</p>\n";
    echo "<pre>" . print_r($testosterone_symptoms, true) . "</pre>\n";
} else {
    echo "<p style='color: red;'>❌ NO SYMPTOMS FOUND</p>\n";
    echo "<p>Value: " . (is_array($testosterone_symptoms) ? 'Empty Array' : var_export($testosterone_symptoms, true)) . "</p>\n";
}

// Check skin symptoms
$skin_symptoms = get_user_meta($user_id, 'ennu_skin_skin_q1', true);
echo "<h2>Skin Symptoms (ennu_skin_skin_q1)</h2>\n";
if (is_array($skin_symptoms) && !empty($skin_symptoms)) {
    echo "<p style='color: green;'>✅ HAS SYMPTOMS:</p>\n";
    echo "<pre>" . print_r($skin_symptoms, true) . "</pre>\n";
} else {
    echo "<p style='color: red;'>❌ NO SYMPTOMS FOUND</p>\n";
    echo "<p>Value: " . (is_array($skin_symptoms) ? 'Empty Array' : var_export($skin_symptoms, true)) . "</p>\n";
}

// Check hair symptoms
$hair_symptoms = get_user_meta($user_id, 'ennu_hair_hair_q1', true);
echo "<h2>Hair Symptoms (ennu_hair_hair_q1)</h2>\n";
if (is_array($hair_symptoms) && !empty($hair_symptoms)) {
    echo "<p style='color: green;'>✅ HAS SYMPTOMS:</p>\n";
    echo "<pre>" . print_r($hair_symptoms, true) . "</pre>\n";
} else {
    echo "<p style='color: red;'>❌ NO SYMPTOMS FOUND</p>\n";
    echo "<p>Value: " . (is_array($hair_symptoms) ? 'Empty Array' : var_export($hair_symptoms, true)) . "</p>\n";
}

// Check sleep symptoms
$sleep_symptoms = get_user_meta($user_id, 'ennu_sleep_sleep_q1', true);
echo "<h2>Sleep Symptoms (ennu_sleep_sleep_q1)</h2>\n";
if (is_array($sleep_symptoms) && !empty($sleep_symptoms)) {
    echo "<p style='color: green;'>✅ HAS SYMPTOMS:</p>\n";
    echo "<pre>" . print_r($sleep_symptoms, true) . "</pre>\n";
} else {
    echo "<p style='color: red;'>❌ NO SYMPTOMS FOUND</p>\n";
    echo "<p>Value: " . (is_array($sleep_symptoms) ? 'Empty Array' : var_export($sleep_symptoms, true)) . "</p>\n";
}

// Check weight loss symptoms
$weight_symptoms = get_user_meta($user_id, 'ennu_weight_loss_weight_q1', true);
echo "<h2>Weight Loss Symptoms (ennu_weight_loss_weight_q1)</h2>\n";
if (is_array($weight_symptoms) && !empty($weight_symptoms)) {
    echo "<p style='color: green;'>✅ HAS SYMPTOMS:</p>\n";
    echo "<pre>" . print_r($weight_symptoms, true) . "</pre>\n";
} else {
    echo "<p style='color: red;'>❌ NO SYMPTOMS FOUND</p>\n";
    echo "<p>Value: " . (is_array($weight_symptoms) ? 'Empty Array' : var_export($weight_symptoms, true)) . "</p>\n";
}

// Check centralized symptoms
$centralized = get_user_meta($user_id, 'ennu_centralized_symptoms', true);
echo "<h2>Centralized Symptoms (ennu_centralized_symptoms)</h2>\n";
if (is_array($centralized) && !empty($centralized)) {
    echo "<p style='color: green;'>✅ HAS CENTRALIZED SYMPTOMS:</p>\n";
    echo "<pre>" . print_r($centralized, true) . "</pre>\n";
} else {
    echo "<p style='color: red;'>❌ NO CENTRALIZED SYMPTOMS FOUND</p>\n";
    echo "<p>Value: " . (is_array($centralized) ? 'Empty Array' : var_export($centralized, true)) . "</p>\n";
}

// Check all ennu_ meta keys for this user
echo "<h2>All ENNU Meta Keys for User {$user_id}</h2>\n";
$all_meta = get_user_meta($user_id);
$ennu_meta = array();

foreach ($all_meta as $key => $values) {
    if (strpos($key, 'ennu_') === 0) {
        $ennu_meta[$key] = $values[0];
    }
}

if (empty($ennu_meta)) {
    echo "<p style='color: red;'>❌ No ENNU meta data found for user</p>\n";
} else {
    echo "<p style='color: green;'>✅ Found " . count($ennu_meta) . " ENNU meta entries:</p>\n";
    echo "<pre>" . print_r($ennu_meta, true) . "</pre>\n";
}

echo "<hr>\n";
echo "<h2>🔧 Next Steps:</h2>\n";
echo "<p>1. <strong>Complete an assessment with symptoms</strong> to test symptom reporting</p>\n";
echo "<p>2. <strong>Check if symptoms are being saved</strong> to user meta</p>\n";
echo "<p>3. <strong>Verify centralized symptoms manager</strong> is aggregating data</p>\n";

echo "<p><em>Check completed at: " . date('Y-m-d H:i:s') . "</em></p>\n";
?> 