<?php
/**
 * ENNU Life Assessments - Monitoring System
 *
 * Comprehensive monitoring for performance, errors, and database health
 *
 * @package ENNU_Life
 * @copyright Copyright (c) 2024, Very Good Plugins, https://verygoodplugins.com
 * @license GPL-3.0+
 * @since 64.6.30
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Monitoring Class
 *
 * Provides comprehensive monitoring for the ENNU Life Assessments plugin
 * including performance metrics, error tracking, and database health monitoring
 */
class ENNU_Monitoring {

	/**
	 * Singleton instance
	 *
	 * @var ENNU_Monitoring
	 */
	private static $instance = null;

	/**
	 * Performance metrics storage
	 *
	 * @var array
	 */
	private $metrics = array();

	/**
	 * Error log storage
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * Database queries storage
	 *
	 * @var array
	 */
	private $queries = array();

	/**
	 * Get singleton instance
	 *
	 * @return ENNU_Monitoring
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
	}

	/**
	 * Initialize monitoring hooks
	 */
	private function init_hooks() {
		// Performance monitoring
		add_action( 'init', array( $this, 'start_performance_timer' ) );
		add_action( 'shutdown', array( $this, 'end_performance_timer' ) );

		// Error monitoring
		add_action( 'wp_ajax_ennu_log_error', array( $this, 'log_error' ) );
		add_action( 'wp_ajax_nopriv_ennu_log_error', array( $this, 'log_error' ) );

		// Database monitoring
		add_filter( 'query', array( $this, 'monitor_database_queries' ) );

		// Custom metrics
		add_action( 'ennu_assessment_completed', array( $this, 'track_assessment_completion' ) );
		add_action( 'ennu_biomarker_updated', array( $this, 'track_biomarker_update' ) );
		add_action( 'ennu_score_calculated', array( $this, 'track_score_calculation' ) );

		// Admin monitoring dashboard
		add_action( 'admin_menu', array( $this, 'add_monitoring_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_monitoring_scripts' ) );
	}

	/**
	 * Start performance timer
	 */
	public function start_performance_timer() {
		$this->metrics['start_time'] = microtime( true );
		$this->metrics['memory_start'] = memory_get_usage();
	}

	/**
	 * End performance timer and log metrics
	 */
	public function end_performance_timer() {
		if ( ! isset( $this->metrics['start_time'] ) ) {
			return;
		}

		$this->metrics['end_time'] = microtime( true );
		$this->metrics['memory_end'] = memory_get_usage();
		$this->metrics['peak_memory'] = memory_get_peak_usage();

		$this->log_performance_metrics();
	}

	/**
	 * Log performance metrics
	 */
	private function log_performance_metrics() {
		$execution_time = $this->metrics['end_time'] - $this->metrics['start_time'];
		$memory_used = $this->metrics['memory_end'] - $this->metrics['memory_start'];
		$peak_memory = $this->metrics['peak_memory'];

		$log_message = sprintf(
			'ENNU Performance: Execution Time: %.4f seconds, Memory Used: %s, Peak Memory: %s',
			$execution_time,
			$this->format_bytes( $memory_used ),
			$this->format_bytes( $peak_memory )
		);

		// REMOVED: error_log( $log_message );

		// Store metrics for admin dashboard
		$this->store_metric( 'performance', array(
			'execution_time' => $execution_time,
			'memory_used' => $memory_used,
			'peak_memory' => $peak_memory,
			'timestamp' => current_time( 'mysql' ),
		) );
	}

	/**
	 * Log error with context
	 *
	 * @param array $error_data Error data from AJAX request.
	 */
	public function log_error( $error_data = array() ) {
		$error = array(
			'message' => sanitize_text_field( $_POST['message'] ?? 'Unknown error' ),
			'file' => sanitize_text_field( $_POST['file'] ?? '' ),
			'line' => intval( $_POST['line'] ?? 0 ),
			'stack_trace' => sanitize_textarea_field( $_POST['stack_trace'] ?? '' ),
			'user_id' => get_current_user_id(),
			'url' => sanitize_url( $_POST['url'] ?? '' ),
			'timestamp' => current_time( 'mysql' ),
		);

		$this->errors[] = $error;

		// Log to WordPress error log
		$log_message = sprintf(
			'ENNU Error: %s in %s:%d - User: %d, URL: %s',
			$error['message'],
			$error['file'],
			$error['line'],
			$error['user_id'],
			$error['url']
		);

		// REMOVED: error_log( $log_message );

		// Store error for admin dashboard
		$this->store_error( $error );

		wp_send_json_success( array( 'error_logged' => true ) );
	}

