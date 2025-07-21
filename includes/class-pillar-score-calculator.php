<?php
/**
 * ENNU Life Pillar Score Calculator
 *
 * This class is responsible for calculating the four Pillar Scores (Mind, Body, Lifestyle, Aesthetics)
 * based on the category scores from one or more assessments.
 *
 * @package ENNU_Life
 * @version 60.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Pillar_Score_Calculator {

	private $category_scores;
	private $pillar_map;

	public function __construct( $category_scores, $pillar_map ) {
		$this->category_scores = $category_scores;
		$this->pillar_map      = $pillar_map;
		error_log( 'PillarScoreCalculator: Instantiated.' );
	}

	public function calculate() {
		error_log( 'PillarScoreCalculator: Starting calculation.' );
		$pillar_scores = array();
		$pillar_totals = array();
		$pillar_counts = array();

		if ( empty( $this->category_scores ) ) {
			error_log( 'PillarScoreCalculator: No category scores provided. Returning empty array.' );
			return $pillar_scores;
		}

		foreach ( $this->pillar_map as $pillar_name => $categories ) {
			$pillar_totals[ $pillar_name ] = 0;
			$pillar_counts[ $pillar_name ] = 0;
		}

		foreach ( $this->category_scores as $category => $score ) {
			error_log( "PillarScoreCalculator: Processing category '{$category}' with score {$score}." );
			foreach ( $this->pillar_map as $pillar_name => $pillar_data ) {
				// Handle both old format (direct array) and new format (with categories key)
				$categories = is_array( $pillar_data ) && isset( $pillar_data['categories'] )
					? $pillar_data['categories']
					: $pillar_data;

				if ( is_array( $categories ) && in_array( $category, $categories ) ) {
					error_log( "PillarScoreCalculator: Category '{$category}' maps to pillar '{$pillar_name}'." );
					$pillar_totals[ $pillar_name ] += $score;
					$pillar_counts[ $pillar_name ]++;
					break;
				}
			}
		}

		foreach ( $pillar_totals as $pillar_name => $total ) {
			if ( $pillar_counts[ $pillar_name ] > 0 ) {
				$pillar_scores[ $pillar_name ] = round( $total / $pillar_counts[ $pillar_name ], 1 );
			} else {
				$pillar_scores[ $pillar_name ] = 0;
			}
		}

		error_log( 'PillarScoreCalculator: Final pillar scores: ' . print_r( $pillar_scores, true ) );
		return $pillar_scores;
	}
}
