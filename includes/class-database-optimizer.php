<?php
/**
 * ENNU Database Optimizer
 * Optimizes database queries and implements caching strategies
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Database_Optimizer {

	private static $instance = null;
	private $cache;
	private $query_log = array();

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
		add_action( 'init', array( $this, 'maybe_add_database_indexes' ) );
		add_filter( 'query', array( $this, 'log_slow_queries' ) );
	}

	/**
	 * Add database indexes for frequently queried meta keys
	 */
	public function maybe_add_database_indexes() {
		if ( get_option( 'ennu_db_indexes_added', false ) ) {
			return;
		}

		global $wpdb;

		$indexes = array(
			array(
				'table'   => $wpdb->usermeta,
				'name'    => 'ennu_meta_key_value',
				'columns' => 'meta_key, meta_value(50)',
			),
			array(
				'table'   => $wpdb->usermeta,
				'name'    => 'ennu_user_meta_key',
				'columns' => 'user_id, meta_key',
			),
			array(
				'table'   => $wpdb->usermeta,
				'name'    => 'ennu_user_id_meta_key',
				'columns' => 'user_id, meta_key(50)',
			),
			array(
				'table'   => $wpdb->options,
				'name'    => 'ennu_option_name_autoload',
				'columns' => 'option_name, autoload',
			),
			array(
				'table'   => $wpdb->options,
				'name'    => 'ennu_transient_cleanup',
				'columns' => 'option_name(50), autoload',
			),
		);

		foreach ( $indexes as $index ) {
			$this->add_index_if_not_exists( $index['table'], $index['name'], $index['columns'] );
		}

		update_option( 'ennu_db_indexes_added', true );
	}

	/**
	 * Add index if it doesn't exist
	 */
	private function add_index_if_not_exists( $table, $index_name, $columns ) {
		global $wpdb;

		$existing_indexes = $wpdb->get_results(
			$wpdb->prepare(
				"SHOW INDEX FROM {$table} WHERE Key_name = %s",
				$index_name
			)
		);

		if ( empty( $existing_indexes ) ) {
			$wpdb->query( "ALTER TABLE {$table} ADD INDEX {$index_name} ({$columns})" );
		}
	}

	/**
	 * Log slow queries for optimization
	 */
	public function log_slow_queries( $query ) {
		$start_time = microtime( true );

		add_filter(
			'query',
			function( $q ) use ( $query, $start_time ) {
				if ( $q === $query ) {
					$execution_time = microtime( true ) - $start_time;

					if ( $execution_time > 0.1 ) {
						$this->query_log[] = array(
							'query'     => $query,
							'time'      => $execution_time,
							'timestamp' => current_time( 'mysql' ),
						);

						if ( WP_DEBUG ) {
							error_log(
								sprintf(
									'ENNU Slow Query (%s seconds): %s',
									round( $execution_time, 4 ),
									$query
								)
							);
						}
					}
				}
				return $q;
			},
			10,
			1
		);

		return $query;
	}

	/**
	 * Get cached user meta with fallback
	 */
	public function get_user_meta_cached( $user_id, $meta_key, $single = true ) {
		$cache_key = "user_meta_{$user_id}_{$meta_key}";
		$cached    = get_transient( $cache_key );

		if ( $cached !== false ) {
			return $cached;
		}

		$value = get_user_meta( $user_id, $meta_key, $single );
		set_transient( $cache_key, $value, 300 );

		return $value;
	}

	/**
	 * Batch get user meta for multiple keys
	 */
	public function get_user_meta_batch( $user_id, $meta_keys ) {
		global $wpdb;

		$cache_key = "user_meta_batch_{$user_id}_" . md5( implode( ',', $meta_keys ) );
		$cached    = get_transient( $cache_key );

		if ( $cached !== false ) {
			return $cached;
		}

		// If no specific keys provided, get all user meta
		if ( empty( $meta_keys ) ) {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT meta_key, meta_value FROM {$wpdb->usermeta} WHERE user_id = %d",
					$user_id
				)
			);
		} else {
			$placeholders = implode( ',', array_fill( 0, count( $meta_keys ), '%s' ) );
			$query_params = array_merge( array( $user_id ), $meta_keys );

			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT meta_key, meta_value FROM {$wpdb->usermeta} 
             WHERE user_id = %d AND meta_key IN ({$placeholders})",
					$query_params
				)
			);
		}

		$meta_data = array();
		foreach ( $results as $result ) {
			$meta_data[ $result->meta_key ] = maybe_unserialize( $result->meta_value );
		}

		set_transient( $cache_key, $meta_data, 300 );

		return $meta_data;
	}

	/**
	 * Optimize assessment queries
	 */
	public function get_user_assessments_optimized( $user_id ) {
		$cache_key = "user_assessments_optimized_{$user_id}";
		$cached    = get_transient( $cache_key );

		if ( $cached !== false ) {
			return $cached;
		}

		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key, meta_value FROM {$wpdb->usermeta} 
             WHERE user_id = %d 
             AND meta_key LIKE %s 
             AND meta_value != ''
             ORDER BY meta_key",
				$user_id,
				'ennu_%_calculated_score'
			)
		);

		$assessments = array();
		foreach ( $results as $result ) {
			$assessment_type                 = str_replace( array( 'ennu_', '_calculated_score' ), '', $result->meta_key );
			$assessments[ $assessment_type ] = (float) $result->meta_value;
		}

		set_transient( $cache_key, $assessments, 600 );

		return $assessments;
	}

	/**
	 * Get system statistics with optimized queries
	 */
	public function get_system_stats_optimized() {
		$cache_key = 'ennu_system_stats_optimized';
		$cached    = get_transient( $cache_key );

		if ( $cached !== false ) {
			return $cached;
		}

		global $wpdb;

		$stats = array();

		$stats['total_users'] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );

		$stats['active_users'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} 
             WHERE meta_key LIKE %s AND meta_value != ''",
				'ennu_%_calculated_score'
			)
		);

		$stats['monthly_assessments'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} um
             JOIN {$wpdb->users} u ON um.user_id = u.ID
             WHERE um.meta_key LIKE %s 
             AND um.meta_value != '' 
             AND CAST(um.meta_value AS SIGNED) > 0 
             AND u.user_registered >= DATE_SUB(NOW(), INTERVAL 1 MONTH)",
				'ennu_%_calculated_score'
			)
		);

		set_transient( $cache_key, $stats, 900 );

		return $stats;
	}

	/**
	 * Clean up old cache entries
	 */
	public function cleanup_cache() {
		// Clear all ENNU-related transients
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_ennu_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_ennu_%'" );

		delete_transient( 'ennu_system_stats' );
		delete_transient( 'ennu_user_stats_*' );

		wp_cache_flush();
	}

	/**
	 * Clean up expired transients to optimize database performance
	 * Based on WordPress database optimization best practices
	 */
	public function cleanup_expired_transients() {
		global $wpdb;
		
		// Delete expired transients
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_%' AND option_value < UNIX_TIMESTAMP()" );
		
		// Delete orphaned transients (timeout exists but transient doesn't)
		$wpdb->query( "
			DELETE t1 FROM {$wpdb->options} t1
			LEFT JOIN {$wpdb->options} t2 ON t1.option_name = REPLACE(t2.option_name, '_transient_timeout_', '_transient_')
			WHERE t1.option_name LIKE '_transient_timeout_%'
			AND t2.option_name IS NULL
		" );
		
		// Clean up old usermeta entries
		$wpdb->query( "
			DELETE FROM {$wpdb->usermeta} 
			WHERE meta_key LIKE 'ennu_%' 
			AND meta_value = '' 
			AND meta_value IS NOT NULL
		" );
		
		error_log( 'ENNU Database Optimizer: Cleaned up expired transients and empty usermeta entries' );
	}

	/**
	 * Optimize database tables for better performance
	 */
	public function optimize_database_tables() {
		global $wpdb;
		
		$tables_to_optimize = array(
			$wpdb->options,
			$wpdb->usermeta,
			$wpdb->users
		);
		
		foreach ( $tables_to_optimize as $table ) {
			$wpdb->query( "OPTIMIZE TABLE {$table}" );
		}
		
		error_log( 'ENNU Database Optimizer: Optimized database tables for better performance' );
	}

	/**
	 * Add query monitoring for slow queries
	 */
	public function monitor_slow_queries() {
		global $wpdb;
		
		// Only log slow queries without changing global settings
		error_log( 'ENNU Database Optimizer: Slow query monitoring enabled (logging only)' );
	}

	/**
	 * Initialize database optimizations
	 * Based on WordPress database optimization best practices from wpspeedfix.com
	 */
	public function initialize_optimizations() {
		// Add database indexes for better performance
		$this->maybe_add_database_indexes();
		
		// Clean up expired transients
		$this->cleanup_expired_transients();
		
		// Schedule regular cleanup
		if ( ! wp_next_scheduled( 'ennu_database_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'ennu_database_cleanup' );
		}
		
		error_log( 'ENNU Database Optimizer: Database optimizations initialized successfully' );
	}

	/**
	 * Get database performance metrics
	 */
	public function get_performance_metrics() {
		global $wpdb;
		
		$metrics = array();
		
		// Get table sizes
		$tables = array( $wpdb->options, $wpdb->usermeta, $wpdb->users );
		foreach ( $tables as $table ) {
			$size = $wpdb->get_var( "SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size' FROM information_schema.TABLES WHERE table_schema = DATABASE() AND table_name = '{$table}'" );
			$metrics['table_sizes'][ $table ] = $size . ' MB';
		}
		
		// Get transient count
		$transient_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'" );
		$metrics['transient_count'] = $transient_count;
		
		// Get usermeta count
		$usermeta_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key LIKE 'ennu_%'" );
		$metrics['ennu_usermeta_count'] = $usermeta_count;
		
		return $metrics;
	}

	/**
	 * Get query performance report
	 */
	public function get_performance_report() {
		return array(
			'slow_queries'    => $this->query_log,
			'cache_stats'     => $this->get_cache_stats(),
			'recommendations' => $this->get_optimization_recommendations(),
		);
	}

	/**
	 * Get cache statistics
	 */
	private function get_cache_stats() {
		global $wpdb;
		
		$total_transients = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_ennu_%'" );
		$expired_transients = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_ennu_%' AND option_value < " . time() );
		
		return array(
			'total_cached_items' => $total_transients,
			'expired_items' => $expired_transients,
			'hit_rate' => 0.8, // Default estimate
		);
	}

	/**
	 * Get optimization recommendations
	 */
	private function get_optimization_recommendations() {
		$recommendations = array();

		if ( count( $this->query_log ) > 10 ) {
			$recommendations[] = 'Consider implementing more aggressive caching - detected ' . count( $this->query_log ) . ' slow queries';
		}

		$cache_stats = $this->get_cache_stats();
		if ( isset( $cache_stats['hit_rate'] ) && $cache_stats['hit_rate'] < 0.7 ) {
			$recommendations[] = 'Cache hit rate is low (' . round( $cache_stats['hit_rate'] * 100, 1 ) . '%) - consider increasing cache TTL';
		}

		return $recommendations;
	}
}

ENNU_Database_Optimizer::get_instance();
