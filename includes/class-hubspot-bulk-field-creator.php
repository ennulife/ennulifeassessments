<?php
/**
 * HubSpot Bulk Field Creator
 *
 * @package   ENNU Life Assessments
 * @copyright Copyright (c) 2024, ENNU Life Team
 * @license   GPL-3.0+
 * @since     64.6.80
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HubSpot Bulk Field Creator Class
 *
 * Provides comprehensive functionality for bulk creating custom object fields in HubSpot
 * with support for various field types, validation, and error handling.
 *
 * @since 64.6.80
 */
class ENNU_HubSpot_Bulk_Field_Creator {

	/**
	 * HubSpot API base URL
	 *
	 * @var string
	 */
	private $api_base_url = 'https://api.hubapi.com';

	/**
	 * API authentication parameters
	 *
	 * @var array
	 */
	private $api_params = array();

	/**
	 * Supported field types
	 *
	 * @var array
	 */
	private $supported_field_types = array(
		'string'      => array(
			'fieldTypes' => array( 'text', 'textarea', 'text' ),
			'validation' => array( 'maxLength' => 255 ),
		),
		'number'      => array(
			'fieldTypes' => array( 'number' ),
			'validation' => array( 'minValue', 'maxValue' ),
		),
		'date'        => array(
			'fieldTypes' => array( 'date' ),
			'validation' => array(),
		),
		'enumeration' => array(
			'fieldTypes' => array( 'select', 'radio', 'checkbox' ),
			'validation' => array( 'options' => 'required' ),
		),
		'boolean'     => array(
			'fieldTypes' => array( 'booleancheckbox' ),
			'validation' => array(),
		),
	);

	/**
	 * Constructor
	 *
	 * @since 64.2.1
	 */
	public function __construct() {
		// Prevent duplicate initialization
		static $initialized = false;
		if ( $initialized ) {
			return;
		}
		$initialized = true;
		
		// Set API base URL
		$this->api_base_url = 'https://api.hubapi.com';
		
		// Register AJAX handlers with improved error handling
		$this->register_ajax_handlers();
		
		$this->init_api_params();
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ), 20 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		
		// Add compatibility fixes for browser security issues
		$this->add_compatibility_fixes();
		
		// Test API connection on load (for debugging) with retry mechanism
		if ( isset( $_GET['test_hubspot_api'] ) && current_user_can( 'manage_options' ) ) {
			error_log( "ENNU HubSpot: Testing API connection on load..." );
			$result = $this->test_api_connection_with_retry();
			error_log( "ENNU HubSpot: Test result: " . json_encode( $result ) );
		}
		
