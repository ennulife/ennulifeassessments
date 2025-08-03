<?php
/**
 * ENNU Life Assessments - Help System
 *
 * Provides contextual help, tooltips, and explanations
 * to improve user understanding and platform discoverability.
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
 * ENNU Help System
 *
 * Manages contextual help, tooltips, and user guidance
 * to ensure optimal user experience and feature discovery.
 *
 * @since 3.37.14
 */
class ENNU_Help_System {

	/**
	 * Help content configuration
	 *
	 * @var array
	 */
	private $help_content = array();

	/**
	 * Tooltip configuration
	 *
	 * @var array
	 */
	private $tooltip_config = array();

	/**
	 * Initialize the help system
	 *
	 * @since 3.37.14
	 */
	public function __construct() {
		$this->init_hooks();
		$this->setup_help_content();
		$this->setup_tooltip_config();
	}

	/**
	 * Initialize WordPress hooks
	 *
	 * @since 3.37.14
	 */
	private function init_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_help_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_help_assets' ) );
		add_action( 'wp_ajax_ennu_get_help_content', array( $this, 'get_help_content_ajax' ) );
		add_action( 'wp_ajax_nopriv_ennu_get_help_content', array( $this, 'get_help_content_ajax' ) );
		add_action( 'wp_footer', array( $this, 'render_help_overlay' ) );
		add_action( 'admin_footer', array( $this, 'render_help_overlay' ) );
		add_filter( 'ennu_help_tooltip', array( $this, 'render_help_tooltip' ), 10, 2 );
	}

	/**
	 * Setup help content configuration
	 *
	 * @since 3.37.14
	 */
	private function setup_help_content() {
		$this->help_content = array(
			'dashboard_overview' => array(
				'title'       => __( 'Dashboard Overview', 'ennulifeassessments' ),
				'content'     => __( 'Your Biometric Canvas is your central health hub. Here you can view all your assessments, track biomarkers, monitor progress, and access personalized recommendations.', 'ennulifeassessments' ),
				'video_url'   => '',
				'related_topics' => array( 'assessments', 'biomarkers', 'progress_tracking' ),
			),
			'assessments' => array(
				'title'       => __( 'Health Assessments', 'ennulifeassessments' ),
				'content'     => __( 'Complete comprehensive health assessments to get personalized insights. Each assessment evaluates different aspects of your health and provides actionable recommendations.', 'ennulifeassessments' ),
				'video_url'   => '',
				'related_topics' => array( 'assessment_types', 'scoring', 'recommendations' ),
			),
			'biomarkers' => array(
				'title'       => __( 'Biomarker Tracking', 'ennulifeassessments' ),
				'content'     => __( 'Biomarkers are measurable indicators of your health status. Track key markers like blood pressure, cholesterol, glucose, and more to monitor your health trends over time.', 'ennulifeassessments' ),
				'video_url'   => '',
				'related_topics' => array( 'biomarker_types', 'normal_ranges', 'trends' ),
			),
			'progress_tracking' => array(
				'title'       => __( 'Progress Tracking', 'ennulifeassessments' ),
				'content'     => __( 'Visualize your health journey with interactive charts and progress indicators. Track improvements, identify trends, and celebrate your health milestones.', 'ennulifeassessments' ),
				'video_url'   => '',
				'related_topics' => array( 'charts', 'milestones', 'goals' ),
			),
			'scoring_system' => array(
				'title'       => __( 'Scoring System', 'ennulifeassessments' ),
				'content'     => __( 'Our scoring system provides honest, transparent evaluations of your health status. Scores are based on scientific evidence and personalized to your unique profile.', 'ennulifeassessments' ),
				'video_url'   => '',
				'related_topics' => array( 'score_interpretation', 'missing_data', 'improvements' ),
			),
			'next_steps' => array(
				'title'       => __( 'Next Steps', 'ennulifeassessments' ),
				'content'     => __( 'Get personalized, actionable recommendations based on your assessment results and biomarker data. Each recommendation is designed to help you achieve optimal health.', 'ennulifeassessments' ),
				'video_url'   => '',
				'related_topics' => array( 'recommendations', 'action_plans', 'goals' ),
			),
			'data_privacy' => array(
				'title'       => __( 'Data Privacy', 'ennulifeassessments' ),
				'content'     => __( 'Your health data is protected with enterprise-grade security. We follow HIPAA guidelines and never share your personal information without your explicit consent.', 'ennulifeassessments' ),
				'video_url'   => '',
				'related_topics' => array( 'security', 'privacy_policy', 'data_control' ),
			),
		);
	}

	/**
	 * Setup tooltip configuration
	 *
	 * @since 3.37.14
	 */
	private function setup_tooltip_config() {
		$this->tooltip_config = array(
			'assessment_score' => array(
				'content'     => __( 'This score represents your current health status in this category. Higher scores indicate better health outcomes.', 'ennulifeassessments' ),
				'position'    => 'top',
				'trigger'     => 'hover',
			),
			'biomarker_value' => array(
				'content'     => __( 'This is your current measurement. Compare it to the normal range to understand your health status.', 'ennulifeassessments' ),
				'position'    => 'right',
				'trigger'     => 'hover',
			),
			'progress_indicator' => array(
				'content'     => __( 'This shows your progress over time. Green indicates improvement, red indicates decline, and yellow indicates stability.', 'ennulifeassessments' ),
				'position'    => 'bottom',
				'trigger'     => 'hover',
			),
			'recommendation' => array(
				'content'     => __( 'This is a personalized recommendation based on your health data. Click to learn more about implementation.', 'ennulifeassessments' ),
				'position'    => 'left',
				'trigger'     => 'hover',
			),
			'missing_data' => array(
				'content'     => __( 'This indicator shows when we don\'t have enough data to provide a complete assessment. Complete more assessments to get better insights.', 'ennulifeassessments' ),
				'position'    => 'top',
				'trigger'     => 'hover',
			),
			'trend_arrow' => array(
				'content'     => __( 'This arrow shows the direction of change in your health markers. Upward arrows indicate improvement, downward arrows indicate decline.', 'ennulifeassessments' ),
				'position'    => 'right',
				'trigger'     => 'hover',
			),
		);
	}

	/**
	 * Enqueue help system assets
	 *
	 * @since 3.37.14
	 */
	public function enqueue_help_assets() {
		wp_enqueue_script(
			'ennu-help-system',
			plugins_url( 'assets/js/help-system.js', dirname( __FILE__ ) ),
			array( 'jquery' ),
			ENNU_LIFE_VERSION,
			true
		);

		wp_enqueue_style(
			'ennu-help-system',
			plugins_url( 'assets/css/help-system.css', dirname( __FILE__ ) ),
			array(),
			ENNU_LIFE_VERSION
		);

		// Localize script with help data
		wp_localize_script(
			'ennu-help-system',
			'ennuHelp',
			array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'ennu_help_nonce' ),
				'helpContent' => $this->help_content,
				'tooltipConfig' => $this->tooltip_config,
				'strings'    => array(
					'help'      => __( 'Help', 'ennulifeassessments' ),
					'close'     => __( 'Close', 'ennulifeassessments' ),
					'learn_more' => __( 'Learn More', 'ennulifeassessments' ),
					'related_topics' => __( 'Related Topics', 'ennulifeassessments' ),
				),
			)
		);
	}

	/**
	 * Get help content via AJAX
	 *
	 * @since 3.37.14
	 */
	public function get_help_content_ajax() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_help_nonce' ) ) {
			wp_die( esc_html__( 'Security check failed', 'ennulifeassessments' ) );
		}

		$topic = sanitize_text_field( wp_unslash( $_POST['topic'] ) );
		
		if ( ! isset( $this->help_content[ $topic ] ) ) {
			wp_send_json_error( __( 'Help topic not found', 'ennulifeassessments' ) );
		}

		wp_send_json_success( $this->help_content[ $topic ] );
	}

	/**
	 * Render help overlay
	 *
	 * @since 3.37.14
	 */
	public function render_help_overlay() {
		?>
		<div id="ennu-help-overlay" class="ennu-help-overlay" style="display: none;">
			<div class="ennu-help-backdrop"></div>
			<div class="ennu-help-modal" id="ennu-help-modal">
				<div class="ennu-help-header">
					<h3 class="ennu-help-title" id="ennu-help-title"></h3>
					<button class="ennu-help-close" id="ennu-help-close" aria-label="<?php esc_attr_e( 'Close help', 'ennulifeassessments' ); ?>">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<line x1="18" y1="6" x2="6" y2="18"></line>
							<line x1="6" y1="6" x2="18" y2="18"></line>
						</svg>
					</button>
				</div>
				<div class="ennu-help-content">
					<div class="ennu-help-text" id="ennu-help-text"></div>
					<div class="ennu-help-video" id="ennu-help-video" style="display: none;">
						<iframe id="ennu-help-video-frame" width="100%" height="315" frameborder="0" allowfullscreen></iframe>
					</div>
					<div class="ennu-help-related" id="ennu-help-related" style="display: none;">
						<h4><?php esc_html_e( 'Related Topics', 'ennulifeassessments' ); ?></h4>
						<ul class="ennu-help-related-list" id="ennu-help-related-list"></ul>
					</div>
				</div>
				<div class="ennu-help-footer">
					<button class="ennu-help-btn ennu-help-btn-secondary" id="ennu-help-secondary">
						<?php esc_html_e( 'Close', 'ennulifeassessments' ); ?>
					</button>
					<button class="ennu-help-btn ennu-help-btn-primary" id="ennu-help-primary">
						<?php esc_html_e( 'Learn More', 'ennulifeassessments' ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render help tooltip
	 *
	 * @since 3.37.14
	 * @param string $content Tooltip content.
	 * @param array  $config Tooltip configuration.
	 * @return string
	 */
	public function render_help_tooltip( $content, $config = array() ) {
		$default_config = array(
			'position' => 'top',
			'trigger'  => 'hover',
			'class'    => '',
		);

		$config = wp_parse_args( $config, $default_config );

		ob_start();
		?>
		<div class="ennu-help-tooltip ennu-help-tooltip-<?php echo esc_attr( $config['position'] ); ?> <?php echo esc_attr( $config['class'] ); ?>" 
			 data-trigger="<?php echo esc_attr( $config['trigger'] ); ?>">
			<div class="ennu-help-tooltip-content">
				<?php echo wp_kses_post( $content ); ?>
			</div>
			<div class="ennu-help-tooltip-arrow"></div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render help trigger button
	 *
	 * @since 3.37.14
	 * @param string $topic Help topic.
	 * @param string $text Button text.
	 * @return string
	 */
	public function render_help_trigger( $topic, $text = '' ) {
		if ( empty( $text ) ) {
			$text = __( 'Help', 'ennulifeassessments' );
		}

		ob_start();
		?>
		<button class="ennu-help-trigger" data-help-topic="<?php echo esc_attr( $topic ); ?>" aria-label="<?php esc_attr_e( 'Get help', 'ennulifeassessments' ); ?>">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
				<circle cx="12" cy="12" r="10"></circle>
				<path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
				<line x1="12" y1="17" x2="12.01" y2="17"></line>
			</svg>
			<?php echo esc_html( $text ); ?>
		</button>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render contextual help for a specific element
	 *
	 * @since 3.37.14
	 * @param string $element_id Element ID.
	 * @param string $help_text Help text.
	 * @param array  $config Configuration.
	 * @return string
	 */
	public function render_contextual_help( $element_id, $help_text, $config = array() ) {
		$default_config = array(
			'position' => 'top',
			'trigger'  => 'hover',
			'icon'     => '?',
		);

		$config = wp_parse_args( $config, $default_config );

		ob_start();
		?>
		<span class="ennu-contextual-help" 
			  data-element="<?php echo esc_attr( $element_id ); ?>"
			  data-position="<?php echo esc_attr( $config['position'] ); ?>"
			  data-trigger="<?php echo esc_attr( $config['trigger'] ); ?>"
			  title="<?php echo esc_attr( $help_text ); ?>">
			<?php echo esc_html( $config['icon'] ); ?>
		</span>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get help statistics
	 *
	 * @since 3.37.14
	 * @return array
	 */
	public function get_help_stats() {
		$stats = array(
			'total_help_requests' => 0,
			'most_requested_topics' => array(),
			'average_session_time' => 0,
		);

		// Get help request statistics from user meta
		global $wpdb;
		
		$help_requests = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
				'_ennu_help_request_%'
			)
		);
		$stats['total_help_requests'] = (int) $help_requests;

		return $stats;
	}

	/**
	 * Track help request
	 *
	 * @since 3.37.14
	 * @param string $topic Help topic.
	 */
	public function track_help_request( $topic ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		$help_requests = get_user_meta( $user_id, '_ennu_help_requests', true );
		if ( ! is_array( $help_requests ) ) {
			$help_requests = array();
		}

		$help_requests[] = array(
			'topic' => $topic,
			'timestamp' => current_time( 'mysql' ),
		);

		update_user_meta( $user_id, '_ennu_help_requests', $help_requests );
	}

	/**
	 * Get help content for a specific topic
	 *
	 * @since 3.37.14
	 * @param string $topic Help topic.
	 * @return array|false
	 */
	public function get_help_content( $topic ) {
		return isset( $this->help_content[ $topic ] ) ? $this->help_content[ $topic ] : false;
	}

	/**
	 * Get tooltip configuration for a specific type
	 *
	 * @since 3.37.14
	 * @param string $type Tooltip type.
	 * @return array|false
	 */
	public function get_tooltip_config( $type ) {
		return isset( $this->tooltip_config[ $type ] ) ? $this->tooltip_config[ $type ] : false;
	}

	/**
	 * Add custom help content
	 *
	 * @since 3.37.14
	 * @param string $topic Topic key.
	 * @param array  $content Help content.
	 */
	public function add_help_content( $topic, $content ) {
		$this->help_content[ $topic ] = $content;
	}

	/**
	 * Add custom tooltip configuration
	 *
	 * @since 3.37.14
	 * @param string $type Tooltip type.
	 * @param array  $config Tooltip configuration.
	 */
	public function add_tooltip_config( $type, $config ) {
		$this->tooltip_config[ $type ] = $config;
	}
}

// Initialize the help system
if ( class_exists( 'ENNU_Help_System' ) ) {
	new ENNU_Help_System();
} 