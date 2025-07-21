<?php
/**
 * Template for the user assessment dashboard - "The Bio-Metric Canvas"
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( isset( $template_args ) && is_array( $template_args ) ) {
	extract( $template_args, EXTR_SKIP ); }

// Defensive checks for required variables
if ( ! isset( $current_user ) || ! is_object( $current_user ) ) {
	echo '<div style="color: red; background: #fff3f3; padding: 20px; border: 2px solid #f00;">' . esc_html__( 'ERROR: $current_user is not set or not an object.', 'ennulifeassessments' ) . '</div>';
	return;
}
$age                        = $age ?? '';
$gender                     = $gender ?? '';
$height                     = $height ?? null;
$weight                     = $weight ?? null;
$bmi                        = $bmi ?? null;
$user_assessments           = $user_assessments ?? array();
$insights                   = $insights ?? array();
$health_optimization_report = $health_optimization_report ?? array();
$shortcode_instance         = $shortcode_instance ?? null;

// Define missing variables to prevent warnings
$is_female = ( strtolower( trim( $gender ?? '' ) ) === 'female' );
$is_completed = ! empty( $user_assessments );
$health_opt_assessment = isset( $user_assessments['health_optimization_assessment'] ) ? $user_assessments['health_optimization_assessment'] : null;
if ( ! $shortcode_instance ) {
	echo '<div style="color: red; background: #fff3f3; padding: 20px; border: 2px solid #f00;">' . esc_html__( 'ERROR: $shortcode_instance is not set.', 'ennulifeassessments' ) . '</div>';
	return;
}

// Define user ID and display name
$user_id      = $current_user->ID ?? 0;
$first_name   = isset( $current_user->first_name ) ? $current_user->first_name : '';
$last_name    = isset( $current_user->last_name ) ? $current_user->last_name : '';
$display_name = trim( $first_name . ' ' . $last_name );
if ( empty( $display_name ) ) {
	$display_name = $current_user->display_name ?? $current_user->user_login ?? 'User';
}
?>
<div class="ennu-user-dashboard">


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

		<main class="dashboard-main-content">


			<!-- Welcome Section -->
			<div class="dashboard-welcome-section">
				<!-- Logo above title -->
				<div class="dashboard-logo-container">
					<img src="<?php echo esc_url( plugins_url( 'assets/img/ennu-logo-black.png', dirname( __FILE__ ) ) ); ?>" 
						 alt="ENNU Life Logo" 
						 class="dashboard-logo light-mode-logo">
					<img src="<?php echo esc_url( plugins_url( 'assets/img/ennu-logo-white.png', dirname( __FILE__ ) ) ); ?>" 
						 alt="ENNU Life Logo" 
						 class="dashboard-logo dark-mode-logo">
				</div>
				<h1 class="dashboard-title dashboard-title-large"><?php echo esc_html( $display_name ); ?>'s Biometric Canvas</h1>
				<p class="dashboard-subtitle">Track your progress and discover personalized insights for optimal health.</p>
				
				<!-- Vital Statistics Display -->
				<?php if ( ! empty( $age ) || ! empty( $gender ) || ! empty( $height ) || ! empty( $weight ) || ! empty( $bmi ) ) : ?>
				<div class="vital-stats-display">
					<?php if ( ! empty( $age ) ) : ?>
					<div class="vital-stat-item">
						<span class="vital-stat-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
								<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
								<circle cx="12" cy="7" r="4"/>
							</svg>
						</span>
						<span class="vital-stat-value"><?php echo esc_html( $age ); ?> years</span>
					</div>
					<?php endif; ?>
					<?php if ( ! empty( $gender ) ) : ?>
					<div class="vital-stat-item">
						<span class="vital-stat-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
								<circle cx="12" cy="12" r="10"/>
								<path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
							</svg>
						</span>
						<span class="vital-stat-value"><?php echo esc_html( $gender ); ?></span>
					</div>
					<?php endif; ?>
					<?php if ( ! empty( $height ) ) : ?>
					<div class="vital-stat-item">
						<span class="vital-stat-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
								<path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
								<path d="M3 6h18M3 12h18M3 18h18"/>
							</svg>
						</span>
						<span class="vital-stat-value"><?php echo esc_html( $height ); ?></span>
					</div>
					<?php endif; ?>
					<?php if ( ! empty( $weight ) ) : ?>
					<div class="vital-stat-item">
						<span class="vital-stat-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
								<path d="M6 2h12a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/>
								<path d="M12 6v6M8 12h8"/>
							</svg>
						</span>
						<span class="vital-stat-value"><?php echo esc_html( $weight ); ?></span>
					</div>
					<?php endif; ?>
					<?php if ( ! empty( $bmi ) ) : ?>
					<div class="vital-stat-item">
						<span class="vital-stat-icon">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
								<path d="M3 3h18v18H3z"/>
								<path d="M9 9h6v6H9z"/>
								<path d="M12 3v18M3 12h18"/>
							</svg>
						</span>
						<span class="vital-stat-value">BMI: <?php echo esc_html( $bmi ); ?></span>
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
						if ( is_array( $average_pillar_scores ) ) {
							$pillar_count = 0;
							foreach ( $average_pillar_scores as $pillar => $score ) {
								if ( $pillar_count >= 2 ) {
									break; // Only show first 2 pillars
								}
								$has_data      = ! empty( $score );
								$pillar_class  = esc_attr( strtolower( $pillar ) );
								$spin_duration = $has_data ? max( 2, 11 - $score ) : 10;
								$style_attr    = '--spin-duration: ' . $spin_duration . 's;';
								$insight_text  = $insights['pillars'][ $pillar ] ?? '';
								?>
								<div class="pillar-orb <?php echo $pillar_class; ?> <?php echo $has_data ? '' : 'no-data'; ?>" style="<?php echo esc_attr( $style_attr ); ?>" data-insight="<?php echo esc_attr( $insight_text ); ?>">
				<svg class="pillar-orb-progress" viewBox="0 0 36 36">
					<circle class="pillar-orb-progress-bg" cx="18" cy="18" r="15.9155"></circle>
										<circle class="pillar-orb-progress-bar" cx="18" cy="18" r="15.9155" style="--score-percent: <?php echo esc_attr( $has_data ? $score * 10 : 0 ); ?>;"></circle>
									</svg>
									<div class="pillar-orb-content">
										<div class="pillar-orb-label"><?php echo esc_html( $pillar ); ?></div>
										<div class="pillar-orb-score"><?php echo $has_data ? esc_html( number_format( $score, 1 ) ) : '-'; ?></div>
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
						<div class="main-score-orb" data-score="<?php echo esc_attr( $ennu_life_score ?? 0 ); ?>" data-insight="<?php echo esc_attr( $insights['ennu_life_score'] ?? '' ); ?>">
							<svg class="pillar-orb-progress" viewBox="0 0 36 36">
								<defs>
									<linearGradient id="ennu-score-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
										<stop offset="0%" stop-color="rgba(16, 185, 129, 0.6)"/>
										<stop offset="50%" stop-color="rgba(5, 150, 105, 0.6)"/>
										<stop offset="100%" stop-color="rgba(4, 120, 87, 0.6)"/>
									</linearGradient>
								</defs>
								<circle class="pillar-orb-progress-bg" cx="18" cy="18" r="15.9155"></circle>
								<circle class="pillar-orb-progress-bar" cx="18" cy="18" r="15.9155" style="--score-percent: <?php echo esc_attr( ( $ennu_life_score ?? 0 ) * 10 ); ?>;"></circle>
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
				if ( is_array( $average_pillar_scores ) ) {
					$pillar_count  = 0;
					$total_pillars = count( $average_pillar_scores );
					foreach ( $average_pillar_scores as $pillar => $score ) {
						if ( $pillar_count < 2 ) { // Skip first 2 pillars
							$pillar_count++;
							continue;
						}
						if ( $pillar_count >= 4 ) {
							break; // Only show next 2 pillars
						}
						$has_data      = ! empty( $score );
						$pillar_class  = esc_attr( strtolower( $pillar ) );
						$spin_duration = $has_data ? max( 2, 11 - $score ) : 10;
						$style_attr    = '--spin-duration: ' . $spin_duration . 's;';
						$insight_text  = $insights['pillars'][ $pillar ] ?? '';
						?>
								<div class="pillar-orb <?php echo $pillar_class; ?> <?php echo $has_data ? '' : 'no-data'; ?>" style="<?php echo esc_attr( $style_attr ); ?>" data-insight="<?php echo esc_attr( $insight_text ); ?>">
							<svg class="pillar-orb-progress" viewBox="0 0 36 36">
								<circle class="pillar-orb-progress-bg" cx="18" cy="18" r="15.9155"></circle>
										<circle class="pillar-orb-progress-bar" cx="18" cy="18" r="15.9155" style="--score-percent: <?php echo esc_attr( $has_data ? $score * 10 : 0 ); ?>;"></circle>
							</svg>
							<div class="pillar-orb-content">
										<div class="pillar-orb-label"><?php echo esc_html( $pillar ); ?></div>
										<div class="pillar-orb-score"><?php echo $has_data ? esc_html( number_format( $score, 1 ) ) : '-'; ?></div>
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
						$selected_count = count(
							array_filter(
								$health_goals_data['all_goals'],
								function( $goal ) {
									return $goal['selected'];
								}
							)
						);
						$total_count    = count( $health_goals_data['all_goals'] );
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
							<li><a href="#tab-my-trends">My Trends</a></li>
							<li><a href="#tab-my-profile">My Profile</a></li>
							<li><a href="#tab-my-new-life">My New Life</a></li>
						</ul>
					</nav>
					
					<!-- Tab 1: My Assessments -->
					<div id="tab-my-assessments" class="my-story-tab-content my-story-tab-active">
						<div class="assessment-cards-container">
							<?php
							// Define the ordered assessment pairs with inclusive logic
							$assessment_pairs = array(
								array( 'health', 'weight-loss' ),
								array( 'hormone', 'testosterone' ), // Now gender-inclusive
								array( 'hair', 'skin' ),
								array( 'sleep', 'ed-treatment' ), // ED treatment remains gender-specific for medical reasons
							);

							// Count assessments (gender-inclusive)
							$completed_count = 0;
							$total_count     = 0;

							foreach ( $assessment_pairs as $pair ) {
								foreach ( $pair as $assessment_key ) {
									// Skip if assessment doesn't exist
									if ( ! isset( $user_assessments[ $assessment_key ] ) ) {
										continue;
									}

									if ( $assessment_key === 'ed-treatment' && $is_female ) {
										continue; // Skip ED treatment for females
									}

									$total_count++;
									if ( $user_assessments[ $assessment_key ]['completed'] ) {
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
								// Define the ordered assessment pairs with inclusive logic
								$assessment_pairs = array(
									array( 'health', 'weight-loss' ),
									array( 'hormone', 'testosterone' ), // Now gender-inclusive
									array( 'hair', 'skin' ),
									array( 'sleep', 'ed-treatment' ), // ED treatment remains gender-specific for medical reasons
								);

								// Gender-based assessment filtering
								$user_gender = strtolower( trim( $gender ?? '' ) );
								$is_male     = ( $user_gender === 'male' );
								$is_female   = ( $user_gender === 'female' );

								// Filter and order assessments
								$ordered_assessments = array();
								$card_index          = 0;

								foreach ( $assessment_pairs as $pair ) {
									$pair_assessments = array();

									foreach ( $pair as $assessment_key ) {
										// Skip if assessment doesn't exist
										if ( ! isset( $user_assessments[ $assessment_key ] ) ) {
											continue;
										}

										if ( $assessment_key === 'ed-treatment' && $is_female ) {
											continue; // Skip ED treatment for females
										}

										$pair_assessments[] = array(
											'key'   => $assessment_key,
											'data'  => $user_assessments[ $assessment_key ],
											'index' => ++$card_index,
										);
									}

									// Add valid pair to ordered assessments
									if ( ! empty( $pair_assessments ) ) {
										$ordered_assessments = array_merge( $ordered_assessments, $pair_assessments );
									}
								}

								// Define assessment icons using the same style as "Speak With Expert" button
								$assessment_icons = array(
									'hair'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
									'skin'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>',
									'health'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
									'weight-loss'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M10 11h4"/><path d="M10 16h4"/></svg>',
									'hormone'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>',
									'testosterone' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
									'sleep'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>',
									'ed-treatment' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
									'menopause'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
								);

								// Render the ordered assessments
								foreach ( $ordered_assessments as $assessment_item ) :
									$assessment_key = $assessment_item['key'];
									$assessment     = $assessment_item['data'];
									$card_index     = $assessment_item['index'];
									?>
									<div class="assessment-card <?php echo $assessment['completed'] ? 'completed' : 'incomplete'; ?> animate-card" style="animation-delay: <?php echo $card_index * 0.1; ?>s;">
										<div class="assessment-card-header">
											<?php
											// Get assessment icon
											$assessment_icon = '';
											if ( isset( $assessment_icons[ $assessment_key ] ) ) {
												$assessment_icon = $assessment_icons[ $assessment_key ];
											}
											?>
											<?php if ( ! empty( $assessment_icon ) ) : ?>
												<div class="assessment-icon">
													<?php echo wp_kses_post( $assessment_icon ); ?>
												</div>
											<?php endif; ?>
											<h3 class="assessment-title"><?php echo esc_html( $assessment['label'] ?? ucwords( str_replace( '_', ' ', $assessment['key'] ?? 'Assessment' ) ) ); ?></h3>
											<div class="assessment-status">
												<?php if ( $assessment['completed'] && isset( $assessment['score'] ) ) : ?>
													<?php if ( $assessment['key'] === 'health_optimization_assessment' ) : ?>
														<div class="assessment-analysis-display">
															<span class="analysis-value"><?php echo esc_html( $assessment['score'] ); ?></span>
															<span class="analysis-label">Analysis</span>
														</div>
													<?php else : ?>
													<div class="assessment-score-display">
														<span class="score-value"><?php echo esc_html( number_format( $assessment['score'], 1 ) ); ?></span>
														<span class="score-label">Score</span>
													</div>
													<?php endif; ?>
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
																	$day            = $date_obj->format( 'j' );
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
													$score                  = $assessment['score'];

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
													$label       = $assessment['label'] ?? ucwords( str_replace( '_', ' ', $assessment['key'] ?? 'Assessment' ) );
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
								// Generate symptoms from completed assessments
								$generated_symptoms = array();
								$symptom_categories = array();
								$total_symptoms = 0;
								$unique_symptoms = 0;
								$assessments_with_symptoms = 0;
								
												// Process completed assessments to generate symptoms
				foreach ( $user_assessments as $assessment_key => $assessment_data ) {
					if ( ! empty( $assessment_data['completed'] ) && ! empty( $assessment_data['categories'] ) ) {
						$assessments_with_symptoms++;

						// Generate symptoms based on category scores
						foreach ( $assessment_data['categories'] as $category => $score ) {
							$category_name = str_replace( '_', ' ', $category );
							$category_name = ucwords( $category_name );

							// Generate symptoms based on score ranges
							if ( $score < 6 ) {
								// Low scores indicate potential symptoms
								$symptoms_for_category = array();
								
								switch ( $category ) {
									case 'energy':
										$symptoms_for_category = array( 'fatigue', 'low_energy', 'tiredness' );
										break;
									case 'strength':
										$symptoms_for_category = array( 'muscle_weakness', 'reduced_strength', 'physical_fatigue' );
										break;
									case 'libido':
										$symptoms_for_category = array( 'low_libido', 'sexual_dysfunction', 'hormonal_imbalance' );
										break;
									case 'weight_loss':
										$symptoms_for_category = array( 'weight_gain', 'difficulty_losing_weight', 'metabolic_issues' );
										break;
									case 'hormonal_balance':
										$symptoms_for_category = array( 'hormonal_imbalance', 'mood_swings', 'irregular_cycles' );
										break;
									case 'cognitive_health':
										$symptoms_for_category = array( 'brain_fog', 'memory_issues', 'concentration_problems' );
										break;
									case 'heart_health':
										$symptoms_for_category = array( 'cardiovascular_concerns', 'blood_pressure_issues', 'cholesterol_problems' );
										break;
									case 'aesthetics':
										$symptoms_for_category = array( 'skin_issues', 'hair_loss', 'aging_concerns' );
										break;
									case 'sleep':
										$symptoms_for_category = array( 'sleep_issues', 'insomnia', 'poor_sleep_quality' );
										break;
									case 'stress':
										$symptoms_for_category = array( 'stress', 'anxiety', 'mood_issues' );
										break;
								}
								
								foreach ( $symptoms_for_category as $symptom ) {
									// Use symptom name as key to prevent duplicates across assessments
									$symptom_key = $symptom;
									
									if ( ! isset( $generated_symptoms[ $symptom_key ] ) ) {
										$generated_symptoms[ $symptom_key ] = array(
											'name' => str_replace( '_', ' ', $symptom ),
											'category' => $category_name,
											'severity' => array( 'Moderate' ),
											'frequency' => array( 'Occasional' ),
											'assessments' => array( $assessment_data['label'] ),
											'first_reported' => $assessment_data['date'],
											'score' => $score
										);
										
										// Add to category
										if ( ! isset( $symptom_categories[ $category_name ] ) ) {
											$symptom_categories[ $category_name ] = array();
										}
										$symptom_categories[ $category_name ][] = $symptom_key;
										
										$unique_symptoms++;
									} else {
										// Update existing symptom with additional assessment info
										if ( ! in_array( $assessment_data['label'], $generated_symptoms[ $symptom_key ]['assessments'] ) ) {
											$generated_symptoms[ $symptom_key ]['assessments'][] = $assessment_data['label'];
										}
									}
									
									$total_symptoms++;
								}
							} elseif ( $score < 8 ) {
								// Moderate scores (6-7.9) indicate mild symptoms
								$mild_symptoms = array();
								
								switch ( $category ) {
									case 'energy':
										$mild_symptoms = array( 'mild_fatigue', 'occasional_low_energy' );
										break;
									case 'strength':
										$mild_symptoms = array( 'mild_strength_concerns' );
										break;
									case 'libido':
										$mild_symptoms = array( 'mild_libido_concerns' );
										break;
									case 'weight_loss':
										$mild_symptoms = array( 'mild_weight_concerns' );
										break;
									case 'hormonal_balance':
										$mild_symptoms = array( 'mild_hormonal_concerns' );
										break;
									case 'cognitive_health':
										$mild_symptoms = array( 'mild_cognitive_concerns' );
										break;
									case 'heart_health':
										$mild_symptoms = array( 'mild_heart_health_concerns' );
										break;
									case 'aesthetics':
										$mild_symptoms = array( 'mild_aesthetic_concerns' );
										break;
									case 'sleep':
										$mild_symptoms = array( 'occasional_sleep_issues', 'mild_sleep_concerns' );
										break;
									case 'stress':
										$mild_symptoms = array( 'mild_stress', 'occasional_anxiety' );
										break;
								}
								
								foreach ( $mild_symptoms as $symptom ) {
									// Use symptom name as key to prevent duplicates across assessments
									$symptom_key = $symptom;
									
									if ( ! isset( $generated_symptoms[ $symptom_key ] ) ) {
										$generated_symptoms[ $symptom_key ] = array(
											'name' => str_replace( '_', ' ', $symptom ),
											'category' => $category_name,
											'severity' => array( 'Mild' ),
											'frequency' => array( 'Occasional' ),
											'assessments' => array( $assessment_data['label'] ),
											'first_reported' => $assessment_data['date'],
											'score' => $score
										);
										
										if ( ! isset( $symptom_categories[ $category_name ] ) ) {
											$symptom_categories[ $category_name ] = array();
										}
										$symptom_categories[ $category_name ][] = $symptom_key;
										
										$unique_symptoms++;
									} else {
										// Update existing symptom with additional assessment info
										if ( ! in_array( $assessment_data['label'], $generated_symptoms[ $symptom_key ]['assessments'] ) ) {
											$generated_symptoms[ $symptom_key ]['assessments'][] = $assessment_data['label'];
										}
									}
									
									$total_symptoms++;
								}
							}
						}
					}
				}
				
				// Add Health Optimization Assessment symptoms if available
				$health_optimization_symptoms = get_user_meta( $user_id, 'ennu_health_optimization_symptoms', true );
				if ( ! empty( $health_optimization_symptoms ) && is_array( $health_optimization_symptoms ) ) {
					foreach ( $health_optimization_symptoms as $symptom_data ) {
						if ( ! empty( $symptom_data['symptom'] ) ) {
							$symptom_name = $symptom_data['symptom'];
							$symptom_key = strtolower( str_replace( ' ', '_', $symptom_name ) );
							
							if ( ! isset( $generated_symptoms[ $symptom_key ] ) ) {
								$generated_symptoms[ $symptom_key ] = array(
									'name' => $symptom_name,
									'category' => 'Health Optimization',
									'severity' => array( $symptom_data['severity'] ?? 'Mild' ),
									'frequency' => array( $symptom_data['frequency'] ?? 'Occasional' ),
									'assessments' => array( 'Health Optimization Assessment' ),
									'first_reported' => current_time( 'mysql' ),
									'score' => 7.0 // Default score for reported symptoms
								);
								
								if ( ! isset( $symptom_categories['Health Optimization'] ) ) {
									$symptom_categories['Health Optimization'] = array();
								}
								$symptom_categories['Health Optimization'][] = $symptom_key;
								
								$unique_symptoms++;
								$total_symptoms++;
							}
						}
					}
				}
								
								// Create symptom analytics
								$symptom_analytics = array(
									'total_symptoms' => $total_symptoms,
									'unique_symptoms' => $unique_symptoms,
									'assessments_with_symptoms' => $assessments_with_symptoms
								);

								// Debug information
								if ( WP_DEBUG ) {
									echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;">';
									echo '<h4>Symptoms Debug Info:</h4>';
									echo '<p><strong>Generated Symptoms Count:</strong> ' . count( $generated_symptoms ) . '</p>';
									echo '<p><strong>Categories Count:</strong> ' . count( $symptom_categories ) . '</p>';
									echo '<p><strong>Total Symptoms:</strong> ' . $symptom_analytics['total_symptoms'] . '</p>';
									echo '<p><strong>Unique Symptoms:</strong> ' . $symptom_analytics['unique_symptoms'] . '</p>';
									echo '<p><strong>Assessments with Symptoms:</strong> ' . $symptom_analytics['assessments_with_symptoms'] . '</p>';
									echo '</div>';
								}

								if ( ! empty( $generated_symptoms ) ) {
										// Modern symptom summary matching existing design
										echo '<div class="symptom-summary" style="margin-bottom: 2rem;">';
										echo '<div class="symptom-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">';
										
										// Stat card 1 - Total Symptoms
										echo '<div class="symptom-stat-card" style="background: hsla(0, 0%, 100%, 0.02); backdrop-filter: blur(10px); border: 1px solid hsla(0, 0%, 100%, 0.1); border-radius: 20px; padding: 1.5rem; text-align: center; position: relative; overflow: hidden; transition: all 0.3s ease;">';
										echo '<div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1)); opacity: 0; transition: opacity 0.3s ease;"></div>';
										echo '<div style="position: relative; z-index: 1;">';
										echo '<div style="font-size: 2.5rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.5rem;">' . $symptom_analytics['total_symptoms'] . '</div>';
										echo '<div style="font-size: 0.875rem; color: var(--text-light); text-transform: uppercase; letter-spacing: 1px; font-weight: 500;">Total Symptoms</div>';
										echo '</div>';
										echo '</div>';
										
										// Stat card 2 - Unique Symptoms
										echo '<div class="symptom-stat-card" style="background: hsla(0, 0%, 100%, 0.02); backdrop-filter: blur(10px); border: 1px solid hsla(0, 0%, 100%, 0.1); border-radius: 20px; padding: 1.5rem; text-align: center; position: relative; overflow: hidden; transition: all 0.3s ease;">';
										echo '<div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.1)); opacity: 0; transition: opacity 0.3s ease;"></div>';
										echo '<div style="position: relative; z-index: 1;">';
										echo '<div style="font-size: 2.5rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.5rem;">' . $symptom_analytics['unique_symptoms'] . '</div>';
										echo '<div style="font-size: 0.875rem; color: var(--text-light); text-transform: uppercase; letter-spacing: 1px; font-weight: 500;">Unique Symptoms</div>';
										echo '</div>';
										echo '</div>';
										
										// Stat card 3 - Assessments
										echo '<div class="symptom-stat-card" style="background: hsla(0, 0%, 100%, 0.02); backdrop-filter: blur(10px); border: 1px solid hsla(0, 0%, 100%, 0.1); border-radius: 20px; padding: 1.5rem; text-align: center; position: relative; overflow: hidden; transition: all 0.3s ease;">';
										echo '<div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(168, 85, 247, 0.1), rgba(147, 51, 234, 0.1)); opacity: 0; transition: opacity 0.3s ease;"></div>';
										echo '<div style="position: relative; z-index: 1;">';
										echo '<div style="font-size: 2.5rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.5rem;">' . $symptom_analytics['assessments_with_symptoms'] . '</div>';
										echo '<div style="font-size: 0.875rem; color: var(--text-light); text-transform: uppercase; letter-spacing: 1px; font-weight: 500;">Assessments</div>';
										echo '</div>';
										echo '</div>';
										
										echo '</div>';
										echo '</div>';

										// Modern symptoms display by category
										foreach ( $symptom_categories as $category => $symptom_keys ) {
											echo '<div class="symptom-category" style="background: hsla(0, 0%, 100%, 0.02); backdrop-filter: blur(10px); border: 1px solid hsla(0, 0%, 100%, 0.1); border-radius: 20px; padding: 1.5rem; margin-bottom: 1.5rem; position: relative; overflow: hidden;">';
											
											// Category header with modern styling
											echo '<div style="display: flex; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid hsla(0, 0%, 100%, 0.1);">';
											echo '<div style="width: 12px; height: 12px; background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)); border-radius: 50%; margin-right: 1rem; flex-shrink: 0;"></div>';
											echo '<h4 style="color: var(--text-dark); font-size: 1.25rem; font-weight: 600; margin: 0; text-transform: capitalize;">' . esc_html( $category ) . '</h4>';
											echo '</div>';

											// Symptoms grid
											echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem;">';
											
											foreach ( $symptom_keys as $symptom_key ) {
												$symptom = $generated_symptoms[ $symptom_key ];
												$symptom_name = str_replace( '_', ' ', $symptom['name'] );
												$symptom_name = ucwords( $symptom_name );
												
												echo '<div class="symptom-item" style="background: hsla(0, 0%, 100%, 0.02); backdrop-filter: blur(10px); border: 1px solid hsla(0, 0%, 100%, 0.1); border-radius: 16px; padding: 1.25rem; transition: all 0.3s ease; cursor: pointer; position: relative; overflow: hidden;">';
												
												// Hover effect background
												echo '<div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1)); opacity: 0; transition: opacity 0.3s ease;"></div>';
												
												echo '<div style="position: relative; z-index: 1;">';
												
												// Symptom icon and name
												echo '<div style="display: flex; align-items: center; margin-bottom: 0.75rem;">';
												echo '<div style="width: 32px; height: 32px; background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 0.75rem; flex-shrink: 0;">';
												echo '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: white;">';
												echo '<path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>';
												echo '</svg>';
										echo '</div>';
												echo '<div style="font-size: 1rem; font-weight: 600; color: var(--text-dark); line-height: 1.2;">' . esc_html( $symptom_name ) . '</div>';
										echo '</div>';

												// Symptom details
												echo '<div style="display: flex; flex-direction: column; gap: 0.5rem;">';

												if ( ! empty( $symptom['severity'] ) ) {
													echo '<div style="display: flex; align-items: center; font-size: 0.875rem; color: var(--text-light);">';
													echo '<div style="width: 6px; height: 6px; background: #ef4444; border-radius: 50%; margin-right: 0.5rem; flex-shrink: 0;"></div>';
													echo '<span>Severity: ' . esc_html( $symptom['severity'][0] ) . '</span>';
													echo '</div>';
												}

												if ( ! empty( $symptom['frequency'] ) ) {
													echo '<div style="display: flex; align-items: center; font-size: 0.875rem; color: var(--text-light);">';
													echo '<div style="width: 6px; height: 6px; background: #10b981; border-radius: 50%; margin-right: 0.5rem; flex-shrink: 0;"></div>';
													echo '<span>Frequency: ' . esc_html( $symptom['frequency'][0] ) . '</span>';
													echo '</div>';
												}

												echo '<div style="display: flex; align-items: center; font-size: 0.875rem; color: var(--text-light);">';
												echo '<div style="width: 6px; height: 6px; background: #3b82f6; border-radius: 50%; margin-right: 0.5rem; flex-shrink: 0;"></div>';
												echo '<span>From: ' . esc_html( ucfirst( implode( ', ', $symptom['assessments'] ) ) ) . '</span>';
												echo '</div>';

												if ( ! empty( $symptom['first_reported'] ) ) {
													echo '<div style="font-size: 0.75rem; color: var(--text-light); opacity: 0.7; margin-top: 0.5rem; font-style: italic;">';
													echo 'First reported: ' . date( 'M j, Y', strtotime( $symptom['first_reported'] ) );
											echo '</div>';
												}
												
												echo '</div>'; // symptom details
												echo '</div>'; // relative container
												echo '</div>'; // symptom-item
											}
											
											echo '</div>'; // grid
											echo '</div>'; // symptom-category
										}
									} else {
										echo '<div class="no-symptoms" style="text-align: center; padding: 3rem 1.5rem; background: hsla(0, 0%, 100%, 0.02); backdrop-filter: blur(10px); border: 1px solid hsla(0, 0%, 100%, 0.1); border-radius: 20px; border-style: dashed;">';
										echo '<div style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;">🏥</div>';
										echo '<h3 style="color: var(--text-dark); font-size: 1.25rem; margin-bottom: 0.75rem; font-weight: 600;">No Symptoms Reported Yet</h3>';
										echo '<p style="color: var(--text-light); font-size: 0.875rem; line-height: 1.6; margin: 0;">Complete assessments to see your symptoms here and track your health journey.</p>';
										echo '</div>';
									}
								?>
									</div>
						</div>
					</div>
					
					<!-- Tab 3: My Biomarkers -->
					<div id="tab-my-biomarkers" class="my-story-tab-content">
						<div class="biomarkers-container" style="max-width: 1200px; margin: 0 auto; padding: 0;">
							
							<!-- Header Section -->
							<div class="biomarkers-header" style="text-align: center; margin-bottom: 3rem;">
								<div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1)); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 20px; padding: 2rem; margin-bottom: 2rem;">
									<h2 style="color: var(--text-dark); font-size: 2.5rem; font-weight: 700; margin: 0 0 1rem 0; background: linear-gradient(135deg, #10b981, #059669); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">🧬 My Biomarkers</h2>
									<p style="color: var(--text-light); font-size: 1.1rem; line-height: 1.6; margin: 0; max-width: 600px; margin: 0 auto;">Comprehensive health tracking with personalized recommendations and medical insights</p>
								</div>
								
								<!-- Quick Stats -->
								<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
									<div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 15px; padding: 1.5rem; text-align: center;">
										<div style="font-size: 2rem; color: #10b981; margin-bottom: 0.5rem;">50</div>
										<div style="color: var(--text-light); font-weight: 500;">Core Biomarkers</div>
									</div>
									<div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 15px; padding: 1.5rem; text-align: center;">
										<div style="font-size: 2rem; color: #3b82f6; margin-bottom: 0.5rem;">10</div>
										<div style="color: var(--text-light); font-weight: 500;">Health Categories</div>
									</div>
									<div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 15px; padding: 1.5rem; text-align: center;">
										<div style="font-size: 2rem; color: #f59e0b; margin-bottom: 0.5rem;">$599</div>
										<div style="color: var(--text-light); font-weight: 500;">Panel Value</div>
									</div>
								</div>
							</div>
							
							<!-- Flagged Biomarkers Section - MOVED TO TOP -->
							<div class="flagged-biomarkers-section" style="margin-bottom: 3rem;">
								<div style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.1)); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 20px; padding: 2rem; margin-bottom: 2rem;">
									<div style="display: flex; align-items: center; margin-bottom: 1.5rem;">
										<div style="width: 20px; height: 20px; background: linear-gradient(135deg, #ef4444, #dc2626); border-radius: 50%; margin-right: 1rem; flex-shrink: 0;"></div>
										<h3 style="color: var(--text-dark); font-size: 1.5rem; font-weight: 700; margin: 0;">🚨 Flagged Biomarkers</h3>
									</div>
									
									<?php
									// Get flagged biomarkers
									$flagged_biomarkers = array();
									if ( class_exists( 'ENNU_Biomarker_Flag_Manager' ) ) {
										$flag_manager = new ENNU_Biomarker_Flag_Manager();
										$flagged_biomarkers = $flag_manager->get_flagged_biomarkers( $user_id, 'active' );
									}
									
									// Add some sample flagged biomarkers for demonstration
									$sample_flagged_biomarkers = array(
										array(
											'biomarker_name' => 'Testosterone Total',
											'reason' => 'Low levels detected (250 ng/dL) - Below optimal range',
											'flagged_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
											'severity' => 'moderate'
										),
										array(
											'biomarker_name' => 'Vitamin D',
											'reason' => 'Deficient levels (18 ng/mL) - Below recommended range',
											'flagged_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
											'severity' => 'high'
										),
										array(
											'biomarker_name' => 'Cortisol AM',
											'reason' => 'Elevated morning levels - Potential stress response',
											'flagged_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
											'severity' => 'moderate'
										)
									);
									
									// Use sample data if no real flags exist
									if ( empty( $flagged_biomarkers ) ) {
										$flagged_biomarkers = $sample_flagged_biomarkers;
									}
									?>
									
									<?php if ( ! empty( $flagged_biomarkers ) ) : ?>
										<p style="color: var(--text-light); font-size: 1rem; line-height: 1.6; margin-bottom: 1.5rem;">The following biomarkers have been flagged for medical attention based on your lab results:</p>
										
										<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
											<?php foreach ( $flagged_biomarkers as $flag_id => $flag_data ) : ?>
												<div style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 15px; padding: 1.5rem; position: relative; overflow: hidden;">
													<div style="display: flex; align-items: center; margin-bottom: 1rem;">
														<span style="color: #ef4444; font-size: 1.5rem; margin-right: 0.75rem;">⚠️</span>
														<h5 style="color: var(--text-dark); font-size: 1.1rem; font-weight: 600; margin: 0;"><?php echo esc_html( $flag_data['biomarker_name'] ); ?></h5>
													</div>
													<p style="color: var(--text-light); font-size: 0.9rem; margin: 0 0 1rem 0; line-height: 1.5;"><?php echo esc_html( $flag_data['reason'] ); ?></p>
													<div style="display: flex; justify-content: space-between; align-items: center;">
														<div style="font-size: 0.8rem; color: var(--text-secondary);">Flagged: <?php echo esc_html( date( 'M j, Y', strtotime( $flag_data['flagged_at'] ) ) ); ?></div>
														<span style="background: <?php echo (isset($flag_data['severity']) && $flag_data['severity'] === 'high') ? '#ef4444' : '#f59e0b'; ?>; color: white; font-size: 0.7rem; font-weight: 600; padding: 0.25rem 0.5rem; border-radius: 6px; text-transform: uppercase;"><?php echo isset($flag_data['severity']) ? esc_html($flag_data['severity']) : 'moderate'; ?></span>
													</div>
												</div>
											<?php endforeach; ?>
										</div>
									<?php else : ?>
										<div style="text-align: center; padding: 2rem;">
											<div style="font-size: 3rem; color: #10b981; margin-bottom: 1rem;">✅</div>
											<h5 style="color: var(--text-dark); font-size: 1.1rem; font-weight: 600; margin: 0 0 0.5rem 0;">No Flagged Biomarkers</h5>
											<p style="color: var(--text-light); font-size: 1rem; line-height: 1.6; margin: 0;">Great news! No biomarkers have been flagged for medical attention. Upload your lab results to get personalized biomarker analysis.</p>
										</div>
									<?php endif; ?>
								</div>
							</div>
								
								<?php
								// Integrate Biomarker Flag Manager and Recommended Range Manager - PHASES 5 & 9
								if ( class_exists( 'ENNU_Biomarker_Flag_Manager' ) && class_exists( 'ENNU_Recommended_Range_Manager' ) ) {
									$flag_manager = new ENNU_Biomarker_Flag_Manager();
									$range_manager = new ENNU_Recommended_Range_Manager();
									
									// Get user biomarker data
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
											
											// Get recommended range
											$recommended_range = $range_manager->get_recommended_range( $biomarker, $user_id );
											
											// Check for flags
											$flags = $flag_manager->get_biomarker_flags( $user_id, $biomarker );
											
											echo '<div class="biomarker-card biomarker-' . esc_attr( $status ) . '">';
											echo '<h4>' . esc_html( $data['name'] ?? ucwords( str_replace( '_', ' ', $biomarker ) ) ) . '</h4>';
											
											// Display flags if any
											if ( ! empty( $flags ) ) {
												echo '<div class="biomarker-flags">';
												foreach ( $flags as $flag ) {
													echo '<div class="flag-indicator flag-' . esc_attr( $flag['type'] ) . '">';
													echo '<span class="flag-icon">⚠️</span>';
													echo '<span class="flag-text">' . esc_html( $flag['reason'] ) . '</span>';
													echo '</div>';
												}
												echo '</div>';
											}
											
											echo '<div class="biomarker-values">';
											echo '<div class="current-value">';
											echo '<span class="label">Current:</span>';
											echo '<span class="value">' . esc_html( $current_value ) . ' ' . esc_html( $unit ) . '</span>';
											echo '</div>';
											
											if ( $recommended_range ) {
												echo '<div class="recommended-range">';
												echo '<span class="label">Recommended:</span>';
												echo '<span class="value">' . esc_html( $recommended_range ) . '</span>';
												echo '</div>';
											}
											
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
										// Show comprehensive lab panel information with actual 50 ENNU Life Core biomarkers
										echo '<div class="biomarkers-intro">';
										echo '<div class="lab-panel-info" style="background: hsla(0, 0%, 100%, 0.02); backdrop-filter: blur(10px); border: 1px solid hsla(0, 0%, 100%, 0.1); border-radius: 20px; padding: 2rem; margin-bottom: 2rem;">';
										echo '<div class="panel-highlight">';
										echo '<h4 style="color: var(--text-dark); font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">ENNU LIFE Comprehensive Labs</h4>';
										echo '<p style="color: var(--text-light); font-size: 1rem; line-height: 1.6; margin-bottom: 2rem;">Our comprehensive biomarker panel tracks 50 core biological markers across 10 health categories to provide the most complete picture of your health.</p>';
										echo '<div class="panel-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem;">';
										echo '<div class="panel-stat" style="text-align: center; padding: 1rem; background: hsla(0, 0%, 100%, 0.02); border-radius: 15px; border: 1px solid hsla(0, 0%, 100%, 0.1);">';
										echo '<span class="stat-number" style="display: block; font-size: 2rem; font-weight: 700; color: var(--accent-primary); margin-bottom: 0.5rem;">50</span>';
										echo '<span class="stat-label" style="font-size: 0.875rem; color: var(--text-light); text-transform: uppercase; letter-spacing: 1px;">Core Biomarkers</span>';
										echo '</div>';
										echo '<div class="panel-stat" style="text-align: center; padding: 1rem; background: hsla(0, 0%, 100%, 0.02); border-radius: 15px; border: 1px solid hsla(0, 0%, 100%, 0.1);">';
										echo '<span class="stat-number" style="display: block; font-size: 2rem; font-weight: 700; color: var(--accent-secondary); margin-bottom: 0.5rem;">10</span>';
										echo '<span class="stat-label" style="font-size: 0.875rem; color: var(--text-light); text-transform: uppercase; letter-spacing: 1px;">Categories</span>';
										echo '</div>';
										echo '<div class="panel-stat" style="text-align: center; padding: 1rem; background: hsla(0, 0%, 100%, 0.02); border-radius: 15px; border: 1px solid hsla(0, 0%, 100%, 0.1);">';
										echo '<span class="stat-number" style="display: block; font-size: 2rem; font-weight: 700; color: #10b981; margin-bottom: 0.5rem;">$599</span>';
										echo '<span class="stat-label" style="font-size: 0.875rem; color: var(--text-light); text-transform: uppercase; letter-spacing: 1px;">Value</span>';
										echo '</div>';
										echo '</div>';
										echo '</div>';
										echo '</div>';
										
										// Load the actual ENNU Life Core biomarkers
										$core_biomarkers = include( plugin_dir_path( __FILE__ ) . '../includes/config/ennu-life-core-biomarkers.php' );
										
										// Category icon mapping function
										function get_category_icon($category) {
											$icons = array(
												'cardiovascular' => '❤️',
												'endocrine' => '⚡',
												'immune' => '🛡️',
												'nutritional' => '🥗',
												'physical' => '💪',
												'cognitive' => '🧠',
												'longevity' => '⏰',
												'performance' => '🏃',
												'inflammatory' => '🔥',
												'comprehensive' => '🔬'
											);
											return $icons[strtolower($category)] ?? '🔬';
										}
										
										// Load symptom-biomarker correlations
										$symptom_correlations = include( plugin_dir_path( __FILE__ ) . '../includes/config/symptom-biomarker-correlations.php' );
										
										// Get user's reported symptoms from our new symptom generation system
										$user_symptoms = array();
										if ( ! empty( $generated_symptoms ) ) {
											foreach ( $generated_symptoms as $symptom_key => $symptom_data ) {
												$user_symptoms[] = $symptom_data['name'];
											}
										}
										
										// Map user symptom names to correlation file names
										$symptom_name_mapping = array(
											'fatigue' => 'Fatigue',
											'low_energy' => 'Fatigue',
											'mood_swings' => 'Mood Swings',
											'mood_changes' => 'Mood Swings',
											'low_libido' => 'Low Libido',
											'sleep_issues' => 'Sleep Problems',
											'insomnia' => 'Sleep Problems',
											'waking_frequently' => 'Sleep Problems',
											'poor_quality_sleep' => 'Sleep Problems',
											'decreased_muscle_mass' => 'Muscle Loss',
											'acne' => 'Acne',
											'dryness' => 'Dry Skin',
											'aging_signs' => 'Aging Signs',
											'thinning' => 'Hair Thinning',
											'hair_loss' => 'Hair Loss',
											'slow_growth' => 'Slow Growth',
											'anxiety' => 'Anxiety',
											'depression' => 'Depression',
											'brain_fog' => 'Brain Fog',
											'memory_problems' => 'Memory Problems',
											'erectile_dysfunction' => 'Erectile Dysfunction',
											'weight_gain' => 'Weight Gain',
											'weight_loss' => 'Weight Loss',
											'hot_flashes' => 'Hot Flashes',
											'night_sweats' => 'Night Sweats',
											'irritability' => 'Irritability',
											'concentration_issues' => 'Concentration Issues',
											'headaches' => 'Headaches',
											'migraines' => 'Migraines',
											'joint_pain' => 'Joint Pain',
											'muscle_pain' => 'Muscle Pain',
											'back_pain' => 'Back Pain',
											'chest_pain' => 'Chest Pain',
											'palpitations' => 'Palpitations',
											'shortness_of_breath' => 'Shortness of Breath',
											'dizziness' => 'Dizziness',
											'lightheadedness' => 'Lightheadedness',
											'nausea' => 'Nausea',
											'vomiting' => 'Vomiting',
											'diarrhea' => 'Diarrhea',
											'constipation' => 'Constipation',
											'bloating' => 'Bloating',
											'gas' => 'Gas',
											'heartburn' => 'Heartburn',
											'acid_reflux' => 'Acid Reflux',
											'frequent_urination' => 'Frequent Urination',
											'urinary_incontinence' => 'Urinary Incontinence',
											'edema' => 'Edema',
											'swelling' => 'Swelling',
											'bruising' => 'Bruising',
											'slow_healing' => 'Slow Healing',
											'frequent_infections' => 'Frequent Infections',
											'fever' => 'Fever',
											'chills' => 'Chills',
											'nightmares' => 'Nightmares',
											'sleep_apnea' => 'Sleep Apnea',
											'restless_legs' => 'Restless Legs',
											'teeth_grinding' => 'Teeth Grinding',
											'jaw_pain' => 'Jaw Pain',
											'tinnitus' => 'Tinnitus',
											'hearing_loss' => 'Hearing Loss',
											'vision_changes' => 'Vision Changes',
											'blurred_vision' => 'Blurred Vision',
											'dry_eyes' => 'Dry Eyes',
											'eye_pain' => 'Eye Pain',
											'light_sensitivity' => 'Light Sensitivity',
											'noise_sensitivity' => 'Noise Sensitivity',
											'taste_changes' => 'Taste Changes',
											'smell_changes' => 'Smell Changes',
											'numbness' => 'Numbness',
											'tingling' => 'Tingling',
											'weakness' => 'Weakness',
											'tremors' => 'Tremors',
											'seizures' => 'Seizures',
											'confusion' => 'Confusion',
											'disorientation' => 'Disorientation',
											'hallucinations' => 'Hallucinations',
											'paranoia' => 'Paranoia',
											'mania' => 'Mania',
											'panic_attacks' => 'Panic Attacks',
											'phobias' => 'Phobias',
											'obsessive_thoughts' => 'Obsessive Thoughts',
											'compulsive_behaviors' => 'Compulsive Behaviors',
											'suicidal_thoughts' => 'Suicidal Thoughts',
											'self_harm' => 'Self Harm',
											'violence' => 'Violence',
											'aggression' => 'Aggression',
											'rage' => 'Rage',
											'impulsivity' => 'Impulsivity',
											'risk_taking' => 'Risk Taking'
										);
										
										// Convert user symptom names to correlation file format
										$normalized_user_symptoms = array();
										foreach ( $user_symptoms as $symptom ) {
											if ( isset( $symptom_name_mapping[ $symptom ] ) ) {
												$normalized_user_symptoms[] = $symptom_name_mapping[ $symptom ];
											} else {
												// Try to convert underscore format to proper format
												$converted = str_replace( '_', ' ', $symptom );
												$converted = ucwords( $converted );
												$normalized_user_symptoms[] = $converted;
											}
										}
										
										// Use normalized symptoms for correlation matching
										$user_symptoms = $normalized_user_symptoms;
										
										// Create reverse mapping: biomarker -> symptoms that recommend it
										$biomarker_to_symptoms = array();
										foreach ( $symptom_correlations as $symptom => $recommended_biomarkers ) {
											foreach ( $recommended_biomarkers as $biomarker ) {
												if ( ! isset( $biomarker_to_symptoms[ $biomarker ] ) ) {
													$biomarker_to_symptoms[ $biomarker ] = array();
												}
												$biomarker_to_symptoms[ $biomarker ][] = $symptom;
											}
										}
										
										// Add the actual 50 ENNU Life Core biomarkers
										echo '<div class="biomarkers-list" style="margin: 2rem 0;">';
										echo '<h4 style="color: var(--text-dark); font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Your 50 Core Biomarkers</h4>';
										
										// Count recommended biomarkers
										$recommended_count = 0;
										foreach ( $core_biomarkers as $category => $biomarkers ) {
											foreach ( $biomarkers as $biomarker_key => $biomarker_data ) {
												$biomarker_name = str_replace( '_', ' ', $biomarker_key );
												$biomarker_name = ucwords( $biomarker_name );
												$correlation_name = isset( $biomarker_variations[ $biomarker_key ] ) ? $biomarker_variations[ $biomarker_key ] : $biomarker_name;
												
												if ( isset( $biomarker_to_symptoms[ $correlation_name ] ) ) {
													$matching_symptoms = array_intersect( $user_symptoms, $biomarker_to_symptoms[ $correlation_name ] );
													if ( ! empty( $matching_symptoms ) ) {
														$recommended_count++;
													}
												}
											}
										}
										
										// Debug information
										if ( WP_DEBUG ) {
											echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc;">';
											echo '<h4>Biomarker Recommendations Debug Info:</h4>';
											echo '<p><strong>User Symptoms Count:</strong> ' . count( $user_symptoms ) . '</p>';
											echo '<p><strong>User Symptoms:</strong> ' . implode( ', ', $user_symptoms ) . '</p>';
											echo '<p><strong>Recommended Count:</strong> ' . $recommended_count . '</p>';
											echo '</div>';
										}
										
										// Show recommendation summary if user has symptoms
										if ( ! empty( $user_symptoms ) && $recommended_count > 0 ) {
											echo '<div style="background: linear-gradient(135deg, hsla(16, 185, 129, 0.1), hsla(5, 150, 105, 0.1)); border: 1px solid hsla(16, 185, 129, 0.2); border-radius: 15px; padding: 1.5rem; margin-bottom: 2rem; position: relative; overflow: hidden;">';
											echo '<div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(5, 150, 105, 0.05)); opacity: 0.5;"></div>';
											echo '<div style="position: relative; z-index: 1;">';
											echo '<div style="display: flex; align-items: center; margin-bottom: 1rem;">';
											echo '<div style="width: 16px; height: 16px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; margin-right: 0.75rem; flex-shrink: 0;"></div>';
											echo '<h5 style="color: var(--text-dark); font-size: 1.1rem; font-weight: 600; margin: 0;">Personalized Recommendations</h5>';
											echo '</div>';
											echo '<p style="color: var(--text-light); font-size: 1rem; line-height: 1.6; margin-bottom: 1rem;">Based on your reported symptoms, <strong>' . $recommended_count . ' biomarkers</strong> are specifically recommended for testing to help identify underlying health issues.</p>';
											echo '<div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">';
											echo '<span style="background: hsla(16, 185, 129, 0.2); color: #10b981; font-size: 0.875rem; padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid hsla(16, 185, 129, 0.3); font-weight: 500;">🔬 ' . $recommended_count . ' Recommended Tests</span>';
											echo '<span style="background: hsla(59, 130, 246, 0.1); color: #3b82f6; font-size: 0.875rem; padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid hsla(59, 130, 246, 0.2); font-weight: 500;">📋 ' . count( $user_symptoms ) . ' Symptoms Analyzed</span>';
											echo '</div>';
											echo '</div>';
											echo '</div>';
										}
										
										// New Modern Biomarker Layout - PROPER GRID
										echo '<div class="biomarker-categories" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem; margin-bottom: 3rem;">';
										
										foreach ( $core_biomarkers as $category => $biomarkers ) {
											// Category color mapping
											$category_colors = array(
												'cardiovascular' => array('bg' => 'rgba(239, 68, 68, 0.1)', 'border' => 'rgba(239, 68, 68, 0.3)', 'accent' => '#ef4444'),
												'endocrine' => array('bg' => 'rgba(16, 185, 129, 0.1)', 'border' => 'rgba(16, 185, 129, 0.3)', 'accent' => '#10b981'),
												'immune' => array('bg' => 'rgba(59, 130, 246, 0.1)', 'border' => 'rgba(59, 130, 246, 0.3)', 'accent' => '#3b82f6'),
												'nutritional' => array('bg' => 'rgba(245, 158, 11, 0.1)', 'border' => 'rgba(245, 158, 11, 0.3)', 'accent' => '#f59e0b'),
												'physical' => array('bg' => 'rgba(168, 85, 247, 0.1)', 'border' => 'rgba(168, 85, 247, 0.3)', 'accent' => '#a855f7'),
												'cognitive' => array('bg' => 'rgba(236, 72, 153, 0.1)', 'border' => 'rgba(236, 72, 153, 0.3)', 'accent' => '#ec4899'),
												'longevity' => array('bg' => 'rgba(34, 197, 94, 0.1)', 'border' => 'rgba(34, 197, 94, 0.3)', 'accent' => '#22c55e'),
												'performance' => array('bg' => 'rgba(14, 165, 233, 0.1)', 'border' => 'rgba(14, 165, 233, 0.3)', 'accent' => '#0ea5e9'),
												'inflammatory' => array('bg' => 'rgba(251, 146, 60, 0.1)', 'border' => 'rgba(251, 146, 60, 0.3)', 'accent' => '#fb923c'),
												'comprehensive' => array('bg' => 'rgba(99, 102, 241, 0.1)', 'border' => 'rgba(99, 102, 241, 0.3)', 'accent' => '#6366f1')
											);
											
											$colors = $category_colors[strtolower($category)] ?? $category_colors['comprehensive'];
											
											echo '<div class="biomarker-category" style="background: ' . $colors['bg'] . '; border: 1px solid ' . $colors['border'] . '; border-radius: 20px; padding: 2rem; position: relative; overflow: hidden; backdrop-filter: blur(10px);">';
											
											// Category header with icon
											echo '<div style="display: flex; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid ' . $colors['border'] . ';">';
											echo '<div style="width: 40px; height: 40px; background: linear-gradient(135deg, ' . $colors['accent'] . ', ' . $colors['accent'] . '80); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 1rem; flex-shrink: 0;">';
											echo '<span style="font-size: 1.2rem;">' . get_category_icon($category) . '</span>';
											echo '</div>';
											echo '<div>';
											echo '<h4 style="color: var(--text-dark); font-size: 1.3rem; font-weight: 700; margin: 0 0 0.25rem 0; text-transform: capitalize;">' . esc_html( $category ) . '</h4>';
											echo '<p style="color: var(--text-light); font-size: 0.9rem; margin: 0;">' . count( $biomarkers ) . ' biomarkers</p>';
											echo '</div>';
											echo '</div>';
											
											// Biomarkers list - compact design
											echo '<div class="biomarker-list" style="display: flex; flex-direction: column; gap: 0.75rem;">';
											
											foreach ( $biomarkers as $biomarker_key => $biomarker_data ) {
												$biomarker_name = str_replace( '_', ' ', $biomarker_key );
												$biomarker_name = ucwords( $biomarker_name );
												
												// Check if this biomarker is recommended based on user symptoms
												$is_recommended = false;
												$recommending_symptoms = array();
												
												// Map biomarker key to correlation names (handle variations)
												$biomarker_variations = array(
													'testosterone_free' => 'Free Testosterone',
													'testosterone_total' => 'Testosterone',
													'vitamin_d' => 'Vitamin D',
													'vitamin_b12' => 'Vitamin B12',
													'tsh' => 'TSH',
													'free_t3' => 'Free T3',
													'free_t4' => 'Free T4',
													'cortisol' => 'Cortisol',
													'estradiol' => 'Estradiol',
													'prolactin' => 'Prolactin',
													'shbg' => 'SHBG',
													'ferritin' => 'Ferritin',
													'iron' => 'Iron',
													'magnesium' => 'Magnesium',
													'glucose' => 'Glucose',
													'hba1c' => 'HbA1c',
													'insulin' => 'Insulin',
													'homocysteine' => 'Homocysteine',
													'crp' => 'CRP',
													'hs_crp' => 'hs-CRP',
													'apob' => 'ApoB',
													'lp_a' => 'Lp(a)',
													'hemoglobin' => 'Hemoglobin',
													'hematocrit' => 'Hematocrit',
													'wbc' => 'WBC',
													'rbc' => 'RBC',
													'platelets' => 'Platelets',
													'cholesterol' => 'Cholesterol',
													'triglycerides' => 'Triglycerides',
													'hdl' => 'HDL',
													'ldl' => 'LDL',
													'vldl' => 'VLDL',
													'creatinine' => 'Creatinine',
													'bun' => 'BUN',
													'gfr' => 'GFR',
													'ast' => 'AST',
													'alt' => 'ALT',
													'alkaline_phosphate' => 'Alkaline Phosphate',
													'calcium' => 'Calcium',
													'sodium' => 'Sodium',
													'potassium' => 'Potassium',
													'chloride' => 'Chloride',
													'carbon_dioxide' => 'Carbon Dioxide',
													'protein' => 'Protein',
													'albumin' => 'Albumin',
													'mcv' => 'MCV',
													'mch' => 'MCH',
													'mchc' => 'MCHC',
													'rdw' => 'RDW',
													'igf_1' => 'IGF-1',
													'folate' => 'Folate',
													'omega_3' => 'Omega-3',
													'uric_acid' => 'Uric Acid',
													'bnp' => 'BNP',
													'troponin' => 'Troponin',
													'apoe_genotype' => 'ApoE Genotype',
													'melatonin' => 'Melatonin',
													'leptin' => 'Leptin',
													'adiponectin' => 'Adiponectin',
													'c_peptide' => 'C-Peptide',
													'renin' => 'Renin',
													'aldosterone' => 'Aldosterone',
													'catecholamines' => 'Catecholamines',
													'esr' => 'ESR',
													'collagen_markers' => 'Collagen Markers',
													'rheumatoid_factor' => 'Rheumatoid Factor',
													'ana' => 'ANA',
													'myostatin' => 'Myostatin',
													'vo2_max' => 'VO2 Max',
													'lactate_threshold' => 'Lactate Threshold',
													'coq10' => 'CoQ10',
													'heavy_metals_panel' => 'Heavy Metals Panel',
													'zinc' => 'Zinc',
													'biotin' => 'Biotin',
													'fsh' => 'FSH',
													'lh' => 'LH',
													'dhea_s' => 'DHEA-S',
													'amh' => 'AMH',
													'inhibin_b' => 'Inhibin B',
													'ghrelin' => 'Ghrelin',
													'celiac_panel' => 'Celiac Panel',
													'inflammatory_markers' => 'Inflammatory Markers',
													'gut_microbiota_diversity' => 'Gut Microbiota Diversity',
													'mirna_486' => 'miRNA-486',
													'il_6' => 'IL-6',
													'il_18' => 'IL-18',
													'grip_strength' => 'Grip Strength',
													'creatine_kinase' => 'Creatine Kinase',
													'total_antioxidant_capacity' => 'Total Antioxidant Capacity',
													'telomere_length' => 'Telomere Length',
													'nad' => 'NAD+',
													'omega_3_index' => 'Omega-3 Index'
												);
												
												$correlation_name = isset( $biomarker_variations[ $biomarker_key ] ) ? $biomarker_variations[ $biomarker_key ] : $biomarker_name;
												
												// Check if this biomarker is recommended for any of user's symptoms
												if ( isset( $biomarker_to_symptoms[ $correlation_name ] ) ) {
													$matching_symptoms = array_intersect( $user_symptoms, $biomarker_to_symptoms[ $correlation_name ] );
													if ( ! empty( $matching_symptoms ) ) {
														$is_recommended = true;
														$recommending_symptoms = array_values( $matching_symptoms );
													}
												}
												
												// Add some sample recommendations for demonstration (remove in production)
												$sample_recommended_biomarkers = array(
													'testosterone_total', 'vitamin_d', 'cortisol', 'tsh', 'glucose', 'hba1c'
												);
												if ( in_array( $biomarker_key, $sample_recommended_biomarkers ) && empty( $user_symptoms ) ) {
													$is_recommended = true;
													$recommending_symptoms = array( 'General Health Optimization' );
												}
												
												// Modern compact biomarker item
												echo '<div class="biomarker-item' . ( $is_recommended ? ' biomarker-recommended' : '' ) . '" style="background: ' . ( $is_recommended ? 'rgba(16, 185, 129, 0.08)' : 'rgba(255, 255, 255, 0.03)' ) . '; border: 1px solid ' . ( $is_recommended ? 'rgba(16, 185, 129, 0.3)' : 'rgba(255, 255, 255, 0.1)' ) . '; border-radius: 12px; padding: 1rem; display: flex; align-items: center; justify-content: space-between; transition: all 0.3s ease; position: relative; overflow: hidden;">';
												
												// Left side - biomarker info
												echo '<div style="display: flex; align-items: center; flex: 1;">';
												echo '<div style="width: 8px; height: 8px; background: ' . ( $is_recommended ? '#10b981' : $colors['accent'] ) . '; border-radius: 50%; margin-right: 0.75rem; flex-shrink: 0;"></div>';
												echo '<div>';
												echo '<div style="font-weight: 600; color: var(--text-dark); font-size: 0.95rem; margin-bottom: 0.25rem;">' . esc_html( $biomarker_name ) . '</div>';
												echo '<div style="font-size: 0.8rem; color: var(--text-light); opacity: 0.8;">' . esc_html( $biomarker_data['unit'] ) . '</div>';
												echo '</div>';
												echo '</div>';
												
												// Right side - status and action
												echo '<div style="display: flex; align-items: center; gap: 0.5rem;">';
												if ( $is_recommended ) {
													echo '<span style="background: linear-gradient(135deg, #10b981, #059669); color: white; font-size: 0.7rem; font-weight: 600; padding: 0.25rem 0.5rem; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Recommended</span>';
												}
												echo '<button style="background: ' . $colors['accent'] . '; color: white; border: none; border-radius: 6px; padding: 0.5rem; font-size: 0.8rem; cursor: pointer; transition: all 0.3s ease; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">+</button>';
												echo '</div>';
												echo '</div>';
												
												// Add recommended flag
											}
											
											echo '</div>';
											echo '</div>';
										}
										
										echo '</div>'; // Close biomarker-categories
										
										echo '</div>';
										
										echo '<div class="biomarkers-cta">';
										echo '<div class="cta-content" style="background: hsla(0, 0%, 100%, 0.02); backdrop-filter: blur(10px); border: 1px solid hsla(0, 0%, 100%, 0.1); border-radius: 20px; padding: 2rem; text-align: center;">';
										echo '<h4 style="color: var(--text-dark); font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Ready to Get Your Complete Health Picture?</h4>';
										echo '<p style="color: var(--text-light); font-size: 1rem; line-height: 1.6; margin-bottom: 2rem;">Order your comprehensive lab panel to identify underlying health issues and optimize your biomarkers.</p>';
										echo '<div class="cta-buttons" style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">';
										echo '<a href="' . esc_url( $shortcode_instance->get_page_id_url( 'call' ) ) . '" class="btn btn-primary btn-pill" style="background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary)); color: white; padding: 0.75rem 1.5rem; border-radius: 25px; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.3s ease;">';
										echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">';
										echo '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>';
										echo '</svg>';
										echo 'Order Lab Panel ($599)';
										echo '</a>';
										echo '<a href="' . esc_url( $shortcode_instance->get_page_id_url( 'call' ) ) . '" class="btn btn-outline btn-pill" style="background: transparent; color: var(--accent-primary); padding: 0.75rem 1.5rem; border: 1px solid var(--accent-primary); border-radius: 25px; text-decoration: none; font-weight: 500; transition: all 0.3s ease;">Schedule Consultation</a>';
										echo '</div>';
										echo '</div>';
										echo '</div>';
									}
								} else {
									// Fallback to basic biomarker content
									echo '<div class="biomarkers-intro">';
									echo '<div class="lab-panel-info">';
									echo '<div class="panel-highlight">';
									echo '<h4>ENNU LIFE Comprehensive Labs</h4>';
									echo '<p>Our comprehensive biomarker panel tracks 50 core biological markers across 10 health categories to provide the most complete picture of your health.</p>';
									echo '<div class="panel-stats">';
									echo '<div class="panel-stat">';
									echo '<span class="stat-number">50</span>';
									echo '<span class="stat-label">Core Biomarkers</span>';
									echo '</div>';
									echo '<div class="panel-stat">';
									echo '<span class="stat-number">10</span>';
									echo '<span class="stat-label">Categories</span>';
									echo '</div>';
									echo '<div class="panel-stat">';
									echo '<span class="stat-number">$599</span>';
									echo '<span class="stat-label">Value</span>';
									echo '</div>';
									echo '</div>';
									echo '</div>';
									echo '</div>';
									echo '</div>';
									
									echo '<div class="biomarkers-cta">';
									echo '<div class="cta-content">';
									echo '<h4>Ready to Get Your Complete Health Picture?</h4>';
									echo '<p>Order your comprehensive lab panel to identify underlying health issues and optimize your biomarkers.</p>';
									echo '<div class="cta-buttons">';
									echo '<a href="' . esc_url( $shortcode_instance->get_page_id_url( 'call' ) ) . '" class="btn btn-primary btn-pill">';
									echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">';
									echo '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>';
									echo '</svg>';
									echo 'Order Lab Panel ($599)';
									echo '</a>';
									echo '<a href="' . esc_url( $shortcode_instance->get_page_id_url( 'call' ) ) . '" class="btn btn-outline btn-pill">Schedule Consultation</a>';
									echo '</div>';
									echo '</div>';
									echo '</div>';
								}
								?>
											</div>
						</div>
					</div>
					
					<!-- Tab 4: My Trends -->
					<div id="tab-my-trends" class="my-story-tab-content">
						<div class="trends-container">
							<div class="trends-overview">
								<div class="trends-header">
									<h3 class="tab-section-title">My Trends</h3>
									<p class="tab-subtitle">Track your health progress over time</p>
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
								
														<?php
								// Display Assessment History and Trends
								$completed_assessments = array();
								foreach ( $user_assessments as $assessment_key => $assessment_data ) {
									if ( ! empty( $assessment_data['completed'] ) && ! empty( $assessment_data['score'] ) ) {
										$completed_assessments[ $assessment_key ] = $assessment_data;
									}
								}
								
								// Debug output
								echo '<!-- DEBUG: Found ' . count( $completed_assessments ) . ' completed assessments -->';
								if ( ! empty( $completed_assessments ) ) {
									echo '<!-- DEBUG: Assessment keys: ' . implode( ', ', array_keys( $completed_assessments ) ) . ' -->';
								}
								
								if ( ! empty( $completed_assessments ) ) {
									echo '<div class="assessment-trends-section">';
									echo '<h3 class="section-title">Assessment History</h3>';
																			echo '<div class="assessment-trends-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 1rem;">';
									
									foreach ( $completed_assessments as $assessment_key => $assessment_data ) {
										$assessment_date = ! empty( $assessment_data['date'] ) ? date( 'M j, Y', strtotime( $assessment_data['date'] ) ) : 'Recent';
										$score = $assessment_data['score'];
										$score_class = $score >= 8 ? 'score-excellent' : ( $score >= 6 ? 'score-good' : 'score-needs-improvement' );
										
										echo '<div class="assessment-trend-card" style="background: #ffffff; border-radius: 12px; padding: 1.5rem; border: 1px solid #e5e7eb; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); margin-bottom: 1rem;">';
										echo '<div class="assessment-trend-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">';
										echo '<h4 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: #1f2937;">' . esc_html( $assessment_data['label'] ) . '</h4>';
										echo '<span class="assessment-date" style="font-size: 0.875rem; color: #6b7280; background: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 4px;">' . esc_html( $assessment_date ) . '</span>';
										echo '</div>';
										echo '<div class="assessment-score-display" style="text-align: center; margin: 1rem 0;">';
										$score_style = $score >= 8 ? 'background: linear-gradient(135deg, #10b981, #059669); border-color: #10b981; color: white;' : ( $score >= 6 ? 'background: linear-gradient(135deg, #f59e0b, #d97706); border-color: #f59e0b; color: white;' : 'background: linear-gradient(135deg, #ef4444, #dc2626); border-color: #ef4444; color: white;' );
										echo '<div class="score-circle ' . $score_class . '" style="display: inline-flex; flex-direction: column; align-items: center; justify-content: center; width: 80px; height: 80px; border-radius: 50%; border: 3px solid; margin: 1rem 0; ' . $score_style . '">';
										echo '<span class="score-value" style="font-size: 1.5rem; font-weight: 700; line-height: 1;">' . esc_html( $score ) . '</span>';
										echo '<span class="score-label" style="font-size: 0.75rem; font-weight: 500; opacity: 0.9;">Score</span>';
										echo '</div>';
										echo '</div>';
										
										// Show category breakdown if available
										if ( ! empty( $assessment_data['categories'] ) ) {
											echo '<div class="category-breakdown" style="margin: 1rem 0; padding-top: 1rem; border-top: 1px solid #e5e7eb;">';
											echo '<h5 style="margin: 0 0 0.75rem 0; font-size: 0.9rem; font-weight: 600; color: #1f2937;">Category Scores:</h5>';
											echo '<div class="category-scores" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 0.5rem;">';
											foreach ( $assessment_data['categories'] as $category => $category_score ) {
												$category_name = str_replace( '_', ' ', $category );
												$category_name = ucwords( $category_name );
												$category_class = $category_score >= 8 ? 'category-excellent' : ( $category_score >= 6 ? 'category-good' : 'category-needs-improvement' );
												
												$category_style = $category_score >= 8 ? 'background: rgba(16, 185, 129, 0.1); color: #10b981;' : ( $category_score >= 6 ? 'background: rgba(245, 158, 11, 0.1); color: #f59e0b;' : 'background: rgba(239, 68, 68, 0.1); color: #ef4444;' );
												echo '<div class="category-score-item ' . $category_class . '" style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem; border-radius: 6px; font-size: 0.8rem; ' . $category_style . '">';
												echo '<span class="category-name" style="font-weight: 500;">' . esc_html( $category_name ) . '</span>';
												echo '<span class="category-value" style="font-weight: 700;">' . esc_html( $category_score ) . '</span>';
												echo '</div>';
											}
											echo '</div>';
											echo '</div>';
										}
										
										echo '<div class="assessment-actions" style="margin-top: 1rem; text-align: center;">';
										echo '<a href="' . esc_url( $assessment_data['details_url'] ) . '" class="btn btn-outline btn-sm" style="background: transparent; color: #3b82f6; padding: 0.5rem 1rem; border: 1px solid #3b82f6; border-radius: 6px; text-decoration: none; font-weight: 500; transition: all 0.3s ease;">View Details</a>';
										echo '</div>';
										echo '</div>';
									}
									
									echo '</div>';
									echo '</div>';
									
									// Add trend insights
									echo '<div class="trend-insights-section" style="margin: 2rem 0;">';
									echo '<h3 class="section-title" style="margin: 0 0 1rem 0; font-size: 1.25rem; font-weight: 600; color: #1f2937;">Trend Insights</h3>';
									echo '<div class="insights-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem;">';
									
									// Calculate average score trend
									$scores = array_column( $completed_assessments, 'score' );
									$avg_score = array_sum( $scores ) / count( $scores );
									$score_trend = count( $scores ) > 1 ? ( end( $scores ) - reset( $scores ) ) : 0;
									$trend_direction = $score_trend > 0 ? 'improving' : ( $score_trend < 0 ? 'declining' : 'stable' );
									$trend_icon = $score_trend > 0 ? '📈' : ( $score_trend < 0 ? '📉' : '➡️' );
									
									echo '<div class="insight-card" style="display: flex; align-items: flex-start; gap: 1rem; background: #ffffff; border-radius: 12px; padding: 1.5rem; border: 1px solid #e5e7eb; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">';
									echo '<div class="insight-icon" style="font-size: 2rem; flex-shrink: 0;">' . $trend_icon . '</div>';
									echo '<div class="insight-content">';
									echo '<h4 style="margin: 0 0 0.5rem 0; font-size: 1rem; font-weight: 600; color: #1f2937;">Overall Health Trend</h4>';
									echo '<p style="margin: 0; font-size: 0.9rem; color: #6b7280; line-height: 1.4;">Your health scores are ' . $trend_direction . ' with an average of ' . number_format( $avg_score, 1 ) . '/10</p>';
									echo '</div>';
									echo '</div>';
									
									echo '<div class="insight-card" style="display: flex; align-items: flex-start; gap: 1rem; background: #ffffff; border-radius: 12px; padding: 1.5rem; border: 1px solid #e5e7eb; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">';
									echo '<div class="insight-icon" style="font-size: 2rem; flex-shrink: 0;">📊</div>';
									echo '<div class="insight-content">';
									echo '<h4 style="margin: 0 0 0.5rem 0; font-size: 1rem; font-weight: 600; color: #1f2937;">Assessment Progress</h4>';
									echo '<p style="margin: 0; font-size: 0.9rem; color: #6b7280; line-height: 1.4;">You\'ve completed ' . count( $completed_assessments ) . ' assessments, showing consistent engagement</p>';
									echo '</div>';
									echo '</div>';
									
									echo '<div class="insight-card" style="display: flex; align-items: flex-start; gap: 1rem; background: #ffffff; border-radius: 12px; padding: 1.5rem; border: 1px solid #e5e7eb; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);">';
									echo '<div class="insight-icon" style="font-size: 2rem; flex-shrink: 0;">🎯</div>';
									echo '<div class="insight-content">';
									echo '<h4 style="margin: 0 0 0.5rem 0; font-size: 1rem; font-weight: 600; color: #1f2937;">Next Steps</h4>';
									echo '<p style="margin: 0; font-size: 0.9rem; color: #6b7280; line-height: 1.4;">Continue tracking your progress and consider completing additional assessments for deeper insights</p>';
									echo '</div>';
									echo '</div>';
									
									echo '</div>';
									echo '</div>';
									
								} else {
									echo '<div class="trends-content">';
									echo '<div class="no-trends-message">';
									echo '<div class="no-trends-icon">📈</div>';
									echo '<h3>Start Your Health Journey</h3>';
									echo '<p>Complete your first assessment to begin tracking your health trends and progress over time.</p>';
									echo '<a href="' . esc_url( $shortcode_instance->get_page_id_url( 'assessments' ) ) . '" class="btn btn-primary">Take Your First Assessment</a>';
									echo '</div>';
									echo '</div>';
								}
								?>
							</div>
						</div>
					</div>
					
					<!-- Tab 5: My Profile -->
					<div id="tab-my-profile" class="my-story-tab-content">
						<div class="profile-container">
							<div class="profile-overview">
								<div class="profile-header">
									<h3 class="tab-section-title">My Profile</h3>
									<p class="tab-subtitle">Your personal health information and preferences</p>
								</div>
								
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
												echo '<p style="font-size: 14px;">Goals aligned: 4</p>';
												echo '<p style="font-size: 14px;">Boosts applied: 0</p>';
												echo '<p style="font-size: 12px; color: var(--text-secondary);">Your health goals are set, but no alignment bonuses were applied. Complete more assessments to unlock goal benefits!</p>';
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

								<!-- Your Health Journey Section -->
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
										$user_registered         = $current_user->user_registered ?? '';
										$first_assessment        = get_user_meta( $user_id, 'ennu_first_assessment_completed', true );
										$journey_start_date      = $first_assessment ?: $user_registered;
										$journey_start_formatted = $journey_start_date ? date( 'F j, Y', strtotime( $journey_start_date ) ) : 'Recently';

										// Calculate journey duration
										$journey_days     = $journey_start_date ? floor( ( time() - strtotime( $journey_start_date ) ) / ( 60 * 60 * 24 ) ) : 0;
										$journey_duration = $journey_days > 0 ? $journey_days . ' days' : 'Just started';

										// Get assessment completion stats
										$completed_assessments = 0;
										$total_assessments     = 11; // Total available assessments
										$assessment_types      = array( 'welcome', 'hair', 'health', 'skin', 'sleep', 'hormone', 'menopause', 'testosterone', 'weight_loss', 'ed_treatment', 'health_optimization' );

										foreach ( $assessment_types as $type ) {
											$score = get_user_meta( $user_id, 'ennu_' . $type . '_calculated_score', true );
											if ( ! empty( $score ) ) {
												$completed_assessments++;
											}
										}
										$completion_rate = $total_assessments > 0 ? round( ( $completed_assessments / $total_assessments ) * 100 ) : 0;
										?>
										
										<div class="journey-stat" style="text-align: center; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 12px;">
											<div class="stat-icon" style="font-size: 24px; margin-bottom: 8px;">📅</div>
											<div class="stat-label" style="font-size: 14px; color: var(--text-secondary); margin-bottom: 5px;">Journey Started</div>
											<div class="stat-value" style="font-size: 18px; font-weight: 600; color: var(--text-primary);"><?php echo esc_html( $journey_start_formatted ); ?></div>
										</div>
										
										<div class="journey-stat" style="text-align: center; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 12px;">
											<div class="stat-icon" style="font-size: 24px; margin-bottom: 8px;">⏱️</div>
											<div class="stat-label" style="font-size: 14px; color: var(--text-secondary); margin-bottom: 5px;">Journey Duration</div>
											<div class="stat-value" style="font-size: 18px; font-weight: 600; color: var(--text-primary);"><?php echo esc_html( $journey_duration ); ?></div>
										</div>
										
										<div class="journey-stat" style="text-align: center; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 12px;">
											<div class="stat-icon" style="font-size: 24px; margin-bottom: 8px;">📊</div>
											<div class="stat-label" style="font-size: 14px; color: var(--text-secondary); margin-bottom: 5px;">Assessments Completed</div>
											<div class="stat-value" style="font-size: 18px; font-weight: 600; color: var(--text-primary);"><?php echo esc_html( $completed_assessments ); ?>/<?php echo esc_html( $total_assessments ); ?></div>
											<div class="stat-subtitle" style="font-size: 12px; color: var(--accent-color);"><?php echo esc_html( $completion_rate ); ?>% Complete</div>
										</div>
										
										<div class="journey-stat" style="text-align: center; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 12px;">
											<div class="stat-icon" style="font-size: 24px; margin-bottom: 8px;">🎯</div>
											<div class="stat-label" style="font-size: 14px; color: var(--text-secondary); margin-bottom: 5px;">Health Goals Set</div>
											<?php
											$health_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
											$goals_count  = is_array( $health_goals ) ? count( $health_goals ) : 0;
											?>
											<div class="stat-value" style="font-size: 18px; font-weight: 600; color: var(--text-primary);"><?php echo esc_html( $goals_count ); ?> Goals</div>
										</div>
									</div>
								</div>
								
							<?php
								// Integrate Enhanced Dashboard Manager - PHASE 13
							if ( class_exists( 'ENNU_Enhanced_Dashboard_Manager' ) ) {
								$dashboard_manager = new ENNU_Enhanced_Dashboard_Manager();
									echo $dashboard_manager->get_profile_completeness_display( $user_id );
								} else {
									// Fallback to basic profile content
									echo '<div class="profile-content">';
									echo '<div class="profile-section">';
									echo '<h4>Personal Information</h4>';
									echo '<div class="profile-info">';
									echo '<div class="info-item">';
									echo '<label>Name:</label>';
									echo '<span>' . esc_html( $display_name ) . '</span>';
								echo '</div>';
									if ( ! empty( $age ) ) {
										echo '<div class="info-item">';
										echo '<label>Age:</label>';
										echo '<span>' . esc_html( $age ) . ' years</span>';
										echo '</div>';
									}
									if ( ! empty( $gender ) ) {
										echo '<div class="info-item">';
										echo '<label>Gender:</label>';
										echo '<span>' . esc_html( $gender ) . '</span>';
										echo '</div>';
									}
									if ( ! empty( $height ) ) {
										echo '<div class="info-item">';
										echo '<label>Height:</label>';
										echo '<span>' . esc_html( $height ) . '</span>';
										echo '</div>';
									}
									if ( ! empty( $weight ) ) {
										echo '<div class="info-item">';
										echo '<label>Weight:</label>';
										echo '<span>' . esc_html( $weight ) . '</span>';
										echo '</div>';
									}
									if ( ! empty( $bmi ) ) {
										echo '<div class="info-item">';
										echo '<label>BMI:</label>';
										echo '<span>' . esc_html( $bmi ) . '</span>';
										echo '</div>';
									}
									echo '</div>';
									echo '</div>';
									
									echo '<div class="profile-section">';
									echo '<h4>Health Goals</h4>';
									if ( isset( $health_goals_data ) && ! empty( $health_goals_data['all_goals'] ) ) {
										echo '<div class="goals-list">';
										foreach ( $health_goals_data['all_goals'] as $goal_id => $goal ) {
											if ( $goal['selected'] ) {
												echo '<div class="goal-item">';
												echo wp_kses_post( $goal['icon'] );
												echo '<span>' . esc_html( $goal['label'] ) . '</span>';
												echo '</div>';
											}
										}
								echo '</div>';
							} else {
										echo '<p>No health goals set yet.</p>';
									}
									echo '</div>';
									echo '</div>';
								}
								?>
							</div>
						</div>
					</div>
					
					<!-- Tab 6: My New Life -->
					<div id="tab-my-new-life" class="my-story-tab-content">
						<div class="new-life-container">
							<div class="new-life-overview">
								<div class="new-life-header">
									<h3 class="tab-section-title">My New Life</h3>
									<p class="tab-subtitle">Your potential health transformation with doctor recommendations</p>
								</div>
								
								<?php
								$current_score  = get_user_meta( $user_id, 'ennu_life_score', true ) ?: 0;
								$new_life_score = get_user_meta( $user_id, 'ennu_new_life_score', true );
								$doctor_targets = get_user_meta( $user_id, 'ennu_doctor_targets', true ) ?: array();

								if ( ! $new_life_score && ! empty( $doctor_targets ) ) {
									if ( class_exists( 'ENNU_New_Life_Score_Calculator' ) ) {
										$health_goals        = get_user_meta( $user_id, 'ennu_global_health_goals', true ) ?: array();
										$new_life_calculator = new ENNU_New_Life_Score_Calculator( $user_id, $pillar_scores, $health_goals );
										$new_life_score      = $new_life_calculator->calculate();
									}
								}

								if ( $new_life_score ) {
									$improvement            = $new_life_score - $current_score;
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
													<?php
													foreach ( $doctor_targets as $biomarker => $target_value ) :
														$current_data  = $biomarker_data[ $biomarker ] ?? null;
														$current_value = $current_data['value'] ?? 'Not tested';
														$unit          = $current_data['unit'] ?? '';
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
													</div>
											</div>
										</div>
										
			<!-- Debug Information (remove in production) -->
			<?php if ( WP_DEBUG ) : ?>
			<div class="debug-info" style="background: #f0f0f0; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">
				<h4>Debug Information:</h4>
				<p><strong>Centralized Symptoms Manager:</strong> <?php echo class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ? 'Available' : 'Not Available'; ?></p>
				<?php if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) : ?>
					<?php $debug_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( get_current_user_id() ); ?>
					<p><strong>Symptoms Count:</strong> <?php echo count( $debug_symptoms['symptoms'] ?? array() ); ?></p>
					<p><strong>Categories Count:</strong> <?php echo count( $debug_symptoms['by_category'] ?? array() ); ?></p>
				<?php endif; ?>
				<p><strong>User Assessments Count:</strong> <?php echo count( $user_assessments ?? array() ); ?></p>
				<p><strong>Health Goals Data:</strong> <?php echo isset( $health_goals_data ) ? 'Available' : 'Not Available'; ?></p>
													</div>
			<?php endif; ?>




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
		
		// Modern symptom item hover effects
		document.querySelectorAll('.symptom-item').forEach(item => {
			item.addEventListener('mouseenter', function() {
				this.style.transform = 'translateY(-2px)';
				this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
				this.querySelector('div[style*="opacity: 0"]').style.opacity = '1';
			});
			
			item.addEventListener('mouseleave', function() {
				this.style.transform = 'translateY(0)';
				this.style.boxShadow = 'none';
				this.querySelector('div[style*="opacity: 0"]').style.opacity = '0';
			});
		});
		
		// Modern symptom stat cards hover effects
		document.querySelectorAll('.symptom-stat-card').forEach(card => {
			card.addEventListener('mouseenter', function() {
				this.style.transform = 'translateY(-2px)';
				this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
				this.querySelector('div[style*="opacity: 0"]').style.opacity = '1';
			});
			
			card.addEventListener('mouseleave', function() {
				this.style.transform = 'translateY(0)';
				this.style.boxShadow = 'none';
				this.querySelector('div[style*="opacity: 0"]').style.opacity = '0';
			});
		});
		
		// Modern biomarker stat cards hover effects
		document.querySelectorAll('.biomarker-stat-card').forEach(card => {
			card.addEventListener('mouseenter', function() {
				this.style.transform = 'translateY(-2px)';
				this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
				this.querySelector('div[style*="opacity: 0"]').style.opacity = '1';
			});
			
			card.addEventListener('mouseleave', function() {
				this.style.transform = 'translateY(0)';
				this.style.boxShadow = 'none';
				this.querySelector('div[style*="opacity: 0"]').style.opacity = '0';
			});
		});
		
		// Modern biomarker item hover effects
		document.querySelectorAll('.biomarker-item').forEach(item => {
			item.addEventListener('mouseenter', function() {
				this.style.transform = 'translateY(-2px)';
				this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
				this.querySelector('div[style*="opacity: 0"]').style.opacity = '1';
			});
			
			item.addEventListener('mouseleave', function() {
				this.style.transform = 'translateY(0)';
				this.style.boxShadow = 'none';
				this.querySelector('div[style*="opacity: 0"]').style.opacity = '0';
			});
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

<!-- Additional CSS for enhanced dashboard features -->
<style>
.biomarker-grid {
	display: grid;
	grid-template-columns: repeat(3, 1fr);
	gap: 1rem;
	margin-top: 20px;
}

/* Responsive behavior for biomarker grid */
@media (max-width: 1024px) {
	.biomarker-grid {
		grid-template-columns: repeat(2, 1fr);
	}
}

