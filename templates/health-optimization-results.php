<?php
/**
 * Template for the Health Optimization (Symptom Qualification) Results Page
 *
 * This template displays a non-scored, text-based report that outlines the user's
 * triggered Health Optimization Vectors and provides clear next steps.
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="ennu-user-dashboard">
	<div class="starfield"></div>
	<div class="dossier-grid" style="grid-template-columns: 1fr; max-width: 800px; margin: auto;">
		<main class="dossier-main-content">
			<div class="dossier-header" style="text-align: center; margin-bottom: 40px;">
				<h1 style="font-size: 2.5rem; color: #fff; font-weight: 700;">Your Symptom Analysis is Complete</h1>
				<p class="ai-narrative" style="font-size: 1.1rem; color: var(--text-light); max-width: 650px; margin: 15px auto 0;">
					Based on the symptoms you've reported, we've identified the following key areas of your health that may require further investigation. This report will guide you on what to discuss with a healthcare professional or investigate further with biomarker testing.
				</p>
			</div>

			<?php if ( ! empty( $triggered_vectors ) ) : ?>
				<div class="category-card recommendations-card" style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; margin-bottom: 25px;">
					<h3 style="font-size: 1.25rem; font-weight: 600; color: #fff; margin: 0 0 20px 0; border-bottom: 1px solid var(--border-color); padding-bottom: 15px;">Triggered Health Optimization Vectors</h3>
					<ul style="list-style: none; padding: 0; margin: 0; color: var(--text-light);">
						<?php foreach ( $triggered_vectors as $vector => $symptoms ) : ?>
							<li style="margin-bottom: 15px; padding-left: 25px; position: relative;">
								<span style="position: absolute; left: 0; top: 5px; color: var(--accent-primary); font-size: 1.2rem;">→</span>
								<strong style="color: var(--text-dark);"><?php echo esc_html( $vector ); ?>:</strong> Was triggered by your responses for "<?php echo esc_html( implode( '", "', $symptoms ) ); ?>".
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php else : ?>
				<div class="category-card" style="text-align: center;">
					<h3 style="font-size: 1.25rem; font-weight: 600; color: #fff; margin: 0 0 20px 0;">No Major Vectors Triggered</h3>
					<p style="font-size: 1rem; color: var(--text-light);">Based on your responses, you haven't indicated any major symptom patterns that align with our Health Optimization Vectors. This is a great sign!</p>
				</div>
			<?php endif; ?>

			<div class="category-card cta-card" style="background: linear-gradient(145deg, var(--accent-secondary), var(--accent-primary)); border-radius: 12px; padding: 30px; margin-top: 30px; text-align: center;">
				<h3 style="font-size: 1.5rem; font-weight: 700; color: #fff; margin: 0 0 15px 0;">The Next Step: See The Full Picture</h3>
				<p style="font-size: 1rem; color: rgba(255,255,255,0.9); max-width: 600px; margin: 0 auto 25px;">
					Symptoms are subjective. To get the objective truth about what's happening inside your body, you need to test, not guess. Our comprehensive biomarker lab tests provide the definitive data you need to optimize your health.
				</p>
				<div class="cta-actions">
					<a href="<?php echo esc_url( home_url( '/?page_id=810' ) ); ?>" class="action-button" style="background-color: #fff; color: #000; padding: 12px 30px; text-decoration: none; border-radius: 50px; font-weight: 700; transition: all 0.2s ease;">Purchase Biomarker Test ($599)</a>
					<a href="<?php echo esc_url( home_url( '/?page_id=811' ) ); ?>" class="action-button" style="background-color: transparent; border: 2px solid #fff; color: #fff; padding: 12px 30px; text-decoration: none; border-radius: 50px; font-weight: 700; transition: all 0.2s ease; margin-left: 15px;">Become a Member ($149/mo)</a>
				</div>
			</div>

			<div class="assessment-actions-footer" style="padding-top: 25px; display: flex; flex-direction: column; gap: 15px; align-items: center; margin-top: 20px;">
				<p style="color: var(--text-light);">Or, you can take another assessment:</p>
				<a href="<?php echo esc_url( home_url( '/?page_id=182' ) ); ?>" class="action-button button-retake" style="background-color: var(--card-bg); color: var(--text-light); border: 1px solid var(--border-color); padding: 10px 30px; text-decoration: none; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease; font-size: 0.9rem;">Return to My Dashboard</a>
			</div>
		</main>
	</div>
</div> 