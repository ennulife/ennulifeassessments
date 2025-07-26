<?php
/**
 * Test Biomarker Tab Display
 *
 * This script directly displays the biomarker management tab
 * to test if the functionality works independently of WordPress hooks.
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
echo '<h1>🧪 Biomarker Tab Display Test</h1>';

// Test 1: Check if classes exist
echo '<h2>1. Class Availability</h2>';
$classes_exist = true;

$required_classes = array(
	'ENNU_Enhanced_Admin',
	'ENNU_Biomarker_Manager',
	'ENNU_Lab_Import_Manager',
	'ENNU_Smart_Recommendation_Engine',
);

foreach ( $required_classes as $class ) {
	$exists = class_exists( $class );
	echo '<p>' . ( $exists ? '✅' : '❌' ) . ' ' . $class . ': ' . ( $exists ? 'EXISTS' : 'MISSING' ) . '</p>';
	if ( ! $exists ) {
		$classes_exist = false;
	}
}

if ( ! $classes_exist ) {
	echo '<div style="background: #f8d7da; padding: 20px; border-left: 4px solid #dc3545; margin: 20px 0;">';
	echo '<h3>❌ CRITICAL ERROR</h3>';
	echo '<p>Required classes are missing. The biomarker management system cannot function.</p>';
	echo '</div>';
	echo '</div>';
	return;
}

// Test 2: Create instances and test functionality
echo '<h2>2. Biomarker Management Tab Display</h2>';

try {
	// Create admin instance
	$admin = new ENNU_Enhanced_Admin();

	// Get current user
	$current_user = wp_get_current_user();

	// Check if method exists
	if ( ! method_exists( $admin, 'add_biomarker_management_tab' ) ) {
		throw new Exception( 'add_biomarker_management_tab method not found in ENNU_Enhanced_Admin' );
	}

	echo '<div style="background: #d4edda; padding: 20px; border-left: 4px solid #28a745; margin: 20px 0;">';
	echo '<h3>✅ SUCCESS: Displaying Biomarker Management Tab</h3>';
	echo '<p>All required classes and methods are available. Displaying the biomarker management interface below:</p>';
	echo '</div>';

	// Display the biomarker management tab
	echo '<div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin: 20px 0;">';
	echo '<h3>🔬 Biomarker Management Interface</h3>';

	// Call the biomarker management tab method directly
	$admin->add_biomarker_management_tab( $current_user );

	echo '</div>';

} catch ( Exception $e ) {
	echo '<div style="background: #f8d7da; padding: 20px; border-left: 4px solid #dc3545; margin: 20px 0;">';
	echo '<h3>❌ ERROR</h3>';
	echo '<p>Error displaying biomarker management tab: ' . esc_html( $e->getMessage() ) . '</p>';
	echo '</div>';
}

// Test 3: Manual hook registration
echo '<h2>3. Manual Hook Registration Test</h2>';

try {
	// Remove any existing hooks
	remove_action( 'show_user_profile', array( $admin, 'add_biomarker_management_tab' ) );
	remove_action( 'edit_user_profile', array( $admin, 'add_biomarker_management_tab' ) );

	// Add hooks manually
	add_action( 'show_user_profile', array( $admin, 'add_biomarker_management_tab' ) );
	add_action( 'edit_user_profile', array( $admin, 'add_biomarker_management_tab' ) );

	echo '<p>✅ Manual hook registration completed</p>';

	// Check if hooks are registered
	$show_hook = has_action( 'show_user_profile', array( $admin, 'add_biomarker_management_tab' ) );
	$edit_hook = has_action( 'edit_user_profile', array( $admin, 'add_biomarker_management_tab' ) );

	echo '<p>' . ( $show_hook ? '✅' : '❌' ) . ' show_user_profile hook: ' . ( $show_hook ? 'REGISTERED' : 'NOT REGISTERED' ) . '</p>';
	echo '<p>' . ( $edit_hook ? '✅' : '❌' ) . ' edit_user_profile hook: ' . ( $edit_hook ? 'REGISTERED' : 'NOT REGISTERED' ) . '</p>';

} catch ( Exception $e ) {
	echo '<p>❌ Error during manual hook registration: ' . esc_html( $e->getMessage() ) . '</p>';
}

// Test 4: Instructions for WordPress profile page
echo '<h2>4. Next Steps</h2>';
echo '<div style="background: #f0f8ff; padding: 20px; border-left: 4px solid #0073aa; margin: 20px 0;">';
echo '<h3>🔍 How to Access Biomarker Management in WordPress Admin:</h3>';
echo '<ol>';
echo '<li><strong>Go to WordPress Admin</strong>: Navigate to <code>/wp-admin/profile.php</code></li>';
echo '<li><strong>Scroll Down</strong>: Look for the "Biomarker Management" section</li>';
echo '<li><strong>If Not Visible</strong>: Try deactivating and reactivating the ENNU Life plugin</li>';
echo '<li><strong>Clear Cache</strong>: If using any caching plugins, clear the cache</li>';
echo '<li><strong>Check Error Logs</strong>: Look for any PHP errors in your server logs</li>';
echo '</ol>';
echo '</div>';

// Test 5: Alternative access method
echo '<h2>5. Alternative Access Method</h2>';
echo '<div style="background: #fff3cd; padding: 20px; border-left: 4px solid #ffc107; margin: 20px 0;">';
echo '<h3>🔄 If Biomarker Tab is Not Visible in Profile:</h3>';
echo '<p>The biomarker management interface is also available through the main ENNU Life admin menu:</p>';
echo '<ol>';
echo '<li>Go to <strong>ENNU Life</strong> → <strong>Dashboard</strong> in the WordPress admin menu</li>';
echo '<li>Look for biomarker management options in the admin dashboard</li>';
echo '<li>Or access via: <code>/wp-admin/admin.php?page=ennu-life</code></li>';
echo '</ol>';
echo '</div>';

echo '</div>';


