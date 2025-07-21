<?php
/**
 * Test Global Fields Debug Script
 *
 * This script tests the global field saving functionality to identify why
 * gender and other global fields are not being saved correctly.
 */

// Load WordPress
require_once '../../../wp-load.php';

echo "<h1>ENNU Global Fields Debug Test</h1>\n";

// Check if shortcode handler exists
if ( ! class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
	echo "<p style='color: red;'>ERROR: ENNU_Assessment_Shortcodes class not found!</p>\n";
	exit;
}

$shortcodes = new ENNU_Assessment_Shortcodes();

echo "<h2>Testing Global Fields Configuration</h2>\n";

// Test 1: Check if weight-loss assessment has global fields
$weight_loss_questions = $shortcodes->get_assessment_questions( 'weight-loss' );
echo "<h3>Weight Loss Assessment Questions:</h3>\n";
echo '<pre>' . print_r( $weight_loss_questions, true ) . "</pre>\n";

// Test 2: Check if global fields are properly identified
echo "<h3>Global Fields Found:</h3>\n";
$global_fields = array();
if ( isset( $weight_loss_questions['questions'] ) ) {
	foreach ( $weight_loss_questions['questions'] as $question_id => $question_def ) {
		if ( isset( $question_def['global_key'] ) ) {
			$global_fields[ $question_id ] = $question_def['global_key'];
			echo "<p>✅ <strong>$question_id</strong> → <strong>ennu_global_{$question_def['global_key']}</strong></p>\n";
		}
	}
}

if ( empty( $global_fields ) ) {
	echo "<p style='color: red;'>❌ No global fields found in weight-loss assessment!</p>\n";
} else {
	echo "<p style='color: green;'>✅ Found " . count( $global_fields ) . " global fields</p>\n";
}

// Test 3: Check current user meta for global fields
$user_id = 1; // admin user
echo "<h3>Current Global Fields in User Meta:</h3>\n";

$global_meta_keys = array(
	'ennu_global_gender',
	'ennu_global_height_weight',
	'ennu_global_first_name',
	'ennu_global_last_name',
	'ennu_global_email',
	'ennu_global_billing_phone',
);

foreach ( $global_meta_keys as $meta_key ) {
	$value = get_user_meta( $user_id, $meta_key, true );
	if ( ! empty( $value ) ) {
		echo "<p>✅ <strong>$meta_key</strong>: " . print_r( $value, true ) . "</p>\n";
	} else {
		echo "<p style='color: orange;'>⚠️ <strong>$meta_key</strong>: <em>empty</em></p>\n";
	}
}

// Test 4: Check assessment-specific fields that should be global
echo "<h3>Assessment-Specific Fields (Should Be Global):</h3>\n";
$assessment_meta_keys = array(
	'ennu_weight_loss_wl_q_gender',
	'ennu_weight_loss_wl_q1',
	'ennu_weight_loss_first_name',
	'ennu_weight_loss_last_name',
	'ennu_weight_loss_email',
);

foreach ( $assessment_meta_keys as $meta_key ) {
	$value = get_user_meta( $user_id, $meta_key, true );
	if ( ! empty( $value ) ) {
		echo "<p style='color: red;'>❌ <strong>$meta_key</strong>: " . print_r( $value, true ) . " (SHOULD BE GLOBAL!)</p>\n";
	} else {
		echo "<p>✅ <strong>$meta_key</strong>: <em>empty</em></p>\n";
	}
}

// Test 5: Test the save_global_meta method directly
echo "<h3>Testing save_global_meta Method:</h3>\n";

// Create test data that mimics the form submission
$test_data = array(
	'assessment_type' => 'weight-loss',
	'wl_q_gender'     => 'male',
	'height_ft'       => '5',
	'height_in'       => '10',
	'weight_lbs'      => '180',
	'first_name'      => 'Test',
	'last_name'       => 'User',
	'email'           => 'test@example.com',
	'billing_phone'   => '555-1234',
);

echo "<p>Test data:</p>\n";
echo '<pre>' . print_r( $test_data, true ) . "</pre>\n";

// Call the save_global_meta method
$shortcodes->save_global_meta( $user_id, $test_data );

echo "<p style='color: green;'>✅ Called save_global_meta method</p>\n";

// Check if global fields were saved
echo "<h3>Global Fields After Test Save:</h3>\n";
foreach ( $global_meta_keys as $meta_key ) {
	$value = get_user_meta( $user_id, $meta_key, true );
	if ( ! empty( $value ) ) {
		echo "<p style='color: green;'>✅ <strong>$meta_key</strong>: " . print_r( $value, true ) . "</p>\n";
	} else {
		echo "<p style='color: red;'>❌ <strong>$meta_key</strong>: <em>empty</em></p>\n";
	}
}

echo "<h2>Debug Complete!</h2>\n";
echo "<p>This test shows whether global fields are being saved correctly.</p>\n";


