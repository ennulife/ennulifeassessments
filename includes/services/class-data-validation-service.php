<?php
/**
 * ENNU Data Validation Service
 * Comprehensive data validation and consistency management
 * 
 * @package ENNU_Life_Assessments
 * @version 3.37.16
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data validation service that ensures data consistency across all assessment types
 * Handles validation, sanitization, and data integrity checks
 */
class ENNU_Data_Validation_Service {

	private static $instance = null;
	private $logger;
	private $validation_rules = array();

	/**
	 * Get singleton instance
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->logger = class_exists( 'ENNU_Logger' ) ? new ENNU_Logger() : null;
		$this->init_validation_rules();
	}

	/**
	 * Initialize validation rules for all assessment types
	 */
	private function init_validation_rules() {
		$this->validation_rules = array(
			'weight_loss' => array(
				'required_fields' => array( 'current_weight', 'target_weight', 'activity_level' ),
				'field_rules' => array(
					'current_weight' => array( 'type' => 'numeric', 'min' => 50, 'max' => 500 ),
					'target_weight' => array( 'type' => 'numeric', 'min' => 50, 'max' => 500 ),
					'activity_level' => array( 'type' => 'enum', 'values' => array( 'sedentary', 'light', 'moderate', 'active', 'very_active' ) )
				)
			),
			'sleep' => array(
				'required_fields' => array( 'sleep_hours', 'sleep_quality', 'sleep_issues' ),
				'field_rules' => array(
					'sleep_hours' => array( 'type' => 'numeric', 'min' => 0, 'max' => 24 ),
					'sleep_quality' => array( 'type' => 'enum', 'values' => array( 'poor', 'fair', 'good', 'excellent' ) ),
					'sleep_issues' => array( 'type' => 'array', 'max_items' => 5 )
				)
			),
			'hormone' => array(
				'required_fields' => array( 'hormone_symptoms', 'test_results' ),
				'field_rules' => array(
					'hormone_symptoms' => array( 'type' => 'array', 'max_items' => 10 ),
					'test_results' => array( 'type' => 'object', 'required_keys' => array( 'testosterone', 'estrogen', 'cortisol' ) )
				)
			),
			'nutrition' => array(
				'required_fields' => array( 'diet_type', 'meal_frequency', 'supplements' ),
				'field_rules' => array(
					'diet_type' => array( 'type' => 'enum', 'values' => array( 'omnivore', 'vegetarian', 'vegan', 'keto', 'paleo', 'mediterranean' ) ),
					'meal_frequency' => array( 'type' => 'numeric', 'min' => 1, 'max' => 10 ),
					'supplements' => array( 'type' => 'array', 'max_items' => 20 )
				)
			),
			'fitness' => array(
				'required_fields' => array( 'workout_frequency', 'workout_duration', 'fitness_goals' ),
				'field_rules' => array(
					'workout_frequency' => array( 'type' => 'numeric', 'min' => 0, 'max' => 7 ),
					'workout_duration' => array( 'type' => 'numeric', 'min' => 0, 'max' => 300 ),
					'fitness_goals' => array( 'type' => 'array', 'max_items' => 5 )
				)
			),
			'stress' => array(
				'required_fields' => array( 'stress_level', 'stress_sources', 'coping_methods' ),
				'field_rules' => array(
					'stress_level' => array( 'type' => 'enum', 'values' => array( 'low', 'moderate', 'high', 'severe' ) ),
					'stress_sources' => array( 'type' => 'array', 'max_items' => 10 ),
					'coping_methods' => array( 'type' => 'array', 'max_items' => 10 )
				)
			),
			'energy' => array(
				'required_fields' => array( 'energy_level', 'energy_pattern', 'energy_boosters' ),
				'field_rules' => array(
					'energy_level' => array( 'type' => 'enum', 'values' => array( 'very_low', 'low', 'moderate', 'high', 'very_high' ) ),
					'energy_pattern' => array( 'type' => 'enum', 'values' => array( 'morning', 'afternoon', 'evening', 'consistent', 'variable' ) ),
					'energy_boosters' => array( 'type' => 'array', 'max_items' => 10 )
				)
			),
			'recovery' => array(
				'required_fields' => array( 'recovery_time', 'recovery_methods', 'injury_history' ),
				'field_rules' => array(
					'recovery_time' => array( 'type' => 'numeric', 'min' => 0, 'max' => 72 ),
					'recovery_methods' => array( 'type' => 'array', 'max_items' => 10 ),
					'injury_history' => array( 'type' => 'array', 'max_items' => 20 )
				)
			),
			'longevity' => array(
				'required_fields' => array( 'age', 'family_history', 'longevity_goals' ),
				'field_rules' => array(
					'age' => array( 'type' => 'numeric', 'min' => 18, 'max' => 120 ),
					'family_history' => array( 'type' => 'array', 'max_items' => 20 ),
					'longevity_goals' => array( 'type' => 'array', 'max_items' => 10 )
				)
			),
			'performance' => array(
				'required_fields' => array( 'performance_metrics', 'performance_goals', 'performance_barriers' ),
				'field_rules' => array(
					'performance_metrics' => array( 'type' => 'object', 'required_keys' => array( 'strength', 'endurance', 'flexibility', 'balance' ) ),
					'performance_goals' => array( 'type' => 'array', 'max_items' => 10 ),
					'performance_barriers' => array( 'type' => 'array', 'max_items' => 10 )
				)
			),
			'wellness' => array(
				'required_fields' => array( 'overall_health', 'wellness_dimensions', 'wellness_goals' ),
				'field_rules' => array(
					'overall_health' => array( 'type' => 'enum', 'values' => array( 'poor', 'fair', 'good', 'excellent' ) ),
					'wellness_dimensions' => array( 'type' => 'object', 'required_keys' => array( 'physical', 'mental', 'emotional', 'social', 'spiritual' ) ),
					'wellness_goals' => array( 'type' => 'array', 'max_items' => 10 )
				)
			)
		);
	}

