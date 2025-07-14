<?php
/**
 * Plugin Name:       ENNU Life Assessments
 * Description:       A comprehensive health assessment and scoring system.
 * Version:           46.0.0
 * Author:            ENNU Life Development Team
 * License:           Proprietary
 * Text Domain:       ennulifeassessments
 * Domain Path:       /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define( 'ENNU_LIFE_VERSION', '46.0.0' );
// Plugin paths - with safety checks
if (function_exists('plugin_dir_path')) {
    define('ENNU_LIFE_PLUGIN_PATH', plugin_dir_path(__FILE__));
} else {
    define('ENNU_LIFE_PLUGIN_PATH', dirname(__FILE__) . '/');
}

if (function_exists('plugin_dir_url')) {
    define('ENNU_LIFE_PLUGIN_URL', plugin_dir_url(__FILE__));
} else {
    define('ENNU_LIFE_PLUGIN_URL', '');
}

// Main plugin class - with class existence check
if (!class_exists('ENNU_Life_Enhanced_Plugin')) {

class ENNU_Life_Enhanced_Plugin {
    
    /**
     * Single instance of the plugin
     */
    private static $instance = null;
    
    /**
     * Plugin components
     */
    private $database = null;
    private $admin = null;
    private $form_handler = null;
    private $shortcodes = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        register_uninstall_hook(__FILE__, array('ENNU_Life_Enhanced_Plugin', 'uninstall'));

        // Load dependencies and initialize the plugin on `plugins_loaded`
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load dependencies and textdomain first
        $this->load_dependencies();
        $this->load_textdomain();
        
        // Initialize components
        $this->init_components();
        
        // Setup all hooks
        $this->setup_hooks();
    }
    
    /**
     * Load all dependencies
     */
    private function load_dependencies() {
        $includes = array(
            'class-enhanced-database.php', // Only load the enhanced version
            'class-enhanced-admin.php',    // Only load the enhanced version
            'class-assessment-shortcodes.php',
            'class-comprehensive-assessment-display.php',
            'class-scoring-system.php',
            'class-score-cache.php',
            'class-ajax-security.php',
            'class-compatibility-manager.php',
            'class-question-mapper.php'
        );
        
        foreach ($includes as $file) {
            $file_path = ENNU_LIFE_PLUGIN_PATH . 'includes/' . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }
    
    /**
     * Initialize components
     */
    private function init_components() {
        // Initialize database - with class existence check
        if (class_exists('ENNU_Enhanced_Database')) {
            $this->database = new ENNU_Enhanced_Database();
        }
        
        // Initialize admin - with class existence check
        if (class_exists('ENNU_Enhanced_Admin')) {
            $this->admin = new ENNU_Enhanced_Admin();
        }
        
        // Initialize shortcodes - with class existence check
        if (class_exists('ENNU_Assessment_Shortcodes')) {
            $this->shortcodes = new ENNU_Assessment_Shortcodes();
        }
    }
    
    /**
     * Setup hooks
     */
    private function setup_hooks() {
        // Frontend Asset Hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));

        // Admin Hooks
        if (is_admin() && $this->admin) {
            add_action('admin_menu', array($this->admin, 'add_admin_menu'));
            add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_admin_assets'));
            add_action('show_user_profile', array($this->admin, 'show_user_assessment_fields'));
            add_action('edit_user_profile', array($this->admin, 'show_user_assessment_fields'));
            add_action('personal_options_update', array($this->admin, 'save_user_assessment_fields'));
            add_action('edit_user_profile_update', array($this->admin, 'save_user_assessment_fields'));
        }

        // Shortcode and AJAX Hooks
        if ($this->shortcodes) {
            add_action('init', array($this->shortcodes, 'register_shortcodes'));
            add_action('wp_ajax_ennu_submit_assessment', array($this->shortcodes, 'handle_assessment_submission'));
            add_action('wp_ajax_nopriv_ennu_submit_assessment', array($this->shortcodes, 'handle_assessment_submission'));
            add_action('wp_enqueue_scripts', array($this->shortcodes, 'enqueue_chart_scripts'));
            add_action('wp_enqueue_scripts', array($this->shortcodes, 'enqueue_results_styles'));
        }
    }
    
    /**
     * Enqueue frontend scripts and styles.
     */
    public function enqueue_frontend_scripts() {
        global $post;

        $has_assessment_shortcode = false;
        if ( is_a( $post, 'WP_Post' ) ) {
            $assessment_shortcodes = array(
                'ennu-welcome-assessment', 'ennu-hair-assessment', 'ennu-ed-treatment-assessment',
                'ennu-weight-loss-assessment', 'ennu-health-assessment', 'ennu-skin-assessment',
                'ennu-sleep-assessment', 'ennu-hormone-assessment', 'ennu-menopause-assessment', 'ennu-testosterone-assessment'
            );
            foreach ($assessment_shortcodes as $shortcode) {
                if (has_shortcode($post->post_content, $shortcode)) {
                    $has_assessment_shortcode = true;
                    break;
                }
            }
        }

        if ( $has_assessment_shortcode ) {
            wp_enqueue_style( 'ennu-frontend-forms', ENNU_LIFE_PLUGIN_URL . 'assets/css/ennu-frontend-forms.css', array(), ENNU_LIFE_VERSION );
            wp_enqueue_script( 'ennu-frontend-forms', ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-frontend-forms.js', array( 'jquery' ), ENNU_LIFE_VERSION, true );
            wp_localize_script( 'ennu-frontend-forms', 'ennu_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
        }
        
        // --- PHASE 3 REFACTOR: Enqueue Dashboard Styles ---
        if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ennu-user-dashboard' ) ) {
            // Enqueue Font Awesome for icons
            wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), '5.15.4' );
            
            wp_enqueue_style( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/css/user-dashboard.css', array(), ENNU_LIFE_VERSION . '.' . time() );
        }
        // --- END PHASE 3 REFACTOR ---
    }
    
    /**
     * Load textdomain
     */
    public function load_textdomain() {
        if (function_exists('load_plugin_textdomain')) {
            load_plugin_textdomain('ennulifeassessments', false, dirname(plugin_basename(__FILE__)) . '/languages');
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // The main 'init' hook has already run at this point, so all dependencies
        // are loaded and components are initialized. We can directly access the
        // database object from the main plugin instance.
        if ( $this->database && method_exists( $this->database, 'create_tables' ) ) {
            $this->database->create_tables();
        }
        
        // Set default options - with safety checks
        if (function_exists('add_option')) {
            add_option('ennu_life_version', ENNU_LIFE_VERSION);
            add_option('ennu_life_activated', time());
        }
        
        // Flush rewrite rules - with safety check
        if (function_exists('flush_rewrite_rules')) {
            flush_rewrite_rules();
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules - with safety check
        if (function_exists('flush_rewrite_rules')) {
            flush_rewrite_rules();
        }
    }

    /**
     * Plugin uninstallation
     */
    public static function uninstall() {
        global $wpdb;

        // Delete options
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'ennu_%'");

        // Delete user meta
        $wpdb->query("DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'ennu_%'");

        // Flush rewrite rules
        if (function_exists('flush_rewrite_rules')) {
            flush_rewrite_rules();
        }
    }
    
    /**
     * Get database instance
     */
    public function get_database() {
        return $this->database;
    }
    
    /**
     * Get admin instance
     */
    public function get_admin() {
        return $this->admin;
    }
    
    /**
     * Get shortcodes instance
     */
    public function get_shortcodes() {
        return $this->shortcodes;
    }

    /**
     * Getter for the shortcode handler instance.
     * This is the definitive fix for the admin panel fatal errors.
     *
     * @return ENNU_Assessment_Shortcodes
     */
    public function get_shortcode_handler() {
        return $this->shortcodes;
    }
    
    /**
     * Check if plugin is compatible
     */
    public static function is_compatible() {
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            return false;
        }
        
        // Check WordPress version
        if (function_exists('get_bloginfo')) {
            $wp_version = get_bloginfo('version');
            if (version_compare($wp_version, '5.0', '<')) {
                return false;
            }
        }
        
        return true;
    }
}

} // End class_exists check

// Initialize the plugin - with safety checks
if (class_exists('ENNU_Life_Enhanced_Plugin')) {
    // Check compatibility first
    if (ENNU_Life_Enhanced_Plugin::is_compatible()) {
        ENNU_Life_Enhanced_Plugin::get_instance();
    } else {
        // Show admin notice for incompatibility
        if (function_exists('add_action')) {
            add_action('admin_notices', function() {
                if (function_exists('current_user_can') && current_user_can('activate_plugins')) {
                    echo '<div class="notice notice-error"><p>';
                    echo '<strong>ENNU Life Plugin:</strong> This plugin requires PHP 7.4+ and WordPress 5.0+.';
                    echo '</p></div>';
                }
            });
        }
    }
}

// Helper function to get plugin instance
if (!function_exists('ennu_life')) {
    function ennu_life() {
        if (class_exists('ENNU_Life_Enhanced_Plugin')) {
            return ENNU_Life_Enhanced_Plugin::get_instance();
        }
        return null;
    }
}

