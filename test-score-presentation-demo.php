<?php
/**
 * Score Presentation Demo Page
 *
 * Demonstrates the new score presentation functionality
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add admin menu item for demo
add_action( 'admin_menu', function() {
	add_submenu_page(
		'tools.php',
		'Score Presentation Demo',
		'Score Demo',
		'manage_options',
		'score-presentation-demo',
		'render_score_presentation_demo'
	);
} );

function render_score_presentation_demo() {
	?>
	<div class="wrap">
		<h1>Score Presentation Demo</h1>
		<p>This page demonstrates the new score presentation functionality with beautiful, interactive score displays.</p>
		
		<h2>ENNU Life Score Presentation</h2>
		<?php echo do_shortcode( '[ennu_score_presentation type="lifescore" show_pillars="true" show_history="true" show_interpretation="true" animation="true" size="large"]' ); ?>
		
		<h2>Individual Pillar Scores</h2>
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">
			<?php echo do_shortcode( '[ennu_score_presentation type="pmind" show_pillars="false" show_history="false" show_interpretation="true" animation="true" size="medium"]' ); ?>
			<?php echo do_shortcode( '[ennu_score_presentation type="pbody" show_pillars="false" show_history="false" show_interpretation="true" animation="true" size="medium"]' ); ?>
			<?php echo do_shortcode( '[ennu_score_presentation type="plifestyle" show_pillars="false" show_history="false" show_interpretation="true" animation="true" size="medium"]' ); ?>
			<?php echo do_shortcode( '[ennu_score_presentation type="paesthetics" show_pillars="false" show_history="false" show_interpretation="true" animation="true" size="medium"]' ); ?>
		</div>
		
		<h2>Assessment Score Presentations</h2>
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin: 20px 0;">
			<?php echo do_shortcode( '[ennu_score_presentation type="weight_loss_assessment" show_pillars="true" show_history="true" show_interpretation="true" animation="true" size="medium"]' ); ?>
			<?php echo do_shortcode( '[ennu_score_presentation type="sleep_assessment" show_pillars="true" show_history="true" show_interpretation="true" animation="true" size="medium"]' ); ?>
		</div>
		
		<h2>Small Size Demo</h2>
		<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;">
			<?php echo do_shortcode( '[ennu_score_presentation type="pmind" show_pillars="false" show_history="false" show_interpretation="true" animation="true" size="small"]' ); ?>
			<?php echo do_shortcode( '[ennu_score_presentation type="pbody" show_pillars="false" show_history="false" show_interpretation="true" animation="true" size="small"]' ); ?>
			<?php echo do_shortcode( '[ennu_score_presentation type="plifestyle" show_pillars="false" show_history="false" show_interpretation="true" animation="true" size="small"]' ); ?>
			<?php echo do_shortcode( '[ennu_score_presentation type="paesthetics" show_pillars="false" show_history="false" show_interpretation="true" animation="true" size="small"]' ); ?>
		</div>
		
		<h2>Usage Instructions</h2>
		<div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;">
			<h3>Shortcode Usage:</h3>
			<code>[ennu_score_presentation type="lifescore" show_pillars="true" show_history="true" show_interpretation="true" animation="true" size="large"]</code>
			
			<h3>Parameters:</h3>
			<ul>
				<li><strong>type</strong>: "lifescore", "pmind", "pbody", "plifestyle", "paesthetics", or assessment type</li>
				<li><strong>show_pillars</strong>: "true" or "false" - Show pillar breakdown</li>
				<li><strong>show_history</strong>: "true" or "false" - Show score history</li>
				<li><strong>show_interpretation</strong>: "true" or "false" - Show score interpretation</li>
				<li><strong>animation</strong>: "true" or "false" - Enable animations</li>
				<li><strong>size</strong>: "small", "medium", or "large" - Size of the presentation</li>
			</ul>
			
			<h3>Examples:</h3>
			<ul>
				<li><code>[ennu_score_presentation type="lifescore"]</code> - Main life score</li>
				<li><code>[ennu_score_presentation type="pmind" size="small"]</code> - Mind pillar score (small)</li>
				<li><code>[ennu_score_presentation type="weight_loss_assessment" show_history="true"]</code> - Weight loss assessment with history</li>
			</ul>
		</div>
	</div>
	<?php
} 