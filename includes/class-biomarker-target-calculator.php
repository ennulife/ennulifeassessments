<?php
/**
 * ENNU Biomarker Target Calculator
 *
 * @package   ENNU_Life
 * @copyright Copyright (c) 2024, ENNU Life, https://ennulife.com
 * @license   GPL-3.0+
 * @since     62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Calculates personalized target values for biomarkers based on AI specialist data
 * and user demographics using linear transformation methods.
 */
class ENNU_Biomarker_Target_Calculator {

	/**
	 * Calculate personalized target value for a biomarker
	 *
	 * @param string $biomarker_key The biomarker identifier
	 * @param float  $current_value Current biomarker value
	 * @param array  $reference_range Reference range data from AI specialist
	 * @param int    $user_age User age
	 * @param string $user_gender User gender
	 * @return array Target value data with confidence score
	 */
	public static function calculate_personalized_target( $biomarker_key, $current_value, $reference_range, $user_age, $user_gender ) {
		
		// Validate inputs
		if ( empty( $biomarker_key ) || ! is_numeric( $current_value ) || empty( $reference_range ) ) {
			return array(
				'target_value' => null,
				'confidence_score' => 0,
				'calculation_method' => 'invalid_input',
				'reasoning' => 'Invalid input parameters'
			);
		}

		// Get optimal range based on demographics
		$optimal_range = self::get_demographic_optimal_range( $reference_range, $user_age, $user_gender );
		
		if ( empty( $optimal_range ) ) {
			return array(
				'target_value' => null,
				'confidence_score' => 0,
				'calculation_method' => 'no_optimal_range',
				'reasoning' => 'No optimal range available for demographics'
			);
		}

		$optimal_min = $optimal_range['optimal_min'];
		$optimal_max = $optimal_range['optimal_max'];
		$optimal_mid = ( $optimal_min + $optimal_max ) / 2;

		// Calculate target based on current value position
		$target_data = self::calculate_target_by_position( $current_value, $optimal_min, $optimal_max, $optimal_mid, $biomarker_key );

		// Apply biomarker-specific adjustments
		$target_data = self::apply_biomarker_specific_adjustments( $target_data, $biomarker_key, $user_age, $user_gender );

		// Calculate confidence score
		$target_data['confidence_score'] = self::calculate_confidence_score( $target_data, $reference_range, $user_age, $user_gender );

		return $target_data;
	}

	/**
	 * Get optimal range based on user demographics
	 *
	 * @param array  $reference_range Reference range data
	 * @param int    $user_age User age
	 * @param string $user_gender User gender
	 * @return array Optimal range for demographics
	 */
	private static function get_demographic_optimal_range( $reference_range, $user_age, $user_gender ) {
		
		// Handle both data structure formats
		$optimal_min = null;
		$optimal_max = null;
		
		if ( isset( $reference_range['ranges']['optimal_min'] ) && isset( $reference_range['ranges']['optimal_max'] ) ) {
			// New format with nested ranges
			$optimal_min = $reference_range['ranges']['optimal_min'];
			$optimal_max = $reference_range['ranges']['optimal_max'];
		} elseif ( isset( $reference_range['optimal_min'] ) && isset( $reference_range['optimal_max'] ) ) {
			// Direct format
			$optimal_min = $reference_range['optimal_min'];
			$optimal_max = $reference_range['optimal_max'];
		} else {
			error_log("ENNU DEBUG: Invalid reference range structure for target calculation");
			return array(
				'optimal_min' => 0,
				'optimal_max' => 1
			);
		}
		
		$optimal_range = array(
			'optimal_min' => $optimal_min,
			'optimal_max' => $optimal_max
		);

		// Apply age adjustments if available
		if ( ! empty( $reference_range['age_adjustments'] ) ) {
			$age_group = self::determine_age_group( $user_age );
			if ( isset( $reference_range['age_adjustments'][ $age_group ] ) ) {
				$age_adjustment = $reference_range['age_adjustments'][ $age_group ];
				$optimal_range['optimal_min'] = $age_adjustment['optimal_min'];
				$optimal_range['optimal_max'] = $age_adjustment['optimal_max'];
			}
		}

		// Apply gender adjustments if available
		if ( ! empty( $reference_range['gender_adjustments'] ) && isset( $reference_range['gender_adjustments'][ $user_gender ] ) ) {
			$gender_adjustment = $reference_range['gender_adjustments'][ $user_gender ];
			$optimal_range['optimal_min'] = $gender_adjustment['optimal_min'];
			$optimal_range['optimal_max'] = $gender_adjustment['optimal_max'];
		}

		return $optimal_range;
	}

	/**
	 * Determine age group for adjustments
	 *
	 * @param int $age User age
	 * @return string Age group
	 */
	private static function determine_age_group( $age ) {
		if ( $age < 18 ) {
			return 'young';
		} elseif ( $age < 65 ) {
			return 'adult';
		} else {
			return 'senior';
		}
	}

