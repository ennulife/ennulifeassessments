<?php
/**
 * ENNU Life Centralized Symptoms Manager
 *
 * Manages centralized storage and retrieval of all user symptoms across all assessments.
 * Provides a single source of truth for symptom data with comprehensive analytics.
 *
 * @package ENNU_Life
 * @version 62.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Centralized_Symptoms_Manager {

	/**
	 * Centralized symptoms meta key
	 */
	const CENTRALIZED_SYMPTOMS_KEY = 'ennu_centralized_symptoms';

	/**
	 * Symptom history meta key
	 */
	const SYMPTOM_HISTORY_KEY = 'ennu_symptom_history';

	/**
	 * Update centralized symptoms for a user
	 *
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type (optional - if provided, only update from this assessment)
	 * @return bool Success status
	 */
	public static function update_centralized_symptoms( $user_id, $assessment_type = null ) {
		try {
			$all_symptoms = self::aggregate_all_symptoms( $user_id, $assessment_type );
			
			// Save centralized symptoms
			update_user_meta( $user_id, self::CENTRALIZED_SYMPTOMS_KEY, $all_symptoms );
			
			// Update symptom history
			self::update_symptom_history( $user_id, $all_symptoms );
			
			// Clear any caches
			wp_cache_delete( $user_id, 'user_meta' );
			
			return true;
		} catch ( Exception $e ) {
			error_log( 'ENNU Centralized Symptoms: Error updating symptoms for user ' . $user_id . ': ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Get centralized symptoms for a user
	 *
	 * @param int $user_id User ID
	 * @return array Centralized symptoms data
	 */
	public static function get_centralized_symptoms( $user_id ) {
		$symptoms = get_user_meta( $user_id, self::CENTRALIZED_SYMPTOMS_KEY, true );
		
		if ( empty( $symptoms ) || ! is_array( $symptoms ) ) {
			// If no centralized data exists, create it
			self::update_centralized_symptoms( $user_id );
			$symptoms = get_user_meta( $user_id, self::CENTRALIZED_SYMPTOMS_KEY, true );
		}
		
		return is_array( $symptoms ) ? $symptoms : array();
	}

	/**
	 * Get symptom history for a user
	 *
	 * @param int $user_id User ID
	 * @param int $limit Number of history entries to return
	 * @return array Symptom history
	 */
	public static function get_symptom_history( $user_id, $limit = 10 ) {
		$history = get_user_meta( $user_id, self::SYMPTOM_HISTORY_KEY, true );
		
		if ( ! is_array( $history ) ) {
			return array();
		}
		
		// Sort by date descending and limit
		usort( $history, function( $a, $b ) {
			return strtotime( $b['date'] ) - strtotime( $a['date'] );
		} );
		
		return array_slice( $history, 0, $limit );
	}

	/**
	 * Aggregate all symptoms from all assessments
	 *
	 * @param int $user_id User ID
	 * @param string $assessment_type Optional assessment type to limit aggregation
	 * @return array Aggregated symptoms
	 */
	private static function aggregate_all_symptoms( $user_id, $assessment_type = null ) {
		$all_symptoms = array(
			'symptoms' => array(),
			'by_assessment' => array(),
			'by_category' => array(),
			'by_severity' => array(),
			'by_frequency' => array(),
			'total_count' => 0,
			'last_updated' => current_time( 'mysql' ),
			'user_id' => $user_id
		);

		// Define assessment types to process
		$assessment_types = $assessment_type ? array( $assessment_type ) : array(
			'health_optimization',
			'hormone',
			'testosterone',
			'menopause',
			'ed_treatment',
			'skin',
			'hair',
			'sleep',
			'weight_loss'
		);

		foreach ( $assessment_types as $type ) {
			$assessment_symptoms = self::get_assessment_symptoms( $user_id, $type );
			
			if ( ! empty( $assessment_symptoms ) ) {
				$all_symptoms['by_assessment'][ $type ] = $assessment_symptoms;
				
				// Aggregate individual symptoms
				foreach ( $assessment_symptoms as $symptom ) {
					$symptom_key = $symptom['name'];
					
					// Add to main symptoms array
					if ( ! isset( $all_symptoms['symptoms'][ $symptom_key ] ) ) {
						$all_symptoms['symptoms'][ $symptom_key ] = array(
							'name' => $symptom['name'],
							'category' => $symptom['category'],
							'assessments' => array(),
							'severity' => array(),
							'frequency' => array(),
							'first_reported' => $symptom['date'],
							'last_reported' => $symptom['date'],
							'occurrence_count' => 0
						);
					}
					
					// Update symptom data
					$all_symptoms['symptoms'][ $symptom_key ]['assessments'][] = $type;
					$all_symptoms['symptoms'][ $symptom_key ]['assessments'] = array_unique( $all_symptoms['symptoms'][ $symptom_key ]['assessments'] );
					
					if ( isset( $symptom['severity'] ) ) {
						$all_symptoms['symptoms'][ $symptom_key ]['severity'][] = $symptom['severity'];
					}
					
					if ( isset( $symptom['frequency'] ) ) {
						$all_symptoms['symptoms'][ $symptom_key ]['frequency'][] = $symptom['frequency'];
					}
					
					$all_symptoms['symptoms'][ $symptom_key ]['last_reported'] = $symptom['date'];
					$all_symptoms['symptoms'][ $symptom_key ]['occurrence_count']++;
					
					// Add to category grouping
					$category = $symptom['category'];
					if ( ! isset( $all_symptoms['by_category'][ $category ] ) ) {
						$all_symptoms['by_category'][ $category ] = array();
					}
					$all_symptoms['by_category'][ $category ][] = $symptom_key;
					
					// Add to severity grouping
					if ( isset( $symptom['severity'] ) ) {
						$severity = $symptom['severity'];
						if ( ! isset( $all_symptoms['by_severity'][ $severity ] ) ) {
							$all_symptoms['by_severity'][ $severity ] = array();
						}
						$all_symptoms['by_severity'][ $severity ][] = $symptom_key;
					}
					
					// Add to frequency grouping
					if ( isset( $symptom['frequency'] ) ) {
						$frequency = $symptom['frequency'];
						if ( ! isset( $all_symptoms['by_frequency'][ $frequency ] ) ) {
							$all_symptoms['by_frequency'][ $frequency ] = array();
						}
						$all_symptoms['by_frequency'][ $frequency ][] = $symptom_key;
					}
					
					$all_symptoms['total_count']++;
				}
			}
		}

		// Remove duplicates from arrays
		foreach ( $all_symptoms['by_category'] as $category => $symptoms ) {
			$all_symptoms['by_category'][ $category ] = array_unique( $symptoms );
		}
		foreach ( $all_symptoms['by_severity'] as $severity => $symptoms ) {
			$all_symptoms['by_severity'][ $severity ] = array_unique( $symptoms );
		}
		foreach ( $all_symptoms['by_frequency'] as $frequency => $symptoms ) {
			$all_symptoms['by_frequency'][ $frequency ] = array_unique( $symptoms );
		}

		return $all_symptoms;
	}

	/**
	 * Get symptoms from a specific assessment
	 *
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @return array Assessment symptoms
	 */
	private static function get_assessment_symptoms( $user_id, $assessment_type ) {
		$symptoms = array();
		
		switch ( $assessment_type ) {
			case 'health_optimization':
				$symptoms = self::get_health_optimization_symptoms( $user_id );
				break;
			case 'hormone':
				$symptoms = self::get_hormone_symptoms( $user_id );
				break;
			case 'testosterone':
				$symptoms = self::get_testosterone_symptoms( $user_id );
				break;
			case 'menopause':
				$symptoms = self::get_menopause_symptoms( $user_id );
				break;
			case 'ed_treatment':
				$symptoms = self::get_ed_treatment_symptoms( $user_id );
				break;
			case 'skin':
				$symptoms = self::get_skin_symptoms( $user_id );
				break;
			case 'hair':
				$symptoms = self::get_hair_symptoms( $user_id );
				break;
			case 'sleep':
				$symptoms = self::get_sleep_symptoms( $user_id );
				break;
			case 'weight_loss':
				$symptoms = self::get_weight_loss_symptoms( $user_id );
				break;
		}
		
		return $symptoms;
	}

	/**
	 * Get health optimization symptoms
	 */
	private static function get_health_optimization_symptoms( $user_id ) {
		$symptoms = array();
		$symptom_questions = array( 'symptom_q1', 'symptom_q2', 'symptom_q3', 'symptom_q4', 'symptom_q5' );
		
		foreach ( $symptom_questions as $q_id ) {
			$symptom_value = get_user_meta( $user_id, 'ennu_health_optimization_assessment_' . $q_id, true );
			$severity_key = str_replace( '_q', '_severity_q', $q_id );
			$frequency_key = str_replace( '_q', '_frequency_q', $q_id );
			
			if ( ! empty( $symptom_value ) ) {
				$symptoms[] = array(
					'name' => $symptom_value,
					'category' => 'Health Optimization',
					'severity' => get_user_meta( $user_id, 'ennu_health_optimization_assessment_' . $severity_key, true ),
					'frequency' => get_user_meta( $user_id, 'ennu_health_optimization_assessment_' . $frequency_key, true ),
					'date' => get_user_meta( $user_id, 'ennu_health_optimization_assessment_score_calculated_at', true ) ?: current_time( 'mysql' )
				);
			}
		}
		
		return $symptoms;
	}

	/**
	 * Get hormone symptoms
	 */
	private static function get_hormone_symptoms( $user_id ) {
		$symptoms = array();
		$symptom_selections = get_user_meta( $user_id, 'ennu_hormone_hormone_q1', true );
		
		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name' => $symptom,
					'category' => 'Hormone',
					'date' => get_user_meta( $user_id, 'ennu_hormone_score_calculated_at', true ) ?: current_time( 'mysql' )
				);
			}
		}
		
		return $symptoms;
	}

	/**
	 * Get testosterone symptoms
	 */
	private static function get_testosterone_symptoms( $user_id ) {
		$symptoms = array();
		$symptom_selections = get_user_meta( $user_id, 'ennu_testosterone_testosterone_q1', true );
		
		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name' => $symptom,
					'category' => 'Testosterone',
					'date' => get_user_meta( $user_id, 'ennu_testosterone_score_calculated_at', true ) ?: current_time( 'mysql' )
				);
			}
		}
		
		return $symptoms;
	}

	/**
	 * Get menopause symptoms
	 */
	private static function get_menopause_symptoms( $user_id ) {
		$symptoms = array();
		$symptom_selections = get_user_meta( $user_id, 'ennu_menopause_menopause_q1', true );
		
		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name' => $symptom,
					'category' => 'Menopause',
					'date' => get_user_meta( $user_id, 'ennu_menopause_score_calculated_at', true ) ?: current_time( 'mysql' )
				);
			}
		}
		
		return $symptoms;
	}

	/**
	 * Get ED treatment symptoms
	 */
	private static function get_ed_treatment_symptoms( $user_id ) {
		$symptoms = array();
		$symptom_selections = get_user_meta( $user_id, 'ennu_ed_treatment_ed_treatment_q1', true );
		
		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name' => $symptom,
					'category' => 'ED Treatment',
					'date' => get_user_meta( $user_id, 'ennu_ed_treatment_score_calculated_at', true ) ?: current_time( 'mysql' )
				);
			}
		}
		
		return $symptoms;
	}

	/**
	 * Get skin symptoms
	 */
	private static function get_skin_symptoms( $user_id ) {
		$symptoms = array();
		$symptom_selections = get_user_meta( $user_id, 'ennu_skin_skin_q1', true );
		
		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name' => $symptom,
					'category' => 'Skin',
					'date' => get_user_meta( $user_id, 'ennu_skin_score_calculated_at', true ) ?: current_time( 'mysql' )
				);
			}
		}
		
		return $symptoms;
	}

	/**
	 * Get hair symptoms
	 */
	private static function get_hair_symptoms( $user_id ) {
		$symptoms = array();
		$symptom_selections = get_user_meta( $user_id, 'ennu_hair_hair_q1', true );
		
		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name' => $symptom,
					'category' => 'Hair',
					'date' => get_user_meta( $user_id, 'ennu_hair_score_calculated_at', true ) ?: current_time( 'mysql' )
				);
			}
		}
		
		return $symptoms;
	}

	/**
	 * Get sleep symptoms
	 */
	private static function get_sleep_symptoms( $user_id ) {
		$symptoms = array();
		$symptom_selections = get_user_meta( $user_id, 'ennu_sleep_sleep_q1', true );
		
		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name' => $symptom,
					'category' => 'Sleep',
					'date' => get_user_meta( $user_id, 'ennu_sleep_score_calculated_at', true ) ?: current_time( 'mysql' )
				);
			}
		}
		
		return $symptoms;
	}

	/**
	 * Get weight loss symptoms and health indicators
	 */
	private static function get_weight_loss_symptoms( $user_id ) {
		$symptoms = array();
		
		// Check for medical conditions that could be symptoms
		$medical_conditions = get_user_meta( $user_id, 'ennu_weight_loss_wl_q9', true );
		if ( is_array( $medical_conditions ) ) {
			foreach ( $medical_conditions as $condition ) {
				$symptoms[] = array(
					'name' => $condition,
					'category' => 'Weight Loss - Medical Condition',
					'date' => get_user_meta( $user_id, 'ennu_weight_loss_score_calculated_at', true ) ?: current_time( 'mysql' )
				);
			}
		}
		
		// Check for low energy levels (could indicate underlying health issues)
		$energy_level = get_user_meta( $user_id, 'ennu_weight_loss_wl_q10', true );
		if ( $energy_level === 'very' || $energy_level === 'somewhat' ) {
			$symptoms[] = array(
				'name' => 'Low Energy Level',
				'category' => 'Weight Loss - Energy',
				'date' => get_user_meta( $user_id, 'ennu_weight_loss_score_calculated_at', true ) ?: current_time( 'mysql' )
			);
		}
		
		// Check for poor sleep quality (could be a symptom)
		$sleep_quality = get_user_meta( $user_id, 'ennu_weight_loss_wl_q5', true );
		if ( $sleep_quality === 'less_than_5' || $sleep_quality === 'poor' ) {
			$symptoms[] = array(
				'name' => 'Poor Sleep Quality',
				'category' => 'Weight Loss - Sleep',
				'date' => get_user_meta( $user_id, 'ennu_weight_loss_score_calculated_at', true ) ?: current_time( 'mysql' )
			);
		}
		
		// Check for high stress levels
		$stress_level = get_user_meta( $user_id, 'ennu_weight_loss_wl_q6', true );
		if ( $stress_level === 'high' || $stress_level === 'very_high' ) {
			$symptoms[] = array(
				'name' => 'High Stress Level',
				'category' => 'Weight Loss - Stress',
				'date' => get_user_meta( $user_id, 'ennu_weight_loss_score_calculated_at', true ) ?: current_time( 'mysql' )
			);
		}
		
		// Check for frequent cravings
		$cravings_frequency = get_user_meta( $user_id, 'ennu_weight_loss_wl_q8', true );
		if ( $cravings_frequency === 'often' || $cravings_frequency === 'very_often' ) {
			$symptoms[] = array(
				'name' => 'Frequent Food Cravings',
				'category' => 'Weight Loss - Cravings',
				'date' => get_user_meta( $user_id, 'ennu_weight_loss_score_calculated_at', true ) ?: current_time( 'mysql' )
			);
		}
		
		return $symptoms;
	}

	/**
	 * Update symptom history
	 */
	private static function update_symptom_history( $user_id, $current_symptoms ) {
		$history = get_user_meta( $user_id, self::SYMPTOM_HISTORY_KEY, true );
		
		if ( ! is_array( $history ) ) {
			$history = array();
		}
		
		// Add current snapshot to history
		$history[] = array(
			'date' => current_time( 'mysql' ),
			'symptoms' => $current_symptoms['symptoms'],
			'total_count' => $current_symptoms['total_count'],
			'assessments' => array_keys( $current_symptoms['by_assessment'] )
		);
		
		// Keep only last 50 entries
		if ( count( $history ) > 50 ) {
			$history = array_slice( $history, -50 );
		}
		
		update_user_meta( $user_id, self::SYMPTOM_HISTORY_KEY, $history );
	}

	/**
	 * Get symptom analytics for a user
	 *
	 * @param int $user_id User ID
	 * @return array Symptom analytics
	 */
	public static function get_symptom_analytics( $user_id ) {
		$symptoms = self::get_centralized_symptoms( $user_id );
		$history = self::get_symptom_history( $user_id, 10 );
		
		$analytics = array(
			'total_symptoms' => $symptoms['total_count'] ?? 0,
			'unique_symptoms' => count( $symptoms['symptoms'] ?? array() ),
			'assessments_with_symptoms' => count( $symptoms['by_assessment'] ?? array() ),
			'most_common_category' => self::get_most_common_category( $symptoms ),
			'most_severe_symptoms' => self::get_most_severe_symptoms( $symptoms ),
			'most_frequent_symptoms' => self::get_most_frequent_symptoms( $symptoms ),
			'symptom_trends' => self::analyze_symptom_trends( $history ),
			'last_updated' => $symptoms['last_updated'] ?? current_time( 'mysql' )
		);
		
		return $analytics;
	}

	/**
	 * Get most common symptom category
	 */
	private static function get_most_common_category( $symptoms ) {
		if ( empty( $symptoms['by_category'] ) ) {
			return null;
		}
		
		$category_counts = array();
		foreach ( $symptoms['by_category'] as $category => $symptom_list ) {
			$category_counts[ $category ] = count( $symptom_list );
		}
		
		arsort( $category_counts );
		return array_keys( $category_counts )[0];
	}

	/**
	 * Get most severe symptoms
	 */
	private static function get_most_severe_symptoms( $symptoms ) {
		$severe_symptoms = array();
		
		if ( ! empty( $symptoms['by_severity'] ) ) {
			$severe_levels = array( 'severe', 'very_severe', 'extreme' );
			foreach ( $severe_levels as $level ) {
				if ( isset( $symptoms['by_severity'][ $level ] ) ) {
					$severe_symptoms = array_merge( $severe_symptoms, $symptoms['by_severity'][ $level ] );
				}
			}
		}
		
		return array_unique( $severe_symptoms );
	}

	/**
	 * Get most frequent symptoms
	 */
	private static function get_most_frequent_symptoms( $symptoms ) {
		$frequent_symptoms = array();
		
		if ( ! empty( $symptoms['by_frequency'] ) ) {
			$frequent_levels = array( 'daily', 'multiple_times_daily', 'constant' );
			foreach ( $frequent_levels as $level ) {
				if ( isset( $symptoms['by_frequency'][ $level ] ) ) {
					$frequent_symptoms = array_merge( $frequent_symptoms, $symptoms['by_frequency'][ $level ] );
				}
			}
		}
		
		return array_unique( $frequent_symptoms );
	}

	/**
	 * Analyze symptom trends over time
	 */
	private static function analyze_symptom_trends( $history ) {
		if ( count( $history ) < 2 ) {
			return array( 'trend' => 'insufficient_data' );
		}
		
		$recent = $history[0]['total_count'];
		$previous = $history[1]['total_count'];
		
		if ( $recent > $previous ) {
			return array( 'trend' => 'increasing', 'change' => $recent - $previous );
		} elseif ( $recent < $previous ) {
			return array( 'trend' => 'decreasing', 'change' => $previous - $recent );
		} else {
			return array( 'trend' => 'stable', 'change' => 0 );
		}
	}

	/**
	 * Hook into assessment completion to auto-update centralized symptoms
	 */
	public static function hook_into_assessment_completion() {
		add_action( 'ennu_assessment_completed', array( __CLASS__, 'on_assessment_completed' ), 10, 2 );
	}

	/**
	 * Handle assessment completion
	 */
	public static function on_assessment_completed( $user_id, $assessment_type ) {
		self::update_centralized_symptoms( $user_id, $assessment_type );
	}
	
	/**
	 * Manually update symptoms for testing
	 */
	public static function force_update_symptoms( $user_id ) {
		return self::update_centralized_symptoms( $user_id );
	}
}

// Initialize hooks
ENNU_Centralized_Symptoms_Manager::hook_into_assessment_completion(); 