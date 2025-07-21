<?php
/**
 * ENNU Goal Progression Tracker
 * Integrates with Intentionality Engine for goal achievement tracking
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Goal_Progression_Tracker {

    /**
     * Initialize goal progression tracker
     */
    public static function init() {
        add_action( 'ennu_scores_updated', array( __CLASS__, 'track_progression' ), 10, 1 );
        add_action( 'ennu_assessment_completed', array( __CLASS__, 'update_goal_progress' ), 10, 2 );
        add_action( 'wp_ajax_ennu_set_goal_targets', array( __CLASS__, 'handle_set_goal_targets' ) );
        add_action( 'wp_ajax_ennu_get_goal_progress', array( __CLASS__, 'handle_get_goal_progress' ) );
        
        error_log('ENNU Goal Progression Tracker: Initialized with Intentionality Engine integration');
    }

    /**
     * Track goal progression after score updates
     *
     * @param int $user_id User ID
     */
    public static function track_progression( $user_id ) {
        $user_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
        if ( empty( $user_goals ) || ! is_array( $user_goals ) ) {
            return;
        }

        $current_scores = self::get_current_user_scores( $user_id );
        $goal_progress = self::calculate_goal_progress( $user_id, $user_goals, $current_scores );
        
        self::update_goal_progress_history( $user_id, $goal_progress );
        self::check_goal_achievements( $user_id, $goal_progress );
        
        do_action( 'ennu_goal_progress_updated', $user_id, $goal_progress );
    }

    /**
     * Update goal progress after assessment completion
     *
     * @param int $user_id User ID
     * @param string $assessment_type Assessment type
     */
    public static function update_goal_progress( $user_id, $assessment_type ) {
        $assessment_goals = self::get_assessment_related_goals( $user_id, $assessment_type );
        if ( empty( $assessment_goals ) ) {
            return;
        }

        $assessment_scores = get_user_meta( $user_id, "ennu_{$assessment_type}_scores", true );
        if ( empty( $assessment_scores ) ) {
            return;
        }

        foreach ( $assessment_goals as $goal ) {
            self::update_individual_goal_progress( $user_id, $goal, $assessment_type, $assessment_scores );
        }
    }

    /**
     * Calculate goal progress for user
     *
     * @param int $user_id User ID
     * @param array $user_goals User's health goals
     * @param array $current_scores Current assessment scores
     * @return array Goal progress data
     */
    private static function calculate_goal_progress( $user_id, $user_goals, $current_scores ) {
        $goal_progress = array();
        $goal_definitions = self::get_goal_definitions();

        foreach ( $user_goals as $goal ) {
            if ( ! isset( $goal_definitions[ $goal ] ) ) {
                continue;
            }

            $definition = $goal_definitions[ $goal ];
            $progress_data = array(
                'goal' => $goal,
                'definition' => $definition,
                'current_level' => self::determine_current_level( $goal, $current_scores ),
                'target_level' => self::get_goal_target( $user_id, $goal ),
                'progress_percentage' => 0,
                'next_milestone' => null,
                'achievements' => array(),
                'recommendations' => array()
            );

            $progress_data['progress_percentage'] = self::calculate_progress_percentage( $progress_data );
            $progress_data['next_milestone'] = self::get_next_milestone( $progress_data );
            $progress_data['achievements'] = self::get_goal_achievements( $user_id, $goal );
            $progress_data['recommendations'] = self::get_goal_recommendations( $goal, $progress_data );

            $goal_progress[ $goal ] = $progress_data;
        }

        return $goal_progress;
    }

    /**
     * Get current user scores across all assessments
     *
     * @param int $user_id User ID
     * @return array Current scores
     */
    private static function get_current_user_scores( $user_id ) {
        $assessment_types = array(
            'testosterone', 'menopause', 'ed_treatment', 'weight_loss', 
            'longevity', 'hair', 'skin', 'health', 'sleep'
        );

        $scores = array();
        foreach ( $assessment_types as $type ) {
            $assessment_scores = get_user_meta( $user_id, "ennu_{$type}_scores", true );
            if ( ! empty( $assessment_scores ) ) {
                $scores[ $type ] = $assessment_scores;
            }
        }

        $scores['life_score'] = get_user_meta( $user_id, 'ennu_life_score', true );
        $scores['pillar_scores'] = get_user_meta( $user_id, 'ennu_pillar_scores', true );

        return $scores;
    }

    /**
     * Get goal definitions with progression levels
     *
     * @return array Goal definitions
     */
    private static function get_goal_definitions() {
        return array(
            'lose_weight' => array(
                'title' => 'Weight Loss',
                'levels' => array(
                    'good' => array( 'min_score' => 60, 'description' => 'Initial weight loss progress' ),
                    'better' => array( 'min_score' => 75, 'description' => 'Significant weight loss achieved' ),
                    'best' => array( 'min_score' => 85, 'description' => 'Optimal weight management' )
                ),
                'related_assessments' => array( 'weight_loss', 'health' ),
                'key_metrics' => array( 'bmi', 'body_composition', 'metabolic_health' )
            ),
            'build_muscle' => array(
                'title' => 'Muscle Building',
                'levels' => array(
                    'good' => array( 'min_score' => 65, 'description' => 'Muscle building foundation' ),
                    'better' => array( 'min_score' => 78, 'description' => 'Noticeable muscle gains' ),
                    'best' => array( 'min_score' => 88, 'description' => 'Optimal muscle development' )
                ),
                'related_assessments' => array( 'testosterone', 'health' ),
                'key_metrics' => array( 'strength', 'muscle_mass', 'recovery' )
            ),
            'improve_energy' => array(
                'title' => 'Energy Enhancement',
                'levels' => array(
                    'good' => array( 'min_score' => 62, 'description' => 'Improved daily energy' ),
                    'better' => array( 'min_score' => 76, 'description' => 'Sustained energy levels' ),
                    'best' => array( 'min_score' => 87, 'description' => 'Optimal energy and vitality' )
                ),
                'related_assessments' => array( 'sleep', 'health', 'testosterone' ),
                'key_metrics' => array( 'sleep_quality', 'fatigue_levels', 'mental_clarity' )
            ),
            'enhance_mood' => array(
                'title' => 'Mood Enhancement',
                'levels' => array(
                    'good' => array( 'min_score' => 64, 'description' => 'Mood stability improvement' ),
                    'better' => array( 'min_score' => 77, 'description' => 'Consistent positive mood' ),
                    'best' => array( 'min_score' => 86, 'description' => 'Optimal emotional well-being' )
                ),
                'related_assessments' => array( 'health', 'sleep', 'testosterone' ),
                'key_metrics' => array( 'mood_stability', 'stress_levels', 'emotional_resilience' )
            ),
            'improve_sleep' => array(
                'title' => 'Sleep Optimization',
                'levels' => array(
                    'good' => array( 'min_score' => 66, 'description' => 'Better sleep quality' ),
                    'better' => array( 'min_score' => 79, 'description' => 'Consistent restful sleep' ),
                    'best' => array( 'min_score' => 89, 'description' => 'Optimal sleep patterns' )
                ),
                'related_assessments' => array( 'sleep', 'health' ),
                'key_metrics' => array( 'sleep_duration', 'sleep_quality', 'sleep_consistency' )
            ),
            'increase_libido' => array(
                'title' => 'Libido Enhancement',
                'levels' => array(
                    'good' => array( 'min_score' => 63, 'description' => 'Improved sexual interest' ),
                    'better' => array( 'min_score' => 76, 'description' => 'Healthy libido levels' ),
                    'best' => array( 'min_score' => 85, 'description' => 'Optimal sexual health' )
                ),
                'related_assessments' => array( 'testosterone', 'ed_treatment', 'health' ),
                'key_metrics' => array( 'sexual_desire', 'sexual_function', 'relationship_satisfaction' )
            ),
            'longevity' => array(
                'title' => 'Longevity Optimization',
                'levels' => array(
                    'good' => array( 'min_score' => 68, 'description' => 'Longevity foundation established' ),
                    'better' => array( 'min_score' => 80, 'description' => 'Strong longevity markers' ),
                    'best' => array( 'min_score' => 90, 'description' => 'Optimal longevity potential' )
                ),
                'related_assessments' => array( 'longevity', 'health' ),
                'key_metrics' => array( 'biological_age', 'health_span', 'disease_prevention' )
            )
        );
    }

    /**
     * Determine current level for a goal
     *
     * @param string $goal Goal name
     * @param array $current_scores Current scores
     * @return string Current level (good, better, best, or none)
     */
    private static function determine_current_level( $goal, $current_scores ) {
        $goal_definitions = self::get_goal_definitions();
        if ( ! isset( $goal_definitions[ $goal ] ) ) {
            return 'none';
        }

        $definition = $goal_definitions[ $goal ];
        $related_assessments = $definition['related_assessments'];
        
        $relevant_scores = array();
        foreach ( $related_assessments as $assessment ) {
            if ( isset( $current_scores[ $assessment ]['overall_score'] ) ) {
                $relevant_scores[] = $current_scores[ $assessment ]['overall_score'];
            }
        }

        if ( empty( $relevant_scores ) ) {
            return 'none';
        }

        $average_score = array_sum( $relevant_scores ) / count( $relevant_scores );

        $levels = $definition['levels'];
        if ( $average_score >= $levels['best']['min_score'] ) {
            return 'best';
        } elseif ( $average_score >= $levels['better']['min_score'] ) {
            return 'better';
        } elseif ( $average_score >= $levels['good']['min_score'] ) {
            return 'good';
        }

        return 'none';
    }

    /**
     * Get goal target level for user
     *
     * @param int $user_id User ID
     * @param string $goal Goal name
     * @return string Target level
     */
    private static function get_goal_target( $user_id, $goal ) {
        $goal_targets = get_user_meta( $user_id, 'ennu_goal_targets', true );
        if ( ! is_array( $goal_targets ) ) {
            $goal_targets = array();
        }

        return $goal_targets[ $goal ] ?? 'better';
    }

    /**
     * Calculate progress percentage
     *
     * @param array $progress_data Progress data
     * @return int Progress percentage
     */
    private static function calculate_progress_percentage( $progress_data ) {
        $current_level = $progress_data['current_level'];
        $target_level = $progress_data['target_level'];

        $level_values = array( 'none' => 0, 'good' => 33, 'better' => 66, 'best' => 100 );
        
        $current_value = $level_values[ $current_level ] ?? 0;
        $target_value = $level_values[ $target_level ] ?? 66;

        if ( $target_value === 0 ) {
            return 0;
        }

        return min( 100, intval( ( $current_value / $target_value ) * 100 ) );
    }

    /**
     * Get next milestone for goal
     *
     * @param array $progress_data Progress data
     * @return array|null Next milestone
     */
    private static function get_next_milestone( $progress_data ) {
        $current_level = $progress_data['current_level'];
        $definition = $progress_data['definition'];

        $level_order = array( 'none', 'good', 'better', 'best' );
        $current_index = array_search( $current_level, $level_order );

        if ( $current_index === false || $current_index >= count( $level_order ) - 1 ) {
            return null;
        }

        $next_level = $level_order[ $current_index + 1 ];
        if ( isset( $definition['levels'][ $next_level ] ) ) {
            return array(
                'level' => $next_level,
                'description' => $definition['levels'][ $next_level ]['description'],
                'min_score' => $definition['levels'][ $next_level ]['min_score']
            );
        }

        return null;
    }

    /**
     * Get goal achievements for user
     *
     * @param int $user_id User ID
     * @param string $goal Goal name
     * @return array Achievements
     */
    private static function get_goal_achievements( $user_id, $goal ) {
        $achievements = get_user_meta( $user_id, 'ennu_goal_achievements', true );
        if ( ! is_array( $achievements ) ) {
            return array();
        }

        return $achievements[ $goal ] ?? array();
    }

    /**
     * Get recommendations for goal
     *
     * @param string $goal Goal name
     * @param array $progress_data Progress data
     * @return array Recommendations
     */
    private static function get_goal_recommendations( $goal, $progress_data ) {
        $current_level = $progress_data['current_level'];
        $next_milestone = $progress_data['next_milestone'];

        $recommendations = array();

        if ( $next_milestone ) {
            $recommendations[] = array(
                'type' => 'milestone',
                'title' => "Reach {$next_milestone['level']} level",
                'description' => $next_milestone['description'],
                'priority' => 'high'
            );
        }

        $goal_specific_recommendations = self::get_goal_specific_recommendations( $goal, $current_level );
        $recommendations = array_merge( $recommendations, $goal_specific_recommendations );

        return $recommendations;
    }

    /**
     * Get goal-specific recommendations
     *
     * @param string $goal Goal name
     * @param string $current_level Current level
     * @return array Recommendations
     */
    private static function get_goal_specific_recommendations( $goal, $current_level ) {
        $recommendations_map = array(
            'lose_weight' => array(
                'none' => array(
                    array( 'type' => 'assessment', 'title' => 'Complete Weight Loss Assessment', 'priority' => 'high' ),
                    array( 'type' => 'action', 'title' => 'Set realistic weight loss targets', 'priority' => 'medium' )
                ),
                'good' => array(
                    array( 'type' => 'action', 'title' => 'Increase physical activity', 'priority' => 'high' ),
                    array( 'type' => 'biomarker', 'title' => 'Monitor metabolic markers', 'priority' => 'medium' )
                )
            ),
            'build_muscle' => array(
                'none' => array(
                    array( 'type' => 'assessment', 'title' => 'Complete Testosterone Assessment', 'priority' => 'high' ),
                    array( 'type' => 'action', 'title' => 'Start resistance training program', 'priority' => 'high' )
                ),
                'good' => array(
                    array( 'type' => 'biomarker', 'title' => 'Check testosterone levels', 'priority' => 'high' ),
                    array( 'type' => 'action', 'title' => 'Optimize protein intake', 'priority' => 'medium' )
                )
            ),
            'improve_energy' => array(
                'none' => array(
                    array( 'type' => 'assessment', 'title' => 'Complete Sleep Assessment', 'priority' => 'high' ),
                    array( 'type' => 'biomarker', 'title' => 'Check thyroid function', 'priority' => 'medium' )
                ),
                'good' => array(
                    array( 'type' => 'action', 'title' => 'Optimize sleep schedule', 'priority' => 'high' ),
                    array( 'type' => 'biomarker', 'title' => 'Monitor vitamin D levels', 'priority' => 'medium' )
                )
            )
        );

        return $recommendations_map[ $goal ][ $current_level ] ?? array();
    }

    /**
     * Update goal progress history
     *
     * @param int $user_id User ID
     * @param array $goal_progress Goal progress data
     */
    private static function update_goal_progress_history( $user_id, $goal_progress ) {
        $history = get_user_meta( $user_id, 'ennu_goal_progress_history', true );
        if ( ! is_array( $history ) ) {
            $history = array();
        }

        $history_entry = array(
            'timestamp' => current_time( 'mysql' ),
            'progress' => $goal_progress
        );

        $history[] = $history_entry;

        update_user_meta( $user_id, 'ennu_goal_progress_history', array_slice( $history, -50 ) );
    }

    /**
     * Check for goal achievements
     *
     * @param int $user_id User ID
     * @param array $goal_progress Goal progress data
     */
    private static function check_goal_achievements( $user_id, $goal_progress ) {
        $existing_achievements = get_user_meta( $user_id, 'ennu_goal_achievements', true );
        if ( ! is_array( $existing_achievements ) ) {
            $existing_achievements = array();
        }

        $new_achievements = false;

        foreach ( $goal_progress as $goal => $progress ) {
            $current_level = $progress['current_level'];
            
            if ( $current_level !== 'none' ) {
                $achievement_key = "{$goal}_{$current_level}";
                
                if ( ! isset( $existing_achievements[ $goal ] ) ) {
                    $existing_achievements[ $goal ] = array();
                }

                if ( ! in_array( $achievement_key, $existing_achievements[ $goal ], true ) ) {
                    $existing_achievements[ $goal ][] = $achievement_key;
                    $new_achievements = true;
                    
                    do_action( 'ennu_goal_achievement_unlocked', $user_id, $goal, $current_level );
                }
            }
        }

        if ( $new_achievements ) {
            update_user_meta( $user_id, 'ennu_goal_achievements', $existing_achievements );
        }
    }

    /**
     * Get assessment-related goals
     *
     * @param int $user_id User ID
     * @param string $assessment_type Assessment type
     * @return array Related goals
     */
    private static function get_assessment_related_goals( $user_id, $assessment_type ) {
        $user_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
        if ( empty( $user_goals ) || ! is_array( $user_goals ) ) {
            return array();
        }

        $goal_definitions = self::get_goal_definitions();
        $related_goals = array();

        foreach ( $user_goals as $goal ) {
            if ( isset( $goal_definitions[ $goal ]['related_assessments'] ) ) {
                $related_assessments = $goal_definitions[ $goal ]['related_assessments'];
                if ( in_array( $assessment_type, $related_assessments, true ) ) {
                    $related_goals[] = $goal;
                }
            }
        }

        return $related_goals;
    }

    /**
     * Update individual goal progress
     *
     * @param int $user_id User ID
     * @param string $goal Goal name
     * @param string $assessment_type Assessment type
     * @param array $assessment_scores Assessment scores
     */
    private static function update_individual_goal_progress( $user_id, $goal, $assessment_type, $assessment_scores ) {
        $current_progress = get_user_meta( $user_id, 'ennu_individual_goal_progress', true );
        if ( ! is_array( $current_progress ) ) {
            $current_progress = array();
        }

        if ( ! isset( $current_progress[ $goal ] ) ) {
            $current_progress[ $goal ] = array();
        }

        $current_progress[ $goal ][ $assessment_type ] = array(
            'score' => $assessment_scores['overall_score'] ?? 0,
            'updated_at' => current_time( 'mysql' )
        );

        update_user_meta( $user_id, 'ennu_individual_goal_progress', $current_progress );
    }

    /**
     * Handle AJAX set goal targets request
     */
    public static function handle_set_goal_targets() {
        check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'User not logged in' );
        }

        $user_id = get_current_user_id();
        $goal_targets = $_POST['goal_targets'] ?? array();

        if ( empty( $goal_targets ) || ! is_array( $goal_targets ) ) {
            wp_send_json_error( 'Invalid goal targets data' );
        }

        $sanitized_targets = array();
        $valid_levels = array( 'good', 'better', 'best' );

        foreach ( $goal_targets as $goal => $target ) {
            $goal = sanitize_text_field( $goal );
            $target = sanitize_text_field( $target );
            
            if ( in_array( $target, $valid_levels, true ) ) {
                $sanitized_targets[ $goal ] = $target;
            }
        }

        $success = update_user_meta( $user_id, 'ennu_goal_targets', $sanitized_targets );

        if ( $success ) {
            wp_send_json_success( array( 'message' => 'Goal targets updated successfully' ) );
        } else {
            wp_send_json_error( 'Failed to update goal targets' );
        }
    }

    /**
     * Handle AJAX get goal progress request
     */
    public static function handle_get_goal_progress() {
        check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( 'User not logged in' );
        }

        $user_id = get_current_user_id();
        $user_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
        
        if ( empty( $user_goals ) ) {
            wp_send_json_success( array( 'goal_progress' => array() ) );
            return;
        }

        $current_scores = self::get_current_user_scores( $user_id );
        $goal_progress = self::calculate_goal_progress( $user_id, $user_goals, $current_scores );

        wp_send_json_success( array( 'goal_progress' => $goal_progress ) );
    }
}