		// Add public test endpoint for debugging
		if ( isset( $_GET['test_hubspot_page'] ) ) {
			add_action( 'init', array( $this, 'test_admin_page_content' ) );
		}
	}

	/**
	 * Register AJAX handlers with improved error handling
	 *
	 * @since 64.6.81
	 */
	private function register_ajax_handlers() {
		$ajax_handlers = array(
			'ennu_get_custom_objects' => 'ajax_get_objects',
			'ennu_validate_schema' => 'ajax_validate_schema',
			'ennu_create_fields' => 'ajax_bulk_create_fields',
			'ennu_test_hubspot_api' => 'ajax_test_api',
			'ennu_import_assessment_fields' => 'ajax_import_assessment_fields',
			'ennu_get_assessments' => 'ajax_get_assessments',
			'ennu_preview_assessment_fields' => 'ajax_preview_assessment_fields',
			'ennu_create_assessment_fields' => 'ajax_create_assessment_fields',
			'ennu_save_sync_settings' => 'ajax_save_sync_settings',
			'ennu_retry_failed_syncs' => 'ajax_retry_failed_syncs',
			'ennu_test_api_connection' => 'ajax_test_api_connection',
			'ennu_preview_global_fields' => 'ajax_preview_global_fields',
			'ennu_create_global_fields' => 'ajax_create_global_fields',
			'ennu_delete_global_fields' => 'ajax_delete_global_fields',
			'ennu_preview_weight_loss_fields' => 'ajax_preview_weight_loss_fields',
			'ennu_create_weight_loss_fields' => 'ajax_create_weight_loss_fields',
			'ennu_delete_weight_loss_fields' => 'ajax_delete_weight_loss_fields',
			'ennu_debug_hubspot_fields' => 'ajax_debug_hubspot_fields',
			'ennu_get_field_statistics' => 'ajax_get_field_statistics',
			'ennu_test_field_statistics' => 'ajax_test_field_statistics',
			'ennu_clear_hubspot_cache' => 'ajax_clear_hubspot_cache',
			'ennu_test_api_with_retry' => 'ajax_test_api_with_retry',
			'ennu_validate_hubspot_connection' => 'ajax_validate_hubspot_connection',
			'ennu_comprehensive_preview' => 'ajax_comprehensive_preview',
			'ennu_create_custom_object_field_groups' => 'ajax_create_custom_object_field_groups',
			'ennu_create_contact_field_groups' => 'ajax_create_contact_field_groups',
		);

		foreach ( $ajax_handlers as $action => $method ) {
			if ( method_exists( $this, $method ) ) {
				add_action( 'wp_ajax_' . $action, array( $this, $method ) );
			} else {
				error_log( "ENNU HubSpot: Method {$method} not found for action {$action}" );
			}
		}
	}

	/**
	 * Add compatibility fixes for browser security and ad-blocker issues
	 *
	 * @since 64.6.81
	 */
	private function add_compatibility_fixes() {
		// Add headers to prevent caching issues
		add_action( 'admin_head', function() {
			if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'ennu-hubspot' ) !== false ) {
				echo '<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">';
				echo '<meta http-equiv="Pragma" content="no-cache">';
				echo '<meta http-equiv="Expires" content="0">';
			}
		});

		// Add JavaScript to handle ad-blocker and security issues
		add_action( 'admin_footer', function() {
			if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'ennu-hubspot' ) !== false ) {
				?>
				<script>
				jQuery(document).ready(function($) {
					// Handle potential ad-blocker issues
					window.addEventListener('error', function(e) {
						if (e.message.includes('hubspot') || e.message.includes('hubapi')) {
							console.warn('ENNU HubSpot: Potential ad-blocker or security issue detected');
							$('#hubspot-compatibility-warning').show();
						}
					});

					// Add retry mechanism for failed API calls
					window.ennuHubSpotRetry = function(fn, maxAttempts = 3) {
						return function(...args) {
							let attempts = 0;
							const attempt = () => {
								attempts++;
								return fn.apply(this, args).catch(error => {
									if (attempts < maxAttempts) {
										console.log(`ENNU HubSpot: Retry attempt ${attempts} for API call`);
										return new Promise(resolve => setTimeout(resolve, 1000 * attempts)).then(attempt);
									}
									throw error;
								});
							};
							return attempt();
						};
					};
				});
				</script>
				<div id="hubspot-compatibility-warning" style="display:none; background:#fff3cd; border:1px solid #ffeaa7; padding:15px; margin:10px 0; border-radius:4px;">
					<h4>⚠️ HubSpot Compatibility Warning</h4>
					<p>Potential browser security or ad-blocker issues detected. Please:</p>
					<ul>
						<li>Disable ad-blockers for this domain</li>
						<li>Check browser security settings</li>
						<li>Try a different browser if issues persist</li>
					</ul>
					<p><strong>Reference:</strong> <a href="https://community.hubspot.com/t5/Sales-Integrations/Hubspot-Wordpress-plugin-doesn-t-work/m-p/495512" target="_blank">HubSpot Community Solution</a></p>
				</div>
				<?php
			}
		});
	}

	/**
	 * Initialize API parameters
	 *
	 * @since 64.2.1
	 */
	private function init_api_params() {
		// Use the provided Private App Access Token
		$access_token = get_option( 'ennu_hubspot_access_token', '' );
		
		// Store the client secret for potential use
		$client_secret = get_option( 'ennu_hubspot_client_secret', '665d374d-f9d9-4fa5-96f3-e58ff203ba16' );
		
		if ( empty( $access_token ) ) {
			// Try to get from WP Fusion if available
			if ( function_exists( 'wp_fusion' ) && wp_fusion() ) {
				$crm = wp_fusion()->crm;
				if ( 'hubspot' === $crm->slug ) {
					$access_token = $crm->get_access_token();
				}
			}
		}

		// Ensure the access token has the correct format
		$auth_header = $access_token;
		if ( strpos( $access_token, 'Bearer ' ) !== 0 ) {
			$auth_header = 'Bearer ' . $access_token;
		}

		$this->api_params = array(
			'headers' => array(
				'Authorization' => $auth_header,
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			),
			'timeout' => 30,
		);
		
		// Log the API configuration for debugging
		error_log( 'ENNU HubSpot: API initialized with access token: ' . substr( $access_token, 0, 20 ) . '...' );
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @since 64.2.1
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'ennu-life_page_ennu-hubspot-bulk-field-creator' !== $hook ) {
			return;
		}
		
		// Enqueue the HubSpot JavaScript file
		wp_enqueue_script(
			'ennu-hubspot-bulk-field-creator',
			ENNU_LIFE_PLUGIN_URL . 'assets/js/hubspot-bulk-field-creator.js',
			array( 'jquery' ),
			ENNU_LIFE_VERSION,
			true
		);
		
		// Localize script with AJAX data
		wp_localize_script( 'ennu-hubspot-bulk-field-creator', 'ennu_hubspot_ajax', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ennu_hubspot_nonce' ),
		) );
	}

	/**
	 * Add admin menu
	 *
	 * @since 64.2.1
	 */
	public function add_admin_menu() {
		// ENABLED: Original HubSpot Bulk Field Creator
		error_log( 'ENNU HubSpot: Registering admin menu for HubSpot Bulk Field Creator' );
		add_submenu_page(
			'ennu-life',
			__( 'HubSpot Bulk Field Creator (Original)', 'ennulifeassessments' ),
			__( 'HubSpot Fields (Original)', 'ennulifeassessments' ),
			'edit_posts', // Changed to match parent menu capability
			'ennu-hubspot-bulk-field-creator',
			array( $this, 'admin_page' )
		);
		error_log( 'ENNU HubSpot: Admin menu registered successfully' );
	}

	/**
	 * Format results for display
	 *
	 * @since 64.2.1
	 * @param array $results Results array
	 * @return string Formatted results
	 */
	private function format_results( $results ) {
		$output = '';
		
		if ( ! empty( $results['success'] ) ) {
			$output .= '<div class="ennu-success-section">';
			$output .= '<h4>✅ Successfully Created Fields:</h4>';
			$output .= '<ul>';
			foreach ( $results['success'] as $success ) {
				$output .= '<li><strong>' . esc_html( $success['field'] ) . '</strong></li>';
			}
			$output .= '</ul>';
			$output .= '</div>';
		}
		
		if ( ! empty( $results['errors'] ) ) {
			$output .= '<div class="ennu-error-section">';
			$output .= '<h4>❌ Errors:</h4>';
			$output .= '<ul>';
			foreach ( $results['errors'] as $error ) {
				$output .= '<li><strong>' . esc_html( $error['field'] ) . '</strong>: ' . esc_html( $error['error'] ) . '</li>';
			}
			$output .= '</ul>';
			$output .= '</div>';
		}
		
		return $output;
	}





	/**
	 * Admin page content
	 */
	public function admin_page() {
		// Check if this is a test request
		if ( isset( $_GET['test_weight_loss'] ) && current_user_can( 'manage_options' ) ) {
			$this->run_weight_loss_test();
			return;
		}
		
		?>
		<div class="wrap">
			<h1>HubSpot Bulk Field Creator</h1>
			
			<!-- Field Statistics Dashboard -->
			<div class="ennu-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; margin-bottom: 20px;">
				<h3 style="color: white; margin: 0 0 15px 0;">📊 Field Statistics Dashboard</h3>
				<div class="ennu-section-content">
					<div id="field-stats-container" style="display: grid; grid-template-columns: 1fr; gap: 15px;">
						<!-- Stats will be loaded here via AJAX -->
						<div style="text-align: center; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 6px;">
							<div style="font-size: 24px; font-weight: bold;">🔄</div>
							<div>Loading Statistics...</div>
						</div>
					</div>
					<div style="margin-top: 15px; text-align: center;">
						<button type="button" id="refresh-all-stats" class="button button-primary" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white;">
							🔄 Refresh All Statistics
						</button>
						<button type="button" id="test-stats" class="button button-secondary" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; margin-left: 10px;">
							🧪 Test Statistics
						</button>
					</div>
				</div>
			</div>
			
			<!-- Comprehensive Preview Section -->
			<div class="ennu-section" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; margin-bottom: 20px;">
				<h3 style="color: white; margin: 0 0 15px 0;">🔍 Comprehensive HubSpot Preview</h3>
				<div class="ennu-section-content">
					<p style="color: white; margin-bottom: 15px;">Check the existence of custom objects, field groups, and fields in your HubSpot account before creating them.</p>
					
					<div class="ennu-actions" style="margin-bottom: 15px;">
						<button type="button" id="comprehensive-preview" class="button button-primary" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white;">
							🔍 Run Comprehensive Preview
						</button>
						<button type="button" id="clear-preview" class="button button-secondary" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; margin-left: 10px;">
							🗑️ Clear Preview
						</button>
					</div>
					
					<div class="ennu-actions" style="margin-bottom: 15px;">
						<button type="button" id="create-custom-object-groups" class="button button-primary" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white;">
							🏗️ Create Custom Object Field Groups
						</button>
						<button type="button" id="create-contact-groups" class="button button-primary" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white; margin-left: 10px;">
							👥 Create Contact Field Groups
						</button>
					</div>
					
					<div id="comprehensive-preview-results" style="display: none;">
						<!-- Preview results will be loaded here -->
					</div>
					
					<div id="comprehensive-preview-loading" style="display: none; text-align: center; padding: 20px;">
						<div style="font-size: 24px;">🔄</div>
						<div>Analyzing HubSpot objects, field groups, and fields...</div>
					</div>
				</div>
			</div>
			
			<div class="ennu-hubspot-container">
				<!-- Test Connection Section -->
				<div class="ennu-section">
					<h3>🔗 Test HubSpot Connection</h3>
					<div class="ennu-section-content">
						<button type="button" id="test-connection" class="button button-primary">Test API Connection</button>
						<div id="test-status" class="ennu-status"></div>
					</div>
				</div>

				<!-- Import Assessment Fields Section -->
				<div class="ennu-section">
					<h3>📋 Import Assessment Fields</h3>
					<div class="ennu-section-content">
						<p>Create HubSpot fields from your assessment questions. Choose an assessment to preview and create fields.</p>
						
						<div class="ennu-form-row">
							<label for="assessment-object-type">Object Type:</label>
							<select id="assessment-object-type" class="ennu-select">
								<option value="">Select Object Type</option>
								<option value="contacts">Contacts</option>
								<option value="companies">Companies</option>
								<option value="deals">Deals</option>
							</select>
						</div>
						
						<div class="ennu-form-row">
							<label for="assessment-select">Select Assessment:</label>
							<select id="assessment-select" class="ennu-select">
								<option value="">Choose an assessment...</option>
							</select>
						</div>
						
						<div class="ennu-actions">
							<button type="button" id="preview-assessment-fields" class="button button-secondary">Preview Fields</button>
							<button type="button" id="import-assessment-fields" class="button button-primary" style="display:none;">Create Fields in HubSpot</button>
						</div>
						
						<div id="field-preview" class="ennu-field-preview"></div>
						<div id="import-status" class="ennu-status"></div>
					</div>
				</div>

				<!-- Real-time Sync Status Section -->
				<div class="ennu-section">
					<h3>🔄 Real-time Sync Status</h3>
					<div class="ennu-section-content">
						<?php
						$sync_enabled = get_option( 'ennu_hubspot_sync_enabled', true );
						$failed_syncs = get_option( 'ennu_hubspot_failed_syncs', array() );
						?>
						
						<div class="ennu-sync-status">
							<p><strong>Auto-Sync:</strong> 
								<span style="color: <?php echo $sync_enabled ? 'green' : 'red'; ?>;">
									<?php echo $sync_enabled ? '✅ Enabled' : '❌ Disabled'; ?>
								</span>
							</p>
							
							<p><strong>Failed Syncs:</strong> 
								<span style="color: <?php echo empty( $failed_syncs ) ? 'green' : 'orange'; ?>;">
									<?php echo count( $failed_syncs ); ?>
								</span>
							</p>
							
							<?php if ( ! empty( $failed_syncs ) ): ?>
								<div class="ennu-failed-syncs">
									<h4>Recent Failed Syncs:</h4>
									<ul>
										<?php foreach ( array_slice( $failed_syncs, -5 ) as $sync ): ?>
											<li>
												User ID: <?php echo $sync['user_id']; ?> | 
												Assessment: <?php echo $sync['assessment_type']; ?> | 
												Error: <?php echo $sync['error']; ?>
											</li>
										<?php endforeach; ?>
									</ul>
									<button type="button" id="retry-failed-syncs" class="button button-secondary">Retry Failed Syncs</button>
								</div>
							<?php endif; ?>
						</div>
						
						<div class="ennu-sync-controls">
							<label>
								<input type="checkbox" id="sync-enabled" <?php checked( $sync_enabled ); ?>>
								Enable real-time sync
							</label>
							<button type="button" id="save-sync-settings" class="button button-primary">Save Settings</button>
						</div>
					</div>
				</div>

				<!-- Global Shared Fields Section -->
				<div class="ennu-section">
					<h3>🌍 Global Shared Fields</h3>
					<div class="ennu-section-content">
						<p><strong>Global Shared Fields (Used Across ALL Assessments)</strong></p>
						<p>This will create 6 global shared fields that are updated across all assessments:</p>
						<ul>
							<li><strong>6 Contact Properties:</strong> Gender, Date of Birth, Height, Weight, BMI, Age</li>
							<li><strong>Shared Across All 11 Assessments:</strong> These fields are updated whenever any assessment is completed</li>
						</ul>
						
						<div class="ennu-actions">
							<button type="button" id="preview-global-fields" class="button button-secondary">
								👁️ Preview Global Fields
							</button>
							<button type="button" id="create-global-fields" class="button button-primary" style="display:none;">
								🚀 Create Global Fields (6 total)
							</button>
							<button type="button" id="delete-global-fields" class="button button-secondary">🗑️ Delete Global Fields</button>
						</div>
						
						<div id="global-fields-preview" class="ennu-field-preview"></div>
						<div id="global-fields-creation-status" class="ennu-status"></div>
					</div>
				</div>

				<!-- Weight Loss Assessment Fields Section -->
				<div class="ennu-section">
					<h3>⚖️ Weight Loss Assessment Fields</h3>
					<div class="ennu-section-content">
						<p><strong>Weight Loss Assessment Field Creation</strong></p>
						<p>This will create all 22 fields for the Weight Loss assessment:</p>
						<ul>
							<li><strong>11 Contact Properties:</strong> Assessment-specific questions and scores</li>
							<li><strong>11 Custom Object Fields:</strong> Historical tracking records in the basic_assessments object</li>
						</ul>
						
						<div class="ennu-actions">
							<button type="button" id="preview-weight-loss-fields" class="button button-secondary">
								👁️ Preview Weight Loss Fields
							</button>
							<button type="button" id="create-weight-loss-fields" class="button button-primary" style="display:none;">
								🚀 Create Weight Loss Fields (22 total)
							</button>
							<button type="button" id="delete-weight-loss-fields" class="button button-secondary">🗑️ Delete Weight Loss Fields</button>
							<button type="button" id="refresh-field-cache" class="button button-secondary">🔄 Refresh Field Cache</button>
						</div>
						
						<div id="weight-loss-preview" class="ennu-field-preview"></div>
						<div id="weight-loss-creation-status" class="ennu-status"></div>
					</div>
				</div>

				<!-- Hormone Assessment Fields Section -->
				<div class="ennu-section">
					<h3>🔄 Hormone Assessment Fields</h3>
					<div class="ennu-section-content">
						<p><strong>Hormone Assessment Field Creation</strong></p>
						<p>This will create all fields for the Hormone assessment:</p>
						<ul>
							<li><strong>Contact Properties:</strong> Assessment-specific questions and scores</li>
							<li><strong>Custom Object Fields:</strong> Historical tracking records in the basic_assessments object</li>
						</ul>
						
						<div class="ennu-actions">
							<button type="button" id="preview-hormone-fields" class="button button-secondary">
								👁️ Preview Hormone Fields
							</button>
							<button type="button" id="create-hormone-fields" class="button button-primary" style="display:none;">
								🚀 Create Hormone Fields
							</button>
							<button type="button" id="delete-hormone-fields" class="button button-secondary">🗑️ Delete Hormone Fields</button>
							<button type="button" id="refresh-hormone-field-cache" class="button button-secondary">🔄 Refresh Field Cache</button>
						</div>
						
						<div id="hormone-preview" class="ennu-field-preview"></div>
						<div id="hormone-creation-status" class="ennu-status"></div>
					</div>
				</div>

				<!-- Sleep Assessment Fields Section -->
				<div class="ennu-section">
					<h3>😴 Sleep Assessment Fields</h3>
					<div class="ennu-section-content">
						<p><strong>Sleep Assessment Field Creation</strong></p>
						<p>This will create all fields for the Sleep assessment:</p>
						<ul>
							<li><strong>Contact Properties:</strong> Assessment-specific questions and scores</li>
							<li><strong>Custom Object Fields:</strong> Historical tracking records in the basic_assessments object</li>
						</ul>
						
						<div class="ennu-actions">
							<button type="button" id="preview-sleep-fields" class="button button-secondary">
								👁️ Preview Sleep Fields
							</button>
							<button type="button" id="create-sleep-fields" class="button button-primary" style="display:none;">
								🚀 Create Sleep Fields
							</button>
							<button type="button" id="delete-sleep-fields" class="button button-secondary">🗑️ Delete Sleep Fields</button>
							<button type="button" id="refresh-sleep-field-cache" class="button button-secondary">🔄 Refresh Field Cache</button>
						</div>
						
						<div id="sleep-preview" class="ennu-field-preview"></div>
						<div id="sleep-creation-status" class="ennu-status"></div>
					</div>
				</div>

				<!-- Health Assessment Fields Section -->
				<div class="ennu-section">
					<h3>🏥 Health Assessment Fields</h3>
					<div class="ennu-section-content">
						<p><strong>Health Assessment Field Creation</strong></p>
						<p>This will create all fields for the Health assessment:</p>
						<ul>
							<li><strong>Contact Properties:</strong> Assessment-specific questions and scores</li>
							<li><strong>Custom Object Fields:</strong> Historical tracking records in the basic_assessments object</li>
						</ul>
						
						<div class="ennu-actions">
							<button type="button" id="preview-health-fields" class="button button-secondary">
								👁️ Preview Health Fields
							</button>
							<button type="button" id="create-health-fields" class="button button-primary" style="display:none;">
								🚀 Create Health Fields
							</button>
							<button type="button" id="delete-health-fields" class="button button-secondary">🗑️ Delete Health Fields</button>
							<button type="button" id="refresh-health-field-cache" class="button button-secondary">🔄 Refresh Field Cache</button>
						</div>
						
						<div id="health-preview" class="ennu-field-preview"></div>
						<div id="health-creation-status" class="ennu-status"></div>
					</div>
				</div>

				<!-- Health Optimization Assessment Fields Section -->
				<div class="ennu-section">
					<h3>⚡ Health Optimization Assessment Fields</h3>
					<div class="ennu-section-content">
						<p><strong>Health Optimization Assessment Field Creation</strong></p>
						<p>This will create all fields for the Health Optimization assessment:</p>
						<ul>
							<li><strong>Contact Properties:</strong> Assessment-specific questions and scores</li>
							<li><strong>Custom Object Fields:</strong> Historical tracking records in the basic_assessments object</li>
						</ul>
						
						<div class="ennu-actions">
							<button type="button" id="preview-health-optimization-fields" class="button button-secondary">
								👁️ Preview Health Optimization Fields
							</button>
							<button type="button" id="create-health-optimization-fields" class="button button-primary" style="display:none;">
								🚀 Create Health Optimization Fields
							</button>
							<button type="button" id="delete-health-optimization-fields" class="button button-secondary">🗑️ Delete Health Optimization Fields</button>
							<button type="button" id="refresh-health-optimization-field-cache" class="button button-secondary">🔄 Refresh Field Cache</button>
						</div>
						
						<div id="health-optimization-preview" class="ennu-field-preview"></div>
						<div id="health-optimization-creation-status" class="ennu-status"></div>
					</div>
				</div>

				<!-- Skin Assessment Fields Section -->
				<div class="ennu-section">
					<h3>✨ Skin Assessment Fields</h3>
					<div class="ennu-section-content">
						<p><strong>Skin Assessment Field Creation</strong></p>
						<p>This will create all fields for the Skin assessment:</p>
						<ul>
							<li><strong>Contact Properties:</strong> Assessment-specific questions and scores</li>
							<li><strong>Custom Object Fields:</strong> Historical tracking records in the basic_assessments object</li>
						</ul>
						
						<div class="ennu-actions">
							<button type="button" id="preview-skin-fields" class="button button-secondary">
								👁️ Preview Skin Fields
							</button>
							<button type="button" id="create-skin-fields" class="button button-primary" style="display:none;">
								🚀 Create Skin Fields
							</button>
							<button type="button" id="delete-skin-fields" class="button button-secondary">🗑️ Delete Skin Fields</button>
							<button type="button" id="refresh-skin-field-cache" class="button button-secondary">🔄 Refresh Field Cache</button>
						</div>
						
						<div id="skin-preview" class="ennu-field-preview"></div>
						<div id="skin-creation-status" class="ennu-status"></div>
					</div>
				</div>

				<!-- Hair Assessment Fields Section -->
				<div class="ennu-section">
					<h3>💇 Hair Assessment Fields</h3>
					<div class="ennu-section-content">
						<p><strong>Hair Assessment Field Creation</strong></p>
						<p>This will create all fields for the Hair assessment:</p>
						<ul>
							<li><strong>Contact Properties:</strong> Assessment-specific questions and scores</li>
							<li><strong>Custom Object Fields:</strong> Historical tracking records in the basic_assessments object</li>
						</ul>
						
						<div class="ennu-actions">
							<button type="button" id="preview-hair-fields" class="button button-secondary">
								👁️ Preview Hair Fields
							</button>
							<button type="button" id="create-hair-fields" class="button button-primary" style="display:none;">
								🚀 Create Hair Fields
							</button>
							<button type="button" id="delete-hair-fields" class="button button-secondary">🗑️ Delete Hair Fields</button>
							<button type="button" id="refresh-hair-field-cache" class="button button-secondary">🔄 Refresh Field Cache</button>
						</div>
						
						<div id="hair-preview" class="ennu-field-preview"></div>
						<div id="hair-creation-status" class="ennu-status"></div>
					</div>
				</div>

				<!-- ED Treatment Assessment Fields Section -->
				<div class="ennu-section">
					<h3>💊 ED Treatment Assessment Fields</h3>
					<div class="ennu-section-content">
						<p><strong>ED Treatment Assessment Field Creation</strong></p>
						<p>This will create all fields for the ED Treatment assessment:</p>
						<ul>
							<li><strong>Contact Properties:</strong> Assessment-specific questions and scores</li>
							<li><strong>Custom Object Fields:</strong> Historical tracking records in the basic_assessments object</li>
						</ul>
						
						<div class="ennu-actions">
							<button type="button" id="preview-ed-treatment-fields" class="button button-secondary">
								👁️ Preview ED Treatment Fields
							</button>
							<button type="button" id="create-ed-treatment-fields" class="button button-primary" style="display:none;">
								🚀 Create ED Treatment Fields
							</button>
							<button type="button" id="delete-ed-treatment-fields" class="button button-secondary">🗑️ Delete ED Treatment Fields</button>
							<button type="button" id="refresh-ed-treatment-field-cache" class="button button-secondary">🔄 Refresh Field Cache</button>
						</div>
						
						<div id="ed-treatment-preview" class="ennu-field-preview"></div>
						<div id="ed-treatment-creation-status" class="ennu-status"></div>
					</div>
				</div>

				<!-- Menopause Assessment Fields Section -->
				<div class="ennu-section">
					<h3>🌺 Menopause Assessment Fields</h3>
					<div class="ennu-section-content">
						<p><strong>Menopause Assessment Field Creation</strong></p>
						<p>This will create all fields for the Menopause assessment:</p>
						<ul>
							<li><strong>Contact Properties:</strong> Assessment-specific questions and scores</li>
							<li><strong>Custom Object Fields:</strong> Historical tracking records in the basic_assessments object</li>
						</ul>
						
						<div class="ennu-actions">
							<button type="button" id="preview-menopause-fields" class="button button-secondary">
								👁️ Preview Menopause Fields
							</button>
							<button type="button" id="create-menopause-fields" class="button button-primary" style="display:none;">
								🚀 Create Menopause Fields
							</button>
							<button type="button" id="delete-menopause-fields" class="button button-secondary">🗑️ Delete Menopause Fields</button>
							<button type="button" id="refresh-menopause-field-cache" class="button button-secondary">🔄 Refresh Field Cache</button>
						</div>
						
						<div id="menopause-preview" class="ennu-field-preview"></div>
						<div id="menopause-creation-status" class="ennu-status"></div>
					</div>
				</div>

				<!-- Testosterone Assessment Fields Section -->
				<div class="ennu-section">
					<h3>💪 Testosterone Assessment Fields</h3>
					<div class="ennu-section-content">
						<p><strong>Testosterone Assessment Field Creation</strong></p>
						<p>This will create all fields for the Testosterone assessment:</p>
						<ul>
							<li><strong>Contact Properties:</strong> Assessment-specific questions and scores</li>
							<li><strong>Custom Object Fields:</strong> Historical tracking records in the basic_assessments object</li>
						</ul>
						
						<div class="ennu-actions">
							<button type="button" id="preview-testosterone-fields" class="button button-secondary">
								👁️ Preview Testosterone Fields
							</button>
							<button type="button" id="create-testosterone-fields" class="button button-primary" style="display:none;">
								🚀 Create Testosterone Fields
							</button>
							<button type="button" id="delete-testosterone-fields" class="button button-secondary">🗑️ Delete Testosterone Fields</button>
							<button type="button" id="refresh-testosterone-field-cache" class="button button-secondary">🔄 Refresh Field Cache</button>
						</div>
						
						<div id="testosterone-preview" class="ennu-field-preview"></div>
						<div id="testosterone-creation-status" class="ennu-status"></div>
					</div>
				</div>

				<!-- Welcome Assessment Fields Section -->
				<div class="ennu-section">
					<h3>👋 Welcome Assessment Fields</h3>
					<div class="ennu-section-content">
						<p><strong>Welcome Assessment Field Creation</strong></p>
						<p>This will create all fields for the Welcome assessment:</p>
						<ul>
							<li><strong>Contact Properties:</strong> Assessment-specific questions and scores</li>
							<li><strong>Custom Object Fields:</strong> Historical tracking records in the basic_assessments object</li>
						</ul>
						
						<div class="ennu-actions">
							<button type="button" id="preview-welcome-fields" class="button button-secondary">
								👁️ Preview Welcome Fields
							</button>
							<button type="button" id="create-welcome-fields" class="button button-primary" style="display:none;">
								🚀 Create Welcome Fields
							</button>
							<button type="button" id="delete-welcome-fields" class="button button-secondary">🗑️ Delete Welcome Fields</button>
						</div>
						
						<div id="welcome-preview" class="ennu-field-preview"></div>
						<div id="welcome-creation-status" class="ennu-status"></div>
					</div>
				</div>

				<!-- Custom Field Creation Section -->
				<div class="ennu-section">
					<h3>🔧 Create Custom Fields</h3>
					<div class="ennu-section-content">
						<select id="custom-object-type" class="ennu-select">
							<option value="">Select Custom Object</option>
						</select>
						
						<div id="field-builder">
							<div id="fields-container"></div>
							<button type="button" id="add-field" class="button">Add Field</button>
						</div>
						
						<div class="ennu-actions">
							<button type="button" id="validate-schema" class="button">Validate Schema</button>
							<button type="button" id="create-fields" class="button button-primary">Create Fields</button>
						</div>
						
						<div id="results" class="ennu-results"></div>
					</div>
				</div>
			</div>
		</div>

		<style>
		.ennu-hubspot-container {
			max-width: 1200px;
			margin: 20px 0;
		}
		
		.ennu-section {
			background: #fff;
			border: 1px solid #ddd;
			border-radius: 8px;
			margin-bottom: 20px;
			overflow: hidden;
		}
		
		.ennu-section h3 {
			background: #f8f9fa;
			margin: 0;
			padding: 15px 20px;
			border-bottom: 1px solid #ddd;
			font-size: 16px;
			font-weight: 600;
		}
		
		.ennu-section-content {
			padding: 20px;
		}
		
		.ennu-select {
			width: 100%;
			max-width: 400px;
			margin-bottom: 15px;
		}
		
		.ennu-status {
			margin-top: 10px;
			padding: 10px;
			border-radius: 4px;
			display: none;
		}
		
		.ennu-status.success {
			background: #d4edda;
			color: #155724;
			border: 1px solid #c3e6cb;
		}
		
		.ennu-status.error {
			background: #f8d7da;
			color: #721c24;
			border: 1px solid #f5c6cb;
		}
		
		.ennu-field-row {
			display: flex;
			gap: 10px;
			margin-bottom: 10px;
			align-items: center;
		}
		
		.ennu-field-row input,
		.ennu-field-row select {
			flex: 1;
		}
		
		.ennu-form-row {
			margin-bottom: 15px;
		}
		
		.ennu-form-row label {
			display: block;
			margin-bottom: 5px;
			font-weight: 600;
		}
		
		.ennu-field-preview {
			margin-top: 20px;
			padding: 15px;
			background: #f8f9fa;
			border: 1px solid #ddd;
			border-radius: 4px;
			display: none;
		}
		
		.ennu-field-preview h4 {
			margin-top: 0;
			color: #333;
		}
		
		.ennu-field-item {
			background: #fff;
			padding: 10px;
			margin-bottom: 8px;
			border: 1px solid #e0e0e0;
			border-radius: 4px;
		}
		
		.ennu-field-item strong {
			color: #0073aa;
		}
		
		.ennu-field-options {
			margin-top: 5px;
			font-size: 12px;
			color: #666;
		}
		
		.ennu-field-row .remove-field {
			background: #dc3545;
			color: white;
			border: none;
			padding: 5px 10px;
			border-radius: 4px;
			cursor: pointer;
		}
		
		.ennu-actions {
			margin-top: 20px;
			display: flex;
			gap: 10px;
		}
		
		.ennu-results {
			margin-top: 20px;
			padding: 15px;
			border-radius: 4px;
			display: none;
		}
		
		.ennu-success-section {
			background: #d4edda;
			color: #155724;
			padding: 15px;
			border-radius: 4px;
			margin-bottom: 15px;
		}
		
		.ennu-error-section {
			background: #f8d7da;
			color: #721c24;
			padding: 15px;
			border-radius: 4px;
		}
		
		.ennu-success-section h4,
		.ennu-error-section h4 {
			margin-top: 0;
		}
		
		.ennu-success-section ul,
		.ennu-error-section ul {
			margin-bottom: 0;
		}
		
		.ennu-preview-section {
			margin-bottom: 25px;
			padding: 15px;
			background: #fff;
			border: 1px solid #e0e0e0;
			border-radius: 6px;
		}
		
		.ennu-preview-section h5 {
			margin-top: 0;
			color: #333;
			border-bottom: 2px solid #0073aa;
			padding-bottom: 8px;
		}
		
		.ennu-field-categories {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
			gap: 15px;
			margin-top: 15px;
		}
		
		.ennu-field-category {
			background: #f8f9fa;
			padding: 12px;
			border-radius: 4px;
			border-left: 4px solid #0073aa;
		}
		
		.ennu-field-category h6 {
			margin-top: 0;
			margin-bottom: 10px;
			color: #0073aa;
			font-size: 14px;
			font-weight: 600;
		}
		
		.ennu-field-item {
			background: #fff;
			padding: 8px;
			margin-bottom: 6px;
			border: 1px solid #e0e0e0;
			border-radius: 3px;
			font-size: 12px;
		}
		
		.ennu-field-item strong {
			color: #333;
			font-weight: 600;
		}
		
		.ennu-field-item small {
			color: #666;
			font-style: italic;
		}
		
		.ennu-existence-summary {
			background: #e3f2fd;
			border: 1px solid #2196f3;
			border-radius: 4px;
			padding: 15px;
			margin-bottom: 20px;
		}
		
		.ennu-existence-summary h5 {
			margin-top: 0;
			color: #1976d2;
		}
		
		.ennu-field-exists {
			background: #f1f8e9 !important;
			border-color: #4caf50 !important;
		}
		
						.ennu-exists-badge {
					background: #4caf50;
					color: white;
					padding: 2px 6px;
					border-radius: 3px;
					font-size: 10px;
					font-weight: bold;
					margin-left: 8px;
				}
				
				.ennu-preview-overview {
					background: #f8f9fa;
					padding: 15px;
					border-radius: 4px;
					margin-bottom: 20px;
					border-left: 4px solid #007cba;
				}
				
				.ennu-section-description {
					background: #e3f2fd;
					padding: 10px;
					border-radius: 4px;
					margin-bottom: 15px;
					border-left: 3px solid #2196f3;
				}
				
				.ennu-category-description {
					background: #f1f8e9;
					padding: 8px;
					border-radius: 3px;
					margin-bottom: 10px;
					border-left: 2px solid #4caf50;
					font-size: 13px;
				}
				
				.ennu-field-mapping {
					background: #fff3cd;
					padding: 8px;
					border-radius: 3px;
					margin: 8px 0;
					border-left: 2px solid #ffc107;
					font-size: 12px;
				}
				
				.ennu-field-options {
					background: #e8f4fd;
					padding: 8px;
					border-radius: 3px;
					margin: 8px 0;
					border-left: 2px solid #17a2b8;
					font-size: 12px;
				}
				
				.ennu-field-mapping code,
				.ennu-field-options code {
					background: #f8f9fa;
					padding: 2px 4px;
					border-radius: 2px;
					font-family: 'Courier New', monospace;
					font-size: 11px;
		}
		</style>

		<script>
		jQuery(document).ready(function($) {
			const nonce = '<?php echo wp_create_nonce( 'ennu_hubspot_nonce' ); ?>';
			
			// Load custom objects
			loadCustomObjects();
			
			// Load field statistics on page load
			loadFieldStatistics();
			
			// Refresh All Statistics
			$('#refresh-all-stats').on('click', function() {
				const button = $(this);
				button.prop('disabled', true).text('🔄 Refreshing...');
				loadFieldStatistics();
				setTimeout(() => {
					button.prop('disabled', false).text('🔄 Refresh All Statistics');
				}, 2000);
			});
			
			// Test Statistics
			$('#test-stats').on('click', function() {
				const button = $(this);
				button.prop('disabled', true).text('🧪 Testing...');
				
				$.post(ajaxurl, {
					action: 'ennu_test_field_statistics',
					nonce: nonce
				})
				.done(function(response) {
					console.log('Test response:', response);
					if (response.success) {
						alert('✅ Test successful! Found ' + response.data.assessments_count + ' assessments.');
					} else {
						alert('❌ Test failed: ' + response.data);
					}
				})
				.fail(function(xhr, status, error) {
					console.error('Test failed:', error);
					alert('❌ Test failed: ' + error);
				})
				.always(function() {
					button.prop('disabled', false).text('🧪 Test Statistics');
				});
			});
			
			// Test API Connection
			$('#test-connection').on('click', function() {
				const button = $(this);
				const status = $('#test-status');
				
				button.prop('disabled', true).text('Testing...');
				status.removeClass('success error').hide();
				
				$.post(ajaxurl, {
					action: 'ennu_test_hubspot_api',
					nonce: nonce
				})
				.done(function(response) {
					if (response.success) {
						status.addClass('success').html('✅ ' + response.data).show();
					} else {
						status.addClass('error').html('❌ ' + response.data).show();
					}
				})
				.fail(function() {
					status.addClass('error').html('❌ Connection failed').show();
				})
				.always(function() {
					button.prop('disabled', false).text('Test API Connection');
				});
			});
			
			// Load assessments on page load
			loadAssessments();
			
			// Preview Assessment Fields
			$('#preview-assessment-fields').on('click', function() {
				const button = $(this);
				const objectType = $('#assessment-object-type').val();
				const assessmentName = $('#assessment-select').val();
				const preview = $('#field-preview');
				
				if (!objectType) {
					alert('Please select an object type first.');
					return;
				}
				
				if (!assessmentName) {
					alert('Please select an assessment first.');
					return;
				}
				
				button.prop('disabled', true).text('Loading Preview...');
				preview.hide();
				
				$.post(ajaxurl, {
					action: 'ennu_preview_assessment_fields',
					nonce: nonce,
					assessment_name: assessmentName
				})
				.done(function(response) {
					if (response.success) {
						displayFieldPreview(response.data);
						$('#import-assessment-fields').show();
					} else {
						preview.addClass('error').html('❌ ' + response.data).show();
					}
				})
				.fail(function() {
					preview.addClass('error').html('❌ Preview failed').show();
				})
				.always(function() {
					button.prop('disabled', false).text('Preview Fields');
				});
			});
			
			// Import Assessment Fields
			$('#import-assessment-fields').on('click', function() {
				const button = $(this);
				const status = $('#import-status');
				const objectType = $('#assessment-object-type').val();
				const assessmentName = $('#assessment-select').val();
				
				if (!objectType) {
					alert('Please select an object type first.');
					return;
				}
				
				if (!assessmentName) {
					alert('Please select an assessment first.');
					return;
				}
				
				button.prop('disabled', true).text('Creating Fields...');
				status.removeClass('success error').hide();
				
				$.post(ajaxurl, {
					action: 'ennu_import_assessment_fields',
					nonce: nonce,
					object_type: objectType,
					assessment_name: assessmentName
				})
				.done(function(response) {
					if (response.success) {
						status.addClass('success').html('✅ ' + response.data).show();
					} else {
						status.addClass('error').html('❌ ' + response.data).show();
					}
				})
				.fail(function() {
					status.addClass('error').html('❌ Import failed').show();
				})
				.always(function() {
					button.prop('disabled', false).text('Create Fields in HubSpot');
				});
			});
			
			function loadAssessments() {
				$.post(ajaxurl, {
					action: 'ennu_get_assessments',
					nonce: nonce
				})
				.done(function(response) {
					if (response.success && response.data) {
						const assessments = response.data;
						const assessmentSelect = $('#assessment-select');
						
						assessmentSelect.empty().append('<option value="">Choose an assessment...</option>');
						
						assessments.forEach(function(assessment) {
							const option = '<option value="' + assessment.name + '">' + assessment.title + '</option>';
							assessmentSelect.append(option);
						});
					}
				})
				.fail(function() {
					console.error('Failed to load assessments');
				});
			}
			
			function displayFieldPreview(data) {
				const preview = $('#field-preview');
				let html = '<h4>📋 Preview: ' + data.assessment_title + ' Fields</h4>';
				html += '<p><strong>Field Count:</strong> ' + data.fields.length + ' fields</p>';
				html += '<p><strong>Note:</strong> This will create new fields in HubSpot. Existing fields with the same names will not be overwritten.</p>';
				
				data.fields.forEach(function(field) {
					html += '<div class="ennu-field-item">';
					html += '<strong>Field Name:</strong> ' + field.name + '<br>';
					html += '<strong>Label:</strong> ' + field.label + '<br>';
					html += '<strong>Type:</strong> ' + field.type + '<br>';
					html += '<strong>Required:</strong> ' + (field.required ? 'Yes' : 'No');
					
					if (field.options && field.options.length > 0) {
						html += '<div class="ennu-field-options">';
						html += '<strong>Options:</strong><br>';
						field.options.forEach(function(option) {
							html += '• ' + option.label + ' (' + option.value + ')<br>';
						});
						html += '</div>';
					}
					
					html += '</div>';
				});
				
				preview.removeClass('error').html(html).show();
			}

			// Helper function to get WordPress field name from HubSpot field name
			function getWordPressFieldName(hubspotFieldName) {
				const wpMapping = {
					// Global Fields (Contact Properties)
					'wl_q_gender': 'wl_q_gender',
					'wl_q_dob': 'wl_q_dob', 
					'wl_q1_height_weight': 'wl_q1',
					
					// Assessment Questions (Contact Properties)
					'wl_q2_weight_loss_goal': 'wl_q2',
					'wl_q3_diet_description': 'wl_q3',
					'wl_q4_exercise_frequency': 'wl_q4',
					'wl_q5_sleep_hours': 'wl_q5',
					'wl_q6_stress_level': 'wl_q6',
					'wl_q7_weight_loss_history': 'wl_q7',
					'wl_q8_emotional_eating': 'wl_q8',
					'wl_q9_medical_conditions': 'wl_q9',
					'wl_q10_motivation_level': 'wl_q10',
					'wl_q11_body_composition_goal': 'wl_q11',
					'wl_q12_support_system': 'wl_q12',
					'wl_q13_confidence_level': 'wl_q13',
					
					// Assessment Scores (Contact Properties)
					'wl_overall_score': 'wl_overall_score',
					'wl_category_motivation_goals_score': 'wl_category_motivation_goals_score',
					'wl_category_nutrition_score': 'wl_category_nutrition_score',
					'wl_category_physical_activity_score': 'wl_category_physical_activity_score',
					'wl_category_lifestyle_factors_score': 'wl_category_lifestyle_factors_score',
					'wl_category_psychological_factors_score': 'wl_category_psychological_factors_score',
					'wl_category_weight_loss_history_score': 'wl_category_weight_loss_history_score',
					'wl_category_behavioral_patterns_score': 'wl_category_behavioral_patterns_score',
					'wl_category_medical_factors_score': 'wl_category_medical_factors_score',
					'wl_category_social_support_score': 'wl_category_social_support_score',
					'wl_category_aesthetics_score': 'wl_category_aesthetics_score',
					'wl_pillar_mind_score': 'wl_pillar_mind_score',
					'wl_pillar_body_score': 'wl_pillar_body_score',
					'wl_pillar_lifestyle_score': 'wl_pillar_lifestyle_score',
					'wl_pillar_aesthetics_score': 'wl_pillar_aesthetics_score',
					
					// Dashboard Aggregated (Contact Properties)
					'ennu_life_score': 'ennu_life_score',
					'ennu_pillar_mind_score': 'ennu_pillar_mind_score',
					'ennu_pillar_body_score': 'ennu_pillar_body_score',
					'ennu_pillar_lifestyle_score': 'ennu_pillar_lifestyle_score',
					'ennu_pillar_aesthetics_score': 'ennu_pillar_aesthetics_score',
					'total_assessments_completed': 'total_assessments_completed',
					
					// User Profile Data (Contact Properties)
					'reported_symptoms': 'reported_symptoms',
					'flagged_biomarkers': 'flagged_biomarkers',
					'health_goals': 'health_goals',
					
					// Metadata (Custom Object Fields)
					'assessment_type': 'assessment_type',
					'assessment_attempt_number': 'assessment_attempt_number',
					'assessment_score': 'assessment_score',
					'assessment_completion_date': 'assessment_completion_date',
					'user_id': 'user_id',
					'user_email': 'user_email',
					'previous_assessment_score': 'previous_assessment_score',
					'score_change': 'score_change',
					'assessment_duration_seconds': 'assessment_duration_seconds',
					'assessment_version': 'assessment_version',
					
					// Assessment Questions (Custom Object Fields) - Same as contact but for historical tracking
					'wl_q2_weight_loss_goal': 'wl_q2',
					'wl_q3_diet_description': 'wl_q3',
					'wl_q4_exercise_frequency': 'wl_q4',
					'wl_q5_sleep_hours': 'wl_q5',
					'wl_q6_stress_level': 'wl_q6',
					'wl_q7_weight_loss_history': 'wl_q7',
					'wl_q8_emotional_eating': 'wl_q8',
					'wl_q9_medical_conditions': 'wl_q9',
					'wl_q10_motivation_level': 'wl_q10',
					'wl_q11_body_composition_goal': 'wl_q11',
					'wl_q12_support_system': 'wl_q12',
					'wl_q13_confidence_level': 'wl_q13',
					
					// Assessment Scores (Custom Object Fields) - Same as contact but for historical tracking
					'wl_overall_score': 'wl_overall_score',
					'wl_category_motivation_goals_score': 'wl_category_motivation_goals_score',
					'wl_category_nutrition_score': 'wl_category_nutrition_score',
					'wl_category_physical_activity_score': 'wl_category_physical_activity_score',
					'wl_category_lifestyle_factors_score': 'wl_category_lifestyle_factors_score',
					'wl_category_psychological_factors_score': 'wl_category_psychological_factors_score',
					'wl_category_weight_loss_history_score': 'wl_category_weight_loss_history_score',
					'wl_category_behavioral_patterns_score': 'wl_category_behavioral_patterns_score',
					'wl_category_medical_factors_score': 'wl_category_medical_factors_score',
					'wl_category_social_support_score': 'wl_category_social_support_score',
					'wl_category_aesthetics_score': 'wl_category_aesthetics_score',
					'wl_pillar_mind_score': 'wl_pillar_mind_score',
					'wl_pillar_body_score': 'wl_pillar_body_score',
					'wl_pillar_lifestyle_score': 'wl_pillar_lifestyle_score',
					'wl_pillar_aesthetics_score': 'wl_pillar_aesthetics_score'
				};
				return wpMapping[hubspotFieldName] || hubspotFieldName;
			}
			
			// Helper function to get WordPress option value from HubSpot option value
			function getWordPressOptionValue(hubspotFieldName, hubspotOptionValue) {
				// For most fields, the option values are the same
				return hubspotOptionValue;
			}
			
			function displayGlobalFieldsPreview(data) {
				const preview = $('#global-fields-preview');
				let html = '<h4>🌍 Preview: Global Shared Fields</h4>';
				html += '<div class="ennu-preview-overview">';
				html += '<p><strong>📊 Total Fields:</strong> ' + data.total_fields + ' fields used across ALL assessments</p>';
				html += '<p><strong>📞 Contact Properties:</strong> ' + data.total_fields + ' fields (shared across all 11 assessments)</p>';
				html += '<p><em>💡 <strong>Global Fields</strong> = Basic user information that gets updated whenever ANY assessment is completed</em></p>';
				html += '</div>';
				
				// Add existence status summary
				html += '<div class="ennu-existence-summary">';
				html += '<h5>📊 Existence Status</h5>';
				html += '<p><strong>Existing Fields:</strong> ' + data.existing_count + ' of ' + data.total_fields + ' exist</p>';
				html += '<p><strong>Missing Fields:</strong> ' + data.missing_count + ' of ' + data.total_fields + ' need to be created</p>';
				html += '<p><strong>Note:</strong> Existing fields will be skipped during creation.</p>';
				html += '</div>';
				
				// Global Fields Section
				html += '<div class="ennu-preview-section">';
				html += '<h5>🌍 Global Shared Fields (' + data.total_fields + ' fields)</h5>';
				html += '<p class="ennu-section-description"><em>These fields are stored on the HubSpot contact record and get updated with each assessment submission. They represent basic user information that applies across all assessments.</em></p>';
				html += '<div class="ennu-field-categories">';
				
				// Group global fields by category
				const globalFields = data.global_fields;
				const fieldGroups = {};
				
				globalFields.forEach(field => {
					const groupName = field.groupName || 'Basic Information';
					if (!fieldGroups[groupName]) {
						fieldGroups[groupName] = [];
					}
					fieldGroups[groupName].push(field);
				});
				
				// Display fields grouped by category
				Object.keys(fieldGroups).forEach(groupName => {
					const fields = fieldGroups[groupName];
					html += '<div class="ennu-field-category">';
					html += '<h6>📁 ' + groupName + ' (' + fields.length + ' fields)</h6>';
					
					// Add group description based on group name
					let groupDescription = '';
					switch(groupName) {
						case 'Basic Information':
							groupDescription = '<em>Core user demographic and physical information that applies across all assessments.</em>';
							break;
						case 'Physical Measurements':
							groupDescription = '<em>Height, weight, BMI, and other physical measurements for health tracking.</em>';
							break;
						case 'Demographics':
							groupDescription = '<em>Age, gender, and other demographic information.</em>';
							break;
						default:
							groupDescription = '<em>Fields related to ' + groupName.toLowerCase() + '.</em>';
					}
					html += '<p class="ennu-category-description">' + groupDescription + '</p>';
					
					fields.forEach(function(field) {
					html += '<div class="ennu-field-item' + (field.exists ? ' ennu-field-exists' : '') + '">';
					html += '<strong>' + field.name + '</strong> - ' + field.label + ' (' + field.type + ')';
					if (field.exists) {
						html += ' <span class="ennu-exists-badge">✅ Exists</span>';
					}
					html += '<br>';
					
					// Add WordPress to HubSpot mapping
					html += '<div class="ennu-field-mapping">';
						html += '<small><strong>WordPress Field Key:</strong> <code>' + (field.wordpress_field_key || field.original_key || 'ennu_global_' + field.name.replace('ennu', '').toLowerCase()) + '</code></small><br>';
						html += '<small><strong>WordPress Field Name:</strong> <code>' + (field.wordpress_field_name || field.label || field.name) + '</code></small><br>';
						html += '<small><strong>HubSpot Field Name:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small><br>';
					if (field.hubspot_id) {
							html += '<small><strong>HubSpot Field ID:</strong> <code>' + field.hubspot_id + '</code></small>';
					} else {
							html += '<small><strong>HubSpot Field ID:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small>';
					}
					html += '</div>';
					
					if (field.options && field.options.length > 0) {
						html += '<div class="ennu-field-options">';
						html += '<small><strong>Answer Options:</strong></small><br>';
						field.options.forEach(function(option) {
							html += '<small>• <strong>WordPress:</strong> <code>' + option.value + '</code> → <strong>HubSpot:</strong> <code>' + option.value + '</code> (' + option.label + ')</small><br>';
						});
						html += '</div>';
					}
						html += '</div>';
					});
					html += '</div>';
				});
				
				html += '</div>'; // Close ennu-field-categories
				html += '</div>'; // Close ennu-preview-section
				
				preview.html(html).show();
			}

			function displayAssessmentPreview(data, assessment) {
				const preview = $('#' + assessment.id + '-preview');
				let html = '<h4>' + assessment.emoji + ' Preview: ' + assessment.name + ' Assessment Complete Field System</h4>';
				html += '<div class="ennu-preview-overview">';
				html += '<p><strong>📊 Total Fields:</strong> ' + data.total_fields + ' fields across 2 HubSpot objects</p>';
				html += '<p><strong>📞 Contact Properties:</strong> ' + data.contact_fields.count + ' fields (stored on the contact record)</p>';
				html += '<p><strong>📋 Custom Object Fields:</strong> ' + data.custom_object_fields.count + ' fields (stored as separate assessment records)</p>';
				html += '<p><em>💡 <strong>Contact Properties</strong> = Current user data that gets updated with each assessment</em></p>';
				html += '<p><em>💡 <strong>Custom Object Fields</strong> = Historical assessment records for tracking progress over time</em></p>';
				html += '</div>';
				
				// Add existence status summary
				if (data.existence_status) {
					const status = data.existence_status;
					let existingGroups = 0;
					let existingFields = 0;
					let totalGroups = 0;
					let totalFields = 0;
					
					// Count existing vs total
					Object.keys(status.contact_properties.groups).forEach(group => {
						totalGroups++;
						if (status.contact_properties.groups[group]) existingGroups++;
					});
					
					Object.keys(status.custom_object_properties.groups).forEach(group => {
						totalGroups++;
						if (status.custom_object_properties.groups[group]) existingGroups++;
					});
					
					Object.keys(status.contact_properties.fields).forEach(field => {
						totalFields++;
						if (status.contact_properties.fields[field]) existingFields++;
					});
					
					Object.keys(status.custom_object_properties.fields).forEach(field => {
						totalFields++;
						if (status.custom_object_properties.fields[field]) existingFields++;
					});
					
					html += '<div class="ennu-existence-summary">';
					html += '<h5>📊 Existence Status</h5>';
					html += '<p><strong>Property Groups:</strong> ' + existingGroups + ' of ' + totalGroups + ' exist</p>';
					html += '<p><strong>Fields:</strong> ' + existingFields + ' of ' + totalFields + ' exist</p>';
					html += '<p><strong>Note:</strong> Existing fields and groups will be skipped during creation.</p>';
					html += '</div>';
				}
				
				// Contact Properties Section
				html += '<div class="ennu-preview-section">';
				html += '<h5>📞 Contact Properties (' + data.contact_fields.count + ' fields)</h5>';
				html += '<p class="ennu-section-description"><em>These fields are stored directly on the HubSpot contact record and get updated with each assessment submission. They represent the user\'s current state and latest assessment data.</em></p>';
				html += '<div class="ennu-field-categories">';
				
				// Group contact fields by groupName
				const contactFields = data.contact_fields.fields;
				const fieldGroups = {};
				
				contactFields.forEach(field => {
					const groupName = field.groupName || 'Other';
					if (!fieldGroups[groupName]) {
						fieldGroups[groupName] = [];
					}
					fieldGroups[groupName].push(field);
				});
				
				// Display fields grouped by category
				Object.keys(fieldGroups).forEach(groupName => {
					const fields = fieldGroups[groupName];
					html += '<div class="ennu-field-category">';
					html += '<h6>📁 ' + groupName + ' (' + fields.length + ' fields)</h6>';
					
					// Add group description based on group name
					let groupDescription = '';
					switch(groupName) {
						case 'Global':
							groupDescription = '<em>Basic user information like gender, date of birth, and height/weight that applies across all assessments.</em>';
							break;
						case 'Assessment Questions':
							groupDescription = '<em>Specific questions from this assessment that capture user responses and preferences.</em>';
							break;
						case 'Assessment Scores':
							groupDescription = '<em>Calculated scores and metrics from this assessment for tracking progress and performance.</em>';
							break;
						case 'Dashboard Aggregated':
							groupDescription = '<em>Combined scores and metrics that aggregate data across multiple assessments.</em>';
							break;
						case 'User Profile':
							groupDescription = '<em>Additional user profile information and preferences.</em>';
							break;
						default:
							groupDescription = '<em>Fields related to ' + groupName.toLowerCase() + '.</em>';
					}
					html += '<p class="ennu-category-description">' + groupDescription + '</p>';
					
					fields.forEach(function(field) {
					const exists = data.existence_status && data.existence_status.contact_properties.fields[field.name];
					html += '<div class="ennu-field-item' + (exists ? ' ennu-field-exists' : '') + '">';
					html += '<strong>' + field.name + '</strong> - ' + field.label + ' (' + field.type + ')';
					if (exists) {
						html += ' <span class="ennu-exists-badge">✅ Exists</span>';
					}
					html += '<br>';
					
					// Add WordPress to HubSpot mapping
					html += '<div class="ennu-field-mapping">';
						html += '<small><strong>WordPress Field Key:</strong> <code>' + (field.wordpress_field_key || field.original_key || 'ennu_' + assessment.id + '_' + field.name.toLowerCase()) + '</code></small><br>';
						html += '<small><strong>WordPress Field Name:</strong> <code>' + (field.wordpress_field_name || field.label || field.name) + '</code></small><br>';
						html += '<small><strong>HubSpot Field Name:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small><br>';
					if (field.hubspot_id) {
							html += '<small><strong>HubSpot Field ID:</strong> <code>' + field.hubspot_id + '</code></small>';
					} else {
							html += '<small><strong>HubSpot Field ID:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small>';
					}
					html += '</div>';
					
					if (field.options && field.options.length > 0) {
						html += '<div class="ennu-field-options">';
						html += '<small><strong>Answer Options:</strong></small><br>';
						field.options.forEach(function(option) {
							html += '<small>• <strong>WordPress:</strong> <code>' + option.value + '</code> → <strong>HubSpot:</strong> <code>' + option.value + '</code> (' + option.label + ')</small><br>';
						});
						html += '</div>';
					}
						html += '</div>';
					});
					html += '</div>';
				});
				
				html += '</div>'; // Close ennu-field-categories
				html += '</div>'; // Close ennu-preview-section
				
				// Custom Object Fields Section
				html += '<div class="ennu-preview-section">';
				html += '<h5>📋 Custom Object Fields (' + data.custom_object_fields.count + ' fields)</h5>';
				html += '<p class="ennu-section-description"><em>These fields are stored in the basic_assessments custom object for historical tracking and progress monitoring.</em></p>';
				html += '<div class="ennu-field-categories">';
				
				// Group custom object fields by groupName
				const customObjectFields = data.custom_object_fields.fields;
				const customObjectFieldGroups = {};
				
				customObjectFields.forEach(field => {
					const groupName = field.groupName || 'Other';
					if (!customObjectFieldGroups[groupName]) {
						customObjectFieldGroups[groupName] = [];
					}
					customObjectFieldGroups[groupName].push(field);
				});
				
				// Display custom object fields grouped by category
				Object.keys(customObjectFieldGroups).forEach(groupName => {
					const fields = customObjectFieldGroups[groupName];
					html += '<div class="ennu-field-category">';
					html += '<h6>📁 ' + groupName + ' (' + fields.length + ' fields)</h6>';
					
					// Add group description based on group name
					let groupDescription = '';
					switch(groupName) {
						case 'Metadata':
							groupDescription = '<em>Assessment metadata including type, completion date, user information, and tracking data.</em>';
							break;
						case 'Assessment Questions':
							groupDescription = '<em>Historical record of user responses to assessment questions for progress tracking.</em>';
							break;
						case 'Assessment Scores':
							groupDescription = '<em>Historical record of calculated scores and metrics for trend analysis.</em>';
							break;
						default:
							groupDescription = '<em>Fields related to ' + groupName.toLowerCase() + '.</em>';
					}
					html += '<p class="ennu-category-description">' + groupDescription + '</p>';
					
					fields.forEach(function(field) {
					const exists = data.existence_status && data.existence_status.custom_object_properties.fields[field.name];
					html += '<div class="ennu-field-item' + (exists ? ' ennu-field-exists' : '') + '">';
					html += '<strong>' + field.name + '</strong> - ' + field.label + ' (' + field.type + ')';
					if (exists) {
						html += ' <span class="ennu-exists-badge">✅ Exists</span>';
					}
					html += '<br>';
					
					// Add WordPress to HubSpot mapping
					html += '<div class="ennu-field-mapping">';
						html += '<small><strong>WordPress Field Key:</strong> <code>' + (field.wordpress_field_key || field.original_key || 'ennu_' + assessment.id + '_' + field.name.toLowerCase()) + '</code></small><br>';
						html += '<small><strong>WordPress Field Name:</strong> <code>' + (field.wordpress_field_name || field.label || field.name) + '</code></small><br>';
						html += '<small><strong>HubSpot Field Name:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small><br>';
					if (field.hubspot_id) {
							html += '<small><strong>HubSpot Field ID:</strong> <code>' + field.hubspot_id + '</code></small>';
					} else {
							html += '<small><strong>HubSpot Field ID:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small>';
					}
					html += '</div>';
					
					if (field.options && field.options.length > 0) {
						html += '<div class="ennu-field-options">';
						html += '<small><strong>Answer Options:</strong></small><br>';
						field.options.forEach(function(option) {
							html += '<small>• <strong>WordPress:</strong> <code>' + option.value + '</code> → <strong>HubSpot:</strong> <code>' + option.value + '</code> (' + option.label + ')</small><br>';
						});
						html += '</div>';
					}
						html += '</div>';
					});
					html += '</div>';
				});
				
				html += '</div>'; // Close ennu-field-categories
				html += '</div>'; // Close ennu-preview-section
				
				preview.html(html).show();
			}

			function displayWeightLossPreview(data) {
				const preview = $('#weight-loss-preview');
				let html = '<h4>⚖️ Preview: ' + data.assessment_title + ' Complete Field System</h4>';
				html += '<div class="ennu-preview-overview">';
				html += '<p><strong>📊 Total Fields:</strong> ' + data.total_fields + ' fields across 2 HubSpot objects</p>';
				html += '<p><strong>📞 Contact Properties:</strong> ' + data.contact_fields.count + ' fields (stored on the contact record)</p>';
				html += '<p><strong>📋 Custom Object Fields:</strong> ' + data.custom_object_fields.count + ' fields (stored as separate assessment records)</p>';
				html += '<p><em>💡 <strong>Contact Properties</strong> = Current user data that gets updated with each assessment</em></p>';
				html += '<p><em>💡 <strong>Custom Object Fields</strong> = Historical assessment records for tracking progress over time</em></p>';
				html += '</div>';
				
				// Add existence status summary
				if (data.existence_status) {
					const status = data.existence_status;
					let existingGroups = 0;
					let existingFields = 0;
					let totalGroups = 0;
					let totalFields = 0;
					
					// Count existing vs total
					Object.keys(status.contact_properties.groups).forEach(group => {
						totalGroups++;
						if (status.contact_properties.groups[group]) existingGroups++;
					});
					
					Object.keys(status.custom_object_properties.groups).forEach(group => {
						totalGroups++;
						if (status.custom_object_properties.groups[group]) existingGroups++;
					});
					
					Object.keys(status.contact_properties.fields).forEach(field => {
						totalFields++;
						if (status.contact_properties.fields[field]) existingFields++;
					});
					
					Object.keys(status.custom_object_properties.fields).forEach(field => {
						totalFields++;
						if (status.custom_object_properties.fields[field]) existingFields++;
					});
					
					html += '<div class="ennu-existence-summary">';
					html += '<h5>📊 Existence Status</h5>';
					html += '<p><strong>Property Groups:</strong> ' + existingGroups + ' of ' + totalGroups + ' exist</p>';
					html += '<p><strong>Fields:</strong> ' + existingFields + ' of ' + totalFields + ' exist</p>';
					html += '<p><strong>Note:</strong> Existing fields and groups will be skipped during creation.</p>';
					html += '</div>';
				}
				
				// Contact Properties Section
				html += '<div class="ennu-preview-section">';
				html += '<h5>📞 Contact Properties (' + data.contact_fields.count + ' fields)</h5>';
				html += '<p class="ennu-section-description"><em>These fields are stored directly on the HubSpot contact record and get updated with each assessment submission. They represent the user\'s current state and latest assessment data.</em></p>';
				html += '<div class="ennu-field-categories">';
				
				// Group fields by category
				const contactFields = data.contact_fields.fields;
				const globalFields = contactFields.filter(f => f.groupName === 'Global');
				const assessmentFields = contactFields.filter(f => f.groupName === 'Weight Loss Assessment');
				const dashboardFields = contactFields.filter(f => f.groupName === 'Dashboard');
				const userProfileFields = contactFields.filter(f => f.groupName === 'User Profile');
				
				if (globalFields.length > 0) {
					html += '<div class="ennu-field-category">';
					html += '<h6>🌍 Global Fields (' + globalFields.length + ')</h6>';
					html += '<p class="ennu-category-description"><em>Basic user information like gender, date of birth, and height/weight that applies across all assessments.</em></p>';
					globalFields.forEach(function(field) {
						const exists = data.existence_status && data.existence_status.contact_properties.fields[field.name];
						html += '<div class="ennu-field-item' + (exists ? ' ennu-field-exists' : '') + '">';
						html += '<strong>' + field.name + '</strong> - ' + field.label + ' (' + field.type + ')';
						if (exists) {
							html += ' <span class="ennu-exists-badge">✅ Exists</span>';
						}
						html += '<br>';
						
						// Add WordPress to HubSpot mapping for global fields
						const wpFieldName = getWordPressFieldName(field.name);
						html += '<div class="ennu-field-mapping">';
						html += '<small><strong>WordPress:</strong> <code>' + wpFieldName + '</code></small><br>';
						html += '<small><strong>HubSpot Name:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small><br>';
						if (field.hubspot_id) {
							html += '<small><strong>HubSpot ID:</strong> <code>' + field.hubspot_id + '</code></small>';
						} else {
							html += '<small><strong>HubSpot ID:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small>';
						}
						html += '</div>';
						
						if (field.options && field.options.length > 0) {
							html += '<div class="ennu-field-options">';
							html += '<small><strong>Answer Options:</strong></small><br>';
							field.options.forEach(function(option) {
								const wpOptionValue = getWordPressOptionValue(field.name, option.value);
								html += '<small>• <strong>WordPress:</strong> <code>' + wpOptionValue + '</code> → <strong>HubSpot:</strong> <code>' + option.value + '</code> (' + option.label + ')</small><br>';
							});
							html += '</div>';
						}
						html += '</div>';
					});
					html += '</div>';
				}
				
				if (assessmentFields.length > 0) {
					html += '<div class="ennu-field-category">';
					html += '<h6>📝 Assessment Questions (' + assessmentFields.length + ')</h6>';
					html += '<p class="ennu-category-description"><em>The actual Weight Loss assessment questions and their answers, plus calculated scores for categories and pillars.</em></p>';
					assessmentFields.forEach(function(field) {
						const exists = data.existence_status && data.existence_status.contact_properties.fields[field.name];
						html += '<div class="ennu-field-item' + (exists ? ' ennu-field-exists' : '') + '">';
						html += '<strong>' + field.name + '</strong> - ' + field.label + ' (' + field.type + ')';
						if (exists) {
							html += ' <span class="ennu-exists-badge">✅ Exists</span>';
						}
						html += '<br>';
						
						// Add WordPress to HubSpot mapping
						const wpFieldName = getWordPressFieldName(field.name);
						html += '<div class="ennu-field-mapping">';
						html += '<small><strong>WordPress:</strong> <code>' + wpFieldName + '</code></small><br>';
						html += '<small><strong>HubSpot Name:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small><br>';
						if (field.hubspot_id) {
							html += '<small><strong>HubSpot ID:</strong> <code>' + field.hubspot_id + '</code></small>';
						} else {
							html += '<small><strong>HubSpot ID:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small>';
						}
						html += '</div>';
						
						if (field.options && field.options.length > 0) {
							html += '<div class="ennu-field-options">';
							html += '<small><strong>Answer Options:</strong></small><br>';
							field.options.forEach(function(option) {
								const wpOptionValue = getWordPressOptionValue(field.name, option.value);
								html += '<small>• <strong>WordPress:</strong> <code>' + wpOptionValue + '</code> → <strong>HubSpot:</strong> <code>' + option.value + '</code> (' + option.label + ')</small><br>';
							});
							html += '</div>';
						}
						html += '</div>';
					});
					html += '</div>';
				}
				
				if (dashboardFields.length > 0) {
					html += '<div class="ennu-field-category">';
					html += '<h6>📊 Dashboard Aggregated (' + dashboardFields.length + ')</h6>';
					html += '<p class="ennu-category-description"><em>Overall ENNU Life scores and pillar scores that aggregate data from all completed assessments.</em></p>';
					dashboardFields.forEach(function(field) {
						const exists = data.existence_status && data.existence_status.contact_properties.fields[field.name];
						html += '<div class="ennu-field-item' + (exists ? ' ennu-field-exists' : '') + '">';
						html += '<strong>' + field.name + '</strong> - ' + field.label + ' (' + field.type + ')';
						if (exists) {
							html += ' <span class="ennu-exists-badge">✅ Exists</span>';
						}
						html += '<br>';
						
						// Add WordPress to HubSpot mapping for dashboard fields
						const wpFieldName = getWordPressFieldName(field.name);
						html += '<div class="ennu-field-mapping">';
						html += '<small><strong>WordPress:</strong> <code>' + wpFieldName + '</code></small><br>';
						html += '<small><strong>HubSpot Name:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small><br>';
						if (field.hubspot_id) {
							html += '<small><strong>HubSpot ID:</strong> <code>' + field.hubspot_id + '</code></small>';
						} else {
							html += '<small><strong>HubSpot ID:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small>';
						}
						html += '</div>';
						
						if (field.options && field.options.length > 0) {
							html += '<div class="ennu-field-options">';
							html += '<small><strong>Answer Options:</strong></small><br>';
							field.options.forEach(function(option) {
								const wpOptionValue = getWordPressOptionValue(field.name, option.value);
								html += '<small>• <strong>WordPress:</strong> <code>' + wpOptionValue + '</code> → <strong>HubSpot:</strong> <code>' + option.value + '</code> (' + option.label + ')</small><br>';
							});
							html += '</div>';
						}
						html += '</div>';
					});
					html += '</div>';
				}
				
				if (userProfileFields.length > 0) {
					html += '<div class="ennu-field-category">';
					html += '<h6>👤 User Profile Data (' + userProfileFields.length + ')</h6>';
					html += '<p class="ennu-category-description"><em>User-reported symptoms, flagged biomarkers, and health goals that help personalize recommendations.</em></p>';
					userProfileFields.forEach(function(field) {
						const exists = data.existence_status && data.existence_status.contact_properties.fields[field.name];
						html += '<div class="ennu-field-item' + (exists ? ' ennu-field-exists' : '') + '">';
						html += '<strong>' + field.name + '</strong> - ' + field.label + ' (' + field.type + ')';
						if (exists) {
							html += ' <span class="ennu-exists-badge">✅ Exists</span>';
						}
						html += '<br>';
						
						// Add WordPress to HubSpot mapping for user profile fields
						const wpFieldName = getWordPressFieldName(field.name);
						html += '<div class="ennu-field-mapping">';
						html += '<small><strong>WordPress:</strong> <code>' + wpFieldName + '</code></small><br>';
						html += '<small><strong>HubSpot Name:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small><br>';
						if (field.hubspot_id) {
							html += '<small><strong>HubSpot ID:</strong> <code>' + field.hubspot_id + '</code></small>';
						} else {
							html += '<small><strong>HubSpot ID:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small>';
						}
						html += '</div>';
						
						if (field.options && field.options.length > 0) {
							html += '<div class="ennu-field-options">';
							html += '<small><strong>Answer Options:</strong></small><br>';
							field.options.forEach(function(option) {
								const wpOptionValue = getWordPressOptionValue(field.name, option.value);
								html += '<small>• <strong>WordPress:</strong> <code>' + wpOptionValue + '</code> → <strong>HubSpot:</strong> <code>' + option.value + '</code> (' + option.label + ')</small><br>';
							});
							html += '</div>';
						}
						html += '</div>';
					});
					html += '</div>';
				}
				
				html += '</div></div>';
				
				// Custom Object Fields Section
				html += '<div class="ennu-preview-section">';
				html += '<h5>📋 Custom Object Fields (' + data.custom_object_fields.count + ' fields)</h5>';
				html += '<p class="ennu-section-description"><em>These fields are stored as separate "Basic Assessment" records in HubSpot. Each assessment submission creates a new record, allowing you to track progress over time and see assessment history.</em></p>';
				html += '<div class="ennu-field-categories">';
				
				const customObjectFields = data.custom_object_fields.fields;
				const metadataFields = customObjectFields.filter(f => f.groupName === 'Metadata');
				const customAssessmentFields = customObjectFields.filter(f => f.groupName === 'Assessment Questions');
				const customScoreFields = customObjectFields.filter(f => f.groupName === 'Assessment Scores');
				
				if (metadataFields.length > 0) {
					html += '<div class="ennu-field-category">';
					html += '<h6>📋 Metadata (' + metadataFields.length + ')</h6>';
					html += '<p class="ennu-category-description"><em>Information about the assessment itself: type, completion date, scores, user details, and tracking data.</em></p>';
					metadataFields.forEach(function(field) {
						const exists = data.existence_status && data.existence_status.custom_object_properties.fields[field.name];
						html += '<div class="ennu-field-item' + (exists ? ' ennu-field-exists' : '') + '">';
						html += '<strong>' + field.name + '</strong> - ' + field.label + ' (' + field.type + ')';
						if (exists) {
							html += ' <span class="ennu-exists-badge">✅ Exists</span>';
						}
						html += '<br>';
						
						// Add WordPress to HubSpot mapping for metadata fields
						const wpFieldName = getWordPressFieldName(field.name);
						html += '<div class="ennu-field-mapping">';
						html += '<small><strong>WordPress:</strong> <code>' + wpFieldName + '</code></small><br>';
						html += '<small><strong>HubSpot Name:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small><br>';
						if (field.hubspot_id) {
							html += '<small><strong>HubSpot ID:</strong> <code>' + field.hubspot_id + '</code></small>';
						} else {
							html += '<small><strong>HubSpot ID:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small>';
						}
						html += '</div>';
						
						if (field.options && field.options.length > 0) {
							html += '<div class="ennu-field-options">';
							html += '<small><strong>Answer Options:</strong></small><br>';
							field.options.forEach(function(option) {
								const wpOptionValue = getWordPressOptionValue(field.name, option.value);
								html += '<small>• <strong>WordPress:</strong> <code>' + wpOptionValue + '</code> → <strong>HubSpot:</strong> <code>' + option.value + '</code> (' + option.label + ')</small><br>';
							});
							html += '</div>';
						}
						html += '</div>';
					});
					html += '</div>';
				}
				
				if (customAssessmentFields.length > 0) {
					html += '<div class="ennu-field-category">';
					html += '<h6>📝 Assessment Questions (' + customAssessmentFields.length + ')</h6>';
					html += '<p class="ennu-category-description"><em>The actual Weight Loss assessment questions and answers stored for historical tracking. Same questions as contact properties but preserved as separate records.</em></p>';
					customAssessmentFields.forEach(function(field) {
						const exists = data.existence_status && data.existence_status.custom_object_properties.fields[field.name];
						html += '<div class="ennu-field-item' + (exists ? ' ennu-field-exists' : '') + '">';
						html += '<strong>' + field.name + '</strong> - ' + field.label + ' (' + field.type + ')';
						if (exists) {
							html += ' <span class="ennu-exists-badge">✅ Exists</span>';
						}
						html += '<br>';
						
						// Add WordPress to HubSpot mapping for custom object fields
						const wpFieldName = getWordPressFieldName(field.name);
						html += '<div class="ennu-field-mapping">';
						html += '<small><strong>WordPress:</strong> <code>' + wpFieldName + '</code></small><br>';
						html += '<small><strong>HubSpot Name:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small><br>';
						if (field.hubspot_id) {
							html += '<small><strong>HubSpot ID:</strong> <code>' + field.hubspot_id + '</code></small>';
						} else {
							html += '<small><strong>HubSpot ID:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small>';
						}
						html += '</div>';
						
						if (field.options && field.options.length > 0) {
							html += '<div class="ennu-field-options">';
							html += '<small><strong>Answer Options:</strong></small><br>';
							field.options.forEach(function(option) {
								const wpOptionValue = getWordPressOptionValue(field.name, option.value);
								html += '<small>• <strong>WordPress:</strong> <code>' + wpOptionValue + '</code> → <strong>HubSpot:</strong> <code>' + option.value + '</code> (' + option.label + ')</small><br>';
							});
							html += '</div>';
						}
						html += '</div>';
					});
					html += '</div>';
				}
				
				if (customScoreFields.length > 0) {
					html += '<div class="ennu-field-category">';
					html += '<h6>📊 Assessment Scores (' + customScoreFields.length + ')</h6>';
					html += '<p class="ennu-category-description"><em>Calculated category and pillar scores for this specific assessment. These scores help track progress and identify areas for improvement.</em></p>';
					customScoreFields.forEach(function(field) {
						const exists = data.existence_status && data.existence_status.custom_object_properties.fields[field.name];
						html += '<div class="ennu-field-item' + (exists ? ' ennu-field-exists' : '') + '">';
						html += '<strong>' + field.name + '</strong> - ' + field.label + ' (' + field.type + ')';
						if (exists) {
							html += ' <span class="ennu-exists-badge">✅ Exists</span>';
						}
						html += '<br>';
						
						// Add WordPress to HubSpot mapping for assessment score fields
						const wpFieldName = getWordPressFieldName(field.name);
						html += '<div class="ennu-field-mapping">';
						html += '<small><strong>WordPress:</strong> <code>' + wpFieldName + '</code></small><br>';
						html += '<small><strong>HubSpot Name:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small><br>';
						if (field.hubspot_id) {
							html += '<small><strong>HubSpot ID:</strong> <code>' + field.hubspot_id + '</code></small>';
						} else {
							html += '<small><strong>HubSpot ID:</strong> <code>' + (field.hubspot_name || field.name) + '</code></small>';
						}
						html += '</div>';
						
						if (field.options && field.options.length > 0) {
							html += '<div class="ennu-field-options">';
							html += '<small><strong>Answer Options:</strong></small><br>';
							field.options.forEach(function(option) {
								const wpOptionValue = getWordPressOptionValue(field.name, option.value);
								html += '<small>• <strong>WordPress:</strong> <code>' + wpOptionValue + '</code> → <strong>HubSpot:</strong> <code>' + option.value + '</code> (' + option.label + ')</small><br>';
							});
							html += '</div>';
						}
						html += '</div>';
					});
					html += '</div>';
				}
				
				html += '</div></div>';
				
				preview.removeClass('error').html(html).show();
			}
			
			function displayFieldStatistics(data) {
				const container = $('#field-stats-container');
				let html = '';
				
				// Overall Summary
				html += '<div style="text-align: center; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 6px; grid-column: 1 / -1;">';
				html += '<div style="font-size: 28px; font-weight: bold; margin-bottom: 10px;">📊 Overall Progress</div>';
				html += '<div style="font-size: 36px; font-weight: bold; color: #4CAF50;">' + data.summary.completion_percentage + '%</div>';
				html += '<div style="font-size: 14px; margin-top: 5px;">Complete</div>';
				html += '<div style="margin-top: 10px; font-size: 12px;">';
				html += '<strong>Total Expected:</strong> ' + data.summary.total_expected_fields + ' fields<br>';
				html += '<strong>Total Existing:</strong> ' + data.summary.total_existing_fields + ' fields<br>';
				html += '<strong>Total Missing:</strong> ' + data.summary.total_missing_fields + ' fields';
				html += '</div>';
				html += '</div>';
				
				// Global Fields Summary
				if (data.global_fields) {
					const global = data.global_fields;
					const globalCompletion = global.expected_contact_fields > 0 ? 
						Math.round((global.existing_contact_fields / global.expected_contact_fields) * 100) : 0;
					const globalColor = globalCompletion >= 80 ? '#4CAF50' : 
									   globalCompletion >= 50 ? '#FF9800' : '#F44336';
					
					html += '<div style="text-align: left; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 6px; grid-column: 1 / -1;">';
					html += '<div style="display: flex; align-items: center; margin-bottom: 15px;">';
					html += '<div style="font-size: 24px; margin-right: 10px;">🌐</div>';
					html += '<div>';
					html += '<div style="font-size: 18px; font-weight: bold;">Global Shared Fields</div>';
					html += '<div style="font-size: 14px; color: ' + globalColor + '; font-weight: bold;">' + globalCompletion + '% Complete</div>';
					html += '</div>';
					html += '</div>';
					
					// Contact Properties Breakdown
					html += '<div style="margin-bottom: 15px;">';
					html += '<div style="font-size: 14px; font-weight: bold; margin-bottom: 8px; color: #2196F3;">📋 Contact Properties</div>';
					html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; font-size: 12px;">';
					html += '<div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 4px;">';
					html += '<div style="font-weight: bold;">Required</div>';
					html += '<div style="font-size: 16px; color: #FF9800;">' + global.expected_contact_fields + '</div>';
					html += '</div>';
					html += '<div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 4px;">';
					html += '<div style="font-weight: bold;">Existing</div>';
					html += '<div style="font-size: 16px; color: #4CAF50;">' + global.existing_contact_fields + '</div>';
					html += '</div>';
					html += '<div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 4px;">';
					html += '<div style="font-weight: bold;">Missing</div>';
					html += '<div style="font-size: 16px; color: #F44336;">' + global.missing_contact_fields + '</div>';
					html += '</div>';
					html += '</div>';
					html += '</div>';
					
					// Custom Object Fields Breakdown
					html += '<div style="margin-bottom: 15px;">';
					html += '<div style="font-size: 14px; font-weight: bold; margin-bottom: 8px; color: #9C27B0;">🗄️ Custom Object Fields</div>';
					html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; font-size: 12px;">';
					html += '<div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 4px;">';
					html += '<div style="font-weight: bold;">Required</div>';
					html += '<div style="font-size: 16px; color: #FF9800;">' + global.expected_custom_object_fields + '</div>';
					html += '</div>';
					html += '<div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 4px;">';
					html += '<div style="font-weight: bold;">Existing</div>';
					html += '<div style="font-size: 16px; color: #4CAF50;">' + global.existing_custom_object_fields + '</div>';
					html += '</div>';
					html += '<div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 4px;">';
					html += '<div style="font-weight: bold;">Missing</div>';
					html += '<div style="font-size: 16px; color: #F44336;">' + global.missing_custom_object_fields + '</div>';
					html += '</div>';
					html += '</div>';
					html += '</div>';
					
					html += '</div>';
				}
				
				// HubSpot Totals
				html += '<div style="text-align: center; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 6px;">';
				html += '<div style="font-size: 24px; font-weight: bold;">🏢</div>';
				html += '<div style="font-size: 18px; font-weight: bold;">HubSpot Totals</div>';
				html += '<div style="font-size: 24px; font-weight: bold; color: #2196F3;">' + data.total_fields + '</div>';
				html += '<div style="font-size: 12px; margin-top: 5px;">Total Fields</div>';
				html += '<div style="margin-top: 10px; font-size: 11px;">';
				html += '<strong>Contact:</strong> ' + data.total_contact_fields + '<br>';
				html += '<strong>Custom Object:</strong> ' + data.total_custom_object_fields;
				html += '</div>';
				html += '</div>';
				
				// Assessment Statistics - Detailed Breakdown
				Object.keys(data.assessments).forEach(function(assessmentId) {
					const assessment = data.assessments[assessmentId];
					const completionColor = assessment.completion_percentage >= 80 ? '#4CAF50' : 
										 assessment.completion_percentage >= 50 ? '#FF9800' : '#F44336';
					
					html += '<div style="text-align: left; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 6px; grid-column: 1 / -1;">';
					html += '<div style="display: flex; align-items: center; margin-bottom: 15px;">';
					html += '<div style="font-size: 24px; margin-right: 10px;">' + getAssessmentEmoji(assessmentId) + '</div>';
					html += '<div>';
					html += '<div style="font-size: 18px; font-weight: bold;">' + assessment.title + '</div>';
					html += '<div style="font-size: 14px; color: ' + completionColor + '; font-weight: bold;">' + assessment.completion_percentage + '% Complete</div>';
					html += '</div>';
					html += '</div>';
					
					// Contact Properties Breakdown
					html += '<div style="margin-bottom: 15px;">';
					html += '<div style="font-size: 14px; font-weight: bold; margin-bottom: 8px; color: #2196F3;">📋 Contact Properties</div>';
					html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; font-size: 12px;">';
					html += '<div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 4px;">';
					html += '<div style="font-weight: bold;">Required</div>';
					html += '<div style="font-size: 16px; color: #FF9800;">' + assessment.expected_contact_fields + '</div>';
					html += '</div>';
					html += '<div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 4px;">';
					html += '<div style="font-weight: bold;">Existing</div>';
					html += '<div style="font-size: 16px; color: #4CAF50;">' + assessment.existing_contact_fields + '</div>';
					html += '</div>';
					html += '<div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 4px;">';
					html += '<div style="font-weight: bold;">Missing</div>';
					html += '<div style="font-size: 16px; color: #F44336;">' + assessment.missing_contact_fields + '</div>';
					html += '</div>';
					html += '</div>';
					html += '</div>';
					
					// Custom Object Fields Breakdown
					html += '<div style="margin-bottom: 15px;">';
					html += '<div style="font-size: 14px; font-weight: bold; margin-bottom: 8px; color: #9C27B0;">🗄️ Custom Object Fields</div>';
					html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; font-size: 12px;">';
					html += '<div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 4px;">';
					html += '<div style="font-weight: bold;">Required</div>';
					html += '<div style="font-size: 16px; color: #FF9800;">' + assessment.expected_custom_object_fields + '</div>';
					html += '</div>';
					html += '<div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 4px;">';
					html += '<div style="font-weight: bold;">Existing</div>';
					html += '<div style="font-size: 16px; color: #4CAF50;">' + assessment.existing_custom_object_fields + '</div>';
					html += '</div>';
					html += '<div style="background: rgba(255,255,255,0.1); padding: 8px; border-radius: 4px;">';
					html += '<div style="font-weight: bold;">Missing</div>';
					html += '<div style="font-size: 16px; color: #F44336;">' + assessment.missing_custom_object_fields + '</div>';
					html += '</div>';
					html += '</div>';
					html += '</div>';
					
					// Total Summary for this assessment
					html += '<div style="border-top: 1px solid rgba(255,255,255,0.2); padding-top: 10px;">';
					html += '<div style="font-size: 12px; font-weight: bold; margin-bottom: 5px;">📊 Assessment Total</div>';
					html += '<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; font-size: 11px;">';
					html += '<div style="background: rgba(255,255,255,0.1); padding: 6px; border-radius: 4px; text-align: center;">';
					html += '<div style="font-weight: bold;">Total Required</div>';
					html += '<div style="font-size: 14px; color: #FF9800;">' + assessment.expected_total + '</div>';
					html += '</div>';
					html += '<div style="background: rgba(255,255,255,0.1); padding: 6px; border-radius: 4px; text-align: center;">';
					html += '<div style="font-weight: bold;">Total Existing</div>';
					html += '<div style="font-size: 14px; color: #4CAF50;">' + assessment.existing_total + '</div>';
					html += '</div>';
					html += '<div style="background: rgba(255,255,255,0.1); padding: 6px; border-radius: 4px; text-align: center;">';
					html += '<div style="font-weight: bold;">Total Missing</div>';
					html += '<div style="font-size: 14px; color: #F44336;">' + assessment.missing_total + '</div>';
					html += '</div>';
					html += '</div>';
					html += '</div>';
					
					html += '</div>';
				});
				
				container.html(html);
			}
			
			function getAssessmentEmoji(assessmentId) {
				const emojiMap = {
					'weight-loss': '⚖️',
					'hormone': '🔄',
					'sleep': '😴',
					'health': '🏥',
					'health-optimization': '⚡',
					'skin': '✨',
					'hair': '💇',
					'ed-treatment': '💊',
					'menopause': '🌺',
					'testosterone': '💪',
					'welcome': '👋'
				};
				return emojiMap[assessmentId] || '📋';
			}
			
			// Add field
			$('#add-field').on('click', function() {
				addField();
			});
			
			function addField() {
				const fieldHtml = `
					<div class="ennu-field-row">
						<input type="text" placeholder="Field Name" class="field-name">
						<select class="field-type">
							<option value="string">Text</option>
							<option value="number">Number</option>
							<option value="date">Date</option>
							<option value="enumeration">Dropdown</option>
							<option value="boolean">Boolean</option>
						</select>
						<input type="checkbox" class="field-required"> Required
						<button type="button" class="remove-field">Remove</button>
					</div>
				`;
				$('#fields-container').append(fieldHtml);
			}
			
			// Remove field
			$(document).on('click', '.remove-field', function() {
				$(this).closest('.ennu-field-row').remove();
			});
			
			// Validate schema
			$('#validate-schema').on('click', function() {
				const fields = getFields();
				if (fields.length === 0) {
					alert('Please add at least one field.');
					return;
				}
				
				$.post(ajaxurl, {
					action: 'ennu_validate_schema',
					nonce: nonce,
					fields: fields
				})
				.done(function(response) {
					if (response.success) {
						alert('✅ Schema is valid!');
					} else {
						alert('❌ ' + response.data);
					}
				});
			});
			
			// Create fields
			$('#create-fields').on('click', function() {
				const objectType = $('#custom-object-type').val();
				const fields = getFields();
				
				if (!objectType) {
					alert('Please select an object type.');
					return;
				}
				
				if (fields.length === 0) {
					alert('Please add at least one field.');
					return;
				}
				
				$.post(ajaxurl, {
					action: 'ennu_create_fields',
					nonce: nonce,
					object_type: objectType,
					fields: fields
				})
				.done(function(response) {
					if (response.success) {
						$('#results').html(response.data).show();
					} else {
						$('#results').html('❌ ' + response.data).show();
					}
				});
			});
			
			function getFields() {
				const fields = [];
				$('.ennu-field-row').each(function() {
					const name = $(this).find('.field-name').val();
					const type = $(this).find('.field-type').val();
					const required = $(this).find('.field-required').is(':checked');
					
					if (name && type) {
						fields.push({
							name: name,
							type: type,
							required: required
						});
					}
				});
				return fields;
			}
			
			// Sync Settings Controls
			$('#save-sync-settings').on('click', function() {
				const syncEnabled = $('#sync-enabled').is(':checked');
				
				$.post(ajaxurl, {
					action: 'ennu_save_sync_settings',
					nonce: nonce,
					sync_enabled: syncEnabled
				})
				.done(function(response) {
					if (response.success) {
						alert('✅ Sync settings saved successfully!');
						location.reload();
					} else {
						alert('❌ ' + response.data);
					}
				});
			});
			
			// Retry Failed Syncs
			$('#retry-failed-syncs').on('click', function() {
				if (!confirm('This will attempt to retry all failed syncs. Continue?')) {
					return;
				}
				
				$.post(ajaxurl, {
					action: 'ennu_retry_failed_syncs',
					nonce: nonce
				})
				.done(function(response) {
					if (response.success) {
						alert('✅ Retry process completed. Check the status below.');
						location.reload();
					} else {
						alert('❌ ' + response.data);
					}
				});
			});

			// Preview Global Fields
			$('#preview-global-fields').on('click', function() {
				const button = $(this);
				const preview = $('#global-fields-preview');
				const createButton = $('#create-global-fields');
				
				button.prop('disabled', true).text('Loading Preview...');
				preview.hide();
				
				$.post(ajaxurl, {
					action: 'ennu_preview_global_fields',
					nonce: nonce
				})
				.done(function(response) {
					if (response.success) {
						displayGlobalFieldsPreview(response.data);
						createButton.show();
					} else {
						preview.addClass('error').html('❌ ' + response.data.message).show();
					}
				})
				.fail(function() {
					preview.addClass('error').html('❌ Preview failed').show();
				})
				.always(function() {
					button.prop('disabled', false).text('👁️ Preview Global Fields');
				});
			});

			// Create Global Fields
			$('#create-global-fields').on('click', function() {
				if (!confirm('This will create all 6 global shared fields used across ALL assessments. Continue?')) {
					return;
				}
				
				const button = $(this);
				const status = $('#global-fields-creation-status');
				
				button.prop('disabled', true).text('Creating Global Fields...');
				status.removeClass('success error').html('🔄 Creating global fields...').show();
				
				$.post(ajaxurl, {
					action: 'ennu_create_global_fields',
					nonce: nonce
				})
				.done(function(response) {
					if (response.success) {
						let statusHtml = '✅ ' + response.data.message;
						
						if (response.data.summary) {
							statusHtml += '<br><br><strong>Summary:</strong><br>';
							statusHtml += '• New Fields Created: ' + response.data.summary.new_fields + '<br>';
							statusHtml += '• Existing Fields Skipped: ' + response.data.summary.skipped_fields + '<br>';
							statusHtml += '• Total Fields Processed: ' + response.data.summary.total_processed;
						}
						
						if (response.data.result && response.data.result.skipped_fields && response.data.result.skipped_fields.length > 0) {
							statusHtml += '<br><br><strong>Skipped Fields:</strong><br>';
							statusHtml += response.data.result.skipped_fields.join(', ');
						}
						
						status.addClass('success').html(statusHtml).show();
					} else {
						status.addClass('error').html('❌ ' + response.data.message + '<br><br><strong>Results:</strong><br>' + JSON.stringify(response.data.result, null, 2)).show();
					}
				})
				.fail(function() {
					status.addClass('error').html('❌ Global field creation failed').show();
				})
				.always(function() {
					button.prop('disabled', false).text('🚀 Create Global Fields (6 total)');
				});
			});

			// Delete Global Fields
			$('#delete-global-fields').on('click', function() {
				if (confirm('⚠️ WARNING: This will permanently delete ALL global shared fields from HubSpot!\n\nThis action cannot be undone. Are you sure you want to proceed?')) {
					var button = $(this);
					button.prop('disabled', true).text('🗑️ Deleting...');
					
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'ennu_delete_global_fields',
							nonce: '<?php echo wp_create_nonce( 'ennu_hubspot_nonce' ); ?>'
						},
						success: function(response) {
							if (response.success) {
								alert('✅ ' + response.data.message);
								// Refresh the preview to show updated status
								$('#preview-global-fields').click();
							} else {
								alert('❌ Error: ' + response.data);
							}
						},
						error: function() {
							alert('❌ Network error occurred');
						},
						complete: function() {
							button.prop('disabled', false).text('🗑️ Delete Global Fields');
						}
					});
				}
			});

			// Preview Weight Loss Fields
			$('#preview-weight-loss-fields').on('click', function() {
				const button = $(this);
				const preview = $('#weight-loss-preview');
				const createButton = $('#create-weight-loss-fields');
				
				button.prop('disabled', true).text('Loading Preview...');
				preview.hide();
				
				$.post(ajaxurl, {
					action: 'ennu_preview_weight_loss_fields',
					nonce: nonce
				})
				.done(function(response) {
					if (response.success) {
						displayWeightLossPreview(response.data);
						createButton.show();
					} else {
						preview.addClass('error').html('❌ ' + response.data.message).show();
					}
				})
				.fail(function() {
					preview.addClass('error').html('❌ Preview failed').show();
				})
				.always(function() {
					button.prop('disabled', false).text('👁️ Preview Weight Loss Fields');
				});
			});

			// Create Weight Loss Fields
			$('#create-weight-loss-fields').on('click', function() {
				if (!confirm('This will create all 22 Weight Loss assessment fields (11 contact properties + 11 custom object fields). This may take a few minutes. Continue?')) {
					return;
				}
				
				const button = $(this);
				const status = $('#weight-loss-creation-status');
				
				button.prop('disabled', true).text('Creating Weight Loss Fields...');
				status.removeClass('success error').html('🔄 Creating Weight Loss fields...').show();
				
				$.post(ajaxurl, {
					action: 'ennu_create_weight_loss_fields',
					nonce: nonce
				})
				.done(function(response) {
					if (response.success) {
						let statusHtml = '✅ ' + response.data.message;
						
						if (response.data.summary) {
							statusHtml += '<br><br><strong>Summary:</strong><br>';
							statusHtml += '• New Fields Created: ' + response.data.summary.new_fields + '<br>';
							statusHtml += '• Existing Fields Skipped: ' + response.data.summary.skipped_fields + '<br>';
							statusHtml += '• Total Fields Processed: ' + response.data.summary.total_processed;
						}
						
						if (response.data.result && response.data.result.contacts && response.data.result.contacts.skipped_fields && response.data.result.contacts.skipped_fields.length > 0) {
							statusHtml += '<br><br><strong>Skipped Contact Fields:</strong><br>';
							statusHtml += response.data.result.contacts.skipped_fields.join(', ');
						}
						
						if (response.data.result && response.data.result.custom_object && response.data.result.custom_object.skipped_fields && response.data.result.custom_object.skipped_fields.length > 0) {
							statusHtml += '<br><br><strong>Skipped Custom Object Fields:</strong><br>';
							statusHtml += response.data.result.custom_object.skipped_fields.join(', ');
						}
						
						status.addClass('success').html(statusHtml).show();
					} else {
						status.addClass('error').html('❌ ' + response.data.message + '<br><br><strong>Results:</strong><br>' + JSON.stringify(response.data.result, null, 2)).show();
					}
				})
				.fail(function() {
					status.addClass('error').html('❌ Weight Loss field creation failed').show();
				})
				.always(function() {
					button.prop('disabled', false).text('🚀 Create Weight Loss Fields (72 total)');
				});
			});

			// Delete Weight Loss Fields
			$('#delete-weight-loss-fields').on('click', function() {
				if (confirm('⚠️ WARNING: This will permanently delete ALL Weight Loss fields from HubSpot!\n\nThis action cannot be undone. Are you sure you want to proceed?')) {
					var button = $(this);
					button.prop('disabled', true).text('🗑️ Deleting...');
					
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'ennu_delete_weight_loss_fields',
							nonce: '<?php echo wp_create_nonce( 'ennu_hubspot_nonce' ); ?>'
						},
						success: function(response) {
							if (response.success) {
								alert('✅ ' + response.data.message);
								// Refresh the preview to show updated status
								$('#preview-weight-loss-fields').click();
							} else {
								alert('❌ Error: ' + response.data);
							}
						},
						error: function() {
							alert('❌ Network error occurred');
						},
						complete: function() {
							button.prop('disabled', false).text('🗑️ Delete Weight Loss Fields');
						}
					});
				}
			});

			// Refresh Field Cache
			$('#refresh-field-cache').on('click', function() {
				var button = $(this);
				button.prop('disabled', true).text('🔄 Refreshing...');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'ennu_debug_hubspot_fields',
						nonce: '<?php echo wp_create_nonce( 'ennu_hubspot_nonce' ); ?>'
					},
					success: function(response) {
						if (response.success) {
							alert('✅ Field cache refreshed! Found ' + response.data.contact_properties.count + ' contact properties and ' + response.data.custom_object_properties.count + ' custom object properties.');
							// Refresh the preview to show updated status
							$('#preview-weight-loss-fields').click();
						} else {
							alert('❌ Error refreshing field cache: ' + response.data.message);
						}
					},
					error: function() {
						alert('❌ Network error occurred');
					},
					complete: function() {
						button.prop('disabled', false).text('🔄 Refresh Field Cache');
					}
				});
			});

			// Generic Assessment Field Handlers
			const assessments = [
				{ id: 'hormone', name: 'Hormone', emoji: '🔄' },
				{ id: 'sleep', name: 'Sleep', emoji: '😴' },
				{ id: 'health', name: 'Health', emoji: '🏥' },
				{ id: 'health-optimization', name: 'Health Optimization', emoji: '⚡' },
				{ id: 'skin', name: 'Skin', emoji: '✨' },
				{ id: 'hair', name: 'Hair', emoji: '💇' },
				{ id: 'ed-treatment', name: 'ED Treatment', emoji: '💊' },
				{ id: 'menopause', name: 'Menopause', emoji: '🌺' },
				{ id: 'testosterone', name: 'Testosterone', emoji: '💪' },
				{ id: 'welcome', name: 'Welcome', emoji: '👋' }
			];

			assessments.forEach(function(assessment) {
				// Preview Assessment Fields
				$('#preview-' + assessment.id + '-fields').on('click', function() {
					const button = $(this);
					const preview = $('#' + assessment.id + '-preview');
					const createButton = $('#create-' + assessment.id + '-fields');
					
					button.prop('disabled', true).text('Loading Preview...');
					preview.hide();
					
					$.post(ajaxurl, {
						action: 'ennu_preview_assessment_fields',
						assessment: assessment.id,
						nonce: nonce
					})
					.done(function(response) {
						if (response.success) {
							displayAssessmentPreview(response.data, assessment);
							createButton.show();
						} else {
							preview.addClass('error').html('❌ ' + response.data.message).show();
						}
					})
					.fail(function() {
						preview.addClass('error').html('❌ Preview failed').show();
					})
					.always(function() {
						button.prop('disabled', false).text('👁️ Preview ' + assessment.name + ' Fields');
					});
				});

				// Create Assessment Fields
				$('#create-' + assessment.id + '-fields').on('click', function() {
					if (!confirm('This will create all fields for the ' + assessment.name + ' assessment. Continue?')) {
						return;
					}
					
					const button = $(this);
					const status = $('#' + assessment.id + '-creation-status');
					
					button.prop('disabled', true).text('Creating ' + assessment.name + ' Fields...');
					status.removeClass('success error').html('🔄 Creating ' + assessment.name + ' fields...').show();
					
					$.post(ajaxurl, {
						action: 'ennu_create_assessment_fields',
						assessment: assessment.id,
						nonce: nonce
					})
					.done(function(response) {
						if (response.success) {
							let statusHtml = '✅ ' + response.data.message;
							
							if (response.data.summary) {
								statusHtml += '<br><br><strong>Summary:</strong><br>';
								statusHtml += '• New Fields Created: ' + response.data.summary.new_fields + '<br>';
								statusHtml += '• Existing Fields Skipped: ' + response.data.summary.skipped_fields + '<br>';
								statusHtml += '• Total Fields Processed: ' + response.data.summary.total_processed;
							}
							
							status.addClass('success').html(statusHtml).show();
						} else {
							status.addClass('error').html('❌ ' + response.data.message).show();
						}
					})
					.fail(function() {
						status.addClass('error').html('❌ ' + assessment.name + ' field creation failed').show();
					})
					.always(function() {
						button.prop('disabled', false).text('🚀 Create ' + assessment.name + ' Fields');
					});
				});

				// Delete Assessment Fields
				$('#delete-' + assessment.id + '-fields').on('click', function() {
					if (confirm('⚠️ WARNING: This will permanently delete ALL ' + assessment.name + ' fields from HubSpot!\n\nThis action cannot be undone. Are you sure you want to proceed?')) {
						var button = $(this);
						button.prop('disabled', true).text('🗑️ Deleting...');
						
						$.ajax({
							url: ajaxurl,
							type: 'POST',
							data: {
								action: 'ennu_delete_assessment_fields',
								assessment: assessment.id,
								nonce: '<?php echo wp_create_nonce( 'ennu_hubspot_nonce' ); ?>'
							},
							success: function(response) {
								if (response.success) {
									alert('✅ ' + response.data.message);
									// Refresh the preview to show updated status
									$('#preview-' + assessment.id + '-fields').click();
								} else {
									alert('❌ Error: ' + response.data);
								}
							},
							error: function() {
								alert('❌ Network error occurred');
							},
							complete: function() {
								button.prop('disabled', false).text('🗑️ Delete ' + assessment.name + ' Fields');
							}
						});
					}
				});

				// Refresh Field Cache for Assessment
				$('#refresh-' + assessment.id + '-field-cache').on('click', function() {
					var button = $(this);
					button.prop('disabled', true).text('🔄 Refreshing...');
					
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'ennu_debug_hubspot_fields',
							nonce: '<?php echo wp_create_nonce( 'ennu_hubspot_nonce' ); ?>'
						},
						success: function(response) {
							if (response.success) {
								alert('✅ Field cache refreshed! Found ' + response.data.contact_properties.count + ' contact properties and ' + response.data.custom_object_properties.count + ' custom object properties.');
								// Refresh the preview to show updated status
								$('#preview-' + assessment.id + '-fields').click();
							} else {
								alert('❌ Error refreshing field cache: ' + response.data.message);
							}
						},
						error: function() {
							alert('❌ Network error occurred');
						},
						complete: function() {
							button.prop('disabled', false).text('🔄 Refresh Field Cache');
						}
					});
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * AJAX handler for getting custom objects
	 *
	 * @since 64.2.1
	 */
	public function ajax_get_objects() {
		error_log( 'ENNU Life: ajax_get_objects called' );
		
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			error_log( 'ENNU Life: ajax_get_objects - unauthorized user' );
			wp_die( 'Unauthorized' );
		}

		$objects = $this->get_custom_objects();
		
		if ( is_wp_error( $objects ) ) {
			error_log( 'ENNU Life: ajax_get_objects - error: ' . $objects->get_error_message() );
			wp_send_json_error( $objects->get_error_message() );
		}

		error_log( 'ENNU Life: ajax_get_objects - success, returning ' . count( $objects ) . ' objects' );
		wp_send_json_success( $objects );
	}

	/**
	 * AJAX handler for validating field schema
	 *
	 * @since 64.2.1
	 */
	public function ajax_validate_schema() {
		error_log( 'ENNU Life: ajax_validate_schema called' );
		
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			error_log( 'ENNU Life: ajax_validate_schema - unauthorized user' );
			wp_die( 'Unauthorized' );
		}

		$fields = isset( $_POST['fields'] ) ? $this->sanitize_fields( $_POST['fields'] ) : array();

		if ( empty( $fields ) ) {
			error_log( 'ENNU Life: ajax_validate_schema - no fields provided' );
			wp_send_json_error( 'No fields provided' );
		}

		$validation_result = $this->validate_field_schema( $fields );
		
		if ( is_wp_error( $validation_result ) ) {
			error_log( 'ENNU Life: ajax_validate_schema - validation error: ' . $validation_result->get_error_message() );
			wp_send_json_error( $validation_result->get_error_message() );
		}

		error_log( 'ENNU Life: ajax_validate_schema - validation successful' );
		wp_send_json_success( 'Schema validation successful' );
	}

	/**
	 * AJAX handler for bulk creating fields
	 *
	 * @since 64.2.1
	 */
	public function ajax_bulk_create_fields() {
		error_log( 'ENNU Life: ajax_bulk_create_fields called' );
		
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			error_log( 'ENNU Life: ajax_bulk_create_fields - unauthorized user' );
			wp_die( 'Unauthorized' );
		}

		$object_type = sanitize_text_field( $_POST['object_type'] );
		$fields      = isset( $_POST['fields'] ) ? $this->sanitize_fields( $_POST['fields'] ) : array();

		if ( empty( $object_type ) || empty( $fields ) ) {
			error_log( 'ENNU Life: ajax_bulk_create_fields - missing parameters' );
			wp_send_json_error( 'Missing required parameters' );
		}

		$results = $this->bulk_create_fields( $object_type, $fields );
		
		if ( is_wp_error( $results ) ) {
			error_log( 'ENNU Life: ajax_bulk_create_fields - error: ' . $results->get_error_message() );
			wp_send_json_error( $results->get_error_message() );
		}

		error_log( 'ENNU Life: ajax_bulk_create_fields - success' );
		wp_send_json_success( $this->format_results( $results ) );
	}

	/**
	 * AJAX handler for testing HubSpot API connection
	 *
	 * @since 64.2.1
	 */
	public function ajax_test_api() {
		error_log( 'ENNU Life: ajax_test_api called' );
		
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			error_log( 'ENNU Life: ajax_test_api - unauthorized user' );
			wp_die( 'Unauthorized' );
		}

		$this->init_api_params(); // Re-initialize params in case they were changed by WP Fusion

		$test_result = $this->test_api_connection();

		if ( is_wp_error( $test_result ) ) {
			error_log( 'ENNU Life: ajax_test_api - error: ' . $test_result->get_error_message() );
			wp_send_json_error( $test_result->get_error_message() );
		}

		error_log( 'ENNU Life: ajax_test_api - success' );
		wp_send_json_success( 'HubSpot API connection test successful!' );
	}

	/**
	 * AJAX handler for getting available assessments
	 *
	 * @since 64.6.91
	 */
	public function ajax_get_assessments() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );
		
		$assessments = $this->get_available_assessments();
		
		if ( is_wp_error( $assessments ) ) {
			wp_send_json_error( $assessments->get_error_message() );
		}
		
		wp_send_json_success( $assessments );
	}
	
	/**
	 * AJAX handler for previewing assessment fields
	 *
	 * @since 64.6.91
	 */
	public function ajax_preview_assessment_fields() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );
		
		// Check for both possible parameter names
		$assessment_name = isset( $_POST['assessment_name'] ) ? sanitize_text_field( $_POST['assessment_name'] ) : '';
		if ( empty( $assessment_name ) ) {
			$assessment_name = isset( $_POST['assessment'] ) ? sanitize_text_field( $_POST['assessment'] ) : '';
		}
		
		if ( empty( $assessment_name ) ) {
			wp_send_json_error( 'Assessment name is required.' );
		}
		
		error_log( 'ENNU HubSpot: Previewing assessment fields for: ' . $assessment_name );
		
		$fields = $this->extract_assessment_fields_by_name( $assessment_name );
		
		if ( is_wp_error( $fields ) ) {
			wp_send_json_error( $fields->get_error_message() );
		}
		
		// Get existing field details from HubSpot to include IDs
		$existing_contact_properties = $this->get_existing_properties( 'contacts' );
		
		// Check if custom object exists before trying to get its properties
		$existing_custom_object_properties = array();
		$custom_object_exists = false;
		
		// Test if custom object exists
		$custom_object_name = $this->get_custom_object_name();
		$test_url = "https://api.hubapi.com/crm/v3/schemas/{$custom_object_name}/properties";
		$test_response = wp_remote_get( $test_url, array(
			'headers' => $this->api_params['headers'],
			'timeout' => 10
		) );
		
		if ( ! is_wp_error( $test_response ) && wp_remote_retrieve_response_code( $test_response ) === 200 ) {
			$custom_object_exists = true;
			$existing_custom_object_properties = $this->get_existing_properties( $this->get_custom_object_name() );
		} else {
			error_log( "ENNU HubSpot: Custom object {$custom_object_name} does not exist yet. Skipping custom object field creation." );
		}
		
		// Add HubSpot field IDs to contact fields
		foreach ( $fields['contact_fields'] as &$field ) {
			if ( isset( $existing_contact_properties[$field['name']] ) ) {
				// Field exists in HubSpot - use the actual HubSpot ID
				$field['hubspot_id'] = $existing_contact_properties[$field['name']]['id'];
				$field['hubspot_name'] = $existing_contact_properties[$field['name']]['name'];
			} else {
				// Field doesn't exist yet - preserve the hubspot_id that was already set correctly by generate_hubspot_field_id()
				$field['hubspot_name'] = $field['name'];
				// Ensure hubspot_id is preserved from extract_assessment_fields_by_name
				if ( ! isset( $field['hubspot_id'] ) || empty( $field['hubspot_id'] ) ) {
					// If hubspot_id is not set, generate it now
					$field['hubspot_id'] = $this->generate_hubspot_field_id( $field['wordpress_field_key'], $assessment_name );
				}
			}
		}
		
		// Add HubSpot field IDs to custom object fields (only if custom object exists)
		if ( $custom_object_exists ) {
			foreach ( $fields['custom_object_fields'] as &$field ) {
				if ( isset( $existing_custom_object_properties[$field['name']] ) ) {
					// Field exists in HubSpot - use the actual HubSpot ID
					$field['hubspot_id'] = $existing_custom_object_properties[$field['name']]['id'];
					$field['hubspot_name'] = $existing_custom_object_properties[$field['name']]['name'];
				} else {
					// Field doesn't exist yet - preserve the hubspot_id that was already set correctly by generate_hubspot_field_id()
					$field['hubspot_name'] = $field['name'];
					// Ensure hubspot_id is preserved from extract_assessment_fields_by_name
					if ( ! isset( $field['hubspot_id'] ) || empty( $field['hubspot_id'] ) ) {
						// If hubspot_id is not set, generate it now
						$field['hubspot_id'] = $this->generate_hubspot_field_id( $field['wordpress_field_key'], $assessment_name, true );
					}
				}
			}
		} else {
			// If custom object doesn't exist, mark custom object fields as not available
			foreach ( $fields['custom_object_fields'] as &$field ) {
				$field['hubspot_name'] = $field['name'];
				$field['hubspot_id'] = $this->generate_hubspot_field_id( $field['wordpress_field_key'], $assessment_name, true );
				$field['not_available'] = true; // Mark as not available
			}
		}
		
		// Structure the data to match what the JavaScript expects
		$preview_data = array(
			'assessment_title' => $fields['assessment_title'],
			'contact_fields' => array(
				'count' => count( $fields['contact_fields'] ),
				'fields' => $fields['contact_fields']
			),
			'custom_object_fields' => array(
				'count' => count( $fields['custom_object_fields'] ),
				'fields' => $fields['custom_object_fields']
			),
			'total_fields' => count( $fields['contact_fields'] ) + count( $fields['custom_object_fields'] ),
			'existence_status' => null // We'll add this later if needed
		);
		
		wp_send_json_success( $preview_data );
	}

	/**
	 * AJAX handler for creating assessment fields
	 *
	 * @since 64.6.99
	 */
	public function ajax_create_assessment_fields() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		// Check for both possible parameter names
		$assessment_name = isset( $_POST['assessment_name'] ) ? sanitize_text_field( $_POST['assessment_name'] ) : '';
		if ( empty( $assessment_name ) ) {
			$assessment_name = isset( $_POST['assessment'] ) ? sanitize_text_field( $_POST['assessment'] ) : '';
		}

		if ( empty( $assessment_name ) ) {
			wp_send_json_error( 'Assessment name is required.' );
		}

		error_log( 'ENNU HubSpot: Creating assessment fields for: ' . $assessment_name );

		try {
			$result = $this->create_assessment_fields( $assessment_name );
			
			// Add a delay to allow HubSpot to process the changes
			if ( $result['success'] ) {
				error_log( 'ENNU HubSpot: Waiting 3 seconds for HubSpot to process field creation...' );
				sleep( 3 );
			}
			
			// Build detailed message
			$message = $assessment_name . ' field creation completed!';
			$details = array();
			
			if ( isset( $result['contacts']['new_fields_count'] ) && $result['contacts']['new_fields_count'] > 0 ) {
				$details[] = 'Contact Properties: ' . $result['contacts']['new_fields_count'] . ' new fields created';
			}
			if ( isset( $result['contacts']['skipped_fields_count'] ) && $result['contacts']['skipped_fields_count'] > 0 ) {
				$details[] = 'Contact Properties: ' . $result['contacts']['skipped_fields_count'] . ' existing fields skipped';
			}
			if ( isset( $result['custom_object']['new_fields_count'] ) && $result['custom_object']['new_fields_count'] > 0 ) {
				$details[] = 'Custom Object Fields: ' . $result['custom_object']['new_fields_count'] . ' new fields created';
			}
			if ( isset( $result['custom_object']['skipped_fields_count'] ) && $result['custom_object']['skipped_fields_count'] > 0 ) {
				$details[] = 'Custom Object Fields: ' . $result['custom_object']['skipped_fields_count'] . ' existing fields skipped';
			}
			
			if ( ! empty( $details ) ) {
				$message .= ' (' . implode( ', ', $details ) . ')';
			}
			
			$total_new = ( isset( $result['contacts']['new_fields_count'] ) ? $result['contacts']['new_fields_count'] : 0 ) + 
						 ( isset( $result['custom_object']['new_fields_count'] ) ? $result['custom_object']['new_fields_count'] : 0 );
			$total_skipped = ( isset( $result['contacts']['skipped_fields_count'] ) ? $result['contacts']['skipped_fields_count'] : 0 ) + 
							 ( isset( $result['custom_object']['skipped_fields_count'] ) ? $result['custom_object']['skipped_fields_count'] : 0 );
			
			if ( $total_new > 0 ) {
				wp_send_json_success( array(
					'message' => $message,
					'result' => $result,
					'summary' => array(
						'new_fields' => $total_new,
						'skipped_fields' => $total_skipped,
						'total_processed' => $total_new + $total_skipped
					)
				) );
			} else {
				wp_send_json_error( array(
					'message' => 'No new fields were created. All fields may already exist.',
					'result' => $result
				) );
			}
		} catch ( Exception $e ) {
			error_log( 'ENNU HubSpot: Error creating assessment fields: ' . $e->getMessage() );
			wp_send_json_error( 'Error creating assessment fields: ' . $e->getMessage() );
		}
	}

	/**
	 * Test API connection with retry mechanism
	 *
	 * @since 64.6.81
	 * @return array Test result
	 */
	public function test_api_connection_with_retry() {
		$max_attempts = 3;
		$attempt = 1;
		
		while ( $attempt <= $max_attempts ) {
			error_log( "ENNU HubSpot: API connection attempt {$attempt} of {$max_attempts}" );
			
			$result = $this->test_api_connection();
			
			if ( $result['success'] ) {
				return $result;
			}
			
			if ( $attempt < $max_attempts ) {
				$delay = $attempt * 2; // Exponential backoff
				error_log( "ENNU HubSpot: API connection failed, retrying in {$delay} seconds..." );
				sleep( $delay );
			}
			
			$attempt++;
		}
		
		return array(
			'success' => false,
			'error' => 'API connection failed after ' . $max_attempts . ' attempts'
		);
	}

	/**
	 * AJAX handler for testing API with retry mechanism
	 *
	 * @since 64.6.81
	 */
	public function ajax_test_api_with_retry() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$result = $this->test_api_connection_with_retry();
		
		if ( $result['success'] ) {
			wp_send_json_success( $result['message'] );
		} else {
			wp_send_json_error( $result['error'] );
		}
	}

	/**
	 * AJAX handler for validating HubSpot connection
	 *
	 * @since 64.6.81
	 */
	public function ajax_validate_hubspot_connection() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$validation_results = array(
			'api_connection' => $this->test_api_connection_with_retry(),
			'access_token_valid' => ! empty( $this->api_params['headers']['Authorization'] ),
			'api_base_url' => $this->api_base_url,
			'timestamp' => current_time( 'mysql' )
		);

		$all_success = true;
		foreach ( $validation_results as $key => $result ) {
			if ( is_array( $result ) && isset( $result['success'] ) && ! $result['success'] ) {
				$all_success = false;
			}
		}

		if ( $all_success ) {
			wp_send_json_success( array(
				'message' => 'HubSpot connection validated successfully',
				'details' => $validation_results
			) );
		} else {
			wp_send_json_error( array(
				'message' => 'HubSpot connection validation failed',
				'details' => $validation_results
			) );
		}
	}


	
	/**
	 * AJAX handler for importing assessment fields with conflict detection
	 *
	 * @since 64.2.1
	 */
	public function ajax_import_assessment_fields() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_hubspot_nonce' ) ) {
			wp_send_json_error( 'Security check failed.' );
		}
		
		$assessment_name = sanitize_text_field( $_POST['assessment_name'] );
		$object_type = sanitize_text_field( $_POST['object_type'] );
		
		if ( empty( $assessment_name ) ) {
			wp_send_json_error( 'Assessment name is required.' );
		}
		
		// Set memory and time limits for large imports
		ini_set( 'memory_limit', '512M' );
		set_time_limit( 300 );
		
		// Extract fields for the specific assessment
		$assessment_data = $this->extract_assessment_fields_by_name( $assessment_name );
		
		if ( is_wp_error( $assessment_data ) ) {
			wp_send_json_error( $assessment_data->get_error_message() );
		}
		
		$fields = $assessment_data['fields'];
		
		if ( empty( $fields ) ) {
			wp_send_json_error( "No fields found for assessment: {$assessment_name}" );
		}
		
		// Get existing properties for conflict detection
		$existing_properties = $this->get_existing_properties( $object_type );
		
		$results = array(
			'successes' => 0,
			'errors' => 0,
			'conflicts' => 0,
			'details' => array()
		);
		
		foreach ( $fields as $field ) {
			try {
				// Check for conflicts
				if ( in_array( $field['name'], $existing_properties ) ) {
					$results['conflicts']++;
					$results['details'][] = array(
						'field' => $field['name'],
						'status' => 'conflict',
						'message' => "Property '{$field['name']}' already exists. Skipped to preserve data."
					);
					continue;
				}
				
				// Create the field
				$result = $this->create_single_field( $object_type, $field, $assessment_name );
				
				if ( $result['success'] ) {
					$results['successes']++;
					$results['details'][] = array(
						'field' => $field['name'],
						'status' => 'success',
						'message' => "Successfully created property '{$field['name']}'"
					);
				} else {
					$results['errors']++;
					$results['details'][] = array(
						'field' => $field['name'],
						'status' => 'error',
						'message' => $result['message']
					);
				}
			} catch ( Exception $e ) {
				$results['errors']++;
				$results['details'][] = array(
					'field' => $field['name'],
					'status' => 'error',
					'message' => "Exception: " . $e->getMessage()
				);
			}
		}
		
		// Create summary message
		$summary = "Import completed: {$results['successes']} created, {$results['conflicts']} skipped (already exist), {$results['errors']} failed.";
		
		wp_send_json_success( array(
			'message' => $summary,
			'results' => $results
		) );
	}

	/**
	 * Test HubSpot API connection
	 *
	 * @since 64.6.99
	 */
	public function test_api_connection() {
		$user_id = get_current_user_id();
		$user = get_user_by( 'ID', $user_id );
		
		error_log( "ENNU HubSpot: Testing API connection for user {$user_id}" );
		
		// Test basic API call
		$url = $this->api_base_url . '/crm/v3/objects/contacts/search';
		$data = array(
			'filterGroups' => array(
				array(
					'filters' => array(
						array(
							'propertyName' => 'email',
							'operator' => 'EQ',
							'value' => $user->user_email
						)
					)
				)
			),
			'limit' => 1
		);
		
		error_log( "ENNU HubSpot: Testing URL: {$url}" );
		error_log( "ENNU HubSpot: Testing data: " . json_encode( $data ) );
		error_log( "ENNU HubSpot: API headers: " . json_encode( $this->api_params['headers'] ) );
		
		$response = wp_remote_post( $url, array(
			'headers' => $this->api_params['headers'],
			'body' => json_encode( $data ),
			'timeout' => 30
		) );

		if ( is_wp_error( $response ) ) {
			error_log( "ENNU HubSpot: WP Error: " . $response->get_error_message() );
			return array( 'success' => false, 'error' => $response->get_error_message() );
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		
		error_log( "ENNU HubSpot: Response status: {$status_code}" );
		error_log( "ENNU HubSpot: Response body: " . $body );
		
		if ( $status_code === 200 ) {
			error_log( "ENNU HubSpot: API connection successful!" );
			return array( 'success' => true, 'status_code' => $status_code, 'body' => $body );
		} else {
			error_log( "ENNU HubSpot: API connection failed with status {$status_code}" );
			return array( 'success' => false, 'status_code' => $status_code, 'body' => $body );
		}
	}

	/**
	 * Get custom objects from HubSpot
	 *
	 * @since 64.2.1
	 * @return array|WP_Error
	 */
	private function get_custom_objects() {
		$response = wp_remote_get( $this->api_base_url . '/crm/v3/schemas', $this->api_params );

		if ( is_wp_error( $response ) ) {
			error_log( 'ENNU Life: HubSpot API Error - ' . $response->get_error_message() );
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( $status_code !== 200 ) {
			error_log( 'ENNU Life: HubSpot API returned status code ' . $status_code );
			return new WP_Error( 'api_error', 'HubSpot API returned status code ' . $status_code );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		error_log( 'ENNU Life: HubSpot API Response - ' . print_r( $data, true ) );

		if ( ! $data ) {
			return new WP_Error( 'api_error', 'Invalid JSON response from HubSpot API' );
		}

		$objects = array();

		// Add standard objects
		$objects[] = array(
			'id'   => 'contacts',
			'name' => 'Contacts',
		);
		$objects[] = array(
			'id'   => 'companies',
			'name' => 'Companies',
		);
		$objects[] = array(
			'id'   => 'deals',
			'name' => 'Deals',
		);

		// Add custom objects if they exist
		if ( isset( $data['results'] ) && is_array( $data['results'] ) ) {
			foreach ( $data['results'] as $object ) {
				// Check for custom objects with various possible structures
				if ( isset( $object['objectTypeId'] ) ) {
					$object_id = $object['objectTypeId'];
					$object_name = isset( $object['labels']['singular'] ) ? $object['labels']['singular'] : $object_id;
					
					// Include all custom objects (not just p_ prefix)
					// Custom objects typically have numeric IDs like "2-12345678"
					if ( is_numeric( str_replace( '-', '', $object_id ) ) || 
						 strpos( $object_id, 'p_' ) === 0 || 
						 strpos( $object_id, 'custom_' ) === 0 ||
						 ( isset( $object['fullyQualifiedName'] ) && strpos( $object['fullyQualifiedName'], 'p_' ) === 0 ) ) {
						
						// Skip if it's already in our standard objects list
						$is_standard = false;
						foreach ( array( 'contacts', 'companies', 'deals', 'tickets' ) as $standard ) {
							if ( $object_id === $standard || $object_name === ucfirst( $standard ) ) {
								$is_standard = true;
								break;
							}
						}
						
						if ( ! $is_standard ) {
							$objects[] = array(
								'id'   => $object_id,
								'name' => $object_name,
							);
							error_log( 'ENNU Life: Found custom object - ID: ' . $object_id . ', Name: ' . $object_name );
						}
					}
				}
			}
		}

		// If no custom objects found, try alternative API endpoints
		if ( count( $objects ) <= 4 ) { // Only standard objects (including tickets)
			error_log( 'ENNU Life: No custom objects found in primary response, trying alternative endpoints' );
			
			// Try the custom objects endpoint
			$custom_response = wp_remote_get( $this->api_base_url . '/crm/v3/objects', $this->api_params );
			
			if ( ! is_wp_error( $custom_response ) ) {
				$custom_body = wp_remote_retrieve_body( $custom_response );
				$custom_data = json_decode( $custom_body, true );
				
				error_log( 'ENNU Life: HubSpot Custom Objects Response - ' . print_r( $custom_data, true ) );
				
				if ( $custom_data && isset( $custom_data['results'] ) ) {
					foreach ( $custom_data['results'] as $custom_object ) {
						if ( isset( $custom_object['objectType'] ) ) {
							$object_id = $custom_object['objectType'];
							$object_name = isset( $custom_object['name'] ) ? $custom_object['name'] : $object_id;
							
							// Include custom objects with various ID formats
							if ( is_numeric( str_replace( '-', '', $object_id ) ) || 
								 strpos( $object_id, 'p_' ) === 0 || 
								 strpos( $object_id, 'custom_' ) === 0 ) {
								
								$objects[] = array(
									'id'   => $object_id,
									'name' => $object_name,
								);
								error_log( 'ENNU Life: Found custom object via alternative endpoint - ID: ' . $object_id . ', Name: ' . $object_name );
							}
						}
					}
				}
			}
			
			// Try the properties endpoint to get object types
			$properties_response = wp_remote_get( $this->api_base_url . '/crm/v3/properties', $this->api_params );
			
			if ( ! is_wp_error( $properties_response ) ) {
				$properties_body = wp_remote_retrieve_body( $properties_response );
				$properties_data = json_decode( $properties_body, true );
				
				if ( $properties_data && isset( $properties_data['results'] ) ) {
					$found_object_types = array();
					foreach ( $properties_data['results'] as $property ) {
						if ( isset( $property['objectType'] ) ) {
							$found_object_types[] = $property['objectType'];
						}
					}
					
					$found_object_types = array_unique( $found_object_types );
					error_log( 'ENNU Life: Found object types from properties - ' . print_r( $found_object_types, true ) );
					
					// Add any custom object types found
					foreach ( $found_object_types as $object_type ) {
						if ( ! in_array( $object_type, array( 'contacts', 'companies', 'deals', 'tickets' ) ) ) {
							$objects[] = array(
								'id'   => $object_type,
								'name' => ucfirst( str_replace( array( 'p_', 'custom_' ), '', $object_type ) ),
							);
							error_log( 'ENNU Life: Added custom object from properties - ID: ' . $object_type );
						}
					}
				}
			}
		}

		error_log( 'ENNU Life: Final objects list - ' . print_r( $objects, true ) );

		return $objects;
	}

	/**
	 * Validate field schema
	 *
	 * @since 64.2.1
	 * @param array $fields Field definitions
	 * @return true|WP_Error
	 */
	private function validate_field_schema( $fields ) {
		foreach ( $fields as $field ) {
			// Check required fields (new structure: name, type, required)
			if ( empty( $field['name'] ) || empty( $field['type'] ) ) {
				return new WP_Error( 'validation_error', 'All fields must have name and type' );
			}

			// Validate field name format
			if ( ! preg_match( '/^[a-z][a-z0-9_]*$/', $field['name'] ) ) {
				return new WP_Error( 'validation_error', 'Field name must be lowercase with underscores only: ' . $field['name'] );
			}

			// Check if field type is supported
			if ( ! isset( $this->supported_field_types[ $field['type'] ] ) ) {
				return new WP_Error( 'validation_error', 'Unsupported field type: ' . $field['type'] );
			}
		}

		return true;
	}

	/**
	 * Bulk create fields in HubSpot
	 *
	 * @since 64.2.1
	 * @param string $object_type Object type ID
	 * @param array  $fields Field definitions
	 * @return array|WP_Error
	 */
	private function bulk_create_fields( $object_type, $fields ) {
		$results = array(
			'success' => array(),
			'errors'  => array(),
		);

		foreach ( $fields as $field ) {
			// Apply rate limiting between field creation requests
			$this->rate_limit_delay();
			
			$result = $this->create_single_field( $object_type, $field );
			
			if ( is_wp_error( $result ) ) {
				$results['errors'][] = array(
					'field' => $field['name'],
					'error' => $result->get_error_message(),
				);
			} elseif ( is_array( $result ) && isset( $result['success'] ) ) {
				// Handle new response format from create_single_field
				if ( $result['success'] ) {
					$results['success'][] = array(
						'field' => $field['name'],
						'message' => $result['message'],
						'data' => isset( $result['data'] ) ? $result['data'] : null,
					);
				} else {
					$results['errors'][] = array(
						'field' => $field['name'],
						'error' => $result['message'],
					);
				}
			} else {
				// Handle legacy response format (string field ID)
				$results['success'][] = array(
					'field' => $field['name'],
					'id'    => $result,
				);
			}
		}

		return $results;
	}

	/**
	 * Create or get assessment-specific property group
	 *
	 * @param string $object_type The object type
	 * @param string $assessment_name The assessment name
	 * @return string Property group name
	 */
	private function create_or_get_assessment_property_group( $object_type, $assessment_name ) {
		error_log( "ENNU HubSpot: create_or_get_assessment_property_group called for object_type: {$object_type}, assessment_name: {$assessment_name}" );
		
		if ( empty( $assessment_name ) ) {
			// Fallback to existing property groups
			$property_groups = $this->get_property_groups( $object_type );
			$fallback_group = ( ! is_wp_error( $property_groups ) && ! empty( $property_groups ) ) ? $property_groups[0]['name'] : 'core';
			error_log( "ENNU HubSpot: Using fallback property group: {$fallback_group}" );
			return $fallback_group;
		}

		// Format assessment name for property group with prefix
		$assessment_prefix = $this->get_assessment_prefix( $assessment_name );
		$assessment_label = str_replace( array( '-', '_' ), ' ', $assessment_name );
		$property_group_name = strtolower( $assessment_prefix . ' ' . $assessment_label . ' assessment' );
		
		error_log( "ENNU HubSpot: Generated property group name: {$property_group_name}" );

		// Check if property group already exists
		$existing_groups = $this->get_property_groups( $object_type );
		if ( ! is_wp_error( $existing_groups ) ) {
			error_log( "ENNU HubSpot: Found " . count( $existing_groups ) . " existing property groups" );
			error_log( "ENNU HubSpot: Looking for property group: {$property_group_name}" );
			foreach ( $existing_groups as $group ) {
				error_log( "ENNU HubSpot: Checking existing group: " . $group['name'] );
				if ( $group['name'] === $property_group_name ) {
					error_log( "ENNU HubSpot: Property group already exists: {$property_group_name}" );
					return $property_group_name;
				}
			}
			error_log( "ENNU HubSpot: Property group not found in existing groups, will create new one" );
		} else {
			error_log( "ENNU HubSpot: Error getting existing property groups: " . $existing_groups->get_error_message() );
		}

		// Create new property group
		error_log( "ENNU HubSpot: Creating new property group: {$property_group_name}" );
		$result = $this->create_property_group( $object_type, $property_group_name );
		if ( is_wp_error( $result ) ) {
			error_log( "ENNU HubSpot: Failed to create property group: " . $result->get_error_message() );
			// Fallback to existing groups if creation fails
			$property_groups = $this->get_property_groups( $object_type );
			$fallback_group = ( ! is_wp_error( $property_groups ) && ! empty( $property_groups ) ) ? $property_groups[0]['name'] : 'core';
			error_log( "ENNU HubSpot: Using fallback property group after creation failure: {$fallback_group}" );
			return $fallback_group;
		}

		error_log( "ENNU HubSpot: Successfully created property group: {$property_group_name}" );
		return $property_group_name;
	}

	/**
	 * Create a new property group in HubSpot
	 *
	 * @param string $object_type The object type
	 * @param string $group_name The property group name
	 * @return bool|WP_Error Success or error
	 */
	private function create_property_group( $object_type, $group_name ) {
		error_log( "ENNU HubSpot: create_property_group called for object_type: {$object_type}, group_name: {$group_name}" );
		
		$this->init_api_params();
		
		// For custom objects, use the object type ID
		if ( $object_type === $this->get_custom_object_name() ) {
			$url = $this->api_base_url . "/crm/v3/properties/{$object_type}/groups";
		} else {
			$url = $this->api_base_url . "/crm/v3/properties/{$object_type}/groups";
		}
		error_log( "ENNU HubSpot: Property group creation URL: {$url}" );
		
		$body = array(
			'name' => $group_name,
			'label' => $group_name,
			'displayOrder' => 1,
		);
		
		error_log( "ENNU HubSpot: Property group creation body: " . json_encode( $body ) );

		$response = wp_remote_post( $url, array(
			'headers' => $this->api_params['headers'],
			'body'    => json_encode( $body ),
			'timeout' => 30,
		) );

		if ( is_wp_error( $response ) ) {
			error_log( "ENNU HubSpot: Property group creation WP_Error: " . $response->get_error_message() );
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		
		error_log( "ENNU HubSpot: Property group creation response status: {$status_code}" );
		error_log( "ENNU HubSpot: Property group creation response body: {$body}" );

		if ( $status_code === 201 ) {
			error_log( "ENNU HubSpot: Property group created successfully" );
			return true;
		}

		$data = json_decode( $body, true );
		$error_message = isset( $data['message'] ) ? $data['message'] : 'Unknown error';
		error_log( "ENNU HubSpot: Property group creation failed: {$error_message}" );
		return new WP_Error( 'api_error', 'Failed to create property group: ' . $error_message );
	}

	/**
	 * Get available property groups from HubSpot
	 *
	 * @since 64.2.1
	 * @return array|WP_Error
	 */
	private function get_property_groups( $object_type ) {
		// For custom objects, use the object type ID
		if ( $object_type === $this->get_custom_object_name() ) {
			$url = $this->api_base_url . '/crm/v3/properties/' . $object_type . '/groups';
		} else {
			$url = $this->api_base_url . '/crm/v3/properties/' . $object_type . '/groups';
		}
		
		$response = wp_remote_get( $url, $this->api_params );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );

		if ( $status_code !== 200 ) {
			$data = json_decode( $body, true );
			$error_message = isset( $data['message'] ) ? $data['message'] : 'Unknown error';
			return new WP_Error( 'api_error', 'Failed to get property groups: ' . $error_message );
		}

		$data = json_decode( $body, true );
		return isset( $data['results'] ) ? $data['results'] : array();
	}

	/**
	 * Generate meaningful description for HubSpot field
	 *
	 * @param array $field Field data
	 * @param string $assessment_name Assessment name
	 * @return string Field description
	 */
	private function generate_field_description( $field, $assessment_name = null ) {
		$description = '';
		
		// Check if this is a global field
		if ( isset( $field['is_global'] ) && $field['is_global'] ) {
			$description = 'Global shared field used across multiple assessments. ';
		}
		
		// Check if this is a metadata field
		if ( isset( $field['groupName'] ) && $field['groupName'] === 'Metadata' ) {
			$description = 'Assessment metadata field for tracking and analytics. ';
		}
		
		// Check if this is a score field
		if ( strpos( $field['name'], 'Score' ) !== false ) {
			$description = 'Calculated assessment score for analytics and reporting. ';
		}
		
		// Check if this is a question field
		if ( strpos( $field['name'], 'Q' ) !== false && strpos( $field['name'], ' - ' ) !== false ) {
			$description = 'User response to assessment question. ';
		}
		
		// Add field type information
		switch ( $field['type'] ) {
			case 'string':
				$description .= 'Text field for storing user responses.';
				break;
			case 'number':
				$description .= 'Numeric field for storing scores and calculations.';
				break;
			case 'date':
				$description .= 'Date field for tracking timestamps and completion dates.';
				break;
			case 'enumeration':
				$description .= 'Dropdown field with predefined answer options.';
				break;
			case 'boolean':
				$description .= 'Yes/No field for binary responses.';
				break;
			default:
				$description .= 'Field created by ENNU Life Assessments plugin.';
		}
		
		// Add assessment context if available
		if ( $assessment_name ) {
			$assessment_title = ucfirst( str_replace( '-', ' ', $assessment_name ) );
			$description .= ' Part of the ' . $assessment_title . ' assessment.';
		}
		
		return $description;
	}

	/**
	 * Get the correct custom object name from HubSpot (with caching)
	 *
	 * @since 64.6.90
	 * @return string Custom object name
	 */
	private function get_custom_object_name() {
		// TEMPORARY HARDCODED RETURN TO PREVENT INFINITE LOOPS
		// This bypasses all caching and API calls to prevent database performance issues
		error_log( 'ENNU HubSpot: Using hardcoded custom object name to prevent infinite loops' );
		return '2-47128703';
	}

	/**
	 * Clear the cached custom object name
	 *
	 * @since 64.6.90
	 */
	private function clear_custom_object_cache() {
		delete_transient( 'ennu_hubspot_custom_object_name' );
		error_log( 'ENNU HubSpot: Cleared custom object name cache' );
	}

	/**
	 * Clear all HubSpot API caches
	 *
	 * @since 64.6.90
	 */
	public function clear_all_hubspot_caches() {
		delete_transient( 'ennu_hubspot_custom_object_name' );
		error_log( 'ENNU HubSpot: Cleared API caches' );
		// Force clear any cached object names
		wp_cache_delete( 'ennu_hubspot_custom_object_name', 'options' );
		// Clear object cache as well
		wp_cache_delete( 'ennu_hubspot_custom_object_name', 'transient' );
		error_log( 'ENNU HubSpot: Force cleared all caches for custom object name' );
	}

	/**
	 * AJAX handler to clear HubSpot API cache
	 *
	 * @since 64.6.90
	 */
	public function ajax_clear_hubspot_cache() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_clear_hubspot_cache' ) ) {
			wp_die( 'Security check failed' );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		// Clear the cache
		$this->clear_all_hubspot_caches();

		// Return success response
		wp_send_json_success( array(
			'message' => 'HubSpot API cache cleared successfully'
		) );
	}

	/**
	 * Add rate limiting delay to avoid hitting HubSpot API limits
	 *
	 * @since 64.6.90
	 */
	public function rate_limit_delay() {
		static $last_call_time = null;
		$current_time = microtime( true );
		
		if ( $last_call_time !== null ) {
			$time_since_last = $current_time - $last_call_time;
			if ( $time_since_last < 1 ) { // Minimum 1 second between calls
				$sleep_time = 1 - $time_since_last;
				usleep( (int)( $sleep_time * 1000000 ) ); // Convert to microseconds and cast to int
			}
		}
		
		$last_call_time = $current_time;
	}



	/**
	 * Create a single field in HubSpot
	 *
	 * @since 64.2.1
	 * @param string $object_type Object type ID
	 * @param array  $field Field definition
	 * @return string|WP_Error Field ID or error
	 */
	private function create_single_field( $object_type, $field, $assessment_name = null ) {
		// Validate required field data
		if ( empty( $field['name'] ) ) {
			error_log( 'ENNU HubSpot: Field name is required' );
			return array(
				'success' => false,
				'message' => 'Field name is required',
				'data' => null,
				'hubspot_id' => null
			);
		}
		
		if ( empty( $field['type'] ) ) {
			error_log( 'ENNU HubSpot: Field type is required for field: ' . $field['name'] );
			return array(
				'success' => false,
				'message' => 'Field type is required for field: ' . $field['name'],
				'data' => null,
				'hubspot_id' => null
			);
		}
		
		// Map field type to HubSpot fieldType
		$field_type_mapping = array(
			'string'      => 'text',
			'number'      => 'number',
			'date'        => 'date',
			'enumeration' => 'select',
			'boolean'     => 'booleancheckbox',
		);

		$fieldType = isset( $field_type_mapping[ $field['type'] ] ) ? $field_type_mapping[ $field['type'] ] : 'text';
		
		// Format label as "FIELDNAME - Question Title"
		$field_name_upper = strtoupper( $field['name'] );
		$label = isset( $field['label'] ) ? $field_name_upper . ' - ' . $field['label'] : $field_name_upper . ' - ' . $field['name'];

		// Create assessment-specific property group
		$property_group = $this->create_or_get_assessment_property_group( $object_type, $assessment_name );

		// Generate meaningful description based on field type and content
		$description = $this->generate_field_description( $field, $assessment_name );

		// Always use HubSpot field ID for the name, fallback to field name if not available
		$field_name = isset( $field['hubspot_id'] ) && ! empty( $field['hubspot_id'] ) ? $field['hubspot_id'] : $field['name'];
		
		$field_data = array(
			'name'        => $field_name,
			'label'       => $label,
			'type'        => $field['type'],
			'fieldType'   => $fieldType,
			'groupName'   => $property_group,
			'description' => $description,
		);

		// Add numberDisplayHint for number fields with decimal precision
		if ( 'number' === $field['type'] && isset( $field['numberDisplayHint'] ) ) {
			$field_data['numberDisplayHint'] = $field['numberDisplayHint'];
		}

		// Add required property if specified
		if ( isset( $field['required'] ) && $field['required'] ) {
			$field_data['required'] = true;
		}

		// Add field-specific properties for enumeration fields
		if ( 'enumeration' === $field['type'] && isset( $field['options'] ) && is_array( $field['options'] ) ) {
			$options = array();
			$display_order = 1;
			
			foreach ( $field['options'] as $option ) {
				$options[] = array(
					'label'        => $option['label'],
					'value'        => $option['value'],
					'displayOrder' => $display_order,
				);
				$display_order++;
			}
			
			$field_data['options'] = $options;
		}

		$params           = $this->api_params;
		$params['body']   = wp_json_encode( $field_data );
		$params['method'] = 'POST';

		// For custom objects, we need to use the correct endpoint
		if ( $object_type === $this->get_custom_object_name() ) {
			// Use the standard properties endpoint with object type ID for custom objects
			$url = $this->api_base_url . '/crm/v3/properties/' . $object_type;
		} else {
			// Standard contacts endpoint
			$url = $this->api_base_url . '/crm/v3/properties/' . $object_type;
		}
		

		
		$response = wp_remote_request( $url, $params );

		if ( is_wp_error( $response ) ) {
			error_log( 'ENNU HubSpot: WP Error in create_single_field: ' . $response->get_error_message() );
			return array(
				'success' => false,
				'message' => 'WordPress error: ' . $response->get_error_message(),
				'data' => null,
				'hubspot_id' => null
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );

		if ( $status_code !== 201 ) {
			$data = json_decode( $body, true );
			$error_message = isset( $data['message'] ) ? $data['message'] : 'Unknown error';
			
			error_log( 'ENNU HubSpot: Failed to create field ' . $field['name'] . ' - Status: ' . $status_code . ' - Error: ' . $error_message );
			error_log( 'ENNU HubSpot: Full error response: ' . $body );

			return array(
				'success' => false,
				'message' => 'Failed to create field ' . $field['name'] . ': ' . $error_message,
				'status_code' => $status_code,
				'data' => $data,
				'hubspot_id' => null
			);
		}

		$data = json_decode( $body, true );
		$hubspot_id = isset( $data['name'] ) ? $data['name'] : null;
		
		error_log( 'ENNU HubSpot: Successfully created field ' . $field['name'] . ' - HubSpot ID: ' . $hubspot_id );
		
		return array(
			'success' => true,
			'message' => 'Field created successfully',
			'data' => $data,
			'hubspot_id' => $hubspot_id
		);
	}

	/**
	 * Sanitize field data
	 *
	 * @since 64.2.1
	 * @param array $fields Raw field data
	 * @return array Sanitized field data
	 */
	private function sanitize_fields( $fields ) {
		$sanitized = array();
		
		foreach ( $fields as $field ) {
			$sanitized[] = array(
				'name'      => sanitize_key( $field['name'] ),
				'type'      => sanitize_text_field( $field['type'] ),
				'required'  => isset( $field['required'] ) ? (bool) $field['required'] : false,
			);
		}
		
		return $sanitized;
	}

	/**
	 * Get predefined field templates
	 *
	 * @since 64.6.99
	 * @return array Field templates
	 */
	public function get_field_templates() {
		return array(
			'weight_loss_contact' => array(
				'name'        => 'Weight Loss Assessment - Contact Properties',
				'description' => 'Complete Weight Loss assessment contact properties (35 fields)',
				'fields'      => $this->get_weight_loss_contact_fields(),
			),
			'weight_loss_custom_object' => array(
				'name'        => 'Weight Loss Assessment - Custom Object',
				'description' => 'Complete Weight Loss assessment custom object fields (35 fields)',
				'fields'      => $this->get_weight_loss_custom_object_fields(),
			),
			'biomarkers'   => array(
				'name'        => 'Biomarker Fields',
				'description' => 'Standard biomarker tracking fields',
				'fields'      => array(
					array( 'name' => 'biomarker_name', 'label' => 'Biomarker Name', 'type' => 'string', 'fieldType' => 'text' ),
					array( 'name' => 'biomarker_value', 'label' => 'Biomarker Value', 'type' => 'number', 'fieldType' => 'number' ),
					array( 'name' => 'biomarker_unit', 'label' => 'Unit', 'type' => 'string', 'fieldType' => 'text' ),
					array( 'name' => 'biomarker_status', 'label' => 'Status', 'type' => 'enumeration', 'fieldType' => 'select' ),
					array( 'name' => 'biomarker_date', 'label' => 'Test Date', 'type' => 'date', 'fieldType' => 'date' ),
					array( 'name' => 'biomarker_notes', 'label' => 'Notes', 'type' => 'string', 'fieldType' => 'textarea' ),
				),
			),
			'symptoms'     => array(
				'name'        => 'Symptom Fields',
				'description' => 'Symptom tracking and severity fields',
				'fields'      => array(
					array( 'name' => 'symptom_name', 'label' => 'Symptom Name', 'type' => 'string', 'fieldType' => 'text' ),
					array( 'name' => 'symptom_severity', 'label' => 'Severity', 'type' => 'enumeration', 'fieldType' => 'select' ),
					array( 'name' => 'symptom_frequency', 'label' => 'Frequency', 'type' => 'enumeration', 'fieldType' => 'select' ),
					array( 'name' => 'symptom_duration', 'label' => 'Duration', 'type' => 'string', 'fieldType' => 'text' ),
					array( 'name' => 'symptom_notes', 'label' => 'Notes', 'type' => 'string', 'fieldType' => 'textarea' ),
				),
			),
			'assessments'  => array(
				'name'        => 'Assessment Fields',
				'description' => 'Assessment completion and scoring fields',
				'fields'      => array(
					array( 'name' => 'assessment_type', 'label' => 'Assessment Type', 'type' => 'enumeration', 'fieldType' => 'select' ),
					array( 'name' => 'assessment_score', 'label' => 'Score', 'type' => 'number', 'fieldType' => 'number' ),
					array( 'name' => 'assessment_date', 'label' => 'Assessment Date', 'type' => 'date', 'fieldType' => 'date' ),
					array( 'name' => 'assessment_status', 'label' => 'Status', 'type' => 'enumeration', 'fieldType' => 'select' ),
					array( 'name' => 'assessment_completion', 'label' => 'Completion %', 'type' => 'number', 'fieldType' => 'number' ),
				),
			),
			'health_goals' => array(
				'name'        => 'Health Goals Fields',
				'description' => 'Health goal tracking and progress fields',
				'fields'      => array(
					array( 'name' => 'goal_type', 'label' => 'Goal Type', 'type' => 'enumeration', 'fieldType' => 'select' ),
					array( 'name' => 'goal_target', 'label' => 'Target Value', 'type' => 'string', 'fieldType' => 'text' ),
					array( 'name' => 'goal_deadline', 'label' => 'Deadline', 'type' => 'date', 'fieldType' => 'date' ),
					array( 'name' => 'goal_progress', 'label' => 'Progress %', 'type' => 'number', 'fieldType' => 'number' ),
					array( 'name' => 'goal_status', 'label' => 'Status', 'type' => 'enumeration', 'fieldType' => 'select' ),
				),
			),
		);
	}

	/**
	 * Get Global Shared Fields (6 fields) - Used across ALL assessments
	 *
	 * @since 64.6.99
	 * @return array Global shared fields
	 */
	public function get_global_shared_fields() {
		return array(
			// Global Shared Fields (6) - Updated across all assessments with proper prefixes
			array(
				'name' => 'ennu_global_gender',
				'label' => 'ENNU Gender - What is your gender?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'ENNU Global',
				'options' => array(
					array( 'label' => 'Male', 'value' => 'male' ),
					array( 'label' => 'Female', 'value' => 'female' ),
					array( 'label' => 'Other', 'value' => 'other' ),
				),
			),
			array(
				'name' => 'ennu_global_date_of_birth',
				'label' => 'ENNU Date of Birth - What is your date of birth?',
				'type' => 'date',
				'fieldType' => 'date',
				'groupName' => 'ENNU Global',
			),
			array(
				'name' => 'ennu_global_height',
				'label' => 'ENNU Height - What is your height?',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'ENNU Global',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'ennu_global_weight',
				'label' => 'ENNU Weight - What is your weight?',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'ENNU Global',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'ennu_global_bmi',
				'label' => 'ENNU BMI - Calculated Body Mass Index',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'ENNU Global',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'ennu_global_age',
				'label' => 'ENNU Age - Calculated age from date of birth',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'ENNU Global',
			),
		);
	}

	/**
	 * Get Weight Loss Assessment Contact Properties (11 fields)
	 *
	 * @since 64.6.99
	 * @return array Contact property fields
	 */
	public function get_weight_loss_contact_fields() {
		return array(
			// Assessment Questions (11) - Weight Loss Specific
			array(
				'name' => 'wl_q2',
				'original_key' => 'wlQ2WeightLossGoal',
				'label' => 'WL Q2 Weight Loss Goal - What is your primary weight loss goal?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Weight Loss Assessment',
				'options' => array(
					array( 'label' => 'Lose 10-20 lbs', 'value' => 'lose_10_20' ),
					array( 'label' => 'Lose 20-50 lbs', 'value' => 'lose_20_50' ),
					array( 'label' => 'Lose 50+ lbs', 'value' => 'lose_50_plus' ),
					array( 'label' => 'Maintain current weight', 'value' => 'maintain' ),
				),
			),
			array(
				'name' => 'wl_q3',
				'original_key' => 'wlQ3DietDescription',
				'label' => 'WL Q3 Diet Description - How would you describe your typical diet?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Weight Loss Assessment',
				'options' => array(
					array( 'label' => 'Balanced', 'value' => 'balanced' ),
					array( 'label' => 'Processed foods', 'value' => 'processed' ),
					array( 'label' => 'Low carb', 'value' => 'low_carb' ),
					array( 'label' => 'Vegetarian', 'value' => 'vegetarian' ),
					array( 'label' => 'Intermittent fasting', 'value' => 'intermittent_fasting' ),
				),
			),
			array(
				'name' => 'wl_q4',
				'original_key' => 'wlQ4ExerciseFrequency',
				'label' => 'WL Q4 Exercise Frequency - How often do you exercise?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Weight Loss Assessment',
				'options' => array(
					array( 'label' => '5+ times per week', 'value' => '5_plus' ),
					array( 'label' => '3-4 times per week', 'value' => '3_4' ),
					array( 'label' => '1-2 times per week', 'value' => '1_2' ),
					array( 'label' => 'Rarely or never', 'value' => 'none' ),
				),
			),
			array(
				'name' => 'wl_q5',
				'original_key' => 'wlQ5SleepHours',
				'label' => 'WL Q5 Sleep Hours - How many hours do you sleep per night?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Weight Loss Assessment',
				'options' => array(
					array( 'label' => 'Less than 5 hours', 'value' => 'less_than_5' ),
					array( 'label' => '5-6 hours', 'value' => '5_6' ),
					array( 'label' => '7-8 hours', 'value' => '7_8' ),
					array( 'label' => 'More than 8 hours', 'value' => 'more_than_8' ),
				),
			),
			array(
				'name' => 'wl_q6',
				'original_key' => 'wlQ6StressLevel',
				'label' => 'WL Q6 Stress Level - How would you rate your daily stress level?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Weight Loss Assessment',
				'options' => array(
					array( 'label' => 'Low', 'value' => 'low' ),
					array( 'label' => 'Moderate', 'value' => 'moderate' ),
					array( 'label' => 'High', 'value' => 'high' ),
					array( 'label' => 'Very high', 'value' => 'very_high' ),
				),
			),
			array(
				'name' => 'wl_q7',
				'original_key' => 'wlQ7WeightLossHistory',
				'label' => 'WL Q7 Weight Loss History - What is your weight loss history?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Weight Loss Assessment',
				'options' => array(
					array( 'label' => 'Never successful', 'value' => 'never_success' ),
					array( 'label' => 'Some success', 'value' => 'some_success' ),
					array( 'label' => 'Good success', 'value' => 'good_success' ),
					array( 'label' => 'First attempt', 'value' => 'first_attempt' ),
				),
			),
			array(
				'name' => 'wl_q8',
				'original_key' => 'wlQ8EmotionalEating',
				'label' => 'WL Q8 Emotional Eating - How often do you eat emotionally?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Weight Loss Assessment',
				'options' => array(
					array( 'label' => 'Often', 'value' => 'often' ),
					array( 'label' => 'Sometimes', 'value' => 'sometimes' ),
					array( 'label' => 'Rarely', 'value' => 'rarely' ),
					array( 'label' => 'Never', 'value' => 'never' ),
				),
			),
			array(
				'name' => 'wl_q9',
				'original_key' => 'wlQ9MedicalConditions',
				'label' => 'WL Q9 Medical Conditions - Do you have any medical conditions affecting weight?',
				'type' => 'enumeration',
				'fieldType' => 'multiselect',
				'groupName' => 'Weight Loss Assessment',
				'options' => array(
					array( 'label' => 'Thyroid issues', 'value' => 'thyroid' ),
					array( 'label' => 'PCOS', 'value' => 'pcos' ),
					array( 'label' => 'Insulin resistance', 'value' => 'insulin_resistance' ),
					array( 'label' => 'None', 'value' => 'none' ),
				),
			),
			array(
				'name' => 'wl_q10',
				'original_key' => 'wlQ10MotivationLevel',
				'label' => 'WL Q10 Motivation Level - How motivated are you to make lifestyle changes?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Weight Loss Assessment',
				'options' => array(
					array( 'label' => 'Very motivated', 'value' => 'very' ),
					array( 'label' => 'Somewhat motivated', 'value' => 'somewhat' ),
					array( 'label' => 'Not very motivated', 'value' => 'not_very' ),
					array( 'label' => 'Unsure', 'value' => 'unsure' ),
				),
			),
			array(
				'name' => 'wl_q11',
				'original_key' => 'wlQ11BodyCompositionGoal',
				'label' => 'WL Q11 Body Composition Goal - What is your primary body composition goal?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Weight Loss Assessment',
				'options' => array(
					array( 'label' => 'Lose fat', 'value' => 'lose_fat' ),
					array( 'label' => 'Build muscle', 'value' => 'build_muscle' ),
					array( 'label' => 'Both', 'value' => 'both' ),
				),
			),
			array(
				'name' => 'wl_q12',
				'original_key' => 'wlQ12SupportSystem',
				'label' => 'WL Q12 Support System - Do you have a support system?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Weight Loss Assessment',
				'options' => array(
					array( 'label' => 'Yes', 'value' => 'yes' ),
					array( 'label' => 'Somewhat', 'value' => 'somewhat' ),
					array( 'label' => 'No', 'value' => 'no' ),
				),
			),
			array(
				'name' => 'wl_q13',
				'original_key' => 'wlQ13ConfidenceLevel',
				'label' => 'WL Q13 Confidence Level - How confident are you in achieving your goals?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Weight Loss Assessment',
				'options' => array(
					array( 'label' => 'Very confident', 'value' => 'very' ),
					array( 'label' => 'Somewhat confident', 'value' => 'somewhat' ),
					array( 'label' => 'Not very confident', 'value' => 'not_very' ),
				),
			),
			
			// Assessment Scores (15)
			array(
				'name' => 'wl_overall_score',
				'original_key' => 'wlOverallScore',
				'label' => 'WL Overall Score - Weight Loss Assessment Overall Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Weight Loss Assessment',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_motivation_goals_score',
				'original_key' => 'wlCategoryMotivationGoalsScore',
				'label' => 'WL Category Motivation Goals Score - Motivation & Goals Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Weight Loss Assessment',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_nutrition_score',
				'original_key' => 'wlCategoryNutritionScore',
				'label' => 'WL Category Nutrition Score - Nutrition Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Weight Loss Assessment',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_physical_activity_score',
				'original_key' => 'wlCategoryPhysicalActivityScore',
				'label' => 'WL Category Physical Activity Score - Physical Activity Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Weight Loss Assessment',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_lifestyle_factors_score',
				'original_key' => 'wlCategoryLifestyleFactorsScore',
				'label' => 'WL Category Lifestyle Factors Score - Lifestyle Factors Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Weight Loss Assessment',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_psychological_factors_score',
				'original_key' => 'wlCategoryPsychologicalFactorsScore',
				'label' => 'WL Category Psychological Factors Score - Psychological Factors Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Weight Loss Assessment',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_weight_loss_history_score',
				'original_key' => 'wl_category_weight_loss_history_score',
				'label' => 'WL Category Weight Loss History Score - Weight Loss History Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Weight Loss Assessment',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_behavioral_patterns_score',
				'original_key' => 'wl_category_behavioral_patterns_score',
				'label' => 'WL Category Behavioral Patterns Score - Behavioral Patterns Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Weight Loss Assessment',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_medical_factors_score',
				'original_key' => 'wl_category_medical_factors_score',
				'label' => 'WL Category Medical Factors Score - Medical Factors Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Weight Loss Assessment',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_social_support_score',
				'original_key' => 'wl_category_social_support_score',
				'label' => 'WL Category Social Support Score - Social Support Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Weight Loss Assessment',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_aesthetics_score',
				'original_key' => 'wl_category_aesthetics_score',
				'label' => 'WL Category Aesthetics Score - Aesthetics Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Weight Loss Assessment',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_pillar_mind_score',
				'original_key' => 'wl_pillar_mind_score',
				'label' => 'WL Pillar Mind Score - Mind Pillar Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Weight Loss Assessment',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_pillar_body_score',
				'original_key' => 'wl_pillar_body_score',
				'label' => 'WL Pillar Body Score - Body Pillar Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Weight Loss Assessment',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_pillar_lifestyle_score',
				'original_key' => 'wl_pillar_lifestyle_score',
				'label' => 'WL Pillar Lifestyle Score - Lifestyle Pillar Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Weight Loss Assessment',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_pillar_aesthetics_score',
				'original_key' => 'wl_pillar_aesthetics_score',
				'label' => 'WL Pillar Aesthetics Score - Aesthetics Pillar Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Weight Loss Assessment',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			
			// Dashboard Aggregated (6)
			array(
				'name' => 'ennu_life_score',
				'original_key' => 'ennu_life_score',
				'label' => 'ENNU Life Score - Overall ENNU Life Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Dashboard',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'ennu_pillar_mind_score',
				'original_key' => 'ennu_pillar_mind_score',
				'label' => 'ENNU Pillar Mind Score - Mind Pillar Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Dashboard',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'ennu_pillar_body_score',
				'original_key' => 'ennu_pillar_body_score',
				'label' => 'ENNU Pillar Body Score - Body Pillar Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Dashboard',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'ennu_pillar_lifestyle_score',
				'original_key' => 'ennu_pillar_lifestyle_score',
				'label' => 'ENNU Pillar Lifestyle Score - Lifestyle Pillar Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Dashboard',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'ennu_pillar_aesthetics_score',
				'original_key' => 'ennu_pillar_aesthetics_score',
				'label' => 'ENNU Pillar Aesthetics Score - Aesthetics Pillar Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Dashboard',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'total_assessments_completed',
				'original_key' => 'total_assessments_completed',
				'label' => 'Total Assessments Completed - Total Assessments Completed',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Dashboard',
			),
		);
	}

	/**
	 * Get Weight Loss Assessment Custom Object Fields (35 fields)
	 *
	 * @since 64.6.99
	 * @return array Custom object fields
	 */
	public function get_weight_loss_custom_object_fields() {
		return array(
			// Metadata (10)
			array(
				'name' => 'wl_assessment_type',
				'original_key' => 'assessment_type',
				'label' => 'WL Assessment Type',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Metadata',
				'options' => array(
					array( 'label' => 'Weight Loss', 'value' => 'weight_loss' ),
				),
			),
			array(
				'name' => 'wl_assessment_attempt_number',
				'original_key' => 'assessment_attempt_number',
				'label' => 'WL Assessment Attempt Number',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Metadata',
			),
			array(
				'name' => 'wl_assessment_score',
				'original_key' => 'assessment_score',
				'label' => 'WL Assessment Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Metadata',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_assessment_completion_date',
				'original_key' => 'assessment_completion_date',
				'label' => 'WL Assessment Completion Date',
				'type' => 'date',
				'fieldType' => 'date',
				'groupName' => 'Metadata',
			),
			array(
				'name' => 'wl_wordpress_user_id',
				'original_key' => 'user_id',
				'label' => 'WL WordPress User ID',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Metadata',
			),
			array(
				'name' => 'wl_user_email',
				'original_key' => 'user_email',
				'label' => 'WL User Email',
				'type' => 'string',
				'fieldType' => 'text',
				'groupName' => 'Metadata',
			),
			array(
				'name' => 'wl_previous_assessment_score',
				'original_key' => 'previous_assessment_score',
				'label' => 'WL Previous Assessment Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Metadata',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_score_change',
				'original_key' => 'score_change',
				'label' => 'WL Score Change',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Metadata',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_assessment_duration_seconds',
				'original_key' => 'assessment_duration_seconds',
				'label' => 'WL Assessment Duration (seconds)',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Metadata',
			),
			array(
				'name' => 'wl_assessment_version',
				'original_key' => 'assessment_version',
				'label' => 'WL Assessment Version',
				'type' => 'string',
				'fieldType' => 'text',
				'groupName' => 'Metadata',
			),
			
			// Assessment Questions (11) - Same as contact properties but for custom object
			array(
				'name' => 'wl_q2',
				'original_key' => 'wl_q2_weight_loss_goal',
				'label' => 'WLQ2 - What is your primary weight loss goal?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Assessment Questions',
				'options' => array(
					array( 'label' => 'Lose 10-20 lbs', 'value' => 'lose_10_20' ),
					array( 'label' => 'Lose 20-50 lbs', 'value' => 'lose_20_50' ),
					array( 'label' => 'Lose 50+ lbs', 'value' => 'lose_50_plus' ),
					array( 'label' => 'Maintain current weight', 'value' => 'maintain' ),
				),
			),
			array(
				'name' => 'wl_q3',
				'original_key' => 'wl_q3_diet_description',
				'label' => 'WLQ3 - How would you describe your typical diet?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Assessment Questions',
				'options' => array(
					array( 'label' => 'Balanced', 'value' => 'balanced' ),
					array( 'label' => 'Processed foods', 'value' => 'processed' ),
					array( 'label' => 'Low carb', 'value' => 'low_carb' ),
					array( 'label' => 'Vegetarian', 'value' => 'vegetarian' ),
					array( 'label' => 'Intermittent fasting', 'value' => 'intermittent_fasting' ),
				),
			),
			array(
				'name' => 'wl_q4',
				'original_key' => 'wl_q4_exercise_frequency',
				'label' => 'WLQ4 - How often do you exercise?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Assessment Questions',
				'options' => array(
					array( 'label' => '5+ times per week', 'value' => '5_plus' ),
					array( 'label' => '3-4 times per week', 'value' => '3_4' ),
					array( 'label' => '1-2 times per week', 'value' => '1_2' ),
					array( 'label' => 'Rarely or never', 'value' => 'none' ),
				),
			),
			array(
				'name' => 'wl_q5',
				'original_key' => 'wl_q5_sleep_hours',
				'label' => 'WLQ5 - How many hours do you sleep per night?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Assessment Questions',
				'options' => array(
					array( 'label' => 'Less than 5 hours', 'value' => 'less_than_5' ),
					array( 'label' => '5-6 hours', 'value' => '5_6' ),
					array( 'label' => '7-8 hours', 'value' => '7_8' ),
					array( 'label' => 'More than 8 hours', 'value' => 'more_than_8' ),
				),
			),
			array(
				'name' => 'wl_q6',
				'original_key' => 'wl_q6_stress_level',
				'label' => 'WLQ6 - How would you rate your daily stress level?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Assessment Questions',
				'options' => array(
					array( 'label' => 'Low', 'value' => 'low' ),
					array( 'label' => 'Moderate', 'value' => 'moderate' ),
					array( 'label' => 'High', 'value' => 'high' ),
					array( 'label' => 'Very high', 'value' => 'very_high' ),
				),
			),
			array(
				'name' => 'wl_q7',
				'original_key' => 'wl_q7_weight_loss_history',
				'label' => 'WLQ7 - What is your weight loss history?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Assessment Questions',
				'options' => array(
					array( 'label' => 'Never successful', 'value' => 'never_success' ),
					array( 'label' => 'Some success', 'value' => 'some_success' ),
					array( 'label' => 'Good success', 'value' => 'good_success' ),
					array( 'label' => 'First attempt', 'value' => 'first_attempt' ),
				),
			),
			array(
				'name' => 'wl_q8',
				'original_key' => 'wl_q8_emotional_eating',
				'label' => 'WLQ8 - How often do you eat emotionally?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Assessment Questions',
				'options' => array(
					array( 'label' => 'Often', 'value' => 'often' ),
					array( 'label' => 'Sometimes', 'value' => 'sometimes' ),
					array( 'label' => 'Rarely', 'value' => 'rarely' ),
					array( 'label' => 'Never', 'value' => 'never' ),
				),
			),
			array(
				'name' => 'wl_q9',
				'original_key' => 'wl_q9_medical_conditions',
				'label' => 'WLQ9 - Do you have any medical conditions affecting weight?',
				'type' => 'enumeration',
				'fieldType' => 'multiselect',
				'groupName' => 'Assessment Questions',
				'options' => array(
					array( 'label' => 'Thyroid issues', 'value' => 'thyroid' ),
					array( 'label' => 'PCOS', 'value' => 'pcos' ),
					array( 'label' => 'Insulin resistance', 'value' => 'insulin_resistance' ),
					array( 'label' => 'None', 'value' => 'none' ),
				),
			),
			array(
				'name' => 'wl_q10',
				'original_key' => 'wl_q10_motivation_level',
				'label' => 'WLQ10 - How motivated are you to make lifestyle changes?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Assessment Questions',
				'options' => array(
					array( 'label' => 'Very motivated', 'value' => 'very' ),
					array( 'label' => 'Somewhat motivated', 'value' => 'somewhat' ),
					array( 'label' => 'Not very motivated', 'value' => 'not_very' ),
					array( 'label' => 'Unsure', 'value' => 'unsure' ),
				),
			),
			array(
				'name' => 'wl_q11',
				'original_key' => 'wl_q11_body_composition_goal',
				'label' => 'WLQ11 - What is your primary body composition goal?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Assessment Questions',
				'options' => array(
					array( 'label' => 'Lose fat', 'value' => 'lose_fat' ),
					array( 'label' => 'Build muscle', 'value' => 'build_muscle' ),
					array( 'label' => 'Both', 'value' => 'both' ),
				),
			),
			array(
				'name' => 'wl_q12',
				'original_key' => 'wl_q12_support_system',
				'label' => 'WLQ12 - Do you have a support system?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Assessment Questions',
				'options' => array(
					array( 'label' => 'Yes', 'value' => 'yes' ),
					array( 'label' => 'Somewhat', 'value' => 'somewhat' ),
					array( 'label' => 'No', 'value' => 'no' ),
				),
			),
			array(
				'name' => 'wl_q13',
				'original_key' => 'wl_q13_confidence_level',
				'label' => 'WLQ13 - How confident are you in achieving your goals?',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Assessment Questions',
				'options' => array(
					array( 'label' => 'Very confident', 'value' => 'very' ),
					array( 'label' => 'Somewhat confident', 'value' => 'somewhat' ),
					array( 'label' => 'Not very confident', 'value' => 'not_very' ),
				),
			),
			
			// Assessment Scores (14) - Same as contact properties but for custom object
			array(
				'name' => 'wl_category_motivation_goals_score',
				'original_key' => 'wl_category_motivation_goals_score',
				'label' => 'WL Category Motivation Goals Score - Motivation & Goals Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Assessment Scores',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_nutrition_score',
				'original_key' => 'wl_category_nutrition_score',
				'label' => 'WL Category Nutrition Score - Nutrition Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Assessment Scores',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_physical_activity_score',
				'original_key' => 'wl_category_physical_activity_score',
				'label' => 'WL Category Physical Activity Score - Physical Activity Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Assessment Scores',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_lifestyle_factors_score',
				'original_key' => 'wl_category_lifestyle_factors_score',
				'label' => 'WL Category Lifestyle Factors Score - Lifestyle Factors Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Assessment Scores',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_psychological_factors_score',
				'original_key' => 'wl_category_psychological_factors_score',
				'label' => 'WL Category Psychological Factors Score - Psychological Factors Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Assessment Scores',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_weight_loss_history_score',
				'original_key' => 'wl_category_weight_loss_history_score',
				'label' => 'WL Category Weight Loss History Score - Weight Loss History Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Assessment Scores',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_behavioral_patterns_score',
				'original_key' => 'wl_category_behavioral_patterns_score',
				'label' => 'WL Category Behavioral Patterns Score - Behavioral Patterns Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Assessment Scores',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_medical_factors_score',
				'original_key' => 'wl_category_medical_factors_score',
				'label' => 'WL Category Medical Factors Score - Medical Factors Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Assessment Scores',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_social_support_score',
				'original_key' => 'wl_category_social_support_score',
				'label' => 'WL Category Social Support Score - Social Support Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Assessment Scores',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_category_aesthetics_score',
				'original_key' => 'wl_category_aesthetics_score',
				'label' => 'WL Category Aesthetics Score - Aesthetics Category Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Assessment Scores',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_pillar_mind_score',
				'original_key' => 'wl_pillar_mind_score',
				'label' => 'WL Pillar Mind Score - Mind Pillar Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Assessment Scores',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_pillar_body_score',
				'original_key' => 'wl_pillar_body_score',
				'label' => 'WL Pillar Body Score - Body Pillar Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Assessment Scores',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_pillar_lifestyle_score',
				'original_key' => 'wl_pillar_lifestyle_score',
				'label' => 'WL Pillar Lifestyle Score - Lifestyle Pillar Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Assessment Scores',
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => 'wl_pillar_aesthetics_score',
				'original_key' => 'wl_pillar_aesthetics_score',
				'label' => 'WL Pillar Aesthetics Score - Aesthetics Pillar Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Assessment Scores',
				'numberDisplayHint' => 'DECIMAL_2',
			),
		);
	}

	/**
	 * Extract all assessment questions and create HubSpot fields
	 *
	 * @since 64.2.1
	 * @return array Assessment field data
	 */
	private function extract_assessment_fields() {
		$assessment_fields = array();
		
		// Get all assessment definitions
		$assessment_files = glob( plugin_dir_path( dirname( __FILE__ ) ) . 'includes/config/assessments/*.php' );
		
		// Debug: Log the path being used
		error_log( 'ENNU Life: Assessment files path: ' . plugin_dir_path( dirname( __FILE__ ) ) . 'includes/config/assessments/*.php' );
		
		error_log( 'ENNU Life: extract_assessment_fields - Found ' . count( $assessment_files ) . ' assessment files' );
		
		// Debug: Log the actual files found
		foreach ( $assessment_files as $file ) {
			error_log( 'ENNU Life: Found assessment file: ' . basename( $file ) );
		}
		
		foreach ( $assessment_files as $file ) {
			error_log( 'ENNU Life: Processing assessment file: ' . basename( $file ) );
			
			$assessment_config = include $file;
			$assessment_name = basename( $file, '.php' );
			
			if ( ! isset( $assessment_config['questions'] ) ) {
				error_log( 'ENNU Life: No questions found in ' . basename( $file ) );
				continue;
			}
			
			error_log( 'ENNU Life: Found ' . count( $assessment_config['questions'] ) . ' questions in ' . basename( $file ) );
			
			foreach ( $assessment_config['questions'] as $question_key => $question ) {
				// Skip global fields that are already handled
				if ( isset( $question['global_key'] ) ) {
					error_log( 'ENNU Life: Skipping global field: ' . $question_key );
					continue;
				}
				
				$field_data = array(
					'name'     => $question_key,
					'type'     => $this->map_question_type_to_field_type( $question['type'] ),
					'required' => isset( $question['required'] ) ? $question['required'] : false,
					'label'    => $question['title'],
					'options'  => array(),
				);
				
				// Extract options for choice-based questions
				if ( isset( $question['options'] ) && is_array( $question['options'] ) ) {
					foreach ( $question['options'] as $option_value => $option_label ) {
						$field_data['options'][] = array(
							'label' => $option_label,
							'value' => $option_value,
						);
					}
				}
				
				$assessment_fields[] = $field_data;
				error_log( 'ENNU Life: Added field: ' . $question_key . ' (' . $field_data['type'] . ')' );
			}
		}
		
		error_log( 'ENNU Life: extract_assessment_fields - Total fields extracted: ' . count( $assessment_fields ) );
		return $assessment_fields;
	}
	
	/**
	 * Get available assessments
	 *
	 * @since 64.6.91
	 * @return array|WP_Error Array of assessment names or WP_Error
	 */
	private function get_available_assessments() {
		$assessment_files = glob( plugin_dir_path( dirname( __FILE__ ) ) . 'includes/config/assessments/*.php' );
		
		if ( empty( $assessment_files ) ) {
			return new WP_Error( 'no_assessments', 'No assessment files found.' );
		}
		
		$assessments = array();
		foreach ( $assessment_files as $file ) {
			$assessment_name = basename( $file, '.php' );
			$assessment_config = include $file;
			
			$assessments[] = array(
				'id' => $assessment_name,
				'name' => $assessment_name,
				'title' => isset( $assessment_config['title'] ) ? $assessment_config['title'] : ucfirst( $assessment_name ),
				'description' => isset( $assessment_config['description'] ) ? $assessment_config['description'] : '',
			);
		}
		
		return $assessments;
	}
	
	/**
	 * Extract fields from a specific assessment
	 *
	 * @since 64.6.91
	 * @param string $assessment_name Assessment name
	 * @return array|WP_Error Array of fields or WP_Error
	 */
	/**
	 * Generate a proper HubSpot field name from question key and title
	 *
	 * @param string $question_key The question key (e.g., 'wlQ2WeightLossGoal')
	 * @param string $question_title The question title
	 * @param string $assessment_name The assessment name
	 * @return string Formatted field name
	 */
	private function generate_hubspot_field_name( $question_key, $question_title, $assessment_name ) {
		// Get assessment prefix based on assessment name
		$assessment_prefix = $this->get_assessment_prefix( $assessment_name );
		
		// Extract the question number from the key (e.g., 'sleep_q1', 'hormone_q2', 'weight_loss_q3')
		$question_number = '';
		if ( preg_match( '/([a-z]+_q\d+)/', $question_key, $matches ) ) {
			$question_number = $matches[1];
		}
		
		// Clean up the question title (remove special characters, limit length)
		$clean_title = preg_replace( '/[^a-zA-Z0-9\s\-]/', '', $question_title );
		$clean_title = trim( $clean_title );
		
		// Limit title length to avoid HubSpot field name limits
		if ( strlen( $clean_title ) > 50 ) {
			$clean_title = substr( $clean_title, 0, 47 ) . '...';
		}
		
		// Format: "SL Q4 - How long does it typically take you to fall asleep" (with Q# in prefix)
		if ( $question_number ) {
			// Extract just the number from the question number (e.g., "1" from "sleep_q1")
			$number_only = preg_replace( '/[^0-9]/', '', $question_number );
			// Format: "SL Q4 - How long does it typically take you to fall asleep"
			return strtoupper( $assessment_prefix ) . ' Q' . $number_only . ' - ' . $clean_title;
		} else {
			// Fallback if no question number found
			return strtoupper( $assessment_prefix ) . ' - ' . $clean_title;
		}
	}
	
	/**
	 * Get assessment prefix for field naming
	 *
	 * @param string $assessment_name Assessment name
	 * @return string Assessment prefix
	 */
	private function get_assessment_prefix( $assessment_name ) {
		$prefix_mapping = array(
			'weight-loss' => 'WL',
			'hormone' => 'HR',
			'sleep' => 'SL',
			'ed-treatment' => 'ED',
			'hair' => 'HA',
			'health-optimization' => 'HO',
			'health' => 'HL',
			'menopause' => 'MN',
			'skin' => 'SK',
			'testosterone' => 'TS',
			'welcome' => 'WC',
		);
		
		return isset( $prefix_mapping[ $assessment_name ] ) ? $prefix_mapping[ $assessment_name ] : strtoupper( substr( $assessment_name, 0, 2 ) );
	}

	/**
	 * Generate HubSpot-compliant field ID from WordPress field key
	 *
	 * @param string $wordpress_field_key WordPress field key
	 * @param string $assessment_name Assessment name
	 * @return string HubSpot-compliant field ID
	 */
	private function generate_hubspot_field_id( $wordpress_field_key, $assessment_name, $is_custom_object = false ) {
		// Get assessment prefix to avoid conflicts with existing HubSpot properties
		$assessment_prefix = $this->get_assessment_prefix( $assessment_name );
		
		// For question fields, they already have the assessment prefix (e.g., wl_q2, sleep_q1)
		if ( preg_match( '/^[a-z]+_q\d+$/', $wordpress_field_key ) ) {
			// Question fields already have the correct format (e.g., wl_q2, sleep_q1)
			$hubspot_id = $wordpress_field_key;
		} else {
			// For custom object fields, use generic names for specific fields only
			// For contact properties, add assessment prefix to avoid conflicts
			if ( $is_custom_object && in_array( $wordpress_field_key, array( 'assessment_type', 'user_email', 'user_id' ) ) ) {
				// These 3 fields should be generic in custom object (no prefix)
				$hubspot_id = $wordpress_field_key;
			} else {
				// All other fields get assessment prefix
				$hubspot_id = strtolower( $assessment_prefix ) . '_' . $wordpress_field_key;
			}
		}
		
		// Ensure HubSpot compliance: lowercase, no spaces, only letters, numbers, and underscores
		$hubspot_id = strtolower( $hubspot_id );
		$hubspot_id = preg_replace( '/[^a-z0-9_]/', '_', $hubspot_id );
		$hubspot_id = preg_replace( '/_+/', '_', $hubspot_id ); // Replace multiple underscores with single
		$hubspot_id = trim( $hubspot_id, '_' ); // Remove leading/trailing underscores
		
		// Ensure it starts with a letter (HubSpot requirement)
		if ( ! preg_match( '/^[a-z]/', $hubspot_id ) ) {
			$hubspot_id = 'field_' . $hubspot_id;
		}
		
		return $hubspot_id;
	}

	private function extract_assessment_fields_by_name( $assessment_name ) {
		$file_path = plugin_dir_path( dirname( __FILE__ ) ) . 'includes/config/assessments/' . $assessment_name . '.php';
		
		if ( ! file_exists( $file_path ) ) {
			return new WP_Error( 'assessment_not_found', 'Assessment file not found: ' . $assessment_name );
		}
		
		$assessment_config = include $file_path;
		
		if ( ! isset( $assessment_config['questions'] ) ) {
			return new WP_Error( 'no_questions', 'No questions found in assessment: ' . $assessment_name );
		}
		
		$contact_fields = array();
		$custom_object_fields = array();
		
		foreach ( $assessment_config['questions'] as $question_key => $question ) {
			// Include global fields in assessment statistics but mark them as global
			if ( isset( $question['global_key'] ) ) {
				// Add global fields to both contact and custom object for statistics
				$global_field_name = $this->generate_hubspot_field_name( $question_key, $question['title'], $assessment_name );
				$global_field_id = $this->generate_hubspot_field_id( $question_key, $assessment_name );
				
				$global_field_data = array(
					'name'     => $global_field_name,
					'hubspot_id' => $global_field_id,
					'wordpress_field_key' => $question_key,
					'wordpress_field_name' => $question['title'],
					'original_key' => $question_key,
					'label'    => $question['title'],
					'type'     => $this->map_question_type_to_field_type( $question['type'] ),
					'required' => isset( $question['required'] ) ? $question['required'] : false,
					'groupName' => 'Global Shared Fields',
					'is_global' => true,
					'description' => $this->generate_field_description( array(
						'name' => $global_field_name,
						'type' => $this->map_question_type_to_field_type( $question['type'] ),
						'label' => $question['title'],
						'is_global' => true
					), $assessment_name ),
					'options'  => array(),
				);
				
				// Extract options for choice-based questions
				if ( isset( $question['options'] ) && is_array( $question['options'] ) ) {
					$option_counter = 1;
					foreach ( $question['options'] as $option_value => $option_label ) {
						$global_field_data['options'][] = array(
							'label' => $option_label,
							'value' => $question_key . '_answer' . $option_counter,
						);
						$option_counter++;
					}
				}
				
				$contact_fields[] = $global_field_data;
				$custom_object_fields[] = $global_field_data;
				continue;
			}
			
			// Generate proper HubSpot field name and ID
			$hubspot_field_name = $this->generate_hubspot_field_name( $question_key, $question['title'], $assessment_name );
			$hubspot_field_id = $this->generate_hubspot_field_id( $question_key, $assessment_name );
			$assessment_prefix = $this->get_assessment_prefix( $assessment_name );
			
			$field_data = array(
				'name'     => $hubspot_field_name, // Use the formatted name for HubSpot
				'hubspot_id' => $hubspot_field_id, // HubSpot-compliant field ID
				'wordpress_field_key' => $question_key, // WordPress field key
				'wordpress_field_name' => $question['title'], // WordPress field name
				'original_key' => $question_key, // Keep original key for WordPress mapping
				'label'    => $question['title'],
				'type'     => $this->map_question_type_to_field_type( $question['type'] ),
				'required' => isset( $question['required'] ) ? $question['required'] : false,
				'groupName' => $assessment_prefix . ' Assessment Questions', // Add group name
				'description' => $this->generate_field_description( array(
					'name' => $hubspot_field_name,
					'type' => $this->map_question_type_to_field_type( $question['type'] ),
					'label' => $question['title']
				), $assessment_name ),
				'options'  => array(),
			);
			
			// Extract options for choice-based questions
			if ( isset( $question['options'] ) && is_array( $question['options'] ) ) {
				$option_counter = 1;
				foreach ( $question['options'] as $option_value => $option_label ) {
					$field_data['options'][] = array(
						'label' => $option_label,
						'value' => $question_key . '_answer' . $option_counter,
					);
					$option_counter++;
				}
			}
			
			// Add to both contact and custom object fields
			$contact_fields[] = $field_data;
			$custom_object_fields[] = $field_data;
		}
		
		// Add metadata fields for custom object
		$custom_object_fields = array_merge( $custom_object_fields, $this->get_assessment_metadata_fields( $assessment_name ) );
		
		return array(
			'assessment_name' => $assessment_name,
			'assessment_title' => isset( $assessment_config['title'] ) ? $assessment_config['title'] : ucfirst( $assessment_name ),
			'contact_fields' => $contact_fields,
			'custom_object_fields' => $custom_object_fields,
		);
	}
	
	/**
	 * Get metadata fields for assessment custom object
	 *
	 * @param string $assessment_name Assessment name
	 * @return array Metadata fields
	 */
	private function get_assessment_metadata_fields( $assessment_name ) {
		$assessment_prefix = $this->get_assessment_prefix( $assessment_name );
		$assessment_title = ucfirst( str_replace( '-', ' ', $assessment_name ) );
		
		return array(
			array(
				'name' => strtoupper( $assessment_prefix ) . ' Assessment Type',
				'hubspot_id' => $this->generate_hubspot_field_id( 'assessment_type', $assessment_name, true ),
				'wordpress_field_key' => 'assessment_type',
				'wordpress_field_name' => strtoupper( $assessment_prefix ) . ' Assessment Type',
				'original_key' => 'assessment_type',
				'label' => strtoupper( $assessment_prefix ) . ' Assessment Type',
				'type' => 'enumeration',
				'fieldType' => 'select',
				'groupName' => 'Metadata',
				'description' => $this->generate_field_description( array(
					'name' => strtoupper( $assessment_prefix ) . ' Assessment Type',
					'type' => 'enumeration',
					'groupName' => 'Metadata'
				), $assessment_name ),
				'options' => array(
					array( 'label' => $assessment_title, 'value' => $assessment_name ),
				),
			),
			array(
				'name' => strtoupper( $assessment_prefix ) . ' Assessment Attempt Number',
				'hubspot_id' => $this->generate_hubspot_field_id( 'assessment_attempt_number', $assessment_name ),
				'wordpress_field_key' => 'assessment_attempt_number',
				'wordpress_field_name' => strtoupper( $assessment_prefix ) . ' Assessment Attempt Number',
				'original_key' => 'assessment_attempt_number',
				'label' => strtoupper( $assessment_prefix ) . ' Assessment Attempt Number',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Metadata',
				'description' => $this->generate_field_description( array(
					'name' => strtoupper( $assessment_prefix ) . ' Assessment Attempt Number',
					'type' => 'number',
					'groupName' => 'Metadata'
				), $assessment_name ),
			),
			array(
				'name' => strtoupper( $assessment_prefix ) . ' Assessment Score',
				'hubspot_id' => $this->generate_hubspot_field_id( 'assessment_score', $assessment_name ),
				'wordpress_field_key' => 'assessment_score',
				'wordpress_field_name' => strtoupper( $assessment_prefix ) . ' Assessment Score',
				'original_key' => 'assessment_score',
				'label' => strtoupper( $assessment_prefix ) . ' Assessment Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Metadata',
				'numberDisplayHint' => 'DECIMAL_2',
				'description' => $this->generate_field_description( array(
					'name' => strtoupper( $assessment_prefix ) . ' Assessment Score',
					'type' => 'number',
					'groupName' => 'Metadata'
				), $assessment_name ),
			),
			array(
				'name' => strtoupper( $assessment_prefix ) . ' Assessment Completion Date',
				'hubspot_id' => $this->generate_hubspot_field_id( 'assessment_completion_date', $assessment_name ),
				'wordpress_field_key' => 'assessment_completion_date',
				'wordpress_field_name' => strtoupper( $assessment_prefix ) . ' Assessment Completion Date',
				'original_key' => 'assessment_completion_date',
				'label' => strtoupper( $assessment_prefix ) . ' Assessment Completion Date',
				'type' => 'date',
				'fieldType' => 'date',
				'groupName' => 'Metadata',
				'description' => $this->generate_field_description( array(
					'name' => strtoupper( $assessment_prefix ) . ' Assessment Completion Date',
					'type' => 'date',
					'groupName' => 'Metadata'
				), $assessment_name ),
			),
			array(
				'name' => strtoupper( $assessment_prefix ) . ' WordPress User ID',
				'hubspot_id' => $this->generate_hubspot_field_id( 'user_id', $assessment_name, true ),
				'wordpress_field_key' => 'user_id',
				'wordpress_field_name' => strtoupper( $assessment_prefix ) . ' WordPress User ID',
				'original_key' => 'user_id',
				'label' => strtoupper( $assessment_prefix ) . ' WordPress User ID',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Metadata',
				'description' => $this->generate_field_description( array(
					'name' => strtoupper( $assessment_prefix ) . ' WordPress User ID',
					'type' => 'number',
					'groupName' => 'Metadata'
				), $assessment_name ),
			),
			array(
				'name' => strtoupper( $assessment_prefix ) . ' User Email',
				'hubspot_id' => $this->generate_hubspot_field_id( 'user_email', $assessment_name, true ),
				'wordpress_field_key' => 'user_email',
				'wordpress_field_name' => strtoupper( $assessment_prefix ) . ' User Email',
				'original_key' => 'user_email',
				'label' => strtoupper( $assessment_prefix ) . ' User Email',
				'type' => 'string',
				'fieldType' => 'text',
				'groupName' => 'Metadata',
				'description' => $this->generate_field_description( array(
					'name' => strtoupper( $assessment_prefix ) . ' User Email',
					'type' => 'string',
					'groupName' => 'Metadata'
				), $assessment_name ),
			),
			array(
				'name' => strtoupper( $assessment_prefix ) . ' Previous Assessment Score',
				'hubspot_id' => $this->generate_hubspot_field_id( 'previous_assessment_score', $assessment_name ),
				'wordpress_field_key' => 'previous_assessment_score',
				'wordpress_field_name' => strtoupper( $assessment_prefix ) . ' Previous Assessment Score',
				'original_key' => 'previous_assessment_score',
				'label' => strtoupper( $assessment_prefix ) . ' Previous Assessment Score',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Metadata',
				'numberDisplayHint' => 'DECIMAL_2',
				'description' => $this->generate_field_description( array(
					'name' => strtoupper( $assessment_prefix ) . ' Previous Assessment Score',
					'type' => 'number',
					'groupName' => 'Metadata'
				), $assessment_name ),
			),
			array(
				'name' => strtoupper( $assessment_prefix ) . ' Score Change',
				'hubspot_id' => $this->generate_hubspot_field_id( 'score_change', $assessment_name ),
				'wordpress_field_key' => 'score_change',
				'wordpress_field_name' => strtoupper( $assessment_prefix ) . ' Score Change',
				'original_key' => 'score_change',
				'label' => strtoupper( $assessment_prefix ) . ' Score Change',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Metadata',
				'description' => $this->generate_field_description( array(
					'name' => strtoupper( $assessment_prefix ) . ' Score Change',
					'type' => 'number',
					'groupName' => 'Metadata'
				), $assessment_name ),
				'numberDisplayHint' => 'DECIMAL_2',
			),
			array(
				'name' => strtoupper( $assessment_prefix ) . ' Assessment Duration Seconds',
				'hubspot_id' => $this->generate_hubspot_field_id( 'assessment_duration_seconds', $assessment_name ),
				'wordpress_field_key' => 'assessment_duration_seconds',
				'wordpress_field_name' => strtoupper( $assessment_prefix ) . ' Assessment Duration (seconds)',
				'original_key' => 'assessment_duration_seconds',
				'label' => strtoupper( $assessment_prefix ) . ' Assessment Duration (seconds)',
				'type' => 'number',
				'fieldType' => 'number',
				'groupName' => 'Metadata',
				'description' => $this->generate_field_description( array(
					'name' => strtoupper( $assessment_prefix ) . ' Assessment Duration Seconds',
					'type' => 'number',
					'groupName' => 'Metadata'
				), $assessment_name ),
			),
			array(
				'name' => strtoupper( $assessment_prefix ) . ' Assessment Version',
				'hubspot_id' => $this->generate_hubspot_field_id( 'assessment_version', $assessment_name ),
				'wordpress_field_key' => 'assessment_version',
				'wordpress_field_name' => strtoupper( $assessment_prefix ) . ' Assessment Version',
				'original_key' => 'assessment_version',
				'label' => strtoupper( $assessment_prefix ) . ' Assessment Version',
				'type' => 'string',
				'fieldType' => 'text',
				'groupName' => 'Metadata',
				'description' => $this->generate_field_description( array(
					'name' => strtoupper( $assessment_prefix ) . ' Assessment Version',
					'type' => 'string',
					'groupName' => 'Metadata'
				), $assessment_name ),
			),
		);
	}
	
	/**
	 * Map assessment question types to HubSpot field types
	 *
	 * @since 64.2.1
	 * @param string $question_type Assessment question type
	 * @return string HubSpot field type
	 */
	private function map_question_type_to_field_type( $question_type ) {
		$type_mapping = array(
			'radio'         => 'enumeration',
			'multiselect'   => 'enumeration',
			'text'          => 'string',
			'textarea'      => 'string',
			'number'        => 'number',
			'date'          => 'date',
			'dob_dropdowns' => 'date',
			'height_weight' => 'string',
			'first_last_name' => 'string',
			'email_phone'   => 'string',
		);
		
		return isset( $type_mapping[ $question_type ] ) ? $type_mapping[ $question_type ] : 'string';
	}
	
	/**
	 * Create Weight Loss Assessment fields (Contact Properties + Custom Object) with existence checking
	 *
	 * @since 64.6.99
	 * @return array Creation results
	 */
	public function create_weight_loss_fields() {
		$results = array();
		
		// Get Weight Loss contact fields (36 fields)
		$contact_fields = $this->get_weight_loss_contact_fields();
		
		// Get Weight Loss custom object fields (36 fields)
		$custom_object_fields = $this->get_weight_loss_custom_object_fields();
		
		// Check existence status
		$existence_status = $this->check_weight_loss_field_existence();
		
		error_log( 'ENNU HubSpot: Creating Weight Loss contact fields: ' . count( $contact_fields ) . ' fields' );
		error_log( 'ENNU HubSpot: Creating Weight Loss custom object fields: ' . count( $custom_object_fields ) . ' fields' );
		
		// Filter out existing fields
		$new_contact_fields = array();
		$skipped_contact_fields = array();
		foreach ( $contact_fields as $field ) {
			if ( $existence_status['contact_properties']['fields'][$field['name']] ) {
				$skipped_contact_fields[] = $field['name'];
			} else {
				$new_contact_fields[] = $field;
			}
		}
		
		$new_custom_object_fields = array();
		$skipped_custom_object_fields = array();
		foreach ( $custom_object_fields as $field ) {
			if ( $existence_status['custom_object_properties']['fields'][$field['name']] ) {
				$skipped_custom_object_fields[] = $field['name'];
			} else {
				$new_custom_object_fields[] = $field;
			}
		}
		
		error_log( 'ENNU HubSpot: Skipping ' . count( $skipped_contact_fields ) . ' existing contact fields' );
		error_log( 'ENNU HubSpot: Skipping ' . count( $skipped_custom_object_fields ) . ' existing custom object fields' );
		
		// Create contact properties (only new ones)
		$contact_results = $this->bulk_create_fields( 'contacts', $new_contact_fields );
		$results['contacts'] = $contact_results;
		$results['contacts']['skipped_fields'] = $skipped_contact_fields;
		$results['contacts']['new_fields_count'] = count( $new_contact_fields );
		$results['contacts']['skipped_fields_count'] = count( $skipped_contact_fields );
		
		// Create custom object fields (only new ones)
		$custom_object_name = $this->get_custom_object_name();
		$custom_object_results = $this->bulk_create_fields( $custom_object_name, $new_custom_object_fields );
		$results['custom_object'] = $custom_object_results;
		$results['custom_object']['skipped_fields'] = $skipped_custom_object_fields;
		$results['custom_object']['new_fields_count'] = count( $new_custom_object_fields );
		$results['custom_object']['skipped_fields_count'] = count( $skipped_custom_object_fields );
		
		error_log( 'ENNU HubSpot: Weight Loss field creation results: ' . json_encode( $results ) );
		
		return $results;
	}
	
	/**
	 * Create assessment fields in HubSpot
	 *
	 * @since 64.2.1
	 * @param string $assessment_name Assessment name to create fields for
	 * @return array Results
	 */
	public function create_assessment_fields( $assessment_name = null ) {
		if ( $assessment_name ) {
			// Create fields for a specific assessment
			$assessment_data = $this->extract_assessment_fields_by_name( $assessment_name );
			
			if ( is_wp_error( $assessment_data ) ) {
				return array(
					'success' => false,
					'error' => $assessment_data->get_error_message()
				);
			}
			
			// Use both contact_fields and custom_object_fields from the assessment data
			$contact_fields = $assessment_data['contact_fields'];
			$custom_object_fields = $assessment_data['custom_object_fields'];
		} else {
			// Create fields for all assessments (legacy behavior)
			$assessment_fields = $this->extract_assessment_fields();
			$contact_fields = $assessment_fields;
			$custom_object_fields = $assessment_fields; // For legacy, use same fields for both
		}
		
		$results = array(
			'success' => true,
			'contacts' => array(
				'new_fields_count' => 0,
				'skipped_fields_count' => 0,
				'success' => array(),
				'errors' => array(),
				'skipped_fields' => array()
			),
			'custom_object' => array(
				'new_fields_count' => 0,
				'skipped_fields_count' => 0,
				'success' => array(),
				'errors' => array(),
				'skipped_fields' => array()
			)
		);
		
		// Get existing properties to check for conflicts
		$existing_contact_properties = $this->get_existing_properties( 'contacts' );
		$custom_object_name = $this->get_custom_object_name();
		$existing_custom_object_properties = $this->get_existing_properties( $custom_object_name );
		
		// Create contact fields
		if ( is_array( $contact_fields ) ) {
			foreach ( $contact_fields as $field ) {
				// Check if field already exists
				if ( isset( $existing_contact_properties[$field['name']] ) ) {
					$results['contacts']['skipped_fields'][] = $field['name'];
					$results['contacts']['skipped_fields_count']++;
				} else {
					$result = $this->create_single_field( 'contacts', $field, $assessment_name );
					
					if ( is_wp_error( $result ) ) {
						$results['contacts']['errors'][] = array(
							'field' => $field['name'],
							'error' => $result->get_error_message(),
						);
					} else {
						$results['contacts']['success'][] = array(
							'field' => $field['name'],
							'result' => $result,
						);
						$results['contacts']['new_fields_count']++;
					}
				}
			}
		}
		
		// Create custom object fields
		if ( is_array( $custom_object_fields ) ) {
			foreach ( $custom_object_fields as $field ) {
				// Check if field already exists
				if ( isset( $existing_custom_object_properties[$field['name']] ) ) {
					$results['custom_object']['skipped_fields'][] = $field['name'];
					$results['custom_object']['skipped_fields_count']++;
				} else {
					$result = $this->create_single_field( $custom_object_name, $field, $assessment_name );
					
					if ( is_wp_error( $result ) ) {
						$results['custom_object']['errors'][] = array(
							'field' => $field['name'],
							'error' => $result->get_error_message(),
						);
					} else {
						$results['custom_object']['success'][] = array(
							'field' => $field['name'],
							'result' => $result,
						);
						$results['custom_object']['new_fields_count']++;
					}
				}
			}
		}
		
		return $results;
	}

	/**
	 * Check if a property already exists in HubSpot
	 *
	 * @param string $property_name The internal name of the property
	 * @param string $object_type The object type (e.g., 'contacts', 'companies')
	 * @return bool True if property exists, false otherwise
	 */
	private function property_exists( $property_name, $object_type ) {
		$this->init_api_params();
		
		// Use different endpoints for standard objects vs custom objects
		if ( in_array( $object_type, array( 'contacts', 'companies', 'deals' ) ) ) {
			$url = "https://api.hubapi.com/crm/v3/properties/{$object_type}/{$property_name}";
		} else {
			// For custom objects, use the standard properties endpoint with object type ID
			$custom_object_name = $this->get_custom_object_name();
			$url = "https://api.hubapi.com/crm/v3/properties/{$custom_object_name}/{$property_name}";
		}
		
		// Add rate limiting delay
		$this->rate_limit_delay();
		
		$response = wp_remote_get( $url, array(
			'headers' => $this->api_params['headers'],
			'timeout' => 30
		) );
		
		if ( is_wp_error( $response ) ) {
			error_log( "ENNU HubSpot: Error checking property existence: " . $response->get_error_message() );
			return false;
		}
		
		$status_code = wp_remote_retrieve_response_code( $response );
		
		// 200 = property exists, 404 = property doesn't exist
		return $status_code === 200;
	}

	/**
	 * Get list of existing properties for an object type
	 *
	 * @param string $object_type The object type (e.g., 'contacts', 'companies')
	 * @return array Array of existing property names
	 */
	private function get_existing_properties( $object_type ) {
		$this->init_api_params();
		
		// Use different endpoints for standard objects vs custom objects
		if ( in_array( $object_type, array( 'contacts', 'companies', 'deals' ) ) ) {
			$url = "https://api.hubapi.com/crm/v3/properties/{$object_type}";
		} else {
			// For custom objects, use the standard properties endpoint with object type ID
			$custom_object_name = $this->get_custom_object_name();
			$url = "https://api.hubapi.com/crm/v3/properties/{$custom_object_name}";
		}
		
		error_log( "ENNU HubSpot: Fetching existing properties for object type: {$object_type} using URL: {$url}" );
		
		// Try up to 5 times with increasing delays to handle potential caching issues
		for ( $attempt = 1; $attempt <= 5; $attempt++ ) {
			// Add rate limiting delay
			$this->rate_limit_delay();
			
			$response = wp_remote_get( $url, array(
				'headers' => $this->api_params['headers'],
				'timeout' => 30
			) );
			
			if ( is_wp_error( $response ) ) {
				error_log( "ENNU HubSpot: Error fetching existing properties (attempt {$attempt}): " . $response->get_error_message() );
				if ( $attempt === 5 ) {
					return array();
				}
				sleep( 1 ); // Wait 1 second before retry
				continue;
			}
			
			$status_code = wp_remote_retrieve_response_code( $response );
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );
			
			// Handle different response structures for standard vs custom objects
			if ( in_array( $object_type, array( 'contacts', 'companies', 'deals' ) ) ) {
				// Standard objects return results array
				if ( ! isset( $data['results'] ) ) {
					error_log( "ENNU HubSpot: No results found in API response for {$object_type} (attempt {$attempt})" );
					if ( $attempt === 5 ) {
						return array();
					}
					sleep( 1 ); // Wait 1 second before retry
					continue;
				}
				$properties = $data['results'];
			} else {
				// Custom objects return properties directly
				if ( ! isset( $data['properties'] ) ) {
					error_log( "ENNU HubSpot: No properties found in API response for {$object_type} (attempt {$attempt})" );
					error_log( "ENNU HubSpot: API response data: " . wp_json_encode( $data ) );
					// If custom object doesn't exist, log error and return empty array
					if ( $attempt === 1 ) {
						error_log( "ENNU HubSpot: Custom object {$object_type} does not exist. Please create it manually in HubSpot." );
					}
					if ( $attempt === 5 ) {
						error_log( "ENNU HubSpot: All attempts failed for custom object {$object_type}" );
						return array();
					}
					sleep( 1 ); // Wait 1 second before retry
					continue;
				}
				$properties = $data['properties'];
			}
			
			error_log( "ENNU HubSpot: Found " . count( $properties ) . " properties for {$object_type} (attempt {$attempt})" );
			
			$existing_properties = array();
			foreach ( $properties as $property ) {
				if ( isset( $property['name'] ) ) {
					$existing_properties[$property['name']] = array(
						'id' => $property['name'],
						'name' => $property['name']
					);
				}
			}
			
			error_log( "ENNU HubSpot: Returning " . count( $existing_properties ) . " existing properties for {$object_type}" );
			return $existing_properties;
		}
		
		return array();
	}



	/**
	 * Create a single field with conflict detection
	 *
	 * @param array $field Field data
	 * @param string $object_type Object type
	 * @return array Result with status and message
	 */
	private function create_single_field_with_conflict_check( $field, $object_type ) {
		// Check if property already exists
		if ( $this->property_exists( $field['name'], $object_type ) ) {
			return array(
				'success' => false,
				'message' => "Property '{$field['name']}' already exists in HubSpot. Skipping to preserve existing data.",
				'conflict' => true
			);
		}
		
		// Proceed with creation
		return $this->create_single_field( $field, $object_type );
	}

	/**
	 * Sync assessment data to HubSpot in real-time
	 *
	 * @param int $user_id WordPress user ID
	 * @param string $assessment_type Assessment type (e.g., 'weight-loss')
	 * @param array $form_data Form data from assessment submission
	 * @return array Sync result with status and details
	 */
	public function sync_assessment_to_hubspot( $user_id, $assessment_type, $form_data ) {
		error_log( "ENNU HubSpot Sync: Starting real-time sync for user {$user_id}, assessment {$assessment_type}" );
		
		// Ensure API parameters are initialized
		$this->init_api_params();
		
		try {
			// 1. Sync contact properties
			$contact_result = $this->sync_contact_properties( $user_id, $assessment_type, $form_data );
			
			// 2. Create custom object record for historical tracking
			$custom_object_result = $this->create_custom_object_record( $user_id, $assessment_type, $form_data );
			
			// 3. Log results
			$this->log_sync_results( $user_id, $assessment_type, $contact_result, $custom_object_result );
			
			$overall_success = $contact_result['success'] && $custom_object_result['success'];
			
			error_log( "ENNU HubSpot Sync: Completed for user {$user_id}, assessment {$assessment_type}. Success: " . ( $overall_success ? 'true' : 'false' ) );
			
			return array(
				'success' => $overall_success,
				'contact_sync' => $contact_result,
				'custom_object_sync' => $custom_object_result,
				'timestamp' => current_time( 'mysql' )
			);
			
		} catch ( Exception $e ) {
			error_log( "ENNU HubSpot Sync: Error syncing assessment for user {$user_id}: " . $e->getMessage() );
			return array(
				'success' => false,
				'error' => $e->getMessage(),
				'timestamp' => current_time( 'mysql' )
			);
		}
	}

	/**
	 * Sync contact properties to HubSpot
	 *
	 * @param int $user_id WordPress user ID
	 * @param string $assessment_type Assessment type
	 * @param array $form_data Form data
	 * @return array Sync result
	 */
	private function sync_contact_properties( $user_id, $assessment_type, $form_data ) {
		$user = get_user_by( 'ID', $user_id );
		if ( ! $user ) {
			return array( 'success' => false, 'error' => 'User not found' );
		}

		// Prepare contact data
		$contact_data = $this->prepare_contact_data( $user_id, $assessment_type, $form_data );
		
		// Get or create HubSpot contact
		$hubspot_contact_id = $this->get_or_create_hubspot_contact( $user_id, $user->user_email );
		
		if ( ! $hubspot_contact_id ) {
			return array( 'success' => false, 'error' => 'Failed to get or create HubSpot contact' );
		}

		// Update contact properties
		$url = $this->api_base_url . '/crm/v3/objects/contacts/' . $hubspot_contact_id;
		
		$data = array(
			'properties' => $contact_data
		);

		$response = wp_remote_request( $url, array(
			'method' => 'PATCH',
			'headers' => $this->api_params['headers'],
			'body' => json_encode( $data ),
			'timeout' => 30
		) );

		if ( is_wp_error( $response ) ) {
			return array( 'success' => false, 'error' => $response->get_error_message() );
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		
		if ( $status_code === 200 ) {
			return array( 'success' => true, 'contact_id' => $hubspot_contact_id );
		} else {
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			$error_message = isset( $body['message'] ) ? $body['message'] : 'Unknown error';
			return array( 'success' => false, 'error' => $error_message );
		}
	}

	/**
	 * Create custom object record for historical tracking
	 *
	 * @param int $user_id WordPress user ID
	 * @param string $assessment_type Assessment type
	 * @param array $form_data Form data
	 * @return array Sync result
	 */
	private function create_custom_object_record( $user_id, $assessment_type, $form_data ) {
		$user = get_user_by( 'ID', $user_id );
		if ( ! $user ) {
			return array( 'success' => false, 'error' => 'User not found' );
		}

		// Prepare custom object data
		$custom_object_data = $this->prepare_custom_object_data( $user_id, $assessment_type, $form_data );
		
		// Create custom object record using the correct object type
		$custom_object_name = $this->get_custom_object_name();
		$url = $this->api_base_url . '/crm/v3/objects/' . $custom_object_name;
		
		$data = array(
			'properties' => $custom_object_data
		);

		$response = wp_remote_post( $url, array(
			'headers' => $this->api_params['headers'],
			'body' => json_encode( $data ),
			'timeout' => 30
		) );

		if ( is_wp_error( $response ) ) {
			error_log( "ENNU HubSpot: Error creating custom object: " . $response->get_error_message() );
			return array( 'success' => false, 'error' => $response->get_error_message() );
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		
		if ( $status_code === 201 ) {
			$record_id = isset( $body['id'] ) ? $body['id'] : null;
			error_log( "ENNU HubSpot: Successfully created custom object record: " . $record_id );
			
			// Automatically associate the custom object record with the contact
			if ( $record_id ) {
				$association_result = $this->associate_custom_object_with_contact( $record_id, $user_id, $user->user_email, $assessment_type );
				if ( $association_result['success'] ) {
					error_log( "ENNU HubSpot: Successfully associated custom object record {$record_id} with contact" );
				} else {
					error_log( "ENNU HubSpot: Failed to associate custom object record: " . $association_result['error'] );
				}
			}
			
			return array( 'success' => true, 'record_id' => $record_id );
		} else {
			$error_message = isset( $body['message'] ) ? $body['message'] : 'Unknown error';
			error_log( "ENNU HubSpot: Error creating custom object. Status: {$status_code}, Response: " . json_encode( $body ) );
			return array( 'success' => false, 'error' => $error_message );
		}
	}

	/**
	 * Associate custom object record with contact
	 *
	 * @param string $custom_object_id Custom object record ID
	 * @param int $user_id WordPress user ID
	 * @param string $email User email
	 * @param string $assessment_type Assessment type
	 * @return array Association result
	 */
	private function associate_custom_object_with_contact( $custom_object_id, $user_id, $email, $assessment_type = '' ) {
		// First, get the contact ID
		$contact_id = $this->get_or_create_hubspot_contact( $user_id, $email );
		
		if ( ! $contact_id ) {
			return array( 'success' => false, 'error' => 'Could not find or create contact' );
		}

		// Get the custom object name
		$custom_object_name = $this->get_custom_object_name();
		
		// Create association using HubSpot's Same Object Associations API
		$url = $this->api_base_url . '/crm/v4/objects/' . $custom_object_name . '/' . $custom_object_id . '/associations/contacts/' . $contact_id . '/assessment_record';
		
		$data = array(
			'types' => array(
				array(
					'associationCategory' => 'HUBSPOT_DEFINED',
					'associationTypeId' => 1
				)
			)
		);

		error_log( "ENNU HubSpot: Creating association between custom object {$custom_object_id} and contact {$contact_id}" );
		error_log( "ENNU HubSpot: Association URL: {$url}" );
		error_log( "ENNU HubSpot: Association data: " . json_encode( $data ) );

		$response = wp_remote_request( $url, array(
			'method' => 'PUT',
			'headers' => $this->api_params['headers'],
			'body' => json_encode( $data ),
			'timeout' => 30
		) );

		if ( is_wp_error( $response ) ) {
			error_log( "ENNU HubSpot: Error creating association: " . $response->get_error_message() );
			return array( 'success' => false, 'error' => $response->get_error_message() );
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		
		error_log( "ENNU HubSpot: Association response status: {$status_code}" );
		error_log( "ENNU HubSpot: Association response body: " . json_encode( $body ) );
		
		if ( $status_code === 200 || $status_code === 201 ) {
			error_log( "ENNU HubSpot: Successfully created association between custom object {$custom_object_id} and contact {$contact_id}" );
			
			// Log activity on the contact record
			$activity_result = $this->log_assessment_activity( $contact_id, $user_id, $assessment_type );
			if ( $activity_result['success'] ) {
				error_log( "ENNU HubSpot: Successfully logged assessment activity on contact {$contact_id}" );
			} else {
				error_log( "ENNU HubSpot: Failed to log assessment activity: " . $activity_result['error'] );
			}
			
			// Add contact to assessment-specific lists
			$list_result = $this->add_contact_to_assessment_lists( $contact_id, $assessment_type );
			if ( $list_result['success'] ) {
				error_log( "ENNU HubSpot: Successfully added contact {$contact_id} to assessment lists" );
			} else {
				error_log( "ENNU HubSpot: Failed to add contact to assessment lists: " . $list_result['error'] );
			}
			
			return array( 'success' => true, 'contact_id' => $contact_id );
		} else {
			$error_message = isset( $body['message'] ) ? $body['message'] : 'Unknown error';
			error_log( "ENNU HubSpot: Error creating association. Status: {$status_code}, Response: " . json_encode( $body ) );
			return array( 'success' => false, 'error' => $error_message );
		}
	}

	/**
	 * Log assessment activity on contact record
	 *
	 * @param string $contact_id HubSpot contact ID
	 * @param int $user_id WordPress user ID
	 * @param string $assessment_type Assessment type
	 * @return array Activity logging result
	 */
	private function log_assessment_activity( $contact_id, $user_id, $assessment_type ) {
		$user = get_user_by( 'ID', $user_id );
		if ( ! $user ) {
			return array( 'success' => false, 'error' => 'User not found' );
		}

		// Get assessment display name
		$assessment_display_name = $this->get_assessment_display_name( $assessment_type );
		
		// Create activity note content
		$note_content = sprintf(
			'✅ **Assessment Completed**\n\n' .
			'**Assessment Type:** %s\n' .
			'**Completed By:** %s %s (%s)\n' .
			'**Completion Date:** %s\n' .
			'**Status:** Assessment data synced to HubSpot\n\n' .
			'This assessment has been automatically processed and all data has been synced to both the Contact record and Basic Assessment custom object.',
			$assessment_display_name,
			$user->first_name ?: 'User',
			$user->last_name ?: '',
			$user->user_email,
			current_time( 'l, F j, Y \@ g:i A' )
		);

		// Log activity using HubSpot's Activity API
		$url = $this->api_base_url . '/crm/v3/objects/contacts/' . $contact_id . '/notes';
		
		$data = array(
			'properties' => array(
				'hs_note_body' => $note_content,
				'hs_timestamp' => time() * 1000 // HubSpot expects milliseconds
			)
		);

		error_log( "ENNU HubSpot: Logging assessment activity on contact {$contact_id}" );
		error_log( "ENNU HubSpot: Activity URL: {$url}" );
		error_log( "ENNU HubSpot: Activity data: " . json_encode( $data ) );

		$response = wp_remote_post( $url, array(
			'headers' => $this->api_params['headers'],
			'body' => json_encode( $data ),
			'timeout' => 30
		) );

		if ( is_wp_error( $response ) ) {
			error_log( "ENNU HubSpot: Error logging activity: " . $response->get_error_message() );
			return array( 'success' => false, 'error' => $response->get_error_message() );
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		
		error_log( "ENNU HubSpot: Activity response status: {$status_code}" );
		error_log( "ENNU HubSpot: Activity response body: " . json_encode( $body ) );
		
		if ( $status_code === 201 ) {
			$note_id = isset( $body['id'] ) ? $body['id'] : null;
			error_log( "ENNU HubSpot: Successfully logged assessment activity. Note ID: {$note_id}" );
			return array( 'success' => true, 'note_id' => $note_id );
		} else {
			$error_message = isset( $body['message'] ) ? $body['message'] : 'Unknown error';
			error_log( "ENNU HubSpot: Error logging activity. Status: {$status_code}, Response: " . json_encode( $body ) );
			return array( 'success' => false, 'error' => $error_message );
		}
	}

	/**
	 * Get assessment display name
	 *
	 * @param string $assessment_type Assessment type
	 * @return string Assessment display name
	 */
	private function get_assessment_display_name( $assessment_type ) {
		$assessment_names = array(
			'weight-loss' => 'Weight Loss Assessment',
			'basic' => 'Basic Assessment',
			'comprehensive' => 'Comprehensive Assessment',
			'wellness' => 'Wellness Assessment',
			'fitness' => 'Fitness Assessment',
			'nutrition' => 'Nutrition Assessment'
		);
		
		return isset( $assessment_names[ $assessment_type ] ) ? $assessment_names[ $assessment_type ] : ucfirst( str_replace( '-', ' ', $assessment_type ) ) . ' Assessment';
	}

	/**
	 * Add contact to assessment-specific lists
	 *
	 * @param string $contact_id HubSpot contact ID
	 * @param string $assessment_type Assessment type
	 * @return array List addition result
	 */
	private function add_contact_to_assessment_lists( $contact_id, $assessment_type ) {
		// Get assessment display name
		$assessment_display_name = $this->get_assessment_display_name( $assessment_type );
		
		// Define list names for different assessment types
		$list_names = array(
			'weight-loss' => 'Weight Loss Assessment Completed',
			'basic' => 'Basic Assessment Completed',
			'comprehensive' => 'Comprehensive Assessment Completed',
			'wellness' => 'Wellness Assessment Completed',
			'fitness' => 'Fitness Assessment Completed',
			'nutrition' => 'Nutrition Assessment Completed'
		);
		
		$list_name = isset( $list_names[ $assessment_type ] ) ? $list_names[ $assessment_type ] : ucfirst( str_replace( '-', ' ', $assessment_type ) ) . ' Assessment Completed';
		
		// First, try to find existing list
		$list_id = $this->find_or_create_list( $list_name, $assessment_type );
		
		if ( ! $list_id ) {
			return array( 'success' => false, 'error' => 'Could not find or create list' );
		}
		
		// Add contact to the list
		$url = $this->api_base_url . '/crm/v3/lists/' . $list_id . '/memberships/add';
		
		$data = array(
			'vids' => array( intval( $contact_id ) )
		);
		
		error_log( "ENNU HubSpot: Adding contact {$contact_id} to list {$list_name} (ID: {$list_id})" );
		error_log( "ENNU HubSpot: List URL: {$url}" );
		error_log( "ENNU HubSpot: List data: " . json_encode( $data ) );
		
		$response = wp_remote_post( $url, array(
			'headers' => $this->api_params['headers'],
			'body' => json_encode( $data ),
			'timeout' => 30
		) );
		
		if ( is_wp_error( $response ) ) {
			error_log( "ENNU HubSpot: Error adding contact to list: " . $response->get_error_message() );
			return array( 'success' => false, 'error' => $response->get_error_message() );
		}
		
		$status_code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		
		error_log( "ENNU HubSpot: List response status: {$status_code}" );
		error_log( "ENNU HubSpot: List response body: " . json_encode( $body ) );
		
		if ( $status_code === 200 || $status_code === 201 ) {
			error_log( "ENNU HubSpot: Successfully added contact {$contact_id} to list {$list_name}" );
			return array( 'success' => true, 'list_id' => $list_id, 'list_name' => $list_name );
		} else {
			$error_message = isset( $body['message'] ) ? $body['message'] : 'Unknown error';
			error_log( "ENNU HubSpot: Error adding contact to list. Status: {$status_code}, Response: " . json_encode( $body ) );
			return array( 'success' => false, 'error' => $error_message );
		}
	}
	
	/**
	 * Find or create a HubSpot list
	 *
	 * @param string $list_name List name
	 * @param string $assessment_type Assessment type
	 * @return string|false List ID or false if not found/created
	 */
	private function find_or_create_list( $list_name, $assessment_type ) {
		// First, try to find existing list
		$list_id = $this->find_list_by_name( $list_name );
		
		if ( $list_id ) {
			error_log( "ENNU HubSpot: Found existing list '{$list_name}' with ID: {$list_id}" );
			return $list_id;
		}
		
		// Create new list if not found
		$list_id = $this->create_assessment_list( $list_name, $assessment_type );
		
		if ( $list_id ) {
			error_log( "ENNU HubSpot: Created new list '{$list_name}' with ID: {$list_id}" );
			return $list_id;
		}
		
		return false;
	}
	
	/**
	 * Find list by name
	 *
	 * @param string $list_name List name
	 * @return string|false List ID or false if not found
	 */
	private function find_list_by_name( $list_name ) {
		$url = $this->api_base_url . '/crm/v3/lists?name=' . urlencode( $list_name );
		
		$response = wp_remote_get( $url, array(
			'headers' => $this->api_params['headers'],
			'timeout' => 30
		) );
		
		if ( is_wp_error( $response ) ) {
			error_log( "ENNU HubSpot: Error finding list: " . $response->get_error_message() );
			return false;
		}
		
		$status_code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		
		if ( $status_code === 200 && isset( $body['results'] ) && count( $body['results'] ) > 0 ) {
			// Return the first matching list ID
			return $body['results'][0]['id'];
		}
		
		return false;
	}
	
	/**
	 * Create a new assessment list
	 *
	 * @param string $list_name List name
	 * @param string $assessment_type Assessment type
	 * @return string|false List ID or false if creation failed
	 */
	private function create_assessment_list( $list_name, $assessment_type ) {
		$url = $this->api_base_url . '/crm/v3/lists';
		
		// Create list description
		$description = "Contacts who have completed the {$this->get_assessment_display_name( $assessment_type )}. This list is automatically managed by the ENNU Life Assessments plugin.";
		
		// CORRECT HubSpot API format for list creation based on official docs
		$data = array(
			'name' => $list_name,
			'description' => $description,
			'listType' => 'STATIC', // Static list for assessment completions
			'objectTypeId' => '0-1', // Contact object type ID
			'processingType' => 'MANUAL' // Manual processing type
		);
		
		error_log( "ENNU HubSpot: Creating new list '{$list_name}'" );
		error_log( "ENNU HubSpot: Create list URL: {$url}" );
		error_log( "ENNU HubSpot: Create list data: " . json_encode( $data ) );
		
		$response = wp_remote_post( $url, array(
			'headers' => $this->api_params['headers'],
			'body' => json_encode( $data ),
			'timeout' => 30
		) );
		
		if ( is_wp_error( $response ) ) {
			error_log( "ENNU HubSpot: Error creating list: " . $response->get_error_message() );
			return false;
		}
		
		$status_code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		
		error_log( "ENNU HubSpot: Create list response status: {$status_code}" );
		error_log( "ENNU HubSpot: Create list response body: " . json_encode( $body ) );
		
		if ( $status_code === 201 && isset( $body['id'] ) ) {
			error_log( "ENNU HubSpot: Successfully created list '{$list_name}' with ID: {$body['id']}" );
			return $body['id'];
		} else {
			$error_message = isset( $body['message'] ) ? $body['message'] : 'Unknown error';
			error_log( "ENNU HubSpot: Error creating list. Status: {$status_code}, Response: " . json_encode( $body ) );
			return false;
		}
	}

	/**
	 * Prepare contact data for HubSpot sync
	 *
	 * @param int $user_id WordPress user ID
	 * @param string $assessment_type Assessment type
	 * @param array $form_data Form data
	 * @return array Contact properties
	 */
	private function prepare_contact_data( $user_id, $assessment_type, $form_data ) {
		$user = get_user_by( 'ID', $user_id );
		$contact_properties = array();

		// Basic contact info - prioritize form data over user meta for real-time sync
		$contact_properties['email'] = $form_data['email'] ?? $user->user_email;
		$contact_properties['firstname'] = $form_data['first_name'] ?? get_user_meta( $user_id, 'first_name', true ) ?: '';
		$contact_properties['lastname'] = $form_data['last_name'] ?? get_user_meta( $user_id, 'last_name', true ) ?: '';
		$contact_properties['phone'] = $form_data['billing_phone'] ?? get_user_meta( $user_id, 'ennu_global_billing_phone', true ) ?: '';

		// Global fields - prioritize form data for real-time sync
		$contact_properties['ennu_global_gender'] = $form_data['gender'] ?? get_user_meta( $user_id, 'ennu_global_gender', true ) ?: '';
		$contact_properties['ennu_global_date_of_birth'] = $form_data['date_of_birth'] ?? get_user_meta( $user_id, 'ennu_global_date_of_birth', true ) ?: '';
		
		// Height and weight - prioritize form data
		if ( isset( $form_data['height_weight'] ) && is_array( $form_data['height_weight'] ) ) {
			$height_value = $form_data['height_weight']['height'] ?? '';
			// Sanitize height value to remove quotes that cause JSON issues
			$height_value = str_replace( array( '"', "'" ), '', $height_value );
			$contact_properties['ennu_global_height'] = $height_value;
			$contact_properties['ennu_global_weight'] = $form_data['height_weight']['weight'] ?? '';
		} else {
			$height_weight = get_user_meta( $user_id, 'ennu_global_height_weight', true );
			if ( is_array( $height_weight ) ) {
				$height_value = isset( $height_weight['height'] ) ? $height_weight['height'] : '';
				// Sanitize height value to remove quotes that cause JSON issues
				$height_value = str_replace( array( '"', "'" ), '', $height_value );
				$contact_properties['ennu_global_height'] = $height_value;
				$contact_properties['ennu_global_weight'] = isset( $height_weight['weight'] ) ? $height_weight['weight'] : '';
			} else {
				$contact_properties['ennu_global_height'] = '';
				$contact_properties['ennu_global_weight'] = '';
			}
		}
		
		$contact_properties['ennu_global_bmi'] = $form_data['bmi'] ?? get_user_meta( $user_id, 'ennu_global_bmi', true ) ?: '';
		$contact_properties['ennu_global_age'] = $form_data['age'] ?? get_user_meta( $user_id, 'ennu_global_exact_age', true ) ?: '';

		// Assessment-specific fields - use form data directly for real-time sync
		$assessment_fields = $this->get_assessment_specific_fields_from_form( $user_id, $assessment_type, $form_data );
		$contact_properties = array_merge( $contact_properties, $assessment_fields );

		// Dashboard aggregated scores - these should be available from user meta
		$contact_properties['ennu_life_score'] = get_user_meta( $user_id, 'ennu_life_score', true ) ?: 0;
		$contact_properties['ennu_pillar_mind_score'] = get_user_meta( $user_id, 'ennu_pillar_mind_score', true ) ?: 0;
		$contact_properties['ennu_pillar_body_score'] = get_user_meta( $user_id, 'ennu_pillar_body_score', true ) ?: 0;
		$contact_properties['ennu_pillar_lifestyle_score'] = get_user_meta( $user_id, 'ennu_pillar_lifestyle_score', true ) ?: 0;
		$contact_properties['ennu_pillar_aesthetics_score'] = get_user_meta( $user_id, 'ennu_pillar_aesthetics_score', true ) ?: 0;
		$contact_properties['total_assessments_completed'] = get_user_meta( $user_id, 'ennu_total_assessments_completed', true ) ?: 0;

		return $contact_properties;
	}

	/**
	 * Get assessment-specific fields from form data for real-time sync
	 *
	 * @param int $user_id WordPress user ID
	 * @param string $assessment_type Assessment type
	 * @param array $form_data Form data
	 * @return array Assessment-specific fields
	 */
	private function get_assessment_specific_fields_from_form( $user_id, $assessment_type, $form_data ) {
		$fields = array();
		
		// Get assessment prefix for field mapping
		$assessment_prefix = $this->get_assessment_prefix( $assessment_type );
		
		// Get assessment configuration to know what fields to look for
		$assessment_config = $this->get_assessment_config( $assessment_type );
		if ( ! $assessment_config || is_wp_error( $assessment_config ) ) {
			return $fields;
		}
		
		// Process each question in the assessment from form data
		foreach ( $assessment_config['questions'] as $question_key => $question ) {
			// Skip global fields that are already handled
			if ( isset( $question['global_key'] ) ) {
				continue;
			}
			
			// Generate HubSpot field ID (not name) for API compatibility
			$hubspot_field_id = $this->generate_hubspot_field_id( $question_key, $assessment_type );
			
			// Get value from form data first, then fallback to user meta
			$field_value = '';
			if ( isset( $form_data[ $question_key ] ) ) {
				$field_value = is_array( $form_data[ $question_key ] ) ? implode( ', ', $form_data[ $question_key ] ) : $form_data[ $question_key ];
			} else {
				// Fallback to user meta
				$meta_key = 'ennu_' . $assessment_type . '_' . $question_key;
				$field_value = get_user_meta( $user_id, $meta_key, true ) ?: '';
			}
			
			$fields[ $hubspot_field_id ] = $field_value;
		}
		
		// Add assessment scores
		$score_fields = $this->get_assessment_score_fields( $user_id, $assessment_type );
		$fields = array_merge( $fields, $score_fields );
		
		return $fields;
	}

	/**
	 * Get assessment-specific fields for Custom Object (questions only, no scores)
	 *
	 * @param int $user_id WordPress user ID
	 * @param string $assessment_type Assessment type
	 * @param array $form_data Form data
	 * @return array Assessment-specific fields for Custom Object
	 */
	private function get_assessment_specific_fields_for_custom_object( $user_id, $assessment_type, $form_data ) {
		$fields = array();
		
		// Get assessment prefix for field mapping
		$assessment_prefix = $this->get_assessment_prefix( $assessment_type );
		
		// Get assessment configuration to know what fields to look for
		$assessment_config = $this->get_assessment_config( $assessment_type );
		if ( ! $assessment_config || is_wp_error( $assessment_config ) ) {
			return $fields;
		}
		
		// Process each question in the assessment from form data
		foreach ( $assessment_config['questions'] as $question_key => $question ) {
			// Skip global fields that are already handled
			if ( isset( $question['global_key'] ) ) {
				continue;
			}
			
			// Generate HubSpot field ID (not name) for API compatibility
			$hubspot_field_id = $this->generate_hubspot_field_id( $question_key, $assessment_type );
			
			// Get value from form data first, then fallback to user meta
			$field_value = '';
			if ( isset( $form_data[ $question_key ] ) ) {
				$field_value = is_array( $form_data[ $question_key ] ) ? implode( ', ', $form_data[ $question_key ] ) : $form_data[ $question_key ];
			} else {
				// Fallback to user meta
				$meta_key = 'ennu_' . $assessment_type . '_' . $question_key;
				$field_value = get_user_meta( $user_id, $meta_key, true ) ?: '';
			}
			
			$fields[ $hubspot_field_id ] = $field_value;
		}
		
		// For Custom Object, we only send question fields, not score fields
		// Score fields are sent to Contact Object only
		
		return $fields;
	}

	/**
	 * Get assessment-specific fields for a user
	 *
	 * @param int $user_id WordPress user ID
	 * @param string $assessment_type Assessment type
	 * @return array Assessment-specific fields
	 */
	private function get_assessment_specific_fields( $user_id, $assessment_type ) {
		$fields = array();
		
		// Get assessment prefix for field mapping
		$assessment_prefix = $this->get_assessment_prefix( $assessment_type );
		
		// Get assessment configuration to know what fields to look for
		$assessment_config = $this->get_assessment_config( $assessment_type );
		if ( ! $assessment_config || is_wp_error( $assessment_config ) ) {
			return $fields;
		}
		
		// Process each question in the assessment
		foreach ( $assessment_config['questions'] as $question_key => $question ) {
			// Skip global fields that are already handled
			if ( isset( $question['global_key'] ) ) {
				continue;
			}
			
			// Generate HubSpot field ID (not name) for API compatibility
			$hubspot_field_id = $this->generate_hubspot_field_id( $question_key, $assessment_type );
			
			// Get WordPress meta key for this question
			$meta_key = 'ennu_' . $assessment_type . '_' . $question_key;
			$field_value = get_user_meta( $user_id, $meta_key, true ) ?: '';
			
			$fields[ $hubspot_field_id ] = $field_value;
		}
		
		// Add assessment scores
		$score_fields = $this->get_assessment_score_fields( $user_id, $assessment_type );
		$fields = array_merge( $fields, $score_fields );
		
		return $fields;
	}
	
	/**
	 * Get assessment configuration
	 *
	 * @param string $assessment_type Assessment type
	 * @return array|WP_Error Assessment configuration
	 */
	private function get_assessment_config( $assessment_type ) {
		$file_path = plugin_dir_path( dirname( __FILE__ ) ) . 'includes/config/assessments/' . $assessment_type . '.php';
		
		if ( ! file_exists( $file_path ) ) {
			return new WP_Error( 'assessment_not_found', 'Assessment file not found: ' . $assessment_type );
		}
		
		$assessment_config = include $file_path;
		
		if ( ! isset( $assessment_config['questions'] ) ) {
			return new WP_Error( 'no_questions', 'No questions found in assessment: ' . $assessment_type );
		}
		
		return $assessment_config;
	}
	
	/**
	 * Get assessment score fields
	 *
	 * @param int $user_id WordPress user ID
	 * @param string $assessment_type Assessment type
	 * @return array Score fields
	 */
	private function get_assessment_score_fields( $user_id, $assessment_type ) {
		$fields = array();
		$assessment_prefix = $this->get_assessment_prefix( $assessment_type );
		
		// Common score field patterns - use proper HubSpot field IDs
		$score_patterns = array(
			'overall_score' => $this->generate_hubspot_field_id( 'overall_score', $assessment_type ),
			'category_motivation_goals_score' => $this->generate_hubspot_field_id( 'category_motivation_goals_score', $assessment_type ),
			'category_nutrition_score' => $this->generate_hubspot_field_id( 'category_nutrition_score', $assessment_type ),
			'category_physical_activity_score' => $this->generate_hubspot_field_id( 'category_physical_activity_score', $assessment_type ),
			'category_lifestyle_factors_score' => $this->generate_hubspot_field_id( 'category_lifestyle_factors_score', $assessment_type ),
			'category_psychological_factors_score' => $this->generate_hubspot_field_id( 'category_psychological_factors_score', $assessment_type ),
			'category_weight_loss_history_score' => $this->generate_hubspot_field_id( 'category_weight_loss_history_score', $assessment_type ),
			'category_behavioral_patterns_score' => $this->generate_hubspot_field_id( 'category_behavioral_patterns_score', $assessment_type ),
			'category_medical_factors_score' => $this->generate_hubspot_field_id( 'category_medical_factors_score', $assessment_type ),
			'category_social_support_score' => $this->generate_hubspot_field_id( 'category_social_support_score', $assessment_type ),
			'category_aesthetics_score' => $this->generate_hubspot_field_id( 'category_aesthetics_score', $assessment_type ),
			'pillar_mind_score' => $this->generate_hubspot_field_id( 'pillar_mind_score', $assessment_type ),
			'pillar_body_score' => $this->generate_hubspot_field_id( 'pillar_body_score', $assessment_type ),
			'pillar_lifestyle_score' => $this->generate_hubspot_field_id( 'pillar_lifestyle_score', $assessment_type ),
			'pillar_aesthetics_score' => $this->generate_hubspot_field_id( 'pillar_aesthetics_score', $assessment_type ),
		);
		
		foreach ( $score_patterns as $score_key => $hubspot_field_id ) {
			$meta_key = 'ennu_' . $assessment_type . '_' . $score_key;
			$score_value = get_user_meta( $user_id, $meta_key, true ) ?: 0;
			$fields[ $hubspot_field_id ] = $score_value;
		}
		
		return $fields;
	}

	/**
	 * Prepare custom object data for HubSpot
	 *
	 * @param int $user_id WordPress user ID
	 * @param string $assessment_type Assessment type
	 * @param array $form_data Form data
	 * @return array Custom object properties
	 */
	private function prepare_custom_object_data( $user_id, $assessment_type, $form_data ) {
		$user = get_user_by( 'ID', $user_id );
		$custom_object_properties = array();

		// Get user data for name formatting
		$first_name = get_user_meta( $user_id, 'first_name', true ) ?: '';
		$last_name = get_user_meta( $user_id, 'last_name', true ) ?: '';
		$gender = get_user_meta( $user_id, 'ennu_global_gender', true ) ?: '';
		$dob = get_user_meta( $user_id, 'ennu_global_date_of_birth', true ) ?: '';
		
		// Calculate age from DOB
		$age = '';
		if ( $dob ) {
			$dob_date = new DateTime( $dob );
			$now = new DateTime();
			$age = $now->diff( $dob_date )->y;
		}

		// Format the name dynamically based on assessment type
		$full_name = trim( $first_name . ' ' . $last_name );
		$gender_initial = strtoupper( substr( $gender, 0, 1 ) );
		$assessment_title = ucfirst( str_replace( '-', ' ', $assessment_type ) );
		$formatted_name = $full_name . ' ' . $gender_initial . ' ' . $age . ' - ' . $assessment_title;

		// Primary display property: basic_assessments_name
		$custom_object_properties['basic_assessments_name'] = $formatted_name;

		// Secondary display properties
		$custom_object_properties['basic_assessments_submission__'] = $this->get_attempt_number( $user_id, $assessment_type );
		$custom_object_properties['basic_assessments_submission_day'] = current_time( 'l, F j, Y \@ g:i A' );

		// Assessment-specific data - make this dynamic for all assessment types
		// Only send fields that actually exist in the Custom Object
		$assessment_fields = $this->get_assessment_specific_fields_for_custom_object( $user_id, $assessment_type, $form_data );
		$custom_object_properties = array_merge( $custom_object_properties, $assessment_fields );

		return $custom_object_properties;
	}

	/**
	 * Get or create HubSpot contact
	 *
	 * @param int $user_id WordPress user ID
	 * @param string $email User email
	 * @return string|false HubSpot contact ID or false on failure
	 */
	private function get_or_create_hubspot_contact( $user_id, $email ) {
		// First, try to find existing contact by email
		$existing_contact_id = $this->find_contact_by_email( $email );
		
		if ( $existing_contact_id ) {
			return $existing_contact_id;
		}

		// Create new contact if not found
		return $this->create_hubspot_contact( $user_id, $email );
	}

	/**
	 * Find HubSpot contact by email
	 *
	 * @param string $email Email address
	 * @return string|false Contact ID or false if not found
	 */
	private function find_contact_by_email( $email ) {
		$url = $this->api_base_url . '/crm/v3/objects/contacts/search';
		
		$data = array(
			'filterGroups' => array(
				array(
					'filters' => array(
						array(
							'propertyName' => 'email',
							'operator' => 'EQ',
							'value' => $email
						)
					)
				)
			),
			'limit' => 1,
			'properties' => array('email')
		);
		
		error_log( "ENNU HubSpot: Searching for contact with email: {$email}" );
		error_log( "ENNU HubSpot: Search URL: {$url}" );
		error_log( "ENNU HubSpot: Search data: " . json_encode( $data ) );

		$response = wp_remote_post( $url, array(
			'headers' => $this->api_params['headers'],
			'body' => json_encode( $data ),
			'timeout' => 30
		) );

		if ( is_wp_error( $response ) ) {
			error_log( "ENNU HubSpot: Error searching for contact: " . $response->get_error_message() );
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		
		error_log( "ENNU HubSpot: Search response status: {$status_code}" );
		error_log( "ENNU HubSpot: Search response body: " . json_encode( $body ) );
		
		if ( $status_code !== 200 ) {
			error_log( "ENNU HubSpot: Error searching for contact. Status: {$status_code}, Response: " . json_encode( $body ) );
			return false;
		}
		
		if ( isset( $body['results'] ) && ! empty( $body['results'] ) ) {
			$contact_id = $body['results'][0]['id'];
			error_log( "ENNU HubSpot: Found existing contact with ID: {$contact_id}" );
			return $contact_id;
		}

		error_log( "ENNU HubSpot: No existing contact found for email: {$email}" );
		return false;
	}

	/**
	 * Create new HubSpot contact
	 *
	 * @param int $user_id WordPress user ID
	 * @param string $email Email address
	 * @return string|false Contact ID or false on failure
	 */
	private function create_hubspot_contact( $user_id, $email ) {
		$url = $this->api_base_url . '/crm/v3/objects/contacts';
		
		$data = array(
			'properties' => array(
				'email' => $email,
				'firstname' => get_user_meta( $user_id, 'first_name', true ) ?: '',
				'lastname' => get_user_meta( $user_id, 'last_name', true ) ?: ''
			)
		);

		error_log( "ENNU HubSpot: Creating new contact with email: {$email}" );
		error_log( "ENNU HubSpot: Create URL: {$url}" );
		error_log( "ENNU HubSpot: Create data: " . json_encode( $data ) );

		$response = wp_remote_post( $url, array(
			'headers' => $this->api_params['headers'],
			'body' => json_encode( $data ),
			'timeout' => 30
		) );

		if ( is_wp_error( $response ) ) {
			error_log( "ENNU HubSpot: Error creating contact: " . $response->get_error_message() );
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		
		error_log( "ENNU HubSpot: Create response status: {$status_code}" );
		error_log( "ENNU HubSpot: Create response body: " . json_encode( $body ) );
		
		if ( $status_code === 201 && isset( $body['id'] ) ) {
			$contact_id = $body['id'];
			error_log( "ENNU HubSpot: Successfully created contact with ID: {$contact_id}" );
			return $contact_id;
		}

		error_log( "ENNU HubSpot: Failed to create contact. Status: {$status_code}, Response: " . json_encode( $body ) );
		return false;
	}

	/**
	 * Get attempt number for assessment
	 *
	 * @param int $user_id WordPress user ID
	 * @param string $assessment_type Assessment type
	 * @return int Attempt number
	 */
	private function get_attempt_number( $user_id, $assessment_type ) {
		$attempt_key = 'ennu_' . $assessment_type . '_attempt_count';
		$attempt_count = get_user_meta( $user_id, $attempt_key, true );
		
		if ( ! $attempt_count ) {
			$attempt_count = 1;
		} else {
			$attempt_count = intval( $attempt_count ) + 1;
		}
		
		update_user_meta( $user_id, $attempt_key, $attempt_count );
		return $attempt_count;
	}

	/**
	 * Log sync results
	 *
	 * @param int $user_id WordPress user ID
	 * @param string $assessment_type Assessment type
	 * @param array $contact_result Contact sync result
	 * @param array $custom_object_result Custom object sync result
	 */
	private function log_sync_results( $user_id, $assessment_type, $contact_result, $custom_object_result ) {
		$sync_log = array(
			'user_id' => $user_id,
			'assessment_type' => $assessment_type,
			'contact_sync' => $contact_result,
			'custom_object_sync' => $custom_object_result,
			'timestamp' => current_time( 'mysql' )
		);

		// Store in user meta for debugging
		update_user_meta( $user_id, 'ennu_hubspot_sync_log_' . $assessment_type, $sync_log );
		
		// Log to error log
		error_log( "ENNU HubSpot Sync Log: " . json_encode( $sync_log ) );
	}

	/**
	 * AJAX handler for saving sync settings
	 *
	 * @since 64.6.99
	 */
	public function ajax_save_sync_settings() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$sync_enabled = isset( $_POST['sync_enabled'] ) ? (bool) $_POST['sync_enabled'] : false;
		
		update_option( 'ennu_hubspot_sync_enabled', $sync_enabled );
		
		wp_send_json_success( 'Sync settings saved successfully' );
	}

	/**
	 * AJAX handler for retrying failed syncs
	 *
	 * @since 64.6.99
	 */
	public function ajax_retry_failed_syncs() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$failed_syncs = get_option( 'ennu_hubspot_failed_syncs', array() );
		
		if ( empty( $failed_syncs ) ) {
			wp_send_json_success( 'No failed syncs to retry' );
		}

		$retry_count = 0;
		$success_count = 0;
		$still_failed = array();

		foreach ( $failed_syncs as $sync ) {
			$retry_count++;
			
			try {
				$result = $this->sync_assessment_to_hubspot( 
					$sync['user_id'], 
					$sync['assessment_type'], 
					$sync['form_data'] 
				);
				
				if ( $result['success'] ) {
					$success_count++;
				} else {
					$still_failed[] = $sync;
				}
			} catch ( Exception $e ) {
				$still_failed[] = $sync;
			}
		}

		// Update failed syncs list
		update_option( 'ennu_hubspot_failed_syncs', $still_failed );
		
		wp_send_json_success( "Retry completed: {$success_count} successful, " . count( $still_failed ) . " still failed" );
	}

	/**
	 * AJAX handler for testing API connection
	 *
	 * @since 64.6.99
	 */
	public function ajax_test_api_connection() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$result = $this->test_api_connection();
		
		if ( $result['success'] ) {
			wp_send_json_success( array(
				'message' => 'API connection successful',
				'result' => $result
			) );
		} else {
			wp_send_json_error( array(
				'message' => 'API connection failed',
				'result' => $result
			) );
		}
	}

	/**
	 * Check if property group exists
	 *
	 * @since 64.6.99
	 * @param string $object_type Object type (contacts, basic_assessments, etc.)
	 * @param string $group_name Property group name
	 * @return bool True if exists, false otherwise
	 */
	private function property_group_exists( $object_type, $group_name ) {
		$url = $this->api_base_url . '/properties/v2/' . $object_type . '/groups';
		$response = wp_remote_get( $url, $this->api_params );
		
		if ( is_wp_error( $response ) ) {
			error_log( 'ENNU HubSpot: Error checking property groups: ' . $response->get_error_message() );
			return false;
		}
		
		$status_code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		
		if ( $status_code === 200 && isset( $body['results'] ) ) {
			foreach ( $body['results'] as $group ) {
				if ( $group['name'] === $group_name ) {
					return true;
				}
			}
		}
		
		return false;
	}

	/**
	 * Check field and property group existence for Weight Loss assessment
	 *
	 * @since 64.6.99
	 * @return array Existence status for fields and groups
	 */
	private function check_weight_loss_field_existence() {
		$existence_status = array(
			'contact_properties' => array(
				'groups' => array(),
				'fields' => array()
			),
			'custom_object_properties' => array(
				'groups' => array(),
				'fields' => array()
			)
		);
		
		// Check property groups
		$contact_groups = array( 'Global', 'Weight Loss Assessment', 'Dashboard', 'User Profile' );
		$custom_object_groups = array( 'Metadata', 'Assessment Questions', 'Assessment Scores' );
		
		foreach ( $contact_groups as $group_name ) {
			$exists = $this->property_group_exists( 'contacts', $group_name );
			$existence_status['contact_properties']['groups'][$group_name] = $exists;
		}
		
		foreach ( $custom_object_groups as $group_name ) {
			$custom_object_name = $this->get_custom_object_name();
		$exists = $this->property_group_exists( $custom_object_name, $group_name );
			$existence_status['custom_object_properties']['groups'][$group_name] = $exists;
		}
		
		// Check individual fields
		$contact_fields = $this->get_weight_loss_contact_fields();
		$custom_object_fields = $this->get_weight_loss_custom_object_fields();
		
		foreach ( $contact_fields as $field ) {
			$exists = $this->property_exists( $field['name'], 'contacts' );
			$existence_status['contact_properties']['fields'][$field['name']] = $exists;
		}
		
		foreach ( $custom_object_fields as $field ) {
			$custom_object_name = $this->get_custom_object_name();
		$exists = $this->property_exists( $field['name'], $custom_object_name );
			$existence_status['custom_object_properties']['fields'][$field['name']] = $exists;
		}
		
		return $existence_status;
	}

	/**
	 * AJAX handler for previewing Weight Loss assessment fields
	 *
	 * @since 64.6.99
	 */
	public function ajax_preview_weight_loss_fields() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		error_log( 'ENNU HubSpot: Previewing Weight Loss fields via AJAX...' );

		try {
			$contact_fields = $this->get_weight_loss_contact_fields();
			$custom_object_fields = $this->get_weight_loss_custom_object_fields();
			$existence_status = $this->check_weight_loss_field_existence();
			
			// Get existing field details from HubSpot to include IDs
			// Add a small delay to ensure we get the latest data
			error_log( 'ENNU HubSpot: Waiting 1 second before fetching existing properties...' );
			sleep( 1 );
			
			$existing_contact_properties = $this->get_existing_properties( 'contacts' );
			$custom_object_name = $this->get_custom_object_name();
			$existing_custom_object_properties = $this->get_existing_properties( $custom_object_name );
			
			// Debug logging
			error_log( 'ENNU HubSpot: Found ' . count( $existing_contact_properties ) . ' existing contact properties' );
			error_log( 'ENNU HubSpot: Found ' . count( $existing_custom_object_properties ) . ' existing custom object properties' );
			
			// Log all existing contact properties for debugging
			error_log( 'ENNU HubSpot: All existing contact properties: ' . implode( ', ', array_keys( $existing_contact_properties ) ) );
			error_log( 'ENNU HubSpot: All existing custom object properties: ' . implode( ', ', array_keys( $existing_custom_object_properties ) ) );
			
			// Add HubSpot field IDs to contact fields
			foreach ( $contact_fields as &$field ) {
				if ( isset( $existing_contact_properties[$field['name']] ) ) {
					$field['hubspot_id'] = $existing_contact_properties[$field['name']]['id'];
					$field['hubspot_name'] = $existing_contact_properties[$field['name']]['name'];
					error_log( 'ENNU HubSpot: Field ' . $field['name'] . ' exists in HubSpot with ID: ' . $field['hubspot_id'] );
				} else {
					$field['hubspot_id'] = null;
					$field['hubspot_name'] = $field['name'];
					error_log( 'ENNU HubSpot: Field ' . $field['name'] . ' does NOT exist in HubSpot - will be created' );
				}
			}
			
			// Add HubSpot field IDs to custom object fields
			foreach ( $custom_object_fields as &$field ) {
				if ( isset( $existing_custom_object_properties[$field['name']] ) ) {
					$field['hubspot_id'] = $existing_custom_object_properties[$field['name']]['id'];
					$field['hubspot_name'] = $existing_custom_object_properties[$field['name']]['name'];
					error_log( 'ENNU HubSpot: Custom Object Field ' . $field['name'] . ' exists in HubSpot with ID: ' . $field['hubspot_id'] );
				} else {
					$field['hubspot_id'] = null;
					$field['hubspot_name'] = $field['name'];
					error_log( 'ENNU HubSpot: Custom Object Field ' . $field['name'] . ' does NOT exist in HubSpot - will be created' );
				}
			}
			
			$preview_data = array(
				'assessment_title' => 'Weight Loss Assessment',
				'contact_fields' => array(
					'count' => count( $contact_fields ),
					'fields' => $contact_fields
				),
				'custom_object_fields' => array(
					'count' => count( $custom_object_fields ),
					'fields' => $custom_object_fields
				),
				'total_fields' => count( $contact_fields ) + count( $custom_object_fields ),
				'existence_status' => $existence_status
			);
			
			wp_send_json_success( $preview_data );
			
		} catch ( Exception $e ) {
			wp_send_json_error( array(
				'message' => 'Error generating preview: ' . $e->getMessage()
			) );
		}
	}

	/**
	 * AJAX handler for creating Weight Loss assessment fields
	 *
	 * @since 64.6.99
	 */
	public function ajax_create_weight_loss_fields() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		error_log( 'ENNU HubSpot: Creating Weight Loss fields via AJAX...' );

		// Custom object should already exist in HubSpot

		$result = $this->create_weight_loss_fields();
		
		// Add a delay to allow HubSpot to process the changes
		if ( $result['success'] ) {
			error_log( 'ENNU HubSpot: Waiting 3 seconds for HubSpot to process field creation...' );
			sleep( 3 );
		}
		
		// Build detailed message
		$message = 'Weight Loss field creation completed!';
		$details = array();
		
		if ( isset( $result['contacts']['new_fields_count'] ) && $result['contacts']['new_fields_count'] > 0 ) {
			$details[] = 'Contact Properties: ' . $result['contacts']['new_fields_count'] . ' new fields created';
		}
		if ( isset( $result['contacts']['skipped_fields_count'] ) && $result['contacts']['skipped_fields_count'] > 0 ) {
			$details[] = 'Contact Properties: ' . $result['contacts']['skipped_fields_count'] . ' existing fields skipped';
		}
		if ( isset( $result['custom_object']['new_fields_count'] ) && $result['custom_object']['new_fields_count'] > 0 ) {
			$details[] = 'Custom Object Fields: ' . $result['custom_object']['new_fields_count'] . ' new fields created';
		}
		if ( isset( $result['custom_object']['skipped_fields_count'] ) && $result['custom_object']['skipped_fields_count'] > 0 ) {
			$details[] = 'Custom Object Fields: ' . $result['custom_object']['skipped_fields_count'] . ' existing fields skipped';
		}
		
		if ( ! empty( $details ) ) {
			$message .= ' (' . implode( ', ', $details ) . ')';
		}
		
		$total_new = ( isset( $result['contacts']['new_fields_count'] ) ? $result['contacts']['new_fields_count'] : 0 ) + 
					 ( isset( $result['custom_object']['new_fields_count'] ) ? $result['custom_object']['new_fields_count'] : 0 );
		$total_skipped = ( isset( $result['contacts']['skipped_fields_count'] ) ? $result['contacts']['skipped_fields_count'] : 0 ) + 
						 ( isset( $result['custom_object']['skipped_fields_count'] ) ? $result['custom_object']['skipped_fields_count'] : 0 );
		
		if ( $total_new > 0 ) {
			wp_send_json_success( array(
				'message' => $message,
				'result' => $result,
				'summary' => array(
					'new_fields' => $total_new,
					'skipped_fields' => $total_skipped,
					'total_processed' => $total_new + $total_skipped
				)
			) );
		} else {
			wp_send_json_success( array(
				'message' => 'All Weight Loss fields already exist! No new fields were created.',
				'result' => $result,
				'summary' => array(
					'new_fields' => 0,
					'skipped_fields' => $total_skipped,
					'total_processed' => $total_skipped
				)
			) );
		}
	}

	/**
	 * Run comprehensive Weight Loss field creation test
	 *
	 * @since 64.7.8
	 */
	public function run_weight_loss_test() {
		echo "<!DOCTYPE html>";
		echo "<html><head><title>ENNU Weight Loss Field Creation Test</title>";
		echo "<style>body{font-family:Arial,sans-serif;margin:20px;} pre{background:#f5f5f5;padding:10px;border-radius:5px;overflow-x:auto;} .success{color:green;} .error{color:red;} .info{color:blue;}</style>";
		echo "</head><body>";
		
		echo "<h1>🔧 ENNU Life Plugin - Weight Loss Field Creation Test</h1>";
		echo "<p><strong>Timestamp:</strong> " . date( 'Y-m-d H:i:s' ) . "</p>";
		echo "<hr>";
		
		// Test 1: API Connection
		echo "<h2>📡 Test 1: HubSpot API Connection</h2>";
		$api_test = $this->test_api_connection();
		echo "<pre>" . print_r( $api_test, true ) . "</pre>";
		echo "<hr>";
		
		// Test 2: Get Weight Loss Contact Fields
		echo "<h2>📋 Test 2: Weight Loss Contact Fields</h2>";
		$contact_fields = $this->get_weight_loss_contact_fields();
		echo "<p><strong>Contact Fields Count:</strong> " . count( $contact_fields ) . "</p>";
		echo "<p><strong>First 3 Contact Fields:</strong></p>";
		echo "<pre>" . print_r( array_slice( $contact_fields, 0, 3 ), true ) . "</pre>";
		echo "<hr>";
		
		// Test 3: Get Weight Loss Custom Object Fields
		echo "<h2>🏗️ Test 3: Weight Loss Custom Object Fields</h2>";
		$custom_object_fields = $this->get_weight_loss_custom_object_fields();
		echo "<p><strong>Custom Object Fields Count:</strong> " . count( $custom_object_fields ) . "</p>";
		echo "<p><strong>First 3 Custom Object Fields:</strong></p>";
		echo "<pre>" . print_r( array_slice( $custom_object_fields, 0, 3 ), true ) . "</pre>";
		echo "<hr>";
		
		// Test 4: Preview Weight Loss Fields
		echo "<h2>👁️ Test 4: Weight Loss Fields Preview</h2>";
		$preview_result = $this->ajax_preview_weight_loss_fields();
		echo "<pre>" . print_r( $preview_result, true ) . "</pre>";
		echo "<hr>";
		
		// Test 5: Create Weight Loss Fields
		echo "<h2>🚀 Test 5: Weight Loss Field Creation</h2>";
		echo "<p class='info'>Starting field creation process with full logging...</p>";
		
		// Enable detailed logging
		error_log( 'ENNU HubSpot: Starting comprehensive Weight Loss field creation test' );
		
		// Create fields with full response logging
		$creation_result = $this->create_weight_loss_fields();
		
		echo "<h3>📊 Creation Results:</h3>";
		echo "<pre>" . print_r( $creation_result, true ) . "</pre>";
		
		// Test 6: Field Statistics
		echo "<h2>📈 Test 6: Field Statistics</h2>";
		$field_stats = $this->ajax_get_field_statistics();
		echo "<pre>" . print_r( $field_stats, true ) . "</pre>";
		
		echo "<hr>";
		echo "<h2>📋 Summary</h2>";
		echo "<p><strong>Test completed at:</strong> " . date( 'Y-m-d H:i:s' ) . "</p>";
		
		// Display recent debug log entries
		echo "<h3>🔍 Recent HubSpot Debug Log Entries:</h3>";
		$debug_log = file_get_contents( WP_CONTENT_DIR . '/debug.log' );
		$lines = explode( "\n", $debug_log );
		$recent_lines = array_slice( $lines, -100 );
		$hubspot_lines = array_filter( $recent_lines, function( $line ) {
			return stripos( $line, 'hubspot' ) !== false || stripos( $line, 'api' ) !== false || stripos( $line, 'weight' ) !== false;
		});
		
		echo "<pre>" . htmlspecialchars( implode( "\n", $hubspot_lines ) ) . "</pre>";
		
		echo "</body></html>";
		exit;
	}

	/**
	 * Delete Weight Loss fields from HubSpot
	 */
	public function delete_weight_loss_fields() {
		$contact_fields = $this->get_weight_loss_contact_fields();
		$custom_object_fields = $this->get_weight_loss_custom_object_fields();
		
		$deleted_contact_fields = array();
		$deleted_custom_object_fields = array();
		$failed_deletions = array();
		
		// Delete contact properties
		foreach ( $contact_fields as $field ) {
			$result = $this->delete_single_field( 'contacts', $field['name'] );
			if ( $result['success'] ) {
				$deleted_contact_fields[] = $field['name'];
			} else {
				$failed_deletions[] = array(
					'type' => 'contact',
					'name' => $field['name'],
					'error' => $result['error']
				);
			}
		}
		
		// Delete custom object properties
		foreach ( $custom_object_fields as $field ) {
			$custom_object_name = $this->get_custom_object_name();
			$result = $this->delete_single_field( $custom_object_name, $field['name'] );
			if ( $result['success'] ) {
				$deleted_custom_object_fields[] = $field['name'];
			} else {
				$failed_deletions[] = array(
					'type' => 'custom_object',
					'name' => $field['name'],
					'error' => $result['error']
				);
			}
		}
		
		return array(
			'success' => true,
			'deleted_contact_fields' => $deleted_contact_fields,
			'deleted_custom_object_fields' => $deleted_custom_object_fields,
			'failed_deletions' => $failed_deletions,
			'total_deleted' => count( $deleted_contact_fields ) + count( $deleted_custom_object_fields ),
			'total_failed' => count( $failed_deletions )
		);
	}
	
	/**
	 * Delete a single field from HubSpot
	 */
	private function delete_single_field( $object_type, $field_name ) {
		$this->init_api_params();
		
		$url = "https://api.hubapi.com/crm/v3/properties/{$object_type}/{$field_name}";
		
		$response = wp_remote_request( $url, array(
			'method' => 'DELETE',
			'headers' => $this->api_params['headers'],
			'timeout' => 30
		) );
		
		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'error' => $response->get_error_message()
			);
		}
		
		$status_code = wp_remote_retrieve_response_code( $response );
		
		if ( $status_code === 204 || $status_code === 200 ) {
			return array( 'success' => true );
		} else {
			$body = wp_remote_retrieve_body( $response );
			return array(
				'success' => false,
				'error' => "HTTP {$status_code}: {$body}"
			);
		}
	}

	/**
	 * AJAX handler for previewing global shared fields
	 *
	 * @since 64.6.99
	 */
	public function ajax_preview_global_fields() {
		// Verify nonce
		if ( ! check_ajax_referer( 'ennu_hubspot_nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		error_log( 'ENNU HubSpot: Previewing Global fields via AJAX...' );

		try {
			// Get global fields
			$global_fields = $this->get_global_shared_fields();
			
			// Get existing properties to check existence
			$existing_properties = $this->get_existing_properties( 'contacts' );
			
			// Add existence status to each field
			foreach ( $global_fields as &$field ) {
				$field['exists'] = false;
				$field['hubspot_id'] = null;
				$field['hubspot_name'] = null;
				
				foreach ( $existing_properties as $existing ) {
					if ( $existing['name'] === $field['name'] ) {
						$field['exists'] = true;
						$field['hubspot_id'] = $existing['id'];
						$field['hubspot_name'] = $existing['name'];
						break;
					}
				}
			}

			$preview_data = array(
				'global_fields' => $global_fields,
				'total_fields' => count( $global_fields ),
				'existing_count' => count( array_filter( $global_fields, function( $field ) { return $field['exists']; } ) ),
				'missing_count' => count( array_filter( $global_fields, function( $field ) { return ! $field['exists']; } ) ),
			);

			wp_send_json_success( $preview_data );

		} catch ( Exception $e ) {
			error_log( 'ENNU HubSpot: Error previewing global fields: ' . $e->getMessage() );
			wp_send_json_error( 'Failed to preview global fields: ' . $e->getMessage() );
		}
	}

	/**
	 * AJAX handler for creating global shared fields
	 *
	 * @since 64.6.99
	 */
	public function ajax_create_global_fields() {
		// Verify nonce
		if ( ! check_ajax_referer( 'ennu_hubspot_nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		// Custom object should already exist in HubSpot

		$result = $this->create_global_fields();
		
		if ( $result['success'] ) {
			wp_send_json_success( array( 
				'message' => $result['message'],
				'summary' => $result['summary'],
				'result' => $result['result']
			) );
		} else {
			wp_send_json_error( $result['message'] );
		}
	}

	/**
	 * Create global shared fields
	 *
	 * @since 64.6.99
	 * @return array Result array
	 */
	public function create_global_fields() {
		try {
			$global_fields = $this->get_global_shared_fields();
			$existing_properties = $this->get_existing_properties( 'contacts' );
			
			$new_fields = 0;
			$skipped_fields = array();
			$failed_fields = array();
			
			foreach ( $global_fields as $field ) {
				$exists = false;
				foreach ( $existing_properties as $existing ) {
					if ( $existing['name'] === $field['name'] ) {
						$exists = true;
						$skipped_fields[] = $field['name'];
						break;
					}
				}
				
				if ( ! $exists ) {
					$result = $this->create_single_field( 'contacts', $field, 'ENNU Global' );
					if ( $result['success'] ) {
						$new_fields++;
					} else {
						$failed_fields[] = $field['name'];
					}
				}
			}
			
			$message = sprintf( 'Successfully processed %d global fields. %d new fields created, %d existing fields skipped.', 
				count( $global_fields ), $new_fields, count( $skipped_fields ) );
			
			return array(
				'success' => true,
				'message' => $message,
				'summary' => array(
					'new_fields' => $new_fields,
					'skipped_fields' => count( $skipped_fields ),
					'total_processed' => count( $global_fields )
				),
				'result' => array(
					'skipped_fields' => $skipped_fields
				)
			);
			
		} catch ( Exception $e ) {
			return array(
				'success' => false,
				'message' => 'Failed to create global fields: ' . $e->getMessage()
			);
		}
	}

	/**
	 * Delete global shared fields
	 *
	 * @since 64.6.99
	 * @return array Result array
	 */
	public function delete_global_fields() {
		try {
			$global_fields = $this->get_global_shared_fields();
			$deleted_fields = array();
			$failed_fields = array();
			
			foreach ( $global_fields as $field ) {
				$result = $this->delete_single_field( 'contacts', $field['name'] );
				if ( $result['success'] ) {
					$deleted_fields[] = $field['name'];
				} else {
					$failed_fields[] = $field['name'];
				}
			}
			
			$message = sprintf( 'Deleted %d global fields. %d successful, %d failed.', 
				count( $global_fields ), count( $deleted_fields ), count( $failed_fields ) );
			
			return array(
				'success' => true,
				'message' => $message,
				'deleted_fields' => $deleted_fields,
				'failed_fields' => $failed_fields
			);
			
		} catch ( Exception $e ) {
			return array(
				'success' => false,
				'message' => 'Failed to delete global fields: ' . $e->getMessage()
			);
		}
	}

	/**
	 * AJAX handler for deleting global shared fields
	 *
	 * @since 64.6.99
	 */
	public function ajax_delete_global_fields() {
		// Verify nonce
		if ( ! check_ajax_referer( 'ennu_hubspot_nonce', 'nonce', false ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		$result = $this->delete_global_fields();
		
		if ( $result['success'] ) {
			wp_send_json_success( array( 'message' => $result['message'] ) );
		} else {
			wp_send_json_error( $result['message'] );
		}
	}

		/**
	 * AJAX handler for debugging HubSpot fields
	 *
	 * @since 64.6.99
	 */
	public function ajax_debug_hubspot_fields() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		error_log( 'ENNU HubSpot: Debugging HubSpot fields via AJAX...' );

		try {
			// Test contact properties
			$contact_properties = $this->get_existing_properties( 'contacts' );

			// Test custom object properties
			$custom_object_name = $this->get_custom_object_name();
			$custom_object_properties = $this->get_existing_properties( $custom_object_name );

			$debug_data = array(
				'contact_properties' => array(
					'count' => count( $contact_properties ),
					'fields' => array_keys( $contact_properties )
				),
				'custom_object_properties' => array(
					'count' => count( $custom_object_properties ),
					'fields' => array_keys( $custom_object_properties )
				),
				'api_status' => 'success'
			);

			wp_send_json_success( $debug_data );

		} catch ( Exception $e ) {
			wp_send_json_error( array(
				'message' => 'Error debugging HubSpot fields: ' . $e->getMessage()
			) );
		}
	}

	/**
	 * AJAX handler for testing field statistics
	 *
	 * @since 64.6.99
	 */
	public function ajax_test_field_statistics() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		error_log( 'ENNU HubSpot: Test field statistics called' );
		
		$assessments = $this->get_available_assessments();
		error_log( 'ENNU HubSpot: Test - Found ' . count( $assessments ) . ' assessments' );
		
		wp_send_json_success( array(
			'message' => 'Test successful',
			'assessments_count' => count( $assessments ),
			'assessments' => $assessments
		) );
	}

	/**
	 * AJAX handler for comprehensive preview of objects, field groups, and fields
	 *
	 * @since 64.10.0
	 */
	public function ajax_comprehensive_preview() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		error_log( 'ENNU HubSpot: Starting comprehensive preview...' );

		try {
			$preview_data = array(
				'custom_objects' => array(),
				'field_groups' => array(),
				'fields' => array(),
				'summary' => array()
			);

			// 1. Check Custom Objects
			$preview_data['custom_objects'] = $this->check_custom_objects();

			// 2. Check Field Groups
			$preview_data['field_groups'] = $this->check_field_groups();

			// 3. Check Fields
			$preview_data['fields'] = $this->check_fields();

			// 4. Generate Summary
			$preview_data['summary'] = $this->generate_preview_summary( $preview_data );

			wp_send_json_success( $preview_data );

		} catch ( Exception $e ) {
			error_log( 'ENNU HubSpot: Preview error: ' . $e->getMessage() );
			wp_send_json_error( 'Preview failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Check custom objects in HubSpot
	 *
	 * @since 64.10.0
	 * @return array
	 */
	private function check_custom_objects() {
		$custom_objects = array();
		
		try {
			// Get all custom objects from HubSpot
			$response = wp_remote_get(
				$this->api_base_url . '/crm-object-schemas/v3/schemas',
				array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $this->access_token,
						'Content-Type' => 'application/json'
					)
				)
			);

			if ( is_wp_error( $response ) ) {
				error_log( 'ENNU HubSpot: Failed to get custom objects: ' . $response->get_error_message() );
				return $custom_objects;
			}

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( isset( $data['results'] ) ) {
				foreach ( $data['results'] as $object ) {
					$custom_objects[] = array(
						'name' => $object['name'] ?? '',
						'object_type_id' => $object['objectTypeId'] ?? '',
						'label' => $object['labels']['singular'] ?? '',
						'exists' => true,
						'status' => '✅ Exists'
					);
				}
			}

			// Check if our expected custom object exists
			$expected_object = $this->get_custom_object_name();
			$found_expected = false;
			
			foreach ( $custom_objects as &$object ) {
				if ( $object['object_type_id'] === $expected_object ) {
					$object['is_expected'] = true;
					$found_expected = true;
					break;
				}
			}

			// If expected object not found, add it as missing
			if ( ! $found_expected ) {
				$custom_objects[] = array(
					'name' => 'Basic Assessments',
					'object_type_id' => $expected_object,
					'label' => 'Basic Assessments',
					'exists' => false,
					'is_expected' => true,
					'status' => '❌ Missing'
				);
			}

		} catch ( Exception $e ) {
			error_log( 'ENNU HubSpot: Error checking custom objects: ' . $e->getMessage() );
		}

		return $custom_objects;
	}

	/**
	 * Check field groups in HubSpot
	 *
	 * @since 64.10.0
	 * @return array
	 */
	private function check_field_groups() {
		$field_groups = array();
		
		try {
			// Check property groups for contacts
			$contact_groups = $this->get_property_groups( 'contacts' );
			foreach ( $contact_groups as $group ) {
				$field_groups[] = array(
					'name' => $group['name'] ?? '',
					'label' => $group['label'] ?? '',
					'object_type' => 'contacts',
					'exists' => true,
					'status' => '✅ Exists'
				);
			}

			// Check property groups for custom object
			$custom_object_name = $this->get_custom_object_name();
			$custom_object_groups = $this->get_property_groups( $custom_object_name );
			foreach ( $custom_object_groups as $group ) {
				$field_groups[] = array(
					'name' => $group['name'] ?? '',
					'label' => $group['label'] ?? '',
					'object_type' => $custom_object_name,
					'exists' => true,
					'status' => '✅ Exists'
				);
			}

			// Check for expected assessment property groups
			$assessments = $this->get_available_assessments();
			foreach ( $assessments as $assessment ) {
				$assessment_name = $assessment['id'];
				$assessment_prefix = $this->get_assessment_prefix( $assessment_name );
				$expected_group_name = strtolower( $assessment_prefix . ' ' . str_replace( array( '-', '_' ), ' ', $assessment_name ) . ' assessment' );
				
				$group_exists = false;
				foreach ( $field_groups as $group ) {
					if ( $group['name'] === $expected_group_name ) {
						$group_exists = true;
						break;
					}
				}

				if ( ! $group_exists ) {
					$field_groups[] = array(
						'name' => $expected_group_name,
						'label' => ucwords( $assessment_prefix . ' ' . str_replace( array( '-', '_' ), ' ', $assessment_name ) . ' Assessment' ),
						'object_type' => 'contacts',
						'exists' => false,
						'is_expected' => true,
						'status' => '❌ Missing'
					);
				}
			}

		} catch ( Exception $e ) {
			error_log( 'ENNU HubSpot: Error checking field groups: ' . $e->getMessage() );
		}

		return $field_groups;
	}

	/**
	 * Check fields in HubSpot
	 *
	 * @since 64.10.0
	 * @return array
	 */
	private function check_fields() {
		$fields = array();
		
		try {
			// Get existing properties
			$contact_properties = $this->get_existing_properties( 'contacts' );
			$custom_object_name = $this->get_custom_object_name();
			$custom_object_properties = $this->get_existing_properties( $custom_object_name );

			// Add existing contact fields
			foreach ( $contact_properties as $property ) {
				$fields[] = array(
					'name' => $property['name'] ?? '',
					'label' => $property['label'] ?? '',
					'type' => $property['type'] ?? '',
					'object_type' => 'contacts',
					'group' => $property['groupName'] ?? '',
					'exists' => true,
					'status' => '✅ Exists'
				);
			}

			// Add existing custom object fields
			foreach ( $custom_object_properties as $property ) {
				$fields[] = array(
					'name' => $property['name'] ?? '',
					'label' => $property['label'] ?? '',
					'type' => $property['type'] ?? '',
					'object_type' => $custom_object_name,
					'group' => $property['groupName'] ?? '',
					'exists' => true,
					'status' => '✅ Exists'
				);
			}

			// Check for expected fields
			$assessments = $this->get_available_assessments();
			foreach ( $assessments as $assessment ) {
				$assessment_name = $assessment['id'];
				$expected_fields = $this->extract_assessment_fields_by_name( $assessment_name );
				
				if ( ! is_wp_error( $expected_fields ) ) {
					// Check contact fields
					foreach ( $expected_fields['contact_fields'] as $field ) {
						$field_exists = false;
						foreach ( $fields as $existing_field ) {
							if ( $existing_field['name'] === $field['name'] && $existing_field['object_type'] === 'contacts' ) {
								$field_exists = true;
								break;
							}
						}

						if ( ! $field_exists ) {
							$fields[] = array(
								'name' => $field['name'],
								'label' => $field['label'],
								'type' => $field['type'],
								'object_type' => 'contacts',
								'group' => $field['groupName'] ?? '',
								'exists' => false,
								'is_expected' => true,
								'assessment' => $assessment_name,
								'status' => '❌ Missing'
							);
						}
					}

					// Check custom object fields
					foreach ( $expected_fields['custom_object_fields'] as $field ) {
						$field_exists = false;
						foreach ( $fields as $existing_field ) {
							if ( $existing_field['name'] === $field['name'] && $existing_field['object_type'] === $custom_object_name ) {
								$field_exists = true;
								break;
							}
						}

						if ( ! $field_exists ) {
							$fields[] = array(
								'name' => $field['name'],
								'label' => $field['label'],
								'type' => $field['type'],
								'object_type' => $custom_object_name,
								'group' => $field['groupName'] ?? '',
								'exists' => false,
								'is_expected' => true,
								'assessment' => $assessment_name,
								'status' => '❌ Missing'
							);
						}
					}
				}
			}

		} catch ( Exception $e ) {
			error_log( 'ENNU HubSpot: Error checking fields: ' . $e->getMessage() );
		}

		return $fields;
	}

	/**
	 * Generate preview summary
	 *
	 * @since 64.10.0
	 * @param array $preview_data
	 * @return array
	 */
	private function generate_preview_summary( $preview_data ) {
		$summary = array(
			'custom_objects' => array(
				'total' => count( $preview_data['custom_objects'] ),
				'existing' => 0,
				'missing' => 0,
				'expected' => 0
			),
			'field_groups' => array(
				'total' => count( $preview_data['field_groups'] ),
				'existing' => 0,
				'missing' => 0,
				'expected' => 0
			),
			'fields' => array(
				'total' => count( $preview_data['fields'] ),
				'existing' => 0,
				'missing' => 0,
				'expected' => 0
			)
		);

		// Count custom objects
		foreach ( $preview_data['custom_objects'] as $object ) {
			if ( $object['exists'] ) {
				$summary['custom_objects']['existing']++;
			} else {
				$summary['custom_objects']['missing']++;
			}
			if ( isset( $object['is_expected'] ) && $object['is_expected'] ) {
				$summary['custom_objects']['expected']++;
			}
		}

		// Count field groups
		foreach ( $preview_data['field_groups'] as $group ) {
			if ( $group['exists'] ) {
				$summary['field_groups']['existing']++;
			} else {
				$summary['field_groups']['missing']++;
			}
			if ( isset( $group['is_expected'] ) && $group['is_expected'] ) {
				$summary['field_groups']['expected']++;
			}
		}

		// Count fields
		foreach ( $preview_data['fields'] as $field ) {
			if ( $field['exists'] ) {
				$summary['fields']['existing']++;
			} else {
				$summary['fields']['missing']++;
			}
			if ( isset( $field['is_expected'] ) && $field['is_expected'] ) {
				$summary['fields']['expected']++;
			}
		}

		return $summary;
	}

	/**
	 * AJAX handler for creating field groups for custom objects
	 *
	 * @since 64.10.1
	 */
	public function ajax_create_custom_object_field_groups() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		error_log( 'ENNU HubSpot: Creating field groups for custom objects...' );

		try {
			$custom_object_name = $this->get_custom_object_name();
			$assessments = $this->get_available_assessments();
			$results = array(
				'success' => array(),
				'errors' => array(),
				'skipped' => array()
			);

			foreach ( $assessments as $assessment ) {
				$assessment_name = $assessment['id'];
				$assessment_prefix = $this->get_assessment_prefix( $assessment_name );
				$group_name = strtolower( $assessment_prefix . ' ' . str_replace( array( '-', '_' ), ' ', $assessment_name ) . ' assessment' );
				$group_label = ucwords( $assessment_prefix . ' ' . str_replace( array( '-', '_' ), ' ', $assessment_name ) . ' Assessment' );

				// Check if group already exists
				if ( $this->property_group_exists( $custom_object_name, $group_name ) ) {
					$results['skipped'][] = array(
						'group' => $group_name,
						'message' => 'Field group already exists'
					);
					continue;
				}

				// Create the property group
				$group_result = $this->create_property_group( $custom_object_name, $group_name );
				
				if ( $group_result['success'] ) {
					$results['success'][] = array(
						'group' => $group_name,
						'label' => $group_label,
						'message' => 'Field group created successfully'
					);
				} else {
					$results['errors'][] = array(
						'group' => $group_name,
						'error' => $group_result['message']
					);
				}

				// Rate limiting
				$this->rate_limit_delay();
			}

			wp_send_json_success( $results );

		} catch ( Exception $e ) {
			error_log( 'ENNU HubSpot: Error creating custom object field groups: ' . $e->getMessage() );
			wp_send_json_error( 'Failed to create field groups: ' . $e->getMessage() );
		}
	}

	/**
	 * AJAX handler for creating field groups for contacts
	 *
	 * @since 64.10.1
	 */
	public function ajax_create_contact_field_groups() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		error_log( 'ENNU HubSpot: Creating field groups for contacts...' );

		try {
			$assessments = $this->get_available_assessments();
			$results = array(
				'success' => array(),
				'errors' => array(),
				'skipped' => array()
			);

			foreach ( $assessments as $assessment ) {
				$assessment_name = $assessment['id'];
				$assessment_prefix = $this->get_assessment_prefix( $assessment_name );
				$group_name = strtolower( $assessment_prefix . ' ' . str_replace( array( '-', '_' ), ' ', $assessment_name ) . ' assessment' );
				$group_label = ucwords( $assessment_prefix . ' ' . str_replace( array( '-', '_' ), ' ', $assessment_name ) . ' Assessment' );

				// Check if group already exists
				if ( $this->property_group_exists( 'contacts', $group_name ) ) {
					$results['skipped'][] = array(
						'group' => $group_name,
						'message' => 'Field group already exists'
					);
					continue;
				}

				// Create the property group
				$group_result = $this->create_property_group( 'contacts', $group_name );
				
				if ( $group_result['success'] ) {
					$results['success'][] = array(
						'group' => $group_name,
						'label' => $group_label,
						'message' => 'Field group created successfully'
					);
				} else {
					$results['errors'][] = array(
						'group' => $group_name,
						'error' => $group_result['message']
					);
				}

				// Rate limiting
				$this->rate_limit_delay();
			}

			wp_send_json_success( $results );

		} catch ( Exception $e ) {
			error_log( 'ENNU HubSpot: Error creating contact field groups: ' . $e->getMessage() );
			wp_send_json_error( 'Failed to create field groups: ' . $e->getMessage() );
		}
	}

	/**
	 * AJAX handler for getting field statistics
	 *
	 * @since 64.6.99
	 */
	public function ajax_get_field_statistics() {
		check_ajax_referer( 'ennu_hubspot_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		error_log( 'ENNU HubSpot: Getting field statistics...' );
		error_log( 'ENNU HubSpot: AJAX call received for field statistics' );

		try {
			// Get existing properties from HubSpot
			$contact_properties = $this->get_existing_properties( 'contacts' );
			$custom_object_name = $this->get_custom_object_name();
			$custom_object_properties = $this->get_existing_properties( $custom_object_name );
			
			// Handle custom object API failures gracefully
			if ( empty( $custom_object_properties ) ) {
				error_log( 'ENNU HubSpot: Custom object properties API failed, using empty array' );
				$custom_object_properties = array();
			}

					// Get all available assessments
		$assessments = $this->get_available_assessments();
		error_log( 'ENNU HubSpot: Found ' . count( $assessments ) . ' assessments' );
			
					// Get global fields for statistics
		$global_fields = $this->get_global_shared_fields();
		$global_contact_fields = $global_fields;
		$global_custom_object_fields = $global_fields;
		
		$stats = array(
			'total_contact_fields' => count( $contact_properties ),
			'total_custom_object_fields' => count( $custom_object_properties ),
			'total_fields' => count( $contact_properties ) + count( $custom_object_properties ),
			'global_fields' => array(
				'contact_fields' => $global_contact_fields,
				'custom_object_fields' => $global_custom_object_fields,
				'expected_contact_fields' => count( $global_contact_fields ),
				'expected_custom_object_fields' => count( $global_custom_object_fields ),
				'existing_contact_fields' => 0,
				'existing_custom_object_fields' => 0,
				'missing_contact_fields' => count( $global_contact_fields ),
				'missing_custom_object_fields' => count( $global_custom_object_fields ),
			),
			'assessments' => array(),
			'summary' => array(
				'total_expected_fields' => 0,
				'total_existing_fields' => 0,
				'total_missing_fields' => 0,
				'completion_percentage' => 0
			)
		);

			// Count global fields that exist
			$global_existing_contact = 0;
			$global_existing_custom_object = 0;
			foreach ( $global_fields as $field ) {
				if ( isset( $contact_properties[ $field['name'] ] ) ) {
					$global_existing_contact++;
				}
				if ( isset( $custom_object_properties[ $field['name'] ] ) ) {
					$global_existing_custom_object++;
				}
			}
			
			// Update global field statistics
			$stats['global_fields']['existing_contact_fields'] = $global_existing_contact;
			$stats['global_fields']['existing_custom_object_fields'] = $global_existing_custom_object;
			$stats['global_fields']['missing_contact_fields'] = count( $global_fields ) - $global_existing_contact;
			$stats['global_fields']['missing_custom_object_fields'] = count( $global_fields ) - $global_existing_custom_object;
			
			// Process each assessment
			foreach ( $assessments as $assessment ) {
				$assessment_name = $assessment['id'];
				$assessment_title = $assessment['title'];
				error_log( 'ENNU HubSpot: Processing assessment: ' . $assessment_name );
				
				// Get expected fields for this assessment
				$expected_fields = $this->extract_assessment_fields_by_name( $assessment_name );
				
				if ( is_wp_error( $expected_fields ) ) {
					error_log( 'ENNU HubSpot: Error extracting fields for ' . $assessment_name . ': ' . $expected_fields->get_error_message() );
					continue;
				}

				$contact_fields = $expected_fields['contact_fields'];
				$custom_object_fields = $expected_fields['custom_object_fields'];
				
				error_log( 'ENNU HubSpot: Extracted ' . count( $contact_fields ) . ' contact fields and ' . count( $custom_object_fields ) . ' custom object fields for ' . $assessment_name );
				
				// Count existing fields
				$existing_contact_fields = 0;
				$existing_custom_object_fields = 0;
				
				foreach ( $contact_fields as $field ) {
					if ( isset( $contact_properties[ $field['name'] ] ) ) {
						$existing_contact_fields++;
					}
				}
				
				foreach ( $custom_object_fields as $field ) {
					if ( isset( $custom_object_properties[ $field['name'] ] ) ) {
						$existing_custom_object_fields++;
					}
				}

				$total_expected = count( $contact_fields ) + count( $custom_object_fields );
				$total_existing = $existing_contact_fields + $existing_custom_object_fields;
				$total_missing = $total_expected - $total_existing;
				$completion_percentage = $total_expected > 0 ? round( ( $total_existing / $total_expected ) * 100 ) : 0;

				$stats['assessments'][ $assessment_name ] = array(
					'title' => $assessment_title,
					'expected_contact_fields' => count( $contact_fields ),
					'expected_custom_object_fields' => count( $custom_object_fields ),
					'expected_total' => $total_expected,
					'existing_contact_fields' => $existing_contact_fields,
					'existing_custom_object_fields' => $existing_custom_object_fields,
					'existing_total' => $total_existing,
					'missing_contact_fields' => count( $contact_fields ) - $existing_contact_fields,
					'missing_custom_object_fields' => count( $custom_object_fields ) - $existing_custom_object_fields,
					'missing_total' => $total_missing,
					'completion_percentage' => $completion_percentage
				);

				// Add to summary totals
				$stats['summary']['total_expected_fields'] += $total_expected;
				$stats['summary']['total_existing_fields'] += $total_existing;
				$stats['summary']['total_missing_fields'] += $total_missing;
			}
			
			// Add global fields to summary totals (count once per object type, not twice)
			$global_expected = count( $global_fields ) * 2; // Both contact and custom object
			$global_existing = $global_existing_contact + $global_existing_custom_object;
			$global_missing = $global_expected - $global_existing;
			
			$stats['summary']['total_expected_fields'] += $global_expected;
			$stats['summary']['total_existing_fields'] += $global_existing;
			$stats['summary']['total_missing_fields'] += $global_missing;

			// Calculate overall completion percentage
			if ( $stats['summary']['total_expected_fields'] > 0 ) {
				$stats['summary']['completion_percentage'] = round( 
					( $stats['summary']['total_existing_fields'] / $stats['summary']['total_expected_fields'] ) * 100 
				);
			}

			// Generate HTML for the statistics dashboard
			$html = $this->generate_field_statistics_html( $stats );
			wp_send_json_success( $html );

		} catch ( Exception $e ) {
			error_log( 'ENNU HubSpot: Error in field statistics: ' . $e->getMessage() );
			error_log( 'ENNU HubSpot: Stack trace: ' . $e->getTraceAsString() );
			
			wp_send_json_error( array(
				'message' => 'Error getting field statistics: ' . $e->getMessage(),
				'debug_info' => array(
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'trace' => $e->getTraceAsString()
				)
			) );
		}
	}

	/**
	 * Generate HTML for field statistics dashboard
	 *
	 * @param array $stats Statistics data
	 * @return string HTML content
	 */
	private function generate_field_statistics_html( $stats ) {
		$html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">';
		
		// Overall Summary Card
		$html .= '<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 20px; text-align: center;">';
		$html .= '<h4 style="margin: 0 0 15px 0; color: white;">📊 Overall Summary</h4>';
		$html .= '<div style="font-size: 36px; font-weight: bold; margin-bottom: 10px;">' . $stats['summary']['completion_percentage'] . '%</div>';
		$html .= '<div style="font-size: 14px; opacity: 0.8;">Completion Rate</div>';
		$html .= '<div style="margin-top: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">';
		$html .= '<div><strong>' . $stats['summary']['total_existing_fields'] . '</strong><br><small>Existing</small></div>';
		$html .= '<div><strong>' . $stats['summary']['total_missing_fields'] . '</strong><br><small>Missing</small></div>';
		$html .= '</div>';
		$html .= '</div>';
		
		// HubSpot Objects Summary
		$html .= '<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 20px;">';
		$html .= '<h4 style="margin: 0 0 15px 0; color: white;">🏢 HubSpot Objects</h4>';
		$html .= '<div style="margin-bottom: 10px;"><strong>Contacts:</strong> ' . $stats['total_contact_fields'] . ' fields</div>';
		$html .= '<div><strong>Custom Objects:</strong> ' . $stats['total_custom_object_fields'] . ' fields</div>';
		$html .= '</div>';
		
		// Global Fields Summary
		$html .= '<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 20px;">';
		$html .= '<h4 style="margin: 0 0 15px 0; color: white;">🌐 Global Fields</h4>';
		$html .= '<div style="margin-bottom: 10px;"><strong>Contacts:</strong> ' . $stats['global_fields']['existing_contact_fields'] . '/' . $stats['global_fields']['expected_contact_fields'] . ' created</div>';
		$html .= '<div><strong>Custom Objects:</strong> ' . $stats['global_fields']['existing_custom_object_fields'] . '/' . $stats['global_fields']['expected_custom_object_fields'] . ' created</div>';
		$html .= '</div>';
		
		// Assessment Fields Summary
		$html .= '<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 20px;">';
		$html .= '<h4 style="margin: 0 0 15px 0; color: white;">📋 Assessment Fields</h4>';
		$html .= '<div style="font-size: 24px; font-weight: bold; margin-bottom: 10px;">' . count( $stats['assessments'] ) . '</div>';
		$html .= '<div style="font-size: 14px; opacity: 0.8;">Assessments</div>';
		$html .= '</div>';
		
		$html .= '</div>';
		
		// Assessment Details
		if ( ! empty( $stats['assessments'] ) ) {
			$html .= '<div style="margin-top: 20px;">';
			$html .= '<h4 style="color: white; margin-bottom: 15px;">📋 Assessment Details</h4>';
			$html .= '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 15px;">';
			
			foreach ( $stats['assessments'] as $assessment_name => $assessment_data ) {
				$html .= '<div style="background: rgba(255,255,255,0.1); border-radius: 8px; padding: 15px;">';
				$html .= '<h5 style="margin: 0 0 10px 0; color: white;">' . esc_html( $assessment_data['title'] ) . '</h5>';
				$html .= '<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 10px;">';
				$html .= '<div><strong>Contacts:</strong> ' . $assessment_data['existing_contact_fields'] . '/' . $assessment_data['expected_contact_fields'] . '</div>';
				$html .= '<div><strong>Custom Objects:</strong> ' . $assessment_data['existing_custom_object_fields'] . '/' . $assessment_data['expected_custom_object_fields'] . '</div>';
				$html .= '</div>';
				$html .= '<div style="text-align: center; font-size: 18px; font-weight: bold;">' . $assessment_data['completion_percentage'] . '%</div>';
				$html .= '</div>';
			}
			
			$html .= '</div>';
			$html .= '</div>';
		}
		
		return $html;
	}

	/**
	 * AJAX handler for deleting Weight Loss fields
	 */
	public function ajax_delete_weight_loss_fields() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_hubspot_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}
		
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}
		
		$result = $this->delete_weight_loss_fields();
		
		if ( $result['success'] ) {
			$message = "🗑️ Deleted {$result['total_deleted']} Weight Loss fields from HubSpot!";
			
			if ( $result['total_failed'] > 0 ) {
				$message .= " {$result['total_failed']} fields failed to delete.";
			}
			
			if ( ! empty( $result['failed_deletions'] ) ) {
				$message .= " Failed deletions: " . implode( ', ', array_map( function( $failure ) {
					return $failure['name'] . ' (' . $failure['error'] . ')';
				}, $result['failed_deletions'] ) );
			}
			
			wp_send_json_success( array(
				'message' => $message,
				'deleted_contact_fields' => $result['deleted_contact_fields'],
				'deleted_custom_object_fields' => $result['deleted_custom_object_fields'],
				'failed_deletions' => $result['failed_deletions']
			) );
		} else {
			wp_send_json_error( 'Failed to delete fields' );
		}
	}



} 