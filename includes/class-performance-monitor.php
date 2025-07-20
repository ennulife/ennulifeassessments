<?php
/**
 * Performance Monitor Class
 * 
 * Monitors and logs performance improvements from optimizations
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Performance_Monitor {
    
    private static $instance = null;
    private $timers = array();
    private $metrics = array();
    
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action( 'wp_footer', array( $this, 'output_performance_data' ) );
        add_action( 'admin_footer', array( $this, 'output_performance_data' ) );
    }
    
    public function start_timer( $name ) {
        $this->timers[ $name ] = array(
            'start' => microtime( true ),
            'memory_start' => memory_get_usage()
        );
    }
    
    public function end_timer( $name ) {
        if ( ! isset( $this->timers[ $name ] ) ) {
            return false;
        }
        
        $timer = $this->timers[ $name ];
        $execution_time = microtime( true ) - $timer['start'];
        $memory_usage = memory_get_usage() - $timer['memory_start'];
        
        $this->metrics[ $name ] = array(
            'execution_time' => $execution_time,
            'memory_usage' => $memory_usage,
            'timestamp' => time()
        );
        
        unset( $this->timers[ $name ] );
        
        return $this->metrics[ $name ];
    }
    
    public function log_database_query_count( $context, $query_count ) {
        $this->metrics[ $context . '_db_queries' ] = array(
            'query_count' => $query_count,
            'timestamp' => time()
        );
    }
    
    public function log_cache_hit_rate( $context, $hits, $misses ) {
        $total = $hits + $misses;
        $hit_rate = $total > 0 ? ( $hits / $total ) * 100 : 0;
        
        $this->metrics[ $context . '_cache_performance' ] = array(
            'hits' => $hits,
            'misses' => $misses,
            'hit_rate' => $hit_rate,
            'timestamp' => time()
        );
    }
    
    public function get_metrics() {
        return $this->metrics;
    }
    
    public function output_performance_data() {
        if ( ! current_user_can( 'manage_options' ) || ! WP_DEBUG ) {
            return;
        }
        
        if ( empty( $this->metrics ) ) {
            return;
        }
        
        echo '<!-- ENNU Performance Metrics -->' . PHP_EOL;
        echo '<script type="text/javascript">' . PHP_EOL;
        echo 'console.group("ENNU Performance Metrics");' . PHP_EOL;
        
        foreach ( $this->metrics as $name => $data ) {
            if ( isset( $data['execution_time'] ) ) {
                echo sprintf(
                    'console.log("⏱️ %s: %.2fms, Memory: %s");' . PHP_EOL,
                    esc_js( $name ),
                    $data['execution_time'] * 1000,
                    esc_js( $this->format_bytes( $data['memory_usage'] ) )
                );
            } elseif ( isset( $data['query_count'] ) ) {
                echo sprintf(
                    'console.log("🗄️ %s: %d queries");' . PHP_EOL,
                    esc_js( $name ),
                    $data['query_count']
                );
            } elseif ( isset( $data['hit_rate'] ) ) {
                echo sprintf(
                    'console.log("💾 %s: %.1f%% hit rate (%d hits, %d misses)");' . PHP_EOL,
                    esc_js( $name ),
                    $data['hit_rate'],
                    $data['hits'],
                    $data['misses']
                );
            }
        }
        
        echo 'console.groupEnd();' . PHP_EOL;
        echo '</script>' . PHP_EOL;
    }
    
    private function format_bytes( $bytes ) {
        if ( $bytes >= 1048576 ) {
            return round( $bytes / 1048576, 2 ) . ' MB';
        } elseif ( $bytes >= 1024 ) {
            return round( $bytes / 1024, 2 ) . ' KB';
        } else {
            return $bytes . ' B';
        }
    }
    
    public function save_metrics_to_log() {
        if ( empty( $this->metrics ) ) {
            return;
        }
        
        $log_entry = array(
            'timestamp' => current_time( 'mysql' ),
            'metrics' => $this->metrics,
            'url' => $_SERVER['REQUEST_URI'] ?? '',
            'user_id' => get_current_user_id()
        );
        
        error_log( 'ENNU Performance: ' . wp_json_encode( $log_entry ) );
    }
}

ENNU_Performance_Monitor::get_instance();
