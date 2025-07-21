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

<div class="ennu-unified-container">
	<div class="starfield"></div>
	
	<div class="ennu-grid">
		<!-- Sidebar -->
		<aside class="ennu-sidebar">
			<!-- Logo -->
			<?php if ( function_exists( 'ennu_render_logo' ) ) : ?>
				<div class="ennu-logo-container" style="text-align: center; margin-bottom: 30px;">
					<?php
					ennu_render_logo(
						array(
							'color' => 'white',
							'size'  => 'medium',
							'link'  => home_url( '/' ),
							'alt'   => 'ENNU Life',
							'class' => '',
						)
					);
					?>
				</div>
			<?php endif; ?>

			<!-- User Info -->
			<div class="ennu-glass-card">
				<h3 class="ennu-section-title">Your Profile</h3>
				<div class="ennu-card-content">
					<p><strong><?php echo esc_html( $current_user->first_name . ' ' . $current_user->last_name ); ?></strong></p>
					<p>Age: <?php echo esc_html( $age ); ?></p>
					<p>Gender: <?php echo esc_html( $gender ); ?></p>
				</div>
			</div>

			<!-- Main Score Orb -->
			<div class="ennu-score-orb" data-score="<?php echo esc_attr( $score ?? 0 ); ?>">
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
				<div class="ennu-glass-card">
					<h3 class="ennu-section-title">Pillar Scores</h3>
					<div class="ennu-pillar-grid">
						<?php foreach ( $pillar_scores as $pillar => $pillar_score ) : ?>
							<?php
							$has_data     = ! empty( $pillar_score );
							$pillar_class = esc_attr( strtolower( $pillar ) );
							?>
							<div class="ennu-pillar-orb <?php echo $pillar_class; ?> <?php echo $has_data ? '' : 'no-data'; ?>">
								<div class="ennu-pillar-content">
									<div class="ennu-pillar-label"><?php echo esc_html( $pillar ); ?></div>
									<div class="ennu-pillar-score"><?php echo $has_data ? esc_html( number_format( $pillar_score, 1 ) ) : '-'; ?></div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Action Buttons -->
			<div class="ennu-btn-group">
				<a href="<?php echo esc_url( $shortcode_instance->get_assessment_cta_url( $assessment_type_slug . '_assessment' ) ); ?>" class="ennu-btn ennu-btn-primary">
					Book Consultation
				</a>
				<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( 'ennu-life-score' ) ); ?>" class="ennu-btn ennu-btn-secondary">
					Get Your ENNU Life Score
				</a>
			</div>
		</aside>

		<!-- Main Content -->
		<main class="ennu-main-content">
			<!-- Header -->
			<div class="ennu-animate-in">
				<h1 class="ennu-title">Your <?php echo esc_html( ucwords( str_replace( '_', ' ', $assessment_type_slug ) ) ); ?> Dossier</h1>
				<p class="ennu-subtitle">
					<strong><?php echo esc_html( $result_content['title'] ); ?>:</strong>
					<?php echo esc_html( $result_content['summary'] ); ?>
				</p>
			</div>

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
