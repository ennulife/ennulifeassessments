<?php
/**
 * Base Shortcode Class
 * 
 * Provides common functionality for all ENNU assessment shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

abstract class ENNU_Base_Shortcode {
    
    protected $shortcode_name;
    protected $default_attributes = array();
    protected $template_cache = array();
    
    public function __construct() {
        $this->init();
    }
    
    protected function init() {
        if ( $this->shortcode_name ) {
            add_shortcode( $this->shortcode_name, array( $this, 'render' ) );
        }
    }
    
    abstract public function render( $atts, $content = null );
    
    protected function parse_attributes( $atts ) {
        return shortcode_atts( $this->default_attributes, $atts, $this->shortcode_name );
    }
    
    protected function get_template( $template_name, $variables = array() ) {
        $cache_key = $template_name . '_' . md5( serialize( $variables ) );
        
        if ( isset( $this->template_cache[ $cache_key ] ) ) {
            return $this->template_cache[ $cache_key ];
        }
        
        $template_path = ENNU_LIFE_PLUGIN_PATH . 'templates/shortcodes/' . $template_name . '.php';
        
        if ( ! file_exists( $template_path ) ) {
            return '<p>Template not found: ' . esc_html( $template_name ) . '</p>';
        }
        
        extract( $variables );
        
        ob_start();
        include $template_path;
        $output = ob_get_clean();
        
        $this->template_cache[ $cache_key ] = $output;
        
        return $output;
    }
    
    protected function enqueue_assets() {
    }
    
    protected function validate_user_permissions() {
        return is_user_logged_in();
    }
    
    protected function get_user_data( $user_id = null ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }
        
        if ( ! $user_id ) {
            return false;
        }
        
        return get_userdata( $user_id );
    }
    
    protected function sanitize_input( $input, $type = 'text' ) {
        switch ( $type ) {
            case 'email':
                return sanitize_email( $input );
            case 'url':
                return esc_url_raw( $input );
            case 'int':
                return intval( $input );
            case 'float':
                return floatval( $input );
            case 'html':
                return wp_kses_post( $input );
            default:
                return sanitize_text_field( $input );
        }
    }
    
    protected function log_error( $message, $context = array() ) {
        error_log( 'ENNU Shortcode Error [' . $this->shortcode_name . ']: ' . $message . ' Context: ' . print_r( $context, true ) );
    }
    
    protected function render_error( $message ) {
        return '<div class="ennu-shortcode-error" style="color: red; padding: 10px; border: 1px solid red; margin: 10px 0;">' . 
               esc_html( $message ) . 
               '</div>';
    }
}
