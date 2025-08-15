<?php
/**
 * ENNU Life Category Score Calculator
 *
 * This class is responsible for calculating the scores for each category within a single assessment.
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Category_Score_Calculator {

	private $assessment_type;
	private $responses;
	private $all_definitions;

	public function __construct( $assessment_type, $responses, $all_definitions ) {
		$this->assessment_type = $assessment_type;
		$this->responses       = $responses;
		$this->all_definitions = $all_definitions;
		// REMOVED: // REMOVED DEBUG LOG: error_log( "CategoryScoreCalculator: Instantiated for assessment type '{$assessment_type}'." );
	}

	public function calculate() {
		// REMOVED: // REMOVED DEBUG LOG: error_log( "CategoryScoreCalculator: Starting calculation for '{$this->assessment_type}'." );
		$assessment_questions = $this->all_definitions[ $this->assessment_type ] ?? array();
		if ( empty( $assessment_questions ) ) {
			// REMOVED: error_log( "CategoryScoreCalculator: No questions found for '{$this->assessment_type}'. Returning empty array." );
			return array();
		}

		$category_scores      = array();
		$questions_to_iterate = isset( $assessment_questions['questions'] ) ? $assessment_questions['questions'] : $assessment_questions;
		// REMOVED: // REMOVED DEBUG LOG: error_log( 'CategoryScoreCalculator: Found ' . count( $questions_to_iterate ) . ' questions to iterate.' );

		foreach ( $questions_to_iterate as $question_key => $question_def ) {
			if ( ! isset( $this->responses[ $question_key ] ) ) {
				continue;
			}
			$answer = $this->responses[ $question_key ];

			if ( isset( $question_def['scoring'] ) ) {
				$scoring_rules = $question_def['scoring'];
				$category      = $scoring_rules['category'] ?? 'General';
				$weight        = $scoring_rules['weight'] ?? 1;

				$answers_to_process = is_array( $answer ) ? $answer : array( $answer );

				foreach ( $answers_to_process as $single_answer ) {
					$score = $scoring_rules['answers'][ $single_answer ] ?? 0;
					 // REMOVED: error_log( "CategoryScoreCalculator: Processing '{$question_key}' - Answer '{$single_answer}' in category '{$category}' gets score of {$score}." );

					if ( ! isset( $category_scores[ $category ] ) ) {
						$category_scores[ $category ] = array(
							'total'  => 0,
							'weight' => 0,
							'count'  => 0,
						);
					}

					$category_scores[ $category ]['total']  += $score * $weight;
					$category_scores[ $category ]['weight'] += $weight;
					$category_scores[ $category ]['count']++;
				}
			}
		}

		$final_category_scores = array();
		foreach ( $category_scores as $category => $data ) {
			if ( $data['weight'] > 0 ) {
				$final_category_scores[ $category ] = round( $data['total'] / $data['weight'], 1 );
			}
		}

		// REMOVED: // REMOVED DEBUG LOG: error_log( "CategoryScoreCalculator: Final category scores for '{$this->assessment_type}': " . print_r( $final_category_scores, true ) );
		return $final_category_scores;
	}
}
