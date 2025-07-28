<?php
/**
 * ENNU Life Assessments - Smart Recommendation Engine
 *
 * Provides intelligent biomarker recommendations based on symptoms and lab data
 *
 * @package ENNU_Life
 * @version 62.7.0
 */

class ENNU_Smart_Recommendation_Engine {

	/**
	 * User ID
	 */
	private $user_id;

	/**
	 * User biomarkers
	 */
	private $user_biomarkers;

	/**
	 * Biomarker configuration
	 */
	private $biomarker_config;

	/**
	 * Constructor
	 */
	public function __construct( $user_id ) {
		$this->user_id = $user_id;
		$this->user_biomarkers = ENNU_Biomarker_Manager::get_user_biomarkers( $user_id );
		$this->load_biomarker_config();
	}

	/**
	 * Get updated biomarker recommendations for a user
	 *
	 * @return array Recommendations
	 */
	public function get_updated_recommendations() {
		try {
			$recommendations = array();

			// Step 1: Get all user symptoms
			$user_symptoms = $this->get_user_symptoms();

			// Step 2: Get user's existing biomarker data
			$user_biomarkers = $this->user_biomarkers;

			// Step 3: Get symptom-to-biomarker correlation map
			$symptom_biomarker_map = $this->get_symptom_biomarker_map();

			// Process each symptom
			foreach ( $user_symptoms as $symptom ) {
				if ( isset( $symptom_biomarker_map[ $symptom ] ) ) {
					foreach ( $symptom_biomarker_map[ $symptom ] as $biomarker ) {
						$recommendation = $this->evaluate_biomarker_recommendation( $symptom, $biomarker );
						if ( $recommendation ) {
							$recommendations[] = $recommendation;
						}
					}
				}
			}

			// Sort by priority
			usort(
				$recommendations,
				function( $a, $b ) {
					return $b['priority'] - $a['priority'];
				}
			);

			// Remove duplicates
			$recommendations = $this->remove_duplicate_recommendations( $recommendations );

			return $recommendations;

		} catch ( Exception $e ) {
			error_log( 'ENNU Smart Recommendation Engine Error: ' . $e->getMessage() );
			return array();
		}
	}

	/**
	 * Evaluate if a biomarker should be recommended
	 *
	 * @param string $symptom Symptom name
	 * @param string $biomarker Biomarker name
	 * @return array|null Recommendation data or null
	 */
	private function evaluate_biomarker_recommendation( $symptom, $biomarker ) {
		$latest_test = ENNU_Biomarker_Manager::get_latest_biomarker_test( $this->user_id, $biomarker );

		// If no test exists, recommend it
		if ( ! $latest_test ) {
			return $this->create_recommendation( $symptom, $biomarker, 'not_tested', null, 'high' );
		}

		// Check if test is outdated
		if ( ENNU_Biomarker_Manager::is_test_outdated( $latest_test ) ) {
			return $this->create_recommendation( $symptom, $biomarker, 'outdated', $latest_test['date_tested'], 'medium' );
		}

		// Check if test result indicates need for follow-up
		$follow_up_reason = $this->check_follow_up_needed( $latest_test, $symptom );
		if ( $follow_up_reason ) {
			return $this->create_recommendation( $symptom, $biomarker, 'follow_up', $latest_test['date_tested'], 'high', $follow_up_reason );
		}

		// No recommendation needed
		return null;
	}

	/**
	 * Create recommendation data structure
	 *
	 * @param string $symptom Symptom name
	 * @param string $biomarker Biomarker name
	 * @param string $reason Reason for recommendation
	 * @param string|null $last_tested Last test date
	 * @param string $priority Priority level
	 * @param string|null $follow_up_reason Follow-up reason
	 * @return array Recommendation data
	 */
	private function create_recommendation( $symptom, $biomarker, $reason, $last_tested, $priority, $follow_up_reason = null ) {
		$recommendation = array(
			'biomarker'           => $biomarker,
			'reason'              => $symptom,
			'recommendation_type' => $reason,
			'priority'            => $priority,
			'last_tested'         => $last_tested,
			'urgency'             => $this->calculate_urgency( $symptom, $biomarker, $reason ),
			'estimated_cost'      => $this->get_estimated_cost( $biomarker ),
			'time_to_results'     => $this->get_time_to_results( $biomarker ),
			'follow_up_reason'    => $follow_up_reason,
		);

		return $recommendation;
	}

