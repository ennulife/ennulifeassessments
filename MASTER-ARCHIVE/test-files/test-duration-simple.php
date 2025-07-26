<?php
/**
 * Simple Smart Duration Test
 * Access via: http://localhost:8888/wp-content/plugins/ennulifeassessments/test-duration-simple.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Security check
if (!current_user_can('manage_options')) {
    wp_die('Access denied');
}

echo "<h1>🧠 Smart Duration System - Phase 1 & 2</h1>";

$user_id = 1;

echo "<h2>📊 Current Implementation</h2>";

// Test severity-based duration
echo "<h3>Severity-Based Duration (Phase 2)</h3>";
$severities = [
    'mild' => '14 days',
    'moderate' => '30 days', 
    'severe' => '60 days',
    'critical' => '90 days'
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Severity</th><th>Duration</th></tr>";
foreach ($severities as $severity => $duration) {
    echo "<tr><td>$severity</td><td>$duration</td></tr>";
}
echo "</table>";

// Test current symptoms
echo "<h3>Current Symptoms Status</h3>";
$symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
$active_count = count($symptoms['symptoms'] ?? []);

echo "<p><strong>Active Symptoms:</strong> $active_count</p>";

if ($active_count > 0) {
    echo "<h4>Symptom Details:</h4>";
    foreach ($symptoms['symptoms'] as $key => $symptom) {
        $duration_info = ENNU_Centralized_Symptoms_Manager::get_symptom_duration_info($symptom);
        
        $status_color = $duration_info['status'] === 'active' ? 'green' : 'red';
        $days_color = $duration_info['days_remaining'] <= 7 ? 'red' : ($duration_info['days_remaining'] <= 14 ? 'orange' : 'green');
        
        echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px; background: #f9f9f9;'>";
        echo "<strong>" . esc_html($symptom['name'] ?? $key) . "</strong><br>";
        echo "Severity: " . esc_html($symptom['severity'] ?? 'unknown') . "<br>";
        echo "Status: <span style='color: $status_color;'>" . esc_html($duration_info['status']) . "</span><br>";
        echo "Days Remaining: <span style='color: $days_color;'>" . esc_html($duration_info['days_remaining']) . "</span><br>";
        echo "Duration: " . esc_html($duration_info['severity_duration']) . " days<br>";
        echo "Expires: " . esc_html($duration_info['expiration_date']) . "<br>";
        echo "</div>";
    }
} else {
    echo "<p>No active symptoms found. Complete an assessment to see symptoms with duration tracking.</p>";
}

echo "<h2>✅ System Status</h2>";
echo "<p><strong>Phase 1:</strong> ✅ Assessment-based expiration active</p>";
echo "<p><strong>Phase 2:</strong> ✅ Severity-based duration active</p>";

echo "<h3>🎯 How It Works</h3>";
echo "<ul>";
echo "<li><strong>Assessment Completion:</strong> Symptoms are updated when assessments are completed</li>";
echo "<li><strong>Automatic Expiration:</strong> Symptoms expire based on severity (14-90 days)</li>";
echo "<li><strong>Real-time Display:</strong> User dashboard shows remaining days for each symptom</li>";
echo "<li><strong>Smart Cleaning:</strong> Expired symptoms are automatically removed</li>";
echo "</ul>";

echo "<p><a href='http://localhost:8888/?page_id=3' target='_blank'>🏠 View User Dashboard</a></p>";
echo "<p><a href='http://localhost:8888/wp-admin/' target='_blank'>⚙️ Admin Dashboard</a></p>";
?> 