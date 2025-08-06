<?php
/**
 * ENNU Internationalization Manager
 * Handles i18n and l10n for the plugin
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Internationalization {

	private static $instance = null;
	private $text_domain     = 'ennulifeassessments';

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor - Enable translation system
	 */
	private function __construct() {
		// Enable translation system
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_translations' ) );
		add_filter( 'ennu_get_localized_string', array( $this, 'get_localized_string' ), 10, 2 );
		add_filter( 'ennu_format_localized_date', array( $this, 'format_localized_date' ), 10, 2 );
		add_filter( 'ennu_format_localized_number', array( $this, 'format_localized_number' ), 10, 2 );
	}

	/**
	 * Load text domain for translations
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'ennulifeassessments',
			false,
			dirname( plugin_basename( __FILE__ ) ) . '/../languages'
		);
	}

	/**
	 * Enqueue translations for JavaScript
	 */
	public function enqueue_translations() {
		$translations = array(
			'required_field' => __( 'This field is required.', 'ennulifeassessments' ),
			'invalid_email' => __( 'Please enter a valid email address.', 'ennulifeassessments' ),
			'processing_form' => __( 'Processing your form...', 'ennulifeassessments' ),
			'form_submitted' => __( 'Form submitted successfully!', 'ennulifeassessments' ),
			'error_occurred' => __( 'An error occurred. Please try again.', 'ennulifeassessments' ),
			'next_question' => __( 'Next Question', 'ennulifeassessments' ),
			'previous_question' => __( 'Previous Question', 'ennulifeassessments' ),
			'submit_assessment' => __( 'Submit Assessment', 'ennulifeassessments' ),
			'progress' => __( 'Progress', 'ennulifeassessments' ),
			'question' => __( 'Question', 'ennulifeassessments' ),
			'of' => __( 'of', 'ennulifeassessments' ),
		);

		wp_localize_script( 'ennu-frontend-forms', 'ennu_translations', $translations );
	}

	/**
	 * Get localized string with context support
	 */
	public function get_localized_string( $string, $context = '' ) {
		$translated = __( $string, 'ennulifeassessments' );

		// Add context-specific translations
		if ( ! empty( $context ) ) {
			$context_key = $context . '_' . sanitize_key( $string );
			$context_translated = __( $context_key, 'ennulifeassessments' );

			if ( $context_translated !== $context_key ) {
				return $context_translated;
			}
		}

		return $translated;
	}

	/**
	 * Get assessment translations
	 */
	public function get_assessment_translations( $assessment_type ) {
		$translations = array(
			'title' => $this->get_localized_string( $assessment_type . '_title' ),
			'description' => $this->get_localized_string( $assessment_type . '_description' ),
			'questions' => $this->get_question_translations( $assessment_type ),
			'answers' => $this->get_answer_translations( $assessment_type ),
		);

		return $translations;
	}

	/**
	 * Get question translations for assessment
	 */
	private function get_question_translations( $assessment_type ) {
		$questions = array();
		$assessment_config = include( dirname( __FILE__ ) . '/config/assessments/' . $assessment_type . '.php' );

		if ( isset( $assessment_config['questions'] ) ) {
			foreach ( $assessment_config['questions'] as $key => $question ) {
				$questions[$key] = array(
					'title' => $this->get_localized_string( $question['title'], $assessment_type ),
					'help_text' => isset( $question['help_text'] ) ? $this->get_localized_string( $question['help_text'], $assessment_type ) : '',
				);
			}
		}

		return $questions;
	}

	/**
	 * Get answer translations for assessment
	 */
	private function get_answer_translations( $assessment_type ) {
		$answers = array();
		$assessment_config = include( dirname( __FILE__ ) . '/config/assessments/' . $assessment_type . '.php' );

		if ( isset( $assessment_config['questions'] ) ) {
			foreach ( $assessment_config['questions'] as $key => $question ) {
				if ( isset( $question['options'] ) ) {
					$answers[$key] = array();
					foreach ( $question['options'] as $option_key => $option_text ) {
						$answers[$key][$option_key] = $this->get_localized_string( $option_text, $assessment_type );
					}
				}
			}
		}

		return $answers;
	}

	/**
	 * Format localized date
	 */
	public function format_localized_date( $date, $format = '' ) {
		if ( empty( $format ) ) {
			$format = get_option( 'date_format' );
		}

		return date_i18n( $format, strtotime( $date ) );
	}

	/**
	 * Format localized number
	 */
	public function format_localized_number( $number, $decimals = 2 ) {
		$locale = get_locale();
		$decimal_separator = localeconv()['decimal_point'] ?? '.';
		$thousands_separator = localeconv()['thousands_sep'] ?? ',';

		return number_format( $number, $decimals, $decimal_separator, $thousands_separator );
	}

	/**
	 * Get current locale information
	 */
	public function get_locale_info() {
		return array(
			'locale' => get_locale(),
			'date_format' => get_option( 'date_format' ),
			'time_format' => get_option( 'time_format' ),
			'decimal_separator' => localeconv()['decimal_point'] ?? '.',
			'thousands_separator' => localeconv()['thousands_sep'] ?? ',',
		);
	}

	/**
	 * Register translatable strings
	 */
	public function register_strings() {
		__( 'Welcome Assessment', $this->text_domain );
		__( 'Hair Assessment', $this->text_domain );
		__( 'Health Assessment', $this->text_domain );
		__( 'Skin Assessment', $this->text_domain );
		__( 'Sleep Assessment', $this->text_domain );
		__( 'Hormone Assessment', $this->text_domain );
		__( 'Menopause Assessment', $this->text_domain );
		__( 'Testosterone Assessment', $this->text_domain );
		__( 'Weight Loss Assessment', $this->text_domain );
		__( 'ED Treatment Assessment', $this->text_domain );
		__( 'Health Optimization Assessment', $this->text_domain );

		__( 'Mind', $this->text_domain );
		__( 'Body', $this->text_domain );
		__( 'Lifestyle', $this->text_domain );
		__( 'Aesthetics', $this->text_domain );

		__( 'Dashboard', $this->text_domain );
		__( 'My Results', $this->text_domain );
		__( 'My Biomarkers', $this->text_domain );
		__( 'My New Life', $this->text_domain );
		__( 'Book Consultation', $this->text_domain );
		__( 'Take Assessment', $this->text_domain );
		__( 'View Details', $this->text_domain );
		__( 'Retake Assessment', $this->text_domain );

		__( 'Lose Weight', $this->text_domain );
		__( 'Build Muscle', $this->text_domain );
		__( 'Improve Sleep', $this->text_domain );
		__( 'Reduce Stress', $this->text_domain );
		__( 'Boost Energy', $this->text_domain );
		__( 'Enhance Focus', $this->text_domain );
		__( 'Better Skin', $this->text_domain );
		__( 'Hair Growth', $this->text_domain );
		__( 'Hormone Balance', $this->text_domain );
		__( 'Heart Health', $this->text_domain );
		__( 'Longevity', $this->text_domain );

		__( 'Assessment completed successfully', $this->text_domain );
		__( 'Scores calculated and saved', $this->text_domain );
		__( 'Error processing assessment', $this->text_domain );
		__( 'Please try again', $this->text_domain );
		__( 'Loading...', $this->text_domain );
		__( 'Saving...', $this->text_domain );
	}

	/**
	 * Get localized script data
	 */
	public function get_script_data() {
		return array(
			'loading' => __( 'Loading...', $this->text_domain ),
			'saving'  => __( 'Saving...', $this->text_domain ),
			'error'   => __( 'An error occurred', $this->text_domain ),
			'success' => __( 'Success!', $this->text_domain ),
			'confirm' => __( 'Are you sure?', $this->text_domain ),
			'cancel'  => __( 'Cancel', $this->text_domain ),
			'save'    => __( 'Save', $this->text_domain ),
			'delete'  => __( 'Delete', $this->text_domain ),
		);
	}

	/**
	 * Generate .pot file for translations
	 */
	public function generate_pot_file() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$pot_file = plugin_dir_path( __FILE__ ) . '../languages/ennu-life-assessments.pot';

		$pot_content = '# ENNU Life Assessments Translation Template
# Copyright (C) 2025 ENNU Life
# This file is distributed under the GPL v2 or later.
msgid ""
msgstr ""
"Project-Id-Version: ENNU Life Assessments 62.2.8\n"
"Report-Msgid-Bugs-To: support@ennulife.com\n"
"POT-Creation-Date: ' . date( 'Y-m-d H:i:s+0000' ) . '\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"Language-Team: ENNU Life <support@ennulife.com>\n"
"Text-Domain: ennu-life-assessments\n"

';

		return file_put_contents( $pot_file, $pot_content );
	}
}

ENNU_Internationalization::get_instance();
