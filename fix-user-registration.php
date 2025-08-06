<?php
/**
 * Fix WordPress User Registration Setting
 * 
 * This script enables user registration in WordPress
 */

// Load WordPress
require_once dirname(__FILE__) . '/../../../wp-load.php';

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Access denied. Admin rights required.');
}

echo "<h1>🔧 Fixing WordPress User Registration</h1>\n";

// Enable user registration
$updated = update_option('users_can_register', 1);
if ($updated) {
    echo "✅ User registration enabled successfully\n";
} else {
    echo "ℹ️ User registration was already enabled\n";
}

// Set default user role to subscriber
$role_updated = update_option('default_role', 'subscriber');
if ($role_updated) {
    echo "✅ Default user role set to subscriber\n";
} else {
    echo "ℹ️ Default user role was already set to subscriber\n";
}

// Check current settings
$users_can_register = get_option('users_can_register');
$default_role = get_option('default_role');

echo "\n<h2>Current Settings:</h2>\n";
echo "Users can register: " . ($users_can_register ? 'YES' : 'NO') . "\n";
echo "Default role: " . $default_role . "\n";

echo "\n✅ WordPress user registration is now properly configured!\n";
?>