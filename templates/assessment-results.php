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

	<!-- Universal Header Component -->
	<?php
	// Prepare header data
	$header_data = array(
		'display_name' => $display_name ?? '',
		'age' => $age ?? '',
		'gender' => $gender ?? '',
		'height' => $height ?? '',
		'weight' => $weight ?? '',
		'bmi' => $bmi ?? '',
		'show_vital_stats' => true,
		'show_theme_toggle' => false, // No theme toggle on results page
		'page_title' => $assessment_title,
		'page_subtitle' => isset( $result_content['summary'] ) ? $result_content['summary'] : 'Your personalized assessment results are ready.',
		'show_logo' => true,
		'logo_color' => 'black',
		'logo_size' => 'medium'
	);
	
	// Load the universal header component
	ennu_load_template( 'universal-header', $header_data );
	?>

	<div class="ennu-single-column">
		<!-- HubSpot Booking Calendar Embed -->
		<div class="ennu-hubspot-embed" style="margin-bottom: 2rem; text-align: center;">
			<!-- Start of Meetings Embed Script -->
			<div class="meetings-iframe-container" data-src="https://meetings.hubspot.com/lescobar2/ennulife?embed=true"></div>
			<script type="text/javascript" src="https://static.hsappstatic.net/MeetingsEmbed/ex/MeetingsEmbedCode.js"></script>
			<!-- End of Meetings Embed Script -->
		</div>

		<!-- Action Buttons -->
		<div class="ennu-btn-group" style="text-align: center; margin-bottom: 2rem;">
			<a href="<?php echo esc_url( $shortcode_instance->get_assessment_cta_url( $assessment_type ) ); ?>" class="ennu-btn ennu-btn-primary">
				Book Consultation
			</a>
			<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( 'dashboard' ) ); ?>" class="ennu-btn ennu-btn-secondary">
				View Dashboard
			</a>
		</div>

		<!-- Main Content -->
		<main class="ennu-main-content">
			<!-- Assessment Results Score Display - TOP CENTER -->
			<div class="assessment-scores-section" style="margin-top: 0; padding-top: 2rem;">
				<!-- Scores Content Grid -->
				<div class="scores-content-grid">
					<!-- Left Pillar Scores -->
					<div class="pillar-scores-left">
						<?php
						// Debug output - Pillar scores fix working correctly
						if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
							echo "<!-- DEBUG: Real pillar scores being displayed correctly -->\n";
						}
						
						// Only use pillar_scores - we have exactly 4 pillars: Mind, Body, Lifestyle, Aesthetics
						$display_scores = $pillar_scores ?? array();
						
						// Always show the 4 pillars, even if some have zero scores
						if ( is_array( $display_scores ) && count( $display_scores ) > 0 ) {
							$pillar_count = 0;
							
							// Use original pillar names
							$pillar_display_names = array(
								'Mind'       => 'Mind',
								'Body'       => 'Body',
								'Lifestyle'  => 'Lifestyle',
								'Aesthetics' => 'Aesthetics',
							);
							
							foreach ( $display_scores as $pillar => $score ) {
								if ( $pillar_count >= 2 ) { break; }
								
								$display_name = $pillar_display_names[ $pillar ] ?? $pillar;
								
								$has_data = is_numeric( $score );
								$pillar_class = esc_attr( strtolower( $pillar ) );
								$spin_duration = $has_data ? max( 2, 11 - $score ) : 10;
								$style_attr = '--spin-duration: ' . $spin_duration . 's;';
								?>
								<div class="pillar-orb <?php echo $pillar_class; ?> <?php echo $has_data ? '' : 'no-data'; ?>" style="<?php echo esc_attr( $style_attr ); ?>">
									<svg class="pillar-orb-progress" viewBox="0 0 36 36">
										<circle class="pillar-orb-progress-bg" cx="18" cy="18" r="15.9155"></circle>
										<circle class="pillar-orb-progress-bar" cx="18" cy="18" r="15.9155" style="--score-percent: <?php echo esc_attr( $has_data ? $score * 10 : 0 ); ?>;"></circle>
									</svg>
									<div class="pillar-orb-content">
										<div class="pillar-orb-label"><?php echo esc_html( $display_name ); ?></div>
										<div class="pillar-orb-score"><?php echo $has_data ? esc_html( number_format( $score, 1 ) ) : '-'; ?></div>
									</div>
									<div class="floating-particles"></div>
									<div class="decoration-dots"></div>
								</div>
								<?php
								$pillar_count++;
							}
						} else {
							// Show sample data if no scores available
							$sample_pillars = array( 'Mind' => 7.5, 'Body' => 6.8 );
							foreach ( $sample_pillars as $pillar => $score ) {
								$display_name = $pillar === 'Mind' ? 'Mental Health' : 'Physical Health';
								$pillar_class = esc_attr( strtolower( $pillar ) );
								$spin_duration = max( 2, 11 - $score );
								$style_attr = '--spin-duration: ' . $spin_duration . 's;';
								?>
								<div class="pillar-orb <?php echo $pillar_class; ?>" style="<?php echo esc_attr( $style_attr ); ?>">
									<svg class="pillar-orb-progress" viewBox="0 0 36 36">
										<circle class="pillar-orb-progress-bg" cx="18" cy="18" r="15.9155"></circle>
										<circle class="pillar-orb-progress-bar" cx="18" cy="18" r="15.9155" style="--score-percent: <?php echo esc_attr( $score * 10 ); ?>;"></circle>
									</svg>
									<div class="pillar-orb-content">
										<div class="pillar-orb-label"><?php echo esc_html( $display_name ); ?></div>
										<div class="pillar-orb-score"><?php echo esc_html( number_format( $score, 1 ) ); ?></div>
									</div>
									<div class="floating-particles"></div>
									<div class="decoration-dots"></div>
								</div>
								<?php
							}
						}
						?>
					</div>

					<!-- Center Assessment Score -->
					<div class="ennu-life-score-center">
						<div class="main-score-orb" data-score="<?php echo esc_attr( $overall_score ?? 0 ); ?>">
							<svg class="pillar-orb-progress" viewBox="0 0 36 36">
								<defs>
									<linearGradient id="assessment-score-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
										<stop offset="0%" stop-color="rgba(16, 185, 129, 0.6)"/>
										<stop offset="50%" stop-color="rgba(5, 150, 105, 0.6)"/>
										<stop offset="100%" stop-color="rgba(4, 120, 87, 0.6)"/>
									</linearGradient>
								</defs>
								<circle class="pillar-orb-progress-bg" cx="18" cy="18" r="15.9155"></circle>
								<circle class="pillar-orb-progress-bar" cx="18" cy="18" r="15.9155" style="--score-percent: <?php echo esc_attr( ( $overall_score ?? 0 ) * 10 ); ?>;"></circle>
							</svg>
							<div class="main-score-text">
								<div class="main-score-value"><?php echo esc_html( number_format( $overall_score ?? 0, 1 ) ); ?></div>
								<div class="main-score-label"><?php echo esc_html( ucwords( str_replace( '_', ' ', $assessment_type ) ) ); ?> Score</div>
							</div>
							<div class="decoration-dots"></div>
						</div>
					</div>

					<!-- Right Pillar Scores -->
					<div class="pillar-scores-right">
						<?php
						// Only use pillar_scores - we have exactly 4 pillars: Mind, Body, Lifestyle, Aesthetics
						$display_scores = $pillar_scores ?? array();
						
						// Always show the 4 pillars, even if some have zero scores
						if ( is_array( $display_scores ) && count( $display_scores ) > 0 ) {
							$pillar_count = 0;
							$total_pillars = count( $display_scores );
							
							// Use original pillar names
							$pillar_display_names = array(
								'Mind'       => 'Mind',
								'Body'       => 'Body',
								'Lifestyle'  => 'Lifestyle',
								'Aesthetics' => 'Aesthetics',
							);
							
							foreach ( $display_scores as $pillar => $score ) {
								if ( $pillar_count < 2 ) { $pillar_count++; continue; }
								if ( $pillar_count >= 4 ) { break; }
								
								$display_name = $pillar_display_names[ $pillar ] ?? $pillar;
								
								$has_data = is_numeric( $score );
								$pillar_class = esc_attr( strtolower( $pillar ) );
								$spin_duration = $has_data ? max( 2, 11 - $score ) : 10;
								$style_attr = '--spin-duration: ' . $spin_duration . 's;';
								?>
								<div class="pillar-orb <?php echo $pillar_class; ?> <?php echo $has_data ? '' : 'no-data'; ?>" style="<?php echo esc_attr( $style_attr ); ?>">
									<svg class="pillar-orb-progress" viewBox="0 0 36 36">
										<circle class="pillar-orb-progress-bg" cx="18" cy="18" r="15.9155"></circle>
										<circle class="pillar-orb-progress-bar" cx="18" cy="18" r="15.9155" style="--score-percent: <?php echo esc_attr( $has_data ? $score * 10 : 0 ); ?>;"></circle>
									</svg>
									<div class="pillar-orb-content">
										<div class="pillar-orb-label"><?php echo esc_html( $display_name ); ?></div>
										<div class="pillar-orb-score"><?php echo $has_data ? esc_html( number_format( $score, 1 ) ) : '-'; ?></div>
									</div>
									<div class="floating-particles"></div>
									<div class="decoration-dots"></div>
								</div>
								<?php
								$pillar_count++;
							}
						} else {
							// Show sample data if no scores available
							$sample_pillars = array( 'Lifestyle' => 8.2, 'Aesthetics' => 7.1 );
							foreach ( $sample_pillars as $pillar => $score ) {
								$display_name = $pillar === 'Lifestyle' ? 'Lifestyle' : 'Appearance';
								$pillar_class = esc_attr( strtolower( $pillar ) );
								$spin_duration = max( 2, 11 - $score );
								$style_attr = '--spin-duration: ' . $spin_duration . 's;';
								?>
								<div class="pillar-orb <?php echo $pillar_class; ?>" style="<?php echo esc_attr( $style_attr ); ?>">
									<svg class="pillar-orb-progress" viewBox="0 0 36 36">
										<circle class="pillar-orb-progress-bg" cx="18" cy="18" r="15.9155"></circle>
										<circle class="pillar-orb-progress-bar" cx="18" cy="18" r="15.9155" style="--score-percent: <?php echo esc_attr( $score * 10 ); ?>;"></circle>
									</svg>
									<div class="pillar-orb-content">
										<div class="pillar-orb-label"><?php echo esc_html( $pillar ); ?></div>
										<div class="pillar-orb-score"><?php echo esc_html( number_format( $score, 1 ) ); ?></div>
									</div>
									<div class="floating-particles"></div>
									<div class="decoration-dots"></div>
								</div>
								<?php
							}
						}
						?>
					</div>
				</div>
			</div>

			<!-- Header -->
			<div class="ennu-animate-in">
				<h1 class="ennu-title"><?php echo esc_html( $assessment_title ); ?> Results</h1>
				<p class="ennu-subtitle">
					Your personalized health insights and recommendations based on your assessment responses.
				</p>
			</div>

			<!-- Category Scores -->
			<?php if ( ! empty( $category_scores ) ) : ?>
				<div class="ennu-card ennu-animate-in ennu-animate-delay-1">
					<h2 class="ennu-section-title">Category Breakdown</h2>
					<div class="ennu-list">
						<?php foreach ( $category_scores as $category => $cat_score ) : ?>
							<div class="ennu-list-item">
								<div class="ennu-list-item-content">
									<div class="ennu-list-item-title"><?php echo esc_html( $category ); ?></div>
									<div class="ennu-progress-bar">
										<div class="ennu-progress-fill" style="--progress-width: <?php echo esc_attr( ( $cat_score / 10 ) * 100 ); ?>%"></div>
									</div>
								</div>
								<div class="ennu-list-item-score"><?php echo esc_html( number_format( $cat_score, 1 ) ); ?>/10</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Recommendations -->
			<?php if ( isset( $result_content['recommendations'] ) && ! empty( $result_content['recommendations'] ) ) : ?>
				<div class="ennu-card ennu-animate-in ennu-animate-delay-2">
					<h2 class="ennu-section-title">Personalized Recommendations</h2>
					<div class="ennu-list">
						<?php foreach ( $result_content['recommendations'] as $recommendation ) : ?>
							<div class="ennu-list-item">
								<div class="ennu-list-item-content">
									<div class="ennu-list-item-description">
										<span style="color: var(--accent-primary); margin-right: 8px;">✓</span>
										<?php echo esc_html( $recommendation ); ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Next Steps -->
			<?php if ( isset( $result_content['next_steps'] ) ) : ?>
				<div class="ennu-card ennu-animate-in ennu-animate-delay-3">
					<h2 class="ennu-section-title">Next Steps</h2>
					<div class="ennu-card-content">
						<p><?php echo esc_html( $result_content['next_steps'] ); ?></p>
					</div>
				</div>
			<?php endif; ?>

			<!-- Benefits -->
			<?php if ( isset( $result_content['benefits'] ) && ! empty( $result_content['benefits'] ) ) : ?>
				<div class="ennu-card ennu-animate-in ennu-animate-delay-4">
					<h2 class="ennu-section-title">What You'll Gain</h2>
					<div class="ennu-list">
						<?php foreach ( $result_content['benefits'] as $benefit ) : ?>
							<div class="ennu-list-item">
								<div class="ennu-list-item-content">
									<div class="ennu-list-item-description">
										<span style="color: var(--accent-primary); margin-right: 8px;">→</span>
										<?php echo esc_html( $benefit ); ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Biomarkers Section -->
			<?php error_log( 'ENNU Results: Reaching biomarkers section' ); ?>
			<div class="ennu-card ennu-animate-in ennu-animate-delay-5">
				<h2 class="ennu-section-title">Related Biomarkers</h2>
				<div class="ennu-biomarkers-section">
					<?php
					// Debug: Check if biomarkers template is loading
					error_log( 'ENNU Results: Loading biomarkers template for user ' . get_current_user_id() );
					
					// Load the biomarkers-only template content
					$biomarkers_data = array(
						'user_id' => get_current_user_id(),
						'user_age' => $age ?? 35,
						'user_gender' => $gender ?? 'male'
					);
					
					// Load the biomarkers template
					ennu_load_template( 'biomarkers-only', $biomarkers_data );
					
					error_log( 'ENNU Results: Biomarkers template loaded successfully' );
					?>
				</div>
			</div>
		</main>
	</div>
</div>

<!-- Theme system is now handled by the centralized ENNUThemeManager --> 
