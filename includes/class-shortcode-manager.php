<?php
/**
 * ENNU Shortcode Manager
 * Dedicated class for handling all shortcode registration and rendering
 * 
 * @package ENNU_Life_Assessments
 * @version 62.28.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages all assessment shortcodes with clean separation of concerns
 */
class ENNU_Shortcode_Manager {

	private $renderer;
	private $logger;

	public function __construct() {
		$this->renderer = new ENNU_Form_Renderer();
		$this->logger = new ENNU_Logger();
		
		$this->init_shortcodes();
	}

	/**
	 * Initialize all assessment shortcodes
	 */
	private function init_shortcodes() {
		// Core assessment shortcodes
		add_shortcode( 'ennu-health-assessment', array( $this, 'render_health_assessment' ) );
		add_shortcode( 'ennu-weight-loss-assessment', array( $this, 'render_weight_loss_assessment' ) );
		add_shortcode( 'ennu-hormone-assessment', array( $this, 'render_hormone_assessment' ) );
		add_shortcode( 'ennu-sleep-assessment', array( $this, 'render_sleep_assessment' ) );
		add_shortcode( 'ennu-skin-assessment', array( $this, 'render_skin_assessment' ) );
		add_shortcode( 'ennu-hair-assessment', array( $this, 'render_hair_assessment' ) );

		// Consultation shortcodes
		add_shortcode( 'ennu-health-optimization-consultation', array( $this, 'render_health_consultation' ) );
		add_shortcode( 'ennu-skin-consultation', array( $this, 'render_skin_consultation' ) );
		add_shortcode( 'ennu-health-consultation', array( $this, 'render_general_health_consultation' ) );
		add_shortcode( 'ennu-hormone-consultation', array( $this, 'render_hormone_consultation' ) );
		add_shortcode( 'ennu-menopause-consultation', array( $this, 'render_menopause_consultation' ) );
		add_shortcode( 'ennu-testosterone-consultation', array( $this, 'render_testosterone_consultation' ) );
		add_shortcode( 'ennu-sleep-consultation', array( $this, 'render_sleep_consultation' ) );

		// Results shortcodes
		add_shortcode( 'ennu-assessment-results', array( $this, 'render_assessment_results' ) );
		add_shortcode( 'ennu-user-profile', array( $this, 'render_user_profile' ) );

		$this->logger->log( 'All ENNU shortcodes registered successfully' );
	}

	/**
	 * Render health assessment form
	 */
	public function render_health_assessment( $atts ) {
		$atts = shortcode_atts( array(
			'title' => 'Health Optimization Assessment',
			'description' => 'Comprehensive health evaluation to optimize your wellness journey.',
			'show_progress' => 'true',
			'auto_submit' => 'false'
		), $atts, 'ennu-health-assessment' );

		return $this->renderer->render_assessment_form( 'health', $atts );
	}

	/**
	 * Render weight loss assessment form
	 */
	public function render_weight_loss_assessment( $atts ) {
		$atts = shortcode_atts( array(
			'title' => 'Weight Loss Assessment',
			'description' => 'Personalized weight loss evaluation and recommendations.',
			'show_progress' => 'true',
			'auto_submit' => 'false'
		), $atts, 'ennu-weight-loss-assessment' );

		return $this->renderer->render_assessment_form( 'weight_loss', $atts );
	}

	/**
	 * Render hormone assessment form
	 */
	public function render_hormone_assessment( $atts ) {
		$atts = shortcode_atts( array(
			'title' => 'Hormone Health Assessment',
			'description' => 'Comprehensive hormone evaluation for optimal balance.',
			'show_progress' => 'true',
			'auto_submit' => 'false'
		), $atts, 'ennu-hormone-assessment' );

		return $this->renderer->render_assessment_form( 'hormone', $atts );
	}

	/**
	 * Render sleep assessment form
	 */
	public function render_sleep_assessment( $atts ) {
		$atts = shortcode_atts( array(
			'title' => 'Sleep Quality Assessment',
			'description' => 'Evaluate your sleep patterns and optimize your rest.',
			'show_progress' => 'true',
			'auto_submit' => 'false'
		), $atts, 'ennu-sleep-assessment' );

		return $this->renderer->render_assessment_form( 'sleep', $atts );
	}

	/**
	 * Render skin assessment form
	 */
	public function render_skin_assessment( $atts ) {
		$atts = shortcode_atts( array(
			'title' => 'Skin Health Assessment',
			'description' => 'Comprehensive skin evaluation for optimal health and appearance.',
			'show_progress' => 'true',
			'auto_submit' => 'false'
		), $atts, 'ennu-skin-assessment' );

		return $this->renderer->render_assessment_form( 'skin', $atts );
	}

