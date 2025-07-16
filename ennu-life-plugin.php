<?php
/**
 * Plugin Name: ENNU Life Assessments
 * Plugin URI: https://ennulife.com
 * Description: Advanced health assessment system with comprehensive user scoring
 * Version: 60.0.0
 * Author: ENNU Life Development Team
 * License: GPLv2 or later
 * Text Domain: ennulifeassessments
 * Domain Path:       /languages
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants
define( 'ENNU_LIFE_VERSION', '60.0.0' );
// Plugin paths - with safety checks
if ( function_exists( 'plugin_dir_path' ) ) {
	define( 'ENNU_LIFE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
} else {
	define( 'ENNU_LIFE_PLUGIN_PATH', dirname( __FILE__ ) . '/' );
}

if ( function_exists( 'plugin_dir_url' ) ) {
	define( 'ENNU_LIFE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
} else {
	define( 'ENNU_LIFE_PLUGIN_URL', '' );
}

// Main plugin class - with class existence check
if ( ! class_exists( 'ENNU_Life_Enhanced_Plugin' ) ) {

	class ENNU_Life_Enhanced_Plugin {

		/**
		 * Single instance of the plugin
		 */
		private static $instance = null;

		/**
		 * Plugin components
		 */
		private $database     = null;
		private $admin        = null;
		private $form_handler = null;
		private $shortcodes   = null;

		/**
		 * Get single instance
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		private function __construct() {
			// Register activation/deactivation hooks
			register_activation_hook( __FILE__, array( $this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
			register_uninstall_hook( __FILE__, array( 'ENNU_Life_Enhanced_Plugin', 'uninstall' ) );

			// Load dependencies and initialize the plugin on `plugins_loaded`
			add_action( 'plugins_loaded', array( $this, 'init' ) );
		}

		/**
		 * Initialize plugin
		 */
		public function init() {
			// Load dependencies and textdomain first
			$this->load_dependencies();
			// $this->load_textdomain(); // This is called too early. It will be moved to the init hook.

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
			);

			foreach ( $includes as $file ) {
				$file_path = ENNU_LIFE_PLUGIN_PATH . 'includes/' . $file;
				if ( file_exists( $file_path ) ) {
					require_once $file_path;
				}
			}
		}

		/**
		 * Initialize components
		 */
		private function init_components() {
			// Initialize database - with class existence check
			if ( class_exists( 'ENNU_Enhanced_Database' ) ) {
				$this->database = new ENNU_Enhanced_Database();
			}

			// Initialize admin - with class existence check
			if ( class_exists( 'ENNU_Enhanced_Admin' ) ) {
				$this->admin = new ENNU_Enhanced_Admin();
			}

			// Initialize shortcodes - with class existence check
			if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
				$this->shortcodes = new ENNU_Assessment_Shortcodes();
			}
		}

		/**
		 * Setup hooks
		 */
		private function setup_hooks() {
			// Load textdomain on the correct hook
			add_action( 'init', array( $this, 'load_textdomain' ) );

			// Frontend Asset Hooks
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );

			// Admin Hooks
			if ( is_admin() && $this->admin ) {
				add_action( 'admin_menu', array( $this->admin, 'add_admin_menu' ) );
				add_action( 'admin_enqueue_scripts', array( $this->admin, 'enqueue_admin_assets' ) );
				add_action( 'show_user_profile', array( $this->admin, 'show_user_assessment_fields' ) );
				add_action( 'edit_user_profile', array( $this->admin, 'show_user_assessment_fields' ) );
				add_action( 'personal_options_update', array( $this->admin, 'save_user_assessment_fields' ) );
				add_action( 'edit_user_profile_update', array( $this->admin, 'save_user_assessment_fields' ) );
			}

			// Shortcode and AJAX Hooks
			if ( $this->shortcodes ) {
				add_action( 'init', array( $this->shortcodes, 'register_shortcodes' ) );
				add_action( 'wp_ajax_ennu_submit_assessment', array( $this->shortcodes, 'handle_assessment_submission' ) );
				add_action( 'wp_ajax_nopriv_ennu_submit_assessment', array( $this->shortcodes, 'handle_assessment_submission' ) );
				add_action( 'wp_ajax_ennu_check_email', array( $this->shortcodes, 'ajax_check_email_exists' ) );
				add_action( 'wp_ajax_nopriv_ennu_check_email', array( $this->shortcodes, 'ajax_check_email_exists' ) );
				add_action( 'wp_enqueue_scripts', array( $this->shortcodes, 'enqueue_chart_scripts' ) );
				add_action( 'wp_enqueue_scripts', array( $this->shortcodes, 'enqueue_results_styles' ) );
			}

			// v57.1.0: Admin AJAX actions for user profile page
			if ( is_admin() && $this->admin ) {
				add_action( 'wp_ajax_ennu_recalculate_all_scores', array( $this->admin, 'handle_recalculate_all_scores' ) );
				add_action( 'wp_ajax_ennu_clear_all_assessment_data', array( $this->admin, 'handle_clear_all_assessment_data' ) );
				add_action( 'wp_ajax_ennu_clear_single_assessment_data', array( $this->admin, 'handle_clear_single_assessment_data' ) );
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
					'ennu-welcome-assessment',
					'ennu-hair-assessment',
					'ennu-ed-treatment-assessment',
					'ennu-weight-loss-assessment',
					'ennu-health-assessment',
					'ennu-skin-assessment',
					'ennu-sleep-assessment',
					'ennu-hormone-assessment',
					'ennu-menopause-assessment',
					'ennu-testosterone-assessment',
					'ennu-health-optimization-assessment',
				);
				foreach ( $assessment_shortcodes as $shortcode ) {
					if ( has_shortcode( $post->post_content, $shortcode ) ) {
						$has_assessment_shortcode = true;
						break;
					}
				}
			}

					if ( $has_assessment_shortcode ) {
			// Use built assets for production, source files for development
			$use_built_assets = ! defined( 'WP_DEBUG' ) || ! WP_DEBUG;
			$css_path = $use_built_assets ? 'assets/dist/ennu-frontend-forms-css.min.css' : 'assets/css/ennu-frontend-forms.css';
			$js_path = $use_built_assets ? 'assets/dist/ennu-frontend-forms.min.js' : 'assets/js/ennu-frontend-forms.js';
			
			wp_enqueue_style( 'ennu-frontend-forms', ENNU_LIFE_PLUGIN_URL . $css_path, array(), ENNU_LIFE_VERSION );
			wp_enqueue_script( 'ennu-frontend-forms', ENNU_LIFE_PLUGIN_URL . $js_path, array( 'jquery' ), ENNU_LIFE_VERSION, true );
			wp_localize_script(
				'ennu-frontend-forms',
				'ennu_ajax',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'ennu_ajax_nonce' ),
					'debug'    => defined( 'WP_DEBUG' ) && WP_DEBUG,
					'version'  => ENNU_LIFE_VERSION,
				)
			);
		}

			// --- PHASE 3 REFACTOR: Enqueue Dashboard Styles ---
			$has_dashboard_shortcode = is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ennu-user-dashboard' );

			// --- v57.0.0 Refactor: Check for details page shortcodes ---
			$has_details_shortcode = false;
			if ( is_a( $post, 'WP_Post' ) ) {
				$content = $post->post_content;
				if ( strpos( $content, 'ennu-' ) !== false && strpos( $content, '-assessment-details' ) !== false ) {
					$has_details_shortcode = true;
				}
			}

			// v60.0.0: UNIFIED ASSET LOADING with Build System Integration
			if ( $has_dashboard_shortcode || $has_details_shortcode || $has_assessment_shortcode ) {
				// Enqueue Font Awesome for icons
				wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), '5.15.4' );

				// Use built assets for production, source files for development
				$use_built_assets = ! defined( 'WP_DEBUG' ) || ! WP_DEBUG;
				$dashboard_css_path = $use_built_assets ? 'assets/dist/user-dashboard-css.min.css' : 'assets/css/user-dashboard.css';
				$dashboard_js_path = $use_built_assets ? 'assets/dist/ennu-user-dashboard.min.js' : 'assets/js/user-dashboard.js';

				wp_enqueue_style( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . $dashboard_css_path, array(), ENNU_LIFE_VERSION );
				wp_enqueue_script( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . $dashboard_js_path, array( 'jquery' ), ENNU_LIFE_VERSION, true );
			}
			// --- END PHASE 3 REFACTOR ---
		}

		/**
		 * Load textdomain
		 */
		public function load_textdomain() {
			if ( function_exists( 'load_plugin_textdomain' ) ) {
				load_plugin_textdomain( 'ennulifeassessments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
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
			if ( function_exists( 'add_option' ) ) {
				add_option( 'ennu_life_version', ENNU_LIFE_VERSION );
				add_option( 'ennu_life_activated', time() );
			}

			// Flush rewrite rules - with safety check
			if ( function_exists( 'flush_rewrite_rules' ) ) {
				flush_rewrite_rules();
			}
		}

		/**
		 * Plugin deactivation
		 */
		public function deactivate() {
			// Flush rewrite rules - with safety check
			if ( function_exists( 'flush_rewrite_rules' ) ) {
				flush_rewrite_rules();
			}
		}

		/**
		 * Plugin uninstallation
		 */
		public static function uninstall() {
			global $wpdb;

			// Delete options
			$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'ennu_%'" );

			// Delete user meta
			$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'ennu_%'" );

			// Flush rewrite rules
			if ( function_exists( 'flush_rewrite_rules' ) ) {
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
			if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
				return false;
			}

			// Check WordPress version
			if ( function_exists( 'get_bloginfo' ) ) {
				$wp_version = get_bloginfo( 'version' );
				if ( version_compare( $wp_version, '5.0', '<' ) ) {
					return false;
				}
			}

			return true;
		}
	}

} // End class_exists check

