<?php
/**
 * CSRF Protection Class
 * 
 * Provides comprehensive CSRF protection for all forms and AJAX requests
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_CSRF_Protection {
    
    private static $instance = null;
    private $nonce_actions = array();
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->init_nonce_actions();
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_nonce_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_nonce_scripts' ) );
    }
    
    private function init_nonce_actions() {
        $this->nonce_actions = array(
            'ennu_ajax_nonce' => 'ENNU AJAX Security',
            'ennu_admin_nonce' => 'ENNU Admin Security',
            'ennu_health_goals_nonce' => 'ENNU Health Goals Security',
            'ennu_assessment_nonce' => 'ENNU Assessment Security',
            'ennu_form_nonce' => 'ENNU Form Security',
        );
    }
    
    public function enqueue_nonce_scripts() {
        wp_localize_script( 'jquery', 'ennu_security', array(
            'ajax_nonce' => wp_create_nonce( 'ennu_ajax_nonce' ),
            'admin_nonce' => wp_create_nonce( 'ennu_admin_nonce' ),
            'health_goals_nonce' => wp_create_nonce( 'ennu_health_goals_nonce' ),
            'assessment_nonce' => wp_create_nonce( 'ennu_assessment_nonce' ),
            'form_nonce' => wp_create_nonce( 'ennu_form_nonce' ),
        ) );
    }
    
    public function verify_nonce( $nonce, $action ) {
        if ( ! isset( $this->nonce_actions[ $action ] ) ) {
            error_log( 'ENNU CSRF: Unknown nonce action: ' . $action );
            return false;
        }
        
        $result = wp_verify_nonce( $nonce, $action );
        
        if ( ! $result ) {
            error_log( 'ENNU CSRF: Nonce verification failed for action: ' . $action );
            $this->log_csrf_failure( $action );
        }
        
        return $result;
    }
    
    public function verify_ajax_nonce( $action = 'ennu_ajax_nonce' ) {
        $nonce = $_POST['nonce'] ?? $_GET['nonce'] ?? '';
        
        if ( empty( $nonce ) ) {
            wp_send_json_error( array( 'message' => 'Security token is missing.' ), 403 );
            wp_die();
        }
        
        if ( ! $this->verify_nonce( $nonce, $action ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed.' ), 403 );
            wp_die();
        }
        
        return true;
    }
    
    public function create_nonce_field( $action = 'ennu_form_nonce', $name = 'nonce' ) {
        return wp_nonce_field( $action, $name, true, false );
    }
    
    public function create_nonce_url( $url, $action = 'ennu_form_nonce' ) {
        return wp_nonce_url( $url, $action );
    }
    
    public function get_nonce( $action = 'ennu_ajax_nonce' ) {
        return wp_create_nonce( $action );
    }
    
    private function log_csrf_failure( $action ) {
        $log_entry = array(
            'timestamp' => current_time( 'mysql' ),
            'action' => $action,
            'user_id' => get_current_user_id(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'referer' => $_SERVER['HTTP_REFERER'] ?? 'unknown',
        );
        
        error_log( 'ENNU CSRF Failure: ' . wp_json_encode( $log_entry ) );
        
        do_action( 'ennu_csrf_failure', $log_entry );
    }
    
    public function validate_form_submission( $action = 'ennu_form_nonce' ) {
        if ( ! isset( $_POST['nonce'] ) ) {
            return new WP_Error( 'missing_nonce', 'Security token is missing.' );
        }
        
        if ( ! $this->verify_nonce( $_POST['nonce'], $action ) ) {
            return new WP_Error( 'invalid_nonce', 'Security check failed.' );
        }
        
        return true;
    }
    
    public function add_nonce_to_form_data( $form_data, $action = 'ennu_form_nonce' ) {
        $form_data['nonce'] = $this->get_nonce( $action );
        return $form_data;
    }
    
    public function is_valid_referer( $allowed_origins = array() ) {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        
        if ( empty( $referer ) ) {
            return false;
        }
        
        $site_url = get_site_url();
        if ( strpos( $referer, $site_url ) === 0 ) {
            return true;
        }
        
        foreach ( $allowed_origins as $origin ) {
            if ( strpos( $referer, $origin ) === 0 ) {
                return true;
            }
        }
        
        return false;
    }
}

ENNU_CSRF_Protection::get_instance();
