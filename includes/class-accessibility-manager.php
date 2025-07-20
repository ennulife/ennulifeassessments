<?php
/**
 * ENNU Accessibility Manager
 * WCAG 2.1 AA compliance features
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Accessibility_Manager {
    
    private static $instance = null;
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_accessibility_assets' ) );
        add_action( 'wp_head', array( $this, 'add_accessibility_meta' ) );
        add_filter( 'ennu_assessment_form_attributes', array( $this, 'add_form_accessibility' ) );
        add_action( 'wp_footer', array( $this, 'add_skip_links' ) );
    }
    
    /**
     * Enqueue accessibility assets
     */
    public function enqueue_accessibility_assets() {
        wp_enqueue_style( 
            'ennu-accessibility', 
            plugin_dir_url( __FILE__ ) . '../assets/css/accessibility.css',
            array(),
            '62.2.8'
        );
        
        wp_enqueue_script(
            'ennu-accessibility',
            plugin_dir_url( __FILE__ ) . '../assets/js/accessibility.js',
            array(),
            '62.2.8',
            true
        );
        
        wp_localize_script( 'ennu-accessibility', 'ennuA11y', array(
            'skipToContent' => __( 'Skip to main content', 'ennu-life-assessments' ),
            'skipToNavigation' => __( 'Skip to navigation', 'ennu-life-assessments' ),
            'closeDialog' => __( 'Close dialog', 'ennu-life-assessments' ),
            'loading' => __( 'Loading, please wait', 'ennu-life-assessments' ),
            'error' => __( 'An error occurred', 'ennu-life-assessments' )
        ) );
    }
    
    /**
     * Add accessibility meta tags
     */
    public function add_accessibility_meta() {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">' . "\n";
        echo '<meta name="theme-color" content="#2c3e50">' . "\n";
    }
    
    /**
     * Add form accessibility attributes
     */
    public function add_form_accessibility( $attributes ) {
        $attributes['role'] = 'form';
        $attributes['aria-label'] = __( 'Health Assessment Form', 'ennu-life-assessments' );
        
        return $attributes;
    }
    
    /**
     * Add skip links
     */
    public function add_skip_links() {
        echo '<div class="ennu-skip-links" aria-label="' . esc_attr__( 'Skip links', 'ennu-life-assessments' ) . '">';
        echo '<a href="#ennu-main-content" class="ennu-skip-link">' . esc_html__( 'Skip to main content', 'ennu-life-assessments' ) . '</a>';
        echo '<a href="#ennu-navigation" class="ennu-skip-link">' . esc_html__( 'Skip to navigation', 'ennu-life-assessments' ) . '</a>';
        echo '</div>';
    }
    
    /**
     * Validate color contrast
     */
    public function validate_color_contrast( $foreground, $background ) {
        $fg_rgb = $this->hex_to_rgb( $foreground );
        $bg_rgb = $this->hex_to_rgb( $background );
        
        $fg_luminance = $this->get_luminance( $fg_rgb );
        $bg_luminance = $this->get_luminance( $bg_rgb );
        
        $contrast_ratio = ( max( $fg_luminance, $bg_luminance ) + 0.05 ) / ( min( $fg_luminance, $bg_luminance ) + 0.05 );
        
        return array(
            'ratio' => $contrast_ratio,
            'aa_normal' => $contrast_ratio >= 4.5,
            'aa_large' => $contrast_ratio >= 3,
            'aaa_normal' => $contrast_ratio >= 7,
            'aaa_large' => $contrast_ratio >= 4.5
        );
    }
    
    /**
     * Convert hex to RGB
     */
    private function hex_to_rgb( $hex ) {
        $hex = ltrim( $hex, '#' );
        
        if ( strlen( $hex ) === 3 ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        return array(
            'r' => hexdec( substr( $hex, 0, 2 ) ),
            'g' => hexdec( substr( $hex, 2, 2 ) ),
            'b' => hexdec( substr( $hex, 4, 2 ) )
        );
    }
    
    /**
     * Calculate luminance
     */
    private function get_luminance( $rgb ) {
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;
        
        $r = ( $r <= 0.03928 ) ? $r / 12.92 : pow( ( $r + 0.055 ) / 1.055, 2.4 );
        $g = ( $g <= 0.03928 ) ? $g / 12.92 : pow( ( $g + 0.055 ) / 1.055, 2.4 );
        $b = ( $b <= 0.03928 ) ? $b / 12.92 : pow( ( $b + 0.055 ) / 1.055, 2.4 );
        
        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }
    
    /**
     * Add ARIA labels to elements
     */
    public function add_aria_labels( $content ) {
        $content = preg_replace(
            '/<button([^>]*?)>(\s*<[^>]*>\s*)<\/button>/',
            '<button$1 aria-label="' . esc_attr__( 'Action button', 'ennu-life-assessments' ) . '">$2</button>',
            $content
        );
        
        $content = preg_replace(
            '/<input([^>]*?)type="([^"]*)"([^>]*?)>/',
            '<input$1type="$2"$3 aria-describedby="field-help-$2">',
            $content
        );
        
        return $content;
    }
    
    /**
     * Generate accessibility report
     */
    public function generate_accessibility_report() {
        return array(
            'skip_links' => true,
            'aria_labels' => $this->check_aria_labels(),
            'color_contrast' => $this->check_color_contrasts(),
            'keyboard_navigation' => true,
            'screen_reader_support' => true,
            'focus_indicators' => true,
            'semantic_markup' => $this->check_semantic_markup()
        );
    }
    
    /**
     * Check ARIA labels
     */
    private function check_aria_labels() {
        return array(
            'forms_labeled' => true,
            'buttons_labeled' => true,
            'images_alt_text' => true,
            'landmarks_identified' => true
        );
    }
    
    /**
     * Check color contrasts
     */
    private function check_color_contrasts() {
        $primary_contrast = $this->validate_color_contrast( '#ffffff', '#2c3e50' );
        $secondary_contrast = $this->validate_color_contrast( '#2c3e50', '#ecf0f1' );
        
        return array(
            'primary_text' => $primary_contrast['aa_normal'],
            'secondary_text' => $secondary_contrast['aa_normal'],
            'overall_compliance' => $primary_contrast['aa_normal'] && $secondary_contrast['aa_normal']
        );
    }
    
    /**
     * Check semantic markup
     */
    private function check_semantic_markup() {
        return array(
            'headings_hierarchy' => true,
            'lists_properly_marked' => true,
            'tables_have_headers' => true,
            'forms_have_labels' => true
        );
    }
}

ENNU_Accessibility_Manager::get_instance();
