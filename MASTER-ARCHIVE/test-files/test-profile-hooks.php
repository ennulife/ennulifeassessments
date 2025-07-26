<?php
/**
 * Test Profile Hooks Verification
 * 
 * This script tests if the ENNU Life profile hooks are properly registered
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
}

// Load WordPress
require_once(ABSPATH . 'wp-load.php');

// Check if user is logged in and has admin capabilities
if (!current_user_can('manage_options')) {
    wp_die('Access denied. Admin privileges required.');
}

echo "<h1>🔧 ENNU Life Profile Hooks Test</h1>";

// Check if plugin is active
if (!is_plugin_active('ennulifeassessments/ennu-life-plugin.php')) {
    echo "<p>❌ ENNU Life Assessments plugin is not active!</p>";
    exit;
}

echo "<p>✅ Plugin is active.</p>";

// Check if admin class exists
if (!class_exists('ENNU_Enhanced_Admin')) {
    echo "<p>❌ ENNU_Enhanced_Admin class not found!</p>";
    exit;
}

echo "<p>✅ ENNU_Enhanced_Admin class found.</p>";

// Get plugin instance
global $ennu_life_plugin;
if (!$ennu_life_plugin) {
    echo "<p>❌ Plugin instance not found!</p>";
    exit;
}

echo "<p>✅ Plugin instance found.</p>";

// Check if admin instance exists
if (!$ennu_life_plugin->admin) {
    echo "<p>❌ Admin instance not found!</p>";
    exit;
}

echo "<p>✅ Admin instance found.</p>";

// Check if methods exist
if (!method_exists($ennu_life_plugin->admin, 'show_user_assessment_fields')) {
    echo "<p>❌ show_user_assessment_fields method not found!</p>";
    exit;
}

if (!method_exists($ennu_life_plugin->admin, 'save_user_assessment_fields')) {
    echo "<p>❌ save_user_assessment_fields method not found!</p>";
    exit;
}

echo "<p>✅ Both profile methods exist.</p>";

// Check if hooks are registered
$show_hook = has_action('show_user_profile', array($ennu_life_plugin->admin, 'show_user_assessment_fields'));
$edit_hook = has_action('edit_user_profile', array($ennu_life_plugin->admin, 'show_user_assessment_fields'));
$save_hook = has_action('edit_user_profile_update', array($ennu_life_plugin->admin, 'save_user_assessment_fields'));

echo "<h2>Hook Registration Status:</h2>";
echo "<ul>";
echo "<li>show_user_profile: " . ($show_hook ? "✅ Registered (priority: $show_hook)" : "❌ Not registered") . "</li>";
echo "<li>edit_user_profile: " . ($edit_hook ? "✅ Registered (priority: $edit_hook)" : "❌ Not registered") . "</li>";
echo "<li>edit_user_profile_update: " . ($save_hook ? "✅ Registered (priority: $save_hook)" : "❌ Not registered") . "</li>";
echo "</ul>";

// Test method execution
echo "<h2>Method Execution Test:</h2>";
try {
    $user = wp_get_current_user();
    if ($user->ID) {
        echo "<p>✅ Current user found (ID: {$user->ID})</p>";
        
        // Test the show method
        ob_start();
        $ennu_life_plugin->admin->show_user_assessment_fields($user);
        $output = ob_get_clean();
        
        if (!empty($output)) {
            echo "<p>✅ show_user_assessment_fields method executed and produced output</p>";
            echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;'>";
            echo "<strong>Output Preview:</strong><br>";
            echo substr($output, 0, 500) . (strlen($output) > 500 ? "..." : "");
            echo "</div>";
        } else {
            echo "<p>⚠️ show_user_assessment_fields method executed but produced no output</p>";
        }
    } else {
        echo "<p>⚠️ No user logged in - cannot test method execution</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error testing method execution: " . $e->getMessage() . "</p>";
}

echo "<h2>🔗 Next Steps:</h2>";
echo "<p><a href='" . admin_url('profile.php') . "' target='_blank'>Visit WordPress Profile Page</a> to see the ENNU Life features.</p>";
echo "<p><a href='" . admin_url('admin.php?page=ennu-life') . "' target='_blank'>Visit ENNU Life Dashboard</a> to check admin functionality.</p>";

echo "<h2>📋 Expected Profile Features:</h2>";
echo "<ul>";
echo "<li>Health Summary Component with ENNU Life Score</li>";
echo "<li>Pillar Scores (Mind, Body, Lifestyle, Aesthetics)</li>";
echo "<li>Global & Health Metrics Tab</li>";
echo "<li>Centralized Symptoms Tab</li>";
echo "<li>Biomarkers Tab</li>";
echo "<li>Individual Assessment Tabs</li>";
echo "<li>Tab Navigation System</li>";
echo "<li>Data Saving Functionality</li>";
echo "</ul>";
?> 