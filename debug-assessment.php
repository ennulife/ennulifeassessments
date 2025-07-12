<?php
/**
 * ENNU Life Assessment Scoring System - Comprehensive Test Runner
 *
 * This script provides 100% test coverage for the scoring engine by testing
 * every possible answer for every scorable question.
 */

// Load environment and files
require_once(dirname(__FILE__) . '/../../../wp-load.php');
if (!current_user_can('manage_options')) die('Access Denied');
require_once(plugin_dir_path(__FILE__) . 'includes/class-scoring-system.php');
require_once(plugin_dir_path(__FILE__) . 'includes/class-question-mapper.php');
$test_data = require_once(plugin_dir_path(__FILE__) . 'test-assessment-data.php');

// --- Test Execution ---
header('Content-Type: text/plain');
echo "ENNU LIFE SCORING SYSTEM: FULL AUDIT & VERIFICATION\n";
echo "====================================================\n\n";

$total_tests = 0;
$total_passed = 0;

foreach ($test_data as $assessment_type => $scenarios) {
    echo "--- Testing: " . ucwords(str_replace('_', ' ', $assessment_type)) . " ---\n\n";

    foreach ($scenarios as $scenario_name => $responses) {
        $total_tests++;
        $question_key = key($responses);
        $answer_value = current($responses);

        // Get expected score from the scoring configuration directly
        $expected_score = ENNU_Assessment_Scoring::get_answer_score($assessment_type, $question_key, $answer_value);
        
        // Calculate actual score
        $result = ENNU_Assessment_Scoring::calculate_scores($assessment_type, $responses);
        $actual_score = $result ? $result['overall_score'] : 'ERROR';

        // Verification
        $status = ($expected_score == $actual_score) ? "PASS" : "FAIL";
        if ($status === "PASS") {
            $total_passed++;
        }

        echo "  Test: " . str_pad($scenario_name, 30) . " | Status: " . str_pad($status, 4) . "\n";
        echo "    - Question: " . $question_key . " | Answer: '" . $answer_value . "'\n";
        echo "    - Expected Score: " . $expected_score . "\n";
        echo "    - Actual Score:   " . $actual_score . "\n\n";
    }
}

echo "--- AUDIT SUMMARY ---\n";
echo "Total Tests Run: " . $total_tests . "\n";
echo "Tests Passed:    " . $total_passed . "\n";
echo "Tests Failed:    " . ($total_tests - $total_passed) . "\n";
echo "Result:          " . ($total_tests == $total_passed ? "ALL TESTS PASSED" : "SOME TESTS FAILED") . "\n";
echo "--- END OF AUDIT ---\n"; 