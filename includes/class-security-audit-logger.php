<?php
/**
 * Security Audit Logger
 *
 * Comprehensive security event logging and monitoring system.
 * Tracks all security-related events for compliance and forensic analysis.
 *
 * @package ENNU_Life_Assessments
 * @since 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Security_Audit_Logger {

	private $log_levels       = array();
	private $monitored_events = array();
	private $retention_days   = 90;

	public function __construct() {
		$this->init_log_levels();
		$this->init_monitored_events();
		$this->setup_hooks();
	}

	/**
	 * Initialize log levels
	 */
	private function init_log_levels() {
		$this->log_levels = array(
			'CRITICAL' => 1,
			'HIGH'     => 2,
			'MEDIUM'   => 3,
			'LOW'      => 4,
			'INFO'     => 5,
		);
	}

	/**
	 * Initialize monitored events
	 */
	private function init_monitored_events() {
		$this->monitored_events = array(
			'user_login'               => 'INFO',
			'user_logout'              => 'INFO',
			'failed_login'             => 'MEDIUM',
			'password_reset'           => 'MEDIUM',
			'user_registration'        => 'INFO',

			'user_role_changed'        => 'HIGH',
			'plugin_activated'         => 'HIGH',
			'plugin_deactivated'       => 'HIGH',
			'theme_switched'           => 'HIGH',
			'option_updated'           => 'MEDIUM',

			'security_threat_detected' => 'CRITICAL',
			'ip_blocked'               => 'HIGH',
			'rate_limit_exceeded'      => 'MEDIUM',
			'suspicious_activity'      => 'HIGH',
			'csrf_token_mismatch'      => 'HIGH',
			'xss_attempt_blocked'      => 'HIGH',
			'sql_injection_blocked'    => 'CRITICAL',

			'sensitive_data_accessed'  => 'MEDIUM',
			'data_export'              => 'HIGH',
			'data_deletion'            => 'HIGH',
			'assessment_submitted'     => 'INFO',
			'health_data_updated'      => 'MEDIUM',

			'file_modification'        => 'HIGH',
			'database_error'           => 'MEDIUM',
			'backup_created'           => 'INFO',
			'backup_restored'          => 'HIGH',
		);
	}

	/**
	 * Setup WordPress hooks
	 */
	private function setup_hooks() {
		add_action( 'wp_login', array( $this, 'log_user_login' ), 10, 2 );
		add_action( 'wp_logout', array( $this, 'log_user_logout' ) );
		add_action( 'wp_login_failed', array( $this, 'log_failed_login' ) );
		add_action( 'password_reset', array( $this, 'log_password_reset' ), 10, 2 );
		add_action( 'user_register', array( $this, 'log_user_registration' ) );

		add_action( 'set_user_role', array( $this, 'log_role_change' ), 10, 3 );
		add_action( 'activated_plugin', array( $this, 'log_plugin_activation' ) );
		add_action( 'deactivated_plugin', array( $this, 'log_plugin_deactivation' ) );
		add_action( 'switch_theme', array( $this, 'log_theme_switch' ), 10, 3 );
		add_action( 'updated_option', array( $this, 'log_option_update' ), 10, 3 );

		add_action( 'ennu_security_threat_detected', array( $this, 'log_security_threat' ), 10, 2 );
		add_action( 'ennu_ip_blocked', array( $this, 'log_ip_blocked' ), 10, 2 );
		add_action( 'ennu_rate_limit_exceeded', array( $this, 'log_rate_limit' ), 10, 2 );
		add_action( 'ennu_suspicious_activity', array( $this, 'log_suspicious_activity' ), 10, 2 );

		add_action( 'ennu_sensitive_data_accessed', array( $this, 'log_data_access' ), 10, 2 );
		add_action( 'ennu_data_exported', array( $this, 'log_data_export' ), 10, 2 );
		add_action( 'ennu_assessment_submitted', array( $this, 'log_assessment_submission' ), 10, 2 );

		add_action( 'wp_scheduled_delete', array( $this, 'cleanup_old_logs' ) );

		if ( ! wp_next_scheduled( 'wp_scheduled_delete' ) ) {
			wp_schedule_event( time(), 'daily', 'wp_scheduled_delete' );
		}
	}

	/**
	 * Log security event
	 */
	public function log_event( $event_type, $message, $data = array(), $level = null ) {
		if ( $level === null ) {
			$level = $this->monitored_events[ $event_type ] ?? 'INFO';
		}

		$log_entry = array(
			'timestamp'   => current_time( 'mysql' ),
			'event_type'  => $event_type,
			'level'       => $level,
			'message'     => $message,
			'user_id'     => get_current_user_id(),
			'user_ip'     => $this->get_client_ip(),
			'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? '',
			'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
			'referer'     => $_SERVER['HTTP_REFERER'] ?? '',
			'session_id'  => session_id(),
			'data'        => $data,
		);

		// Store in database
		$this->store_log_entry( $log_entry );

		if ( $level === 'CRITICAL' ) {
			$this->send_security_alert( $log_entry );
		}

		if ( in_array( $level, array( 'CRITICAL', 'HIGH' ) ) ) {
			// REMOVED: error_log( "ENNU Security Audit [{$level}] {$event_type}: {$message}" );
		}
	}

	/**
	 * Store log entry in database
	 */
	private function store_log_entry( $log_entry ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_audit_log';

		$result = $wpdb->insert(
			$table_name,
			array(
				'timestamp'   => $log_entry['timestamp'],
				'event_type'  => $log_entry['event_type'],
				'level'       => $log_entry['level'],
				'message'     => $log_entry['message'],
				'user_id'     => $log_entry['user_id'],
				'user_ip'     => $log_entry['user_ip'],
				'user_agent'  => $log_entry['user_agent'],
				'request_uri' => $log_entry['request_uri'],
				'referer'     => $log_entry['referer'],
				'session_id'  => $log_entry['session_id'],
				'event_data'  => json_encode( $log_entry['data'] ),
			),
			array( '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( $result === false ) {
			// REMOVED: error_log( 'Failed to store audit log entry: ' . $wpdb->last_error );
		}
	}

	/**
	 * Send security alert
	 */
	private function send_security_alert( $log_entry ) {
		$admin_email = get_option( 'admin_email' );
		$site_name   = get_bloginfo( 'name' );

		$subject = "[{$site_name}] Critical Security Alert: {$log_entry['event_type']}";

		$message  = "A critical security event has been detected on your website.\n\n";
		$message .= "Event Type: {$log_entry['event_type']}\n";
		$message .= "Level: {$log_entry['level']}\n";
		$message .= "Message: {$log_entry['message']}\n";
		$message .= "Time: {$log_entry['timestamp']}\n";
		$message .= "User ID: {$log_entry['user_id']}\n";
		$message .= "IP Address: {$log_entry['user_ip']}\n";
		$message .= "User Agent: {$log_entry['user_agent']}\n";
		$message .= "Request URI: {$log_entry['request_uri']}\n\n";
		$message .= "Please investigate this incident immediately.\n\n";
		$message .= 'Event Data: ' . json_encode( $log_entry['data'], JSON_PRETTY_PRINT );

		wp_mail( $admin_email, $subject, $message );
	}

	/**
	 * Get client IP address
	 */
	private function get_client_ip() {
		$ip_headers = array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $ip_headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = $_SERVER[ $header ];
				if ( strpos( $ip, ',' ) !== false ) {
					$ip = trim( explode( ',', $ip )[0] );
				}
				if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
					return $ip;
				}
			}
		}

		return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
	}

	/**
	 * Log user login
	 */
	public function log_user_login( $user_login, $user ) {
		$this->log_event(
			'user_login',
			"User {$user_login} logged in successfully",
			array(
				'user_id'    => $user->ID,
				'user_login' => $user_login,
				'user_email' => $user->user_email,
			)
		);
	}

	/**
	 * Log user logout
	 */
	public function log_user_logout() {
		$user = wp_get_current_user();
		if ( $user->ID ) {
			$this->log_event(
				'user_logout',
				"User {$user->user_login} logged out",
				array(
					'user_id'    => $user->ID,
					'user_login' => $user->user_login,
				)
			);
		}
	}

	/**
	 * Log failed login
	 */
	public function log_failed_login( $username ) {
		$this->log_event(
			'failed_login',
			"Failed login attempt for username: {$username}",
			array(
				'username' => $username,
				'ip'       => $this->get_client_ip(),
			)
		);
	}

	/**
	 * Log password reset
	 */
	public function log_password_reset( $user, $new_pass ) {
		$this->log_event(
			'password_reset',
			"Password reset for user: {$user->user_login}",
			array(
				'user_id'    => $user->ID,
				'user_login' => $user->user_login,
				'user_email' => $user->user_email,
			)
		);
	}

	/**
	 * Log user registration
	 */
	public function log_user_registration( $user_id ) {
		$user = get_user_by( 'ID', $user_id );
		$this->log_event(
			'user_registration',
			"New user registered: {$user->user_login}",
			array(
				'user_id'    => $user_id,
				'user_login' => $user->user_login,
				'user_email' => $user->user_email,
			)
		);
	}

	/**
	 * Log role change
	 */
	public function log_role_change( $user_id, $role, $old_roles ) {
		$user = get_user_by( 'ID', $user_id );
		$this->log_event(
			'user_role_changed',
			"User role changed for {$user->user_login}",
			array(
				'user_id'    => $user_id,
				'user_login' => $user->user_login,
				'new_role'   => $role,
				'old_roles'  => $old_roles,
			)
		);
	}

	/**
	 * Log plugin activation
	 */
	public function log_plugin_activation( $plugin ) {
		$this->log_event(
			'plugin_activated',
			"Plugin activated: {$plugin}",
			array(
				'plugin' => $plugin,
			)
		);
	}

	/**
	 * Log plugin deactivation
	 */
	public function log_plugin_deactivation( $plugin ) {
		$this->log_event(
			'plugin_deactivated',
			"Plugin deactivated: {$plugin}",
			array(
				'plugin' => $plugin,
			)
		);
	}

	/**
	 * Log theme switch
	 */
	public function log_theme_switch( $new_name, $new_theme, $old_theme ) {
		$this->log_event(
			'theme_switched',
			"Theme switched to: {$new_name}",
			array(
				'new_theme' => $new_name,
				'old_theme' => $old_theme->get( 'Name' ),
			)
		);
	}

	/**
	 * Log option updates
	 */
	public function log_option_update( $option, $old_value, $value ) {
		$monitored_options = array(
			'admin_email',
			'users_can_register',
			'default_role',
			'active_plugins',
			'stylesheet',
			'template',
		);

		if ( in_array( $option, $monitored_options ) ) {
			$this->log_event(
				'option_updated',
				"Option updated: {$option}",
				array(
					'option'    => $option,
					'old_value' => $old_value,
					'new_value' => $value,
				)
			);
		}
	}

	/**
	 * Log security threat
	 */
	public function log_security_threat( $threat_type, $data ) {
		$this->log_event( 'security_threat_detected', "Security threat detected: {$threat_type}", $data );
	}

	/**
	 * Log IP blocked
	 */
	public function log_ip_blocked( $ip, $reason ) {
		$this->log_event(
			'ip_blocked',
			"IP address blocked: {$ip}",
			array(
				'ip'     => $ip,
				'reason' => $reason,
			)
		);
	}

	/**
	 * Log rate limit exceeded
	 */
	public function log_rate_limit( $ip, $limit_type ) {
		$this->log_event(
			'rate_limit_exceeded',
			"Rate limit exceeded for IP: {$ip}",
			array(
				'ip'         => $ip,
				'limit_type' => $limit_type,
			)
		);
	}

	/**
	 * Log suspicious activity
	 */
	public function log_suspicious_activity( $activity_type, $data ) {
		$this->log_event( 'suspicious_activity', "Suspicious activity detected: {$activity_type}", $data );
	}

	/**
	 * Log data access
	 */
	public function log_data_access( $data_type, $data ) {
		$this->log_event( 'sensitive_data_accessed', "Sensitive data accessed: {$data_type}", $data );
	}

	/**
	 * Log data export
	 */
	public function log_data_export( $export_type, $data ) {
		$this->log_event( 'data_exported', "Data exported: {$export_type}", $data );
	}

	/**
	 * Log assessment submission
	 */
	public function log_assessment_submission( $assessment_type, $data ) {
		$this->log_event( 'assessment_submitted', "Assessment submitted: {$assessment_type}", $data );
	}

	/**
	 * Cleanup old logs
	 */
	public function cleanup_old_logs() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_audit_log';

		$cutoff_date = date( 'Y-m-d H:i:s', strtotime( "-{$this->retention_days} days" ) );

		$deleted = $wpdb->query(
			$wpdb->prepare(
				"
            DELETE FROM {$table_name} 
            WHERE timestamp < %s
        ",
				$cutoff_date
			)
		);

		if ( $deleted !== false ) {
			$this->log_event(
				'log_cleanup',
				"Cleaned up {$deleted} old audit log entries",
				array(
					'deleted_count' => $deleted,
					'cutoff_date'   => $cutoff_date,
				)
			);
		}
	}

	/**
	 * Get audit log entries
	 */
	public function get_audit_logs( $filters = array(), $limit = 100, $offset = 0 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ennu_audit_log';

		$where_clauses = array();
		$where_values  = array();

		if ( ! empty( $filters['event_type'] ) ) {
			$where_clauses[] = 'event_type = %s';
			$where_values[]  = $filters['event_type'];
		}

		if ( ! empty( $filters['level'] ) ) {
			$where_clauses[] = 'level = %s';
			$where_values[]  = $filters['level'];
		}

		if ( ! empty( $filters['user_id'] ) ) {
			$where_clauses[] = 'user_id = %d';
			$where_values[]  = $filters['user_id'];
		}

		if ( ! empty( $filters['ip_address'] ) ) {
			$where_clauses[] = 'user_ip = %s';
			$where_values[]  = $filters['ip_address'];
		}

		if ( ! empty( $filters['date_from'] ) ) {
			$where_clauses[] = 'timestamp >= %s';
			$where_values[]  = $filters['date_from'];
		}

		if ( ! empty( $filters['date_to'] ) ) {
			$where_clauses[] = 'timestamp <= %s';
			$where_values[]  = $filters['date_to'];
		}

		$where_sql = '';
		if ( ! empty( $where_clauses ) ) {
			$where_sql = 'WHERE ' . implode( ' AND ', $where_clauses );
		}

		$sql            = "SELECT * FROM {$table_name} {$where_sql} ORDER BY timestamp DESC LIMIT %d OFFSET %d";
		$where_values[] = $limit;
		$where_values[] = $offset;

		return $wpdb->get_results( $wpdb->prepare( $sql, $where_values ) );
	}

	/**
	 * Create audit log table
	 */
	public static function create_audit_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'ennu_audit_log';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            event_type varchar(50) NOT NULL,
            level varchar(20) NOT NULL,
            message text NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            user_ip varchar(45) NOT NULL,
            user_agent text,
            request_uri text,
            referer text,
            session_id varchar(128),
            event_data longtext,
            PRIMARY KEY (id),
            KEY event_type (event_type),
            KEY level (level),
            KEY user_id (user_id),
            KEY user_ip (user_ip),
            KEY timestamp (timestamp)
        ) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Export audit logs
	 */
	public function export_audit_logs( $format = 'csv', $filters = array() ) {
		$logs = $this->get_audit_logs( $filters, 10000 ); // Export up to 10k records

		if ( $format === 'csv' ) {
			return $this->export_logs_csv( $logs );
		} elseif ( $format === 'json' ) {
			return $this->export_logs_json( $logs );
		}

		return false;
	}

	/**
	 * Export logs as CSV
	 */
	private function export_logs_csv( $logs ) {
		$csv_data = "ID,Timestamp,Event Type,Level,Message,User ID,IP Address,User Agent,Request URI\n";

		foreach ( $logs as $log ) {
			$csv_data .= sprintf(
				"%d,%s,%s,%s,\"%s\",%d,%s,\"%s\",\"%s\"\n",
				$log->id,
				$log->timestamp,
				$log->event_type,
				$log->level,
				str_replace( '"', '""', $log->message ),
				$log->user_id,
				$log->user_ip,
				str_replace( '"', '""', $log->user_agent ),
				str_replace( '"', '""', $log->request_uri )
			);
		}

		return $csv_data;
	}

	/**
	 * Export logs as JSON
	 */
	private function export_logs_json( $logs ) {
		$json_logs = array();

		foreach ( $logs as $log ) {
			$json_logs[] = array(
				'id'          => $log->id,
				'timestamp'   => $log->timestamp,
				'event_type'  => $log->event_type,
				'level'       => $log->level,
				'message'     => $log->message,
				'user_id'     => $log->user_id,
				'user_ip'     => $log->user_ip,
				'user_agent'  => $log->user_agent,
				'request_uri' => $log->request_uri,
				'referer'     => $log->referer,
				'session_id'  => $log->session_id,
				'event_data'  => json_decode( $log->event_data, true ),
			);
		}

		return json_encode( $json_logs, JSON_PRETTY_PRINT );
	}
}

if ( class_exists( 'ENNU_Security_Audit_Logger' ) ) {
	new ENNU_Security_Audit_Logger();

	register_activation_hook( __FILE__, array( 'ENNU_Security_Audit_Logger', 'create_audit_table' ) );
}
