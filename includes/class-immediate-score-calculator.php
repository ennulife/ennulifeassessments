<?php
/**
 * ENNU Immediate Score Calculator
 * Ensures 100% of users receive all scores immediately after any assessment
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Immediate_Score_Calculator {

    /**
     * Trigger immediate score calculation after assessment submission
     *
     * @param string $assessment_type Assessment type submitted
     * @param array $form_data Form data submitted
     * @param int $user_id User ID
     */
    public static function trigger_after_assessment( $assessment_type, $form_data, $user_id ) {
        self::calculate_submitted_assessment_scores( $assessment_type, $form_data, $user_id );
        
        self::generate_all_missing_scores( $user_id );
        
        self::calculate_all_pillar_scores( $user_id );
        
        if ( class_exists( 'ENNU_Profile_Completeness_Tracker' ) ) {
            ENNU_Profile_Completeness_Tracker::calculate_completeness( $user_id );
        }
        
        error_log( "ENNU: Immediate scores generated for user {$user_id} after {$assessment_type} assessment" );
    }

    /**
     * Calculate scores for the submitted assessment
     *
     * @param string $assessment_type Assessment type
     * @param array $form_data Form data
     * @param int $user_id User ID
     */
    private static function calculate_submitted_assessment_scores( $assessment_type, $form_data, $user_id ) {
        if ( class_exists( 'ENNU_Scoring_System' ) ) {
            $scores = ENNU_Scoring_System::calculate_scores_for_assessment( $assessment_type, $form_data );
            
            if ( ! empty( $scores ) ) {
                update_user_meta( $user_id, 'ennu_' . $assessment_type . '_scores', $scores );
                update_user_meta( $user_id, 'ennu_' . $assessment_type . '_last_updated', current_time( 'mysql' ) );
            }
        }
    }

    /**
     * Generate all missing scores using smart defaults
     *
     * @param int $user_id User ID
     */
    private static function generate_all_missing_scores( $user_id ) {
        if ( ! class_exists( 'ENNU_Smart_Defaults_Generator' ) ) {
            return;
        }
        
        $smart_defaults = ENNU_Smart_Defaults_Generator::generate_defaults_for_user( $user_id );
        
        foreach ( $smart_defaults as $assessment_type => $defaults ) {
            update_user_meta( $user_id, 'ennu_' . $assessment_type . '_scores', array(
                'overall_score' => $defaults['current_score'],
                'projected_score' => $defaults['projected_score'],
                'confidence_level' => $defaults['confidence_level'],
                'data_source' => 'smart_defaults',
                'generated_at' => $defaults['generated_at']
            ) );
            
            $category_scores = self::generate_category_scores_from_overall( $defaults['current_score'] );
            update_user_meta( $user_id, 'ennu_' . $assessment_type . '_category_scores', $category_scores );
        }
    }

    /**
     * Generate category scores from overall score
     *
     * @param int $overall_score Overall assessment score
     * @return array Category scores
     */
    private static function generate_category_scores_from_overall( $overall_score ) {
        $variance = 8; // +/- 8 points variance
        $categories = array(
            'physical_health',
            'mental_wellbeing', 
            'energy_levels',
            'sleep_quality',
            'stress_management',
            'nutrition_habits'
        );
        
        $category_scores = array();
        
        foreach ( $categories as $category ) {
            $category_score = $overall_score + wp_rand( -$variance, $variance );
            $category_score = max( 20, min( 95, $category_score ) ); // Keep within bounds
            $category_scores[ $category ] = $category_score;
        }
        
        return $category_scores;
    }

    /**
     * Calculate all pillar scores from available assessments
     *
     * @param int $user_id User ID
     */
    private static function calculate_all_pillar_scores( $user_id ) {
        if ( ! class_exists( 'ENNU_Scoring_System' ) ) {
            return;
        }
        
        $all_category_scores = array();
        $assessment_types = array(
            'testosterone', 'menopause', 'ed_treatment', 'weight_loss', 
            'longevity', 'sleep', 'stress', 'nutrition', 'fitness', 'cognitive'
        );
        
        foreach ( $assessment_types as $assessment_type ) {
            $category_scores = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_category_scores', true );
            if ( is_array( $category_scores ) && ! empty( $category_scores ) ) {
                $all_category_scores = array_merge( $all_category_scores, $category_scores );
            }
        }
        
        $pillar_scores = self::calculate_pillar_scores_from_categories( $all_category_scores );
        
        update_user_meta( $user_id, 'ennu_pillar_scores', $pillar_scores );
        update_user_meta( $user_id, 'ennu_pillar_scores_last_updated', current_time( 'mysql' ) );
        
        $overall_life_score = self::calculate_overall_life_score( $pillar_scores );
        update_user_meta( $user_id, 'ennu_life_score', $overall_life_score );
    }

    /**
     * Calculate pillar scores from category scores
     *
     * @param array $all_category_scores All category scores
     * @return array Pillar scores
     */
    private static function calculate_pillar_scores_from_categories( $all_category_scores ) {
        if ( empty( $all_category_scores ) ) {
            return array();
        }
        
        $pillar_mapping = array(
            'Mind' => array( 'mental_wellbeing', 'stress_management', 'cognitive_function', 'mood_stability' ),
            'Body' => array( 'physical_health', 'energy_levels', 'hormonal_balance', 'cardiovascular_health' ),
            'Lifestyle' => array( 'sleep_quality', 'nutrition_habits', 'exercise_frequency', 'work_life_balance' ),
            'Aesthetics' => array( 'skin_health', 'body_composition', 'physical_appearance', 'confidence_levels' )
        );
        
        $pillar_scores = array();
        
        foreach ( $pillar_mapping as $pillar => $categories ) {
            $pillar_total = 0;
            $pillar_count = 0;
            
            foreach ( $categories as $category ) {
                if ( isset( $all_category_scores[ $category ] ) ) {
                    $pillar_total += floatval( $all_category_scores[ $category ] );
                    $pillar_count++;
                }
            }
            
            if ( $pillar_count > 0 ) {
                $pillar_scores[ $pillar ] = intval( round( $pillar_total / $pillar_count ) );
            } else {
                $pillar_scores[ $pillar ] = 65; // Reasonable default
            }
        }
        
        return $pillar_scores;
    }

    /**
     * Calculate overall ENNU Life Score
     *
     * @param array $pillar_scores Pillar scores
     * @return int Overall life score
     */
    private static function calculate_overall_life_score( $pillar_scores ) {
        if ( empty( $pillar_scores ) ) {
            return 65; // Default score
        }
        
        $weights = array(
            'Mind' => 0.25,
            'Body' => 0.35,
            'Lifestyle' => 0.25,
            'Aesthetics' => 0.15
        );
        
        $weighted_total = 0;
        $total_weight = 0;
        
        foreach ( $pillar_scores as $pillar => $score ) {
            if ( isset( $weights[ $pillar ] ) ) {
                $weighted_total += $score * $weights[ $pillar ];
                $total_weight += $weights[ $pillar ];
            }
        }
        
        if ( $total_weight > 0 ) {
            return intval( round( $weighted_total / $total_weight ) );
        }
        
        return 65; // Fallback default
    }

    /**
     * Get immediate scores for user (for display purposes)
     *
     * @param int $user_id User ID
     * @return array All user scores
     */
    public static function get_immediate_scores_for_user( $user_id ) {
        $scores = array(
            'life_score' => get_user_meta( $user_id, 'ennu_life_score', true ),
            'pillar_scores' => get_user_meta( $user_id, 'ennu_pillar_scores', true ),
            'assessment_scores' => array(),
            'profile_completeness' => get_user_meta( $user_id, 'ennu_profile_completeness', true )
        );
        
        $assessment_types = array(
            'testosterone', 'menopause', 'ed_treatment', 'weight_loss', 
            'longevity', 'sleep', 'stress', 'nutrition', 'fitness', 'cognitive'
        );
        
        foreach ( $assessment_types as $assessment_type ) {
            $assessment_scores = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_scores', true );
            if ( ! empty( $assessment_scores ) ) {
                $scores['assessment_scores'][ $assessment_type ] = $assessment_scores;
            }
        }
        
        return $scores;
    }
}
