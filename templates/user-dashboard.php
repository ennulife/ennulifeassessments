<?php
/**
 * Template for the user assessment dashboard - "The Bio-Metric Canvas"
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if (isset($template_args) && is_array($template_args)) { extract($template_args, EXTR_SKIP); }

// Defensive checks for required variables
if (!isset($current_user) || !is_object($current_user)) {
	echo '<div style="color: red; background: #fff3f3; padding: 20px; border: 2px solid #f00;">' . esc_html__('ERROR: $current_user is not set or not an object.', 'ennulifeassessments') . '</div>';
	return;
}
$age = $age ?? '';
$gender = $gender ?? '';
$height = $height ?? null;
$weight = $weight ?? null;
$bmi = $bmi ?? null;
$user_assessments = $user_assessments ?? array();
$insights = $insights ?? array();
$health_optimization_report = $health_optimization_report ?? array();
$shortcode_instance = $shortcode_instance ?? null;
if (!$shortcode_instance) {
	echo '<div style="color: red; background: #fff3f3; padding: 20px; border: 2px solid #f00;">' . esc_html__('ERROR: $shortcode_instance is not set.', 'ennulifeassessments') . '</div>';
	return;
}

// Define user ID and display name
$user_id = $current_user->ID ?? 0;
$first_name = isset($current_user->first_name) ? $current_user->first_name : '';
$last_name = isset($current_user->last_name) ? $current_user->last_name : '';
$display_name = trim($first_name . ' ' . $last_name);
if (empty($display_name)) {
	$display_name = $current_user->display_name ?? $current_user->user_login ?? 'User';
}
?>
<div class="ennu-user-dashboard">
	<!-- Four-Engine Scoring Breakdown -->
	<div class="ennu-scoring-engines" style="margin-bottom: 30px; padding: 20px; background: rgba(255,255,255,0.1); border-radius: 12px;">
		<h3 style="margin-bottom: 20px; color: var(--text-primary);">Your Four-Engine Scoring Breakdown</h3>
		
		<div class="engine-summary" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
			<div class="engine-card" style="padding: 15px; background: rgba(255,255,255,0.05); border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
				<h4 style="color: var(--accent-color); margin-bottom: 10px;">1. Quantitative Engine</h4>
				<p style="font-size: 14px; margin-bottom: 8px;">Base assessment scores from your completed evaluations</p>
				<?php 
				$base_score = get_user_meta( $user_id, 'ennu_life_score', true );
				echo '<p style="font-weight: bold;">Current Score: ' . esc_html( $base_score ? round( $base_score, 1 ) : 'N/A' ) . '</p>';
				?>
			</div>
			
			<div class="engine-card" style="padding: 15px; background: rgba(255,255,255,0.05); border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
				<h4 style="color: var(--accent-color); margin-bottom: 10px;">2. Qualitative Engine</h4>
				<?php 
				$qualitative_data = get_user_meta( $user_id, 'ennu_qualitative_data', true );
				if ( ! empty( $qualitative_data['penalty_summary'] ) ) {
					$summary = $qualitative_data['penalty_summary'];
					echo '<p style="font-size: 14px;">Symptoms analyzed: ' . esc_html( $summary['total_symptoms'] ) . '</p>';
					if ( ! empty( $summary['pillars_penalized'] ) ) {
						echo '<p style="font-size: 14px;">Pillars affected: ' . esc_html( implode( ', ', $summary['pillars_penalized'] ) ) . '</p>';
					}
					echo '<p style="font-size: 12px; color: var(--text-secondary);">' . esc_html( $qualitative_data['user_explanation'] ?? '' ) . '</p>';
				} else {
					echo '<p style="font-size: 14px;">No symptom penalties applied</p>';
					echo '<p><a href="#" class="ennu-btn" style="font-size: 12px; padding: 5px 10px;">Complete Health Assessment</a></p>';
				}
				?>
			</div>
			
			<div class="engine-card" style="padding: 15px; background: rgba(255,255,255,0.05); border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
				<h4 style="color: var(--accent-color); margin-bottom: 10px;">3. Objective Engine</h4>
				<?php 
				$objective_data = get_user_meta( $user_id, 'ennu_objective_data', true );
				if ( ! empty( $objective_data['adjustment_summary'] ) ) {
					$summary = $objective_data['adjustment_summary'];
					echo '<p style="font-size: 14px;">Biomarkers analyzed: ' . esc_html( $summary['total_biomarkers'] ) . '</p>';
					echo '<p style="font-size: 14px;">Adjustments applied: ' . esc_html( $summary['adjustments_applied'] ) . '</p>';
					echo '<p style="font-size: 12px; color: var(--text-secondary);">' . esc_html( $objective_data['user_explanation'] ?? '' ) . '</p>';
				} else {
					echo '<p style="font-size: 14px;">No biomarker data available</p>';
					echo '<p><a href="#" class="ennu-btn" style="font-size: 12px; padding: 5px 10px;">Upload Lab Results</a></p>';
				}
				?>
			</div>
			
			<div class="engine-card" style="padding: 15px; background: rgba(255,255,255,0.05); border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
				<h4 style="color: var(--accent-color); margin-bottom: 10px;">4. Intentionality Engine</h4>
				<?php 
				$intentionality_data = get_user_meta( $user_id, 'ennu_intentionality_data', true );
				if ( ! empty( $intentionality_data['boost_summary'] ) ) {
					$summary = $intentionality_data['boost_summary'];
					echo '<p style="font-size: 14px;">Goals aligned: ' . esc_html( $summary['total_goals'] ) . '</p>';
					echo '<p style="font-size: 14px;">Boosts applied: ' . esc_html( $summary['boosts_applied'] ) . '</p>';
					echo '<p style="font-size: 12px; color: var(--text-secondary);">' . esc_html( $intentionality_data['user_explanation'] ?? '' ) . '</p>';
				} else {
					echo '<p style="font-size: 14px;">No goal alignment boosts</p>';
					echo '<p><a href="#" class="ennu-btn" style="font-size: 12px; padding: 5px 10px;">Set Health Goals</a></p>';
				}
				?>
			</div>
		</div>
	</div>

	<!-- Goal Boost Explanation Section -->
	<div class="goal-boost-explanation" style="margin-bottom: 30px; padding: 25px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.1)); border-radius: 16px; border: 1px solid rgba(59, 130, 246, 0.2);">
		<div class="explanation-header" style="display: flex; align-items: center; margin-bottom: 20px;">
			<div class="explanation-icon" style="width: 50px; height: 50px; background: linear-gradient(135deg, #3b82f6, #2563eb); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24" style="color: white;">
					<path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
				</svg>
			</div>
			<div class="explanation-title">
				<h3 style="margin: 0; color: var(--text-primary); font-size: 20px; font-weight: 600;">How Goal Boosts Work</h3>
				<p style="margin: 5px 0 0 0; color: var(--text-secondary); font-size: 14px;">Understanding your scoring system and goal alignment</p>
			</div>
		</div>
		
		<div class="explanation-content" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
			<div class="explanation-card" style="padding: 20px; background: rgba(255,255,255,0.05); border-radius: 12px;">
				<h4 style="color: var(--accent-color); margin-bottom: 15px; font-size: 16px;">🎯 Goal Boost Application</h4>
				<p style="font-size: 14px; line-height: 1.6; color: var(--text-secondary); margin-bottom: 10px;">
					<strong>Goal boosts are applied to your CURRENT scores</strong>, not your New Life scores. When you select health goals, the Intentionality Engine (4th engine) applies percentage boosts to specific health pillars.
				</p>
				<div style="background: rgba(16, 185, 129, 0.1); padding: 12px; border-radius: 8px; border-left: 4px solid #10b981;">
					<p style="font-size: 13px; margin: 0; color: var(--text-primary);">
						<strong>Example:</strong> "Weight Loss" goal gives +15% boost to Body pillar and +10% boost to Lifestyle pillar
					</p>
				</div>
			</div>
			
			<div class="explanation-card" style="padding: 20px; background: rgba(255,255,255,0.05); border-radius: 12px;">
				<h4 style="color: var(--accent-color); margin-bottom: 15px; font-size: 16px;">📊 Scoring Impact</h4>
				<p style="font-size: 14px; line-height: 1.6; color: var(--text-secondary); margin-bottom: 10px;">
					<strong>Every assessment question with scoring configuration impacts your scores.</strong> Questions without scoring are used for data collection and personalization only.
				</p>
				<div style="background: rgba(59, 130, 246, 0.1); padding: 12px; border-radius: 8px; border-left: 4px solid #3b82f6;">
					<p style="font-size: 13px; margin: 0; color: var(--text-primary);">
						<strong>Scoring happens:</strong> Immediately after assessment completion, with real-time updates via AJAX
					</p>
				</div>
			</div>
			
			<div class="explanation-card" style="padding: 20px; background: rgba(255,255,255,0.05); border-radius: 12px;">
				<h4 style="color: var(--accent-color); margin-bottom: 15px; font-size: 16px;">🚀 New Life Score Calculation</h4>
				<p style="font-size: 14px; line-height: 1.6; color: var(--text-secondary); margin-bottom: 10px;">
					<strong>New Life scores are aspirational</strong> - they show your potential health transformation based on doctor-recommended biomarker targets and your selected health goals.
				</p>
				<div style="background: rgba(168, 85, 247, 0.1); padding: 12px; border-radius: 8px; border-left: 4px solid #a855f7;">
					<p style="font-size: 13px; margin: 0; color: var(--text-primary);">
						<strong>Formula:</strong> Current Scores + Doctor Targets + Goal Boosts = New Life Score
					</p>
				</div>
			</div>
		</div>
	</div>

	<!-- Light/Dark Mode Toggle -->
	<div class="theme-toggle-container">
		<button class="theme-toggle" id="theme-toggle" aria-label="Toggle light/dark mode">
			<div class="toggle-track">
				<div class="toggle-thumb">
					<svg class="toggle-icon sun-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<circle cx="12" cy="12" r="5"/>
						<line x1="12" y1="1" x2="12" y2="3"/>
						<line x1="12" y1="21" x2="12" y2="23"/>
						<line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
						<line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
						<line x1="1" y1="12" x2="3" y2="12"/>
						<line x1="21" y1="12" x2="23" y2="12"/>
						<line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
						<line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
					</svg>
					<svg class="toggle-icon moon-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
						<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
					</svg>
				</div>
			</div>
		</button>
	</div>

	<div class="starfield"></div>



	<div class="dashboard-main-grid ennu-logo-pattern-bg">
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

		<main class="dashboard-main-content">
			<!-- Journey Start Date Section -->
			<div class="journey-start-section" style="margin-bottom: 30px; padding: 25px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1)); border-radius: 16px; border: 1px solid rgba(16, 185, 129, 0.2);">
				<div class="journey-header" style="display: flex; align-items: center; margin-bottom: 20px;">
					<div class="journey-icon" style="width: 50px; height: 50px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24" style="color: white;">
							<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
						</svg>
					</div>
					<div class="journey-title">
						<h2 style="margin: 0; color: var(--text-primary); font-size: 24px; font-weight: 600;">Your Health Journey</h2>
						<p style="margin: 5px 0 0 0; color: var(--text-secondary); font-size: 16px;">Started on your path to optimal health</p>
					</div>
				</div>
				
				<div class="journey-details" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
					<?php
					// Get journey start date (user registration or first assessment)
					$user_registered = $current_user->user_registered ?? '';
					$first_assessment = get_user_meta($user_id, 'ennu_first_assessment_completed', true);
					$journey_start_date = $first_assessment ?: $user_registered;
					$journey_start_formatted = $journey_start_date ? date('F j, Y', strtotime($journey_start_date)) : 'Recently';
					
					// Calculate journey duration
					$journey_days = $journey_start_date ? floor((time() - strtotime($journey_start_date)) / (60 * 60 * 24)) : 0;
					$journey_duration = $journey_days > 0 ? $journey_days . ' days' : 'Just started';
					
					// Get assessment completion stats
					$completed_assessments = 0;
					$total_assessments = 11; // Total available assessments
					$assessment_types = array('welcome', 'hair', 'health', 'skin', 'sleep', 'hormone', 'menopause', 'testosterone', 'weight_loss', 'ed_treatment', 'health_optimization');
					
					foreach ($assessment_types as $type) {
						$score = get_user_meta($user_id, 'ennu_' . $type . '_calculated_score', true);
						if (!empty($score)) {
							$completed_assessments++;
						}
					}
					$completion_rate = $total_assessments > 0 ? round(($completed_assessments / $total_assessments) * 100) : 0;
					?>
					
					<div class="journey-stat" style="text-align: center; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 12px;">
						<div class="stat-icon" style="font-size: 24px; margin-bottom: 8px;">📅</div>
						<div class="stat-label" style="font-size: 14px; color: var(--text-secondary); margin-bottom: 5px;">Journey Started</div>
						<div class="stat-value" style="font-size: 18px; font-weight: 600; color: var(--text-primary);"><?php echo esc_html($journey_start_formatted); ?></div>
					</div>
					
					<div class="journey-stat" style="text-align: center; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 12px;">
						<div class="stat-icon" style="font-size: 24px; margin-bottom: 8px;">⏱️</div>
						<div class="stat-label" style="font-size: 14px; color: var(--text-secondary); margin-bottom: 5px;">Journey Duration</div>
						<div class="stat-value" style="font-size: 18px; font-weight: 600; color: var(--text-primary);"><?php echo esc_html($journey_duration); ?></div>
					</div>
					
					<div class="journey-stat" style="text-align: center; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 12px;">
						<div class="stat-icon" style="font-size: 24px; margin-bottom: 8px;">📊</div>
						<div class="stat-label" style="font-size: 14px; color: var(--text-secondary); margin-bottom: 5px;">Assessments Completed</div>
						<div class="stat-value" style="font-size: 18px; font-weight: 600; color: var(--text-primary);"><?php echo esc_html($completed_assessments); ?>/<?php echo esc_html($total_assessments); ?></div>
						<div class="stat-subtitle" style="font-size: 12px; color: var(--accent-color);"><?php echo esc_html($completion_rate); ?>% Complete</div>
					</div>
					
					<div class="journey-stat" style="text-align: center; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 12px;">
						<div class="stat-icon" style="font-size: 24px; margin-bottom: 8px;">🎯</div>
						<div class="stat-label" style="font-size: 14px; color: var(--text-secondary); margin-bottom: 5px;">Health Goals Set</div>
						<?php 
						$health_goals = get_user_meta($user_id, 'ennu_global_health_goals', true);
						$goals_count = is_array($health_goals) ? count($health_goals) : 0;
						?>
						<div class="stat-value" style="font-size: 18px; font-weight: 600; color: var(--text-primary);"><?php echo esc_html($goals_count); ?> Goals</div>
					</div>
				</div>
			</div>

			<!-- Welcome Section -->
			<div class="dashboard-welcome-section">
				<!-- Logo above title -->
				<div class="dashboard-logo-container">
					<img src="<?php echo esc_url(plugins_url('assets/img/ennu-logo-black.png', dirname(__FILE__))); ?>" 
						 alt="ENNU Life Logo" 
						 class="dashboard-logo light-mode-logo">
					<img src="<?php echo esc_url(plugins_url('assets/img/ennu-logo-white.png', dirname(__FILE__))); ?>" 
						 alt="ENNU Life Logo" 
						 class="dashboard-logo dark-mode-logo">
				</div>
				<h1 class="dashboard-title dashboard-title-large"><?php echo esc_html($display_name); ?>'s Biometric Canvas</h1>
				<p class="dashboard-subtitle">Track your progress and discover personalized insights for optimal health.</p>
				
				<!-- Vital Statistics Display -->
				<?php if (!empty($age) || !empty($gender) || !empty($height) || !empty($weight) || !empty($bmi)) : ?>
				<div class="vital-stats-display">
					<?php if (!empty($age)) : ?>
					<div class="vital-stat-item">
						<span class="vital-stat-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
								<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
								<circle cx="12" cy="7" r="4"/>
							</svg>
						</span>
						<span class="vital-stat-value"><?php echo esc_html($age); ?> years</span>
					</div>
					<?php endif; ?>
					<?php if (!empty($gender)) : ?>
					<div class="vital-stat-item">
						<span class="vital-stat-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
								<circle cx="12" cy="12" r="10"/>
								<path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
							</svg>
						</span>
						<span class="vital-stat-value"><?php echo esc_html($gender); ?></span>
					</div>
					<?php endif; ?>
					<?php if (!empty($height)) : ?>
					<div class="vital-stat-item">
						<span class="vital-stat-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
								<path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
								<path d="M3 6h18M3 12h18M3 18h18"/>
							</svg>
						</span>
						<span class="vital-stat-value"><?php echo esc_html($height); ?></span>
					</div>
					<?php endif; ?>
					<?php if (!empty($weight)) : ?>
					<div class="vital-stat-item">
						<span class="vital-stat-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
								<path d="M6 2h12a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/>
								<path d="M12 6v6M8 12h8"/>
							</svg>
						</span>
						<span class="vital-stat-value"><?php echo esc_html($weight); ?></span>
					</div>
					<?php endif; ?>
					<?php if (!empty($bmi)) : ?>
					<div class="vital-stat-item">
						<span class="vital-stat-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
								<path d="M3 3h18v18H3z"/>
								<path d="M9 9h6v6H9z"/>
								<path d="M12 3v18M3 12h18"/>
							</svg>
						</span>
						<span class="vital-stat-value">BMI: <?php echo esc_html($bmi); ?></span>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>

			<!-- Scores Row -->
			<div class="dashboard-scores-row">
				<!-- Scores Title -->
				<div class="scores-title-container">
					<h2 class="scores-title">MY LIFE SCORES</h2>
			</div>

				<!-- Scores Content Grid -->
				<div class="scores-content-grid">
					<!-- Left Pillar Scores -->
					<div class="pillar-scores-left">
						<?php
						if (is_array($average_pillar_scores)) {
							$pillar_count = 0;
							foreach ($average_pillar_scores as $pillar => $score) {
								if ($pillar_count >= 2) break; // Only show first 2 pillars
								$has_data = !empty($score);
								$pillar_class = esc_attr(strtolower($pillar));
								$spin_duration = $has_data ? max(2, 11 - $score) : 10;
								$style_attr = '--spin-duration: ' . $spin_duration . 's;';
								$insight_text = $insights['pillars'][$pillar] ?? '';
								?>
								<div class="pillar-orb <?php echo $pillar_class; ?> <?php echo $has_data ? '' : 'no-data'; ?>" style="<?php echo esc_attr($style_attr); ?>" data-insight="<?php echo esc_attr($insight_text); ?>">
				<svg class="pillar-orb-progress" viewBox="0 0 36 36">
					<circle class="pillar-orb-progress-bg" cx="18" cy="18" r="15.9155"></circle>
										<circle class="pillar-orb-progress-bar" cx="18" cy="18" r="15.9155" style="--score-percent: <?php echo esc_attr($has_data ? $score * 10 : 0); ?>;"></circle>
									</svg>
									<div class="pillar-orb-content">
										<div class="pillar-orb-label"><?php echo esc_html($pillar); ?></div>
										<div class="pillar-orb-score"><?php echo $has_data ? esc_html(number_format($score, 1)) : '-'; ?></div>
									</div>
									<div class="floating-particles"></div>
									<div class="decoration-dots"></div>
								</div>
								<?php
								$pillar_count++;
							}
						}
						?>
					</div>

					<!-- Center ENNU Life Score -->
					<div class="ennu-life-score-center">
						<div class="main-score-orb" data-score="<?php echo esc_attr($ennu_life_score ?? 0); ?>" data-insight="<?php echo esc_attr($insights['ennu_life_score'] ?? ''); ?>">
							<svg class="pillar-orb-progress" viewBox="0 0 36 36">
								<defs>
									<linearGradient id="ennu-score-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
										<stop offset="0%" stop-color="rgba(16, 185, 129, 0.6)"/>
										<stop offset="50%" stop-color="rgba(5, 150, 105, 0.6)"/>
										<stop offset="100%" stop-color="rgba(4, 120, 87, 0.6)"/>
									</linearGradient>
								</defs>
								<circle class="pillar-orb-progress-bg" cx="18" cy="18" r="15.9155"></circle>
								<circle class="pillar-orb-progress-bar" cx="18" cy="18" r="15.9155" style="--score-percent: <?php echo esc_attr(($ennu_life_score ?? 0) * 10); ?>;"></circle>
				</svg>
				<div class="main-score-text">
					<div class="main-score-value">0.0</div>
					<div class="main-score-label">ENNU Life Score</div>
							</div>
							<div class="decoration-dots"></div>
				</div>
			</div>

					<!-- Right Pillar Scores -->
					<div class="pillar-scores-right">
				<?php
						if (is_array($average_pillar_scores)) {
							$pillar_count = 0;
							$total_pillars = count($average_pillar_scores);
							foreach ($average_pillar_scores as $pillar => $score) {
								if ($pillar_count < 2) { // Skip first 2 pillars
									$pillar_count++;
									continue;
								}
								if ($pillar_count >= 4) break; // Only show next 2 pillars
								$has_data = !empty($score);
								$pillar_class = esc_attr(strtolower($pillar));
								$spin_duration = $has_data ? max(2, 11 - $score) : 10;
								$style_attr = '--spin-duration: ' . $spin_duration . 's;';
								$insight_text = $insights['pillars'][$pillar] ?? '';
						?>
								<div class="pillar-orb <?php echo $pillar_class; ?> <?php echo $has_data ? '' : 'no-data'; ?>" style="<?php echo esc_attr($style_attr); ?>" data-insight="<?php echo esc_attr($insight_text); ?>">
							<svg class="pillar-orb-progress" viewBox="0 0 36 36">
								<circle class="pillar-orb-progress-bg" cx="18" cy="18" r="15.9155"></circle>
										<circle class="pillar-orb-progress-bar" cx="18" cy="18" r="15.9155" style="--score-percent: <?php echo esc_attr($has_data ? $score * 10 : 0); ?>;"></circle>
							</svg>
							<div class="pillar-orb-content">
										<div class="pillar-orb-label"><?php echo esc_html($pillar); ?></div>
										<div class="pillar-orb-score"><?php echo $has_data ? esc_html(number_format($score, 1)) : '-'; ?></div>
									</div>
									<div class="floating-particles"></div>
									<div class="decoration-dots"></div>
								</div>
								<?php
								$pillar_count++;
							}
						}
						?>
					</div>
				</div>

				<!-- Contextual Text Container -->
				<div class="contextual-text-container">
					<div class="contextual-text" id="contextual-text">
						<!-- This will be populated by JavaScript -->
					</div>
				</div>
			</div>

			<!-- My Health Goals Section -->
			<div class="health-goals-section">
				<div class="scores-title-container">
					<h2 class="scores-title">MY HEALTH GOALS</h2>
				</div>
				<div class="health-goals-grid" role="group" aria-label="Select your health goals">
					<?php
					if ( isset( $health_goals_data ) && isset( $health_goals_data['all_goals'] ) ) :
						foreach ( $health_goals_data['all_goals'] as $goal_id => $goal ) :
							?>
							<div class="goal-pill <?php echo $goal['selected'] ? 'selected' : ''; ?>" 
								 data-goal-id="<?php echo esc_attr( $goal_id ); ?>"
								 role="button" 
								 tabindex="0"
								 aria-pressed="<?php echo $goal['selected'] ? 'true' : 'false'; ?>"
								 aria-label="<?php echo esc_attr( $goal['label'] . ( $goal['selected'] ? ' - Currently selected' : ' - Click to select' ) ); ?>"
								 title="<?php echo esc_attr( $goal['description'] ?? $goal['label'] ); ?>">
								<div class="goal-pill-icon" aria-hidden="true">
									<?php echo wp_kses_post( $goal['icon'] ); ?>
								</div>
								<span class="goal-pill-text"><?php echo esc_html( $goal['label'] ); ?></span>
								<span class="goal-pill-check <?php echo $goal['selected'] ? 'visible' : ''; ?>" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
										<polyline points="20,6 9,17 4,12"></polyline>
									</svg>
								</span>
								<div class="goal-pill-loading" aria-hidden="true">
									<div class="loading-spinner"></div>
								</div>
							</div>
							<?php
						endforeach;
						
						// Add goals summary
						$selected_count = count( array_filter( $health_goals_data['all_goals'], function( $goal ) { return $goal['selected']; } ) );
						$total_count = count( $health_goals_data['all_goals'] );
						?>
						<div class="goals-summary">
							<div class="goals-counter">
								<span class="selected-count"><?php echo esc_html( $selected_count ); ?></span> of 
								<span class="total-count"><?php echo esc_html( $total_count ); ?></span> goals selected
							</div>
							<?php if ( $selected_count > 0 ) : ?>
								<div class="goals-boost-indicator">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
										<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
									</svg>
									<span>Boost applied to matching assessments</span>
								</div>
							<?php endif; ?>
						</div>
					<?php else : ?>
						<div class="no-goals-message">
							<div class="no-goals-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="48" height="48">
									<circle cx="12" cy="12" r="10"/>
									<path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
									<line x1="12" y1="17" x2="12.01" y2="17"/>
								</svg>
							</div>
							<h3>No Health Goals Available</h3>
							<p>Complete your first assessment to unlock personalized health goals that will boost your scoring in relevant areas.</p>
							<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( 'assessments' ) ); ?>" class="btn btn-primary">
								Start an Assessment
							</a>
						</div>
						<?php
					endif;
					?>
				</div>
			</div>

			<!-- My Story Tabbed Section -->
			<div class="my-story-section">
				<div class="scores-title-container">
					<h2 class="scores-title">MY STORY</h2>
				</div>
				
				<div class="my-story-tabs">
					<nav class="my-story-tab-nav">
						<ul>
							<li><a href="#tab-my-assessments" class="my-story-tab-active">My Assessments</a></li>
							<li><a href="#tab-my-symptoms">My Symptoms</a></li>
							<li><a href="#tab-my-biomarkers">My Biomarkers</a></li>
							<li><a href="#tab-my-new-life">My New Life</a></li>
						</ul>
					</nav>
					
					<!-- Tab 1: My Assessments -->
					<div id="tab-my-assessments" class="my-story-tab-content my-story-tab-active">
						<div class="assessment-cards-container">
							<?php
							// Define the ordered assessment pairs with gender logic (same as display logic)
							$assessment_pairs = array(
								array('health', 'weight-loss'),
								array('hormone', 'testosterone'), // Will be filtered by gender
								array('hair', 'skin'),
								array('sleep', 'ed-treatment') // Will be filtered by gender
							);
							
							// Gender-based assessment filtering
							$user_gender = strtolower(trim($gender ?? ''));
							$is_male = ($user_gender === 'male');
							$is_female = ($user_gender === 'female');
							
							// Count assessments with gender-based filtering
							$completed_count = 0;
							$total_count = 0;
							
							foreach ($assessment_pairs as $pair) {
								foreach ($pair as $assessment_key) {
									// Skip if assessment doesn't exist
									if (!isset($user_assessments[$assessment_key])) {
										continue;
									}
									
									// Gender-based filtering (same logic as display)
									if ($assessment_key === 'testosterone' && $is_female) {
										continue; // Skip testosterone for females
									}
									if ($assessment_key === 'hormone' && $is_male) {
										continue; // Skip hormone for males (show testosterone instead)
									}
									if ($assessment_key === 'ed-treatment' && $is_female) {
										continue; // Skip ED treatment for females
									}
									
									$total_count++;
									if ($user_assessments[$assessment_key]['completed']) {
										$completed_count++;
									}
								}
							}
							?>
							
							<!-- Progress Summary -->
							<div class="progress-summary">
								<div class="progress-stats">
									<div class="stat-item">
										<span class="stat-number"><?php echo esc_html( $completed_count ); ?></span>
										<span class="stat-label">Completed</span>
									</div>
									<div class="stat-item">
										<span class="stat-number"><?php echo esc_html( $total_count - $completed_count ); ?></span>
										<span class="stat-label">Remaining</span>
									</div>
									<div class="stat-item">
										<span class="stat-number"><?php echo esc_html( $total_count > 0 ? round( ( $completed_count / $total_count ) * 100 ) : 0 ); ?>%</span>
										<span class="stat-label">Progress</span>
									</div>
								</div>
								<div class="progress-bar">
									<div class="progress-fill" style="width: <?php echo esc_attr( $total_count > 0 ? ( $completed_count / $total_count ) * 100 : 0 ); ?>%"></div>
								</div>
							</div>
							
							<div class="assessment-cards-grid">
								<?php
								// Define the ordered assessment pairs with gender logic
								$assessment_pairs = array(
									array('health', 'weight-loss'),
									array('hormone', 'testosterone'), // Will be filtered by gender
									array('hair', 'skin'),
									array('sleep', 'ed-treatment') // Will be filtered by gender
								);
								
								// Gender-based assessment filtering
								$user_gender = strtolower(trim($gender ?? ''));
								$is_male = ($user_gender === 'male');
								$is_female = ($user_gender === 'female');
								
								// Filter and order assessments
								$ordered_assessments = array();
								$card_index = 0;
								
								foreach ($assessment_pairs as $pair) {
									$pair_assessments = array();
									
									foreach ($pair as $assessment_key) {
										// Skip if assessment doesn't exist
										if (!isset($user_assessments[$assessment_key])) {
											continue;
										}
										
										// Gender-based filtering
										if ($assessment_key === 'testosterone' && $is_female) {
											continue; // Skip testosterone for females
										}
										if ($assessment_key === 'hormone' && $is_male) {
											continue; // Skip hormone for males (show testosterone instead)
										}
										if ($assessment_key === 'ed-treatment' && $is_female) {
											continue; // Skip ED treatment for females
										}
										
										$pair_assessments[] = array(
											'key' => $assessment_key,
											'data' => $user_assessments[$assessment_key],
											'index' => ++$card_index
										);
									}
									
									// Add valid pair to ordered assessments
									if (!empty($pair_assessments)) {
										$ordered_assessments = array_merge($ordered_assessments, $pair_assessments);
									}
								}
								
								// Define assessment icons using the same style as "Speak With Expert" button
								$assessment_icons = array(
									'hair' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
									'skin' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
									'health' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
									'weight-loss' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M10 11h4"/><path d="M10 16h4"/></svg>',
									'hormone' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>',
									'testosterone' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
									'sleep' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>',
									'ed-treatment' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
									'menopause' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>'
								);
								
								// Render the ordered assessments
								foreach ($ordered_assessments as $assessment_item) :
									$assessment_key = $assessment_item['key'];
									$assessment = $assessment_item['data'];
									$card_index = $assessment_item['index'];
									?>
									<div class="assessment-card <?php echo $assessment['completed'] ? 'completed' : 'incomplete'; ?> animate-card" style="animation-delay: <?php echo $card_index * 0.1; ?>s;">
										<div class="assessment-card-header">
											<?php 
											// Get assessment icon
											$assessment_icon = '';
											if (isset($assessment_icons[$assessment_key])) {
												$assessment_icon = $assessment_icons[$assessment_key];
											}
											// Debug: Let's see what's happening
											// echo "<!-- Debug: assessment_key = '$assessment_key', icon found = " . (!empty($assessment_icon) ? 'yes' : 'no') . " -->";
											?>
											<?php if (!empty($assessment_icon)) : ?>
												<div class="assessment-icon">
													<?php echo wp_kses_post( $assessment_icon ); ?>
												</div>
											<?php endif; ?>
											<h3 class="assessment-title"><?php echo esc_html( $assessment['label'] ?? ucwords( str_replace( '_', ' ', $assessment['key'] ?? 'Assessment' ) ) ); ?></h3>
											<div class="assessment-status">
												<?php if ( $assessment['completed'] && isset( $assessment['score'] ) ) : ?>
													<div class="assessment-score-display">
														<span class="score-value"><?php echo esc_html( number_format( $assessment['score'], 1 ) ); ?></span>
														<span class="score-label">Score</span>
													</div>
												<?php endif; ?>
												<?php if ( $assessment['completed'] ) : ?>
													<div class="status-completed-container">
														<span class="status-badge completed">
															<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
																<polyline points="20,6 9,17 4,12"></polyline>
															</svg>
															<span class="completed-text">Completed</span>
														</span>
														<?php if ( ! empty( $assessment['date'] ) ) : ?>
															<div class="assessment-timestamp">
																<?php 
																$timestamp = $assessment['date'];
																// Format the timestamp - if it's a Unix timestamp, convert it
																if ( is_numeric( $timestamp ) ) {
																	$date_obj = new DateTime();
																	$date_obj->setTimestamp( $timestamp );
																} else {
																	// If it's already a formatted date string, try to parse and reformat
																	$date_obj = DateTime::createFromFormat( 'Y-m-d H:i:s', $timestamp );
																	if ( ! $date_obj ) {
																		$date_obj = new DateTime( $timestamp );
																	}
																}
																
																if ( $date_obj ) {
																	// Get day of week
																	$day_of_week = $date_obj->format( 'l' );
																	
																	// Get month
																	$month = $date_obj->format( 'F' );
																	
																	// Get day with ordinal suffix
																	$day = $date_obj->format( 'j' );
																	$ordinal_suffix = '';
																	if ( $day >= 11 && $day <= 13 ) {
																		$ordinal_suffix = 'th';
																	} else {
																		switch ( $day % 10 ) {
																			case 1:
																				$ordinal_suffix = 'st';
																				break;
																			case 2:
																				$ordinal_suffix = 'nd';
																				break;
																			case 3:
																				$ordinal_suffix = 'rd';
																				break;
																			default:
																				$ordinal_suffix = 'th';
																		}
																	}
																	
																	// Get year
																	$year = $date_obj->format( 'Y' );
																	
																	// Get time in 12-hour format with lowercase am/pm
																	$time = $date_obj->format( 'g:i' ) . strtolower( $date_obj->format( 'A' ) );
																	
																	// Combine all parts
																	$formatted_date = $day_of_week . ' ' . $month . ' ' . $day . $ordinal_suffix . ', ' . $year . ' @ ' . $time;
																} else {
																	$formatted_date = $timestamp; // Fallback to original
																}
																echo esc_html( $formatted_date );
																?>
															</div>
														<?php endif; ?>
													</div>
												<?php else : ?>
													<span class="status-badge incomplete">
														<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
															<circle cx="12" cy="12" r="10"></circle>
															<path d="M12 6v6l4 2"></path>
														</svg>
														Pending
													</span>
												<?php endif; ?>
											</div>
										</div>
										
										<div class="assessment-card-body">
											<p class="assessment-description">
												<?php 
												if ( $assessment['completed'] ) {
													// For completed assessments, show primary recommendation/status
													$primary_recommendation = '';
													$score = $assessment['score'];
													
													// Generate status based on score
													if ( $score >= 8.0 ) {
														$primary_recommendation = sprintf( __( 'Excellent results! Your %s score indicates optimal health in this area.', 'ennulifeassessments' ), $assessment['label'] ?? 'assessment' );
													} elseif ( $score >= 6.5 ) {
														$primary_recommendation = sprintf( __( 'Good progress! Your %s shows positive indicators with room for optimization.', 'ennulifeassessments' ), $assessment['label'] ?? 'assessment' );
													} elseif ( $score >= 5.0 ) {
														$primary_recommendation = sprintf( __( 'Moderate results. Your %s suggests several areas for targeted improvement.', 'ennulifeassessments' ), $assessment['label'] ?? 'assessment' );
													} else {
														$primary_recommendation = sprintf( __( 'Significant opportunities for improvement identified in your %s results.', 'ennulifeassessments' ), $assessment['label'] ?? 'assessment' );
													}
													
													echo esc_html( $primary_recommendation );
												} else {
													// For incomplete assessments, show the default message
													$label = $assessment['label'] ?? ucwords( str_replace( '_', ' ', $assessment['key'] ?? 'Assessment' ) );
													$description = sprintf( __( 'Complete your %s to get personalized insights and recommendations.', 'ennulifeassessments' ), $label );
													echo esc_html( $description );
												}
												?>
											</p>
											
											<?php if ( $assessment['completed'] ) : ?>
												<!-- Speak With Expert Link - moved to top of completed section -->
												<div class="expert-consultation-link">
													<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( 'call' ) ); ?>" class="speak-expert-link">
														<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
															<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
															<circle cx="9" cy="7" r="4"/>
															<path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
															<path d="M16 3.13a4 4 0 0 1 0 7.75"/>
														</svg>
														Speak With Expert
													</a>
												</div>
												
												<!-- Recommendations Section (Hidden by default) -->
												<div class="assessment-section recommendations-section hidden">
													<h4 class="scores-title">RECOMMENDATIONS</h4>
													<div class="recommendations-content">
														<p>Your personalized recommendations will appear here based on your assessment results.</p>
														<!-- This can be populated with actual recommendations data -->
													</div>
												</div>
												
												<!-- Breakdown Section (Hidden by default) -->
												<?php if ( ! empty( $assessment['categories'] ) ) : ?>
													<div class="assessment-section breakdown-section hidden">
														<h4 class="scores-title">CATEGORY SCORES</h4>
														<div class="category-scores">
															<?php foreach ( $assessment['categories'] as $category => $score ) : ?>
																<div class="category-score-item">
																	<span class="category-name"><?php echo esc_html( $category ); ?></span>
																	<div class="category-score-bar">
																		<div class="category-score-fill" style="width: <?php echo esc_attr( $score * 10 ); ?>%"></div>
																	</div>
																	<span class="category-score-value"><?php echo esc_html( number_format( $score, 1 ) ); ?></span>
																</div>
															<?php endforeach; ?>
														</div>
													</div>
												<?php endif; ?>
											<?php endif; ?>
											
											<div class="assessment-card-actions">
												<?php if ( $assessment['completed'] ) : ?>
													<button type="button" class="btn btn-recommendations" data-assessment="<?php echo esc_attr( $assessment['key'] ); ?>">Recommendations</button>
													<button type="button" class="btn btn-breakdown" data-assessment="<?php echo esc_attr( $assessment['key'] ); ?>">Breakdown</button>
													<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( $shortcode_instance->get_assessment_page_slug( $assessment['key'] ) . '-assessment-details' ) ); ?>" class="btn btn-history">History</a>
												<?php else : ?>
													<!-- Expert consultation and Start Assessment for incomplete assessments -->
													<div class="incomplete-actions-row">
														<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( 'call' ) ); ?>" class="btn btn-expert-incomplete">
															<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14">
																<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
																<circle cx="9" cy="7" r="4"/>
																<path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
																<path d="M16 3.13a4 4 0 0 1 0 7.75"/>
															</svg>
															Speak With Expert
														</a>
														<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( $shortcode_instance->get_assessment_page_slug( $assessment['key'] ) ) ); ?>" class="btn btn-primary btn-pill">Start Assessment</a>
													</div>
												<?php endif; ?>
											</div>
											
											<?php if ( $assessment['completed'] ) : ?>
												<div class="assessment-retake-link">
													<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( $shortcode_instance->get_assessment_page_slug( $assessment['key'] ) ) ); ?>">Retake Assessment</a>
												</div>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
					
					<!-- Tab 2: My Symptoms -->
					<div id="tab-my-symptoms" class="my-story-tab-content">
						<div class="symptoms-container">
							<div class="symptoms-overview">
								<div class="symptoms-header">
									<h3 class="tab-section-title">My Symptoms</h3>
									<p class="tab-subtitle">Track your health symptoms and the biomarkers that should be reviewed</p>
								</div>
								
								<?php
								// Enhanced symptom data with specific biomarker recommendations
								$official_symptoms = array(
									'Energy' => array(
										'Fatigue' => array(
											'severity' => 'moderate', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'TSH', 'Free T3', 'Free T4', 'Vitamin D', 'B12', 'Iron', 'Ferritin', 'Testosterone', 'DHEA-S')
										),
										'Lack of Motivation' => array(
											'severity' => 'mild', 
											'frequency' => '3-4 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'DHEA-S', 'Cortisol', 'Dopamine', 'Serotonin', 'Vitamin D', 'B12')
										),
										'Reduced Physical Performance' => array(
											'severity' => 'moderate', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Cortisol', 'Creatine Kinase', 'Lactate Dehydrogenase', 'Vitamin D', 'B12', 'Iron')
										),
										'Decreased Physical Activity' => array(
											'severity' => 'mild', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Cortisol', 'Vitamin D', 'B12', 'Iron', 'Ferritin')
										),
										'Muscle Weakness' => array(
											'severity' => 'moderate', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Creatine Kinase', 'Vitamin D', 'B12', 'Iron', 'Ferritin', 'Calcium', 'Magnesium')
										),
										'Weakness' => array(
											'severity' => 'moderate', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Cortisol', 'Vitamin D', 'B12', 'Iron', 'Ferritin', 'Calcium', 'Magnesium', 'Creatine Kinase')
										)
									),
									'Heart Health' => array(
										'Chest Pain' => array(
											'severity' => 'critical', 
											'frequency' => 'weekly', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Troponin', 'CK-MB', 'BNP', 'CRP', 'Homocysteine', 'Lipoprotein(a)', 'ApoB', 'LDL-C', 'HDL-C', 'Triglycerides')
										),
										'Shortness of Breath' => array(
											'severity' => 'critical', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('BNP', 'Troponin', 'CRP', 'D-Dimer', 'Hemoglobin', 'Iron', 'Ferritin', 'Vitamin B12')
										),
										'Palpitations' => array(
											'severity' => 'moderate', 
											'frequency' => '3-4 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Troponin', 'BNP', 'Electrolytes', 'Magnesium', 'Calcium', 'Potassium', 'Sodium', 'Thyroid Panel')
										),
										'Lightheadedness' => array(
											'severity' => 'moderate', 
											'frequency' => '2-3 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Hemoglobin', 'Iron', 'Ferritin', 'B12', 'Electrolytes', 'Cortisol', 'Blood Pressure')
										),
										'Poor Exercise Tolerance' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('VO2 Max', 'Lactate Threshold', 'Hemoglobin', 'Iron', 'Ferritin', 'Testosterone', 'Cortisol')
										),
										'Swelling' => array(
											'severity' => 'mild', 
											'frequency' => 'weekly', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('BNP', 'Albumin', 'Creatinine', 'eGFR', 'Electrolytes', 'CRP')
										)
									),
									'Hormones' => array(
										'Anxiety' => array(
											'severity' => 'moderate', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'DHEA-S', 'Testosterone', 'Estradiol', 'Progesterone', 'Thyroid Panel', 'Vitamin D', 'B12', 'Magnesium')
										),
										'Depression' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'DHEA-S', 'Testosterone', 'Estradiol', 'Progesterone', 'Thyroid Panel', 'Vitamin D', 'B12', 'Folate', 'Omega-3')
										),
										'Irritability' => array(
											'severity' => 'mild', 
											'frequency' => '3-4 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'Testosterone', 'Estradiol', 'Progesterone', 'Thyroid Panel', 'Blood Sugar', 'Magnesium')
										),
										'Hot Flashes' => array(
											'severity' => 'moderate', 
											'frequency' => '4-5 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Estradiol', 'Progesterone', 'FSH', 'LH', 'Testosterone', 'DHEA-S', 'Thyroid Panel')
										),
										'Night Sweats' => array(
											'severity' => 'mild', 
											'frequency' => '2-3 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Estradiol', 'Progesterone', 'FSH', 'LH', 'Testosterone', 'Cortisol', 'Thyroid Panel')
										),
										'Erectile Dysfunction' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Free Testosterone', 'DHEA-S', 'Estradiol', 'Prolactin', 'Thyroid Panel', 'Lipid Panel', 'Blood Sugar')
										),
										'Vaginal Dryness' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Estradiol', 'Progesterone', 'FSH', 'LH', 'Testosterone', 'DHEA-S', 'Vitamin D')
										),
										'Infertility' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('FSH', 'LH', 'Estradiol', 'Progesterone', 'Testosterone', 'Prolactin', 'AMH', 'Inhibin B', 'Thyroid Panel')
										)
									),
									'Weight Loss' => array(
										'Increased Body Fat' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Cortisol', 'Insulin', 'Leptin', 'Adiponectin', 'Thyroid Panel', 'Lipid Panel', 'Blood Sugar')
										),
										'Abdominal Fat Gain' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Cortisol', 'Insulin', 'Leptin', 'Adiponectin', 'Thyroid Panel', 'Lipid Panel', 'Blood Sugar', 'CRP')
										),
										'Weight Changes' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Cortisol', 'Insulin', 'Leptin', 'Adiponectin', 'Thyroid Panel', 'Blood Sugar', 'Electrolytes')
										),
										'Slow Metabolism' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Thyroid Panel', 'Testosterone', 'Cortisol', 'Insulin', 'Leptin', 'Adiponectin', 'Vitamin D', 'B12')
										),
										'Blood Glucose Dysregulation' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Fasting Glucose', 'HbA1c', 'Insulin', 'C-Peptide', 'Leptin', 'Adiponectin', 'Lipid Panel', 'CRP')
										),
										'High Blood Pressure' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Renin', 'Aldosterone', 'Cortisol', 'Catecholamines', 'Lipid Panel', 'CRP', 'Homocysteine', 'Uric Acid')
										),
										'Joint Pain' => array(
											'severity' => 'mild', 
											'frequency' => '3-4 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('CRP', 'ESR', 'Uric Acid', 'Vitamin D', 'Calcium', 'Magnesium', 'Omega-3', 'Collagen Markers')
										),
										'Sleep Problems' => array(
											'severity' => 'moderate', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'Melatonin', 'Testosterone', 'Estradiol', 'Progesterone', 'Thyroid Panel', 'Vitamin D', 'Magnesium')
										)
									),
									'Strength' => array(
										'Muscle Loss' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'IGF-1', 'Creatine Kinase', 'Myostatin', 'Vitamin D', 'B12', 'Iron', 'Protein Markers')
										),
										'Muscle Mass Loss' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'IGF-1', 'Creatine Kinase', 'Myostatin', 'Vitamin D', 'B12', 'Iron', 'Protein Markers', 'Creatinine')
										),
										'Decreased Mobility' => array(
											'severity' => 'moderate', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'IGF-1', 'Creatine Kinase', 'Vitamin D', 'Calcium', 'Magnesium', 'CRP', 'Collagen Markers')
										),
										'Poor Balance' => array(
											'severity' => 'mild', 
											'frequency' => '2-3 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'B12', 'Iron', 'Calcium', 'Magnesium', 'Testosterone', 'Cortisol')
										),
										'Slow Recovery' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'IGF-1', 'Creatine Kinase', 'CRP', 'Vitamin D', 'B12', 'Iron', 'Protein Markers')
										),
										'Prolonged Soreness' => array(
											'severity' => 'mild', 
											'frequency' => '3-4 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Creatine Kinase', 'CRP', 'Lactate Dehydrogenase', 'Vitamin D', 'B12', 'Iron', 'Magnesium', 'Omega-3')
										)
									),
									'Longevity' => array(
										'Chronic Fatigue' => array(
											'severity' => 'moderate', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'DHEA-S', 'Testosterone', 'Thyroid Panel', 'Vitamin D', 'B12', 'Iron', 'Ferritin', 'CRP', 'Telomere Length')
										),
										'Cognitive Decline' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'B12', 'Folate', 'Omega-3', 'CRP', 'Homocysteine', 'ApoE', 'BDNF', 'Telomere Length')
										),
										'Frequent Illness' => array(
											'severity' => 'moderate', 
											'frequency' => 'monthly', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'Zinc', 'Selenium', 'CRP', 'White Blood Cell Count', 'Immunoglobulin Levels', 'Telomere Length')
										),
										'Itchy Skin' => array(
											'severity' => 'mild', 
											'frequency' => 'weekly', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'Zinc', 'B12', 'Iron', 'Liver Function Tests', 'Allergy Markers', 'CRP')
										),
										'Slow Healing Wounds' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'Zinc', 'B12', 'Iron', 'Protein Markers', 'Blood Sugar', 'CRP', 'Collagen Markers')
										)
									),
									'Cognitive Health' => array(
										'Brain Fog' => array(
											'severity' => 'critical', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'B12', 'Folate', 'Omega-3', 'Thyroid Panel', 'Cortisol', 'Testosterone', 'Estradiol', 'CRP', 'Homocysteine')
										),
										'Confusion' => array(
											'severity' => 'critical', 
											'frequency' => '3-4 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'B12', 'Folate', 'Omega-3', 'Thyroid Panel', 'Cortisol', 'Blood Sugar', 'Electrolytes', 'CRP')
										),
										'Memory Loss' => array(
											'severity' => 'critical', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'B12', 'Folate', 'Omega-3', 'Thyroid Panel', 'Cortisol', 'Testosterone', 'Estradiol', 'CRP', 'Homocysteine', 'ApoE')
										),
										'Poor Concentration' => array(
											'severity' => 'critical', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'B12', 'Folate', 'Omega-3', 'Thyroid Panel', 'Cortisol', 'Testosterone', 'Estradiol', 'CRP', 'Homocysteine', 'Dopamine')
										),
										'Language Problems' => array(
											'severity' => 'critical', 
											'frequency' => 'weekly', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'B12', 'Folate', 'Omega-3', 'Thyroid Panel', 'Cortisol', 'CRP', 'Homocysteine', 'ApoE')
										),
										'Poor Coordination' => array(
											'severity' => 'critical', 
											'frequency' => 'weekly', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'B12', 'Folate', 'Omega-3', 'Thyroid Panel', 'Cortisol', 'Blood Sugar', 'Electrolytes', 'CRP')
										),
										'Change in Personality' => array(
											'severity' => 'critical', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'Testosterone', 'Estradiol', 'Thyroid Panel', 'CRP', 'Homocysteine', 'ApoE', 'Neurotransmitter Panel')
										),
										'Mood Changes' => array(
											'severity' => 'critical', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'Testosterone', 'Estradiol', 'Progesterone', 'Thyroid Panel', 'Vitamin D', 'B12', 'Folate', 'Omega-3', 'CRP')
										),
										'Sleep Disturbance' => array(
											'severity' => 'critical', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'Melatonin', 'Testosterone', 'Estradiol', 'Progesterone', 'Thyroid Panel', 'Vitamin D', 'Magnesium', 'CRP')
										)
									),
									'Libido' => array(
										'Low Libido' => array(
											'severity' => 'mild', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Free Testosterone', 'DHEA-S', 'Estradiol', 'Progesterone', 'Prolactin', 'Thyroid Panel', 'Vitamin D', 'B12')
										),
										'Low Self-Esteem' => array(
											'severity' => 'mild', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Cortisol', 'DHEA-S', 'Estradiol', 'Progesterone', 'Thyroid Panel', 'Vitamin D', 'B12', 'Serotonin')
										)
									)
								);
								
								$total_symptoms = 0;
								foreach ($official_symptoms as $category => $symptoms) {
									$total_symptoms += count($symptoms);
								}
								?>
								
								<div class="symptoms-stats">
									<div class="symptom-stat-card">
										<div class="stat-number"><?php echo esc_html( $total_symptoms ); ?></div>
										<div class="stat-label">Total Symptoms Reported</div>
									</div>
									<div class="symptom-stat-card">
										<div class="stat-number"><?php echo esc_html( count($official_symptoms) ); ?></div>
										<div class="stat-label">Symptom Categories</div>
									</div>
									<div class="symptom-stat-card">
										<div class="stat-number">4</div>
										<div class="stat-label">Assessments Completed</div>
									</div>
								</div>
								
								<div class="symptoms-categories">
									<?php foreach ($official_symptoms as $category => $symptoms) : ?>
										<div class="collapsible-section">
											<div class="collapsible-header" onclick="toggleCollapsible(this)">
												<h4 class="collapsible-title">
													<svg class="collapsible-icon" fill="currentColor" viewBox="0 0 20 20">
														<path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
													</svg>
													<?php echo esc_html($category); ?>
												</h4>
												<span class="symptom-count"><?php echo count($symptoms); ?> symptoms</span>
											</div>
											<div class="collapsible-content">
												<div class="symptoms-list">
													<?php foreach ($symptoms as $symptom => $details) : ?>
														<div class="symptom-item enhanced">
															<div class="symptom-main">
																<div class="symptom-icon">
																	<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
																		<circle cx="12" cy="12" r="3"/>
																		<path d="M12 1v6m0 6v6"/>
																		<path d="M17.5 7.5l-5.5 5.5-5.5-5.5"/>
																	</svg>
																</div>
																<div class="symptom-details">
																	<span class="symptom-text"><?php echo esc_html($symptom); ?></span>
																	<div class="symptom-meta">
																		<span class="symptom-frequency"><?php echo esc_html($details['frequency']); ?></span>
																		<span class="symptom-assessment"><?php echo esc_html($details['assessment']); ?></span>
																	</div>
																	<!-- Enhanced biomarker display -->
																	<div class="symptom-biomarkers">
																		<span class="biomarkers-label">Review Biomarkers:</span>
																		<div class="biomarkers-list">
																			<?php foreach ($details['biomarkers'] as $biomarker) : ?>
																				<span class="biomarker-tag"><?php echo esc_html($biomarker); ?></span>
																			<?php endforeach; ?>
																		</div>
																	</div>
																</div>
															</div>
															<div class="symptom-severity">
																<div class="severity-indicator <?php echo esc_attr($details['severity']); ?>">
																	<span class="severity-dot"></span>
																	<span class="severity-label"><?php echo esc_html(ucfirst($details['severity'])); ?></span>
																</div>
															</div>
														</div>
													<?php endforeach; ?>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
								
								<!-- Symptom Analysis & Recommendations -->
								<div class="symptom-analysis">
									<div class="analysis-header">
										<h4 class="analysis-title">Symptom Analysis & Recommendations</h4>
										<p class="analysis-subtitle">Based on your reported symptoms, here's what we recommend:</p>
									</div>
									
									<div class="recommendations-grid">
										<div class="recommendation-card priority">
											<div class="rec-icon">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
													<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
												</svg>
											</div>
											<div class="rec-content">
												<h5>Priority: Hormone Optimization</h5>
												<p>Your symptoms suggest hormonal imbalances. Lab testing recommended to identify root causes.</p>
												<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-primary btn-sm">Schedule Consultation</a>
											</div>
										</div>
										
										<div class="recommendation-card">
											<div class="rec-icon">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
													<path d="M12 2v20M2 12h20"/>
												</svg>
											</div>
											<div class="rec-content">
												<h5>Complete Lab Panel</h5>
												<p>Comprehensive biomarker testing to identify underlying health issues.</p>
												<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-secondary btn-sm">Order Labs</a>
											</div>
										</div>
										
										<div class="recommendation-card">
											<div class="rec-icon">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
													<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
													<circle cx="12" cy="7" r="4"/>
												</svg>
											</div>
											<div class="rec-content">
												<h5>Personalized Coaching</h5>
												<p>Work with a health coach to address symptoms systematically.</p>
												<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-secondary btn-sm">Get Coach</a>
											</div>
										</div>
									</div>
								</div>
								
								<!-- Symptom Tracking CTA -->
								<div class="symptom-tracking-cta">
									<div class="cta-content">
										<h4>Ready to Transform Your Health?</h4>
										<p>Get personalized recommendations and track your progress with our comprehensive health assessment.</p>
										<div class="cta-buttons">
											<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-primary btn-pill">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
													<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
												</svg>
												ENNU Full Body Diagnostic ($599)
											</a>
											<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-outline btn-pill">
												Join ENNU LIFE Membership
											</a>
										</div>
									</div>
								</div>
							</div>
||||||| f31b4df
					<p class="report-subtitle">
						<?php if ( $is_completed ) : ?>
							Your key health vectors. Expand to see related symptoms and biomarkers.
						<?php else : ?>
							Explore health vectors to understand symptoms and biomarkers for each area.
						<?php endif; ?>
					</p>

					<?php if ( ! $is_completed && $health_opt_assessment ) : ?>
						<div class="empty-state-actions" style="margin: 15px 0;">
							<a href="<?php echo esc_url( $health_opt_assessment['url'] ); ?>" class="action-button button-report" style="width: 100%; text-align: center;">Start Health Optimization</a>
						</div>
					<?php endif; ?>
					
					<!-- Tab 2: My Symptoms -->
					<div id="tab-my-symptoms" class="my-story-tab-content">
						<div class="symptoms-container">
							<div class="symptoms-overview">
								<div class="symptoms-header">
									<h3 class="tab-section-title">My Symptoms</h3>
									<p class="tab-subtitle">Track your health symptoms and the biomarkers that should be reviewed</p>
								</div>
								
								<?php
								// NEW: Use centralized symptoms system
								if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
									$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( get_current_user_id() );
									$symptom_analytics = ENNU_Centralized_Symptoms_Manager::get_symptom_analytics( get_current_user_id() );
									
									if ( ! empty( $centralized_symptoms['symptoms'] ) ) {
										echo '<div class="symptom-summary">';
										echo '<div class="symptom-stats">';
										echo '<div class="stat-item"><span class="stat-number">' . $symptom_analytics['total_symptoms'] . '</span><span class="stat-label">Total Symptoms</span></div>';
										echo '<div class="stat-item"><span class="stat-number">' . $symptom_analytics['unique_symptoms'] . '</span><span class="stat-label">Unique Symptoms</span></div>';
										echo '<div class="stat-item"><span class="stat-number">' . $symptom_analytics['assessments_with_symptoms'] . '</span><span class="stat-label">Assessments</span></div>';
										echo '</div>';
										echo '</div>';
										
										// Display symptoms by category
										foreach ( $centralized_symptoms['by_category'] as $category => $symptom_keys ) {
											echo '<div class="symptom-category">';
											echo '<h4 class="category-title">' . esc_html( $category ) . '</h4>';
											
											foreach ( $symptom_keys as $symptom_key ) {
												$symptom = $centralized_symptoms['symptoms'][ $symptom_key ];
												echo '<div class="symptom-item">';
												echo '<div class="symptom-name">' . esc_html( $symptom['name'] ) . '</div>';
												
												if ( ! empty( $symptom['severity'] ) ) {
													echo '<div class="symptom-severity">Severity: ' . esc_html( $symptom['severity'][0] ) . '</div>';
												}
												
												if ( ! empty( $symptom['frequency'] ) ) {
													echo '<div class="symptom-frequency">Frequency: ' . esc_html( $symptom['frequency'][0] ) . '</div>';
												}
												
												echo '<div class="symptom-assessments">From: ' . esc_html( implode( ', ', $symptom['assessments'] ) ) . '</div>';
												echo '</div>';
											}
											
											echo '</div>';
										}
									} else {
										echo '<div class="no-symptoms">No symptoms reported yet. Complete assessments to see your symptoms here.</div>';
									}
								} else {
									// FALLBACK: Enhanced symptom data with specific biomarker recommendations
								$official_symptoms = array(
									'Energy' => array(
										'Fatigue' => array(
											'severity' => 'moderate', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'TSH', 'Free T3', 'Free T4', 'Vitamin D', 'B12', 'Iron', 'Ferritin', 'Testosterone', 'DHEA-S')
										),
										'Lack of Motivation' => array(
											'severity' => 'mild', 
											'frequency' => '3-4 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'DHEA-S', 'Cortisol', 'Dopamine', 'Serotonin', 'Vitamin D', 'B12')
										),
										'Reduced Physical Performance' => array(
											'severity' => 'moderate', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Cortisol', 'Creatine Kinase', 'Lactate Dehydrogenase', 'Vitamin D', 'B12', 'Iron')
										),
										'Decreased Physical Activity' => array(
											'severity' => 'mild', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Cortisol', 'Vitamin D', 'B12', 'Iron', 'Ferritin')
										),
										'Muscle Weakness' => array(
											'severity' => 'moderate', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Creatine Kinase', 'Vitamin D', 'B12', 'Iron', 'Ferritin', 'Calcium', 'Magnesium')
										),
										'Weakness' => array(
											'severity' => 'moderate', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Cortisol', 'Vitamin D', 'B12', 'Iron', 'Ferritin', 'Calcium', 'Magnesium', 'Creatine Kinase')
										)
									),
									'Heart Health' => array(
										'Chest Pain' => array(
											'severity' => 'critical', 
											'frequency' => 'weekly', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Troponin', 'CK-MB', 'BNP', 'CRP', 'Homocysteine', 'Lipoprotein(a)', 'ApoB', 'LDL-C', 'HDL-C', 'Triglycerides')
										),
										'Shortness of Breath' => array(
											'severity' => 'critical', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('BNP', 'Troponin', 'CRP', 'D-Dimer', 'Hemoglobin', 'Iron', 'Ferritin', 'Vitamin B12')
										),
										'Palpitations' => array(
											'severity' => 'moderate', 
											'frequency' => '3-4 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Troponin', 'BNP', 'Electrolytes', 'Magnesium', 'Calcium', 'Potassium', 'Sodium', 'Thyroid Panel')
										),
										'Lightheadedness' => array(
											'severity' => 'moderate', 
											'frequency' => '2-3 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Hemoglobin', 'Iron', 'Ferritin', 'B12', 'Electrolytes', 'Cortisol', 'Blood Pressure')
										),
										'Poor Exercise Tolerance' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('VO2 Max', 'Lactate Threshold', 'Hemoglobin', 'Iron', 'Ferritin', 'Testosterone', 'Cortisol')
										),
										'Swelling' => array(
											'severity' => 'mild', 
											'frequency' => 'weekly', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('BNP', 'Albumin', 'Creatinine', 'eGFR', 'Electrolytes', 'CRP')
										)
									),
									'Hormones' => array(
										'Anxiety' => array(
											'severity' => 'moderate', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'DHEA-S', 'Testosterone', 'Estradiol', 'Progesterone', 'Thyroid Panel', 'Vitamin D', 'B12', 'Magnesium')
										),
										'Depression' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'DHEA-S', 'Testosterone', 'Estradiol', 'Progesterone', 'Thyroid Panel', 'Vitamin D', 'B12', 'Folate', 'Omega-3')
										),
										'Irritability' => array(
											'severity' => 'mild', 
											'frequency' => '3-4 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'Testosterone', 'Estradiol', 'Progesterone', 'Thyroid Panel', 'Blood Sugar', 'Magnesium')
										),
										'Hot Flashes' => array(
											'severity' => 'moderate', 
											'frequency' => '4-5 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Estradiol', 'Progesterone', 'FSH', 'LH', 'Testosterone', 'DHEA-S', 'Thyroid Panel')
										),
										'Night Sweats' => array(
											'severity' => 'mild', 
											'frequency' => '2-3 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Estradiol', 'Progesterone', 'FSH', 'LH', 'Testosterone', 'Cortisol', 'Thyroid Panel')
										),
										'Erectile Dysfunction' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Free Testosterone', 'DHEA-S', 'Estradiol', 'Prolactin', 'Thyroid Panel', 'Lipid Panel', 'Blood Sugar')
										),
										'Vaginal Dryness' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Estradiol', 'Progesterone', 'FSH', 'LH', 'Testosterone', 'DHEA-S', 'Vitamin D')
										),
										'Infertility' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('FSH', 'LH', 'Estradiol', 'Progesterone', 'Testosterone', 'Prolactin', 'AMH', 'Inhibin B', 'Thyroid Panel')
										)
									),
									'Weight Loss' => array(
										'Increased Body Fat' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Cortisol', 'Insulin', 'Leptin', 'Adiponectin', 'Thyroid Panel', 'Lipid Panel', 'Blood Sugar')
										),
										'Abdominal Fat Gain' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Cortisol', 'Insulin', 'Leptin', 'Adiponectin', 'Thyroid Panel', 'Lipid Panel', 'Blood Sugar', 'CRP')
										),
										'Weight Changes' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Cortisol', 'Insulin', 'Leptin', 'Adiponectin', 'Thyroid Panel', 'Blood Sugar', 'Electrolytes')
										),
										'Slow Metabolism' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Thyroid Panel', 'Testosterone', 'Cortisol', 'Insulin', 'Leptin', 'Adiponectin', 'Vitamin D', 'B12')
										),
										'Blood Glucose Dysregulation' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Fasting Glucose', 'HbA1c', 'Insulin', 'C-Peptide', 'Leptin', 'Adiponectin', 'Lipid Panel', 'CRP')
										),
										'High Blood Pressure' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Renin', 'Aldosterone', 'Cortisol', 'Catecholamines', 'Lipid Panel', 'CRP', 'Homocysteine', 'Uric Acid')
										),
										'Joint Pain' => array(
											'severity' => 'mild', 
											'frequency' => '3-4 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('CRP', 'ESR', 'Uric Acid', 'Vitamin D', 'Calcium', 'Magnesium', 'Omega-3', 'Collagen Markers')
										),
										'Sleep Problems' => array(
											'severity' => 'moderate', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'Melatonin', 'Testosterone', 'Estradiol', 'Progesterone', 'Thyroid Panel', 'Vitamin D', 'Magnesium')
										)
									),
									'Strength' => array(
										'Muscle Loss' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'IGF-1', 'Creatine Kinase', 'Myostatin', 'Vitamin D', 'B12', 'Iron', 'Protein Markers')
										),
										'Muscle Mass Loss' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'IGF-1', 'Creatine Kinase', 'Myostatin', 'Vitamin D', 'B12', 'Iron', 'Protein Markers', 'Creatinine')
										),
										'Decreased Mobility' => array(
											'severity' => 'moderate', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'IGF-1', 'Creatine Kinase', 'Vitamin D', 'Calcium', 'Magnesium', 'CRP', 'Collagen Markers')
										),
										'Poor Balance' => array(
											'severity' => 'mild', 
											'frequency' => '2-3 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'B12', 'Iron', 'Calcium', 'Magnesium', 'Testosterone', 'Cortisol')
										),
										'Slow Recovery' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'IGF-1', 'Creatine Kinase', 'CRP', 'Vitamin D', 'B12', 'Iron', 'Protein Markers')
										),
										'Prolonged Soreness' => array(
											'severity' => 'mild', 
											'frequency' => '3-4 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Creatine Kinase', 'CRP', 'Lactate Dehydrogenase', 'Vitamin D', 'B12', 'Iron', 'Magnesium', 'Omega-3')
										)
									),
									'Longevity' => array(
										'Chronic Fatigue' => array(
											'severity' => 'moderate', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'DHEA-S', 'Testosterone', 'Thyroid Panel', 'Vitamin D', 'B12', 'Iron', 'Ferritin', 'CRP', 'Telomere Length')
										),
										'Cognitive Decline' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'B12', 'Folate', 'Omega-3', 'CRP', 'Homocysteine', 'ApoE', 'BDNF', 'Telomere Length')
										),
										'Frequent Illness' => array(
											'severity' => 'moderate', 
											'frequency' => 'monthly', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'Zinc', 'Selenium', 'CRP', 'White Blood Cell Count', 'Immunoglobulin Levels', 'Telomere Length')
										),
										'Itchy Skin' => array(
											'severity' => 'mild', 
											'frequency' => 'weekly', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'Zinc', 'B12', 'Iron', 'Liver Function Tests', 'Allergy Markers', 'CRP')
										),
										'Slow Healing Wounds' => array(
											'severity' => 'moderate', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'Zinc', 'B12', 'Iron', 'Protein Markers', 'Blood Sugar', 'CRP', 'Collagen Markers')
										)
									),
									'Cognitive Health' => array(
										'Brain Fog' => array(
											'severity' => 'critical', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'B12', 'Folate', 'Omega-3', 'Thyroid Panel', 'Cortisol', 'Testosterone', 'Estradiol', 'CRP', 'Homocysteine')
										),
										'Confusion' => array(
											'severity' => 'critical', 
											'frequency' => '3-4 times/week', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'B12', 'Folate', 'Omega-3', 'Thyroid Panel', 'Cortisol', 'Blood Sugar', 'Electrolytes', 'CRP')
										),
										'Memory Loss' => array(
											'severity' => 'critical', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'B12', 'Folate', 'Omega-3', 'Thyroid Panel', 'Cortisol', 'Testosterone', 'Estradiol', 'CRP', 'Homocysteine', 'ApoE')
										),
										'Poor Concentration' => array(
											'severity' => 'critical', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'B12', 'Folate', 'Omega-3', 'Thyroid Panel', 'Cortisol', 'Testosterone', 'Estradiol', 'CRP', 'Homocysteine', 'Dopamine')
										),
										'Language Problems' => array(
											'severity' => 'critical', 
											'frequency' => 'weekly', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'B12', 'Folate', 'Omega-3', 'Thyroid Panel', 'Cortisol', 'CRP', 'Homocysteine', 'ApoE')
										),
										'Poor Coordination' => array(
											'severity' => 'critical', 
											'frequency' => 'weekly', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Vitamin D', 'B12', 'Folate', 'Omega-3', 'Thyroid Panel', 'Cortisol', 'Blood Sugar', 'Electrolytes', 'CRP')
										),
										'Change in Personality' => array(
											'severity' => 'critical', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'Testosterone', 'Estradiol', 'Thyroid Panel', 'CRP', 'Homocysteine', 'ApoE', 'Neurotransmitter Panel')
										),
										'Mood Changes' => array(
											'severity' => 'critical', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'Testosterone', 'Estradiol', 'Progesterone', 'Thyroid Panel', 'Vitamin D', 'B12', 'Folate', 'Omega-3', 'CRP')
										),
										'Sleep Disturbance' => array(
											'severity' => 'critical', 
											'frequency' => 'daily', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Cortisol', 'Melatonin', 'Testosterone', 'Estradiol', 'Progesterone', 'Thyroid Panel', 'Vitamin D', 'Magnesium', 'CRP')
										)
									),
									'Libido' => array(
										'Low Libido' => array(
											'severity' => 'mild', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Free Testosterone', 'DHEA-S', 'Estradiol', 'Progesterone', 'Prolactin', 'Thyroid Panel', 'Vitamin D', 'B12')
										),
										'Low Self-Esteem' => array(
											'severity' => 'mild', 
											'frequency' => 'ongoing', 
											'assessment' => 'Health Optimization',
											'biomarkers' => array('Testosterone', 'Cortisol', 'DHEA-S', 'Estradiol', 'Progesterone', 'Thyroid Panel', 'Vitamin D', 'B12', 'Serotonin')
										)
									)
								);
								
								$total_symptoms = 0;
								foreach ($official_symptoms as $category => $symptoms) {
									$total_symptoms += count($symptoms);
								}
								?>
								
								<?php } // End of fallback condition ?>
								
								<div class="symptoms-stats">
									<div class="symptom-stat-card">
										<div class="stat-number"><?php echo esc_html( $total_symptoms ); ?></div>
										<div class="stat-label">Total Symptoms Reported</div>
									</div>
									<div class="symptom-stat-card">
										<div class="stat-number"><?php echo esc_html( count($official_symptoms) ); ?></div>
										<div class="stat-label">Symptom Categories</div>
									</div>
									<div class="symptom-stat-card">
										<div class="stat-number">4</div>
										<div class="stat-label">Assessments Completed</div>
									</div>
								</div>
								
								<div class="symptoms-categories">
									<?php foreach ($official_symptoms as $category => $symptoms) : ?>
										<div class="collapsible-section">
											<div class="collapsible-header" onclick="toggleCollapsible(this)">
												<h4 class="collapsible-title">
													<svg class="collapsible-icon" fill="currentColor" viewBox="0 0 20 20">
														<path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
													</svg>
													<?php echo esc_html($category); ?>
												</h4>
												<span class="symptom-count"><?php echo count($symptoms); ?> symptoms</span>
											</div>
											<div class="collapsible-content">
												<div class="symptoms-list">
													<?php foreach ($symptoms as $symptom => $details) : ?>
														<div class="symptom-item enhanced">
															<div class="symptom-main">
																<div class="symptom-icon">
																	<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
																		<circle cx="12" cy="12" r="3"/>
																		<path d="M12 1v6m0 6v6"/>
																		<path d="M17.5 7.5l-5.5 5.5-5.5-5.5"/>
																	</svg>
																</div>
																<div class="symptom-details">
																	<span class="symptom-text"><?php echo esc_html($symptom); ?></span>
																	<div class="symptom-meta">
																		<span class="symptom-frequency"><?php echo esc_html($details['frequency']); ?></span>
																		<span class="symptom-assessment"><?php echo esc_html($details['assessment']); ?></span>
																	</div>
																	<!-- Enhanced biomarker display -->
																	<div class="symptom-biomarkers">
																		<span class="biomarkers-label">Review Biomarkers:</span>
																		<div class="biomarkers-list">
																			<?php foreach ($details['biomarkers'] as $biomarker) : ?>
																				<span class="biomarker-tag"><?php echo esc_html($biomarker); ?></span>
																			<?php endforeach; ?>
																		</div>
																	</div>
																</div>
															</div>
															<div class="symptom-severity">
																<div class="severity-indicator <?php echo esc_attr($details['severity']); ?>">
																	<span class="severity-dot"></span>
																	<span class="severity-label"><?php echo esc_html(ucfirst($details['severity'])); ?></span>
																</div>
															</div>
														</div>
													<?php endforeach; ?>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
								
								<!-- Symptom Analysis & Recommendations -->
								<div class="symptom-analysis">
									<div class="analysis-header">
										<h4 class="analysis-title">Symptom Analysis & Recommendations</h4>
										<p class="analysis-subtitle">Based on your reported symptoms, here's what we recommend:</p>
									</div>
									
									<div class="recommendations-grid">
										<div class="recommendation-card priority">
											<div class="rec-icon">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
													<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
												</svg>
											</div>
											<div class="rec-content">
												<h5>Priority: Hormone Optimization</h5>
												<p>Your symptoms suggest hormonal imbalances. Lab testing recommended to identify root causes.</p>
												<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-primary btn-sm">Schedule Consultation</a>
											</div>
										</div>
										
										<div class="recommendation-card">
											<div class="rec-icon">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
													<path d="M12 2v20M2 12h20"/>
												</svg>
											</div>
											<div class="rec-content">
												<h5>Complete Lab Panel</h5>
												<p>Comprehensive biomarker testing to identify underlying health issues.</p>
												<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-secondary btn-sm">Order Labs</a>
											</div>
										</div>
										
										<div class="recommendation-card">
											<div class="rec-icon">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
													<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
													<circle cx="12" cy="7" r="4"/>
												</svg>
											</div>
											<div class="rec-content">
												<h5>Personalized Coaching</h5>
												<p>Work with a health coach to address symptoms systematically.</p>
												<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-secondary btn-sm">Get Coach</a>
											</div>
										</div>
									</div>
								</div>
								
								<!-- Symptom Tracking CTA -->
								<div class="symptom-tracking-cta">
									<div class="cta-content">
										<h4>Ready to Transform Your Health?</h4>
										<p>Get personalized recommendations and track your progress with our comprehensive health assessment.</p>
										<div class="cta-buttons">
											<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-primary btn-pill">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
													<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
												</svg>
												ENNU Full Body Diagnostic ($599)
											</a>
											<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-outline btn-pill">
												Join ENNU LIFE Membership
											</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<!-- Tab 3: My Biomarkers -->
					<div id="tab-my-biomarkers" class="my-story-tab-content">
						<div class="biomarkers-container">
							<div class="biomarkers-overview">
								<div class="biomarkers-header">
									<h3 class="tab-section-title">My Biomarkers</h3>
									<p class="tab-subtitle">ENNU LIFE Comprehensive Lab Panel - Track your biological markers for optimal health</p>
								</div>
								
								<div class="biomarkers-intro">
									<div class="lab-panel-info">
										<div class="panel-highlight">
											<h4>ENNU LIFE Comprehensive Labs</h4>
											<p>Our comprehensive biomarker panel tracks 75+ core biological markers across 12 health categories to provide the most complete picture of your health.</p>
											<div class="panel-stats">
												<div class="panel-stat">
													<span class="stat-number">75+</span>
													<span class="stat-label">Core Biomarkers</span>
												</div>
												<div class="panel-stat">
													<span class="stat-number">12</span>
													<span class="stat-label">Categories</span>
												</div>
												<div class="panel-stat">
													<span class="stat-number">$799</span>
													<span class="stat-label">Value</span>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<?php
								// Enhanced ENNU Life biomarkers - 75+ core biomarkers across 12 categories
								$official_biomarkers = array(
									'Physical Measurements' => array(
										'description' => 'Direct physical measurements for body composition and vital signs',
										'biomarkers' => array(
											'Weight' => array('value' => '185', 'unit' => 'lbs', 'status' => 'high', 'range' => '150-180', 'trend' => 'increasing', 'priority' => 'high'),
											'BMI (Body Mass Index)' => array('value' => '28.5', 'unit' => 'kg/m²', 'status' => 'high', 'range' => '18.5-24.9', 'trend' => 'increasing', 'priority' => 'high'),
											'Body Fat %' => array('value' => '25', 'unit' => '%', 'status' => 'high', 'range' => '10-20', 'trend' => 'increasing', 'priority' => 'high'),
											'Waist Measurement' => array('value' => '38', 'unit' => 'inches', 'status' => 'high', 'range' => '<40', 'trend' => 'increasing', 'priority' => 'high'),
											'Neck Measurement' => array('value' => '16.5', 'unit' => 'inches', 'status' => 'high', 'range' => '<17', 'trend' => 'stable', 'priority' => 'medium'),
											'Blood Pressure' => array('value' => '135/85', 'unit' => 'mmHg', 'status' => 'high', 'range' => '<120/80', 'trend' => 'increasing', 'priority' => 'critical'),
											'Heart Rate' => array('value' => '75', 'unit' => 'bpm', 'status' => 'normal', 'range' => '60-80', 'trend' => 'stable', 'priority' => 'normal'),
											'Temperature' => array('value' => '98.2', 'unit' => '°F', 'status' => 'normal', 'range' => '98.0-98.6', 'trend' => 'stable', 'priority' => 'normal')
										)
									),
									'Basic Metabolic Panel' => array(
										'description' => 'Essential metabolic markers for energy, kidney function, and electrolyte balance',
										'biomarkers' => array(
											'Glucose' => array('value' => '105', 'unit' => 'mg/dL', 'status' => 'high', 'range' => '70-85', 'trend' => 'increasing', 'priority' => 'critical'),
											'HbA1c (Hemoglobin A1c)' => array('value' => '5.8', 'unit' => '%', 'status' => 'high', 'range' => '<5.5', 'trend' => 'increasing', 'priority' => 'critical'),
											'BUN (Blood Urea Nitrogen)' => array('value' => '18', 'unit' => 'mg/dL', 'status' => 'high', 'range' => '7-15', 'trend' => 'increasing', 'priority' => 'high'),
											'Creatinine' => array('value' => '1.2', 'unit' => 'mg/dL', 'status' => 'high', 'range' => '0.7-1.1', 'trend' => 'increasing', 'priority' => 'high'),
											'GFR (Glomerular Filtration Rate)' => array('value' => '75', 'unit' => 'mL/min/1.73m²', 'status' => 'low', 'range' => '>90', 'trend' => 'decreasing', 'priority' => 'high'),
											'BUN/Creatinine Ratio' => array('value' => '15', 'unit' => 'ratio', 'status' => 'normal', 'range' => '10-15', 'trend' => 'stable', 'priority' => 'normal'),
											'Sodium' => array('value' => '140', 'unit' => 'mEq/L', 'status' => 'normal', 'range' => '136-142', 'trend' => 'stable', 'priority' => 'normal'),
											'Potassium' => array('value' => '4.2', 'unit' => 'mEq/L', 'status' => 'normal', 'range' => '3.8-4.5', 'trend' => 'stable', 'priority' => 'normal')
										)
									),
									'Electrolytes & Minerals' => array(
										'description' => 'Essential minerals and electrolytes for cellular function',
										'biomarkers' => array(
											'Chloride' => array('value' => '102', 'unit' => 'mEq/L', 'status' => 'normal', 'range' => '98-104', 'trend' => 'stable', 'priority' => 'normal'),
											'Carbon Dioxide' => array('value' => '25', 'unit' => 'mEq/L', 'status' => 'normal', 'range' => '23-27', 'trend' => 'stable', 'priority' => 'normal'),
											'Calcium' => array('value' => '9.8', 'unit' => 'mg/dL', 'status' => 'normal', 'range' => '8.5-10.5', 'trend' => 'stable', 'priority' => 'normal'),
											'Phosphorus' => array('value' => '3.2', 'unit' => 'mg/dL', 'status' => 'normal', 'range' => '2.5-4.5', 'trend' => 'stable', 'priority' => 'normal'),
											'Magnesium' => array('value' => '1.8', 'unit' => 'mg/dL', 'status' => 'low', 'range' => '1.9-2.5', 'trend' => 'decreasing', 'priority' => 'high'),
											'Iron' => array('value' => '65', 'unit' => 'µg/dL', 'status' => 'low', 'range' => '70-180', 'trend' => 'decreasing', 'priority' => 'high'),
											'Ferritin' => array('value' => '45', 'unit' => 'ng/mL', 'status' => 'low', 'range' => '50-300', 'trend' => 'decreasing', 'priority' => 'high'),
											'Zinc' => array('value' => '70', 'unit' => 'µg/dL', 'status' => 'low', 'range' => '80-120', 'trend' => 'decreasing', 'priority' => 'medium')
										)
									),
									'Protein Panel' => array(
										'description' => 'Protein markers for nutritional status and organ function',
										'biomarkers' => array(
											'Total Protein' => array('value' => '7.2', 'unit' => 'g/dL', 'status' => 'normal', 'range' => '6.0-8.3', 'trend' => 'stable', 'priority' => 'normal'),
											'Albumin' => array('value' => '4.1', 'unit' => 'g/dL', 'status' => 'normal', 'range' => '3.4-5.0', 'trend' => 'stable', 'priority' => 'normal'),
											'Globulin' => array('value' => '3.1', 'unit' => 'g/dL', 'status' => 'normal', 'range' => '2.0-3.5', 'trend' => 'stable', 'priority' => 'normal'),
											'A/G Ratio' => array('value' => '1.3', 'unit' => 'ratio', 'status' => 'normal', 'range' => '1.1-2.2', 'trend' => 'stable', 'priority' => 'normal')
										)
									),
									'Liver Function' => array(
										'description' => 'Liver health and function markers',
										'biomarkers' => array(
											'ALT (Alanine Aminotransferase)' => array('value' => '35', 'unit' => 'U/L', 'status' => 'high', 'range' => '7-35', 'trend' => 'increasing', 'priority' => 'high'),
											'AST (Aspartate Aminotransferase)' => array('value' => '32', 'unit' => 'U/L', 'status' => 'normal', 'range' => '10-40', 'trend' => 'stable', 'priority' => 'normal'),
											'Alkaline Phosphatase' => array('value' => '85', 'unit' => 'U/L', 'status' => 'normal', 'range' => '44-147', 'trend' => 'stable', 'priority' => 'normal'),
											'GGT (Gamma-Glutamyl Transferase)' => array('value' => '45', 'unit' => 'U/L', 'status' => 'high', 'range' => '9-48', 'trend' => 'increasing', 'priority' => 'medium'),
											'Bilirubin Total' => array('value' => '1.2', 'unit' => 'mg/dL', 'status' => 'normal', 'range' => '0.3-1.2', 'trend' => 'stable', 'priority' => 'normal')
										)
									),
									'Complete Blood Count' => array(
										'description' => 'Comprehensive blood cell analysis for overall health',
										'biomarkers' => array(
											'White Blood Cells' => array('value' => '7.2', 'unit' => 'K/µL', 'status' => 'normal', 'range' => '4.5-11.0', 'trend' => 'stable', 'priority' => 'normal'),
											'Red Blood Cells' => array('value' => '4.8', 'unit' => 'M/µL', 'status' => 'normal', 'range' => '4.5-5.9', 'trend' => 'stable', 'priority' => 'normal'),
											'Hemoglobin' => array('value' => '14.2', 'unit' => 'g/dL', 'status' => 'normal', 'range' => '13.5-17.5', 'trend' => 'stable', 'priority' => 'normal'),
											'Hematocrit' => array('value' => '42', 'unit' => '%', 'status' => 'normal', 'range' => '41-50', 'trend' => 'stable', 'priority' => 'normal'),
											'Platelets' => array('value' => '250', 'unit' => 'K/µL', 'status' => 'normal', 'range' => '150-450', 'trend' => 'stable', 'priority' => 'normal'),
											'MCV' => array('value' => '88', 'unit' => 'fL', 'status' => 'normal', 'range' => '80-100', 'trend' => 'stable', 'priority' => 'normal'),
											'MCH' => array('value' => '29', 'unit' => 'pg', 'status' => 'normal', 'range' => '27-32', 'trend' => 'stable', 'priority' => 'normal'),
											'MCHC' => array('value' => '34', 'unit' => 'g/dL', 'status' => 'normal', 'range' => '32-36', 'trend' => 'stable', 'priority' => 'normal')
										)
									),
									'Lipid Panel' => array(
										'description' => 'Cholesterol and cardiovascular risk markers',
										'biomarkers' => array(
											'Total Cholesterol' => array('value' => '220', 'unit' => 'mg/dL', 'status' => 'high', 'range' => '<200', 'trend' => 'increasing', 'priority' => 'high'),
											'LDL Cholesterol' => array('value' => '145', 'unit' => 'mg/dL', 'status' => 'high', 'range' => '<100', 'trend' => 'increasing', 'priority' => 'high'),
											'HDL Cholesterol' => array('value' => '45', 'unit' => 'mg/dL', 'status' => 'low', 'range' => '>60', 'trend' => 'decreasing', 'priority' => 'high'),
											'Triglycerides' => array('value' => '180', 'unit' => 'mg/dL', 'status' => 'high', 'range' => '<150', 'trend' => 'increasing', 'priority' => 'high'),
											'Non-HDL Cholesterol' => array('value' => '175', 'unit' => 'mg/dL', 'status' => 'high', 'range' => '<130', 'trend' => 'increasing', 'priority' => 'high'),
											'VLDL Cholesterol' => array('value' => '36', 'unit' => 'mg/dL', 'status' => 'high', 'range' => '5-30', 'trend' => 'increasing', 'priority' => 'medium'),
											'Lipoprotein(a)' => array('value' => '45', 'unit' => 'mg/dL', 'status' => 'high', 'range' => '<30', 'trend' => 'stable', 'priority' => 'high'),
											'ApoB' => array('value' => '95', 'unit' => 'mg/dL', 'status' => 'high', 'range' => '<90', 'trend' => 'increasing', 'priority' => 'high')
										)
									),
									'Hormones' => array(
										'description' => 'Key hormones that regulate metabolism, energy, and overall wellbeing',
										'biomarkers' => array(
											'Total Testosterone' => array('value' => '320', 'unit' => 'ng/dL', 'status' => 'low', 'range' => '400-1000', 'trend' => 'decreasing', 'priority' => 'critical'),
											'Free Testosterone' => array('value' => '45', 'unit' => 'pg/mL', 'status' => 'low', 'range' => '50-250', 'trend' => 'decreasing', 'priority' => 'critical'),
											'Estradiol (E2)' => array('value' => '28', 'unit' => 'pg/mL', 'status' => 'normal', 'range' => '20-40', 'trend' => 'stable', 'priority' => 'normal'),
											'Progesterone' => array('value' => '0.8', 'unit' => 'ng/mL', 'status' => 'low', 'range' => '1.0-2.0', 'trend' => 'decreasing', 'priority' => 'high'),
											'FSH' => array('value' => '12', 'unit' => 'mIU/mL', 'status' => 'high', 'range' => '4-12', 'trend' => 'increasing', 'priority' => 'high'),
											'LH' => array('value' => '8', 'unit' => 'mIU/mL', 'status' => 'normal', 'range' => '2-9', 'trend' => 'stable', 'priority' => 'normal'),
											'DHEA-S' => array('value' => '180', 'unit' => 'µg/dL', 'status' => 'low', 'range' => '200-350', 'trend' => 'decreasing', 'priority' => 'high'),
											'Cortisol' => array('value' => '18', 'unit' => 'µg/dL', 'status' => 'high', 'range' => '6-18', 'trend' => 'increasing', 'priority' => 'high'),
											'Prolactin' => array('value' => '15', 'unit' => 'ng/mL', 'status' => 'normal', 'range' => '4-15', 'trend' => 'stable', 'priority' => 'normal'),
											'IGF-1' => array('value' => '180', 'unit' => 'ng/mL', 'status' => 'low', 'range' => '200-400', 'trend' => 'decreasing', 'priority' => 'high')
										)
									),
									'Thyroid Function' => array(
										'description' => 'Thyroid hormones that regulate metabolism and energy',
										'biomarkers' => array(
											'TSH' => array('value' => '3.2', 'unit' => 'µIU/mL', 'status' => 'high', 'range' => '0.4-2.5', 'trend' => 'increasing', 'priority' => 'high'),
											'Free T3' => array('value' => '2.8', 'unit' => 'pg/mL', 'status' => 'low', 'range' => '3.0-4.5', 'trend' => 'decreasing', 'priority' => 'high'),
											'Free T4' => array('value' => '1.1', 'unit' => 'ng/dL', 'status' => 'low', 'range' => '1.2-1.8', 'trend' => 'decreasing', 'priority' => 'high'),
											'Reverse T3' => array('value' => '25', 'unit' => 'ng/dL', 'status' => 'high', 'range' => '10-24', 'trend' => 'increasing', 'priority' => 'medium'),
											'TPO Antibodies' => array('value' => '35', 'unit' => 'IU/mL', 'status' => 'high', 'range' => '<35', 'trend' => 'stable', 'priority' => 'medium')
										)
									),
									'Vitamins & Nutrients' => array(
										'description' => 'Essential vitamins and nutrients for optimal health',
										'biomarkers' => array(
											'Vitamin D (25-OH)' => array('value' => '28', 'unit' => 'ng/mL', 'status' => 'low', 'range' => '30-80', 'trend' => 'decreasing', 'priority' => 'critical'),
											'Vitamin B12' => array('value' => '350', 'unit' => 'pg/mL', 'status' => 'low', 'range' => '400-900', 'trend' => 'decreasing', 'priority' => 'high'),
											'Folate (B9)' => array('value' => '6.5', 'unit' => 'ng/mL', 'status' => 'low', 'range' => '7-20', 'trend' => 'decreasing', 'priority' => 'high'),
											'Vitamin B6' => array('value' => '8.5', 'unit' => 'ng/mL', 'status' => 'normal', 'range' => '5-50', 'trend' => 'stable', 'priority' => 'normal'),
											'Omega-3 Index' => array('value' => '4.2', 'unit' => '%', 'status' => 'low', 'range' => '6-8', 'trend' => 'decreasing', 'priority' => 'high'),
											'Homocysteine' => array('value' => '12', 'unit' => 'µmol/L', 'status' => 'high', 'range' => '<10', 'trend' => 'increasing', 'priority' => 'high'),
											'CRP (C-Reactive Protein)' => array('value' => '3.2', 'unit' => 'mg/L', 'status' => 'high', 'range' => '<3.0', 'trend' => 'increasing', 'priority' => 'high'),
											'ESR (Erythrocyte Sedimentation Rate)' => array('value' => '18', 'unit' => 'mm/hr', 'status' => 'high', 'range' => '<15', 'trend' => 'increasing', 'priority' => 'medium')
										)
									),
									'Cardiovascular Risk' => array(
										'description' => 'Advanced cardiovascular risk assessment markers',
										'biomarkers' => array(
											'Troponin I' => array('value' => '0.02', 'unit' => 'ng/mL', 'status' => 'normal', 'range' => '<0.04', 'trend' => 'stable', 'priority' => 'normal'),
											'BNP' => array('value' => '35', 'unit' => 'pg/mL', 'status' => 'normal', 'range' => '<100', 'trend' => 'stable', 'priority' => 'normal'),
											'Uric Acid' => array('value' => '7.2', 'unit' => 'mg/dL', 'status' => 'high', 'range' => '3.4-7.0', 'trend' => 'increasing', 'priority' => 'medium'),
											'Creatine Kinase' => array('value' => '180', 'unit' => 'U/L', 'status' => 'high', 'range' => '38-174', 'trend' => 'increasing', 'priority' => 'medium'),
											'Lactate Dehydrogenase' => array('value' => '220', 'unit' => 'U/L', 'status' => 'normal', 'range' => '140-280', 'trend' => 'stable', 'priority' => 'normal')
										)
									),
									'Advanced Markers' => array(
										'description' => 'Specialized markers for comprehensive health assessment',
										'biomarkers' => array(
											'Insulin' => array('value' => '18', 'unit' => 'µIU/mL', 'status' => 'high', 'range' => '3-15', 'trend' => 'increasing', 'priority' => 'high'),
											'Leptin' => array('value' => '25', 'unit' => 'ng/mL', 'status' => 'high', 'range' => '2-20', 'trend' => 'increasing', 'priority' => 'high'),
											'Adiponectin' => array('value' => '8', 'unit' => 'µg/mL', 'status' => 'low', 'range' => '10-30', 'trend' => 'decreasing', 'priority' => 'medium'),
											'Myostatin' => array('value' => '8.5', 'unit' => 'ng/mL', 'status' => 'high', 'range' => '3-8', 'trend' => 'increasing', 'priority' => 'medium'),
											'Telomere Length' => array('value' => '6.2', 'unit' => 'kb', 'status' => 'low', 'range' => '7-10', 'trend' => 'decreasing', 'priority' => 'high'),
											'BDNF' => array('value' => '18', 'unit' => 'ng/mL', 'status' => 'low', 'range' => '20-40', 'trend' => 'decreasing', 'priority' => 'medium')
										)
									)
								);
								
								// Calculate summary statistics
								$total_biomarkers = 0;
								$abnormal_count = 0;
								$critical_count = 0;
								
								foreach ($official_biomarkers as $category => $data) {
									foreach ($data['biomarkers'] as $biomarker => $details) {
										$total_biomarkers++;
										if ($details['status'] === 'high' || $details['status'] === 'low') {
											$abnormal_count++;
										}
										if ($details['status'] === 'high' && in_array($biomarker, ['Total Testosterone', 'Free Testosterone', 'Glucose', 'HbA1c'])) {
											$critical_count++;
										}
									}
								}
								?>
								
								<!-- Biomarker Summary Dashboard -->
								<div class="biomarker-summary">
									<div class="summary-stats">
										<div class="summary-stat">
											<div class="stat-number"><?php echo esc_html( $total_biomarkers ); ?></div>
											<div class="stat-label">Core Biomarkers</div>
										</div>
										<div class="summary-stat warning">
											<div class="stat-number"><?php echo esc_html( $abnormal_count ); ?></div>
											<div class="stat-label">Abnormal Values</div>
										</div>
										<div class="summary-stat critical">
											<div class="stat-number"><?php echo esc_html( $critical_count ); ?></div>
											<div class="stat-label">Critical Issues</div>
										</div>
										<div class="summary-stat">
											<div class="stat-number">8</div>
											<div class="stat-label">Categories</div>
										</div>
									</div>
								</div>
								
								<div class="biomarkers-grid">
									<?php foreach ($official_biomarkers as $category => $data) : ?>
										<div class="collapsible-section">
											<div class="collapsible-header" onclick="toggleCollapsible(this)">
												<h4 class="collapsible-title">
													<svg class="collapsible-icon" fill="currentColor" viewBox="0 0 20 20">
														<path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
													</svg>
													<?php echo esc_html($category); ?>
												</h4>
												<div class="category-info">
													<p class="category-description"><?php echo esc_html($data['description']); ?></p>
													<span class="category-count"><?php echo count($data['biomarkers']); ?> markers</span>
												</div>
											</div>
											<div class="collapsible-content">
												<div class="biomarkers-list">
													<?php foreach ($data['biomarkers'] as $biomarker => $details) : ?>
														<div class="biomarker-item enhanced <?php echo esc_attr($details['priority']); ?>-priority">
															<div class="biomarker-info">
																<div class="biomarker-header">
																	<div class="biomarker-name"><?php echo esc_html($biomarker); ?></div>
																	<?php if ($details['priority'] === 'critical') : ?>
																		<div class="priority-badge critical">
																			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
																				<circle cx="12" cy="12" r="10"/>
																				<line x1="12" y1="8" x2="12" y2="12"/>
																				<line x1="12" y1="16" x2="12.01" y2="16"/>
																			</svg>
																			Critical
																		</div>
																	<?php elseif ($details['priority'] === 'high') : ?>
																		<div class="priority-badge high">
																			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
																				<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
																				<line x1="12" y1="9" x2="12" y2="13"/>
																				<line x1="12" y1="17" x2="12.01" y2="17"/>
																			</svg>
																			High
																		</div>
																	<?php endif; ?>
																</div>
																<div class="biomarker-value">
																	<span class="value-number"><?php echo esc_html($details['value']); ?></span>
																	<span class="value-unit"><?php echo esc_html($details['unit']); ?></span>
																</div>
															</div>
															<div class="biomarker-status">
																<div class="status-indicator <?php echo esc_attr($details['status']); ?>">
																	<span class="status-dot"></span>
																	<span class="status-label"><?php echo esc_html(ucfirst($details['status'])); ?></span>
																</div>
																<div class="biomarker-range">
																	<span class="range-label">Range:</span>
																	<span class="range-value"><?php echo esc_html($details['range']); ?></span>
																</div>
																<div class="biomarker-trend">
																	<span class="trend-icon <?php echo esc_attr($details['trend']); ?>">
																		<?php if ($details['trend'] === 'increasing') : ?>
																			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
																				<polyline points="18,15 12,9 6,15"/>
																			</svg>
																		<?php elseif ($details['trend'] === 'decreasing') : ?>
																			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
																				<polyline points="6,9 12,15 18,9"/>
																			</svg>
																		<?php else : ?>
																			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12">
																				<line x1="5" y1="12" x2="19" y2="12"/>
																			</svg>
																		<?php endif; ?>
																	</span>
																	<span class="trend-label"><?php echo esc_html(ucfirst($details['trend'])); ?></span>
																</div>
															</div>
														</div>
													<?php endforeach; ?>
												</div>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
								
								<!-- Critical Issues Alert -->
								<?php if ($critical_count > 0) : ?>
								<div class="critical-alert">
									<div class="alert-header">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
											<circle cx="12" cy="12" r="10"/>
											<line x1="12" y1="8" x2="12" y2="12"/>
											<line x1="12" y1="16" x2="12.01" y2="16"/>
										</svg>
										<h4>Critical Health Issues Detected</h4>
									</div>
									<p>You have <?php echo esc_html( $critical_count ); ?> critical biomarker values that require immediate attention. These issues are likely contributing to your symptoms and overall health concerns.</p>
									<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-primary btn-pill">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
											<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
										</svg>
										Schedule Urgent Consultation
									</a>
								</div>
								<?php endif; ?>
								
								<div class="lab-cta enhanced">
									<div class="cta-content">
										<h4>Ready to Get Your Complete Health Picture?</h4>
										<p>Order your ENNU LIFE Comprehensive Lab Panel to track these biomarkers and optimize your health journey.</p>
										<div class="cta-features">
											<div class="feature">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
													<polyline points="20,6 9,17 4,12"/>
												</svg>
												<span>50 core biomarkers tested</span>
											</div>
											<div class="feature">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
													<polyline points="20,6 9,17 4,12"/>
												</svg>
												<span>Comprehensive health analysis</span>
											</div>
											<div class="feature">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
													<polyline points="20,6 9,17 4,12"/>
												</svg>
												<span>Personalized recommendations</span>
											</div>
										</div>
										<div class="cta-buttons">
											<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-primary btn-pill">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
													<path d="M9 11H1l8-8 8 8h-8v8z"/>
												</svg>
												Order Lab Tests - $497
											</a>
											<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-secondary btn-pill">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
													<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
												</svg>
												Schedule Consultation
											</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<!-- Tab 4: My New Life -->
					<div id="tab-my-new-life" class="my-story-tab-content">
						<div class="new-life-container">
							<div class="new-life-overview">
								<div class="new-life-header">
									<h3 class="tab-section-title">My New Life Journey</h3>
									<p class="tab-subtitle">Your personalized path to optimal health and vitality</p>
								</div>
								
								<!-- Enhanced Transformation Overview -->
								<div class="transformation-overview enhanced">
									<div class="overview-stats">
										<div class="overview-stat current-score">
											<div class="stat-icon">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
													<circle cx="12" cy="12" r="10"/>
													<path d="M12 6v6l4 2"/>
												</svg>
											</div>
											<div class="stat-content">
												<div class="stat-number">6.8</div>
												<div class="stat-label">Current ENNU Score</div>
												<div class="stat-status">Good Foundation</div>
											</div>
										</div>
										<div class="overview-stat target-score">
											<div class="stat-icon">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
													<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
												</svg>
											</div>
											<div class="stat-content">
												<div class="stat-number">9.2</div>
												<div class="stat-label">Target ENNU Score</div>
												<div class="stat-status">Peak Performance</div>
											</div>
										</div>
										<div class="overview-stat improvement">
											<div class="stat-icon">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
													<path d="M7 14l3-3 2 2 7-7 2 2-9 9-4-4-2 2z"/>
												</svg>
											</div>
											<div class="stat-content">
												<div class="stat-number">+2.4</div>
												<div class="stat-label">Points to Gain</div>
												<div class="stat-status">Significant Potential</div>
											</div>
										</div>
										<div class="overview-stat timeline">
											<div class="stat-icon">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
													<path d="M12 2v20M2 12h20"/>
												</svg>
											</div>
											<div class="stat-content">
												<div class="stat-number">8-12</div>
												<div class="stat-label">Months to Goal</div>
												<div class="stat-status">Realistic Timeline</div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- Enhanced Life Coach Section -->
								<div class="life-coach-section enhanced">
									<div class="coach-card">
										<div class="coach-avatar">
											<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="48" height="48">
												<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
												<circle cx="12" cy="7" r="4"/>
											</svg>
										</div>
										<div class="coach-info">
											<h4 class="coach-name">Dr. Sarah Chen</h4>
											<p class="coach-title">Certified Health Optimization Specialist</p>
											<p class="coach-description">Your dedicated coach will guide you through your transformation journey, providing personalized strategies to optimize each health pillar. With over 15 years of experience in functional medicine and health optimization, Dr. Chen specializes in helping clients achieve sustainable health improvements.</p>
											<div class="coach-features">
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>Weekly check-ins</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>Personalized protocols</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>Progress tracking</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>24/7 support access</span>
												</div>
											</div>
											<div class="coach-pricing">
												<div class="pricing-amount">$197</div>
												<div class="pricing-period">per month</div>
											</div>
											<div class="coach-cta-buttons">
												<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-primary btn-pill coach-cta">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
														<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
													</svg>
													Schedule Free Consultation - $0
												</a>
												<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-outline btn-sm">
													Learn More About Coaching
												</a>
											</div>
										</div>
									</div>
								</div>
								
								<!-- Enhanced Transformation Journey -->
								<div class="transformation-journey enhanced">
									<h4 class="journey-title">Your Transformation Path</h4>
									<p class="journey-subtitle">Elevate each pillar to achieve your optimal ENNU LIFE SCORE</p>
									
									<div class="journey-visualization">
										<!-- Enhanced Score Comparison -->
										<div class="score-comparison enhanced">
											<div class="current-score-section">
												<h5>Current ENNU LIFE SCORE</h5>
												<div class="score-circle current">
													<div class="score-value">6.8</div>
													<div class="score-label">Current</div>
													<div class="score-status">Good Foundation</div>
												</div>
											</div>
											
											<div class="transformation-arrow enhanced">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32">
													<line x1="5" y1="12" x2="19" y2="12"/>
													<polyline points="12,5 19,12 12,19"/>
												</svg>
												<span class="arrow-label">Transform</span>
												<span class="arrow-subtitle">8-12 months</span>
											</div>
											
											<div class="target-score-section">
												<h5>Target ENNU LIFE SCORE</h5>
												<div class="score-circle target">
													<div class="score-value">9.2</div>
													<div class="score-label">Optimal</div>
													<div class="score-status">Peak Performance</div>
												</div>
											</div>
										</div>
										
										<!-- Enhanced Pillar Optimization Path -->
										<div class="pillar-optimization enhanced">
											<h5>Pillar Optimization Pathway</h5>
											<div class="pillars-progress">
												<div class="pillar-progress-item high-priority">
													<div class="pillar-header">
														<div class="pillar-info">
															<span class="pillar-name">Mind</span>
															<span class="pillar-priority">High Priority</span>
														</div>
														<div class="pillar-scores">
															<span class="pillar-current">5.2</span>
															<span class="pillar-arrow">→</span>
															<span class="pillar-target">8.5</span>
														</div>
													</div>
													<div class="pillar-progress-bar">
														<div class="progress-fill" style="width: 61.2%"></div>
													</div>
													<div class="pillar-details">
														<div class="improvement-needed">
															+3.3 points needed
														</div>
														<div class="pillar-recommendations">
															<span class="rec-tag">Stress Management</span>
															<span class="rec-tag">Sleep Optimization</span>
															<span class="rec-tag">Cognitive Enhancement</span>
														</div>
														<div class="pillar-actions">
															<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-sm btn-primary">
																Optimize Mind
															</a>
														</div>
													</div>
												</div>
												
												<div class="pillar-progress-item medium-priority">
													<div class="pillar-header">
														<div class="pillar-info">
															<span class="pillar-name">Body</span>
															<span class="pillar-priority">Medium Priority</span>
														</div>
														<div class="pillar-scores">
															<span class="pillar-current">7.1</span>
															<span class="pillar-arrow">→</span>
															<span class="pillar-target">9.0</span>
														</div>
													</div>
													<div class="pillar-progress-bar">
														<div class="progress-fill" style="width: 78.9%"></div>
													</div>
													<div class="pillar-details">
														<div class="improvement-needed">
															+1.9 points needed
														</div>
														<div class="pillar-recommendations">
															<span class="rec-tag">Strength Training</span>
															<span class="rec-tag">Recovery Optimization</span>
														</div>
														<div class="pillar-actions">
															<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-sm btn-primary">
																Optimize Body
															</a>
														</div>
													</div>
												</div>
												
												<div class="pillar-progress-item high-priority">
													<div class="pillar-header">
														<div class="pillar-info">
															<span class="pillar-name">Lifestyle</span>
															<span class="pillar-priority">High Priority</span>
														</div>
														<div class="pillar-scores">
															<span class="pillar-current">6.3</span>
															<span class="pillar-arrow">→</span>
															<span class="pillar-target">9.5</span>
														</div>
													</div>
													<div class="pillar-progress-bar">
														<div class="progress-fill" style="width: 66.3%"></div>
													</div>
													<div class="pillar-details">
														<div class="improvement-needed">
															+3.2 points needed
														</div>
														<div class="pillar-recommendations">
															<span class="rec-tag">Nutrition Plan</span>
															<span class="rec-tag">Habit Formation</span>
															<span class="rec-tag">Environment Design</span>
														</div>
														<div class="pillar-actions">
															<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-sm btn-primary">
																Optimize Lifestyle
															</a>
														</div>
													</div>
												</div>
												
												<div class="pillar-progress-item medium-priority">
													<div class="pillar-header">
														<div class="pillar-info">
															<span class="pillar-name">Aesthetics</span>
															<span class="pillar-priority">Medium Priority</span>
														</div>
														<div class="pillar-scores">
															<span class="pillar-current">8.4</span>
															<span class="pillar-arrow">→</span>
															<span class="pillar-target">9.8</span>
														</div>
													</div>
													<div class="pillar-progress-bar">
														<div class="progress-fill" style="width: 85.7%"></div>
													</div>
													<div class="pillar-details">
														<div class="improvement-needed">
															+1.4 points needed
														</div>
														<div class="pillar-recommendations">
															<span class="rec-tag">Skin Care</span>
															<span class="rec-tag">Hair Health</span>
														</div>
														<div class="pillar-actions">
															<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-sm btn-primary">
																Optimize Aesthetics
															</a>
														</div>
													</div>
												</div>
											</div>
										</div>
										
										<!-- Enhanced Journey Milestones -->
										<div class="journey-milestones enhanced">
											<h5>Transformation Milestones</h5>
											<div class="milestones-timeline">
												<div class="milestone completed">
													<div class="milestone-icon">
														<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
															<path d="M12 2v20M2 12h20"/>
														</svg>
													</div>
													<div class="milestone-content">
														<h6>Assessment Complete</h6>
														<p>Foundation established with comprehensive health evaluation</p>
														<span class="milestone-status">Completed</span>
													</div>
												</div>
												<div class="milestone current">
													<div class="milestone-icon">
														<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
															<circle cx="12" cy="12" r="3"/>
														</svg>
													</div>
													<div class="milestone-content">
														<h6>Optimization Plan</h6>
														<p>Personalized strategies developed for each health pillar</p>
														<span class="milestone-status">In Progress</span>
													</div>
												</div>
												<div class="milestone">
													<div class="milestone-icon">
														<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
															<polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/>
														</svg>
													</div>
													<div class="milestone-content">
														<h6>Active Transformation</h6>
														<p>Implementing lifestyle changes with coaching support</p>
														<span class="milestone-status">Next Phase</span>
													</div>
												</div>
												<div class="milestone">
													<div class="milestone-icon">
														<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
															<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
														</svg>
													</div>
													<div class="milestone-content">
														<h6>Peak Performance</h6>
														<p>Optimal health across all pillars - living your new life!</p>
														<span class="milestone-status">Target Goal</span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- Enhanced Success Stories -->
								<div class="success-stories-section enhanced">
									<h4 class="stories-title">Transformation Success Stories</h4>
									<p class="stories-subtitle">Real people achieving their optimal health with ENNU LIFE</p>
									
									<div class="stories-grid">
										<div class="story-card">
											<div class="story-header">
												<div class="story-avatar">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32">
														<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
														<circle cx="12" cy="7" r="4"/>
													</svg>
												</div>
												<div class="story-info">
													<h5>Michael R.</h5>
													<p class="story-age">Age 42, Executive</p>
												</div>
												<div class="story-score-improvement">
													<span class="score-before">5.2</span>
													<span class="score-arrow">→</span>
													<span class="score-after">8.7</span>
												</div>
											</div>
											<div class="story-content">
												<p>"ENNU LIFE transformed my approach to health. I went from constant fatigue to peak performance in just 8 months. The personalized coaching made all the difference."</p>
												<div class="story-highlights">
													<span class="highlight">+3.5 points</span>
													<span class="highlight">8 months</span>
													<span class="highlight">Peak Performance</span>
												</div>
											</div>
										</div>
										
										<div class="story-card">
											<div class="story-header">
												<div class="story-avatar">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32">
														<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
														<circle cx="12" cy="7" r="4"/>
													</svg>
												</div>
												<div class="story-info">
													<h5>Jennifer L.</h5>
													<p class="story-age">Age 38, Entrepreneur</p>
												</div>
												<div class="story-score-improvement">
													<span class="score-before">6.1</span>
													<span class="score-arrow">→</span>
													<span class="score-after">9.1</span>
												</div>
											</div>
											<div class="story-content">
												<p>"The pillar-based approach helped me understand exactly what I needed to optimize. My energy levels and mental clarity have never been better."</p>
												<div class="story-highlights">
													<span class="highlight">+3.0 points</span>
													<span class="highlight">10 months</span>
													<span class="highlight">Optimal Health</span>
												</div>
											</div>
										</div>
										
										<div class="story-card">
											<div class="story-header">
												<div class="story-avatar">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32">
														<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
														<circle cx="12" cy="7" r="4"/>
													</svg>
												</div>
												<div class="story-info">
													<h5>David K.</h5>
													<p class="story-age">Age 45, Consultant</p>
												</div>
												<div class="story-score-improvement">
													<span class="score-before">4.8</span>
													<span class="score-arrow">→</span>
													<span class="score-after">8.9</span>
												</div>
											</div>
											<div class="story-content">
												<p>"The comprehensive approach addressed everything from stress management to nutrition. I feel 10 years younger and more productive than ever."</p>
												<div class="story-highlights">
													<span class="highlight">+4.1 points</span>
													<span class="highlight">12 months</span>
													<span class="highlight">Life Transformation</span>
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<!-- Enhanced Transformation Programs -->
								<div class="transformation-programs enhanced">
									<h4 class="programs-title">Choose Your Transformation Path</h4>
									<p class="programs-subtitle">Select the program that best fits your goals and timeline</p>
									
									<div class="programs-grid">
										<div class="program-card starter">
											<div class="program-header">
												<h5>ENNU Full Body Diagnostic</h5>
												<div class="program-price">$599</div>
											</div>
											<div class="program-features">
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>In-Depth Biomarker Report (50+ Biomarkers)</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>Advanced Review of Lab Results</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>Personalized Clinical Recommendations</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>Comprehensive Health + Family History Analysis</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>Physical Exam</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>Your Story: Comprehensive health report</span>
												</div>
											</div>
											<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-outline btn-pill">Get Started</a>
										</div>
										
										<div class="program-card premium featured">
											<div class="program-badge">Most Popular</div>
											<div class="program-header">
												<h5>ENNU LIFE Membership</h5>
												<div class="program-price">$1788</div>
												<div class="program-savings">Pay in full and save $447</div>
											</div>
											<div class="program-features">
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>Scheduled Telehealth Visits Every 3-4 Months</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>Direct Access to a Dedicated Care Advocate</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>In-Depth Biomarker Report (50+ Biomarkers)</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>Personalized Clinical Recommendations</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>Peptide Therapy</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>Access to Premium Pharmaceuticals</span>
												</div>
											</div>
											<div class="program-pricing-options">
												<div class="pricing-option">
													<span class="price">$1341</span>
													<span class="period">Yearly (Pay in full)</span>
												</div>
												<div class="pricing-option">
													<span class="price">$149</span>
													<span class="period">Monthly</span>
												</div>
											</div>
											<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-primary btn-pill">Choose Membership</a>
										</div>
										
										<div class="program-card elite">
											<div class="program-header">
												<h5>ENNU Elite Transformation</h5>
												<div class="program-price">$4,997</div>
											</div>
											<div class="program-features">
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>12-Month Comprehensive Transformation</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>Weekly 1-on-1 Coaching Sessions</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>Quarterly Lab Testing & Analysis</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>Advanced Peptide & Hormone Therapy</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>24/7 Priority Support Access</span>
												</div>
												<div class="feature">
													<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
														<polyline points="20,6 9,17 4,12"/>
													</svg>
													<span>Guaranteed Score Improvement or Money Back</span>
												</div>
											</div>
											<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-outline btn-pill">Learn More</a>
										</div>
									</div>
								</div>
								
								<!-- Enhanced Call to Action -->
								<div class="new-life-cta enhanced">
									<div class="cta-highlight">
										<h4>Ready to Begin Your Transformation?</h4>
										<p class="cta-wordplay">ENNU LIFE = A New Life</p>
										<p>Your journey to optimal health starts with a single step. Let's unlock your potential together.</p>
										<div class="cta-stats">
											<div class="cta-stat">
												<span class="stat-number">95%</span>
												<span class="stat-label">Success Rate</span>
											</div>
											<div class="cta-stat">
												<span class="stat-number">8-12</span>
												<span class="stat-label">Months to Goal</span>
											</div>
											<div class="cta-stat">
												<span class="stat-number">24/7</span>
												<span class="stat-label">Support</span>
											</div>
											<div class="cta-stat">
												<span class="stat-number">+2.4</span>
												<span class="stat-label">Avg. Score Gain</span>
											</div>
										</div>
										<div class="cta-buttons">
											<a href="<?php echo esc_url($shortcode_instance->get_page_id_url('call')); ?>" class="btn btn-primary btn-pill btn-large">
												<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
													<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
												</svg>
												Start My Journey
											</a>
											<a href="#tab-my-assessments" class="btn btn-secondary switch-tab" data-tab="tab-my-assessments">
												Complete More Assessments
											</a>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Charts Section -->
			<div class="charts-section">
				<h2 class="section-title">Your Health Trends</h2>
				<div class="charts-grid">
					<div class="chart-card">
						<h3 class="chart-title">ENNU Life Score History</h3>
						<div class="chart-wrapper">
							<canvas id="scoreHistoryChart" width="400" height="200"></canvas>
						</div>
						<p class="chart-description">Track your overall health score over time</p>
					</div>
					
					<div class="chart-card">
						<h3 class="chart-title">BMI Trends</h3>
						<div class="chart-wrapper">
							<canvas id="bmiHistoryChart" width="400" height="200"></canvas>
						</div>
						<p class="chart-description">Monitor your body mass index changes</p>
					</div>
				</div>
			</div>

			<!-- Quick Actions -->
			<div class="quick-actions-section">
				<h2 class="section-title">Quick Actions</h2>
				<div class="quick-actions-grid">
					<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( 'assessments' ) ); ?>" class="quick-action-card">
						<div class="action-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
								<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
								<polyline points="14,2 14,8 20,8"/>
								<line x1="16" y1="13" x2="8" y2="13"/>
								<line x1="16" y1="17" x2="8" y2="17"/>
								<polyline points="10,9 9,9 8,9"/>
							</svg>
						</div>
						<h3>Take New Assessment</h3>
						<p>Complete additional assessments to get more insights</p>
					</a>
					
					<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( 'call' ) ); ?>" class="quick-action-card">
						<div class="action-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
								<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
							</svg>
						</div>
						<h3>Schedule Consultation</h3>
						<p>Book a call with our health specialists</p>
					</a>
					
					<a href="<?php echo esc_url( $shortcode_instance->get_page_id_url( 'ennu-life-score' ) ); ?>" class="quick-action-card">
						<div class="action-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
								<circle cx="12" cy="12" r="10"/>
								<circle cx="12" cy="10" r="3"/>
								<path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"/>
							</svg>
						</div>
						<h3>Get ENNU Life Score</h3>
						<p>Discover your comprehensive health score</p>
					</a>
				</div>
			</div>


</main>
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		console.log('ENNU Dashboard: DOM loaded, initializing tabs...');
		
		// Tab switching functionality
		const tabLinks = document.querySelectorAll('.my-story-tab-nav a');
		const tabContents = document.querySelectorAll('.my-story-tab-content');
		
		console.log('ENNU Dashboard: Found', tabLinks.length, 'tab links and', tabContents.length, 'tab contents');
		
		tabLinks.forEach((link, index) => {
			console.log('ENNU Dashboard: Tab link', index + 1, 'href:', link.getAttribute('href'));
			link.addEventListener('click', function(e) {
				e.preventDefault();
				console.log('ENNU Dashboard: Tab clicked:', this.getAttribute('href'));
				
				// Remove active class from all tabs and contents
				tabLinks.forEach(l => l.classList.remove('my-story-tab-active'));
				tabContents.forEach(c => c.classList.remove('my-story-tab-active'));
				
				// Add active class to clicked tab
				this.classList.add('my-story-tab-active');
				console.log('ENNU Dashboard: Added active class to tab link');
				
				// Show corresponding content
				const targetId = this.getAttribute('href').substring(1);
				const targetContent = document.getElementById(targetId);
				if (targetContent) {
					targetContent.classList.add('my-story-tab-active');
					console.log('ENNU Dashboard: Activated tab content:', targetId);
				} else {
					console.error('ENNU Dashboard: Target content not found:', targetId);
				}
			});
		});
		
		// Show first tab by default
		if (tabLinks.length > 0) {
			tabLinks[0].click();
		}
		
		// Initialize scroll reveal animations
		initializeScrollReveal();
		
		// Enhanced hover effects
		document.querySelectorAll('.animated-card, .program-card, .recommendation-card').forEach(card => {
			card.classList.add('hover-lift');
		});
		
		// Add focus-ring class to interactive elements
		document.querySelectorAll('.btn, .collapsible-header').forEach(element => {
			element.classList.add('focus-ring');
		});
	});
	
	// Collapsible section functionality
	function toggleCollapsible(header) {
		const section = header.parentElement;
		const content = section.querySelector('.collapsible-content');
		const icon = header.querySelector('.collapsible-icon');
		
		if (section.classList.contains('expanded')) {
			// Collapse
			section.classList.remove('expanded');
			content.style.maxHeight = '0';
			content.style.opacity = '0';
			content.style.padding = '0 1.5rem';
		} else {
			// Expand
			section.classList.add('expanded');
			content.style.maxHeight = content.scrollHeight + 'px';
			content.style.opacity = '1';
			content.style.padding = '1.5rem';
		}
	}
	
	// Scroll reveal functionality
	function initializeScrollReveal() {
		const observerOptions = {
			threshold: 0.1,
			rootMargin: '0px 0px -50px 0px'
		};
		
		const observer = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
				if (entry.isIntersecting) {
					entry.target.classList.add('revealed');
				}
			});
		}, observerOptions);
		
		// Observe all scroll-reveal elements
		document.querySelectorAll('.scroll-reveal').forEach(el => {
			observer.observe(el);
		});
	}
</script>

<!-- Additional CSS for new dashboard tabs -->
<style>
.biomarker-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 20px;
	margin-top: 20px;
}

.biomarker-card {
	background: var(--card-bg);
	border-radius: 8px;
	padding: 20px;
	border-left: 4px solid #ddd;
	box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.biomarker-card.biomarker-optimal {
	border-left-color: #28a745;
}

.biomarker-card.biomarker-suboptimal {
	border-left-color: #ffc107;
}

.biomarker-card.biomarker-poor {
	border-left-color: #dc3545;
}

.biomarker-card h4 {
	margin: 0 0 15px 0;
	color: var(--text-dark);
	font-size: 1.1rem;
}

.biomarker-values {
	margin-bottom: 10px;
}

.biomarker-values .current-value,
.biomarker-values .target-value {
	display: block;
	margin-bottom: 5px;
}

.biomarker-values .label {
	font-weight: 600;
	color: var(--text-light);
	margin-right: 8px;
}

.biomarker-values .value {
	font-weight: 700;
	color: var(--text-dark);
}

.test-date {
	font-size: 0.9rem;
	color: var(--text-light);
	margin-bottom: 10px;
}

.status-indicator {
	display: inline-block;
	padding: 4px 8px;
	border-radius: 4px;
	font-size: 0.8rem;
	font-weight: 600;
	text-transform: uppercase;
}

.status-indicator.status-optimal {
	background: #d4edda;
	color: #155724;
}

.status-indicator.status-suboptimal {
	background: #fff3cd;
	color: #856404;
}

.status-indicator.status-poor {
	background: #f8d7da;
	color: #721c24;
}

.no-data-message {
	text-align: center;
	padding: 40px 20px;
	color: var(--text-light);
}

.score-comparison {
	display: flex;
	align-items: center;
	justify-content: center;
	gap: 30px;
	margin: 30px 0;
	flex-wrap: wrap;
}

.current-score-card,
.new-life-score-card {
	text-align: center;
	padding: 20px;
	border-radius: 12px;
	background: var(--card-bg);
	border: 2px solid var(--border-color);
	min-width: 180px;
}

.new-life-score-card {
	border-color: #28a745;
	background: linear-gradient(135deg, #f8f9fa 0%, #e9f7ef 100%);
}

.score-display {
	font-size: 3rem;
	font-weight: 700;
	color: var(--primary-color);
	margin: 10px 0;
}

.score-display.new-life {
	color: #28a745;
}

.score-label {
	font-size: 0.9rem;
	color: var(--text-light);
	font-weight: 600;
}

.arrow-improvement {
	text-align: center;
}

.improvement-arrow {
	font-size: 2rem;
	color: #28a745;
	font-weight: 700;
}

.improvement-text {
	display: block;
	margin-top: 10px;
}

.improvement-value {
	font-size: 1.2rem;
	font-weight: 700;
	color: #28a745;
}

.improvement-percent {
	display: block;
	font-size: 0.9rem;
	color: var(--text-light);
}

.transformation-plan {
	margin-top: 40px;
}

.targets-overview {
	margin: 20px 0;
}

.targets-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 15px;
	margin-top: 15px;
}

.target-item {
	background: var(--card-bg);
	padding: 15px;
	border-radius: 8px;
	border: 1px solid var(--border-color);
}

.biomarker-name {
	font-weight: 600;
	color: var(--text-dark);
	margin-bottom: 8px;
}

.target-progress {
	display: flex;
	align-items: center;
	gap: 10px;
}

.target-progress .current {
	color: var(--text-light);
}

.target-progress .arrow {
	color: #28a745;
	font-weight: 700;
}

.target-progress .target {
	color: #28a745;
	font-weight: 600;
}

.next-steps {
	margin-top: 30px;
	padding: 20px;
	background: var(--card-bg);
	border-radius: 8px;
	border: 1px solid var(--border-color);
}

.next-steps ul {
	margin: 15px 0;
	padding-left: 20px;
}

.next-steps li {
	margin-bottom: 8px;
	color: var(--text-dark);
}

.no-new-life-data {
	text-align: center;
	padding: 40px 20px;
}

.no-new-life-data ul {
	text-align: left;
	display: inline-block;
	margin: 20px 0;
}
</style>

<!-- Tab 3: My Biomarkers -->
<div id="tab-my-biomarkers" class="my-story-tab-content">
	<div class="biomarkers-overview">
		<div class="biomarkers-header">
			<h3 class="tab-section-title">My Biomarkers</h3>
			<p class="tab-subtitle">Track your lab results and doctor recommendations</p>
		</div>
		
		<?php
		$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true ) ?: array();
		$doctor_targets = get_user_meta( $user_id, 'ennu_doctor_targets', true ) ?: array();
		
		if ( ! empty( $biomarker_data ) ) {
			echo '<div class="biomarker-grid">';
			foreach ( $biomarker_data as $biomarker => $data ) {
				$current_value = $data['value'];
				$target_value = $doctor_targets[ $biomarker ] ?? null;
				$status = $data['status'] ?? 'unknown';
				$unit = $data['unit'] ?? '';
				$test_date = $data['test_date'] ?? $data['import_date'] ?? '';
				
				echo '<div class="biomarker-card biomarker-' . esc_attr( $status ) . '">';
				echo '<h4>' . esc_html( $data['name'] ?? ucwords( str_replace( '_', ' ', $biomarker ) ) ) . '</h4>';
				echo '<div class="biomarker-values">';
				echo '<div class="current-value">';
				echo '<span class="label">Current:</span>';
				echo '<span class="value">' . esc_html( $current_value ) . ' ' . esc_html( $unit ) . '</span>';
				echo '</div>';
				if ( $target_value ) {
					echo '<div class="target-value">';
					echo '<span class="label">Target:</span>';
					echo '<span class="value">' . esc_html( $target_value ) . ' ' . esc_html( $unit ) . '</span>';
					echo '</div>';
				}
				echo '</div>';
				if ( $test_date ) {
					echo '<div class="test-date">Tested: ' . esc_html( date( 'M j, Y', strtotime( $test_date ) ) ) . '</div>';
				}
				echo '<div class="status-indicator status-' . esc_attr( $status ) . '">' . esc_html( ucfirst( $status ) ) . '</div>';
				echo '</div>';
			}
			echo '</div>';
		} else {
			echo '<div class="no-data-message">';
			echo '<h4>No Lab Data Available</h4>';
			echo '<p>Complete your lab tests to see biomarker results here. Contact your healthcare provider to get started with comprehensive testing.</p>';
			echo '<a href="#" class="ennu-btn ennu-btn-primary">Book Lab Consultation</a>';
			echo '</div>';
		}
		?>
	</div>
</div>

<!-- Tab 4: My New Life -->
<div id="tab-my-new-life" class="my-story-tab-content">
	<div class="new-life-overview">
		<div class="new-life-header">
			<h3 class="tab-section-title">My New Life</h3>
			<p class="tab-subtitle">Your potential health transformation with doctor recommendations</p>
		</div>
		
		<?php
		$current_score = get_user_meta( $user_id, 'ennu_life_score', true ) ?: 0;
		$new_life_score = get_user_meta( $user_id, 'ennu_new_life_score', true );
		$doctor_targets = get_user_meta( $user_id, 'ennu_doctor_targets', true ) ?: array();
		
		if ( ! $new_life_score && ! empty( $doctor_targets ) ) {
			if ( class_exists( 'ENNU_New_Life_Score_Calculator' ) ) {
				$health_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true ) ?: array();
				$new_life_calculator = new ENNU_New_Life_Score_Calculator( $user_id, $pillar_scores, $health_goals );
				$new_life_score = $new_life_calculator->calculate();
			}
		}
		
		if ( $new_life_score ) {
			$improvement = $new_life_score - $current_score;
			$improvement_percentage = $current_score > 0 ? ( $improvement / $current_score ) * 100 : 0;
			?>
			
			<div class="score-comparison">
				<div class="current-score-card">
					<h4>Current ENNU LIFE Score</h4>
					<div class="score-display"><?php echo esc_html( number_format( $current_score, 1 ) ); ?></div>
					<div class="score-label">Your Health Today</div>
				</div>
				
				<div class="arrow-improvement">
					<div class="improvement-arrow">→</div>
					<div class="improvement-text">
						<span class="improvement-value">+<?php echo esc_html( number_format( $improvement, 1 ) ); ?></span>
						<span class="improvement-percent">(+<?php echo esc_html( number_format( $improvement_percentage, 1 ) ); ?>%)</span>
					</div>
				</div>
				
				<div class="new-life-score-card">
					<h4>Your New Life Score</h4>
					<div class="score-display new-life"><?php echo esc_html( number_format( $new_life_score, 1 ) ); ?></div>
					<div class="score-label">Your Health Potential</div>
				</div>
			</div>
			
			<div class="transformation-plan">
				<h4>Your Transformation Roadmap</h4>
				<?php if ( ! empty( $doctor_targets ) ) : ?>
					<div class="targets-overview">
						<p>Based on your lab results, your doctor has set <?php echo count( $doctor_targets ); ?> target values to help you achieve your New Life Score:</p>
						<div class="targets-grid">
							<?php foreach ( $doctor_targets as $biomarker => $target_value ) : 
								$current_data = $biomarker_data[ $biomarker ] ?? null;
								$current_value = $current_data['value'] ?? 'Not tested';
								$unit = $current_data['unit'] ?? '';
							?>
								<div class="target-item">
									<div class="biomarker-name"><?php echo esc_html( ucwords( str_replace( '_', ' ', $biomarker ) ) ); ?></div>
									<div class="target-progress">
										<span class="current"><?php echo esc_html( $current_value ); ?></span>
										<span class="arrow">→</span>
										<span class="target"><?php echo esc_html( $target_value . ' ' . $unit ); ?></span>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
				
				<div class="next-steps">
					<h5>Recommended Next Steps:</h5>
					<ul>
						<li>Follow your personalized treatment plan</li>
						<li>Schedule regular follow-up consultations</li>
						<li>Retake assessments to track progress</li>
						<li>Monitor biomarker improvements</li>
					</ul>
					<a href="#" class="ennu-btn ennu-btn-primary">Book Follow-up Consultation</a>
				</div>
			</div>
			
		<?php } else { ?>
			<div class="no-new-life-data">
				<h4>Unlock Your New Life Score</h4>
				<p>To see your health transformation potential, you need:</p>
				<ul>
					<li>Complete lab testing with biomarker results</li>
					<li>Doctor-recommended target values</li>
					<li>Personalized treatment plan</li>
				</ul>
				<p>Book a consultation with our health optimization specialists to get started on your transformation journey.</p>
				<a href="#" class="ennu-btn ennu-btn-primary">Start Your Transformation</a>
			</div>
		<?php } ?>
	</div>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Tab switching functionality
		const tabLinks = document.querySelectorAll('.my-story-tab-nav a');
		const tabContents = document.querySelectorAll('.my-story-tab-content');
		
		tabLinks.forEach(link => {
			link.addEventListener('click', function(e) {
				e.preventDefault();
				
				// Remove active class from all tabs and contents
				tabLinks.forEach(l => l.classList.remove('my-story-tab-active'));
				tabContents.forEach(c => c.classList.remove('my-story-tab-active'));
				
				// Add active class to clicked tab
				this.classList.add('my-story-tab-active');
				
				// Show corresponding content
				const targetId = this.getAttribute('href').substring(1);
				const targetContent = document.getElementById(targetId);
				if (targetContent) {
					targetContent.classList.add('my-story-tab-active');
				}
			});
		});
		
		// Show first tab by default
		if (tabLinks.length > 0) {
			tabLinks[0].click();
		}
		
		// Initialize scroll reveal animations
		initializeScrollReveal();
		
		// Enhanced hover effects
		document.querySelectorAll('.animated-card, .program-card, .recommendation-card').forEach(card => {
			card.classList.add('hover-lift');
		});
		
		// Add focus-ring class to interactive elements
		document.querySelectorAll('.btn, .collapsible-header').forEach(element => {
			element.classList.add('focus-ring');
		});
	});
	
	// Collapsible section functionality
	function toggleCollapsible(header) {
		const section = header.parentElement;
		const content = section.querySelector('.collapsible-content');
		const icon = header.querySelector('.collapsible-icon');
		
		if (section.classList.contains('expanded')) {
			// Collapse
			section.classList.remove('expanded');
			content.style.maxHeight = '0';
			content.style.opacity = '0';
			content.style.padding = '0 1.5rem';
		} else {
			// Expand
			section.classList.add('expanded');
			content.style.maxHeight = content.scrollHeight + 'px';
			content.style.opacity = '1';
			content.style.padding = '1.5rem';
		}
	}
	
	// Scroll reveal functionality
	function initializeScrollReveal() {
		const observerOptions = {
			threshold: 0.1,
			rootMargin: '0px 0px -50px 0px'
		};
		
		const observer = new IntersectionObserver((entries) => {
			entries.forEach(entry => {
				if (entry.isIntersecting) {
					entry.target.classList.add('revealed');
				}
			});
		}, observerOptions);
		
		// Observe all scroll-reveal elements
		document.querySelectorAll('.scroll-reveal').forEach(el => {
			observer.observe(el);
		});
	}
</script>                 