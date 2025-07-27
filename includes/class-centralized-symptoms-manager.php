<?php
/**
 * ENNU Life Centralized Symptoms Manager
 *
 * Manages centralized storage and retrieval of all user symptoms across all assessments.
 * Symptoms are ONE LOG that only gets removed when users take assessments and answer
 * questions in a way that no longer triggers them.
 *
 * @package ENNU_Life
 * @version 64.2.1
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
	 * Symptom triggers meta key - tracks what triggered each symptom
	 */
	const SYMPTOM_TRIGGERS_KEY = 'ennu_symptom_triggers';

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
			
			// Get current symptoms
			$current_symptoms = self::get_centralized_symptoms( $user_id );
			
			// Get new symptoms from assessments
			$new_symptoms = self::aggregate_all_symptoms( $user_id, $assessment_type );
			
			// Merge symptoms using proper logic
			$merged_symptoms = self::merge_symptoms_with_logic( $current_symptoms, $new_symptoms, $user_id, $assessment_type );
			
			// Save centralized symptoms
			update_user_meta( $user_id, self::CENTRALIZED_SYMPTOMS_KEY, $merged_symptoms );
			error_log( "ENNU Centralized Symptoms: Saved symptoms to database for user {$user_id}" );

			// Auto-flag biomarkers based on symptoms
			if ( ! empty( $merged_symptoms['symptoms'] ) ) {
				$symptoms_list = array();
				foreach ( $merged_symptoms['symptoms'] as $symptom_data ) {
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
		// Use batch retrieval for better performance
		$db_optimizer = ENNU_Database_Optimizer::get_instance();
		$meta_data = $db_optimizer->get_user_meta_batch( $user_id, array(
			self::CENTRALIZED_SYMPTOMS_KEY,
			self::SYMPTOM_TRIGGERS_KEY
		) );

		$symptoms = $meta_data[ self::CENTRALIZED_SYMPTOMS_KEY ] ?? array();

		if ( empty( $symptoms ) || ! is_array( $symptoms ) ) {
			// If no centralized data exists, return empty structure to prevent infinite loop
			$symptoms = array(
				'symptoms' => array(),
				'by_assessment' => array(),
				'by_category' => array(),
				'by_severity' => array(),
				'by_frequency' => array(),
				'total_count' => 0,
				'last_updated' => current_time( 'mysql' ),
				'user_id' => $user_id,
			);
		}

		return is_array( $symptoms ) ? $symptoms : array();
	}

	/**
	 * Merge symptoms using proper logic - symptoms are ONE LOG
	 *
	 * @param array $current_symptoms Current symptoms
	 * @param array $new_symptoms New symptoms from assessment
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @return array Merged symptoms
	 */
	private static function merge_symptoms_with_logic( $current_symptoms, $new_symptoms, $user_id, $assessment_type ) {
		$merged_symptoms = array(
			'symptoms'      => array(),
			'by_assessment' => array(),
			'by_category'   => array(),
			'by_severity'   => array(),
			'by_frequency'  => array(),
			'total_count'   => 0,
			'last_updated'  => current_time( 'mysql' ),
			'user_id'       => $user_id,
		);

		// Get symptom triggers to understand what caused each symptom
		$db_optimizer = ENNU_Database_Optimizer::get_instance();
		$meta_data = $db_optimizer->get_user_meta_batch( $user_id, array(
			self::SYMPTOM_TRIGGERS_KEY
		) );
		
		$symptom_triggers = $meta_data[ self::SYMPTOM_TRIGGERS_KEY ] ?? array();
		if ( ! is_array( $symptom_triggers ) ) {
			$symptom_triggers = array();
		}

		// Process current symptoms - keep them unless explicitly resolved
		if ( ! empty( $current_symptoms['symptoms'] ) ) {
			foreach ( $current_symptoms['symptoms'] as $symptom_key => $symptom_data ) {
				// Check if this symptom should be resolved based on new assessment data
				if ( self::should_resolve_symptom( $symptom_key, $symptom_data, $user_id, $assessment_type ) ) {
					error_log( "ENNU Centralized Symptoms: Resolving symptom '{$symptom_key}' for user {$user_id} based on assessment {$assessment_type}" );
					continue; // Skip this symptom - it's resolved
				}

				// Keep the symptom
				$merged_symptoms['symptoms'][ $symptom_key ] = $symptom_data;
				$merged_symptoms['total_count']++;
			}
		}

		// Process new symptoms - add them if they don't already exist
		if ( ! empty( $new_symptoms['symptoms'] ) ) {
			foreach ( $new_symptoms['symptoms'] as $symptom_key => $symptom_data ) {
				if ( ! isset( $merged_symptoms['symptoms'][ $symptom_key ] ) ) {
					// Add new symptom
					$merged_symptoms['symptoms'][ $symptom_key ] = $symptom_data;
					$merged_symptoms['total_count']++;

					// Track what triggered this symptom
					$symptom_triggers[ $symptom_key ] = array(
						'assessment_type' => $assessment_type,
						'triggered_at'    => current_time( 'mysql' ),
						'trigger_conditions' => self::get_symptom_trigger_conditions( $symptom_key, $user_id, $assessment_type ),
					);
				} else {
					// Update existing symptom with new assessment info
					$existing_symptom = $merged_symptoms['symptoms'][ $symptom_key ];
					
					// Add new assessment to the list
					if ( ! in_array( $assessment_type, $existing_symptom['assessments'] ) ) {
						$existing_symptom['assessments'][] = $assessment_type;
					}

					// Update last reported time
					$existing_symptom['last_reported'] = current_time( 'mysql' );
					$existing_symptom['occurrence_count']++;

					$merged_symptoms['symptoms'][ $symptom_key ] = $existing_symptom;
				}
			}
		}

		// Update categorized arrays
		$merged_symptoms = self::rebuild_categorized_arrays( $merged_symptoms );

		// Save updated symptom triggers
		update_user_meta( $user_id, self::SYMPTOM_TRIGGERS_KEY, $symptom_triggers );

		return $merged_symptoms;
	}

	/**
	 * Check if a symptom should be resolved based on new assessment data
	 *
	 * @param string $symptom_key Symptom key
	 * @param array $symptom_data Symptom data
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @return bool True if symptom should be resolved
	 */
	private static function should_resolve_symptom( $symptom_key, $symptom_data, $user_id, $assessment_type ) {
		// Get the trigger conditions for this symptom
		$symptom_triggers = get_user_meta( $user_id, self::SYMPTOM_TRIGGERS_KEY, true );
		if ( ! is_array( $symptom_triggers ) || ! isset( $symptom_triggers[ $symptom_key ] ) ) {
			return false; // No trigger info, keep symptom
		}

		$trigger_info = $symptom_triggers[ $symptom_key ];
		$trigger_conditions = $trigger_info['trigger_conditions'] ?? array();

		// Check if the current assessment provides data that resolves the trigger conditions
		foreach ( $trigger_conditions as $condition ) {
			if ( self::check_condition_resolution( $condition, $user_id, $assessment_type ) ) {
				error_log( "ENNU Centralized Symptoms: Condition resolved for symptom '{$symptom_key}': " . print_r( $condition, true ) );
				return true; // This condition is resolved, so the symptom should be removed
			}
		}

		return false; // No conditions resolved, keep symptom
	}

	/**
	 * Check if a specific condition is resolved by the current assessment
	 *
	 * @param array $condition Trigger condition
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @return bool True if condition is resolved
	 */
	private static function check_condition_resolution( $condition, $user_id, $assessment_type ) {
		$condition_type = $condition['type'] ?? '';
		$condition_field = $condition['field'] ?? '';
		$condition_value = $condition['value'] ?? '';

		switch ( $condition_type ) {
			case 'score_threshold':
				// Check if score is now above threshold
				$current_score = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_calculated_score', true );
				if ( $current_score && floatval( $current_score ) > floatval( $condition_value ) ) {
					return true;
				}
				break;

			case 'question_answer':
				// Check if question answer has changed to a positive response
				$current_answer = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_' . $condition_field, true );
				if ( $current_answer && $current_answer !== $condition_value ) {
					// Check if new answer is positive (resolves the symptom)
					if ( self::is_positive_response( $current_answer ) ) {
						return true;
					}
				}
				break;

			case 'category_score':
				// Check if category score is now above threshold
				$category_scores = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_category_scores', true );
				if ( is_array( $category_scores ) && isset( $category_scores[ $condition_field ] ) ) {
					$category_score = floatval( $category_scores[ $condition_field ] );
					if ( $category_score > floatval( $condition_value ) ) {
						return true;
					}
				}
				break;
		}

		return false;
	}

	/**
	 * Check if a response is positive (resolves symptoms)
	 *
	 * @param mixed $response User response
	 * @return bool True if response is positive
	 */
	private static function is_positive_response( $response ) {
		$positive_responses = array(
			'no', 'never', 'none', 'excellent', 'very_good', 'good', 'high', 'frequent', 'daily',
			'strongly_disagree', 'disagree', 'not_at_all', 'rarely', 'seldom'
		);

		$response_lower = strtolower( strval( $response ) );
		return in_array( $response_lower, $positive_responses );
	}

	/**
	 * Get trigger conditions for a symptom based on assessment data
	 *
	 * @param string $symptom_key Symptom key
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @return array Trigger conditions
	 */
	private static function get_symptom_trigger_conditions( $symptom_key, $user_id, $assessment_type ) {
		$conditions = array();

		// Get assessment data to determine what triggered this symptom
		$assessment_data = self::get_assessment_data_for_symptom( $user_id, $assessment_type, $symptom_key );

		if ( ! empty( $assessment_data ) ) {
			// Add score-based conditions
			$overall_score = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_calculated_score', true );
			if ( $overall_score && floatval( $overall_score ) < 6.0 ) {
				$conditions[] = array(
					'type' => 'score_threshold',
					'field' => 'overall_score',
					'value' => '6.0',
					'description' => 'Overall score below 6.0'
				);
			}

			// Add question-based conditions
			foreach ( $assessment_data as $field => $value ) {
				if ( strpos( $field, '_q' ) !== false && ! empty( $value ) ) {
					$conditions[] = array(
						'type' => 'question_answer',
						'field' => $field,
						'value' => $value,
						'description' => "Question {$field} answered with '{$value}'"
					);
				}
			}

			// Add category-based conditions
			$category_scores = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_category_scores', true );
			if ( is_array( $category_scores ) ) {
				foreach ( $category_scores as $category => $score ) {
					if ( floatval( $score ) < 6.0 ) {
						$conditions[] = array(
							'type' => 'category_score',
							'field' => $category,
							'value' => '6.0',
							'description' => "Category '{$category}' score below 6.0"
						);
					}
				}
			}
		}

		return $conditions;
	}

	/**
	 * Get assessment data relevant to a specific symptom
	 *
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @param string $symptom_key Symptom key
	 * @return array Assessment data
	 */
	private static function get_assessment_data_for_symptom( $user_id, $assessment_type, $symptom_key ) {
		$data = array();

		// Get all user meta for this assessment using batch retrieval
		$db_optimizer = ENNU_Database_Optimizer::get_instance();
		$user_meta = $db_optimizer->get_user_meta_batch( $user_id, array() );
		$assessment_prefix = 'ennu_' . $assessment_type . '_';

		foreach ( $user_meta as $key => $value ) {
			if ( strpos( $key, $assessment_prefix ) === 0 ) {
				$field_name = str_replace( $assessment_prefix, '', $key );
				$data[ $field_name ] = $value;
			}
		}

		return $data;
	}

	/**
	 * Rebuild categorized arrays after merging symptoms
	 *
	 * @param array $symptoms Symptoms array
	 * @return array Rebuilt symptoms array
	 */
	private static function rebuild_categorized_arrays( $symptoms ) {
		$symptoms['by_category'] = array();
		$symptoms['by_severity'] = array();
		$symptoms['by_frequency'] = array();

		if ( ! isset( $symptoms['symptoms'] ) || ! is_array( $symptoms['symptoms'] ) ) {
			return $symptoms;
		}

		foreach ( $symptoms['symptoms'] as $symptom_key => $symptom_data ) {
			// Ensure symptom_data is an array
			if ( ! is_array( $symptom_data ) ) {
				continue;
			}

			// Add to category grouping
			$category = isset( $symptom_data['category'] ) ? $symptom_data['category'] : 'General';
			if ( ! isset( $symptoms['by_category'][ $category ] ) ) {
				$symptoms['by_category'][ $category ] = array();
			}
			$symptoms['by_category'][ $category ][] = $symptom_key;

			// Add to severity grouping
			$severity = 'moderate'; // Default
			if ( isset( $symptom_data['severity'] ) ) {
				if ( is_array( $symptom_data['severity'] ) ) {
					$severity = ! empty( $symptom_data['severity'] ) ? $symptom_data['severity'][0] : 'moderate';
				} else {
					$severity = $symptom_data['severity'];
				}
			}
			if ( ! isset( $symptoms['by_severity'][ $severity ] ) ) {
				$symptoms['by_severity'][ $severity ] = array();
			}
			$symptoms['by_severity'][ $severity ][] = $symptom_key;

			// Add to frequency grouping
			$frequency = 'frequent'; // Default
			if ( isset( $symptom_data['frequency'] ) ) {
				if ( is_array( $symptom_data['frequency'] ) ) {
					$frequency = ! empty( $symptom_data['frequency'] ) ? $symptom_data['frequency'][0] : 'frequent';
				} else {
					$frequency = $symptom_data['frequency'];
				}
			}
			if ( ! isset( $symptoms['by_frequency'][ $frequency ] ) ) {
				$symptoms['by_frequency'][ $frequency ] = array();
			}
			$symptoms['by_frequency'][ $frequency ][] = $symptom_key;
		}

		return $symptoms;
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
			'slow_healing' => array( 'vitamin_d', 'zinc', 'vitamin_c', 'glucose' )
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
					$flag_created = $flag_manager->flag_biomarker(
						$user_id,
						$biomarker,
						'symptom_triggered',
						"Flagged due to reported symptom: {$symptom_name}",
						null,
						'centralized_symptoms',
						$symptom_name
					);
					
					if ( $flag_created ) {
						$flags_created++;
						error_log( "ENNU Centralized Symptoms: Created flag for biomarker '{$biomarker}' due to symptom '{$symptom_name}'" );
					}
				}
			} else {
				error_log( "ENNU Centralized Symptoms: No biomarker mapping found for symptom '{$symptom_name}'" );
			}
		}

		error_log( "ENNU Centralized Symptoms: Auto-flagging complete. Created {$flags_created} flags for user {$user_id}" );
		return $flags_created;
	}

	/**
	 * Get symptom analytics for a user
	 *
	 * @param int $user_id User ID
	 * @return array Symptom analytics
	 */
	public static function get_symptom_analytics( $user_id ) {
		$symptoms = self::get_centralized_symptoms( $user_id );
		
		if ( empty( $symptoms['symptoms'] ) ) {
			return array(
				'total_symptoms' => 0,
				'by_category' => array(),
				'by_severity' => array(),
				'by_frequency' => array(),
				'most_common_category' => '',
				'most_severe_symptoms' => array(),
				'most_frequent_symptoms' => array(),
				'trends' => array()
			);
		}

		return array(
			'total_symptoms' => count( $symptoms['symptoms'] ),
			'by_category' => $symptoms['by_category'] ?? array(),
			'by_severity' => $symptoms['by_severity'] ?? array(),
			'by_frequency' => $symptoms['by_frequency'] ?? array(),
			'most_common_category' => self::get_most_common_category( $symptoms['symptoms'] ),
			'most_severe_symptoms' => self::get_most_severe_symptoms( $symptoms['symptoms'] ),
			'most_frequent_symptoms' => self::get_most_frequent_symptoms( $symptoms['symptoms'] ),
			'trends' => self::analyze_symptom_trends( $symptoms )
		);
	}

	/**
	 * Get most common symptom category
	 *
	 * @param array $symptoms Symptoms array
	 * @return string Most common category
	 */
	private static function get_most_common_category( $symptoms ) {
		$categories = array();
		foreach ( $symptoms as $symptom ) {
			$category = $symptom['category'] ?? 'General';
			$categories[$category] = ( $categories[$category] ?? 0 ) + 1;
		}
		
		if ( empty( $categories ) ) {
			return 'General';
		}
		
		arsort( $categories );
		return array_keys( $categories )[0];
	}

	/**
	 * Get most severe symptoms
	 *
	 * @param array $symptoms Symptoms array
	 * @return array Most severe symptoms
	 */
	private static function get_most_severe_symptoms( $symptoms ) {
		$severe_symptoms = array();
		foreach ( $symptoms as $symptom ) {
			$severity = $symptom['severity'] ?? 'moderate';
			if ( in_array( $severity, array( 'severe', 'very_severe', 'extreme', 'critical' ) ) ) {
				$severe_symptoms[] = $symptom['name'];
			}
		}
		return array_slice( $severe_symptoms, 0, 5 );
	}

	/**
	 * Get most frequent symptoms
	 *
	 * @param array $symptoms Symptoms array
	 * @return array Most frequent symptoms
	 */
	private static function get_most_frequent_symptoms( $symptoms ) {
		$frequent_symptoms = array();
		foreach ( $symptoms as $symptom ) {
			$frequency = $symptom['frequency'] ?? 'frequent';
			if ( in_array( $frequency, array( 'daily', 'constant', 'frequent' ) ) ) {
				$frequent_symptoms[] = $symptom['name'];
			}
		}
		return array_slice( $frequent_symptoms, 0, 5 );
	}

	/**
	 * Analyze symptom trends
	 *
	 * @param array $symptoms Symptoms array
	 * @return array Trend analysis
	 */
	private static function analyze_symptom_trends( $symptoms ) {
		$trends = array(
			'improving' => 0,
			'stable' => 0,
			'worsening' => 0
		);

		foreach ( $symptoms as $symptom ) {
			$trend = $symptom['trend'] ?? 'stable';
			if ( isset( $trends[$trend] ) ) {
				$trends[$trend]++;
			}
		}

		return $trends;
	}

	/**
	 * Hook into assessment completion
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
		self::update_centralized_symptoms( $user_id, $assessment_type );
	}

	/**
	 * Handle biomarker flag removal
	 *
	 * @param int $user_id User ID
	 * @param string $biomarker_name Biomarker name
	 * @param string $removal_reason Removal reason
	 */
	public static function on_biomarker_flag_removed( $user_id, $biomarker_name, $removal_reason ) {
		error_log( "ENNU Centralized Symptoms: Biomarker flag removed for user {$user_id}, biomarker: {$biomarker_name}, reason: {$removal_reason}" );
		
		// Check if any symptoms should be resolved due to this biomarker being unflagged
		$symptoms_resolved = self::resolve_symptoms_for_unflagged_biomarker( $user_id, $biomarker_name );
		
		if ( $symptoms_resolved > 0 ) {
			error_log( "ENNU Centralized Symptoms: Resolved {$symptoms_resolved} symptoms for user {$user_id} due to biomarker '{$biomarker_name}' being unflagged" );
		}
	}

	/**
	 * Resolve symptoms when a biomarker is unflagged
	 *
	 * @param int $user_id User ID
	 * @param string $biomarker_name Biomarker name
	 * @return int Number of symptoms resolved
	 */
	private static function resolve_symptoms_for_unflagged_biomarker( $user_id, $biomarker_name ) {
		$symptoms_resolved = 0;
		$symptoms = self::get_centralized_symptoms( $user_id );
		
		if ( empty( $symptoms['symptoms'] ) ) {
			return 0;
		}

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

		$flag_manager = new ENNU_Biomarker_Flag_Manager();
		$symptoms_to_remove = array();

		foreach ( $symptoms['symptoms'] as $symptom_key => $symptom_data ) {
			if ( ! is_array( $symptom_data ) || ! isset( $symptom_data['name'] ) ) {
				continue;
			}

			$symptom_name = $symptom_data['name'];
			$symptom_key_clean = strtolower( str_replace( array( ' ', '-', '_' ), '_', $symptom_name ) );
			$symptom_key_clean = preg_replace( '/[^a-z0-9_]/', '', $symptom_key_clean );

			if ( isset( $symptom_biomarker_mapping[$symptom_key_clean] ) ) {
				$associated_biomarkers = $symptom_biomarker_mapping[$symptom_key_clean];
				
				if ( in_array( $biomarker_name, $associated_biomarkers ) ) {
					// Check if ALL associated biomarkers are unflagged
					$all_unflagged = true;
					
					foreach ( $associated_biomarkers as $associated_biomarker ) {
						$flags = $flag_manager->get_biomarker_flags( $user_id, $associated_biomarker );
						if ( ! empty( $flags ) ) {
							foreach ( $flags as $flag ) {
								if ( isset( $flag['status'] ) && $flag['status'] === 'active' ) {
									$all_unflagged = false;
									break 2;
								}
							}
						}
					}
					
					if ( $all_unflagged ) {
						$symptoms_to_remove[] = $symptom_key;
						$symptoms_resolved++;
					}
				}
			}
		}

		// Remove resolved symptoms
		if ( ! empty( $symptoms_to_remove ) ) {
			foreach ( $symptoms_to_remove as $symptom_key ) {
				unset( $symptoms['symptoms'][ $symptom_key ] );
			}

			// Rebuild categorized arrays
			$symptoms = self::rebuild_categorized_arrays( $symptoms );
			$symptoms['total_count'] = count( $symptoms['symptoms'] );
			$symptoms['last_updated'] = current_time( 'mysql' );

			// Save updated symptoms
			update_user_meta( $user_id, self::CENTRALIZED_SYMPTOMS_KEY, $symptoms );
		}

		return $symptoms_resolved;
	}

	/**
	 * Force update symptoms for a user
	 *
	 * @param int $user_id User ID
	 * @return bool Success status
	 */
	public static function force_update_symptoms( $user_id ) {
		return self::update_centralized_symptoms( $user_id );
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

		// Build meta keys for batch retrieval
		$meta_keys = array();
		foreach ( $symptom_questions as $q_id ) {
			$meta_keys[] = 'ennu_health_optimization_assessment_' . $q_id;
			$severity_key  = str_replace( '_q', '_severity_q', $q_id );
			$frequency_key = str_replace( '_q', '_frequency_q', $q_id );
			$meta_keys[] = 'ennu_health_optimization_assessment_' . $severity_key;
			$meta_keys[] = 'ennu_health_optimization_assessment_' . $frequency_key;
		}
		$meta_keys[] = 'ennu_health_optimization_assessment_score_calculated_at';

		// Get all meta data in one batch query
		$db_optimizer = ENNU_Database_Optimizer::get_instance();
		$meta_data = $db_optimizer->get_user_meta_batch( $user_id, $meta_keys );

		foreach ( $symptom_questions as $q_id ) {
			$symptom_key = 'ennu_health_optimization_assessment_' . $q_id;
			$symptom_value = $meta_data[ $symptom_key ] ?? '';
			$severity_key  = str_replace( '_q', '_severity_q', $q_id );
			$frequency_key = str_replace( '_q', '_frequency_q', $q_id );

			if ( ! empty( $symptom_value ) ) {
				$symptoms[] = array(
					'name'      => $symptom_value,
					'category'  => 'Health Optimization',
					'severity'  => $meta_data[ 'ennu_health_optimization_assessment_' . $severity_key ] ?? '',
					'frequency' => $meta_data[ 'ennu_health_optimization_assessment_' . $frequency_key ] ?? '',
					'date'      => $meta_data[ 'ennu_health_optimization_assessment_score_calculated_at' ] ?: current_time( 'mysql' ),
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
		
		// Get hormone data in batch
		$db_optimizer = ENNU_Database_Optimizer::get_instance();
		$meta_data = $db_optimizer->get_user_meta_batch( $user_id, array(
			'ennu_hormone_hormone_q1',
			'ennu_hormone_score_calculated_at'
		) );

		$symptom_selections = $meta_data[ 'ennu_hormone_hormone_q1' ] ?? array();

		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name'     => $symptom,
					'category' => 'Hormone',
					'date'     => $meta_data[ 'ennu_hormone_score_calculated_at' ] ?: current_time( 'mysql' ),
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
		
		// Get testosterone data in batch
		$db_optimizer = ENNU_Database_Optimizer::get_instance();
		$meta_data = $db_optimizer->get_user_meta_batch( $user_id, array(
			'ennu_testosterone_testosterone_q1',
			'ennu_testosterone_score_calculated_at'
		) );

		$symptom_selections = $meta_data[ 'ennu_testosterone_testosterone_q1' ] ?? array();

		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name'     => $symptom,
					'category' => 'Testosterone',
					'date'     => $meta_data[ 'ennu_testosterone_score_calculated_at' ] ?: current_time( 'mysql' ),
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
		
		// Get menopause data in batch
		$db_optimizer = ENNU_Database_Optimizer::get_instance();
		$meta_data = $db_optimizer->get_user_meta_batch( $user_id, array(
			'ennu_menopause_menopause_q1',
			'ennu_menopause_score_calculated_at'
		) );

		$symptom_selections = $meta_data[ 'ennu_menopause_menopause_q1' ] ?? array();

		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name'     => $symptom,
					'category' => 'Menopause',
					'date'     => $meta_data[ 'ennu_menopause_score_calculated_at' ] ?: current_time( 'mysql' ),
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
		
		// Get ED treatment data in batch
		$db_optimizer = ENNU_Database_Optimizer::get_instance();
		$meta_data = $db_optimizer->get_user_meta_batch( $user_id, array(
			'ennu_ed_treatment_ed_treatment_q1',
			'ennu_ed_treatment_score_calculated_at'
		) );

		$symptom_selections = $meta_data[ 'ennu_ed_treatment_ed_treatment_q1' ] ?? array();

		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name'     => $symptom,
					'category' => 'ED Treatment',
					'date'     => $meta_data[ 'ennu_ed_treatment_score_calculated_at' ] ?: current_time( 'mysql' ),
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
		
		// Get skin data in batch
		$db_optimizer = ENNU_Database_Optimizer::get_instance();
		$meta_data = $db_optimizer->get_user_meta_batch( $user_id, array(
			'ennu_skin_skin_q1',
			'ennu_skin_score_calculated_at'
		) );

		$symptom_selections = $meta_data[ 'ennu_skin_skin_q1' ] ?? array();

		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name'     => $symptom,
					'category' => 'Skin',
					'date'     => $meta_data[ 'ennu_skin_score_calculated_at' ] ?: current_time( 'mysql' ),
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
		
		// Get hair data in batch
		$db_optimizer = ENNU_Database_Optimizer::get_instance();
		$meta_data = $db_optimizer->get_user_meta_batch( $user_id, array(
			'ennu_hair_hair_q1',
			'ennu_hair_score_calculated_at'
		) );

		$symptom_selections = $meta_data[ 'ennu_hair_hair_q1' ] ?? array();

		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name'     => $symptom,
					'category' => 'Hair',
					'date'     => $meta_data[ 'ennu_hair_score_calculated_at' ] ?: current_time( 'mysql' ),
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
		
		// Get sleep data in batch
		$db_optimizer = ENNU_Database_Optimizer::get_instance();
		$meta_data = $db_optimizer->get_user_meta_batch( $user_id, array(
			'ennu_sleep_sleep_q1',
			'ennu_sleep_score_calculated_at'
		) );

		$symptom_selections = $meta_data[ 'ennu_sleep_sleep_q1' ] ?? array();

		if ( is_array( $symptom_selections ) ) {
			foreach ( $symptom_selections as $symptom ) {
				$symptoms[] = array(
					'name'     => $symptom,
					'category' => 'Sleep',
					'date'     => $meta_data[ 'ennu_sleep_score_calculated_at' ] ?: current_time( 'mysql' ),
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

		// Get all weight loss data in batch
		$db_optimizer = ENNU_Database_Optimizer::get_instance();
		$meta_data = $db_optimizer->get_user_meta_batch( $user_id, array(
			'ennu_weight-loss_wl_q9',
			'ennu_weight-loss_wl_q10',
			'ennu_weight-loss_wl_q5',
			'ennu_weight-loss_wl_q6',
			'ennu_weight-loss_wl_q8',
			'ennu_weight-loss_score_calculated_at'
		) );

		// Check for medical conditions that could be symptoms
		$medical_conditions = $meta_data[ 'ennu_weight-loss_wl_q9' ] ?? array();
		error_log( "ENNU Centralized Symptoms: Medical conditions for user {$user_id}: " . print_r( $medical_conditions, true ) );
		if ( is_array( $medical_conditions ) ) {
			foreach ( $medical_conditions as $condition ) {
				$symptoms[] = array(
					'name'     => $condition,
					'category' => 'Weight Loss - Medical Condition',
					'date'     => $meta_data[ 'ennu_weight-loss_score_calculated_at' ] ?: current_time( 'mysql' ),
				);
			}
		}

		// Check for low energy levels (could indicate underlying health issues)
		$energy_level = $meta_data[ 'ennu_weight-loss_wl_q10' ] ?? '';
		error_log( "ENNU Centralized Symptoms: Energy level for user {$user_id}: {$energy_level}" );
		if ( $energy_level === 'very' || $energy_level === 'somewhat' ) {
			$symptoms[] = array(
				'name'     => 'Low Energy Level',
				'category' => 'Weight Loss - Energy',
				'date'     => $meta_data[ 'ennu_weight-loss_score_calculated_at' ] ?: current_time( 'mysql' ),
			);
		}

		// Check for poor sleep quality (could be a symptom)
		$sleep_quality = $meta_data[ 'ennu_weight-loss_wl_q5' ] ?? '';
		error_log( "ENNU Centralized Symptoms: Sleep quality for user {$user_id}: {$sleep_quality}" );
		if ( $sleep_quality === 'less_than_5' || $sleep_quality === 'poor' ) {
			$symptoms[] = array(
				'name'     => 'Poor Sleep Quality',
				'category' => 'Weight Loss - Sleep',
				'date'     => $meta_data[ 'ennu_weight-loss_score_calculated_at' ] ?: current_time( 'mysql' ),
			);
		}

		// Check for high stress levels (could be a symptom)
		$stress_level = $meta_data[ 'ennu_weight-loss_wl_q6' ] ?? '';
		error_log( "ENNU Centralized Symptoms: Stress level for user {$user_id}: {$stress_level}" );
		if ( $stress_level === 'very' || $stress_level === 'high' ) {
			$symptoms[] = array(
				'name'     => 'High Stress Level',
				'category' => 'Weight Loss - Stress',
				'date'     => $meta_data[ 'ennu_weight-loss_score_calculated_at' ] ?: current_time( 'mysql' ),
			);
		}

		// Check for frequent cravings (could indicate hormonal issues)
		$cravings_frequency = $meta_data[ 'ennu_weight-loss_wl_q8' ] ?? '';
		error_log( "ENNU Centralized Symptoms: Cravings frequency for user {$user_id}: {$cravings_frequency}" );
		if ( $cravings_frequency === 'daily' || $cravings_frequency === 'multiple_times' ) {
			$symptoms[] = array(
				'name'     => 'Frequent Food Cravings',
				'category' => 'Weight Loss - Cravings',
				'date'     => $meta_data[ 'ennu_weight-loss_score_calculated_at' ] ?: current_time( 'mysql' ),
			);
		}

		error_log( "ENNU Centralized Symptoms: Total weight loss symptoms found for user {$user_id}: " . count( $symptoms ) );
		return $symptoms;
	}

	/**
	 * Get symptoms grouped by category
	 *
	 * @param int $user_id User ID
	 * @return array Symptoms grouped by category
	 */
	public static function get_symptoms_by_category( $user_id ) {
		$centralized_symptoms = self::get_centralized_symptoms( $user_id );
		return isset( $centralized_symptoms['by_category'] ) ? $centralized_symptoms['by_category'] : array();
	}

	/**
	 * Get symptoms grouped by assessment
	 *
	 * @param int $user_id User ID
	 * @return array Symptoms grouped by assessment
	 */
	public static function get_symptoms_by_assessment( $user_id ) {
		$centralized_symptoms = self::get_centralized_symptoms( $user_id );
		return isset( $centralized_symptoms['by_assessment'] ) ? $centralized_symptoms['by_assessment'] : array();
	}

	/**
	 * Get symptoms grouped by severity
	 *
	 * @param int $user_id User ID
	 * @return array Symptoms grouped by severity
	 */
	public static function get_symptoms_by_severity( $user_id ) {
		$centralized_symptoms = self::get_centralized_symptoms( $user_id );
		return isset( $centralized_symptoms['by_severity'] ) ? $centralized_symptoms['by_severity'] : array();
	}

	/**
	 * Get symptoms grouped by frequency
	 *
	 * @param int $user_id User ID
	 * @return array Symptoms grouped by frequency
	 */
	public static function get_symptoms_by_frequency( $user_id ) {
		$centralized_symptoms = self::get_centralized_symptoms( $user_id );
		return isset( $centralized_symptoms['by_frequency'] ) ? $centralized_symptoms['by_frequency'] : array();
	}

	/**
	 * Get total symptom count
	 *
	 * @param int $user_id User ID
	 * @return int Total symptom count
	 */
	public static function get_total_symptom_count( $user_id ) {
		$centralized_symptoms = self::get_centralized_symptoms( $user_id );
		return isset( $centralized_symptoms['total_count'] ) ? $centralized_symptoms['total_count'] : 0;
	}

	/**
	 * Get symptom history for a user
	 *
	 * @param int $user_id User ID
	 * @return array Symptom history
	 */
	public static function get_symptom_history( $user_id ) {
		$centralized_symptoms = self::get_centralized_symptoms( $user_id );
		return isset( $centralized_symptoms['symptoms'] ) ? $centralized_symptoms['symptoms'] : array();
	}

	/**
	 * Get symptom duration information
	 * 
	 * Since we implemented the ONE LOG symptoms system, symptoms don't expire.
	 * They only get removed when assessments are completed and questions are 
	 * answered in ways that no longer trigger them.
	 *
	 * @param array $symptom_data Symptom data array
	 * @return array Duration information
	 */
	public static function get_symptom_duration_info( $symptom_data ) {
		// In the ONE LOG system, symptoms are persistent until resolved by assessment completion
		// They don't have traditional expiration dates
		
		$current_time = current_time( 'timestamp' );
		$last_reported = isset( $symptom_data['last_reported'] ) ? strtotime( $symptom_data['last_reported'] ) : $current_time;
		
		// Calculate days since last reported
		$days_since_reported = floor( ( $current_time - $last_reported ) / ( 24 * 60 * 60 ) );
		
		// In the ONE LOG system, symptoms are always active until resolved
		// We'll show them as "active" with no expiration
		return array(
			'status' => 'active',
			'days_remaining' => 0, // No expiration in ONE LOG system
			'days_since_reported' => $days_since_reported,
			'last_reported' => $symptom_data['last_reported'] ?? current_time( 'mysql' ),
			'is_persistent' => true, // Symptoms persist until assessment resolution
			'resolution_method' => 'assessment_completion' // Only resolved by assessment completion
		);
	}
}

// Initialize hooks
ENNU_Centralized_Symptoms_Manager::hook_into_assessment_completion();
