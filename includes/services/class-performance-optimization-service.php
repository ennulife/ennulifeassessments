<?php
/**
 * ENNU Performance Optimization Service
 *
 * Comprehensive performance optimization using strategy pattern
 *
 * @package ENNU_Life_Assessments
 * @since 64.15.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Performance Strategy Interface
 *
 * @since 64.15.0
 */
interface ENNU_Performance_Strategy_Interface {
	
	/**
	 * Optimize database queries
	 *
	 * @param string $query_type Query type to optimize
	 * @param array $query_data Query data
	 * @return array Optimization result
	 */
	public function optimize_database_queries( $query_type, $query_data );
	
	/**
	 * Optimize asset loading
	 *
	 * @param string $asset_type Asset type to optimize
	 * @param array $asset_data Asset data
	 * @return array Optimization result
	 */
	public function optimize_asset_loading( $asset_type, $asset_data );
	
	/**
	 * Optimize caching strategy
	 *
	 * @param string $cache_type Cache type to optimize
	 * @param array $cache_data Cache data
	 * @return array Optimization result
	 */
	public function optimize_caching_strategy( $cache_type, $cache_data );
	
	/**
	 * Optimize code execution
	 *
	 * @param string $execution_type Execution type to optimize
	 * @param array $execution_data Execution data
	 * @return array Optimization result
	 */
	public function optimize_code_execution( $execution_type, $execution_data );
	
	/**
	 * Get performance metrics
	 *
	 * @return array Performance metrics
	 */
	public function get_performance_metrics();
}

/**
 * ENNU Performance Optimization Service Class
 *
 * @since 64.15.0
 */
class ENNU_Performance_Optimization_Service {
	
	/**
	 * Performance strategies
	 *
	 * @var array
	 */
	private $strategies = array();
	
	/**
	 * Current strategy
	 *
	 * @var ENNU_Performance_Strategy_Interface
	 */
	private $current_strategy;
	
	/**
	 * Performance configuration
	 *
	 * @var array
	 */
	private $performance_config = array(
		'database_optimization' => true,
		'asset_optimization' => true,
		'caching_optimization' => true,
		'code_optimization' => true,
		'query_limit' => 1000,
		'cache_ttl' => 3600,
		'asset_minification' => true,
		'compression_enabled' => true,
		'memory_limit' => '256M',
		'execution_time_limit' => 30,
	);
	