	/**
	 * Validate assessment data
	 * 
	 * @param string $assessment_type Assessment type
	 * @param array $data Assessment data
	 * @return array Validation result with success status and errors
	 */
	public function validate_assessment_data( $assessment_type, $data ) {
		$result = array(
			'success' => true,
			'errors' => array(),
			'warnings' => array(),
			'sanitized_data' => array()
		);

		// Check if assessment type is valid
		if ( ! isset( $this->validation_rules[ $assessment_type ] ) ) {
			$result['success'] = false;
			$result['errors'][] = 'Invalid assessment type: ' . $assessment_type;
			return $result;
		}

		$rules = $this->validation_rules[ $assessment_type ];

		// Validate required fields
		foreach ( $rules['required_fields'] as $field ) {
			if ( ! isset( $data[ $field ] ) || empty( $data[ $field ] ) ) {
				$result['success'] = false;
				$result['errors'][] = 'Required field missing: ' . $field;
			}
		}

		// Validate field rules
		foreach ( $rules['field_rules'] as $field => $rule ) {
			if ( isset( $data[ $field ] ) ) {
				$field_result = $this->validate_field( $field, $data[ $field ], $rule );
				if ( ! $field_result['success'] ) {
					$result['success'] = false;
					$result['errors'] = array_merge( $result['errors'], $field_result['errors'] );
				}
				if ( ! empty( $field_result['warnings'] ) ) {
					$result['warnings'] = array_merge( $result['warnings'], $field_result['warnings'] );
				}
				$result['sanitized_data'][ $field ] = $field_result['sanitized_value'];
			}
		}

		// Log validation results
		if ( $this->logger ) {
			$log_data = array(
				'assessment_type' => $assessment_type,
				'success' => $result['success'],
				'error_count' => count( $result['errors'] ),
				'warning_count' => count( $result['warnings'] )
			);
			$this->logger->log( 'Assessment data validation completed', $log_data );
		}

		return $result;
	}

	/**
	 * Validate individual field
	 * 
	 * @param string $field_name Field name
	 * @param mixed $value Field value
	 * @param array $rule Validation rule
	 * @return array Validation result
	 */
	private function validate_field( $field_name, $value, $rule ) {
		$result = array(
			'success' => true,
			'errors' => array(),
			'warnings' => array(),
			'sanitized_value' => $value
		);

		switch ( $rule['type'] ) {
			case 'numeric':
				$result = $this->validate_numeric_field( $field_name, $value, $rule );
				break;
			case 'enum':
				$result = $this->validate_enum_field( $field_name, $value, $rule );
				break;
			case 'array':
				$result = $this->validate_array_field( $field_name, $value, $rule );
				break;
			case 'object':
				$result = $this->validate_object_field( $field_name, $value, $rule );
				break;
			case 'string':
				$result = $this->validate_string_field( $field_name, $value, $rule );
				break;
			default:
				$result['errors'][] = 'Unknown validation type: ' . $rule['type'];
				$result['success'] = false;
		}

		return $result;
	}

