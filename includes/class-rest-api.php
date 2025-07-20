<?php
/**
 * ENNU REST API
 * Modern API endpoints for external integrations
 *
 * @package ENNU_Life
 * @version 62.1.67
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_REST_API {
    
    private $namespace = 'ennu/v1';
    private $security;
    private $user_manager;
    private $analytics_service;
    
    public function __construct() {
        $this->security = new ENNU_AJAX_Security();
        $this->user_manager = new ENNU_User_Manager();
        $this->analytics_service = new ENNU_Analytics_Service();
        
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }
    
    /**
     * Register REST API routes
     */
    public function register_routes() {
        
        register_rest_route( $this->namespace, '/assessments', array(
            'methods' => 'GET',
            'callback' => array( $this, 'get_assessments' ),
            'permission_callback' => array( $this, 'check_read_permission' )
        ) );
        
        register_rest_route( $this->namespace, '/users/(?P<id>\d+)/scores', array(
            'methods' => 'GET',
            'callback' => array( $this, 'get_user_scores' ),
            'permission_callback' => array( $this, 'check_user_permission' ),
            'args' => array(
                'id' => array(
                    'validate_callback' => function( $param ) {
                        return is_numeric( $param );
                    }
                )
            )
        ) );
        
        register_rest_route( $this->namespace, '/analytics/stats', array(
            'methods' => 'GET',
            'callback' => array( $this, 'get_analytics_stats' ),
            'permission_callback' => array( $this, 'check_manage_permission' )
        ) );
    }
    
    /**
     * Get all available assessments
     */
    public function get_assessments( $request ) {
        $assessments = array(
            'welcome' => array(
                'name' => 'Welcome Assessment',
                'description' => 'Initial health assessment'
            ),
            'hair' => array(
                'name' => 'Hair Assessment',
                'description' => 'Hair health and scalp analysis'
            ),
            'health' => array(
                'name' => 'Health Assessment',
                'description' => 'Comprehensive health evaluation'
            )
        );
        
        return rest_ensure_response( $assessments );
    }
    
    /**
     * Get user scores
     */
    public function get_user_scores( $request ) {
        $user_id = (int) $request['id'];
        
        $scores = array(
            'life_score' => get_user_meta( $user_id, 'ennu_life_score', true ),
            'pillar_scores' => array(
                'mind' => get_user_meta( $user_id, 'ennu_mind_pillar_score', true ),
                'body' => get_user_meta( $user_id, 'ennu_body_pillar_score', true ),
                'lifestyle' => get_user_meta( $user_id, 'ennu_lifestyle_pillar_score', true ),
                'aesthetics' => get_user_meta( $user_id, 'ennu_aesthetics_pillar_score', true )
            )
        );
        
        return rest_ensure_response( $scores );
    }
    
    /**
     * Get analytics stats
     */
    public function get_analytics_stats( $request ) {
        $stats = $this->analytics_service->get_system_stats();
        return rest_ensure_response( $stats );
    }
    
    /**
     * Check read permission
     */
    public function check_read_permission( $request ) {
        return current_user_can( 'read' );
    }
    
    /**
     * Check user permission
     */
    public function check_user_permission( $request ) {
        $user_id = (int) $request['id'];
        return current_user_can( 'edit_user', $user_id ) || get_current_user_id() === $user_id;
    }
    
    /**
     * Check manage permission
     */
    public function check_manage_permission( $request ) {
        return current_user_can( 'manage_options' );
    }
}

new ENNU_REST_API();
