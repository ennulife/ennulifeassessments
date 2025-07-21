<?php
/**
 * ENNU Immediate Score Calculator
 *
 * Provides immediate all-score generation for users after any assessment completion.
 * Integrates with existing Four-Engine Scoring Symphony.
 *
 * @package ENNU_Life
 * @version 62.2.8
 * @author ENNU Life Development Team
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Immediate_Score_Calculator {

	/**
	 * Calculate immediate scores for a user after assessment completion
	 *
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type completed
	 * @param array $assessment_data Assessment response data
	 * @return array Immediate score data
	 */
	public static function calculate_immediate_scores( $user_id, $assessment_type, $assessment_data = array() ) {
		$existing_scores = self::get_existing_user_scores( $user_id );
		$smart_defaults  = ENNU_Smart_Defaults_Generator::generate_defaults_for_user( $user_id );

		if ( ! empty( $existing_scores['ennu_life_score'] ) ) {
			return self::update_existing_scores( $user_id, $assessment_type, $existing_scores );
		}

		return self::generate_initial_scores( $user_id, $assessment_type, $smart_defaults, $assessment_data );
	}

	/**
	 * Get existing user scores
	 *
	 * @param int $user_id User ID
	 * @return array Existing scores
	 */
	private static function get_existing_user_scores( $user_id ) {
		return array(
			'ennu_life_score' => get_user_meta( $user_id, 'ennu_life_score', true ),
			'pillar_scores'   => get_user_meta( $user_id, 'ennu_average_pillar_scores', true ),
			'potential_score' => get_user_meta( $user_id, 'ennu_potential_life_score', true ),
			'completeness'    => get_user_meta( $user_id, 'ennu_score_completeness', true ),
		);
	}

	/**
	 * Generate initial scores for new users
	 *
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @param array $smart_defaults Smart defaults data
	 * @param array $assessment_data Assessment data
	 * @return array Initial scores
	 */
	private static function generate_initial_scores( $user_id, $assessment_type, $smart_defaults, $assessment_data ) {
		$assessment_scores = self::calculate_assessment_specific_scores( $assessment_type, $assessment_data );

		$pillar_scores = array_merge( $smart_defaults['baseline_scores'], $assessment_scores['pillar_adjustments'] );

		$ennu_life_score = array_sum( $pillar_scores ) / 4;

		$potential_scores     = $smart_defaults['potential_scores'];
		$potential_life_score = array_sum( $potential_scores ) / 4;

		$profile_completeness = ENNU_Profile_Completeness_Tracker::calculate_completeness( $user_id );

		$immediate_scores = array(
			'ennu_life_score'         => round( $ennu_life_score, 2 ),
			'pillar_scores'           => $pillar_scores,
			'potential_life_score'    => round( $potential_life_score, 2 ),
			'potential_pillar_scores' => $potential_scores,
			'profile_completeness'    => $profile_completeness['overall_percentage'],
			'assessment_completed'    => $assessment_type,
			'generated_at'            => current_time( 'mysql' ),
			'is_immediate_generation' => true,
			'data_sources'            => array( $assessment_type ),
		);

		self::save_immediate_scores( $user_id, $immediate_scores );

		return $immediate_scores;
	}

	/**
	 * Update existing scores with new assessment data
	 *
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @param array $existing_scores Existing scores
	 * @return array Updated scores
	 */
	private static function update_existing_scores( $user_id, $assessment_type, $existing_scores ) {
		ENNU_Assessment_Scoring::calculate_and_save_all_user_scores( $user_id, true );

		$updated_scores = self::get_existing_user_scores( $user_id );

		$profile_completeness = ENNU_Profile_Completeness_Tracker::calculate_completeness( $user_id );

		$immediate_scores = array(
			'ennu_life_score'      => $updated_scores['ennu_life_score'],
			'pillar_scores'        => $updated_scores['pillar_scores'],
			'potential_life_score' => $updated_scores['potential_score'],
			'profile_completeness' => $profile_completeness['overall_percentage'],
			'assessment_completed' => $assessment_type,
			'updated_at'           => current_time( 'mysql' ),
			'is_score_update'      => true,
		);

		return $immediate_scores;
	}

	/**
	 * Calculate assessment-specific score adjustments
	 *
	 * @param string $assessment_type Assessment type
	 * @param array $assessment_data Assessment data
	 * @return array Score adjustments
	 */
	private static function calculate_assessment_specific_scores( $assessment_type, $assessment_data ) {
		$pillar_adjustments = array(
			'mind'       => 0,
			'body'       => 0,
			'lifestyle'  => 0,
			'aesthetics' => 0,
		);

		switch ( $assessment_type ) {
			case 'welcome_assessment':
				$pillar_adjustments['mind']      += 0.2;
				$pillar_adjustments['lifestyle'] += 0.3;
				break;

			case 'health_optimization_assessment':
				$pillar_adjustments['body'] += 0.5;
				$pillar_adjustments['mind'] += 0.3;
				break;

			case 'testosterone_assessment':
			case 'hormone_assessment':
				$pillar_adjustments['body'] += 0.4;
				$pillar_adjustments['mind'] += 0.2;
				break;

			case 'sleep_assessment':
				$pillar_adjustments['lifestyle'] += 0.5;
				$pillar_adjustments['mind']      += 0.3;
				break;

			case 'weight_loss_assessment':
				$pillar_adjustments['body']       += 0.6;
				$pillar_adjustments['aesthetics'] += 0.4;
				break;

			case 'skin_assessment':
			case 'hair_assessment':
				$pillar_adjustments['aesthetics'] += 0.5;
				$pillar_adjustments['lifestyle']  += 0.2;
				break;
		}

		return array(
			'pillar_adjustments' => $pillar_adjustments,
			'assessment_impact'  => array_sum( $pillar_adjustments ),
		);
	}

	/**
	 * Save immediate scores to user meta
	 *
	 * @param int $user_id User ID
	 * @param array $immediate_scores Immediate scores data
	 */
	private static function save_immediate_scores( $user_id, $immediate_scores ) {
		update_user_meta( $user_id, 'ennu_life_score', $immediate_scores['ennu_life_score'] );
		update_user_meta( $user_id, 'ennu_average_pillar_scores', $immediate_scores['pillar_scores'] );
		update_user_meta( $user_id, 'ennu_potential_life_score', $immediate_scores['potential_life_score'] );
		update_user_meta( $user_id, 'ennu_score_completeness', $immediate_scores['profile_completeness'] );
		update_user_meta( $user_id, 'ennu_immediate_scores_data', $immediate_scores );

		$score_history = get_user_meta( $user_id, 'ennu_life_score_history', true );
		if ( ! is_array( $score_history ) ) {
			$score_history = array();
		}

		$score_history[] = array(
			'score'      => $immediate_scores['ennu_life_score'],
			'date'       => current_time( 'mysql' ),
			'timestamp'  => time(),
			'source'     => 'immediate_generation',
			'assessment' => $immediate_scores['assessment_completed'],
		);

		if ( count( $score_history ) > 50 ) {
			$score_history = array_slice( $score_history, -50 );
		}

		update_user_meta( $user_id, 'ennu_life_score_history', $score_history );
	}

	/**
	 * Get immediate scores for dashboard display
	 *
	 * @param int $user_id User ID
	 * @return array Formatted scores for display
	 */
	public static function get_dashboard_scores( $user_id ) {
		$immediate_data = get_user_meta( $user_id, 'ennu_immediate_scores_data', true );
		$current_scores = self::get_existing_user_scores( $user_id );

		if ( empty( $current_scores['ennu_life_score'] ) ) {
			$welcome_scores = self::calculate_immediate_scores( $user_id, 'welcome_assessment' );
			return self::format_scores_for_dashboard( $welcome_scores );
		}

		return self::format_scores_for_dashboard( $current_scores );
	}

	/**
	 * Format scores for dashboard display
	 *
	 * @param array $scores Raw scores data
	 * @return array Formatted scores
	 */
	private static function format_scores_for_dashboard( $scores ) {
		$ennu_life_score = isset( $scores['ennu_life_score'] ) ? $scores['ennu_life_score'] : 0;
		$pillar_scores   = isset( $scores['pillar_scores'] ) ? $scores['pillar_scores'] : array();

		return array(
			'ennu_life_score' => array(
				'value'          => round( $ennu_life_score, 1 ),
				'interpretation' => ENNU_Assessment_Scoring::get_score_interpretation( $ennu_life_score ),
				'display_class'  => self::get_score_display_class( $ennu_life_score ),
			),
			'pillar_scores'   => array(
				'mind'       => array(
					'value' => round( isset( $pillar_scores['mind'] ) ? $pillar_scores['mind'] : 0, 1 ),
					'label' => 'Mind',
					'icon'  => 'brain',
				),
				'body'       => array(
					'value' => round( isset( $pillar_scores['body'] ) ? $pillar_scores['body'] : 0, 1 ),
					'label' => 'Body',
					'icon'  => 'heart',
				),
				'lifestyle'  => array(
					'value' => round( isset( $pillar_scores['lifestyle'] ) ? $pillar_scores['lifestyle'] : 0, 1 ),
					'label' => 'Lifestyle',
					'icon'  => 'activity',
				),
				'aesthetics' => array(
					'value' => round( isset( $pillar_scores['aesthetics'] ) ? $pillar_scores['aesthetics'] : 0, 1 ),
					'label' => 'Aesthetics',
					'icon'  => 'star',
				),
			),
			'potential_score' => isset( $scores['potential_life_score'] ) ? round( $scores['potential_life_score'], 1 ) : 0,
			'completeness'    => isset( $scores['profile_completeness'] ) ? round( $scores['profile_completeness'], 1 ) : 0,
		);
	}

	/**
	 * Get CSS class for score display
	 *
	 * @param float $score Score value
	 * @return string CSS class
	 */
	private static function get_score_display_class( $score ) {
		if ( $score >= 8.5 ) {
			return 'score-excellent';
		}
		if ( $score >= 7.0 ) {
			return 'score-good';
		}
		if ( $score >= 5.5 ) {
			return 'score-fair';
		}
		if ( $score >= 3.5 ) {
			return 'score-needs-attention';
		}
		return 'score-critical';
	}

	/**
	 * Trigger immediate score calculation after assessment submission
	 *
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @param array $form_data Form submission data
	 */
	public static function trigger_after_assessment( $user_id, $assessment_type, $form_data ) {
		$immediate_scores = self::calculate_immediate_scores( $user_id, $assessment_type, $form_data );

		do_action( 'ennu_immediate_scores_generated', $user_id, $assessment_type, $immediate_scores );

		return $immediate_scores;
	}
}
