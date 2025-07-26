<?php
/**
 * ENNU User Manager Service
 * Extracted from monolithic Enhanced Admin class
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_User_Manager {

	private $cache;

	public function __construct() {
		// ENNU_Score_Cache uses static methods, no instance needed
	}

	/**
	 * Get user assessment data with caching
	 */
	public function get_user_assessments( $user_id ) {
		// Try to get from cache first
		$cached = ENNU_Score_Cache::get_cached_score( $user_id, 'user_assessments' );

		if ( $cached !== false ) {
			return $cached['score_data'];
		}

		global $wpdb;
		$assessments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key, meta_value FROM {$wpdb->usermeta} 
             WHERE user_id = %d AND meta_key LIKE %s",
				$user_id,
				'%_calculated_score'
			)
		);

		// Cache the results
		ENNU_Score_Cache::cache_score( $user_id, 'user_assessments', $assessments );
		return $assessments;
	}

	/**
	 * Get user global data with validation
	 */
	public function get_user_global_data( $user_id ) {
		$global_keys = array(
			'ennu_global_health_goals',
			'ennu_global_gender',
			'ennu_global_height_weight',
			'ennu_global_date_of_birth',
			'ennu_global_exact_age',
			'ennu_global_age_range',
			'ennu_global_age_category',
		);

		$data = array();
		foreach ( $global_keys as $key ) {
			$value = get_user_meta( $user_id, $key, true );
			if ( ! empty( $value ) ) {
				$data[ $key ] = $this->validate_global_field( $key, $value );
			}
		}

		// Get comprehensive age data if DOB exists
		if ( ! empty( $data['ennu_global_date_of_birth'] ) ) {
			$age_data = ENNU_Age_Management_System::get_user_age_data( $user_id );
			if ( ! empty( $age_data ) ) {
				$data = array_merge( $data, $age_data );
			}
		}

		return $data;
	}

	/**
	 * Validate global field data
	 */
	private function validate_global_field( $key, $value ) {
		switch ( $key ) {
			case 'ennu_global_gender':
				return in_array( $value, array( 'male', 'female', 'other' ), true ) ? $value : 'other';

			case 'ennu_global_height_weight':
				if ( is_array( $value ) && isset( $value['ft'], $value['in'], $value['lbs'] ) ) {
					$ft = is_numeric( $value['ft'] ) && $value['ft'] >= 3 && $value['ft'] <= 8 ? (int) $value['ft'] : 5;
					$in = is_numeric( $value['in'] ) && $value['in'] >= 0 && $value['in'] <= 11 ? (int) $value['in'] : 6;
					$lbs = is_numeric( $value['lbs'] ) && $value['lbs'] >= 50 && $value['lbs'] <= 1000 ? (float) $value['lbs'] : 150;
					return array( 'ft' => $ft, 'in' => $in, 'lbs' => $lbs );
				}
				return array( 'ft' => 5, 'in' => 6, 'lbs' => 150 );

			case 'ennu_global_date_of_birth':
				return ENNU_Age_Management_System::is_valid_dob( $value ) ? $value : '';

			case 'ennu_global_exact_age':
				return is_numeric( $value ) && $value >= ENNU_Age_Management_System::MIN_AGE && $value <= ENNU_Age_Management_System::MAX_AGE ? (int) $value : 0;

			case 'ennu_global_age_range':
				return ENNU_Age_Management_System::get_age_range_label( $value ) ? $value : '';

			case 'ennu_global_age_category':
				return ENNU_Age_Management_System::get_age_category_label( $value ) ? $value : '';

			case 'ennu_global_health_goals':
				if ( is_array( $value ) ) {
					$valid_goals = array( 'weight_loss', 'muscle_gain', 'energy_boost', 'sleep_improvement', 'stress_reduction', 'hormone_balance', 'skin_health', 'hair_health', 'cognitive_function', 'longevity', 'athletic_performance' );
					return array_intersect( $value, $valid_goals );
				}
				return array();

			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Update user global data with validation
	 */
	public function update_user_global_data( $user_id, $data ) {
		$updated = array();

		foreach ( $data as $key => $value ) {
			$validated_value = $this->validate_global_field( $key, $value );
			update_user_meta( $user_id, $key, $validated_value );
			$updated[ $key ] = $validated_value;
		}

		// Handle age data updates
		if ( isset( $data['ennu_global_date_of_birth'] ) && ! empty( $data['ennu_global_date_of_birth'] ) ) {
			$age_data = ENNU_Age_Management_System::update_user_age_data( $user_id, $data['ennu_global_date_of_birth'] );
			if ( $age_data ) {
				$updated = array_merge( $updated, $age_data );
			}
		}

		ENNU_Score_Cache::invalidate_cache( $user_id );

		return $updated;
	}

	/**
	 * Update user age data specifically
	 *
	 * @param int $user_id User ID
	 * @param string $dob Date of birth
	 * @return array|false Updated age data or false if invalid
	 */
	public function update_user_age_data( $user_id, $dob ) {
		return ENNU_Age_Management_System::update_user_age_data( $user_id, $dob );
	}

	/**
	 * Get user statistics with caching
	 */
	public function get_user_stats( $user_id ) {
		// Try to get from cache first
		$cached = ENNU_Score_Cache::get_cached_score( $user_id, 'user_stats' );

		if ( $cached !== false ) {
			return $cached['score_data'];
		}

		$stats = array(
			'total_assessments'  => $this->count_user_assessments( $user_id ),
			'completion_rate'    => $this->calculate_completion_rate( $user_id ),
			'last_activity'      => $this->get_last_activity( $user_id ),
			'health_goals_count' => $this->count_health_goals( $user_id ),
		);

		// Cache the results
		ENNU_Score_Cache::cache_score( $user_id, 'user_stats', $stats );
		return $stats;
	}

	/**
	 * Count user assessments
	 */
	private function count_user_assessments( $user_id ) {
		global $wpdb;
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} 
             WHERE user_id = %d AND meta_key LIKE %s AND meta_value != ''",
				$user_id,
				'%_calculated_score'
			)
		);
	}

	/**
	 * Calculate completion rate
	 */
	private function calculate_completion_rate( $user_id ) {
		$total_assessments = 10;
		$completed         = $this->count_user_assessments( $user_id );
		return min( 100, round( ( $completed / $total_assessments ) * 100, 1 ) );
	}

	/**
	 * Get last activity timestamp
	 */
	private function get_last_activity( $user_id ) {
		global $wpdb;
		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MAX(umeta_id) FROM {$wpdb->usermeta} 
             WHERE user_id = %d AND meta_key LIKE %s",
				$user_id,
				'ennu_%'
			)
		);
	}

	/**
	 * Count health goals
	 */
	private function count_health_goals( $user_id ) {
		$goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
		return is_array( $goals ) ? count( $goals ) : 0;
	}
}
