<?php
/**
 * ENNU Goal Progression Tracker
 *
 * Tracks user progress towards health goals with "Good → Better → Best" methodology.
 *
 * @package ENNU_Life
 * @version 62.2.8
 * @author ENNU Life Development Team
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Goal_Progression_Tracker {

	/**
	 * Track goal progression for a user
	 *
	 * @param int $user_id User ID
	 * @return array Progression data
	 */
	public static function track_progression( $user_id ) {
		$health_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );

		if ( empty( $health_goals ) || ! is_array( $health_goals ) ) {
			return array();
		}

		$progression_data = array();

		foreach ( $health_goals as $goal ) {
			$goal_progress             = self::calculate_goal_progress( $user_id, $goal );
			$progression_data[ $goal ] = $goal_progress;
		}

		$overall_progress = self::calculate_overall_progress( $progression_data );

		$result = array(
			'goals'           => $progression_data,
			'overall'         => $overall_progress,
			'achievements'    => self::check_achievements( $progression_data ),
			'next_milestones' => self::get_next_milestones( $progression_data ),
			'updated_at'      => current_time( 'mysql' ),
		);

		update_user_meta( $user_id, 'ennu_goal_progression', $result );

		return $result;
	}

	/**
	 * Calculate progress for a specific goal
	 *
	 * @param int $user_id User ID
	 * @param string $goal Goal name
	 * @return array Goal progress data
	 */
	private static function calculate_goal_progress( $user_id, $goal ) {
		$baseline_score = self::get_baseline_score_for_goal( $user_id, $goal );
		$current_score  = self::get_current_score_for_goal( $user_id, $goal );
		$target_score   = self::get_target_score_for_goal( $goal );

		$progress_percentage = 0;
		if ( $target_score > $baseline_score ) {
			$progress_percentage = min( 100, ( ( $current_score - $baseline_score ) / ( $target_score - $baseline_score ) ) * 100 );
		}

		$stage = self::determine_progress_stage( $progress_percentage );

		return array(
			'goal'                => $goal,
			'baseline_score'      => $baseline_score,
			'current_score'       => $current_score,
			'target_score'        => $target_score,
			'progress_percentage' => max( 0, $progress_percentage ),
			'stage'               => $stage,
			'stage_description'   => self::get_stage_description( $stage ),
			'recommendations'     => self::get_goal_recommendations( $goal, $stage ),
		);
	}

	/**
	 * Get baseline score for a goal
	 *
	 * @param int $user_id User ID
	 * @param string $goal Goal name
	 * @return float Baseline score
	 */
	private static function get_baseline_score_for_goal( $user_id, $goal ) {
		$score_history = get_user_meta( $user_id, 'ennu_life_score_history', true );

		if ( empty( $score_history ) || ! is_array( $score_history ) ) {
			return 5.0;
		}

		$first_entry = reset( $score_history );
		return isset( $first_entry['score'] ) ? $first_entry['score'] : 5.0;
	}

	/**
	 * Get current score for a goal
	 *
	 * @param int $user_id User ID
	 * @param string $goal Goal name
	 * @return float Current score
	 */
	private static function get_current_score_for_goal( $user_id, $goal ) {
		$pillar_scores = get_user_meta( $user_id, 'ennu_average_pillar_scores', true );

		if ( empty( $pillar_scores ) || ! is_array( $pillar_scores ) ) {
			return 5.0;
		}

		$goal_pillar_map = array(
			'Weight Loss'           => 'body',
			'Muscle Gain'           => 'body',
			'Energy Boost'          => 'body',
			'Better Sleep'          => 'lifestyle',
			'Stress Management'     => 'mind',
			'Hormone Optimization'  => 'body',
			'Cognitive Enhancement' => 'mind',
			'Skin Health'           => 'aesthetics',
			'Hair Health'           => 'aesthetics',
			'Longevity'             => 'lifestyle',
		);

		$pillar = isset( $goal_pillar_map[ $goal ] ) ? $goal_pillar_map[ $goal ] : 'body';

		return isset( $pillar_scores[ $pillar ] ) ? $pillar_scores[ $pillar ] : 5.0;
	}

	/**
	 * Get target score for a goal
	 *
	 * @param string $goal Goal name
	 * @return float Target score
	 */
	private static function get_target_score_for_goal( $goal ) {
		$target_scores = array(
			'Weight Loss'           => 8.5,
			'Muscle Gain'           => 8.0,
			'Energy Boost'          => 8.5,
			'Better Sleep'          => 8.0,
			'Stress Management'     => 8.0,
			'Hormone Optimization'  => 8.5,
			'Cognitive Enhancement' => 8.0,
			'Skin Health'           => 8.0,
			'Hair Health'           => 7.5,
			'Longevity'             => 9.0,
		);

		return isset( $target_scores[ $goal ] ) ? $target_scores[ $goal ] : 8.0;
	}

	/**
	 * Determine progress stage based on percentage
	 *
	 * @param float $percentage Progress percentage
	 * @return string Progress stage
	 */
	private static function determine_progress_stage( $percentage ) {
		if ( $percentage >= 80 ) {
			return 'best';
		}
		if ( $percentage >= 40 ) {
			return 'better';
		}
		return 'good';
	}

	/**
	 * Get stage description
	 *
	 * @param string $stage Progress stage
	 * @return string Stage description
	 */
	private static function get_stage_description( $stage ) {
		$descriptions = array(
			'good'   => 'Foundation Building - You\'re establishing healthy habits',
			'better' => 'Momentum Building - You\'re making solid progress',
			'best'   => 'Excellence Achieved - You\'re reaching optimal levels',
		);

		return isset( $descriptions[ $stage ] ) ? $descriptions[ $stage ] : 'Getting Started';
	}

	/**
	 * Get recommendations for a goal and stage
	 *
	 * @param string $goal Goal name
	 * @param string $stage Progress stage
	 * @return array Recommendations
	 */
	private static function get_goal_recommendations( $goal, $stage ) {
		$recommendations = array(
			'Weight Loss'  => array(
				'good'   => array( 'Focus on consistent caloric deficit', 'Track daily food intake' ),
				'better' => array( 'Add strength training', 'Optimize meal timing' ),
				'best'   => array( 'Fine-tune macronutrients', 'Consider advanced protocols' ),
			),
			'Energy Boost' => array(
				'good'   => array( 'Establish consistent sleep schedule', 'Reduce caffeine after 2 PM' ),
				'better' => array( 'Add morning sunlight exposure', 'Optimize nutrition timing' ),
				'best'   => array( 'Consider advanced supplementation', 'Track HRV and recovery' ),
			),
		);

		$goal_recs  = isset( $recommendations[ $goal ] ) ? $recommendations[ $goal ] : array();
		$stage_recs = isset( $goal_recs[ $stage ] ) ? $goal_recs[ $stage ] : array();

		return $stage_recs;
	}

	/**
	 * Calculate overall progress across all goals
	 *
	 * @param array $progression_data Individual goal progression data
	 * @return array Overall progress data
	 */
	private static function calculate_overall_progress( $progression_data ) {
		if ( empty( $progression_data ) ) {
			return array(
				'average_progress' => 0,
				'stage'            => 'getting_started',
				'goals_in_best'    => 0,
				'goals_in_better'  => 0,
				'goals_in_good'    => 0,
			);
		}

		$total_progress = 0;
		$stage_counts   = array(
			'best'   => 0,
			'better' => 0,
			'good'   => 0,
		);

		foreach ( $progression_data as $goal_data ) {
			$total_progress += $goal_data['progress_percentage'];
			$stage_counts[ $goal_data['stage'] ]++;
		}

		$average_progress = $total_progress / count( $progression_data );
		$overall_stage    = self::determine_progress_stage( $average_progress );

		return array(
			'average_progress' => round( $average_progress, 1 ),
			'stage'            => $overall_stage,
			'goals_in_best'    => $stage_counts['best'],
			'goals_in_better'  => $stage_counts['better'],
			'goals_in_good'    => $stage_counts['good'],
			'total_goals'      => count( $progression_data ),
		);
	}

	/**
	 * Check for achievements
	 *
	 * @param array $progression_data Individual goal progression data
	 * @return array Achievements
	 */
	private static function check_achievements( $progression_data ) {
		$achievements = array();

		foreach ( $progression_data as $goal_data ) {
			if ( 'best' === $goal_data['stage'] ) {
				$achievements[] = array(
					'type'    => 'goal_excellence',
					'goal'    => $goal_data['goal'],
					'message' => 'Excellent progress in ' . $goal_data['goal'] . '!',
					'date'    => current_time( 'mysql' ),
				);
			}

			if ( $goal_data['progress_percentage'] >= 50 && 'better' === $goal_data['stage'] ) {
				$achievements[] = array(
					'type'    => 'milestone_reached',
					'goal'    => $goal_data['goal'],
					'message' => 'Halfway to your ' . $goal_data['goal'] . ' goal!',
					'date'    => current_time( 'mysql' ),
				);
			}
		}

		return $achievements;
	}

	/**
	 * Get next milestones for goals
	 *
	 * @param array $progression_data Individual goal progression data
	 * @return array Next milestones
	 */
	private static function get_next_milestones( $progression_data ) {
		$milestones = array();

		foreach ( $progression_data as $goal_data ) {
			$next_milestone = '';
			$progress       = $goal_data['progress_percentage'];

			if ( $progress < 25 ) {
				$next_milestone = 'Reach 25% progress';
			} elseif ( $progress < 50 ) {
				$next_milestone = 'Reach halfway point';
			} elseif ( $progress < 75 ) {
				$next_milestone = 'Reach 75% progress';
			} elseif ( $progress < 100 ) {
				$next_milestone = 'Achieve goal excellence';
			} else {
				$next_milestone = 'Maintain excellence';
			}

			$milestones[ $goal_data['goal'] ] = $next_milestone;
		}

		return $milestones;
	}

	/**
	 * Get progression summary for dashboard
	 *
	 * @param int $user_id User ID
	 * @return array Progression summary
	 */
	public static function get_progression_summary( $user_id ) {
		$progression = get_user_meta( $user_id, 'ennu_goal_progression', true );

		if ( empty( $progression ) ) {
			$progression = self::track_progression( $user_id );
		}

		return array(
			'overall_progress'    => $progression['overall']['average_progress'],
			'stage'               => $progression['overall']['stage'],
			'recent_achievements' => array_slice( $progression['achievements'], -3 ),
			'priority_goals'      => self::get_priority_goals( $progression['goals'] ),
		);
	}

	/**
	 * Get priority goals that need attention
	 *
	 * @param array $goals_data Goals progression data
	 * @return array Priority goals
	 */
	private static function get_priority_goals( $goals_data ) {
		$priority = array();

		foreach ( $goals_data as $goal_data ) {
			if ( $goal_data['progress_percentage'] < 30 ) {
				$priority[] = array(
					'goal'        => $goal_data['goal'],
					'progress'    => $goal_data['progress_percentage'],
					'stage'       => $goal_data['stage'],
					'next_action' => isset( $goal_data['recommendations'][0] ) ? $goal_data['recommendations'][0] : 'Continue current approach',
				);
			}
		}

		return array_slice( $priority, 0, 3 );
	}
}