// Initialize the plugin - with safety checks
if ( class_exists( 'ENNU_Life_Enhanced_Plugin' ) ) {
	// Check compatibility first
	if ( ENNU_Life_Enhanced_Plugin::is_compatible() ) {
		ENNU_Life_Enhanced_Plugin::get_instance();
	} else {
		// Show admin notice for incompatibility
		if ( function_exists( 'add_action' ) ) {
			add_action(
				'admin_notices',
				function() {
					if ( function_exists( 'current_user_can' ) && current_user_can( 'activate_plugins' ) ) {
						echo '<div class="notice notice-error"><p>';
						echo '<strong>ENNU Life Plugin:</strong> This plugin requires PHP 7.4+ and WordPress 5.0+.';
						echo '</p></div>';
					}
				}
			);
		}
	}
}

// Helper function to get plugin instance
if ( ! function_exists( 'ennu_life' ) ) {
	function ennu_life() {
		if ( class_exists( 'ENNU_Life_Enhanced_Plugin' ) ) {
			return ENNU_Life_Enhanced_Plugin::get_instance();
		}
		return null;
	}
}

/**
 * A centralized, secure template loader for the plugin.
 *
 * This function handles loading template files and makes passed data
 * available to the template as local variables.
 *
 * @param string $template_name The name of the template file to load.
 * @param array  $data          An associative array of data to be extracted into variables.
 */
function ennu_load_template( $template_name, $data = array() ) {
	// Ensure the template name is a valid file name.
	$template_name = basename( $template_name );
	$template_path = ENNU_LIFE_PLUGIN_PATH . 'templates/' . $template_name;

	if ( file_exists( $template_path ) ) {
		// This is a safe, controlled use of extract for templating purposes.
		// It turns the keys of the $data array into variables for the template.
		extract( $data, EXTR_SKIP );
		include $template_path;
	}
}

