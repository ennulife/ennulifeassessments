<?php
/**
 * ENNU Profile Completeness Tracker
 * Tracks user profile completeness and data accuracy levels
 *
 * @package ENNU_Life
 * @version 62.2.8
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
			'last_updated'        => current_time( 'mysql' ),
		);

		$sections         = self::get_completeness_sections();
		$total_weight     = 0;
		$completed_weight = 0;

		foreach ( $sections as $section_key => $section_config ) {
			$section_completeness = self::calculate_section_completeness( $user_id, $section_key, $section_config );
			$weight               = $section_config['weight'];

			$total_weight     += $weight;
			$completed_weight += ( $section_completeness['percentage'] / 100 ) * $weight;

			if ( $section_completeness['percentage'] >= 80 ) {
				$completeness_data['completed_sections'][] = $section_key;
			} else {
				$completeness_data['missing_sections'][] = array(
					'section'        => $section_key,
					'percentage'     => $section_completeness['percentage'],
					'missing_fields' => $section_completeness['missing_fields'],
				);
			}
		}

		$completeness_data['overall_percentage'] = $total_weight > 0 ? intval( round( ( $completed_weight / $total_weight ) * 100 ) ) : 0;

		$completeness_data['data_accuracy_level'] = self::determine_accuracy_level( $completeness_data['overall_percentage'] );

		$completeness_data['recommendations'] = self::generate_recommendations( $completeness_data['missing_sections'] );

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
				'weight' => 20,
				'fields' => array(
					'ennu_age'    => 'required',
					'ennu_gender' => 'required',
					'ennu_height' => 'required',
					'ennu_weight' => 'required',
				),
			),
			'health_goals'          => array(
				'weight' => 15,
				'fields' => array(
					'ennu_global_health_goals' => 'required',
				),
			),
			'assessments_completed' => array(
				'weight' => 35,
				'fields' => array(
					'welcome_assessment'        => 'assessment',
					'primary_health_assessment' => 'assessment',
				),
			),
			'symptoms_data'         => array(
				'weight' => 15,
				'fields' => array(
					'ennu_user_symptoms' => 'optional',
				),
			),
			'biomarkers_data'       => array(
				'weight' => 10,
				'fields' => array(
					'ennu_user_biomarkers' => 'optional',
				),
			),
			'lifestyle_data'        => array(
				'weight' => 5,
				'fields' => array(
					'ennu_activity_level' => 'optional',
					'ennu_sleep_hours'    => 'optional',
					'ennu_stress_level'   => 'optional',
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
			} else {
				$field_value  = get_user_meta( $user_id, $field_key, true );
				$is_completed = ! empty( $field_value );
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
		$assessment_mapping = array(
			'welcome_assessment'        => 'ennu_welcome_scores',
			'primary_health_assessment' => array(
				'ennu_testosterone_scores',
				'ennu_menopause_scores',
				'ennu_ed_treatment_scores',
				'ennu_weight_loss_scores',
				'ennu_longevity_scores',
			),
		);

		if ( ! isset( $assessment_mapping[ $assessment_key ] ) ) {
			return false;
		}

		$meta_keys = $assessment_mapping[ $assessment_key ];

		if ( is_string( $meta_keys ) ) {
			$scores = get_user_meta( $user_id, $meta_keys, true );
			return ! empty( $scores );
		}

		if ( is_array( $meta_keys ) ) {
			foreach ( $meta_keys as $meta_key ) {
				$scores = get_user_meta( $user_id, $meta_key, true );
				if ( ! empty( $scores ) ) {
					return true;
				}
			}
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
		if ( $percentage >= 80 ) {
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

			switch ( $section_key ) {
				case 'basic_demographics':
					if ( $percentage < 50 ) {
						$recommendations[] = array(
							'priority'       => 'high',
							'title'          => 'Complete Basic Information',
							'description'    => 'Add your age, gender, height, and weight for accurate health assessments.',
							'action_url'     => '/profile-setup/',
							'estimated_time' => '2 minutes',
						);
					}
					break;

				case 'health_goals':
					$recommendations[] = array(
						'priority'       => 'high',
						'title'          => 'Set Your Health Goals',
						'description'    => 'Define your health objectives to get personalized recommendations.',
						'action_url'     => '/health-goals/',
						'estimated_time' => '3 minutes',
					);
					break;

				case 'assessments_completed':
					if ( $percentage < 30 ) {
						$recommendations[] = array(
							'priority'       => 'critical',
							'title'          => 'Complete Your First Assessment',
							'description'    => 'Take a health assessment to unlock your personalized health insights.',
							'action_url'     => '/assessments/',
							'estimated_time' => '5-10 minutes',
						);
					}
					break;

				case 'biomarkers_data':
					$recommendations[] = array(
						'priority'       => 'medium',
						'title'          => 'Upload Lab Results',
						'description'    => 'Add your biomarker data for evidence-based health insights.',
						'action_url'     => '/lab-data-upload/',
						'estimated_time' => '5 minutes',
					);
					break;

				case 'symptoms_data':
					$recommendations[] = array(
						'priority'       => 'medium',
						'title'          => 'Track Your Symptoms',
						'description'    => 'Log your symptoms to identify patterns and correlations.',
						'action_url'     => '/symptom-tracker/',
						'estimated_time' => '3 minutes',
					);
					break;
			}
		}

		usort(
			$recommendations,
			function( $a, $b ) {
				$priority_order = array(
					'critical' => 1,
					'high'     => 2,
					'medium'   => 3,
					'low'      => 4,
				);
				return $priority_order[ $a['priority'] ] - $priority_order[ $b['priority'] ];
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

		if ( empty( $completeness_data ) ) {
			$completeness_data = self::calculate_completeness( $user_id );
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
}