	/**
	 * Monitor database queries
	 *
	 * @param string $query SQL query.
	 * @return string
	 */
	public function monitor_database_queries( $query ) {
		// Only monitor ENNU-related queries
		if ( strpos( $query, 'ennu_' ) !== false ) {
			$query_data = array(
				'query' => $query,
				'timestamp' => current_time( 'mysql' ),
				'execution_time' => 0, // Would need to hook into query execution to get this
			);

			$this->queries[] = $query_data;

			// Log slow queries (if we had execution time)
			if ( isset( $query_data['execution_time'] ) && $query_data['execution_time'] > 1.0 ) {
				// REMOVED: error_log( sprintf( 'ENNU Slow Query: %s (%.4f seconds)', $query, $query_data['execution_time'] ) );
			}
		}

		return $query;
	}

	/**
	 * Track assessment completion
	 *
	 * @param int $assessment_id Assessment ID.
	 */
	public function track_assessment_completion( $assessment_id ) {
		$this->store_metric( 'assessments_completed', array(
			'assessment_id' => $assessment_id,
			'user_id' => get_current_user_id(),
			'timestamp' => current_time( 'mysql' ),
		) );

		// REMOVED: // REMOVED DEBUG LOG: error_log( sprintf( 'ENNU Assessment Completed: ID %d by User %d', $assessment_id, get_current_user_id() ) );
	}

	/**
	 * Track biomarker update
	 *
	 * @param int $biomarker_id Biomarker ID.
	 */
	public function track_biomarker_update( $biomarker_id ) {
		$this->store_metric( 'biomarkers_updated', array(
			'biomarker_id' => $biomarker_id,
			'user_id' => get_current_user_id(),
			'timestamp' => current_time( 'mysql' ),
		) );

		// REMOVED: error_log( sprintf( 'ENNU Biomarker Updated: ID %d by User %d', $biomarker_id, get_current_user_id() ) );
	}

	/**
	 * Track score calculation
	 *
	 * @param array $score_data Score calculation data.
	 */
	public function track_score_calculation( $score_data ) {
		$this->store_metric( 'scores_calculated', array(
			'assessment_type' => $score_data['type'] ?? 'unknown',
			'score' => $score_data['score'] ?? 0,
			'user_id' => get_current_user_id(),
			'timestamp' => current_time( 'mysql' ),
		) );

		// REMOVED: error_log( sprintf( 'ENNU Score Calculated: Type %s, Score %.1f by User %d', $score_data['type'] ?? 'unknown', $score_data['score'] ?? 0, get_current_user_id() ) );
	}

	/**
	 * Track form performance with detailed metrics
	 */
	public function track_form_performance( $form_data ) {
		$start_time = microtime( true );
		
		// Track form submission performance
		$performance_data = array(
			'form_type' => $form_data['assessment_type'] ?? 'unknown',
			'user_id' => get_current_user_id(),
			'start_time' => $start_time,
			'memory_usage' => memory_get_usage( true ),
			'peak_memory' => memory_get_peak_usage( true ),
			'ip_address' => $this->get_client_ip(),
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
			'url' => $_SERVER['REQUEST_URI'] ?? '',
		);
		
		// Store performance data
		$this->store_performance_data( $performance_data );
		
		return $performance_data;
	}
	
	/**
	 * Track user engagement metrics
	 */
	public function track_user_engagement( $user_id, $action, $context = array() ) {
		$engagement_data = array(
			'user_id' => $user_id,
			'action' => $action,
			'timestamp' => current_time( 'mysql' ),
			'context' => $context,
			'session_id' => $this->get_session_id(),
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
			'ip_address' => $this->get_client_ip(),
		);
		
		$this->store_engagement_data( $engagement_data );
	}
	