	/**
	 * Validate numeric field
	 */
	private function validate_numeric_field( $field_name, $value, $rule ) {
		$result = array(
			'success' => true,
			'errors' => array(),
			'warnings' => array(),
			'sanitized_value' => $value
		);

		// Check if value is numeric
		if ( ! is_numeric( $value ) ) {
			$result['success'] = false;
			$result['errors'][] = "Field '{$field_name}' must be numeric";
			return $result;
		}

		$numeric_value = floatval( $value );

		// Check minimum value
		if ( isset( $rule['min'] ) && $numeric_value < $rule['min'] ) {
			$result['success'] = false;
			$result['errors'][] = "Field '{$field_name}' must be at least {$rule['min']}";
		}

		// Check maximum value
		if ( isset( $rule['max'] ) && $numeric_value > $rule['max'] ) {
			$result['success'] = false;
			$result['errors'][] = "Field '{$field_name}' must be no more than {$rule['max']}";
		}

		$result['sanitized_value'] = $numeric_value;
		return $result;
	}

	/**
	 * Validate enum field
	 */
	private function validate_enum_field( $field_name, $value, $rule ) {
		$result = array(
			'success' => true,
			'errors' => array(),
			'warnings' => array(),
			'sanitized_value' => $value
		);

		if ( ! in_array( $value, $rule['values'], true ) ) {
			$result['success'] = false;
			$result['errors'][] = "Field '{$field_name}' must be one of: " . implode( ', ', $rule['values'] );
		}

		$result['sanitized_value'] = sanitize_text_field( $value );
		return $result;
	}

	/**
	 * Validate array field
	 */
	private function validate_array_field( $field_name, $value, $rule ) {
		$result = array(
			'success' => true,
			'errors' => array(),
			'warnings' => array(),
			'sanitized_value' => $value
		);

		// Ensure value is an array
		if ( ! is_array( $value ) ) {
			$value = array( $value );
		}

		// Check maximum items
		if ( isset( $rule['max_items'] ) && count( $value ) > $rule['max_items'] ) {
			$result['warnings'][] = "Field '{$field_name}' has more than {$rule['max_items']} items";
			$value = array_slice( $value, 0, $rule['max_items'] );
		}

		// Sanitize array values
		$sanitized_array = array();
		foreach ( $value as $item ) {
			$sanitized_array[] = sanitize_text_field( $item );
		}

		$result['sanitized_value'] = $sanitized_array;
		return $result;
	}

	/**
	 * Validate object field
	 */
	private function validate_object_field( $field_name, $value, $rule ) {
		$result = array(
			'success' => true,
			'errors' => array(),
			'warnings' => array(),
			'sanitized_value' => $value
		);

		// Ensure value is an object/array
		if ( ! is_array( $value ) && ! is_object( $value ) ) {
			$result['success'] = false;
			$result['errors'][] = "Field '{$field_name}' must be an object";
			return $result;
		}

		$value_array = (array) $value;

		// Check required keys
		if ( isset( $rule['required_keys'] ) ) {
			foreach ( $rule['required_keys'] as $required_key ) {
				if ( ! isset( $value_array[ $required_key ] ) ) {
					$result['success'] = false;
					$result['errors'][] = "Field '{$field_name}' missing required key: {$required_key}";
				}
			}
		}

		// Sanitize object values
		$sanitized_object = array();
		foreach ( $value_array as $key => $val ) {
			$sanitized_object[ sanitize_key( $key ) ] = is_array( $val ) ? array_map( 'sanitize_text_field', $val ) : sanitize_text_field( $val );
		}

		$result['sanitized_value'] = $sanitized_object;
		return $result;
	}

	/**
	 * Validate string field
	 */
	private function validate_string_field( $field_name, $value, $rule ) {
		$result = array(
			'success' => true,
			'errors' => array(),
			'warnings' => array(),
			'sanitized_value' => $value
		);

		// Check minimum length
		if ( isset( $rule['min_length'] ) && strlen( $value ) < $rule['min_length'] ) {
			$result['success'] = false;
			$result['errors'][] = "Field '{$field_name}' must be at least {$rule['min_length']} characters";
		}

		// Check maximum length
		if ( isset( $rule['max_length'] ) && strlen( $value ) > $rule['max_length'] ) {
			$result['success'] = false;
			$result['errors'][] = "Field '{$field_name}' must be no more than {$rule['max_length']} characters";
		}

		$result['sanitized_value'] = sanitize_text_field( $value );
		return $result;
	}