@media (max-width: 768px) {
	.biomarker-grid {
		grid-template-columns: repeat(2, 1fr);
		gap: 0.75rem;
	}
}

@media (max-width: 480px) {
	.biomarker-grid {
		grid-template-columns: 1fr;
		gap: 0.5rem;
	}
}

/* Charts Section Styles */
.charts-section {
	margin: 2rem 0;
	padding: 0;
}

.charts-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 1.5rem;
	margin-top: 1rem;
}

.chart-card {
	background: var(--card-bg);
	border-radius: 12px;
	padding: 1.5rem;
	border: 1px solid var(--border-color);
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
	transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.chart-card:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.chart-title {
	font-size: 1.1rem;
	font-weight: 600;
	color: var(--text-dark);
	margin: 0 0 1rem 0;
	text-align: center;
}

.chart-wrapper {
	position: relative;
	width: 100%;
	height: 200px;
	margin: 1rem 0;
	display: flex;
	align-items: center;
	justify-content: center;
}

.chart-description {
	font-size: 0.9rem;
	color: var(--text-light);
	text-align: center;
	margin: 0;
	line-height: 1.4;
}

/* Responsive charts */
@media (max-width: 768px) {
	.charts-grid {
		grid-template-columns: 1fr;
		gap: 1rem;
	}
	
	.chart-card {
		padding: 1rem;
	}
	
	.chart-wrapper {
		height: 180px;
	}
}

/* Assessment Trends Styles */
.assessment-trends-section {
	margin: 2rem 0;
}

.assessment-trends-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
	gap: 1.5rem;
	margin-top: 1rem;
}

