<?php
/**
 * Debug Biomarker Hooks
 *
 * This script checks if the biomarker management hooks are properly registered.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	require_once '../../../wp-load.php';
}

// Check if we're in admin context
if ( ! is_admin() ) {
	wp_die( 'This test must be run from the WordPress admin area.' );
}

echo '<div class="wrap">';
echo '<h1>🔧 Debug Biomarker Hooks</h1>';

// Test 1: Check if plugin is active
echo '<h2>1. Plugin Status</h2>';
$plugin_active = is_plugin_active( 'ennulifeassessments/ennu-life-plugin.php' );
echo '<p>' . ( $plugin_active ? '✅' : '❌' ) . ' ENNU Life Plugin: ' . ( $plugin_active ? 'ACTIVE' : 'INACTIVE' ) . '</p>';

// Test 2: Check if admin class exists
echo '<h2>2. Admin Class Check</h2>';
$admin_exists = class_exists( 'ENNU_Enhanced_Admin' );
echo '<p>' . ( $admin_exists ? '✅' : '❌' ) . ' ENNU_Enhanced_Admin: ' . ( $admin_exists ? 'EXISTS' : 'MISSING' ) . '</p>';

if ( $admin_exists ) {
	$admin      = new ENNU_Enhanced_Admin();
	$has_method = method_exists( $admin, 'add_biomarker_management_tab' );
	echo '<p>' . ( $has_method ? '✅' : '❌' ) . ' add_biomarker_management_tab method: ' . ( $has_method ? 'EXISTS' : 'MISSING' ) . '</p>';
}

// Test 3: Check all registered hooks
echo '<h2>3. Registered Hooks</h2>';
global $wp_filter;

$hooks_to_check = array(
	'show_user_profile',
	'edit_user_profile',
	'personal_options_update',
	'edit_user_profile_update',
);

foreach ( $hooks_to_check as $hook ) {
	if ( isset( $wp_filter[ $hook ] ) ) {
		echo '<p>✅ <strong>' . $hook . '</strong>: ' . count( $wp_filter[ $hook ]->callbacks ) . ' callbacks registered</p>';

		// Show the callbacks
		foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( is_array( $callback['function'] ) ) {
					$class  = is_object( $callback['function'][0] ) ? get_class( $callback['function'][0] ) : $callback['function'][0];
					$method = $callback['function'][1];
					echo '<p style="margin-left: 20px;">- Priority ' . $priority . ': ' . $class . '::' . $method . '</p>';
				} else {
					echo '<p style="margin-left: 20px;">- Priority ' . $priority . ': ' . $callback['function'] . '</p>';
				}
			}
		}
	} else {
		echo '<p>❌ <strong>' . $hook . '</strong>: NO CALLBACKS REGISTERED</p>';
	}
}

// Test 4: Check AJAX hooks
echo '<h2>4. AJAX Hooks</h2>';
$ajax_hooks_to_check = array(
	'wp_ajax_ennu_import_lab_results',
	'wp_ajax_ennu_save_biomarker',
);

foreach ( $ajax_hooks_to_check as $hook ) {
	if ( isset( $wp_filter[ $hook ] ) ) {
		echo '<p>✅ <strong>' . $hook . '</strong>: ' . count( $wp_filter[ $hook ]->callbacks ) . ' callbacks registered</p>';
	} else {
		echo '<p>❌ <strong>' . $hook . '</strong>: NO CALLBACKS REGISTERED</p>';
	}
}

// Test 5: Check if biomarker classes exist
echo '<h2>5. Biomarker Classes</h2>';
$biomarker_classes = array(
	'ENNU_Biomarker_Manager',
	'ENNU_Lab_Import_Manager',
	'ENNU_Smart_Recommendation_Engine',
);

foreach ( $biomarker_classes as $class ) {
	$exists = class_exists( $class );
	echo '<p>' . ( $exists ? '✅' : '❌' ) . ' ' . $class . ': ' . ( $exists ? 'EXISTS' : 'MISSING' ) . '</p>';
}

// Test 6: Manual hook registration test
echo '<h2>6. Manual Hook Registration Test</h2>';
if ( $admin_exists && $has_method ) {
	echo '<p>Attempting to manually register biomarker hooks...</p>';

	// Remove existing hooks first
	remove_action( 'show_user_profile', array( $admin, 'add_biomarker_management_tab' ) );
	remove_action( 'edit_user_profile', array( $admin, 'add_biomarker_management_tab' ) );

	// Re-add hooks
	add_action( 'show_user_profile', array( $admin, 'add_biomarker_management_tab' ) );
	add_action( 'edit_user_profile', array( $admin, 'add_biomarker_management_tab' ) );

	echo '<p>✅ Manual hook registration completed</p>';

	// Check if they're now registered
	$show_hook = has_action( 'show_user_profile', array( $admin, 'add_biomarker_management_tab' ) );
	$edit_hook = has_action( 'edit_user_profile', array( $admin, 'add_biomarker_management_tab' ) );

	echo '<p>' . ( $show_hook ? '✅' : '❌' ) . ' show_user_profile hook: ' . ( $show_hook ? 'REGISTERED' : 'NOT REGISTERED' ) . '</p>';
	echo '<p>' . ( $edit_hook ? '✅' : '❌' ) . ' edit_user_profile hook: ' . ( $edit_hook ? 'REGISTERED' : 'NOT REGISTERED' ) . '</p>';
} else {
	echo '<p>❌ Cannot test manual registration - admin class or method missing</p>';
}

// Test 7: Instructions
echo '<h2>7. Next Steps</h2>';
echo '<div style="background: #f0f8ff; padding: 20px; border-left: 4px solid #0073aa; margin: 20px 0;">';
echo '<h3>🔍 Troubleshooting Steps:</h3>';
echo '<ol>';
echo '<li><strong>Deactivate and Reactivate</strong> the ENNU Life plugin</li>';
echo '<li><strong>Clear Cache</strong> if using any caching plugins</li>';
echo '<li><strong>Check Error Logs</strong> for any PHP errors</li>';
echo '<li><strong>Visit Profile Page</strong>: Go to <code>/wp-admin/profile.php</code></li>';
echo '<li><strong>Look for Biomarker Section</strong>: Scroll down to find "Biomarker Management"</li>';
echo '</ol>';
echo '</div>';

echo '</div>';


