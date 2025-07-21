<?php
/**
 * ENNU Smart Question Display System
 *
 * Implements intelligent question display logic based on user data,
 * assessment context, and progressive disclosure principles.
 *
 * @package ENNU_Life
 * @version 62.2.8
 * @author ENNU Life Development Team
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Smart_Question_Display {

	/**
	 * Initialize the smart question display system
	 */
	public static function init() {
		add_filter( 'ennu_assessment_questions', array( __CLASS__, 'filter_questions_by_context' ), 10, 3 );
		add_filter( 'ennu_question_display_order', array( __CLASS__, 'optimize_question_order' ), 10, 3 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_smart_display_scripts' ) );
		add_action( 'wp_ajax_ennu_get_next_questions', array( __CLASS__, 'ajax_get_next_questions' ) );
		add_action( 'wp_ajax_nopriv_ennu_get_next_questions', array( __CLASS__, 'ajax_get_next_questions' ) );
	}

	/**
	 * Filter questions based on user context and data completeness
	 *
	 * @param array  $questions Assessment questions
	 * @param string $assessment_type Assessment type
	 * @param int    $user_id User ID
	 * @return array Filtered questions
	 */
	public static function filter_questions_by_context( $questions, $assessment_type, $user_id ) {
		if ( ! $user_id ) {
			return $questions;
		}

		$user_data = ENNU_Progressive_Data_Collector::get_user_global_data( $user_id );
		$filtered_questions = array();

		foreach ( $questions as $question_key => $question_data ) {
			if ( self::should_display_question( $question_key, $question_data, $user_data, $assessment_type ) ) {
				$filtered_questions[ $question_key ] = self::enhance_question_data( $question_data, $user_data );
			}
		}

		return $filtered_questions;
	}

	/**
	 * Determine if a question should be displayed based on context
	 *
	 * @param string $question_key Question key
	 * @param array  $question_data Question configuration
	 * @param array  $user_data User's global data
	 * @param string $assessment_type Assessment type
	 * @return bool Whether to display the question
	 */
	private static function should_display_question( $question_key, $question_data, $user_data, $assessment_type ) {
		if ( isset( $question_data['required'] ) && $question_data['required'] ) {
			if ( isset( $question_data['global_key'] ) ) {
				$global_key = $question_data['global_key'];
				if ( ! isset( $user_data[ $global_key ] ) || empty( $user_data[ $global_key ] ) ) {
					return true;
				}
			} else {
				return true;
			}
		}

		if ( isset( $question_data['display_conditions'] ) ) {
			return self::evaluate_display_conditions( $question_data['display_conditions'], $user_data );
		}

		if ( isset( $question_data['min_age'] ) || isset( $question_data['max_age'] ) ) {
			$age = self::get_user_age( $user_data );
			if ( $age > 0 ) {
				if ( isset( $question_data['min_age'] ) && $age < $question_data['min_age'] ) {
					return false;
				}
				if ( isset( $question_data['max_age'] ) && $age > $question_data['max_age'] ) {
					return false;
				}
			}
		}

		if ( isset( $question_data['gender_specific'] ) && isset( $user_data['gender'] ) ) {
			$allowed_genders = is_array( $question_data['gender_specific'] ) 
				? $question_data['gender_specific'] 
				: array( $question_data['gender_specific'] );
			
			if ( ! in_array( $user_data['gender'], $allowed_genders, true ) ) {
				return false;
			}
		}

		return self::apply_assessment_specific_logic( $question_key, $assessment_type, $user_data );
	}

	/**
	 * Evaluate display conditions using simple rule engine
	 *
	 * @param array $conditions Display conditions
	 * @param array $user_data User data
	 * @return bool Whether conditions are met
	 */
	private static function evaluate_display_conditions( $conditions, $user_data ) {
		foreach ( $conditions as $condition ) {
			$field = $condition['field'] ?? '';
			$operator = $condition['operator'] ?? '=';
			$value = $condition['value'] ?? '';

			if ( ! isset( $user_data[ $field ] ) ) {
				continue;
			}

			$user_value = $user_data[ $field ];

			switch ( $operator ) {
				case '=':
				case '==':
					if ( $user_value !== $value ) {
						return false;
					}
					break;
				case '!=':
					if ( $user_value === $value ) {
						return false;
					}
					break;
				case '>':
					if ( ! is_numeric( $user_value ) || ! is_numeric( $value ) || $user_value <= $value ) {
						return false;
					}
					break;
				case '<':
					if ( ! is_numeric( $user_value ) || ! is_numeric( $value ) || $user_value >= $value ) {
						return false;
					}
					break;
				case 'contains':
					if ( is_array( $user_value ) ) {
						if ( ! in_array( $value, $user_value, true ) ) {
							return false;
						}
					} else {
						if ( strpos( $user_value, $value ) === false ) {
							return false;
						}
					}
					break;
				case 'not_contains':
					if ( is_array( $user_value ) ) {
						if ( in_array( $value, $user_value, true ) ) {
							return false;
						}
					} else {
						if ( strpos( $user_value, $value ) !== false ) {
							return false;
						}
					}
					break;
			}
		}

		return true;
	}

	/**
	 * Apply assessment-specific display logic
	 *
	 * @param string $question_key Question key
	 * @param string $assessment_type Assessment type
	 * @param array  $user_data User data
	 * @return bool Whether to display question
	 */
	private static function apply_assessment_specific_logic( $question_key, $assessment_type, $user_data ) {
		switch ( $assessment_type ) {
			case 'welcome':
				return true;

			case 'testosterone':
				return true;

			case 'hormone':
				return ! isset( $user_data['gender'] ) || 'male' !== $user_data['gender'];

			case 'menopause':
				if ( isset( $user_data['gender'] ) && 'female' !== $user_data['gender'] ) {
					return false;
				}
				$age = self::get_user_age( $user_data );
				return $age >= 35; // Show for women 35+ as perimenopause can start early

			case 'ed-treatment':
				return isset( $user_data['gender'] ) && 'male' === $user_data['gender'];

			case 'weight-loss':
			case 'health':
			case 'hair':
			case 'skin':
			case 'sleep':
				return true;

			default:
				return true;
		}
	}

	/**
	 * Get user's age from date of birth
	 *
	 * @param array $user_data User data
	 * @return int Age in years
	 */
	private static function get_user_age( $user_data ) {
		if ( ! isset( $user_data['user_dob_combined'] ) || empty( $user_data['user_dob_combined'] ) ) {
			return 0;
		}

		$dob = $user_data['user_dob_combined'];
		$birth_date = new DateTime( $dob );
		$current_date = new DateTime();
		$age = $current_date->diff( $birth_date )->y;

		return $age;
	}

	/**
	 * Enhance question data with smart features
	 *
	 * @param array $question_data Original question data
	 * @param array $user_data User data
	 * @return array Enhanced question data
	 */
	private static function enhance_question_data( $question_data, $user_data ) {
		if ( isset( $question_data['global_key'] ) ) {
			$global_key = $question_data['global_key'];
			if ( isset( $user_data[ $global_key ] ) && ! empty( $user_data[ $global_key ] ) ) {
				$question_data['default_value'] = $user_data[ $global_key ];
				$question_data['is_pre_filled'] = true;
			}
		}

		$question_data['contextual_help'] = self::generate_contextual_help( $question_data, $user_data );

		$question_data['display_priority'] = self::calculate_question_priority( $question_data, $user_data );

		return $question_data;
	}

	/**
	 * Generate contextual help text based on user data
	 *
	 * @param array $question_data Question configuration
	 * @param array $user_data User data
	 * @return string Contextual help text
	 */
	private static function generate_contextual_help( $question_data, $user_data ) {
		$help_text = '';

		$age = self::get_user_age( $user_data );
		if ( $age > 0 ) {
			if ( $age >= 40 && strpos( $question_data['title'] ?? '', 'hormone' ) !== false ) {
				$help_text .= 'Hormone levels naturally change after age 40. ';
			}
			if ( $age >= 50 && strpos( $question_data['title'] ?? '', 'energy' ) !== false ) {
				$help_text .= 'Energy optimization becomes increasingly important with age. ';
			}
		}

		if ( isset( $user_data['health_goals'] ) && is_array( $user_data['health_goals'] ) ) {
			if ( in_array( 'weight_loss', $user_data['health_goals'], true ) ) {
				if ( strpos( $question_data['title'] ?? '', 'exercise' ) !== false ) {
					$help_text .= 'Regular exercise is crucial for sustainable weight loss. ';
				}
			}
			if ( in_array( 'longevity', $user_data['health_goals'], true ) ) {
				if ( strpos( $question_data['title'] ?? '', 'sleep' ) !== false ) {
					$help_text .= 'Quality sleep is one of the most important longevity factors. ';
				}
			}
		}

		return trim( $help_text );
	}

	/**
	 * Calculate question display priority
	 *
	 * @param array $question_data Question configuration
	 * @param array $user_data User data
	 * @return int Priority score (higher = more important)
	 */
	private static function calculate_question_priority( $question_data, $user_data ) {
		$priority = 50; // Base priority

		if ( isset( $question_data['required'] ) && $question_data['required'] ) {
			$priority += 30;
		}

		if ( isset( $question_data['global_key'] ) ) {
			$priority += 20;
		}

		if ( isset( $question_data['is_pre_filled'] ) && $question_data['is_pre_filled'] ) {
			$priority -= 15;
		}

		$age = self::get_user_age( $user_data );
		if ( $age > 0 ) {
			if ( isset( $question_data['min_age'] ) && $age >= $question_data['min_age'] ) {
				$priority += 10;
			}
		}

		if ( isset( $user_data['health_goals'] ) && is_array( $user_data['health_goals'] ) ) {
			$question_title = strtolower( $question_data['title'] ?? '' );
			foreach ( $user_data['health_goals'] as $goal ) {
				if ( strpos( $question_title, str_replace( '_', ' ', $goal ) ) !== false ) {
					$priority += 15;
					break;
				}
			}
		}

		return $priority;
	}

	/**
	 * Optimize question display order based on priority and flow
	 *
	 * @param array  $questions Questions array
	 * @param string $assessment_type Assessment type
	 * @param int    $user_id User ID
	 * @return array Reordered questions
	 */
	public static function optimize_question_order( $questions, $assessment_type, $user_id ) {
		if ( ! $user_id ) {
			return $questions;
		}

		$user_data = ENNU_Progressive_Data_Collector::get_user_global_data( $user_id );
		$ordered_questions = array();

		foreach ( $questions as $key => $question ) {
			$questions[ $key ]['_priority'] = self::calculate_question_priority( $question, $user_data );
		}

		uasort( $questions, function( $a, $b ) {
			return ( $b['_priority'] ?? 0 ) - ( $a['_priority'] ?? 0 );
		});

		foreach ( $questions as $key => $question ) {
			unset( $questions[ $key ]['_priority'] );
		}

		return $questions;
	}

	/**
	 * Enqueue smart display scripts
	 */
	public static function enqueue_smart_display_scripts() {
		wp_localize_script(
			'ennu-frontend-forms',
			'ennu_smart_display',
			array(
				'ajax_url'           => admin_url( 'admin-ajax.php' ),
				'nonce'              => wp_create_nonce( 'ennu_smart_display_nonce' ),
				'progressive_config' => array(
					'max_questions_per_step' => 3,
					'auto_advance_delay'     => 300,
					'show_progress_hints'    => true,
					'enable_smart_skipping'  => true,
				),
			)
		);
	}

	/**
	 * AJAX handler to get next questions dynamically
	 */
	public static function ajax_get_next_questions() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_smart_display_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		$assessment_type = sanitize_text_field( $_POST['assessment_type'] ?? '' );
		$current_step = intval( $_POST['current_step'] ?? 0 );
		$user_responses = $_POST['user_responses'] ?? array();

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( 'User not logged in' );
		}

		$next_questions = self::get_dynamic_next_questions( $assessment_type, $current_step, $user_responses, $user_id );

		wp_send_json_success(
			array(
				'questions'     => $next_questions,
				'has_more'      => count( $next_questions ) > 0,
				'progress_hint' => self::generate_progress_hint( $assessment_type, $current_step, $user_id ),
			)
		);
	}

	/**
	 * Get dynamic next questions based on user responses
	 *
	 * @param string $assessment_type Assessment type
	 * @param int    $current_step Current step
	 * @param array  $user_responses User responses so far
	 * @param int    $user_id User ID
	 * @return array Next questions
	 */
	private static function get_dynamic_next_questions( $assessment_type, $current_step, $user_responses, $user_id ) {
		return array();
	}

	/**
	 * Generate progress hint for user
	 *
	 * @param string $assessment_type Assessment type
	 * @param int    $current_step Current step
	 * @param int    $user_id User ID
	 * @return string Progress hint
	 */
	private static function generate_progress_hint( $assessment_type, $current_step, $user_id ) {
		$user_data = ENNU_Progressive_Data_Collector::get_user_global_data( $user_id );
		$completeness = ENNU_Progressive_Data_Collector::calculate_data_completeness( $user_data );

		if ( $completeness['overall_percentage'] >= 80 ) {
			return 'Great! Your profile is nearly complete. Just a few more questions.';
		} elseif ( $completeness['overall_percentage'] >= 60 ) {
			return 'You\'re making good progress! These questions will help us provide better recommendations.';
		} elseif ( $completeness['critical_percentage'] >= 100 ) {
			return 'Essential information captured! Additional details will enhance your results.';
		} else {
			return 'These questions help us understand your health goals and provide personalized recommendations.';
		}
	}
}

ENNU_Smart_Question_Display::init();
