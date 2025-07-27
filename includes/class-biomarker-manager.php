<?php
/**
 * ENNU Life Biomarker Data Manager
 * Handles lab data import, storage, and doctor recommendations
 *
 * @package ENNU_Life
 * @version 62.1.67
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Biomarker_Manager {

	public static function import_lab_results( $user_id, $lab_data ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'insufficient_permissions', 'Insufficient permissions to import lab data' );
		}

		$validated_data = self::validate_lab_data( $lab_data );

		if ( is_wp_error( $validated_data ) ) {
			return $validated_data;
		}

		update_user_meta( $user_id, 'ennu_biomarker_data', $validated_data );
		update_user_meta( $user_id, 'ennu_lab_import_date', current_time( 'mysql' ) );

		ENNU_Assessment_Scoring::calculate_and_save_all_user_scores( $user_id, true );

		error_log( 'ENNU Biomarker Manager: Imported lab results for user ' . $user_id );

		return array(
			'success'             => true,
			'biomarkers_imported' => count( $validated_data ),
			'import_date'         => current_time( 'mysql' ),
		);
	}

	public static function add_doctor_recommendations( $user_id, $recommendations ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'insufficient_permissions', 'Insufficient permissions to add recommendations' );
		}

		$existing_recommendations = get_user_meta( $user_id, 'ennu_doctor_recommendations', true );
		if ( ! is_array( $existing_recommendations ) ) {
			$existing_recommendations = array();
		}

		$new_recommendation = array(
			'date'              => current_time( 'mysql' ),
			'doctor_id'         => get_current_user_id(),
			'recommendations'   => $recommendations,
			'biomarker_targets' => $recommendations['biomarker_targets'] ?? array(),
			'lifestyle_advice'  => $recommendations['lifestyle_advice'] ?? '',
			'follow_up_date'    => $recommendations['follow_up_date'] ?? null,
		);

		$existing_recommendations[] = $new_recommendation;

		update_user_meta( $user_id, 'ennu_doctor_recommendations', $existing_recommendations );

		return array(
			'success'           => true,
			'recommendation_id' => count( $existing_recommendations ) - 1,
		);
	}

	public static function get_user_biomarkers( $user_id ) {
		// Get primary biomarker data
		$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		if ( ! is_array( $biomarker_data ) ) {
			$biomarker_data = array();
		}

		// Get auto-synced biomarker data (weight, BMI, height, etc.)
		$auto_sync_data = get_user_meta( $user_id, 'ennu_user_biomarkers', true );
		if ( is_array( $auto_sync_data ) && ! empty( $auto_sync_data ) ) {
			// Merge auto-sync data with primary biomarker data
			// Auto-sync data takes precedence for weight, BMI, height
			foreach ( $auto_sync_data as $biomarker_key => $biomarker_info ) {
				if ( isset( $biomarker_info['value'] ) && ! empty( $biomarker_info['value'] ) ) {
					$biomarker_data[ $biomarker_key ] = array(
						'value' => $biomarker_info['value'],
						'unit' => $biomarker_info['unit'] ?? '',
						'date' => $biomarker_info['date'] ?? current_time( 'mysql' ),
						'source' => $biomarker_info['source'] ?? 'auto_sync',
						'notes' => $biomarker_info['notes'] ?? 'Auto-synced from global fields',
					);
				}
			}
		}

		return $biomarker_data;
	}

	public static function get_doctor_recommendations( $user_id ) {
		return get_user_meta( $user_id, 'ennu_doctor_recommendations', true );
	}

	private static function validate_lab_data( $lab_data ) {
		if ( ! is_array( $lab_data ) ) {
			return new WP_Error( 'invalid_format', 'Lab data must be an array' );
		}

		$validated_data = array();

		foreach ( $lab_data as $biomarker_name => $biomarker_info ) {
			if ( ! is_array( $biomarker_info ) || ! array_key_exists( 'value', $biomarker_info ) ) {
				continue;
			}

			$validated_data[ $biomarker_name ] = array(
				'value'           => floatval( $biomarker_info['value'] ),
				'unit'            => sanitize_text_field( $biomarker_info['unit'] ?? '' ),
				'reference_range' => sanitize_text_field( $biomarker_info['reference_range'] ?? '' ),
				'test_date'       => sanitize_text_field( $biomarker_info['test_date'] ?? current_time( 'mysql' ) ),
				'lab_name'        => sanitize_text_field( $biomarker_info['lab_name'] ?? '' ),
			);
		}

		return $validated_data;
	}

	public static function calculate_new_life_score_projection( $user_id ) {
		$current_biomarkers     = self::get_user_biomarkers( $user_id );
		$doctor_recommendations = self::get_doctor_recommendations( $user_id );

		if ( empty( $current_biomarkers ) || empty( $doctor_recommendations ) ) {
			return null;
		}

		$latest_recommendations = end( $doctor_recommendations );
		$biomarker_targets      = $latest_recommendations['biomarker_targets'] ?? array();

		$projected_biomarkers = $current_biomarkers;
		foreach ( $biomarker_targets as $biomarker_name => $target_value ) {
			if ( isset( $projected_biomarkers[ $biomarker_name ] ) ) {
				$projected_biomarkers[ $biomarker_name ]['value'] = $target_value;
			}
		}

		if ( class_exists( 'ENNU_Objective_Engine' ) ) {
			$current_scores = get_user_meta( $user_id, 'ennu_average_pillar_scores', true );

			$objective_engine = new ENNU_Objective_Engine( $projected_biomarkers );
			$projected_scores = $objective_engine->apply_biomarker_actuality_adjustments( $current_scores );

			$projected_life_score = array_sum( $projected_scores ) / count( $projected_scores );

			return array(
				'projected_pillar_scores' => $projected_scores,
				'projected_life_score'    => $projected_life_score,
				'improvement_potential'   => $projected_life_score - ( get_user_meta( $user_id, 'ennu_life_score', true ) ?? 0 ),
			);
		}

		return null;
	}

	public static function get_biomarker_recommendations( $user_id ) {
		$user_symptoms      = ENNU_Assessment_Scoring::get_symptom_data_for_user( $user_id );
		$biomarker_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/biomarker-map.php';

		if ( ! file_exists( $biomarker_map_file ) ) {
			return array();
		}

		$biomarker_map          = require $biomarker_map_file;
		$recommended_biomarkers = array();

		$all_symptoms = array();
		foreach ( $user_symptoms as $assessment_type => $symptoms ) {
			if ( is_array( $symptoms ) ) {
				$all_symptoms = array_merge( $all_symptoms, $symptoms );
			}
		}

		$symptom_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/symptom-map.php';
		if ( file_exists( $symptom_map_file ) ) {
			$symptom_map = require $symptom_map_file;

			foreach ( $all_symptoms as $symptom ) {
				if ( isset( $symptom_map[ $symptom ] ) ) {
					foreach ( $symptom_map[ $symptom ] as $category => $weight_data ) {
						if ( isset( $biomarker_map[ $category ] ) ) {
							$recommended_biomarkers = array_merge( $recommended_biomarkers, $biomarker_map[ $category ] );
						}
					}
				}
			}
		}

		return array_unique( $recommended_biomarkers );
	}

	/**
	 * Get all available biomarkers in the system
	 * Returns all biomarkers defined in the biomarker map
	 *
	 * @return array Array of all available biomarker IDs
	 */
	public static function get_all_available_biomarkers() {
		$biomarker_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/biomarker-map.php';
		
		if (!file_exists($biomarker_map_file)) {
			return array();
		}
		
		$biomarker_map = require $biomarker_map_file;
		$all_biomarkers = array();
		
		// Flatten the nested array structure
		foreach ($biomarker_map as $vector => $biomarkers) {
			if (is_array($biomarkers)) {
				$all_biomarkers = array_merge($all_biomarkers, $biomarkers);
			}
		}
		
		// Remove duplicates and return unique biomarkers
		return array_unique($all_biomarkers);
	}

	/**
	 * Get comprehensive biomarker measurement data for display
	 * Always returns data for all biomarkers, even when user data is missing
	 *
	 * @param string $biomarker_id Biomarker identifier
	 * @param int $user_id User ID
	 * @return array Complete measurement data or placeholder data
	 */
	public static function get_biomarker_measurement_data($biomarker_id, $user_id) {
		// Get user's biomarker data
		$biomarker_data = self::get_user_biomarkers($user_id);
		
		// Check if user has data for this biomarker
		$has_user_data = isset($biomarker_data[$biomarker_id]);
		
		// Get recommended range (always available)
		$range_manager = new ENNU_Recommended_Range_Manager();
		$user_data = self::get_user_demographic_data($user_id);
		$recommended_range = $range_manager->get_recommended_range($biomarker_id, $user_data);
		
		if (isset($recommended_range['error'])) {
			return array(
				'error' => $recommended_range['error'],
				'biomarker_id' => $biomarker_id
			);
		}
		
		// Set current value and unit based on user data availability
		if ($has_user_data) {
			$data = $biomarker_data[$biomarker_id];
			$current_value = $data['value'];
			$unit = $data['unit'] ?? '';
			$percentage_position = self::calculate_percentage_position($current_value, $recommended_range['optimal_min'], $recommended_range['optimal_max']);
			$status = self::get_enhanced_status($current_value, $recommended_range);
		} else {
			// No user data - use placeholder values
			$current_value = null;
			$unit = $recommended_range['unit'] ?? '';
			$percentage_position = null;
			$status = array(
				'status' => 'no-data',
				'status_text' => 'No Data Available',
				'status_class' => 'no-data'
			);
		}
		
		// Get target value if exists
		$doctor_targets = get_user_meta($user_id, 'ennu_doctor_targets', true);
		$target_value = isset($doctor_targets[$biomarker_id]) ? $doctor_targets[$biomarker_id] : null;
		
		// Calculate AI target if no doctor target exists
		if (!$target_value && class_exists('ENNU_Biomarker_Target_Calculator')) {
			// Ensure we have valid range data for the target calculator
			if (isset($recommended_range['optimal_min']) && isset($recommended_range['optimal_max']) && 
				$recommended_range['optimal_min'] !== null && $recommended_range['optimal_max'] !== null) {
				
				// Use current value if available, otherwise use midpoint of optimal range
				$value_for_calculation = $current_value ?: ($recommended_range['optimal_min'] + (($recommended_range['optimal_max'] - $recommended_range['optimal_min']) / 2));
				
				$user_data = self::get_user_demographic_data($user_id);
				$target_data = ENNU_Biomarker_Target_Calculator::calculate_personalized_target(
					$biomarker_id,
					$value_for_calculation,
					$recommended_range,
					$user_data['age'],
					$user_data['gender']
				);
				if ($target_data && isset($target_data['target_value'])) {
					$target_value = $target_data['target_value'];
				}
			} else {
				// Fallback to midpoint of optimal range if range data is invalid
				$target_value = $recommended_range['optimal_min'] + (($recommended_range['optimal_max'] - $recommended_range['optimal_min']) / 2);
			}
		}
		
		// If still no target value, provide a default optimal target for educational purposes
		if (!$target_value && isset($recommended_range['optimal_min']) && isset($recommended_range['optimal_max'])) {
			$target_value = $recommended_range['optimal_min'] + (($recommended_range['optimal_max'] - $recommended_range['optimal_min']) / 2);
		}
		
		$target_position = $target_value ? self::calculate_percentage_position($target_value, $recommended_range['optimal_min'], $recommended_range['optimal_max']) : null;
		
		// Check for flags (may exist even without lab data)
		$flag_manager = new ENNU_Biomarker_Flag_Manager();
		$flags = $flag_manager->get_biomarker_flags($user_id, $biomarker_id);
		$has_flags = !empty($flags);
		
		// Get achievement status
		$achievement_status = self::get_achievement_status($current_value, $target_value, $recommended_range);
		
		// Get health vector
		$health_vector = self::get_biomarker_health_vector($biomarker_id);
		
		// Check for admin overrides
		$has_admin_override = self::check_admin_override($user_id, $biomarker_id);
		
		return array(
			'biomarker_id' => $biomarker_id,
			'current_value' => $current_value,
			'target_value' => $target_value,
			'unit' => $unit,
			'optimal_min' => $recommended_range['optimal_min'],
			'optimal_max' => $recommended_range['optimal_max'],
			'percentage_position' => $percentage_position,
			'target_position' => $target_position,
			'status' => $status,
			'has_flags' => $has_flags,
			'flags' => $flags,
			'achievement_status' => $achievement_status,
			'health_vector' => $health_vector,
			'has_admin_override' => $has_admin_override,
			'display_name' => $recommended_range['display_name'] ?? ucwords(str_replace('_', ' ', $biomarker_id)),
			'has_user_data' => $has_user_data
		);
	}
	
	/**
	 * Calculate percentage position of value within range
	 *
	 * @param float $value Current value
	 * @param float $min_range Minimum range value
	 * @param float $max_range Maximum range value
	 * @return float Percentage position (0-100)
	 */
	public static function calculate_percentage_position($value, $min_range, $max_range) {
		// Validate inputs - ensure they are numeric and convert to float
		if (!is_numeric($value) || !is_numeric($min_range) || !is_numeric($max_range)) {
			return 50; // Default to middle if any value is not numeric
		}
		
		// Convert to float to ensure proper numeric operations
		$value = (float)$value;
		$min_range = (float)$min_range;
		$max_range = (float)$max_range;
		
		// Additional safety check after conversion
		if (!is_finite($value) || !is_finite($min_range) || !is_finite($max_range)) {
			return 50; // Default to middle if any value is not finite
		}
		
		if ($max_range <= $min_range) {
			return 50; // Default to middle if range is invalid
		}
		
		$position = (($value - $min_range) / ($max_range - $min_range)) * 100;
		
		// Clamp to 0-100 range
		return max(0, min(100, $position));
	}
	
	/**
	 * Get enhanced status with color coding
	 *
	 * @param float $value Current value
	 * @param array $range_data Range information
	 * @return array Status information
	 */
	public static function get_enhanced_status($value, $range_data) {
		$optimal_min = $range_data['optimal_min'];
		$optimal_max = $range_data['optimal_max'];
		$normal_min = $range_data['normal_min'] ?? $optimal_min;
		$normal_max = $range_data['normal_max'] ?? $optimal_max;
		
		// Determine status
		if ($value >= $optimal_min && $value <= $optimal_max) {
			$status = 'optimal';
			$status_text = 'Optimal';
			$status_class = 'optimal';
		} elseif ($value >= $normal_min && $value <= $normal_max) {
			$status = 'suboptimal';
			$status_text = $value < $optimal_min ? 'Below Optimal' : 'Above Optimal';
			$status_class = $value < $optimal_min ? 'below-optimal' : 'above-optimal';
		} else {
			$status = 'critical';
			$status_text = $value < $normal_min ? 'Below Normal' : 'Above Normal';
			$status_class = 'below-optimal';
		}
		
		return array(
			'status' => $status,
			'status_text' => $status_text,
			'status_class' => $status_class,
			'is_optimal' => $status === 'optimal',
			'is_critical' => $status === 'critical'
		);
	}
	
	/**
	 * Get achievement status for biomarker
	 *
	 * @param float $current_value Current biomarker value
	 * @param float|null $target_value Target value
	 * @param array $range_data Range information
	 * @return array Achievement status
	 */
	public static function get_achievement_status($current_value, $target_value, $range_data) {
		if (!$target_value) {
			return array(
				'status' => 'no-target',
				'text' => 'No Target Set',
				'icon_class' => 'in-progress'
			);
		}
		
		// If no current value but we have a target, it's an educational target
		if (!$current_value && $target_value) {
			return array(
				'status' => 'educational',
				'text' => 'Educational Target',
				'icon_class' => 'educational'
			);
		}
		
		// Check if target is achieved (within 5% tolerance)
		$tolerance = abs($target_value * 0.05);
		$is_achieved = abs($current_value - $target_value) <= $tolerance;
		
		if ($is_achieved) {
			return array(
				'status' => 'achieved',
				'text' => 'Target Achieved',
				'icon_class' => 'achieved'
			);
		} else {
			return array(
				'status' => 'in-progress',
				'text' => 'Working Toward Target',
				'icon_class' => 'in-progress'
			);
		}
	}
	
	/**
	 * Get health vector for biomarker
	 *
	 * @param string $biomarker_id Biomarker identifier
	 * @return string Health vector name
	 */
	public static function get_biomarker_health_vector($biomarker_id) {
		// Define biomarker to health vector mapping
		$vector_mapping = array(
			// Cardiovascular
			'total_cholesterol' => 'Cardiovascular Health',
			'hdl' => 'Cardiovascular Health',
			'ldl' => 'Cardiovascular Health',
			'triglycerides' => 'Cardiovascular Health',
			'crp' => 'Inflammatory Markers',
			'homocysteine' => 'Cardiovascular Health',
			'apob' => 'Cardiovascular Health',
			
			// Endocrine
			'testosterone' => 'Hormonal Balance',
			'estradiol' => 'Hormonal Balance',
			'cortisol' => 'Stress Response',
			'tsh' => 'Thyroid Function',
			't3' => 'Thyroid Function',
			't4' => 'Thyroid Function',
			'insulin' => 'Metabolic Health',
			'dhea_s' => 'Hormonal Balance',
			
			// Immune
			'wbc' => 'Immune Function',
			'esr' => 'Inflammatory Markers',
			'vitamin_d' => 'Immune Function',
			'zinc' => 'Immune Function',
			'selenium' => 'Immune Function',
			'glutathione' => 'Antioxidant Status',
			
			// Nutritional
			'vitamin_b12' => 'Nutritional Status',
			'folate' => 'Nutritional Status',
			'iron' => 'Nutritional Status',
			'magnesium' => 'Nutritional Status',
			'omega_3' => 'Nutritional Status',
			
			// Physical
			'creatine_kinase' => 'Muscle Function',
			'igf_1' => 'Growth & Recovery',
			
			// Cognitive
			'apoe_genotype' => 'Cognitive Health',
			
			// Longevity
			'telomere_length' => 'Cellular Aging',
			'nad_plus' => 'Cellular Energy'
		);
		
		return $vector_mapping[$biomarker_id] ?? 'General Health';
	}
	
	/**
	 * Check if biomarker has admin override
	 *
	 * @param int $user_id User ID
	 * @param string $biomarker_id Biomarker identifier
	 * @return bool True if admin override exists
	 */
	public static function check_admin_override($user_id, $biomarker_id) {
		$admin_overrides = get_user_meta($user_id, 'ennu_admin_biomarker_overrides', true);
		
		if (!is_array($admin_overrides)) {
			return false;
		}
		
		return isset($admin_overrides[$biomarker_id]);
	}
	
	/**
	 * Get user demographic data for range calculations
	 *
	 * @param int $user_id User ID
	 * @return array User demographic data
	 */
	private static function get_user_demographic_data($user_id) {
		$user_data = array();
		
		// Get user's age
		$birth_date = get_user_meta($user_id, 'ennu_birth_date', true);
		if ($birth_date) {
			$birth = new DateTime($birth_date);
			$now = new DateTime();
			$user_data['age'] = $now->diff($birth)->y;
		} else {
			$user_data['age'] = 35; // Default age
		}
		
		// Get user's gender
		$gender = get_user_meta($user_id, 'ennu_gender', true);
		$user_data['gender'] = $gender ?: 'male'; // Default gender
		
		return $user_data;
	}
}
