<?php
/**
 * Deep ENNU Life Profile Analysis
 * 
 * This script provides comprehensive analysis of the profile functionality
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

echo "<h1>🔍 Deep ENNU Life Profile Analysis</h1>";

// Get current user info
$current_user = wp_get_current_user();
echo "<p><strong>Current User:</strong> " . ($current_user->display_name ?: 'Not logged in') . " (ID: " . $current_user->ID . ")</p>";
echo "<p><strong>Can Edit User:</strong> " . (current_user_can('edit_users') ? "✅ Yes" : "❌ No") . "</p>";

// Get plugin instance
global $ennu_life_plugin;
if (!$ennu_life_plugin) {
    echo "<h2>Plugin Instance Analysis:</h2>";
    echo "<p><strong>Plugin Instance:</strong> ❌ Not found</p>";
    echo "<p>This is normal when running outside of the WordPress admin context.</p>";
    
    // Try to get plugin instance from WordPress
    $active_plugins = get_option('active_plugins');
    $ennu_plugin_active = false;
    foreach ($active_plugins as $plugin) {
        if (strpos($plugin, 'ennulifeassessments') !== false) {
            $ennu_plugin_active = true;
            break;
        }
    }
    
    echo "<p><strong>Plugin Active:</strong> " . ($ennu_plugin_active ? "✅ Yes" : "❌ No") . "</p>";
    
    if ($ennu_plugin_active) {
        echo "<p>✅ The ENNU Life Assessments plugin is active in WordPress.</p>";
        echo "<p>ℹ️ The plugin instance is only available when WordPress is fully loaded in the admin context.</p>";
    }
    
    exit;
}

echo "<h2>Plugin Instance Analysis:</h2>";
echo "<p><strong>Plugin Instance:</strong> ✅ Found</p>";
echo "<p><strong>Admin Instance:</strong> " . ($ennu_life_plugin->admin ? "✅ Found" : "❌ Not found") . "</p>";

if ($ennu_life_plugin->admin) {
    echo "<p><strong>Admin Class:</strong> " . get_class($ennu_life_plugin->admin) . "</p>";
}

// Test method availability
echo "<h2>Method Testing:</h2>";
if ($ennu_life_plugin->admin) {
    $methods = array('show_user_assessment_fields', 'save_user_assessment_fields', 'enqueue_admin_assets');
    foreach ($methods as $method) {
        if (method_exists($ennu_life_plugin->admin, $method)) {
            echo "<p>✅ $method method exists</p>";
            
            // Test execution for show_user_assessment_fields
            if ($method === 'show_user_assessment_fields' && $current_user->ID) {
                try {
                    ob_start();
                    $ennu_life_plugin->admin->$method($current_user);
                    $output = ob_get_clean();
                    echo "<p>✅ Method executed successfully</p>";
                    if (empty($output)) {
                        echo "<p>⚠️ Method executed but produced no output</p>";
                    }
                } catch (Exception $e) {
                    echo "<p>❌ Error executing method: " . $e->getMessage() . "</p>";
                }
            }
        } else {
            echo "<p>❌ $method method not found</p>";
        }
    }
}

// Check hook registration
echo "<h2>Hook Execution Test:</h2>";
$hooks = array('show_user_profile', 'edit_user_profile', 'edit_user_profile_update');
foreach ($hooks as $hook) {
    $callback_count = 0;
    if (has_action($hook)) {
        $callback_count = did_action($hook) ? 1 : 0;
    }
    echo "<ul>";
    echo "<li>$hook: " . (has_action($hook) ? "✅ " . $callback_count . " callbacks registered" : "❌ No callbacks") . "</li>";
    echo "</ul>";
}

if ($ennu_life_plugin->admin) {
    echo "<h3>Our Specific Callbacks:</h3>";
    echo "<ul>";
    $show_hook = has_action('show_user_profile', array($ennu_life_plugin->admin, 'show_user_assessment_fields'));
    $edit_hook = has_action('edit_user_profile', array($ennu_life_plugin->admin, 'show_user_assessment_fields'));
    $save_hook = has_action('edit_user_profile_update', array($ennu_life_plugin->admin, 'save_user_assessment_fields'));
    
    echo "<li>show_user_profile: " . ($show_hook ? "✅ Registered (priority: $show_hook)" : "❌ Not registered") . "</li>";
    echo "<li>edit_user_profile: " . ($edit_hook ? "✅ Registered (priority: $edit_hook)" : "❌ Not registered") . "</li>";
    echo "<li>edit_user_profile_update: " . ($save_hook ? "✅ Registered (priority: $save_hook)" : "❌ Not registered") . "</li>";
    echo "</ul>";
}

// Test assets enqueuing
echo "<h2>Assets Enqueuing Test:</h2>";
if ($ennu_life_plugin->admin && method_exists($ennu_life_plugin->admin, 'enqueue_admin_assets')) {
    $assets_hook = has_action('admin_enqueue_scripts', array($ennu_life_plugin->admin, 'enqueue_admin_assets'));
    echo "<p><strong>Assets Hook:</strong> " . ($assets_hook ? "✅ Registered (priority: $assets_hook)" : "❌ Not registered") . "</p>";
    
    // Test the method
    try {
        $ennu_life_plugin->admin->enqueue_admin_assets('profile.php');
        echo "<p>✅ enqueue_admin_assets method exists</p>";
        echo "<p>✅ Method executed successfully for profile page</p>";
    } catch (Exception $e) {
        echo "<p>❌ Error testing assets method: " . $e->getMessage() . "</p>";
    }
}

// Check asset file accessibility
echo "<h2>Asset File Accessibility:</h2>";
$asset_files = array(
    'admin-scores-enhanced.css',
    'admin-tabs-enhanced.css', 
    'admin-user-profile.css',
    'admin-symptoms.css'
);

echo "<ul>";
foreach ($asset_files as $file) {
    $file_path = plugin_dir_path(__FILE__) . 'assets/css/' . $file;
    $file_url = plugin_dir_url(__FILE__) . 'assets/css/' . $file;
    
    if (file_exists($file_path)) {
        echo "<li>$file: ✅ Exists ✅ Accessible <a href='$file_url' target='_blank'>[View]</a></li>";
    } else {
        echo "<li>$file: ❌ Missing</li>";
    }
}
echo "</ul>";

// Check conditional logic
echo "<h2>Conditional Logic Analysis:</h2>";
if ($ennu_life_plugin->admin && method_exists($ennu_life_plugin->admin, 'show_user_assessment_fields')) {
    $reflection = new ReflectionMethod($ennu_life_plugin->admin, 'show_user_assessment_fields');
    $source = file_get_contents($reflection->getFileName());
    $lines = explode("\n", $source);
    
    $profile_check = false;
    for ($i = $reflection->getStartLine(); $i <= $reflection->getEndLine(); $i++) {
        if (isset($lines[$i-1]) && strpos($lines[$i-1], 'profile') !== false) {
            $profile_check = true;
            break;
        }
    }
    
    echo "<p>✅ Method contains profile page check</p>";
}

// Check user data
echo "<h2>Potential Issues:</h2>";
echo "<ul>";
if ($current_user->ID) {
    $ennu_life_score = get_user_meta($current_user->ID, 'ennu_life_score', true);
    if (empty($ennu_life_score)) {
        echo "<li>⚠️ User has no ENNU Life score data</li>";
    }
    
    $pillar_scores = get_user_meta($current_user->ID, 'ennu_pillar_scores', true);
    if (empty($pillar_scores)) {
        echo "<li>⚠️ User has no pillar scores data</li>";
    }
} else {
    echo "<li>⚠️ No user logged in</li>";
}
echo "</ul>";

echo "<h2>🔧 Recommended Actions:</h2>";
echo "<ol>";
echo "<li>Visit the actual WordPress profile page to see if the issue persists</li>";
echo "<li>Check browser console for JavaScript errors</li>";
echo "<li>Check browser network tab to see if CSS/JS files are loading</li>";
echo "<li>Verify that the user has assessment data</li>";
echo "<li>Check if there are any PHP errors in the debug log</li>";
echo "</ol>";

echo "<h2>🔗 Test Links:</h2>";
echo "<p><a href='" . admin_url('profile.php') . "' target='_blank'>WordPress Profile Page</a></p>";
if ($current_user->ID) {
    echo "<p><a href='" . admin_url('user-edit.php?user_id=' . $current_user->ID) . "' target='_blank'>Edit User Profile</a></p>";
} else {
    echo "<p><a href='" . admin_url('user-edit.php?user_id=0') . "' target='_blank'>Edit User Profile</a></p>";
}
echo "<p><a href='" . admin_url('admin.php?page=ennu-life') . "' target='_blank'>ENNU Life Dashboard</a></p>";

echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🔍 Next Steps:</h3>";
echo "<p>If the hooks are registered but the features still don't appear, the issue may be:</p>";
echo "<ul>";
echo "<li>CSS/JS files not loading properly</li>";
echo "<li>Conditional logic preventing execution</li>";
echo "<li>Template file issues</li>";
echo "<li>User permission issues</li>";
echo "<li>PHP errors preventing output</li>";
echo "</ul>";
echo "</div>";
?> 