<?php
/**
 * ENNU Performance Monitor
 * Tracks and optimizes plugin performance
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Performance_Monitor {
    
    private static $instance = null;
    private $start_time;
    private $queries_start;
    private $memory_start;
    private $metrics = array();
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->start_monitoring();
        add_action( 'wp_footer', array( $this, 'output_debug_info' ) );
        add_action( 'admin_footer', array( $this, 'output_debug_info' ) );
    }
    
    /**
     * Start performance monitoring
     */
    public function start_monitoring() {
        $this->start_time = microtime( true );
        $this->memory_start = memory_get_usage( true );
        
        global $wpdb;
        $this->queries_start = $wpdb->num_queries;
    }
    
    /**
     * Record a performance metric
     */
    public function record_metric( $name, $value, $type = 'time' ) {
        $this->metrics[ $name ] = array(
            'value' => $value,
            'type' => $type,
            'timestamp' => microtime( true )
        );
    }
    
    /**
     * Start timing an operation
     */
    public function start_timer( $name ) {
        $this->metrics[ $name . '_start' ] = microtime( true );
    }
    
    /**
     * End timing an operation
     */
    public function end_timer( $name ) {
        if ( isset( $this->metrics[ $name . '_start' ] ) ) {
            $duration = microtime( true ) - $this->metrics[ $name . '_start' ];
            $this->record_metric( $name, $duration, 'time' );
            unset( $this->metrics[ $name . '_start' ] );
        }
    }
    
    /**
     * Get current performance stats
     */
    public function get_stats() {
        global $wpdb;
        
        $current_time = microtime( true );
        $current_memory = memory_get_usage( true );
        
        return array(
            'total_time' => $current_time - $this->start_time,
            'memory_usage' => $current_memory - $this->memory_start,
            'peak_memory' => memory_get_peak_usage( true ),
            'queries_count' => $wpdb->num_queries - $this->queries_start,
            'custom_metrics' => $this->metrics
        );
    }
    
    /**
     * Output debug information
     */
    public function output_debug_info() {
        if ( ! current_user_can( 'manage_options' ) || ! WP_DEBUG ) {
            return;
        }
        
        $stats = $this->get_stats();
        
        echo '<!-- ENNU Performance Stats -->';
        echo '<!-- Total Time: ' . round( $stats['total_time'] * 1000, 2 ) . 'ms -->';
        echo '<!-- Memory Usage: ' . $this->format_bytes( $stats['memory_usage'] ) . ' -->';
        echo '<!-- Peak Memory: ' . $this->format_bytes( $stats['peak_memory'] ) . ' -->';
        echo '<!-- Database Queries: ' . $stats['queries_count'] . ' -->';
        
        if ( ! empty( $stats['custom_metrics'] ) ) {
            echo '<!-- Custom Metrics: -->';
            foreach ( $stats['custom_metrics'] as $name => $metric ) {
                if ( $metric['type'] === 'time' ) {
                    echo '<!-- ' . $name . ': ' . round( $metric['value'] * 1000, 2 ) . 'ms -->';
                } else {
                    echo '<!-- ' . $name . ': ' . $metric['value'] . ' -->';
                }
            }
        }
    }
    
    /**
     * Format bytes for display
     */
    private function format_bytes( $bytes, $precision = 2 ) {
        $units = array( 'B', 'KB', 'MB', 'GB', 'TB' );
        
        for ( $i = 0; $bytes > 1024 && $i < count( $units ) - 1; $i++ ) {
            $bytes /= 1024;
        }
        
        return round( $bytes, $precision ) . ' ' . $units[ $i ];
    }
    
    /**
     * Log slow queries
     */
    public function log_slow_query( $query, $execution_time ) {
        if ( $execution_time > 0.1 ) {
            error_log( sprintf(
                'ENNU Slow Query (%s seconds): %s',
                round( $execution_time, 4 ),
                $query
            ) );
        }
    }
    
    /**
     * Optimize database queries
     */
    public function suggest_optimizations() {
        global $wpdb;
        
        $suggestions = array();
        
        if ( $wpdb->num_queries > 50 ) {
            $suggestions[] = 'Consider implementing query caching - detected ' . $wpdb->num_queries . ' queries';
        }
        
        $memory_usage = memory_get_peak_usage( true );
        if ( $memory_usage > 64 * 1024 * 1024 ) {
            $suggestions[] = 'High memory usage detected: ' . $this->format_bytes( $memory_usage );
        }
        
        return $suggestions;
    }
}

if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    ENNU_Performance_Monitor::get_instance();
}
