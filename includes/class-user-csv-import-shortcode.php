<?php
/**
 * ENNU Life User CSV Import Shortcode
 * Allows users to import biomarkers to their own profile via shortcode
 *
 * @package ENNU_Life
 * @copyright Copyright (c) 2024, Very Good Plugins, https://verygoodplugins.com
 * @license   GPL-3.0+
 * @since     64.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_User_CSV_Import_Shortcode {

	/**
	 * Initialize the shortcode
	 */
	public function __construct() {
		add_shortcode( 'ennu_user_csv_import', array( $this, 'render_import_form' ) );
		add_action( 'wp_ajax_ennu_user_csv_import', array( $this, 'handle_user_csv_import' ) );
		add_action( 'wp_ajax_nopriv_ennu_user_csv_import', array( $this, 'handle_user_csv_import' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_frontend_assets() {
		// Only enqueue if shortcode is present on the page
		global $post;
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ennu_user_csv_import' ) ) {
			wp_enqueue_style( 'ennu-user-csv-import', ENNU_LIFE_PLUGIN_URL . 'assets/css/user-csv-import.css', array(), ENNU_LIFE_VERSION );
			wp_enqueue_script( 'ennu-user-csv-import', ENNU_LIFE_PLUGIN_URL . 'assets/js/user-csv-import.js', array( 'jquery' ), ENNU_LIFE_VERSION, true );

			wp_localize_script(
				'ennu-user-csv-import',
				'ennuUserCSVImport',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'ennu_user_csv_import_nonce' ),
					'strings' => array(
						'selectFile' => __( 'Please select a CSV file', 'ennulifeassessments' ),
						'processing' => __( 'Processing...', 'ennulifeassessments' ),
						'success'    => __( 'Import successful!', 'ennulifeassessments' ),
						'error'      => __( 'Import failed', 'ennulifeassessments' ),
					),
				)
			);
		}
	}

	/**
	 * Render the import form shortcode
	 */
	public function render_import_form( $atts ) {
		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			return $this->render_login_message();
		}

		$atts = shortcode_atts(
			array(
				'show_instructions' => 'true',
				'show_sample'       => 'true',
				'max_file_size'     => '5',
				'allowed_types'     => 'csv',
			),
			$atts,
			'ennu_user_csv_import'
		);

		$current_user = wp_get_current_user();
		$user_biomarkers = get_user_meta( $current_user->ID, 'ennu_biomarker_data', true );
		$biomarker_count = is_array( $user_biomarkers ) ? count( $user_biomarkers ) : 0;

		ob_start();
		?>
		<div class="ennu-user-csv-import-container">
			<div class="ennu-import-header">
				<h2><?php esc_html_e( 'Import Your Biomarker Data', 'ennulifeassessments' ); ?></h2>
				<p><?php esc_html_e( 'Upload your lab results to track your health biomarkers.', 'ennulifeassessments' ); ?></p>
			</div>

			<div class="ennu-user-info">
				<p><strong><?php esc_html_e( 'Importing for:', 'ennulifeassessments' ); ?></strong> <?php echo esc_html( $current_user->display_name ); ?></p>
				<p><strong><?php esc_html_e( 'Current biomarkers:', 'ennulifeassessments' ); ?></strong> <?php echo esc_html( $biomarker_count ); ?></p>
			</div>

			<?php if ( $atts['show_instructions'] === 'true' ) : ?>
				<div class="ennu-import-instructions">
					<h3><?php esc_html_e( 'How to Import Your Data', 'ennulifeassessments' ); ?></h3>
					<ol>
						<li><?php esc_html_e( 'Download your lab results as a CSV file', 'ennulifeassessments' ); ?></li>
						<li><?php esc_html_e( 'Format your data with these columns: biomarker_name, value, unit, date', 'ennulifeassessments' ); ?></li>
						<li><?php esc_html_e( 'Upload the file below', 'ennulifeassessments' ); ?></li>
						<li><?php esc_html_e( 'Review and confirm the import', 'ennulifeassessments' ); ?></li>
					</ol>
				</div>
			<?php endif; ?>

			<form id="ennu-user-csv-import-form" enctype="multipart/form-data">
				<div class="ennu-form-group">
					<label for="user_csv_file"><?php esc_html_e( 'Select CSV File', 'ennulifeassessments' ); ?></label>
					<input type="file" id="user_csv_file" name="user_csv_file" accept=".csv" required />
					<small><?php esc_html_e( 'Maximum file size:', 'ennulifeassessments' ); ?> <?php echo esc_html( $atts['max_file_size'] ); ?>MB</small>
				</div>

				<div class="ennu-form-group">
					<label class="ennu-checkbox-label">
						<input type="checkbox" name="overwrite_existing" value="1" />
						<?php esc_html_e( 'Overwrite existing biomarker values', 'ennulifeassessments' ); ?>
					</label>
				</div>

				<div class="ennu-form-group">
					<label class="ennu-checkbox-label">
						<input type="checkbox" name="update_scores" value="1" checked />
						<?php esc_html_e( 'Update my life scores after import', 'ennulifeassessments' ); ?>
					</label>
				</div>

				<div class="ennu-form-actions">
					<button type="submit" class="ennu-import-btn">
						<?php esc_html_e( 'Import Biomarkers', 'ennulifeassessments' ); ?>
					</button>
					<div class="ennu-spinner" style="display: none;"></div>
				</div>
			</form>

			<div id="ennu-user-import-results" style="display: none;">
				<h3><?php esc_html_e( 'Import Results', 'ennulifeassessments' ); ?></h3>
				<div id="ennu-user-import-summary"></div>
				<div id="ennu-user-import-details"></div>
			</div>

			<?php if ( $atts['show_sample'] === 'true' ) : ?>
				<div class="ennu-sample-section">
					<h3><?php esc_html_e( 'Sample CSV Format', 'ennulifeassessments' ); ?></h3>
					<div class="ennu-sample-csv">
						<pre>biomarker_name,value,unit,date
