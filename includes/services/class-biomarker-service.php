<?php
/**
 * ENNU Biomarker Service
 *
 * Handles biomarker data management, validation, and processing.
 *
 * @package ENNU_Life_Assessments
 * @since 64.11.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Biomarker Service Class
 *
 * @since 64.11.0
 */
class ENNU_Biomarker_Service {
	
	/**
	 * Database instance
	 *
	 * @var ENNU_Enhanced_Database
	 */
	private $database;
	
	/**
	 * Biomarker categories mapping
	 *
	 * @var array
	 */
	private $biomarker_categories = array(
		'testosterone' => 'Hormones',
		'estradiol' => 'Hormones',
		'cortisol' => 'Hormones',
		'tsh' => 'Hormones',
		't3' => 'Hormones',
		't4' => 'Hormones',
		'glucose' => 'Metabolic',
		'insulin' => 'Metabolic',
		'hba1c' => 'Metabolic',
		'cholesterol' => 'Lipids',
		'triglycerides' => 'Lipids',
		'ldl' => 'Lipids',
		'hdl' => 'Lipids',
		'blood_pressure' => 'Cardiovascular',
		'heart_rate' => 'Cardiovascular',
		'wbc' => 'Immune',
		'rbc' => 'Immune',
		'hemoglobin' => 'Immune',
		'platelets' => 'Immune',
	);
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->database = ENNU_Life_Enhanced_Plugin::get_instance()->get_database();
	}
	
	/**
	 * Validate biomarker data
	 *
	 * @param array $biomarker_data Biomarker data to validate.
	 * @return array Validation result with 'valid' boolean and 'errors' array.
	 */
	public function validate_biomarker( $biomarker_data ) {
		$errors = array();
		
		// Check required fields
		$required_fields = array( 'name', 'value', 'unit', 'reference_range' );
		foreach ( $required_fields as $field ) {
			if ( empty( $biomarker_data[ $field ] ) ) {
				$errors[] = sprintf( 'Missing required field: %s', $field );
			}
		}
		
		// Validate value is numeric
		if ( ! empty( $biomarker_data['value'] ) && ! is_numeric( $biomarker_data['value'] ) ) {
			$errors[] = 'Biomarker value must be numeric';
		}
		
		// Validate reference range format
		if ( ! empty( $biomarker_data['reference_range'] ) ) {
			if ( ! preg_match( '/^\d+-\d+$/', $biomarker_data['reference_range'] ) ) {
				$errors[] = 'Reference range must be in format "min-max"';
			}
		}
		
		// Validate unit
		if ( ! empty( $biomarker_data['unit'] ) && strlen( $biomarker_data['unit'] ) > 20 ) {
			$errors[] = 'Unit must be 20 characters or less';
		}
		
		return array(
			'valid' => empty( $errors ),
			'errors' => $errors,
		);
	}
	
	/**
	 * Check if biomarker value is within reference range
	 *
	 * @param array $biomarker Biomarker data.
	 * @return string Status: 'normal', 'low', or 'high'.
	 */
	public function check_reference_range( $biomarker ) {
		if ( empty( $biomarker['value'] ) || empty( $biomarker['reference_range'] ) ) {
			return 'unknown';
		}
		
		$value = floatval( $biomarker['value'] );
		list( $min, $max ) = explode( '-', $biomarker['reference_range'] );
		$min = floatval( $min );
		$max = floatval( $max );
		
		if ( $value < $min ) {
			return 'low';
		} elseif ( $value > $max ) {
			return 'high';
		} else {
			return 'normal';
		}
	}
	
	/**
	 * Classify biomarker by category
	 *
	 * @param string $biomarker_name Biomarker name.
	 * @return string Category name.
	 */
	public function classify_biomarker( $biomarker_name ) {
		$normalized_name = strtolower( str_replace( array( ' ', '-', '_' ), '', $biomarker_name ) );
		
		foreach ( $this->biomarker_categories as $key => $category ) {
			if ( strpos( $normalized_name, $key ) !== false ) {
				return $category;
			}
		}
		
		return 'Other';
	}
	
	/**
	 * Save biomarker data
	 *
	 * @param array $biomarker_data Biomarker data to save.
	 * @return array Result with 'success' boolean and 'id' if successful.
	 */
	public function save_biomarker( $biomarker_data ) {
		// Validate data first
		$validation = $this->validate_biomarker( $biomarker_data );
		if ( ! $validation['valid'] ) {
			return array(
				'success' => false,
				'errors' => $validation['errors'],
			);
		}
		
		// Add category if not provided
		if ( empty( $biomarker_data['category'] ) ) {
			$biomarker_data['category'] = $this->classify_biomarker( $biomarker_data['name'] );
		}
		
		// Add timestamp
		$biomarker_data['created_at'] = current_time( 'mysql' );
		
		// Save to database
		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_biomarkers';
		
		$result = $wpdb->insert(
			$table_name,
			array(
				'user_id' => intval( $biomarker_data['user_id'] ),
				'name' => sanitize_text_field( $biomarker_data['name'] ),
				'value' => floatval( $biomarker_data['value'] ),
				'unit' => sanitize_text_field( $biomarker_data['unit'] ),
				'reference_range' => sanitize_text_field( $biomarker_data['reference_range'] ),
				'category' => sanitize_text_field( $biomarker_data['category'] ),
				'created_at' => $biomarker_data['created_at'],
			),
			array( '%d', '%s', '%f', '%s', '%s', '%s', '%s' )
		);
		
		if ( $result === false ) {
			return array(
				'success' => false,
				'errors' => array( 'Database error: ' . $wpdb->last_error ),
			);
		}
		
		return array(
			'success' => true,
			'id' => $wpdb->insert_id,
		);
	}
	
	/**
	 * Get biomarker by ID
	 *
	 * @param int $biomarker_id Biomarker ID.
	 * @return array|null Biomarker data or null if not found.
	 */
	public function get_biomarker( $biomarker_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_biomarkers';
		
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE id = %d",
				$biomarker_id
			),
			ARRAY_A
		);
		
		return $result ?: null;
	}
	
	/**
	 * Get biomarkers by user ID
	 *
	 * @param int $user_id User ID.
	 * @return array Array of biomarker data.
	 */
	public function get_user_biomarkers( $user_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_biomarkers';
		
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY created_at DESC",
				$user_id
			),
			ARRAY_A
		);
		
		return $results ?: array();
	}
	
	/**
	 * Update biomarker data
	 *
	 * @param int   $biomarker_id Biomarker ID.
	 * @param array $update_data Data to update.
	 * @return array Result with 'success' boolean.
	 */
	public function update_biomarker( $biomarker_id, $update_data ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_biomarkers';
		
		// Prepare update data
		$update_fields = array();
		$update_formats = array();
		
		foreach ( $update_data as $field => $value ) {
			switch ( $field ) {
				case 'name':
					$update_fields[ $field ] = sanitize_text_field( $value );
					$update_formats[] = '%s';
					break;
				case 'value':
					$update_fields[ $field ] = floatval( $value );
					$update_formats[] = '%f';
					break;
				case 'unit':
					$update_fields[ $field ] = sanitize_text_field( $value );
					$update_formats[] = '%s';
					break;
				case 'reference_range':
					$update_fields[ $field ] = sanitize_text_field( $value );
					$update_formats[] = '%s';
					break;
				case 'category':
					$update_fields[ $field ] = sanitize_text_field( $value );
					$update_formats[] = '%s';
					break;
			}
		}
		
		if ( empty( $update_fields ) ) {
			return array(
				'success' => false,
				'errors' => array( 'No valid fields to update' ),
			);
		}
		
		$result = $wpdb->update(
			$table_name,
			$update_fields,
			array( 'id' => $biomarker_id ),
			$update_formats,
			array( '%d' )
		);
		
		if ( $result === false ) {
			return array(
				'success' => false,
				'errors' => array( 'Database error: ' . $wpdb->last_error ),
			);
		}
		
		return array(
			'success' => true,
		);
	}
	
	/**
	 * Delete biomarker
	 *
	 * @param int $biomarker_id Biomarker ID.
	 * @return array Result with 'success' boolean.
	 */
	public function delete_biomarker( $biomarker_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_biomarkers';
		
		$result = $wpdb->delete(
			$table_name,
			array( 'id' => $biomarker_id ),
			array( '%d' )
		);
		
		if ( $result === false ) {
			return array(
				'success' => false,
				'errors' => array( 'Database error: ' . $wpdb->last_error ),
			);
		}
		
		return array(
			'success' => true,
		);
	}
	
	/**
	 * Get biomarker categories
	 *
	 * @return array Array of category names.
	 */
	public function get_biomarker_categories() {
		return array_values( array_unique( $this->biomarker_categories ) );
	}
	
	/**
	 * Get biomarkers by category
	 *
	 * @param string $category Category name.
	 * @return array Array of biomarker names in category.
	 */
	public function get_biomarkers_by_category( $category ) {
		$biomarkers = array();
		
		foreach ( $this->biomarker_categories as $biomarker => $cat ) {
			if ( $cat === $category ) {
				$biomarkers[] = $biomarker;
			}
		}
		
		return $biomarkers;
	}
	
	/**
	 * Calculate biomarker trends
	 *
	 * @param int    $user_id User ID.
	 * @param string $biomarker_name Biomarker name.
	 * @param int    $days Number of days to analyze.
	 * @return array Trend data.
	 */
	public function calculate_trends( $user_id, $biomarker_name, $days = 30 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_biomarkers';
		
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT value, created_at FROM {$table_name} 
				WHERE user_id = %d AND name = %s 
				AND created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)
				ORDER BY created_at ASC",
				$user_id,
				$biomarker_name,
				$days
			),
			ARRAY_A
		);
		
		if ( count( $results ) < 2 ) {
			return array(
				'trend' => 'insufficient_data',
				'change_percent' => 0,
				'data_points' => count( $results ),
			);
		}
		
		// Calculate trend
		$first_value = floatval( $results[0]['value'] );
		$last_value = floatval( $results[ count( $results ) - 1 ]['value'] );
		$change_percent = ( ( $last_value - $first_value ) / $first_value ) * 100;
		
		$trend = 'stable';
		if ( $change_percent > 5 ) {
			$trend = 'increasing';
		} elseif ( $change_percent < -5 ) {
			$trend = 'decreasing';
		}
		
		return array(
			'trend' => $trend,
			'change_percent' => round( $change_percent, 2 ),
			'data_points' => count( $results ),
			'first_value' => $first_value,
			'last_value' => $last_value,
		);
	}
} 