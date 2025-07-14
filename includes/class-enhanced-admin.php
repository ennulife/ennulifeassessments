<?php
/**
 * ENNU Life Enhanced Admin Class - Definitive Rebuild
 *
 * This file has been programmatically rebuilt from scratch to resolve all 
 * fatal errors and restore all necessary functionality. It is the single,
 * correct source of truth for the admin class.
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Enhanced_Admin {
    
    public function __construct() {
        // Hooks are managed from the main plugin file.
    }

    /**
     * The single, correct function to build the entire admin menu.
     */
    public function add_admin_menu() {
        add_menu_page(
            __('ENNU Life', 'ennulifeassessments'),
            __('ENNU Life', 'ennulifeassessments'),
            'manage_options',
            'ennu-life',
            array($this, 'render_admin_dashboard_page'),
            'dashicons-heart',
            30
        );
        
        add_submenu_page(
            'ennu-life',
            __('Dashboard', 'ennulifeassessments'),
            __('Dashboard', 'ennulifeassessments'),
            'manage_options',
            'ennu-life',
            array($this, 'render_admin_dashboard_page')
        );

        add_submenu_page(
            'ennu-life',
            __( 'Analytics', 'ennulifeassessments' ),
            __( 'Analytics', 'ennulifeassessments' ),
            'manage_options',
            'ennu-life-analytics',
            array( $this, 'render_analytics_dashboard_page' )
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
    
    public function render_admin_dashboard_page() {
        echo '<div class="wrap"><h1>' . __('ENNU Life Dashboard', 'ennulifeassessments') . '</h1>';
        $stats = $this->get_assessment_statistics();
        echo '<div class="ennu-dashboard-stats" style="display:flex;gap:20px;margin:20px 0;">';
        echo '<div class="ennu-stat-card" style="flex:1;background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);"><h3>' . __('Total Users with Assessments', 'ennulifeassessments') . '</h3><span class="ennu-stat-number" style="font-size:2em;font-weight:bold;">' . $stats['active_users'] . '</span></div>';
        echo '<div class="ennu-stat-card" style="flex:1;background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 5px rgba(0,0,0,0.1);"><h3>' . __('Submissions This Month', 'ennulifeassessments') . '</h3><span class="ennu-stat-number" style="font-size:2em;font-weight:bold;">' . $stats['monthly_assessments'] . '</span></div>';
        echo '</div>';
        echo '<h2>' . __('Recent Assessments', 'ennulifeassessments') . '</h2>';
        $this->display_recent_assessments_table();
        echo '</div>';
    }
    
    public function render_analytics_dashboard_page() {
        if ( ! is_user_logged_in() ) { return; }
        $user_id = get_current_user_id();
        $current_user = wp_get_current_user();
        $ennu_life_score = get_user_meta($user_id, 'ennu_life_score', true);
        $average_pillar_scores = ENNU_Assessment_Scoring::calculate_average_pillar_scores($user_id);
        $dob_combined = get_user_meta($user_id, 'ennu_global_user_dob_combined', true);
        $age = $dob_combined ? (new DateTime())->diff(new DateTime($dob_combined))->y : 'N/A';
        $gender = ucfirst(get_user_meta($user_id, 'ennu_global_gender', true));
        include ENNU_LIFE_PLUGIN_PATH . 'templates/admin/analytics-dashboard.php';
    }
    
    public function assessments_page() {
        echo '<div class="wrap"><h1>' . __('ENNU Life Assessments', 'ennulifeassessments') . '</h1>';
        $this->display_all_assessments_table();
        echo '</div>';
    }
    
    public function settings_page() {
        echo '<div class="wrap"><h1>' . __('ENNU Life Settings', 'ennulifeassessments') . '</h1>';
        if (isset($_POST['submit']) && isset($_POST['ennu_settings_nonce']) && wp_verify_nonce($_POST['ennu_settings_nonce'], 'ennu_settings_update')) {
            $this->save_settings();
            echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'ennulifeassessments') . '</p></div>';
        }
        $settings = $this->get_plugin_settings();
        echo '<form method="post" action=""><table class="form-table">';
        wp_nonce_field('ennu_settings_update', 'ennu_settings_nonce');
        echo '<tr><th>' . __('Admin Email Notifications', 'ennulifeassessments') . '</th><td><input type="email" name="admin_email" value="' . esc_attr($settings['admin_email']) . '" class="regular-text" /></td></tr>';
        submit_button();
        echo '</table></form>';
    }
    
    public function show_user_assessment_fields($user) {
        if (!current_user_can('edit_user', $user->ID)) { return; }

        // --- Render Health Summary Component ---
        $ennu_life_score = get_user_meta($user->ID, 'ennu_life_score', true);
        $average_pillar_scores = ENNU_Assessment_Scoring::calculate_average_pillar_scores($user->ID);
        
        include ENNU_LIFE_PLUGIN_PATH . 'templates/admin/user-health-summary.php';
        // --- END Health Summary Component ---

        wp_nonce_field('ennu_user_profile_update_' . $user->ID, 'ennu_assessment_nonce');
        
        $this->display_global_fields_section($user->ID);
        $assessments = array_keys(ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions());
        echo '<h2>' . esc_html__('Assessment Answers', 'ennulifeassessments') . '</h2><div class="ennu-admin-tabs">';
        echo '<nav class="ennu-admin-tab-nav"><ul>';
        foreach ($assessments as $key) {
            if ($key === 'welcome_assessment') continue;
            $label = ucwords(str_replace(['_', 'assessment'], ' ', $key));
            echo '<li><a href="#tab-' . esc_attr($key) . '">' . esc_html($label) . '</a></li>';
        }
        echo '</ul></nav>';
        foreach ($assessments as $assessment_key) {
            if ($assessment_key === 'welcome_assessment') continue;
            echo '<div id="tab-' . esc_attr($assessment_key) . '" class="ennu-admin-tab-content">';
        echo '<table class="form-table">';
            $this->display_assessment_fields_editable($user->ID, $assessment_key);
            echo '</table></div>';
        }
        echo '</div>';
    }

    public function enqueue_admin_assets($hook) {
        if ($hook !== 'profile.php' && $hook !== 'user-edit.php') {
                        return;
        }
        wp_enqueue_style('ennu-admin-styles', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-scores-enhanced.css', array(), ENNU_LIFE_VERSION);
        wp_enqueue_script('ennu-admin-scripts', ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-admin.js', array('jquery'), ENNU_LIFE_VERSION, true);
        wp_localize_script('ennu-admin-scripts', 'ennuAdmin', ['nonce' => wp_create_nonce('ennu_admin_nonce')]);
    }
    
    public function save_user_assessment_fields($user_id) {
        if (!current_user_can('edit_user', $user_id) || !isset($_POST['ennu_assessment_nonce']) || !wp_verify_nonce($_POST['ennu_assessment_nonce'], 'ennu_user_profile_update_' . $user_id)) {
            return;
        }
        $assessments = array_keys(ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions());
        foreach ($assessments as $assessment_type) {
            $questions = $this->get_direct_assessment_questions($assessment_type);
            foreach ($questions as $question_id => $q_data) {
                if (isset($q_data['global_key'])) continue;
                $meta_key = 'ennu_' . $assessment_type . '_' . $question_id;
                if (isset($_POST[$meta_key])) {
                    $value_to_save = is_array($_POST[$meta_key]) ? array_map('sanitize_text_field', $_POST[$meta_key]) : sanitize_text_field($_POST[$meta_key]);
                    update_user_meta($user_id, $meta_key, $value_to_save);
                } elseif ($q_data['type'] === 'multiselect') {
                    update_user_meta($user_id, $meta_key, []);
                }
            }
        }
    }
    
    // --- PRIVATE HELPER METHODS ---

    private function get_assessment_statistics() {
        global $wpdb;
        $stats = ['total_assessments' => 0, 'monthly_assessments' => 0, 'active_users' => 0];
        $assessment_types = array_keys(ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_all_assessment_definitions());
        $meta_keys = [];
        foreach ($assessment_types as $type) {
            if ($type === 'welcome_assessment') continue;
            $meta_keys[] = 'ennu_' . $type . '_calculated_score';
        }
        if (!empty($meta_keys)) {
            $placeholders = implode(', ', array_fill(0, count($meta_keys), '%s'));
            $stats['active_users'] = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key IN ($placeholders) AND meta_value != ''", $meta_keys));
        }
        $stats['monthly_assessments'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key LIKE 'ennu_%_calculated_score' AND meta_value != '' AND CAST(meta_value AS SIGNED) > 0 AND user_id IN (SELECT ID FROM {$wpdb->users} WHERE user_registered >= DATE_SUB(NOW(), INTERVAL 1 MONTH))");
        return $stats;
    }
    
    private function display_recent_assessments_table() {
        global $wpdb;
        $results = $wpdb->get_results("SELECT u.ID, u.user_login, u.user_email, um.meta_value as score, REPLACE(REPLACE(um.meta_key, 'ennu_', ''), '_calculated_score', '') as assessment_type FROM {$wpdb->usermeta} um JOIN {$wpdb->users} u ON um.user_id = u.ID WHERE um.meta_key LIKE 'ennu_%_calculated_score' AND um.meta_value != '' ORDER BY um.umeta_id DESC LIMIT 10");
        if (empty($results)) { echo '<p>' . __('No recent assessment submissions found.', 'ennulifeassessments') . '</p>'; return; }
        echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>User</th><th>Email</th><th>Assessment Type</th><th>Score</th></tr></thead><tbody>';
        foreach ($results as $result) {
            echo '<tr>';
            echo '<td><a href="' . esc_url(get_edit_user_link($result->ID)) . '">' . esc_html($result->user_login) . '</a></td>';
            echo '<td>' . esc_html($result->user_email) . '</td>';
            echo '<td>' . esc_html(ucwords(str_replace('_', ' ', $result->assessment_type))) . '</td>';
            echo '<td>' . number_format(floatval($result->score), 1) . '/10</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
    
    private function get_assessment_count($assessment_type) {
        global $wpdb;
        return (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value != ''", 'ennu_' . $assessment_type . '_calculated_score'));
    }
    
    private function display_all_assessments_table() {
        global $wpdb;
        $user_ids = $wpdb->get_col("SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key LIKE 'ennu_%_calculated_score' ORDER BY user_id DESC LIMIT 50");
        if (empty($user_ids)) { echo '<p>' . __('No assessment data found.', 'ennulifeassessments') . '</p>'; return; }
        echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>User</th><th>Email</th><th>Assessments Completed</th><th>Date Registered</th></tr></thead><tbody>';
        foreach ($user_ids as $user_id) {
            $user = get_userdata($user_id);
            if (!$user) continue;
            $assessments = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key LIKE %s", $user_id, '%_calculated_score'));
            $assessment_list = [];
            if (!empty($assessments)) {
                foreach ($assessments as $assessment) {
                    $type = str_replace(['ennu_', '_calculated_score'], '', $assessment->meta_key);
                    $label = ucwords(str_replace('_', ' ', $type));
                    $assessment_list[] = $label . ' (' . number_format(floatval($assessment->meta_value), 1) . '/10)';
                }
            }
            echo '<tr><td><a href="' . esc_url(get_edit_user_link($user_id)) . '">' . esc_html($user->user_login) . '</a></td><td>' . esc_html($user->user_email) . '</td><td>' . implode('<br>', $assessment_list) . '</td><td>' . date('M j, Y', strtotime($user->user_registered)) . '</td></tr>';
        }
        echo '</tbody></table>';
    }
    
    private function get_plugin_settings() {
        return get_option('ennu_plugin_settings', ['admin_email' => get_option('admin_email'), 'email_notifications' => 1]);
    }
    
    private function save_settings() {
        $settings['admin_email'] = isset($_POST['admin_email']) ? sanitize_email($_POST['admin_email']) : '';
        update_option('ennu_plugin_settings', $settings);
    }
    
    private function display_system_status() {
        echo '<table class="form-table"><tbody>';
        echo '<tr><th>' . __('WordPress Version', 'ennulifeassessments') . '</th><td>' . get_bloginfo('version') . '</td></tr>';
        echo '<tr><th>' . __('Plugin Version', 'ennulifeassessments') . '</th><td>' . ENNU_LIFE_VERSION . '</td></tr>';
        echo '<tr><th>' . __('PHP Version', 'ennulifeassessments') . '</th><td>' . PHP_VERSION . '</td></tr>';
        echo '</tbody></table>';
    }
    
    private function display_global_fields_section( $user_id ) {
        echo '<h3>' . esc_html__( 'Global User Data', 'ennulifeassessments' ) . '</h3><table class="form-table">';
        $global_fields = ['first_name' => 'First Name', 'last_name' => 'Last Name', 'user_email' => 'Email', 'ennu_global_gender' => 'Gender', 'ennu_global_user_dob_combined' => 'DOB'];
        foreach($global_fields as $key => $label) {
            $value = (in_array($key, ['first_name', 'last_name', 'user_email'])) ? get_the_author_meta($key, $user_id) : get_user_meta($user_id, $key, true);
            echo '<tr><th><label>' . esc_html($label) . '</label></th><td>' . esc_html($value) . '</td></tr>';
        }
        echo '</table>';
    }
    
    private function display_assessment_fields_editable($user_id, $assessment_type) {
        $questions = $this->get_direct_assessment_questions($assessment_type);
        if (empty($questions)) return;
        foreach ($questions as $question_id => $q_data) {
            if (isset($q_data['global_key'])) continue;
            $meta_key = 'ennu_' . $assessment_type . '_' . $question_id;
            $current_value = get_user_meta($user_id, $meta_key, true);
            echo '<tr><th><label for="' . esc_attr($meta_key) . '">' . esc_html($q_data['title']) . '</label></th><td>';
            $this->render_field_for_admin($meta_key, $q_data['type'] ?? 'text', $q_data['options'] ?? [], $current_value, $assessment_type, $question_id);
            echo '</td></tr>';
        }
    }

    private function get_direct_assessment_questions($assessment_type) {
        return ENNU_Life_Enhanced_Plugin::get_instance()->get_shortcode_handler()->get_assessment_questions($assessment_type);
    }

    private function render_field_for_admin($meta_key, $type, $options, $current_value, $assessment_type, $question_id) {
        switch ($type) {
            case 'multiselect': $this->render_checkbox_field($meta_key, $current_value, $options, $assessment_type, $question_id); break;
            case 'radio': $this->render_radio_field($meta_key, $current_value, $options, $assessment_type, $question_id); break;
            default: echo '<input type="text" name="' . esc_attr($meta_key) . '" value="' . esc_attr($current_value) . '" class="regular-text" />'; break;
        }
    }

    private function render_radio_field($meta_key, $current_value, $options, $assessment_type, $question_id) {
        foreach ($options as $value => $label) {
            $score = ENNU_Assessment_Scoring::get_answer_score($assessment_type, $question_id, $value);
            $score_display = $score !== null ? ' (' . esc_html($score) . ' pts)' : '';
            echo '<label><input type="radio" name="' . esc_attr($meta_key) . '" value="' . esc_attr($value) . '" ' . checked($current_value, $value, false) . '> ' . esc_html($label) . $score_display . '</label><br>';
        }
    }

    private function render_checkbox_field($meta_key, $current_values, $options, $assessment_type, $question_id) {
        $current_values = is_array($current_values) ? $current_values : [];
        foreach ($options as $value => $label) {
            $score = ENNU_Assessment_Scoring::get_answer_score($assessment_type, $question_id, $value);
            $score_display = $score !== null ? ' (' . esc_html($score) . ' pts)' : '';
            echo '<label><input type="checkbox" name="' . esc_attr($meta_key) . '[]" value="' . esc_attr($value) . '" ' . checked(in_array($value, $current_values), true, false) . '> ' . esc_html($label) . $score_display . '</label><br>';
        }
    }
}