glucose,95,mg/dL,2024-01-15
hba1c,5.2,%,2024-01-15
testosterone,650,ng/dL,2024-01-15
vitamin_d,35,ng/mL,2024-01-15
cortisol,15,μg/dL,2024-01-15</pre>
					</div>
					<p><a href="<?php echo esc_url( ENNU_LIFE_PLUGIN_URL . 'sample-biomarkers.csv' ); ?>" download class="ennu-download-sample"><?php esc_html_e( 'Download Sample CSV File', 'ennulifeassessments' ); ?></a></p>
				</div>
			<?php endif; ?>

			<div class="ennu-supported-biomarkers">
				<h3><?php esc_html_e( 'Supported Biomarkers', 'ennulifeassessments' ); ?></h3>
				<p><?php esc_html_e( 'We support 40+ common biomarkers including:', 'ennulifeassessments' ); ?></p>
				<div class="ennu-biomarker-list">
					<?php
					$biomarkers = $this->get_available_biomarkers();
					$sample_biomarkers = array_slice( $biomarkers, 0, 15, true );
					foreach ( $sample_biomarkers as $key => $info ) {
						echo '<span class="ennu-biomarker-tag">' . esc_html( $info['name'] ) . '</span>';
					}
					?>
					<span class="ennu-biomarker-tag"><?php esc_html_e( '...and more', 'ennulifeassessments' ); ?></span>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render login message for non-logged-in users
	 */
	private function render_login_message() {
		ob_start();
		?>
		<div class="ennu-user-csv-import-container">
			<div class="ennu-login-message">
				<h2><?php esc_html_e( 'Login Required', 'ennulifeassessments' ); ?></h2>
				<p><?php esc_html_e( 'You must be logged in to import biomarker data.', 'ennulifeassessments' ); ?></p>
				<div class="ennu-login-actions">
					<a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="ennu-btn ennu-btn-primary">
						<?php esc_html_e( 'Login', 'ennulifeassessments' ); ?>
					</a>
					<?php if ( get_option( 'users_can_register' ) ) : ?>
						<a href="<?php echo esc_url( wp_registration_url() ); ?>" class="ennu-btn ennu-btn-secondary">
							<?php esc_html_e( 'Register', 'ennulifeassessments' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle user CSV import via AJAX
	 */
	public function handle_user_csv_import() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_user_csv_import_nonce' ) ) {
			wp_send_json_error( __( 'Security check failed', 'ennulifeassessments' ) );
		}

		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( __( 'You must be logged in to import data', 'ennulifeassessments' ) );
		}

		$user_id = get_current_user_id();
		$result = $this->process_user_csv_import( $user_id );
		
		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Process user CSV file import
	 */
	private function process_user_csv_import( $user_id ) {
		if ( ! isset( $_FILES['user_csv_file'] ) || $_FILES['user_csv_file']['error'] !== UPLOAD_ERR_OK ) {
			return array(
				'success' => false,
				'message' => __( 'CSV file upload failed. Please check the file and try again.', 'ennulifeassessments' ),
			);
		}

		$csv_file = $_FILES['user_csv_file']['tmp_name'];
		$overwrite_existing = isset( $_POST['overwrite_existing'] ) && $_POST['overwrite_existing'] === '1';
		$update_scores = isset( $_POST['update_scores'] ) && $_POST['update_scores'] === '1';

		// Check file size (5MB limit)
		if ( $_FILES['user_csv_file']['size'] > 5 * 1024 * 1024 ) {
			return array(
				'success' => false,
				'message' => __( 'File size must be less than 5MB.', 'ennulifeassessments' ),
			);
		}

		$imported_data = array();
		$errors = array();
		$warnings = array();
		$row_number = 0;

		if ( ( $handle = fopen( $csv_file, 'r' ) ) !== false ) {
			// Skip header row
			$header = fgetcsv( $handle );
			$row_number++;

			while ( ( $row = fgetcsv( $handle ) ) !== false ) {
				$row_number++;
				
				if ( count( $row ) < 4 ) {
					$errors[] = sprintf( __( 'Row %d: Insufficient data. Expected 4 columns (biomarker_name, value, unit, date)', 'ennulifeassessments' ), $row_number );
					continue;
				}

				$biomarker_name = sanitize_key( trim( $row[0] ) );
				$value = trim( $row[1] );
				$unit = sanitize_text_field( trim( $row[2] ) );
				$date = sanitize_text_field( trim( $row[3] ) );

				// Validate biomarker name
				if ( empty( $biomarker_name ) ) {
					$errors[] = sprintf( __( 'Row %d: Empty biomarker name', 'ennulifeassessments' ), $row_number );
					continue;
				}

				// Validate value
				if ( ! is_numeric( $value ) ) {
					$errors[] = sprintf( __( 'Row %d: Invalid value for %s (%s)', 'ennulifeassessments' ), $row_number, $biomarker_name, $value );
					continue;
				}

				$value = floatval( $value );

				// Validate date
				if ( ! $this->is_valid_date( $date ) ) {
					$warnings[] = sprintf( __( 'Row %d: Invalid date format for %s (%s). Using current date.', 'ennulifeassessments' ), $row_number, $biomarker_name, $date );
					$date = current_time( 'Y-m-d' );
				}

				// Check if biomarker exists in our system
				$available_biomarkers = $this->get_available_biomarkers();
				if ( ! isset( $available_biomarkers[ $biomarker_name ] ) ) {
					$warnings[] = sprintf( __( 'Row %d: Unknown biomarker %s. Importing anyway.', 'ennulifeassessments' ), $row_number, $biomarker_name );
				}

				$imported_data[ $biomarker_name ] = array(
					'value' => $value,
					'unit' => $unit,
					'date' => $date,
					'import_date' => current_time( 'mysql' ),
					'import_method' => 'user_csv',
					'row_number' => $row_number,
				);
			}
			fclose( $handle );
		} else {
			return array(
				'success' => false,
				'message' => __( 'Could not read CSV file', 'ennulifeassessments' ),
			);
		}

		if ( empty( $imported_data ) ) {
			return array(
				'success' => false,
				'message' => __( 'No valid biomarker data found in CSV file', 'ennulifeassessments' ),
			);
		}

		// Get existing biomarker data
		$existing_data = get_user_meta( $user_id, 'ennu_biomarker_data', true );
		if ( ! is_array( $existing_data ) ) {
			$existing_data = array();
		}

		// Merge or overwrite data
		if ( $overwrite_existing ) {
			$final_data = array_merge( $existing_data, $imported_data );
		} else {
			$final_data = $existing_data;
			foreach ( $imported_data as $biomarker => $data ) {
				if ( ! isset( $existing_data[ $biomarker ] ) ) {
					$final_data[ $biomarker ] = $data;
				} else {
					$warnings[] = sprintf( __( 'Biomarker %s already exists. Skipping (use overwrite option to replace).', 'ennulifeassessments' ), $biomarker );
				}
			}
		}

		// Save the data
		update_user_meta( $user_id, 'ennu_biomarker_data', $final_data );
		update_user_meta( $user_id, 'ennu_last_user_csv_import', current_time( 'mysql' ) );

		// Update scores if requested
		if ( $update_scores && class_exists( 'ENNU_Assessment_Scoring' ) ) {
			ENNU_Assessment_Scoring::calculate_and_save_all_user_scores( $user_id, true );
		}

		// Log import
		$this->log_user_import( $user_id, $imported_data, $errors, $warnings );

		return array(
			'success' => true,
			'message' => sprintf( __( 'Successfully imported %d biomarkers to your profile', 'ennulifeassessments' ), count( $imported_data ) ),
			'imported_count' => count( $imported_data ),
			'imported_data' => $imported_data,
			'errors' => $errors,
			'warnings' => $warnings,
			'overwritten' => $overwrite_existing,
			'scores_updated' => $update_scores,
		);
	}

	/**
	 * Validate date format
	 */
	private function is_valid_date( $date ) {
		$d = DateTime::createFromFormat( 'Y-m-d', $date );
		return $d && $d->format( 'Y-m-d' ) === $date;
	}

	/**
	 * Get available biomarkers
	 */
	private function get_available_biomarkers() {
		$biomarkers = array(
			'glucose' => array( 'name' => 'Glucose', 'unit' => 'mg/dL' ),
			'hba1c' => array( 'name' => 'HbA1c', 'unit' => '%' ),
			'testosterone' => array( 'name' => 'Testosterone', 'unit' => 'ng/dL' ),
			'cortisol' => array( 'name' => 'Cortisol', 'unit' => 'μg/dL' ),
			'vitamin_d' => array( 'name' => 'Vitamin D', 'unit' => 'ng/mL' ),
			'tsh' => array( 'name' => 'TSH', 'unit' => 'μIU/mL' ),
			't3' => array( 'name' => 'T3', 'unit' => 'ng/dL' ),
			't4' => array( 'name' => 'T4', 'unit' => 'μg/dL' ),
			'cholesterol_total' => array( 'name' => 'Total Cholesterol', 'unit' => 'mg/dL' ),
			'hdl' => array( 'name' => 'HDL', 'unit' => 'mg/dL' ),
			'ldl' => array( 'name' => 'LDL', 'unit' => 'mg/dL' ),
			'triglycerides' => array( 'name' => 'Triglycerides', 'unit' => 'mg/dL' ),
			'creatinine' => array( 'name' => 'Creatinine', 'unit' => 'mg/dL' ),
			'egfr' => array( 'name' => 'eGFR', 'unit' => 'mL/min/1.73m²' ),
			'albumin' => array( 'name' => 'Albumin', 'unit' => 'g/dL' ),
			'bilirubin_total' => array( 'name' => 'Total Bilirubin', 'unit' => 'mg/dL' ),
			'ast' => array( 'name' => 'AST', 'unit' => 'U/L' ),
			'alt' => array( 'name' => 'ALT', 'unit' => 'U/L' ),
			'ggt' => array( 'name' => 'GGT', 'unit' => 'U/L' ),
			'alkaline_phosphatase' => array( 'name' => 'Alkaline Phosphatase', 'unit' => 'U/L' ),
			'wbc' => array( 'name' => 'White Blood Cells', 'unit' => 'K/μL' ),
			'rbc' => array( 'name' => 'Red Blood Cells', 'unit' => 'M/μL' ),
			'hemoglobin' => array( 'name' => 'Hemoglobin', 'unit' => 'g/dL' ),
			'hematocrit' => array( 'name' => 'Hematocrit', 'unit' => '%' ),
			'platelets' => array( 'name' => 'Platelets', 'unit' => 'K/μL' ),
			'ferritin' => array( 'name' => 'Ferritin', 'unit' => 'ng/mL' ),
			'iron' => array( 'name' => 'Iron', 'unit' => 'μg/dL' ),
			'tibc' => array( 'name' => 'TIBC', 'unit' => 'μg/dL' ),
			'vitamin_b12' => array( 'name' => 'Vitamin B12', 'unit' => 'pg/mL' ),
			'folate' => array( 'name' => 'Folate', 'unit' => 'ng/mL' ),
			'homocysteine' => array( 'name' => 'Homocysteine', 'unit' => 'μmol/L' ),
			'crp' => array( 'name' => 'C-Reactive Protein', 'unit' => 'mg/L' ),
			'esr' => array( 'name' => 'ESR', 'unit' => 'mm/hr' ),
			'uric_acid' => array( 'name' => 'Uric Acid', 'unit' => 'mg/dL' ),
			'calcium' => array( 'name' => 'Calcium', 'unit' => 'mg/dL' ),
			'magnesium' => array( 'name' => 'Magnesium', 'unit' => 'mg/dL' ),
			'phosphorus' => array( 'name' => 'Phosphorus', 'unit' => 'mg/dL' ),
			'sodium' => array( 'name' => 'Sodium', 'unit' => 'mEq/L' ),
			'potassium' => array( 'name' => 'Potassium', 'unit' => 'mEq/L' ),
			'chloride' => array( 'name' => 'Chloride', 'unit' => 'mEq/L' ),
			'co2' => array( 'name' => 'CO2', 'unit' => 'mEq/L' ),
		);

		return apply_filters( 'ennu_available_biomarkers', $biomarkers );
	}

	/**
	 * Log user import activity
	 */
	private function log_user_import( $user_id, $imported_data, $errors, $warnings ) {
		$log_entry = array(
			'date' => current_time( 'mysql' ),
			'user_id' => $user_id,
			'imported_count' => count( $imported_data ),
			'imported_biomarkers' => array_keys( $imported_data ),
			'errors' => $errors,
			'warnings' => $warnings,
			'import_method' => 'user_frontend',
		);

		$import_history = get_user_meta( $user_id, 'ennu_user_csv_import_history', true );
		if ( ! is_array( $import_history ) ) {
			$import_history = array();
		}

		$import_history[] = $log_entry;
		
		// Keep only last 10 imports
		if ( count( $import_history ) > 10 ) {
			$import_history = array_slice( $import_history, -10 );
		}

		update_user_meta( $user_id, 'ennu_user_csv_import_history', $import_history );
	}
} 