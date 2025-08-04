<?php
/**
 * Next Steps Widget System
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
 * Next Steps Widget System
 * 
 * Generates personalized next steps guidance for users based on their
 * assessment data, profile completion status, and health goals.
 */
class ENNU_Next_Steps_Widget {

	/**
	 * Initialize the Next Steps Widget System
	 */
	public function __construct() {
		add_action( 'wp_ajax_get_next_steps', array( $this, 'ajax_get_next_steps' ) );
		add_action( 'wp_ajax_nopriv_get_next_steps', array( $this, 'ajax_get_next_steps' ) );
	}

	/**
	 * Get personalized next steps for a user
	 *
	 * @param int $user_id User ID
	 * @return array Next steps data
	 */
	public function get_next_steps( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return array();
		}

		$next_steps = array(
			'priority_actions' => $this->get_priority_actions( $user_id ),
			'assessment_recommendations' => $this->get_assessment_recommendations( $user_id ),
			'goal_suggestions' => $this->get_goal_suggestions( $user_id ),
			'progress_improvements' => $this->get_progress_improvements( $user_id ),
			'completion_status' => $this->get_completion_status( $user_id )
		);

		return $next_steps;
	}

	/**
	 * Get priority actions for the user
	 *
	 * @param int $user_id User ID
	 * @return array Priority actions
	 */
	private function get_priority_actions( $user_id ) {
		$actions = array();

		// Check for missing critical assessments
		$missing_assessments = $this->get_missing_critical_assessments( $user_id );
		if ( ! empty( $missing_assessments ) ) {
			$actions[] = array(
				'type' => 'assessment',
				'title' => 'Complete Critical Assessments',
				'description' => 'Take these assessments to unlock your complete health profile.',
				'items' => $missing_assessments,
				'priority' => 'high',
				'icon' => '📋'
			);
		}

		// Check for missing lab results
		$missing_labs = $this->get_missing_lab_results( $user_id );
		if ( ! empty( $missing_labs ) ) {
			$actions[] = array(
				'type' => 'lab_results',
				'title' => 'Import Lab Results',
				'description' => 'Import your lab results to get personalized biomarker insights.',
				'items' => $missing_labs,
				'priority' => 'high',
				'icon' => '🔬'
			);
		}

		// Check for low scores that need attention
		$low_scores = $this->get_low_scores_needing_attention( $user_id );
		if ( ! empty( $low_scores ) ) {
			$actions[] = array(
				'type' => 'improvement',
				'title' => 'Focus on Improvement Areas',
				'description' => 'These areas need attention based on your current scores.',
				'items' => $low_scores,
				'priority' => 'medium',
				'icon' => '🎯'
			);
		}

		// Check for consultation booking
		if ( ! $this->has_upcoming_consultation( $user_id ) ) {
			$actions[] = array(
				'type' => 'consultation',
				'title' => 'Book a Consultation',
				'description' => 'Schedule a consultation to discuss your results and create a personalized plan.',
				'priority' => 'medium',
				'icon' => '📅'
			);
		}

		return $actions;
	}

	/**
	 * Get assessment recommendations based on user data
	 *
	 * @param int $user_id User ID
	 * @return array Assessment recommendations
	 */
	private function get_assessment_recommendations( $user_id ) {
		$recommendations = array();

		// Get user's completed assessments
		$completed_assessments = $this->get_completed_assessments( $user_id );
		$all_assessments = $this->get_all_available_assessments();

		// Find missing assessments
		$missing_assessments = array_diff( $all_assessments, $completed_assessments );

		foreach ( $missing_assessments as $assessment ) {
			$recommendations[] = array(
				'assessment' => $assessment,
				'reason' => $this->get_assessment_recommendation_reason( $assessment, $user_id ),
				'priority' => $this->get_assessment_priority( $assessment ),
				'estimated_time' => $this->get_assessment_estimated_time( $assessment )
			);
		}

		// Sort by priority
		usort( $recommendations, function( $a, $b ) {
			$priority_order = array( 'high' => 3, 'medium' => 2, 'low' => 1 );
			return $priority_order[ $b['priority'] ] - $priority_order[ $a['priority'] ];
		} );

		return $recommendations;
	}

	/**
	 * Get goal suggestions based on user data
	 *
	 * @param int $user_id User ID
	 * @return array Goal suggestions
	 */
	private function get_goal_suggestions( $user_id ) {
		$suggestions = array();

		// Get user's current scores
		$pillar_scores = $this->get_pillar_scores( $user_id );

		// Suggest goals based on lowest scores
		$lowest_pillar = $this->get_lowest_scoring_pillar( $pillar_scores );
		if ( $lowest_pillar ) {
			$suggestions[] = array(
				'type' => 'pillar_improvement',
				'title' => "Improve Your {$lowest_pillar} Score",
				'description' => "Focus on improving your {$lowest_pillar} score from {$pillar_scores[$lowest_pillar]} to your target.",
				'current_score' => $pillar_scores[ $lowest_pillar ],
				'target_score' => $this->get_target_score( $lowest_pillar ),
				'actions' => $this->get_pillar_improvement_actions( $lowest_pillar )
			);
		}

		// Suggest specific biomarker goals
		$biomarker_goals = $this->get_biomarker_goals( $user_id );
		$suggestions = array_merge( $suggestions, $biomarker_goals );

		// Suggest lifestyle goals
		$lifestyle_goals = $this->get_lifestyle_goals( $user_id );
		$suggestions = array_merge( $suggestions, $lifestyle_goals );

		return $suggestions;
	}

	/**
	 * Get progress improvements for the user
	 *
	 * @param int $user_id User ID
	 * @return array Progress improvements
	 */
	private function get_progress_improvements( $user_id ) {
		$improvements = array();

		// Check for recent improvements
		$recent_improvements = $this->get_recent_improvements( $user_id );
		if ( ! empty( $recent_improvements ) ) {
			$improvements[] = array(
				'type' => 'recent_improvements',
				'title' => 'Recent Progress',
				'description' => 'You\'ve made progress in these areas:',
				'items' => $recent_improvements,
				'icon' => '📈'
			);
		}

		// Check for plateau areas
		$plateau_areas = $this->get_plateau_areas( $user_id );
		if ( ! empty( $plateau_areas ) ) {
			$improvements[] = array(
				'type' => 'plateau_areas',
				'title' => 'Areas for Breakthrough',
				'description' => 'These areas may need a different approach:',
				'items' => $plateau_areas,
				'icon' => '🚀'
			);
		}

		return $improvements;
	}

	/**
	 * Get completion status for the user
	 *
	 * @param int $user_id User ID
	 * @return array Completion status
	 */
	private function get_completion_status( $user_id ) {
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
			'status' => $this->get_completion_status_text( $completion_percentage )
		);
	}

	/**
	 * Get missing critical assessments
	 *
	 * @param int $user_id User ID
	 * @return array Missing critical assessments
	 */
	private function get_missing_critical_assessments( $user_id ) {
		// Use actual assessment definitions from the system
		if ( class_exists( 'ENNU_Scoring_System' ) ) {
			$all_definitions = ENNU_Scoring_System::get_all_definitions();
			$critical_assessments = array();
			
			// Map assessment keys to display names
			$assessment_names = array(
				'hair' => 'Hair Loss Assessment',
				'ed-treatment' => 'ED Treatment Assessment',
				'weight-loss' => 'Weight Loss Assessment',
				'health' => 'Health Assessment',
				'health-optimization' => 'Health Optimization Assessment',
				'skin' => 'Skin Health Assessment',
				'hormone' => 'Hormone Assessment',
				'testosterone' => 'Testosterone Assessment',
				'menopause' => 'Menopause Assessment',
				'sleep' => 'Sleep Assessment'
			);
			
			foreach ( $assessment_names as $key => $name ) {
				if ( isset( $all_definitions[ $key ] ) ) {
					$critical_assessments[ $key ] = $name;
				}
			}
		} else {
			// Fallback to basic assessments if scoring system not available
			$critical_assessments = array(
				'hair' => 'Hair Loss Assessment',
				'ed-treatment' => 'ED Treatment Assessment',
				'weight-loss' => 'Weight Loss Assessment',
				'health' => 'Health Assessment',
				'sleep' => 'Sleep Assessment'
			);
		}

		$completed_assessments = $this->get_completed_assessments( $user_id );
		$missing = array();

		foreach ( $critical_assessments as $key => $name ) {
			if ( ! in_array( $key, $completed_assessments ) ) {
				$missing[] = array(
					'key' => $key,
					'name' => $name,
					'url' => $this->get_assessment_url( $key )
				);
			}
		}

		return $missing;
	}

	/**
	 * Get missing lab results
	 *
	 * @param int $user_id User ID
	 * @return array Missing lab results
	 */
	private function get_missing_lab_results( $user_id ) {
		$critical_biomarkers = array(
			'glucose' => 'Blood Glucose',
			'hba1c' => 'HbA1c',
			'testosterone_total' => 'Total Testosterone',
			'cortisol' => 'Cortisol',
			'vitamin_d' => 'Vitamin D',
			'tsh' => 'TSH'
		);

		$user_biomarkers = $this->get_user_biomarkers( $user_id );
		$missing = array();

		foreach ( $critical_biomarkers as $key => $name ) {
			if ( ! isset( $user_biomarkers[ $key ] ) || empty( $user_biomarkers[ $key ] ) ) {
				$missing[] = array(
					'key' => $key,
					'name' => $name,
					'description' => "Import your {$name} lab results for personalized insights."
				);
			}
		}

		return $missing;
	}

	/**
	 * Get low scores needing attention
	 *
	 * @param int $user_id User ID
	 * @return array Low scores needing attention
	 */
	private function get_low_scores_needing_attention( $user_id ) {
		$pillar_scores = $this->get_pillar_scores( $user_id );
		$low_scores = array();

		foreach ( $pillar_scores as $pillar => $score ) {
			if ( $score < 6.0 ) {
				$low_scores[] = array(
					'pillar' => $pillar,
					'score' => $score,
					'recommendations' => $this->get_pillar_improvement_actions( $pillar )
				);
			}
		}

		return $low_scores;
	}

	/**
	 * Check if user has upcoming consultation
	 *
	 * @param int $user_id User ID
	 * @return bool Has upcoming consultation
	 */
	private function has_upcoming_consultation( $user_id ) {
		// This would integrate with your booking system
		// For now, return false to suggest booking
		return false;
	}

	/**
	 * Get completed assessments for user
	 *
	 * @param int $user_id User ID
	 * @return array Completed assessments
	 */
	private function get_completed_assessments( $user_id ) {
		$completed = array();
		
		// Check for completed assessments using actual meta keys
		$assessment_keys = array(
			'hair' => 'ennu_hair_completed',
			'ed-treatment' => 'ennu_ed_treatment_completed',
			'weight-loss' => 'ennu_weight_loss_completed',
			'health' => 'ennu_health_completed',
			'health-optimization' => 'ennu_health_optimization_completed',
			'skin' => 'ennu_skin_completed',
			'hormone' => 'ennu_hormone_completed',
			'testosterone' => 'ennu_testosterone_completed',
			'menopause' => 'ennu_menopause_completed',
			'sleep' => 'ennu_sleep_completed'
		);
		
		foreach ( $assessment_keys as $key => $meta_key ) {
			$completed_status = get_user_meta( $user_id, $meta_key, true );
			if ( ! empty( $completed_status ) ) {
				$completed[] = $key;
			}
		}
		
		return $completed;
	}

	/**
	 * Get all available assessments
	 *
	 * @return array All available assessments
	 */
	private function get_all_available_assessments() {
		// Use actual assessment definitions from the system
		if ( class_exists( 'ENNU_Scoring_System' ) ) {
			$all_definitions = ENNU_Scoring_System::get_all_definitions();
			return array_keys( $all_definitions );
		}
		
		// Fallback to basic assessments
		return array(
			'hair',
			'ed-treatment',
			'weight-loss',
			'health',
			'health-optimization',
			'skin',
			'hormone',
			'testosterone',
			'menopause',
			'sleep'
		);
	}

	/**
	 * Get assessment recommendation reason
	 *
	 * @param string $assessment Assessment key
	 * @param int $user_id User ID
	 * @return string Recommendation reason
	 */
	private function get_assessment_recommendation_reason( $assessment, $user_id ) {
		$reasons = array(
			'hair' => 'Hair health assessment helps identify underlying health issues and provides personalized treatment recommendations.',
			'ed-treatment' => 'ED treatment assessment evaluates your condition and provides personalized treatment options.',
			'weight-loss' => 'Weight loss assessment helps create a personalized plan for healthy weight management.',
			'health' => 'General health assessment provides a comprehensive overview of your overall wellness.',
			'health-optimization' => 'Health optimization assessment identifies areas for improvement and creates a personalized wellness plan.',
			'skin' => 'Skin health assessment helps identify skin conditions and provides personalized skincare recommendations.',
			'hormone' => 'Hormone assessment evaluates your hormonal balance and provides personalized treatment options.',
			'testosterone' => 'Testosterone assessment helps identify low T symptoms and provides personalized treatment recommendations.',
			'menopause' => 'Menopause assessment evaluates your symptoms and provides personalized hormone replacement therapy options.',
			'sleep' => 'Sleep assessment helps identify sleep issues and provides personalized recommendations for better sleep quality.'
		);

		return isset( $reasons[ $assessment ] ) ? $reasons[ $assessment ] : 'This assessment will provide valuable insights for your health journey.';
	}

	/**
	 * Get assessment priority
	 *
	 * @param string $assessment Assessment key
	 * @return string Priority level
	 */
	private function get_assessment_priority( $assessment ) {
		$high_priority = array( 'health', 'health-optimization', 'sleep', 'weight-loss' );
		$medium_priority = array( 'hair', 'ed-treatment', 'hormone', 'testosterone', 'menopause' );
		$low_priority = array( 'skin' );

		if ( in_array( $assessment, $high_priority ) ) {
			return 'high';
		} elseif ( in_array( $assessment, $medium_priority ) ) {
			return 'medium';
		} else {
			return 'low';
		}
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
	 * Get target score for pillar
	 *
	 * @param string $pillar Pillar name
	 * @return float Target score
	 */
	private function get_target_score( $pillar ) {
		// This would be personalized based on user goals
		// For now, return 8.0 as a general target
		return 8.0;
	}

	/**
	 * Get pillar improvement actions
	 *
	 * @param string $pillar Pillar name
	 * @return array Improvement actions
	 */
	private function get_pillar_improvement_actions( $pillar ) {
		$actions = array(
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

		return isset( $actions[ $pillar ] ) ? $actions[ $pillar ] : array();
	}

	/**
	 * Get biomarker goals
	 *
	 * @param int $user_id User ID
	 * @return array Biomarker goals
	 */
	private function get_biomarker_goals( $user_id ) {
		// Get actual biomarker data from user meta
		$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		$goals = array();
		
		if ( empty( $biomarker_data ) || ! is_array( $biomarker_data ) ) {
			// No biomarker data available
			return array();
		}
		
		// Analyze biomarker data for goals
		$critical_biomarkers = array(
			'vitamin_d' => array(
				'name' => 'Vitamin D',
				'optimal_range' => '30-50 ng/mL',
				'unit' => 'ng/mL',
				'actions' => array(
					'Increase sun exposure safely',
					'Consider vitamin D supplementation',
					'Retest in 3 months'
				)
			),
			'glucose' => array(
				'name' => 'Blood Glucose',
				'optimal_range' => '70-100 mg/dL',
				'unit' => 'mg/dL',
				'actions' => array(
					'Monitor carbohydrate intake',
					'Exercise regularly',
					'Retest in 3 months'
				)
			),
			'testosterone_total' => array(
				'name' => 'Total Testosterone',
				'optimal_range' => '300-1000 ng/dL',
				'unit' => 'ng/dL',
				'actions' => array(
					'Optimize sleep quality',
					'Exercise regularly',
					'Consider consultation'
				)
			)
		);
		
		foreach ( $critical_biomarkers as $biomarker => $info ) {
			if ( isset( $biomarker_data[ $biomarker ] ) && ! empty( $biomarker_data[ $biomarker ] ) ) {
				$current_value = $biomarker_data[ $biomarker ];
				$goals[] = array(
					'type' => 'biomarker_goal',
					'title' => "Optimize {$info['name']} Levels",
					'description' => "Your {$info['name']} levels are {$current_value} {$info['unit']}. Aim for {$info['optimal_range']}.",
					'current_value' => $current_value . ' ' . $info['unit'],
					'target_value' => $info['optimal_range'],
					'actions' => $info['actions']
				);
			}
		}
		
		return $goals;
	}

	/**
	 * Get lifestyle goals
	 *
	 * @param int $user_id User ID
	 * @return array Lifestyle goals
	 */
	private function get_lifestyle_goals( $user_id ) {
		// This would analyze user's lifestyle data and suggest goals
		// For now, return sample goals
		return array(
			array(
				'type' => 'lifestyle_goal',
				'title' => 'Improve Sleep Quality',
				'description' => 'Aim for 7-9 hours of quality sleep per night.',
				'current_value' => '6 hours',
				'target_value' => '7-9 hours',
				'actions' => array(
					'Establish consistent sleep schedule',
					'Create relaxing bedtime routine',
					'Optimize sleep environment'
				)
			)
		);
	}

	/**
	 * Get recent improvements
	 *
	 * @param int $user_id User ID
	 * @return array Recent improvements
	 */
	private function get_recent_improvements( $user_id ) {
		// This would analyze user's progress over time
		// For now, return empty array
		return array();
	}

	/**
	 * Get plateau areas
	 *
	 * @param int $user_id User ID
	 * @return array Plateau areas
	 */
	private function get_plateau_areas( $user_id ) {
		// This would identify areas where progress has stalled
		// For now, return empty array
		return array();
	}

	/**
	 * Get assessment URL
	 *
	 * @param string $assessment Assessment key
	 * @return string Assessment URL
	 */
	private function get_assessment_url( $assessment ) {
		// Get the actual assessment page URL from the system
		$settings = get_option( 'ennu_life_settings', array() );
		$page_mappings = $settings['page_mappings'] ?? array();
		
		// SIMPLE: Look for direct page_id mapping for this assessment type
		$page_id_key = $assessment . '_assessment_page_id';
		
		// Check if we have a direct page_id configured
		if ( isset( $page_mappings[ $page_id_key ] ) && ! empty( $page_mappings[ $page_id_key ] ) ) {
			$page_id = $page_mappings[ $page_id_key ];
			return get_permalink( $page_id );
		}
		
		// Fallback to default assessment URL structure
		return home_url( "/assessments/{$assessment}/" );
	}

	/**
	 * Get user biomarkers
	 *
	 * @param int $user_id User ID
	 * @return array User biomarkers
	 */
	private function get_user_biomarkers( $user_id ) {
		// Get actual biomarker data from user meta
		$biomarkers = array();
		
		// Common biomarker meta keys
		$biomarker_keys = array(
			'glucose' => 'ennu_biomarker_glucose',
			'hba1c' => 'ennu_biomarker_hba1c',
			'testosterone_total' => 'ennu_biomarker_testosterone_total',
			'cortisol' => 'ennu_biomarker_cortisol',
			'vitamin_d' => 'ennu_biomarker_vitamin_d',
			'tsh' => 'ennu_biomarker_tsh',
			'cholesterol_total' => 'ennu_biomarker_cholesterol_total',
			'cholesterol_hdl' => 'ennu_biomarker_cholesterol_hdl',
			'cholesterol_ldl' => 'ennu_biomarker_cholesterol_ldl',
			'triglycerides' => 'ennu_biomarker_triglycerides',
			'hemoglobin' => 'ennu_biomarker_hemoglobin',
			'platelets' => 'ennu_biomarker_platelets',
			'wbc' => 'ennu_biomarker_wbc',
			'rbc' => 'ennu_biomarker_rbc',
			'creatinine' => 'ennu_biomarker_creatinine',
			'gfr' => 'ennu_biomarker_gfr',
			'albumin' => 'ennu_biomarker_albumin',
			'bilirubin_total' => 'ennu_biomarker_bilirubin_total',
			'ast' => 'ennu_biomarker_ast',
			'alt' => 'ennu_biomarker_alt',
			'alkaline_phosphatase' => 'ennu_biomarker_alkaline_phosphatase'
		);
		
		foreach ( $biomarker_keys as $key => $meta_key ) {
			$value = get_user_meta( $user_id, $meta_key, true );
			if ( ! empty( $value ) ) {
				$biomarkers[ $key ] = $value;
			}
		}
		
		return $biomarkers;
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
	 * AJAX handler for getting next steps
	 */
	public function ajax_get_next_steps() {
		check_ajax_referer( 'ennu_next_steps_nonce', 'nonce' );

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_die( 'User not logged in' );
		}

		$next_steps = $this->get_next_steps( $user_id );
		wp_send_json_success( $next_steps );
	}

	/**
	 * Render next steps widget
	 *
	 * @param int $user_id User ID
	 * @return string Rendered widget HTML
	 */
	public function render_next_steps_widget( $user_id = null ) {
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			return '';
		}

		$next_steps = $this->get_next_steps( $user_id );

		ob_start();
		?>
		<div class="ennu-next-steps-widget" data-user-id="<?php echo esc_attr( $user_id ); ?>">
			<div class="ennu-next-steps-header">
				<h3>🎯 Your Next Steps</h3>
				<p>Personalized guidance to optimize your health journey</p>
			</div>

			<?php if ( ! empty( $next_steps['priority_actions'] ) ) : ?>
				<div class="ennu-priority-actions">
					<h4>Priority Actions</h4>
					<?php foreach ( $next_steps['priority_actions'] as $action ) : ?>
						<div class="ennu-action-item ennu-priority-<?php echo esc_attr( $action['priority'] ); ?>">
							<div class="ennu-action-icon"><?php echo esc_html( $action['icon'] ); ?></div>
							<div class="ennu-action-content">
								<h5><?php echo esc_html( $action['title'] ); ?></h5>
								<p><?php echo esc_html( $action['description'] ); ?></p>
								<?php if ( ! empty( $action['items'] ) ) : ?>
									<ul class="ennu-action-items">
										<?php foreach ( $action['items'] as $item ) : ?>
											<li>
												<?php if ( isset( $item['url'] ) ) : ?>
													<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['name'] ?? 'Action' ); ?></a>
												<?php else : ?>
													<?php echo esc_html( $item['name'] ?? 'Action' ); ?>
												<?php endif; ?>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $next_steps['assessment_recommendations'] ) ) : ?>
				<div class="ennu-assessment-recommendations">
					<h4>Recommended Assessments</h4>
					<?php foreach ( $next_steps['assessment_recommendations'] as $recommendation ) : ?>
						<div class="ennu-recommendation-item ennu-priority-<?php echo esc_attr( $recommendation['priority'] ); ?>">
							<div class="ennu-recommendation-content">
								<h5><?php echo esc_html( ucfirst( $recommendation['assessment'] ) ); ?> Assessment</h5>
								<p><?php echo esc_html( $recommendation['reason'] ); ?></p>
								<div class="ennu-recommendation-meta">
									<span class="ennu-estimated-time">⏱️ <?php echo esc_html( $recommendation['estimated_time'] ); ?></span>
									<span class="ennu-priority-badge ennu-priority-<?php echo esc_attr( $recommendation['priority'] ); ?>">
										<?php echo esc_html( ucfirst( $recommendation['priority'] ) ); ?> Priority
									</span>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $next_steps['goal_suggestions'] ) ) : ?>
				<div class="ennu-goal-suggestions">
					<h4>Suggested Goals</h4>
					<?php foreach ( $next_steps['goal_suggestions'] as $goal ) : ?>
						<div class="ennu-goal-item">
							<div class="ennu-goal-content">
								<h5><?php echo esc_html( $goal['title'] ); ?></h5>
								<p><?php echo esc_html( $goal['description'] ); ?></p>
								<?php if ( isset( $goal['current_score'] ) && isset( $goal['target_score'] ) ) : ?>
									<div class="ennu-score-progress">
										<span class="ennu-current-score"><?php echo esc_html( $goal['current_score'] ); ?></span>
										<span class="ennu-progress-arrow">→</span>
										<span class="ennu-target-score"><?php echo esc_html( $goal['target_score'] ); ?></span>
									</div>
								<?php endif; ?>
								<?php if ( ! empty( $goal['actions'] ) ) : ?>
									<ul class="ennu-goal-actions">
										<?php foreach ( $goal['actions'] as $action ) : ?>
											<li><?php echo esc_html( $action ); ?></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $next_steps['completion_status'] ) ) : ?>
				<div class="ennu-completion-status">
					<h4>Profile Completion</h4>
					<div class="ennu-completion-progress">
						<div class="ennu-progress-bar">
							<div class="ennu-progress-fill" style="width: <?php echo esc_attr( $next_steps['completion_status']['percentage'] ); ?>%;"></div>
						</div>
						<div class="ennu-progress-text">
							<?php echo esc_html( $next_steps['completion_status']['completed_count'] ); ?> of <?php echo esc_html( $next_steps['completion_status']['total_count'] ); ?> assessments completed
							(<?php echo esc_html( $next_steps['completion_status']['percentage'] ); ?>%)
						</div>
					</div>
					<p class="ennu-completion-status-text"><?php echo esc_html( $next_steps['completion_status']['status'] ); ?></p>
				</div>
			<?php endif; ?>
		</div>

		<style>
		.ennu-next-steps-widget {
			background: #ffffff;
			border-radius: 12px;
			padding: 24px;
			box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
			margin-bottom: 24px;
		}

		.ennu-next-steps-header {
			margin-bottom: 24px;
			text-align: center;
		}

		.ennu-next-steps-header h3 {
			margin: 0 0 8px 0;
			color: #2c3e50;
			font-size: 1.5rem;
		}

		.ennu-next-steps-header p {
			margin: 0;
			color: #7f8c8d;
			font-size: 0.9rem;
		}

		.ennu-priority-actions,
		.ennu-assessment-recommendations,
		.ennu-goal-suggestions,
		.ennu-completion-status {
			margin-bottom: 24px;
		}

		.ennu-priority-actions h4,
		.ennu-assessment-recommendations h4,
		.ennu-goal-suggestions h4,
		.ennu-completion-status h4 {
			margin: 0 0 16px 0;
			color: #2c3e50;
			font-size: 1.2rem;
		}

		.ennu-action-item,
		.ennu-recommendation-item,
		.ennu-goal-item {
			display: flex;
			align-items: flex-start;
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

		.ennu-priority-low {
			background: #f0fff4;
			border-left-color: #38a169;
		}

		.ennu-action-icon {
			font-size: 1.5rem;
			margin-right: 12px;
			margin-top: 2px;
		}

		.ennu-action-content,
		.ennu-recommendation-content,
		.ennu-goal-content {
			flex: 1;
		}

		.ennu-action-content h5,
		.ennu-recommendation-content h5,
		.ennu-goal-content h5 {
			margin: 0 0 8px 0;
			color: #2c3e50;
			font-size: 1rem;
		}

		.ennu-action-content p,
		.ennu-recommendation-content p,
		.ennu-goal-content p {
			margin: 0 0 8px 0;
			color: #7f8c8d;
			font-size: 0.9rem;
			line-height: 1.4;
		}

		.ennu-action-items {
			margin: 8px 0 0 0;
			padding-left: 20px;
		}

		.ennu-action-items li {
			margin-bottom: 4px;
			color: #2c3e50;
			font-size: 0.9rem;
		}

		.ennu-action-items a {
			color: #3498db;
			text-decoration: none;
		}

		.ennu-action-items a:hover {
			text-decoration: underline;
		}

		.ennu-recommendation-meta {
			display: flex;
			align-items: center;
			gap: 12px;
			margin-top: 8px;
		}

		.ennu-estimated-time {
			font-size: 0.8rem;
			color: #7f8c8d;
		}

		.ennu-priority-badge {
			padding: 2px 8px;
			border-radius: 12px;
			font-size: 0.7rem;
			font-weight: 600;
			text-transform: uppercase;
		}

		.ennu-priority-high .ennu-priority-badge {
			background: #fed7d7;
			color: #c53030;
		}

		.ennu-priority-medium .ennu-priority-badge {
			background: #feebc8;
			color: #c05621;
		}

		.ennu-priority-low .ennu-priority-badge {
			background: #c6f6d5;
			color: #2f855a;
		}

		.ennu-score-progress {
			display: flex;
			align-items: center;
			gap: 8px;
			margin: 8px 0;
		}

		.ennu-current-score {
			font-weight: 600;
			color: #e53e3e;
		}

		.ennu-progress-arrow {
			color: #7f8c8d;
		}

		.ennu-target-score {
			font-weight: 600;
			color: #38a169;
		}

		.ennu-goal-actions {
			margin: 8px 0 0 0;
			padding-left: 20px;
		}

		.ennu-goal-actions li {
			margin-bottom: 4px;
			color: #2c3e50;
			font-size: 0.9rem;
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
		</style>
		<?php
		return ob_get_clean();
	}
}

// Initialize the Next Steps Widget System
new ENNU_Next_Steps_Widget(); 