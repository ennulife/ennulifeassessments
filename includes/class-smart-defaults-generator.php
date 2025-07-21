<?php
/**
 * ENNU Smart Defaults Generator
 * Generates reasonable score projections for incomplete assessments
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Smart_Defaults_Generator {

	/**
	 * Generate smart defaults for a user based on existing data
	 *
	 * @param int $user_id User ID
	 * @return array Smart defaults for missing assessments
	 */
	public static function generate_defaults_for_user( $user_id ) {
		$existing_data = self::get_user_existing_data( $user_id );
		$demographics  = self::get_user_demographics( $user_id );

		$defaults = array();

		$assessment_types = array(
			'testosterone',
			'menopause',
			'ed_treatment',
			'weight_loss',
			'longevity',
			'sleep',
			'stress',
			'nutrition',
			'fitness',
			'cognitive',
		);

		foreach ( $assessment_types as $assessment_type ) {
			if ( ! self::user_has_assessment( $user_id, $assessment_type ) ) {
				$defaults[ $assessment_type ] = self::generate_assessment_defaults(
					$assessment_type,
					$existing_data,
					$demographics
				);
			}
		}

		return $defaults;
	}

	/**
	 * Generate defaults for a specific assessment
	 *
	 * @param string $assessment_type Assessment type
	 * @param array $existing_data User's existing assessment data
	 * @param array $demographics User demographics
	 * @return array Default scores and projections
	 */
	private static function generate_assessment_defaults( $assessment_type, $existing_data, $demographics ) {
		$base_scores             = self::get_base_scores_for_assessment( $assessment_type );
		$demographic_adjustments = self::calculate_demographic_adjustments( $demographics );
		$existing_data_influence = self::calculate_existing_data_influence( $existing_data );

		$defaults = array(
			'current_score'    => self::calculate_adjusted_score(
				$base_scores['current'],
				$demographic_adjustments,
				$existing_data_influence
			),
			'projected_score'  => self::calculate_adjusted_score(
				$base_scores['projected'],
				$demographic_adjustments,
				$existing_data_influence,
				0.15 // 15% improvement projection
			),
			'confidence_level' => self::calculate_confidence_level( $existing_data, $assessment_type ),
			'data_source'      => 'smart_defaults',
			'generated_at'     => current_time( 'mysql' ),
		);

		return $defaults;
	}

	/**
	 * Get user's existing assessment data
	 *
	 * @param int $user_id User ID
	 * @return array Existing assessment data
	 */
	private static function get_user_existing_data( $user_id ) {
		$existing_data = array();

		$assessment_types = array( 'testosterone', 'menopause', 'ed_treatment', 'weight_loss', 'longevity' );

		foreach ( $assessment_types as $type ) {
			$scores = get_user_meta( $user_id, 'ennu_' . $type . '_scores', true );
			if ( ! empty( $scores ) ) {
				$existing_data[ $type ] = $scores;
			}
		}

		$existing_data['health_goals'] = get_user_meta( $user_id, 'ennu_global_health_goals', true );
		$existing_data['symptoms']     = get_user_meta( $user_id, 'ennu_user_symptoms', true );
		$existing_data['biomarkers']   = get_user_meta( $user_id, 'ennu_user_biomarkers', true );

		return $existing_data;
	}

	/**
	 * Get user demographics
	 *
	 * @param int $user_id User ID
	 * @return array User demographics
	 */
	private static function get_user_demographics( $user_id ) {
		return array(
			'age'            => get_user_meta( $user_id, 'ennu_age', true ),
			'gender'         => get_user_meta( $user_id, 'ennu_gender', true ),
			'height'         => get_user_meta( $user_id, 'ennu_height', true ),
			'weight'         => get_user_meta( $user_id, 'ennu_weight', true ),
			'activity_level' => get_user_meta( $user_id, 'ennu_activity_level', true ),
		);
	}

	/**
	 * Check if user has completed an assessment
	 *
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @return bool Whether user has completed the assessment
	 */
	private static function user_has_assessment( $user_id, $assessment_type ) {
		$scores = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_scores', true );
		return ! empty( $scores );
	}

	/**
	 * Get base scores for an assessment type
	 *
	 * @param string $assessment_type Assessment type
	 * @return array Base current and projected scores
	 */
	private static function get_base_scores_for_assessment( $assessment_type ) {
		$base_scores = array(
			'testosterone' => array(
				'current'   => 65,
				'projected' => 78,
			),
			'menopause'    => array(
				'current'   => 62,
				'projected' => 75,
			),
			'ed_treatment' => array(
				'current'   => 58,
				'projected' => 72,
			),
			'weight_loss'  => array(
				'current'   => 60,
				'projected' => 76,
			),
			'longevity'    => array(
				'current'   => 68,
				'projected' => 80,
			),
			'sleep'        => array(
				'current'   => 64,
				'projected' => 77,
			),
			'stress'       => array(
				'current'   => 59,
				'projected' => 73,
			),
			'nutrition'    => array(
				'current'   => 66,
				'projected' => 79,
			),
			'fitness'      => array(
				'current'   => 63,
				'projected' => 76,
			),
			'cognitive'    => array(
				'current'   => 67,
				'projected' => 81,
			),
		);

		return isset( $base_scores[ $assessment_type ] )
			? $base_scores[ $assessment_type ]
			: array(
				'current'   => 65,
				'projected' => 78,
			);
	}

	/**
	 * Calculate demographic adjustments
	 *
	 * @param array $demographics User demographics
	 * @return float Adjustment factor
	 */
	private static function calculate_demographic_adjustments( $demographics ) {
		$adjustment = 0;

		$age = intval( $demographics['age'] );
		if ( $age < 30 ) {
			$adjustment += 0.05; // Younger users tend to have better baseline health
		} elseif ( $age > 50 ) {
			$adjustment -= 0.03; // Older users may have more health challenges
		}

		$activity_level = $demographics['activity_level'];
		if ( 'high' === $activity_level ) {
			$adjustment += 0.08;
		} elseif ( 'low' === $activity_level ) {
			$adjustment -= 0.05;
		}

		return $adjustment;
	}

	/**
	 * Calculate influence from existing data
	 *
	 * @param array $existing_data User's existing assessment data
	 * @return float Influence factor
	 */
	private static function calculate_existing_data_influence( $existing_data ) {
		if ( empty( $existing_data ) ) {
			return 0;
		}

		$total_scores = 0;
		$count        = 0;

		foreach ( $existing_data as $assessment_type => $data ) {
			if ( is_array( $data ) && isset( $data['overall_score'] ) ) {
				$total_scores += floatval( $data['overall_score'] );
				$count++;
			}
		}

		if ( $count === 0 ) {
			return 0;
		}

		$average_score = $total_scores / $count;

		return ( $average_score - 65 ) / 100;
	}

	/**
	 * Calculate adjusted score
	 *
	 * @param float $base_score Base score
	 * @param float $demographic_adj Demographic adjustment
	 * @param float $data_influence Existing data influence
	 * @param float $improvement_factor Optional improvement factor for projections
	 * @return int Adjusted score
	 */
	private static function calculate_adjusted_score( $base_score, $demographic_adj, $data_influence, $improvement_factor = 0 ) {
		$adjusted_score = $base_score + ( $base_score * $demographic_adj ) + ( $base_score * $data_influence ) + ( $base_score * $improvement_factor );

		$adjusted_score = max( 30, min( 95, $adjusted_score ) );

		return intval( round( $adjusted_score ) );
	}

	/**
	 * Calculate confidence level for generated defaults
	 *
	 * @param array $existing_data User's existing data
	 * @param string $assessment_type Assessment type
	 * @return string Confidence level (low, medium, high)
	 */
	private static function calculate_confidence_level( $existing_data, $assessment_type ) {
		$data_points = 0;

		if ( ! empty( $existing_data['health_goals'] ) ) {
			$data_points++;
		}
		if ( ! empty( $existing_data['symptoms'] ) ) {
			$data_points++;
		}
		if ( ! empty( $existing_data['biomarkers'] ) ) {
			$data_points += 2; // Biomarkers are more valuable
		}

		$related_assessments = 0;
		foreach ( $existing_data as $type => $data ) {
			if ( in_array( $type, array( 'testosterone', 'menopause', 'ed_treatment', 'weight_loss', 'longevity' ), true ) ) {
				$related_assessments++;
			}
		}

		$total_confidence_score = $data_points + $related_assessments;

		if ( $total_confidence_score >= 4 ) {
			return 'high';
		} elseif ( $total_confidence_score >= 2 ) {
			return 'medium';
		} else {
			return 'low';
		}
	}
}
