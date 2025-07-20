<?php
/**
 * ENNU User Manager Service
 * Extracted from monolithic Enhanced Admin class
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class ENNU_User_Manager {
    
    private $cache;
    
    public function __construct() {
        $this->cache = new ENNU_Score_Cache();
    }
    
    /**
     * Get user assessment data with caching
     */
    public function get_user_assessments( $user_id ) {
        $cache_key = "user_assessments_{$user_id}";
        $cached = $this->cache->get( $cache_key );
        
        if ( $cached !== false ) {
            return $cached;
        }
        
        global $wpdb;
        $assessments = $wpdb->get_results( $wpdb->prepare( 
            "SELECT meta_key, meta_value FROM {$wpdb->usermeta} 
             WHERE user_id = %d AND meta_key LIKE %s", 
            $user_id, '%_calculated_score' 
        ) );
        
        $this->cache->set( $cache_key, $assessments, 300 );
        return $assessments;
    }
    
    /**
     * Get user global data with validation
     */
    public function get_user_global_data( $user_id ) {
        $global_keys = array(
            'ennu_global_health_goals',
            'ennu_global_gender',
            'ennu_global_height_feet',
            'ennu_global_height_inches',
            'ennu_global_weight',
            'ennu_global_date_of_birth'
        );
        
        $data = array();
        foreach ( $global_keys as $key ) {
            $value = get_user_meta( $user_id, $key, true );
            if ( ! empty( $value ) ) {
                $data[ $key ] = $this->validate_global_field( $key, $value );
            }
        }
        
        return $data;
    }
    
    /**
     * Validate global field data
     */
    private function validate_global_field( $key, $value ) {
        switch ( $key ) {
            case 'ennu_global_gender':
                return in_array( $value, array( 'male', 'female', 'other' ), true ) ? $value : 'other';
                
            case 'ennu_global_height_feet':
                return is_numeric( $value ) && $value >= 3 && $value <= 8 ? (int) $value : 5;
                
            case 'ennu_global_height_inches':
                return is_numeric( $value ) && $value >= 0 && $value <= 11 ? (int) $value : 6;
                
            case 'ennu_global_weight':
                return is_numeric( $value ) && $value >= 50 && $value <= 1000 ? (float) $value : 150;
                
            case 'ennu_global_health_goals':
                if ( is_array( $value ) ) {
                    $valid_goals = array( 'weight_loss', 'muscle_gain', 'energy_boost', 'sleep_improvement', 'stress_reduction', 'hormone_balance', 'skin_health', 'hair_health', 'cognitive_function', 'longevity', 'athletic_performance' );
                    return array_intersect( $value, $valid_goals );
                }
                return array();
                
            default:
                return sanitize_text_field( $value );
        }
    }
    
    /**
     * Update user global data with validation
     */
    public function update_user_global_data( $user_id, $data ) {
        $updated = array();
        
        foreach ( $data as $key => $value ) {
            $validated_value = $this->validate_global_field( $key, $value );
            update_user_meta( $user_id, $key, $validated_value );
            $updated[ $key ] = $validated_value;
        }
        
        $this->cache->delete( "user_assessments_{$user_id}" );
        
        return $updated;
    }
    
    /**
     * Get user statistics with caching
     */
    public function get_user_stats( $user_id ) {
        $cache_key = "user_stats_{$user_id}";
        $cached = $this->cache->get( $cache_key );
        
        if ( $cached !== false ) {
            return $cached;
        }
        
        $stats = array(
            'total_assessments' => $this->count_user_assessments( $user_id ),
            'completion_rate' => $this->calculate_completion_rate( $user_id ),
            'last_activity' => $this->get_last_activity( $user_id ),
            'health_goals_count' => $this->count_health_goals( $user_id )
        );
        
        $this->cache->set( $cache_key, $stats, 600 );
        return $stats;
    }
    
    /**
     * Count user assessments
     */
    private function count_user_assessments( $user_id ) {
        global $wpdb;
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} 
             WHERE user_id = %d AND meta_key LIKE %s AND meta_value != ''",
            $user_id, '%_calculated_score'
        ) );
    }
    
    /**
     * Calculate completion rate
     */
    private function calculate_completion_rate( $user_id ) {
        $total_assessments = 10;
        $completed = $this->count_user_assessments( $user_id );
        return min( 100, round( ( $completed / $total_assessments ) * 100, 1 ) );
    }
    
    /**
     * Get last activity timestamp
     */
    private function get_last_activity( $user_id ) {
        global $wpdb;
        return $wpdb->get_var( $wpdb->prepare(
            "SELECT MAX(umeta_id) FROM {$wpdb->usermeta} 
             WHERE user_id = %d AND meta_key LIKE %s",
            $user_id, 'ennu_%'
        ) );
    }
    
    /**
     * Count health goals
     */
    private function count_health_goals( $user_id ) {
        $goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
        return is_array( $goals ) ? count( $goals ) : 0;
    }
}
