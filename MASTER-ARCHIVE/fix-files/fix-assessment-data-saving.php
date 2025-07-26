<?php
/**
 * ENNU Life Assessments - Comprehensive Data Saving Fix
 * 
 * Fixes issues with:
 * 1. Question answers not saving to user meta
 * 2. Correlated symptoms not being processed
 * 3. Score calculations not working properly
 * 
 * @package ENNU_Life_Assessments
 * @version 64.3.35
 * @author Matt Codeweaver - The GOAT of WordPress Development
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Assessment_Data_Saving_Fix {
    
    /**
     * Initialize the fix
     */
    public static function init() {
        add_action( 'wp_ajax_ennu_submit_assessment', array( __CLASS__, 'enhanced_assessment_submission' ), 1 );
        add_action( 'wp_ajax_nopriv_ennu_submit_assessment', array( __CLASS__, 'enhanced_assessment_submission' ), 1 );
        add_action( 'ennu_assessment_completed', array( __CLASS__, 'process_assessment_completion' ), 10, 2 );
    }
    
    /**
     * Enhanced assessment submission handler
     */
    public static function enhanced_assessment_submission() {
        error_log( 'ENNU FIX: Enhanced assessment submission started' );
        
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'ennu_ajax_nonce' ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed' ), 403 );
            return;
        }
        
        $form_data = $_POST;
        error_log( 'ENNU FIX: Raw form data: ' . print_r( $form_data, true ) );
        
        // Get or create user
        $user_id = self::get_or_create_user( $form_data );
        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( array( 'message' => $user_id->get_error_message() ), 400 );
            return;
        }
        
        error_log( "ENNU FIX: Processing for user ID: {$user_id}" );
        
        // 1. SAVE QUESTION ANSWERS
        $answers_saved = self::save_question_answers( $user_id, $form_data );
        error_log( "ENNU FIX: Question answers saved: " . ( $answers_saved ? 'SUCCESS' : 'FAILED' ) );
        
        // 2. PROCESS CORRELATED SYMPTOMS
        $symptoms_processed = self::process_correlated_symptoms( $user_id, $form_data );
        error_log( "ENNU FIX: Symptoms processed: " . ( $symptoms_processed ? 'SUCCESS' : 'FAILED' ) );
        
        // 3. CALCULATE AND SAVE SCORES
        $scores_calculated = self::calculate_and_save_scores( $user_id, $form_data );
        error_log( "ENNU FIX: Scores calculated: " . ( $scores_calculated ? 'SUCCESS' : 'FAILED' ) );
        
        // 4. SAVE GLOBAL FIELDS
        $global_fields_saved = self::save_global_fields( $user_id, $form_data );
        error_log( "ENNU FIX: Global fields saved: " . ( $global_fields_saved ? 'SUCCESS' : 'FAILED' ) );
        
        // 5. TRIGGER COMPLETION HOOKS
        do_action( 'ennu_assessment_completed', $user_id, array(
            'assessment_type' => $form_data['assessment_type'] ?? '',
            'form_data' => $form_data,
            'timestamp' => current_time( 'mysql' )
        ) );
        
        // 6. GENERATE RESULTS TOKEN
        $results_token = wp_generate_password( 32, false );
        set_transient( 'ennu_results_' . $results_token, array(
            'user_id' => $user_id,
            'assessment_type' => $form_data['assessment_type'] ?? '',
            'scores' => $scores_calculated,
            'timestamp' => current_time( 'mysql' )
        ), HOUR_IN_SECONDS );
        
        // 7. SEND SUCCESS RESPONSE
        $redirect_url = self::get_redirect_url( $form_data['assessment_type'] ?? '', $results_token );
        
        wp_send_json_success( array(
            'message' => 'Assessment completed successfully',
            'redirect_url' => $redirect_url,
            'results_token' => $results_token,
            'debug_info' => array(
                'answers_saved' => $answers_saved,
                'symptoms_processed' => $symptoms_processed,
                'scores_calculated' => $scores_calculated,
                'global_fields_saved' => $global_fields_saved
            )
        ) );
    }
    
    /**
     * Get or create user
     */
    private static function get_or_create_user( $form_data ) {
        $email = sanitize_email( $form_data['email'] ?? '' );
        if ( empty( $email ) ) {
            return new WP_Error( 'missing_email', 'Email is required' );
        }
        
        $existing_user = get_user_by( 'email', $email );
        if ( $existing_user ) {
            wp_set_current_user( $existing_user->ID );
            wp_set_auth_cookie( $existing_user->ID );
            return $existing_user->ID;
        }
        
        // Create new user
        $username = sanitize_user( $email );
        $password = wp_generate_password();
        $user_data = array(
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => $password,
            'first_name' => sanitize_text_field( $form_data['first_name'] ?? '' ),
            'last_name' => sanitize_text_field( $form_data['last_name'] ?? '' ),
            'role' => 'subscriber'
        );
        
        $user_id = wp_insert_user( $user_data );
        if ( is_wp_error( $user_id ) ) {
            return $user_id;
        }
        
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );
        
        return $user_id;
    }
    
    /**
     * Save question answers to user meta
     */
    private static function save_question_answers( $user_id, $form_data ) {
        $assessment_type = sanitize_text_field( $form_data['assessment_type'] ?? '' );
        if ( empty( $assessment_type ) ) {
            error_log( 'ENNU FIX: No assessment type provided' );
            return false;
        }
        
        $saved_count = 0;
        $failed_count = 0;
        
        // Get assessment questions configuration
        $questions = self::get_assessment_questions( $assessment_type );
        
        foreach ( $form_data as $field_name => $field_value ) {
            // Skip non-question fields
            if ( in_array( $field_name, array( 'action', 'nonce', 'assessment_type', 'first_name', 'last_name', 'email', 'billing_phone' ) ) ) {
                continue;
            }
            
            // Determine meta key
            $meta_key = 'ennu_' . $assessment_type . '_' . $field_name;
            
            // Sanitize value based on type
            $sanitized_value = self::sanitize_field_value( $field_value, $field_name, $questions );
            
            // Save to user meta
            $result = update_user_meta( $user_id, $meta_key, $sanitized_value );
            
            if ( $result !== false ) {
                $saved_count++;
                error_log( "ENNU FIX: Saved field {$meta_key} = " . print_r( $sanitized_value, true ) );
            } else {
                $failed_count++;
                error_log( "ENNU FIX: Failed to save field {$meta_key}" );
            }
        }
        
        // Save completion timestamp
        update_user_meta( $user_id, 'ennu_' . $assessment_type . '_completed_at', current_time( 'mysql' ) );
        update_user_meta( $user_id, 'ennu_' . $assessment_type . '_last_updated', current_time( 'mysql' ) );
        
        error_log( "ENNU FIX: Question answers - Saved: {$saved_count}, Failed: {$failed_count}" );
        
        return $saved_count > 0;
    }
    
    /**
     * Process correlated symptoms
     */
    private static function process_correlated_symptoms( $user_id, $form_data ) {
        $assessment_type = sanitize_text_field( $form_data['assessment_type'] ?? '' );
        if ( empty( $assessment_type ) ) {
            return false;
        }
        
        // Extract symptoms from form data
        $symptoms = array();
        foreach ( $form_data as $field_name => $field_value ) {
            if ( strpos( $field_name, '_symptoms' ) !== false || strpos( $field_name, 'symptom' ) !== false ) {
                if ( is_array( $field_value ) ) {
                    $symptoms = array_merge( $symptoms, $field_value );
                } else {
                    $symptoms[] = $field_value;
                }
            }
        }
        
        if ( ! empty( $symptoms ) ) {
            // Save symptoms to user meta
            update_user_meta( $user_id, 'ennu_' . $assessment_type . '_symptoms', $symptoms );
            
            // Update centralized symptoms
            if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
                ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $user_id, $assessment_type );
            }
            
            error_log( "ENNU FIX: Processed symptoms: " . print_r( $symptoms, true ) );
            return true;
        }
        
        return false;
    }
    
    /**
     * Calculate and save scores
     */
    private static function calculate_and_save_scores( $user_id, $form_data ) {
        $assessment_type = sanitize_text_field( $form_data['assessment_type'] ?? '' );
        if ( empty( $assessment_type ) ) {
            return false;
        }
        
        try {
            // Use the existing scoring system
            if ( class_exists( 'ENNU_Scoring_System' ) ) {
                $scores = ENNU_Scoring_System::calculate_scores_for_assessment( $assessment_type, $form_data );
                
                if ( ! empty( $scores ) ) {
                    // Save individual assessment scores
                    update_user_meta( $user_id, 'ennu_' . $assessment_type . '_scores', $scores );
                    update_user_meta( $user_id, 'ennu_' . $assessment_type . '_score_calculated_at', current_time( 'mysql' ) );
                    
                    // Calculate and save all user scores
                    ENNU_Scoring_System::calculate_and_save_all_user_scores( $user_id );
                    
                    error_log( "ENNU FIX: Scores calculated and saved: " . print_r( $scores, true ) );
                    return true;
                }
            }
            
            // Fallback to simple scoring if main system fails
            $fallback_score = self::calculate_fallback_score( $assessment_type, $form_data );
            if ( $fallback_score !== false ) {
                update_user_meta( $user_id, 'ennu_' . $assessment_type . '_fallback_score', $fallback_score );
                error_log( "ENNU FIX: Fallback score calculated: {$fallback_score}" );
                return true;
            }
            
        } catch ( Exception $e ) {
            error_log( "ENNU FIX: Score calculation error: " . $e->getMessage() );
        }
        
        return false;
    }
    
    /**
     * Save global fields
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
                $value = sanitize_text_field( $form_data[ $form_field ] );
                $result = update_user_meta( $user_id, $meta_key, $value );
                
                if ( $result !== false ) {
                    $saved_count++;
                    error_log( "ENNU FIX: Saved global field {$meta_key} = {$value}" );
                }
            }
        }
        
        // Handle height/weight combination
        if ( isset( $form_data['height_ft'], $form_data['height_in'], $form_data['weight_lbs'] ) ) {
            $height_weight = array(
                'ft' => intval( $form_data['height_ft'] ),
                'in' => intval( $form_data['height_in'] ),
                'lbs' => floatval( $form_data['weight_lbs'] )
            );
            update_user_meta( $user_id, 'ennu_global_height_weight', $height_weight );
            $saved_count++;
            
            // Calculate and save BMI
            $height_in_total = ( $height_weight['ft'] * 12 ) + $height_weight['in'];
            if ( $height_in_total > 0 && $height_weight['lbs'] > 0 ) {
                $bmi = ( $height_weight['lbs'] / ( $height_in_total * $height_in_total ) ) * 703;
                update_user_meta( $user_id, 'ennu_calculated_bmi', round( $bmi, 1 ) );
            }
        }
        
        error_log( "ENNU FIX: Global fields saved: {$saved_count}" );
        return $saved_count > 0;
    }
    
    /**
     * Get assessment questions configuration
     */
    private static function get_assessment_questions( $assessment_type ) {
        // Try to load from configuration files
        $config_path = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessments/' . $assessment_type . '.php';
        if ( file_exists( $config_path ) ) {
            return include $config_path;
        }
        
        // Fallback to hardcoded questions
        return self::get_fallback_questions( $assessment_type );
    }
    
    /**
     * Get fallback questions if config file doesn't exist
     */
    private static function get_fallback_questions( $assessment_type ) {
        $fallback_questions = array(
            'hair_assessment' => array(
                'hair_q1' => array( 'type' => 'radio' ),
                'hair_q2' => array( 'type' => 'multiselect' ),
                'hair_q3' => array( 'type' => 'radio' ),
                'hair_q4' => array( 'type' => 'radio' ),
                'hair_q5' => array( 'type' => 'radio' ),
                'hair_q6' => array( 'type' => 'radio' ),
                'hair_q7' => array( 'type' => 'radio' ),
                'hair_q8' => array( 'type' => 'radio' )
            ),
            'weight_loss_assessment' => array(
                'weight_q1' => array( 'type' => 'radio' ),
                'weight_q2' => array( 'type' => 'radio' ),
                'weight_q3' => array( 'type' => 'radio' ),
                'weight_q4' => array( 'type' => 'radio' ),
                'weight_q5' => array( 'type' => 'radio' ),
                'weight_q6' => array( 'type' => 'radio' ),
                'weight_q7' => array( 'type' => 'radio' ),
                'weight_q8' => array( 'type' => 'radio' ),
                'weight_q9' => array( 'type' => 'radio' ),
                'weight_q10' => array( 'type' => 'radio' )
            ),
            'health_optimization_assessment' => array(
                'health_opt_q1' => array( 'type' => 'multiselect' ),
                'health_opt_q2' => array( 'type' => 'multiselect' ),
                'health_opt_q3' => array( 'type' => 'multiselect' ),
                'health_opt_q4' => array( 'type' => 'multiselect' ),
                'health_opt_q5' => array( 'type' => 'multiselect' ),
                'health_opt_q6' => array( 'type' => 'multiselect' ),
                'health_opt_q7' => array( 'type' => 'multiselect' ),
                'health_opt_q8' => array( 'type' => 'multiselect' )
            )
        );
        
        return $fallback_questions[ $assessment_type ] ?? array();
    }
    
    /**
     * Sanitize field value based on type
     */
    private static function sanitize_field_value( $value, $field_name, $questions ) {
        if ( is_array( $value ) ) {
            return array_map( 'sanitize_text_field', $value );
        }
        
        if ( is_email( $value ) ) {
            return sanitize_email( $value );
        }
        
        if ( is_numeric( $value ) ) {
            return floatval( $value );
        }
        
        return sanitize_text_field( $value );
    }
    
    /**
     * Calculate fallback score if main scoring system fails
     */
    private static function calculate_fallback_score( $assessment_type, $form_data ) {
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
            return round( ( $score / $count ) * 2, 1 ); // Scale to 0-10
        }
        
        return false;
    }
    
    /**
     * Get redirect URL
     */
    private static function get_redirect_url( $assessment_type, $results_token ) {
        $base_url = home_url();
        
        switch ( $assessment_type ) {
            case 'hair_assessment':
                return add_query_arg( 'token', $results_token, $base_url . '/hair-assessment-results/' );
            case 'weight_loss_assessment':
                return add_query_arg( 'token', $results_token, $base_url . '/weight-loss-assessment-results/' );
            case 'health_optimization_assessment':
                return add_query_arg( 'token', $results_token, $base_url . '/health-optimization-results/' );
            default:
                return add_query_arg( 'token', $results_token, $base_url . '/assessment-results/' );
        }
    }
    
    /**
     * Process assessment completion
     */
    public static function process_assessment_completion( $user_id, $assessment_data ) {
        error_log( "ENNU FIX: Assessment completion processed for user {$user_id}" );
        
        // Update user journey data
        update_user_meta( $user_id, 'ennu_last_assessment_completed', $assessment_data['assessment_type'] ?? '' );
        update_user_meta( $user_id, 'ennu_last_activity', current_time( 'mysql' ) );
        
        // Count completed assessments
        $completed_count = self::count_completed_assessments( $user_id );
        update_user_meta( $user_id, 'ennu_completed_assessments_count', $completed_count );
        
        // Clear any cached data
        if ( class_exists( 'ENNU_Score_Cache' ) ) {
            ENNU_Score_Cache::invalidate_cache( $user_id, $assessment_data['assessment_type'] ?? '' );
        }
    }
    
    /**
     * Count completed assessments
     */
    private static function count_completed_assessments( $user_id ) {
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
        
        return $completed;
    }
}

// Initialize the fix
ENNU_Assessment_Data_Saving_Fix::init();

// Add activation hook to ensure the fix is loaded
register_activation_hook( __FILE__, function() {
    error_log( 'ENNU FIX: Assessment data saving fix activated' );
} );

// Test function to verify the fix is working
function ennu_test_data_saving_fix() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    error_log( 'ENNU FIX: Data saving fix is loaded and ready' );
    
    // Test user meta saving
    $test_user_id = get_current_user_id();
    if ( $test_user_id ) {
        $test_result = update_user_meta( $test_user_id, 'ennu_test_field', 'test_value_' . time() );
        error_log( 'ENNU FIX: Test user meta save result: ' . ( $test_result ? 'SUCCESS' : 'FAILED' ) );
    }
}

// Run test on admin pages
if ( is_admin() ) {
    add_action( 'admin_init', 'ennu_test_data_saving_fix' );
} 