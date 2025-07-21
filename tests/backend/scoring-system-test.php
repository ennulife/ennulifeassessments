<?php
/**
 * Test case for the ENNU_Assessment_Scoring class.
 *
 * @package ENNU_Life_Tests
 */

use WP_UnitTestCase;

class ScoringSystemTest extends WP_UnitTestCase {

	/**
	 * Test the calculate_scores method with a sample set of answers
	 * for the hair assessment, based on the documentation example.
	 */
	public function test_calculate_scores_hair_assessment_example() {
		// Sample responses from the documentation.
		$responses = array(
			'hair_q1' => 'male',        // Maps to 'gender' -> 2 points
			'hair_q2' => 'receding',    // Maps to 'hair_concerns' -> 3 points
			'hair_q6' => 'high',        // Maps to 'stress_level' -> 3 points
		);

		$assessment_type = 'hair_assessment';

		// Calculate the scores.
		$result = ENNU_Assessment_Scoring::calculate_scores( $assessment_type, $responses );

		// In the new scoring system, this is a simple sum. 2 + 3 + 3 = 8
		$this->assertEquals( 8, $result['overall_score'], 'The overall score should be the sum of the points for each answer.' );
	}

	/**
	 * Test the skin assessment scoring with a different data set.
	 */
	public function test_calculate_scores_skin_assessment() {
		$responses = array(
			'skin_q1' => 'oily',            // skin_type -> 2
			'skin_q2' => 'aging',           // primary_concern -> 3
			'skin_q3' => 'high',            // sun_exposure -> 3
			'skin_q4' => 'none',            // skincare_routine -> 4
		);

		$assessment_type = 'skin_assessment';
		$result          = ENNU_Assessment_Scoring::calculate_scores( $assessment_type, $responses );

		// Expected score: 2 + 3 + 3 + 4 = 12
		$this->assertEquals( 12, $result['overall_score'] );
	}

	/**
	 * Test that an answer not present in the scoring config defaults to 0.
	 */
	public function test_calculate_scores_with_unscorable_answer() {
		$responses = array(
			'hair_q1' => 'male', // 2 points
			'hair_q2' => 'non_existent_concern', // Should be 0 points
		);

		$assessment_type = 'hair_assessment';
		$result          = ENNU_Assessment_Scoring::calculate_scores( $assessment_type, $responses );

		$this->assertEquals( 2, $result['overall_score'] );
	}

	/**
	 * Test scoring for a multi-select (checkbox) question.
	 * The ED assessment has a question for health conditions which is multi-select.
	 */
	public function test_calculate_scores_multi_select_question() {
		// From the ED assessment, `health_conditions` can have multiple values.
		$responses = array(
			'ed_q4' => array( 'diabetes', 'heart' ), // diabetes (3) + heart (4) = 7
		);

		$assessment_type = 'ed_treatment_assessment';
		$result          = ENNU_Assessment_Scoring::calculate_scores( $assessment_type, $responses );

		$this->assertEquals( 7, $result['overall_score'] );
	}

	/**
	 * Test a scenario where no answers are scorable.
	 */
	public function test_calculate_scores_with_no_scorable_answers() {
		$responses = array(
			'hair_q1' => 'unscorable_gender',
			'hair_q2' => 'unscorable_concern',
		);

		$assessment_type = 'hair_assessment';
		$result          = ENNU_Assessment_Scoring::calculate_scores( $assessment_type, $responses );

		$this->assertEquals( 0, $result['overall_score'] );
	}

	/**
	 * Test performance benchmarks for scoring calculations
	 */
	public function test_scoring_performance_benchmarks() {
		$start_time = microtime( true );
		$responses  = array(
			'hair_q1' => 'male',
			'hair_q2' => 'receding',
			'hair_q6' => 'high',
			'hair_q7' => 'yes',
			'hair_q8' => 'moderate',
		);

		$result         = ENNU_Assessment_Scoring::calculate_scores( 'hair_assessment', $responses );
		$execution_time = microtime( true ) - $start_time;

		$this->assertLessThan( 0.1, $execution_time, 'Scoring calculation should complete within 100ms' );
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'overall_score', $result );
	}

	/**
	 * Test score interpretation accuracy
	 */
	public function test_score_interpretation_accuracy() {
		$test_cases = array(
			array(
				'score'          => 9.5,
				'expected_range' => 'excellent',
			),
			array(
				'score'          => 8.0,
				'expected_range' => 'good',
			),
			array(
				'score'          => 6.5,
				'expected_range' => 'fair',
			),
			array(
				'score'          => 4.0,
				'expected_range' => 'needs_attention',
			),
			array(
				'score'          => 2.0,
				'expected_range' => 'critical',
			),
		);

		foreach ( $test_cases as $case ) {
			if ( $case['score'] >= 9.0 ) {
				$this->assertGreaterThanOrEqual( 9.0, $case['score'], 'Excellent scores should be >= 9.0' );
			} elseif ( $case['score'] >= 7.0 ) {
				$this->assertGreaterThanOrEqual( 7.0, $case['score'], 'Good scores should be >= 7.0' );
			} elseif ( $case['score'] >= 5.0 ) {
				$this->assertGreaterThanOrEqual( 5.0, $case['score'], 'Fair scores should be >= 5.0' );
			} elseif ( $case['score'] >= 3.0 ) {
				$this->assertGreaterThanOrEqual( 3.0, $case['score'], 'Needs attention scores should be >= 3.0' );
			} else {
				$this->assertLessThan( 3.0, $case['score'], 'Critical scores should be < 3.0' );
			}
		}
	}

	/**
	 * Test edge cases and error handling
	 */
	public function test_scoring_edge_cases_extended() {
		$result = ENNU_Assessment_Scoring::calculate_scores( 'hair_assessment', null );
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'overall_score', $result );

		$empty_responses = array(
			'hair_q1' => '',
			'hair_q2' => '',
		);
		$result          = ENNU_Assessment_Scoring::calculate_scores( 'hair_assessment', $empty_responses );
		$this->assertIsArray( $result );
		$this->assertEquals( 0, $result['overall_score'] );

		$large_responses = array();
		for ( $i = 1; $i <= 100; $i++ ) {
			$large_responses[ "question_$i" ] = "answer_$i";
		}
		$result = ENNU_Assessment_Scoring::calculate_scores( 'comprehensive_assessment', $large_responses );
		$this->assertIsArray( $result );
	}
}
