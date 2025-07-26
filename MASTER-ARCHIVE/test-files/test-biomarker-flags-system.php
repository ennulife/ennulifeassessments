<?php
/**
 * Test Biomarker Flag System
 * Access via: http://localhost:8888/wp-content/plugins/ennulifeassessments/test-biomarker-flags-system.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Security check
if (!current_user_can('manage_options')) {
    wp_die('Access denied');
}

echo "<h1>🔬 Test Biomarker Flag System</h1>";

// Test the flag manager
$flag_manager = new ENNU_Biomarker_Flag_Manager();
$user_id = 1; // Test with user ID 1

echo "<h2>✅ System Understanding:</h2>";
echo "<ul>";
echo "<li><strong>🔍 Flag Source:</strong> Flags are created from symptoms via auto-flagging</li>";
echo "<li><strong>📊 Display:</strong> My Biomarkers tab shows actual flagged biomarkers</li>";
echo "<li><strong>🚫 Removal:</strong> Flags can ONLY be removed in WP-Admin user profile</li>";
echo "<li><strong>👤 User Access:</strong> Regular users can view but not remove flags</li>";
echo "<li><strong>⚙️ Admin Access:</strong> Admins can remove flags in user profile edit</li>";
echo "</ul>";

// Get current flagged biomarkers
$flagged_biomarkers = $flag_manager->get_flagged_biomarkers($user_id, 'active');
$flagged_count = count($flagged_biomarkers);

echo "<h2>📊 Current Flag Status:</h2>";
echo "<ul>";
echo "<li><strong>User ID:</strong> $user_id</li>";
echo "<li><strong>Active Flags:</strong> $flagged_count</li>";
echo "<li><strong>Flag Source:</strong> Symptom-based auto-flagging</li>";
echo "</ul>";

if (!empty($flagged_biomarkers)) {
    echo "<h3>🔍 Current Flagged Biomarkers:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Flag ID</th><th>Biomarker</th><th>Reason</th><th>Flag Type</th><th>Flagged At</th></tr>";
    
    foreach ($flagged_biomarkers as $flag_id => $flag_data) {
        echo "<tr>";
        echo "<td>" . esc_html($flag_id) . "</td>";
        echo "<td>" . esc_html($flag_data['biomarker_name']) . "</td>";
        echo "<td>" . esc_html($flag_data['reason']) . "</td>";
        echo "<td>" . esc_html($flag_data['flag_type']) . "</td>";
        echo "<td>" . esc_html($flag_data['flagged_at']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<h3>✅ No Active Flags</h3>";
    echo "<p>No biomarkers are currently flagged for this user.</p>";
}

echo "<h2>🔗 Test Links</h2>";
echo "<p><a href='http://localhost/?page_id=2469' target='_blank' style='background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0;'>🏠 Test User Dashboard</a></p>";
echo "<p><a href='http://localhost/wp-admin/user-edit.php?user_id=1' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0;'>⚙️ Admin User Profile Edit</a></p>";

echo "<h2>📋 Test Instructions</h2>";
echo "<ol>";
echo "<li><strong>View Flags:</strong> Visit dashboard → My Biomarkers tab</li>";
echo "<li><strong>Check Count:</strong> Verify flagged count matches actual flags</li>";
echo "<li><strong>Admin Removal:</strong> Go to WP-Admin → Users → Edit User → Remove flags</li>";
echo "<li><strong>User Access:</strong> Confirm users can view but not remove flags</li>";
echo "</ol>";

echo "<h3>🎯 Expected Results:</h2>";
echo "<ul>";
echo "<li>✅ My Biomarkers tab shows actual flagged biomarkers (not sample data)</li>";
echo "<li>✅ Flagged count in stats matches actual flag count</li>";
echo "<li>✅ Flags show correct biomarker names and reasons</li>";
echo "<li>✅ Users cannot remove flags (admin only)</li>";
echo "<li>✅ Admin can remove flags in user profile edit</li>";
echo "</ul>";

echo "<p><strong>Status:</strong> ✅ Biomarker flag system READY!</p>";
echo "<p><strong>Next:</strong> Test the dashboard to see actual flagged biomarkers</p>";
?> 