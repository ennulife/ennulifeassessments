<?php
/**
 * Advanced Security Manager
 * 
 * Provides enterprise-grade security features including rate limiting,
 * IP-based access controls, security audit logging, and threat detection.
 *
 * @package ENNU_Life_Assessments
 * @since 62.2.9
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Advanced_Security_Manager {
    
    private $rate_limits = array();
    private $blocked_ips = array();
    private $security_log = array();
    private $threat_patterns = array();
    
    public function __construct() {
        $this->init_security_features();
        $this->load_threat_patterns();
        $this->setup_hooks();
    }
    
    /**
     * Initialize security features
     */
    private function init_security_features() {
        $this->rate_limits = array(
            'login_attempts' => 5,
            'api_requests' => 60,
            'form_submissions' => 10,
            'ajax_requests' => 30,
            'assessment_submissions' => 3
        );
        
        $this->blocked_ips = get_option('ennu_blocked_ips', array());
    }
    
    /**
     * Load threat detection patterns
     */
    private function load_threat_patterns() {
        $this->threat_patterns = array(
            'sql_injection' => array(
                '/(\bunion\b.*\bselect\b)|(\bselect\b.*\bunion\b)/i',
                '/\b(select|insert|update|delete|drop|create|alter)\b.*\b(from|into|table|database)\b/i',
                '/(\bor\b|\band\b).*[\'"].*[\'"].*(\bor\b|\band\b)/i'
            ),
            'xss_attempts' => array(
                '/<script[^>]*>.*?<\/script>/i',
                '/javascript:/i',
                '/on\w+\s*=/i',
                '/<iframe[^>]*>.*?<\/iframe>/i'
            ),
            'path_traversal' => array(
                '/\.\.\//',
                '/\.\.\\\\/',
                '/\.\.\%2f/',
                '/\.\.\%5c/'
            ),
            'command_injection' => array(
                '/[;&|`$(){}[\]]/i',
                '/\b(exec|system|shell_exec|passthru|eval)\b/i'
            )
        );
    }
    
    /**
     * Setup WordPress hooks
     */
    private function setup_hooks() {
        add_action('init', array($this, 'check_ip_restrictions'));
        add_action('wp_login_failed', array($this, 'handle_failed_login'));
        add_action('wp_ajax_nopriv_ennu_assessment', array($this, 'rate_limit_ajax'), 1);
        add_action('wp_ajax_ennu_assessment', array($this, 'rate_limit_ajax'), 1);
        add_filter('wp_die_handler', array($this, 'custom_die_handler'));
        
        add_action('send_headers', array($this, 'add_security_headers'));
        
        add_filter('pre_user_query', array($this, 'validate_user_queries'));
        add_action('wp_loaded', array($this, 'validate_all_inputs'));
    }
    
    /**
     * Check IP-based access restrictions
     */
    public function check_ip_restrictions() {
        $client_ip = $this->get_client_ip();
        
        if (in_array($client_ip, $this->blocked_ips)) {
            $this->log_security_event('blocked_ip_access', array(
                'ip' => $client_ip,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'request_uri' => $_SERVER['REQUEST_URI'] ?? ''
            ));
            
            wp_die('Access denied. Your IP address has been blocked due to suspicious activity.', 'Access Denied', array('response' => 403));
        }
        
        $this->detect_threats();
    }
    
    /**
     * Rate limiting for AJAX requests
     */
    public function rate_limit_ajax() {
        $client_ip = $this->get_client_ip();
        $action = $_POST['action'] ?? '';
        
        if (!$this->check_rate_limit($client_ip, 'ajax_requests')) {
            $this->log_security_event('rate_limit_exceeded', array(
                'ip' => $client_ip,
                'action' => $action,
                'limit_type' => 'ajax_requests'
            ));
            
            wp_die(json_encode(array(
                'success' => false,
                'message' => 'Rate limit exceeded. Please try again later.'
            )), 'Rate Limit Exceeded', array('response' => 429));
        }
    }
    
    /**
     * Handle failed login attempts
     */
    public function handle_failed_login($username) {
        $client_ip = $this->get_client_ip();
        
        if (!$this->check_rate_limit($client_ip, 'login_attempts')) {
            $this->block_ip($client_ip, 'Repeated failed login attempts');
            
            $this->log_security_event('ip_blocked_login_attempts', array(
                'ip' => $client_ip,
                'username' => $username,
                'attempts' => $this->get_attempt_count($client_ip, 'login_attempts')
            ));
        }
        
        $this->log_security_event('failed_login', array(
            'ip' => $client_ip,
            'username' => $username,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ));
    }
    
    /**
     * Check rate limits
     */
    private function check_rate_limit($identifier, $limit_type) {
        $cache_key = "ennu_rate_limit_{$limit_type}_{$identifier}";
        $attempts = get_transient($cache_key);
        
        if ($attempts === false) {
            $attempts = 0;
        }
        
        $limit = $this->rate_limits[$limit_type] ?? 60;
        
        if ($attempts >= $limit) {
            return false;
        }
        
        set_transient($cache_key, $attempts + 1, 60); // 1 minute window
        
        return true;
    }
    
    /**
     * Get attempt count for rate limiting
     */
    private function get_attempt_count($identifier, $limit_type) {
        $cache_key = "ennu_rate_limit_{$limit_type}_{$identifier}";
        return get_transient($cache_key) ?: 0;
    }
    
    /**
     * Block IP address
     */
    private function block_ip($ip, $reason = '') {
        if (!in_array($ip, $this->blocked_ips)) {
            $this->blocked_ips[] = $ip;
            update_option('ennu_blocked_ips', $this->blocked_ips);
            
            $this->log_security_event('ip_blocked', array(
                'ip' => $ip,
                'reason' => $reason,
                'timestamp' => current_time('mysql')
            ));
        }
    }
    
    /**
     * Detect security threats
     */
    private function detect_threats() {
        $request_data = array_merge($_GET, $_POST);
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        foreach ($this->threat_patterns as $threat_type => $patterns) {
            foreach ($patterns as $pattern) {
                foreach ($request_data as $key => $value) {
                    if (is_string($value) && preg_match($pattern, $value)) {
                        $this->handle_threat_detection($threat_type, $key, $value);
                        return;
                    }
                }
                
                if (preg_match($pattern, $request_uri) || preg_match($pattern, $user_agent)) {
                    $this->handle_threat_detection($threat_type, 'request_headers', $request_uri . ' | ' . $user_agent);
                    return;
                }
            }
        }
    }
    
    /**
     * Handle threat detection
     */
    private function handle_threat_detection($threat_type, $field, $value) {
        $client_ip = $this->get_client_ip();
        
        $this->log_security_event('threat_detected', array(
            'threat_type' => $threat_type,
            'ip' => $client_ip,
            'field' => $field,
            'value' => substr($value, 0, 200), // Limit logged value length
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? ''
        ));
        
        if (in_array($threat_type, array('sql_injection', 'command_injection'))) {
            $this->block_ip($client_ip, "Detected {$threat_type} attempt");
            wp_die('Security violation detected. Access denied.', 'Security Violation', array('response' => 403));
        }
        
        if ($threat_type === 'xss_attempts') {
            add_filter('pre_user_query', array($this, 'sanitize_suspicious_input'));
        }
    }
    
    /**
     * Add security headers
     */
    public function add_security_headers() {
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
            
            $csp = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https:; connect-src 'self';";
            header("Content-Security-Policy: {$csp}");
        }
    }
    
    /**
     * Validate all inputs
     */
    public function validate_all_inputs() {
        foreach ($_GET as $key => $value) {
            $_GET[$key] = $this->sanitize_input($value);
        }
        
        foreach ($_POST as $key => $value) {
            $_POST[$key] = $this->sanitize_input($value);
        }
    }
    
    /**
     * Sanitize input data
     */
    private function sanitize_input($input) {
        if (is_array($input)) {
            return array_map(array($this, 'sanitize_input'), $input);
        }
        
        if (!is_string($input)) {
            return $input;
        }
        
        $input = str_replace(chr(0), '', $input);
        
        $input = trim($input);
        
        return $input;
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_headers = array(
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Log security events
     */
    private function log_security_event($event_type, $data = array()) {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'event_type' => $event_type,
            'ip' => $this->get_client_ip(),
            'user_id' => get_current_user_id(),
            'data' => $data
        );
        
        // Store in database
        global $wpdb;
        $table_name = $wpdb->prefix . 'ennu_security_log';
        
        $wpdb->insert(
            $table_name,
            array(
                'timestamp' => $log_entry['timestamp'],
                'event_type' => $event_type,
                'ip_address' => $log_entry['ip'],
                'user_id' => $log_entry['user_id'],
                'event_data' => json_encode($data)
            ),
            array('%s', '%s', '%s', '%d', '%s')
        );
        
        if (in_array($event_type, array('threat_detected', 'ip_blocked', 'rate_limit_exceeded'))) {
            error_log("ENNU Security Event [{$event_type}]: " . json_encode($log_entry));
        }
    }
    
    /**
     * Get security statistics
     */
    public function get_security_stats($days = 7) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ennu_security_log';
        
        $since = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $stats = array();
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT event_type, COUNT(*) as count 
            FROM {$table_name} 
            WHERE timestamp >= %s 
            GROUP BY event_type
        ", $since));
        
        foreach ($results as $row) {
            $stats[$row->event_type] = $row->count;
        }
        
        $top_ips = $wpdb->get_results($wpdb->prepare("
            SELECT ip_address, COUNT(*) as count 
            FROM {$table_name} 
            WHERE timestamp >= %s 
            GROUP BY ip_address 
            ORDER BY count DESC 
            LIMIT 10
        ", $since));
        
        $stats['top_ips'] = $top_ips;
        $stats['blocked_ips_count'] = count($this->blocked_ips);
        
        return $stats;
    }
    
    /**
     * Create security log table
     */
    public static function create_security_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ennu_security_log';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            event_type varchar(50) NOT NULL,
            ip_address varchar(45) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            event_data longtext,
            PRIMARY KEY (id),
            KEY event_type (event_type),
            KEY ip_address (ip_address),
            KEY timestamp (timestamp)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Unblock IP address
     */
    public function unblock_ip($ip) {
        $key = array_search($ip, $this->blocked_ips);
        if ($key !== false) {
            unset($this->blocked_ips[$key]);
            $this->blocked_ips = array_values($this->blocked_ips);
            update_option('ennu_blocked_ips', $this->blocked_ips);
            
            $this->log_security_event('ip_unblocked', array('ip' => $ip));
            return true;
        }
        return false;
    }
    
    /**
     * Get blocked IPs
     */
    public function get_blocked_ips() {
        return $this->blocked_ips;
    }
    
    /**
     * Update rate limits
     */
    public function update_rate_limits($new_limits) {
        $this->rate_limits = array_merge($this->rate_limits, $new_limits);
        update_option('ennu_rate_limits', $this->rate_limits);
    }
}

if (class_exists('ENNU_Advanced_Security_Manager')) {
    new ENNU_Advanced_Security_Manager();
    
    register_activation_hook(__FILE__, array('ENNU_Advanced_Security_Manager', 'create_security_table'));
}
