<?php
/**
 * Template for displaying assessment details - REBORN as a Bio-Metric Canvas Overture
 * This template is now a "dumb" component. All data fetching and processing
 * is handled in the `render_detailed_results_page` method.
 *
 * @version 62.1.57
 * @see ENNU_Assessment_Shortcodes::render_detailed_results_page()
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// All data is passed in via the $data variable, which is extracted by ennu_load_template().
// This removes the need for local variables with null coalescing.

// Defensive checks for required variables
$score              = $score ?? 0;
$result_content     = $result_content ?? array();
$assessment_title   = $assessment_title ?? 'Assessment Details';
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

	<!-- Simplified Header for Details Page -->
	<?php
	// Prepare minimal header data for details page
	$header_data = array(
		'display_name' => $display_name ?? '',
		'show_vital_stats' => false, // No vital stats on details page
		'show_theme_toggle' => false, // No theme toggle on details page
		'page_title' => $assessment_title,
		'page_subtitle' => isset( $result_content['summary'] ) ? $result_content['summary'] : 'Your detailed assessment analysis and progress tracking.',
		'show_logo' => false, // Logo already in hero section
		'minimal' => true // Use minimal header style
	);
	
	// Load the universal header component if needed
	// ennu_load_template( 'universal-header', $header_data );
	?>

	<div class="ennu-single-column">
		
		<!-- Dynamic Details Header Section -->
		<div class="ennu-details-header" style="text-align: center; padding: 2rem 1rem; margin-bottom: 2rem;">
			<?php
			// Get user's first name for personalization
			$user_id = get_current_user_id();
			$first_name = '';
			if ( $user_id ) {
				$user_data = get_userdata( $user_id );
				$first_name = $user_data->first_name ?: $user_data->display_name ?: '';
			}
			
			// Create personalized header
			$details_headline = 'Detailed Analysis & Progress Tracking';
			if ( ! empty( $first_name ) ) {
				$details_headline = $first_name . ', Here\'s Your ' . $details_headline;
			}
			?>
			
			<h1 class="ennu-details-headline" style="font-size: 2.5rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem;">
				<?php echo esc_html( $details_headline ); ?>
			</h1>
			<p class="ennu-details-subheadline" style="font-size: 1.25rem; color: #6b7280; max-width: 700px; margin: 0 auto;">
				Dive deep into your assessment results with comprehensive breakdowns, progress charts, and biomarker analysis.
			</p>
		</div>

			<!-- Assessment Details Score Display - Modern Card Design -->
			<div class="assessment-scores-section" style="margin-top: 0; padding-top: 2rem;">
				<!-- Include Modern Scoring UI CSS -->
				<link rel="stylesheet" href="<?php echo esc_url( plugins_url( 'assets/css/modern-scoring-ui.css', dirname( __FILE__ ) ) ); ?>" />
				
				<!-- Assessment Details Title -->
				<h2 class="ennu-section-title" style="text-align: center; margin-bottom: 2rem; color: #1f2937; font-size: 2rem; font-weight: 600;">Detailed Assessment Analysis</h2>
				
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
										<h3 style="margin: 0; font-size: 16px; font-weight: 600;"><?php echo esc_html( ucwords( str_replace( '_', ' ', $assessment_type ) ) ); ?> Details</h3>
									</div>
									<div class="member-details">
										<div class="member-detail-item">
											<span class="detail-label">Status:</span>
											<span class="detail-value">Analysis Complete</span>
										</div>
										<?php if ( ! empty( $display_name ) ) : ?>
										<div class="member-detail-item">
											<span class="detail-label">For:</span>
											<span class="detail-value"><?php echo esc_html( $display_name ); ?></span>
										</div>
										<?php endif; ?>
										<div class="member-detail-item">
											<span class="detail-label">View:</span>
											<span class="detail-value">Detailed Analysis</span>
										</div>
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
						
							<!-- Column 3: Progress & Actions -->
							<div class="pillar-card coach-info-card">
								<div class="pillar-card-header">
									<div class="pillar-card-title">Progress & Actions</div>
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
						
						// Calculate trend (for details page, we'll show improvement potential)
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
									<?php echo $trend_arrow; ?> Assessment Detail
								</div>
								<div><?php echo esc_html($score_percent); ?>%</div>
							</div>
						</div>
					<?php endforeach; ?>
					</div>

					<!-- Initialize Progress Bar Animations -->
					<script>
					document.addEventListener('DOMContentLoaded', function() {
						// Initialize modern scoring UI for assessment details
						console.log('🎨 Initializing Assessment Details Scoring UI');
						
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
						
						console.log('✅ Assessment Details Scoring UI initialized successfully');
					});
					</script>
				</div>
			</div>

			<!-- PROGRESS OVER TIME CHART SECTION -->
			<div class="ennu-card ennu-animate-in ennu-animate-delay-1">
				<h2 class="ennu-section-title">Progress Over Time</h2>
				<div class="ennu-progress-chart-container">
                    <div class="ennu-progress-chart">
                        <canvas id="assessmentTimelineChart" width="800" height="400"></canvas>
                    </div>
					<div class="ennu-progress-stats">
						<div class="ennu-progress-stat">
							<div class="ennu-progress-stat-value"><?php echo esc_html( number_format( $overall_score ?? 0, 1 ) ); ?></div>
							<div class="ennu-progress-stat-label">Current Score</div>
						</div>
						<div class="ennu-progress-stat">
							<?php 
							// Calculate evidence-based target score based on current score and category
							$current_score = $overall_score ?? 0;
							$target_score = $current_score;
							$improvement_needed = 0;
							
							if ($current_score > 0) {
								// Evidence-based improvement targets based on health assessment research
								if ($current_score < 5.0) {
									$improvement_needed = min(2.0, 10.0 - $current_score); // Significant improvement potential
								} elseif ($current_score < 7.0) {
									$improvement_needed = min(1.5, 10.0 - $current_score); // Moderate improvement potential  
								} elseif ($current_score < 8.5) {
									$improvement_needed = min(1.0, 10.0 - $current_score); // Gradual improvement potential
								} else {
									$improvement_needed = min(0.5, 10.0 - $current_score); // Maintenance/fine-tuning
								}
								$target_score = min(10.0, $current_score + $improvement_needed);
							}
							?>
							<div class="ennu-progress-stat-value"><?php echo esc_html( number_format( $target_score, 1 ) ); ?></div>
							<div class="ennu-progress-stat-label">Realistic Target</div>
						</div>
						<div class="ennu-progress-stat">
							<div class="ennu-progress-stat-value">+<?php echo esc_html( number_format( $improvement_needed, 1 ) ); ?></div>
							<div class="ennu-progress-stat-label">Potential Improvement</div>
						</div>
					</div>
				</div>
			</div>



			<!-- Next Steps -->
			<?php if ( isset( $result_content['next_steps'] ) ) : ?>
				<div class="ennu-card ennu-animate-in ennu-animate-delay-3">
					<h2 class="ennu-section-title">Next Steps</h2>
					<div class="ennu-card-content">
						<p><?php echo esc_html( $result_content['next_steps'] ); ?></p>
					</div>
				</div>
			<?php endif; ?>


	</div>
</div>

<!-- Progress Chart JavaScript -->
<!-- Chart boot handled by assets/js/assessment-details.js -->

<!-- Theme system is now handled by the centralized ENNUThemeManager --> 
