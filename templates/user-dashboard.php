<?php
/**
 * Template for the user assessment dashboard - "The Bio-Metric Canvas"
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
?>
<div class="ennu-user-dashboard">
	<div class="starfield"></div>

	<div class="dashboard-main-grid">
		<aside class="dashboard-sidebar">
			<div class="user-info-header">
				<h2><?php echo esc_html( $current_user->first_name . ' ' . $current_user->last_name ); ?></h2>
				<div class="user-vitals">
					<span>Age: <?php echo esc_html( $age ); ?></span>
					<span>Gender: <?php echo esc_html( $gender ); ?></span>
					<?php if ( $height ) : ?>
						<span>Height: <?php echo esc_html( $height ); ?></span>
					<?php endif; ?>
					<?php if ( $weight ) : ?>
						<span>Weight: <?php echo esc_html( $weight ); ?></span>
					<?php endif; ?>
					<?php if ( $bmi ) : ?>
						<span>BMI: <?php echo esc_html( $bmi ); ?></span>
					<?php endif; ?>
				</div>
			</div>

			<div class="assessment-actions-footer" style="padding-top: 15px; border-top: 1px solid var(--border-color); margin-top: 15px; display: flex; flex-direction: column; gap: 10px;">
				<a href="<?php echo esc_url( home_url( '/?page_id=189' ) ); ?>" class="action-button button-report" style="flex: 1; text-align: center; background-color: var(--accent-primary); color: #fff; padding: 10px; text-decoration: none; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease; font-size: 0.9rem;">Schedule a Call</a>
				<a href="<?php echo esc_url( home_url( '/?page_id=190' ) ); ?>" class="action-button button-retake" style="flex: 1; text-align: center; background-color: var(--card-bg); color: var(--text-light); border: 1px solid var(--border-color); padding: 10px; text-decoration: none; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease; font-size: 0.9rem;">Get Your ENNU Life Score</a>
			</div>

			<div class="main-score-orb" data-score="<?php echo esc_attr( $ennu_life_score ?? 0 ); ?>">
				<svg class="pillar-orb-progress" viewBox="0 0 36 36">
					<circle class="pillar-orb-progress-bg" cx="18" cy="18" r="15.9155"></circle>
					<circle class="pillar-orb-progress-bar" cx="18" cy="18" r="15.9155" style="--score-percent: <?php echo esc_attr( ( $ennu_life_score ?? 0 ) * 10 ); ?>;"></circle>
				</svg>
				<div class="main-score-text">
					<div class="main-score-value">0.0</div>
					<div class="main-score-label">ENNU Life Score</div>
				</div>
			</div>
			<p class="main-score-insight"><?php echo esc_html( $insights['ennu_life_score'] ?? '' ); ?></p>

			<div class="pillar-scores-grid">
				<?php
				if ( is_array( $average_pillar_scores ) ) {
					foreach ( $average_pillar_scores as $pillar => $score ) {
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
						</div>
						<?php
					}
				}
				?>
			</div>
			<div class="pillar-context-display"></div>

			<?php
			$health_opt_assessment = $user_assessments['health_optimization_assessment'] ?? null;
			if ( isset( $health_optimization_report, $health_optimization_report['health_map'] ) ) :
				$health_map   = $health_optimization_report['health_map'];
				$is_completed = !empty($health_optimization_report['user_symptoms']);
				?>
				<div class="health-optimization-report-card">
					<div class="report-header">
						<h3 class="report-title">Health Optimization</h3>
						<button type="button" id="toggle-all-accordions" class="toggle-all-btn">Toggle All</button>
					</div>
					
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

					<div class="health-map-accordion">
						<?php foreach ( $health_map as $vector => $data ) :
							$is_triggered = ! empty( $data['triggered_symptoms_count'] );
							?>
							<div class="accordion-item <?php echo $is_triggered ? 'triggered' : ''; ?>" data-color-index="<?php echo esc_attr( crc32( $vector ) % 6 ); ?>">
								<div class="accordion-header">
									<h4 class="vector-title"><?php echo esc_html( $vector ); ?></h4>
									<div class="vector-counts">
										<span class="count-badge symptoms <?php echo ( $data['triggered_symptoms_count'] > 0 ) ? 'active' : ''; ?>" title="Triggered Symptoms">
											<?php echo esc_html( $data['triggered_symptoms_count'] ); ?>
										</span>
										<span class="count-badge biomarkers <?php echo ( $data['recommended_biomarkers_count'] > 0 ) ? 'active' : ''; ?>" title="Recommended Biomarkers">
											<?php echo esc_html( $data['recommended_biomarkers_count'] ); ?>
										</span>
										<span class="accordion-icon"></span>
									</div>
								</div>
								<div class="accordion-content">
									<div class="map-section">
										<h5>Symptoms</h5>
										<ul class="symptom-list">
											<?php foreach ( $data['symptoms'] as $symptom ) : ?>
												<li class="<?php echo $is_completed && in_array( $symptom, $health_optimization_report['user_symptoms'], true ) ? 'active pulsate' : ''; ?>"><?php echo esc_html( $symptom ); ?></li>
											<?php endforeach; ?>
										</ul>
									</div>
									<div class="map-section">
										<h5>Biomarkers</h5>
										<ul class="biomarker-list">
											<?php foreach ( $data['biomarkers'] as $biomarker ) : ?>
												<li class="<?php echo $is_completed && in_array( $biomarker, $health_optimization_report['recommended_biomarkers'], true ) ? 'active pulsate' : ''; ?>"><?php echo esc_html( $biomarker ); ?></li>
											<?php endforeach; ?>
										</ul>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</aside>

		<main class="dashboard-main-content">
			<div class="assessment-card-list">
				<?php 
				$assessments_to_display = array_filter(
					$user_assessments,
					function( $key ) {
						return $key !== 'health_optimization_assessment';
					},
					ARRAY_FILTER_USE_KEY
				);
				?>
				<?php foreach ( $assessments_to_display as $key => $data ) : ?>
					<div class="assessment-list-item" aria-expanded="false">
						<div class="assessment-summary">
							<div class="assessment-icon"><?php echo isset( $data['icon'] ) ? esc_html( $data['icon'] ) : '📄'; ?></div>
							<div class="assessment-info">
								<h4><?php echo isset( $data['label'] ) ? esc_html( $data['label'] ) : 'Assessment'; ?></h4>
								<p><?php echo ! empty( $data['completed'] ) && ! empty( $data['date'] ) ? 'Completed on: ' . esc_html( date( 'F j, Y', strtotime( $data['date'] ) ) ) : 'Not yet completed.'; ?></p>
							</div>
							<div class="assessment-actions">
								<?php if ( ! empty( $data['completed'] ) ) : ?>
									<span class="assessment-score-badge"><?php echo isset( $data['score'] ) ? esc_html( number_format( $data['score'], 1 ) ) : '-'; ?></span>
									<button type="button" class="details-toggle-icon"></button>
								<?php else : ?>
									<a href="<?php echo isset( $data['url'] ) ? esc_url( $data['url'] ) : '#'; ?>" class="action-button button-retake">Start Now</a>
								<?php endif; ?>
							</div>
						</div>
						<?php if ( ! empty( $data['completed'] ) ) : ?>
							<div class="category-details-container">
								<div class="category-details-inner">
									<ul class="category-score-list">
										<?php if ( is_array( $data['categories'] ) ) : ?>
											<?php foreach ( $data['categories'] as $category => $score ) : ?>
												<li class="category-score-item" style="--score: <?php echo esc_attr( $score ); ?>">
													<div class="category-info">
														<span class="category-name-group">
															<span class="category-name"><?php echo esc_html( $category ); ?></span>
														</span>
														<span class="category-score-value"><?php echo esc_html( number_format( $score, 1 ) ); ?>/10</span>
													</div>
													<div class="category-bar-bg">
														<div class="category-bar-fill"></div>
													</div>
												</li>
											<?php endforeach; ?>
										<?php endif; ?>
									</ul>
									<div class="assessment-actions-footer">
										<a href="<?php echo isset( $data['url'] ) ? esc_url( $data['url'] ) : '#'; ?>" class="action-button button-retake">Retake Assessment</a>
										<a href="<?php echo isset( $data['details_url'] ) ? esc_url( $data['details_url'] ) : '#'; ?>" class="action-button button-report">View Full Report</a>
									</div>
								</div>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<?php
			$uncompleted_assessments = array_filter(
				$user_assessments,
				function( $assessment ) {
					return ! $assessment['completed'];
				}
			);
			?>

			<?php if ( ! empty( $uncompleted_assessments ) && count( $uncompleted_assessments ) < count( $user_assessments ) - 1 ) : ?>
				<div class="next-steps-card" style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; margin-top: 25px;">
					<h3 style="font-size: 16px; font-weight: 600; color: #fff; margin: 0 0 15px 0;">Continue Your Journey</h3>
					<p style="font-size: 14px; color: var(--text-light); margin: 0 0 20px 0;">You've made a great start. Complete the following assessments to get an even more accurate ENNU LIFE SCORE.</p>
					<div class="next-steps-actions" style="display: flex; flex-direction: column; gap: 10px;">
						<?php foreach ( $uncompleted_assessments as $assessment ) : 
                            if($assessment['key'] === 'health_optimization_assessment') continue;
                        ?>
							<a href="<?php echo esc_url( $assessment['url'] ); ?>" class="action-button button-report" style="text-align: center; background-color: var(--accent-secondary); color: #fff; padding: 10px; text-decoration: none; border-radius: 0.375rem; font-weight: 600; transition: all 0.2s ease; font-size: 0.9rem;">
								Start <?php echo esc_html( $assessment['label'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<div class="historical-charts-grid">
				<div class="chart-card">
					<h4>ENNU LIFE SCORE History</h4>
					<div class="chart-wrapper">
						<canvas id="ennuLifeScoreTimelineChart"></canvas>
					</div>
				</div>
				<div class="chart-card">
					<h4>BMI Over Time</h4>
					<div class="chart-wrapper">
						<canvas id="bmiHistoryChart"></canvas>
					</div>
				</div>
			</div>
		</main>
	</div>
</div> 