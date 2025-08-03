<?php
/**
 * Progress Tracking System
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
 * Progress Tracking System
 * 
 * Tracks user progress through assessments, calculates completion percentages,
 * and implements milestone tracking and achievement badges.
 */
class ENNU_Progress_Tracker {

	/**
	 * Initialize the Progress Tracking System
	 */
	public function __construct() {
		add_action( 'wp_ajax_get_user_progress', array( $this, 'ajax_get_user_progress' ) );
		add_action( 'wp_ajax_nopriv_get_user_progress', array( $this, 'ajax_get_user_progress' ) );
		add_action( 'wp_ajax_update_progress', array( $this, 'ajax_update_progress' ) );
		add_action( 'wp_ajax_nopriv_update_progress', array( $this, 'ajax_update_progress' ) );
	}

	/**
	 * Get comprehensive progress data for a user
	 *
	 * @param int $user_id User ID
	 * @return array Progress data
	 */
	public function get_user_progress( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return array();
		}

		$progress = array(
			'profile_completion' => $this->get_profile_completion( $user_id ),
			'assessment_progress' => $this->get_assessment_progress( $user_id ),
			'biomarker_progress' => $this->get_biomarker_progress( $user_id ),
			'milestones' => $this->get_milestones( $user_id ),
			'achievements' => $this->get_achievements( $user_id ),
			'streak_data' => $this->get_streak_data( $user_id ),
			'improvement_areas' => $this->get_improvement_areas( $user_id )
		);

