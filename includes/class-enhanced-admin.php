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
		add_action( 'admin_menu', array( $this, 'add_biomarker_admin_pages' ) );
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
		if ( isset( $_POST['import_lab_data'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'import_lab_data' ) ) {
			$user_id = intval( $_POST['user_id'] );
			$lab_data = $this->parse_lab_data_input( $_POST['lab_data'] );
			
			$result = ENNU_Biomarker_Manager::import_lab_results( $user_id, $lab_data );
			
			if ( is_wp_error( $result ) ) {
				echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
			} else {
				echo '<div class="notice notice-success"><p>Lab data imported successfully!</p></div>';
			}
		}
		
		if ( isset( $_POST['add_recommendations'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'add_doctor_recommendations' ) ) {
			$user_id = intval( $_POST['user_id'] );
			$recommendations = array(
				'biomarker_targets' => $this->parse_lab_data_input( $_POST['biomarker_targets'] ),
				'lifestyle_advice' => sanitize_textarea_field( $_POST['lifestyle_advice'] )
			);
			
			$result = ENNU_Biomarker_Manager::add_doctor_recommendations( $user_id, $recommendations );
			
			if ( is_wp_error( $result ) ) {
				echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
			} else {
				echo '<div class="notice notice-success"><p>Doctor recommendations added successfully!</p></div>';
			}
		}
		
		?>
		<div class="wrap">
			<h1>Biomarker Management</h1>
			
			<h2>Import Lab Data</h2>
			<form method="post" action="">
				<?php wp_nonce_field( 'import_lab_data' ); ?>
				
				<table class="form-table">
					<tr>
						<th scope="row">User ID</th>
						<td><input type="number" name="user_id" required /></td>
					</tr>
					<tr>
						<th scope="row">Lab Data (JSON)</th>
						<td>
							<textarea name="lab_data" rows="10" cols="50" placeholder='{"Total_Testosterone": {"value": 650, "unit": "ng/dL", "test_date": "2024-01-15"}}'></textarea>
							<p class="description">Enter lab data in JSON format</p>
						</td>
					</tr>
				</table>
				
				<p class="submit">
					<input type="submit" name="import_lab_data" class="button-primary" value="Import Lab Data" />
				</p>
			</form>
			
			<h2>Doctor Recommendations</h2>
			<form method="post" action="">
				<?php wp_nonce_field( 'add_doctor_recommendations' ); ?>
				
				<table class="form-table">
					<tr>
						<th scope="row">User ID</th>
						<td><input type="number" name="user_id" required /></td>
					</tr>
					<tr>
						<th scope="row">Biomarker Targets (JSON)</th>
						<td>
							<textarea name="biomarker_targets" rows="5" cols="50" placeholder='{"Total_Testosterone": 700, "Vitamin_D": 50}'></textarea>
							<p class="description">Enter target values for biomarkers</p>
						</td>
					</tr>
					<tr>
						<th scope="row">Lifestyle Advice</th>
						<td>
							<textarea name="lifestyle_advice" rows="5" cols="50" placeholder="Recommended lifestyle changes..."></textarea>
						</td>
					</tr>
				</table>
				
				<p class="submit">
					<input type="submit" name="add_recommendations" class="button-primary" value="Add Recommendations" />
				</p>
			</form>
		</div>
		<?php
	}
	
	private function parse_lab_data_input( $input ) {
		$data = json_decode( stripslashes( $input ), true );
		return is_array( $data ) ? $data : array();
	}

	/**
	 * The single, correct function to build the entire admin menu.
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'ENNU Life', 'ennulifeassessments' ),
			__( 'ENNU Life', 'ennulifeassessments' ),
			'manage_options',
			'ennu-life',
			array( $this, 'render_admin_dashboard_page' ),
			'dashicons-heart',
			30
		);

		add_submenu_page(
			'ennu-life',
			__( 'Dashboard', 'ennulifeassessments' ),
			__( 'Dashboard', 'ennulifeassessments' ),
			'manage_options',
			'ennu-life',
			array( $this, 'render_admin_dashboard_page' )
		);

		add_submenu_page(
			'ennu-life',
			__( 'Analytics', 'ennulifeassessments' ),
			__( 'Analytics', 'ennulifeassessments' ),
			'manage_options',
			'ennu-life-analytics',
			array( $this, 'render_analytics_dashboard_page' )
		);

		add_submenu_page(
			'ennu-life',
			__( 'Assessments', 'ennulifeassessments' ),
			__( 'Assessments', 'ennulifeassessments' ),
			'manage_options',
			'ennu-life-assessments',
			array( $this, 'assessments_page' )
		);

		add_submenu_page(
			'ennu-life',
			__( 'Settings', 'ennulifeassessments' ),
			__( 'Settings', 'ennulifeassessments' ),
			'manage_options',
			'ennu-life-settings',
			array( $this, 'settings_page' )
		);

		add_submenu_page(
			'ennu-life',
			__( 'HubSpot Booking', 'ennulifeassessments' ),
			__( 'HubSpot Booking', 'ennulifeassessments' ),
			'manage_options',
			'ennu-life-hubspot-booking',
			array( $this, 'hubspot_booking_page' )
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
		if ( current_user_can('administrator') ) {
			if ( isset($_POST['ennu_clear_user_data']) && check_admin_referer('ennu_clear_user_data_action') ) {
				$user_id = get_current_user_id();
				global $wpdb;
				$meta_keys = $wpdb->get_col( $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta WHERE user_id = %d AND meta_key LIKE 'ennu_%'", $user_id ) );
				foreach ( $meta_keys as $meta_key ) {
					delete_user_meta( $user_id, $meta_key );
				}
				echo '<div class="notice notice-success is-dismissible"><strong>All ENNU assessment data for your user has been cleared.</strong></div>';
			}
			echo '<form method="post" style="margin: 30px 0; padding: 20px; background: #ffe; border: 2px solid #fc0; border-radius: 8px;">';
			wp_nonce_field('ennu_clear_user_data_action');
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
		$average_pillar_scores = ENNU_Assessment_Scoring::calculate_average_pillar_scores( $user_id );
		$dob_combined          = get_user_meta( $user_id, 'ennu_global_user_dob_combined', true );
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

		$settings = $this->get_plugin_settings();
		$assessment_keys = array_keys( ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions() );
		$pages = get_pages();
		$page_options = array();
		foreach ( $pages as $page ) {
			$page_options[ $page->ID ] = $page->post_title;
		}

		// Tab Navigation
		echo '<div class="ennu-tabs">';
		echo '<div class="ennu-tab active" data-tab="pages">📄 Page Management</div>';
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
			'hair' => 'hair',
			'ed-treatment' => 'ed-treatment',
			'weight-loss' => 'weight-loss',
			'health' => 'health',
			'health-optimization' => 'health-optimization',
			'skin' => 'skin',
			'hormone' => 'hormone',
			'testosterone' => 'testosterone',
			'menopause' => 'menopause',
			'sleep' => 'sleep'
		);
		
		$filtered_assessments = array();
		foreach ( $assessment_menu_order as $slug => $key ) {
			if ( in_array( $key, $assessment_keys ) ) {
				$filtered_assessments[ $slug ] = $key;
			}
		}
		
		// Assessment Form Pages
		foreach ( $filtered_assessments as $slug => $key ) {
			$label = ucwords( str_replace( '-', ' ', $key ) );
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
			if ( $slug === 'welcome' ) continue;
			$label = ucwords( str_replace( '-', ' ', $key ) );
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
			if ( $slug === 'welcome' ) continue;
			$label = ucwords( str_replace( '-', ' ', $key ) );
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
			if ( $slug === 'welcome' ) continue;
			$label = ucwords( str_replace( '-', ' ', $key ) );
			$hierarchical_slug = "assessments/{$slug}/consultation";
			$this->render_enhanced_page_dropdown( $hierarchical_slug, $label . " Consultation (/assessments/{$slug}/consultation/)", $settings['page_mappings'], $page_options, "/assessments/{$slug}/consultation/" );
		}
		echo '</div></div>';

		submit_button( 'Save All Page Settings', 'primary', 'submit', false, array('style' => 'margin-top: 1rem;') );
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
		
		$this->display_menu_update_results();
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

		$settings = $this->get_hubspot_settings();
		$consultation_types = $this->get_consultation_types();

		echo '<form method="post" action="">';
		wp_nonce_field( 'ennu_hubspot_update', 'ennu_hubspot_nonce' );

		echo '<h2>' . esc_html__( 'HubSpot Configuration', 'ennulifeassessments' ) . '</h2>';
		echo '<table class="form-table" role="presentation">';
		
		echo '<tr><th scope="row"><label for="hubspot_portal_id">' . esc_html__( 'HubSpot Portal ID', 'ennulifeassessments' ) . '</label></th>';
		echo '<td><input type="text" id="hubspot_portal_id" name="hubspot_portal_id" value="' . esc_attr( $settings['portal_id'] ?? '' ) . '" class="regular-text" placeholder="12345678">';
		echo '<p class="description">' . esc_html__( 'Your HubSpot portal ID (found in your HubSpot account settings)', 'ennulifeassessments' ) . '</p></td></tr>';

		echo '<tr><th scope="row"><label for="hubspot_api_key">' . esc_html__( 'HubSpot API Key (Optional)', 'ennulifeassessments' ) . '</label></th>';
		echo '<td><input type="password" id="hubspot_api_key" name="hubspot_api_key" value="' . esc_attr( $settings['api_key'] ?? '' ) . '" class="regular-text">';
		echo '<p class="description">' . esc_html__( 'Required for advanced integrations with WP Fusion', 'ennulifeassessments' ) . '</p></td></tr>';

		echo '</table>';

		echo '<h2>' . esc_html__( 'Consultation Booking Embeds', 'ennulifeassessments' ) . '</h2>';
		echo '<p>' . esc_html__( 'Paste your HubSpot calendar embed codes for each consultation type. These will be used in the consultation shortcodes.', 'ennulifeassessments' ) . '</p>';

		foreach ( $consultation_types as $type => $config ) {
			echo '<h3>' . esc_html( $config['title'] ) . '</h3>';
			echo '<table class="form-table" role="presentation">';
			
			echo '<tr><th scope="row"><label for="embed_' . esc_attr( $type ) . '">' . esc_html__( 'Calendar Embed Code', 'ennulifeassessments' ) . '</label></th>';
			echo '<td><textarea id="embed_' . esc_attr( $type ) . '" name="embeds[' . esc_attr( $type ) . '][embed_code]" rows="6" cols="80" class="large-text code">' . esc_textarea( $settings['embeds'][ $type ]['embed_code'] ?? '' ) . '</textarea>';
			echo '<p class="description">' . esc_html__( 'Paste the complete HubSpot calendar embed code here', 'ennulifeassessments' ) . '</p></td></tr>';

			echo '<tr><th scope="row"><label for="meeting_type_' . esc_attr( $type ) . '">' . esc_html__( 'Meeting Type ID', 'ennulifeassessments' ) . '</label></th>';
			echo '<td><input type="text" id="meeting_type_' . esc_attr( $type ) . '" name="embeds[' . esc_attr( $type ) . '][meeting_type]" value="' . esc_attr( $settings['embeds'][ $type ]['meeting_type'] ?? '' ) . '" class="regular-text" placeholder="consultation-30min">';
			echo '<p class="description">' . esc_html__( 'The HubSpot meeting type identifier', 'ennulifeassessments' ) . '</p></td></tr>';

			echo '<tr><th scope="row"><label for="pre_populate_' . esc_attr( $type ) . '">' . esc_html__( 'Pre-populate Fields', 'ennulifeassessments' ) . '</label></th>';
			echo '<td>';
			$pre_populate_fields = $settings['embeds'][ $type ]['pre_populate_fields'] ?? array( 'firstname', 'lastname', 'email' );
			$available_fields = array(
				'firstname' => 'First Name',
				'lastname' => 'Last Name', 
				'email' => 'Email',
				'phone' => 'Phone',
				'assessment_results' => 'Assessment Results'
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
			'hair_restoration' => array(
				'title' => 'Hair Restoration Consultation',
				'description' => 'Book a consultation with hair restoration specialists',
				'icon' => '🦱',
				'color' => '#667eea'
			),
			'ed_treatment' => array(
				'title' => 'ED Treatment Consultation', 
				'description' => 'Book a confidential consultation for ED treatment options',
				'icon' => '🔒',
				'color' => '#f093fb'
			),
			'weight_loss' => array(
				'title' => 'Weight Loss Consultation',
				'description' => 'Book a consultation for personalized weight loss plans',
				'icon' => '⚖️',
				'color' => '#4facfe'
			),
			'health_optimization' => array(
				'title' => 'Health Optimization Consultation',
				'description' => 'Book a consultation for comprehensive health optimization',
				'icon' => '🏥',
				'color' => '#fa709a'
			),
			'skin_care' => array(
				'title' => 'Skin Care Consultation',
				'description' => 'Book a consultation with skincare specialists',
				'icon' => '✨',
				'color' => '#a8edea'
			),
			'general_consultation' => array(
				'title' => 'General Health Consultation',
				'description' => 'Book a general health consultation',
				'icon' => '👨‍⚕️',
				'color' => '#667eea'
			),
			'schedule_call' => array(
				'title' => 'Schedule a Call',
				'description' => 'General call scheduling for any health concerns',
				'icon' => '📞',
				'color' => '#4facfe'
			),
			'ennu_life_score' => array(
				'title' => 'Get Your ENNU Life Score',
				'description' => 'Book a consultation to get your personalized ENNU Life Score',
				'icon' => '📊',
				'color' => '#fa709a'
			),
			'health_optimization_results' => array(
				'title' => 'Health Optimization Results Consultation',
				'description' => 'Discuss your health optimization assessment results',
				'icon' => '🏥',
				'color' => '#fa709a'
			),
			'confidential_consultation' => array(
				'title' => 'Confidential Consultation',
				'description' => 'Book a confidential consultation for sensitive health matters',
				'icon' => '🔒',
				'color' => '#f093fb'
			)
		);
	}

	/**
	 * Get HubSpot settings
	 */
	private function get_hubspot_settings() {
		return get_option( 'ennu_hubspot_settings', array(
			'portal_id' => '',
			'api_key' => '',
			'embeds' => array(),
			'wpfusion_enabled' => false,
			'auto_create_contact' => false
		) );
	}

	/**
	 * Save HubSpot settings
	 */
	private function save_hubspot_settings() {
		$settings = array(
			'portal_id' => sanitize_text_field( $_POST['hubspot_portal_id'] ?? '' ),
			'api_key' => sanitize_text_field( $_POST['hubspot_api_key'] ?? '' ),
			'embeds' => array(),
			'wpfusion_enabled' => isset( $_POST['wpfusion_enabled'] ),
			'auto_create_contact' => isset( $_POST['auto_create_contact'] )
		);

		if ( isset( $_POST['embeds'] ) && is_array( $_POST['embeds'] ) ) {
			foreach ( $_POST['embeds'] as $type => $embed_data ) {
				$settings['embeds'][ $type ] = array(
					'embed_code' => wp_kses_post( $embed_data['embed_code'] ?? '' ),
					'meeting_type' => sanitize_text_field( $embed_data['meeting_type'] ?? '' ),
					'pre_populate_fields' => isset( $embed_data['pre_populate_fields'] ) ? array_map( 'sanitize_text_field', $embed_data['pre_populate_fields'] ) : array()
				);
			}
		}

		update_option( 'ennu_hubspot_settings', $settings );
	}

	/**
	 * Enhanced page dropdown renderer for the new admin interface
	 */
	private function render_enhanced_page_dropdown( $slug, $label, $current_mappings, $page_options, $url_path ) {
		$page_id = isset( $current_mappings[ $slug ] ) ? $current_mappings[ $slug ] : '';
		$selected_url = $page_id ? get_permalink( $page_id ) : '';
		$shortcode = '';
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
			$permalink = get_permalink( $page_id );
			$permalink_display = esc_url( $permalink );
			$permalink_text = esc_html( str_replace( home_url(), '', $permalink ) );
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
		$all_required_pages = $this->get_all_required_pages();
		$total_required_pages = count( $all_required_pages );
		
		$assigned_pages = 0;
		$missing_pages = 0;
		$published_pages = 0;
		$draft_pages = 0;
		$menu_items = 0;
		
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
			$page_id = $page_mappings[ $slug ] ?? '';
			$page_title = $page_data['title'] ?? ucwords( str_replace( array('-', '_', '/'), array(' ', ' ', ' → '), $slug ) );
			$page_url_path = $page_data['url_path'] ?? '/' . $slug . '/';
			
			if ( $page_id && get_post( $page_id ) ) {
				$actual_title = get_the_title( $page_id );
				$page_url = get_permalink( $page_id );
				$status = get_post_status( $page_id );
				$status_icon = $status === 'publish' ? '✅' : '⚠️';
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
			'Core Pages' => array('dashboard', 'assessments', 'registration', 'signup', 'assessment-results'),
			'Consultation Pages' => array('call', 'ennu-life-score', 'health-optimization-results'),
		);
		
		// Add assessment pages dynamically
		$assessment_keys = array_keys( ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions() );
		$assessment_menu_order = array(
			'hair' => 'hair',
			'ed-treatment' => 'ed-treatment',
			'weight-loss' => 'weight-loss',
			'health' => 'health',
			'health-optimization' => 'health-optimization',
			'skin' => 'skin',
			'hormone' => 'hormone',
			'testosterone' => 'testosterone',
			'menopause' => 'menopause',
			'sleep' => 'sleep'
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
			if ( empty( $category_pages ) ) continue;
			
			echo '<div style="background: white; padding: 1rem; border-radius: 6px; margin-bottom: 1rem; border-left: 3px solid #667eea;">';
			echo '<h6 style="margin: 0 0 0.75rem 0; color: #2d3748; font-weight: 600;">' . esc_html( $category_name ) . ' (' . count( $category_pages ) . ' pages)</h6>';
			
			foreach ( $category_pages as $page_slug ) {
				$page_id = $page_mappings[ $page_slug ] ?? '';
				$page_data = $all_required_pages[ $page_slug ] ?? array();
				$page_title = $page_data['title'] ?? ucwords( str_replace( array('-', '_', '/'), array(' ', ' ', ' → '), $page_slug ) );
				$page_url_path = $page_data['url_path'] ?? '/' . $page_slug . '/';
				
				if ( $page_id && get_post( $page_id ) ) {
					$actual_title = get_the_title( $page_id );
					$page_url = get_permalink( $page_id );
					$status = get_post_status( $page_id );
					$status_icon = $status === 'publish' ? '✅' : '⚠️';
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
			'dashboard' => array(
				'title' => 'Dashboard Page',
				'url_path' => '/dashboard/',
				'content' => '[ennu-user-dashboard]'
			),
			'assessments' => array(
				'title' => 'Assessments Landing Page',
				'url_path' => '/assessments/',
				'content' => '[ennu-assessments]'
			),
			'registration' => array(
				'title' => 'Registration Page',
				'url_path' => '/registration/',
				'content' => '[ennu-welcome]'
			),
			'signup' => array(
				'title' => 'Sign Up Page',
				'url_path' => '/signup/',
				'content' => '[ennu-signup]'
			),
			'assessment-results' => array(
				'title' => 'Generic Results Page',
				'url_path' => '/assessment-results/',
				'content' => '[ennu-assessment-results]'
			)
		);
		
		// Consultation pages
		$consultation_pages = array(
			'call' => array(
				'title' => 'Schedule a Call Page',
				'url_path' => '/call/',
				'content' => 'Schedule your free health consultation with our experts.'
			),
			'ennu-life-score' => array(
				'title' => 'Get Your ENNU Life Score Page',
				'url_path' => '/ennu-life-score/',
				'content' => 'Discover your personalized ENNU Life Score and health insights.'
			),
			'health-optimization-results' => array(
				'title' => 'Health Optimization Results Page',
				'url_path' => '/health-optimization-results/',
				'content' => '[ennu-health-optimization-results]'
			)
		);
		
		// Assessment pages
		$assessment_keys = array_keys( ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions() );
		// Use the actual assessment keys from definitions (with hyphens)
		$assessment_menu_order = array(
			'hair' => 'hair',
			'ed-treatment' => 'ed-treatment',
			'weight-loss' => 'weight-loss',
			'health' => 'health',
			'health-optimization' => 'health-optimization',
			'skin' => 'skin',
			'hormone' => 'hormone',
			'testosterone' => 'testosterone',
			'menopause' => 'menopause',
			'sleep' => 'sleep'
		);
		
		$filtered_assessments = array();
		foreach ( $assessment_menu_order as $slug => $key ) {
			if ( in_array( $key, $assessment_keys ) ) {
				$filtered_assessments[ $slug ] = $key;
			}
		}
		
		// Add assessment form pages
		foreach ( $filtered_assessments as $slug => $key ) {
			$label = ucwords( str_replace( '-', ' ', $key ) );
			$all_required_pages["assessments/{$slug}"] = array(
				'title' => $label . ' Assessment Form',
				'url_path' => "/assessments/{$slug}/",
				'content' => "[ennu-{$slug}]"
			);
		}
		
		// Add assessment results pages
		foreach ( $filtered_assessments as $slug => $key ) {
			$label = ucwords( str_replace( '-', ' ', $key ) );
			$all_required_pages["assessments/{$slug}/results"] = array(
				'title' => $label . ' Results',
				'url_path' => "/assessments/{$slug}/results/",
				'content' => "[ennu-{$slug}-results]"
			);
		}
		
		// Add assessment details pages
		foreach ( $filtered_assessments as $slug => $key ) {
			$label = ucwords( str_replace( '-', ' ', $key ) );
			$all_required_pages["assessments/{$slug}/details"] = array(
				'title' => $label . ' Details',
				'url_path' => "/assessments/{$slug}/details/",
				'content' => "[ennu-{$slug}-assessment-details]"
			);
		}
		
		// Add assessment consultation pages
		foreach ( $filtered_assessments as $slug => $key ) {
			$label = ucwords( str_replace( '-', ' ', $key ) );
			$all_required_pages["assessments/{$slug}/consultation"] = array(
				'title' => $label . ' Consultation',
				'url_path' => "/assessments/{$slug}/consultation/",
				'content' => "[ennu-{$slug}-consultation]"
			);
		}
		
		// Merge all pages
		return array_merge( $core_pages, $consultation_pages, $all_required_pages );
	}

	public function show_user_assessment_fields( $user ) {
		error_log('ENNU Enhanced Admin: show_user_assessment_fields called for user ID: ' . $user->ID);
		if ( ! current_user_can( 'edit_user', $user->ID ) ) {
			echo '<div style="color: red;">ENNU Debug: Insufficient permissions to edit user.</div>';
			return;
		}

		// Debug marker for output confirmation
		echo '<div style="background: #e0f7fa; color: #006064; padding: 8px; margin-bottom: 8px; border-left: 4px solid #00bcd4;">ENNU Debug: show_user_assessment_fields output for user ID ' . esc_html($user->ID) . '</div>';

		// --- Render Health Summary Component ---
		$ennu_life_score       = get_user_meta( $user->ID, 'ennu_life_score', true );
		$average_pillar_scores = get_user_meta( $user->ID, 'ennu_average_pillar_scores', true );
		if ( ! is_array( $average_pillar_scores ) ) {
			$average_pillar_scores = array( 'Mind' => 0, 'Body' => 0, 'Lifestyle' => 0, 'Aesthetics' => 0 );
		}
		include ENNU_LIFE_PLUGIN_PATH . 'templates/admin/user-health-summary.php';
		wp_nonce_field( 'ennu_user_profile_update_' . $user->ID, 'ennu_assessment_nonce' );
		$assessments = array_keys( ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions() );
		echo '<h2>' . esc_html__( 'User Assessment Data', 'ennulifeassessments' ) . '</h2><div class="ennu-admin-tabs">';
		echo '<nav class="ennu-admin-tab-nav"><ul>';
		echo '<li><a href="#tab-global-metrics" class="ennu-admin-tab-active">' . esc_html__( 'Global & Health Metrics', 'ennulifeassessments' ) . '</a></li>';
		foreach ( $assessments as $key ) {
			if ( 'welcome_assessment' === $key || 'welcome' === $key ) { continue; }
			$label = ucwords( str_replace( array( '_', 'assessment' ), ' ', $key ) );
			echo '<li><a href="#tab-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</a></li>';
		}
		echo '</ul></nav>';
		echo '<div id="tab-global-metrics" class="ennu-admin-tab-content ennu-admin-tab-active">';
		$this->display_global_fields_section( $user->ID );
		echo '</div>';
		foreach ( $assessments as $assessment_key ) {
			if ( 'welcome_assessment' === $assessment_key || 'welcome' === $assessment_key ) { continue; }
			echo '<div id="tab-' . esc_attr( $assessment_key ) . '" class="ennu-admin-tab-content">';
			echo '<table class="form-table">';
			$this->display_assessment_fields_editable( $user->ID, $assessment_key );
			echo '</table>';
			echo '<p><button type="button" class="button button-secondary ennu-clear-single-assessment-data" data-assessment-key="' . esc_attr( $assessment_key ) . '" data-user-id="' . esc_attr( $user->ID ) . '">' . esc_html__( 'Clear Data for This Assessment', 'ennulifeassessments' ) . '</button></p>';
			echo '</div>';
		}
		echo '</div>';
		echo '<div class="ennu-admin-actions-section">';
		echo '<h3>' . esc_html__( 'Administrative Actions', 'ennulifeassessments' ) . '</h3>';
		echo '<p>' . esc_html__( 'Use these actions to manage this user\'s assessment data. These actions are immediate and cannot be undone.', 'ennulifeassessments' ) . '</p>';
		echo '<div class="ennu-admin-action-buttons">';
		echo '<button type="button" id="ennu-recalculate-scores" class="button button-primary" data-user-id="' . esc_attr( $user->ID ) . '">' . esc_html__( 'Recalculate All Scores', 'ennulifeassessments' ) . '</button>';
		echo '<button type="button" id="ennu-clear-all-data" class="button button-delete" data-user-id="' . esc_attr( $user->ID ) . '">' . esc_html__( 'Clear All Assessment Data', 'ennulifeassessments' ) . '</button>';
		echo '<span class="spinner"></span>';
		echo '</div></div>';
	}

	private function display_global_fields_section( $user_id ) {
		echo '<table class="form-table">';
		// Debug marker for output confirmation
		echo '<tr><td colspan="2" style="background: #fffde7; color: #f57c00;">ENNU Debug: display_global_fields_section for user ID ' . esc_html($user_id) . '</td></tr>';
		$global_fields = array(
			'first_name'                    => array( 'label' => 'First Name' ),
			'last_name'                     => array( 'label' => 'Last Name' ),
			'user_email'                    => array( 'label' => 'Email' ),
			'ennu_global_gender'            => array( 'label' => 'Gender' ),
			'ennu_global_user_dob_combined' => array( 'label' => 'DOB' ),
			'ennu_global_health_goals'      => array(
				'label'   => 'Health Goals',
				'type'    => 'multiselect',
				'options' => $this->get_health_goal_options(),
			),
			'ennu_global_height_weight'     => array(
				'label' => 'Height & Weight',
				'type'  => 'height_weight',
			),
			'ennu_calculated_bmi'           => array( 'label' => 'Calculated BMI' ),
		);
		foreach ( $global_fields as $key => $data ) {
			$current_value = get_user_meta( $user_id, $key, true );
			if ( $key === 'ennu_global_gender' ) {
				echo '<tr><th>' . esc_html( $data['label'] ) . '</th><td>';
				echo '<select name="ennu_global_gender">
					<option value="">Select Gender</option>
					<option value="male"' . selected( $current_value, 'male', false ) . '>Male</option>
					<option value="female"' . selected( $current_value, 'female', false ) . '>Female</option>
					<option value="other"' . selected( $current_value, 'other', false ) . '>Other</option>
				</select>';
				echo '</td></tr>';
			} elseif ( $key === 'ennu_global_user_dob_combined' ) {
				echo '<tr><th>' . esc_html( $data['label'] ) . '</th><td>';
				echo '<input type="date" name="ennu_global_user_dob_combined" value="' . esc_attr( $current_value ) . '" />';
				echo '</td></tr>';
			} elseif ( $key === 'ennu_global_height_weight' ) {
				$height = isset( $current_value['ft'] ) ? $current_value['ft'] : '';
				$inches = isset( $current_value['in'] ) ? $current_value['in'] : '';
				$weight = isset( $current_value['weight'] ) ? $current_value['weight'] : '';
				echo '<tr><th>' . esc_html( $data['label'] ) . '</th><td>';
				echo 'Height: <input type="number" name="ennu_global_height_weight[ft]" value="' . esc_attr( $height ) . '" min="0" max="8" style="width:50px;" /> ft ';
				echo '<input type="number" name="ennu_global_height_weight[in]" value="' . esc_attr( $inches ) . '" min="0" max="11" style="width:50px;" /> in ';
				echo 'Weight: <input type="number" name="ennu_global_height_weight[weight]" value="' . esc_attr( $weight ) . '" min="0" max="999" style="width:70px;" /> lbs';
				echo '</td></tr>';
			} elseif ( $key === 'ennu_calculated_bmi' ) {
				echo '<tr><th>' . esc_html( $data['label'] ) . '</th><td>' . esc_html( $current_value ) . '</td></tr>';
			} elseif ( $key === 'ennu_global_health_goals' ) {
				$options = $data['options'];
				$selected = is_array( $current_value ) ? $current_value : array();
				echo '<tr><th>' . esc_html( $data['label'] ) . '</th><td>';
				foreach ( $options as $val => $label ) {
					echo '<label><input type="checkbox" name="ennu_global_health_goals[]" value="' . esc_attr( $val ) . '"' . checked( in_array( $val, $selected, true ), true, false ) . '> ' . esc_html( $label ) . '</label> ';
				}
				echo '</td></tr>';
			} else {
				echo '<tr><th>' . esc_html( $data['label'] ) . '</th><td>' . esc_html( $current_value ) . '</td></tr>';
			}
		}
		echo '</table>';
	}

	public function enqueue_admin_assets( $hook ) {
		error_log('ENNU Enhanced Admin: enqueue_admin_assets called with hook: ' . $hook);
		
		// Fallback: check current screen if available
		$screen_id = '';
		if ( function_exists('get_current_screen') ) {
			$screen = get_current_screen();
			if ( $screen ) {
				$screen_id = $screen->id;
				error_log('ENNU Enhanced Admin: get_current_screen() id: ' . $screen_id);
			}
		}
		
		// Debug: log the current page
		global $pagenow;
		error_log('ENNU Enhanced Admin: Current pagenow: ' . ($pagenow ?? 'unknown'));
		
		// More aggressive loading - check multiple conditions
		$should_load = false;
		
		// Check hook names
		if ( in_array( $hook, array( 'profile.php', 'user-edit.php' ), true ) ) {
			$should_load = true;
			error_log('ENNU Enhanced Admin: Loading based on hook: ' . $hook);
		}
		
		// Check screen ID
		if ( in_array( $screen_id, array( 'profile', 'user-edit' ), true ) ) {
			$should_load = true;
			error_log('ENNU Enhanced Admin: Loading based on screen_id: ' . $screen_id);
		}
		
		// Check pagenow
		if ( $pagenow === 'profile.php' || $pagenow === 'user-edit.php' ) {
			$should_load = true;
			error_log('ENNU Enhanced Admin: Loading based on pagenow: ' . $pagenow);
		}
		
		// Check if we're on a profile page by URL
		if ( strpos( $_SERVER['REQUEST_URI'], 'profile.php' ) !== false || strpos( $_SERVER['REQUEST_URI'], 'user-edit.php' ) !== false ) {
			$should_load = true;
			error_log('ENNU Enhanced Admin: Loading based on REQUEST_URI: ' . $_SERVER['REQUEST_URI']);
		}
		
		if ( $should_load ) {
			error_log('ENNU Enhanced Admin: Enqueuing assets for hook: ' . $hook . ', screen_id: ' . $screen_id . ', pagenow: ' . ($pagenow ?? 'unknown'));
			
			wp_enqueue_style( 'ennu-admin-tabs-enhanced', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-tabs-enhanced.css', array(), ENNU_LIFE_VERSION );
			wp_enqueue_style( 'ennu-admin-scores-enhanced', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-scores-enhanced.css', array(), ENNU_LIFE_VERSION );
			wp_enqueue_script( 'ennu-admin-enhanced', ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-admin-enhanced.js', array('jquery'), ENNU_LIFE_VERSION, true );
			
			wp_localize_script( 'ennu-admin-enhanced', 'ennuAdmin', array( 
				'nonce' => wp_create_nonce( 'ennu_admin_nonce' ),
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'confirm_msg' => __( 'Are you sure you want to clear this user\'s assessment data? This action cannot be undone.', 'ennulifeassessments' ),
				'plugin_url' => ENNU_LIFE_PLUGIN_URL,
				'debug' => defined( 'WP_DEBUG' ) && WP_DEBUG
			) );
			
			wp_enqueue_style(
				'ennu-logo-style',
				ENNU_LIFE_PLUGIN_URL . 'assets/css/ennu-logo.css',
				array(),
				ENNU_LIFE_VERSION
			);
			
			// Add inline script for immediate tab initialization
			wp_add_inline_script( 'ennu-admin-enhanced', '
				console.log("ENNU Admin Enhanced: Script enqueued successfully");
				console.log("ENNU Admin Enhanced: Hook: ' . $hook . '");
				console.log("ENNU Admin Enhanced: Screen ID: ' . $screen_id . '");
				console.log("ENNU Admin Enhanced: Pagenow: ' . ($pagenow ?? 'unknown') . '");
				console.log("ENNU Admin Enhanced: REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? 'unknown') . '");
				
				// Force tab initialization on multiple events
				function forceTabInit() {
					console.log("ENNU Admin Enhanced: Force tab init called");
					if (typeof window.initializeEnnuAdmin === "function") {
						console.log("ENNU Admin Enhanced: Calling initializeEnnuAdmin");
						window.initializeEnnuAdmin();
					} else {
						console.log("ENNU Admin Enhanced: initializeEnnuAdmin function not found");
					}
				}
				
				// Try initialization on multiple events
				if (document.readyState === "loading") {
					document.addEventListener("DOMContentLoaded", forceTabInit);
				} else {
					forceTabInit();
				}
				
				// Also try on window load
				window.addEventListener("load", forceTabInit);
				
				// And try after a short delay
				setTimeout(forceTabInit, 100);
				setTimeout(forceTabInit, 500);
				setTimeout(forceTabInit, 1000);
			');
			
			error_log('ENNU Enhanced Admin: Assets enqueued successfully');
		} else {
			error_log('ENNU Enhanced Admin: NOT enqueuing assets - hook: ' . $hook . ', screen_id: ' . $screen_id . ', pagenow: ' . ($pagenow ?? 'unknown') . ', REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? 'unknown'));
		}
	}

	// --- v57.1.0: AJAX Handlers for Admin Actions ---

	public function handle_recalculate_all_scores() {
		check_ajax_referer( 'ennu_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_users' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
		}

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => 'Invalid user ID.' ), 400 );
		}

		// Recalculate ENNU LIFE SCORE which depends on individual scores
		$new_score = ENNU_Assessment_Scoring::calculate_ennu_life_score( $user_id, true ); // Pass true to force recalc

		// Optionally, recalculate BMI if height/weight exists
		$height_weight = get_user_meta( $user_id, 'ennu_global_height_weight', true );
		if ( ! empty( $height_weight['ft'] ) && ! empty( $height_weight['lbs'] ) ) {
			$height_in_total = ( intval( $height_weight['ft'] ) * 12 ) + intval( $height_weight['in'] );
			$weight_lbs      = intval( $height_weight['lbs'] );
			if ( $height_in_total > 0 && $weight_lbs > 0 ) {
				$bmi = ( $weight_lbs / ( $height_in_total * $height_in_total ) ) * 703;
				update_user_meta( $user_id, 'ennu_calculated_bmi', round( $bmi, 1 ) );
			}
		}

		wp_send_json_success( array( 'message' => 'All scores recalculated successfully.' ) );
	}

	public function handle_clear_all_assessment_data() {
		check_ajax_referer( 'ennu_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_users' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
		}

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		if ( ! $user_id ) {
			wp_send_json_error( array( 'message' => 'Invalid user ID.' ), 400 );
		}

		global $wpdb;
		$wpdb->delete( $wpdb->usermeta, array( 'user_id' => $user_id, 'meta_key' => 'ennu_%' ), array( '%d', '%s' ) );
		// A more direct, powerful approach requires a LIKE comparison
		$meta_keys_to_delete = $wpdb->get_col( $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta WHERE user_id = %d AND meta_key LIKE %s", $user_id, 'ennu_%' ) );
		foreach ( $meta_keys_to_delete as $meta_key ) {
			delete_user_meta( $user_id, $meta_key );
		}

		wp_send_json_success( array( 'message' => 'All assessment data cleared.' ) );
	}

	public function handle_clear_single_assessment_data() {
		check_ajax_referer( 'ennu_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'edit_users' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions.' ), 403 );
		}

		$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
		$assessment_key = isset( $_POST['assessment_key'] ) ? sanitize_key( $_POST['assessment_key'] ) : '';

		if ( ! $user_id || empty( $assessment_key ) ) {
			wp_send_json_error( array( 'message' => 'Invalid user ID or assessment key.' ), 400 );
		}

		global $wpdb;
		$meta_key_pattern = 'ennu_' . $assessment_key . '_%';
		$meta_keys_to_delete = $wpdb->get_col( $wpdb->prepare( "SELECT meta_key FROM $wpdb->usermeta WHERE user_id = %d AND meta_key LIKE %s", $user_id, $meta_key_pattern ) );

		foreach ( $meta_keys_to_delete as $meta_key ) {
			delete_user_meta( $user_id, $meta_key );
		}

		// After clearing, we must recalculate the master ENNU LIFE SCORE
		ENNU_Assessment_Scoring::calculate_ennu_life_score( $user_id, true );

		wp_send_json_success( array( 'message' => 'Assessment data cleared.' ) );
	}


	public function save_user_assessment_fields( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) || ! isset( $_POST['ennu_assessment_nonce'] ) || ! wp_verify_nonce( $_POST['ennu_assessment_nonce'], 'ennu_user_profile_update_' . $user_id ) ) {
			return;
		}

		// --- Save Global Fields ---
		$global_keys = array( 
			'ennu_global_gender',
			'ennu_global_user_dob_combined', 
			'ennu_global_health_goals', 
			'ennu_global_height_weight' 
		);
		
		foreach ( $global_keys as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value_to_save = is_array( $_POST[ $key ] ) ? array_map( 'sanitize_text_field', $_POST[ $key ] ) : sanitize_text_field( $_POST[ $key ] );
				update_user_meta( $user_id, $key, $value_to_save );
				error_log('ENNU Enhanced Admin: Saved global field ' . $key . ' = ' . print_r($value_to_save, true));
			} elseif ( $key === 'ennu_global_health_goals' ) { // If no checkboxes are checked, save an empty array.
				update_user_meta( $user_id, $key, array() );
				error_log('ENNU Enhanced Admin: Saved empty health goals array');
			}
		}


		$assessments = array_keys( ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions() );
		foreach ( $assessments as $assessment_type ) {
			$all_questions = $this->get_direct_assessment_questions( $assessment_type );
			
			// This is the definitive fix, aligning the save logic with the display logic.
			$questions = isset( $all_questions['questions'] ) ? $all_questions['questions'] : $all_questions;

			foreach ( $questions as $question_id => $q_data ) {
				if ( ! is_array( $q_data ) || isset( $q_data['global_key'] ) ) {
					continue;
				}
				$meta_key = 'ennu_' . $assessment_type . '_' . $question_id;
				if ( isset( $_POST[ $meta_key ] ) ) {
					$value_to_save = is_array( $_POST[ $meta_key ] ) ? array_map( 'sanitize_text_field', $_POST[ $meta_key ] ) : sanitize_text_field( $_POST[ $meta_key ] );
					update_user_meta( $user_id, $meta_key, $value_to_save );
				} elseif ( isset($q_data['type']) && $q_data['type'] === 'multiselect' ) {
					update_user_meta( $user_id, $meta_key, array() );
				}
			}
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
		$stats['monthly_assessments'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key LIKE 'ennu_%_calculated_score' AND meta_value != '' AND CAST(meta_value AS SIGNED) > 0 AND user_id IN (SELECT ID FROM {$wpdb->users} WHERE user_registered >= DATE_SUB(NOW(), INTERVAL 1 MONTH))" );
		return $stats;
	}

	private function display_recent_assessments_table() {
		global $wpdb;
		$results = $wpdb->get_results( "SELECT u.ID, u.user_login, u.user_email, um.meta_value as score, REPLACE(REPLACE(um.meta_key, 'ennu_', ''), '_calculated_score', '') as assessment_type FROM {$wpdb->usermeta} um JOIN {$wpdb->users} u ON um.user_id = u.ID WHERE um.meta_key LIKE 'ennu_%_calculated_score' AND um.meta_value != '' ORDER BY um.umeta_id DESC LIMIT 10" );
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
				$height_ft = $current_value['ft'] ?? '';
				$height_in = $current_value['in'] ?? '';
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
							$value_key = ($key === 'ennu_bmi_history') ? 'bmi' : 'score';
							if ( isset( $entry['date'], $entry[$value_key] ) ) {
								echo '<li><strong>' . esc_html( number_format( (float) $entry[$value_key], 1 ) ) . '</strong> on ' . esc_html( date( 'M j, Y @ g:i a', strtotime( $entry['date'] ) ) ) . '</li>';
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
					$options[$goal_id] = $goal_data['label'];
				}
				return $options;
			}
		}
		
		// Fallback to welcome assessment options if config not available
		$definitions = ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions();
		$welcome_options = $definitions['welcome_assessment']['questions']['welcome_q3']['options'] ?? array();
		
		if ( !empty( $welcome_options ) ) {
			return $welcome_options;
		}
		
		// Final fallback - default health goals
		return array(
			'longevity' => 'Longevity & Healthy Aging',
			'energy' => 'Improve Energy & Vitality',
			'strength' => 'Build Strength & Muscle',
			'libido' => 'Enhance Libido & Sexual Health',
			'weight_loss' => 'Achieve & Maintain Healthy Weight',
			'hormonal_balance' => 'Hormonal Balance',
			'cognitive_health' => 'Sharpen Cognitive Function',
			'heart_health' => 'Support Heart Health',
			'aesthetics' => 'Improve Hair, Skin & Nails',
			'sleep' => 'Improve Sleep Quality',
			'stress' => 'Reduce Stress & Improve Resilience',
		);
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
			'ennu_' . $assessment_type . '_pillar_scores'        => 'Pillar Scores',
		);

		foreach ( $system_fields as $key => $label ) {
			$value = get_user_meta( $user_id, $key, true );
			if ( empty( $value ) ) {
				continue;
			}
			if ( is_array( $value ) ) {
				$value_str = '<ul style="margin:0; padding:0; list-style:none;">';
				foreach ( $value as $k => $v ) {
					$value_str .= '<li><strong>' . esc_html( ucwords( str_replace( '_', ' ', $k ) ) ) . ':</strong> ' . esc_html( is_array($v) ? json_encode($v) : $v ) . '</li>';
				}
				$value_str .= '</ul>';
				$value = $value_str;
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
		$pages_to_create['dashboard'] = array( 
			'title' => 'Health Assessment Dashboard | Track Your Wellness Journey | ENNU Life', 
			'menu_label' => 'Dashboard',
			'content' => '[ennu-user-dashboard]',
			'parent' => 0
		);
		$pages_to_create['assessments'] = array( 
			'title' => 'Free Health Assessments | Comprehensive Wellness Evaluations | ENNU Life', 
			'menu_label' => 'Assessments',
			'content' => '[ennu-assessments]',
			'parent' => 0
		);

		// Core utility page - SEO Optimized
		$pages_to_create['assessment-results'] = array( 
			'title' => 'Health Assessment Results | Personalized Wellness Insights | ENNU Life', 
			'menu_label' => 'Results',
			'content' => '[ennu-assessment-results]',
			'parent' => 0
		);

		// Registration page (Welcome Assessment) - Root level for better UX
		$pages_to_create['registration'] = array( 
			'title' => 'Health Registration | Start Your Wellness Journey | ENNU Life', 
			'menu_label' => 'Registration',
			'content' => '[ennu-welcome]',
			'parent' => 0
		);

		// Signup page - Premium product selection
		$pages_to_create['signup'] = array( 
			'title' => 'Sign Up | Premium Health Services | ENNU Life', 
			'menu_label' => 'Sign Up',
			'content' => '[ennu-signup]',
			'parent' => 0
		);

		// Consultation & Call Pages - SEO Optimized
		$pages_to_create['call'] = array( 
			'title' => 'Schedule a Call | Free Health Consultation | ENNU Life', 
			'menu_label' => 'Schedule Call',
			'content' => 'Schedule your free health consultation with our experts.',
			'parent' => 0
		);
		$pages_to_create['ennu-life-score'] = array( 
			'title' => 'Get Your ENNU Life Score | Personalized Health Assessment | ENNU Life', 
			'menu_label' => 'ENNU Life Score',
			'content' => 'Discover your personalized ENNU Life Score and health insights.',
			'parent' => 0
		);

		// SEO-Optimized Assessment-Specific Titles with Short Menu Labels
		$seo_assessment_titles = array(
			'hair' => array(
				'main' => 'Hair Loss Assessment | Male Pattern Baldness Evaluation | ENNU Life',
				'menu_label' => 'Hair Loss',
				'results' => 'Hair Loss Assessment Results | Personalized Hair Health Analysis | ENNU Life', 
				'details' => 'Hair Loss Treatment Options | Detailed Hair Health Dossier | ENNU Life',
				'booking' => 'Hair Treatment Consultation | Hair Loss Solutions | ENNU Life'
			),
			'ed-treatment' => array(
				'main' => 'Erectile Dysfunction Assessment | ED Treatment Evaluation | ENNU Life',
				'menu_label' => 'ED Treatment',
				'results' => 'ED Assessment Results | Erectile Dysfunction Analysis | ENNU Life',
				'details' => 'ED Treatment Options | Erectile Dysfunction Solutions Dossier | ENNU Life',
				'booking' => 'ED Treatment Consultation | Erectile Dysfunction Solutions | ENNU Life'
			),
			'health-optimization' => array(
				'main' => 'Health Optimization Assessment | Comprehensive Wellness Check | ENNU Life',
				'menu_label' => 'Health Optimization',
				'results' => 'Health Optimization Results | Personalized Wellness Plan | ENNU Life',
				'details' => 'Health Optimization Solutions | Detailed Wellness Improvement Plan | ENNU Life',
				'booking' => 'Health Consultation | Comprehensive Wellness Evaluation | ENNU Life'
			),
			'health' => array(
				'main' => 'General Health Assessment | Complete Wellness Evaluation | ENNU Life',
				'menu_label' => 'General Health',
				'results' => 'Health Assessment Results | Comprehensive Wellness Analysis | ENNU Life',
				'details' => 'Health Improvement Plan | Detailed Wellness Solutions Dossier | ENNU Life',
				'booking' => 'Health Consultation | Comprehensive Wellness Evaluation | ENNU Life'
			),
			'hormone' => array(
				'main' => 'Hormone Assessment | Testosterone & Hormone Level Evaluation | ENNU Life',
				'menu_label' => 'Hormone Balance',
				'results' => 'Hormone Assessment Results | Hormone Balance Analysis | ENNU Life',
				'details' => 'Hormone Therapy Options | Detailed Hormone Balance Solutions | ENNU Life',
				'booking' => 'Hormone Consultation | Hormone Balance Specialists | ENNU Life'
			),
			'menopause' => array(
				'main' => 'Menopause Assessment | Hormone Replacement Therapy Evaluation | ENNU Life',
				'menu_label' => 'Menopause',
				'results' => 'Menopause Assessment Results | HRT Suitability Analysis | ENNU Life',
				'details' => 'Menopause Treatment Options | HRT Solutions Dossier | ENNU Life',
				'booking' => 'Menopause Consultation | HRT Specialists | ENNU Life'
			),
			'skin' => array(
				'main' => 'Skin Health Assessment | Anti-Aging Skincare Evaluation | ENNU Life',
				'menu_label' => 'Skin Health',
				'results' => 'Skin Assessment Results | Personalized Skincare Analysis | ENNU Life',
				'details' => 'Skin Treatment Options | Anti-Aging Skincare Solutions | ENNU Life',
				'booking' => 'Skin Treatment Consultation | Anti-Aging Skincare | ENNU Life'
			),
			'sleep' => array(
				'main' => 'Sleep Quality Assessment | Insomnia & Sleep Disorder Evaluation | ENNU Life',
				'menu_label' => 'Sleep Quality',
				'results' => 'Sleep Assessment Results | Sleep Quality Analysis | ENNU Life',
				'details' => 'Sleep Improvement Solutions | Detailed Sleep Optimization Plan | ENNU Life',
				'booking' => 'Sleep Consultation | Sleep Optimization Specialists | ENNU Life'
			),
			'testosterone' => array(
				'main' => 'Testosterone Assessment | Low T Evaluation & TRT Screening | ENNU Life',
				'menu_label' => 'Testosterone',
				'results' => 'Testosterone Assessment Results | Low T Analysis & TRT Evaluation | ENNU Life',
				'details' => 'Testosterone Replacement Therapy | TRT Options & Solutions | ENNU Life',
				'booking' => 'Testosterone Consultation | TRT Specialists | ENNU Life'
			),
			'weight-loss' => array(
				'main' => 'Weight Loss Assessment | Medical Weight Management Evaluation | ENNU Life',
				'menu_label' => 'Weight Loss',
				'results' => 'Weight Loss Assessment Results | Personalized Weight Management Plan | ENNU Life',
				'details' => 'Weight Loss Solutions | Medical Weight Management Options | ENNU Life',
				'booking' => 'Weight Loss Consultation | Medical Weight Management | ENNU Life'
			)
		);

		// Assessment Form Pages (children of /assessments/)
		foreach ($assessment_keys as $key) {
			// Assessment keys are already in the correct format (e.g., 'hair', 'ed-treatment', 'weight-loss')
			$slug = $key;
			
			// Skip welcome assessment - it's now at root level
			if ( 'welcome' === $key ) {
				continue;
			}
			
			// Use SEO-optimized title if available, otherwise fallback to definition title with SEO enhancement
			if (isset($seo_assessment_titles[$slug]['main'])) {
				$title = $seo_assessment_titles[$slug]['main'];
			} else {
				$base_title = $all_definitions[$key]['title'] ?? ucwords(str_replace('-', ' ', $key));
				$title = $base_title . ' | Professional Health Evaluation | ENNU Life';
			}

			// Form Page (child of assessments)
			$pages_to_create["assessments/{$slug}"] = array(
				'title' => $title, 
				'menu_label' => $seo_assessment_titles[$slug]['menu_label'] ?? ucwords(str_replace('-', ' ', $key)),
				'content' => "[ennu-{$slug}]",
				'parent' => "assessments"
			);
			
				// Results Page (child of specific assessment) - SEO Optimized
				$results_slug = $slug . '-results';
				$results_title = isset($seo_assessment_titles[$slug]['results']) 
					? $seo_assessment_titles[$slug]['results']
					: ucwords(str_replace('-', ' ', $key)) . ' Results | Personalized Health Analysis | ENNU Life';
				
				$pages_to_create["assessments/{$slug}/results"] = array(
					'title' => $results_title, 
				'menu_label' => 'Results',
					'content' => "[ennu-{$results_slug}]",
					'parent' => "assessments/{$slug}"
				);

				// Details Page (child of specific assessment) - SEO Optimized  
				$details_slug = $slug . '-assessment-details';
				$details_title = isset($seo_assessment_titles[$slug]['details'])
					? $seo_assessment_titles[$slug]['details'] 
					: ucwords(str_replace('-', ' ', $key)) . ' Treatment Options | Detailed Health Solutions | ENNU Life';
				
				$pages_to_create["assessments/{$slug}/details"] = array(
					'title' => $details_title, 
				'menu_label' => 'Treatment Options',
					'content' => "[ennu-{$details_slug}]",
					'parent' => "assessments/{$slug}"
				);

			// Booking Page (child of specific assessment) - SEO Optimized
			$booking_slug = $slug . '-consultation';
			$booking_title = isset($seo_assessment_titles[$slug]['booking'])
				? $seo_assessment_titles[$slug]['booking']
				: ucwords(str_replace('-', ' ', $key)) . ' Consultation | Professional Health Consultation | ENNU Life';
			
			$pages_to_create["assessments/{$slug}/consultation"] = array(
				'title' => $booking_title, 
				'menu_label' => 'Book Consultation',
				'content' => "[ennu-{$booking_slug}]",
				'parent' => "assessments/{$slug}"
			);
		}

		$page_mappings = get_option( 'ennu_created_pages', array() );
		$created_parents = array(); // Track parent page IDs

		// Sort pages to create parents first
		$sorted_pages = array();
		foreach ( $pages_to_create as $slug => $page_data ) {
			if ( $page_data['parent'] === 0 ) {
				$sorted_pages[$slug] = $page_data; // Parent pages first
			}
		}
		foreach ( $pages_to_create as $slug => $page_data ) {
			if ( $page_data['parent'] !== 0 ) {
				$sorted_pages[$slug] = $page_data; // Child pages after
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
		
		// Auto-update the primary menu with the new structure
		$this->update_primary_menu_structure( $page_mappings );
		
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

	/**
	 * Update the primary menu by adding missing plugin pages, preserving all existing items.
	 * This is a non-destructive operation that only adds missing items.
	 * Enhanced with admin feedback, error handling, and logging.
	 */
	private function update_primary_menu_structure( $page_mappings ) {
		$added_items = array();
		$skipped_items = array();
		$errors = array();

		// Get or create primary menu
		$menu_locations = get_nav_menu_locations();
		$primary_menu_id = $menu_locations['primary'] ?? null;
		if ( ! $primary_menu_id ) {
			$menu_name = 'Primary Menu';
			$menu_exists = wp_get_nav_menu_object( $menu_name );
			if ( ! $menu_exists ) {
				$primary_menu_id = wp_create_nav_menu( $menu_name );
				if ( is_wp_error( $primary_menu_id ) ) {
					$errors[] = sprintf( __( 'Failed to create primary menu: %s', 'ennulifeassessments' ), $primary_menu_id->get_error_message() );
					return;
				}
				$locations = get_theme_mod( 'nav_menu_locations' );
				$locations['primary'] = $primary_menu_id;
				set_theme_mod( 'nav_menu_locations', $locations );
			} else {
				$primary_menu_id = $menu_exists->term_id;
			}
		}
		if ( ! $primary_menu_id ) {
			$errors[] = __( 'Could not identify or create primary menu', 'ennulifeassessments' );
			return;
		}

		// Get all existing menu items and their page IDs
		$existing_items = wp_get_nav_menu_items( $primary_menu_id );
		$existing_page_ids = array();
		$existing_menu_items = array();
		if ( $existing_items ) {
			foreach ( $existing_items as $item ) {
				if ( $item->object == 'page' ) {
					$existing_page_ids[] = (int) $item->object_id;
					$existing_menu_items[ $item->object_id ] = $item;
				}
			}
		}

		// Define optimal menu structure (slugs, menu labels, order, parent)
		$menu_structure = array(
			'root' => array(
				array('slug' => 'registration', 'menu_label' => 'Registration', 'order' => 1, 'parent' => 0),
				array('slug' => 'signup', 'menu_label' => 'Sign Up', 'order' => 2, 'parent' => 0),
				array('slug' => 'assessments', 'menu_label' => 'Assessments', 'order' => 3, 'parent' => 0),
				array('slug' => 'dashboard', 'menu_label' => 'Dashboard', 'order' => 4, 'parent' => 0),
				array('slug' => 'call', 'menu_label' => 'Schedule Call', 'order' => 5, 'parent' => 0),
				array('slug' => 'ennu-life-score', 'menu_label' => 'ENNU Life Score', 'order' => 6, 'parent' => 0),
			),
			'assessments' => array(
				array('slug' => 'assessments/hair', 'menu_label' => 'Hair Loss', 'order' => 1, 'parent' => 'assessments'),
				array('slug' => 'assessments/ed-treatment', 'menu_label' => 'ED Treatment', 'order' => 2, 'parent' => 'assessments'),
				array('slug' => 'assessments/weight-loss', 'menu_label' => 'Weight Loss', 'order' => 3, 'parent' => 'assessments'),
				array('slug' => 'assessments/health', 'menu_label' => 'General Health', 'order' => 4, 'parent' => 'assessments'),
				array('slug' => 'assessments/health-optimization', 'menu_label' => 'Health Optimization', 'order' => 5, 'parent' => 'assessments'),
				array('slug' => 'assessments/skin', 'menu_label' => 'Skin Health', 'order' => 6, 'parent' => 'assessments'),
				array('slug' => 'assessments/hormone', 'menu_label' => 'Hormone Balance', 'order' => 7, 'parent' => 'assessments'),
				array('slug' => 'assessments/testosterone', 'menu_label' => 'Testosterone', 'order' => 8, 'parent' => 'assessments'),
				array('slug' => 'assessments/menopause', 'menu_label' => 'Menopause', 'order' => 9, 'parent' => 'assessments'),
				array('slug' => 'assessments/sleep', 'menu_label' => 'Sleep Quality', 'order' => 10, 'parent' => 'assessments'),
			),
		);

		// Track created menu items for parent relationships
		$created_items = array();

		// Add root-level items if missing
		foreach ( $menu_structure['root'] as $item ) {
			$page_id = $page_mappings[ $item['slug'] ] ?? null;
			if ( ! $page_id || ! get_post( $page_id ) ) {
				$skipped_items[] = sprintf( __( 'Page not found for slug: %s', 'ennulifeassessments' ), $item['slug'] );
				continue;
			}
			if ( in_array( (int) $page_id, $existing_page_ids ) ) {
				$created_items[ $item['slug'] ] = $existing_menu_items[ $page_id ]->ID;
				$skipped_items[] = sprintf( __( 'Already in menu: %s', 'ennulifeassessments' ), isset($item['title']) ? $item['title'] : $item['slug'] );
				continue;
			}
			$menu_label = get_post_meta( $page_id, '_ennu_menu_label', true );
			if ( ! $menu_label ) $menu_label = $item['menu_label'] ?? ucwords(str_replace(['-', '_'], ' ', basename($item['slug'])));
			$menu_item_id = wp_update_nav_menu_item( $primary_menu_id, 0, array(
				'menu-item-title' => $menu_label,
				'menu-item-object' => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-status' => 'publish',
				'menu-item-type' => 'post_type',
				'menu-item-parent-id' => 0,
				'menu-item-position' => $item['order'],
			) );
			if ( $menu_item_id && ! is_wp_error( $menu_item_id ) ) {
				$created_items[ $item['slug'] ] = $menu_item_id;
				$added_items[] = sprintf( __( 'Added: %s', 'ennulifeassessments' ), $menu_label );
			} else {
				$error_msg = is_wp_error( $menu_item_id ) ? $menu_item_id->get_error_message() : __( 'Unknown error', 'ennulifeassessments' );
				$errors[] = sprintf( __( 'Failed to add menu item for %s: %s', 'ennulifeassessments' ), isset($item['title']) ? $item['title'] : $item['slug'], $error_msg );
			}
		}

		// Add assessment submenu items if missing
		foreach ( $menu_structure['assessments'] as $item ) {
			$page_id = $page_mappings[ $item['slug'] ] ?? null;
			if ( ! $page_id || ! get_post( $page_id ) ) {
				$skipped_items[] = sprintf( __( 'Page not found for slug: %s', 'ennulifeassessments' ), $item['slug'] );
				continue;
			}
			if ( in_array( (int) $page_id, $existing_page_ids ) ) {
				$created_items[ $item['slug'] ] = $existing_menu_items[ $page_id ]->ID;
				$skipped_items[] = sprintf( __( 'Already in menu: %s', 'ennulifeassessments' ), isset($item['title']) ? $item['title'] : $item['slug'] );
				continue;
			}
			$parent_id = $created_items[ $item['parent'] ] ?? 0;
			if ( ! $parent_id ) {
				$skipped_items[] = sprintf( __( 'Parent menu item not found for: %s', 'ennulifeassessments' ), isset($item['title']) ? $item['title'] : $item['slug'] );
				continue;
			}
			$menu_label = get_post_meta( $page_id, '_ennu_menu_label', true );
			if ( ! $menu_label ) $menu_label = $item['menu_label'] ?? ucwords(str_replace(['-', '_'], ' ', basename($item['slug'])));
			$menu_item_id = wp_update_nav_menu_item( $primary_menu_id, 0, array(
				'menu-item-title' => $menu_label,
				'menu-item-object' => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-status' => 'publish',
				'menu-item-type' => 'post_type',
				'menu-item-parent-id' => $parent_id,
				'menu-item-position' => $item['order'],
			) );
			if ( $menu_item_id && ! is_wp_error( $menu_item_id ) ) {
				$created_items[ $item['slug'] ] = $menu_item_id;
				$added_items[] = sprintf( __( 'Added submenu: %s', 'ennulifeassessments' ), $menu_label );
			} else {
				$error_msg = is_wp_error( $menu_item_id ) ? $menu_item_id->get_error_message() : __( 'Unknown error', 'ennulifeassessments' );
				$errors[] = sprintf( __( 'Failed to add submenu item for %s: %s', 'ennulifeassessments' ), isset($item['title']) ? $item['title'] : $item['slug'], $error_msg );
			}
		}

		// Add nested sub-pages for each assessment (results, details, consultation)
		foreach ( $page_mappings as $slug => $page_id ) {
			if ( strpos( $slug, 'assessments/' ) === 0 && strpos( $slug, '/results' ) !== false ) {
				// Results pages
				if ( ! $page_id || ! get_post( $page_id ) ) {
					$skipped_items[] = sprintf( __( 'Results page not found for slug: %s', 'ennulifeassessments' ), $slug );
					continue;
				}
				if ( in_array( (int) $page_id, $existing_page_ids ) ) {
					$skipped_items[] = sprintf( __( 'Results already in menu: %s', 'ennulifeassessments' ), $slug );
					continue;
				}
				$parent_slug = str_replace( '/results', '', $slug );
				$parent_id = $created_items[ $parent_slug ] ?? 0;
				if ( ! $parent_id ) {
					$skipped_items[] = sprintf( __( 'Parent not found for results: %s', 'ennulifeassessments' ), $slug );
					continue;
				}
				$menu_label = get_post_meta( $page_id, '_ennu_menu_label', true );
				if ( ! $menu_label ) $menu_label = __( 'Results', 'ennulifeassessments' );
				$menu_item_id = wp_update_nav_menu_item( $primary_menu_id, 0, array(
					'menu-item-title' => $menu_label,
					'menu-item-object' => 'page',
					'menu-item-object-id' => $page_id,
					'menu-item-status' => 'publish',
					'menu-item-type' => 'post_type',
					'menu-item-parent-id' => $parent_id,
					'menu-item-position' => 1,
				) );
				if ( $menu_item_id && ! is_wp_error( $menu_item_id ) ) {
					$added_items[] = sprintf( __( 'Added results: %s', 'ennulifeassessments' ), $menu_label );
				} else {
					$error_msg = is_wp_error( $menu_item_id ) ? $menu_item_id->get_error_message() : __( 'Unknown error', 'ennulifeassessments' );
					$errors[] = sprintf( __( 'Failed to add results for %s: %s', 'ennulifeassessments' ), $slug, $error_msg );
				}
			} elseif ( strpos( $slug, 'assessments/' ) === 0 && strpos( $slug, '/details' ) !== false ) {
				// Details pages
				if ( ! $page_id || ! get_post( $page_id ) ) {
					$skipped_items[] = sprintf( __( 'Details page not found for slug: %s', 'ennulifeassessments' ), $slug );
					continue;
				}
				if ( in_array( (int) $page_id, $existing_page_ids ) ) {
					$skipped_items[] = sprintf( __( 'Details already in menu: %s', 'ennulifeassessments' ), $slug );
					continue;
				}
				$parent_slug = str_replace( '/details', '', $slug );
				$parent_id = $created_items[ $parent_slug ] ?? 0;
				if ( ! $parent_id ) {
					$skipped_items[] = sprintf( __( 'Parent not found for details: %s', 'ennulifeassessments' ), $slug );
					continue;
				}
				$menu_label = get_post_meta( $page_id, '_ennu_menu_label', true );
				if ( ! $menu_label ) $menu_label = __( 'Treatment Options', 'ennulifeassessments' );
				$menu_item_id = wp_update_nav_menu_item( $primary_menu_id, 0, array(
					'menu-item-title' => $menu_label,
					'menu-item-object' => 'page',
					'menu-item-object-id' => $page_id,
					'menu-item-status' => 'publish',
					'menu-item-type' => 'post_type',
					'menu-item-parent-id' => $parent_id,
					'menu-item-position' => 2,
				) );
				if ( $menu_item_id && ! is_wp_error( $menu_item_id ) ) {
					$added_items[] = sprintf( __( 'Added details: %s', 'ennulifeassessments' ), $menu_label );
				} else {
					$error_msg = is_wp_error( $menu_item_id ) ? $menu_item_id->get_error_message() : __( 'Unknown error', 'ennulifeassessments' );
					$errors[] = sprintf( __( 'Failed to add details for %s: %s', 'ennulifeassessments' ), $slug, $error_msg );
				}
			} elseif ( strpos( $slug, 'assessments/' ) === 0 && strpos( $slug, '/consultation' ) !== false ) {
				// Consultation pages
				if ( ! $page_id || ! get_post( $page_id ) ) {
					$skipped_items[] = sprintf( __( 'Consultation page not found for slug: %s', 'ennulifeassessments' ), $slug );
					continue;
				}
				if ( in_array( (int) $page_id, $existing_page_ids ) ) {
					$skipped_items[] = sprintf( __( 'Consultation already in menu: %s', 'ennulifeassessments' ), $slug );
					continue;
				}
				$parent_slug = str_replace( '/consultation', '', $slug );
				$parent_id = $created_items[ $parent_slug ] ?? 0;
				if ( ! $parent_id ) {
					$skipped_items[] = sprintf( __( 'Parent not found for consultation: %s', 'ennulifeassessments' ), $slug );
					continue;
				}
				$menu_label = get_post_meta( $page_id, '_ennu_menu_label', true );
				if ( ! $menu_label ) $menu_label = __( 'Book Consultation', 'ennulifeassessments' );
				$menu_item_id = wp_update_nav_menu_item( $primary_menu_id, 0, array(
					'menu-item-title' => $menu_label,
					'menu-item-object' => 'page',
					'menu-item-object-id' => $page_id,
					'menu-item-status' => 'publish',
					'menu-item-type' => 'post_type',
					'menu-item-parent-id' => $parent_id,
					'menu-item-position' => 3,
				) );
				if ( $menu_item_id && ! is_wp_error( $menu_item_id ) ) {
					$added_items[] = sprintf( __( 'Added consultation: %s', 'ennulifeassessments' ), $menu_label );
				} else {
					$error_msg = is_wp_error( $menu_item_id ) ? $menu_item_id->get_error_message() : __( 'Unknown error', 'ennulifeassessments' );
					$errors[] = sprintf( __( 'Failed to add consultation for %s: %s', 'ennulifeassessments' ), $slug, $error_msg );
				}
			}
		}

		// Store menu update results for admin feedback
		$menu_update_results = array(
			'added_items' => $added_items,
			'skipped_items' => $skipped_items,
			'errors' => $errors,
			'timestamp' => current_time( 'mysql' ),
		);
		update_option( 'ennu_menu_update_results', $menu_update_results );

		// Log the update for debugging
		if ( ! empty( $added_items ) || ! empty( $errors ) ) {
			error_log( sprintf(
				'ENNU Life Menu Update - Added: %d, Skipped: %d, Errors: %d',
				count( $added_items ),
				count( $skipped_items ),
				count( $errors )
			) );
		}
	}

	/**
	 * Display page status overview
	 */
	private function display_page_status_overview( $page_mappings ) {
		if ( empty( $page_mappings ) ) {
			echo '<div class="notice notice-info"><p>' . esc_html__( 'No pages have been created yet. Use the "Create Missing Assessment Pages" button below to get started.', 'ennulifeassessments' ) . '</p></div>';
			return;
		}

		echo '<h2>' . esc_html__( 'Page Status Overview', 'ennulifeassessments' ) . '</h2>';
		echo '<div class="ennu-page-status-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(30px, 1fr)); gap: 20px; margin: 20px;">';
		
		$total_pages = count( $page_mappings );
		$existing_pages = 0;
		$missing_pages = 0;
		$page_status = array();

		foreach ( $page_mappings as $slug => $page_id ) {
			$page = get_post( $page_id );
			if ( $page && $page->post_status === 'publish' ) {
				$existing_pages++;
				$status = 'exists';
				$status_class = 'success';
				$status_text = __( 'Published', 'ennulifeassessments' );
			} else {
				$missing_pages++;
				$status = 'missing';
				$status_class = 'error';
				$status_text = __( 'Missing', 'ennulifeassessments' );
			}
			
			$page_status[ $slug ] = array(
				'id' => $page_id,
				'status' => $status,
				'class' => $status_class,
				'text' => $status_text,
				'title' => $page ? $page->post_title : $slug,
				'url' => $page ? get_permalink( $page_id ) : '',
			);
		}

		// Display summary cards
		echo '<div class="ennu-status-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; text-align: center;">';
		echo '<h3 style="margin: 0 0 10px; color: #333;">' . esc_html__( 'Total Pages', 'ennulifeassessments' ) . '</h3>';
		echo '<div style="font-size: 2em; font-weight: bold; color: #0073aa;">' . esc_html( $total_pages ) . '</div>';
		echo '</div>';

		echo '<div class="ennu-status-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; text-align: center;">';
		echo '<h3 style="margin: 0 0 10px; color: #333;">' . esc_html__( 'Published', 'ennulifeassessments' ) . '</h3>';
		echo '<div style="font-size: 2em; font-weight: bold; color: #46b450;">' . esc_html( $existing_pages ) . '</div>';
		echo '</div>';

		echo '<div class="ennu-status-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; text-align: center;">';
		echo '<h3 style="margin: 0 0 10px; color: #333;">' . esc_html__( 'Missing', 'ennulifeassessments' ) . '</h3>';
		echo '<div style="font-size: 2em; font-weight: bold; color: #dc3232;">' . esc_html( $missing_pages ) . '</div>';
		echo '</div>';

		echo '</div>';

		// Display detailed page list
		echo '<h3>' . esc_html__( 'Page Details', 'ennulifeassessments' ) . '</h3>';
		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr><th>' . esc_html__( 'Page', 'ennulifeassessments' ) . '</th><th>' . esc_html__( 'Status', 'ennulifeassessments' ) . '</th><th>' . esc_html__( 'Page ID', 'ennulifeassessments' ) . '</th><th>' . esc_html__( 'Actions', 'ennulifeassessments' ) . '</th></tr></thead><tbody>';
		
		foreach ( $page_status as $slug => $info ) {
			echo '<tr>';
			echo '<td><strong>' . esc_html( $info['title'] ) . '</strong><br><small style="color: #666;">/' . esc_html( $slug ) . '/</small></td>';
			echo '<td><span class="ennu-status-badge ennu-status-' . esc_attr( $info['class'] ) . '" style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; background: ' . ( $info['class'] === 'success' ? '#46b450' : '#dc3232' ) . '; color: white;">' . esc_html( $info['text'] ) . '</span></td>';
			echo '<td>' . esc_html( $info['id'] ) . '</td>';
			echo '<td>';
			if ( $info['status'] === 'exists' && $info['url'] ) {
				echo '<a href="' . esc_url( $info['url'] ) . '" target="_blank" class="button button-small">' . esc_html__( 'View', 'ennulifeassessments' ) . '</a> ';
				echo '<a href="' . esc_url( get_edit_post_link( $info['id'] ) ) . '" class="button button-small">' . esc_html__( 'Edit', 'ennulifeassessments' ) . '</a>';
			} else {
				echo '<span style="color: #666;">' . esc_html__( 'Page not found', 'ennulifeassessments' ) . '</span>';
			}
			echo '</td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Display menu update results
	 */
	private function display_menu_update_results() {
		$menu_results = get_option( 'ennu_menu_update_results', array() );
		if ( empty( $menu_results ) ) {
			return;
		}

		echo '<h2>' . esc_html__( 'Menu Update Results', 'ennulifeassessments' ) . '</h2>';
		echo '<div class="ennu-menu-results" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0;">';
		
		if ( ! empty( $menu_results['added_items'] ) ) {
			echo '<div class="ennu-result-section" style="margin-bottom: 15px;">';
			echo '<h4 style="color: #46b450; margin: 0 0 10px;">' . esc_html__( '✅ Added to Menu:', 'ennulifeassessments' ) . '</h4>';
			echo '<ul style="margin: 0; padding-left: 20px;">';
			foreach ( $menu_results['added_items'] as $item ) {
				echo '<li>' . esc_html( $item ) . '</li>';
			}
			echo '</ul>';
			echo '</div>';
		}

		if ( ! empty( $menu_results['skipped_items'] ) ) {
			echo '<div class="ennu-result-section" style="margin-bottom: 15px;">';
			echo '<h4 style="color: #ffb90; margin: 0 0 10px;">' . esc_html__( '⚠️ Skipped (Already in Menu):', 'ennulifeassessments' ) . '</h4>';
			echo '<ul style="margin: 0; padding-left: 20px;">';
			foreach ( $menu_results['skipped_items'] as $item ) {
				echo '<li>' . esc_html( $item ) . '</li>';
			}
			echo '</ul>';
			echo '</div>';
		}

		if ( ! empty( $menu_results['errors'] ) ) {
			echo '<div class="ennu-result-section" style="margin-bottom: 15px;">';
			echo '<h4 style="color: #dc3232; margin: 0 0 10px;">' . esc_html__( '❌ Errors:', 'ennulifeassessments' ) . '</h4>';
			echo '<ul style="margin: 0; padding-left: 20px;">';
			foreach ( $menu_results['errors'] as $error ) {
				echo '<li>' . esc_html( $error ) . '</li>';
			}
			echo '</ul>';
			echo '</div>';
		}

		if ( isset( $menu_results['timestamp'] ) ) {
			echo '<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 12px; color: #666;">';
			echo '<strong>' . esc_html__( 'Last Updated:', 'ennulifeassessments' ) . '</strong> ' . esc_html( $menu_results['timestamp'] );
			echo '</div>';
		}

		echo '</div>';
	}
}