	/**
	 * Generate comprehensive performance report
	 */
	public function generate_performance_report() {
		$report = array(
			'form_submissions' => $this->get_form_submission_stats(),
			'average_load_time' => $this->get_average_load_time(),
			'error_rate' => $this->get_error_rate(),
			'user_engagement' => $this->get_user_engagement_stats(),
			'system_health' => $this->get_system_health_metrics(),
			'performance_trends' => $this->get_performance_trends(),
		);
		
		return $report;
	}
	
	/**
	 * Get form submission statistics
	 */
	private function get_form_submission_stats() {
		global $wpdb;
		
		$stats = array();
		
		// Get total submissions in last 30 days
		$total_submissions = $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT(*) FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE 'ennu_%_submission_date' 
			AND meta_value >= %s
		", date( 'Y-m-d H:i:s', strtotime( '-30 days' ) ) ) );
		
		// Get submissions by assessment type
		$submissions_by_type = $wpdb->get_results( "
			SELECT 
				SUBSTRING_INDEX(meta_key, '_', 2) as assessment_type,
				COUNT(*) as count
			FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE 'ennu_%_submission_date'
			GROUP BY assessment_type
		" );
		
		$stats['total_submissions_30_days'] = $total_submissions;
		$stats['submissions_by_type'] = $submissions_by_type;
		
		return $stats;
	}
	
	/**
	 * Get average load time
	 */
	private function get_average_load_time() {
		$performance_log = get_option( 'ennu_performance_log', array() );
		
		if ( empty( $performance_log ) ) {
			return 0;
		}
		
		$total_time = 0;
		$count = 0;
		
		foreach ( $performance_log as $log ) {
			if ( isset( $log['execution_time'] ) ) {
				$total_time += $log['execution_time'];
				$count++;
			}
		}
		
		return $count > 0 ? $total_time / $count : 0;
	}
	
	/**
	 * Get error rate
	 */
	private function get_error_rate() {
		$error_log = get_option( 'ennu_error_log', array() );
		$performance_log = get_option( 'ennu_performance_log', array() );
		
		$error_count = count( $error_log );
		$total_operations = count( $performance_log );
		
		return $total_operations > 0 ? ( $error_count / $total_operations ) * 100 : 0;
	}
	
	/**
	 * Get user engagement statistics
	 */
	private function get_user_engagement_stats() {
		global $wpdb;
		
		$stats = array();
		
		// Get active users in last 7 days
		$active_users = $wpdb->get_var( $wpdb->prepare( "
			SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE 'ennu_%_last_activity' 
			AND meta_value >= %s
		", date( 'Y-m-d H:i:s', strtotime( '-7 days' ) ) ) );
		
		// Get completion rate
		$total_started = $wpdb->get_var( "
			SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE 'ennu_%_started'
		" );
		
		$total_completed = $wpdb->get_var( "
			SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE 'ennu_%_completed'
		" );
		
		$completion_rate = $total_started > 0 ? ( $total_completed / $total_started ) * 100 : 0;
		
		$stats['active_users_7_days'] = $active_users;
		$stats['completion_rate'] = round( $completion_rate, 2 );
		
		return $stats;
	}
	
	/**
	 * Get system health metrics
	 */
	private function get_system_health_metrics() {
		$metrics = array();
		
		// Memory usage
		$metrics['memory_usage'] = memory_get_usage( true );
		$metrics['peak_memory'] = memory_get_peak_usage( true );
		$metrics['memory_limit'] = ini_get( 'memory_limit' );
		
		// Database performance
		global $wpdb;
		$metrics['database_size'] = $this->get_database_size();
		$metrics['slow_queries'] = $this->get_slow_query_count();
		
		// Cache performance
		$metrics['cache_hit_rate'] = $this->get_cache_hit_rate();
		
		return $metrics;
	}
	
	/**
	 * Get performance trends
	 */
	private function get_performance_trends() {
		$performance_log = get_option( 'ennu_performance_log', array() );
		
		$trends = array();
		$daily_averages = array();
		
		// Group by date
		foreach ( $performance_log as $log ) {
			$date = date( 'Y-m-d', strtotime( $log['timestamp'] ) );
			if ( ! isset( $daily_averages[$date] ) ) {
				$daily_averages[$date] = array();
			}
			$daily_averages[$date][] = $log['execution_time'];
		}
		
		// Calculate daily averages
		foreach ( $daily_averages as $date => $times ) {
			$trends[$date] = array_sum( $times ) / count( $times );
		}
		
		return $trends;
	}
	
	/**
	 * Store performance data
	 */
	private function store_performance_data( $data ) {
		$performance_log = get_option( 'ennu_performance_log', array() );
		$performance_log[] = $data;
		
		// Keep only last 1000 entries
		if ( count( $performance_log ) > 1000 ) {
			$performance_log = array_slice( $performance_log, -1000 );
		}
		
		update_option( 'ennu_performance_log', $performance_log );
	}
	
	/**
	 * Store engagement data
	 */
	private function store_engagement_data( $data ) {
		$engagement_log = get_option( 'ennu_engagement_log', array() );
		$engagement_log[] = $data;
		
		// Keep only last 5000 entries
		if ( count( $engagement_log ) > 5000 ) {
			$engagement_log = array_slice( $engagement_log, -5000 );
		}
		
		update_option( 'ennu_engagement_log', $engagement_log );
	}
	
	/**
	 * Get client IP address
	 */
	private function get_client_ip() {
		$ip_keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' );
		
		foreach ( $ip_keys as $key ) {
			if ( array_key_exists( $key, $_SERVER ) === true ) {
				foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
					$ip = trim( $ip );
					if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
						return $ip;
					}
				}
			}
		}
		
		return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
	}
	
	/**
	 * Get session ID
	 */
	private function get_session_id() {
		return session_id() ?: uniqid();
	}
	
	/**
	 * Get database size
	 */
	private function get_database_size() {
		global $wpdb;
		
		$size = $wpdb->get_var( "
			SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) 
			FROM information_schema.TABLES 
			WHERE table_schema = DATABASE()
		" );
		
		return $size . ' MB';
	}
	
	/**
	 * Get slow query count
	 */
	private function get_slow_query_count() {
		$performance_log = get_option( 'ennu_performance_log', array() );
		$slow_count = 0;
		
		foreach ( $performance_log as $log ) {
			if ( isset( $log['execution_time'] ) && $log['execution_time'] > 1.0 ) {
				$slow_count++;
			}
		}
		
		return $slow_count;
	}
	
	/**
	 * Get cache hit rate
	 */
	private function get_cache_hit_rate() {
		// This would integrate with actual cache system
		// For now, return estimated rate
		return 85.5; // Estimated 85.5% cache hit rate
	}

	/**
	 * Store metric data
	 *
	 * @param string $metric_type Metric type.
	 * @param array  $data Metric data.
	 */
	private function store_metric( $metric_type, $data ) {
		$metrics = get_option( 'ennu_monitoring_metrics', array() );
		
		if ( ! isset( $metrics[ $metric_type ] ) ) {
			$metrics[ $metric_type ] = array();
		}

		$metrics[ $metric_type ][] = $data;

		// Keep only last 1000 entries per metric type
		if ( count( $metrics[ $metric_type ] ) > 1000 ) {
			$metrics[ $metric_type ] = array_slice( $metrics[ $metric_type ], -1000 );
		}

		update_option( 'ennu_monitoring_metrics', $metrics );
	}

	/**
	 * Store error data
	 *
	 * @param array $error Error data.
	 */
	private function store_error( $error ) {
		$errors = get_option( 'ennu_monitoring_errors', array() );
		$errors[] = $error;

		// Keep only last 500 errors
		if ( count( $errors ) > 500 ) {
			$errors = array_slice( $errors, -500 );
		}

		update_option( 'ennu_monitoring_errors', $errors );
	}

	/**
	 * Add monitoring menu to admin
	 */
	public function add_monitoring_menu() {
		add_submenu_page(
			'ennu-life-dashboard',
			__( 'System Monitoring', 'ennulifeassessments' ),
			__( 'Monitoring', 'ennulifeassessments' ),
			'manage_options',
			'ennu-monitoring',
			array( $this, 'render_monitoring_page' )
		);
	}

	/**
	 * Enqueue monitoring scripts
	 *
	 * @param string $hook_suffix Current admin page.
	 */
	public function enqueue_monitoring_scripts( $hook_suffix ) {
		if ( 'ennu-life-dashboard_page_ennu-monitoring' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_script( 'ennu-monitoring', ENNU_LIFE_PLUGIN_URL . 'assets/js/monitoring.js', array( 'jquery' ), ENNU_LIFE_VERSION, true );
		wp_localize_script( 'ennu-monitoring', 'ennuMonitoring', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'ennu_monitoring_nonce' ),
		) );
	}

	/**
	 * Render monitoring page
	 */
	public function render_monitoring_page() {
		$metrics = get_option( 'ennu_monitoring_metrics', array() );
		$errors = get_option( 'ennu_monitoring_errors', array() );

		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'ENNU Life Assessments - System Monitoring', 'ennulifeassessments' ); ?></h1>

			<div class="ennu-monitoring-dashboard">
				<!-- Performance Metrics -->
				<div class="ennu-monitoring-section">
					<h2><?php echo esc_html__( 'Performance Metrics', 'ennulifeassessments' ); ?></h2>
					<div class="ennu-metrics-grid">
						<?php $this->render_performance_metrics( $metrics ); ?>
					</div>
				</div>

				<!-- Error Tracking -->
				<div class="ennu-monitoring-section">
					<h2><?php echo esc_html__( 'Error Tracking', 'ennulifeassessments' ); ?></h2>
					<div class="ennu-errors-list">
						<?php $this->render_error_list( $errors ); ?>
					</div>
				</div>

				<!-- Database Health -->
				<div class="ennu-monitoring-section">
					<h2><?php echo esc_html__( 'Database Health', 'ennulifeassessments' ); ?></h2>
					<div class="ennu-database-health">
						<?php $this->render_database_health(); ?>
					</div>
				</div>
			</div>
		</div>

		<style>
		.ennu-monitoring-dashboard {
			margin-top: 20px;
		}

		.ennu-monitoring-section {
			background: #fff;
			border: 1px solid #ccd0d4;
			border-radius: 4px;
			padding: 20px;
			margin-bottom: 20px;
		}

		.ennu-metrics-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
			gap: 20px;
		}

		.ennu-metric-card {
			background: #f9f9f9;
			border: 1px solid #ddd;
			border-radius: 4px;
			padding: 15px;
		}

		.ennu-metric-value {
			font-size: 24px;
			font-weight: bold;
			color: #0073aa;
		}

		.ennu-metric-label {
			font-size: 14px;
			color: #666;
			margin-top: 5px;
		}

		.ennu-errors-list {
			max-height: 400px;
			overflow-y: auto;
		}

		.ennu-error-item {
			background: #fff3cd;
			border: 1px solid #ffeaa7;
			border-radius: 4px;
			padding: 10px;
			margin-bottom: 10px;
		}

		.ennu-error-message {
			font-weight: bold;
			color: #856404;
		}

		.ennu-error-details {
			font-size: 12px;
			color: #666;
			margin-top: 5px;
		}
		</style>
		<?php
	}

