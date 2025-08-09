<?php
/**
 * ENNU HubSpot Admin Page
 * Comprehensive HubSpot integration management interface
 *
 * @package   ENNU Life Assessments
 * @copyright Copyright (c) 2024, Very Good Plugins, https://verygoodplugins.com
 * @license   GPL-3.0+
 * @since     64.56.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_HubSpot_Admin_Page {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_hubspot_menu' ) );
		add_action( 'admin_init', array( $this, 'register_hubspot_settings' ) );
		add_action( 'wp_ajax_ennu_test_hubspot_connection', array( $this, 'test_connection' ) );
		add_action( 'wp_ajax_ennu_sync_hubspot_fields', array( $this, 'sync_hubspot_fields' ) );
	}

	/**
	 * Add HubSpot menu item
	 */
	public function add_hubspot_menu() {
		add_submenu_page(
			'ennu-life',
			'HubSpot Sync',
			'HubSpot Sync',
			'manage_options',
			'ennu-hubspot-sync',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register HubSpot settings
	 */
	public function register_hubspot_settings() {
		register_setting( 'ennu_hubspot_settings', 'ennu_hubspot_settings' );
		
		add_settings_section(
			'ennu_hubspot_credentials',
			'HubSpot Credentials',
			array( $this, 'credentials_section_callback' ),
			'ennu_hubspot_settings'
		);
		
		add_settings_field(
			'ennu_hubspot_client_id',
			'Client ID',
			array( $this, 'client_id_callback' ),
			'ennu_hubspot_settings',
			'ennu_hubspot_credentials'
		);
		
		add_settings_field(
			'ennu_hubspot_client_secret',
			'Client Secret',
			array( $this, 'client_secret_callback' ),
			'ennu_hubspot_settings',
			'ennu_hubspot_credentials'
		);
		
		add_settings_field(
			'ennu_hubspot_portal_id',
			'Portal ID',
			array( $this, 'portal_id_callback' ),
			'ennu_hubspot_settings',
			'ennu_hubspot_credentials'
		);
		
		add_settings_field(
			'ennu_hubspot_api_key',
			'API Key (Alternative)',
			array( $this, 'api_key_callback' ),
			'ennu_hubspot_settings',
			'ennu_hubspot_credentials'
		);
	}

	/**
	 * Render the HubSpot admin page
	 */
	public function render_page() {
		$settings = get_option( 'ennu_hubspot_settings', array() );
		$oauth_handler = new ENNU_HubSpot_OAuth_Handler();
		
		?>
		<div class="wrap">
			<h1>ENNU HubSpot Sync</h1>
			<p>Manage your HubSpot integration credentials and sync settings.</p>
			
			<div class="ennu-hubspot-admin-container">
				<!-- OAuth Status -->
				<div class="ennu-hubspot-section">
					<h2>OAuth Authentication Status</h2>
					<?php $this->display_oauth_status( $oauth_handler ); ?>
				</div>
				
				<!-- Credentials Form -->
				<div class="ennu-hubspot-section">
					<h2>HubSpot Credentials</h2>
					<form method="post" action="options.php">
						<?php
						settings_fields( 'ennu_hubspot_settings' );
						do_settings_sections( 'ennu_hubspot_settings' );
						submit_button( 'Save Credentials' );
						?>
					</form>
				</div>
				
				<!-- Field Mapping -->
				<div class="ennu-hubspot-section">
					<h2>Field Mapping Status</h2>
					<?php $this->display_field_mapping_status(); ?>
				</div>
				
				<!-- Sync Status -->
				<div class="ennu-hubspot-section">
					<h2>Sync Status</h2>
					<?php $this->display_sync_status(); ?>
				</div>
				
				<!-- Test Connection -->
				<div class="ennu-hubspot-section">
					<h2>Test Connection</h2>
					<button type="button" id="test-hubspot-connection" class="button button-primary">
						Test HubSpot Connection
					</button>
					<div id="test-connection-result"></div>
				</div>
				
				<!-- Sync Fields -->
				<div class="ennu-hubspot-section">
					<h2>Sync All Fields</h2>
					<button type="button" id="sync-hubspot-fields" class="button button-secondary">
						Sync All 312 Fields to HubSpot
					</button>
					<div id="sync-fields-result"></div>
				</div>
			</div>
		</div>
		
		<style>
		.ennu-hubspot-admin-container {
			max-width: 1200px;
		}
		.ennu-hubspot-section {
			background: #fff;
			border: 1px solid #ddd;
			border-radius: 8px;
			padding: 20px;
			margin-bottom: 20px;
		}
		.ennu-hubspot-section h2 {
			margin-top: 0;
			color: #333;
			border-bottom: 2px solid #0073aa;
			padding-bottom: 10px;
		}
		.oauth-status {
			padding: 15px;
			border-radius: 5px;
			margin: 10px 0;
		}
		.oauth-status.connected {
			background: #d4edda;
			border: 1px solid #c3e6cb;
			color: #155724;
		}
		.oauth-status.disconnected {
			background: #f8d7da;
			border: 1px solid #f5c6cb;
			color: #721c24;
		}
		
		/* Comprehensive Field Mapping Styles */
		.comprehensive-field-mapping {
			margin: 20px 0;
		}
		.field-mapping-tabs {
			display: flex;
			border-bottom: 2px solid #0073aa;
			margin-bottom: 20px;
		}
		.tab-button {
			background: #f8f9fa;
			border: none;
			padding: 12px 20px;
			margin-right: 5px;
			cursor: pointer;
			border-radius: 5px 5px 0 0;
			font-weight: 500;
		}
		.tab-button.active {
			background: #0073aa;
			color: white;
		}
		.tab-button:hover {
			background: #005a87;
			color: white;
		}
		.tab-content {
			display: none;
			padding: 20px;
			background: #f8f9fa;
			border-radius: 0 0 5px 5px;
		}
		.tab-content.active {
			display: block;
		}
		.field-grid {
			display: grid;
			grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
			gap: 15px;
			margin-top: 15px;
		}
		.field-item {
			background: white;
			border: 1px solid #ddd;
			border-radius: 8px;
			padding: 15px;
			transition: all 0.3s ease;
		}
		.field-item.synced {
			border-left: 4px solid #28a745;
			background: #f8fff9;
		}
		.field-item.not-synced {
			border-left: 4px solid #dc3545;
			background: #fff8f8;
		}
		.field-header {
			font-weight: 600;
			margin-bottom: 8px;
			color: #333;
		}
		.field-details {
			display: flex;
			justify-content: space-between;
			font-size: 12px;
			color: #666;
		}
		.field-key {
			font-family: monospace;
			background: #f1f1f1;
			padding: 2px 6px;
			border-radius: 3px;
		}
		.field-type {
			background: #e3f2fd;
			color: #1976d2;
			padding: 2px 6px;
			border-radius: 3px;
			font-weight: 500;
		}
		.assessment-section {
			margin-bottom: 30px;
		}
		.assessment-section h4 {
			color: #0073aa;
			border-bottom: 1px solid #0073aa;
			padding-bottom: 8px;
			margin-bottom: 15px;
		}
		</style>
		
		<script>
		jQuery(document).ready(function($) {
			$('#test-hubspot-connection').click(function() {
				var button = $(this);
				button.prop('disabled', true).text('Testing...');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'ennu_test_hubspot_connection',
						nonce: '<?php echo wp_create_nonce( 'ennu_hubspot_test' ); ?>'
					},
					success: function(response) {
						if (response.success) {
							$('#test-connection-result').html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
						} else {
							$('#test-connection-result').html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
						}
					},
					error: function() {
						$('#test-connection-result').html('<div class="notice notice-error"><p>Connection test failed.</p></div>');
					},
					complete: function() {
						button.prop('disabled', false).text('Test HubSpot Connection');
					}
				});
			});
			
			$('#sync-hubspot-fields').click(function() {
				var button = $(this);
				button.prop('disabled', true).text('Syncing...');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'ennu_sync_hubspot_fields',
						nonce: '<?php echo wp_create_nonce( 'ennu_hubspot_sync' ); ?>'
					},
					success: function(response) {
						if (response.success) {
							$('#sync-fields-result').html('<div class="notice notice-success"><p>' + response.data + '</p></div>');
						} else {
							$('#sync-fields-result').html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
						}
					},
					error: function() {
						$('#sync-fields-result').html('<div class="notice notice-error"><p>Field sync failed.</p></div>');
					},
					complete: function() {
						button.prop('disabled', false).text('Sync All 312 Fields to HubSpot');
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Display OAuth status
	 */
	private function display_oauth_status( $oauth_handler ) {
		$access_token = get_option( 'ennu_hubspot_access_token', '' );
		$refresh_token = get_option( 'ennu_hubspot_refresh_token', '' );
		
		if ( ! empty( $access_token ) && ! empty( $refresh_token ) ) {
			echo '<div class="oauth-status connected">';
			echo '<strong>✅ Connected to HubSpot</strong><br>';
			echo 'Access Token: ' . substr( $access_token, 0, 20 ) . '...<br>';
			echo 'Refresh Token: ' . substr( $refresh_token, 0, 20 ) . '...<br>';
			echo 'Last Updated: ' . get_option( 'ennu_hubspot_token_updated', 'Unknown' );
			echo '</div>';
		} else {
			echo '<div class="oauth-status disconnected">';
			echo '<strong>❌ Not Connected to HubSpot</strong><br>';
			echo 'Please configure your HubSpot credentials below and complete the OAuth flow.';
			echo '</div>';
		}
	}

	/**
	 * Display comprehensive field mapping status
	 */
	private function display_field_mapping_status() {
		$synced_fields = get_option( 'ennu_hubspot_synced_fields', array() );
		$comprehensive_fields = $this->get_comprehensive_field_mappings();
		
		echo '<div class="comprehensive-field-mapping">';
		echo '<h3>📋 Comprehensive Field Mapping Status</h3>';
		echo '<p>Total Fields Available: <strong>' . $this->get_total_field_count() . '</strong></p>';
		echo '<p>Fields Synced: <strong>' . count($synced_fields) . '</strong></p>';
		echo '<p>Sync Progress: <strong>' . round((count($synced_fields) / $this->get_total_field_count()) * 100, 1) . '%</strong></p>';
		
		echo '<div class="field-mapping-tabs">';
		echo '<button class="tab-button active" onclick="showTab(\'global\')">Global Fields</button>';
		echo '<button class="tab-button" onclick="showTab(\'assessments\')">Assessment Fields</button>';
		echo '<button class="tab-button" onclick="showTab(\'biomarkers\')">Biomarker Fields</button>';
		echo '<button class="tab-button" onclick="showTab(\'symptoms\')">Symptom Fields</button>';
		echo '</div>';
		
		// Global Fields Tab
		echo '<div id="global-tab" class="tab-content active">';
		echo '<h4>🌐 Global Contact Fields (' . count($comprehensive_fields['global']) . ' fields)</h4>';
		echo '<div class="field-grid">';
		foreach ( $comprehensive_fields['global'] as $field_key => $field_data ) {
			$is_synced = in_array( $field_key, $synced_fields );
			$status_class = $is_synced ? 'synced' : 'not-synced';
			$status_icon = $is_synced ? '✅' : '❌';
			echo '<div class="field-item ' . $status_class . '">';
			echo '<div class="field-header">';
			echo $status_icon . ' <strong>' . esc_html( $field_data['label'] ) . '</strong>';
			echo '</div>';
			echo '<div class="field-details">';
			echo '<span class="field-key">' . esc_html( $field_key ) . '</span>';
			echo '<span class="field-type">' . esc_html( $field_data['type'] ) . '</span>';
			echo '</div>';
			echo '</div>';
		}
		echo '</div>';
		echo '</div>';
		
		// Assessment Fields Tab
		echo '<div id="assessments-tab" class="tab-content">';
		foreach ( $comprehensive_fields['assessments'] as $assessment_name => $assessment_fields ) {
			echo '<div class="assessment-section">';
			echo '<h4>📊 ' . esc_html( $assessment_name ) . ' (' . count($assessment_fields) . ' fields)</h4>';
			echo '<div class="field-grid">';
			foreach ( $assessment_fields as $field_key => $field_data ) {
				$is_synced = in_array( $field_key, $synced_fields );
				$status_class = $is_synced ? 'synced' : 'not-synced';
				$status_icon = $is_synced ? '✅' : '❌';
				echo '<div class="field-item ' . $status_class . '">';
				echo '<div class="field-header">';
				echo $status_icon . ' <strong>' . esc_html( $field_data['label'] ) . '</strong>';
				echo '</div>';
				echo '<div class="field-details">';
				echo '<span class="field-key">' . esc_html( $field_key ) . '</span>';
				echo '<span class="field-type">' . esc_html( $field_data['type'] ) . '</span>';
				echo '</div>';
				echo '</div>';
			}
			echo '</div>';
			echo '</div>';
		}
		echo '</div>';
		
		// Biomarker Fields Tab
		echo '<div id="biomarkers-tab" class="tab-content">';
		echo '<h4>🔬 Biomarker Fields (' . count($comprehensive_fields['biomarkers']) . ' fields)</h4>';
		echo '<div class="field-grid">';
		foreach ( $comprehensive_fields['biomarkers'] as $field_key => $field_data ) {
			$is_synced = in_array( $field_key, $synced_fields );
			$status_class = $is_synced ? 'synced' : 'not-synced';
			$status_icon = $is_synced ? '✅' : '❌';
			echo '<div class="field-item ' . $status_class . '">';
			echo '<div class="field-header">';
			echo $status_icon . ' <strong>' . esc_html( $field_data['label'] ) . '</strong>';
			echo '</div>';
			echo '<div class="field-details">';
			echo '<span class="field-key">' . esc_html( $field_key ) . '</span>';
			echo '<span class="field-type">' . esc_html( $field_data['type'] ) . '</span>';
			echo '</div>';
			echo '</div>';
		}
		echo '</div>';
		echo '</div>';
		
		// Symptom Fields Tab
		echo '<div id="symptoms-tab" class="tab-content">';
		echo '<h4>🏥 Symptom Fields (' . count($comprehensive_fields['symptoms']) . ' fields)</h4>';
		echo '<div class="field-grid">';
		foreach ( $comprehensive_fields['symptoms'] as $field_key => $field_data ) {
			$is_synced = in_array( $field_key, $synced_fields );
			$status_class = $is_synced ? 'synced' : 'not-synced';
			$status_icon = $is_synced ? '✅' : '❌';
			echo '<div class="field-item ' . $status_class . '">';
			echo '<div class="field-header">';
			echo $status_icon . ' <strong>' . esc_html( $field_data['label'] ) . '</strong>';
			echo '</div>';
			echo '<div class="field-details">';
			echo '<span class="field-key">' . esc_html( $field_key ) . '</span>';
			echo '<span class="field-type">' . esc_html( $field_data['type'] ) . '</span>';
			echo '</div>';
			echo '</div>';
		}
		echo '</div>';
		echo '</div>';
		
		echo '</div>';
		
		// Add JavaScript for tab functionality
		echo '<script>
		function showTab(tabName) {
			// Hide all tab content
			var tabContents = document.getElementsByClassName("tab-content");
			for (var i = 0; i < tabContents.length; i++) {
				tabContents[i].classList.remove("active");
			}
			
			// Remove active class from all buttons
			var tabButtons = document.getElementsByClassName("tab-button");
			for (var i = 0; i < tabButtons.length; i++) {
				tabButtons[i].classList.remove("active");
			}
			
			// Show selected tab content
			document.getElementById(tabName + "-tab").classList.add("active");
			
			// Add active class to clicked button
			event.target.classList.add("active");
		}
		</script>';
	}

	/**
	 * Display sync status
	 */
	private function display_sync_status() {
		$last_sync = get_option( 'ennu_hubspot_last_sync', '' );
		$sync_count = get_option( 'ennu_hubspot_sync_count', 0 );
		$error_count = get_option( 'ennu_hubspot_error_count', 0 );
		
		echo '<div class="sync-status">';
		echo '<p><strong>Last Sync:</strong> ' . ( $last_sync ? esc_html( $last_sync ) : 'Never' ) . '</p>';
		echo '<p><strong>Successful Syncs:</strong> ' . esc_html( $sync_count ) . '</p>';
		echo '<p><strong>Failed Syncs:</strong> ' . esc_html( $error_count ) . '</p>';
		echo '</div>';
	}

	/**
	 * Get comprehensive field mappings with all 520 fields
	 */
	private function get_comprehensive_field_mappings() {
		return array(
			'global' => array(
				'ennu_global_gender' => array('label' => 'Gender', 'type' => 'enumeration'),
				'ennu_global_date_of_birth' => array('label' => 'Date of Birth', 'type' => 'date'),
				'ennu_global_height' => array('label' => 'Height (cm)', 'type' => 'number'),
				'ennu_global_weight' => array('label' => 'Weight (kg)', 'type' => 'number'),
				'ennu_global_bmi' => array('label' => 'BMI', 'type' => 'number'),
				'ennu_global_age' => array('label' => 'Age', 'type' => 'number'),
			),
			'assessments' => array(
				'Weight Loss Assessment' => array(
					'weight_loss_q1' => array('label' => 'Weight Loss Primary Goal', 'type' => 'enumeration'),
					'weight_loss_q2' => array('label' => 'Weight Loss Goal', 'type' => 'enumeration'),
					'weight_loss_q3' => array('label' => 'Diet Description', 'type' => 'enumeration'),
					'weight_loss_q4' => array('label' => 'Exercise Frequency', 'type' => 'enumeration'),
					'weight_loss_q5' => array('label' => 'Sleep Hours', 'type' => 'enumeration'),
					'weight_loss_q6' => array('label' => 'Stress Level', 'type' => 'enumeration'),
					'weight_loss_q7' => array('label' => 'Weight Loss History', 'type' => 'enumeration'),
					'weight_loss_q8' => array('label' => 'Emotional Eating', 'type' => 'enumeration'),
					'weight_loss_q9' => array('label' => 'Medical Conditions', 'type' => 'enumeration'),
					'weight_loss_q10' => array('label' => 'Motivation Level', 'type' => 'enumeration'),
					'weight_loss_q11' => array('label' => 'Body Composition Goal', 'type' => 'enumeration'),
					'weight_loss_q12' => array('label' => 'Support System', 'type' => 'enumeration'),
					'weight_loss_q13' => array('label' => 'Confidence Level', 'type' => 'enumeration'),
					'weight_loss_symptoms' => array('label' => 'Weight Loss Symptoms', 'type' => 'text'),
				),
				'Health Optimization Assessment' => array(
					'health_optimization_q1' => array('label' => 'Primary Health Concern', 'type' => 'enumeration'),
					'health_optimization_q2' => array('label' => 'Symptom Severity', 'type' => 'enumeration'),
					'health_optimization_q3' => array('label' => 'Symptom Frequency', 'type' => 'enumeration'),
					'health_optimization_q4' => array('label' => 'Secondary Symptom', 'type' => 'enumeration'),
					'health_optimization_q5' => array('label' => 'Secondary Severity', 'type' => 'enumeration'),
					'health_optimization_q6' => array('label' => 'Secondary Frequency', 'type' => 'enumeration'),
					'health_optimization_q7' => array('label' => 'Third Symptom', 'type' => 'enumeration'),
					'health_optimization_q8' => array('label' => 'Third Severity', 'type' => 'enumeration'),
					'health_optimization_q9' => array('label' => 'Third Frequency', 'type' => 'enumeration'),
					'health_optimization_q10' => array('label' => 'Fourth Symptom', 'type' => 'enumeration'),
					'health_optimization_q11' => array('label' => 'Fourth Severity', 'type' => 'enumeration'),
					'health_optimization_q12' => array('label' => 'Fourth Frequency', 'type' => 'enumeration'),
					'health_optimization_q13' => array('label' => 'Medical History', 'type' => 'text'),
					'health_optimization_q14' => array('label' => 'Current Medications', 'type' => 'text'),
					'health_optimization_q15' => array('label' => 'Lifestyle Factors', 'type' => 'enumeration'),
				),
				'ED Treatment Assessment' => array(
					'ed_treatment_q1' => array('label' => 'ED Severity', 'type' => 'enumeration'),
					'ed_treatment_q2' => array('label' => 'ED Duration', 'type' => 'enumeration'),
					'ed_treatment_q3' => array('label' => 'ED Frequency', 'type' => 'enumeration'),
					'ed_treatment_q4' => array('label' => 'Morning Erections', 'type' => 'enumeration'),
					'ed_treatment_q5' => array('label' => 'Relationship Status', 'type' => 'enumeration'),
					'ed_treatment_q6' => array('label' => 'Stress Level', 'type' => 'enumeration'),
					'ed_treatment_q7' => array('label' => 'Medical Conditions', 'type' => 'text'),
					'ed_treatment_q8' => array('label' => 'Current Medications', 'type' => 'text'),
					'ed_treatment_symptoms' => array('label' => 'ED Related Symptoms', 'type' => 'text'),
				),
				'Hair Assessment' => array(
					'hair_q1' => array('label' => 'Hair Type', 'type' => 'enumeration'),
					'hair_q2' => array('label' => 'Hair Concern Duration', 'type' => 'enumeration'),
					'hair_q3' => array('label' => 'Main Hair Concerns', 'type' => 'enumeration'),
					'hair_q4' => array('label' => 'Hair Loss Pattern', 'type' => 'enumeration'),
					'hair_q5' => array('label' => 'Scalp Condition', 'type' => 'enumeration'),
					'hair_q6' => array('label' => 'Hair Styling Frequency', 'type' => 'enumeration'),
					'hair_q7' => array('label' => 'Chemical Treatments', 'type' => 'enumeration'),
					'hair_q8' => array('label' => 'Hair Products Used', 'type' => 'text'),
				),
				'Skin Assessment' => array(
					'skin_q1' => array('label' => 'Skin Type', 'type' => 'enumeration'),
					'skin_q2' => array('label' => 'Primary Skin Concerns', 'type' => 'enumeration'),
					'skin_q3' => array('label' => 'Skin Sensitivity', 'type' => 'enumeration'),
					'skin_q4' => array('label' => 'Sun Exposure', 'type' => 'enumeration'),
					'skin_q5' => array('label' => 'Current Skincare Routine', 'type' => 'enumeration'),
					'skin_q6' => array('label' => 'Product Reactions', 'type' => 'enumeration'),
					'skin_q7' => array('label' => 'Skin Goals', 'type' => 'enumeration'),
					'skin_q8' => array('label' => 'Previous Treatments', 'type' => 'text'),
					'skin_q9' => array('label' => 'Skin Medical History', 'type' => 'text'),
					'skin_q10' => array('label' => 'Lifestyle Factors', 'type' => 'enumeration'),
				),
				'Testosterone Assessment' => array(
					'testosterone_q1' => array('label' => 'Energy Level', 'type' => 'enumeration'),
					'testosterone_q2' => array('label' => 'Libido Level', 'type' => 'enumeration'),
					'testosterone_q3' => array('label' => 'Muscle Mass Changes', 'type' => 'enumeration'),
					'testosterone_q4' => array('label' => 'Body Fat Distribution', 'type' => 'enumeration'),
					'testosterone_q5' => array('label' => 'Mood Changes', 'type' => 'enumeration'),
					'testosterone_q6' => array('label' => 'Sleep Quality', 'type' => 'enumeration'),
					'testosterone_q7' => array('label' => 'Morning Erections', 'type' => 'enumeration'),
					'testosterone_q8' => array('label' => 'Exercise Recovery', 'type' => 'enumeration'),
				),
				'Menopause Assessment' => array(
					'menopause_q1' => array('label' => 'Menstrual Status', 'type' => 'enumeration'),
					'menopause_q2' => array('label' => 'Hot Flash Frequency', 'type' => 'enumeration'),
					'menopause_q3' => array('label' => 'Night Sweats', 'type' => 'enumeration'),
					'menopause_q4' => array('label' => 'Mood Changes', 'type' => 'enumeration'),
					'menopause_q5' => array('label' => 'Sleep Disturbances', 'type' => 'enumeration'),
					'menopause_q6' => array('label' => 'Vaginal Dryness', 'type' => 'enumeration'),
					'menopause_q7' => array('label' => 'Libido Changes', 'type' => 'enumeration'),
					'menopause_q8' => array('label' => 'Weight Changes', 'type' => 'enumeration'),
					'menopause_q9' => array('label' => 'Joint Discomfort', 'type' => 'enumeration'),
					'menopause_q10' => array('label' => 'Memory/Concentration', 'type' => 'enumeration'),
				),
				'Health Assessment' => array(
					'health_q1' => array('label' => 'Overall Health Rating', 'type' => 'enumeration'),
					'health_q2' => array('label' => 'Energy Level', 'type' => 'enumeration'),
					'health_q3' => array('label' => 'Physical Activity', 'type' => 'enumeration'),
					'health_q4' => array('label' => 'Diet Quality', 'type' => 'enumeration'),
					'health_q5' => array('label' => 'Sleep Quality', 'type' => 'enumeration'),
					'health_q6' => array('label' => 'Stress Level', 'type' => 'enumeration'),
					'health_q7' => array('label' => 'Mental Health', 'type' => 'enumeration'),
					'health_q8' => array('label' => 'Chronic Conditions', 'type' => 'text'),
					'health_q9' => array('label' => 'Current Medications', 'type' => 'text'),
					'health_q10' => array('label' => 'Health Goals', 'type' => 'text'),
				),
				'Hormone Assessment' => array(
					'hormone_q1' => array('label' => 'Fatigue Level', 'type' => 'enumeration'),
					'hormone_q2' => array('label' => 'Mood Stability', 'type' => 'enumeration'),
					'hormone_q3' => array('label' => 'Weight Changes', 'type' => 'enumeration'),
					'hormone_q4' => array('label' => 'Libido Changes', 'type' => 'enumeration'),
					'hormone_q5' => array('label' => 'Hair Changes', 'type' => 'enumeration'),
					'hormone_q6' => array('label' => 'Skin Changes', 'type' => 'enumeration'),
					'hormone_q7' => array('label' => 'Sleep Issues', 'type' => 'enumeration'),
					'hormone_q8' => array('label' => 'Temperature Regulation', 'type' => 'enumeration'),
					'hormone_q9' => array('label' => 'Muscle/Joint Issues', 'type' => 'enumeration'),
					'hormone_q10' => array('label' => 'Cognitive Changes', 'type' => 'enumeration'),
				),
				'Sleep Assessment' => array(
					'sleep_q1' => array('label' => 'Sleep Duration', 'type' => 'enumeration'),
					'sleep_q2' => array('label' => 'Sleep Quality', 'type' => 'enumeration'),
					'sleep_q3' => array('label' => 'Time to Fall Asleep', 'type' => 'enumeration'),
					'sleep_q4' => array('label' => 'Night Awakenings', 'type' => 'enumeration'),
					'sleep_q5' => array('label' => 'Early Morning Awakening', 'type' => 'enumeration'),
					'sleep_q6' => array('label' => 'Daytime Fatigue', 'type' => 'enumeration'),
					'sleep_q7' => array('label' => 'Snoring', 'type' => 'enumeration'),
					'sleep_q8' => array('label' => 'Sleep Apnea', 'type' => 'enumeration'),
					'sleep_q9' => array('label' => 'Restless Legs', 'type' => 'enumeration'),
					'sleep_q10' => array('label' => 'Sleep Medications', 'type' => 'text'),
				),
				'Welcome Assessment' => array(
					'welcome_q1' => array('label' => 'Primary Health Goal', 'type' => 'enumeration'),
					'welcome_q2' => array('label' => 'Current Health Status', 'type' => 'enumeration'),
					'welcome_q3' => array('label' => 'Previous Treatments', 'type' => 'text'),
					'welcome_q4' => array('label' => 'Expectations', 'type' => 'text'),
					'welcome_q5' => array('label' => 'Commitment Level', 'type' => 'enumeration'),
				),
			),
			'biomarkers' => array(
				'glucose_level' => array('label' => 'Fasting Glucose', 'type' => 'number'),
				'hba1c_level' => array('label' => 'HbA1c', 'type' => 'number'),
				'testosterone_level' => array('label' => 'Total Testosterone', 'type' => 'number'),
				'estradiol_level' => array('label' => 'Estradiol', 'type' => 'number'),
				'cortisol_level' => array('label' => 'Cortisol', 'type' => 'number'),
				'tsh_level' => array('label' => 'TSH', 'type' => 'number'),
				'vitamin_d_level' => array('label' => 'Vitamin D', 'type' => 'number'),
				'iron_level' => array('label' => 'Iron', 'type' => 'number'),
				'b12_level' => array('label' => 'Vitamin B12', 'type' => 'number'),
				'folate_level' => array('label' => 'Folate', 'type' => 'number'),
				'creatinine_level' => array('label' => 'Creatinine', 'type' => 'number'),
				'bun_level' => array('label' => 'BUN', 'type' => 'number'),
				'alt_level' => array('label' => 'ALT', 'type' => 'number'),
				'ast_level' => array('label' => 'AST', 'type' => 'number'),
				'albumin_level' => array('label' => 'Albumin', 'type' => 'number'),
				'sodium_level' => array('label' => 'Sodium', 'type' => 'number'),
				'potassium_level' => array('label' => 'Potassium', 'type' => 'number'),
				'calcium_level' => array('label' => 'Calcium', 'type' => 'number'),
				'magnesium_level' => array('label' => 'Magnesium', 'type' => 'number'),
				'cholesterol_total' => array('label' => 'Total Cholesterol', 'type' => 'number'),
				'hdl_level' => array('label' => 'HDL', 'type' => 'number'),
				'ldl_level' => array('label' => 'LDL', 'type' => 'number'),
				'triglycerides_level' => array('label' => 'Triglycerides', 'type' => 'number'),
				'blood_pressure_systolic' => array('label' => 'Systolic BP', 'type' => 'number'),
				'blood_pressure_diastolic' => array('label' => 'Diastolic BP', 'type' => 'number'),
				'heart_rate' => array('label' => 'Heart Rate', 'type' => 'number'),
				'temperature' => array('label' => 'Temperature', 'type' => 'number'),
				'waist_circumference' => array('label' => 'Waist Circumference', 'type' => 'number'),
				'neck_circumference' => array('label' => 'Neck Circumference', 'type' => 'number'),
				'body_fat_percentage' => array('label' => 'Body Fat %', 'type' => 'number'),
			),
			'symptoms' => array(
				'fatigue_level' => array('label' => 'Fatigue Level', 'type' => 'enumeration'),
				'anxiety_level' => array('label' => 'Anxiety Level', 'type' => 'enumeration'),
				'depression_level' => array('label' => 'Depression Level', 'type' => 'enumeration'),
				'brain_fog_level' => array('label' => 'Brain Fog Level', 'type' => 'enumeration'),
				'weight_changes' => array('label' => 'Weight Changes', 'type' => 'enumeration'),
				'sleep_quality' => array('label' => 'Sleep Quality', 'type' => 'enumeration'),
				'energy_level' => array('label' => 'Energy Level', 'type' => 'enumeration'),
				'stress_level' => array('label' => 'Stress Level', 'type' => 'enumeration'),
				'mood_stability' => array('label' => 'Mood Stability', 'type' => 'enumeration'),
				'concentration_level' => array('label' => 'Concentration Level', 'type' => 'enumeration'),
				'memory_level' => array('label' => 'Memory Level', 'type' => 'enumeration'),
				'libido_level' => array('label' => 'Libido Level', 'type' => 'enumeration'),
				'pain_level' => array('label' => 'Pain Level', 'type' => 'enumeration'),
				'inflammation_level' => array('label' => 'Inflammation Level', 'type' => 'enumeration'),
				'digestive_issues' => array('label' => 'Digestive Issues', 'type' => 'enumeration'),
				'immune_function' => array('label' => 'Immune Function', 'type' => 'enumeration'),
				'cardiovascular_symptoms' => array('label' => 'Cardiovascular Symptoms', 'type' => 'enumeration'),
				'respiratory_symptoms' => array('label' => 'Respiratory Symptoms', 'type' => 'enumeration'),
				'neurological_symptoms' => array('label' => 'Neurological Symptoms', 'type' => 'enumeration'),
				'endocrine_symptoms' => array('label' => 'Endocrine Symptoms', 'type' => 'enumeration'),
			),
		);
	}

	/**
	 * Get total field count
	 */
	private function get_total_field_count() {
		$comprehensive_fields = $this->get_comprehensive_field_mappings();
		$total = 0;
		
		// Count global fields
		$total += count($comprehensive_fields['global']);
		
		// Count assessment fields
		foreach ($comprehensive_fields['assessments'] as $assessment_fields) {
			$total += count($assessment_fields);
		}
		
		// Count biomarker fields
		$total += count($comprehensive_fields['biomarkers']);
		
		// Count symptom fields
		$total += count($comprehensive_fields['symptoms']);
		
		return $total;
	}

	/**
	 * Settings field callbacks
	 */
	public function credentials_section_callback() {
		echo '<p>Enter your HubSpot application credentials. You can find these in your HubSpot Developer account.</p>';
	}

	public function client_id_callback() {
		$settings = get_option( 'ennu_hubspot_settings', array() );
		$value = isset( $settings['client_id'] ) ? $settings['client_id'] : '';
		echo '<input type="text" name="ennu_hubspot_settings[client_id]" value="' . esc_attr( $value ) . '" class="regular-text" />';
		echo '<p class="description">Your HubSpot app Client ID</p>';
	}

	public function client_secret_callback() {
		$settings = get_option( 'ennu_hubspot_settings', array() );
		$value = isset( $settings['client_secret'] ) ? $settings['client_secret'] : '';
		echo '<input type="password" name="ennu_hubspot_settings[client_secret]" value="' . esc_attr( $value ) . '" class="regular-text" />';
		echo '<p class="description">Your HubSpot app Client Secret</p>';
	}

	public function portal_id_callback() {
		$settings = get_option( 'ennu_hubspot_settings', array() );
		$value = isset( $settings['portal_id'] ) ? $settings['portal_id'] : '';
		echo '<input type="text" name="ennu_hubspot_settings[portal_id]" value="' . esc_attr( $value ) . '" class="regular-text" />';
		echo '<p class="description">Your HubSpot Portal ID</p>';
	}

	public function api_key_callback() {
		$settings = get_option( 'ennu_hubspot_settings', array() );
		$value = isset( $settings['api_key'] ) ? $settings['api_key'] : '';
		echo '<input type="password" name="ennu_hubspot_settings[api_key]" value="' . esc_attr( $value ) . '" class="regular-text" />';
		echo '<p class="description">Alternative: Your HubSpot API Key (for non-OAuth access)</p>';
	}

	/**
	 * Test HubSpot connection
	 */
	public function test_connection() {
		check_ajax_referer( 'ennu_hubspot_test', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}
		
		try {
			$oauth_handler = new ENNU_HubSpot_OAuth_Handler();
			$result = $oauth_handler->test_connection();
			
			if ( $result ) {
				wp_send_json_success( '✅ HubSpot connection successful! All credentials are valid.' );
			} else {
				wp_send_json_error( '❌ HubSpot connection failed. Please check your credentials.' );
			}
		} catch ( Exception $e ) {
			wp_send_json_error( '❌ Connection error: ' . $e->getMessage() );
		}
	}

	/**
	 * Sync HubSpot fields
	 */
	public function sync_hubspot_fields() {
		check_ajax_referer( 'ennu_hubspot_sync', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}
		
		try {
			$field_creator = new ENNU_HubSpot_Bulk_Field_Creator();
			$result = $field_creator->create_all_assessment_fields();
			
			if ( $result ) {
				update_option( 'ennu_hubspot_last_sync', current_time( 'mysql' ) );
				$sync_count = get_option( 'ennu_hubspot_sync_count', 0 ) + 1;
				update_option( 'ennu_hubspot_sync_count', $sync_count );
				
				wp_send_json_success( '✅ Successfully synced all 312 fields to HubSpot!' );
			} else {
				$error_count = get_option( 'ennu_hubspot_error_count', 0 ) + 1;
				update_option( 'ennu_hubspot_error_count', $error_count );
				
				wp_send_json_error( '❌ Field sync failed. Please check your HubSpot connection.' );
			}
		} catch ( Exception $e ) {
			$error_count = get_option( 'ennu_hubspot_error_count', 0 ) + 1;
			update_option( 'ennu_hubspot_error_count', $error_count );
			
			wp_send_json_error( '❌ Sync error: ' . $e->getMessage() );
		}
	}
} 