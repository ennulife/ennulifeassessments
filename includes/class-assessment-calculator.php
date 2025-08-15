<?php
/**
 * ENNU Life Assessment Score Calculator
 *
 * This class is responsible for calculating the overall score for a single assessment.
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Assessment_Calculator {

	private $assessment_type;
	private $responses;
	private $all_definitions;

	public function __construct( $assessment_type, $responses, $all_definitions ) {
		$this->assessment_type = $assessment_type;
		$this->responses       = $responses;
		$this->all_definitions = $all_definitions;
		// REMOVED: // REMOVED DEBUG LOG: error_log( "AssessmentCalculator: Instantiated for assessment type '{$assessment_type}'." );
	}

	public function calculate() {
		// REMOVED: // REMOVED DEBUG LOG: error_log( "AssessmentCalculator: Starting calculation for '{$this->assessment_type}'." );
		$assessment_questions = $this->all_definitions[ $this->assessment_type ] ?? array();

		if ( empty( $assessment_questions ) ) {
			// REMOVED: error_log( "AssessmentCalculator: No questions found for '{$this->assessment_type}'. Returning 0." );
			return 0;
		}

		$total_score  = 0;
		$total_weight = 0;

		$questions_to_iterate = isset( $assessment_questions['questions'] ) ? $assessment_questions['questions'] : $assessment_questions;
		// REMOVED: // REMOVED DEBUG LOG: error_log( 'AssessmentCalculator: Found ' . count( $questions_to_iterate ) . ' questions to iterate.' );

		foreach ( $questions_to_iterate as $question_key => $question_def ) {
			if ( ! isset( $this->responses[ $question_key ] ) ) {
				continue;
			}
			$answer = $this->responses[ $question_key ];
			// REMOVED: error_log( "AssessmentCalculator: Processing question '{$question_key}' with answer: " . print_r( $answer, true ) );

			if ( isset( $question_def['scoring'] ) ) {
				$scoring_rules = $question_def['scoring'];
				$weight        = $scoring_rules['weight'] ?? 1;

				$answers_to_process = is_array( $answer ) ? $answer : array( $answer );

				foreach ( $answers_to_process as $single_answer ) {
					$score = $scoring_rules['answers'][ $single_answer ] ?? 0;
					// REMOVED: error_log( "AssessmentCalculator: Answer '{$single_answer}' gets score of {$score} with weight {$weight}." );
					if ( $weight > 0 ) {
						$total_score  += $score * $weight;
						$total_weight += $weight;
					}
				}
			} else {
				// REMOVED: error_log( "AssessmentCalculator: Question '{$question_key}' has no scoring rules." );
			}
		}

		$final_score = $total_weight > 0 ? round( $total_score / $total_weight, 1 ) : 0;
		// REMOVED: // REMOVED DEBUG LOG: error_log( "AssessmentCalculator: Final overall score for '{$this->assessment_type}' is {$final_score}." );
		return $final_score;
	}
}
