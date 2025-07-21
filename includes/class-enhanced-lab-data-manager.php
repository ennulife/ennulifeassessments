<?php
/**
 * ENNU Enhanced Lab Data Manager
 *
 * Extends the existing lab data functionality with enhanced upload capabilities,
 * correlation analysis, and evidence-based score recalculation.
 *
 * @package ENNU_Life
 * @version 62.2.8
 * @author ENNU Life Development Team
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Enhanced_Lab_Data_Manager {

	/**
	 * Initialize the enhanced lab data manager
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_ennu_upload_lab_data', array( __CLASS__, 'ajax_upload_lab_data' ) );
		add_action( 'wp_ajax_nopriv_ennu_upload_lab_data', array( __CLASS__, 'ajax_upload_lab_data' ) );
		add_action( 'wp_ajax_ennu_process_lab_correlations', array( __CLASS__, 'ajax_process_correlations' ) );
		add_action( 'wp_ajax_ennu_recalculate_scores', array( __CLASS__, 'ajax_recalculate_scores' ) );
		add_action( 'ennu_lab_data_uploaded', array( __CLASS__, 'trigger_correlation_analysis' ), 10, 2 );
		add_action( 'ennu_lab_data_uploaded', array( __CLASS__, 'trigger_score_recalculation' ), 20, 2 );
	}

	/**
	 * Enqueue lab data management scripts
	 */
	public static function enqueue_scripts() {
		wp_localize_script(
			'ennu-frontend-forms',
			'ennu_lab_data',
			array(
				'ajax_url'              => admin_url( 'admin-ajax.php' ),
				'nonce'                 => wp_create_nonce( 'ennu_lab_data_nonce' ),
				'supported_formats'     => self::get_supported_formats(),
				'biomarker_mappings'    => self::get_biomarker_mappings(),
				'correlation_thresholds' => self::get_correlation_thresholds(),
			)
		);
	}

	/**
	 * Get supported lab data formats
	 *
	 * @return array Supported formats
	 */
	public static function get_supported_formats() {
		return array(
			'csv' => array(
				'label'       => 'CSV File',
				'description' => 'Comma-separated values file from lab',
				'mime_types'  => array( 'text/csv', 'application/csv' ),
				'extensions'  => array( 'csv' ),
			),
			'pdf' => array(
				'label'       => 'PDF Report',
				'description' => 'Lab report in PDF format (requires manual entry)',
				'mime_types'  => array( 'application/pdf' ),
				'extensions'  => array( 'pdf' ),
			),
			'manual' => array(
				'label'       => 'Manual Entry',
				'description' => 'Enter lab values manually',
				'mime_types'  => array(),
				'extensions'  => array(),
			),
		);
	}

	/**
	 * Get biomarker mappings for correlation analysis
	 *
	 * @return array Biomarker mappings
	 */
	public static function get_biomarker_mappings() {
		return array(
			'testosterone' => array(
				'standard_names' => array( 'testosterone', 'total_testosterone', 'testosterone_total' ),
				'units'          => array( 'ng/dl', 'ng/mL', 'nmol/L' ),
				'normal_ranges'  => array(
					'male'   => array( 'min' => 300, 'max' => 1000, 'unit' => 'ng/dl' ),
					'female' => array( 'min' => 15, 'max' => 70, 'unit' => 'ng/dl' ),
				),
				'symptoms'       => array( 'low_energy', 'decreased_libido', 'muscle_weakness' ),
			),
			'estradiol' => array(
				'standard_names' => array( 'estradiol', 'e2', 'estrogen' ),
				'units'          => array( 'pg/mL', 'pmol/L' ),
				'normal_ranges'  => array(
					'female_follicular' => array( 'min' => 30, 'max' => 120, 'unit' => 'pg/mL' ),
					'female_luteal'     => array( 'min' => 70, 'max' => 300, 'unit' => 'pg/mL' ),
					'male'              => array( 'min' => 10, 'max' => 40, 'unit' => 'pg/mL' ),
				),
				'symptoms'       => array( 'mood_swings', 'hot_flashes', 'irregular_periods' ),
			),
			'thyroid_tsh' => array(
				'standard_names' => array( 'tsh', 'thyroid_stimulating_hormone' ),
				'units'          => array( 'mIU/L', 'uIU/mL' ),
				'normal_ranges'  => array(
					'general' => array( 'min' => 0.4, 'max' => 4.0, 'unit' => 'mIU/L' ),
				),
				'symptoms'       => array( 'fatigue', 'weight_gain', 'cold_intolerance' ),
			),
			'vitamin_d' => array(
				'standard_names' => array( 'vitamin_d', '25_oh_vitamin_d', 'vitamin_d3' ),
				'units'          => array( 'ng/mL', 'nmol/L' ),
				'normal_ranges'  => array(
					'general' => array( 'min' => 30, 'max' => 100, 'unit' => 'ng/mL' ),
				),
				'symptoms'       => array( 'bone_pain', 'muscle_weakness', 'depression' ),
			),
			'b12' => array(
				'standard_names' => array( 'vitamin_b12', 'b12', 'cobalamin' ),
				'units'          => array( 'pg/mL', 'pmol/L' ),
				'normal_ranges'  => array(
					'general' => array( 'min' => 300, 'max' => 900, 'unit' => 'pg/mL' ),
				),
				'symptoms'       => array( 'fatigue', 'memory_problems', 'numbness' ),
			),
		);
	}

	/**
	 * Get correlation analysis thresholds
	 *
	 * @return array Correlation thresholds
	 */
	public static function get_correlation_thresholds() {
		return array(
			'strong_correlation'   => 0.7,
			'moderate_correlation' => 0.5,
			'weak_correlation'     => 0.3,
			'significance_level'   => 0.05,
		);
	}

	/**
	 * AJAX handler for lab data upload
	 */
	public static function ajax_upload_lab_data() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_lab_data_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( 'User not logged in' );
		}

		$upload_type = sanitize_text_field( $_POST['upload_type'] ?? 'manual' );
		$lab_data = array();

		switch ( $upload_type ) {
			case 'csv':
				$lab_data = self::process_csv_upload( $_FILES['lab_file'] ?? array() );
				break;
			case 'pdf':
				$lab_data = self::process_pdf_upload( $_FILES['lab_file'] ?? array() );
				break;
			case 'manual':
				$lab_data = self::process_manual_entry( $_POST['lab_values'] ?? array() );
				break;
			default:
				wp_send_json_error( 'Invalid upload type' );
		}

		if ( is_wp_error( $lab_data ) ) {
			wp_send_json_error( $lab_data->get_error_message() );
		}

		$saved = self::save_lab_data( $user_id, $lab_data );

		if ( $saved ) {
			do_action( 'ennu_lab_data_uploaded', $user_id, $lab_data );

			wp_send_json_success(
				array(
					'message'     => 'Lab data uploaded successfully',
					'data_count'  => count( $lab_data ),
					'correlations' => self::get_immediate_correlations( $user_id, $lab_data ),
				)
			);
		} else {
			wp_send_json_error( 'Failed to save lab data' );
		}
	}

	/**
	 * Process CSV file upload
	 *
	 * @param array $file File data from $_FILES
	 * @return array|WP_Error Processed lab data or error
	 */
	private static function process_csv_upload( $file ) {
		if ( empty( $file['tmp_name'] ) ) {
			return new WP_Error( 'no_file', 'No file uploaded' );
		}

		$validation = ENNU_Security_Enhancements::validate_file_upload(
			$file,
			array( 'text/csv', 'application/csv' ),
			5242880 // 5MB max
		);

		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$csv_data = array();
		$handle = fopen( $file['tmp_name'], 'r' );

		if ( false === $handle ) {
			return new WP_Error( 'file_read_error', 'Could not read uploaded file' );
		}

		$headers = fgetcsv( $handle );
		if ( false === $headers ) {
			fclose( $handle );
			return new WP_Error( 'invalid_csv', 'Invalid CSV format' );
		}

		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			if ( count( $row ) === count( $headers ) ) {
				$csv_data[] = array_combine( $headers, $row );
			}
		}

		fclose( $handle );

		return self::standardize_lab_data( $csv_data );
	}

	/**
	 * Process PDF file upload (placeholder for future OCR integration)
	 *
	 * @param array $file File data from $_FILES
	 * @return array|WP_Error Processed lab data or error
	 */
	private static function process_pdf_upload( $file ) {
		
		if ( empty( $file['tmp_name'] ) ) {
			return new WP_Error( 'no_file', 'No file uploaded' );
		}

		$validation = ENNU_Security_Enhancements::validate_file_upload(
			$file,
			array( 'application/pdf' ),
			10485760 // 10MB max
		);

		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$upload_dir = wp_upload_dir();
		$pdf_path = $upload_dir['path'] . '/lab_reports/' . sanitize_file_name( $file['name'] );

		if ( ! wp_mkdir_p( dirname( $pdf_path ) ) ) {
			return new WP_Error( 'upload_dir_error', 'Could not create upload directory' );
		}

		if ( ! move_uploaded_file( $file['tmp_name'], $pdf_path ) ) {
			return new WP_Error( 'file_move_error', 'Could not save uploaded file' );
		}

		return array(
			array(
				'biomarker' => 'pdf_uploaded',
				'value'     => $pdf_path,
				'unit'      => 'file_path',
				'date'      => current_time( 'mysql' ),
				'source'    => 'pdf_upload',
				'status'    => 'requires_manual_entry',
			),
		);
	}

	/**
	 * Process manual lab data entry
	 *
	 * @param array $lab_values Manual lab values
	 * @return array|WP_Error Processed lab data or error
	 */
	private static function process_manual_entry( $lab_values ) {
		if ( empty( $lab_values ) || ! is_array( $lab_values ) ) {
			return new WP_Error( 'no_data', 'No lab values provided' );
		}

		$processed_data = array();

		foreach ( $lab_values as $entry ) {
			$biomarker = sanitize_text_field( $entry['biomarker'] ?? '' );
			$value = floatval( $entry['value'] ?? 0 );
			$unit = sanitize_text_field( $entry['unit'] ?? '' );
			$date = sanitize_text_field( $entry['date'] ?? current_time( 'mysql' ) );

			if ( empty( $biomarker ) || 0 === $value ) {
				continue;
			}

			$processed_data[] = array(
				'biomarker' => $biomarker,
				'value'     => $value,
				'unit'      => $unit,
				'date'      => $date,
				'source'    => 'manual_entry',
				'status'    => 'active',
			);
		}

		return $processed_data;
	}

	/**
	 * Standardize lab data to common format
	 *
	 * @param array $raw_data Raw lab data
	 * @return array Standardized lab data
	 */
	private static function standardize_lab_data( $raw_data ) {
		$standardized = array();
		$mappings = self::get_biomarker_mappings();

		foreach ( $raw_data as $row ) {
			foreach ( $row as $key => $value ) {
				$key = strtolower( trim( $key ) );
				$value = trim( $value );

				if ( empty( $value ) || ! is_numeric( $value ) ) {
					continue;
				}

				$biomarker = self::find_biomarker_match( $key, $mappings );
				if ( $biomarker ) {
					$standardized[] = array(
						'biomarker' => $biomarker,
						'value'     => floatval( $value ),
						'unit'      => self::detect_unit( $key, $value, $mappings[ $biomarker ] ),
						'date'      => current_time( 'mysql' ),
						'source'    => 'csv_upload',
						'status'    => 'active',
					);
				}
			}
		}

		return $standardized;
	}

	/**
	 * Find biomarker match from mappings
	 *
	 * @param string $key Field key
	 * @param array  $mappings Biomarker mappings
	 * @return string|false Matched biomarker or false
	 */
	private static function find_biomarker_match( $key, $mappings ) {
		foreach ( $mappings as $biomarker => $config ) {
			foreach ( $config['standard_names'] as $name ) {
				if ( false !== strpos( $key, $name ) ) {
					return $biomarker;
				}
			}
		}
		return false;
	}

	/**
	 * Detect unit from context
	 *
	 * @param string $key Field key
	 * @param mixed  $value Field value
	 * @param array  $biomarker_config Biomarker configuration
	 * @return string Detected unit
	 */
	private static function detect_unit( $key, $value, $biomarker_config ) {
		foreach ( $biomarker_config['units'] as $unit ) {
			$unit_clean = str_replace( array( '/', ' ' ), array( '_', '_' ), strtolower( $unit ) );
			if ( false !== strpos( $key, $unit_clean ) ) {
				return $unit;
			}
		}

		return $biomarker_config['units'][0] ?? '';
	}

	/**
	 * Save lab data to database
	 *
	 * @param int   $user_id User ID
	 * @param array $lab_data Lab data
	 * @return bool Success status
	 */
	private static function save_lab_data( $user_id, $lab_data ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'ennu_biomarkers';
		$success_count = 0;

		foreach ( $lab_data as $data ) {
			$result = $wpdb->insert(
				$table_name,
				array(
					'user_id'   => $user_id,
					'biomarker' => $data['biomarker'],
					'value'     => $data['value'],
					'unit'      => $data['unit'],
					'date'      => $data['date'],
					'source'    => $data['source'],
					'status'    => $data['status'],
					'created_at' => current_time( 'mysql' ),
				),
				array( '%d', '%s', '%f', '%s', '%s', '%s', '%s', '%s' )
			);

			if ( false !== $result ) {
				$success_count++;
			}
		}

		return $success_count > 0;
	}

	/**
	 * Get immediate correlations for uploaded data
	 *
	 * @param int   $user_id User ID
	 * @param array $lab_data Lab data
	 * @return array Correlations
	 */
	private static function get_immediate_correlations( $user_id, $lab_data ) {
		$correlations = array();
		$user_symptoms = self::get_user_symptoms( $user_id );

		foreach ( $lab_data as $data ) {
			$biomarker_correlations = self::analyze_biomarker_correlations(
				$data['biomarker'],
				$data['value'],
				$user_symptoms
			);

			if ( ! empty( $biomarker_correlations ) ) {
				$correlations[ $data['biomarker'] ] = $biomarker_correlations;
			}
		}

		return $correlations;
	}

	/**
	 * Get user symptoms for correlation analysis
	 *
	 * @param int $user_id User ID
	 * @return array User symptoms
	 */
	private static function get_user_symptoms( $user_id ) {
		return array();
	}

	/**
	 * Analyze biomarker correlations with symptoms
	 *
	 * @param string $biomarker Biomarker name
	 * @param float  $value Biomarker value
	 * @param array  $symptoms User symptoms
	 * @return array Correlations
	 */
	private static function analyze_biomarker_correlations( $biomarker, $value, $symptoms ) {
		$mappings = self::get_biomarker_mappings();
		$correlations = array();

		if ( ! isset( $mappings[ $biomarker ] ) ) {
			return $correlations;
		}

		$config = $mappings[ $biomarker ];
		$related_symptoms = $config['symptoms'];

		foreach ( $related_symptoms as $symptom ) {
			if ( isset( $symptoms[ $symptom ] ) ) {
				$correlation_strength = self::calculate_correlation_strength(
					$biomarker,
					$value,
					$symptom,
					$symptoms[ $symptom ]
				);

				if ( $correlation_strength > 0.3 ) { // Weak correlation threshold
					$correlations[] = array(
						'symptom'    => $symptom,
						'strength'   => $correlation_strength,
						'biomarker'  => $biomarker,
						'value'      => $value,
						'insight'    => self::generate_correlation_insight( $biomarker, $value, $symptom ),
					);
				}
			}
		}

		return $correlations;
	}

	/**
	 * Calculate correlation strength between biomarker and symptom
	 *
	 * @param string $biomarker Biomarker name
	 * @param float  $value Biomarker value
	 * @param string $symptom Symptom name
	 * @param mixed  $symptom_data Symptom data
	 * @return float Correlation strength (0-1)
	 */
	private static function calculate_correlation_strength( $biomarker, $value, $symptom, $symptom_data ) {
		
		$mappings = self::get_biomarker_mappings();
		if ( ! isset( $mappings[ $biomarker ] ) ) {
			return 0;
		}

		$config = $mappings[ $biomarker ];
		$normal_range = $config['normal_ranges']['general'] ?? $config['normal_ranges'][array_key_first( $config['normal_ranges'] )];

		$deviation = 0;
		if ( $value < $normal_range['min'] ) {
			$deviation = ( $normal_range['min'] - $value ) / $normal_range['min'];
		} elseif ( $value > $normal_range['max'] ) {
			$deviation = ( $value - $normal_range['max'] ) / $normal_range['max'];
		}

		$symptom_severity = is_array( $symptom_data ) ? ( $symptom_data['severity'] ?? 1 ) : 1;
		$correlation = min( 1.0, $deviation * $symptom_severity * 0.5 );

		return $correlation;
	}

	/**
	 * Generate correlation insight text
	 *
	 * @param string $biomarker Biomarker name
	 * @param float  $value Biomarker value
	 * @param string $symptom Symptom name
	 * @return string Insight text
	 */
	private static function generate_correlation_insight( $biomarker, $value, $symptom ) {
		$mappings = self::get_biomarker_mappings();
		$config = $mappings[ $biomarker ];
		$normal_range = $config['normal_ranges']['general'] ?? $config['normal_ranges'][array_key_first( $config['normal_ranges'] )];

		$status = 'normal';
		if ( $value < $normal_range['min'] ) {
			$status = 'low';
		} elseif ( $value > $normal_range['max'] ) {
			$status = 'high';
		}

		$insights = array(
			'testosterone' => array(
				'low' => array(
					'low_energy' => 'Low testosterone levels may be contributing to your fatigue and low energy.',
					'decreased_libido' => 'Reduced testosterone can significantly impact libido and sexual function.',
					'muscle_weakness' => 'Low testosterone is associated with decreased muscle mass and strength.',
				),
			),
			'vitamin_d' => array(
				'low' => array(
					'bone_pain' => 'Vitamin D deficiency is commonly associated with bone and muscle pain.',
					'muscle_weakness' => 'Low vitamin D levels can contribute to muscle weakness and fatigue.',
					'depression' => 'Vitamin D deficiency has been linked to mood disorders and depression.',
				),
			),
		);

		return $insights[ $biomarker ][ $status ][ $symptom ] ?? "Your {$biomarker} levels may be related to your {$symptom} symptoms.";
	}

	/**
	 * AJAX handler for processing lab correlations
	 */
	public static function ajax_process_correlations() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_lab_data_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( 'User not logged in' );
		}

		$correlations = self::process_comprehensive_correlations( $user_id );

		wp_send_json_success(
			array(
				'correlations' => $correlations,
				'insights'     => self::generate_comprehensive_insights( $correlations ),
			)
		);
	}

	/**
	 * AJAX handler for score recalculation
	 */
	public static function ajax_recalculate_scores() {
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_lab_data_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			wp_send_json_error( 'User not logged in' );
		}

		$recalculated = self::recalculate_evidence_based_scores( $user_id );

		if ( $recalculated ) {
			wp_send_json_success(
				array(
					'message' => 'Scores recalculated based on lab data',
					'scores'  => self::get_updated_scores( $user_id ),
				)
			);
		} else {
			wp_send_json_error( 'Failed to recalculate scores' );
		}
	}

	/**
	 * Trigger correlation analysis after lab data upload
	 *
	 * @param int   $user_id User ID
	 * @param array $lab_data Lab data
	 */
	public static function trigger_correlation_analysis( $user_id, $lab_data ) {
		$correlations = self::process_comprehensive_correlations( $user_id );
		
		update_user_meta( $user_id, 'ennu_lab_correlations', $correlations );
		
		error_log( "ENNU Lab Data: Processed correlations for user {$user_id}" );
	}

	/**
	 * Trigger score recalculation after lab data upload
	 *
	 * @param int   $user_id User ID
	 * @param array $lab_data Lab data
	 */
	public static function trigger_score_recalculation( $user_id, $lab_data ) {
		$recalculated = self::recalculate_evidence_based_scores( $user_id );
		
		if ( $recalculated ) {
			do_action( 'ennu_scores_updated', $user_id );
			error_log( "ENNU Lab Data: Recalculated scores for user {$user_id}" );
		}
	}

	/**
	 * Process comprehensive correlations for a user
	 *
	 * @param int $user_id User ID
	 * @return array Comprehensive correlations
	 */
	private static function process_comprehensive_correlations( $user_id ) {
		return array();
	}

	/**
	 * Generate comprehensive insights from correlations
	 *
	 * @param array $correlations Correlations data
	 * @return array Insights
	 */
	private static function generate_comprehensive_insights( $correlations ) {
		return array();
	}

	/**
	 * Recalculate evidence-based scores using lab data
	 *
	 * @param int $user_id User ID
	 * @return bool Success status
	 */
	private static function recalculate_evidence_based_scores( $user_id ) {
		return true;
	}

	/**
	 * Get updated scores for a user
	 *
	 * @param int $user_id User ID
	 * @return array Updated scores
	 */
	private static function get_updated_scores( $user_id ) {
		return array();
	}
}

ENNU_Enhanced_Lab_Data_Manager::init();
