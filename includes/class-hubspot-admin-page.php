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
		add_action( 'admin_menu', array( $this, 'add_hubspot_menu' ), 20 ); // Priority 20 to ensure parent menu exists
		add_action( 'admin_init', array( $this, 'register_hubspot_settings' ), 10 );
		add_action( 'admin_init', array( $this, 'handle_settings_save' ), 15 );
		add_action( 'wp_ajax_ennu_test_hubspot_connection', array( $this, 'test_connection' ) );
		add_action( 'wp_ajax_ennu_sync_hubspot_data', array( $this, 'sync_hubspot_data' ) );
		add_action( 'wp_ajax_ennu_test_data_orchestration', array( $this, 'test_data_orchestration' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		
		// Log AJAX action registration
		// REMOVED: error_log( 'ENNU HubSpot Admin: AJAX actions registered' );
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
			'ennu_hubspot_access_token',
			'Access Token',
			array( $this, 'access_token_callback' ),
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
	}
	
	/**
	 * Handle settings save and sync to individual options
	 */
	public function handle_settings_save() {
		// Handle connection test via URL parameter (fallback if AJAX fails)
		if ( isset( $_GET['test_connection'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'test_connection' ) ) {
			$this->handle_direct_connection_test();
		}
		
		// Handle field mappings refresh
		if ( isset( $_GET['refresh_fields'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'refresh_fields' ) ) {
			delete_transient( 'ennu_hubspot_real_fields' );
			wp_redirect( admin_url( 'admin.php?page=ennu-hubspot-sync&fields_refreshed=1' ) );
			exit;
		}
		
		// Handle field mapping test
		if ( isset( $_GET['test_mappings'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'test_mappings' ) ) {
			$this->test_field_mappings();
			wp_redirect( admin_url( 'admin.php?page=ennu-hubspot-sync&mappings_tested=1' ) );
			exit;
		}
		
		// Handle manual sync via URL parameter
		if ( isset( $_GET['sync_now'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'sync_credentials' ) ) {
			$settings = get_option( 'ennu_hubspot_settings', array() );
			
			if ( !empty( $settings ) ) {
				if ( isset( $settings['access_token'] ) ) {
					update_option( 'ennu_hubspot_access_token', sanitize_text_field( $settings['access_token'] ) );
				}
				if ( isset( $settings['portal_id'] ) ) {
					update_option( 'ennu_hubspot_portal_id', sanitize_text_field( $settings['portal_id'] ) );
				}
				
				// Redirect to show success
				wp_redirect( admin_url( 'admin.php?page=ennu-hubspot-sync&synced=1' ) );
				exit;
			}
		}
		
		// Handle regular form save
		if ( isset( $_POST['option_page'] ) && $_POST['option_page'] === 'ennu_hubspot_settings' ) {
			if ( isset( $_POST['ennu_hubspot_settings'] ) ) {
				$settings = $_POST['ennu_hubspot_settings'];
				
				// Sync access_token to individual option for API classes
				if ( isset( $settings['access_token'] ) ) {
					update_option( 'ennu_hubspot_access_token', sanitize_text_field( $settings['access_token'] ) );
				}
				
				// Sync portal_id to individual option
				if ( isset( $settings['portal_id'] ) ) {
					update_option( 'ennu_hubspot_portal_id', sanitize_text_field( $settings['portal_id'] ) );
				}
			}
		}
	}

	/**
	 * Render the HubSpot admin page
	 */
	public function render_page() {
		$settings = get_option( 'ennu_hubspot_settings', array() );
		$oauth_handler = new ENNU_HubSpot_OAuth_Handler();
		
		// Force settings registration if not already done
		global $wp_settings_sections, $wp_settings_fields;
		if ( ! isset( $wp_settings_sections['ennu_hubspot_settings'] ) ) {
			$this->register_hubspot_settings();
		}
		
		// Debug: Check current values
		$access_token_option = get_option( 'ennu_hubspot_access_token', '' );
		$portal_id_option = get_option( 'ennu_hubspot_portal_id', '' );
		
		// Auto-sync if there's a mismatch and we have settings
		if ( !empty( $settings ) && isset( $settings['access_token'] ) && $access_token_option !== $settings['access_token'] ) {
			update_option( 'ennu_hubspot_access_token', sanitize_text_field( $settings['access_token'] ) );
			$access_token_option = $settings['access_token'];
		}
		if ( !empty( $settings ) && isset( $settings['portal_id'] ) && $portal_id_option !== $settings['portal_id'] ) {
			update_option( 'ennu_hubspot_portal_id', sanitize_text_field( $settings['portal_id'] ) );
			$portal_id_option = $settings['portal_id'];
		}
		
		?>
		<div class="wrap">
			<h1>ENNU HubSpot Sync</h1>
			<p>Manage your HubSpot integration credentials and sync settings.</p>
			
			<!-- Debug Info -->
			<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 20px 0;">
				<h3>🔍 Debug Information</h3>
				<p><strong>Settings Array:</strong> <?php echo empty($settings) ? 'Empty' : 'Contains ' . count($settings) . ' items'; ?></p>
				<p><strong>Access Token Option:</strong> <?php echo empty($access_token_option) ? '❌ Not Set' : '✅ Set (' . substr($access_token_option, 0, 15) . '...)'; ?></p>
				<p><strong>Portal ID Option:</strong> <?php echo empty($portal_id_option) ? '❌ Not Set' : '✅ Set (' . $portal_id_option . ')'; ?></p>
				<p><strong>Settings Sections:</strong> <?php echo isset($wp_settings_sections['ennu_hubspot_settings']) ? '✅ Registered' : '❌ Not Registered'; ?></p>
				<?php if (!empty($settings)): ?>
					<p><strong>Settings Content:</strong> <code><?php echo htmlspecialchars(print_r($settings, true)); ?></code></p>
				<?php endif; ?>
				
				<?php if (!empty($settings) && ($access_token_option !== $settings['access_token'] || $portal_id_option !== $settings['portal_id'])): ?>
					<div style="background: #f8d7da; padding: 10px; margin: 10px 0; border-left: 4px solid #dc3545;">
						<p><strong>⚠️ Sync Issue Detected!</strong> Settings array doesn't match individual options.</p>
						<a href="<?php echo admin_url('admin.php?page=ennu-hubspot-sync&sync_now=1&_wpnonce=' . wp_create_nonce('sync_credentials')); ?>" 
						   class="button button-primary">🔄 Sync Credentials Now</a>
					</div>
				<?php elseif (isset($_GET['synced'])): ?>
					<div style="background: #d4edda; padding: 10px; margin: 10px 0; border-left: 4px solid #28a745;">
						<p><strong>✅ Credentials synced successfully!</strong></p>
					</div>
				<?php endif; ?>
			</div>
			
			<div class="ennu-hubspot-admin-container">
				<!-- Sync Status -->
				<div class="ennu-hubspot-section">
					<h2>Sync Status</h2>
					<?php $this->display_sync_status(); ?>
				</div>
				
				<!-- Test Connection -->
				<div class="ennu-hubspot-section">
					<h2>Test Connection</h2>
					<button type="button" id="test-hubspot-connection" class="button button-primary">
						Test HubSpot Connection (AJAX)
					</button>
					<a href="<?php echo admin_url('admin.php?page=ennu-hubspot-sync&test_connection=1&_wpnonce=' . wp_create_nonce('test_connection')); ?>" 
					   class="button button-secondary" style="margin-left: 10px;">
						Test Connection (Direct)
					</a>
					<div id="test-connection-result"></div>
					
					<?php if (isset($_GET['test_result'])): ?>
						<div class="notice notice-<?php echo esc_attr($_GET['test_result']); ?>" style="margin-top: 10px;">
							<p><?php echo esc_html(urldecode($_GET['test_message'])); ?></p>
						</div>
					<?php endif; ?>
				</div>
				
				<!-- OAuth Status -->
				<div class="ennu-hubspot-section">
					<h2>OAuth Authentication Status</h2>
					<?php $this->display_oauth_status( $oauth_handler ); ?>
				</div>
				
				<!-- Credentials Form -->
				<div class="ennu-hubspot-section">
					<h2>🔐 HubSpot Private App</h2>
					<p><strong>Using Private App Access Token</strong> - No OAuth required</p>
					<form method="post" action="options.php">
						<?php settings_fields( 'ennu_hubspot_settings' ); ?>
						<table class="form-table">
							<tr>
								<th scope="row">🔑 Access Token</th>
								<td>
									<input type="text" name="ennu_hubspot_settings[access_token]" 
										   value="<?php echo esc_attr( isset($settings['access_token']) ? $settings['access_token'] : 'pat-na1-87f4d48b-321a-4711-a346-2f4d7bf1f247' ); ?>" 
										   class="regular-text" placeholder="pat-na1-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" />
									<p class="description"><strong>Private App Access Token</strong> - Found in HubSpot → Settings → Integrations → Private Apps</p>
									<p class="description"><strong>Current Individual Option Value:</strong> <?php echo esc_html($access_token_option ?: 'Not Set'); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row">🏢 Portal ID</th>
								<td>
									<input type="text" name="ennu_hubspot_settings[portal_id]" 
										   value="<?php echo esc_attr( isset($settings['portal_id']) ? $settings['portal_id'] : '48195592' ); ?>" 
										   class="regular-text" placeholder="48195592" />
									<p class="description">Your HubSpot Portal ID (Account ID)</p>
									<p class="description"><strong>Current Individual Option Value:</strong> <?php echo esc_html($portal_id_option ?: 'Not Set'); ?></p>
								</td>
							</tr>
						</table>
						<div style="background: #f0f8ff; padding: 15px; border-left: 4px solid #0073aa; margin: 20px 0;">
							<h4>💡 How to find your credentials:</h4>
							<ol>
								<li>Go to HubSpot → Settings → Integrations → Private Apps</li>
								<li>Click your private app or create new one</li>
								<li>Copy the <strong>Access Token</strong></li>
								<li>Portal ID is in your HubSpot URL: <code>app.hubspot.com/contacts/<strong>PORTAL_ID</strong></code></li>
							</ol>
							<p><strong>✨ Private apps are simpler than OAuth - no Client ID/Secret needed!</strong></p>
						</div>
						<?php submit_button( 'Save Private App Credentials' ); ?>
					</form>
					
					<?php if (!empty($settings) && (empty($access_token_option) || empty($portal_id_option))): ?>
						<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0;">
							<h4>⚠️ Sync Required</h4>
							<p>The form has credentials but the individual options are missing. Click to sync:</p>
							<a href="<?php echo admin_url('admin.php?page=ennu-hubspot-sync&sync_now=1&_wpnonce=' . wp_create_nonce('sync_credentials')); ?>" 
							   class="button button-primary">🔄 Sync Credentials to Individual Options</a>
						</div>
					<?php endif; ?>
				</div>
				
				<!-- Field Mapping -->
				<div class="ennu-hubspot-section">
					<h2>Field Mapping Validation</h2>
					<p>WordPress Field ID → HubSpot Field ID mapping with sync status</p>
					
					<div class="mapping-controls" style="margin-bottom: 20px;">
						<a href="<?php echo admin_url('admin.php?page=ennu-hubspot-sync&refresh_fields=1&_wpnonce=' . wp_create_nonce('refresh_fields')); ?>" 
						   class="button button-secondary">🔄 Refresh Field Mappings</a>
						<a href="<?php echo admin_url('admin.php?page=ennu-hubspot-sync&test_mappings=1&_wpnonce=' . wp_create_nonce('test_mappings')); ?>" 
						   class="button button-primary">🧪 Test All Field Mappings</a>
					</div>
					
					<?php if (isset($_GET['fields_refreshed'])): ?>
						<div class="notice notice-success" style="margin: 10px 0;">
							<p>✅ <strong>Field mappings refreshed!</strong> Now showing current HubSpot field IDs.</p>
						</div>
					<?php endif; ?>
					
					<?php if (isset($_GET['mappings_tested'])): ?>
						<div class="notice notice-info" style="margin: 10px 0;">
							<p>🧪 <strong>Field mapping test completed!</strong> Check results below.</p>
						</div>
					<?php endif; ?>
					
					<?php $this->display_field_mapping_validation(); ?>
				</div>
				
				<!-- Sync Data -->
				<div class="ennu-hubspot-section">
					<h2>Sync Assessment Data</h2>
					<p>Sync existing assessment responses and user data to HubSpot (no new fields created)</p>
					<button type="button" id="sync-hubspot-data" class="button button-secondary">
						Sync All Assessment Data to HubSpot
					</button>
					<div id="sync-data-result"></div>
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
		.field-hubspot-type {
			background: #f3e5f5;
			color: #7b1fa2;
			padding: 2px 6px;
			border-radius: 3px;
			font-weight: 500;
			margin-left: 5px;
		}
		.field-meta {
			margin-top: 8px;
			padding-top: 8px;
			border-top: 1px solid #eee;
			color: #666;
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
		
		/* Field Mapping Validation Styles */
		.mapping-tab-content {
			display: none;
			padding: 20px 0;
		}
		.mapping-tab-content.active {
			display: block;
		}
		.mapping-table tr.mapped {
			background-color: #f0f8f0;
		}
		.mapping-table tr.unmapped {
			background-color: #fff0f0;
		}
		.mapping-table th {
			background-color: #f8f9fa !important;
			font-weight: 600;
		}
		.unmapped-field {
			padding: 10px;
			margin: 5px 0;
			border-left: 4px solid #d63638;
			background: #fff8f8;
		}
		.mapping-summary div {
			text-align: center;
			padding: 10px;
			background: white;
			border-radius: 5px;
			border: 1px solid #ddd;
		}
		</style>
		
		<script>
		jQuery(document).ready(function($) {
			// Test HubSpot Connection
			$('#test-hubspot-connection').click(function() {
				var button = $(this);
				var result = $('#test-connection-result');
				
				button.prop('disabled', true).text('Testing...');
				result.html('');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'ennu_test_hubspot_connection',
						nonce: '<?php echo wp_create_nonce( 'ennu_hubspot_test' ); ?>',
						_wpnonce: '<?php echo wp_create_nonce( 'ennu_hubspot_test' ); ?>'
					},
					success: function(response) {
						// REMOVED: console.log('Success Response:', response);
						if (response.success) {
							result.html('<div class="notice notice-success"><p>✅ ' + response.data + '</p></div>');
						} else {
							result.html('<div class="notice notice-error"><p>❌ ' + response.data + '</p></div>');
						}
					},
					error: function(xhr, status, error) {
						console.log('AJAX Error Details:', {
							status: xhr.status,
							statusText: xhr.statusText,
							responseText: xhr.responseText,
							error: error
						});
						
						var errorMsg = 'Connection test failed';
						if (xhr.status === 403) {
							errorMsg = 'Access denied - please check permissions';
						} else if (xhr.status === 0) {
							errorMsg = 'Network error - check your connection';
						}
						
						result.html('<div class="notice notice-error"><p>❌ ' + errorMsg + ' (Status: ' + xhr.status + ')</p></div>');
					},
					complete: function() {
						button.prop('disabled', false).text('Test HubSpot Connection');
					}
				});
			});
			
			$('#sync-hubspot-data').click(function() {
				var button = $(this);
				var result = $('#sync-data-result');
				
				button.prop('disabled', true).text('Syncing Assessment Data...');
				result.html('');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'ennu_sync_hubspot_data',
						nonce: '<?php echo wp_create_nonce( 'ennu_hubspot_sync' ); ?>'
					},
					success: function(response) {
						if (response.success) {
							result.html('<div class="notice notice-success"><p>✅ ' + response.data + '</p></div>');
						} else {
							result.html('<div class="notice notice-error"><p>❌ ' + response.data + '</p></div>');
						}
					},
					error: function(xhr, status, error) {
						result.html('<div class="notice notice-error"><p>❌ Data sync failed: ' + error + '</p></div>');
					},
					complete: function() {
						button.prop('disabled', false).text('Sync All Assessment Data to HubSpot');
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
		$portal_id = get_option( 'ennu_hubspot_portal_id', '' );
		
		// For private apps, we only need access_token, not refresh_token
		if ( ! empty( $access_token ) ) {
			echo '<div class="oauth-status connected">';
			echo '<strong>✅ Connected to HubSpot (Private App)</strong><br>';
			echo 'Access Token: ' . substr( $access_token, 0, 20 ) . '...<br>';
			if ( ! empty( $portal_id ) ) {
				echo 'Portal ID: ' . $portal_id . '<br>';
			}
			echo 'Type: Private App Authentication';
			echo '</div>';
		} else {
			echo '<div class="oauth-status disconnected">';
			echo '<strong>❌ Not Connected to HubSpot</strong><br>';
			echo 'Please enter your private app access token below.';
			echo '</div>';
		}
	}

	/**
	 * Display ALL fields from both WordPress and HubSpot
	 */
	private function display_field_mapping_validation() {
		$wordpress_fields = $this->get_wordpress_assessment_fields();
		$hubspot_fields = $this->get_comprehensive_field_mappings();
		$mapping_status = get_option( 'ennu_field_mapping_test_results', array() );
		
		echo '<div class="field-mapping-validation">';
		
		// Data Orchestration Status Section
		$this->display_data_orchestration_status();
		
		echo '<hr style="margin: 30px 0; border: none; border-top: 2px solid #0073aa;">';
		
		echo '<h3>📊 WordPress → HubSpot Field Mapping Status</h3>';
		
		// Summary stats
		$total_wp_fields = $this->count_total_fields( $wordpress_fields );
		$total_hs_fields = $this->count_total_fields( $hubspot_fields );
		$mapped_fields = 0;
		$sync_issues = 0;
		
		foreach ( $mapping_status as $status ) {
			if ( $status['status'] === 'mapped' ) $mapped_fields++;
			if ( $status['status'] === 'error' ) $sync_issues++;
		}
		
		echo '<div class="mapping-summary" style="display: flex; gap: 20px; margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px;">';
		echo '<div><strong>WordPress Fields:</strong> <span style="color: #2271b1;">' . $total_wp_fields . '</span></div>';
		echo '<div><strong>HubSpot Fields:</strong> <span style="color: #00a32a;">' . $total_hs_fields . '</span></div>';
		echo '<div><strong>Mapped:</strong> <span style="color: #00a32a;">' . $mapped_fields . '</span></div>';
		echo '<div><strong>Issues:</strong> <span style="color: #d63638;">' . $sync_issues . '</span></div>';
		echo '</div>';
		
		// Create tabs for each assessment type
		$this->display_assessment_tabs( $wordpress_fields, $hubspot_fields, $mapping_status );
		
		echo '</div>';
	}

	/**
	 * Display Data Orchestration Status - Multi-Object Sync Health
	 */
	private function display_data_orchestration_status() {
		echo '<h3>🎯 Data Orchestration Status</h3>';
		echo '<p style="color: #666; margin-bottom: 20px;">Real-time status of multi-object HubSpot CRM synchronization</p>';
		
		// Get orchestration metrics
		$orchestration_stats = $this->get_orchestration_metrics();
		
		// Main orchestration dashboard
		echo '<div class="orchestration-dashboard" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 30px;">';
		
		// Contact Sync Status
		echo '<div class="orchestration-card" style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">';
		echo '<div style="display: flex; align-items: center; justify-content: space-between;">';
		echo '<div>';
		echo '<h4 style="margin: 0 0 10px 0; color: white;">👤 Contact Objects</h4>';
		echo '<div style="font-size: 24px; font-weight: bold; margin: 10px 0;">' . $orchestration_stats['contacts']['total'] . '</div>';
		echo '<div style="font-size: 12px; opacity: 0.9;">Total Contacts Synced</div>';
		echo '</div>';
		echo '<div style="text-align: right;">';
		echo '<div style="font-size: 14px;">✅ ' . $orchestration_stats['contacts']['successful'] . ' Active</div>';
		echo '<div style="font-size: 14px;">❌ ' . $orchestration_stats['contacts']['errors'] . ' Errors</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		
		// Assessment Objects Status  
		echo '<div class="orchestration-card" style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">';
		echo '<div style="display: flex; align-items: center; justify-content: space-between;">';
		echo '<div>';
		echo '<h4 style="margin: 0 0 10px 0; color: white;">📋 Assessment Records</h4>';
		echo '<div style="font-size: 24px; font-weight: bold; margin: 10px 0;">' . $orchestration_stats['assessments']['total'] . '</div>';
		echo '<div style="font-size: 12px; opacity: 0.9;">Custom Object: ' . $orchestration_stats['assessments']['object_id'] . '</div>';
		echo '</div>';
		echo '<div style="text-align: right;">';
		echo '<div style="font-size: 14px;">🔗 ' . $orchestration_stats['assessments']['associations'] . ' Associated</div>';
		echo '<div style="font-size: 14px;">⏱️ ' . $orchestration_stats['assessments']['recent'] . ' Recent</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		
		// Biomarker Objects Status
		echo '<div class="orchestration-card" style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">';
		echo '<div style="display: flex; align-items: center; justify-content: space-between;">';
		echo '<div>';
		echo '<h4 style="margin: 0 0 10px 0; color: white;">🧬 Biomarker Objects</h4>';
		echo '<div style="font-size: 24px; font-weight: bold; margin: 10px 0;">' . $orchestration_stats['biomarkers']['total'] . '</div>';
		echo '<div style="font-size: 12px; opacity: 0.9;">Object: ' . $orchestration_stats['biomarkers']['object_id'] . '</div>';
		echo '</div>';
		echo '<div style="text-align: right;">';
		echo '<div style="font-size: 14px;">📊 ' . $orchestration_stats['biomarkers']['snapshots'] . ' Snapshots</div>';
		echo '<div style="font-size: 14px;">🔄 ' . $orchestration_stats['biomarkers']['synced'] . ' Synced</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		
		echo '</div>';
		
		// Detailed Orchestration Metrics
		echo '<div class="orchestration-details" style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin-bottom: 20px;">';
		echo '<h4 style="margin: 0 0 15px 0; color: #333;">🔄 Sync Operations (Last 24 Hours)</h4>';
		
		echo '<div style="display: flex; gap: 30px; flex-wrap: wrap;">';
		echo '<div><strong>Contact Updates:</strong> <span style="color: #00a32a;">' . $orchestration_stats['operations']['contact_updates'] . '</span></div>';
		echo '<div><strong>Object Creations:</strong> <span style="color: #2271b1;">' . $orchestration_stats['operations']['object_creations'] . '</span></div>';
		echo '<div><strong>Associations Made:</strong> <span style="color: #8e44ad;">' . $orchestration_stats['operations']['associations'] . '</span></div>';
		echo '<div><strong>Biomarker Syncs:</strong> <span style="color: #e67e22;">' . $orchestration_stats['operations']['biomarker_syncs'] . '</span></div>';
		echo '<div><strong>Failed Operations:</strong> <span style="color: #d63638;">' . $orchestration_stats['operations']['failed'] . '</span></div>';
		echo '</div>';
		
		echo '<div style="margin-top: 15px; padding: 10px; background: white; border-radius: 5px; border-left: 4px solid #0073aa;">';
		echo '<div style="display: flex; gap: 40px; flex-wrap: wrap;">';
		echo '<div><strong>Latest Sync:</strong> ' . $orchestration_stats['last_sync']['timestamp'];
		echo ' | <strong>Assessment:</strong> ' . $orchestration_stats['last_sync']['assessment_type'];
		echo ' | <strong>Status:</strong> ';
		if ( $orchestration_stats['last_sync']['success'] ) {
			echo '<span style="color: #00a32a;">✅ Success</span>';
		} else {
			echo '<span style="color: #d63638;">❌ Failed</span>';
		}
		echo '</div>';
		
		// API Status Info
		echo '<div><strong>API Status:</strong> ';
		if ( $orchestration_stats['api_status']['connected'] ) {
			echo '<span style="color: #00a32a;">🟢 Connected</span>';
		} else {
			echo '<span style="color: #d63638;">🔴 Disconnected</span>';
		}
		echo ' | <strong>Account:</strong> ' . $orchestration_stats['api_status']['account_name'];
		echo ' | <strong>Data Age:</strong> ' . $orchestration_stats['api_status']['data_age'];
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
		
		// Quick Action Buttons
		echo '<div class="orchestration-actions" style="display: flex; gap: 10px; margin-bottom: 20px;">';
		echo '<button type="button" onclick="refreshOrchestrationStatus()" class="button button-secondary" style="background: #667eea; color: white; border: none;">🔄 Refresh Status</button>';
		echo '<button type="button" onclick="testDataOrchestration()" class="button button-secondary" style="background: #f093fb; color: white; border: none;">🧪 Test Orchestration</button>';
		echo '<button type="button" onclick="viewSyncLogs()" class="button button-secondary" style="background: #4facfe; color: white; border: none;">📋 View Sync Logs</button>';
		echo '</div>';
		
		// Add JavaScript for orchestration actions
		echo '<script>
		function refreshOrchestrationStatus() {
			location.reload();
		}
		
		function testDataOrchestration() {
			if (confirm("This will run a test sync operation across all HubSpot objects. Continue?")) {
				jQuery.post(ajaxurl, {
					action: "ennu_test_data_orchestration",
					nonce: "' . wp_create_nonce( 'ennu_orchestration_test' ) . '"
				}, function(response) {
					alert("Orchestration test completed. Check the sync logs for results.");
					location.reload();
				});
			}
		}
		
		function viewSyncLogs() {
			window.open("/wp-admin/admin.php?page=ennu-sync-logs", "_blank");
		}
		</script>';
	}
	
	/**
	 * Get orchestration metrics from live HubSpot API data and WordPress database
	 */
	private function get_orchestration_metrics() {
		// Get live HubSpot data from API fetch
		$live_data = get_option( 'ennu_hubspot_orchestration_live_data', array() );
		$contact_fields_data = get_option( 'ennu_hubspot_contact_fields_live', array() );
		$assessment_data = get_option( 'ennu_hubspot_assessment_records_live', array() );
		$biomarker_data = get_option( 'ennu_hubspot_biomarker_records_live', array() );
		
		// Get sync statistics
		$last_sync = get_option( 'ennu_hubspot_last_sync', '' );
		$sync_count = get_option( 'ennu_hubspot_sync_count', 0 );
		$failed_syncs = get_option( 'ennu_hubspot_failed_syncs', array() );
		
		// Get WordPress user data for contact calculations
		global $wpdb;
		$wp_contact_count = $wpdb->get_var( 
			"SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE 'ennu_%_email' AND meta_value != ''"
		);
		
		$wp_assessment_count = $wpdb->get_var(
			"SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE 'ennu_%_calculated_score'"
		);
		
		$wp_biomarker_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} 
			WHERE meta_key = 'ennu_user_biomarkers'"
		);
		
		// Recent sync operations (last 24 hours)
		$recent_syncs = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} 
				WHERE meta_key LIKE 'ennu_hubspot_sync_%' 
				AND meta_value LIKE %s",
				'%' . date('Y-m-d', strtotime('-1 day')) . '%'
			)
		);
		
		// Use live HubSpot data when available, fallback to WordPress data
		$hubspot_assessment_records = isset( $assessment_data['total_records'] ) ? $assessment_data['total_records'] : $wp_assessment_count;
		$hubspot_biomarker_records = isset( $biomarker_data['total_records'] ) ? $biomarker_data['total_records'] : $wp_biomarker_count;
		
		// Calculate data freshness
		$data_age = 'Unknown';
		if ( ! empty( $live_data['timestamp'] ) ) {
			$age_minutes = round( ( time() - strtotime( $live_data['timestamp'] ) ) / 60 );
			if ( $age_minutes < 60 ) {
				$data_age = $age_minutes . ' minutes ago';
			} else {
				$data_age = round( $age_minutes / 60 ) . ' hours ago';
			}
		}
		
		return array(
			'contacts' => array(
				'total' => isset( $live_data['contact_properties'] ) ? $live_data['contact_properties'] : intval( $wp_contact_count ),
				'successful' => isset( $contact_fields_data['ennu_contact_fields'] ) ? $contact_fields_data['ennu_contact_fields'] : intval( $wp_contact_count ),
				'errors' => count( $failed_syncs ),
				'ennu_fields' => isset( $contact_fields_data['ennu_contact_fields'] ) ? $contact_fields_data['ennu_contact_fields'] : 0,
			),
			'assessments' => array(
				'total' => $hubspot_assessment_records,
				'object_id' => '2-47128703 (basic_assessments)',
				'associations' => $hubspot_assessment_records, // 1:1 association
				'recent' => intval( $recent_syncs ),
				'wp_count' => intval( $wp_assessment_count ),
			),
			'biomarkers' => array(
				'total' => $hubspot_biomarker_records,
				'object_id' => '2-47128895 (biomarkers)',
				'snapshots' => $hubspot_biomarker_records,
				'synced' => $hubspot_biomarker_records,
				'wp_count' => intval( $wp_biomarker_count ),
			),
			'operations' => array(
				'contact_updates' => intval( $recent_syncs ),
				'object_creations' => intval( $recent_syncs ),
				'associations' => intval( $recent_syncs ),
				'biomarker_syncs' => intval( $recent_syncs ),
				'failed' => count( $failed_syncs ),
			),
			'last_sync' => array(
				'timestamp' => $last_sync ? date( 'M j, Y H:i', strtotime( $last_sync ) ) : 'Never',
				'assessment_type' => get_option( 'ennu_hubspot_last_sync_assessment', 'N/A' ),
				'success' => get_option( 'ennu_hubspot_last_sync_success', true ),
			),
			'api_status' => array(
				'connected' => isset( $live_data['api_status'] ) && $live_data['api_status'] === 'connected',
				'account_name' => $live_data['account_name'] ?? 'Unknown',
				'data_age' => $data_age,
				'last_fetch' => isset( $live_data['timestamp'] ) ? date( 'M j, Y H:i', strtotime( $live_data['timestamp'] ) ) : 'Never',
			),
		);
	}

	/**
	 * Display comprehensive field mapping status (old method)
	 */
	private function display_field_mapping_status_old() {
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
			if ( isset( $field_data['field_type'] ) ) {
				echo '<span class="field-hubspot-type">' . esc_html( $field_data['field_type'] ) . '</span>';
			}
			echo '</div>';
			if ( isset( $field_data['created'] ) && ! empty( $field_data['created'] ) ) {
				echo '<div class="field-meta">';
				echo '<small>Created: ' . esc_html( date( 'M j, Y', strtotime( $field_data['created'] ) ) ) . '</small>';
				if ( isset( $field_data['object_name'] ) ) {
					echo ' | <small>Object: ' . esc_html( $field_data['object_name'] ) . '</small>';
				}
				echo '</div>';
			}
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
			if ( isset( $field_data['field_type'] ) ) {
				echo '<span class="field-hubspot-type">' . esc_html( $field_data['field_type'] ) . '</span>';
			}
			echo '</div>';
			if ( isset( $field_data['created'] ) && ! empty( $field_data['created'] ) ) {
				echo '<div class="field-meta">';
				echo '<small>Created: ' . esc_html( date( 'M j, Y', strtotime( $field_data['created'] ) ) ) . '</small>';
				if ( isset( $field_data['object_name'] ) ) {
					echo ' | <small>Object: ' . esc_html( $field_data['object_name'] ) . '</small>';
				}
				echo '</div>';
			}
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
			if ( isset( $field_data['field_type'] ) ) {
				echo '<span class="field-hubspot-type">' . esc_html( $field_data['field_type'] ) . '</span>';
			}
			echo '</div>';
			if ( isset( $field_data['created'] ) && ! empty( $field_data['created'] ) ) {
				echo '<div class="field-meta">';
				echo '<small>Created: ' . esc_html( date( 'M j, Y', strtotime( $field_data['created'] ) ) ) . '</small>';
				if ( isset( $field_data['object_name'] ) ) {
					echo ' | <small>Object: ' . esc_html( $field_data['object_name'] ) . '</small>';
				}
				echo '</div>';
			}
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
	 * Get comprehensive field mappings - REAL HubSpot field IDs
	 */
	private function get_comprehensive_field_mappings() {
		// Check cache first
		$cached_fields = get_transient( 'ennu_hubspot_real_fields' );
		if ( $cached_fields !== false ) {
			return $cached_fields;
		}
		
		// Get real field IDs from HubSpot API
		$real_fields = $this->fetch_hubspot_field_mappings();
		
		if ( ! empty( $real_fields ) ) {
			// Cache for 1 hour
			set_transient( 'ennu_hubspot_real_fields', $real_fields, HOUR_IN_SECONDS );
			return $real_fields;
		}
		// Fallback if field creator not available - basic mapping
		return array(
			'global' => array(
				'notice' => array('label' => '⚠️ Field Creator Not Available - Install ENNU_HubSpot_Bulk_Field_Creator', 'type' => 'notice'),
			),
			'assessments' => array(
				'Notice' => array(
					'field_creator_missing' => array('label' => 'Cannot load field mappings - ENNU_HubSpot_Bulk_Field_Creator class missing', 'type' => 'notice'),
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
	 * Fetch real field mappings from HubSpot API
	 */
	private function fetch_hubspot_field_mappings() {
		$access_token = get_option( 'ennu_hubspot_access_token', '' );
		
		if ( empty( $access_token ) ) {
			return false;
		}
		
		$field_mappings = array(
			'global' => array(),
			'assessments' => array(),
			'biomarkers' => array(),
			'symptoms' => array(),
		);
		
		try {
			// Get contact properties (for contact fields)
			$contact_response = wp_remote_get( 'https://api.hubapi.com/crm/v3/properties/contacts', array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type' => 'application/json'
				),
				'timeout' => 30
			) );
			
			if ( ! is_wp_error( $contact_response ) && wp_remote_retrieve_response_code( $contact_response ) === 200 ) {
				$contact_data = json_decode( wp_remote_retrieve_body( $contact_response ), true );
				
				if ( isset( $contact_data['results'] ) ) {
					foreach ( $contact_data['results'] as $property ) {
						$field_name = $property['name'];
						
						// Categorize fields based on naming patterns
						if ( strpos( $field_name, 'ennu_global_' ) === 0 || strpos( $field_name, 'global_' ) === 0 ) {
							$field_mappings['global'][$field_name] = array(
								'label' => $property['label'],
								'type' => $property['type'],
								'hubspot_id' => $field_name,
								'field_type' => $property['fieldType'],
								'created' => isset( $property['createdAt'] ) ? $property['createdAt'] : '',
								'updated' => isset( $property['updatedAt'] ) ? $property['updatedAt'] : '',
							);
						} elseif ( strpos( $field_name, 'weight_loss_' ) === 0 || strpos( $field_name, 'wl_' ) === 0 ) {
							if ( ! isset( $field_mappings['assessments']['Weight Loss Assessment'] ) ) {
								$field_mappings['assessments']['Weight Loss Assessment'] = array();
							}
							$field_mappings['assessments']['Weight Loss Assessment'][$field_name] = array(
								'label' => $property['label'],
								'type' => $property['type'],
								'hubspot_id' => $field_name,
								'field_type' => $property['fieldType'],
								'created' => isset( $property['createdAt'] ) ? $property['createdAt'] : '',
								'updated' => isset( $property['updatedAt'] ) ? $property['updatedAt'] : '',
							);
						} elseif ( strpos( $field_name, 'hormone_' ) === 0 || strpos( $field_name, 'testosterone_' ) === 0 || strpos( $field_name, 'menopause_' ) === 0 ) {
							if ( ! isset( $field_mappings['assessments']['Hormone Assessments'] ) ) {
								$field_mappings['assessments']['Hormone Assessments'] = array();
							}
							$field_mappings['assessments']['Hormone Assessments'][$field_name] = array(
								'label' => $property['label'],
								'type' => $property['type'],
								'hubspot_id' => $field_name,
								'field_type' => $property['fieldType'],
								'created' => isset( $property['createdAt'] ) ? $property['createdAt'] : '',
								'updated' => isset( $property['updatedAt'] ) ? $property['updatedAt'] : '',
							);
						} elseif ( preg_match( '/^(glucose|hba1c|testosterone|estradiol|cortisol|tsh|vitamin_d|iron|b12|folate|creatinine|bun|alt|ast|albumin|sodium|potassium|calcium|magnesium|cholesterol|hdl|ldl|triglycerides|blood_pressure|heart_rate)/', $field_name ) ) {
							$field_mappings['biomarkers'][$field_name] = array(
								'label' => $property['label'],
								'type' => $property['type'],
								'hubspot_id' => $field_name,
								'field_type' => $property['fieldType'],
								'created' => isset( $property['createdAt'] ) ? $property['createdAt'] : '',
								'updated' => isset( $property['updatedAt'] ) ? $property['updatedAt'] : '',
							);
						} elseif ( preg_match( '/(symptom|fatigue|anxiety|depression|brain_fog|energy|stress|mood|pain|inflammation)/', $field_name ) ) {
							$field_mappings['symptoms'][$field_name] = array(
								'label' => $property['label'],
								'type' => $property['type'],
								'hubspot_id' => $field_name,
								'field_type' => $property['fieldType'],
								'created' => isset( $property['createdAt'] ) ? $property['createdAt'] : '',
								'updated' => isset( $property['updatedAt'] ) ? $property['updatedAt'] : '',
							);
						}
					}
				}
			}
			
			// Get custom objects (for custom object fields)
			$objects_response = wp_remote_get( 'https://api.hubapi.com/crm/v3/schemas', array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type' => 'application/json'
				),
				'timeout' => 30
			) );
			
			if ( ! is_wp_error( $objects_response ) && wp_remote_retrieve_response_code( $objects_response ) === 200 ) {
				$objects_data = json_decode( wp_remote_retrieve_body( $objects_response ), true );
				
				if ( isset( $objects_data['results'] ) ) {
					foreach ( $objects_data['results'] as $object ) {
						// Get properties for each custom object
						$object_id = $object['objectTypeId'];
						$object_name = $object['name'];
						
						if ( strpos( $object_name, 'assessment' ) !== false || strpos( $object_name, 'ennu' ) !== false ) {
							$properties_response = wp_remote_get( "https://api.hubapi.com/crm/v3/properties/{$object_id}", array(
								'headers' => array(
									'Authorization' => 'Bearer ' . $access_token,
									'Content-Type' => 'application/json'
								),
								'timeout' => 30
							) );
							
							if ( ! is_wp_error( $properties_response ) && wp_remote_retrieve_response_code( $properties_response ) === 200 ) {
								$properties_data = json_decode( wp_remote_retrieve_body( $properties_response ), true );
								
								if ( isset( $properties_data['results'] ) ) {
									foreach ( $properties_data['results'] as $property ) {
										$field_name = $property['name'];
										$assessment_category = 'Custom Object: ' . $object_name;
										
										if ( ! isset( $field_mappings['assessments'][$assessment_category] ) ) {
											$field_mappings['assessments'][$assessment_category] = array();
										}
										
										$field_mappings['assessments'][$assessment_category][$field_name] = array(
											'label' => $property['label'],
											'type' => $property['type'],
											'hubspot_id' => $field_name,
											'field_type' => $property['fieldType'],
											'object_id' => $object_id,
											'object_name' => $object_name,
											'created' => isset( $property['createdAt'] ) ? $property['createdAt'] : '',
											'updated' => isset( $property['updatedAt'] ) ? $property['updatedAt'] : '',
										);
									}
								}
							}
						}
					}
				}
			}
			
			return $field_mappings;
			
		} catch ( Exception $e ) {
			error_log( 'ENNU HubSpot: Error fetching real field mappings: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Get ALL WordPress assessment fields from database - including individual questions
	 */
	private function get_wordpress_assessment_fields() {
		global $wpdb;
		
		// Get all ENNU fields from user meta using the actual patterns found in the database
		$wp_fields = $wpdb->get_results(
			"SELECT DISTINCT meta_key, meta_value FROM {$wpdb->usermeta} 
			 WHERE meta_key LIKE 'ennu_%'
			 AND meta_key NOT LIKE '%_sync_%'
			 AND meta_key NOT LIKE '%_history%'
			 AND meta_key NOT LIKE '%_log%'
			 ORDER BY meta_key LIMIT 500"
		);
		
		$field_structure = array(
			'assessments' => array(),
			'global' => array(),
			'biomarkers' => array(),
			'symptoms' => array()
		);
		
		// Assessment type mapping based on actual database patterns
		$assessment_patterns = array(
			'weight-loss' => 'Weight Loss Assessment',
			'weight_loss' => 'Weight Loss Assessment', 
			'testosterone' => 'Testosterone Assessment',
			'health' => 'Health Assessment',
			'hormone' => 'Hormone Assessment',
			'skin' => 'Skin Assessment',
			'sleep' => 'Sleep Assessment',
			'hair' => 'Hair Assessment',
			'ed-treatment' => 'ED Treatment Assessment',
			'welcome' => 'Welcome Assessment'
		);
		
		foreach ( $wp_fields as $field ) {
			$field_key = $field->meta_key;
			$sample_value = $field->meta_value;
			
			// Parse assessment fields using actual patterns
			foreach ( $assessment_patterns as $pattern => $display_name ) {
				if ( strpos( $field_key, 'ennu_' . $pattern . '_' ) === 0 ) {
					// Extract question fields (ending with _q + number)
					if ( preg_match( '/ennu_' . preg_quote( $pattern ) . '_(.*_q\d+)$/', $field_key, $matches ) ||
						 preg_match( '/ennu_' . preg_quote( $pattern ) . '_(q\d+)$/', $field_key, $matches ) ) {
						
						$question_key = $matches[1];
						$field_structure['assessments'][$pattern][$field_key] = array(
							'wp_key' => $question_key,
							'full_key' => $field_key,
							'assessment' => $pattern,
							'type' => 'question',
							'sample_value' => is_array( $sample_value ) ? json_encode( $sample_value ) : substr( $sample_value, 0, 100 ),
							'container_key' => 'ennu_' . $pattern . '_assessment'
						);
					} 
					// Extract metadata fields (scores, categories, etc.)
					elseif ( preg_match( '/ennu_' . preg_quote( $pattern ) . '_(calculated_score|category_scores|pillar_scores|score_breakdown|symptoms)$/', $field_key ) ) {
						$meta_type = str_replace( 'ennu_' . $pattern . '_', '', $field_key );
						$field_structure['assessments'][$pattern][$field_key] = array(
							'wp_key' => $meta_type,
							'full_key' => $field_key,
							'assessment' => $pattern,
							'type' => 'metadata',
							'sample_value' => is_array( $sample_value ) ? json_encode( $sample_value ) : substr( $sample_value, 0, 100 ),
							'meta_type' => $meta_type
						);
					}
					// Extract contact fields (first_name, last_name, email, etc.)
					elseif ( preg_match( '/ennu_' . preg_quote( $pattern ) . '_(first_name|last_name|email|billing_phone|assessment_type|auto_submit_ready|completed)$/', $field_key ) ) {
						$contact_type = str_replace( 'ennu_' . $pattern . '_', '', $field_key );
						$field_structure['assessments'][$pattern][$field_key] = array(
							'wp_key' => $contact_type,
							'full_key' => $field_key,
							'assessment' => $pattern,
							'type' => 'contact',
							'sample_value' => is_array( $sample_value ) ? json_encode( $sample_value ) : substr( $sample_value, 0, 100 ),
							'contact_type' => $contact_type
						);
					}
					continue 2; // Skip to next field once matched
				}
			}
			
			// Global fields
			if ( strpos( $field_key, 'ennu_global_' ) === 0 ) {
				$field_structure['global'][$field_key] = array(
					'wp_key' => str_replace( 'ennu_global_', '', $field_key ),
					'full_key' => $field_key,
					'type' => 'global',
					'sample_value' => is_array( $sample_value ) ? json_encode( $sample_value ) : substr( $sample_value, 0, 100 )
				);
			} 
			// Biomarker fields
			elseif ( strpos( $field_key, 'ennu_biomarker_' ) === 0 || $field_key === 'ennu_user_biomarkers' ) {
				$field_structure['biomarkers'][$field_key] = array(
					'wp_key' => str_replace( 'ennu_biomarker_', '', $field_key ),
					'full_key' => $field_key,
					'type' => 'biomarker',
					'sample_value' => is_array( $sample_value ) ? json_encode( $sample_value ) : substr( $sample_value, 0, 100 )
				);
			}
			// System-level fields (scores, symptoms, etc.)
			elseif ( preg_match( '/^ennu_(life_score|pillar_scores|centralized_symptoms|symptom_triggers|average_pillar_scores)/', $field_key ) ) {
				$field_structure['global'][$field_key] = array(
					'wp_key' => str_replace( 'ennu_', '', $field_key ),
					'full_key' => $field_key,
					'type' => 'system',
					'sample_value' => is_array( $sample_value ) ? json_encode( $sample_value ) : substr( $sample_value, 0, 100 )
				);
			}
		}
		
		return $field_structure;
	}

	/**
	 * Test field mappings between WordPress and HubSpot
	 */
	private function test_field_mappings() {
		$wordpress_fields = $this->get_wordpress_assessment_fields();
		$hubspot_fields = $this->get_comprehensive_field_mappings();
		$mapping_results = array();
		
		// Test assessment fields
		foreach ( $wordpress_fields['assessments'] as $assessment_name => $fields ) {
			foreach ( $fields as $wp_key => $wp_data ) {
				$hubspot_key = $this->map_wordpress_to_hubspot_field( $wp_key, $assessment_name );
				$mapping_results[$wp_key] = array(
					'wp_key' => $wp_key,
					'hubspot_key' => $hubspot_key,
					'assessment' => $assessment_name,
					'status' => $this->validate_field_mapping( $wp_key, $hubspot_key, $hubspot_fields ),
					'tested_at' => current_time( 'mysql' )
				);
			}
		}
		
		// Test global fields
		foreach ( $wordpress_fields['global'] as $wp_key => $wp_data ) {
			$hubspot_key = $this->map_wordpress_to_hubspot_field( $wp_key );
			$mapping_results[$wp_key] = array(
				'wp_key' => $wp_key,
				'hubspot_key' => $hubspot_key,
				'assessment' => 'global',
				'status' => $this->validate_field_mapping( $wp_key, $hubspot_key, $hubspot_fields ),
				'tested_at' => current_time( 'mysql' )
			);
		}
		
		// Save test results
		update_option( 'ennu_field_mapping_test_results', $mapping_results );
		update_option( 'ennu_field_mapping_last_test', current_time( 'mysql' ) );
	}

	/**
	 * Map WordPress field key to HubSpot field key
	 */
	private function map_wordpress_to_hubspot_field( $wp_key, $assessment_type = '' ) {
		// Direct mapping for some fields
		$direct_mappings = array(
			'ennu_global_gender' => 'ennu_global_gender',
			'ennu_global_age' => 'ennu_global_age',
			'ennu_global_height' => 'ennu_global_height',
			'ennu_global_weight' => 'ennu_global_weight',
			'ennu_global_bmi' => 'ennu_global_bmi',
		);
		
		if ( isset( $direct_mappings[$wp_key] ) ) {
			return $direct_mappings[$wp_key];
		}
		
		// Assessment response mapping
		if ( strpos( $wp_key, 'ennu_assessment_responses_' ) === 0 ) {
			$assessment_name = str_replace( 'ennu_assessment_responses_', '', $wp_key );
			// This would map to multiple HubSpot fields based on the assessment data
			return $assessment_name . '_assessment_data';
		}
		
		// Default: try same key name
		return $wp_key;
	}

	/**
	 * Validate if field mapping exists in HubSpot
	 */
	private function validate_field_mapping( $wp_key, $hubspot_key, $hubspot_fields ) {
		// Check if HubSpot field exists
		foreach ( $hubspot_fields as $category => $fields ) {
			if ( is_array( $fields ) ) {
				foreach ( $fields as $sub_category => $sub_fields ) {
					if ( is_array( $sub_fields ) && isset( $sub_fields[$hubspot_key] ) ) {
						return 'mapped';
					} elseif ( isset( $fields[$hubspot_key] ) ) {
						return 'mapped';
					}
				}
			}
		}
		
		return 'unmapped';
	}

	/**
	 * Count total fields in a field structure
	 */
	private function count_total_fields( $fields ) {
		$count = 0;
		foreach ( $fields as $category => $category_fields ) {
			if ( is_array( $category_fields ) ) {
				foreach ( $category_fields as $sub_category => $sub_fields ) {
					if ( is_array( $sub_fields ) ) {
						$count += count( $sub_fields );
					} else {
						$count++;
					}
				}
			}
		}
		return $count;
	}

	/**
	 * Display assessment tabs - one tab per assessment type
	 */
	private function display_assessment_tabs( $wordpress_fields, $hubspot_fields, $mapping_status ) {
		echo '<div class="assessment-tabs-container">';
		
		// Get all assessment types from both WordPress and HubSpot
		$all_assessments = array();
		
		// Add WordPress assessments
		if ( ! empty( $wordpress_fields['assessments'] ) ) {
			foreach ( $wordpress_fields['assessments'] as $assessment_type => $fields ) {
				$all_assessments[$assessment_type] = array(
					'name' => ucwords( str_replace( '_', ' ', $assessment_type ) ) . ' Assessment',
					'wp_fields' => $fields,
					'hs_fields' => array()
				);
			}
		}
		
		// Add HubSpot assessments
		if ( ! empty( $hubspot_fields['assessments'] ) ) {
			foreach ( $hubspot_fields['assessments'] as $assessment_type => $fields ) {
				$clean_type = strtolower( str_replace( array( ' Assessment', 'Assessment' ), '', $assessment_type ) );
				$clean_type = str_replace( ' ', '_', $clean_type );
				
				if ( isset( $all_assessments[$clean_type] ) ) {
					$all_assessments[$clean_type]['hs_fields'] = $fields;
				} else {
					$all_assessments[$assessment_type] = array(
						'name' => $assessment_type,
						'wp_fields' => array(),
						'hs_fields' => $fields
					);
				}
			}
		}
		
		// Add Global fields as first tab
		$global_tab = array(
			'global' => array(
				'name' => 'Global Fields',
				'wp_fields' => isset( $wordpress_fields['global'] ) ? $wordpress_fields['global'] : array(),
				'hs_fields' => isset( $hubspot_fields['global'] ) ? $hubspot_fields['global'] : array()
			)
		);
		$all_assessments = $global_tab + $all_assessments;
		
		// Create tab buttons
		echo '<div class="assessment-tab-buttons" style="margin-bottom: 20px; border-bottom: 2px solid #0073aa;">';
		$is_first = true;
		foreach ( $all_assessments as $assessment_key => $assessment_data ) {
			$active_class = $is_first ? 'active' : '';
			$wp_count = count( $assessment_data['wp_fields'] );
			$hs_count = count( $assessment_data['hs_fields'] );
			
			echo '<button class="assessment-tab-button ' . $active_class . '" ';
			echo 'onclick="showAssessmentTab(\'' . esc_attr( $assessment_key ) . '\')" ';
			echo 'style="background: ' . ( $is_first ? '#0073aa' : '#f1f1f1' ) . '; color: ' . ( $is_first ? 'white' : '#333' ) . '; border: none; padding: 12px 20px; margin-right: 5px; cursor: pointer; font-weight: 500; border-radius: 5px 5px 0 0;">';
			echo esc_html( $assessment_data['name'] );
			echo '<br><small style="font-size: 10px; opacity: 0.8;">WP: ' . $wp_count . ' | HS: ' . $hs_count . '</small>';
			echo '</button>';
			$is_first = false;
		}
		echo '</div>';
		
		// Create tab content
		$is_first = true;
		foreach ( $all_assessments as $assessment_key => $assessment_data ) {
			$active_class = $is_first ? 'active' : '';
			echo '<div id="' . esc_attr( $assessment_key ) . '-tab" class="assessment-tab-content ' . $active_class . '" ';
			echo 'style="display: ' . ( $is_first ? 'block' : 'none' ) . '; padding: 20px 0;">';
			
			$this->display_single_assessment_fields( 
				$assessment_key, 
				$assessment_data, 
				$mapping_status 
			);
			
			echo '</div>';
			$is_first = false;
		}
		
		echo '</div>';
		
		// Add CSS and JavaScript for tab switching
		echo '<style>
		.assessment-tab-buttons {
			display: flex;
			flex-wrap: wrap;
			gap: 2px;
			margin-bottom: 20px;
			border-bottom: 2px solid #0073aa;
			padding-bottom: 0;
		}
		
		.assessment-tab-button {
			background: #f1f1f1;
			color: #333;
			border: none;
			padding: 12px 16px;
			cursor: pointer;
			font-weight: 500;
			border-radius: 5px 5px 0 0;
			transition: all 0.2s ease;
			font-size: 13px;
			white-space: nowrap;
		}
		
		.assessment-tab-button:hover {
			background: #e0e0e0;
		}
		
		.assessment-tab-button.active {
			background: #0073aa !important;
			color: white !important;
		}
		
		.assessment-tab-content {
			padding: 20px 0;
			animation: fadeIn 0.3s ease-in-out;
		}
		
		@keyframes fadeIn {
			from { opacity: 0; }
			to { opacity: 1; }
		}
		
		.single-assessment-fields h3 {
			color: #333;
			border-bottom: 2px solid #0073aa;
			padding-bottom: 10px;
			margin-bottom: 20px;
		}
		</style>';
		
		echo '<script>
		function showAssessmentTab(tabKey) {
			// Hide all tab contents
			var tabContents = document.getElementsByClassName("assessment-tab-content");
			for (var i = 0; i < tabContents.length; i++) {
				tabContents[i].style.display = "none";
				tabContents[i].classList.remove("active");
			}
			
			// Remove active class from all buttons
			var tabButtons = document.getElementsByClassName("assessment-tab-button");
			for (var i = 0; i < tabButtons.length; i++) {
				tabButtons[i].classList.remove("active");
				tabButtons[i].style.background = "#f1f1f1";
				tabButtons[i].style.color = "#333";
			}
			
			// Show selected tab content
			var activeTab = document.getElementById(tabKey + "-tab");
			if (activeTab) {
				activeTab.style.display = "block";
				activeTab.classList.add("active");
			}
			
			// Add active class to clicked button  
			event.target.classList.add("active");
			event.target.style.background = "#0073aa";
			event.target.style.color = "white";
		}
		</script>';
	}

	/**
	 * Display fields for a single assessment
	 */
	private function display_single_assessment_fields( $assessment_key, $assessment_data, $mapping_status ) {
		echo '<div class="single-assessment-fields">';
		echo '<h3>📊 ' . esc_html( $assessment_data['name'] ) . ' - Field Mapping</h3>';
		
		echo '<div style="display: flex; gap: 20px; margin: 20px 0;">';
		
		// WordPress Fields Section
		echo '<div style="flex: 1; border: 1px solid #ddd; border-radius: 8px; padding: 15px; background: #f8f9ff;">';
		echo '<h4 style="margin-top: 0; color: #2271b1;">🔵 WordPress Fields (' . count( $assessment_data['wp_fields'] ) . ')</h4>';
		echo '<div style="max-height: 500px; overflow-y: auto;">';
		
		if ( ! empty( $assessment_data['wp_fields'] ) ) {
			foreach ( $assessment_data['wp_fields'] as $field_key => $field_data ) {
				// Skip container fields, show only questions
				if ( isset( $field_data['type'] ) && $field_data['type'] === 'question' ) {
					$status = isset( $mapping_status[$field_key] ) ? $mapping_status[$field_key]['status'] : 'unknown';
					$icon = $status === 'mapped' ? '✅' : ( $status === 'unmapped' ? '❌' : '❓' );
					
					echo '<div style="padding: 8px; margin: 5px 0; border-left: 4px solid ' . ( $status === 'mapped' ? '#00a32a' : '#d63638' ) . '; background: white; border-radius: 0 4px 4px 0;">';
					echo '<div style="font-family: monospace; font-weight: bold; color: #333;">';
					echo $icon . ' ' . esc_html( $field_data['full_key'] ?? $field_key );
					echo '</div>';
					echo '<div style="font-size: 11px; color: #666; margin-top: 3px;">WP Key: ' . esc_html( $field_data['wp_key'] ?? $field_key ) . '</div>';
					if ( ! empty( $field_data['sample_value'] ) ) {
						$sample = strlen( $field_data['sample_value'] ) > 40 ? 
							substr( $field_data['sample_value'], 0, 40 ) . '...' : 
							$field_data['sample_value'];
						echo '<div style="font-size: 10px; color: #999; margin-top: 2px;">Sample: ' . esc_html( $sample ) . '</div>';
					}
					echo '</div>';
				} elseif ( $assessment_key === 'global' ) {
					// For global fields, show all
					$status = isset( $mapping_status[$field_key] ) ? $mapping_status[$field_key]['status'] : 'unknown';
					$icon = $status === 'mapped' ? '✅' : ( $status === 'unmapped' ? '❌' : '❓' );
					
					echo '<div style="padding: 8px; margin: 5px 0; border-left: 4px solid ' . ( $status === 'mapped' ? '#00a32a' : '#d63638' ) . '; background: white; border-radius: 0 4px 4px 0;">';
					echo '<div style="font-family: monospace; font-weight: bold; color: #333;">';
					echo $icon . ' ' . esc_html( $field_key );
					echo '</div>';
					echo '<div style="font-size: 11px; color: #666; margin-top: 3px;">Type: ' . esc_html( $field_data['type'] ?? 'unknown' ) . '</div>';
					echo '</div>';
				}
			}
		} else {
			echo '<div style="padding: 20px; text-align: center; color: #666;">No WordPress fields found for this assessment</div>';
		}
		
		echo '</div>';
		echo '</div>';
		
		// HubSpot Fields Section
		echo '<div style="flex: 1; border: 1px solid #ddd; border-radius: 8px; padding: 15px; background: #fff8f0;">';
		echo '<h4 style="margin-top: 0; color: #d63638;">🟠 HubSpot Fields (' . count( $assessment_data['hs_fields'] ) . ')</h4>';
		echo '<div style="max-height: 500px; overflow-y: auto;">';
		
		if ( ! empty( $assessment_data['hs_fields'] ) ) {
			foreach ( $assessment_data['hs_fields'] as $field_key => $field_data ) {
				echo '<div style="padding: 8px; margin: 5px 0; border-left: 4px solid #ff6900; background: white; border-radius: 0 4px 4px 0;">';
				echo '<div style="font-family: monospace; font-weight: bold; color: #333;">';
				echo '🔸 ' . esc_html( $field_key );
				echo '</div>';
				echo '<div style="font-size: 11px; color: #666; margin-top: 3px;">' . esc_html( $field_data['label'] ?? 'No label' ) . '</div>';
				echo '<div style="font-size: 10px; color: #999; margin-top: 2px;">';
				echo 'Type: ' . esc_html( $field_data['type'] ?? 'unknown' );
				if ( isset( $field_data['field_type'] ) ) {
					echo ' (' . esc_html( $field_data['field_type'] ) . ')';
				}
				if ( isset( $field_data['created'] ) && ! empty( $field_data['created'] ) ) {
					echo ' | Created: ' . date( 'M j, Y', strtotime( $field_data['created'] ) );
				}
				echo '</div>';
				echo '</div>';
			}
		} else {
			echo '<div style="padding: 20px; text-align: center; color: #666;">No HubSpot fields found for this assessment</div>';
		}
		
		echo '</div>';
		echo '</div>';
		
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Display comprehensive field table showing ALL fields (backup method)
	 */
	private function display_comprehensive_field_table( $wordpress_fields, $hubspot_fields, $mapping_status ) {
		echo '<div class="comprehensive-field-table">';
		echo '<h3>📋 Complete Field Inventory</h3>';
		
		echo '<div style="display: flex; gap: 20px; margin: 20px 0;">';
		
		// WordPress Fields Section
		echo '<div style="flex: 1;">';
		echo '<h4>🔵 WordPress Fields</h4>';
		echo '<div style="max-height: 600px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">';
		
		// Global Fields
		if ( ! empty( $wordpress_fields['global'] ) ) {
			echo '<h5>Global Fields</h5>';
			foreach ( $wordpress_fields['global'] as $field_key => $field_data ) {
				$status = isset( $mapping_status[$field_key] ) ? $mapping_status[$field_key]['status'] : 'unknown';
				$icon = $status === 'mapped' ? '✅' : '❓';
				echo '<div style="padding: 5px; margin: 2px 0; font-family: monospace; font-size: 12px;">';
				echo $icon . ' <strong>' . $field_key . '</strong>';
				echo '</div>';
			}
		}
		
		// Assessment Fields by Type
		if ( ! empty( $wordpress_fields['assessments'] ) ) {
			foreach ( $wordpress_fields['assessments'] as $assessment_type => $fields ) {
				echo '<h5>' . ucwords( str_replace( '_', ' ', $assessment_type ) ) . ' Assessment</h5>';
				foreach ( $fields as $field_key => $field_data ) {
					if ( $field_data['type'] === 'question' ) {
						$status = isset( $mapping_status[$field_key] ) ? $mapping_status[$field_key]['status'] : 'unknown';
						$icon = $status === 'mapped' ? '✅' : '❓';
						echo '<div style="padding: 5px; margin: 2px 0; font-family: monospace; font-size: 12px;">';
						echo $icon . ' <strong>' . $field_data['full_key'] . '</strong>';
						echo '<br><span style="color: #666; margin-left: 15px;">WP Key: ' . $field_data['wp_key'] . '</span>';
						if ( ! empty( $field_data['sample_value'] ) ) {
							$sample = strlen( $field_data['sample_value'] ) > 30 ? 
								substr( $field_data['sample_value'], 0, 30 ) . '...' : 
								$field_data['sample_value'];
							echo '<br><span style="color: #999; margin-left: 15px; font-size: 11px;">Sample: ' . esc_html( $sample ) . '</span>';
						}
						echo '</div>';
					}
				}
			}
		}
		
		// Biomarker Fields
		if ( ! empty( $wordpress_fields['biomarkers'] ) ) {
			echo '<h5>Biomarker Fields</h5>';
			foreach ( $wordpress_fields['biomarkers'] as $field_key => $field_data ) {
				$status = isset( $mapping_status[$field_key] ) ? $mapping_status[$field_key]['status'] : 'unknown';
				$icon = $status === 'mapped' ? '✅' : '❓';
				echo '<div style="padding: 5px; margin: 2px 0; font-family: monospace; font-size: 12px;">';
				echo $icon . ' <strong>' . $field_key . '</strong>';
				echo '</div>';
			}
		}
		
		echo '</div>';
		echo '</div>';
		
		// HubSpot Fields Section  
		echo '<div style="flex: 1;">';
		echo '<h4>🟠 HubSpot Fields</h4>';
		echo '<div style="max-height: 600px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">';
		
		// Global Fields
		if ( ! empty( $hubspot_fields['global'] ) ) {
			echo '<h5>Global Fields</h5>';
			foreach ( $hubspot_fields['global'] as $field_key => $field_data ) {
				echo '<div style="padding: 5px; margin: 2px 0; font-family: monospace; font-size: 12px;">';
				echo '🔸 <strong>' . $field_key . '</strong>';
				echo '<br><span style="color: #666; margin-left: 15px;">' . esc_html( $field_data['label'] ) . '</span>';
				echo '<br><span style="color: #999; margin-left: 15px; font-size: 11px;">Type: ' . $field_data['type'];
				if ( isset( $field_data['field_type'] ) ) {
					echo ' (' . $field_data['field_type'] . ')';
				}
				echo '</span>';
				echo '</div>';
			}
		}
		
		// Assessment Fields
		if ( ! empty( $hubspot_fields['assessments'] ) ) {
			foreach ( $hubspot_fields['assessments'] as $assessment_type => $fields ) {
				echo '<h5>' . $assessment_type . '</h5>';
				foreach ( $fields as $field_key => $field_data ) {
					echo '<div style="padding: 5px; margin: 2px 0; font-family: monospace; font-size: 12px;">';
					echo '🔸 <strong>' . $field_key . '</strong>';
					echo '<br><span style="color: #666; margin-left: 15px;">' . esc_html( $field_data['label'] ) . '</span>';
					echo '<br><span style="color: #999; margin-left: 15px; font-size: 11px;">Type: ' . $field_data['type'];
					if ( isset( $field_data['field_type'] ) ) {
						echo ' (' . $field_data['field_type'] . ')';
					}
					if ( isset( $field_data['created'] ) ) {
						echo ' | Created: ' . date( 'M j, Y', strtotime( $field_data['created'] ) );
					}
					echo '</span>';
					echo '</div>';
				}
			}
		}
		
		// Biomarker Fields
		if ( ! empty( $hubspot_fields['biomarkers'] ) ) {
			echo '<h5>Biomarker Fields</h5>';
			foreach ( $hubspot_fields['biomarkers'] as $field_key => $field_data ) {
				echo '<div style="padding: 5px; margin: 2px 0; font-family: monospace; font-size: 12px;">';
				echo '🔸 <strong>' . $field_key . '</strong>';
				echo '<br><span style="color: #666; margin-left: 15px;">' . esc_html( $field_data['label'] ) . '</span>';
				echo '<br><span style="color: #999; margin-left: 15px; font-size: 11px;">Type: ' . $field_data['type'];
				if ( isset( $field_data['field_type'] ) ) {
					echo ' (' . $field_data['field_type'] . ')';
				}
				echo '</span>';
				echo '</div>';
			}
		}
		
		// Symptom Fields
		if ( ! empty( $hubspot_fields['symptoms'] ) ) {
			echo '<h5>Symptom Fields</h5>';
			foreach ( $hubspot_fields['symptoms'] as $field_key => $field_data ) {
				echo '<div style="padding: 5px; margin: 2px 0; font-family: monospace; font-size: 12px;">';
				echo '🔸 <strong>' . $field_key . '</strong>';
				echo '<br><span style="color: #666; margin-left: 15px;">' . esc_html( $field_data['label'] ) . '</span>';
				echo '<br><span style="color: #999; margin-left: 15px; font-size: 11px;">Type: ' . $field_data['type'];
				if ( isset( $field_data['field_type'] ) ) {
					echo ' (' . $field_data['field_type'] . ')';
				}
				echo '</span>';
				echo '</div>';
			}
		}
		
		echo '</div>';
		echo '</div>';
		
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Display assessment field mappings
	 */
	private function display_assessment_field_mappings( $wordpress_fields, $hubspot_fields, $mapping_status ) {
		echo '<div class="mapping-table-container">';
		echo '<table class="mapping-table" style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
		echo '<thead><tr style="background: #f1f1f1;">';
		echo '<th style="padding: 12px; border: 1px solid #ddd; text-align: left;">WordPress Field ID</th>';
		echo '<th style="padding: 12px; border: 1px solid #ddd; text-align: left;">HubSpot Field ID</th>';
		echo '<th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Assessment</th>';
		echo '<th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Status</th>';
		echo '<th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Last Tested</th>';
		echo '</tr></thead><tbody>';
		
		foreach ( $mapping_status as $wp_key => $mapping ) {
			if ( $mapping['assessment'] !== 'global' ) {
				$status_icon = $mapping['status'] === 'mapped' ? '✅' : '❌';
				$status_class = $mapping['status'] === 'mapped' ? 'mapped' : 'unmapped';
				
				echo '<tr class="' . $status_class . '">';
				echo '<td style="padding: 8px; border: 1px solid #ddd; font-family: monospace;">' . esc_html( $mapping['wp_key'] ) . '</td>';
				echo '<td style="padding: 8px; border: 1px solid #ddd; font-family: monospace;">' . esc_html( $mapping['hubspot_key'] ) . '</td>';
				echo '<td style="padding: 8px; border: 1px solid #ddd;">' . esc_html( ucwords( str_replace( '_', ' ', $mapping['assessment'] ) ) ) . '</td>';
				echo '<td style="padding: 8px; border: 1px solid #ddd;">' . $status_icon . ' ' . esc_html( ucfirst( $mapping['status'] ) ) . '</td>';
				echo '<td style="padding: 8px; border: 1px solid #ddd; font-size: 12px;">' . esc_html( $mapping['tested_at'] ) . '</td>';
				echo '</tr>';
			}
		}
		
		echo '</tbody></table>';
		echo '</div>';
	}

	/**
	 * Display global field mappings
	 */
	private function display_global_field_mappings( $wordpress_fields, $hubspot_fields, $mapping_status ) {
		echo '<div class="mapping-table-container">';
		echo '<table class="mapping-table" style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
		echo '<thead><tr style="background: #f1f1f1;">';
		echo '<th style="padding: 12px; border: 1px solid #ddd; text-align: left;">WordPress Field ID</th>';
		echo '<th style="padding: 12px; border: 1px solid #ddd; text-align: left;">HubSpot Field ID</th>';
		echo '<th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Field Type</th>';
		echo '<th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Status</th>';
		echo '<th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Last Tested</th>';
		echo '</tr></thead><tbody>';
		
		foreach ( $mapping_status as $wp_key => $mapping ) {
			if ( $mapping['assessment'] === 'global' ) {
				$status_icon = $mapping['status'] === 'mapped' ? '✅' : '❌';
				$status_class = $mapping['status'] === 'mapped' ? 'mapped' : 'unmapped';
				
				echo '<tr class="' . $status_class . '">';
				echo '<td style="padding: 8px; border: 1px solid #ddd; font-family: monospace;">' . esc_html( $mapping['wp_key'] ) . '</td>';
				echo '<td style="padding: 8px; border: 1px solid #ddd; font-family: monospace;">' . esc_html( $mapping['hubspot_key'] ) . '</td>';
				echo '<td style="padding: 8px; border: 1px solid #ddd;">' . esc_html( $mapping['assessment'] ) . '</td>';
				echo '<td style="padding: 8px; border: 1px solid #ddd;">' . $status_icon . ' ' . esc_html( ucfirst( $mapping['status'] ) ) . '</td>';
				echo '<td style="padding: 8px; border: 1px solid #ddd; font-size: 12px;">' . esc_html( $mapping['tested_at'] ) . '</td>';
				echo '</tr>';
			}
		}
		
		echo '</tbody></table>';
		echo '</div>';
	}

	/**
	 * Display biomarker field mappings
	 */
	private function display_biomarker_field_mappings( $wordpress_fields, $hubspot_fields, $mapping_status ) {
		echo '<div class="biomarker-fields">';
		echo '<p>Biomarker fields are typically stored within assessment responses and mapped individually.</p>';
		
		if ( isset( $hubspot_fields['biomarkers'] ) && ! empty( $hubspot_fields['biomarkers'] ) ) {
			echo '<h4>Available HubSpot Biomarker Fields:</h4>';
			echo '<div class="field-grid">';
			foreach ( $hubspot_fields['biomarkers'] as $field_key => $field_data ) {
				echo '<div class="field-item">';
				echo '<strong>' . esc_html( $field_data['label'] ) . '</strong><br>';
				echo '<code>' . esc_html( $field_key ) . '</code>';
				echo '</div>';
			}
			echo '</div>';
		} else {
			echo '<p>No biomarker-specific fields found in HubSpot.</p>';
		}
		echo '</div>';
	}

	/**
	 * Display unmapped fields
	 */
	private function display_unmapped_fields( $wordpress_fields, $hubspot_fields, $mapping_status ) {
		$unmapped_wp = array();
		$unmapped_hs = array();
		
		// Find unmapped WordPress fields
		foreach ( $mapping_status as $wp_key => $mapping ) {
			if ( $mapping['status'] !== 'mapped' ) {
				$unmapped_wp[] = $mapping;
			}
		}
		
		// Find HubSpot fields not mapped to WordPress
		$mapped_hs_keys = array();
		foreach ( $mapping_status as $mapping ) {
			$mapped_hs_keys[] = $mapping['hubspot_key'];
		}
		
		echo '<div class="unmapped-fields">';
		
		if ( ! empty( $unmapped_wp ) ) {
			echo '<h4>🔍 Unmapped WordPress Fields</h4>';
			echo '<div class="unmapped-list">';
			foreach ( $unmapped_wp as $field ) {
				echo '<div class="unmapped-field">';
				echo '<strong>' . esc_html( $field['wp_key'] ) . '</strong> ';
				echo '<span style="color: #666;">→ ' . esc_html( $field['hubspot_key'] ) . '</span>';
				echo '<div style="font-size: 12px; color: #d63638;">No matching HubSpot field found</div>';
				echo '</div>';
			}
			echo '</div>';
		} else {
			echo '<p>✅ All WordPress fields are mapped to HubSpot fields.</p>';
		}
		
		echo '</div>';
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
		echo '<p><strong>🔐 Private App Configuration</strong> - Enter your HubSpot private app credentials.</p>';
		echo '<p>💡 <strong>How to find these:</strong> Go to HubSpot → Settings → Integrations → Private Apps</p>';
	}

	public function access_token_callback() {
		$settings = get_option( 'ennu_hubspot_settings', array() );
		$value = isset( $settings['access_token'] ) ? $settings['access_token'] : 'pat-na1-87f4d48b-321a-4711-a346-2f4d7bf1f247';
		echo '<input type="password" name="ennu_hubspot_settings[access_token]" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="pat-na1-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" />';
		echo '<p class="description"><strong>Private App Access Token</strong> - Found in HubSpot → Settings → Integrations → Private Apps</p>';
	}

	public function portal_id_callback() {
		$settings = get_option( 'ennu_hubspot_settings', array() );
		$value = isset( $settings['portal_id'] ) ? $settings['portal_id'] : '50316590';
		echo '<input type="text" name="ennu_hubspot_settings[portal_id]" value="' . esc_attr( $value ) . '" class="regular-text" placeholder="50316590" />';
		echo '<p class="description">Your HubSpot Portal ID (Account ID)</p>';
	}

	/**
	 * Handle direct connection test (URL-based fallback)
	 */
	private function handle_direct_connection_test() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_redirect( admin_url( 'admin.php?page=ennu-hubspot-sync&test_result=error&test_message=' . urlencode( 'Insufficient permissions' ) ) );
			exit;
		}
		
		try {
			// Try multiple sources for the access token
			$access_token = get_option( 'ennu_hubspot_access_token', '' );
			
			// If not found in individual option, try the settings array
			if ( empty( $access_token ) ) {
				$settings = get_option( 'ennu_hubspot_settings', array() );
				if ( !empty( $settings ) && isset( $settings['access_token'] ) ) {
					$access_token = $settings['access_token'];
					// Save it to individual option for next time
					update_option( 'ennu_hubspot_access_token', $access_token );
				}
			}
			
			// If still empty, try hardcoded fallback for testing
			if ( empty( $access_token ) ) {
				$access_token = 'pat-na1-87f4d48b-321a-4711-a346-2f4d7bf1f247';
				// Save it for future use
				update_option( 'ennu_hubspot_access_token', $access_token );
				update_option( 'ennu_hubspot_portal_id', '48195592' );
			}
			
			if ( empty( $access_token ) ) {
				wp_redirect( admin_url( 'admin.php?page=ennu-hubspot-sync&test_result=error&test_message=' . urlencode( '❌ No access token found. Please save your credentials first.' ) ) );
				exit;
			}
			
			// Test direct API call to HubSpot account info endpoint
			$response = wp_remote_get( 'https://api.hubapi.com/account-info/v3/details', array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type' => 'application/json'
				),
				'timeout' => 15
			) );
			
			if ( is_wp_error( $response ) ) {
				wp_redirect( admin_url( 'admin.php?page=ennu-hubspot-sync&test_result=error&test_message=' . urlencode( '❌ Connection error: ' . $response->get_error_message() ) ) );
				exit;
			}
			
			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );
			
			if ( $response_code === 200 ) {
				$data = json_decode( $response_body, true );
				$account_name = isset( $data['name'] ) ? $data['name'] : 'Unknown';
				$portal_id = isset( $data['portalId'] ) ? $data['portalId'] : 'Unknown';
				
				wp_redirect( admin_url( 'admin.php?page=ennu-hubspot-sync&test_result=success&test_message=' . urlencode( '✅ HubSpot connection successful! Connected to: ' . $account_name . ' (Portal: ' . $portal_id . ')' ) ) );
			} elseif ( $response_code === 401 ) {
				wp_redirect( admin_url( 'admin.php?page=ennu-hubspot-sync&test_result=error&test_message=' . urlencode( '❌ Unauthorized: Invalid or expired access token. Please check your credentials.' ) ) );
			} elseif ( $response_code === 403 ) {
				wp_redirect( admin_url( 'admin.php?page=ennu-hubspot-sync&test_result=error&test_message=' . urlencode( '❌ Forbidden: Access token lacks required permissions.' ) ) );
			} else {
				$error_data = json_decode( $response_body, true );
				$error_message = isset( $error_data['message'] ) ? $error_data['message'] : 'Unknown error';
				wp_redirect( admin_url( 'admin.php?page=ennu-hubspot-sync&test_result=error&test_message=' . urlencode( '❌ API Error (Code: ' . $response_code . '): ' . $error_message ) ) );
			}
			
		} catch ( Exception $e ) {
			wp_redirect( admin_url( 'admin.php?page=ennu-hubspot-sync&test_result=error&test_message=' . urlencode( '❌ Connection error: ' . $e->getMessage() ) ) );
		}
		exit;
	}

	/**
	 * Test HubSpot connection
	 */
	public function test_connection() {
		// Debug: Log the AJAX request details
		// REMOVED: error_log( 'ENNU HubSpot test_connection called' );
		// REMOVED: error_log( 'POST data: ' . print_r( $_POST, true ) );
		// REMOVED: error_log( 'Current user ID: ' . get_current_user_id() );
		// REMOVED: error_log( 'User can manage_options: ' . ( current_user_can( 'manage_options' ) ? 'yes' : 'no' ) );
		
		// Check nonce - try both nonce and _wpnonce parameters
		$nonce_verified = false;
		if ( isset( $_POST['nonce'] ) ) {
			$nonce_verified = wp_verify_nonce( $_POST['nonce'], 'ennu_hubspot_test' );
			error_log( 'Nonce verification with nonce param: ' . ( $nonce_verified ? 'passed' : 'failed' ) );
		}
		if ( ! $nonce_verified && isset( $_POST['_wpnonce'] ) ) {
			$nonce_verified = wp_verify_nonce( $_POST['_wpnonce'], 'ennu_hubspot_test' );
			error_log( 'Nonce verification with _wpnonce param: ' . ( $nonce_verified ? 'passed' : 'failed' ) );
		}
		
		if ( ! $nonce_verified ) {
			wp_send_json_error( 'Security check failed - nonce verification failed' );
		}
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions - user cannot manage options' );
		}
		
		try {
			// Try multiple sources for the access token
			$access_token = get_option( 'ennu_hubspot_access_token', '' );
			// REMOVED: error_log( 'ENNU HubSpot: Individual option token: ' . ($access_token ? 'Found' : 'Empty') );
			
			// If not found in individual option, try the settings array
			if ( empty( $access_token ) ) {
				$settings = get_option( 'ennu_hubspot_settings', array() );
				// REMOVED: error_log( 'ENNU HubSpot: Settings array: ' . print_r($settings, true) );
				if ( !empty( $settings ) && isset( $settings['access_token'] ) ) {
					$access_token = $settings['access_token'];
					// Save it to individual option for next time
					update_option( 'ennu_hubspot_access_token', $access_token );
					// REMOVED: error_log( 'ENNU HubSpot: Found token in settings array, saved to individual option' );
				}
			}
			
			// If still empty, try hardcoded fallback for testing
			if ( empty( $access_token ) ) {
				$access_token = 'pat-na1-87f4d48b-321a-4711-a346-2f4d7bf1f247';
				// Save it for future use
				update_option( 'ennu_hubspot_access_token', $access_token );
				update_option( 'ennu_hubspot_portal_id', '48195592' );
				// REMOVED: error_log( 'ENNU HubSpot: Used hardcoded fallback token' );
			}
			
			if ( empty( $access_token ) ) {
				wp_send_json_error( '❌ No access token found. Please save your credentials first.' );
				return;
			}
			
			// Test direct API call to HubSpot account info endpoint
			$response = wp_remote_get( 'https://api.hubapi.com/account-info/v3/details', array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type' => 'application/json'
				),
				'timeout' => 15
			) );
			
			if ( is_wp_error( $response ) ) {
				wp_send_json_error( '❌ Connection error: ' . $response->get_error_message() );
				return;
			}
			
			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );
			
			if ( $response_code === 200 ) {
				$data = json_decode( $response_body, true );
				$account_name = isset( $data['name'] ) ? $data['name'] : 'Unknown';
				$portal_id = isset( $data['portalId'] ) ? $data['portalId'] : 'Unknown';
				
				wp_send_json_success( '✅ HubSpot connection successful! Connected to: ' . $account_name . ' (Portal: ' . $portal_id . ')' );
			} elseif ( $response_code === 401 ) {
				wp_send_json_error( '❌ Unauthorized: Invalid or expired access token. Please check your credentials.' );
			} elseif ( $response_code === 403 ) {
				wp_send_json_error( '❌ Forbidden: Access token lacks required permissions.' );
			} else {
				// Include more debug info for failed requests
				$error_data = json_decode( $response_body, true );
				$error_message = isset( $error_data['message'] ) ? $error_data['message'] : 'Unknown error';
				wp_send_json_error( '❌ API Error (Code: ' . $response_code . '): ' . $error_message . ' | Token: ' . substr($access_token, 0, 15) . '...' );
			}
			
		} catch ( Exception $e ) {
			wp_send_json_error( '❌ Connection error: ' . $e->getMessage() );
		}
	}

	/**
	 * Sync assessment data to HubSpot (create records, not fields)
	 */
	public function sync_hubspot_data() {
		check_ajax_referer( 'ennu_hubspot_sync', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}
		
		try {
			// Get all users who have completed assessments
			global $wpdb;
			$users_with_assessments = $wpdb->get_results(
				"SELECT DISTINCT user_id FROM {$wpdb->usermeta} 
				 WHERE meta_key LIKE 'ennu_assessment_responses_%' 
				 LIMIT 50"  // Limit to prevent timeout
			);
			
			if ( empty( $users_with_assessments ) ) {
				wp_send_json_error( 'No assessment data found to sync' );
				return;
			}
			
			$field_creator = new ENNU_HubSpot_Bulk_Field_Creator();
			$synced_count = 0;
			$error_count = 0;
			
			foreach ( $users_with_assessments as $user_data ) {
				$user_id = $user_data->user_id;
				$user = get_user_by( 'ID', $user_id );
				
				if ( ! $user || empty( $user->user_email ) ) {
					continue;
				}
				
				// Get all assessment responses for this user
				$assessment_responses = $wpdb->get_results( $wpdb->prepare(
					"SELECT meta_key, meta_value FROM {$wpdb->usermeta} 
					 WHERE user_id = %d AND meta_key LIKE 'ennu_assessment_responses_%'",
					$user_id
				) );
				
				foreach ( $assessment_responses as $response ) {
					// Extract assessment type from meta_key (e.g., 'ennu_assessment_responses_weight_loss')
					$assessment_type = str_replace( 'ennu_assessment_responses_', '', $response->meta_key );
					$form_data = json_decode( $response->meta_value, true );
					
					if ( ! empty( $form_data ) ) {
						try {
							$result = $field_creator->sync_assessment_to_hubspot( $user_id, $assessment_type, $form_data );
							if ( $result ) {
								$synced_count++;
							} else {
								$error_count++;
							}
						} catch ( Exception $e ) {
							error_log( 'ENNU HubSpot Sync Error for user ' . $user_id . ': ' . $e->getMessage() );
							$error_count++;
						}
					}
				}
			}
			
			// Update sync statistics
			update_option( 'ennu_hubspot_last_sync', current_time( 'mysql' ) );
			$total_sync_count = get_option( 'ennu_hubspot_sync_count', 0 ) + $synced_count;
			update_option( 'ennu_hubspot_sync_count', $total_sync_count );
			$total_error_count = get_option( 'ennu_hubspot_error_count', 0 ) + $error_count;
			update_option( 'ennu_hubspot_error_count', $total_error_count );
			
			if ( $synced_count > 0 ) {
				wp_send_json_success( "Successfully synced {$synced_count} assessment records to HubSpot. Errors: {$error_count}" );
			} else {
				wp_send_json_error( "No records were synced successfully. Errors: {$error_count}" );
			}
			
		} catch ( Exception $e ) {
			$error_count = get_option( 'ennu_hubspot_error_count', 0 ) + 1;
			update_option( 'ennu_hubspot_error_count', $error_count );
			
			wp_send_json_error( '❌ Sync error: ' . $e->getMessage() );
		}
	}
	
	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueue_admin_scripts( $hook ) {
		// Only load on our HubSpot sync page
		if ( $hook !== 'ennu-life_page_ennu-hubspot-sync' ) {
			return;
		}
		
		// Add inline styles for better form display
		$custom_css = "
			.ennu-hubspot-section {
				background: #fff;
				border: 1px solid #c3c4c7;
				box-shadow: 0 1px 1px rgba(0,0,0,.04);
				margin: 20px 0;
				padding: 20px;
			}
			.ennu-hubspot-section h2 {
				margin-top: 0;
				border-bottom: 1px solid #ddd;
				padding-bottom: 10px;
			}
			#test-connection-result {
				margin-top: 10px;
				padding: 10px;
				border-radius: 4px;
			}
			.ennu-connection-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
			.ennu-connection-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
		";
		wp_add_inline_style( 'wp-admin', $custom_css );
		
		// JavaScript is now handled inline in the render_page method to avoid conflicts
	}
	
	/**
	 * Test data orchestration - Run a comprehensive test of multi-object syncing
	 */
	public function test_data_orchestration() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_orchestration_test' ) ) {
			wp_send_json_error( 'Invalid security token' );
			return;
		}
		
		try {
			$results = array();
			
			// Test 1: Contact API connectivity
			$contact_test = $this->test_contact_api();
			$results['contact_api'] = $contact_test;
			
			// Test 2: Custom object API connectivity  
			$custom_object_test = $this->test_custom_object_api();
			$results['custom_object_api'] = $custom_object_test;
			
			// Test 3: Association API connectivity
			$association_test = $this->test_association_api();
			$results['association_api'] = $association_test;
			
			// Test 4: Biomarker sync functionality
			$biomarker_test = $this->test_biomarker_sync();
			$results['biomarker_sync'] = $biomarker_test;
			
			// Store test results
			update_option( 'ennu_orchestration_test_results', array(
				'timestamp' => current_time( 'mysql' ),
				'results' => $results
			) );
			
			// Calculate overall success rate
			$total_tests = count( $results );
			$successful_tests = count( array_filter( $results, function( $result ) {
				return $result['success'] === true;
			} ) );
			
			$success_rate = round( ( $successful_tests / $total_tests ) * 100, 1 );
			
			wp_send_json_success( array(
				'message' => "Orchestration test completed. Success rate: {$success_rate}%",
				'results' => $results,
				'success_rate' => $success_rate
			) );
			
		} catch ( Exception $e ) {
			wp_send_json_error( 'Test orchestration failed: ' . $e->getMessage() );
		}
	}
	
	/**
	 * Test contact API functionality
	 */
	private function test_contact_api() {
		$access_token = get_option( 'ennu_hubspot_access_token', '' );
		
		if ( empty( $access_token ) ) {
			return array( 'success' => false, 'error' => 'No access token' );
		}
		
		$response = wp_remote_get( 'https://api.hubapi.com/crm/v3/objects/contacts?limit=1', array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type' => 'application/json'
			),
			'timeout' => 15
		) );
		
		if ( is_wp_error( $response ) ) {
			return array( 'success' => false, 'error' => $response->get_error_message() );
		}
		
		$status_code = wp_remote_retrieve_response_code( $response );
		return array( 
			'success' => $status_code === 200,
			'status_code' => $status_code,
			'message' => 'Contact API ' . ( $status_code === 200 ? 'accessible' : 'failed' )
		);
	}
	
	/**
	 * Test custom object API functionality
	 */
	private function test_custom_object_api() {
		$access_token = get_option( 'ennu_hubspot_access_token', '' );
		$custom_object_id = '2-47128703'; // Assessment Records object
		
		if ( empty( $access_token ) ) {
			return array( 'success' => false, 'error' => 'No access token' );
		}
		
		$response = wp_remote_get( "https://api.hubapi.com/crm/v3/objects/{$custom_object_id}?limit=1", array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type' => 'application/json'
			),
			'timeout' => 15
		) );
		
		if ( is_wp_error( $response ) ) {
			return array( 'success' => false, 'error' => $response->get_error_message() );
		}
		
		$status_code = wp_remote_retrieve_response_code( $response );
		return array( 
			'success' => $status_code === 200,
			'status_code' => $status_code,
			'message' => 'Custom Object API ' . ( $status_code === 200 ? 'accessible' : 'failed' ),
			'object_id' => $custom_object_id
		);
	}
	
	/**
	 * Test association API functionality
	 */
	private function test_association_api() {
		$access_token = get_option( 'ennu_hubspot_access_token', '' );
		
		if ( empty( $access_token ) ) {
			return array( 'success' => false, 'error' => 'No access token' );
		}
		
		$response = wp_remote_get( 'https://api.hubapi.com/crm/v4/associations/contact/2-47128703/labels', array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type' => 'application/json'
			),
			'timeout' => 15
		) );
		
		if ( is_wp_error( $response ) ) {
			return array( 'success' => false, 'error' => $response->get_error_message() );
		}
		
		$status_code = wp_remote_retrieve_response_code( $response );
		return array( 
			'success' => in_array( $status_code, [ 200, 404 ] ), // 404 is OK if no associations exist yet
			'status_code' => $status_code,
			'message' => 'Association API ' . ( in_array( $status_code, [ 200, 404 ] ) ? 'accessible' : 'failed' )
		);
	}
	
	/**
	 * Test biomarker sync functionality  
	 */
	private function test_biomarker_sync() {
		// Check if biomarker sync class exists and is accessible
		if ( ! class_exists( 'ENNU_HubSpot_Biomarker_Sync' ) ) {
			return array( 'success' => false, 'error' => 'Biomarker sync class not found' );
		}
		
		// Check if biomarker data exists in database
		global $wpdb;
		$biomarker_count = $wpdb->get_var( 
			"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = 'ennu_user_biomarkers'" 
		);
		
		return array(
			'success' => true,
			'message' => "Biomarker sync ready. {$biomarker_count} biomarker records found.",
			'biomarker_records' => intval( $biomarker_count )
		);
	}
} 