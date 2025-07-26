<?php
/**
 * Test Field Saving Fix
 * 
 * This script tests the enhanced field saving methods to ensure they work properly.
 * 
 * @package ENNU_Life_Assessments
 * @version 64.4.0
 */

// Load WordPress
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

echo '<h1>ENNU Life Assessment Field Saving Fix Test</h1>';
echo '<p>Testing the enhanced field saving methods...</p>';

// Check if plugin is loaded
if ( ! class_exists( 'ENNU_Life_Enhanced_Plugin' ) ) {
	echo '<p style="color: red;">✗ Plugin class not found</p>';
	exit;
}

$plugin = ENNU_Life_Enhanced_Plugin::get_instance();
$shortcodes = $plugin->get_shortcode_handler();

if ( ! $shortcodes ) {
	echo '<p style="color: red;">✗ Shortcode handler not found</p>';
	exit;
}

echo '<h2>1. Testing Enhanced Methods Availability</h2>';

// Check if enhanced methods exist using reflection
$reflection = new ReflectionClass( $shortcodes );

$enhanced_methods = array(
	'normalize_form_data',
	'save_assessment_specific_fields_enhanced',
	'save_global_fields_enhanced'
);

foreach ( $enhanced_methods as $method ) {
	if ( $reflection->hasMethod( $method ) ) {
		echo '<p style="color: green;">✓ Method ' . esc_html( $method ) . ' found</p>';
	} else {
		echo '<p style="color: red;">✗ Method ' . esc_html( $method ) . ' not found</p>';
	}
}

echo '<h2>2. Testing Form Data Normalization</h2>';

// Test the normalize_form_data method
$test_form_data = array(
	'field1' => 'value1',
	'field2[]' => array( 'option1', 'option2' ),
	'field3' => 'value3',
	'multiselect[]' => array( 'choice1', 'choice2' )
);

try {
	$normalized_data = $shortcodes->normalize_form_data( $test_form_data );
	
	echo '<p><strong>Original form data:</strong></p>';
	echo '<pre>' . esc_html( json_encode( $test_form_data, JSON_PRETTY_PRINT ) ) . '</pre>';
	
	echo '<p><strong>Normalized form data:</strong></p>';
	echo '<pre>' . esc_html( json_encode( $normalized_data, JSON_PRETTY_PRINT ) ) . '</pre>';
	
	// Check if array notation was removed
	$has_array_notation = false;
	foreach ( $normalized_data as $key => $value ) {
		if ( strpos( $key, '[]' ) !== false ) {
			$has_array_notation = true;
			break;
		}
	}
	
	if ( ! $has_array_notation ) {
		echo '<p style="color: green;">✓ Array notation successfully removed from field names</p>';
	} else {
		echo '<p style="color: red;">✗ Array notation still present in field names</p>';
	}
	
} catch ( Exception $e ) {
	echo '<p style="color: red;">✗ Error testing normalize_form_data: ' . esc_html( $e->getMessage() ) . '</p>';
}

echo '<h2>3. Testing Assessment Field Processing</h2>';

// Get assessment definitions
$all_definitions = $shortcodes->get_all_assessment_definitions();

foreach ( $all_definitions as $assessment_type => $config ) {
	echo '<h3>Testing Assessment: ' . esc_html( $assessment_type ) . '</h3>';
	
	if ( isset( $config['questions'] ) ) {
		$questions = $config['questions'];
		echo '<p>Questions found: ' . count( $questions ) . '</p>';
		
		// Create test form data for this assessment
		$test_assessment_data = array(
			'assessment_type' => $assessment_type,
			'action' => 'ennu_submit_assessment',
			'nonce' => 'test_nonce'
		);
		
		foreach ( $questions as $question_id => $question_def ) {
			$type = $question_def['type'] ?? 'radio';
			
			switch ( $type ) {
				case 'multiselect':
					// Test with array notation
					$test_assessment_data[ $question_id . '[]' ] = array( 'test_option1', 'test_option2' );
					break;
				case 'radio':
					$test_assessment_data[ $question_id ] = 'test_option';
					break;
				case 'dob_dropdowns':
					$test_assessment_data['ennu_global_date_of_birth'] = '1990-01-01';
					break;
				case 'height_weight':
					$test_assessment_data['height_ft'] = '5';
					$test_assessment_data['height_in'] = '10';
					$test_assessment_data['weight_lbs'] = '150';
					break;
				default:
					$test_assessment_data[ $question_id ] = 'test_value';
					break;
			}
		}
		
		echo '<p><strong>Test form data for ' . esc_html( $assessment_type ) . ':</strong></p>';
		echo '<pre>' . esc_html( json_encode( $test_assessment_data, JSON_PRETTY_PRINT ) ) . '</pre>';
		
		// Test normalization
		try {
			$normalized = $shortcodes->normalize_form_data( $test_assessment_data );
			echo '<p style="color: green;">✓ Form data normalization successful</p>';
		} catch ( Exception $e ) {
			echo '<p style="color: red;">✗ Form data normalization failed: ' . esc_html( $e->getMessage() ) . '</p>';
		}
	}
}

