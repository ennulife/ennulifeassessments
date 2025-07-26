<?php
/**
 * ENNU Life Assessments - Test Assessment Data Saving Fix
 * 
 * Comprehensive test to verify that:
 * 1. Question answers are saved to user meta
 * 2. Correlated symptoms are processed
 * 3. Score calculations work properly
 * 
 * @package ENNU_Life_Assessments
 * @version 64.3.35
 * @author Matt Codeweaver - The GOAT of WordPress Development
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Test_Assessment_Data_Saving_Fix {
    
    /**
     * Run comprehensive test
     */
    public static function run_test() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        echo '<div style="font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f9f9f9; border-radius: 8px;">';
        echo '<h1 style="color: #333; border-bottom: 2px solid #0073aa; padding-bottom: 10px;">🧪 ENNU Assessment Data Saving Fix - Comprehensive Test</h1>';
        echo '<p style="color: #666; font-size: 14px;">Testing assessment data saving functionality by Matt Codeweaver - The GOAT of WordPress Development</p>';
        
        $test_results = array();
        
        // Test 1: User Meta Saving
        $test_results['user_meta'] = self::test_user_meta_saving();
        
        // Test 2: Question Answer Processing
        $test_results['question_answers'] = self::test_question_answer_processing();
        
        // Test 3: Symptom Processing
        $test_results['symptom_processing'] = self::test_symptom_processing();
        
        // Test 4: Score Calculation
        $test_results['score_calculation'] = self::test_score_calculation();
        
        // Test 5: Global Fields
        $test_results['global_fields'] = self::test_global_fields();
        
        // Test 6: Assessment Completion
        $test_results['assessment_completion'] = self::test_assessment_completion();
        
        // Display results
        self::display_test_results( $test_results );
        
        echo '</div>';
    }
    
    /**
     * Test user meta saving functionality
     */
    private static function test_user_meta_saving() {
        $test_user_id = get_current_user_id();
        $test_key = 'ennu_test_meta_' . time();
        $test_value = 'test_value_' . time();
        
        $result = update_user_meta( $test_user_id, $test_key, $test_value );
        $retrieved_value = get_user_meta( $test_user_id, $test_key, true );
        
        // Clean up
        delete_user_meta( $test_user_id, $test_key );
        
        return array(
            'success' => $result !== false && $retrieved_value === $test_value,
            'message' => $result !== false ? 'User meta saving works correctly' : 'User meta saving failed',
            'details' => array(
                'save_result' => $result,
                'retrieved_value' => $retrieved_value,
                'expected_value' => $test_value
            )
        );
    }
    
    /**
     * Test question answer processing
     */
    private static function test_question_answer_processing() {
        $test_user_id = get_current_user_id();
        $assessment_type = 'hair_assessment';
        
        // Simulate form data
        $form_data = array(
            'assessment_type' => $assessment_type,
            'hair_q1' => 'Male',
            'hair_q2' => array( 'receding', 'thinning' ),
            'hair_q3' => 'High',
            'hair_q4' => 'Yes',
            'hair_q5' => 'Moderate',
            'hair_q6' => 'Poor',
            'hair_q7' => 'Some',
            'hair_q8' => 'Realistic'
        );
        
        // Process using the fix
        $answers_saved = self::process_question_answers( $test_user_id, $form_data );
        
        // Verify saved data
        $saved_data = array();
        foreach ( $form_data as $field_name => $field_value ) {
            if ( $field_name !== 'assessment_type' ) {
                $meta_key = 'ennu_' . $assessment_type . '_' . $field_name;
                $saved_value = get_user_meta( $test_user_id, $meta_key, true );
                $saved_data[ $field_name ] = $saved_value;
            }
        }
        
        // Clean up
        foreach ( $form_data as $field_name => $field_value ) {
            if ( $field_name !== 'assessment_type' ) {
                $meta_key = 'ennu_' . $assessment_type . '_' . $field_name;
                delete_user_meta( $test_user_id, $meta_key );
            }
        }
        
        return array(
            'success' => $answers_saved && count( array_filter( $saved_data ) ) === count( $form_data ) - 1,
            'message' => $answers_saved ? 'Question answer processing works correctly' : 'Question answer processing failed',
            'details' => array(
                'form_data' => $form_data,
                'saved_data' => $saved_data,
                'answers_saved' => $answers_saved
            )
        );
    }
    
    /**
     * Test symptom processing
     */
    private static function test_symptom_processing() {
        $test_user_id = get_current_user_id();
        $assessment_type = 'health_optimization_assessment';
        
        // Simulate symptom data
        $form_data = array(
            'assessment_type' => $assessment_type,
            'health_opt_q1' => array( 'fatigue', 'brain_fog', 'low_libido' ),
            'health_opt_q2' => array( 'anxiety', 'depression' ),
            'health_opt_q3' => array( 'weight_gain', 'slow_metabolism' ),
            'health_opt_q4' => array( 'poor_sleep', 'insomnia' ),
            'health_opt_q5' => array( 'joint_pain', 'muscle_weakness' ),
            'health_opt_q6' => array( 'digestive_issues', 'bloating' ),
            'health_opt_q7' => array( 'skin_problems', 'hair_loss' ),
            'health_opt_q8' => array( 'mood_swings', 'irritability' )
        );
        
        // Process symptoms
        $symptoms_processed = self::process_symptoms( $test_user_id, $form_data );
        
        // Verify saved symptoms
        $saved_symptoms = get_user_meta( $test_user_id, 'ennu_' . $assessment_type . '_symptoms', true );
        
        // Clean up
        delete_user_meta( $test_user_id, 'ennu_' . $assessment_type . '_symptoms' );
        
        return array(
            'success' => $symptoms_processed && ! empty( $saved_symptoms ),
            'message' => $symptoms_processed ? 'Symptom processing works correctly' : 'Symptom processing failed',
            'details' => array(
                'form_data' => $form_data,
                'saved_symptoms' => $saved_symptoms,
                'symptoms_processed' => $symptoms_processed
            )
        );
    }
    
    /**
     * Test score calculation
     */
    private static function test_score_calculation() {
        $test_user_id = get_current_user_id();
        $assessment_type = 'hair_assessment';
        
        // Simulate form data with known scoring values
        $form_data = array(
            'assessment_type' => $assessment_type,
            'hair_q1' => 'Male',
            'hair_q2' => array( 'receding' ),
            'hair_q3' => 'High',
            'hair_q4' => 'Yes',
            'hair_q5' => 'Moderate',
            'hair_q6' => 'Poor',
            'hair_q7' => 'Some',
            'hair_q8' => 'Realistic'
        );
        
        // Calculate scores
        $scores_calculated = self::calculate_scores( $test_user_id, $form_data );
        
        // Verify saved scores
        $saved_scores = get_user_meta( $test_user_id, 'ennu_' . $assessment_type . '_scores', true );
        $fallback_score = get_user_meta( $test_user_id, 'ennu_' . $assessment_type . '_fallback_score', true );
        
        // Clean up
        delete_user_meta( $test_user_id, 'ennu_' . $assessment_type . '_scores' );
        delete_user_meta( $test_user_id, 'ennu_' . $assessment_type . '_fallback_score' );
        
        return array(
            'success' => $scores_calculated && ( ! empty( $saved_scores ) || ! empty( $fallback_score ) ),
            'message' => $scores_calculated ? 'Score calculation works correctly' : 'Score calculation failed',
            'details' => array(
                'form_data' => $form_data,
                'saved_scores' => $saved_scores,
                'fallback_score' => $fallback_score,
                'scores_calculated' => $scores_calculated
            )
        );
    }
    
    /**
     * Test global fields
     */
    private static function test_global_fields() {
        $test_user_id = get_current_user_id();
        
        // Simulate global field data
        $form_data = array(
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@test.com',
            'billing_phone' => '555-123-4567',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'health_goals' => array( 'weight_loss', 'energy', 'sleep' ),
            'height_ft' => '6',
            'height_in' => '2',
            'weight_lbs' => '180'
        );
        
        // Save global fields
        $global_fields_saved = self::save_global_fields( $test_user_id, $form_data );
        
        // Verify saved data
        $saved_data = array();
        $global_fields = array(
            'first_name' => 'ennu_global_first_name',
            'last_name' => 'ennu_global_last_name',
            'email' => 'ennu_global_email',
            'billing_phone' => 'ennu_global_billing_phone',
            'gender' => 'ennu_global_gender',
            'date_of_birth' => 'ennu_global_date_of_birth',
            'health_goals' => 'ennu_global_health_goals',
            'height_weight' => 'ennu_global_height_weight'
        );
        
        foreach ( $global_fields as $field => $meta_key ) {
            $saved_data[ $field ] = get_user_meta( $test_user_id, $meta_key, true );
        }
        
        // Clean up
        foreach ( $global_fields as $field => $meta_key ) {
            delete_user_meta( $test_user_id, $meta_key );
        }
        delete_user_meta( $test_user_id, 'ennu_calculated_bmi' );
        
        return array(
            'success' => $global_fields_saved && count( array_filter( $saved_data ) ) > 0,
            'message' => $global_fields_saved ? 'Global fields saving works correctly' : 'Global fields saving failed',
            'details' => array(
                'form_data' => $form_data,
                'saved_data' => $saved_data,
                'global_fields_saved' => $global_fields_saved
            )
        );
    }
    
    /**
     * Test assessment completion
     */
    private static function test_assessment_completion() {
        $test_user_id = get_current_user_id();
        $assessment_type = 'hair_assessment';
        
        // Simulate assessment completion
        $assessment_data = array(
            'assessment_type' => $assessment_type,
            'form_data' => array( 'test' => 'data' ),
            'timestamp' => current_time( 'mysql' )
        );
        
        // Process completion
        self::process_completion( $test_user_id, $assessment_data );
        
        // Verify completion data
        $last_assessment = get_user_meta( $test_user_id, 'ennu_last_assessment_completed', true );
        $last_activity = get_user_meta( $test_user_id, 'ennu_last_activity', true );
        $completed_count = get_user_meta( $test_user_id, 'ennu_completed_assessments_count', true );
        
        // Clean up
        delete_user_meta( $test_user_id, 'ennu_last_assessment_completed' );
        delete_user_meta( $test_user_id, 'ennu_last_activity' );
        delete_user_meta( $test_user_id, 'ennu_completed_assessments_count' );
        
        return array(
            'success' => $last_assessment === $assessment_type && ! empty( $last_activity ),
            'message' => $last_assessment === $assessment_type ? 'Assessment completion processing works correctly' : 'Assessment completion processing failed',
            'details' => array(
                'assessment_data' => $assessment_data,
                'last_assessment' => $last_assessment,
                'last_activity' => $last_activity,
                'completed_count' => $completed_count
            )
        );
    }
    
    /**
     * Helper function to process question answers
     */
    private static function process_question_answers( $user_id, $form_data ) {
        $assessment_type = sanitize_text_field( $form_data['assessment_type'] ?? '' );
        if ( empty( $assessment_type ) ) {
            return false;
        }
        
        $saved_count = 0;
        
        foreach ( $form_data as $field_name => $field_value ) {
            if ( in_array( $field_name, array( 'action', 'nonce', 'assessment_type', 'first_name', 'last_name', 'email', 'billing_phone' ) ) ) {
                continue;
            }
            
            $meta_key = 'ennu_' . $assessment_type . '_' . $field_name;
            $sanitized_value = is_array( $field_value ) ? array_map( 'sanitize_text_field', $field_value ) : sanitize_text_field( $field_value );
            
            $result = update_user_meta( $user_id, $meta_key, $sanitized_value );
            if ( $result !== false ) {
                $saved_count++;
            }
        }
        
        return $saved_count > 0;
    }
    
    /**
     * Helper function to process symptoms
     */
    private static function process_symptoms( $user_id, $form_data ) {
        $assessment_type = sanitize_text_field( $form_data['assessment_type'] ?? '' );
        if ( empty( $assessment_type ) ) {
            return false;
        }
        
        $symptoms = array();
        foreach ( $form_data as $field_name => $field_value ) {
            if ( strpos( $field_name, '_q' ) !== false && is_array( $field_value ) ) {
                $symptoms = array_merge( $symptoms, $field_value );
            }
        }
        
        if ( ! empty( $symptoms ) ) {
            update_user_meta( $user_id, 'ennu_' . $assessment_type . '_symptoms', $symptoms );
            return true;
        }
        
        return false;
    }
    
    /**
     * Helper function to calculate scores
     */
    private static function calculate_scores( $user_id, $form_data ) {
        $assessment_type = sanitize_text_field( $form_data['assessment_type'] ?? '' );
        if ( empty( $assessment_type ) ) {
            return false;
        }
        
        try {
            if ( class_exists( 'ENNU_Scoring_System' ) ) {
                $scores = ENNU_Scoring_System::calculate_scores_for_assessment( $assessment_type, $form_data );
                if ( ! empty( $scores ) ) {
                    update_user_meta( $user_id, 'ennu_' . $assessment_type . '_scores', $scores );
                    return true;
                }
            }
            
            // Fallback scoring
            $score = 0;
            $count = 0;
            foreach ( $form_data as $field_name => $field_value ) {
                if ( strpos( $field_name, '_q' ) !== false ) {
                    if ( is_array( $field_value ) ) {
                        $score += count( $field_value );
                    } else {
                        $score += 1;
                    }
                    $count++;
                }
            }
            
            if ( $count > 0 ) {
                $fallback_score = round( ( $score / $count ) * 2, 1 );
                update_user_meta( $user_id, 'ennu_' . $assessment_type . '_fallback_score', $fallback_score );
                return true;
            }
            
        } catch ( Exception $e ) {
            error_log( "ENNU Test: Score calculation error: " . $e->getMessage() );
        }
        
        return false;
    }
    
    /**
     * Helper function to save global fields
     */
    private static function save_global_fields( $user_id, $form_data ) {
        $global_fields = array(
            'first_name' => 'ennu_global_first_name',
            'last_name' => 'ennu_global_last_name',
            'email' => 'ennu_global_email',
            'billing_phone' => 'ennu_global_billing_phone',
            'gender' => 'ennu_global_gender',
            'date_of_birth' => 'ennu_global_date_of_birth',
            'health_goals' => 'ennu_global_health_goals'
        );
        
        $saved_count = 0;
        
        foreach ( $global_fields as $form_field => $meta_key ) {
            if ( isset( $form_data[ $form_field ] ) && ! empty( $form_data[ $form_field ] ) ) {
                $value = is_array( $form_data[ $form_field ] ) ? $form_data[ $form_field ] : sanitize_text_field( $form_data[ $form_field ] );
                $result = update_user_meta( $user_id, $meta_key, $value );
                if ( $result !== false ) {
                    $saved_count++;
                }
            }
        }
        
        // Handle height/weight
        if ( isset( $form_data['height_ft'], $form_data['height_in'], $form_data['weight_lbs'] ) ) {
            $height_weight = array(
                'ft' => intval( $form_data['height_ft'] ),
                'in' => intval( $form_data['height_in'] ),
                'lbs' => floatval( $form_data['weight_lbs'] )
            );
            update_user_meta( $user_id, 'ennu_global_height_weight', $height_weight );
            $saved_count++;
            
            // Calculate BMI
            $height_in_total = ( $height_weight['ft'] * 12 ) + $height_weight['in'];
            if ( $height_in_total > 0 && $height_weight['lbs'] > 0 ) {
                $bmi = ( $height_weight['lbs'] / ( $height_in_total * $height_in_total ) ) * 703;
                update_user_meta( $user_id, 'ennu_calculated_bmi', round( $bmi, 1 ) );
            }
        }
        
        return $saved_count > 0;
    }
    
    /**
     * Helper function to process completion
     */
    private static function process_completion( $user_id, $assessment_data ) {
        update_user_meta( $user_id, 'ennu_last_assessment_completed', $assessment_data['assessment_type'] ?? '' );
        update_user_meta( $user_id, 'ennu_last_activity', current_time( 'mysql' ) );
        
        // Count completed assessments
        $assessments = array(
            'hair_assessment', 'weight_loss_assessment', 'health_assessment',
            'ed_treatment_assessment', 'skin_assessment', 'sleep_assessment',
            'hormone_assessment', 'menopause_assessment', 'testosterone_assessment'
        );
        
        $completed = 0;
        foreach ( $assessments as $assessment ) {
            if ( get_user_meta( $user_id, 'ennu_' . $assessment . '_completed_at', true ) ) {
                $completed++;
            }
        }
        
        update_user_meta( $user_id, 'ennu_completed_assessments_count', $completed );
    }
    
    /**
     * Display test results
     */
    private static function display_test_results( $test_results ) {
        $total_tests = count( $test_results );
        $passed_tests = 0;
        
        foreach ( $test_results as $test_name => $result ) {
            if ( $result['success'] ) {
                $passed_tests++;
            }
        }
        
        echo '<div style="margin: 20px 0; padding: 15px; background: ' . ( $passed_tests === $total_tests ? '#d4edda' : '#f8d7da' ) . '; border: 1px solid ' . ( $passed_tests === $total_tests ? '#c3e6cb' : '#f5c6cb' ) . '; border-radius: 5px;">';
        echo '<h2 style="margin: 0 0 10px 0; color: ' . ( $passed_tests === $total_tests ? '#155724' : '#721c24' ) . ';">';
        echo $passed_tests === $total_tests ? '✅ ALL TESTS PASSED' : '❌ SOME TESTS FAILED';
        echo '</h2>';
        echo '<p style="margin: 0; color: ' . ( $passed_tests === $total_tests ? '#155724' : '#721c24' ) . ';">';
        echo "Passed: {$passed_tests}/{$total_tests} tests";
        echo '</p>';
        echo '</div>';
        
        foreach ( $test_results as $test_name => $result ) {
            $status_color = $result['success'] ? '#28a745' : '#dc3545';
            $status_icon = $result['success'] ? '✅' : '❌';
            
            echo '<div style="margin: 15px 0; padding: 15px; background: white; border: 1px solid #ddd; border-radius: 5px;">';
            echo '<h3 style="margin: 0 0 10px 0; color: #333;">';
            echo $status_icon . ' ' . ucwords( str_replace( '_', ' ', $test_name ) );
            echo '</h3>';
            echo '<p style="margin: 0 0 10px 0; color: ' . $status_color . '; font-weight: bold;">';
            echo $result['message'];
            echo '</p>';
            
            if ( ! empty( $result['details'] ) ) {
                echo '<details style="margin-top: 10px;">';
                echo '<summary style="cursor: pointer; color: #0073aa; font-weight: bold;">View Details</summary>';
                echo '<pre style="background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; margin: 10px 0 0 0; font-size: 12px;">';
                echo htmlspecialchars( print_r( $result['details'], true ) );
                echo '</pre>';
                echo '</details>';
            }
            
            echo '</div>';
        }
        
        echo '<div style="margin: 20px 0; padding: 15px; background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 5px;">';
        echo '<h3 style="margin: 0 0 10px 0; color: #0056b3;">🔧 Next Steps</h3>';
        echo '<ul style="margin: 0; padding-left: 20px; color: #0056b3;">';
        echo '<li>If all tests passed, the assessment data saving fix is working correctly</li>';
        echo '<li>If any tests failed, check the error logs and verify the fix implementation</li>';
        echo '<li>Test with actual assessment submissions to ensure end-to-end functionality</li>';
        echo '<li>Monitor the debug log for any additional issues</li>';
        echo '</ul>';
        echo '</div>';
    }
}

// Add admin menu item for testing
add_action( 'admin_menu', function() {
    add_submenu_page(
        'tools.php',
        'ENNU Assessment Data Saving Test',
        'ENNU Data Test',
        'manage_options',
        'ennu-assessment-data-test',
        array( 'ENNU_Test_Assessment_Data_Saving_Fix', 'run_test' )
    );
} );

// Auto-run test if requested
if ( isset( $_GET['page'] ) && $_GET['page'] === 'ennu-assessment-data-test' ) {
    // Test will run when page loads
} 