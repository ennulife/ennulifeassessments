<?php
/**
 * Memory Optimizer for ENNU Life Plugin
 * 
 * Fixes memory exhaustion issues by optimizing memory usage and increasing limits
 * 
 * @package ENNU_Life_Assessments
 * @since 62.27.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_Memory_Optimizer {
    
    /**
     * Initialize the memory optimizer
     */
    public static function init() {
        add_action( 'init', array( __CLASS__, 'optimize_memory' ), 1 );
        add_action( 'wp_loaded', array( __CLASS__, 'check_memory_status' ) );
    }
    
    /**
     * Optimize memory settings
     */
    public static function optimize_memory() {
        // Increase memory limit if possible
        $current_limit = ini_get( 'memory_limit' );
        $current_bytes = self::convert_to_bytes( $current_limit );
        
        // If current limit is less than 512M, try to increase it
        if ( $current_bytes < 536870912 ) { // 512M in bytes
            $new_limit = '512M';
            
            // Try to set the new limit
            if ( wp_is_ini_value_changeable( 'memory_limit' ) ) {
                ini_set( 'memory_limit', $new_limit );
            }
            
            // Log the attempt
            error_log( "ENNU Memory Optimizer: Attempted to increase memory limit from {$current_limit} to {$new_limit}" );
        }
        
        // Set WordPress memory limits
        if ( ! defined( 'WP_MEMORY_LIMIT' ) ) {
            define( 'WP_MEMORY_LIMIT', '512M' );
        }
        
        if ( ! defined( 'WP_MAX_MEMORY_LIMIT' ) ) {
            define( 'WP_MAX_MEMORY_LIMIT', '512M' );
        }
        
        // Optimize garbage collection
        if ( function_exists( 'gc_enable' ) ) {
            gc_enable();
        }
    }
    
    /**
     * Check memory status and log warnings
     */
    public static function check_memory_status() {
        $current_limit = ini_get( 'memory_limit' );
        $current_usage = memory_get_usage( true );
        $current_peak = memory_get_peak_usage( true );
        $current_bytes = self::convert_to_bytes( $current_limit );
        
        // Log memory status
        error_log( "ENNU Memory Status: Limit: {$current_limit}, Usage: " . size_format( $current_usage ) . ", Peak: " . size_format( $current_peak ) );
        
        // Warn if usage is high
        if ( $current_usage > ( $current_bytes * 0.8 ) ) {
            error_log( "ENNU Memory Warning: High memory usage detected. Usage: " . size_format( $current_usage ) . " / {$current_limit}" );
        }
    }
    
    /**
     * Convert memory string to bytes
     */
    private static function convert_to_bytes( $memory_string ) {
        $memory_string = trim( $memory_string );
        $last = strtolower( substr( $memory_string, -1 ) );
        $value = (int) $memory_string;
        
        switch ( $last ) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * Force garbage collection
     */
    public static function force_garbage_collection() {
        if ( function_exists( 'gc_collect_cycles' ) ) {
            $collected = gc_collect_cycles();
            error_log( "ENNU Memory Optimizer: Garbage collection freed {$collected} cycles" );
            return $collected;
        }
        return 0;
    }
    
    /**
     * Get memory usage statistics
     */
    public static function get_memory_stats() {
        return array(
            'limit' => ini_get( 'memory_limit' ),
            'usage' => memory_get_usage( true ),
            'peak' => memory_get_peak_usage( true ),
            'free' => memory_get_usage( true ) - memory_get_usage( false ),
            'percentage' => ( memory_get_usage( true ) / self::convert_to_bytes( ini_get( 'memory_limit' ) ) ) * 100
        );
    }
}

// Initialize the memory optimizer
ENNU_Memory_Optimizer::init(); 