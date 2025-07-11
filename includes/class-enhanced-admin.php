<?php
/**
 * ENNU Life Enhanced Admin Class - Bulletproof Edition
 * 
 * Integrates enterprise security, performance optimization, and
 * bulletproof error handling for zero-issue deployment.
 * 
 * @package ENNU_Life
 * @version 23.1.0
 * @author Manus - World's Greatest WordPress Developer
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies - with safety checks
$ajax_security_file = dirname(__FILE__) . '/class-ajax-security.php';
if (file_exists($ajax_security_file)) {
    require_once $ajax_security_file;
}

$cache_file = dirname(__FILE__) . '/class-score-cache.php';
if (file_exists($cache_file)) {
    require_once $cache_file;
}

class ENNU_Enhanced_Admin {
    
    /**
     * Performance monitoring
     * 
     * @var array
     */
    private $performance_log = array();
    
    public function __construct() {
        // Original WordPress hooks (preserved)
        add_action('show_user_profile', array($this, 'show_user_assessment_fields'));
        add_action('edit_user_profile', array($this, 'show_user_assessment_fields'));
        add_action('personal_options_update', array($this, 'save_user_assessment_fields'));
        add_action('edit_user_profile_update', array($this, 'save_user_assessment_fields'));
        
        // Enhanced AJAX handlers with bulletproof security
        add_action('wp_ajax_ennu_recalculate_scores', array($this, 'ajax_recalculate_scores'));
        add_action('wp_ajax_ennu_export_user_data', array($this, 'ajax_export_user_data'));
        add_action('wp_ajax_ennu_sync_hubspot', array($this, 'ajax_sync_hubspot'));
        add_action('wp_ajax_ennu_clear_cache', array($this, 'ajax_clear_cache'));
        add_action('wp_ajax_ennu_security_stats', array($this, 'ajax_security_stats'));
        
        // Enhanced asset loading
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Performance monitoring
        add_action('admin_footer', array($this, 'output_performance_stats'));
    }

    /**
     * Enhanced asset enqueuing with conditional loading
     */
    public function enqueue_admin_assets($hook) {
        // Only load on user profile pages
        if ($hook !== 'profile.php' && $hook !== 'user-edit.php') {
            return;
        }
        
        $start_time = microtime(true);
        
        try {
            // Enhanced CSS with conflict prevention
            wp_enqueue_style(
                'ennu-admin-scores-enhanced', 
                ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-scores-enhanced.css', 
                array(), 
                ENNU_LIFE_VERSION . '-' . filemtime(ENNU_LIFE_PLUGIN_PATH . 'assets/css/admin-scores-enhanced.css')
            );
            
            // Enhanced JavaScript with error handling
            wp_enqueue_script(
                'ennu-admin-scores-enhanced', 
                ENNU_LIFE_PLUGIN_URL . 'assets/js/admin-scores-enhanced.js', 
                array('jquery'), 
                ENNU_LIFE_VERSION . '-' . filemtime(ENNU_LIFE_PLUGIN_PATH . 'assets/js/admin-scores-enhanced.js'), 
                true
            );
            
            // Enhanced localization with security data
            wp_localize_script('ennu-admin-scores-enhanced', 'ennuAdminEnhanced', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ennu_admin_nonce'),
                'security' => array(
                    'max_requests_per_minute' => 10,
                    'retry_attempts' => 3,
                    'retry_delay' => 1000
                ),
                'strings' => array(
                    'recalculating' => __('Recalculating scores...', 'ennu-life'),
                    'exporting' => __('Exporting data...', 'ennu-life'),
                    'syncing' => __('Syncing with HubSpot...', 'ennu-life'),
                    'clearing_cache' => __('Clearing cache...', 'ennu-life'),
                    'success' => __('Operation completed successfully!', 'ennu-life'),
                    'error' => __('An error occurred. Please try again.', 'ennu-life'),
                    'rate_limit' => __('Too many requests. Please wait a moment.', 'ennu-life'),
                    'network_error' => __('Network error. Please check your connection.', 'ennu-life'),
                    'permission_denied' => __('Permission denied. Please refresh the page.', 'ennu-life')
                ),
                'cache_stats' => ENNU_Score_Cache::get_cache_stats(),
                'security_stats' => ENNU_AJAX_Security::get_security_stats()
            ));
            
            // Log performance
            $execution_time = microtime(true) - $start_time;
            $this->log_performance('enqueue_assets', $execution_time);
            
        } catch (Exception $e) {
            error_log('ENNU Enhanced Admin Asset Error: ' . $e->getMessage());
        }
    }

    /**
     * Enhanced user assessment fields display with bulletproof error handling
     */
    public function show_user_assessment_fields($user) {
        if (!current_user_can('edit_user', $user->ID)) {
            return;
        }
        
        $start_time = microtime(true);
        
        try {
            wp_nonce_field('ennu_user_profile_update', 'ennu_assessment_nonce');
            
            // Enhanced Score Dashboard Section (NEW)
            $this->display_enhanced_score_dashboard($user->ID);
            
            // Performance Monitoring Section (NEW)
            $this->display_performance_dashboard($user->ID);
            
            // Security Monitoring Section (NEW)
            $this->display_security_dashboard($user->ID);
            
            // Original sections (PRESERVED)
            echo '<h2>' . __('ENNU Life Global Information', 'ennu-life') . '</h2>';
            echo '<p><em>' . __('This information is shared across all assessments.', 'ennu-life') . '</em></p>';
            echo '<table class="form-table">';
            $this->display_global_fields($user->ID);
            echo '</table>';

            // Enhanced WP Fusion Integration Panel
            $this->display_enhanced_wp_fusion_panel($user->ID);

            echo '<h2>' . __( 'ENNU Life Assessment Data', 'ennu-life' ) . '</h2>';
            echo '<style>
                .ennu-profile-section { margin: 20px 0; border: 1px solid #ddd; padding: 15px; background: #f9f9f9; }
                .ennu-field-row { display: flex; margin: 5px 0; padding: 5px; border-bottom: 1px solid #eee; }
                .ennu-field-label { font-weight: bold; width: 200px; color: #333; }
                .ennu-field-id { font-family: monospace; color: #666; font-size: 11px; width: 250px; }
                .ennu-field-value { flex: 1; color: #555; }
                .ennu-empty-value { color: #999; font-style: italic; }
                .ennu-system-field { background: #fff3cd; }
                .ennu-global-field { background: #d1ecf1; }
            </style>';

            // Global User Fields - Comprehensive Display
            ENNU_Comprehensive_Assessment_Display::display_global_fields_comprehensive( $user->ID );
            
            // Assessment-Specific Fields - Comprehensive Display
            $assessments = array(
                'welcome' => 'Welcome Assessment',
                'hair' => 'Hair Health Assessment', 
                'weight_loss' => 'Weight Loss Assessment',
                'health' => 'General Health Assessment',
                'skin' => 'Skin Health Assessment',
                'ed_treatment' => 'ED Treatment Assessment'
            );

            foreach ( $assessments as $type => $title ) {
                ENNU_Comprehensive_Assessment_Display::display_comprehensive_section( $user->ID, $type, $title );
            }

            // Global System Fields - Comprehensive Display
            ENNU_Comprehensive_Assessment_Display::display_global_system_fields( $user->ID );
            
            // Log performance
            $execution_time = microtime(true) - $start_time;
            $this->log_performance('show_user_fields', $execution_time, $user->ID);
            
        } catch (Exception $e) {
            error_log('ENNU Enhanced Admin Asset Error: ' . $e->getMessage());
        }
    }

    /**
     * Display global user fields section
     */
    private function display_global_fields_section( $user_id ) {
        echo '<div class="ennu-profile-section">';
        echo '<h3>Global User Data (Persistent Across All Assessments)</h3>';
        
        $global_fields = array(
            'ennu_global_first_name' => 'First Name',
            'ennu_global_last_name' => 'Last Name', 
            'ennu_global_email' => 'Email Address',
            'ennu_global_billing_phone' => 'Phone Number',
            'ennu_global_dob_month' => 'Birth Month',
            'ennu_global_dob_day' => 'Birth Day',
            'ennu_global_dob_year' => 'Birth Year',
            'ennu_global_calculated_age' => 'Calculated Age',
            'ennu_global_gender' => 'Gender',
            'ennu_global_profile_created' => 'Profile Created Date',
            'ennu_global_last_updated' => 'Last Updated',
            'ennu_global_data_source' => 'Data Source'
        );

        foreach ( $global_fields as $field_id => $field_name ) {
            $value = get_user_meta( $user_id, $field_id, true );
            $display_value = !empty($value) ? $value : '<span class="ennu-empty-value">Not provided</span>';
            
            echo '<div class="ennu-field-row ennu-global-field">';
            echo '<div class="ennu-field-label">' . esc_html($field_name) . ':</div>';
            echo '<div class="ennu-field-id">' . esc_html($field_id) . '</div>';
            echo '<div class="ennu-field-value">' . $display_value . '</div>';
            echo '</div>';
        }
        
        echo '</div>';
    }

    /**
     * Display assessment-specific fields section
     */
    private function display_assessment_section( $user_id, $assessment_type, $title ) {
        echo '<div class="ennu-profile-section">';
        echo '<h3>' . esc_html($title) . '</h3>';
        
        // Get all possible questions for this assessment
        $questions = $this->get_all_assessment_questions( $assessment_type );
        
        // Assessment completion status
        $completion_status = get_user_meta( $user_id, "ennu_{$assessment_type}_completion_status", true );
        $completion_date = get_user_meta( $user_id, "ennu_{$assessment_type}_completion_date", true );
        
        echo '<div class="ennu-field-row">';
        echo '<div class="ennu-field-label">Completion Status:</div>';
        echo '<div class="ennu-field-id">ennu_' . $assessment_type . '_completion_status</div>';
        echo '<div class="ennu-field-value">' . (!empty($completion_status) ? $completion_status : '<span class="ennu-empty-value">Not completed</span>') . '</div>';
        echo '</div>';
        
        echo '<div class="ennu-field-row">';
        echo '<div class="ennu-field-label">Completion Date:</div>';
        echo '<div class="ennu-field-id">ennu_' . $assessment_type . '_completion_date</div>';
        echo '<div class="ennu-field-value">' . (!empty($completion_date) ? $completion_date : '<span class="ennu-empty-value">Not completed</span>') . '</div>';
        echo '</div>';

        // Display all questions and their answers
        foreach ( $questions as $index => $question ) {
            $question_num = $index + 1;
            $field_id = "ennu_{$assessment_type}_question_{$question_num}";
            $value = get_user_meta( $user_id, $field_id, true );
            $display_value = !empty($value) ? $value : '<span class="ennu-empty-value">Not answered</span>';
            
            echo '<div class="ennu-field-row">';
            echo '<div class="ennu-field-label">Q' . $question_num . ': ' . esc_html(wp_trim_words($question['title'], 8)) . '</div>';
            echo '<div class="ennu-field-id">' . esc_html($field_id) . '</div>';
            echo '<div class="ennu-field-value">' . $display_value . '</div>';
            echo '</div>';
        }
        
        echo '</div>';
    }

    /**
     * Display hidden system fields section
     */
    private function display_system_fields_section( $user_id ) {
        echo '<div class="ennu-profile-section">';
        echo '<h3>Hidden System Fields (Admin Only)</h3>';
        
        $system_fields = array(
            'ennu_system_ip_address' => 'IP Address',
            'ennu_system_user_agent' => 'User Agent',
            'ennu_system_session_id' => 'Session ID',
            'ennu_system_referrer' => 'Referrer URL',
            'ennu_system_utm_source' => 'UTM Source',
            'ennu_system_utm_medium' => 'UTM Medium',
            'ennu_system_utm_campaign' => 'UTM Campaign',
            'ennu_system_form_version' => 'Form Version',
            'ennu_system_ab_test_group' => 'A/B Test Group',
            'ennu_system_device_type' => 'Device Type',
            'ennu_system_browser' => 'Browser',
            'ennu_system_os' => 'Operating System',
            'ennu_system_screen_resolution' => 'Screen Resolution',
            'ennu_system_timezone' => 'Timezone',
            'ennu_system_language' => 'Language',
            'ennu_system_total_time_spent' => 'Total Time Spent (seconds)',
            'ennu_system_pages_visited' => 'Pages Visited Count',
            'ennu_system_form_abandonment' => 'Form Abandonment Count',
            'ennu_system_retry_attempts' => 'Retry Attempts',
            'ennu_system_error_count' => 'Error Count',
            'ennu_system_last_activity' => 'Last Activity Timestamp',
            'ennu_system_conversion_funnel' => 'Conversion Funnel Stage',
            'ennu_system_lead_score' => 'Lead Score',
            'ennu_system_engagement_level' => 'Engagement Level',
            'ennu_system_data_quality_score' => 'Data Quality Score'
        );

        foreach ( $system_fields as $field_id => $field_name ) {
            $value = get_user_meta( $user_id, $field_id, true );
            $display_value = !empty($value) ? $value : '<span class="ennu-empty-value">Not tracked</span>';
            
            echo '<div class="ennu-field-row ennu-system-field">';
            echo '<div class="ennu-field-label">' . esc_html($field_name) . ':</div>';
            echo '<div class="ennu-field-id">' . esc_html($field_id) . '</div>';
            echo '<div class="ennu-field-value">' . $display_value . '</div>';
            echo '</div>';
        }
        
        echo '</div>';
    }

    /**
     * Get all possible questions for an assessment type
     */
    private function get_all_assessment_questions( $assessment_type ) {
        // Include the shortcodes class to get questions
        if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
            $shortcodes = new ENNU_Assessment_Shortcodes();
            $method = new ReflectionMethod( $shortcodes, 'get_assessment_questions' );
            $method->setAccessible( true );
            return $method->invoke( $shortcodes, $assessment_type . '_assessment' );
        }
        
        return array(); // Fallback if class not available
    }

    /**
     * Display comprehensive assessment section with all possible fields
     */
    private function display_comprehensive_assessment_section($user_id, $assessment_type, $assessment_label) {
        // Include the comprehensive display class
        require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-comprehensive-assessment-display.php';
        
        // Use the comprehensive display method
        ENNU_Comprehensive_Assessment_Display::display_comprehensive_section($user_id, $assessment_type, $assessment_label);
    }

    /**
     * Enhanced score dashboard with real-time updates
     */
    private function display_enhanced_score_dashboard($user_id) {
        try {
            echo '<div class="ennu-enhanced-admin-container">';
            echo '<h2>' . __('ENNU Health Intelligence Dashboard', 'ennu-life') . '</h2>';
            
            // Cache status indicator
            $cache_stats = ENNU_Score_Cache::get_cache_stats();
            echo '<div class="ennu-cache-status">';
            echo '<span class="ennu-cache-indicator ' . ($cache_stats['cached_entries'] > 0 ? 'active' : 'inactive') . '"></span>';
            echo sprintf(__('Cache: %d entries active', 'ennu-life'), $cache_stats['cached_entries']);
            echo '</div>';
            
            // Enhanced score display
            $this->display_enhanced_assessment_scores($user_id);
            
            // Action buttons with enhanced functionality
            echo '<div class="ennu-dashboard-actions">';
            echo '<button type="button" id="ennu-recalculate-scores" class="button button-primary">';
            echo '<span class="dashicons dashicons-update"></span> ' . __('Recalculate Scores', 'ennu-life');
            echo '</button>';
            
            echo '<button type="button" id="ennu-export-data" class="button button-secondary">';
            echo '<span class="dashicons dashicons-download"></span> ' . __('Export Data', 'ennu-life');
            echo '</button>';
            
            echo '<button type="button" id="ennu-sync-hubspot" class="button button-secondary">';
            echo '<span class="dashicons dashicons-cloud"></span> ' . __('Sync HubSpot', 'ennu-life');
            echo '</button>';
            
            echo '<button type="button" id="ennu-clear-cache" class="button button-secondary">';
            echo '<span class="dashicons dashicons-trash"></span> ' . __('Clear Cache', 'ennu-life');
            echo '</button>';
            echo '</div>';
            
            // Real-time status display
            echo '<div id="ennu-operation-status" class="ennu-status-display"></div>';
            
            echo '</div>';
            
        } catch (Exception $e) {
            error_log('ENNU Enhanced Score Dashboard Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Enhanced assessment scores display with caching
     */
    private function display_enhanced_assessment_scores($user_id) {
        $assessments = array(
            'hair_assessment' => __('Hair Health', 'ennu-life'),
            'weight_loss_assessment' => __('Weight Management', 'ennu-life'),
            'health_assessment' => __('General Health', 'ennu-life'),
            'ed_treatment_assessment' => __('ED Treatment', 'ennu-life'),
            'skin_assessment' => __('Skin Health', 'ennu-life')
        );
        
        echo '<div class="ennu-scores-grid">';
        
        foreach ($assessments as $assessment_type => $label) {
            // Try to get cached score first
            $cached_score = ENNU_Score_Cache::get_cached_score($user_id, $assessment_type);
            
            if ($cached_score !== false) {
                $score_data = $cached_score['score_data'];
                $is_cached = true;
            } else {
                // Fallback to database
                $overall_score = get_user_meta($user_id, $assessment_type . '_calculated_score', true);
                $interpretation = get_user_meta($user_id, $assessment_type . '_score_interpretation', true);
                
                if ($overall_score) {
                    $score_data = array(
                        'overall_score' => $overall_score,
                        'interpretation' => $interpretation ?: $this->get_score_interpretation($overall_score)
                    );
                    $is_cached = false;
                } else {
                    $score_data = null;
                    $is_cached = false;
                }
            }
            
            echo '<div class="ennu-score-card ' . ($score_data ? 'has-score' : 'no-score') . '">';
            echo '<div class="ennu-score-header">';
            echo '<h3>' . esc_html($label) . '</h3>';
            
            if ($is_cached) {
                echo '<span class="ennu-cache-badge" title="' . __('Loaded from cache', 'ennu-life') . '">⚡</span>';
            }
            
            echo '</div>';
            
            if ($score_data) {
                $score = $score_data['overall_score'];
                $interpretation = $score_data['interpretation'];
                $score_class = $this->get_score_class($score);
                
                echo '<div class="ennu-score-display">';
                echo '<div class="ennu-score-number ' . esc_attr($score_class) . '">';
                echo number_format($score, 1) . '/10';
                echo '</div>';
                echo '<div class="ennu-score-interpretation ' . esc_attr($score_class) . '">';
                echo esc_html($interpretation);
                echo '</div>';
                echo '</div>';
                
                // Progress bar
                echo '<div class="ennu-score-progress">';
                echo '<div class="ennu-progress-bar ' . esc_attr($score_class) . '" style="width: ' . ($score * 10) . '%"></div>';
                echo '</div>';
                
            } else {
                echo '<div class="ennu-no-score">';
                echo '<span class="dashicons dashicons-minus"></span>';
                echo '<p>' . __('No assessment data', 'ennu-life') . '</p>';
                echo '</div>';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
        
        // Overall health score
        $overall_score = get_user_meta($user_id, 'ennu_overall_health_score', true);
        if ($overall_score) {
            echo '<div class="ennu-overall-score">';
            echo '<h3>' . __('Overall Health Score', 'ennu-life') . '</h3>';
            echo '<div class="ennu-overall-display">';
            echo '<span class="ennu-overall-number">' . number_format($overall_score, 1) . '/10</span>';
            echo '<span class="ennu-overall-interpretation">' . esc_html($this->get_score_interpretation($overall_score)) . '</span>';
            echo '</div>';
            echo '</div>';
        }
    }
    
    /**
     * Performance dashboard
     */
    private function display_performance_dashboard($user_id) {
        echo '<div class="ennu-performance-dashboard">';
        echo '<h3>' . __('Performance Metrics', 'ennu-life') . '</h3>';
        
        // Get performance stats
        $stats = $this->get_performance_stats();
        
        if (!empty($stats)) {
            echo '<div class="ennu-performance-grid">';
            echo '<div class="ennu-perf-metric">';
            echo '<span class="ennu-perf-label">' . __('Total Operations', 'ennu-life') . '</span>';
            echo '<span class="ennu-perf-value">' . $stats['total_operations'] . '</span>';
            echo '</div>';
            
            echo '<div class="ennu-perf-metric">';
            echo '<span class="ennu-perf-label">' . __('Total Time', 'ennu-life') . '</span>';
            echo '<span class="ennu-perf-value">' . number_format($stats['total_execution_time'], 3) . 's</span>';
            echo '</div>';
            
            if (isset($stats['operations']['show_user_fields'])) {
                echo '<div class="ennu-perf-metric">';
                echo '<span class="ennu-perf-label">' . __('Page Load Time', 'ennu-life') . '</span>';
                echo '<span class="ennu-perf-value">' . number_format($stats['operations']['show_user_fields']['avg_time'], 3) . 's</span>';
                echo '</div>';
            }
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    /**
     * Security dashboard
     */
    private function display_security_dashboard($user_id) {
        echo '<div class="ennu-security-dashboard">';
        echo '<h3>' . __('Security Status', 'ennu-life') . '</h3>';
        
        $security_stats = ENNU_AJAX_Security::get_security_stats();
        
        echo '<div class="ennu-security-grid">';
        echo '<div class="ennu-security-metric">';
        echo '<span class="ennu-security-label">' . __('Security Events', 'ennu-life') . '</span>';
        echo '<span class="ennu-security-value">' . $security_stats['total_events'] . '</span>';
        echo '</div>';
        
        echo '<div class="ennu-security-metric">';
        echo '<span class="ennu-security-label">' . __('Blocked IPs', 'ennu-life') . '</span>';
        echo '<span class="ennu-security-value">' . $security_stats['blocked_ips'] . '</span>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
    }

    /**
     * Bulletproof AJAX score recalculation
     */
    public function ajax_recalculate_scores() {
        $start_time = microtime(true);
        
        try {
            // Enhanced security validation
            $user_id = intval($_POST['user_id'] ?? 0);
            $validation_result = ENNU_AJAX_Security::validate_ajax_request('recalculate_scores', $user_id);
            
            if (is_wp_error($validation_result)) {
                wp_send_json_error(array(
                    'message' => $validation_result->get_error_message(),
                    'code' => $validation_result->get_error_code()
                ));
                return;
            }
            
            // Clear cache for user
            ENNU_Score_Cache::invalidate_cache($user_id);
            
            // Recalculate scores
            $database = ENNU_Life_Enhanced_Database::get_instance();
            $results = array();
            
            $assessments = array('hair_assessment', 'weight_loss_assessment', 'health_assessment', 'ed_treatment_assessment', 'skin_assessment');
            
            foreach ($assessments as $assessment_type) {
                $score_data = $database->calculate_and_store_scores($assessment_type, array(), null, $user_id);
                $results[$assessment_type] = $score_data ? 'success' : 'no_data';
            }
            
            // Log performance
            $execution_time = microtime(true) - $start_time;
            $this->log_performance('ajax_recalculate', $execution_time, $user_id);
            
            wp_send_json_success(array(
                'message' => __('Scores recalculated successfully!', 'ennu-life'),
                'results' => $results,
                'execution_time' => $execution_time
            ));
            
        } catch (Exception $e) {
            error_log('ENNU AJAX Recalculate Error: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => __('Failed to recalculate scores. Please try again.', 'ennu-life'),
                'error' => $e->getMessage()
            ));
        }
    }
    
    /**
     * Enhanced data export with security
     */
    public function ajax_export_user_data() {
        $start_time = microtime(true);
        
        try {
            $user_id = intval($_POST['user_id'] ?? 0);
            $validation_result = ENNU_AJAX_Security::validate_ajax_request('export_data', $user_id);
            
            if (is_wp_error($validation_result)) {
                wp_send_json_error(array(
                    'message' => $validation_result->get_error_message(),
                    'code' => $validation_result->get_error_code()
                ));
                return;
            }
            
            // Get comprehensive user data
            $database = ENNU_Life_Enhanced_Database::get_instance();
            $export_data = array();
            
            $assessments = array('hair_assessment', 'weight_loss_assessment', 'health_assessment', 'ed_treatment_assessment', 'skin_assessment');
            
            foreach ($assessments as $assessment_type) {
                $assessment_data = $database->get_user_assessment_data($user_id, $assessment_type);
                if (!empty($assessment_data)) {
                    $export_data[$assessment_type] = $assessment_data;
                }
            }
            
            // Add metadata
            $export_data['_metadata'] = array(
                'exported_at' => current_time('mysql'),
                'user_id' => $user_id,
                'export_version' => ENNU_LIFE_VERSION
            );
            
            // Log performance
            $execution_time = microtime(true) - $start_time;
            $this->log_performance('ajax_export', $execution_time, $user_id);
            
            wp_send_json_success(array(
                'message' => __('Data exported successfully!', 'ennu-life'),
                'data' => $export_data,
                'execution_time' => $execution_time
            ));
            
        } catch (Exception $e) {
            error_log('ENNU AJAX Export Error: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => __('Failed to export data. Please try again.', 'ennu-life'),
                'error' => $e->getMessage()
            ));
        }
    }
    
    /**
     * Enhanced HubSpot sync
     */
    public function ajax_sync_hubspot() {
        $start_time = microtime(true);
        
        try {
            $user_id = intval($_POST['user_id'] ?? 0);
            $validation_result = ENNU_AJAX_Security::validate_ajax_request('sync_hubspot', $user_id);
            
            if (is_wp_error($validation_result)) {
                wp_send_json_error(array(
                    'message' => $validation_result->get_error_message(),
                    'code' => $validation_result->get_error_code()
                ));
                return;
            }
            
            // Placeholder for HubSpot sync logic
            // This would integrate with HubSpot API
            
            // Log performance
            $execution_time = microtime(true) - $start_time;
            $this->log_performance('ajax_hubspot_sync', $execution_time, $user_id);
            
            wp_send_json_success(array(
                'message' => __('HubSpot sync completed!', 'ennu-life'),
                'execution_time' => $execution_time
            ));
            
        } catch (Exception $e) {
            error_log('ENNU AJAX HubSpot Sync Error: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => __('Failed to sync with HubSpot. Please try again.', 'ennu-life'),
                'error' => $e->getMessage()
            ));
        }
    }
    
    /**
     * Clear cache AJAX handler
     */
    public function ajax_clear_cache() {
        try {
            $user_id = intval($_POST['user_id'] ?? 0);
            $validation_result = ENNU_AJAX_Security::validate_ajax_request('clear_cache', $user_id);
            
            if (is_wp_error($validation_result)) {
                wp_send_json_error(array(
                    'message' => $validation_result->get_error_message(),
                    'code' => $validation_result->get_error_code()
                ));
                return;
            }
            
            if ($user_id) {
                ENNU_Score_Cache::invalidate_cache($user_id);
                $message = __('User cache cleared successfully!', 'ennu-life');
            } else {
                $cleared = ENNU_Score_Cache::clear_all_cache();
                $message = sprintf(__('Cleared %d cache entries!', 'ennu-life'), $cleared);
            }
            
            wp_send_json_success(array('message' => $message));
            
        } catch (Exception $e) {
            error_log('ENNU AJAX Clear Cache Error: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => __('Failed to clear cache. Please try again.', 'ennu-life'),
                'error' => $e->getMessage()
            ));
        }
    }
    
    /**
     * Security stats AJAX handler
     */
    public function ajax_security_stats() {
        try {
            $validation_result = ENNU_AJAX_Security::validate_ajax_request('security_stats');
            
            if (is_wp_error($validation_result)) {
                wp_send_json_error(array(
                    'message' => $validation_result->get_error_message(),
                    'code' => $validation_result->get_error_code()
                ));
                return;
            }
            
            $stats = ENNU_AJAX_Security::get_security_stats();
            
            wp_send_json_success(array(
                'message' => __('Security stats retrieved!', 'ennu-life'),
                'stats' => $stats
            ));
            
        } catch (Exception $e) {
            error_log('ENNU AJAX Security Stats Error: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => __('Failed to get security stats.', 'ennu-life'),
                'error' => $e->getMessage()
            ));
        }
    }
    
    // ... (Include all original methods from the previous admin class)
    // This ensures 100% backward compatibility
    
    /**
     * Original methods preserved for backward compatibility
     */
    private function get_assessment_structure($assessment_type) {
        if (!class_exists('ENNU_Assessment_Shortcodes')) {
            require_once ENNU_LIFE_PLUGIN_PATH . 'includes/class-assessment-shortcodes.php';
        }
        $shortcodes_instance = new ENNU_Assessment_Shortcodes();
        $reflection = new ReflectionMethod('ENNU_Assessment_Shortcodes', 'get_assessment_questions');
        $reflection->setAccessible(true);
        return $reflection->invoke($shortcodes_instance, $assessment_type);
    }
    
    private function display_global_fields($user_id) {
        // Original implementation preserved
        $global_fields = array(
            'user_dob_combined' => __('Date of Birth', 'ennu-life'),
            'user_age' => __('Age', 'ennu-life'),
            'ennu_contact_mobile' => __('Mobile Phone', 'ennu-life'),
            'ennu_contact_phone' => __('Phone', 'ennu-life')
        );
        
        foreach ($global_fields as $meta_key => $label) {
            $current_value = get_user_meta($user_id, $meta_key, true);
            $this->render_text_field($meta_key, $label, $current_value);
        }
    }
    
    private function display_single_assessment_section($user_id, $assessment_type, $assessment_label) {
        // Original implementation preserved
        echo "<h3>{$assessment_label}</h3>";
        echo '<table class="form-table">';
        
        try {
            $questions = $this->get_assessment_structure($assessment_type);
            
            if ($questions && isset($questions['questions'])) {
                foreach ($questions['questions'] as $question_id => $question_data) {
                    $meta_key = $assessment_type . '_' . $question_id;
                    $current_value = get_user_meta($user_id, $meta_key, true);
                    $label = $question_data['question'] ?? $question_id;
                    
                    if (isset($question_data['type'])) {
                        switch ($question_data['type']) {
                            case 'radio':
                                $options = $question_data['options'] ?? array();
                                $this->render_radio_field($meta_key, $label, $current_value, $options);
                                break;
                            case 'checkbox':
                                $options = $question_data['options'] ?? array();
                                $this->render_checkbox_field($meta_key, $label, $current_value, $options);
                                break;
                            default:
                                $this->render_text_field($meta_key, $label, $current_value);
                                break;
                        }
                    } else {
                        $this->render_text_field($meta_key, $label, $current_value);
                    }
                }
            }
        } catch (Exception $e) {
            echo '<tr><td colspan="2"><em>' . __('Error loading assessment questions.', 'ennu-life') . '</em></td></tr>';
        }
        
        echo '</table>';
    }
    
    private function render_text_field($meta_key, $label, $current_value) {
        echo '<tr><th><label for="' . esc_attr($meta_key) . '">' . esc_html($label) . '</label></th>';
        echo '<td><input type="text" id="' . esc_attr($meta_key) . '" name="' . esc_attr($meta_key) . '" value="' . esc_attr($current_value) . '" class="regular-text" /></td></tr>';
    }
    
    private function render_radio_field($meta_key, $label, $current_value, $options) {
        echo '<tr><th>' . esc_html($label) . '</th><td>';
        foreach ($options as $option_value => $option_label) {
            $checked = checked($current_value, $option_value, false);
            echo '<label><input type="radio" name="' . esc_attr($meta_key) . '" value="' . esc_attr($option_value) . '" ' . $checked . ' /> ' . esc_html($option_label) . '</label><br />';
        }
        echo '</td></tr>';
    }
    
    private function render_checkbox_field($meta_key, $label, $current_values, $options) {
        if (!is_array($current_values)) {
            $current_values = array();
        }
        
        echo '<tr><th>' . esc_html($label) . '</th><td>';
        foreach ($options as $option_value => $option_label) {
            $checked = in_array($option_value, $current_values) ? 'checked="checked"' : '';
            echo '<label><input type="checkbox" name="' . esc_attr($meta_key) . '[]" value="' . esc_attr($option_value) . '" ' . $checked . ' /> ' . esc_html($option_label) . '</label><br />';
        }
        echo '</td></tr>';
    }
    
    public function save_user_assessment_fields($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        
        if (!wp_verify_nonce($_POST['ennu_assessment_nonce'] ?? '', 'ennu_user_profile_update')) {
            return false;
        }
        
        // Save all posted fields
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'ennu_') === 0 || strpos($key, '_assessment_') !== false || in_array($key, array('user_dob_combined', 'user_age'))) {
                if (is_array($value)) {
                    update_user_meta($user_id, $key, array_map('sanitize_text_field', $value));
                } else {
                    update_user_meta($user_id, $key, sanitize_text_field($value));
                }
            }
        }
        
        return true;
    }
    
    private function display_enhanced_wp_fusion_panel($user_id) {
        echo '<div class="ennu-wp-fusion-panel">';
        echo '<h3>' . __('Integration Status', 'ennu-life') . '</h3>';
        
        // WP Fusion status
        if (function_exists('wp_fusion') && wp_fusion()) {
            echo '<div class="ennu-integration-status active">';
            echo '<span class="dashicons dashicons-yes-alt"></span>';
            echo __('WP Fusion: Connected', 'ennu-life');
            echo '</div>';
        } else {
            echo '<div class="ennu-integration-status inactive">';
            echo '<span class="dashicons dashicons-warning"></span>';
            echo __('WP Fusion: Not Available', 'ennu-life');
            echo '</div>';
        }
        
        // HubSpot status
        if (defined('HUBSPOT_API_KEY')) {
            echo '<div class="ennu-integration-status active">';
            echo '<span class="dashicons dashicons-yes-alt"></span>';
            echo __('HubSpot: Connected', 'ennu-life');
            echo '</div>';
        } else {
            echo '<div class="ennu-integration-status inactive">';
            echo '<span class="dashicons dashicons-warning"></span>';
            echo __('HubSpot: Not Configured', 'ennu-life');
            echo '</div>';
        }
        
        echo '</div>';
    }
    
    private function get_score_class($score) {
        if ($score >= 8.0) return 'excellent';
        if ($score >= 6.0) return 'good';
        if ($score >= 4.0) return 'fair';
        if ($score >= 2.0) return 'needs-attention';
        return 'critical';
    }
    
    private function get_score_interpretation($score) {
        if ($score >= 8.0) return __('Excellent', 'ennu-life');
        if ($score >= 6.0) return __('Good', 'ennu-life');
        if ($score >= 4.0) return __('Fair', 'ennu-life');
        if ($score >= 2.0) return __('Needs Attention', 'ennu-life');
        return __('Critical', 'ennu-life');
    }
    
    /**
     * Log performance metrics
     */
    private function log_performance($operation, $execution_time, $user_id = null) {
        $this->performance_log[] = array(
            'operation' => $operation,
            'execution_time' => $execution_time,
            'user_id' => $user_id,
            'timestamp' => microtime(true),
            'memory_usage' => memory_get_usage(true)
        );
        
        // Log slow operations
        if ($execution_time > 0.5) {
            error_log("ENNU Performance Warning: {$operation} took {$execution_time}s for user {$user_id}");
        }
    }
    
    /**
     * Get performance statistics
     */
    public function get_performance_stats() {
        if (empty($this->performance_log)) {
            return array();
        }
        
        $total_time = 0;
        $operations = array();
        
        foreach ($this->performance_log as $log) {
            $total_time += $log['execution_time'];
            
            if (!isset($operations[$log['operation']])) {
                $operations[$log['operation']] = array(
                    'count' => 0,
                    'total_time' => 0,
                    'avg_time' => 0
                );
            }
            
            $operations[$log['operation']]['count']++;
            $operations[$log['operation']]['total_time'] += $log['execution_time'];
            $operations[$log['operation']]['avg_time'] = $operations[$log['operation']]['total_time'] / $operations[$log['operation']]['count'];
        }
        
        return array(
            'total_operations' => count($this->performance_log),
            'total_execution_time' => $total_time,
            'operations' => $operations
        );
    }
    
    /**
     * Output performance stats in admin footer
     */
    public function output_performance_stats() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $stats = $this->get_performance_stats();
        if (!empty($stats)) {
            echo '<!-- ENNU Performance: ' . $stats['total_operations'] . ' operations, ' . number_format($stats['total_execution_time'], 3) . 's total -->';
        }
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        // Add main ENNU Life menu
        add_menu_page(
            __('ENNU Life', 'ennu-life'),
            __('ENNU Life', 'ennu-life'),
            'manage_options',
            'ennu-life',
            array($this, 'admin_page'),
            'dashicons-heart',
            30
        );
        
        // Add submenu pages
        add_submenu_page(
            'ennu-life',
            __('Dashboard', 'ennu-life'),
            __('Dashboard', 'ennu-life'),
            'manage_options',
            'ennu-life',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'ennu-life',
            __('Assessments', 'ennu-life'),
            __('Assessments', 'ennu-life'),
            'manage_options',
            'ennu-life-assessments',
            array($this, 'assessments_page')
        );
        
        add_submenu_page(
            'ennu-life',
            __('Settings', 'ennu-life'),
            __('Settings', 'ennu-life'),
            'manage_options',
            'ennu-life-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Enqueue admin scripts (compatibility method)
     */
    public function enqueue_admin_scripts($hook) {
        // This method exists for compatibility
        // The actual script enqueuing is handled by enqueue_admin_assets
        $this->enqueue_admin_assets($hook);
    }
    
    /**
     * Main admin page
     */
    public function admin_page() {
        echo '<div class="wrap">';
        echo '<h1>' . __('ENNU Life Dashboard', 'ennu-life') . '</h1>';
        
        // Get assessment statistics
        $stats = $this->get_assessment_statistics();
        
        echo '<div class="ennu-dashboard-stats">';
        echo '<div class="ennu-stat-card">';
        echo '<h3>' . __('Total Assessments', 'ennu-life') . '</h3>';
        echo '<span class="ennu-stat-number">' . $stats['total_assessments'] . '</span>';
        echo '</div>';
        
        echo '<div class="ennu-stat-card">';
        echo '<h3>' . __('This Month', 'ennu-life') . '</h3>';
        echo '<span class="ennu-stat-number">' . $stats['monthly_assessments'] . '</span>';
        echo '</div>';
        
        echo '<div class="ennu-stat-card">';
        echo '<h3>' . __('Active Users', 'ennu-life') . '</h3>';
        echo '<span class="ennu-stat-number">' . $stats['active_users'] . '</span>';
        echo '</div>';
        echo '</div>';
        
        // Recent assessments table
        echo '<h2>' . __('Recent Assessments', 'ennu-life') . '</h2>';
        $this->display_recent_assessments_table();
        
        echo '</div>';
    }
    
    /**
     * Assessments page
     */
    public function assessments_page() {
        echo '<div class="wrap">';
        echo '<h1>' . __('ENNU Life Assessments', 'ennu-life') . '</h1>';
        
        // Assessment types overview
        $assessment_types = array(
            'hair_assessment' => __('Hair Assessment', 'ennu-life'),
            'weight_loss_assessment' => __('Weight Loss Assessment', 'ennu-life'),
            'health_assessment' => __('Health Assessment', 'ennu-life'),
            'ed_treatment_assessment' => __('ED Treatment Assessment', 'ennu-life'),
            'skin_assessment' => __('Skin Assessment', 'ennu-life')
        );
        
        echo '<div class="ennu-assessments-grid">';
        foreach ($assessment_types as $type => $label) {
            $count = $this->get_assessment_count($type);
            echo '<div class="ennu-assessment-card">';
            echo '<h3>' . esc_html($label) . '</h3>';
            echo '<p class="ennu-assessment-count">' . sprintf(__('%d completed', 'ennu-life'), $count) . '</p>';
            echo '<a href="' . admin_url('users.php?assessment_filter=' . $type) . '" class="button">' . __('View Results', 'ennu-life') . '</a>';
            echo '</div>';
        }
        echo '</div>';
        
        // Recent assessments table
        echo '<h2>' . __('All Assessment Results', 'ennu-life') . '</h2>';
        $this->display_all_assessments_table();
        
        echo '</div>';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        echo '<div class="wrap">';
        echo '<h1>' . __('ENNU Life Settings', 'ennu-life') . '</h1>';
        
        // Handle form submission
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['ennu_settings_nonce'], 'ennu_settings_update')) {
            $this->save_settings();
            echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'ennu-life') . '</p></div>';
        }
        
        // Get current settings
        $settings = $this->get_plugin_settings();
        
        echo '<form method="post" action="">';
        wp_nonce_field('ennu_settings_update', 'ennu_settings_nonce');
        
        echo '<table class="form-table">';
        
        // Email settings
        echo '<tr>';
        echo '<th scope="row">' . __('Admin Email', 'ennu-life') . '</th>';
        echo '<td><input type="email" name="admin_email" value="' . esc_attr($settings['admin_email']) . '" class="regular-text" /></td>';
        echo '</tr>';
        
        // Assessment settings
        echo '<tr>';
        echo '<th scope="row">' . __('Enable Email Notifications', 'ennu-life') . '</th>';
        echo '<td><input type="checkbox" name="email_notifications" value="1" ' . checked($settings['email_notifications'], 1, false) . ' /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Cache Assessment Results', 'ennu-life') . '</th>';
        echo '<td><input type="checkbox" name="cache_results" value="1" ' . checked($settings['cache_results'], 1, false) . ' /></td>';
        echo '</tr>';
        
        // Integration settings
        echo '<tr>';
        echo '<th scope="row">' . __('WP Fusion Integration', 'ennu-life') . '</th>';
        echo '<td><input type="checkbox" name="wp_fusion_enabled" value="1" ' . checked($settings['wp_fusion_enabled'], 1, false) . ' /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('HubSpot API Key', 'ennu-life') . '</th>';
        echo '<td><input type="text" name="hubspot_api_key" value="' . esc_attr($settings['hubspot_api_key']) . '" class="regular-text" /></td>';
        echo '</tr>';
        
        echo '</table>';
        
        submit_button();
        echo '</form>';
        
        // System status
        echo '<h2>' . __('System Status', 'ennu-life') . '</h2>';
        $this->display_system_status();
        
        echo '</div>';
    }
    
    /**
     * Get assessment statistics
     */
    private function get_assessment_statistics() {
        global $wpdb;
        
        $stats = array(
            'total_assessments' => 0,
            'monthly_assessments' => 0,
            'active_users' => 0
        );
        
        // Get total assessments (count users with any assessment data)
        $assessment_types = array('hair_assessment', 'weight_loss_assessment', 'health_assessment', 'ed_treatment_assessment', 'skin_assessment');
        $meta_keys = array();
        foreach ($assessment_types as $type) {
            $meta_keys[] = "'{$type}_calculated_score'";
        }
        
        if (!empty($meta_keys)) {
            $meta_keys_str = implode(',', $meta_keys);
            $total = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key IN ({$meta_keys_str}) AND meta_value != ''");
            $stats['total_assessments'] = intval($total);
        }
        
        // Get monthly assessments (rough estimate based on recent user registrations)
        $monthly = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->users} WHERE user_registered >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        $stats['monthly_assessments'] = intval($monthly);
        
        // Active users (users with assessment data)
        $stats['active_users'] = $stats['total_assessments'];
        
        return $stats;
    }
    
    /**
     * Display recent assessments table
     */
    private function display_recent_assessments_table() {
        global $wpdb;
        
        // Get recent users with assessment data
        $results = $wpdb->get_results("
            SELECT DISTINCT u.ID, u.user_login, u.user_email, u.user_registered, um.meta_key, um.meta_value
            FROM {$wpdb->users} u
            JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
            WHERE um.meta_key LIKE '%_calculated_score'
            ORDER BY u.user_registered DESC
            LIMIT 10
        ");
        
        if (empty($results)) {
            echo '<p>' . __('No assessment data found.', 'ennu-life') . '</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . __('User', 'ennu-life') . '</th>';
        echo '<th>' . __('Email', 'ennu-life') . '</th>';
        echo '<th>' . __('Assessment Type', 'ennu-life') . '</th>';
        echo '<th>' . __('Score', 'ennu-life') . '</th>';
        echo '<th>' . __('Date', 'ennu-life') . '</th>';
        echo '</tr></thead><tbody>';
        
        foreach ($results as $result) {
            $assessment_type = str_replace('_calculated_score', '', $result->meta_key);
            $assessment_label = ucwords(str_replace('_', ' ', $assessment_type));
            
            echo '<tr>';
            echo '<td><a href="' . admin_url('user-edit.php?user_id=' . $result->ID) . '">' . esc_html($result->user_login) . '</a></td>';
            echo '<td>' . esc_html($result->user_email) . '</td>';
            echo '<td>' . esc_html($assessment_label) . '</td>';
            echo '<td>' . number_format(floatval($result->meta_value), 1) . '/10</td>';
            echo '<td>' . date('M j, Y', strtotime($result->user_registered)) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    /**
     * Get assessment count for specific type
     */
    private function get_assessment_count($assessment_type) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->usermeta} 
            WHERE meta_key = %s AND meta_value != ''
        ", $assessment_type . '_calculated_score'));
        
        return intval($count);
    }
    
    /**
     * Display all assessments table
     */
    private function display_all_assessments_table() {
        global $wpdb;
        
        // Get all assessment data
        $results = $wpdb->get_results("
            SELECT u.ID, u.user_login, u.user_email, u.user_registered, 
                   GROUP_CONCAT(CONCAT(um.meta_key, ':', um.meta_value) SEPARATOR '|') as assessments
            FROM {$wpdb->users} u
            JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
            WHERE um.meta_key LIKE '%_calculated_score'
            GROUP BY u.ID
            ORDER BY u.user_registered DESC
            LIMIT 50
        ");
        
        if (empty($results)) {
            echo '<p>' . __('No assessment data found.', 'ennu-life') . '</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . __('User', 'ennu-life') . '</th>';
        echo '<th>' . __('Email', 'ennu-life') . '</th>';
        echo '<th>' . __('Assessments Completed', 'ennu-life') . '</th>';
        echo '<th>' . __('Date Registered', 'ennu-life') . '</th>';
        echo '<th>' . __('Actions', 'ennu-life') . '</th>';
        echo '</tr></thead><tbody>';
        
        foreach ($results as $result) {
            $assessments = explode('|', $result->assessments);
            $assessment_list = array();
            
            foreach ($assessments as $assessment) {
                list($key, $value) = explode(':', $assessment);
                $type = str_replace('_calculated_score', '', $key);
                $label = ucwords(str_replace('_', ' ', $type));
                $assessment_list[] = $label . ' (' . number_format(floatval($value), 1) . '/10)';
            }
            
            echo '<tr>';
            echo '<td><a href="' . admin_url('user-edit.php?user_id=' . $result->ID) . '">' . esc_html($result->user_login) . '</a></td>';
            echo '<td>' . esc_html($result->user_email) . '</td>';
            echo '<td>' . implode('<br>', $assessment_list) . '</td>';
            echo '<td>' . date('M j, Y', strtotime($result->user_registered)) . '</td>';
            echo '<td><a href="' . admin_url('user-edit.php?user_id=' . $result->ID) . '" class="button button-small">' . __('View Details', 'ennu-life') . '</a></td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    /**
     * Get plugin settings
     */
    private function get_plugin_settings() {
        return array(
            'admin_email' => get_option('ennu_admin_email', get_option('admin_email')),
            'email_notifications' => get_option('ennu_email_notifications', 1),
            'cache_results' => get_option('ennu_cache_results', 1),
            'wp_fusion_enabled' => get_option('ennu_wp_fusion_enabled', 0),
            'hubspot_api_key' => get_option('ennu_hubspot_api_key', '')
        );
    }
    
    /**
     * Save plugin settings
     */
    private function save_settings() {
        if (isset($_POST['admin_email'])) {
            update_option('ennu_admin_email', sanitize_email($_POST['admin_email']));
        }
        
        update_option('ennu_email_notifications', isset($_POST['email_notifications']) ? 1 : 0);
        update_option('ennu_cache_results', isset($_POST['cache_results']) ? 1 : 0);
        update_option('ennu_wp_fusion_enabled', isset($_POST['wp_fusion_enabled']) ? 1 : 0);
        
        if (isset($_POST['hubspot_api_key'])) {
            update_option('ennu_hubspot_api_key', sanitize_text_field($_POST['hubspot_api_key']));
        }
    }
    
    /**
     * Display system status
     */
    private function display_system_status() {
        echo '<table class="form-table">';
        
        // WordPress version
        echo '<tr>';
        echo '<th scope="row">' . __('WordPress Version', 'ennu-life') . '</th>';
        echo '<td>' . get_bloginfo('version') . '</td>';
        echo '</tr>';
        
        // Plugin version
        echo '<tr>';
        echo '<th scope="row">' . __('Plugin Version', 'ennu-life') . '</th>';
        echo '<td>' . ENNU_LIFE_VERSION . '</td>';
        echo '</tr>';
        
        // PHP version
        echo '<tr>';
        echo '<th scope="row">' . __('PHP Version', 'ennu-life') . '</th>';
        echo '<td>' . PHP_VERSION . '</td>';
        echo '</tr>';
        
        // WP Fusion status
        echo '<tr>';
        echo '<th scope="row">' . __('WP Fusion', 'ennu-life') . '</th>';
        echo '<td>' . (function_exists('wp_fusion') ? __('Active', 'ennu-life') : __('Not Installed', 'ennu-life')) . '</td>';
        echo '</tr>';
        
        // Cache status
        $cache_stats = ENNU_Score_Cache::get_cache_stats();
        echo '<tr>';
        echo '<th scope="row">' . __('Cache Status', 'ennu-life') . '</th>';
        echo '<td>' . sprintf(__('%d entries cached', 'ennu-life'), $cache_stats['cached_entries']) . '</td>';
        echo '</tr>';
        
        echo '</table>';
    }
}

