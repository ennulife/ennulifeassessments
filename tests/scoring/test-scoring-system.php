<?php
/**
 * Scoring System Tests for ENNU Life Assessments
 * 
 * The definitive test suite for validating the mathematical precision
 * of the world's most advanced health assessment scoring system.
 */

class Test_ENNU_Scoring_System extends ENNU_Test_Case {

    /**
     * Scoring system instance
     * @var ENNU_Assessment_Scoring
     */
    private $scoring;

    /**
     * Set up scoring tests
     */
    public function setUp(): void {
        parent::setUp();
        
        if ( class_exists( 'ENNU_Assessment_Scoring' ) ) {
            $this->scoring = new ENNU_Assessment_Scoring();
        }
    }

    /**
     * Test scoring system initialization
     */
    public function test_scoring_system_initialization() {
        $this->assertInstanceOf( 'ENNU_Assessment_Scoring', $this->scoring );
        $this->assertTrue( method_exists( $this->scoring, 'calculate_assessment_score' ) );
    }

    /**
     * Test hair assessment scoring with valid data
     */
    public function test_hair_assessment_scoring_calculation() {
        // Prepare test data
        $form_data = array(
            'hair_concerns' => array( 'thinning', 'balding' ),
            'hair_loss_pattern' => 'crown',
            'age_range' => '30-39',
            'gender' => 'male',
            'severity_rating' => '7',
            'family_history' => 'yes',
        );

        // Calculate score
        $result = $this->scoring->calculate_assessment_score( 'hair_assessment', $form_data, $this->test_user_id );

        // Validate score structure
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'overall_score', $result );
        $this->assertArrayHasKey( 'category_scores', $result );
        $this->assertArrayHasKey( 'pillar_scores', $result );

        // Validate score ranges
        $this->assertIsNumeric( $result['overall_score'] );
        $this->assertGreaterThanOrEqual( 0, $result['overall_score'] );
        $this->assertLessThanOrEqual( 100, $result['overall_score'] );

        // Validate category scores
        $this->assertIsArray( $result['category_scores'] );
        foreach ( $result['category_scores'] as $category => $score ) {
            $this->assertIsNumeric( $score );
            $this->assertGreaterThanOrEqual( 0, $score );
            $this->assertLessThanOrEqual( 100, $score );
        }

