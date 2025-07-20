<?php
/**
 * ENNU Assessment AJAX Handler
 * Extracted from monolithic Assessment Shortcodes class
 *
 * @package ENNU_Life
 * @version 62.1.67
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Assessment_AJAX_Handler {
    
    private $security;
    private $performance_monitor;
    private $form_handler;
    
    public function __construct() {
        $this->security = new ENNU_AJAX_Security();
        $this->performance_monitor = ENNU_Performance_Monitor::get_instance();
        
        add_action( 'wp_ajax_ennu_submit_assessment', array( $this, 'handle_assessment_submission' ) );
        add_action( 'wp_ajax_nopriv_ennu_submit_assessment', array( $this, 'handle_assessment_submission' ) );
        add_action( 'wp_ajax_ennu_retake_assessment', array( $this, 'handle_assessment_retake' ) );
        add_action( 'wp_ajax_ennu_get_assessment_progress', array( $this, 'handle_get_progress' ) );
    }
    
    /**
     * Handle assessment form submission with enhanced security
     */
    public function handle_assessment_submission() {
        $this->performance_monitor->start_timer( 'assessment_submission' );
        
        $security_result = $this->security->validate_ajax_request( array(
            'action' => 'ennu_submit_assessment',
            'capability' => 'read',
            'rate_limit' => array(
                'requests' => 10,
                'window' => 300
            )
        ) );
        
        if ( is_wp_error( $security_result ) ) {
            wp_send_json_error( array(
                'message' => $security_result->get_error_message(),
                'code' => $security_result->get_error_code()
            ) );
        }
        
        $form_data = $this->sanitize_form_data( $_POST );
        
        if ( empty( $form_data['assessment_type'] ) ) {
            wp_send_json_error( array(
                'message' => 'Assessment type is required',
                'code' => 'MISSING_ASSESSMENT_TYPE'
            ) );
        }
        
        $user_id = $this->get_or_create_user( $form_data );
        
        if ( is_wp_error( $user_id ) ) {
            wp_send_json_error( array(
                'message' => $user_id->get_error_message(),
                'code' => $user_id->get_error_code()
            ) );
        }
        
        $this->save_assessment_data( $user_id, $form_data );
        
        $scores = $this->calculate_scores( $user_id, $form_data );
        
        ENNU_Assessment_Scoring::calculate_and_save_all_user_scores( $user_id );
        
        do_action( 'ennu_assessment_completed', $user_id, array(
            'assessment_type' => $form_data['assessment_type'],
            'form_data' => $form_data,
            'timestamp' => current_time( 'mysql' )
        ) );
        
        $results_token = $this->store_results_transient( $user_id, $form_data['assessment_type'], $scores, $form_data );
        
        $this->performance_monitor->end_timer( 'assessment_submission' );
        
        wp_send_json_success( array(
            'message' => 'Assessment submitted successfully',
            'redirect_url' => $this->get_results_url( $results_token ),
            'user_id' => $user_id,
            'scores' => $scores
        ) );
    }
    
    /**
     * Handle assessment retake with real-time score updates
     */
    public function handle_assessment_retake() {
        $security_result = $this->security->validate_ajax_request( array(
            'action' => 'ennu_retake_assessment',
            'capability' => 'read'
        ) );
        
        if ( is_wp_error( $security_result ) ) {
            wp_send_json_error( array(
                'message' => $security_result->get_error_message(),
                'code' => $security_result->get_error_code()
            ) );
        }
        
        $user_id = get_current_user_id();
        $assessment_type = sanitize_text_field( $_POST['assessment_type'] ?? '' );
        
        if ( empty( $assessment_type ) ) {
            wp_send_json_error( array(
                'message' => 'Assessment type is required',
                'code' => 'MISSING_ASSESSMENT_TYPE'
            ) );
        }
        
        $this->clear_assessment_data( $user_id, $assessment_type );
        
        wp_send_json_success( array(
            'message' => 'Assessment data cleared for retake',
            'redirect_url' => $this->get_assessment_url( $assessment_type )
        ) );
    }
    
    /**
     * Get assessment progress
     */
    public function handle_get_progress() {
        $security_result = $this->security->validate_ajax_request( array(
            'action' => 'ennu_get_assessment_progress',
            'capability' => 'read'
        ) );
        
        if ( is_wp_error( $security_result ) ) {
            wp_send_json_error( array(
                'message' => $security_result->get_error_message(),
                'code' => $security_result->get_error_code()
            ) );
        }
        
        $user_id = get_current_user_id();
        
        if ( ! $user_id ) {
            wp_send_json_error( array(
                'message' => 'User not logged in',
                'code' => 'NOT_LOGGED_IN'
            ) );
        }
        
        $progress = $this->calculate_user_progress( $user_id );
        
        wp_send_json_success( array(
            'progress' => $progress,
            'completion_rate' => $progress['completion_rate'],
            'next_assessment' => $progress['next_assessment']
        ) );
    }
    
    /**
     * Sanitize form data with enhanced validation
     */
    private function sanitize_form_data( $data ) {
        $sanitized = array();
        
        foreach ( $data as $key => $value ) {
            if ( is_array( $value ) ) {
                $sanitized[ $key ] = array_map( 'sanitize_text_field', $value );
            } else {
                $sanitized[ $key ] = sanitize_text_field( $value );
            }
        }
        
        if ( isset( $sanitized['email'] ) && ! is_email( $sanitized['email'] ) ) {
            return new WP_Error( 'INVALID_EMAIL', 'Invalid email address provided' );
        }
        
        return $sanitized;
    }
    
    /**
     * Get or create user account
     */
    private function get_or_create_user( $form_data ) {
        if ( is_user_logged_in() ) {
            return get_current_user_id();
        }
        
        if ( empty( $form_data['email'] ) ) {
            return new WP_Error( 'MISSING_EMAIL', 'Email is required for new users' );
        }
        
        $existing_user = get_user_by( 'email', $form_data['email'] );
        
        if ( $existing_user ) {
            wp_set_current_user( $existing_user->ID );
            wp_set_auth_cookie( $existing_user->ID );
            return $existing_user->ID;
        }
        
        $username = sanitize_user( $form_data['email'] );
        $password = wp_generate_password();
        
        $user_id = wp_create_user( $username, $password, $form_data['email'] );
        
        if ( is_wp_error( $user_id ) ) {
            return $user_id;
        }
        
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );
        
        return $user_id;
    }
    
    /**
     * Save assessment data
     */
    private function save_assessment_data( $user_id, $form_data ) {
        $assessment_type = $form_data['assessment_type'];
        
        foreach ( $form_data as $key => $value ) {
            if ( strpos( $key, $assessment_type . '_' ) === 0 ) {
                update_user_meta( $user_id, 'ennu_' . $key, $value );
            }
        }
        
        update_user_meta( $user_id, 'ennu_' . $assessment_type . '_completed', current_time( 'mysql' ) );
    }
    
    /**
     * Calculate assessment scores
     */
    private function calculate_scores( $user_id, $form_data ) {
        $assessment_type = $form_data['assessment_type'];
        $calculator = new ENNU_Assessment_Calculator();
        
        return $calculator->calculate_assessment_score( $user_id, $assessment_type, $form_data );
    }
    
    /**
     * Store results in transient
     */
    private function store_results_transient( $user_id, $assessment_type, $scores, $form_data ) {
        $token = wp_generate_password( 32, false );
        
        $transient_data = array(
            'user_id' => $user_id,
            'assessment_type' => $assessment_type,
            'scores' => $scores,
            'form_data' => $form_data,
            'timestamp' => current_time( 'mysql' )
        );
        
        set_transient( 'ennu_results_' . $token, $transient_data, 3600 );
        
        return $token;
    }
    
    /**
     * Get results URL
     */
    private function get_results_url( $token ) {
        return add_query_arg( array(
            'ennu_results' => $token,
            'view' => 'results'
        ), home_url() );
    }
    
    /**
     * Get assessment URL
     */
    private function get_assessment_url( $assessment_type ) {
        return add_query_arg( array(
            'assessment' => $assessment_type
        ), home_url() );
    }
    
    /**
     * Clear assessment data for retake
     */
    private function clear_assessment_data( $user_id, $assessment_type ) {
        global $wpdb;
        
        $wpdb->delete(
            $wpdb->usermeta,
            array(
                'user_id' => $user_id,
                'meta_key' => 'ennu_' . $assessment_type . '_calculated_score'
            )
        );
        
        $meta_keys = $wpdb->get_col( $wpdb->prepare(
            "SELECT meta_key FROM {$wpdb->usermeta} 
             WHERE user_id = %d AND meta_key LIKE %s",
            $user_id,
            'ennu_' . $assessment_type . '_%'
        ) );
        
        foreach ( $meta_keys as $meta_key ) {
            delete_user_meta( $user_id, $meta_key );
        }
    }
    
    /**
     * Calculate user progress
     */
    private function calculate_user_progress( $user_id ) {
        $assessments = array( 'welcome', 'hair', 'health', 'skin', 'sleep', 'hormone', 'menopause', 'testosterone', 'weight_loss', 'health_optimization' );
        $completed = 0;
        $next_assessment = null;
        
        foreach ( $assessments as $assessment ) {
            $score = get_user_meta( $user_id, 'ennu_' . $assessment . '_calculated_score', true );
            
            if ( ! empty( $score ) ) {
                $completed++;
            } elseif ( ! $next_assessment ) {
                $next_assessment = $assessment;
            }
        }
        
        return array(
            'total_assessments' => count( $assessments ),
            'completed_assessments' => $completed,
            'completion_rate' => round( ( $completed / count( $assessments ) ) * 100, 1 ),
            'next_assessment' => $next_assessment
        );
    }
}

new ENNU_Assessment_AJAX_Handler();