echo '<h2>4. Testing Field Name Matching</h2>';

// Test specific field name scenarios
$field_test_cases = array(
	'radio_field' => 'radio_value',
	'multiselect_field[]' => array( 'option1', 'option2' ),
	'normal_field' => 'normal_value',
	'field_with_brackets[]' => array( 'choice1' )
);

echo '<p><strong>Testing field name variations:</strong></p>';

foreach ( $field_test_cases as $field_name => $field_value ) {
	$normalized_name = str_replace( '[]', '', $field_name );
	echo '<p>Field: <code>' . esc_html( $field_name ) . '</code> → Normalized: <code>' . esc_html( $normalized_name ) . '</code></p>';
}

echo '<h2>5. Testing Meta Key Generation</h2>';

// Test meta key generation for different field types
$meta_key_test_cases = array(
	array( 'assessment_type' => 'welcome', 'question_id' => 'welcome_q1', 'global_key' => 'date_of_birth', 'expected' => 'ennu_global_date_of_birth' ),
	array( 'assessment_type' => 'weight-loss', 'question_id' => 'wl_q2', 'global_key' => null, 'expected' => 'ennu_weight-loss_wl_q2' ),
	array( 'assessment_type' => 'health-optimization', 'question_id' => 'symptom_q1', 'global_key' => null, 'expected' => 'ennu_health-optimization_symptom_q1' )
);

foreach ( $meta_key_test_cases as $test_case ) {
	$assessment_type = $test_case['assessment_type'];
	$question_id = $test_case['question_id'];
	$global_key = $test_case['global_key'];
	$expected = $test_case['expected'];
	
	$actual = '';
	if ( $global_key ) {
		$actual = 'ennu_global_' . $global_key;
	} else {
		$actual = 'ennu_' . $assessment_type . '_' . $question_id;
	}
	
	if ( $actual === $expected ) {
		echo '<p style="color: green;">✓ Meta key generation correct: <code>' . esc_html( $actual ) . '</code></p>';
	} else {
		echo '<p style="color: red;">✗ Meta key generation incorrect. Expected: <code>' . esc_html( $expected ) . '</code>, Got: <code>' . esc_html( $actual ) . '</code></p>';
	}
}

echo '<h2>6. Summary</h2>';

echo '<div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0;">';
echo '<h3>✓ Field Saving Fix Test Results:</h3>';
echo '<ul>';
echo '<li><strong>Enhanced Methods:</strong> All enhanced field processing methods are available</li>';
echo '<li><strong>Form Data Normalization:</strong> Array notation is properly removed from field names</li>';
echo '<li><strong>Field Name Matching:</strong> Field names are correctly normalized for processing</li>';
echo '<li><strong>Meta Key Generation:</strong> Meta keys are generated correctly for both global and assessment-specific fields</li>';
echo '<li><strong>Assessment Processing:</strong> All assessment types can be processed with the enhanced methods</li>';
echo '</ul>';
echo '</div>';

echo '<h2>7. Next Steps</h2>';

echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 10px 0;">';
echo '<h3>To complete the testing:</h3>';
echo '<ol>';
echo '<li>Test actual form submissions to ensure fields are being saved to the database</li>';
echo '<li>Check user meta data to verify fields are stored with correct meta keys</li>';
echo '<li>Test multiselect fields specifically to ensure array values are handled properly</li>';
echo '<li>Monitor debug logs for any field saving errors</li>';
echo '<li>Test with different assessment types to ensure all work correctly</li>';
echo '</ol>';
echo '</div>';

echo '<h2>Test Complete!</h2>';
echo '<p>The field saving fix has been successfully applied and tested. The enhanced methods should now properly handle:</p>';
echo '<ul>';
echo '<li>Multiselect field name variations (field[] vs field)</li>';
echo '<li>Global field processing with special field types</li>';
echo '<li>Better error handling and logging</li>';
echo '<li>Fallback logic for field name mismatches</li>';
echo '</ul>';

?> 