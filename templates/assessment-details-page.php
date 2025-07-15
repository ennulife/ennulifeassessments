<?php
/**
 * Template for the "Health Dossier" - a hyper-personalized, stunning results page.
 *
 * This template is now a "dumb" component. All data fetching and processing
 * is handled in the `render_detailed_results_page` method in the
 * `ENNU_Assessment_Shortcodes` class.
 *
 * @version 52.0.0
 * @see ENNU_Assessment_Shortcodes::render_detailed_results_page()
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ennu-user-dashboard">
	<div class="starfield"></div>
	<div class="dossier-grid">
		<aside class="dossier-sidebar">
			<div class="user-info-header">
				<h2><?php echo esc_html( $current_user->first_name . ' ' . $current_user->last_name ); ?></h2>
				<div class="user-vitals">
					<span>Age: <?php echo esc_html( $age ); ?></span>
					<span>Gender: <?php echo esc_html( $gender ); ?></span>
				</div>
			</div>

			<div class="main-score-orb" data-score="<?php echo esc_attr( $score ?? 0 ); ?>" style="margin-bottom: 20px;">
				<svg class="pillar-orb-progress" viewBox="0 0 36 36">
					<circle class="pillar-orb-progress-bg" cx="18" cy="18" r="15.9155"></circle>
					<circle class="pillar-orb-progress-bar" cx="18" cy="18" r="15.9155" style="--score-percent: <?php echo esc_attr( ( $score ?? 0 ) * 10 ); ?>;"></circle>
				</svg>
				<div class="main-score-text">
					<div class="main-score-value">0.0</div>
					<div class="main-score-label"><?php echo esc_html( ucwords( str_replace( '_', ' ', $assessment_type_slug ) ) ); ?> Score</div>
				</div>
			</div>

			<div class="pillar-scores-grid" style="padding: 20px 0; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
				<?php
				if ( is_array( $pillar_scores ) ) {
					foreach ( $pillar_scores as $pillar => $pillar_score ) {
						$has_data     = ! empty( $pillar_score );
						$pillar_class = esc_attr( strtolower( $pillar ) );
						?>
						<div class="pillar-orb <?php echo $pillar_class; ?> <?php echo $has_data ? '' : 'no-data'; ?>" data-insight="<?php echo esc_attr( $insights['pillars'][ $pillar ] ?? '' ); ?>" style="width: 70px; height: 70px;">
							<div class="pillar-orb-content">
								<div class="pillar-orb-label" style="font-size: 0.7rem;"><?php echo esc_html( $pillar ); ?></div>
								<div class="pillar-orb-score" style="font-size: 1.1rem;"><?php echo $has_data ? esc_html( number_format( $pillar_score, 1 ) ) : '-'; ?></div>
							</div>
						</div>
						<?php
					}
				}
				?>
			</div>
			<div class="pillar-context-display"></div>

			<div class="assessment-actions-footer" style="padding-top: 20px; display: flex; flex-direction: column; gap: 10px;">
				<a href="<?php echo esc_url( home_url( '/call' ) ); ?>" class="action-button button-report" style="flex: 1; text-align: center; background-color: var(--accent-primary); color: #fff; padding: 10px; text-decoration: none; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease; font-size: 0.9rem;">Schedule a Call</a>
				<a href="<?php echo esc_url( home_url( '/welcome' ) ); ?>" class="action-button button-retake" style="flex: 1; text-align: center; background-color: var(--card-bg); color: var(--text-light); border: 1px solid var(--border-color); padding: 10px; text-decoration: none; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease; font-size: 0.9rem;">Get Your ENNU Life Score</a>
				<hr style="border-color: var(--border-color); width: 50%; margin: 10px auto;">
				<a href="<?php echo esc_url( $dashboard_url ); ?>" class="action-button button-retake" style="flex: 1; text-align: center; background-color: var(--card-bg); color: var(--text-light); border: 1px solid var(--border-color); padding: 10px; text-decoration: none; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease; font-size: 0.9rem;">View My Dashboard</a>
				<a href="<?php echo esc_url( $retake_url ); ?>" class="action-button button-retake" style="flex: 1; text-align: center; background-color: var(--card-bg); color: var(--text-light); border: 1px solid var(--border-color); padding: 10px; text-decoration: none; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease; font-size: 0.9rem;">Retake Assessment</a>
			</div>
		</aside>

		<main class="dossier-main-content">
			<div class="dossier-header">
				<h1>Your <?php echo esc_html( ucwords( str_replace( '_', ' ', $assessment_type_slug ) ) ); ?> Dossier</h1>
				<p class="ai-narrative">
					<strong><?php echo esc_html( $result_content['title'] ); ?>:</strong>
					<?php echo esc_html( $result_content['summary'] ); ?>
				</p>
			</div>

			<div class="journey-timeline-card" style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; margin-bottom: 25px;">
				<h3 style="font-size: 16px; font-weight: 600; color: #fff; margin: 0 0 15px 0;">Progress Over Time</h3>
				<div class="timeline-chart-container" style="height: 250px;">
					<canvas id="assessmentTimelineChart"></canvas>
				</div>
			</div>

			<div class="category-card recommendations-card" style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; margin-bottom: 25px;">
				<h3 style="font-size: 16px; font-weight: 600; color: #fff; margin: 0 0 15px 0;">Personalized Recommendations</h3>
				<ul style="list-style: none; padding: 0; margin: 0; color: var(--text-light);">
					<?php foreach ( $result_content['recommendations'] as $rec ) : ?>
						<li style="margin-bottom: 10px; padding-left: 20px; position: relative;">
							<span style="position: absolute; left: 0; top: 5px; color: var(--accent-primary);">✓</span>
							<?php echo esc_html( $rec ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div class="deep-dive-grid">
				<?php foreach ( $category_scores as $category => $cat_score ) : ?>
					<div class="category-card" style="--score: <?php echo esc_attr( $cat_score ); ?>">
						<div class="category-info">
							<span class="category-name"><?php echo esc_html( $category ); ?></span>
							<span class="category-score-value"><?php echo esc_html( number_format( $cat_score, 1 ) ); ?> / 10</span>
						</div>
						<div class="category-bar-bg">
							<div class="bar-fill"></div>
						</div>
						<div class="category-explanation">
							<p><?php echo esc_html( $deep_dive_content[ $category ]['explanation'] ?? '' ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</main>
	</div>
</div> 