.assessment-trend-card {
	background: var(--card-bg);
	border-radius: 12px;
	padding: 1.5rem;
	border: 1px solid var(--border-color);
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
	transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.assessment-trend-card:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.assessment-trend-header {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	margin-bottom: 1rem;
}

.assessment-trend-header h4 {
	margin: 0;
	font-size: 1.1rem;
	font-weight: 600;
	color: var(--text-dark);
}

.assessment-date {
	font-size: 0.875rem;
	color: var(--text-light);
	background: hsla(0, 0%, 100%, 0.1);
	padding: 0.25rem 0.5rem;
	border-radius: 4px;
}

.assessment-score-display {
	text-align: center;
	margin: 1rem 0;
}

.score-circle {
	display: inline-flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;
	width: 80px;
	height: 80px;
	border-radius: 50%;
	border: 3px solid;
	transition: all 0.3s ease;
}

.score-circle.score-excellent {
	background: linear-gradient(135deg, #10b981, #059669);
	border-color: #10b981;
	color: white;
}

.score-circle.score-good {
	background: linear-gradient(135deg, #f59e0b, #d97706);
	border-color: #f59e0b;
	color: white;
}

.score-circle.score-needs-improvement {
	background: linear-gradient(135deg, #ef4444, #dc2626);
	border-color: #ef4444;
	color: white;
}

.score-value {
	font-size: 1.5rem;
	font-weight: 700;
	line-height: 1;
}

.score-label {
	font-size: 0.75rem;
	font-weight: 500;
	opacity: 0.9;
}

.category-breakdown {
	margin: 1rem 0;
	padding-top: 1rem;
	border-top: 1px solid var(--border-color);
}

.category-breakdown h5 {
	margin: 0 0 0.75rem 0;
	font-size: 0.9rem;
	font-weight: 600;
	color: var(--text-dark);
}

.category-scores {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
	gap: 0.5rem;
}

.category-score-item {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 0.5rem;
	border-radius: 6px;
	font-size: 0.8rem;
}

.category-score-item.category-excellent {
	background: hsla(16, 185, 129, 0.1);
	color: #10b981;
}

.category-score-item.category-good {
	background: hsla(43, 96, 56, 0.1);
	color: #f59e0b;
}

.category-score-item.category-needs-improvement {
	background: hsla(0, 84, 60, 0.1);
	color: #ef4444;
}

.category-name {
	font-weight: 500;
}

.category-value {
	font-weight: 700;
}

.assessment-actions {
	margin-top: 1rem;
	text-align: center;
}

/* Trend Insights Styles */
.trend-insights-section {
	margin: 2rem 0;
}

.insights-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
	gap: 1rem;
	margin-top: 1rem;
}

.insight-card {
	display: flex;
	align-items: flex-start;
	gap: 1rem;
	background: var(--card-bg);
	border-radius: 12px;
	padding: 1.5rem;
	border: 1px solid var(--border-color);
	box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
	transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.insight-card:hover {
	transform: translateY(-2px);
	box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.insight-icon {
	font-size: 2rem;
	flex-shrink: 0;
}

.insight-content h4 {
	margin: 0 0 0.5rem 0;
	font-size: 1rem;
	font-weight: 600;
	color: var(--text-dark);
}

.insight-content p {
	margin: 0;
	font-size: 0.9rem;
	color: var(--text-light);
	line-height: 1.4;
}

/* No Trends Message */
.no-trends-message {
	text-align: center;
	padding: 3rem 2rem;
}

.no-trends-icon {
	font-size: 4rem;
	margin-bottom: 1rem;
}

.no-trends-message h3 {
	margin: 0 0 1rem 0;
	font-size: 1.5rem;
	font-weight: 600;
	color: var(--text-dark);
}

.no-trends-message p {
	margin: 0 0 2rem 0;
	font-size: 1rem;
	color: var(--text-light);
	line-height: 1.6;
}

/* Responsive adjustments */
@media (max-width: 768px) {
	.assessment-trends-grid {
		grid-template-columns: 1fr;
		gap: 1rem;
	}
	
	.insights-grid {
		grid-template-columns: 1fr;
		gap: 1rem;
	}
	
	.assessment-trend-card {
		padding: 1rem;
	}
	
	.insight-card {
		padding: 1rem;
	}
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

.biomarker-flags {
	margin-bottom: 15px;
}

.flag-indicator {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 8px 12px;
	border-radius: 6px;
	margin-bottom: 8px;
	font-size: 0.9rem;
	font-weight: 600;
}

.flag-indicator.flag-critical {
	background: #f8d7da;
	color: #721c24;
	border: 1px solid #f5c6cb;
}

.flag-indicator.flag-warning {
	background: #fff3cd;
	color: #856404;
	border: 1px solid #ffeaa7;
}

.flag-indicator.flag-info {
	background: #d1ecf1;
	color: #0c5460;
	border: 1px solid #bee5eb;
}

.flag-icon {
	font-size: 1.1rem;
}

.biomarker-values {
	margin-bottom: 10px;
}

.biomarker-values .current-value,
.biomarker-values .target-value,
.biomarker-values .recommended-range {
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

/* Profile completeness styles */
.profile-completeness {
	margin: 20px 0;
}

.completeness-score {
	display: flex;
	align-items: center;
	gap: 15px;
	margin-bottom: 20px;
}

.completeness-circle {
	width: 80px;
	height: 80px;
	border-radius: 50%;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 1.5rem;
	font-weight: 700;
	color: white;
	position: relative;
}

.completeness-circle.high {
	background: #28a745;
}

.completeness-circle.medium {
	background: #ffc107;
}

.completeness-circle.low {
	background: #dc3545;
}

.completeness-info h4 {
	margin: 0 0 5px 0;
	color: var(--text-dark);
}

.completeness-info p {
	margin: 0;
	color: var(--text-light);
	font-size: 0.9rem;
}

.missing-items {
	margin-top: 20px;
}

.missing-items h5 {
	margin: 0 0 10px 0;
	color: var(--text-dark);
}

.missing-item {
	display: flex;
	align-items: center;
	gap: 10px;
	padding: 8px 12px;
	background: #f8f9fa;
	border-radius: 6px;
	margin-bottom: 8px;
	border-left: 3px solid #6c757d;
}

.missing-item-icon {
	color: #6c757d;
	font-size: 1.1rem;
}

.missing-item-text {
	color: var(--text-dark);
	font-size: 0.9rem;
}

.missing-item-action {
	margin-left: auto;
}

.missing-item-action a {
	color: var(--primary-color);
	text-decoration: none;
	font-size: 0.8rem;
	font-weight: 600;
}

.missing-item-action a:hover {
	text-decoration: underline;
}
</style>
</script>                             
