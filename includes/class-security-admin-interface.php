<?php
/**
 * Security Admin Interface
 *
 * Provides administrative interface for managing security settings,
 * viewing audit logs, and monitoring security events.
 *
 * @package ENNU_Life_Assessments
 * @since 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Security_Admin_Interface {

	private $security_manager;
	private $audit_logger;

	public function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Setup WordPress hooks
	 */
	private function setup_hooks() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_ajax_ennu_security_action', array( $this, 'handle_security_actions' ) );
		add_action( 'wp_ajax_ennu_export_audit_logs', array( $this, 'export_audit_logs' ) );
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'ennu-life-assessments',
			'Security Center',
			'Security',
			'manage_options',
			'ennu-security',
			array( $this, 'render_security_page' )
		);
	}

	/**
	 * Enqueue admin scripts
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( strpos( $hook, 'ennu-security' ) !== false ) {
			wp_enqueue_script( 'ennu-security-admin', plugin_dir_url( __FILE__ ) . '../assets/js/security-admin.js', array( 'jquery' ), '1.0.0', true );
			wp_enqueue_style( 'ennu-security-admin', plugin_dir_url( __FILE__ ) . '../assets/css/security-admin.css', array(), '1.0.0' );

			wp_localize_script(
				'ennu-security-admin',
				'ennuSecurity',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'ennu_security_nonce' ),
					'strings' => array(
						'confirm_unblock'    => __( 'Are you sure you want to unblock this IP address?', 'ennu-life-assessments' ),
						'confirm_clear_logs' => __( 'Are you sure you want to clear all audit logs?', 'ennu-life-assessments' ),
						'export_success'     => __( 'Audit logs exported successfully', 'ennu-life-assessments' ),
						'action_success'     => __( 'Action completed successfully', 'ennu-life-assessments' ),
						'action_error'       => __( 'An error occurred while performing the action', 'ennu-life-assessments' ),
					),
				)
			);
		}
	}

	/**
	 * Render security page
	 */
	public function render_security_page() {
		$active_tab = $_GET['tab'] ?? 'dashboard';

		?>
		<div class="wrap">
			<h1><?php _e( 'ENNU Security Center', 'ennu-life-assessments' ); ?></h1>
			
			<nav class="nav-tab-wrapper">
				<a href="?page=ennu-security&tab=dashboard" class="nav-tab <?php echo $active_tab === 'dashboard' ? 'nav-tab-active' : ''; ?>">
					<?php _e( 'Dashboard', 'ennu-life-assessments' ); ?>
				</a>
				<a href="?page=ennu-security&tab=blocked-ips" class="nav-tab <?php echo $active_tab === 'blocked-ips' ? 'nav-tab-active' : ''; ?>">
					<?php _e( 'Blocked IPs', 'ennu-life-assessments' ); ?>
				</a>
				<a href="?page=ennu-security&tab=audit-logs" class="nav-tab <?php echo $active_tab === 'audit-logs' ? 'nav-tab-active' : ''; ?>">
					<?php _e( 'Audit Logs', 'ennu-life-assessments' ); ?>
				</a>
				<a href="?page=ennu-security&tab=settings" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
					<?php _e( 'Settings', 'ennu-life-assessments' ); ?>
				</a>
				<a href="?page=ennu-security&tab=2fa" class="nav-tab <?php echo $active_tab === '2fa' ? 'nav-tab-active' : ''; ?>">
					<?php _e( 'Two-Factor Auth', 'ennu-life-assessments' ); ?>
				</a>
			</nav>
			
			<div class="tab-content">
				<?php
				switch ( $active_tab ) {
					case 'dashboard':
						$this->render_dashboard_tab();
						break;
					case 'blocked-ips':
						$this->render_blocked_ips_tab();
						break;
					case 'audit-logs':
						$this->render_audit_logs_tab();
						break;
					case 'settings':
						$this->render_settings_tab();
						break;
					case '2fa':
						$this->render_2fa_tab();
						break;
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render dashboard tab
	 */
	private function render_dashboard_tab() {
		if ( class_exists( 'ENNU_Advanced_Security_Manager' ) ) {
			$security_manager = new ENNU_Advanced_Security_Manager();
			$stats            = $security_manager->get_security_stats( 7 );
		} else {
			$stats = array();
		}

		?>
		<div class="security-dashboard">
			<div class="security-stats-grid">
				<div class="security-stat-card">
					<h3><?php _e( 'Blocked IPs', 'ennu-life-assessments' ); ?></h3>
					<div class="stat-number"><?php echo $stats['blocked_ips_count'] ?? 0; ?></div>
					<p><?php _e( 'Currently blocked IP addresses', 'ennu-life-assessments' ); ?></p>
				</div>
				
				<div class="security-stat-card">
					<h3><?php _e( 'Threats Detected', 'ennu-life-assessments' ); ?></h3>
					<div class="stat-number"><?php echo $stats['threat_detected'] ?? 0; ?></div>
					<p><?php _e( 'Security threats in last 7 days', 'ennu-life-assessments' ); ?></p>
				</div>
				
				<div class="security-stat-card">
					<h3><?php _e( 'Failed Logins', 'ennu-life-assessments' ); ?></h3>
					<div class="stat-number"><?php echo $stats['failed_login'] ?? 0; ?></div>
					<p><?php _e( 'Failed login attempts in last 7 days', 'ennu-life-assessments' ); ?></p>
				</div>
				
				<div class="security-stat-card">
					<h3><?php _e( 'Rate Limits', 'ennu-life-assessments' ); ?></h3>
					<div class="stat-number"><?php echo $stats['rate_limit_exceeded'] ?? 0; ?></div>
					<p><?php _e( 'Rate limit violations in last 7 days', 'ennu-life-assessments' ); ?></p>
				</div>
			</div>
			
			<div class="security-recent-events">
				<h3><?php _e( 'Recent Security Events', 'ennu-life-assessments' ); ?></h3>
				<?php $this->render_recent_events(); ?>
			</div>
			
			<div class="security-quick-actions">
				<h3><?php _e( 'Quick Actions', 'ennu-life-assessments' ); ?></h3>
				<div class="quick-actions-grid">
					<button class="button button-secondary" onclick="ennuSecurity.exportAuditLogs('csv')">
						<?php _e( 'Export Audit Logs (CSV)', 'ennu-life-assessments' ); ?>
					</button>
					<button class="button button-secondary" onclick="ennuSecurity.clearOldLogs()">
						<?php _e( 'Clear Old Logs', 'ennu-life-assessments' ); ?>
					</button>
					<button class="button button-secondary" onclick="ennuSecurity.runSecurityScan()">
						<?php _e( 'Run Security Scan', 'ennu-life-assessments' ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render blocked IPs tab
	 */
	private function render_blocked_ips_tab() {
		if ( class_exists( 'ENNU_Advanced_Security_Manager' ) ) {
			$security_manager = new ENNU_Advanced_Security_Manager();
			$blocked_ips      = $security_manager->get_blocked_ips();
		} else {
			$blocked_ips = array();
		}

		?>
		<div class="blocked-ips-section">
			<div class="section-header">
				<h3><?php _e( 'Blocked IP Addresses', 'ennu-life-assessments' ); ?></h3>
				<button class="button button-primary" onclick="ennuSecurity.showAddIpForm()">
					<?php _e( 'Block IP Address', 'ennu-life-assessments' ); ?>
				</button>
			</div>
			
			<div id="add-ip-form" class="add-ip-form" style="display: none;">
				<h4><?php _e( 'Block New IP Address', 'ennu-life-assessments' ); ?></h4>
				<table class="form-table">
					<tr>
						<th><label for="ip-address"><?php _e( 'IP Address', 'ennu-life-assessments' ); ?></label></th>
						<td><input type="text" id="ip-address" class="regular-text" placeholder="192.168.1.1" /></td>
					</tr>
					<tr>
						<th><label for="block-reason"><?php _e( 'Reason', 'ennu-life-assessments' ); ?></label></th>
						<td><input type="text" id="block-reason" class="regular-text" placeholder="Manual block" /></td>
					</tr>
				</table>
				<p class="submit">
					<button class="button button-primary" onclick="ennuSecurity.blockIp()">
						<?php _e( 'Block IP', 'ennu-life-assessments' ); ?>
					</button>
					<button class="button button-secondary" onclick="ennuSecurity.hideAddIpForm()">
						<?php _e( 'Cancel', 'ennu-life-assessments' ); ?>
					</button>
				</p>
			</div>
			
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php _e( 'IP Address', 'ennu-life-assessments' ); ?></th>
						<th><?php _e( 'Date Blocked', 'ennu-life-assessments' ); ?></th>
						<th><?php _e( 'Reason', 'ennu-life-assessments' ); ?></th>
						<th><?php _e( 'Actions', 'ennu-life-assessments' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $blocked_ips ) ) : ?>
						<tr>
							<td colspan="4"><?php _e( 'No blocked IP addresses', 'ennu-life-assessments' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $blocked_ips as $ip ) : ?>
							<tr>
								<td><?php echo esc_html( $ip ); ?></td>
								<td><?php echo esc_html( date( 'Y-m-d H:i:s' ) ); ?></td>
								<td><?php _e( 'Security violation', 'ennu-life-assessments' ); ?></td>
								<td>
									<button class="button button-small" onclick="ennuSecurity.unblockIp('<?php echo esc_js( $ip ); ?>')">
										<?php _e( 'Unblock', 'ennu-life-assessments' ); ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render audit logs tab
	 */
	private function render_audit_logs_tab() {
		$page     = $_GET['paged'] ?? 1;
		$per_page = 50;
		$offset   = ( $page - 1 ) * $per_page;

		if ( class_exists( 'ENNU_Security_Audit_Logger' ) ) {
			$audit_logger = new ENNU_Security_Audit_Logger();
			$logs         = $audit_logger->get_audit_logs( array(), $per_page, $offset );
		} else {
			$logs = array();
		}

		?>
		<div class="audit-logs-section">
			<div class="section-header">
				<h3><?php _e( 'Security Audit Logs', 'ennu-life-assessments' ); ?></h3>
				<div class="export-buttons">
					<button class="button button-secondary" onclick="ennuSecurity.exportAuditLogs('csv')">
						<?php _e( 'Export CSV', 'ennu-life-assessments' ); ?>
					</button>
					<button class="button button-secondary" onclick="ennuSecurity.exportAuditLogs('json')">
						<?php _e( 'Export JSON', 'ennu-life-assessments' ); ?>
					</button>
				</div>
			</div>
			
			<div class="log-filters">
				<form method="get" action="">
					<input type="hidden" name="page" value="ennu-security" />
					<input type="hidden" name="tab" value="audit-logs" />
					
					<select name="event_type">
						<option value=""><?php _e( 'All Event Types', 'ennu-life-assessments' ); ?></option>
						<option value="user_login"><?php _e( 'User Login', 'ennu-life-assessments' ); ?></option>
						<option value="failed_login"><?php _e( 'Failed Login', 'ennu-life-assessments' ); ?></option>
						<option value="security_threat_detected"><?php _e( 'Security Threat', 'ennu-life-assessments' ); ?></option>
						<option value="ip_blocked"><?php _e( 'IP Blocked', 'ennu-life-assessments' ); ?></option>
					</select>
					
					<select name="level">
						<option value=""><?php _e( 'All Levels', 'ennu-life-assessments' ); ?></option>
						<option value="CRITICAL"><?php _e( 'Critical', 'ennu-life-assessments' ); ?></option>
						<option value="HIGH"><?php _e( 'High', 'ennu-life-assessments' ); ?></option>
						<option value="MEDIUM"><?php _e( 'Medium', 'ennu-life-assessments' ); ?></option>
						<option value="LOW"><?php _e( 'Low', 'ennu-life-assessments' ); ?></option>
					</select>
					
					<input type="date" name="date_from" placeholder="<?php _e( 'From Date', 'ennu-life-assessments' ); ?>" />
					<input type="date" name="date_to" placeholder="<?php _e( 'To Date', 'ennu-life-assessments' ); ?>" />
					
					<button type="submit" class="button"><?php _e( 'Filter', 'ennu-life-assessments' ); ?></button>
				</form>
			</div>
			
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php _e( 'Timestamp', 'ennu-life-assessments' ); ?></th>
						<th><?php _e( 'Event Type', 'ennu-life-assessments' ); ?></th>
						<th><?php _e( 'Level', 'ennu-life-assessments' ); ?></th>
						<th><?php _e( 'Message', 'ennu-life-assessments' ); ?></th>
						<th><?php _e( 'IP Address', 'ennu-life-assessments' ); ?></th>
						<th><?php _e( 'User', 'ennu-life-assessments' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $logs ) ) : ?>
						<tr>
							<td colspan="6"><?php _e( 'No audit logs found', 'ennu-life-assessments' ); ?></td>
						</tr>
					<?php else : ?>
						<?php foreach ( $logs as $log ) : ?>
							<tr class="log-level-<?php echo strtolower( $log->level ); ?>">
								<td><?php echo esc_html( $log->timestamp ); ?></td>
								<td><?php echo esc_html( $log->event_type ); ?></td>
								<td><span class="log-level-badge level-<?php echo strtolower( $log->level ); ?>"><?php echo esc_html( $log->level ); ?></span></td>
								<td><?php echo esc_html( $log->message ); ?></td>
								<td><?php echo esc_html( $log->user_ip ); ?></td>
								<td>
									<?php
									if ( $log->user_id ) {
										$user = get_user_by( 'ID', $log->user_id );
										echo $user ? esc_html( $user->user_login ) : 'Unknown';
									} else {
										echo '—';
									}
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Render settings tab
	 */
	private function render_settings_tab() {
		if ( isset( $_POST['save_security_settings'] ) ) {
			$this->save_security_settings();
		}

		$settings = get_option( 'ennu_security_settings', array() );

		?>
		<div class="security-settings-section">
			<form method="post" action="">
				<?php wp_nonce_field( 'ennu_security_settings', 'security_settings_nonce' ); ?>
				
				<h3><?php _e( 'Rate Limiting Settings', 'ennu-life-assessments' ); ?></h3>
				<table class="form-table">
					<tr>
						<th><label for="login_attempts"><?php _e( 'Login Attempts (per minute)', 'ennu-life-assessments' ); ?></label></th>
						<td><input type="number" id="login_attempts" name="login_attempts" value="<?php echo esc_attr( $settings['login_attempts'] ?? 5 ); ?>" min="1" max="100" /></td>
					</tr>
					<tr>
						<th><label for="api_requests"><?php _e( 'API Requests (per minute)', 'ennu-life-assessments' ); ?></label></th>
						<td><input type="number" id="api_requests" name="api_requests" value="<?php echo esc_attr( $settings['api_requests'] ?? 60 ); ?>" min="1" max="1000" /></td>
					</tr>
					<tr>
						<th><label for="form_submissions"><?php _e( 'Form Submissions (per minute)', 'ennu-life-assessments' ); ?></label></th>
						<td><input type="number" id="form_submissions" name="form_submissions" value="<?php echo esc_attr( $settings['form_submissions'] ?? 10 ); ?>" min="1" max="100" /></td>
					</tr>
				</table>
				
				<h3><?php _e( 'Security Features', 'ennu-life-assessments' ); ?></h3>
				<table class="form-table">
					<tr>
						<th><label for="enable_2fa"><?php _e( 'Enable Two-Factor Authentication', 'ennu-life-assessments' ); ?></label></th>
						<td><input type="checkbox" id="enable_2fa" name="enable_2fa" value="1" <?php checked( $settings['enable_2fa'] ?? false ); ?> /></td>
					</tr>
					<tr>
						<th><label for="enable_audit_logging"><?php _e( 'Enable Audit Logging', 'ennu-life-assessments' ); ?></label></th>
						<td><input type="checkbox" id="enable_audit_logging" name="enable_audit_logging" value="1" <?php checked( $settings['enable_audit_logging'] ?? true ); ?> /></td>
					</tr>
					<tr>
						<th><label for="enable_threat_detection"><?php _e( 'Enable Threat Detection', 'ennu-life-assessments' ); ?></label></th>
						<td><input type="checkbox" id="enable_threat_detection" name="enable_threat_detection" value="1" <?php checked( $settings['enable_threat_detection'] ?? true ); ?> /></td>
					</tr>
				</table>
				
				<h3><?php _e( 'Email Notifications', 'ennu-life-assessments' ); ?></h3>
				<table class="form-table">
					<tr>
						<th><label for="security_email"><?php _e( 'Security Alert Email', 'ennu-life-assessments' ); ?></label></th>
						<td><input type="email" id="security_email" name="security_email" value="<?php echo esc_attr( $settings['security_email'] ?? get_option( 'admin_email' ) ); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th><label for="email_critical_only"><?php _e( 'Email Critical Events Only', 'ennu-life-assessments' ); ?></label></th>
						<td><input type="checkbox" id="email_critical_only" name="email_critical_only" value="1" <?php checked( $settings['email_critical_only'] ?? true ); ?> /></td>
					</tr>
				</table>
				
				<p class="submit">
					<input type="submit" name="save_security_settings" class="button-primary" value="<?php _e( 'Save Settings', 'ennu-life-assessments' ); ?>" />
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Render 2FA tab
	 */
	private function render_2fa_tab() {
		?>
		<div class="two-factor-section">
			<h3><?php _e( 'Two-Factor Authentication', 'ennu-life-assessments' ); ?></h3>
			<p><?php _e( 'Two-factor authentication adds an extra layer of security to user accounts.', 'ennu-life-assessments' ); ?></p>
			
			<div class="2fa-status">
				<h4><?php _e( 'Current Status', 'ennu-life-assessments' ); ?></h4>
				<?php
				$users_with_2fa = get_users(
					array(
						'meta_key'     => 'ennu_2fa_settings',
						'meta_compare' => 'EXISTS',
					)
				);
				?>
				<p><?php printf( __( '%d users have two-factor authentication enabled.', 'ennu-life-assessments' ), count( $users_with_2fa ) ); ?></p>
			</div>
			
			<div class="2fa-settings">
				<h4><?php _e( 'Global Settings', 'ennu-life-assessments' ); ?></h4>
				<form method="post" action="">
					<?php wp_nonce_field( 'ennu_2fa_settings', '2fa_settings_nonce' ); ?>
					
					<table class="form-table">
						<tr>
							<th><label for="require_2fa_admin"><?php _e( 'Require 2FA for Administrators', 'ennu-life-assessments' ); ?></label></th>
							<td><input type="checkbox" id="require_2fa_admin" name="require_2fa_admin" value="1" /></td>
						</tr>
						<tr>
							<th><label for="grace_period"><?php _e( 'Grace Period (days)', 'ennu-life-assessments' ); ?></label></th>
							<td><input type="number" id="grace_period" name="grace_period" value="7" min="0" max="30" /></td>
						</tr>
					</table>
					
					<p class="submit">
						<input type="submit" name="save_2fa_settings" class="button-primary" value="<?php _e( 'Save Settings', 'ennu-life-assessments' ); ?>" />
					</p>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Render recent events
	 */
	private function render_recent_events() {
		if ( class_exists( 'ENNU_Security_Audit_Logger' ) ) {
			$audit_logger = new ENNU_Security_Audit_Logger();
			$recent_logs  = $audit_logger->get_audit_logs( array(), 10 );
		} else {
			$recent_logs = array();
		}

		if ( empty( $recent_logs ) ) {
			echo '<p>' . __( 'No recent security events', 'ennu-life-assessments' ) . '</p>';
			return;
		}

		echo '<ul class="recent-events-list">';
		foreach ( $recent_logs as $log ) {
			echo '<li class="event-level-' . strtolower( $log->level ) . '">';
			echo '<span class="event-time">' . esc_html( $log->timestamp ) . '</span>';
			echo '<span class="event-type">' . esc_html( $log->event_type ) . '</span>';
			echo '<span class="event-message">' . esc_html( $log->message ) . '</span>';
			echo '</li>';
		}
		echo '</ul>';
	}

	/**
	 * Handle security actions
	 */
	public function handle_security_actions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_security_nonce' ) ) {
			wp_die( 'Invalid nonce' );
		}

		$action = sanitize_text_field( $_POST['security_action'] );

		switch ( $action ) {
			case 'unblock_ip':
				$this->handle_unblock_ip();
				break;
			case 'block_ip':
				$this->handle_block_ip();
				break;
			case 'clear_old_logs':
				$this->handle_clear_old_logs();
				break;
			case 'run_security_scan':
				$this->handle_security_scan();
				break;
		}
	}

	/**
	 * Handle unblock IP
	 */
	private function handle_unblock_ip() {
		$ip = sanitize_text_field( $_POST['ip'] );

		if ( class_exists( 'ENNU_Advanced_Security_Manager' ) ) {
			$security_manager = new ENNU_Advanced_Security_Manager();
			$result           = $security_manager->unblock_ip( $ip );

			if ( $result ) {
				wp_send_json_success( array( 'message' => 'IP address unblocked successfully' ) );
			} else {
				wp_send_json_error( array( 'message' => 'Failed to unblock IP address' ) );
			}
		} else {
			wp_send_json_error( array( 'message' => 'Security manager not available' ) );
		}
	}

	/**
	 * Handle block IP
	 */
	private function handle_block_ip() {
		$ip     = sanitize_text_field( $_POST['ip'] );
		$reason = sanitize_text_field( $_POST['reason'] );

		if ( ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			wp_send_json_error( array( 'message' => 'Invalid IP address' ) );
		}

		if ( class_exists( 'ENNU_Advanced_Security_Manager' ) ) {
			$security_manager = new ENNU_Advanced_Security_Manager();
			$security_manager->block_ip( $ip, $reason );
			wp_send_json_success( array( 'message' => 'IP address blocked successfully' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Security manager not available' ) );
		}
	}

	/**
	 * Handle clear old logs
	 */
	private function handle_clear_old_logs() {
		if ( class_exists( 'ENNU_Security_Audit_Logger' ) ) {
			$audit_logger = new ENNU_Security_Audit_Logger();
			$audit_logger->cleanup_old_logs();
			wp_send_json_success( array( 'message' => 'Old logs cleared successfully' ) );
		} else {
			wp_send_json_error( array( 'message' => 'Audit logger not available' ) );
		}
	}

	/**
	 * Handle security scan
	 */
	private function handle_security_scan() {
		$scan_results = array();

		$scan_results['file_permissions'] = $this->check_file_permissions();
		$scan_results['wp_version']       = $this->check_wp_version();
		$scan_results['plugin_updates']   = $this->check_plugin_updates();
		$scan_results['user_accounts']    = $this->check_user_accounts();
		$scan_results['security_headers'] = $this->check_security_headers();

		wp_send_json_success(
			array(
				'message' => 'Security scan completed',
				'results' => $scan_results,
			)
		);
	}

	/**
	 * Check file permissions
	 */
	private function check_file_permissions() {
		$issues = array();

		$wp_config_path = ABSPATH . 'wp-config.php';
		if ( file_exists( $wp_config_path ) ) {
			$perms = fileperms( $wp_config_path ) & 0777;
			if ( $perms > 0644 ) {
				$issues[] = 'wp-config.php has overly permissive permissions';
			}
		}

		$htaccess_path = ABSPATH . '.htaccess';
		if ( file_exists( $htaccess_path ) ) {
			$perms = fileperms( $htaccess_path ) & 0777;
			if ( $perms > 0644 ) {
				$issues[] = '.htaccess has overly permissive permissions';
			}
		}

		return array(
			'status' => empty( $issues ) ? 'pass' : 'warning',
			'issues' => $issues,
		);
	}

	/**
	 * Check WordPress version
	 */
	private function check_wp_version() {
		global $wp_version;

		$latest_version = get_transient( 'wp_latest_version' );
		if ( ! $latest_version ) {
			$response = wp_remote_get( 'https://api.wordpress.org/core/version-check/1.7/' );
			if ( ! is_wp_error( $response ) ) {
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				if ( isset( $data['offers'][0]['version'] ) ) {
					$latest_version = $data['offers'][0]['version'];
					set_transient( 'wp_latest_version', $latest_version, HOUR_IN_SECONDS );
				}
			}
		}

		$is_outdated = $latest_version && version_compare( $wp_version, $latest_version, '<' );

		return array(
			'status'          => $is_outdated ? 'warning' : 'pass',
			'current_version' => $wp_version,
			'latest_version'  => $latest_version,
			'message'         => $is_outdated ? 'WordPress is outdated' : 'WordPress is up to date',
		);
	}

	/**
	 * Check plugin updates
	 */
	private function check_plugin_updates() {
		$updates          = get_site_transient( 'update_plugins' );
		$outdated_plugins = array();

		if ( $updates && isset( $updates->response ) ) {
			foreach ( $updates->response as $plugin => $data ) {
				$plugin_data        = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
				$outdated_plugins[] = array(
					'name'            => $plugin_data['Name'],
					'current_version' => $plugin_data['Version'],
					'new_version'     => $data->new_version,
				);
			}
		}

		return array(
			'status'           => empty( $outdated_plugins ) ? 'pass' : 'warning',
			'outdated_count'   => count( $outdated_plugins ),
			'outdated_plugins' => $outdated_plugins,
		);
	}

	/**
	 * Check user accounts
	 */
	private function check_user_accounts() {
		$issues = array();

		$admin_user = get_user_by( 'login', 'admin' );
		if ( $admin_user ) {
			$issues[] = 'Default admin username detected';
		}

		$users = get_users( array( 'role' => 'administrator' ) );
		foreach ( $users as $user ) {
			if ( strlen( $user->user_pass ) < 8 ) {
				$issues[] = "User {$user->user_login} may have a weak password";
			}
		}

		return array(
			'status' => empty( $issues ) ? 'pass' : 'warning',
			'issues' => $issues,
		);
	}

	/**
	 * Check security headers
	 */
	private function check_security_headers() {
		$headers_to_check = array(
			'X-Content-Type-Options',
			'X-Frame-Options',
			'X-XSS-Protection',
			'Content-Security-Policy',
		);

		$missing_headers = array();

		foreach ( $headers_to_check as $header ) {
			if ( ! headers_sent() ) {
				$missing_headers[] = $header;
			}
		}

		return array(
			'status'          => empty( $missing_headers ) ? 'pass' : 'warning',
			'missing_headers' => $missing_headers,
		);
	}

	/**
	 * Export audit logs
	 */
	public function export_audit_logs() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_security_nonce' ) ) {
			wp_die( 'Invalid nonce' );
		}

		$format  = sanitize_text_field( $_POST['format'] ?? 'csv' );
		$filters = array();

		if ( ! empty( $_POST['event_type'] ) ) {
			$filters['event_type'] = sanitize_text_field( $_POST['event_type'] );
		}

		if ( ! empty( $_POST['level'] ) ) {
			$filters['level'] = sanitize_text_field( $_POST['level'] );
		}

		if ( ! empty( $_POST['date_from'] ) ) {
			$filters['date_from'] = sanitize_text_field( $_POST['date_from'] );
		}

		if ( ! empty( $_POST['date_to'] ) ) {
			$filters['date_to'] = sanitize_text_field( $_POST['date_to'] );
		}

		if ( class_exists( 'ENNU_Security_Audit_Logger' ) ) {
			$audit_logger = new ENNU_Security_Audit_Logger();
			$export_data  = $audit_logger->export_audit_logs( $format, $filters );

			if ( $export_data ) {
				$filename = 'ennu-audit-logs-' . date( 'Y-m-d-H-i-s' ) . '.' . $format;

				header( 'Content-Type: application/octet-stream' );
				header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
				header( 'Content-Length: ' . strlen( $export_data ) );

				echo $export_data;
				exit;
			} else {
				wp_send_json_error( array( 'message' => 'Failed to export audit logs' ) );
			}
		} else {
			wp_send_json_error( array( 'message' => 'Audit logger not available' ) );
		}
	}

	/**
	 * Save security settings
	 */
	private function save_security_settings() {
		if ( ! wp_verify_nonce( $_POST['security_settings_nonce'], 'ennu_security_settings' ) ) {
			return;
		}

		$settings = array(
			'login_attempts'          => intval( $_POST['login_attempts'] ?? 5 ),
			'api_requests'            => intval( $_POST['api_requests'] ?? 60 ),
			'form_submissions'        => intval( $_POST['form_submissions'] ?? 10 ),
			'enable_2fa'              => isset( $_POST['enable_2fa'] ),
			'enable_audit_logging'    => isset( $_POST['enable_audit_logging'] ),
			'enable_threat_detection' => isset( $_POST['enable_threat_detection'] ),
			'security_email'          => sanitize_email( $_POST['security_email'] ?? get_option( 'admin_email' ) ),
			'email_critical_only'     => isset( $_POST['email_critical_only'] ),
		);

		update_option( 'ennu_security_settings', $settings );

		if ( class_exists( 'ENNU_Advanced_Security_Manager' ) ) {
			$security_manager = new ENNU_Advanced_Security_Manager();
			$security_manager->update_rate_limits(
				array(
					'login_attempts'   => $settings['login_attempts'],
					'api_requests'     => $settings['api_requests'],
					'form_submissions' => $settings['form_submissions'],
				)
			);
		}

		update_option( 'ennu_2fa_enabled', $settings['enable_2fa'] );

		add_action(
			'admin_notices',
			function() {
				echo '<div class="notice notice-success is-dismissible"><p>Security settings saved successfully.</p></div>';
			}
		);
	}
}

if ( is_admin() ) {
	new ENNU_Security_Admin_Interface();
}
