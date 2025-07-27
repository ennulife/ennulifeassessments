<?php
/**
 * Test Instant Assessment Workflow
 * 
 * @package ENNU_Life
 * @version 64.2.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Test_Instant_Workflow {
    
    public static function run_test() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Access denied' );
        }
        
        $user_id = 1; // Admin user
        
        echo "<h1>Instant Assessment Workflow Test</h1>";
        
        // Step 1: Clear existing data
        echo "<h2>1. Clearing Existing Data</h2>";
        delete_user_meta($user_id, 'ennu_centralized_symptoms');
        delete_user_meta($user_id, 'ennu_biomarker_flags');
        
        // Clear weight loss assessment data
        for ($i = 1; $i <= 13; $i++) {
            delete_user_meta($user_id, "ennu_weight-loss_wl_q{$i}");
        }
        
        echo "<p>✅ Cleared existing symptoms and biomarker flags</p>";
        
        // Step 2: Populate test weight loss assessment data
        echo "<h2>2. Populating Test Weight Loss Assessment Data</h2>";
        
        $test_data = array(
            'ennu_weight-loss_wl_q9' => array('thyroid', 'insulin_resistance'),
            'ennu_weight-loss_wl_q10' => 'somewhat',
            'ennu_weight-loss_wl_q5' => 'less_than_5',
            'ennu_weight-loss_wl_q6' => 'high',
            'ennu_weight-loss_wl_q8' => 'often',
            'ennu_weight-loss_score_calculated_at' => current_time('mysql')
        );
        
        foreach ($test_data as $key => $value) {
            update_user_meta($user_id, $key, $value);
            echo "<p>✅ Set {$key} = " . (is_array($value) ? implode(', ', $value) : $value) . "</p>";
        }
        
        // Step 3: Manually trigger the assessment completion workflow
        echo "<h2>3. Triggering Instant Assessment Completion Workflow</h2>";
        
        // Clear debug log first
        error_log("=== INSTANT WORKFLOW TEST START ===");
        
        // Trigger the assessment completion action
        do_action('ennu_assessment_completed', $user_id, 'weight_loss');
        
        echo "<p>✅ Triggered assessment completion action</p>";
        
        // Step 4: Check results
        echo "<h2>4. Checking Results</h2>";
        
        // Check centralized symptoms
        $centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
        echo "<h3>Centralized Symptoms:</h3>";
        echo "<pre>" . print_r($centralized_symptoms, true) . "</pre>";
        
        // Check biomarker flags
        $flag_manager = new ENNU_Biomarker_Flag_Manager();
        $user_flags = $flag_manager->get_user_flags($user_id);
        echo "<h3>Biomarker Flags:</h3>";
        echo "<pre>" . print_r($user_flags, true) . "</pre>";
        
        // Step 5: Summary
        echo "<h2>5. Workflow Summary</h2>";
        
        $symptoms_count = isset($centralized_symptoms['total_count']) ? $centralized_symptoms['total_count'] : 0;
        $flags_count = is_array($user_flags) ? count($user_flags) : 0;
        
        echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 8px; border-left: 4px solid #4caf50;'>";
        echo "<h3>✅ Instant Workflow Results:</h3>";
        echo "<ul>";
        echo "<li><strong>Symptoms Extracted:</strong> {$symptoms_count}</li>";
        echo "<li><strong>Biomarkers Flagged:</strong> {$flags_count}</li>";
        echo "<li><strong>Workflow Status:</strong> " . ($symptoms_count > 0 && $flags_count > 0 ? 'SUCCESS' : 'NEEDS ATTENTION') . "</li>";
        echo "</ul>";
        echo "</div>";
        
        echo "<h2>6. Test Complete</h2>";
        echo "<p>This test verifies that both symptoms and biomarker flagging happen instantly when an assessment is submitted.</p>";
        echo "<p><strong>Next Steps:</strong> Submit a real weight loss assessment to see the instant workflow in action!</p>";
    }
} 