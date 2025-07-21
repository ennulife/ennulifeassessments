<?php
/**
 * ENNU Progressive Data Collector
 *
 * Implements smart data collection logic with progressive disclosure
 * and intelligent pre-filling based on existing user data.
 *
 * @package ENNU_Life
 * @version 62.2.8
 * @author ENNU Life Development Team
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Progressive_Data_Collector {

	/**
	 * Initialize the progressive data collector
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_ennu_get_user_data', array( __CLASS__, 'ajax_get_user_data' ) );
		add_action( 'wp_ajax_nopriv_ennu_get_user_data', array( __CLASS__, 'ajax_get_user_data' ) );
		add_filter( 'ennu_assessment_question_data', array( __CLASS__, 'pre_fill_question_data' ), 10, 3 );
		add_action( 'ennu_before_assessment_render', array( __CLASS__, 'inject_progressive_logic' ) );
	}

	/**
	 * Enqueue progressive data collection scripts
	 */
	public static function enqueue_scripts() {
		wp_localize_script(
			'ennu-frontend-forms',
			'ennu_progressive_data',
			array(
				'ajax_url'        => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( 'ennu_progressive_data_nonce' ),
				'global_fields'   => self::get_global_field_mapping(),
				'smart_questions' => self::get_smart_question_config(),
			)
		);
	}

	/**
	 * Get global field mapping configuration
	 *
	 * @return array Global field mappings
	 */
	public static function get_global_field_mapping() {
		return array(
			'gender'              => 'ennu_global_gender',
			'user_dob_combined'   => 'ennu_global_user_dob_combined',
			'health_goals'        => 'ennu_global_health_goals',
			'height_weight'       => 'ennu_global_height_weight',
			'full_name'           => array( 'first_name', 'last_name' ),
			'contact_info'        => array( 'user_email', 'phone' ),
			'medical_history'     => 'ennu_global_medical_history',
			'lifestyle_factors'   => 'ennu_global_lifestyle_factors',
			'current_medications' => 'ennu_global_current_medications',
		);
	}

	/**
	 * Get smart question display configuration
	 *
	 * @return array Smart question rules
	 */
	public static function get_smart_question_config() {
		return array(
			'conditional_display' => array(
				'testosterone_questions' => array(
					'condition' => 'age >= 30 OR symptoms_present',
					'priority'  => 'high',
				),
				'menopause_questions'    => array(
					'condition' => 'gender === "female" AND age >= 40',
					'priority'  => 'high',
				),
				'lifestyle_questions'    => array(
					'condition' => 'basic_info_complete',
					'priority'  => 'medium',
				),
			),
			'progressive_disclosure' => array(
				'max_questions_per_step' => 3,
				'auto_advance_delay'     => 300,
				'show_progress_bar'      => true,
				'enable_back_navigation' => true,
			),
		);
	}

	/**
	 * AJAX handler to get user data for pre-filling
	 */
	public static function ajax_get_user_data() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_progressive_data_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( 'User not logged in' );
		}

		$user_data = self::get_user_global_data( $user_id );
		$completeness = self::calculate_data_completeness( $user_data );

		wp_send_json_success(
			array(
				'user_data'    => $user_data,
				'completeness' => $completeness,
				'missing_data' => self::identify_missing_data( $user_data ),
			)
		);
	}

	/**
	 * Get user's global data across all assessments
	 *
	 * @param int $user_id User ID
	 * @return array User's global data
	 */
	public static function get_user_global_data( $user_id ) {
		$global_data = array();
		$field_mapping = self::get_global_field_mapping();

		foreach ( $field_mapping as $key => $meta_key ) {
			if ( is_array( $meta_key ) ) {
				$global_data[ $key ] = array();
				foreach ( $meta_key as $sub_key ) {
					if ( 'first_name' === $sub_key || 'last_name' === $sub_key ) {
						$user = get_userdata( $user_id );
						$global_data[ $key ][ $sub_key ] = $user->$sub_key ?? '';
					} elseif ( 'user_email' === $sub_key ) {
						$user = get_userdata( $user_id );
						$global_data[ $key ][ $sub_key ] = $user->user_email ?? '';
					} else {
						$global_data[ $key ][ $sub_key ] = get_user_meta( $user_id, $sub_key, true );
					}
				}
			} else {
				$global_data[ $key ] = get_user_meta( $user_id, $meta_key, true );
			}
		}

		return $global_data;
	}

	/**
	 * Calculate data completeness percentage
	 *
	 * @param array $user_data User's data
	 * @return array Completeness information
	 */
	public static function calculate_data_completeness( $user_data ) {
		$total_fields = 0;
		$completed_fields = 0;
		$critical_fields = array( 'gender', 'user_dob_combined', 'health_goals' );
		$critical_completed = 0;

		foreach ( $user_data as $key => $value ) {
			$total_fields++;
			
			if ( self::is_field_completed( $value ) ) {
				$completed_fields++;
				
				if ( in_array( $key, $critical_fields, true ) ) {
					$critical_completed++;
				}
			}
		}

		$overall_percentage = $total_fields > 0 ? round( ( $completed_fields / $total_fields ) * 100 ) : 0;
		$critical_percentage = count( $critical_fields ) > 0 ? round( ( $critical_completed / count( $critical_fields ) ) * 100 ) : 0;

		return array(
			'overall_percentage'  => $overall_percentage,
			'critical_percentage' => $critical_percentage,
			'completed_fields'    => $completed_fields,
			'total_fields'        => $total_fields,
			'accuracy_level'      => self::determine_accuracy_level( $overall_percentage, $critical_percentage ),
		);
	}

	/**
	 * Check if a field is considered completed
	 *
	 * @param mixed $value Field value
	 * @return bool Whether field is completed
	 */
	private static function is_field_completed( $value ) {
		if ( is_array( $value ) ) {
			return ! empty( array_filter( $value ) );
		}
		
		return ! empty( $value ) && '' !== trim( $value );
	}

	/**
	 * Determine data accuracy level
	 *
	 * @param int $overall_percentage Overall completion percentage
	 * @param int $critical_percentage Critical fields completion percentage
	 * @return string Accuracy level
	 */
	private static function determine_accuracy_level( $overall_percentage, $critical_percentage ) {
		if ( $critical_percentage >= 100 && $overall_percentage >= 80 ) {
			return 'high';
		} elseif ( $critical_percentage >= 100 && $overall_percentage >= 60 ) {
			return 'medium';
		} elseif ( $critical_percentage >= 67 ) {
			return 'basic';
		} else {
			return 'low';
		}
	}

	/**
	 * Identify missing data for targeted collection
	 *
	 * @param array $user_data User's current data
	 * @return array Missing data priorities
	 */
	public static function identify_missing_data( $user_data ) {
		$missing_data = array();
		$priority_fields = array(
			'critical' => array( 'gender', 'user_dob_combined', 'health_goals' ),
			'high'     => array( 'height_weight', 'full_name' ),
			'medium'   => array( 'contact_info', 'medical_history' ),
			'low'      => array( 'lifestyle_factors', 'current_medications' ),
		);

		foreach ( $priority_fields as $priority => $fields ) {
			foreach ( $fields as $field ) {
				if ( ! isset( $user_data[ $field ] ) || ! self::is_field_completed( $user_data[ $field ] ) ) {
					$missing_data[ $priority ][] = $field;
				}
			}
		}

		return $missing_data;
	}

	/**
	 * Pre-fill question data based on existing user information
	 *
	 * @param array  $question_data Question configuration
	 * @param string $question_key Question key
	 * @param int    $user_id User ID
	 * @return array Modified question data
	 */
	public static function pre_fill_question_data( $question_data, $question_key, $user_id ) {
		if ( ! $user_id || ! isset( $question_data['global_key'] ) ) {
			return $question_data;
		}

		$global_key = $question_data['global_key'];
		$user_data = self::get_user_global_data( $user_id );

		if ( isset( $user_data[ $global_key ] ) && self::is_field_completed( $user_data[ $global_key ] ) ) {
			$question_data['pre_filled_value'] = $user_data[ $global_key ];
			$question_data['is_pre_filled'] = true;
		}

		return $question_data;
	}

	/**
	 * Inject progressive logic into assessment rendering
	 *
	 * @param string $assessment_type Assessment type
	 */
	public static function inject_progressive_logic( $assessment_type ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		$user_data = self::get_user_global_data( $user_id );
		$completeness = self::calculate_data_completeness( $user_data );
		$missing_data = self::identify_missing_data( $user_data );

		echo '<script type="text/javascript">';
		echo 'window.ennuProgressiveData = ' . wp_json_encode(
			array(
				'userData'     => $user_data,
				'completeness' => $completeness,
				'missingData'  => $missing_data,
				'assessmentType' => $assessment_type,
			)
		) . ';';
		echo '</script>';
	}

	/**
	 * Get smart question recommendations for user
	 *
	 * @param int $user_id User ID
	 * @return array Recommended questions
	 */
	public static function get_smart_question_recommendations( $user_id ) {
		$user_data = self::get_user_global_data( $user_id );
		$missing_data = self::identify_missing_data( $user_data );
		$recommendations = array();

		if ( ! empty( $missing_data['critical'] ) ) {
			$recommendations['priority'] = 'critical';
			$recommendations['questions'] = self::map_missing_data_to_questions( $missing_data['critical'] );
		} elseif ( ! empty( $missing_data['high'] ) ) {
			$recommendations['priority'] = 'high';
			$recommendations['questions'] = self::map_missing_data_to_questions( $missing_data['high'] );
		} else {
			$recommendations['priority'] = 'enhancement';
			$recommendations['questions'] = self::get_enhancement_questions( $user_data );
		}

		return $recommendations;
	}

	/**
	 * Map missing data fields to specific questions
	 *
	 * @param array $missing_fields Missing field keys
	 * @return array Question configurations
	 */
	private static function map_missing_data_to_questions( $missing_fields ) {
		$question_map = array(
			'gender'              => array( 'type' => 'radio', 'title' => 'What is your gender?' ),
			'user_dob_combined'   => array( 'type' => 'dob_dropdowns', 'title' => 'What is your date of birth?' ),
			'health_goals'        => array( 'type' => 'multiselect', 'title' => 'What are your primary health goals?' ),
			'height_weight'       => array( 'type' => 'height_weight', 'title' => 'What is your height and weight?' ),
			'full_name'           => array( 'type' => 'name_fields', 'title' => 'What is your full name?' ),
			'contact_info'        => array( 'type' => 'contact_fields', 'title' => 'How can we contact you?' ),
			'medical_history'     => array( 'type' => 'multiselect', 'title' => 'Do you have any medical conditions?' ),
			'lifestyle_factors'   => array( 'type' => 'multiselect', 'title' => 'Tell us about your lifestyle' ),
			'current_medications' => array( 'type' => 'textarea', 'title' => 'What medications are you currently taking?' ),
		);

		$questions = array();
		foreach ( $missing_fields as $field ) {
			if ( isset( $question_map[ $field ] ) ) {
				$questions[ $field ] = $question_map[ $field ];
			}
		}

		return $questions;
	}

	/**
	 * Get enhancement questions for users with complete basic data
	 *
	 * @param array $user_data User's current data
	 * @return array Enhancement questions
	 */
	private static function get_enhancement_questions( $user_data ) {
		$enhancement_questions = array();

		if ( isset( $user_data['user_dob_combined'] ) ) {
			$age = self::calculate_age_from_dob( $user_data['user_dob_combined'] );
			
			if ( $age >= 40 ) {
				$enhancement_questions['hormone_optimization'] = array(
					'type'  => 'multiselect',
					'title' => 'Are you interested in hormone optimization?',
				);
			}
			
			if ( $age >= 50 ) {
				$enhancement_questions['longevity_focus'] = array(
					'type'  => 'multiselect',
					'title' => 'What longevity factors are most important to you?',
				);
			}
		}

		if ( isset( $user_data['health_goals'] ) && is_array( $user_data['health_goals'] ) ) {
			if ( in_array( 'weight_loss', $user_data['health_goals'], true ) ) {
				$enhancement_questions['nutrition_details'] = array(
					'type'  => 'multiselect',
					'title' => 'Tell us more about your nutrition preferences',
				);
			}
			
			if ( in_array( 'strength', $user_data['health_goals'], true ) ) {
				$enhancement_questions['exercise_details'] = array(
					'type'  => 'multiselect',
					'title' => 'What is your current exercise routine?',
				);
			}
		}

		return $enhancement_questions;
	}

	/**
	 * Calculate age from date of birth
	 *
	 * @param string $dob Date of birth in Y-m-d format
	 * @return int Age in years
	 */
	private static function calculate_age_from_dob( $dob ) {
		if ( empty( $dob ) ) {
			return 0;
		}

		$birth_date = new DateTime( $dob );
		$current_date = new DateTime();
		$age = $current_date->diff( $birth_date )->y;

		return $age;
	}
}

ENNU_Progressive_Data_Collector::init();
