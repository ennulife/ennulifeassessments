<?php
/**
 * Template for the user dashboard when not logged in
 *
 * @package ENNU_Life
 * @version 58.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ennu-user-dashboard">
	<div class="starfield"></div>
	<?php
	if ( function_exists( 'ennu_render_logo' ) ) {
		echo '<div class="ennu-logo-container">';
		ennu_render_logo([
			'color' => 'white',
			'size' => 'medium',
			'link' => home_url( '/' ),
			'alt' => 'ENNU Life',
			'class' => ''
		]);
		echo '</div>';
	}
	?>
	<div class="dashboard-logged-out-container" style="max-width: 600px; margin: 100px auto; text-align: center; padding: 40px; background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color);">
		<h1 style="color: #fff; font-size: 2.5rem; margin-bottom: 20px;">Access Your Health Dashboard</h1>
		<p style="color: var(--text-light); font-size: 1.1rem; margin-bottom: 30px;">
			Please log in to view your personalized health assessments, track your progress, and access your ENNU LIFE SCORE.
		</p>
		
		<div class="dashboard-actions">
			<a href="<?php echo esc_url( $login_url ); ?>" class="action-button button-primary">Log In</a>
			<a href="<?php echo esc_url( $registration_url ); ?>" class="action-button button-secondary">Create Account</a>
		</div>
		
		<div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid var(--border-color);">
			<p style="color: var(--text-light); font-size: 0.9rem;">
				New to ENNU Life? Start your health journey today with our comprehensive assessments.
			</p>
			<a href="<?php echo esc_url( home_url( '/?page_id=190' ) ); ?>" 
			   style="color: var(--accent-primary); text-decoration: none; font-weight: 600;">
				Learn More →
			</a>
		</div>
	</div>
</div> 