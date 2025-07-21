<?php
/**
 * Template for displaying the "expired" or "empty" state for an assessment results page.
 * This template ensures a consistent, high-quality user experience even for edge cases.
 *
 * @version 54.0.0
 * @see ENNU_Assessment_Shortcodes::render_thank_you_page()
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// All data is passed in via the $data variable, extracted by ennu_load_template().
$dashboard_url = ennu_life()->get_shortcodes()->get_dashboard_url();
$retake_url    = isset( $assessment_type ) ? ennu_life()->get_shortcodes()->get_assessment_page_url( $assessment_type ) : $dashboard_url;
?>

<div class="ennu-user-dashboard"> <!-- Use the main dashboard class for consistent styling -->
	<div class="starfield"></div>
	<div class="dossier-grid" style="grid-template-columns: 1fr; max-width: 600px; margin: auto;">
		<main class="dossier-main-content">
			<?php
			if ( function_exists( 'ennu_render_logo' ) ) {
				echo '<div class="ennu-logo-container">';
				ennu_render_logo(
					array(
						'color' => 'white',
						'size'  => 'medium',
						'link'  => home_url( '/' ),
						'alt'   => 'ENNU Life',
						'class' => '',
					)
				);
				echo '</div>';
			}
			?>
			<div class="ennu-results-empty-state" style="text-align: center; background-color: var(--card-bg); border: 1px solid var(--border-color); border-radius: var(--rounded-lg); padding: var(--spacing-6);">
				<div class="empty-state-icon" style="font-size: 40px; font-weight: bold; color: #ffc107; background: rgba(255, 193, 7, 0.1); width: 80px; height: 80px; line-height: 80px; border-radius: 50%; margin: 0 auto 20px; border: 1px solid #ffc107;">!</div>
				<h2 class="empty-state-title" style="font-size: 28px; font-weight: 700; color: #fff; margin-bottom: 15px;">
					<?php echo esc_html__( 'Results Processed', 'ennulifeassessments' ); ?>
				</h2>
				<p class="empty-state-message" style="font-size: 16px; color: var(--text-light); line-height: 1.6; margin-bottom: 30px;">
					<?php echo wp_kses_post( __( 'This one-time results link has expired. Your complete assessment history is permanently saved to your private dashboard.', 'ennulifeassessments' ) ); ?>
				</p>
				<div class="empty-state-actions" style="display: flex; justify-content: center; gap: 15px;">
					<a href="<?php echo esc_url( $dashboard_url ); ?>" class="action-button button-report" style="text-align: center; background-color: var(--accent-primary); color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease; font-size: 0.9rem;"><?php echo esc_html__( 'View My Dashboard', 'ennulifeassessments' ); ?></a>
					<a href="<?php echo esc_url( $retake_url ); ?>" class="action-button button-retake" style="text-align: center; background-color: var(--card-bg); color: var(--text-light); border: 1px solid var(--border-color); padding: 10px 20px; text-decoration: none; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease; font-size: 0.9rem;"><?php echo esc_html__( 'Take Assessment Again', 'ennulifeassessments' ); ?></a>
				</div>
			</div>
		</main>
	</div>
</div> 
