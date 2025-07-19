<?php
/**
 * Menu Structure Debug Script
 * Run this in your WordPress admin to diagnose menu issues
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Check if we're in admin
if (!is_admin()) {
    wp_die('This script must be run from WordPress admin.');
}

echo '<div style="font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f9f9f9; border-radius: 8px;">';
echo '<h1 style="color: #333; border-bottom: 2px solid #0073aa; padding-bottom: 10px;">🔍 ENNU Life Menu Structure Debug</h1>';

// 1. Check if plugin is active
echo '<h2 style="color: #0073aa;">1. Plugin Status</h2>';
if (is_plugin_active('ennulifeassessments/ennu-life-plugin.php')) {
    echo '<p style="color: #46b450; font-weight: bold;">✅ Plugin is active</p>';
} else {
    echo '<p style="color: #dc3232; font-weight: bold;">❌ Plugin is NOT active</p>';
    echo '<p>Please activate the ENNU Life Assessments plugin first.</p>';
    echo '</div>';
    exit;
}

// 2. Check if pages have been created
echo '<h2 style="color: #0073aa;">2. Page Creation Status</h2>';
$page_mappings = get_option('ennu_created_pages', array());
if (empty($page_mappings)) {
    echo '<p style="color: #dc3232; font-weight: bold;">❌ No pages have been created yet</p>';
    echo '<p>You need to run the page creation process first. Go to:</p>';
    echo '<p><strong>ENNU Life → Settings → Create Missing Assessment Pages</strong></p>';
} else {
    echo '<p style="color: #46b450; font-weight: bold;">✅ Pages have been created (' . count($page_mappings) . ' pages)</p>';
    
    echo '<h3>Created Pages:</h3>';
    echo '<table style="width: 100%; border-collapse: collapse; background: white; border-radius: 4px; overflow: hidden;">';
    echo '<thead><tr style="background: #0073aa; color: white;"><th style="padding: 10px; text-align: left;">Slug</th><th style="padding: 10px; text-align: left;">Page ID</th><th style="padding: 10px; text-align: left;">Status</th><th style="padding: 10px; text-align: left;">Menu Label</th></tr></thead><tbody>';
    
    foreach ($page_mappings as $slug => $page_id) {
        $page = get_post($page_id);
        $menu_label = get_post_meta($page_id, '_ennu_menu_label', true);
        $status = $page ? '✅ Published' : '❌ Missing';
        $status_color = $page ? '#46b450' : '#dc3232';
        
        echo '<tr style="border-bottom: 1px solid #ddd;">';
        echo '<td style="padding: 8px;">' . esc_html($slug) . '</td>';
        echo '<td style="padding: 8px;">' . esc_html($page_id) . '</td>';
        echo '<td style="padding: 8px; color: ' . $status_color . '; font-weight: bold;">' . $status . '</td>';
        echo '<td style="padding: 8px;">' . esc_html($menu_label ?: 'Default') . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

// 3. Check menu locations
echo '<h2 style="color: #0073aa;">3. Menu Location Status</h2>';
$menu_locations = get_nav_menu_locations();
$primary_menu_id = $menu_locations['primary'] ?? null;

if (!$primary_menu_id) {
    echo '<p style="color: #dc3232; font-weight: bold;">❌ No primary menu location assigned</p>';
    echo '<p>Your theme may not have a "primary" menu location, or it\'s not assigned.</p>';
} else {
    echo '<p style="color: #46b450; font-weight: bold;">✅ Primary menu location found (ID: ' . $primary_menu_id . ')</p>';
    
    $menu = wp_get_nav_menu_object($primary_menu_id);
    if ($menu) {
        echo '<p>Menu Name: <strong>' . esc_html($menu->name) . '</strong></p>';
    }
}

// 4. Check existing menu items
echo '<h2 style="color: #0073aa;">4. Current Menu Items</h2>';
if ($primary_menu_id) {
    $existing_items = wp_get_nav_menu_items($primary_menu_id);
    if (empty($existing_items)) {
        echo '<p style="color: #ffb900; font-weight: bold;">⚠️ No items in primary menu</p>';
    } else {
        echo '<p style="color: #46b450; font-weight: bold;">✅ Found ' . count($existing_items) . ' menu items</p>';
        
        echo '<h3>Current Menu Structure:</h3>';
        echo '<div style="background: white; padding: 15px; border-radius: 4px; border-left: 4px solid #0073aa;">';
        display_menu_tree($existing_items);
        echo '</div>';
    }
} else {
    echo '<p style="color: #dc3232;">Cannot check menu items - no primary menu location</p>';
}

// 5. Check for ENNU menu items specifically
echo '<h2 style="color: #0073aa;">5. ENNU Menu Items Check</h2>';
if ($primary_menu_id && !empty($existing_items)) {
    $ennu_items = array();
    foreach ($existing_items as $item) {
        if ($item->object == 'page' && isset($page_mappings)) {
            foreach ($page_mappings as $slug => $page_id) {
                if ($item->object_id == $page_id) {
                    $ennu_items[] = $item;
                    break;
                }
            }
        }
    }
    
    if (empty($ennu_items)) {
        echo '<p style="color: #dc3232; font-weight: bold;">❌ No ENNU pages found in menu</p>';
        echo '<p>The pages exist but are not in the menu. This means the menu update process hasn\'t run or failed.</p>';
    } else {
        echo '<p style="color: #46b450; font-weight: bold;">✅ Found ' . count($ennu_items) . ' ENNU items in menu</p>';
        
        echo '<h3>ENNU Menu Items:</h3>';
        echo '<ul style="background: white; padding: 15px; border-radius: 4px;">';
        foreach ($ennu_items as $item) {
            $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $item->menu_item_parent ? 1 : 0);
            echo '<li>' . $indent . esc_html($item->title) . ' (ID: ' . $item->ID . ')</li>';
        }
        echo '</ul>';
    }
}

// 6. Check menu update results
echo '<h2 style="color: #0073aa;">6. Menu Update History</h2>';
$menu_results = get_option('ennu_menu_update_results', array());
if (empty($menu_results)) {
    echo '<p style="color: #ffb900; font-weight: bold;">⚠️ No menu update history found</p>';
    echo '<p>This suggests the menu update process has never been run.</p>';
} else {
    echo '<p style="color: #46b450; font-weight: bold;">✅ Menu update history found</p>';
    
    if (!empty($menu_results['added_items'])) {
        echo '<h3>✅ Added Items:</h3>';
        echo '<ul style="background: white; padding: 15px; border-radius: 4px;">';
        foreach ($menu_results['added_items'] as $item) {
            echo '<li>' . esc_html($item) . '</li>';
        }
        echo '</ul>';
    }
    
    if (!empty($menu_results['skipped_items'])) {
        echo '<h3>⚠️ Skipped Items:</h3>';
        echo '<ul style="background: white; padding: 15px; border-radius: 4px;">';
        foreach ($menu_results['skipped_items'] as $item) {
            echo '<li>' . esc_html($item) . '</li>';
        }
        echo '</ul>';
    }
    
    if (!empty($menu_results['errors'])) {
        echo '<h3>❌ Errors:</h3>';
        echo '<ul style="background: white; padding: 15px; border-radius: 4px; color: #dc3232;">';
        foreach ($menu_results['errors'] as $error) {
            echo '<li>' . esc_html($error) . '</li>';
        }
        echo '</ul>';
    }
    
    if (isset($menu_results['timestamp'])) {
        echo '<p><strong>Last Updated:</strong> ' . esc_html($menu_results['timestamp']) . '</p>';
    }
}

// 7. Action buttons
echo '<h2 style="color: #0073aa;">7. Troubleshooting Actions</h2>';
echo '<div style="background: white; padding: 20px; border-radius: 4px; border-left: 4px solid #0073aa;">';

if (empty($page_mappings)) {
    echo '<p><strong>Step 1:</strong> Create the pages first</p>';
    echo '<a href="' . admin_url('admin.php?page=ennu-settings') . '" style="background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 10px 0;">Go to ENNU Settings</a>';
} else {
    echo '<p><strong>Step 1:</strong> Force menu update</p>';
    echo '<form method="post" style="margin: 10px 0;">';
    wp_nonce_field('ennu_force_menu_update', 'ennu_force_menu_nonce');
    echo '<input type="submit" name="ennu_force_menu_update" value="Force Menu Update" style="background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">';
    echo '</form>';
    
    echo '<p><strong>Step 2:</strong> Check theme menu location</p>';
    echo '<a href="' . admin_url('nav-menus.php') . '" style="background: #46b450; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 10px 0;">Go to Menu Settings</a>';
}

echo '</div>';

// Handle force menu update
if (isset($_POST['ennu_force_menu_update']) && wp_verify_nonce($_POST['ennu_force_menu_nonce'], 'ennu_force_menu_update')) {
    echo '<div style="background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 4px; margin: 20px 0;">';
    echo '<h3>🔄 Forcing Menu Update...</h3>';
    
    // Get the admin class and force menu update
    if (class_exists('ENNU_Enhanced_Admin')) {
        $admin = new ENNU_Enhanced_Admin();
        $reflection = new ReflectionClass($admin);
        $method = $reflection->getMethod('update_primary_menu_structure');
        $method->setAccessible(true);
        $method->invoke($admin, $page_mappings);
        
        echo '<p>✅ Menu update completed! Refresh this page to see the results.</p>';
    } else {
        echo '<p>❌ Could not access admin class</p>';
    }
    echo '</div>';
}

echo '</div>';

// Helper function to display menu tree
function display_menu_tree($items, $parent_id = 0) {
    foreach ($items as $item) {
        if ($item->menu_item_parent == $parent_id) {
            $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $parent_id ? 1 : 0);
            echo '<div style="margin: 5px 0;">' . $indent . '📋 ' . esc_html($item->title) . '</div>';
            display_menu_tree($items, $item->ID);
        }
    }
}
?> 