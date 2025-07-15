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
	<div class="dashboard-logged-out-container" style="max-width: 600px; margin: 100px auto; text-align: center; padding: 40px; background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color);">
		<h1 style="color: #fff; font-size: 2.5rem; margin-bottom: 20px;">Access Your Health Dashboard</h1>
		<p style="color: var(--text-light); font-size: 1.1rem; margin-bottom: 30px;">
			Please log in to view your personalized health assessments, track your progress, and access your ENNU LIFE SCORE.
		</p>
		
		<div class="auth-buttons" style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
			<a href="<?php echo esc_url( $login_url ); ?>" 
			   class="action-button button-report" 
			   style="background-color: var(--accent-primary); color: #fff; padding: 15px 30px; text-decoration: none; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease; font-size: 1rem;">
				Log In
			</a>
			<a href="<?php echo esc_url( $registration_url ); ?>" 
			   class="action-button button-retake" 
			   style="background-color: var(--card-bg); color: var(--text-light); border: 1px solid var(--border-color); padding: 15px 30px; text-decoration: none; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease; font-size: 1rem;">
				Create Account
			</a>
		</div>
		
		<div style="margin-top: 40px; padding-top: 30px; border-top: 1px solid var(--border-color);">
			<p style="color: var(--text-light); font-size: 0.9rem;">
				New to ENNU Life? Start your health journey today with our comprehensive assessments.
			</p>
			<a href="<?php echo esc_url( home_url( '/welcome' ) ); ?>" 
			   style="color: var(--accent-primary); text-decoration: none; font-weight: 600;">
				Learn More →
			</a>
		</div>
	</div>
</div> 