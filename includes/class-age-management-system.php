<?php
/**
 * ENNU Age Management System
 * Comprehensive age data management with auto-calculation capabilities
 *
 * @package ENNU_Life
 * @version 62.11.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Age_Management_System {

	/**
	 * Age range definitions for clinical categorization
	 */
	const AGE_RANGES = array(
		'18-25' => array( 'min' => 18, 'max' => 25, 'label' => 'Young Adult (18-25)' ),
		'26-35' => array( 'min' => 26, 'max' => 35, 'label' => 'Young Adult (26-35)' ),
		'36-45' => array( 'min' => 36, 'max' => 45, 'label' => 'Middle Age (36-45)' ),
		'46-55' => array( 'min' => 46, 'max' => 55, 'label' => 'Middle Age (46-55)' ),
		'56-65' => array( 'min' => 56, 'max' => 65, 'label' => 'Pre-Senior (56-65)' ),
		'66-75' => array( 'min' => 66, 'max' => 75, 'label' => 'Senior (66-75)' ),
		'76+'   => array( 'min' => 76, 'max' => 999, 'label' => 'Elderly (76+)' ),
	);

	/**
	 * Clinical age categories
	 */
	const AGE_CATEGORIES = array(
		'young_adult' => array( 'min' => 18, 'max' => 35, 'label' => 'Young Adult' ),
		'middle_age'  => array( 'min' => 36, 'max' => 55, 'label' => 'Middle Age' ),
		'pre_senior'  => array( 'min' => 56, 'max' => 65, 'label' => 'Pre-Senior' ),
		'senior'      => array( 'min' => 66, 'max' => 75, 'label' => 'Senior' ),
		'elderly'     => array( 'min' => 76, 'max' => 999, 'label' => 'Elderly' ),
	);

	/**
	 * Minimum age requirement
	 */
	const MIN_AGE = 18;

	/**
	 * Maximum age for validation
	 */
	const MAX_AGE = 120;

	/**
	 * Calculate exact age from date of birth
	 *
	 * @param string $dob Date of birth in Y-m-d format
	 * @return int|false Age in years or false if invalid
	 */
	public static function calculate_exact_age( $dob ) {
		if ( empty( $dob ) || ! self::is_valid_dob( $dob ) ) {
			return false;
		}

		try {
			$birth_date = new DateTime( $dob );
			$current_date = new DateTime();
			$age = $current_date->diff( $birth_date )->y;

			// Validate age range
			if ( $age < self::MIN_AGE || $age > self::MAX_AGE ) {
				return false;
			}

			return $age;
		} catch ( Exception $e ) {
			error_log( "ENNU Age Management: Error calculating age from DOB: " . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Get age range from exact age
	 *
	 * @param int $age Exact age
	 * @return string|false Age range key or false if invalid
	 */
	public static function get_age_range( $age ) {
		if ( ! is_numeric( $age ) || $age < self::MIN_AGE || $age > self::MAX_AGE ) {
			return false;
		}

		foreach ( self::AGE_RANGES as $range_key => $range_data ) {
			if ( $age >= $range_data['min'] && $age <= $range_data['max'] ) {
				return $range_key;
			}
		}

		return false;
	}

	/**
	 * Get age category from exact age
	 *
	 * @param int $age Exact age
	 * @return string|false Age category key or false if invalid
	 */
	public static function get_age_category( $age ) {
		if ( ! is_numeric( $age ) || $age < self::MIN_AGE || $age > self::MAX_AGE ) {
			return false;
		}

		foreach ( self::AGE_CATEGORIES as $category_key => $category_data ) {
			if ( $age >= $category_data['min'] && $age <= $category_data['max'] ) {
				return $category_key;
			}
		}

		return false;
	}

	/**
	 * Get age range label
	 *
	 * @param string $range_key Age range key
	 * @return string|false Age range label or false if invalid
	 */
	public static function get_age_range_label( $range_key ) {
		return isset( self::AGE_RANGES[ $range_key ] ) ? self::AGE_RANGES[ $range_key ]['label'] : false;
	}

	/**
	 * Get age category label
	 *
	 * @param string $category_key Age category key
	 * @return string|false Age category label or false if invalid
	 */
	public static function get_age_category_label( $category_key ) {
		return isset( self::AGE_CATEGORIES[ $category_key ] ) ? self::AGE_CATEGORIES[ $category_key ]['label'] : false;
	}

	/**
	 * Validate date of birth
	 *
	 * @param string $dob Date of birth
	 * @return bool True if valid, false otherwise
	 */
	public static function is_valid_dob( $dob ) {
		if ( empty( $dob ) ) {
			return false;
		}

		// Check if it's a valid date format
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $dob ) ) {
			return false;
		}

		try {
			$birth_date = new DateTime( $dob );
			$current_date = new DateTime();

			// Check if date is in the future
			if ( $birth_date > $current_date ) {
				return false;
			}

			// Check if date is too far in the past (older than MAX_AGE)
			$max_birth_date = clone $current_date;
			$max_birth_date->sub( new DateInterval( 'P' . self::MAX_AGE . 'Y' ) );
			if ( $birth_date < $max_birth_date ) {
				return false;
			}

			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Get comprehensive age data for a user
	 *
	 * @param int $user_id User ID
	 * @return array Age data or empty array if not available
	 */
	public static function get_user_age_data( $user_id ) {
		$dob = get_user_meta( $user_id, 'ennu_global_date_of_birth', true );
		
		if ( empty( $dob ) ) {
			return array();
		}

		$exact_age = self::calculate_exact_age( $dob );
		if ( $exact_age === false ) {
			return array();
		}

		$age_range = self::get_age_range( $exact_age );
		$age_category = self::get_age_category( $exact_age );

		return array(
			'date_of_birth' => $dob,
			'exact_age'     => $exact_age,
			'age_range'     => $age_range,
			'age_range_label' => self::get_age_range_label( $age_range ),
			'age_category'  => $age_category,
			'age_category_label' => self::get_age_category_label( $age_category ),
			'is_valid'      => true,
		);
	}

	/**
	 * Update user age data from date of birth
	 *
	 * @param int $user_id User ID
	 * @param string $dob Date of birth
	 * @return array Updated age data or false if invalid
	 */
	public static function update_user_age_data( $user_id, $dob ) {
		if ( ! self::is_valid_dob( $dob ) ) {
			return false;
		}

		$exact_age = self::calculate_exact_age( $dob );
		if ( $exact_age === false ) {
			return false;
		}

		$age_range = self::get_age_range( $exact_age );
		$age_category = self::get_age_category( $exact_age );

		// Update all age-related user meta
		update_user_meta( $user_id, 'ennu_global_date_of_birth', $dob );
		update_user_meta( $user_id, 'ennu_global_exact_age', $exact_age );
		update_user_meta( $user_id, 'ennu_global_age_range', $age_range );
		update_user_meta( $user_id, 'ennu_global_age_category', $age_category );

		// Clear any cached data
		wp_cache_delete( $user_id, 'user_meta' );

		return array(
			'date_of_birth' => $dob,
			'exact_age'     => $exact_age,
			'age_range'     => $age_range,
			'age_range_label' => self::get_age_range_label( $age_range ),
			'age_category'  => $age_category,
			'age_category_label' => self::get_age_category_label( $age_category ),
			'is_valid'      => true,
		);
	}

	/**
	 * Get age-adjusted biomarker reference ranges
	 *
	 * @param string $biomarker Biomarker name
	 * @param int $age Exact age
	 * @param string $gender Gender (male/female)
	 * @return array|false Reference ranges or false if not available
	 */
	public static function get_age_adjusted_ranges( $biomarker, $age, $gender = 'male' ) {
		// This will be expanded with actual biomarker reference ranges
		// For now, return a placeholder structure
		return array(
			'biomarker' => $biomarker,
			'age'       => $age,
			'gender'    => $gender,
			'age_range' => self::get_age_range( $age ),
			'category'  => self::get_age_category( $age ),
		);
	}

	/**
	 * Get age-specific clinical recommendations
	 *
	 * @param int $age Exact age
	 * @param string $assessment_type Assessment type
	 * @return array Age-specific recommendations
	 */
	public static function get_age_specific_recommendations( $age, $assessment_type ) {
		$age_category = self::get_age_category( $age );
		
		$recommendations = array(
			'young_adult' => array(
				'general' => 'Focus on establishing healthy habits and preventive care.',
				'priorities' => array( 'nutrition', 'exercise', 'sleep', 'stress_management' ),
			),
			'middle_age' => array(
				'general' => 'Monitor key biomarkers and address age-related changes.',
				'priorities' => array( 'hormone_balance', 'cardiovascular_health', 'bone_health', 'metabolic_health' ),
			),
			'pre_senior' => array(
				'general' => 'Focus on maintaining function and preventing decline.',
				'priorities' => array( 'cognitive_health', 'muscle_mass', 'bone_density', 'cardiovascular_health' ),
			),
			'senior' => array(
				'general' => 'Optimize quality of life and manage chronic conditions.',
				'priorities' => array( 'mobility', 'cognitive_function', 'nutrition', 'social_connection' ),
			),
			'elderly' => array(
				'general' => 'Maintain independence and address specific geriatric concerns.',
				'priorities' => array( 'fall_prevention', 'medication_management', 'nutrition', 'cognitive_health' ),
			),
		);

		return isset( $recommendations[ $age_category ] ) ? $recommendations[ $age_category ] : array();
	}

	/**
	 * Format age display for user interface
	 *
	 * @param int $age Exact age
	 * @return string Formatted age display
	 */
	public static function format_age_display( $age ) {
		$age_range = self::get_age_range( $age );
		$age_category = self::get_age_category( $age );

		$display = sprintf( '%d years', $age );
		
		if ( $age_range ) {
			$display .= sprintf( ' (Age Range: %s)', self::get_age_range_label( $age_range ) );
		}
		
		if ( $age_category ) {
			$display .= sprintf( ', Category: %s', self::get_age_category_label( $age_category ) );
		}

		return $display;
	}

	/**
	 * Get age validation error message
	 *
	 * @param string $dob Date of birth
	 * @return string Error message or empty string if valid
	 */
	public static function get_validation_error( $dob ) {
		if ( empty( $dob ) ) {
			return 'Date of birth is required.';
		}

		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $dob ) ) {
			return 'Please enter a valid date in YYYY-MM-DD format.';
		}

		try {
			$birth_date = new DateTime( $dob );
			$current_date = new DateTime();

			if ( $birth_date > $current_date ) {
				return 'Date of birth cannot be in the future.';
			}

			$age = $current_date->diff( $birth_date )->y;

			if ( $age < self::MIN_AGE ) {
				return sprintf( 'You must be at least %d years old to use this service.', self::MIN_AGE );
			}

			if ( $age > self::MAX_AGE ) {
				return sprintf( 'Please enter a valid date of birth (maximum age: %d years).', self::MAX_AGE );
			}

			return '';
		} catch ( Exception $e ) {
			return 'Please enter a valid date of birth.';
		}
	}

	/**
	 * Initialize age management system
	 */
	public static function init() {
		// Add hooks for automatic age calculations
		add_action( 'user_register', array( __CLASS__, 'maybe_calculate_age' ), 10, 1 );
		add_action( 'profile_update', array( __CLASS__, 'maybe_calculate_age' ), 10, 1 );
		add_action( 'updated_user_meta', array( __CLASS__, 'maybe_calculate_age_from_meta' ), 10, 4 );
	}

	/**
	 * Maybe calculate age when user is created or updated
	 *
	 * @param int $user_id User ID
	 */
	public static function maybe_calculate_age( $user_id ) {
		$dob = get_user_meta( $user_id, 'ennu_global_date_of_birth', true );
		if ( ! empty( $dob ) ) {
			self::update_user_age_data( $user_id, $dob );
		}
	}

	/**
	 * Maybe calculate age when DOB meta is updated
	 *
	 * @param int $meta_id Meta ID
	 * @param int $user_id User ID
	 * @param string $meta_key Meta key
	 * @param mixed $meta_value Meta value
	 */
	public static function maybe_calculate_age_from_meta( $meta_id, $user_id, $meta_key, $meta_value ) {
		if ( $meta_key === 'ennu_global_date_of_birth' && ! empty( $meta_value ) ) {
			self::update_user_age_data( $user_id, $meta_value );
		}
	}
}

// Initialize the age management system
ENNU_Age_Management_System::init(); 