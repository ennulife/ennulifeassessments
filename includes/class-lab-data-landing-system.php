<?php
/**
 * ENNU Lab Data Landing System
 * Provides dedicated landing page and management for lab data imports
 *
 * @package ENNU_Life
 * @version 62.2.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Lab_Data_Landing_System {

	/**
	 * Initialize lab data landing system
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_rewrite_rules' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle_lab_data_landing' ) );
		add_action( 'wp_ajax_ennu_upload_lab_data', array( __CLASS__, 'handle_lab_data_upload' ) );
		add_action( 'wp_ajax_ennu_validate_lab_data', array( __CLASS__, 'handle_lab_data_validation' ) );
		add_action( 'wp_ajax_ennu_get_csv_template', array( __CLASS__, 'handle_get_csv_template' ) );
		add_action( 'wp_ajax_ennu_upload_pdf', array( __CLASS__, 'handle_pdf_upload' ) );

		error_log( 'ENNU Lab Data Landing System: Initialized' );
	}

	/**
	 * Add rewrite rules for lab data landing page
	 */
	public static function add_rewrite_rules() {
		add_rewrite_rule( '^lab-data-import/?$', 'index.php?ennu_lab_data_landing=1', 'top' );
		add_rewrite_tag( '%ennu_lab_data_landing%', '([^&]+)' );
	}

	/**
	 * Handle lab data landing page display
	 */
	public static function handle_lab_data_landing() {
		if ( ! get_query_var( 'ennu_lab_data_landing' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'ennu_medical_director' ) ) {
			wp_die( 'Insufficient permissions to access lab data import.' );
		}

		self::display_lab_data_landing_page();
		exit;
	}

	/**
	 * Display lab data landing page
	 */
	private static function display_lab_data_landing_page() {
		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title>Lab Data Import - ENNU Life</title>
			<?php wp_head(); ?>
			<style>
				.lab-data-landing {
					max-width: 1200px;
					margin: 0 auto;
					padding: 20px;
					font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
				}
				.lab-data-header {
					text-align: center;
					margin-bottom: 40px;
				}
				.lab-data-sections {
					display: grid;
					grid-template-columns: 1fr 1fr;
					gap: 30px;
					margin-bottom: 40px;
				}
				.lab-data-section {
					background: #f8f9fa;
					padding: 30px;
					border-radius: 8px;
					border: 1px solid #e9ecef;
				}
				.section-title {
					font-size: 1.5em;
					margin-bottom: 20px;
					color: #2c3e50;
				}
				.upload-area {
					border: 2px dashed #007cba;
					padding: 40px;
					text-align: center;
					border-radius: 8px;
					background: #fff;
					margin-bottom: 20px;
				}
				.upload-area.dragover {
					background: #e3f2fd;
					border-color: #1976d2;
				}
				.btn {
					background: #007cba;
					color: white;
					padding: 12px 24px;
					border: none;
					border-radius: 4px;
					cursor: pointer;
					text-decoration: none;
					display: inline-block;
					margin: 5px;
				}
				.btn:hover {
					background: #005a87;
				}
				.btn-secondary {
					background: #6c757d;
				}
				.btn-secondary:hover {
					background: #545b62;
				}
				.template-list {
					list-style: none;
					padding: 0;
				}
				.template-list li {
					padding: 10px;
					background: white;
					margin-bottom: 10px;
					border-radius: 4px;
					border: 1px solid #dee2e6;
					display: flex;
					justify-content: space-between;
					align-items: center;
				}
				.validation-results {
					margin-top: 20px;
					padding: 15px;
					border-radius: 4px;
					display: none;
				}
				.validation-success {
					background: #d4edda;
					border: 1px solid #c3e6cb;
					color: #155724;
				}
				.validation-error {
					background: #f8d7da;
					border: 1px solid #f5c6cb;
					color: #721c24;
				}
				.progress-bar {
					width: 100%;
					height: 20px;
					background: #e9ecef;
					border-radius: 10px;
					overflow: hidden;
					margin: 10px 0;
					display: none;
				}
				.progress-fill {
					height: 100%;
					background: #007cba;
					width: 0%;
					transition: width 0.3s ease;
				}
				@media (max-width: 768px) {
					.lab-data-sections {
						grid-template-columns: 1fr;
					}
				}
			</style>
		</head>
		<body>
			<div class="lab-data-landing">
				<div class="lab-data-header">
					<h1>Lab Data Import Center</h1>
					<p>Import and manage patient lab results with validation and error checking</p>
				</div>

				<div class="lab-data-sections">
					<div class="lab-data-section">
						<h2 class="section-title">Upload Lab Data</h2>
						<div class="upload-area" id="uploadArea">
							<p><strong>Drag and drop CSV files here</strong></p>
							<p>or</p>
							<input type="file" id="labDataFile" accept=".csv" style="display: none;">
							<button class="btn" onclick="document.getElementById('labDataFile').click()">
								Choose CSV File
							</button>
						</div>
						<div class="progress-bar" id="progressBar">
							<div class="progress-fill" id="progressFill"></div>
						</div>
						<div class="validation-results" id="validationResults"></div>
						<div style="margin-top: 20px;">
							<button class="btn" id="validateBtn" style="display: none;">Validate Data</button>
							<button class="btn" id="importBtn" style="display: none;">Import Data</button>
						</div>
					</div>

					<div class="lab-data-section">
						<h2 class="section-title">CSV Templates</h2>
						<p>Download standardized CSV templates for different lab providers:</p>
						<ul class="template-list">
							<li>
								<span>Quest Diagnostics Template</span>
								<button class="btn btn-secondary" onclick="downloadTemplate('quest')">Download</button>
							</li>
							<li>
								<span>LabCorp Template</span>
								<button class="btn btn-secondary" onclick="downloadTemplate('labcorp')">Download</button>
							</li>
							<li>
								<span>Generic Lab Template</span>
								<button class="btn btn-secondary" onclick="downloadTemplate('generic')">Download</button>
							</li>
							<li>
								<span>ENNU Life Full Panel Template</span>
								<button class="btn btn-secondary" onclick="downloadTemplate('ennu_full')">Download</button>
							</li>
						</ul>
					</div>
				</div>

				<div class="lab-data-section">
					<h2 class="section-title">Import Instructions</h2>
					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
						<div>
							<h3>Supported File Formats</h3>
							<ul>
								<li>CSV files (.csv)</li>
								<li>UTF-8 encoding recommended</li>
								<li>Maximum file size: 10MB</li>
								<li>Maximum 1000 records per file</li>
							</ul>
						</div>
						<div>
							<h3>Required Fields</h3>
							<ul>
								<li>Patient ID or Email</li>
								<li>Biomarker Name</li>
								<li>Test Value</li>
								<li>Test Date</li>
								<li>Reference Range (optional)</li>
							</ul>
						</div>
					</div>
				</div>
			</div>

			<script>
				let selectedFile = null;
				let validationPassed = false;

				const uploadArea = document.getElementById('uploadArea');
				const fileInput = document.getElementById('labDataFile');
				const validateBtn = document.getElementById('validateBtn');
				const importBtn = document.getElementById('importBtn');
				const progressBar = document.getElementById('progressBar');
				const progressFill = document.getElementById('progressFill');
				const validationResults = document.getElementById('validationResults');

				uploadArea.addEventListener('dragover', (e) => {
					e.preventDefault();
					uploadArea.classList.add('dragover');
				});

				uploadArea.addEventListener('dragleave', () => {
					uploadArea.classList.remove('dragover');
				});

				uploadArea.addEventListener('drop', (e) => {
					e.preventDefault();
					uploadArea.classList.remove('dragover');
					const files = e.dataTransfer.files;
					if (files.length > 0) {
						handleFileSelection(files[0]);
					}
				});

				fileInput.addEventListener('change', (e) => {
					if (e.target.files.length > 0) {
						handleFileSelection(e.target.files[0]);
					}
				});

				function handleFileSelection(file) {
					if (!file.name.toLowerCase().endsWith('.csv')) {
						alert('Please select a CSV file.');
						return;
					}

					selectedFile = file;
					uploadArea.innerHTML = `<p><strong>Selected:</strong> ${file.name}</p><p>Size: ${(file.size / 1024).toFixed(1)} KB</p>`;
					validateBtn.style.display = 'inline-block';
					importBtn.style.display = 'none';
					validationPassed = false;
					validationResults.style.display = 'none';
				}

				function downloadTemplate(templateType) {
					const formData = new FormData();
					formData.append('action', 'ennu_get_csv_template');
					formData.append('nonce', '<?php echo wp_create_nonce( 'ennu_ajax_nonce' ); ?>');
					formData.append('template_type', templateType);

					fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
						method: 'POST',
						body: formData
					})
					.then(response => response.blob())
					.then(blob => {
						const url = window.URL.createObjectURL(blob);
						const a = document.createElement('a');
						a.style.display = 'none';
						a.href = url;
						a.download = `${templateType}_lab_template.csv`;
						document.body.appendChild(a);
						a.click();
						window.URL.revokeObjectURL(url);
						document.body.removeChild(a);
					})
					.catch(error => {
						console.error('Template download error:', error);
						alert('Error downloading template. Please try again.');
					});
				}
			</script>
			<?php wp_footer(); ?>
		</body>
		</html>
		<?php
	}

	/**
	 * Handle lab data upload AJAX request
	 */
	public static function handle_lab_data_upload() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'ennu_medical_director' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		if ( ! isset( $_FILES['lab_data_file'] ) ) {
			wp_send_json_error( 'No file uploaded' );
		}

		$file = $_FILES['lab_data_file'];

		if ( $file['error'] !== UPLOAD_ERR_OK ) {
			wp_send_json_error( 'File upload error: ' . $file['error'] );
		}

		if ( ! self::validate_file_type( $file ) ) {
			wp_send_json_error( 'Invalid file type. Only CSV files are allowed.' );
		}

		$import_result = self::process_lab_data_import( $file );

		if ( $import_result['success'] ) {
			wp_send_json_success( $import_result['data'] );
		} else {
			wp_send_json_error( $import_result['message'] );
		}
	}

	/**
	 * Handle lab data validation AJAX request
	 */
	public static function handle_lab_data_validation() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'ennu_medical_director' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
		}

		if ( ! isset( $_FILES['lab_data_file'] ) ) {
			wp_send_json_error( 'No file uploaded' );
		}

		$file = $_FILES['lab_data_file'];

		if ( $file['error'] !== UPLOAD_ERR_OK ) {
			wp_send_json_error( 'File upload error: ' . $file['error'] );
		}

		$validation_result = self::validate_lab_data_file( $file );

		if ( $validation_result['success'] ) {
			wp_send_json_success( $validation_result['data'] );
		} else {
			wp_send_json_error( $validation_result['message'] );
		}
	}

	/**
	 * Handle CSV template download AJAX request
	 */
	public static function handle_get_csv_template() {
		check_ajax_referer( 'ennu_ajax_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'ennu_medical_director' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		$template_type    = sanitize_text_field( $_POST['template_type'] ?? 'generic' );
		$template_content = self::generate_csv_template( $template_type );

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="' . $template_type . '_lab_template.csv"' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Pragma: public' );

		echo $template_content;
		exit;
	}

	/**
	 * Handle PDF upload via AJAX
	 */
	public static function handle_pdf_upload() {
		// Debug logging
		error_log( 'ENNU PDF Upload: AJAX request received' );
		error_log( 'ENNU PDF Upload: POST data: ' . print_r( $_POST, true ) );
		error_log( 'ENNU PDF Upload: FILES data: ' . print_r( $_FILES, true ) );
		
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_ajax_nonce' ) ) {
			wp_die( json_encode( array(
				'success' => false,
				'message' => 'Security check failed.',
				'notification' => array(
					'type' => 'error',
					'title' => 'Security Error',
					'message' => 'Invalid security token. Please refresh the page and try again.',
				),
			) ) );
		}
		
		// Check user permissions
		if ( ! is_user_logged_in() ) {
			wp_die( json_encode( array(
				'success' => false,
				'message' => 'User not logged in.',
				'notification' => array(
					'type' => 'error',
					'title' => 'Authentication Required',
					'message' => 'Please log in to upload LabCorp results.',
				),
			) ) );
		}
		
		$user_id = get_current_user_id();
		
		// Check if file was uploaded
		if ( ! isset( $_FILES['labcorp_pdf'] ) || $_FILES['labcorp_pdf']['error'] !== UPLOAD_ERR_OK ) {
			$error_message = 'No file uploaded or upload error occurred.';
			if ( isset( $_FILES['labcorp_pdf']['error'] ) ) {
				switch ( $_FILES['labcorp_pdf']['error'] ) {
					case UPLOAD_ERR_INI_SIZE:
						$error_message = 'File too large. Maximum size is 10MB.';
						break;
					case UPLOAD_ERR_PARTIAL:
						$error_message = 'File upload was incomplete.';
						break;
					case UPLOAD_ERR_NO_FILE:
						$error_message = 'No file was uploaded.';
						break;
					default:
						$error_message = 'Upload error occurred.';
				}
			}
			
			wp_die( json_encode( array(
				'success' => false,
				'message' => $error_message,
				'notification' => array(
					'type' => 'error',
					'title' => 'Upload Failed',
					'message' => $error_message,
				),
			) ) );
		}
		
		$file = $_FILES['labcorp_pdf'];
		
		// Validate file type
		$allowed_types = array( 'application/pdf' );
		$file_type = mime_content_type( $file['tmp_name'] );
		
		if ( ! in_array( $file_type, $allowed_types ) ) {
			wp_die( json_encode( array(
				'success' => false,
				'message' => 'Invalid file type. Only PDF files are allowed.',
				'notification' => array(
					'type' => 'error',
					'title' => 'Invalid File Type',
					'message' => 'Please upload a valid PDF file from LabCorp.',
				),
			) ) );
		}
		
		// Validate file size (10MB max)
		if ( $file['size'] > 10 * 1024 * 1024 ) {
			wp_die( json_encode( array(
				'success' => false,
				'message' => 'File too large. Maximum size is 10MB.',
				'notification' => array(
					'type' => 'error',
					'title' => 'File Too Large',
					'message' => 'Please upload a PDF file smaller than 10MB.',
				),
			) ) );
		}
		
		// Create upload directory if it doesn't exist
		$upload_dir = wp_upload_dir();
		$labcorp_dir = $upload_dir['basedir'] . '/labcorp-pdfs/';
		
		if ( ! file_exists( $labcorp_dir ) ) {
			wp_mkdir_p( $labcorp_dir );
		}
		
		// Generate unique filename
		$filename = 'labcorp_' . $user_id . '_' . time() . '.pdf';
		$file_path = $labcorp_dir . $filename;
		
		// Move uploaded file
		if ( ! move_uploaded_file( $file['tmp_name'], $file_path ) ) {
			wp_die( json_encode( array(
				'success' => false,
				'message' => 'Failed to save uploaded file.',
				'notification' => array(
					'type' => 'error',
					'title' => 'File Save Failed',
					'message' => 'Unable to process the uploaded file. Please try again.',
				),
			) ) );
		}
		
		// Process PDF
		try {
			$processor = new ENNU_PDF_Processor();
			$result = $processor->process_labcorp_pdf( $file_path, $user_id );
			
			// Clean up uploaded file
			unlink( $file_path );
			
			// Log success
			error_log( 'ENNU PDF Upload: Success - ' . print_r( $result, true ) );
			
			// Return detailed result with notification
			wp_die( json_encode( $result ) );
			
		} catch ( Exception $e ) {
			// Clean up uploaded file
			if ( file_exists( $file_path ) ) {
				unlink( $file_path );
			}
			
			wp_die( json_encode( array(
				'success' => false,
				'message' => 'PDF processing failed: ' . $e->getMessage(),
				'notification' => array(
					'type' => 'error',
					'title' => 'Processing Failed',
					'message' => 'Unable to process the LabCorp PDF. Please ensure it contains valid biomarker data.',
				),
			) ) );
		}
	}

	/**
	 * Validate file type
	 *
	 * @param array $file File data
	 * @return bool Whether file is valid
	 */
	private static function validate_file_type( $file ) {
		$allowed_types  = array( 'text/csv', 'application/csv', 'text/plain', 'application/pdf' );
		$file_extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

		return ( $file_extension === 'csv' && in_array( $file['type'], $allowed_types, true ) ) ||
			   ( $file_extension === 'pdf' && $file['type'] === 'application/pdf' );
	}

	/**
	 * Validate lab data file
	 *
	 * @param array $file File data
	 * @return array Validation result
	 */
	private static function validate_lab_data_file( $file ) {
		$csv_data = self::parse_csv_file( $file['tmp_name'] );

		if ( empty( $csv_data ) ) {
			return array(
				'success' => false,
				'message' => 'Unable to parse CSV file or file is empty',
			);
		}

		$validation_errors = array();
		$record_count      = 0;
		$valid_biomarkers  = 0;
		$patient_count     = 0;
		$patients_found    = array();

		foreach ( $csv_data as $row_index => $row ) {
			if ( $row_index === 0 ) {
				continue;
			}

			$record_count++;

			$required_fields = array( 'patient_id', 'biomarker_name', 'test_value', 'test_date' );
			foreach ( $required_fields as $field ) {
				if ( empty( $row[ $field ] ) ) {
					$validation_errors[] = "Row {$row_index}: Missing required field '{$field}'";
				}
			}

			if ( ! empty( $row['biomarker_name'] ) && self::is_valid_biomarker( $row['biomarker_name'] ) ) {
				$valid_biomarkers++;
			}

			if ( ! empty( $row['patient_id'] ) && ! in_array( $row['patient_id'], $patients_found, true ) ) {
				$patients_found[] = $row['patient_id'];
				$patient_count++;
			}
		}

		if ( ! empty( $validation_errors ) ) {
			return array(
				'success' => false,
				'message' => 'Validation errors found: ' . implode( '; ', array_slice( $validation_errors, 0, 5 ) ),
			);
		}

		return array(
			'success' => true,
			'data'    => array(
				'record_count'     => $record_count,
				'valid_biomarkers' => $valid_biomarkers,
				'patient_count'    => $patient_count,
			),
		);
	}

	/**
	 * Process lab data import
	 *
	 * @param array $file File data
	 * @return array Import result
	 */
	private static function process_lab_data_import( $file ) {
		$file_extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
		
		if ( $file_extension === 'pdf' ) {
			return self::process_pdf_import( $file );
		} else {
			// Existing CSV processing logic
			$csv_data = self::parse_csv_file( $file['tmp_name'] );

			if ( empty( $csv_data ) ) {
				return array(
					'success' => false,
					'message' => 'Unable to parse CSV file or file is empty',
				);
			}

			$imported_count   = 0;
			$patients_updated = array();
			$flagged_count    = 0;

			foreach ( $csv_data as $row_index => $row ) {
				if ( $row_index === 0 ) {
					continue;
				}

				$import_result = self::import_lab_record( $row );

				if ( $import_result['success'] ) {
					$imported_count++;
					if ( ! in_array( $import_result['user_id'], $patients_updated, true ) ) {
						$patients_updated[] = $import_result['user_id'];
					}
				}

				if ( $import_result['flagged'] ) {
					$flagged_count++;
				}
			}

			return array(
				'success' => true,
				'message' => "Successfully imported {$imported_count} records for " . count( $patients_updated ) . " patients. {$flagged_count} records flagged for review.",
				'data'    => array(
					'imported_count'   => $imported_count,
					'patients_updated' => count( $patients_updated ),
					'flagged_count'    => $flagged_count,
				),
			);
		}
	}

	/**
	 * Process PDF import for LabCorp documents
	 *
	 * @param array $file File data
	 * @return array Import result
	 */
	private static function process_pdf_import( $file ) {
		// Check if PDF processor class exists
		if ( ! class_exists( 'ENNU_PDF_Processor' ) ) {
			return array(
				'success' => false,
				'message' => 'PDF processor not available. Please ensure the ENNU_PDF_Processor class is loaded.',
			);
		}
		
		// Create PDF processor instance
		$pdf_processor = new ENNU_PDF_Processor();
		
		// Get current user ID
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return array(
				'success' => false,
				'message' => 'User not authenticated.',
			);
		}
		
		// Process the PDF
		$result = $pdf_processor->process_labcorp_pdf( $file['tmp_name'], $user_id );
		
		return $result;
	}

	/**
	 * Parse CSV file
	 *
	 * @param string $file_path File path
	 * @return array CSV data
	 */
	private static function parse_csv_file( $file_path ) {
		$csv_data = array();

		if ( ( $handle = fopen( $file_path, 'r' ) ) !== false ) {
			$header = fgetcsv( $handle );

			if ( $header ) {
				$csv_data[] = array_combine( $header, $header );

				while ( ( $data = fgetcsv( $handle ) ) !== false ) {
					if ( count( $data ) === count( $header ) ) {
						$csv_data[] = array_combine( $header, $data );
					}
				}
			}

			fclose( $handle );
		}

		return $csv_data;
	}

	/**
	 * Import single lab record
	 *
	 * @param array $row CSV row data
	 * @return array Import result
	 */
	private static function import_lab_record( $row ) {
		$patient_id      = sanitize_text_field( $row['patient_id'] ?? '' );
		$biomarker_name  = sanitize_text_field( $row['biomarker_name'] ?? '' );
		$test_value      = sanitize_text_field( $row['test_value'] ?? '' );
		$test_date       = sanitize_text_field( $row['test_date'] ?? '' );
		$reference_range = sanitize_text_field( $row['reference_range'] ?? '' );

		$user = self::find_user_by_identifier( $patient_id );

		if ( ! $user ) {
			return array(
				'success' => false,
				'message' => "User not found for identifier: {$patient_id}",
			);
		}

		$biomarker_data = array(
			$biomarker_name => array(
				'value'           => $test_value,
				'date_tested'     => $test_date,
				'reference_range' => $reference_range,
			)
		);

		// Use the new centralized saving method
		$import_success = ENNU_Biomarker_Manager::save_user_biomarkers( $user->ID, $biomarker_data, 'lab_import' );

		$flagged = false;
		if ( class_exists( 'ENNU_Biomarker_Flag_Manager' ) ) {
			$flag_manager = new ENNU_Biomarker_Flag_Manager();
			$should_flag  = $flag_manager->should_auto_flag(
				$biomarker_name,
				array(
					'value'  => $test_value,
					'status' => self::determine_biomarker_status( $biomarker_name, $test_value, $reference_range ),
				)
			);

			if ( $should_flag ) {
				$flag_manager->flag_biomarker( $user->ID, $biomarker_name, 'auto_flagged', 'Imported lab result requires attention' );
				$flagged = true;
			}
		}

		return array(
			'success' => $import_success,
			'user_id' => $user->ID,
			'flagged' => $flagged,
		);
	}

	/**
	 * Find user by identifier (ID or email)
	 *
	 * @param string $identifier User identifier
	 * @return WP_User|null User object or null
	 */
	private static function find_user_by_identifier( $identifier ) {
		if ( is_numeric( $identifier ) ) {
			$user = get_user_by( 'id', intval( $identifier ) );
			if ( $user ) {
				return $user;
			}
		}

		if ( is_email( $identifier ) ) {
			$user = get_user_by( 'email', $identifier );
			if ( $user ) {
				return $user;
			}
		}

		$user = get_user_by( 'login', $identifier );
		if ( $user ) {
			return $user;
		}

		return null;
	}

	/**
	 * Check if biomarker name is valid
	 *
	 * @param string $biomarker_name Biomarker name
	 * @return bool Whether biomarker is valid
	 */
	private static function is_valid_biomarker( $biomarker_name ) {
		$valid_biomarkers = array(
			'testosterone_total',
			'testosterone_free',
			'estradiol',
			'thyroid_tsh',
			'thyroid_t3',
			'thyroid_t4',
			'vitamin_d',
			'vitamin_b12',
			'folate',
			'iron',
			'ferritin',
			'hemoglobin_a1c',
			'glucose_fasting',
			'cholesterol_total',
			'cholesterol_ldl',
			'cholesterol_hdl',
			'triglycerides',
			'crp',
			'cortisol',
			'dhea_s',
			'igf_1',
			'insulin',
			'leptin',
			'adiponectin',
			'homocysteine',
			'uric_acid',
		);

		$normalized_name = strtolower( str_replace( array( ' ', '-' ), '_', $biomarker_name ) );
		return in_array( $normalized_name, $valid_biomarkers, true );
	}

	/**
	 * Determine biomarker status based on value and reference range
	 *
	 * @param string $biomarker_name Biomarker name
	 * @param string $test_value Test value
	 * @param string $reference_range Reference range
	 * @return string Status (normal, low, high, critical)
	 */
	private static function determine_biomarker_status( $biomarker_name, $test_value, $reference_range ) {
		if ( empty( $reference_range ) ) {
			return 'unknown';
		}

		$value = floatval( $test_value );

		if ( preg_match( '/(\d+\.?\d*)\s*-\s*(\d+\.?\d*)/', $reference_range, $matches ) ) {
			$min = floatval( $matches[1] );
			$max = floatval( $matches[2] );

			if ( $value < $min ) {
				return 'low';
			} elseif ( $value > $max ) {
				return 'high';
			} else {
				return 'normal';
			}
		}

		return 'unknown';
	}

	/**
	 * Generate CSV template
	 *
	 * @param string $template_type Template type
	 * @return string CSV content
	 */
	private static function generate_csv_template( $template_type ) {
		$templates = array(
			'quest'     => array(
				'headers'     => array( 'patient_id', 'biomarker_name', 'test_value', 'test_date', 'reference_range', 'units', 'status' ),
				'sample_data' => array(),
			),
			'labcorp'   => array(
				'headers'     => array( 'patient_id', 'test_name', 'result', 'date_collected', 'reference_interval', 'unit', 'flag' ),
				'sample_data' => array(),
			),
			'generic'   => array(
				'headers'     => array( 'patient_id', 'biomarker_name', 'test_value', 'test_date', 'reference_range' ),
				'sample_data' => array(),
			),
			'ennu_full' => array(
				'headers'     => array( 'patient_id', 'biomarker_name', 'test_value', 'test_date', 'reference_range', 'units', 'lab_provider', 'notes' ),
				'sample_data' => array(),
			),
		);

		$template = $templates[ $template_type ] ?? $templates['generic'];

		$csv_content = implode( ',', $template['headers'] ) . "\n";

		foreach ( $template['sample_data'] as $row ) {
			$csv_content .= implode( ',', $row ) . "\n";
		}

		return $csv_content;
	}
}
