<?php
/**
 * Actionable Feedback System
 *
 * @package   ENNU Life Assessments
 * @copyright Copyright (c) 2024, Very Good Plugins, https://verygoodplugins.com
 * @license   GPL-3.0+
 * @since     3.37.14
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Actionable Feedback System
 * 
 * Generates specific, actionable recommendations for every score
 * and provides personalized improvement tips and goal setting guidance.
 */
class ENNU_Actionable_Feedback {

	/**
	 * Initialize the Actionable Feedback System
	 */
	public function __construct() {
		add_action( 'wp_ajax_get_actionable_feedback', array( $this, 'ajax_get_actionable_feedback' ) );
		add_action( 'wp_ajax_nopriv_get_actionable_feedback', array( $this, 'ajax_get_actionable_feedback' ) );
	}

	/**
	 * Get actionable feedback for a user
	 *
	 * @param int $user_id User ID
	 * @return array Actionable feedback data
	 */
	public function get_actionable_feedback( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return array();
		}

		$feedback = array(
			'score_insights' => $this->get_score_insights( $user_id ),
			'improvement_tips' => $this->get_improvement_tips( $user_id ),
			'goal_suggestions' => $this->get_goal_suggestions( $user_id ),
			'personalized_recommendations' => $this->get_personalized_recommendations( $user_id )
		);