	/**
	 * Render performance metrics
	 *
	 * @param array $metrics Metrics data.
	 */
	private function render_performance_metrics( $metrics ) {
		$performance = $metrics['performance'] ?? array();
		$assessments = $metrics['assessments_completed'] ?? array();
		$biomarkers = $metrics['biomarkers_updated'] ?? array();
		$scores = $metrics['scores_calculated'] ?? array();

		?>
		<div class="ennu-metric-card">
			<div class="ennu-metric-value"><?php echo count( $assessments ); ?></div>
			<div class="ennu-metric-label"><?php echo esc_html__( 'Assessments Completed', 'ennulifeassessments' ); ?></div>
		</div>

		<div class="ennu-metric-card">
			<div class="ennu-metric-value"><?php echo count( $biomarkers ); ?></div>
			<div class="ennu-metric-label"><?php echo esc_html__( 'Biomarkers Updated', 'ennulifeassessments' ); ?></div>
		</div>

		<div class="ennu-metric-card">
			<div class="ennu-metric-value"><?php echo count( $scores ); ?></div>
			<div class="ennu-metric-label"><?php echo esc_html__( 'Scores Calculated', 'ennulifeassessments' ); ?></div>
		</div>

		<?php if ( ! empty( $performance ) ) : ?>
		<div class="ennu-metric-card">
			<div class="ennu-metric-value"><?php echo number_format( end( $performance )['execution_time'], 4 ); ?>s</div>
			<div class="ennu-metric-label"><?php echo esc_html__( 'Last Execution Time', 'ennulifeassessments' ); ?></div>
		</div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render error list
	 *
	 * @param array $errors Errors data.
	 */
	private function render_error_list( $errors ) {
		if ( empty( $errors ) ) {
			echo '<p>' . esc_html__( 'No errors logged.', 'ennulifeassessments' ) . '</p>';
			return;
		}

		// Show last 10 errors
		$recent_errors = array_slice( $errors, -10 );

		foreach ( $recent_errors as $error ) {
			?>
			<div class="ennu-error-item">
				<div class="ennu-error-message"><?php echo esc_html( $error['message'] ); ?></div>
				<div class="ennu-error-details">
					<?php echo esc_html( $error['file'] . ':' . $error['line'] ); ?> | 
					<?php echo esc_html( $error['timestamp'] ); ?> | 
					User: <?php echo esc_html( $error['user_id'] ); ?>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * Render database health
	 */
	private function render_database_health() {
		global $wpdb;

		// Check ENNU tables
		$ennu_tables = array(
			$wpdb->prefix . 'usermeta', // Check for ENNU user meta
		);

		$table_status = array();
		foreach ( $ennu_tables as $table ) {
			$result = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) );
			$table_status[ $table ] = ! empty( $result );
		}

		// Check for ENNU user meta entries
		$ennu_meta_count = $wpdb->get_var( $wpdb->prepare( 
			"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key LIKE %s", 
			'ennu_%' 
		) );

		?>
		<div class="ennu-metric-card">
			<div class="ennu-metric-value"><?php echo esc_html( $ennu_meta_count ); ?></div>
			<div class="ennu-metric-label"><?php echo esc_html__( 'ENNU User Meta Entries', 'ennulifeassessments' ); ?></div>
		</div>

		<div class="ennu-metric-card">
			<div class="ennu-metric-value"><?php echo esc_html( count( array_filter( $table_status ) ) ); ?>/<?php echo esc_html( count( $table_status ) ); ?></div>
			<div class="ennu-metric-label"><?php echo esc_html__( 'Tables Status', 'ennulifeassessments' ); ?></div>
		</div>
		<?php
	}

	/**
	 * Format bytes to human readable format
	 *
	 * @param int $bytes Bytes to format.
	 * @return string
	 */
	private function format_bytes( $bytes ) {
		$units = array( 'B', 'KB', 'MB', 'GB' );
		$bytes = max( $bytes, 0 );
		$pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow = min( $pow, count( $units ) - 1 );

		$bytes /= pow( 1024, $pow );

		return round( $bytes, 2 ) . ' ' . $units[ $pow ];
	}

	/**
	 * Get monitoring data for API
	 *
	 * @return array
	 */
	public function get_monitoring_data() {
		$metrics = get_option( 'ennu_monitoring_metrics', array() );
		$errors = get_option( 'ennu_monitoring_errors', array() );

		return array(
			'metrics' => $metrics,
			'errors' => array_slice( $errors, -50 ), // Last 50 errors
			'timestamp' => current_time( 'mysql' ),
		);
	}

	/**
	 * Clear monitoring data
	 */
	public function clear_monitoring_data() {
		delete_option( 'ennu_monitoring_metrics' );
		delete_option( 'ennu_monitoring_errors' );
	}
}

// Initialize monitoring
ENNU_Monitoring::get_instance(); 