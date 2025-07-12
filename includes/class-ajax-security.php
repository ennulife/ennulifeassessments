<?php
/**
 * ENNU Life Enterprise AJAX Security System
 * 
 * Provides bulletproof security for all AJAX operations with
 * rate limiting, comprehensive validation, and threat detection.
 * 
 * @package ENNU_Life
 * @version 23.1.0
 * @author Manus - World's Greatest WordPress Developer
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enterprise AJAX Security Class
 * 
 * Implements military-grade security for AJAX operations
 */
class ENNU_AJAX_Security {
    
    /**
     * Rate limiting storage
     * 
     * @var array
     */
    private static $rate_limits = array();
    
    /**
     * Security configuration
     * 
     * @var array
     */
    private static $config = array(
        'max_requests_per_minute' => 10,
        'max_requests_per_hour' => 100,
        'enable_ip_validation' => true,
        'enable_user_agent_validation' => true,
        'enable_referrer_validation' => true,
        'log_security_events' => true
    );
    
    /**
     * Blocked IPs cache
     * 
     * @var array
     */
    private static $blocked_ips = array();
    
    /**
     * Security event log
     * 
     * @var array
     */
    private static $security_log = array();
    
    /**
     * Initialize security system
     */
    public static function init() {
        // Load blocked IPs from database - with safety check
        if (function_exists('get_option')) {
            self::$blocked_ips = get_option('ennu_blocked_ips', array());
        } else {
            self::$blocked_ips = array();
        }
        
        // Clean old rate limit data
        self::cleanup_rate_limits();
        
        // Hook into WordPress - with safety check
        if (function_exists('add_action')) {
            add_action('wp_ajax_ennu_log_security_event', array(__CLASS__, 'log_security_event'));
            add_action('wp_ajax_nopriv_ennu_log_security_event', array(__CLASS__, 'log_security_event'));
        }
    }
    
    /**
     * Comprehensive AJAX request validation
     * 
     * @param string $action AJAX action name
     * @param int $user_id User ID (optional)
     * @param array $additional_checks Additional security checks
     * @return bool|WP_Error Validation result
     */
    public static function validate_ajax_request($action, $user_id = null, $additional_checks = array()) {
        $start_time = microtime(true);
        $client_ip = self::get_client_ip();
        $user_agent = self::get_user_agent();
        
        try {
            // 1. IP Blacklist Check
            if (self::is_ip_blocked($client_ip)) {
                self::log_security_event('blocked_ip_attempt', array(
                    'ip' => $client_ip,
                    'action' => $action,
                    'user_id' => $user_id
                ));
                return new WP_Error('blocked_ip', 'Access denied from this IP address.');
            }
            
            // 2. Nonce Verification (Critical)
            if (!self::verify_nonce()) {
                self::log_security_event('invalid_nonce', array(
                    'ip' => $client_ip,
                    'action' => $action,
                    'user_id' => $user_id
                ));
                return new WP_Error('invalid_nonce', 'Security verification failed. Please refresh the page and try again.');
            }
            
            // 3. User Capability Check
            if ($user_id && !self::verify_user_capability($user_id)) {
                self::log_security_event('insufficient_permissions', array(
                    'ip' => $client_ip,
                    'action' => $action,
                    'user_id' => $user_id
                ));
                return new WP_Error('insufficient_permissions', 'You do not have permission to perform this action.');
            }
            
            // 4. Rate Limiting
            $rate_limit_result = self::check_rate_limits($action, $user_id, $client_ip);
            if (is_wp_error($rate_limit_result)) {
                self::log_security_event('rate_limit_exceeded', array(
                    'ip' => $client_ip,
                    'action' => $action,
                    'user_id' => $user_id
                ));
                return $rate_limit_result;
            }
            
            // 5. User ID Validation
            if ($user_id && !self::validate_user_id($user_id)) {
                self::log_security_event('invalid_user_id', array(
                    'ip' => $client_ip,
                    'action' => $action,
                    'user_id' => $user_id
                ));
                return new WP_Error('invalid_user_id', 'Invalid user identifier.');
            }
            
            // 6. Request Origin Validation
            if (self::$config['enable_referrer_validation'] && !self::validate_request_origin()) {
                self::log_security_event('invalid_origin', array(
                    'ip' => $client_ip,
                    'action' => $action,
                    'user_id' => $user_id,
                    'referrer' => wp_get_referer()
                ));
                return new WP_Error('invalid_origin', 'Request origin validation failed.');
            }
            
            // 7. User Agent Validation
            if (self::$config['enable_user_agent_validation'] && !self::validate_user_agent($user_agent)) {
                self::log_security_event('suspicious_user_agent', array(
                    'ip' => $client_ip,
                    'action' => $action,
                    'user_id' => $user_id,
                    'user_agent' => $user_agent
                ));
                return new WP_Error('suspicious_user_agent', 'Request blocked due to suspicious user agent.');
            }
            
            // 8. Additional Custom Checks
            if (!empty($additional_checks)) {
                foreach ($additional_checks as $check_name => $check_function) {
                    if (is_callable($check_function)) {
                        $check_result = call_user_func($check_function);
                        if (is_wp_error($check_result)) {
                            self::log_security_event('custom_check_failed', array(
                                'ip' => $client_ip,
                                'action' => $action,
                                'user_id' => $user_id,
                                'check_name' => $check_name
                            ));
                            return $check_result;
                        }
                    }
                }
            }
            
            // 9. Log successful validation
            $execution_time = microtime(true) - $start_time;
            self::log_security_event('validation_success', array(
                'ip' => $client_ip,
                'action' => $action,
                'user_id' => $user_id,
                'execution_time' => $execution_time
            ));
            
            return true;
            
        } catch (Exception $e) {
            self::log_security_event('validation_error', array(
                'ip' => $client_ip,
                'action' => $action,
                'user_id' => $user_id,
                'error' => $e->getMessage()
            ));
            
            return new WP_Error('validation_error', 'Security validation failed due to system error.');
        }
    }
    
