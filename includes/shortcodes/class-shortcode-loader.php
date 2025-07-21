<?php
/**
 * Shortcode Loader Class
 * 
 * Manages loading and initialization of all ENNU shortcode classes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Shortcode_Loader {
    
    private static $instance = null;
    private $loaded_shortcodes = array();
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action( 'init', array( $this, 'load_shortcodes' ), 5 );
    }
    
    public function load_shortcodes() {
        $shortcode_files = array(
            'class-base-shortcode.php',
            'class-dashboard-shortcode.php',
            'class-assessment-form-shortcode.php',
            'class-scores-display-shortcode.php'
        );
        
        foreach ( $shortcode_files as $file ) {
            $file_path = ENNU_LIFE_PLUGIN_PATH . 'includes/shortcodes/' . $file;
            if ( file_exists( $file_path ) ) {
                require_once $file_path;
                $this->loaded_shortcodes[] = $file;
            } else {
                error_log( 'ENNU Shortcode Loader: File not found - ' . $file );
            }
        }
        
        error_log( 'ENNU Shortcode Loader: Loaded ' . count( $this->loaded_shortcodes ) . ' shortcode files' );
    }
    
    public function get_loaded_shortcodes() {
        return $this->loaded_shortcodes;
    }
}

ENNU_Shortcode_Loader::get_instance();
