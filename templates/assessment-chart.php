<?php
/**
 * Template for displaying the assessment radar chart.
 *
 * This template is loaded by the [ennu-assessment-chart] shortcode.
 * It is now a "dumb" component; all data is fetched and processed
 * in the `render_chart_page` method.
 *
 * @version 2.0.0
 * @see ENNU_Assessment_Shortcodes::render_chart_page()
 *
 * @var string $assessment_title The title of the assessment.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ennu-chart-container">
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
	<h2><?php echo esc_html( $assessment_title ); ?> - Score Breakdown</h2>
	<p>This chart provides a visual representation of your scores across the different health categories.</p>
	<div style="max-width: 600px; margin: 0 auto;">
		<canvas id="ennuRadarChart"></canvas>
	</div>
</div> 
