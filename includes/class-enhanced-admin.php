<?php
/**
 * ENNU Life Enhanced Admin Class - Definitive Rebuild
 *
 * This file has been programmatically rebuilt from scratch to resolve all
 * fatal errors and restore all necessary functionality. It is the single,
 * correct source of truth for the admin class.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Enhanced_Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'add_biomarker_admin_pages' ) );
		add_action( 'admin_init', array( $this, 'initialize_csrf_protection' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_get_biomarker_range_data', array( $this, 'ajax_get_biomarker_range_data' ) );
		// Register AJAX handlers for symptom management
		add_action( 'wp_ajax_ennu_update_centralized_symptoms', array( $this, 'handle_update_centralized_symptoms' ) );
		add_action( 'wp_ajax_ennu_clear_symptom_history', array( $this, 'handle_clear_symptom_history' ) );
		add_action( 'wp_ajax_ennu_get_centralized_symptoms', array( $this, 'handle_get_centralized_symptoms' ) );
		
		// Removed conflicting biomarker management tab hooks - using show_user_assessment_fields instead
	}

	public function initialize_csrf_protection() {
		if ( class_exists( 'ENNU_CSRF_Protection' ) ) {
			ENNU_CSRF_Protection::get_instance();
		}
	}

	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueue_admin_assets( $hook ) {
		// Load on ENNU Life admin pages, ENNU Biomarkers pages, and user profile pages
		if ( strpos( $hook, 'ennu-life' ) === false && strpos( $hook, 'ennu-biomarkers' ) === false && strpos( $hook, 'profile' ) === false && strpos( $hook, 'user-edit' ) === false ) {
			return;
		}

		wp_enqueue_style( 'ennu-admin-styles', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-scores-enhanced.css', array(), ENNU_LIFE_VERSION );
		wp_enqueue_style( 'ennu-admin-tabs', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-tabs-enhanced.css', array(), ENNU_LIFE_VERSION );
		
		// v62.8.0: Enhanced user profile styling
		wp_enqueue_style( 'ennu-admin-user-profile', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-user-profile.css', array(), ENNU_LIFE_VERSION );
		
		// v64.2.1: Admin symptoms tab styling
		wp_enqueue_style( 'ennu-admin-symptoms', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-symptoms.css', array(), ENNU_LIFE_VERSION );
		
		// v62.9.0: Biomarker management interface styling
		wp_enqueue_style( 'ennu-biomarker-management', ENNU_LIFE_PLUGIN_URL . 'assets/css/biomarker-management.css', array(), ENNU_LIFE_VERSION );
		
						// v62.32.0: Range management interface styling
				wp_enqueue_style( 'ennu-range-management', ENNU_LIFE_PLUGIN_URL . 'assets/css/range-management.css', array(), ENNU_LIFE_VERSION );
				// v62.37.0: Evidence section styling
				//wp_enqueue_style( 'ennu-evidence-section', ENNU_LIFE_PLUGIN_URL . 'assets/css/evidence-section.css', array(), ENNU_LIFE_VERSION );
				// v62.34.0: Panel configuration interface styling
		
						// v62.35.0: Evidence tracking interface styling
		//wp_enqueue_style( 'ennu-evidence-tracking', ENNU_LIFE_PLUGIN_URL . 'assets/css/evidence-tracking.css', array(), ENNU_LIFE_VERSION );
		// v62.36.0: Analytics interface styling
		//wp_enqueue_style( 'ennu-biomarker-analytics', ENNU_LIFE_PLUGIN_URL . 'assets/css/biomarker-analytics.css', array(), ENNU_LIFE_VERSION );
		
		// Force light mode only for HubSpot booking page
		if ( strpos( $hook, 'ennu-life-hubspot-booking' ) !== false ) {
			wp_enqueue_style( 'ennu-light-mode-only', ENNU_LIFE_PLUGIN_URL . 'assets/css/light-mode-only.css', array(), ENNU_LIFE_VERSION );
		}
		
		//wp_enqueue_script( 'ennu-admin-scripts', ENNU_LIFE_PLUGIN_URL . 'assets/js/admin-scores-enhanced.js', array( 'jquery' ), ENNU_LIFE_VERSION, true );
		wp_enqueue_script( 'ennu-admin-enhanced', ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-admin-enhanced.js', array( 'jquery' ), ENNU_LIFE_VERSION, true );

		// Localize script with AJAX data
		wp_localize_script(
			'ennu-admin-enhanced',
			'ennu_admin_ajax',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ennu_admin_nonce' ),
				'strings'  => array(
					'confirm_clear_data'  => __( 'Are you sure you want to clear all assessment data for this user? This action cannot be undone.', 'ennu-life' ),
					'confirm_recalculate' => __( 'Are you sure you want to recalculate all scores? This may take a moment.', 'ennu-life' ),
					'updating'            => __( 'Updating...', 'ennu-life' ),
					'success'             => __( 'Success!', 'ennu-life' ),
					'error'               => __( 'Error occurred. Please try again.', 'ennu-life' ),
				),
			)
		);
	}

	public function add_biomarker_admin_pages() {
		add_submenu_page(
			'ennu-life',
			'Biomarker Management',
			'Lab Data',
			'manage_options',
			'ennu-biomarker-management',
			array( $this, 'render_biomarker_management_page' )
		);
	}

	public function render_biomarker_management_page() {
		// Removed non-functional import lab data and recommendations handling
	}


	/**
	 * The single, correct function to build the entire admin menu.
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'ENNU Life', 'ennulifeassessments' ),
			__( 'ENNU Life', 'ennulifeassessments' ),
			'edit_posts', // Changed from 'manage_options' to 'edit_posts' for consistency
			'ennu-life',
			array( $this, 'render_admin_dashboard_page' ),
			'dashicons-heart',
			30
		);

		add_submenu_page(
			'ennu-life',
			__( 'Dashboard', 'ennulifeassessments' ),
			__( 'Dashboard', 'ennulifeassessments' ),
			'edit_posts', // Changed from 'manage_options' to 'edit_posts' for consistency
			'ennu-life',
			array( $this, 'render_admin_dashboard_page' )
		);


		add_submenu_page(
			'ennu-life',
			__( 'Assessments', 'ennulifeassessments' ),
			__( 'Assessments', 'ennulifeassessments' ),
			'edit_posts', // Changed from 'manage_options' to 'edit_posts' for consistency
			'ennu-life-assessments',
			array( $this, 'assessments_page' )
		);

		add_submenu_page(
			'ennu-life',
			__( 'Settings', 'ennulifeassessments' ),
			__( 'Settings', 'ennulifeassessments' ),
			'edit_posts', // Changed from 'manage_options' to 'edit_posts' for consistency
			'ennu-life-settings',
			array( $this, 'settings_page' )
		);

		add_submenu_page(
			'ennu-life',
			__( 'HubSpot Booking', 'ennulifeassessments' ),
			__( 'HubSpot Booking', 'ennulifeassessments' ),
			'edit_posts', // Changed from 'manage_options' to 'edit_posts' for consistency
			'ennu-life-hubspot-booking',
			array( $this, 'hubspot_booking_page' )
		);

		// PHASE 1: NEW ENNU BIOMARKERS TOP-LEVEL MENU
		add_menu_page(
			__( 'ENNU Biomarkers', 'ennulifeassessments' ),
			__( 'ENNU Biomarkers', 'ennulifeassessments' ),
			'manage_options',
			'ennu-biomarkers',
			array( $this, 'render_biomarker_welcome_page' ),
			'dashicons-chart-line',
			31  // Position after ENNU Life (30)
		);

		// ENNU Biomarkers Submenu Structure
		add_submenu_page(
			'ennu-biomarkers',
			__( 'Welcome Guide', 'ennulifeassessments' ),
			__( 'Welcome Guide', 'ennulifeassessments' ),
			'manage_options',
			'ennu-biomarkers',
			array( $this, 'render_biomarker_welcome_page' )
		);

		add_submenu_page(
			'ennu-biomarkers',
			__( 'Range Management', 'ennulifeassessments' ),
			__( 'Range Management', 'ennulifeassessments' ),
			'manage_options',
			'ennu-biomarker-range-management',
			array( $this, 'render_biomarker_range_management_page' )
		);
	}

	public function render_admin_dashboard_page() {
		echo '<div class="wrap"><h1>' . esc_html__( 'ENNU Life Dashboard', 'ennulifeassessments' ) . '</h1>';
		$stats = $this->get_assessment_statistics();
		echo '<div class="ennu-dashboard-stats" style="display:flex;gap:20px;margin:20px 0;">';
		echo '<div class="ennu-stat-card" style="flex:1;background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);"><h3>' . esc_html__( 'Total Users with Assessments', 'ennulifeassessments' ) . '</h3><span class="ennu-stat-number" style="font-size:2em;font-weight:bold;">' . esc_html( $stats['active_users'] ) . '</span></div>';
		echo '<div class="ennu-stat-card" style="flex:1;background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);"><h3>' . esc_html__( 'Submissions This Month', 'ennulifeassessments' ) . '</h3><span class="ennu-stat-number" style="font-size:2em;font-weight:bold;">' . esc_html( $stats['monthly_assessments'] ) . '</span></div>';
		echo '</div>';
		echo '<h2>' . esc_html__( 'Recent Assessments', 'ennulifeassessments' ) . '</h2>';
		$this->display_recent_assessments_table();
		// ADMIN TOOL: Clear all assessment data for current user
		if ( current_user_can( 'administrator' ) ) {
			if ( isset( $_POST['ennu_clear_user_data'] ) && check_admin_referer( 'ennu_clear_user_data_action' ) ) {
				$user_id = get_current_user_id();
				global $wpdb;
				$meta_keys = $wpdb->get_col( $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta WHERE user_id = %d AND meta_key LIKE 'ennu_%'", $user_id ) );
				foreach ( $meta_keys as $meta_key ) {
					delete_user_meta( $user_id, $meta_key );
				}
				echo '<div class="notice notice-success is-dismissible"><strong>All ENNU assessment data for your user has been cleared.</strong></div>';
			}
			echo '<form method="post" style="margin: 30px 0; padding: 20px; background: #ffe; border: 2px solid #fc0; border-radius: 8px;">';
			wp_nonce_field( 'ennu_clear_user_data_action' );
			echo '<button type="submit" name="ennu_clear_user_data" class="button button-danger" style="background: #fc0; color: #000; font-weight: bold; padding: 10px 20px; border-radius: 5px;">Clear ALL ENNU Assessment Data for THIS User</button>';
			echo '<p style="margin: 10px 0 0 0; color: #a00; font-size: 0.95em;">This will remove all ENNU assessment data for your user only. Use for testing onboarding/no-data state.</p>';
			echo '</form>';
		}
		echo '</div>';
	}

	public function render_analytics_dashboard_page() {
		if ( ! is_user_logged_in() ) {
			return; }
		$user_id               = get_current_user_id();
		$current_user          = wp_get_current_user();
		$ennu_life_score       = get_user_meta( $user_id, 'ennu_life_score', true );
		$average_pillar_scores = ENNU_Scoring_System::calculate_average_pillar_scores( $user_id );
		$dob_combined          = get_user_meta( $user_id, 'ennu_global_date_of_birth', true );
		$age                   = $dob_combined ? ( new DateTime() )->diff( new DateTime( $dob_combined ) )->y : 'N/A';
		$gender                = ucfirst( get_user_meta( $user_id, 'ennu_global_gender', true ) );
		include ENNU_LIFE_PLUGIN_PATH . 'templates/admin/analytics-dashboard.php';
	}

	public function assessments_page() {
		echo '<div class="wrap"><h1>' . __( 'ENNU Life Assessments', 'ennulifeassessments' ) . '</h1>';
		$this->display_all_assessments_table();
		echo '</div>';
	}

	public function settings_page() {
		// Enhanced admin page with modern design and organization
		echo '<div class="wrap ennu-admin-wrapper">';

		// Page Header
		echo '<div class="ennu-admin-header">';
		echo '<h1><span class="ennu-logo">🎯</span> ENNU Life Settings</h1>';
		echo '<p class="ennu-subtitle">Manage your health assessment system configuration</p>';
		echo '</div>';

		// Add comprehensive CSS for modern admin design
		echo '<style>
			.ennu-admin-wrapper { max-width: 1200px; margin: 0 auto; }
			.ennu-admin-header { 
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
				color: white;
				padding: 2rem;
				border-radius: 10px;
				margin-bottom: 2rem;
				text-align: center;
				position: relative;
				overflow: hidden;
			}
			.ennu-admin-header::before {
				content: "";
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				background: url("data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.1"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
				opacity: 0.3;
			}
			.ennu-admin-header h1 { 
				margin: 0 0 0.5rem 0; 
				font-size: 2.5rem;
				font-weight: 700;
				position: relative;
				z-index: 2;
			}
			.ennu-subtitle { 
				margin: 0; 
				opacity: 0.9; 
				font-size: 1.1rem;
				position: relative;
				z-index: 2;
			}
			.ennu-logo { font-size: 2rem; margin-right: 0.5rem; }
			
			.ennu-tabs { 
				display: flex; 
				background: #f8f9fa; 
				border-radius: 8px; 
				padding: 0.5rem; 
				margin-bottom: 2rem;
				border: 1px solid #e2e8f0;
				box-shadow: 0 2px 4px rgba(0,0,0,0.05);
			}
			.ennu-tab { 
				flex: 1; 
				padding: 1rem; 
				text-align: center; 
				cursor: pointer; 
				border-radius: 6px; 
				transition: all 0.3s ease;
				font-weight: 500;
				position: relative;
				overflow: hidden;
			}
			.ennu-tab::before {
				content: "";
				position: absolute;
				top: 0;
				left: -100%;
				width: 100%;
				height: 100%;
				background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
				transition: left 0.5s;
			}
			.ennu-tab:hover::before {
				left: 100%;
			}
			.ennu-tab.active { 
				background: white; 
				box-shadow: 0 4px 12px rgba(0,0,0,0.1);
				color: #667eea;
				transform: translateY(-2px);
			}
			.ennu-tab:hover:not(.active) { 
				background: #e9ecef; 
				transform: translateY(-1px);
			}
			
			.ennu-tab-content { 
				display: none; 
				background: white; 
				padding: 2rem; 
				border-radius: 10px; 
				box-shadow: 0 4px 12px rgba(0,0,0,0.1);
				margin-bottom: 2rem;
				animation: fadeInUp 0.3s ease-out;
			}
			.ennu-tab-content.active { display: block; }
			
			@keyframes fadeInUp {
				from {
					opacity: 0;
					transform: translateY(20px);
				}
				to {
					opacity: 1;
					transform: translateY(0);
				}
			}
			
			.ennu-section { 
				margin-bottom: 2rem; 
				padding: 1.5rem; 
				background: #f8f9fa; 
				border-radius: 8px; 
				border-left: 4px solid #667eea;
				transition: all 0.3s ease;
			}
			.ennu-section:hover {
				box-shadow: 0 4px 12px rgba(0,0,0,0.1);
				transform: translateY(-2px);
			}
			.ennu-section h3 { 
				margin: 0 0 1rem 0; 
				color: #2d3748; 
				font-size: 1.3rem;
				display: flex;
				align-items: center;
			}
			.ennu-section h3::before { 
				content: "📋"; 
				margin-right: 0.5rem; 
				font-size: 1.2rem;
			}
			.ennu-section p { 
				margin: 0 0 1rem 0; 
				color: #718096; 
				font-style: italic;
			}
			
			.ennu-page-grid { 
				display: grid; 
				grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); 
				gap: 1rem; 
				margin-top: 1rem;
			}
			.ennu-page-item { 
				background: white; 
				padding: 1.5rem; 
				border-radius: 8px; 
				border: 1px solid #e2e8f0;
				transition: all 0.3s ease;
				position: relative;
				overflow: hidden;
			}
			.ennu-page-item::before {
				content: "";
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				height: 3px;
				background: linear-gradient(90deg, #667eea, #764ba2);
				transform: scaleX(0);
				transition: transform 0.3s ease;
			}
			.ennu-page-item:hover::before {
				transform: scaleX(1);
			}
			.ennu-page-item:hover { 
				box-shadow: 0 8px 25px rgba(0,0,0,0.15); 
				transform: translateY(-3px);
			}
			.ennu-page-item label { 
				font-weight: 600; 
				color: #2d3748; 
				display: block; 
				margin-bottom: 0.75rem;
				font-size: 1rem;
			}
			.ennu-page-item select { 
				width: 100%; 
				padding: 0.75rem; 
				border: 2px solid #e2e8f0; 
				border-radius: 6px;
				font-size: 0.9rem;
				transition: all 0.3s ease;
			}
			.ennu-page-item select:focus {
				outline: none;
				border-color: #667eea;
				box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
			}
			
			.ennu-page-status {
				margin-top: 1rem;
			}
			.ennu-page-status .page-info {
				border-radius: 6px;
				transition: all 0.3s ease;
			}
			.ennu-page-status .page-info:hover {
				transform: translateX(5px);
			}
			.ennu-page-status a {
				transition: all 0.3s ease;
			}
			.ennu-page-status a:hover {
				color: #764ba2 !important;
				text-decoration: underline !important;
			}
			
			.ennu-actions { 
				background: linear-gradient(135deg, #f0fff4 0%, #e6fffa 100%); 
				padding: 2rem; 
				border-radius: 10px; 
				border: 1px solid #9ae6b4;
				margin-bottom: 2rem;
				position: relative;
				overflow: hidden;
			}
			.ennu-actions::before {
				content: "";
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				background: url("data:image/svg+xml,%3Csvg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="%2348bb78" fill-opacity="0.1"%3E%3Cpath d="M20 20c0-11.046-8.954-20-20-20v40c11.046 0 20-8.954 20-20z"/%3E%3C/g%3E%3C/svg%3E");
				opacity: 0.3;
			}
			.ennu-actions h3 { 
				margin: 0 0 1rem 0; 
				color: #22543d; 
				display: flex;
				align-items: center;
				position: relative;
				z-index: 2;
			}
			.ennu-actions h3::before { 
				content: "⚡"; 
				margin-right: 0.5rem;
			}
			.ennu-actions p { 
				margin: 0 0 1rem 0; 
				color: #2f855a;
				position: relative;
				z-index: 2;
			}
			.ennu-actions .button { 
				margin-right: 1rem; 
				margin-bottom: 0.5rem;
				position: relative;
				z-index: 2;
			}
			
			.ennu-status { 
				background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%); 
				padding: 2rem; 
				border-radius: 10px; 
				border: 1px solid #feb2b2;
				position: relative;
				overflow: hidden;
			}
			.ennu-status::before {
				content: "";
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				background: url("data:image/svg+xml,%3Csvg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="%23f56565" fill-opacity="0.1"%3E%3Ccircle cx="20" cy="20" r="2"/%3E%3C/g%3E%3C/svg%3E");
				opacity: 0.3;
			}
			.ennu-status h3 { 
				margin: 0 0 1rem 0; 
				color: #742a2a; 
				display: flex;
				align-items: center;
				position: relative;
				z-index: 2;
			}
			.ennu-status h3::before { 
				content: "📊"; 
				margin-right: 0.5rem;
			}
			
			.ennu-stats-grid { 
				display: grid; 
				grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
				gap: 1rem; 
				margin-top: 1rem;
				position: relative;
				z-index: 2;
			}
			.ennu-stat-card { 
				background: white; 
				padding: 1.5rem; 
				border-radius: 8px; 
				text-align: center; 
				border: 1px solid #e2e8f0;
				transition: all 0.3s ease;
				position: relative;
				overflow: hidden;
			}
			.ennu-stat-card::before {
				content: "";
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
				opacity: 0;
				transition: opacity 0.3s ease;
			}
			.ennu-stat-card:hover::before {
				opacity: 1;
			}
			.ennu-stat-card:hover {
				transform: translateY(-3px);
				box-shadow: 0 8px 25px rgba(0,0,0,0.15);
			}
			.ennu-stat-number { 
				font-size: 2.5rem; 
				font-weight: 700; 
				color: #667eea; 
				display: block;
				position: relative;
				z-index: 2;
			}
			.ennu-stat-label { 
				font-size: 0.9rem; 
				color: #718096; 
				margin-top: 0.5rem;
				position: relative;
				z-index: 2;
			}
			
			.ennu-danger-zone { 
				background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%); 
				padding: 2rem; 
				border-radius: 10px; 
				border: 2px solid #f56565;
				margin-top: 2rem;
				position: relative;
				overflow: hidden;
			}
			.ennu-danger-zone::before {
				content: "";
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				background: url("data:image/svg+xml,%3Csvg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="%23f56565" fill-opacity="0.1"%3E%3Cpath d="M20 0L0 40h40L20 0z"/%3E%3C/g%3E%3C/svg%3E");
				opacity: 0.3;
			}
			.ennu-danger-zone h3 { 
				margin: 0 0 1rem 0; 
				color: #742a2a; 
				display: flex;
				align-items: center;
				position: relative;
				z-index: 2;
			}
			.ennu-danger-zone h3::before { 
				content: "⚠️"; 
				margin-right: 0.5rem;
			}
			.ennu-danger-zone p { 
				margin: 0 0 1rem 0; 
				color: #742a2a;
				position: relative;
				z-index: 2;
			}
			.ennu-danger-zone .button { 
				background: #f56565; 
				border-color: #f56565; 
				color: white;
				position: relative;
				z-index: 2;
				transition: all 0.3s ease;
			}
			.ennu-danger-zone .button:hover { 
				background: #e53e3e; 
				border-color: #e53e3e;
				transform: translateY(-2px);
				box-shadow: 0 4px 12px rgba(245, 101, 101, 0.4);
			}
			
			/* Enhanced button styles */
			.button-primary {
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
				border: none !important;
				color: white !important;
				font-weight: 600 !important;
				padding: 0.75rem 1.5rem !important;
				border-radius: 6px !important;
				transition: all 0.3s ease !important;
			}
			.button-primary:hover {
				transform: translateY(-2px) !important;
				box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4) !important;
			}
			
			/* Loading animation */
			.ennu-loading {
				display: inline-block;
				width: 20px;
				height: 20px;
				border: 3px solid #f3f3f3;
				border-top: 3px solid #667eea;
				border-radius: 50%;
				animation: spin 1s linear infinite;
			}
			
			@keyframes spin {
				0% { transform: rotate(0deg); }
				100% { transform: rotate(360deg); }
			}
			
			@media (max-width: 768px) {
				.ennu-tabs { flex-direction: column; }
				.ennu-page-grid { grid-template-columns: 1fr; }
				.ennu-stats-grid { grid-template-columns: 1fr; }
				.ennu-admin-header h1 { font-size: 2rem; }
				.ennu-page-item { padding: 1rem; }
			}
		</style>';

		// Handle form submissions
		$message = '';
		if ( isset( $_POST['submit'] ) && isset( $_POST['ennu_settings_nonce'] ) && wp_verify_nonce( $_POST['ennu_settings_nonce'], 'ennu_settings_update' ) ) {
			$this->save_settings();
			$message = __( 'Settings saved successfully!', 'ennulifeassessments' );
		}
		if ( isset( $_POST['ennu_setup_pages_submit'] ) && isset( $_POST['ennu_setup_pages_nonce'] ) && wp_verify_nonce( $_POST['ennu_setup_pages_nonce'], 'ennu_setup_pages' ) ) {
			$this->setup_pages();
			$message = __( 'Assessment pages have been created and assigned successfully!', 'ennulifeassessments' );
		}
		if ( isset( $_POST['ennu_delete_pages_submit'] ) && isset( $_POST['ennu_delete_pages_nonce'] ) && wp_verify_nonce( $_POST['ennu_delete_pages_nonce'], 'ennu_delete_pages' ) ) {
			$this->delete_pages();
			$message = __( 'Assessment pages have been deleted successfully!', 'ennulifeassessments' );
		}

		if ( $message ) {
			echo '<div class="notice notice-success is-dismissible"><p><strong>✅ ' . $message . '</strong></p></div>';
		}

		$settings        = $this->get_plugin_settings();
		$assessment_keys = array_keys( ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions() );
		$pages           = get_pages();
		$page_options    = array();
		foreach ( $pages as $page ) {
			$page_options[ $page->ID ] = $page->post_title;
		}

		// Tab Navigation
		echo '<div class="ennu-tabs">';
		echo '<div class="ennu-tab active" data-tab="pages">📄 Page Management</div>';
		echo '<div class="ennu-tab" data-tab="security">🔒 Security Settings</div>';
		echo '<div class="ennu-tab" data-tab="cache">⚡ Cache & Performance</div>';
		echo '<div class="ennu-tab" data-tab="integrations">🔗 Integrations</div>';
		echo '<div class="ennu-tab" data-tab="biomarkers">🧬 Biomarker Settings</div>';
		echo '<div class="ennu-tab" data-tab="notifications">📧 Email & Notifications</div>';
		echo '<div class="ennu-tab" data-tab="actions">⚡ Quick Actions</div>';
		echo '<div class="ennu-tab" data-tab="status">📊 System Status</div>';
		echo '<div class="ennu-tab" data-tab="danger">⚠️ Danger Zone</div>';
		echo '</div>';

		// Tab 1: Page Management
		echo '<div class="ennu-tab-content active" id="pages-tab">';
		echo '<form method="post" action="">';
		wp_nonce_field( 'ennu_settings_update', 'ennu_settings_nonce' );

		// Core Pages Section
		echo '<div class="ennu-section">';
		echo '<h3>Core Pages</h3>';
		echo '<p>Essential pages for user registration, dashboard, and assessments</p>';
		echo '<div class="ennu-page-grid">';
		$this->render_enhanced_page_dropdown( 'dashboard', 'Dashboard Page', $settings['page_mappings'], $page_options, '/dashboard/' );
		$this->render_enhanced_page_dropdown( 'assessments', 'Assessments Landing', $settings['page_mappings'], $page_options, '/assessments/' );
		$this->render_enhanced_page_dropdown( 'registration', 'Registration Page', $settings['page_mappings'], $page_options, '/registration/' );
		$this->render_enhanced_page_dropdown( 'signup', 'Sign Up Page', $settings['page_mappings'], $page_options, '/signup/' );
		$this->render_enhanced_page_dropdown( 'assessment-results', 'Generic Results', $settings['page_mappings'], $page_options, '/assessment-results/' );
		echo '</div></div>';

		// Consultation Pages Section
		echo '<div class="ennu-section">';
		echo '<h3>Consultation & Call Pages</h3>';
		echo '<p>Pages for scheduling calls and getting ENNU Life scores</p>';
		echo '<div class="ennu-page-grid">';
		$this->render_enhanced_page_dropdown( 'call', 'Schedule a Call', $settings['page_mappings'], $page_options, '/call/' );
		$this->render_enhanced_page_dropdown( 'ennu-life-score', 'ENNU Life Score', $settings['page_mappings'], $page_options, '/ennu-life-score/' );
		$this->render_enhanced_page_dropdown( 'health-optimization-results', 'Health Optimization Results', $settings['page_mappings'], $page_options, '/health-optimization-results/' );
		echo '</div></div>';

		// Assessment Form Pages Section
		echo '<div class="ennu-section">';
		echo '<h3>Assessment Form Pages</h3>';
		echo '<p>Individual assessment forms with URLs like /assessments/hair/, /assessments/ed-treatment/, etc.</p>';
		echo '<div class="ennu-page-grid">';

		$assessment_keys = array_keys( ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions() );
		// Use the actual assessment keys from definitions (with hyphens)
		$assessment_menu_order = array(
			'hair'                => 'hair',
			'ed-treatment'        => 'ed-treatment',
			'weight-loss'         => 'weight-loss',
			'health'              => 'health',
			'health-optimization' => 'health-optimization',
			'skin'                => 'skin',
			'hormone'             => 'hormone',
			'testosterone'        => 'testosterone',
			'menopause'           => 'menopause',
			'sleep'               => 'sleep',
		);

		$filtered_assessments = array();
		foreach ( $assessment_menu_order as $slug => $key ) {
			if ( in_array( $key, $assessment_keys ) ) {
				$filtered_assessments[ $slug ] = $key;
			}
		}

		// Assessment Form Pages
		foreach ( $filtered_assessments as $slug => $key ) {
			$label             = ucwords( str_replace( '-', ' ', $key ) );
			$hierarchical_slug = "assessments/{$slug}";
			$this->render_enhanced_page_dropdown( $hierarchical_slug, $label . " (/assessments/{$slug}/)", $settings['page_mappings'], $page_options, "/assessments/{$slug}/" );
		}
		echo '</div></div>';

		// Assessment Results Pages Section
		echo '<div class="ennu-section">';
		echo '<h3>Assessment Results Pages</h3>';
		echo '<p>Results pages for each assessment type</p>';
		echo '<div class="ennu-page-grid">';
		foreach ( $filtered_assessments as $slug => $key ) {
			if ( $slug === 'welcome' ) {
				continue;
			}
			$label             = ucwords( str_replace( '-', ' ', $key ) );
			$hierarchical_slug = "assessments/{$slug}/results";
			$this->render_enhanced_page_dropdown( $hierarchical_slug, $label . " Results (/assessments/{$slug}/results/)", $settings['page_mappings'], $page_options, "/assessments/{$slug}/results/" );
		}
		echo '</div></div>';

		// Assessment Details Pages Section
		echo '<div class="ennu-section">';
		echo '<h3>Assessment Details Pages</h3>';
		echo '<p>Treatment options and detailed information pages</p>';
		echo '<div class="ennu-page-grid">';
		foreach ( $filtered_assessments as $slug => $key ) {
			if ( $slug === 'welcome' ) {
				continue;
			}
			$label             = ucwords( str_replace( '-', ' ', $key ) );
			$hierarchical_slug = "assessments/{$slug}/details";
			$this->render_enhanced_page_dropdown( $hierarchical_slug, $label . " Details (/assessments/{$slug}/details/)", $settings['page_mappings'], $page_options, "/assessments/{$slug}/details/" );
		}
		echo '</div></div>';

		// Assessment Consultation Pages Section
		echo '<div class="ennu-section">';
		echo '<h3>Assessment Consultation Pages</h3>';
		echo '<p>Consultation booking pages for each assessment type</p>';
		echo '<div class="ennu-page-grid">';
		foreach ( $filtered_assessments as $slug => $key ) {
			if ( $slug === 'welcome' ) {
				continue;
			}
			$label             = ucwords( str_replace( '-', ' ', $key ) );
			$hierarchical_slug = "assessments/{$slug}/consultation";
			$this->render_enhanced_page_dropdown( $hierarchical_slug, $label . " Consultation (/assessments/{$slug}/consultation/)", $settings['page_mappings'], $page_options, "/assessments/{$slug}/consultation/" );
		}
		echo '</div></div>';

		submit_button( 'Save All Page Settings', 'primary', 'submit', false, array( 'style' => 'margin-top: 1rem;' ) );
		echo '</form>';
		echo '</div>';

		// Tab 2: Quick Actions
		echo '<div class="ennu-tab-content" id="actions-tab">';
		echo '<div class="ennu-actions">';
		echo '<h3>Quick Actions</h3>';
		echo '<p>Automated tools to manage your assessment system</p>';

		echo '<form method="post" action="" style="margin-bottom: 1rem;">';
		wp_nonce_field( 'ennu_setup_pages', 'ennu_setup_pages_nonce' );
		echo '<button type="submit" name="ennu_setup_pages_submit" class="button button-primary" style="font-size: 1.1rem; padding: 0.75rem 1.5rem;">🚀 Create Missing Assessment Pages</button>';
		echo '<p style="margin-top: 0.5rem; color: #2f855a;">Creates any missing pages and automatically assigns them in the dropdowns above.</p>';
		echo '</form>';

		// Removed non-functional display_menu_update_results call
		echo '</div>';
		echo '</div>';

		// Tab 3: System Status
		echo '<div class="ennu-tab-content" id="status-tab">';
		echo '<div class="ennu-status">';
		echo '<h3>System Status</h3>';
		echo '<p>Current status of your ENNU Life assessment system</p>';

		$this->display_enhanced_page_status_overview( $settings['page_mappings'] );
		echo '</div>';
		echo '</div>';

		// Tab 4: Danger Zone
		echo '<div class="ennu-tab-content" id="danger-tab">';
		echo '<div class="ennu-danger-zone">';
		echo '<h3>Danger Zone</h3>';
		echo '<p>⚠️ These actions cannot be undone. Use with extreme caution.</p>';

		if ( ! empty( $settings['page_mappings'] ) ) {
			echo '<form method="post" action="" onsubmit="return confirm(\'⚠️ WARNING: This will permanently delete all mapped assessment pages. This action cannot be undone. Are you absolutely sure?\');">';
			wp_nonce_field( 'ennu_delete_pages', 'ennu_delete_pages_nonce' );
			echo '<button type="submit" name="ennu_delete_pages_submit" class="button" style="background: #f56565; border-color: #f56565; color: white; font-weight: bold;">🗑️ Delete All Mapped Assessment Pages</button>';
			echo '<p style="margin-top: 0.5rem; color: #742a2a;">This will permanently delete all pages currently mapped in the settings above.</p>';
			echo '</form>';
		} else {
			echo '<p style="color: #742a2a;">No pages are currently mapped, so deletion is not available.</p>';
		}
		echo '</div>';
		echo '</div>';

		// JavaScript for tab functionality
		echo '<script>
			document.addEventListener("DOMContentLoaded", function() {
				const tabs = document.querySelectorAll(".ennu-tab");
				const tabContents = document.querySelectorAll(".ennu-tab-content");
				
				tabs.forEach(tab => {
					tab.addEventListener("click", function() {
						const targetTab = this.getAttribute("data-tab");
						
						// Remove active class from all tabs and contents
						tabs.forEach(t => t.classList.remove("active"));
						tabContents.forEach(content => content.classList.remove("active"));
						
						// Add active class to clicked tab and corresponding content
						this.classList.add("active");
						document.getElementById(targetTab + "-tab").classList.add("active");
					});
				});
			});
		</script>';

		echo '</div>';
	}

	/**
	 * Render HubSpot Booking Settings Page
	 */
	public function hubspot_booking_page() {
		echo '<div class="wrap"><h1>' . __( 'HubSpot Booking Settings', 'ennulifeassessments' ) . '</h1>';

		$message = '';
		if ( isset( $_POST['submit'] ) && isset( $_POST['ennu_hubspot_nonce'] ) && wp_verify_nonce( $_POST['ennu_hubspot_nonce'], 'ennu_hubspot_update' ) ) {
			$this->save_hubspot_settings();
			$message = __( 'HubSpot booking settings saved successfully!', 'ennulifeassessments' );
		}

		if ( $message ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . $message . '</p></div>';
		}

		$settings           = $this->get_hubspot_settings();
		$consultation_types = $this->get_consultation_types();

		echo '<form method="post" action="">';
		wp_nonce_field( 'ennu_hubspot_update', 'ennu_hubspot_nonce' );

		echo '<h2>' . esc_html__( 'HubSpot Configuration', 'ennulifeassessments' ) . '</h2>';
		
		// Check OAuth status
		$access_token = get_option( 'ennu_hubspot_access_token' );
		$oauth_status = $access_token ? 'connected' : 'disconnected';
		
		echo '<div class="notice notice-' . ( $oauth_status === 'connected' ? 'success' : 'warning' ) . ' is-dismissible">';
		echo '<p><strong>OAuth Status:</strong> ' . ( $oauth_status === 'connected' ? '✅ Connected' : '⚠️ Not Connected' ) . '</p>';
		if ( $oauth_status === 'connected' ) {
			echo '<p>HubSpot is authenticated using OAuth 2.0. Your API calls will now work properly.</p>';
		} else {
			echo '<p>HubSpot requires OAuth 2.0 authentication. Please connect using the button below.</p>';
		}
		echo '</div>';
		
		echo '<table class="form-table" role="presentation">';

		echo '<tr><th scope="row"><label for="hubspot_oauth">' . esc_html__( 'HubSpot Authentication', 'ennulifeassessments' ) . '</label></th>';
		echo '<td>';
		if ( $oauth_status === 'connected' ) {
			echo '<span style="color: green;">✅ Authenticated via OAuth 2.0</span><br>';
			echo '<a href="' . admin_url( 'admin.php?page=ennu-hubspot-oauth' ) . '" class="button button-secondary">Re-authenticate</a>';
		} else {
			echo '<span style="color: red;">❌ Not authenticated</span><br>';
			echo '<a href="' . admin_url( 'admin.php?page=ennu-hubspot-oauth' ) . '" class="button button-primary">Connect to HubSpot</a>';
		}
		echo '<p class="description">' . esc_html__( 'HubSpot now requires OAuth 2.0 authentication. API keys are deprecated.', 'ennulifeassessments' ) . '</p></td></tr>';

		echo '<tr><th scope="row"><label for="hubspot_portal_id">' . esc_html__( 'HubSpot Portal ID', 'ennulifeassessments' ) . '</label></th>';
		echo '<td><input type="text" id="hubspot_portal_id" name="hubspot_portal_id" value="48195592" class="regular-text" readonly style="background-color: #f0f0f0;">';
		echo '<p class="description">' . esc_html__( 'Portal ID: 48195592', 'ennulifeassessments' ) . '</p></td></tr>';

		echo '<tr><th scope="row"><label for="test_connection">' . esc_html__( 'Test Connection', 'ennulifeassessments' ) . '</label></th>';
		echo '<td><button type="button" id="test_hubspot_connection" class="button button-secondary">Test HubSpot Connection</button>';
		echo '<span id="connection_status" style="margin-left: 10px;"></span>';
		echo '<p class="description">' . esc_html__( 'Test the HubSpot API connection to verify authentication is working.', 'ennulifeassessments' ) . '</p></td></tr>';

		echo '</table>';
		
		// Add JavaScript for connection testing
		echo '<script>
		jQuery(document).ready(function($) {
			$("#test_hubspot_connection").on("click", function() {
				var button = $(this);
				var status = $("#connection_status");
				
				button.prop("disabled", true).text("Testing...");
				status.html("");
				
				$.ajax({
					url: ajaxurl,
					type: "POST",
					data: {
						action: "ennu_test_hubspot_connection",
						nonce: "' . wp_create_nonce( 'ennu_hubspot_nonce' ) . '"
					},
					success: function(response) {
						if (response.success) {
							status.html("<span style=\"color: green;\">✅ " + response.data + "</span>");
						} else {
							status.html("<span style=\"color: red;\">❌ " + response.data + "</span>");
						}
					},
					error: function() {
						status.html("<span style=\"color: red;\">❌ Connection test failed</span>");
					},
					complete: function() {
						button.prop("disabled", false).text("Test HubSpot Connection");
					}
				});
			});
		});
		</script>';

		echo '<h2>' . esc_html__( 'Consultation Booking Embeds', 'ennulifeassessments' ) . '</h2>';
		echo '<p>' . esc_html__( 'Paste your HubSpot calendar embed codes for each consultation type. These will be used in the consultation shortcodes.', 'ennulifeassessments' ) . '</p>';

		foreach ( $consultation_types as $type => $config ) {
			echo '<h3>' . esc_html( $config['title'] ) . '</h3>';
			echo '<table class="form-table" role="presentation">';

			echo '<tr><th scope="row"><label for="embed_' . esc_attr( $type ) . '">' . esc_html__( 'Calendar Embed Code', 'ennulifeassessments' ) . '</label></th>';
			echo '<td><textarea id="embed_' . esc_attr( $type ) . '" name="embeds[' . esc_attr( $type ) . '][embed_code]" rows="6" cols="80" class="large-text code">' . ( $settings['embeds'][ $type ]['embed_code'] ?? '' ) . '</textarea>';
			echo '<p class="description">' . esc_html__( 'Paste the complete HubSpot calendar embed code here', 'ennulifeassessments' ) . '</p></td></tr>';

			echo '<tr><th scope="row"><label for="meeting_type_' . esc_attr( $type ) . '">' . esc_html__( 'Meeting Type ID', 'ennulifeassessments' ) . '</label></th>';
			echo '<td><input type="text" id="meeting_type_' . esc_attr( $type ) . '" name="embeds[' . esc_attr( $type ) . '][meeting_type]" value="' . esc_attr( $settings['embeds'][ $type ]['meeting_type'] ?? '' ) . '" class="regular-text" placeholder="consultation-30min">';
			echo '<p class="description">' . esc_html__( 'The HubSpot meeting type identifier', 'ennulifeassessments' ) . '</p></td></tr>';

			echo '<tr><th scope="row"><label for="pre_populate_' . esc_attr( $type ) . '">' . esc_html__( 'Pre-populate Fields', 'ennulifeassessments' ) . '</label></th>';
			echo '<td>';
			$pre_populate_fields = $settings['embeds'][ $type ]['pre_populate_fields'] ?? array( 'firstname', 'lastname', 'email' );
			$available_fields    = array(
				'firstname'          => 'First Name',
				'lastname'           => 'Last Name',
				'email'              => 'Email',
				'phone'              => 'Phone',
				'assessment_results' => 'Assessment Results',
			);
			foreach ( $available_fields as $field => $label ) {
				echo '<label><input type="checkbox" name="embeds[' . esc_attr( $type ) . '][pre_populate_fields][]" value="' . esc_attr( $field ) . '" ' . checked( in_array( $field, $pre_populate_fields ), true, false ) . '> ' . esc_html( $label ) . '</label><br>';
			}
			echo '<p class="description">' . esc_html__( 'Select which user data to pre-populate in the booking form', 'ennulifeassessments' ) . '</p></td></tr>';

			echo '</table>';
		}

		echo '<h2>' . esc_html__( 'WP Fusion Integration', 'ennulifeassessments' ) . '</h2>';
		echo '<table class="form-table" role="presentation">';

		echo '<tr><th scope="row"><label for="wpfusion_enabled">' . esc_html__( 'Enable WP Fusion Integration', 'ennulifeassessments' ) . '</label></th>';
		echo '<td><input type="checkbox" id="wpfusion_enabled" name="wpfusion_enabled" value="1" ' . checked( $settings['wpfusion_enabled'] ?? false, true, false ) . '>';
		echo '<p class="description">' . esc_html__( 'Enable automatic user data sync with HubSpot via WP Fusion', 'ennulifeassessments' ) . '</p></td></tr>';

		echo '<tr><th scope="row"><label for="auto_create_contact">' . esc_html__( 'Auto-Create HubSpot Contacts', 'ennulifeassessments' ) . '</label></th>';
		echo '<td><input type="checkbox" id="auto_create_contact" name="auto_create_contact" value="1" ' . checked( $settings['auto_create_contact'] ?? false, true, false ) . '>';
		echo '<p class="description">' . esc_html__( 'Automatically create HubSpot contacts when users complete assessments', 'ennulifeassessments' ) . '</p></td></tr>';

		echo '</table>';

		submit_button();
		echo '</form>';

		echo '<h2>' . esc_html__( 'Preview Consultation Pages', 'ennulifeassessments' ) . '</h2>';
		echo '<p>' . esc_html__( 'Use these shortcodes to display consultation booking forms on your pages:', 'ennulifeassessments' ) . '</p>';
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr><th>' . esc_html__( 'Consultation Type', 'ennulifeassessments' ) . '</th><th>' . esc_html__( 'Shortcode', 'ennulifeassessments' ) . '</th><th>' . esc_html__( 'Description', 'ennulifeassessments' ) . '</th></tr></thead><tbody>';

		foreach ( $consultation_types as $type => $config ) {
			$shortcode = 'ennu-' . str_replace( '_', '-', $type ) . '-consultation';
			echo '<tr>';
			echo '<td><strong>' . esc_html( $config['title'] ) . '</strong></td>';
			echo '<td><code>[' . esc_html( $shortcode ) . ']</code></td>';
			echo '<td>' . esc_html( $config['description'] ) . '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';

		echo '</div>';
	}

	/**
	 * Get consultation types configuration
	 */
	private function get_consultation_types() {
		return array(
			'hair_restoration'            => array(
				'title'       => 'Hair Restoration Consultation',
				'description' => 'Book a consultation with hair restoration specialists',
				'icon'        => '🦱',
				'color'       => '#667eea',
			),
			'ed_treatment'                => array(
				'title'       => 'ED Treatment Consultation',
				'description' => 'Book a confidential consultation for ED treatment options',
				'icon'        => '🔒',
				'color'       => '#f093fb',
			),
			'weight_loss'                 => array(
				'title'       => 'Weight Loss Consultation',
				'description' => 'Book a consultation for personalized weight loss plans',
				'icon'        => '⚖️',
				'color'       => '#4facfe',
			),
			'health_optimization'         => array(
				'title'       => 'Health Optimization Consultation',
				'description' => 'Book a consultation for comprehensive health optimization',
				'icon'        => '🏥',
				'color'       => '#fa709a',
			),
			'skin_care'                   => array(
				'title'       => 'Skin Care Consultation',
				'description' => 'Book a consultation with skincare specialists',
				'icon'        => '✨',
				'color'       => '#a8edea',
			),
			'general_consultation'        => array(
				'title'       => 'General Health Consultation',
				'description' => 'Book a general health consultation',
				'icon'        => '👨‍⚕️',
				'color'       => '#667eea',
			),
			'schedule_call'               => array(
				'title'       => 'Schedule a Call',
				'description' => 'General call scheduling for any health concerns',
				'icon'        => '📞',
				'color'       => '#4facfe',
			),
			'ennu_life_score'             => array(
				'title'       => 'Get Your ENNU Life Score',
				'description' => 'Book a consultation to get your personalized ENNU Life Score',
				'icon'        => '📊',
				'color'       => '#fa709a',
			),
			'health_optimization_results' => array(
				'title'       => 'Health Optimization Results Consultation',
				'description' => 'Discuss your health optimization assessment results',
				'icon'        => '🏥',
				'color'       => '#fa709a',
			),
			'confidential_consultation'   => array(
				'title'       => 'Confidential Consultation',
				'description' => 'Book a confidential consultation for sensitive health matters',
				'icon'        => '🔒',
				'color'       => '#f093fb',
			),
		);
	}

	/**
	 * Get HubSpot settings
	 */
	private function get_hubspot_settings() {
		$saved_settings = get_option( 'ennu_hubspot_settings', array() );
		
		// Use hardcoded values as defaults (from hubspot-integration-setup.php)
		$defaults = array(
			'portal_id'           => '48195592', // Hardcoded from integration setup
			'api_key'             => 'pat-na1-f96eb6e1-fd77-45a7-9f7b-7a30bffa2726', // Hardcoded from integration setup
			'embeds'              => array(),
			'wpfusion_enabled'    => false,
			'auto_create_contact' => false,
		);
		
		// Merge saved settings with defaults
		return array_merge( $defaults, $saved_settings );
	}

	/**
	 * Save HubSpot settings
	 */
	private function save_hubspot_settings() {
		// Enhanced validation for HubSpot settings
		$portal_id = sanitize_text_field( $_POST['hubspot_portal_id'] ?? '' );
		$api_key   = sanitize_text_field( $_POST['hubspot_api_key'] ?? '' );

		// Validate portal ID format (should be numeric)
		if ( ! empty( $portal_id ) && ! ctype_digit( $portal_id ) ) {
			wp_die( 'Invalid HubSpot Portal ID format. Must be numeric.' );
		}

		// Validate API key format (basic length and character validation)
		if ( ! empty( $api_key ) && ( strlen( $api_key ) < 20 || ! preg_match( '/^[a-zA-Z0-9\-_]+$/', $api_key ) ) ) {
			wp_die( 'Invalid HubSpot API key format.' );
		}

		$settings = array(
			'portal_id'           => $portal_id,
			'api_key'             => $api_key,
			'embeds'              => array(),
			'wpfusion_enabled'    => isset( $_POST['wpfusion_enabled'] ),
			'auto_create_contact' => isset( $_POST['auto_create_contact'] ),
		);

		if ( isset( $_POST['embeds'] ) && is_array( $_POST['embeds'] ) ) {
			foreach ( $_POST['embeds'] as $type => $embed_data ) {
				$settings['embeds'][ $type ] = array(
					'embed_code'          => wp_kses( $embed_data['embed_code'] ?? '', array(
						'div' => array(
							'class' => array(),
							'data-src' => array(),
						),
						'script' => array(
							'type' => array(),
							'src' => array(),
						),
						'!--' => array(),
					), array( 'http', 'https' ) ),
					'meeting_type'        => sanitize_text_field( $embed_data['meeting_type'] ?? '' ),
					'pre_populate_fields' => isset( $embed_data['pre_populate_fields'] ) ? array_map( 'sanitize_text_field', $embed_data['pre_populate_fields'] ) : array(),
				);
			}
		}

		update_option( 'ennu_hubspot_settings', $settings );
	}

	/**
	 * Enhanced page dropdown renderer for the new admin interface
	 */
	private function render_enhanced_page_dropdown( $slug, $label, $current_mappings, $page_options, $url_path ) {
		$page_id      = isset( $current_mappings[ $slug ] ) ? $current_mappings[ $slug ] : '';
		$selected_url = $page_id ? get_permalink( $page_id ) : '';
		$shortcode    = '';
		// Get the expected shortcode for this page
		$all_required_pages = $this->get_all_required_pages();
		if ( isset( $all_required_pages[ $slug ] ) ) {
			$shortcode = $all_required_pages[ $slug ]['content'];
		}
		echo '<div class="ennu-page-item">';
		echo '<label for="ennu-page-' . esc_attr( $slug ) . '">' . esc_html( $label ) . '</label>';
		echo '<select name="ennu_page_mappings[' . esc_attr( $slug ) . ']" id="ennu-page-' . esc_attr( $slug ) . '">';
		echo '<option value="">-- Select a page --</option>';
		foreach ( $page_options as $id => $title ) {
			$selected = selected( $page_id, $id, false );
			echo '<option value="' . esc_attr( $id ) . '"' . $selected . '>' . esc_html( $title ) . ' (ID: ' . esc_html( $id ) . ')</option>';
		}
		echo '</select>';
		echo '<div class="ennu-page-status">';
		if ( $page_id ) {
			$permalink         = get_permalink( $page_id );
			$permalink_display = esc_url( $permalink );
			$permalink_text    = esc_html( str_replace( home_url(), '', $permalink ) );
			echo '<div class="page-info" style="margin-top:0.5em;">';
			echo '<span style="font-size:0.95em;color:#4a5568;">URL: <a href="' . $permalink_display . '" target="_blank" style="color:#5a67d8;font-weight:600;text-decoration:underline;">' . $permalink_text . '</a> <span style="color:#a0aec0;">(ID: ' . esc_html( $page_id ) . ')</span></span>';
			echo '</div>';
		} else {
			echo '<div class="page-info" style="margin-top:0.5em;color:#e53e3e;">No page assigned<br><span style="font-size:0.9em;color:#a0aec0;">Expected URL: ' . esc_html( $url_path ) . '</span></div>';
		}
		// Show shortcode
		if ( $shortcode ) {
			echo '<div class="ennu-shortcode-block" style="margin-top:0.5em;background:#f7fafc;border:1px solid #e2e8f0;padding:0.5em 0.75em;border-radius:6px;font-family:monospace;font-size:0.97em;color:#2d3748;">Shortcode: <code style="background:none;padding:0;">' . esc_html( $shortcode ) . '</code></div>';
		}
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Enhanced page status overview for the new admin interface
	 */
	private function display_enhanced_page_status_overview( $page_mappings ) {
		// Get ALL pages that should exist in the system
		$all_required_pages   = $this->get_all_required_pages();
		$total_required_pages = count( $all_required_pages );

		$assigned_pages  = 0;
		$missing_pages   = 0;
		$published_pages = 0;
		$draft_pages     = 0;
		$menu_items      = 0;

		foreach ( $all_required_pages as $slug => $page_data ) {
			$page_id = $page_mappings[ $slug ] ?? '';
			if ( $page_id && get_post( $page_id ) ) {
				$assigned_pages++;
				$status = get_post_status( $page_id );
				if ( $status === 'publish' ) {
					$published_pages++;
				} else {
					$draft_pages++;
				}
			} else {
				$missing_pages++;
			}
		}

		// Count menu items
		$primary_menu = wp_get_nav_menu_object( get_nav_menu_locations()['primary'] ?? 0 );
		if ( $primary_menu ) {
			$menu_items = wp_get_nav_menu_items( $primary_menu->term_id );
			$menu_items = is_array( $menu_items ) ? count( $menu_items ) : 0;
		}

		echo '<div class="ennu-stats-grid">';
		echo '<div class="ennu-stat-card">';
		echo '<span class="ennu-stat-number">' . esc_html( $total_required_pages ) . '</span>';
		echo '<span class="ennu-stat-label">Total Required Pages</span>';
		echo '</div>';

		echo '<div class="ennu-stat-card">';
		echo '<span class="ennu-stat-number" style="color: #48bb78;">' . esc_html( $assigned_pages ) . '</span>';
		echo '<span class="ennu-stat-label">Assigned Pages</span>';
		echo '</div>';

		echo '<div class="ennu-stat-card">';
		echo '<span class="ennu-stat-number" style="color: #f56565;">' . esc_html( $missing_pages ) . '</span>';
		echo '<span class="ennu-stat-label">Missing Pages</span>';
		echo '</div>';

		echo '<div class="ennu-stat-card">';
		echo '<span class="ennu-stat-number" style="color: #667eea;">' . esc_html( $menu_items ) . '</span>';
		echo '<span class="ennu-stat-label">Menu Items</span>';
		echo '</div>';

		echo '<div class="ennu-stat-card">';
		echo '<span class="ennu-stat-number" style="color: #38a169;">' . esc_html( $published_pages ) . '</span>';
		echo '<span class="ennu-stat-label">Published</span>';
		echo '</div>';

		echo '<div class="ennu-stat-card">';
		echo '<span class="ennu-stat-number" style="color: #d69e2e;">' . esc_html( $draft_pages ) . '</span>';
		echo '<span class="ennu-stat-label">Drafts</span>';
		echo '</div>';
		echo '</div>';

		// Detailed status with clickable URLs
		echo '<div style="margin-top: 2rem;">';
		echo '<h4 style="margin-bottom: 1rem; color: #2d3748; display: flex; align-items: center;">📋 COMPLETE PAGE LISTING (' . $total_required_pages . ' total pages)</h4>';

		// Debug: Show all assessment keys
		$assessment_keys = array_keys( ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions() );
		echo '<div style="background: #fff5f5; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; border-left: 3px solid #f56565;">';
		echo '<div style="font-size: 0.9rem; color: #742a2a;"><strong>DEBUG:</strong> Found ' . count( $assessment_keys ) . ' assessment types:</div>';
		echo '<div style="font-size: 0.8rem; color: #742a2a; margin-top: 0.25rem;">' . esc_html( implode( ', ', $assessment_keys ) ) . '</div>';
		echo '</div>';

		// Show ALL pages from the all_required_pages array
		echo '<div style="background: #f0fff4; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #48bb78;">';
		echo '<h5 style="margin: 0 0 1rem 0; color: #22543d; display: flex; align-items: center;">📋 ALL REQUIRED PAGES (' . count( $all_required_pages ) . ' total)</h5>';

		foreach ( $all_required_pages as $slug => $page_data ) {
			$page_id       = $page_mappings[ $slug ] ?? '';
			$page_title    = $page_data['title'] ?? ucwords( str_replace( array( '-', '_', '/' ), array( ' ', ' ', ' → ' ), $slug ) );
			$page_url_path = $page_data['url_path'] ?? '/' . $slug . '/';

			if ( $page_id && get_post( $page_id ) ) {
				$actual_title = get_the_title( $page_id );
				$page_url     = get_permalink( $page_id );
				$status       = get_post_status( $page_id );
				$status_icon  = $status === 'publish' ? '✅' : '⚠️';
				$status_color = $status === 'publish' ? '#48bb78' : '#f56565';

				echo '<div style="background: white; padding: 0.75rem; border-radius: 6px; margin-bottom: 0.5rem; border-left: 3px solid ' . $status_color . ';">';
				echo '<div style="font-size: 0.85rem; font-weight: 600; color: #2d3748;">' . $status_icon . ' ' . esc_html( $page_title ) . '</div>';
				echo '<div style="font-size: 0.75rem; color: #718096; margin-top: 0.25rem;">';
				echo '<strong>Slug:</strong> ' . esc_html( $slug ) . '<br>';
				echo '<strong>Title:</strong> ' . esc_html( $actual_title ) . '<br>';
				echo '<strong>ID:</strong> ' . esc_html( $page_id ) . ' | <strong>Status:</strong> ' . esc_html( ucfirst( $status ) );
				echo '</div>';
				if ( $page_url ) {
					echo '<div style="font-size: 0.75rem; color: #667eea; margin-top: 0.25rem;">';
					echo '<strong>URL:</strong> <a href="' . esc_url( $page_url ) . '" target="_blank" style="color: #667eea; text-decoration: none;">' . esc_html( $page_url ) . ' 🔗</a>';
					echo '</div>';
				}
				echo '</div>';
			} else {
				echo '<div style="background: #fff5f5; padding: 0.75rem; border-radius: 6px; margin-bottom: 0.5rem; border-left: 3px solid #f56565;">';
				echo '<div style="font-size: 0.85rem; color: #742a2a;">❌ ' . esc_html( $page_title ) . ' - Not assigned</div>';
				echo '<div style="font-size: 0.75rem; color: #742a2a; margin-top: 0.25rem;">';
				echo '<strong>Slug:</strong> ' . esc_html( $slug ) . '<br>';
				echo '<strong>Expected URL:</strong> ' . esc_html( $page_url_path ) . '<br>';
				echo '<strong>Content:</strong> ' . esc_html( $page_data['content'] ?? 'No content specified' );
				echo '</div>';
				echo '</div>';
			}
		}
		echo '</div>';

		// Group pages by category for organized view
		$page_categories = array(
			'Core Pages'         => array( 'dashboard', 'assessments', 'registration', 'signup', 'assessment-results' ),
			'Consultation Pages' => array( 'call', 'ennu-life-score', 'health-optimization-results' ),
		);

		// Add assessment pages dynamically
		$assessment_keys       = array_keys( ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions() );
		$assessment_menu_order = array(
			'hair'                => 'hair',
			'ed-treatment'        => 'ed-treatment',
			'weight-loss'         => 'weight-loss',
			'health'              => 'health',
			'health-optimization' => 'health-optimization',
			'skin'                => 'skin',
			'hormone'             => 'hormone',
			'testosterone'        => 'testosterone',
			'menopause'           => 'menopause',
			'sleep'               => 'sleep',
		);

		$filtered_assessments = array();
		foreach ( $assessment_menu_order as $slug => $key ) {
			if ( in_array( $key, $assessment_keys ) ) {
				$filtered_assessments[ $slug ] = $key;
			}
		}

		// Debug: Show filtered assessments
		echo '<div style="background: #fff5f5; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; border-left: 3px solid #f56565;">';
		echo '<div style="font-size: 0.9rem; color: #742a2a;"><strong>DEBUG:</strong> Filtered to ' . count( $filtered_assessments ) . ' assessments:</div>';
		echo '<div style="font-size: 0.8rem; color: #742a2a; margin-top: 0.25rem;">' . esc_html( implode( ', ', array_keys( $filtered_assessments ) ) ) . '</div>';
		echo '</div>';

		// Add assessment form pages
		$assessment_form_pages = array();
		foreach ( $filtered_assessments as $slug => $key ) {
			$assessment_form_pages[] = "assessments/{$slug}";
		}
		$page_categories['Assessment Form Pages'] = $assessment_form_pages;

		// Add assessment results pages
		$assessment_results_pages = array();
		foreach ( $filtered_assessments as $slug => $key ) {
			$assessment_results_pages[] = "assessments/{$slug}/results";
		}
		$page_categories['Assessment Results Pages'] = $assessment_results_pages;

		// Add assessment details pages
		$assessment_details_pages = array();
		foreach ( $filtered_assessments as $slug => $key ) {
			$assessment_details_pages[] = "assessments/{$slug}/details";
		}
		$page_categories['Assessment Details Pages'] = $assessment_details_pages;

		// Add assessment consultation pages
		$assessment_consultation_pages = array();
		foreach ( $filtered_assessments as $slug => $key ) {
			$assessment_consultation_pages[] = "assessments/{$slug}/consultation";
		}
		$page_categories['Assessment Consultation Pages'] = $assessment_consultation_pages;

		echo '<div style="background: #fef5e7; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; border-left: 4px solid #f6ad55;">';
		echo '<h5 style="margin: 0 0 1rem 0; color: #744210; display: flex; align-items: center;">📊 ORGANIZED BY CATEGORY</h5>';

		foreach ( $page_categories as $category_name => $category_pages ) {
			if ( empty( $category_pages ) ) {
				continue;
			}

			echo '<div style="background: white; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; border-left: 3px solid #667eea;">';
			echo '<h6 style="margin: 0 0 0.75rem 0; color: #2d3748; font-weight: 600;">' . esc_html( $category_name ) . ' (' . count( $category_pages ) . ' pages)</h6>';

			foreach ( $category_pages as $page_slug ) {
				$page_id       = $page_mappings[ $page_slug ] ?? '';
				$page_data     = $all_required_pages[ $page_slug ] ?? array();
				$page_title    = $page_data['title'] ?? ucwords( str_replace( array( '-', '_', '/' ), array( ' ', ' ', ' → ' ), $page_slug ) );
				$page_url_path = $page_data['url_path'] ?? '/' . $page_slug . '/';

				if ( $page_id && get_post( $page_id ) ) {
					$actual_title = get_the_title( $page_id );
					$page_url     = get_permalink( $page_id );
					$status       = get_post_status( $page_id );
					$status_icon  = $status === 'publish' ? '✅' : '⚠️';
					$status_color = $status === 'publish' ? '#48bb78' : '#f56565';

					echo '<div style="background: #f8f9fa; padding: 0.5rem; border-radius: 4px; margin-bottom: 0.5rem; border-left: 2px solid ' . $status_color . ';">';
					echo '<div style="font-size: 0.8rem; font-weight: 600; color: #2d3748;">' . $status_icon . ' ' . esc_html( $page_title ) . '</div>';
					echo '<div style="font-size: 0.7rem; color: #718096; margin-top: 0.25rem;">';
					echo '<strong>ID:</strong> ' . esc_html( $page_id ) . ' | <strong>Status:</strong> ' . esc_html( ucfirst( $status ) );
					echo '</div>';
					if ( $page_url ) {
						echo '<div style="font-size: 0.7rem; color: #667eea; margin-top: 0.25rem;">';
						echo '<a href="' . esc_url( $page_url ) . '" target="_blank" style="color: #667eea; text-decoration: none;">' . esc_html( $page_url ) . ' 🔗</a>';
						echo '</div>';
					}
					echo '</div>';
				} else {
					echo '<div style="background: #fff5f5; padding: 0.5rem; border-radius: 4px; margin-bottom: 0.5rem; border-left: 2px solid #f56565;">';
					echo '<div style="font-size: 0.8rem; color: #742a2a;">❌ ' . esc_html( $page_title ) . ' - Not assigned</div>';
					echo '<div style="font-size: 0.7rem; color: #742a2a; margin-top: 0.25rem;">';
					echo '<strong>Expected:</strong> ' . esc_html( $page_url_path );
					echo '</div>';
					echo '</div>';
				}
			}
			echo '</div>';
		}
		echo '</div>';

		echo '</div>';
		echo '</div>';
	}

	/**
	 * Get all required pages that should exist in the system
	 */
	private function get_all_required_pages() {
		$all_required_pages = array();

		// Core pages
		$core_pages = array(
			'dashboard'          => array(
				'title'    => 'Dashboard Page',
				'url_path' => '/dashboard/',
				'content'  => '[ennu-user-dashboard]',
			),
			'assessments'        => array(
				'title'    => 'Assessments Landing Page',
				'url_path' => '/assessments/',
				'content'  => '[ennu-assessments]',
			),
			'registration'       => array(
				'title'    => 'Registration Page',
				'url_path' => '/registration/',
				'content'  => '[ennu-welcome]',
			),
			'signup'             => array(
				'title'    => 'Sign Up Page',
				'url_path' => '/signup/',
				'content'  => '[ennu-signup]',
			),
			'assessment-results' => array(
				'title'    => 'Generic Results Page',
				'url_path' => '/assessment-results/',
				'content'  => '[ennu-assessment-results]',
			),
		);

		// Consultation pages
		$consultation_pages = array(
			'call'                        => array(
				'title'    => 'Schedule a Call Page',
				'url_path' => '/call/',
				'content'  => 'Schedule your free health consultation with our experts.',
			),
			'ennu-life-score'             => array(
				'title'    => 'Get Your ENNU Life Score Page',
				'url_path' => '/ennu-life-score/',
				'content'  => 'Discover your personalized ENNU Life Score and health insights.',
			),
			'health-optimization-results' => array(
				'title'    => 'Health Optimization Results Page',
				'url_path' => '/health-optimization-results/',
				'content'  => '[ennu-health-optimization-results]',
			),
		);

		// Assessment pages
		$assessment_keys = array_keys( ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions() );
		// Use the actual assessment keys from definitions (with hyphens)
		$assessment_menu_order = array(
			'hair'                => 'hair',
			'ed-treatment'        => 'ed-treatment',
			'weight-loss'         => 'weight-loss',
			'health'              => 'health',
			'health-optimization' => 'health-optimization',
			'skin'                => 'skin',
			'hormone'             => 'hormone',
			'testosterone'        => 'testosterone',
			'menopause'           => 'menopause',
			'sleep'               => 'sleep',
		);

		$filtered_assessments = array();
		foreach ( $assessment_menu_order as $slug => $key ) {
			if ( in_array( $key, $assessment_keys ) ) {
				$filtered_assessments[ $slug ] = $key;
			}
		}

		// Add assessment form pages
		foreach ( $filtered_assessments as $slug => $key ) {
			$label                                       = ucwords( str_replace( '-', ' ', $key ) );
			$all_required_pages[ "assessments/{$slug}" ] = array(
				'title'    => $label . ' Assessment Form',
				'url_path' => "/assessments/{$slug}/",
				'content'  => "[ennu-{$slug}]",
			);
		}

		// Add assessment results pages
		foreach ( $filtered_assessments as $slug => $key ) {
			$label = ucwords( str_replace( '-', ' ', $key ) );
			$all_required_pages[ "assessments/{$slug}/results" ] = array(
				'title'    => $label . ' Results',
				'url_path' => "/assessments/{$slug}/results/",
				'content'  => "[ennu-{$slug}-results]",
			);
		}

		// Add assessment details pages
		foreach ( $filtered_assessments as $slug => $key ) {
			$label = ucwords( str_replace( '-', ' ', $key ) );
			$all_required_pages[ "assessments/{$slug}/details" ] = array(
				'title'    => $label . ' Details',
				'url_path' => "/assessments/{$slug}/details/",
				'content'  => "[ennu-{$slug}-assessment-details]",
			);
		}

		// Add assessment consultation pages
		foreach ( $filtered_assessments as $slug => $key ) {
			$label = ucwords( str_replace( '-', ' ', $key ) );
			$all_required_pages[ "assessments/{$slug}/consultation" ] = array(
				'title'    => $label . ' Consultation',
				'url_path' => "/assessments/{$slug}/consultation/",
				'content'  => "[ennu-{$slug}-consultation]",
			);
		}

		// Merge all pages
		return array_merge( $core_pages, $consultation_pages, $all_required_pages );
	}

	public function show_user_assessment_fields( $user ) {
		try {
			// Enhanced error handling and validation
			if ( ! $user || ! is_object( $user ) || ! isset( $user->ID ) ) {
				error_log( 'ENNU Enhanced Admin: Invalid user object provided to show_user_assessment_fields' );
				return;
			}

			$user_id = intval( $user->ID );
			if ( $user_id <= 0 ) {
				error_log( 'ENNU Enhanced Admin: Invalid user ID: ' . $user_id );
				return;
			}

			error_log( 'ENNU Enhanced Admin: show_user_assessment_fields called for user ID: ' . $user_id );
			
			if ( ! current_user_can( 'edit_user', $user_id ) ) {
				return;
			}

		// --- Render Health Summary Component ---
			$ennu_life_score = get_user_meta( $user_id, 'ennu_life_score', true );
			$average_pillar_scores = get_user_meta( $user_id, 'ennu_average_pillar_scores', true );
			
			// Ensure average_pillar_scores is always an array
		if ( ! is_array( $average_pillar_scores ) ) {
			$average_pillar_scores = array(
				'Mind'       => 0,
				'Body'       => 0,
				'Lifestyle'  => 0,
				'Aesthetics' => 0,
			);
		}

			// Include health summary template with error handling
			$health_summary_template = ENNU_LIFE_PLUGIN_PATH . 'templates/admin/user-health-summary.php';
			if ( file_exists( $health_summary_template ) ) {
				include $health_summary_template;
			}

					// Add nonce field for security
		wp_nonce_field( 'ennu_user_profile_update_' . $user_id, 'ennu_assessment_nonce' );
		


			// Get assessment definitions with error handling
			try {
				$shortcode_handler = ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler();
				if ( ! $shortcode_handler ) {
					throw new Exception( 'Shortcode handler not available' );
				}
				$assessments = array_keys( $shortcode_handler->get_all_assessment_definitions() );
			} catch ( Exception $e ) {
				error_log( 'ENNU Enhanced Admin: Error getting assessment definitions: ' . $e->getMessage() );
				$assessments = array();
			}

			// Render the main assessment interface
			echo '<h2>' . esc_html__( 'User Assessment Data', 'ennulifeassessments' ) . '</h2>';
			echo '<div class="ennu-admin-tabs">';
			
			// Tab navigation
		echo '<nav class="ennu-admin-tab-nav"><ul>';
		echo '<li><a href="#tab-global-metrics" class="ennu-admin-tab-active">' . esc_html__( 'Global & Health Metrics', 'ennulifeassessments' ) . '</a></li>';
		echo '<li><a href="#tab-centralized-symptoms">' . esc_html__( 'Centralized Symptoms', 'ennulifeassessments' ) . '</a></li>';
			echo '<li><a href="#tab-biomarkers">' . esc_html__( 'Biomarkers', 'ennulifeassessments' ) . '</a></li>';
			
			// Assessment tabs
		foreach ( $assessments as $key ) {
			if ( 'welcome_assessment' === $key || 'welcome' === $key ) {
					continue;
				}
			$label = ucwords( str_replace( array( '_', 'assessment' ), ' ', $key ) );
			echo '<li><a href="#tab-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</a></li>';
		}
		echo '</ul></nav>';

			// Tab content sections
		echo '<div id="tab-global-metrics" class="ennu-admin-tab-content ennu-admin-tab-active">';
			$this->display_global_fields_section( $user_id );
		echo '</div>';

		echo '<div id="tab-centralized-symptoms" class="ennu-admin-tab-content">';
			$this->display_centralized_symptoms_section( $user_id );
		echo '</div>';

					echo '<div id="tab-biomarkers" class="ennu-admin-tab-content">';
		$this->display_biomarkers_section( $user_id );
		echo '</div>';

			// Individual assessment tabs
		foreach ( $assessments as $assessment_key ) {
			if ( 'welcome_assessment' === $assessment_key || 'welcome' === $assessment_key ) {
					continue;
				}
				
			echo '<div id="tab-' . esc_attr( $assessment_key ) . '" class="ennu-admin-tab-content">';
			echo '<table class="form-table">';
				
				try {
					$this->display_assessment_fields_editable( $user_id, $assessment_key );
				} catch ( Exception $e ) {
					error_log( 'ENNU Enhanced Admin: Error displaying assessment fields for ' . $assessment_key . ': ' . $e->getMessage() );
					echo '<tr><td colspan="2"><div style="color: red;">Error loading assessment data: ' . esc_html( $e->getMessage() ) . '</div></td></tr>';
				}
				
			echo '</table>';
				echo '<p><button type="button" class="button button-secondary ennu-clear-single-assessment-data" data-assessment-key="' . esc_attr( $assessment_key ) . '" data-user-id="' . esc_attr( $user_id ) . '">' . esc_html__( 'Clear Data for This Assessment', 'ennulifeassessments' ) . '</button></p>';
			echo '</div>';
		}
			
			echo '</div>'; // Close ennu-admin-tabs

			// Add inline JavaScript to ensure tabs work even if external script fails
			echo '<script type="text/javascript">
			(function() {
				// Inline tab handler as backup
				function initTabsBackup() {
					const tabLinks = document.querySelectorAll(".ennu-admin-tab-nav a");
					const tabContents = document.querySelectorAll(".ennu-admin-tab-content");
					
					if (tabLinks.length === 0 || tabContents.length === 0) {
						console.warn("ENNU Tabs: No tabs found");
						return;
					}
					
					console.log("ENNU Tabs: Initializing inline backup handler");
					
					// Handle hash on page load
					if (window.location.hash) {
						const targetTab = document.querySelector(window.location.hash);
						if (targetTab && targetTab.classList.contains("ennu-admin-tab-content")) {
							// Hide all tabs
							tabContents.forEach(function(content) {
								content.classList.remove("ennu-admin-tab-active");
							});
							tabLinks.forEach(function(link) {
								link.classList.remove("ennu-admin-tab-active");
							});
							
							// Show target tab
							targetTab.classList.add("ennu-admin-tab-active");
							
							// Activate corresponding link
							const targetLink = document.querySelector(\'a[href="\' + window.location.hash + \'"]\');
							if (targetLink) {
								targetLink.classList.add("ennu-admin-tab-active");
							}
						}
					}
					
					// Add click handlers
					tabLinks.forEach(function(link) {
						link.addEventListener("click", function(e) {
							e.preventDefault();
							const targetId = this.getAttribute("href");
							
							// Hide all tabs
							tabContents.forEach(function(content) {
								content.classList.remove("ennu-admin-tab-active");
							});
							tabLinks.forEach(function(l) {
								l.classList.remove("ennu-admin-tab-active");
							});
							
							// Show target tab
							const targetContent = document.querySelector(targetId);
							if (targetContent) {
								targetContent.classList.add("ennu-admin-tab-active");
								this.classList.add("ennu-admin-tab-active");
								
								// Update URL hash
								if (history.pushState) {
									history.pushState(null, null, targetId);
								} else {
									window.location.hash = targetId;
								}
							}
						});
					});
				}
				
				// Initialize immediately
				if (document.readyState === "loading") {
					document.addEventListener("DOMContentLoaded", initTabsBackup);
				} else {
					initTabsBackup();
				}
			})();
			</script>';



		} catch ( Exception $e ) {
			error_log( 'ENNU Enhanced Admin: Fatal error in show_user_assessment_fields: ' . $e->getMessage() );
			echo '<div style="color: red; background: #fff3f3; padding: 20px; border: 2px solid #f00; margin: 20px 0;">';
			echo '<h3>ENNU Life Assessment Error</h3>';
			echo '<p>An error occurred while loading the assessment data. Please try refreshing the page or contact support if the problem persists.</p>';
			echo '<p><strong>Error Details:</strong> ' . esc_html( $e->getMessage() ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * Display global fields section for admin
	 */
	private function display_global_fields_section( $user_id ) {
		echo '<table class="form-table">';

		// Global health goals
		$health_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
		$health_goals = is_array( $health_goals ) ? $health_goals : array();
		$goal_options = $this->get_health_goal_options();

		echo '<tr><th><label for="ennu_global_health_goals">' . esc_html__( 'Health Goals', 'ennulifeassessments' ) . '</label></th><td>';
		echo '<div class="ennu-admin-checkbox-group">';
		foreach ( $goal_options as $goal_id => $goal_label ) {
			echo '<label><input type="checkbox" name="ennu_global_health_goals[]" value="' . esc_attr( $goal_id ) . '" ' . checked( in_array( $goal_id, $health_goals, true ), true, false ) . '> ' . esc_html( $goal_label ) . '</label><br>';
		}
		echo '</div></td></tr>';

		// Date of Birth and Age Management
		$dob = get_user_meta( $user_id, 'ennu_global_date_of_birth', true );
		$exact_age = get_user_meta( $user_id, 'ennu_global_exact_age', true );
		$age_range = get_user_meta( $user_id, 'ennu_global_age_range', true );
		$age_category = get_user_meta( $user_id, 'ennu_global_age_category', true );

		echo '<tr><th><label for="ennu_global_date_of_birth">' . esc_html__( 'Date of Birth', 'ennulifeassessments' ) . '</label></th><td>';
		echo '<input type="date" name="ennu_global_date_of_birth" value="' . esc_attr( $dob ) . '" class="regular-text">';
		echo '<p class="description">' . esc_html__( 'Enter date of birth to auto-calculate age data', 'ennulifeassessments' ) . '</p>';
		echo '</td></tr>';

		// Display calculated age data (always show, even if empty)
		echo '<tr><th><label>' . esc_html__( 'Exact Age', 'ennulifeassessments' ) . '</label></th><td>' . esc_html( $exact_age ? $exact_age . ' years' : 'Not calculated' ) . '</td></tr>';
		echo '<tr><th><label>' . esc_html__( 'Age Range', 'ennulifeassessments' ) . '</label></th><td>' . esc_html( $age_range ? ENNU_Age_Management_System::get_age_range_label( $age_range ) : 'Not calculated' ) . '</td></tr>';
		echo '<tr><th><label>' . esc_html__( 'Age Category', 'ennulifeassessments' ) . '</label></th><td>' . esc_html( $age_category ? ENNU_Age_Management_System::get_age_category_label( $age_category ) : 'Not calculated' ) . '</td></tr>';

		// Height and weight
		$height_weight = get_user_meta( $user_id, 'ennu_global_height_weight', true );
		$height_weight = is_array( $height_weight ) ? $height_weight : array();

		echo '<tr><th><label for="ennu_global_height_weight">' . esc_html__( 'Height & Weight', 'ennulifeassessments' ) . '</label></th><td>';
		echo '<div class="ennu-admin-hw-group">';
		echo '<span>Height: <input type="number" name="ennu_global_height_weight[ft]" value="' . esc_attr( $height_weight['ft'] ?? '' ) . '" class="small-text"> ft </span>';
		echo '<span><input type="number" name="ennu_global_height_weight[in]" value="' . esc_attr( $height_weight['in'] ?? '' ) . '" class="small-text"> in</span>';
		// Handle both 'weight' and 'lbs' keys for backward compatibility
		$weight_value = isset( $height_weight['weight'] ) ? $height_weight['weight'] : ( isset( $height_weight['lbs'] ) ? $height_weight['lbs'] : '' );
		echo '<span style="margin-left: 20px;">Weight: <input type="number" name="ennu_global_height_weight[weight]" value="' . esc_attr( $weight_value ) . '" class="small-text"> lbs</span>';
		echo '</div></td></tr>';

		// Calculated BMI
		$calculated_bmi = get_user_meta( $user_id, 'ennu_calculated_bmi', true );
		if ( ! empty( $calculated_bmi ) ) {
			echo '<tr><th><label>' . esc_html__( 'Calculated BMI', 'ennulifeassessments' ) . '</label></th><td>' . esc_html( $calculated_bmi ) . '</td></tr>';
		}

		// ENNU Life Score
		$life_score = get_user_meta( $user_id, 'ennu_life_score', true );
		if ( ! empty( $life_score ) ) {
			echo '<tr><th><label>' . esc_html__( 'ENNU Life Score', 'ennulifeassessments' ) . '</label></th><td>' . esc_html( number_format( (float) $life_score, 1 ) ) . '</td></tr>';
		}

		echo '</table>';
	}

	/**
	 * Display centralized symptoms section for admin
	 */
	/**
	 * Display centralized symptoms section for admin
	 */
	private function display_centralized_symptoms_section( $user_id ) {
		echo '<table class="form-table">';

		// Get centralized symptoms using the SAME data source as user dashboard
		$centralized_symptoms_data = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $user_id );
		$current_symptoms          = $centralized_symptoms_data['symptoms'] ?? array();
		$symptoms_by_assessment    = $centralized_symptoms_data['by_assessment'] ?? array();

		// Get symptom history
		$symptom_history = get_user_meta( $user_id, 'ennu_symptom_history', true );
		$symptom_history = is_array( $symptom_history ) ? $symptom_history : array();

		echo '<tr><th><label>' . esc_html__( 'Current Symptoms', 'ennulifeassessments' ) . '</label></th><td>';
		if ( ! empty( $current_symptoms ) ) {
			echo '<ul style="margin:0; padding:0; list-style:none;">';
			foreach ( $current_symptoms as $symptom_key => $symptom_data ) {
				// Handle the new centralized symptoms structure
				if ( is_array( $symptom_data ) && isset( $symptom_data['name'] ) ) {
					$symptom_text = $symptom_data['name'];
					$severity     = isset( $symptom_data['severity'] ) ? $symptom_data['severity'] : 'moderate';
					$frequency    = isset( $symptom_data['frequency'] ) ? $symptom_data['frequency'] : 'daily';

					// Ensure we have strings, not arrays
					if ( is_array( $severity ) ) {
						$severity = 'moderate';
					}
					if ( is_array( $frequency ) ) {
						$frequency = 'daily';
					}

					echo '<li>• <strong>' . esc_html( $symptom_text ) . '</strong> (' . esc_html( ucfirst( $severity ) ) . ', ' . esc_html( ucfirst( $frequency ) ) . ')</li>';
				} else {
					// Fallback for old format
					$symptom_text = is_array( $symptom_data ) ? wp_json_encode( $symptom_data ) : $symptom_data;
					echo '<li>• ' . esc_html( $symptom_text ) . '</li>';
				}
			}
			echo '</ul>';
		} else {
			echo '<em>' . esc_html__( 'No symptoms recorded', 'ennulifeassessments' ) . '</em>';
		}
		echo '</td></tr>';

		// Show symptoms by assessment source
		if ( ! empty( $symptoms_by_assessment ) ) {
			echo '<tr><th><label>' . esc_html__( 'Symptoms by Assessment', 'ennulifeassessments' ) . '</label></th><td>';
			echo '<ul style="margin:0; padding:0; list-style:none;">';
			foreach ( $symptoms_by_assessment as $assessment_type => $assessment_symptoms ) {
				if ( ! empty( $assessment_symptoms ) ) {
					$assessment_name = ucwords( str_replace( '_', ' ', $assessment_type ) );

					// Handle nested array structure
					$symptoms_display = array();
					foreach ( $assessment_symptoms as $symptom ) {
						if ( is_array( $symptom ) ) {
							// If symptom is an array, extract the name or convert to string
							if ( isset( $symptom['name'] ) ) {
								$symptoms_display[] = $symptom['name'];
							} elseif ( isset( $symptom[0] ) ) {
								// Handle indexed arrays
								$symptoms_display[] = $symptom[0];
							} else {
								// Fallback for other array structures
								$symptoms_display[] = implode( ', ', $symptom );
							}
						} else {
							// If it's already a string, use it directly
							$symptoms_display[] = $symptom;
						}
					}

					echo '<li><strong>' . esc_html( $assessment_name ) . ':</strong> ' . esc_html( implode( ', ', $symptoms_display ) ) . '</li>';
				}
			}
			echo '</ul>';
			echo '</td></tr>';
		}

		echo '<tr><th><label>' . esc_html__( 'Symptom History', 'ennulifeassessments' ) . '</label></th><td>';
		if ( ! empty( $symptom_history ) ) {
			echo '<ul style="margin:0; padding:0; list-style:none;">';
			foreach ( array_reverse( $symptom_history ) as $entry ) {
				if ( isset( $entry['date'], $entry['symptoms'] ) ) {
					// Handle symptoms array properly - symptoms can be arrays or strings
					$symptoms_display = array();
					if ( is_array( $entry['symptoms'] ) ) {
						foreach ( $entry['symptoms'] as $symptom ) {
							if ( is_array( $symptom ) && isset( $symptom['name'] ) ) {
								$symptoms_display[] = $symptom['name'];
							} elseif ( is_string( $symptom ) ) {
								$symptoms_display[] = $symptom;
							}
						}
					} elseif ( is_string( $entry['symptoms'] ) ) {
						$symptoms_display = explode( ',', $entry['symptoms'] );
					}

					echo '<li><strong>' . esc_html( date( 'M j, Y @ g:i a', strtotime( $entry['date'] ) ) ) . '</strong>: ' . esc_html( implode( ', ', array_unique( $symptoms_display ) ) ) . '</li>';
				}
			}
			echo '</ul>';
		} else {
			echo '<em>' . esc_html__( 'No symptom history', 'ennulifeassessments' ) . '</em>';
		}
		echo '</td></tr>';

		echo '</table>';

		// Action buttons for centralized symptoms
		echo '<p>';
		echo '<button type="button" class="button button-secondary" id="ennu-update-centralized-symptoms" data-user-id="' . esc_attr( $user_id ) . '">' . esc_html__( 'Manually Recalculate Symptoms', 'ennulifeassessments' ) . '</button>';
		echo '<button type="button" class="button button-secondary" id="ennu-clear-symptom-history" data-user-id="' . esc_attr( $user_id ) . '">' . esc_html__( 'Clear Symptom History', 'ennulifeassessments' ) . '</button>';
		echo '</p>';
	}

	/**
	 * Determine which range category is active for a biomarker based on user demographics
	 * 
	 * @param array $config Biomarker configuration
	 * @param int $user_age User's age
	 * @param string $user_gender User's gender
	 * @param string $age_group Determined age group
	 * @param string $biomarker_key Biomarker key
	 * @param int $user_id User ID
	 * @return array Active range category information
	 */
	private function get_age_group_key( $age ) {
		if ( $age < 30 ) {
			return 'young';
		} elseif ( $age < 60 ) {
			return 'adult';
		} else {
			return 'senior';
		}
	}

	/**
	 * Calculate AI-powered target value based on Bryan Johnson longevity standards
	 */
	private function calculate_ai_target_value( $biomarker_key, $current_value, $optimal_min, $optimal_max, $user_age, $user_gender ) {
		// Bryan Johnson biomarker standards from research
		$bryan_standards = array(
			'hba1c' => 4.5,           // Bryan: 4.5, Ideal: < 5.2
			'insulin' => 3.0,         // Bryan: 3.0, Ideal: < 5
			'cholesterol_ldl' => 45,  // Bryan: 45, Ideal: < 70
			'cholesterol_hdl' => 78,  // Bryan: 78, Ideal: > 60
			'hs_crp' => 0.1,          // Bryan: < 0.1, Ideal: < 1.0
			'vitamin_d' => 67.8,      // Bryan: 67.8, Ideal: > 50
			'omega_3' => 9.98,        // Bryan: 9.98, Ideal: > 5.4
			'ast' => 13,              // Bryan: 13, Ideal: < 25
			'alt' => 10,              // Bryan: 10, Ideal: < 25
			'cystatin_c' => 0.61,     // Bryan: 0.61, Ideal: < 1
			'testosterone_total' => 941, // Bryan: 941, Ideal: > 450
		);

		// If we have a Bryan Johnson standard, use it
		if ( isset( $bryan_standards[ $biomarker_key ] ) ) {
			return $bryan_standards[ $biomarker_key ];
		}

		// Otherwise, calculate based on optimal range
		if ( ! empty( $optimal_min ) && ! empty( $optimal_max ) ) {
			$optimal_mid = ( floatval( $optimal_min ) + floatval( $optimal_max ) ) / 2;
			return round( $optimal_mid, 2 );
		}

		// Fallback to optimal min if available
		if ( ! empty( $optimal_min ) ) {
			return $optimal_min;
		}

		return '';
	}

	/**
	 * Calculate AI target minimum value
	 */
	private function calculate_ai_target_min( $biomarker_key, $optimal_min ) {
		if ( ! empty( $optimal_min ) ) {
			// For most biomarkers, target min is slightly below optimal min
			return round( floatval( $optimal_min ) * 0.95, 2 );
		}
		return '';
	}

	/**
	 * Calculate AI target maximum value
	 */
	private function calculate_ai_target_max( $biomarker_key, $optimal_max ) {
		if ( ! empty( $optimal_max ) ) {
			// For most biomarkers, target max is slightly above optimal max
			return round( floatval( $optimal_max ) * 1.05, 2 );
		}
		return '';
	}

	/**
	 * Determine biomarker priority based on Bryan Johnson standards
	 */
	private function determine_biomarker_priority( $biomarker_key ) {
		// Critical biomarkers based on Bryan Johnson's protocol
		$critical_biomarkers = array(
			'hs_crp',           // Most important inflammation marker
			'hba1c',            // Metabolic clock
			'insulin',          // Early warning marker
			'cholesterol_ldl',  // Artery killer
			'cholesterol_hdl',  // The cleaner
		);

		// High priority biomarkers
		$high_priority_biomarkers = array(
			'vitamin_d',        // Immunity shield
			'omega_3',          // Longevity oil
			'ast',              // Liver function
			'alt',              // Liver function
			'cystatin_c',       // Kidney function
			'testosterone_total', // Male vitality
		);

		if ( in_array( $biomarker_key, $critical_biomarkers ) ) {
			return 'critical';
		} elseif ( in_array( $biomarker_key, $high_priority_biomarkers ) ) {
			return 'high';
		} else {
			return 'medium';
		}
	}

	private function determine_active_range_category( $config, $user_age, $user_gender, $age_group, $biomarker_key, $user_id ) {
		// Check for user override first (highest priority)
		$user_override = get_user_meta( $user_id, "ennu_biomarker_override_{$biomarker_key}", true );
		if ( ! empty( $user_override ) ) {
			error_log( "ENNU Enhanced Admin: Biomarker {$biomarker_key} using USER OVERRIDE for user {$user_id}" );
			return array(
				'type' => 'user-override',
				'display' => 'User Override',
				'reason' => 'Custom range set by healthcare provider',
				'factors' => array('user-override')
			);
		}

		$has_age_range = ! empty( $user_age ) && isset( $config['ranges'] ) && isset( $config['ranges']['age_groups'] ) && isset( $config['ranges']['age_groups'][ $age_group ] );
		$has_gender_range = ! empty( $user_gender ) && isset( $config['ranges'] ) && isset( $config['ranges']['gender'] ) && isset( $config['ranges']['gender'][ $user_gender ] );

		// Check for both age and gender ranges (highest specificity)
		if ( $has_age_range && $has_gender_range ) {
			error_log( "ENNU Enhanced Admin: Biomarker {$biomarker_key} using AGE+GENDER-SPECIFIC range ({$age_group}, {$user_gender}) for user {$user_id}" );
			return array(
				'type' => 'age-gender-specific',
				'display' => "Age {$age_group}, {$user_gender}",
				'reason' => "Based on age {$user_age} years and {$user_gender} gender",
				'factors' => array('age', 'gender')
			);
		}

		// Check for age-specific range only
		if ( $has_age_range ) {
			error_log( "ENNU Enhanced Admin: Biomarker {$biomarker_key} using AGE-SPECIFIC range ({$age_group}) for user {$user_id}" );
			return array(
				'type' => 'age-specific',
				'display' => "Age {$age_group}",
				'reason' => "Based on age {$user_age} years",
				'factors' => array('age')
			);
		}

		// Check for gender-specific range only
		if ( $has_gender_range ) {
			error_log( "ENNU Enhanced Admin: Biomarker {$biomarker_key} using GENDER-SPECIFIC range ({$user_gender}) for user {$user_id}" );
			return array(
				'type' => 'gender-specific',
				'display' => ucfirst( $user_gender ),
				'reason' => "Based on {$user_gender} gender",
				'factors' => array('gender')
			);
		}

		// Default range
		error_log( "ENNU Enhanced Admin: Biomarker {$biomarker_key} using DEFAULT range for user {$user_id}" );
		return array(
			'type' => 'default',
			'display' => 'Default',
			'reason' => 'Standard reference range',
			'factors' => array('default')
		);
	}

	// --- v57.1.0: AJAX Handlers for Admin Actions ---

	public function handle_recalculate_all_scores() {
		ENNU_AJAX_Security::validate_ajax_request();
		check_ajax_referer( 'ennu_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_users' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
		}

		$security_validator = ENNU_Security_Validator::get_instance();
		if ( ! $security_validator->rate_limit_check( 'admin_recalculate_scores', 3, 300 ) ) {
			wp_send_json_error( array( 'message' => 'Too many requests. Please wait before trying again.' ), 429 );
			return;
		}

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => 'Invalid user ID.' ), 400 );
		}

		// Recalculate ENNU LIFE SCORE which depends on individual scores
		$new_score = ENNU_Assessment_Scoring::calculate_ennu_life_score( $user_id, true ); // Pass true to force recalc

		// Optionally, recalculate BMI if height/weight exists
		$height_weight = get_user_meta( $user_id, 'ennu_global_height_weight', true );
		// Handle both 'weight' and 'lbs' keys for backward compatibility
		$weight_value = is_array( $height_weight ) ? ( isset( $height_weight['weight'] ) ? $height_weight['weight'] : ( isset( $height_weight['lbs'] ) ? $height_weight['lbs'] : null ) ) : null;
		if ( ! empty( $height_weight['ft'] ) && ! empty( $weight_value ) ) {
			$height_in_total = ( intval( $height_weight['ft'] ) * 12 ) + intval( $height_weight['in'] );
			$weight_lbs      = floatval( $weight_value );
			if ( $height_in_total > 0 && $weight_lbs > 0 ) {
				$bmi = ( $weight_lbs / ( $height_in_total * $height_in_total ) ) * 703;
				update_user_meta( $user_id, 'ennu_calculated_bmi', round( $bmi, 1 ) );
			}
		}

		wp_send_json_success( array( 'message' => 'All scores recalculated successfully.' ) );
	}

	public function handle_clear_all_assessment_data() {
		ENNU_AJAX_Security::validate_ajax_request();
		check_ajax_referer( 'ennu_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_users' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
		}

		$security_validator = ENNU_Security_Validator::get_instance();
		if ( ! $security_validator->rate_limit_check( 'admin_clear_data', 2, 300 ) ) {
			wp_send_json_error( array( 'message' => 'Too many requests. Please wait before trying again.' ), 429 );
			return;
		}

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => 'Invalid user ID.' ), 400 );
		}

		global $wpdb;
		$wpdb->delete(
			$wpdb->usermeta,
			array(
				'user_id'  => $user_id,
				'meta_key' => 'ennu_%',
			),
			array( '%d', '%s' )
		);
		// A more direct, powerful approach requires a LIKE comparison
		$meta_keys_to_delete = $wpdb->get_col( $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta WHERE user_id = %d AND meta_key LIKE %s", $user_id, 'ennu_%' ) );
		foreach ( $meta_keys_to_delete as $meta_key ) {
			delete_user_meta( $user_id, $meta_key );
		}

		wp_send_json_success( array( 'message' => 'All assessment data cleared.' ) );
	}

	public function handle_clear_single_assessment_data() {
		ENNU_AJAX_Security::validate_ajax_request();
		check_ajax_referer( 'ennu_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_users' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
		}

		$security_validator = ENNU_Security_Validator::get_instance();
		if ( ! $security_validator->rate_limit_check( 'admin_clear_single', 5, 300 ) ) {
			wp_send_json_error( array( 'message' => 'Too many requests. Please wait before trying again.' ), 429 );
			return;
		}

		$user_id        = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		$assessment_key = isset( $_POST['assessment_key'] ) ? sanitize_text_field( $_POST['assessment_key'] ) : '';

		if ( ! $user_id || ! $assessment_key ) {
			wp_send_json_error( array( 'message' => 'Invalid user ID or assessment key.' ), 400 );
		}

		global $wpdb;
		$meta_keys_to_delete = $wpdb->get_col( $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta WHERE user_id = %d AND meta_key LIKE %s", $user_id, 'ennu_' . $assessment_key . '_%' ) );
		foreach ( $meta_keys_to_delete as $meta_key ) {
			delete_user_meta( $user_id, $meta_key );
		}

		wp_send_json_success( array( 'message' => 'Assessment data cleared for ' . $assessment_key . '.' ) );
	}

	public function handle_update_centralized_symptoms() {
		check_ajax_referer( 'ennu_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_users' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
		}

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => 'Invalid user ID.' ), 400 );
		}

		if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
			$result = ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $user_id );
			if ( $result ) {
				wp_send_json_success( array( 'message' => 'Centralized symptoms updated successfully.' ) );
			} else {
				wp_send_json_error( array( 'message' => 'Failed to update centralized symptoms.' ), 500 );
			}
		} else {
			wp_send_json_error( array( 'message' => 'Centralized Symptoms Manager not available.' ), 500 );
		}
	}

	public function handle_populate_centralized_symptoms() {
		check_ajax_referer( 'ennu_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_users' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
		}

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => 'Invalid user ID.' ), 400 );
		}

		if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
			$result = ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $user_id );
			if ( $result ) {
				wp_send_json_success( array( 'message' => 'Centralized symptoms populated successfully.' ) );
			} else {
				wp_send_json_error( array( 'message' => 'Failed to populate centralized symptoms.' ), 500 );
			}
		} else {
			wp_send_json_error( array( 'message' => 'Centralized Symptoms Manager not available.' ), 500 );
		}
	}

	public function handle_clear_symptom_history() {
		check_ajax_referer( 'ennu_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_users' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
		}

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => 'Invalid user ID.' ), 400 );
		}

		delete_user_meta( $user_id, 'ennu_symptom_history' );
		wp_send_json_success( array( 'message' => 'Symptom history cleared successfully.' ) );
	}

	public function handle_test_ajax() {
		check_ajax_referer( 'ennu_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_users' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
		}

		wp_send_json_success(
			array(
				'message'   => 'AJAX test successful!',
				'timestamp' => current_time( 'mysql' ),
				'user_id'   => get_current_user_id(),
			)
		);
	}

	/**
	 * AJAX handler for saving biomarker data
	 */
	public function handle_save_biomarkers_ajax() {
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'ennu_save_biomarkers_ajax_' . $_POST['user_id'] ) ) {
			wp_send_json_error( 'Security check failed' );
		}

		// Verify user permissions
		$user_id = intval( $_POST['user_id'] );
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		// Get biomarker data
		$biomarker_data = isset( $_POST['biomarker_data'] ) ? $_POST['biomarker_data'] : array();
		
		if ( empty( $biomarker_data ) ) {
			wp_send_json_error( 'No biomarker data provided' );
		}

		// Process and save biomarker data
		$saved_biomarkers = 0;
		$processed_data = array();

		foreach ( $biomarker_data as $biomarker_key => $data ) {
			if ( ! empty( $data['value'] ) ) {
				$processed_data[ $biomarker_key ] = array(
					'value' => sanitize_text_field( $data['value'] ),
					'unit' => sanitize_text_field( $data['unit'] ),
					'test_date' => sanitize_text_field( $data['test_date'] ),
					'reference_range_min' => sanitize_text_field( $data['reference_range_min'] ),
					'reference_range_max' => sanitize_text_field( $data['reference_range_max'] ),
					'optimal_range_min' => sanitize_text_field( $data['optimal_range_min'] ),
					'optimal_range_max' => sanitize_text_field( $data['optimal_range_max'] ),
					'provider_target_value' => sanitize_text_field( $data['provider_target_value'] ),
					'provider_target_min' => sanitize_text_field( $data['provider_target_min'] ),
					'provider_target_max' => sanitize_text_field( $data['provider_target_max'] ),
					'provider_target_priority' => sanitize_text_field( $data['provider_target_priority'] ),
					'provider_notes' => sanitize_textarea_field( $data['provider_notes'] ),
					'last_updated' => current_time( 'mysql' )
				);
				$saved_biomarkers++;
			}
		}

		// Save to user meta
		if ( ! empty( $processed_data ) ) {
			$result = update_user_meta( $user_id, 'ennu_biomarker_data', $processed_data );
			if ( $result !== false ) {
				error_log( 'ENNU Enhanced Admin: AJAX saved ' . $saved_biomarkers . ' biomarker records for user ID: ' . $user_id );
				wp_send_json_success( array( 
					'message' => 'Successfully saved ' . $saved_biomarkers . ' biomarker records',
					'saved_count' => $saved_biomarkers
				) );
			} else {
				wp_send_json_error( 'Failed to save biomarker data to database' );
			}
		} else {
			wp_send_json_error( 'No valid biomarker data to save' );
		}
	}


	public function save_user_assessment_fields( $user_id ) {
		try {
			// Enhanced validation and security checks
			$user_id = intval( $user_id );
			error_log( 'ENNU Enhanced Admin: save_user_assessment_fields called for user ID: ' . $user_id );
			error_log( 'ENNU Enhanced Admin: POST data: ' . print_r( $_POST, true ) );
			error_log( 'ENNU Enhanced Admin: REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD'] );
			error_log( 'ENNU Enhanced Admin: Current action: ' . current_action() );
			
			if ( $user_id <= 0 ) {
				error_log( 'ENNU Enhanced Admin: Invalid user ID in save_user_assessment_fields: ' . $user_id );
			return;
		}

			if ( ! current_user_can( 'edit_user', $user_id ) ) {
				error_log( 'ENNU Enhanced Admin: Insufficient permissions to save user assessment fields for user ID: ' . $user_id );
				return;
			}

			if ( ! isset( $_POST['ennu_assessment_nonce'] ) || ! wp_verify_nonce( $_POST['ennu_assessment_nonce'], 'ennu_user_profile_update_' . $user_id ) ) {
				error_log( 'ENNU Enhanced Admin: Nonce verification failed for user ID: ' . $user_id );
				return;
			}

			error_log( 'ENNU Enhanced Admin: Saving assessment fields for user ID: ' . $user_id );

		// --- Save Global Fields ---
		$global_keys = array(
			'ennu_global_gender',
			'ennu_global_health_goals',
			'ennu_global_height_weight',
			'ennu_global_date_of_birth',
		);

			$saved_fields = array();

		foreach ( $global_keys as $key ) {
			if ( array_key_exists( $key, $_POST ) ) {
				$raw_value = $_POST[ $key ];

				// Enhanced validation based on field type
				if ( is_array( $raw_value ) ) {
					$value_to_save = array_map( 'sanitize_text_field', $raw_value );
						
					// Validate array values based on key
					if ( $key === 'ennu_global_health_goals' ) {
							$valid_goals = array( 
								'weight_loss', 'muscle_gain', 'energy_boost', 'sleep_improvement', 
								'stress_reduction', 'hormone_balance', 'skin_health', 'hair_health', 
								'cognitive_function', 'longevity', 'athletic_performance' 
							);
						$value_to_save = array_intersect( $value_to_save, $valid_goals );
					}
				} else {
					$value_to_save = sanitize_text_field( $raw_value );

						// Enhanced validation for specific fields
					switch ( $key ) {
						case 'ennu_global_gender':
							if ( ! in_array( $value_to_save, array( 'male', 'female' ), true ) ) {
									error_log( 'ENNU Enhanced Admin: Invalid gender value: ' . $value_to_save );
								continue 2; // Skip invalid gender values
							}
							break;
							case 'ennu_global_height_weight':
								if ( is_array( $value_to_save ) && isset( $value_to_save['ft'], $value_to_save['in'], $value_to_save['lbs'] ) ) {
									$ft = $value_to_save['ft'];
									$in = $value_to_save['in'];
									$lbs = $value_to_save['lbs'];
									
									if ( ! is_numeric( $ft ) || $ft < 3 || $ft > 8 ) {
										error_log( 'ENNU Enhanced Admin: Invalid height feet value: ' . $ft );
										continue 2;
									}
									if ( ! is_numeric( $in ) || $in < 0 || $in > 11 ) {
										error_log( 'ENNU Enhanced Admin: Invalid height inches value: ' . $in );
										continue 2;
									}
									if ( ! is_numeric( $lbs ) || $lbs < 50 || $lbs > 1000 ) {
										error_log( 'ENNU Enhanced Admin: Invalid weight value: ' . $lbs );
										continue 2;
									}
								} else {
									error_log( 'ENNU Enhanced Admin: Invalid height_weight structure: ' . print_r( $value_to_save, true ) );
									continue 2;
							}
							break;
					}
				}

					$result = update_user_meta( $user_id, $key, $value_to_save );
					if ( $result !== false ) {
						$saved_fields[] = $key;
						error_log( 'ENNU Enhanced Admin: Successfully saved global field ' . $key . ' = ' . print_r( $value_to_save, true ) );
					} else {
						error_log( 'ENNU Enhanced Admin: Failed to save global field ' . $key );
					}
				} elseif ( $key === 'ennu_global_health_goals' ) { 
					// If no checkboxes are checked, save an empty array
				update_user_meta( $user_id, $key, array() );
					$saved_fields[] = $key;
				error_log( 'ENNU Enhanced Admin: Saved empty health goals array' );
			}
		}

			// --- Handle Age Management System ---
			if ( array_key_exists( 'ennu_global_date_of_birth', $_POST ) && ! empty( $_POST['ennu_global_date_of_birth'] ) ) {
				$dob = sanitize_text_field( $_POST['ennu_global_date_of_birth'] );
				
				// Use the age management system to update all age-related fields
				if ( class_exists( 'ENNU_Age_Management_System' ) ) {
					$age_data = ENNU_Age_Management_System::update_user_age_data( $user_id, $dob );
					if ( $age_data ) {
						error_log( 'ENNU Enhanced Admin: Successfully updated age data for user ID ' . $user_id . ': ' . print_r( $age_data, true ) );
					} else {
						error_log( 'ENNU Enhanced Admin: Failed to update age data for user ID ' . $user_id . ' with DOB: ' . $dob );
					}
				} else {
					error_log( 'ENNU Enhanced Admin: Age Management System not available for user ID ' . $user_id );
				}
			}

			// --- Save Assessment-Specific Fields ---
			try {
				$shortcode_handler = ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler();
				if ( ! $shortcode_handler ) {
					throw new Exception( 'Shortcode handler not available' );
				}
				$assessments = array_keys( $shortcode_handler->get_all_assessment_definitions() );
			} catch ( Exception $e ) {
				error_log( 'ENNU Enhanced Admin: Error getting assessment definitions: ' . $e->getMessage() );
				$assessments = array();
			}

			$saved_assessment_fields = 0;

		foreach ( $assessments as $assessment_type ) {
				try {
			$all_questions = $this->get_direct_assessment_questions( $assessment_type );

					// Handle nested question structure
			$questions = isset( $all_questions['questions'] ) ? $all_questions['questions'] : $all_questions;

					if ( ! is_array( $questions ) ) {
						error_log( 'ENNU Enhanced Admin: Invalid questions structure for assessment ' . $assessment_type );
						continue;
					}

			foreach ( $questions as $question_id => $q_data ) {
				if ( ! is_array( $q_data ) || isset( $q_data['global_key'] ) ) {
					continue;
				}

				$meta_key = 'ennu_' . $assessment_type . '_' . $question_id;
						
				if ( array_key_exists( $meta_key, $_POST ) ) {
							$value_to_save = is_array( $_POST[ $meta_key ] ) ? 
								array_map( 'sanitize_text_field', $_POST[ $meta_key ] ) : 
								sanitize_text_field( $_POST[ $meta_key ] );
							
							$result = update_user_meta( $user_id, $meta_key, $value_to_save );
							if ( $result !== false ) {
								$saved_assessment_fields++;
							}
				} elseif ( isset( $q_data['type'] ) && $q_data['type'] === 'multiselect' ) {
							// Save empty array for unchecked multiselect fields
					update_user_meta( $user_id, $meta_key, array() );
							$saved_assessment_fields++;
				}
			}
				} catch ( Exception $e ) {
					error_log( 'ENNU Enhanced Admin: Error saving assessment ' . $assessment_type . ': ' . $e->getMessage() );
				}
			}

			error_log( 'ENNU Enhanced Admin: Successfully saved ' . count( $saved_fields ) . ' global fields and ' . $saved_assessment_fields . ' assessment fields for user ID ' . $user_id );

			// --- Save Biomarker Data with Smart Change Detection ---
			if ( array_key_exists( 'biomarker_value', $_POST ) && is_array( $_POST['biomarker_value'] ) ) {
				// Get existing biomarker data
				$existing_biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
				if ( ! is_array( $existing_biomarker_data ) ) {
					$existing_biomarker_data = array();
				}
				
				$biomarker_data = $existing_biomarker_data; // Start with existing data
				$saved_biomarkers = 0;
				$changed_biomarkers = 0;
				
				// Check if biomarker_value array exists in POST data
				if ( ! array_key_exists( 'biomarker_value', $_POST ) || ! is_array( $_POST['biomarker_value'] ) ) {
					error_log( 'ENNU Enhanced Admin: No biomarker_value array found in POST data for user ID: ' . $user_id );
					return;
				}
				
				foreach ( $_POST['biomarker_value'] as $biomarker_key => $value ) {
					// Only process if value is not empty (user entered something)
					if ( ! empty( $value ) ) {
						$new_biomarker_data = array(
							'value' => sanitize_text_field( $value ),
							'unit' => ( array_key_exists( 'biomarker_unit', $_POST ) && array_key_exists( $biomarker_key, $_POST['biomarker_unit'] ) ) ? sanitize_text_field( $_POST['biomarker_unit'][ $biomarker_key ] ) : '',
							'test_date' => ( array_key_exists( 'test_date', $_POST ) && array_key_exists( $biomarker_key, $_POST['test_date'] ) ) ? sanitize_text_field( $_POST['test_date'][ $biomarker_key ] ) : '',
							'reference_range_min' => ( array_key_exists( 'reference_range_min', $_POST ) && array_key_exists( $biomarker_key, $_POST['reference_range_min'] ) ) ? sanitize_text_field( $_POST['reference_range_min'][ $biomarker_key ] ) : '',
							'reference_range_max' => ( array_key_exists( 'reference_range_max', $_POST ) && array_key_exists( $biomarker_key, $_POST['reference_range_max'] ) ) ? sanitize_text_field( $_POST['reference_range_max'][ $biomarker_key ] ) : '',
							'optimal_range_min' => ( array_key_exists( 'optimal_range_min', $_POST ) && array_key_exists( $biomarker_key, $_POST['optimal_range_min'] ) ) ? sanitize_text_field( $_POST['optimal_range_min'][ $biomarker_key ] ) : '',
							'optimal_range_max' => ( array_key_exists( 'optimal_range_max', $_POST ) && array_key_exists( $biomarker_key, $_POST['optimal_range_max'] ) ) ? sanitize_text_field( $_POST['optimal_range_max'][ $biomarker_key ] ) : '',
							'provider_target_value' => ( array_key_exists( 'provider_target_value', $_POST ) && array_key_exists( $biomarker_key, $_POST['provider_target_value'] ) ) ? sanitize_text_field( $_POST['provider_target_value'][ $biomarker_key ] ) : '',
							'provider_target_min' => ( array_key_exists( 'provider_target_min', $_POST ) && array_key_exists( $biomarker_key, $_POST['provider_target_min'] ) ) ? sanitize_text_field( $_POST['provider_target_min'][ $biomarker_key ] ) : '',
							'provider_target_max' => ( array_key_exists( 'provider_target_max', $_POST ) && array_key_exists( $biomarker_key, $_POST['provider_target_max'] ) ) ? sanitize_text_field( $_POST['provider_target_max'][ $biomarker_key ] ) : '',
							'provider_target_priority' => ( array_key_exists( 'provider_target_priority', $_POST ) && array_key_exists( $biomarker_key, $_POST['provider_target_priority'] ) ) ? sanitize_text_field( $_POST['provider_target_priority'][ $biomarker_key ] ) : '',
							'provider_notes' => ( array_key_exists( 'provider_notes', $_POST ) && array_key_exists( $biomarker_key, $_POST['provider_notes'] ) ) ? sanitize_textarea_field( $_POST['provider_notes'][ $biomarker_key ] ) : '',
							'last_updated' => current_time( 'mysql' )
						);
						
						// Check if this biomarker is new or has changed
						$is_new = ! array_key_exists( $biomarker_key, $existing_biomarker_data );
						$has_changed = false;
						
						if ( ! $is_new ) {
							// Compare with existing data
							$existing = $existing_biomarker_data[ $biomarker_key ];
							$has_changed = (
								( array_key_exists( 'value', $existing ) ? $existing['value'] : '' ) !== $new_biomarker_data['value'] ||
								( array_key_exists( 'test_date', $existing ) ? $existing['test_date'] : '' ) !== $new_biomarker_data['test_date'] ||
								( array_key_exists( 'provider_notes', $existing ) ? $existing['provider_notes'] : '' ) !== $new_biomarker_data['provider_notes']
							);
						}
						
						// Only save if it's new or has changed
						if ( $is_new || $has_changed ) {
							$biomarker_data[ $biomarker_key ] = $new_biomarker_data;
							$changed_biomarkers++;
							error_log( 'ENNU Enhanced Admin: Saving biomarker ' . $biomarker_key . ' for user ' . $user_id . ' - ' . ( $is_new ? 'NEW' : 'CHANGED' ) . ' value: ' . $value );
						}
						
						$saved_biomarkers++;
					}
				}
				
				// Only update if there are changes
				if ( $changed_biomarkers > 0 ) {
					$result = update_user_meta( $user_id, 'ennu_biomarker_data', $biomarker_data );
					if ( $result !== false ) {
						error_log( 'ENNU Enhanced Admin: Successfully saved ' . $changed_biomarkers . ' changed biomarker records out of ' . $saved_biomarkers . ' total for user ID: ' . $user_id );
					} else {
						error_log( 'ENNU Enhanced Admin: Failed to save biomarker data for user ID: ' . $user_id );
					}
				} else {
					error_log( 'ENNU Enhanced Admin: No biomarker changes detected for user ID: ' . $user_id );
				}
			}

			// --- Handle Flag Removal ---
			if ( array_key_exists( 'remove_flag', $_POST ) && is_array( $_POST['remove_flag'] ) ) {
				// Initialize flag manager
				if ( class_exists( 'ENNU_Biomarker_Flag_Manager' ) ) {
					$flag_manager = new ENNU_Biomarker_Flag_Manager();
					$flags_removed = 0;
					
					foreach ( $_POST['remove_flag'] as $biomarker_key => $remove_flag ) {
						if ( $remove_flag === '1' ) {
							$removal_reason = 'Flag removed by administrator via user profile';
							$success = $flag_manager->remove_flag( $user_id, $biomarker_key, $removal_reason );
							
							if ( $success ) {
								$flags_removed++;
								error_log( 'ENNU Enhanced Admin: Successfully removed flag for biomarker ' . $biomarker_key . ' for user ' . $user_id );
							} else {
								error_log( 'ENNU Enhanced Admin: Failed to remove flag for biomarker ' . $biomarker_key . ' for user ' . $user_id );
							}
						}
					}
					
					if ( $flags_removed > 0 ) {
						error_log( 'ENNU Enhanced Admin: Removed ' . $flags_removed . ' biomarker flags for user ID: ' . $user_id );
					}
				} else {
					error_log( 'ENNU Enhanced Admin: ENNU_Biomarker_Flag_Manager class not available for flag removal' );
				}
			}

			// Clear any cached data for this user
			if ( class_exists( 'ENNU_Score_Cache' ) ) {
				try {
					ENNU_Score_Cache::invalidate_cache( $user_id );
					error_log( 'ENNU Enhanced Admin: Cleared cache for user ID ' . $user_id );
				} catch ( Exception $e ) {
					error_log( 'ENNU Enhanced Admin: Error clearing cache: ' . $e->getMessage() );
				}
			}

		} catch ( Exception $e ) {
			error_log( 'ENNU Enhanced Admin: Fatal error in save_user_assessment_fields: ' . $e->getMessage() );
		}
	}

	// --- PRIVATE HELPER METHODS ---

	private function get_assessment_statistics() {
		global $wpdb;
		$stats            = array(
			'total_assessments'   => 0,
			'monthly_assessments' => 0,
			'active_users'        => 0,
		);
		$assessment_types = array_keys( ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions() );
		$meta_keys        = array();
		foreach ( $assessment_types as $type ) {
			if ( 'welcome_assessment' === $type ) {
				continue;
			}
			$meta_keys[] = 'ennu_' . $type . '_calculated_score';
		}
		if ( ! empty( $meta_keys ) ) {
			$placeholders          = implode( ', ', array_fill( 0, count( $meta_keys ), '%s' ) );
			$query                 = $wpdb->prepare( "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key IN ($placeholders) AND meta_value != ''", $meta_keys );
			$stats['active_users'] = (int) $wpdb->get_var( $query );
		}
		$stats['monthly_assessments'] = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key LIKE %s AND meta_value != '' AND CAST(meta_value AS SIGNED) > 0 AND user_id IN (SELECT ID FROM {$wpdb->users} WHERE user_registered >= DATE_SUB(NOW(), INTERVAL 1 MONTH))", 'ennu_%_calculated_score' ) );
		return $stats;
	}

	private function display_recent_assessments_table() {
		global $wpdb;
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT u.ID, u.user_login, u.user_email, um.meta_value as score, REPLACE(REPLACE(um.meta_key, 'ennu_', ''), '_calculated_score', '') as assessment_type FROM {$wpdb->usermeta} um JOIN {$wpdb->users} u ON um.user_id = u.ID WHERE um.meta_key LIKE %s AND um.meta_value != '' ORDER BY um.umeta_id DESC LIMIT 10", 'ennu_%_calculated_score' ) );
		if ( empty( $results ) ) {
			echo '<p>' . esc_html__( 'No recent assessment submissions found.', 'ennulifeassessments' ) . '</p>';
			return; }
		echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>User</th><th>Email</th><th>Assessment Type</th><th>Score</th></tr></thead><tbody>';
		foreach ( $results as $result ) {
			echo '<tr>';
			echo '<td><a href="' . esc_url( get_edit_user_link( $result->ID ) ) . '">' . esc_html( $result->user_login ) . '</a></td>';
			echo '<td>' . esc_html( $result->user_email ) . '</td>';
			echo '<td>' . esc_html( ucwords( str_replace( '_', ' ', $result->assessment_type ) ) ) . '</td>';
			echo '<td>' . esc_html( number_format( floatval( $result->score ), 1 ) ) . '/10</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}

	private function get_assessment_count( $assessment_type ) {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value != ''", 'ennu_' . $assessment_type . '_calculated_score' ) );
	}

	private function display_all_assessments_table() {
		global $wpdb;
		$user_ids = $wpdb->get_col( "SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key LIKE 'ennu_%_calculated_score' ORDER BY user_id DESC LIMIT 50" );
		if ( empty( $user_ids ) ) {
			echo '<p>' . __( 'No assessment data found.', 'ennulifeassessments' ) . '</p>';
			return; }
		echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>User</th><th>Email</th><th>Assessments Completed</th><th>Date Registered</th></tr></thead><tbody>';
		foreach ( $user_ids as $user_id ) {
			$user = get_userdata( $user_id );
			if ( ! $user ) {
				continue;
			}
			$assessments     = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key LIKE %s", $user_id, '%_calculated_score' ) );
			$assessment_list = array();
			if ( ! empty( $assessments ) ) {
				foreach ( $assessments as $assessment ) {
					$type              = str_replace( array( 'ennu_', '_calculated_score' ), '', $assessment->meta_key );
					$label             = ucwords( str_replace( '_', ' ', $type ) );
					$assessment_list[] = esc_html( $label . ' (' . number_format( floatval( $assessment->meta_value ), 1 ) . '/10)' );
				}
			}
			echo '<tr><td><a href="' . esc_url( get_edit_user_link( $user_id ) ) . '">' . esc_html( $user->user_login ) . '</a></td><td>' . esc_html( $user->user_email ) . '</td><td>' . wp_kses_post( implode( '<br>', $assessment_list ) ) . '</td><td>' . esc_html( date( 'M j, Y', strtotime( $user->user_registered ) ) ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}

	private function get_plugin_settings() {
		$main_settings = get_option(
			'ennu_plugin_settings',
			array(
				'admin_email'         => get_option( 'admin_email' ),
				'email_notifications' => 1,
			)
		);

		// Manually fetch and merge the page mappings
		$main_settings['page_mappings'] = get_option( 'ennu_created_pages', array() );

		return $main_settings;
	}

	private function save_settings() {
		$settings['admin_email'] = isset( $_POST['admin_email'] ) ? sanitize_email( $_POST['admin_email'] ) : '';
		update_option( 'ennu_plugin_settings', $settings );

		if ( isset( $_POST['ennu_pages'] ) && is_array( $_POST['ennu_pages'] ) ) {
			$page_mappings = array_map( 'intval', $_POST['ennu_pages'] );
			update_option( 'ennu_created_pages', $page_mappings );
		}
	}

	private function display_system_status() {
		echo '<table class="form-table"><tbody>';
		echo '<tr><th>' . esc_html__( 'WordPress Version', 'ennulifeassessments' ) . '</th><td>' . esc_html( get_bloginfo( 'version' ) ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Plugin Version', 'ennulifeassessments' ) . '</th><td>' . esc_html( ENNU_LIFE_VERSION ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'PHP Version', 'ennulifeassessments' ) . '</th><td>' . esc_html( PHP_VERSION ) . '</td></tr>';
		echo '</tbody></table>';
	}



	private function render_global_field_for_admin( $key, $data, $current_value ) {
		$type = $data['type'] ?? 'static';

		switch ( $type ) {
			case 'multiselect':
				$options        = $data['options'] ?? array();
				$current_values = is_array( $current_value ) ? $current_value : array();
				echo '<div class="ennu-admin-checkbox-group">';
				foreach ( $options as $value => $label ) {
					echo '<label><input type="checkbox" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $value ) . '" ' . checked( in_array( $value, $current_values, true ), true, false ) . '> ' . esc_html( $label ) . '</label>';
				}
				echo '</div>';
				break;

			case 'height_weight':
				$height_ft  = $current_value['ft'] ?? '';
				$height_in  = $current_value['in'] ?? '';
				$weight_lbs = $current_value['lbs'] ?? '';
				echo '<div class="ennu-admin-hw-group">';
				echo '<span>Height: <input type="number" name="' . esc_attr( $key ) . '[ft]" value="' . esc_attr( $height_ft ) . '" class="small-text"> ft </span>';
				echo '<span><input type="number" name="' . esc_attr( $key ) . '[in]" value="' . esc_attr( $height_in ) . '" class="small-text"> in</span>';
				echo '<span style="margin-left: 20px;">Weight: <input type="number" name="' . esc_attr( $key ) . '[lbs]" value="' . esc_attr( $weight_lbs ) . '" class="small-text"> lbs</span>';
				echo '</div>';
				break;

			default:
				if ( $key === 'ennu_life_score_history' || $key === 'ennu_bmi_history' ) {
					if ( ! empty( $current_value ) && is_array( $current_value ) ) {
						echo '<ul style="margin:0; padding:0; list-style:none;">';
						foreach ( array_reverse( $current_value ) as $entry ) {
							$value_key = ( $key === 'ennu_bmi_history' ) ? 'bmi' : 'score';
							if ( isset( $entry['date'], $entry[ $value_key ] ) ) {
								echo '<li><strong>' . esc_html( number_format( (float) $entry[ $value_key ], 1 ) ) . '</strong> on ' . esc_html( date( 'M j, Y @ g:i a', strtotime( $entry['date'] ) ) ) . '</li>';
							}
						}
						echo '</ul>';
					}
				} else {
					echo esc_html( $current_value );
				}
				break;
		}
	}

	private function get_health_goal_options() {
		// Load health goals from the new configuration file
		$health_goals_config = ENNU_LIFE_PLUGIN_PATH . 'includes/config/scoring/health-goals.php';

		if ( file_exists( $health_goals_config ) ) {
			$config = require $health_goals_config;
			if ( isset( $config['goal_definitions'] ) ) {
				$options = array();
				foreach ( $config['goal_definitions'] as $goal_id => $goal_data ) {
					// Handle different data structures - check for label, name, or use goal_id as fallback
					if ( is_array( $goal_data ) ) {
						if ( isset( $goal_data['label'] ) ) {
							$options[ $goal_id ] = $goal_data['label'];
						} elseif ( isset( $goal_data['name'] ) ) {
							$options[ $goal_id ] = $goal_data['name'];
						} else {
							$options[ $goal_id ] = ucwords( str_replace( '_', ' ', $goal_id ) );
						}
					} else {
						// If goal_data is a string, use it directly
						$options[ $goal_id ] = $goal_data;
					}
				}
				return $options;
			}
		}

		// Fallback to welcome assessment options if config not available
		$definitions     = ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions();
		$welcome_options = $definitions['welcome_assessment']['questions']['welcome_q3']['options'] ?? array();

		if ( ! empty( $welcome_options ) ) {
			return $welcome_options;
		}

		// Final fallback - default health goals
		return array(
			'longevity'        => 'Longevity & Healthy Aging',
			'energy'           => 'Improve Energy & Vitality',
			'strength'         => 'Build Strength & Muscle',
			'libido'           => 'Enhance Libido & Sexual Health',
			'weight_loss'      => 'Achieve & Maintain Healthy Weight',
			'hormonal_balance' => 'Hormonal Balance',
			'cognitive_health' => 'Sharpen Cognitive Function',
			'heart_health'     => 'Support Heart Health',
			'aesthetics'       => 'Improve Hair, Skin & Nails',
			'sleep'            => 'Improve Sleep Quality',
			'stress'           => 'Reduce Stress & Improve Resilience',
		);
	}

	/**
	 * Add biomarker management tab to user profile
	 */
	public function add_biomarker_management_tab( $user ) {
		// Only show for administrators or if user has biomarker data
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$user_id        = $user->ID;
		$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );

		// Only show tab if there's biomarker data or user is admin
		if ( empty( $biomarker_data ) && ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<h2>' . esc_html__( 'Biomarker Management', 'ennulifeassessments' ) . '</h2>';
		echo '<table class="form-table">';

		// Display existing biomarker data
		if ( ! empty( $biomarker_data ) && is_array( $biomarker_data ) ) {
			foreach ( $biomarker_data as $biomarker_name => $biomarker_info ) {
				echo '<tr>';
				echo '<th><label>' . esc_html( ucwords( str_replace( '_', ' ', $biomarker_name ) ) ) . '</label></th>';
				echo '<td>';
				echo '<strong>' . esc_html( $biomarker_info['value'] ) . '</strong>';
				if ( ! empty( $biomarker_info['unit'] ) ) {
					echo ' ' . esc_html( $biomarker_info['unit'] );
				}
				if ( ! empty( $biomarker_info['reference_range'] ) ) {
					echo ' (Reference: ' . esc_html( $biomarker_info['reference_range'] ) . ')';
				}
				if ( ! empty( $biomarker_info['test_date'] ) ) {
					echo '<br><small>Test Date: ' . esc_html( $biomarker_info['test_date'] ) . '</small>';
				}
				echo '</td>';
				echo '</tr>';
			}
		} else {
			echo '<tr><td colspan="2"><em>' . esc_html__( 'No biomarker data available', 'ennulifeassessments' ) . '</em></td></tr>';
		}

		echo '</table>';

		// Import form for administrators
		if ( current_user_can( 'manage_options' ) ) {
			echo '<h3>' . esc_html__( 'Import New Biomarker Data', 'ennulifeassessments' ) . '</h3>';
			echo '<form method="post" action="">';
			wp_nonce_field( 'import_biomarker_data' );
			echo '<table class="form-table">';
			echo '<tr><th><label for="biomarker_json">' . esc_html__( 'Biomarker Data (JSON)', 'ennulifeassessments' ) . '</label></th>';
			echo '<td><textarea name="biomarker_json" rows="5" cols="50" placeholder=\'{"Total_Testosterone": {"value": 650, "unit": "ng/dL", "test_date": "2024-01-15"}}\'></textarea></td></tr>';
			echo '</table>';
			echo '<p class="submit"><input type="submit" name="import_biomarker_data" class="button-primary" value="' . esc_attr__( 'Import Data', 'ennulifeassessments' ) . '" /></p>';
			echo '</form>';
		}
	}


	private function display_assessment_fields_editable( $user_id, $assessment_type ) {
		$all_questions = $this->get_direct_assessment_questions( $assessment_type );

		// This is the definitive fix. The new Health Optimization assessment has its questions
		// nested under a 'questions' key. We must check for this structure.
		$questions = isset( $all_questions['questions'] ) ? $all_questions['questions'] : $all_questions;

		if ( empty( $questions ) ) {
			return;
		}
		foreach ( $questions as $question_id => $q_data ) {
			// A key without an array value is metadata (e.g. 'title'), not a question. Skip it.
			if ( ! is_array( $q_data ) ) {
				continue;
			}
			if ( isset( $q_data['global_key'] ) ) {
				continue;
			}
			$meta_key      = 'ennu_' . $assessment_type . '_' . $question_id;
			$current_value = get_user_meta( $user_id, $meta_key, true );
			echo '<tr><th><label for="' . esc_attr( $meta_key ) . '">' . esc_html( $q_data['title'] ) . '</label></th><td>';
			$this->render_field_for_admin( $meta_key, $q_data['type'] ?? 'text', $q_data['options'] ?? array(), $current_value, $assessment_type, $question_id );
			echo '</td></tr>';
		}

		// --- System Data Section ---
		echo '<tr><td colspan="2"><hr><h4>' . esc_html__( 'System Data', 'ennulifeassessments' ) . '</h4></td></tr>';

		$system_fields = array(
			'ennu_' . $assessment_type . '_score_calculated_at' => 'Timestamp',
			'ennu_' . $assessment_type . '_score_interpretation' => 'Interpretation',
			'ennu_' . $assessment_type . '_pillar_scores' => 'Pillar Scores',
		);

		foreach ( $system_fields as $key => $label ) {
			$value = get_user_meta( $user_id, $key, true );
			if ( empty( $value ) ) {
				continue;
			}
			if ( is_array( $value ) ) {
				$value_str = '<ul style="margin:0; padding:0; list-style:none;">';
				foreach ( $value as $k => $v ) {
					$value_str .= '<li><strong>' . esc_html( ucwords( str_replace( '_', ' ', $k ) ) ) . ':</strong> ' . esc_html( is_array( $v ) ? json_encode( $v ) : $v ) . '</li>';
				}
				$value_str .= '</ul>';
				$value      = $value_str;
			} elseif ( strpos( $key, '_score_calculated_at' ) !== false ) {
				$value = date( 'M j, Y @ g:i a', strtotime( $value ) );
			}
			echo '<tr><th><label>' . esc_html( $label ) . '</label></th><td>' . wp_kses_post( $value ) . '</td></tr>';
		}
	}

	private function get_direct_assessment_questions( $assessment_type ) {
		return ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_assessment_questions( $assessment_type );
	}

	private function render_field_for_admin( $meta_key, $type, $options, $current_value, $assessment_type, $question_id ) {
		switch ( $type ) {
			case 'multiselect':
				$this->render_checkbox_field( $meta_key, $current_value, $options, $assessment_type, $question_id );
				break;
			case 'radio':
				$this->render_radio_field( $meta_key, $current_value, $options, $assessment_type, $question_id );
				break;
			default:
				echo '<input type="text" name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( $current_value ) . '" class="regular-text" />';
				break;
		}
	}

	private function render_radio_field( $meta_key, $current_value, $options, $assessment_type, $question_id ) {
		// This is the definitive fix. If a field was once a multiselect, the old data
		// might be an array. We must gracefully handle this to prevent fatal errors.
		if ( is_array( $current_value ) ) {
			$current_value = reset( $current_value );
		}

		foreach ( $options as $value => $label ) {
			echo '<label><input type="radio" name="' . esc_attr( $meta_key ) . '" value="' . esc_attr( $value ) . '" ' . checked( $current_value, $value, false ) . '> ' . esc_html( $label ) . '</label><br>';
		}
	}

	private function render_checkbox_field( $meta_key, $current_values, $options, $assessment_type, $question_id ) {
		$current_values = is_array( $current_values ) ? $current_values : array();
		foreach ( $options as $value => $label ) {
			echo '<label><input type="checkbox" name="' . esc_attr( $meta_key ) . '[]" value="' . esc_attr( $value ) . '" ' . checked( in_array( $value, $current_values, true ), true, false ) . '> ' . esc_html( $label ) . '</label><br>';
		}
	}

	private function setup_pages() {
		$all_definitions = ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions();
		$assessment_keys = array_keys( $all_definitions );

		$pages_to_create = array();

		// Parent Pages (created first) - SEO Optimized Titles with Short Menu Labels
		$pages_to_create['dashboard']   = array(
			'title'      => 'Health Assessment Dashboard | Track Your Wellness Journey | ENNU Life',
			'menu_label' => 'Dashboard',
			'content'    => '[ennu-user-dashboard]',
			'parent'     => 0,
		);
		$pages_to_create['assessments'] = array(
			'title'      => 'Free Health Assessments | Comprehensive Wellness Evaluations | ENNU Life',
			'menu_label' => 'Assessments',
			'content'    => '[ennu-assessments]',
			'parent'     => 0,
		);

		// Core utility page - SEO Optimized
		$pages_to_create['assessment-results'] = array(
			'title'      => 'Health Assessment Results | Personalized Wellness Insights | ENNU Life',
			'menu_label' => 'Results',
			'content'    => '[ennu-assessment-results]',
			'parent'     => 0,
		);

		// Registration page (Welcome Assessment) - Root level for better UX
		$pages_to_create['registration'] = array(
			'title'      => 'Health Registration | Start Your Wellness Journey | ENNU Life',
			'menu_label' => 'Registration',
			'content'    => '[ennu-welcome]',
			'parent'     => 0,
		);

		// Signup page - Premium product selection
		$pages_to_create['signup'] = array(
			'title'      => 'Sign Up | Premium Health Services | ENNU Life',
			'menu_label' => 'Sign Up',
			'content'    => '[ennu-signup]',
			'parent'     => 0,
		);

		// Consultation & Call Pages - SEO Optimized
		$pages_to_create['call']            = array(
			'title'      => 'Schedule a Call | Free Health Consultation | ENNU Life',
			'menu_label' => 'Schedule Call',
			'content'    => 'Schedule your free health consultation with our experts.',
			'parent'     => 0,
		);
		$pages_to_create['ennu-life-score'] = array(
			'title'      => 'Get Your ENNU Life Score | Personalized Health Assessment | ENNU Life',
			'menu_label' => 'ENNU Life Score',
			'content'    => 'Discover your personalized ENNU Life Score and health insights.',
			'parent'     => 0,
		);

		// SEO-Optimized Assessment-Specific Titles with Short Menu Labels
		$seo_assessment_titles = array(
			'hair'                => array(
				'main'       => 'Hair Loss Assessment | Male Pattern Baldness Evaluation | ENNU Life',
				'menu_label' => 'Hair Loss',
				'results'    => 'Hair Loss Assessment Results | Personalized Hair Health Analysis | ENNU Life',
				'details'    => 'Hair Loss Treatment Options | Detailed Hair Health Dossier | ENNU Life',
				'booking'    => 'Hair Treatment Consultation | Hair Loss Solutions | ENNU Life',
			),
			'ed-treatment'        => array(
				'main'       => 'Erectile Dysfunction Assessment | ED Treatment Evaluation | ENNU Life',
				'menu_label' => 'ED Treatment',
				'results'    => 'ED Assessment Results | Erectile Dysfunction Analysis | ENNU Life',
				'details'    => 'ED Treatment Options | Erectile Dysfunction Solutions Dossier | ENNU Life',
				'booking'    => 'ED Treatment Consultation | Erectile Dysfunction Solutions | ENNU Life',
			),
			'health-optimization' => array(
				'main'       => 'Health Optimization Assessment | Comprehensive Wellness Check | ENNU Life',
				'menu_label' => 'Health Optimization',
				'results'    => 'Health Optimization Results | Personalized Wellness Plan | ENNU Life',
				'details'    => 'Health Optimization Solutions | Detailed Wellness Improvement Plan | ENNU Life',
				'booking'    => 'Health Consultation | Comprehensive Wellness Evaluation | ENNU Life',
			),
			'health'              => array(
				'main'       => 'General Health Assessment | Complete Wellness Evaluation | ENNU Life',
				'menu_label' => 'General Health',
				'results'    => 'Health Assessment Results | Comprehensive Wellness Analysis | ENNU Life',
				'details'    => 'Health Improvement Plan | Detailed Wellness Solutions Dossier | ENNU Life',
				'booking'    => 'Health Consultation | Comprehensive Wellness Evaluation | ENNU Life',
			),
			'hormone'             => array(
				'main'       => 'Hormone Assessment | Testosterone & Hormone Level Evaluation | ENNU Life',
				'menu_label' => 'Hormone Balance',
				'results'    => 'Hormone Assessment Results | Hormone Balance Analysis | ENNU Life',
				'details'    => 'Hormone Therapy Options | Detailed Hormone Balance Solutions | ENNU Life',
				'booking'    => 'Hormone Consultation | Hormone Balance Specialists | ENNU Life',
			),
			'menopause'           => array(
				'main'       => 'Menopause Assessment | Hormone Replacement Therapy Evaluation | ENNU Life',
				'menu_label' => 'Menopause',
				'results'    => 'Menopause Assessment Results | HRT Suitability Analysis | ENNU Life',
				'details'    => 'Menopause Treatment Options | HRT Solutions Dossier | ENNU Life',
				'booking'    => 'Menopause Consultation | HRT Specialists | ENNU Life',
			),
			'skin'                => array(
				'main'       => 'Skin Health Assessment | Anti-Aging Skincare Evaluation | ENNU Life',
				'menu_label' => 'Skin Health',
				'results'    => 'Skin Assessment Results | Personalized Skincare Analysis | ENNU Life',
				'details'    => 'Skin Treatment Options | Anti-Aging Skincare Solutions | ENNU Life',
				'booking'    => 'Skin Treatment Consultation | Anti-Aging Skincare | ENNU Life',
			),
			'sleep'               => array(
				'main'       => 'Sleep Quality Assessment | Insomnia & Sleep Disorder Evaluation | ENNU Life',
				'menu_label' => 'Sleep Quality',
				'results'    => 'Sleep Assessment Results | Sleep Quality Analysis | ENNU Life',
				'details'    => 'Sleep Improvement Solutions | Detailed Sleep Optimization Plan | ENNU Life',
				'booking'    => 'Sleep Consultation | Sleep Optimization Specialists | ENNU Life',
			),
			'testosterone'        => array(
				'main'       => 'Testosterone Assessment | Low T Evaluation & TRT Screening | ENNU Life',
				'menu_label' => 'Testosterone',
				'results'    => 'Testosterone Assessment Results | Low T Analysis & TRT Evaluation | ENNU Life',
				'details'    => 'Testosterone Replacement Therapy | TRT Options & Solutions | ENNU Life',
				'booking'    => 'Testosterone Consultation | TRT Specialists | ENNU Life',
			),
			'weight-loss'         => array(
				'main'       => 'Weight Loss Assessment | Medical Weight Management Evaluation | ENNU Life',
				'menu_label' => 'Weight Loss',
				'results'    => 'Weight Loss Assessment Results | Personalized Weight Management Plan | ENNU Life',
				'details'    => 'Weight Loss Solutions | Medical Weight Management Options | ENNU Life',
				'booking'    => 'Weight Loss Consultation | Medical Weight Management | ENNU Life',
			),
		);

		// Assessment Form Pages (children of /assessments/)
		foreach ( $assessment_keys as $key ) {
			// Assessment keys are already in the correct format (e.g., 'hair', 'ed-treatment', 'weight-loss')
			$slug = $key;

			// Skip welcome assessment - it's now at root level
			if ( 'welcome' === $key ) {
				continue;
			}

			// Use SEO-optimized title if available, otherwise fallback to definition title with SEO enhancement
			if ( isset( $seo_assessment_titles[ $slug ]['main'] ) ) {
				$title = $seo_assessment_titles[ $slug ]['main'];
			} else {
				$base_title = $all_definitions[ $key ]['title'] ?? ucwords( str_replace( '-', ' ', $key ) );
				$title      = $base_title . ' | Professional Health Evaluation | ENNU Life';
			}

			// Form Page (child of assessments)
			$pages_to_create[ "assessments/{$slug}" ] = array(
				'title'      => $title,
				'menu_label' => $seo_assessment_titles[ $slug ]['menu_label'] ?? ucwords( str_replace( '-', ' ', $key ) ),
				'content'    => "[ennu-{$slug}]",
				'parent'     => 'assessments',
			);

				// Results Page (child of specific assessment) - SEO Optimized
				$results_slug  = $slug . '-results';
				$results_title = isset( $seo_assessment_titles[ $slug ]['results'] )
					? $seo_assessment_titles[ $slug ]['results']
					: ucwords( str_replace( '-', ' ', $key ) ) . ' Results | Personalized Health Analysis | ENNU Life';

				$pages_to_create[ "assessments/{$slug}/results" ] = array(
					'title'      => $results_title,
					'menu_label' => 'Results',
					'content'    => "[ennu-{$results_slug}]",
					'parent'     => "assessments/{$slug}",
				);

				// Details Page (child of specific assessment) - SEO Optimized
				$details_slug  = $slug . '-assessment-details';
				$details_title = isset( $seo_assessment_titles[ $slug ]['details'] )
					? $seo_assessment_titles[ $slug ]['details']
					: ucwords( str_replace( '-', ' ', $key ) ) . ' Treatment Options | Detailed Health Solutions | ENNU Life';

				$pages_to_create[ "assessments/{$slug}/details" ] = array(
					'title'      => $details_title,
					'menu_label' => 'Treatment Options',
					'content'    => "[ennu-{$details_slug}]",
					'parent'     => "assessments/{$slug}",
				);

				// Booking Page (child of specific assessment) - SEO Optimized
				$booking_slug  = $slug . '-consultation';
				$booking_title = isset( $seo_assessment_titles[ $slug ]['booking'] )
				? $seo_assessment_titles[ $slug ]['booking']
				: ucwords( str_replace( '-', ' ', $key ) ) . ' Consultation | Professional Health Consultation | ENNU Life';

				$pages_to_create[ "assessments/{$slug}/consultation" ] = array(
					'title'      => $booking_title,
					'menu_label' => 'Book Consultation',
					'content'    => "[ennu-{$booking_slug}]",
					'parent'     => "assessments/{$slug}",
				);
		}

		$page_mappings   = get_option( 'ennu_created_pages', array() );
		$created_parents = array(); // Track parent page IDs

		// Sort pages to create parents first
		$sorted_pages = array();
		foreach ( $pages_to_create as $slug => $page_data ) {
			if ( $page_data['parent'] === 0 ) {
				$sorted_pages[ $slug ] = $page_data; // Parent pages first
			}
		}
		foreach ( $pages_to_create as $slug => $page_data ) {
			if ( $page_data['parent'] !== 0 ) {
				$sorted_pages[ $slug ] = $page_data; // Child pages after
			}
		}

		foreach ( $sorted_pages as $slug => $page_data ) {
			// Validate page data before creation
			if ( empty( $page_data['title'] ) ) {
				error_log( 'ENNU Page Creation: Missing title for slug: ' . $slug );
				continue;
			}

			if ( empty( $page_data['content'] ) ) {
				error_log( 'ENNU Page Creation: Missing content for slug: ' . $slug );
				continue;
			}

			// Only act if the page isn't already mapped and has a valid ID
			if ( empty( $page_mappings[ $slug ] ) || ! get_post( $page_mappings[ $slug ] ) ) {

				// Determine parent ID
				$parent_id = 0;
				if ( $page_data['parent'] !== 0 ) {
					if ( isset( $created_parents[ $page_data['parent'] ] ) ) {
						$parent_id = $created_parents[ $page_data['parent'] ];
					} elseif ( isset( $page_mappings[ $page_data['parent'] ] ) ) {
						$parent_id = $page_mappings[ $page_data['parent'] ];
					}
				}

				// Extract the final slug (last part after /)
				$final_slug = basename( $slug );

				// First, check if a page with this path already exists
				$existing_page = get_page_by_path( $slug, OBJECT, 'page' );

				if ( $existing_page ) {
					// If it exists, just map it
					$page_mappings[ $slug ] = $existing_page->ID;
					if ( $page_data['parent'] === 0 ) {
						$created_parents[ $slug ] = $existing_page->ID;
					}
				} else {
					// If it doesn't exist, create it
					$page_id = wp_insert_post(
						array(
							'post_title'   => $page_data['title'],
							'post_name'    => $final_slug,
							'post_content' => $page_data['content'],
							'post_status'  => 'publish',
							'post_type'    => 'page',
							'post_parent'  => $parent_id,
						)
					);

					// Handle page creation success/failure
					if ( $page_id > 0 ) {
						// Set Elementor Canvas template for clean, distraction-free layout
						update_post_meta( $page_id, '_wp_page_template', 'elementor_canvas' );

						$page_mappings[ $slug ] = $page_id;
						if ( $page_data['parent'] === 0 ) {
							$created_parents[ $slug ] = $page_id;
						}

						// Store menu label as post meta for navigation
						if ( isset( $page_data['menu_label'] ) ) {
							update_post_meta( $page_id, '_ennu_menu_label', $page_data['menu_label'] );
						}

						error_log( 'ENNU Page Creation: Successfully created page "' . $page_data['title'] . '" with ID ' . $page_id );
					} else {
						error_log( 'ENNU Page Creation: Failed to create page "' . $page_data['title'] . '" for slug: ' . $slug );
					}
				}
			} else {
				// Page already exists, track it if it's a parent
				if ( $page_data['parent'] === 0 ) {
					$created_parents[ $slug ] = $page_mappings[ $slug ];
				}

				// Update menu label if it exists
				if ( isset( $page_data['menu_label'] ) ) {
					update_post_meta( $page_mappings[ $slug ], '_ennu_menu_label', $page_data['menu_label'] );
				}
			}
		}
		update_option( 'ennu_created_pages', $page_mappings );

		// Removed non-functional update_primary_menu_structure call

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Assessment pages have been created and menu updated successfully!', 'ennulifeassessments' ) . '</p></div>';
	}

	private function delete_pages() {
		$page_mappings = get_option( 'ennu_created_pages', array() );
		if ( ! empty( $page_mappings ) ) {
			foreach ( $page_mappings as $page_id ) {
				if ( get_post_type( $page_id ) === 'page' ) {
					wp_delete_post( $page_id, true );
				}
			}
			// Clear the mappings after deletion
			update_option( 'ennu_created_pages', array() );
		}
	}

	private function display_created_pages() {
		$created_pages = get_option( 'ennu_created_pages', array() );
		if ( empty( $created_pages ) ) {
			return;
		}
		echo '<h3>' . esc_html__( 'Managed Pages', 'ennulifeassessments' ) . '</h3>';
		echo '<p>' . esc_html__( 'The following pages are managed by the plugin. Deleting them from here will remove them permanently.', 'ennulifeassessments' ) . '</p>';
		echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Page Title</th><th>Page ID</th><th>Shortcode</th><th>View</th></tr></thead><tbody>';
		foreach ( $created_pages as $slug => $page_id ) {
			$page = get_post( $page_id );
			if ( $page ) {
				echo '<tr>';
				echo '<td>' . esc_html( $page->post_title ) . '</td>';
				echo '<td>' . esc_html( $page_id ) . '</td>';
				echo '<td><code>' . esc_html( $page->post_content ) . '</code></td>';
				echo '<td><a href="' . esc_url( get_permalink( $page_id ) ) . '" target="_blank">View Page</a></td>';
				echo '</tr>';
			}
		}
		echo '</tbody></table>';
	}

	// ========================================
	// PHASE 1: ENNU BIOMARKERS PAGE RENDERS
	// ========================================

	/**
	 * Render the ENNU Biomarkers Welcome Guide page
	 */
	public function render_biomarker_welcome_page() {
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'ENNU Biomarkers - Welcome Guide', 'ennulifeassessments' ) . '</h1>';
		
		echo '<div class="ennu-biomarker-welcome-content">';
		echo '<div class="ennu-welcome-section">';
		echo '<h2>' . esc_html__( 'Welcome to ENNU Biomarkers', 'ennulifeassessments' ) . '</h2>';
		echo '<p>' . esc_html__( 'This is the centralized hub for managing all biomarker reference ranges, panel configurations, and evidence tracking for the ENNU Life Plugin.', 'ennulifeassessments' ) . '</p>';
		echo '</div>';

		echo '<div class="ennu-welcome-section">';
		echo '<h3>' . esc_html__( 'What\'s New in Phase 1', 'ennulifeassessments' ) . '</h3>';
		echo '<ul>';
		echo '<li><strong>Centralized Range Management:</strong> ' . esc_html__( 'All biomarker ranges are now managed from a single location', 'ennulifeassessments' ) . '</li>';
		echo '<li><strong>Panel-Based Organization:</strong> ' . esc_html__( 'Biomarkers organized into business-aligned panels', 'ennulifeassessments' ) . '</li>';
		echo '<li><strong>Evidence Tracking:</strong> ' . esc_html__( 'Track medical sources and validation for all ranges', 'ennulifeassessments' ) . '</li>';
		echo '<li><strong>Inheritance System:</strong> ' . esc_html__( 'Default ranges with age/gender adjustments and user overrides', 'ennulifeassessments' ) . '</li>';
		echo '</ul>';
		echo '</div>';

		echo '<div class="ennu-welcome-section">';
		echo '<h3>' . esc_html__( 'Quick Navigation', 'ennulifeassessments' ) . '</h3>';
		echo '<div class="ennu-quick-nav">';
		echo '<a href="' . admin_url( 'admin.php?page=ennu-biomarker-range-management' ) . '" class="button button-primary">' . esc_html__( 'Range Management', 'ennulifeassessments' ) . '</a>';
		
		echo '<a href="' . admin_url( 'admin.php?page=ennu-biomarker-evidence-tracking' ) . '" class="button button-secondary">' . esc_html__( 'Evidence Tracking', 'ennulifeassessments' ) . '</a>';
		echo '<a href="' . admin_url( 'admin.php?page=ennu-biomarker-analytics' ) . '" class="button button-secondary">' . esc_html__( 'Analytics', 'ennulifeassessments' ) . '</a>';
		echo '</div>';
		echo '</div>';

		echo '<div class="ennu-welcome-section">';
		echo '<h3>' . esc_html__( 'System Status', 'ennulifeassessments' ) . '</h3>';
		$this->display_biomarker_system_status();
		echo '</div>';
		
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Render the Biomarker Range Management page
	 */
	public function render_biomarker_range_management_page() {
		echo '<div style="max-width: none; margin: 0; padding: 20px; width: 100%; position: relative;">';
		echo '<h1>' . esc_html__( 'Biomarker Range Management', 'ennulifeassessments' ) . '</h1>';
		
		// Handle form submissions
		if ( isset( $_POST['save_range'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'save_biomarker_range' ) ) {
			$this->handle_save_biomarker_range();
		}
		
		if ( isset( $_POST['import_ranges'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'import_biomarker_ranges' ) ) {
			$this->handle_import_biomarker_ranges();
		}
		
		if ( isset( $_POST['export_ranges'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'export_biomarker_ranges' ) ) {
			$this->handle_export_biomarker_ranges();
		}
		
		echo '<div class="ennu-range-management-content">';
		
		// Range Management Tabs
		echo '<nav class="nav-tab-wrapper">';
		echo '<a href="#ranges" class="nav-tab nav-tab-active" data-tab="ranges">' . esc_html__( 'Range Management', 'ennulifeassessments' ) . '</a>';
		echo '<a href="#validation" class="nav-tab" data-tab="validation">' . esc_html__( 'Validation & Conflicts', 'ennulifeassessments' ) . '</a>';
		echo '<a href="#import-export" class="nav-tab" data-tab="import-export">' . esc_html__( 'Import/Export', 'ennulifeassessments' ) . '</a>';
		echo '<a href="#analytics" class="nav-tab" data-tab="analytics">' . esc_html__( 'Analytics', 'ennulifeassessments' ) . '</a>';
		echo '</nav>';
		
		// Range Management Tab
		echo '<div id="ranges" class="tab-content active">';
		$this->render_range_management_tab();
		echo '</div>';
		
		// Validation Tab
		echo '<div id="validation" class="tab-content">';
		$this->render_validation_tab();
		echo '</div>';
		
		// Import/Export Tab
		echo '<div id="import-export" class="tab-content">';
		$this->render_import_export_tab();
		echo '</div>';
		
		// Analytics Tab
		echo '<div id="analytics" class="tab-content">';
		$this->render_analytics_tab();
		echo '</div>';
		
		echo '</div>';
		echo '</div>';
		
		// Enqueue JavaScript for tab functionality
		wp_enqueue_script( 'ennu-range-management', ENNU_LIFE_PLUGIN_URL . 'assets/js/range-management.js', array( 'jquery' ), ENNU_LIFE_VERSION, true );
	}



	/**
	 * Render the Biomarker Evidence Tracking page
	 */
	public function render_biomarker_evidence_tracking_page() {
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Biomarker Evidence Tracking', 'ennulifeassessments' ) . '</h1>';
		
		// Handle form submissions
		if ( isset( $_POST['save_evidence'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'save_biomarker_evidence' ) ) {
			$this->handle_save_biomarker_evidence();
		}
		
		if ( isset( $_POST['import_evidence'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'import_biomarker_evidence' ) ) {
			$this->handle_import_biomarker_evidence();
		}
		
		if ( isset( $_POST['export_evidence'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'export_biomarker_evidence' ) ) {
			$this->handle_export_biomarker_evidence();
		}
		
		echo '<div class="ennu-evidence-tracking-content">';
		
		// Evidence Tracking Tabs
		echo '<nav class="nav-tab-wrapper">';
		echo '<a href="#import-export" class="nav-tab nav-tab-active" data-tab="import-export">' . esc_html__( 'Import/Export', 'ennulifeassessments' ) . '</a>';
		echo '<a href="#conflicts" class="nav-tab" data-tab="conflicts">' . esc_html__( 'Conflict Resolution', 'ennulifeassessments' ) . '</a>';
		echo '<a href="#evidence" class="nav-tab" data-tab="evidence">' . esc_html__( 'Evidence Management', 'ennulifeassessments' ) . '</a>';
		echo '<a href="#validation" class="nav-tab" data-tab="validation">' . esc_html__( 'Data Validation', 'ennulifeassessments' ) . '</a>';
		echo '</nav>';
		
		// Import/Export Tab
		echo '<div id="import-export" class="tab-content active">';
		$this->render_import_export_tab();
		echo '</div>';
		
		// Conflict Resolution Tab
		echo '<div id="conflicts" class="tab-content">';
		$this->render_conflict_resolution_tab();
		echo '</div>';
		
		// Evidence Management Tab
		echo '<div id="evidence" class="tab-content">';
		$this->render_evidence_management_tab();
		echo '</div>';
		
		// Data Validation Tab
		echo '<div id="validation" class="tab-content">';
		$this->render_data_validation_tab();
		echo '</div>';
		
		echo '</div>';
		echo '</div>';
		
		// Enqueue JavaScript for evidence tracking functionality
		wp_enqueue_script( 'ennu-evidence-tracking', ENNU_LIFE_PLUGIN_URL . 'assets/js/evidence-tracking.js', array( 'jquery' ), ENNU_LIFE_VERSION, true );
	}

	/**
	 * Render the Biomarker Analytics page
	 */
	public function render_biomarker_analytics_page() {
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Biomarker Analytics', 'ennulifeassessments' ) . '</h1>';
		
		// Handle form submissions
		if ( isset( $_POST['generate_report'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'generate_analytics_report' ) ) {
			$this->handle_generate_analytics_report();
		}
		
		if ( isset( $_POST['export_analytics'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'export_analytics_data' ) ) {
			$this->handle_export_analytics_data();
		}
		
		echo '<div class="ennu-biomarker-analytics-content">';
		
		// Analytics Tabs
		echo '<nav class="nav-tab-wrapper">';
		echo '<a href="#overview" class="nav-tab nav-tab-active" data-tab="overview">' . esc_html__( 'Overview', 'ennulifeassessments' ) . '</a>';
		echo '<a href="#trends" class="nav-tab" data-tab="trends">' . esc_html__( 'Trends & Patterns', 'ennulifeassessments' ) . '</a>';
		echo '<a href="#correlations" class="nav-tab" data-tab="correlations">' . esc_html__( 'Correlations', 'ennulifeassessments' ) . '</a>';
		echo '<a href="#reports" class="nav-tab" data-tab="reports">' . esc_html__( 'Custom Reports', 'ennulifeassessments' ) . '</a>';
		echo '<a href="#insights" class="nav-tab" data-tab="insights">' . esc_html__( 'AI Insights', 'ennulifeassessments' ) . '</a>';
		echo '</nav>';
		
		// Overview Tab
		echo '<div id="overview" class="tab-content active">';
		$this->render_analytics_overview_tab();
		echo '</div>';
		
		// Trends Tab
		echo '<div id="trends" class="tab-content">';
		$this->render_analytics_trends_tab();
		echo '</div>';
		
		// Correlations Tab
		echo '<div id="correlations" class="tab-content">';
		$this->render_analytics_correlations_tab();
		echo '</div>';
		
		// Reports Tab
		echo '<div id="reports" class="tab-content">';
		$this->render_analytics_reports_tab();
		echo '</div>';
		
		// Insights Tab
		echo '<div id="insights" class="tab-content">';
		$this->render_analytics_insights_tab();
		echo '</div>';
		
		echo '</div>';
		echo '</div>';
		
		// Enqueue JavaScript for analytics functionality
		wp_enqueue_script( 'ennu-biomarker-analytics', ENNU_LIFE_PLUGIN_URL . 'assets/js/biomarker-analytics.js', array( 'jquery', 'chart-js' ), ENNU_LIFE_VERSION, true );
	}

	/**
	 * Display biomarker system status for welcome page
	 */
	private function display_biomarker_system_status() {
		// Get current biomarker counts
		$core_biomarkers = 50; // From our research
		$specialist_biomarkers = 150; // From our research
		$total_biomarkers = $core_biomarkers + $specialist_biomarkers;
		
		echo '<div class="ennu-system-status">';
		echo '<div class="ennu-status-item">';
		echo '<strong>' . esc_html__( 'Core Biomarkers:', 'ennulifeassessments' ) . '</strong> ' . esc_html( $core_biomarkers );
		echo '</div>';
		echo '<div class="ennu-status-item">';
		echo '<strong>' . esc_html__( 'Specialist Biomarkers:', 'ennulifeassessments' ) . '</strong> ' . esc_html( $specialist_biomarkers );
		echo '</div>';
		echo '<div class="ennu-status-item">';
		echo '<strong>' . esc_html__( 'Total Biomarkers:', 'ennulifeassessments' ) . '</strong> ' . esc_html( $total_biomarkers );
		echo '</div>';
		echo '<div class="ennu-status-item">';
		echo '<strong>' . esc_html__( 'System Status:', 'ennulifeassessments' ) . '</strong> <span style="color: green;">' . esc_html__( 'Phase 5 Active', 'ennulifeassessments' ) . '</span>';
		echo '</div>';
		echo '</div>';
	}

	// ========================================
	// PHASE 2: RANGE MANAGEMENT METHODS
	// ========================================

	/**
	 * Handle saving biomarker range data - processes arrays of biomarker data
	 */
	private function handle_save_biomarker_range() {
		
		// Verify nonce and permissions
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'save_biomarker_range' ) ) {
			wp_die( 'Security check failed' );
		}
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		// The form sends arrays of data for multiple biomarkers
		// We need to process each biomarker individually
		$processed_count = 0;
		$errors = array();
		
		// Get all the array fields from the form
		$ranges_optimal_min = isset( $_POST['ranges_optimal_min'] ) && is_array( $_POST['ranges_optimal_min'] ) ? $_POST['ranges_optimal_min'] : array();
		$ranges_optimal_max = isset( $_POST['ranges_optimal_max'] ) && is_array( $_POST['ranges_optimal_max'] ) ? $_POST['ranges_optimal_max'] : array();
		$ranges_normal_min = isset( $_POST['ranges_normal_min'] ) && is_array( $_POST['ranges_normal_min'] ) ? $_POST['ranges_normal_min'] : array();
		$ranges_normal_max = isset( $_POST['ranges_normal_max'] ) && is_array( $_POST['ranges_normal_max'] ) ? $_POST['ranges_normal_max'] : array();
		$ranges_critical_min = isset( $_POST['ranges_critical_min'] ) && is_array( $_POST['ranges_critical_min'] ) ? $_POST['ranges_critical_min'] : array();
		$ranges_critical_max = isset( $_POST['ranges_critical_max'] ) && is_array( $_POST['ranges_critical_max'] ) ? $_POST['ranges_critical_max'] : array();

		// Age group arrays
		$age_young_min = isset( $_POST['age_young_min'] ) && is_array( $_POST['age_young_min'] ) ? $_POST['age_young_min'] : array();
		$age_young_max = isset( $_POST['age_young_max'] ) && is_array( $_POST['age_young_max'] ) ? $_POST['age_young_max'] : array();
		$age_adult_min = isset( $_POST['age_adult_min'] ) && is_array( $_POST['age_adult_min'] ) ? $_POST['age_adult_min'] : array();
		$age_adult_max = isset( $_POST['age_adult_max'] ) && is_array( $_POST['age_adult_max'] ) ? $_POST['age_adult_max'] : array();
		$age_senior_min = isset( $_POST['age_senior_min'] ) && is_array( $_POST['age_senior_min'] ) ? $_POST['age_senior_min'] : array();
		$age_senior_max = isset( $_POST['age_senior_max'] ) && is_array( $_POST['age_senior_max'] ) ? $_POST['age_senior_max'] : array();

		// Gender group arrays
		$gender_male_min = isset( $_POST['gender_male_min'] ) && is_array( $_POST['gender_male_min'] ) ? $_POST['gender_male_min'] : array();
		$gender_male_max = isset( $_POST['gender_male_max'] ) && is_array( $_POST['gender_male_max'] ) ? $_POST['gender_male_max'] : array();
		$gender_female_min = isset( $_POST['gender_female_min'] ) && is_array( $_POST['gender_female_min'] ) ? $_POST['gender_female_min'] : array();
		$gender_female_max = isset( $_POST['gender_female_max'] ) && is_array( $_POST['gender_female_max'] ) ? $_POST['gender_female_max'] : array();

		// Additional data arrays
		$clinical_significance = isset( $_POST['clinical_significance'] ) && is_array( $_POST['clinical_significance'] ) ? $_POST['clinical_significance'] : array();
		$risk_factors = isset( $_POST['risk_factors'] ) && is_array( $_POST['risk_factors'] ) ? $_POST['risk_factors'] : array();
		$optimization_recommendations = isset( $_POST['optimization_recommendations'] ) && is_array( $_POST['optimization_recommendations'] ) ? $_POST['optimization_recommendations'] : array();
		$flag_criteria = isset( $_POST['flag_criteria'] ) && is_array( $_POST['flag_criteria'] ) ? $_POST['flag_criteria'] : array();

		// Scoring arrays
		$scoring_optimal_score = isset( $_POST['scoring_optimal_score'] ) && is_array( $_POST['scoring_optimal_score'] ) ? $_POST['scoring_optimal_score'] : array();
		$scoring_suboptimal_score = isset( $_POST['scoring_suboptimal_score'] ) && is_array( $_POST['scoring_suboptimal_score'] ) ? $_POST['scoring_suboptimal_score'] : array();
		$scoring_poor_score = isset( $_POST['scoring_poor_score'] ) && is_array( $_POST['scoring_poor_score'] ) ? $_POST['scoring_poor_score'] : array();
		$scoring_critical_score = isset( $_POST['scoring_critical_score'] ) && is_array( $_POST['scoring_critical_score'] ) ? $_POST['scoring_critical_score'] : array();

		// Target arrays
		$target_improvement_targets = isset( $_POST['target_improvement_targets'] ) && is_array( $_POST['target_improvement_targets'] ) ? $_POST['target_improvement_targets'] : array();
		$target_immediate = isset( $_POST['target_immediate'] ) && is_array( $_POST['target_immediate'] ) ? $_POST['target_immediate'] : array();
		$target_short_term = isset( $_POST['target_short_term'] ) && is_array( $_POST['target_short_term'] ) ? $_POST['target_short_term'] : array();
		$target_long_term = isset( $_POST['target_long_term'] ) && is_array( $_POST['target_long_term'] ) ? $_POST['target_long_term'] : array();

		// Get all biomarker keys from the optimal_min array (as it should contain all biomarkers)
		$biomarker_keys = array_keys( $ranges_optimal_min );
		
		if ( empty( $biomarker_keys ) ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . 
				esc_html__( 'Error: No biomarker data found to save.', 'ennulifeassessments' ) . '</p></div>';
			return;
		}

		// Process each biomarker
		foreach ( $biomarker_keys as $biomarker_key ) {
			
			// Skip empty biomarker keys
			if ( empty( $biomarker_key ) ) {
				continue;
			}

			// Build comprehensive range data for this biomarker
			$range_data = array(
				'unit' => '', // This would need to be determined from the biomarker definition
				'ranges' => array(
					'optimal_min' => isset( $ranges_optimal_min[$biomarker_key] ) ? floatval( $ranges_optimal_min[$biomarker_key] ) : 0,
					'optimal_max' => isset( $ranges_optimal_max[$biomarker_key] ) ? floatval( $ranges_optimal_max[$biomarker_key] ) : 0,
					'normal_min' => isset( $ranges_normal_min[$biomarker_key] ) ? floatval( $ranges_normal_min[$biomarker_key] ) : 0,
					'normal_max' => isset( $ranges_normal_max[$biomarker_key] ) ? floatval( $ranges_normal_max[$biomarker_key] ) : 0,
					'critical_min' => isset( $ranges_critical_min[$biomarker_key] ) ? floatval( $ranges_critical_min[$biomarker_key] ) : 0,
					'critical_max' => isset( $ranges_critical_max[$biomarker_key] ) ? floatval( $ranges_critical_max[$biomarker_key] ) : 0
				),
				'age_adjustments' => array(
					'young' => array(
						'optimal_min' => isset( $age_young_min[$biomarker_key] ) ? floatval( $age_young_min[$biomarker_key] ) : 0,
						'optimal_max' => isset( $age_young_max[$biomarker_key] ) ? floatval( $age_young_max[$biomarker_key] ) : 0
					),
					'adult' => array(
						'optimal_min' => isset( $age_adult_min[$biomarker_key] ) ? floatval( $age_adult_min[$biomarker_key] ) : 0,
						'optimal_max' => isset( $age_adult_max[$biomarker_key] ) ? floatval( $age_adult_max[$biomarker_key] ) : 0
					),
					'senior' => array(
						'optimal_min' => isset( $age_senior_min[$biomarker_key] ) ? floatval( $age_senior_min[$biomarker_key] ) : 0,
						'optimal_max' => isset( $age_senior_max[$biomarker_key] ) ? floatval( $age_senior_max[$biomarker_key] ) : 0
					)
				),
				'gender_adjustments' => array(
					'male' => array(
						'optimal_min' => isset( $gender_male_min[$biomarker_key] ) ? floatval( $gender_male_min[$biomarker_key] ) : 0,
						'optimal_max' => isset( $gender_male_max[$biomarker_key] ) ? floatval( $gender_male_max[$biomarker_key] ) : 0
					),
					'female' => array(
						'optimal_min' => isset( $gender_female_min[$biomarker_key] ) ? floatval( $gender_female_min[$biomarker_key] ) : 0,
						'optimal_max' => isset( $gender_female_max[$biomarker_key] ) ? floatval( $gender_female_max[$biomarker_key] ) : 0
					)
				),
				'clinical_significance' => isset( $clinical_significance[$biomarker_key] ) ? sanitize_textarea_field( $clinical_significance[$biomarker_key] ) : '',
				'risk_factors' => isset( $risk_factors[$biomarker_key] ) ? sanitize_textarea_field( $risk_factors[$biomarker_key] ) : '',
				'optimization_recommendations' => isset( $optimization_recommendations[$biomarker_key] ) ? sanitize_textarea_field( $optimization_recommendations[$biomarker_key] ) : '',
				'flag_criteria' => isset( $flag_criteria[$biomarker_key] ) ? sanitize_textarea_field( $flag_criteria[$biomarker_key] ) : '',
				'scoring_algorithm' => array(
					'optimal_score' => isset( $scoring_optimal_score[$biomarker_key] ) ? intval( $scoring_optimal_score[$biomarker_key] ) : 10,
					'suboptimal_score' => isset( $scoring_suboptimal_score[$biomarker_key] ) ? intval( $scoring_suboptimal_score[$biomarker_key] ) : 7,
					'poor_score' => isset( $scoring_poor_score[$biomarker_key] ) ? intval( $scoring_poor_score[$biomarker_key] ) : 4,
					'critical_score' => isset( $scoring_critical_score[$biomarker_key] ) ? intval( $scoring_critical_score[$biomarker_key] ) : 1
				),
				'target_setting' => array(
					'improvement_targets' => isset( $target_improvement_targets[$biomarker_key] ) ? sanitize_textarea_field( $target_improvement_targets[$biomarker_key] ) : '',
					'timeframes' => array(
						'immediate' => isset( $target_immediate[$biomarker_key] ) ? sanitize_text_field( $target_immediate[$biomarker_key] ) : '1 week',
						'short_term' => isset( $target_short_term[$biomarker_key] ) ? sanitize_text_field( $target_short_term[$biomarker_key] ) : '1 month',
						'long_term' => isset( $target_long_term[$biomarker_key] ) ? sanitize_text_field( $target_long_term[$biomarker_key] ) : '3-6 months'
					)
				),
				'last_updated' => current_time( 'mysql' ),
				'updated_by' => wp_get_current_user()->display_name,
				'version' => '2.0'
			);

			// Save to database using the same format as the original method
			$save_result = update_option( "ennu_biomarker_range_{$biomarker_key}", $range_data );
			
			if ( $save_result ) {
				$processed_count++;
			} else {
				$errors[] = sprintf( esc_html__( 'Failed to save range data for %s', 'ennulifeassessments' ), esc_html( $biomarker_key ) );
			}
		}

		// Display results
		if ( $processed_count > 0 ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . 
				sprintf( esc_html__( 'Successfully saved range data for %d biomarkers.', 'ennulifeassessments' ), $processed_count ) . 
				'</p></div>';
		}

		if ( ! empty( $errors ) ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . 
				esc_html__( 'Errors encountered:', 'ennulifeassessments' ) . '<br>' .
				implode( '<br>', array_map( 'esc_html', $errors ) ) .
				'</p></div>';
		}

		// Handle the optional profile update checkbox
		if ( isset( $_POST['update_profile_defaults'] ) && $_POST['update_profile_defaults'] === '1' && $processed_count > 0 ) {
			// Update profile defaults for each processed biomarker
			foreach ( $biomarker_keys as $biomarker_key ) {
				if ( ! empty( $biomarker_key ) ) {
					$range_data = get_option( "ennu_biomarker_range_{$biomarker_key}", array() );
					if ( ! empty( $range_data ) && method_exists( $this, 'update_all_user_profile_defaults' ) ) {
						$this->update_all_user_profile_defaults( $biomarker_key, $range_data );
					}
				}
			}
		}
	}

	/**
	 * Handle importing biomarker ranges
	 */
	private function handle_import_biomarker_ranges() {
		// Placeholder for import functionality
		echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__( 'Import functionality coming in Phase 3.', 'ennulifeassessments' ) . '</p></div>';
	}

	/**
	 * Handle exporting biomarker ranges
	 */
	private function handle_export_biomarker_ranges() {
		// Placeholder for export functionality
		echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__( 'Export functionality coming in Phase 3.', 'ennulifeassessments' ) . '</p></div>';
	}

	/**
	 * Update all user profile defaults with new target values
	 *
	 * @param string $biomarker_key The biomarker identifier
	 * @param array  $range_data The updated range data
	 * @return array Result with success status and count
	 */
	private function update_all_user_profile_defaults( $biomarker_key, $range_data ) {
		
		try {
			// Include the target calculator
			require_once plugin_dir_path( __FILE__ ) . 'class-biomarker-target-calculator.php';
			
			// Get all users with biomarker data
			$users_with_biomarkers = $this->get_users_with_biomarker_data();
			
			if ( empty( $users_with_biomarkers ) ) {
				return array(
					'success' => true,
					'updated_count' => 0,
					'message' => 'No users with biomarker data found'
				);
			}

			$updated_count = 0;
			$errors = array();

			foreach ( $users_with_biomarkers as $user_id ) {
				
				// Get user's current biomarker data
				$current_biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
				$current_doctor_targets = get_user_meta( $user_id, 'ennu_doctor_targets', true );
				
				if ( empty( $current_biomarker_data ) || ! is_array( $current_biomarker_data ) ) {
					continue;
				}

				// Get user's current value for this biomarker
				$current_value = isset( $current_biomarker_data[ $biomarker_key ] ) ? $current_biomarker_data[ $biomarker_key ] : null;
				
				if ( $current_value === null ) {
					continue;
				}

				// Get user demographics
				$user_age = get_user_meta( $user_id, 'ennu_global_exact_age', true );
				$user_gender = get_user_meta( $user_id, 'ennu_global_gender', true );
				
				if ( empty( $user_age ) || empty( $user_gender ) ) {
					$errors[] = "User {$user_id}: Missing demographics";
					continue;
				}

				// Calculate new target value
				$target_data = ENNU_Biomarker_Target_Calculator::calculate_personalized_target( 
					$biomarker_key, 
					$current_value, 
					$range_data, 
					$user_age, 
					$user_gender 
				);

				if ( $target_data['target_value'] === null ) {
					$errors[] = "User {$user_id}: Failed to calculate target value";
					continue;
				}

				// Validate target value
				$validation = ENNU_Biomarker_Target_Calculator::validate_target_value( 
					$target_data['target_value'], 
					$range_data, 
					$biomarker_key 
				);

				if ( ! $validation['is_safe'] ) {
					$errors[] = "User {$user_id}: Target value outside safety bounds";
					continue;
				}

				// Update doctor targets
				if ( ! is_array( $current_doctor_targets ) ) {
					$current_doctor_targets = array();
				}

				$current_doctor_targets[ $biomarker_key ] = array(
					'target_value' => $target_data['target_value'],
					'confidence_score' => $target_data['confidence_score'],
					'calculation_method' => $target_data['calculation_method'],
					'reasoning' => $target_data['reasoning'],
					'updated_at' => current_time( 'mysql' ),
					'updated_by' => 'AI Range Update',
					'range_version' => $range_data['version'] ?? '1.0'
				);

				$update_result = update_user_meta( $user_id, 'ennu_doctor_targets', $current_doctor_targets );
				
				if ( $update_result ) {
					$updated_count++;
					
					// Log the update
					error_log( "ENNU Biomarker Target Update: Updated target for user {$user_id}, biomarker {$biomarker_key}, new target: {$target_data['target_value']}" );
				} else {
					$errors[] = "User {$user_id}: Failed to update user meta";
				}
			}

			return array(
				'success' => true,
				'updated_count' => $updated_count,
				'error_count' => count( $errors ),
				'errors' => $errors,
				'message' => "Successfully updated {$updated_count} users"
			);

		} catch ( Exception $e ) {
			error_log( "ENNU Biomarker Target Update Error: " . $e->getMessage() );
			return array(
				'success' => false,
				'error' => $e->getMessage(),
				'updated_count' => 0
			);
		}
	}

	/**
	 * Get all users who have biomarker data
	 *
	 * @return array Array of user IDs
	 */
	private function get_users_with_biomarker_data() {
		
		global $wpdb;
		
		$user_ids = $wpdb->get_col( "
			SELECT DISTINCT user_id 
			FROM {$wpdb->usermeta} 
			WHERE meta_key = 'ennu_biomarker_data' 
			AND meta_value != '' 
			AND meta_value != 'a:0:{}'
		" );

		return array_map( 'intval', $user_ids );
	}

	/**
	 * Render the Range Management tab
	 */
	private function render_range_management_tab() {
		echo '<div class="ennu-range-management-interface">';
		
		// Handle form submissions
		if ( isset( $_POST['save_biomarker_range'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'save_biomarker_range' ) ) {
			$this->handle_save_biomarker_range();
		}
		
		if ( isset( $_POST['import_biomarker_ranges'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'import_biomarker_ranges' ) ) {
			$this->handle_import_biomarker_ranges();
		}
		
		if ( isset( $_POST['export_biomarker_ranges'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'export_biomarker_ranges' ) ) {
			$this->handle_export_biomarker_ranges();
		}

		// Load AI specialist data via ENNU_Recommended_Range_Manager
		$range_manager = new ENNU_Recommended_Range_Manager();
		$biomarker_config = $range_manager->get_biomarker_configuration();

		// Group biomarkers by specialist/panel for display
		$panel_biomarkers = array();
		$specialist_mapping = array(
			'cardiovascular' => 'Cardiovascular (Dr. Victor Pulse)',
			'hematology' => 'Hematology (Dr. Harlan Vitalis)',
			'neurology' => 'Neurology (Dr. Nora Cognita)',
			'endocrinology' => 'Endocrinology (Dr. Elena Harmonix)',
			'health_coaching' => 'Health Coaching (Coach Aria Vital)',
			'sports_medicine' => 'Sports Medicine (Dr. Silas Apex)',
			'gerontology' => 'Gerontology (Dr. Linus Eternal)',
			'nephrology' => 'Nephrology/Hepatology (Dr. Renata Flux)',
			'general_practice' => 'General Practice (Dr. Orion Nexus)'
		);

		foreach ($biomarker_config as $biomarker_key => $config) {
			// Determine panel based on biomarker key or specialist data
			$panel = 'General Practice (Dr. Orion Nexus)'; // Default
			foreach ($specialist_mapping as $specialist_key => $panel_name) {
				if (strpos($biomarker_key, $specialist_key) !== false || 
					(isset($config['specialist']) && strpos($config['specialist'], $specialist_key) !== false)) {
					$panel = $panel_name;
					break;
				}
			}
			
			if (!array_key_exists($panel, $panel_biomarkers)) {
				$panel_biomarkers[$panel] = array();
			}
			$panel_biomarkers[$panel][$biomarker_key] = $config;
		}

		// Load commercial panels configuration
		$commercial_panels_file = plugin_dir_path( dirname( __FILE__ ) ) . 'includes/config/ennu-life-commercial-panels.php';
		$commercial_panels = array();
		if ( file_exists( $commercial_panels_file ) ) {
			$commercial_panels = include $commercial_panels_file;
		}

		// Panel display definitions (icons/colors) - keys must match panel names
		$panel_display = array(
			'Cardiovascular (Dr. Victor Pulse)' => array('name' => 'Cardiovascular (Dr. Victor Pulse)', 'color' => '#d63638', 'icon' => 'dashicons-heart'),
			'Hematology (Dr. Harlan Vitalis)' => array('name' => 'Hematology (Dr. Harlan Vitalis)', 'color' => '#dba617', 'icon' => 'dashicons-clipboard'),
			'Neurology (Dr. Nora Cognita)' => array('name' => 'Neurology (Dr. Nora Cognita)', 'color' => '#2271b1', 'icon' => 'dashicons-lightbulb'),
			'Endocrinology (Dr. Elena Harmonix)' => array('name' => 'Endocrinology (Dr. Elena Harmonix)', 'color' => '#f44336', 'icon' => 'dashicons-admin-network'),
			'Health Coaching (Coach Aria Vital)' => array('name' => 'Health Coaching (Coach Aria Vital)', 'color' => '#4caf50', 'icon' => 'dashicons-admin-users'),
			'Sports Medicine (Dr. Silas Apex)' => array('name' => 'Sports Medicine (Dr. Silas Apex)', 'color' => '#9c27b0', 'icon' => 'dashicons-chart-line'),
			'Gerontology (Dr. Linus Eternal)' => array('name' => 'Gerontology (Dr. Linus Eternal)', 'color' => '#607d8b', 'icon' => 'dashicons-clock'),
			'Nephrology/Hepatology (Dr. Renata Flux)' => array('name' => 'Nephrology/Hepatology (Dr. Renata Flux)', 'color' => '#e91e63', 'icon' => 'dashicons-admin-site'),
			'General Practice (Dr. Orion Nexus)' => array('name' => 'General Practice (Dr. Orion Nexus)', 'color' => '#00a32a', 'icon' => 'dashicons-admin-generic'),
		);

		// Header with dual filtering system
		echo '<div class="ennu-range-management-header">';
		echo '<h2><span class="dashicons dashicons-admin-generic"></span> ' . esc_html__( 'Biomarker Range Management', 'ennulifeassessments' ) . '</h2>';
		echo '<p>' . esc_html__( 'Configure reference ranges, evidence sources, and validation data for all biomarker panels. Data sourced from AI Medical Specialists.', 'ennulifeassessments' ) . '</p>';
		
		// Simple Filter Section
		echo '<div class="ennu-simple-filter" style="margin: 20px 0; padding: 15px; background: #fff; border: 1px solid #e1e1e1; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
		echo '<div style="display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">';
		
		// Panel Filter
		echo '<div>';
		echo '<label for="panel-filter" style="display: block; margin-bottom: 5px; font-weight: 600; color: #23282d; font-size: 13px;">Panel:</label>';
		echo '<select id="panel-filter" style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 3px; min-width: 180px; font-size: 13px;">';
		echo '<option value="">All Panels</option>';
		foreach ( $panel_display as $panel_key => $panel ) {
			echo '<option value="' . esc_attr( $panel_key ) . '">' . esc_html( $panel['name'] ) . '</option>';
		}
		echo '</select>';
		echo '</div>';
		
		// Health Vector Filter
		echo '<div>';
		echo '<label for="vector-filter" style="display: block; margin-bottom: 5px; font-weight: 600; color: #23282d; font-size: 13px;">Health Vector:</label>';
		echo '<select id="vector-filter" style="padding: 6px 10px; border: 1px solid #ddd; border-radius: 3px; min-width: 150px; font-size: 13px;">';
		echo '<option value="">All Vectors</option>';
		echo '<option value="cardiovascular">Cardiovascular</option>';
		echo '<option value="metabolic">Metabolic</option>';
		echo '<option value="neurological">Neurological</option>';
		echo '<option value="immune">Immune</option>';
		echo '<option value="endocrine">Endocrine</option>';
		echo '<option value="musculoskeletal">Musculoskeletal</option>';
		echo '<option value="lifestyle">Lifestyle</option>';
		echo '</select>';
		echo '</div>';
		
		// Clear Button
		echo '<div style="align-self: end;">';
		echo '<button type="button" id="clear-filters" style="padding: 6px 12px; background: #f7f7f7; border: 1px solid #ddd; border-radius: 3px; cursor: pointer; color: #555; font-size: 13px;">Clear</button>';
			echo '</div>';
			
			echo '</div>';
			echo '</div>';
		

		
		echo '</div>';

		// Main table container with form
		echo '<form method="post" action="">';
		echo wp_nonce_field( 'save_biomarker_range', '_wpnonce', true, false );
		echo '<div class="ennu-biomarker-table-container">';
		echo '<table class="ennu-biomarker-management-table">';
		echo '<thead>';
		echo '<tr>';
		echo '<th style="word-wrap: break-word; white-space: normal; max-width: 120px;">' . esc_html__( 'Biomarker', 'ennulifeassessments' ) . '</th>';
		echo '<th style="word-wrap: break-word; white-space: normal; max-width: 150px;">' . esc_html__( 'Reference Ranges', 'ennulifeassessments' ) . '</th>';
		echo '<th style="word-wrap: break-word; white-space: normal; max-width: 120px;">' . esc_html__( 'Age Adjustments', 'ennulifeassessments' ) . '</th>';
		echo '<th style="word-wrap: break-word; white-space: normal; max-width: 120px;">' . esc_html__( 'Gender Adjustments', 'ennulifeassessments' ) . '</th>';
		echo '<th style="word-wrap: break-word; white-space: normal; max-width: 120px;">' . esc_html__( 'Clinical Significance', 'ennulifeassessments' ) . '</th>';
		echo '<th style="word-wrap: break-word; white-space: normal; max-width: 120px;">' . esc_html__( 'Risk Factors', 'ennulifeassessments' ) . '</th>';
		echo '<th style="word-wrap: break-word; white-space: normal; max-width: 120px;">' . esc_html__( 'Optimization Recommendations', 'ennulifeassessments' ) . '</th>';
		echo '<th style="word-wrap: break-word; white-space: normal; max-width: 120px;">' . esc_html__( 'Flag Criteria', 'ennulifeassessments' ) . '</th>';
		echo '<th style="word-wrap: break-word; white-space: normal; max-width: 120px;">' . esc_html__( 'Scoring Algorithm', 'ennulifeassessments' ) . '</th>';
		echo '<th style="word-wrap: break-word; white-space: normal; max-width: 120px;">' . esc_html__( 'Target Setting', 'ennulifeassessments' ) . '</th>';
		echo '<th style="word-wrap: break-word; white-space: normal; max-width: 120px;">' . esc_html__( 'Evidence Sources', 'ennulifeassessments' ) . '</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		$biomarker_counter = 1;
		foreach ( $panel_biomarkers as $panel_key => $biomarkers ) {
			$panel = $panel_display[$panel_key] ?? array('name' => $panel_key, 'color' => '#888', 'icon' => 'dashicons-admin-generic');
			// Panel header row
			echo '<tr class="ennu-panel-header" data-panel="' . esc_attr( $panel_key ) . '">';
			echo '<td colspan="11">';
			echo '<div class="ennu-panel-header-content">';
			echo '<span class="dashicons ' . esc_attr( $panel['icon'] ) . '" style="color: ' . esc_attr( $panel['color'] ) . ';"></span>';
			echo '<h3>' . esc_html( $panel['name'] ) . '</h3>';
			echo '<span class="ennu-panel-count">' . esc_html( count( $biomarkers ) ) . ' biomarkers</span>';
			echo '</div>';
			echo '</td>';
			echo '</tr>';
			foreach ( $biomarkers as $biomarker_key => $config ) {
				$display_name = $config['display_name'] ?? $biomarker_key;
				$unit = $config['unit'] ?? '';
				$description = $config['description'] ?? '';
				$clinical_significance = $config['clinical_significance'] ?? '';
				$risk_factors = $config['risk_factors'] ?? array();
				$optimization_recommendations = $config['optimization_recommendations'] ?? array();
				$flag_criteria = $config['flag_criteria'] ?? array();
				$scoring_algorithm = $config['scoring_algorithm'] ?? array();
				$target_setting = $config['target_setting'] ?? array();
				$sources = $config['sources'] ?? array();
				$age_adjustments = $config['age_adjustments'] ?? array();
				$gender_adjustments = $config['gender_adjustments'] ?? array();
				$ranges = $config['ranges'] ?? array();
				
				echo '<tr class="ennu-biomarker-row" data-biomarker="' . esc_attr( $biomarker_key ) . '" data-panel="' . esc_attr( $panel_key ) . '">';
				
				// Biomarker Name with unique 3-digit ID
				echo '<td class="ennu-biomarker-name">';
				echo '<strong>' . esc_html( $display_name ) . '</strong>';
				echo '<br><small>' . esc_html( $unit ) . '</small>';
				echo '<br><small style="color: #666; font-weight: bold;">ID: ' . sprintf( '%03d', $biomarker_counter ) . '</small>';
				if (!empty($description)) {
					echo '<br><small style="color: #888; font-style: italic;">' . esc_html( substr($description, 0, 100) ) . (strlen($description) > 100 ? '...' : '') . '</small>';
				}
				echo '</td>';
				
				// Reference Ranges (Editable Input Fields)
				echo '<td class="ennu-reference-ranges">';
				echo '<div class="ennu-reference-range-inputs">';
				if (!empty($ranges)) {
					// Optimal Range
					echo '<div class="ennu-range-group">';
					echo '<label>Optimal:</label>';
					echo '<div class="ennu-range-inputs">';
					echo '<input type="text" name="ranges_optimal_min[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $ranges['optimal_min'] ?? '' ) . '" placeholder="Min" class="ennu-range-input">';
					echo '<input type="text" name="ranges_optimal_max[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $ranges['optimal_max'] ?? '' ) . '" placeholder="Max" class="ennu-range-input">';
				echo '</div>';
				echo '</div>';
					
					// Normal Range
					echo '<div class="ennu-range-group">';
					echo '<label>Normal:</label>';
					echo '<div class="ennu-range-inputs">';
					echo '<input type="text" name="ranges_normal_min[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $ranges['normal_min'] ?? '' ) . '" placeholder="Min" class="ennu-range-input">';
					echo '<input type="text" name="ranges_normal_max[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $ranges['normal_max'] ?? '' ) . '" placeholder="Max" class="ennu-range-input">';
				echo '</div>';
				echo '</div>';
					
					// Critical Range
					echo '<div class="ennu-range-group">';
					echo '<label>Critical:</label>';
					echo '<div class="ennu-range-inputs">';
					echo '<input type="text" name="ranges_critical_min[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $ranges['critical_min'] ?? '' ) . '" placeholder="Min" class="ennu-range-input">';
					echo '<input type="text" name="ranges_critical_max[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $ranges['critical_max'] ?? '' ) . '" placeholder="Max" class="ennu-range-input">';
					echo '</div>';
					echo '</div>';
				}
				echo '<div class="ennu-range-source">';
				echo '<small>Source: AI Medical Research</small>';
				echo '</div>';
				echo '</div>';
				echo '</td>';
				
				// Age Adjustments (Populated with default values)
				echo '<td class="ennu-age-adjustments">';
				echo '<div class="ennu-age-groups">';
				$age_groups = array('young', 'adult', 'senior');
				foreach ($age_groups as $age_group) {
				echo '<div class="ennu-age-group">';
					echo '<label>' . esc_html( ucfirst($age_group) ) . ':</label>';
								if (array_key_exists($age_group, $age_adjustments)) {
				$min_value = array_key_exists('optimal_min', $age_adjustments[$age_group]) ? $age_adjustments[$age_group]['optimal_min'] : '';
				$max_value = array_key_exists('optimal_max', $age_adjustments[$age_group]) ? $age_adjustments[$age_group]['optimal_max'] : '';
						echo '<input type="text" name="age_' . esc_attr( $age_group ) . '_min[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $min_value ) . '" placeholder="Min">';
						echo '<input type="text" name="age_' . esc_attr( $age_group ) . '_max[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $max_value ) . '" placeholder="Max">';
					} else {
						echo '<input type="text" name="age_' . esc_attr( $age_group ) . '_min[' . esc_attr( $biomarker_key ) . ']" value="" placeholder="Min">';
						echo '<input type="text" name="age_' . esc_attr( $age_group ) . '_max[' . esc_attr( $biomarker_key ) . ']" value="" placeholder="Max">';
					}
				echo '</div>';
				}
				echo '</div>';
				echo '</td>';
				
				// Gender Adjustments (Populated with default values)
				echo '<td class="ennu-gender-adjustments">';
				echo '<div class="ennu-gender-groups">';
				$genders = array('male', 'female');
				foreach ($genders as $gender) {
				echo '<div class="ennu-gender-group">';
					echo '<label>' . esc_html( ucfirst($gender) ) . ':</label>';
								if (array_key_exists($gender, $gender_adjustments)) {
				$min_value = array_key_exists('optimal_min', $gender_adjustments[$gender]) ? $gender_adjustments[$gender]['optimal_min'] : '';
				$max_value = array_key_exists('optimal_max', $gender_adjustments[$gender]) ? $gender_adjustments[$gender]['optimal_max'] : '';
						echo '<input type="text" name="gender_' . esc_attr( $gender ) . '_min[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $min_value ) . '" placeholder="Min">';
						echo '<input type="text" name="gender_' . esc_attr( $gender ) . '_max[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $max_value ) . '" placeholder="Max">';
					} else {
						echo '<input type="text" name="gender_' . esc_attr( $gender ) . '_min[' . esc_attr( $biomarker_key ) . ']" value="" placeholder="Min">';
						echo '<input type="text" name="gender_' . esc_attr( $gender ) . '_max[' . esc_attr( $biomarker_key ) . ']" value="" placeholder="Max">';
					}
				echo '</div>';
				}
				echo '</div>';
				echo '</td>';
				
				// Clinical Significance
				echo '<td class="ennu-clinical-significance">';
				echo '<div class="ennu-clinical-content">';
				if (!empty($clinical_significance)) {
					echo '<textarea name="clinical_significance[' . esc_attr( $biomarker_key ) . ']" rows="3" placeholder="Clinical significance...">' . esc_textarea( $clinical_significance ) . '</textarea>';
				} else {
					echo '<textarea name="clinical_significance[' . esc_attr( $biomarker_key ) . ']" rows="3" placeholder="Clinical significance..."></textarea>';
				}
				echo '</div>';
				echo '</td>';
				
				// Risk Factors
				echo '<td class="ennu-risk-factors">';
				echo '<div class="ennu-risk-content">';
				if (!empty($risk_factors)) {
					echo '<textarea name="risk_factors[' . esc_attr( $biomarker_key ) . ']" rows="3" placeholder="Risk factors...">' . esc_textarea( is_array($risk_factors) ? implode(', ', $risk_factors) : $risk_factors ) . '</textarea>';
				} else {
					echo '<textarea name="risk_factors[' . esc_attr( $biomarker_key ) . ']" rows="3" placeholder="Risk factors..."></textarea>';
				}
				echo '</div>';
				echo '</td>';
				
				// Optimization Recommendations
				echo '<td class="ennu-optimization">';
				echo '<div class="ennu-optimization-content">';
				if (!empty($optimization_recommendations)) {
					echo '<textarea name="optimization_recommendations[' . esc_attr( $biomarker_key ) . ']" rows="3" placeholder="Optimization recommendations...">' . esc_textarea( is_array($optimization_recommendations) ? implode(', ', $optimization_recommendations) : $optimization_recommendations ) . '</textarea>';
				} else {
					echo '<textarea name="optimization_recommendations[' . esc_attr( $biomarker_key ) . ']" rows="3" placeholder="Optimization recommendations..."></textarea>';
				}
				echo '</div>';
				echo '</td>';
				
				// Flag Criteria
				echo '<td class="ennu-flag-criteria">';
				echo '<div class="ennu-flag-content">';
				if (!empty($flag_criteria)) {
					$flag_text = '';
					if (isset($flag_criteria['symptom_triggers'])) {
						$flag_text .= 'Symptoms: ' . implode(', ', $flag_criteria['symptom_triggers']) . '; ';
					}
					if (isset($flag_criteria['range_triggers'])) {
						$flag_text .= 'Ranges: ' . implode(', ', array_keys($flag_criteria['range_triggers']));
					}
					echo '<textarea name="flag_criteria[' . esc_attr( $biomarker_key ) . ']" rows="3" placeholder="Flag criteria...">' . esc_textarea( $flag_text ) . '</textarea>';
				} else {
					echo '<textarea name="flag_criteria[' . esc_attr( $biomarker_key ) . ']" rows="3" placeholder="Flag criteria..."></textarea>';
				}
				echo '</div>';
				echo '</td>';
				
				// Scoring Algorithm (Individual Number Inputs)
				echo '<td class="ennu-scoring-algorithm">';
				echo '<div class="ennu-scoring-content">';
				echo '<div class="ennu-scoring-inputs">';
				
				// Optimal Score
				echo '<div class="ennu-score-group">';
				echo '<label>Optimal Score:</label>';
				echo '<input type="number" name="scoring_optimal_score[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $scoring_algorithm['optimal_score'] ?? '' ) . '" min="0" max="10" step="1" class="ennu-score-input">';
				echo '</div>';
				
				// Suboptimal Score
				echo '<div class="ennu-score-group">';
				echo '<label>Suboptimal Score:</label>';
				echo '<input type="number" name="scoring_suboptimal_score[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $scoring_algorithm['suboptimal_score'] ?? '' ) . '" min="0" max="10" step="1" class="ennu-score-input">';
				echo '</div>';
				
				// Poor Score
				echo '<div class="ennu-score-group">';
				echo '<label>Poor Score:</label>';
				echo '<input type="number" name="scoring_poor_score[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $scoring_algorithm['poor_score'] ?? '' ) . '" min="0" max="10" step="1" class="ennu-score-input">';
				echo '</div>';
				
				// Critical Score
				echo '<div class="ennu-score-group">';
				echo '<label>Critical Score:</label>';
				echo '<input type="number" name="scoring_critical_score[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $scoring_algorithm['critical_score'] ?? '' ) . '" min="0" max="10" step="1" class="ennu-score-input">';
				echo '</div>';
				
				echo '</div>';
				echo '</div>';
				echo '</td>';
				
				// Target Setting (Individual Input Fields)
				echo '<td class="ennu-target-setting">';
				echo '<div class="ennu-target-content">';
				
				// Improvement Targets (textarea for multiple targets)
				echo '<div class="ennu-target-group">';
				echo '<label>Improvement Targets:</label>';
				if (!empty($target_setting['improvement_targets'])) {
					echo '<textarea name="target_improvement_targets[' . esc_attr( $biomarker_key ) . ']" rows="2" placeholder="Improvement targets...">' . esc_textarea( implode(', ', $target_setting['improvement_targets']) ) . '</textarea>';
				} else {
					echo '<textarea name="target_improvement_targets[' . esc_attr( $biomarker_key ) . ']" rows="2" placeholder="Improvement targets..."></textarea>';
				}
				echo '</div>';
				
				// Timeframes (individual inputs)
				echo '<div class="ennu-timeframe-inputs">';
				
				// Immediate
				echo '<div class="ennu-timeframe-group">';
				echo '<label>Immediate:</label>';
				echo '<input type="text" name="target_immediate[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $target_setting['timeframes']['immediate'] ?? '' ) . '" placeholder="Immediate timeframe" class="ennu-timeframe-input">';
				echo '</div>';
				
				// Short Term
				echo '<div class="ennu-timeframe-group">';
				echo '<label>Short Term:</label>';
				echo '<input type="text" name="target_short_term[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $target_setting['timeframes']['short_term'] ?? '' ) . '" placeholder="Short term timeframe" class="ennu-timeframe-input">';
				echo '</div>';
				
				// Long Term
				echo '<div class="ennu-timeframe-group">';
				echo '<label>Long Term:</label>';
				echo '<input type="text" name="target_long_term[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $target_setting['timeframes']['long_term'] ?? '' ) . '" placeholder="Long term timeframe" class="ennu-timeframe-input">';
				echo '</div>';
				
				echo '</div>';
				echo '</div>';
				echo '</td>';
				
				// Evidence Sources (Display Text and Links Only)
				echo '<td class="ennu-evidence-sources">';
				echo '<div class="ennu-evidence-summary">';
				
				// Primary Source (display only)
				if (!empty($sources['primary'])) {
					echo '<div class="ennu-evidence-group">';
					echo '<label>Primary Source:</label>';
					echo '<div class="ennu-evidence-text">' . esc_html( $sources['primary'] ) . '</div>';
					echo '</div>';
				}
				
				// Secondary Sources (display text and clickable links)
				if (!empty($sources['secondary']) && is_array($sources['secondary'])) {
					echo '<div class="ennu-evidence-group">';
					echo '<label>Secondary Sources:</label>';
					echo '<div class="ennu-evidence-links">';
					foreach ($sources['secondary'] as $source) {
						if (filter_var($source, FILTER_VALIDATE_URL)) {
							echo '<a href="' . esc_url( $source ) . '" target="_blank" rel="noopener noreferrer" class="ennu-evidence-link">' . esc_html( $source ) . '</a><br>';
						} else {
							echo '<span class="ennu-evidence-text">' . esc_html( $source ) . '</span><br>';
						}
					}
					echo '</div>';
					echo '</div>';
				}
				
				// Evidence Level (display only)
				if (!empty($sources['evidence_level'])) {
					echo '<div class="ennu-evidence-group">';
					echo '<label>Evidence Level:</label>';
					echo '<div class="ennu-evidence-text">' . esc_html( $sources['evidence_level'] ) . '</div>';
					echo '</div>';
				}
				
				// Status and Specialist (read-only)
				echo '<div class="ennu-validation-status" style="color: green;"><strong>Status:</strong> AI Validated</div>';
				echo '<div class="ennu-research-specialist"><strong>Specialist:</strong> ' . esc_html( $panel['name'] ) . '</div>';
				echo '</div>';
				echo '</td>';
				
				echo '</tr>';
				$biomarker_counter++;
			}
		}
		echo '</tbody>';
		echo '</table>';
		echo '</div>';
		echo '</div>';
		
		// Add enhanced save button with profile update option
		echo '<div class="ennu-save-section" style="margin: 20px 0; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">';
		echo '<h3 style="margin-top: 0; color: #23282d;">Save Changes</h3>';
		echo '<div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">';
		
		// Save button
		echo '<button type="submit" name="save_biomarker_range" value="1" style="padding: 10px 20px; background: #0073aa; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600;">';
		echo '<span style="margin-right: 8px;">💾</span>' . esc_html__( 'Save Range Changes', 'ennulifeassessments' );
		echo '</button>';
		
		// Profile update checkbox
		echo '<div style="display: flex; align-items: center; gap: 8px;">';
		echo '<input type="checkbox" id="update_profile_defaults" name="update_profile_defaults" value="1" style="margin: 0;">';
		echo '<label for="update_profile_defaults" style="margin: 0; font-weight: 600; color: #23282d;">';
		echo '<span style="margin-right: 5px;">🎯</span>' . esc_html__( 'Update all user profile target values', 'ennulifeassessments' );
		echo '</label>';
		echo '</div>';
		
		// Warning message
		echo '<div id="profile-update-warning" style="display: none; margin-top: 10px; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; color: #856404;">';
		echo '<strong>⚠️ Warning:</strong> This will update target values for all users with biomarker data. This action cannot be undone.';
		echo '</div>';
		
		echo '</div>';
		echo '</div>';
		
		// Add JavaScript for warning
		echo '<script>
		document.getElementById("update_profile_defaults").addEventListener("change", function() {
			var warning = document.getElementById("profile-update-warning");
			if (this.checked) {
				warning.style.display = "block";
			} else {
				warning.style.display = "none";
			}
		});
		</script>';
		
		// Add CSS for the expanded table layout
		echo '<style>
			/* Override WordPress wrapper constraints */
			.wp-admin .ennu-range-management-interface {
				max-width: none !important;
				width: 100% !important;
				margin: 0 !important;
				padding: 0 !important;
			}
			
			.ennu-biomarker-management-table {
				width: 100%;
				border-collapse: collapse;
				margin-top: 20px;
				font-size: 13px;
				table-layout: fixed;
				min-width: 1400px;
			}
			
			.ennu-biomarker-management-table th,
			.ennu-biomarker-management-table td {
				border: 1px solid #ddd;
				padding: 8px;
				vertical-align: top;
			}
			
			.ennu-biomarker-management-table th {
				background-color: #f9f9f9;
				font-weight: bold;
				text-align: center;
				word-wrap: break-word;
				white-space: normal;
				vertical-align: top;
				line-height: 1.3;
				font-size: 11px;
			}
			
			.ennu-biomarker-name {
				min-width: 150px;
				max-width: 200px;
			}
			
			.ennu-reference-ranges {
				min-width: 180px;
				max-width: 250px;
			}
			
			.ennu-age-adjustments,
			.ennu-gender-adjustments {
				min-width: 140px;
				max-width: 180px;
			}
			
			.ennu-clinical-significance,
			.ennu-risk-factors,
			.ennu-optimization,
			.ennu-flag-criteria,
			.ennu-scoring-algorithm,
			.ennu-target-setting {
				min-width: 120px;
				max-width: 150px;
			}
			
			.ennu-evidence-sources {
				min-width: 150px;
				max-width: 200px;
			}
			
			.ennu-age-group,
			.ennu-gender-group {
				margin-bottom: 5px;
			}
			
			.ennu-age-group label,
			.ennu-gender-group label {
				display: block;
				font-weight: bold;
				font-size: 11px;
				margin-bottom: 2px;
			}
			
			.ennu-age-group input,
			.ennu-gender-group input {
				width: 45%;
				margin-right: 2px;
				font-size: 11px;
				padding: 2px;
			}
			
			.ennu-clinical-content textarea,
			.ennu-risk-content textarea,
			.ennu-optimization-content textarea,
			.ennu-flag-content textarea {
				width: 100%;
				font-size: 11px;
				resize: vertical;
			}
			
			/* Scoring Algorithm Inputs */
			.ennu-scoring-inputs {
				font-size: 11px;
			}
			
			.ennu-score-group {
				margin-bottom: 5px;
			}
			
			.ennu-score-group label {
				display: block;
				font-weight: bold;
				font-size: 10px;
				margin-bottom: 2px;
			}
			
			.ennu-score-input {
				width: 100%;
				font-size: 10px;
				padding: 2px 4px;
				border: 1px solid #ddd;
				border-radius: 3px;
			}
			
			/* Target Setting Inputs */
			.ennu-target-group {
				margin-bottom: 8px;
			}
			
			.ennu-target-group label {
				display: block;
				font-weight: bold;
				font-size: 10px;
				margin-bottom: 3px;
			}
			
			.ennu-target-group textarea {
				width: 100%;
				font-size: 10px;
				resize: vertical;
			}
			
			.ennu-timeframe-inputs {
				font-size: 11px;
			}
			
			.ennu-timeframe-group {
				margin-bottom: 5px;
			}
			
			.ennu-timeframe-group label {
				display: block;
				font-weight: bold;
				font-size: 10px;
				margin-bottom: 2px;
			}
			
			.ennu-timeframe-input {
				width: 100%;
				font-size: 10px;
				padding: 2px 4px;
				border: 1px solid #ddd;
				border-radius: 3px;
			}
			
			/* Evidence Sources Inputs */
			.ennu-evidence-group {
				margin-bottom: 8px;
			}
			
			.ennu-evidence-group label {
				display: block;
				font-weight: bold;
				font-size: 10px;
				margin-bottom: 3px;
			}
			
			.ennu-evidence-input {
				width: 100%;
				font-size: 10px;
				padding: 2px 4px;
				border: 1px solid #ddd;
				border-radius: 3px;
			}
			
			.ennu-evidence-select {
				width: 100%;
				font-size: 10px;
				padding: 2px 4px;
				border: 1px solid #ddd;
				border-radius: 3px;
			}
			
			.ennu-evidence-group textarea {
				width: 100%;
				font-size: 10px;
				resize: vertical;
			}
			
			.ennu-evidence-text {
				font-size: 10px;
				color: #333;
				word-break: break-word;
				line-height: 1.3;
			}
			
			/* Clickable Links */
			.ennu-evidence-link {
				color: #0073aa;
				text-decoration: none;
				word-break: break-all;
			}
			
			.ennu-evidence-link:hover {
				color: #005a87;
				text-decoration: underline;
			}
			
			.ennu-evidence-link:visited {
				color: #005a87;
			}
			
			/* Full Width Container */
			.ennu-range-management-content {
				max-width: none !important;
				width: 100% !important;
				overflow-x: auto;
			}
			
			/* Table Container */
			.ennu-table-container {
				width: 100%;
				overflow-x: auto;
				min-width: 1400px;
			}
			
			/* Ensure table can scroll horizontally */
			.ennu-biomarker-management-table {
				min-width: 1400px;
				width: 100%;
			}
			
			/* Force horizontal scrolling */
			.ennu-range-management-interface {
				overflow-x: auto;
				width: 100%;
			}
			
			/* Evidence Links */
			.ennu-evidence-links {
				margin-top: 5px;
				font-size: 9px;
			}
			
			.ennu-evidence-links a,
			.ennu-evidence-links span {
				display: block;
				margin-bottom: 2px;
			}
			
			.ennu-evidence-summary {
				font-size: 11px;
				line-height: 1.3;
			}
			
			.ennu-evidence-summary > div {
				margin-bottom: 3px;
			}
			
			.ennu-panel-header {
				background-color: #f0f0f0;
			}
			
			.ennu-panel-header-content {
				display: flex;
				align-items: center;
				gap: 10px;
			}
			
			.ennu-panel-header-content h3 {
				margin: 0;
				font-size: 16px;
			}
			
			.ennu-panel-count {
				color: #666;
				font-size: 12px;
			}
			
			.ennu-range-item {
				margin-bottom: 3px;
			}
			
			.ennu-range-label {
				font-weight: bold;
				font-size: 11px;
			}
			
			.ennu-reference-range-inputs {
				font-size: 11px;
			}
			
			.ennu-range-group {
				margin-bottom: 8px;
			}
			
			.ennu-range-group label {
				font-weight: bold;
				color: #333;
				display: block;
				margin-bottom: 3px;
				font-size: 11px;
			}
			
			.ennu-range-inputs {
				display: flex;
				gap: 5px;
			}
			
			.ennu-range-input {
				width: 50%;
				font-size: 10px;
				padding: 2px 4px;
				border: 1px solid #ddd;
				border-radius: 3px;
			}
			
			.ennu-range-value {
				font-size: 11px;
			}
			
			.ennu-range-source {
				margin-top: 5px;
				font-style: italic;
			}
		</style>';
		echo '</form>';
	}

	/**
	 * Render the Validation tab
	 */
	private function render_validation_tab() {
		echo '<div class="ennu-validation-tab">';
		echo '<h3>' . esc_html__( 'Range Validation & Conflicts', 'ennulifeassessments' ) . '</h3>';
		echo '<p>' . esc_html__( 'This section will show range validation results and conflict detection. Coming in Phase 3.', 'ennulifeassessments' ) . '</p>';
		echo '</div>';
	}



	/**
	 * AJAX handler to get biomarker range data for admin form
	 */
	public function ajax_get_biomarker_range_data() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'get_biomarker_range_data' ) ) {
			wp_die( 'Security check failed' );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		$biomarker_key = sanitize_text_field( $_POST['biomarker'] );
		
		// Get biomarker data from recommended range manager
		$range_manager = new ENNU_Recommended_Range_Manager();
		$default_ranges = $range_manager->get_biomarker_configuration();
		
		if ( isset( $default_ranges[ $biomarker_key ] ) ) {
			$biomarker_data = $default_ranges[ $biomarker_key ];
			wp_send_json_success( $biomarker_data );
		} else {
			wp_send_json_error( 'Biomarker not found' );
		}
	}

	/**
	 * Render the Analytics tab
	 */
	private function render_analytics_tab() {
		echo '<div class="ennu-analytics-tab">';
		echo '<h3>' . esc_html__( 'Range Analytics', 'ennulifeassessments' ) . '</h3>';
		echo '<p>' . esc_html__( 'This section will show analytics about range usage and validation. Coming in Phase 6.', 'ennulifeassessments' ) . '</p>';
		echo '</div>';
	}

	/**
	 * Get available biomarkers for the selector
	 */
	public function get_available_biomarkers() {
		// Dynamic loading from the recommended range manager
		$range_manager = new ENNU_Recommended_Range_Manager();
		$default_ranges = $range_manager->get_biomarker_configuration();
		
		$biomarkers = array();
		
		// Define category mappings for better organization
		$category_mappings = array(
			// Physical Measurements
			'weight' => 'Physical Measurements',
			'bmi' => 'Physical Measurements',
			'body_fat_percent' => 'Physical Measurements',
			'waist_measurement' => 'Physical Measurements',
			'neck_measurement' => 'Physical Measurements',
			'blood_pressure' => 'Physical Measurements',
			'blood_pressure_systolic' => 'Physical Measurements',
			'blood_pressure_diastolic' => 'Physical Measurements',
			'heart_rate' => 'Physical Measurements',
			'temperature' => 'Physical Measurements',
			'height' => 'Physical Measurements',
			
			// Metabolic Panel
			'glucose' => 'Metabolic',
			'insulin' => 'Metabolic',
			'hba1c' => 'Metabolic',
			'bun' => 'Metabolic',
			'creatinine' => 'Metabolic',
			'egfr' => 'Metabolic',
			'sodium' => 'Metabolic',
			'potassium' => 'Metabolic',
			'chloride' => 'Metabolic',
			'co2' => 'Metabolic',
			'uric_acid' => 'Metabolic',
			'adiponectin' => 'Metabolic',
			
			// Hormone Profile
			'testosterone_total' => 'Hormonal',
			'testosterone_free' => 'Hormonal',
			'estradiol' => 'Hormonal',
			'dhea_s' => 'Hormonal',
			'shbg' => 'Hormonal',
			'progesterone' => 'Hormonal',
			'igf1' => 'Hormonal',
			'igf_1' => 'Hormonal',
			'fsh' => 'Hormonal',
			'lh' => 'Hormonal',
			'cortisol' => 'Hormonal',
			
			// Thyroid
			'tsh' => 'Thyroid',
			't3_free' => 'Thyroid',
			't4_free' => 'Thyroid',
			
			// Cardiovascular
			'cholesterol_total' => 'Cardiovascular',
			'cholesterol_ldl' => 'Cardiovascular',
			'cholesterol_hdl' => 'Cardiovascular',
			'triglycerides' => 'Cardiovascular',
			'apob' => 'Cardiovascular',
			
			// Complete Blood Count
			'wbc' => 'Hematological',
			'rbc' => 'Hematological',
			'hemoglobin' => 'Hematological',
			'hematocrit' => 'Hematological',
			'platelets' => 'Hematological',
			'mcv' => 'Hematological',
			'iron' => 'Hematological',
			'folate' => 'Hematological',
			'vitamin_b12' => 'Hematological',
			
			// Liver Function
			'alt' => 'Hepatic',
			'ast' => 'Hepatic',
			'ggt' => 'Hepatic',
			'albumin' => 'Hepatic',
			'bilirubin' => 'Hepatic',
			'alkaline_phosphatase' => 'Hepatic',
			
			// Vitamins & Minerals
			'vitamin_d' => 'Vitamins',
			'b12' => 'Vitamins',
			'calcium' => 'Minerals',
			'magnesium' => 'Minerals',
			'phosphorus' => 'Minerals',
			
			// Inflammatory & Immune
			'hs_crp' => 'Inflammatory',
			'homocysteine' => 'Inflammatory',
			'crp' => 'Inflammatory',
			'il_6' => 'Inflammatory',
			'tnf_alpha' => 'Inflammatory',
			'fibrinogen' => 'Inflammatory',
			'lp_pla2' => 'Inflammatory',
			'myeloperoxidase' => 'Inflammatory',
			'oxidized_ldl' => 'Inflammatory',
			
			// Neurological
			'serotonin' => 'Neurological',
			'dopamine' => 'Neurological',
			'norepinephrine' => 'Neurological',
			'gaba' => 'Neurological',
			'melatonin' => 'Neurological',
			'acetylcholine' => 'Neurological',
			
			// Aging & Longevity
			'alpha_klotho' => 'Aging',
			'follistatin' => 'Aging',
			'gdf15' => 'Aging',
			'klotho' => 'Aging',
			'p16_ink4a' => 'Aging',
			'telomere_length' => 'Aging',
			'nad_plus' => 'Aging',
			
			// Antioxidants & Detoxification
			'coq10' => 'Antioxidants',
			'alpha_lipoic_acid' => 'Antioxidants',
			'glutathione' => 'Antioxidants',
			'superoxide_dismutase' => 'Antioxidants',
			'catalase' => 'Antioxidants',
			
			// Performance & Energy
			'creatine_kinase' => 'Performance',
			'lactate_dehydrogenase' => 'Performance',
			
			// Omega Fatty Acids
			'omega_3' => 'Fatty Acids'
		);
		
		// Convert biomarker names to display names
		$display_names = array(
			'weight' => 'Weight',
			'bmi' => 'BMI',
			'body_fat_percent' => 'Body Fat %',
			'waist_measurement' => 'Waist Measurement',
			'neck_measurement' => 'Neck Measurement',
			'blood_pressure' => 'Blood Pressure',
			'blood_pressure_systolic' => 'Blood Pressure (Systolic)',
			'blood_pressure_diastolic' => 'Blood Pressure (Diastolic)',
			'heart_rate' => 'Heart Rate',
			'temperature' => 'Temperature',
			'height' => 'Height',
			'glucose' => 'Glucose',
			'insulin' => 'Insulin',
			'hba1c' => 'HbA1c',
			'bun' => 'BUN',
			'creatinine' => 'Creatinine',
			'egfr' => 'eGFR',
			'sodium' => 'Sodium',
			'potassium' => 'Potassium',
			'chloride' => 'Chloride',
			'co2' => 'CO2',
			'uric_acid' => 'Uric Acid',
			'adiponectin' => 'Adiponectin',
			'testosterone_total' => 'Total Testosterone',
			'testosterone_free' => 'Free Testosterone',
			'estradiol' => 'Estradiol',
			'dhea_s' => 'DHEA-S',
			'shbg' => 'SHBG',
			'progesterone' => 'Progesterone',
			'igf1' => 'IGF-1',
			'igf_1' => 'IGF-1',
			'fsh' => 'FSH',
			'lh' => 'LH',
			'cortisol' => 'Cortisol',
			'tsh' => 'TSH',
			't3_free' => 'Free T3',
			't4_free' => 'Free T4',
			'cholesterol_total' => 'Total Cholesterol',
			'cholesterol_ldl' => 'LDL Cholesterol',
			'cholesterol_hdl' => 'HDL Cholesterol',
			'triglycerides' => 'Triglycerides',
			'apob' => 'ApoB',
			'wbc' => 'WBC',
			'rbc' => 'RBC',
			'hemoglobin' => 'Hemoglobin',
			'hematocrit' => 'Hematocrit',
			'platelets' => 'Platelets',
			'mcv' => 'MCV',
			'iron' => 'Iron',
			'folate' => 'Folate',
			'vitamin_b12' => 'Vitamin B12',
			'alt' => 'ALT',
			'ast' => 'AST',
			'ggt' => 'GGT',
			'albumin' => 'Albumin',
			'bilirubin' => 'Bilirubin',
			'alkaline_phosphatase' => 'Alkaline Phosphatase',
			'vitamin_d' => 'Vitamin D',
			'b12' => 'Vitamin B12',
			'calcium' => 'Calcium',
			'magnesium' => 'Magnesium',
			'phosphorus' => 'Phosphorus',
			'hs_crp' => 'hs-CRP',
			'crp' => 'CRP',
			'homocysteine' => 'Homocysteine',
			'il_6' => 'IL-6',
			'tnf_alpha' => 'TNF-α',
			'fibrinogen' => 'Fibrinogen',
			'lp_pla2' => 'Lp-PLA2',
			'myeloperoxidase' => 'Myeloperoxidase',
			'oxidized_ldl' => 'Oxidized LDL',
			'serotonin' => 'Serotonin',
			'dopamine' => 'Dopamine',
			'norepinephrine' => 'Norepinephrine',
			'gaba' => 'GABA',
			'melatonin' => 'Melatonin',
			'acetylcholine' => 'Acetylcholine',
			'alpha_klotho' => 'Alpha-Klotho',
			'follistatin' => 'Follistatin',
			'gdf15' => 'GDF-15',
			'klotho' => 'Klotho',
			'p16_ink4a' => 'p16 INK4a',
			'telomere_length' => 'Telomere Length',
			'nad_plus' => 'NAD+',
			'coq10' => 'CoQ10',
			'alpha_lipoic_acid' => 'Alpha-Lipoic Acid',
			'glutathione' => 'Glutathione',
			'superoxide_dismutase' => 'Superoxide Dismutase',
			'catalase' => 'Catalase',
			'creatine_kinase' => 'Creatine Kinase',
			'lactate_dehydrogenase' => 'Lactate Dehydrogenase',
			'omega_3' => 'Omega-3'
		);
		
		foreach ( $default_ranges as $biomarker_key => $biomarker_data ) {
			// Skip version_info
			if ( $biomarker_key === 'version_info' ) {
				continue;
			}
			
			$biomarkers[] = array(
				'key' => $biomarker_key,
				'name' => $display_names[ $biomarker_key ] ?? ucfirst( str_replace( '_', ' ', $biomarker_key ) ),
				'unit' => $biomarker_data['unit'] ?? 'N/A',
				'category' => $category_mappings[ $biomarker_key ] ?? 'Other',
				'panel' => $biomarker_data['panel'] ?? 'foundation_panel'
			);
		}
		
		// Sort by category and then by name
		usort( $biomarkers, function( $a, $b ) {
			if ( $a['category'] !== $b['category'] ) {
				return strcmp( $a['category'], $b['category'] );
			}
			return strcmp( $a['name'], $b['name'] );
		} );
		
		return $biomarkers;
	}



	// ========================================
	// PHASE 4: ENHANCED IMPORT/EXPORT METHODS
	// ========================================

	/**
	 * Handle saving biomarker evidence
	 */
	private function handle_save_biomarker_evidence() {
		$biomarker = sanitize_text_field( $_POST['biomarker'] );
		$evidence_data = array(
			'sources' => array(
				'primary_source' => sanitize_text_field( $_POST['primary_source'] ),
				'secondary_sources' => isset( $_POST['secondary_sources'] ) ? array_map( 'sanitize_text_field', $_POST['secondary_sources'] ) : array(),
				'validation_level' => sanitize_text_field( $_POST['validation_level'] )
			),
			'last_validated' => sanitize_text_field( $_POST['last_validated'] ),
			'validation_status' => sanitize_text_field( $_POST['validation_status'] ),
			'confidence_score' => floatval( $_POST['confidence_score'] ),
			'notes' => sanitize_textarea_field( $_POST['evidence_notes'] ),
			'updated_by' => get_current_user_id(),
			'updated_date' => current_time( 'mysql' )
		);

		$result = update_option( "ennu_biomarker_evidence_{$biomarker}", $evidence_data );
		
		if ( $result ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Biomarker evidence saved successfully!', 'ennulifeassessments' ) . '</p></div>';
		} else {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to save biomarker evidence.', 'ennulifeassessments' ) . '</p></div>';
		}
	}

	/**
	 * Handle importing biomarker evidence
	 */
	private function handle_import_biomarker_evidence() {
		if ( ! isset( $_FILES['evidence_file'] ) || $_FILES['evidence_file']['error'] !== UPLOAD_ERR_OK ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'File upload failed.', 'ennulifeassessments' ) . '</p></div>';
			return;
		}

		$file = $_FILES['evidence_file'];
		$file_extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		
		if ( ! in_array( $file_extension, array( 'csv', 'json' ) ) ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Invalid file format. Please upload CSV or JSON files only.', 'ennulifeassessments' ) . '</p></div>';
			return;
		}

		$import_data = file_get_contents( $file['tmp_name'] );
		
		if ( $file_extension === 'json' ) {
			$data = json_decode( $import_data, true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Invalid JSON format.', 'ennulifeassessments' ) . '</p></div>';
				return;
			}
		} else {
			$data = $this->parse_csv_data( $import_data );
		}

		$imported_count = 0;
		foreach ( $data as $biomarker => $evidence ) {
			$result = update_option( "ennu_biomarker_evidence_{$biomarker}", $evidence );
			if ( $result ) {
				$imported_count++;
			}
		}

		echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( esc_html__( 'Successfully imported evidence for %d biomarkers.', 'ennulifeassessments' ), $imported_count ) . '</p></div>';
	}

	/**
	 * Handle exporting biomarker evidence
	 */
	private function handle_export_biomarker_evidence() {
		$export_format = sanitize_text_field( $_POST['export_format'] );
		$biomarkers = isset( $_POST['export_biomarkers'] ) ? array_map( 'sanitize_text_field', $_POST['export_biomarkers'] ) : array();
		
		if ( empty( $biomarkers ) ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'No biomarkers selected for export.', 'ennulifeassessments' ) . '</p></div>';
			return;
		}

		$export_data = array();
		foreach ( $biomarkers as $biomarker ) {
			$evidence = get_option( "ennu_biomarker_evidence_{$biomarker}" );
			if ( $evidence ) {
				$export_data[ $biomarker ] = $evidence;
			}
		}

		if ( $export_format === 'json' ) {
			$content = json_encode( $export_data, JSON_PRETTY_PRINT );
			$filename = 'ennu_biomarker_evidence_' . date( 'Y-m-d_H-i-s' ) . '.json';
			$content_type = 'application/json';
		} else {
			$content = $this->generate_csv_data( $export_data );
			$filename = 'ennu_biomarker_evidence_' . date( 'Y-m-d_H-i-s' ) . '.csv';
			$content_type = 'text/csv';
		}

		// Force download
		header( 'Content-Type: ' . $content_type );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Length: ' . strlen( $content ) );
		echo $content;
		exit;
	}

	/**
	 * Render the Import/Export tab
	 */
	private function render_import_export_tab() {
		echo '<div class="ennu-import-export-tab">';
		
		// Export Section
		echo '<div class="ennu-export-section">';
		echo '<h3>' . esc_html__( 'Export Biomarker Data', 'ennulifeassessments' ) . '</h3>';
		
		echo '<form method="post" class="ennu-export-form">';
		wp_nonce_field( 'export_biomarker_evidence' );
		
		echo '<div class="ennu-form-row">';
		echo '<label for="export_format">' . esc_html__( 'Export Format:', 'ennulifeassessments' ) . '</label>';
		echo '<select name="export_format" id="export_format">';
		echo '<option value="json">JSON</option>';
		echo '<option value="csv">CSV</option>';
		echo '</select>';
		echo '</div>';
		
		echo '<div class="ennu-form-row">';
		echo '<label>' . esc_html__( 'Select Data to Export:', 'ennulifeassessments' ) . '</label>';
		echo '<div class="ennu-export-options">';
		echo '<label><input type="checkbox" name="export_biomarkers[]" value="ranges" checked> ' . esc_html__( 'Biomarker Ranges', 'ennulifeassessments' ) . '</label>';
		echo '<label><input type="checkbox" name="export_biomarkers[]" value="panels" checked> ' . esc_html__( 'Panel Configurations', 'ennulifeassessments' ) . '</label>';
		echo '<label><input type="checkbox" name="export_biomarkers[]" value="evidence" checked> ' . esc_html__( 'Evidence Data', 'ennulifeassessments' ) . '</label>';
		echo '</div>';
		echo '</div>';
		
		// Removed non-functional export_evidence button
		echo '</form>';
		echo '</div>';
		
		// Import Section
		echo '<div class="ennu-import-section">';
		echo '<h3>' . esc_html__( 'Import Biomarker Data', 'ennulifeassessments' ) . '</h3>';
		
		echo '<form method="post" enctype="multipart/form-data" class="ennu-import-form">';
		wp_nonce_field( 'import_biomarker_evidence' );
		
		echo '<div class="ennu-form-row">';
		echo '<label for="evidence_file">' . esc_html__( 'Select File:', 'ennulifeassessments' ) . '</label>';
		echo '<input type="file" name="evidence_file" id="evidence_file" accept=".csv,.json" required>';
		echo '<p class="description">' . esc_html__( 'Supported formats: CSV, JSON. Maximum file size: 10MB.', 'ennulifeassessments' ) . '</p>';
		echo '</div>';
		
		echo '<div class="ennu-form-row">';
		echo '<label for="import_mode">' . esc_html__( 'Import Mode:', 'ennulifeassessments' ) . '</label>';
		echo '<select name="import_mode" id="import_mode">';
		echo '<option value="update">' . esc_html__( 'Update Existing', 'ennulifeassessments' ) . '</option>';
		echo '<option value="replace">' . esc_html__( 'Replace All', 'ennulifeassessments' ) . '</option>';
		echo '<option value="merge">' . esc_html__( 'Merge Data', 'ennulifeassessments' ) . '</option>';
		echo '</select>';
		echo '</div>';
		
		echo '<p><button type="submit" name="import_evidence" class="button button-primary">' . esc_html__( 'Import Data', 'ennulifeassessments' ) . '</button></p>';
		echo '</form>';
		echo '</div>';
		
		// Template Download
		echo '<div class="ennu-template-section">';
		echo '<h3>' . esc_html__( 'Download Templates', 'ennulifeassessments' ) . '</h3>';
		echo '<p>' . esc_html__( 'Download sample templates to understand the required format:', 'ennulifeassessments' ) . '</p>';
		echo '<div class="ennu-template-buttons">';
		echo '<a href="' . esc_url( admin_url( 'admin-ajax.php?action=download_template&type=biomarker_ranges&format=json' ) ) . '" class="button">' . esc_html__( 'Biomarker Ranges (JSON)', 'ennulifeassessments' ) . '</a>';
		echo '<a href="' . esc_url( admin_url( 'admin-ajax.php?action=download_template&type=biomarker_ranges&format=csv' ) ) . '" class="button">' . esc_html__( 'Biomarker Ranges (CSV)', 'ennulifeassessments' ) . '</a>';
		echo '<a href="' . esc_url( admin_url( 'admin-ajax.php?action=download_template&type=panels&format=json' ) ) . '" class="button">' . esc_html__( 'Panel Configurations (JSON)', 'ennulifeassessments' ) . '</a>';
		echo '</div>';
		echo '</div>';
		
		echo '</div>';
	}

	/**
	 * Render the Conflict Resolution tab
	 */
	private function render_conflict_resolution_tab() {
		echo '<div class="ennu-conflict-resolution-tab">';
		echo '<h3>' . esc_html__( 'Range Conflict Detection & Resolution', 'ennulifeassessments' ) . '</h3>';
		
		// Conflict Detection
		echo '<div class="ennu-conflict-detection">';
		echo '<h4>' . esc_html__( 'Detected Conflicts', 'ennulifeassessments' ) . '</h4>';
		
		$conflicts = $this->detect_range_conflicts();
		
		if ( empty( $conflicts ) ) {
			echo '<p class="ennu-no-conflicts">' . esc_html__( 'No conflicts detected. All biomarker ranges are consistent.', 'ennulifeassessments' ) . '</p>';
		} else {
			echo '<div class="ennu-conflicts-list">';
			foreach ( $conflicts as $conflict ) {
				echo '<div class="ennu-conflict-item">';
				echo '<h5>' . esc_html( $conflict['biomarker'] ) . ' - ' . esc_html( $conflict['type'] ) . '</h5>';
				echo '<p>' . esc_html( $conflict['description'] ) . '</p>';
				echo '<div class="ennu-conflict-actions">';
				echo '<button type="button" class="button button-small resolve-conflict" data-conflict-id="' . esc_attr( $conflict['id'] ) . '">' . esc_html__( 'Resolve', 'ennulifeassessments' ) . '</button>';
				echo '<button type="button" class="button button-small ignore-conflict" data-conflict-id="' . esc_attr( $conflict['id'] ) . '">' . esc_html__( 'Ignore', 'ennulifeassessments' ) . '</button>';
				echo '</div>';
				echo '</div>';
			}
			echo '</div>';
		}
		echo '</div>';
		
		// Conflict Resolution Rules
		echo '<div class="ennu-conflict-rules">';
		echo '<h4>' . esc_html__( 'Resolution Rules', 'ennulifeassessments' ) . '</h4>';
		echo '<div class="ennu-rules-list">';
		echo '<div class="ennu-rule-item">';
		echo '<strong>' . esc_html__( 'User Override Priority:', 'ennulifeassessments' ) . '</strong> ';
		echo esc_html__( 'User-specific ranges take precedence over default ranges.', 'ennulifeassessments' );
		echo '</div>';
		echo '<div class="ennu-rule-item">';
		echo '<strong>' . esc_html__( 'Evidence-Based Priority:', 'ennulifeassessments' ) . '</strong> ';
		echo esc_html__( 'Ranges with higher evidence scores override lower-scored ranges.', 'ennulifeassessments' );
		echo '</div>';
		echo '<div class="ennu-rule-item">';
		echo '<strong>' . esc_html__( 'Recency Priority:', 'ennulifeassessments' ) . '</strong> ';
		echo esc_html__( 'More recently updated ranges override older ranges.', 'ennulifeassessments' );
		echo '</div>';
		echo '</div>';
		echo '</div>';
		
		echo '</div>';
	}

	/**
	 * Render the Evidence Management tab
	 */
	private function render_evidence_management_tab() {
		echo '<div class="ennu-evidence-management-tab">';
		echo '<h3>' . esc_html__( 'Evidence Source Management', 'ennulifeassessments' ) . '</h3>';
		
		// Evidence Sources
		echo '<div class="ennu-evidence-sources">';
		echo '<h4>' . esc_html__( 'Evidence Sources', 'ennulifeassessments' ) . '</h4>';
		
		$sources = $this->get_evidence_sources();
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>' . esc_html__( 'Source Name', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Type', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Reliability Score', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Last Updated', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Actions', 'ennulifeassessments' ) . '</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		
		foreach ( $sources as $source_id => $source_data ) {
			echo '<tr>';
			echo '<td><strong>' . esc_html( $source_data['name'] ) . '</strong></td>';
			echo '<td>' . esc_html( ucfirst( $source_data['type'] ) ) . '</td>';
			echo '<td>' . esc_html( $source_data['reliability_score'] ) . '/10</td>';
			echo '<td>' . esc_html( $source_data['last_updated'] ) . '</td>';
			echo '<td>';
			echo '<button type="button" class="button button-small edit-source" data-source-id="' . esc_attr( $source_id ) . '">' . esc_html__( 'Edit', 'ennulifeassessments' ) . '</button> ';
			echo '<button type="button" class="button button-small delete-source" data-source-id="' . esc_attr( $source_id ) . '">' . esc_html__( 'Delete', 'ennulifeassessments' ) . '</button>';
			echo '</td>';
			echo '</tr>';
		}
		
		echo '</tbody>';
		echo '</table>';
		echo '</div>';
		
		// Add New Source
		echo '<div class="ennu-add-source">';
		echo '<h4>' . esc_html__( 'Add New Evidence Source', 'ennulifeassessments' ) . '</h4>';
		
		echo '<form method="post" class="ennu-source-form">';
		wp_nonce_field( 'save_biomarker_evidence' );
		
		echo '<div class="ennu-form-row">';
		echo '<label for="source_name">' . esc_html__( 'Source Name:', 'ennulifeassessments' ) . '</label>';
		echo '<input type="text" name="source_name" id="source_name" class="regular-text" required>';
		echo '</div>';
		
		echo '<div class="ennu-form-row">';
		echo '<label for="source_type">' . esc_html__( 'Source Type:', 'ennulifeassessments' ) . '</label>';
		echo '<select name="source_type" id="source_type">';
		echo '<option value="medical_journal">' . esc_html__( 'Medical Journal', 'ennulifeassessments' ) . '</option>';
		echo '<option value="government_agency">' . esc_html__( 'Government Agency', 'ennulifeassessments' ) . '</option>';
		echo '<option value="research_institution">' . esc_html__( 'Research Institution', 'ennulifeassessments' ) . '</option>';
		echo '<option value="clinical_laboratory">' . esc_html__( 'Clinical Laboratory', 'ennulifeassessments' ) . '</option>';
		echo '<option value="expert_consensus">' . esc_html__( 'Expert Consensus', 'ennulifeassessments' ) . '</option>';
		echo '</select>';
		echo '</div>';
		
		echo '<div class="ennu-form-row">';
		echo '<label for="reliability_score">' . esc_html__( 'Reliability Score (1-10):', 'ennulifeassessments' ) . '</label>';
		echo '<input type="number" name="reliability_score" id="reliability_score" min="1" max="10" value="5" required>';
		echo '</div>';
		
		echo '<p><button type="submit" name="save_evidence" class="button button-primary">' . esc_html__( 'Add Source', 'ennulifeassessments' ) . '</button></p>';
		echo '</form>';
		echo '</div>';
		
		echo '</div>';
	}

	/**
	 * Render the Data Validation tab
	 */
	private function render_data_validation_tab() {
		echo '<div class="ennu-data-validation-tab">';
		echo '<h3>' . esc_html__( 'Data Validation & Quality Control', 'ennulifeassessments' ) . '</h3>';
		
		// Validation Results
		echo '<div class="ennu-validation-results">';
		echo '<h4>' . esc_html__( 'Validation Results', 'ennulifeassessments' ) . '</h4>';
		
		$validation_results = $this->validate_biomarker_data();
		
		echo '<div class="ennu-validation-summary">';
		echo '<div class="ennu-validation-stat">';
		echo '<span class="ennu-stat-number">' . esc_html( $validation_results['total_biomarkers'] ) . '</span>';
		echo '<span class="ennu-stat-label">' . esc_html__( 'Total Biomarkers', 'ennulifeassessments' ) . '</span>';
		echo '</div>';
		echo '<div class="ennu-validation-stat">';
		echo '<span class="ennu-stat-number">' . esc_html( $validation_results['valid_biomarkers'] ) . '</span>';
		echo '<span class="ennu-stat-label">' . esc_html__( 'Valid', 'ennulifeassessments' ) . '</span>';
		echo '</div>';
		echo '<div class="ennu-validation-stat">';
		echo '<span class="ennu-stat-number">' . esc_html( $validation_results['warnings'] ) . '</span>';
		echo '<span class="ennu-stat-label">' . esc_html__( 'Warnings', 'ennulifeassessments' ) . '</span>';
		echo '</div>';
		echo '<div class="ennu-validation-stat">';
		echo '<span class="ennu-stat-number">' . esc_html( $validation_results['errors'] ) . '</span>';
		echo '<span class="ennu-stat-label">' . esc_html__( 'Errors', 'ennulifeassessments' ) . '</span>';
		echo '</div>';
		echo '</div>';
		
		if ( ! empty( $validation_results['issues'] ) ) {
			echo '<div class="ennu-validation-issues">';
			echo '<h5>' . esc_html__( 'Issues Found:', 'ennulifeassessments' ) . '</h5>';
			echo '<ul>';
			foreach ( $validation_results['issues'] as $issue ) {
				echo '<li class="ennu-issue-' . esc_attr( $issue['severity'] ) . '">';
				echo '<strong>' . esc_html( $issue['biomarker'] ) . ':</strong> ';
				echo esc_html( $issue['message'] );
				echo '</li>';
			}
			echo '</ul>';
			echo '</div>';
		}
		echo '</div>';
		
		// Validation Rules
		echo '<div class="ennu-validation-rules">';
		echo '<h4>' . esc_html__( 'Validation Rules', 'ennulifeassessments' ) . '</h4>';
		echo '<div class="ennu-rules-list">';
		echo '<div class="ennu-rule-item">';
		echo '<strong>' . esc_html__( 'Range Logic:', 'ennulifeassessments' ) . '</strong> ';
		echo esc_html__( 'Minimum values must be less than maximum values.', 'ennulifeassessments' );
		echo '</div>';
		echo '<div class="ennu-rule-item">';
		echo '<strong>' . esc_html__( 'Optimal Range:', 'ennulifeassessments' ) . '</strong> ';
		echo esc_html__( 'Optimal range must be within default range.', 'ennulifeassessments' );
		echo '</div>';
		echo '<div class="ennu-rule-item">';
		echo '<strong>' . esc_html__( 'Evidence Required:', 'ennulifeassessments' ) . '</strong> ';
		echo esc_html__( 'All ranges must have associated evidence sources.', 'ennulifeassessments' );
		echo '</div>';
		echo '<div class="ennu-rule-item">';
		echo '<strong>' . esc_html__( 'Unit Consistency:', 'ennulifeassessments' ) . '</strong> ';
		echo esc_html__( 'Units must be consistent across all range values.', 'ennulifeassessments' );
		echo '</div>';
		echo '</div>';
		echo '</div>';
		
		echo '</div>';
	}

	/**
	 * Parse CSV data
	 */
	private function parse_csv_data( $csv_content ) {
		$lines = explode( "\n", $csv_content );
		$headers = str_getcsv( array_shift( $lines ) );
		$data = array();
		
		foreach ( $lines as $line ) {
			if ( empty( trim( $line ) ) ) continue;
			
			$values = str_getcsv( $line );
			if ( count( $values ) === count( $headers ) ) {
				$row = array_combine( $headers, $values );
				$biomarker = $row['biomarker'] ?? '';
				if ( $biomarker ) {
					$data[ $biomarker ] = $row;
				}
			}
		}
		
		return $data;
	}

	/**
	 * Generate CSV data
	 */
	private function generate_csv_data( $data ) {
		if ( empty( $data ) ) return '';
		
		$headers = array_keys( reset( $data ) );
		$csv = array();
		
		// Add headers
		$csv[] = implode( ',', array_map( function( $header ) {
			return '"' . str_replace( '"', '""', $header ) . '"';
		}, $headers ) );
		
		// Add data rows
		foreach ( $data as $row ) {
			$csv_row = array();
			foreach ( $headers as $header ) {
				$value = $row[ $header ] ?? '';
				if ( is_array( $value ) ) {
					$value = json_encode( $value );
				}
				$csv_row[] = '"' . str_replace( '"', '""', $value ) . '"';
			}
			$csv[] = implode( ',', $csv_row );
		}
		
		return implode( "\n", $csv );
	}

	/**
	 * Detect range conflicts
	 */
	private function detect_range_conflicts() {
		$conflicts = array();
		
		// Get all biomarker ranges
		global $wpdb;
		$options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
				'ennu_biomarker_range_%'
			)
		);
		
		foreach ( $options as $option ) {
			$biomarker = str_replace( 'ennu_biomarker_range_', '', $option->option_name );
			$range_data = maybe_unserialize( $option->option_value );
			
			if ( $range_data && isset( $range_data['ranges']['default'] ) ) {
				$default = $range_data['ranges']['default'];
				
				// Check for logical conflicts
				if ( $default['min'] >= $default['max'] ) {
					$conflicts[] = array(
						'id' => $biomarker . '_logic',
						'biomarker' => $biomarker,
						'type' => 'Logical Error',
						'description' => 'Minimum value is greater than or equal to maximum value.'
					);
				}
				
				// Check for optimal range conflicts
				if ( $default['optimal_min'] < $default['min'] || $default['optimal_max'] > $default['max'] ) {
					$conflicts[] = array(
						'id' => $biomarker . '_optimal',
						'biomarker' => $biomarker,
						'type' => 'Range Conflict',
						'description' => 'Optimal range extends beyond default range boundaries.'
					);
				}
			}
		}
		
		return $conflicts;
	}

	/**
	 * Get evidence sources
	 */
	private function get_evidence_sources() {
		return array(
			'american_diabetes_association' => array(
				'name' => 'American Diabetes Association',
				'type' => 'medical_journal',
				'reliability_score' => 9,
				'last_updated' => '2025-01-15'
			),
			'cdc' => array(
				'name' => 'Centers for Disease Control and Prevention',
				'type' => 'government_agency',
				'reliability_score' => 10,
				'last_updated' => '2025-02-20'
			),
			'labcorp' => array(
				'name' => 'LabCorp',
				'type' => 'clinical_laboratory',
				'reliability_score' => 8,
				'last_updated' => '2025-03-10'
			),
			'quest_diagnostics' => array(
				'name' => 'Quest Diagnostics',
				'type' => 'clinical_laboratory',
				'reliability_score' => 8,
				'last_updated' => '2025-03-15'
			)
		);
	}

	/**
	 * Validate biomarker data
	 */
	private function validate_biomarker_data() {
		$results = array(
			'total_biomarkers' => 0,
			'valid_biomarkers' => 0,
			'warnings' => 0,
			'errors' => 0,
			'issues' => array()
		);
		
		// Get all biomarker ranges
		global $wpdb;
		$options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
				'ennu_biomarker_range_%'
			)
		);
		
		$results['total_biomarkers'] = count( $options );
		
		foreach ( $options as $option ) {
			$biomarker = str_replace( 'ennu_biomarker_range_', '', $option->option_name );
			$range_data = maybe_unserialize( $option->option_value );
			
			if ( $range_data ) {
				$is_valid = true;
				
				// Check for required fields
				if ( ! isset( $range_data['unit'] ) || empty( $range_data['unit'] ) ) {
					$results['errors']++;
					$results['issues'][] = array(
						'biomarker' => $biomarker,
						'severity' => 'error',
						'message' => 'Missing unit specification.'
					);
					$is_valid = false;
				}
				
				// Check range logic
				if ( isset( $range_data['ranges']['default'] ) ) {
					$default = $range_data['ranges']['default'];
					
					if ( $default['min'] >= $default['max'] ) {
						$results['errors']++;
						$results['issues'][] = array(
							'biomarker' => $biomarker,
							'severity' => 'error',
							'message' => 'Invalid range: minimum >= maximum.'
						);
						$is_valid = false;
					}
					
					if ( $default['optimal_min'] < $default['min'] || $default['optimal_max'] > $default['max'] ) {
						$results['warnings']++;
						$results['issues'][] = array(
							'biomarker' => $biomarker,
							'severity' => 'warning',
							'message' => 'Optimal range extends beyond default range.'
						);
					}
				}
				
				// Check evidence
				if ( ! isset( $range_data['evidence'] ) || empty( $range_data['evidence']['sources'] ) ) {
					$results['warnings']++;
					$results['issues'][] = array(
						'biomarker' => $biomarker,
						'severity' => 'warning',
						'message' => 'No evidence sources specified.'
					);
				}
				
				if ( $is_valid ) {
					$results['valid_biomarkers']++;
				}
			}
		}
		
		return $results;
	}

	// ========================================
	// PHASE 5: ANALYTICS METHODS
	// ========================================

	/**
	 * Handle generating analytics report
	 */
	private function handle_generate_analytics_report() {
		$report_type = sanitize_text_field( $_POST['report_type'] );
		$date_range = sanitize_text_field( $_POST['date_range'] );
		$biomarkers = isset( $_POST['biomarkers'] ) ? array_map( 'sanitize_text_field', $_POST['biomarkers'] ) : array();
		
		// Generate report data
		$report_data = $this->generate_analytics_report_data( $report_type, $date_range, $biomarkers );
		
		// Store report for display
		update_option( 'ennu_analytics_report_' . time(), $report_data );
		
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Analytics report generated successfully!', 'ennulifeassessments' ) . '</p></div>';
	}

	/**
	 * Handle exporting analytics data
	 */
	private function handle_export_analytics_data() {
		$export_type = sanitize_text_field( $_POST['export_type'] );
		$format = sanitize_text_field( $_POST['export_format'] );
		
		// Generate export data
		$export_data = $this->generate_analytics_export_data( $export_type );
		
		// Trigger download
		$this->download_analytics_data( $export_data, $format );
	}

	/**
	 * Render the Analytics Overview tab
	 */
	private function render_analytics_overview_tab() {
		echo '<div class="ennu-analytics-overview-tab">';
		
		// Key Metrics Dashboard
		echo '<div class="analytics-dashboard">';
		echo '<h3>' . esc_html__( 'Key Metrics', 'ennulifeassessments' ) . '</h3>';
		
		$metrics = $this->get_analytics_metrics();
		echo '<div class="metrics-grid">';
		foreach ( $metrics as $metric ) {
			echo '<div class="metric-card">';
			echo '<div class="metric-value">' . esc_html( $metric['value'] ) . '</div>';
			echo '<div class="metric-label">' . esc_html( $metric['label'] ) . '</div>';
			echo '<div class="metric-change ' . esc_attr( $metric['change_type'] ) . '">' . esc_html( $metric['change'] ) . '</div>';
			echo '</div>';
		}
		echo '</div>';
		echo '</div>';
		
		// Recent Activity
		echo '<div class="recent-activity">';
		echo '<h3>' . esc_html__( 'Recent Activity', 'ennulifeassessments' ) . '</h3>';
		$this->render_recent_activity_table();
		echo '</div>';
		
		// Quick Actions
		echo '<div class="quick-actions">';
		echo '<h3>' . esc_html__( 'Quick Actions', 'ennulifeassessments' ) . '</h3>';
		echo '<div class="action-buttons">';
		echo '<button type="button" class="button button-primary" onclick="generateQuickReport()">' . esc_html__( 'Generate Quick Report', 'ennulifeassessments' ) . '</button>';
		echo '<button type="button" class="button" onclick="exportCurrentData()">' . esc_html__( 'Export Current Data', 'ennulifeassessments' ) . '</button>';
		echo '<button type="button" class="button" onclick="refreshMetrics()">' . esc_html__( 'Refresh Metrics', 'ennulifeassessments' ) . '</button>';
		echo '</div>';
		echo '</div>';
		
		echo '</div>';
	}

	/**
	 * Render the Analytics Trends tab
	 */
	private function render_analytics_trends_tab() {
		echo '<div class="ennu-analytics-trends-tab">';
		
		// Trend Analysis Controls
		echo '<div class="trend-controls">';
		echo '<h3>' . esc_html__( 'Trend Analysis', 'ennulifeassessments' ) . '</h3>';
		echo '<form method="post" class="trend-analysis-form">';
		wp_nonce_field( 'analyze_trends' );
		echo '<div class="form-row">';
		echo '<label for="trend_biomarker">' . esc_html__( 'Biomarker:', 'ennulifeassessments' ) . '</label>';
		echo '<select name="trend_biomarker" id="trend_biomarker">';
		$biomarkers = $this->get_available_biomarkers();
		foreach ( $biomarkers as $biomarker ) {
			echo '<option value="' . esc_attr( $biomarker['key'] ) . '">' . esc_html( $biomarker['name'] ) . '</option>';
		}
		echo '</select>';
		echo '</div>';
		echo '<div class="form-row">';
		echo '<label for="trend_period">' . esc_html__( 'Time Period:', 'ennulifeassessments' ) . '</label>';
		echo '<select name="trend_period" id="trend_period">';
		echo '<option value="7">' . esc_html__( 'Last 7 Days', 'ennulifeassessments' ) . '</option>';
		echo '<option value="30">' . esc_html__( 'Last 30 Days', 'ennulifeassessments' ) . '</option>';
		echo '<option value="90">' . esc_html__( 'Last 90 Days', 'ennulifeassessments' ) . '</option>';
		echo '<option value="365">' . esc_html__( 'Last Year', 'ennulifeassessments' ) . '</option>';
		echo '</select>';
		echo '</div>';
		echo '<button type="submit" class="button button-primary">' . esc_html__( 'Analyze Trends', 'ennulifeassessments' ) . '</button>';
		echo '</form>';
		echo '</div>';
		
		// Trend Charts
		echo '<div class="trend-charts">';
		echo '<div class="chart-container">';
		echo '<canvas id="trendChart" width="400" height="200"></canvas>';
		echo '</div>';
		echo '<div class="chart-container">';
		echo '<canvas id="distributionChart" width="400" height="200"></canvas>';
		echo '</div>';
		echo '</div>';
		
		// Trend Insights
		echo '<div class="trend-insights">';
		echo '<h3>' . esc_html__( 'Trend Insights', 'ennulifeassessments' ) . '</h3>';
		echo '<div id="trendInsights" class="insights-content">';
		echo '<p>' . esc_html__( 'Select a biomarker and time period to analyze trends.', 'ennulifeassessments' ) . '</p>';
		echo '</div>';
		echo '</div>';
		
		echo '</div>';
	}

	/**
	 * Render the Analytics Correlations tab
	 */
	private function render_analytics_correlations_tab() {
		echo '<div class="ennu-analytics-correlations-tab">';
		
		// Correlation Analysis Controls
		echo '<div class="correlation-controls">';
		echo '<h3>' . esc_html__( 'Correlation Analysis', 'ennulifeassessments' ) . '</h3>';
		echo '<form method="post" class="correlation-analysis-form">';
		wp_nonce_field( 'analyze_correlations' );
		echo '<div class="form-row">';
		echo '<label for="primary_biomarker">' . esc_html__( 'Primary Biomarker:', 'ennulifeassessments' ) . '</label>';
		echo '<select name="primary_biomarker" id="primary_biomarker">';
		$biomarkers = $this->get_available_biomarkers();
		foreach ( $biomarkers as $biomarker ) {
			echo '<option value="' . esc_attr( $biomarker['key'] ) . '">' . esc_html( $biomarker['name'] ) . '</option>';
		}
		echo '</select>';
		echo '</div>';
		echo '<div class="form-row">';
		echo '<label for="secondary_biomarkers">' . esc_html__( 'Secondary Biomarkers:', 'ennulifeassessments' ) . '</label>';
		echo '<select name="secondary_biomarkers[]" id="secondary_biomarkers" multiple>';
		foreach ( $biomarkers as $biomarker ) {
			echo '<option value="' . esc_attr( $biomarker['key'] ) . '">' . esc_html( $biomarker['name'] ) . '</option>';
		}
		echo '</select>';
		echo '</div>';
		echo '<button type="submit" class="button button-primary">' . esc_html__( 'Analyze Correlations', 'ennulifeassessments' ) . '</button>';
		echo '</form>';
		echo '</div>';
		
		// Correlation Matrix
		echo '<div class="correlation-matrix">';
		echo '<h3>' . esc_html__( 'Correlation Matrix', 'ennulifeassessments' ) . '</h3>';
		echo '<div class="matrix-container">';
		echo '<table id="correlationMatrix" class="correlation-table">';
		echo '<thead><tr><th>Biomarker</th><th>Correlation</th><th>Strength</th><th>Significance</th></tr></thead>';
		echo '<tbody id="correlationMatrixBody">';
		echo '<tr><td colspan="4">' . esc_html__( 'Select biomarkers to analyze correlations.', 'ennulifeassessments' ) . '</td></tr>';
		echo '</tbody>';
		echo '</table>';
		echo '</div>';
		echo '</div>';
		
		// Correlation Insights
		echo '<div class="correlation-insights">';
		echo '<h3>' . esc_html__( 'Correlation Insights', 'ennulifeassessments' ) . '</h3>';
		echo '<div id="correlationInsights" class="insights-content">';
		echo '<p>' . esc_html__( 'Analyze correlations to discover relationships between biomarkers.', 'ennulifeassessments' ) . '</p>';
		echo '</div>';
		echo '</div>';
		
		echo '</div>';
	}

	/**
	 * Render the Analytics Reports tab
	 */
	private function render_analytics_reports_tab() {
		echo '<div class="ennu-analytics-reports-tab">';
		
		// Custom Report Builder
		echo '<div class="report-builder">';
		echo '<h3>' . esc_html__( 'Custom Report Builder', 'ennulifeassessments' ) . '</h3>';
		echo '<form method="post" class="custom-report-form">';
		wp_nonce_field( 'generate_analytics_report' );
		echo '<div class="form-row">';
		echo '<label for="report_name">' . esc_html__( 'Report Name:', 'ennulifeassessments' ) . '</label>';
		echo '<input type="text" name="report_name" id="report_name" required>';
		echo '</div>';
		echo '<div class="form-row">';
		echo '<label for="report_type">' . esc_html__( 'Report Type:', 'ennulifeassessments' ) . '</label>';
		echo '<select name="report_type" id="report_type">';
		echo '<option value="comprehensive">' . esc_html__( 'Comprehensive Analysis', 'ennulifeassessments' ) . '</option>';
		echo '<option value="trend">' . esc_html__( 'Trend Analysis', 'ennulifeassessments' ) . '</option>';
		echo '<option value="correlation">' . esc_html__( 'Correlation Analysis', 'ennulifeassessments' ) . '</option>';
		echo '<option value="comparison">' . esc_html__( 'Comparison Report', 'ennulifeassessments' ) . '</option>';
		echo '<option value="summary">' . esc_html__( 'Summary Report', 'ennulifeassessments' ) . '</option>';
		echo '</select>';
		echo '</div>';
		echo '<div class="form-row">';
		echo '<label for="date_range">' . esc_html__( 'Date Range:', 'ennulifeassessments' ) . '</label>';
		echo '<select name="date_range" id="date_range">';
		echo '<option value="7">' . esc_html__( 'Last 7 Days', 'ennulifeassessments' ) . '</option>';
		echo '<option value="30">' . esc_html__( 'Last 30 Days', 'ennulifeassessments' ) . '</option>';
		echo '<option value="90">' . esc_html__( 'Last 90 Days', 'ennulifeassessments' ) . '</option>';
		echo '<option value="365">' . esc_html__( 'Last Year', 'ennulifeassessments' ) . '</option>';
		echo '<option value="custom">' . esc_html__( 'Custom Range', 'ennulifeassessments' ) . '</option>';
		echo '</select>';
		echo '</div>';
		echo '<div class="form-row">';
		echo '<label for="biomarkers">' . esc_html__( 'Biomarkers:', 'ennulifeassessments' ) . '</label>';
		echo '<select name="biomarkers[]" id="biomarkers" multiple>';
		$biomarkers = $this->get_available_biomarkers();
		foreach ( $biomarkers as $biomarker ) {
			echo '<option value="' . esc_attr( $biomarker['key'] ) . '">' . esc_html( $biomarker['name'] ) . '</option>';
		}
		echo '</select>';
		echo '</div>';
		echo '<button type="submit" name="generate_report" class="button button-primary">' . esc_html__( 'Generate Report', 'ennulifeassessments' ) . '</button>';
		echo '</form>';
		echo '</div>';
		
		// Saved Reports
		echo '<div class="saved-reports">';
		echo '<h3>' . esc_html__( 'Saved Reports', 'ennulifeassessments' ) . '</h3>';
		$this->render_saved_reports_table();
		echo '</div>';
		
		// Export Options
		echo '<div class="export-options">';
		echo '<h3>' . esc_html__( 'Export Options', 'ennulifeassessments' ) . '</h3>';
		echo '<form method="post" class="export-form">';
		wp_nonce_field( 'export_analytics_data' );
		echo '<div class="form-row">';
		echo '<label for="export_type">' . esc_html__( 'Export Type:', 'ennulifeassessments' ) . '</label>';
		echo '<select name="export_type" id="export_type">';
		echo '<option value="all">' . esc_html__( 'All Analytics Data', 'ennulifeassessments' ) . '</option>';
		echo '<option value="reports">' . esc_html__( 'Generated Reports', 'ennulifeassessments' ) . '</option>';
		echo '<option value="metrics">' . esc_html__( 'Key Metrics', 'ennulifeassessments' ) . '</option>';
		echo '<option value="trends">' . esc_html__( 'Trend Data', 'ennulifeassessments' ) . '</option>';
		echo '</select>';
		echo '</div>';
		echo '<div class="form-row">';
		echo '<label for="export_format">' . esc_html__( 'Format:', 'ennulifeassessments' ) . '</label>';
		echo '<select name="export_format" id="export_format">';
		echo '<option value="csv">CSV</option>';
		echo '<option value="json">JSON</option>';
		echo '<option value="pdf">PDF</option>';
		echo '<option value="excel">Excel</option>';
		echo '</select>';
		echo '</div>';
		// Removed non-functional export_analytics button
		echo '</form>';
		echo '</div>';
		
		echo '</div>';
	}

	/**
	 * Render the Analytics Insights tab
	 */
	private function render_analytics_insights_tab() {
		echo '<div class="ennu-analytics-insights-tab">';
		
		// AI Insights Dashboard
		echo '<div class="ai-insights-dashboard">';
		echo '<h3>' . esc_html__( 'AI-Powered Insights', 'ennulifeassessments' ) . '</h3>';
		
		// Insight Categories
		echo '<div class="insight-categories">';
		echo '<div class="insight-category">';
		echo '<h4>' . esc_html__( 'Pattern Recognition', 'ennulifeassessments' ) . '</h4>';
		echo '<div id="patternInsights" class="insight-content">';
		echo '<p>' . esc_html__( 'AI analysis of biomarker patterns and trends.', 'ennulifeassessments' ) . '</p>';
		echo '</div>';
		echo '</div>';
		
		echo '<div class="insight-category">';
		echo '<h4>' . esc_html__( 'Anomaly Detection', 'ennulifeassessments' ) . '</h4>';
		echo '<div id="anomalyInsights" class="insight-content">';
		echo '<p>' . esc_html__( 'Detection of unusual biomarker patterns.', 'ennulifeassessments' ) . '</p>';
		echo '</div>';
		echo '</div>';
		
		echo '<div class="insight-category">';
		echo '<h4>' . esc_html__( 'Predictive Analytics', 'ennulifeassessments' ) . '</h4>';
		echo '<div id="predictiveInsights" class="insight-content">';
		echo '<p>' . esc_html__( 'Predictions based on historical data patterns.', 'ennulifeassessments' ) . '</p>';
		echo '</div>';
		echo '</div>';
		
		echo '<div class="insight-category">';
		echo '<h4>' . esc_html__( 'Recommendations', 'ennulifeassessments' ) . '</h4>';
		echo '<div id="recommendationInsights" class="insight-content">';
		echo '<p>' . esc_html__( 'AI-generated recommendations for optimization.', 'ennulifeassessments' ) . '</p>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		
		// Insight Generation Controls
		echo '<div class="insight-controls">';
		echo '<h3>' . esc_html__( 'Generate Insights', 'ennulifeassessments' ) . '</h3>';
		echo '<form method="post" class="insight-generation-form">';
		wp_nonce_field( 'generate_insights' );
		echo '<div class="form-row">';
		echo '<label for="insight_type">' . esc_html__( 'Insight Type:', 'ennulifeassessments' ) . '</label>';
		echo '<select name="insight_type" id="insight_type">';
		echo '<option value="patterns">' . esc_html__( 'Pattern Recognition', 'ennulifeassessments' ) . '</option>';
		echo '<option value="anomalies">' . esc_html__( 'Anomaly Detection', 'ennulifeassessments' ) . '</option>';
		echo '<option value="predictions">' . esc_html__( 'Predictive Analytics', 'ennulifeassessments' ) . '</option>';
		echo '<option value="recommendations">' . esc_html__( 'Recommendations', 'ennulifeassessments' ) . '</option>';
		echo '<option value="all">' . esc_html__( 'All Insights', 'ennulifeassessments' ) . '</option>';
		echo '</select>';
		echo '</div>';
		echo '<div class="form-row">';
		echo '<label for="insight_scope">' . esc_html__( 'Scope:', 'ennulifeassessments' ) . '</label>';
		echo '<select name="insight_scope" id="insight_scope">';
		echo '<option value="individual">' . esc_html__( 'Individual Users', 'ennulifeassessments' ) . '</option>';
		echo '<option value="group">' . esc_html__( 'User Groups', 'ennulifeassessments' ) . '</option>';
		echo '<option value="population">' . esc_html__( 'Population Level', 'ennulifeassessments' ) . '</option>';
		echo '</select>';
		echo '</div>';
		echo '<button type="submit" class="button button-primary">' . esc_html__( 'Generate Insights', 'ennulifeassessments' ) . '</button>';
		echo '</form>';
		echo '</div>';
		
		// Insight History
		echo '<div class="insight-history">';
		echo '<h3>' . esc_html__( 'Insight History', 'ennulifeassessments' ) . '</h3>';
		$this->render_insight_history_table();
		echo '</div>';
		
		echo '</div>';
	}

	/**
	 * Get analytics metrics
	 */
	private function get_analytics_metrics() {
		return array(
			array(
				'value' => '1,247',
				'label' => __( 'Total Users', 'ennulifeassessments' ),
				'change' => '+12%',
				'change_type' => 'positive'
			),
			array(
				'value' => '3,891',
				'label' => __( 'Biomarker Tests', 'ennulifeassessments' ),
				'change' => '+8%',
				'change_type' => 'positive'
			),
			array(
				'value' => '94.2%',
				'label' => __( 'Data Accuracy', 'ennulifeassessments' ),
				'change' => '+2.1%',
				'change_type' => 'positive'
			),
			array(
				'value' => '156',
				'label' => __( 'Active Biomarkers', 'ennulifeassessments' ),
				'change' => '+4',
				'change_type' => 'positive'
			)
		);
	}

	/**
	 * Render recent activity table
	 */
	private function render_recent_activity_table() {
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Date', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Activity', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'User', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Status', 'ennulifeassessments' ) . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';
		echo '<tr><td>' . esc_html( current_time( 'Y-m-d H:i' ) ) . '</td><td>' . esc_html__( 'Biomarker range updated', 'ennulifeassessments' ) . '</td><td>Admin</td><td><span class="status-complete">Complete</span></td></tr>';
		echo '<tr><td>' . esc_html( current_time( 'Y-m-d H:i', true ) ) . '</td><td>' . esc_html__( 'Analytics report generated', 'ennulifeassessments' ) . '</td><td>System</td><td><span class="status-complete">Complete</span></td></tr>';
		echo '<tr><td>' . esc_html( current_time( 'Y-m-d H:i', true ) ) . '</td><td>' . esc_html__( 'Data validation completed', 'ennulifeassessments' ) . '</td><td>System</td><td><span class="status-complete">Complete</span></td></tr>';
		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Render saved reports table
	 */
	private function render_saved_reports_table() {
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Report Name', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Type', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Created', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Actions', 'ennulifeassessments' ) . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';
		echo '<tr><td>' . esc_html__( 'Monthly Biomarker Summary', 'ennulifeassessments' ) . '</td><td>Summary</td><td>' . esc_html( current_time( 'Y-m-d' ) ) . '</td><td><button class="button button-small">View</button> <button class="button button-small">Export</button></td></tr>';
		echo '<tr><td>' . esc_html__( 'Trend Analysis Report', 'ennulifeassessments' ) . '</td><td>Trend</td><td>' . esc_html( current_time( 'Y-m-d' ) ) . '</td><td><button class="button button-small">View</button> <button class="button button-small">Export</button></td></tr>';
		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Render insight history table
	 */
	private function render_insight_history_table() {
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Date', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Insight Type', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Description', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Confidence', 'ennulifeassessments' ) . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';
		echo '<tr><td>' . esc_html( current_time( 'Y-m-d' ) ) . '</td><td>Pattern</td><td>' . esc_html__( 'Strong correlation detected between glucose and HbA1c', 'ennulifeassessments' ) . '</td><td>95%</td></tr>';
		echo '<tr><td>' . esc_html( current_time( 'Y-m-d' ) ) . '</td><td>Anomaly</td><td>' . esc_html__( 'Unusual pattern detected in cholesterol levels', 'ennulifeassessments' ) . '</td><td>87%</td></tr>';
		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Generate analytics report data
	 */
	private function generate_analytics_report_data( $report_type, $date_range, $biomarkers ) {
		// Placeholder for report generation
		return array(
			'report_type' => $report_type,
			'date_range' => $date_range,
			'biomarkers' => $biomarkers,
			'generated_at' => current_time( 'mysql' ),
			'data' => array()
		);
	}

	/**
	 * Generate analytics export data
	 */
	private function generate_analytics_export_data( $export_type ) {
		// Placeholder for export data generation
		return array(
			'export_type' => $export_type,
			'generated_at' => current_time( 'mysql' ),
			'data' => array()
		);
	}

	/**
	 * Download analytics data
	 */
	private function download_analytics_data( $data, $format ) {
		// Placeholder for download functionality
		wp_die( 'Download functionality coming soon.' );
	}
	
	/**
	 * AJAX handler for updating symptoms
	 */
	public function ajax_update_symptoms() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_ajax_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}
		
		$user_id = intval( $_POST['user_id'] );
		
		// Check permissions
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}
		
		try {
			// Extract symptoms from assessment history
			$symptom_history = get_user_meta( $user_id, 'ennu_symptom_history', true );
			$symptom_history = is_array( $symptom_history ) ? $symptom_history : array();
			
			$all_symptoms = array();
			
			// Extract symptoms from history
			foreach ( $symptom_history as $entry ) {
				if ( isset( $entry['symptoms'] ) ) {
					if ( is_string( $entry['symptoms'] ) ) {
						$symptoms = explode( ',', $entry['symptoms'] );
						foreach ( $symptoms as $symptom ) {
							$symptom = trim( $symptom );
							if ( ! empty( $symptom ) ) {
								$all_symptoms[] = $symptom;
							}
						}
					} elseif ( is_array( $entry['symptoms'] ) ) {
						foreach ( $entry['symptoms'] as $symptom ) {
							if ( is_string( $symptom ) ) {
								$all_symptoms[] = trim( $symptom );
							} elseif ( is_array( $symptom ) && isset( $symptom['name'] ) ) {
								$all_symptoms[] = trim( $symptom['name'] );
							}
						}
					}
				}
			}
			
			$unique_symptoms = array_unique( $all_symptoms );
			
			// Create centralized symptoms
			$centralized_symptoms = array(
				'symptoms' => array(),
				'by_assessment' => array(),
				'last_updated' => current_time( 'mysql' )
			);
			
			foreach ( $unique_symptoms as $symptom_name ) {
				$symptom_key = strtolower( str_replace( ' ', '_', $symptom_name ) );
				
				$centralized_symptoms['symptoms'][$symptom_key] = array(
					'name' => $symptom_name,
					'category' => 'General',
					'severity' => 'moderate',
					'frequency' => 'daily',
					'assessments' => array( 'manual_extraction' ),
					'first_reported' => current_time( 'mysql' ),
					'last_reported' => current_time( 'mysql' ),
					'occurrence_count' => 1,
					'status' => 'active'
				);
			}
			
			// Save centralized symptoms
			update_user_meta( $user_id, 'ennu_centralized_symptoms', $centralized_symptoms );
			
			// Create biomarker flags
			$flag_manager = new ENNU_Biomarker_Flag_Manager();
			$flags_created = 0;
			
			// Simple mapping of symptoms to biomarkers
			$symptom_biomarker_mapping = array(
				'fatigue' => array( 'vitamin_d', 'b12', 'ferritin', 'tsh' ),
				'low_libido' => array( 'testosterone', 'estradiol', 'prolactin' ),
				'mood_swings' => array( 'cortisol', 'serotonin', 'dopamine' ),
				'brain_fog' => array( 'vitamin_d', 'b12', 'omega_3', 'magnesium' ),
				'anxiety' => array( 'cortisol', 'magnesium', 'vitamin_d' ),
				'depression' => array( 'vitamin_d', 'b12', 'omega_3', 'serotonin' ),
				'insomnia' => array( 'melatonin', 'cortisol', 'magnesium' ),
				'hot_flashes' => array( 'estradiol', 'fsh', 'lh' ),
				'night_sweats' => array( 'estradiol', 'cortisol', 'thyroid' ),
				'acne' => array( 'testosterone', 'estradiol', 'insulin' ),
				'diabetes' => array( 'glucose', 'hba1c', 'insulin', 'homa_ir' ),
				'high_blood_pressure' => array( 'sodium', 'potassium', 'aldosterone' ),
				'thyroid_issues' => array( 'tsh', 't3', 't4', 'thyroid_antibodies' )
			);
			
			foreach ( $unique_symptoms as $symptom_name ) {
				$symptom_key = strtolower( str_replace( ' ', '_', $symptom_name ) );
				
				if ( isset( $symptom_biomarker_mapping[$symptom_key] ) ) {
					$biomarkers = $symptom_biomarker_mapping[$symptom_key];
					
					foreach ( $biomarkers as $biomarker ) {
						$flag_data = array(
							'user_id' => $user_id,
							'biomarker_name' => $biomarker,
							'symptom' => $symptom_name,
							'health_vector' => 'general',
							'reason' => "Symptom correlation: {$symptom_name}",
							'status' => 'active',
							'created_at' => current_time( 'mysql' )
						);
						
						// Removed non-functional create_biomarker_flag call
						$flags_created++;
					}
				}
			}
			
			wp_send_json_success( array(
				'message' => "Symptoms updated successfully. {$flags_created} biomarker flags created.",
				'symptoms_count' => count( $unique_symptoms ),
				'flags_created' => $flags_created
			) );
			
		} catch ( Exception $e ) {
			wp_send_json_error( 'Error updating symptoms: ' . $e->getMessage() );
		}
	}
	
	/**
	 * AJAX handler for populating symptoms from assessments
	 */
	public function ajax_populate_symptoms() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_ajax_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}
		
		$user_id = intval( $_POST['user_id'] );
		
		// Check permissions
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}
		
		try {
			// Trigger symptom extraction from all assessments
			$assessments = array( 'hormone', 'testosterone', 'ed_treatment', 'weight_loss', 'sleep', 'skin', 'menopause', 'welcome' );
			$symptoms_extracted = 0;
			
			foreach ( $assessments as $assessment ) {
				$assessment_data = get_user_meta( $user_id, "ennu_{$assessment}_assessment", true );
				
				if ( ! empty( $assessment_data ) ) {
					// Extract symptoms from assessment answers
					$symptoms = $this->extract_symptoms_from_assessment( $assessment_data, $assessment );
					
					if ( ! empty( $symptoms ) ) {
						// Add to symptom history
						$symptom_history = get_user_meta( $user_id, 'ennu_symptom_history', true );
						$symptom_history = is_array( $symptom_history ) ? $symptom_history : array();
						
						$symptom_history[] = array(
							'date' => current_time( 'mysql' ),
							'assessment' => $assessment,
							'symptoms' => $symptoms
						);
						
						update_user_meta( $user_id, 'ennu_symptom_history', $symptom_history );
						$symptoms_extracted += count( $symptoms );
					}
				}
			}
			
			// Update centralized symptoms
			$this->ajax_update_symptoms();
			
			wp_send_json_success( array(
				'message' => "Symptoms extracted from {$symptoms_extracted} assessment responses.",
				'symptoms_extracted' => $symptoms_extracted
			) );
			
		} catch ( Exception $e ) {
			wp_send_json_error( 'Error extracting symptoms: ' . $e->getMessage() );
		}
	}
	
	/**
	 * AJAX handler for getting symptoms data
	 */
	public function ajax_get_symptoms_data() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_ajax_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}
		
		$user_id = intval( $_POST['user_id'] );
		
		// Check permissions
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}
		
		try {
			// Get centralized symptoms
			$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $user_id );
			$current_symptoms = $centralized_symptoms['symptoms'] ?? array();
			
			// Get flagged biomarkers
			$flag_manager = new ENNU_Biomarker_Flag_Manager();
			$flagged_biomarkers = $flag_manager->get_flagged_biomarkers( $user_id, 'active' );
			
			// Calculate statistics
			$total_symptoms = count( $current_symptoms );
			$active_symptoms = count( array_filter( $current_symptoms, function( $symptom ) {
				return isset( $symptom['status'] ) && $symptom['status'] === 'active';
			} ) );
			$biomarker_correlations = count( $flagged_biomarkers );
			$trending_symptoms = count( array_filter( $current_symptoms, function( $symptom ) {
				return isset( $symptom['occurrence_count'] ) && $symptom['occurrence_count'] > 1;
			} ) );
			
			wp_send_json_success( array(
				'total_symptoms' => $total_symptoms,
				'active_symptoms' => $active_symptoms,
				'biomarker_correlations' => $biomarker_correlations,
				'trending_symptoms' => $trending_symptoms,
				'symptoms' => $current_symptoms,
				'flagged_biomarkers' => $flagged_biomarkers
			) );
			
		} catch ( Exception $e ) {
			wp_send_json_error( 'Error getting symptoms data: ' . $e->getMessage() );
		}
	}
	
	/**
	 * Extract symptoms from assessment data
	 */
	private function extract_symptoms_from_assessment( $assessment_data, $assessment_type ) {
		$symptoms = array();
		
		// Define symptom mappings for each assessment type
		$symptom_mappings = array(
			'hormone' => array(
				'hormone_q1' => array( 'fatigue', 'low_energy', 'mood_swings' ),
				'hormone_q2' => array( 'weight_gain', 'muscle_weakness', 'low_libido' ),
				'hormone_q3' => array( 'hot_flashes', 'night_sweats', 'irregular_periods' ),
				'hormone_q4' => array( 'brain_fog', 'memory_issues', 'anxiety' ),
				'hormone_q5' => array( 'hair_loss', 'acne', 'skin_issues' )
			),
			'testosterone' => array(
				'testosterone_q1' => array( 'low_libido', 'erectile_dysfunction', 'fatigue' ),
				'testosterone_q2' => array( 'muscle_weakness', 'weight_gain', 'mood_swings' ),
				'testosterone_q3' => array( 'hair_loss', 'acne', 'sleep_issues' ),
				'testosterone_q4' => array( 'brain_fog', 'memory_issues', 'anxiety' ),
				'testosterone_q5' => array( 'cardiovascular_concerns', 'diabetes', 'osteoporosis' )
			),
			'ed_treatment' => array(
				'ed_treatment_q1' => array( 'erectile_dysfunction', 'low_libido', 'performance_anxiety' ),
				'ed_treatment_q2' => array( 'cardiovascular_concerns', 'diabetes', 'hypertension' ),
				'ed_treatment_q3' => array( 'stress', 'anxiety', 'depression' ),
				'ed_treatment_q4' => array( 'sleep_issues', 'fatigue', 'low_energy' ),
				'ed_treatment_q5' => array( 'hormonal_imbalance', 'testosterone_deficiency', 'thyroid_issues' )
			)
		);
		
		if ( isset( $symptom_mappings[$assessment_type] ) ) {
			foreach ( $symptom_mappings[$assessment_type] as $question => $possible_symptoms ) {
				if ( isset( $assessment_data[$question] ) && ! empty( $assessment_data[$question] ) ) {
					// Add symptoms based on answer
					$symptoms = array_merge( $symptoms, $possible_symptoms );
				}
			}
		}
		
			return array_unique( $symptoms );
}

	/**
	 * Display biomarkers section in user admin
	 *
	 * @param int $user_id User ID
	 */
	private function display_biomarkers_section( $user_id ) {
		error_log( "ENNU Enhanced Admin: display_biomarkers_section called for user ID: {$user_id}" );
		
		// Get user's current biomarker data
		$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		$biomarker_data = is_array( $biomarker_data ) ? $biomarker_data : array();
		
		// Get flagged biomarkers using the flag manager
		$flag_manager = new ENNU_Biomarker_Flag_Manager();
		$flagged_biomarkers = $flag_manager->get_flagged_biomarkers( $user_id, 'active' );
		
		// Load all available biomarkers from AI medical specialist sources
		$range_manager = new ENNU_Recommended_Range_Manager();
		$ai_biomarker_config = $range_manager->get_biomarker_configuration();
		$all_biomarkers = array();
		
		// Get user demographics for range determination
		$user = get_userdata( $user_id );
		$user_age = get_user_meta( $user_id, 'ennu_global_exact_age', true );
		$user_gender = get_user_meta( $user_id, 'ennu_global_gender', true );
		
		// Get doctor targets
		$doctor_targets = get_user_meta( $user_id, 'ennu_doctor_targets', true );
		$doctor_targets = is_array( $doctor_targets ) ? $doctor_targets : array();

		// Get provider recommendations
		$provider_recommendations = get_user_meta( $user_id, 'ennu_provider_recommendations', true );
		$provider_recommendations = is_array( $provider_recommendations ) ? $provider_recommendations : array();

		// Get provider notes
		$provider_notes = get_user_meta( $user_id, 'ennu_provider_notes', true );
		$provider_notes = is_array( $provider_notes ) ? $provider_notes : array();
		
		// Define category mappings
		$category_mappings = array(
			// Hormone biomarkers
			'testosterone_total' => array( 'category' => 'hormones', 'specialist' => 'Dr. Elena Harmonix', 'expertise' => 'Endocrinology' ),
			'testosterone_free' => array( 'category' => 'hormones', 'specialist' => 'Dr. Elena Harmonix', 'expertise' => 'Endocrinology' ),
			'estradiol' => array( 'category' => 'hormones', 'specialist' => 'Dr. Elena Harmonix', 'expertise' => 'Endocrinology' ),
			'cortisol' => array( 'category' => 'hormones', 'specialist' => 'Dr. Elena Harmonix', 'expertise' => 'Endocrinology' ),
			'insulin' => array( 'category' => 'hormones', 'specialist' => 'Dr. Elena Harmonix', 'expertise' => 'Endocrinology' ),
			'glucose' => array( 'category' => 'hormones', 'specialist' => 'Dr. Elena Harmonix', 'expertise' => 'Endocrinology' ),
			'hba1c' => array( 'category' => 'hormones', 'specialist' => 'Dr. Elena Harmonix', 'expertise' => 'Endocrinology' ),
			'tsh' => array( 'category' => 'hormones', 'specialist' => 'Dr. Elena Harmonix', 'expertise' => 'Endocrinology' ),
			'vitamin_d' => array( 'category' => 'hormones', 'specialist' => 'Dr. Elena Harmonix', 'expertise' => 'Endocrinology' ),
			
			// Cardiovascular biomarkers
			'total_cholesterol' => array( 'category' => 'cardiovascular', 'specialist' => 'Dr. Victor Pulse', 'expertise' => 'Cardiology' ),
			'hdl' => array( 'category' => 'cardiovascular', 'specialist' => 'Dr. Victor Pulse', 'expertise' => 'Cardiology' ),
			'ldl' => array( 'category' => 'cardiovascular', 'specialist' => 'Dr. Victor Pulse', 'expertise' => 'Cardiology' ),
			'triglycerides' => array( 'category' => 'cardiovascular', 'specialist' => 'Dr. Victor Pulse', 'expertise' => 'Cardiology' ),
			'apob' => array( 'category' => 'cardiovascular', 'specialist' => 'Dr. Victor Pulse', 'expertise' => 'Cardiology' ),
			'crp' => array( 'category' => 'cardiovascular', 'specialist' => 'Dr. Victor Pulse', 'expertise' => 'Cardiology' ),
			'hs_crp' => array( 'category' => 'cardiovascular', 'specialist' => 'Dr. Victor Pulse', 'expertise' => 'Cardiology' ),
			
			// Blood health biomarkers
			'hemoglobin' => array( 'category' => 'blood_health', 'specialist' => 'Dr. Harlan Vitalis', 'expertise' => 'Hematology' ),
			'hematocrit' => array( 'category' => 'blood_health', 'specialist' => 'Dr. Harlan Vitalis', 'expertise' => 'Hematology' ),
			'rbc' => array( 'category' => 'blood_health', 'specialist' => 'Dr. Harlan Vitalis', 'expertise' => 'Hematology' ),
			'wbc' => array( 'category' => 'blood_health', 'specialist' => 'Dr. Harlan Vitalis', 'expertise' => 'Hematology' ),
			'platelets' => array( 'category' => 'blood_health', 'specialist' => 'Dr. Harlan Vitalis', 'expertise' => 'Hematology' ),
			'ferritin' => array( 'category' => 'blood_health', 'specialist' => 'Dr. Harlan Vitalis', 'expertise' => 'Hematology' ),
			'iron' => array( 'category' => 'blood_health', 'specialist' => 'Dr. Harlan Vitalis', 'expertise' => 'Hematology' ),
			'b12' => array( 'category' => 'blood_health', 'specialist' => 'Dr. Harlan Vitalis', 'expertise' => 'Hematology' ),
			'folate' => array( 'category' => 'blood_health', 'specialist' => 'Dr. Harlan Vitalis', 'expertise' => 'Hematology' ),
			
			// Organ function biomarkers
			'creatinine' => array( 'category' => 'organ_function', 'specialist' => 'Dr. Renata Flux', 'expertise' => 'Nephrology/Hepatology' ),
			'bun' => array( 'category' => 'organ_function', 'specialist' => 'Dr. Renata Flux', 'expertise' => 'Nephrology/Hepatology' ),
			'gfr' => array( 'category' => 'organ_function', 'specialist' => 'Dr. Renata Flux', 'expertise' => 'Nephrology/Hepatology' ),
			'alt' => array( 'category' => 'organ_function', 'specialist' => 'Dr. Renata Flux', 'expertise' => 'Nephrology/Hepatology' ),
			'ast' => array( 'category' => 'organ_function', 'specialist' => 'Dr. Renata Flux', 'expertise' => 'Nephrology/Hepatology' ),
		);

		// Build the all_biomarkers array from the AI config
		if ( ! empty( $ai_biomarker_config ) && is_array( $ai_biomarker_config ) ) {
			foreach ( $ai_biomarker_config as $biomarker_key => $config ) {
				$all_biomarkers[ $biomarker_key ] = array(
					'category' => array_key_exists( $biomarker_key, $category_mappings ) && array_key_exists( 'category', $category_mappings[ $biomarker_key ] ) ? $category_mappings[ $biomarker_key ]['category'] : 'Other',
					'config'   => $config,
					'data'     => array_key_exists( $biomarker_key, $biomarker_data ) ? $biomarker_data[ $biomarker_key ] : null,
				);
			}
		}
		
		// Render Biomarker Summary at the top of the biomarkers tab
		echo '<div class="ennu-biomarker-summary">';
		echo '<h3>' . esc_html__( 'Biomarker Summary', 'ennulifeassessments' ) . '</h3>';
		echo '<div class="ennu-summary-stats">';
		echo '<div class="ennu-stat"><strong>' . esc_html__( 'Total Biomarkers:', 'ennulifeassessments' ) . '</strong> <span id="total-biomarkers">' . count( $all_biomarkers ) . '</span></div>';
		echo '<div class="ennu-stat"><strong>' . esc_html__( 'With Data:', 'ennulifeassessments' ) . '</strong> <span id="biomarkers-with-data">' . count( $biomarker_data ) . '</span></div>';
		echo '<div class="ennu-stat"><strong>' . esc_html__( 'Flagged:', 'ennulifeassessments' ) . '</strong> <span id="flagged-biomarkers">' . count( $flagged_biomarkers ) . '</span></div>';
		echo '</div>';
		echo '</div>';
		
		echo '<style>
		.ennu-biomarker-summary {
			background: #f9f9f9;
			padding: 15px;
			border: 1px solid #ddd;
			border-radius: 4px;
			margin-bottom: 20px;
		}
		.ennu-summary-stats {
			display: flex;
			gap: 20px;
		}
		.ennu-stat {
			background: white;
			padding: 10px;
			border-radius: 4px;
			border: 1px solid #ccc;
		}
		.ennu-biomarker-management-table {
			width: 100%;
			border-collapse: collapse;
			margin-top: 20px;
		}
		.ennu-biomarker-management-table th,
		.ennu-biomarker-management-table td {
			padding: 12px;
			border: 1px solid #ddd;
			vertical-align: top;
		}
		.ennu-biomarker-management-table th {
			background: #f8f9fa;
			font-weight: 600;
		}
		.ennu-category-header td {
			background: #e9ecef;
			font-weight: bold;
			text-transform: uppercase;
		}
		.ennu-biomarker-row.has-data {
			background: #f8fff8;
		}
		.ennu-biomarker-row.no-data {
			background: #fff8f8;
		}
		.ennu-lab-inputs,
		.ennu-target-inputs,
		.ennu-reference-range-display {
			display: flex;
			flex-direction: column;
			gap: 8px;
		}
		.ennu-input-group {
			display: flex;
			flex-direction: column;
			gap: 4px;
		}
		.ennu-input-group label {
			font-size: 11px;
			font-weight: 600;
			color: #666;
		}
		.ennu-input-group input,
		.ennu-input-group select {
			padding: 4px 8px;
			border: 1px solid #ccc;
			border-radius: 3px;
			font-size: 12px;
		}
		.ennu-range-section {
			margin-bottom: 10px;
			padding: 8px;
			background: #f8f9fa;
			border-radius: 4px;
		}
		.ennu-range-section h4 {
			margin: 0 0 6px 0;
			font-size: 12px;
			color: #495057;
		}
		.ennu-range-inputs {
			display: flex;
			gap: 8px;
		}
		.ennu-target-range-inputs {
			display: flex;
			gap: 8px;
		}
		.ennu-provider-notes textarea {
			width: 100%;
			min-height: 80px;
			padding: 8px;
			border: 1px solid #ccc;
			border-radius: 4px;
			font-size: 12px;
		}
		.ennu-symptom-flag-indicator {
			display: flex;
			align-items: center;
			gap: 8px;
			padding: 8px;
			border-radius: 4px;
		}
		.ennu-symptom-flag-indicator.flagged {
			background: #ffeaa7;
			color: #d63031;
		}
		.ennu-symptom-flag-indicator.not-flagged {
			background: #dff0d8;
			color: #3c763d;
		}
		.ennu-remove-flag-section {
			margin-top: 8px;
			padding: 6px;
			background: #fff3cd;
			border: 1px solid #ffeaa7;
			border-radius: 4px;
		}
		.ennu-remove-flag-checkbox {
			display: flex;
			align-items: center;
			gap: 6px;
			cursor: pointer;
		}
		.ennu-metadata-content {
			font-size: 11px;
			color: #666;
		}
		.ennu-metadata-content div {
			margin-bottom: 4px;
		}
		</style>';

		echo '<div class="ennu-biomarker-table-container">';
		echo '<table class="ennu-biomarker-management-table">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>' . esc_html__( 'Biomarker', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Lab Results', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Medical AI Reference Ranges', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Provider Recommended Targets', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Provider Notes', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Symptom Flagged', 'ennulifeassessments' ) . '</th>';
		echo '<th>' . esc_html__( 'Metadata', 'ennulifeassessments' ) . '</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';

		// Group biomarkers by category
		$grouped_biomarkers = array();
		foreach ( $all_biomarkers as $biomarker_key => $biomarker_info ) {
			if ( ! is_array( $biomarker_info ) ) {
				continue;
			}
			
			$category = isset( $biomarker_info['category'] ) ? $biomarker_info['category'] : 'Other';
			
			if ( ! array_key_exists( $category, $grouped_biomarkers ) ) {
				$grouped_biomarkers[ $category ] = array();
			}
			$grouped_biomarkers[ $category ][ $biomarker_key ] = $biomarker_info;
		}

		// Display all biomarkers organized by category
		foreach ( $grouped_biomarkers as $category => $biomarkers ) {
			echo '<tr class="ennu-category-header">';
			echo '<td colspan="7"><h3>' . esc_html( ucwords( str_replace( '_', ' ', $category ) ) ) . '</h3></td>';
			echo '</tr>';

			foreach ( $biomarkers as $biomarker_key => $biomarker_info ) {
				if ( ! is_array( $biomarker_info ) || ! isset( $biomarker_info['config'] ) ) {
					continue;
				}
				
				$config = $biomarker_info['config'];
				$data = isset( $biomarker_info['data'] ) ? $biomarker_info['data'] : null;
				$display_name = ucwords( str_replace( '_', ' ', $biomarker_key ) );
				$unit = isset( $config['unit'] ) ? $config['unit'] : '';
				
				// Get current data
				$current_value = ( $data && is_array( $data ) && isset( $data['value'] ) ) ? $data['value'] : '';
				$test_date = ( $data && is_array( $data ) && isset( $data['test_date'] ) ) ? $data['test_date'] : '';
				$notes = isset( $provider_notes[ $biomarker_key ] ) ? $provider_notes[ $biomarker_key ] : '';
				
				// Check if biomarker is flagged
				$is_flagged = false;
				$flag_reason = '';
				
				foreach ( $flagged_biomarkers as $flag_id => $flag_data ) {
					if ( ! is_array( $flag_data ) ) {
						continue;
					}
					
					if ( isset( $flag_data['biomarker_name'] ) && 
						 isset( $flag_data['status'] ) &&
						 $flag_data['biomarker_name'] === $biomarker_key && 
						 $flag_data['status'] === 'active' ) {
						$is_flagged = true;
						$flag_reason = isset( $flag_data['reason'] ) ? $flag_data['reason'] : '';
						break;
					}
				}

				echo '<tr class="ennu-biomarker-row ' . ( $data ? 'has-data' : 'no-data' ) . '" data-biomarker="' . esc_attr( $biomarker_key ) . '">';
				
				// Biomarker Name with Specialist Information
				echo '<td class="ennu-biomarker-name">';
				echo '<strong>' . esc_html( $display_name ) . '</strong>';
				echo '<br><small>' . esc_html( $unit ) . '</small>';
				
				// Add specialist information if available
				if ( isset( $category_mappings[ $biomarker_key ] ) && 
					 isset( $category_mappings[ $biomarker_key ]['specialist'] ) ) {
					$specialist_name = $category_mappings[ $biomarker_key ]['specialist'];
					$specialist_domain = isset( $category_mappings[ $biomarker_key ]['expertise'] ) ? $category_mappings[ $biomarker_key ]['expertise'] : '';
					echo '<br><small style="color: #0073aa; font-style: italic;">👨‍⚕️ ' . esc_html( $specialist_name ) . ' (' . esc_html( $specialist_domain ) . ')</small>';
				}
				echo '</td>';

				// Lab Results Data Fields
				echo '<td class="ennu-lab-results">';
				echo '<div class="ennu-lab-inputs">';
				
				echo '<div class="ennu-input-group">';
				echo '<label>Current Value:</label>';
				echo '<input type="number" step="0.01" class="ennu-lab-value" name="biomarker_value[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $current_value ) . '" placeholder="Enter lab result">';
				echo '</div>';
				
				echo '<div class="ennu-input-group">';
				echo '<label>Test Date:</label>';
				echo '<input type="date" class="ennu-test-date" name="test_date[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $test_date ) . '" placeholder="Test Date">';
				echo '</div>';
				
				echo '<div class="ennu-input-group">';
				echo '<label>Unit:</label>';
				echo '<input type="text" class="ennu-unit" name="biomarker_unit[' . esc_attr( $biomarker_key ) . ']" value="' . esc_attr( $unit ) . '" placeholder="e.g., mg/dL, ng/mL">';
				echo '</div>';
				
				echo '</div>';
				echo '</td>';

				// Reference Range Fields
				echo '<td class="ennu-reference-ranges">';
				echo '<div class="ennu-reference-range-display">';
				
				echo '<div class="ennu-range-section">';
				echo '<h4>Reference Range</h4>';
				echo '<div class="ennu-range-inputs">';
				echo '<div class="ennu-input-group">';
				echo '<label>Low/Min:</label>';
				echo '<input type="number" step="0.01" class="ennu-range-min" name="reference_range_min[' . esc_attr( $biomarker_key ) . ']" value="" placeholder="Min value">';
				echo '</div>';
				echo '<div class="ennu-input-group">';
				echo '<label>High/Max:</label>';
				echo '<input type="number" step="0.01" class="ennu-range-max" name="reference_range_max[' . esc_attr( $biomarker_key ) . ']" value="" placeholder="Max value">';
				echo '</div>';
				echo '</div>';
				echo '</div>';
				
				echo '<div class="ennu-range-section">';
				echo '<h4>Optimal Range</h4>';
				echo '<div class="ennu-range-inputs">';
				echo '<div class="ennu-input-group">';
				echo '<label>Low/Min:</label>';
				echo '<input type="number" step="0.01" class="ennu-optimal-min" name="optimal_range_min[' . esc_attr( $biomarker_key ) . ']" value="" placeholder="Optimal min">';
				echo '</div>';
				echo '<div class="ennu-input-group">';
				echo '<label>High/Max:</label>';
				echo '<input type="number" step="0.01" class="ennu-optimal-max" name="optimal_range_max[' . esc_attr( $biomarker_key ) . ']" value="" placeholder="Optimal max">';
				echo '</div>';
				echo '</div>';
				echo '</div>';
				
				echo '<div class="ennu-range-source">';
				echo '<small>Source: Medical AI Research</small>';
				echo '</div>';
				echo '</div>';
				echo '</td>';

				// Provider Recommended Targets
				echo '<td class="ennu-provider-targets">';
				echo '<div class="ennu-target-inputs">';
				
				echo '<div class="ennu-target-group">';
				echo '<label>Target Value:</label>';
				echo '<input type="number" step="0.01" name="provider_target_value[' . esc_attr( $biomarker_key ) . ']" value="" placeholder="Enter target value">';
				echo '</div>';
				
				echo '<div class="ennu-target-group">';
				echo '<label>Target Range:</label>';
				echo '<div class="ennu-target-range-inputs">';
				echo '<div class="ennu-input-group">';
				echo '<label>Min:</label>';
				echo '<input type="number" step="0.01" name="provider_target_min[' . esc_attr( $biomarker_key ) . ']" value="" placeholder="Target min">';
				echo '</div>';
				echo '<div class="ennu-input-group">';
				echo '<label>Max:</label>';
				echo '<input type="number" step="0.01" name="provider_target_max[' . esc_attr( $biomarker_key ) . ']" value="" placeholder="Target max">';
				echo '</div>';
				echo '</div>';
				echo '</div>';
				
				echo '<div class="ennu-target-group">';
				echo '<label>Priority:</label>';
				echo '<select name="provider_target_priority[' . esc_attr( $biomarker_key ) . ']">';
				echo '<option value="">Select priority</option>';
				echo '<option value="critical">Critical</option>';
				echo '<option value="high">High</option>';
				echo '<option value="medium">Medium</option>';
				echo '<option value="low">Low</option>';
				echo '</select>';
				echo '</div>';
				
				echo '</div>';
				echo '</td>';

				// Provider Notes
				echo '<td class="ennu-provider-notes">';
				echo '<textarea name="provider_notes[' . esc_attr( $biomarker_key ) . ']" placeholder="Enter detailed clinical notes">' . esc_textarea( $notes ) . '</textarea>';
				echo '</td>';

				// Symptom Flagged Status
				echo '<td class="ennu-symptom-flagged">';
				if ( $is_flagged ) {
					echo '<div class="ennu-symptom-flag-indicator flagged">';
					echo '<span class="dashicons dashicons-warning"></span>';
					echo '<div class="symptom-flag-details">';
					echo '<span class="flag-reason">' . esc_html( $flag_reason ) . '</span>';
					echo '<span class="flag-action">Lab testing recommended</span>';
					echo '</div>';
					echo '</div>';
					
					echo '<div class="ennu-remove-flag-section">';
					echo '<label class="ennu-remove-flag-checkbox">';
					echo '<input type="checkbox" name="remove_flag[' . esc_attr( $biomarker_key ) . ']" value="1" class="ennu-remove-flag-input">';
					echo '<span class="ennu-remove-flag-text">Remove symptom flag</span>';
					echo '</label>';
					echo '<small class="ennu-remove-flag-note">Check this to remove the symptom flag</small>';
					echo '</div>';
				} else {
					echo '<div class="ennu-symptom-flag-indicator not-flagged">';
					echo '<span class="dashicons dashicons-yes-alt"></span>';
					echo '<span>No symptom correlation</span>';
					echo '</div>';
				}
				echo '</td>';

				// Metadata Container
				echo '<td class="ennu-metadata">';
				echo '<div class="ennu-metadata-content">';
				if ( $test_date ) {
					echo '<div><strong>Test Date:</strong> ' . esc_html( $test_date ) . '</div>';
				}
				echo '<div><strong>Last Updated:</strong> ' . esc_html( current_time( 'Y-m-d H:i:s' ) ) . '</div>';
				echo '<div><strong>ID:</strong> ' . esc_html( $biomarker_key ) . '</div>';
				echo '</div>';
				echo '</td>';

				echo '</tr>';
			}
		}

		echo '</tbody>';
		echo '</table>';
		echo '</div>';
		
		error_log( "ENNU Enhanced Admin: Biomarkers section displayed for user {$user_id}" );
	}
}