	/**
	 * Check if follow-up testing is needed
	 *
	 * @param array $test_data Test data
	 * @param string $symptom Symptom name
	 * @return string|null Follow-up reason or null
	 */
	private function check_follow_up_needed( $test_data, $symptom ) {
		if ( empty( $test_data['status'] ) ) {
			return null;
		}

		$status = $test_data['status'];
		$value  = $test_data['value'];

		// Define follow-up conditions based on biomarker and symptom
		$follow_up_conditions = $this->get_follow_up_conditions();

		if ( isset( $follow_up_conditions[ $test_data['biomarker_name'] ] ) ) {
			$conditions = $follow_up_conditions[ $test_data['biomarker_name'] ];

			foreach ( $conditions as $condition ) {
				if ( $this->evaluate_follow_up_condition( $test_data, $condition, $symptom ) ) {
					return $condition['reason'];
				}
			}
		}

		return null;
	}

	/**
	 * Get follow-up conditions for biomarkers
	 *
	 * @return array Follow-up conditions
	 */
	private function get_follow_up_conditions() {
		return array(
			'Testosterone' => array(
				array(
					'condition' => 'status == "low"',
					'reason'    => 'Low testosterone levels detected - follow-up recommended',
					'urgency'   => 'high',
				),
				array(
					'condition' => 'status == "high"',
					'reason'    => 'Elevated testosterone levels - monitoring recommended',
					'urgency'   => 'medium',
				),
			),
			'Vitamin D'    => array(
				array(
					'condition' => 'status == "low"',
					'reason'    => 'Vitamin D deficiency detected - supplementation monitoring needed',
					'urgency'   => 'high',
				),
			),
			'Cortisol'     => array(
				array(
					'condition' => 'status == "high"',
					'reason'    => 'Elevated cortisol levels - stress management recommended',
					'urgency'   => 'high',
				),
				array(
					'condition' => 'status == "low"',
					'reason'    => 'Low cortisol levels - adrenal function evaluation needed',
					'urgency'   => 'high',
				),
			),
			'TSH'          => array(
				array(
					'condition' => 'status == "high"',
					'reason'    => 'Elevated TSH - thyroid function evaluation recommended',
					'urgency'   => 'high',
				),
				array(
					'condition' => 'status == "low"',
					'reason'    => 'Low TSH - hyperthyroidism evaluation needed',
					'urgency'   => 'high',
				),
			),
		);
	}

	/**
	 * Evaluate follow-up condition
	 *
	 * @param array $test_data Test data
	 * @param array $condition Condition to evaluate
	 * @param string $symptom Symptom name
	 * @return bool True if condition is met
	 */
	private function evaluate_follow_up_condition( $test_data, $condition, $symptom ) {
		$condition_expr = $condition['condition'];

		// Simple condition evaluation
		if ( strpos( $condition_expr, 'status ==' ) !== false ) {
			$required_status = str_replace( '"', '', substr( $condition_expr, strpos( $condition_expr, '"' ) + 1 ) );
			$required_status = str_replace( '"', '', $required_status );

			return $test_data['status'] === $required_status;
		}

		return false;
	}

	/**
	 * Calculate urgency level
	 *
	 * @param string $symptom Symptom name
	 * @param string $biomarker Biomarker name
	 * @param string $reason Recommendation reason
	 * @return string Urgency level
	 */
	private function calculate_urgency( $symptom, $biomarker, $reason ) {
		$symptom_urgency   = $this->get_symptom_urgency( $symptom );
		$biomarker_urgency = $this->get_biomarker_urgency( $biomarker );
		$reason_urgency    = $this->get_reason_urgency( $reason );

		// Calculate weighted urgency
		$urgency_score = ( $symptom_urgency * 0.4 ) + ( $biomarker_urgency * 0.4 ) + ( $reason_urgency * 0.2 );

		if ( $urgency_score >= 4 ) {
			return 'critical';
		} elseif ( $urgency_score >= 3 ) {
			return 'high';
		} elseif ( $urgency_score >= 2 ) {
			return 'medium';
		} else {
			return 'low';
		}
	}

	/**
	 * Get symptom urgency score
	 *
	 * @param string $symptom Symptom name
	 * @return int Urgency score (1-5)
	 */
	private function get_symptom_urgency( $symptom ) {
		$urgency_scores = array(
			'Fatigue'             => 4,
			'Anxiety'             => 3,
			'Low Libido'          => 2,
			'Weight Gain'         => 3,
			'Brain Fog'           => 4,
			'Sleep Problems'      => 3,
			'Joint Pain'          => 2,
			'Depression'          => 5,
			'Chest Pain'          => 5,
			'Shortness of Breath' => 5,
			'Severe Headache'     => 5,
		);

		return $urgency_scores[ $symptom ] ?? 2;
	}

