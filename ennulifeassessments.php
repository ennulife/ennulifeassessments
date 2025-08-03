<?php
/**
 * Plugin Name: ENNU Life Assessments
 * Plugin URI: https://enulife.com
 * Description: Comprehensive health assessment and biomarker management system
 * Version: 64.53.6
 * Author: Luis Escobar
 * Author URI: https://ennulife.com
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ennulifeassessments
 * Domain Path: /languages
 *
 * @package ENNU_Life
 * @copyright Copyright (c) 2024, Luis Escobar, https://ennulife.com
 * @license GPL-3.0+
 * @since 1.0.0
 */
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
/**
 * Plugin version
 *
 * @var string
 */
const ENNU_LIFE_VERSION = '64.53.6';
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
		private $unified_admin     = null;
		private $form_handler      = null;
		private $shortcodes        = null;
		private $health_goals_ajax = null;
		private $ajax_handler      = null;
		private $monitoring        = null;
		private $hubspot_oauth     = null;
		
		/**
		 * Service components (refactored architecture)
		 */
		private $biomarker_service = null;
		private $assessment_service = null;
		// private $unified_import_service = null; // DISABLED - User requested removal
		private $configuration_manager = null;
		private $unified_security_service = null;
		private $performance_optimization_service = null;
		private $smart_defaults_generator = null;
		private $goal_progression_tracker = null;
		private $advanced_database_optimizer = null;
		private $advanced_asset_optimizer = null;
		private $code_quality_manager = null;
		private $documentation_manager = null;
		private $comprehensive_testing_framework = null;
		private $deployment_manager = null;
		private $unified_scoring_service = null;
		private $assessment_rendering_service = null;
		private $data_validation_service = null;
		private $progressive_data_collector = null;
		private $unified_api_service = null;
		private $pdf_processor = null;
		private $hipaa_compliance = null;
		
		// Phase 5 UX Improvement Systems
		private $onboarding_system = null;
		private $help_system = null;
		private $smart_defaults = null;
		private $keyboard_shortcuts = null;
		private $auto_save = null;

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
			error_log( 'ENNU Life Plugin: Constructor called' );
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
			// Performance monitoring start
			$start_time = microtime( true );
			$start_memory = memory_get_usage();
			
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
			
			// Performance monitoring end
			$execution_time = microtime( true ) - $start_time;
			$memory_used = memory_get_usage() - $start_memory;
			$peak_memory = memory_get_peak_usage();
			
			error_log( sprintf( 'ENNU Life Plugin: Initialization Complete. Time: %.4fs, Memory: %.2f MB, Peak: %.2f MB', 
				$execution_time, $memory_used / 1024 / 1024, $peak_memory / 1024 / 1024 ) );
			
			// Log warning if performance is poor
			if ( $execution_time > 5.0 ) {
				error_log( 'ENNU Life Plugin: WARNING - Slow initialization detected. Consider optimizing.' );
			}
		}

		/**
		 * Load plugin dependencies
		 */
		private function load_dependencies() {
			// Load new service classes (refactored architecture)
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-biomarker-service.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-assessment-service.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-ajax-handler.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-unified-import-service.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-configuration-manager.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-json-configuration-strategy.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-database-configuration-strategy.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-yaml-configuration-strategy.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-options-configuration-strategy.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-unified-security-service.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-wordpress-security-strategy.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-advanced-security-strategy.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-minimal-security-strategy.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-performance-optimization-service.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-standard-performance-strategy.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-advanced-performance-strategy.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-minimal-performance-strategy.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-smart-defaults-generator.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-goal-progression-tracker.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-advanced-database-optimizer.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-advanced-asset-optimizer.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-code-quality-manager.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-documentation-manager.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-hubspot-field-mapper.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-missing-data-notice.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-comprehensive-testing-framework.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-deployment-manager.php';
			
			// Load scoring engine classes
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-qualitative-engine.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-objective-engine.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-intentionality-engine.php';
			
			// Load core classes
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-assessment-shortcodes.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-ennu-monitoring.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-scoring-system.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-biomarker-flag-manager.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-biomarker-manager.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-recommended-range-manager.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-age-management-system.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-health-optimization-calculator.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-centralized-symptoms-manager.php';
			
			// Load missing classes that are causing warnings
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-health-goals-ajax.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-medical-role-manager.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-role-based-access-control.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-lab-data-landing-system.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-trends-visualization-system.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-enhanced-dashboard-manager.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-global-fields-processor.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-ai-medical-team-reference-ranges.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-csv-biomarker-importer.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-user-csv-import-shortcode.php';
					require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-ajax-handler.php';
		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-ajax-handler.php';
		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-unified-scoring-service.php';
		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-assessment-rendering-service.php';
		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-data-validation-service.php';
		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-progressive-data-collector.php';
		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-unified-api-service.php';
		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-ajax-security.php';
		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-pdf-security.php';
		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-pdf-processor.php';
		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-hipaa-compliance.php';
		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-shortcode-manager.php';
		
				// Load Phase 5 UX Improvement Systems - temporarily disabled to fix translation loading
		// require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-onboarding-system.php';
		// require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-help-system.php';
		// require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-smart-defaults.php';
		// require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-keyboard-shortcuts.php';
		// require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-auto-save.php';
		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-missing-data-notice.php';
		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-next-steps-widget.php';
		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-progress-tracker.php';
		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-actionable-feedback.php';
		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-data-manager.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-hubspot-bulk-field-creator.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-hubspot-cli-commands.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-unified-assessment-system.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-unified-assessment-admin.php';
			
			// Load new HubSpot field creation system classes
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-hubspot-api-manager.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-field-type-mapper.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-database-initializer.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-property-group-manager.php';
			error_log( 'ENNU Life Plugin: Including HubSpot OAuth handler' );
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-hubspot-oauth-handler.php';
			error_log( 'ENNU Life Plugin: HubSpot OAuth handler included successfully' );
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-goal-progression-tracker.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-csrf-protection.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-target-weight-calculator.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-ennu-rest-api.php';
			
			// Load Slack notifications manager
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-slack-notifications.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-slack-admin.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-form-handler.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-enhanced-database.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-database-optimizer.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-enhanced-admin.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-performance-monitor.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-pillar-score-calculator.php';
			require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-assessment-constants.php';
		require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-ui-constants.php';
			
					// Initialize components with performance optimization
		if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
			$this->shortcodes = new ENNU_Assessment_Shortcodes();
		}
		if ( class_exists( 'ENNU_Monitoring' ) ) {
			$this->monitoring = ENNU_Monitoring::get_instance();
		}
			
			// Initialize new service classes
			if ( class_exists( 'ENNU_Biomarker_Service' ) ) {
				$this->biomarker_service = new ENNU_Biomarker_Service();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Biomarker_Service' );
			}
			
			if ( class_exists( 'ENNU_Assessment_Service' ) ) {
				$this->assessment_service = new ENNU_Assessment_Service();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Assessment_Service' );
			}
			
			if ( class_exists( 'ENNU_AJAX_Service_Handler' ) ) {
				$this->ajax_handler = new ENNU_AJAX_Service_Handler();
				$this->ajax_handler->init(); // Initialize AJAX handlers
				error_log( 'ENNU Life Plugin: Initialized ENNU_AJAX_Service_Handler' );
			}
			
			// Initialize unified scoring service
			if ( class_exists( 'ENNU_Unified_Scoring_Service' ) ) {
				$this->unified_scoring_service = ENNU_Unified_Scoring_Service::get_instance();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Unified_Scoring_Service' );
			}
			
			// Initialize assessment rendering service
			if ( class_exists( 'ENNU_Assessment_Rendering_Service' ) ) {
				$this->assessment_rendering_service = ENNU_Assessment_Rendering_Service::get_instance();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Assessment_Rendering_Service' );
			}
			
			// Initialize data validation service
			if ( class_exists( 'ENNU_Data_Validation_Service' ) ) {
				$this->data_validation_service = ENNU_Data_Validation_Service::get_instance();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Data_Validation_Service' );
			}
			
			// Initialize progressive data collector service
			if ( class_exists( 'ENNU_Progressive_Data_Collector' ) ) {
				$this->progressive_data_collector = ENNU_Progressive_Data_Collector::get_instance();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Progressive_Data_Collector' );
			}
			
			// Initialize unified API service
			if ( class_exists( 'ENNU_Unified_API_Service' ) ) {
				$this->unified_api_service = ENNU_Unified_API_Service::get_instance();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Unified_API_Service' );
			}
			
			// UNIFIED IMPORT SERVICE DISABLED - User requested removal
			// if ( class_exists( 'ENNU_Unified_Import_Service' ) ) {
			// 	$this->unified_import_service = new ENNU_Unified_Import_Service();
			// 	$this->unified_import_service->init(); // Initialize unified import service
			// 	error_log( 'ENNU Life Plugin: Initialized ENNU_Unified_Import_Service' );
			// }
			
					if ( class_exists( 'ENNU_Configuration_Manager' ) ) {
			$this->configuration_manager = new ENNU_Configuration_Manager();
			$this->configuration_manager->init_config_directory(); // Initialize configuration directory
			error_log( 'ENNU Life Plugin: Initialized ENNU_Configuration_Manager' );
		}
		
		if ( class_exists( 'ENNU_Unified_Security_Service' ) ) {
			$this->unified_security_service = new ENNU_Unified_Security_Service();
			$this->unified_security_service->init(); // Initialize unified security service
			error_log( 'ENNU Life Plugin: Initialized ENNU_Unified_Security_Service' );
		}
		
					if ( class_exists( 'ENNU_Performance_Optimization_Service' ) ) {
				$this->performance_optimization_service = new ENNU_Performance_Optimization_Service();
				$this->performance_optimization_service->init(); // Initialize performance optimization service
				error_log( 'ENNU Life Plugin: Initialized ENNU_Performance_Optimization_Service' );
			}
			
			// Initialize Smart Defaults Generator
			if ( class_exists( 'ENNU_Smart_Defaults_Generator' ) ) {
				$this->smart_defaults_generator = new ENNU_Smart_Defaults_Generator();
				$this->smart_defaults_generator->init(); // Initialize smart defaults generator
				error_log( 'ENNU Life Plugin: Initialized ENNU_Smart_Defaults_Generator' );
			}
			
			// Initialize Goal Progression Tracker
			if ( class_exists( 'ENNU_Goal_Progression_Tracker' ) ) {
				$this->goal_progression_tracker = new ENNU_Goal_Progression_Tracker();
				$this->goal_progression_tracker->init(); // Initialize goal progression tracker
				error_log( 'ENNU Life Plugin: Initialized ENNU_Goal_Progression_Tracker' );
			}
			
			// Initialize Advanced Database Optimizer
			if ( class_exists( 'ENNU_Advanced_Database_Optimizer' ) ) {
				$this->advanced_database_optimizer = new ENNU_Advanced_Database_Optimizer();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Advanced_Database_Optimizer' );
			}
			
			// Initialize Advanced Asset Optimizer
			if ( class_exists( 'ENNU_Advanced_Asset_Optimizer' ) ) {
				$this->advanced_asset_optimizer = new ENNU_Advanced_Asset_Optimizer();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Advanced_Asset_Optimizer' );
			}
			
			// Initialize Code Quality Manager
			if ( class_exists( 'ENNU_Code_Quality_Manager' ) ) {
				$this->code_quality_manager = new ENNU_Code_Quality_Manager();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Code_Quality_Manager' );
			}
			
			// Initialize Documentation Manager
			if ( class_exists( 'ENNU_Documentation_Manager' ) ) {
				$this->documentation_manager = new ENNU_Documentation_Manager();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Documentation_Manager' );
			}
			
			// Initialize Comprehensive Testing Framework
			if ( class_exists( 'ENNU_Comprehensive_Testing_Framework' ) ) {
				$this->comprehensive_testing_framework = new ENNU_Comprehensive_Testing_Framework();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Comprehensive_Testing_Framework' );
			}
			
			// Initialize Deployment Manager
			if ( class_exists( 'ENNU_Deployment_Manager' ) ) {
				$this->deployment_manager = new ENNU_Deployment_Manager();
				error_log( 'ENNU Life Plugin: Initialized ENNU_Deployment_Manager' );
			}

			// Initialize HubSpot OAuth Handler
			if ( class_exists( 'ENNU_HubSpot_OAuth_Handler' ) ) {
				$this->hubspot_oauth = new ENNU_HubSpot_OAuth_Handler();
				error_log( 'ENNU Life Plugin: Initialized ENNU_HubSpot_OAuth_Handler' );
			}

			// Initialize Phase 5 UX Improvement Systems - temporarily disabled to fix translation loading
			// if ( class_exists( 'ENNU_Onboarding_System' ) ) {
			// 	$this->onboarding_system = new ENNU_Onboarding_System();
			// 	error_log( 'ENNU Life Plugin: Initialized ENNU_Onboarding_System' );
			// }
			
			// if ( class_exists( 'ENNU_Help_System' ) ) {
			// 	$this->help_system = new ENNU_Help_System();
			// 	error_log( 'ENNU Life Plugin: Initialized ENNU_Help_System' );
			// }
			
			// if ( class_exists( 'ENNU_Smart_Defaults' ) ) {
			// 	$this->smart_defaults = new ENNU_Smart_Defaults();
			// 	error_log( 'ENNU Life Plugin: Initialized ENNU_Smart_Defaults' );
			// }
			
			// if ( class_exists( 'ENNU_Keyboard_Shortcuts' ) ) {
			// 	$this->keyboard_shortcuts = new ENNU_Keyboard_Shortcuts();
			// 	error_log( 'ENNU Life Plugin: Initialized ENNU_Keyboard_Shortcuts' );
			// }
			
			// if ( class_exists( 'ENNU_Auto_Save' ) ) {
			// 	$this->auto_save = new ENNU_Auto_Save();
			// 	error_log( 'ENNU Life Plugin: Initialized ENNU_Auto_Save' );
			// }

			// HubSpot OAuth Handler is already initialized above
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
		error_log( 'ENNU Life Plugin: Checking for ENNU_Enhanced_Admin class...' );
		if ( class_exists( 'ENNU_Enhanced_Admin' ) ) {
			error_log( 'ENNU Life Plugin: ENNU_Enhanced_Admin class found, initializing...' );
			$this->admin = new ENNU_Enhanced_Admin();
			error_log( 'ENNU Life Plugin: Initialized ENNU_Enhanced_Admin' );
		} else {
			error_log( 'ENNU Life Plugin: ERROR - ENNU_Enhanced_Admin class not found' );
		}

		// Initialize Unified Assessment Admin - CRITICAL FIX
		error_log( 'ENNU Life Plugin: Checking for ENNU_Unified_Assessment_Admin class...' );
		if ( class_exists( 'ENNU_Unified_Assessment_Admin' ) ) {
			error_log( 'ENNU Life Plugin: ENNU_Unified_Assessment_Admin class found, initializing...' );
			$this->unified_admin = new ENNU_Unified_Assessment_Admin();
			error_log( 'ENNU Life Plugin: Initialized ENNU_Unified_Assessment_Admin' );
		} else {
			error_log( 'ENNU Life Plugin: ERROR - ENNU_Unified_Assessment_Admin class not found' );
		}

			// Initialize Health Goals AJAX handlers - NEW
			if ( class_exists( 'ENNU_Health_Goals_Ajax' ) ) {
				$this->health_goals_ajax = new ENNU_Health_Goals_Ajax();
				error_log( 'ENNU Life Plugin: Initialized Health Goals AJAX handlers' );
			} else {
				error_log( 'ENNU Life Plugin: WARNING - ENNU_Health_Goals_Ajax class not found' );
			}

			// Initialize modern ENNU_AJAX_Service_Handler - CRITICAL FIX
			if ( class_exists( 'ENNU_AJAX_Service_Handler' ) ) {
				$this->ajax_handler = new ENNU_AJAX_Service_Handler();
				error_log( 'ENNU Life Plugin: Initialized ENNU_AJAX_Service_Handler' );
			} elseif ( class_exists( 'ENNU_AJAX_Service_Handler' ) ) {
				$this->ajax_handler = new ENNU_AJAX_Service_Handler();
				error_log( 'ENNU Life Plugin: Initialized ENNU_AJAX_Service_Handler' );
			} else {
				error_log( 'ENNU Life Plugin: ERROR - No AJAX handler class found!' );
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
				ENNU_Lab_Data_Landing_System::init();
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

		// Initialize Slack Notifications Manager
		if ( class_exists( 'ENNU_Slack_Notifications_Manager' ) ) {
			ENNU_Slack_Notifications_Manager::get_instance();
			error_log( 'ENNU Life Plugin: Initialized ENNU_Slack_Notifications_Manager' );
		} else {
			error_log( 'ENNU Life Plugin: WARNING - ENNU_Slack_Notifications_Manager class not found' );
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

		// --- REFACTORING STEP: Activate the modern AJAX Handler ---
		// NOTE: ENNU_AJAX_Handler is already initialized above, so we skip duplicate initialization
		// if ( class_exists( 'ENNU_AJAX_Handler' ) ) {
		// 	new ENNU_AJAX_Handler();
		// 	error_log( 'ENNU Life Plugin: Initialized modern ENNU_AJAX_Handler.' );
		// } else {
		// 	error_log( 'ENNU Life Plugin: WARNING - Modern ENNU_AJAX_Handler class not found.' );
		// }
		// --- END REFACTORING STEP ---

		// --- REFACTORING STEP: Activate the modern Shortcode Manager ---
		if ( class_exists( 'ENNU_Shortcode_Manager' ) ) {
			new ENNU_Shortcode_Manager();
			error_log( 'ENNU Life Plugin: Initialized modern ENNU_Shortcode_Manager.' );
		} else {
			error_log( 'ENNU Life Plugin: WARNING - Modern ENNU_Shortcode_Manager class not found.' );
		}
		// --- END REFACTORING STEP ---

		// --- REFACTORING STEP: Activate the modern Data Manager ---
		if ( class_exists( 'ENNU_Data_Manager' ) ) {
			new ENNU_Data_Manager();
			error_log( 'ENNU Life Plugin: Initialized modern ENNU_Data_Manager.' );
		} else {
			error_log( 'ENNU Life Plugin: WARNING - Modern ENNU_Data_Manager class not found.' );
		}
		// --- END REFACTORING STEP ---

		// Initialize HubSpot Bulk Field Creator
		if ( class_exists( 'ENNU_HubSpot_Bulk_Field_Creator' ) ) {
			new ENNU_HubSpot_Bulk_Field_Creator();
			error_log( 'ENNU Life Plugin: Initialized ENNU_HubSpot_Bulk_Field_Creator' );
		} else {
			error_log( 'ENNU Life Plugin: WARNING - ENNU_HubSpot_Bulk_Field_Creator class not found' );
		}
		
		// Initialize HubSpot API Manager
		if ( class_exists( 'ENNU_HubSpot_API_Manager' ) ) {
			new ENNU_HubSpot_API_Manager();
			error_log( 'ENNU Life Plugin: Initialized ENNU_HubSpot_API_Manager' );
		} else {
			error_log( 'ENNU Life Plugin: WARNING - ENNU_HubSpot_API_Manager class not found' );
		}
		
		// Initialize Database Initializer
		if ( class_exists( 'ENNU_Database_Initializer' ) ) {
			new ENNU_Database_Initializer();
			error_log( 'ENNU Life Plugin: Initialized ENNU_Database_Initializer' );
		} else {
			error_log( 'ENNU Life Plugin: WARNING - ENNU_Database_Initializer class not found' );
		}
		
		// Initialize Property Group Manager
		if ( class_exists( 'ENNU_Property_Group_Manager' ) ) {
			new ENNU_Property_Group_Manager();
			error_log( 'ENNU Life Plugin: Initialized ENNU_Property_Group_Manager' );
		} else {
			error_log( 'ENNU Life Plugin: WARNING - ENNU_Property_Group_Manager class not found' );
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

			// Initialize PDF Processor - LabCorp Integration
			if ( class_exists( 'ENNU_PDF_Processor' ) ) {
				$this->pdf_processor = new ENNU_PDF_Processor();
				error_log( 'ENNU Life Plugin: Initialized ENNU_PDF_Processor' );
			} else {
				error_log( 'ENNU Life Plugin: WARNING - ENNU_PDF_Processor class not found' );
			}

			// Initialize HIPAA Compliance
			if ( class_exists( 'ENNU_HIPAA_Compliance' ) ) {
				$this->hipaa_compliance = new ENNU_HIPAA_Compliance();
				$this->hipaa_compliance->init();
				error_log( 'ENNU Life Plugin: Initialized ENNU_HIPAA_Compliance' );
			} else {
				error_log( 'ENNU Life Plugin: WARNING - ENNU_HIPAA_Compliance class not found' );
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
			// Initialize the modern Shortcode Manager
			if ( class_exists( 'ENNU_Shortcode_Manager' ) ) {
				$this->shortcodes = new ENNU_Shortcode_Manager();
				error_log( 'ENNU Life Plugin: Initialized modern ENNU_Shortcode_Manager' );
			} else {
				error_log( 'ENNU Life Plugin: ERROR - ENNU_Shortcode_Manager class not found!' );
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
			// This method is now deprecated as the new Shortcode Manager and AJAX Handler
			// register their own hooks internally.
			error_log( 'ENNU Life Plugin: setup_shortcode_hooks() is deprecated and no longer registers hooks.' );
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
			
			// Assessment Results Styles Hook
			if ( $this->shortcodes ) {
				add_action( 'wp_enqueue_scripts', array( $this->shortcodes, 'enqueue_results_styles' ) );
			}

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
			// add_action( 'init', array( $this, 'setup_shortcode_hooks' ), 10 ); // This is now deprecated
			
			// Assessment Submission AJAX Handler - DISABLED due to conflict with modern ENNU_AJAX_Handler
			// The modern AJAX handler is now responsible for all assessment submissions
			// if ( isset( $this->shortcodes ) ) {
			// 	add_action( 'wp_ajax_ennu_submit_assessment', array( $this->shortcodes, 'handle_assessment_submission' ) );
			// 	add_action( 'wp_ajax_nopriv_ennu_submit_assessment', array( $this->shortcodes, 'handle_assessment_submission' ) );
			// 	error_log( 'ENNU Life Plugin: Assessment submission AJAX handlers registered' );
			// } else {
			// 	error_log( 'ENNU Life Plugin: ERROR - Shortcodes instance is null, cannot register AJAX handlers!' );
			// }

					// Target Weight Calculator Hook
		if ( class_exists( 'ENNU_Target_Weight_Calculator' ) ) {
			add_action( 'ennu_assessment_completed', array( 'ENNU_Target_Weight_Calculator', 'trigger_calculation_on_assessment_completion' ), 20, 2 );
			error_log( 'ENNU Life Plugin: Target Weight Calculator hook registered' );
		} else {
			error_log( 'ENNU Life Plugin: WARNING - ENNU_Target_Weight_Calculator class not found' );
		}

		// Database Optimizer Initialization
		if ( class_exists( 'ENNU_Database_Optimizer' ) ) {
			$db_optimizer = ENNU_Database_Optimizer::get_instance();
			$db_optimizer->initialize_optimizations();
			error_log( 'ENNU Life Plugin: Database optimizer initialized' );
		} else {
			error_log( 'ENNU Life Plugin: WARNING - ENNU_Database_Optimizer class not found' );
		}

		// Database Cleanup Schedule
		add_action( 'ennu_database_cleanup', array( $this, 'run_database_cleanup' ) );

			// Custom Login Page Logo
			add_action( 'login_enqueue_scripts', array( $this, 'customize_login_logo' ) );
			add_filter( 'login_headerurl', array( $this, 'customize_login_logo_url' ) );
			add_filter( 'login_headertext', array( $this, 'customize_login_logo_title' ) );
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
			$has_biomarkers_shortcode  = is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ennu-biomarkers' );

			// --- v57.0.0 Refactor: Check for details page shortcodes ---
			$has_details_shortcode = false;
			if ( is_a( $post, 'WP_Post' ) ) {
				$content = $post->post_content;
				if ( strpos( $content, 'ennu-' ) !== false && strpos( $content, '-assessment-details' ) !== false ) {
					$has_details_shortcode = true;
				}
			}

			// --- v64.5.21: Check for results page shortcodes ---
			$has_results_shortcode = false;
			if ( is_a( $post, 'WP_Post' ) ) {
				$content = $post->post_content;
				if ( strpos( $content, 'ennu-' ) !== false && strpos( $content, '-results' ) !== false ) {
					$has_results_shortcode = true;
				}
			}

			// v64.5.21: UNIFIED ASSET LOADING. Load dashboard assets if ANY relevant shortcode is present.
			if ( $has_dashboard_shortcode || $has_details_shortcode || $has_results_shortcode || $has_assessment_shortcode || $has_assessments_shortcode || $has_biomarkers_shortcode ) {
				// Enqueue Font Awesome for icons
				wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), '5.15.4' );

				// Enqueue theme system styles
				wp_enqueue_style( 'ennu-theme-system', ENNU_LIFE_PLUGIN_URL . 'assets/css/theme-system.css', array(), ENNU_LIFE_VERSION . '.' . time() );
				
				wp_enqueue_style( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/css/user-dashboard.css', array(), ENNU_LIFE_VERSION . '.' . time() );
				
				// Enqueue My Story and My Insights tab styling
				wp_enqueue_style( 'ennu-my-story-insights', ENNU_LIFE_PLUGIN_URL . 'assets/css/my-story-insights-styling.css', array(), ENNU_LIFE_VERSION . '.' . time() );
				
				// Enqueue Chart.js for trends visualization
				wp_enqueue_script( 'chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js', array(), '3.9.1', true );
				
				// Enqueue Chart.js time adapter for time-based charts
				wp_enqueue_script( 'chartjs-adapter-date-fns', 'https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@2.0.1/dist/chartjs-adapter-date-fns.bundle.min.js', array( 'chartjs' ), '2.0.1', true );
				
				// Enqueue theme manager script
				wp_enqueue_script( 'ennu-theme-manager', ENNU_LIFE_PLUGIN_URL . 'assets/js/theme-manager.js', array(), ENNU_LIFE_VERSION . '.' . time(), true );
				
				// Enqueue user dashboard script
				wp_enqueue_script( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/js/user-dashboard.js', array( 'jquery', 'chartjs', 'chartjs-adapter-date-fns', 'ennu-theme-manager' ), ENNU_LIFE_VERSION . '.' . time(), true );

				// --- FIX: Add nonce localization for user dashboard ---
				if ( $has_dashboard_shortcode ) {
					wp_localize_script(
						'ennu-user-dashboard',
						'ennu_ajax',
						array(
							'ajax_url' => admin_url( 'admin-ajax.php' ),
							'nonce'    => wp_create_nonce( 'ennu_ajax_nonce' ),
						)
					);
				}
			}

			// --- AMELIA BOOKING LIGHT MODE ---
			// Check if Amelia booking shortcode is present on the page
			$has_amelia_booking = false;
			if ( is_a( $post, 'WP_Post' ) ) {
				$content = $post->post_content;
				if ( strpos( $content, 'ameliabooking' ) !== false || strpos( $content, 'amelia' ) !== false ) {
					$has_amelia_booking = true;
				}
			}

			// Enqueue Amelia light mode CSS if booking is present
			if ( $has_amelia_booking ) {
				wp_enqueue_style( 'ennu-amelia-light-mode', ENNU_LIFE_PLUGIN_URL . 'assets/css/amelia-light-mode.css', array(), ENNU_LIFE_VERSION . '.' . time() );
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

		public function get_unified_admin() {
			return $this->unified_admin;
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
		 * @return ENNU_Assessment_Shortcodes The one true shortcode handler instance.
		 */
		public function get_shortcode_handler() {
			return $this->shortcodes;
		}

		/**
		 * Customize the WordPress login page logo
		 */
		public function customize_login_logo() {
			?>
			<style type="text/css">
				#login h1 a {
					background-image: url(<?php echo esc_url( ENNU_LIFE_PLUGIN_URL . 'assets/img/ennu-logo-black.png' ); ?>) !important;
					background-size: contain !important;
					background-repeat: no-repeat !important;
					background-position: center !important;
					height: 80px !important;
					width: 320px !important;
					text-indent: -9999px !important;
					overflow: hidden !important;
					display: block !important;
					margin: 0 auto 25px !important;
				}
			</style>
			<?php
		}

		/**
		 * Customize the login logo URL to point to the site home
		 */
		public function customize_login_logo_url() {
			return home_url();
		}

		/**
		 * Customize the login logo title text
		 */
		public function customize_login_logo_title() {
			return get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'description' );
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

		/**
		 * Run database cleanup tasks
		 */
		public function run_database_cleanup() {
			if ( class_exists( 'ENNU_Database_Optimizer' ) ) {
				$db_optimizer = ENNU_Database_Optimizer::get_instance();
				$db_optimizer->cleanup_expired_transients();
				$db_optimizer->optimize_database_tables();
				error_log( 'ENNU Life Plugin: Scheduled database cleanup completed' );
			}
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
	
	// Add .php extension if not present
	if ( ! str_ends_with( $template_name, '.php' ) ) {
		$template_name .= '.php';
	}
	
	$template_path = ENNU_LIFE_PLUGIN_PATH . 'templates/' . $template_name;

	// Debug: Log template loading attempt
	error_log( 'ENNU Template Loader: Attempting to load template: ' . $template_path );

	if ( file_exists( $template_path ) ) {
		// Debug: Log successful template found
		error_log( 'ENNU Template Loader: Template found, including: ' . $template_path );
		
		// This is a safe, controlled use of extract for templating purposes.
		// It turns the keys of the $data array into variables for the template.
		extract( $data, EXTR_SKIP );
		include $template_path;
		
		// Debug: Log template inclusion completed
		error_log( 'ENNU Template Loader: Template inclusion completed: ' . $template_path );
	} else {
		// Debug output if template not found
		error_log( 'ENNU Template Loader: Template not found at: ' . $template_path );
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

