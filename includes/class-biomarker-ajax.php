<?php
/**
 * ENNU Life Biomarker AJAX Handlers
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Biomarker_Ajax {
    
    public function __construct() {
        add_action( 'wp_ajax_ennu_get_biomarker_data', array( $this, 'get_biomarker_data' ) );
        add_action( 'wp_ajax_ennu_get_score_projection', array( $this, 'get_score_projection' ) );
        add_action( 'wp_ajax_ennu_get_biomarker_recommendations', array( $this, 'get_biomarker_recommendations' ) );
    }
    
    public function get_biomarker_data() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_ajax_nonce' ) ) {
            wp_die( 'Security check failed' );
        }
        
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( 'User not logged in' );
        }
        
        $biomarker_data = ENNU_Biomarker_Manager::get_user_biomarkers( $user_id );
        $doctor_recommendations = ENNU_Biomarker_Manager::get_doctor_recommendations( $user_id );
        
        wp_send_json_success( array(
            'biomarkers' => $biomarker_data,
            'recommendations' => $doctor_recommendations
        ) );
    }
    
    public function get_score_projection() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_ajax_nonce' ) ) {
            wp_die( 'Security check failed' );
        }
        
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( 'User not logged in' );
        }
        
        $projection = ENNU_Biomarker_Manager::calculate_new_life_score_projection( $user_id );
        
        if ( $projection ) {
            wp_send_json_success( $projection );
        } else {
            wp_send_json_error( 'Unable to calculate projection' );
        }
    }
    
    public function get_biomarker_recommendations() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_ajax_nonce' ) ) {
            wp_die( 'Security check failed' );
        }
        
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            wp_send_json_error( 'User not logged in' );
        }
        
        $recommendations = ENNU_Biomarker_Manager::get_biomarker_recommendations( $user_id );
        
        wp_send_json_success( array(
            'recommended_biomarkers' => $recommendations
        ) );
    }
}

new ENNU_Biomarker_Ajax();
