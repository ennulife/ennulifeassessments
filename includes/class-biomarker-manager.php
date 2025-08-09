<?php
/**
 * ENNU Life Biomarker Data Manager
 * Handles lab data import, storage, and doctor recommendations
 *
 * @package ENNU_Life
 * @version 62.1.67
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Biomarker_Manager {

    /**
     * Convenience: upsert core physical biomarkers derived from assessment globals
     * - weight (lbs)
     * - height (in)
     * - bmi (kg/m²)
     * Optionally a target_weight when provided by assessment answers (e.g., desired loss)
     */
    public static function upsert_physical_biomarkers_from_globals( $user_id, $maybe_target_weight_lbs = null, $source = 'auto_sync' ) {
        if ( empty( $user_id ) ) {
            return false;
        }

        $hw = get_user_meta( $user_id, 'ennu_global_height_weight', true );
        if ( ! is_array( $hw ) ) {
            $hw = array();
        }

        $ft  = isset( $hw['ft'] ) ? floatval( $hw['ft'] ) : 0;
        $in  = isset( $hw['in'] ) ? floatval( $hw['in'] ) : 0;
        $lbs = isset( $hw['lbs'] ) ? floatval( $hw['lbs'] ) : ( isset( $hw['weight'] ) ? floatval( $hw['weight'] ) : 0 );
        $total_inches = max( 0, ( $ft * 12.0 ) + $in );

        $biomarkers = array();
        $now = current_time( 'mysql' );

        if ( $lbs > 0 ) {
            $biomarkers['weight'] = array(
                'value' => $lbs,
                'unit'  => 'lbs',
                'date'  => $now,
                'notes' => 'Auto-synced from global height/weight',
            );
        }

        if ( $total_inches > 0 ) {
            $biomarkers['height'] = array(
                'value' => $total_inches,
                'unit'  => 'inches',
                'date'  => $now,
                'notes' => 'Auto-synced from global height/weight',
            );
        }

        $bmi = get_user_meta( $user_id, 'ennu_calculated_bmi', true );
        if ( $bmi ) {
            $biomarkers['bmi'] = array(
                'value' => floatval( $bmi ),
                'unit'  => 'kg/m²',
                'date'  => $now,
                'notes' => 'Auto-synced from calculated BMI',
            );
        }

        // Age biomarker from DOB
        $dob = get_user_meta( $user_id, 'ennu_global_date_of_birth', true );
        if ( $dob ) {
            try {
                $birth = new DateTime( $dob );
                $nowDt = new DateTime();
                $ageYears = (int) $nowDt->diff( $birth )->y;
                if ( $ageYears > 0 ) {
                    $biomarkers['age'] = array(
                        'value' => $ageYears,
                        'unit'  => 'years',
                        'date'  => $now,
                        'notes' => 'Auto-synced from date of birth',
                    );
                }
            } catch ( Exception $e ) {
                // ignore
            }
        }

        if ( $maybe_target_weight_lbs && is_numeric( $maybe_target_weight_lbs ) ) {
            $biomarkers['target_weight'] = array(
                'value' => floatval( $maybe_target_weight_lbs ),
                'unit'  => 'lbs',
                'date'  => $now,
                'notes' => 'Target derived from assessment submission',
            );
        }

        if ( empty( $biomarkers ) ) {
            return false;
        }

        // Persist to auto-sync store so UI pulls these by default
        return self::save_user_biomarkers( $user_id, $biomarkers, $source );
    }

	public static function import_lab_results( $user_id, $lab_data, $source = 'manual' ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$sanitized_data = array();
		foreach ( $lab_data as $biomarker => $data ) {
			// Basic sanitization
			$sanitized_data[ sanitize_key( $biomarker ) ] = array(
				'value'     => sanitize_text_field( $data['value'] ),
				'unit'      => sanitize_text_field( $data['unit'] ),
				'test_date' => sanitize_text_field( $data['test_date'] ),
			);
		}

		update_user_meta( $user_id, 'ennu_biomarker_data', $sanitized_data );

		// Trigger score recalculation after import
		if ( class_exists( 'ENNU_Scoring_System' ) ) {
			$scoring_system = new ENNU_Scoring_System();
			$scoring_system->calculate_and_save_all_user_scores( $user_id );
		}

		return true;
	}

	public static function add_doctor_recommendations( $user_id, $recommendations ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'insufficient_permissions', 'Insufficient permissions to add recommendations' );
		}

		$existing_recommendations = get_user_meta( $user_id, 'ennu_doctor_recommendations', true );
		if ( ! is_array( $existing_recommendations ) ) {
			$existing_recommendations = array();
		}

		$new_recommendation = array(
			'date'              => current_time( 'mysql' ),
			'doctor_id'         => get_current_user_id(),
			'recommendations'   => $recommendations,
			'biomarker_targets' => $recommendations['biomarker_targets'] ?? array(),
			'lifestyle_advice'  => $recommendations['lifestyle_advice'] ?? '',
			'follow_up_date'    => $recommendations['follow_up_date'] ?? null,
		);

		$existing_recommendations[] = $new_recommendation;

		update_user_meta( $user_id, 'ennu_doctor_recommendations', $existing_recommendations );

		// Trigger score recalculation
		if ( class_exists( 'ENNU_Scoring_System' ) ) {
			ENNU_Scoring_System::calculate_and_save_all_user_scores( $user_id );
		}

		return array(
			'success'           => true,
			'recommendation_id' => count( $existing_recommendations ) - 1,
		);
	}

	public static function get_user_biomarkers( $user_id ) {
		// Get primary biomarker data
		$biomarker_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		if ( ! is_array( $biomarker_data ) ) {
			$biomarker_data = array();
		}

		// Get auto-synced biomarker data (weight, BMI, height, etc.)
		$auto_sync_data = get_user_meta( $user_id, 'ennu_user_biomarkers', true );
		if ( is_array( $auto_sync_data ) && ! empty( $auto_sync_data ) ) {
			// Merge auto-sync data with primary biomarker data
			// Auto-sync data takes precedence for weight, BMI, height
			foreach ( $auto_sync_data as $biomarker_key => $biomarker_info ) {
				if ( isset( $biomarker_info['value'] ) && ! empty( $biomarker_info['value'] ) ) {
					$biomarker_data[ $biomarker_key ] = array(
						'value' => $biomarker_info['value'],
						'unit' => $biomarker_info['unit'] ?? '',
						'date' => $biomarker_info['date'] ?? current_time( 'mysql' ),
						'source' => $biomarker_info['source'] ?? 'auto_sync',
						'notes' => $biomarker_info['notes'] ?? 'Auto-synced from global fields',
					);
				}
			}
		}

		return $biomarker_data;
	}

	public static function get_doctor_recommendations( $user_id ) {
		return get_user_meta( $user_id, 'ennu_doctor_recommendations', true );
	}

	/**
	 * Save user biomarker data from any source.
	 * This is the new single source of truth for writing biomarker data.
	 *
	 * @param int    $user_id The user ID.
	 * @param array  $biomarkers_to_save The new biomarker data to save.
	 * @param string $source The source of the data (e.g., 'manual', 'lab_import', 'auto_sync').
	 * @return bool True on success, false on failure.
	 */
	public static function save_user_biomarkers( $user_id, $biomarkers_to_save, $source = 'manual' ) {
		if ( empty( $user_id ) || empty( $biomarkers_to_save ) || ! is_array( $biomarkers_to_save ) ) {
			return false;
		}

		// Decide which meta key to write to based on the source.
		// This maintains the separation of manual vs. automated data.
		$meta_key = ( $source === 'manual' ) ? 'ennu_biomarker_data' : 'ennu_user_biomarkers';

		$existing_biomarkers = get_user_meta( $user_id, $meta_key, true );
		if ( ! is_array( $existing_biomarkers ) ) {
			$existing_biomarkers = array();
		}

		// Add a source and timestamp to the data being saved
		$timestamp = current_time( 'mysql' );
		foreach ( $biomarkers_to_save as $key => &$data ) {
			if ( is_array( $data ) ) {
				$data['source'] = $source;
				$data['date'] = $data['date'] ?? $timestamp;
			}
		}

		$merged_biomarkers = array_merge( $existing_biomarkers, $biomarkers_to_save );

		return update_user_meta( $user_id, $meta_key, $merged_biomarkers );
	}

	private static function validate_lab_data( $lab_data ) {
		if ( ! is_array( $lab_data ) ) {
			return new WP_Error( 'invalid_format', 'Lab data must be an array' );
		}

		$validated_data = array();

		foreach ( $lab_data as $biomarker_name => $biomarker_info ) {
			if ( ! is_array( $biomarker_info ) || ! array_key_exists( 'value', $biomarker_info ) ) {
				continue;
			}

			$validated_data[ $biomarker_name ] = array(
				'value'           => floatval( $biomarker_info['value'] ),
				'unit'            => sanitize_text_field( $biomarker_info['unit'] ?? '' ),
				'reference_range' => sanitize_text_field( $biomarker_info['reference_range'] ?? '' ),
				'test_date'       => sanitize_text_field( $biomarker_info['test_date'] ?? current_time( 'mysql' ) ),
				'lab_name'        => sanitize_text_field( $biomarker_info['lab_name'] ?? '' ),
			);
		}

		return $validated_data;
	}

	public static function calculate_new_life_score_projection( $user_id ) {
		$current_biomarkers     = self::get_user_biomarkers( $user_id );
		$doctor_recommendations = self::get_doctor_recommendations( $user_id );

		if ( empty( $current_biomarkers ) || empty( $doctor_recommendations ) ) {
			return null;
		}

		$latest_recommendations = end( $doctor_recommendations );
		$biomarker_targets      = $latest_recommendations['biomarker_targets'] ?? array();

		$projected_biomarkers = $current_biomarkers;
		foreach ( $biomarker_targets as $biomarker_name => $target_value ) {
			if ( isset( $projected_biomarkers[ $biomarker_name ] ) ) {
				$projected_biomarkers[ $biomarker_name ]['value'] = $target_value;
			}
		}

		if ( class_exists( 'ENNU_Objective_Engine' ) ) {
			$current_scores = get_user_meta( $user_id, 'ennu_average_pillar_scores', true );

			$objective_engine = new ENNU_Objective_Engine( $projected_biomarkers );
			$projected_scores = $objective_engine->apply_biomarker_actuality_adjustments( $current_scores );

			$projected_life_score = array_sum( $projected_scores ) / count( $projected_scores );

			return array(
				'projected_pillar_scores' => $projected_scores,
				'projected_life_score'    => $projected_life_score,
				'improvement_potential'   => $projected_life_score - ( get_user_meta( $user_id, 'ennu_life_score', true ) ?? 0 ),
			);
		}

		return null;
	}

	public static function get_biomarker_recommendations( $user_id ) {
		// Get user symptoms from centralized symptoms manager
		if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
			$symptoms_data = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $user_id );
			$user_symptoms = $symptoms_data['symptoms'] ?? array();
		} else {
			$user_symptoms = array();
		}
		
		$biomarker_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/biomarker-map.php';

		if ( ! file_exists( $biomarker_map_file ) ) {
			return array();
		}

		$biomarker_map          = require $biomarker_map_file;
		$recommended_biomarkers = array();

		$all_symptoms = array();
		foreach ( $user_symptoms as $assessment_type => $symptoms ) {
			if ( is_array( $symptoms ) ) {
				$all_symptoms = array_merge( $all_symptoms, $symptoms );
			}
		}

		$symptom_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/symptom-map.php';
		if ( file_exists( $symptom_map_file ) ) {
			$symptom_map = require $symptom_map_file;

			foreach ( $all_symptoms as $symptom ) {
				if ( isset( $symptom_map[ $symptom ] ) ) {
					foreach ( $symptom_map[ $symptom ] as $category => $weight_data ) {
						if ( isset( $biomarker_map[ $category ] ) ) {
							$recommended_biomarkers = array_merge( $recommended_biomarkers, $biomarker_map[ $category ] );
						}
					}
				}
			}
		}

		return array_unique( $recommended_biomarkers );
	}

	/**
	 * Get all available biomarkers in the system
	 * Returns all biomarkers defined in the biomarker map
	 *
	 * @return array Array of all available biomarker IDs
	 */
	public static function get_all_available_biomarkers() {
		$biomarker_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/biomarker-map.php';
		
		if (!file_exists($biomarker_map_file)) {
			return array();
		}
		
		$biomarker_map = require $biomarker_map_file;
		$all_biomarkers = array();
		
		// Flatten the nested array structure
		foreach ($biomarker_map as $vector => $biomarkers) {
			if (is_array($biomarkers)) {
				$all_biomarkers = array_merge($all_biomarkers, $biomarkers);
			}
		}
		
		// Remove duplicates and return unique biomarkers
		return array_unique($all_biomarkers);
	}

	/**
	 * Get a lean, optimized list of user biomarkers for the admin display.
	 *
	 * @param int $user_id The ID of the user.
	 * @return array An array of biomarker data optimized for display.
	 */
	public static function get_user_biomarkers_for_admin_display( $user_id ) {
		$user_biomarkers = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		if ( ! is_array( $user_biomarkers ) ) {
			$user_biomarkers = array();
		}
		return $user_biomarkers;
	}

	/**
	 * Get fallback display name for common biomarkers
	 *
	 * @param string $biomarker_key The biomarker key
	 * @return string Human readable display name
	 */
	public static function get_fallback_display_name( $biomarker_key ) {
		$fallback_names = array(
			// Physical Measurements
			'weight' => 'Weight',
			'height' => 'Height', 
			'bmi' => 'BMI',
			'body_fat_percent' => 'Body Fat Percent',
			'waist_measurement' => 'Waist Measurement',
			'neck_measurement' => 'Neck Measurement',
			'hip_measurement' => 'Hip Measurement',
			'chest_measurement' => 'Chest Measurement',
			'arm_measurement' => 'Arm Measurement',
			'thigh_measurement' => 'Thigh Measurement',
			'grip_strength' => 'Grip Strength',
			
			// Cardiovascular
			'blood_pressure' => 'Blood Pressure',
			'heart_rate' => 'Heart Rate',
			'cholesterol' => 'Total Cholesterol',
			'hdl' => 'HDL Cholesterol',
			'ldl' => 'LDL Cholesterol',
			'triglycerides' => 'Triglycerides',
			'apob' => 'ApoB',
			'hs_crp' => 'hs-CRP',
			'homocysteine' => 'Homocysteine',
			
			// Hormones
			'testosterone_total' => 'Total Testosterone',
			'testosterone_free' => 'Free Testosterone',
			'estradiol' => 'Estradiol',
			'progesterone' => 'Progesterone',
			'cortisol' => 'Cortisol',
			'tsh' => 'TSH',
			't3' => 'T3',
			't4' => 'T4',
			'free_t3' => 'Free T3',
			'free_t4' => 'Free T4',
			
			// Metabolic
			'glucose' => 'Glucose',
			'hba1c' => 'HbA1c',
			'insulin' => 'Insulin',
			'fasting_insulin' => 'Fasting Insulin',
			
			// Blood Work
			'hemoglobin' => 'Hemoglobin',
			'hematocrit' => 'Hematocrit',
			'rbc' => 'Red Blood Cells',
			'wbc' => 'White Blood Cells',
			'platelets' => 'Platelets',
			'ferritin' => 'Ferritin',
			
			// Liver Function
			'alt' => 'ALT',
			'ast' => 'AST',
			'alkaline_phosphatase' => 'Alkaline Phosphatase',
			'bilirubin' => 'Bilirubin',
			
			// Kidney Function
			'creatinine' => 'Creatinine',
			'bun' => 'BUN',
			'gfr' => 'eGFR',
			
			// Vitamins & Minerals
			'vitamin_d' => 'Vitamin D',
			'vitamin_b12' => 'Vitamin B12',
			'folate' => 'Folate',
			'calcium' => 'Calcium',
			'magnesium' => 'Magnesium',
			'potassium' => 'Potassium',
			'sodium' => 'Sodium',
			
			// Other
			'uric_acid' => 'Uric Acid',
			'albumin' => 'Albumin',
		);
		
		return $fallback_names[ $biomarker_key ] ?? ucwords( str_replace( '_', ' ', $biomarker_key ) );
	}

	/**
	 * Get biomarker measurement data for display with missing data handling
	 *
	 * @param int $user_id User ID
	 * @param string $biomarker_key Biomarker key
	 * @return array Formatted biomarker data for display
	 */
	public static function get_biomarker_measurement_data( $user_id, $biomarker_key ) {
		// Validate inputs
		if ( empty( $biomarker_key ) || ! is_string( $biomarker_key ) || trim( $biomarker_key ) === '' ) {
			return array(
				'biomarker_key' => '',
				'biomarker_name' => '',
				'display_name' => 'Pending Data Validation',
				'has_user_data' => false,
				'current_value' => '',
				'unit' => '',
				'status' => 'error',
				'display_value' => 'Invalid biomarker key provided',
			);
		}
		
		$biomarker_key = trim( $biomarker_key );
		
		// Get user biomarkers with fallback handling
		$user_biomarkers = self::get_user_biomarkers( $user_id );
		
		// Check if biomarker exists in user data
		if ( ! isset( $user_biomarkers[ $biomarker_key ] ) ) {
			// Try alternative key formats
			$alternative_keys = self::get_alternative_biomarker_keys( $biomarker_key );
			foreach ( $alternative_keys as $alt_key ) {
				if ( isset( $user_biomarkers[ $alt_key ] ) ) {
					$biomarker_key = $alt_key;
					break;
				}
			}
		}
		
		// Get biomarker data if available
		$biomarker_data = $user_biomarkers[ $biomarker_key ] ?? null;
		$has_user_data = ! empty( $biomarker_data ) && isset( $biomarker_data['value'] );
		
		// Get display name with multiple fallbacks
		$display_name = '';
		if ( $has_user_data && isset( $biomarker_data['display_name'] ) ) {
			$display_name = $biomarker_data['display_name'];
		}
		
		// Try to get display name from range manager
		if ( empty( $display_name ) && class_exists( 'ENNU_Recommended_Range_Manager' ) ) {
			$range_manager = new ENNU_Recommended_Range_Manager();
			$range_data = $range_manager->get_recommended_range( $biomarker_key );
			if ( ! isset( $range_data['error'] ) && ! empty( $range_data['display_name'] ) ) {
				$display_name = $range_data['display_name'];
			}
		}
		
		// Use fallback display name mapping
		if ( empty( $display_name ) ) {
			$display_name = self::get_fallback_display_name( $biomarker_key );
		}
		
		// Get optimal range from the recommended range manager
		$optimal_range = null;
		if ( class_exists( 'ENNU_Recommended_Range_Manager' ) ) {
			$range_manager = new ENNU_Recommended_Range_Manager();
			$optimal_range = $range_manager->get_recommended_range( $biomarker_key );
			
					// Keep original flat structure for template compatibility
		$original_optimal_range = $optimal_range;
		
		// Create nested structure for target calculator compatibility
		$nested_optimal_range = null;
		if ( $optimal_range && ! isset( $optimal_range['error'] ) ) {
			$nested_optimal_range = array(
				'ranges' => array(
					'optimal_min' => $optimal_range['optimal_min'],
					'optimal_max' => $optimal_range['optimal_max'],
					'normal_min' => $optimal_range['normal_min'],
					'normal_max' => $optimal_range['normal_max'],
					'critical_min' => $optimal_range['critical_min'],
					'critical_max' => $optimal_range['critical_max']
				),
				'unit' => $optimal_range['unit'],
				'description' => $optimal_range['description']
			);
		}
		}

		// Get doctor target value
		$doctor_recommendations = get_user_meta( $user_id, 'ennu_doctor_recommendations', true );
		$doctor_target = isset( $doctor_recommendations['biomarker_targets'][ $biomarker_key ] ) 
			? $doctor_recommendations['biomarker_targets'][ $biomarker_key ] 
			: null;

		// Get AI target if no doctor target exists
		$target_value = $doctor_target;
		if ( ! $target_value && class_exists( 'ENNU_Biomarker_Target_Calculator' ) && $has_user_data ) {
			// Get user demographics for AI calculation
			$user_age = get_user_meta( $user_id, 'ennu_global_exact_age', true );
			$user_gender = get_user_meta( $user_id, 'ennu_global_gender', true );
			$current_value = floatval( $biomarker_data['value'] );
			
			if ( $user_age && $user_gender && $nested_optimal_range ) {
				$ai_target_data = ENNU_Biomarker_Target_Calculator::calculate_personalized_target( 
					$biomarker_key, 
					$current_value, 
					$nested_optimal_range, 
					$user_age, 
					$user_gender 
				);
				if ( $ai_target_data && isset( $ai_target_data['target_value'] ) ) {
					$target_value = $ai_target_data['target_value'];
				}
			}
		}

		// Create measurement data structure matching render_biomarker_measurement expectations
		$measurement_data = array(
			'biomarker_id' => $biomarker_key, // render function expects biomarker_id, not biomarker_key
			'biomarker_key' => $biomarker_key, // Keep both for compatibility
			'biomarker_name' => $biomarker_key,
			'display_name' => $display_name,
			'has_user_data' => $has_user_data,
			'current_value' => $has_user_data ? $biomarker_data['value'] : 0,
			'target_value' => $target_value,
			'unit' => $has_user_data ? ( $biomarker_data['unit'] ?? '' ) : ( $original_optimal_range['unit'] ?? '' ),
			'date' => $has_user_data ? ( $biomarker_data['date'] ?? '' ) : '',
			'source' => $has_user_data ? ( $biomarker_data['source'] ?? '' ) : '',
			'optimal_range' => $original_optimal_range,
			'optimal_min' => isset( $original_optimal_range['optimal_min'] ) ? $original_optimal_range['optimal_min'] : 0,
			'optimal_max' => isset( $original_optimal_range['optimal_max'] ) ? $original_optimal_range['optimal_max'] : 100,
			'percentage_position' => 50, // Default to middle, will be calculated below
			'target_position' => null,
			'status' => $has_user_data ? 'normal' : 'missing',
			'achievement_status' => 'normal',
			'has_flags' => false,
			'flags' => array(),
			'health_vector' => '',
			'has_admin_override' => false,
		);

		// Calculate status and percentage if we have data and ranges
		if ( $has_user_data && $original_optimal_range && ! isset( $original_optimal_range['error'] ) ) {
			$value = floatval( $biomarker_data['value'] );
			$optimal_min = floatval( $original_optimal_range['optimal_min'] ?? 0 );
			$optimal_max = floatval( $original_optimal_range['optimal_max'] ?? 100 );
			
			if ( $optimal_min > 0 || $optimal_max > 0 ) {
				// Calculate percentage position in optimal range
				if ( $value >= $optimal_min && $value <= $optimal_max ) {
					$measurement_data['status'] = 'optimal';
					$measurement_data['percentage_position'] = ( ( $value - $optimal_min ) / ( $optimal_max - $optimal_min ) ) * 100;
				} elseif ( $value < $optimal_min ) {
					$measurement_data['status'] = 'low';
					$measurement_data['percentage_position'] = 0;
				} else {
					$measurement_data['status'] = 'high';
					$measurement_data['percentage_position'] = 100;
				}
			}
		} else if ( ! $has_user_data ) {
			$measurement_data['display_value'] = 'Awaiting lab results';
		}

		return $measurement_data;
	}

	/**
	 * Get missing biomarker data structure
	 *
	 * @param string $biomarker_key Biomarker key
	 * @param array $partial_data Partial data if available
	 * @return array Missing biomarker data structure
	 */
	private static function get_missing_biomarker_data( $biomarker_key, $partial_data = array() ) {
		return array(
			'biomarker_key' => $biomarker_key,
			'current_value' => null,
			'unit' => self::get_default_unit( $biomarker_key ),
			'date' => null,
			'source' => null,
			'optimal_range' => null,
			'doctor_target' => null,
			'ai_target' => null,
			'percentage_in_range' => 0,
			'status' => 'missing',
			'flags' => array(),
			'achievement_status' => 'no_data',
			'has_data' => false,
			'display_value' => 'Awaiting lab results',
			'missing_reason' => self::get_missing_data_reason( $biomarker_key ),
			'recommendation' => self::get_missing_data_recommendation( $biomarker_key )
		);
	}

	/**
	 * Get alternative biomarker keys for fallback lookup
	 *
	 * @param string $biomarker_key The primary biomarker key
	 * @return array Array of alternative keys to try
	 */
	public static function get_alternative_biomarker_keys( $biomarker_key ) {
		$alternative_mappings = array(
			'weight' => array( 'body_weight', 'weight_lbs', 'weight_kg' ),
			'height' => array( 'body_height', 'height_cm', 'height_in' ),
			'bmi' => array( 'body_mass_index', 'calculated_bmi' ),
			'body_fat_percent' => array( 'body_fat_percentage', 'fat_percent', 'bf_percent' ),
			'waist_measurement' => array( 'waist_circumference', 'waist_cm', 'waist_in' ),
			'neck_measurement' => array( 'neck_circumference', 'neck_cm', 'neck_in' ),
			'hip_measurement' => array( 'hip_circumference', 'hip_cm', 'hip_in' ),
			'blood_pressure' => array( 'bp', 'blood_pressure_systolic', 'systolic_bp' ),
			'heart_rate' => array( 'hr', 'pulse', 'resting_heart_rate' ),
			'testosterone_total' => array( 'total_testosterone', 'testosterone', 'test_total' ),
			'testosterone_free' => array( 'free_testosterone', 'free_test', 'test_free' ),
		);
		
		return $alternative_mappings[ $biomarker_key ] ?? array();
	}

	/**
	 * Get default unit for a biomarker
	 *
	 * @param string $biomarker_key Biomarker key
	 * @return string Default unit
	 */
	private static function get_default_unit( $biomarker_key ) {
		$default_units = array(
			'testosterone_total' => 'ng/dL',
			'testosterone_free' => 'pg/mL',
			'estradiol' => 'pg/mL',
			'vitamin_d' => 'ng/mL',
			'glucose' => 'mg/dL',
			'cholesterol_total' => 'mg/dL',
			'hdl' => 'mg/dL',
			'ldl' => 'mg/dL',
			'triglycerides' => 'mg/dL',
			'weight' => 'lbs',
			'bmi' => 'kg/m²',
			'height' => 'inches',
			'age' => 'years',
		);

		return isset( $default_units[ $biomarker_key ] ) ? $default_units[ $biomarker_key ] : '';
	}

	/**
	 * Get missing data reason
	 *
	 * @param string $biomarker_key Biomarker key
	 * @return string Reason for missing data
	 */
	private static function get_missing_data_reason( $biomarker_key ) {
		$core_biomarkers = array( 'weight', 'height', 'age', 'bmi' );
		
		if ( in_array( $biomarker_key, $core_biomarkers ) ) {
			return 'Complete your profile to get this measurement';
		}
		
		return 'Lab test required to get this biomarker value';
	}

	/**
	 * Get missing data recommendation
	 *
	 * @param string $biomarker_key Biomarker key
	 * @return string Recommendation for missing data
	 */
	private static function get_missing_data_recommendation( $biomarker_key ) {
		$core_biomarkers = array( 'weight', 'height', 'age', 'bmi' );
		
		if ( in_array( $biomarker_key, $core_biomarkers ) ) {
			return 'Update your profile information to see this value';
		}
		
		return 'Schedule lab tests to get comprehensive biomarker analysis';
	}

	/**
	 * Determine biomarker status based on value and range
	 *
	 * @param mixed $value Biomarker value
	 * @param array $optimal_range Optimal range data
	 * @return string Status (optimal, suboptimal, critical, missing)
	 */
	private static function determine_biomarker_status( $value, $optimal_range ) {
		if ( empty( $value ) || $value === '' ) {
			return 'missing';
		}

		if ( ! $optimal_range || ! isset( $optimal_range['min'] ) || ! isset( $optimal_range['max'] ) ) {
			return 'normal'; // Default when no range available
		}

		$numeric_value = floatval( $value );
		$min = floatval( $optimal_range['min'] );
		$max = floatval( $optimal_range['max'] );

		if ( $numeric_value >= $min && $numeric_value <= $max ) {
			return 'optimal';
		} elseif ( $numeric_value < $min * 0.8 || $numeric_value > $max * 1.2 ) {
			return 'critical';
		} else {
			return 'suboptimal';
		}
	}
	
	/**
	 * Get enhanced status with color coding
	 *
	 * @param float $value Current value
	 * @param array $range_data Range information
	 * @return array Status information
	 */
	public static function get_enhanced_status($value, $range_data) {
		$optimal_min = $range_data['optimal_min'];
		$optimal_max = $range_data['optimal_max'];
		$normal_min = $range_data['normal_min'] ?? $optimal_min;
		$normal_max = $range_data['normal_max'] ?? $optimal_max;
		
		// Determine status
		if ($value >= $optimal_min && $value <= $optimal_max) {
			$status = 'optimal';
			$status_text = 'Optimal';
			$status_class = 'optimal';
		} elseif ($value >= $normal_min && $value <= $normal_max) {
			$status = 'suboptimal';
			$status_text = $value < $optimal_min ? 'Below Optimal' : 'Above Optimal';
			$status_class = $value < $optimal_min ? 'below-optimal' : 'above-optimal';
		} else {
			$status = 'critical';
			$status_text = $value < $normal_min ? 'Below Normal' : 'Above Normal';
			$status_class = 'below-optimal';
		}
		
		return array(
			'status' => $status,
			'status_text' => $status_text,
			'status_class' => $status_class,
			'is_optimal' => $status === 'optimal',
			'is_critical' => $status === 'critical'
		);
	}
	
	/**
	 * Get achievement status for biomarker
	 *
	 * @param float $current_value Current biomarker value
	 * @param float|null $target_value Target value
	 * @param array $range_data Range information
	 * @return array Achievement status
	 */
	public static function get_achievement_status($current_value, $target_value, $range_data) {
		if (!$target_value) {
			return array(
				'status' => 'no-target',
				'text' => 'No Target Set',
				'icon_class' => 'in-progress'
			);
		}
		
		// If no current value but we have a target, it's an educational target
		if (!$current_value && $target_value) {
			return array(
				'status' => 'educational',
				'text' => 'Educational Target',
				'icon_class' => 'educational'
			);
		}
		
		// Check if target is achieved (within 5% tolerance)
		$tolerance = abs($target_value * 0.05);
		$is_achieved = abs($current_value - $target_value) <= $tolerance;
		
		if ($is_achieved) {
			return array(
				'status' => 'achieved',
				'text' => 'Target Achieved',
				'icon_class' => 'achieved'
			);
		} else {
			return array(
				'status' => 'in-progress',
				'text' => 'Working Toward Target',
				'icon_class' => 'in-progress'
			);
		}
	}
	
	/**
	 * Get health vector for biomarker
	 *
	 * @param string $biomarker_id Biomarker identifier
	 * @return string Health vector name
	 */
	public static function get_biomarker_health_vector($biomarker_id) {
		// Define biomarker to health vector mapping
		$vector_mapping = array(
			// Cardiovascular
			'total_cholesterol' => 'Cardiovascular Health',
			'hdl' => 'Cardiovascular Health',
			'ldl' => 'Cardiovascular Health',
			'triglycerides' => 'Cardiovascular Health',
			'crp' => 'Inflammatory Markers',
			'homocysteine' => 'Cardiovascular Health',
			'apob' => 'Cardiovascular Health',
			
			// Endocrine
			'testosterone' => 'Hormonal Balance',
			'estradiol' => 'Hormonal Balance',
			'cortisol' => 'Stress Response',
			'tsh' => 'Thyroid Function',
			't3' => 'Thyroid Function',
			't4' => 'Thyroid Function',
			'insulin' => 'Metabolic Health',
			'dhea_s' => 'Hormonal Balance',
			
			// Immune
			'wbc' => 'Immune Function',
			'esr' => 'Inflammatory Markers',
			'vitamin_d' => 'Immune Function',
			'zinc' => 'Immune Function',
			'selenium' => 'Immune Function',
			'glutathione' => 'Antioxidant Status',
			
			// Nutritional
			'vitamin_b12' => 'Nutritional Status',
			'folate' => 'Nutritional Status',
			'iron' => 'Nutritional Status',
			'magnesium' => 'Nutritional Status',
			'omega_3' => 'Nutritional Status',
			
			// Physical
			'creatine_kinase' => 'Muscle Function',
			'igf_1' => 'Growth & Recovery',
			
			// Cognitive
			'apoe_genotype' => 'Cognitive Health',
			
			// Longevity
			'telomere_length' => 'Cellular Aging',
			'nad_plus' => 'Cellular Energy'
		);
		
		return $vector_mapping[$biomarker_id] ?? 'General Health';
	}
	
	/**
	 * Check if biomarker has admin override
	 *
	 * @param int $user_id User ID
	 * @param string $biomarker_id Biomarker identifier
	 * @return bool True if admin override exists
	 */
	public static function check_admin_override($user_id, $biomarker_id) {
		$admin_overrides = get_user_meta($user_id, 'ennu_admin_biomarker_overrides', true);
		
		if (!is_array($admin_overrides)) {
			return false;
		}
		
		return isset($admin_overrides[$biomarker_id]);
	}
	
	/**
	 * Get user demographic data for range calculations
	 *
	 * @param int $user_id User ID
	 * @return array User demographic data
	 */
	private static function get_user_demographic_data($user_id) {
		$user_data = array();
		
		// Get user's age
		$birth_date = get_user_meta($user_id, 'ennu_birth_date', true);
		if ($birth_date) {
			$birth = new DateTime($birth_date);
			$now = new DateTime();
			$user_data['age'] = $now->diff($birth)->y;
		} else {
			$user_data['age'] = 35; // Default age
		}
		
		// Get user's gender
		$gender = get_user_meta($user_id, 'ennu_gender', true);
		$user_data['gender'] = $gender ?: 'male'; // Default gender
		
		return $user_data;
	}
}
