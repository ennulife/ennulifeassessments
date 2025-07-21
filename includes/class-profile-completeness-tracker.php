<?php
/**
 * ENNU Profile Completeness Tracker
 *
 * Tracks and calculates user profile completeness for data accuracy levels.
 *
 * @package ENNU_Life
 * @version 62.2.8
 * @author ENNU Life Development Team
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
			'overall_percentage' => 0,
			'category_scores'    => array(),
			'missing_items'      => array(),
			'recommendations'    => array(),
			'accuracy_level'     => 'low',
		);

		$categories = array(
			'basic_info'   => self::check_basic_info( $user_id ),
			'health_goals' => self::check_health_goals( $user_id ),
			'assessments'  => self::check_assessments( $user_id ),
			'biomarkers'   => self::check_biomarkers( $user_id ),
			'symptoms'     => self::check_symptoms( $user_id ),
		);

		$total_score = 0;
		foreach ( $categories as $category => $data ) {
			$completeness_data['category_scores'][ $category ] = $data['percentage'];
			$completeness_data['missing_items'][ $category ]   = $data['missing'];
			$total_score                                      += $data['percentage'];
		}

		$completeness_data['overall_percentage'] = $total_score / count( $categories );
		$completeness_data['accuracy_level']     = self::determine_accuracy_level( $completeness_data['overall_percentage'] );
		$completeness_data['recommendations']    = self::generate_recommendations( $completeness_data );

		update_user_meta( $user_id, 'ennu_profile_completeness', $completeness_data );

		return $completeness_data;
	}

	/**
	 * Check basic information completeness
	 *
	 * @param int $user_id User ID
	 * @return array Basic info data
	 */
	private static function check_basic_info( $user_id ) {
		$required_fields = array(
			'ennu_global_gender'         => 'Gender',
			'ennu_global_date_of_birth'  => 'Date of Birth',
			'ennu_global_height'         => 'Height',
			'ennu_global_weight'         => 'Weight',
			'ennu_global_activity_level' => 'Activity Level',
		);

		$completed = 0;
		$missing   = array();

		foreach ( $required_fields as $field => $label ) {
			$value = get_user_meta( $user_id, $field, true );
			if ( ! empty( $value ) ) {
				$completed++;
			} else {
				$missing[] = $label;
			}
		}

		return array(
			'percentage' => ( $completed / count( $required_fields ) ) * 100,
			'completed'  => $completed,
			'total'      => count( $required_fields ),
			'missing'    => $missing,
		);
	}

	/**
	 * Check health goals completeness
	 *
	 * @param int $user_id User ID
	 * @return array Health goals data
	 */
	private static function check_health_goals( $user_id ) {
		$health_goals = get_user_meta( $user_id, 'ennu_global_health_goals', true );
		$missing      = array();

		if ( empty( $health_goals ) || ! is_array( $health_goals ) ) {
			$missing[]  = 'Health Goals Selection';
			$percentage = 0;
		} else {
			$percentage = count( $health_goals ) >= 3 ? 100 : ( count( $health_goals ) / 3 ) * 100;
			if ( count( $health_goals ) < 3 ) {
				$missing[] = 'Additional Health Goals (recommended: 3+)';
			}
		}

		return array(
			'percentage' => $percentage,
			'completed'  => is_array( $health_goals ) ? count( $health_goals ) : 0,
			'total'      => 3,
			'missing'    => $missing,
		);
	}

	/**
	 * Check assessments completeness
	 *
	 * @param int $user_id User ID
	 * @return array Assessments data
	 */
	private static function check_assessments( $user_id ) {
		$assessment_types = array(
			'welcome_assessment'             => 'Welcome Assessment',
			'health_optimization_assessment' => 'Health Optimization Assessment',
			'testosterone_assessment'        => 'Testosterone Assessment',
			'hormone_assessment'             => 'Hormone Assessment',
			'sleep_assessment'               => 'Sleep Assessment',
			'weight_loss_assessment'         => 'Weight Loss Assessment',
		);

		$completed = 0;
		$missing   = array();

		foreach ( $assessment_types as $type => $label ) {
			$scores = get_user_meta( $user_id, 'ennu_' . $type . '_scores', true );
			if ( ! empty( $scores ) ) {
				$completed++;
			} else {
				$missing[] = $label;
			}
		}

		return array(
			'percentage' => ( $completed / count( $assessment_types ) ) * 100,
			'completed'  => $completed,
			'total'      => count( $assessment_types ),
			'missing'    => $missing,
		);
	}

	/**
	 * Check biomarkers completeness
	 *
	 * @param int $user_id User ID
	 * @return array Biomarkers data
	 */
	private static function check_biomarkers( $user_id ) {
		$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		$missing        = array();

		if ( empty( $biomarker_data ) || ! is_array( $biomarker_data ) ) {
			$missing[]  = 'Lab Results Upload';
			$percentage = 0;
		} else {
			$essential_biomarkers = array(
				'Total Cholesterol',
				'HDL',
				'LDL',
				'Triglycerides',
				'Glucose',
				'HbA1c',
				'TSH',
				'Testosterone',
			);

			$found = 0;
			foreach ( $essential_biomarkers as $biomarker ) {
				if ( isset( $biomarker_data[ $biomarker ] ) ) {
					$found++;
				}
			}

			$percentage = ( $found / count( $essential_biomarkers ) ) * 100;

			if ( $found < count( $essential_biomarkers ) ) {
				$missing[] = 'Essential Lab Results (' . ( count( $essential_biomarkers ) - $found ) . ' missing)';
			}
		}

		return array(
			'percentage' => $percentage,
			'completed'  => is_array( $biomarker_data ) ? count( $biomarker_data ) : 0,
			'total'      => 8,
			'missing'    => $missing,
		);
	}

	/**
	 * Check symptoms completeness
	 *
	 * @param int $user_id User ID
	 * @return array Symptoms data
	 */
	private static function check_symptoms( $user_id ) {
		$symptoms_data = array();
		$missing       = array();

		if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
			$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $user_id );
			$symptoms_count       = ! empty( $centralized_symptoms['symptoms'] ) ? count( $centralized_symptoms['symptoms'] ) : 0;
		} else {
			$symptoms_count = 0;
		}

		if ( 0 === $symptoms_count ) {
			$missing[]  = 'Symptom Assessment';
			$percentage = 0;
		} else {
			$percentage = min( 100, ( $symptoms_count / 10 ) * 100 );
		}

		return array(
			'percentage' => $percentage,
			'completed'  => $symptoms_count,
			'total'      => 10,
			'missing'    => $missing,
		);
	}

	/**
	 * Determine accuracy level based on completeness percentage
	 *
	 * @param float $percentage Completeness percentage
	 * @return string Accuracy level
	 */
	private static function determine_accuracy_level( $percentage ) {
		if ( $percentage >= 90 ) {
			return 'excellent';
		}
		if ( $percentage >= 75 ) {
			return 'high';
		}
		if ( $percentage >= 50 ) {
			return 'medium';
		}
		if ( $percentage >= 25 ) {
			return 'low';
		}
		return 'very_low';
	}

	/**
	 * Generate recommendations based on completeness data
	 *
	 * @param array $completeness_data Completeness data
	 * @return array Recommendations
	 */
	private static function generate_recommendations( $completeness_data ) {
		$recommendations = array();

		foreach ( $completeness_data['category_scores'] as $category => $percentage ) {
			if ( $percentage < 75 ) {
				switch ( $category ) {
					case 'basic_info':
						$recommendations[] = 'Complete your basic profile information for more accurate scoring.';
						break;
					case 'health_goals':
						$recommendations[] = 'Select your health goals to unlock personalized recommendations.';
						break;
					case 'assessments':
						$recommendations[] = 'Take additional assessments to get comprehensive health insights.';
						break;
					case 'biomarkers':
						$recommendations[] = 'Upload your lab results for evidence-based health optimization.';
						break;
					case 'symptoms':
						$recommendations[] = 'Complete symptom assessments for targeted health recommendations.';
						break;
				}
			}
		}

		if ( $completeness_data['overall_percentage'] < 50 ) {
			$recommendations[] = 'Your profile is less than 50% complete. Consider completing more sections for better insights.';
		}

		return $recommendations;
	}

	/**
	 * Get completeness status for dashboard display
	 *
	 * @param int $user_id User ID
	 * @return array Status data for display
	 */
	public static function get_completeness_status( $user_id ) {
		$completeness = get_user_meta( $user_id, 'ennu_profile_completeness', true );

		if ( empty( $completeness ) ) {
			$completeness = self::calculate_completeness( $user_id );
		}

		return array(
			'percentage' => round( $completeness['overall_percentage'], 1 ),
			'level'      => $completeness['accuracy_level'],
			'next_steps' => array_slice( $completeness['recommendations'], 0, 3 ),
			'categories' => $completeness['category_scores'],
		);
	}
}
