<?php
/**
 * ENNU Life Assessments - Lab Import Manager
 *
 * Handles lab results import from various sources
 *
 * @package ENNU_Life
 * @version 62.7.0
 */

class ENNU_Lab_Import_Manager {

	/**
	 * Supported lab providers
	 */
	private $supported_providers = array(
		'labcorp' => array(
			'name'         => 'LabCorp',
			'api_endpoint' => 'https://api.labcorp.com/v1/results',
			'file_formats' => array( 'csv', 'pdf' ),
			'mapping_file' => 'labcorp-mapping.json',
		),
		'quest'   => array(
			'name'         => 'Quest Diagnostics',
			'api_endpoint' => 'https://api.questdiagnostics.com/v1/results',
			'file_formats' => array( 'csv', 'pdf' ),
			'mapping_file' => 'quest-mapping.json',
		),
		'sonora'  => array(
			'name'         => 'Sonora Quest',
			'api_endpoint' => 'https://api.sonoraquest.com/v1/results',
			'file_formats' => array( 'csv', 'pdf' ),
			'mapping_file' => 'sonora-mapping.json',
		),
		'generic' => array(
			'name'         => 'Generic CSV',
			'api_endpoint' => null,
			'file_formats' => array( 'csv' ),
			'mapping_file' => 'generic-mapping.json',
		),
	);

