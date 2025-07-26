<?php
/**
 * Form Debug Test
 * 
 * This file helps debug form submission issues
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	require_once( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php' );
}

echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; background: #f9f9f9; border-radius: 8px;">';
echo '<h1>🔍 Form Debug Test</h1>';

$user_id = get_current_user_id();

// Test 1: Check current biomarker data
echo '<h2>1. Current Biomarker Data</h2>';
$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
if ( ! empty( $biomarker_data ) ) {
	echo '<p>✅ Current biomarker data found:</p>';
	echo '<pre style="background: #fff; padding: 10px; border: 1px solid #ddd; max-height: 200px; overflow-y: auto;">';
	print_r( $biomarker_data );
	echo '</pre>';
} else {
	echo '<p>❌ No current biomarker data found</p>';
}

// Test 2: Check if form submission is working
echo '<h2>2. Form Submission Test</h2>';

if ( isset( $_POST['test_form'] ) ) {
	echo '<div style="background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; margin: 10px 0;">';
	echo '<h3>🧪 Form Submission Results:</h3>';
	
	echo '<p><strong>POST Data Received:</strong></p>';
	echo '<pre style="background: #fff; padding: 10px; border: 1px solid #ddd;">';
	print_r( $_POST );
	echo '</pre>';
	
	// Check if biomarker values are in POST
	if ( isset( $_POST['biomarker_value'] ) && is_array( $_POST['biomarker_value'] ) ) {
		echo '<p>✅ biomarker_value array found in POST</p>';
		foreach ( $_POST['biomarker_value'] as $key => $value ) {
			echo '<p>• ' . esc_html( $key ) . ': ' . esc_html( $value ) . '</p>';
		}
	} else {
		echo '<p>❌ biomarker_value array NOT found in POST</p>';
	}
	
	// Check nonce
	if ( isset( $_POST['ennu_assessment_nonce'] ) ) {
		echo '<p>✅ Nonce found in POST</p>';
		$nonce_valid = wp_verify_nonce( $_POST['ennu_assessment_nonce'], 'ennu_user_profile_update_' . $user_id );
		echo '<p>Nonce valid: ' . ( $nonce_valid ? '✅ Yes' : '❌ No' ) . '</p>';
	} else {
		echo '<p>❌ Nonce NOT found in POST</p>';
	}
	
	echo '</div>';
} else {
	echo '<form method="post" style="background: #fff; padding: 20px; border: 1px solid #ddd; margin: 10px 0;">';
	echo '<input type="hidden" name="test_form" value="1">';
	echo '<input type="hidden" name="ennu_assessment_nonce" value="' . wp_create_nonce( 'ennu_user_profile_update_' . $user_id ) . '">';
	
	echo '<h3>Test Biomarker Values:</h3>';
	echo '<div style="margin: 10px 0;">';
	echo '<label>Glucose: <input type="number" step="0.01" name="biomarker_value[glucose]" value="98" placeholder="Enter value"></label>';
	echo '</div>';
	echo '<div style="margin: 10px 0;">';
	echo '<label>HbA1c: <input type="number" step="0.01" name="biomarker_value[hba1c]" value="5.2" placeholder="Enter value"></label>';
	echo '</div>';
	echo '<div style="margin: 10px 0;">';
	echo '<label>Testosterone: <input type="number" step="0.01" name="biomarker_value[testosterone]" value="650" placeholder="Enter value"></label>';
	echo '</div>';
	
	echo '<button type="submit" style="background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">🔍 Test Form Submission</button>';
	echo '</form>';
}

// Test 3: Direct save test
echo '<h2>3. Direct Save Test</h2>';

if ( isset( $_POST['test_direct_save'] ) ) {
	echo '<div style="background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; margin: 10px 0;">';
	echo '<h3>🧪 Direct Save Results:</h3>';
	
	// Create test data
	$test_data = array(
		'glucose' => array(
			'value' => '98',
			'unit' => 'mg/dL',
			'test_date' => '2025-01-24',
			'last_updated' => current_time( 'mysql' )
		),
		'hba1c' => array(
			'value' => '5.2',
			'unit' => '%',
			'test_date' => '2025-01-24',
			'last_updated' => current_time( 'mysql' )
		)
	);
	
	// Save directly
	$result = update_user_meta( $user_id, 'ennu_biomarker_data', $test_data );
	
	if ( $result !== false ) {
		echo '<p>✅ Direct save successful!</p>';
		
		// Verify the save
		$saved_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		if ( ! empty( $saved_data ) ) {
			echo '<p>✅ Data verified in database:</p>';
			echo '<pre style="background: #fff; padding: 10px; border: 1px solid #ddd;">';
			print_r( $saved_data );
			echo '</pre>';
		} else {
			echo '<p>❌ Data not found in database after save</p>';
		}
	} else {
		echo '<p>❌ Direct save failed</p>';
	}
	
	echo '</div>';
} else {
	echo '<form method="post" style="background: #fff; padding: 20px; border: 1px solid #ddd; margin: 10px 0;">';
	echo '<input type="hidden" name="test_direct_save" value="1">';
	echo '<p>Test direct save to database with glucose=98:</p>';
	echo '<button type="submit" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">💾 Test Direct Save</button>';
	echo '</form>';
}

// Test 4: Profile page form test
echo '<h2>4. Profile Page Form Test</h2>';
echo '<p>To test the actual profile page form:</p>';
echo '<ol>';
echo '<li><strong>Go to Profile Page:</strong> <a href="' . admin_url( 'profile.php' ) . '" target="_blank">' . admin_url( 'profile.php' ) . '</a></li>';
echo '<li><strong>Scroll down</strong> to find the "Biomarkers" section</li>';
echo '<li><strong>Enter 98 for glucose</strong> and any other values</li>';
echo '<li><strong>Click "Update Profile"</strong> at the bottom</li>';
echo '<li><strong>Check this page again</strong> to see if data was saved</li>';
echo '</ol>';

echo '<p><strong>Debug Instructions:</strong></p>';
echo '<ol>';
echo '<li>Open browser developer tools (F12)</li>';
echo '<li>Go to Network tab</li>';
echo '<li>Submit the form</li>';
echo '<li>Look for the POST request to profile.php</li>';
echo '<li>Check if biomarker_value data is being sent</li>';
echo '</ol>';

echo '</div>'; 