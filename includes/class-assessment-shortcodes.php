<?php
/**
 * ENNU Life Assessment Shortcodes Class - Fixed Version
 *
 * Handles all assessment shortcodes with proper security, performance,
 * and WordPress standards compliance.
 *
 * @package ENNU_Life
 * @version 14.1.11
 * @author ENNU Life Development Team
 * @since 14.1.11
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
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Initialize the class on the 'init' hook.
	 */
	public function init() {
		error_log('ENNU Shortcodes: init() called.');
		
		// Ensure the scoring system class is available
		if ( ! class_exists( 'ENNU_Assessment_Scoring' ) ) {
			error_log('ENNU Shortcodes: ERROR - ENNU_Assessment_Scoring class not found!');
			return;
		}
		
		$this->all_definitions = ENNU_Assessment_Scoring::get_all_definitions();
		error_log('ENNU Shortcodes: Loaded ' . count($this->all_definitions) . ' assessment definitions.');
	
		$this->init_assessments();
		$this->setup_hooks();
		$this->register_shortcodes();
		error_log('ENNU Shortcodes: init() completed.');
	}

	/**
	 * Initialize assessment configurations.
	 * This is now hooked to 'init' to allow for translations.
	 */
	public function init_assessments() {
		$this->assessments = array(
			'welcome_assessment'          => array(
				'title'       => __( 'Welcome Assessment', 'ennulifeassessments' ),
				'description' => __( 'Let\'s get to know you and your health goals.', 'ennulifeassessments' ),
				'questions'   => 6,
				'theme_color' => '#5A67D8', // Indigo color
				'icon_set'    => 'hormone',
			),
			'hair_assessment'             => array(
				'title'       => __( 'Hair Assessment', 'ennulifeassessments' ),
				'description' => __( 'Comprehensive hair health evaluation', 'ennulifeassessments' ),
				'questions'   => 11,
				'theme_color' => '#667eea',
				'icon_set'    => 'hair',
			),
			'hair_restoration_assessment' => array(
				'title'       => __( 'Hair Restoration Assessment', 'ennulifeassessments' ),
				'description' => __( 'Advanced hair restoration evaluation', 'ennulifeassessments' ),
				'questions'   => 11,
				'theme_color' => '#764ba2',
				'icon_set'    => 'restoration',
			),
			'ed_treatment_assessment'     => array(
				'title'       => __( 'ED Treatment Assessment', 'ennulifeassessments' ),
				'description' => __( 'Confidential ED treatment evaluation', 'ennulifeassessments' ),
				'questions'   => 12,
				'theme_color' => '#f093fb',
				'icon_set'    => 'medical',
			),
			'weight_loss_assessment'      => array(
				'title'       => __( 'Weight Loss Assessment', 'ennulifeassessments' ),
				'description' => __( 'Personalized weight management evaluation', 'ennulifeassessments' ),
				'questions'   => 13,
				'theme_color' => '#4facfe',
				'icon_set'    => 'fitness',
			),
			'weight_loss_quiz'            => array(
				'title'       => __( 'Weight Loss Quiz', 'ennulifeassessments' ),
				'description' => __( 'Quick weight loss readiness quiz', 'ennulifeassessments' ),
				'questions'   => 8,
				'theme_color' => '#43e97b',
				'icon_set'    => 'quiz',
			),
			'health_assessment'           => array(
				'title'       => __( 'Health Assessment', 'ennulifeassessments' ),
				'description' => __( 'Comprehensive health evaluation', 'ennulifeassessments' ),
				'questions'   => 11,
				'theme_color' => '#fa709a',
				'icon_set'    => 'health',
			),
			'skin_assessment'             => array(
				'title'       => __( 'Skin Assessment', 'ennulifeassessments' ),
				'description' => __( 'Comprehensive skin health evaluation', 'ennulifeassessments' ),
				'questions'   => 9,
				'theme_color' => '#a8edea',
				'icon_set'    => 'skin',
			),
			'advanced_skin_assessment'    => array(
				'title'       => __( 'Advanced Skin Assessment', 'ennulifeassessments' ),
				'description' => __( 'Detailed skin health analysis', 'ennulifeassessments' ),
				'questions'   => 9,
				'theme_color' => '#a8edea',
				'icon_set'    => 'skin',
			),
			'skin_assessment_enhanced'    => array(
				'title'       => __( 'Skin Assessment Enhanced', 'ennulifeassessments' ),
				'description' => __( 'Enhanced skin evaluation', 'ennulifeassessments' ),
				'questions'   => 8,
				'theme_color' => '#d299c2',
				'icon_set'    => 'skincare',
			),
			'hormone_assessment'          => array(
				'title'       => __( 'Hormone Assessment', 'ennulifeassessments' ),
				'description' => __( 'Comprehensive hormone evaluation', 'ennulifeassessments' ),
				'questions'   => 12,
				'theme_color' => '#ffecd2',
				'icon_set'    => 'hormone',
			),
			// --- NEW ASSESSMENTS ---
			'sleep_assessment'            => array(
				'title'       => __( 'Sleep Assessment', 'ennulifeassessments' ),
				'description' => __( 'Placeholder for sleep assessment description.', 'ennulifeassessments' ),
				'questions'   => 1,
				'theme_color' => '#4a90e2',
				'icon_set'    => 'quiz',
			),
			'menopause_assessment'        => array(
				'title'       => __( 'Menopause Assessment', 'ennulifeassessments' ),
				'description' => __( 'Placeholder for menopause assessment description.', 'ennulifeassessments' ),
				'questions'   => 1,
				'theme_color' => '#d0021b',
				'icon_set'    => 'medical',
			),
			'testosterone_assessment'     => array(
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
		error_log('ENNU Shortcodes: register_shortcodes() called.');
		error_log('ENNU Shortcodes: all_definitions count: ' . count($this->all_definitions));
		if ( empty( $this->all_definitions ) ) {
			error_log('ENNU Shortcodes: No definitions found, cannot register shortcodes.');
			return;
		}

		// Dynamically register assessment, results, and details shortcodes
		foreach ( $this->all_definitions as $assessment_key => $config ) {
			// e.g., [ennu-hair-assessment] or [ennu-hair] 
			$assessment_slug = str_replace( '_', '-', $assessment_key );
			add_shortcode( "ennu-{$assessment_slug}", array( $this, 'render_assessment_shortcode' ) );
			error_log('ENNU Shortcodes: Registered assessment shortcode: ennu-' . $assessment_slug);

			// e.g., [ennu-hair-results]
			$results_slug = $assessment_slug . '-results';
			add_shortcode( "ennu-{$results_slug}", array( $this, 'render_thank_you_page' ) );
			error_log('ENNU Shortcodes: Registered results shortcode: ennu-' . $results_slug);
			
			// e.g., [ennu-hair-assessment-details]
			$details_slug = $assessment_slug . '-assessment-details';
			add_shortcode(
				"ennu-{$details_slug}",
				function( $atts ) use ( $assessment_key ) {
					return $this->render_detailed_results_page( $atts, '', $assessment_key );
				}
			);
			error_log('ENNU Shortcodes: Registered details shortcode: ennu-' . $details_slug);
		}

		// Register the core, non-assessment-specific shortcodes
		add_shortcode( 'ennu-user-dashboard', array( $this, 'render_user_dashboard' ) );
		add_shortcode( 'ennu-assessment-results', array( $this, 'render_thank_you_page' ) ); // Generic fallback
		error_log('ENNU Shortcodes: Registered core shortcodes: ennu-user-dashboard, ennu-assessment-results');
	}

	/**
	 * Setup WordPress hooks for AJAX and asset enqueuing.
	 */
	private function setup_hooks() {
		// All hooks are now registered in the main plugin file for centralized control.
		// This method is kept for clarity and future use if needed.
		add_action( 'wp_ajax_nopriv_ennu_check_email', array( $this, 'ajax_check_email_exists' ) );
		add_action( 'wp_ajax_ennu_submit_assessment', array( $this, 'handle_assessment_submission' ) );
		add_action( 'wp_ajax_nopriv_ennu_submit_assessment', array( $this, 'handle_assessment_submission' ) );
		add_action( 'wp_ajax_ennu_check_auth_state', array( $this, 'ajax_check_auth_state' ) );
		add_action( 'wp_ajax_nopriv_ennu_check_auth_state', array( $this, 'ajax_check_auth_state' ) );
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
		// Extract assessment type from shortcode tag (keep hyphens, only remove 'ennu-' prefix)
		$assessment_type = str_replace( 'ennu-', '', $tag );
		$config          = $this->all_definitions[ $assessment_type ] ?? null;

		// --- NEW: Welcome Shortcode Logic for Logged-In Users ---
		if ( $assessment_type === 'welcome_assessment' && is_user_logged_in() ) {
			$dashboard_url = $this->get_page_id_url( 'dashboard' );
			$booking_url   = $this->get_page_id_url( 'call' );
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
				'billing_phone' => get_user_meta( $user_id, 'billing_phone', true ),
				'dob_combined'  => get_user_meta( $user_id, 'ennu_global_user_dob_combined', true ),
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
			 
			<!-- Assessment Header -->
			<div class="assessment-header">
				<h1 class="assessment-title"><?php echo esc_html( $config['title'] ); ?></h1>
				<p class="assessment-description"><?php echo esc_html( $config['description'] ); ?></p>
				
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
			</div>
			
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
		
		$output    = '';
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

		$active_class       = $question_number === 1 ? 'active' : '';
		$is_global_slide    = ! empty( $question['global_key'] );

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
				$all_questions = $this->get_assessment_questions( $assessment_type );
				$questions = isset( $all_questions['questions'] ) ? $all_questions['questions'] : $all_questions;
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
				return $this->_render_dob_dropdowns_question( $config, $name );
			case 'multiselect':
				return $this->_render_multiselect_question( $config, $name, $saved_value );
			case 'first_last_name':
				return $this->_render_first_last_name_question( $config, $name );
			case 'email_phone':
				return $this->_render_email_phone_question( $config, $name );
			case 'height_weight':
				return $this->_render_height_weight_question( $config, $name );
			case 'contact_info':
				return $this->_render_contact_info_question( $config, $name );
			default: // Default to 'radio'
				return $this->_render_radio_question( $config, $name, $saved_value );
		}
	}

	/**
	 * Renders the HTML for a 'Date of Birth' question.
	 */
	private function _render_dob_dropdowns_question( $question, $question_key ) {
		$current_user_data = is_user_logged_in() ? wp_get_current_user() : null;
		$dob               = $current_user_data ? get_user_meta( $current_user_data->ID, 'ennu_global_user_dob_combined', true ) : '';
		$is_logged_in      = ! empty( $dob );
		$dob_parts         = ! empty( $dob ) ? explode( '-', $dob ) : array( '', '', '' );
		$year_val          = $dob_parts[0];
		$month_val         = $dob_parts[1];
		$day_val           = $dob_parts[2];

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
					<select id="<?php echo esc_attr( $question_key ); ?>_month" name="dob_month" required>
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
					<select id="<?php echo esc_attr( $question_key ); ?>_day" name="dob_day" required>
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
					<select id="<?php echo esc_attr( $question_key ); ?>_year" name="dob_year" required>
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
				<input type="hidden" name="dob_combined" class="dob-combined" />
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
	 * Renders the HTML for a 'height_weight' question.
	 */
	private function _render_height_weight_question( $question, $question_key ) {
		$height_weight_data = is_user_logged_in() ? get_user_meta( get_current_user_id(), 'ennu_global_height_weight', true ) : array();
		$height_ft          = is_array( $height_weight_data ) && isset( $height_weight_data['ft'] ) ? $height_weight_data['ft'] : '';
		$height_in          = is_array( $height_weight_data ) && isset( $height_weight_data['in'] ) ? $height_weight_data['in'] : '';
		$weight_lbs         = is_array( $height_weight_data ) && isset( $height_weight_data['lbs'] ) ? $height_weight_data['lbs'] : '';

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
	private function _render_contact_info_question( $question, $question_key ) {
		$current_user = is_user_logged_in() ? wp_get_current_user() : null;
		$first_name = $current_user ? $current_user->first_name : '';
		$last_name  = $current_user ? $current_user->last_name : '';
		$email      = $current_user ? $current_user->user_email : '';
		$phone      = $current_user ? get_user_meta( $current_user->ID, 'billing_phone', true ) : '';
		$readonly   = $current_user ? 'readonly' : '';

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
						<input type="radio" id="<?php echo esc_attr( $question_key ); ?>_<?php echo esc_attr( $option_value ); ?>" name="<?php echo esc_attr( $question_key ); ?>" value="<?php echo esc_attr( $option_value ); ?>" <?php checked( $pre_selected_value, $option_value ); ?> required>
						<label for="<?php echo esc_attr( $question_key ); ?>_<?php echo esc_attr( $option_value ); ?>"><?php echo esc_html( $option_label ); ?></label>
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
	private function _render_first_last_name_question( $question, $question_key ) {
		$current_user = is_user_logged_in() ? wp_get_current_user() : null;
		$first_name = $current_user ? $current_user->first_name : '';
		$last_name  = $current_user ? $current_user->last_name : '';
		$readonly   = $current_user ? 'readonly' : '';

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
	private function _render_email_phone_question( $question, $question_key ) {
		$current_user = is_user_logged_in() ? wp_get_current_user() : null;
		$email    = $current_user ? $current_user->user_email : '';
		$phone    = $current_user ? get_user_meta( $current_user->ID, 'billing_phone', true ) : '';
		$readonly = $current_user ? 'readonly' : '';

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
		return $this->all_definitions[ $assessment_type ] ?? array();
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
		$this->_log_submission_debug( '--- Submission process started ---' );

		// 1. Security Check: Verify AJAX nonce
		$this->_log_submission_debug( 'Verifying nonce...' );
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );
		$this->_log_submission_debug( 'Nonce verified successfully.' );

		// Future-proofing: Capture user IP for potential rate-limiting or security audits.
		$user_ip = $_SERVER['REMOTE_ADDR'];
		$this->_log_submission_debug( 'User IP captured.', $user_ip );
		$this->_log_submission_debug( 'Raw POST data:', $_POST );

		// 2. Get and Sanitize Data
		$form_data = $this->sanitize_assessment_data( $_POST );
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

		// 6. Save Global Data (New Robust Handler)
		$this->_log_submission_debug( 'Saving global meta data...' );
		$this->save_global_meta( $user_id, $form_data );
		$this->sync_core_data_to_wp( $user_id, $form_data );
		$this->_log_submission_debug( 'Global meta data saved.' );

		// 7. Save Assessment-Specific Data
		$this->_log_submission_debug( 'Saving assessment-specific meta data...' );
		$this->save_assessment_specific_meta( $user_id, $form_data );
		$this->_log_submission_debug( 'Assessment-specific meta data saved.' );

		// 8. ROUTE TO THE CORRECT ENGINE
		$assessment_config = $this->all_definitions[ $form_data['assessment_type'] ] ?? array();
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

			$redirect_url = $this->get_thank_you_url( 'health_optimization_assessment', $results_token );
			$this->_log_submission_debug( 'Qualitative flow complete. Sending redirect URL.', $redirect_url );
			
			// Include auth state data in response for frontend
			$auth_state_data = $this->get_current_auth_state_data();
			
			wp_send_json_success( array( 
				'redirect_url' => $redirect_url,
				'auth_state' => $auth_state_data
			) );

		} else {
			// --- NEW: Quantitative Engine Flow using the new Orchestrator ---
			$this->_log_submission_debug( 'Processing with NEW Quantitative engine.' );
			
			$scores = ENNU_Assessment_Scoring::calculate_scores_for_assessment( $form_data['assessment_type'], $form_data );
			$this->_log_submission_debug( 'Initial scores calculated.', $scores );

			if ( $scores ) {
				$completion_time = date( 'Y-m-d H:i:s.u' );

				// Save the scores for this specific assessment
				update_user_meta( $user_id, 'ennu_' . $form_data['assessment_type'] . '_score_calculated_at', $completion_time );
				update_user_meta( $user_id, 'ennu_' . $form_data['assessment_type'] . '_calculated_score', $scores['overall_score'] );
				update_user_meta( $user_id, 'ennu_' . $form_data['assessment_type'] . '_score_interpretation', ENNU_Assessment_Scoring::get_score_interpretation( $scores['overall_score'] ) );
				update_user_meta( $user_id, 'ennu_' . $form_data['assessment_type'] . '_category_scores', $scores['category_scores'] );
				update_user_meta( $user_id, 'ennu_' . $form_data['assessment_type'] . '_pillar_scores', $scores['pillar_scores'] );
				
				// Now, calculate and save all the master user scores
				ENNU_Assessment_Scoring::calculate_and_save_all_user_scores( $user_id );
				$this->_log_submission_debug( 'All master user scores calculated and saved.' );

				$results_token = $this->store_results_transient( $user_id, $form_data['assessment_type'], $scores, $form_data );
				$this->_log_submission_debug( 'Results transient stored.', $results_token );

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
			$redirect_url = $this->get_thank_you_url( $form_data['assessment_type'], $results_token ?? null );
			$this->_log_submission_debug( 'Quantitative flow complete. Sending redirect URL.', $redirect_url );
			
			// Include auth state data in response for frontend
			$auth_state_data = $this->get_current_auth_state_data();
			
			wp_send_json_success( array( 
				'redirect_url' => $redirect_url,
				'auth_state' => $auth_state_data
			) );
		}
		$this->_log_submission_debug( '--- Submission process finished ---' );
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
		$token = wp_generate_password( 32, false );
		$data_to_store = array(
			'user_id'         => $user_id,
			'assessment_type' => $assessment_type,
			'score'           => $scores['overall_score'],
			'interpretation'  => ENNU_Assessment_Scoring::get_score_interpretation( $scores['overall_score'] ),
			'category_scores' => $scores['category_scores'],
			'answers'         => $form_data,
		);
		// Use a manual transient to avoid issues with object caching on some hosts
		$this->_set_manual_transient( 'ennu_results_' . $token, $data_to_store, HOUR_IN_SECONDS );
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
		// Security check
		check_ajax_referer('ennu_ajax_nonce', 'nonce');

		$email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

		if (empty($email) || !is_email($email)) {
			wp_send_json_error(array('message' => 'Invalid email provided.'), 400);
			return;
		}

		if (email_exists($email)) {
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
		// Security check
		check_ajax_referer('ennu_ajax_nonce', 'nonce');

		$response = array(
			'is_logged_in' => is_user_logged_in(),
			'needs_contact_form' => true,
			'auto_submit_ready' => false,
			'user_data' => array()
		);

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$user_id = $user->ID;
			
			// Get current user data
			$current_user_data = array(
				'first_name' => $user->first_name,
				'last_name' => $user->last_name,
				'email' => $user->user_email,
				'billing_phone' => get_user_meta( $user_id, 'billing_phone', true ),
			);
			
			// Check if user needs contact form
			$needs_contact_form = $this->user_needs_contact_form( $current_user_data );
			
			$response['needs_contact_form'] = $needs_contact_form;
			$response['auto_submit_ready'] = ! $needs_contact_form;
			$response['user_data'] = $current_user_data;
			$response['missing_fields'] = $needs_contact_form ? $this->get_missing_contact_fields( $current_user_data ) : array();
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
				$billing_phone = get_user_meta( $user->ID, 'billing_phone', true );
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
		$questions       = $config['questions'] ?? $config;

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
	private function save_global_meta( $user_id, $data ) {
		$assessment_type = $data['assessment_type'];
		$questions       = $this->get_assessment_questions( $assessment_type );

		foreach ( $questions as $question_id => $question_def ) {
			if ( isset( $question_def['global_key'] ) ) {
				$meta_key      = 'ennu_global_' . $question_def['global_key'];
				$value_to_save = null;

				// Handle special field types that have different data keys
				if ( $question_def['type'] === 'dob_dropdowns' ) {
					if ( isset( $data['dob_combined'] ) ) {
						$value_to_save = sanitize_text_field( $data['dob_combined'] );
					}
				} elseif ( $question_def['type'] === 'height_weight' ) {
					if ( isset( $data['height_ft'], $data['height_in'], $data['weight_lbs'] ) ) {
						$value_to_save = array(
							'ft'  => sanitize_text_field( $data['height_ft'] ),
							'in'  => sanitize_text_field( $data['height_in'] ),
							'lbs' => sanitize_text_field( $data['weight_lbs'] ),
						);
					}
				} else {
					// Standard fields
					if ( isset( $data[ $question_id ] ) ) {
						$value_to_save = $data[ $question_id ];
					}
				}

				// Save the data if a value was found
				if ( $value_to_save !== null ) {
					if ( is_array( $value_to_save ) ) {
						// No need to sanitize here as individual parts are sanitized above
						update_user_meta( $user_id, $meta_key, $value_to_save );
					} else {
						update_user_meta( $user_id, $meta_key, sanitize_text_field( $value_to_save ) );
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
			update_user_meta( $user_id, 'billing_phone', $data['billing_phone'] );
		}
	}

	/**
	 * Send assessment notification email
	 *
	 * @param array $data Assessment data
	 */
	private function send_assessment_notification( $data ) {
		$to      = $data['contact_email'];
		$subject = sprintf(
			__( 'Your %s Results - ENNU Life', 'ennulifeassessments' ),
			$this->assessments[ $data['assessment_type'] ]['title']
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
			$this->assessments[ $data['assessment_type'] ]['title']
		);
		$admin_message = $this->get_admin_notification_template( $data );

		wp_mail( $admin_email, $admin_subject, $admin_message, $headers );
	}

	/**
	 * Get assessment email template
	 *
	 * @param array $data Assessment data
	 * @return string
	 */
	private function get_assessment_email_template( $data ) {
		$assessment_title = $this->assessments[ $data['assessment_type'] ]['title'];

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
		$assessment_title = $this->assessments[ $data['assessment_type'] ]['title'];

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
	 * Get thank you page URL - FINAL CORRECTED VERSION
	 *
	 * @param string $assessment_type Assessment type
	 * @return string
	 */
	private function get_thank_you_url( $assessment_type, $token = null ) {
		$query_args = array();
		if ( $token ) {
			$query_args['results_token'] = $token;
		}
		
		// Special routing for the new qualitative assessment results page
		if ($assessment_type === 'health_optimization_assessment') {
			return $this->get_page_id_url( 'health-optimization-results', $query_args );
		}
		
		// Correctly map assessment type (e.g., 'hair_assessment') to the results slug (e.g., 'hair-results').
		$slug = str_replace( '_assessment', '-results', $assessment_type );
		
		// Try specific results page first, then fallback to generic results page
		$specific_url = $this->get_page_id_url( $slug, $query_args );
		
		// If specific page doesn't exist in mapping, use generic results page
		$created_pages = get_option( 'ennu_created_pages', array() );
		if ( empty( $created_pages[ $slug ] ) ) {
			return $this->get_page_id_url( 'assessment-results', $query_args );
		}
		
		return $specific_url;
	}

	/**
	 * Render assessment results page
	 *
	 * @param array $atts Shortcode attributes
	 * @return string
	 */
	public function render_results_page( $atts = array() ) {
		// This method now mirrors render_thank_you_page to handle the generic results page.
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
			$content_data        = $content_config[ $assessment_type ] ?? $content_config['default'];
			$result_content      = $content_data['score_ranges'][ $interpretation_key ] ?? $content_data['score_ranges']['fair'];
			$conditional_recs    = $content_data['conditional_recommendations'] ?? array();
			$matched_recs        = array();

			// ... (conditional recs logic can be added here if needed) ...

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

			$data = compact( 'content_data', 'bmi', 'result_content', 'score', 'matched_recs', 'assessment_type', 'category_scores' );

			// Add the shortcode instance to the data so templates can use get_page_id_url
			$data['shortcode_instance'] = $this;

		ob_start();
			ennu_load_template( 'assessment-results.php', $data );
			return ob_get_clean();
		}

		// Handle empty state for the generic results page.
		ob_start();
		ennu_load_template( 'assessment-results-expired.php' );
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
		);

		foreach ( $shortcodes_to_check as $shortcode ) {
			if ( has_shortcode( $post->post_content, $shortcode ) ) {
				// --- UNIFIED AESTHETICS ---
				// Load the main dashboard styles for a consistent "Bio-Metric Canvas" look
				wp_enqueue_style( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/css/user-dashboard.css', array(), ENNU_LIFE_VERSION );
				wp_enqueue_style( 'ennu-details-page-style', ENNU_LIFE_PLUGIN_URL . 'assets/css/assessment-details-page.css', array(), ENNU_LIFE_VERSION );
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
		$results_token     = isset( $_GET['results_token'] ) ? sanitize_text_field( $_GET['results_token'] ) : null;
		$results_transient = $results_token ? $this->_get_manual_transient( 'ennu_results_' . $results_token ) : false;

		if ( $results_transient ) {
			$this->_delete_manual_transient( 'ennu_results_' . $results_token );

			// --- DEFINITIVE DATA HARMONIZATION FIX ---
			$assessment_type = $results_transient['assessment_type'] ?? '';
			$score           = $results_transient['score'] ?? 0;
			$interpretation  = $results_transient['interpretation'] ?? array();
			$interpretation_key = strtolower( $interpretation['level'] ?? 'fair' );

			// 1. Load the master results content configuration
			$content_config_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/results-content.php';
			$content_config      = file_exists( $content_config_file ) ? require $content_config_file : array();
			$assessment_content  = $content_config[ $assessment_type ] ?? $content_config['default'];
			
			// 2. Select the correct content block based on the score
			$result_content = $assessment_content['score_ranges'][ $interpretation_key ] ?? $assessment_content['score_ranges']['fair'];

			// 3. Prepare all necessary URLs for the template's action buttons
			$details_slug       = str_replace( '_assessment', '-assessment-details', $assessment_type );
			$details_button_url = $this->get_page_id_url( $details_slug );
			$dashboard_button_url = $this->get_page_id_url( 'dashboard' );
			$retake_url         = $this->get_assessment_page_url( $assessment_type );

			// 4. Meticulously construct the final data array for the template
			$data = array(
				'user_id'              => $results_transient['user_id'] ?? get_current_user_id(),
				'assessment_type'      => $assessment_type,
				'assessment_title'     => $this->assessments[$assessment_type]['title'] ?? 'Assessment',
				'score'                => $score,
				'category_scores'      => $results_transient['category_scores'] ?? array(),
				'result_content'       => $result_content,
				'matched_recs'         => array(), // This can be enhanced later
				'details_button_url'   => $details_button_url,
				'dashboard_button_url' => $dashboard_button_url,
				'retake_url'           => $retake_url,
			);

			// Load the template with the correctly structured data
			ennu_load_template( 'assessment-results.php', $data );

		} else {
			// Load the expired/empty state template
			ennu_load_template( 'assessment-results-expired.php' );
		}

		return ob_get_clean(); // RETURN ALL CAPTURED OUTPUT
	}

	private function get_user_assessments_data( $user_id ) {
		$user_assessments   = array();
		$assessment_configs = $this->all_definitions;
		$created_pages      = get_option( 'ennu_created_pages', array() );
		$user_gender        = get_user_meta( $user_id, 'ennu_global_gender', true );

		$dashboard_icons = array(
			'hair_assessment'         => '🦱',
			'ed_treatment_assessment' => '❤️‍🩹',
			'weight_loss_assessment'  => '⚖️',
			'health_assessment'       => '❤️',
			'skin_assessment'         => '✨',
			'sleep_assessment'        => '😴',
			'hormone_assessment'      => '🔬',
			'menopause_assessment'    => '🌡️',
			'testosterone_assessment' => '💪',
			'health_optimization_assessment' => '⚙️',
		);

		foreach ( $assessment_configs as $key => $config ) {
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
			
			$assessment_url = $this->get_assessment_page_url($key);
			$details_url    = $this->get_page_id_url( $details_slug );
			
			$score        = get_user_meta( $user_id, 'ennu_' . $key . '_calculated_score', true );
			$is_completed = 'qualitative' === ( $config['assessment_engine'] ?? '' ) ? (bool) get_user_meta( $user_id, 'ennu_' . $key . '_symptom_q1', true ) : ! empty( $score );

			$user_assessments[ $key ] = array(
				'key'         => $key,
				'label'       => $config['title'] ?? ucwords( str_replace( '_', ' ', $key ) ),
				'icon'        => $dashboard_icons[ $key ] ?? '📄',
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
		// Definitive Fix: Add a guard to ensure the assessment type is valid.
		if ( empty( $assessment_type ) || ! isset( $this->all_definitions[ $assessment_type ] ) ) {
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

		// --- ROUTE TO THE CORRECT ENGINE ---
		if ( $is_qualitative ) {
			// Qualitative Engine Flow
			$symptom_data = ENNU_Assessment_Scoring::get_symptom_data_for_user( $user_id );
			if ( empty( $symptom_data ) ) {
				return $this->render_error_message( __( 'You have not yet completed this assessment.', 'ennulifeassessments' ) );
			}
			$report_data = ENNU_Assessment_Scoring::get_health_optimization_report_data( $symptom_data );
			ob_start();
			ennu_load_template( 'health-optimization-results.php', $report_data );
			return ob_get_clean();

		} else {
			// Quantitative Engine Flow
			$score = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_calculated_score', true );
			if ( ! $score ) {
				return $this->render_error_message( __( 'You have not yet completed this assessment.', 'ennulifeassessments' ) );
			}

			$data = $this->get_quantitative_dossier_data( $user_id, $assessment_type );

			// Failsafe if data retrieval fails
			if ( empty( $data ) ) {
				return $this->render_error_message( __( 'Could not retrieve assessment data.', 'ennulifeassessments' ) );
			}

			// Add the shortcode instance to the data so templates can use get_page_id_url
			$data['shortcode_instance'] = $this;

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

		$score = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_calculated_score', true );
		$interpretation = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_score_interpretation', true );
		$category_scores = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_category_scores', true );
		$score_history = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_historical_scores', true );

		$assessment_type_slug = str_replace( '_assessment', '', $assessment_type );
		$dob = get_user_meta( $user_id, 'ennu_global_user_dob_combined', true );
		$age = $dob ? ( new DateTime() )->diff( new DateTime( $dob ) )->y : null;
		$gender = get_user_meta( $user_id, 'ennu_global_gender', true );

		$dashboard_url = $this->get_page_id_url( 'dashboard' );
		$retake_url = $this->get_assessment_page_url( $assessment_type );

		$insights_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/dashboard-insights.php';
		$insights = file_exists( $insights_file ) ? require $insights_file : array();

		$content_config_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/results-content.php';
		$content_config = file_exists( $content_config_file ) ? require $content_config_file : array();
		$content_data = $content_config[ $assessment_type ] ?? $content_config['default'];
		$interpretation_key = isset( $interpretation['level'] ) ? strtolower( $interpretation['level'] ) : 'fair';
		$result_content = $content_data['score_ranges'][ $interpretation_key ] ?? $content_data['score_ranges']['fair'];

		$pillar_scores_meta = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_pillar_scores', true );
		$pillar_scores = ! empty( $pillar_scores_meta ) && is_array( $pillar_scores_meta ) ? $pillar_scores_meta : array();
		
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
		if ( is_array( $score_history ) ) {
			foreach ( $score_history as $entry ) {
				if ( isset( $entry['date'] ) && isset( $entry['score'] ) ) {
					$formatted_score_history[] = array(
						'date' => $entry['date'],
						'score' => (float) $entry['score']
					);
				}
			}
		}
		
		// Enqueue Chart.js and localize data
		wp_enqueue_script( 'chartjs', ENNU_LIFE_PLUGIN_URL . 'assets/js/chart.umd.js', array(), '4.4.1', true );
		wp_enqueue_script( 'chartjs-adapter-date-fns', 'https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js', array( 'chartjs' ), '1.1.0', true );
		wp_enqueue_script( 'ennu-assessment-details', ENNU_LIFE_PLUGIN_URL . 'assets/js/assessment-details.js', array( 'jquery', 'chartjs', 'chartjs-adapter-date-fns' ), ENNU_LIFE_VERSION, true );
		
		wp_localize_script(
			'ennu-assessment-details',
			'assessmentDetailsData',
			array(
				'scoreHistory' => $formatted_score_history,
				'assessmentType' => ucwords( str_replace( '_', ' ', $assessment_type_slug ) )
			)
		);
		
		return compact(
			'current_user', 'age', 'gender', 'assessment_type_slug', 'pillar_scores',
			'pillar_colors', 'category_scores', 'score_history', 'deep_dive_content',
			'score', 'dashboard_url', 'retake_url', 'result_content', 'insights'
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
		
		// Return the nested consultation page URL
		return $this->get_page_id_url( "assessments/{$slug}/consultation" );
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
				'icon'             => '🦱',
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
				'icon'             => '🔒',
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
				'icon'             => '⚖️',
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
				'icon'             => '🏥',
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
				'icon'             => '✨',
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
		ob_start();
		?>
		<div class="ennu-user-dashboard"> <!-- Use the main dashboard class for consistent styling -->
			<div class="starfield"></div>
			<div class="dossier-grid" style="grid-template-columns: 1fr; max-width: 600px; margin: auto;">
				<main class="dossier-main-content">
					<div class="ennu-error" style="text-align: center;">
						<div class="empty-state-icon" style="font-size: 40px; font-weight: bold; color: #ffc107; background: rgba(255, 193, 7, 0.1); width: 80px; height: 80px; line-height: 80px; border-radius: 50%; margin: 0 auto 20px; border: 1px solid #ffc107;">!</div>
						<h2 class="empty-state-title" style="font-size: 28px; font-weight: 700; color: #fff; margin-bottom: 15px;">
							<?php echo esc_html__( 'Assessment Not Available', 'ennulifeassessments' ); ?>
						</h2>
						<p class="empty-state-message" style="font-size: 16px; color: var(--text-light); line-height: 1.6; margin-bottom: 30px;">
							<?php echo esc_html( $message ); ?>
						</p>
						<div class="empty-state-actions" style="display: flex; justify-content: center; gap: 15px;">
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
			ennu_load_template('user-dashboard-logged-out.php', compact('login_url', 'registration_url'));
			return ob_get_clean();
		}

		// Enqueue necessary scripts and styles.
		wp_enqueue_style( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/css/user-dashboard.css', array(), ENNU_LIFE_VERSION );
		wp_enqueue_script( 'chartjs', ENNU_LIFE_PLUGIN_URL . 'assets/js/chart.umd.js', array(), '4.4.1', true );
		wp_enqueue_script( 'chartjs-adapter-date-fns', 'https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js', array( 'chartjs' ), '1.1.0', true );
		wp_enqueue_script( 'ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/js/user-dashboard.js', array( 'jquery', 'chartjs', 'chartjs-adapter-date-fns' ), ENNU_LIFE_VERSION, true );

		$user_id = get_current_user_id();

		// Fetch all required data.
		$current_user          = wp_get_current_user();
		$ennu_life_score       = get_user_meta( $user_id, 'ennu_life_score', true );
		$average_pillar_scores = get_user_meta( $user_id, 'ennu_average_pillar_scores', true );
		
		// DEBUG: Check what's in average_pillar_scores
		error_log( '[ENNU DEBUG] Dashboard - ennu_average_pillar_scores from DB: ' . print_r( $average_pillar_scores, true ) );
		
		if ( ! is_array( $average_pillar_scores ) || empty( $average_pillar_scores ) ) {
			// Calculate base pillar scores without penalties
			$base_pillar_scores = ENNU_Assessment_Scoring::calculate_average_pillar_scores( $user_id );
			
			// If we have base scores, use them
			if ( ! empty( $base_pillar_scores ) && array_sum( $base_pillar_scores ) > 0 ) {
				$average_pillar_scores = $base_pillar_scores;
			} else {
				// Initialize with default structure
				$average_pillar_scores = array(
					'Mind' => 0,
					'Body' => 0,
					'Lifestyle' => 0,
					'Aesthetics' => 0
				);
			}
			// Save for future use
			update_user_meta( $user_id, 'ennu_average_pillar_scores', $average_pillar_scores );
		}
		
		// Ensure proper capitalization of pillar names
		$formatted_pillar_scores = array();
		foreach ( $average_pillar_scores as $pillar => $score ) {
			$formatted_pillar_scores[ucfirst(strtolower($pillar))] = $score;
		}
		$average_pillar_scores = $formatted_pillar_scores;
		
		// DEBUG: Check what's in the formatted pillar scores
		error_log( '[ENNU DEBUG] Formatted pillar scores: ' . print_r( $average_pillar_scores, true ) );
		
		$dob                   = get_user_meta( $user_id, 'ennu_global_user_dob_combined', true );
		$age                   = $dob ? ( new DateTime() )->diff( new DateTime( $dob ) )->y : null;
		$gender                = get_user_meta( $user_id, 'ennu_global_gender', true );
		$height_weight_data    = get_user_meta( $user_id, 'ennu_global_height_weight', true );
		$height                = is_array( $height_weight_data ) && ! empty( $height_weight_data['ft'] ) ? "{$height_weight_data['ft']}' {$height_weight_data['in']}\"" : null;
		$weight                = is_array( $height_weight_data ) && ! empty( $height_weight_data['lbs'] ) ? $height_weight_data['lbs'] . ' lbs' : null;
		$bmi                   = get_user_meta( $user_id, 'ennu_calculated_bmi', true );
		$score_history         = get_user_meta( $user_id, 'ennu_life_score_history', true );
		$bmi_history           = get_user_meta( $user_id, 'ennu_bmi_history', true );

		$score_history = is_array( $score_history ) ? array_filter( $score_history, fn($e) => isset($e['date'], $e['score']) && !empty($e['date']) ) : [];
		usort( $score_history, fn($a, $b) => strtotime($a['date']) - strtotime($b['date']) );
		
		$insights              = include ENNU_LIFE_PLUGIN_PATH . 'includes/config/dashboard/insights.php';
		$user_assessments      = $this->get_user_assessments_data( $user_id );
		
		// --- NEW: Health Optimization Report Data Processing using the new Calculator ---
        $health_opt_calculator = new ENNU_Health_Optimization_Calculator( $user_id, $this->all_definitions );
        $triggered_vectors = $health_opt_calculator->get_triggered_vectors();
        $recommended_biomarkers = $health_opt_calculator->get_biomarker_recommendations();
        
        $health_map_config_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/biomarker-map.php';
        $biomarker_map = file_exists($health_map_config_file) ? require $health_map_config_file : array();

		// --- Definitive Data Pre-processing ---
		$processed_health_map = array();
        foreach ( $biomarker_map as $vector => $biomarkers ) {
            $processed_health_map[ $vector ] = array(
                'symptoms'   => array(), // This can be populated if needed in the future
                'biomarkers' => $biomarkers,
                'triggered_symptoms_count' => isset($triggered_vectors[$vector]) ? count($triggered_vectors[$vector]['instances']) : 0,
                'recommended_biomarkers_count' => isset($triggered_vectors[$vector]) ? count(array_intersect($biomarkers, $recommended_biomarkers)) : 0,
            );
        }

		$health_optimization_report = array(
			'health_map'             => $processed_health_map,
			'user_symptoms'          => array_keys($triggered_vectors),
			'triggered_vectors'      => array_keys($triggered_vectors),
			'recommended_biomarkers' => $recommended_biomarkers,
		);
		// --- End Data Pre-processing ---

		// --- MASTER DEBUG: Log the final data before sending to template ---
		error_log( '[ENNU DEBUG] Final Health Optimization Report Data: ' . print_r( $health_optimization_report, true ) );

		// Remove the forced demo block that was overriding real data
		
		wp_localize_script(
			'ennu-user-dashboard',
			'dashboardData',
			array(
				'score_history' => array_values( $score_history ),
				'bmi_history'   => is_array( $bmi_history ) ? array_values( $bmi_history ) : array(),
			)
		);

		$data = compact(
			'current_user', 'ennu_life_score', 'average_pillar_scores', 'age', 'gender', 'dob',
			'user_assessments', 'score_history', 'height', 'weight', 'bmi', 'insights', 'health_optimization_report'
		);

		// Add the shortcode instance to the data so templates can use get_page_id_url
		$data['shortcode_instance'] = $this;

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
			ennu_load_template( 'assessment-results-expired.php' );
			return ob_get_clean();
		}

		$this->_delete_manual_transient( 'ennu_results_' . $results_token );

		$report_data = ENNU_Assessment_Scoring::get_health_optimization_report_data( $results_transient );
		
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
		$auto_submit_ready = ! $needs_contact_form;
		
		$output = '';
		
		// Always add hidden fields for logged-in users to ensure email validation passes
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$output .= '<input type="hidden" name="first_name" value="' . esc_attr( $user->first_name ) . '">';
			$output .= '<input type="hidden" name="last_name" value="' . esc_attr( $user->last_name ) . '">';
			$output .= '<input type="hidden" name="email" value="' . esc_attr( $user->user_email ) . '">';
			$billing_phone = get_user_meta( $user->ID, 'billing_phone', true );
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
		$required_fields = array( 'first_name', 'last_name', 'email' );
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
			'last_name' => 'last_name', 
			'email' => 'email',
			'phone' => 'billing_phone'
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
			'is_logged_in' => is_user_logged_in(),
			'needs_contact_form' => true,
			'auto_submit_ready' => false,
			'user_data' => array()
		);

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			$user_id = $user->ID;
			
			// Get current user data
			$current_user_data = array(
				'first_name' => $user->first_name,
				'last_name' => $user->last_name,
				'email' => $user->user_email,
				'billing_phone' => get_user_meta( $user_id, 'billing_phone', true ),
			);
			
			// Check if user needs contact form
			$needs_contact_form = $this->user_needs_contact_form( $current_user_data );
			
			$response['needs_contact_form'] = $needs_contact_form;
			$response['auto_submit_ready'] = ! $needs_contact_form;
			$response['user_data'] = $current_user_data;
			$response['missing_fields'] = $needs_contact_form ? $this->get_missing_contact_fields( $current_user_data ) : array();
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
	private function get_page_id_url( $page_type, $query_args = array() ) {
		// Use auto-generated page mappings first
		$created_pages = get_option( 'ennu_created_pages', array() );
		if ( ! empty( $created_pages[ $page_type ] ) && get_post( $created_pages[ $page_type ] ) ) {
			$page_id = intval( $created_pages[ $page_type ] );
			$url = home_url( "/?page_id={$page_id}" );
			if ( ! empty( $query_args ) ) {
				$url = add_query_arg( $query_args, $url );
			}
			return $url;
		}

		// Fallback to pretty permalink
		$url = home_url( '/' . ltrim( $page_type, '/' ) . '/' );
		if ( ! empty( $query_args ) ) {
			$url = add_query_arg( $query_args, $url );
		}
		return $url;
	}
}
// Initialize the class
// new ENNU_Assessment_Shortcodes();

