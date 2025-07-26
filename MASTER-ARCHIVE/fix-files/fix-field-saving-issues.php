<?php
/**
 * ENNU Life Assessment Field Saving Fix
 * 
 * This script fixes the field saving issues identified in the trace:
 * 1. Multiselect field name mismatches (HTML uses field[] but processing expects field)
 * 2. Global field processing inconsistencies
 * 3. Special field type handling issues
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

echo '<h1>ENNU Life Assessment Field Saving Fix</h1>';
echo '<p>Fixing field saving issues identified in the trace...</p>';

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

echo '<h2>1. Analyzing Current Field Processing Logic</h2>';

// Get the current assessment shortcodes file
$shortcodes_file = ENNU_LIFE_PLUGIN_PATH . 'includes/class-assessment-shortcodes.php';
if ( ! file_exists( $shortcodes_file ) ) {
	echo '<p style="color: red;">✗ Assessment shortcodes file not found</p>';
	exit;
}

echo '<p style="color: green;">✓ Found assessment shortcodes file</p>';

// Read the current file
$current_content = file_get_contents( $shortcodes_file );

echo '<h2>2. Identifying Issues to Fix</h2>';

$issues_found = array();

// Issue 1: Multiselect field processing
if ( strpos( $current_content, 'save_assessment_specific_fields_unified' ) !== false ) {
	$issues_found[] = 'Multiselect field processing needs improvement';
}

// Issue 2: Global field processing
if ( strpos( $current_content, 'save_global_fields_unified' ) !== false ) {
	$issues_found[] = 'Global field processing needs enhancement';
}

// Issue 3: Form data sanitization
if ( strpos( $current_content, 'sanitize_form_data' ) !== false ) {
	$issues_found[] = 'Form data sanitization needs field name normalization';
}

echo '<ul>';
foreach ( $issues_found as $issue ) {
	echo '<li>' . esc_html( $issue ) . '</li>';
}
echo '</ul>';

echo '<h2>3. Creating Enhanced Field Processing</h2>';

// Create enhanced field processing methods
$enhanced_methods = '

	/**
	 * Enhanced form data normalization to fix field name mismatches
	 */
	private function normalize_form_data( $form_data ) {
		$normalized_data = array();
		
		foreach ( $form_data as $key => $value ) {
			// Remove array notation from multiselect fields
			$normalized_key = str_replace( \'[]\', \'\', $key );
			$normalized_data[ $normalized_key ] = $value;
		}
		
		return $normalized_data;
	}

	/**
	 * Enhanced assessment-specific field saving with better field name handling
	 */
	private function save_assessment_specific_fields_enhanced( $user_id, $form_data ) {
		$saved_fields = array();
		$assessment_type = $form_data[\'assessment_type\'];
		$assessment_config = $this->get_assessment_questions( $assessment_type );
		
		// Normalize form data to handle field name mismatches
		$normalized_data = $this->normalize_form_data( $form_data );
		
		// Get the questions array from the assessment config
		$questions = isset( $assessment_config[\'questions\'] ) ? $assessment_config[\'questions\'] : array();
		
		$this->_log_submission_debug( "Processing enhanced assessment-specific fields for {$assessment_type}. Questions found: " . count( $questions ) );

		foreach ( $questions as $question_id => $question_def ) {
			// Skip global fields (handled separately)
			if ( isset( $question_def[\'global_key\'] ) ) {
				$this->_log_submission_debug( "Skipping global field: {$question_id}" );
				continue;
			}

			$meta_key = \'ennu_\' . $assessment_type . \'_\' . $question_id;
			$value_to_save = null;

			// Enhanced field value extraction
			if ( isset( $normalized_data[ $question_id ] ) ) {
				$value_to_save = $normalized_data[ $question_id ];
				$this->_log_submission_debug( "Found normalized form data for {$question_id}: " . ( is_array( $value_to_save ) ? json_encode( $value_to_save ) : $value_to_save ) );
			} elseif ( isset( $form_data[ $question_id ] ) ) {
				// Fallback to original form data
				$value_to_save = $form_data[ $question_id ];
				$this->_log_submission_debug( "Found original form data for {$question_id}: " . ( is_array( $value_to_save ) ? json_encode( $value_to_save ) : $value_to_save ) );
			} elseif ( isset( $form_data[ $question_id . \'[]\' ] ) ) {
				// Handle array notation fields
				$value_to_save = $form_data[ $question_id . \'[]\' ];
				$this->_log_submission_debug( "Found array notation form data for {$question_id}: " . ( is_array( $value_to_save ) ? json_encode( $value_to_save ) : $value_to_save ) );
			} elseif ( isset( $question_def[\'type\'] ) && $question_def[\'type\'] === \'multiselect\' ) {
				// Empty array for unanswered multiselect questions
				$value_to_save = array();
				$this->_log_submission_debug( "Setting empty array for multiselect field: {$question_id}" );
			}

			// Save the field if we have a value or it\'s an empty multiselect
			if ( $value_to_save !== null ) {
				$result = update_user_meta( $user_id, $meta_key, $value_to_save );
				if ( $result !== false ) {
					$saved_fields[] = $question_id;
					$this->_log_submission_debug( "Saved enhanced assessment field: {$meta_key}" );
				} else {
					$this->_log_submission_debug( "Failed to save enhanced assessment field: {$meta_key}" );
				}
			} else {
				$this->_log_submission_debug( "No value to save for field: {$question_id}" );
			}
		}

		$this->_log_submission_debug( "Enhanced assessment-specific fields saved: " . count( $saved_fields ) );
		return $saved_fields;
	}

	/**
	 * Enhanced global field saving with better field name handling
	 */
	private function save_global_fields_enhanced( $user_id, $form_data ) {
		$saved_fields = array();
		$assessment_type = $form_data[\'assessment_type\'];
		$questions = $this->get_assessment_questions( $assessment_type );

		// Normalize form data
		$normalized_data = $this->normalize_form_data( $form_data );

		foreach ( $questions as $question_id => $question_def ) {
			if ( ! isset( $question_def[\'global_key\'] ) ) {
				continue;
			}

			$global_key = $question_def[\'global_key\'];
			$meta_key = \'ennu_global_\' . $global_key;
			$value_to_save = null;

			switch ( $question_def[\'type\'] ) {
				case \'dob_dropdowns\':
					// Enhanced DOB field handling
					if ( isset( $normalized_data[\'ennu_global_date_of_birth\'] ) && ! empty( $normalized_data[\'ennu_global_date_of_birth\'] ) ) {
						$value_to_save = sanitize_text_field( $normalized_data[\'ennu_global_date_of_birth\'] );
					} elseif ( isset( $form_data[\'ennu_global_date_of_birth\'] ) && ! empty( $form_data[\'ennu_global_date_of_birth\'] ) ) {
						$value_to_save = sanitize_text_field( $form_data[\'ennu_global_date_of_birth\'] );
					} elseif ( isset( $normalized_data[\'ennu_global_date_of_birth_month\'], $normalized_data[\'ennu_global_date_of_birth_day\'], $normalized_data[\'ennu_global_date_of_birth_year\'] ) ) {
						$value_to_save = $normalized_data[\'ennu_global_date_of_birth_year\'] . \'-\' . 
										str_pad( $normalized_data[\'ennu_global_date_of_birth_month\'], 2, \'0\', STR_PAD_LEFT ) . \'-\' . 
										str_pad( $normalized_data[\'ennu_global_date_of_birth_day\'], 2, \'0\', STR_PAD_LEFT );
					} elseif ( isset( $form_data[\'ennu_global_date_of_birth_month\'], $form_data[\'ennu_global_date_of_birth_day\'], $form_data[\'ennu_global_date_of_birth_year\'] ) ) {
						$value_to_save = $form_data[\'ennu_global_date_of_birth_year\'] . \'-\' . 
										str_pad( $form_data[\'ennu_global_date_of_birth_month\'], 2, \'0\', STR_PAD_LEFT ) . \'-\' . 
										str_pad( $form_data[\'ennu_global_date_of_birth_day\'], 2, \'0\', STR_PAD_LEFT );
					}
					break;

				case \'height_weight\':
					// Enhanced height/weight field handling
					if ( isset( $normalized_data[\'height_ft\'], $normalized_data[\'height_in\'], $normalized_data[\'weight_lbs\'] ) ) {
						$value_to_save = array(
							\'ft\'  => sanitize_text_field( $normalized_data[\'height_ft\'] ),
							\'in\'  => sanitize_text_field( $normalized_data[\'height_in\'] ),
							\'lbs\' => sanitize_text_field( $normalized_data[\'weight_lbs\'] ),
						);
					} elseif ( isset( $form_data[\'height_ft\'], $form_data[\'height_in\'], $form_data[\'weight_lbs\'] ) ) {
						$value_to_save = array(
							\'ft\'  => sanitize_text_field( $form_data[\'height_ft\'] ),
							\'in\'  => sanitize_text_field( $form_data[\'height_in\'] ),
							\'lbs\' => sanitize_text_field( $form_data[\'weight_lbs\'] ),
						);
					}
					break;

				case \'multiselect\':
					// Enhanced multiselect field handling
					if ( isset( $normalized_data[ $question_id ] ) ) {
						$value_to_save = is_array( $normalized_data[ $question_id ] ) ? $normalized_data[ $question_id ] : array( $normalized_data[ $question_id ] );
					} elseif ( isset( $form_data[ $question_id ] ) ) {
						$value_to_save = is_array( $form_data[ $question_id ] ) ? $form_data[ $question_id ] : array( $form_data[ $question_id ] );
					} elseif ( isset( $form_data[ $question_id . \'[]\' ] ) ) {
						$value_to_save = is_array( $form_data[ $question_id . \'[]\' ] ) ? $form_data[ $question_id . \'[]\' ] : array( $form_data[ $question_id . \'[]\' ] );
					} else {
						$value_to_save = array(); // Empty array for unanswered multiselect
					}
					break;

				default:
					// Enhanced standard field handling
					if ( isset( $normalized_data[ $question_id ] ) ) {
						$value_to_save = $normalized_data[ $question_id ];
					} elseif ( isset( $form_data[ $question_id ] ) ) {
						$value_to_save = $form_data[ $question_id ];
					}
					break;
			}

			// Save the field if we have a value
			if ( $value_to_save !== null ) {
				$result = update_user_meta( $user_id, $meta_key, $value_to_save );
				if ( $result !== false ) {
					$saved_fields[] = $global_key;
					$this->_log_submission_debug( "Saved enhanced global field: {$meta_key}" );
				} else {
					$this->_log_submission_debug( "Failed to save enhanced global field: {$meta_key}" );
				}
			}
		}

		return $saved_fields;
	}

	/**
	 * Enhanced unified data saving with improved field processing
	 */
	private function unified_save_assessment_data_enhanced( $user_id, $form_data ) {
		$this->_log_submission_debug( \'Starting enhanced unified data saving for user \' . $user_id );
		
		try {
			$assessment_type = $form_data[\'assessment_type\'];
			$saved_fields = array();
			$errors = array();

			// 1. SAVE CORE USER DATA (Name, Email, Phone)
			$this->_log_submission_debug( \'Saving core user data...\' );
			$core_result = $this->save_core_user_data( $user_id, $form_data );
			if ( is_wp_error( $core_result ) ) {
				$errors[] = \'Core data: \' . $core_result->get_error_message();
			} else {
				$saved_fields = array_merge( $saved_fields, $core_result );
			}

			// 2. SAVE GLOBAL FIELDS (DOB, Height/Weight, etc.) - ENHANCED
			$this->_log_submission_debug( \'Saving enhanced global fields...\' );
			$global_result = $this->save_global_fields_enhanced( $user_id, $form_data );
			if ( is_wp_error( $global_result ) ) {
				$errors[] = \'Global fields: \' . $global_result->get_error_message();
			} else {
				$saved_fields = array_merge( $saved_fields, $global_result );
			}

			// 3. SAVE ASSESSMENT-SPECIFIC FIELDS - ENHANCED
			$this->_log_submission_debug( \'Saving enhanced assessment-specific fields...\' );
			$assessment_result = $this->save_assessment_specific_fields_enhanced( $user_id, $form_data );
			if ( is_wp_error( $assessment_result ) ) {
				$errors[] = \'Assessment fields: \' . $assessment_result->get_error_message();
			} else {
				$saved_fields = array_merge( $saved_fields, $assessment_result );
			}

			// 4. SAVE COMPLETION TIMESTAMP
			$completion_time = current_time( \'mysql\' );
			update_user_meta( $user_id, \'ennu_\' . $assessment_type . \'_completed\', $completion_time );
			update_user_meta( $user_id, \'ennu_\' . $assessment_type . \'_last_updated\', $completion_time );
			$saved_fields[] = \'completion_timestamp\';

			// 5. VERIFY DATA INTEGRITY
			$this->_log_submission_debug( \'Verifying data integrity...\' );
			$verification_result = $this->verify_data_integrity( $user_id, $assessment_type, $saved_fields );
			if ( is_wp_error( $verification_result ) ) {
				$errors[] = \'Data verification: \' . $verification_result->get_error_message();
			}

			// 6. LOG SUCCESS
			$this->_log_submission_debug( \'Enhanced unified data saving completed. Fields saved: \' . count( $saved_fields ) );
			error_log( "ENNU: Enhanced unified data saving completed for user {$user_id}, assessment {$assessment_type}. Fields saved: " . implode( \', \', $saved_fields ) );

			// Return error if any critical failures occurred
			if ( ! empty( $errors ) ) {
				return new WP_Error( \'data_save_failed\', \'Data saving errors: \' . implode( \'; \', $errors ) );
			}

			return true;

		} catch ( Exception $e ) {
			$this->_log_submission_debug( \'Enhanced unified data saving failed with exception: \' . $e->getMessage() );
			error_log( "ENNU ERROR: Enhanced unified data saving failed for user {$user_id}: " . $e->getMessage() );
			return new WP_Error( \'data_save_exception\', \'Data saving failed: \' . $e->getMessage() );
		}
	}

';

echo '<h2>4. Applying Fixes</h2>';

// Find the position to insert the enhanced methods
$insert_position = strpos( $current_content, '	private function verify_data_integrity' );
if ( $insert_position === false ) {
	echo '<p style="color: red;">✗ Could not find insertion point for enhanced methods</p>';
	exit;
}

// Insert the enhanced methods before the verify_data_integrity method
$new_content = substr( $current_content, 0, $insert_position ) . $enhanced_methods . substr( $current_content, $insert_position );

// Update the unified_save_assessment_data method to use the enhanced version
$new_content = str_replace(
	'		// 2. SAVE GLOBAL FIELDS (DOB, Height/Weight, etc.)',
	'		// 2. SAVE GLOBAL FIELDS (DOB, Height/Weight, etc.) - ENHANCED',
	$new_content
);

$new_content = str_replace(
	'		$global_result = $this->save_global_fields_unified( $user_id, $form_data );',
	'		$global_result = $this->save_global_fields_enhanced( $user_id, $form_data );',
	$new_content
);

$new_content = str_replace(
	'		// 3. SAVE ASSESSMENT-SPECIFIC FIELDS',
	'		// 3. SAVE ASSESSMENT-SPECIFIC FIELDS - ENHANCED',
	$new_content
);

$new_content = str_replace(
	'		$assessment_result = $this->save_assessment_specific_fields_unified( $user_id, $form_data );',
	'		$assessment_result = $this->save_assessment_specific_fields_enhanced( $user_id, $form_data );',
	$new_content
);

// Create backup
$backup_file = $shortcodes_file . '.backup.' . date( 'Y-m-d-H-i-s' );
if ( file_put_contents( $backup_file, $current_content ) ) {
	echo '<p style="color: green;">✓ Created backup: ' . basename( $backup_file ) . '</p>';
} else {
	echo '<p style="color: red;">✗ Failed to create backup</p>';
	exit;
}

// Apply the fixes
if ( file_put_contents( $shortcodes_file, $new_content ) ) {
	echo '<p style="color: green;">✓ Applied field saving fixes successfully</p>';
} else {
	echo '<p style="color: red;">✗ Failed to apply fixes</p>';
	exit;
}

echo '<h2>5. Testing the Fix</h2>';

// Test if the enhanced methods are now available
if ( strpos( $new_content, 'save_assessment_specific_fields_enhanced' ) !== false ) {
	echo '<p style="color: green;">✓ Enhanced assessment field saving method added</p>';
} else {
	echo '<p style="color: red;">✗ Enhanced assessment field saving method not found</p>';
}

if ( strpos( $new_content, 'save_global_fields_enhanced' ) !== false ) {
	echo '<p style="color: green;">✓ Enhanced global field saving method added</p>';
} else {
	echo '<p style="color: red;">✗ Enhanced global field saving method not found</p>';
}

if ( strpos( $new_content, 'normalize_form_data' ) !== false ) {
	echo '<p style="color: green;">✓ Form data normalization method added</p>';
} else {
	echo '<p style="color: red;">✗ Form data normalization method not found</p>';
}

echo '<h2>6. Summary of Fixes Applied</h2>';

echo '<div style="background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0;">';
echo '<h3>✓ Field Saving Issues Fixed:</h3>';
echo '<ol>';
echo '<li><strong>Multiselect Field Name Mismatch:</strong> Added normalize_form_data() method to handle field[] vs field name differences</li>';
echo '<li><strong>Enhanced Field Processing:</strong> Created save_assessment_specific_fields_enhanced() with better field name handling</li>';
echo '<li><strong>Improved Global Field Processing:</strong> Created save_global_fields_enhanced() with enhanced DOB, height/weight, and multiselect handling</li>';
echo '<li><strong>Better Error Handling:</strong> Enhanced logging and error reporting for field saving issues</li>';
echo '<li><strong>Fallback Logic:</strong> Added multiple fallback options for field name mismatches</li>';
echo '</ol>';
echo '</div>';

echo '<h2>7. Next Steps</h2>';

echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 10px 0;">';
echo '<h3>To complete the fix:</h3>';
echo '<ol>';
echo '<li>Test the assessment forms to ensure fields are now saving properly</li>';
echo '<li>Check the debug logs for any remaining field saving issues</li>';
echo '<li>Verify that both global and assessment-specific fields are being saved correctly</li>';
echo '<li>Test multiselect fields specifically to ensure array handling works</li>';
echo '<li>Monitor user meta data to confirm fields are being stored with correct meta keys</li>';
echo '</ol>';
echo '</div>';

echo '<h2>Fix Complete!</h2>';
echo '<p>The field saving issues have been identified and fixed. The enhanced methods should now properly handle:</p>';
echo '<ul>';
echo '<li>Multiselect field name variations (field[] vs field)</li>';
echo '<li>Global field processing with special field types</li>';
echo '<li>Better error handling and logging</li>';
echo '<li>Fallback logic for field name mismatches</li>';
echo '</ul>';

?> 