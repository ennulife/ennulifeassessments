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

<div class="ennu-unified-container" data-theme="light">


	<!-- Theme Toggle Removed - Light Mode Only -->

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

			<!-- Assessment Score Presentation -->
			<?php
			// Get assessment-specific score data
			$assessment_score_data = array(
				'type'             => 'assessment',
				'assessment_type'  => $assessment_type,
				'title'            => $assessment_title . ' Score',
				'score'            => $score,
				'interpretation'   => array(
					'level'       => $score >= 8.5 ? 'Excellent' : ( $score >= 7.0 ? 'Good' : ( $score >= 5.5 ? 'Fair' : 'Needs Improvement' ) ),
					'color'       => $score >= 8.5 ? '#10b981' : ( $score >= 7.0 ? '#3b82f6' : ( $score >= 5.5 ? '#f59e0b' : '#ef4444' ) ),
					'description' => $score >= 8.5 ? 'Outstanding performance! You\'re in the top tier.' : ( $score >= 7.0 ? 'Good performance with room for improvement.' : ( $score >= 5.5 ? 'Fair performance. Consider optimization strategies.' : 'Focus on improvement strategies for better results.' ) ),
					'class'       => $score >= 8.5 ? 'excellent' : ( $score >= 7.0 ? 'good' : ( $score >= 5.5 ? 'fair' : 'needs-improvement' ) ),
				),
				'pillar_scores'    => $category_scores,
				'history'          => get_user_meta( $user_id, 'ennu_' . $assessment_type . '_historical_scores', true ) ?: array(),
				'last_updated'     => get_user_meta( $user_id, 'ennu_' . $assessment_type . '_last_updated', true ),
			);
			?>
			
			<?php echo do_shortcode( '[ennu_score_presentation type="' . $assessment_type . '" show_pillars="true" show_history="true" show_interpretation="true" animation="true" size="large"]' ); ?>

			<!-- ENNU Life Score Presentation -->
			<?php
			$ennu_life_score = get_user_meta( $user_id, 'ennu_life_score', true );
			$ennu_life_score = is_numeric( $ennu_life_score ) ? $ennu_life_score : 0;
			?>
			
			<?php echo do_shortcode( '[ennu_score_presentation type="lifescore" show_pillars="true" show_history="false" show_interpretation="true" animation="true" size="medium"]' ); ?>

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

<!-- Theme system is now handled by the centralized ENNUThemeManager --> 
