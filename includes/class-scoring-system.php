<?php
/**
 * ENNU Life Assessment Scoring System Orchestrator
 *
 * This class is the public API for the scoring system. It orchestrates the
 * individual calculator classes to produce the final scores and recommendations.
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
// phpcs:disable WordPress.Security.NonceVerification.Missing
// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_print_r

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Assessment_Scoring {

	private static $all_definitions = array();
    private static $pillar_map = array();

    public static function get_all_definitions() {
		if ( empty( self::$all_definitions ) ) {
            $assessment_files = glob( ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessments/*.php' );
            foreach ( $assessment_files as $file ) {
                $assessment_key = basename( $file, '.php' );
                self::$all_definitions[ $assessment_key ] = require $file;
            }
        }
        return self::$all_definitions;
    }

    public static function get_health_pillar_map() {
        if ( empty( self::$pillar_map ) ) {
            $pillar_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/scoring/pillar-map.php';
            if ( file_exists( $pillar_map_file ) ) {
                self::$pillar_map = require $pillar_map_file;
            }
        }
        return self::$pillar_map;
    }

    public static function calculate_scores_for_assessment( $assessment_type, $responses ) {
        self::get_all_definitions();

        $assessment_calculator = new ENNU_Assessment_Calculator( $assessment_type, $responses, self::$all_definitions );
        $overall_score = $assessment_calculator->calculate();

        $category_calculator = new ENNU_Category_Score_Calculator( $assessment_type, $responses, self::$all_definitions );
        $category_scores = $category_calculator->calculate();

        $pillar_calculator = new ENNU_Pillar_Score_Calculator( $category_scores, self::get_health_pillar_map() );
        $pillar_scores = $pillar_calculator->calculate();

		return array(
			'overall_score'   => $overall_score,
            'category_scores' => $category_scores,
            'pillar_scores'   => $pillar_scores,
        );
    }

    public static function calculate_and_save_all_user_scores( $user_id, $force_recalc = false ) {
        $all_definitions = self::get_all_definitions();
        $pillar_map = self::get_health_pillar_map();
        $health_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
        $goal_definitions_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/scoring/health-goals.php';
        $goal_definitions = file_exists($goal_definitions_file) ? require $goal_definitions_file : array();

        // 1. Get all category scores from all completed assessments
        $all_category_scores = array();
        foreach ( array_keys($all_definitions) as $assessment_type ) {
            if ( 'health_optimization_assessment' === $assessment_type ) continue;
            $category_scores = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_category_scores', true );
            if ( is_array( $category_scores ) && ! empty( $category_scores ) ) {
                $all_category_scores = array_merge( $all_category_scores, $category_scores );
            }
        }

        // 2. Calculate Base Pillar Scores
        $pillar_calculator = new ENNU_Pillar_Score_Calculator( $all_category_scores, $pillar_map );
        $base_pillar_scores = $pillar_calculator->calculate();

        // 3. Calculate Final ENNU Life Score and associated data
        $ennu_life_score_calculator = new ENNU_Life_Score_Calculator( $user_id, $base_pillar_scores, $health_goals, $goal_definitions );
        $ennu_life_score_data = $ennu_life_score_calculator->calculate();
        
        // 4. Save the results
        update_user_meta( $user_id, 'ennu_life_score', $ennu_life_score_data['ennu_life_score'] );
        update_user_meta( $user_id, 'ennu_pillar_score_data', $ennu_life_score_data['pillar_score_data'] );
        update_user_meta( $user_id, 'ennu_average_pillar_scores', $ennu_life_score_data['average_pillar_scores'] );

        // 5. Calculate and Save Potential Score
        $potential_score_calculator = new ENNU_Potential_Score_Calculator( $base_pillar_scores, $health_goals, $goal_definitions );
        $potential_score = $potential_score_calculator->calculate();
        update_user_meta( $user_id, 'ennu_potential_life_score', $potential_score );

        // 6. Calculate and Save Score Completeness
        $completeness_calculator = new ENNU_Score_Completeness_Calculator( $user_id, $all_definitions );
        $completeness_score = $completeness_calculator->calculate();
        update_user_meta( $user_id, 'ennu_score_completeness', $completeness_score );
    }

	public static function get_score_interpretation( $score ) {
		if ( $score >= 8.5 ) {
			return array(
				'level'       => 'Excellent',
				'color'       => '#10b981',
				'description' => 'Outstanding results expected with minimal intervention needed.',
			);
		} elseif ( $score >= 7.0 ) {
			return array(
				'level'       => 'Good',
				'color'       => '#3b82f6',
				'description' => 'Good foundation with some areas for optimization.',
			);
		} elseif ( $score >= 5.5 ) {
			return array(
				'level'       => 'Fair',
				'color'       => '#f59e0b',
				'description' => 'Moderate intervention recommended for best results.',
			);
		} elseif ( $score >= 3.5 ) {
			return array(
				'level'       => 'Needs Attention',
				'color'       => '#ef4444',
				'description' => 'Significant improvements recommended for optimal outcomes.',
			);
		} else {
			return array(
				'level'       => 'Critical',
				'color'       => '#dc2626',
				'description' => 'Immediate comprehensive intervention strongly recommended.',
			);
		}
	}
}

