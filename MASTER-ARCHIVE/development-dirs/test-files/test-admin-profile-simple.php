<?php
/**
 * ENNU Life - Simple Admin Profile Test
 *
 * This script provides a simple way to test the admin profile functionality
 * without complex debugging.
 */

// Load WordPress
require_once dirname( __FILE__ ) . '/../../../wp-load.php';

// Ensure we're in admin context
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Access denied' );
}

echo '<h1>ENNU Life - Simple Admin Profile Test</h1>';
echo '<p><strong>Testing the admin profile page functionality.</strong></p>';

// Test user ID
$test_user_id = 1;

echo '<h2>Step 1: Check Admin Profile Page</h2>';

$profile_url = admin_url( 'user-edit.php?user_id=' . $test_user_id );
echo "<p><strong>Admin Profile URL:</strong> <a href='$profile_url' target='_blank'>$profile_url</a></p>";

echo '<h2>Step 2: Manual Test Instructions</h2>';

echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo '<h3>How to Test the Admin Symptoms Tab</h3>';
echo '<ol>';
echo '<li><strong>Click the link above</strong> to open the admin user profile page</li>';
echo '<li><strong>Look for the tab navigation</strong> - you should see tabs like:</li>';
echo '<ul>';
echo '<li>Global & Health Metrics</li>';
echo '<li><strong>Centralized Symptoms</strong> ← This is the new tab</li>';
echo '<li>Individual assessment tabs (Hormone, Health, etc.)</li>';
echo '</ul>';
echo "<li><strong>Click on 'Centralized Symptoms' tab</strong></li>";
echo '<li><strong>Check if the content loads</strong> - you should see:</li>';
echo '<ul>';
echo '<li>Summary statistics cards</li>';
echo '<li>Symptom categories</li>';
echo '<li>Management buttons</li>';
echo '</ul>';
echo "<li><strong>Test the buttons</strong> - try clicking 'Populate Centralized Symptoms'</li>";
echo '<li><strong>Check browser console</strong> (F12) for any JavaScript errors</li>';
echo '</ol>';
echo '</div>';

echo '<h2>Step 3: Expected Behavior</h2>';

echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo '<h3>What Should Happen</h3>';
echo '<ul>';
echo "<li>✅ <strong>Tab appears:</strong> 'Centralized Symptoms' tab should be visible</li>";
echo '<li>✅ <strong>Content loads:</strong> Tab content should display when clicked</li>';
echo '<li>✅ <strong>Buttons work:</strong> Management buttons should be clickable</li>';
echo '<li>✅ <strong>No constant modals:</strong> Should not get repeated confirmation dialogs</li>';
echo '<li>✅ <strong>AJAX works:</strong> Buttons should perform their actions</li>';
echo '</ul>';
echo '</div>';

echo '<h2>Step 4: Troubleshooting</h2>';

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>If Something Doesn't Work</h3>";
echo '<ul>';
echo '<li><strong>Tab not visible:</strong> Check if the admin assets are loading (look for CSS/JS errors in console)</li>';
echo "<li><strong>Content not loading:</strong> Check if there's centralized symptoms data for the user</li>";
echo '<li><strong>Buttons not working:</strong> Check browser console for JavaScript errors</li>';
echo '<li><strong>Constant modals:</strong> This indicates duplicate event listeners (should be fixed now)</li>';
echo '<li><strong>AJAX failures:</strong> Check network tab in browser dev tools</li>';
echo '</ul>';
echo '</div>';

echo '<h2>Step 5: Quick Status Check</h2>';

// Check if classes exist
$status_checks = array(
	'ENNU_Enhanced_Admin class exists'               => class_exists( 'ENNU_Enhanced_Admin' ),
	'ENNU_Centralized_Symptoms_Manager class exists' => class_exists( 'ENNU_Centralized_Symptoms_Manager' ),
	'Admin assets CSS file exists'                   => file_exists( ENNU_LIFE_PLUGIN_PATH . 'assets/css/admin-symptoms-enhanced.css' ),
	'Admin JavaScript file exists'                   => file_exists( ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-admin-enhanced.js' ),
	'AJAX handlers registered'                       => has_action( 'wp_ajax_ennu_populate_centralized_symptoms' ),
);

echo "<div style='background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo '<h3>System Status</h3>';

$passed = 0;
$total  = count( $status_checks );

foreach ( $status_checks as $check => $result ) {
	if ( $result ) {
		echo "✅ $check<br>";
		$passed++;
	} else {
		echo "❌ $check<br>";
	}
}

$success_rate = round( ( $passed / $total ) * 100, 1 );
echo "<br><strong>Status: $success_rate% ($passed/$total checks passed)</strong><br>";

if ( $success_rate >= 80 ) {
	echo '🎉 <strong>System looks good!</strong> Try the admin profile page.<br>';
} else {
	echo '⚠️ <strong>Some issues detected.</strong> Check the failed items above.<br>';
}

echo '</div>';

echo '<h2>Step 6: Next Steps</h2>';

echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo '<h3>What to Do Next</h3>';
echo '<ol>';
echo '<li><strong>Test the admin profile page</strong> using the link above</li>';
echo '<li><strong>Report any issues</strong> you encounter</li>';
echo '<li><strong>Check browser console</strong> for error messages</li>';
echo '<li><strong>Verify the symptoms tab</strong> displays correctly</li>';
echo '<li><strong>Test the management buttons</strong> to ensure they work</li>';
echo '</ol>';
echo '</div>';

echo '<br><strong>Simple test completed!</strong>';
echo '<br><em>Test completed at: ' . current_time( 'mysql' ) . '</em>';


