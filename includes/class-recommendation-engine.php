<?php
/**
 * ENNU Life Recommendation Engine
 *
 * This class is responsible for generating personalized recommendations based on
 * a user's assessment results and health goals.
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Recommendation_Engine {

	private $user_id;
	private $assessment_data;
	private $recommendation_definitions;

	public function __construct( $user_id, $assessment_data, $recommendation_definitions ) {
		$this->user_id                    = $user_id;
		$this->assessment_data            = $assessment_data;
		$this->recommendation_definitions = $recommendation_definitions;
		error_log( "RecommendationEngine: Instantiated for user ID {$user_id}." );
	}

	public function generate() {
		error_log( 'RecommendationEngine: Starting recommendation generation.' );
		$recommendations = array(
			'low_scores'        => array(),
			'health_goals'      => array(),
			'triggered_vectors' => array(),
		);

		$low_score_threshold = $this->recommendation_definitions['low_score_threshold'] ?? 5.5;

		// Generate recommendations based on low category scores
		if ( isset( $this->assessment_data['category_scores'] ) ) {
			foreach ( $this->assessment_data['category_scores'] as $category => $score ) {
				if ( $score < $low_score_threshold ) {
					$recommendation_text             = str_replace( '{category}', $category, $this->recommendation_definitions['recommendations']['default_low_score'] );
					$recommendations['low_scores'][] = $recommendation_text;
					error_log( "RecommendationEngine: Added low score recommendation for category '{$category}'." );
				}
			}
		}

		// Generate recommendations based on health goals
		$health_goals = get_user_meta( $this->user_id, 'ennu_global_health_goals', true );
		if ( ! empty( $health_goals ) && is_array( $health_goals ) ) {
			foreach ( $health_goals as $goal ) {
				if ( isset( $this->recommendation_definitions['recommendations']['health_goals'][ $goal ] ) ) {
					$recommendations['health_goals'][] = $this->recommendation_definitions['recommendations']['health_goals'][ $goal ];
					error_log( "RecommendationEngine: Added health goal recommendation for goal '{$goal}'." );
				}
			}
		}

		// Generate recommendations based on triggered health vectors
		$health_opt_calculator = new ENNU_Health_Optimization_Calculator( $this->user_id, ENNU_Assessment_Scoring::get_all_definitions() );
		$triggered_vectors     = array_keys( $health_opt_calculator->get_triggered_vectors() );

		if ( ! empty( $triggered_vectors ) ) {
			foreach ( $triggered_vectors as $vector ) {
				if ( isset( $this->recommendation_definitions['recommendations']['triggered_vectors'][ $vector ] ) ) {
					$recommendations['triggered_vectors'][] = $this->recommendation_definitions['recommendations']['triggered_vectors'][ $vector ];
					error_log( "RecommendationEngine: Added triggered vector recommendation for vector '{$vector}'." );
				}
			}
		}

		error_log( 'RecommendationEngine: Final recommendations generated: ' . print_r( $recommendations, true ) );
		return $recommendations;
	}
}
