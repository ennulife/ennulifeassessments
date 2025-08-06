<?php
/**
 * Slack Notifications Admin Interface
 *
 * Provides admin interface for configuring and monitoring Slack notifications
 *
 * @package ENNU_Life_Assessments
 * @subpackage Admin
 * @since 64.48.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Slack_Admin {

	/**
	 * Initialize the admin interface
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'wp_ajax_ennu_test_slack_notification', array( $this, 'handle_test_notification' ) );
		add_action( 'wp_ajax_ennu_slack_notification_status', array( $this, 'handle_notification_status' ) );
		add_action( 'wp_ajax_ennu_clear_slack_logs', array( $this, 'handle_clear_logs' ) );
		add_action( 'wp_ajax_ennu_test_all_notifications', array( $this, 'handle_test_all_notifications' ) );
		add_action( 'wp_ajax_ennu_test_all_notifications_direct', array( $this, 'handle_test_all_notifications_direct' ) );
		add_action( 'wp_ajax_ennu_debug_webhook', array( $this, 'handle_debug_webhook' ) );
		add_action( 'wp_ajax_ennu_run_comprehensive_test', array( $this, 'handle_run_comprehensive_test' ) );
		
		// Add direct URL endpoint for testing
		add_action( 'init', array( $this, 'handle_direct_test_url' ) );
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'ennu-life',
			'Slack Notifications',
			'Slack Notifications',
			'manage_options',
			'ennu-slack-notifications',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting( 'ennu_slack_settings', 'ennu_slack_enabled' );
		register_setting( 'ennu_slack_settings', 'ennu_slack_webhook_url' );
		register_setting( 'ennu_slack_settings', 'ennu_slack_channel' );
		register_setting( 'ennu_slack_settings', 'ennu_slack_username' );
	}

	/**
	 * Render admin page
	 */
	public function render_admin_page() {
		$slack_manager = ENNU_Slack_Notifications_Manager::get_instance();
		$logs = $slack_manager->get_notification_logs();
		$stats = $slack_manager->get_notification_statistics();
		
		// Ensure stats is an array
		if ( ! is_array( $stats ) ) {
			$stats = array(
				'total_notifications' => 0,
				'successful_notifications' => 0,
				'failed_notifications' => 0,
				'by_type' => array(),
			);
		}
		?>
		<div class="wrap">
			<h1>Slack Notifications Configuration</h1>
			
			<div class="ennu-slack-admin">
				<!-- Configuration Section -->
				<div class="ennu-slack-section">
					<h2>Configuration</h2>
					<form method="post" action="options.php">
						<?php settings_fields( 'ennu_slack_settings' ); ?>
						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="ennu_slack_enabled">Enable Slack Notifications</label>
								</th>
								<td>
									<input type="checkbox" id="ennu_slack_enabled" name="ennu_slack_enabled" value="1" <?php checked( get_option( 'ennu_slack_enabled', false ) ); ?> />
									<p class="description">Enable real-time Slack notifications for assessment completions, user registrations, and critical health alerts.</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="ennu_slack_webhook_url">Webhook URL</label>
								</th>
								<td>
									<input type="url" id="ennu_slack_webhook_url" name="ennu_slack_webhook_url" value="<?php echo esc_attr( get_option( 'ennu_slack_webhook_url', '' ) ); ?>" class="regular-text" />
									<p class="description">Your Slack incoming webhook URL. Format: https://hooks.slack.com/services/T00000000/B00000000/XXXXXXXXXXXXXXXXXXXXXXXX</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="ennu_slack_channel">Default Channel</label>
								</th>
								<td>
									<input type="text" id="ennu_slack_channel" name="ennu_slack_channel" value="<?php echo esc_attr( get_option( 'ennu_slack_channel', '#general' ) ); ?>" class="regular-text" />
									<p class="description">Default channel for notifications (e.g., #general, #ennu-assessments)</p>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="ennu_slack_username">Bot Username</label>
								</th>
								<td>
									<input type="text" id="ennu_slack_username" name="ennu_slack_username" value="<?php echo esc_attr( get_option( 'ennu_slack_username', 'ENNU Life Bot' ) ); ?>" class="regular-text" />
									<p class="description">Username for the Slack bot</p>
								</td>
							</tr>
						</table>
						<?php submit_button( 'Save Settings' ); ?>
					</form>
				</div>

				<!-- Test Section -->
				<div class="ennu-slack-section">
					<h2>Test Notifications</h2>
					<p>Test your Slack integration by sending notifications.</p>
					<div style="margin-bottom: 15px;">
						<button type="button" id="test-slack-notification" class="button button-primary">Send Single Test</button>
						<button type="button" id="test-all-notifications" class="button button-secondary" style="margin-left: 10px;">🧪 Test ALL Notification Types</button>
						<a href="<?php echo admin_url( 'admin-ajax.php?action=ennu_test_all_notifications_direct&nonce=' . wp_create_nonce( 'ennu_slack_admin' ) ); ?>" class="button button-secondary" style="margin-left: 10px;">🚀 Direct Test (All Types)</a>
						<button type="button" id="debug-webhook" class="button button-secondary" style="margin-left: 10px;">🔧 Debug Webhook</button>
					</div>
					<div id="test-result" style="margin-top: 10px;"></div>
					<div id="test-all-result" style="margin-top: 10px;"></div>
				</div>

				<!-- Status Section -->
				<div class="ennu-slack-section">
					<h2>Integration Status</h2>
					<div id="slack-status">
						<p>Loading status...</p>
					</div>
				</div>

				<!-- Statistics Section -->
				<div class="ennu-slack-section">
					<h2>Notification Statistics</h2>
					<div class="ennu-stats-grid">
						<div class="ennu-stat-card">
							<h3><?php echo esc_html( isset( $stats['total_notifications'] ) ? $stats['total_notifications'] : 0 ); ?></h3>
							<p>Total Notifications</p>
						</div>
						<div class="ennu-stat-card">
							<h3><?php echo esc_html( isset( $stats['successful_notifications'] ) ? $stats['successful_notifications'] : 0 ); ?></h3>
							<p>Successful</p>
						</div>
						<div class="ennu-stat-card">
							<h3><?php echo esc_html( isset( $stats['failed_notifications'] ) ? $stats['failed_notifications'] : 0 ); ?></h3>
							<p>Failed</p>
						</div>
						<div class="ennu-stat-card">
							<h3><?php echo esc_html( isset( $stats['by_type'] ) && is_array( $stats['by_type'] ) ? count( $stats['by_type'] ) : 0 ); ?></h3>
							<p>Notification Types</p>
						</div>
					</div>
				</div>

				<!-- Logs Section -->
				<div class="ennu-slack-section">
					<h2>Recent Logs</h2>
					<div class="ennu-logs-controls">
						<button type="button" id="clear-slack-logs" class="button button-secondary">Clear Logs</button>
					</div>
					<div class="ennu-logs-container">
						<?php if ( empty( $logs ) ) : ?>
							<p>No logs available.</p>
						<?php else : ?>
							<table class="wp-list-table widefat fixed striped">
								<thead>
									<tr>
										<th>Timestamp</th>
										<th>Type</th>
										<th>Notification Type</th>
										<th>Message</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( array_slice( $logs, -20 ) as $log ) : ?>
										<?php if ( is_array( $log ) ) : ?>
											<tr>
												<td><?php echo esc_html( isset( $log['timestamp'] ) ? $log['timestamp'] : 'N/A' ); ?></td>
												<td>
													<span class="ennu-log-type ennu-log-type-<?php echo esc_attr( isset( $log['type'] ) ? $log['type'] : 'unknown' ); ?>">
														<?php echo esc_html( ucfirst( isset( $log['type'] ) ? $log['type'] : 'unknown' ) ); ?>
													</span>
												</td>
												<td><?php echo esc_html( isset( $log['notification_type'] ) ? $log['notification_type'] : 'N/A' ); ?></td>
												<td><?php echo esc_html( isset( $log['message'] ) ? $log['message'] : 'N/A' ); ?></td>
											</tr>
										<?php endif; ?>
									<?php endforeach; ?>
								</tbody>
							</table>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<style>
			.ennu-slack-admin {
				max-width: 1200px;
			}
			.ennu-slack-section {
				background: #fff;
				border: 1px solid #ccd0d4;
				border-radius: 4px;
				padding: 20px;
				margin-bottom: 20px;
			}
			.ennu-stats-grid {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
				gap: 20px;
				margin-top: 15px;
			}
			.ennu-stat-card {
				background: #f9f9f9;
				border: 1px solid #ddd;
				border-radius: 4px;
				padding: 20px;
				text-align: center;
			}
			.ennu-stat-card h3 {
				margin: 0 0 10px 0;
				font-size: 24px;
				color: #0073aa;
			}
			.ennu-stat-card p {
				margin: 0;
				color: #666;
			}
			.ennu-logs-controls {
				margin-bottom: 15px;
			}
			.ennu-log-type {
				padding: 2px 8px;
				border-radius: 3px;
				font-size: 12px;
				font-weight: bold;
			}
			.ennu-log-type-success {
				background: #d4edda;
				color: #155724;
			}
			.ennu-log-type-error {
				background: #f8d7da;
				color: #721c24;
			}
		</style>

		<script>
		jQuery(document).ready(function($) {
			// Test notification
			$('#test-slack-notification').on('click', function() {
				var button = $(this);
				button.prop('disabled', true).text('Sending...');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'ennu_test_slack_notification',
						nonce: '<?php echo wp_create_nonce( 'ennu_slack_test' ); ?>'
					},
					success: function(response) {
						if (response.success) {
							$('#test-result').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
						} else {
							$('#test-result').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
						}
					},
					error: function() {
						$('#test-result').html('<div class="notice notice-error"><p>Failed to send test notification.</p></div>');
					},
					complete: function() {
						button.prop('disabled', false).text('Send Single Test');
					}
				});
			});

			// Test all notifications
			$('#test-all-notifications').on('click', function() {
				var button = $(this);
				button.prop('disabled', true).text('🧪 Testing ALL Notifications...');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'ennu_test_all_notifications',
						nonce: '<?php echo wp_create_nonce( 'ennu_slack_admin' ); ?>'
					},
					success: function(response) {
						if (response.success) {
							var data = response.data;
							var html = '<div class="notice notice-success">';
							html += '<h4>✅ All Notification Types Tested Successfully!</h4>';
							html += '<p><strong>User:</strong> ' + data.user_info.name + ' (' + data.user_info.email + ')</p>';
							html += '<p><strong>Channel:</strong> ' + data.user_info.channel + '</p>';
							html += '<h5>Test Results:</h5>';
							html += '<ul>';
							data.results.forEach(function(result) {
								html += '<li>' + result + '</li>';
							});
							html += '</ul>';
							html += '<h5>Statistics:</h5>';
							html += '<ul>';
							html += '<li>Total Notifications: ' + data.statistics.total_notifications + '</li>';
							html += '<li>Successful: ' + data.statistics.successful_notifications + '</li>';
							html += '<li>Failed: ' + data.statistics.failed_notifications + '</li>';
							html += '<li>Notification Types: ' + Object.keys(data.statistics.by_type).length + '</li>';
							html += '</ul>';
							html += '<p><strong>Check your Slack channel #basic-assessments to see all notifications!</strong></p>';
							html += '</div>';
							$('#test-all-result').html(html);
						} else {
							$('#test-all-result').html('<div class="notice notice-error"><p>Failed to test all notifications.</p></div>');
						}
					},
					error: function() {
						$('#test-all-result').html('<div class="notice notice-error"><p>Failed to test all notifications.</p></div>');
					},
					complete: function() {
						button.prop('disabled', false).text('🧪 Test ALL Notification Types');
					}
				});
			});

			// Debug webhook
			$('#debug-webhook').on('click', function() {
				var button = $(this);
				button.prop('disabled', true).text('🔧 Debugging...');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'ennu_debug_webhook',
						nonce: '<?php echo wp_create_nonce( 'ennu_slack_admin' ); ?>'
					},
					success: function(response) {
						if (response.success) {
							var data = response.data;
							var html = '<div class="notice ' + (data.success ? 'notice-success' : 'notice-error') + '">';
							html += '<h4>🔧 Webhook Debug Results</h4>';
							html += '<p><strong>Status:</strong> ' + (data.success ? '✅ Success' : '❌ Failed') + '</p>';
							html += '<p><strong>Message:</strong> ' + data.message + '</p>';
							if (data.response_code) {
								html += '<p><strong>Response Code:</strong> ' + data.response_code + '</p>';
							}
							if (data.response_body) {
								html += '<p><strong>Response Body:</strong> <code>' + data.response_body + '</code></p>';
							}
							if (data.payload) {
								html += '<p><strong>Payload:</strong> <code>' + data.payload + '</code></p>';
							}
							html += '</div>';
							$('#test-all-result').html(html);
						} else {
							$('#test-all-result').html('<div class="notice notice-error"><p>Debug failed.</p></div>');
						}
					},
					error: function() {
						$('#test-all-result').html('<div class="notice notice-error"><p>Debug failed.</p></div>');
					},
					complete: function() {
						button.prop('disabled', false).text('🔧 Debug Webhook');
					}
				});
			});

			// Load status
			function loadStatus() {
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'ennu_slack_notification_status',
						nonce: '<?php echo wp_create_nonce( 'ennu_slack_status' ); ?>'
					},
					success: function(response) {
						if (response.success) {
							var status = response.data;
							var html = '<table class="form-table">';
							html += '<tr><th>Enabled:</th><td>' + (status.enabled ? 'Yes' : 'No') + '</td></tr>';
							html += '<tr><th>Webhook Configured:</th><td>' + (status.webhook_configured ? 'Yes' : 'No') + '</td></tr>';
							html += '<tr><th>Channel:</th><td>' + status.channel + '</td></tr>';
							html += '<tr><th>Username:</th><td>' + status.username + '</td></tr>';
							html += '<tr><th>Queue Count:</th><td>' + status.queue_count + '</td></tr>';
							html += '</table>';
							$('#slack-status').html(html);
						}
					}
				});
			}

			// Clear logs
			$('#clear-slack-logs').on('click', function() {
				if (confirm('Are you sure you want to clear all Slack notification logs?')) {
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'ennu_clear_slack_logs',
							nonce: '<?php echo wp_create_nonce( 'ennu_slack_clear_logs' ); ?>'
						},
						success: function(response) {
							if (response.success) {
								location.reload();
							}
						}
					});
				}
			});

			// Load status on page load
			loadStatus();
		});
		</script>
		<?php
	}

	/**
	 * Handle test notification AJAX request
	 */
	public function handle_test_notification() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		check_ajax_referer( 'ennu_slack_test', 'nonce' );

		$slack_manager = ENNU_Slack_Notifications_Manager::get_instance();
		$success = $slack_manager->handle_test_notification();

		wp_send_json_success( array(
			'success' => $success,
			'message' => $success ? 'Test notification sent successfully!' : 'Test notification failed. Check error logs.'
		) );
	}

	/**
	 * Handle notification status AJAX request
	 */
	public function handle_notification_status() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		check_ajax_referer( 'ennu_slack_status', 'nonce' );

		$slack_manager = ENNU_Slack_Notifications_Manager::get_instance();
		$status = $slack_manager->handle_notification_status();

		wp_send_json_success( $status );
	}

	/**
	 * Handle clear logs AJAX request.
	 */
	public function handle_clear_logs() {
		// Verify nonce
		check_ajax_referer( 'ennu_slack_admin', 'nonce' );
		
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		
		$slack_manager = ENNU_Slack_Notifications_Manager::get_instance();
		$slack_manager->clear_notification_logs();
		
		wp_send_json_success( array( 'message' => 'Logs cleared successfully' ) );
	}

	/**
	 * Handle test all notifications AJAX request.
	 */
	public function handle_test_all_notifications() {
		// Verify nonce
		check_ajax_referer( 'ennu_slack_admin', 'nonce' );
		
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		
		$result = $this->test_all_notifications();
		wp_send_json_success( $result );
	}

	/**
	 * Handle direct test all notifications AJAX request.
	 */
	public function handle_test_all_notifications_direct() {
		// Verify nonce
		check_ajax_referer( 'ennu_slack_admin', 'nonce' );
		
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		
		// Test all notifications directly
		$result = $this->test_all_notifications();
		
		// Output results as HTML
		echo '<!DOCTYPE html>';
		echo '<html><head>';
		echo '<title>ENNU Slack Notifications Test Results</title>';
		echo '<style>';
		echo 'body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }';
		echo '.container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }';
		echo 'h1 { color: #333; text-align: center; }';
		echo '.test-section { margin: 20px 0; padding: 15px; border-left: 4px solid #0073aa; background: #f9f9f9; }';
		echo '.success { color: #28a745; font-weight: bold; }';
		echo '.warning { color: #ffc107; font-weight: bold; }';
		echo '.stats { background: #e9ecef; padding: 15px; border-radius: 5px; margin: 20px 0; }';
		echo '</style>';
		echo '</head><body>';
		
		echo '<div class="container">';
		echo '<h1>🧪 ENNU Life Assessments - Slack Notifications Test Results</h1>';
		
		if ( $result['success'] ) {
			echo '<div class="test-section" style="background: #d4edda; border-left-color: #28a745;">';
			echo '<h2>✅ All Notification Types Tested Successfully!</h2>';
			echo '<p><strong>User:</strong> ' . $result['user_info']['name'] . ' (' . $result['user_info']['email'] . ')</p>';
			echo '<p><strong>Channel:</strong> ' . $result['user_info']['channel'] . '</p>';
			echo '<h3>Test Results:</h3>';
			echo '<ul>';
			foreach ( $result['results'] as $test_result ) {
				echo '<li>' . esc_html( $test_result ) . '</li>';
			}
			echo '</ul>';
			echo '<h3>Statistics:</h3>';
			echo '<ul>';
			echo '<li>Total Notifications: ' . $result['statistics']['total_notifications'] . '</li>';
			echo '<li>Successful: ' . $result['statistics']['successful_notifications'] . '</li>';
			echo '<li>Failed: ' . $result['statistics']['failed_notifications'] . '</li>';
			echo '<li>Notification Types: ' . count( $result['statistics']['by_type'] ) . '</li>';
			echo '</ul>';
			echo '<p><strong>Check your Slack channel #basic-assessments to see all notifications!</strong></p>';
			echo '</div>';
		} else {
			echo '<div class="test-section" style="background: #f8d7da; border-left-color: #dc3545;">';
			echo '<h2>❌ Test Failed</h2>';
			echo '<p>Failed to test all notifications.</p>';
			echo '</div>';
		}
		
		echo '<div class="test-section">';
		echo '<h2>What You Should See in Slack</h2>';
		echo '<ol>';
		echo '<li><strong>Assessment Completion Notifications:</strong> Messages with user info, assessment type, completion time, and score</li>';
		echo '<li><strong>User Registration:</strong> New user alerts with email and registration time</li>';
		echo '<li><strong>Critical Health Alerts:</strong> High-priority alerts with red warning symbols</li>';
		echo '<li><strong>Appointment Bookings:</strong> Scheduling notifications with service details</li>';
		echo '<li><strong>Daily Summary:</strong> Analytics report with activity statistics</li>';
		echo '</ol>';
		echo '</div>';
		
		echo '<p><a href="' . admin_url( 'admin.php?page=ennu-slack-notifications' ) . '" class="button button-primary">← Back to Slack Settings</a></p>';
		echo '</div>';
		echo '</body></html>';
		exit;
	}

	/**
	 * Handle debug webhook AJAX request
	 */
	public function handle_debug_webhook() {
		// Verify nonce
		check_ajax_referer( 'ennu_slack_admin', 'nonce' );
		
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		
		$slack_manager = ENNU_Slack_Notifications_Manager::get_instance();
		$debug_result = $slack_manager->debug_webhook_connection();
		
		wp_send_json_success( $debug_result );
	}

	/**
	 * Handle run comprehensive test AJAX request.
	 */
	public function handle_run_comprehensive_test() {
		// Verify nonce
		check_ajax_referer( 'ennu_slack_admin', 'nonce' );
		
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}
		
		$result = $this->test_all_notifications();
		wp_send_json_success( $result );
	}

	/**
	 * Handle direct test URL
	 */
	public function handle_direct_test_url() {
		// Check if this is our test URL
		if ( isset( $_GET['ennu_slack_test'] ) && $_GET['ennu_slack_test'] === 'comprehensive' ) {
			// Set content type
			header( 'Content-Type: text/html; charset=utf-8' );
			
			echo '<!DOCTYPE html>';
			echo '<html lang="en">';
			echo '<head>';
			echo '<meta charset="UTF-8">';
			echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
			echo '<title>ENNU Life Assessments - Comprehensive Slack Test</title>';
			echo '<style>';
			echo 'body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }';
			echo '.container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }';
			echo '.success { color: #28a745; }';
			echo '.error { color: #dc3545; }';
			echo '.info { color: #17a2b8; }';
			echo '.test-result { margin: 10px 0; padding: 10px; border-left: 4px solid #ddd; }';
			echo '.test-result.success { border-left-color: #28a745; background: #f8fff9; }';
			echo '.test-result.error { border-left-color: #dc3545; background: #fff8f8; }';
			echo '.summary { margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px; }';
			echo '</style>';
			echo '</head>';
			echo '<body>';
			
			echo '<div class="container">';
			echo '<h1>🔧 ENNU Life Assessments - Comprehensive Slack Test</h1>';
			echo '<p class="info">Running comprehensive test of all Slack notification types...</p>';
			
			try {
				// Check if our plugin is active
				if ( ! class_exists( 'ENNU_Slack_Notifications_Manager' ) ) {
					throw new Exception( 'ENNU Slack Notifications Manager not found. Plugin may not be active.' );
				}
				
				// Get the Slack manager instance
				$slack_manager = ENNU_Slack_Notifications_Manager::get_instance();
				
				echo '<h2>📋 Configuration Check</h2>';
				echo '<ul>';
				echo '<li><strong>Enabled:</strong> <span class="' . ( $slack_manager->is_enabled() ? 'success' : 'error' ) . '">' . ( $slack_manager->is_enabled() ? 'Yes' : 'No' ) . '</span></li>';
				echo '<li><strong>Webhook URL:</strong> <span class="' . ( ! empty( $slack_manager->get_webhook_url() ) ? 'success' : 'error' ) . '">' . ( ! empty( $slack_manager->get_webhook_url() ) ? 'Configured' : 'Not configured' ) . '</span></li>';
				echo '<li><strong>Channel:</strong> ' . $slack_manager->get_channel() . '</li>';
				echo '<li><strong>Username:</strong> ' . $slack_manager->get_username() . '</li>';
				echo '</ul>';
				
				if ( ! $slack_manager->is_enabled() || empty( $slack_manager->get_webhook_url() ) ) {
					throw new Exception( 'Slack integration not properly configured.' );
				}
				
				echo '<h2>🧪 Running Comprehensive Test</h2>';
				
				// Run the comprehensive test
				$results = $slack_manager->run_comprehensive_test();
				
				echo '<h3>📊 Test Results</h3>';
				
				$success_count = 0;
				$total_count = 0;
				
				foreach ( $results as $test_name => $result ) {
					$test_display_name = ucwords( str_replace( '_', ' ', $test_name ) );
					
					if ( $test_name === 'debug_webhook' ) {
						$is_success = $result['success'];
						$message = $result['message'];
						if ( $is_success ) $success_count++;
					} else {
						$is_success = $result;
						$message = $result ? 'Success' : 'Failed';
						if ( $result ) $success_count++;
					}
					
					$total_count++;
					
					echo '<div class="test-result ' . ( $is_success ? 'success' : 'error' ) . '">';
					echo '<strong>' . $test_display_name . ':</strong> ' . $message;
					echo '</div>';
				}
				
				echo '<div class="summary">';
				echo '<h3>📈 Summary</h3>';
				echo '<p><strong>Tests Passed:</strong> ' . $success_count . '/' . $total_count . '</p>';
				echo '<p><strong>Success Rate:</strong> ' . round( ( $success_count / $total_count ) * 100, 2 ) . '%</p>';
				
				if ( $success_count === $total_count ) {
					echo '<h3 class="success">🎉 All tests passed! Slack integration is working perfectly!</h3>';
					echo '<p>Check your Slack channel <strong>#basic-assessments</strong> to see all the test notifications.</p>';
				} else {
					echo '<h3 class="error">⚠️ Some tests failed. Check the logs for details.</h3>';
				}
				echo '</div>';
				
			} catch ( Exception $e ) {
				echo '<h2 class="error">❌ Error</h2>';
				echo '<p><strong>Error:</strong> ' . $e->getMessage() . '</p>';
				echo '<p><strong>File:</strong> ' . $e->getFile() . '</p>';
				echo '<p><strong>Line:</strong> ' . $e->getLine() . '</p>';
			}
			
			echo '<hr>';
			echo '<p><em>Test completed at: ' . date( 'Y-m-d H:i:s' ) . '</em></p>';
			echo '<p><a href="' . admin_url( 'admin.php?page=ennu-slack-notifications' ) . '">← Back to Slack Notifications Admin</a></p>';
			
			echo '</div>';
			echo '</body>';
			echo '</html>';
			
			exit;
		}
	}

	/**
	 * Test all notification types.
	 * This function triggers every type of notification for testing purposes.
	 */
	public function test_all_notifications() {
		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized access' );
		}
		
		$slack_manager = ENNU_Slack_Notifications_Manager::get_instance();
		$user_id = get_current_user_id();
		$current_user = wp_get_current_user();
		
		$results = array();
		
		// 1. Test Assessment Completion Notifications
		$assessment_types = array( 'health_optimization', 'weight_loss', 'hormone_optimization', 'testosterone' );
		foreach ( $assessment_types as $type ) {
			$slack_manager->send_assessment_completion_notification( $user_id, $type );
			$results[] = "✅ {$type} assessment completion notification sent";
			sleep( 1 ); // Small delay to avoid rate limiting
		}
		
		// 2. Test User Registration Notification
		$slack_manager->send_user_registration_notification( $user_id );
		$results[] = "✅ User registration notification sent";
		sleep( 1 );
		
		// 3. Test Critical Health Alerts
		$critical_alerts = array(
			array(
				'assessment_type' => 'cardiovascular',
				'critical_finding' => 'Blood pressure reading 180/110 - requires immediate attention',
				'action_required' => 'Immediate medical consultation recommended',
				'severity' => 'high'
			),
			array(
				'assessment_type' => 'metabolic',
				'critical_finding' => 'HbA1c level 8.5% - indicates poor glycemic control',
				'action_required' => 'Immediate lifestyle intervention and monitoring required',
				'severity' => 'high'
			)
		);
		
		foreach ( $critical_alerts as $alert_data ) {
			$slack_manager->send_critical_health_alert( $user_id, $alert_data );
			$results[] = "⚠️ Critical health alert ({$alert_data['assessment_type']}) sent";
			sleep( 1 );
		}
		
		// 4. Test Appointment Booking Notifications
		$appointments = array(
			array(
				'service' => 'Hormone Optimization Consultation',
				'date_time' => '2025-01-15 15:00:00',
				'duration' => '60 minutes',
				'provider' => 'Dr. Elena Harmonix'
			),
			array(
				'service' => 'Comprehensive Health Assessment',
				'date_time' => '2025-01-20 10:30:00',
				'duration' => '90 minutes',
				'provider' => 'Dr. Victor Pulse'
			)
		);
		
		foreach ( $appointments as $booking_data ) {
			$slack_manager->send_appointment_notification( $user_id, $booking_data );
			$results[] = "✅ Appointment booking ({$booking_data['service']}) sent";
			sleep( 1 );
		}
		
		// 5. Test Daily Summary Notification
		$summary_data = array(
			'total_assessments' => 15,
			'new_users' => 8,
			'critical_alerts' => 2,
			'appointments' => 12
		);
		$slack_manager->send_daily_summary( $summary_data );
		$results[] = "✅ Daily summary notification sent";
		
		// Get statistics
		$stats = $slack_manager->get_notification_statistics();
		
		return array(
			'success' => true,
			'message' => 'All notification types tested successfully!',
			'results' => $results,
			'statistics' => $stats,
			'user_info' => array(
				'name' => $current_user->display_name,
				'email' => $current_user->user_email,
				'channel' => '#basic-assessments'
			)
		);
	}
} 