		return $progress;
	}

	/**
	 * Get profile completion data
	 *
	 * @param int $user_id User ID
	 * @return array Profile completion data
	 */
	private function get_profile_completion( $user_id ) {
		$completed_assessments = $this->get_completed_assessments( $user_id );
		$all_assessments = $this->get_all_available_assessments();
		$total_assessments = count( $all_assessments );
		$completed_count = count( $completed_assessments );
		$completion_percentage = $total_assessments > 0 ? round( ( $completed_count / $total_assessments ) * 100 ) : 0;

		$missing_assessments = array_diff( $all_assessments, $completed_assessments );

		return array(
			'percentage' => $completion_percentage,
			'completed_count' => $completed_count,
			'total_count' => $total_assessments,
			'missing_assessments' => $missing_assessments,
			'status' => $this->get_completion_status_text( $completion_percentage ),
			'next_assessment' => $this->get_next_recommended_assessment( $user_id )
		);
	}

	/**
	 * Get assessment progress data
	 *
	 * @param int $user_id User ID
	 * @return array Assessment progress data
	 */
	private function get_assessment_progress( $user_id ) {
		$assessments = $this->get_all_available_assessments();
		$completed_assessments = $this->get_completed_assessments( $user_id );
		$progress_data = array();

		foreach ( $assessments as $assessment ) {
			$is_completed = in_array( $assessment, $completed_assessments );
			$last_completion_date = $this->get_last_completion_date( $user_id, $assessment );
			$score = $this->get_assessment_score( $user_id, $assessment );

			$progress_data[ $assessment ] = array(
				'name' => $this->get_assessment_display_name( $assessment ),
				'completed' => $is_completed,
				'last_completion_date' => $last_completion_date,
				'score' => $score,
				'estimated_time' => $this->get_assessment_estimated_time( $assessment ),
				'priority' => $this->get_assessment_priority( $assessment ),
				'url' => $this->get_assessment_url( $assessment )
			);
		}

		return $progress_data;
	}

	/**
	 * Get biomarker progress data
	 *
	 * @param int $user_id User ID
	 * @return array Biomarker progress data
	 */
	private function get_biomarker_progress( $user_id ) {
		$critical_biomarkers = $this->get_critical_biomarkers();
		$user_biomarkers = $this->get_user_biomarkers( $user_id );
		$progress_data = array();

		foreach ( $critical_biomarkers as $biomarker => $data ) {
			$has_data = isset( $user_biomarkers[ $biomarker ] ) && ! empty( $user_biomarkers[ $biomarker ] );
			$current_value = $has_data ? $user_biomarkers[ $biomarker ] : null;
			$last_update = $this->get_biomarker_last_update( $user_id, $biomarker );

			$progress_data[ $biomarker ] = array(
				'name' => $data['name'],
				'has_data' => $has_data,
				'current_value' => $current_value,
				'last_update' => $last_update,
				'optimal_range' => $data['optimal_range'],
				'unit' => $data['unit'],
				'priority' => $data['priority']
			);
		}

		return $progress_data;
	}

	/**
	 * Get milestones for the user
	 *
	 * @param int $user_id User ID
	 * @return array Milestones data
	 */
	private function get_milestones( $user_id ) {
		$milestones = array();
		$completed_assessments = $this->get_completed_assessments( $user_id );
		$completed_count = count( $completed_assessments );

		// Assessment completion milestones
		$assessment_milestones = array(
			1 => array( 'name' => 'First Assessment', 'description' => 'Completed your first assessment', 'icon' => '🎯' ),
			3 => array( 'name' => 'Getting Started', 'description' => 'Completed 3 assessments', 'icon' => '🚀' ),
			5 => array( 'name' => 'Halfway There', 'description' => 'Completed 5 assessments', 'icon' => '📊' ),
			8 => array( 'name' => 'Comprehensive Profile', 'description' => 'Completed all assessments', 'icon' => '🏆' )
		);

		foreach ( $assessment_milestones as $count => $milestone ) {
			$achieved = $completed_count >= $count;
			$achievement_date = $achieved ? $this->get_milestone_achievement_date( $user_id, "assessment_{$count}" ) : null;

			$milestones[] = array(
				'type' => 'assessment_completion',
				'name' => $milestone['name'],
				'description' => $milestone['description'],
				'icon' => $milestone['icon'],
				'required_count' => $count,
				'current_count' => $completed_count,
				'achieved' => $achieved,
				'achievement_date' => $achievement_date
			);
		}

		// Biomarker milestones
		$biomarker_count = $this->get_completed_biomarker_count( $user_id );
		$biomarker_milestones = array(
			3 => array( 'name' => 'Basic Labs', 'description' => 'Imported 3 biomarker results', 'icon' => '🔬' ),
			6 => array( 'name' => 'Comprehensive Labs', 'description' => 'Imported 6 biomarker results', 'icon' => '📈' ),
			10 => array( 'name' => 'Full Lab Profile', 'description' => 'Imported all critical biomarkers', 'icon' => '🏥' )
		);

		foreach ( $biomarker_milestones as $count => $milestone ) {
			$achieved = $biomarker_count >= $count;
			$achievement_date = $achieved ? $this->get_milestone_achievement_date( $user_id, "biomarker_{$count}" ) : null;

			$milestones[] = array(
				'type' => 'biomarker_completion',
				'name' => $milestone['name'],
				'description' => $milestone['description'],
				'icon' => $milestone['icon'],
				'required_count' => $count,
				'current_count' => $biomarker_count,
				'achieved' => $achieved,
				'achievement_date' => $achievement_date
			);
		}

		return $milestones;
	}

	/**
	 * Get achievements for the user
	 *
	 * @param int $user_id User ID
	 * @return array Achievements data
	 */
	private function get_achievements( $user_id ) {
		$achievements = array();

		// Score-based achievements
		$pillar_scores = $this->get_pillar_scores( $user_id );
		foreach ( $pillar_scores as $pillar => $score ) {
			if ( $score >= 8.0 ) {
				$achievements[] = array(
					'type' => 'high_score',
					'name' => "{$pillar} Master",
					'description' => "Achieved a score of {$score} in {$pillar}",
					'icon' => '⭐',
					'achievement_date' => $this->get_achievement_date( $user_id, "high_score_{$pillar}" )
				);
			}
		}

		// Consistency achievements
		$streak_days = $this->get_current_streak_days( $user_id );
		$streak_achievements = array(
			7 => array( 'name' => 'Week Warrior', 'description' => '7 days of consistent activity', 'icon' => '🔥' ),
			30 => array( 'name' => 'Monthly Master', 'description' => '30 days of consistent activity', 'icon' => '📅' ),
			90 => array( 'name' => 'Quarterly Champion', 'description' => '90 days of consistent activity', 'icon' => '🏆' )
		);

		foreach ( $streak_achievements as $days => $achievement ) {
			if ( $streak_days >= $days ) {
				$achievements[] = array(
					'type' => 'streak',
					'name' => $achievement['name'],
					'description' => $achievement['description'],
					'icon' => $achievement['icon'],
					'achievement_date' => $this->get_achievement_date( $user_id, "streak_{$days}" )
				);
			}
		}

		return $achievements;
	}

	/**
	 * Get streak data for the user
	 *
	 * @param int $user_id User ID
	 * @return array Streak data
	 */
	private function get_streak_data( $user_id ) {
		$current_streak = $this->get_current_streak_days( $user_id );
		$longest_streak = $this->get_longest_streak_days( $user_id );
		$last_activity = $this->get_last_activity_date( $user_id );

		return array(
			'current_streak' => $current_streak,
			'longest_streak' => $longest_streak,
			'last_activity' => $last_activity,
			'streak_status' => $this->get_streak_status_text( $current_streak )
		);
	}

	/**
	 * Get improvement areas for the user
	 *
	 * @param int $user_id User ID
	 * @return array Improvement areas
	 */
	private function get_improvement_areas( $user_id ) {
		$improvement_areas = array();
		$pillar_scores = $this->get_pillar_scores( $user_id );

		// Find areas with scores below 7.0
		foreach ( $pillar_scores as $pillar => $score ) {
			if ( $score < 7.0 ) {
				$improvement_areas[] = array(
					'pillar' => $pillar,
					'current_score' => $score,
					'target_score' => 8.0,
					'improvement_needed' => round( 8.0 - $score, 1 ),
					'recommendations' => $this->get_pillar_improvement_recommendations( $pillar )
				);
			}
		}

		// Sort by improvement needed (highest first)
		usort( $improvement_areas, function( $a, $b ) {
			return $b['improvement_needed'] - $a['improvement_needed'];
		} );

		return $improvement_areas;
	}

	/**
	 * Get all available assessments
	 *
	 * @return array All available assessments
	 */
	private function get_all_available_assessments() {
		return array(
			'stress',
			'cognitive',
			'mental_health',
			'sleep',
			'nutrition',
			'physical_activity',
			'aesthetics',
			'biomarkers'
		);
	}

	/**
	 * Get completed assessments for user
	 *
	 * @param int $user_id User ID
	 * @return array Completed assessments
	 */
	private function get_completed_assessments( $user_id ) {
		// Get user's actual assessment history from database
		$user_assessments = get_user_meta( $user_id, 'ennu_assessments', true );
		if ( ! is_array( $user_assessments ) ) {
			return array();
		}
		
		$completed = array();
		foreach ( $user_assessments as $assessment_type => $data ) {
			if ( ! empty( $data['submitted_at'] ) ) {
				$completed[] = $assessment_type;
			}
		}
		
		return $completed;
	}

	/**
	 * Get critical biomarkers
	 *
	 * @return array Critical biomarkers
	 */
	private function get_critical_biomarkers() {
		return array(
			'glucose' => array(
				'name' => 'Blood Glucose',
				'optimal_range' => '70-100 mg/dL',
				'unit' => 'mg/dL',
				'priority' => 'high'
			),
			'hba1c' => array(
				'name' => 'HbA1c',
				'optimal_range' => '< 5.7%',
				'unit' => '%',
				'priority' => 'high'
			),
			'testosterone_total' => array(
				'name' => 'Total Testosterone',
				'optimal_range' => '300-1000 ng/dL',
				'unit' => 'ng/dL',
				'priority' => 'high'
			),
			'cortisol' => array(
				'name' => 'Cortisol',
				'optimal_range' => '6-20 μg/dL',
				'unit' => 'μg/dL',
				'priority' => 'medium'
			),
			'vitamin_d' => array(
				'name' => 'Vitamin D',
				'optimal_range' => '30-50 ng/mL',
				'unit' => 'ng/mL',
				'priority' => 'medium'
			),
			'tsh' => array(
				'name' => 'TSH',
				'optimal_range' => '0.4-4.0 mIU/L',
				'unit' => 'mIU/L',
				'priority' => 'medium'
			)
		);
	}

	/**
	 * Get user biomarkers
	 *
	 * @param int $user_id User ID
	 * @return array User biomarkers
	 */
	private function get_user_biomarkers( $user_id ) {
		// Get user's actual biomarker data from database
		$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		if ( ! is_array( $biomarker_data ) ) {
			return array();
		}
		
		$user_biomarkers = array();
		foreach ( $biomarker_data as $biomarker => $data ) {
			if ( isset( $data['value'] ) && ! empty( $data['value'] ) ) {
				$user_biomarkers[ $biomarker ] = $data['value'];
			}
		}
		
		return $user_biomarkers;
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
	 * Get completion status text
	 *
	 * @param int $percentage Completion percentage
	 * @return string Status text
	 */
	private function get_completion_status_text( $percentage ) {
		if ( $percentage >= 90 ) {
			return 'Excellent! Your profile is nearly complete.';
		} elseif ( $percentage >= 70 ) {
			return 'Good progress! A few more assessments to go.';
		} elseif ( $percentage >= 50 ) {
			return 'You\'re halfway there! Keep going.';
		} elseif ( $percentage >= 30 ) {
			return 'Getting started! Complete more assessments for better insights.';
		} else {
			return 'Just getting started! Complete assessments to unlock your full profile.';
		}
	}

	/**
	 * Get next recommended assessment
	 *
	 * @param int $user_id User ID
	 * @return string Next recommended assessment
	 */
	private function get_next_recommended_assessment( $user_id ) {
		$completed_assessments = $this->get_completed_assessments( $user_id );
		$all_assessments = $this->get_all_available_assessments();
		$missing_assessments = array_diff( $all_assessments, $completed_assessments );

		// Priority order for recommendations
		$priority_order = array( 'stress', 'sleep', 'nutrition', 'cognitive', 'mental_health', 'physical_activity', 'aesthetics', 'biomarkers' );

		foreach ( $priority_order as $assessment ) {
			if ( in_array( $assessment, $missing_assessments ) ) {
				return $assessment;
			}
		}

		return ! empty( $missing_assessments ) ? reset( $missing_assessments ) : '';
	}

	/**
	 * Get assessment display name
	 *
	 * @param string $assessment Assessment key
	 * @return string Display name
	 */
	private function get_assessment_display_name( $assessment ) {
		$names = array(
			'stress' => 'Stress Assessment',
			'cognitive' => 'Cognitive Assessment',
			'mental_health' => 'Mental Health Assessment',
			'sleep' => 'Sleep Assessment',
			'nutrition' => 'Nutrition Assessment',
			'physical_activity' => 'Physical Activity Assessment',
			'aesthetics' => 'Aesthetics Assessment',
			'biomarkers' => 'Biomarker Assessment'
		);

		return isset( $names[ $assessment ] ) ? $names[ $assessment ] : ucfirst( $assessment ) . ' Assessment';
	}

	/**
	 * Get assessment estimated time
	 *
	 * @param string $assessment Assessment key
	 * @return string Estimated time
	 */
	private function get_assessment_estimated_time( $assessment ) {
		$times = array(
			'stress' => '5-10 minutes',
			'cognitive' => '10-15 minutes',
			'mental_health' => '10-15 minutes',
			'sleep' => '5-10 minutes',
			'nutrition' => '10-15 minutes',
			'physical_activity' => '5-10 minutes',
			'aesthetics' => '5-10 minutes',
			'biomarkers' => '5 minutes'
		);

		return isset( $times[ $assessment ] ) ? $times[ $assessment ] : '5-10 minutes';
	}

	/**
	 * Get assessment priority
	 *
	 * @param string $assessment Assessment key
	 * @return string Priority level
	 */
	private function get_assessment_priority( $assessment ) {
		$high_priority = array( 'stress', 'sleep', 'nutrition' );
		$medium_priority = array( 'cognitive', 'mental_health', 'physical_activity' );
		$low_priority = array( 'aesthetics', 'biomarkers' );

		if ( in_array( $assessment, $high_priority ) ) {
			return 'high';
		} elseif ( in_array( $assessment, $medium_priority ) ) {
			return 'medium';
		} else {
			return 'low';
		}
	}

	/**
	 * Get assessment URL
	 *
	 * @param string $assessment Assessment key
	 * @return string Assessment URL
	 */
	private function get_assessment_url( $assessment ) {
		return home_url( "/assessment/{$assessment}/" );
	}

	/**
	 * Get last completion date for assessment
	 *
	 * @param int $user_id User ID
	 * @param string $assessment Assessment key
	 * @return string|null Last completion date
	 */
	private function get_last_completion_date( $user_id, $assessment ) {
		// This would get the actual last completion date
		// For now, return null
		return null;
	}

	/**
	 * Get assessment score
	 *
	 * @param int $user_id User ID
	 * @param string $assessment Assessment key
	 * @return float|null Assessment score
	 */
	private function get_assessment_score( $user_id, $assessment ) {
		// This would get the actual assessment score
		// For now, return null
		return null;
	}

	/**
	 * Get biomarker last update
	 *
	 * @param int $user_id User ID
	 * @param string $biomarker Biomarker key
	 * @return string|null Last update date
	 */
	private function get_biomarker_last_update( $user_id, $biomarker ) {
		// This would get the actual last update date
		// For now, return null
		return null;
	}

	/**
	 * Get completed biomarker count
	 *
	 * @param int $user_id User ID
	 * @return int Completed biomarker count
	 */
	private function get_completed_biomarker_count( $user_id ) {
		$user_biomarkers = $this->get_user_biomarkers( $user_id );
		return count( $user_biomarkers );
	}

	/**
	 * Get milestone achievement date
	 *
	 * @param int $user_id User ID
	 * @param string $milestone_key Milestone key
	 * @return string|null Achievement date
	 */
	private function get_milestone_achievement_date( $user_id, $milestone_key ) {
		// This would get the actual achievement date
		// For now, return null
		return null;
	}

	/**
	 * Get achievement date
	 *
	 * @param int $user_id User ID
	 * @param string $achievement_key Achievement key
	 * @return string|null Achievement date
	 */
	private function get_achievement_date( $user_id, $achievement_key ) {
		// This would get the actual achievement date
		// For now, return null
		return null;
	}

	/**
	 * Get current streak days
	 *
	 * @param int $user_id User ID
	 * @return int Current streak days
	 */
	private function get_current_streak_days( $user_id ) {
		// Get actual streak data from user meta
		$streak_data = get_user_meta( $user_id, 'ennu_activity_streak', true );
		
		if ( ! empty( $streak_data ) && is_array( $streak_data ) ) {
			return $streak_data['current_streak'] ?? 0;
		}
		
		// Calculate streak from activity log
		$activity_log = get_user_meta( $user_id, 'ennu_activity_log', true );
		if ( ! empty( $activity_log ) && is_array( $activity_log ) ) {
			$current_streak = 0;
			$today = date( 'Y-m-d' );
			
			// Count consecutive days with activity
			for ( $i = 0; $i < 30; $i++ ) {
				$check_date = date( 'Y-m-d', strtotime( "-$i days" ) );
				if ( isset( $activity_log[ $check_date ] ) ) {
					$current_streak++;
				} else {
					break;
				}
			}
			
			return $current_streak;
		}
		
		return 0;
	}

	/**
	 * Get longest streak days
	 *
	 * @param int $user_id User ID
	 * @return int Longest streak days
	 */
	private function get_longest_streak_days( $user_id ) {
		// Get actual streak data from user meta
		$streak_data = get_user_meta( $user_id, 'ennu_activity_streak', true );
		
		if ( ! empty( $streak_data ) && is_array( $streak_data ) ) {
			return $streak_data['longest_streak'] ?? 0;
		}
		
		// Calculate longest streak from activity log
		$activity_log = get_user_meta( $user_id, 'ennu_activity_log', true );
		if ( ! empty( $activity_log ) && is_array( $activity_log ) ) {
			$longest_streak = 0;
			$current_streak = 0;
			
			// Sort activity log by date
			ksort( $activity_log );
			
			foreach ( $activity_log as $date => $activity ) {
				$current_streak++;
				if ( $current_streak > $longest_streak ) {
					$longest_streak = $current_streak;
				}
			}
			
			return $longest_streak;
		}
		
		return 0;
	}

	/**
	 * Get last activity date
	 *
	 * @param int $user_id User ID
	 * @return string|null Last activity date
	 */
	private function get_last_activity_date( $user_id ) {
		// This would get the actual last activity date
		// For now, return null
		return null;
	}

	/**
	 * Get streak status text
	 *
	 * @param int $streak_days Streak days
	 * @return string Status text
	 */
	private function get_streak_status_text( $streak_days ) {
		if ( $streak_days >= 30 ) {
			return 'Amazing! You\'re on fire! 🔥';
		} elseif ( $streak_days >= 14 ) {
			return 'Great consistency! Keep it up! 💪';
		} elseif ( $streak_days >= 7 ) {
			return 'Good start! Building momentum! 🚀';
		} elseif ( $streak_days >= 3 ) {
			return 'Getting into the groove! 📈';
		} else {
			return 'Starting your journey! Every day counts! 🌟';
		}
	}

	/**
	 * Get pillar improvement recommendations
	 *
	 * @param string $pillar Pillar name
	 * @return array Improvement recommendations
	 */
	private function get_pillar_improvement_recommendations( $pillar ) {
		$recommendations = array(
			'Mind' => array(
				'Take stress management assessment',
				'Practice mindfulness techniques',
				'Consider cognitive training exercises'
			),
			'Body' => array(
				'Complete physical activity assessment',
				'Import recent lab results',
				'Track your biomarkers regularly'
			),
			'Lifestyle' => array(
				'Complete sleep assessment',
				'Take nutrition assessment',
				'Track daily habits and routines'
			),
			'Aesthetics' => array(
				'Complete aesthetics assessment',
				'Track appearance-related markers',
				'Consider consultation for personalized advice'
			)
		);

		return isset( $recommendations[ $pillar ] ) ? $recommendations[ $pillar ] : array();
	}

	/**
	 * AJAX handler for getting user progress
	 */
	public function ajax_get_user_progress() {
		check_ajax_referer( 'ennu_progress_nonce', 'nonce' );

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_die( 'User not logged in' );
		}

		$progress = $this->get_user_progress( $user_id );
		wp_send_json_success( $progress );
	}

	/**
	 * AJAX handler for updating progress
	 */
	public function ajax_update_progress() {
		check_ajax_referer( 'ennu_progress_nonce', 'nonce' );

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_die( 'User not logged in' );
		}

		$action = sanitize_text_field( $_POST['action_type'] ?? '' );
		$data = $_POST['data'] ?? array();

		$result = $this->update_progress( $user_id, $action, $data );
		wp_send_json_success( $result );
	}

	/**
	 * Update progress for a user
	 *
	 * @param int $user_id User ID
	 * @param string $action Action type
	 * @param array $data Action data
	 * @return array Update result
	 */
	public function update_progress( $user_id, $action, $data ) {
		// This would update the actual progress data
		// For now, return success
		return array(
			'success' => true,
			'message' => 'Progress updated successfully'
		);
	}

	/**
	 * Render progress widget
	 *
	 * @param int $user_id User ID
	 * @return string Rendered widget HTML
	 */
	public function render_progress_widget( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return '';
		}

		$progress = $this->get_user_progress( $user_id );

		ob_start();
		?>
		<div class="ennu-progress-widget" data-user-id="<?php echo esc_attr( $user_id ); ?>">
			<div class="ennu-progress-header">
				<h3>📊 Your Progress</h3>
				<p>Track your health journey and celebrate achievements</p>
			</div>

			<?php if ( ! empty( $progress['profile_completion'] ) ) : ?>
				<div class="ennu-profile-completion">
					<h4>Profile Completion</h4>
					<div class="ennu-completion-progress">
						<div class="ennu-progress-bar">
							<div class="ennu-progress-fill" style="width: <?php echo esc_attr( $progress['profile_completion']['percentage'] ); ?>%;"></div>
						</div>
						<div class="ennu-progress-text">
							<?php echo esc_html( $progress['profile_completion']['completed_count'] ); ?> of <?php echo esc_html( $progress['profile_completion']['total_count'] ); ?> assessments completed
							(<?php echo esc_html( $progress['profile_completion']['percentage'] ); ?>%)
						</div>
					</div>
					<p class="ennu-completion-status-text"><?php echo esc_html( $progress['profile_completion']['status'] ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $progress['milestones'] ) ) : ?>
				<div class="ennu-milestones">
					<h4>Milestones</h4>
					<div class="ennu-milestones-grid">
						<?php foreach ( $progress['milestones'] as $milestone ) : ?>
							<div class="ennu-milestone-item <?php echo $milestone['achieved'] ? 'achieved' : 'pending'; ?>">
								<div class="ennu-milestone-icon"><?php echo esc_html( $milestone['icon'] ); ?></div>
								<div class="ennu-milestone-content">
									<h5><?php echo esc_html( $milestone['name'] ); ?></h5>
									<p><?php echo esc_html( $milestone['description'] ); ?></p>
									<div class="ennu-milestone-progress">
										<?php echo esc_html( $milestone['current_count'] ); ?> / <?php echo esc_html( $milestone['required_count'] ); ?>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $progress['achievements'] ) ) : ?>
				<div class="ennu-achievements">
					<h4>Recent Achievements</h4>
					<div class="ennu-achievements-grid">
						<?php foreach ( array_slice( $progress['achievements'], 0, 3 ) as $achievement ) : ?>
							<div class="ennu-achievement-item">
								<div class="ennu-achievement-icon"><?php echo esc_html( $achievement['icon'] ); ?></div>
								<div class="ennu-achievement-content">
									<h5><?php echo esc_html( $achievement['name'] ); ?></h5>
									<p><?php echo esc_html( $achievement['description'] ); ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $progress['streak_data'] ) ) : ?>
				<div class="ennu-streak-data">
					<h4>Activity Streak</h4>
					<div class="ennu-streak-info">
						<div class="ennu-streak-current">
							<span class="ennu-streak-number"><?php echo esc_html( $progress['streak_data']['current_streak'] ); ?></span>
							<span class="ennu-streak-label">Current Streak</span>
						</div>
						<div class="ennu-streak-longest">
							<span class="ennu-streak-number"><?php echo esc_html( $progress['streak_data']['longest_streak'] ); ?></span>
							<span class="ennu-streak-label">Longest Streak</span>
						</div>
					</div>
					<p class="ennu-streak-status"><?php echo esc_html( $progress['streak_data']['streak_status'] ); ?></p>
				</div>
			<?php endif; ?>
		</div>

		<style>
		.ennu-progress-widget {
			background: #ffffff;
			border-radius: 12px;
			padding: 24px;
			box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
			margin-bottom: 24px;
		}

		.ennu-progress-header {
			margin-bottom: 24px;
			text-align: center;
		}

		.ennu-progress-header h3 {
			margin: 0 0 8px 0;
			color: #2c3e50;
			font-size: 1.5rem;
		}

		.ennu-progress-header p {
			margin: 0;
			color: #7f8c8d;
			font-size: 0.9rem;
		}

		.ennu-profile-completion,
		.ennu-milestones,
		.ennu-achievements,
		.ennu-streak-data {
			margin-bottom: 24px;
		}

		.ennu-profile-completion h4,
		.ennu-milestones h4,
		.ennu-achievements h4,
		.ennu-streak-data h4 {
			margin: 0 0 16px 0;
			color: #2c3e50;
			font-size: 1.2rem;
		}

		.ennu-completion-progress {
			margin-bottom: 12px;
		}

		.ennu-progress-bar {
			width: 100%;
			height: 8px;
			background: #e2e8f0;
			border-radius: 4px;
			overflow: hidden;
			margin-bottom: 8px;
		}

		.ennu-progress-fill {
			height: 100%;
			background: linear-gradient(90deg, #3498db, #2980b9);
			transition: width 0.3s ease;
		}

		.ennu-progress-text {
			font-size: 0.9rem;
			color: #2c3e50;
			font-weight: 500;
		}

		.ennu-completion-status-text {
			margin: 0;
			color: #7f8c8d;
			font-size: 0.9rem;
			font-style: italic;
		}

		.ennu-milestones-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
			gap: 16px;
		}

		.ennu-milestone-item {
			display: flex;
			align-items: flex-start;
			padding: 16px;
			border-radius: 8px;
			border: 2px solid;
		}

		.ennu-milestone-item.achieved {
			background: #f0fff4;
			border-color: #38a169;
		}

		.ennu-milestone-item.pending {
			background: #f7fafc;
			border-color: #e2e8f0;
		}

		.ennu-milestone-icon {
			font-size: 1.5rem;
			margin-right: 12px;
			margin-top: 2px;
		}

		.ennu-milestone-content {
			flex: 1;
		}

		.ennu-milestone-content h5 {
			margin: 0 0 8px 0;
			color: #2c3e50;
			font-size: 1rem;
		}

		.ennu-milestone-content p {
			margin: 0 0 8px 0;
			color: #7f8c8d;
			font-size: 0.9rem;
			line-height: 1.4;
		}

		.ennu-milestone-progress {
			font-size: 0.8rem;
			color: #2c3e50;
			font-weight: 600;
		}

		.ennu-achievements-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
			gap: 16px;
		}

		.ennu-achievement-item {
			display: flex;
			align-items: flex-start;
			padding: 16px;
			border-radius: 8px;
			background: #fffaf0;
			border-left: 4px solid #dd6b20;
		}

		.ennu-achievement-icon {
			font-size: 1.5rem;
			margin-right: 12px;
			margin-top: 2px;
		}

		.ennu-achievement-content {
			flex: 1;
		}

		.ennu-achievement-content h5 {
			margin: 0 0 8px 0;
			color: #2c3e50;
			font-size: 1rem;
		}

		.ennu-achievement-content p {
			margin: 0;
			color: #7f8c8d;
			font-size: 0.9rem;
			line-height: 1.4;
		}

		.ennu-streak-info {
			display: flex;
			justify-content: space-around;
			margin-bottom: 12px;
		}

		.ennu-streak-current,
		.ennu-streak-longest {
			text-align: center;
		}

		.ennu-streak-number {
			display: block;
			font-size: 2rem;
			font-weight: 700;
			color: #3498db;
		}

		.ennu-streak-label {
			display: block;
			font-size: 0.8rem;
			color: #7f8c8d;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}

		.ennu-streak-status {
			margin: 0;
			color: #7f8c8d;
			font-size: 0.9rem;
			font-style: italic;
			text-align: center;
		}
		</style>
		<?php
		return ob_get_clean();
	}
}

// Initialize the Progress Tracking System
new ENNU_Progress_Tracker(); 