	/**
	 * Calculate target value based on current value position relative to optimal range
	 *
	 * @param float  $current_value Current biomarker value
	 * @param float  $optimal_min Optimal minimum
	 * @param float  $optimal_max Optimal maximum
	 * @param float  $optimal_mid Optimal midpoint
	 * @param string $biomarker_key Biomarker identifier
	 * @return array Target calculation data
	 */
	private static function calculate_target_by_position( $current_value, $optimal_min, $optimal_max, $optimal_mid, $biomarker_key ) {
		
		// Safety check for valid range values
		if ( $optimal_min === null || $optimal_max === null || $optimal_min === $optimal_max ) {
			error_log("ENNU DEBUG: Invalid range values for $biomarker_key - optimal_min: $optimal_min, optimal_max: $optimal_max");
			return array(
				'target_value' => $current_value,
				'calculation_method' => 'fallback_invalid_range',
				'reasoning' => 'Invalid range values, using current value as target',
				'optimal_min' => $optimal_min,
				'optimal_max' => $optimal_max,
				'current_position' => 0.5,
				'range_width' => 0
			);
		}
		
		$range_width = $optimal_max - $optimal_min;
		$position_in_range = ( $current_value - $optimal_min ) / $range_width;

		// Determine calculation strategy based on current position
		if ( $current_value < $optimal_min ) {
			// Below optimal - target the lower end of optimal range
			$target_value = $optimal_min + ( $range_width * 0.25 );
			$calculation_method = 'below_optimal_target_upper_quartile';
			$reasoning = 'Current value below optimal range, targeting upper quartile of optimal range for gradual improvement';
			
		} elseif ( $current_value > $optimal_max ) {
			// Above optimal - target the upper end of optimal range
			$target_value = $optimal_max - ( $range_width * 0.25 );
			$calculation_method = 'above_optimal_target_lower_quartile';
			$reasoning = 'Current value above optimal range, targeting lower quartile of optimal range for gradual reduction';
			
		} elseif ( $position_in_range < 0.3 ) {
			// Lower third of optimal range - target midpoint
			$target_value = $optimal_mid;
			$calculation_method = 'lower_optimal_target_midpoint';
			$reasoning = 'Current value in lower third of optimal range, targeting midpoint for balance';
			
		} elseif ( $position_in_range > 0.7 ) {
			// Upper third of optimal range - target midpoint
			$target_value = $optimal_mid;
			$calculation_method = 'upper_optimal_target_midpoint';
			$reasoning = 'Current value in upper third of optimal range, targeting midpoint for balance';
			
		} else {
			// Middle of optimal range - maintain current value
			$target_value = $current_value;
			$calculation_method = 'maintain_current_optimal';
			$reasoning = 'Current value in optimal range, maintaining current level';
		}

		// Apply safety bounds
		$target_value = max( $optimal_min * 0.8, min( $optimal_max * 1.2, $target_value ) );

		return array(
			'target_value' => round( $target_value, 2 ),
			'calculation_method' => $calculation_method,
			'reasoning' => $reasoning,
			'optimal_min' => $optimal_min,
			'optimal_max' => $optimal_max,
			'current_position' => $position_in_range,
			'range_width' => $range_width
		);
	}

	/**
	 * Apply biomarker-specific adjustments based on medical knowledge
	 *
	 * @param array  $target_data Target calculation data
	 * @param string $biomarker_key Biomarker identifier
	 * @param int    $user_age User age
	 * @param string $user_gender User gender
	 * @return array Adjusted target data
	 */
	private static function apply_biomarker_specific_adjustments( $target_data, $biomarker_key, $user_age, $user_gender ) {
		
		$adjustments = array();

		// Hormone-specific adjustments
		if ( strpos( $biomarker_key, 'testosterone' ) !== false ) {
			if ( $user_age > 40 ) {
				// Older males may benefit from slightly higher targets
				$target_data['target_value'] *= 1.05;
				$adjustments[] = 'Age-adjusted testosterone target (5% increase for age >40)';
			}
		}

		if ( strpos( $biomarker_key, 'vitamin_d' ) !== false || $biomarker_key === 'vitamin_d_25_oh' ) {
			// Vitamin D targets may be higher for optimal health
			$target_data['target_value'] = min( $target_data['target_value'] * 1.1, $target_data['optimal_max'] * 1.1 );
			$adjustments[] = 'Vitamin D optimization adjustment (10% increase for optimal health)';
		}

		if ( strpos( $biomarker_key, 'cortisol' ) !== false ) {
			// Cortisol targets should be conservative
			$target_data['target_value'] = max( $target_data['target_value'] * 0.95, $target_data['optimal_min'] * 0.95 );
			$adjustments[] = 'Cortisol conservative adjustment (5% reduction for safety)';
		}

		// Cardiovascular risk factor adjustments
		if ( in_array( $biomarker_key, array( 'ldl_cholesterol', 'triglycerides', 'apo_b' ) ) ) {
			// Target lower values for cardiovascular health
			$target_data['target_value'] = max( $target_data['target_value'] * 0.9, $target_data['optimal_min'] * 0.9 );
			$adjustments[] = 'Cardiovascular risk factor adjustment (10% reduction for heart health)';
		}

		if ( $biomarker_key === 'hdl_cholesterol' ) {
			// Target higher HDL values
			$target_data['target_value'] = min( $target_data['target_value'] * 1.1, $target_data['optimal_max'] * 1.1 );
			$adjustments[] = 'HDL optimization adjustment (10% increase for cardiovascular health)';
		}

		// Apply adjustments to reasoning
		if ( ! empty( $adjustments ) ) {
			$target_data['reasoning'] .= ' Additional adjustments: ' . implode( ', ', $adjustments );
		}

		return $target_data;
	}

