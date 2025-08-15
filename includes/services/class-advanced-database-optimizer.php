<?php
/**
 * ENNU Advanced Database Optimizer
 *
 * Advanced database query optimization and performance enhancement
 *
 * @package ENNU_Life_Assessments
 * @since 64.17.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Advanced Database Optimizer Class
 *
 * @since 64.17.0
 */
class ENNU_Advanced_Database_Optimizer {
	
	/**
	 * Query cache
	 *
	 * @var array
	 */
	private $query_cache = array();
	
	/**
	 * Performance metrics
	 *
	 * @var array
	 */
	private $performance_metrics = array();
	
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'wp_ajax_ennu_optimize_database', array( $this, 'optimize_database' ) );
		add_action( 'wp_ajax_nopriv_ennu_optimize_database', array( $this, 'optimize_database' ) );
	}
	
	/**
	 * Initialize database optimizer
	 */
	public function init() {
		// Add query monitoring
		add_filter( 'query', array( $this, 'monitor_query' ) );
		
		// Add database optimization hooks
		add_action( 'wp_loaded', array( $this, 'check_database_health' ) );
		
		// Add admin menu
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		
		// REMOVED: // REMOVED DEBUG LOG: error_log( 'ENNU Advanced Database Optimizer: Initialized successfully' );
	}
	
	/**
	 * Monitor database queries
	 *
	 * @param string $query SQL query
	 * @return string Modified query
	 */
	public function monitor_query( $query ) {
		$start_time = microtime( true );
		$start_memory = memory_get_usage();
		
		// Store query for analysis
		$this->store_query_metrics( $query, $start_time, $start_memory );
		
		return $query;
	}
	
	/**
	 * Store query metrics
	 *
	 * @param string $query SQL query
	 * @param float $start_time Start time
	 * @param int $start_memory Start memory usage
	 */
	private function store_query_metrics( $query, $start_time, $start_memory ) {
		$query_hash = md5( $query );
		
		if ( ! isset( $this->query_cache[ $query_hash ] ) ) {
			$this->query_cache[ $query_hash ] = array(
				'query' => $query,
				'count' => 0,
				'total_time' => 0,
				'total_memory' => 0,
				'avg_time' => 0,
				'avg_memory' => 0,
				'last_executed' => current_time( 'mysql' ),
			);
		}
		
		$this->query_cache[ $query_hash ]['count']++;
		$this->query_cache[ $query_hash ]['total_time'] += microtime( true ) - $start_time;
		$this->query_cache[ $query_hash ]['total_memory'] += memory_get_usage() - $start_memory;
		$this->query_cache[ $query_hash ]['avg_time'] = $this->query_cache[ $query_hash ]['total_time'] / $this->query_cache[ $query_hash ]['count'];
		$this->query_cache[ $query_hash ]['avg_memory'] = $this->query_cache[ $query_hash ]['total_memory'] / $this->query_cache[ $query_hash ]['count'];
		$this->query_cache[ $query_hash ]['last_executed'] = current_time( 'mysql' );
	}
	
	/**
	 * Check database health
	 */
	public function check_database_health() {
		global $wpdb;
		
		// Check for slow queries
		$slow_queries = $this->identify_slow_queries();
		
		if ( ! empty( $slow_queries ) ) {
			// REMOVED: error_log( 'ENNU Database Optimizer: Found ' . count( $slow_queries ) . ' slow queries' );
			$this->optimize_slow_queries( $slow_queries );
		}
		
		// Check for duplicate queries
		$duplicate_queries = $this->identify_duplicate_queries();
		
		if ( ! empty( $duplicate_queries ) ) {
			// REMOVED: error_log( 'ENNU Database Optimizer: Found ' . count( $duplicate_queries ) . ' duplicate queries' );
			$this->optimize_duplicate_queries( $duplicate_queries );
		}
		
		// Check database size
		$db_size = $this->get_database_size();
		if ( $db_size > 100 * 1024 * 1024 ) { // 100MB
			// REMOVED: error_log( 'ENNU Database Optimizer: Database size is ' . round( $db_size / 1024 / 1024, 2 ) . 'MB' );
			$this->suggest_database_cleanup();
		}
	}
	
	/**
	 * Identify slow queries
	 *
	 * @return array Slow queries
	 */
	private function identify_slow_queries() {
		$slow_queries = array();
		
		foreach ( $this->query_cache as $hash => $metrics ) {
			if ( $metrics['avg_time'] > 0.1 ) { // Queries taking more than 100ms
				$slow_queries[ $hash ] = $metrics;
			}
		}
		
		return $slow_queries;
	}
	
	/**
	 * Identify duplicate queries
	 *
	 * @return array Duplicate queries
	 */
	private function identify_duplicate_queries() {
		$duplicate_queries = array();
		
		foreach ( $this->query_cache as $hash => $metrics ) {
			if ( $metrics['count'] > 10 ) { // Queries executed more than 10 times
				$duplicate_queries[ $hash ] = $metrics;
			}
		}
		
		return $duplicate_queries;
	}
	
	/**
	 * Optimize slow queries
	 *
	 * @param array $slow_queries Slow queries
	 */
	private function optimize_slow_queries( $slow_queries ) {
		foreach ( $slow_queries as $hash => $metrics ) {
			$optimization = $this->suggest_query_optimization( $metrics['query'] );
			
			if ( $optimization ) {
				// REMOVED: error_log( "ENNU Database Optimizer: Query optimization suggested for query: {$optimization}" );
			}
		}
	}
	
	/**
	 * Optimize duplicate queries
	 *
	 * @param array $duplicate_queries Duplicate queries
	 */
	private function optimize_duplicate_queries( $duplicate_queries ) {
		foreach ( $duplicate_queries as $hash => $metrics ) {
			$caching_suggestion = $this->suggest_caching_strategy( $metrics['query'] );
			
			if ( $caching_suggestion ) {
				// REMOVED: error_log( "ENNU Database Optimizer: Caching suggested for query: {$caching_suggestion}" );
			}
		}
	}
	
	/**
	 * Suggest query optimization
	 *
	 * @param string $query SQL query
	 * @return string Optimization suggestion
	 */
	private function suggest_query_optimization( $query ) {
		$suggestions = array();
		
		// Check for missing indexes
		if ( preg_match( '/WHERE\s+(\w+)\s*=/i', $query, $matches ) ) {
			$column = $matches[1];
			$suggestions[] = "Consider adding index on column: {$column}";
		}
		
		// Check for SELECT *
		if ( preg_match( '/SELECT\s+\*/i', $query ) ) {
			$suggestions[] = "Consider selecting specific columns instead of SELECT *";
		}
		
		// Check for ORDER BY without LIMIT
		if ( preg_match( '/ORDER BY/i', $query ) && ! preg_match( '/LIMIT/i', $query ) ) {
			$suggestions[] = "Consider adding LIMIT clause to ORDER BY queries";
		}
		
		// Check for nested queries
		if ( preg_match( '/SELECT.*SELECT/i', $query ) ) {
			$suggestions[] = "Consider using JOIN instead of nested SELECT";
		}
		
		return implode( '; ', $suggestions );
	}
	
	/**
	 * Suggest caching strategy
	 *
	 * @param string $query SQL query
	 * @return string Caching suggestion
	 */
	private function suggest_caching_strategy( $query ) {
		if ( preg_match( '/SELECT/i', $query ) && ! preg_match( '/WHERE.*NOW\(\)/i', $query ) ) {
			return "Consider implementing object caching for this SELECT query";
		}
		
		return null;
	}
	
	/**
	 * Get database size
	 *
	 * @return int Database size in bytes
	 */
	private function get_database_size() {
		global $wpdb;
		
		$size_query = "SELECT SUM(data_length + index_length) AS size FROM information_schema.tables WHERE table_schema = DATABASE()";
		$result = $wpdb->get_var( $size_query );
		
		return intval( $result );
	}
	
	/**
	 * Suggest database cleanup
	 */
	private function suggest_database_cleanup() {
		$suggestions = array(
			'Clean up old post revisions',
			'Remove unused post meta',
			'Clean up expired transients',
			'Optimize database tables',
			'Remove unused user meta',
		);
		
		// REMOVED: error_log( 'ENNU Database Optimizer: Database cleanup suggestions: ' . implode( ', ', $suggestions ) );
	}
	
	/**
	 * Optimize database tables
	 */
	public function optimize_database_tables() {
		global $wpdb;
		
		$tables = array(
			$wpdb->posts,
			$wpdb->postmeta,
			$wpdb->users,
			$wpdb->usermeta,
			$wpdb->options,
		);
		
		$optimized_tables = array();
		
		foreach ( $tables as $table ) {
			$result = $wpdb->query( "OPTIMIZE TABLE {$table}" );
			if ( $result !== false ) {
				$optimized_tables[] = $table;
			}
		}
		
		// REMOVED: error_log( 'ENNU Database Optimizer: Optimized tables: ' . implode( ', ', $optimized_tables ) );
		
		return $optimized_tables;
	}
	
	/**
	 * Clean up database
	 */
	public function cleanup_database() {
		global $wpdb;
		
		$cleanup_results = array();
		
		// Clean up post revisions
		$revisions_deleted = $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'revision'" );
		$cleanup_results['revisions'] = $revisions_deleted;
		
		// Clean up expired transients
		$transients_deleted = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' AND option_value < " . time() );
		$cleanup_results['transients'] = $transients_deleted;
		
		// Clean up orphaned post meta
		$orphaned_meta_deleted = $wpdb->query( "DELETE pm FROM {$wpdb->postmeta} pm LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID WHERE p.ID IS NULL" );
		$cleanup_results['orphaned_meta'] = $orphaned_meta_deleted;
		
		// REMOVED: error_log( 'ENNU Database Optimizer: Cleanup results: ' . json_encode( $cleanup_results ) );
		
		return $cleanup_results;
	}
	
	/**
	 * Get performance metrics
	 *
	 * @return array Performance metrics
	 */
	public function get_performance_metrics() {
		$total_queries = 0;
		$total_time = 0;
		$total_memory = 0;
		$slow_queries = 0;
		$duplicate_queries = 0;
		
		foreach ( $this->query_cache as $metrics ) {
			$total_queries += $metrics['count'];
			$total_time += $metrics['total_time'];
			$total_memory += $metrics['total_memory'];
			
			if ( $metrics['avg_time'] > 0.1 ) {
				$slow_queries++;
			}
			
			if ( $metrics['count'] > 10 ) {
				$duplicate_queries++;
			}
		}
		
		return array(
			'total_queries' => $total_queries,
			'total_time' => $total_time,
			'total_memory' => $total_memory,
			'avg_query_time' => $total_queries > 0 ? $total_time / $total_queries : 0,
			'avg_query_memory' => $total_queries > 0 ? $total_memory / $total_queries : 0,
			'slow_queries' => $slow_queries,
			'duplicate_queries' => $duplicate_queries,
			'database_size' => $this->get_database_size(),
		);
	}
	
	/**
	 * AJAX handler for database optimization
	 */
	public function optimize_database() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_database_optimization' ) ) {
			wp_die( 'Security check failed' );
		}
		
		$action = isset( $_POST['action_type'] ) ? sanitize_text_field( $_POST['action_type'] ) : '';
		
		switch ( $action ) {
			case 'optimize_tables':
				$result = $this->optimize_database_tables();
				break;
				
			case 'cleanup_database':
				$result = $this->cleanup_database();
				break;
				
			case 'get_metrics':
				$result = $this->get_performance_metrics();
				break;
				
			default:
				$result = array( 'error' => 'Invalid action' );
		}
		
		wp_send_json_success( $result );
	}
	
	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'ennu-life-assessments',
			'Database Optimizer',
			'Database Optimizer',
			'manage_options',
			'ennu-database-optimizer',
			array( $this, 'admin_page' )
		);
	}
	
	/**
	 * Admin page
	 */
	public function admin_page() {
		$metrics = $this->get_performance_metrics();
		?>
		<div class="wrap">
			<h1>ENNU Advanced Database Optimizer</h1>
			<p>Advanced database query optimization and performance enhancement.</p>
			
			<div class="database-metrics">
				<h2>Database Performance Metrics</h2>
				<table class="widefat">
					<thead>
						<tr>
							<th>Metric</th>
							<th>Value</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Total Queries</td>
							<td><?php echo esc_html( $metrics['total_queries'] ); ?></td>
						</tr>
						<tr>
							<td>Average Query Time</td>
							<td><?php echo esc_html( round( $metrics['avg_query_time'] * 1000, 2 ) ); ?>ms</td>
						</tr>
						<tr>
							<td>Average Query Memory</td>
							<td><?php echo esc_html( round( $metrics['avg_query_memory'] / 1024, 2 ) ); ?>KB</td>
						</tr>
						<tr>
							<td>Slow Queries</td>
							<td><?php echo esc_html( $metrics['slow_queries'] ); ?></td>
						</tr>
						<tr>
							<td>Duplicate Queries</td>
							<td><?php echo esc_html( $metrics['duplicate_queries'] ); ?></td>
						</tr>
						<tr>
							<td>Database Size</td>
							<td><?php echo esc_html( round( $metrics['database_size'] / 1024 / 1024, 2 ) ); ?>MB</td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<div class="optimization-actions">
				<h2>Optimization Actions</h2>
				<p>
					<button id="optimize-tables" class="button button-primary">Optimize Database Tables</button>
					<button id="cleanup-database" class="button button-secondary">Clean Up Database</button>
					<button id="refresh-metrics" class="button button-secondary">Refresh Metrics</button>
				</p>
			</div>
			
			<div id="optimization-results" style="display: none;">
				<h2>Optimization Results</h2>
				<div id="results-content"></div>
			</div>
		</div>
		
		<script>
		jQuery(document).ready(function($) {
			$('#optimize-tables').on('click', function() {
				performOptimization('optimize_tables');
			});
			
			$('#cleanup-database').on('click', function() {
				performOptimization('cleanup_database');
			});
			
			$('#refresh-metrics').on('click', function() {
				performOptimization('get_metrics');
			});
			
			function performOptimization(action) {
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'ennu_optimize_database',
						action_type: action,
						nonce: '<?php echo wp_create_nonce( 'ennu_database_optimization' ); ?>'
					},
					success: function(response) {
						if (response.success) {
							$('#results-content').html(JSON.stringify(response.data, null, 2));
							$('#optimization-results').show();
						}
					}
				});
			}
		});
		</script>
		<?php
	}
} 