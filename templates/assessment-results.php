<?php
/**
 * Template for displaying assessment results - REBORN as a Bio-Metric Canvas Overture
 * This template is now a "dumb" component. All data fetching and processing
 * is handled in the `render_thank_you_page` method.
 *
 * @version 62.1.57
 * @see ENNU_Assessment_Shortcodes::render_thank_you_page()
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// All data is passed in via the $data variable, which is extracted by ennu_load_template().
// This removes the need for local variables with null coalescing.

// Defensive checks for required variables
$score              = $score ?? 0;
$result_content     = $result_content ?? array();
$assessment_title   = $assessment_title ?? 'Assessment Results';
$category_scores    = $category_scores ?? array();
$matched_recs       = $matched_recs ?? array();
$shortcode_instance = $shortcode_instance ?? null;
$assessment_type    = $assessment_type ?? '';

if ( empty( $shortcode_instance ) || ! is_object( $shortcode_instance ) ) {
	echo '<div class="ennu-error">ERROR: Shortcode instance is missing. Please contact support.</div>';
	return;
}

// Defensive check for assessment_type
if ( empty( $assessment_type ) ) {
	echo '<div class="ennu-error">ERROR: Assessment type is missing. Please contact support.</div>';
	return;
}

?>

<div class="ennu-unified-container assessment-results-page" data-theme="light">

	<!-- Simplified Header for Results Page -->
	<?php
	// Prepare minimal header data for results page
	$header_data = array(
		'display_name' => $display_name ?? '',
		'show_vital_stats' => false, // No vital stats on results page
		'show_theme_toggle' => false, // No theme toggle on results page
		'page_title' => $assessment_title,
		'page_subtitle' => isset( $result_content['summary'] ) ? $result_content['summary'] : 'Your personalized assessment results are ready.',
		'show_logo' => false, // Logo already in hero section
		'minimal' => true // Use minimal header style
	);
	
	// Load the universal header component if needed
	// ennu_load_template( 'universal-header', $header_data );
	?>

	<div class="ennu-single-column">
		
		<!-- Dynamic Results Header Section -->
		<div class="ennu-results-header" style="text-align: center; padding: 2rem 1rem; margin-bottom: 2rem;">
			<?php
			// Load assessment headlines configuration for dynamic text
			$headlines_config_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessment-headlines.php';
			$headlines_config = file_exists( $headlines_config_file ) ? require $headlines_config_file : array();
			
			// Get the canonical assessment type for proper mapping
			$canonical_assessment_type = ENNU_Assessment_Constants::get_canonical_key( $assessment_type );
			
			// Get the headline data for this assessment type
			$headline_data = $headlines_config[ $canonical_assessment_type ] ?? $headlines_config['default'];
			
			// Get user's first name for personalization
			$user_id = get_current_user_id();
			$first_name = '';
			if ( $user_id ) {
				$user_data = get_userdata( $user_id );
				$first_name = $user_data->first_name ?: $user_data->display_name ?: '';
			}
			
			// Extract headline components and personalize if we have a name
			$results_headline = $headline_data['headline'] ?? 'Your Results Are Ready!';
			if ( ! empty( $first_name ) ) {
				$results_headline = $first_name . ', ' . $results_headline;
			}
			$results_subheadline = $headline_data['subheadline'] ?? 'Let\'s book a consultation to discuss your personalized health plan and next steps.';
			?>
			
			<h1 class="ennu-results-headline" style="font-size: 2.5rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem;">
				<?php echo esc_html( $results_headline ); ?>
			</h1>
			<p class="ennu-results-subheadline" style="font-size: 1.25rem; color: #6b7280; max-width: 700px; margin: 0 auto; margin-bottom: 2rem;">
				<?php echo esc_html( $results_subheadline ); ?>
			</p>
			
			<!-- HubSpot Booking Calendar Embed -->
			<div class="ennu-hubspot-embed" style="margin: 2rem auto; max-width: 1000px;">
				<!-- Start of Meetings Embed Script -->
				<div class="meetings-iframe-container" data-src="https://meetings.hubspot.com/lescobar2/ennulife?embed=true"></div>
				<script type="text/javascript" src="https://static.hsappstatic.net/MeetingsEmbed/ex/MeetingsEmbedCode.js"></script>
				<!-- End of Meetings Embed Script -->
			</div>
		</div>

			<!-- Assessment Results Score Display - Modern Card Design -->
			<div class="assessment-scores-section" style="margin-top: 0; padding-top: 2rem;">
				<!-- Include Modern Scoring UI CSS -->
				<link rel="stylesheet" href="<?php echo esc_url( plugins_url( 'assets/css/modern-scoring-ui.css', dirname( __FILE__ ) ) ); ?>" />
				
				<!-- Assessment Results Title -->
				<h2 class="ennu-section-title" style="text-align: center; margin-bottom: 2rem; color: #1f2937; font-size: 2rem; font-weight: 600;">Your Assessment Results</h2>
				
				<!-- Modern Scoring Layout -->
				<div class="modern-scores-layout">
					<?php
					// Prepare assessment-specific scores for display
					$assessment_pillar_scores = $pillar_scores ?? array();
					$assessment_overall_score = $overall_score ?? 0;
					$assessment_overall_percent = $assessment_overall_score ? round($assessment_overall_score * 10) : 0;
					
					// Define all expected pillars
					$all_pillars = array( 'Mind', 'Body', 'Lifestyle', 'Aesthetics' );
					?>

					<!-- Hero Assessment Score with Assessment Info -->
					<div class="hero-row">
						
							<!-- Column 1: Assessment Information -->
							<div class="pillar-card member-info-card">
								<div class="pillar-card-header">
									<img src="<?php echo esc_url( plugins_url( 'assets/img/ennu-logo-black.png', dirname( __FILE__ ) ) ); ?>" 
									     alt="ENNU Life" 
									     class="member-card-logo"
									     style="max-height: 35px; width: auto; display: block; margin: 0 auto;">
								</div>
								
								<div class="member-info-content">
									<div class="member-greeting" style="text-align: center; margin-top: 10px;">
										<h3 style="margin: 0; font-size: 16px; font-weight: 600;"><?php echo esc_html( ucwords( str_replace( '_', ' ', $assessment_type ) ) ); ?> Results</h3>
									</div>
									<div class="member-details">
										<div class="member-detail-item">
											<span class="detail-label">Status:</span>
											<span class="detail-value">Complete</span>
										</div>
										<?php if ( ! empty( $display_name ) ) : ?>
										<div class="member-detail-item">
											<span class="detail-label">For:</span>
											<span class="detail-value"><?php echo esc_html( $display_name ); ?></span>
										</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
						
							<!-- Column 2: Assessment Score (styled like EnnuLife score) -->
							<div class="pillar-card assessment" 
							     style="--progress: <?php echo esc_attr($assessment_overall_percent); ?>"
							     data-pillar="<?php echo esc_attr( $assessment_type ); ?>"
							     role="button"
							     tabindex="0"
							     aria-label="<?php echo esc_attr( ucwords( str_replace( '_', ' ', $assessment_type ) ) . ' score ' . number_format($assessment_overall_score, 1) . ' out of 10'); ?>">
								
								<div class="pillar-card-header">
									<div class="pillar-card-title"><?php echo esc_html( ucwords( str_replace( '_', ' ', $assessment_type ) ) ); ?> Score</div>
								</div>
								
								<div class="pillar-card-score">
									<div class="pillar-score-value" aria-live="polite">
										<?php echo esc_html(number_format($assessment_overall_score, 1)); ?>
									</div>
									<div class="pillar-score-max">/10</div>
								</div>
								
								<div class="pillar-progress-bar"
								     role="progressbar" 
								     aria-valuemin="0" 
								     aria-valuemax="10" 
								     aria-valuenow="<?php echo esc_attr($assessment_overall_score); ?>">
									<div class="pillar-progress-fill"></div>
								</div>
								
								<div class="pillar-card-footer">
									<div class="pillar-target-label">
										Score: <span class="target-value-small"><?php echo esc_html(number_format($assessment_overall_score, 1)); ?>/10</span>
									</div>
									<div class="pillar-progress-percent"><?php echo esc_html(round($assessment_overall_percent)); ?>%</div>
								</div>
							</div>
						
							<!-- Column 3: Next Steps -->
							<div class="pillar-card coach-info-card">
								<div class="pillar-card-header">
									<div class="pillar-card-title">Next Steps</div>
								</div>
								
								<div class="coach-info-content">
									<div class="health-coach-buttons" style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
										<a href="<?php echo esc_url( $shortcode_instance->get_assessment_cta_url( $assessment_type ) ); ?>" class="health-coach-btn primary" style="background-color: #374151; color: white;">
											Book Consultation
										</a>
										<a href="<?php echo esc_url( '?' . ENNU_UI_Constants::get_page_type( 'DASHBOARD' ) ); ?>" class="health-coach-btn" style="background-color: #374151; color: white;">
											View Dashboard
										</a>
										<a href="<?php echo esc_url( $retake_url ); ?>" class="health-coach-btn" style="background-color: #374151; color: white;">
											Retake Assessment
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>

					<!-- Pillars Row - All 4 Assessment Pillars -->
					<div class="pillars-row">
					<?php foreach ($all_pillars as $pillar) : 
						$has_data = isset($assessment_pillar_scores[$pillar]) && is_numeric($assessment_pillar_scores[$pillar]);
						$score = $has_data ? $assessment_pillar_scores[$pillar] : 0;
						$score_percent = $score ? round($score * 10) : 0;
						
						// Calculate trend (for results page, we'll show improvement potential)
						$trend_value = $has_data ? min((10 - $score) * 0.1, 0.5) : 0;
						$trend_class = $trend_value > 0 ? 'up' : 'neutral';
						$trend_arrow = $trend_value > 0 ? '↗' : '→';
						?>
						
						<div class="pillar-card <?php echo esc_attr(strtolower($pillar)); ?>" 
						     style="--progress: <?php echo esc_attr($score_percent); ?>"
						     data-pillar="<?php echo esc_attr($pillar); ?>"
						     role="button"
						     tabindex="0"
						     aria-label="<?php echo esc_attr($pillar . ' assessment score ' . number_format($score, 1) . ' out of 10'); ?>">
							
							<div class="pillar-card-header">
								<div class="pillar-card-title">
									<?php echo esc_html($pillar); ?>
								</div>
							</div>
							
							<div class="pillar-card-score">
								<div class="pillar-score-value" aria-live="polite">
									<?php echo $has_data ? esc_html(number_format($score, 1)) : '0.0'; ?>
								</div>
								<div class="pillar-score-max">/10</div>
							</div>
							
							<div class="pillar-progress-bar" 
							     role="progressbar" 
							     aria-valuemin="0" 
							     aria-valuemax="10" 
							     aria-valuenow="<?php echo esc_attr($score); ?>">
								<div class="pillar-progress-fill"></div>
							</div>
							
							<div class="pillar-card-footer">
								<div class="pillar-trend <?php echo esc_attr($trend_class); ?>">
									<?php echo $trend_arrow; ?> Assessment Result
								</div>
								<div><?php echo esc_html($score_percent); ?>%</div>
							</div>
						</div>
					<?php endforeach; ?>
					</div>

					<!-- Initialize Progress Bar Animations -->
					<script>
					document.addEventListener('DOMContentLoaded', function() {
						// Initialize modern scoring UI for assessment results
						console.log('🎨 Initializing Assessment Results Scoring UI');
						
						// Animate progress bars after page load
						setTimeout(() => {
							const cards = document.querySelectorAll('.pillar-card');
							
							cards.forEach((card, index) => {
								const progress = parseInt(card.style.getPropertyValue('--progress') || 0);
								const progressFill = card.querySelector('.pillar-progress-fill');
								
								if (progressFill && progress > 0) {
									// Stagger animation for visual appeal
									setTimeout(() => {
										progressFill.style.setProperty('width', `${progress}%`, 'important');
									}, index * 200);
								}
							});
							
							// Animate circular progress for assessment score
							const circularProgress = document.querySelector('.progress-bar');
							if (circularProgress) {
								const progress = parseInt(circularProgress.getAttribute('data-progress') || 0);
								const circumference = 283; // 2 * π * 45
								const offset = circumference - (circumference * progress / 100);
								circularProgress.style.strokeDashoffset = offset;
							}
						}, 800);
						
						console.log('✅ Assessment Results Scoring UI initialized successfully');
					});
					</script>
				</div>
			</div>



			<!-- Next Steps -->
			<?php if ( isset( $result_content['next_steps'] ) ) : ?>
				<div class="ennu-card ennu-animate-in ennu-animate-delay-2">
					<h2 class="ennu-section-title">Next Steps</h2>
					<div class="ennu-card-content">
						<p><?php echo esc_html( $result_content['next_steps'] ); ?></p>
					</div>
				</div>
			<?php endif; ?>



	</div>
</div>

<!-- Theme system is now handled by the centralized ENNUThemeManager --> 
