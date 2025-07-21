<?php
/**
 * Template Security Class
 * 
 * Provides security functions specifically for template rendering
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Template_Security {
    
    private static $instance = null;
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_filter( 'ennu_escape_template_data', array( $this, 'escape_template_data' ), 10, 2 );
    }
    
    public function escape_template_data( $data, $context = 'html' ) {
        if ( is_array( $data ) ) {
            return array_map( function( $item ) use ( $context ) {
                return $this->escape_template_data( $item, $context );
            }, $data );
        }
        
        switch ( $context ) {
            case 'html':
                return esc_html( $data );
            case 'attr':
                return esc_attr( $data );
            case 'url':
                return esc_url( $data );
            case 'js':
                return esc_js( $data );
            case 'textarea':
                return esc_textarea( $data );
            default:
                return esc_html( $data );
        }
    }
    
    public function safe_echo( $data, $context = 'html' ) {
        echo $this->escape_template_data( $data, $context );
    }
    
    public function validate_template_vars( $required_vars, $template_args ) {
        $missing_vars = array();
        
        foreach ( $required_vars as $var ) {
            if ( ! isset( $template_args[ $var ] ) ) {
                $missing_vars[] = $var;
            }
        }
        
        if ( ! empty( $missing_vars ) ) {
            error_log( 'ENNU Template Security: Missing required variables: ' . implode( ', ', $missing_vars ) );
            return false;
        }
        
        return true;
    }
    
    public function sanitize_user_display_data( $user_data ) {
        $data_access_control = ENNU_Data_Access_Control::get_instance();
        return $data_access_control->sanitize_display_data( $user_data, 'dashboard' );
    }
}

ENNU_Template_Security::get_instance();
