<?php
// Test user meta functionality
echo "Testing user meta functionality...<br>";

// Check if WordPress is loaded
if (!defined('ABSPATH')) {
    echo "WordPress not loaded<br>";
    exit;
}

echo "WordPress loaded successfully<br>";

// Check if user is admin
if (!current_user_can('manage_options')) {
    echo "Not admin user<br>";
    exit;
}

echo "Admin user confirmed<br>";

$user_id = 1;
echo "Testing with user ID: {$user_id}<br>";

// Test basic user meta functionality
$test_key = 'ennu_test_meta';
$test_value = 'test_value_' . time();

echo "Testing update_user_meta...<br>";
$success = update_user_meta($user_id, $test_key, $test_value);
echo "Update result: " . ($success ? 'SUCCESS' : 'FAILED') . "<br>";

echo "Testing get_user_meta...<br>";
$retrieved_value = get_user_meta($user_id, $test_key, true);
echo "Retrieved value: " . $retrieved_value . "<br>";

if ($retrieved_value === $test_value) {
    echo "✅ User meta test PASSED<br>";
} else {
    echo "❌ User meta test FAILED<br>";
}

// Clean up
delete_user_meta($user_id, $test_key);
echo "Test complete!";
?> 