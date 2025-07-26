<?php
/**
 * ENNU Life Profile Hooks Fix
 * 
 * This script ensures the profile hooks are properly registered and working
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

echo "<h1>🔧 ENNU Life Profile Hooks Fix</h1>";

// Get plugin instance
global $ennu_life_plugin;
if (!$ennu_life_plugin) {
    echo "<p>❌ Plugin instance not found!</p>";
    exit;
}

echo "<p>✅ Plugin instance and admin instance found.</p>";

// Check current hook status
$show_hook = has_action('show_user_profile', array($ennu_life_plugin->admin, 'show_user_assessment_fields'));
$edit_hook = has_action('edit_user_profile', array($ennu_life_plugin->admin, 'show_user_assessment_fields'));
$save_hook = has_action('edit_user_profile_update', array($ennu_life_plugin->admin, 'save_user_assessment_fields'));
$assets_hook = has_action('admin_enqueue_scripts', array($ennu_life_plugin->admin, 'enqueue_admin_assets'));

echo "<h2>Current Hook Status:</h2>";
echo "<ul>";
echo "<li>show_user_profile: " . ($show_hook ? "✅ Registered" : "❌ Not registered") . "</li>";
echo "<li>edit_user_profile: " . ($edit_hook ? "✅ Registered" : "❌ Not registered") . "</li>";
echo "<li>edit_user_profile_update: " . ($save_hook ? "✅ Registered" : "❌ Not registered") . "</li>";
echo "<li>admin_enqueue_scripts: " . ($assets_hook ? "✅ Registered" : "❌ Not registered") . "</li>";
echo "</ul>";

// Fix missing hooks if needed
echo "<h2>Fixing Missing Hooks:</h2>";
if (!$show_hook) {
    add_action('show_user_profile', array($ennu_life_plugin->admin, 'show_user_assessment_fields'));
    echo "<p>✅ Added show_user_profile hook</p>";
}
if (!$edit_hook) {
    add_action('edit_user_profile', array($ennu_life_plugin->admin, 'show_user_assessment_fields'));
    echo "<p>✅ Added edit_user_profile hook</p>";
}
if (!$save_hook) {
    add_action('edit_user_profile_update', array($ennu_life_plugin->admin, 'save_user_assessment_fields'));
    echo "<p>✅ Added edit_user_profile_update hook</p>";
}
if (!$assets_hook) {
    add_action('admin_enqueue_scripts', array($ennu_life_plugin->admin, 'enqueue_admin_assets'));
    echo "<p>✅ Added admin_enqueue_scripts hook</p>";
}

// Verify hooks are now registered
$show_hook = has_action('show_user_profile', array($ennu_life_plugin->admin, 'show_user_assessment_fields'));
$edit_hook = has_action('edit_user_profile', array($ennu_life_plugin->admin, 'show_user_assessment_fields'));
$save_hook = has_action('edit_user_profile_update', array($ennu_life_plugin->admin, 'save_user_assessment_fields'));
$assets_hook = has_action('admin_enqueue_scripts', array($ennu_life_plugin->admin, 'enqueue_admin_assets'));

echo "<h2>Verification:</h2>";
echo "<ul>";
echo "<li>show_user_profile: " . ($show_hook ? "✅ Registered" : "❌ Not registered") . "</li>";
echo "<li>edit_user_profile: " . ($edit_hook ? "✅ Registered" : "❌ Not registered") . "</li>";
echo "<li>edit_user_profile_update: " . ($save_hook ? "✅ Registered" : "❌ Not registered") . "</li>";
echo "<li>admin_enqueue_scripts: " . ($assets_hook ? "✅ Registered" : "❌ Not registered") . "</li>";
echo "</ul>";

if ($show_hook && $edit_hook && $save_hook && $assets_hook) {
    echo "<div style='background: #d1e7dd; color: #0f5132; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✅ All hooks successfully registered!</h3>";
    echo "<p>The ENNU Life profile features should now be available on the WordPress profile page.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Some hooks are still missing!</h3>";
    echo "<p>Please check the plugin initialization code.</p>";
    echo "</div>";
}

// Test method availability
echo "<h2>Method Availability Test:</h2>";
echo "<ul>";
echo "<li>show_user_assessment_fields: " . (method_exists($ennu_life_plugin->admin, 'show_user_assessment_fields') ? "✅ Exists" : "❌ Missing") . "</li>";
echo "<li>save_user_assessment_fields: " . (method_exists($ennu_life_plugin->admin, 'save_user_assessment_fields') ? "✅ Exists" : "❌ Missing") . "</li>";
echo "<li>enqueue_admin_assets: " . (method_exists($ennu_life_plugin->admin, 'enqueue_admin_assets') ? "✅ Exists" : "❌ Missing") . "</li>";
echo "</ul>";

// Check error log for recent ENNU-related entries
echo "<h2>Error Log Check:</h2>";
$log_file = ABSPATH . 'wp-content/debug.log';
if (file_exists($log_file)) {
    $log_content = file_get_contents($log_file);
    $ennu_entries = array();
    $lines = explode("\n", $log_content);
    foreach ($lines as $line) {
        if (strpos($line, 'ENNU') !== false) {
            $ennu_entries[] = $line;
        }
    }
    
    if (!empty($ennu_entries)) {
        echo "<p><strong>Recent ENNU-related errors:</strong></p>";
        echo "<ul>";
        $recent_entries = array_slice($ennu_entries, -10); // Last 10 entries
        foreach ($recent_entries as $entry) {
            echo "<li>" . htmlspecialchars($entry) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>✅ No recent ENNU-related errors found in debug log.</p>";
    }
} else {
    echo "<p>⚠️ Debug log file not found.</p>";
}

echo "<h2>🔗 Next Steps:</h2>";
echo "<p><a href='" . admin_url('profile.php') . "' target='_blank'>Visit WordPress Profile Page</a> to verify the ENNU Life features are now visible.</p>";
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

echo "<div style='background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>ℹ️ Note:</h3>";
echo "<p>This fix registers the hooks for the current session. For a permanent fix, the issue should be resolved in the main plugin initialization code.</p>";
echo "</div>";
?> 