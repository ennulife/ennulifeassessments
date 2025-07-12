<?php
/**
 * Test script to check if assessment data is being saved
 * Place this in your WordPress root directory and access via browser
 */

// Load WordPress
require_once('wp-config.php');
require_once('wp-load.php');

// Check if user is logged in as admin
if (!current_user_can('manage_options')) {
    die('Access denied. Please log in as admin.');
}

echo '<h1>ENNU Assessment Data Test</h1>';

// Get all users
$users = get_users();

echo '<h2>Users with Assessment Data:</h2>';

foreach ($users as $user) {
    $user_id = $user->ID;
    $user_meta = get_user_meta($user_id);
    
    // Filter for ENNU-related meta
    $ennu_meta = array();
    foreach ($user_meta as $key => $value) {
        if (strpos($key, 'ennu_') === 0) {
            $ennu_meta[$key] = $value[0];
        }
    }
    
    if (!empty($ennu_meta)) {
        echo '<div style="border: 1px solid #ccc; padding: 15px; margin: 10px 0;">';
        echo '<h3>User: ' . $user->display_name . ' (ID: ' . $user_id . ')</h3>';
        echo '<h4>Email: ' . $user->user_email . '</h4>';
        
        foreach ($ennu_meta as $meta_key => $meta_value) {
            echo '<p><strong>' . $meta_key . ':</strong> ' . $meta_value . '</p>';
        }
        echo '</div>';
    }
}

echo '<h2>Recent Assessment Submissions:</h2>';

// Check for recent assessment submissions
global $wpdb;
$recent_meta = $wpdb->get_results("
    SELECT um.user_id, um.meta_key, um.meta_value, u.user_email, u.display_name
    FROM {$wpdb->usermeta} um
    JOIN {$wpdb->users} u ON um.user_id = u.ID
    WHERE um.meta_key LIKE 'ennu_%'
    ORDER BY um.umeta_id DESC
    LIMIT 50
");

if ($recent_meta) {
    echo '<table border="1" cellpadding="5" cellspacing="0">';
    echo '<tr><th>User</th><th>Meta Key</th><th>Meta Value</th></tr>';
    
    foreach ($recent_meta as $meta) {
        echo '<tr>';
        echo '<td>' . $meta->display_name . ' (' . $meta->user_email . ')</td>';
        echo '<td>' . $meta->meta_key . '</td>';
        echo '<td>' . substr($meta->meta_value, 0, 100) . (strlen($meta->meta_value) > 100 ? '...' : '') . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
} else {
    echo '<p><strong>No assessment data found in database.</strong></p>';
}

echo '<h2>Test Instructions:</h2>';
echo '<ol>';
echo '<li>Submit an assessment form on your website</li>';
echo '<li>Refresh this page to see if new data appears</li>';
echo '<li>Check the "Users with Assessment Data" section above</li>';
echo '</ol>';
?> 