		return $feedback;
	}

	/**
	 * Get score insights with actionable feedback
	 *
	 * @param int $user_id User ID
	 * @return array Score insights
	 */
	private function get_score_insights( $user_id ) {
		$pillar_scores = $this->get_pillar_scores( $user_id );
		$insights = array();

		foreach ( $pillar_scores as $pillar => $score ) {
			$insights[ $pillar ] = array(
				'score' => $score,
				'interpretation' => $this->interpret_score( $score ),
				'strengths' => $this->get_pillar_strengths( $pillar, $score ),
				'improvement_areas' => $this->get_pillar_improvement_areas( $pillar, $score ),
				'actionable_steps' => $this->get_pillar_actionable_steps( $pillar, $score ),
				'timeframe' => $this->get_improvement_timeframe( $score )
			);
		}

		return $insights;
	}

	/**
	 * Get improvement tips for the user
	 *
	 * @param int $user_id User ID
	 * @return array Improvement tips
	 */
	private function get_improvement_tips( $user_id ) {
		$tips = array();
		$pillar_scores = $this->get_pillar_scores( $user_id );

		// Find lowest scoring pillar
		$lowest_pillar = $this->get_lowest_scoring_pillar( $pillar_scores );
		if ( $lowest_pillar ) {
			$tips[] = array(
				'type' => 'priority_focus',
				'title' => "Focus on {$lowest_pillar}",
				'description' => "Your {$lowest_pillar} score of {$pillar_scores[$lowest_pillar]} needs attention.",
				'tips' => $this->get_pillar_specific_tips( $lowest_pillar ),
				'priority' => 'high'
			);
		}

		// Add general improvement tips
		$tips[] = array(
			'type' => 'general_improvement',
			'title' => 'General Health Optimization',
			'description' => 'These tips apply to all areas of your health.',
			'tips' => $this->get_general_improvement_tips(),
			'priority' => 'medium'
		);

		return $tips;
	}

	/**
	 * Get goal suggestions for the user
	 *
	 * @param int $user_id User ID
	 * @return array Goal suggestions
	 */
	private function get_goal_suggestions( $user_id ) {
		$suggestions = array();
		$pillar_scores = $this->get_pillar_scores( $user_id );

		foreach ( $pillar_scores as $pillar => $score ) {
			if ( $score < 8.0 ) {
				$suggestions[] = array(
					'pillar' => $pillar,
					'current_score' => $score,
					'target_score' => 8.0,
					'goal_description' => "Improve {$pillar} score from {$score} to 8.0",
					'specific_actions' => $this->get_pillar_specific_actions( $pillar ),
					'timeline' => $this->get_goal_timeline( $score ),
					'milestones' => $this->get_goal_milestones( $pillar, $score )
				);
			}
		}

		return $suggestions;
	}

	/**
	 * Get personalized recommendations
	 *
	 * @param int $user_id User ID
	 * @return array Personalized recommendations
	 */
	private function get_personalized_recommendations( $user_id ) {
		$recommendations = array();

		// Assessment recommendations
		$missing_assessments = $this->get_missing_assessments( $user_id );
		if ( ! empty( $missing_assessments ) ) {
			// Convert assessment objects to strings for display
			$assessment_items = array();
			foreach ( $missing_assessments as $assessment ) {
				$assessment_items[] = $assessment['name'] . ' - ' . $assessment['description'] . ' (' . $assessment['estimated_time'] . ')';
			}
			
			$recommendations[] = array(
				'type' => 'assessment',
				'title' => 'Complete Missing Assessments',
				'description' => 'These assessments will provide more accurate insights.',
				'items' => $assessment_items,
				'priority' => 'high'
			);
		}

		// Biomarker recommendations
		$biomarker_recommendations = $this->get_biomarker_recommendations( $user_id );
		if ( ! empty( $biomarker_recommendations ) ) {
			// Ensure biomarker recommendations have items array
			foreach ( $biomarker_recommendations as &$recommendation ) {
				if ( ! isset( $recommendation['items'] ) ) {
					$recommendation['items'] = array(
						'Import your latest blood work results',
						'Upload lab reports for analysis',
						'Track biomarker trends over time'
					);
				}
			}
			$recommendations = array_merge( $recommendations, $biomarker_recommendations );
		}

		// Lifestyle recommendations
		$lifestyle_recommendations = $this->get_lifestyle_recommendations( $user_id );
		if ( ! empty( $lifestyle_recommendations ) ) {
			// Ensure lifestyle recommendations have items array
			foreach ( $lifestyle_recommendations as &$recommendation ) {
				if ( ! isset( $recommendation['items'] ) ) {
					$recommendation['items'] = array(
						'Establish consistent sleep schedule',
						'Create relaxing bedtime routine',
						'Optimize sleep environment',
						'Track sleep quality metrics'
					);
				}
			}
			$recommendations = array_merge( $recommendations, $lifestyle_recommendations );
		}

		return $recommendations;
	}

	/**
	 * Interpret score and provide context
	 *
	 * @param float $score Score value
	 * @return string Score interpretation
	 */
	private function interpret_score( $score ) {
		if ( $score >= 8.5 ) {
			return 'Excellent! You\'re performing at an optimal level.';
		} elseif ( $score >= 7.0 ) {
			return 'Good! You\'re above average with room for improvement.';
		} elseif ( $score >= 6.0 ) {
			return 'Fair. There are specific areas for improvement.';
		} elseif ( $score >= 5.0 ) {
			return 'Below average. Focus on targeted improvements.';
		} else {
			return 'Needs attention. Consider professional guidance.';
		}
	}

	/**
	 * Get pillar strengths
	 *
	 * @param string $pillar Pillar name
	 * @param float $score Score value
	 * @return array Pillar strengths
	 */
	private function get_pillar_strengths( $pillar, $score ) {
		if ( $score < 6.0 ) {
			return array();
		}

		$strengths = array(
			'Mind' => array(
				'Good stress management',
				'Strong cognitive function',
				'Positive mental outlook'
			),
			'Body' => array(
				'Optimal biomarker levels',
				'Good physical condition',
				'Healthy body composition'
			),
			'Lifestyle' => array(
				'Consistent healthy habits',
				'Good sleep quality',
				'Balanced nutrition'
			),
			'Aesthetics' => array(
				'Positive self-image',
				'Good appearance markers',
				'Confident presentation'
			)
		);

		return isset( $strengths[ $pillar ] ) ? $strengths[ $pillar ] : array();
	}

	/**
	 * Get pillar improvement areas
	 *
	 * @param string $pillar Pillar name
	 * @param float $score Score value
	 * @return array Improvement areas
	 */
	private function get_pillar_improvement_areas( $pillar, $score ) {
		if ( $score >= 8.0 ) {
			return array();
		}

		$improvement_areas = array(
			'Mind' => array(
				'Stress management techniques',
				'Cognitive training exercises',
				'Mental health awareness'
			),
			'Body' => array(
				'Biomarker optimization',
				'Physical activity routine',
				'Body composition management'
			),
			'Lifestyle' => array(
				'Sleep hygiene practices',
				'Nutrition optimization',
				'Habit tracking systems'
			),
			'Aesthetics' => array(
				'Appearance-related goals',
				'Confidence building',
				'Self-image improvement'
			)
		);

		return isset( $improvement_areas[ $pillar ] ) ? $improvement_areas[ $pillar ] : array();
	}

	/**
	 * Get pillar actionable steps
	 *
	 * @param string $pillar Pillar name
	 * @param float $score Score value
	 * @return array Actionable steps
	 */
	private function get_pillar_actionable_steps( $pillar, $score ) {
		$steps = array();

		switch ( $pillar ) {
			case 'Mind':
				$steps = array(
					'Take the stress assessment to identify specific stressors',
					'Practice 10 minutes of daily mindfulness meditation',
					'Consider cognitive training apps for mental sharpness'
				);
				break;
			case 'Body':
				$steps = array(
					'Import your latest lab results for biomarker analysis',
					'Complete the physical activity assessment',
					'Track your weight and body composition regularly'
				);
				break;
			case 'Lifestyle':
				$steps = array(
					'Complete the sleep assessment to optimize rest',
					'Take the nutrition assessment for dietary guidance',
					'Track your daily habits and routines'
				);
				break;
			case 'Aesthetics':
				$steps = array(
					'Complete the aesthetics assessment',
					'Set specific appearance-related goals',
					'Work on confidence-building activities'
				);
				break;
		}

		return $steps;
	}

	/**
	 * Get improvement timeframe
	 *
	 * @param float $score Score value
	 * @return string Improvement timeframe
	 */
	private function get_improvement_timeframe( $score ) {
		if ( $score >= 7.5 ) {
			return '2-4 weeks for noticeable improvement';
		} elseif ( $score >= 6.0 ) {
			return '4-8 weeks for significant improvement';
		} elseif ( $score >= 5.0 ) {
			return '8-12 weeks for major improvement';
		} else {
			return '12+ weeks for comprehensive improvement';
		}
	}

	/**
	 * Get pillar scores for a user using SSOT
	 *
	 * @param int $user_id User ID
	 * @return array Pillar scores
	 */
	private function get_pillar_scores( $user_id ) {
		// Use the proper scoring system instead of hardcoded data
		if ( class_exists( 'ENNU_Scoring_System' ) ) {
			return ENNU_Scoring_System::calculate_average_pillar_scores( $user_id );
		}
		
		// Fallback to user meta if scoring system not available
		$pillar_scores = get_user_meta( $user_id, 'ennu_average_pillar_scores', true );
		return is_array( $pillar_scores ) ? $pillar_scores : array();
	}

	/**
	 * Get lowest scoring pillar
	 *
	 * @param array $pillar_scores Pillar scores
	 * @return string|null Lowest scoring pillar
	 */
	private function get_lowest_scoring_pillar( $pillar_scores ) {
		if ( empty( $pillar_scores ) ) {
			return null;
		}

		$lowest_score = min( $pillar_scores );
		$lowest_pillar = array_search( $lowest_score, $pillar_scores );

		return $lowest_pillar;
	}

	/**
	 * Get pillar specific tips
	 *
	 * @param string $pillar Pillar name
	 * @return array Pillar specific tips
	 */
	private function get_pillar_specific_tips( $pillar ) {
		$tips = array(
			'Mind' => array(
				'Practice deep breathing exercises daily',
				'Limit screen time before bed',
				'Engage in cognitive training activities',
				'Consider professional stress management'
			),
			'Body' => array(
				'Get regular blood work done',
				'Establish a consistent exercise routine',
				'Track your biomarkers monthly',
				'Consider working with a health coach'
			),
			'Lifestyle' => array(
				'Establish a consistent sleep schedule',
				'Track your nutrition intake',
				'Create morning and evening routines',
				'Set specific habit goals'
			),
			'Aesthetics' => array(
				'Set specific appearance goals',
				'Track your progress regularly',
				'Focus on confidence-building activities',
				'Consider professional guidance'
			)
		);

		return isset( $tips[ $pillar ] ) ? $tips[ $pillar ] : array();
	}

	/**
	 * Get general improvement tips
	 *
	 * @return array General improvement tips
	 */
	private function get_general_improvement_tips() {
		return array(
			'Track your progress consistently',
			'Set specific, measurable goals',
			'Celebrate small wins along the way',
			'Stay consistent with your routines',
			'Get adequate sleep and rest',
			'Stay hydrated throughout the day',
			'Move your body regularly',
			'Manage stress effectively'
		);
	}

	/**
	 * Get pillar specific actions
	 *
	 * @param string $pillar Pillar name
	 * @return array Pillar specific actions
	 */
	private function get_pillar_specific_actions( $pillar ) {
		$actions = array(
			'Mind' => array(
				'Complete stress assessment',
				'Practice daily meditation',
				'Engage in cognitive exercises',
				'Seek professional guidance if needed'
			),
			'Body' => array(
				'Import lab results',
				'Complete physical assessment',
				'Establish exercise routine',
				'Track biomarkers regularly'
			),
			'Lifestyle' => array(
				'Complete sleep assessment',
				'Take nutrition assessment',
				'Create daily routines',
				'Track habits consistently'
			),
			'Aesthetics' => array(
				'Complete aesthetics assessment',
				'Set appearance goals',
				'Track progress regularly',
				'Build confidence activities'
			)
		);

		return isset( $actions[ $pillar ] ) ? $actions[ $pillar ] : array();
	}

	/**
	 * Get goal timeline
	 *
	 * @param float $score Score value
	 * @return string Goal timeline
	 */
	private function get_goal_timeline( $score ) {
		if ( $score >= 7.0 ) {
			return '2-4 weeks';
		} elseif ( $score >= 6.0 ) {
			return '4-8 weeks';
		} else {
			return '8-12 weeks';
		}
	}

	/**
	 * Get goal milestones
	 *
	 * @param string $pillar Pillar name
	 * @param float $score Score value
	 * @return array Goal milestones
	 */
	private function get_goal_milestones( $pillar, $score ) {
		$target = 8.0;
		$current = $score;
		$improvement_needed = $target - $current;

		$milestones = array();
		$step_size = $improvement_needed / 3;

		for ( $i = 1; $i <= 3; $i++ ) {
			$milestone_score = $current + ( $step_size * $i );
			$milestones[] = array(
				'step' => $i,
				'target_score' => round( $milestone_score, 1 ),
				'description' => "Reach {$pillar} score of " . round( $milestone_score, 1 )
			);
		}

		return $milestones;
	}

	/**
	 * Get missing assessments
	 *
	 * @param int $user_id User ID
	 * @return array Missing assessments
	 */
	private function get_missing_assessments( $user_id ) {
		// Get actual missing assessments from the system
		if ( class_exists( 'ENNU_Scoring_System' ) ) {
			$all_definitions = ENNU_Scoring_System::get_all_definitions();
			$missing = array();
			
			// Get completed assessments from user meta
			$user_assessments = get_user_meta( $user_id, 'ennu_assessments', true );
			$completed_assessments = array();
			if ( is_array( $user_assessments ) ) {
				foreach ( $user_assessments as $assessment_type => $data ) {
					if ( ! empty( $data['submitted_at'] ) ) {
						$completed_assessments[] = $assessment_type;
					}
				}
			}
			
			foreach ( $all_definitions as $assessment_type => $definition ) {
				if ( ! in_array( $assessment_type, $completed_assessments ) ) {
					$missing[] = array(
						'name' => $definition['title'] ?? ucfirst( $assessment_type ) . ' Assessment',
						'description' => $definition['description'] ?? 'Complete this assessment for personalized insights',
						'estimated_time' => $definition['estimated_time'] ?? '5-10 minutes'
					);
				}
			}
			
			return $missing;
		}
		
		// Fallback to empty array if scoring system not available
		return array();
	}

	/**
	 * Get biomarker recommendations
	 *
	 * @param int $user_id User ID
	 * @return array Biomarker recommendations
	 */
	private function get_biomarker_recommendations( $user_id ) {
		// Get actual biomarker data from user meta
		$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		$recommendations = array();
		
		if ( empty( $biomarker_data ) || ! is_array( $biomarker_data ) ) {
			// No biomarker data available
			$recommendations[] = array(
				'type' => 'biomarker',
				'title' => 'Import Lab Results',
				'description' => 'Import your latest lab results for personalized insights.',
				'priority' => 'high'
			);
		} else {
			// Analyze existing biomarker data for recommendations
			$critical_biomarkers = array(
				'glucose' => array( 'name' => 'Blood Glucose', 'optimal' => '70-100 mg/dL' ),
				'hba1c' => array( 'name' => 'HbA1c', 'optimal' => '< 5.7%' ),
				'testosterone_total' => array( 'name' => 'Total Testosterone', 'optimal' => '300-1000 ng/dL' ),
				'vitamin_d' => array( 'name' => 'Vitamin D', 'optimal' => '30-50 ng/mL' )
			);
			
			foreach ( $critical_biomarkers as $biomarker => $info ) {
				if ( ! isset( $biomarker_data[ $biomarker ] ) || empty( $biomarker_data[ $biomarker ] ) ) {
					$recommendations[] = array(
						'type' => 'biomarker',
						'title' => "Add {$info['name']}",
						'description' => "Import your {$info['name']} results for complete analysis.",
						'priority' => 'medium'
					);
				}
			}
		}
		
		return $recommendations;
	}

	/**
	 * Get lifestyle recommendations
	 *
	 * @param int $user_id User ID
	 * @return array Lifestyle recommendations
	 */
	private function get_lifestyle_recommendations( $user_id ) {
		// Get actual lifestyle data from user assessments
		$user_assessments = get_user_meta( $user_id, 'ennu_assessments', true );
		$recommendations = array();
		
		if ( empty( $user_assessments ) || ! is_array( $user_assessments ) ) {
			// No assessment data available
			$recommendations[] = array(
				'type' => 'lifestyle',
				'title' => 'Complete Assessments',
				'description' => 'Complete your health assessments for personalized lifestyle recommendations.',
				'priority' => 'high'
			);
		} else {
			// Analyze completed assessments for lifestyle recommendations
			$lifestyle_assessments = array(
				'sleep' => array( 'name' => 'Sleep Assessment', 'focus' => 'sleep quality' ),
				'nutrition' => array( 'name' => 'Nutrition Assessment', 'focus' => 'dietary habits' ),
				'physical_activity' => array( 'name' => 'Physical Activity Assessment', 'focus' => 'exercise routine' )
			);
			
			foreach ( $lifestyle_assessments as $assessment_type => $info ) {
				if ( ! isset( $user_assessments[ $assessment_type ] ) || empty( $user_assessments[ $assessment_type ]['submitted_at'] ) ) {
					$recommendations[] = array(
						'type' => 'lifestyle',
						'title' => "Complete {$info['name']}",
						'description' => "Take the {$info['name']} to get personalized {$info['focus']} recommendations.",
						'priority' => 'medium'
					);
				}
			}
		}
		
		return $recommendations;
	}

	/**
	 * AJAX handler for getting actionable feedback
	 */
	public function ajax_get_actionable_feedback() {
		check_ajax_referer( 'ennu_feedback_nonce', 'nonce' );

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_die( 'User not logged in' );
		}

		$feedback = $this->get_actionable_feedback( $user_id );
		wp_send_json_success( $feedback );
	}

	/**
	 * Render actionable feedback widget
	 *
	 * @param int $user_id User ID
	 * @return string Rendered widget HTML
	 */
	public function render_actionable_feedback_widget( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return '';
		}

		$feedback = $this->get_actionable_feedback( $user_id );

		ob_start();
		?>
		<div class="ennu-actionable-feedback-widget" data-user-id="<?php echo esc_attr( $user_id ); ?>">
			<div class="ennu-feedback-header">
				<h3>💡 Actionable Insights</h3>
				<p>Personalized recommendations to improve your health</p>
			</div>

			<?php if ( ! empty( $feedback['score_insights'] ) ) : ?>
				<div class="ennu-score-insights">
					<h4>Score Analysis</h4>
					<?php foreach ( $feedback['score_insights'] as $pillar => $insight ) : ?>
						<div class="ennu-insight-item">
							<div class="ennu-insight-header">
								<h5><?php echo esc_html( $pillar ); ?> Score: <?php echo esc_html( $insight['score'] ); ?></h5>
								<span class="ennu-interpretation"><?php echo esc_html( $insight['interpretation'] ); ?></span>
							</div>
							<?php if ( ! empty( $insight['actionable_steps'] ) ) : ?>
								<div class="ennu-actionable-steps">
									<h6>Next Steps:</h6>
									<ul>
										<?php foreach ( $insight['actionable_steps'] as $step ) : ?>
											<li><?php echo esc_html( $step ); ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endif; ?>
							<div class="ennu-timeframe">
								<strong>Expected improvement:</strong> <?php echo esc_html( $insight['timeframe'] ); ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $feedback['improvement_tips'] ) ) : ?>
				<div class="ennu-improvement-tips">
					<h4>Improvement Tips</h4>
					<?php foreach ( $feedback['improvement_tips'] as $tip ) : ?>
						<div class="ennu-tip-item ennu-priority-<?php echo esc_attr( $tip['priority'] ); ?>">
							<div class="ennu-tip-content">
								<h5><?php echo esc_html( $tip['title'] ); ?></h5>
								<p><?php echo esc_html( $tip['description'] ); ?></p>
								<?php if ( ! empty( $tip['tips'] ) ) : ?>
									<ul class="ennu-tip-list">
										<?php foreach ( $tip['tips'] as $tip_item ) : ?>
											<li><?php echo esc_html( $tip_item ); ?></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $feedback['goal_suggestions'] ) ) : ?>
				<div class="ennu-goal-suggestions">
					<h4>Suggested Goals</h4>
					<?php foreach ( $feedback['goal_suggestions'] as $goal ) : ?>
						<div class="ennu-goal-item">
							<div class="ennu-goal-content">
								<h5><?php echo esc_html( $goal['goal_description'] ); ?></h5>
								<div class="ennu-goal-progress">
									<span class="ennu-current"><?php echo esc_html( $goal['current_score'] ); ?></span>
									<span class="ennu-arrow">→</span>
									<span class="ennu-target"><?php echo esc_html( $goal['target_score'] ); ?></span>
								</div>
								<div class="ennu-goal-timeline">
									<strong>Timeline:</strong> <?php echo esc_html( $goal['timeline'] ); ?>
								</div>
								<?php if ( ! empty( $goal['specific_actions'] ) ) : ?>
									<div class="ennu-goal-actions">
										<h6>Actions to take:</h6>
										<ul>
											<?php foreach ( $goal['specific_actions'] as $action ) : ?>
												<li><?php echo esc_html( $action ); ?></li>
											<?php endforeach; ?>
										</ul>
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<style>
		.ennu-actionable-feedback-widget {
			background: #ffffff;
			border-radius: 12px;
			padding: 24px;
			box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
			margin-bottom: 24px;
		}

		.ennu-feedback-header {
			margin-bottom: 24px;
			text-align: center;
		}

		.ennu-feedback-header h3 {
			margin: 0 0 8px 0;
			color: #2c3e50;
			font-size: 1.5rem;
		}

		.ennu-feedback-header p {
			margin: 0;
			color: #7f8c8d;
			font-size: 0.9rem;
		}

		.ennu-score-insights,
		.ennu-improvement-tips,
		.ennu-goal-suggestions {
			margin-bottom: 24px;
		}

		.ennu-score-insights h4,
		.ennu-improvement-tips h4,
		.ennu-goal-suggestions h4 {
			margin: 0 0 16px 0;
			color: #2c3e50;
			font-size: 1.2rem;
		}

		.ennu-insight-item {
			padding: 16px;
			margin-bottom: 16px;
			border-radius: 8px;
			background: #f8f9fa;
			border-left: 4px solid #3498db;
		}

		.ennu-insight-header h5 {
			margin: 0 0 8px 0;
			color: #2c3e50;
			font-size: 1.1rem;
		}

		.ennu-interpretation {
			color: #7f8c8d;
			font-size: 0.9rem;
			font-style: italic;
		}

		.ennu-actionable-steps {
			margin: 16px 0;
		}

		.ennu-actionable-steps h6 {
			margin: 0 0 8px 0;
			color: #2c3e50;
			font-size: 0.9rem;
		}

		.ennu-actionable-steps ul {
			margin: 0;
			padding-left: 20px;
		}

		.ennu-actionable-steps li {
			margin-bottom: 4px;
			color: #2c3e50;
			font-size: 0.9rem;
		}

		.ennu-timeframe {
			margin-top: 12px;
			font-size: 0.8rem;
			color: #7f8c8d;
		}

		.ennu-tip-item {
			padding: 16px;
			margin-bottom: 12px;
			border-radius: 8px;
			border-left: 4px solid;
		}

		.ennu-priority-high {
			background: #fff5f5;
			border-left-color: #e53e3e;
		}

		.ennu-priority-medium {
			background: #fffaf0;
			border-left-color: #dd6b20;
		}

		.ennu-tip-content h5 {
			margin: 0 0 8px 0;
			color: #2c3e50;
			font-size: 1rem;
		}

		.ennu-tip-content p {
			margin: 0 0 12px 0;
			color: #7f8c8d;
			font-size: 0.9rem;
			line-height: 1.4;
		}

		.ennu-tip-list {
			margin: 0;
			padding-left: 20px;
		}

		.ennu-tip-list li {
			margin-bottom: 4px;
			color: #2c3e50;
			font-size: 0.9rem;
		}

		.ennu-goal-item {
			padding: 16px;
			margin-bottom: 16px;
			border-radius: 8px;
			background: #f0fff4;
			border-left: 4px solid #38a169;
		}

		.ennu-goal-content h5 {
			margin: 0 0 12px 0;
			color: #2c3e50;
			font-size: 1rem;
		}

		.ennu-goal-progress {
			display: flex;
			align-items: center;
			gap: 8px;
			margin-bottom: 8px;
		}

		.ennu-current {
			font-weight: 600;
			color: #e53e3e;
		}

		.ennu-arrow {
			color: #7f8c8d;
		}

		.ennu-target {
			font-weight: 600;
			color: #38a169;
		}

		.ennu-goal-timeline {
			margin-bottom: 12px;
			font-size: 0.8rem;
			color: #7f8c8d;
		}

		.ennu-goal-actions h6 {
			margin: 0 0 8px 0;
			color: #2c3e50;
			font-size: 0.9rem;
		}

		.ennu-goal-actions ul {
			margin: 0;
			padding-left: 20px;
		}

		.ennu-goal-actions li {
			margin-bottom: 4px;
			color: #2c3e50;
			font-size: 0.9rem;
		}
		</style>
		<?php
		return ob_get_clean();
	}
}

// Initialize the Actionable Feedback System
new ENNU_Actionable_Feedback(); 