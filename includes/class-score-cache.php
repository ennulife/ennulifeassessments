<?php
/**
 * ENNU Life Advanced Score Caching System
 * 
 * Eliminates performance issues by implementing intelligent caching
 * for assessment scores and calculations.
 * 
 * @package ENNU_Life
 * @version 23.1.0
 * @author Manus - World's Greatest WordPress Developer
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Advanced Score Caching System
 * 
 * Provides intelligent caching for assessment scores to eliminate
 * database query overhead and improve performance.
 */
class ENNU_Score_Cache {
    
    /**
     * In-memory cache for current request
     * 
     * @var array
     */
    private static $memory_cache = array();
    
    /**
     * Cache expiry time in seconds (1 hour default)
     * 
     * @var int
     */
    private static $cache_expiry = 3600;
    
    /**
     * Cache version for invalidation
     * 
     * @var string
     */
    private static $cache_version = '23.1.0';
    
    /**
     * Get cached score data
     * 
     * @param int $user_id User ID
     * @param string $assessment_type Assessment type
     * @return array|false Cached score data or false if not cached
     */
    public static function get_cached_score($user_id, $assessment_type) {
        // Check memory cache first (fastest)
        $memory_key = self::get_memory_key($user_id, $assessment_type);
        if (isset(self::$memory_cache[$memory_key])) {
            return self::$memory_cache[$memory_key];
        }
        
        // Check WordPress transient cache
        $cache_key = self::get_cache_key($user_id, $assessment_type);
        $cached_data = get_transient($cache_key);
        
        if ($cached_data !== false && self::is_cache_valid($cached_data)) {
            // Store in memory cache for subsequent requests
            self::$memory_cache[$memory_key] = $cached_data;
            return $cached_data;
        }
        
        return false;
    }
    
    /**
     * Cache score data
     * 
     * @param int $user_id User ID
     * @param string $assessment_type Assessment type
     * @param array $score_data Score data to cache
     * @return bool Success status
     */
    public static function cache_score($user_id, $assessment_type, $score_data) {
        if (!is_array($score_data) || empty($score_data)) {
            return false;
        }
        
        // Add cache metadata
        $cache_data = array(
            'score_data' => $score_data,
            'cached_at' => time(),
            'cache_version' => self::$cache_version,
            'user_id' => $user_id,
            'assessment_type' => $assessment_type
        );
        
        // Store in WordPress transient
        $cache_key = self::get_cache_key($user_id, $assessment_type);
        $transient_result = set_transient($cache_key, $cache_data, self::$cache_expiry);
        
        // Store in memory cache
        $memory_key = self::get_memory_key($user_id, $assessment_type);
        self::$memory_cache[$memory_key] = $cache_data;
        
        // Log cache operation
        error_log("ENNU Cache: Stored score for user {$user_id}, assessment {$assessment_type}");
        
        return $transient_result;
    }
    
    /**
     * Invalidate cache for specific user and assessment
     * 
     * @param int $user_id User ID
     * @param string $assessment_type Assessment type (null for all assessments)
     * @return bool Success status
     */
    public static function invalidate_cache($user_id, $assessment_type = null) {
        $invalidated = 0;
        
        if ($assessment_type) {
            // Invalidate specific assessment
            $cache_key = self::get_cache_key($user_id, $assessment_type);
            delete_transient($cache_key);
            
            $memory_key = self::get_memory_key($user_id, $assessment_type);
            unset(self::$memory_cache[$memory_key]);
            
            $invalidated = 1;
        } else {
            // Invalidate all assessments for user
            $assessments = array('hair_assessment', 'weight_loss_assessment', 'health_assessment', 'ed_treatment_assessment', 'skin_assessment', 'welcome_assessment');
            
            foreach ($assessments as $type) {
                $cache_key = self::get_cache_key($user_id, $type);
                delete_transient($cache_key);
                
                $memory_key = self::get_memory_key($user_id, $type);
                unset(self::$memory_cache[$memory_key]);
                
                $invalidated++;
            }
        }
        
        // Log cache invalidation
        error_log("ENNU Cache: Invalidated {$invalidated} cache entries for user {$user_id}");
        
        return true;
    }
    
