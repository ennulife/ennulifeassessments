<?php
/**
 * ENNU Life Assessment Shortcodes Class - Fixed Version
 *
 * Handles all assessment shortcodes with proper security, performance,
 * and WordPress standards compliance.
 *
 * @package ENNU_Life
 * @version 62.2.8
 * @author ENNU Life Development Team
 * @since 62.2.8
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * ENNU Assessment Shortcodes Class
 *
 * Provides secure, performant, and accessible assessment shortcodes
 * with Pixfort icon integration and proper WordPress standards.
 */
final class ENNU_Assessment_Shortcodes {

	/**
	 * Assessment configurations
	 *
	 * @var array
	 */
	private $assessments = array();

	/**
	 * All assessment questions, loaded from a config file.
	 *
	 * @var array
	 */
	private $all_definitions = array();

	/**
	 * Template cache
	 *
	 * @var array
	 */
	private $template_cache = array();

	/**
	 * Initialization flag to prevent multiple initializations
	 *
	 * @var bool
	 */
	private static $initialized = false;

	/**
	 * Shortcodes registration flag to prevent multiple registrations
	 *
	 * @var bool
	 */
	private static $shortcodes_registered = false;

	/**
	 * Database optimizer instance for performance optimization
	 *
	 * @var ENNU_Database_Optimizer|null
	 */
	private $db_optimizer = null;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		
		// Initialize database optimizer for performance
		if ( class_exists( 'ENNU_Database_Optimizer' ) ) {
			$this->db_optimizer = ENNU_Database_Optimizer::get_instance();
		}
	}

	/**
	 * Initialize the class on the 'init' hook.
	 */
	public function init() {
		// Prevent multiple initializations
		if ( self::$initialized ) {
			error_log( 'ENNU Shortcodes: init() called again - skipping to prevent duplicate registration.' );
			return;
		}
		self::$initialized = true;
		
		error_log( 'ENNU Shortcodes: init() called.' );

		// Ensure the scoring system class is available
		if ( ! class_exists( 'ENNU_Scoring_System' ) ) {
			error_log( 'ENNU Shortcodes: ERROR - ENNU_Scoring_System class not found!' );
			return;
		}

		$this->all_definitions = ENNU_Scoring_System::get_all_definitions();
		error_log( 'ENNU Shortcodes: Loaded ' . count( $this->all_definitions ) . ' assessment definitions.' );

		// Delay assessment initialization until after textdomain is loaded
		add_action( 'init', array( $this, 'init_assessments' ), 15 );
		
		$this->setup_hooks();
		$this->register_shortcodes();
		error_log( 'ENNU Shortcodes: init() completed.' );
	}

	/**
	 * Initialize assessment configurations.
	 * This is now hooked to 'init' to allow for translations.
	 */
	public function init_assessments() {
		$this->assessments = array(
			'welcome_assessment'             => array(
				'title'       => __( 'Welcome Assessment', 'ennulifeassessments' ),
				'description' => __( 'Let\'s get to know you and your health goals.', 'ennulifeassessments' ),
				'questions'   => 6,
				'theme_color' => '#5A67D8', // Indigo color
				'icon_set'    => 'hormone',
			),
			'hair_assessment'                => array(
				'title'       => __( 'Hair Assessment', 'ennulifeassessments' ),
				'description' => __( 'Comprehensive hair health evaluation', 'ennulifeassessments' ),
				'questions'   => 11,
				'theme_color' => '#667eea',
				'icon_set'    => 'hair',
			),
			'hair_restoration_assessment'    => array(
				'title'       => __( 'Hair Restoration Assessment', 'ennulifeassessments' ),
				'description' => __( 'Advanced hair restoration evaluation', 'ennulifeassessments' ),
				'questions'   => 11,
				'theme_color' => '#764ba2',
				'icon_set'    => 'restoration',
			),
			'ed_treatment_assessment'        => array(
				'title'       => __( 'ED Treatment Assessment', 'ennulifeassessments' ),
				'description' => __( 'Confidential ED treatment evaluation', 'ennulifeassessments' ),
				'questions'   => 12,
				'theme_color' => '#f093fb',
				'icon_set'    => 'medical',
			),
			'weight_loss_assessment'         => array(
				'title'       => __( 'Weight Loss Assessment', 'ennulifeassessments' ),
				'description' => __( 'Personalized weight management evaluation', 'ennulifeassessments' ),
				'questions'   => 13,
				'theme_color' => '#4facfe',
				'icon_set'    => 'fitness',
			),
			'weight_loss_quiz'               => array(
				'title'       => __( 'Weight Loss Quiz', 'ennulifeassessments' ),
				'description' => __( 'Quick weight loss readiness quiz', 'ennulifeassessments' ),
				'questions'   => 8,
				'theme_color' => '#43e97b',
				'icon_set'    => 'quiz',
			),
			'health_assessment'              => array(
				'title'       => __( 'Health Assessment', 'ennulifeassessments' ),
				'description' => __( 'Comprehensive health evaluation', 'ennulifeassessments' ),
				'questions'   => 11,
				'theme_color' => '#fa709a',
				'icon_set'    => 'health',
			),
			'skin_assessment'                => array(
				'title'       => __( 'Skin Assessment', 'ennulifeassessments' ),
				'description' => __( 'Comprehensive skin health evaluation', 'ennulifeassessments' ),
				'questions'   => 9,
				'theme_color' => '#a8edea',
				'icon_set'    => 'skin',
			),
			'advanced_skin_assessment'       => array(
				'title'       => __( 'Advanced Skin Assessment', 'ennulifeassessments' ),
				'description' => __( 'Detailed skin health analysis', 'ennulifeassessments' ),
				'questions'   => 9,
				'theme_color' => '#a8edea',
				'icon_set'    => 'skin',
			),
			'skin_assessment_enhanced'       => array(
				'title'       => __( 'Skin Assessment Enhanced', 'ennulifeassessments' ),
				'description' => __( 'Enhanced skin evaluation', 'ennulifeassessments' ),
				'questions'   => 8,
				'theme_color' => '#d299c2',
				'icon_set'    => 'skincare',
			),
			'hormone_assessment'             => array(
				'title'       => __( 'Hormone Assessment', 'ennulifeassessments' ),
				'description' => __( 'Comprehensive hormone evaluation', 'ennulifeassessments' ),
				'questions'   => 12,
				'theme_color' => '#ffecd2',
				'icon_set'    => 'hormone',
			),
			// --- NEW ASSESSMENTS ---
			'sleep_assessment'               => array(
				'title'       => __( 'Sleep Assessment', 'ennulifeassessments' ),
				'description' => __( 'Placeholder for sleep assessment description.', 'ennulifeassessments' ),
				'questions'   => 1,
				'theme_color' => '#4a90e2',
				'icon_set'    => 'quiz',
			),
			'menopause_assessment'           => array(
				'title'       => __( 'Menopause Assessment', 'ennulifeassessments' ),
				'description' => __( 'Placeholder for menopause assessment description.', 'ennulifeassessments' ),
				'questions'   => 1,
				'theme_color' => '#d0021b',
				'icon_set'    => 'medical',
			),
			'testosterone_assessment'        => array(
				'title'       => __( 'Testosterone Assessment', 'ennulifeassessments' ),
				'description' => __( 'Placeholder for testosterone assessment description.', 'ennulifeassessments' ),
				'questions'   => 1,
				'theme_color' => '#f5a623',
				'icon_set'    => 'medical',
			),
			'health_optimization_assessment' => array(
				'title'       => __( 'Health Optimization Assessment', 'ennulifeassessments' ),
				'description' => __( 'Comprehensive health optimization assessment', 'ennulifeassessments' ),
				'questions'   => 11,
				'theme_color' => '#fa709a',
				'icon_set'    => 'health',
			),
		);
	}

	/**
	 * Register all assessment shortcodes
	 */
	public function register_shortcodes() {
		// Prevent multiple shortcode registrations
		if ( self::$shortcodes_registered ) {
			error_log( 'ENNU Shortcodes: register_shortcodes() called again - skipping to prevent duplicate registration.' );
			return;
		}
		self::$shortcodes_registered = true;
		
		error_log( 'ENNU Shortcodes: register_shortcodes() called.' );
		error_log( 'ENNU Shortcodes: all_definitions count: ' . count( $this->all_definitions ) );
		if ( empty( $this->all_definitions ) ) {
			error_log( 'ENNU Shortcodes: No definitions found, cannot register shortcodes.' );
			return;
		}

		// Dynamically register assessment, results, and details shortcodes
		foreach ( $this->all_definitions as $assessment_key => $config ) {
			// Assessment keys are already the correct format (e.g., 'hair', 'ed-treatment', 'weight-loss')
			$assessment_slug = $assessment_key;

			// Register assessment shortcode (e.g., [ennu-hair], [ennu-ed-treatment])
			add_shortcode( "ennu-{$assessment_slug}", array( $this, 'render_assessment_shortcode' ) );
			error_log( 'ENNU Shortcodes: Registered assessment shortcode: ennu-' . $assessment_slug );

			// Register results shortcode (e.g., [ennu-hair-results], [ennu-ed-treatment-results])
			$results_slug = $assessment_slug . '-results';
			add_shortcode( "ennu-{$results_slug}", array( $this, 'render_thank_you_page' ) );
			error_log( 'ENNU Shortcodes: Registered results shortcode: ennu-' . $results_slug );

			// Register details shortcode (e.g., [ennu-hair-assessment-details], [ennu-ed-treatment-assessment-details])
			$details_slug = $assessment_slug . '-assessment-details';
			add_shortcode(
				"ennu-{$details_slug}",
				function( $atts ) use ( $assessment_key ) {
					return $this->render_detailed_results_page( $atts, '', $assessment_key );
				}
			);
			error_log( 'ENNU Shortcodes: Registered details shortcode: ennu-' . $details_slug );
		}

		// Register the core, non-assessment-specific shortcodes
		add_shortcode( 'ennu-user-dashboard', array( $this, 'render_user_dashboard' ) );
		add_shortcode( 'ennu-assessment-results', array( $this, 'render_thank_you_page' ) ); // Generic fallback
		add_shortcode( 'ennu-assessments', array( $this, 'render_assessments_listing' ) ); // Assessments listing page
		add_shortcode( 'ennu-signup', array( $this, 'signup_shortcode' ) ); // Signup page with product selection
		add_shortcode( 'scorepresentation', array( $this, 'render_score_presentation' ) ); // Score presentation shortcode
		add_shortcode( 'ennu-biomarkers', array( $this, 'render_biomarkers_only' ) ); // Biomarkers only shortcode
		error_log( 'ENNU Shortcodes: Registered core shortcodes: ennu-user-dashboard, ennu-assessment-results, ennu-assessments, ennu-signup, scorepresentation, ennu-biomarkers' );

		// Register consultation shortcodes to match page creation
		$consultation_types = array(
			'hair',
			'ed-treatment',
			'weight-loss',
			'health-optimization',
			'skin',
			'health',
			'hormone',
			'menopause',
			'testosterone',
			'sleep',
		);

		foreach ( $consultation_types as $type ) {
			$shortcode_name = 'ennu-' . $type . '-consultation';
			add_shortcode( $shortcode_name, array( $this, 'render_consultation_shortcode' ) );
			error_log( 'ENNU Shortcodes: Registered consultation shortcode: ' . $shortcode_name );
		}
	}

	/**
	 * Setup WordPress hooks for AJAX and asset enqueuing.
	 */
	private function setup_hooks() {
		// All hooks are now registered in the main plugin file for centralized control.
		// This method is kept for clarity and future use if needed.
		add_action( 'wp_ajax_nopriv_ennu_check_email', array( $this, 'ajax_check_email_exists' ) );
		add_action( 'wp_ajax_ennu_check_auth_state', array( $this, 'ajax_check_auth_state' ) );
		add_action( 'wp_ajax_nopriv_ennu_check_auth_state', array( $this, 'ajax_check_auth_state' ) );
		
		// HubSpot sync test AJAX handlers
		add_action( 'wp_ajax_test_manual_hubspot_sync', array( $this, 'ajax_test_manual_hubspot_sync' ) );
		add_action( 'wp_ajax_test_assessment_hook', array( $this, 'ajax_test_assessment_hook' ) );
		add_action( 'wp_ajax_check_debug_logs', array( $this, 'ajax_check_debug_logs' ) );
		add_action( 'wp_ajax_ennu_get_pillar_modal', array( $this, 'ajax_get_pillar_modal' ) );
	}

	/**
	 * Render assessment shortcode
	 *
	 * @param array $atts Shortcode attributes
	 * @param string $content Shortcode content
	 * @param string $tag Shortcode tag
	 * @return string
	 */
	public function render_assessment_shortcode( $atts, $content = '', $tag = '' ) {
		// Extract assessment type from shortcode tag
		$assessment_type = str_replace( 'ennu-', '', $tag );
		$config          = $this->all_definitions[ $assessment_type ] ?? null;

		// --- NEW: Welcome Shortcode Logic for Logged-In Users ---
		if ( $assessment_type === 'welcome' && is_user_logged_in() ) {
					$dashboard_url = '?' . ENNU_UI_Constants::get_page_type( 'DASHBOARD' );
		$booking_url   = '?' . ENNU_UI_Constants::get_page_type( 'CALL' );
			ob_start();
			?>
			<div class="ennu-welcome-back-container" style="text-align: center; padding: 40px; border: 1px solid #e2e8f0; border-radius: 12px; max-width: 600px; margin: 2rem auto;">
				<h2 style="font-size: 24px; font-weight: 700; color: #1a202c; margin-bottom: 15px;">Welcome Back!</h2>
				<p style="font-size: 16px; color: #4a5568; line-height: 1.6; margin-bottom: 30px;">You've already completed the initial setup. What would you like to do next?</p>
				<div class="ennu-welcome-back-actions" style="display: flex; justify-content: center; gap: 15px;">
					<a href="<?php echo esc_url( $dashboard_url ); ?>" class="button button-primary" style="background-color: #2d3748; color: #fff; padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: 600;">View My Dashboard</a>
					<a href="<?php echo esc_url( $booking_url ); ?>" class="button button-secondary" style="background-color: transparent; color: #2d3748; border: 1px solid #e2e8f0; padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: 600;">Schedule a Call</a>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}

		// Validate assessment type
		if ( ! $config ) {
			return $this->render_error_message( __( 'Invalid assessment type.', 'ennulifeassessments' ) );
		}

		// Definitive gender-filtering logic
		if ( isset( $config['gender_filter'] ) ) {
			$user_id     = get_current_user_id();
			$user_gender = $user_id ? get_user_meta( $user_id, 'ennu_global_gender', true ) : '';

			if ( ! empty( $user_gender ) && $user_gender !== $config['gender_filter'] ) {
				return $this->render_error_message( __( 'This assessment is not available for your profile.', 'ennulifeassessments' ) );
			}
		}

		// Parse attributes
		$atts = shortcode_atts(
			array(
				'theme'         => 'default',
				'show_progress' => 'true',
				'auto_advance'  => 'true',
				'cache'         => 'true',
			),
			$atts,
			$tag
		);

		// Check cache
		$cache_key = md5( $assessment_type . serialize( $atts ) );
		if ( $atts['cache'] === 'true' && isset( $this->template_cache[ $cache_key ] ) ) {
			return $this->template_cache[ $cache_key ];
		}

		try {
			// Render assessment
			$output = $this->render_assessment( $assessment_type, $atts );

			// Cache output
			if ( $atts['cache'] === 'true' ) {
				$this->template_cache[ $cache_key ] = $output;
			}

			return $output;

		} catch ( Exception $e ) {
			error_log( 'ENNU Assessment Error: ' . $e->getMessage() );
			return $this->render_error_message( __( 'Assessment temporarily unavailable.', 'ennulifeassessments' ) );
		}
	}

	/**
	 * Render assessment HTML
	 *
	 * @param string $assessment_type Assessment type
	 * @param array $atts Shortcode attributes
	 * @return string
	 */
	private function render_assessment( $assessment_type, $atts ) {
		$config = $this->all_definitions[ $assessment_type ];

		// Ensure required fields exist with fallbacks
		if ( ! isset( $config['title'] ) ) {
			$config['title'] = ucwords( str_replace( array( '-', '_' ), ' ', $assessment_type ) ) . ' Assessment';
		}
		if ( ! isset( $config['description'] ) ) {
			$config['description'] = 'Complete this assessment to get personalized insights.';
		}

		// Get current user data to pre-populate fields if logged in
		$current_user_data = array();
		if ( is_user_logged_in() ) {
			$user              = wp_get_current_user();
			$user_id           = $user->ID;
			$current_user_data = array(
				// Get data from the most reliable source first (native WP_User object)
				'first_name'    => $user->first_name,
				'last_name'     => $user->last_name,
				'email'         => $user->user_email,
				// Fallback to user meta for other fields, using the correct global keys
				'billing_phone' => get_user_meta( $user_id, 'ennu_global_billing_phone', true ),
				'dob_combined'  => get_user_meta( $user_id, 'ennu_global_date_of_birth', true ),
				'gender'        => get_user_meta( $user_id, 'ennu_global_gender', true ),
			);
		}

		// Start output buffering
		ob_start();

		// Include assessment template
		$template_file = $this->get_assessment_template( $assessment_type );
		if ( file_exists( $template_file ) ) {
			// Pass the current user data to the template
			include $template_file;
		} else {
			echo $this->render_default_assessment( $assessment_type, $config, $atts, $current_user_data );
		}

		return ob_get_clean();
	}

	/**
	 * Get assessment template file path
	 *
	 * @param string $assessment_type Assessment type
	 * @return string
	 */
	private function get_assessment_template( $assessment_type ) {
		$template_name = 'assessment-' . str_replace( '_', '-', $assessment_type ) . '.php';

		// Check theme directory first
		$theme_template = get_stylesheet_directory() . '/ennu-life/' . $template_name;
		if ( file_exists( $theme_template ) ) {
			return $theme_template;
		}

		// Check plugin templates directory
		if ( defined( 'ENNU_LIFE_PLUGIN_PATH' ) ) {
			$plugin_template = ENNU_LIFE_PLUGIN_PATH . 'templates/' . $template_name;
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		return '';
	}

	/**
	 * Render default assessment template
	 *
	 * @param string $assessment_type Assessment type
	 * @param array $config Assessment configuration
	 * @param array $atts Shortcode attributes
	 * @return string
	 */
	private function render_default_assessment( $assessment_type, $config, $atts, $current_user_data = array() ) {
		$current_user = wp_get_current_user();
		$nonce        = wp_create_nonce( 'ennu_assessment_' . $assessment_type );

		// Get the actual questions to count them properly
		$questions       = $this->get_assessment_questions( $assessment_type );
		$total_questions = count( $questions );

		ob_start();
		?>
		<div class="ennu-assessment" 
			 data-assessment="<?php echo esc_attr( $assessment_type ); ?>"
			 data-theme="<?php echo esc_attr( $atts['theme'] ); ?>">
			 
			<!-- ENNU Life Header -->
			<?php echo $this->render_ennu_header( $assessment_type, $config ); ?>
			 
			<!-- Progress Bar (if enabled) -->
			<?php if ( $atts['show_progress'] === 'true' ) : ?>
			<div class="progress-container">
				<div class="progress-bar">
					<div class="ennu-progress-fill" data-progress="0"></div>
				</div>
				<div class="progress-text">
					<span><?php esc_html_e( 'Question', 'ennulifeassessments' ); ?> 
						  <span id="currentStep" class="current-question">1</span> 
						  <?php esc_html_e( 'of', 'ennulifeassessments' ); ?> 
						  <span id="totalSteps" class="total-questions"><?php echo esc_html( $total_questions ); ?></span>
					</span>
				</div>
			</div>
			<?php endif; ?>
			
			<!-- Assessment Form -->
			<form id="ennu-assessment-form-<?php echo esc_attr( $assessment_type ); ?>" class="ennu-assessment-form" data-assessment="<?php echo esc_attr( $assessment_type ); ?>" data-nonce="<?php echo wp_create_nonce( 'ennu_ajax_nonce' ); ?>">
				<input type="hidden" name="action" value="ennu_submit_assessment">
				<input type="hidden" name="assessment_type" value="<?php echo esc_attr( $assessment_type ); ?>">
				
				<!-- Questions Container -->
				<div class="questions-container">
					<?php echo $this->render_assessment_questions( $assessment_type, $config, $current_user_data ); ?>
				</div>
				
				<!-- Success Message -->
				<div class="assessment-success" style="display: none;">
					<div class="success-icon">✓</div>
					<h2><?php esc_html_e( 'Assessment Complete!', 'ennulifeassessments' ); ?></h2>
					<p><?php esc_html_e( 'Thank you for completing your assessment. Your personalized results and recommendations will be sent to your email shortly.', 'ennulifeassessments' ); ?></p>
					<div class="next-steps">
						<h3><?php esc_html_e( 'What happens next?', 'ennulifeassessments' ); ?></h3>
						<ul>
							<li><?php esc_html_e( 'Our medical team will review your responses', 'ennulifeassessments' ); ?></li>
							<li><?php esc_html_e( 'You\'ll receive personalized recommendations via email', 'ennulifeassessments' ); ?></li>
							<li><?php esc_html_e( 'A specialist may contact you to discuss treatment options', 'ennulifeassessments' ); ?></li>
						</ul>
					</div>
				</div>
			</form>
		</div>
		
		
		<!-- Assessment JavaScript Debug Info -->
		<script>
		// Assessment will be automatically initialized by ennu-assessment-modern.js
		// when DOM is ready and .ennu-assessment container is found
		</script>
		<?php

		return ob_get_clean();
	}

	/**
	 * Render assessment questions
	 *
	 * @param string $assessment_type Assessment type
	 * @param array $config Assessment configuration
	 * @return string
	 */
	private function render_assessment_questions( $assessment_type, $config, $current_user_data = array() ) {
		$all_questions = $this->get_assessment_questions( $assessment_type );

		// The definitive fix: Check if the questions are nested under a 'questions' key.
		$questions = isset( $all_questions['questions'] ) ? $all_questions['questions'] : $all_questions;

		$output          = '';
		$question_number = 1;
		foreach ( $questions as $question_key => $question ) {
			if ( ! is_array( $question ) ) {
				continue;
			}
			$output .= $this->render_question( $assessment_type, $question_number, $question, $config, $current_user_data, $question_key );
			$question_number++;
		}

		// Smart Contact Form Logic: Add contact form if needed
		$output .= $this->render_smart_contact_form( $assessment_type, $question_number, $config, $current_user_data );

		return $output;
	}

	/**
	 * Render individual question by dispatching to a dedicated method based on type.
	 */
	private function render_question( $assessment_type, $question_number, $question, $config, $current_user_data = array(), $question_key = '' ) {
		$user_id = get_current_user_id();

		// This block is the definitive fix, restoring the logic to fetch saved user data.
		$pre_selected_value = null;
		if ( $user_id ) {
			$meta_key = 'ennu_' . $assessment_type . '_' . $question_key;
			if ( ! empty( $question['global_key'] ) ) {
				$meta_key = 'ennu_global_' . $question['global_key'];
			}
			$saved_value = get_user_meta( $user_id, $meta_key, true );

			error_log( "ENNU DEBUG: Pre-populating field {$question_key} -> {$meta_key} -> " . ( is_array( $saved_value ) ? json_encode( $saved_value ) : $saved_value ) );

			if ( 'multiselect' === ( $question['type'] ?? '' ) ) {
				$pre_selected_value = is_array( $saved_value ) ? $saved_value : array();
			} else {
				$pre_selected_value = $saved_value;
			}
		} else {
			if ( 'multiselect' === ( $question['type'] ?? '' ) ) {
				$pre_selected_value = array();
			}
		}

		$active_class    = $question_number === 1 ? 'active' : '';
		$is_global_slide = ! empty( $question['global_key'] );

		ob_start();
		?>
		<div class="question-slide <?php echo esc_attr( $active_class ); ?>" data-step="<?php echo esc_attr( $question_number ); ?>" data-question-key="<?php echo esc_attr( $question_key ); ?>" data-question-type="<?php echo esc_attr( $question['type'] ); ?>" 
		<?php
		if ( $is_global_slide ) {
			echo 'data-is-global="true"';}
		?>
		>
			<div class="question-header">
				<h2 class="question-title"><?php echo esc_html( $question['title'] ); ?></h2>
				<?php if ( ! empty( $question['description'] ) ) : ?>
					<p class="question-description"><?php echo esc_html( $question['description'] ); ?></p>
				<?php endif; ?>
			</div>
			
			<div class="question-content">
				<?php
				// Render the primary question (e.g., the multiselect symptom checklist)
				echo $this->_render_field( $question['type'], $question, $question_key, $pre_selected_value );
				?>
			</div>

			<?php if ( isset( $question['qualifiers'] ) && is_array( $question['qualifiers'] ) ) : ?>
				<div class="qualifiers-container" style="display: none;">
					<hr>
					<h3 class="qualifiers-title"><?php echo esc_html__( 'Please provide more detail:', 'ennulifeassessments' ); ?></h3>
					<?php foreach ( $question['qualifiers'] as $symptom_value => $qualifier_questions ) : ?>
						<div class="qualifier-group" data-qualifier-for="<?php echo esc_attr( $symptom_value ); ?>" style="display: none;">
							<?php foreach ( $qualifier_questions as $qualifier_def ) : ?>
								<div class="qualifier-question">
									<h4 class="qualifier-title"><?php echo esc_html( $qualifier_def['title'] ); ?></h4>
									<?php echo $this->_render_field( $qualifier_def['type'], $qualifier_def, $qualifier_def['id'], '' ); ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			
			<div class="question-navigation">
				<?php if ( $question_number > 1 ) : ?>
					<button type="button" class="nav-button prev">← Previous</button>
				<?php endif; ?>
				
				<?php
				// This needs to be intelligent about the nested questions.
				$all_questions    = $this->get_assessment_questions( $assessment_type );
				$questions        = isset( $all_questions['questions'] ) ? $all_questions['questions'] : $all_questions;
				$total_questions  = count( $questions );
				$is_last_question = $question_number >= $total_questions;
				?>
				
				<button type="button" class="nav-button next <?php echo esc_attr( $is_last_question ? 'submit' : '' ); ?>">
					<?php echo $is_last_question ? esc_html__( 'Submit & See Results', 'ennulifeassessments' ) : esc_html__( 'Next →', 'ennulifeassessments' ); ?>
				</button>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	// This new method replaces the multiple _render_*_question methods
	private function _render_field( $type, $config, $name, $saved_value ) {
		// This is where the fatal error was. The $current_user_data was not being passed down.
		// The logic is now moved back inside the _render_* functions to ensure correct context.
		// This dispatcher is now simpler and more robust.
		switch ( $type ) {
			case 'dob_dropdowns':
				return $this->_render_dob_dropdowns_question( $config, $name, $saved_value );
			case 'multiselect':
				return $this->_render_multiselect_question( $config, $name, $saved_value );
			case 'first_last_name':
				return $this->_render_first_last_name_question( $config, $name, $saved_value );
			case 'email_phone':
				return $this->_render_email_phone_question( $config, $name, $saved_value );
			case 'height_weight':
				return $this->_render_height_weight_question( $config, $name, $saved_value );
			case 'contact_info':
				return $this->_render_contact_info_question( $config, $name, $saved_value );
			default: // Default to 'radio'
				return $this->_render_radio_question( $config, $name, $saved_value );
		}
	}

	/**
	 * Renders the HTML for a 'Date of Birth' question.
	 */
	private function _render_dob_dropdowns_question( $question, $question_key, $saved_value = '' ) {
		// Use the saved_value parameter passed from render_question method
		$dob = $saved_value;

		// Fallback to fetching from user meta if no saved_value provided
		if ( empty( $dob ) && is_user_logged_in() ) {
			$user_id = get_current_user_id();
			$dob     = get_user_meta( $user_id, 'ennu_global_date_of_birth', true );
		}

		$is_logged_in = ! empty( $dob );
		$dob_parts    = ! empty( $dob ) ? explode( '-', $dob ) : array( '', '', '' );
		$year_val     = $dob_parts[0];
		$month_val    = intval( $dob_parts[1] ); // Convert to integer to fix dropdown selection
		$day_val      = intval( $dob_parts[2] ); // Convert to integer for consistency

		$months = array(
			1  => 'January',
			2  => 'February',
			3  => 'March',
			4  => 'April',
			5  => 'May',
			6  => 'June',
			7  => 'July',
			8  => 'August',
			9  => 'September',
			10 => 'October',
			11 => 'November',
			12 => 'December',
		);

		ob_start();
		?>
		<div class="question-content">
			<div class="dob-dropdowns">
				<div class="dob-field">
					<label for="<?php echo esc_attr( $question_key ); ?>_month">Month:</label>
					<select id="<?php echo esc_attr( $question_key ); ?>_month" name="ennu_global_date_of_birth_month" required>
						<option value="" disabled <?php selected( ! $is_logged_in ); ?>>Month</option>
						<?php foreach ( $months as $num => $name ) : ?>
							<option value="<?php echo esc_attr( $num ); ?>" <?php selected( $month_val, $num ); ?>>
								<?php echo esc_html( $name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="dob-field">
					<label for="<?php echo esc_attr( $question_key ); ?>_day">Day:</label>
					<select id="<?php echo esc_attr( $question_key ); ?>_day" name="ennu_global_date_of_birth_day" required>
						<option value="" disabled <?php selected( ! $is_logged_in ); ?>>Day</option>
						<?php for ( $i = 1; $i <= 31; $i++ ) : ?>
							<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $day_val, $i ); ?>>
								<?php echo esc_html( $i ); ?>
							</option>
						<?php endfor; ?>
					</select>
				</div>
				<div class="dob-field">
					<label for="<?php echo esc_attr( $question_key ); ?>_year">Year:</label>
					<select id="<?php echo esc_attr( $question_key ); ?>_year" name="ennu_global_date_of_birth_year" required>
						<option value="" disabled <?php selected( ! $is_logged_in ); ?>>Year</option>
						<?php for ( $i = date( 'Y' ); $i >= 1900; $i-- ) : ?>
							<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $year_val, $i ); ?>>
								<?php echo esc_html( $i ); ?>
							</option>
						<?php endfor; ?>
					</select>
				</div>
			</div>
			<div class="calculated-age-display" style="min-height: 20px; margin-top: 10px;"></div>
			<input type="hidden" name="ennu_global_date_of_birth" class="dob-combined" value="<?php echo esc_attr( $dob ); ?>" />
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Renders the HTML for a 'multiselect' question.
	 */
	private function _render_multiselect_question( $question, $question_key, $saved_options ) {
		// Ensure $saved_options is always an array to prevent fatal errors.
		$saved_options = is_array( $saved_options ) ? $saved_options : array();

		$count       = count( $question['options'] );
		$num_columns = 2; // Default
		if ( $count === 3 || $count > 4 ) {
			$num_columns = 3; }
		if ( $count === 4 ) {
			$num_columns = 4; }
		$column_class = 'columns-' . $num_columns;
		ob_start();
		?>
		<div class="question-content">
			<div class="answer-options <?php echo esc_attr( $column_class ); ?>">
				<?php foreach ( $question['options'] as $option_value => $option_label ) : ?>
					<div class="answer-option">
						<input type="checkbox" 
							   id="<?php echo esc_attr( $question_key ); ?>_<?php echo esc_attr( $option_value ); ?>" 
							   name="<?php echo esc_attr( $question_key ); ?>[]" 
							   value="<?php echo esc_attr( $option_value ); ?>" 
							   <?php checked( in_array( $option_value, $saved_options ) ); ?> required>
						<label for="<?php echo esc_attr( $question_key ); ?>_<?php echo esc_attr( $option_value ); ?>">
							<?php echo esc_html( $option_label ); ?>
						</label>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Retrieves user height/weight data from various sources with fallback logic.
	 *
	 * @param mixed $saved_value The saved value from the form submission
	 * @return array Height/weight data array
	 */
	private function get_user_height_weight_data( $saved_value = '' ) {
		$height_weight_data = array();
		
		// If we have a saved value, try to use it first
		if ( ! empty( $saved_value ) ) {
			if ( is_array( $saved_value ) ) {
				$height_weight_data = $saved_value;
			} elseif ( is_string( $saved_value ) ) {
				$height_weight_data = maybe_unserialize( $saved_value );
			}
		}
		
		// If no saved value or invalid data, check user meta
		if ( empty( $height_weight_data ) || ! is_array( $height_weight_data ) ) {
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
				
				// Check global height/weight data
				$global_height_weight = get_user_meta( $user_id, 'ennu_global_height_weight', true );
				if ( ! empty( $global_height_weight ) ) {
					if ( is_array( $global_height_weight ) ) {
						$height_weight_data = $global_height_weight;
					} elseif ( is_string( $global_height_weight ) ) {
						$height_weight_data = maybe_unserialize( $global_height_weight );
					}
				}
				
				// If still no data, check individual height and weight fields
				if ( empty( $height_weight_data ) || ! is_array( $height_weight_data ) ) {
					$height_weight_data = array();
					
					// Check individual height fields
					$height_ft = get_user_meta( $user_id, 'ennu_global_height_ft', true );
					$height_in = get_user_meta( $user_id, 'ennu_global_height_in', true );
					
					if ( ! empty( $height_ft ) ) {
						$height_weight_data['ft'] = $height_ft;
					}
					if ( ! empty( $height_in ) ) {
						$height_weight_data['in'] = $height_in;
					}
					
					// Check individual weight fields
					$weight_lbs = get_user_meta( $user_id, 'ennu_global_weight_lbs', true );
					if ( empty( $weight_lbs ) ) {
						$weight_lbs = get_user_meta( $user_id, 'ennu_global_weight', true );
					}
					
					if ( ! empty( $weight_lbs ) ) {
						$height_weight_data['weight'] = $weight_lbs;
					}
				}
			}
		}
		
		// Ensure we return an array
		return is_array( $height_weight_data ) ? $height_weight_data : array();
	}

	/**
	 * Renders the HTML for a 'height_weight' question.
	 */
	private function _render_height_weight_question( $question, $question_key, $saved_value = '' ) {
		// Get height/weight data with enhanced fallback logic
		$height_weight_data = $this->get_user_height_weight_data( $saved_value );

		// Enhanced fallback logic: Always check global meta for height/weight
		if ( is_user_logged_in() ) {
			$user_id              = get_current_user_id();
			$global_height_weight = get_user_meta( $user_id, 'ennu_global_height_weight', true );

			// If we have global data and no saved_value, or if saved_value is empty, use global data
			if ( ! empty( $global_height_weight ) && ( empty( $height_weight_data ) || ! is_array( $height_weight_data ) ) ) {
				$height_weight_data = $global_height_weight;
			}
		}

		// Ensure it's an array and handle potential serialization issues
		if ( is_string( $height_weight_data ) ) {
			$height_weight_data = maybe_unserialize( $height_weight_data );
		}
		$height_weight_data = is_array( $height_weight_data ) ? $height_weight_data : array();

		$height_ft  = isset( $height_weight_data['ft'] ) ? $height_weight_data['ft'] : '';
		$height_in  = isset( $height_weight_data['in'] ) ? $height_weight_data['in'] : '';
		// Handle both 'weight' and 'lbs' keys for backward compatibility
		$weight_lbs = isset( $height_weight_data['weight'] ) ? $height_weight_data['weight'] : ( isset( $height_weight_data['lbs'] ) ? $height_weight_data['lbs'] : '' );

		// Debug logging (only in development)
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( "ENNU Height/Weight Debug - Question Key: {$question_key}" );
			error_log( 'ENNU Height/Weight Debug - Saved Value: ' . print_r( $saved_value, true ) );
			error_log( 'ENNU Height/Weight Debug - Height Weight Data: ' . print_r( $height_weight_data, true ) );
			error_log( "ENNU Height/Weight Debug - Parsed Values - ft: {$height_ft}, in: {$height_in}, lbs: {$weight_lbs}" );
		}

		ob_start();
		?>
		<div class="question-content">
			<div class="height-weight-container">
				<div class="hw-field">
					<label for="<?php echo esc_attr( $question_key ); ?>_height_ft">Height (ft):</label>
					<input type="number" id="<?php echo esc_attr( $question_key ); ?>_height_ft" name="height_ft" min="0" step="1" required value="<?php echo esc_attr( $height_ft ); ?>">
				</div>
				<div class="hw-field">
					<label for="<?php echo esc_attr( $question_key ); ?>_height_in">Height (in):</label>
					<input type="number" id="<?php echo esc_attr( $question_key ); ?>_height_in" name="height_in" min="0" step="1" required value="<?php echo esc_attr( $height_in ); ?>">
				</div>
				<div class="hw-field">
					<label for="<?php echo esc_attr( $question_key ); ?>_weight_lbs">Weight (lbs):</label>
					<input type="number" id="<?php echo esc_attr( $question_key ); ?>_weight_lbs" name="weight_lbs" min="0" step="0.1" required value="<?php echo esc_attr( $weight_lbs ); ?>">
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Renders the HTML for a 'contact_info' question.
	 */
	private function _render_contact_info_question( $question, $question_key, $saved_value = '' ) {
		$current_user = is_user_logged_in() ? wp_get_current_user() : null;
		$first_name   = $current_user ? $current_user->first_name : '';
		$last_name    = $current_user ? $current_user->last_name : '';
		$email        = $current_user ? $current_user->user_email : '';
		$phone        = $current_user ? get_user_meta( $current_user->ID, 'billing_phone', true ) : '';
		$readonly     = $current_user ? 'readonly' : '';

		ob_start();
		?>
		<div class="question-content">
			<div class="contact-fields">
				<div class="contact-field">
					<label for="<?php echo esc_attr( $question_key ); ?>_first_name">First Name:</label>
					<input type="text" id="<?php echo esc_attr( $question_key ); ?>_first_name" name="first_name" placeholder="First Name" value="<?php echo esc_attr( $first_name ); ?>" <?php echo $readonly; ?> required>
						</div>
				<div class="contact-field">
					<label for="<?php echo esc_attr( $question_key ); ?>_last_name">Last Name:</label>
					<input type="text" id="<?php echo esc_attr( $question_key ); ?>_last_name" name="last_name" placeholder="Last Name" value="<?php echo esc_attr( $last_name ); ?>" <?php echo $readonly; ?> required>
				</div>
				<div class="contact-field">
					<label for="<?php echo esc_attr( $question_key ); ?>_email">Email:</label>
					<input type="email" id="<?php echo esc_attr( $question_key ); ?>_email" name="email" placeholder="Email Address" value="<?php echo esc_attr( $email ); ?>" <?php echo $readonly; ?> required>
				</div>
				<div class="contact-field">
					<label for="<?php echo esc_attr( $question_key ); ?>_phone">Phone:</label>
					<input type="tel" id="<?php echo esc_attr( $question_key ); ?>_phone" name="billing_phone" placeholder="Phone Number" value="<?php echo esc_attr( $phone ); ?>" <?php echo $readonly; ?>>
				</div>
			</div>
		</div>
				<?php
				return ob_get_clean();
	}

	/**
	 * Renders the HTML for a 'radio' button question.
	 */
	private function _render_radio_question( $question, $question_key, $pre_selected_value ) {
		// This is the definitive fix. If a field was once a multiselect, the old data
		// might be an array. We must gracefully handle this to prevent fatal errors.
		if ( is_array( $pre_selected_value ) ) {
			$pre_selected_value = reset( $pre_selected_value );
		}

		// Debug logging for gender fields
		if ( strpos( $question_key, 'gender' ) !== false || ( isset( $question['global_key'] ) && $question['global_key'] === 'gender' ) ) {
			error_log( "ENNU DEBUG: Rendering gender field {$question_key} with pre_selected_value: " . ( $pre_selected_value ?: 'empty' ) );
		}

		$count       = count( $question['options'] );
		$num_columns = 2; // Default
		if ( $count === 3 || $count > 4 ) {
			$num_columns = 3;
		}
		if ( $count === 4 ) {
			$num_columns = 4;
		}
		$column_class = 'columns-' . $num_columns;

		ob_start();
		?>
		<div class="question-content">
			<div class="answer-options <?php echo esc_attr( $column_class ); ?>">
				<?php foreach ( $question['options'] as $option_value => $option_label ) : ?>
					<div class="answer-option">
						<input type="radio" 
							   id="<?php echo esc_attr( $question_key ); ?>_<?php echo esc_attr( $option_value ); ?>" 
							   name="<?php echo esc_attr( $question_key ); ?>" 
							   value="<?php echo esc_attr( $option_value ); ?>" 
							   <?php echo ( $pre_selected_value === $option_value ) ? 'checked="checked"' : ''; ?> required>
						<label for="<?php echo esc_attr( $question_key ); ?>_<?php echo esc_attr( $option_value ); ?>">
							<?php echo esc_html( $option_label ); ?>
						</label>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Renders the HTML for a 'first_last_name' question.
	 */
	private function _render_first_last_name_question( $question, $question_key, $saved_value = '' ) {
		$current_user = is_user_logged_in() ? wp_get_current_user() : null;
		$first_name   = $current_user ? $current_user->first_name : '';
		$last_name    = $current_user ? $current_user->last_name : '';
		$readonly     = $current_user ? 'readonly' : '';

		ob_start();
		?>
		<div class="question-content">
			<div class="contact-fields">
				<div class="contact-field">
					<label for="<?php echo esc_attr( $question_key ); ?>_first_name">First Name:</label>
					<input type="text" id="<?php echo esc_attr( $question_key ); ?>_first_name" name="first_name" placeholder="First Name" value="<?php echo esc_attr( $first_name ); ?>" <?php echo $readonly; ?> required>
				</div>
				<div class="contact-field">
					<label for="<?php echo esc_attr( $question_key ); ?>_last_name">Last Name:</label>
					<input type="text" id="<?php echo esc_attr( $question_key ); ?>_last_name" name="last_name" placeholder="Last Name" value="<?php echo esc_attr( $last_name ); ?>" <?php echo $readonly; ?> required>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Renders the HTML for an 'email_phone' question.
	 */
	private function _render_email_phone_question( $question, $question_key, $saved_value = '' ) {
		$current_user = is_user_logged_in() ? wp_get_current_user() : null;
		$email        = $current_user ? $current_user->user_email : '';
		$phone        = $current_user ? get_user_meta( $current_user->ID, 'billing_phone', true ) : '';
		$readonly     = $current_user ? 'readonly' : '';

		ob_start();
		?>
		<div class="question-content">
			<div class="contact-fields">
				<div class="contact-field">
					<label for="<?php echo esc_attr( $question_key ); ?>_email">Email:</label>
					<input type="email" id="<?php echo esc_attr( $question_key ); ?>_email" name="email" placeholder="Email Address" value="<?php echo esc_attr( $email ); ?>" <?php echo $readonly; ?> required>
				</div>
				<div class="contact-field">
					<label for="<?php echo esc_attr( $question_key ); ?>_phone">Phone:</label>
					<input type="tel" id="<?php echo esc_attr( $question_key ); ?>_phone" name="billing_phone" placeholder="Phone Number" value="<?php echo esc_attr( $phone ); ?>" <?php echo $readonly; ?>>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get assessment questions configuration
	 *
	 * @param string $assessment_type Assessment type
	 * @return array
	 */
	public function get_assessment_questions( $assessment_type ) {
		// DEFINITIVE FIX: Use the class property, not a direct file include,
		// to ensure the correct, unified definitions are always used.
		
		// If all_definitions is empty, try to reload from scoring system
		if ( empty( $this->all_definitions ) ) {
			error_log( 'ENNU Shortcodes: all_definitions is empty, attempting to reload from scoring system...' );
			
			// Force reload from scoring system
			if ( class_exists( 'ENNU_Scoring_System' ) ) {
				// CRITICAL FIX: Skip transient deletion during AJAX requests to prevent slow queries
				if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
					delete_transient( 'ennu_assessment_definitions_v1' );
				} else {
					error_log( 'ENNU Shortcodes: Skipping transient deletion during AJAX request to prevent slow queries.' );
					// CRITICAL FIX: Skip the entire reload process during AJAX to prevent slow queries
					return array();
				}
				
				// Reload definitions
				$this->all_definitions = ENNU_Scoring_System::get_all_definitions();
				error_log( 'ENNU Shortcodes: Reloaded ' . count( $this->all_definitions ) . ' assessment definitions from scoring system.' );
			} else {
				error_log( 'ENNU Shortcodes: ERROR - ENNU_Scoring_System class not found during reload attempt!' );
			}
		}
		
		$assessment_config = $this->all_definitions[ $assessment_type ] ?? array();
		return isset( $assessment_config['questions'] ) ? $assessment_config['questions'] : array();
	}

	public function get_all_assessment_definitions() {
		return $this->all_definitions;
	}

	/**
	 * Handle assessment form submission via AJAX.
	 *
	 * This method provides security checks, data sanitization, validation,
	 * and saves the assessment data to the database.
	 */
	public function handle_assessment_submission() {
		error_log( 'ENNU REDIRECT DEBUG: handle_assessment_submission() method called' );
		
		// IMMEDIATE DATABASE OPTIMIZATION - CRITICAL FIX FOR SLOW QUERIES
		global $wpdb;
		
		error_log( 'ENNU REDIRECT DEBUG: Starting database optimization' );
		
		// Force cleanup of expired transients immediately
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' AND option_value < " . time() );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_%' AND option_value < " . time() );
		
		error_log( 'ENNU REDIRECT DEBUG: Database optimization completed' );
		
		$this->_log_submission_debug( '--- Submission process started with immediate database optimization ---' );

		// AGGRESSIVE DATABASE OPTIMIZATION - CRITICAL FIX FOR SLOW QUERIES
		// Skip database optimization during AJAX to prevent timeouts
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			if ( class_exists( 'ENNU_Database_Optimizer' ) ) {
				$db_optimizer = new ENNU_Database_Optimizer();
				$db_optimizer->cleanup_expired_transients();
				$db_optimizer->optimize_database_tables();
				$this->_log_submission_debug( 'Database optimization completed to prevent slow queries.' );
			}
		} else {
			error_log( 'ENNU REDIRECT DEBUG: Skipping database optimization during AJAX to prevent timeouts' );
			$this->_log_submission_debug( 'Database optimization skipped during AJAX to prevent timeouts' );
		}

		$security_result = ENNU_AJAX_Security::validate_ajax_request( 'ennu_submit_assessment' );

		if ( is_wp_error( $security_result ) ) {
			wp_send_json_error(
				array(
					'message' => $security_result->get_error_message(),
					'code'    => $security_result->get_error_code(),
				)
			);
		}
		$this->_log_submission_debug( 'Verifying nonce...' );
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );
		$this->_log_submission_debug( 'Nonce verified successfully.' );

		$security_validator = ENNU_Security_Validator::get_instance();
		if ( ! $security_validator->rate_limit_check( 'assessment_submission', 5, 300 ) ) {
			wp_send_json_error( array( 'message' => 'Too many submission attempts. Please wait before trying again.' ), 429 );
			return;
		}

		// Future-proofing: Capture user IP for potential rate-limiting or security audits.
		$user_ip = $_SERVER['REMOTE_ADDR'];
		$this->_log_submission_debug( 'User IP captured.', $user_ip );
		$this->_log_submission_debug( 'Raw POST data:', $_POST );

		// 2. Get and Sanitize Data with enhanced security
		$input_sanitizer = ENNU_Input_Sanitizer::get_instance();
		$form_data       = $input_sanitizer->sanitize_form_data( $_POST, 'assessment' );
		$this->_log_submission_debug( 'Sanitized form data:', $form_data );

		// 3. Validate Data
		$validation_result = $this->validate_assessment_data( $form_data );
		if ( is_wp_error( $validation_result ) ) {
			$this->_log_submission_debug( 'Validation failed.', $validation_result->get_error_message() );
			wp_send_json_error(
				array(
					'message' => $validation_result->get_error_message(),
					'code'    => $validation_result->get_error_code(),
				),
				400
			);
			return;
		}
		$this->_log_submission_debug( 'Validation passed.' );

		// 4. Determine User ID (Create user if doesn't exist)
		$email   = $form_data['email'];
		$user_id = email_exists( $email );
		$this->_log_submission_debug( 'Checking for existing user with email.', $email );

		if ( ! $user_id ) {
			$this->_log_submission_debug( 'User does not exist. Creating new user.' );
			// User does not exist, create a new one with all available details
			$password  = wp_generate_password();
			$user_data = array(
				'user_login' => $email,
				'user_email' => $email,
				'user_pass'  => $password,
				'first_name' => $form_data['first_name'] ?? '',
				'last_name'  => $form_data['last_name'] ?? '',
			);
			$user_id   = wp_insert_user( $user_data );

			if ( is_wp_error( $user_id ) ) {
				$this->_log_submission_debug( 'Failed to create new user.', $user_id->get_error_message() );
				wp_send_json_error( array( 'message' => 'Could not create a new user account: ' . $user_id->get_error_message() ), 500 );
				return;
			}
			$this->_log_submission_debug( 'New user created successfully.', array( 'user_id' => $user_id ) );
			// Log the new user in
			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id );
		} else {
			$this->_log_submission_debug( 'User exists.', array( 'user_id' => $user_id ) );
			// User already exists, check if they are logged in
			if ( ! is_user_logged_in() || get_current_user_id() != $user_id ) {
				$this->_log_submission_debug( 'User exists but is not logged in. Sending login required error.' );
				$login_url = wp_login_url( get_permalink() );
				wp_send_json_error(
					array(
						'message' => 'An account with this email already exists. Please <a href="' . esc_url( $login_url ) . '">log in</a> to continue.',
						'action'  => 'login_required',
					),
					409
				);
				return;
			}
		}
		$this->_log_submission_debug( 'User ID determined.', $user_id );

		// 5. Calculate and save BMI if applicable
		if ( isset( $form_data['height_ft'] ) && isset( $form_data['height_in'] ) && isset( $form_data['weight_lbs'] ) ) {
			$this->_log_submission_debug( 'Calculating BMI.' );
			$height_in_total = ( intval( $form_data['height_ft'] ) * 12 ) + intval( $form_data['height_in'] );
			$weight_lbs      = intval( $form_data['weight_lbs'] );
			if ( $height_in_total > 0 && $weight_lbs > 0 ) {
				$bmi = ( $weight_lbs / ( $height_in_total * $height_in_total ) ) * 703;
				$this->_log_submission_debug( 'BMI calculated.', $bmi );
				update_user_meta( $user_id, 'ennu_calculated_bmi', round( $bmi, 1 ) );

				// --- v57.0.9: Store Historical BMI ---
				$bmi_history = get_user_meta( $user_id, 'ennu_bmi_history', true );
				if ( ! is_array( $bmi_history ) ) {
					$bmi_history = array();
				}
				$bmi_history[] = array(
					'date' => date( 'Y-m-d H:i:s.u' ),
					'bmi'  => round( $bmi, 1 ),
				);
				update_user_meta( $user_id, 'ennu_bmi_history', $bmi_history );
				$this->_log_submission_debug( 'BMI history updated.' );
				// --- END ---
			}
		}

		// 6. UNIFIED DATA SAVING SYSTEM - CRITICAL FIX
		$this->_log_submission_debug( 'Starting unified data saving system...' );
		$save_result = $this->unified_save_assessment_data( $user_id, $form_data );
		
		if ( is_wp_error( $save_result ) ) {
			$this->_log_submission_debug( 'Data saving failed.', $save_result->get_error_message() );
			wp_send_json_error( array( 'message' => 'Failed to save assessment data: ' . $save_result->get_error_message() ), 500 );
			return;
		}
		$this->_log_submission_debug( 'Unified data saving completed successfully.' );

		// 8. ROUTE TO THE CORRECT ENGINE
		// Convert assessment type to match config file naming convention
		$config_assessment_type = $form_data['assessment_type'];
		if ( $form_data['assessment_type'] === 'health_optimization_assessment' ) {
			$config_assessment_type = 'health-optimization';
		}
		
		$assessment_config = $this->all_definitions[ $config_assessment_type ] ?? array();
		$this->_log_submission_debug( 'Routing to assessment engine.', $assessment_config['assessment_engine'] ?? 'quantitative' );
		if ( isset( $assessment_config['assessment_engine'] ) && $assessment_config['assessment_engine'] === 'qualitative' ) {

			$this->_log_submission_debug( 'Processing with Qualitative engine.' );
			// For qualitative, we store the raw form data to generate the report
			$results_token = wp_generate_password( 32, false );
			$this->_set_manual_transient( 'ennu_results_' . $results_token, $form_data, HOUR_IN_SECONDS );

			try {
				$this->_log_submission_debug( 'Preparing email data for qualitative assessment...' );
				// Prepare a dedicated data array for the email templates
				$email_data = array(
					'assessment_type' => $form_data['assessment_type'],
					'contact_name'    => trim( ( $form_data['first_name'] ?? '' ) . ' ' . ( $form_data['last_name'] ?? '' ) ),
					'contact_email'   => $form_data['email'],
					'contact_phone'   => $form_data['billing_phone'] ?? 'N/A',
					'answers'         => array(),
				);

				// Extract only the question/answer pairs for the email body from the original form data
				foreach ( $form_data as $key => $value ) {
					// A simple check to see if the key represents a question
					if ( strpos( $key, '_q' ) !== false ) {
						$email_data['answers'][ $key ] = is_array( $value ) ? implode( ', ', $value ) : $value;
					}
				}
				$this->_log_submission_debug( 'Email data prepared.', $email_data );

				$this->_log_submission_debug( 'Sending notification email...' );
				$this->send_assessment_notification( $email_data );
				$this->_log_submission_debug( 'Notification email sent.' );
			} catch ( Throwable $e ) { // Use Throwable to catch all errors and exceptions
				$this->_log_submission_debug( 'Email notification failed.', $e->getMessage() );
				// Log the error, but don't crash the submission process
				error_log( 'ENNU Assessments - Email Notification Failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() );
			}

			// Update centralized symptoms for qualitative assessments too
			if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
				ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $user_id, $form_data['assessment_type'] );
				$this->_log_submission_debug( 'Centralized symptoms updated for qualitative assessment.' );

				// Trigger assessment completion hook for other systems
				do_action( 'ennu_assessment_completed', $user_id, $form_data['assessment_type'] );
				
				// Trigger HubSpot real-time sync
				$this->trigger_hubspot_sync( $user_id, $form_data['assessment_type'], $form_data );
			}

			$redirect_url = $this->get_thank_you_url( 'health_optimization_assessment', $results_token );
			$this->_log_submission_debug( 'Qualitative flow complete. Sending redirect URL.', $redirect_url );

			// Include auth state data in response for frontend
			$auth_state_data = $this->get_current_auth_state_data();

			wp_send_json_success(
				array(
					'redirect_url' => $redirect_url,
					'auth_state'   => $auth_state_data,
				)
			);

		} else {
			// --- NEW: Quantitative Engine Flow using the new Orchestrator ---
			$this->_log_submission_debug( 'Processing with NEW Quantitative engine.' );

			$scores = ENNU_Scoring_System::calculate_scores_for_assessment( $form_data['assessment_type'], $form_data );
			$this->_log_submission_debug( 'Initial scores calculated.', $scores );
			$this->_log_submission_debug( 'Pillar scores from calculation:', $scores['pillar_scores'] ?? 'NOT FOUND' );

			if ( $scores ) {
				$completion_time = date( 'Y-m-d H:i:s.u' );

				// Save the scores for this specific assessment
				update_user_meta( $user_id, 'ennu_' . $form_data['assessment_type'] . '_score_calculated_at', $completion_time );
				$canonical_assessment_type = ENNU_Assessment_Constants::get_canonical_key( $form_data['assessment_type'] );
			update_user_meta( $user_id, ENNU_Assessment_Constants::get_full_meta_key( $canonical_assessment_type, 'calculated_score' ), $scores['overall_score'] );
				update_user_meta( $user_id, 'ennu_' . $form_data['assessment_type'] . '_score_interpretation', ENNU_Scoring_System::get_score_interpretation( $scores['overall_score'] ) );
				update_user_meta( $user_id, 'ennu_' . $form_data['assessment_type'] . '_category_scores', $scores['category_scores'] );
				update_user_meta( $user_id, 'ennu_' . $form_data['assessment_type'] . '_pillar_scores', $scores['pillar_scores'] );

				// --- DOSSIER CHART FIX: Save score history for progress tracking ---
				$score_history_key = 'ennu_' . $form_data['assessment_type'] . '_historical_scores';
				$score_history = get_user_meta( $user_id, $score_history_key, true );
				if ( ! is_array( $score_history ) ) {
					$score_history = array();
				}
				
				// Add current score to history with timestamp
				$score_history[] = array(
					'date'  => $completion_time,
					'score' => $scores['overall_score'],
				);
				
				// Keep only last 50 entries to prevent database bloat
				if ( count( $score_history ) > 50 ) {
					$score_history = array_slice( $score_history, -50 );
				}
				
				update_user_meta( $user_id, $score_history_key, $score_history );
				$this->_log_submission_debug( 'Score history saved for dossier chart.', array( 'entries' => count( $score_history ) ) );
				// --- END DOSSIER CHART FIX ---

				// Now, calculate and save all the master user scores
				ENNU_Scoring_System::calculate_and_save_all_user_scores( $user_id );
				$this->_log_submission_debug( 'All master user scores calculated and saved.' );

				// Update centralized symptoms
				if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
					ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $user_id, $form_data['assessment_type'] );
					$this->_log_submission_debug( 'Centralized symptoms updated.' );
				}

				// Trigger assessment completion hook for symptom extraction
				do_action( 'ennu_assessment_completed', $user_id, $form_data['assessment_type'] );
				
				// Trigger HubSpot real-time sync
				$this->trigger_hubspot_sync( $user_id, $form_data['assessment_type'], $form_data );
				$results_token = $this->store_results_transient( $user_id, $form_data['assessment_type'], $scores, $form_data );
				$this->_log_submission_debug( 'Results transient stored.', $results_token );
				$this->_log_submission_debug( 'Scores being stored in transient:', $scores );

				// --- Definitive Fix: Prepare data and gracefully handle email errors ---
				try {
					$this->_log_submission_debug( 'Preparing email data for quantitative assessment...' );
					// Prepare a dedicated data array for the email templates
					$email_data = array(
						'assessment_type' => $form_data['assessment_type'],
						'contact_name'    => trim( ( $form_data['first_name'] ?? '' ) . ' ' . ( $form_data['last_name'] ?? '' ) ),
						'contact_email'   => $form_data['email'],
						'contact_phone'   => $form_data['billing_phone'] ?? 'N/A',
						'answers'         => array(),
					);

					// Extract only the question/answer pairs for the email body from the original form data
					foreach ( $form_data as $key => $value ) {
						// A simple check to see if the key represents a question
						if ( strpos( $key, '_q' ) !== false ) {
							$email_data['answers'][ $key ] = is_array( $value ) ? implode( ', ', $value ) : $value;
						}
					}
					$this->_log_submission_debug( 'Email data prepared.', $email_data );

					$this->_log_submission_debug( 'Sending notification email...' );
					$this->send_assessment_notification( $email_data );
					$this->_log_submission_debug( 'Notification email sent.' );
				} catch ( Throwable $e ) { // Use Throwable to catch all errors and exceptions
					$this->_log_submission_debug( 'Email notification failed.', $e->getMessage() );
					// Log the error, but don't crash the submission process
					error_log( 'ENNU Assessments - Email Notification Failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() );
				}
			}
			error_log( 'ENNU REDIRECT DEBUG: About to call get_thank_you_url with assessment_type: ' . $form_data['assessment_type'] );
			
					// Special redirect for welcome assessment ONLY
		if ( $form_data['assessment_type'] === 'welcome' ) {
			$redirect_url = $this->get_thank_you_url( 'welcome', $results_token ?? null );
			error_log( 'ENNU REDIRECT DEBUG: Welcome assessment - using configured results page' );
			
			// Validate redirect URL
			if ( ! $redirect_url ) {
				error_log( 'ENNU REDIRECT DEBUG: No welcome assessment results page configured - redirecting to /signup' );
				$redirect_url = home_url( '/signup' );
			}
		} else {
			$redirect_url = $this->get_thank_you_url( $form_data['assessment_type'], $results_token ?? null );
			error_log( 'ENNU REDIRECT DEBUG: get_thank_you_url returned: ' . $redirect_url );
			
			// Validate redirect URL
			if ( ! $redirect_url ) {
				error_log( 'ENNU REDIRECT DEBUG: No redirect URL generated - admin must configure results page' );
				wp_send_json_error( array(
					'message' => 'Assessment submitted successfully, but no results page is configured. Please contact support.',
					'redirect_url' => false
				) );
				return;
			}
		}
			$this->_log_submission_debug( 'Quantitative flow complete. Sending redirect URL.', $redirect_url );

			// Include auth state data in response for frontend
			$auth_state_data = $this->get_current_auth_state_data();

			wp_send_json_success(
				array(
					'redirect_url' => $redirect_url,
					'auth_state'   => $auth_state_data,
				)
			);
		}
		$this->_log_submission_debug( '--- Submission process finished ---' );
	}

	/**
	 * UNIFIED DATA SAVING SYSTEM - CRITICAL FIX
	 * This method replaces all previous data saving methods with a single, robust system
	 * that ensures 100% data persistence with proper error handling and validation.
	 *
	 * @param int $user_id The user ID
	 * @param array $form_data The sanitized form data
	 * @return true|WP_Error True on success, WP_Error on failure
	 */
	private function unified_save_assessment_data( $user_id, $form_data ) {
		$this->_log_submission_debug( 'Starting unified data saving for user ' . $user_id );
		
		// Initialize database optimizer for performance
		if ( $this->db_optimizer ) {
			$this->db_optimizer->cleanup_expired_transients();
		}
		
		try {
			$assessment_type = $form_data['assessment_type'];
			$saved_fields = array();
			$errors = array();

			// 1. SAVE CORE USER DATA (Name, Email, Phone)
			$this->_log_submission_debug( 'Saving core user data...' );
			$core_result = $this->save_core_user_data( $user_id, $form_data );
			if ( is_wp_error( $core_result ) ) {
				$errors[] = 'Core data: ' . $core_result->get_error_message();
			} else {
				$saved_fields = array_merge( $saved_fields, $core_result );
			}

					// 2. SAVE GLOBAL FIELDS (DOB, Height/Weight, etc.) - ENHANCED
		$this->_log_submission_debug( 'Saving enhanced global fields...' );
		$global_result = $this->save_global_fields_enhanced( $user_id, $form_data );
		if ( is_wp_error( $global_result ) ) {
			$errors[] = 'Global fields: ' . $global_result->get_error_message();
		} else {
			$saved_fields = array_merge( $saved_fields, $global_result );
		}

		// 2.5. PROCESS GLOBAL FIELDS VIA GLOBAL FIELDS PROCESSOR
		$this->_log_submission_debug( 'Processing global fields via global fields processor...' );
		if ( class_exists( 'ENNU_Global_Fields_Processor' ) ) {
			ENNU_Global_Fields_Processor::process_form_data( $user_id, $form_data );
		}

			// 3. SAVE ASSESSMENT-SPECIFIC FIELDS - ENHANCED
			$this->_log_submission_debug( 'Saving enhanced assessment-specific fields...' );
			$assessment_result = $this->save_assessment_specific_fields_enhanced( $user_id, $form_data );
			if ( is_wp_error( $assessment_result ) ) {
				$errors[] = 'Assessment fields: ' . $assessment_result->get_error_message();
			} else {
				$saved_fields = array_merge( $saved_fields, $assessment_result );
			}

			// 4. SAVE COMPLETION TIMESTAMP
			$completion_time = current_time( 'mysql' );
			update_user_meta( $user_id, 'ennu_' . $assessment_type . '_completed', $completion_time );
			update_user_meta( $user_id, 'ennu_' . $assessment_type . '_last_updated', $completion_time );
			$saved_fields[] = 'completion_timestamp';

			// 5. VERIFY DATA INTEGRITY
			$this->_log_submission_debug( 'Verifying data integrity...' );
			$verification_result = $this->verify_data_integrity( $user_id, $assessment_type, $saved_fields );
			if ( is_wp_error( $verification_result ) ) {
				$errors[] = 'Data verification: ' . $verification_result->get_error_message();
			}

			// 6. LOG SUCCESS
			$this->_log_submission_debug( 'Unified data saving completed. Fields saved: ' . count( $saved_fields ) );
			error_log( "ENNU: Unified data saving completed for user {$user_id}, assessment {$assessment_type}. Fields saved: " . implode( ', ', $saved_fields ) );

			// Return error if any critical failures occurred
			if ( ! empty( $errors ) ) {
				return new WP_Error( 'data_save_failed', 'Data saving errors: ' . implode( '; ', $errors ) );
			}

			return true;

		} catch ( Exception $e ) {
			$this->_log_submission_debug( 'Unified data saving failed with exception: ' . $e->getMessage() );
			error_log( "ENNU ERROR: Unified data saving failed for user {$user_id}: " . $e->getMessage() );
			return new WP_Error( 'data_save_exception', 'Data saving failed: ' . $e->getMessage() );
		}
	}

	/**
	 * Save core user data (name, email, phone)
	 */
	private function save_core_user_data( $user_id, $form_data ) {
		$saved_fields = array();

		// Update WordPress user fields
		$user_data_update = array( 'ID' => $user_id );
		if ( isset( $form_data['first_name'] ) ) {
			$user_data_update['first_name'] = sanitize_text_field( $form_data['first_name'] );
			$saved_fields[] = 'first_name';
		}
		if ( isset( $form_data['last_name'] ) ) {
			$user_data_update['last_name'] = sanitize_text_field( $form_data['last_name'] );
			$saved_fields[] = 'last_name';
		}
		if ( count( $user_data_update ) > 1 ) {
			$result = wp_update_user( $user_data_update );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		// Save to user meta for consistency - OPTIMIZED with batch operations
		$meta_to_update = array();
		if ( isset( $form_data['first_name'] ) ) {
			$meta_to_update['ennu_global_first_name'] = sanitize_text_field( $form_data['first_name'] );
		}
		if ( isset( $form_data['last_name'] ) ) {
			$meta_to_update['ennu_global_last_name'] = sanitize_text_field( $form_data['last_name'] );
		}
		if ( isset( $form_data['email'] ) ) {
			$meta_to_update['ennu_global_email'] = sanitize_email( $form_data['email'] );
			$saved_fields[] = 'email';
		}
		
		// Batch update user meta for better performance
		if ( ! empty( $meta_to_update ) ) {
			foreach ( $meta_to_update as $meta_key => $meta_value ) {
				update_user_meta( $user_id, $meta_key, $meta_value );
			}
		}
		if ( isset( $form_data['billing_phone'] ) ) {
			update_user_meta( $user_id, 'ennu_global_billing_phone', sanitize_text_field( $form_data['billing_phone'] ) );
			$saved_fields[] = 'phone';
		}

		// Sync with WooCommerce if available
		if ( class_exists( 'WooCommerce' ) ) {
			if ( isset( $form_data['first_name'] ) ) {
				update_user_meta( $user_id, 'billing_first_name', sanitize_text_field( $form_data['first_name'] ) );
				update_user_meta( $user_id, 'shipping_first_name', sanitize_text_field( $form_data['first_name'] ) );
			}
			if ( isset( $form_data['last_name'] ) ) {
				update_user_meta( $user_id, 'billing_last_name', sanitize_text_field( $form_data['last_name'] ) );
				update_user_meta( $user_id, 'shipping_last_name', sanitize_text_field( $form_data['last_name'] ) );
			}
			if ( isset( $form_data['email'] ) ) {
				update_user_meta( $user_id, 'billing_email', sanitize_email( $form_data['email'] ) );
			}
			if ( isset( $form_data['billing_phone'] ) ) {
				update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $form_data['billing_phone'] ) );
			}
		}

		return $saved_fields;
	}

	/**
	 * Save global fields with unified logic
	 */
	private function save_global_fields_unified( $user_id, $form_data ) {
		$saved_fields = array();
		$assessment_type = $form_data['assessment_type'];
		$questions = $this->get_assessment_questions( $assessment_type );

		foreach ( $questions as $question_id => $question_def ) {
			if ( ! isset( $question_def['global_key'] ) ) {
				continue;
			}

			$global_key = $question_def['global_key'];
			$meta_key = 'ennu_global_' . $global_key;
			$value_to_save = null;

			switch ( $question_def['type'] ) {
				case 'dob_dropdowns':
					// Handle DOB fields
					if ( isset( $form_data['ennu_global_date_of_birth'] ) && ! empty( $form_data['ennu_global_date_of_birth'] ) ) {
						$value_to_save = sanitize_text_field( $form_data['ennu_global_date_of_birth'] );
					} elseif ( isset( $form_data['ennu_global_date_of_birth_month'], $form_data['ennu_global_date_of_birth_day'], $form_data['ennu_global_date_of_birth_year'] ) ) {
						$value_to_save = $form_data['ennu_global_date_of_birth_year'] . '-' . 
										str_pad( $form_data['ennu_global_date_of_birth_month'], 2, '0', STR_PAD_LEFT ) . '-' . 
										str_pad( $form_data['ennu_global_date_of_birth_day'], 2, '0', STR_PAD_LEFT );
					}
					break;

				case 'height_weight':
					// Handle height/weight fields
					if ( isset( $form_data['height_ft'], $form_data['height_in'], $form_data['weight_lbs'] ) ) {
						$value_to_save = array(
							'ft'  => sanitize_text_field( $form_data['height_ft'] ),
							'in'  => sanitize_text_field( $form_data['height_in'] ),
							'lbs' => sanitize_text_field( $form_data['weight_lbs'] ),
						);
					}
					break;

				case 'multiselect':
					// Handle multiselect fields
					if ( isset( $form_data[ $question_id ] ) ) {
						$value_to_save = is_array( $form_data[ $question_id ] ) ? $form_data[ $question_id ] : array( $form_data[ $question_id ] );
					} else {
						$value_to_save = array(); // Empty array for unanswered multiselect
					}
					break;

				default:
					// Handle standard fields
					if ( isset( $form_data[ $question_id ] ) ) {
						$value_to_save = $form_data[ $question_id ];
					}
					break;
			}

			// Save the field if we have a value
			if ( $value_to_save !== null ) {
				$result = update_user_meta( $user_id, $meta_key, $value_to_save );
				if ( $result !== false ) {
					$saved_fields[] = $global_key;
					$this->_log_submission_debug( "Saved global field: {$meta_key}" );
				} else {
					$this->_log_submission_debug( "Failed to save global field: {$meta_key}" );
				}
			}
		}

		return $saved_fields;
	}

	/**
	 * Save assessment-specific fields with unified logic
	 */
	private function save_assessment_specific_fields_unified( $user_id, $form_data ) {
		$saved_fields = array();
		$assessment_type = $form_data['assessment_type'];
		$assessment_config = $this->get_assessment_questions( $assessment_type );
		
		// Get the questions array from the assessment config
		$questions = isset( $assessment_config['questions'] ) ? $assessment_config['questions'] : array();
		
		$this->_log_submission_debug( "Processing assessment-specific fields for {$assessment_type}. Questions found: " . count( $questions ) );

		foreach ( $questions as $question_id => $question_def ) {
			// Skip global fields (handled separately)
			if ( isset( $question_def['global_key'] ) ) {
				$this->_log_submission_debug( "Skipping global field: {$question_id}" );
				continue;
			}

			$meta_key = 'ennu_' . $assessment_type . '_' . $question_id;
			$value_to_save = null;

			// Handle different field types
			if ( isset( $form_data[ $question_id ] ) ) {
				$value_to_save = $form_data[ $question_id ];
				$this->_log_submission_debug( "Found form data for {$question_id}: " . ( is_array( $value_to_save ) ? json_encode( $value_to_save ) : $value_to_save ) );
			} elseif ( isset( $question_def['type'] ) && $question_def['type'] === 'multiselect' ) {
				// Empty array for unanswered multiselect questions
				$value_to_save = array();
				$this->_log_submission_debug( "Setting empty array for multiselect field: {$question_id}" );
			}

			// Save the field if we have a value or it's an empty multiselect
			if ( $value_to_save !== null ) {
				$result = update_user_meta( $user_id, $meta_key, $value_to_save );
				if ( $result !== false ) {
					$saved_fields[] = $question_id;
					$this->_log_submission_debug( "Saved assessment field: {$meta_key}" );
				} else {
					$this->_log_submission_debug( "Failed to save assessment field: {$meta_key}" );
				}
			} else {
				$this->_log_submission_debug( "No value to save for field: {$question_id}" );
			}
		}

		$this->_log_submission_debug( "Assessment-specific fields saved: " . count( $saved_fields ) );
		return $saved_fields;
	}

	/**
	 * Enhanced form data normalization to fix field name mismatches
	 */
	private function normalize_form_data( $form_data ) {
		$normalized_data = array();
		
		foreach ( $form_data as $key => $value ) {
			// Remove array notation from multiselect fields
			$normalized_key = str_replace( '[]', '', $key );
			$normalized_data[ $normalized_key ] = $value;
		}
		
		return $normalized_data;
	}

	/**
	 * Enhanced assessment-specific field saving with better field name handling
	 */
	private function save_assessment_specific_fields_enhanced( $user_id, $form_data ) {
		$saved_fields = array();
		$assessment_type = $form_data['assessment_type'];
		$questions = $this->get_assessment_questions( $assessment_type );
		
		// Normalize form data to handle field name mismatches
		$normalized_data = $this->normalize_form_data( $form_data );
		
		$this->_log_submission_debug( "Processing enhanced assessment-specific fields for {$assessment_type}. Questions found: " . count( $questions ) );

		foreach ( $questions as $question_id => $question_def ) {
			// Skip global fields (handled separately)
			if ( isset( $question_def['global_key'] ) ) {
				$this->_log_submission_debug( "Skipping global field: {$question_id}" );
				continue;
			}

			$meta_key = 'ennu_' . $assessment_type . '_' . $question_id;
			$value_to_save = null;

			// Enhanced field value extraction
			if ( isset( $normalized_data[ $question_id ] ) ) {
				$value_to_save = $normalized_data[ $question_id ];
				$this->_log_submission_debug( "Found normalized form data for {$question_id}: " . ( is_array( $value_to_save ) ? json_encode( $value_to_save ) : $value_to_save ) );
			} elseif ( isset( $form_data[ $question_id ] ) ) {
				// Fallback to original form data
				$value_to_save = $form_data[ $question_id ];
				$this->_log_submission_debug( "Found original form data for {$question_id}: " . ( is_array( $value_to_save ) ? json_encode( $value_to_save ) : $value_to_save ) );
			} elseif ( isset( $form_data[ $question_id . '[]' ] ) ) {
				// Handle array notation fields
				$value_to_save = $form_data[ $question_id . '[]' ];
				$this->_log_submission_debug( "Found array notation form data for {$question_id}: " . ( is_array( $value_to_save ) ? json_encode( $value_to_save ) : $value_to_save ) );
			} elseif ( isset( $question_def['type'] ) && $question_def['type'] === 'multiselect' ) {
				// Empty array for unanswered multiselect questions
				$value_to_save = array();
				$this->_log_submission_debug( "Setting empty array for multiselect field: {$question_id}" );
			}

			// Save the field if we have a value or it's an empty multiselect
			if ( $value_to_save !== null ) {
				$result = update_user_meta( $user_id, $meta_key, $value_to_save );
				if ( $result !== false ) {
					$saved_fields[] = $question_id;
					$this->_log_submission_debug( "Saved enhanced assessment field: {$meta_key}" );
				} else {
					$this->_log_submission_debug( "Failed to save enhanced assessment field: {$meta_key}" );
				}
			} else {
				$this->_log_submission_debug( "No value to save for field: {$question_id}" );
			}
		}

		$this->_log_submission_debug( "Enhanced assessment-specific fields saved: " . count( $saved_fields ) );
		return $saved_fields;
	}

	/**
	 * Enhanced global field saving with better field name handling
	 */
	private function save_global_fields_enhanced( $user_id, $form_data ) {
		$saved_fields = array();
		$assessment_type = $form_data['assessment_type'];
		$questions = $this->get_assessment_questions( $assessment_type );

		// Normalize form data
		$normalized_data = $this->normalize_form_data( $form_data );

		// OPTIMIZED: Collect all meta updates for batch processing
		$meta_updates = array();
		
		foreach ( $questions as $question_id => $question_def ) {
			if ( ! isset( $question_def['global_key'] ) ) {
				continue;
			}

			$global_key = $question_def['global_key'];
			$meta_key = 'ennu_global_' . $global_key;
			$value_to_save = null;

			switch ( $question_def['type'] ) {
				case 'dob_dropdowns':
					// Enhanced DOB field handling
					if ( isset( $normalized_data['ennu_global_date_of_birth'] ) && ! empty( $normalized_data['ennu_global_date_of_birth'] ) ) {
						$value_to_save = sanitize_text_field( $normalized_data['ennu_global_date_of_birth'] );
					} elseif ( isset( $form_data['ennu_global_date_of_birth'] ) && ! empty( $form_data['ennu_global_date_of_birth'] ) ) {
						$value_to_save = sanitize_text_field( $form_data['ennu_global_date_of_birth'] );
					} elseif ( isset( $normalized_data['ennu_global_date_of_birth_month'], $normalized_data['ennu_global_date_of_birth_day'], $normalized_data['ennu_global_date_of_birth_year'] ) ) {
						$value_to_save = $normalized_data['ennu_global_date_of_birth_year'] . '-' . 
										str_pad( $normalized_data['ennu_global_date_of_birth_month'], 2, '0', STR_PAD_LEFT ) . '-' . 
										str_pad( $normalized_data['ennu_global_date_of_birth_day'], 2, '0', STR_PAD_LEFT );
					} elseif ( isset( $form_data['ennu_global_date_of_birth_month'], $form_data['ennu_global_date_of_birth_day'], $form_data['ennu_global_date_of_birth_year'] ) ) {
						$value_to_save = $form_data['ennu_global_date_of_birth_year'] . '-' . 
										str_pad( $form_data['ennu_global_date_of_birth_month'], 2, '0', STR_PAD_LEFT ) . '-' . 
										str_pad( $form_data['ennu_global_date_of_birth_day'], 2, '0', STR_PAD_LEFT );
					}
					break;

				case 'height_weight':
					// Enhanced height/weight field handling
					if ( isset( $normalized_data['height_ft'], $normalized_data['height_in'], $normalized_data['weight_lbs'] ) ) {
						$value_to_save = array(
							'ft'  => sanitize_text_field( $normalized_data['height_ft'] ),
							'in'  => sanitize_text_field( $normalized_data['height_in'] ),
							'lbs' => sanitize_text_field( $normalized_data['weight_lbs'] ),
						);
					} elseif ( isset( $form_data['height_ft'], $form_data['height_in'], $form_data['weight_lbs'] ) ) {
						$value_to_save = array(
							'ft'  => sanitize_text_field( $form_data['height_ft'] ),
							'in'  => sanitize_text_field( $form_data['height_in'] ),
							'lbs' => sanitize_text_field( $form_data['weight_lbs'] ),
						);
					}
					break;

				case 'multiselect':
					// Enhanced multiselect field handling
					if ( isset( $normalized_data[ $question_id ] ) ) {
						$value_to_save = is_array( $normalized_data[ $question_id ] ) ? $normalized_data[ $question_id ] : array( $normalized_data[ $question_id ] );
					} elseif ( isset( $form_data[ $question_id ] ) ) {
						$value_to_save = is_array( $form_data[ $question_id ] ) ? $form_data[ $question_id ] : array( $form_data[ $question_id ] );
					} elseif ( isset( $form_data[ $question_id . '[]' ] ) ) {
						$value_to_save = is_array( $form_data[ $question_id . '[]' ] ) ? $form_data[ $question_id . '[]' ] : array( $form_data[ $question_id . '[]' ] );
					} else {
						$value_to_save = array(); // Empty array for unanswered multiselect
					}
					break;

				default:
					// Enhanced standard field handling
					if ( isset( $normalized_data[ $question_id ] ) ) {
						$value_to_save = $normalized_data[ $question_id ];
					} elseif ( isset( $form_data[ $question_id ] ) ) {
						$value_to_save = $form_data[ $question_id ];
					}
					break;
			}

			// Collect meta updates for batch processing
			if ( $value_to_save !== null ) {
				$meta_updates[$meta_key] = $value_to_save;
				$saved_fields[] = $global_key;
			}
		}

		// OPTIMIZED: Batch update all user meta for better performance
		if ( ! empty( $meta_updates ) ) {
			foreach ( $meta_updates as $meta_key => $meta_value ) {
				$result = update_user_meta( $user_id, $meta_key, $meta_value );
				if ( $result !== false ) {
					$this->_log_submission_debug( "Saved enhanced global field: {$meta_key}" );
				} else {
					$this->_log_submission_debug( "Failed to save enhanced global field: {$meta_key}" );
				}
			}
		}

		return $saved_fields;
	}

	/**
	 * Verify data integrity after saving
	 */
	private function verify_data_integrity( $user_id, $assessment_type, $saved_fields ) {
		// Check that at least some fields were saved
		if ( empty( $saved_fields ) ) {
			return new WP_Error( 'no_fields_saved', 'No fields were saved during the assessment submission.' );
		}

		// Verify completion timestamp exists
		$completion_time = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_completed', true );
		if ( empty( $completion_time ) ) {
			return new WP_Error( 'completion_timestamp_missing', 'Assessment completion timestamp is missing.' );
		}

		$this->_log_submission_debug( "Data integrity verification passed. Fields saved: " . count( $saved_fields ) );
		return true;
	}

	/**
	 * Stores the results of a quantitative assessment in a temporary transient.
	 *
	 * @param int    $user_id         The user's ID.
	 * @param string $assessment_type The type of assessment.
	 * @param array  $scores          The calculated scores.
	 * @param array  $form_data       The submitted form data.
	 * @return string The generated results token.
	 */
	public function store_results_transient( $user_id, $assessment_type, $scores, $form_data ) {
		$token         = wp_generate_password( 32, false );
		$data_to_store = array(
			'user_id'         => $user_id,
			'assessment_type' => $assessment_type,
			'score'           => $scores['overall_score'],
			'interpretation'  => ENNU_Scoring_System::get_score_interpretation( $scores['overall_score'] ),
			'category_scores' => $scores['category_scores'],
			'pillar_scores'   => $scores['pillar_scores'] ?? array(), // Add pillar scores to transient
			'answers'         => $form_data,
		);
		// Use a manual transient to avoid issues with object caching on some hosts
		// Extended to 24 hours to give users more time to access results
		$this->_set_manual_transient( 'ennu_results_' . $token, $data_to_store, DAY_IN_SECONDS );
		return $token;
	}

	/**
	 * A private helper for detailed submission logging.
	 *
	 * @param string $message The debug message.
	 * @param mixed  $data    Optional data to include in the log.
	 */
	private function _log_submission_debug( $message, $data = null ) {
		if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
			return;
		}
		$log_message = '[ENNU Submission Debug] ' . $message;
		if ( null !== $data ) {
			$log_message .= ': ' . print_r( $data, true );
		}
		error_log( $log_message );
	}

	/**
	 * Manually sets a transient-like value in the options table to bypass object cache.
	 */
	private function _set_manual_transient( $key, $value, $expiration ) {
		update_option( '_ennu_manual_transient_' . $key, $value, false );
		update_option( '_ennu_manual_transient_timeout_' . $key, time() + $expiration, false );
	}

	/**
	 * Manually gets a transient-like value from the options table.
	 */
	private function _get_manual_transient( $key ) {
		$timeout = get_option( '_ennu_manual_transient_timeout_' . $key );
		if ( false === $timeout || $timeout < time() ) {
			$this->_delete_manual_transient( $key );
			return false;
		}
		return get_option( '_ennu_manual_transient_' . $key );
	}

	/**
	 * Manually deletes a transient-like value from the options table.
	 */
	private function _delete_manual_transient( $key ) {
		delete_option( '_ennu_manual_transient_' . $key );
		delete_option( '_ennu_manual_transient_timeout_' . $key );
	}

	/**
	 * AJAX handler to check if an email exists for a non-logged-in user.
	 */
	public function ajax_check_email_exists() {
		// Security check with rate limiting
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		$security_validator = ENNU_Security_Validator::get_instance();
		if ( ! $security_validator->rate_limit_check( 'email_check', 20, 300 ) ) {
			wp_send_json_error( array( 'message' => 'Too many requests. Please wait before trying again.' ), 429 );
			return;
		}

		$email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

		if ( empty( $email ) || ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => 'Invalid email provided.' ), 400 );
			return;
		}

		if ( email_exists( $email ) ) {
			// Email exists, prompt user to log in.
			$login_url = wp_login_url( get_permalink() );
			$message   = sprintf(
				__( 'An account with this email already exists. <a href="%s">Please log in</a> to continue.', 'ennulifeassessments' ),
				esc_url( $login_url )
			);
			wp_send_json_success(
				array(
					'exists'  => true,
					'message' => $message,
				)
			);
		} else {
			// Email does not exist.
			wp_send_json_success( array( 'exists' => false ) );
		}
	}

	/**
	 * AJAX handler to check current user authentication and contact info status.
	 * Used to dynamically update frontend state after account creation.
	 */
	public function ajax_check_auth_state() {
		// Security check with rate limiting
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		$security_validator = ENNU_Security_Validator::get_instance();
		if ( ! $security_validator->rate_limit_check( 'auth_check', 30, 300 ) ) {
			wp_send_json_error( array( 'message' => 'Too many requests. Please wait before trying again.' ), 429 );
			return;
		}

		$response = array(
			'is_logged_in'       => is_user_logged_in(),
			'needs_contact_form' => true,
			'auto_submit_ready'  => false,
			'user_data'          => array(),
		);

		if ( is_user_logged_in() ) {
			$user    = wp_get_current_user();
			$user_id = $user->ID;

			// Get current user data
			$current_user_data = array(
				'first_name'    => $user->first_name,
				'last_name'     => $user->last_name,
				'email'         => $user->user_email,
				'billing_phone' => get_user_meta( $user_id, 'ennu_global_billing_phone', true ),
			);

			// Check if user needs contact form
			$needs_contact_form = $this->user_needs_contact_form( $current_user_data );

			$response['needs_contact_form'] = $needs_contact_form;
			$response['auto_submit_ready']  = ! $needs_contact_form;
			$response['user_data']          = $current_user_data;
			$response['missing_fields']     = $needs_contact_form ? $this->get_missing_contact_fields( $current_user_data ) : array();
		}

		wp_send_json_success( $response );
	}

	/**
	 * Sanitize all incoming assessment data from the $_POST array.
	 *
	 * @param array $post_data The raw $_POST data.
	 * @return array The sanitized data.
	 */
	private function sanitize_assessment_data( $post_data ) {
		$sanitized_data = array();
		foreach ( $post_data as $key => $value ) {
			if ( is_array( $value ) ) {
				$sanitized_data[ sanitize_key( $key ) ] = array_map( 'sanitize_text_field', $value );
			} else {
				$sanitized_data[ sanitize_key( $key ) ] = sanitize_text_field( $value );
			}
		}

		// Ensure logged-in users have their contact info in the form data
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();

			// Add user data if missing (for cases where hidden fields might not have been submitted)
			if ( empty( $sanitized_data['email'] ) && $user->user_email ) {
				$sanitized_data['email'] = sanitize_email( $user->user_email );
			}
			if ( empty( $sanitized_data['first_name'] ) && $user->first_name ) {
				$sanitized_data['first_name'] = sanitize_text_field( $user->first_name );
			}
			if ( empty( $sanitized_data['last_name'] ) && $user->last_name ) {
				$sanitized_data['last_name'] = sanitize_text_field( $user->last_name );
			}
			if ( empty( $sanitized_data['billing_phone'] ) ) {
				$billing_phone = get_user_meta( $user->ID, 'ennu_global_billing_phone', true );
				if ( $billing_phone ) {
					$sanitized_data['billing_phone'] = sanitize_text_field( $billing_phone );
				}
			}
		}

		return $sanitized_data;
	}

	/**
	 * Validate the sanitized assessment data.
	 *
	 * @param array $data The sanitized data.
	 * @return true|WP_Error True if valid, WP_Error on failure.
	 */
	private function validate_assessment_data( $data ) {
		if ( empty( $data['assessment_type'] ) ) {
			return new WP_Error( 'validation_failed_assessment_type', 'Assessment type is missing.' );
		}
		if ( empty( $data['email'] ) || ! is_email( $data['email'] ) ) {
			return new WP_Error( 'validation_failed_email', 'A valid email address is required.' );
		}
		return true;
	}

	/**
	 * Save the assessment-specific data as user meta.
	 *
	 * @param int $user_id The ID of the user.
	 * @param array $data The sanitized assessment data.
	 */
	private function save_assessment_specific_meta( $user_id, $data ) {
		$assessment_type = $data['assessment_type'];
		$config          = $this->all_definitions[ $assessment_type ] ?? array();

		// The definitive fix: Check if the questions are nested.
		$questions = $config['questions'] ?? $config;

		foreach ( $questions as $question_id => $question_def ) {
			if ( ! is_array( $question_def ) || isset( $question_def['global_key'] ) ) {
				continue;
			}

			// Save the primary answer
			$meta_key = 'ennu_' . $assessment_type . '_' . $question_id;
			if ( isset( $data[ $question_id ] ) ) {
				update_user_meta( $user_id, $meta_key, $data[ $question_id ] );

				// Save qualifier data
				if ( isset( $question_def['qualifiers'] ) && is_array( $data[ $question_id ] ) ) {
					foreach ( $data[ $question_id ] as $symptom_value ) {
						if ( isset( $question_def['qualifiers'][ $symptom_value ] ) ) {
							foreach ( $question_def['qualifiers'][ $symptom_value ] as $qualifier ) {
								$qualifier_key = $qualifier['id'];
								if ( isset( $data[ $qualifier_key ] ) ) {
									$qualifier_meta_key = 'ennu_' . $assessment_type . '_' . $qualifier_key;
									update_user_meta( $user_id, $qualifier_meta_key, $data[ $qualifier_key ] );
								}
							}
						}
					}
				}
			} elseif ( isset( $question_def['type'] ) && $question_def['type'] === 'multiselect' ) {
				// Definitive fix for unanswered multiselect questions causing fatal errors.
				// If a multiselect question exists in the definition but not in the submitted
				// data, it means no options were checked. We must save an empty array.
				update_user_meta( $user_id, $meta_key, array() );
			}
		}
	}

	/**
	 * Saves all fields marked with a `global_key` to user meta.
	 * This is the new, robust handler that replaces the brittle hardcoded function.
	 *
	 * @param int $user_id The ID of the user.
	 * @param array $data The sanitized assessment data.
	 */
	public function save_global_meta( $user_id, $data ) {
		$assessment_type = $data['assessment_type'];
		$questions       = $this->get_assessment_questions( $assessment_type );

		error_log( "ENNU DEBUG: Saving global meta for user {$user_id}, assessment {$assessment_type}" );

		foreach ( $questions as $question_id => $question_def ) {
			if ( isset( $question_def['global_key'] ) ) {
				$meta_key = 'ennu_global_' . $question_def['global_key'];
				$value_to_save = null;

				// SINGLE UNIFIED METHOD - NO LEGACY CODE
				switch ( $question_def['type'] ) {
					case 'dob_dropdowns':
						// DOB: Combine month/day/year into YYYY-MM-DD format
						if ( isset( $data['ennu_global_date_of_birth'] ) && ! empty( $data['ennu_global_date_of_birth'] ) ) {
							$value_to_save = sanitize_text_field( $data['ennu_global_date_of_birth'] );
						} elseif ( isset( $data['ennu_global_date_of_birth_month'], $data['ennu_global_date_of_birth_day'], $data['ennu_global_date_of_birth_year'] ) ) {
							$value_to_save = $data['ennu_global_date_of_birth_year'] . '-' . $data['ennu_global_date_of_birth_month'] . '-' . $data['ennu_global_date_of_birth_day'];
						}
						break;

					case 'height_weight':
						// Height/Weight: Combine into array structure
						if ( isset( $data['height_ft'], $data['height_in'], $data['weight_lbs'] ) ) {
							$value_to_save = array(
								'ft'  => sanitize_text_field( $data['height_ft'] ),
								'in'  => sanitize_text_field( $data['height_in'] ),
								'lbs' => sanitize_text_field( $data['weight_lbs'] ),
							);
						}
						break;

					default:
						// Standard fields: Use question_id directly
						if ( isset( $data[ $question_id ] ) ) {
							$value_to_save = $data[ $question_id ];
						}
						break;
				}

				// Save the data if a value was found
				if ( $value_to_save !== null ) {
					$result = update_user_meta( $user_id, $meta_key, $value_to_save );
					error_log( "ENNU DEBUG: Saved global field {$meta_key} - result: " . ( $result ? 'success' : 'failed' ) );
					
					// Update age data if this is a DOB field
					if ( $meta_key === 'ennu_global_date_of_birth' && ! empty( $value_to_save ) ) {
						$age_data = ENNU_Age_Management_System::update_user_age_data( $user_id, $value_to_save );
					}
				}
			}
		}
	}

	/**
	 * Syncs core data (name, email, phone) to native WP and WooCommerce fields.
	 *
	 * @param int $user_id The ID of the user.
	 * @param array $data The sanitized assessment data.
	 */
	private function sync_core_data_to_wp( $user_id, $data ) {
		// Update WP Native User Fields
		$user_data_update = array( 'ID' => $user_id );
		if ( isset( $data['first_name'] ) ) {
			$user_data_update['first_name'] = $data['first_name']; }
		if ( isset( $data['last_name'] ) ) {
			$user_data_update['last_name'] = $data['last_name']; }
		if ( count( $user_data_update ) > 1 ) {
			wp_update_user( $user_data_update );
		}

		// Sync with WooCommerce Billing & Shipping fields
		if ( isset( $data['first_name'] ) ) {
			update_user_meta( $user_id, 'billing_first_name', $data['first_name'] );
			update_user_meta( $user_id, 'shipping_first_name', $data['first_name'] );
		}
		if ( isset( $data['last_name'] ) ) {
			update_user_meta( $user_id, 'billing_last_name', $data['last_name'] );
			update_user_meta( $user_id, 'shipping_last_name', $data['last_name'] );
		}
		if ( isset( $data['email'] ) ) {
			update_user_meta( $user_id, 'billing_email', $data['email'] );
		}
		if ( isset( $data['billing_phone'] ) ) {
			update_user_meta( $user_id, 'ennu_global_billing_phone', $data['billing_phone'] );
		}
	}

	/**
	 * Send assessment notification email
	 *
	 * @param array $data Assessment data
	 */
	private function send_assessment_notification( $data ) {
		$to = $data['contact_email'];

		// Fix assessment type mapping for notifications
		$assessment_title = $this->get_assessment_title_for_notification( $data['assessment_type'] );

		$subject = sprintf(
			__( 'Your %s Results - ENNU Life', 'ennulifeassessments' ),
			$assessment_title
		);

		$message = $this->get_assessment_email_template( $data );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ENNU Life <noreply@ennulife.com>',
		);

		wp_mail( $to, $subject, $message, $headers );

		// Also send admin notification
		$admin_email   = get_option( 'admin_email' );
		$admin_subject = sprintf(
			__( 'New %s Submission', 'ennulifeassessments' ),
			$assessment_title
		);
		$admin_message = $this->get_admin_notification_template( $data );

		wp_mail( $admin_email, $admin_subject, $admin_message, $headers );
	}

	/**
	 * Render the ENNU Life header component for assessments
	 *
	 * @param string $assessment_type The type of assessment
	 * @param array $config Assessment configuration
	 * @return string HTML for the header
	 */
	private function render_ennu_header( $assessment_type = '', $config = array() ) {
		$current_user = wp_get_current_user();
		$first_name   = isset( $current_user->first_name ) ? $current_user->first_name : '';
		$last_name    = isset( $current_user->last_name ) ? $current_user->last_name : '';
		$display_name = trim( $first_name . ' ' . $last_name );
		if ( empty( $display_name ) ) {
			$display_name = $current_user->display_name ?? $current_user->user_login ?? 'User';
		}

		// Get assessment-specific title and description
		$assessment_title       = isset( $config['title'] ) ? $config['title'] : 'Assessment';
		$assessment_description = isset( $config['description'] ) ? $config['description'] : 'Complete your assessment to get personalized insights and recommendations.';

		ob_start();
		?>
		<div class="ennu-header-container" style="text-align: center; margin: 0; padding: 0; background: transparent; border-radius: 0; position: relative; overflow: visible;">
			<!-- Logo Container -->
			<div class="ennu-logo-container" style="margin-bottom: 15px; position: relative; z-index: 2; text-align: center;">
				<img src="<?php echo esc_url( plugins_url( 'assets/img/ennu-logo-black.png', dirname( __FILE__ ) ) ); ?>" 
					 alt="ENNU Life Logo" 
					 class="ennu-logo"
					 style="height: 40px; width: auto; display: inline-block; margin: 0 auto;">
			</div>
			
			<!-- Title and Subtitle -->
			<div class="ennu-header-content" style="position: relative; z-index: 2;">
				<h1 class="ennu-header-title" style="font-size: 2.4rem; font-weight: 300; color: #212529; margin: 0 0 8px 0; line-height: 1.2; text-align: center; letter-spacing: -0.5px; position: relative;"><?php echo esc_html( $assessment_title ); ?></h1>
				<p class="ennu-header-subtitle" style="font-size: 1.1rem; color: #6c757d; margin: 0 0 20px 0; line-height: 1.4; text-align: center; font-weight: 400; opacity: 0.8;"><?php echo esc_html( $assessment_description ); ?></p>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Convert assessment key to page slug for URLs
	 */
	public function get_assessment_page_slug( $assessment_key ) {
		// Reverse mapping from assessment keys to page slugs
		$assessment_to_slug_mapping = array(
			'weight_loss_assessment'         => 'weight-loss',
			'hair_assessment'                => 'hair',
			'skin_assessment'                => 'skin',
			'ed_treatment_assessment'        => 'ed-treatment',
			'health_assessment'              => 'health',
			'hormone_assessment'             => 'hormone',
			'sleep_assessment'               => 'sleep',
			'menopause_assessment'           => 'menopause',
			'testosterone_assessment'        => 'testosterone',
			'health_optimization_assessment' => 'health-optimization',
			'welcome_assessment'             => 'welcome',
		);

		return $assessment_to_slug_mapping[ $assessment_key ] ?? str_replace( '_', '-', $assessment_key );
	}

	/**
	 * Get the details page ID for an assessment from admin settings
	 */
	public function get_assessment_details_page_id( $assessment_key ) {
		// Get the actual page ID from admin settings (selected pages, not created pages)
		$settings = get_option( 'ennu_life_settings', array() );
		$page_mappings = $settings['page_mappings'] ?? array();
		$slug = $this->get_assessment_page_slug( $assessment_key );
		
		// Try the complex format first (assessments/{slug}/details)
		$complex_key = "assessments/{$slug}/details";
		if ( isset( $page_mappings[ $complex_key ] ) ) {
			return $page_mappings[ $complex_key ];
		}
		
		// Try the simple format (slug-details)
		$simple_key = "{$slug}-details";
		if ( isset( $page_mappings[ $simple_key ] ) ) {
			return $page_mappings[ $simple_key ];
		}
		
		// Fallback to direct page lookup
		$page = get_page_by_path( $complex_key );
		if ( $page ) {
			return $page->ID;
		}
		
		$page = get_page_by_path( $simple_key );
		if ( $page ) {
			return $page->ID;
		}
		
		return null;
	}

	/**
	 * Get the consultation page ID for an assessment from admin settings
	 */
	public function get_assessment_consultation_page_id( $assessment_key ) {
		// Get the actual page ID from admin settings (selected pages, not created pages)
		$settings = get_option( 'ennu_life_settings', array() );
		$page_mappings = $settings['page_mappings'] ?? array();
		$slug = $this->get_assessment_page_slug( $assessment_key );
		
		// Try the complex format first (assessments/{slug}/consultation)
		$complex_key = "assessments/{$slug}/consultation";
		if ( isset( $page_mappings[ $complex_key ] ) ) {
			return $page_mappings[ $complex_key ];
		}
		
		// Try the simple format (slug-consultation)
		$simple_key = "{$slug}-consultation";
		if ( isset( $page_mappings[ $simple_key ] ) ) {
			return $page_mappings[ $simple_key ];
		}
		
		// Fallback to direct page lookup
		$page = get_page_by_path( $complex_key );
		if ( $page ) {
			return $page->ID;
		}
		
		$page = get_page_by_path( $simple_key );
		if ( $page ) {
			return $page->ID;
		}
		
		// Final fallback to generic call page
		$call_page_id = $page_mappings['call'] ?? null;
		if ( $call_page_id ) {
			return $call_page_id;
		}
		
		return null;
	}

	/**
	 * Get assessment title for notifications with proper mapping
	 */
	private function get_assessment_title_for_notification( $assessment_type ) {
		// Map from config file keys to $this->assessments keys
		$assessment_mapping = array(
			'weight-loss'         => 'weight_loss_assessment',
			'hair'                => 'hair_assessment',
			'skin'                => 'skin_assessment',
			'ed-treatment'        => 'ed_treatment_assessment',
			'health'              => 'health_assessment',
			'hormone'             => 'hormone_assessment',
			'sleep'               => 'sleep_assessment',
			'menopause'           => 'menopause_assessment',
			'testosterone'        => 'testosterone_assessment',
			'health-optimization' => 'health_optimization_assessment',
			'welcome'             => 'welcome_assessment',
		);

		$mapped_key = $assessment_mapping[ $assessment_type ] ?? $assessment_type;

		if ( isset( $this->assessments[ $mapped_key ] ) ) {
			return $this->assessments[ $mapped_key ]['title'];
		}

		// Fallback to config file title if available
		if ( isset( $this->all_definitions[ $assessment_type ] ) ) {
			return $this->all_definitions[ $assessment_type ]['title'] ?? ucwords( str_replace( '-', ' ', $assessment_type ) );
		}

		// Final fallback
		return ucwords( str_replace( '-', ' ', $assessment_type ) );
	}

	/**
	 * Get assessment email template
	 *
	 * @param array $data Assessment data
	 * @return string
	 */
	private function get_assessment_email_template( $data ) {
		$assessment_title = $this->get_assessment_title_for_notification( $data['assessment_type'] );

		ob_start();
		?>
		<!DOCTYPE html>
		<html>
		<head>
			<meta charset="UTF-8">
			<title><?php echo esc_html( $assessment_title ); ?> Results</title>
		</head>
		<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
			<div style="max-width: 600px; margin: 0 auto; padding: 20px;">
				<h1 style="color: #667eea;">Thank you for completing your <?php echo esc_html( $assessment_title ); ?>!</h1>
				
				<p>Dear <?php echo esc_html( $data['contact_name'] ); ?>,</p>
				
				<p>Thank you for taking the time to complete your assessment. Our medical team will review your responses and provide personalized recommendations.</p>
				
				<h2>What happens next?</h2>
				<ul>
					<li>Our medical team will review your responses within 24 hours</li>
					<li>You'll receive personalized recommendations via email</li>
					<li>A specialist may contact you to discuss treatment options</li>
				</ul>
				
				<p>If you have any questions, please don't hesitate to contact us.</p>
				
				<p>Best regards,<br>The ENNU Life Team</p>
				
				<hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
				<p style="font-size: 12px; color: #666;">
					This email was sent to <?php echo esc_html( $data['contact_email'] ); ?> because you completed an assessment on our website.
				</p>
			</div>
		</body>
		</html>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get admin notification template
	 *
	 * @param array $data Assessment data
	 * @return string
	 */
	private function get_admin_notification_template( $data ) {
		$assessment_title = $this->get_assessment_title_for_notification( $data['assessment_type'] );

		ob_start();
		?>
		<h2>New <?php echo esc_html( $assessment_title ); ?> Submission</h2>
		
		<p><strong>Contact Information:</strong></p>
		<ul>
			<li>Name: <?php echo esc_html( $data['contact_name'] ); ?></li>
			<li>Email: <?php echo esc_html( $data['contact_email'] ); ?></li>
			<li>Phone: <?php echo esc_html( $data['contact_phone'] ); ?></li>
		</ul>
		
		<p><strong>Assessment Answers:</strong></p>
		<ul>
			<?php foreach ( $data['answers'] as $question => $answer ) : ?>
				<li><?php echo esc_html( ucwords( str_replace( '_', ' ', $question ) ) ); ?>: <?php echo esc_html( $answer ); ?></li>
			<?php endforeach; ?>
		</ul>
		
		<p><strong>Submission Time:</strong> <?php echo esc_html( current_time( 'Y-m-d H:i:s' ) ); ?></p>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get thank you URL for assessment type
	 *
	 * @param string $assessment_type
	 * @param string $token
	 * @return string
	 */
	public function get_thank_you_url( $assessment_type, $token = null ) {
		error_log( 'ENNU REDIRECT DEBUG: Starting get_thank_you_url with assessment_type: ' . $assessment_type . ', token: ' . ( $token ? 'present' : 'null' ) );
		
		// Get the page mappings from admin settings ONLY
		$settings = get_option( 'ennu_life_settings', array() );
		$page_mappings = $settings['page_mappings'] ?? array();
		
		// Handle both formats: 'weight-loss' and 'weight_loss_assessment'
		$base_slug = $assessment_type;
		if ( strpos( $assessment_type, '-' ) !== false ) {
			$base_slug = $assessment_type;
		} else {
			// Convert assessment type (e.g., 'weight_loss_assessment') to slug (e.g., 'weight-loss')
			$base_slug = str_replace( '_assessment', '', $assessment_type );
			$base_slug = str_replace( '_', '-', $base_slug );
		}
		
		// Create assessment-specific results page slug
		$results_slug = $base_slug . '-assessment-details';
		
		// ONLY use admin-configured pages - NO AUTOMATIC CREATION
		if ( isset( $page_mappings[ $results_slug ] ) && ! empty( $page_mappings[ $results_slug ] ) ) {
			$page_id = $page_mappings[ $results_slug ];
			
			// Use the ?page_id= format with token parameter
			$redirect_url = home_url( "/?page_id={$page_id}" );
			
			// Add results token if available
			if ( $token ) {
				$redirect_url = add_query_arg( 'token', urlencode( $token ), $redirect_url );
			}
			
			error_log( 'ENNU REDIRECT DEBUG: Using admin-configured results page for ' . $base_slug . ' with page_id=' . $page_id . ' (slug: ' . $results_slug . ')' );
			return $redirect_url;
		}
		
		// NO FALLBACKS - Return false if page not configured
		error_log( 'ENNU REDIRECT DEBUG: No admin-configured results page found for ' . $base_slug . ' (slug: ' . $results_slug . ') - Redirect will fail' );
		return false;
	}

	/**
	 * Render assessment results page
	 *
	 * @param array $atts Shortcode attributes
	 * @return string
	 */
	public function render_results_page( $atts = array() ) {
		error_log( 'ENNU REDIRECT DEBUG: render_results_page() called' );
		
		// Check for token in URL parameters
		$token = isset( $_GET['token'] ) ? sanitize_text_field( $_GET['token'] ) : '';
		error_log( 'ENNU REDIRECT DEBUG: Token from URL: ' . ( $token ? $token : 'not found' ) );
		
		// If token is provided, validate it and get results
		if ( ! empty( $token ) ) {
			error_log( 'ENNU REDIRECT DEBUG: Token found, validating...' );
			$results_data = $this->validate_token_and_get_results( $token );
			
			if ( $results_data && is_array( $results_data ) && isset( $results_data['assessment_type'] ) ) {
				// Display results using token data
				$assessment_type    = $results_data['assessment_type'];
				$score              = $results_data['score'];
				$interpretation_arr = $results_data['interpretation'];
				$interpretation_key = isset( $interpretation_arr['level'] ) ? strtolower( $interpretation_arr['level'] ) : 'fair';
				$category_scores    = $results_data['category_scores'];
				$user_answers       = $results_data['answers'] ?? array();
				$user_id            = $results_data['user_id'];
				$bmi                = get_user_meta( $user_id, 'ennu_calculated_bmi', true );

				$content_config_file = plugin_dir_path( __FILE__ ) . '../includes/config/results-content.php';
				$content_config      = file_exists( $content_config_file ) ? require $content_config_file : array();
				
				// Map assessment types to content configuration keys
				$assessment_type_mapping = array(
					'weight-loss' => 'weight_loss_assessment',
					'ed-treatment' => 'ed_treatment_assessment',
					'health-optimization' => 'health_optimization_assessment',
					'hair' => 'hair_assessment',
					'skin' => 'skin_assessment',
					'hormone' => 'hormone_assessment',
					'sleep' => 'sleep_assessment',
					'menopause' => 'menopause_assessment',
					'testosterone' => 'testosterone_assessment',
					'health' => 'health_assessment',
					'welcome' => 'welcome_assessment'
				);
				
				$content_key = $assessment_type_mapping[ $assessment_type ] ?? $assessment_type;
				error_log( 'ENNU Results Page Debug: Assessment type: ' . $assessment_type . ', Mapped to: ' . $content_key );
				$content_data        = $content_config[ $content_key ] ?? $content_config['default'];
				$result_content      = $content_data['score_ranges'][ $interpretation_key ] ?? $content_data['score_ranges']['fair'];
				$conditional_recs    = $content_data['conditional_recommendations'] ?? array();
				$matched_recs        = array();

				wp_enqueue_style( 'ennu-assessment-results', ENNU_LIFE_PLUGIN_URL . 'assets/css/assessment-results.css', array(), ENNU_LIFE_VERSION );
				wp_enqueue_script( 'chartjs', ENNU_LIFE_PLUGIN_URL . 'assets/js/chart.umd.js', array(), '4.4.1', true );
				wp_enqueue_script( 'ennu-assessment-results', ENNU_LIFE_PLUGIN_URL . 'assets/js/assessment-results.js', array( 'chartjs' ), ENNU_LIFE_VERSION, true );
				wp_localize_script(
					'ennu-assessment-results',
					'ennuResultsData',
					array(
						'chart_labels' => array_keys( $category_scores ),
						'chart_data'   => array_values( $category_scores ),
					)
				);

				// Calculate pillar scores for the results display
				$pillar_scores = ENNU_Scoring_System::calculate_average_pillar_scores( $user_id );
				$overall_score = $score; // Use the assessment score as overall score

				$data = compact( 'content_data', 'bmi', 'result_content', 'score', 'matched_recs', 'assessment_type', 'category_scores', 'pillar_scores', 'overall_score' );

				// Add the shortcode instance to the data so templates can use get_page_id_url
				$data['shortcode_instance'] = $this;

				ob_start();
				ennu_load_template( 'assessment-results.php', $data );
				return ob_get_clean();
			}
		}

		// Fallback to user-based transient system for logged-in users
		$user_id           = get_current_user_id();
		$results_transient = $user_id ? get_transient( 'ennu_assessment_results_' . $user_id ) : false;

		if ( $results_transient && is_array( $results_transient ) && isset( $results_transient['assessment_type'] ) ) {
			// Logic from render_thank_you_page to display results.
			delete_transient( 'ennu_assessment_results_' . $user_id );

			$assessment_type    = $results_transient['assessment_type'];
			$score              = $results_transient['score'];
			$interpretation_arr = $results_transient['interpretation'];
			$interpretation_key = isset( $interpretation_arr['level'] ) ? strtolower( $interpretation_arr['level'] ) : 'fair';
			$category_scores    = $results_transient['category_scores'];
			$user_answers       = $results_transient['answers'] ?? array();
			$bmi                = get_user_meta( $user_id, 'ennu_calculated_bmi', true );

			$content_config_file = plugin_dir_path( __FILE__ ) . '../includes/config/results-content.php';
			$content_config      = file_exists( $content_config_file ) ? require $content_config_file : array();
			
			// Map assessment types to content configuration keys
			$assessment_type_mapping = array(
				'weight-loss' => 'weight_loss_assessment',
				'ed-treatment' => 'ed_treatment_assessment',
				'health-optimization' => 'health_optimization_assessment',
				'hair' => 'hair_assessment',
				'skin' => 'skin_assessment',
				'hormone' => 'hormone_assessment',
				'sleep' => 'sleep_assessment',
				'menopause' => 'menopause_assessment',
				'testosterone' => 'testosterone_assessment',
				'health' => 'health_assessment',
				'welcome' => 'welcome_assessment'
			);
			
			$content_key = $assessment_type_mapping[ $assessment_type ] ?? $assessment_type;
			error_log( 'ENNU Results Page Debug: Assessment type: ' . $assessment_type . ', Mapped to: ' . $content_key );
			$content_data        = $content_config[ $content_key ] ?? $content_config['default'];
			$result_content      = $content_data['score_ranges'][ $interpretation_key ] ?? $content_data['score_ranges']['fair'];
			$conditional_recs    = $content_data['conditional_recommendations'] ?? array();
			$matched_recs        = array();

			wp_enqueue_style( 'ennu-assessment-results', ENNU_LIFE_PLUGIN_URL . 'assets/css/assessment-results.css', array(), ENNU_LIFE_VERSION );
			wp_enqueue_script( 'chartjs', ENNU_LIFE_PLUGIN_URL . 'assets/js/chart.umd.js', array(), '4.4.1', true );
			wp_enqueue_script( 'ennu-assessment-results', ENNU_LIFE_PLUGIN_URL . 'assets/js/assessment-results.js', array( 'chartjs' ), ENNU_LIFE_VERSION, true );
			wp_localize_script(
				'ennu-assessment-results',
				'ennuResultsData',
				array(
					'chart_labels' => array_keys( $category_scores ),
					'chart_data'   => array_values( $category_scores ),
				)
			);

			// Calculate pillar scores for the results display
			$pillar_scores = ENNU_Scoring_System::calculate_average_pillar_scores( $user_id );
			$overall_score = $score; // Use the assessment score as overall score

			$data = compact( 'content_data', 'bmi', 'result_content', 'score', 'matched_recs', 'assessment_type', 'category_scores', 'pillar_scores', 'overall_score' );

			// Add the shortcode instance to the data so templates can use get_page_id_url
			$data['shortcode_instance'] = $this;

			ob_start();
			ennu_load_template( 'assessment-results.php', $data );
			return ob_get_clean();
		}

		// Handle empty state for the generic results page.
		ob_start();
		$data = array( 'shortcode_instance' => $this );
		ennu_load_template( 'assessment-results-expired.php', $data );
		return ob_get_clean();
	}

	/**
	 * Render chart page
	 *
	 * @param array $atts Shortcode attributes
	 * @return string
	 */
	public function render_chart_page( $atts = array() ) {
		if ( ! is_user_logged_in() ) {
			return '<div class="ennu-chart-container"><p>You must be logged in to view results.</p></div>';
		}

		$user_id = get_current_user_id();

		// For now, we'll default to the latest completed assessment. A more robust solution
		// would allow specifying the assessment type in the shortcode.
		$assessment_type = get_user_meta( $user_id, 'ennu_last_assessment_completed', true );
		if ( ! $assessment_type ) {
			return '<div class="ennu-chart-container"><p>No assessment results found. Please complete an assessment first.</p></div>';
		}

		$score           = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_calculated_score', true );
		$category_scores = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_category_scores', true );

		if ( ! $score || ! is_array( $category_scores ) || empty( $category_scores ) ) {
			return '<div class="ennu-chart-container"><p>No score data found for your latest assessment. Please complete an assessment first.</p></div>';
		}

		$assessment_title = $this->assessments[ $assessment_type ]['title'] ?? 'Your Assessment';
		$chart_labels     = array_keys( $category_scores );
		$chart_data       = array_values( $category_scores );

		wp_enqueue_script( 'chartjs', ENNU_LIFE_PLUGIN_URL . 'assets/js/chart.umd.js', array(), '4.4.1', true );
		wp_enqueue_script( 'ennu-assessment-chart', ENNU_LIFE_PLUGIN_URL . 'assets/js/assessment-chart.js', array( 'chartjs' ), ENNU_LIFE_VERSION, true );
		wp_localize_script(
			'ennu-assessment-chart',
			'ennuChartData',
			array(
				'labels' => $chart_labels,
				'data'   => $chart_data,
			)
		);

		$data = compact( 'assessment_title' );

		ob_start();
		ennu_load_template( 'assessment-chart.php', $data );
		return ob_get_clean();
	}

	/**
	 * Enqueue Chart.js script if a page contains the chart shortcode.
	 */
	public function enqueue_chart_scripts() {
		global $post;
		if ( ! is_a( $post, 'WP_Post' ) ) {
			return;
		}

		$shortcodes_to_check = array(
			'ennu-assessment-chart',
			'ennu-user-dashboard',
			'ennu-hair-assessment-details',
			'ennu-ed-treatment-assessment-details',
			'ennu-weight-loss-assessment-details',
			'ennu-health-assessment-details',
			'ennu-skin-assessment-details',
			'ennu-sleep-assessment-details',
			'ennu-hormone-assessment-details',
			'ennu-menopause-assessment-details',
			'ennu-testosterone-assessment-details',
		);

		$load_scripts = false;
		foreach ( $shortcodes_to_check as $shortcode ) {
			if ( has_shortcode( $post->post_content, $shortcode ) ) {
				$load_scripts = true;
				break;
			}
		}

		if ( $load_scripts ) {
			wp_enqueue_script(
				'chartjs',
				ENNU_LIFE_PLUGIN_URL . 'assets/js/chart.umd.js',
				array(),
				'4.4.0',
				true
			);
			// existing Chart.js
			wp_enqueue_style( 'user-dashboard-style', ENNU_LIFE_PLUGIN_URL . 'assets/css/user-dashboard.css', array(), ENNU_LIFE_VERSION );
		}
	}

	/**
	 * Enqueue results page styles if a page contains a results shortcode.
	 */
	public function enqueue_results_styles() {
		global $post;
		if ( ! is_a( $post, 'WP_Post' ) || empty( $post->post_content ) ) {
			return;
		}

		$shortcodes_to_check = array(
			// The generic results page
			'ennu-assessment-results',
			// All specific results pages
			'ennu-hair-results',
			'ennu-ed-results',
			'ennu-weight-loss-results',
			'ennu-health-results',
			'ennu-skin-results',
			'ennu-sleep-results',
			'ennu-hormone-results',
			'ennu-menopause-results',
			'ennu-testosterone-results',
			// All details pages (dossier)
			'ennu-hair-assessment-details',
			'ennu-ed-treatment-assessment-details',
			'ennu-weight-loss-assessment-details',
			'ennu-health-assessment-details',
			'ennu-skin-assessment-details',
			'ennu-sleep-assessment-details',
			'ennu-hormone-assessment-details',
			'ennu-menopause-assessment-details',
			'ennu-testosterone-assessment-details',
			// All consultation shortcodes
			'ennu-hair-consultation',
			'ennu-ed-treatment-consultation',
			'ennu-weight-loss-consultation',
			'ennu-health-optimization-consultation',
			'ennu-skin-consultation',
			'ennu-health-consultation',
			'ennu-hormone-consultation',
			'ennu-menopause-consultation',
			'ennu-testosterone-consultation',
			'ennu-sleep-consultation',
		);

		foreach ( $shortcodes_to_check as $shortcode ) {
			if ( has_shortcode( $post->post_content, $shortcode ) ) {
				// --- UNIFIED LUXURY DESIGN SYSTEM ---
				// Load the unified design system for consistent "Bio-Metric Canvas" look
				wp_enqueue_style( 'ennu-unified-design', ENNU_LIFE_PLUGIN_URL . 'assets/css/ennu-unified-design.css', array(), ENNU_LIFE_VERSION );
				wp_enqueue_style( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/css/user-dashboard.css', array(), ENNU_LIFE_VERSION );
				wp_enqueue_style( 'ennu-details-page-style', ENNU_LIFE_PLUGIN_URL . 'assets/css/assessment-details-page.css', array(), ENNU_LIFE_VERSION );
				// Load the modern assessment results styling
				wp_enqueue_style( 'ennu-assessment-results', ENNU_LIFE_PLUGIN_URL . 'assets/css/assessment-results.css', array(), ENNU_LIFE_VERSION );
				// Force light mode only for results, dossier, and booking pages
				wp_enqueue_style( 'ennu-light-mode-only', ENNU_LIFE_PLUGIN_URL . 'assets/css/light-mode-only.css', array(), ENNU_LIFE_VERSION );
				wp_enqueue_script( 'chartjs', ENNU_LIFE_PLUGIN_URL . 'assets/js/chart.umd.js', array(), '4.4.0', true );
				// Break after finding the first match
				return;
			}
		}
	}

	/**
	 * Render thank you page shortcode, now with dynamic results.
	 *
	 * @param array $atts Shortcode attributes
	 * @param string $content Shortcode content
	 * @param string $tag Shortcode tag
	 * @return string
	 */
	public function render_thank_you_page( $atts, $content = '', $tag = '' ) {
		ob_start(); // START ALL OUTPUT BUFFERING

		// --- TOKEN-BASED RESULTS ---
		// Check for both 'results_token' (legacy) and 'token' (new AJAX format)
		$results_token = isset( $_GET['results_token'] ) ? sanitize_text_field( $_GET['results_token'] ) : null;
		if ( ! $results_token ) {
			$results_token = isset( $_GET['token'] ) ? sanitize_text_field( $_GET['token'] ) : null;
		}
		$transient_key = 'ennu_results_' . $results_token;
		$results_transient = $results_token ? $this->_get_manual_transient( $transient_key ) : false;
		error_log( 'ENNU Results Debug: Looking for transient key: ' . $transient_key . ', Found: ' . ( $results_transient ? 'yes' : 'no' ) );

		if ( $results_transient ) {
			// Don't delete the transient immediately - let user view results multiple times
			// $this->_delete_manual_transient( 'ennu_results_' . $results_token );

			// --- DEFINITIVE DATA HARMONIZATION FIX ---
			$assessment_type    = $results_transient['assessment_type'] ?? '';
			$score              = $results_transient['score'] ?? 0;
			$interpretation     = $results_transient['interpretation'] ?? array();
			$interpretation_key = strtolower( $interpretation['level'] ?? 'fair' );

			// 1. Load the master results content configuration
			$content_config_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/results-content.php';
			$content_config      = file_exists( $content_config_file ) ? require $content_config_file : array();
			
			// Map assessment types to content configuration keys
			$assessment_type_mapping = array(
				'weight-loss' => 'weight_loss_assessment',
				'ed-treatment' => 'ed_treatment_assessment',
				'health-optimization' => 'health_optimization_assessment',
				'hair' => 'hair_assessment',
				'skin' => 'skin_assessment',
				'hormone' => 'hormone_assessment',
				'sleep' => 'sleep_assessment',
				'menopause' => 'menopause_assessment',
				'testosterone' => 'testosterone_assessment',
				'health' => 'health_assessment',
				'welcome' => 'welcome_assessment'
			);
			
			$content_key = $assessment_type_mapping[ $assessment_type ] ?? $assessment_type;
			error_log( 'ENNU Results Debug: Assessment type: ' . $assessment_type . ', Mapped to: ' . $content_key );
			$assessment_content  = $content_config[ $content_key ] ?? $content_config['default'];

			// 2. Select the correct content block based on the score
			$result_content = $assessment_content['score_ranges'][ $interpretation_key ] ?? $assessment_content['score_ranges']['fair'];

			// 3. Prepare all necessary URLs for the template's action buttons
			$details_slug         = str_replace( '_assessment', '-assessment-details', $assessment_type );
			$details_button_url   = '?' . ENNU_UI_Constants::get_page_type( 'ASSESSMENTS' );
			$dashboard_button_url = '?' . ENNU_UI_Constants::get_page_type( 'DASHBOARD' );
			$retake_url           = $this->get_assessment_page_url( $assessment_type );

			// 4. Meticulously construct the final data array for the template
			$data = array(
				'user_id'                  => $results_transient['user_id'] ?? get_current_user_id(),
				'assessment_type'          => $assessment_type,
				'assessment_title'         => $this->assessments[ $assessment_type ]['title'] ?? ucwords( str_replace( '_', ' ', $assessment_type ) ),
				'overall_score'            => $score, // Make sure this is available for the template
				'score'                    => $score,
				'pillar_scores'            => $results_transient['pillar_scores'] ?? array(), // Add pillar scores
				'category_scores'          => $results_transient['category_scores'] ?? array(),
				'result_content'           => $result_content,
				'matched_recs'             => array(),
				'details_button_url'       => $details_button_url,
				'dashboard_button_url'     => $dashboard_button_url,
				'retake_url'               => $retake_url,
				'enable_real_time_updates' => true,
				'shortcode_instance'       => $this, // Add the shortcode instance for template access
			);

			// Add user data for universal header
			$user_id = $data['user_id'];
			if ( $user_id ) {
				$user = get_user_by( 'ID', $user_id );
				if ( $user ) {
					$first_name = get_user_meta( $user_id, 'first_name', true );
					$last_name = get_user_meta( $user_id, 'last_name', true );
					$display_name = trim( $first_name . ' ' . $last_name );
					if ( empty( $display_name ) ) {
						$display_name = $user->display_name ?? $user->user_login ?? 'User';
					}
					
					$data['display_name'] = $display_name;
					// Enhanced data retrieval - try multiple sources for each field
					$data['age'] = get_user_meta( $user_id, 'ennu_global_age', true );
					if ( empty( $data['age'] ) ) {
						$data['age'] = get_user_meta( $user_id, 'ennu_global_exact_age', true );
					}
					if ( empty( $data['age'] ) ) {
						$data['age'] = get_user_meta( $user_id, 'age', true );
					}
					
					$data['gender'] = get_user_meta( $user_id, 'ennu_global_gender', true );
					if ( empty( $data['gender'] ) ) {
						$data['gender'] = get_user_meta( $user_id, 'gender', true );
					}
					
					// Get height and weight from combined field
					$height_weight_data = get_user_meta( $user_id, 'ennu_global_height_weight', true );
					if ( ! empty( $height_weight_data ) && is_array( $height_weight_data ) ) {
						// Format height as "5' 10""
						if ( ! empty( $height_weight_data['ft'] ) && ! empty( $height_weight_data['in'] ) ) {
							$data['height'] = "{$height_weight_data['ft']}' {$height_weight_data['in']}\"";
						}
						
						// Get weight in lbs
						if ( isset( $height_weight_data['weight'] ) ) {
							$data['weight'] = $height_weight_data['weight'] . ' lbs';
						} elseif ( isset( $height_weight_data['lbs'] ) ) {
							$data['weight'] = $height_weight_data['lbs'] . ' lbs';
						}
					} else {
						// Fallback to separate fields
						$data['height'] = get_user_meta( $user_id, 'ennu_global_height', true );
						if ( empty( $data['height'] ) ) {
							$data['height'] = get_user_meta( $user_id, 'height', true );
						}
						
						$data['weight'] = get_user_meta( $user_id, 'ennu_global_weight', true );
						if ( empty( $data['weight'] ) ) {
							$data['weight'] = get_user_meta( $user_id, 'weight', true );
						}
					}
					
					// Get BMI
					$data['bmi'] = get_user_meta( $user_id, 'ennu_global_bmi', true );
					if ( empty( $data['bmi'] ) ) {
						$data['bmi'] = get_user_meta( $user_id, 'bmi', true );
					}
					
					// Calculate BMI if we have height and weight but no BMI
					if ( empty( $data['bmi'] ) && ! empty( $data['height'] ) && ! empty( $data['weight'] ) ) {
						// Extract weight value (remove "lbs")
						$weight_value = str_replace( ' lbs', '', $data['weight'] );
						$weight_value = floatval( $weight_value );
						
						// Extract height value (convert to inches)
						if ( preg_match( "/(\d+)' (\d+)\"/", $data['height'], $matches ) ) {
							$height_ft = intval( $matches[1] );
							$height_in = intval( $matches[2] );
							$height_inches = ( $height_ft * 12 ) + $height_in;
							
							// Calculate BMI: (weight in lbs * 703) / (height in inches)^2
							if ( $height_inches > 0 ) {
								$bmi = ( $weight_value * 703 ) / ( $height_inches * $height_inches );
								$data['bmi'] = round( $bmi, 1 );
							}
						}
					}
					
					// Debug logging - show all available user meta
					$all_user_meta = get_user_meta( $user_id );
					$relevant_meta = array();
					foreach ( $all_user_meta as $key => $value ) {
						if ( strpos( $key, 'age' ) !== false || 
							 strpos( $key, 'gender' ) !== false || 
							 strpos( $key, 'height' ) !== false || 
							 strpos( $key, 'weight' ) !== false || 
							 strpos( $key, 'bmi' ) !== false ||
							 strpos( $key, 'ennu' ) !== false ) {
							$relevant_meta[ $key ] = $value[0] ?? '';
						}
					}
					error_log( 'ENNU Results: All relevant user meta for user ' . $user_id . ': ' . print_r( $relevant_meta, true ) );
					error_log( 'ENNU Results: User data for header - Name: ' . $data['display_name'] . ', Age: ' . $data['age'] . ', Gender: ' . $data['gender'] );
				}
			}

			// Ensure proper CSS is loaded for the score display
			wp_enqueue_style( 'ennu-unified-design', ENNU_LIFE_PLUGIN_URL . 'assets/css/ennu-unified-design.css', array(), ENNU_LIFE_VERSION );
			wp_enqueue_style( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/css/user-dashboard.css', array(), ENNU_LIFE_VERSION );
			wp_enqueue_style( 'ennu-details-page-style', ENNU_LIFE_PLUGIN_URL . 'assets/css/assessment-details-page.css', array(), ENNU_LIFE_VERSION );
			wp_enqueue_style( 'ennu-light-mode-only', ENNU_LIFE_PLUGIN_URL . 'assets/css/light-mode-only.css', array(), ENNU_LIFE_VERSION );

			// Load the template with the correctly structured data
			$data['shortcode_instance'] = $this;
			ennu_load_template( 'assessment-results.php', $data );

		} else {
			// FALLBACK: Try to get assessment data from user meta if transient expired
			$fallback_data = $this->get_fallback_assessment_data( $results_token );
			
			if ( $fallback_data ) {
				// Use fallback data to show results
				$assessment_type    = $fallback_data['assessment_type'] ?? '';
				$score              = $fallback_data['score'] ?? 0;
				$interpretation     = $fallback_data['interpretation'] ?? array();
				$interpretation_key = strtolower( $interpretation['level'] ?? 'fair' );

				// 1. Load the master results content configuration
				$content_config_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/results-content.php';
				$content_config      = file_exists( $content_config_file ) ? require $content_config_file : array();
				$assessment_content  = $content_config[ $assessment_type ] ?? $content_config['default'];

				// 2. Select the correct content block based on the score
				$result_content = $assessment_content['score_ranges'][ $interpretation_key ] ?? $assessment_content['score_ranges']['fair'];

				// 3. Prepare all necessary URLs for the template's action buttons
				$details_slug         = str_replace( '_assessment', '-assessment-details', $assessment_type );
				$details_button_url   = '?' . ENNU_UI_Constants::get_page_type( 'ASSESSMENTS' );
				$dashboard_button_url = '?' . ENNU_UI_Constants::get_page_type( 'DASHBOARD' );
				$retake_url           = $this->get_assessment_page_url( $assessment_type );

				// 4. Construct the final data array for the template
				$data = array(
					'user_id'                  => $fallback_data['user_id'] ?? get_current_user_id(),
					'assessment_type'          => $assessment_type,
					'assessment_title'         => $this->assessments[ $assessment_type ]['title'] ?? ucwords( str_replace( '_', ' ', $assessment_type ) ),
					'overall_score'            => $score, // Make sure this is available for the template
					'score'                    => $score,
					'pillar_scores'            => $fallback_data['pillar_scores'] ?? array(), // Add pillar scores
					'category_scores'          => $fallback_data['category_scores'] ?? array(),
					'result_content'           => $result_content,
					'matched_recs'             => array(),
					'details_button_url'       => $details_button_url,
					'dashboard_button_url'     => $dashboard_button_url,
					'retake_url'               => $retake_url,
					'enable_real_time_updates' => true,
					'shortcode_instance'       => $this, // Add the shortcode instance for template access
				);

				// Add user data for universal header
				$user_id = $data['user_id'];
				if ( $user_id ) {
					$user = get_user_by( 'ID', $user_id );
					if ( $user ) {
						$first_name = get_user_meta( $user_id, 'first_name', true );
						$last_name = get_user_meta( $user_id, 'last_name', true );
						$display_name = trim( $first_name . ' ' . $last_name );
						if ( empty( $display_name ) ) {
							$display_name = $user->display_name ?? $user->user_login ?? 'User';
						}
						
						$data['display_name'] = $display_name;
						
						// Enhanced data retrieval - try multiple sources for each field
						$data['age'] = get_user_meta( $user_id, 'ennu_global_age', true );
						if ( empty( $data['age'] ) ) {
							$data['age'] = get_user_meta( $user_id, 'ennu_global_exact_age', true );
						}
						if ( empty( $data['age'] ) ) {
							$data['age'] = get_user_meta( $user_id, 'age', true );
						}
						
						$data['gender'] = get_user_meta( $user_id, 'ennu_global_gender', true );
						if ( empty( $data['gender'] ) ) {
							$data['gender'] = get_user_meta( $user_id, 'gender', true );
						}
						
						// Get height and weight from combined field
						$height_weight_data = get_user_meta( $user_id, 'ennu_global_height_weight', true );
						if ( ! empty( $height_weight_data ) && is_array( $height_weight_data ) ) {
							// Format height as "5' 10""
							if ( ! empty( $height_weight_data['ft'] ) && ! empty( $height_weight_data['in'] ) ) {
								$data['height'] = "{$height_weight_data['ft']}' {$height_weight_data['in']}\"";
							}
							
							// Get weight in lbs
							if ( isset( $height_weight_data['weight'] ) ) {
								$data['weight'] = $height_weight_data['weight'] . ' lbs';
							} elseif ( isset( $height_weight_data['lbs'] ) ) {
								$data['weight'] = $height_weight_data['lbs'] . ' lbs';
							}
						} else {
							// Fallback to separate fields
							$data['height'] = get_user_meta( $user_id, 'ennu_global_height', true );
							if ( empty( $data['height'] ) ) {
								$data['height'] = get_user_meta( $user_id, 'height', true );
							}
							
							$data['weight'] = get_user_meta( $user_id, 'ennu_global_weight', true );
							if ( empty( $data['weight'] ) ) {
								$data['weight'] = get_user_meta( $user_id, 'weight', true );
							}
						}
						
						// Get BMI
						$data['bmi'] = get_user_meta( $user_id, 'ennu_global_bmi', true );
						if ( empty( $data['bmi'] ) ) {
							$data['bmi'] = get_user_meta( $user_id, 'bmi', true );
						}
						
						// Calculate BMI if we have height and weight but no BMI
						if ( empty( $data['bmi'] ) && ! empty( $data['height'] ) && ! empty( $data['weight'] ) ) {
							// Extract weight value (remove "lbs")
							$weight_value = str_replace( ' lbs', '', $data['weight'] );
							$weight_value = floatval( $weight_value );
							
							// Extract height value (convert to inches)
							if ( preg_match( "/(\d+)' (\d+)\"/", $data['height'], $matches ) ) {
								$height_ft = intval( $matches[1] );
								$height_in = intval( $matches[2] );
								$height_inches = ( $height_ft * 12 ) + $height_in;
								
								// Calculate BMI: (weight in lbs * 703) / (height in inches)^2
								if ( $height_inches > 0 ) {
									$bmi = ( $weight_value * 703 ) / ( $height_inches * $height_inches );
									$data['bmi'] = round( $bmi, 1 );
								}
							}
						}
						
						// Debug logging - show all available user meta
						$all_user_meta = get_user_meta( $user_id );
						$relevant_meta = array();
						foreach ( $all_user_meta as $key => $value ) {
							if ( strpos( $key, 'age' ) !== false || 
								 strpos( $key, 'gender' ) !== false || 
								 strpos( $key, 'height' ) !== false || 
								 strpos( $key, 'weight' ) !== false || 
								 strpos( $key, 'bmi' ) !== false ||
								 strpos( $key, 'ennu' ) !== false ) {
								$relevant_meta[ $key ] = $value[0] ?? '';
							}
						}
						error_log( 'ENNU Results: All relevant user meta for user ' . $user_id . ': ' . print_r( $relevant_meta, true ) );
						error_log( 'ENNU Results: User data for header - Name: ' . $data['display_name'] . ', Age: ' . $data['age'] . ', Gender: ' . $data['gender'] );
					}
				}

				// Ensure proper CSS is loaded for the score display
				wp_enqueue_style( 'ennu-unified-design', ENNU_LIFE_PLUGIN_URL . 'assets/css/ennu-unified-design.css', array(), ENNU_LIFE_VERSION );
				wp_enqueue_style( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/css/user-dashboard.css', array(), ENNU_LIFE_VERSION );
				wp_enqueue_style( 'ennu-details-page-style', ENNU_LIFE_PLUGIN_URL . 'assets/css/assessment-details-page.css', array(), ENNU_LIFE_VERSION );
				wp_enqueue_style( 'ennu-light-mode-only', ENNU_LIFE_PLUGIN_URL . 'assets/css/light-mode-only.css', array(), ENNU_LIFE_VERSION );

				// Load biomarkers CSS and JavaScript for the biomarkers section
				wp_enqueue_style( 'ennu-theme-system', ENNU_LIFE_PLUGIN_URL . 'assets/css/theme-system.css', array(), ENNU_LIFE_VERSION );
				wp_enqueue_script( 'ennu-theme-manager', ENNU_LIFE_PLUGIN_URL . 'assets/js/theme-manager.js', array(), ENNU_LIFE_VERSION, true );
				wp_enqueue_script( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/js/user-dashboard.js', array( 'jquery', 'ennu-theme-manager' ), ENNU_LIFE_VERSION, true );

				// Load the template with the fallback data
				$data['shortcode_instance'] = $this;
				ennu_load_template( 'assessment-results.php', $data );
			} else {
				// Load the expired/empty state template
				$data = array( 'shortcode_instance' => $this );
				ennu_load_template( 'assessment-results-expired.php', $data );
			}
		}

		return ob_get_clean(); // RETURN ALL CAPTURED OUTPUT
	}

	/**
	 * Validate token and get assessment results
	 *
	 * @param string $token The results token to validate
	 * @return array|false Assessment data or false if token is invalid
	 */
	private function validate_token_and_get_results( $token ) {
		error_log( 'ENNU REDIRECT DEBUG: validate_token_and_get_results() called with token: ' . $token );
		
		if ( empty( $token ) ) {
			error_log( 'ENNU REDIRECT DEBUG: Token is empty' );
			return false;
		}

		// Get the transient data using the token
		$results_data = get_transient( 'ennu_results_token_' . $token );
		error_log( 'ENNU REDIRECT DEBUG: Retrieved transient data: ' . ( $results_data ? 'found' : 'not found' ) );
		
		if ( ! $results_data || ! is_array( $results_data ) ) {
			error_log( 'ENNU REDIRECT DEBUG: No valid results data found for token' );
			return false;
		}

		// Check if the token has expired (24 hours)
		$token_created = isset( $results_data['token_created'] ) ? $results_data['token_created'] : 0;
		$current_time = time();
		$time_diff = $current_time - $token_created;
		$expiry_time = 24 * 60 * 60; // 24 hours
		
		error_log( 'ENNU REDIRECT DEBUG: Token created: ' . $token_created . ', Current time: ' . $current_time . ', Time diff: ' . $time_diff . ' seconds' );
		
		if ( $time_diff > $expiry_time ) {
			error_log( 'ENNU REDIRECT DEBUG: Token has expired (diff: ' . $time_diff . ' > ' . $expiry_time . ')' );
			// Token has expired, delete it
			delete_transient( 'ennu_results_token_' . $token );
			return false;
		}

		error_log( 'ENNU REDIRECT DEBUG: Token is valid, returning results data' );
		// Token is valid, return the results data
		return $results_data;
	}

	/**
	 * Fallback method to retrieve assessment data from user meta when transient has expired
	 *
	 * @param string $results_token The results token to look up
	 * @return array|false Assessment data or false if not found
	 */
	private function get_fallback_assessment_data( $results_token ) {
		if ( ! $results_token ) {
			return false;
		}

		// Try to determine the assessment type from the token or user's recent assessments
		$current_user_id = get_current_user_id();
		if ( ! $current_user_id ) {
			return false;
		}

		// Get the user's most recent assessment data
		$assessment_types = array(
			'health_assessment',
			'weight_loss_assessment', 
			'ed_treatment_assessment',
			'hair_assessment',
			'skin_assessment',
			'sleep_assessment',
			'hormone_assessment',
			'menopause_assessment',
			'testosterone_assessment'
		);

		$most_recent_assessment = null;
		$most_recent_time = 0;

		foreach ( $assessment_types as $assessment_type ) {
			$completion_time = get_user_meta( $current_user_id, 'ennu_' . $assessment_type . '_completed_at', true );
			if ( $completion_time && strtotime( $completion_time ) > $most_recent_time ) {
				$most_recent_time = strtotime( $completion_time );
				$most_recent_assessment = $assessment_type;
			}
		}

		// If no recent assessment found, try to get from user meta
		if ( ! $most_recent_assessment ) {
			foreach ( $assessment_types as $assessment_type ) {
				$score = get_user_meta( $current_user_id, 'ennu_' . $assessment_type . '_calculated_score', true );
				if ( ! empty( $score ) ) {
					$most_recent_assessment = $assessment_type;
					break;
				}
			}
		}

		// If still no assessment found, return false
		if ( ! $most_recent_assessment ) {
			return false;
		}

		// Get the actual score and data for the most recent assessment
		$score = get_user_meta( $current_user_id, 'ennu_' . $most_recent_assessment . '_calculated_score', true );
		if ( empty( $score ) ) {
			$score = 7.0; // Default score if not found
		}

		$interpretation = ENNU_Scoring_System::get_score_interpretation( $score );
		
		// Get category scores if available
		$category_scores = get_user_meta( $current_user_id, 'ennu_' . $most_recent_assessment . '_category_scores', true );
		if ( empty( $category_scores ) ) {
			$category_scores = array(
				'overall_health' => $score,
				'wellness' => $score,
				'lifestyle' => $score,
			);
		}

		// Get pillar scores if available
		$pillar_scores = get_user_meta( $current_user_id, 'ennu_' . $most_recent_assessment . '_pillar_scores', true );
		if ( empty( $pillar_scores ) ) {
			$pillar_scores = array(
				'Mind' => $score,
				'Body' => $score,
				'Lifestyle' => $score,
				'Aesthetics' => $score,
			);
		}

		return array(
			'user_id'         => $current_user_id,
			'assessment_type' => $most_recent_assessment,
			'score'           => $score,
			'interpretation'  => $interpretation,
			'category_scores' => $category_scores,
			'pillar_scores'   => $pillar_scores,
			'answers'         => array(), // We don't have the original form data in fallback
		);
	}

	private function get_user_assessments_data( $user_id ) {
		$user_assessments   = array();
		$assessment_configs = $this->all_definitions;
		// Get the actual page mappings from admin settings (selected pages, not created pages)
		$settings = get_option( 'ennu_life_settings', array() );
		$created_pages = $settings['page_mappings'] ?? array();
		$user_gender        = get_user_meta( $user_id, 'ennu_global_gender', true );

		$dashboard_icons = array(
			'hair_assessment'                => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
			'ed_treatment_assessment'        => '🔴', // Test with simple emoji first
			'weight_loss_assessment'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M3 6h18M3 12h18M3 18h18"/><path d="M7 6v12M17 6v12"/></svg>',
			'health_assessment'              => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>',
			'skin_assessment'                => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
			'sleep_assessment'               => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/><circle cx="12" cy="12" r="4"/></svg>',
			'hormone_assessment'             => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/><circle cx="12" cy="12" r="4"/></svg>',
			'menopause_assessment'           => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/><circle cx="12" cy="12" r="4"/></svg>',
			'testosterone_assessment'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/><circle cx="12" cy="12" r="4"/></svg>',
			'health_optimization_assessment' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/><circle cx="12" cy="12" r="4"/></svg>',
		);

		foreach ( $assessment_configs as $key => $config ) {
			// Skip welcome assessment as it has special handling
			if ( 'welcome_assessment' === $key ) {
				continue;
			}

			if ( isset( $config['gender_filter'] ) && ! empty( $user_gender ) && $config['gender_filter'] !== $user_gender ) {
				continue;
			}

			// This is the definitive fix for all details page slugs.
			if ( $key === 'health_optimization_assessment' ) {
				$details_slug = 'health-optimization-details';
			} else {
				$details_slug = str_replace( '_assessment', '-assessment-details', $key );
			}

			$assessment_url = $this->get_assessment_page_url( $key );
			$details_url    = $this->get_page_id_url( $details_slug );

			// Special handling for Health Optimization Assessment (qualitative)
			if ( $key === 'health_optimization_assessment' ) {
				$is_completed = (bool) get_user_meta( $user_id, 'ennu_health_optimization_assessment_symptom_q1', true );
				$score = $is_completed ? 'Qualitative Analysis' : 0; // Health Optimization shows analysis, not numerical score
			} else {
				$score        = get_user_meta( $user_id, 'ennu_' . $key . '_calculated_score', true );
				$is_completed = 'qualitative' === ( $config['assessment_engine'] ?? '' ) ? (bool) get_user_meta( $user_id, 'ennu_' . $key . '_symptom_q1', true ) : ! empty( $score );
			}

			$user_assessments[ $key ] = array(
				'key'         => $key,
				'label'       => $config['title'] ?? ucwords( str_replace( '_', ' ', $key ) ),
				'icon'        => $dashboard_icons[ $key ] ?? '',
				'url'         => $assessment_url,
				'completed'   => $is_completed,
				'score'       => $is_completed ? (float) $score : 0,
				'date'        => $is_completed ? get_user_meta( $user_id, 'ennu_' . $key . '_score_calculated_at', true ) : '',
				'categories'  => $is_completed ? get_user_meta( $user_id, 'ennu_' . $key . '_category_scores', true ) : array(),
				'details_url' => $details_url,
			);
		}
		return $user_assessments;
	}

	public function render_detailed_results_page( $atts, $content = '', $assessment_type = '' ) {
		// Enqueue the assessment details CSS
		wp_enqueue_style( 'ennu-assessment-details-page', ENNU_LIFE_PLUGIN_URL . 'assets/css/assessment-details-page.css', array(), ENNU_LIFE_VERSION );
		
		// Definitive Fix: Add a guard to ensure the assessment type is valid.
		if ( empty( $assessment_type ) || ! isset( $this->all_definitions[ $assessment_type ] ) ) {
			error_log( 'ENNU Dossier Error: Invalid assessment type: ' . $assessment_type );
			return $this->render_error_message( __( 'Invalid assessment type provided.', 'ennulifeassessments' ) );
		}

		// Determine if this is a qualitative assessment
		$is_qualitative = isset( $this->all_definitions[ $assessment_type ]['assessment_engine'] ) &&
						  'qualitative' === $this->all_definitions[ $assessment_type ]['assessment_engine'];

		// All details pages require a logged-in user.
		if ( ! is_user_logged_in() ) {
			$login_url = wp_login_url( get_permalink() );
			return $this->render_error_message( sprintf( __( 'You must be logged in to view these results. <a href="%s">Log in here</a>.', 'ennulifeassessments' ), esc_url( $login_url ) ) );
		}
		$user_id = get_current_user_id();

		// --- ENHANCED DEBUGGING ---
		error_log( 'ENNU Dossier Debug: User ID: ' . $user_id . ', Assessment Type: ' . $assessment_type );
		
		// Use single source of truth for assessment naming
		$canonical_assessment_type = ENNU_Assessment_Constants::get_canonical_key( $assessment_type );
		error_log( 'ENNU Dossier Debug: Canonical assessment type: ' . $canonical_assessment_type );
		
		$submission_flag = get_user_meta( $user_id, ENNU_Assessment_Constants::get_full_meta_key( $canonical_assessment_type, 'assessment_submitted' ), true );
		error_log( 'ENNU Dossier Debug: Submission flag: ' . ($submission_flag ? $submission_flag : 'NOT SET') );
		
		// Check for completion flag using single source of truth
		$completion_flag = get_user_meta( $user_id, ENNU_Assessment_Constants::get_full_meta_key( $canonical_assessment_type, 'completed' ), true );
		error_log( 'ENNU Dossier Debug: Completion flag: ' . ($completion_flag ? $completion_flag : 'NOT SET') );
		
		// Check for calculated score using single source of truth
		$calculated_score = get_user_meta( $user_id, ENNU_Assessment_Constants::get_full_meta_key( $canonical_assessment_type, 'calculated_score' ), true );
		error_log( 'ENNU Dossier Debug: Calculated score: ' . ($calculated_score ? $calculated_score : 'NOT SET') );
		
		// Check for score calculation timestamp using single source of truth
		$score_timestamp = get_user_meta( $user_id, ENNU_Assessment_Constants::get_full_meta_key( $canonical_assessment_type, 'score_calculated_at' ), true );
		error_log( 'ENNU Dossier Debug: Score timestamp: ' . ($score_timestamp ? $score_timestamp : 'NOT SET') );
		
		// Also check old format keys for debugging
		$old_score_key = 'ennu_' . $assessment_type . '_calculated_score';
		$old_score = get_user_meta( $user_id, $old_score_key, true );
		error_log( 'ENNU Dossier Debug: Old score key: ' . $old_score_key . ' = ' . ($old_score ? $old_score : 'NOT SET') );

		// --- ROUTE TO THE CORRECT ENGINE ---
		if ( $is_qualitative ) {
			// Qualitative Engine Flow
			$symptom_data = ENNU_Scoring_System::get_symptom_data_for_user( $user_id );
			if ( empty( $symptom_data ) ) {
				error_log( 'ENNU Dossier Error: No symptom data found for qualitative assessment' );
				return $this->render_error_message( __( 'You have not yet completed this assessment.', 'ennulifeassessments' ) );
			}
			$report_data = ENNU_Scoring_System::get_health_optimization_report_data( $symptom_data );
			ob_start();
			ennu_load_template( 'health-optimization-results.php', $report_data );
			return ob_get_clean();

		} else {
			// Quantitative Engine Flow using single source of truth
			$score = get_user_meta( $user_id, ENNU_Assessment_Constants::get_full_meta_key( $canonical_assessment_type, 'calculated_score' ), true );
			
			// Check old format as fallback if new format doesn't have data
			if ( ! $score ) {
				$old_score_key = 'ennu_' . $assessment_type . '_calculated_score';
				$score = get_user_meta( $user_id, $old_score_key, true );
				if ( $score ) {
					error_log( 'ENNU Dossier Debug: Found score in old format, migrating to new format' );
					// Migrate to new format
					update_user_meta( $user_id, ENNU_Assessment_Constants::get_full_meta_key( $canonical_assessment_type, 'calculated_score' ), $score );
				}
			}
			
			if ( ! $score ) {
				error_log( 'ENNU Dossier Error: No calculated score found for assessment type: ' . $assessment_type . ' (canonical: ' . $canonical_assessment_type . ')' );
				error_log( 'ENNU Dossier Error: User meta key checked: ' . ENNU_Assessment_Constants::get_full_meta_key( $canonical_assessment_type, 'calculated_score' ) );
				
				// Check if assessment is actually completed but score is missing
				$is_completed = $completion_flag || $submission_flag;
				if ( $is_completed ) {
					error_log( 'ENNU Dossier Debug: Assessment appears to be completed but score is missing. This might be a data inconsistency.' );
					$error_message = sprintf(
						__( 'Your assessment data appears to be incomplete. Please contact support to resolve this issue.', 'ennulifeassessments' )
					);
				} else {
					// Provide helpful error message with options
					$dashboard_url = $this->get_dashboard_url();
					$assessment_url = $this->get_assessment_page_url( $assessment_type );
					
					$error_message = sprintf(
						__( 'You have not yet completed this assessment.<br><br>If you believe you have already completed this assessment, please contact support.', 'ennulifeassessments' )
					);
				}
				
				return $this->render_error_message( $error_message );
			}

			$data = $this->get_quantitative_dossier_data( $user_id, $assessment_type );

			// Failsafe if data retrieval fails
			if ( empty( $data ) ) {
				error_log( 'ENNU Dossier Error: Could not retrieve dossier data for assessment: ' . $assessment_type );
				return $this->render_error_message( __( 'Could not retrieve assessment data.', 'ennulifeassessments' ) );
			}

			// Add the shortcode instance to the data so templates can use get_page_id_url
			$data['shortcode_instance'] = $this;

			// Load biomarkers CSS and JavaScript for the biomarkers section
			wp_enqueue_style( 'ennu-theme-system', ENNU_LIFE_PLUGIN_URL . 'assets/css/theme-system.css', array(), ENNU_LIFE_VERSION );
			wp_enqueue_script( 'ennu-theme-manager', ENNU_LIFE_PLUGIN_URL . 'assets/js/theme-manager.js', array(), ENNU_LIFE_VERSION, true );
			wp_enqueue_script( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/js/user-dashboard.js', array( 'jquery', 'ennu-theme-manager' ), ENNU_LIFE_VERSION, true );

			ob_start();
			ennu_load_template( 'assessment-details-page.php', $data );
			return ob_get_clean();
		}
	}

	private function get_quantitative_dossier_data( $user_id, $assessment_type ) {
		$current_user = get_userdata( $user_id );
		if ( ! $current_user ) {
			return array(); // Definitive Fix: Prevent fatal error if user doesn't exist.
		}

		// Use single source of truth for all meta keys
		$canonical_assessment_type = ENNU_Assessment_Constants::get_canonical_key( $assessment_type );
		$score           = get_user_meta( $user_id, ENNU_Assessment_Constants::get_full_meta_key( $canonical_assessment_type, 'calculated_score' ), true );
		$interpretation  = get_user_meta( $user_id, ENNU_Assessment_Constants::get_full_meta_key( $canonical_assessment_type, 'score_interpretation' ), true );
		$category_scores = get_user_meta( $user_id, ENNU_Assessment_Constants::get_full_meta_key( $canonical_assessment_type, 'category_scores' ), true );
		$score_history   = get_user_meta( $user_id, ENNU_Assessment_Constants::get_full_meta_key( $canonical_assessment_type, 'historical_scores' ), true );

		$assessment_type_slug = str_replace( '_assessment', '', $assessment_type );
		$dob                  = get_user_meta( $user_id, 'ennu_global_date_of_birth', true );
		$age                  = $dob ? ( new DateTime() )->diff( new DateTime( $dob ) )->y : null;
		$gender               = get_user_meta( $user_id, 'ennu_global_gender', true );

		$dashboard_url = $this->get_page_id_url( 'dashboard' );
		$retake_url    = $this->get_assessment_page_url( $assessment_type );

		$insights_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/dashboard-insights.php';
		$insights      = file_exists( $insights_file ) ? require $insights_file : array();

		$content_config_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/results-content.php';
		$content_config      = file_exists( $content_config_file ) ? require $content_config_file : array();
		$content_data        = $content_config[ $assessment_type ] ?? $content_config['default'];
		$interpretation_key  = isset( $interpretation['level'] ) ? strtolower( $interpretation['level'] ) : 'fair';
		$result_content      = $content_data['score_ranges'][ $interpretation_key ] ?? $content_data['score_ranges']['fair'];

		// Calculate pillar scores using the proper scoring system
		$pillar_scores = ENNU_Scoring_System::calculate_average_pillar_scores( $user_id );
		
		// Fallback to assessment-specific pillar scores if scoring system doesn't have data
		if ( empty( $pillar_scores ) ) {
			$pillar_scores_meta = get_user_meta( $user_id, ENNU_Assessment_Constants::get_full_meta_key( $canonical_assessment_type, 'pillar_scores' ), true );
			$pillar_scores = ! empty( $pillar_scores_meta ) && is_array( $pillar_scores_meta ) ? $pillar_scores_meta : array();
		}

		$pillar_colors = array(
			'mind'       => '#8e44ad',
			'body'       => '#2980b9',
			'lifestyle'  => '#27ae60',
			'aesthetics' => '#f39c12',
		);

		$deep_dive_content = array();
		if ( is_array( $category_scores ) ) {
			foreach ( $category_scores as $category => $cat_score ) {
				$deep_dive_content[ $category ] = array(
					'explanation' => 'This category reflects your status in ' . esc_html( $category ) . '.',
					'user_answer' => '',
					'action_plan' => array(),
				);
			}
		}

		// Format score history for JavaScript
		$formatted_score_history = array();
		if ( is_array( $score_history ) && ! empty( $score_history ) ) {
			foreach ( $score_history as $entry ) {
				if ( isset( $entry['date'] ) && isset( $entry['score'] ) ) {
					$formatted_score_history[] = array(
						'date'  => $entry['date'],
						'score' => (float) $entry['score'],
					);
				}
			}
		}
		
		// --- DOSSIER CHART FIX: Generate sample history for existing assessments ---
		if ( empty( $formatted_score_history ) && $score ) {
			// Create a sample history entry for the current score if no history exists
			$formatted_score_history[] = array(
				'date'  => current_time( 'Y-m-d H:i:s' ),
				'score' => (float) $score,
			);
			error_log( 'ENNU Dossier Chart: Generated sample history for assessment ' . $assessment_type );
		}
		// --- END DOSSIER CHART FIX ---

		// Enqueue Chart.js and localize data
		wp_enqueue_script( 'chartjs', ENNU_LIFE_PLUGIN_URL . 'assets/js/chart.umd.js', array(), '4.4.1', true );
		wp_enqueue_script( 'chartjs-adapter-date-fns', 'https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js', array( 'chartjs' ), '1.1.0', true );
		wp_enqueue_script( 'ennu-assessment-details', ENNU_LIFE_PLUGIN_URL . 'assets/js/assessment-details.js', array( 'jquery', 'chartjs', 'chartjs-adapter-date-fns' ), ENNU_LIFE_VERSION, true );

		wp_localize_script(
			'ennu-assessment-details',
			'assessmentDetailsData',
			array(
				'scoreHistory'   => $formatted_score_history,
				'assessmentType' => esc_js( ucwords( str_replace( '_', ' ', $assessment_type_slug ) ) ),
			)
		);

		return compact(
			'current_user',
			'age',
			'gender',
			'assessment_type_slug',
			'pillar_scores',
			'pillar_colors',
			'category_scores',
			'score_history',
			'deep_dive_content',
			'score',
			'dashboard_url',
			'retake_url',
			'result_content',
			'insights'
		) + array(
			'assessment_type' => $assessment_type,
			'overall_score' => $score,
			'assessment_title' => ucwords( str_replace( '_', ' ', $assessment_type_slug ) ),
			'display_name' => $current_user->display_name ?? '',
			'height' => get_user_meta( $user_id, 'ennu_global_height', true ),
			'weight' => get_user_meta( $user_id, 'ennu_global_weight', true ),
			'bmi' => get_user_meta( $user_id, 'ennu_global_bmi', true ),
			'dashboard_button_url' => $dashboard_url,
			'details_button_url' => $retake_url,
			'assessment_date' => get_user_meta( $user_id, 'ennu_' . $assessment_type . '_score_calculated_at', true ) ?: current_time( 'Y-m-d H:i:s' )
		);
	}

	/**
	 * Helper function to get the URL for the main assessment page.
	 * @param string $assessment_type
	 * @return string
	 */
	private function get_assessment_page_url( $assessment_type ) {
		$slug = str_replace( '_', '-', $assessment_type );
		return $this->get_page_id_url( $slug );
	}

	/**
	 * Helper function to get the CTA URL for an assessment results page.
	 * @param string $assessment_type
	 * @return string
	 */
	public function get_assessment_cta_url( $assessment_type ) {
		// Convert assessment type to slug format
		$slug = str_replace( '_', '-', $assessment_type );

		// Try multiple consultation URL patterns in order of preference
		$consultation_patterns = array(
			"assessments/{$slug}/consultation",
			"book-{$slug}-consultation",
			"{$slug}-consultation",
			'consultation',
		);

		foreach ( $consultation_patterns as $pattern ) {
			$url = $this->get_page_id_url( $pattern );
			if ( ! empty( $url ) && $url !== home_url( "/{$pattern}/" ) ) {
				return $url;
			}
		}

		// Final fallback to generic consultation page
		return $this->get_page_id_url( 'call' );
	}

	/**
	 * Get thank you content data for assessment type
	 */
	private function get_thank_you_content( $assessment_type ) {
		$content_map = array(
			'hair_assessment'         => array(
				'title'            => 'Your Hair Assessment is Complete!',
				'message'          => 'Thank you for completing your hair health assessment. Our hair restoration specialists will review your responses to create a personalized hair growth plan tailored to your specific needs.',
				'next_steps'       => 'Schedule a consultation with our hair restoration specialists to discuss your personalized treatment options and get started on your hair growth journey.',
				'benefits'         => array(
					'Personalized hair restoration strategy',
					'Advanced treatment options (PRP, transplants, medications)',
					'Hair growth timeline and realistic expectations',
					'Customized pricing for your treatment plan',
				),
				'button_text'      => 'Book Your Hair Consultation',
				'consultation_url' => $this->get_page_id_url( 'book-hair-consultation' ),
				'contact_label'    => 'Questions about hair restoration?',
				'phone'            => '+1-800-ENNU-HAIR',
				'phone_display'    => '(800) ENNU-HAIR',
				'email'            => 'hair@ennulife.com',
				'icon'             => '',
				'color'            => '#667eea',
				'bg_color'         => '#f8f9ff',
				'gradient'         => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
				'shadow'           => 'rgba(102, 126, 234, 0.3)',
				'info_bg'          => 'linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%)',
			),
			'ed_treatment_assessment' => array(
				'title'            => 'Your ED Assessment is Complete!',
				'message'          => 'Thank you for taking this important step. Our medical professionals will confidentially review your responses to recommend the most effective ED treatment options for you.',
				'next_steps'       => 'Schedule a confidential consultation with our medical specialists to discuss your personalized treatment options in a discreet and professional environment.',
				'benefits'         => array(
					'Confidential medical consultation',
					'FDA-approved treatment options',
					'Discreet and professional care',
					'Personalized treatment recommendations',
				),
				'button_text'      => 'Book Your Confidential Call',
				'consultation_url' => $this->get_page_id_url( 'book-ed-consultation' ),
				'contact_label'    => 'Confidential questions?',
				'phone'            => '+1-800-ENNU-MENS',
				'phone_display'    => '(800) ENNU-MENS',
				'email'            => 'confidential@ennulife.com',
				'icon'             => '',
				'color'            => '#f093fb',
				'bg_color'         => '#fef7ff',
				'gradient'         => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
				'shadow'           => 'rgba(240, 147, 251, 0.3)',
				'info_bg'          => 'linear-gradient(135deg, #fef7ff 0%, #fdf2ff 100%)',
				'extra_section'    => '<div class="privacy-notice" style="margin-top: 30px; padding: 20px; background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%); border-radius: 8px; border-left: 4px solid #28a745; font-size: 0.95em;"><p><strong>🔒 Your Privacy is Protected:</strong> All consultations are completely confidential and HIPAA compliant. Your information is secure and private.</p></div>',
			),
			'weight_loss_assessment'  => array(
				'title'            => 'Your Weight Loss Assessment is Complete!',
				'message'          => 'Thank you for completing your weight management assessment. Our team will create a comprehensive weight loss plan designed specifically for your goals and lifestyle.',
				'next_steps'       => 'Schedule a consultation with our weight loss specialists to discuss your personalized treatment options and start your transformation journey today.',
				'benefits'         => array(
					'Customized weight loss strategy',
					'Medical weight loss options (Semaglutide, etc.)',
					'Nutritional guidance and meal planning',
					'Long-term success and maintenance plan',
				),
				'button_text'      => 'Book Your Weight Loss Call',
				'consultation_url' => $this->get_page_id_url( 'book-weight-loss-consultation' ),
				'contact_label'    => 'Questions about weight loss?',
				'phone'            => '+1-800-ENNU-SLIM',
				'phone_display'    => '(800) ENNU-SLIM',
				'email'            => 'weightloss@ennulife.com',
				'icon'             => '',
				'color'            => '#4facfe',
				'bg_color'         => '#f0faff',
				'gradient'         => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
				'shadow'           => 'rgba(79, 172, 254, 0.3)',
				'info_bg'          => 'linear-gradient(135deg, #f0faff 0%, #e6f7ff 100%)',
			),
			'health_assessment'       => array(
				'title'            => 'Your Health Assessment is Complete!',
				'message'          => 'Thank you for completing your comprehensive health evaluation. Our healthcare team will review your responses to create a personalized wellness plan for optimal health.',
				'next_steps'       => 'Schedule a consultation with our healthcare specialists to discuss your personalized wellness plan and optimize your overall health.',
				'benefits'         => array(
					'Comprehensive health evaluation',
					'Preventive care recommendations',
					'Hormone optimization options',
					'Ongoing health monitoring plan',
				),
				'button_text'      => 'Book Your Health Consultation',
				'consultation_url' => $this->get_page_id_url( 'book-health-consultation' ),
				'contact_label'    => 'Questions about health optimization?',
				'phone'            => '+1-800-ENNU-HLTH',
				'phone_display'    => '(800) ENNU-HLTH',
				'email'            => 'health@ennulife.com',
				'icon'             => '',
				'color'            => '#fa709a',
				'bg_color'         => '#fff8fb',
				'gradient'         => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
				'shadow'           => 'rgba(250, 112, 154, 0.3)',
				'info_bg'          => 'linear-gradient(135deg, #fff8fb 0%, #fef5f8 100%)',
			),
			'skin_assessment'         => array(
				'title'            => 'Your Skin Assessment is Complete!',
				'message'          => 'Thank you for completing your skin health evaluation. Our dermatology specialists will review your responses to create a personalized skincare and treatment plan.',
				'next_steps'       => 'Schedule a consultation with our skincare specialists to discuss your personalized treatment options and achieve your skin goals.',
				'benefits'         => array(
					'Personalized skincare regimen',
					'Advanced treatments (Botox, fillers, laser)',
					'Professional product recommendations',
					'Skin rejuvenation timeline',
				),
				'button_text'      => 'Book Your Skin Consultation',
				'consultation_url' => $this->get_page_id_url( 'book-skin-consultation' ),
				'contact_label'    => 'Questions about skincare?',
				'phone'            => '+1-800-ENNU-SKIN',
				'phone_display'    => '(800) ENNU-SKIN',
				'email'            => 'skin@ennulife.com',
				'icon'             => '',
				'color'            => '#a8edea',
				'bg_color'         => '#f0fffe',
				'gradient'         => 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
				'shadow'           => 'rgba(168, 237, 234, 0.3)',
				'info_bg'          => 'linear-gradient(135deg, #f0fffe 0%, #edfffe 100%)',
			),
		);

		return isset( $content_map[ $assessment_type ] ) ? $content_map[ $assessment_type ] : $content_map['health_assessment'];
	}

	/**
	 * Adjust color brightness
	 *
	 * @param string $hex_color Hex color code
	 * @param int $percent Brightness adjustment percentage
	 * @return string
	 */
	private function adjust_color_brightness( $hex_color, $percent ) {
		$hex_color = ltrim( $hex_color, '#' );

		if ( strlen( $hex_color ) === 3 ) {
			$hex_color = str_repeat( substr( $hex_color, 0, 1 ), 2 ) .
						str_repeat( substr( $hex_color, 1, 1 ), 2 ) .
						str_repeat( substr( $hex_color, 2, 1 ), 2 );
		}

		$r = hexdec( substr( $hex_color, 0, 2 ) );
		$g = hexdec( substr( $hex_color, 2, 2 ) );
		$b = hexdec( substr( $hex_color, 4, 2 ) );

		$r = max( 0, min( 255, $r + ( $r * $percent / 100 ) ) );
		$g = max( 0, min( 255, $g + ( $g * $percent / 100 ) ) );
		$b = max( 0, min( 255, $b + ( $b * $percent / 100 ) ) );

		return '#' . str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT ) .
					str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT ) .
					str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );
	}

	/**
	 * Render error message
	 *
	 * @param string $message Error message
	 * @return string
	 */
	private function render_error_message( $message ) {
		$dashboard_url = $this->get_dashboard_url();
		$assessment_url = $this->get_assessment_page_url( 'health_optimization_assessment' ); // Default assessment type
		ob_start();
		?>
		<div class="ennu-user-dashboard"> <!-- Use the main dashboard class for consistent styling -->
			<div class="dossier-grid" style="grid-template-columns: 1fr; max-width: 600px; margin: auto;">
				<main class="dossier-main-content">
					<div class="ennu-error" style="text-align: center;">
						<div class="empty-state-icon" style="font-size: 40px; font-weight: bold; color: #ffc107; background: rgba(255, 193, 7, 0.1); width: 80px; height: 80px; line-height: 80px; border-radius: 50%; margin: 0 auto 20px; border: 1px solid #ffc107;">!</div>
						<h2 class="empty-state-title" style="font-size: 28px; font-weight: 700; color: #fff; margin-bottom: 15px;">
							<?php echo esc_html__( 'Assessment Not Available', 'ennulifeassessments' ); ?>
						</h2>
						<div class="empty-state-message" style="font-size: 16px; color: var(--text-light); line-height: 1.6; margin-bottom: 30px;">
							<?php echo wp_kses_post( $message ); ?>
						</div>
						<div class="empty-state-actions" style="display: flex; justify-content: center; gap: 15px;">
							<a href="<?php echo esc_url( $assessment_url ); ?>" class="action-button button-report" style="text-align: center; background-color: var(--accent-primary); color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease; font-size: 0.9rem;"><?php echo esc_html__( 'Take Assessment Now', 'ennulifeassessments' ); ?></a>
							<a href="<?php echo esc_url( $dashboard_url ); ?>" class="action-button button-report" style="text-align: center; background-color: var(--accent-primary); color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease; font-size: 0.9rem;"><?php echo esc_html__( 'Return to My Dashboard', 'ennulifeassessments' ); ?></a>
						</div>
					</div>
				</main>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public function render_user_dashboard( $atts = array() ) {
		if ( ! is_user_logged_in() ) {
			$login_url        = wp_login_url( get_permalink() );
			$registration_url = wp_registration_url();
			ob_start();
			ennu_load_template( 'user-dashboard-logged-out.php', compact( 'login_url', 'registration_url' ) );
			return ob_get_clean();
		}

		// Enqueue necessary scripts and styles.
		wp_enqueue_style( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/css/user-dashboard.css', array(), ENNU_LIFE_VERSION );
		wp_enqueue_style( 'ennu-modal-system', ENNU_LIFE_PLUGIN_URL . 'assets/css/modal-system.css', array(), ENNU_LIFE_VERSION );
		wp_enqueue_script( 'chartjs', ENNU_LIFE_PLUGIN_URL . 'assets/js/chart.umd.js', array(), '4.4.1', true );
		wp_enqueue_script( 'chartjs-adapter-date-fns', 'https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js', array( 'chartjs' ), '1.1.0', true );
		wp_enqueue_script( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/js/user-dashboard.js', array( 'jquery', 'chartjs', 'chartjs-adapter-date-fns' ), ENNU_LIFE_VERSION . '.' . time(), true );

		$user_id = get_current_user_id();

		// Fetch all required data.
		$current_user = wp_get_current_user();

		// Get user assessments data first
		$user_assessments = $this->get_user_assessments_data( $user_id );

		// Calculate ENNU Life score from completed assessments (Dashboard uses simple calculation)
		$ennu_life_score       = 0;
		$completed_assessments = 0;
		$total_score           = 0;

		foreach ( $user_assessments as $assessment ) {
			if ( $assessment['completed'] && $assessment['score'] > 0 ) {
				$total_score += $assessment['score'];
				$completed_assessments++;
			}
		}

		if ( $completed_assessments > 0 ) {
			$ennu_life_score = round( $total_score / $completed_assessments, 1 );
		}

		// Update the ENNU Life score in user meta
		update_user_meta( $user_id, 'ennu_life_score', $ennu_life_score );

		// Calculate pillar scores using the proper scoring system
		$pillar_scores = ENNU_Scoring_System::calculate_average_pillar_scores( $user_id );
		$average_pillar_scores = $pillar_scores; // Assign to the variable expected by the template

		$dob                = get_user_meta( $user_id, 'ennu_global_date_of_birth', true );
		
		// Use Age Management System to calculate age data from DOB
		if ( class_exists( 'ENNU_Age_Management_System' ) && ! empty( $dob ) ) {
			$age_data = ENNU_Age_Management_System::get_user_age_data( $user_id );
			$age = $age_data['exact_age'] ?? '';
			$age_range = $age_data['age_range'] ?? '';
			$age_category = $age_data['age_category'] ?? '';
		} else {
			// Fallback to stored values if Age Management System not available
			$age = get_user_meta( $user_id, 'ennu_global_exact_age', true );
			$age_range = get_user_meta( $user_id, 'ennu_global_age_range', true );
			$age_category = get_user_meta( $user_id, 'ennu_global_age_category', true );
		}
		
		$gender             = get_user_meta( $user_id, 'ennu_global_gender', true );
		$height_weight_data = get_user_meta( $user_id, 'ennu_global_height_weight', true );
		$height             = is_array( $height_weight_data ) && ! empty( $height_weight_data['ft'] ) ? "{$height_weight_data['ft']}' {$height_weight_data['in']}\"" : null;
		// Handle both 'weight' and 'lbs' keys for backward compatibility
		$weight_value = is_array( $height_weight_data ) ? ( isset( $height_weight_data['weight'] ) ? $height_weight_data['weight'] : ( isset( $height_weight_data['lbs'] ) ? $height_weight_data['lbs'] : null ) ) : null;
		$weight             = $weight_value ? $weight_value . ' lbs' : null;
		$bmi                = get_user_meta( $user_id, 'ennu_calculated_bmi', true );
		$score_history      = get_user_meta( $user_id, 'ennu_life_score_history', true );
		$bmi_history        = get_user_meta( $user_id, 'ennu_bmi_history', true );

		// Enhanced score history validation and processing
		if ( is_array( $score_history ) ) {
			$score_history = array_filter( $score_history, function( $entry ) {
				return isset( $entry['date'], $entry['score'] ) 
					&& ! empty( $entry['date'] ) 
					&& is_numeric( $entry['score'] )
					&& $entry['score'] >= 0 
					&& $entry['score'] <= 10;
			});
			
			// Sort by date
			usort( $score_history, function( $a, $b ) {
				return strtotime( $a['date'] ) - strtotime( $b['date'] );
			});
			
			// Ensure we have at least some data for demonstration
			if ( empty( $score_history ) ) {
				$current_score = get_user_meta( $user_id, 'ennu_life_score', true );
				if ( ! empty( $current_score ) && is_numeric( $current_score ) ) {
					$score_history = array(
						array(
							'score' => floatval( $current_score ),
							'date'  => current_time( 'mysql' ),
							'timestamp' => time()
						)
					);
				}
			}
		} else {
			$score_history = array();
		}

		$insights         = include ENNU_LIFE_PLUGIN_PATH . 'includes/config/dashboard/insights.php';
		$user_assessments = $this->get_user_assessments_data( $user_id );

		// --- NEW: Health Optimization Report Data Processing using the new Calculator ---
		$health_opt_calculator  = new ENNU_Health_Optimization_Calculator( $user_id, $this->all_definitions );
		$triggered_vectors      = $health_opt_calculator->get_triggered_vectors();
		$recommended_biomarkers = $health_opt_calculator->get_biomarker_recommendations();

		// Get symptom data from centralized symptoms manager
		if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
			$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $user_id );
			$symptom_analytics = ENNU_Centralized_Symptoms_Manager::get_symptom_analytics( $user_id );
		} else {
			$centralized_symptoms = array();
			$symptom_analytics = array(
				'total_symptoms' => 0,
				'unique_symptoms' => 0,
				'assessments_with_symptoms' => 0,
				'most_common_category' => 'None',
				'most_severe_symptoms' => array(),
				'most_frequent_symptoms' => array(),
				'symptom_trends' => array( 'trend' => 'stable', 'change' => 0 )
			);
		}

		// Get biomarker data with proper flagging
		$biomarker_data = ENNU_Biomarker_Manager::get_user_biomarkers( $user_id );
		$flagged_biomarkers = array();
		
		if ( class_exists( 'ENNU_Biomarker_Flag_Manager' ) ) {
			$flag_manager = new ENNU_Biomarker_Flag_Manager();
			$flagged_biomarkers = $flag_manager->get_flagged_biomarkers( $user_id );
		}

		// Process biomarker data to include flagging information
		$processed_biomarkers = array();
		if ( ! empty( $biomarker_data ) && is_array( $biomarker_data ) ) {
			foreach ( $biomarker_data as $biomarker_name => $data ) {
				$processed_biomarkers[ $biomarker_name ] = array(
					'biomarker_id' => $biomarker_name,
					'display_name' => ucwords( str_replace( '_', ' ', $biomarker_name ) ),
					'current_value' => $data['value'] ?? '',
					'target_value' => $data['target'] ?? '',
					'unit' => $data['unit'] ?? '',
					'percentage_position' => $data['percentage_position'] ?? 50,
					'target_position' => $data['target_position'] ?? 50,
					'has_flags' => ! empty( $flagged_biomarkers ),
					'flags' => $flagged_biomarkers,
					'has_admin_override' => isset( $data['admin_override'] ) && $data['admin_override'],
					'range_data' => $data['range_data'] ?? array(),
					'last_updated' => $data['last_updated'] ?? current_time( 'mysql' )
				);
			}
		}

		$health_map_config_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/biomarker-map.php';
		$biomarker_map          = file_exists( $health_map_config_file ) ? require $health_map_config_file : array();

		// --- Definitive Data Pre-processing ---
		$processed_health_map = array();
		foreach ( $biomarker_map as $vector => $biomarkers ) {
			$processed_health_map[ $vector ] = array(
				'symptoms'                     => array(), // This can be populated if needed in the future
				'biomarkers'                   => $biomarkers,
				'triggered_symptoms_count'     => isset( $triggered_vectors[ $vector ] ) ? count( $triggered_vectors[ $vector ]['instances'] ) : 0,
				'recommended_biomarkers_count' => isset( $triggered_vectors[ $vector ] ) ? count( array_intersect( $biomarkers, $recommended_biomarkers ) ) : 0,
			);
		}

		$health_optimization_report = array(
			'health_map'             => $processed_health_map,
			'user_symptoms'          => array_keys( $triggered_vectors ),
			'triggered_vectors'      => array_keys( $triggered_vectors ),
			'recommended_biomarkers' => $recommended_biomarkers,
		);
		// --- End Data Pre-processing ---

		// --- MASTER DEBUG: Log the final data before sending to template ---
		error_log( '[ENNU DEBUG] Final Health Optimization Report Data: ' . print_r( $health_optimization_report, true ) );

		// Remove the forced demo block that was overriding real data

		// No sample data - show empty state if no real data exists
		if ( empty( $score_history ) ) {
			$score_history = array();
		}

		// DEBUG: Log score history data before passing to JavaScript
		error_log( '[ENNU DEBUG] Score History before wp_localize_script: ' . print_r( $score_history, true ) );
		error_log( '[ENNU DEBUG] BMI History before wp_localize_script: ' . print_r( $bmi_history, true ) );

		wp_localize_script(
			'ennu-user-dashboard',
			'dashboardData',
			array(
				'score_history' => array_values( $score_history ),
				'bmi_history'   => is_array( $bmi_history ) ? array_values( $bmi_history ) : array(),
			)
		);

		// DEBUG: Log what was actually passed to JavaScript
		error_log( '[ENNU DEBUG] dashboardData passed to JavaScript: ' . print_r( array(
			'score_history' => array_values( $score_history ),
			'bmi_history'   => is_array( $bmi_history ) ? array_values( $bmi_history ) : array(),
		), true ) );

		// Get user health goals
		$health_goals_data = $this->get_user_health_goals( $user_id );

		// DEBUG: Log user assessments data
		error_log( '[ENNU DEBUG] User Assessments Data: ' . print_r( $user_assessments, true ) );
		error_log( '[ENNU DEBUG] ENNU Life Score: ' . $ennu_life_score );
		error_log( '[ENNU DEBUG] Average Pillar Scores: ' . print_r( $average_pillar_scores, true ) );
		
		$data = compact(
			'current_user',
			'ennu_life_score',
			'average_pillar_scores',
			'age',
			'age_range',
			'age_category',
			'gender',
			'dob',
			'user_assessments',
			'score_history',
			'height',
			'weight',
			'bmi',
			'insights',
			'health_optimization_report',
			'health_goals_data'
		);

		// Add the shortcode instance to the data so templates can use get_page_id_url
		$data['shortcode_instance'] = $this;

		// DEBUG: Log the critical user data before sending to template
		error_log( '[ENNU DEBUG] User Dashboard Data - Age: ' . ( $age ?? 'NULL' ) . ', Age Range: ' . ( $age_range ?? 'NULL' ) . ', Age Category: ' . ( $age_category ?? 'NULL' ) . ', Gender: ' . ( $gender ?? 'NULL' ) . ', Height: ' . ( $height ?? 'NULL' ) . ', Weight: ' . ( $weight ?? 'NULL' ) . ', BMI: ' . ( $bmi ?? 'NULL' ) );
		error_log( '[ENNU DEBUG] User Dashboard Data - DOB: ' . ( $dob ?? 'NULL' ) );
		error_log( '[ENNU DEBUG] User Dashboard Data - User ID: ' . $user_id );
		error_log( '[ENNU DEBUG] User Dashboard Data - Current User: ' . ( $current_user ? $current_user->ID : 'NULL' ) );

		ob_start();
		ennu_load_template( 'user-dashboard.php', $data );
		return ob_get_clean();
	}



	public static function get_trinity_pillar_map() {
		return array(
			'mind'       => array( 'Psychological Factors', 'Treatment Motivation' ),
			'body'       => array( 'Condition Severity', 'Medical Factors' ),
			'lifestyle'  => array( 'Physical Health', 'Treatment History', 'Progression Timeline', 'Symptom Pattern', 'Sleep & Recovery', 'Preventive Health', 'Lifestyle Choices', 'Environmental Factors', 'Skincare Habits' ),
			'aesthetics' => array( 'Hair Health Status', 'Primary Skin Issue', 'Skin Characteristics', 'Motivation & Goals', 'Current Status' ),
		);
	}
	
	/**
	 * Check if pillar has valid data for dashboard display
	 *
	 * @param string $pillar The pillar name
	 * @param array $pillar_scores The pillar scores array
	 * @return bool True if pillar has valid data
	 */
	public static function pillar_has_valid_data_for_dashboard( $pillar, $pillar_scores ) {
		return isset( $pillar_scores[ $pillar ] ) && is_numeric( $pillar_scores[ $pillar ] ) && $pillar_scores[ $pillar ] > 0;
	}
	
	/**
	 * Get mind score if valid data exists
	 *
	 * @param int $user_id The user ID
	 * @param array $pillar_scores The pillar scores array
	 * @return float|null The mind score or null if no valid data
	 */
	private static function get_mind_score_if_valid( $user_id, $pillar_scores ) {
		if ( isset( $pillar_scores['Mind'] ) && is_numeric( $pillar_scores['Mind'] ) && $pillar_scores['Mind'] > 0 ) {
			return $pillar_scores['Mind'];
		}
		return null;
	}
	
	/**
	 * Get body score if valid data exists
	 *
	 * @param int $user_id The user ID
	 * @param array $pillar_scores The pillar scores array
	 * @return float|null The body score or null if no valid data
	 */
	private static function get_body_score_if_valid( $user_id, $pillar_scores ) {
		if ( isset( $pillar_scores['Body'] ) && is_numeric( $pillar_scores['Body'] ) && $pillar_scores['Body'] > 0 ) {
			return $pillar_scores['Body'];
		}
		return null;
	}
	
	/**
	 * Get lifestyle score if valid data exists
	 *
	 * @param int $user_id The user ID
	 * @param array $pillar_scores The pillar scores array
	 * @return float|null The lifestyle score or null if no valid data
	 */
	private static function get_lifestyle_score_if_valid( $user_id, $pillar_scores ) {
		if ( isset( $pillar_scores['Lifestyle'] ) && is_numeric( $pillar_scores['Lifestyle'] ) && $pillar_scores['Lifestyle'] > 0 ) {
			return $pillar_scores['Lifestyle'];
		}
		return null;
	}

	/**
	 * Get aesthetics score if valid data exists
	 *
	 * @param int $user_id The user ID
	 * @param array $pillar_scores The pillar scores array
	 * @return float|null The aesthetics score or null if no valid data
	 */
	private static function get_aesthetics_score_if_valid( $user_id, $pillar_scores ) {
		if ( isset( $pillar_scores['Aesthetics'] ) && is_numeric( $pillar_scores['Aesthetics'] ) && $pillar_scores['Aesthetics'] > 0 ) {
			return $pillar_scores['Aesthetics'];
		}
		return null;
	}

	/**
	 * Get the URL for the user dashboard page.
	 *
	 * @return string The dashboard URL.
	 */
	public function get_dashboard_url() {
		return $this->get_page_id_url( 'dashboard' );
	}

	public function render_health_optimization_results( $atts ) {
		$results_token     = isset( $_GET['results_token'] ) ? sanitize_text_field( $_GET['results_token'] ) : null;
		$results_transient = $results_token ? $this->_get_manual_transient( 'ennu_results_' . $results_token ) : false;

		if ( ! $results_transient ) {
			ob_start();
			$data = array( 'shortcode_instance' => $this );
			ennu_load_template( 'assessment-results-expired.php', $data );
			return ob_get_clean();
		}

		$this->_delete_manual_transient( 'ennu_results_' . $results_token );

		$report_data = ENNU_Scoring_System::get_health_optimization_report_data( $results_transient );

		// Add the shortcode instance to the data so templates can use get_page_id_url
		$report_data['shortcode_instance'] = $this;

		ob_start();
		ennu_load_template( 'health-optimization-results.php', $report_data );
		return ob_get_clean();
	}

	/**
	 * Render smart contact form with intelligent logic
	 * Shows contact form only when needed, with only missing fields
	 */
	private function render_smart_contact_form( $assessment_type, $question_number, $config, $current_user_data = array() ) {
		$needs_contact_form = $this->user_needs_contact_form( $current_user_data );
		$auto_submit_ready  = ! $needs_contact_form;

		$output = '';

		// Always add hidden fields for logged-in users to ensure email validation passes
		if ( is_user_logged_in() ) {
			$user          = wp_get_current_user();
			$output       .= '<input type="hidden" name="first_name" value="' . esc_attr( $user->first_name ) . '">';
			$output       .= '<input type="hidden" name="last_name" value="' . esc_attr( $user->last_name ) . '">';
			$output       .= '<input type="hidden" name="email" value="' . esc_attr( $user->user_email ) . '">';
					$billing_phone = get_user_meta( $user->ID, 'ennu_global_billing_phone', true );
		if ( $billing_phone ) {
			$output .= '<input type="hidden" name="billing_phone" value="' . esc_attr( $billing_phone ) . '">';
		}
		}

		// Add auto-submit flag
		$output .= '<input type="hidden" name="auto_submit_ready" value="' . ( $auto_submit_ready ? '1' : '0' ) . '">';

		if ( $needs_contact_form ) {
			// Show contact form with only missing fields
			$missing_fields = $this->get_missing_contact_fields( $current_user_data );

			$button_text = is_user_logged_in() ?
				__( 'Complete Assessment', 'ennulifeassessments' ) :
				__( 'Get My Results', 'ennulifeassessments' );

			$privacy_message = is_user_logged_in() ?
				__( 'Complete your profile to get personalized results.', 'ennulifeassessments' ) :
				__( 'Your information is secure and will be used to create your personalized health assessment.', 'ennulifeassessments' );

			ob_start();
			?>
			<div class="question-slide contact-form-slide" data-is-contact-form="true">
				<div class="question-header">
					<h2 class="question-title"><?php echo esc_html__( 'Contact Information', 'ennulifeassessments' ); ?></h2>
					<p class="question-description"><?php echo esc_html( $privacy_message ); ?></p>
				</div>
				
				<div class="question-content">
					<div class="contact-fields">
						<?php if ( in_array( 'first_name', $missing_fields ) ) : ?>
							<div class="contact-field">
								<label for="contact_first_name"><?php esc_html_e( 'First Name', 'ennulifeassessments' ); ?></label>
								<input type="text" id="contact_first_name" name="first_name" placeholder="<?php esc_attr_e( 'First Name', 'ennulifeassessments' ); ?>" value="<?php echo esc_attr( $current_user_data['first_name'] ?? '' ); ?>" required>
							</div>
						<?php endif; ?>
						
						<?php if ( in_array( 'last_name', $missing_fields ) ) : ?>
							<div class="contact-field">
								<label for="contact_last_name"><?php esc_html_e( 'Last Name', 'ennulifeassessments' ); ?></label>
								<input type="text" id="contact_last_name" name="last_name" placeholder="<?php esc_attr_e( 'Last Name', 'ennulifeassessments' ); ?>" value="<?php echo esc_attr( $current_user_data['last_name'] ?? '' ); ?>" required>
							</div>
						<?php endif; ?>
						
						<?php if ( in_array( 'email', $missing_fields ) ) : ?>
							<div class="contact-field">
								<label for="contact_email"><?php esc_html_e( 'Email Address', 'ennulifeassessments' ); ?></label>
								<input type="email" id="contact_email" name="email" placeholder="<?php esc_attr_e( 'Email Address', 'ennulifeassessments' ); ?>" value="<?php echo esc_attr( $current_user_data['email'] ?? '' ); ?>" required>
							</div>
						<?php endif; ?>
						
						<?php if ( in_array( 'phone', $missing_fields ) ) : ?>
							<div class="contact-field">
								<label for="contact_phone"><?php esc_html_e( 'Phone Number', 'ennulifeassessments' ); ?></label>
								<input type="tel" id="contact_phone" name="billing_phone" placeholder="<?php esc_attr_e( 'Phone Number', 'ennulifeassessments' ); ?>" value="<?php echo esc_attr( $current_user_data['billing_phone'] ?? '' ); ?>">
							</div>
						<?php endif; ?>
					</div>
					
					<div class="privacy-notice">
						<p><small><?php esc_html_e( 'We respect your privacy and will never share your information with third parties.', 'ennulifeassessments' ); ?></small></p>
					</div>
				</div>
				
				<div class="question-navigation">
					<?php if ( $question_number > 1 ) : ?>
						<button type="button" class="nav-button prev">← <?php esc_html_e( 'Previous', 'ennulifeassessments' ); ?></button>
					<?php endif; ?>
					<button type="button" class="nav-button next submit"><?php echo esc_html( $button_text ); ?></button>
				</div>
			</div>
			<?php
			$output .= ob_get_clean();
		}

		return $output;
	}

	/**
	 * Determine if user needs contact form
	 *
	 * @param array $current_user_data Current user data
	 * @return bool
	 */
	private function user_needs_contact_form( $current_user_data = array() ) {
		// Always show contact form for logged out users
		if ( ! is_user_logged_in() ) {
			return true;
		}

		// For logged in users, check if they have all required contact info
		$required_fields    = array( 'first_name', 'last_name', 'email' );
		$recommended_fields = array( 'billing_phone' ); // Phone is recommended but not required

		foreach ( $required_fields as $field ) {
			if ( empty( $current_user_data[ $field ] ) ) {
				return true; // Missing required field
			}
		}

		// If phone is missing, show contact form (phone is important for support)
		if ( empty( $current_user_data['billing_phone'] ) ) {
			return true;
		}

		return false; // All contact info is complete
	}

	/**
	 * Get missing contact fields
	 *
	 * @param array $current_user_data Current user data
	 * @return array
	 */
	private function get_missing_contact_fields( $current_user_data = array() ) {
		$missing_fields = array();

		// Always check these fields
		$fields_to_check = array(
			'first_name' => 'first_name',
			'last_name'  => 'last_name',
			'email'      => 'email',
			'phone'      => 'billing_phone',
		);

		foreach ( $fields_to_check as $key => $data_key ) {
			if ( empty( $current_user_data[ $data_key ] ) ) {
				$missing_fields[] = $key;
			}
		}

		return $missing_fields;
	}

	/**
	 * Get current authentication state data
	 * Helper method to generate auth state data consistently
	 */
	private function get_current_auth_state_data() {
		$response = array(
			'is_logged_in'       => is_user_logged_in(),
			'needs_contact_form' => true,
			'auto_submit_ready'  => false,
			'user_data'          => array(),
		);

		if ( is_user_logged_in() ) {
			$user    = wp_get_current_user();
			$user_id = $user->ID;

			// Get current user data
			$current_user_data = array(
				'first_name'    => $user->first_name,
				'last_name'     => $user->last_name,
				'email'         => $user->user_email,
				'billing_phone' => get_user_meta( $user_id, 'ennu_global_billing_phone', true ),
			);

			// Check if user needs contact form
			$needs_contact_form = $this->user_needs_contact_form( $current_user_data );

			$response['needs_contact_form'] = $needs_contact_form;
			$response['auto_submit_ready']  = ! $needs_contact_form;
			$response['user_data']          = $current_user_data;
			$response['missing_fields']     = $needs_contact_form ? $this->get_missing_contact_fields( $current_user_data ) : array();
		}

		return $response;
	}

	/**
	 * Helper method to generate URLs using page IDs instead of pretty permalinks
	 * This ensures more reliable URL generation across different WordPress configurations
	 *
	 * @param string $page_type The type of page (e.g., 'dashboard', 'call', 'welcome')
	 * @param array $query_args Optional query arguments to add to the URL
	 * @return string The page ID-based URL
	 */
	public function get_page_id_url( $page_type, $query_args = array() ) {
		// Use selected page mappings from admin settings first (selected pages, not created pages)
		$settings = get_option( 'ennu_life_settings', array() );
		$page_mappings = $settings['page_mappings'] ?? array();
		if ( ! empty( $page_mappings[ $page_type ] ) && get_post( $page_mappings[ $page_type ] ) ) {
			$page_id = intval( $page_mappings[ $page_type ] );
			$url     = home_url( "/?page_id={$page_id}" );
			if ( ! empty( $query_args ) ) {
				$url = add_query_arg( $query_args, $url );
			}
			return $url;
		}

		// Try to find page by slug for assessment pages and other dynamic pages
		$page = get_page_by_path( $page_type );
		if ( $page && $page->post_status === 'publish' ) {
			$url = home_url( "/?page_id={$page->ID}" );
			if ( ! empty( $query_args ) ) {
				$url = add_query_arg( $query_args, $url );
			}
			return $url;
		}

		// CRITICAL FIX: Force ?page_id= format even for fallback
		// Try to find any page that might match the slug pattern
		$all_pages = get_pages();
		foreach ( $all_pages as $page ) {
			if ( $page->post_status === 'publish' ) {
				// Check if page slug contains the page_type or vice versa
				if ( strpos( $page->post_name, $page_type ) !== false || strpos( $page_type, $page->post_name ) !== false ) {
					$url = home_url( "/?page_id={$page->ID}" );
					if ( ! empty( $query_args ) ) {
						$url = add_query_arg( $query_args, $url );
					}
					return $url;
				}
			}
		}

		// Final fallback: Create a ?page_id= URL with a default page ID
		// This ensures we NEVER use pretty permalinks
		$default_page_id = 1; // WordPress default page ID
		$url             = home_url( "/?page_id={$default_page_id}" );
		if ( ! empty( $query_args ) ) {
			$url = add_query_arg( $query_args, $url );
		}
		return $url;
	}

	/**
	 * Render consultation shortcode
	 *
	 * @param array $atts Shortcode attributes
	 * @param string $content Shortcode content
	 * @param string $tag Shortcode tag
	 * @return string
	 */
	public function render_consultation_shortcode( $atts, $content = '', $tag = '' ) {
		// Extract consultation type from shortcode tag
		$consultation_type = str_replace( array( 'ennu-', '-consultation' ), '', $tag );

		// Map assessment keys to consultation config keys
		$consultation_key_mapping = array(
			'hair'                => 'hair_restoration',
			'hair-restoration'    => 'hair_restoration',
			'ed-treatment'        => 'ed_treatment',
			'weight-loss'         => 'weight_loss',
			'health-optimization' => 'health_optimization',
			'skin'                => 'skin_care',
			'skin-care'           => 'skin_care',
			'health'              => 'general_consultation',
			'hormone'             => 'hormone',
			'menopause'           => 'menopause',
			'testosterone'        => 'testosterone',
			'sleep'               => 'sleep',
		);

		$consultation_type = $consultation_key_mapping[ $consultation_type ] ?? $consultation_type;

		// Get HubSpot settings
		$hubspot_settings    = get_option( 'ennu_hubspot_settings', array() );
		$consultation_config = $this->get_consultation_config( $consultation_type );

		if ( ! $consultation_config ) {
			return $this->render_error_message( __( 'Invalid consultation type.', 'ennulifeassessments' ) );
		}

		// Get user data for pre-population
		$user_data = $this->get_user_data_for_consultation();

		// Hardcoded HubSpot embed code for reliable functionality
		$embed_code = '<!-- Start of Meetings Embed Script -->
    <div class="meetings-iframe-container" data-src="https://meetings.hubspot.com/lescobar2/ennulife?embed=true"></div>
    <script type="text/javascript" src="https://static.hsappstatic.net/MeetingsEmbed/ex/MeetingsEmbedCode.js"></script>
  <!-- End of Meetings Embed Script -->';
		
		$meeting_type        = $consultation_type . '-consultation';
		$pre_populate_fields = array( 'firstname', 'lastname', 'email' );
		
		error_log( 'ENNU HubSpot: Using default embed code for ' . $consultation_type );

		// Prepare data for template
		$data = array(
			'consultation_type'     => $consultation_type,
			'consultation_config'   => $consultation_config,
			'embed_code'           => $embed_code,
			'meeting_type'         => $meeting_type,
			'user_data'            => $user_data,
			'pre_populate_fields'  => $pre_populate_fields,
			'shortcode_instance'   => $this,
		);

		// Load the consultation booking template
		ob_start();
		ennu_load_template( 'consultation-booking', $data );
		return ob_get_clean();
	}

	/**
	 * Get consultation configuration
	 */
	private function get_consultation_config( $consultation_type ) {
		$configs = array(
			'hair_restoration'            => array(
				'title'         => 'Hair Restoration Consultation',
				'description'   => 'Schedule a personalized consultation with our hair restoration specialists to discuss your hair growth journey.',
				'benefits'      => array(
					'Personalized hair restoration strategy',
					'Advanced treatment options (PRP, transplants, medications)',
					'Hair growth timeline and realistic expectations',
					'Customized pricing for your treatment plan',
				),
				'contact_label' => 'Questions about hair restoration?',
				'phone'         => '+1-800-ENNU-HAIR',
				'phone_display' => '(800) ENNU-HAIR',
				'email'         => 'hair@ennulife.com',
				'icon'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="80" height="80"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/><path d="M12 6c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6-2.69-6-6-6zm0 10c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z"/></svg>',
				'color'         => '#667eea',
				'gradient'      => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
				'info_bg'       => 'linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%)',
			),
			'ed_treatment'                => array(
				'title'         => 'ED Treatment Consultation',
				'description'   => 'Book a confidential consultation with our medical specialists to discuss personalized ED treatment options.',
				'benefits'      => array(
					'Confidential medical consultation',
					'FDA-approved treatment options',
					'Discreet and professional care',
					'Personalized treatment recommendations',
				),
				'contact_label' => 'Confidential questions?',
				'phone'         => '+1-800-ENNU-MENS',
				'phone_display' => '(800) ENNU-MENS',
				'email'         => 'confidential@ennulife.com',
				'icon'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="80" height="80"><path d="M9 12l2 2 4-4"/><path d="M21 12c-1 0-2-1-2-2s1-2 2-2 2 1 2 2-1 2-2 2z"/><path d="M3 12c1 0 2-1 2-2s-1-2-2-2-2 1-2 2 1 2 2 2z"/><path d="M12 3c0 1-1 2-2 2s-2-1-2-2 1-2 2-2 2 1 2 2z"/><path d="M12 21c0-1 1-2 2-2s2 1 2 2-1 2-2 2-2-1-2-2z"/></svg>',
				'color'         => '#f093fb',
				'gradient'      => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
				'info_bg'       => 'linear-gradient(135deg, #fef7ff 0%, #fdf2ff 100%)',
				'extra_section' => '<div class="privacy-notice" style="margin-top: 30px; padding: 20px; background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%); border-radius: 8px; border-left: 4px solid #28a745; font-size: 0.95em;"><p><strong>🔒 Your Privacy is Protected:</strong> All consultations are completely confidential and HIPAA compliant. Your information is secure and private.</p></div>',
			),
			'weight_loss'                 => array(
				'title'         => 'Weight Loss Consultation',
				'description'   => 'Schedule a consultation to discuss your personalized weight loss plan and achieve your health goals.',
				'benefits'      => array(
					'Personalized weight loss strategy',
					'Medical supervision and support',
					'Nutrition and exercise guidance',
					'Long-term success planning',
				),
				'contact_label' => 'Questions about weight loss?',
				'phone'         => '+1-800-ENNU-WEIGHT',
				'phone_display' => '(800) ENNU-WEIGHT',
				'email'         => 'weight@ennulife.com',
				'icon'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="80" height="80"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
				'color'         => '#4facfe',
				'gradient'      => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
				'info_bg'       => 'linear-gradient(135deg, #f0faff 0%, #e6f7ff 100%)',
			),
			'health_optimization'         => array(
				'title'         => 'Health Optimization Consultation',
				'description'   => 'Book a comprehensive consultation to optimize your overall health and wellness.',
				'benefits'      => array(
					'Comprehensive health evaluation',
					'Preventive care recommendations',
					'Hormone optimization options',
					'Ongoing health monitoring plan',
				),
				'contact_label' => 'Questions about health optimization?',
				'phone'         => '+1-800-ENNU-HLTH',
				'phone_display' => '(800) ENNU-HLTH',
				'email'         => 'health@ennulife.com',
				'icon'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="80" height="80"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
				'color'         => '#fa709a',
				'gradient'      => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
				'info_bg'       => 'linear-gradient(135deg, #fff8fb 0%, #fef5f8 100%)',
			),
			'skin_care'                   => array(
				'title'         => 'Skin Care Consultation',
				'description'   => 'Schedule a consultation with our skincare specialists to achieve your skin goals.',
				'benefits'      => array(
					'Personalized skincare regimen',
					'Advanced treatments (Botox, fillers, laser)',
					'Professional product recommendations',
					'Skin rejuvenation timeline',
				),
				'contact_label' => 'Questions about skincare?',
				'phone'         => '+1-800-ENNU-SKIN',
				'phone_display' => '(800) ENNU-SKIN',
				'email'         => 'skin@ennulife.com',
				'icon'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="80" height="80"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
				'color'         => '#a8edea',
				'gradient'      => 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
				'info_bg'       => 'linear-gradient(135deg, #f0fffe 0%, #edfffe 100%)',
			),
			'general_consultation'        => array(
				'title'         => 'General Health Consultation',
				'description'   => 'Schedule a general health consultation to discuss any health concerns or questions.',
				'benefits'      => array(
					'Comprehensive health review',
					'Personalized recommendations',
					'Preventive care guidance',
					'Referral to specialists if needed',
				),
				'contact_label' => 'General health questions?',
				'phone'         => '+1-800-ENNU-LIFE',
				'phone_display' => '(800) ENNU-LIFE',
				'email'         => 'info@ennulife.com',
				'icon'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="80" height="80"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/><path d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>',
				'color'         => '#667eea',
				'gradient'      => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
				'info_bg'       => 'linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%)',
			),
			'schedule_call'               => array(
				'title'         => 'Schedule a Call',
				'description'   => 'Book a call to discuss any health concerns or questions with our team.',
				'benefits'      => array(
					'Flexible scheduling options',
					'No-obligation consultation',
					'Expert health guidance',
					'Personalized recommendations',
				),
				'contact_label' => 'Need immediate assistance?',
				'phone'         => '+1-800-ENNU-LIFE',
				'phone_display' => '(800) ENNU-LIFE',
				'email'         => 'info@ennulife.com',
				'icon'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="80" height="80"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
				'color'         => '#4facfe',
				'gradient'      => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
				'info_bg'       => 'linear-gradient(135deg, #f0faff 0%, #e6f7ff 100%)',
			),
			'ennu_life_score'             => array(
				'title'         => 'Get Your ENNU Life Score',
				'description'   => 'Schedule a consultation to get your personalized ENNU Life Score and health insights.',
				'benefits'      => array(
					'Comprehensive health assessment',
					'Personalized ENNU Life Score',
					'Detailed health insights',
					'Actionable recommendations',
				),
				'contact_label' => 'Questions about your ENNU Life Score?',
				'phone'         => '+1-800-ENNU-LIFE',
				'phone_display' => '(800) ENNU-LIFE',
				'email'         => 'score@ennulife.com',
				'icon'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="80" height="80"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>',
				'color'         => '#fa709a',
				'gradient'      => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
				'info_bg'       => 'linear-gradient(135deg, #fff8fb 0%, #fef5f8 100%)',
			),
			'health_optimization_results' => array(
				'title'         => 'Health Optimization Results Consultation',
				'description'   => 'Discuss your health optimization assessment results with our specialists.',
				'benefits'      => array(
					'Detailed results review',
					'Personalized optimization plan',
					'Treatment recommendations',
					'Follow-up monitoring',
				),
				'contact_label' => 'Questions about your results?',
				'phone'         => '+1-800-ENNU-HLTH',
				'phone_display' => '(800) ENNU-HLTH',
				'email'         => 'results@ennulife.com',
				'icon'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="80" height="80"><path d="M9 11H1l8-8 8 8h-8v8z"/></svg>',
				'color'         => '#fa709a',
				'gradient'      => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
				'info_bg'       => 'linear-gradient(135deg, #fff8fb 0%, #fef5f8 100%)',
			),
			'confidential_consultation'   => array(
				'title'         => 'Confidential Consultation',
				'description'   => 'Book a confidential consultation for sensitive health matters in a secure environment.',
				'benefits'      => array(
					'Complete confidentiality',
					'HIPAA compliant care',
					'Discreet treatment options',
					'Professional medical guidance',
				),
				'contact_label' => 'Confidential questions?',
				'phone'         => '+1-800-ENNU-CONF',
				'phone_display' => '(800) ENNU-CONF',
				'email'         => 'confidential@ennulife.com',
				'icon'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="80" height="80"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><circle cx="12" cy="16" r="1"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
				'color'         => '#f093fb',
				'gradient'      => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
				'info_bg'       => 'linear-gradient(135deg, #fef7ff 0%, #fdf2ff 100%)',
				'extra_section' => '<div class="privacy-notice" style="margin-top: 30px; padding: 20px; background: linear-gradient(135deg, #e8f5e8 0%, #f0f8f0 100%); border-radius: 8px; border-left: 4px solid #28a745; font-size: 0.95em;"><p><strong>🔒 Your Privacy is Protected:</strong> All consultations are completely confidential and HIPAA compliant. Your information is secure and private.</p></div>',
			),
			'sleep'                       => array(
				'title'         => 'Sleep Consultation',
				'description'   => 'Schedule a consultation with our sleep specialists to discuss your personalized sleep optimization plan.',
				'benefits'      => array(
					'Personalized sleep optimization strategy',
					'Sleep disorder evaluation and treatment',
					'Lifestyle and environmental recommendations',
					'Long-term sleep improvement plan',
				),
				'contact_label' => 'Questions about sleep optimization?',
				'phone'         => '+1-800-ENNU-SLEEP',
				'phone_display' => '(800) ENNU-SLEEP',
				'email'         => 'sleep@ennulife.com',
				'icon'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="80" height="80"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/><path d="M12 6v6l4 2"/></svg>',
				'color'         => '#667eea',
				'gradient'      => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
				'info_bg'       => 'linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%)',
			),
			'hormone'                     => array(
				'title'         => 'Hormone Consultation',
				'description'   => 'Book a consultation with our hormone specialists to discuss your hormone optimization needs.',
				'benefits'      => array(
					'Comprehensive hormone evaluation',
					'Personalized hormone optimization plan',
					'Bioidentical hormone therapy options',
					'Ongoing hormone monitoring',
				),
				'contact_label' => 'Questions about hormone optimization?',
				'phone'         => '+1-800-ENNU-HORM',
				'phone_display' => '(800) ENNU-HORM',
				'email'         => 'hormone@ennulife.com',
				'icon'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="80" height="80"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
				'color'         => '#fa709a',
				'gradient'      => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
				'info_bg'       => 'linear-gradient(135deg, #fff8fb 0%, #fef5f8 100%)',
			),
			'menopause'                   => array(
				'title'         => 'Menopause Consultation',
				'description'   => 'Schedule a consultation with our menopause specialists to discuss your personalized treatment options.',
				'benefits'      => array(
					'Comprehensive menopause evaluation',
					'Symptom management strategies',
					'Hormone replacement therapy options',
					'Lifestyle and wellness guidance',
				),
				'contact_label' => 'Questions about menopause?',
				'phone'         => '+1-800-ENNU-MENO',
				'phone_display' => '(800) ENNU-MENO',
				'email'         => 'menopause@ennulife.com',
				'icon'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="80" height="80"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/><path d="M12 6v6l4 2"/></svg>',
				'color'         => '#f093fb',
				'gradient'      => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
				'info_bg'       => 'linear-gradient(135deg, #fef7ff 0%, #fdf2ff 100%)',
			),
			'testosterone'                => array(
				'title'         => 'Testosterone Consultation',
				'description'   => 'Book a consultation with our testosterone specialists to discuss your hormone optimization needs.',
				'benefits'      => array(
					'Comprehensive testosterone evaluation',
					'Personalized testosterone optimization',
					'Testosterone replacement therapy options',
					'Ongoing hormone monitoring',
				),
				'contact_label' => 'Questions about testosterone optimization?',
				'phone'         => '+1-800-ENNU-TEST',
				'phone_display' => '(800) ENNU-TEST',
				'email'         => 'testosterone@ennulife.com',
				'icon'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="80" height="80"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
				'color'         => '#4facfe',
				'gradient'      => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
				'info_bg'       => 'linear-gradient(135deg, #f0faff 0%, #e6f7ff 100%)',
			),
		);

		return $configs[ $consultation_type ] ?? null;
	}

	/**
	 * Get the registration page URL (admin-selected or fallback)
	 */
	public function get_registration_url() {
		return $this->get_page_id_url('registration');
	}

	/**
	 * Get the login page URL (admin-selected or fallback)
	 */
	public function get_login_url() {
		return $this->get_page_id_url('login');
	}

	/**
	 * Get dynamic consultation title based on consultation type and user context
	 *
	 * @param string $consultation_type The consultation type
	 * @param array  $consultation_config The consultation configuration
	 * @return string
	 */
	public function get_dynamic_consultation_title( $consultation_type, $consultation_config ) {
		// Get user data for personalization
		$user_data = $this->get_user_data_for_consultation();
		$user_name = trim( $user_data['firstname'] . ' ' . $user_data['lastname'] );
		
		// Base title from config
		$base_title = $consultation_config['title'] ?? 'Health Consultation';
		
		// If user is logged in and has a name, personalize the title
		if ( is_user_logged_in() && ! empty( $user_name ) ) {
			// Create personalized titles based on consultation type
			$personalized_titles = array(
				'hair_restoration'    => "Hi {$user_name}, Ready for Your Hair Restoration Journey?",
				'ed_treatment'        => "Hi {$user_name}, Let's Discuss Your Health Goals",
				'weight_loss'         => "Hi {$user_name}, Ready to Transform Your Health?",
				'health_optimization' => "Hi {$user_name}, Let's Optimize Your Wellness",
				'skin_care'           => "Hi {$user_name}, Ready for Your Skin Transformation?",
				'general_consultation' => "Hi {$user_name}, Let's Discuss Your Health",
				'hormone'             => "Hi {$user_name}, Let's Balance Your Hormones",
				'menopause'           => "Hi {$user_name}, Let's Navigate This Together",
				'testosterone'        => "Hi {$user_name}, Let's Optimize Your Vitality",
				'sleep'               => "Hi {$user_name}, Let's Improve Your Sleep",
			);
			
			return $personalized_titles[ $consultation_type ] ?? "Hi {$user_name}, " . $base_title;
		}
		
		// For non-logged-in users, use more engaging titles
		$engaging_titles = array(
			'hair_restoration'    => 'Ready to Restore Your Hair?',
			'ed_treatment'        => 'Confidential Health Consultation',
			'weight_loss'         => 'Ready to Transform Your Health?',
			'health_optimization' => 'Let\'s Optimize Your Wellness',
			'skin_care'           => 'Ready for Your Skin Transformation?',
			'general_consultation' => 'Let\'s Discuss Your Health Goals',
			'hormone'             => 'Let\'s Balance Your Hormones',
			'menopause'           => 'Let\'s Navigate This Together',
			'testosterone'        => 'Let\'s Optimize Your Vitality',
			'sleep'               => 'Let\'s Improve Your Sleep',
		);
		
		return $engaging_titles[ $consultation_type ] ?? $base_title;
	}

	/**
	 * Get user data for consultation pre-population
	 */
	private function get_user_data_for_consultation() {
		$user_data = array(
			'firstname'          => '',
			'lastname'           => '',
			'email'              => '',
			'phone'              => '',
			'assessment_results' => '',
		);

		if ( is_user_logged_in() ) {
			$user    = wp_get_current_user();
			$user_id = $user->ID;

			$user_data['firstname'] = $user->first_name;
			$user_data['lastname']  = $user->last_name;
			$user_data['email']     = $user->user_email;
			$user_data['phone']     = get_user_meta( $user_id, 'ennu_global_billing_phone', true );

			// Get assessment results for pre-population
			$assessment_results = array();
			$assessment_types   = array( 'hair_assessment', 'ed_treatment_assessment', 'weight_loss_assessment', 'health_assessment', 'skin_assessment' );

			foreach ( $assessment_types as $type ) {
				$score = get_user_meta( $user_id, 'ennu_' . $type . '_calculated_score', true );
		if ( $score ) {
					$assessment_results[] = ucwords( str_replace( '_', ' ', $type ) ) . ': ' . $score . '/10';
				}
			}

			if ( ! empty( $assessment_results ) ) {
				$user_data['assessment_results'] = implode( ', ', $assessment_results );
			}
		}

		return $user_data;
	}

	/**
	 * Render assessments listing page
	 *
	 * @param array $atts Shortcode attributes
	 * @return string
	 */
	public function render_assessments_listing( $atts = array() ) {
		$user_id          = get_current_user_id();
		$user_gender      = $user_id ? get_user_meta( $user_id, 'ennu_global_gender', true ) : '';
		$user_assessments = $user_id ? $this->get_user_assessments_data( $user_id ) : array();
		$all_assessments  = $this->all_definitions;

		// Define assessment icons using the same style as dashboard
		$assessment_icons = array(
			'hair'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
			'skin'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
			'health'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
			'weight-loss'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M10 11h4"/><path d="M10 16h4"/></svg>',
			'hormone'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
			'menopause'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
			'testosterone' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
			'sleep'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/><circle cx="12" cy="12" r="4"/></svg>',
			'ed-treatment' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
		);

		// Define assessment descriptions for logged-out users
		$assessment_descriptions = array(
			'hair'         => 'Comprehensive hair health assessment to identify causes of hair loss and thinning.',
			'skin'         => 'Advanced skin analysis to understand your skin type and optimize your skincare routine.',
			'health'       => 'Complete health optimization assessment for overall wellness and vitality.',
			'weight-loss'  => 'Personalized weight management assessment with custom nutrition and exercise plans.',
			'hormone'      => 'Hormonal health evaluation to balance your endocrine system naturally.',
			'menopause'    => 'Specialized assessment for managing menopause symptoms and hormonal changes.',
			'testosterone' => 'Comprehensive testosterone optimization for energy, strength, and vitality.',
			'sleep'        => 'Sleep quality assessment to improve rest and recovery for better health.',
			'ed-treatment' => 'Specialized assessment for erectile dysfunction and sexual health optimization.',
		);

				ob_start();
		?>
		<div class="ennu-user-dashboard">
			<!-- Light/Dark Mode Toggle -->
			<div class="theme-toggle-container">
				<button class="theme-toggle" id="theme-toggle" aria-label="Toggle light/dark mode">
					<div class="toggle-track">
						<div class="toggle-thumb">
							<svg class="toggle-icon sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<circle cx="12" cy="12" r="5"/>
								<line x1="12" y1="1" x2="12" y2="3"/>
								<line x1="12" y1="21" x2="12" y2="23"/>
								<line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
								<line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
								<line x1="1" y1="12" x2="3" y2="12"/>
								<line x1="21" y1="12" x2="23" y2="12"/>
								<line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
								<line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
							</svg>
							<svg class="toggle-icon moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
								<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
							</svg>
						</div>
					</div>
				</button>
			</div>

			<div class="dashboard-main-grid">
				<main class="dashboard-main-content">
					<!-- Welcome Section -->
					<div class="dashboard-welcome-section">
						<?php if ( $user_id ) : ?>
							<h1 class="dashboard-title dashboard-title-large">Your Health Assessments</h1>
							<p class="dashboard-subtitle">Track your progress and take control of your health with our comprehensive, personalized assessments.</p>
						<?php else : ?>
							<h1 class="dashboard-title dashboard-title-large">Free Health Assessments</h1>
							<p class="dashboard-subtitle">Discover your personalized health insights with our comprehensive assessments. Each assessment is designed by medical professionals to provide actionable recommendations for your unique health journey.</p>
							
							<!-- Call to Action for Logged Out Users -->
							<div class="cta-section">
								<div class="cta-card">
									<div class="cta-icon">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
											<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
										</svg>
									</div>
									<div class="cta-content">
										<h3>Start Your Health Journey Today</h3>
										<p>Join thousands of users who have transformed their health with our personalized assessments. Get your free health score and personalized recommendations.</p>
										<div class="cta-buttons">
											<a href="<?php echo esc_url( $this->get_registration_url() ); ?>" class="btn btn-primary btn-pill">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
													<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
													<circle cx="9" cy="7" r="4"/>
													<path d="m22 21-2-2m0 0a5.5 5.5 0 1 0-7.78-7.78 5.5 5.5 0 0 0 7.78 7.78Z"/>
												</svg>
												Create Free Account
											</a>
											<a href="<?php echo esc_url( $this->get_login_url() ); ?>" class="btn btn-secondary btn-pill">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
													<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
													<polyline points="10,17 15,12 10,7"/>
													<line x1="15" y1="12" x2="3" y2="12"/>
												</svg>
												Sign In
											</a>
										</div>
									</div>
								</div>
							</div>
						<?php endif; ?>
					</div>

					<!-- Assessment Cards Section -->
					<div class="assessment-cards-section">
						<div class="assessment-cards-grid">
							<?php
							// For logged-out users, show all available assessments
							$assessments_to_show = $user_id ? $user_assessments : $all_assessments;

							foreach ( $assessments_to_show as $assessment_key => $assessment ) :
								// Handle different array structures for logged-in vs logged-out users
								if ( $user_id ) {
									// For logged-in users, assessment is already an array with 'key' and 'label'
									$assessment_key   = $assessment['key'];
									$assessment_label = $assessment['label'];
									$is_completed     = isset( $assessment['completed'] ) ? $assessment['completed'] : false;
									$has_score        = $is_completed && isset( $assessment['score'] );
								} else {
									// For logged-out users, assessment is the config array with 'title'
									$assessment_label = $assessment['title'] ?? ucwords( str_replace( '_', ' ', $assessment_key ) );
									$is_completed     = false;
									$has_score        = false;
								}

								$assessment_icon        = isset( $assessment_icons[ $assessment_key ] ) ? $assessment_icons[ $assessment_key ] : '';
								$assessment_description = isset( $assessment_descriptions[ $assessment_key ] ) ? $assessment_descriptions[ $assessment_key ] : '';
								?>
								<div class="assessment-card <?php echo $user_id && $is_completed ? 'completed' : 'incomplete'; ?> animate-card">
									<div class="assessment-card-header">
										<?php if ( ! empty( $assessment_icon ) ) : ?>
											<div class="assessment-icon">
												<?php echo $assessment_icon; ?>
											</div>
										<?php endif; ?>
										<h3 class="assessment-title"><?php echo esc_html( $assessment_label ); ?></h3>
										
										<?php if ( $user_id ) : ?>
											<div class="assessment-status">
												<?php if ( $has_score ) : ?>
													<div class="assessment-score-display">
														<span class="score-value"><?php echo esc_html( number_format( $assessment['score'], 1 ) ); ?></span>
														<span class="score-label">/10</span>
													</div>
												<?php endif; ?>
												
												<div class="status-badge <?php echo $is_completed ? 'completed' : 'incomplete'; ?>">
													<span class="<?php echo $is_completed ? 'completed-text' : 'incomplete-text'; ?>">
														<?php echo $is_completed ? 'Completed' : 'Not Started'; ?>
													</span>
												</div>
											</div>
										<?php endif; ?>
									</div>
									
									<?php if ( ! $user_id && ! empty( $assessment_description ) ) : ?>
										<div class="assessment-description">
											<p><?php echo esc_html( $assessment_description ); ?></p>
										</div>
									<?php endif; ?>
									
									<div class="assessment-card-actions">
										<?php if ( $user_id && $is_completed ) : ?>
											<a href="<?php echo esc_url( $this->get_page_id_url( $this->get_assessment_page_slug( $assessment_key ) . '-details' ) ); ?>" class="btn btn-history">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
													<path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
												</svg>
												History
											</a>
										<?php endif; ?>
										
										<?php if ( $user_id ) : ?>
											<a href="<?php echo esc_url( $this->get_page_id_url( $this->get_assessment_page_slug( $assessment_key ) ) ); ?>" class="btn btn-primary btn-pill">
												<?php echo $is_completed ? 'Retake Assessment' : 'Start Assessment'; ?>
											</a>
										<?php else : ?>
											<a href="<?php echo esc_url( $this->get_page_id_url( $this->get_assessment_page_slug( $assessment_key ) ) ); ?>" class="btn btn-primary btn-pill">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
													<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
													<circle cx="9" cy="7" r="4"/>
													<path d="m22 21-2-2m0 0a5.5 5.5 0 1 0-7.78-7.78 5.5 5.5 0 0 0 7.78 7.78Z"/>
												</svg>
												Start Free Assessment
											</a>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</main>
			</div>
		</div>
		<?php
				return ob_get_clean();
	}

	/**
	 * Get user health goals and all available goals
	 */
	private function get_user_health_goals( $user_id ) {
		// Get user's selected goals from user meta (FIXED: Using correct meta key)
		$user_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
		$user_goals = is_array( $user_goals ) ? $user_goals : array();

		// Load health goals from configuration file
		$health_goals_config = ENNU_LIFE_PLUGIN_PATH . 'includes/config/scoring/health-goals.php';
		$all_goals           = array();

		if ( file_exists( $health_goals_config ) ) {
			$config = require $health_goals_config;
			if ( isset( $config['goal_definitions'] ) ) {
				$all_goals = $config['goal_definitions'];
			}
		}

		// Fallback to hardcoded goals if config not available
		if ( empty( $all_goals ) ) {
			$all_goals = array(
				'longevity'        => array(
					'id'          => 'longevity',
					'label'       => 'Longevity & Healthy Aging',
					'description' => 'Focus on extending healthy lifespan and aging gracefully',
					'icon'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
					'category'    => 'Wellness',
				),
				'energy'           => array(
					'id'          => 'energy',
					'label'       => 'Improve Energy & Vitality',
					'description' => 'Boost daily energy levels and combat fatigue',
					'icon'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="3"/><path d="M12 1v6m0 6v6"/><path d="M21 12h-6m-6 0H3"/><path d="M18.36 5.64l-4.24 4.24m0 4.24l4.24 4.24"/><path d="M5.64 5.64l4.24 4.24m0 4.24l-4.24 4.24"/></svg>',
					'category'    => 'Wellness',
				),
				'strength'         => array(
					'id'          => 'strength',
					'label'       => 'Build Strength & Muscle',
					'description' => 'Build lean muscle mass and physical strength',
					'icon'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M6 2v3a2 2 0 0 0 2 2h3"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M18 2v3a2 2 0 0 1-2 2h-3"/><path d="M4 22h16"/><path d="M10 14.66V17c0 1.1.9 2 2 2s2-.9 2-2v-2.34"/><path d="M12 14.66V17"/></svg>',
					'category'    => 'Fitness',
				),
				'libido'           => array(
					'id'          => 'libido',
					'label'       => 'Enhance Libido & Sexual Health',
					'description' => 'Enhance sexual health and performance',
					'icon'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>',
					'category'    => 'Men\'s Health',
				),
				'weight_loss'      => array(
					'id'          => 'weight_loss',
					'label'       => 'Achieve & Maintain Healthy Weight',
					'description' => 'Achieve and maintain a healthy weight',
					'icon'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M3 6h18M3 12h18M3 18h18"/><path d="M7 6v12M17 6v12"/></svg>',
					'category'    => 'Fitness',
				),
				'hormonal_balance' => array(
					'id'          => 'hormonal_balance',
					'label'       => 'Hormonal Balance',
					'description' => 'Optimize hormonal health and balance',
					'icon'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
					'category'    => 'Hormones',
				),
				'cognitive_health' => array(
					'id'          => 'cognitive_health',
					'label'       => 'Sharpen Cognitive Function',
					'description' => 'Sharpen memory and mental clarity',
					'icon'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M9 12l2 2 4-4"/><path d="M21 12c-1.1 0-2-.9-2-2V5c0-1.1.9-2 2-2s2 .9 2 2v5c0 1.1-.9 2-2 2z"/><path d="M3 12c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2S1 3.9 1 5v5c0 1.1.9 2 2 2z"/></svg>',
					'category'    => 'Mental Health',
				),
				'heart_health'     => array(
					'id'          => 'heart_health',
					'label'       => 'Support Heart Health',
					'description' => 'Support cardiovascular health and function',
					'icon'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
					'category'    => 'Wellness',
				),
				'aesthetics'       => array(
					'id'          => 'aesthetics',
					'label'       => 'Improve Hair, Skin & Nails',
					'description' => 'Improve hair, skin, and overall appearance',
					'icon'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="3"/><path d="m12 1 3 6 6 3-6 3-3 6-3-6-6-3 6-3 3-6z"/></svg>',
					'category'    => 'Aesthetics',
				),
				'sleep'            => array(
					'id'          => 'sleep',
					'label'       => 'Improve Sleep Quality',
					'description' => 'Improve sleep quality and recovery',
					'icon'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M12 3a6.364 6.364 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>',
					'category'    => 'Wellness',
				),
				'stress'           => array(
					'id'          => 'stress',
					'label'       => 'Reduce Stress & Improve Resilience',
					'description' => 'Reduce stress and improve resilience',
					'icon'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M8 2v4l-3-3"/><path d="M16 6l3-3v4"/><rect width="8" height="6" x="8" y="6" rx="1"/><path d="m16 16-2-2 1.5-1.5L14 11l5.5 5.5L18 18l-1.5-1.5L15 18"/><path d="M4 4v16h16"/></svg>',
					'category'    => 'Mental Health',
				),
			);
		}

		// Convert goals to the format expected by the template
		$formatted_goals = array();
		foreach ( $all_goals as $goal_id => $goal_data ) {
			// Handle different data structures - check for label, name, or use goal_id as fallback
			if ( is_array( $goal_data ) ) {
				if ( isset( $goal_data['label'] ) ) {
					$label = $goal_data['label'];
				} elseif ( isset( $goal_data['name'] ) ) {
					$label = $goal_data['name'];
			} else {
					$label = ucwords( str_replace( '_', ' ', $goal_id ) );
				}
			} else {
				// If goal_data is a string, use it directly
				$label = $goal_data;
			}

			$formatted_goals[ $goal_id ] = array(
				'id'          => $goal_id,
				'label'       => $label,
				'description' => ( is_array( $goal_data ) && isset( $goal_data['description'] ) ) ? $goal_data['description'] : $label,
				'icon'        => ( is_array( $goal_data ) && isset( $goal_data['icon'] ) ) ? $goal_data['icon'] : '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><circle cx="12" cy="12" r="3"/></svg>',
				'category'    => ( is_array( $goal_data ) && isset( $goal_data['category'] ) ) ? $goal_data['category'] : 'General',
				'selected'    => in_array( $goal_id, $user_goals ),
			);
		}

		return array(
			'user_goals' => $user_goals,
			'all_goals'  => $formatted_goals,
		);
	}

	/**
	 * Signup shortcode - Premium product selection page
	 */
	public function signup_shortcode( $atts ) {
		// Enqueue necessary styles
		wp_enqueue_style( 'ennu-frontend-forms' );

				ob_start();
		?>
		<div class="ennu-signup-container">
			<!-- Hero Section -->
			<div class="ennu-signup-hero">
				<div class="ennu-signup-hero-content">
					<h1 class="ennu-signup-title">
						<span class="ennu-signup-title-line">Your</span>
						<span class="ennu-signup-title-line">First</span>
						<span class="ennu-signup-title-line">Step</span>
						<span class="ennu-signup-title-line">Towards</span>
						<span class="ennu-signup-title-line">Optimization</span>
					</h1>
					<p class="ennu-signup-subtitle">
						From your first consultation to personalized care, we make every step seamless, evidence-based, and built around you. Here's how to get started with ENNU.
					</p>
				</div>
			</div>

			<!-- Process Steps -->
			<div class="ennu-signup-process">
				<div class="ennu-process-step">
					<div class="ennu-process-icon">1</div>
					<div class="ennu-process-content">
						<h3>Take Our Personalized Health Survey</h3>
						<p>Take our FREE health survey and discover potential issues preventing you from reaching your health goals. Based on your answers, we present possible medical, lifestyle, environmental and body chemistry issues hindering your progress.</p>
					</div>
				</div>
				
				<div class="ennu-process-step">
					<div class="ennu-process-icon">2</div>
					<div class="ennu-process-content">
						<h3>Optimal Health Assessment</h3>
						<p>Discover your body on a deeper level with our Optimal Health Assessment. This elevated experience includes two in-office visits, a 60-minute provider consultation, 40+ biomarker testing, and a personalized health plan.</p>
					</div>
				</div>
				
				<div class="ennu-process-step">
					<div class="ennu-process-icon">3</div>
					<div class="ennu-process-content">
						<h3>Become a Member</h3>
						<p>After completing your health survey, you'll enroll in our membership, unlocking access to comprehensive care, advanced labs, and ongoing provider support. This is where your transformation truly begins.</p>
					</div>
				</div>
				
				<div class="ennu-process-step">
					<div class="ennu-process-icon">4</div>
					<div class="ennu-process-content">
						<h3>Customized Treatment Plan</h3>
						<p>ENNU's medically supervised program redefines aging and wellness. Your dedicated specialist will review personalized options based on your health history, symptoms, and lab results.</p>
					</div>
				</div>
				
				<div class="ennu-process-step">
					<div class="ennu-process-icon">5</div>
					<div class="ennu-process-content">
						<h3>Ongoing Care. Elevated Living.</h3>
						<p>This is more than a plan - it's a partnership. From primary care to hormone health and beyond, our team supports you at every stage of your wellness journey.</p>
					</div>
				</div>
			</div>

			<!-- Contact Section -->
			<div class="ennu-signup-contact">
				<p class="ennu-contact-text">Have a question or want more information? We are here to help!</p>
				<div class="ennu-contact-team">
					<div class="ennu-team-avatars">
						<img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/img/team-1.jpg'; ?>" alt="Team Member" class="ennu-avatar">
						<img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/img/team-2.jpg'; ?>" alt="Team Member" class="ennu-avatar">
						<img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/img/team-3.jpg'; ?>" alt="Team Member" class="ennu-avatar">
					</div>
					<a href="<?php echo esc_url( $this->get_page_id_url( 'contact' ) ); ?>" class="ennu-contact-btn">
						<span>Contact Our Team</span>
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
							<path d="M16.6078425,18.811767 L20.5264762,4.70468553 C20.6742919,4.17254928 20.3627384,3.62133938 19.8306022,3.47352376 C19.6554882,3.42488097 19.4704285,3.42488097 19.2953145,3.47352376 L5.18823301,7.3921575 C4.65609676,7.53997312 4.34454334,8.09118302 4.49235897,8.62331927 C4.55925685,8.86415166 4.7139198,9.07119988 4.92587794,9.20367371 L10.8043081,12.8776926 C10.9330772,12.9581733 11.0418267,13.0669228 11.1223074,13.1956919 L14.7963263,19.0741221 C15.0890366,19.5424586 15.705987,19.6848318 16.1743235,19.3921214 C16.3862817,19.2596476 16.5409446,19.0525994 16.6078425,18.811767 Z M11,13 L20.2498731,3.77461792" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</a>
				</div>
			</div>

			<!-- Product Selection -->
			<div class="ennu-products-section">
				<div class="ennu-products-grid">
					<!-- ENNU Life Membership -->
					<div class="ennu-product-card ennu-product-membership">
						<div class="ennu-product-header">
							<h2 class="ennu-product-title">ENNU Life Membership</h2>
							<div class="ennu-product-badge">Benefits Included:</div>
						</div>
						
						<div class="ennu-product-features">
							<ul class="ennu-features-list">
								<li><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Scheduled Telehealth Visits Every 3-4 Months</li>
								<li><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Direct Access to a Dedicated Care Advocate</li>
								<li><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>In-Depth Biomarker Report (50+ Biomarkers)</li>
								<li><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Personalized Clinical Recommendations</li>
								<li><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Peptide Therapy</li>
								<li><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Comprehensive Health + Family History Analysis</li>
								<li><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Physical Exam</li>
								<li><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Your Story: Comprehensive report outlining your health history, lab results, goals, and personalized plan</li>
								<li><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Access to Premium Pharmaceuticals at Member-Only Pricing</li>
							</ul>
						</div>
						
						<div class="ennu-product-pricing">
							<div class="ennu-price-display">
								<span class="ennu-price-amount">$1,788</span>
								<span class="ennu-price-savings">Pay in full and save $447</span>
							</div>
							<div class="ennu-product-buttons">
								<a href="<?php echo esc_url( $this->get_page_id_url( 'membership-yearly' ) ); ?>" class="ennu-product-btn ennu-btn-primary">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<path d="M5 12h14M12 5l7 7-7 7"/>
									</svg>
									<span>$1,341 Yearly</span>
								</a>
								<a href="<?php echo esc_url( $this->get_page_id_url( 'membership-monthly' ) ); ?>" class="ennu-product-btn ennu-btn-secondary">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<path d="M5 12h14M12 5l7 7-7 7"/>
									</svg>
									<span>$149 Monthly</span>
								</a>
							</div>
						</div>
					</div>

					<!-- Comprehensive Diagnostics -->
					<div class="ennu-product-card ennu-product-diagnostics">
						<div class="ennu-product-header">
							<h2 class="ennu-product-title">ENNU Life Comprehensive Diagnostics</h2>
							<div class="ennu-product-badge">Benefits Included:</div>
						</div>
						
						<div class="ennu-product-features">
							<ul class="ennu-features-list">
								<li><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>In-Depth Biomarker Report (50+ Biomarkers)</li>
								<li><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Advanced Review of Lab Results</li>
								<li><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Personalized Clinical Recommendations</li>
								<li><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Comprehensive Health + Family History Analysis</li>
								<li><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Physical Exam</li>
								<li><svg viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>Your Story: Comprehensive report outlining your health history, lab results, goals, and personalized plan</li>
							</ul>
						</div>
						
						<div class="ennu-product-pricing">
							<div class="ennu-price-display">
								<span class="ennu-price-amount">$599</span>
								<span class="ennu-price-savings">One-time comprehensive assessment</span>
							</div>
							<div class="ennu-product-buttons">
								<a href="<?php echo esc_url( $this->get_page_id_url( 'comprehensive-diagnostics' ) ); ?>" class="ennu-product-btn ennu-btn-primary">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
										<path d="M5 12h14M12 5l7 7-7 7"/>
									</svg>
									<span>Get Started - $599</span>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<style>
		.ennu-signup-container {
			max-width: 1200px;
			margin: 0 auto;
			padding: 2rem 1rem;
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
		}

		/* Hero Section */
		.ennu-signup-hero {
			text-align: center;
			margin-bottom: 4rem;
			padding: 3rem 0;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			border-radius: 20px;
			color: white;
		}

		.ennu-signup-title {
			font-size: 3.5rem;
			font-weight: 700;
			margin-bottom: 1.5rem;
			line-height: 1.2;
		}

		.ennu-signup-title-line {
			display: block;
			opacity: 0;
			animation: slideInUp 0.6s ease forwards;
		}

		.ennu-signup-title-line:nth-child(1) { animation-delay: 0.1s; }
		.ennu-signup-title-line:nth-child(2) { animation-delay: 0.2s; }
		.ennu-signup-title-line:nth-child(3) { animation-delay: 0.3s; }
		.ennu-signup-title-line:nth-child(4) { animation-delay: 0.4s; }
		.ennu-signup-title-line:nth-child(5) { animation-delay: 0.5s; }

		@keyframes slideInUp {
			from {
				opacity: 0;
				transform: translateY(30px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		.ennu-signup-subtitle {
			font-size: 1.25rem;
			max-width: 600px;
			margin: 0 auto;
			opacity: 0.9;
			line-height: 1.6;
		}

		/* Process Steps */
		.ennu-signup-process {
			margin-bottom: 4rem;
		}

		.ennu-process-step {
			display: flex;
			align-items: flex-start;
			margin-bottom: 2rem;
			padding: 1.5rem;
			background: white;
			border-radius: 15px;
			box-shadow: 0 4px 20px rgba(0,0,0,0.1);
			transition: transform 0.3s ease;
		}

		.ennu-process-step:hover {
			transform: translateY(-5px);
		}

		.ennu-process-icon {
			width: 60px;
			height: 60px;
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 1.5rem;
			font-weight: bold;
			margin-right: 1.5rem;
			flex-shrink: 0;
		}

		.ennu-process-content h3 {
			margin: 0 0 0.5rem 0;
			font-size: 1.25rem;
			font-weight: 600;
			color: #333;
		}

		.ennu-process-content p {
			margin: 0;
			color: #666;
			line-height: 1.6;
		}

		/* Contact Section */
		.ennu-signup-contact {
			text-align: center;
			margin-bottom: 4rem;
			padding: 2rem;
			background: #f8f9fa;
			border-radius: 15px;
		}

		.ennu-contact-text {
			font-size: 1.1rem;
			color: #666;
			margin-bottom: 1.5rem;
		}

		.ennu-contact-team {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 1rem;
			flex-wrap: wrap;
		}

		.ennu-team-avatars {
			display: flex;
			gap: 0.5rem;
		}

		.ennu-avatar {
			width: 60px;
			height: 60px;
			border-radius: 50%;
			border: 3px solid white;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
		}

		.ennu-contact-btn {
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
			padding: 0.75rem 1.5rem;
			background: #667eea;
			color: white;
			text-decoration: none;
			border-radius: 50px;
			font-weight: 600;
			transition: all 0.3s ease;
		}

		.ennu-contact-btn:hover {
			background: #5a6fd8;
			transform: translateY(-2px);
			color: white;
		}

		/* Products Section */
		.ennu-products-section {
			margin-bottom: 3rem;
		}

		.ennu-products-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
			gap: 2rem;
		}

		.ennu-product-card {
			background: white;
			border-radius: 20px;
			padding: 2rem;
			box-shadow: 0 10px 40px rgba(0,0,0,0.1);
			transition: transform 0.3s ease, box-shadow 0.3s ease;
			border: 2px solid transparent;
		}

		.ennu-product-card:hover {
			transform: translateY(-10px);
			box-shadow: 0 20px 60px rgba(0,0,0,0.15);
		}

		.ennu-product-membership {
			border-color: #667eea;
		}

		.ennu-product-diagnostics {
			border-color: #764ba2;
		}

		.ennu-product-header {
			text-align: center;
			margin-bottom: 2rem;
		}

		.ennu-product-title {
			font-size: 1.75rem;
			font-weight: 700;
			margin: 0 0 1rem 0;
			color: #333;
		}

		.ennu-product-badge {
			display: inline-block;
			background: #333;
			color: white;
			padding: 0.5rem 1rem;
			border-radius: 25px;
			font-size: 0.875rem;
			font-weight: 600;
		}

		.ennu-features-list {
			list-style: none;
			padding: 0;
			margin: 0 0 2rem 0;
		}

		.ennu-features-list li {
			display: flex;
			align-items: flex-start;
			margin-bottom: 1rem;
			padding-left: 0;
		}

		.ennu-features-list svg {
			width: 20px;
			height: 20px;
			color: #28a745;
			margin-right: 0.75rem;
			margin-top: 0.125rem;
			flex-shrink: 0;
		}

		.ennu-features-list li span {
			color: #555;
			line-height: 1.5;
		}

		.ennu-product-pricing {
			text-align: center;
		}

		.ennu-price-display {
			margin-bottom: 1.5rem;
		}

		.ennu-price-amount {
			display: block;
			font-size: 2.5rem;
			font-weight: 700;
			color: #333;
			margin-bottom: 0.5rem;
		}

		.ennu-price-savings {
			display: block;
			font-size: 0.9rem;
			color: #666;
		}

		.ennu-product-buttons {
			display: flex;
			flex-direction: column;
			gap: 1rem;
		}

		.ennu-product-btn {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			gap: 0.5rem;
			padding: 1rem 2rem;
			border-radius: 50px;
			text-decoration: none;
			font-weight: 600;
			font-size: 1.1rem;
			transition: all 0.3s ease;
			border: none;
			cursor: pointer;
		}

		.ennu-btn-primary {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
		}

		.ennu-btn-primary:hover {
			transform: translateY(-2px);
			box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
			color: white;
		}

		.ennu-btn-secondary {
			background: #333;
			color: white;
		}

		.ennu-btn-secondary:hover {
			background: #555;
			transform: translateY(-2px);
			color: white;
		}

		/* Responsive Design */
		@media (max-width: 768px) {
			.ennu-signup-title {
				font-size: 2.5rem;
			}

			.ennu-products-grid {
				grid-template-columns: 1fr;
			}

			.ennu-process-step {
				flex-direction: column;
				text-align: center;
			}

			.ennu-process-icon {
				margin-right: 0;
				margin-bottom: 1rem;
			}

			.ennu-contact-team {
				flex-direction: column;
			}
		}
		</style>
		<?php
				return ob_get_clean();
	}

	/**
	 * Trigger HubSpot real-time sync
	 *
	 * @param int $user_id WordPress user ID
	 * @param string $assessment_type Assessment type
	 * @param array $form_data Form data
	 */
	private function trigger_hubspot_sync( $user_id, $assessment_type, $form_data ) {
		// Check if HubSpot sync is enabled
		$sync_enabled = get_option( 'ennu_hubspot_sync_enabled', true );
		
		if ( ! $sync_enabled ) {
			$this->_log_submission_debug( 'HubSpot sync is disabled. Skipping sync.' );
			return;
		}

		// Check if HubSpot Bulk Field Creator class exists
		if ( ! class_exists( 'ENNU_HubSpot_Bulk_Field_Creator' ) ) {
			$this->_log_submission_debug( 'ENNU_HubSpot_Bulk_Field_Creator class not found. Skipping sync.' );
			return;
		}

		try {
			// Initialize HubSpot sync
			$hubspot_sync = new ENNU_HubSpot_Bulk_Field_Creator();
			
			// Trigger the sync
			$sync_result = $hubspot_sync->sync_assessment_to_hubspot( $user_id, $assessment_type, $form_data );
			
			$this->_log_submission_debug( 'HubSpot sync completed.', $sync_result );
			
			// Store sync result in user meta for debugging
			update_user_meta( $user_id, 'ennu_hubspot_sync_result_' . $assessment_type, $sync_result );
			
		} catch ( Exception $e ) {
			$this->_log_submission_debug( 'HubSpot sync failed: ' . $e->getMessage() );
			error_log( "ENNU HubSpot Sync Error: " . $e->getMessage() );
		}
	}

	/**
	 * AJAX handler for testing manual HubSpot sync
	 */
	public function ajax_test_manual_hubspot_sync() {
		check_ajax_referer( 'test_hubspot_sync', 'nonce' );
		
		$user_id = intval( $_POST['user_id'] );
		
		// Define test form data for the AJAX handler
		$test_form_data = array(
			'assessment_type' => 'weight_loss',
			'first_name' => 'Test',
			'last_name' => 'User',
			'email' => get_user_by( 'ID', $user_id )->user_email,
			'wl_q2' => 'lose_20_50',
			'wl_q3' => 'balanced',
			'wl_q4' => '3_4',
			'wl_q5' => '7_8',
			'wl_q6' => 'moderate',
			'wl_q7' => 'some_success',
			'wl_q8' => 'sometimes',
			'wl_q9' => array( 'pcos', 'none' ),
			'wl_q10' => 'very',
			'wl_q11' => 'both',
			'wl_q12' => 'yes',
			'wl_q13' => 'somewhat'
		);
		
		if ( ! class_exists( 'ENNU_HubSpot_Bulk_Field_Creator' ) ) {
			wp_send_json_error( array( 'message' => 'HubSpot class not found' ) );
			return;
		}
		
		try {
			$hubspot_sync = new ENNU_HubSpot_Bulk_Field_Creator();
			$result = $hubspot_sync->sync_assessment_to_hubspot( $user_id, 'weight_loss', $test_form_data );
			
			wp_send_json_success( $result );
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * AJAX handler for testing assessment completion hook
	 */
	public function ajax_test_assessment_hook() {
		check_ajax_referer( 'test_assessment_hook', 'nonce' );
		
		$user_id = intval( $_POST['user_id'] );
		
		try {
			// Trigger the assessment completion hook
			do_action( 'ennu_assessment_completed', $user_id, 'weight_loss' );
			
			wp_send_json_success( array(
				'user_id' => $user_id,
				'assessment_type' => 'weight_loss',
				'hook_fired' => true
			) );
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * AJAX handler for checking debug logs
	 */
	public function ajax_check_debug_logs() {
		check_ajax_referer( 'check_debug_logs', 'nonce' );
		
		$debug_log_path = WP_CONTENT_DIR . '/debug.log';
		$logs = array();
		$hubspot_logs_count = 0;
		$assessment_logs_count = 0;
		
		if ( file_exists( $debug_log_path ) ) {
			$log_content = file_get_contents( $debug_log_path );
			$log_lines = explode( "\n", $log_content );
			
			// Get last 100 lines
			$recent_logs = array_slice( $log_lines, -100 );
			
			foreach ( $recent_logs as $line ) {
				if ( strpos( $line, 'ENNU HubSpot' ) !== false ) {
					$hubspot_logs_count++;
					$logs[] = $line;
				}
				if ( strpos( $line, 'ennu_assessment_completed' ) !== false ) {
					$assessment_logs_count++;
					$logs[] = $line;
				}
			}
		}
		
		wp_send_json_success( array(
			'hubspot_logs_count' => $hubspot_logs_count,
			'assessment_logs_count' => $assessment_logs_count,
			'logs' => $logs
		) );
	}

	/**
	 * AJAX handler for pillar modal content
	 */
	public function ajax_get_pillar_modal() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_pillar_modal' ) ) {
			wp_die( 'Security check failed' );
		}

		$pillar = sanitize_text_field( $_POST['pillar'] );
		$modal_content = ENNU_Missing_Data_Notice::get_pillar_modal_content( $pillar );

		echo $modal_content;
		wp_die();
	}

	/**
	 * Check for missing redirect pages and show admin notice
	 */
	public function check_missing_redirect_pages() {
		$settings = get_option( 'ennu_life_settings', array() );
		$page_mappings = $settings['page_mappings'] ?? array();
		
		$required_pages = array(
			'weight-loss-assessment-details',
			'health-optimization-assessment-details',
			'hormone-assessment-details',
			'menopause-assessment-details',
			'testosterone-assessment-details',
			'sleep-assessment-details',
			'skin-assessment-details',
			'hair-assessment-details',
			'ed-treatment-assessment-details',
			'health-assessment-details',
			'welcome-assessment-details'
		);
		
		$missing_pages = array();
		foreach ( $required_pages as $page_slug ) {
			if ( ! isset( $page_mappings[ $page_slug ] ) || empty( $page_mappings[ $page_slug ] ) ) {
				$missing_pages[] = $page_slug;
			}
		}
		
		if ( ! empty( $missing_pages ) ) {
			echo '<div class="notice notice-warning"><p>';
			echo '<strong>ENNU Life:</strong> The following assessment results pages are not configured: ';
			echo implode( ', ', $missing_pages );
			echo '. Please configure them in the ENNU Life settings to enable proper redirects.';
			echo '</p></div>';
		}
	}
}
// Initialize the class
// new ENNU_Assessment_Shortcodes();

