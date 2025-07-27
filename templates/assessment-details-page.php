<?php
/**
 * Template for the "Health Dossier" - a hyper-personalized, stunning results page.
 *
 * This template is now a "dumb" component. All data fetching and processing
 * is handled in the `render_detailed_results_page` method in the
 * `ENNU_Assessment_Shortcodes` class.
 *
 * @version 62.1.57
 * @see ENNU_Assessment_Shortcodes::render_detailed_results_page()
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ennu-unified-container assessment-details-page" data-theme="light">

	<!-- Universal Header Component -->
	<?php
	// Prepare header data
	$header_data = array(
		'display_name' => $current_user->first_name . ' ' . $current_user->last_name,
		'age' => $age ?? '',
		'gender' => $gender ?? '',
		'height' => '',
		'weight' => '',
		'bmi' => '',
		'show_vital_stats' => true,
		'show_theme_toggle' => false, // No theme toggle on dossier page
		'page_title' => ucwords( str_replace( '_', ' ', $assessment_type_slug ) ) . ' Dossier',
		'page_subtitle' => isset( $result_content['summary'] ) ? $result_content['summary'] : 'Your detailed assessment analysis and personalized insights.',
		'show_logo' => true,
		'logo_color' => 'white',
		'logo_size' => 'medium'
	);
	
	// Load the universal header component
	ennu_load_template( 'universal-header', $header_data );
	?>

	<div class="ennu-single-column">
		<!-- Action Buttons -->
		<div class="ennu-btn-group" style="text-align: center; margin-bottom: 2rem;">
			<a href="<?php echo esc_url( $shortcode_instance->get_assessment_cta_url( $assessment_type_slug . '_assessment' ) ); ?>" class="ennu-btn ennu-btn-primary">
				Book Consultation
			</a>
			<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( 'dashboard' ) ); ?>" class="ennu-btn ennu-btn-secondary">
				View Dashboard
			</a>
		</div>

		<!-- Main Content -->
		<main class="ennu-main-content">
			<!-- Assessment Score Display -->
			<div class="ennu-card ennu-animate-in">
				<div class="ennu-score-display" style="text-align: center; margin-bottom: 2rem;">
					<!-- Main Score Orb -->
					<div class="ennu-score-orb" data-score="<?php echo esc_attr( $score ?? 0 ); ?>" style="margin: 0 auto 1rem;">
						<svg viewBox="0 0 36 36">
							<defs>
								<linearGradient id="scoreGradient" x1="0%" y1="0%" x2="100%" y2="100%">
									<stop offset="0%" stop-color="var(--accent-primary)"/>
									<stop offset="100%" stop-color="var(--accent-secondary)"/>
								</linearGradient>
							</defs>
							<circle class="ennu-score-orb-bg" cx="18" cy="18" r="15.9155"></circle>
							<circle class="ennu-score-orb-progress" cx="18" cy="18" r="15.9155" style="--score-percent: <?php echo esc_attr( ( $score ?? 0 ) * 10 ); ?>;"></circle>
						</svg>
						<div class="ennu-score-text">
							<div class="ennu-score-value"><?php echo esc_html( number_format( $score ?? 0, 1 ) ); ?></div>
							<div class="ennu-score-label"><?php echo esc_html( ucwords( str_replace( '_', ' ', $assessment_type_slug ) ) ); ?> Score</div>
						</div>
					</div>

					<!-- Pillar Scores -->
					<?php if ( is_array( $pillar_scores ) && ! empty( $pillar_scores ) ) : ?>
						<div class="ennu-pillar-scores" style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">
							<?php foreach ( $pillar_scores as $pillar => $pillar_score ) : ?>
								<?php
								$has_data     = ! empty( $pillar_score );
								$pillar_class = esc_attr( strtolower( $pillar ) );
								?>
								<div class="ennu-pillar-orb <?php echo $pillar_class; ?> <?php echo $has_data ? '' : 'no-data'; ?>" style="flex: 0 0 auto;">
									<div class="ennu-pillar-content">
										<div class="ennu-pillar-label"><?php echo esc_html( $pillar ); ?></div>
										<div class="ennu-pillar-score"><?php echo $has_data ? esc_html( number_format( $pillar_score, 1 ) ) : '-'; ?></div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

		<!-- Main Content -->
		<main class="ennu-main-content">

			<!-- Progress Timeline -->
			<div class="ennu-card ennu-animate-in ennu-animate-delay-1">
				<h2 class="ennu-section-title">Progress Over Time</h2>
				<div class="ennu-chart-container" style="height: 250px;">
					<canvas id="assessmentTimelineChart"></canvas>
				</div>
			</div>

			<!-- Recommendations -->
			<?php if ( isset( $result_content['recommendations'] ) && ! empty( $result_content['recommendations'] ) ) : ?>
				<div class="ennu-card ennu-animate-in ennu-animate-delay-2">
					<h2 class="ennu-section-title">Personalized Recommendations</h2>
					<div class="ennu-list">
						<?php foreach ( $result_content['recommendations'] as $rec ) : ?>
							<div class="ennu-list-item">
								<div class="ennu-list-item-content">
									<div class="ennu-list-item-description">
										<span style="color: var(--accent-primary); margin-right: 8px;">✓</span>
										<?php echo esc_html( $rec ); ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Category Deep Dive -->
			<?php if ( ! empty( $category_scores ) ) : ?>
				<div class="ennu-animate-in ennu-animate-delay-3">
					<h2 class="ennu-section-title">Category Analysis</h2>
					<div class="ennu-category-grid">
						<?php foreach ( $category_scores as $category => $cat_score ) : ?>
							<div class="ennu-card">
								<div class="ennu-card-header">
									<h3 class="ennu-card-title"><?php echo esc_html( $category ); ?></h3>
									<div class="ennu-list-item-score"><?php echo esc_html( number_format( $cat_score, 1 ) ); ?>/10</div>
								</div>
								<div class="ennu-progress-bar">
									<div class="ennu-progress-fill" style="--progress-width: <?php echo esc_attr( ( $cat_score / 10 ) * 100 ); ?>%"></div>
								</div>
								<div class="ennu-card-content">
									<p><?php echo esc_html( $deep_dive_content[ $category ]['explanation'] ?? '' ); ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Biomarkers Section -->
			<div class="ennu-animate-in ennu-animate-delay-4">
				<h2 class="ennu-section-title">Related Biomarkers</h2>
				<div class="ennu-biomarkers-section">
					<?php
					// Debug: Check if biomarkers template is loading
					error_log( 'ENNU Dossier: Loading biomarkers template for user ' . get_current_user_id() );
					
					// Load the biomarkers-only template content
					$biomarkers_data = array(
						'user_id' => get_current_user_id(),
						'user_age' => $age ?? 35,
						'user_gender' => $gender ?? 'male'
					);
					
					// Load the biomarkers template
					ennu_load_template( 'biomarkers-only', $biomarkers_data );
					
					error_log( 'ENNU Dossier: Biomarkers template loaded successfully' );
					?>
				</div>
			</div>
		</main>
	</div>
</div>

<style>
/* Additional specific styles for assessment details */
.ennu-category-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 20px;
	margin-top: 20px;
}

.ennu-chart-container {
	position: relative;
	background: var(--card-bg);
	border-radius: var(--rounded-md);
	padding: 20px;
}

@media (max-width: 768px) {
	.ennu-category-grid {
		grid-template-columns: 1fr;
	}
}
</style> 