	/**
	 * Validate user data consistency
	 * 
	 * @param int $user_id User ID
	 * @return array Consistency check result
	 */
	public function validate_user_data_consistency( $user_id ) {
		$result = array(
			'success' => true,
			'errors' => array(),
			'warnings' => array(),
			'inconsistencies' => array()
		);

		// Get all user assessment data
		$assessment_types = array_keys( $this->validation_rules );
		$user_data = array();

		foreach ( $assessment_types as $type ) {
			$data = get_user_meta( $user_id, "ennu_{$type}_data", true );
			if ( ! empty( $data ) ) {
				$user_data[ $type ] = $data;
			}
		}

		// Check for data inconsistencies
		$result = $this->check_data_inconsistencies( $user_data );

		// Log consistency check results
		if ( $this->logger ) {
			$log_data = array(
				'user_id' => $user_id,
				'assessment_count' => count( $user_data ),
				'inconsistency_count' => count( $result['inconsistencies'] )
			);
			$this->logger->log( 'User data consistency check completed', $log_data );
		}

		return $result;
	}

	/**
	 * Check for data inconsistencies across assessments
	 */
	private function check_data_inconsistencies( $user_data ) {
		$result = array(
			'success' => true,
			'errors' => array(),
			'warnings' => array(),
			'inconsistencies' => array()
		);

		// Check for conflicting age data
		$ages = array();
		if ( isset( $user_data['longevity']['age'] ) ) {
			$ages[] = $user_data['longevity']['age'];
		}
		if ( isset( $user_data['wellness']['age'] ) ) {
			$ages[] = $user_data['wellness']['age'];
		}
		if ( count( array_unique( $ages ) ) > 1 ) {
			$result['inconsistencies'][] = 'Conflicting age data across assessments';
		}

		// Check for conflicting weight data
		$weights = array();
		if ( isset( $user_data['weight_loss']['current_weight'] ) ) {
			$weights[] = $user_data['weight_loss']['current_weight'];
		}
		if ( isset( $user_data['nutrition']['current_weight'] ) ) {
			$weights[] = $user_data['nutrition']['current_weight'];
		}
		if ( count( array_unique( $weights ) ) > 1 ) {
			$result['inconsistencies'][] = 'Conflicting weight data across assessments';
		}

		// Check for conflicting activity levels
		$activity_levels = array();
		if ( isset( $user_data['weight_loss']['activity_level'] ) ) {
			$activity_levels[] = $user_data['weight_loss']['activity_level'];
		}
		if ( isset( $user_data['fitness']['activity_level'] ) ) {
			$activity_levels[] = $user_data['fitness']['activity_level'];
		}
		if ( count( array_unique( $activity_levels ) ) > 1 ) {
			$result['inconsistencies'][] = 'Conflicting activity level data across assessments';
		}

		if ( ! empty( $result['inconsistencies'] ) ) {
			$result['success'] = false;
		}

		return $result;
	}

	/**
	 * Sanitize assessment data
	 * 
	 * @param array $data Raw assessment data
	 * @return array Sanitized data
	 */
	public function sanitize_assessment_data( $data ) {
		$sanitized = array();

		foreach ( $data as $key => $value ) {
			$sanitized_key = sanitize_key( $key );
			
			if ( is_array( $value ) ) {
				$sanitized[ $sanitized_key ] = array_map( 'sanitize_text_field', $value );
			} elseif ( is_numeric( $value ) ) {
				$sanitized[ $sanitized_key ] = floatval( $value );
			} else {
				$sanitized[ $sanitized_key ] = sanitize_text_field( $value );
			}
		}

		return $sanitized;
	}

	/**
	 * Get validation rules for an assessment type
	 * 
	 * @param string $assessment_type Assessment type
	 * @return array Validation rules
	 */
	public function get_validation_rules( $assessment_type ) {
		return isset( $this->validation_rules[ $assessment_type ] ) ? $this->validation_rules[ $assessment_type ] : array();
	}

	/**
	 * Get all validation rules
	 * 
	 * @return array All validation rules
	 */
	public function get_all_validation_rules() {
		return $this->validation_rules;
	}
} 