	/**
	 * Calculate confidence score for the target value
	 *
	 * @param array  $target_data Target calculation data
	 * @param array  $reference_range Reference range data
	 * @param int    $user_age User age
	 * @param string $user_gender User gender
	 * @return float Confidence score (0-1)
	 */
	private static function calculate_confidence_score( $target_data, $reference_range, $user_age, $user_gender ) {
		
		$confidence = 0.8; // Base confidence

		// Reduce confidence if no demographic adjustments available
		if ( empty( $reference_range['age_adjustments'] ) && empty( $reference_range['gender_adjustments'] ) ) {
			$confidence -= 0.1;
		}

		// Reduce confidence for extreme positions
		if ( $target_data['current_position'] < 0 || $target_data['current_position'] > 1 ) {
			$confidence -= 0.2;
		}

		// Increase confidence for maintain_current_optimal method
		if ( $target_data['calculation_method'] === 'maintain_current_optimal' ) {
			$confidence += 0.1;
		}

		// Reduce confidence for biomarker-specific adjustments
		if ( strpos( $target_data['reasoning'], 'Additional adjustments' ) !== false ) {
			$confidence -= 0.05;
		}

		// Ensure confidence is within bounds
		return max( 0.1, min( 1.0, $confidence ) );
	}

	/**
	 * Generate target values for all biomarkers for a user
	 *
	 * @param int    $user_id User ID
	 * @param array  $current_values Current biomarker values
	 * @return array All target values with metadata
	 */
	public static function generate_all_targets_for_user( $user_id, $current_values = array() ) {
		
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return array();
		}

		$user_age = get_user_meta( $user_id, 'ennu_global_exact_age', true );
		$user_gender = get_user_meta( $user_id, 'ennu_global_gender', true );

		if ( empty( $user_age ) || empty( $user_gender ) ) {
			return array();
		}

		// Get AI specialist biomarker configurations
		$range_manager = new ENNU_Recommended_Range_Manager();
		$biomarker_config = $range_manager->get_biomarker_configuration();

		$all_targets = array();

		foreach ( $biomarker_config as $biomarker_key => $config ) {
			
			$current_value = isset( $current_values[ $biomarker_key ] ) ? $current_values[ $biomarker_key ] : null;
			
			if ( $current_value === null ) {
				// Skip if no current value available
				continue;
			}

			$target_data = self::calculate_personalized_target( 
				$biomarker_key, 
				$current_value, 
				$config, 
				$user_age, 
				$user_gender 
			);

			if ( $target_data['target_value'] !== null ) {
				$all_targets[ $biomarker_key ] = array(
					'target_value' => $target_data['target_value'],
					'confidence_score' => $target_data['confidence_score'],
					'calculation_method' => $target_data['calculation_method'],
					'reasoning' => $target_data['reasoning'],
					'current_value' => $current_value,
					'optimal_range' => array(
						'min' => $target_data['optimal_min'],
						'max' => $target_data['optimal_max']
					),
					'generated_at' => current_time( 'mysql' ),
					'user_id' => $user_id
				);
			}
		}

		return $all_targets;
	}

	/**
	 * Validate target value against safety bounds
	 *
	 * @param float  $target_value Target value
	 * @param array  $reference_range Reference range data
	 * @param string $biomarker_key Biomarker identifier
	 * @return array Validation result
	 */
	public static function validate_target_value( $target_value, $reference_range, $biomarker_key ) {
		
		$critical_min = $reference_range['ranges']['critical_min'] ?? $reference_range['ranges']['normal_min'];
		$critical_max = $reference_range['ranges']['critical_max'] ?? $reference_range['ranges']['normal_max'];

		$safety_min = $critical_min * 0.8;
		$safety_max = $critical_max * 1.2;

		$is_safe = ( $target_value >= $safety_min && $target_value <= $safety_max );
		$is_optimal = ( $target_value >= $reference_range['ranges']['optimal_min'] && $target_value <= $reference_range['ranges']['optimal_max'] );

		return array(
			'is_safe' => $is_safe,
			'is_optimal' => $is_optimal,
			'safety_bounds' => array( 'min' => $safety_min, 'max' => $safety_max ),
			'optimal_bounds' => array( 'min' => $reference_range['ranges']['optimal_min'], 'max' => $reference_range['ranges']['optimal_max'] ),
			'critical_bounds' => array( 'min' => $critical_min, 'max' => $critical_max )
		);
	}
} 