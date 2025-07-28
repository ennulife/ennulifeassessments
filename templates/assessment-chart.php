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

// Get the shortcode instance for proper URL generation
$shortcode_instance = ennu_life()->get_shortcodes();
$home_page_url = $shortcode_instance->get_page_id_url('dashboard');
?>

<div class="ennu-chart-container">
	<?php
	if ( function_exists( 'ennu_render_logo' ) ) {
		echo '<div class="ennu-logo-container">';
		ennu_render_logo(
			array(
				'color' => 'white',
				'size'  => 'medium',
				'link'  => $home_page_url,
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
