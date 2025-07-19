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
            $cache_key = 'ennu_assessment_definitions_v1';
            $cached_definitions = get_transient( $cache_key );
            
            if ( false !== $cached_definitions && isset( $cached_definitions['definitions'] ) ) {
                self::$all_definitions = $cached_definitions['definitions'];
                return self::$all_definitions;
            }
            
            $assessment_files = glob( ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessments/*.php' );
            $file_mod_times = array();
            
            foreach ( $assessment_files as $file ) {
                $assessment_key = basename( $file, '.php' );
                self::$all_definitions[ $assessment_key ] = require $file;
                $file_mod_times[ $file ] = filemtime( $file );
            }
            
            $cache_data = array(
                'definitions' => self::$all_definitions,
                'file_mod_times' => $file_mod_times,
                'cached_at' => time()
            );
            
            set_transient( $cache_key, $cache_data, 12 * HOUR_IN_SECONDS );
            error_log( 'ENNU Caching: Assessment definitions loaded from files and cached' );
        }
        return self::$all_definitions;
    }

    public static function get_health_pillar_map() {
        if ( empty( self::$pillar_map ) ) {
            $cache_key = 'ennu_pillar_map_v1';
            $cached_pillar_map = get_transient( $cache_key );
            
            if ( false !== $cached_pillar_map && isset( $cached_pillar_map['pillar_map'] ) ) {
                self::$pillar_map = $cached_pillar_map['pillar_map'];
                return self::$pillar_map;
            }
            
            $pillar_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/scoring/pillar-map.php';
            if ( file_exists( $pillar_map_file ) ) {
                self::$pillar_map = require $pillar_map_file;
                
                $cache_data = array(
                    'pillar_map' => self::$pillar_map,
                    'file_mod_time' => filemtime( $pillar_map_file ),
                    'cached_at' => time()
                );
                
                set_transient( $cache_key, $cache_data, 12 * HOUR_IN_SECONDS );
                error_log( 'ENNU Caching: Pillar map loaded from file and cached' );
            }
        }
        return self::$pillar_map;
    }

    public static function get_health_goal_definitions() {
        static $goal_definitions = null;
        
        if ( null === $goal_definitions ) {
            $cache_key = 'ennu_health_goal_definitions_v1';
            $cached_goals = get_transient( $cache_key );
            
            if ( false !== $cached_goals && isset( $cached_goals['goal_definitions'] ) ) {
                $goal_definitions = $cached_goals['goal_definitions'];
                return $goal_definitions;
            }
            
            $goal_definitions_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/scoring/health-goals.php';
            if ( file_exists( $goal_definitions_file ) ) {
                $goal_definitions = require $goal_definitions_file;
                
                $cache_data = array(
                    'goal_definitions' => $goal_definitions,
                    'file_mod_time' => filemtime( $goal_definitions_file ),
                    'cached_at' => time()
                );
                
                set_transient( $cache_key, $cache_data, 12 * HOUR_IN_SECONDS );
                error_log( 'ENNU Caching: Health goal definitions loaded from file and cached' );
            } else {
                $goal_definitions = array();
            }
        }
        
        return $goal_definitions;
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
        $goal_definitions = self::get_health_goal_definitions();

        // 1. Get all category scores from all completed assessments
        $all_category_scores = array();
        foreach ( $all_definitions as $assessment_type => $definition ) {
            if ( 'health_optimization_assessment' === $assessment_type ) continue;
            $category_scores = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_category_scores', true );
            if ( is_array( $category_scores ) && ! empty( $category_scores ) ) {
                $all_category_scores = array_merge( $all_category_scores, $category_scores );
            }
        }

        // 2. Calculate Base Pillar Scores (Quantitative Engine)
        $pillar_calculator = new ENNU_Pillar_Score_Calculator( $all_category_scores, $pillar_map );
        $base_pillar_scores = $pillar_calculator->calculate();

        // 3. Apply Intentionality Engine (Goal Alignment Boost) - NEW!
        $final_pillar_scores = $base_pillar_scores;
        $intentionality_data = array();
        
        if ( !empty( $health_goals ) && !empty( $goal_definitions ) && class_exists( 'ENNU_Intentionality_Engine' ) ) {
            $intentionality_engine = new ENNU_Intentionality_Engine( $health_goals, $goal_definitions, $base_pillar_scores );
            $final_pillar_scores = $intentionality_engine->apply_goal_alignment_boost();
            $intentionality_data = array(
                'boost_log' => $intentionality_engine->get_boost_log(),
                'boost_summary' => $intentionality_engine->get_boost_summary(),
                'user_explanation' => $intentionality_engine->get_user_explanation(),
            );
            
            error_log( 'ENNU Scoring: Applied Intentionality Engine boosts for user ' . $user_id );
        } else {
            error_log( 'ENNU Scoring: Skipped Intentionality Engine - missing goals, definitions, or class' );
        }

        // 4. Calculate Final ENNU Life Score with goal-boosted pillars
        $ennu_life_score_calculator = new ENNU_Life_Score_Calculator( $user_id, $final_pillar_scores, $health_goals, $goal_definitions );
        $ennu_life_score_data = $ennu_life_score_calculator->calculate();
        
        // 5. Save the results including intentionality data
        update_user_meta( $user_id, 'ennu_life_score', $ennu_life_score_data['ennu_life_score'] );
        update_user_meta( $user_id, 'ennu_pillar_score_data', $ennu_life_score_data['pillar_score_data'] );
        update_user_meta( $user_id, 'ennu_average_pillar_scores', $ennu_life_score_data['average_pillar_scores'] );
        update_user_meta( $user_id, 'ennu_intentionality_data', $intentionality_data );

        // 6. Calculate and Save Potential Score
        $potential_score_calculator = new ENNU_Potential_Score_Calculator( $final_pillar_scores, $health_goals, $goal_definitions );
        $potential_score = $potential_score_calculator->calculate();
        update_user_meta( $user_id, 'ennu_potential_life_score', $potential_score );

        // 7. Calculate and Save Score Completeness
        $completeness_calculator = new ENNU_Score_Completeness_Calculator( $user_id, $all_definitions );
        $completeness_score = $completeness_calculator->calculate();
        update_user_meta( $user_id, 'ennu_score_completeness', $completeness_score );
        
        // 8. Update score history for tracking
        $score_history = get_user_meta( $user_id, 'ennu_life_score_history', true );
        if ( ! is_array( $score_history ) ) {
            $score_history = array();
        }
        
        $score_history[] = array(
            'score' => $ennu_life_score_data['ennu_life_score'],
            'date' => current_time( 'mysql' ),
            'timestamp' => time(),
            'goal_boost_applied' => !empty( $intentionality_data['boost_summary']['boosts_applied'] ),
            'goals_count' => count( $health_goals ),
        );
        
        // Keep only last 50 entries
        if ( count( $score_history ) > 50 ) {
            $score_history = array_slice( $score_history, -50 );
        }
        
        update_user_meta( $user_id, 'ennu_life_score_history', $score_history );
        
        error_log( 'ENNU Scoring: Complete scoring calculation finished for user ' . $user_id . ' with final score: ' . $ennu_life_score_data['ennu_life_score'] );
    }

    /**
     * Get or calculate the user's average pillar scores.
     *
     * @param int $user_id
     * @return array
     */
    public static function calculate_average_pillar_scores( $user_id ) {
        $pillar_scores = get_user_meta( $user_id, 'ennu_average_pillar_scores', true );
        if ( is_array( $pillar_scores ) && ! empty( $pillar_scores ) ) {
            return $pillar_scores;
        }
        $all_definitions = self::get_all_definitions();
        $pillar_map = self::get_health_pillar_map();
        $all_category_scores = array();
        foreach ( $all_definitions as $assessment_type => $definition ) {
            if ( 'health_optimization_assessment' === $assessment_type ) continue;
            $category_scores = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_category_scores', true );
            if ( is_array( $category_scores ) && ! empty( $category_scores ) ) {
                $all_category_scores = array_merge( $all_category_scores, $category_scores );
            }
        }
        $pillar_calculator = new ENNU_Pillar_Score_Calculator( $all_category_scores, $pillar_map );
        $base_pillar_scores = $pillar_calculator->calculate();
        // Save for future use
        update_user_meta( $user_id, 'ennu_average_pillar_scores', $base_pillar_scores );
        return $base_pillar_scores;
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

	/**
	 * Get symptom data for a specific user from qualitative assessments.
	 *
	 * @param int $user_id The user ID
	 * @return array Array of symptom data organized by assessment type
	 */
	public static function get_symptom_data_for_user( $user_id ) {
		$symptom_data = array();
		
		// Get symptom data from health optimization assessment
		$health_opt_symptoms = get_user_meta( $user_id, 'ennu_health_optimization_symptoms', true );
		if ( ! empty( $health_opt_symptoms ) && is_array( $health_opt_symptoms ) ) {
			$symptom_data['health_optimization'] = $health_opt_symptoms;
		}
		
		// Get symptom data from other qualitative assessments
		$qualitative_assessments = array(
			'testosterone' => 'ennu_testosterone_symptoms',
			'hormone' => 'ennu_hormone_symptoms',
			'menopause' => 'ennu_menopause_symptoms',
			'ed_treatment' => 'ennu_ed_treatment_symptoms',
			'skin' => 'ennu_skin_symptoms',
			'hair' => 'ennu_hair_symptoms',
			'sleep' => 'ennu_sleep_symptoms',
			'weight_loss' => 'ennu_weight_loss_symptoms'
		);
		
		foreach ( $qualitative_assessments as $assessment_type => $meta_key ) {
			$assessment_symptoms = get_user_meta( $user_id, $meta_key, true );
			if ( ! empty( $assessment_symptoms ) && is_array( $assessment_symptoms ) ) {
				$symptom_data[ $assessment_type ] = $assessment_symptoms;
			}
		}
		
		// Get qualitative question responses that might contain symptoms
		$qualitative_responses = get_user_meta( $user_id, 'ennu_qualitative_responses', true );
		if ( ! empty( $qualitative_responses ) && is_array( $qualitative_responses ) ) {
			$symptom_data['qualitative'] = $qualitative_responses;
		}
		
		return $symptom_data;
	}

    public static function clear_configuration_cache() {
        delete_transient( 'ennu_assessment_definitions_v1' );
        delete_transient( 'ennu_pillar_map_v1' );
        delete_transient( 'ennu_health_goal_definitions_v1' );
        
        self::$all_definitions = array();
        self::$pillar_map = array();
        
        error_log( 'ENNU Caching: All configuration caches cleared' );
        
        return array(
            'success' => true,
            'message' => 'Configuration caches cleared successfully',
            'cleared_caches' => array(
                'assessment_definitions',
                'pillar_map', 
                'health_goal_definitions'
            )
        );
    }
}

