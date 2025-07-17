<?php
/**
 * Template for displaying assessment results - REBORN as a "Bio-Metric Canvas" Overture
 * This template is now a "dumb" component. All data fetching and processing
 * is handled in the `render_thank_you_page` method.
 *
 * @version 53.0.0
 * @see ENNU_Assessment_Shortcodes::render_thank_you_page()
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// All data is passed in via the $data variable, which is extracted by ennu_load_template().
// This removes the need for local variables with null coalescing.
?>

<div class="ennu-user-dashboard"> <!-- Use the main dashboard class for consistent styling -->
	<div class="starfield"></div>
	<div class="dossier-grid">
		<aside class="dossier-sidebar">
			<div class="main-score-orb" data-score="<?php echo esc_attr( $score ); ?>">
				<svg class="pillar-orb-progress" viewBox="0 0 36 36">
					<circle class="pillar-orb-progress-bg" cx="18" cy="18" r="15.9155"></circle>
					<circle class="pillar-orb-progress-bar" cx="18" cy="18" r="15.9155" style="--score-percent: <?php echo esc_attr( $score * 10 ); ?>;"></circle>
				</svg>
				<div class="main-score-text">
					<div class="main-score-value"><?php echo esc_html( number_format( $score, 1 ) ); ?></div>
					<div class="main-score-label">Your Score</div>
				</div>
			</div>

			<div class="main-score-insight" style="opacity: 1; text-align:center;">
				<h2 class="ennu-score-card-title" style="color: #fff; font-size: 1.5rem; margin-bottom: 10px;"><?php echo esc_html( $result_content['title'] ); ?></h2>
				<p class="ennu-score-summary" style="font-size: 1rem;"><?php echo esc_html( $result_content['summary'] ); ?></p>
			</div>
			<div class="assessment-actions-footer" style="padding-top: 15px; border-top: 1px solid var(--border-color); margin-top: 15px; display: flex; flex-direction: column; gap: 10px;">
				<a href="<?php echo esc_url( $shortcode_instance->get_assessment_cta_url( $assessment_type ) ); ?>" class="action-button button-report" style="flex: 1; text-align: center; background-color: var(--accent-secondary); color: #fff; padding: 10px; text-decoration: none; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease; font-size: 0.9rem;">Book Consultation</a>
				<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( 'ennu-life-score' ) ); ?>" class="action-button button-retake" style="flex: 1; text-align: center; background-color: var(--card-bg); color: var(--text-light); border: 1px solid var(--border-color); padding: 10px; text-decoration: none; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease; font-size: 0.9rem;">Get Your ENNU Life Score</a>
			</div>
		</aside>

		<main class="dossier-main-content">
			<div class="dossier-header">
				<h1><?php echo esc_html( $assessment_title ); ?></h1>
				<p class="ai-narrative">Here is a summary of your results. Your complete, permanent record is available on your main dashboard.</p>
			</div>
			
			<div class="deep-dive-grid">
				<div class="category-card recommendations-card">
					<h3><?php echo esc_html__( 'Personalized Recommendations', 'ennulifeassessments' ); ?></h3>
					<ul>
						<?php foreach ( $result_content['recommendations'] as $rec ) : ?>
							<li><?php echo esc_html( $rec ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
				
				<?php if ( ! empty( $category_scores ) ) : ?>
					<div class="category-card">
						<h3><?php echo esc_html__( 'Score Breakdown', 'ennulifeassessments' ); ?></h3>
						<?php foreach ( $category_scores as $category => $cat_score ) : ?>
							<div class="category-score-item" style="--score: <?php echo esc_attr( $cat_score ); ?>">
								<div class="category-info">
									<span class="category-name"><?php echo esc_html( $category ); ?></span>
									<span class="category-score-value"><?php echo esc_html( number_format( $cat_score, 1 ) ); ?> / 10</span>
								</div>
								<div class="category-bar-bg">
									<div class="bar-fill"></div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $matched_recs ) ) : ?>
				<div class="category-card conditional-recs-card">
					<h3><?php echo esc_html__( 'Specific Observations', 'ennulifeassessments' ); ?></h3>
					<ul>
						<?php foreach ( $matched_recs as $rec ) : ?>
							<li><?php echo esc_html( $rec ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php endif; ?>
			</div>
		</main>
	</div>
</div>

<script>
// Small inline script to trigger animations on this specific page
document.addEventListener('DOMContentLoaded', function() {
    const mainOrb = document.querySelector('.main-score-orb');
    if (mainOrb) {
        setTimeout(() => {
            mainOrb.classList.add('loaded');
        }, 100);
    }
});
</script> 
