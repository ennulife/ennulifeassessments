<?php
/**
 * Peptide Therapy Assessment Shortcode
 *
 * @package ENNU_Life_Assessments
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Peptide_Therapy_Shortcode {

	/**
	 * Initialize the shortcode
	 */
	public static function init() {
		add_shortcode( 'ennu_peptide_therapy_assessment', array( __CLASS__, 'render_assessment' ) );
		add_action( 'wp_ajax_ennu_submit_peptide_therapy', array( __CLASS__, 'handle_submission' ) );
		add_action( 'wp_ajax_nopriv_ennu_submit_peptide_therapy', array( __CLASS__, 'handle_submission' ) );
	}

	/**
	 * Render the assessment form
	 */
	public static function render_assessment( $atts ) {
		$atts = shortcode_atts( array(
			'redirect' => '/dashboard',
			'show_progress' => 'yes',
		), $atts );

		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			return '<div class="ennu-assessment-notice">Please <a href="/login">log in</a> to take the Peptide Therapy Assessment.</div>';
		}

		$user_id = get_current_user_id();
		
		// Check if assessment already completed
		$existing_data = get_user_meta( $user_id, 'ennu_assessment_responses_peptide-therapy', true );
		if ( $existing_data && ! isset( $_GET['retake'] ) ) {
			return self::render_completed_message();
		}

		// Get questions from unified assessment system
		if ( class_exists( 'ENNU_Unified_Assessment_System' ) ) {
			$assessment_system = ENNU_Unified_Assessment_System::get_instance();
			$assessment_data = $assessment_system->get_assessment_configuration( 'peptide-therapy' );
			$questions = $assessment_data['questions'] ?? array();
		} else {
			return '<div class="ennu-assessment-error">Assessment system not available. Please contact support.</div>';
		}

		ob_start();
		?>
		<div class="ennu-peptide-therapy-assessment" id="peptide-therapy-assessment">
			<div class="assessment-header">
				<h2>Peptide Therapy Optimization Assessment</h2>
				<p class="assessment-description">Discover your personalized peptide therapy recommendations based on your health goals and current status.</p>
			</div>

			<?php if ( $atts['show_progress'] === 'yes' ) : ?>
			<div class="assessment-progress">
				<div class="progress-bar">
					<div class="progress-fill" id="progress-fill" style="width: 0%;"></div>
				</div>
				<div class="progress-text">
					<span id="progress-current">0</span> of <span id="progress-total"><?php echo count( $questions ); ?></span> questions
				</div>
			</div>
			<?php endif; ?>

			<form id="peptide-therapy-form" class="ennu-assessment-form" method="post">
				<?php wp_nonce_field( 'ennu_peptide_therapy_nonce', 'peptide_nonce' ); ?>
				<input type="hidden" name="action" value="ennu_submit_peptide_therapy">
				<input type="hidden" name="redirect_url" value="<?php echo esc_attr( $atts['redirect'] ); ?>">

				<div class="assessment-sections">
					<?php 
					$question_index = 0;
					$current_section = '';
					
					foreach ( $questions as $question_id => $question ) :
						// Extract section from question ID (e.g., 'pep_q1' -> section 1)
						$section_number = ceil( ( $question_index + 1 ) / 3 );
						$section_name = self::get_section_name( $section_number );
						
						if ( $section_name !== $current_section ) :
							if ( $current_section !== '' ) echo '</div>'; // Close previous section
							$current_section = $section_name;
							?>
							<div class="assessment-section" data-section="<?php echo $section_number; ?>">
								<h3 class="section-title"><?php echo esc_html( $section_name ); ?></h3>
						<?php endif; ?>

						<div class="question-wrapper" data-question="<?php echo $question_index + 1; ?>">
							<label class="question-label">
								<span class="question-number">Q<?php echo $question_index + 1; ?>.</span>
								<?php echo esc_html( $question['title'] ); ?>
								<?php if ( isset( $question['required'] ) && $question['required'] ) : ?>
									<span class="required">*</span>
								<?php endif; ?>
							</label>

							<?php if ( $question['type'] === 'radio' ) : ?>
								<div class="radio-options">
									<?php foreach ( $question['options'] as $value => $label ) : ?>
									<label class="radio-option">
										<input type="radio" 
											   name="<?php echo esc_attr( $question_id ); ?>" 
											   value="<?php echo esc_attr( $value ); ?>"
											   data-question-index="<?php echo $question_index; ?>"
											   <?php echo ( isset( $question['required'] ) && $question['required'] ) ? 'required' : ''; ?>>
										<span class="radio-label"><?php echo esc_html( $label ); ?></span>
									</label>
									<?php endforeach; ?>
								</div>
							<?php elseif ( $question['type'] === 'checkbox' ) : ?>
								<div class="checkbox-options">
									<?php foreach ( $question['options'] as $value => $label ) : ?>
									<label class="checkbox-option">
										<input type="checkbox" 
											   name="<?php echo esc_attr( $question_id ); ?>[]" 
											   value="<?php echo esc_attr( $value ); ?>"
											   data-question-index="<?php echo $question_index; ?>">
										<span class="checkbox-label"><?php echo esc_html( $label ); ?></span>
									</label>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>

						<?php 
						$question_index++;
					endforeach; 
					
					if ( $current_section !== '' ) echo '</div>'; // Close last section
					?>
				</div>

				<div class="assessment-actions">
					<button type="button" id="prev-section" class="btn btn-secondary" style="display: none;">Previous</button>
					<button type="button" id="next-section" class="btn btn-primary">Next</button>
					<button type="submit" id="submit-assessment" class="btn btn-success" style="display: none;">Complete Assessment</button>
				</div>
			</form>

			<div class="assessment-footer">
				<p class="privacy-notice">Your responses are confidential and protected by our <a href="/privacy-policy" target="_blank">Privacy Policy</a>.</p>
			</div>
		</div>

		<style>
		.ennu-peptide-therapy-assessment {
			max-width: 800px;
			margin: 0 auto;
			padding: 30px;
			background: #fff;
			border-radius: 10px;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
		}

		.assessment-header {
			text-align: center;
			margin-bottom: 30px;
		}

		.assessment-header h2 {
			color: #2c3e50;
			margin-bottom: 10px;
		}

		.assessment-description {
			color: #7f8c8d;
			font-size: 16px;
		}

		.assessment-progress {
			margin-bottom: 30px;
		}

		.progress-bar {
			height: 20px;
			background: #ecf0f1;
			border-radius: 10px;
			overflow: hidden;
		}

		.progress-fill {
			height: 100%;
			background: linear-gradient(90deg, #3498db, #2ecc71);
			transition: width 0.3s ease;
		}

		.progress-text {
			text-align: center;
			margin-top: 10px;
			color: #7f8c8d;
		}

		.assessment-section {
			display: none;
			animation: fadeIn 0.3s ease;
		}

		.assessment-section.active {
			display: block;
		}

		@keyframes fadeIn {
			from { opacity: 0; transform: translateY(10px); }
			to { opacity: 1; transform: translateY(0); }
		}

		.section-title {
			color: #34495e;
			margin-bottom: 20px;
			padding-bottom: 10px;
			border-bottom: 2px solid #ecf0f1;
		}

		.question-wrapper {
			margin-bottom: 25px;
		}

		.question-label {
			display: block;
			margin-bottom: 15px;
			color: #2c3e50;
			font-weight: 500;
		}

		.question-number {
			color: #3498db;
			font-weight: bold;
			margin-right: 5px;
		}

		.required {
			color: #e74c3c;
			margin-left: 3px;
		}

		.radio-options,
		.checkbox-options {
			display: flex;
			flex-direction: column;
			gap: 10px;
		}

		.radio-option,
		.checkbox-option {
			display: flex;
			align-items: center;
			padding: 12px;
			background: #f8f9fa;
			border: 2px solid transparent;
			border-radius: 8px;
			cursor: pointer;
			transition: all 0.2s ease;
		}

		.radio-option:hover,
		.checkbox-option:hover {
			background: #e8f4fd;
			border-color: #3498db;
		}

		.radio-option input[type="radio"],
		.checkbox-option input[type="checkbox"] {
			margin-right: 10px;
		}

		.radio-option input[type="radio"]:checked + .radio-label,
		.checkbox-option input[type="checkbox"]:checked + .checkbox-label {
			font-weight: 600;
			color: #2980b9;
		}

		.assessment-actions {
			display: flex;
			justify-content: space-between;
			margin-top: 30px;
			padding-top: 20px;
			border-top: 2px solid #ecf0f1;
		}

		.btn {
			padding: 12px 30px;
			border: none;
			border-radius: 5px;
			font-size: 16px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s ease;
		}

		.btn-primary {
			background: #3498db;
			color: white;
		}

		.btn-primary:hover {
			background: #2980b9;
		}

		.btn-secondary {
			background: #95a5a6;
			color: white;
		}

		.btn-secondary:hover {
			background: #7f8c8d;
		}

		.btn-success {
			background: #2ecc71;
			color: white;
		}

		.btn-success:hover {
			background: #27ae60;
		}

		.assessment-footer {
			margin-top: 30px;
			text-align: center;
		}

		.privacy-notice {
			color: #7f8c8d;
			font-size: 14px;
		}

		.privacy-notice a {
			color: #3498db;
			text-decoration: none;
		}

		.privacy-notice a:hover {
			text-decoration: underline;
		}

		.ennu-assessment-notice,
		.ennu-assessment-error {
			padding: 20px;
			background: #f8f9fa;
			border-left: 4px solid #3498db;
			border-radius: 5px;
			margin: 20px 0;
		}

		.ennu-assessment-error {
			border-left-color: #e74c3c;
		}

		@media (max-width: 768px) {
			.ennu-peptide-therapy-assessment {
				padding: 20px;
			}

			.radio-option,
			.checkbox-option {
				padding: 10px;
			}

			.btn {
				padding: 10px 20px;
				font-size: 14px;
			}
		}
		</style>

		<script>
		jQuery(document).ready(function($) {
			var currentSection = 1;
			var totalSections = $('.assessment-section').length;
			var totalQuestions = <?php echo count( $questions ); ?>;
			
			// Show first section
			$('.assessment-section[data-section="1"]').addClass('active');
			
			// Update progress
			function updateProgress() {
				var answered = $('input[type="radio"]:checked, input[type="checkbox"]:checked').length;
				var percentage = (answered / totalQuestions) * 100;
				$('#progress-fill').css('width', percentage + '%');
				$('#progress-current').text(answered);
			}
			
			// Navigation
			$('#next-section').click(function() {
				if (validateCurrentSection()) {
					if (currentSection < totalSections) {
						$('.assessment-section[data-section="' + currentSection + '"]').removeClass('active');
						currentSection++;
						$('.assessment-section[data-section="' + currentSection + '"]').addClass('active');
						
						// Update buttons
						$('#prev-section').show();
						if (currentSection === totalSections) {
							$('#next-section').hide();
							$('#submit-assessment').show();
						}
						
						// Scroll to top
						$('html, body').animate({ scrollTop: $('.ennu-peptide-therapy-assessment').offset().top - 100 }, 300);
					}
				}
			});
			
			$('#prev-section').click(function() {
				if (currentSection > 1) {
					$('.assessment-section[data-section="' + currentSection + '"]').removeClass('active');
					currentSection--;
					$('.assessment-section[data-section="' + currentSection + '"]').addClass('active');
					
					// Update buttons
					if (currentSection === 1) {
						$('#prev-section').hide();
					}
					$('#submit-assessment').hide();
					$('#next-section').show();
					
					// Scroll to top
					$('html, body').animate({ scrollTop: $('.ennu-peptide-therapy-assessment').offset().top - 100 }, 300);
				}
			});
			
			// Validate current section
			function validateCurrentSection() {
				var isValid = true;
				var currentSectionEl = $('.assessment-section[data-section="' + currentSection + '"]');
				
				currentSectionEl.find('input[required]').each(function() {
					var name = $(this).attr('name');
					if ($('input[name="' + name + '"]:checked').length === 0) {
						isValid = false;
						$(this).closest('.question-wrapper').addClass('error');
					} else {
						$(this).closest('.question-wrapper').removeClass('error');
					}
				});
				
				if (!isValid) {
					alert('Please answer all required questions before proceeding.');
				}
				
				return isValid;
			}
			
			// Update progress on input change
			$('input[type="radio"], input[type="checkbox"]').change(function() {
				updateProgress();
			});
			
			// Form submission
			$('#peptide-therapy-form').submit(function(e) {
				e.preventDefault();
				
				if (!validateCurrentSection()) {
					return false;
				}
				
				var formData = $(this).serialize();
				
				// Show loading state
				$('#submit-assessment').prop('disabled', true).text('Processing...');
				
				$.ajax({
					url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
					type: 'POST',
					data: formData,
					success: function(response) {
						if (response.success) {
							// Redirect to results page
							window.location.href = response.data.redirect_url || '/dashboard';
						} else {
							alert(response.data.message || 'An error occurred. Please try again.');
							$('#submit-assessment').prop('disabled', false).text('Complete Assessment');
						}
					},
					error: function() {
						alert('An error occurred. Please try again.');
						$('#submit-assessment').prop('disabled', false).text('Complete Assessment');
					}
				});
			});
		});
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get section name based on section number
	 */
	private static function get_section_name( $section_number ) {
		$sections = array(
			1 => 'Primary Health Goals',
			2 => 'Weight & Metabolism',
			3 => 'Recovery & Performance',
			4 => 'Hormonal Health',
			5 => 'Cognitive Function',
			6 => 'Anti-Aging & Longevity',
			7 => 'Sexual Health',
			8 => 'Immune & Gut Health',
			9 => 'Current Health Status'
		);
		
		return $sections[ $section_number ] ?? 'Section ' . $section_number;
	}

	/**
	 * Render completed message
	 */
	private static function render_completed_message() {
		ob_start();
		?>
		<div class="ennu-assessment-completed">
			<h3>You've Already Completed the Peptide Therapy Assessment</h3>
			<p>View your results and recommendations in your dashboard.</p>
			<div class="completed-actions">
				<a href="/dashboard" class="btn btn-primary">View Results</a>
				<a href="?retake=true" class="btn btn-secondary">Retake Assessment</a>
			</div>
		</div>
		<style>
		.ennu-assessment-completed {
			max-width: 600px;
			margin: 0 auto;
			padding: 40px;
			background: #fff;
			border-radius: 10px;
			text-align: center;
			box-shadow: 0 2px 10px rgba(0,0,0,0.1);
		}
		.ennu-assessment-completed h3 {
			color: #2c3e50;
			margin-bottom: 15px;
		}
		.ennu-assessment-completed p {
			color: #7f8c8d;
			margin-bottom: 25px;
		}
		.completed-actions {
			display: flex;
			gap: 15px;
			justify-content: center;
		}
		.completed-actions .btn {
			padding: 12px 30px;
			border-radius: 5px;
			text-decoration: none;
			font-weight: 600;
			transition: all 0.3s ease;
		}
		.completed-actions .btn-primary {
			background: #3498db;
			color: white;
		}
		.completed-actions .btn-primary:hover {
			background: #2980b9;
		}
		.completed-actions .btn-secondary {
			background: #95a5a6;
			color: white;
		}
		.completed-actions .btn-secondary:hover {
			background: #7f8c8d;
		}
		</style>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle form submission
	 */
	public static function handle_submission() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['peptide_nonce'], 'ennu_peptide_therapy_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Security verification failed.' ) );
		}

		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => 'You must be logged in to submit the assessment.' ) );
		}

		$user_id = get_current_user_id();
		
		// Process responses
		$responses = array();
		$questions_prefix = 'pep_q';
		
		for ( $i = 1; $i <= 22; $i++ ) {
			$question_key = $questions_prefix . $i;
			if ( isset( $_POST[ $question_key ] ) ) {
				$responses[ $question_key ] = is_array( $_POST[ $question_key ] ) 
					? array_map( 'sanitize_text_field', $_POST[ $question_key ] )
					: sanitize_text_field( $_POST[ $question_key ] );
			}
		}

		// Save responses
		$assessment_data = array(
			'user_id' => $user_id,
			'assessment_type' => 'peptide_therapy',
			'timestamp' => current_time( 'mysql' ),
			'responses' => $responses,
			'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
			'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
		);

		update_user_meta( $user_id, 'ennu_assessment_responses_peptide-therapy', $assessment_data );

		// Calculate scores
		if ( class_exists( 'ENNU_Assessment_Calculator' ) ) {
			$calculator = new ENNU_Assessment_Calculator();
			$scores = $calculator->calculate_assessment_scores( $user_id, 'peptide_therapy', $responses );
			
			// Save scores
			update_user_meta( $user_id, 'ennu_peptide-therapy_scores', $scores );
			
			// Generate recommendations
			$recommendations = self::generate_peptide_recommendations( $scores );
			update_user_meta( $user_id, 'ennu_peptide-therapy_recommendations', $recommendations );
		}

		// Trigger completion action
		do_action( 'ennu_assessment_completed', $user_id, 'peptide_therapy' );

		// Send HubSpot update
		if ( class_exists( 'ENNU_HubSpot_API_Manager' ) ) {
			$hubspot = new ENNU_HubSpot_API_Manager();
			$hubspot->update_contact_assessment_data( $user_id, 'peptide_therapy', $responses, $scores );
		}

		// Return success
		$redirect_url = sanitize_text_field( $_POST['redirect_url'] ?? '/dashboard' );
		wp_send_json_success( array(
			'message' => 'Assessment completed successfully!',
			'redirect_url' => $redirect_url . '#peptide-therapy-results'
		) );
	}

	/**
	 * Generate peptide recommendations based on scores
	 */
	private static function generate_peptide_recommendations( $scores ) {
		$recommendations = array(
			'primary' => array(),
			'secondary' => array(),
			'optimization' => array()
		);

		$category_scores = $scores['categories'] ?? array();
		
		// Map high-scoring categories to peptides
		$peptide_mapping = array(
			'weight_management' => array( 'AOD-9604', 'Tesamorelin', 'Tirzepatide' ),
			'recovery_performance' => array( 'BPC-157', 'Ipamorelin', 'CJC-1295' ),
			'hormonal_optimization' => array( 'CJC-1295', 'Gonadorelin', 'HCG' ),
			'cognitive_enhancement' => array( 'NAD+', 'Tesamorelin' ),
			'anti_aging' => array( 'NAD+', 'CJC-1295', 'Ipamorelin' ),
			'sexual_health' => array( 'PT-141', 'Gonadorelin' ),
			'immune_gut' => array( 'BPC-157', 'NAD+' )
		);

		// Sort categories by score
		arsort( $category_scores );
		
		$count = 0;
		foreach ( $category_scores as $category => $score ) {
			if ( isset( $peptide_mapping[ $category ] ) ) {
				if ( $count < 2 && $score >= 7.0 ) {
					// Primary recommendations for top 2 categories with high scores
					foreach ( $peptide_mapping[ $category ] as $peptide ) {
						if ( ! in_array( $peptide, $recommendations['primary'] ) ) {
							$recommendations['primary'][] = $peptide;
						}
					}
				} elseif ( $count < 4 && $score >= 5.0 ) {
					// Secondary recommendations for next 2 categories
					foreach ( $peptide_mapping[ $category ] as $peptide ) {
						if ( ! in_array( $peptide, $recommendations['primary'] ) && 
							 ! in_array( $peptide, $recommendations['secondary'] ) ) {
							$recommendations['secondary'][] = $peptide;
						}
					}
				} elseif ( $score >= 4.0 ) {
					// Optimization recommendations for remaining categories
					foreach ( $peptide_mapping[ $category ] as $peptide ) {
						if ( ! in_array( $peptide, $recommendations['primary'] ) && 
							 ! in_array( $peptide, $recommendations['secondary'] ) &&
							 ! in_array( $peptide, $recommendations['optimization'] ) ) {
							$recommendations['optimization'][] = $peptide;
						}
					}
				}
				$count++;
			}
		}

		// Limit recommendations
		$recommendations['primary'] = array_slice( $recommendations['primary'], 0, 3 );
		$recommendations['secondary'] = array_slice( $recommendations['secondary'], 0, 2 );
		$recommendations['optimization'] = array_slice( $recommendations['optimization'], 0, 2 );

		return $recommendations;
	}
}

// Initialize the shortcode
ENNU_Peptide_Therapy_Shortcode::init();