    /**
     * Warm up cache for user
     * 
     * Pre-calculates and caches scores for all assessments
     * 
     * @param int $user_id User ID
     * @return array Results of cache warming
     */
    public static function warm_cache($user_id) {
        $results = array();
        $assessments = array('hair_assessment', 'weight_loss_assessment', 'health_assessment', 'ed_treatment_assessment', 'skin_assessment');
        
        foreach ($assessments as $assessment_type) {
            // Check if user has data for this assessment
            $has_data = get_user_meta($user_id, $assessment_type . '_q1', true);
            
            if ($has_data) {
                // Calculate and cache score
                $database = ENNU_Life_Database::get_instance();
                $score_data = $database->calculate_and_store_scores($assessment_type, array(), null, $user_id);
                
                if ($score_data) {
                    self::cache_score($user_id, $assessment_type, $score_data);
                    $results[$assessment_type] = 'cached';
                } else {
                    $results[$assessment_type] = 'failed';
                }
            } else {
                $results[$assessment_type] = 'no_data';
            }
        }
        
        return $results;
    }
    
    /**
     * Get cache statistics
     * 
     * @return array Cache statistics
     */
    public static function get_cache_stats() {
        global $wpdb;
        
        // Count cached entries
        $cache_count = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_ennu_score_%'
        ");
        
        return array(
            'cached_entries' => (int) $cache_count,
            'memory_cache_size' => count(self::$memory_cache),
            'cache_expiry' => self::$cache_expiry,
            'cache_version' => self::$cache_version
        );
    }
    
    /**
     * Clear all cache entries
     * 
     * @return int Number of entries cleared
     */
    public static function clear_all_cache() {
        global $wpdb;
        
        // Clear WordPress transients
        $deleted = $wpdb->query("
            DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_ennu_score_%' 
            OR option_name LIKE '_transient_timeout_ennu_score_%'
        ");
        
        // Clear memory cache
        self::$memory_cache = array();
        
        error_log("ENNU Cache: Cleared {$deleted} cache entries");
        
        return $deleted;
    }
    
    /**
     * Generate cache key
     * 
     * @param int $user_id User ID
     * @param string $assessment_type Assessment type
     * @return string Cache key
     */
    private static function get_cache_key($user_id, $assessment_type) {
        return "ennu_score_{$user_id}_{$assessment_type}_v" . self::$cache_version;
    }
    
    /**
     * Generate memory cache key
     * 
     * @param int $user_id User ID
     * @param string $assessment_type Assessment type
     * @return string Memory cache key
     */
    private static function get_memory_key($user_id, $assessment_type) {
        return "{$user_id}_{$assessment_type}";
    }
    
    /**
     * Check if cached data is valid
     * 
     * @param array $cached_data Cached data
     * @return bool Validity status
     */
    private static function is_cache_valid($cached_data) {
        if (!is_array($cached_data)) {
            return false;
        }
        
        // Check cache version
        if (!isset($cached_data['cache_version']) || $cached_data['cache_version'] !== self::$cache_version) {
            return false;
        }
        
        // Check expiry
        if (!isset($cached_data['cached_at']) || (time() - $cached_data['cached_at']) > self::$cache_expiry) {
            return false;
        }
        
        // Check data integrity
        if (!isset($cached_data['score_data']) || !is_array($cached_data['score_data'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Set cache expiry time
     * 
     * @param int $seconds Expiry time in seconds
     */
    public static function set_cache_expiry($seconds) {
        self::$cache_expiry = max(300, (int) $seconds); // Minimum 5 minutes
    }
    
    /**
     * Get cache expiry time
     * 
     * @return int Expiry time in seconds
     */
    public static function get_cache_expiry() {
        return self::$cache_expiry;
    }
}

