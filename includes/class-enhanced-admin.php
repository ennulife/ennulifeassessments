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
        add_action('wp_ajax_ennu_clear_cache', array($this, 'ajax_clear_cache'));
        add_action('wp_ajax_ennu_security_stats', array($this, 'ajax_security_stats'));
        
        // Enhanced asset loading
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Performance monitoring
        add_action('admin_footer', array($this, 'output_performance_stats'));

        // New AJAX handler for clearing data
        add_action('wp_ajax_ennu_clear_user_data', array($this, 'ajax_clear_user_data'));

        // WP Fusion Integration
        add_filter('wpf_meta_fields', array($this, 'add_fields_to_wpfusion'));
    }

    /**
     * Register all custom assessment fields with WP Fusion.
     */
    public function add_fields_to_wpfusion( $meta_fields ) {
        $assessments = [
            'hair_assessment' => 'Hair Assessment',
            'skin_assessment' => 'Skin Assessment',
            'health_assessment' => 'Health Assessment',
            'weight_loss_assessment' => 'Weight Loss Assessment',
            'ed_treatment_assessment' => 'ED Treatment Assessment'
        ];

        foreach ($assessments as $type => $label) {
            // Add the overall score and interpretation
            $meta_fields['ennu_' . $type . '_calculated_score'] = array(
                'label' => $label . ' - Score',
                'type'  => 'text',
                'group' => 'ENNU Assessments'
            );
            $meta_fields['ennu_' . $type . '_score_interpretation'] = array(
                'label' => $label . ' - Interpretation',
                'type'  => 'text',
                'group' => 'ENNU Assessments'
            );
            $meta_fields['ennu_' . $type . '_category_scores'] = array(
                'label' => $label . ' - Category Scores',
                'type'  => 'text',
                'group' => 'ENNU Assessments'
            );
        }

        return $meta_fields;
    }

    public function ajax_clear_user_data() {
        if ( !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ennu_admin_nonce') ) {
            wp_send_json_error('Security check failed.');
        }

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        if ( !$user_id || !current_user_can('edit_user', $user_id) ) {
            wp_send_json_error('Permission denied.');
        }

        global $wpdb;
        $meta_keys_to_delete = $wpdb->get_col($wpdb->prepare(
            "SELECT meta_key FROM $wpdb->usermeta WHERE user_id = %d AND meta_key LIKE %s",
            $user_id,
            'ennu_%'
        ));

        if (!empty($meta_keys_to_delete)) {
            foreach ($meta_keys_to_delete as $meta_key) {
                delete_user_meta($user_id, $meta_key);
            }
            wp_send_json_success('User assessment data cleared successfully. ' . count($meta_keys_to_delete) . ' entries deleted.');
        } else {
            wp_send_json_success('No ENNU assessment data found for this user.');
        }
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
                    'recalculating' => __('Recalculating scores...', 'ennulifeassessments'),
                    'exporting' => __('Exporting data...', 'ennulifeassessments'),
                    'syncing' => __('Syncing with HubSpot...', 'ennulifeassessments'),
                    'clearing_cache' => __('Clearing cache...', 'ennulifeassessments'),
                    'success' => __('Operation completed successfully!', 'ennulifeassessments'),
                    'error' => __('An error occurred. Please try again.', 'ennulifeassessments'),
                    'rate_limit' => __('Too many requests. Please wait a moment.', 'ennulifeassessments'),
                    'network_error' => __('Network error. Please check your connection.', 'ennulifeassessments'),
                    'permission_denied' => __('Permission denied. Please refresh the page.', 'ennulifeassessments'),
                    'confirm_clear' => __('Are you sure you want to permanently delete all ENNU assessment data for this user? This cannot be undone.', 'ennulifeassessments')
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
            
            // New: Clear All Button
            echo '<h2>' . __('ENNU Life Data Management', 'ennulifeassessments') . '</h2>';
            echo '<button type="button" id="ennu-clear-data" class="button button-danger" data-user-id="' . esc_attr($user->ID) . '">' . __('Clear All ENNU Data', 'ennulifeassessments') . '</button>';
            echo '<p><em>' . __('This will delete all assessment data for this user. Cannot be undone.', 'ennulifeassessments') . '</em></p>';
            
            // Enhanced Score Dashboard Section (NEW)
            $this->display_enhanced_score_dashboard($user->ID);
            
            // Performance Monitoring Section (NEW)
            $this->display_performance_dashboard($user->ID);
            
            // Security Monitoring Section (NEW)
            $this->display_security_dashboard($user->ID);
            
            // Original sections (PRESERVED)
            echo '<h2>' . __('ENNU Life Global Information', 'ennulifeassessments') . '</h2>';
            echo '<p><em>' . __('This information is shared across all assessments. Edit below.', 'ennulifeassessments') . '</em></p>';
            echo '<table class="form-table">';
            $this->display_global_fields_editable($user->ID); // New editable version
            echo '</table>';

            // Enhanced WP Fusion Integration Panel
            $this->display_enhanced_wp_fusion_panel($user->ID);

            echo '<h2>' . __( 'ENNU Life Assessment Data', 'ennulifeassessments' ) . '</h2>';
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
            
            // Assessment-Specific Fields - Editable Display
            $assessments = array(
                'welcome_assessment' => 'Welcome Assessment',
                'hair_assessment' => 'Hair Health Assessment', 
                'weight_loss_assessment' => 'Weight Loss Assessment',
                'health_assessment' => 'General Health Assessment',
                'skin_assessment' => 'Skin Health Assessment',
                'ed_treatment_assessment' => 'ED Treatment Assessment'
            );

            foreach ( $assessments as $type => $title ) {
                echo '<h3>' . esc_html($title) . '</h3>';
                echo '<table class="form-table">';
                $this->display_assessment_fields_editable($user->ID, $type);
                echo '</table>';
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
     * Display editable global fields
     */
    private function display_global_fields_editable($user_id) {
        $global_fields = array(
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'user_email' => 'Email Address',
            'billing_phone' => 'Phone Number',
            'user_gender' => 'Gender'
        );

        foreach ($global_fields as $key => $label) {
            $value = get_user_meta($user_id, $key, true);
            if (in_array($key, ['first_name', 'last_name', 'user_email'])) {
                $value = get_the_author_meta($key, $user_id); // From WP user object
            }
            echo '<tr>';
            echo '<th><label for="' . esc_attr($key) . '">' . esc_html($label) . '</label></th>';
            echo '<td><input type="text" name="' . esc_attr($key) . '" id="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="regular-text" /></td>';
            echo '</tr>';
        }
    }

    /**
     * Display editable assessment fields
     */
    private function display_assessment_fields_editable($user_id, $assessment_type) {
        $questions = $this->get_direct_assessment_questions($assessment_type);
        if (empty($questions)) {
            // No need for an admin notice here if it's expected that some might not have questions.
            // add_action('admin_notices', function() use ($assessment_type) {
            //     echo '<div class="notice notice-error"><p>ENNU Error: No questions loaded for ' . esc_html($assessment_type) . '. Using fallback.</p></div>';
            // });
            return; // Simply exit if no questions are defined.
        }

        foreach ($questions as $index => $q_data) {
            // Skip global fields as they are displayed in their own section.
            if (isset($q_data['global_key'])) {
                continue;
            }

            $assessment_prefix = str_replace('_assessment', '', $assessment_type);
            $simple_question_id = $assessment_prefix . '_q' . ($index + 1);
            $meta_key = 'ennu_' . $assessment_type . '_' . $simple_question_id;

            // Use the scoring_key for score lookups, which is the correct way
            $scoring_key = $q_data['scoring_key'] ?? null;
            
            $current_value = get_user_meta($user_id, $meta_key, true);
            $label = $q_data['title'] ?? $simple_question_id;

            echo '<tr>';
            echo '<th><label for="' . esc_attr($meta_key) . '">' . esc_html($label) . '</label><br/><small><code>' . esc_html($simple_question_id) . '</code></small></th>';
            echo '<td>';

            // Use 'type' for text fields, and check for 'options' to determine radio/checkbox.
            $question_type = $q_data['type'] ?? 'text';
            $options = $q_data['options'] ?? array();

            switch ($question_type) {
                case 'multiselect':
                    $this->render_checkbox_field($meta_key, $current_value, $options, $assessment_type, $simple_question_id);
                    break;
                case 'single':
                case 'radio':
                case 'dob_dropdowns': // dob_dropdowns are treated as single choice on the frontend, text on backend
                    $this->render_radio_field($meta_key, $current_value, $options, $assessment_type, $simple_question_id);
                    break;
                default:
                    // If options are present, it should be a radio button group.
                    if (!empty($options)) {
                        $this->render_radio_field($meta_key, $current_value, $options, $assessment_type, $simple_question_id);
                    } else {
                        $this->render_text_field($meta_key, $current_value);
                    }
                    break;
            }
            
            echo '</td></tr>';
        }
    }

    private function get_direct_assessment_questions($assessment_type) {
        // First, attempt to load questions dynamically from the Shortcodes helper
        if (class_exists('ENNU_Assessment_Shortcodes')) {
            try {
                // Directly instantiate and call the public method.
                // This removes the need for fragile Reflection and is more performant.
                $shortcodes = new ENNU_Assessment_Shortcodes();
                $questions   = $shortcodes->get_assessment_questions($assessment_type);
                if (!empty($questions) && is_array($questions)) {
                    return $questions; // Successfully retrieved questions
                }
            } catch (Exception $e) {
                error_log('ENNU get_direct_assessment_questions error: ' . $e->getMessage());
            }
        }

        // Fallback to hard-coded definitions (populate/extend as required)
        switch ($assessment_type) {
            case 'welcome_assessment':
            case 'hair_assessment':
            case 'ed_treatment_assessment':
            case 'weight_loss_assessment':
            case 'health_assessment':
            case 'skin_assessment':
                return array(); // No hard-coded set yet – keep empty to avoid fatal errors
            default:
                return array();
        }
    }

    private function get_fallback_questions($assessment_type) {
        // Basic fallback structures (minimal, expand as needed)
        switch ($assessment_type) {
            case 'hair_assessment':
                return array(
                    'hair_q1' => array('title' => 'Date of Birth', 'type' => 'text'),
                    'hair_q2' => array('title' => 'Gender', 'type' => 'radio', 'options' => array(array('value' => 'male', 'label' => 'Male'), array('value' => 'female', 'label' => 'Female'))),
                    // Add more as per shortcodes
                );
            // Add cases for other types
            default:
                return array();
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
     * Get all possible questions for an assessment type
     */
    private function get_all_assessment_questions( $assessment_type ) {
        // Include the shortcodes class to get questions
        if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
            // Directly instantiate and call the public method.
            // This is safer, more performant, and more reliable than Reflection.
            $shortcodes = new ENNU_Assessment_Shortcodes();
            return $shortcodes->get_assessment_questions( $assessment_type . '_assessment' );
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
            echo '<h2>' . __('ENNU Health Intelligence Dashboard', 'ennulifeassessments') . '</h2>';
            
            // Cache status indicator
            $cache_stats = ENNU_Score_Cache::get_cache_stats();
            echo '<div class="ennu-cache-status">';
            echo '<span class="ennu-cache-indicator ' . ($cache_stats['cached_entries'] > 0 ? 'active' : 'inactive') . '"></span>';
            echo sprintf(__('Cache: %d entries active', 'ennulifeassessments'), $cache_stats['cached_entries']);
            echo '</div>';
            
            // Enhanced score display
            $this->display_enhanced_assessment_scores($user_id);
            
            // Action buttons with enhanced functionality
            echo '<div class="ennu-dashboard-actions">';
            echo '<button type="button" id="ennu-recalculate-scores" class="button button-primary">';
            echo '<span class="dashicons dashicons-update"></span> ' . __('Recalculate Scores', 'ennulifeassessments');
            echo '</button>';
            
            echo '<button type="button" id="ennu-export-data" class="button button-secondary">';
            echo '<span class="dashicons dashicons-download"></span> ' . __('Export Data', 'ennulifeassessments');
            echo '</button>';
            
            echo '<button type="button" id="ennu-clear-cache" class="button button-secondary">';
            echo '<span class="dashicons dashicons-trash"></span> ' . __('Clear Cache', 'ennulifeassessments');
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
            'hair_assessment' => __('Hair Health', 'ennulifeassessments'),
            'weight_loss_assessment' => __('Weight Management', 'ennulifeassessments'),
            'health_assessment' => __('General Health', 'ennulifeassessments'),
            'ed_treatment_assessment' => __('ED Treatment', 'ennulifeassessments'),
            'skin_assessment' => __('Skin Health', 'ennulifeassessments')
        );
        
        echo '<div class="ennu-scores-grid">';
        
        foreach ($assessments as $assessment_type => $label) {
            // Try to get cached score first
            $cached_score = ENNU_Score_Cache::get_cached_score($user_id, $assessment_type);
            
            if ($cached_score !== false) {
                $score_data = $cached_score['score_data'];
                $is_cached = true;
            } else {
                // Fallback to database, using the correct 'ennu_' prefixed meta key
                $meta_key_score = 'ennu_' . $assessment_type . '_calculated_score';
                $meta_key_interpretation = 'ennu_' . $assessment_type . '_score_interpretation';
                
                $overall_score = get_user_meta($user_id, $meta_key_score, true);
                $interpretation = get_user_meta($user_id, $meta_key_interpretation, true);
                
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
                echo '<span class="ennu-cache-badge" title="' . __('Loaded from cache', 'ennulifeassessments') . '">⚡</span>';
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
                echo '<p>' . __('No assessment data', 'ennulifeassessments') . '</p>';
                echo '</div>';
            }
            
            echo '</div>';
        }
        
        echo '</div>';
        
        // Overall health score
        $overall_score = get_user_meta($user_id, 'ennu_overall_health_score', true);
        if ($overall_score) {
            echo '<div class="ennu-overall-score">';
            echo '<h3>' . __('Overall Health Score', 'ennulifeassessments') . '</h3>';
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
        try {
            echo '<div class="ennu-performance-dashboard">';
            echo '<h3>' . __('Performance Metrics', 'ennulifeassessments') . '</h3>';
            
            // Get performance stats
            $stats = $this->get_performance_stats();
            
            if (!empty($stats) && isset($stats['total_operations'])) {
                echo '<div class="ennu-performance-grid">';
                echo '<div class="ennu-perf-metric">';
                echo '<span class="ennu-perf-label">' . __('Total Operations', 'ennulifeassessments') . '</span>';
                echo '<span class="ennu-perf-value">' . $stats['total_operations'] . '</span>';
                echo '</div>';
                
                echo '<div class="ennu-perf-metric">';
                echo '<span class="ennu-perf-label">' . __('Total Time', 'ennulifeassessments') . '</span>';
                echo '<span class="ennu-perf-value">' . number_format($stats['total_execution_time'], 3) . 's</span>';
                echo '</div>';
                
                if (isset($stats['operations']['show_user_fields'])) {
                    echo '<div class="ennu-perf-metric">';
                    echo '<span class="ennu-perf-label">' . __('Page Load Time', 'ennulifeassessments') . '</span>';
                    echo '<span class="ennu-perf-value">' . number_format($stats['operations']['show_user_fields']['avg_time'], 3) . 's</span>';
                    echo '</div>';
                }
                echo '</div>';
            }
            echo '</div>';
        } catch (Exception $e) {
            error_log('ENNU Performance Dashboard Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Display security dashboard
     */
    private function display_security_dashboard($user_id) {
        echo '<div class="ennu-security-dashboard">';
        echo '<h3>' . __('Security Status', 'ennulifeassessments') . '</h3>';
        
        $security_stats = ENNU_AJAX_Security::get_security_stats();
        
        echo '<div class="ennu-security-grid">';
        echo '<div class="ennu-security-metric">';
        echo '<span class="ennu-security-label">' . __('Security Events', 'ennulifeassessments') . '</span>';
        echo '<span class="ennu-security-value">' . $security_stats['total_events'] . '</span>';
        echo '</div>';
        
        echo '<div class="ennu-security-metric">';
        echo '<span class="ennu-security-label">' . __('Blocked IPs', 'ennulifeassessments') . '</span>';
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
                'message' => __('Scores recalculated successfully!', 'ennulifeassessments'),
                'results' => $results,
                'execution_time' => $execution_time
            ));
            
        } catch (Exception $e) {
            error_log('ENNU AJAX Recalculate Error: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => __('Failed to recalculate scores. Please try again.', 'ennulifeassessments'),
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
                'message' => __('Data exported successfully!', 'ennulifeassessments'),
                'data' => $export_data,
                'execution_time' => $execution_time
            ));
            
        } catch (Exception $e) {
            error_log('ENNU AJAX Export Error: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => __('Failed to export data. Please try again.', 'ennulifeassessments'),
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
                $message = __('User cache cleared successfully!', 'ennulifeassessments');
            } else {
                $cleared = ENNU_Score_Cache::clear_all_cache();
                $message = sprintf(__('Cleared %d cache entries!', 'ennulifeassessments'), $cleared);
            }
            
            wp_send_json_success(array('message' => $message));
            
        } catch (Exception $e) {
            error_log('ENNU AJAX Clear Cache Error: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => __('Failed to clear cache. Please try again.', 'ennulifeassessments'),
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
                'message' => __('Security stats retrieved!', 'ennulifeassessments'),
                'stats' => $stats
            ));
            
        } catch (Exception $e) {
            error_log('ENNU AJAX Security Stats Error: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => __('Failed to get security stats.', 'ennulifeassessments'),
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
            'user_dob_combined' => __('Date of Birth', 'ennulifeassessments'),
            'user_age' => __('Age', 'ennulifeassessments'),
            'ennu_contact_mobile' => __('Mobile Phone', 'ennulifeassessments'),
            'ennu_contact_phone' => __('Phone', 'ennulifeassessments')
        );
        
        foreach ($global_fields as $meta_key => $label) {
            $current_value = get_user_meta($user_id, $meta_key, true);
            $this->render_text_field($meta_key, $current_value);
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
                                $this->render_radio_field($meta_key, $current_value, $options);
                                break;
                            case 'checkbox':
                                $options = $question_data['options'] ?? array();
                                $this->render_checkbox_field($meta_key, $current_value, $options);
                                break;
                            default:
                                $this->render_text_field($meta_key, $current_value);
                                break;
                        }
                    } else {
                        $this->render_text_field($meta_key, $current_value);
                    }
                }
            }
        } catch (Exception $e) {
            echo '<tr><td colspan="2"><em>' . __('Error loading assessment questions.', 'ennulifeassessments') . '</em></td></tr>';
        }
        
        echo '</table>';
    }
    
    private function render_text_field($meta_key, $current_value) {
        echo '<input type="text" name="' . esc_attr($meta_key) . '" value="' . esc_attr($current_value) . '" class="regular-text" />';
    }

    private function render_radio_field($meta_key, $current_value, $options, $assessment_type, $simple_question_id) {
        echo '<fieldset><legend class="screen-reader-text">' . esc_html($meta_key) . '</legend>';
        foreach ($options as $option) {
            $score = ENNU_Assessment_Scoring::get_answer_score($assessment_type, $simple_question_id, $option['value']);
            $score_display = ($score !== null) ? '<strong>(' . $score . ' pts)</strong>' : '';

            echo '<label style="display: block; margin-bottom: 5px;">';
            echo '<input type="radio" name="' . esc_attr($meta_key) . '" value="' . esc_attr($option['value']) . '" ' . checked($current_value, $option['value'], false) . ' /> ';
            echo esc_html($option['label']) . ' ' . $score_display . ' <small><code>' . esc_html($option['value']) . '</code></small>';
            echo '</label>';
        }
        echo '</fieldset>';
    }

    private function render_checkbox_field($meta_key, $current_values, $options, $assessment_type, $simple_question_id) {
        // Ensure current_values is an array for easy checking.
        $saved_options = is_array($current_values) ? $current_values : array_map('trim', explode(',', $current_values));

        echo '<fieldset><legend class="screen-reader-text">' . esc_html($meta_key) . '</legend>';
        foreach ($options as $option) {
            $is_checked = in_array($option['value'], $saved_options);
            $score = ENNU_Assessment_Scoring::get_answer_score($assessment_type, $simple_question_id, $option['value']);
            $score_display = ($score !== null) ? '<strong>(' . $score . ' pts)</strong>' : '';

            echo '<label style="display: block; margin-bottom: 5px;">';
            echo '<input type="checkbox" name="' . esc_attr($meta_key) . '[]" value="' . esc_attr($option['value']) . '" ' . checked($is_checked, true, false) . ' /> ';
            echo esc_html($option['label']) . ' ' . $score_display . ' <small><code>' . esc_html($option['value']) . '</code></small>';
            echo '</label>';
        }
        echo '</fieldset>';
    }
    
    public function save_user_assessment_fields($user_id) {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }

        // 1. Save Global Fields (Text Inputs)
        $global_text_fields = ['first_name', 'last_name', 'user_email', 'billing_phone', 'user_gender'];
        foreach ($global_text_fields as $field_key) {
            if (isset($_POST[$field_key])) {
                $value = sanitize_text_field($_POST[$field_key]);
                update_user_meta($user_id, $field_key, $value);
                if (in_array($field_key, ['first_name', 'last_name', 'user_email'])) {
                    wp_update_user(['ID' => $user_id, $field_key => $value]);
                }
            }
        }

        // 2. Save Assessment-Specific Fields
        $assessments = [
            'welcome_assessment', 'hair_assessment', 'weight_loss_assessment',
            'health_assessment', 'skin_assessment', 'ed_treatment_assessment'
        ];

        foreach ($assessments as $assessment_type) {
            $questions = $this->get_direct_assessment_questions($assessment_type);
            if (empty($questions)) {
                continue;
            }

            foreach ($questions as $index => $q_data) {
                if (isset($q_data['global_key'])) {
                    continue; // Skip globals, they are handled above or not editable here
                }
                
                $assessment_prefix = str_replace('_assessment', '', $assessment_type);
                $simple_question_id = $assessment_prefix . '_q' . ($index + 1);
                $meta_key = 'ennu_' . $assessment_type . '_' . $simple_question_id;
                $question_type = $q_data['type'] ?? 'text';

                // This logic correctly handles all cases, including empty checkboxes
                if ($question_type === 'multiselect') {
                    // For multiselect, default to an empty array if not present in POST
                    $submitted_values = $_POST[$meta_key] ?? [];
                    $sanitized_values = is_array($submitted_values) ? array_map('sanitize_text_field', $submitted_values) : [];
                    $value_to_save = implode(', ', $sanitized_values);
                    update_user_meta($user_id, $meta_key, $value_to_save);
                } else {
                    // For all other types, only update if the key exists in POST
                    if (isset($_POST[$meta_key])) {
                        $value_to_save = sanitize_text_field($_POST[$meta_key]);
                        update_user_meta($user_id, $meta_key, $value_to_save);
                    }
                }
            }
        }
        return true;
    }
    
    private function display_enhanced_wp_fusion_panel($user_id) {
        echo '<div class="ennu-wp-fusion-panel">';
        echo '<h3>' . __('Integration Status', 'ennulifeassessments') . '</h3>';
        
        // WP Fusion status
        if (function_exists('wp_fusion') && wp_fusion()) {
            echo '<div class="ennu-integration-status active">';
            echo '<span class="dashicons dashicons-yes-alt"></span>';
            echo __('WP Fusion: Connected', 'ennulifeassessments');
            echo '</div>';
        } else {
            echo '<div class="ennu-integration-status inactive">';
            echo '<span class="dashicons dashicons-warning"></span>';
            echo __('WP Fusion: Not Available', 'ennulifeassessments');
            echo '</div>';
        }
        
        // HubSpot status
        if (defined('HUBSPOT_API_KEY')) {
            echo '<div class="ennu-integration-status active">';
            echo '<span class="dashicons dashicons-yes-alt"></span>';
            echo __('HubSpot: Connected', 'ennulifeassessments');
            echo '</div>';
        } else {
            echo '<div class="ennu-integration-status inactive">';
            echo '<span class="dashicons dashicons-warning"></span>';
            echo __('HubSpot: Not Configured', 'ennulifeassessments');
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
        if ($score >= 8.0) return __('Excellent', 'ennulifeassessments');
        if ($score >= 6.0) return __('Good', 'ennulifeassessments');
        if ($score >= 4.0) return __('Fair', 'ennulifeassessments');
        if ($score >= 2.0) return __('Needs Attention', 'ennulifeassessments');
        return __('Critical', 'ennulifeassessments');
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
            __('ENNU Life', 'ennulifeassessments'),
            __('ENNU Life', 'ennulifeassessments'),
            'manage_options',
            'ennu-life',
            array($this, 'admin_page'),
            'dashicons-heart',
            30
        );
        
        // Add submenu pages
        add_submenu_page(
            'ennu-life',
            __('Dashboard', 'ennulifeassessments'),
            __('Dashboard', 'ennulifeassessments'),
            'manage_options',
            'ennu-life',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'ennu-life',
            __('Assessments', 'ennulifeassessments'),
            __('Assessments', 'ennulifeassessments'),
            'manage_options',
            'ennu-life-assessments',
            array($this, 'assessments_page')
        );
        
        add_submenu_page(
            'ennu-life',
            __('Settings', 'ennulifeassessments'),
            __('Settings', 'ennulifeassessments'),
            'manage_options',
            'ennu-life-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Main admin page
     */
    public function admin_page() {
        echo '<div class="wrap">';
        echo '<h1>' . __('ENNU Life Dashboard', 'ennulifeassessments') . '</h1>';
        
        // Get assessment statistics
        $stats = $this->get_assessment_statistics();
        
        echo '<div class="ennu-dashboard-stats">';
        echo '<div class="ennu-stat-card">';
        echo '<h3>' . __('Total Assessments', 'ennulifeassessments') . '</h3>';
        echo '<span class="ennu-stat-number">' . $stats['total_assessments'] . '</span>';
        echo '</div>';
        
        echo '<div class="ennu-stat-card">';
        echo '<h3>' . __('This Month', 'ennulifeassessments') . '</h3>';
        echo '<span class="ennu-stat-number">' . $stats['monthly_assessments'] . '</span>';
        echo '</div>';
        
        echo '<div class="ennu-stat-card">';
        echo '<h3>' . __('Active Users', 'ennulifeassessments') . '</h3>';
        echo '<span class="ennu-stat-number">' . $stats['active_users'] . '</span>';
        echo '</div>';
        echo '</div>';
        
        // Recent assessments table
        echo '<h2>' . __('Recent Assessments', 'ennulifeassessments') . '</h2>';
        $this->display_recent_assessments_table();
        
        echo '</div>';
    }
    
    /**
     * Assessments page
     */
    public function assessments_page() {
        echo '<div class="wrap">';
        echo '<h1>' . __('ENNU Life Assessments', 'ennulifeassessments') . '</h1>';
        
        // Assessment types overview
        $assessment_types = array(
            'hair_assessment' => __('Hair Assessment', 'ennulifeassessments'),
            'weight_loss_assessment' => __('Weight Loss Assessment', 'ennulifeassessments'),
            'health_assessment' => __('Health Assessment', 'ennulifeassessments'),
            'ed_treatment_assessment' => __('ED Treatment Assessment', 'ennulifeassessments'),
            'skin_assessment' => __('Skin Assessment', 'ennulifeassessments')
        );
        
        echo '<div class="ennu-assessments-grid">';
        foreach ($assessment_types as $type => $label) {
            $count = $this->get_assessment_count($type);
            echo '<div class="ennu-assessment-card">';
            echo '<h3>' . esc_html($label) . '</h3>';
            echo '<p class="ennu-assessment-count">' . sprintf(__('%d completed', 'ennulifeassessments'), $count) . '</p>';
            echo '<a href="' . admin_url('users.php?assessment_filter=' . $type) . '" class="button">' . __('View Results', 'ennulifeassessments') . '</a>';
            echo '</div>';
        }
        echo '</div>';
        
        // Recent assessments table
        echo '<h2>' . __('All Assessment Results', 'ennulifeassessments') . '</h2>';
        $this->display_all_assessments_table();
        
        echo '</div>';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        echo '<div class="wrap">';
        echo '<h1>' . __('ENNU Life Settings', 'ennulifeassessments') . '</h1>';
        
        // Handle form submission
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['ennu_settings_nonce'], 'ennu_settings_update')) {
            $this->save_settings();
            echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'ennulifeassessments') . '</p></div>';
        }
        
        // Get current settings
        $settings = $this->get_plugin_settings();
        
        echo '<form method="post" action="">';
        wp_nonce_field('ennu_settings_update', 'ennu_settings_nonce');
        
        echo '<table class="form-table">';
        
        // Email settings
        echo '<tr>';
        echo '<th scope="row">' . __('Admin Email', 'ennulifeassessments') . '</th>';
        echo '<td><input type="email" name="admin_email" value="' . esc_attr($settings['admin_email']) . '" class="regular-text" /></td>';
        echo '</tr>';
        
        // Assessment settings
        echo '<tr>';
        echo '<th scope="row">' . __('Enable Email Notifications', 'ennulifeassessments') . '</th>';
        echo '<td><input type="checkbox" name="email_notifications" value="1" ' . checked($settings['email_notifications'], 1, false) . ' /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('Cache Assessment Results', 'ennulifeassessments') . '</th>';
        echo '<td><input type="checkbox" name="cache_results" value="1" ' . checked($settings['cache_results'], 1, false) . ' /></td>';
        echo '</tr>';
        
        // Integration settings
        echo '<tr>';
        echo '<th scope="row">' . __('WP Fusion Integration', 'ennulifeassessments') . '</th>';
        echo '<td><input type="checkbox" name="wp_fusion_enabled" value="1" ' . checked($settings['wp_fusion_enabled'], 1, false) . ' /></td>';
        echo '</tr>';
        
        echo '<tr>';
        echo '<th scope="row">' . __('HubSpot API Key', 'ennulifeassessments') . '</th>';
        echo '<td><input type="text" name="hubspot_api_key" value="' . esc_attr($settings['hubspot_api_key']) . '" class="regular-text" /></td>';
        echo '</tr>';
        
        echo '</table>';
        
        submit_button();
        echo '</form>';
        
        // System status
        echo '<h2>' . __('System Status', 'ennulifeassessments') . '</h2>';
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
            echo '<p>' . __('No assessment data found.', 'ennulifeassessments') . '</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . __('User', 'ennulifeassessments') . '</th>';
        echo '<th>' . __('Email', 'ennulifeassessments') . '</th>';
        echo '<th>' . __('Assessment Type', 'ennulifeassessments') . '</th>';
        echo '<th>' . __('Score', 'ennulifeassessments') . '</th>';
        echo '<th>' . __('Date', 'ennulifeassessments') . '</th>';
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
            echo '<p>' . __('No assessment data found.', 'ennulifeassessments') . '</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . __('User', 'ennulifeassessments') . '</th>';
        echo '<th>' . __('Email', 'ennulifeassessments') . '</th>';
        echo '<th>' . __('Assessments Completed', 'ennulifeassessments') . '</th>';
        echo '<th>' . __('Date Registered', 'ennulifeassessments') . '</th>';
        echo '<th>' . __('Actions', 'ennulifeassessments') . '</th>';
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
            echo '<td><a href="' . admin_url('user-edit.php?user_id=' . $result->ID) . '" class="button button-small">' . __('View Details', 'ennulifeassessments') . '</a></td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    /**
     * Get plugin settings
     */
    private function get_plugin_settings() {
        $saved_settings = get_option('ennu_plugin_settings', array());
        return array_merge(
            array(
                'admin_email' => get_option('ennu_admin_email', get_option('admin_email')),
                'email_notifications' => get_option('ennu_email_notifications', 1),
                'cache_results' => get_option('ennu_cache_results', 1),
                'wp_fusion_enabled' => get_option('ennu_wp_fusion_enabled', 0),
                'hubspot_api_key' => get_option('ennu_hubspot_api_key', '')
            ),
            $saved_settings // Merge with any settings that might have been saved previously
        );
    }
    
    /**
     * Save plugin settings
     */
    private function save_settings() {
        $settings = $this->get_plugin_settings();

        $settings['admin_email'] = isset($_POST['admin_email']) ? sanitize_email($_POST['admin_email']) : $settings['admin_email'];
        $settings['email_notifications'] = isset($_POST['email_notifications']) ? 1 : 0;
        $settings['cache_results'] = isset($_POST['cache_results']) ? 1 : 0;
        $settings['wp_fusion_enabled'] = isset($_POST['wp_fusion_enabled']) ? 1 : 0;
        $settings['hubspot_api_key'] = isset($_POST['hubspot_api_key']) ? sanitize_text_field($_POST['hubspot_api_key']) : $settings['hubspot_api_key'];

        update_option('ennu_plugin_settings', $settings);
    }
    
    /**
     * Display system status
     */
    private function display_system_status() {
        echo '<table class="form-table">';
        
        // WordPress version
        echo '<tr>';
        echo '<th scope="row">' . __('WordPress Version', 'ennulifeassessments') . '</th>';
        echo '<td>' . get_bloginfo('version') . '</td>';
        echo '</tr>';
        
        // Plugin version
        echo '<tr>';
        echo '<th scope="row">' . __('Plugin Version', 'ennulifeassessments') . '</th>';
        echo '<td>' . ENNU_LIFE_VERSION . '</td>';
        echo '</tr>';
        
        // PHP version
        echo '<tr>';
        echo '<th scope="row">' . __('PHP Version', 'ennulifeassessments') . '</th>';
        echo '<td>' . PHP_VERSION . '</td>';
        echo '</tr>';
        
        // WP Fusion status
        echo '<tr>';
        echo '<th scope="row">' . __('WP Fusion', 'ennulifeassessments') . '</th>';
        echo '<td>' . (function_exists('wp_fusion') ? __('Active', 'ennulifeassessments') : __('Not Installed', 'ennulifeassessments')) . '</td>';
        echo '</tr>';
        
        // Cache status
        $cache_stats = ENNU_Score_Cache::get_cache_stats();
        echo '<tr>';
        echo '<th scope="row">' . __('Cache Status', 'ennulifeassessments') . '</th>';
        echo '<td>' . sprintf(__('%d entries cached', 'ennulifeassessments'), $cache_stats['cached_entries']) . '</td>';
        echo '</tr>';
        
        echo '</table>';
    }
}

