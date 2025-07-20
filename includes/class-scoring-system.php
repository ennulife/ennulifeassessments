<?php
/**
 * ENNU Life Scoring System
 * Handles all scoring calculations and data management
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Scoring_System {
    
    private static $all_definitions = array();
    private static $pillar_map = array();

    public static function get_all_definitions() {
        if ( ! empty( self::$all_definitions ) ) {
            return self::$all_definitions;
        }
        
        $cached_definitions = get_transient( 'ennu_assessment_definitions_v1' );
        if ( false !== $cached_definitions ) {
            self::$all_definitions = $cached_definitions;
            return self::$all_definitions;
        }
        
        $definitions = array();
        $config_dir = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessments/';
        
        if ( is_dir( $config_dir ) ) {
            $files = glob( $config_dir . '*.php' );
            foreach ( $files as $file ) {
                $assessment_type = basename( $file, '.php' );
                $definition = require $file;
                if ( is_array( $definition ) ) {
                    $definitions[ $assessment_type ] = $definition;
                }
            }
        }
        
        self::$all_definitions = $definitions;
        set_transient( 'ennu_assessment_definitions_v1', $definitions, 12 * HOUR_IN_SECONDS );
        
        return self::$all_definitions;
    }

    public static function get_health_pillar_map() {
        if ( ! empty( self::$pillar_map ) ) {
            return self::$pillar_map;
        }
        
        $cached_pillar_map = get_transient( 'ennu_pillar_map_v1' );
        if ( false !== $cached_pillar_map ) {
            self::$pillar_map = $cached_pillar_map;
            return self::$pillar_map;
        }
        
        $pillar_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/scoring/pillar-map.php';
        if ( file_exists( $pillar_map_file ) ) {
            $pillar_map = require $pillar_map_file;
            if ( is_array( $pillar_map ) ) {
                self::$pillar_map = $pillar_map;
                set_transient( 'ennu_pillar_map_v1', $pillar_map, 12 * HOUR_IN_SECONDS );
                return self::$pillar_map;
            }
        }
        
        $default_pillar_map = array(
            'Mind' => array(
                'categories' => array( 'cognitive_function', 'mental_clarity', 'mood_stability' ),
                'weight' => 0.25
            ),
            'Body' => array(
                'categories' => array( 'cardiovascular_health', 'metabolic_function', 'hormonal_balance' ),
                'weight' => 0.35
            ),
            'Lifestyle' => array(
                'categories' => array( 'exercise_frequency', 'nutrition_quality', 'sleep_patterns' ),
                'weight' => 0.25
            ),
            'Aesthetics' => array(
                'categories' => array( 'skin_health', 'body_composition', 'physical_appearance' ),
                'weight' => 0.15
            )
        );
        
        self::$pillar_map = $default_pillar_map;
        set_transient( 'ennu_pillar_map_v1', $default_pillar_map, 12 * HOUR_IN_SECONDS );
        
        return self::$pillar_map;
    }

    public static function get_health_goal_definitions() {
        $cached_goal_definitions = get_transient( 'ennu_health_goal_definitions_v1' );
        if ( false !== $cached_goal_definitions ) {
            return $cached_goal_definitions;
        }
        
        $goal_definitions_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/scoring/health-goals.php';
        if ( file_exists( $goal_definitions_file ) ) {
            $goal_definitions = require $goal_definitions_file;
            if ( is_array( $goal_definitions ) ) {
                set_transient( 'ennu_health_goal_definitions_v1', $goal_definitions, 12 * HOUR_IN_SECONDS );
                return $goal_definitions;
            }
        }
        
        $default_goal_definitions = array(
            'goal_definitions' => array(
                'weight_loss' => array(
                    'name' => 'Weight Loss',
                    'pillar_boosts' => array( 'Body' => 0.15, 'Lifestyle' => 0.10 )
                ),
                'energy_boost' => array(
                    'name' => 'Energy Boost',
                    'pillar_boosts' => array( 'Body' => 0.10, 'Mind' => 0.10 )
                )
            )
        );
        
        set_transient( 'ennu_health_goal_definitions_v1', $default_goal_definitions, 12 * HOUR_IN_SECONDS );
        return $default_goal_definitions;
    }

    public static function calculate_and_save_all_user_scores( $user_id, $force_recalc = false ) {
        $performance_monitor = ENNU_Performance_Monitor::get_instance();
        $performance_monitor->start_timer( 'scoring_calculation' );
        
        $all_definitions = self::get_all_definitions();
        $pillar_map = self::get_health_pillar_map();
        $health_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
        $goal_definitions = self::get_health_goal_definitions();

        // 1. Get all category scores from all completed assessments - OPTIMIZED
        $all_category_scores = array();
        
        $meta_keys = array();
        foreach ( array_keys($all_definitions) as $assessment_type ) {
            if ( 'health_optimization_assessment' === $assessment_type ) continue;
            $meta_keys[] = 'ennu_' . $assessment_type . '_category_scores';
        }
        
        $user_meta_batch = array();
        foreach ($meta_keys as $key) {
            $user_meta_batch[$key] = get_user_meta( $user_id, $key, true );
        }
        
        foreach ( array_keys($all_definitions) as $assessment_type ) {
            if ( 'health_optimization_assessment' === $assessment_type ) continue;
            $meta_key = 'ennu_' . $assessment_type . '_category_scores';
            $category_scores = $user_meta_batch[$meta_key] ?? array();
            if ( is_array( $category_scores ) && ! empty( $category_scores ) ) {
                $all_category_scores = array_merge( $all_category_scores, $category_scores );
            }
        }

        // 2. Calculate Base Pillar Scores (Quantitative Engine)
        $pillar_calculator = new ENNU_Pillar_Score_Calculator( $all_category_scores, $pillar_map );
        $base_pillar_scores = $pillar_calculator->calculate();

        // 3. Apply Qualitative Engine (Symptom Penalties) - NEW!
        $qualitative_adjusted_scores = $base_pillar_scores;
        $qualitative_data = array();
        
        if ( class_exists( 'ENNU_Qualitative_Engine' ) ) {
            $user_symptoms = self::get_symptom_data_for_user( $user_id );
            $all_symptoms = array();
            
            foreach ( $user_symptoms as $assessment_type => $symptoms ) {
                if ( is_array( $symptoms ) ) {
                    $all_symptoms = array_merge( $all_symptoms, $symptoms );
                }
            }
            
            if ( ! empty( $all_symptoms ) ) {
                $qualitative_engine = new ENNU_Qualitative_Engine( $all_symptoms );
                $qualitative_adjusted_scores = $qualitative_engine->apply_pillar_integrity_penalties( $base_pillar_scores );
                $qualitative_data = array(
                    'penalty_log' => $qualitative_engine->get_penalty_log(),
                    'penalty_summary' => $qualitative_engine->get_penalty_summary(),
                    'user_explanation' => $qualitative_engine->get_user_explanation(),
                );
                
                error_log( 'ENNU Scoring: Applied Qualitative Engine penalties for user ' . $user_id );
            }
        }

        $objective_adjusted_scores = $qualitative_adjusted_scores;
        $objective_data = array();
        
        if ( class_exists( 'ENNU_Objective_Engine' ) ) {
            $user_biomarkers = get_user_meta( $user_id, 'ennu_biomarker_data', true );
            
            if ( ! empty( $user_biomarkers ) && is_array( $user_biomarkers ) ) {
                $objective_engine = new ENNU_Objective_Engine( $user_biomarkers );
                $objective_adjusted_scores = $objective_engine->apply_biomarker_actuality_adjustments( $qualitative_adjusted_scores );
                $objective_data = array(
                    'adjustment_log' => $objective_engine->get_adjustment_log(),
                    'adjustment_summary' => $objective_engine->get_adjustment_summary(),
                    'user_explanation' => $objective_engine->get_user_explanation(),
                );
                
                error_log( 'ENNU Scoring: Applied Objective Engine adjustments for user ' . $user_id );
            }
        }

        $final_pillar_scores = $objective_adjusted_scores;
        $intentionality_data = array();
        
        if ( !empty( $health_goals ) && !empty( $goal_definitions ) && class_exists( 'ENNU_Intentionality_Engine' ) ) {
            $intentionality_engine = new ENNU_Intentionality_Engine( $health_goals, $goal_definitions, $objective_adjusted_scores );
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

        // 6. Calculate Final ENNU Life Score with all engine adjustments
        $ennu_life_score_calculator = new ENNU_Life_Score_Calculator( $user_id, $final_pillar_scores, $health_goals, $goal_definitions );
        $ennu_life_score_data = $ennu_life_score_calculator->calculate();
        
        update_user_meta( $user_id, 'ennu_life_score_data', $ennu_life_score_data );
        update_user_meta( $user_id, 'ennu_pillar_scores', $final_pillar_scores );
        update_user_meta( $user_id, 'ennu_qualitative_data', $qualitative_data );
        update_user_meta( $user_id, 'ennu_objective_data', $objective_data );
        update_user_meta( $user_id, 'ennu_intentionality_data', $intentionality_data );
        
        $score_history = get_user_meta( $user_id, 'ennu_score_history', true );
        if ( ! is_array( $score_history ) ) {
            $score_history = array();
        }
        
        $score_history[] = array(
            'score' => $ennu_life_score_data['ennu_life_score'],
            'date' => current_time( 'mysql' ),
            'timestamp' => time(),
            'goal_boost_applied' => !empty( $intentionality_data['boost_summary']['boosts_applied'] ),
            'goals_count' => count( $health_goals ),
            'symptom_penalties_applied' => !empty( $qualitative_data['penalty_summary']['penalties_applied'] ),
            'biomarker_adjustments_applied' => !empty( $objective_data['adjustment_summary']['adjustments_applied'] ),
        );
        
        update_user_meta( $user_id, 'ennu_score_history', $score_history );
        
        $metrics = $performance_monitor->end_timer( 'scoring_calculation' );
        error_log( 'ENNU Scoring: Complete scoring calculation finished for user ' . $user_id . ' with final score: ' . $ennu_life_score_data['ennu_life_score'] . ' (execution time: ' . round($metrics['execution_time'] * 1000, 2) . 'ms, memory: ' . round($metrics['memory_usage'] / 1024, 2) . 'KB)' );
        
        return $ennu_life_score_data;
    }

    public static function calculate_average_pillar_scores( $user_id ) {
        $pillar_scores = get_user_meta( $user_id, 'ennu_average_pillar_scores', true );
        if ( is_array( $pillar_scores ) && ! empty( $pillar_scores ) ) {
            return $pillar_scores;
        }
        
        $performance_monitor = ENNU_Performance_Monitor::get_instance();
        $performance_monitor->start_timer( 'pillar_calculation' );
        
        $all_definitions = self::get_all_definitions();
        $pillar_map = self::get_health_pillar_map();
        $all_category_scores = array();
        
        $meta_keys = array();
        foreach ( array_keys($all_definitions) as $assessment_type ) {
            if ( 'health_optimization_assessment' === $assessment_type ) continue;
            $meta_keys[] = 'ennu_' . $assessment_type . '_category_scores';
        }
        
        $user_meta_batch = array();
        foreach ($meta_keys as $key) {
            $user_meta_batch[$key] = get_user_meta( $user_id, $key, true );
        }
        
        foreach ( array_keys($all_definitions) as $assessment_type ) {
            if ( 'health_optimization_assessment' === $assessment_type ) continue;
            $meta_key = 'ennu_' . $assessment_type . '_category_scores';
            $category_scores = $user_meta_batch[$meta_key] ?? array();
            if ( is_array( $category_scores ) && ! empty( $category_scores ) ) {
                $all_category_scores = array_merge( $all_category_scores, $category_scores );
            }
        }
        
        $pillar_calculator = new ENNU_Pillar_Score_Calculator( $all_category_scores, $pillar_map );
        $base_pillar_scores = $pillar_calculator->calculate();
        
        $metrics = $performance_monitor->end_timer( 'pillar_calculation' );
        error_log( 'ENNU Scoring: Average pillar scores calculated for user ' . $user_id . ' (execution time: ' . round($metrics['execution_time'] * 1000, 2) . 'ms, memory: ' . round($metrics['memory_usage'] / 1024, 2) . 'KB)' );
        
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
		// NEW: Use centralized symptoms system if available
		if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
			$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $user_id );
			
			// Convert centralized format to expected format for backward compatibility
			$symptom_data = array();
			
			if ( ! empty( $centralized_symptoms['by_assessment'] ) ) {
				foreach ( $centralized_symptoms['by_assessment'] as $assessment_type => $symptoms ) {
					$symptom_data[ $assessment_type ] = array();
					foreach ( $symptoms as $symptom ) {
						$symptom_data[ $assessment_type ][] = $symptom['name'];
					}
				}
			}
			
			return $symptom_data;
		}
		
		// FALLBACK: Original fragmented approach for backward compatibility
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
