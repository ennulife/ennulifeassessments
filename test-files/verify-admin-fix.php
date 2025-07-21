<?php
/**
 * ENNU Life Admin Fix Verification
 * 
 * Access this file via: /wp-content/plugins/ennulifeassessments/verify-admin-fix.php
 * 
 * @package ENNU_Life
 * @version 57.2.5
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check permissions
if (!current_user_can('manage_options')) {
    wp_die('Insufficient permissions to run this verification.');
}

echo '<!DOCTYPE html>';
echo '<html><head><title>ENNU Admin Fix Verification</title>';
echo '<style>';
echo 'body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; }';
echo '.success { color: green; }';
echo '.error { color: red; }';
echo '.warning { color: orange; }';
echo '.section { background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 5px; }';
echo '</style>';
echo '</head><body>';

echo '<h1>ENNU Life Admin Fix Verification</h1>';

// Test 1: Plugin Instance
echo '<div class="section">';
echo '<h2>1. Plugin Instance Check</h2>';
$plugin = ENNU_Life_Enhanced_Plugin::get_instance();
if ($plugin) {
    echo '<p class="success">✅ Plugin instance found</p>';
    
    $admin = $plugin->get_admin();
    if ($admin) {
        echo '<p class="success">✅ Admin class instance found</p>';
    } else {
        echo '<p class="error">❌ Admin class instance NOT found</p>';
    }
} else {
    echo '<p class="error">❌ Plugin instance NOT found</p>';
}
echo '</div>';

// Test 2: Hook Registration
echo '<div class="section">';
echo '<h2>2. Hook Registration Check</h2>';
if ($admin) {
    $has_show_profile = has_action('show_user_profile', array($admin, 'show_user_assessment_fields'));
    $has_edit_profile = has_action('edit_user_profile', array($admin, 'show_user_assessment_fields'));
    $has_save_profile = has_action('edit_user_profile_update', array($admin, 'save_user_assessment_fields'));
    
    echo '<p class="' . ($has_show_profile ? 'success' : 'error') . '">' . 
         ($has_show_profile ? '✅' : '❌') . ' show_user_profile hook: ' . ($has_show_profile ? 'Registered' : 'NOT Registered') . '</p>';
    echo '<p class="' . ($has_edit_profile ? 'success' : 'error') . '">' . 
         ($has_edit_profile ? '✅' : '❌') . ' edit_user_profile hook: ' . ($has_edit_profile ? 'Registered' : 'NOT Registered') . '</p>';
    echo '<p class="' . ($has_save_profile ? 'success' : 'error') . '">' . 
         ($has_save_profile ? '✅' : '❌') . ' edit_user_profile_update hook: ' . ($has_save_profile ? 'Registered' : 'NOT Registered') . '</p>';
}
echo '</div>';

// Test 3: File Existence
echo '<div class="section">';
echo '<h2>3. File Existence Check</h2>';
$css_file = ENNU_LIFE_PLUGIN_PATH . 'assets/css/admin-tabs-enhanced.css';
$js_file = ENNU_LIFE_PLUGIN_PATH . 'assets/js/ennu-admin-enhanced.js';

echo '<p class="' . (file_exists($css_file) ? 'success' : 'error') . '">' . 
     (file_exists($css_file) ? '✅' : '❌') . ' CSS file exists: ' . basename($css_file) . '</p>';
echo '<p class="' . (file_exists($js_file) ? 'success' : 'error') . '">' . 
     (file_exists($js_file) ? '✅' : '❌') . ' JS file exists: ' . basename($js_file) . '</p>';
echo '</div>';

// Test 4: Global Fields Save Function
echo '<div class="section">';
echo '<h2>4. Global Fields Save Function Check</h2>';
if ($admin) {
    $reflection = new ReflectionClass($admin);
    $method = $reflection->getMethod('save_user_assessment_fields');
    $source = file_get_contents($reflection->getFileName());
    $start_line = $method->getStartLine();
    $end_line = $method->getEndLine();
    
    $method_source = '';
    for ($i = $start_line - 1; $i < $end_line; $i++) {
        $method_source .= $source[$i] . "\n";
    }
    
    $required_fields = array(
        'ennu_global_gender',
        'ennu_global_user_dob_combined',
        'ennu_global_health_goals',
        'ennu_global_height_weight'
    );
    
    foreach ($required_fields as $field) {
        $found = strpos($method_source, $field) !== false;
        echo '<p class="' . ($found ? 'success' : 'error') . '">' . 
             ($found ? '✅' : '❌') . ' Field ' . $field . ' in save function: ' . ($found ? 'Found' : 'Missing') . '</p>';
    }
}
echo '</div>';

// Test 5: Test Save Functionality
echo '<div class="section">';
echo '<h2>5. Test Save Functionality</h2>';
$current_user_id = get_current_user_id();
$test_fields = array(
    'ennu_global_gender' => 'male',
    'ennu_global_user_dob_combined' => '1990-01-01',
    'ennu_global_health_goals' => array('weight_loss', 'energy'),
    'ennu_global_height_weight' => array('ft' => '5', 'in' => '10', 'weight' => '150')
);

echo '<p>Testing save functionality for user ID: ' . $current_user_id . '</p>';

// Save test data
foreach ($test_fields as $key => $value) {
    $result = update_user_meta($current_user_id, $key, $value);
    echo '<p class="' . ($result ? 'success' : 'error') . '">' . 
         ($result ? '✅' : '❌') . ' Saved ' . $key . ': ' . print_r($value, true) . '</p>';
}

// Verify saved data
echo '<h3>Verification:</h3>';
foreach ($test_fields as $key => $expected_value) {
    $saved_value = get_user_meta($current_user_id, $key, true);
    $matches = ($saved_value == $expected_value);
    echo '<p class="' . ($matches ? 'success' : 'error') . '">' . 
         ($matches ? '✅' : '❌') . ' ' . $key . ': Expected ' . print_r($expected_value, true) . ', Got ' . print_r($saved_value, true) . '</p>';
}
echo '</div>';

// Test 6: Instructions
echo '<div class="section">';
echo '<h2>6. Next Steps</h2>';
echo '<p><strong>To test the admin tabs and fields:</strong></p>';
echo '<ol>';
echo '<li>Go to <a href="' . admin_url('profile.php') . '" target="_blank">Your Profile Page</a></li>';
echo '<li>Scroll down to find the ENNU Life Assessment Data section</li>';
echo '<li>Try clicking the tabs (Global & Health Metrics, Hair Assessment, etc.)</li>';
echo '<li>Fill in the gender, DOB, height, and weight fields</li>';
echo '<li>Click "Update Profile" to save the data</li>';
echo '<li>Refresh the page to verify the data persists</li>';
echo '</ol>';

echo '<p><strong>Expected Results:</strong></p>';
echo '<ul>';
echo '<li>✅ Tabs should switch content when clicked</li>';
echo '<li>✅ All fields should save correctly</li>';
echo '<li>✅ No JavaScript errors in browser console</li>';
echo '<li>✅ Data should persist after page refresh</li>';
echo '</ul>';
echo '</div>';

echo '</body></html>';
?> 