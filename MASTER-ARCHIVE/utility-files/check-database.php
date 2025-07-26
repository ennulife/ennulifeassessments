<?php
/**
 * Database Check Script
 * 
 * @package ENNU_Life
 * @version 64.2.4
 */

// Load WordPress
require_once('../../../wp-load.php');

// Only run for admins
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Access denied' );
}

global $wpdb;

echo "<h2>🔍 ENNU Database Check - Latest Assessment Data</h2>";
echo "<p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";

// Check latest user meta for user ID 1
$user_id = 1;

echo "<h3>📊 Latest User Meta Data (User ID: {$user_id})</h3>";

$latest_meta = $wpdb->get_results($wpdb->prepare(
    "SELECT meta_key, meta_value, umeta_id 
     FROM {$wpdb->usermeta} 
     WHERE user_id = %d AND meta_key LIKE 'ennu_%' 
     ORDER BY umeta_id DESC 
     LIMIT 15",
    $user_id
));

if ($latest_meta) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Meta Key</th><th>Meta Value Preview</th><th>Meta ID</th></tr>";
    
    foreach ($latest_meta as $meta) {
        $preview = substr($meta->meta_value, 0, 100);
        if (strlen($meta->meta_value) > 100) {
            $preview .= "...";
        }
        
        echo "<tr>";
        echo "<td>" . esc_html($meta->meta_key) . "</td>";
        echo "<td>" . esc_html($preview) . "</td>";
        echo "<td>" . esc_html($meta->umeta_id) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No ENNU user meta found for user {$user_id}</p>";
}

// Check centralized symptoms data
echo "<h3>🏥 Centralized Symptoms Data</h3>";

$centralized_symptoms = get_user_meta($user_id, 'ennu_centralized_symptoms', true);
if ($centralized_symptoms) {
    echo "<pre>" . print_r($centralized_symptoms, true) . "</pre>";
} else {
    echo "<p>No centralized symptoms data found</p>";
}

// Check symptom history
echo "<h3>📈 Symptom History</h3>";

$symptom_history = get_user_meta($user_id, 'ennu_symptom_history', true);
if ($symptom_history) {
    echo "<pre>" . print_r($symptom_history, true) . "</pre>";
} else {
    echo "<p>No symptom history found</p>";
}

// Check assessment data
echo "<h3>📋 Assessment Data</h3>";

$assessment_types = array('health', 'hormone', 'testosterone', 'menopause', 'ed_treatment', 'skin', 'hair', 'sleep', 'weight_loss', 'health_optimization');

foreach ($assessment_types as $type) {
    $assessment_data = get_user_meta($user_id, 'ennu_' . $type . '_assessment', true);
    if ($assessment_data) {
        echo "<h4>Assessment Type: {$type}</h4>";
        echo "<pre>" . print_r($assessment_data, true) . "</pre>";
    }
}

// Check biomarker data
echo "<h3>🔬 Biomarker Data</h3>";

$biomarker_data = get_user_meta($user_id, 'ennu_biomarkers', true);
if ($biomarker_data) {
    echo "<pre>" . print_r($biomarker_data, true) . "</pre>";
} else {
    echo "<p>No biomarker data found</p>";
}

// Test centralized symptoms manager
echo "<h3>🔄 Centralized Symptoms Manager Test</h3>";

if (class_exists('ENNU_Centralized_Symptoms_Manager')) {
    $manager_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
    echo "<h4>Manager Output:</h4>";
    echo "<pre>" . print_r($manager_symptoms, true) . "</pre>";
} else {
    echo "<p>Centralized Symptoms Manager class not found</p>";
}

echo "<h3>✅ Database Check Complete</h3>";
?> 