<?php
/**
 * Test Form Submission Debug
 * 
 * This file helps debug the form submission issue where biomarker data
 * appears blank after saving.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	require_once( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php' );
}

// Check if user is logged in and has admin privileges
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Access denied' );
}

echo '<div style="font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f9f9f9; border-radius: 8px;">';
echo '<h1>🔍 ENNU Form Submission Debug</h1>';

// Test 1: Check if the save method is being called
echo '<h2>1. Save Method Test</h2>';
echo '<p>Let\'s check if the save method is being called when you submit the form:</p>';

// Add a test action to trigger the save method
add_action( 'personal_options_update', function( $user_id ) {
	error_log( 'ENNU Debug: personal_options_update triggered for user ID: ' . $user_id );
	error_log( 'ENNU Debug: POST data: ' . print_r( $_POST, true ) );
}, 1 );

add_action( 'edit_user_profile_update', function( $user_id ) {
	error_log( 'ENNU Debug: edit_user_profile_update triggered for user ID: ' . $user_id );
	error_log( 'ENNU Debug: POST data: ' . print_r( $_POST, true ) );
}, 1 );

echo '<p>✅ Debug hooks added. Check the debug log after form submission.</p>';

// Test 2: Check current biomarker data
echo '<h2>2. Current Biomarker Data</h2>';
$user_id = get_current_user_id();
$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );

if ( ! empty( $biomarker_data ) ) {
	echo '<p>✅ Biomarker data found for user ID ' . $user_id . ':</p>';
	echo '<pre style="background: #fff; padding: 10px; border: 1px solid #ddd; overflow-x: auto;">';
	print_r( $biomarker_data );
	echo '</pre>';
} else {
	echo '<p>❌ No biomarker data found for user ID ' . $user_id . '</p>';
}

// Test 3: Check if the form fields are being generated correctly
echo '<h2>3. Form Field Generation Test</h2>';
echo '<p>Let\'s check if the biomarker form fields are being generated correctly:</p>';

if ( class_exists( 'ENNU_Enhanced_Admin' ) ) {
	echo '<p>✅ ENNU_Enhanced_Admin class found</p>';
	echo '<p>✅ Form fields should be generated on the profile page</p>';
} else {
	echo '<p>❌ ENNU_Enhanced_Admin class not found</p>';
}

// Test 4: Check WordPress profile form structure
echo '<h2>4. WordPress Profile Form Structure</h2>';
echo '<p>Let\'s verify the WordPress profile form structure:</p>';

echo '<div style="background: #fff; padding: 20px; border: 1px solid #ddd; margin: 10px 0;">';
echo '<h3>Expected Form Structure:</h3>';
echo '<ul>';
echo '<li><strong>Form ID:</strong> your-profile</li>';
echo '<li><strong>Action:</strong> ' . admin_url( 'profile.php' ) . '</li>';
echo '<li><strong>Method:</strong> POST</li>';
echo '<li><strong>Nonce Field:</strong> ennu_assessment_nonce</li>';
echo '<li><strong>Biomarker Fields:</strong> biomarker_value[biomarker_key]</li>';
echo '</ul>';
echo '</div>';

// Test 5: Instructions for testing
echo '<h2>5. Testing Instructions</h2>';
echo '<div style="background: #e8f5e8; padding: 20px; border-left: 4px solid #4caf50; margin: 10px 0;">';
echo '<h3>🔍 How to Test:</h3>';
echo '<ol>';
echo '<li><strong>Go to Profile Page:</strong> Navigate to <code>/wp-admin/profile.php</code></li>';
echo '<li><strong>Find Biomarker Section:</strong> Scroll down to the "Biomarkers" tab</li>';
echo '<li><strong>Enter Test Data:</strong> Enter a value in any biomarker field (e.g., "Test Value")</li>';
echo '<li><strong>Submit Form:</strong> Click "Update Profile"</li>';
echo '<li><strong>Check Debug Log:</strong> Look for debug messages in <code>wp-content/debug.log</code></li>';
echo '<li><strong>Verify Data:</strong> Check if the data persists after page refresh</li>';
echo '</ol>';
echo '</div>';

// Test 6: Debug log monitoring
echo '<h2>6. Debug Log Monitoring</h2>';
echo '<p>Recent debug log entries:</p>';

$debug_log = WP_CONTENT_DIR . '/debug.log';
if ( file_exists( $debug_log ) ) {
	$log_entries = file( $debug_log );
	$recent_entries = array_slice( $log_entries, -20 ); // Last 20 entries
	
	echo '<div style="background: #000; color: #0f0; padding: 10px; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto;">';
	foreach ( $recent_entries as $entry ) {
		if ( strpos( $entry, 'ENNU' ) !== false ) {
			echo esc_html( $entry );
		}
	}
	echo '</div>';
} else {
	echo '<p>❌ Debug log not found at: ' . $debug_log . '</p>';
}

// Test 7: Form submission simulation
echo '<h2>7. Form Submission Simulation</h2>';
echo '<p>Let\'s simulate a form submission to test the save method:</p>';

if ( isset( $_POST['test_simulation'] ) ) {
	echo '<div style="background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7; margin: 10px 0;">';
	echo '<h3>🧪 Simulation Results:</h3>';
	
	// Simulate POST data
	$_POST['biomarker_value'] = array(
		'test_biomarker' => '123.45'
	);
	$_POST['biomarker_unit'] = array(
		'test_biomarker' => 'mg/dL'
	);
	$_POST['ennu_assessment_nonce'] = wp_create_nonce( 'ennu_user_profile_update_' . $user_id );
	
	// Call the save method
	if ( class_exists( 'ENNU_Enhanced_Admin' ) ) {
		$admin = new ENNU_Enhanced_Admin();
		$admin->save_user_assessment_fields( $user_id );
		
		// Check if data was saved
		$saved_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		if ( ! empty( $saved_data ) && isset( $saved_data['test_biomarker'] ) ) {
			echo '<p>✅ Simulation successful! Data saved: ' . print_r( $saved_data['test_biomarker'], true ) . '</p>';
		} else {
			echo '<p>❌ Simulation failed! No data saved.</p>';
		}
	}
	
	echo '</div>';
} else {
	echo '<form method="post" style="background: #fff; padding: 20px; border: 1px solid #ddd; margin: 10px 0;">';
	echo '<input type="hidden" name="test_simulation" value="1">';
	echo '<button type="submit" style="background: #0073aa; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">🧪 Run Simulation</button>';
	echo '</form>';
}

echo '</div>';
?> 