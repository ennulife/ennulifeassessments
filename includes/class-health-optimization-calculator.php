<?php
/**
 * ENNU Life Health Optimization Calculator
 *
 * This class is responsible for all calculations related to the Health Optimization feature,
 * including Pillar Integrity Penalties and biomarker recommendations.
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Health_Optimization_Calculator {

	private $user_id;
	private $mappings;
	private $all_definitions;

	public function __construct( $user_id, $all_definitions ) {
		$this->user_id         = $user_id;
		$this->all_definitions = $all_definitions;
		$this->mappings        = $this->get_health_optimization_mappings();
		error_log( "HealthOptimizationCalculator: Instantiated for user ID {$user_id}." );
	}

	public function calculate_pillar_penalties() {
		error_log( 'HealthOptimizationCalculator: Starting pillar penalty calculation.' );
		$triggered_vectors = $this->get_triggered_vectors();
		$penalty_matrix    = $this->mappings['pillar_integrity_penalty_matrix'] ?? array();

		$pillar_penalties = array(
			'mind'       => 0,
			'body'       => 0,
			'lifestyle'  => 0,
			'aesthetics' => 0,
		);

		foreach ( $triggered_vectors as $vector => $vector_data ) {
			error_log( "HealthOptimizationCalculator: Processing triggered vector '{$vector}'." );
			if ( isset( $penalty_matrix[ $vector ] ) ) {
				$vector_config = $penalty_matrix[ $vector ];
				$pillar_impact = $vector_config['pillar_impact'];

				$highest_impact_instance = null;
				foreach ( $vector_data['instances'] as $instance ) {
					if ( $highest_impact_instance === null ) {
						$highest_impact_instance = $instance;
					} else {
						$severity_map  = array(
							'Severe'   => 3,
							'Moderate' => 2,
							'Mild'     => 1,
						);
						$frequency_map = array(
							'Daily'   => 3,
							'Weekly'  => 2,
							'Monthly' => 1,
						);

						if ( ( $severity_map[ $instance['severity'] ] ?? 0 ) > ( $severity_map[ $highest_impact_instance['severity'] ] ?? 0 ) ) {
							$highest_impact_instance = $instance;
						} elseif ( ( $frequency_map[ $instance['frequency'] ] ?? 0 ) > ( $frequency_map[ $highest_impact_instance['frequency'] ] ?? 0 ) ) {
							 $highest_impact_instance = $instance;
						}
					}
				}

				if ( $highest_impact_instance ) {
					$severity     = $highest_impact_instance['severity'] ?? 'Mild';
					$frequency    = $highest_impact_instance['frequency'] ?? 'Monthly';
					$base_penalty = $vector_config['penalties'][ $severity ][ $frequency ] ?? 0;

					$trigger_score_multiplier = min( 1.5, 1 + ( $vector_data['score'] - 1 ) * 0.1 );
					$final_penalty            = $base_penalty * $trigger_score_multiplier;
					error_log( "HealthOptimizationCalculator: Vector '{$vector}' produced a final penalty of {$final_penalty} for pillar '{$pillar_impact}'." );

					if ( $final_penalty > $pillar_penalties[ $pillar_impact ] ) {
						$pillar_penalties[ $pillar_impact ] = $final_penalty;
						error_log( "HealthOptimizationCalculator: New max penalty for pillar '{$pillar_impact}' is {$final_penalty}." );
					}
				}
			}
		}
		error_log( 'HealthOptimizationCalculator: Final pillar penalties: ' . print_r( $pillar_penalties, true ) );
		return $pillar_penalties;
	}

	public function get_biomarker_recommendations() {
		error_log( 'HealthOptimizationCalculator: Starting biomarker recommendation.' );
		$triggered_vectors = $this->get_triggered_vectors();
		$biomarker_map     = $this->mappings['vector_to_biomarker_map'] ?? array();

		$recommended_biomarkers = array();
		foreach ( $triggered_vectors as $vector => $data ) {
			if ( isset( $biomarker_map[ $vector ] ) ) {
				$recommended_biomarkers = array_merge( $recommended_biomarkers, $biomarker_map[ $vector ] );
				error_log( "HealthOptimizationCalculator: Vector '{$vector}' recommends biomarkers: " . implode( ', ', $biomarker_map[ $vector ] ) );
			}
		}

		$final_recommendations = array_unique( $recommended_biomarkers );
		error_log( 'HealthOptimizationCalculator: Final unique biomarker recommendations: ' . print_r( $final_recommendations, true ) );
		return $final_recommendations;
	}

	public function get_triggered_vectors() {
		$symptom_data = $this->get_symptom_data_for_user();
		$vector_map   = $this->mappings['symptom_to_vector_map'] ?? array();

		$triggered_vectors = array();

		foreach ( $symptom_data as $q_id => $data ) {
			if ( empty( $data['selection'] ) ) {
				continue;
			}

			$symptoms = is_array( $data['selection'] ) ? $data['selection'] : array( $data['selection'] );
			foreach ( $symptoms as $symptom ) {
				if ( isset( $vector_map[ $symptom ] ) ) {
					foreach ( $vector_map[ $symptom ] as $vector => $vector_data ) {
						$weight = $vector_data['weight'] ?? 0.5;
						if ( ! isset( $triggered_vectors[ $vector ] ) ) {
							$triggered_vectors[ $vector ] = array(
								'score'     => 0,
								'instances' => array(),
							);
						}
						$triggered_vectors[ $vector ]['score']      += $weight;
						$triggered_vectors[ $vector ]['instances'][] = array(
							'severity'  => $data['severity'],
							'frequency' => $data['frequency'],
						);
					}
				}
			}
		}
		return $triggered_vectors;
	}

	private function get_symptom_data_for_user() {
		$symptom_data      = array();
		$symptom_questions = $this->all_definitions['health_optimization_assessment']['questions'] ?? array();

		foreach ( $symptom_questions as $q_id => $q_def ) {
			if ( strpos( $q_id, 'symptom_' ) === 0 ) {
				$severity_key  = str_replace( '_q', '_severity_q', $q_id );
				$frequency_key = str_replace( '_q', '_frequency_q', $q_id );

				$symptom_value = get_user_meta( $this->user_id, 'ennu_health_optimization_assessment_' . $q_id, true );
				if ( ! empty( $symptom_value ) ) {
					$symptom_data[ $q_id ] = array(
						'selection' => $symptom_value,
						'severity'  => get_user_meta( $this->user_id, 'ennu_health_optimization_assessment_' . $severity_key, true ),
						'frequency' => get_user_meta( $this->user_id, 'ennu_health_optimization_assessment_' . $frequency_key, true ),
					);
				}
			}
		}
		return $symptom_data;
	}

	private function get_health_optimization_mappings() {
		// Load the modularized mapping files
		$symptom_map_file    = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/symptom-map.php';
		$penalty_matrix_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/penalty-matrix.php';
		$biomarker_map_file  = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/biomarker-map.php';

		$mappings = array();
		if ( file_exists( $symptom_map_file ) ) {
			$mappings['symptom_to_vector_map'] = require $symptom_map_file;
		}
		if ( file_exists( $penalty_matrix_file ) ) {
			$mappings['pillar_integrity_penalty_matrix'] = require $penalty_matrix_file;
		}
		if ( file_exists( $biomarker_map_file ) ) {
			$mappings['vector_to_biomarker_map'] = require $biomarker_map_file;
		}

		return $mappings;
	}
}
