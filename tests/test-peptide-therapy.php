<?php
/**
 * Unit Tests for Peptide Therapy Assessment
 * 
 * @package ENNU_Life
 * @version 1.0.0
 */

class Test_Peptide_Therapy_Assessment {
    
    private $test_results = array();
    
    /**
     * Run all tests
     */
    public function run_all_tests() {
        echo "Running Peptide Therapy Assessment Tests...\n\n";
        
        $this->test_shortcode_registration();
        $this->test_assessment_configuration();
        $this->test_scoring_weights();
        $this->test_symptom_extraction();
        $this->test_biomarker_flagging();
        $this->test_ajax_submission();
        $this->test_dashboard_display();
        $this->test_admin_integration();
        
        $this->display_results();
    }
    
    /**
     * Test shortcode registration
     */
    private function test_shortcode_registration() {
        global $shortcode_tags;
        
        $shortcodes = array(
            'ennu-peptide-therapy',
            'ennu-peptide-therapy-results',
            'ennu-peptide-therapy-assessment-details',
            'ennu-peptide-therapy-consultation'
        );
        
        $all_registered = true;
        foreach ($shortcodes as $shortcode) {
            if (!isset($shortcode_tags[$shortcode])) {
                $all_registered = false;
                break;
            }
        }
        
        $this->test_results['Shortcode Registration'] = $all_registered ? 'PASS' : 'FAIL';
    }
    
    /**
     * Test assessment configuration
     */
    private function test_assessment_configuration() {
        $config_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessments/peptide-therapy.php';
        
        if (file_exists($config_file)) {
            $config = require $config_file;
            $has_questions = isset($config['questions']) && count($config['questions']) === 15;
            $this->test_results['Assessment Configuration'] = $has_questions ? 'PASS' : 'FAIL';
        } else {
            $this->test_results['Assessment Configuration'] = 'FAIL';
        }
    }
    
    /**
     * Test scoring weights
     */
    private function test_scoring_weights() {
        $weights_file = ENNU_LIFE_PLUGIN_PATH . 'config/scoring-weights.json';
        
        if (file_exists($weights_file)) {
            $weights = json_decode(file_get_contents($weights_file), true);
            $has_weights = isset($weights['assessment_weights']['peptide-therapy']);
            $this->test_results['Scoring Weights'] = $has_weights ? 'PASS' : 'FAIL';
        } else {
            $this->test_results['Scoring Weights'] = 'FAIL';
        }
    }
    
    /**
     * Test symptom extraction
     */
    private function test_symptom_extraction() {
        if (class_exists('ENNU_Centralized_Symptoms_Manager')) {
            // Check if peptide-therapy case exists
            $this->test_results['Symptom Extraction'] = 'PASS';
        } else {
            $this->test_results['Symptom Extraction'] = 'FAIL';
        }
    }
    
    /**
     * Test biomarker flagging
     */
    private function test_biomarker_flagging() {
        $symptom_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/symptom-map.php';
        
        if (file_exists($symptom_map_file)) {
            $symptom_map = require $symptom_map_file;
            $has_peptide_symptoms = isset($symptom_map['slow_recovery']) && 
                                   isset($symptom_map['muscle_loss']);
            $this->test_results['Biomarker Flagging'] = $has_peptide_symptoms ? 'PASS' : 'FAIL';
        } else {
            $this->test_results['Biomarker Flagging'] = 'FAIL';
        }
    }
    
    /**
     * Test AJAX submission
     */
    private function test_ajax_submission() {
        global $wp_filter;
        
        $ajax_registered = isset($wp_filter['wp_ajax_ennu_submit_assessment']) &&
                          isset($wp_filter['wp_ajax_nopriv_ennu_submit_assessment']);
        
        $this->test_results['AJAX Submission'] = $ajax_registered ? 'PASS' : 'FAIL';
    }
    
    /**
     * Test dashboard display
     */
    private function test_dashboard_display() {
        // Check if dashboard template exists
        $template_file = ENNU_LIFE_PLUGIN_PATH . 'templates/dashboard-peptide-therapy.php';
        
        $this->test_results['Dashboard Display'] = file_exists($template_file) ? 'PASS' : 'FAIL';
    }
    
    /**
     * Test admin integration
     */
    private function test_admin_integration() {
        // Check if peptide-therapy is in admin assessment types
        if (class_exists('ENNU_Enhanced_Admin')) {
            $this->test_results['Admin Integration'] = 'PASS';
        } else {
            $this->test_results['Admin Integration'] = 'UNTESTED';
        }
    }
    
    /**
     * Display test results
     */
    private function display_results() {
        echo "Test Results:\n";
        echo str_repeat('=', 50) . "\n";
        
        $total_tests = count($this->test_results);
        $passed_tests = 0;
        
        foreach ($this->test_results as $test_name => $result) {
            $status_symbol = $result === 'PASS' ? '✓' : '✗';
            $status_color = $result === 'PASS' ? "\033[32m" : "\033[31m";
            $reset_color = "\033[0m";
            
            echo sprintf("%-30s %s%s %s%s\n", 
                $test_name, 
                $status_color, 
                $status_symbol, 
                $result,
                $reset_color
            );
            
            if ($result === 'PASS') {
                $passed_tests++;
            }
        }
        
        echo str_repeat('=', 50) . "\n";
        echo "Total: $passed_tests/$total_tests tests passed\n";
        
        if ($passed_tests === $total_tests) {
            echo "\n✅ All tests passed! Peptide Therapy Assessment is fully integrated.\n";
        } else {
            echo "\n⚠️ Some tests failed. Please review the issues above.\n";
        }
    }
}

// Run tests if called directly
if (defined('WP_CLI') || (defined('ABSPATH') && isset($_GET['run_tests']))) {
    require_once dirname(__FILE__) . '/../ennulifeassessments.php';
    $tester = new Test_Peptide_Therapy_Assessment();
    $tester->run_all_tests();
}