	/**
	 * Performance monitoring data
	 *
	 * @var array
	 */
	private $performance_data = array(
		'execution_times' => array(),
		'memory_usage' => array(),
		'query_counts' => array(),
		'cache_hits' => array(),
		'cache_misses' => array(),
	);
	
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->register_strategies();
		$this->set_default_strategy();
		$this->init_performance_monitoring();
	}
	
	/**
	 * Register performance strategies
	 */
	private function register_strategies() {
		// Standard performance strategy
		$this->strategies['standard'] = new ENNU_Standard_Performance_Strategy();
		
		// Advanced performance strategy
		$this->strategies['advanced'] = new ENNU_Advanced_Performance_Strategy();
		
		// Minimal performance strategy
		$this->strategies['minimal'] = new ENNU_Minimal_Performance_Strategy();
	}
	
	/**
	 * Set default strategy
	 */
	private function set_default_strategy() {
		$this->current_strategy = $this->strategies['standard'];
	}
	
	/**
	 * Set performance strategy
	 *
	 * @param string $strategy_name Strategy name
	 * @return bool Success status
	 */
	public function set_strategy( $strategy_name ) {
		if ( ! isset( $this->strategies[ $strategy_name ] ) ) {
			error_log( "ENNU Performance Optimization Service: Unknown strategy '{$strategy_name}'" );
			return false;
		}
		
		$this->current_strategy = $this->strategies[ $strategy_name ];
		error_log( "ENNU Performance Optimization Service: Strategy set to '{$strategy_name}'" );
		return true;
	}
	
	/**
	 * Optimize database queries
	 *
	 * @param string $query_type Query type
	 * @param array $query_data Query data
	 * @return array Optimization result
	 */
	public function optimize_database_queries( $query_type, $query_data = array() ) {
		if ( ! $this->performance_config['database_optimization'] ) {
			return array( 'optimized' => false, 'message' => 'Database optimization disabled' );
		}
		
		$start_time = microtime( true );
		$start_memory = memory_get_usage();
		
		$result = $this->current_strategy->optimize_database_queries( $query_type, $query_data );
		
		$end_time = microtime( true );
		$end_memory = memory_get_usage();
		
		$this->log_performance_metric( 'database_optimization', array(
			'query_type' => $query_type,
			'execution_time' => $end_time - $start_time,
			'memory_usage' => $end_memory - $start_memory,
			'result' => $result,
		) );
		
		return $result;
	}
	
	/**
	 * Optimize asset loading
	 *
	 * @param string $asset_type Asset type
	 * @param array $asset_data Asset data
	 * @return array Optimization result
	 */
	public function optimize_asset_loading( $asset_type, $asset_data = array() ) {
		if ( ! $this->performance_config['asset_optimization'] ) {
			return array( 'optimized' => false, 'message' => 'Asset optimization disabled' );
		}
		
		$start_time = microtime( true );
		$start_memory = memory_get_usage();
		
		$result = $this->current_strategy->optimize_asset_loading( $asset_type, $asset_data );
		
		$end_time = microtime( true );
		$end_memory = memory_get_usage();
		
		$this->log_performance_metric( 'asset_optimization', array(
			'asset_type' => $asset_type,
			'execution_time' => $end_time - $start_time,
			'memory_usage' => $end_memory - $start_memory,
			'result' => $result,
		) );
		
		return $result;
	}
	
	/**
	 * Optimize caching strategy
	 *
	 * @param string $cache_type Cache type
	 * @param array $cache_data Cache data
	 * @return array Optimization result
	 */
	public function optimize_caching_strategy( $cache_type, $cache_data = array() ) {
		if ( ! $this->performance_config['caching_optimization'] ) {
			return array( 'optimized' => false, 'message' => 'Caching optimization disabled' );
		}
		
		$start_time = microtime( true );
		$start_memory = memory_get_usage();
		
		$result = $this->current_strategy->optimize_caching_strategy( $cache_type, $cache_data );
		
		$end_time = microtime( true );
		$end_memory = memory_get_usage();
		
		$this->log_performance_metric( 'caching_optimization', array(
			'cache_type' => $cache_type,
			'execution_time' => $end_time - $start_time,
			'memory_usage' => $end_memory - $start_memory,
			'result' => $result,
		) );
		
		return $result;
	}
	
	/**
	 * Optimize code execution
	 *
	 * @param string $execution_type Execution type
	 * @param array $execution_data Execution data
	 * @return array Optimization result
	 */
	public function optimize_code_execution( $execution_type, $execution_data = array() ) {
		if ( ! $this->performance_config['code_optimization'] ) {
			return array( 'optimized' => false, 'message' => 'Code optimization disabled' );
		}
		
		$start_time = microtime( true );
		$start_memory = memory_get_usage();
		
		$result = $this->current_strategy->optimize_code_execution( $execution_type, $execution_data );
		
		$end_time = microtime( true );
		$end_memory = memory_get_usage();
		
		$this->log_performance_metric( 'code_optimization', array(
			'execution_type' => $execution_type,
			'execution_time' => $end_time - $start_time,
			'memory_usage' => $end_memory - $start_memory,
			'result' => $result,
		) );
		
		return $result;
	}
	
	/**
	 * Get performance metrics
	 *
	 * @return array Performance metrics
	 */
	public function get_performance_metrics() {
		$metrics = $this->current_strategy->get_performance_metrics();
		
		// Add service-specific metrics
		$metrics['service_metrics'] = array(
			'total_optimizations' => count( $this->performance_data['execution_times'] ),
			'average_execution_time' => $this->calculate_average_execution_time(),
			'average_memory_usage' => $this->calculate_average_memory_usage(),
			'cache_efficiency' => $this->calculate_cache_efficiency(),
		);
		
		return $metrics;
	}
	
	/**
	 * Initialize performance monitoring
	 */
	private function init_performance_monitoring() {
		// Set memory limit
		if ( $this->performance_config['memory_limit'] ) {
			ini_set( 'memory_limit', $this->performance_config['memory_limit'] );
		}
		
		// Set execution time limit
		if ( $this->performance_config['execution_time_limit'] ) {
			set_time_limit( $this->performance_config['execution_time_limit'] );
		}
		
		// Enable compression if available
		if ( $this->performance_config['compression_enabled'] && function_exists( 'ob_gzhandler' ) ) {
			ob_start( 'ob_gzhandler' );
		}
		
		error_log( 'ENNU Performance Optimization Service: Performance monitoring initialized' );
	}
	
	/**
	 * Log performance metric
	 *
	 * @param string $metric_type Metric type
	 * @param array $metric_data Metric data
	 */
	private function log_performance_metric( $metric_type, $metric_data ) {
		$this->performance_data['execution_times'][] = $metric_data['execution_time'];
		$this->performance_data['memory_usage'][] = $metric_data['memory_usage'];
		
		error_log( "ENNU Performance Metric: {$metric_type} - Execution Time: {$metric_data['execution_time']}s, Memory: {$metric_data['memory_usage']} bytes" );
	}
	
	/**
	 * Calculate average execution time
	 *
	 * @return float Average execution time
	 */
	private function calculate_average_execution_time() {
		if ( empty( $this->performance_data['execution_times'] ) ) {
			return 0.0;
		}
		
		return array_sum( $this->performance_data['execution_times'] ) / count( $this->performance_data['execution_times'] );
	}
	
	/**
	 * Calculate average memory usage
	 *
	 * @return float Average memory usage
	 */
	private function calculate_average_memory_usage() {
		if ( empty( $this->performance_data['memory_usage'] ) ) {
			return 0.0;
		}
		
		return array_sum( $this->performance_data['memory_usage'] ) / count( $this->performance_data['memory_usage'] );
	}
	
	/**
	 * Calculate cache efficiency
	 *
	 * @return float Cache efficiency percentage
	 */
	private function calculate_cache_efficiency() {
		$hits = array_sum( $this->performance_data['cache_hits'] );
		$misses = array_sum( $this->performance_data['cache_misses'] );
		
		if ( $hits + $misses === 0 ) {
			return 0.0;
		}
		
		return ( $hits / ( $hits + $misses ) ) * 100;
	}
	
	/**
	 * Get performance configuration
	 *
	 * @return array Performance configuration
	 */
	public function get_performance_config() {
		return $this->performance_config;
	}
	
	/**
	 * Update performance configuration
	 *
	 * @param array $config New configuration
	 */
	public function update_performance_config( $config ) {
		$this->performance_config = array_merge( $this->performance_config, $config );
		error_log( 'ENNU Performance Optimization Service: Performance configuration updated' );
	}
	
	/**
	 * Initialize performance service
	 */
	public function init() {
		// Register performance hooks
		add_action( 'wp_enqueue_scripts', array( $this, 'optimize_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'optimize_admin_assets' ) );
		add_action( 'wp_head', array( $this, 'add_performance_headers' ) );
		add_action( 'wp_footer', array( $this, 'log_performance_summary' ) );
		
		error_log( 'ENNU Performance Optimization Service: Initialized successfully' );
	}
	
	/**
	 * Optimize frontend assets
	 */
	public function optimize_frontend_assets() {
		// Optimize CSS loading
		$this->optimize_asset_loading( 'css', array(
			'minify' => $this->performance_config['asset_minification'],
			'combine' => true,
			'defer' => false,
		) );
		
		// Optimize JavaScript loading
		$this->optimize_asset_loading( 'js', array(
			'minify' => $this->performance_config['asset_minification'],
			'combine' => true,
			'defer' => true,
		) );
	}
	
	/**
	 * Optimize admin assets
	 */
	public function optimize_admin_assets() {
		// Optimize admin CSS
		$this->optimize_asset_loading( 'admin_css', array(
			'minify' => $this->performance_config['asset_minification'],
			'combine' => false,
			'defer' => false,
		) );
		
		// Optimize admin JavaScript
		$this->optimize_asset_loading( 'admin_js', array(
			'minify' => $this->performance_config['asset_minification'],
			'combine' => false,
			'defer' => true,
		) );
	}
	
	/**
	 * Add performance headers
	 */
	public function add_performance_headers() {
		// Add performance headers
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		
		// Add caching headers
		header( 'Cache-Control: public, max-age=3600' );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() + 3600 ) );
	}
	
	/**
	 * Log performance summary
	 */
	public function log_performance_summary() {
		$metrics = $this->get_performance_metrics();
		
		error_log( 'ENNU Performance Summary: ' . json_encode( $metrics ) );
	}
} 