<?php
/**
 * Test Biomarker Profile Integration
 *
 * This script verifies that the biomarker management tab is properly
 * integrated into the WordPress admin profile page.
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
echo '<h1>🧪 Biomarker Profile Integration Test</h1>';

// Test 1: Check if biomarker management classes exist
echo '<h2>1. Class Existence Check</h2>';
$classes_to_check = array(
	'ENNU_Biomarker_Manager',
	'ENNU_Lab_Import_Manager',
	'ENNU_Smart_Recommendation_Engine',
);

foreach ( $classes_to_check as $class ) {
	$exists = class_exists( $class );
	echo '<p>' . ( $exists ? '✅' : '❌' ) . ' ' . $class . ': ' . ( $exists ? 'EXISTS' : 'MISSING' ) . '</p>';
}

// Test 2: Check if admin class has biomarker method
echo '<h2>2. Admin Method Check</h2>';
$admin      = new ENNU_Enhanced_Admin();
$has_method = method_exists( $admin, 'add_biomarker_management_tab' );
echo '<p>' . ( $has_method ? '✅' : '❌' ) . ' add_biomarker_management_tab method: ' . ( $has_method ? 'EXISTS' : 'MISSING' ) . '</p>';

// Test 3: Check if WordPress hooks are registered
echo '<h2>3. WordPress Hook Registration</h2>';
$has_show_profile = has_action( 'show_user_profile', array( $admin, 'add_biomarker_management_tab' ) );
$has_edit_profile = has_action( 'edit_user_profile', array( $admin, 'add_biomarker_management_tab' ) );

echo '<p>' . ( $has_show_profile ? '✅' : '❌' ) . ' show_user_profile hook: ' . ( $has_show_profile ? 'REGISTERED' : 'NOT REGISTERED' ) . '</p>';
echo '<p>' . ( $has_edit_profile ? '✅' : '❌' ) . ' edit_user_profile hook: ' . ( $has_edit_profile ? 'REGISTERED' : 'NOT REGISTERED' ) . '</p>';

// Test 4: Check AJAX handlers
echo '<h2>4. AJAX Handler Registration</h2>';
$has_import_ajax = has_action( 'wp_ajax_ennu_import_lab_results', array( $admin, 'ajax_import_lab_results' ) );
$has_save_ajax   = has_action( 'wp_ajax_ennu_save_biomarker', array( $admin, 'ajax_save_biomarker' ) );

echo '<p>' . ( $has_import_ajax ? '✅' : '❌' ) . ' ennu_import_lab_results AJAX: ' . ( $has_import_ajax ? 'REGISTERED' : 'NOT REGISTERED' ) . '</p>';
echo '<p>' . ( $has_save_ajax ? '✅' : '❌' ) . ' ennu_save_biomarker AJAX: ' . ( $has_save_ajax ? 'REGISTERED' : 'NOT REGISTERED' ) . '</p>';

// Test 5: Check if biomarker data structure is accessible
echo '<h2>5. Biomarker Data Structure Test</h2>';
try {
	$biomarker_manager = new ENNU_Biomarker_Manager();
	$current_user_id   = get_current_user_id();
	$user_biomarkers   = $biomarker_manager->get_user_biomarkers( $current_user_id );

	echo '<p>✅ Biomarker Manager: WORKING</p>';
	echo '<p>📊 User Biomarkers Count: ' . count( $user_biomarkers ) . '</p>';

	if ( ! empty( $user_biomarkers ) ) {
		echo '<h3>Current Biomarkers:</h3>';
		echo '<ul>';
		foreach ( $user_biomarkers as $biomarker_name => $tests ) {
			$latest = end( $tests );
			echo '<li><strong>' . esc_html( $biomarker_name ) . '</strong>: ' . esc_html( $latest['value'] ) . ' ' . esc_html( $latest['unit'] ) . ' (' . esc_html( $latest['status'] ) . ')</li>';
		}
		echo '</ul>';
	} else {
		echo '<p>📝 No biomarker data found for current user.</p>';
	}
} catch ( Exception $e ) {
	echo '<p>❌ Biomarker Manager Error: ' . esc_html( $e->getMessage() ) . '</p>';
}

// Test 6: Check smart recommendation engine
echo '<h2>6. Smart Recommendation Engine Test</h2>';
try {
	$smart_engine    = new ENNU_Smart_Recommendation_Engine();
	$current_user_id = get_current_user_id();
	$recommendations = $smart_engine->get_updated_recommendations( $current_user_id );

	echo '<p>✅ Smart Recommendation Engine: WORKING</p>';
	echo '<p>🎯 Recommendations Count: ' . count( $recommendations ) . '</p>';

	if ( ! empty( $recommendations ) ) {
		echo '<h3>Current Recommendations:</h3>';
		echo '<ul>';
		foreach ( array_slice( $recommendations, 0, 5 ) as $rec ) { // Show first 5
			echo '<li><strong>' . esc_html( $rec['biomarker'] ) . '</strong>: ' . esc_html( $rec['reason'] ) . ' (' . esc_html( $rec['urgency'] ) . ' urgency)</li>';
		}
		echo '</ul>';
	} else {
		echo '<p>📝 No recommendations at this time.</p>';
	}
} catch ( Exception $e ) {
	echo '<p>❌ Smart Recommendation Engine Error: ' . esc_html( $e->getMessage() ) . '</p>';
}

// Test 7: Check lab import manager
echo '<h2>7. Lab Import Manager Test</h2>';
try {
	$lab_import_manager  = new ENNU_Lab_Import_Manager();
	$supported_providers = $lab_import_manager->get_supported_providers();

	echo '<p>✅ Lab Import Manager: WORKING</p>';
	echo '<p>🏥 Supported Providers: ' . count( $supported_providers ) . '</p>';

	echo '<h3>Supported Lab Providers:</h3>';
	echo '<ul>';
	foreach ( $supported_providers as $key => $provider ) {
		echo '<li><strong>' . esc_html( $provider['name'] ) . '</strong> (' . esc_html( $key ) . ')</li>';
	}
	echo '</ul>';
} catch ( Exception $e ) {
	echo '<p>❌ Lab Import Manager Error: ' . esc_html( $e->getMessage() ) . '</p>';
}

// Test 8: Instructions for manual verification
echo '<h2>8. Manual Verification Instructions</h2>';
echo '<div style="background: #f0f8ff; padding: 20px; border-left: 4px solid #0073aa; margin: 20px 0;">';
echo '<h3>🔍 How to Verify Biomarker Tab in WordPress Admin:</h3>';
echo '<ol>';
echo '<li><strong>Go to WordPress Admin</strong>: Navigate to <code>/wp-admin/profile.php</code></li>';
echo '<li><strong>Look for Biomarker Tab</strong>: Scroll down to find the "Biomarker Management" section</li>';
echo '<li><strong>Check Features</strong>: Verify the following sections are present:';
echo '<ul>';
echo '<li>Current Biomarkers overview</li>';
echo '<li>Recommended Tests</li>';
echo '<li>Import Lab Results form</li>';
echo '<li>Manual Biomarker Entry form</li>';
echo '</ul></li>';
echo '<li><strong>Test Functionality</strong>: Try uploading a lab file or adding a manual biomarker</li>';
echo '</ol>';
echo '</div>';

// Test 9: Summary
echo '<h2>9. Integration Summary</h2>';
$all_tests_passed = $has_method && $has_show_profile && $has_edit_profile && $has_import_ajax && $has_save_ajax;

if ( $all_tests_passed ) {
	echo '<div style="background: #d4edda; padding: 20px; border-left: 4px solid #28a745; margin: 20px 0;">';
	echo '<h3>🎉 SUCCESS: Biomarker Profile Integration Complete!</h3>';
	echo '<p>The biomarker management tab is properly integrated into the WordPress admin profile page.</p>';
	echo '<p><strong>Next Steps:</strong> Visit <code>/wp-admin/profile.php</code> to see the biomarker management interface.</p>';
	echo '</div>';
} else {
	echo '<div style="background: #f8d7da; padding: 20px; border-left: 4px solid #dc3545; margin: 20px 0;">';
	echo '<h3>⚠️ ISSUES DETECTED</h3>';
	echo '<p>Some components of the biomarker profile integration are missing or not properly configured.</p>';
	echo '<p>Please check the individual test results above and ensure all required components are loaded.</p>';
	echo '</div>';
}

echo '</div>';


