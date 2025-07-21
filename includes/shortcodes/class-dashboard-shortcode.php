<?php
/**
 * Dashboard Shortcode Class
 * 
 * Handles the user dashboard shortcode functionality
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once ENNU_LIFE_PLUGIN_PATH . 'includes/shortcodes/class-base-shortcode.php';

class ENNU_Dashboard_Shortcode extends ENNU_Base_Shortcode {
    
    protected $shortcode_name = 'ennu_user_dashboard';
    
    protected $default_attributes = array(
        'show_scores' => 'true',
        'show_assessments' => 'true',
        'show_recommendations' => 'true',
        'theme' => 'default'
    );
    
    public function render( $atts, $content = null ) {
        if ( ! $this->validate_user_permissions() ) {
            return $this->render_error( 'You must be logged in to view your dashboard.' );
        }
        
        $atts = $this->parse_attributes( $atts );
        $user_id = get_current_user_id();
        
        $this->enqueue_assets();
        
        $dashboard_data = $this->get_dashboard_data( $user_id );
        
        return $this->get_template( 'dashboard', array(
            'user_id' => $user_id,
            'dashboard_data' => $dashboard_data,
            'attributes' => $atts
        ) );
    }
    
    protected function enqueue_assets() {
        wp_enqueue_script( 
            'ennu-dashboard-modern', 
            ENNU_LIFE_PLUGIN_URL . 'assets/js/user-dashboard-modern.js', 
            array(), 
            ENNU_LIFE_PLUGIN_VERSION, 
            true 
        );
        
        wp_enqueue_style( 
            'ennu-dashboard', 
            ENNU_LIFE_PLUGIN_URL . 'assets/css/user-dashboard.css', 
            array(), 
            ENNU_LIFE_PLUGIN_VERSION 
        );
        
        wp_localize_script( 'ennu-dashboard-modern', 'ennuAjax', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'ennu_ajax_nonce' )
        ) );
    }
    
    private function get_dashboard_data( $user_id ) {
        $data = array();
        
        $data['scores'] = array(
            'ennu_life_score' => get_user_meta( $user_id, 'ennu_life_score', true ),
            'pillar_scores' => get_user_meta( $user_id, 'ennu_average_pillar_scores', true ),
            'completeness' => get_user_meta( $user_id, 'ennu_score_completeness', true )
        );
        
        $data['assessments'] = $this->get_user_assessments( $user_id );
        $data['recommendations'] = $this->get_user_recommendations( $user_id );
        $data['goals'] = get_user_meta( $user_id, 'ennu_global_health_goals', true );
        
        return $data;
    }
    
    private function get_user_assessments( $user_id ) {
        $assessments = array();
        $all_definitions = ENNU_Assessment_Scoring::get_all_definitions();
        
        foreach ( $all_definitions as $assessment_type => $definition ) {
            $completion_status = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_completed', true );
            $last_updated = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_last_updated', true );
            
            $assessments[ $assessment_type ] = array(
                'name' => $definition['name'] ?? ucwords( str_replace( '_', ' ', $assessment_type ) ),
                'completed' => ! empty( $completion_status ),
                'last_updated' => $last_updated,
                'score' => get_user_meta( $user_id, 'ennu_' . $assessment_type . '_overall_score', true )
            );
        }
        
        return $assessments;
    }
    
    private function get_user_recommendations( $user_id ) {
        return get_user_meta( $user_id, 'ennu_personalized_recommendations', true ) ?: array();
    }
}

new ENNU_Dashboard_Shortcode();
