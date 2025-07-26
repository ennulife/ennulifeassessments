<?php
/**
 * ENNU Profile Completeness Tracker
 * Tracks user profile completeness and data accuracy levels
 *
 * @package ENNU_Life
 * @version 64.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Profile_Completeness_Tracker {

	/**
	 * Calculate profile completeness for a user
	 *
	 * @param int $user_id User ID
	 * @return array Completeness data
	 */
	public static function calculate_completeness( $user_id ) {
		$completeness_data = array(
			'overall_percentage'  => 0,
			'data_accuracy_level' => 'low',
			'completed_sections'  => array(),
			'missing_sections'    => array(),
			'recommendations'     => array(),
			'section_details'     => array(),
			'last_updated'        => current_time( 'mysql' ),
		);

		try {
			$sections         = self::get_completeness_sections();
			$total_weight     = 0;
			$completed_weight = 0;

			foreach ( $sections as $section_key => $section_config ) {
				$section_completeness = self::calculate_section_completeness( $user_id, $section_key, $section_config );
				$weight               = $section_config['weight'];

				$total_weight     += $weight;
				$completed_weight += ( $section_completeness['percentage'] / 100 ) * $weight;

				$completeness_data['section_details'][ $section_key ] = $section_completeness;

				if ( $section_completeness['percentage'] >= 80 ) {
					$completeness_data['completed_sections'][] = $section_key;
				} else {
					$completeness_data['missing_sections'][] = array(
						'section'        => $section_key,
						'percentage'     => $section_completeness['percentage'],
						'missing_fields' => $section_completeness['missing_fields'],
						'weight'         => $weight,
					);
				}
			}

			$completeness_data['overall_percentage'] = $total_weight > 0 ? intval( round( ( $completed_weight / $total_weight ) * 100 ) ) : 0;

			$completeness_data['data_accuracy_level'] = self::determine_accuracy_level( $completeness_data['overall_percentage'] );

			$completeness_data['recommendations'] = self::generate_recommendations( $completeness_data['missing_sections'] );

		} catch ( Exception $e ) {
			// Log error and return safe defaults
			error_log( 'ENNU Profile Completeness Tracker Error: ' . $e->getMessage() );
			
			// Ensure section_details is always an array
			$completeness_data['section_details'] = array();
			$completeness_data['recommendations'] = array();
		}

		update_user_meta( $user_id, 'ennu_profile_completeness', $completeness_data );

		return $completeness_data;
	}

	/**
	 * Get completeness sections configuration
	 *
	 * @return array Sections configuration
	 */
	private static function get_completeness_sections() {
		return array(
			'basic_demographics'    => array(
				'weight' => 25,
				'fields' => array(
					'ennu_age'    => 'required',
					'ennu_gender' => 'required',
					'ennu_height' => 'required',
					'ennu_weight' => 'required',
					'ennu_bmi'    => 'calculated',
				),
			),
			'health_goals'          => array(
				'weight' => 20,
				'fields' => array(
					'ennu_global_health_goals' => 'required',
				),
			),
			'assessments_completed' => array(
				'weight' => 30,
				'fields' => array(
					'ennu_welcome_calculated_score'        => 'assessment',
					'ennu_health_calculated_score'         => 'assessment',
					'ennu_hormone_calculated_score'        => 'assessment',
					'ennu_sleep_calculated_score'          => 'assessment',
					'ennu_weight_loss_calculated_score'    => 'assessment',
					'ennu_health_optimization_calculated_score' => 'assessment',
				),
			),
			'symptoms_data'         => array(
				'weight' => 15,
				'fields' => array(
					'ennu_user_symptoms' => 'optional',
					'ennu_symptom_history' => 'optional',
				),
			),
			'biomarkers_data'       => array(
				'weight' => 10,
				'fields' => array(
					'ennu_user_biomarkers' => 'optional',
					'ennu_biomarker_targets' => 'optional',
				),
			),
		);
	}

	/**
	 * Calculate completeness for a specific section
	 *
	 * @param int $user_id User ID
	 * @param string $section_key Section key
	 * @param array $section_config Section configuration
	 * @return array Section completeness data
	 */
	private static function calculate_section_completeness( $user_id, $section_key, $section_config ) {
		$fields           = $section_config['fields'];
		$total_fields     = count( $fields );
		$completed_fields = 0;
		$missing_fields   = array();

		foreach ( $fields as $field_key => $field_type ) {
			$is_completed = false;

			if ( 'assessment' === $field_type ) {
				$is_completed = self::check_assessment_completion( $user_id, $field_key );
			} elseif ( 'calculated' === $field_type ) {
				$is_completed = self::check_calculated_field( $user_id, $field_key );
			} else {
				$field_value  = get_user_meta( $user_id, $field_key, true );
				$is_completed = self::validate_field_value( $field_value, $field_type );
			}

			if ( $is_completed ) {
				$completed_fields++;
			} else {
				$missing_fields[] = $field_key;
			}
		}

		$percentage = $total_fields > 0 ? intval( round( ( $completed_fields / $total_fields ) * 100 ) ) : 0;

		return array(
			'percentage'       => $percentage,
			'completed_fields' => $completed_fields,
			'total_fields'     => $total_fields,
			'missing_fields'   => $missing_fields,
		);
	}

	/**
	 * Check if user has completed an assessment
	 *
	 * @param int $user_id User ID
	 * @param string $assessment_key Assessment key
	 * @return bool Whether assessment is completed
	 */
	private static function check_assessment_completion( $user_id, $assessment_key ) {
		$score = get_user_meta( $user_id, $assessment_key, true );
		return ! empty( $score ) && is_numeric( $score );
	}

	/**
	 * Check if calculated field exists
	 *
	 * @param int $user_id User ID
	 * @param string $field_key Field key
	 * @return bool Whether field exists
	 */
	private static function check_calculated_field( $user_id, $field_key ) {
		$value = get_user_meta( $user_id, $field_key, true );
		return ! empty( $value );
	}

	/**
	 * Validate field value based on type
	 *
	 * @param mixed $value Field value
	 * @param string $type Field type
	 * @return bool Whether field is valid
	 */
	private static function validate_field_value( $value, $type ) {
		if ( 'required' === $type ) {
			return ! empty( $value );
		} elseif ( 'optional' === $type ) {
			// Optional fields get partial credit if they exist
			return ! empty( $value );
		}
		return false;
	}

	/**
	 * Determine data accuracy level based on completeness percentage
	 *
	 * @param int $percentage Completeness percentage
	 * @return string Accuracy level
	 */
	private static function determine_accuracy_level( $percentage ) {
		if ( $percentage >= 90 ) {
			return 'excellent';
		} elseif ( $percentage >= 80 ) {
			return 'high';
		} elseif ( $percentage >= 60 ) {
			return 'medium';
		} elseif ( $percentage >= 40 ) {
			return 'moderate';
		} else {
			return 'low';
		}
	}

	/**
	 * Generate recommendations based on missing sections
	 *
	 * @param array $missing_sections Missing sections data
	 * @return array Recommendations
	 */
	private static function generate_recommendations( $missing_sections ) {
		$recommendations = array();

		foreach ( $missing_sections as $missing_section ) {
			$section_key = $missing_section['section'];
			$percentage  = $missing_section['percentage'];
			$weight      = $missing_section['weight'];

			switch ( $section_key ) {
				case 'basic_demographics':
					if ( $percentage < 60 ) {
						$recommendations[] = array(
							'priority'       => 'critical',
							'title'          => 'Complete Your Basic Information',
							'description'    => 'Add your age, gender, height, and weight for accurate health assessments and personalized recommendations.',
							'action_url'     => '#profile-setup',
							'estimated_time' => '2 minutes',
							'icon'           => '👤',
							'weight'         => $weight,
						);
					}
					break;

				case 'health_goals':
					$recommendations[] = array(
						'priority'       => 'high',
						'title'          => 'Set Your Health Goals',
						'description'    => 'Define your health objectives to get personalized recommendations and track your progress.',
						'action_url'     => '#health-goals',
						'estimated_time' => '3 minutes',
						'icon'           => '🎯',
						'weight'         => $weight,
					);
					break;

				case 'assessments_completed':
					if ( $percentage < 50 ) {
						$recommendations[] = array(
							'priority'       => 'critical',
							'title'          => 'Complete Health Assessments',
							'description'    => 'Take our comprehensive health assessments to unlock your personalized health insights and scoring.',
							'action_url'     => '#assessments',
							'estimated_time' => '10-15 minutes',
							'icon'           => '📊',
							'weight'         => $weight,
						);
					} elseif ( $percentage < 80 ) {
						$recommendations[] = array(
							'priority'       => 'medium',
							'title'          => 'Complete More Assessments',
							'description'    => 'Complete additional assessments for a more comprehensive health profile.',
							'action_url'     => '#assessments',
							'estimated_time' => '5-10 minutes',
							'icon'           => '📈',
							'weight'         => $weight,
						);
					}
					break;

				case 'biomarkers_data':
					$recommendations[] = array(
						'priority'       => 'medium',
						'title'          => 'Upload Lab Results',
						'description'    => 'Add your biomarker data for evidence-based health insights and personalized recommendations.',
						'action_url'     => '#biomarkers',
						'estimated_time' => '5 minutes',
						'icon'           => '🔬',
						'weight'         => $weight,
					);
					break;

				case 'symptoms_data':
					$recommendations[] = array(
						'priority'       => 'medium',
						'title'          => 'Track Your Symptoms',
						'description'    => 'Log your symptoms to identify patterns and correlations with your health data.',
						'action_url'     => '#symptoms',
						'estimated_time' => '3 minutes',
						'icon'           => '📝',
						'weight'         => $weight,
					);
					break;
			}
		}

		// Sort by priority and weight
		usort(
			$recommendations,
			function( $a, $b ) {
				$priority_order = array(
					'critical' => 1,
					'high'     => 2,
					'medium'   => 3,
					'low'      => 4,
				);
				
				$priority_diff = $priority_order[ $a['priority'] ] - $priority_order[ $b['priority'] ];
				if ( $priority_diff !== 0 ) {
					return $priority_diff;
				}
				
				// If same priority, sort by weight (higher weight first)
				return $b['weight'] - $a['weight'];
			}
		);

		return $recommendations;
	}

	/**
	 * Get profile completeness for display
	 *
	 * @param int $user_id User ID
	 * @return array Completeness data for display
	 */
	public static function get_completeness_for_display( $user_id ) {
		$completeness_data = get_user_meta( $user_id, 'ennu_profile_completeness', true );

		if ( empty( $completeness_data ) || !is_array( $completeness_data ) ) {
			$completeness_data = self::calculate_completeness( $user_id );
		}

		// Ensure all required keys exist with safe defaults
		$default_data = array(
			'overall_percentage'  => 0,
			'data_accuracy_level' => 'low',
			'completed_sections'  => array(),
			'missing_sections'    => array(),
			'recommendations'     => array(),
			'section_details'     => array(),
			'last_updated'        => current_time( 'mysql' ),
		);

		$completeness_data = wp_parse_args( $completeness_data, $default_data );

		// Ensure arrays are actually arrays
		if ( !is_array( $completeness_data['completed_sections'] ) ) {
			$completeness_data['completed_sections'] = array();
		}
		if ( !is_array( $completeness_data['missing_sections'] ) ) {
			$completeness_data['missing_sections'] = array();
		}
		if ( !is_array( $completeness_data['recommendations'] ) ) {
			$completeness_data['recommendations'] = array();
		}
		if ( !is_array( $completeness_data['section_details'] ) ) {
			$completeness_data['section_details'] = array();
		}

		return $completeness_data;
	}

	/**
	 * Get next recommended action for user
	 *
	 * @param int $user_id User ID
	 * @return array|null Next recommended action
	 */
	public static function get_next_recommended_action( $user_id ) {
		$completeness_data = self::get_completeness_for_display( $user_id );

		if ( ! empty( $completeness_data['recommendations'] ) ) {
			return $completeness_data['recommendations'][0]; // Return highest priority recommendation
		}

		return null;
	}

	/**
	 * Get section display name
	 *
	 * @param string $section_key Section key
	 * @return string Display name
	 */
	public static function get_section_display_name( $section_key ) {
		$section_names = array(
			'basic_demographics'    => 'Basic Information',
			'health_goals'          => 'Health Goals',
			'assessments_completed' => 'Health Assessments',
			'symptoms_data'         => 'Symptom Tracking',
			'biomarkers_data'       => 'Lab Results',
		);

		return isset( $section_names[ $section_key ] ) ? $section_names[ $section_key ] : ucwords( str_replace( '_', ' ', $section_key ) );
	}

	/**
	 * Get accuracy level display name
	 *
	 * @param string $accuracy_level Accuracy level
	 * @return string Display name
	 */
	public static function get_accuracy_level_display_name( $accuracy_level ) {
		$accuracy_names = array(
			'excellent' => 'Excellent',
			'high'      => 'High',
			'medium'    => 'Medium',
			'moderate'  => 'Moderate',
			'low'       => 'Low',
		);

		return isset( $accuracy_names[ $accuracy_level ] ) ? $accuracy_names[ $accuracy_level ] : ucfirst( $accuracy_level );
	}

	/**
	 * Force recalculate completeness for a user
	 *
	 * @param int $user_id User ID
	 * @return array Updated completeness data
	 */
	public static function recalculate_completeness( $user_id ) {
		delete_user_meta( $user_id, 'ennu_profile_completeness' );
		return self::calculate_completeness( $user_id );
	}

	/**
	 * Get completeness summary for quick display
	 *
	 * @param int $user_id User ID
	 * @return array Summary data
	 */
	public static function get_completeness_summary( $user_id ) {
		$completeness_data = self::get_completeness_for_display( $user_id );

		return array(
			'percentage'      => $completeness_data['overall_percentage'],
			'accuracy_level'  => $completeness_data['data_accuracy_level'],
			'completed_count' => count( $completeness_data['completed_sections'] ),
			'missing_count'   => count( $completeness_data['missing_sections'] ),
			'next_action'     => self::get_next_recommended_action( $user_id ),
		);
	}
}
