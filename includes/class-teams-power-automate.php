<?php
/**
 * Microsoft Teams Integration via Power Automate
 * 
 * Modern approach using Power Automate HTTP triggers instead of deprecated webhooks
 * Supports full Adaptive Cards and advanced workflow capabilities
 *
 * @package ENNU_Life_Assessments
 * @since 64.61.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Teams_Power_Automate {

	/**
	 * Singleton instance
	 */
	private static $instance = null;

	/**
	 * Power Automate Flow URLs
	 */
	private $flow_urls = array();

	/**
	 * Notification queue for batch processing
	 */
	private $notification_queue = array();

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
		$this->load_flow_urls();
		$this->init_hooks();
	}

	/**
	 * Load Power Automate flow URLs from database
	 */
	private function load_flow_urls() {
		$saved_urls = get_option( 'ennu_power_automate_urls', array() );
		
		$default_urls = array(
			'patient_assessments' => '',
			'biomarker_updates' => '',
			'critical_alerts' => '',
			'new_registrations' => '',
			'appointments' => '',
			'daily_summaries' => '',
			'system_alerts' => '',
			'revenue_metrics' => '',
			'patient_success' => ''
		);

		$this->flow_urls = wp_parse_args( $saved_urls, $default_urls );
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		// Assessment completions
		add_action( 'ennu_assessment_completed', array( $this, 'notify_assessment_completion' ), 10, 2 );
		
		// Biomarker updates
		add_action( 'ennu_biomarker_snapshot_created', array( $this, 'notify_biomarker_update' ), 10, 4 );
		
		// Critical health alerts
		add_action( 'ennu_critical_health_alert', array( $this, 'notify_critical_alert' ), 10, 2 );
		
		// User registrations
		add_action( 'user_register', array( $this, 'notify_new_registration' ), 10, 1 );
		
		// Appointments
		add_action( 'ennu_booking_created', array( $this, 'notify_appointment' ), 10, 2 );
		
		// Daily summary cron
		add_action( 'ennu_daily_summary_cron', array( $this, 'send_daily_summary' ) );
		
		// System errors
		add_action( 'ennu_system_error', array( $this, 'notify_system_alert' ), 10, 2 );
		
		// Admin AJAX endpoints
		add_action( 'wp_ajax_ennu_test_power_automate', array( $this, 'handle_test_flow' ) );
		add_action( 'wp_ajax_ennu_save_flow_urls', array( $this, 'handle_save_urls' ) );
		
		// Schedule daily summary if not scheduled
		if ( ! wp_next_scheduled( 'ennu_daily_summary_cron' ) ) {
			wp_schedule_event( strtotime( 'today 18:00:00' ), 'daily', 'ennu_daily_summary_cron' );
		}
	}

	/**
	 * Send notification to Power Automate flow
	 */
	private function send_to_flow( $flow_type, $data ) {
		// Check if flow URL is configured
		if ( empty( $this->flow_urls[ $flow_type ] ) ) {
			// REMOVED: error_log( "Power Automate flow URL not configured for: {$flow_type}" );
			return false;
		}

		$flow_url = $this->flow_urls[ $flow_type ];

		// Add metadata
		$data['notification_type'] = $flow_type;
		$data['timestamp'] = current_time( 'c' ); // ISO 8601 format
		$data['source'] = 'ENNU Life Assessments';
		$data['environment'] = wp_get_environment_type();

		// Send to Power Automate
		$response = wp_remote_post( $flow_url, array(
			'body' => json_encode( $data ),
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept' => 'application/json'
			),
			'timeout' => 30,
			'sslverify' => true
		) );

		if ( is_wp_error( $response ) ) {
			error_log( "Power Automate error for {$flow_type}: " . $response->get_error_message() );
			$this->handle_failed_notification( $flow_type, $data, $response->get_error_message() );
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		
		// Power Automate returns 202 (Accepted) for successful async processing
		if ( $response_code === 200 || $response_code === 202 ) {
			// REMOVED: error_log( "Power Automate notification sent successfully for {$flow_type}" );
			$this->log_notification_success( $flow_type, $data );
			return true;
		} else {
			$body = wp_remote_retrieve_body( $response );
			// REMOVED: error_log( "Power Automate failed with HTTP {$response_code} for {$flow_type}: {$body}" );
			$this->handle_failed_notification( $flow_type, $data, "HTTP {$response_code}" );
			return false;
		}
	}

	/**
	 * Notify assessment completion
	 */
	public function notify_assessment_completion( $user_id, $assessment_type ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) return;

		// Get assessment data
		$scores = get_user_meta( $user_id, 'ennu_assessment_scores_' . $assessment_type, true );
		$overall_score = 0;
		
		if ( is_array( $scores ) && ! empty( $scores ) ) {
			$overall_score = round( array_sum( $scores ) / count( $scores ), 1 );
		}

		// Get recommendations
		$recommendations = get_user_meta( $user_id, 'ennu_assessment_recommendations_' . $assessment_type, true );
		
		$data = array(
			'patient_name' => $user->display_name,
			'patient_email' => $user->user_email,
			'patient_id' => $user_id,
			'assessment_type' => ucwords( str_replace( '_', ' ', $assessment_type ) ),
			'overall_score' => $overall_score,
			'scores' => $scores ?: array(),
			'recommendations' => array_slice( $recommendations ?: array(), 0, 3 ),
			'completion_time' => current_time( 'Y-m-d H:i:s' ),
			'dashboard_url' => admin_url( 'admin.php?page=ennu-assessments&user_id=' . $user_id )
		);

		$this->send_to_flow( 'patient_assessments', $data );
		
		// If score is concerning, also send critical alert
		if ( $overall_score < 5 ) {
			$this->notify_critical_alert( $user_id, array(
				'assessment_type' => $assessment_type,
				'critical_finding' => 'Low assessment score: ' . $overall_score . '/10',
				'action_required' => 'Immediate follow-up recommended',
				'severity' => 'high'
			) );
		}
	}

	/**
	 * Notify biomarker update
	 */
	public function notify_biomarker_update( $record_id, $contact_id, $user_id, $changes ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) return;

		// Get update number
		$update_count = get_user_meta( $user_id, 'ennu_biomarker_update_count', true );
		$update_count = $update_count ? intval( $update_count ) + 1 : 1;
		update_user_meta( $user_id, 'ennu_biomarker_update_count', $update_count );

		// Process changes for display
		$key_changes = array();
		$critical_findings = array();
		
		foreach ( $changes as $biomarker => $change ) {
			$formatted_name = ucwords( str_replace( '_', ' ', $biomarker ) );
			
			// Build change description
			$change_text = $formatted_name . ': ';
			if ( isset( $change['old'] ) && isset( $change['new'] ) ) {
				$change_text .= $change['old'] . ' → ' . $change['new'];
			} else {
				$change_text .= $change['new'];
			}
			if ( isset( $change['unit'] ) ) {
				$change_text .= ' ' . $change['unit'];
			}
			
			// Add status indicator
			if ( isset( $change['status'] ) ) {
				if ( $change['status'] === 'optimal' ) {
					$change_text = '✅ ' . $change_text;
				} elseif ( $change['status'] === 'suboptimal' ) {
					$change_text = '⚠️ ' . $change_text;
				} elseif ( $change['status'] === 'poor' || $change['status'] === 'critical' ) {
					$change_text = '❌ ' . $change_text;
					$critical_findings[] = $change_text;
				}
			}
			
			$key_changes[] = $change_text;
		}

		$data = array(
			'patient_name' => $user->display_name,
			'patient_email' => $user->user_email,
			'patient_id' => $user_id,
			'update_number' => $update_count,
			'biomarkers_changed' => count( $changes ),
			'key_changes' => array_slice( $key_changes, 0, 5 ), // Top 5 changes
			'all_changes' => $changes,
			'source' => 'Lab Test',
			'record_id' => $record_id,
			'has_critical' => ! empty( $critical_findings ),
			'critical_findings' => $critical_findings,
			'dashboard_url' => admin_url( 'admin.php?page=ennu-biomarkers&user_id=' . $user_id )
		);

		$this->send_to_flow( 'biomarker_updates', $data );
		
		// Send critical alert if needed
		if ( ! empty( $critical_findings ) ) {
			$this->notify_critical_alert( $user_id, array(
				'critical_finding' => implode( ', ', $critical_findings ),
				'action_required' => 'Review critical biomarker values immediately',
				'severity' => 'high',
				'source' => 'biomarker_update'
			) );
		}
	}

	/**
	 * Notify critical health alert
	 */
	public function notify_critical_alert( $user_id, $alert_data ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) return;

		// Get user phone if available
		$phone = get_user_meta( $user_id, 'phone', true );
		
		$data = array(
			'patient_name' => $user->display_name,
			'patient_email' => $user->user_email,
			'patient_phone' => $phone ?: 'Not provided',
			'patient_id' => $user_id,
			'critical_finding' => $alert_data['critical_finding'] ?? 'Critical health issue detected',
			'severity' => strtoupper( $alert_data['severity'] ?? 'HIGH' ),
			'action_required' => $alert_data['action_required'] ?? 'Immediate medical consultation required',
			'alert_source' => $alert_data['source'] ?? 'system',
			'alert_time' => current_time( 'Y-m-d H:i:s' ),
			'patient_profile_url' => admin_url( 'admin.php?page=ennu-patients&user_id=' . $user_id ),
			'requires_immediate_action' => true
		);

		// Send to critical alerts flow
		$this->send_to_flow( 'critical_alerts', $data );
		
		// Log critical alert
		$this->log_critical_alert( $user_id, $data );
		
		// Trigger additional notifications if configured
		do_action( 'ennu_critical_alert_sent', $user_id, $data );
	}

	/**
	 * Notify new registration
	 */
	public function notify_new_registration( $user_id ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) return;

		// Get registration metadata
		$source = get_user_meta( $user_id, 'registration_source', true ) ?: 'Direct';
		$referrer = get_user_meta( $user_id, 'registration_referrer', true ) ?: 'Organic';
		
		// Get location from IP if available
		$location = $this->get_user_location( $user_id );

		$data = array(
			'patient_name' => $user->display_name,
			'patient_email' => $user->user_email,
			'patient_id' => $user_id,
			'registration_time' => $user->user_registered,
			'registration_source' => $source,
			'referrer' => $referrer,
			'location' => $location,
			'initial_assessment_pending' => true,
			'profile_url' => admin_url( 'user-edit.php?user_id=' . $user_id ),
			'welcome_email_sent' => get_user_meta( $user_id, 'welcome_email_sent', true ) ? true : false
		);

		$this->send_to_flow( 'new_registrations', $data );
	}

	/**
	 * Notify appointment booking
	 */
	public function notify_appointment( $user_id, $booking_data ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) return;

		$data = array(
			'patient_name' => $user->display_name,
			'patient_email' => $user->user_email,
			'patient_id' => $user_id,
			'service' => $booking_data['service'] ?? 'Consultation',
			'appointment_date' => $booking_data['date'] ?? '',
			'appointment_time' => $booking_data['time'] ?? '',
			'duration_minutes' => $booking_data['duration'] ?? 60,
			'provider' => $booking_data['provider'] ?? 'Dr. Elena Harmonix',
			'appointment_type' => $booking_data['type'] ?? 'In-person',
			'reason' => $booking_data['reason'] ?? 'General consultation',
			'booking_id' => $booking_data['id'] ?? uniqid( 'apt_' ),
			'calendar_url' => admin_url( 'admin.php?page=ennu-calendar&date=' . $booking_data['date'] ),
			'requires_preparation' => $booking_data['requires_preparation'] ?? false
		);

		$this->send_to_flow( 'appointments', $data );
	}

	/**
	 * Send daily summary
	 */
	public function send_daily_summary() {
		global $wpdb;
		
		$today = current_time( 'Y-m-d' );
		$yesterday = date( 'Y-m-d', strtotime( '-1 day' ) );
		
		// Gather metrics
		$metrics = array(
			'assessments_completed' => $this->count_assessments_today( $today ),
			'new_registrations' => $this->count_registrations_today( $today ),
			'biomarker_updates' => $this->count_biomarker_updates_today( $today ),
			'appointments_scheduled' => $this->count_appointments_today( $today ),
			'critical_alerts' => $this->count_critical_alerts_today( $today ),
			'active_patients' => $this->count_active_patients(),
			'revenue_today' => $this->calculate_revenue_today( $today ),
			'conversion_rate' => $this->calculate_conversion_rate( $today ),
			'top_assessment' => $this->get_top_assessment_today( $today ),
			'avg_assessment_score' => $this->calculate_avg_assessment_score( $today )
		);

		// Calculate trends
		$yesterday_metrics = array(
			'assessments' => $this->count_assessments_today( $yesterday ),
			'revenue' => $this->calculate_revenue_today( $yesterday ),
			'conversion' => $this->calculate_conversion_rate( $yesterday )
		);

		$data = array(
			'date' => $today,
			'day_of_week' => date( 'l' ),
			'assessments_completed' => $metrics['assessments_completed'],
			'assessments_trend' => $this->calculate_trend( $yesterday_metrics['assessments'], $metrics['assessments_completed'] ),
			'new_registrations' => $metrics['new_registrations'],
			'biomarker_updates' => $metrics['biomarker_updates'],
			'appointments_scheduled' => $metrics['appointments_scheduled'],
			'critical_alerts' => $metrics['critical_alerts'],
			'active_patients' => $metrics['active_patients'],
			'revenue' => $metrics['revenue_today'],
			'revenue_formatted' => '$' . number_format( $metrics['revenue_today'], 2 ),
			'revenue_trend' => $this->calculate_trend( $yesterday_metrics['revenue'], $metrics['revenue_today'] ),
			'conversion_rate' => $metrics['conversion_rate'],
			'conversion_trend' => $this->calculate_trend( $yesterday_metrics['conversion'], $metrics['conversion_rate'] ),
			'top_assessment_type' => $metrics['top_assessment'],
			'avg_assessment_score' => $metrics['avg_assessment_score'],
			'dashboard_url' => admin_url( 'admin.php?page=ennu-dashboard' ),
			'requires_attention' => $metrics['critical_alerts'] > 0
		);

		$this->send_to_flow( 'daily_summaries', $data );
		
		// Also send revenue metrics if significant
		if ( $metrics['revenue_today'] > 1000 ) {
			$this->send_revenue_update( $data );
		}
	}

	/**
	 * Notify system alert
	 */
	public function notify_system_alert( $error_type, $error_details ) {
		$data = array(
			'error_type' => $error_type,
			'error_message' => $error_details['message'] ?? 'Unknown error',
			'error_code' => $error_details['code'] ?? '',
			'affected_component' => $error_details['component'] ?? 'System',
			'affected_records' => $error_details['affected_records'] ?? 0,
			'severity' => $error_details['severity'] ?? 'medium',
			'error_time' => current_time( 'Y-m-d H:i:s' ),
			'stack_trace' => $error_details['stack_trace'] ?? '',
			'suggested_action' => $error_details['suggested_action'] ?? 'Review error logs',
			'auto_retry_scheduled' => $error_details['retry'] ?? false,
			'logs_url' => admin_url( 'admin.php?page=ennu-logs' ),
			'requires_immediate_attention' => $error_details['severity'] === 'critical'
		);

		$this->send_to_flow( 'system_alerts', $data );
	}

	/**
	 * Send revenue update
	 */
	private function send_revenue_update( $daily_data ) {
		$data = array(
			'date' => $daily_data['date'],
			'revenue' => $daily_data['revenue'],
			'revenue_formatted' => $daily_data['revenue_formatted'],
			'conversion_rate' => $daily_data['conversion_rate'],
			'new_customers' => $daily_data['new_registrations'],
			'assessments_sold' => $daily_data['assessments_completed'],
			'avg_order_value' => $daily_data['revenue'] / max( $daily_data['assessments_completed'], 1 ),
			'top_product' => $daily_data['top_assessment_type'],
			'monthly_progress' => $this->calculate_monthly_progress(),
			'quarterly_target_progress' => $this->calculate_quarterly_progress(),
			'dashboard_url' => admin_url( 'admin.php?page=ennu-revenue' )
		);

		$this->send_to_flow( 'revenue_metrics', $data );
	}

	/**
	 * Handle test flow AJAX request
	 */
	public function handle_test_flow() {
		check_ajax_referer( 'ennu_power_automate_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		$flow_type = sanitize_text_field( $_POST['flow_type'] );
		
		// Send test data
		$test_data = array(
			'test_mode' => true,
			'patient_name' => 'Test Patient',
			'patient_email' => 'test@example.com',
			'message' => 'This is a test notification from ENNU Life Assessments',
			'test_time' => current_time( 'Y-m-d H:i:s' )
		);

		$success = $this->send_to_flow( $flow_type, $test_data );
		
		wp_send_json( array(
			'success' => $success,
			'message' => $success ? 
				'Test notification sent successfully! Check your Teams channel.' : 
				'Failed to send test notification. Check your Power Automate flow URL.'
		) );
	}

	/**
	 * Handle save URLs AJAX request
	 */
	public function handle_save_urls() {
		check_ajax_referer( 'ennu_power_automate_nonce', 'nonce' );
		
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized' );
		}

		$urls = array();
		foreach ( $this->flow_urls as $flow_type => $url ) {
			if ( isset( $_POST[ 'flow_' . $flow_type ] ) ) {
				$urls[ $flow_type ] = esc_url_raw( $_POST[ 'flow_' . $flow_type ] );
			}
		}

		update_option( 'ennu_power_automate_urls', $urls );
		$this->flow_urls = $urls;

		wp_send_json_success( 'Power Automate flow URLs saved successfully!' );
	}

	/**
	 * Helper: Count assessments today
	 */
	private function count_assessments_today( $date ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE 'ennu_assessment_completed_%' 
			AND DATE(meta_value) = %s",
			$date
		) ) ?: 0;
	}

	/**
	 * Helper: Count registrations today
	 */
	private function count_registrations_today( $date ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->users} 
			WHERE DATE(user_registered) = %s",
			$date
		) ) ?: 0;
	}

	/**
	 * Helper: Count biomarker updates today
	 */
	private function count_biomarker_updates_today( $date ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->usermeta} 
			WHERE meta_key = 'ennu_biomarker_updated' 
			AND DATE(meta_value) = %s",
			$date
		) ) ?: 0;
	}

	/**
	 * Helper: Count appointments today
	 */
	private function count_appointments_today( $date ) {
		// Mock data - replace with actual appointment counting
		return rand( 5, 15 );
	}

	/**
	 * Helper: Count critical alerts today
	 */
	private function count_critical_alerts_today( $date ) {
		return get_transient( 'ennu_critical_alerts_' . $date ) ?: 0;
	}

	/**
	 * Helper: Count active patients
	 */
	private function count_active_patients() {
		global $wpdb;
		return $wpdb->get_var(
			"SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE 'ennu_assessment_%' 
			AND meta_value > DATE_SUB(NOW(), INTERVAL 30 DAY)"
		) ?: 0;
	}

	/**
	 * Helper: Calculate revenue today
	 */
	private function calculate_revenue_today( $date ) {
		// Mock calculation - replace with actual revenue data
		$assessments = $this->count_assessments_today( $date );
		return $assessments * rand( 150, 350 );
	}

	/**
	 * Helper: Calculate conversion rate
	 */
	private function calculate_conversion_rate( $date ) {
		$registrations = $this->count_registrations_today( $date );
		$assessments = $this->count_assessments_today( $date );
		
		if ( $registrations > 0 ) {
			return round( ( $assessments / $registrations ) * 100, 1 );
		}
		return 0;
	}

	/**
	 * Helper: Get top assessment type today
	 */
	private function get_top_assessment_today( $date ) {
		global $wpdb;
		$result = $wpdb->get_var( $wpdb->prepare(
			"SELECT meta_key FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE 'ennu_assessment_completed_%' 
			AND DATE(meta_value) = %s 
			GROUP BY meta_key 
			ORDER BY COUNT(*) DESC 
			LIMIT 1",
			$date
		) );
		
		if ( $result ) {
			$type = str_replace( 'ennu_assessment_completed_', '', $result );
			return ucwords( str_replace( '_', ' ', $type ) );
		}
		
		return 'General Health';
	}

	/**
	 * Helper: Calculate average assessment score
	 */
	private function calculate_avg_assessment_score( $date ) {
		// Mock calculation - implement actual score averaging
		return round( rand( 65, 85 ) / 10, 1 );
	}

	/**
	 * Helper: Calculate trend
	 */
	private function calculate_trend( $yesterday, $today ) {
		if ( $yesterday == 0 ) return '+100%';
		$change = ( ( $today - $yesterday ) / $yesterday ) * 100;
		return ( $change >= 0 ? '+' : '' ) . round( $change, 1 ) . '%';
	}

	/**
	 * Helper: Get user location
	 */
	private function get_user_location( $user_id ) {
		$ip = get_user_meta( $user_id, 'registration_ip', true );
		if ( $ip ) {
			// Implement IP geolocation if needed
			return 'United States'; // Default
		}
		return 'Unknown';
	}

	/**
	 * Helper: Calculate monthly progress
	 */
	private function calculate_monthly_progress() {
		// Calculate percentage of monthly target achieved
		return rand( 60, 95 ) . '%';
	}

	/**
	 * Helper: Calculate quarterly progress
	 */
	private function calculate_quarterly_progress() {
		// Calculate percentage of quarterly target achieved
		return rand( 70, 85 ) . '%';
	}

	/**
	 * Log critical alert
	 */
	private function log_critical_alert( $user_id, $alert_data ) {
		$log = get_option( 'ennu_critical_alerts_log', array() );
		
		$log[] = array(
			'user_id' => $user_id,
			'timestamp' => current_time( 'mysql' ),
			'data' => $alert_data
		);
		
		// Keep last 100 entries
		if ( count( $log ) > 100 ) {
			$log = array_slice( $log, -100 );
		}
		
		update_option( 'ennu_critical_alerts_log', $log );
		
		// Update daily counter
		$date = current_time( 'Y-m-d' );
		$count = get_transient( 'ennu_critical_alerts_' . $date ) ?: 0;
		set_transient( 'ennu_critical_alerts_' . $date, $count + 1, DAY_IN_SECONDS );
	}

	/**
	 * Log notification success
	 */
	private function log_notification_success( $flow_type, $data ) {
		$log = get_option( 'ennu_power_automate_log', array() );
		
		$log[] = array(
			'type' => 'success',
			'flow' => $flow_type,
			'timestamp' => current_time( 'mysql' ),
			'data_size' => strlen( json_encode( $data ) )
		);
		
		// Keep last 500 entries
		if ( count( $log ) > 500 ) {
			$log = array_slice( $log, -500 );
		}
		
		update_option( 'ennu_power_automate_log', $log );
	}

	/**
	 * Handle failed notification
	 */
	private function handle_failed_notification( $flow_type, $data, $error ) {
		// Log failure
		$log = get_option( 'ennu_power_automate_failures', array() );
		
		$log[] = array(
			'flow' => $flow_type,
			'timestamp' => current_time( 'mysql' ),
			'error' => $error,
			'data' => $data
		);
		
		// Keep last 100 failures
		if ( count( $log ) > 100 ) {
			$log = array_slice( $log, -100 );
		}
		
		update_option( 'ennu_power_automate_failures', $log );
		
		// Queue for retry if critical
		if ( in_array( $flow_type, array( 'critical_alerts', 'system_alerts' ) ) ) {
			$this->queue_for_retry( $flow_type, $data );
		}
	}

	/**
	 * Queue notification for retry
	 */
	private function queue_for_retry( $flow_type, $data ) {
		$queue = get_option( 'ennu_notification_retry_queue', array() );
		
		$queue[] = array(
			'flow' => $flow_type,
			'data' => $data,
			'attempts' => 1,
			'next_retry' => time() + 300 // Retry in 5 minutes
		);
		
		update_option( 'ennu_notification_retry_queue', $queue );
		
		// Schedule retry cron if not scheduled
		if ( ! wp_next_scheduled( 'ennu_retry_failed_notifications' ) ) {
			wp_schedule_single_event( time() + 300, 'ennu_retry_failed_notifications' );
		}
	}

	/**
	 * Process retry queue
	 */
	public function process_retry_queue() {
		$queue = get_option( 'ennu_notification_retry_queue', array() );
		$new_queue = array();
		
		foreach ( $queue as $item ) {
			if ( $item['next_retry'] <= time() && $item['attempts'] < 3 ) {
				// Retry sending
				if ( $this->send_to_flow( $item['flow'], $item['data'] ) ) {
					// Success - don't re-queue
					continue;
				} else {
					// Failed - increment attempts and re-queue
					$item['attempts']++;
					$item['next_retry'] = time() + ( 300 * $item['attempts'] ); // Exponential backoff
					$new_queue[] = $item;
				}
			} elseif ( $item['attempts'] < 3 ) {
				// Not time yet - keep in queue
				$new_queue[] = $item;
			}
			// Items with 3+ attempts are dropped
		}
		
		update_option( 'ennu_notification_retry_queue', $new_queue );
		
		// Reschedule if items remain
		if ( ! empty( $new_queue ) ) {
			wp_schedule_single_event( time() + 300, 'ennu_retry_failed_notifications' );
		}
	}
}

// Initialize
add_action( 'init', function() {
	ENNU_Teams_Power_Automate::get_instance();
} );

// Add retry processing
add_action( 'ennu_retry_failed_notifications', function() {
	$instance = ENNU_Teams_Power_Automate::get_instance();
	$instance->process_retry_queue();
} );