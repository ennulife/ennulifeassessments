<?php
/**
 * ENNU Life Assessments - Onboarding System
 *
 * Provides guided tours, progressive disclosure, and contextual help
 * to ensure users understand and can effectively use the platform.
 *
 * @package   ENNU Life Assessments
 * @copyright Copyright (c) 2024, Very Good Plugins, https://verygoodplugins.com
 * @license   GPL-3.0+
 * @since     3.37.14
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ENNU Onboarding System
 *
 * Manages user onboarding, guided tours, and progressive disclosure
 * to ensure optimal user experience and platform adoption.
 *
 * @since 3.37.14
 */
class ENNU_Onboarding_System {

	/**
	 * Onboarding steps configuration
	 *
	 * @var array
	 */
	private $onboarding_steps = array();

	/**
	 * User onboarding progress
	 *
	 * @var array
	 */
	private $user_progress = array();

	/**
	 * Initialize the onboarding system
	 *
	 * @since 3.37.14
	 */
	public function __construct() {
		$this->init_hooks();
		$this->setup_onboarding_steps();
	}

	/**
	 * Initialize WordPress hooks
	 *
	 * @since 3.37.14
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_onboarding_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_onboarding_assets' ) );
		add_action( 'wp_ajax_ennu_save_onboarding_progress', array( $this, 'save_onboarding_progress' ) );
		add_action( 'wp_ajax_nopriv_ennu_save_onboarding_progress', array( $this, 'save_onboarding_progress' ) );
		add_action( 'wp_ajax_ennu_complete_onboarding_step', array( $this, 'complete_onboarding_step' ) );
		add_action( 'wp_ajax_nopriv_ennu_complete_onboarding_step', array( $this, 'complete_onboarding_step' ) );
		add_action( 'wp_footer', array( $this, 'render_onboarding_overlay' ) );
		add_action( 'admin_footer', array( $this, 'render_onboarding_overlay' ) );
	}

	/**
	 * Setup onboarding steps configuration
	 *
	 * @since 3.37.14
	 */
	private function setup_onboarding_steps() {
		$this->onboarding_steps = array(
			'welcome' => array(
				'title'       => __( 'Welcome to ENNU Life Assessments', 'ennulifeassessments' ),
				'description' => __( 'Your personalized health journey starts here. Let\'s get you oriented with your Biometric Canvas.', 'ennulifeassessments' ),
				'icon'        => '🎯',
				'duration'    => 3000,
				'position'    => 'center',
				'actions'     => array(
					'primary'   => __( 'Start Tour', 'ennulifeassessments' ),
					'secondary' => __( 'Skip Tour', 'ennulifeassessments' ),
				),
			),
			'dashboard_overview' => array(
				'title'       => __( 'Your Biometric Canvas', 'ennulifeassessments' ),
				'description' => __( 'This is your central hub where all your health data comes together. Think of it as your personal health command center.', 'ennulifeassessments' ),
				'icon'        => '📊',
				'duration'    => 4000,
				'position'    => 'top',
				'target'      => '.ennu-user-dashboard',
				'actions'     => array(
					'primary'   => __( 'Next', 'ennulifeassessments' ),
					'secondary' => __( 'Skip', 'ennulifeassessments' ),
				),
			),
			'assessments_section' => array(
				'title'       => __( 'Health Assessments', 'ennulifeassessments' ),
				'description' => __( 'Complete comprehensive health assessments to get personalized insights and recommendations.', 'ennulifeassessments' ),
				'icon'        => '🔬',
				'duration'    => 4000,
				'position'    => 'left',
				'target'      => '.assessments-section',
				'actions'     => array(
					'primary'   => __( 'Next', 'ennulifeassessments' ),
					'secondary' => __( 'Skip', 'ennulifeassessments' ),
				),
			),
			'biomarkers_section' => array(
				'title'       => __( 'Biomarker Tracking', 'ennulifeassessments' ),
				'description' => __( 'Monitor your key health markers over time to track progress and identify trends.', 'ennulifeassessments' ),
				'icon'        => '📈',
				'duration'    => 4000,
				'position'    => 'right',
				'target'      => '.biomarkers-section',
				'actions'     => array(
					'primary'   => __( 'Next', 'ennulifeassessments' ),
					'secondary' => __( 'Skip', 'ennulifeassessments' ),
				),
			),
			'progress_tracking' => array(
				'title'       => __( 'Progress Tracking', 'ennulifeassessments' ),
				'description' => __( 'Visualize your health journey with interactive charts and progress indicators.', 'ennulifeassessments' ),
				'icon'        => '📊',
				'duration'    => 4000,
				'position'    => 'bottom',
				'target'      => '.progress-tracking-section',
				'actions'     => array(
					'primary'   => __( 'Next', 'ennulifeassessments' ),
					'secondary' => __( 'Skip', 'ennulifeassessments' ),
				),
			),
			'next_steps' => array(
				'title'       => __( 'Actionable Next Steps', 'ennulifeassessments' ),
				'description' => __( 'Get personalized recommendations and actionable steps to improve your health.', 'ennulifeassessments' ),
				'icon'        => '🎯',
				'duration'    => 4000,
				'position'    => 'center',
				'target'      => '.next-steps-section',
				'actions'     => array(
					'primary'   => __( 'Next', 'ennulifeassessments' ),
					'secondary' => __( 'Skip', 'ennulifeassessments' ),
				),
			),
			'completion' => array(
				'title'       => __( 'You\'re All Set!', 'ennulifeassessments' ),
				'description' => __( 'You now have a complete understanding of your Biometric Canvas. Start exploring and tracking your health journey!', 'ennulifeassessments' ),
				'icon'        => '🎉',
				'duration'    => 5000,
				'position'    => 'center',
				'actions'     => array(
					'primary'   => __( 'Start Exploring', 'ennulifeassessments' ),
					'secondary' => __( 'View Tutorials', 'ennulifeassessments' ),
				),
			),
		);
	}

