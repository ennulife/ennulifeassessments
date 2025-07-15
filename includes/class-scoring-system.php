<?php
/**
 * ENNU Life Assessment Scoring System
 *
 * This class is responsible for calculating scores based on the unified
 * assessment definitions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Assessment_Scoring {

	private static $all_definitions = array();

	private static function load_all_definitions() {
		if ( empty( self::$all_definitions ) ) {
			$definitions_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessment-definitions.php';
			if ( file_exists( $definitions_file ) ) {
				self::$all_definitions = require $definitions_file;
			}
		}
	}

	public static function calculate_scores( $assessment_type, $responses ) {
		self::load_all_definitions();
		$assessment_questions = self::$all_definitions[ $assessment_type ] ?? array();

		if ( empty( $assessment_questions ) ) {
			return false;
		}

		$category_scores = array();
		$total_score     = 0;
		$total_weight    = 0;

		// This is the definitive fix. We must check for a nested 'questions' array.
		$questions_to_iterate = isset( $assessment_questions['questions'] ) ? $assessment_questions['questions'] : $assessment_questions;

		foreach ( $questions_to_iterate as $question_key => $question_def ) {
			// --- DEFINITIVE FIX: Check if the response for this question exists before processing ---
			if ( ! isset( $responses[ $question_key ] ) ) {
				continue;
			}
			$answer = $responses[ $question_key ];

			// Gracefully handle cases where the question definition might not be found.
			if ( ! isset( $questions_to_iterate[ $question_key ] ) ) {
				continue;
			}
			$question_def = $questions_to_iterate[ $question_key ];

			if ( isset( $question_def['scoring'] ) ) {
				$scoring_rules = $question_def['scoring'];
				$category      = $scoring_rules['category'] ?? 'General';
				$weight        = $scoring_rules['weight'] ?? 1;

				$answers_to_process = is_array( $answer ) ? $answer : array( $answer );

				foreach ( $answers_to_process as $single_answer ) {
					$score = $scoring_rules['answers'][ $single_answer ] ?? 0;

					if ( ! isset( $category_scores[ $category ] ) ) {
						$category_scores[ $category ] = array(
							'total'  => 0,
							'weight' => 0,
							'count'  => 0,
						);
					}

					$category_scores[ $category ]['total']  += $score * $weight;
					$category_scores[ $category ]['weight'] += $weight;
					$category_scores[ $category ]['count']++;

					if ( $weight > 0 ) {
						$total_score  += $score * $weight;
						$total_weight += $weight;
					}
				}
			}
		}

		$final_category_scores = array();
		foreach ( $category_scores as $category => $data ) {
			if ( $data['weight'] > 0 ) {
				$final_category_scores[ $category ] = round( $data['total'] / $data['weight'], 1 );
			}
		}

		$overall_score = $total_weight > 0 ? round( $total_score / $total_weight, 1 ) : 0;

		// --- PHASE 2: CALCULATE PILLAR SCORES ---
		$pillar_map    = self::get_health_pillar_map();
		$pillar_scores = array();
		$pillar_totals = array();
		$pillar_counts = array();

		foreach ( $pillar_map as $pillar_name => $categories ) {
			$pillar_totals[ $pillar_name ] = 0;
			$pillar_counts[ $pillar_name ] = 0;
		}

		foreach ( $final_category_scores as $category => $score ) {
			foreach ( $pillar_map as $pillar_name => $categories ) {
				if ( in_array( $category, $categories ) ) {
					$pillar_totals[ $pillar_name ] += $score;
					$pillar_counts[ $pillar_name ]++;
					break; // Move to the next category once pillar is found
				}
			}
		}

		foreach ( $pillar_totals as $pillar_name => $total ) {
			if ( $pillar_counts[ $pillar_name ] > 0 ) {
				$pillar_scores[ $pillar_name ] = round( $total / $pillar_counts[ $pillar_name ], 1 );
			} else {
				$pillar_scores[ $pillar_name ] = 0; // Default to 0 if no categories for this pillar
			}
		}
		// --- END PHASE 2 ---

		return array(
			'overall_score'   => $overall_score,
			'category_scores' => $final_category_scores,
			'pillar_scores'   => $pillar_scores, // Add pillar scores to the return value
		);
	}

	public static function get_answer_score( $assessment_type, $question_key, $answer_value ) {
		self::load_all_definitions();
		$assessment_questions = self::$all_definitions[ $assessment_type ] ?? array();
		$question_def         = $assessment_questions[ $question_key ] ?? null;

		if ( $question_def && isset( $question_def['scoring']['answers'][ $answer_value ] ) ) {
			return $question_def['scoring']['answers'][ $answer_value ];
		}

		return null;
	}

	/**
	 * Get category definitions for assessment type
	 */
	public static function get_category_definitions( $assessment_type ) {
		switch ( $assessment_type ) {
			case 'hair_assessment':
				return array(
					'Hair Health Status'     => 'Current condition and severity of hair concerns',
					'Progression Timeline'   => 'How long hair changes have been occurring',
					'Progression Rate'       => 'Speed of hair loss or changes',
					'Genetic Factors'        => 'Family history and genetic predisposition',
					'Lifestyle Factors'      => 'Stress levels and lifestyle impact',
					'Nutritional Support'    => 'Diet quality and nutritional factors',
					'Treatment History'      => 'Previous treatments and experiences',
					'Treatment Expectations' => 'Goals and realistic expectations',
				);

			case 'ed_treatment_assessment':
				return array(
					'Condition Severity'    => 'Severity and frequency of erectile dysfunction',
					'Medical Factors'       => 'Underlying health conditions affecting ED',
					'Physical Health'       => 'Exercise habits and cardiovascular fitness',
					'Psychological Factors' => 'Stress levels and mental health impact',
					'Psychosocial Factors'  => 'Relationship status and social factors',
					'Treatment Motivation'  => 'Goals and motivation for treatment',
					'Drug Interactions'     => 'Current medications and potential interactions',
				);

			case 'weight_loss_assessment':
				return array(
					'Current Status'        => 'Current weight status and starting point',
					'Physical Activity'     => 'Exercise frequency and activity levels',
					'Nutrition'             => 'Diet quality and eating habits',
					'Behavioral Patterns'   => 'Eating behaviors and patterns',
					'Lifestyle Factors'     => 'Sleep quality and lifestyle choices',
					'Psychological Factors' => 'Stress levels and mental health',
					'Social Support'        => 'Support system and environment',
					'Motivation & Goals'    => 'Motivation level and weight loss goals',
					'Weight Loss History'   => 'Previous attempts and experiences',
				);

			case 'health_assessment':
				return array(
					'Current Health Status'  => 'Overall health and wellness baseline',
					'Vitality & Energy'      => 'Energy levels and daily vitality',
					'Physical Activity'      => 'Exercise habits and fitness level',
					'Nutrition'              => 'Diet quality and nutritional habits',
					'Sleep & Recovery'       => 'Sleep quality and recovery patterns',
					'Stress & Mental Health' => 'Stress management and mental wellness',
					'Preventive Health'      => 'Preventive care and health monitoring',
					'Health Motivation'      => 'Health goals and motivation for improvement',
				);

			case 'skin_assessment':
				return array(
					'Skin Characteristics'    => 'Natural skin type and characteristics',
					'Skin Issues'             => 'Current skin concerns and conditions',
					'Environmental Factors'   => 'Sun exposure and environmental impact',
					'Skincare Habits'         => 'Current skincare routine and practices',
					'Skin Reactivity'         => 'Sensitivity and product reactions',
					'Lifestyle Impact'        => 'Lifestyle factors affecting skin health',
					'Treatment Accessibility' => 'Budget and treatment options',
					'Treatment Goals'         => 'Skincare goals and expectations',
				);

			default:
				return array();
		}
	}

	/**
	 * Get score interpretation
	 */
	public static function get_score_interpretation( $score ) {
		if ( $score >= 8.5 ) {
			return array(
				'level'       => 'Excellent',
				'color'       => '#10b981',
				'description' => 'Outstanding results expected with minimal intervention needed.',
			);
		} elseif ( $score >= 7.0 ) {
			return array(
				'level'       => 'Good',
				'color'       => '#3b82f6',
				'description' => 'Good foundation with some areas for optimization.',
			);
		} elseif ( $score >= 5.5 ) {
			return array(
				'level'       => 'Fair',
				'color'       => '#f59e0b',
				'description' => 'Moderate intervention recommended for best results.',
			);
		} elseif ( $score >= 3.5 ) {
			return array(
				'level'       => 'Needs Attention',
				'color'       => '#ef4444',
				'description' => 'Significant improvements recommended for optimal outcomes.',
			);
		} else {
			return array(
				'level'       => 'Critical',
				'color'       => '#dc2626',
				'description' => 'Immediate comprehensive intervention strongly recommended.',
			);
		}
	}

	public static function get_health_pillar_map() {
		return array(
			'mind'       => array(
				// Original categories
				'Psychological Factors',
				'Treatment Motivation',
				'Stress & Mental Health',
				'Readiness for Change',
				'Treatment Expectations',
				'Social Support',
				'Motivation & Goals',
				'Health Motivation',
				'Vitality & Drive',
				'Mental Clarity',
				'Mood & Wellbeing',
				// Missing categories from actual assessments
				'Psychosocial Factors',    // ED treatment
				'Mood & Cognition',        // Hormone
				'Mental Acuity',           // Hormone
				'Timeline',                // ED treatment (motivation related)
			),
			'body'       => array(
				// Original categories
				'Condition Severity',
				'Medical Factors',
				'Drug Interactions',
				'Genetic Factors',
				'Nutritional Support',
				'Internal Health',
				'Current Health Status',
				'Vitality & Energy',
				'Sleep & Recovery',
				'Anabolic Response',
				'Symptom Severity',
				'Menopause Stage',
				'Physical Symptoms',
				// Missing categories from actual assessments
				'Demographics',            // Used in multiple assessments
				'Current Status',          // Weight loss
				'Vitality',                // Hormone
				'Hair Health Status',      // Hair (moved from aesthetics to body for health aspect)
			),
			'lifestyle'  => array(
				// Original categories
				'Physical Health',
				'Treatment History',
				'Progression Timeline',
				'Symptom Pattern',
				'Preventive Health',
				'Lifestyle Choices',
				'Environmental Factors',
				'Skincare Habits',
				'Weight Loss History',
				'Behavioral Patterns',
				'Physical Activity',
				'Nutrition',
				'Sleep Duration',
				'Sleep Quality',
				'Sleep Continuity',
				'Sleep Dependency',
				'Current Regimen',
				'Lifestyle & Diet',
				// Missing categories from actual assessments
				'Lifestyle Factors',       // Hair
				'Hydration',               // Skin
				'Sleep Latency',           // Sleep
				'Daytime Function',        // Sleep
				'Sleep Hygiene',           // Sleep
				'Diet & Lifestyle',        // Hormone
			),
			'aesthetics' => array(
				// Original categories (removed Hair Health Status)
				'Primary Skin Issue',
				'Skin Characteristics',
				'Current Status',
				'Progression Rate',
				'Skin Reactivity',
				// Aesthetic-specific categories
				'Hair Health Status',      // Hair appearance
				'Advanced Care',           // Skin
			),
		);
	}

	public static function get_symptom_data_for_user( $user_id ) {
		self::load_all_definitions();
		$symptom_data = array();
		$symptom_questions = self::$all_definitions['health_optimization_assessment'] ?? array();

		foreach ( $symptom_questions as $q_id => $q_def ) {
			if ( strpos( $q_id, 'symptom_' ) === 0 ) {
				$severity_key = str_replace( '_q', '_severity_q', $q_id );
				$frequency_key = str_replace( '_q', '_frequency_q', $q_id );
				
				$symptom_value = get_user_meta( $user_id, 'ennu_health_optimization_assessment_' . $q_id, true );
				if ( !empty($symptom_value) ) {
					$symptom_data[ $q_id ] = array(
						'selection' => $symptom_value,
						'severity' => get_user_meta( $user_id, 'ennu_health_optimization_assessment_' . $severity_key, true ),
						'frequency' => get_user_meta( $user_id, 'ennu_health_optimization_assessment_' . $frequency_key, true ),
					);
				}
			}
		}
		return $symptom_data;
	}

	private static function _get_health_optimization_mappings() {
		$mappings_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization-mappings.php';
		if ( file_exists( $mappings_file ) ) {
			return require $mappings_file;
		}
		return array();
	}

	private static function _calculate_pillar_integrity_penalties( $user_id ) {
		$mappings = self::_get_health_optimization_mappings();
		$symptom_data = self::get_symptom_data_for_user( $user_id );

		$vector_map = $mappings['symptom_to_vector_map'] ?? array();
		$penalty_matrix = $mappings['pillar_integrity_penalty_matrix'] ?? array();

		$triggered_vectors = array();

		foreach($symptom_data as $q_id => $data) {
			if (empty($data['selection'])) continue;

			$symptoms = is_array($data['selection']) ? $data['selection'] : array($data['selection']);
			foreach($symptoms as $symptom) {
				if (isset($vector_map[$symptom])) {
					foreach($vector_map[$symptom] as $vector) {
						if (!isset($triggered_vectors[$vector])) {
							$triggered_vectors[$vector] = array();
						}
						$triggered_vectors[$vector][] = array(
							'severity' => $data['severity'],
							'frequency' => $data['frequency']
						);
					}
				}
			}
		}
		
		$pillar_penalties = array(
			'mind' => 0,
			'body' => 0,
			'lifestyle' => 0,
			'aesthetics' => 0,
		);

		foreach($triggered_vectors as $vector => $instances) {
			if (isset($penalty_matrix[$vector])) {
				$vector_config = $penalty_matrix[$vector];
				$pillar_impact = $vector_config['pillar_impact'];
				$max_penalty_for_vector = 0;

				foreach($instances as $instance) {
					$severity = $instance['severity'] ?? 'Mild';
					$frequency = $instance['frequency'] ?? 'Monthly';
					$penalty = $vector_config['penalties'][$severity][$frequency] ?? 0;
					if ($penalty > $max_penalty_for_vector) {
						$max_penalty_for_vector = $penalty;
					}
				}

				if ($max_penalty_for_vector > $pillar_penalties[$pillar_impact]) {
					$pillar_penalties[$pillar_impact] = $max_penalty_for_vector;
				}
			}
		}

		return $pillar_penalties;
	}


	public static function calculate_ennu_life_score( $user_id, $force_recalc = false ) {
		self::load_all_definitions();

		$assessment_types  = array_keys( self::$all_definitions );
		$all_pillar_scores = array();

		// Initialize accumulators for each pillar
		foreach ( self::get_health_pillar_map() as $pillar_key => $categories ) {
			$all_pillar_scores[ $pillar_key ] = array();
		}

		// 1. Collect all pillar scores from all completed assessments
		foreach ( $assessment_types as $assessment_type ) {
			if ( 'health_optimization_assessment' === $assessment_type ) {
                continue;
            }
			$pillar_scores = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_pillar_scores', true );
			if ( is_array( $pillar_scores ) && ! empty( $pillar_scores ) ) {
				foreach ( $pillar_scores as $pillar_name => $score ) {
					if ( isset( $all_pillar_scores[ $pillar_name ] ) ) {
						$all_pillar_scores[ $pillar_name ][] = $score;
					}
				}
			}
		}

		// 2. Calculate the average for each pillar to get the BASE score
		$base_pillar_scores = array();
		foreach ( $all_pillar_scores as $pillar_name => $scores ) {
			if ( ! empty( $scores ) ) {
				$base_pillar_scores[ $pillar_name ] = array_sum( $scores ) / count( $scores );
			} else {
				$base_pillar_scores[ $pillar_name ] = 0;
			}
		}

		// 3. Calculate Pillar Integrity Penalties
		$pillar_penalties = self::_calculate_pillar_integrity_penalties( $user_id );

		// 4. Apply Penalties to get the Final Adjusted Pillar Scores
		$final_pillar_scores = array();
		$pillar_score_data = array();
		foreach($base_pillar_scores as $pillar_name => $base_score) {
			$penalty = $pillar_penalties[$pillar_name] ?? 0;
			$final_score = $base_score * (1 - $penalty);
			$final_pillar_scores[$pillar_name] = $final_score;

			$pillar_score_data[$pillar_name] = array(
				'base' => round($base_score, 1),
				'penalty' => round($penalty * 100, 0), // store as percentage
				'final' => round($final_score, 1),
			);
		}

		// 5. Apply strategic weights to the FINAL scores
		$weights = array(
			'mind'       => 0.3,
			'body'       => 0.3,
			'lifestyle'  => 0.3,
			'aesthetics' => 0.1,
		);

		$ennu_life_score = 0;
		foreach ( $final_pillar_scores as $pillar_name => $final_score ) {
			if ( isset( $weights[ $pillar_name ] ) ) {
				$ennu_life_score += $final_score * $weights[ $pillar_name ];
			}
		}

		// Save the detailed score data for transparency
		update_user_meta( $user_id, 'ennu_pillar_score_data', $pillar_score_data );
		
		// Fix capitalization for dashboard display
		$capitalized_pillar_scores = array();
		foreach ( $final_pillar_scores as $pillar_name => $score ) {
			$capitalized_pillar_scores[ ucfirst( $pillar_name ) ] = round( $score, 1 );
		}
		update_user_meta( $user_id, 'ennu_average_pillar_scores', $capitalized_pillar_scores );

		return round( $ennu_life_score, 1 );
	}

	public static function calculate_average_pillar_scores( $user_id ) {
		self::load_all_definitions();

		$assessment_types  = array_keys( self::$all_definitions );
		$all_pillar_scores = array();

		foreach ( self::get_health_pillar_map() as $pillar_key => $categories ) {
			$all_pillar_scores[ $pillar_key ] = array();
		}

		foreach ( $assessment_types as $assessment_type ) {
			$pillar_scores = get_user_meta( $user_id, 'ennu_' . $assessment_type . '_pillar_scores', true );
			if ( is_array( $pillar_scores ) && ! empty( $pillar_scores ) ) {
				foreach ( $pillar_scores as $pillar_name => $score ) {
					if ( isset( $all_pillar_scores[ $pillar_name ] ) ) {
						$all_pillar_scores[ $pillar_name ][] = $score;
					}
				}
			}
		}

		$average_pillar_scores = array();
		foreach ( $all_pillar_scores as $pillar_name => $scores ) {
			if ( ! empty( $scores ) ) {
				$average_pillar_scores[ ucfirst( $pillar_name ) ] = round( array_sum( $scores ) / count( $scores ), 1 );
			} else {
				$average_pillar_scores[ ucfirst( $pillar_name ) ] = 0;
			}
		}

		return $average_pillar_scores;
	}

	public static function get_health_optimization_report_data( $form_data ) {
		$mappings = self::_get_health_optimization_mappings();
		$symptom_map = $mappings['symptom_to_vector_map'] ?? array();
		$biomarker_map = $mappings['vector_to_biomarker_map'] ?? array();
		
		// 1. Get all user-selected symptoms from the form data.
		$user_symptoms = array();
		if ( !empty( $form_data ) ) {
			foreach ( $form_data as $key => $value ) {
				if ( strpos( $key, 'symptom_q' ) === 0 && ! empty( $value ) ) {
					$symptoms_list = ( isset( $value['selection'] ) && is_array( $value['selection'] ) ) ? $value['selection'] : (array) $value;
					// Convert symptom keys to display values
					foreach ( $symptoms_list as $symptom_key ) {
						// Map lowercase keys to capitalized display values
						$symptom_display = str_replace( '_', ' ', $symptom_key );
						$symptom_display = ucwords( $symptom_display );
						$user_symptoms[] = $symptom_display;
					}
				}
			}
			$user_symptoms = array_unique( $user_symptoms );
		}
		
		// 2. Build the complete health map: a structured array of all vectors, symptoms, and biomarkers.
		$health_map = array();
		foreach ( $biomarker_map as $vector => $biomarkers ) {
			$health_map[ $vector ] = array(
				'symptoms'   => array(),
				'biomarkers' => $biomarkers,
			);
		}
		
		foreach ( $symptom_map as $symptom => $vectors ) {
			foreach ( $vectors as $vector ) {
				if ( isset( $health_map[ $vector ] ) ) {
					$health_map[ $vector ]['symptoms'][] = $symptom;
				}
			}
		}
		
		// 3. Determine which parts of the map were triggered by the user.
		$triggered_vectors = array();
		$recommended_biomarkers = array();
		if ( !empty( $user_symptoms ) ) {
			foreach ( $user_symptoms as $symptom ) {
				if ( isset( $symptom_map[ $symptom ] ) ) {
					foreach ( $symptom_map[ $symptom ] as $vector ) {
						if ( ! in_array( $vector, $triggered_vectors ) ) {
							$triggered_vectors[] = $vector;
						}
						if ( isset( $biomarker_map[ $vector ] ) ) {
							$recommended_biomarkers = array_merge( $recommended_biomarkers, $biomarker_map[ $vector ] );
						}
					}
				}
			}
			$recommended_biomarkers = array_unique( $recommended_biomarkers );
			sort( $recommended_biomarkers );
		}
		
		// 4. Return the complete map and the user's specific triggered data.
		return array(
			'health_map'             => $health_map,
			'user_symptoms'          => $user_symptoms,
			'triggered_vectors'      => $triggered_vectors,
			'recommended_biomarkers' => $recommended_biomarkers,
		);
	}
}

