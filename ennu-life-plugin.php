<?php
/**
 * Plugin Name: ENNU Life Assessment Plugin
 * Plugin URI: https://ennulife.com
 * Description: Advanced health and wellness assessment system with enhanced features, modern UI, and comprehensive data management.
 * Version: 24.12.4
 * Author: ENNU Life Development Team
 * Author URI: https://ennulife.com
 * License: Proprietary
 * Text Domain: ennulifeassessments
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8.1
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define( 'ENNU_LIFE_VERSION', '24.12.4' );
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
        // Register activation/deactivation hooks - with safety checks
        if (function_exists('register_activation_hook')) {
            register_activation_hook(__FILE__, array($this, 'activate'));
        }
        
        if (function_exists('register_deactivation_hook')) {
            register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        }
        
        // Initialize plugin - with safety checks
        if (function_exists('add_action')) {
            add_action('plugins_loaded', array($this, 'init'));
            // Hook frontend scripts correctly
            add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        }
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
        
        // Setup hooks
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
        if (function_exists('add_action')) {
            // Admin hooks
            if (function_exists('is_admin') && is_admin() && $this->admin) {
                add_action('admin_menu', array($this->admin, 'add_admin_menu'));
                add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_admin_scripts'));
            }
        }
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        if (!function_exists('wp_enqueue_style') || !function_exists('wp_enqueue_script')) {
            return;
        }
        
        // Enqueue frontend styles
        $css_file = ENNU_LIFE_PLUGIN_URL . 'assets/css/ennu-frontend-forms.css';
        if (file_exists(ENNU_LIFE_PLUGIN_PATH . 'assets/css/ennu-frontend-forms.css')) {
            wp_enqueue_style('ennu-assessment-frontend', $css_file, array(), ENNU_LIFE_VERSION);
        }
        
        // Enqueue frontend scripts
        $js_file = ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-frontend-forms.js';
        if (file_exists(ENNU_LIFE_PLUGIN_PATH . 'assets/js/ennu-frontend-forms.js')) {
            wp_enqueue_script('ennu-assessment-frontend', $js_file, array('jquery'), ENNU_LIFE_VERSION, true);
            
            // Localize script
            if (function_exists('wp_localize_script')) {
                wp_localize_script('ennu-assessment-frontend', 'ennu_ajax', array(
                    'ajax_url' => function_exists('admin_url') ? admin_url('admin-ajax.php') : '',
                    'nonce' => function_exists('wp_create_nonce') ? wp_create_nonce('ennu_ajax_nonce') : ''
                ));
                // Add debug log
                wp_add_inline_script('ennu-assessment-frontend', 'console.log("ENNU JS Loaded Successfully");');
            }
        }
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

