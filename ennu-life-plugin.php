<?php
/**
 * Plugin Name: ENNU Life Assessments
 * Plugin URI: https://ennulife.com
 * Description: Comprehensive health assessment and biomarker tracking system with AI-powered medical insights and personalized recommendations.
 * Version: 64.2.7
 * Author: ENNU Life Team
 * Author URI: https://ennulife.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ennulifeassessments
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'ENNU_LIFE_VERSION', '64.2.7' );
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

// Biomarker management classes are now loaded in load_dependencies()

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
		private $database          = null;
		private $admin             = null;
		private $form_handler      = null;
		private $shortcodes        = null;
		private $health_goals_ajax = null;

		/**
		 * Initialization flag to prevent multiple initializations
		 */
		private static $initialized = false;

		/**
		 * Hooks setup flag to prevent multiple hook registrations
		 */
		private static $hooks_setup = false;

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
			// Prevent multiple initializations
			if ( self::$initialized ) {
				error_log( 'ENNU Life Plugin: init() called again - skipping to prevent duplicate hook registration.' );
				return;
			}
			self::$initialized = true;
			
			// Load dependencies and textdomain first
			error_log( 'ENNU Life Plugin: Initializing...' );
			$this->load_dependencies();
			// $this->load_textdomain(); // This is called too early. It will be moved to the init hook.

			// Initialize components
			$this->init_components();

			// Setup all hooks
			$this->setup_hooks();
			error_log( 'ENNU Life Plugin: Initialization Complete.' );
		}

		/**
		 * Load all dependencies
		 */
		private function load_dependencies() {
			$includes = array(
				// PHP Configuration Override (load first)
				'php-config-override.php',
				
				// Core Infrastructure
				'class-enhanced-database.php',
				'class-enhanced-admin.php',
				'class-ajax-security.php',
				'class-compatibility-manager.php',
				'class-security-validator.php',
				'class-data-access-control.php',
				'class-template-security.php',
				'class-input-sanitizer.php',
				'class-csrf-protection.php',

				// Biomarker Management System
				'class-biomarker-manager.php',
				'class-lab-import-manager.php',
				'class-smart-recommendation-engine.php',
				'class-csv-biomarker-importer.php',
				'class-user-csv-import-shortcode.php',

				// New Scoring Engine Architecture
				'class-assessment-calculator.php',
				'class-category-score-calculator.php',
				'class-pillar-score-calculator.php',
				'class-health-optimization-calculator.php',
				'class-potential-score-calculator.php',
				'class-new-life-score-calculator.php',
				'class-recommendation-engine.php',
				'class-score-completeness-calculator.php',
				'class-ennu-life-score-calculator.php',
				'class-biomarker-admin.php',
				'class-wp-fusion-integration.php',
				'class-user-manager.php',
				'class-analytics-service.php',
				'class-data-export-service.php',
				'class-performance-monitor.php',
				'class-database-optimizer.php',
				'class-assessment-ajax-handler.php',

				// Four-Engine Scoring Symphony Implementation
				'class-intentionality-engine.php',
				'class-qualitative-engine.php',
				'class-objective-engine.php',
				'class-biomarker-ajax.php',
				'class-health-goals-ajax.php',
				'migrations/health-goals-migration.php',
				// Main Orchestrator and Frontend Classes
				'class-scoring-system.php',
				'class-assessment-shortcodes.php',
				'class-form-handler.php',
				'class-ajax-handler.php',
				'class-shortcode-manager.php',
				'class-comprehensive-assessment-display.php',
				'class-score-cache.php',
				'class-centralized-symptoms-manager.php',

				'class-progressive-data-collector.php',
				'class-smart-question-display.php',
				'class-biomarker-flag-manager.php',
				'class-goal-progression-tracker.php',
				'class-lab-data-landing-system.php',
				'class-trends-visualization-system.php',
				'class-medical-role-manager.php',
				'class-ennu-rest-api.php',

				'class-recommended-range-manager.php',

				'class-role-based-access-control.php',

				'class-enhanced-dashboard-manager.php',
				'class-profile-completeness-tracker.php',
				'class-biomarker-auto-sync.php',

				// Age Management System
				'class-age-management-system.php',
				
						// Memory Optimization System
		'class-memory-optimizer.php',
		
		// Global Fields Processor
		'class-global-fields-processor.php',
		
						// AI Medical Team Reference Ranges System
				'class-ai-medical-team-reference-ranges.php',
				
				// PHASE 1: ENNU Biomarker Range Orchestrator
				'class-biomarker-range-orchestrator.php',
				
				// Biomarker Panel Management System
				'class-biomarker-panels.php',
				
				// AI Target Value Calculator
				'class-biomarker-target-calculator.php',
				
				// Target Weight Calculator
				'class-target-weight-calculator.php',
				
				// HubSpot Integration
				'class-hubspot-bulk-field-creator.php',
				'class-hubspot-cli-commands.php',
				
				// Score Presentation System
				'shortcodes/class-score-presentation-shortcode.php',
			);

			foreach ( $includes as $file ) {
				$file_path = ENNU_LIFE_PLUGIN_PATH . 'includes/' . $file;
				if ( file_exists( $file_path ) ) {
					require_once $file_path;
					error_log( 'ENNU Life Plugin: Loaded dependency: ' . $file );
				} else {
					error_log( 'ENNU Life Plugin: FAILED to load dependency: ' . $file );
				}
			}
		}

		/**
		 * Initialize components
		 */
		private function init_components() {
					// Initialize database - with class existence check
		if ( class_exists( 'ENNU_Life_Enhanced_Database' ) ) {
			$this->database = new ENNU_Life_Enhanced_Database();
		}

			// Initialize admin - with class existence check
			if ( class_exists( 'ENNU_Enhanced_Admin' ) ) {
				$this->admin = new ENNU_Enhanced_Admin();
			}

			// Initialize Health Goals AJAX handlers - NEW
			if ( class_exists( 'ENNU_Health_Goals_Ajax' ) ) {
				$this->health_goals_ajax = new ENNU_Health_Goals_Ajax();
				error_log( 'ENNU Life Plugin: Initialized Health Goals AJAX handlers' );
			} else {
				error_log( 'ENNU Life Plugin: WARNING - ENNU_Health_Goals_Ajax class not found' );
			}

			// Initialize Medical Role Manager - PHASE 10
			if ( class_exists( 'ENNU_Medical_Role_Manager' ) ) {
				ENNU_Medical_Role_Manager::init();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Medical_Role_Manager' );
			} else {
				error_log( 'ENNU Life Plugin: WARNING - ENNU_Medical_Role_Manager class not found' );
			}

			// Initialize Role-Based Access Control - PHASE 11
			if ( class_exists( 'ENNU_Role_Based_Access_Control' ) ) {
				new ENNU_Role_Based_Access_Control();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Role_Based_Access_Control' );
			} else {
				error_log( 'ENNU Life Plugin: WARNING - ENNU_Role_Based_Access_Control class not found' );
			}

			// Initialize Biomarker Flag Manager - PHASE 5
			if ( class_exists( 'ENNU_Biomarker_Flag_Manager' ) ) {
				new ENNU_Biomarker_Flag_Manager();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Biomarker_Flag_Manager' );
			} else {
				error_log( 'ENNU Life Plugin: WARNING - ENNU_Biomarker_Flag_Manager class not found' );
			}

			// Initialize Lab Data Landing System - PHASE 6
			if ( class_exists( 'ENNU_Lab_Data_Landing_System' ) ) {
				new ENNU_Lab_Data_Landing_System();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Lab_Data_Landing_System' );
			} else {
				error_log( 'ENNU Life Plugin: WARNING - ENNU_Lab_Data_Landing_System class not found' );
			}

			// Initialize Trends Visualization System - PHASE 8
			if ( class_exists( 'ENNU_Trends_Visualization_System' ) ) {
				ENNU_Trends_Visualization_System::init();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Trends_Visualization_System' );
			} else {
				error_log( 'ENNU Life Plugin: WARNING - ENNU_Trends_Visualization_System class not found' );
			}

			// Initialize Recommended Range Manager - PHASE 9
			if ( class_exists( 'ENNU_Recommended_Range_Manager' ) ) {
				new ENNU_Recommended_Range_Manager();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Recommended_Range_Manager' );
			} else {
				error_log( 'ENNU Life Plugin: WARNING - ENNU_Recommended_Range_Manager class not found' );
			}

			// Initialize Enhanced Dashboard Manager - PHASE 13
			if ( class_exists( 'ENNU_Enhanced_Dashboard_Manager' ) ) {
				new ENNU_Enhanced_Dashboard_Manager();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Enhanced_Dashboard_Manager' );
			} else {
				error_log( 'ENNU Life Plugin: WARNING - ENNU_Enhanced_Dashboard_Manager class not found' );
			}

					// Initialize Global Fields Processor - PHASE 14
		if ( class_exists( 'ENNU_Global_Fields_Processor' ) ) {
			ENNU_Global_Fields_Processor::init();
			error_log( 'ENNU Life Plugin: Initialized ENNU_Global_Fields_Processor' );
		} else {
			error_log( 'ENNU Life Plugin: WARNING - ENNU_Global_Fields_Processor class not found' );
		}

		// Initialize AI Medical Team Reference Ranges - PHASE 15
		if ( class_exists( 'ENNU_AI_Medical_Team_Reference_Ranges' ) ) {
			new ENNU_AI_Medical_Team_Reference_Ranges();
			error_log( 'ENNU Life Plugin: Initialized ENNU_AI_Medical_Team_Reference_Ranges' );
		} else {
			error_log( 'ENNU Life Plugin: WARNING - ENNU_AI_Medical_Team_Reference_Ranges class not found' );
		}

		// Initialize CSV Biomarker Importer
		if ( class_exists( 'ENNU_CSV_Biomarker_Importer' ) ) {
			new ENNU_CSV_Biomarker_Importer();
			error_log( 'ENNU Life Plugin: Initialized ENNU_CSV_Biomarker_Importer' );
		} else {
			error_log( 'ENNU Life Plugin: WARNING - ENNU_CSV_Biomarker_Importer class not found' );
		}

		// Initialize User CSV Import Shortcode
		if ( class_exists( 'ENNU_User_CSV_Import_Shortcode' ) ) {
			new ENNU_User_CSV_Import_Shortcode();
			error_log( 'ENNU Life Plugin: Initialized ENNU_User_CSV_Import_Shortcode' );
		} else {
			error_log( 'ENNU Life Plugin: WARNING - ENNU_User_CSV_Import_Shortcode class not found' );
		}

		// Initialize HubSpot Bulk Field Creator
		if ( class_exists( 'ENNU_HubSpot_Bulk_Field_Creator' ) ) {
			new ENNU_HubSpot_Bulk_Field_Creator();
			error_log( 'ENNU Life Plugin: Initialized ENNU_HubSpot_Bulk_Field_Creator' );
		} else {
			error_log( 'ENNU Life Plugin: WARNING - ENNU_HubSpot_Bulk_Field_Creator class not found' );
		}

		// Initialize HubSpot CLI Commands
		if ( class_exists( 'ENNU_HubSpot_CLI_Commands' ) ) {
			new ENNU_HubSpot_CLI_Commands();
			error_log( 'ENNU Life Plugin: Initialized ENNU_HubSpot_CLI_Commands' );
		} else {
			error_log( 'ENNU Life Plugin: WARNING - ENNU_HubSpot_CLI_Commands class not found' );
		}

		// Remove orchestrator loading and initialization

			// Initialize Goal Progression Tracker - PHASE 12
			if ( class_exists( 'ENNU_Goal_Progression_Tracker' ) ) {
				new ENNU_Goal_Progression_Tracker();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Goal_Progression_Tracker' );
			} else {
				error_log( 'ENNU Life Plugin: WARNING - ENNU_Goal_Progression_Tracker class not found' );
			}

			// Initialize CSRF Protection - PHASE 0
			if ( class_exists( 'ENNU_CSRF_Protection' ) ) {
				ENNU_CSRF_Protection::get_instance();
				error_log( 'ENNU Life Plugin: Initialized ENNU_CSRF_Protection' );
			} else {
				error_log( 'ENNU Life Plugin: WARNING - ENNU_CSRF_Protection class not found' );
			}

			// Initialize shortcodes on init hook to ensure proper timing
			add_action( 'init', array( $this, 'init_shortcodes' ), 5 ); // Priority 5 to run before shortcode registration
		}

		/**
		 * Initialize shortcodes after WordPress functions are loaded
		 */
		public function init_shortcodes() {
			if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
				$this->shortcodes = new ENNU_Assessment_Shortcodes();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Assessment_Shortcodes on plugins_loaded hook.' );
			} else {
				error_log( 'ENNU Life Plugin: ERROR - ENNU_Assessment_Shortcodes class not found!' );
			}

			// Initialize REST API
			if ( class_exists( 'ENNU_REST_API' ) ) {
				ENNU_REST_API::init();
				error_log( 'ENNU Life Plugin: Initialized ENNU_REST_API.' );
			} else {
				error_log( 'ENNU Life Plugin: ERROR - ENNU_REST_API class not found!' );
			}
		}

		/**
		 * Setup shortcode hooks after shortcodes are initialized
		 */
		public function setup_shortcode_hooks() {
			if ( $this->shortcodes ) {
				error_log( 'ENNU Life Plugin: Setting up shortcode AJAX and frontend hooks.' );
				add_action( 'wp_ajax_ennu_submit_assessment', array( $this->shortcodes, 'handle_assessment_submission' ) );
				add_action( 'wp_ajax_nopriv_ennu_submit_assessment', array( $this->shortcodes, 'handle_assessment_submission' ) );
				add_action( 'wp_ajax_ennu_check_email', array( $this->shortcodes, 'ajax_check_email_exists' ) );
				add_action( 'wp_ajax_nopriv_ennu_check_email', array( $this->shortcodes, 'ajax_check_email_exists' ) );
				add_action( 'wp_ajax_ennu_check_auth_state', array( $this->shortcodes, 'ajax_check_auth_state' ) );
				add_action( 'wp_ajax_nopriv_ennu_check_auth_state', array( $this->shortcodes, 'ajax_check_auth_state' ) );
				add_action( 'wp_enqueue_scripts', array( $this->shortcodes, 'enqueue_chart_scripts' ) );
				add_action( 'wp_enqueue_scripts', array( $this->shortcodes, 'enqueue_results_styles' ) );
			} else {
				error_log( 'ENNU Life Plugin: ERROR - shortcodes object is still null during setup_shortcode_hooks!' );
			}
		}

		/**
		 * Setup hooks
		 */
		private function setup_hooks() {
			// Prevent multiple hook registrations
			if ( self::$hooks_setup ) {
				error_log( 'ENNU Life Plugin: setup_hooks() called again - skipping to prevent duplicate hook registration.' );
				return;
			}
			self::$hooks_setup = true;
			
			// Load textdomain on the correct hook
			add_action( 'init', array( $this, 'load_textdomain' ) );

			// Frontend Asset Hooks
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );

			// Admin Hooks - ALWAYS register these hooks
			if ( $this->admin ) {
				error_log( 'ENNU Life Plugin: Setting up admin hooks...' );

				add_action( 'admin_menu', array( $this->admin, 'add_admin_menu' ) );
				add_action( 'admin_enqueue_scripts', array( $this->admin, 'enqueue_admin_assets' ) );
				add_action( 'show_user_profile', array( $this->admin, 'show_user_assessment_fields' ) );
				add_action( 'edit_user_profile', array( $this->admin, 'show_user_assessment_fields' ) );
				add_action( 'personal_options_update', array( $this->admin, 'save_user_assessment_fields' ) );
				add_action( 'edit_user_profile_update', array( $this->admin, 'save_user_assessment_fields' ) );

				// v57.1.0: Admin AJAX actions for user profile page
				add_action( 'wp_ajax_ennu_recalculate_all_scores', array( $this->admin, 'handle_recalculate_all_scores' ) );
				add_action( 'wp_ajax_ennu_clear_all_assessment_data', array( $this->admin, 'handle_clear_all_assessment_data' ) );
				add_action( 'wp_ajax_ennu_clear_single_assessment_data', array( $this->admin, 'handle_clear_single_assessment_data' ) );

				// v62.5.0: Centralized Symptoms Management AJAX actions
				add_action( 'wp_ajax_ennu_update_centralized_symptoms', array( $this->admin, 'handle_update_centralized_symptoms' ) );
				add_action( 'wp_ajax_ennu_populate_centralized_symptoms', array( $this->admin, 'handle_populate_centralized_symptoms' ) );
				add_action( 'wp_ajax_ennu_clear_symptom_history', array( $this->admin, 'handle_clear_symptom_history' ) );
				add_action( 'wp_ajax_ennu_test_ajax', array( $this->admin, 'handle_test_ajax' ) );

				// v62.7.0: Biomarker Management AJAX actions
				add_action( 'wp_ajax_ennu_import_lab_results', array( $this->admin, 'ajax_import_lab_results' ) );
				add_action( 'wp_ajax_ennu_save_biomarker', array( $this->admin, 'ajax_save_biomarker' ) );
				add_action( 'wp_ajax_ennu_save_biomarkers_ajax', array( $this->admin, 'handle_save_biomarkers_ajax' ) );
				
				// v63.1.0: Symptoms Management AJAX actions
				add_action( 'wp_ajax_ennu_update_symptoms', array( $this->admin, 'ajax_update_symptoms' ) );
				add_action( 'wp_ajax_ennu_populate_symptoms', array( $this->admin, 'ajax_populate_symptoms' ) );
				add_action( 'wp_ajax_ennu_get_symptoms_data', array( $this->admin, 'ajax_get_symptoms_data' ) );

				error_log( 'ENNU Life Plugin: Admin hooks registered successfully' );
			} else {
				error_log( 'ENNU Life Plugin: ERROR - Admin instance is null, cannot register admin hooks!' );
			}

			// Shortcode and AJAX Hooks will be set up after shortcodes are initialized
			add_action( 'init', array( $this, 'setup_shortcode_hooks' ), 10 ); // Priority 10 to run after shortcode init (priority 5)
			
			// Target Weight Calculator Hook
			if ( class_exists( 'ENNU_Target_Weight_Calculator' ) ) {
				add_action( 'ennu_assessment_completed', array( 'ENNU_Target_Weight_Calculator', 'trigger_calculation_on_assessment_completion' ), 20, 2 );
				error_log( 'ENNU Life Plugin: Target Weight Calculator hook registered' );
			} else {
				error_log( 'ENNU Life Plugin: WARNING - ENNU_Target_Weight_Calculator class not found' );
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
					'ennu-welcome',
					'ennu-hair',
					'ennu-ed-treatment',
					'ennu-weight-loss',
					'ennu-health',
					'ennu-skin',
					'ennu-sleep',
					'ennu-hormone',
					'ennu-menopause',
					'ennu-testosterone',
					'ennu-health-optimization',
				);
				foreach ( $assessment_shortcodes as $shortcode ) {
					if ( has_shortcode( $post->post_content, $shortcode ) ) {
						$has_assessment_shortcode = true;
						break;
					}
				}
			}

			if ( $has_assessment_shortcode ) {
				wp_enqueue_style( 'ennu-frontend-forms', ENNU_LIFE_PLUGIN_URL . 'assets/css/ennu-frontend-forms.css', array(), ENNU_LIFE_VERSION );
				wp_enqueue_script( 'ennu-frontend-forms', ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-frontend-forms.js', array(), ENNU_LIFE_VERSION, true );
				wp_localize_script(
					'ennu-frontend-forms',
					'ennu_ajax',
					array(
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'nonce'    => wp_create_nonce( 'ennu_ajax_nonce' ),
					)
				);
			}

			// --- PHASE 3 REFACTOR: Enqueue Dashboard Styles ---
			$has_dashboard_shortcode   = is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ennu-user-dashboard' );
			$has_assessments_shortcode = is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ennu-assessments' );

			// --- v57.0.0 Refactor: Check for details page shortcodes ---
			$has_details_shortcode = false;
			if ( is_a( $post, 'WP_Post' ) ) {
				$content = $post->post_content;
				if ( strpos( $content, 'ennu-' ) !== false && strpos( $content, '-assessment-details' ) !== false ) {
					$has_details_shortcode = true;
				}
			}

			// v57.0.3: UNIFIED ASSET LOADING. Load dashboard assets if ANY relevant shortcode is present.
			if ( $has_dashboard_shortcode || $has_details_shortcode || $has_assessment_shortcode || $has_assessments_shortcode ) {
				// Enqueue Font Awesome for icons
				wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), '5.15.4' );

				// Enqueue theme system styles
				wp_enqueue_style( 'ennu-theme-system', ENNU_LIFE_PLUGIN_URL . 'assets/css/theme-system.css', array(), ENNU_LIFE_VERSION . '.' . time() );
				
				wp_enqueue_style( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/css/user-dashboard.css', array(), ENNU_LIFE_VERSION . '.' . time() );
				
				// Enqueue Chart.js for trends visualization
				wp_enqueue_script( 'chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', array(), '3.9.1', true );
				
				// Enqueue Chart.js time adapter for time-based charts
				wp_enqueue_script( 'chartjs-adapter-date-fns', 'https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@2.0.1/dist/chartjs-adapter-date-fns.bundle.min.js', array( 'chartjs' ), '2.0.1', true );
				
				// Enqueue theme manager script
				wp_enqueue_script( 'ennu-theme-manager', ENNU_LIFE_PLUGIN_URL . 'assets/js/theme-manager.js', array(), ENNU_LIFE_VERSION . '.' . time(), true );
				
				// Enqueue user dashboard script
				wp_enqueue_script( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/js/user-dashboard.js', array( 'jquery', 'chartjs', 'chartjs-adapter-date-fns', 'ennu-theme-manager' ), ENNU_LIFE_VERSION . '.' . time(), true );
			}
			// --- END PHASE 3 REFACTOR ---
		}

		/**
		 * Load textdomain
		 */
		public function load_textdomain() {
			if ( function_exists( 'load_plugin_textdomain' ) ) {
				load_plugin_textdomain( 'ennu-life-assessments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
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

			wp_clear_scheduled_hook( 'ennu_daily_cleanup' );
			wp_clear_scheduled_hook( 'ennu_weekly_reports' );
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

// Add test endpoint for instant workflow
add_action('init', function() {
    if (isset($_GET['test_instant_workflow']) && current_user_can('manage_options')) {
        require_once plugin_dir_path(__FILE__) . 'includes/class-test-instant-workflow.php';
        ENNU_Test_Instant_Workflow::run_test();
        exit;
    }
    
    // Add test endpoint for biomarker flagging
    if (isset($_GET['test_biomarker_flagging']) && current_user_can('manage_options')) {
        require_once plugin_dir_path(__FILE__) . 'test-biomarker-flagging.php';
        exit;
    }
    
    // Add simple flag test endpoint
    if (isset($_GET['test_simple_flag']) && current_user_can('manage_options')) {
        require_once plugin_dir_path(__FILE__) . 'simple-flag-test.php';
        exit;
    }
    
    // Add user meta test endpoint
    if (isset($_GET['test_user_meta']) && current_user_can('manage_options')) {
        require_once plugin_dir_path(__FILE__) . 'test-user-meta.php';
        exit;
    }
    
    // Add symptom flagging test endpoint
    if (isset($_GET['test_symptom_flagging']) && current_user_can('manage_options')) {
        require_once plugin_dir_path(__FILE__) . 'test-symptom-flagging.php';
        exit;
    }
});

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

/*
 * CHANGELOG
 *
 * ## [62.6.0] - 2024-01-15
 *
 * ### Changed
 * - **Simplified Admin Interface**: Replaced multiple management buttons with single "Recalculate Centralized Symptoms" button
 * - **Automatic Symptom Centralization**: Symptoms are now automatically centralized when assessments are completed (both quantitative and qualitative)
 * - **No Admin Intervention Required**: Removed manual populate, update, and clear history buttons - system works automatically
 *
 * ### Added
 * - **Automatic Processing**: Added automatic symptom centralization for qualitative assessments
 * - **Streamlined UI**: Cleaner admin interface with only essential recalculation functionality
 * - **Assessment Completion Hooks**: Added hooks for other systems to respond to assessment completion
 *
 * ## [62.5.0] - 2024-01-15
 *
 * ### Added
 * - **Admin Symptoms Tab**: Added comprehensive centralized symptoms tab to WordPress admin user profile
 * - **Symptom Management**: Added populate, update, and clear history buttons for symptom management
 * - **Enhanced UI**: Professional admin interface with summary statistics, analytics, and organized symptom display
 * - **AJAX Handlers**: Added secure AJAX handlers for symptom management operations
 * - **JavaScript Integration**: Enhanced admin JavaScript with proper event handling and error management
 * - **Debug Tools**: Created comprehensive debugging scripts for troubleshooting admin functionality
 */

