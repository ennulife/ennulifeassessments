<?php
/**
 * ENNU Life Potential Score Calculator
 *
 * This class is responsible for calculating the user's aspirational "Potential Score."
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Potential_Score_Calculator {

    private $base_pillar_scores;
    private $health_goals;
    private $goal_definitions;
    private $weights;

    public function __construct( $base_pillar_scores, $health_goals, $goal_definitions ) {
        $this->base_pillar_scores = $base_pillar_scores;
        $this->health_goals = $health_goals;
        $this->goal_definitions = $goal_definitions;
        $this->weights = array(
            'mind'       => 0.3,
            'body'       => 0.3,
            'lifestyle'  => 0.3,
            'aesthetics' => 0.1,
        );
        error_log("PotentialScoreCalculator: Instantiated.");
    }

    public function calculate() {
        error_log("PotentialScoreCalculator: Starting calculation.");
        // The "Potential Score" assumes all penalties are zero.
        // It starts with the user's base pillar scores and applies the max possible health goal bonus.
        $potential_pillar_scores = $this->base_pillar_scores;

        // Apply health goal bonuses
        if ( ! empty( $this->health_goals ) && is_array( $this->health_goals ) ) {
            foreach ( $this->health_goals as $goal ) {
                if ( isset( $this->goal_definitions[ $goal ]['pillar_bonus'] ) ) {
                    foreach ( $this->goal_definitions[ $goal ]['pillar_bonus'] as $pillar => $bonus ) {
                        if ( isset( $potential_pillar_scores[ $pillar ] ) ) {
                            $potential_pillar_scores[ $pillar ] *= ( 1 + $bonus );
                            error_log("PotentialScoreCalculator: Applied bonus of {$bonus} to pillar '{$pillar}' for goal '{$goal}'.");
                        }
                    }
                }
            }
        }
        
        // Calculate the final weighted score
        $potential_ennu_life_score = 0;
        foreach ( $potential_pillar_scores as $pillar_name => $score ) {
            if ( isset( $this->weights[ $pillar_name ] ) ) {
                $potential_ennu_life_score += $score * $this->weights[ $pillar_name ];
            }
        }

        $final_potential_score = min( 10, round( $potential_ennu_life_score, 1 ) );
        error_log("PotentialScoreCalculator: Final potential score calculated: {$final_potential_score}");
        // The potential score should not exceed the maximum possible score of 10.
        return $final_potential_score;
    }
} 