<?php
/**
 * ENNU Life New Life Score Calculator
 * Calculates aspirational scores based on doctor-recommended biomarker targets
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_New_Life_Score_Calculator {
    
    private $user_id;
    private $current_biomarkers;
    private $doctor_targets;
    private $base_pillar_scores;
    private $health_goals;
    private $weights;
    
    public function __construct( $user_id, $base_pillar_scores, $health_goals = array() ) {
        $this->user_id = $user_id;
        $this->base_pillar_scores = $base_pillar_scores;
        $this->health_goals = $health_goals;
        $this->current_biomarkers = get_user_meta( $user_id, 'ennu_biomarker_data', true ) ?: array();
        $this->doctor_targets = get_user_meta( $user_id, 'ennu_doctor_targets', true ) ?: array();
        $this->weights = array(
            'mind' => 0.3,
            'body' => 0.3,
            'lifestyle' => 0.3,
            'aesthetics' => 0.1,
        );
        
        error_log( "NewLifeScoreCalculator: Instantiated for user {$user_id}" );
    }
    
    public function calculate() {
        error_log( "NewLifeScoreCalculator: Starting New Life Score calculation" );
        
        $aspirational_scores = $this->base_pillar_scores;
        
        if ( empty( $this->doctor_targets ) ) {
            error_log( "NewLifeScoreCalculator: No doctor targets found, using potential score logic" );
            return $this->calculate_potential_score();
        }
        
        foreach ( $this->doctor_targets as $biomarker => $target_value ) {
            $current_value = $this->current_biomarkers[ $biomarker ]['value'] ?? null;
            
            if ( $current_value && $target_value ) {
                $improvement_factor = $this->calculate_improvement_factor( $biomarker, $current_value, $target_value );
                $aspirational_scores = $this->apply_biomarker_improvement( $aspirational_scores, $biomarker, $improvement_factor );
                
                error_log( "NewLifeScoreCalculator: Applied improvement for {$biomarker}: {$improvement_factor}" );
            }
        }
        
        $aspirational_scores = $this->apply_health_goal_boosts( $aspirational_scores );
        
        $new_life_score = 0;
        foreach ( $aspirational_scores as $pillar => $score ) {
            $pillar_key = strtolower( $pillar );
            if ( isset( $this->weights[ $pillar_key ] ) ) {
                $new_life_score += $score * $this->weights[ $pillar_key ];
            }
        }
        
        $final_score = min( 10, round( $new_life_score, 1 ) );
        error_log( "NewLifeScoreCalculator: Final New Life Score: {$final_score}" );
        
        $this->save_new_life_score_data( $final_score, $aspirational_scores );
        
        return $final_score;
    }
    
    private function calculate_potential_score() {
        if ( class_exists( 'ENNU_Potential_Score_Calculator' ) ) {
            $goal_definitions = $this->get_goal_definitions();
            $potential_calculator = new ENNU_Potential_Score_Calculator( $this->base_pillar_scores, $this->health_goals, $goal_definitions );
            return $potential_calculator->calculate();
        }
        
        $potential_scores = $this->apply_health_goal_boosts( $this->base_pillar_scores );
        
        $potential_score = 0;
        foreach ( $potential_scores as $pillar => $score ) {
            $pillar_key = strtolower( $pillar );
            if ( isset( $this->weights[ $pillar_key ] ) ) {
                $potential_score += $score * $this->weights[ $pillar_key ];
            }
        }
        
        return min( 10, round( $potential_score, 1 ) );
    }
    
    private function calculate_improvement_factor( $biomarker, $current_value, $target_value ) {
        $biomarker_config = $this->get_biomarker_config();
        
        if ( ! isset( $biomarker_config[ $biomarker ] ) ) {
            return 1.0;
        }
        
        $config = $biomarker_config[ $biomarker ];
        $ranges = $config['ranges'] ?? array();
        
        if ( ! isset( $ranges['optimal'] ) ) {
            return 1.0;
        }
        
        $optimal_range = $ranges['optimal'];
        $optimal_mid = ( $optimal_range['min'] + $optimal_range['max'] ) / 2;
        
        $current_distance = abs( $current_value - $optimal_mid );
        $target_distance = abs( $target_value - $optimal_mid );
        
        if ( $current_distance == 0 ) {
            return 1.0;
        }
        
        $improvement_ratio = max( 0, ( $current_distance - $target_distance ) / $current_distance );
        
        return 1.0 + ( $improvement_ratio * 0.3 );
    }
    
    private function apply_biomarker_improvement( $pillar_scores, $biomarker, $improvement_factor ) {
        $biomarker_pillar_map = $this->get_biomarker_pillar_mapping();
        
        if ( ! isset( $biomarker_pillar_map[ $biomarker ] ) ) {
            return $pillar_scores;
        }
        
        $affected_pillars = $biomarker_pillar_map[ $biomarker ];
        
        foreach ( $affected_pillars as $pillar => $impact_weight ) {
            if ( isset( $pillar_scores[ $pillar ] ) ) {
                $adjustment = ( $improvement_factor - 1.0 ) * $impact_weight;
                $pillar_scores[ $pillar ] = min( 10, $pillar_scores[ $pillar ] * ( 1 + $adjustment ) );
            }
        }
        
        return $pillar_scores;
    }
    
    private function apply_health_goal_boosts( $pillar_scores ) {
        if ( empty( $this->health_goals ) ) {
            return $pillar_scores;
        }
        
        $goal_definitions = $this->get_goal_definitions();
        
        if ( empty( $goal_definitions ) ) {
            return $pillar_scores;
        }
        
        foreach ( $this->health_goals as $goal ) {
            if ( isset( $goal_definitions[ $goal ]['pillar_bonus'] ) ) {
                foreach ( $goal_definitions[ $goal ]['pillar_bonus'] as $pillar => $bonus ) {
                    if ( isset( $pillar_scores[ $pillar ] ) ) {
                        $pillar_scores[ $pillar ] *= ( 1 + $bonus );
                    }
                }
            }
        }
        
        return $pillar_scores;
    }
    
    private function get_goal_definitions() {
        $health_goals_config = ENNU_LIFE_PLUGIN_PATH . 'includes/config/scoring/health-goals.php';
        
        if ( file_exists( $health_goals_config ) ) {
            $config = require $health_goals_config;
            return $config['goal_definitions'] ?? array();
        }
        
        return array();
    }
    
    private function get_biomarker_config() {
        $core_biomarkers_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/ennu-life-core-biomarkers.php';
        $advanced_biomarkers_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/advanced-biomarker-addons.php';
        
        $biomarkers = array();
        
        if ( file_exists( $core_biomarkers_file ) ) {
            $core_config = require $core_biomarkers_file;
            if ( isset( $core_config['biomarkers'] ) ) {
                $biomarkers = array_merge( $biomarkers, $core_config['biomarkers'] );
            }
        }
        
        if ( file_exists( $advanced_biomarkers_file ) ) {
            $advanced_config = require $advanced_biomarkers_file;
            if ( isset( $advanced_config['biomarkers'] ) ) {
                $biomarkers = array_merge( $biomarkers, $advanced_config['biomarkers'] );
            }
        }
        
        return $biomarkers;
    }
    
    private function get_biomarker_pillar_mapping() {
        return array(
            'vitamin_d' => array( 'Body' => 0.6, 'Mind' => 0.4 ),
            'testosterone' => array( 'Body' => 0.8, 'Mind' => 0.2 ),
            'cortisol' => array( 'Mind' => 0.7, 'Lifestyle' => 0.3 ),
            'tsh' => array( 'Body' => 0.8, 'Lifestyle' => 0.2 ),
            'free_t3' => array( 'Body' => 0.8, 'Mind' => 0.2 ),
            'free_t4' => array( 'Body' => 0.8, 'Mind' => 0.2 ),
            'total_cholesterol' => array( 'Body' => 0.9, 'Lifestyle' => 0.1 ),
            'hdl_cholesterol' => array( 'Body' => 0.8, 'Lifestyle' => 0.2 ),
            'ldl_cholesterol' => array( 'Body' => 0.9, 'Lifestyle' => 0.1 ),
            'triglycerides' => array( 'Body' => 0.8, 'Lifestyle' => 0.2 ),
            'blood_glucose' => array( 'Body' => 0.9, 'Lifestyle' => 0.1 ),
            'hemoglobin_a1c' => array( 'Body' => 0.9, 'Lifestyle' => 0.1 ),
            'vitamin_b12' => array( 'Body' => 0.6, 'Mind' => 0.4 ),
            'folate' => array( 'Body' => 0.6, 'Mind' => 0.4 ),
            'ferritin' => array( 'Body' => 0.8, 'Lifestyle' => 0.2 ),
            'crp' => array( 'Body' => 0.7, 'Lifestyle' => 0.3 ),
            'homocysteine' => array( 'Body' => 0.6, 'Mind' => 0.4 ),
            'igf_1' => array( 'Body' => 0.9, 'Aesthetics' => 0.1 ),
            'dhea_s' => array( 'Body' => 0.7, 'Mind' => 0.3 ),
            'estradiol' => array( 'Body' => 0.8, 'Aesthetics' => 0.2 ),
        );
    }
    
    private function save_new_life_score_data( $new_life_score, $aspirational_pillar_scores ) {
        update_user_meta( $this->user_id, 'ennu_new_life_score', $new_life_score );
        update_user_meta( $this->user_id, 'ennu_new_life_pillar_scores', $aspirational_pillar_scores );
        update_user_meta( $this->user_id, 'ennu_new_life_score_calculated_at', current_time( 'mysql' ) );
        
        $history = get_user_meta( $this->user_id, 'ennu_new_life_score_history', true ) ?: array();
        $history[] = array(
            'score' => $new_life_score,
            'date' => current_time( 'mysql' ),
            'pillar_scores' => $aspirational_pillar_scores,
            'doctor_targets_count' => count( $this->doctor_targets ),
            'health_goals_count' => count( $this->health_goals )
        );
        
        if ( count( $history ) > 50 ) {
            $history = array_slice( $history, -50 );
        }
        
        update_user_meta( $this->user_id, 'ennu_new_life_score_history', $history );
        
        error_log( "NewLifeScoreCalculator: Saved New Life Score data for user {$this->user_id}" );
    }
    
    public function get_improvement_potential() {
        $current_score = get_user_meta( $this->user_id, 'ennu_life_score', true ) ?: 0;
        $new_life_score = $this->calculate();
        
        return array(
            'current_score' => floatval( $current_score ),
            'new_life_score' => $new_life_score,
            'improvement' => $new_life_score - $current_score,
            'improvement_percentage' => $current_score > 0 ? ( ( $new_life_score - $current_score ) / $current_score ) * 100 : 0
        );
    }
}
