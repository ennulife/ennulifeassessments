<?php
/**
 * ENNU Life Score Calculator
 *
 * This class is responsible for calculating the final, adjusted ENNU LIFE SCORE.
 * It takes the base Pillar Scores and applies penalties from the Health Optimization data.
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Life_Score_Calculator {

    private $user_id;
    private $base_pillar_scores;
    private $all_definitions;

    public function __construct( $user_id, $base_pillar_scores, $all_definitions ) {
        $this->user_id = $user_id;
        $this->base_pillar_scores = $base_pillar_scores;
        $this->all_definitions = $all_definitions;
        error_log("EnnuLifeScoreCalculator: Instantiated for user ID {$user_id}.");
    }

    public function calculate() {
        error_log("EnnuLifeScoreCalculator: Starting calculation.");
        // 1. Calculate Pillar Integrity Penalties
        $health_opt_defs = $this->all_definitions['health_optimization_assessment'] ?? array();
        $health_opt_calculator = new ENNU_Health_Optimization_Calculator( $this->user_id, array( 'health_optimization_assessment' => $health_opt_defs ) );
        $pillar_penalties = $health_opt_calculator->calculate_pillar_penalties();
        error_log("EnnuLifeScoreCalculator: Calculated pillar penalties: " . print_r($pillar_penalties, true));

        // 2. Apply Penalties to get the Final Adjusted Pillar Scores
        $final_pillar_scores = array();
        $pillar_score_data = array();
        foreach($this->base_pillar_scores as $pillar_name => $base_score) {
            $penalty = $pillar_penalties[$pillar_name] ?? 0;
            $final_score = $base_score * (1 - $penalty);
            $final_pillar_scores[$pillar_name] = $final_score;

            $pillar_score_data[$pillar_name] = array(
                'base' => round($base_score, 1),
                'penalty' => round($penalty * 100, 0), // store as percentage
                'final' => round($final_score, 1),
            );
        }
        error_log("EnnuLifeScoreCalculator: Calculated final adjusted pillar scores: " . print_r($final_pillar_scores, true));

        // 3. Apply strategic weights to the FINAL scores
        $weights = array(
            'mind'       => 0.3,
            'body'       => 0.3,
            'lifestyle'  => 0.3,
            'aesthetics' => 0.1,
        );

        $ennu_life_score = 0;
        foreach ( $final_pillar_scores as $pillar_name => $final_score ) {
            if ( isset( $weights[ $pillar_name ] ) ) {
                $ennu_life_score += $final_score * $weights[ $pillar_name ];
            }
        }
        error_log("EnnuLifeScoreCalculator: Final ENNU LIFE SCORE calculated: " . round($ennu_life_score, 1));

        // The calculator should only calculate. It should not save.
        // The orchestrator will be responsible for saving this data.
        $capitalized_pillar_scores = array();
		foreach ( $final_pillar_scores as $pillar_name => $score ) {
			$capitalized_pillar_scores[ ucfirst( $pillar_name ) ] = round( $score, 1 );
		}

        return array(
            'ennu_life_score' => round( $ennu_life_score, 1 ),
            'pillar_score_data' => $pillar_score_data,
            'average_pillar_scores' => $capitalized_pillar_scores,
        );
    }
} 