        // Validate pillar scores
        $this->assertIsArray( $result['pillar_scores'] );
        $expected_pillars = array( 'mind', 'body', 'lifestyle', 'aesthetics' );
        foreach ( $expected_pillars as $pillar ) {
            $this->assertArrayHasKey( $pillar, $result['pillar_scores'] );
            $this->assertIsNumeric( $result['pillar_scores'][ $pillar ] );
            $this->assertGreaterThanOrEqual( 0, $result['pillar_scores'][ $pillar ] );
            $this->assertLessThanOrEqual( 100, $result['pillar_scores'][ $pillar ] );
        }
    }

    /**
     * Test score consistency - same input should produce same output
     */
    public function test_scoring_consistency() {
        $form_data = array(
            'current_weight' => '180',
            'target_weight' => '160',
            'activity_level' => 'moderate',
            'diet_quality' => 'good',
            'age_range' => '30-39',
            'gender' => 'male',
        );

        // Calculate score multiple times
        $result1 = $this->scoring->calculate_assessment_score( 'weight_loss_assessment', $form_data, $this->test_user_id );
        $result2 = $this->scoring->calculate_assessment_score( 'weight_loss_assessment', $form_data, $this->test_user_id );

        $this->assertEquals( $result1['overall_score'], $result2['overall_score'] );
        $this->assertEquals( $result1['category_scores'], $result2['category_scores'] );
        $this->assertEquals( $result1['pillar_scores'], $result2['pillar_scores'] );
    }

    /**
     * Test ENNU Life Score calculation after multiple assessments
     */
    public function test_ennu_life_score_calculation() {
        // Create multiple assessment data
        $this->create_mock_assessment_data( 'hair_assessment', 80 );
        $this->create_mock_assessment_data( 'weight_loss_assessment', 70 );

        // Trigger ENNU Life Score calculation
        if ( method_exists( $this->scoring, 'calculate_ennu_life_score' ) ) {
            $ennu_score = $this->scoring->calculate_ennu_life_score( $this->test_user_id );

            // Validate ENNU Life Score
            $this->assertIsNumeric( $ennu_score );
            $this->assertGreaterThanOrEqual( 0, $ennu_score );
            $this->assertLessThanOrEqual( 100, $ennu_score );

            // Check that score was saved to user meta
            $saved_score = get_user_meta( $this->test_user_id, 'ennu_life_score', true );
            $this->assertEquals( $ennu_score, $saved_score );
        }
    }

    /**
     * Test score validation with invalid data
     */
    public function test_scoring_with_invalid_data() {
        $invalid_data = array(
            'invalid_field' => 'invalid_value',
            'age_range' => '', // Empty required field
        );

        $result = $this->scoring->calculate_assessment_score( 'hair_assessment', $invalid_data, $this->test_user_id );

        // Should handle gracefully - either return error or default scores
        $this->assertIsArray( $result );
        
        if ( isset( $result['error'] ) ) {
            $this->assertNotEmpty( $result['error'] );
        } else {
            // Should return valid structure with default/minimum scores
            $this->assertArrayHasKey( 'overall_score', $result );
            $this->assertIsNumeric( $result['overall_score'] );
        }
    }

    /**
     * Test edge cases - boundary value testing
     */
    public function test_scoring_edge_cases() {
        // Test with minimal data
        $minimal_data = array(
            'age_range' => '18-25',
            'gender' => 'female',
        );

        $result = $this->scoring->calculate_assessment_score( 'health_assessment', $minimal_data, $this->test_user_id );
        $this->assertIsArray( $result );
        $this->assertArrayHasKey( 'overall_score', $result );

        // Test with maximum negative indicators
        $negative_data = array(
            'age_range' => '65+',
            'gender' => 'male',
            'health_conditions' => array( 'diabetes', 'hypertension', 'obesity' ),
            'activity_level' => 'sedentary',
            'stress_level' => 'very_high',
        );

        $result = $this->scoring->calculate_assessment_score( 'health_assessment', $negative_data, $this->test_user_id );
        $this->assertIsArray( $result );
        $this->assertGreaterThanOrEqual( 0, $result['overall_score'] );
    }

    /**
     * Test scoring performance with large datasets
     */
    public function test_scoring_performance() {
        $start_time = microtime( true );

        $large_data = array(
            'age_range' => '30-39',
            'gender' => 'male',
            'symptoms' => array_fill( 0, 50, 'test_symptom' ), // Large array
            'medications' => array_fill( 0, 20, 'test_medication' ),
        );

        $result = $this->scoring->calculate_assessment_score( 'health_assessment', $large_data, $this->test_user_id );

        $execution_time = microtime( true ) - $start_time;

        // Should complete within reasonable time (1 second)
        $this->assertLessThan( 1.0, $execution_time );
        $this->assertIsArray( $result );
    }

    /**
     * Test historical score tracking
     */
    public function test_historical_score_tracking() {
        // Create initial score
        $this->create_mock_assessment_data( 'hair_assessment', 60 );
        
        // Simulate time passage
        $this->advance_time( 86400 ); // 1 day
        
        // Create updated score
        $this->create_mock_assessment_data( 'hair_assessment', 75 );

        // Check history exists
        $history = get_user_meta( $this->test_user_id, 'ennu_life_score_history', true );
        
        if ( ! empty( $history ) ) {
            $this->assertIsArray( $history );
            $this->assertGreaterThan( 0, count( $history ) );
            
            // Each history entry should have timestamp and score
            foreach ( $history as $entry ) {
                $this->assertArrayHasKey( 'score', $entry );
                $this->assertArrayHasKey( 'timestamp', $entry );
                $this->assertIsNumeric( $entry['score'] );
                $this->assertNotEmpty( $entry['timestamp'] );
            }
        }
    }
}