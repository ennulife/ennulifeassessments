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
$score = $score ?? 0;
$result_content = $result_content ?? array();
$assessment_title = $assessment_title ?? 'Assessment Results';
$category_scores = $category_scores ?? array();
$matched_recs = $matched_recs ?? array();
$shortcode_instance = $shortcode_instance ?? null;
$assessment_type = $assessment_type ?? '';

if (empty($shortcode_instance) || !is_object($shortcode_instance)) {
    echo '<div class="ennu-error">ERROR: Shortcode instance is missing. Please contact support.</div>';
    return;
}

// Defensive check for assessment_type
if (empty($assessment_type)) {
    echo '<div class="ennu-error">ERROR: Assessment type is missing. Please contact support.</div>';
    return;
}

?>

<div class="ennu-unified-container" data-theme="dark">
	<div class="starfield"></div>

	<!-- Theme Toggle -->
	<div class="ennu-theme-toggle">
		<button class="ennu-theme-btn" onclick="toggleTheme()" aria-label="Toggle theme">
			<svg class="ennu-theme-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path class="ennu-sun-icon" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
				<path class="ennu-moon-icon" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
			</svg>
		</button>
	</div>

	<div class="ennu-grid">
		<!-- Sidebar -->
		<aside class="ennu-sidebar">
			<!-- Logo -->
			<?php if ( function_exists( 'ennu_render_logo' ) ) : ?>
				<div class="ennu-logo-container" style="text-align: center; margin-bottom: 30px;">
					<?php
					ennu_render_logo([
						'color' => 'white',
						'size' => 'medium',
						'link' => home_url( '/' ),
						'alt' => 'ENNU Life',
						'class' => ''
					]);
					?>
				</div>
			<?php endif; ?>

			<!-- Main Score Orb -->
			<div class="ennu-score-orb" data-score="<?php echo esc_attr( $score ); ?>">
				<svg viewBox="0 0 36 36">
					<defs>
						<linearGradient id="scoreGradient" x1="0%" y1="0%" x2="100%" y2="100%">
							<stop offset="0%" stop-color="var(--accent-primary)"/>
							<stop offset="100%" stop-color="var(--accent-secondary)"/>
						</linearGradient>
					</defs>
					<circle class="ennu-score-orb-bg" cx="18" cy="18" r="15.9155"></circle>
					<circle class="ennu-score-orb-progress" cx="18" cy="18" r="15.9155" style="--score-percent: <?php echo esc_attr( $score * 10 ); ?>;"></circle>
				</svg>
				<div class="ennu-score-text">
					<div class="ennu-score-value"><?php echo esc_html( number_format( $score, 1 ) ); ?></div>
					<div class="ennu-score-label"><?php echo esc_html( $assessment_title ); ?> Score</div>
				</div>
			</div>

			<!-- Score Insight -->
			<div class="ennu-glass-card">
				<h3 class="ennu-section-title">Score Insight</h3>
				<div class="ennu-card-content">
					<?php if ( isset( $result_content['summary'] ) ) : ?>
						<p><?php echo esc_html( $result_content['summary'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<!-- Action Buttons -->
			<div class="ennu-btn-group">
				<a href="<?php echo esc_url( $shortcode_instance->get_assessment_cta_url( $assessment_type ) ); ?>" class="ennu-btn ennu-btn-primary">
					Book Consultation
				</a>
				<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( 'dashboard' ) ); ?>" class="ennu-btn ennu-btn-secondary">
					View Dashboard
				</a>
			</div>
		</aside>

		<!-- Main Content -->
		<main class="ennu-main-content">
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
		</main>
	</div>
</div>

<script>
// Theme toggle functionality
function toggleTheme() {
	const container = document.querySelector('.ennu-unified-container');
	const currentTheme = container.getAttribute('data-theme');
	const newTheme = currentTheme === 'light' ? 'dark' : 'light';
	container.setAttribute('data-theme', newTheme);
	localStorage.setItem('ennu-theme', newTheme);
}

document.addEventListener('DOMContentLoaded', function() {
	const savedTheme = localStorage.getItem('ennu-theme') || 'dark';
	const container = document.querySelector('.ennu-unified-container');
	container.setAttribute('data-theme', savedTheme);
});
</script> 
