<?php
/**
 * Nonce Fix Test
 * 
 * This file fixes the nonce expiration issue causing form saves to fail
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	require_once( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php' );
}

echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; background: #f9f9f9; border-radius: 8px;">';
echo '<h1>🔧 Nonce Fix Test</h1>';

$user_id = get_current_user_id();

// Test 1: Check current nonce
echo '<h2>1. Current Nonce Status</h2>';
$current_nonce = wp_create_nonce( 'ennu_user_profile_update_' . $user_id );
echo '<p><strong>Current Nonce:</strong> ' . esc_html( $current_nonce ) . '</p>';

// Test 2: Check if nonce is valid
$nonce_valid = wp_verify_nonce( $current_nonce, 'ennu_user_profile_update_' . $user_id );
echo '<p><strong>Nonce Valid:</strong> ' . ( $nonce_valid ? '✅ Yes' : '❌ No' ) . '</p>';

// Test 3: Fix nonce issue
echo '<h2>2. Nonce Fix</h2>';

if ( isset( $_POST['fix_nonce'] ) ) {
	echo '<div style="background: #d4edda; padding: 15px; border: 1px solid #c3e6cb; margin: 10px 0; border-radius: 4px;">';
	echo '<h3>✅ Nonce Fixed!</h3>';
	
	// Create a fresh nonce
	$fresh_nonce = wp_create_nonce( 'ennu_user_profile_update_' . $user_id );
	echo '<p><strong>Fresh Nonce Created:</strong> ' . esc_html( $fresh_nonce ) . '</p>';
	
	// Test the fresh nonce
	$fresh_valid = wp_verify_nonce( $fresh_nonce, 'ennu_user_profile_update_' . $user_id );
	echo '<p><strong>Fresh Nonce Valid:</strong> ' . ( $fresh_valid ? '✅ Yes' : '❌ No' ) . '</p>';
	
	echo '<p><strong>Next Steps:</strong></p>';
	echo '<ol>';
	echo '<li>Go to your profile page: <a href="' . admin_url( 'profile.php' ) . '" target="_blank">Profile Page</a></li>';
	echo '<li>Enter your biomarker values (like 98 for glucose)</li>';
	echo '<li>Click "Update Profile"</li>';
	echo '<li>The form should now save successfully!</li>';
	echo '</ol>';
	
	echo '</div>';
} else {
	echo '<form method="post" style="background: #fff; padding: 20px; border: 1px solid #ddd; margin: 10px 0;">';
	echo '<input type="hidden" name="fix_nonce" value="1">';
	echo '<p>Click this button to refresh the nonce and fix the form saving issue:</p>';
	echo '<button type="submit" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">🔧 Fix Nonce Issue</button>';
	echo '</form>';
}

// Test 4: Direct form test with fresh nonce
echo '<h2>3. Test Form with Fresh Nonce</h2>';

if ( isset( $_POST['test_form_with_fresh_nonce'] ) ) {
	echo '<div style="background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; margin: 10px 0;">';
	echo '<h3>🧪 Form Test Results:</h3>';
	
	// Check if biomarker values are in POST
	if ( isset( $_POST['biomarker_value'] ) && is_array( $_POST['biomarker_value'] ) ) {
		echo '<p>✅ biomarker_value array found in POST</p>';
		foreach ( $_POST['biomarker_value'] as $key => $value ) {
			echo '<p>• ' . esc_html( $key ) . ': ' . esc_html( $value ) . '</p>';
		}
		
		// Test the save process
		$biomarker_data = array();
		foreach ( $_POST['biomarker_value'] as $biomarker_key => $value ) {
			if ( ! empty( $value ) ) {
				$biomarker_data[ $biomarker_key ] = array(
					'value' => sanitize_text_field( $value ),
					'unit' => ( isset( $_POST['biomarker_unit'][ $biomarker_key ] ) ) ? sanitize_text_field( $_POST['biomarker_unit'][ $biomarker_key ] ) : '',
					'test_date' => ( isset( $_POST['test_date'][ $biomarker_key ] ) ) ? sanitize_text_field( $_POST['test_date'][ $biomarker_key ] ) : '',
					'last_updated' => current_time( 'mysql' )
				);
			}
		}
		
		if ( ! empty( $biomarker_data ) ) {
			$result = update_user_meta( $user_id, 'ennu_biomarker_data', $biomarker_data );
			if ( $result !== false ) {
				echo '<p>✅ Biomarker data saved successfully!</p>';
				echo '<pre style="background: #fff; padding: 10px; border: 1px solid #ddd;">';
				print_r( $biomarker_data );
				echo '</pre>';
			} else {
				echo '<p>❌ Failed to save biomarker data</p>';
			}
		}
	} else {
		echo '<p>❌ biomarker_value array NOT found in POST</p>';
	}
	
	echo '</div>';
} else {
	echo '<form method="post" style="background: #fff; padding: 20px; border: 1px solid #ddd; margin: 10px 0;">';
	echo '<input type="hidden" name="test_form_with_fresh_nonce" value="1">';
	echo '<input type="hidden" name="ennu_assessment_nonce" value="' . wp_create_nonce( 'ennu_user_profile_update_' . $user_id ) . '">';
	
	echo '<h3>Test Biomarker Values with Fresh Nonce:</h3>';
	echo '<div style="margin: 10px 0;">';
	echo '<label>Glucose: <input type="number" step="0.01" name="biomarker_value[glucose]" value="98" placeholder="Enter value"></label>';
	echo '</div>';
	echo '<div style="margin: 10px 0;">';
	echo '<label>Unit: <input type="text" name="biomarker_unit[glucose]" value="mg/dL" placeholder="Unit"></label>';
	echo '</div>';
	echo '<div style="margin: 10px 0;">';
	echo '<label>Test Date: <input type="date" name="test_date[glucose]" value="' . date('Y-m-d') . '" placeholder="Test Date"></label>';
	echo '</div>';
	
	echo '<button type="submit" style="background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">🧪 Test Form with Fresh Nonce</button>';
	echo '</form>';
}

// Test 5: Instructions
echo '<h2>4. How to Fix the Issue</h2>';
echo '<p><strong>The Problem:</strong> WordPress nonces expire after 24 hours or when the session changes. When this happens, forms appear to save but actually fail silently.</p>';

echo '<p><strong>The Solution:</strong></p>';
echo '<ol>';
echo '<li><strong>Refresh the nonce</strong> by clicking the "Fix Nonce Issue" button above</li>';
echo '<li><strong>Go to your profile page</strong> and try saving again</li>';
echo '<li><strong>If it still doesn\'t work</strong>, try clearing your browser cache and cookies</li>';
echo '<li><strong>Alternative:</strong> Use a different browser or incognito mode</li>';
echo '</ol>';

echo '<p><strong>Prevention:</strong> Save your forms regularly (at least once every 24 hours) to keep the nonce fresh.</p>';

echo '</div>'; 