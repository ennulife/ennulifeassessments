<?php
/**
 * Test Enhanced Symptom History
 * Access via: http://localhost:8888/wp-content/plugins/ennulifeassessments/test-enhanced-symptom-history.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Security check
if (!current_user_can('manage_options')) {
    wp_die('Access denied');
}

echo "<h1>📅 Enhanced Symptom History Test</h1>";

echo "<h2>✅ New Features Added:</h2>";
echo "<ul>";
echo "<li><strong>📋 Assessment Source:</strong> Shows which assessment triggered each symptom</li>";
echo "<li><strong>⏰ Relative Time:</strong> Displays 'x days ago', 'hours ago', etc.</li>";
echo "<li><strong>🎨 Enhanced Styling:</strong> Better visual hierarchy and readability</li>";
echo "<li><strong>📊 More Context:</strong> Assessment names and time context</li>";
echo "</ul>";

echo "<h3>🔧 Helper Functions Added:</h3>";
echo "<ul>";
echo "<li><strong>get_relative_time():</strong> Converts timestamps to human-readable format</li>";
echo "<li><strong>get_assessment_name():</strong> Maps assessment types to friendly names</li>";
echo "</ul>";

echo "<h3>🎯 Time Formatting Examples:</h3>";
echo "<ul>";
echo "<li><strong>Just now:</strong> Less than 1 minute ago</li>";
echo "<li><strong>5 minutes ago:</strong> Recent activity</li>";
echo "<li><strong>2 hours ago:</strong> Same day activity</li>";
echo "<li><strong>3 days ago:</strong> Recent history</li>";
echo "<li><strong>2 weeks ago:</strong> Older entries</li>";
echo "<li><strong>3 months ago:</strong> Historical data</li>";
echo "</ul>";

echo "<h3>📋 Assessment Names:</h3>";
echo "<ul>";
echo "<li><strong>ennu-menopause:</strong> Menopause Assessment</li>";
echo "<li><strong>ennu-skin:</strong> Skin Health Assessment</li>";
echo "<li><strong>ennu-sleep:</strong> Sleep Quality Assessment</li>";
echo "<li><strong>ennu-testosterone:</strong> Testosterone Assessment</li>";
echo "<li><strong>ennu-weight-loss:</strong> Weight Loss Assessment</li>";
echo "<li><strong>ennu-welcome:</strong> Welcome Assessment</li>";
echo "</ul>";

echo "<h2>🔗 Test Links</h2>";
echo "<p><a href='http://localhost/?page_id=2469' target='_blank' style='background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0;'>🏠 Test Your Dashboard</a></p>";
echo "<p><a href='http://localhost/wp-admin/' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0;'>⚙️ Admin Dashboard</a></p>";

echo "<h2>📋 Test Instructions</h2>";
echo "<ol>";
echo "<li>Visit your dashboard: <strong>http://localhost/?page_id=2469</strong></li>";
echo "<li>Navigate to the 'My Symptoms' tab</li>";
echo "<li>Scroll down to the 'Symptom History' section</li>";
echo "<li>Look for the enhanced timeline entries</li>";
echo "<li>Verify each entry shows:</li>";
echo "<ul>";
echo "<li>📅 Date and time</li>";
echo "<li>⏰ Relative time (e.g., '2 days ago')</li>";
echo "<li>📋 Assessment source (if available)</li>";
echo "<li>🏷️ Symptom tags with dark text</li>";
echo "</ul>";
echo "</ol>";

echo "<h3>🎯 Expected Results:</h3>";
echo "<ul>";
echo "<li>✅ Timeline shows relative time formatting</li>";
echo "<li>✅ Assessment sources are displayed</li>";
echo "<li>✅ Better visual hierarchy</li>";
echo "<li>✅ More contextual information</li>";
echo "<li>✅ Improved user experience</li>";
echo "</ul>";

echo "<p><strong>Status:</strong> ✅ Enhanced symptom history READY!</p>";
echo "<p><strong>Next:</strong> Test the dashboard to see the improved timeline</p>";
?> 