	/**
	 * Render hair assessment form
	 */
	public function render_hair_assessment( $atts ) {
		$atts = shortcode_atts( array(
			'title' => 'Hair Health Assessment',
			'description' => 'Evaluate your hair health and get personalized recommendations.',
			'show_progress' => 'true',
			'auto_submit' => 'false'
		), $atts, 'ennu-hair-assessment' );

		return $this->renderer->render_assessment_form( 'hair', $atts );
	}

	/**
	 * Render health optimization consultation
	 */
	public function render_health_consultation( $atts ) {
		$atts = shortcode_atts( array(
			'title' => 'Health Optimization Consultation',
			'description' => 'Schedule your personalized health optimization consultation.',
			'consultation_type' => 'health_optimization'
		), $atts, 'ennu-health-optimization-consultation' );

		return $this->renderer->render_consultation_form( 'health_optimization', $atts );
	}

	/**
	 * Render skin consultation
	 */
	public function render_skin_consultation( $atts ) {
		$atts = shortcode_atts( array(
			'title' => 'Skin Health Consultation',
			'description' => 'Schedule your personalized skin health consultation.',
			'consultation_type' => 'skin'
		), $atts, 'ennu-skin-consultation' );

		return $this->renderer->render_consultation_form( 'skin', $atts );
	}

	/**
	 * Render general health consultation
	 */
	public function render_general_health_consultation( $atts ) {
		$atts = shortcode_atts( array(
			'title' => 'General Health Consultation',
			'description' => 'Schedule your personalized health consultation.',
			'consultation_type' => 'general_health'
		), $atts, 'ennu-health-consultation' );

		return $this->renderer->render_consultation_form( 'general_health', $atts );
	}

	/**
	 * Render hormone consultation
	 */
	public function render_hormone_consultation( $atts ) {
		$atts = shortcode_atts( array(
			'title' => 'Hormone Health Consultation',
			'description' => 'Schedule your personalized hormone health consultation.',
			'consultation_type' => 'hormone'
		), $atts, 'ennu-hormone-consultation' );

		return $this->renderer->render_consultation_form( 'hormone', $atts );
	}

	/**
	 * Render menopause consultation
	 */
	public function render_menopause_consultation( $atts ) {
		$atts = shortcode_atts( array(
			'title' => 'Menopause Consultation',
			'description' => 'Schedule your personalized menopause consultation.',
			'consultation_type' => 'menopause'
		), $atts, 'ennu-menopause-consultation' );

		return $this->renderer->render_consultation_form( 'menopause', $atts );
	}

	/**
	 * Render testosterone consultation
	 */
	public function render_testosterone_consultation( $atts ) {
		$atts = shortcode_atts( array(
			'title' => 'Testosterone Consultation',
			'description' => 'Schedule your personalized testosterone consultation.',
			'consultation_type' => 'testosterone'
		), $atts, 'ennu-testosterone-consultation' );

		return $this->renderer->render_consultation_form( 'testosterone', $atts );
	}

	/**
	 * Render sleep consultation
	 */
	public function render_sleep_consultation( $atts ) {
		$atts = shortcode_atts( array(
			'title' => 'Sleep Consultation',
			'description' => 'Schedule your personalized sleep consultation.',
			'consultation_type' => 'sleep'
		), $atts, 'ennu-sleep-consultation' );

		return $this->renderer->render_consultation_form( 'sleep', $atts );
	}

	/**
	 * Render assessment results
	 */
	public function render_assessment_results( $atts ) {
		$atts = shortcode_atts( array(
			'token' => '',
			'show_details' => 'true',
			'show_recommendations' => 'true'
		), $atts, 'ennu-assessment-results' );

		if ( empty( $atts['token'] ) ) {
			return '<p>No results token provided.</p>';
		}

		return $this->renderer->render_assessment_results( $atts['token'], $atts );
	}

	/**
	 * Render user profile
	 */
	public function render_user_profile( $atts ) {
		$atts = shortcode_atts( array(
			'user_id' => get_current_user_id(),
			'show_assessments' => 'true',
			'show_scores' => 'true'
		), $atts, 'ennu-user-profile' );

		if ( ! $atts['user_id'] ) {
			return '<p>Please log in to view your profile.</p>';
		}

		return $this->renderer->render_user_profile( $atts['user_id'], $atts );
	}
}

/**
 * Form renderer for generating HTML output
 */
