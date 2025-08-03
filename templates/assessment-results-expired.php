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
// Use the shortcode instance passed in the data array
if ( ! isset( $shortcode_instance ) ) {
	// Fallback to getting the assessment shortcodes instance from the shortcode manager
	$shortcode_manager = ennu_life()->get_shortcodes();
	if ( $shortcode_manager && method_exists( $shortcode_manager, 'get_renderer' ) ) {
		$shortcode_instance = $shortcode_manager->get_renderer();
	}
	
	// If still not available, create a new instance
	if ( ! $shortcode_instance && class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
		$shortcode_instance = new ENNU_Assessment_Shortcodes();
	}
}

// Generate URLs using the shortcode instance
$dashboard_url = '#';
$retake_url = '#';
$home_url = '#';

if ( class_exists( 'ENNU_UI_Constants' ) ) {
	$dashboard_url = '?' . ENNU_UI_Constants::get_page_type('DASHBOARD');
	$retake_url    = isset( $assessment_type ) 
		? '?' . ENNU_UI_Constants::get_page_type('ASSESSMENTS')
		: $dashboard_url;
	$home_url = '?' . ENNU_UI_Constants::get_page_type('DASHBOARD');
}
?>

<div class="ennu-user-dashboard"> <!-- Use the main dashboard class for consistent styling -->

	<div class="dossier-grid" style="grid-template-columns: 1fr; max-width: 600px; margin: auto;">
		<main class="dossier-main-content">
			<?php
			if ( function_exists( 'ennu_render_logo' ) ) {
				echo '<div class="ennu-logo-container">';
				ennu_render_logo(
					array(
						'color' => 'white',
						'size'  => 'medium',
						'link'  => $home_url,
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
