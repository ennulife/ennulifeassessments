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
	 * Duration constants (in days)
	 */
	const DURATION_MILD = 14;      // Mild symptoms expire in 14 days
	const DURATION_MODERATE = 30;  // Moderate symptoms expire in 30 days
	const DURATION_SEVERE = 60;    // Severe symptoms expire in 60 days
	const DURATION_CRITICAL = 90;  // Critical symptoms expire in 90 days
	const DURATION_DEFAULT = 30;   // Default duration for unknown severity

	/**
	 * Update centralized symptoms for a user
	 *
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type (optional - if provided, only update from this assessment)
	 * @return bool Success status
	 */
	public static function update_centralized_symptoms( $user_id, $assessment_type = null ) {
		try {
			error_log( "ENNU Centralized Symptoms: Starting update for user {$user_id}, assessment_type: " . ($assessment_type ?: 'all') );
			
			$all_symptoms = self::aggregate_all_symptoms( $user_id, $assessment_type );
			error_log( "ENNU Centralized Symptoms: Aggregated symptoms for user {$user_id}: " . print_r( $all_symptoms, true ) );

			// Clean expired symptoms before saving
			$all_symptoms = self::clean_expired_symptoms( $all_symptoms );

			// Save centralized symptoms
			update_user_meta( $user_id, self::CENTRALIZED_SYMPTOMS_KEY, $all_symptoms );
			error_log( "ENNU Centralized Symptoms: Saved symptoms to database for user {$user_id}" );

			// Update symptom history
			self::update_symptom_history( $user_id, $all_symptoms );

			// Auto-flag biomarkers based on symptoms
			if ( ! empty( $all_symptoms['symptoms'] ) ) {
				$symptoms_list = array();
				foreach ( $all_symptoms['symptoms'] as $symptom_data ) {
					if ( is_array( $symptom_data ) && isset( $symptom_data['name'] ) ) {
						$symptoms_list[] = $symptom_data;
					}
				}
				
				if ( ! empty( $symptoms_list ) ) {
					$flags_created = self::auto_flag_biomarkers_from_symptoms( $user_id, $symptoms_list );
					if ( $flags_created > 0 ) {
						error_log( "ENNU Centralized Symptoms: Created {$flags_created} biomarker flags for user {$user_id}" );
					}
				}
			}

			// Clear any caches
			wp_cache_delete( $user_id, 'user_meta' );

			error_log( "ENNU Centralized Symptoms: Update completed successfully for user {$user_id}" );
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

		// Clean expired symptoms before returning
		$symptoms = self::clean_expired_symptoms( $symptoms );

		return is_array( $symptoms ) ? $symptoms : array();
	}

	/**
	 * Clean expired symptoms from the symptoms array
	 *
	 * @param array $symptoms Symptoms array
	 * @return array Cleaned symptoms array
	 */
	private static function clean_expired_symptoms( $symptoms ) {
		if ( empty( $symptoms ) || ! is_array( $symptoms ) ) {
			return array();
		}

		$cleaned_symptoms = $symptoms;
		$current_time = current_time( 'timestamp' );

		// Clean expired symptoms from main symptoms array
		if ( ! empty( $symptoms['symptoms'] ) ) {
			foreach ( $symptoms['symptoms'] as $symptom_key => $symptom_data ) {
				if ( self::is_symptom_expired( $symptom_data, $current_time ) ) {
					unset( $cleaned_symptoms['symptoms'][ $symptom_key ] );
				}
			}
		}

		// Clean expired symptoms from categorized arrays
		$categorized_arrays = array( 'by_category', 'by_severity', 'by_frequency' );
		foreach ( $categorized_arrays as $array_type ) {
			if ( ! empty( $symptoms[ $array_type ] ) ) {
				foreach ( $symptoms[ $array_type ] as $category => $symptom_list ) {
					$cleaned_symptoms[ $array_type ][ $category ] = array();
					foreach ( $symptom_list as $symptom ) {
						if ( ! self::is_symptom_expired( $symptom, $current_time ) ) {
							$cleaned_symptoms[ $array_type ][ $category ][] = $symptom;
						}
					}
				}
			}
		}

		// Update counts after cleaning
		$cleaned_symptoms['total_count'] = count( $cleaned_symptoms['symptoms'] );
		$cleaned_symptoms['last_updated'] = current_time( 'mysql' );

		return $cleaned_symptoms;
	}

	/**
	 * Check if a symptom is expired (MODIFIED: Symptoms only expire when biomarkers are unflagged)
	 *
	 * @param array $symptom_data Symptom data
	 * @param int $current_time Current timestamp
	 * @return bool True if expired
	 */
	private static function is_symptom_expired( $symptom_data, $current_time = null ) {
		// NEW LOGIC: Symptoms should NOT automatically expire
		// They should only move to history when biomarkers are unflagged by admin
		return false;
	}

	/**
	 * Move symptoms to history when biomarkers are unflagged by admin
	 *
	 * @param int $user_id User ID
	 * @param string $biomarker_name Biomarker that was unflagged
	 * @return int Number of symptoms moved to history
	 */
	public static function move_symptoms_to_history_on_unflag( $user_id, $biomarker_name ) {
		$symptoms_moved = 0;
		
		// Get current centralized symptoms
		$centralized_symptoms = self::get_centralized_symptoms( $user_id );
		$current_symptoms = $centralized_symptoms['symptoms'] ?? array();
		
		// Get symptom to biomarker mapping (reverse lookup)
		$symptom_biomarker_mapping = array(
			'fatigue' => array( 'vitamin_d', 'vitamin_b12', 'ferritin', 'tsh', 'cortisol' ),
			'low_libido' => array( 'testosterone_total', 'testosterone_free', 'estradiol', 'prolactin' ),
			'mood_swings' => array( 'cortisol', 'estradiol', 'progesterone', 'thyroid_tsh' ),
			'brain_fog' => array( 'vitamin_d', 'vitamin_b12', 'omega_3', 'magnesium', 'homocysteine' ),
			'anxiety' => array( 'cortisol', 'magnesium', 'vitamin_d', 'thyroid_tsh' ),
			'depression' => array( 'vitamin_d', 'vitamin_b12', 'omega_3', 'cortisol', 'serotonin' ),
			'insomnia' => array( 'melatonin', 'cortisol', 'magnesium', 'thyroid_tsh' ),
			'hot_flashes' => array( 'estradiol', 'fsh', 'lh', 'progesterone' ),
			'night_sweats' => array( 'estradiol', 'cortisol', 'thyroid_tsh', 'progesterone' ),
			'acne' => array( 'testosterone_total', 'estradiol', 'insulin', 'cortisol' ),
			'diabetes' => array( 'glucose', 'hba1c', 'insulin', 'homa_ir' ),
			'high_blood_pressure' => array( 'sodium', 'potassium', 'aldosterone', 'cortisol' ),
			'thyroid_issues' => array( 'tsh', 't3', 't4', 'thyroid_antibodies' ),
			'weight_gain' => array( 'insulin', 'cortisol', 'thyroid_tsh', 'testosterone_total' ),
			'weight_loss' => array( 'thyroid_tsh', 'cortisol', 'insulin', 'glucose' ),
			'irritability' => array( 'cortisol', 'estradiol', 'progesterone', 'thyroid_tsh' ),
			'headaches' => array( 'magnesium', 'vitamin_d', 'cortisol', 'estradiol' ),
			'joint_pain' => array( 'vitamin_d', 'omega_3', 'cortisol', 'estradiol' ),
			'muscle_weakness' => array( 'testosterone_total', 'vitamin_d', 'magnesium', 'cortisol' ),
			'frequent_illness' => array( 'vitamin_d', 'vitamin_c', 'zinc', 'wbc' ),
			'slow_healing' => array( 'vitamin_d', 'zinc', 'vitamin_c', 'glucose' )
		);
		
		// Find symptoms that are associated with this biomarker
		$symptoms_to_move = array();
		$remaining_symptoms = array();
		
		foreach ( $current_symptoms as $symptom_data ) {
			if ( ! is_array( $symptom_data ) || ! isset( $symptom_data['name'] ) ) {
				$remaining_symptoms[] = $symptom_data;
				continue;
			}
			
			$symptom_name = $symptom_data['name'];
			$symptom_key = strtolower( str_replace( array( ' ', '-', '_' ), '_', $symptom_name ) );
			$symptom_key = preg_replace( '/[^a-z0-9_]/', '', $symptom_key );
			
			// Check if this symptom is associated with the unflagged biomarker
			$should_move = false;
			if ( isset( $symptom_biomarker_mapping[$symptom_key] ) ) {
				$associated_biomarkers = $symptom_biomarker_mapping[$symptom_key];
				if ( in_array( $biomarker_name, $associated_biomarkers ) ) {
					// Check if ALL associated biomarkers are unflagged
					$flag_manager = new ENNU_Biomarker_Flag_Manager();
					$all_unflagged = true;
					
					foreach ( $associated_biomarkers as $associated_biomarker ) {
						$flags = $flag_manager->get_biomarker_flags( $user_id, $associated_biomarker );
						if ( ! empty( $flags ) ) {
							// Check if any flags are still active
							foreach ( $flags as $flag ) {
								if ( isset( $flag['status'] ) && $flag['status'] === 'active' ) {
									$all_unflagged = false;
									break 2;
								}
							}
						}
					}
					
					if ( $all_unflagged ) {
						$should_move = true;
					}
				}
			}
			
			if ( $should_move ) {
				$symptoms_to_move[] = $symptom_data;
				$symptoms_moved++;
			} else {
				$remaining_symptoms[] = $symptom_data;
			}
		}
		
		// Move symptoms to history
		if ( ! empty( $symptoms_to_move ) ) {
			$symptom_history = get_user_meta( $user_id, self::SYMPTOM_HISTORY_KEY, true );
			if ( ! is_array( $symptom_history ) ) {
				$symptom_history = array();
			}
			
			// Add symptoms to history with unflag reason
			$history_entry = array(
				'date' => current_time( 'mysql' ),
				'symptoms' => $symptoms_to_move,
				'reason' => "Moved to history: Biomarker '{$biomarker_name}' unflagged by admin",
				'action' => 'admin_unflag'
			);
			
			$symptom_history[] = $history_entry;
			update_user_meta( $user_id, self::SYMPTOM_HISTORY_KEY, $symptom_history );
			
			// Update centralized symptoms with remaining symptoms
			$centralized_symptoms['symptoms'] = $remaining_symptoms;
			$centralized_symptoms['total_count'] = count( $remaining_symptoms );
			$centralized_symptoms['last_updated'] = current_time( 'mysql' );
			
			update_user_meta( $user_id, self::CENTRALIZED_SYMPTOMS_KEY, $centralized_symptoms );
			
			error_log( "ENNU Centralized Symptoms: Moved {$symptoms_moved} symptoms to history for user {$user_id} due to biomarker '{$biomarker_name}' being unflagged" );
		}
		
		return $symptoms_moved;
	}

	/**
	 * Get duration in days for a given severity level
	 *
	 * @param string $severity Symptom severity
	 * @return int Duration in days
	 */
	private static function get_duration_for_severity( $severity ) {
		// Ensure severity is a string
		if ( ! is_string( $severity ) ) {
			$severity = 'moderate'; // Default fallback
		}
		
		// Additional safety check for arrays
		if ( is_array( $severity ) ) {
			$severity = 'moderate';
		}
		
		switch ( strtolower( $severity ) ) {
			case 'mild':
			case 'slight':
				return self::DURATION_MILD;
			case 'moderate':
			case 'medium':
				return self::DURATION_MODERATE;
			case 'severe':
			case 'very_severe':
				return self::DURATION_SEVERE;
			case 'extreme':
			case 'critical':
				return self::DURATION_CRITICAL;
			default:
				return self::DURATION_DEFAULT;
		}
	}

	/**
	 * Get symptom duration info for display
	 *
	 * @param array $symptom_data Symptom data
	 * @return array Duration information
	 */
	public static function get_symptom_duration_info( $symptom_data ) {
		$current_time = time();
		$last_reported = isset( $symptom_data['last_reported'] ) ? strtotime( $symptom_data['last_reported'] ) : $current_time;
		$severity = isset( $symptom_data['severity'] ) ? $symptom_data['severity'] : 'moderate';
		
		// Ensure severity is a string
		if ( ! is_string( $severity ) ) {
			$severity = 'moderate';
		}
		
		// Additional safety check for arrays
		if ( is_array( $severity ) ) {
			$severity = 'moderate';
		}
		
		$duration_days = self::get_duration_for_severity( $severity );
		$expiration_time = $last_reported + ( $duration_days * 24 * 60 * 60 );
		$days_remaining = max( 0, ceil( ( $expiration_time - $current_time ) / ( 24 * 60 * 60 ) ) );
		
		if ( $current_time > $expiration_time ) {
			return array(
				'status' => 'expired',
				'days_remaining' => 0,
				'expiration_date' => date( 'Y-m-d H:i:s', $expiration_time )
			);
		} else {
			return array(
				'status' => 'active',
				'days_remaining' => $days_remaining,
				'expiration_date' => date( 'Y-m-d H:i:s', $expiration_time )
			);
		}
	}

	/**
	 * Automatically flag biomarkers based on reported symptoms
	 *
	 * @param int $user_id User ID
	 * @param array $symptoms Array of symptoms
	 * @return int Number of flags created
	 */
	public static function auto_flag_biomarkers_from_symptoms( $user_id, $symptoms ) {
		error_log( "ENNU Centralized Symptoms: Starting auto_flag_biomarkers_from_symptoms for user {$user_id} with " . count($symptoms) . " symptoms" );
		
		if ( empty( $symptoms ) ) {
			error_log( "ENNU Centralized Symptoms: No symptoms provided for biomarker flagging" );
			return 0;
		}

		// Symptom to biomarker mapping
		$symptom_biomarker_mapping = array(
			'fatigue' => array( 'vitamin_d', 'vitamin_b12', 'ferritin', 'tsh', 'cortisol' ),
			'low_libido' => array( 'testosterone_total', 'testosterone_free', 'estradiol', 'prolactin' ),
			'mood_swings' => array( 'cortisol', 'estradiol', 'progesterone', 'thyroid_tsh' ),
			'brain_fog' => array( 'vitamin_d', 'vitamin_b12', 'omega_3', 'magnesium', 'homocysteine' ),
			'anxiety' => array( 'cortisol', 'magnesium', 'vitamin_d', 'thyroid_tsh' ),
			'depression' => array( 'vitamin_d', 'vitamin_b12', 'omega_3', 'cortisol', 'serotonin' ),
			'insomnia' => array( 'melatonin', 'cortisol', 'magnesium', 'thyroid_tsh' ),
			'hot_flashes' => array( 'estradiol', 'fsh', 'lh', 'progesterone' ),
			'night_sweats' => array( 'estradiol', 'cortisol', 'thyroid_tsh', 'progesterone' ),
			'acne' => array( 'testosterone_total', 'estradiol', 'insulin', 'cortisol' ),
			'diabetes' => array( 'glucose', 'hba1c', 'insulin', 'homa_ir' ),
			'high_blood_pressure' => array( 'sodium', 'potassium', 'aldosterone', 'cortisol' ),
			'thyroid_issues' => array( 'tsh', 't3', 't4', 'thyroid_antibodies' ),
			'weight_gain' => array( 'insulin', 'cortisol', 'thyroid_tsh', 'testosterone_total' ),
			'weight_loss' => array( 'thyroid_tsh', 'cortisol', 'insulin', 'glucose' ),
			'irritability' => array( 'cortisol', 'estradiol', 'progesterone', 'thyroid_tsh' ),
			'headaches' => array( 'magnesium', 'vitamin_d', 'cortisol', 'estradiol' ),
			'joint_pain' => array( 'vitamin_d', 'omega_3', 'cortisol', 'estradiol' ),
			'muscle_weakness' => array( 'testosterone_total', 'vitamin_d', 'magnesium', 'cortisol' ),
			'frequent_illness' => array( 'vitamin_d', 'vitamin_c', 'zinc', 'wbc' ),
			'slow_healing' => array( 'vitamin_d', 'zinc', 'vitamin_c', 'glucose' ),
			// Add weight loss specific symptoms
			'thyroid' => array( 'tsh', 't3', 't4', 'thyroid_antibodies' ),
			'insulin_resistance' => array( 'insulin', 'glucose', 'hba1c', 'homa_ir' ),
			'low_energy_level' => array( 'vitamin_d', 'vitamin_b12', 'ferritin', 'tsh', 'cortisol' ),
			'poor_sleep_quality' => array( 'melatonin', 'cortisol', 'magnesium', 'thyroid_tsh' ),
			'high_stress_level' => array( 'cortisol', 'magnesium', 'vitamin_d', 'thyroid_tsh' ),
			'frequent_food_cravings' => array( 'insulin', 'glucose', 'cortisol', 'serotonin' )
		);

		$flag_manager = new ENNU_Biomarker_Flag_Manager();
		$flags_created = 0;

		foreach ( $symptoms as $symptom_data ) {
			$symptom_name = is_array( $symptom_data ) ? $symptom_data['name'] : $symptom_data;
			$symptom_key = strtolower( str_replace( array( ' ', '-', '_' ), '_', $symptom_name ) );
			
			// Clean up the symptom key
			$symptom_key = preg_replace( '/[^a-z0-9_]/', '', $symptom_key );
			
			error_log( "ENNU Centralized Symptoms: Processing symptom '{$symptom_name}' with key '{$symptom_key}'" );
			
			if ( isset( $symptom_biomarker_mapping[$symptom_key] ) ) {
				$biomarkers = $symptom_biomarker_mapping[$symptom_key];
				error_log( "ENNU Centralized Symptoms: Found biomarkers for symptom '{$symptom_name}': " . implode(', ', $biomarkers) );
				
				foreach ( $biomarkers as $biomarker ) {
					$reason = "Flagged due to reported symptom: {$symptom_name}";
					$success = $flag_manager->flag_biomarker( $user_id, $biomarker, 'symptom_triggered', $reason, 'system' );
					
					if ( $success ) {
						$flags_created++;
						error_log( "ENNU Centralized Symptoms: Created flag for biomarker '{$biomarker}' due to symptom '{$symptom_name}'" );
					} else {
						error_log( "ENNU Centralized Symptoms: Failed to create flag for biomarker '{$biomarker}' due to symptom '{$symptom_name}'" );
					}
				}
			} else {
				error_log( "ENNU Centralized Symptoms: No biomarker mapping found for symptom '{$symptom_name}' with key '{$symptom_key}'" );
			}
		}

		error_log( "ENNU Centralized Symptoms: Auto-flagging complete. Created {$flags_created} biomarker flags for user {$user_id}" );
		return $flags_created;
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
		usort(
			$history,
			function( $a, $b ) {
				return strtotime( $b['date'] ) - strtotime( $a['date'] );
			}
		);

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
		error_log( "ENNU Centralized Symptoms: Starting aggregate_all_symptoms for user {$user_id}, assessment_type: " . ($assessment_type ?: 'all') );
		
		$all_symptoms = array(
			'symptoms'      => array(),
			'by_assessment' => array(),
			'by_category'   => array(),
			'by_severity'   => array(),
			'by_frequency'  => array(),
			'total_count'   => 0,
			'last_updated'  => current_time( 'mysql' ),
			'user_id'       => $user_id,
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
			'weight_loss',
		);

		foreach ( $assessment_types as $type ) {
			error_log( "ENNU Centralized Symptoms: Processing assessment type: {$type}" );
			$assessment_symptoms = self::get_assessment_symptoms( $user_id, $type );
			error_log( "ENNU Centralized Symptoms: Found " . count($assessment_symptoms) . " symptoms for {$type}" );

			if ( ! empty( $assessment_symptoms ) ) {
				$all_symptoms['by_assessment'][ $type ] = $assessment_symptoms;

				// Aggregate individual symptoms
				foreach ( $assessment_symptoms as $symptom ) {
					// Ensure symptom name is a string
					if ( ! isset( $symptom['name'] ) || ! is_string( $symptom['name'] ) ) {
						continue;
					}
					$symptom_key = $symptom['name'];

					// Add to main symptoms array
					if ( ! isset( $all_symptoms['symptoms'][ $symptom_key ] ) ) {
						$all_symptoms['symptoms'][ $symptom_key ] = array(
							'name'             => $symptom['name'],
							'category'         => $symptom['category'],
							'assessments'      => array(),
							'severity'         => array(),
							'frequency'        => array(),
							'first_reported'   => $symptom['date'],
							'last_reported'    => $symptom['date'],
							'occurrence_count' => 0,
						);
					}

					// Update symptom data
					$all_symptoms['symptoms'][ $symptom_key ]['assessments'][] = $type;
					$all_symptoms['symptoms'][ $symptom_key ]['assessments']   = array_unique( $all_symptoms['symptoms'][ $symptom_key ]['assessments'] );

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

		error_log( "ENNU Centralized Symptoms: Aggregation complete. Total symptoms: {$all_symptoms['total_count']}" );
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
		error_log( "ENNU Centralized Symptoms: Getting symptoms for assessment type: {$assessment_type}, user: {$user_id}" );
		
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

		error_log( "ENNU Centralized Symptoms: Found " . count($symptoms) . " symptoms for {$assessment_type}" );
		return $symptoms;
	}

	/**
	 * Get health optimization symptoms
	 */
	private static function get_health_optimization_symptoms( $user_id ) {
		$symptoms          = array();
		$symptom_questions = array( 'symptom_q1', 'symptom_q2', 'symptom_q3', 'symptom_q4', 'symptom_q5' );

		foreach ( $symptom_questions as $q_id ) {
			$symptom_value = get_user_meta( $user_id, 'ennu_health_optimization_assessment_' . $q_id, true );
			$severity_key  = str_replace( '_q', '_severity_q', $q_id );
			$frequency_key = str_replace( '_q', '_frequency_q', $q_id );

			if ( ! empty( $symptom_value ) ) {
				$symptoms[] = array(
					'name'      => $symptom_value,
					'category'  => 'Health Optimization',
					'severity'  => get_user_meta( $user_id, 'ennu_health_optimization_assessment_' . $severity_key, true ),
					'frequency' => get_user_meta( $user_id, 'ennu_health_optimization_assessment_' . $frequency_key, true ),
					'date'      => get_user_meta( $user_id, 'ennu_health_optimization_assessment_score_calculated_at', true ) ?: current_time( 'mysql' ),
				);
			}
		}

		return $symptoms;
	}

	/**
	 * Get hormone symptoms
	 */
	private static function get_hormone_symptoms( $user_id ) {
		$symptoms           = array();
		$symptom_selections = get_user_meta( $user_id, 'ennu_hormone_hormone_q1', true );

		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name'     => $symptom,
					'category' => 'Hormone',
					'date'     => get_user_meta( $user_id, 'ennu_hormone_score_calculated_at', true ) ?: current_time( 'mysql' ),
				);
			}
		}

		return $symptoms;
	}

	/**
	 * Get testosterone symptoms
	 */
	private static function get_testosterone_symptoms( $user_id ) {
		$symptoms           = array();
		$symptom_selections = get_user_meta( $user_id, 'ennu_testosterone_testosterone_q1', true );

		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name'     => $symptom,
					'category' => 'Testosterone',
					'date'     => get_user_meta( $user_id, 'ennu_testosterone_score_calculated_at', true ) ?: current_time( 'mysql' ),
				);
			}
		}

		return $symptoms;
	}

	/**
	 * Get menopause symptoms
	 */
	private static function get_menopause_symptoms( $user_id ) {
		$symptoms           = array();
		$symptom_selections = get_user_meta( $user_id, 'ennu_menopause_menopause_q1', true );

		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name'     => $symptom,
					'category' => 'Menopause',
					'date'     => get_user_meta( $user_id, 'ennu_menopause_score_calculated_at', true ) ?: current_time( 'mysql' ),
				);
			}
		}

		return $symptoms;
	}

	/**
	 * Get ED treatment symptoms
	 */
	private static function get_ed_treatment_symptoms( $user_id ) {
		$symptoms           = array();
		$symptom_selections = get_user_meta( $user_id, 'ennu_ed_treatment_ed_treatment_q1', true );

		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name'     => $symptom,
					'category' => 'ED Treatment',
					'date'     => get_user_meta( $user_id, 'ennu_ed_treatment_score_calculated_at', true ) ?: current_time( 'mysql' ),
				);
			}
		}

		return $symptoms;
	}

	/**
	 * Get skin symptoms
	 */
	private static function get_skin_symptoms( $user_id ) {
		$symptoms           = array();
		$symptom_selections = get_user_meta( $user_id, 'ennu_skin_skin_q1', true );

		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name'     => $symptom,
					'category' => 'Skin',
					'date'     => get_user_meta( $user_id, 'ennu_skin_score_calculated_at', true ) ?: current_time( 'mysql' ),
				);
			}
		}

		return $symptoms;
	}

	/**
	 * Get hair symptoms
	 */
	private static function get_hair_symptoms( $user_id ) {
		$symptoms           = array();
		$symptom_selections = get_user_meta( $user_id, 'ennu_hair_hair_q1', true );

		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name'     => $symptom,
					'category' => 'Hair',
					'date'     => get_user_meta( $user_id, 'ennu_hair_score_calculated_at', true ) ?: current_time( 'mysql' ),
				);
			}
		}

		return $symptoms;
	}

	/**
	 * Get sleep symptoms
	 */
	private static function get_sleep_symptoms( $user_id ) {
		$symptoms           = array();
		$symptom_selections = get_user_meta( $user_id, 'ennu_sleep_sleep_q1', true );

		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name'     => $symptom,
					'category' => 'Sleep',
					'date'     => get_user_meta( $user_id, 'ennu_sleep_score_calculated_at', true ) ?: current_time( 'mysql' ),
				);
			}
		}

		return $symptoms;
	}

	/**
	 * Get weight loss symptoms and health indicators
	 */
	private static function get_weight_loss_symptoms( $user_id ) {
		error_log( "ENNU Centralized Symptoms: Getting weight loss symptoms for user {$user_id}" );
		$symptoms = array();

		// Check for medical conditions that could be symptoms
		$medical_conditions = get_user_meta( $user_id, 'ennu_weight-loss_wl_q9', true );
		error_log( "ENNU Centralized Symptoms: Medical conditions for user {$user_id}: " . print_r( $medical_conditions, true ) );
		if ( is_array( $medical_conditions ) ) {
			foreach ( $medical_conditions as $condition ) {
				$symptoms[] = array(
					'name'     => $condition,
					'category' => 'Weight Loss - Medical Condition',
					'date'     => get_user_meta( $user_id, 'ennu_weight-loss_score_calculated_at', true ) ?: current_time( 'mysql' ),
				);
			}
		}

		// Check for low energy levels (could indicate underlying health issues)
		$energy_level = get_user_meta( $user_id, 'ennu_weight-loss_wl_q10', true );
		error_log( "ENNU Centralized Symptoms: Energy level for user {$user_id}: {$energy_level}" );
		if ( $energy_level === 'very' || $energy_level === 'somewhat' ) {
			$symptoms[] = array(
				'name'     => 'Low Energy Level',
				'category' => 'Weight Loss - Energy',
				'date'     => get_user_meta( $user_id, 'ennu_weight-loss_score_calculated_at', true ) ?: current_time( 'mysql' ),
			);
		}

		// Check for poor sleep quality (could be a symptom)
		$sleep_quality = get_user_meta( $user_id, 'ennu_weight-loss_wl_q5', true );
		error_log( "ENNU Centralized Symptoms: Sleep quality for user {$user_id}: {$sleep_quality}" );
		if ( $sleep_quality === 'less_than_5' || $sleep_quality === 'poor' ) {
			$symptoms[] = array(
				'name'     => 'Poor Sleep Quality',
				'category' => 'Weight Loss - Sleep',
				'date'     => get_user_meta( $user_id, 'ennu_weight-loss_score_calculated_at', true ) ?: current_time( 'mysql' ),
			);
		}

		// Check for high stress levels
		$stress_level = get_user_meta( $user_id, 'ennu_weight-loss_wl_q6', true );
		error_log( "ENNU Centralized Symptoms: Stress level for user {$user_id}: {$stress_level}" );
		if ( $stress_level === 'high' || $stress_level === 'very_high' ) {
			$symptoms[] = array(
				'name'     => 'High Stress Level',
				'category' => 'Weight Loss - Stress',
				'date'     => get_user_meta( $user_id, 'ennu_weight-loss_score_calculated_at', true ) ?: current_time( 'mysql' ),
			);
		}

		// Check for frequent cravings
		$cravings_frequency = get_user_meta( $user_id, 'ennu_weight-loss_wl_q8', true );
		error_log( "ENNU Centralized Symptoms: Cravings frequency for user {$user_id}: {$cravings_frequency}" );
		if ( $cravings_frequency === 'often' || $cravings_frequency === 'very_often' ) {
			$symptoms[] = array(
				'name'     => 'Frequent Food Cravings',
				'category' => 'Weight Loss - Cravings',
				'date'     => get_user_meta( $user_id, 'ennu_weight-loss_score_calculated_at', true ) ?: current_time( 'mysql' ),
			);
		}

		error_log( "ENNU Centralized Symptoms: Extracted " . count($symptoms) . " weight loss symptoms for user {$user_id}" );
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
			'date'        => current_time( 'mysql' ),
			'symptoms'    => $current_symptoms['symptoms'],
			'total_count' => $current_symptoms['total_count'],
			'assessments' => array_keys( $current_symptoms['by_assessment'] ),
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
		$history  = self::get_symptom_history( $user_id, 10 );

		$analytics = array(
			'total_symptoms'            => $symptoms['total_count'] ?? 0,
			'unique_symptoms'           => count( $symptoms['symptoms'] ?? array() ),
			'assessments_with_symptoms' => count( $symptoms['by_assessment'] ?? array() ),
			'most_common_category'      => self::get_most_common_category( $symptoms ),
			'most_severe_symptoms'      => self::get_most_severe_symptoms( $symptoms ),
			'most_frequent_symptoms'    => self::get_most_frequent_symptoms( $symptoms ),
			'symptom_trends'            => self::analyze_symptom_trends( $history ),
			'last_updated'              => $symptoms['last_updated'] ?? current_time( 'mysql' ),
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
					// Ensure we only merge string values
					$level_symptoms = $symptoms['by_severity'][ $level ];
					if ( is_array( $level_symptoms ) ) {
						foreach ( $level_symptoms as $symptom ) {
							if ( is_string( $symptom ) ) {
								$severe_symptoms[] = $symptom;
							}
						}
					}
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
					// Ensure we only merge string values
					$level_symptoms = $symptoms['by_frequency'][ $level ];
					if ( is_array( $level_symptoms ) ) {
						foreach ( $level_symptoms as $symptom ) {
							if ( is_string( $symptom ) ) {
								$frequent_symptoms[] = $symptom;
							}
						}
					}
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

		$recent   = $history[0]['total_count'];
		$previous = $history[1]['total_count'];

		if ( $recent > $previous ) {
			return array(
				'trend'  => 'increasing',
				'change' => $recent - $previous,
			);
		} elseif ( $recent < $previous ) {
			return array(
				'trend'  => 'decreasing',
				'change' => $previous - $recent,
			);
		} else {
			return array(
				'trend'  => 'stable',
				'change' => 0,
			);
		}
	}

	/**
	 * Hook into assessment completion and other events
	 */
	public static function hook_into_assessment_completion() {
		add_action( 'ennu_assessment_completed', array( __CLASS__, 'on_assessment_completed' ), 10, 2 );
		add_action( 'ennu_biomarker_flag_removed', array( __CLASS__, 'on_biomarker_flag_removed' ), 10, 3 );
	}

	/**
	 * Handle assessment completion
	 *
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 */
	public static function on_assessment_completed( $user_id, $assessment_type ) {
		error_log( "ENNU Centralized Symptoms: Assessment completed for user {$user_id}, type: {$assessment_type}" );
		$result = self::update_centralized_symptoms( $user_id, $assessment_type );
		error_log( "ENNU Centralized Symptoms: Update result for user {$user_id}: " . print_r( $result, true ) );
	}

	/**
	 * Handle biomarker flag removal - move associated symptoms to history
	 *
	 * @param int $user_id User ID
	 * @param string $biomarker_name Biomarker name
	 * @param string $removal_reason Reason for removal
	 */
	public static function on_biomarker_flag_removed( $user_id, $biomarker_name, $removal_reason ) {
		$symptoms_moved = self::move_symptoms_to_history_on_unflag( $user_id, $biomarker_name );
		
		if ( $symptoms_moved > 0 ) {
			error_log( "ENNU Centralized Symptoms: Moved {$symptoms_moved} symptoms to history for user {$user_id} when biomarker '{$biomarker_name}' was unflagged" );
		}
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