class ENNU_Form_Renderer {

	/**
	 * Render assessment form
	 */
	public function render_assessment_form( $assessment_type, $atts ) {
		ob_start();
		
		// Enqueue required assets
		$this->enqueue_form_assets( $assessment_type );
		
		// Generate nonce
		$nonce = wp_create_nonce( 'ennu_submit_assessment' );
		
		?>
		<div class="ennu-assessment-form" data-assessment-type="<?php echo esc_attr( $assessment_type ); ?>">
			<div class="ennu-form-header">
				<h2><?php echo esc_html( $atts['title'] ); ?></h2>
				<p><?php echo esc_html( $atts['description'] ); ?></p>
			</div>

			<?php if ( $atts['show_progress'] === 'true' ) : ?>
				<div class="ennu-progress-bar">
					<div class="ennu-progress-fill" style="width: 0%"></div>
				</div>
			<?php endif; ?>

			<form id="ennu-assessment-form-<?php echo esc_attr( $assessment_type ); ?>" 
				  class="ennu-assessment-form" 
				  data-auto-submit="<?php echo esc_attr( $atts['auto_submit'] ); ?>">
				
				<input type="hidden" name="assessment_type" value="<?php echo esc_attr( $assessment_type ); ?>">
				<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>">
				<input type="hidden" name="action" value="ennu_submit_assessment">

				<?php $this->render_form_fields( $assessment_type ); ?>

				<div class="ennu-form-actions">
					<button type="submit" class="ennu-submit-btn">
						Submit Assessment
					</button>
				</div>
			</form>

			<div class="ennu-form-messages"></div>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Render consultation form
	 */
	public function render_consultation_form( $consultation_type, $atts ) {
		ob_start();
		
		// Enqueue required assets
		$this->enqueue_form_assets( 'consultation' );
		
		// Generate nonce
		$nonce = wp_create_nonce( 'ennu_submit_consultation' );
		
		?>
		<div class="ennu-consultation-form" data-consultation-type="<?php echo esc_attr( $consultation_type ); ?>">
			<div class="ennu-form-header">
				<h2><?php echo esc_html( $atts['title'] ); ?></h2>
				<p><?php echo esc_html( $atts['description'] ); ?></p>
			</div>

			<form id="ennu-consultation-form-<?php echo esc_attr( $consultation_type ); ?>" 
				  class="ennu-consultation-form">
				
				<input type="hidden" name="consultation_type" value="<?php echo esc_attr( $consultation_type ); ?>">
				<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>">
				<input type="hidden" name="action" value="ennu_submit_consultation">

				<?php $this->render_consultation_fields( $consultation_type ); ?>

				<div class="ennu-form-actions">
					<button type="submit" class="ennu-submit-btn">
						Schedule Consultation
					</button>
				</div>
			</form>

			<div class="ennu-form-messages"></div>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Render assessment results
	 */
	public function render_assessment_results( $token, $atts ) {
		// Get results from transient
		$results_data = get_transient( 'ennu_results_' . $token );
		
		if ( ! $results_data ) {
			return '<p>Results not found or expired.</p>';
		}

		ob_start();
		?>
		<div class="ennu-assessment-results">
			<div class="ennu-results-header">
				<h2>Assessment Results</h2>
				<p>Your personalized assessment results and recommendations.</p>
			</div>

			<?php if ( isset( $results_data['scores'] ) ) : ?>
				<div class="ennu-score-summary">
					<div class="ennu-overall-score">
						<h3>Overall Score: <?php echo esc_html( $results_data['scores']['overall_score'] ); ?>/10</h3>
						<div class="ennu-score-level" style="color: <?php echo esc_attr( $results_data['scores']['color'] ); ?>">
							<?php echo esc_html( $results_data['scores']['level'] ); ?>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( $atts['show_details'] === 'true' ) : ?>
				<div class="ennu-results-details">
					<!-- Detailed results would go here -->
				</div>
			<?php endif; ?>

			<?php if ( $atts['show_recommendations'] === 'true' ) : ?>
				<div class="ennu-recommendations">
					<!-- Recommendations would go here -->
				</div>
			<?php endif; ?>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Render user profile
	 */
	public function render_user_profile( $user_id, $atts ) {
		$user = get_user_by( 'ID', $user_id );
		
		if ( ! $user ) {
			return '<p>User not found.</p>';
		}

		ob_start();
		?>
		<div class="ennu-user-profile">
			<div class="ennu-profile-header">
				<h2>User Profile</h2>
				<p>Welcome, <?php echo esc_html( $user->display_name ); ?>!</p>
			</div>

			<?php if ( $atts['show_assessments'] === 'true' ) : ?>
				<div class="ennu-user-assessments">
					<h3>Your Assessments</h3>
					<?php $this->render_user_assessments( $user_id ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $atts['show_scores'] === 'true' ) : ?>
				<div class="ennu-user-scores">
					<h3>Your Scores</h3>
					<?php $this->render_user_scores( $user_id ); ?>
				</div>
			<?php endif; ?>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Render form fields based on assessment type
	 */
	private function render_form_fields( $assessment_type ) {
		// This would load fields from assessment configuration
		// For now, render basic fields
		?>
		<div class="ennu-form-section">
			<h3>Personal Information</h3>
			
			<div class="ennu-field-group">
				<label for="first_name">First Name *</label>
				<input type="text" id="first_name" name="first_name" required>
			</div>

			<div class="ennu-field-group">
				<label for="last_name">Last Name *</label>
				<input type="text" id="last_name" name="last_name" required>
			</div>

			<div class="ennu-field-group">
				<label for="email">Email Address *</label>
				<input type="email" id="email" name="email" required>
			</div>

			<div class="ennu-field-group">
				<label for="billing_phone">Phone Number</label>
				<input type="tel" id="billing_phone" name="billing_phone">
			</div>
		</div>

		<div class="ennu-form-section">
			<h3>Assessment Questions</h3>
			<!-- Assessment-specific questions would be rendered here -->
			<p>Assessment questions for <?php echo esc_html( $assessment_type ); ?> would be loaded here.</p>
		</div>
		<?php
	}

	/**
	 * Render consultation fields
	 */
	private function render_consultation_fields( $consultation_type ) {
		?>
		<div class="ennu-form-section">
			<h3>Consultation Details</h3>
			
			<div class="ennu-field-group">
				<label for="consultation_name">Full Name *</label>
				<input type="text" id="consultation_name" name="consultation_name" required>
			</div>

			<div class="ennu-field-group">
				<label for="consultation_email">Email Address *</label>
				<input type="email" id="consultation_email" name="consultation_email" required>
			</div>

			<div class="ennu-field-group">
				<label for="consultation_phone">Phone Number</label>
				<input type="tel" id="consultation_phone" name="consultation_phone">
			</div>

			<div class="ennu-field-group">
				<label for="consultation_message">Message</label>
				<textarea id="consultation_message" name="consultation_message" rows="4"></textarea>
			</div>
		</div>
		<?php
	}

	/**
	 * Render user assessments
	 */
	private function render_user_assessments( $user_id ) {
		$assessments = array( 'health', 'weight_loss', 'hormone', 'sleep', 'skin', 'hair' );
		
		foreach ( $assessments as $assessment_type ) {
			$completed = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_completed', true );
			
			if ( $completed ) {
				echo '<div class="ennu-assessment-item">';
				echo '<strong>' . esc_html( ucwords( str_replace( '_', ' ', $assessment_type ) ) ) . '</strong>';
				echo '<span>Completed: ' . esc_html( date( 'M j, Y', strtotime( $completed ) ) ) . '</span>';
				echo '</div>';
			}
		}
	}

	/**
	 * Render user scores
	 */
	private function render_user_scores( $user_id ) {
		$assessments = array( 'health', 'weight_loss', 'hormone', 'sleep', 'skin', 'hair' );
		
		foreach ( $assessments as $assessment_type ) {
			$score = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_score', true );
			
			if ( $score ) {
				echo '<div class="ennu-score-item">';
				echo '<strong>' . esc_html( ucwords( str_replace( '_', ' ', $assessment_type ) ) ) . '</strong>';
				echo '<span>Score: ' . esc_html( $score ) . '/10</span>';
				echo '</div>';
			}
		}
	}

	/**
	 * Enqueue form assets
	 */
	private function enqueue_form_assets( $assessment_type ) {
		wp_enqueue_script( 'ennu-form-handler', 
			plugins_url( 'assets/js/form-handler.js', ENNU_PLUGIN_FILE ), 
			array( 'jquery' ), 
			ENNU_VERSION, 
			true 
		);

		wp_enqueue_style( 'ennu-form-styles', 
			plugins_url( 'assets/css/form-styles.css', ENNU_PLUGIN_FILE ), 
			array(), 
			ENNU_VERSION 
		);

		// Localize script with AJAX URL and nonce
		wp_localize_script( 'ennu-form-handler', 'ennu_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'ennu_ajax_nonce' )
		) );
	}
} 