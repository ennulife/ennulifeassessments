<?php
/**
 * Score Presentation Template
 *
 * Beautiful, interactive score display template
 *
 * @package ENNU_Life
 * @copyright Copyright (c) 2024, Very Good Plugins, https://verygoodplugins.com
 * @license GPL-3.0+
 * @since 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Extract data from the template loader
$score_data = $score_data ?? array();
$user_id    = $user_id ?? get_current_user_id();
$attributes = $attributes ?? array();

// Default values
$type              = $score_data['type'] ?? 'lifescore';
$title             = $score_data['title'] ?? 'Score';
$score             = $score_data['score'] ?? 0;
$interpretation    = $score_data['interpretation'] ?? array();
$pillar_scores     = $score_data['pillar_scores'] ?? array();
$history           = $score_data['history'] ?? array();
$last_updated      = $score_data['last_updated'] ?? '';
$show_pillars      = filter_var( $attributes['show_pillars'] ?? 'true', FILTER_VALIDATE_BOOLEAN );
$show_history      = filter_var( $attributes['show_history'] ?? 'false', FILTER_VALIDATE_BOOLEAN );
$show_interpretation = filter_var( $attributes['show_interpretation'] ?? 'true', FILTER_VALIDATE_BOOLEAN );
$animation         = filter_var( $attributes['animation'] ?? 'true', FILTER_VALIDATE_BOOLEAN );
$size              = $attributes['size'] ?? 'medium';
$theme             = $attributes['theme'] ?? 'light';

// Size classes
$size_classes = array(
	'small'  => 'ennu-score-small',
	'medium' => 'ennu-score-medium',
	'large'  => 'ennu-score-large',
);

$size_class = $size_classes[ $size ] ?? 'ennu-score-medium';
?>

<div class="ennu-score-presentation <?php echo esc_attr( $size_class ); ?> ennu-theme-<?php echo esc_attr( $theme ); ?>" 
     data-type="<?php echo esc_attr( $type ); ?>"
     data-score="<?php echo esc_attr( $score ); ?>"
     data-animation="<?php echo $animation ? 'true' : 'false'; ?>">
	
	<!-- Main Score Display -->
	<div class="ennu-score-main">
		<div class="ennu-score-orb-container">
			<div class="ennu-score-orb center orb-tile ennu-life-score-center" 
			     data-score="<?php echo esc_attr( $score ); ?>" 
			     style="--score-percent: <?php echo esc_attr( $score * 10 ); ?>;" 
			     role="progressbar" 
			     aria-valuemin="0" 
			     aria-valuemax="100" 
			     aria-valuenow="<?php echo esc_attr( $score * 10 ); ?>" 
			     aria-label="<?php echo esc_attr( $title . ' score ' . number_format( $score, 1 ) . ' out of 10' ); ?>">
			     
				<!-- Glass panel with effects -->
				<div class="glass">
					<div class="blobs">
						<span class="blob"></span>
						<span class="blob"></span>
					</div>
					<div class="shine"></div>
				</div>
				
				<!-- Progress indicator -->
				<svg class="pillar-orb-progress" viewBox="0 0 100 100" preserveAspectRatio="none">
					<defs>
						<linearGradient id="grad-<?php echo esc_attr( $type ); ?>" x1="0%" y1="0%" x2="100%" y2="0%">
							<stop offset="0%" stop-color="<?php echo esc_attr( $interpretation['color'] ?? '#2dd4bf' ); ?>"/>
							<stop offset="50%" stop-color="<?php echo esc_attr( $interpretation['color'] ?? '#14b8a6' ); ?>"/>
							<stop offset="100%" stop-color="<?php echo esc_attr( $interpretation['color'] ?? '#0d9488' ); ?>"/>
						</linearGradient>
						<clipPath id="clip-<?php echo esc_attr( $type ); ?>">
							<rect x="2" y="2" width="96" height="96" rx="10" ry="10" />
						</clipPath>
					</defs>
					<rect x="2" y="2" width="96" height="96" rx="10" ry="10" class="pillar-orb-progress-bg"></rect>
					<rect x="2" y="2" width="96" height="96" rx="10" ry="10" class="pillar-orb-progress-bar" 
					      stroke="url(#grad-<?php echo esc_attr( $type ); ?>)" 
					      clip-path="url(#clip-<?php echo esc_attr( $type ); ?>)"></rect>
				</svg>
				
				<!-- Content -->
				<div class="main-score-orb" data-score="<?php echo esc_attr( number_format( $score, 1 ) ); ?>">
					<div class="main-score-text ennu-score-text">
						<div class="main-score-value ennu-score-value"><?php echo esc_html( number_format( $score, 1 ) ); ?></div>
						<div class="main-score-label ennu-score-label"><?php echo esc_html( $title ); ?></div>
					</div>
				</div>
			</div>
		</div>

		<!-- Score Details -->
		<div class="ennu-score-details">
			<?php if ( $show_interpretation && ! empty( $interpretation ) ) : ?>
				<div class="ennu-score-interpretation">
					<div class="ennu-score-level ennu-score-level-<?php echo esc_attr( $interpretation['class'] ?? 'unknown' ); ?>">
						<?php echo esc_html( $interpretation['level'] ?? 'Unknown' ); ?>
					</div>
					<?php if ( ! empty( $interpretation['description'] ) ) : ?>
						<div class="ennu-score-description">
							<?php echo esc_html( $interpretation['description'] ); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $last_updated ) ) : ?>
				<div class="ennu-score-last-updated">
					<small>Last updated: <?php echo esc_html( date( 'M j, Y', strtotime( $last_updated ) ) ); ?></small>
				</div>
			<?php endif; ?>
		</div>

		<!-- PHASE 3: Actionable Insights for Score -->
		<?php if ( class_exists( 'ENNU_Actionable_Feedback' ) ) : ?>
			<div class="ennu-score-actionable-insights">
				<h4 class="ennu-insights-title">Actionable Insights</h4>
				<?php
				$ennu_actionable_feedback = new ENNU_Actionable_Feedback();
				echo $ennu_actionable_feedback->render_actionable_feedback_widget();
				?>
			</div>
		<?php endif; ?>

	<!-- Pillar Scores -->
	<?php if ( $show_pillars && ! empty( $pillar_scores ) ) : ?>
		<div class="ennu-pillar-scores">
			<h4 class="ennu-pillar-title">Pillar Breakdown</h4>
			<div class="ennu-pillar-grid">
				<?php foreach ( $pillar_scores as $pillar => $pillar_score ) : ?>
					<?php
					$pillar_score = is_numeric( $pillar_score ) ? floatval( $pillar_score ) : 0;
					
					// Create interpretation data inline since $this is not available in template
					$pillar_interpretation = array();
					if ( $pillar_score >= 8.5 ) {
						$pillar_interpretation = array(
							'level'       => 'Excellent',
							'color'       => '#10b981',
							'description' => 'Outstanding performance!',
							'class'       => 'excellent',
						);
					} elseif ( $pillar_score >= 7.0 ) {
						$pillar_interpretation = array(
							'level'       => 'Good',
							'color'       => '#3b82f6',
							'description' => 'Good performance with room for improvement.',
							'class'       => 'good',
						);
					} elseif ( $pillar_score >= 5.5 ) {
						$pillar_interpretation = array(
							'level'       => 'Fair',
							'color'       => '#f59e0b',
							'description' => 'Fair performance. Consider optimization strategies.',
							'class'       => 'fair',
						);
					} else {
						$pillar_interpretation = array(
							'level'       => 'Needs Improvement',
							'color'       => '#ef4444',
							'description' => 'Focus on improvement strategies for better results.',
							'class'       => 'needs-improvement',
						);
					}
					?>
					<div class="ennu-pillar-item" data-pillar="<?php echo esc_attr( $pillar ); ?>">
						<div class="pillar-orb <?php echo esc_attr( strtolower( $pillar ) ); ?> orb-tile orb-rect" 
						     role="progressbar" 
						     aria-valuemin="0" 
						     aria-valuemax="100" 
						     aria-valuenow="<?php echo esc_attr( $pillar_score * 10 ); ?>" 
						     style="--score-percent: <?php echo esc_attr( $pillar_score * 10 ); ?>;" 
						     aria-label="<?php echo esc_attr( ucfirst( $pillar ) . ' score ' . number_format( $pillar_score, 1 ) . ' out of 10' ); ?>">
						     
							<!-- Glass panel with effects -->
							<div class="glass">
								<div class="blobs">
									<span class="blob"></span>
									<span class="blob"></span>
								</div>
								<div class="shine"></div>
							</div>
							
							<!-- Progress indicator -->
							<svg class="pillar-orb-progress" viewBox="0 0 100 200" preserveAspectRatio="none">
								<defs>
									<linearGradient id="grad-<?php echo esc_attr( $pillar ); ?>" x1="0%" y1="0%" x2="100%" y2="0%">
										<stop offset="0%" stop-color="<?php echo esc_attr( $pillar_interpretation['color'] ); ?>"/>
										<stop offset="50%" stop-color="<?php echo esc_attr( $pillar_interpretation['color'] ); ?>"/>
										<stop offset="100%" stop-color="<?php echo esc_attr( $pillar_interpretation['color'] ); ?>"/>
									</linearGradient>
									<clipPath id="clip-<?php echo esc_attr( $pillar ); ?>">
										<rect x="2" y="2" width="96" height="196" rx="16" ry="16" />
									</clipPath>
								</defs>
								<rect x="2" y="2" width="96" height="196" rx="16" ry="16" class="pillar-orb-progress-bg"></rect>
								<rect x="2" y="2" width="96" height="196" rx="16" ry="16" class="pillar-orb-progress-bar" 
								      stroke="url(#grad-<?php echo esc_attr( $pillar ); ?>)" 
								      clip-path="url(#clip-<?php echo esc_attr( $pillar ); ?>)"></rect>
							</svg>
							
							<!-- Content -->
							<div class="pillar-orb-content ennu-pillar-content">
								<div class="pillar-orb-label ennu-pillar-name"><?php echo esc_html( ucfirst( $pillar ) ); ?></div>
								<div class="pillar-orb-score ennu-pillar-score"><?php echo esc_html( number_format( $pillar_score, 1 ) ); ?></div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<!-- Score History -->
	<?php if ( $show_history && ! empty( $history ) ) : ?>
		<div class="ennu-score-history">
			<h4 class="ennu-history-title">Score History</h4>
			<div class="ennu-history-chart">
				<canvas id="score-history-chart-<?php echo esc_attr( $type ); ?>" width="400" height="200"></canvas>
			</div>
			<div class="ennu-history-list">
				<?php
				$recent_history = array_slice( $history, -5 ); // Show last 5 entries
				foreach ( $recent_history as $entry ) :
					$entry_score = $entry['score'] ?? 0;
					$entry_date  = $entry['date'] ?? '';
					
					// Create interpretation data inline since $this is not available in template
					$entry_interpretation = array();
					if ( $entry_score >= 8.5 ) {
						$entry_interpretation = array(
							'level'       => 'Excellent',
							'color'       => '#10b981',
							'description' => 'Outstanding performance!',
							'class'       => 'excellent',
						);
					} elseif ( $entry_score >= 7.0 ) {
						$entry_interpretation = array(
							'level'       => 'Good',
							'color'       => '#3b82f6',
							'description' => 'Good performance with room for improvement.',
							'class'       => 'good',
						);
					} elseif ( $entry_score >= 5.5 ) {
						$entry_interpretation = array(
							'level'       => 'Fair',
							'color'       => '#f59e0b',
							'description' => 'Fair performance. Consider optimization strategies.',
							'class'       => 'fair',
						);
					} else {
						$entry_interpretation = array(
							'level'       => 'Needs Improvement',
							'color'       => '#ef4444',
							'description' => 'Focus on improvement strategies for better results.',
							'class'       => 'needs-improvement',
						);
					}
				?>
					<div class="ennu-history-item">
						<div class="ennu-history-score ennu-score-level-<?php echo esc_attr( $entry_interpretation['class'] ); ?>">
							<?php echo esc_html( number_format( $entry_score, 1 ) ); ?>
						</div>
						<div class="ennu-history-date">
							<?php echo esc_html( date( 'M j, Y', strtotime( $entry_date ) ) ); ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>

	<!-- Action Buttons -->
	<div class="ennu-score-actions">
		<?php if ( $type === 'lifescore' ) : ?>
			<button class="ennu-btn ennu-btn-primary ennu-btn-refresh-score" data-type="lifescore">
				<svg viewBox="0 0 24 24" class="ennu-btn-icon">
					<path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
				</svg>
				Refresh Score
			</button>
		<?php elseif ( $type === 'assessment' ) : ?>
			<button class="ennu-btn ennu-btn-secondary ennu-btn-retake-assessment" data-type="<?php echo esc_attr( $score_data['assessment_type'] ?? '' ); ?>">
				<svg viewBox="0 0 24 24" class="ennu-btn-icon">
					<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
				</svg>
									<?php echo esc_html( ENNU_UI_Constants::get_button_text( 'RETAKE_ASSESSMENT' ) ); ?>
			</button>
		<?php endif; ?>
	</div>
</div>

<script type="text/javascript">
// Initialize score presentation
document.addEventListener('DOMContentLoaded', function() {
    const scorePresentation = document.querySelector('.ennu-score-presentation[data-type="<?php echo esc_js( $type ); ?>"]');
    if (scorePresentation) {
        // Animate score orb on load
        if (<?php echo $animation ? 'true' : 'false'; ?>) {
            setTimeout(function() {
                scorePresentation.classList.add('ennu-animated');
            }, 100);
        }
        
        // Initialize chart if history is shown
        <?php if ( $show_history && ! empty( $history ) ) : ?>
        if (typeof Chart !== 'undefined') {
            const ctx = document.getElementById('score-history-chart-<?php echo esc_js( $type ); ?>');
            if (ctx) {
                const chartData = <?php echo json_encode( $history ); ?>;
                const labels = chartData.map(entry => entry.date ? new Date(entry.date).toLocaleDateString() : '');
                const scores = chartData.map(entry => entry.score || 0);
                
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: '<?php echo esc_js( $title ); ?>',
                            data: scores,
                            borderColor: '<?php echo esc_js( $interpretation['color'] ?? '#667eea' ); ?>',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 10,
                                ticks: {
                                    stepSize: 2
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
        }
        <?php endif; ?>
    }
});
</script> 