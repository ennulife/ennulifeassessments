<?php
/**
 * ENNU Life Admin Tabs and Fields Fix Test
 * 
 * This script tests both the global fields saving functionality and tab navigation
 * to ensure everything is working correctly.
 * 
 * @package ENNU_Life
 * @version 57.2.5
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Ensure we're in admin context
if (!is_admin()) {
    wp_die('This script must be run from the WordPress admin area.');
}

// Check permissions
if (!current_user_can('manage_options')) {
    wp_die('Insufficient permissions to run this test.');
}

echo '<div style="font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #fff; border: 1px solid #ccc; border-radius: 8px;">';
echo '<h1 style="color: #2271b1; border-bottom: 2px solid #2271b1; padding-bottom: 10px;">ENNU Life Admin Tabs and Fields Fix Test</h1>';

// Test 1: Check if admin class exists and hooks are registered
echo '<h2 style="color: #333; margin-top: 30px;">Test 1: Admin Class and Hook Registration</h2>';
echo '<div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 10px 0;">';

$plugin = ENNU_Life_Enhanced_Plugin::get_instance();
if ($plugin) {
    echo '<p style="color: green;">✅ Plugin instance found</p>';
    
    $admin = $plugin->get_admin();
    if ($admin) {
        echo '<p style="color: green;">✅ Admin class instance found</p>';
        
        // Check if hooks are registered
        $has_show_profile = has_action('show_user_profile', array($admin, 'show_user_assessment_fields'));
        $has_edit_profile = has_action('edit_user_profile', array($admin, 'show_user_assessment_fields'));
        $has_save_profile = has_action('edit_user_profile_update', array($admin, 'save_user_assessment_fields'));
        
        echo '<p style="color: ' . ($has_show_profile ? 'green' : 'red') . ';">' . 
             ($has_show_profile ? '✅' : '❌') . ' show_user_profile hook: ' . ($has_show_profile ? 'Registered' : 'NOT Registered') . '</p>';
        echo '<p style="color: ' . ($has_edit_profile ? 'green' : 'red') . ';">' . 
             ($has_edit_profile ? '✅' : '❌') . ' edit_user_profile hook: ' . ($has_edit_profile ? 'Registered' : 'NOT Registered') . '</p>';
        echo '<p style="color: ' . ($has_save_profile ? 'green' : 'red') . ';">' . 
             ($has_save_profile ? '✅' : '❌') . ' edit_user_profile_update hook: ' . ($has_save_profile ? 'Registered' : 'NOT Registered') . '</p>';
        
    } else {
        echo '<p style="color: red;">❌ Admin class instance NOT found</p>';
    }
} else {
    echo '<p style="color: red;">❌ Plugin instance NOT found</p>';
}
echo '</div>';

// Test 2: Check if assets are being enqueued correctly
echo '<h2 style="color: #333; margin-top: 30px;">Test 2: Asset Enqueuing</h2>';
echo '<div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 10px 0;">';

$css_file = ENNU_LIFE_PLUGIN_PATH . 'assets/css/admin-tabs-enhanced.css';
$js_file = ENNU_LIFE_PLUGIN_PATH . 'assets/js/ennu-admin-enhanced.js';

echo '<p style="color: ' . (file_exists($css_file) ? 'green' : 'red') . ';">' . 
     (file_exists($css_file) ? '✅' : '❌') . ' CSS file exists: ' . basename($css_file) . '</p>';
echo '<p style="color: ' . (file_exists($js_file) ? 'green' : 'red') . ';">' . 
     (file_exists($js_file) ? '✅' : '❌') . ' JS file exists: ' . basename($js_file) . '</p>';

// Check if assets are enqueued
$wp_styles = wp_styles();
$wp_scripts = wp_scripts();

$css_enqueued = isset($wp_styles->registered['ennu-admin-tabs-enhanced']);
$js_enqueued = isset($wp_scripts->registered['ennu-admin-enhanced']);

echo '<p style="color: ' . ($css_enqueued ? 'green' : 'orange') . ';">' . 
     ($css_enqueued ? '✅' : '⚠️') . ' CSS enqueued: ' . ($css_enqueued ? 'Yes' : 'No (may be conditional)') . '</p>';
echo '<p style="color: ' . ($js_enqueued ? 'green' : 'orange') . ';">' . 
     ($js_enqueued ? '✅' : '⚠️') . ' JS enqueued: ' . ($js_enqueued ? 'Yes' : 'No (may be conditional)') . '</p>';

echo '</div>';

// Test 3: Test global fields saving functionality
echo '<h2 style="color: #333; margin-top: 30px;">Test 3: Global Fields Save Function</h2>';
echo '<div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 10px 0;">';

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
    echo '<p style="color: ' . ($result ? 'green' : 'red') . ';">' . 
         ($result ? '✅' : '❌') . ' Saved ' . $key . ': ' . print_r($value, true) . '</p>';
}

// Verify saved data
echo '<h3>Verification:</h3>';
foreach ($test_fields as $key => $expected_value) {
    $saved_value = get_user_meta($current_user_id, $key, true);
    $matches = ($saved_value == $expected_value);
    echo '<p style="color: ' . ($matches ? 'green' : 'red') . ';">' . 
         ($matches ? '✅' : '❌') . ' ' . $key . ': Expected ' . print_r($expected_value, true) . ', Got ' . print_r($saved_value, true) . '</p>';
}

echo '</div>';

// Test 4: Check if the save function includes all required fields
echo '<h2 style="color: #333; margin-top: 30px;">Test 4: Save Function Field Coverage</h2>';
echo '<div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 10px 0;">';

if ($admin) {
    // Use reflection to check the save function
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
        echo '<p style="color: ' . ($found ? 'green' : 'red') . ';">' . 
             ($found ? '✅' : '❌') . ' Field ' . $field . ' in save function: ' . ($found ? 'Found' : 'Missing') . '</p>';
    }
    
    echo '<details style="margin-top: 10px;">';
    echo '<summary style="cursor: pointer; color: #2271b1;">View Save Function Source</summary>';
    echo '<pre style="background: #fff; padding: 10px; border: 1px solid #ddd; overflow-x: auto; font-size: 12px;">' . htmlspecialchars($method_source) . '</pre>';
    echo '</details>';
}

echo '</div>';

// Test 5: Generate test form to verify tab functionality
echo '<h2 style="color: #333; margin-top: 30px;">Test 5: Tab Functionality Test</h2>';
echo '<div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 10px 0;">';

echo '<p>Below is a test form that simulates the admin user profile page structure:</p>';

// Enqueue the required assets
wp_enqueue_style('ennu-admin-tabs-enhanced', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-tabs-enhanced.css', array(), ENNU_LIFE_VERSION);
wp_enqueue_script('ennu-admin-enhanced', ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-admin-enhanced.js', array('jquery'), ENNU_LIFE_VERSION, true);

wp_localize_script('ennu-admin-enhanced', 'ennuAdmin', array( 
    'nonce' => wp_create_nonce('ennu_admin_nonce'),
    'ajax_url' => admin_url('admin-ajax.php'),
    'confirm_msg' => 'Are you sure?',
    'plugin_url' => ENNU_LIFE_PLUGIN_URL,
    'debug' => true
));

echo '<form method="post" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">';
wp_nonce_field('ennu_user_profile_update_' . $current_user_id, 'ennu_assessment_nonce');

echo '<div class="ennu-admin-tabs">';
echo '<nav class="ennu-admin-tab-nav"><ul>';
echo '<li><a href="#tab-global-metrics" class="ennu-admin-tab-active">Global & Health Metrics</a></li>';
echo '<li><a href="#tab-hair">Hair Assessment</a></li>';
echo '<li><a href="#tab-health">Health Assessment</a></li>';
echo '</ul></nav>';

echo '<div id="tab-global-metrics" class="ennu-admin-tab-content ennu-admin-tab-active">';
echo '<table class="form-table">';
echo '<tr><th>Gender</th><td>';
echo '<select name="ennu_global_gender">';
echo '<option value="">Select Gender</option>';
echo '<option value="male"' . selected(get_user_meta($current_user_id, 'ennu_global_gender', true), 'male', false) . '>Male</option>';
echo '<option value="female"' . selected(get_user_meta($current_user_id, 'ennu_global_gender', true), 'female', false) . '>Female</option>';
echo '<option value="other"' . selected(get_user_meta($current_user_id, 'ennu_global_gender', true), 'other', false) . '>Other</option>';
echo '</select>';
echo '</td></tr>';

echo '<tr><th>Date of Birth</th><td>';
echo '<input type="date" name="ennu_global_user_dob_combined" value="' . esc_attr(get_user_meta($current_user_id, 'ennu_global_user_dob_combined', true)) . '" />';
echo '</td></tr>';

echo '<tr><th>Height & Weight</th><td>';
$height_weight = get_user_meta($current_user_id, 'ennu_global_height_weight', true);
$height_ft = isset($height_weight['ft']) ? $height_weight['ft'] : '';
$height_in = isset($height_weight['in']) ? $height_weight['in'] : '';
$weight = isset($height_weight['weight']) ? $height_weight['weight'] : '';
echo 'Height: <input type="number" name="ennu_global_height_weight[ft]" value="' . esc_attr($height_ft) . '" min="0" max="8" style="width:50px;" /> ft ';
echo '<input type="number" name="ennu_global_height_weight[in]" value="' . esc_attr($height_in) . '" min="0" max="11" style="width:50px;" /> in ';
echo 'Weight: <input type="number" name="ennu_global_height_weight[weight]" value="' . esc_attr($weight) . '" min="0" max="999" style="width:70px;" /> lbs';
echo '</td></tr>';
echo '</table>';
echo '</div>';

echo '<div id="tab-hair" class="ennu-admin-tab-content">';
echo '<h3>Hair Assessment Data</h3>';
echo '<p>This tab should be clickable and show this content.</p>';
echo '</div>';

echo '<div id="tab-health" class="ennu-admin-tab-content">';
echo '<h3>Health Assessment Data</h3>';
echo '<p>This tab should also be clickable and show this content.</p>';
echo '</div>';

echo '</div>';

echo '<p style="margin-top: 20px;"><input type="submit" value="Test Save" class="button button-primary" /></p>';
echo '</form>';

echo '<div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-left: 4px solid #2271b1;">';
echo '<h4>Instructions:</h4>';
echo '<ol>';
echo '<li>Try clicking on the "Hair Assessment" and "Health Assessment" tabs - they should switch content</li>';
echo '<li>Fill in the gender, DOB, height, and weight fields</li>';
echo '<li>Click "Test Save" to verify the data is saved</li>';
echo '<li>Check the browser console for any JavaScript errors</li>';
echo '</ol>';
echo '</div>';

echo '</div>';

// Test 6: JavaScript functionality check
echo '<h2 style="color: #333; margin-top: 30px;">Test 6: JavaScript Functionality</h2>';
echo '<div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 10px 0;">';

echo '<script type="text/javascript">
console.log("ENNU Test: Checking JavaScript functionality...");

// Check if jQuery is available
if (typeof jQuery !== "undefined") {
    console.log("ENNU Test: ✅ jQuery is available");
} else {
    console.log("ENNU Test: ❌ jQuery is NOT available");
}

// Check if our initialization function exists
if (typeof window.initializeEnnuAdmin === "function") {
    console.log("ENNU Test: ✅ initializeEnnuAdmin function exists");
} else {
    console.log("ENNU Test: ❌ initializeEnnuAdmin function does NOT exist");
}

// Check if ennuAdmin object exists
if (typeof ennuAdmin !== "undefined") {
    console.log("ENNU Test: ✅ ennuAdmin object exists");
    console.log("ENNU Test: ennuAdmin.nonce:", ennuAdmin.nonce);
    console.log("ENNU Test: ennuAdmin.ajax_url:", ennuAdmin.ajax_url);
} else {
    console.log("ENNU Test: ❌ ennuAdmin object does NOT exist");
}

// Test tab functionality
document.addEventListener("DOMContentLoaded", function() {
    console.log("ENNU Test: DOM loaded, checking for tab elements...");
    
    const tabLinks = document.querySelectorAll(".ennu-admin-tab-nav a");
    const tabContents = document.querySelectorAll(".ennu-admin-tab-content");
    
    console.log("ENNU Test: Found", tabLinks.length, "tab links");
    console.log("ENNU Test: Found", tabContents.length, "tab contents");
    
    if (tabLinks.length > 0 && tabContents.length > 0) {
        console.log("ENNU Test: ✅ Tab structure found");
        
        // Test tab switching
        tabLinks.forEach(function(link, index) {
            console.log("ENNU Test: Tab", index + 1, "href:", link.getAttribute("href"));
        });
    } else {
        console.log("ENNU Test: ❌ Tab structure NOT found");
    }
});

// Force initialization
setTimeout(function() {
    console.log("ENNU Test: Attempting to force tab initialization...");
    if (typeof window.initializeEnnuAdmin === "function") {
        window.initializeEnnuAdmin();
        console.log("ENNU Test: ✅ Tab initialization called");
    } else {
        console.log("ENNU Test: ❌ Cannot call tab initialization");
    }
}, 1000);
</script>';

echo '<p>Check the browser console (F12 → Console tab) for detailed JavaScript debugging information.</p>';
echo '</div>';

echo '<h2 style="color: #333; margin-top: 30px;">Summary</h2>';
echo '<div style="background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #2271b1;">';
echo '<p><strong>Expected Results:</strong></p>';
echo '<ul>';
echo '<li>✅ All hooks should be registered</li>';
echo '<li>✅ CSS and JS files should exist</li>';
echo '<li>✅ Global fields should save correctly</li>';
echo '<li>✅ Tabs should be clickable and switch content</li>';
echo '<li>✅ No JavaScript errors in console</li>';
echo '</ul>';
echo '<p><strong>If tabs are not working:</strong></p>';
echo '<ul>';
echo '<li>Check browser console for JavaScript errors</li>';
echo '<li>Verify CSS is loading (check Network tab in dev tools)</li>';
echo '<li>Ensure jQuery is available</li>';
echo '<li>Check if there are theme conflicts</li>';
echo '</ul>';
echo '</div>';

echo '</div>';
?> 