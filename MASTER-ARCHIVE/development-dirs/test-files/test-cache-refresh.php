<?php
/**
 * ENNU Life Cache Refresh Test
 *
 * This script forces a cache refresh and tests the save function directly
 *
 * @package ENNU_Life
 * @version 57.2.5
 */

// Load WordPress
require_once '../../../wp-load.php';

// Check permissions
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Insufficient permissions to run this test.' );
}

echo '<!DOCTYPE html>';
echo '<html><head><title>ENNU Cache Refresh Test</title>';
echo '<style>';
echo 'body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; }';
echo '.success { color: green; }';
echo '.error { color: red; }';
echo '.section { background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 5px; }';
echo '</style>';
echo '</head><body>';

echo '<h1>ENNU Life Cache Refresh Test</h1>';

// Force cache refresh
echo '<div class="section">';
echo '<h2>1. Cache Refresh</h2>';

// Clear WordPress object cache
if ( function_exists( 'wp_cache_flush' ) ) {
	wp_cache_flush();
	echo '<p class="success">✅ WordPress object cache cleared</p>';
}

// Clear any transients
if ( function_exists( 'delete_transient' ) ) {
	// Clear any potential transients
	delete_transient( 'ennu_admin_cache' );
	echo '<p class="success">✅ Transients cleared</p>';
}

// Force PHP to reload the file
if ( function_exists( 'opcache_reset' ) ) {
	opcache_reset();
	echo '<p class="success">✅ PHP OPcache reset</p>';
}

echo '</div>';

// Test the save function directly
echo '<div class="section">';
echo '<h2>2. Direct Save Function Test</h2>';

$current_user_id = get_current_user_id();
$plugin          = ENNU_Life_Enhanced_Plugin::get_instance();
$admin           = $plugin->get_admin();

if ( $admin ) {
	echo '<p class="success">✅ Admin class loaded after cache refresh</p>';

	// Test direct save
	$test_data = array(
		'ennu_global_gender'            => 'female',
		'ennu_global_user_dob_combined' => '1985-06-15',
		'ennu_global_health_goals'      => array( 'weight_loss', 'energy', 'strength' ),
		'ennu_global_height_weight'     => array(
			'ft'     => '5',
			'in'     => '8',
			'weight' => '140',
		),
	);

	foreach ( $test_data as $key => $value ) {
		$result = update_user_meta( $current_user_id, $key, $value );
		echo '<p class="' . ( $result ? 'success' : 'error' ) . '">' .
			 ( $result ? '✅' : '❌' ) . ' Direct save ' . $key . ': ' . print_r( $value, true ) . '</p>';
	}

	// Verify saved data
	echo '<h3>Verification After Cache Refresh:</h3>';
	foreach ( $test_data as $key => $expected_value ) {
		$saved_value = get_user_meta( $current_user_id, $key, true );
		$matches     = ( $saved_value == $expected_value );
		echo '<p class="' . ( $matches ? 'success' : 'error' ) . '">' .
			 ( $matches ? '✅' : '❌' ) . ' ' . $key . ': Expected ' . print_r( $expected_value, true ) . ', Got ' . print_r( $saved_value, true ) . '</p>';
	}
} else {
	echo '<p class="error">❌ Admin class not found after cache refresh</p>';
}

echo '</div>';

// Test form submission simulation
echo '<div class="section">';
echo '<h2>3. Form Submission Simulation</h2>';

// Simulate POST data
$_POST['ennu_assessment_nonce']         = wp_create_nonce( 'ennu_user_profile_update_' . $current_user_id );
$_POST['ennu_global_gender']            = 'male';
$_POST['ennu_global_user_dob_combined'] = '1990-01-01';
$_POST['ennu_global_health_goals']      = array( 'longevity', 'energy' );
$_POST['ennu_global_height_weight']     = array(
	'ft'     => '6',
	'in'     => '0',
	'weight' => '180',
);

echo '<p>Simulating form submission with POST data...</p>';

// Call the save function directly
if ( $admin && method_exists( $admin, 'save_user_assessment_fields' ) ) {
	$admin->save_user_assessment_fields( $current_user_id );
	echo '<p class="success">✅ Save function called successfully</p>';

	// Verify the data was saved
	echo '<h3>Verification After Form Simulation:</h3>';
	$form_data = array(
		'ennu_global_gender'            => 'male',
		'ennu_global_user_dob_combined' => '1990-01-01',
		'ennu_global_health_goals'      => array( 'longevity', 'energy' ),
		'ennu_global_height_weight'     => array(
			'ft'     => '6',
			'in'     => '0',
			'weight' => '180',
		),
	);

	foreach ( $form_data as $key => $expected_value ) {
		$saved_value = get_user_meta( $current_user_id, $key, true );
		$matches     = ( $saved_value == $expected_value );
		echo '<p class="' . ( $matches ? 'success' : 'error' ) . '">' .
			 ( $matches ? '✅' : '❌' ) . ' ' . $key . ': Expected ' . print_r( $expected_value, true ) . ', Got ' . print_r( $saved_value, true ) . '</p>';
	}
} else {
	echo '<p class="error">❌ Could not call save function</p>';
}

echo '</div>';

// Instructions
echo '<div class="section">';
echo '<h2>4. Next Steps</h2>';
echo '<p><strong>After running this test:</strong></p>';
echo '<ol>';
echo '<li>Go to <a href="' . admin_url( 'profile.php' ) . '" target="_blank">Your Profile Page</a></li>';
echo '<li>The fields should now show the test data we just saved</li>';
echo '<li>Try filling in different values and saving</li>';
echo '<li>Refresh the page to verify persistence</li>';
echo '</ol>';
echo '</div>';

echo '</body></html>';


