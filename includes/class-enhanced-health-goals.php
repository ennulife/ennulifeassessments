<?php
/**
 * ENNU Enhanced Health Goals System
 *
 * Provides enhanced health goals functionality with progressive collection,
 * smart recommendations, and goal achievement tracking.
 *
 * @package ENNU_Life
 * @version 62.2.8
 * @author ENNU Life Development Team
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Enhanced_Health_Goals {

	/**
	 * Initialize the enhanced health goals system
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_ennu_update_health_goals', array( __CLASS__, 'ajax_update_health_goals' ) );
		add_action( 'wp_ajax_nopriv_ennu_update_health_goals', array( __CLASS__, 'ajax_update_health_goals' ) );
		add_action( 'wp_ajax_ennu_get_goal_recommendations', array( __CLASS__, 'ajax_get_goal_recommendations' ) );
		add_action( 'wp_ajax_nopriv_ennu_get_goal_recommendations', array( __CLASS__, 'ajax_get_goal_recommendations' ) );
		add_filter( 'ennu_health_goals_options', array( __CLASS__, 'enhance_goals_options' ), 10, 2 );
	}

	/**
	 * Enqueue enhanced health goals scripts
	 */
	public static function enqueue_scripts() {
		wp_localize_script(
			'ennu-frontend-forms',
			'ennu_enhanced_goals',
			array(
				'ajax_url'           => admin_url( 'admin-ajax.php' ),
				'nonce'              => wp_create_nonce( 'ennu_enhanced_goals_nonce' ),
				'goal_categories'    => self::get_goal_categories(),
				'smart_suggestions'  => self::get_smart_suggestions_config(),
			)
		);
	}

	/**
	 * Get enhanced goal categories with descriptions and priorities
	 *
	 * @return array Goal categories
	 */
	public static function get_goal_categories() {
		return array(
			'foundational' => array(
				'label'       => 'Foundational Health',
				'description' => 'Essential health basics that form the foundation of wellness',
				'priority'    => 1,
				'goals'       => array(
					'energy'       => array(
						'label'       => 'Improve Energy & Vitality',
						'description' => 'Boost daily energy levels and reduce fatigue',
						'icon'        => '⚡',
						'priority'    => 1,
					),
					'sleep'        => array(
						'label'       => 'Improve Sleep Quality',
						'description' => 'Enhance sleep duration and quality for better recovery',
						'icon'        => '😴',
						'priority'    => 2,
					),
					'stress'       => array(
						'label'       => 'Reduce Stress & Improve Resilience',
						'description' => 'Manage stress levels and build mental resilience',
						'icon'        => '🧘',
						'priority'    => 3,
					),
				),
			),
			'physical' => array(
				'label'       => 'Physical Optimization',
				'description' => 'Enhance physical performance and body composition',
				'priority'    => 2,
				'goals'       => array(
					'weight_loss'      => array(
						'label'       => 'Achieve & Maintain Healthy Weight',
						'description' => 'Reach and sustain optimal body weight',
						'icon'        => '⚖️',
						'priority'    => 1,
					),
					'strength'         => array(
						'label'       => 'Build Strength & Muscle',
						'description' => 'Increase muscle mass and physical strength',
						'icon'        => '💪',
						'priority'    => 2,
					),
					'heart_health'     => array(
						'label'       => 'Support Heart Health',
						'description' => 'Improve cardiovascular health and endurance',
						'icon'        => '❤️',
						'priority'    => 3,
					),
				),
			),
			'hormonal' => array(
				'label'       => 'Hormonal Balance',
				'description' => 'Optimize hormone levels for better health and vitality',
				'priority'    => 3,
				'goals'       => array(
					'hormonal_balance' => array(
						'label'       => 'Hormonal Balance',
						'description' => 'Optimize hormone levels for overall wellness',
						'icon'        => '⚖️',
						'priority'    => 1,
					),
					'libido'           => array(
						'label'       => 'Enhance Libido & Sexual Health',
						'description' => 'Improve sexual health and intimate wellness',
						'icon'        => '💕',
						'priority'    => 2,
					),
				),
			),
			'longevity' => array(
				'label'       => 'Longevity & Aging',
				'description' => 'Focus on healthy aging and long-term wellness',
				'priority'    => 4,
				'goals'       => array(
					'longevity'        => array(
						'label'       => 'Longevity & Healthy Aging',
						'description' => 'Promote healthy aging and increase lifespan',
						'icon'        => '🌱',
						'priority'    => 1,
					),
					'cognitive_health' => array(
						'label'       => 'Sharpen Cognitive Function',
						'description' => 'Enhance memory, focus, and mental clarity',
						'icon'        => '🧠',
						'priority'    => 2,
					),
				),
			),
			'aesthetic' => array(
				'label'       => 'Aesthetic Enhancement',
				'description' => 'Improve appearance and aesthetic wellness',
				'priority'    => 5,
				'goals'       => array(
					'aesthetics'       => array(
						'label'       => 'Improve Hair, Skin & Nails',
						'description' => 'Enhance appearance and aesthetic health',
						'icon'        => '✨',
						'priority'    => 1,
					),
				),
			),
		);
	}

	/**
	 * Get smart suggestions configuration
	 *
	 * @return array Smart suggestions rules
	 */
	public static function get_smart_suggestions_config() {
		return array(
			'age_based' => array(
				'20-29' => array( 'strength', 'energy', 'aesthetics' ),
				'30-39' => array( 'energy', 'weight_loss', 'stress', 'hormonal_balance' ),
				'40-49' => array( 'hormonal_balance', 'heart_health', 'cognitive_health', 'longevity' ),
				'50+'   => array( 'longevity', 'cognitive_health', 'heart_health', 'hormonal_balance' ),
			),
			'gender_based' => array(
				'male'   => array( 'strength', 'libido', 'heart_health' ),
				'female' => array( 'hormonal_balance', 'aesthetics', 'weight_loss' ),
			),
			'assessment_based' => array(
				'testosterone' => array( 'libido', 'strength', 'energy' ),
				'hormone'      => array( 'hormonal_balance', 'energy', 'sleep' ),
				'weight-loss'  => array( 'weight_loss', 'strength', 'heart_health' ),
				'sleep'        => array( 'sleep', 'energy', 'stress' ),
				'hair'         => array( 'aesthetics', 'hormonal_balance' ),
				'skin'         => array( 'aesthetics', 'stress', 'sleep' ),
			),
		);
	}

	/**
	 * AJAX handler to update health goals
	 */
	public static function ajax_update_health_goals() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_enhanced_goals_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( 'User not logged in' );
		}

		$goals = $_POST['goals'] ?? array();
		$goals = array_map( 'sanitize_text_field', $goals );

		$valid_goals = self::get_all_goal_keys();
		$goals = array_intersect( $goals, $valid_goals );

		$saved = update_user_meta( $user_id, 'ennu_global_health_goals', $goals );

		if ( $saved ) {
			do_action( 'ennu_health_goals_updated', $user_id, $goals );

			$recommendations = self::get_goal_recommendations( $user_id );

			wp_send_json_success(
				array(
					'message'         => 'Health goals updated successfully',
					'goals'           => $goals,
					'recommendations' => $recommendations,
				)
			);
		} else {
			wp_send_json_error( 'Failed to update health goals' );
		}
	}

	/**
	 * AJAX handler to get goal recommendations
	 */
	public static function ajax_get_goal_recommendations() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_enhanced_goals_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( 'User not logged in' );
		}

		$current_goals = $_POST['current_goals'] ?? array();
		$user_data = $_POST['user_data'] ?? array();

		$recommendations = self::generate_smart_recommendations( $user_id, $current_goals, $user_data );

		wp_send_json_success(
			array(
				'recommendations' => $recommendations,
				'explanation'     => self::get_recommendation_explanation( $recommendations, $user_data ),
			)
		);
	}

	/**
	 * Get all available goal keys
	 *
	 * @return array Goal keys
	 */
	public static function get_all_goal_keys() {
		$categories = self::get_goal_categories();
		$goal_keys = array();

		foreach ( $categories as $category ) {
			$goal_keys = array_merge( $goal_keys, array_keys( $category['goals'] ) );
		}

		return $goal_keys;
	}

	/**
	 * Get goal recommendations for a user
	 *
	 * @param int $user_id User ID
	 * @return array Goal recommendations
	 */
	public static function get_goal_recommendations( $user_id ) {
		$user_data = ENNU_Progressive_Data_Collector::get_user_global_data( $user_id );
		$current_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true ) ?: array();

		return self::generate_smart_recommendations( $user_id, $current_goals, $user_data );
	}

	/**
	 * Generate smart goal recommendations
	 *
	 * @param int   $user_id User ID
	 * @param array $current_goals Current goals
	 * @param array $user_data User data
	 * @return array Recommendations
	 */
	private static function generate_smart_recommendations( $user_id, $current_goals, $user_data ) {
		$recommendations = array();
		$suggestions_config = self::get_smart_suggestions_config();

		if ( isset( $user_data['user_dob_combined'] ) ) {
			$age = self::calculate_age( $user_data['user_dob_combined'] );
			$age_group = self::get_age_group( $age );

			if ( isset( $suggestions_config['age_based'][ $age_group ] ) ) {
				$age_suggestions = $suggestions_config['age_based'][ $age_group ];
				foreach ( $age_suggestions as $goal ) {
					if ( ! in_array( $goal, $current_goals, true ) ) {
						$recommendations['age_based'][] = $goal;
					}
				}
			}
		}

		if ( isset( $user_data['gender'] ) && isset( $suggestions_config['gender_based'][ $user_data['gender'] ] ) ) {
			$gender_suggestions = $suggestions_config['gender_based'][ $user_data['gender'] ];
			foreach ( $gender_suggestions as $goal ) {
				if ( ! in_array( $goal, $current_goals, true ) ) {
					$recommendations['gender_based'][] = $goal;
				}
			}
		}

		$completed_assessments = self::get_user_completed_assessments( $user_id );
		foreach ( $completed_assessments as $assessment ) {
			if ( isset( $suggestions_config['assessment_based'][ $assessment ] ) ) {
				$assessment_suggestions = $suggestions_config['assessment_based'][ $assessment ];
				foreach ( $assessment_suggestions as $goal ) {
					if ( ! in_array( $goal, $current_goals, true ) ) {
						$recommendations['assessment_based'][] = $goal;
					}
				}
			}
		}

		$recommendations = self::prioritize_recommendations( $recommendations );

		return $recommendations;
	}

	/**
	 * Calculate age from date of birth
	 *
	 * @param string $dob Date of birth
	 * @return int Age
	 */
	private static function calculate_age( $dob ) {
		if ( empty( $dob ) ) {
			return 0;
		}

		$birth_date = new DateTime( $dob );
		$current_date = new DateTime();
		$age = $current_date->diff( $birth_date )->y;

		return $age;
	}

	/**
	 * Get age group for recommendations
	 *
	 * @param int $age Age
	 * @return string Age group
	 */
	private static function get_age_group( $age ) {
		if ( $age < 30 ) {
			return '20-29';
		} elseif ( $age < 40 ) {
			return '30-39';
		} elseif ( $age < 50 ) {
			return '40-49';
		} else {
			return '50+';
		}
	}

	/**
	 * Get user's completed assessments
	 *
	 * @param int $user_id User ID
	 * @return array Completed assessments
	 */
	private static function get_user_completed_assessments( $user_id ) {
		return array();
	}

	/**
	 * Prioritize recommendations by removing duplicates and scoring
	 *
	 * @param array $recommendations Raw recommendations
	 * @return array Prioritized recommendations
	 */
	private static function prioritize_recommendations( $recommendations ) {
		$all_recommendations = array();
		$scores = array();

		foreach ( $recommendations as $source => $goals ) {
			foreach ( $goals as $goal ) {
				if ( ! isset( $scores[ $goal ] ) ) {
					$scores[ $goal ] = 0;
				}
				$scores[ $goal ]++;
				$all_recommendations[ $goal ] = $source;
			}
		}

		arsort( $scores );

		$prioritized = array();
		$count = 0;
		foreach ( $scores as $goal => $score ) {
			if ( $count >= 5 ) { // Limit to top 5 recommendations
				break;
			}
			$prioritized[] = array(
				'goal'   => $goal,
				'score'  => $score,
				'source' => $all_recommendations[ $goal ],
			);
			$count++;
		}

		return $prioritized;
	}

	/**
	 * Get explanation for recommendations
	 *
	 * @param array $recommendations Recommendations
	 * @param array $user_data User data
	 * @return string Explanation
	 */
	private static function get_recommendation_explanation( $recommendations, $user_data ) {
		if ( empty( $recommendations ) ) {
			return 'Based on your current goals, you have a well-rounded health focus. Consider exploring additional areas as your health journey evolves.';
		}

		$explanations = array();

		foreach ( $recommendations as $rec ) {
			switch ( $rec['source'] ) {
				case 'age_based':
					$explanations[] = 'age-appropriate health priorities';
					break;
				case 'gender_based':
					$explanations[] = 'gender-specific health considerations';
					break;
				case 'assessment_based':
					$explanations[] = 'your assessment results';
					break;
			}
		}

		$unique_explanations = array_unique( $explanations );
		$explanation = 'These recommendations are based on ' . implode( ', ', $unique_explanations ) . '.';

		return $explanation;
	}

	/**
	 * Enhance goals options with additional data
	 *
	 * @param array $options Original options
	 * @param int   $user_id User ID
	 * @return array Enhanced options
	 */
	public static function enhance_goals_options( $options, $user_id ) {
		$categories = self::get_goal_categories();
		$enhanced_options = array();

		foreach ( $categories as $category_key => $category ) {
			foreach ( $category['goals'] as $goal_key => $goal_data ) {
				$enhanced_options[ $goal_key ] = array(
					'label'       => $goal_data['label'],
					'description' => $goal_data['description'],
					'icon'        => $goal_data['icon'],
					'category'    => $category['label'],
					'priority'    => $goal_data['priority'],
				);
			}
		}

		return $enhanced_options;
	}
}

ENNU_Enhanced_Health_Goals::init();
