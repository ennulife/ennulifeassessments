<?php
/**
 * ENNU Life Intentionality Engine
 * Applies goal alignment boosts to pillar scores
 * Implements the fourth engine in the "Scoring Symphony"
 *
 * @package ENNU_Life
 * @version 62.1.67
 * @author The World's Greatest WordPress Developer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Intentionality_Engine {

	private $user_health_goals;
	private $goal_definitions;
	private $base_pillar_scores;
	private $boost_log;

	public function __construct( $user_health_goals, $goal_definitions, $base_pillar_scores ) {
		$this->user_health_goals  = is_array( $user_health_goals ) ? $user_health_goals : array();
		$this->goal_definitions   = $goal_definitions;
		$this->base_pillar_scores = $base_pillar_scores;
		$this->boost_log          = array();

		error_log( 'ENNU Intentionality Engine: Initialized with ' . count( $this->user_health_goals ) . ' user goals' );
	}

	/**
	 * Apply goal alignment boost to pillar scores
	 * Implements the documented +5% non-cumulative boost system
	 */
	public function apply_goal_alignment_boost() {
		if ( empty( $this->user_health_goals ) || empty( $this->goal_definitions ) ) {
			error_log( 'ENNU Intentionality Engine: No goals or definitions available, returning original scores' );
			return $this->base_pillar_scores;
		}

		$boosted_scores = $this->base_pillar_scores;
		$applied_boosts = array(); // Track which pillars have received boosts

		// Get boost rules from configuration
		$boost_rules = $this->goal_definitions['boost_rules'] ?? array();
		$goal_map    = $this->goal_definitions['goal_to_pillar_map'] ?? array();

		$max_boost_per_pillar = $boost_rules['max_boost_per_pillar'] ?? 0.05;
		$cumulative_boost     = $boost_rules['cumulative_boost'] ?? false;

		error_log( 'ENNU Intentionality Engine: Processing ' . count( $this->user_health_goals ) . ' goals' );

		foreach ( $this->user_health_goals as $goal ) {
			if ( isset( $goal_map[ $goal ] ) ) {
				$goal_config    = $goal_map[ $goal ];
				$primary_pillar = $goal_config['primary_pillar'];
				$boost_amount   = $goal_config['boost_percentage'] ?? $max_boost_per_pillar;

				// Convert pillar name to proper case for matching
				$pillar_name = $this->normalize_pillar_name( $primary_pillar );

				error_log( "ENNU Intentionality Engine: Processing goal '{$goal}' for pillar '{$pillar_name}'" );

				// Apply boost only if pillar exists in scores
				if ( isset( $boosted_scores[ $pillar_name ] ) ) {

					// Check if boost should be applied (non-cumulative by default)
					if ( $cumulative_boost || ! isset( $applied_boosts[ $pillar_name ] ) ) {

						$current_score    = $boosted_scores[ $pillar_name ];
						$boost_multiplier = 1 + $boost_amount;
						$new_score        = $current_score * $boost_multiplier;

						// Cap at maximum possible score (10.0)
						$new_score = min( $new_score, 10.0 );

						$boosted_scores[ $pillar_name ] = $new_score;

						// Log the boost application
						$this->boost_log[] = array(
							'goal'           => $goal,
							'pillar'         => $pillar_name,
							'original_score' => $current_score,
							'boost_amount'   => $boost_amount,
							'new_score'      => $new_score,
							'boost_applied'  => true,
						);

						$applied_boosts[ $pillar_name ] = true;

						error_log( "ENNU Intentionality Engine: Applied {$boost_amount}% boost to {$pillar_name} pillar ({$current_score} -> {$new_score})" );

					} else {
						// Boost not applied due to non-cumulative rule
						$this->boost_log[] = array(
							'goal'           => $goal,
							'pillar'         => $pillar_name,
							'original_score' => $boosted_scores[ $pillar_name ],
							'boost_amount'   => $boost_amount,
							'new_score'      => $boosted_scores[ $pillar_name ],
							'boost_applied'  => false,
							'reason'         => 'non_cumulative_rule',
						);

						error_log( "ENNU Intentionality Engine: Skipped boost for {$pillar_name} pillar (non-cumulative rule)" );
					}
				} else {
					error_log( "ENNU Intentionality Engine: Pillar '{$pillar_name}' not found in scores" );
				}
			} else {
				error_log( "ENNU Intentionality Engine: Goal '{$goal}' not found in goal definitions" );
			}
		}

		error_log( 'ENNU Intentionality Engine: Applied boosts to ' . count( $applied_boosts ) . ' pillars' );

		return $boosted_scores;
	}

	/**
	 * Normalize pillar name to proper case for matching
	 */
	private function normalize_pillar_name( $pillar_name ) {
		$pillar_map = array(
			'mind'       => 'Mind',
			'body'       => 'Body',
			'lifestyle'  => 'Lifestyle',
			'aesthetics' => 'Aesthetics',
		);

		$lowercase_pillar = strtolower( $pillar_name );
		return $pillar_map[ $lowercase_pillar ] ?? ucfirst( $pillar_name );
	}

	/**
	 * Get detailed boost application log
	 */
	public function get_boost_log() {
		return $this->boost_log;
	}

	/**
	 * Get boost summary for user display
	 */
	public function get_boost_summary() {
		$summary = array(
			'total_goals'       => count( $this->user_health_goals ),
			'boosts_applied'    => 0,
			'pillars_boosted'   => array(),
			'total_boost_value' => 0,
		);

		foreach ( $this->boost_log as $log_entry ) {
			if ( $log_entry['boost_applied'] ) {
				$summary['boosts_applied']++;
				$summary['pillars_boosted'][]  = $log_entry['pillar'];
				$summary['total_boost_value'] += $log_entry['boost_amount'];
			}
		}

		$summary['pillars_boosted'] = array_unique( $summary['pillars_boosted'] );

		return $summary;
	}

	/**
	 * Calculate potential boost if all goals were aligned
	 */
	public function calculate_potential_boost( $all_possible_goals = null ) {
		if ( $all_possible_goals === null ) {
			$all_possible_goals = array_keys( $this->goal_definitions['goal_to_pillar_map'] ?? array() );
		}

		$potential_boosts = array();
		$goal_map         = $this->goal_definitions['goal_to_pillar_map'] ?? array();

		foreach ( $all_possible_goals as $goal ) {
			if ( isset( $goal_map[ $goal ] ) ) {
				$primary_pillar = $this->normalize_pillar_name( $goal_map[ $goal ]['primary_pillar'] );
				$boost_amount   = $goal_map[ $goal ]['boost_percentage'] ?? 0.05;

				if ( ! isset( $potential_boosts[ $primary_pillar ] ) ) {
					$potential_boosts[ $primary_pillar ] = $boost_amount;
				}
			}
		}

		return $potential_boosts;
	}

	/**
	 * Validate goal configuration
	 */
	public function validate_configuration() {
		$validation_results = array(
			'valid'    => true,
			'errors'   => array(),
			'warnings' => array(),
		);

		// Check if goal definitions exist
		if ( empty( $this->goal_definitions ) ) {
			$validation_results['valid']    = false;
			$validation_results['errors'][] = 'No goal definitions provided';
			return $validation_results;
		}

		// Check goal-to-pillar mapping
		$goal_map = $this->goal_definitions['goal_to_pillar_map'] ?? array();
		if ( empty( $goal_map ) ) {
			$validation_results['valid']    = false;
			$validation_results['errors'][] = 'No goal-to-pillar mapping found';
		}

		// Validate each goal mapping
		foreach ( $goal_map as $goal_id => $goal_config ) {
			if ( ! isset( $goal_config['primary_pillar'] ) ) {
				$validation_results['errors'][] = "Goal '{$goal_id}' missing primary_pillar";
				$validation_results['valid']    = false;
			}

			if ( ! isset( $goal_config['boost_percentage'] ) ) {
				$validation_results['warnings'][] = "Goal '{$goal_id}' missing boost_percentage, using default";
			}
		}

		// Check boost rules
		$boost_rules = $this->goal_definitions['boost_rules'] ?? array();
		if ( empty( $boost_rules ) ) {
			$validation_results['warnings'][] = 'No boost rules defined, using defaults';
		}

		return $validation_results;
	}

	/**
	 * Static method to quickly check if goals affect scoring
	 */
	public static function goals_affect_scoring( $user_goals, $goal_definitions ) {
		if ( empty( $user_goals ) || empty( $goal_definitions ) ) {
			return false;
		}

		$goal_map = $goal_definitions['goal_to_pillar_map'] ?? array();

		foreach ( $user_goals as $goal ) {
			if ( isset( $goal_map[ $goal ] ) ) {
				return true; // At least one goal has scoring impact
			}
		}

		return false;
	}

	/**
	 * Get user-friendly explanation of goal boosts
	 */
	public function get_user_explanation() {
		if ( empty( $this->user_health_goals ) ) {
			return 'No health goals selected. Set your goals to receive alignment bonuses!';
		}

		$summary = $this->get_boost_summary();

		if ( $summary['boosts_applied'] === 0 ) {
			return 'Your health goals are set, but no alignment bonuses were applied. Complete more assessments to unlock goal benefits!';
		}

		$pillars_text     = implode( ', ', $summary['pillars_boosted'] );
		$boost_percentage = round( $summary['total_boost_value'] * 100, 1 );

		return "Your {$summary['total_goals']} health goals provided alignment bonuses to your {$pillars_text} pillar scores, adding up to {$boost_percentage}% total boost value.";
	}
}
