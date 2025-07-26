<?php
/**
 * Test file for Biomarker Auto-Sync System
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_die('Please log in to test the biomarker auto-sync.');
}

// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

echo '<h1>Biomarker Auto-Sync Test</h1>';
echo '<h2>User: ' . esc_html($current_user->display_name) . ' (ID: ' . $user_id . ')</h2>';

// Check if the auto-sync class exists
if (!class_exists('ENNU_Biomarker_Auto_Sync')) {
    echo '<p style="color: red;">❌ ENNU_Biomarker_Auto_Sync class not found!</p>';
    exit;
}

$auto_sync = new ENNU_Biomarker_Auto_Sync();

echo '<h3>1. Current Global Fields Data</h3>';

// Display current global fields
$height_weight = get_user_meta($user_id, 'ennu_global_height_weight', true);
$health_goals = get_user_meta($user_id, 'ennu_global_health_goals', true);
$age = get_user_meta($user_id, 'ennu_age', true);
$gender = get_user_meta($user_id, 'ennu_gender', true);

echo '<h4>Height/Weight Data:</h4>';
echo '<pre>' . print_r($height_weight, true) . '</pre>';

echo '<h4>Health Goals:</h4>';
echo '<pre>' . print_r($health_goals, true) . '</pre>';

echo '<h4>Age:</h4>';
echo '<pre>' . print_r($age, true) . '</pre>';

echo '<h4>Gender:</h4>';
echo '<pre>' . print_r($gender, true) . '</pre>';

echo '<h3>2. Current Biomarker Data (Before Sync)</h3>';
$current_biomarkers = get_user_meta($user_id, 'ennu_user_biomarkers', true);
echo '<pre>' . print_r($current_biomarkers, true) . '</pre>';

echo '<h3>3. Running Auto-Sync</h3>';

// Run the auto-sync
$sync_results = $auto_sync->sync_user_biomarkers($user_id);

echo '<h4>Sync Results:</h4>';
echo '<pre>' . print_r($sync_results, true) . '</pre>';

echo '<h3>4. Updated Biomarker Data (After Sync)</h3>';
$updated_biomarkers = get_user_meta($user_id, 'ennu_user_biomarkers', true);
echo '<pre>' . print_r($updated_biomarkers, true) . '</pre>';

echo '<h3>5. Sync Status</h3>';
$sync_status = $auto_sync->get_sync_status($user_id);
echo '<pre>' . print_r($sync_status, true) . '</pre>';

echo '<h3>6. Individual User Meta Fields (Compatibility Check)</h3>';
$height = get_user_meta($user_id, 'ennu_height', true);
$weight = get_user_meta($user_id, 'ennu_weight', true);
$bmi = get_user_meta($user_id, 'ennu_bmi', true);

echo '<h4>Height (cm):</h4>';
echo '<pre>' . print_r($height, true) . '</pre>';

echo '<h4>Weight (kg):</h4>';
echo '<pre>' . print_r($weight, true) . '</pre>';

echo '<h4>BMI:</h4>';
echo '<pre>' . print_r($bmi, true) . '</pre>';

echo '<h3>7. Test Summary</h3>';

if ($sync_results['success']) {
    echo '<p style="color: green;">✅ Auto-sync completed successfully!</p>';
    echo '<p>Updated fields: ' . implode(', ', $sync_results['updated_fields']) . '</p>';
} else {
    echo '<p style="color: red;">❌ Auto-sync failed!</p>';
    echo '<p>Errors: ' . implode(', ', $sync_results['errors']) . '</p>';
}

echo '<hr>';
echo '<h3>How It Works</h3>';
echo '<ol>';
echo '<li><strong>Height/Weight Sync:</strong> Converts ft/in to cm and lbs to kg, calculates BMI</li>';
echo '<li><strong>Demographics Sync:</strong> Maps age and gender to biomarker format</li>';
echo '<li><strong>Health Goals Sync:</strong> Creates biomarker targets based on health goals</li>';
echo '<li><strong>Data Storage:</strong> Updates both biomarker array and individual meta fields</li>';
echo '<li><strong>Logging:</strong> Tracks all sync operations for debugging</li>';
echo '</ol>';

echo '<p><a href="javascript:history.back()">← Back</a></p>';
?> 