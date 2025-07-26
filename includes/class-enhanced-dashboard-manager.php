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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Enhanced_Dashboard_Manager {

	/**
	 * Initialize the Enhanced Dashboard Manager
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_dashboard_assets' ) );
		add_action( 'wp_ajax_ennu_get_profile_completeness', array( $this, 'ajax_get_profile_completeness' ) );
		add_action( 'wp_ajax_ennu_get_dashboard_widgets', array( $this, 'ajax_get_dashboard_widgets' ) );
		add_action( 'wp_ajax_ennu_update_profile_section', array( $this, 'ajax_update_profile_section' ) );
		add_filter( 'ennu_dashboard_widgets', array( $this, 'add_completeness_widgets' ) );
	}

	/**
	 * Enqueue dashboard assets
	 */
	public function enqueue_dashboard_assets() {
		if ( is_page() && has_shortcode( get_post()->post_content, 'ennu_user_dashboard' ) ) {
			wp_enqueue_style( 'ennu-enhanced-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/css/enhanced-dashboard.css', array(), ENNU_LIFE_VERSION );
			wp_enqueue_script( 'ennu-enhanced-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/js/enhanced-dashboard.js', array( 'jquery' ), ENNU_LIFE_VERSION, true );

			wp_localize_script(
				'ennu-enhanced-dashboard',
				'ennuEnhancedDashboard',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'ennu_ajax_nonce' ),
					'userId'  => get_current_user_id(),
				)
			);
		}
	}

	/**
	 * Get profile completeness display HTML
	 *
	 * @param int $user_id User ID
	 * @return string HTML output
	 */
	public function get_profile_completeness_display( $user_id ) {
		if ( ! class_exists( 'ENNU_Profile_Completeness_Tracker' ) ) {
			return '<div class="ennu-error">Profile completeness tracker not available</div>';
		}

		try {
			$completeness_data = ENNU_Profile_Completeness_Tracker::get_completeness_for_display( $user_id );
			$summary = ENNU_Profile_Completeness_Tracker::get_completeness_summary( $user_id );
		} catch ( Exception $e ) {
			error_log( 'ENNU Enhanced Dashboard: Error getting completeness data: ' . $e->getMessage() );
			return '<div class="ennu-error">Error loading profile completeness data: ' . esc_html( $e->getMessage() ) . '</div>';
		}

		$html = '<div class="profile-completeness-widget" style="background: rgba(255,255,255,0.05); border-radius: 16px; padding: 25px; border: 1px solid rgba(255,255,255,0.1);">';
		
		// Header
		$html .= '<div class="completeness-header" style="display: flex; align-items: center; margin-bottom: 25px;">';
		$html .= '<div class="completeness-icon" style="width: 40px; height: 40px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">';
		$html .= '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" style="color: white;">';
		$html .= '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>';
		$html .= '</svg>';
		$html .= '</div>';
		$html .= '<div>';
		$html .= '<h3 style="margin: 0; color: var(--text-primary); font-size: 18px; font-weight: 600;">Profile Completeness</h3>';
		$html .= '<p style="margin: 5px 0 0 0; color: var(--text-secondary); font-size: 14px;">Your health profile completion status</p>';
		$html .= '</div>';
		$html .= '</div>';

		// Overall Progress
		$html .= '<div class="completeness-overview" style="margin-bottom: 25px;">';
		$html .= '<div class="progress-display" style="text-align: center; margin-bottom: 20px;">';
		$html .= '<div class="progress-circle" style="width: 120px; height: 120px; border-radius: 50%; background: conic-gradient(#10b981 0deg ' . ($completeness_data['overall_percentage'] * 3.6) . 'deg, rgba(255,255,255,0.1) ' . ($completeness_data['overall_percentage'] * 3.6) . 'deg 360deg); display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; position: relative;">';
		$html .= '<div class="progress-inner" style="width: 90px; height: 90px; border-radius: 50%; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center;">';
		$html .= '<div class="progress-text">';
		$html .= '<div class="progress-percentage" style="font-size: 24px; font-weight: 700; color: var(--text-primary);">' . esc_html($completeness_data['overall_percentage']) . '%</div>';
		$html .= '<div class="progress-label" style="font-size: 12px; color: var(--text-secondary);">Complete</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';

		// Accuracy Level
		$accuracy_class = 'accuracy-' . $completeness_data['data_accuracy_level'];
		$accuracy_color = $this->get_accuracy_color($completeness_data['data_accuracy_level']);
		$html .= '<div class="accuracy-indicator" style="text-align: center; margin-bottom: 25px;">';
		$html .= '<div class="accuracy-badge" style="display: inline-flex; align-items: center; padding: 8px 16px; background: ' . $accuracy_color . '; border-radius: 20px; font-size: 14px; font-weight: 600; color: white;">';
		$html .= '<span style="margin-right: 8px;">📊</span>';
		$html .= 'Data Accuracy: ' . esc_html(ENNU_Profile_Completeness_Tracker::get_accuracy_level_display_name($completeness_data['data_accuracy_level']));
		$html .= '</div>';
		$html .= '</div>';

		// Section Details
		$html .= '<div class="section-details" style="margin-bottom: 25px;">';
		$html .= '<h4 style="margin: 0 0 15px 0; color: var(--text-primary); font-size: 16px; font-weight: 600;">Section Progress</h4>';
		
		if (!empty($completeness_data['section_details']) && is_array($completeness_data['section_details'])) {
			foreach ($completeness_data['section_details'] as $section_key => $section_data) {
				if (!is_array($section_data)) continue;
				
				$section_name = ENNU_Profile_Completeness_Tracker::get_section_display_name($section_key);
				$percentage = isset($section_data['percentage']) ? $section_data['percentage'] : 0;
				$is_completed = $percentage >= 80;
				
				$html .= '<div class="section-item" style="margin-bottom: 12px; padding: 12px; background: rgba(255,255,255,0.05); border-radius: 8px; border-left: 4px solid ' . ($is_completed ? '#10b981' : '#f59e0b') . ';">';
				$html .= '<div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">';
				$html .= '<span class="section-name" style="font-weight: 600; color: var(--text-primary);">' . esc_html($section_name) . '</span>';
				$html .= '<span class="section-percentage" style="font-size: 14px; font-weight: 600; color: ' . ($is_completed ? '#10b981' : '#f59e0b') . ';">' . esc_html($percentage) . '%</span>';
				$html .= '</div>';
				
				$html .= '<div class="section-progress" style="width: 100%; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden;">';
				$html .= '<div class="section-progress-fill" style="height: 100%; background: ' . ($is_completed ? '#10b981' : '#f59e0b') . '; width: ' . esc_attr($percentage) . '%; transition: width 0.3s ease;"></div>';
				$html .= '</div>';
				
				if (!$is_completed && !empty($section_data['missing_fields']) && is_array($section_data['missing_fields'])) {
					$html .= '<div class="section-missing" style="margin-top: 8px; font-size: 12px; color: var(--text-secondary);">';
					$html .= 'Missing: ' . esc_html(count($section_data['missing_fields'])) . ' field' . (count($section_data['missing_fields']) !== 1 ? 's' : '');
					$html .= '</div>';
				}
				$html .= '</div>';
			}
		} else {
			$html .= '<div class="no-section-data" style="text-align: center; padding: 20px; color: var(--text-secondary);">';
			$html .= '<p>No section data available</p>';
			$html .= '</div>';
		}
		$html .= '</div>';

		// Recommendations
		if (!empty($completeness_data['recommendations']) && is_array($completeness_data['recommendations'])) {
			$html .= '<div class="recommendations-section">';
			$html .= '<h4 style="margin: 0 0 15px 0; color: var(--text-primary); font-size: 16px; font-weight: 600;">Recommended Actions</h4>';
			
			foreach (array_slice($completeness_data['recommendations'], 0, 3) as $index => $recommendation) {
				if (!is_array($recommendation)) continue;
				
				$priority_color = $this->get_priority_color(isset($recommendation['priority']) ? $recommendation['priority'] : 'medium');
				$icon = isset($recommendation['icon']) ? $recommendation['icon'] : '📋';
				$title = isset($recommendation['title']) ? $recommendation['title'] : 'Complete Profile';
				$description = isset($recommendation['description']) ? $recommendation['description'] : 'Add missing information to improve your profile.';
				$estimated_time = isset($recommendation['estimated_time']) ? $recommendation['estimated_time'] : '5 minutes';
				
				$html .= '<div class="recommendation-item" style="margin-bottom: 12px; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 8px; border-left: 4px solid ' . $priority_color . ';">';
				$html .= '<div class="recommendation-header" style="display: flex; align-items: center; margin-bottom: 8px;">';
				$html .= '<span class="recommendation-icon" style="font-size: 18px; margin-right: 10px;">' . esc_html($icon) . '</span>';
				$html .= '<span class="recommendation-title" style="font-weight: 600; color: var(--text-primary);">' . esc_html($title) . '</span>';
				$html .= '<span class="recommendation-time" style="margin-left: auto; font-size: 12px; color: var(--text-secondary);">' . esc_html($estimated_time) . '</span>';
				$html .= '</div>';
				$html .= '<p class="recommendation-description" style="margin: 0; font-size: 14px; color: var(--text-secondary); line-height: 1.4;">' . esc_html($description) . '</p>';
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
	public function get_data_accuracy_indicators( $user_id ) {
		if ( ! class_exists( 'ENNU_Profile_Completeness_Tracker' ) ) {
			return '';
		}

		$completeness_data = ENNU_Profile_Completeness_Tracker::get_completeness_for_display( $user_id );
		$accuracy_level    = $completeness_data['data_accuracy_level'];

		$indicators = array(
			'high'     => array(
				'color'       => '#28a745',
				'icon'        => '🎯',
				'message'     => 'Your data is highly accurate and complete',
				'description' => 'All key health information is available for precise recommendations',
			),
			'medium'   => array(
				'color'       => '#ffc107',
				'icon'        => '📊',
				'message'     => 'Your data accuracy is good',
				'description' => 'Most health information is available, some areas could be improved',
			),
			'moderate' => array(
				'color'       => '#fd7e14',
				'icon'        => '📈',
				'message'     => 'Your data accuracy is moderate',
				'description' => 'Basic health information is available, additional data would improve recommendations',
			),
			'low'      => array(
				'color'       => '#dc3545',
				'icon'        => '📋',
				'message'     => 'Your data accuracy needs improvement',
				'description' => 'Limited health information available, completing your profile will significantly improve recommendations',
			),
		);

		$indicator = $indicators[ $accuracy_level ];

		$html  = '<div class="ennu-accuracy-indicator" style="border-left: 4px solid ' . esc_attr( $indicator['color'] ) . '">';
		$html .= '<div class="ennu-indicator-content">';
		$html .= '<span class="ennu-indicator-icon">' . $indicator['icon'] . '</span>';
		$html .= '<div class="ennu-indicator-text">';
		$html .= '<h4 style="color: ' . esc_attr( $indicator['color'] ) . '">' . esc_html( $indicator['message'] ) . '</h4>';
		$html .= '<p>' . esc_html( $indicator['description'] ) . '</p>';
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
	public function get_improvement_guidance( $user_id ) {
		if ( ! class_exists( 'ENNU_Profile_Completeness_Tracker' ) ) {
			return '';
		}

		$next_action = ENNU_Profile_Completeness_Tracker::get_next_recommended_action( $user_id );

		if ( ! $next_action ) {
			return '<div class="ennu-improvement-guidance ennu-complete">';
			$html  = '<div class="ennu-guidance-header">';
			$html .= '<span class="ennu-guidance-icon">🎉</span>';
			$html .= '<h4>Profile Complete!</h4>';
			$html .= '</div>';
			$html .= '<p>Your profile is complete and optimized for the best health recommendations.</p>';
			$html .= '</div>';
			return $html;
		}

		$priority_colors = array(
			'critical' => '#dc3545',
			'high'     => '#fd7e14',
			'medium'   => '#ffc107',
			'low'      => '#28a745',
		);

		$priority_icons = array(
			'critical' => '🚨',
			'high'     => '⚡',
			'medium'   => '📋',
			'low'      => '💡',
		);

		$color = $priority_colors[ $next_action['priority'] ];
		$icon  = $priority_icons[ $next_action['priority'] ];

		$html  = '<div class="ennu-improvement-guidance" style="border-left: 4px solid ' . esc_attr( $color ) . '">';
		$html .= '<div class="ennu-guidance-header">';
		$html .= '<span class="ennu-guidance-icon">' . $icon . '</span>';
		$html .= '<h4 style="color: ' . esc_attr( $color ) . '">' . esc_html( $next_action['title'] ) . '</h4>';
		$html .= '<span class="ennu-estimated-time">' . esc_html( $next_action['estimated_time'] ) . '</span>';
		$html .= '</div>';
		$html .= '<p>' . esc_html( $next_action['description'] ) . '</p>';
		if ( ! empty( $next_action['action_url'] ) ) {
			$html .= '<a href="' . esc_url( $next_action['action_url'] ) . '" class="ennu-guidance-button" style="background-color: ' . esc_attr( $color ) . '">Get Started</a>';
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
	private function get_section_display_name( $section_key ) {
		$section_names = array(
			'basic_demographics'    => 'Basic Demographics',
			'health_goals'          => 'Health Goals',
			'assessments_completed' => 'Health Assessments',
			'symptoms_data'         => 'Symptoms Tracking',
			'biomarkers_data'       => 'Lab Results',
			'lifestyle_data'        => 'Lifestyle Information',
		);

		return isset( $section_names[ $section_key ] ) ? $section_names[ $section_key ] : ucwords( str_replace( '_', ' ', $section_key ) );
	}

	/**
	 * AJAX handler for getting profile completeness
	 */
	public function ajax_get_profile_completeness() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( 'User not authenticated' );
			return;
		}

		$completeness_html = $this->get_profile_completeness_display( $user_id );
		$accuracy_html     = $this->get_data_accuracy_indicators( $user_id );
		$guidance_html     = $this->get_improvement_guidance( $user_id );

		wp_send_json_success(
			array(
				'completeness' => $completeness_html,
				'accuracy'     => $accuracy_html,
				'guidance'     => $guidance_html,
			)
		);
	}

	/**
	 * AJAX handler for getting dashboard widgets
	 */
	public function ajax_get_dashboard_widgets() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( 'User not authenticated' );
			return;
		}

		$widgets = array(
			'profile_completeness' => $this->get_profile_completeness_display( $user_id ),
			'data_accuracy'        => $this->get_data_accuracy_indicators( $user_id ),
			'improvement_guidance' => $this->get_improvement_guidance( $user_id ),
		);

		wp_send_json_success( $widgets );
	}

	/**
	 * AJAX handler for updating profile section
	 */
	public function ajax_update_profile_section() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( 'User not authenticated' );
			return;
		}

		$section = sanitize_text_field( $_POST['section'] ?? '' );
		$field   = sanitize_text_field( $_POST['field'] ?? '' );
		$value   = sanitize_text_field( $_POST['value'] ?? '' );

		if ( empty( $section ) || empty( $field ) ) {
			wp_send_json_error( 'Invalid section or field' );
			return;
		}

		update_user_meta( $user_id, $field, $value );

		if ( class_exists( 'ENNU_Profile_Completeness_Tracker' ) ) {
			$completeness_data = ENNU_Profile_Completeness_Tracker::calculate_completeness( $user_id );
			wp_send_json_success(
				array(
					'message'      => 'Profile updated successfully',
					'completeness' => $completeness_data,
				)
			);
		} else {
			wp_send_json_success(
				array(
					'message' => 'Profile updated successfully',
				)
			);
		}
	}

	/**
	 * Add completeness widgets to dashboard
	 *
	 * @param array $widgets Existing widgets
	 * @return array Updated widgets
	 */
	public function add_completeness_widgets( $widgets ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return $widgets;
		}

		$widgets['profile_completeness'] = array(
			'title'    => 'Profile Completeness',
			'content'  => $this->get_profile_completeness_display( $user_id ),
			'priority' => 10,
		);

		$widgets['data_accuracy'] = array(
			'title'    => 'Data Accuracy',
			'content'  => $this->get_data_accuracy_indicators( $user_id ),
			'priority' => 20,
		);

		$widgets['improvement_guidance'] = array(
			'title'    => 'Next Steps',
			'content'  => $this->get_improvement_guidance( $user_id ),
			'priority' => 30,
		);

		return $widgets;
	}

	/**
	 * Get accuracy level color
	 *
	 * @param string $accuracy_level Accuracy level
	 * @return string Color code
	 */
	private function get_accuracy_color($accuracy_level) {
		$colors = array(
			'excellent' => '#10b981',
			'high'      => '#3b82f6',
			'medium'    => '#f59e0b',
			'moderate'  => '#f97316',
			'low'       => '#ef4444',
		);
		
		return isset($colors[$accuracy_level]) ? $colors[$accuracy_level] : '#6b7280';
	}

	/**
	 * Get priority color
	 *
	 * @param string $priority Priority level
	 * @return string Color code
	 */
	private function get_priority_color($priority) {
		$colors = array(
			'critical' => '#ef4444',
			'high'     => '#f59e0b',
			'medium'   => '#3b82f6',
			'low'      => '#6b7280',
		);
		
		return isset($colors[$priority]) ? $colors[$priority] : '#6b7280';
	}
}