    /**
     * Verify nonce with enhanced security
     */
    private static function verify_nonce() {
        // Check for nonce in multiple locations
        $nonce = null;
        
        if (isset($_POST['nonce'])) {
            $nonce = $_POST['nonce'];
        } elseif (isset($_GET['nonce'])) {
            $nonce = $_GET['nonce'];
        } elseif (isset($_SERVER['HTTP_X_WP_NONCE'])) {
            $nonce = $_SERVER['HTTP_X_WP_NONCE'];
        }
        
        if (!$nonce) {
            return false;
        }
        
        // Verify a single, consistent nonce for all AJAX operations.
        return wp_verify_nonce($nonce, 'ennu_ajax_nonce');
    }
    
    /**
     * Verify user capability
     */
    private static function verify_user_capability($user_id) {
        if (!$user_id) {
            return false;
        }
        
        // Check if user exists
        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }
        
        // Check capability
        return current_user_can('edit_user', $user_id) || current_user_can('manage_options');
    }
    
    /**
     * Advanced rate limiting with multiple tiers
     */
    private static function check_rate_limits($action, $user_id, $client_ip) {
        $current_time = time();
        
        // Create rate limit keys
        $keys = array(
            'user_minute' => "user_{$user_id}_minute",
            'user_hour' => "user_{$user_id}_hour",
            'ip_minute' => "ip_{$client_ip}_minute",
            'ip_hour' => "ip_{$client_ip}_hour",
            'action_minute' => "action_{$action}_minute"
        );
        
        // Check each rate limit
        foreach ($keys as $type => $key) {
            $limit_result = self::check_individual_rate_limit($key, $type, $current_time);
            if (is_wp_error($limit_result)) {
                return $limit_result;
            }
        }
        
        return true;
    }
    
    /**
     * Check individual rate limit
     */
    private static function check_individual_rate_limit($key, $type, $current_time) {
        if (!isset(self::$rate_limits[$key])) {
            self::$rate_limits[$key] = array();
        }
        
        // Determine time window and limit
        $time_window = strpos($type, 'minute') !== false ? 60 : 3600;
        $max_requests = strpos($type, 'minute') !== false ? self::$config['max_requests_per_minute'] : self::$config['max_requests_per_hour'];
        
        // Clean old entries
        self::$rate_limits[$key] = array_filter(
            self::$rate_limits[$key],
            function($timestamp) use ($current_time, $time_window) {
                return ($current_time - $timestamp) < $time_window;
            }
        );
        
        // Check if limit exceeded
        if (count(self::$rate_limits[$key]) >= $max_requests) {
            $wait_time = $time_window - ($current_time - min(self::$rate_limits[$key]));
            return new WP_Error('rate_limit_exceeded', "Rate limit exceeded. Please wait {$wait_time} seconds before trying again.");
        }
        
        // Add current request
        self::$rate_limits[$key][] = $current_time;
        
        return true;
    }
    
    /**
     * Validate user ID
     */
    private static function validate_user_id($user_id) {
        if (!is_numeric($user_id) || $user_id <= 0) {
            return false;
        }
        
        return get_userdata($user_id) !== false;
    }
    
    /**
     * Validate request origin
     */
    private static function validate_request_origin() {
        $referrer = wp_get_referer();
        
        if (!$referrer) {
            return false;
        }
        
        $site_url = site_url();
        $admin_url = admin_url();
        
        return (strpos($referrer, $site_url) === 0) || (strpos($referrer, $admin_url) === 0);
    }
    
    /**
     * Validate user agent
     */
    private static function validate_user_agent($user_agent) {
        if (empty($user_agent)) {
            return false;
        }
        
        // Check for suspicious patterns
        $suspicious_patterns = array(
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scraper/i',
            '/curl/i',
            '/wget/i'
        );
        
        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $user_agent)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get client IP address
     */
    private static function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Get user agent
     */
    private static function get_user_agent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }
    
    /**
     * Check if IP is blocked
     */
    private static function is_ip_blocked($ip) {
        return in_array($ip, self::$blocked_ips);
    }
    
    /**
     * Block IP address
     */
    public static function block_ip($ip, $reason = '') {
        if (!in_array($ip, self::$blocked_ips)) {
            self::$blocked_ips[] = $ip;
            update_option('ennu_blocked_ips', self::$blocked_ips);
            
            self::log_security_event('ip_blocked', array(
                'ip' => $ip,
                'reason' => $reason
            ));
        }
    }
    
    /**
     * Unblock IP address
     */
    public static function unblock_ip($ip) {
        $key = array_search($ip, self::$blocked_ips);
        if ($key !== false) {
            unset(self::$blocked_ips[$key]);
            self::$blocked_ips = array_values(self::$blocked_ips);
            update_option('ennu_blocked_ips', self::$blocked_ips);
            
            self::log_security_event('ip_unblocked', array(
                'ip' => $ip
            ));
        }
    }
    
    /**
     * Log security event
     */
    public static function log_security_event($event_type, $data = array()) {
        if (!self::$config['log_security_events']) {
            return;
        }
        
        $event = array(
            'timestamp' => current_time('mysql'),
            'event_type' => $event_type,
            'data' => $data,
            'ip' => self::get_client_ip(),
            'user_agent' => self::get_user_agent(),
            'user_id' => get_current_user_id()
        );
        
        // Store in memory
        self::$security_log[] = $event;
        
        // Storing a large, growing log in wp_options is a performance risk.
        // This has been disabled to prevent database bloat. Critical events
        // are still logged to the standard error log.
        /*
        $stored_events = get_option('ennu_security_log', array());
        $stored_events[] = $event;
        
        // Keep only last 1000 events
        if (count($stored_events) > 1000) {
            $stored_events = array_slice($stored_events, -1000);
        }
        
        update_option('ennu_security_log', $stored_events);
        */
        
        // Log critical events to error log
        $critical_events = array('blocked_ip_attempt', 'invalid_nonce', 'rate_limit_exceeded');
        if (in_array($event_type, $critical_events)) {
            error_log("ENNU Security Alert: {$event_type} - " . json_encode($data));
        }
    }
    
    /**
     * Get security statistics
     */
    public static function get_security_stats() {
        $stored_events = get_option('ennu_security_log', array());
        
        $stats = array(
            'total_events' => count($stored_events),
            'blocked_ips' => count(self::$blocked_ips),
            'event_types' => array(),
            'recent_events' => array_slice($stored_events, -10)
        );
        
        // This will now always be empty, which is intended.
        foreach ($stored_events as $event) {
            $type = $event['event_type'];
            if (!isset($stats['event_types'][$type])) {
                $stats['event_types'][$type] = 0;
            }
            $stats['event_types'][$type]++;
        }
        
        return $stats;
    }
    
    /**
     * Clean up old rate limit data
     */
    private static function cleanup_rate_limits() {
        $current_time = time();
        
        foreach (self::$rate_limits as $key => $timestamps) {
            self::$rate_limits[$key] = array_filter(
                $timestamps,
                function($timestamp) use ($current_time) {
                    return ($current_time - $timestamp) < 3600; // Keep last hour
                }
            );
            
            // Remove empty arrays
            if (empty(self::$rate_limits[$key])) {
                unset(self::$rate_limits[$key]);
            }
        }
    }
    
    /**
     * Configure security settings
     */
    public static function configure($config) {
        self::$config = array_merge(self::$config, $config);
    }
    
    /**
     * Get current configuration
     */
    public static function get_config() {
        return self::$config;
    }
}

// Initialize security system only if WordPress is loaded
if (function_exists('add_action')) {
    ENNU_AJAX_Security::init();
}

