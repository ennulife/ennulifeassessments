<?php
/**
 * ENNU Smart Defaults Generator
 *
 * Generates reasonable score projections for incomplete assessments
 * to provide immediate all-score generation for users.
 *
 * @package ENNU_Life
 * @version 62.2.8
 * @author ENNU Life Development Team
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Smart_Defaults_Generator {

	/**
	 * Generate smart defaults for a user based on existing data
	 *
	 * @param int $user_id User ID
	 * @return array Smart defaults data
	 */
	public static function generate_defaults_for_user( $user_id ) {
		$defaults = array();

		$user_meta    = get_user_meta( $user_id );
		$age          = self::get_user_age( $user_id );
		$gender       = get_user_meta( $user_id, 'ennu_global_gender', true );
		$health_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );

		$defaults['age_group']    = self::categorize_age( $age );
		$defaults['gender']       = $gender ? $gender : 'unknown';
		$defaults['health_goals'] = is_array( $health_goals ) ? $health_goals : array();

		$defaults['baseline_scores']    = self::generate_baseline_scores( $age, $gender, $health_goals );
		$defaults['potential_scores']   = self::generate_potential_scores( $defaults['baseline_scores'], $health_goals );
		$defaults['completeness_level'] = self::calculate_data_completeness( $user_id );

		return $defaults;
	}

	/**
	 * Get user age from date of birth
	 *
	 * @param int $user_id User ID
	 * @return int Age in years
	 */
	private static function get_user_age( $user_id ) {
		$dob = get_user_meta( $user_id, 'ennu_global_date_of_birth', true );
		if ( empty( $dob ) ) {
			return 35;
		}

		$birth_date = new DateTime( $dob );
		$today      = new DateTime();
		$age        = $today->diff( $birth_date )->y;

		return max( 18, min( 100, $age ) );
	}

	/**
	 * Categorize age into groups for scoring
	 *
	 * @param int $age Age in years
	 * @return string Age category
	 */
	private static function categorize_age( $age ) {
		if ( $age < 25 ) {
			return 'young_adult';
		}
		if ( $age < 35 ) {
			return 'early_adult';
		}
		if ( $age < 45 ) {
			return 'mid_adult';
		}
		if ( $age < 55 ) {
			return 'mature_adult';
		}
		if ( $age < 65 ) {
			return 'pre_senior';
		}
		return 'senior';
	}

	/**
	 * Generate baseline scores based on demographics
	 *
	 * @param int $age User age
	 * @param string $gender User gender
	 * @param array $health_goals User health goals
	 * @return array Baseline scores
	 */
	private static function generate_baseline_scores( $age, $gender, $health_goals ) {
		$base_score = 6.5;

		if ( $age < 30 ) {
			$base_score += 0.5;
		} elseif ( $age > 50 ) {
			$base_score -= 0.3;
		}

		$pillar_scores = array(
			'mind'       => $base_score + ( wp_rand( -50, 50 ) / 100 ),
			'body'       => $base_score + ( wp_rand( -50, 50 ) / 100 ),
			'lifestyle'  => $base_score + ( wp_rand( -50, 50 ) / 100 ),
			'aesthetics' => $base_score + ( wp_rand( -50, 50 ) / 100 ),
		);

		foreach ( $pillar_scores as $pillar => $score ) {
			$pillar_scores[ $pillar ] = max( 3.0, min( 9.0, $score ) );
		}

		return $pillar_scores;
	}

	/**
	 * Generate potential scores based on health goals
	 *
	 * @param array $baseline_scores Baseline pillar scores
	 * @param array $health_goals User health goals
	 * @return array Potential scores
	 */
	private static function generate_potential_scores( $baseline_scores, $health_goals ) {
		$potential_scores = array();

		foreach ( $baseline_scores as $pillar => $score ) {
			$improvement = 1.5;

			if ( ! empty( $health_goals ) ) {
				$improvement += 0.5;
			}

			$potential_scores[ $pillar ] = min( 10.0, $score + $improvement );
		}

		return $potential_scores;
	}

	/**
	 * Calculate data completeness percentage
	 *
	 * @param int $user_id User ID
	 * @return float Completeness percentage (0-100)
	 */
	private static function calculate_data_completeness( $user_id ) {
		$required_fields = array(
			'ennu_global_gender',
			'ennu_global_date_of_birth',
			'ennu_global_height',
			'ennu_global_weight',
			'ennu_global_health_goals',
		);

		$completed = 0;
		foreach ( $required_fields as $field ) {
			$value = get_user_meta( $user_id, $field, true );
			if ( ! empty( $value ) ) {
				$completed++;
			}
		}

		$assessment_types = array(
			'welcome_assessment',
			'health_optimization_assessment',
			'testosterone_assessment',
			'hormone_assessment',
		);

		foreach ( $assessment_types as $assessment ) {
			$scores = get_user_meta( $user_id, 'ennu_' . $assessment . '_scores', true );
			if ( ! empty( $scores ) ) {
				$completed += 2;
			}
		}

		$total_possible = count( $required_fields ) + ( count( $assessment_types ) * 2 );

		return ( $completed / $total_possible ) * 100;
	}

	/**
	 * Generate immediate scores for new users
	 *
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type completed
	 * @return array Immediate score data
	 */
	public static function generate_immediate_scores( $user_id, $assessment_type ) {
		$defaults = self::generate_defaults_for_user( $user_id );

		$immediate_scores = array(
			'ennu_life_score'         => array_sum( $defaults['baseline_scores'] ) / 4,
			'pillar_scores'           => $defaults['baseline_scores'],
			'potential_life_score'    => array_sum( $defaults['potential_scores'] ) / 4,
			'potential_pillar_scores' => $defaults['potential_scores'],
			'profile_completeness'    => $defaults['completeness_level'],
			'assessment_completed'    => $assessment_type,
			'generated_at'            => current_time( 'mysql' ),
			'is_smart_default'        => true,
		);

		update_user_meta( $user_id, 'ennu_immediate_scores', $immediate_scores );
		update_user_meta( $user_id, 'ennu_smart_defaults_data', $defaults );

		return $immediate_scores;
	}
}
