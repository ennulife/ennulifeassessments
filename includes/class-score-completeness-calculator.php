<?php
/**
 * ENNU Life Score Completeness Calculator
 *
 * This class is responsible for calculating the "Score Completeness" percentage.
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Score_Completeness_Calculator {

    private $user_id;
    private $all_definitions;
    private $completeness_weights;

    public function __construct( $user_id, $all_definitions ) {
        $this->user_id = $user_id;
        $this->all_definitions = $all_definitions;
        $this->completeness_weights = array(
            'assessments' => 60,
            'health_optimization' => 20,
            'health_goals' => 20,
        );
        error_log("ScoreCompletenessCalculator: Instantiated for user ID {$user_id}.");
    }

    public function calculate() {
        error_log("ScoreCompletenessCalculator: Starting calculation.");
        $achieved_points = 0;

        // Calculate points for completed assessments
        $total_assessments = count($this->all_definitions);
        $completed_assessments = 0;
        foreach ( $this->all_definitions as $assessment_key => $config ) {
            if ( get_user_meta( $this->user_id, 'ennu_' . $assessment_key . '_calculated_score', true ) ) {
                $completed_assessments++;
            }
        }
        if ($total_assessments > 0) {
            $achieved_points += ( $completed_assessments / $total_assessments ) * $this->completeness_weights['assessments'];
            error_log("ScoreCompletenessCalculator: User has completed {$completed_assessments}/{$total_assessments} assessments, awarding points.");
        }

        // Calculate points for completing the health optimization assessment
        $health_opt_data = get_user_meta( $this->user_id, 'ennu_health_optimization_assessment_symptom_q1', true );
        if ( ! empty( $health_opt_data ) ) {
            $achieved_points += $this->completeness_weights['health_optimization'];
            error_log("ScoreCompletenessCalculator: Health optimization completed, awarding points.");
        }

        // Calculate points for setting health goals
        if ( get_user_meta( $this->user_id, 'ennu_global_health_goals', true ) ) {
            $achieved_points += $this->completeness_weights['health_goals'];
            error_log("ScoreCompletenessCalculator: Health goals are set, awarding points.");
        }

        $final_score = round( $achieved_points );
        error_log("ScoreCompletenessCalculator: Final completeness score: {$final_score}");
        return $final_score;
    }
}  