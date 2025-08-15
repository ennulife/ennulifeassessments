<?php
/**
 * ENNU Life Qualitative Engine
 * Applies symptom-based pillar integrity penalties
 * Implements the second engine in the "Scoring Symphony"
 *
 * @package ENNU_Life
 * @version 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Qualitative_Engine {

	private $user_symptoms;
	private $symptom_map;
	private $penalty_matrix;
	private $penalty_log;

	public function __construct( $user_symptoms ) {
		$this->user_symptoms = is_array( $user_symptoms ) ? $user_symptoms : array();
		$this->load_configuration();
		$this->penalty_log = array();

	}

	private function load_configuration() {
		$symptom_map_file    = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/symptom-map.php';
		$penalty_matrix_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/penalty-matrix.php';

		$this->symptom_map    = file_exists( $symptom_map_file ) ? require $symptom_map_file : array();
		$this->penalty_matrix = file_exists( $penalty_matrix_file ) ? require $penalty_matrix_file : array();
	}

	public function apply_pillar_integrity_penalties( $base_pillar_scores ) {
		if ( empty( $this->user_symptoms ) || empty( $this->symptom_map ) || empty( $this->penalty_matrix ) ) {
			return $base_pillar_scores;
		}

		$penalized_scores     = $base_pillar_scores;
		$triggered_categories = $this->identify_triggered_categories();
		$pillar_penalties     = $this->calculate_pillar_penalties( $triggered_categories );

		foreach ( $pillar_penalties as $pillar => $penalty_multiplier ) {
			if ( isset( $penalized_scores[ $pillar ] ) ) {
				$original_score              = $penalized_scores[ $pillar ];
				$penalized_scores[ $pillar ] = $original_score * ( 1 - $penalty_multiplier );

				$this->penalty_log[] = array(
					'pillar'               => $pillar,
					'original_score'       => $original_score,
					'penalty_multiplier'   => $penalty_multiplier,
					'new_score'            => $penalized_scores[ $pillar ],
					'triggered_categories' => $triggered_categories[ $pillar ] ?? array(),
				);

			}
		}

		return $penalized_scores;
	}

	private function identify_triggered_categories() {
		$triggered_categories = array();

		foreach ( $this->user_symptoms as $symptom ) {
			if ( isset( $this->symptom_map[ $symptom ] ) ) {
				foreach ( $this->symptom_map[ $symptom ] as $category => $weight_data ) {
					if ( ! isset( $triggered_categories[ $category ] ) ) {
						$triggered_categories[ $category ] = array();
					}
					$triggered_categories[ $category ][] = array(
						'symptom' => $symptom,
						'weight'  => $weight_data['weight'] ?? 1.0,
					);
				}
			}
		}

		return $triggered_categories;
	}

	private function calculate_pillar_penalties( $triggered_categories ) {
		$pillar_penalties = array();

		foreach ( $this->penalty_matrix as $category => $category_config ) {
			if ( isset( $triggered_categories[ $category ] ) ) {
				$pillar    = $category_config['pillar_impact'];
				$severity  = $this->determine_category_severity( $category, $triggered_categories[ $category ] );
				$frequency = 'Daily';

				$penalty_value = $category_config['penalties'][ $severity ][ $frequency ] ?? 0;

				if ( ! isset( $pillar_penalties[ $pillar ] ) || $penalty_value > $pillar_penalties[ $pillar ] ) {
					$pillar_penalties[ $pillar ] = $penalty_value;
				}
			}
		}

		return $pillar_penalties;
	}

	private function determine_category_severity( $category, $symptoms ) {
		$total_weight = 0;
		foreach ( $symptoms as $symptom_data ) {
			$total_weight += $symptom_data['weight'];
		}

		if ( $total_weight >= 2.0 ) {
			return 'Severe';
		} elseif ( $total_weight >= 1.0 ) {
			return 'Moderate';
		} else {
			return 'Mild';
		}
	}

	public function get_penalty_log() {
		return $this->penalty_log;
	}

	public function get_penalty_summary() {
		$summary = array(
			'total_symptoms'      => count( $this->user_symptoms ),
			'penalties_applied'   => count( $this->penalty_log ),
			'pillars_penalized'   => array(),
			'total_penalty_value' => 0,
		);

		foreach ( $this->penalty_log as $log_entry ) {
			$summary['pillars_penalized'][]  = $log_entry['pillar'];
			$summary['total_penalty_value'] += $log_entry['penalty_multiplier'];
		}

		$summary['pillars_penalized'] = array_unique( $summary['pillars_penalized'] );

		return $summary;
	}

	public function get_user_explanation() {
		if ( empty( $this->user_symptoms ) ) {
			return 'No symptoms reported. Complete the Health Optimization Assessment to unlock symptom-based scoring adjustments.';
		}

		$summary = $this->get_penalty_summary();

		if ( $summary['penalties_applied'] === 0 ) {
			return 'Your reported symptoms did not trigger any pillar penalties. Your scores reflect your assessment responses without symptom-based adjustments.';
		}

		$pillars_text       = implode( ', ', $summary['pillars_penalized'] );
		$penalty_percentage = round( $summary['total_penalty_value'] * 100, 1 );

		return "Your {$summary['total_symptoms']} reported symptoms resulted in pillar integrity penalties affecting your {$pillars_text} pillar scores, with a total penalty impact of {$penalty_percentage}%.";
	}
}
