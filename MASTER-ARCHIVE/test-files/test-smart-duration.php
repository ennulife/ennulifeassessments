<?php
/**
 * Test Smart Duration System
 * 
 * Tests the new smart duration system with:
 * 1. Assessment-based expiration
 * 2. Severity-based duration
 */

// Load WordPress
require_once('../../../wp-load.php');

// Ensure we're logged in as admin
if (!current_user_can('manage_options')) {
    wp_die('Access denied');
}

echo "<h1>🧠 Smart Duration System Test</h1>";

// Test user ID
$user_id = 1;

echo "<h2>📊 Current System Status</h2>";

// Test 1: Check current symptoms
echo "<h3>1. Current Symptoms Check</h3>";
$current_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
echo "<p><strong>Total Symptoms:</strong> " . count($current_symptoms['symptoms'] ?? []) . "</p>";

if (!empty($current_symptoms['symptoms'])) {
    echo "<h4>Active Symptoms with Duration Info:</h4>";
    foreach ($current_symptoms['symptoms'] as $symptom_key => $symptom_data) {
        $duration_info = ENNU_Centralized_Symptoms_Manager::get_symptom_duration_info($symptom_data);
        
        echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
        echo "<strong>Symptom:</strong> " . esc_html($symptom_data['name'] ?? $symptom_key) . "<br>";
        echo "<strong>Severity:</strong> " . esc_html($symptom_data['severity'] ?? 'unknown') . "<br>";
        echo "<strong>Status:</strong> " . esc_html($duration_info['status']) . "<br>";
        echo "<strong>Days Remaining:</strong> " . esc_html($duration_info['days_remaining']) . "<br>";
        echo "<strong>Duration (days):</strong> " . esc_html($duration_info['severity_duration']) . "<br>";
        echo "<strong>Expires:</strong> " . esc_html($duration_info['expiration_date']) . "<br>";
        echo "</div>";
    }
} else {
    echo "<p>No current symptoms found.</p>";
}

// Test 2: Test severity-based duration
echo "<h3>2. Severity-Based Duration Test</h3>";
$test_severities = ['mild', 'moderate', 'severe', 'critical', 'unknown'];
foreach ($test_severities as $severity) {
    $duration = ENNU_Centralized_Symptoms_Manager::get_duration_for_severity($severity);
    echo "<p><strong>$severity:</strong> $duration days</p>";
}

// Test 3: Test symptom expiration
echo "<h3>3. Symptom Expiration Test</h3>";
$test_symptom = array(
    'name' => 'Test Symptom',
    'severity' => 'moderate',
    'last_reported' => date('Y-m-d H:i:s', strtotime('-15 days')),
    'frequency' => 'daily'
);

$duration_info = ENNU_Centralized_Symptoms_Manager::get_symptom_duration_info($test_symptom);
echo "<p><strong>Test Symptom (15 days old):</strong></p>";
echo "<ul>";
echo "<li>Status: " . esc_html($duration_info['status']) . "</li>";
echo "<li>Days Remaining: " . esc_html($duration_info['days_remaining']) . "</li>";
echo "<li>Duration: " . esc_html($duration_info['severity_duration']) . " days</li>";
echo "</ul>";

// Test 4: Force update symptoms
echo "<h3>4. Force Update Symptoms</h3>";
$result = ENNU_Centralized_Symptoms_Manager::force_update_symptoms($user_id);
echo "<p><strong>Update Result:</strong> " . ($result ? 'Success' : 'Failed') . "</p>";

// Test 5: Check symptom history
echo "<h3>5. Symptom History</h3>";
$history = ENNU_Centralized_Symptoms_Manager::get_symptom_history($user_id, 5);
echo "<p><strong>History Entries:</strong> " . count($history) . "</p>";

if (!empty($history)) {
    echo "<h4>Recent History:</h4>";
    foreach ($history as $entry) {
        echo "<div style='border: 1px solid #eee; margin: 5px; padding: 5px;'>";
        echo "<strong>Date:</strong> " . esc_html($entry['date']) . "<br>";
        echo "<strong>Total Symptoms:</strong> " . esc_html($entry['total_count'] ?? 'N/A') . "<br>";
        echo "</div>";
    }
}

echo "<h2>✅ Test Complete</h2>";
echo "<p>The Smart Duration System is now active with:</p>";
echo "<ul>";
echo "<li><strong>Phase 1:</strong> Assessment-based expiration</li>";
echo "<li><strong>Phase 2:</strong> Severity-based duration (Mild: 14d, Moderate: 30d, Severe: 60d, Critical: 90d)</li>";
echo "</ul>";

echo "<p><a href='http://localhost:8888/?page_id=3' target='_blank'>View User Dashboard</a></p>";
?> 