	/**
	 * Enqueue onboarding assets
	 *
	 * @since 3.37.14
	 */
	public function enqueue_onboarding_assets() {
		// Only load for logged-in users on relevant pages
		if ( ! is_user_logged_in() || ! $this->should_show_onboarding() ) {
			return;
		}

		wp_enqueue_script(
			'ennu-onboarding',
			plugins_url( 'assets/js/onboarding.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			ENNU_LIFE_VERSION,
			true
		);

		wp_enqueue_style(
			'ennu-onboarding',
			plugins_url( 'assets/css/onboarding.css', dirname( __FILE__ ) ),
			array(),
			ENNU_LIFE_VERSION
		);

		// Localize script with onboarding data
		wp_localize_script(
			'ennu-onboarding',
			'ennuOnboarding',
			array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'ennu_onboarding_nonce' ),
				'steps'      => $this->onboarding_steps,
				'userProgress' => $this->get_user_progress(),
				'strings'    => array(
					'next'      => __( 'Next', 'ennulifeassessments' ),
					'previous'  => __( 'Previous', 'ennulifeassessments' ),
					'skip'      => __( 'Skip', 'ennulifeassessments' ),
					'complete'  => __( 'Complete', 'ennulifeassessments' ),
					'close'     => __( 'Close', 'ennulifeassessments' ),
				),
			)
		);
	}

	/**
	 * Check if onboarding should be shown
	 *
	 * @since 3.37.14
	 * @return bool
	 */
	private function should_show_onboarding() {
		// Check if user has completed onboarding
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}

		$onboarding_completed = get_user_meta( $user_id, '_ennu_onboarding_completed', true );
		if ( $onboarding_completed ) {
			return false;
		}

		// Check if we're on a relevant page
		$current_page = get_post_type();
		$relevant_pages = array( 'page', 'post' );
		
		// Also check for specific shortcodes
		global $post;
		if ( $post && has_shortcode( $post->post_content, 'ennu_user_dashboard' ) ) {
			return true;
		}

		return in_array( $current_page, $relevant_pages, true );
	}

	/**
	 * Get user onboarding progress
	 *
	 * @since 3.37.14
	 * @return array
	 */
	private function get_user_progress() {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return array();
		}

		$progress = get_user_meta( $user_id, '_ennu_onboarding_progress', true );
		return is_array( $progress ) ? $progress : array();
	}

	/**
	 * Save onboarding progress via AJAX
	 *
	 * @since 3.37.14
	 */
	public function save_onboarding_progress() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_onboarding_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed', 'ennulifeassessments' ) );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( __( 'User not logged in', 'ennulifeassessments' ) );
		}

		$progress = sanitize_text_field( wp_unslash( $_POST['progress'] ) );
		update_user_meta( $user_id, '_ennu_onboarding_progress', $progress );

		wp_send_json_success( __( 'Progress saved', 'ennulifeassessments' ) );
	}

	/**
	 * Complete onboarding step via AJAX
	 *
	 * @since 3.37.14
	 */
	public function complete_onboarding_step() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_onboarding_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed', 'ennulifeassessments' ) );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( __( 'User not logged in', 'ennulifeassessments' ) );
		}

		$step = sanitize_text_field( wp_unslash( $_POST['step'] ) );
		$completed = sanitize_text_field( wp_unslash( $_POST['completed'] ) );

		$progress = $this->get_user_progress();
		$progress[ $step ] = $completed;
		update_user_meta( $user_id, '_ennu_onboarding_progress', $progress );

		// Check if all steps are completed
		$all_steps = array_keys( $this->onboarding_steps );
		$completed_steps = array_filter( $progress );
		
		if ( count( $completed_steps ) === count( $all_steps ) ) {
			update_user_meta( $user_id, '_ennu_onboarding_completed', current_time( 'mysql' ) );
		}

		wp_send_json_success( __( 'Step completed', 'ennulifeassessments' ) );
	}

	/**
	 * Render onboarding overlay
	 *
	 * @since 3.37.14
	 */
	public function render_onboarding_overlay() {
		if ( ! $this->should_show_onboarding() ) {
			return;
		}

		?>
		<div id="ennu-onboarding-overlay" class="ennu-onboarding-overlay" style="display: none;">
			<div class="ennu-onboarding-backdrop"></div>
			<div class="ennu-onboarding-tooltip" id="ennu-onboarding-tooltip">
				<div class="ennu-onboarding-header">
					<div class="ennu-onboarding-icon" id="ennu-onboarding-icon"></div>
					<h3 class="ennu-onboarding-title" id="ennu-onboarding-title"></h3>
					<button class="ennu-onboarding-close" id="ennu-onboarding-close" aria-label="<?php esc_attr_e( 'Close tour', 'ennulifeassessments' ); ?>">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<line x1="18" y1="6" x2="6" y2="18"></line>
							<line x1="6" y1="6" x2="18" y2="18"></line>
						</svg>
					</button>
				</div>
				<div class="ennu-onboarding-content">
					<p class="ennu-onboarding-description" id="ennu-onboarding-description"></p>
				</div>
				<div class="ennu-onboarding-footer">
					<div class="ennu-onboarding-progress">
						<span class="ennu-onboarding-step-counter" id="ennu-onboarding-step-counter"></span>
					</div>
					<div class="ennu-onboarding-actions">
						<button class="ennu-onboarding-btn ennu-onboarding-btn-secondary" id="ennu-onboarding-secondary">
							<?php esc_html_e( 'Skip', 'ennulifeassessments' ); ?>
						</button>
						<button class="ennu-onboarding-btn ennu-onboarding-btn-primary" id="ennu-onboarding-primary">
							<?php esc_html_e( 'Next', 'ennulifeassessments' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render onboarding trigger button
	 *
	 * @since 3.37.14
	 * @return string
	 */
	public function render_onboarding_trigger() {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$user_id = get_current_user_id();
		$onboarding_completed = get_user_meta( $user_id, '_ennu_onboarding_completed', true );

		if ( $onboarding_completed ) {
			return '';
		}

		ob_start();
		?>
		<div class="ennu-onboarding-trigger">
			<button class="ennu-onboarding-trigger-btn" id="ennu-onboarding-trigger" aria-label="<?php esc_attr_e( 'Start platform tour', 'ennulifeassessments' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
					<circle cx="12" cy="12" r="10"></circle>
					<path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
					<line x1="12" y1="17" x2="12.01" y2="17"></line>
				</svg>
				<?php esc_html_e( 'Take Tour', 'ennulifeassessments' ); ?>
			</button>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get onboarding statistics
	 *
	 * @since 3.37.14
	 * @return array
	 */
	public function get_onboarding_stats() {
		global $wpdb;

		$stats = array(
			'total_users'        => 0,
			'completed_onboarding' => 0,
			'completion_rate'    => 0,
			'average_completion_time' => 0,
		);

		// Get total users
		$stats['total_users'] = count_users()['total_users'];

		// Get users who completed onboarding
		$completed_users = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value != ''",
				'_ennu_onboarding_completed'
			)
		);
		$stats['completed_onboarding'] = (int) $completed_users;

		// Calculate completion rate
		if ( $stats['total_users'] > 0 ) {
			$stats['completion_rate'] = round( ( $stats['completed_onboarding'] / $stats['total_users'] ) * 100, 2 );
		}

		return $stats;
	}

	/**
	 * Reset user onboarding progress
	 *
	 * @since 3.37.14
	 * @param int $user_id User ID.
	 */
	public function reset_user_onboarding( $user_id ) {
		delete_user_meta( $user_id, '_ennu_onboarding_completed' );
		delete_user_meta( $user_id, '_ennu_onboarding_progress' );
	}

	/**
	 * Check if user has completed onboarding
	 *
	 * @since 3.37.14
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public function has_completed_onboarding( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return false;
		}

		$completed = get_user_meta( $user_id, '_ennu_onboarding_completed', true );
		return ! empty( $completed );
	}
}

// Initialize the onboarding system
if ( class_exists( 'ENNU_Onboarding_System' ) ) {
	new ENNU_Onboarding_System();
} 