	/**
	 * Get biomarker urgency score
	 *
	 * @param string $biomarker Biomarker name
	 * @return int Urgency score (1-5)
	 */
	private function get_biomarker_urgency( $biomarker ) {
		$urgency_scores = array(
			'Testosterone' => 4,
			'Vitamin D'    => 3,
			'Cortisol'     => 4,
			'TSH'          => 4,
			'Glucose'      => 4,
			'HbA1c'        => 4,
			'Insulin'      => 3,
			'CRP'          => 3,
			'Homocysteine' => 3,
			'Vitamin B12'  => 3,
			'Iron'         => 3,
			'Ferritin'     => 2,
		);

		return $urgency_scores[ $biomarker ] ?? 2;
	}

	/**
	 * Get reason urgency score
	 *
	 * @param string $reason Recommendation reason
	 * @return int Urgency score (1-5)
	 */
	private function get_reason_urgency( $reason ) {
		$urgency_scores = array(
			'not_tested' => 3,
			'outdated'   => 2,
			'follow_up'  => 4,
			'abnormal'   => 4,
			'critical'   => 5,
		);

		return $urgency_scores[ $reason ] ?? 2;
	}

	/**
	 * Get estimated cost for biomarker test
	 *
	 * @param string $biomarker Biomarker name
	 * @return string Estimated cost
	 */
	private function get_estimated_cost( $biomarker ) {
		$costs = array(
			'Testosterone' => '$50-100',
			'Vitamin D'    => '$30-60',
			'Cortisol'     => '$40-80',
			'TSH'          => '$30-60',
			'Glucose'      => '$20-40',
			'HbA1c'        => '$25-50',
			'Insulin'      => '$40-80',
			'CRP'          => '$30-60',
			'Homocysteine' => '$50-100',
			'Vitamin B12'  => '$30-60',
			'Iron'         => '$25-50',
			'Ferritin'     => '$30-60',
		);

		return $costs[ $biomarker ] ?? '$30-80';
	}

	/**
	 * Get time to results for biomarker test
	 *
	 * @param string $biomarker Biomarker name
	 * @return string Time to results
	 */
	private function get_time_to_results( $biomarker ) {
		$times = array(
			'Testosterone' => '1-3 days',
			'Vitamin D'    => '1-2 days',
			'Cortisol'     => '1-2 days',
			'TSH'          => '1-2 days',
			'Glucose'      => 'Same day',
			'HbA1c'        => '1-2 days',
			'Insulin'      => '1-2 days',
			'CRP'          => '1-2 days',
			'Homocysteine' => '2-3 days',
			'Vitamin B12'  => '1-2 days',
			'Iron'         => '1-2 days',
			'Ferritin'     => '1-2 days',
		);

		return $times[ $biomarker ] ?? '1-3 days';
	}

	/**
	 * Remove duplicate recommendations
	 *
	 * @param array $recommendations Recommendations array
	 * @return array Deduplicated recommendations
	 */
	private function remove_duplicate_recommendations( $recommendations ) {
		$seen         = array();
		$deduplicated = array();

		foreach ( $recommendations as $recommendation ) {
			$key = $recommendation['biomarker'];

			if ( ! isset( $seen[ $key ] ) ) {
				$seen[ $key ]   = true;
				$deduplicated[] = $recommendation;
			} else {
				// If duplicate found, keep the one with higher priority
				foreach ( $deduplicated as $index => $existing ) {
					if ( $existing['biomarker'] === $key ) {
						if ( $recommendation['priority'] > $existing['priority'] ) {
							$deduplicated[ $index ] = $recommendation;
						}
						break;
					}
				}
			}
		}

		return $deduplicated;
	}

	/**
	 * Get user symptoms from centralized symptoms system
	 *
	 * @return array Symptoms
	 */
	private function get_user_symptoms() {
		$centralized_symptoms = get_user_meta( $this->user_id, 'ennu_centralized_symptoms', true );

		if ( ! is_array( $centralized_symptoms ) ) {
			return array();
		}

		$symptoms = array();
		foreach ( $centralized_symptoms as $category => $symptom_list ) {
			if ( is_array( $symptom_list ) ) {
				foreach ( $symptom_list as $symptom => $data ) {
					$symptoms[] = $symptom;
				}
			}
		}

		return $symptoms;
	}

