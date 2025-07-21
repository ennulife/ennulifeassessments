<?php
/**
 * ENNU Enhanced Dashboard Manager
 * 
 * Manages enhanced dashboard features including profile completeness display,
 * data accuracy indicators, and improvement guidance integration
 * 
 * @package ENNU_Life_Assessments
 * @version 62.2.9
 */

if (!defined('ABSPATH')) {
    exit;
}

class ENNU_Enhanced_Dashboard_Manager {
    
    /**
     * Initialize the Enhanced Dashboard Manager
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_dashboard_assets'));
        add_action('wp_ajax_ennu_get_profile_completeness', array($this, 'ajax_get_profile_completeness'));
        add_action('wp_ajax_ennu_get_dashboard_widgets', array($this, 'ajax_get_dashboard_widgets'));
        add_action('wp_ajax_ennu_update_profile_section', array($this, 'ajax_update_profile_section'));
        add_filter('ennu_dashboard_widgets', array($this, 'add_completeness_widgets'));
    }
    
    /**
     * Enqueue dashboard assets
     */
    public function enqueue_dashboard_assets() {
        if (is_page() && has_shortcode(get_post()->post_content, 'ennu_user_dashboard')) {
            wp_enqueue_style('ennu-enhanced-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/css/enhanced-dashboard.css', array(), ENNU_LIFE_VERSION);
            wp_enqueue_script('ennu-enhanced-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/js/enhanced-dashboard.js', array('jquery'), ENNU_LIFE_VERSION, true);
            
            wp_localize_script('ennu-enhanced-dashboard', 'ennuEnhancedDashboard', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ennu_ajax_nonce'),
                'userId' => get_current_user_id()
            ));
        }
    }
    
    /**
     * Get profile completeness display HTML
     * 
     * @param int $user_id User ID
     * @return string HTML output
     */
    public function get_profile_completeness_display($user_id) {
        if (!class_exists('ENNU_Profile_Completeness_Tracker')) {
            return '<div class="ennu-error">Profile completeness tracker not available</div>';
        }
        
        $completeness_data = ENNU_Profile_Completeness_Tracker::get_completeness_for_display($user_id);
        
        $html = '<div class="ennu-profile-completeness-widget">';
        $html .= '<div class="ennu-widget-header">';
        $html .= '<h3>Profile Completeness</h3>';
        $html .= '<div class="ennu-completeness-percentage">' . esc_html($completeness_data['overall_percentage']) . '%</div>';
        $html .= '</div>';
        
        $html .= '<div class="ennu-progress-bar">';
        $html .= '<div class="ennu-progress-fill" style="width: ' . esc_attr($completeness_data['overall_percentage']) . '%"></div>';
        $html .= '</div>';
        
        $accuracy_class = 'ennu-accuracy-' . $completeness_data['data_accuracy_level'];
        $accuracy_labels = array(
            'high' => 'High Accuracy',
            'medium' => 'Medium Accuracy',
            'moderate' => 'Moderate Accuracy',
            'low' => 'Low Accuracy'
        );
        
        $html .= '<div class="ennu-data-accuracy ' . $accuracy_class . '">';
        $html .= '<span class="ennu-accuracy-label">Data Accuracy: ' . esc_html($accuracy_labels[$completeness_data['data_accuracy_level']]) . '</span>';
        $html .= '</div>';
        
        if (!empty($completeness_data['completed_sections'])) {
            $html .= '<div class="ennu-completed-sections">';
            $html .= '<h4>Completed Sections</h4>';
            $html .= '<ul>';
            foreach ($completeness_data['completed_sections'] as $section) {
                $section_name = $this->get_section_display_name($section);
                $html .= '<li class="ennu-completed-section"><span class="ennu-checkmark">✓</span> ' . esc_html($section_name) . '</li>';
            }
            $html .= '</ul>';
            $html .= '</div>';
        }
        
        if (!empty($completeness_data['missing_sections'])) {
            $html .= '<div class="ennu-missing-sections">';
            $html .= '<h4>Areas for Improvement</h4>';
            foreach ($completeness_data['missing_sections'] as $missing_section) {
                $section_name = $this->get_section_display_name($missing_section['section']);
                $html .= '<div class="ennu-missing-section">';
                $html .= '<div class="ennu-section-header">';
                $html .= '<span class="ennu-section-name">' . esc_html($section_name) . '</span>';
                $html .= '<span class="ennu-section-percentage">' . esc_html($missing_section['percentage']) . '% complete</span>';
                $html .= '</div>';
                $html .= '<div class="ennu-section-progress">';
                $html .= '<div class="ennu-section-progress-fill" style="width: ' . esc_attr($missing_section['percentage']) . '%"></div>';
                $html .= '</div>';
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        
        if (!empty($completeness_data['recommendations'])) {
            $html .= '<div class="ennu-recommendations">';
            $html .= '<h4>Recommended Actions</h4>';
            foreach ($completeness_data['recommendations'] as $recommendation) {
                $priority_class = 'ennu-priority-' . $recommendation['priority'];
                $html .= '<div class="ennu-recommendation ' . $priority_class . '">';
                $html .= '<div class="ennu-recommendation-header">';
                $html .= '<h5>' . esc_html($recommendation['title']) . '</h5>';
                $html .= '<span class="ennu-estimated-time">' . esc_html($recommendation['estimated_time']) . '</span>';
                $html .= '</div>';
                $html .= '<p>' . esc_html($recommendation['description']) . '</p>';
                if (!empty($recommendation['action_url'])) {
                    $html .= '<a href="' . esc_url($recommendation['action_url']) . '" class="ennu-action-button">Take Action</a>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get data accuracy indicators HTML
     * 
     * @param int $user_id User ID
     * @return string HTML output
     */
    public function get_data_accuracy_indicators($user_id) {
        if (!class_exists('ENNU_Profile_Completeness_Tracker')) {
            return '';
        }
        
        $completeness_data = ENNU_Profile_Completeness_Tracker::get_completeness_for_display($user_id);
        $accuracy_level = $completeness_data['data_accuracy_level'];
        
        $indicators = array(
            'high' => array(
                'color' => '#28a745',
                'icon' => '🎯',
                'message' => 'Your data is highly accurate and complete',
                'description' => 'All key health information is available for precise recommendations'
            ),
            'medium' => array(
                'color' => '#ffc107',
                'icon' => '📊',
                'message' => 'Your data accuracy is good',
                'description' => 'Most health information is available, some areas could be improved'
            ),
            'moderate' => array(
                'color' => '#fd7e14',
                'icon' => '📈',
                'message' => 'Your data accuracy is moderate',
                'description' => 'Basic health information is available, additional data would improve recommendations'
            ),
            'low' => array(
                'color' => '#dc3545',
                'icon' => '📋',
                'message' => 'Your data accuracy needs improvement',
                'description' => 'Limited health information available, completing your profile will significantly improve recommendations'
            )
        );
        
        $indicator = $indicators[$accuracy_level];
        
        $html = '<div class="ennu-accuracy-indicator" style="border-left: 4px solid ' . esc_attr($indicator['color']) . '">';
        $html .= '<div class="ennu-indicator-content">';
        $html .= '<span class="ennu-indicator-icon">' . $indicator['icon'] . '</span>';
        $html .= '<div class="ennu-indicator-text">';
        $html .= '<h4 style="color: ' . esc_attr($indicator['color']) . '">' . esc_html($indicator['message']) . '</h4>';
        $html .= '<p>' . esc_html($indicator['description']) . '</p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get improvement guidance widget HTML
     * 
     * @param int $user_id User ID
     * @return string HTML output
     */
    public function get_improvement_guidance($user_id) {
        if (!class_exists('ENNU_Profile_Completeness_Tracker')) {
            return '';
        }
        
        $next_action = ENNU_Profile_Completeness_Tracker::get_next_recommended_action($user_id);
        
        if (!$next_action) {
            return '<div class="ennu-improvement-guidance ennu-complete">';
            $html = '<div class="ennu-guidance-header">';
            $html .= '<span class="ennu-guidance-icon">🎉</span>';
            $html .= '<h4>Profile Complete!</h4>';
            $html .= '</div>';
            $html .= '<p>Your profile is complete and optimized for the best health recommendations.</p>';
            $html .= '</div>';
            return $html;
        }
        
        $priority_colors = array(
            'critical' => '#dc3545',
            'high' => '#fd7e14',
            'medium' => '#ffc107',
            'low' => '#28a745'
        );
        
        $priority_icons = array(
            'critical' => '🚨',
            'high' => '⚡',
            'medium' => '📋',
            'low' => '💡'
        );
        
        $color = $priority_colors[$next_action['priority']];
        $icon = $priority_icons[$next_action['priority']];
        
        $html = '<div class="ennu-improvement-guidance" style="border-left: 4px solid ' . esc_attr($color) . '">';
        $html .= '<div class="ennu-guidance-header">';
        $html .= '<span class="ennu-guidance-icon">' . $icon . '</span>';
        $html .= '<h4 style="color: ' . esc_attr($color) . '">' . esc_html($next_action['title']) . '</h4>';
        $html .= '<span class="ennu-estimated-time">' . esc_html($next_action['estimated_time']) . '</span>';
        $html .= '</div>';
        $html .= '<p>' . esc_html($next_action['description']) . '</p>';
        if (!empty($next_action['action_url'])) {
            $html .= '<a href="' . esc_url($next_action['action_url']) . '" class="ennu-guidance-button" style="background-color: ' . esc_attr($color) . '">Get Started</a>';
        }
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get section display name
     * 
     * @param string $section_key Section key
     * @return string Display name
     */
    private function get_section_display_name($section_key) {
        $section_names = array(
            'basic_demographics' => 'Basic Demographics',
            'health_goals' => 'Health Goals',
            'assessments_completed' => 'Health Assessments',
            'symptoms_data' => 'Symptoms Tracking',
            'biomarkers_data' => 'Lab Results',
            'lifestyle_data' => 'Lifestyle Information'
        );
        
        return isset($section_names[$section_key]) ? $section_names[$section_key] : ucwords(str_replace('_', ' ', $section_key));
    }
    
    /**
     * AJAX handler for getting profile completeness
     */
    public function ajax_get_profile_completeness() {
        check_ajax_referer('ennu_ajax_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not authenticated');
            return;
        }
        
        $completeness_html = $this->get_profile_completeness_display($user_id);
        $accuracy_html = $this->get_data_accuracy_indicators($user_id);
        $guidance_html = $this->get_improvement_guidance($user_id);
        
        wp_send_json_success(array(
            'completeness' => $completeness_html,
            'accuracy' => $accuracy_html,
            'guidance' => $guidance_html
        ));
    }
    
    /**
     * AJAX handler for getting dashboard widgets
     */
    public function ajax_get_dashboard_widgets() {
        check_ajax_referer('ennu_ajax_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not authenticated');
            return;
        }
        
        $widgets = array(
            'profile_completeness' => $this->get_profile_completeness_display($user_id),
            'data_accuracy' => $this->get_data_accuracy_indicators($user_id),
            'improvement_guidance' => $this->get_improvement_guidance($user_id)
        );
        
        wp_send_json_success($widgets);
    }
    
    /**
     * AJAX handler for updating profile section
     */
    public function ajax_update_profile_section() {
        check_ajax_referer('ennu_ajax_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not authenticated');
            return;
        }
        
        $section = sanitize_text_field($_POST['section'] ?? '');
        $field = sanitize_text_field($_POST['field'] ?? '');
        $value = sanitize_text_field($_POST['value'] ?? '');
        
        if (empty($section) || empty($field)) {
            wp_send_json_error('Invalid section or field');
            return;
        }
        
        update_user_meta($user_id, $field, $value);
        
        if (class_exists('ENNU_Profile_Completeness_Tracker')) {
            $completeness_data = ENNU_Profile_Completeness_Tracker::calculate_completeness($user_id);
            wp_send_json_success(array(
                'message' => 'Profile updated successfully',
                'completeness' => $completeness_data
            ));
        } else {
            wp_send_json_success(array(
                'message' => 'Profile updated successfully'
            ));
        }
    }
    
    /**
     * Add completeness widgets to dashboard
     * 
     * @param array $widgets Existing widgets
     * @return array Updated widgets
     */
    public function add_completeness_widgets($widgets) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return $widgets;
        }
        
        $widgets['profile_completeness'] = array(
            'title' => 'Profile Completeness',
            'content' => $this->get_profile_completeness_display($user_id),
            'priority' => 10
        );
        
        $widgets['data_accuracy'] = array(
            'title' => 'Data Accuracy',
            'content' => $this->get_data_accuracy_indicators($user_id),
            'priority' => 20
        );
        
        $widgets['improvement_guidance'] = array(
            'title' => 'Next Steps',
            'content' => $this->get_improvement_guidance($user_id),
            'priority' => 30
        );
        
        return $widgets;
    }
}