	/**
	 * Import lab results from file
	 *
	 * @param int $user_id User ID
	 * @param string $file_path File path
	 * @param string $provider Lab provider
	 * @return array Import results
	 */
	public function import_lab_results( $user_id, $file_path, $provider = 'generic' ) {
		try {
			// Validate user
			if ( ! $user_id || ! get_user_by( 'ID', $user_id ) ) {
				throw new Exception( 'Invalid user ID' );
			}

			// Validate file
			if ( ! file_exists( $file_path ) ) {
				throw new Exception( 'File not found: ' . $file_path );
			}

			// Validate provider
			if ( ! isset( $this->supported_providers[ $provider ] ) ) {
				throw new Exception( 'Unsupported provider: ' . $provider );
			}

			$provider_config = $this->supported_providers[ $provider ];
			$file_extension  = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );

			// Check if file format is supported
			if ( ! in_array( $file_extension, $provider_config['file_formats'] ) ) {
				throw new Exception( 'Unsupported file format: ' . $file_extension );
			}

			// Process based on file type
			switch ( $file_extension ) {
				case 'csv':
					return $this->import_csv_results( $user_id, $file_path, $provider );
				case 'pdf':
					return $this->import_pdf_results( $user_id, $file_path, $provider );
				default:
					throw new Exception( 'Unsupported file format: ' . $file_extension );
			}
		} catch ( Exception $e ) {
			error_log( 'ENNU Lab Import Manager Error: ' . $e->getMessage() );
			return array(
				'success'  => false,
				'message'  => $e->getMessage(),
				'imported' => 0,
				'errors'   => array( $e->getMessage() ),
			);
		}
	}

	/**
	 * Import CSV lab results
	 *
	 * @param int $user_id User ID
	 * @param string $file_path CSV file path
	 * @param string $provider Lab provider
	 * @return array Import results
	 */
	private function import_csv_results( $user_id, $file_path, $provider ) {
		$results = array(
			'success'  => true,
			'message'  => 'Import completed successfully',
			'imported' => 0,
			'errors'   => array(),
		);

		// Load provider mapping
		$mapping = $this->load_provider_mapping( $provider );

		$handle = fopen( $file_path, 'r' );
		if ( ! $handle ) {
			throw new Exception( 'Could not open file: ' . $file_path );
		}

		// Read header
		$header = fgetcsv( $handle );
		if ( ! $header ) {
			throw new Exception( 'Could not read CSV header' );
		}

		// Map columns using provider mapping
		$column_mapping = $this->map_csv_columns( $header, $mapping );

		$biomarker_manager = new ENNU_Biomarker_Manager();

		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			try {
				$biomarker_data = $this->parse_csv_row( $row, $column_mapping, $mapping );

				if ( $biomarker_data && ! empty( $biomarker_data['biomarker_name'] ) ) {
					if ( $biomarker_manager->save_biomarker_data( $user_id, $biomarker_data['biomarker_name'], $biomarker_data['data'] ) ) {
						$results['imported']++;
					} else {
						$results['errors'][] = 'Failed to save biomarker: ' . $biomarker_data['biomarker_name'];
					}
				}
			} catch ( Exception $e ) {
				$results['errors'][] = $e->getMessage();
			}
		}

		fclose( $handle );

		if ( empty( $results['imported'] ) ) {
			$results['success'] = false;
			$results['message'] = 'No biomarkers were imported';
		}

		return $results;
	}

	/**
	 * Import PDF lab results
	 *
	 * @param int $user_id User ID
	 * @param string $file_path PDF file path
	 * @param string $provider Lab provider
	 * @return array Import results
	 */
	private function import_pdf_results( $user_id, $file_path, $provider ) {
		// This would require PDF parsing library
		// For now, return error
		return array(
			'success'  => false,
			'message'  => 'PDF import not yet implemented',
			'imported' => 0,
			'errors'   => array( 'PDF import requires additional libraries' ),
		);
	}

	/**
	 * Load provider mapping configuration
	 *
	 * @param string $provider Provider name
	 * @return array Mapping configuration
	 */
	private function load_provider_mapping( $provider ) {
		$mapping_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/lab-providers/' . $this->supported_providers[ $provider ]['mapping_file'];

		if ( file_exists( $mapping_file ) ) {
			$mapping_data = file_get_contents( $mapping_file );
			return json_decode( $mapping_data, true );
		}

		// Return default mapping
		return $this->get_default_mapping();
	}

	/**
	 * Get default mapping for generic CSV
	 *
	 * @return array Default mapping
	 */
	private function get_default_mapping() {
		return array(
			'columns'           => array(
				'biomarker_name'       => array( 'Biomarker', 'Test Name', 'Analyte' ),
				'value'                => array( 'Value', 'Result', 'Test Result' ),
				'unit'                 => array( 'Unit', 'Units', 'Reference Unit' ),
				'reference_range_low'  => array( 'Reference Low', 'Low', 'Min' ),
				'reference_range_high' => array( 'Reference High', 'High', 'Max' ),
				'date_tested'          => array( 'Date Tested', 'Test Date', 'Collection Date' ),
				'lab_name'             => array( 'Lab Name', 'Laboratory', 'Provider' ),
				'status'               => array( 'Status', 'Flag', 'Result Status' ),
			),
			'biomarker_mapping' => array(
				'Testosterone' => array( 'testosterone', 'total testosterone', 'free testosterone' ),
				'Vitamin D'    => array( 'vitamin d', '25-oh vitamin d', 'vitamin d3' ),
				'Cortisol'     => array( 'cortisol', 'am cortisol', 'pm cortisol' ),
				'TSH'          => array( 'tsh', 'thyroid stimulating hormone' ),
				'T3'           => array( 't3', 'triiodothyronine', 'free t3' ),
				'T4'           => array( 't4', 'thyroxine', 'free t4' ),
				'Glucose'      => array( 'glucose', 'fasting glucose' ),
				'HbA1c'        => array( 'hba1c', 'hemoglobin a1c', 'a1c' ),
				'Insulin'      => array( 'insulin', 'fasting insulin' ),
				'CRP'          => array( 'crp', 'c-reactive protein', 'high sensitivity crp' ),
				'Homocysteine' => array( 'homocysteine', 'hcy' ),
				'Vitamin B12'  => array( 'vitamin b12', 'b12', 'cobalamin' ),
				'Folate'       => array( 'folate', 'folic acid' ),
				'Iron'         => array( 'iron', 'serum iron' ),
				'Ferritin'     => array( 'ferritin', 'serum ferritin' ),
			),
		);
	}

	/**
	 * Map CSV columns to biomarker fields
	 *
	 * @param array $header CSV header
	 * @param array $mapping Provider mapping
	 * @return array Column mapping
	 */
	private function map_csv_columns( $header, $mapping ) {
		$column_mapping = array();

		foreach ( $mapping['columns'] as $field => $possible_names ) {
			foreach ( $possible_names as $name ) {
				$index = array_search( strtolower( $name ), array_map( 'strtolower', $header ) );
				if ( $index !== false ) {
					$column_mapping[ $field ] = $index;
					break;
				}
			}
		}

		return $column_mapping;
	}

	/**
	 * Parse CSV row into biomarker data
	 *
	 * @param array $row CSV row data
	 * @param array $column_mapping Column mapping
	 * @param array $mapping Provider mapping
	 * @return array Biomarker data
	 */
	private function parse_csv_row( $row, $column_mapping, $mapping ) {
		$data = array();

		// Extract data using column mapping
		foreach ( $column_mapping as $field => $index ) {
			if ( isset( $row[ $index ] ) ) {
				$data[ $field ] = trim( $row[ $index ] );
			}
		}

		// Map biomarker name to standard name
		if ( isset( $data['biomarker_name'] ) ) {
			$standard_name = $this->map_biomarker_name( $data['biomarker_name'], $mapping['biomarker_mapping'] );
			if ( $standard_name ) {
				$data['biomarker_name'] = $standard_name;
			}
		}

		// Validate required fields
		if ( empty( $data['biomarker_name'] ) || empty( $data['value'] ) ) {
			return null;
		}

		// Clean and format data
		$data['value']                = $this->clean_numeric_value( $data['value'] );
		$data['reference_range_low']  = isset( $data['reference_range_low'] ) ? $this->clean_numeric_value( $data['reference_range_low'] ) : '';
		$data['reference_range_high'] = isset( $data['reference_range_high'] ) ? $this->clean_numeric_value( $data['reference_range_high'] ) : '';

		// Set default date if not provided
		if ( empty( $data['date_tested'] ) ) {
			$data['date_tested'] = date( 'Y-m-d' );
		}

		return array(
			'biomarker_name' => $data['biomarker_name'],
			'data'           => $data,
		);
	}

	/**
	 * Map biomarker name to standard name
	 *
	 * @param string $biomarker_name Raw biomarker name
	 * @param array $mapping Biomarker mapping
	 * @return string Standard biomarker name
	 */
	private function map_biomarker_name( $biomarker_name, $mapping ) {
		$biomarker_name_lower = strtolower( trim( $biomarker_name ) );

		foreach ( $mapping as $standard_name => $variations ) {
			foreach ( $variations as $variation ) {
				if ( strpos( $biomarker_name_lower, strtolower( $variation ) ) !== false ) {
					return $standard_name;
				}
			}
		}

		return $biomarker_name; // Return original if no mapping found
	}

	/**
	 * Clean numeric value
	 *
	 * @param string $value Raw value
	 * @return float Cleaned numeric value
	 */
	private function clean_numeric_value( $value ) {
		// Remove non-numeric characters except decimal point
		$cleaned = preg_replace( '/[^0-9.]/', '', $value );
		return floatval( $cleaned );
	}

	/**
	 * Import from lab API
	 *
	 * @param int $user_id User ID
	 * @param string $provider Lab provider
	 * @param array $credentials API credentials
	 * @return array Import results
	 */
	public function import_from_api( $user_id, $provider, $credentials ) {
		try {
			if ( ! isset( $this->supported_providers[ $provider ] ) ) {
				throw new Exception( 'Unsupported provider: ' . $provider );
			}

			$provider_config = $this->supported_providers[ $provider ];

			if ( ! $provider_config['api_endpoint'] ) {
				throw new Exception( 'Provider does not support API import' );
			}

			// This would implement actual API calls
			// For now, return placeholder
			return array(
				'success'  => false,
				'message'  => 'API import not yet implemented',
				'imported' => 0,
				'errors'   => array( 'API integration requires provider-specific implementation' ),
			);

		} catch ( Exception $e ) {
			error_log( 'ENNU Lab Import Manager API Error: ' . $e->getMessage() );
			return array(
				'success'  => false,
				'message'  => $e->getMessage(),
				'imported' => 0,
				'errors'   => array( $e->getMessage() ),
			);
		}
	}

	/**
	 * Get supported providers
	 *
	 * @return array Supported providers
	 */
	public function get_supported_providers() {
		return $this->supported_providers;
	}

	/**
	 * Validate import file
	 *
	 * @param string $file_path File path
	 * @param string $provider Provider name
	 * @return array Validation results
	 */
	public function validate_import_file( $file_path, $provider ) {
		$results = array(
			'valid'    => false,
			'errors'   => array(),
			'warnings' => array(),
			'preview'  => array(),
		);

		try {
			if ( ! file_exists( $file_path ) ) {
				$results['errors'][] = 'File not found';
				return $results;
			}

			if ( ! isset( $this->supported_providers[ $provider ] ) ) {
				$results['errors'][] = 'Unsupported provider';
				return $results;
			}

			$provider_config = $this->supported_providers[ $provider ];
			$file_extension  = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );

			if ( ! in_array( $file_extension, $provider_config['file_formats'] ) ) {
				$results['errors'][] = 'Unsupported file format: ' . $file_extension;
				return $results;
			}

			if ( $file_extension === 'csv' ) {
				$results = $this->validate_csv_file( $file_path, $provider );
			}
		} catch ( Exception $e ) {
			$results['errors'][] = $e->getMessage();
		}

		return $results;
	}

	/**
	 * Validate CSV file
	 *
	 * @param string $file_path CSV file path
	 * @param string $provider Provider name
	 * @return array Validation results
	 */
	private function validate_csv_file( $file_path, $provider ) {
		$results = array(
			'valid'    => false,
			'errors'   => array(),
			'warnings' => array(),
			'preview'  => array(),
		);

		$handle = fopen( $file_path, 'r' );
		if ( ! $handle ) {
			$results['errors'][] = 'Could not open file';
			return $results;
		}

		// Read header
		$header = fgetcsv( $handle );
		if ( ! $header ) {
			$results['errors'][] = 'Could not read CSV header';
			fclose( $handle );
			return $results;
		}

		// Load mapping
		$mapping        = $this->load_provider_mapping( $provider );
		$column_mapping = $this->map_csv_columns( $header, $mapping );

		// Check required columns
		$required_columns = array( 'biomarker_name', 'value' );
		foreach ( $required_columns as $required ) {
			if ( ! isset( $column_mapping[ $required ] ) ) {
				$results['errors'][] = 'Missing required column: ' . $required;
			}
		}

		// Read preview rows
		$preview_count = 0;
		while ( ( $row = fgetcsv( $handle ) ) !== false && $preview_count < 5 ) {
			$biomarker_data = $this->parse_csv_row( $row, $column_mapping, $mapping );
			if ( $biomarker_data ) {
				$results['preview'][] = $biomarker_data;
			}
			$preview_count++;
		}

		fclose( $handle );

		// Set valid if no errors
		if ( empty( $results['errors'] ) ) {
			$results['valid'] = true;
		}

		return $results;
	}
}
