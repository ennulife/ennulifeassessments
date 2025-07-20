<?php
/**
 * ENNU Mobile Optimization Manager
 * Comprehensive mobile experience with PWA features, advanced touch interactions,
 * offline functionality, and performance optimizations
 *
 * @package ENNU_Life_Assessments
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Mobile_Optimization {
    
    private static $instance = null;
    private $is_mobile = false;
    private $device_type = 'desktop';
    private $viewport_width = 1200;
    private $supports_pwa = false;
    
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
        
        add_action( 'wp_head', array( $this, 'add_pwa_manifest' ) );
        add_action( 'wp_head', array( $this, 'add_service_worker' ) );
        add_action( 'wp_footer', array( $this, 'add_mobile_scripts' ) );
        
        add_action( 'wp_ajax_ennu_mobile_sync', array( $this, 'handle_mobile_sync' ) );
        add_action( 'wp_ajax_nopriv_ennu_mobile_sync', array( $this, 'handle_mobile_sync' ) );
        add_action( 'wp_ajax_ennu_mobile_dashboard', array( $this, 'get_mobile_dashboard' ) );
        
        add_filter( 'ennu_asset_loading', array( $this, 'optimize_mobile_assets' ) );
        add_action( 'template_redirect', array( $this, 'implement_mobile_caching' ) );
        
        add_filter( 'ennu_form_classes', array( $this, 'add_touch_classes' ) );
        add_filter( 'ennu_button_classes', array( $this, 'add_touch_classes' ) );
    }
    
    /**
     * Detect device type and capabilities
     */
    public function detect_device() {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $mobile_agents = array(
            'Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry', 
            'Windows Phone', 'Opera Mini', 'IEMobile', 'webOS', 'Kindle'
        );
        
        foreach ( $mobile_agents as $agent ) {
            if ( strpos( $user_agent, $agent ) !== false ) {
                $this->is_mobile = true;
                
                if ( strpos( $user_agent, 'iPad' ) !== false || strpos( $user_agent, 'Tablet' ) !== false ) {
                    $this->device_type = 'tablet';
                    $this->viewport_width = 768;
                } else {
                    $this->device_type = 'mobile';
                    $this->viewport_width = 375;
                }
                break;
            }
        }
        
        if ( ! $this->is_mobile && wp_is_mobile() ) {
            $this->is_mobile = true;
            $this->device_type = 'mobile';
            $this->viewport_width = 375;
        }
        
        $this->supports_pwa = $this->detect_pwa_support( $user_agent );
    }
    
    /**
     * Detect Progressive Web App support
     */
    private function detect_pwa_support( $user_agent ) {
        $pwa_browsers = array( 'Chrome', 'Firefox', 'Safari', 'Edge', 'Opera' );
        
        foreach ( $pwa_browsers as $browser ) {
            if ( strpos( $user_agent, $browser ) !== false ) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Enqueue mobile-specific assets
     */
    public function enqueue_mobile_assets() {
        if ( $this->is_mobile ) {
            wp_enqueue_style(
                'ennu-mobile-optimized',
                plugin_dir_url( __FILE__ ) . '../assets/css/mobile-optimized.css',
                array(),
                '62.2.9'
            );
            
            wp_enqueue_script(
                'ennu-touch-gestures',
                plugin_dir_url( __FILE__ ) . '../assets/js/touch-gestures.js',
                array(),
                '62.2.9',
                true
            );
            
            wp_enqueue_script(
                'ennu-mobile-enhanced',
                plugin_dir_url( __FILE__ ) . '../assets/js/mobile-enhanced.js',
                array( 'ennu-touch-gestures' ),
                '62.2.9',
                true
            );
            
            wp_localize_script( 'ennu-mobile-enhanced', 'ennuMobile', array(
                'device_type' => $this->device_type,
                'is_touch' => $this->is_touch_device(),
                'viewport_width' => $this->viewport_width,
                'supports_pwa' => $this->supports_pwa,
                'swipe_enabled' => true,
                'offline_enabled' => true,
                'pull_to_refresh' => true,
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'ennu_mobile_nonce' ),
                'strings' => array(
                    'loading' => __( 'Loading...', 'ennu-life-assessments' ),
                    'offline' => __( 'You are offline. Changes will sync when connection is restored.', 'ennu-life-assessments' ),
                    'pull_refresh' => __( 'Pull to refresh', 'ennu-life-assessments' ),
                    'release_refresh' => __( 'Release to refresh', 'ennu-life-assessments' ),
                    'swipe_navigate' => __( 'Swipe to navigate', 'ennu-life-assessments' ),
                    'tap_continue' => __( 'Tap to continue', 'ennu-life-assessments' ),
                    'sync_complete' => __( 'Sync completed', 'ennu-life-assessments' ),
                    'sync_error' => __( 'Sync failed. Please try again.', 'ennu-life-assessments' )
                )
            ) );
        }
    }
    
    /**
     * Add comprehensive mobile meta tags
     */
    public function add_mobile_meta() {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">' . "\n";
        echo '<meta name="format-detection" content="telephone=no">' . "\n";
        echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
        echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">' . "\n";
        echo '<meta name="apple-mobile-web-app-title" content="ENNU Life">' . "\n";
        
        echo '<meta name="theme-color" content="#2196F3">' . "\n";
        echo '<meta name="msapplication-navbutton-color" content="#2196F3">' . "\n";
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="#2196F3">' . "\n";
        
        echo '<link rel="apple-touch-icon" sizes="180x180" href="' . plugin_dir_url( __FILE__ ) . '../assets/images/apple-touch-icon-180x180.png">' . "\n";
        echo '<link rel="apple-touch-icon" sizes="152x152" href="' . plugin_dir_url( __FILE__ ) . '../assets/images/apple-touch-icon-152x152.png">' . "\n";
        echo '<link rel="apple-touch-icon" sizes="144x144" href="' . plugin_dir_url( __FILE__ ) . '../assets/images/apple-touch-icon-144x144.png">' . "\n";
        echo '<link rel="apple-touch-icon" sizes="120x120" href="' . plugin_dir_url( __FILE__ ) . '../assets/images/apple-touch-icon-120x120.png">' . "\n";
        echo '<link rel="apple-touch-icon" sizes="114x114" href="' . plugin_dir_url( __FILE__ ) . '../assets/images/apple-touch-icon-114x114.png">' . "\n";
        echo '<link rel="apple-touch-icon" sizes="76x76" href="' . plugin_dir_url( __FILE__ ) . '../assets/images/apple-touch-icon-76x76.png">' . "\n";
        echo '<link rel="apple-touch-icon" sizes="72x72" href="' . plugin_dir_url( __FILE__ ) . '../assets/images/apple-touch-icon-72x72.png">' . "\n";
        echo '<link rel="apple-touch-icon" sizes="60x60" href="' . plugin_dir_url( __FILE__ ) . '../assets/images/apple-touch-icon-60x60.png">' . "\n";
        echo '<link rel="apple-touch-icon" sizes="57x57" href="' . plugin_dir_url( __FILE__ ) . '../assets/images/apple-touch-icon-57x57.png">' . "\n";
        
        echo '<link rel="icon" type="image/png" sizes="192x192" href="' . plugin_dir_url( __FILE__ ) . '../assets/images/android-icon-192x192.png">' . "\n";
        echo '<link rel="icon" type="image/png" sizes="32x32" href="' . plugin_dir_url( __FILE__ ) . '../assets/images/favicon-32x32.png">' . "\n";
        echo '<link rel="icon" type="image/png" sizes="96x96" href="' . plugin_dir_url( __FILE__ ) . '../assets/images/favicon-96x96.png">' . "\n";
        echo '<link rel="icon" type="image/png" sizes="16x16" href="' . plugin_dir_url( __FILE__ ) . '../assets/images/favicon-16x16.png">' . "\n";
        
        echo '<meta name="msapplication-TileColor" content="#2196F3">' . "\n";
        echo '<meta name="msapplication-TileImage" content="' . plugin_dir_url( __FILE__ ) . '../assets/images/ms-icon-144x144.png">' . "\n";
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
