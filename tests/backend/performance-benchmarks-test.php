<?php
/**
 * Performance benchmark tests for critical functions
 *
 * Tests performance of scoring calculations, database queries,
 * and other critical operations to ensure efficiency optimizations
 * are maintained.
 *
 * @package ENNU_Life_Tests
 */

use WP_UnitTestCase;

class PerformanceBenchmarksTest extends WP_UnitTestCase {

    /**
     * Test scoring system performance
     */
    public function test_scoring_system_performance() {
        $user_id = self::factory()->user->create();
        
        $assessments = array('hair', 'skin', 'sleep', 'hormone');
        foreach ($assessments as $assessment) {
            update_user_meta($user_id, "ennu_{$assessment}_assessment_category_scores", array(
                "{$assessment}_health" => rand(50, 90) / 10,
                "{$assessment}_condition" => rand(60, 85) / 10,
                "{$assessment}_symptoms" => rand(40, 80) / 10
            ));
        }
        
        $start_time = microtime(true);
        ENNU_Assessment_Scoring::calculate_and_save_all_user_scores($user_id);
        $execution_time = microtime(true) - $start_time;
        
        $this->assertLessThan(2.0, $execution_time, 'Complete scoring should finish within 2 seconds');
        
        $ennu_life_score = get_user_meta($user_id, 'ennu_life_score', true);
        $this->assertIsNumeric($ennu_life_score);
        $this->assertGreaterThan(0, $ennu_life_score);
        $this->assertLessThanOrEqual(10, $ennu_life_score);
    }

    /**
     * Test database query efficiency for N+1 optimization
     */
    public function test_database_query_efficiency() {
        $user_id = self::factory()->user->create();
        
        $assessments = array('hair', 'skin', 'sleep', 'hormone', 'nutrition');
        foreach ($assessments as $assessment) {
            update_user_meta($user_id, "ennu_{$assessment}_assessment_category_scores", array(
                "{$assessment}_health" => rand(50, 90) / 10,
                "{$assessment}_condition" => rand(60, 85) / 10
            ));
        }
        
        $queries_before = get_num_queries();
        ENNU_Assessment_Scoring::calculate_average_pillar_scores($user_id);
        $queries_after = get_num_queries();
        
        $query_count = $queries_after - $queries_before;
        $this->assertLessThan(15, $query_count, 'Should use minimal database queries (< 15) due to N+1 optimization');
    }

    /**
     * Test individual scoring calculation performance
     */
    public function test_individual_scoring_performance() {
        $responses = array(
            'hair_q1' => 'male',
            'hair_q2' => 'receding',
            'hair_q6' => 'high',
            'hair_q7' => 'yes',
            'hair_q8' => 'moderate',
            'hair_q9' => 'daily',
            'hair_q10' => 'stressed'
        );
        
        $start_time = microtime(true);
        $result = ENNU_Assessment_Scoring::calculate_scores_for_assessment('hair_assessment', $responses);
        $execution_time = microtime(true) - $start_time;
        
        $this->assertLessThan(0.1, $execution_time, 'Single assessment scoring should complete within 100ms');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('overall_score', $result);
        $this->assertArrayHasKey('category_scores', $result);
    }

    /**
     * Test configuration caching performance
     */
    public function test_configuration_caching_performance() {
        if (method_exists('ENNU_Assessment_Scoring', 'clear_configuration_cache')) {
            ENNU_Assessment_Scoring::clear_configuration_cache();
        }
        
        $start_time = microtime(true);
        $definitions1 = ENNU_Assessment_Scoring::get_assessment_definitions();
        $first_call_time = microtime(true) - $start_time;
        
        $start_time = microtime(true);
        $definitions2 = ENNU_Assessment_Scoring::get_assessment_definitions();
        $second_call_time = microtime(true) - $start_time;
        
        $this->assertLessThan($first_call_time, $second_call_time, 'Cached call should be faster than initial call');
        $this->assertEquals($definitions1, $definitions2, 'Cached data should match original data');
    }

