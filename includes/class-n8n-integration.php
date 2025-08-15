<?php
/**
 * ENNU Direct Microsoft Teams Integration
 * 
 * Sends notifications directly to Microsoft Teams channels using webhook URLs
 * Each notification type goes to its designated Teams channel
 *
 * @package ENNU_Life_Assessments
 * @since 70.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_N8N_Integration {
	
	/**
	 * Singleton instance
	 */
	private static $instance = null;
	
	/**
	 * Direct Microsoft Teams webhook URLs for each channel
	 * IMPORTANT: Replace these with your actual Teams webhook URLs
	 * To get a webhook URL: Teams Channel > Connectors > Incoming Webhook > Configure
	 */
	private $webhooks = array(
		// Patient Assessments channel
		'assessment' => 'https://bodyshapesmd.webhook.office.com/webhookb2/3807dfcf-e155-41e6-8c5a-161427dfc11d@4e6b1b2f-9500-4157-b1d8-12802c9623b6/IncomingWebhook/b6a08193f7324f5abddca9090d1ef70e/245bd929-0bfe-4af1-8eb0-0863a92b2a32/V2ZgtSTEwaKLqbTZnJV2z1WbNJ4BV0bGm9eyjJsby4trc1',
		
		// Biomarker Updates channel
		'biomarker' => 'https://bodyshapesmd.webhook.office.com/webhookb2/3807dfcf-e155-41e6-8c5a-161427dfc11d@4e6b1b2f-9500-4157-b1d8-12802c9623b6/IncomingWebhook/2f66827b164f4436aaba559ddeda6833/245bd929-0bfe-4af1-8eb0-0863a92b2a32/V2eWKki1WpzCngeS9zq9CfkjcNBLOYwZeh3HNGjP3jMbQ1',
		
		// Critical Alerts channel (using biomarker for now - update when you have critical webhook)
		'critical' => 'https://bodyshapesmd.webhook.office.com/webhookb2/3807dfcf-e155-41e6-8c5a-161427dfc11d@4e6b1b2f-9500-4157-b1d8-12802c9623b6/IncomingWebhook/2f66827b164f4436aaba559ddeda6833/245bd929-0bfe-4af1-8eb0-0863a92b2a32/V2eWKki1WpzCngeS9zq9CfkjcNBLOYwZeh3HNGjP3jMbQ1',
		
		// New Registrations channel
		'registration' => 'https://bodyshapesmd.webhook.office.com/webhookb2/3807dfcf-e155-41e6-8c5a-161427dfc11d@4e6b1b2f-9500-4157-b1d8-12802c9623b6/IncomingWebhook/8644a08f2e2e4c93afcfeb36f424daf9/245bd929-0bfe-4af1-8eb0-0863a92b2a32/V2a7RUlyzcdnDEejqvRnALD2EluWyHshHtmSDt84-5w841',
		
		// Appointments channel (placeholder - update when you have webhook)
		'appointment' => 'https://bodyshapesmd.webhook.office.com/webhookb2/YOUR-APPOINTMENT-WEBHOOK-URL',
		
		// System Alerts channel (placeholder - update when you have webhook)
		'system_alert' => 'https://bodyshapesmd.webhook.office.com/webhookb2/YOUR-SYSTEM-WEBHOOK-URL',
		
		// Revenue Metrics channel (placeholder - update when you have webhook)
		'revenue' => 'https://bodyshapesmd.webhook.office.com/webhookb2/YOUR-REVENUE-WEBHOOK-URL',
		
		// Patient Success channel (placeholder - update when you have webhook)
		'success' => 'https://bodyshapesmd.webhook.office.com/webhookb2/YOUR-SUCCESS-WEBHOOK-URL',
		
		// Daily Summaries channel
		'daily_summary' => 'https://bodyshapesmd.webhook.office.com/webhookb2/3807dfcf-e155-41e6-8c5a-161427dfc11d@4e6b1b2f-9500-4157-b1d8-12802c9623b6/IncomingWebhook/252950c03dbd4679ad8bc66a7964d23d/245bd929-0bfe-4af1-8eb0-0863a92b2a32/V24NOsdoyOKRAz7ISwfY2l05jTx_TW5vGcNEv1I4hXYG81'
	);
	
	/**
	 * Get singleton instance
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
		$this->init_hooks();
		$this->load_webhook_urls();
	}
	
	/**
	 * Load webhook URLs from database (allows admin configuration)
	 */
	private function load_webhook_urls() {
		$saved_urls = get_option( 'ennu_teams_webhooks' );
		if ( $saved_urls && is_array( $saved_urls ) ) {
			$this->webhooks = array_merge( $this->webhooks, $saved_urls );
		}
	}
	
	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		// Assessment notifications
		add_action( 'ennu_assessment_completed', array( $this, 'send_assessment_notification' ), 10, 2 );
		
		// Biomarker notifications
		add_action( 'ennu_biomarker_updated', array( $this, 'send_biomarker_notification' ), 10, 2 );
		add_action( 'ennu_biomarker_snapshot_created', array( $this, 'send_biomarker_notification_snapshot' ), 10, 4 );
		
		// Critical alerts
		add_action( 'ennu_critical_value_detected', array( $this, 'send_critical_alert' ), 10, 3 );
		
		// User registration
		add_action( 'user_register', array( $this, 'send_registration_notification' ), 10, 1 );
		
		// Appointments (if using Amelia)
		add_action( 'amelia_booking_added', array( $this, 'send_appointment_notification' ), 10, 1 );
		
		// System alerts
		add_action( 'ennu_system_error', array( $this, 'send_system_alert' ), 10, 2 );
		
		// Admin AJAX for testing
		add_action( 'wp_ajax_ennu_test_teams_webhook', array( $this, 'handle_test_webhook' ) );
		add_action( 'wp_ajax_ennu_create_test_user', array( $this, 'handle_create_test_user' ) );
		add_action( 'wp_ajax_ennu_save_teams_urls', array( $this, 'handle_save_urls' ) );
		
		// Add admin menu for testing
		add_action( 'admin_menu', array( $this, 'add_test_menu' ) );
	}
	
	/**
	 * Send assessment completion notification
	 */
	public function send_assessment_notification( $user_id, $assessment_type ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) return;
		
		// Get assessment data
		$scores = get_user_meta( $user_id, 'ennu_assessment_scores_' . $assessment_type, true );
		$responses = get_user_meta( $user_id, 'ennu_assessment_responses_' . $assessment_type, true );
		$recommendations = get_user_meta( $user_id, 'ennu_assessment_recommendations_' . $assessment_type, true );
		
		// Special handling for peptide therapy assessment
		if ( $assessment_type === 'peptide-therapy' ) {
			$peptide_recommendations = get_user_meta( $user_id, 'ennu_peptide-therapy_recommendations', true );
			if ( $peptide_recommendations ) {
				$recommendations = $peptide_recommendations;
			}
		}
		
		// Get user full name
		$first_name = get_user_meta( $user_id, 'first_name', true );
		$last_name = get_user_meta( $user_id, 'last_name', true );
		$full_name = trim( $first_name . ' ' . $last_name ) ?: $user->display_name;
		
		// Prepare data for n8n
		$data = array(
			'patient_name' => $full_name,
			'patient_email' => $user->user_email,
			'patient_id' => $user_id,
			'assessment_type' => ucwords( str_replace( '_', ' ', $assessment_type ) ),
			'scores' => $scores ?: array(),
			'recommendations' => is_array( $recommendations ) ? array_slice( $recommendations, 0, 3 ) : array(),
			'dashboard_url' => admin_url( 'admin.php?page=ennu-assessments&user_id=' . $user_id ),
			'timestamp' => current_time( 'c' ),
			'raw_assessment_type' => $assessment_type
		);
		
		// Send to n8n
		$this->send_to_n8n( 'assessment', $data );
		
		// Log for debugging
		// REMOVED: error_log( 'ENNU n8n: Assessment notification sent for user ' . $user_id );
	}
	
	/**
	 * Send biomarker update notification
	 */
	public function send_biomarker_notification( $user_id, $biomarker_data ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) return;
		
		// Get user name
		$full_name = $this->get_user_full_name( $user_id );
		
		// Get update count
		$update_count = get_user_meta( $user_id, 'ennu_biomarker_update_count', true );
		$update_count = $update_count ? intval( $update_count ) + 1 : 1;
		update_user_meta( $user_id, 'ennu_biomarker_update_count', $update_count );
		
		// Get previous biomarkers for comparison
		$previous = get_user_meta( $user_id, 'ennu_previous_biomarkers', true ) ?: array();
		
		// Calculate changes
		$changes = array();
		$has_critical = false;
		
		foreach ( $biomarker_data as $key => $value ) {
			$formatted_name = $this->format_biomarker_name( $key );
			$unit = $this->get_biomarker_unit( $key );
			
			// Check if critical
			if ( $this->is_critical_value( $key, $value ) ) {
				$has_critical = true;
				$icon = '❌';
			} elseif ( $this->is_optimal_value( $key, $value ) ) {
				$icon = '✅';
			} else {
				$icon = '⚠️';
			}
			
			// Format change
			if ( isset( $previous[ $key ] ) && $previous[ $key ] != $value ) {
				$changes[] = sprintf( 
					'%s %s: %s → %s %s',
					$icon,
					$formatted_name,
					$previous[ $key ],
					$value,
					$unit
				);
			} else {
				$changes[] = sprintf(
					'%s %s: %s %s',
					$icon,
					$formatted_name,
					$value,
					$unit
				);
			}
		}
		
		// Save current as previous
		update_user_meta( $user_id, 'ennu_previous_biomarkers', $biomarker_data );
		
		// Get source
		$source = get_user_meta( $user_id, 'ennu_last_biomarker_source', true ) ?: 'Manual Entry';
		
		$data = array(
			'patient_name' => $full_name,
			'patient_email' => $user->user_email,
			'patient_id' => $user_id,
			'update_number' => $update_count,
			'biomarkers_count' => count( $biomarker_data ),
			'changes' => array_slice( $changes, 0, 10 ), // Limit to 10 changes
			'has_critical' => $has_critical,
			'source' => $source,
			'status' => $has_critical ? 'Needs Attention ⚠️' : 'Good Progress ✅',
			'next_test_date' => date( 'F j, Y', strtotime( '+3 months' ) ),
			'report_url' => admin_url( 'admin.php?page=ennu-biomarkers&user_id=' . $user_id ),
			'timestamp' => current_time( 'c' )
		);
		
		// Send to n8n
		$this->send_to_n8n( 'biomarker', $data );
		
		// If critical, also send critical alert
		if ( $has_critical ) {
			$this->send_critical_alert_for_biomarker( $user_id, $biomarker_data );
		}
	}
	
	/**
	 * Send critical alert
	 */
	public function send_critical_alert( $user_id, $biomarker_key, $value ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) return;
		
		$full_name = $this->get_user_full_name( $user_id );
		$phone = get_user_meta( $user_id, 'billing_phone', true ) ?: 'Not provided';
		
		// Get last appointment
		$last_appointment = $this->get_last_appointment( $user_id );
		$last_contact = get_user_meta( $user_id, 'last_contact_date', true ) ?: 'Never';
		
		// Format the finding
		$biomarker_name = $this->format_biomarker_name( $biomarker_key );
		$unit = $this->get_biomarker_unit( $biomarker_key );
		$normal_range = $this->get_normal_range( $biomarker_key );
		
		$finding = sprintf(
			'%s: %s %s (Normal: %s)',
			$biomarker_name,
			$value,
			$unit,
			$normal_range
		);
		
		// Determine risk level
		$risk_level = $this->calculate_risk_level( $biomarker_key, $value );
		
		// Get required actions
		$actions = $this->get_required_actions( $biomarker_key, $value, $risk_level );
		
		$data = array(
			'patient_name' => $full_name,
			'patient_email' => $user->user_email,
			'patient_phone' => $phone,
			'patient_id' => $user_id,
			'finding' => $finding,
			'biomarker' => $biomarker_key,
			'value' => $value,
			'risk_level' => strtoupper( $risk_level ),
			'actions' => $actions,
			'last_appointment' => $last_appointment,
			'last_contact' => $last_contact,
			'timestamp' => current_time( 'c' ),
			'profile_url' => admin_url( 'admin.php?page=ennu-patients&user_id=' . $user_id )
		);
		
		// Send to n8n critical workflow
		$this->send_to_n8n( 'critical', $data );
		
		// Log critical alert
		$this->log_critical_alert( $user_id, $biomarker_key, $value );
	}
	
	/**
	 * Send registration notification
	 */
	public function send_registration_notification( $user_id ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) return;
		
		$full_name = $this->get_user_full_name( $user_id );
		
		// Get registration source
		$source = isset( $_SERVER['HTTP_REFERER'] ) ? parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_HOST ) : 'Direct';
		
		// Get location from IP
		$ip = $_SERVER['REMOTE_ADDR'];
		$location = $this->get_location_from_ip( $ip );
		
		$data = array(
			'patient_name' => $full_name,
			'patient_email' => $user->user_email,
			'patient_id' => $user_id,
			'registration_time' => $user->user_registered,
			'source' => $source,
			'location' => $location,
			'ip_address' => $ip,
			'profile_url' => admin_url( 'user-edit.php?user_id=' . $user_id ),
			'timestamp' => current_time( 'c' )
		);
		
		// Send to n8n
		$this->send_to_n8n( 'registration', $data );
		
		// Also send directly to Teams New Registrations channel
		$this->send_direct_to_teams_registration( $user_id, $full_name, $user->user_email );
	}
	
	/**
	 * Send appointment notification
	 */
	public function send_appointment_notification( $booking_id ) {
		// For Amelia bookings
		global $wpdb;
		
		$booking = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}amelia_customer_bookings WHERE id = %d",
			$booking_id
		) );
		
		if ( ! $booking ) return;
		
		// Get customer info
		$customer = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}amelia_users WHERE id = %d",
			$booking->customerId
		) );
		
		// Get service info
		$service = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}amelia_services WHERE id = %d",
			$booking->serviceId
		) );
		
		$data = array(
			'patient_name' => $customer->firstName . ' ' . $customer->lastName,
			'patient_email' => $customer->email,
			'patient_phone' => $customer->phone,
			'service' => $service->name,
			'appointment_date' => date( 'F j, Y', strtotime( $booking->bookingStart ) ),
			'appointment_time' => date( 'g:i A', strtotime( $booking->bookingStart ) ),
			'duration' => $service->duration . ' minutes',
			'provider' => 'Dr. Elena Harmonix', // Or get actual provider
			'booking_id' => $booking_id,
			'timestamp' => current_time( 'c' )
		);
		
		$this->send_to_n8n( 'appointment', $data );
	}
	
	/**
	 * Send system alert
	 */
	public function send_system_alert( $error_type, $error_details ) {
		$data = array(
			'error_type' => $error_type,
			'error_message' => $error_details['message'] ?? 'Unknown error',
			'error_code' => $error_details['code'] ?? '',
			'component' => $error_details['component'] ?? 'System',
			'severity' => $error_details['severity'] ?? 'medium',
			'stack_trace' => $error_details['stack_trace'] ?? '',
			'timestamp' => current_time( 'c' ),
			'admin_url' => admin_url( 'admin.php?page=ennu-logs' )
		);
		
		$this->send_to_n8n( 'system_alert', $data );
	}
	
	/**
	 * Core function to send data directly to Teams
	 */
	private function send_to_teams( $webhook_key, $data ) {
		// Check if webhook URL exists
		if ( empty( $this->webhooks[ $webhook_key ] ) ) {
			return false;
		}
		
		$url = $this->webhooks[ $webhook_key ];
		
		// Skip if using placeholder URL
		if ( strpos( $url, 'YOUR-' ) !== false ) {
			return false;
		}
		
		// Create Teams message card based on notification type
		$message_card = $this->create_teams_message_card( $webhook_key, $data );
		
		// Send the request
		$response = wp_remote_post( $url, array(
			'body' => json_encode( $message_card ),
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'timeout' => 30,
			'sslverify' => true
		) );
		
		if ( is_wp_error( $response ) ) {
			return false;
		}
		
		$response_code = wp_remote_retrieve_response_code( $response );
		
		return ( $response_code === 200 || $response_code === 202 );
	}
	
	/**
	 * Alias for backward compatibility
	 */
	private function send_to_n8n( $webhook_key, $data ) {
		return $this->send_to_teams( $webhook_key, $data );
	}
	
	/**
	 * Create Teams message card based on notification type
	 */
	private function create_teams_message_card( $type, $data ) {
		$theme_colors = array(
			'assessment' => '00B294',   // Teal for assessments
			'biomarker' => '0078D4',    // Blue for biomarkers
			'critical' => 'FF0000',     // Red for critical alerts
			'registration' => '00B294',  // Teal for registrations
			'appointment' => 'FFC000',   // Gold for appointments
			'system_alert' => 'FF8C00',  // Orange for system alerts
			'revenue' => '107C10',       // Green for revenue
			'success' => '00B294'        // Teal for success stories
		);
		
		$card = array(
			'@type' => 'MessageCard',
			'@context' => 'https://schema.org/extensions',
			'themeColor' => $theme_colors[ $type ] ?? '00B294',
			'summary' => $this->get_notification_title( $type, $data ),
		);
		
		// Build card based on type
		switch ( $type ) {
			case 'assessment':
				$card['sections'] = $this->build_assessment_card( $data );
				break;
			case 'biomarker':
				$card['sections'] = $this->build_biomarker_card( $data );
				break;
			case 'critical':
				$card['sections'] = $this->build_critical_card( $data );
				break;
			case 'registration':
				$card['sections'] = $this->build_registration_card( $data );
				break;
			case 'appointment':
				$card['sections'] = $this->build_appointment_card( $data );
				break;
			case 'system_alert':
				$card['sections'] = $this->build_system_alert_card( $data );
				break;
			case 'revenue':
				$card['sections'] = $this->build_revenue_card( $data );
				break;
			case 'success':
				$card['sections'] = $this->build_success_card( $data );
				break;
			default:
				$card['sections'] = $this->build_generic_card( $data );
		}
		
		// Add action buttons if URLs are provided
		if ( ! empty( $data['profile_url'] ) || ! empty( $data['dashboard_url'] ) ) {
			$card['potentialAction'] = array();
			
			if ( ! empty( $data['profile_url'] ) ) {
				$card['potentialAction'][] = array(
					'@type' => 'OpenUri',
					'name' => 'View Profile',
					'targets' => array(
						array( 'os' => 'default', 'uri' => $data['profile_url'] )
					)
				);
			}
			
			if ( ! empty( $data['dashboard_url'] ) ) {
				$card['potentialAction'][] = array(
					'@type' => 'OpenUri',
					'name' => 'View Dashboard',
					'targets' => array(
						array( 'os' => 'default', 'uri' => $data['dashboard_url'] )
					)
				);
			}
		}
		
		return $card;
	}
	
	/**
	 * Add test menu to WordPress admin
	 */
	public function add_test_menu() {
		add_submenu_page(
			'ennu-life',
			'Teams Notifications Test',
			'Test Teams',
			'manage_options',
			'ennu-test-teams',
			array( $this, 'render_test_page' )
		);
	}
	
	/**
	 * Render test page
	 */
	public function render_test_page() {
		?>
		<div class="wrap">
			<h1>Test Teams Notifications</h1>
			
			<div class="notice notice-info">
				<p><strong>Current Configuration:</strong></p>
				<ul>
					<li>✅ <strong>Registration webhook:</strong> Configured (New Registrations channel)</li>
					<li>✅ <strong>Assessment webhook:</strong> Configured (Patient Assessments channel)</li>
					<li>✅ <strong>Biomarker webhook:</strong> Configured (Biomarker Updates channel)</li>
					<li>✅ <strong>Critical webhook:</strong> Configured (using Biomarker channel)</li>
					<li>✅ <strong>Daily Summary:</strong> Configured (Daily Summaries channel)</li>
					<li>❌ Appointment webhook: Not configured</li>
					<li>❌ System Alert webhook: Not configured</li>
					<li>❌ Revenue webhook: Not configured</li>
					<li>❌ Success webhook: Not configured</li>
				</ul>
			</div>
			
			<div class="card" style="max-width: 800px; margin-top: 20px;">
				<h2>Test Teams Notifications</h2>
				<p>Click any button below to send a test notification to the corresponding Teams channel.</p>
				
				<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 20px;">
					<button class="test-webhook button button-primary" data-webhook="registration">
						👤 Test Registration
					</button>
					<button class="test-webhook button button-primary" data-webhook="assessment">
						📋 Test Assessment
					</button>
					<button class="test-webhook button button-primary" data-webhook="biomarker">
						🔬 Test Biomarker
					</button>
					<button class="test-webhook button button-primary" data-webhook="critical">
						🚨 Test Critical Alert
					</button>
				</div>
				<div id="test-result" style="margin-top: 20px;"></div>
			</div>
			
			<div class="card" style="max-width: 600px; margin-top: 20px;">
				<h2>Create Test User</h2>
				<p>This will create a real test user and trigger the actual registration notification.</p>
				
				<form id="create-test-user">
					<table class="form-table">
						<tr>
							<th>Email</th>
							<td><input type="email" id="test-email" value="test-<?php echo time(); ?>@example.com" class="regular-text" /></td>
						</tr>
						<tr>
							<th>First Name</th>
							<td><input type="text" id="test-firstname" value="Test" class="regular-text" /></td>
						</tr>
						<tr>
							<th>Last Name</th>
							<td><input type="text" id="test-lastname" value="User <?php echo date('His'); ?>" class="regular-text" /></td>
						</tr>
					</table>
					<button type="submit" class="button button-primary">Create Test User & Send Notification</button>
				</form>
				<div id="user-result" style="margin-top: 20px;"></div>
			</div>
			
			<script>
			jQuery(document).ready(function($) {
				// Test webhook notifications
				$('.test-webhook').on('click', function() {
					var $button = $(this);
					var webhook = $button.data('webhook');
					var originalText = $button.text();
					var $result = $('#test-result');
					
					$button.prop('disabled', true).text('Sending...');
					$result.html('<div class="notice notice-info"><p>Sending ' + webhook + ' test notification...</p></div>');
					
					$.post(ajaxurl, {
						action: 'ennu_test_teams_webhook',
						webhook: webhook,
						nonce: '<?php echo wp_create_nonce( 'ennu_teams_nonce' ); ?>'
					}, function(response) {
						if (response.success) {
							$result.html('<div class="notice notice-success"><p>✅ ' + response.message + '</p><p>Check your Teams "' + getChannelName(webhook) + '" channel.</p></div>');
						} else {
							$result.html('<div class="notice notice-error"><p>❌ ' + response.message + '</p></div>');
						}
						$button.prop('disabled', false).text(originalText);
					});
				});
				
				function getChannelName(webhook) {
					var channels = {
						'registration': 'New Registrations',
						'assessment': 'Patient Assessments',
						'biomarker': 'Biomarker Updates',
						'critical': 'Biomarker Updates (Critical Alert)'
					};
					return channels[webhook] || webhook;
				}
				
				// Create test user
				$('#create-test-user').on('submit', function(e) {
					e.preventDefault();
					
					var $form = $(this);
					var $button = $form.find('button[type="submit"]');
					var $result = $('#user-result');
					
					$button.prop('disabled', true).text('Creating user...');
					$result.html('<div class="notice notice-info"><p>Creating test user...</p></div>');
					
					$.post(ajaxurl, {
						action: 'ennu_create_test_user',
						email: $('#test-email').val(),
						first_name: $('#test-firstname').val(),
						last_name: $('#test-lastname').val(),
						nonce: '<?php echo wp_create_nonce( 'ennu_teams_nonce' ); ?>'
					}, function(response) {
						if (response.success) {
							$result.html('<div class="notice notice-success"><p>✅ User created! User ID: ' + response.data.user_id + '<br>Check your Teams channel for the notification.</p></div>');
							// Update email field with new timestamp
							$('#test-email').val('test-' + Date.now() + '@example.com');
						} else {
							$result.html('<div class="notice notice-error"><p>❌ ' + response.data.message + '</p></div>');
						}
						$button.prop('disabled', false).text('Create Test User & Send Notification');
					});
				});
			});
			</script>
		</div>
		<?php
	}
	
	/**
	 * Test webhook AJAX handler
	 */
	public function handle_test_webhook() {
		check_ajax_referer( 'ennu_teams_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		
		$webhook = sanitize_text_field( $_POST['webhook'] );
		
		// Send test data customized per notification type
		$test_data = array(
			'test_mode' => true,
			'patient_name' => 'Test Patient',
			'patient_email' => 'test@example.com',
			'patient_id' => '999',
			'timestamp' => current_time( 'c' )
		);
		
		// Customize data based on webhook type
		switch ( $webhook ) {
			case 'assessment':
				$test_data['assessment_type'] = 'Weight Loss Assessment';
				$test_data['scores'] = array( 
					'Body' => 8.5, 
					'Mind' => 7.2, 
					'Lifestyle' => 6.8,
					'Aesthetics' => 9.1
				);
				$test_data['dashboard_url'] = admin_url( 'admin.php?page=ennu-life' );
				break;
				
			case 'biomarker':
				$test_data['count'] = '12';
				$test_data['critical_markers'] = array( 'Glucose', 'HbA1c' );
				$test_data['changes'] = array(
					'Glucose: 95 mg/dL → 110 mg/dL',
					'Testosterone: 450 ng/dL → 520 ng/dL'
				);
				break;
				
			case 'critical':
				$test_data['biomarker'] = 'Glucose';
				$test_data['value'] = '285 mg/dL';
				$test_data['risk_level'] = 'HIGH';
				$test_data['normal_range'] = '70-100 mg/dL';
				$test_data['actions'] = "⚡ Contact patient within 24 hours\n📞 Schedule urgent consultation\n🏥 Consider immediate medical referral";
				break;
				
			case 'registration':
				$test_data['source'] = 'Landing Page';
				$test_data['profile_url'] = admin_url( 'user-edit.php?user_id=999' );
				break;
		}
		
		$success = $this->send_to_teams( $webhook, $test_data );
		
		wp_send_json( array(
			'success' => $success,
			'message' => $success ? 'Test sent! Check your Teams channel.' : 'Failed to send test. Make sure the webhook URL is configured.'
		) );
	}
	
	/**
	 * AJAX handler to create test user
	 */
	public function handle_create_test_user() {
		check_ajax_referer( 'ennu_teams_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		
		$email = sanitize_email( $_POST['email'] );
		$first_name = sanitize_text_field( $_POST['first_name'] );
		$last_name = sanitize_text_field( $_POST['last_name'] );
		
		// Check if user exists
		if ( email_exists( $email ) ) {
			wp_send_json_error( array( 'message' => 'User with this email already exists.' ) );
			return;
		}
		
		// Create user
		$user_data = array(
			'user_login' => $email,
			'user_email' => $email,
			'user_pass' => wp_generate_password(),
			'first_name' => $first_name,
			'last_name' => $last_name,
			'role' => 'subscriber'
		);
		
		$user_id = wp_insert_user( $user_data );
		
		if ( is_wp_error( $user_id ) ) {
			wp_send_json_error( array( 'message' => 'Failed to create user: ' . $user_id->get_error_message() ) );
			return;
		}
		
		// The user_register hook should automatically trigger the Teams notification
		wp_send_json_success( array( 
			'user_id' => $user_id,
			'message' => 'User created successfully. The Teams notification should have been sent automatically.'
		) );
	}
	
	/**
	 * Helper functions
	 */
	
	private function get_user_full_name( $user_id ) {
		$first = get_user_meta( $user_id, 'first_name', true );
		$last = get_user_meta( $user_id, 'last_name', true );
		$user = get_userdata( $user_id );
		return trim( $first . ' ' . $last ) ?: $user->display_name;
	}
	
	private function format_biomarker_name( $key ) {
		$names = array(
			'glucose' => 'Glucose',
			'hba1c' => 'HbA1c',
			'testosterone' => 'Testosterone',
			'vitamin_d' => 'Vitamin D',
			'tsh' => 'TSH',
			'hdl_cholesterol' => 'HDL Cholesterol',
			'ldl_cholesterol' => 'LDL Cholesterol',
			'hscrp' => 'hs-CRP'
		);
		return $names[ $key ] ?? ucwords( str_replace( '_', ' ', $key ) );
	}
	
	private function get_biomarker_unit( $key ) {
		$units = array(
			'glucose' => 'mg/dL',
			'hba1c' => '%',
			'testosterone' => 'ng/dL',
			'vitamin_d' => 'ng/mL',
			'tsh' => 'mIU/L',
			'hdl_cholesterol' => 'mg/dL',
			'ldl_cholesterol' => 'mg/dL',
			'hscrp' => 'mg/L'
		);
		return $units[ $key ] ?? '';
	}
	
	private function is_critical_value( $key, $value ) {
		$critical = array(
			'hba1c' => array( 'high' => 9.0, 'low' => 3.0 ),
			'glucose' => array( 'high' => 200, 'low' => 60 ),
			'blood_pressure_systolic' => array( 'high' => 180, 'low' => 90 )
		);
		
		if ( isset( $critical[ $key ] ) ) {
			$limits = $critical[ $key ];
			if ( isset( $limits['high'] ) && $value >= $limits['high'] ) return true;
			if ( isset( $limits['low'] ) && $value <= $limits['low'] ) return true;
		}
		
		return false;
	}
	
	private function is_optimal_value( $key, $value ) {
		$optimal = array(
			'glucose' => array( 70, 100 ),
			'hba1c' => array( 4.0, 5.6 ),
			'testosterone' => array( 400, 900 ),
			'vitamin_d' => array( 40, 80 )
		);
		
		if ( isset( $optimal[ $key ] ) ) {
			return $value >= $optimal[ $key ][0] && $value <= $optimal[ $key ][1];
		}
		
		return true;
	}
	
	private function get_normal_range( $key ) {
		$ranges = array(
			'glucose' => '70-100 mg/dL',
			'hba1c' => '4.0-5.6%',
			'testosterone' => '400-900 ng/dL',
			'vitamin_d' => '40-80 ng/mL'
		);
		return $ranges[ $key ] ?? 'N/A';
	}
	
	private function calculate_risk_level( $key, $value ) {
		if ( $this->is_critical_value( $key, $value ) ) {
			return 'HIGH';
		}
		if ( ! $this->is_optimal_value( $key, $value ) ) {
			return 'MEDIUM';
		}
		return 'LOW';
	}
	
	private function get_required_actions( $key, $value, $risk_level ) {
		if ( $risk_level === 'HIGH' ) {
			return "1. ⚡ Contact patient within 24 hours\n" .
			       "2. 📞 Schedule urgent consultation\n" .
			       "3. 🏥 Consider immediate medical referral\n" .
			       "4. 💊 Review medication compliance";
		}
		return "1. 📞 Contact patient within 48 hours\n" .
		       "2. 📅 Schedule follow-up appointment";
	}
	
	private function get_last_appointment( $user_id ) {
		// Check if using Amelia
		global $wpdb;
		$user = get_userdata( $user_id );
		
		$last = $wpdb->get_var( $wpdb->prepare(
			"SELECT bookingStart 
			FROM {$wpdb->prefix}amelia_customer_bookings cb
			JOIN {$wpdb->prefix}amelia_users u ON cb.customerId = u.id
			WHERE u.email = %s
			AND bookingStart < NOW()
			ORDER BY bookingStart DESC
			LIMIT 1",
			$user->user_email
		) );
		
		return $last ? date( 'F j, Y', strtotime( $last ) ) : 'Never';
	}
	
	private function get_location_from_ip( $ip ) {
		// Simple implementation - you could use a service like ipapi.co
		return 'United States'; // Default
	}
	
	private function log_critical_alert( $user_id, $biomarker, $value ) {
		$alerts = get_option( 'ennu_critical_alerts_log', array() );
		$alerts[] = array(
			'user_id' => $user_id,
			'biomarker' => $biomarker,
			'value' => $value,
			'timestamp' => current_time( 'mysql' )
		);
		
		// Keep last 100
		if ( count( $alerts ) > 100 ) {
			$alerts = array_slice( $alerts, -100 );
		}
		
		update_option( 'ennu_critical_alerts_log', $alerts );
	}
	
	private function send_critical_alert_for_biomarker( $user_id, $biomarker_data ) {
		foreach ( $biomarker_data as $key => $value ) {
			if ( $this->is_critical_value( $key, $value ) ) {
				$this->send_critical_alert( $user_id, $key, $value );
				break; // Send only the first critical
			}
		}
	}
	
	/**
	 * Get notification title based on type
	 */
	private function get_notification_title( $type, $data ) {
		$titles = array(
			'assessment' => 'Patient Assessment Completed',
			'biomarker' => 'Biomarker Update',
			'critical' => '⚠️ CRITICAL ALERT',
			'registration' => 'New Patient Registration',
			'appointment' => 'New Appointment Booked',
			'system_alert' => 'System Alert',
			'revenue' => 'Revenue Update',
			'success' => 'Patient Success Story'
		);
		
		return $titles[ $type ] ?? 'ENNU Life Notification';
	}
	
	/**
	 * Build assessment notification card
	 */
	private function build_assessment_card( $data ) {
		$facts = array(
			array( 'name' => 'Patient', 'value' => $data['patient_name'] ?? 'Unknown' ),
			array( 'name' => 'Email', 'value' => $data['patient_email'] ?? 'N/A' ),
			array( 'name' => 'Assessment', 'value' => $data['assessment_type'] ?? 'Unknown' ),
		);
		
		// Add scores if available
		if ( ! empty( $data['scores'] ) && is_array( $data['scores'] ) ) {
			foreach ( $data['scores'] as $category => $score ) {
				$facts[] = array( 
					'name' => $category . ' Score', 
					'value' => number_format( $score, 1 ) . '/10'
				);
			}
		}
		
		return array(
			array(
				'activityTitle' => '📋 ' . ( $data['assessment_type'] ?? 'Assessment' ) . ' Completed',
				'activitySubtitle' => date( 'F j, Y g:i A' ),
				'facts' => $facts
			)
		);
	}
	
	/**
	 * Build biomarker notification card
	 */
	private function build_biomarker_card( $data ) {
		$facts = array(
			array( 'name' => 'Patient', 'value' => $data['patient_name'] ?? 'Unknown' ),
			array( 'name' => 'Updated Biomarkers', 'value' => $data['count'] ?? '0' ),
		);
		
		// Add critical markers if any
		if ( ! empty( $data['critical_markers'] ) ) {
			$facts[] = array( 
				'name' => '⚠️ Critical', 
				'value' => implode( ', ', $data['critical_markers'] )
			);
		}
		
		return array(
			array(
				'activityTitle' => '🔬 Biomarker Update',
				'activitySubtitle' => date( 'F j, Y g:i A' ),
				'facts' => $facts
			)
		);
	}
	
	/**
	 * Build critical alert card
	 */
	private function build_critical_card( $data ) {
		return array(
			array(
				'activityTitle' => '🚨 CRITICAL VALUE DETECTED',
				'activitySubtitle' => 'Immediate Action Required',
				'facts' => array(
					array( 'name' => 'Patient', 'value' => $data['patient_name'] ?? 'Unknown' ),
					array( 'name' => 'Biomarker', 'value' => $data['biomarker'] ?? 'Unknown' ),
					array( 'name' => 'Value', 'value' => $data['value'] ?? 'N/A' ),
					array( 'name' => 'Risk Level', 'value' => $data['risk_level'] ?? 'HIGH' ),
				),
				'text' => $data['actions'] ?? 'Contact patient immediately.'
			)
		);
	}
	
	/**
	 * Build registration notification card
	 */
	private function build_registration_card( $data ) {
		return array(
			array(
				'activityTitle' => '🎉 New Patient Registration',
				'activitySubtitle' => date( 'F j, Y g:i A' ),
				'facts' => array(
					array( 'name' => 'Patient Name', 'value' => $data['patient_name'] ?? 'Not provided' ),
					array( 'name' => 'Email', 'value' => $data['patient_email'] ?? 'N/A' ),
					array( 'name' => 'User ID', 'value' => '#' . ( $data['patient_id'] ?? '0' ) ),
					array( 'name' => 'Source', 'value' => $data['source'] ?? 'Direct' ),
				)
			)
		);
	}
	
	/**
	 * Build appointment notification card
	 */
	private function build_appointment_card( $data ) {
		return array(
			array(
				'activityTitle' => '📅 New Appointment Booked',
				'activitySubtitle' => $data['appointment_date'] ?? date( 'F j, Y' ),
				'facts' => array(
					array( 'name' => 'Patient', 'value' => $data['patient_name'] ?? 'Unknown' ),
					array( 'name' => 'Service', 'value' => $data['service'] ?? 'Consultation' ),
					array( 'name' => 'Date', 'value' => $data['appointment_date'] ?? 'TBD' ),
					array( 'name' => 'Time', 'value' => $data['appointment_time'] ?? 'TBD' ),
					array( 'name' => 'Provider', 'value' => $data['provider'] ?? 'TBD' ),
				)
			)
		);
	}
	
	/**
	 * Build system alert card
	 */
	private function build_system_alert_card( $data ) {
		return array(
			array(
				'activityTitle' => '⚙️ System Alert',
				'activitySubtitle' => $data['error_type'] ?? 'System Error',
				'facts' => array(
					array( 'name' => 'Component', 'value' => $data['component'] ?? 'System' ),
					array( 'name' => 'Severity', 'value' => strtoupper( $data['severity'] ?? 'medium' ) ),
					array( 'name' => 'Error Code', 'value' => $data['error_code'] ?? 'N/A' ),
				),
				'text' => $data['error_message'] ?? 'An error occurred.'
			)
		);
	}
	
	/**
	 * Build revenue notification card
	 */
	private function build_revenue_card( $data ) {
		return array(
			array(
				'activityTitle' => '💰 Revenue Update',
				'activitySubtitle' => date( 'F j, Y' ),
				'facts' => array(
					array( 'name' => 'Transaction Type', 'value' => $data['type'] ?? 'Payment' ),
					array( 'name' => 'Amount', 'value' => '$' . number_format( $data['amount'] ?? 0, 2 ) ),
					array( 'name' => 'Patient', 'value' => $data['patient_name'] ?? 'N/A' ),
					array( 'name' => 'Status', 'value' => $data['status'] ?? 'Completed' ),
				)
			)
		);
	}
	
	/**
	 * Build success story card
	 */
	private function build_success_card( $data ) {
		return array(
			array(
				'activityTitle' => '🌟 Patient Success Story',
				'activitySubtitle' => date( 'F j, Y' ),
				'facts' => array(
					array( 'name' => 'Patient', 'value' => $data['patient_name'] ?? 'Anonymous' ),
					array( 'name' => 'Achievement', 'value' => $data['achievement'] ?? 'Goal Reached' ),
					array( 'name' => 'Duration', 'value' => $data['duration'] ?? 'N/A' ),
				),
				'text' => $data['story'] ?? 'Patient has achieved significant health improvements.'
			)
		);
	}
	
	/**
	 * Build generic notification card (fallback)
	 */
	private function build_generic_card( $data ) {
		$facts = array();
		
		// Convert data to facts
		foreach ( $data as $key => $value ) {
			if ( ! in_array( $key, array( 'webhook_type', 'site_url', 'wordpress_version', 'plugin_version', 'profile_url', 'dashboard_url', 'timestamp' ) ) ) {
				if ( ! is_array( $value ) && ! is_object( $value ) ) {
					$facts[] = array(
						'name' => ucwords( str_replace( '_', ' ', $key ) ),
						'value' => (string) $value
					);
				}
			}
		}
		
		return array(
			array(
				'activityTitle' => 'ENNU Life Notification',
				'activitySubtitle' => date( 'F j, Y g:i A' ),
				'facts' => array_slice( $facts, 0, 10 ) // Limit to 10 facts
			)
		);
	}
	
	/**
	 * Send direct notification to Teams New Registrations channel
	 */
	private function send_direct_to_teams_registration( $user_id, $full_name, $email ) {
		// Now using the main send_to_teams method
		$data = array(
			'patient_name' => $full_name,
			'patient_email' => $email,
			'patient_id' => $user_id,
			'source' => isset( $_SERVER['HTTP_REFERER'] ) ? parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_HOST ) : 'Direct',
			'profile_url' => admin_url( 'user-edit.php?user_id=' . $user_id )
		);
		
		return $this->send_to_teams( 'registration', $data );
		
		// Create Teams message card
		$message = array(
			'@type' => 'MessageCard',
			'@context' => 'https://schema.org/extensions',
			'themeColor' => '00B294',
			'summary' => 'New Patient Registration',
			'sections' => array(
				array(
					'activityTitle' => '🎉 New Patient Registration',
					'activitySubtitle' => current_time( 'F j, Y g:i A' ),
					'facts' => array(
						array(
							'name' => 'Patient Name',
							'value' => $full_name ?: 'Not provided'
						),
						array(
							'name' => 'Email',
							'value' => $email
						),
						array(
							'name' => 'User ID',
							'value' => '#' . $user_id
						),
						array(
							'name' => 'Registration Time',
							'value' => current_time( 'g:i A' )
						),
						array(
							'name' => 'Source',
							'value' => isset( $_SERVER['HTTP_REFERER'] ) ? parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_HOST ) : 'Direct'
						)
					)
				)
			),
			'potentialAction' => array(
				array(
					'@type' => 'OpenUri',
					'name' => 'View Profile',
					'targets' => array(
						array(
							'os' => 'default',
							'uri' => admin_url( 'user-edit.php?user_id=' . $user_id )
						)
					)
				)
			)
		);
		
		// Send to Teams
		$response = wp_remote_post( $webhook_url, array(
			'headers' => array(
				'Content-Type' => 'application/json'
			),
			'body' => json_encode( $message ),
			'timeout' => 10
		) );
		
		// Log the result
		if ( is_wp_error( $response ) ) {
			error_log( 'ENNU Teams Registration Notification Error: ' . $response->get_error_message() );
		}
	}
}

// Initialize the integration
add_action( 'init', array( 'ENNU_N8N_Integration', 'get_instance' ) );

// Add to main plugin file to load this class
// require_once plugin_dir_path( __FILE__ ) . 'includes/class-n8n-integration.php';