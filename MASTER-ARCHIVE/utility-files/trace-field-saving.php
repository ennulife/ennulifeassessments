<?php
/**
 * ENNU Life Assessment Field Saving Trace
 * 
 * This script traces the complete flow from form submission to data saving
 * to identify why assessment fields are not being saved properly.
 * 
 * @package ENNU_Life_Assessments
 * @version 64.4.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Only run if user is admin
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Access denied' );
}

// Load WordPress
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

echo '<h1>ENNU Life Assessment Field Saving Trace</h1>';
echo '<p>This script traces the complete flow to identify field saving issues.</p>';

// 1. Check if plugin is loaded
echo '<h2>1. Plugin Loading Check</h2>';
if ( class_exists( 'ENNU_Life_Enhanced_Plugin' ) ) {
	echo '<p style="color: green;">✓ Plugin class loaded successfully</p>';
	$plugin = ENNU_Life_Enhanced_Plugin::get_instance();
} else {
	echo '<p style="color: red;">✗ Plugin class not found</p>';
	exit;
}

// 2. Check assessment shortcodes
echo '<h2>2. Assessment Shortcodes Check</h2>';
$shortcodes = $plugin->get_shortcode_handler();
if ( $shortcodes ) {
	echo '<p style="color: green;">✓ Shortcode handler loaded</p>';
} else {
	echo '<p style="color: red;">✗ Shortcode handler not found</p>';
	exit;
}

// 3. Get all assessment definitions
echo '<h2>3. Assessment Definitions</h2>';
$all_definitions = $shortcodes->get_all_assessment_definitions();
echo '<p>Total assessments found: ' . count( $all_definitions ) . '</p>';

foreach ( $all_definitions as $assessment_type => $config ) {
	echo '<h3>Assessment: ' . esc_html( $assessment_type ) . '</h3>';
	echo '<p>Title: ' . esc_html( $config['title'] ?? 'No title' ) . '</p>';
	echo '<p>Engine: ' . esc_html( $config['assessment_engine'] ?? 'Not specified' ) . '</p>';
	
	if ( isset( $config['questions'] ) ) {
		echo '<p>Questions: ' . count( $config['questions'] ) . '</p>';
		echo '<ul>';
		foreach ( $config['questions'] as $question_id => $question_def ) {
			$global_key = isset( $question_def['global_key'] ) ? ' (Global: ' . $question_def['global_key'] . ')' : '';
			echo '<li><strong>' . esc_html( $question_id ) . '</strong> - Type: ' . esc_html( $question_def['type'] ?? 'unknown' ) . $global_key . '</li>';
		}
		echo '</ul>';
	}
}

// 4. Test form data structure
echo '<h2>4. Form Data Structure Analysis</h2>';
echo '<p>This shows what field names are expected vs what might be submitted:</p>';

foreach ( $all_definitions as $assessment_type => $config ) {
	echo '<h3>Assessment: ' . esc_html( $assessment_type ) . '</h3>';
	
	if ( isset( $config['questions'] ) ) {
		echo '<table border="1" style="border-collapse: collapse; width: 100%; margin-bottom: 20px;">';
		echo '<tr><th>Question ID</th><th>Type</th><th>Expected Form Field</th><th>Global Key</th><th>Meta Key</th></tr>';
		
		foreach ( $config['questions'] as $question_id => $question_def ) {
			$type = $question_def['type'] ?? 'unknown';
			$global_key = $question_def['global_key'] ?? '';
			
			// Determine expected form field name
			$expected_field = $question_id;
			if ( $type === 'multiselect' ) {
				$expected_field = $question_id . '[]';
			}
			
			// Determine meta key
			$meta_key = '';
			if ( ! empty( $global_key ) ) {
				$meta_key = 'ennu_global_' . $global_key;
			} else {
				$meta_key = 'ennu_' . $assessment_type . '_' . $question_id;
			}
			
			echo '<tr>';
			echo '<td>' . esc_html( $question_id ) . '</td>';
			echo '<td>' . esc_html( $type ) . '</td>';
			echo '<td>' . esc_html( $expected_field ) . '</td>';
			echo '<td>' . esc_html( $global_key ) . '</td>';
			echo '<td>' . esc_html( $meta_key ) . '</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
}

// 5. Check existing user data
echo '<h2>5. Existing User Data Check</h2>';
$current_user = wp_get_current_user();
if ( $current_user->ID ) {
	echo '<p>Current user ID: ' . $current_user->ID . '</p>';
	echo '<p>Current user email: ' . $current_user->user_email . '</p>';
	
	// Get all user meta
	$user_meta = get_user_meta( $current_user->ID );
	echo '<h3>Existing User Meta:</h3>';
	echo '<ul>';
	foreach ( $user_meta as $meta_key => $meta_values ) {
		if ( strpos( $meta_key, 'ennu_' ) === 0 ) {
			$value = is_array( $meta_values ) ? $meta_values[0] : $meta_values;
			echo '<li><strong>' . esc_html( $meta_key ) . '</strong>: ' . esc_html( $value ) . '</li>';
		}
	}
	echo '</ul>';
} else {
	echo '<p style="color: orange;">No user logged in</p>';
}

// 6. Test form rendering
echo '<h2>6. Form Field Rendering Test</h2>';
echo '<p>This shows how fields are actually rendered in HTML:</p>';

foreach ( $all_definitions as $assessment_type => $config ) {
	echo '<h3>Assessment: ' . esc_html( $assessment_type ) . '</h3>';
	
	if ( isset( $config['questions'] ) ) {
		foreach ( $config['questions'] as $question_id => $question_def ) {
			echo '<h4>Question: ' . esc_html( $question_id ) . '</h4>';
			echo '<p>Type: ' . esc_html( $question_def['type'] ?? 'unknown' ) . '</p>';
			
			// Simulate field rendering
			$type = $question_def['type'] ?? 'radio';
			$name = $question_id;
			
			if ( $type === 'multiselect' ) {
				$name = $question_id . '[]';
			}
			
			echo '<p><strong>Expected HTML field name:</strong> <code>' . esc_html( $name ) . '</code></p>';
			
			// Show what the form handler expects
			if ( ! empty( $question_def['global_key'] ) ) {
				echo '<p><strong>Global field processing:</strong> Will look for <code>' . esc_html( $question_id ) . '</code> in form data</p>';
			} else {
				echo '<p><strong>Assessment field processing:</strong> Will look for <code>' . esc_html( $question_id ) . '</code> in form data</p>';
			}
		}
	}
}

// 7. Check form handler logic
echo '<h2>7. Form Handler Logic Analysis</h2>';
echo '<p>Key points in the form processing:</p>';
echo '<ul>';
echo '<li><strong>Form data sanitization:</strong> Uses ENNU_Input_Sanitizer::sanitize_form_data()</li>';
echo '<li><strong>Global fields:</strong> Processed in save_global_fields_unified()</li>';
echo '<li><strong>Assessment fields:</strong> Processed in save_assessment_specific_fields_unified()</li>';
echo '<li><strong>Field lookup:</strong> Uses question_id as the key in form_data array</li>';
echo '</ul>';

// 8. Potential issues identified
echo '<h2>8. Potential Issues Identified</h2>';
echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 10px 0;">';
echo '<h3>Common Field Saving Issues:</h3>';
echo '<ol>';
echo '<li><strong>Field name mismatch:</strong> HTML form field names must exactly match question_id in config</li>';
echo '<li><strong>Array vs string:</strong> Multiselect fields use array notation (field[]) but processing expects field name without []</li>';
echo '<li><strong>Global vs assessment fields:</strong> Fields with global_key are processed differently</li>';
echo '<li><strong>Special field types:</strong> DOB, height/weight, and contact fields have custom processing</li>';
echo '<li><strong>Form data structure:</strong> Form data must include assessment_type key</li>';
echo '</ol>';
echo '</div>';

// 9. Debug recommendations
echo '<h2>9. Debug Recommendations</h2>';
echo '<div style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; margin: 10px 0;">';
echo '<h3>To debug field saving issues:</h3>';
echo '<ol>';
echo '<li>Enable WP_DEBUG and check error logs</li>';
echo '<li>Add logging to handle_assessment_submission() method</li>';
echo '<li>Check the _log_submission_debug() output</li>';
echo '<li>Verify form data structure before processing</li>';
echo '<li>Test with a simple assessment first (like welcome)</li>';
echo '<li>Check if fields are being skipped due to global_key logic</li>';
echo '</ol>';
echo '</div>';

// 10. Test form submission simulation
echo '<h2>10. Form Submission Simulation</h2>';
echo '<p>This shows what a typical form submission would look like:</p>';

foreach ( $all_definitions as $assessment_type => $config ) {
	echo '<h3>Assessment: ' . esc_html( $assessment_type ) . '</h3>';
	
	$sample_form_data = array(
		'assessment_type' => $assessment_type,
		'action' => 'ennu_submit_assessment',
		'nonce' => 'test_nonce'
	);
	
	if ( isset( $config['questions'] ) ) {
		foreach ( $config['questions'] as $question_id => $question_def ) {
			$type = $question_def['type'] ?? 'radio';
			
			switch ( $type ) {
				case 'multiselect':
					$sample_form_data[ $question_id ] = array( 'option1', 'option2' );
					break;
				case 'radio':
					$sample_form_data[ $question_id ] = 'option1';
					break;
				case 'dob_dropdowns':
					$sample_form_data['ennu_global_date_of_birth'] = '1990-01-01';
					break;
				case 'height_weight':
					$sample_form_data['height_ft'] = '5';
					$sample_form_data['height_in'] = '10';
					$sample_form_data['weight_lbs'] = '150';
					break;
				default:
					$sample_form_data[ $question_id ] = 'sample_value';
					break;
			}
		}
	}
	
	echo '<pre style="background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6;">';
	echo esc_html( json_encode( $sample_form_data, JSON_PRETTY_PRINT ) );
	echo '</pre>';
}

echo '<h2>Trace Complete</h2>';
echo '<p>This trace has identified the key areas to check for field saving issues. The most common problems are:</p>';
echo '<ul>';
echo '<li>Field name mismatches between HTML form and processing logic</li>';
echo '<li>Incorrect handling of special field types (multiselect, DOB, etc.)</li>';
echo '<li>Global vs assessment-specific field processing confusion</li>';
echo '<li>Form data structure issues</li>';
echo '</ul>';
echo '<p>Check the debug logs and form submission data to identify the specific issue.</p>';
?> 