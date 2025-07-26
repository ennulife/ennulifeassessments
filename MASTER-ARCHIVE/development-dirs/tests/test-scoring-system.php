<?php
/**
 * Test ENNU Scoring System
 */

class Test_ENNU_Scoring_System extends WP_UnitTestCase {

	private $user_id;

	public function setUp(): void {
		parent::setUp();
		$this->user_id = $this->factory->user->create();
	}

	public function test_life_score_calculation() {
		$calculator = new ENNU_Life_Score_Calculator();

		update_user_meta( $this->user_id, 'ennu_mind_pillar_score', 75 );
		update_user_meta( $this->user_id, 'ennu_body_pillar_score', 80 );
		update_user_meta( $this->user_id, 'ennu_lifestyle_pillar_score', 70 );
		update_user_meta( $this->user_id, 'ennu_aesthetics_pillar_score', 85 );

		$life_score = $calculator->calculate_life_score( $this->user_id );

		$this->assertIsNumeric( $life_score );
		$this->assertGreaterThan( 0, $life_score );
		$this->assertLessThanOrEqual( 100, $life_score );
	}

	public function test_pillar_score_calculation() {
		$calculator = new ENNU_Pillar_Score_Calculator();

		update_user_meta( $this->user_id, 'ennu_welcome_calculated_score', 80 );
		update_user_meta( $this->user_id, 'ennu_health_calculated_score', 75 );

		$pillar_scores = $calculator->calculate_pillar_scores( $this->user_id );

		$this->assertIsArray( $pillar_scores );
		$this->assertArrayHasKey( 'mind', $pillar_scores );
		$this->assertArrayHasKey( 'body', $pillar_scores );
		$this->assertArrayHasKey( 'lifestyle', $pillar_scores );
		$this->assertArrayHasKey( 'aesthetics', $pillar_scores );
	}

	public function test_intentionality_engine() {
		$engine = new ENNU_Intentionality_Engine();

		update_user_meta( $this->user_id, 'ennu_global_health_goals', array( 'weight_loss', 'energy_boost' ) );
		update_user_meta( $this->user_id, 'ennu_body_pillar_score', 70 );

		$boosted_scores = $engine->apply_goal_alignment_boost(
			$this->user_id,
			array(
				'body'       => 70,
				'mind'       => 75,
				'lifestyle'  => 80,
				'aesthetics' => 65,
			)
		);

		$this->assertGreaterThan( 70, $boosted_scores['body'] );
	}

	public function test_new_life_score_calculation() {
		$calculator = new ENNU_New_Life_Score_Calculator();

		update_user_meta( $this->user_id, 'ennu_life_score', 75 );
		update_user_meta(
			$this->user_id,
			'ennu_biomarker_data',
			array(
				'testosterone' => array(
					'value' => 300,
					'unit'  => 'ng/dL',
				),
				'vitamin_d'    => array(
					'value' => 20,
					'unit'  => 'ng/mL',
				),
			)
		);
		update_user_meta(
			$this->user_id,
			'ennu_doctor_targets',
			array(
				'testosterone' => 500,
				'vitamin_d'    => 40,
			)
		);

		$new_life_score = $calculator->calculate_new_life_score( $this->user_id );

		$this->assertIsNumeric( $new_life_score );
		$this->assertGreaterThan( 75, $new_life_score );
	}
}