	/**
	 * Get symptom-biomarker correlations
	 *
	 * @return array Correlations
	 */
	private function get_symptom_biomarker_map() {
		// Load from configuration file
		$correlations_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/symptom-biomarker-correlations.php';

		if ( file_exists( $correlations_file ) ) {
			return include $correlations_file;
		}

		// Return default correlations
		return array(
			'Fatigue'             => array( 'Testosterone', 'Vitamin D', 'Vitamin B12', 'Iron', 'Cortisol', 'TSH' ),
			'Anxiety'             => array( 'Cortisol', 'Testosterone', 'Vitamin D', 'Magnesium', 'TSH' ),
			'Low Libido'          => array( 'Testosterone', 'Estradiol', 'Prolactin', 'Cortisol' ),
			'Weight Gain'         => array( 'Testosterone', 'Cortisol', 'Insulin', 'TSH', 'Glucose' ),
			'Brain Fog'           => array( 'Vitamin B12', 'Vitamin D', 'Homocysteine', 'TSH', 'Cortisol' ),
			'Sleep Problems'      => array( 'Cortisol', 'Melatonin', 'Testosterone', 'Vitamin D' ),
			'Joint Pain'          => array( 'Vitamin D', 'CRP', 'ESR', 'Uric Acid', 'Calcium' ),
			'Depression'          => array( 'Vitamin D', 'Vitamin B12', 'Cortisol', 'Testosterone', 'TSH' ),
			'Chest Pain'          => array( 'CRP', 'Homocysteine', 'Lipid Panel', 'Troponin' ),
			'Shortness of Breath' => array( 'Hemoglobin', 'Iron', 'Ferritin', 'BNP' ),
			'Severe Headache'     => array( 'CRP', 'Homocysteine', 'Blood Pressure', 'Glucose' ),
		);
	}

	/**
	 * Get biomarker trends for a user
	 *
	 * @param string $biomarker Biomarker name
	 * @param int $months Number of months to analyze
	 * @return array Trend data
	 */
	public function get_biomarker_trends( $biomarker, $months = 12 ) {
		$biomarker_data = $this->user_biomarkers;

		if ( empty( $biomarker_data ) ) {
			return array();
		}

		// Sort by date
		usort(
			$biomarker_data,
			function( $a, $b ) {
				return strtotime( $a['date_tested'] ) - strtotime( $b['date_tested'] );
			}
		);

		// Filter to specified time period
		$cutoff_date   = date( 'Y-m-d', strtotime( "-{$months} months" ) );
		$filtered_data = array_filter(
			$biomarker_data,
			function( $test ) use ( $cutoff_date ) {
				return $test['date_tested'] >= $cutoff_date;
			}
		);

		// Calculate trend
		$trend = $this->calculate_trend( $filtered_data );

		return array(
			'biomarker'         => $biomarker,
			'data_points'       => count( $filtered_data ),
			'trend'             => $trend,
			'latest_value'      => end( $filtered_data )['value'] ?? null,
			'earliest_value'    => reset( $filtered_data )['value'] ?? null,
			'change_percentage' => $this->calculate_change_percentage( $filtered_data ),
		);
	}

	/**
	 * Calculate trend from data points
	 *
	 * @param array $data_points Data points
	 * @return string Trend direction
	 */
	private function calculate_trend( $data_points ) {
		if ( count( $data_points ) < 2 ) {
			return 'insufficient_data';
		}

		$values      = array_column( $data_points, 'value' );
		$first_value = reset( $values );
		$last_value  = end( $values );

		$change            = $last_value - $first_value;
		$change_percentage = ( $change / $first_value ) * 100;

		if ( $change_percentage > 10 ) {
			return 'increasing';
		} elseif ( $change_percentage < -10 ) {
			return 'decreasing';
		} else {
			return 'stable';
		}
	}

	/**
	 * Calculate percentage change
	 *
	 * @param array $data_points Data points
	 * @return float Percentage change
	 */
	private function calculate_change_percentage( $data_points ) {
		if ( count( $data_points ) < 2 ) {
			return 0;
		}

		$values      = array_column( $data_points, 'value' );
		$first_value = reset( $values );
		$last_value  = end( $values );

		if ( $first_value == 0 ) {
			return 0;
		}

		return ( ( $last_value - $first_value ) / $first_value ) * 100;
	}

	/**
	 * Load biomarker configuration
	 */
	private function load_biomarker_config() {
		$config_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/biomarker-config.php';

		if ( file_exists( $config_file ) ) {
			$this->biomarker_config = include $config_file;
		} else {
			$this->biomarker_config = array();
		}
	}
}
