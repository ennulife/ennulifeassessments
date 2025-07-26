<?php
/**
 * Simple Form Submission Test
 * 
 * This file tests the biomarker form submission functionality
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	require_once( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php' );
}

// Check if user is logged in and has admin privileges
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Access denied' );
}

echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; background: #f9f9f9; border-radius: 8px;">';
echo '<h1>🧪 Simple Form Submission Test</h1>';

$user_id = get_current_user_id();

// Test 1: Check current biomarker data
echo '<h2>1. Current Biomarker Data</h2>';
$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );

if ( ! empty( $biomarker_data ) ) {
	echo '<p>✅ Biomarker data found:</p>';
	echo '<pre style="background: #fff; padding: 10px; border: 1px solid #ddd; overflow-x: auto;">';
	print_r( $biomarker_data );
	echo '</pre>';
} else {
	echo '<p>❌ No biomarker data found</p>';
}

// Test 2: Simulate form submission
echo '<h2>2. Form Submission Test</h2>';

if ( isset( $_POST['test_biomarker_save'] ) ) {
	echo '<div style="background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; margin: 10px 0;">';
	echo '<h3>🧪 Test Results:</h3>';
	
	// Simulate POST data for biomarker submission
	$_POST['biomarker_value'] = array(
		'glucose' => '95',
		'hba1c' => '5.2',
		'testosterone' => '650'
	);
	$_POST['biomarker_unit'] = array(
		'glucose' => 'mg/dL',
		'hba1c' => '%',
		'testosterone' => 'ng/dL'
	);
	$_POST['test_date'] = array(
		'glucose' => '2025-01-24',
		'hba1c' => '2025-01-24',
		'testosterone' => '2025-01-24'
	);
	$_POST['ennu_assessment_nonce'] = wp_create_nonce( 'ennu_user_profile_update_' . $user_id );
	
	// Call the save method
	if ( class_exists( 'ENNU_Enhanced_Admin' ) ) {
		$admin = new ENNU_Enhanced_Admin();
		$admin->save_user_assessment_fields( $user_id );
		
		// Check if data was saved
		$saved_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		if ( ! empty( $saved_data ) ) {
			echo '<p>✅ Test successful! Biomarker data saved:</p>';
			echo '<pre style="background: #fff; padding: 10px; border: 1px solid #ddd;">';
			print_r( $saved_data );
			echo '</pre>';
		} else {
			echo '<p>❌ Test failed! No biomarker data was saved.</p>';
		}
	} else {
		echo '<p>❌ ENNU_Enhanced_Admin class not found</p>';
	}
	
	echo '</div>';
} else {
	echo '<form method="post" style="background: #fff; padding: 20px; border: 1px solid #ddd; margin: 10px 0;">';
	echo '<input type="hidden" name="test_biomarker_save" value="1">';
	echo '<p>This will simulate saving biomarker data with test values:</p>';
	echo '<ul>';
	echo '<li>Glucose: 95 mg/dL</li>';
	echo '<li>HbA1c: 5.2%</li>';
	echo '<li>Testosterone: 650 ng/dL</li>';
	echo '</ul>';
	echo '<button type="submit" style="background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">🧪 Test Biomarker Save</button>';
	echo '</form>';
}

// Test 3: Check if the profile page is accessible
echo '<h2>3. Profile Page Access</h2>';
echo '<p>To test the actual form submission:</p>';
echo '<ol>';
echo '<li><strong>Go to Profile Page:</strong> <a href="' . admin_url( 'profile.php' ) . '" target="_blank">' . admin_url( 'profile.php' ) . '</a></li>';
echo '<li><strong>Scroll down</strong> to find the "Biomarkers" section</li>';
echo '<li><strong>Enter test values</strong> in any biomarker fields</li>';
echo '<li><strong>Click "Update Profile"</strong> to save</li>';
echo '<li><strong>Refresh the page</strong> to verify data persists</li>';
echo '</ol>';

// Test 4: Debug log monitoring
echo '<h2>4. Recent Debug Log</h2>';
$debug_log = WP_CONTENT_DIR . '/debug.log';
if ( file_exists( $debug_log ) ) {
	$log_entries = file( $debug_log );
	$recent_entries = array_slice( $log_entries, -10 ); // Last 10 entries
	
	echo '<div style="background: #000; color: #0f0; padding: 10px; font-family: monospace; font-size: 12px; max-height: 200px; overflow-y: auto;">';
	foreach ( $recent_entries as $entry ) {
		if ( strpos( $entry, 'ENNU' ) !== false ) {
			echo esc_html( $entry );
		}
	}
	echo '</div>';
} else {
	echo '<p>❌ Debug log not found</p>';
}

echo '</div>';
?> 