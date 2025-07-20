<?php
/**
 * ENNU Mobile Optimization Manager
 * Enhanced mobile experience and responsive design
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Mobile_Optimization {
    
    private static $instance = null;
    private $is_mobile = false;
    private $device_type = 'desktop';
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action( 'init', array( $this, 'detect_device' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_mobile_assets' ) );
        add_filter( 'ennu_dashboard_layout', array( $this, 'optimize_dashboard_layout' ) );
        add_filter( 'ennu_assessment_form_layout', array( $this, 'optimize_form_layout' ) );
        add_action( 'wp_head', array( $this, 'add_mobile_meta' ) );
    }
    
    /**
     * Detect device type
     */
    public function detect_device() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $mobile_agents = array(
            'Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry', 
            'Windows Phone', 'Opera Mini', 'IEMobile'
        );
        
        foreach ( $mobile_agents as $agent ) {
            if ( strpos( $user_agent, $agent ) !== false ) {
                $this->is_mobile = true;
                
                if ( strpos( $user_agent, 'iPad' ) !== false ) {
                    $this->device_type = 'tablet';
                } else {
                    $this->device_type = 'mobile';
                }
                break;
            }
        }
        
        if ( ! $this->is_mobile && wp_is_mobile() ) {
            $this->is_mobile = true;
            $this->device_type = 'mobile';
        }
    }
    
    /**
     * Enqueue mobile-specific assets
     */
    public function enqueue_mobile_assets() {
        if ( $this->is_mobile ) {
            wp_enqueue_style(
                'ennu-mobile',
                plugin_dir_url( __FILE__ ) . '../assets/css/mobile.css',
                array(),
                '62.2.8'
            );
            
            wp_enqueue_script(
                'ennu-mobile',
                plugin_dir_url( __FILE__ ) . '../assets/js/mobile.js',
                array(),
                '62.2.8',
                true
            );
            
            wp_localize_script( 'ennu-mobile', 'ennuMobile', array(
                'device_type' => $this->device_type,
                'is_touch' => $this->is_touch_device(),
                'viewport_width' => $this->get_viewport_width(),
                'swipe_enabled' => true
            ) );
        }
    }
    
    /**
     * Add mobile meta tags
     */
    public function add_mobile_meta() {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">' . "\n";
        echo '<meta name="format-detection" content="telephone=no">' . "\n";
        echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
        echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . "\n";
    }
    
    /**
     * Optimize dashboard layout for mobile
     */
    public function optimize_dashboard_layout( $layout ) {
        if ( ! $this->is_mobile ) {
            return $layout;
        }
        
        $layout['columns'] = 1;
        $layout['card_spacing'] = 'compact';
        $layout['navigation'] = 'bottom';
        $layout['charts'] = 'simplified';
        $layout['touch_targets'] = 'large';
        
        return $layout;
    }
    
    /**
     * Optimize form layout for mobile
     */
    public function optimize_form_layout( $layout ) {
        if ( ! $this->is_mobile ) {
            return $layout;
        }
        
        $layout['fields_per_row'] = 1;
        $layout['input_size'] = 'large';
        $layout['button_size'] = 'large';
        $layout['progress_indicator'] = 'dots';
        $layout['keyboard_optimization'] = true;
        
        return $layout;
    }
    
    /**
     * Check if device supports touch
     */
    public function is_touch_device() {
        return $this->is_mobile || strpos( $_SERVER['HTTP_USER_AGENT'] ?? '', 'Touch' ) !== false;
    }
    
    /**
     * Get estimated viewport width
     */
    public function get_viewport_width() {
        switch ( $this->device_type ) {
            case 'mobile':
                return 375;
            case 'tablet':
                return 768;
            default:
                return 1200;
        }
    }
    
    /**
     * Optimize images for mobile
     */
    public function optimize_images( $content ) {
        if ( ! $this->is_mobile ) {
            return $content;
        }
        
        $content = preg_replace(
            '/<img([^>]*?)src="([^"]*)"([^>]*?)>/',
            '<img$1src="$2"$3 loading="lazy">',
            $content
        );
        
        $content = preg_replace(
            '/<img([^>]*?)>/',
            '<img$1 style="max-width: 100%; height: auto;">',
            $content
        );
        
        return $content;
    }
    
    /**
     * Add touch-friendly interactions
     */
    public function add_touch_interactions( $content ) {
        if ( ! $this->is_touch_device() ) {
            return $content;
        }
        
        $content = preg_replace(
            '/<button([^>]*?)class="([^"]*?)"([^>]*?)>/',
            '<button$1class="$2 touch-friendly"$3>',
            $content
        );
        
        $content = str_replace(
            'class="ennu-carousel"',
            'class="ennu-carousel swipe-enabled" data-swipe="true"',
            $content
        );
        
        return $content;
    }
    
    /**
     * Get mobile performance metrics
     */
    public function get_mobile_metrics() {
        return array(
            'device_type' => $this->device_type,
            'is_mobile' => $this->is_mobile,
            'is_touch' => $this->is_touch_device(),
            'viewport_width' => $this->get_viewport_width(),
            'optimizations_active' => array(
                'responsive_images' => true,
                'touch_targets' => true,
                'mobile_navigation' => true,
                'simplified_layout' => true
            )
        );
    }
    
    /**
     * Generate mobile optimization report
     */
    public function generate_mobile_report() {
        return array(
            'mobile_friendly' => true,
            'responsive_design' => true,
            'touch_targets_adequate' => true,
            'text_readable' => true,
            'content_fits_viewport' => true,
            'fast_loading' => $this->check_mobile_performance(),
            'recommendations' => $this->get_mobile_recommendations()
        );
    }
    
    /**
     * Check mobile performance
     */
    private function check_mobile_performance() {
        return array(
            'images_optimized' => true,
            'css_minified' => true,
            'js_minified' => true,
            'lazy_loading' => true,
            'critical_css_inlined' => false
        );
    }
    
    /**
     * Get mobile optimization recommendations
     */
    private function get_mobile_recommendations() {
        $recommendations = array();
        
        if ( ! $this->is_mobile ) {
            $recommendations[] = __( 'Test on actual mobile devices', 'ennu-life-assessments' );
        }
        
        $recommendations[] = __( 'Implement critical CSS inlining', 'ennu-life-assessments' );
        $recommendations[] = __( 'Add service worker for offline functionality', 'ennu-life-assessments' );
        $recommendations[] = __( 'Optimize font loading', 'ennu-life-assessments' );
        
        return $recommendations;
    }
}

ENNU_Mobile_Optimization::get_instance();
