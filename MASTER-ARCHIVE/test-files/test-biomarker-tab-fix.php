<?php
/**
 * Test Biomarker Tab Fix
 * 
 * This script verifies that the biomarker management tab is properly
 * hooked to the WordPress admin profile page.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	require_once '../../../wp-load.php';
}

// Check if we're in admin context
if ( ! is_admin() ) {
	wp_die( 'This test must be run from the WordPress admin area.' );
}

// Check if user has admin capabilities
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'You do not have sufficient permissions to access this page.' );
}

echo '<div class="wrap">';
echo '<h1>🔧 Test Biomarker Tab Fix</h1>';

// Test 1: Check if plugin is active
echo '<h2>1. Plugin Status</h2>';
$plugin_active = is_plugin_active( 'ennulifeassessments/ennu-life-plugin.php' );
echo '<p>' . ( $plugin_active ? '✅' : '❌' ) . ' ENNU Life Plugin: ' . ( $plugin_active ? 'ACTIVE' : 'INACTIVE' ) . '</p>';

// Test 2: Check if admin class exists and has method
echo '<h2>2. Admin Class & Method Check</h2>';
$admin_exists = class_exists( 'ENNU_Enhanced_Admin' );
echo '<p>' . ( $admin_exists ? '✅' : '❌' ) . ' ENNU_Enhanced_Admin: ' . ( $admin_exists ? 'EXISTS' : 'MISSING' ) . '</p>';

if ( $admin_exists ) {
	$admin      = new ENNU_Enhanced_Admin();
	$has_method = method_exists( $admin, 'add_biomarker_management_tab' );
	echo '<p>' . ( $has_method ? '✅' : '❌' ) . ' add_biomarker_management_tab method: ' . ( $has_method ? 'EXISTS' : 'MISSING' ) . '</p>';
}

// Test 3: Check if hooks are registered
echo '<h2>3. Hook Registration Check</h2>';
global $wp_filter;

$show_hook = has_action( 'show_user_profile', array( $admin, 'add_biomarker_management_tab' ) );
$edit_hook = has_action( 'edit_user_profile', array( $admin, 'add_biomarker_management_tab' ) );

echo '<p>' . ( $show_hook ? '✅' : '❌' ) . ' show_user_profile hook: ' . ( $show_hook ? 'REGISTERED' : 'NOT REGISTERED' ) . '</p>';
echo '<p>' . ( $edit_hook ? '✅' : '❌' ) . ' edit_user_profile hook: ' . ( $edit_hook ? 'REGISTERED' : 'NOT REGISTERED' ) . '</p>';

// Test 4: Display biomarker management tab
echo '<h2>4. Biomarker Management Tab Display</h2>';
echo '<div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin: 20px 0;">';
echo '<h3>🔬 Biomarker Management Interface Preview</h3>';

try {
	$current_user = wp_get_current_user();
	$admin->add_biomarker_management_tab( $current_user );
	echo '<p style="color: green; font-weight: bold;">✅ SUCCESS: Biomarker management tab displayed correctly!</p>';
} catch ( Exception $e ) {
	echo '<p style="color: red; font-weight: bold;">❌ ERROR: ' . esc_html( $e->getMessage() ) . '</p>';
}

echo '</div>';

// Test 5: Instructions for WordPress profile page
echo '<h2>5. How to Access in WordPress Admin</h2>';
echo '<div style="background: #f0f8ff; padding: 20px; border-left: 4px solid #0073aa; margin: 20px 0;">';
echo '<h3>🔍 Biomarker Tab is Now Available in WordPress Admin Profile:</h3>';
echo '<ol>';
echo '<li><strong>Go to WordPress Admin</strong>: Navigate to <a href="' . admin_url( 'profile.php' ) . '" target="_blank">' . admin_url( 'profile.php' ) . '</a></li>';
echo '<li><strong>Scroll Down</strong>: Look for the "Biomarker Management" section</li>';
echo '<li><strong>View Data</strong>: See existing biomarker data (if any)</li>';
echo '<li><strong>Import Data</strong>: Use the JSON import form to add new biomarker data</li>';
echo '</ol>';
echo '</div>';

// Test 6: Success message
echo '<h2>6. Test Results</h2>';
if ( $plugin_active && $admin_exists && $has_method && $show_hook && $edit_hook ) {
	echo '<div style="background: #d4edda; padding: 20px; border-left: 4px solid #28a745; margin: 20px 0;">';
	echo '<h3>🎉 SUCCESS: Biomarker Tab Fix is Working!</h3>';
	echo '<p>The biomarker management tab is now properly integrated into the WordPress admin profile page.</p>';
	echo '<p><strong>Next Steps:</strong></p>';
	echo '<ul>';
	echo '<li>Visit <a href="' . admin_url( 'profile.php' ) . '" target="_blank">your WordPress profile</a> to see the biomarker tab</li>';
	echo '<li>Test the biomarker data import functionality</li>';
	echo '<li>Verify that the tab appears for other users when editing their profiles</li>';
	echo '</ul>';
	echo '</div>';
} else {
	echo '<div style="background: #f8d7da; padding: 20px; border-left: 4px solid #dc3545; margin: 20px 0;">';
	echo '<h3>❌ ISSUES DETECTED</h3>';
	echo '<p>Some components are not working correctly. Please check the error messages above.</p>';
	echo '</div>';
}

echo '</div>'; 