<?php
/**
 * ENNU Smart Question Display System
 * Intelligently displays questions based on user context and assessment flow
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Smart_Question_Display {

	/**
	 * Initialize smart question display hooks
	 */
	public static function init() {
		add_action( 'wp_ajax_ennu_get_smart_questions', array( __CLASS__, 'handle_get_smart_questions' ) );
		add_action( 'wp_ajax_nopriv_ennu_get_smart_questions', array( __CLASS__, 'handle_get_smart_questions' ) );
		add_filter( 'ennu_assessment_questions', array( __CLASS__, 'filter_assessment_questions' ), 10, 3 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue smart question display scripts
	 */
	public static function enqueue_scripts() {
		wp_enqueue_script(
			'ennu-smart-question-display',
			plugin_dir_url( __FILE__ ) . '../assets/js/smart-question-display.js',
			array( 'jquery' ),
			'62.2.8',
			true
		);

		wp_localize_script(
			'ennu-smart-question-display',
			'ennuSmartQuestions',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ennu_smart_questions_nonce' ),
				'strings' => array(
					'loading'  => __( 'Loading questions...', 'ennu-life' ),
					'error'    => __( 'Error loading questions', 'ennu-life' ),
					'skip'     => __( 'Skip this question', 'ennu-life' ),
					'back'     => __( 'Previous question', 'ennu-life' ),
					'next'     => __( 'Next question', 'ennu-life' ),
					'complete' => __( 'Complete assessment', 'ennu-life' ),
				),
			)
		);
	}

	/**
	 * Handle get smart questions via AJAX
	 */
	public static function handle_get_smart_questions() {
		check_ajax_referer( 'ennu_smart_questions_nonce', 'nonce' );

		$user_id          = get_current_user_id();
		$assessment_type  = sanitize_text_field( $_POST['assessment_type'] ?? '' );
		$current_question = sanitize_text_field( $_POST['current_question'] ?? '' );
		$user_answers     = $_POST['user_answers'] ?? array();

		$user_answers = array_map( 'sanitize_text_field', $user_answers );

		$smart_questions = self::get_smart_questions_for_user( $user_id, $assessment_type, $current_question, $user_answers );

		wp_send_json_success(
			array(
				'questions'       => $smart_questions,
				'progress'        => self::calculate_assessment_progress( $user_answers, $assessment_type ),
				'recommendations' => self::get_question_recommendations( $user_id, $assessment_type, $user_answers ),
			)
		);
	}

	/**
	 * Filter assessment questions based on smart display logic
	 *
	 * @param array $questions Original questions
	 * @param string $assessment_type Assessment type
	 * @param int $user_id User ID
	 * @return array Filtered questions
	 */
	public static function filter_assessment_questions( $questions, $assessment_type, $user_id ) {
		if ( ! $user_id ) {
			return $questions;
		}

		$user_data          = self::get_user_context_data( $user_id );
		$filtered_questions = array();

		foreach ( $questions as $question_key => $question_config ) {
			if ( self::should_display_question( $question_key, $question_config, $user_data, $assessment_type ) ) {
				$filtered_questions[ $question_key ] = $question_config;
			}
		}

		return $filtered_questions;
	}

	/**
	 * Get smart questions for user based on context
	 *
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @param string $current_question Current question key
	 * @param array $user_answers User's current answers
	 * @return array Smart questions
	 */
	private static function get_smart_questions_for_user( $user_id, $assessment_type, $current_question, $user_answers ) {
		$user_data         = self::get_user_context_data( $user_id );
		$assessment_config = self::get_assessment_config( $assessment_type );

		if ( empty( $assessment_config['questions'] ) ) {
			return array();
		}

		$questions       = $assessment_config['questions'];
		$smart_questions = array();

		$filtered_questions = self::apply_smart_filtering( $questions, $user_data, $user_answers, $assessment_type );
		$ordered_questions  = self::apply_smart_ordering( $filtered_questions, $user_data, $user_answers );

		$next_questions = self::get_next_questions( $ordered_questions, $current_question, $user_answers );

		foreach ( $next_questions as $question_key => $question_config ) {
			$smart_questions[] = array(
				'key'            => $question_key,
				'title'          => $question_config['title'],
				'type'           => $question_config['type'],
				'options'        => $question_config['options'] ?? array(),
				'required'       => $question_config['required'] ?? false,
				'help_text'      => $question_config['help_text'] ?? '',
				'smart_context'  => self::get_question_smart_context( $question_key, $user_data, $assessment_type ),
				'estimated_time' => $question_config['estimated_time'] ?? '30 seconds',
			);
		}

		return $smart_questions;
	}

	/**
	 * Apply smart filtering to questions
	 *
	 * @param array $questions Questions array
	 * @param array $user_data User context data
	 * @param array $user_answers User answers
	 * @param string $assessment_type Assessment type
	 * @return array Filtered questions
	 */
	private static function apply_smart_filtering( $questions, $user_data, $user_answers, $assessment_type ) {
		$filtered_questions = array();

		foreach ( $questions as $question_key => $question_config ) {
			if ( isset( $user_answers[ $question_key ] ) ) {
				continue;
			}

			if ( ! self::is_question_relevant_to_user( $question_key, $question_config, $user_data, $assessment_type ) ) {
				continue;
			}

			if ( ! self::check_question_conditions( $question_config, $user_answers, $user_data ) ) {
				continue;
			}

			$filtered_questions[ $question_key ] = $question_config;
		}

		return $filtered_questions;
	}

	/**
	 * Apply smart ordering to questions
	 *
	 * @param array $questions Questions array
	 * @param array $user_data User context data
	 * @param array $user_answers User answers
	 * @return array Ordered questions
	 */
	private static function apply_smart_ordering( $questions, $user_data, $user_answers ) {
		$ordered_questions = $questions;

		uasort(
			$ordered_questions,
			function( $a, $b ) use ( $user_data ) {
				$priority_a = $a['smart_priority'] ?? self::calculate_question_priority( $a, $user_data );
				$priority_b = $b['smart_priority'] ?? self::calculate_question_priority( $b, $user_data );

				return $priority_b - $priority_a; // Higher priority first
			}
		);

		return $ordered_questions;
	}

	/**
	 * Get next questions to display
	 *
	 * @param array $ordered_questions Ordered questions
	 * @param string $current_question Current question key
	 * @param array $user_answers User answers
	 * @return array Next questions
	 */
	private static function get_next_questions( $ordered_questions, $current_question, $user_answers ) {
		$max_questions  = 3; // Show up to 3 questions at a time
		$next_questions = array();

		if ( empty( $current_question ) ) {
			return array_slice( $ordered_questions, 0, $max_questions, true );
		}

		$question_keys = array_keys( $ordered_questions );
		$current_index = array_search( $current_question, $question_keys, true );

		if ( $current_index !== false ) {
			$start_index    = $current_index + 1;
			$next_questions = array_slice( $ordered_questions, $start_index, $max_questions, true );
		}

		return $next_questions;
	}

	/**
	 * Check if question should be displayed
	 *
	 * @param string $question_key Question key
	 * @param array $question_config Question configuration
	 * @param array $user_data User data
	 * @param string $assessment_type Assessment type
	 * @return bool Whether to display question
	 */
	private static function should_display_question( $question_key, $question_config, $user_data, $assessment_type ) {
		if ( isset( $question_config['gender_specific'] ) ) {
			$user_gender = $user_data['gender'] ?? '';
			if ( ! empty( $user_gender ) && $question_config['gender_specific'] !== $user_gender ) {
				return false;
			}
		}

		if ( isset( $question_config['age_range'] ) ) {
			$user_age = intval( $user_data['age'] ?? 0 );
			if ( $user_age > 0 ) {
				$age_range = $question_config['age_range'];
				if ( $user_age < $age_range['min'] || $user_age > $age_range['max'] ) {
					return false;
				}
			}
		}

		if ( isset( $question_config['assessment_types'] ) ) {
			if ( ! in_array( $assessment_type, $question_config['assessment_types'], true ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if question is relevant to user
	 *
	 * @param string $question_key Question key
	 * @param array $question_config Question configuration
	 * @param array $user_data User data
	 * @param string $assessment_type Assessment type
	 * @return bool Whether question is relevant
	 */
	private static function is_question_relevant_to_user( $question_key, $question_config, $user_data, $assessment_type ) {
		$basic_questions = array( 'age', 'gender', 'height', 'weight' );
		if ( in_array( $question_key, $basic_questions, true ) ) {
			return true;
		}

		$relevance_rules = self::get_question_relevance_rules();

		if ( isset( $relevance_rules[ $question_key ] ) ) {
			$rules = $relevance_rules[ $question_key ];

			foreach ( $rules as $rule ) {
				if ( ! self::evaluate_relevance_rule( $rule, $user_data, $assessment_type ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Check question conditions
	 *
	 * @param array $question_config Question configuration
	 * @param array $user_answers User answers
	 * @param array $user_data User data
	 * @return bool Whether conditions are met
	 */
	private static function check_question_conditions( $question_config, $user_answers, $user_data ) {
		if ( empty( $question_config['conditions'] ) ) {
			return true;
		}

		foreach ( $question_config['conditions'] as $condition ) {
			if ( ! self::evaluate_condition( $condition, $user_answers, $user_data ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Calculate question priority
	 *
	 * @param array $question_config Question configuration
	 * @param array $user_data User data
	 * @return int Priority score
	 */
	private static function calculate_question_priority( $question_config, $user_data ) {
		$base_priority = $question_config['priority'] ?? 5;

		if ( isset( $question_config['priority_modifiers'] ) ) {
			foreach ( $question_config['priority_modifiers'] as $modifier ) {
				if ( self::evaluate_priority_modifier( $modifier, $user_data ) ) {
					$base_priority += $modifier['adjustment'];
				}
			}
		}

		return max( 1, min( 10, $base_priority ) ); // Keep within 1-10 range
	}

	/**
	 * Get question smart context
	 *
	 * @param string $question_key Question key
	 * @param array $user_data User data
	 * @param string $assessment_type Assessment type
	 * @return array Smart context
	 */
	private static function get_question_smart_context( $question_key, $user_data, $assessment_type ) {
		$context = array(
			'personalization' => '',
			'why_asking'      => '',
			'relevance_score' => 5,
		);

		if ( ! empty( $user_data['age'] ) ) {
			$age = intval( $user_data['age'] );
			if ( $age < 30 ) {
				$context['personalization'] = 'This is particularly important for younger adults.';
			} elseif ( $age > 50 ) {
				$context['personalization'] = 'This becomes more significant as we age.';
			}
		}

		$assessment_contexts = array(
			'testosterone' => 'This helps us understand your hormonal health profile.',
			'menopause'    => 'This information helps us assess your menopausal transition.',
			'weight_loss'  => 'This data helps us create your personalized weight management plan.',
			'longevity'    => 'This contributes to your overall longevity assessment.',
		);

		if ( isset( $assessment_contexts[ $assessment_type ] ) ) {
			$context['why_asking'] = $assessment_contexts[ $assessment_type ];
		}

		return $context;
	}

	/**
	 * Get user context data
	 *
	 * @param int $user_id User ID
	 * @return array User context data
	 */
	private static function get_user_context_data( $user_id ) {
		$context_fields = array( 'age', 'gender', 'height', 'weight', 'activity_level', 'health_goals' );
		$context_data   = array();

		foreach ( $context_fields as $field ) {
			$value = get_user_meta( $user_id, 'ennu_' . $field, true );
			if ( ! empty( $value ) ) {
				$context_data[ $field ] = $value;
			}
		}

		return $context_data;
	}

	/**
	 * Get assessment configuration
	 *
	 * @param string $assessment_type Assessment type
	 * @return array Assessment configuration
	 */
	private static function get_assessment_config( $assessment_type ) {
		$config_file = plugin_dir_path( __FILE__ ) . 'config/assessments/' . $assessment_type . '.php';

		if ( file_exists( $config_file ) ) {
			return include $config_file;
		}

		return array();
	}

	/**
	 * Calculate assessment progress
	 *
	 * @param array $user_answers User answers
	 * @param string $assessment_type Assessment type
	 * @return array Progress data
	 */
	private static function calculate_assessment_progress( $user_answers, $assessment_type ) {
		$assessment_config  = self::get_assessment_config( $assessment_type );
		$total_questions    = count( $assessment_config['questions'] ?? array() );
		$answered_questions = count( $user_answers );

		$percentage = $total_questions > 0 ? intval( round( ( $answered_questions / $total_questions ) * 100 ) ) : 0;

		return array(
			'answered'                 => $answered_questions,
			'total'                    => $total_questions,
			'percentage'               => $percentage,
			'estimated_time_remaining' => max( 0, ( $total_questions - $answered_questions ) * 30 ), // 30 seconds per question
		);
	}

	/**
	 * Get question recommendations
	 *
	 * @param int $user_id User ID
	 * @param string $assessment_type Assessment type
	 * @param array $user_answers User answers
	 * @return array Recommendations
	 */
	private static function get_question_recommendations( $user_id, $assessment_type, $user_answers ) {
		$recommendations = array();

		if ( count( $user_answers ) > 5 ) {
			$recommendations[] = array(
				'type'    => 'progress',
				'message' => 'Great progress! You\'re building a comprehensive health profile.',
				'icon'    => 'progress',
			);
		}

		if ( $assessment_type === 'testosterone' && isset( $user_answers['testosterone_q1'] ) ) {
			$symptoms = $user_answers['testosterone_q1'];
			if ( is_array( $symptoms ) && count( $symptoms ) > 3 ) {
				$recommendations[] = array(
					'type'    => 'insight',
					'message' => 'Based on your symptoms, we recommend completing the full assessment for accurate insights.',
					'icon'    => 'insight',
				);
			}
		}

		return $recommendations;
	}

	/**
	 * Get question relevance rules
	 *
	 * @return array Relevance rules
	 */
	private static function get_question_relevance_rules() {
		return array(
			'testosterone_symptoms' => array(
				array(
					'type'  => 'gender',
					'value' => array( 'male', 'non_binary' ),
				),
				array(
					'type' => 'age',
					'min'  => 18,
				),
			),
			'menopause_symptoms'    => array(
				array(
					'type'  => 'gender',
					'value' => array( 'female', 'non_binary' ),
				),
				array(
					'type' => 'age',
					'min'  => 35,
				),
			),
			'pregnancy_related'     => array(
				array(
					'type'  => 'gender',
					'value' => array( 'female' ),
				),
				array(
					'type' => 'age',
					'min'  => 18,
					'max'  => 50,
				),
			),
		);
	}

	/**
	 * Evaluate relevance rule
	 *
	 * @param array $rule Relevance rule
	 * @param array $user_data User data
	 * @param string $assessment_type Assessment type
	 * @return bool Whether rule passes
	 */
	private static function evaluate_relevance_rule( $rule, $user_data, $assessment_type ) {
		switch ( $rule['type'] ) {
			case 'gender':
				$user_gender = $user_data['gender'] ?? '';
				return in_array( $user_gender, $rule['value'], true );

			case 'age':
				$user_age = intval( $user_data['age'] ?? 0 );
				$min_age  = $rule['min'] ?? 0;
				$max_age  = $rule['max'] ?? 150;
				return $user_age >= $min_age && $user_age <= $max_age;

			case 'assessment':
				return in_array( $assessment_type, $rule['value'], true );

			default:
				return true;
		}
	}

	/**
	 * Evaluate condition
	 *
	 * @param array $condition Condition to evaluate
	 * @param array $user_answers User answers
	 * @param array $user_data User data
	 * @return bool Whether condition is met
	 */
	private static function evaluate_condition( $condition, $user_answers, $user_data ) {
		$field    = $condition['field'];
		$operator = $condition['operator'];
		$value    = $condition['value'];

		$field_value = $user_answers[ $field ] ?? $user_data[ $field ] ?? null;

		switch ( $operator ) {
			case 'equals':
				return $field_value === $value;
			case 'not_equals':
				return $field_value !== $value;
			case 'contains':
				return is_array( $field_value ) && in_array( $value, $field_value, true );
			case 'not_contains':
				return ! is_array( $field_value ) || ! in_array( $value, $field_value, true );
			case 'greater_than':
				return is_numeric( $field_value ) && floatval( $field_value ) > floatval( $value );
			case 'less_than':
				return is_numeric( $field_value ) && floatval( $field_value ) < floatval( $value );
			default:
				return true;
		}
	}

	/**
	 * Evaluate priority modifier
	 *
	 * @param array $modifier Priority modifier
	 * @param array $user_data User data
	 * @return bool Whether modifier applies
	 */
	private static function evaluate_priority_modifier( $modifier, $user_data ) {
		return self::evaluate_condition( $modifier['condition'], array(), $user_data );
	}
}

ENNU_Smart_Question_Display::init();