    /**
     * Test memory usage during large operations
     */
    public function test_memory_usage_efficiency() {
        $memory_before = memory_get_usage();
        
        $user_ids = array();
        for ($i = 0; $i < 10; $i++) {
            $user_id = self::factory()->user->create();
            $user_ids[] = $user_id;
            
            update_user_meta($user_id, 'ennu_hair_assessment_category_scores', array(
                'hair_health' => rand(50, 90) / 10,
                'scalp_condition' => rand(60, 85) / 10
            ));
            
            ENNU_Assessment_Scoring::calculate_and_save_all_user_scores($user_id);
        }
        
        $memory_after = memory_get_usage();
        $memory_used = $memory_after - $memory_before;
        
        $this->assertLessThan(10 * 1024 * 1024, $memory_used, 'Memory usage should be reasonable for multiple users');
    }

    /**
     * Test AJAX endpoint response times
     */
    public function test_ajax_endpoint_performance() {
        $_POST = array(
            'action' => 'ennu_submit_assessment',
            'nonce' => wp_create_nonce('ennu_ajax_nonce'),
            'assessment_type' => 'hair_assessment',
            'hair_q1' => 'male',
            'hair_q2' => 'receding',
            'email' => 'test@example.com'
        );
        
        $start_time = microtime(true);
        
        try {
            do_action('wp_ajax_ennu_submit_assessment');
            do_action('wp_ajax_nopriv_ennu_submit_assessment');
        } catch (Exception $e) {
        }
        
        $execution_time = microtime(true) - $start_time;
        
        $this->assertLessThan(1.0, $execution_time, 'AJAX endpoint should respond within 1 second');
    }

    /**
     * Test large dataset handling
     */
    public function test_large_dataset_performance() {
        $user_id = self::factory()->user->create();
        
        $large_responses = array();
        for ($i = 1; $i <= 50; $i++) {
            $large_responses["question_$i"] = "answer_$i";
        }
        
        $start_time = microtime(true);
        $result = ENNU_Assessment_Scoring::calculate_scores_for_assessment('comprehensive_assessment', $large_responses);
        $execution_time = microtime(true) - $start_time;
        
        $this->assertLessThan(0.5, $execution_time, 'Large dataset processing should complete within 500ms');
        $this->assertIsArray($result);
    }

    /**
     * Test concurrent operations simulation
     */
    public function test_concurrent_operations_simulation() {
        $user_ids = array();
        $execution_times = array();
        
        for ($i = 0; $i < 5; $i++) {
            $user_id = self::factory()->user->create();
            $user_ids[] = $user_id;
            
            update_user_meta($user_id, 'ennu_hair_assessment_category_scores', array(
                'hair_health' => rand(50, 90) / 10
            ));
            
            $start_time = microtime(true);
            ENNU_Assessment_Scoring::calculate_and_save_all_user_scores($user_id);
            $execution_times[] = microtime(true) - $start_time;
        }
        
        $average_time = array_sum($execution_times) / count($execution_times);
        $max_time = max($execution_times);
        
        $this->assertLessThan(2.0, $average_time, 'Average concurrent operation time should be reasonable');
        $this->assertLessThan(3.0, $max_time, 'Maximum concurrent operation time should not exceed 3 seconds');
    }

    /**
     * Test performance regression detection
     */
    public function test_performance_regression_detection() {
        $baseline_operations = array(
            'single_scoring' => 0.1,      // 100ms
            'complete_scoring' => 2.0,    // 2 seconds
            'database_queries' => 15,     // 15 queries max
            'memory_usage' => 5 * 1024 * 1024  // 5MB max
        );
        
        $user_id = self::factory()->user->create();
        update_user_meta($user_id, 'ennu_hair_assessment_category_scores', array(
            'hair_health' => 7.5
        ));
        
        $start_time = microtime(true);
        ENNU_Assessment_Scoring::calculate_scores_for_assessment('hair_assessment', array('hair_q1' => 'male'));
        $single_time = microtime(true) - $start_time;
        
        $start_time = microtime(true);
        ENNU_Assessment_Scoring::calculate_and_save_all_user_scores($user_id);
        $complete_time = microtime(true) - $start_time;
        
        $this->assertLessThan($baseline_operations['single_scoring'], $single_time, 
            'Single scoring performance regression detected');
        $this->assertLessThan($baseline_operations['complete_scoring'], $complete_time, 
            'Complete scoring performance regression detected');
    }
}
