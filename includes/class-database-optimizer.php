<?php
/**
 * ENNU Database Optimizer
 * Optimizes database queries for better performance
 *
 * @package ENNU_Life_Assessments
 * @version 62.28.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database optimization system for ENNU Life Assessments
 */
class ENNU_Database_Optimizer {
	
	private static $instance = null;
	private $performance_log = array();
	
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Get user assessment data with optimized batch query
	 */
	public function get_user_assessment_data_optimized( $user_id, $assessment_type ) {
		$start_time = microtime( true );
		
		// Get all user meta in single query
		$all_user_meta = get_user_meta( $user_id );
		
		// Filter relevant assessment data
		$assessment_data = array();
		$prefix = 'ennu_' . $assessment_type . '_';
		
		foreach ( $all_user_meta as $meta_key => $meta_values ) {
			if ( strpos( $meta_key, $prefix ) === 0 ) {
				$question_id = str_replace( $prefix, '', $meta_key );
				$assessment_data[$question_id] = $meta_values[0];
			}
		}
		
		$execution_time = microtime( true ) - $start_time;
		$this->log_performance( 'get_user_assessment_data_optimized', $execution_time, $user_id );
		
		return $assessment_data;
	}
	
	/**
	 * Batch save assessment data with optimized queries
	 */
	public function batch_save_assessment_data( $user_id, $assessment_type, $data ) {
		$start_time = microtime( true );
		
		// Prepare batch update
		$updates = array();
		foreach ( $data as $key => $value ) {
			$meta_key = 'ennu_' . $assessment_type . '_' . $key;
			$updates[$meta_key] = $value;
		}
		
		// Execute batch update
		$success = $this->batch_update_user_meta( $user_id, $updates );
		
		$execution_time = microtime( true ) - $start_time;
		$this->log_performance( 'batch_save_assessment_data', $execution_time, $user_id );
		
		return $success;
	}
	
	/**
	 * Batch update user meta with optimized queries
	 */
	private function batch_update_user_meta( $user_id, $updates ) {
		global $wpdb;
		
		if ( empty( $updates ) ) {
			return true;
		}
		
		// Prepare batch query
		$values = array();
		$placeholders = array();
		
		foreach ( $updates as $meta_key => $meta_value ) {
			$values[] = $user_id;
			$values[] = $meta_key;
			$values[] = $meta_value;
			$placeholders[] = "(%d, %s, %s)";
		}
		
		$query = "INSERT INTO {$wpdb->usermeta} (user_id, meta_key, meta_value) VALUES " . implode( ', ', $placeholders );
		$query .= " ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value)";
		
		$result = $wpdb->query( $wpdb->prepare( $query, $values ) );
		
		return $result !== false;
	}
	
	/**
	 * Get cached scores with intelligent caching
	 */
	public function get_cached_scores( $user_id ) {
		$cache_key = 'ennu_scores_' . $user_id;
		$cached_scores = wp_cache_get( $cache_key );
		
		if ( $cached_scores === false ) {
			$scores = $this->calculate_fresh_scores( $user_id );
			wp_cache_set( $cache_key, $scores, '', 3600 ); // 1 hour cache
			return $scores;
		}
		
		return $cached_scores;
	}
	
	/**
	 * Calculate fresh scores for user
	 */
	private function calculate_fresh_scores( $user_id ) {
		// This would integrate with the existing scoring system
		// For now, return empty array as placeholder
		return array();
	}
	
	/**
	 * Invalidate user cache
	 */
	public function invalidate_user_cache( $user_id ) {
		$cache_keys = array(
			'ennu_scores_' . $user_id,
			'ennu_symptoms_' . $user_id,
			'ennu_biomarkers_' . $user_id,
			'ennu_assessment_data_' . $user_id
		);
		
		foreach ( $cache_keys as $key ) {
			wp_cache_delete( $key );
		}
	}
	
	/**
	 * Log performance metrics
	 */
	private function log_performance( $operation, $execution_time, $user_id = null ) {
		$performance_data = array(
			'operation' => $operation,
			'execution_time' => $execution_time,
			'user_id' => $user_id,
			'timestamp' => current_time( 'mysql' ),
			'memory_usage' => memory_get_usage( true ),
			'peak_memory' => memory_get_peak_usage( true )
		);
		
		$this->performance_log[] = $performance_data;
		
		// Log slow queries
		if ( $execution_time > 1.0 ) {
			error_log( "ENNU Slow Query: {$operation} took {$execution_time} seconds for user {$user_id}" );
		}
	}
	
	/**
	 * Get performance statistics
	 */
	public function get_performance_statistics() {
		$stats = array(
			'total_operations' => count( $this->performance_log ),
			'average_time' => 0,
			'slow_queries' => 0,
			'memory_usage' => 0
		);
		
		if ( ! empty( $this->performance_log ) ) {
			$total_time = 0;
			$total_memory = 0;
			
			foreach ( $this->performance_log as $log ) {
				$total_time += $log['execution_time'];
				$total_memory += $log['memory_usage'];
				
				if ( $log['execution_time'] > 1.0 ) {
					$stats['slow_queries']++;
				}
			}
			
			$stats['average_time'] = $total_time / count( $this->performance_log );
			$stats['memory_usage'] = $total_memory / count( $this->performance_log );
		}
		
		return $stats;
	}
	
	/**
	 * Clear performance log
	 */
	public function clear_performance_log() {
		$this->performance_log = array();
	}
}
