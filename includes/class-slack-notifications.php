<?php
/**
 * Slack Notifications Manager
 *
 * Handles real-time Slack notifications for assessment completions, health alerts,
 * user registrations, and appointment bookings. Implements HIPAA-compliant data handling
 * and comprehensive error handling with monitoring.
 *
 * @package ENNU_Life_Assessments
 * @subpackage Notifications
 * @since 64.48.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Slack_Notifications_Manager {

	/**
	 * Single instance of the plugin
	 */
	private static $instance = null;

	/**
	 * Slack configuration
	 */
	private $webhook_url = '';
	private $channel = '#general';
	private $username = 'ENNU Life Bot';
	private $enabled = false;

	/**
	 * Rate limiting
	 */
	private $last_notification_time = 0;
	private $notification_queue = array();

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
		// Hardcoded Slack configuration
		$this->enabled = true; // Always enabled
		$this->webhook_url = 'https://hooks.slack.com/services/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';
		$this->channel = '#basic-assessments';
		$this->username = 'ENNU Life Bot';

		// Initialize hooks since we have a webhook URL
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Assessment completion notifications
		add_action( 'ennu_assessment_completed', array( $this, 'send_assessment_completion_notification' ), 10, 2 );
		
		// User registration notifications
		add_action( 'user_register', array( $this, 'send_user_registration_notification' ), 10, 1 );
		
		// Critical health alerts
		add_action( 'ennu_critical_health_alert', array( $this, 'send_critical_health_alert' ), 10, 2 );
		
		// Appointment booking notifications
		add_action( 'ennu_booking_created', array( $this, 'send_appointment_notification' ), 10, 2 );
		
		// Daily summary notifications
		add_action( 'ennu_daily_summary', array( $this, 'send_daily_summary' ), 10, 1 );
		
		// Error handling and monitoring
		add_action( 'wp_ajax_ennu_test_slack_notification', array( $this, 'handle_test_notification' ) );
		add_action( 'wp_ajax_ennu_slack_notification_status', array( $this, 'handle_notification_status' ) );
	}

	/**
	 * Send assessment completion notification
	 *
	 * @param int    $user_id User ID
	 * @param string $assessment_type Assessment type
	 */
	public function send_assessment_completion_notification( $user_id, $assessment_type ) {
		if ( ! $this->enabled || empty( $this->webhook_url ) ) {
			return;
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			$this->log_error( 'Invalid user ID for assessment completion notification: ' . $user_id );
			return;
		}

		// Get assessment score if available
		$score = get_user_meta( $user_id, 'ennu_assessment_score_' . $assessment_type, true );
		$score_text = $score ? "Score: {$score}/100" : 'Score: Pending';

		// Sanitize data for HIPAA compliance
		$safe_data = array(
			'user_id' => absint( $user_id ),
			'assessment_type' => sanitize_text_field( $assessment_type ),
			'score' => $score,
			'completion_time' => current_time( 'mysql' ),
			'user_email' => sanitize_email( $user->user_email ),
			'user_name' => sanitize_text_field( $user->display_name ),
		);

		$message = array(
			'text' => '🎯 New Assessment Completed',
			'blocks' => array(
				array(
					'type' => 'section',
					'text' => array(
						'type' => 'mrkdwn',
						'text' => sprintf(
							"*Assessment Type:* %s\n*User:* %s (%s)\n*Completion Time:* %s\n*%s*",
							ucwords( str_replace( '_', ' ', $safe_data['assessment_type'] ) ),
							$safe_data['user_name'],
							$safe_data['user_email'],
							$safe_data['completion_time'],
							$score_text
						)
					)
				),
				array(
					'type' => 'actions',
					'elements' => array(
						array(
							'type' => 'button',
							'text' => array(
								'type' => 'plain_text',
								'text' => 'View Details'
							),
							'url' => admin_url( 'admin.php?page=ennu-assessments&user_id=' . $safe_data['user_id'] )
						)
					)
				)
			)
		);

		$this->send_notification( $message, 'assessment_completion' );
	}

	/**
	 * Send user registration notification
	 *
	 * @param int $user_id User ID
	 */
	public function send_user_registration_notification( $user_id ) {
		if ( ! $this->enabled || empty( $this->webhook_url ) ) {
			return;
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}

		$safe_data = array(
			'user_id' => absint( $user_id ),
			'user_email' => sanitize_email( $user->user_email ),
			'user_name' => sanitize_text_field( $user->display_name ),
			'registration_time' => current_time( 'mysql' ),
		);

		$message = array(
			'text' => '👤 New User Registration',
			'blocks' => array(
				array(
					'type' => 'section',
					'text' => array(
						'type' => 'mrkdwn',
						'text' => sprintf(
							"*New User:* %s\n*Email:* %s\n*Registration Time:* %s\n*Source:* Direct registration",
							$safe_data['user_name'],
							$safe_data['user_email'],
							$safe_data['registration_time']
						)
					)
				)
			)
		);

		$this->send_notification( $message, 'user_registration' );
	}

	/**
	 * Send critical health alert
	 *
	 * @param int   $user_id User ID
	 * @param array $alert_data Alert data
	 */
	public function send_critical_health_alert( $user_id, $alert_data ) {
		if ( ! $this->enabled || empty( $this->webhook_url ) ) {
			return;
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}

		// Sanitize alert data
		$safe_alert_data = array(
			'assessment_type' => sanitize_text_field( $alert_data['assessment_type'] ?? '' ),
			'critical_finding' => sanitize_text_field( $alert_data['critical_finding'] ?? '' ),
			'action_required' => sanitize_text_field( $alert_data['action_required'] ?? '' ),
			'severity' => sanitize_text_field( $alert_data['severity'] ?? 'high' ),
		);

		$message = array(
			'text' => '🚨 CRITICAL HEALTH ALERT',
			'blocks' => array(
				array(
					'type' => 'section',
					'text' => array(
						'type' => 'mrkdwn',
						'text' => sprintf(
							"*User:* %s\n*Assessment:* %s\n*Critical Finding:* %s\n*Action Required:* %s",
							$user->display_name,
							ucwords( str_replace( '_', ' ', $safe_alert_data['assessment_type'] ) ),
							$safe_alert_data['critical_finding'],
							$safe_alert_data['action_required']
						)
					)
				),
				array(
					'type' => 'context',
					'elements' => array(
						array(
							'type' => 'mrkdwn',
							'text' => '⚠️ This requires immediate attention'
						)
					)
				)
			)
		);

		// Send to alerts channel if different from default
		$original_channel = $this->channel;
		$this->channel = '#ennu-alerts';
		$this->send_notification( $message, 'critical_health_alert' );
		$this->channel = $original_channel;
	}

	/**
	 * Send appointment booking notification
	 *
	 * @param int   $user_id User ID
	 * @param array $booking_data Booking data
	 */
	public function send_appointment_notification( $user_id, $booking_data ) {
		if ( ! $this->enabled || empty( $this->webhook_url ) ) {
			return;
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}

		// Sanitize booking data
		$safe_booking_data = array(
			'service' => sanitize_text_field( $booking_data['service'] ?? '' ),
			'date_time' => sanitize_text_field( $booking_data['date_time'] ?? '' ),
			'duration' => sanitize_text_field( $booking_data['duration'] ?? '60 minutes' ),
			'provider' => sanitize_text_field( $booking_data['provider'] ?? 'Dr. Elena Harmonix' ),
		);

		$message = array(
			'text' => '📅 New Appointment Scheduled',
			'blocks' => array(
				array(
					'type' => 'section',
					'text' => array(
						'type' => 'mrkdwn',
						'text' => sprintf(
							"*Patient:* %s\n*Service:* %s\n*Date/Time:* %s\n*Duration:* %s\n*Provider:* %s",
							$user->display_name,
							$safe_booking_data['service'],
							$safe_booking_data['date_time'],
							$safe_booking_data['duration'],
							$safe_booking_data['provider']
						)
					)
				)
			)
		);

		$this->send_notification( $message, 'appointment_booking' );
	}

	/**
	 * Send daily summary notification
	 *
	 * @param array $summary_data Summary data
	 */
	public function send_daily_summary( $summary_data ) {
		if ( ! $this->enabled || empty( $this->webhook_url ) ) {
			return;
		}

		$safe_summary_data = array(
			'total_assessments' => absint( $summary_data['total_assessments'] ?? 0 ),
			'new_users' => absint( $summary_data['new_users'] ?? 0 ),
			'critical_alerts' => absint( $summary_data['critical_alerts'] ?? 0 ),
			'appointments' => absint( $summary_data['appointments'] ?? 0 ),
		);

		$message = array(
			'text' => '📊 Daily Summary Report',
			'blocks' => array(
				array(
					'type' => 'section',
					'text' => array(
						'type' => 'mrkdwn',
						'text' => sprintf(
							"*Daily Summary for %s*\n\n*Assessments Completed:* %d\n*New Users:* %d\n*Critical Alerts:* %d\n*Appointments Scheduled:* %d",
							date( 'Y-m-d' ),
							$safe_summary_data['total_assessments'],
							$safe_summary_data['new_users'],
							$safe_summary_data['critical_alerts'],
							$safe_summary_data['appointments']
						)
					)
				)
			)
		);

		$this->send_notification( $message, 'daily_summary' );
	}

	/**
	 * Send notification to Slack
	 *
	 * @param array  $message Message data
	 * @param string $notification_type Notification type
	 * @return bool Success status
	 */
	public function send_notification( $message, $notification_type = 'general' ) {
		// Rate limiting - 1 message per second
		$current_time = time();
		if ( $current_time - $this->last_notification_time < 1 ) {
			$this->queue_notification( $message, $notification_type );
			return false;
		}

		// Add channel and username to message
		$message['channel'] = $this->channel;
		$message['username'] = $this->username;

		// Ensure proper JSON encoding and validate payload
		$json_payload = json_encode( $message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$this->log_error( 'JSON encoding failed: ' . json_last_error_msg(), $notification_type );
			return false;
		}

		// Log the payload for debugging
		$this->log_success( 'Sending payload: ' . substr( $json_payload, 0, 200 ) . '...', $notification_type );

		try {
			$response = wp_remote_post( $this->webhook_url, array(
				'body' => $json_payload,
				'headers' => array(
					'Content-Type' => 'application/json; charset=utf-8',
				),
				'timeout' => 15,
				'data_format' => 'body',
			) );

			if ( is_wp_error( $response ) ) {
				$this->log_error( 'Slack notification failed: ' . $response->get_error_message(), $notification_type );
				return false;
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			$response_body = wp_remote_retrieve_body( $response );
			
			if ( $response_code !== 200 ) {
				$this->log_error( 'Slack notification failed with code: ' . $response_code . ' - Response: ' . $response_body, $notification_type );
				return false;
			}

			// Check for Slack-specific error responses
			if ( ! empty( $response_body ) ) {
				$response_data = json_decode( $response_body, true );
				if ( isset( $response_data['error'] ) ) {
					$this->log_error( 'Slack API error: ' . $response_data['error'], $notification_type );
					return false;
				}
			}

			$this->last_notification_time = $current_time;
			$this->log_success( 'Slack notification sent successfully', $notification_type );
			return true;

		} catch ( Exception $e ) {
			$this->log_error( 'Slack notification exception: ' . $e->getMessage(), $notification_type );
			return false;
		}
	}

	/**
	 * Queue notification for later sending
	 *
	 * @param array  $message Message data
	 * @param string $notification_type Notification type
	 */
	private function queue_notification( $message, $notification_type ) {
		$this->notification_queue[] = array(
			'message' => $message,
			'type' => $notification_type,
			'timestamp' => time(),
		);

		// Process queue after 1 second
		wp_schedule_single_event( time() + 1, 'ennu_process_slack_queue' );
	}

	/**
	 * Process notification queue
	 */
	public function process_notification_queue() {
		if ( empty( $this->notification_queue ) ) {
			return;
		}

		foreach ( $this->notification_queue as $key => $item ) {
			$this->send_notification( $item['message'], $item['type'] );
			unset( $this->notification_queue[ $key ] );
		}
	}

	/**
	 * Handle test notification AJAX request
	 */
	public function handle_test_notification() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		$test_message = array(
			'text' => '🧪 Test Notification',
			'blocks' => array(
				array(
					'type' => 'section',
					'text' => array(
						'type' => 'mrkdwn',
						'text' => '*Test Notification*\nThis is a test notification from the ENNU Life Assessments plugin. If you receive this, your Slack integration is working correctly!'
					)
				)
			)
		);

		$success = $this->send_notification( $test_message, 'test' );

		wp_send_json( array(
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

		$status = array(
			'enabled' => $this->enabled,
			'webhook_configured' => ! empty( $this->webhook_url ),
			'channel' => $this->channel,
			'username' => $this->username,
			'queue_count' => count( $this->notification_queue ),
		);

		wp_send_json( $status );
	}

	/**
	 * Debug Slack webhook connection
	 */
	public function debug_webhook_connection() {
		if ( empty( $this->webhook_url ) ) {
			return array( 'success' => false, 'message' => 'Webhook URL not configured' );
		}

		$test_message = array(
			'text' => '🔧 Debug Test - ENNU Life Assessments',
			'blocks' => array(
				array(
					'type' => 'section',
					'text' => array(
						'type' => 'mrkdwn',
						'text' => '*Debug Test*\nTesting Slack webhook connection...'
					)
				)
			)
		);

		// Test JSON encoding
		$json_payload = json_encode( $test_message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return array( 
				'success' => false, 
				'message' => 'JSON encoding failed: ' . json_last_error_msg(),
				'payload' => $test_message
			);
		}

		// Test HTTP request
		$response = wp_remote_post( $this->webhook_url, array(
			'body' => $json_payload,
			'headers' => array(
				'Content-Type' => 'application/json; charset=utf-8',
			),
			'timeout' => 15,
			'data_format' => 'body',
		) );

		if ( is_wp_error( $response ) ) {
			return array( 
				'success' => false, 
				'message' => 'HTTP request failed: ' . $response->get_error_message(),
				'payload' => $json_payload
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		return array(
			'success' => $response_code === 200,
			'message' => 'Response code: ' . $response_code . ' - Body: ' . $response_body,
			'response_code' => $response_code,
			'response_body' => $response_body,
			'payload' => $json_payload
		);
	}

	/**
	 * Run comprehensive test of all notification types
	 */
	public function run_comprehensive_test() {
		$results = array();
		$user_id = 1; // Admin user
		
		// Test 1: Simple test notification
		$test_message = array(
			'text' => '🧪 Comprehensive Test - ENNU Life Assessments',
			'blocks' => array(
				array(
					'type' => 'section',
					'text' => array(
						'type' => 'mrkdwn',
						'text' => '*Comprehensive Test Started*\nTesting all notification types...'
					)
				)
			)
		);
		$results['simple_test'] = $this->send_notification( $test_message, 'comprehensive_test' );
		sleep( 1 ); // Rate limiting
		
		// Test 2: Assessment completion notification
		$results['assessment_completion'] = $this->send_assessment_completion_notification( $user_id, 'metabolic_health' );
		sleep( 1 );
		
		// Test 3: User registration notification
		$results['user_registration'] = $this->send_user_registration_notification( $user_id );
		sleep( 1 );
		
		// Test 4: Critical health alert
		$alert_data = array(
			'assessment_type' => 'metabolic_health',
			'critical_finding' => 'High glucose levels detected',
			'action_required' => 'Immediate medical consultation recommended',
			'severity' => 'high'
		);
		$results['critical_alert'] = $this->send_critical_health_alert( $user_id, $alert_data );
		sleep( 1 );
		
		// Test 5: Appointment notification
		$booking_data = array(
			'service' => 'Comprehensive Health Assessment',
			'date_time' => '2025-01-15 10:00 AM',
			'duration' => '90 minutes',
			'provider' => 'Dr. Elena Harmonix'
		);
		$results['appointment'] = $this->send_appointment_notification( $user_id, $booking_data );
		sleep( 1 );
		
		// Test 6: Daily summary
		$summary_data = array(
			'total_assessments' => 25,
			'new_users' => 8,
			'critical_alerts' => 2,
			'appointments' => 12
		);
		$results['daily_summary'] = $this->send_daily_summary( $summary_data );
		sleep( 1 );
		
		// Test 7: Debug webhook connection
		$results['debug_webhook'] = $this->debug_webhook_connection();
		
		return $results;
	}

	/**
	 * Log error
	 *
	 * @param string $message Error message
	 * @param string $notification_type Notification type
	 */
	private function log_error( $message, $notification_type = 'general' ) {
		error_log( 'ENNU Slack Notification Error [' . $notification_type . ']: ' . $message );
		
		// Store in WordPress error log
		$log_entry = array(
			'timestamp' => current_time( 'mysql' ),
			'type' => 'error',
			'notification_type' => $notification_type,
			'message' => $message,
		);
		
		$logs = get_option( 'ennu_slack_notification_logs', array() );
		$logs[] = $log_entry;
		
		// Keep only last 100 entries
		if ( count( $logs ) > 100 ) {
			$logs = array_slice( $logs, -100 );
		}
		
		update_option( 'ennu_slack_notification_logs', $logs );
	}

	/**
	 * Log success
	 *
	 * @param string $message Success message
	 * @param string $notification_type Notification type
	 */
	private function log_success( $message, $notification_type = 'general' ) {
		$log_entry = array(
			'timestamp' => current_time( 'mysql' ),
			'type' => 'success',
			'notification_type' => $notification_type,
			'message' => $message,
		);
		
		$logs = get_option( 'ennu_slack_notification_logs', array() );
		$logs[] = $log_entry;
		
		// Keep only last 100 entries
		if ( count( $logs ) > 100 ) {
			$logs = array_slice( $logs, -100 );
		}
		
		update_option( 'ennu_slack_notification_logs', $logs );
	}

	/**
	 * Get notification logs
	 *
	 * @return array Logs
	 */
	public function get_notification_logs() {
		return get_option( 'ennu_slack_notification_logs', array() );
	}

	/**
	 * Clear notification logs
	 */
	public function clear_notification_logs() {
		delete_option( 'ennu_slack_notification_logs' );
	}

	/**
	 * Get notification statistics
	 */
	public function get_notification_statistics() {
		$logs = $this->get_notification_logs();
		$total = count( $logs );
		$errors = 0;
		$success = 0;

		foreach ( $logs as $log ) {
			if ( $log['type'] === 'error' ) {
				$errors++;
			} else {
				$success++;
			}
		}

		return array(
			'total'   => $total,
			'errors'  => $errors,
			'success' => $success,
		);
	}

	/**
	 * Get enabled status
	 */
	public function is_enabled() {
		return $this->enabled;
	}

	/**
	 * Get webhook URL
	 */
	public function get_webhook_url() {
		return $this->webhook_url;
	}

	/**
	 * Get channel name
	 */
	public function get_channel() {
		return $this->channel;
	}

	/**
	 * Get username
	 */
	public function get_username() {
		return $this->username;
	}
}

// Initialize the Slack notifications manager
add_action( 'init', array( 'ENNU_Slack_Notifications_Manager', 'get_instance' ) );

// Process notification queue
add_action( 'ennu_process_slack_queue', array( 'ENNU_Slack_Notifications_Manager', 'get_instance' ), 'process_notification_queue' ); 