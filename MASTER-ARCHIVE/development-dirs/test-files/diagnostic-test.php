<?php
/**
 * Diagnostic Test for Form Submission Issues
 * 
 * This file helps diagnose why biomarker data is not saving
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	require_once( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php' );
}

echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; background: #f9f9f9; border-radius: 8px;">';
echo '<h1>🔍 Diagnostic Test for Form Submission</h1>';

$user_id = get_current_user_id();

// Test 1: Check if user is logged in
echo '<h2>1. User Authentication</h2>';
if ( $user_id > 0 ) {
	echo '<p>✅ User is logged in (ID: ' . $user_id . ')</p>';
} else {
	echo '<p>❌ User is not logged in</p>';
	echo '<p><a href="' . wp_login_url() . '">Login here</a></p>';
	echo '</div>';
	exit;
}

// Test 2: Check if admin class exists
echo '<h2>2. Admin Class Check</h2>';
if ( class_exists( 'ENNU_Enhanced_Admin' ) ) {
	echo '<p>✅ ENNU_Enhanced_Admin class exists</p>';
} else {
	echo '<p>❌ ENNU_Enhanced_Admin class not found</p>';
	echo '</div>';
	exit;
}

// Test 3: Check current biomarker data
echo '<h2>3. Current Biomarker Data</h2>';
$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
if ( ! empty( $biomarker_data ) ) {
	echo '<p>✅ Current biomarker data found:</p>';
	echo '<pre style="background: #fff; padding: 10px; border: 1px solid #ddd; max-height: 200px; overflow-y: auto;">';
	print_r( $biomarker_data );
	echo '</pre>';
} else {
	echo '<p>❌ No current biomarker data found</p>';
}

// Test 4: Check if hooks are registered
echo '<h2>4. Hook Registration Check</h2>';
global $wp_filter;
$personal_hooks = isset( $wp_filter['personal_options_update'] ) ? $wp_filter['personal_options_update'] : null;
$edit_hooks = isset( $wp_filter['edit_user_profile_update'] ) ? $wp_filter['edit_user_profile_update'] : null;

if ( $personal_hooks && ! empty( $personal_hooks->callbacks ) ) {
	echo '<p>✅ personal_options_update hooks registered</p>';
	foreach ( $personal_hooks->callbacks as $priority => $callbacks ) {
		foreach ( $callbacks as $callback ) {
			if ( is_array( $callback['function'] ) && is_object( $callback['function'][0] ) ) {
				$class_name = get_class( $callback['function'][0] );
				if ( strpos( $class_name, 'ENNU' ) !== false ) {
					echo '<p>✅ Found ENNU hook: ' . $class_name . '::' . $callback['function'][1] . '</p>';
				}
			}
		}
	}
} else {
	echo '<p>❌ No personal_options_update hooks found</p>';
}

if ( $edit_hooks && ! empty( $edit_hooks->callbacks ) ) {
	echo '<p>✅ edit_user_profile_update hooks registered</p>';
} else {
	echo '<p>❌ No edit_user_profile_update hooks found</p>';
}

// Test 5: Test direct save method
echo '<h2>5. Direct Save Method Test</h2>';

if ( isset( $_POST['test_direct_save'] ) ) {
	echo '<div style="background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; margin: 10px 0;">';
	echo '<h3>🧪 Direct Save Test Results:</h3>';
	
	// Create test data
	$test_data = array(
		'glucose' => array(
			'value' => '95',
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
	
	// Try to save directly
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
	echo '<p>Test direct save to database:</p>';
	echo '<button type="submit" style="background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">🔧 Test Direct Save</button>';
	echo '</form>';
}

// Test 6: Check form submission simulation
echo '<h2>6. Form Submission Simulation</h2>';

if ( isset( $_POST['test_form_simulation'] ) ) {
	echo '<div style="background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; margin: 10px 0;">';
	echo '<h3>🧪 Form Simulation Results:</h3>';
	
	// Simulate POST data
	$_POST['biomarker_value'] = array(
		'glucose' => '95',
		'hba1c' => '5.2'
	);
	$_POST['biomarker_unit'] = array(
		'glucose' => 'mg/dL',
		'hba1c' => '%'
	);
	$_POST['test_date'] = array(
		'glucose' => '2025-01-24',
		'hba1c' => '2025-01-24'
	);
	
	// Call the save method
	$admin = new ENNU_Enhanced_Admin();
	$admin->save_user_assessment_fields( $user_id );
	
	// Check result
	$saved_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
	if ( ! empty( $saved_data ) ) {
		echo '<p>✅ Form simulation successful!</p>';
		echo '<pre style="background: #fff; padding: 10px; border: 1px solid #ddd;">';
		print_r( $saved_data );
		echo '</pre>';
	} else {
		echo '<p>❌ Form simulation failed - no data saved</p>';
	}
	
	echo '</div>';
} else {
	echo '<form method="post" style="background: #fff; padding: 20px; border: 1px solid #ddd; margin: 10px 0;">';
	echo '<input type="hidden" name="test_form_simulation" value="1">';
	echo '<p>Test form submission simulation:</p>';
	echo '<button type="submit" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">📝 Test Form Simulation</button>';
	echo '</form>';
}

// Test 7: Check profile page access
echo '<h2>7. Profile Page Access</h2>';
echo '<p>To test the actual form submission:</p>';
echo '<ol>';
echo '<li><strong>Go to Profile Page:</strong> <a href="' . admin_url( 'profile.php' ) . '" target="_blank">' . admin_url( 'profile.php' ) . '</a></li>';
echo '<li><strong>Scroll down</strong> to find the "Biomarkers" section</li>';
echo '<li><strong>Enter test values</strong> in any biomarker fields</li>';
echo '<li><strong>Click "Update Profile"</strong> at the bottom</li>';
echo '<li><strong>Check this page again</strong> to see if data was saved</li>';
echo '</ol>';

echo '</div>'; 