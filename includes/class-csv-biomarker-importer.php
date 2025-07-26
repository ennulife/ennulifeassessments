<?php
/**
 * ENNU Life CSV Biomarker Importer
 * Streamlined CSV import functionality for current biomarker values only
 *
 * @package ENNU_Life
 * @version 64.4.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_CSV_Biomarker_Importer {

	/**
	 * Initialize the importer
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_csv_import_page' ) );
		add_action( 'wp_ajax_ennu_csv_import_biomarkers', array( $this, 'handle_csv_import' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Add CSV import page to admin menu
	 */
	public function add_csv_import_page() {
		add_submenu_page(
			'ennu-life',
			__( 'CSV Biomarker Import', 'ennulifeassessments' ),
			__( 'CSV Import', 'ennulifeassessments' ),
			'manage_options',
			'ennu-csv-import',
			array( $this, 'render_csv_import_page' )
		);
	}

	/**
	 * Enqueue admin assets
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( strpos( $hook, 'ennu-csv-import' ) === false ) {
			return;
		}

		wp_enqueue_style( 'ennu-csv-import', ENNU_LIFE_PLUGIN_URL . 'assets/css/csv-import.css', array(), ENNU_LIFE_VERSION );
		wp_enqueue_script( 'ennu-csv-import', ENNU_LIFE_PLUGIN_URL . 'assets/js/csv-import.js', array( 'jquery' ), ENNU_LIFE_VERSION, true );

		wp_localize_script(
			'ennu-csv-import',
			'ennuCSVImport',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ennu_csv_import_nonce' ),
			)
		);
	}

	/**
	 * Render the CSV import page
	 */
	public function render_csv_import_page() {
		$users = get_users( array( 'meta_key' => 'ennu_life_score' ) );
		?>
		<div class="wrap ennu-csv-import">
			<h1><?php esc_html_e( 'CSV Biomarker Import', 'ennulifeassessments' ); ?></h1>
			
			<div class="ennu-import-container">
				<div class="ennu-import-instructions">
					<h2><?php esc_html_e( 'Import Current Biomarker Values', 'ennulifeassessments' ); ?></h2>
					<p><?php esc_html_e( 'Upload a CSV file with current biomarker values. The CSV should have the following format:', 'ennulifeassessments' ); ?></p>
					<code>biomarker_name,value,unit,date</code>
					<p><strong><?php esc_html_e( 'Example:', 'ennulifeassessments' ); ?></strong></p>
					<pre>glucose,95,mg/dL,2024-01-15
hba1c,5.2,%,2024-01-15
testosterone,650,ng/dL,2024-01-15</pre>
				</div>

				<form id="ennu-csv-import-form" enctype="multipart/form-data">
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Select User', 'ennulifeassessments' ); ?></th>
							<td>
								<select name="user_id" required>
									<option value=""><?php esc_html_e( 'Select a user...', 'ennulifeassessments' ); ?></option>
									<?php foreach ( $users as $user ) : ?>
										<option value="<?php echo esc_attr( $user->ID ); ?>">
											<?php echo esc_html( $user->display_name . ' (' . $user->user_email . ')' ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'CSV File', 'ennulifeassessments' ); ?></th>
							<td>
								<input type="file" name="biomarker_csv" accept=".csv" required />
								<p class="description"><?php esc_html_e( 'Upload a CSV file with current biomarker values', 'ennulifeassessments' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Import Options', 'ennulifeassessments' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="overwrite_existing" value="1" />
									<?php esc_html_e( 'Overwrite existing biomarker values', 'ennulifeassessments' ); ?>
								</label>
								<br>
								<label>
									<input type="checkbox" name="update_scores" value="1" checked />
									<?php esc_html_e( 'Update life scores after import', 'ennulifeassessments' ); ?>
								</label>
							</td>
						</tr>
					</table>

					<p class="submit">
						<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e( 'Import Biomarkers', 'ennulifeassessments' ); ?>" />
						<span class="spinner" style="float: none; margin-left: 10px;"></span>
					</p>
				</form>

				<div id="ennu-import-results" style="display: none;">
					<h3><?php esc_html_e( 'Import Results', 'ennulifeassessments' ); ?></h3>
					<div id="ennu-import-summary"></div>
					<div id="ennu-import-details"></div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Handle CSV import via AJAX
	 */
	public function handle_csv_import() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_csv_import_nonce' ) ) {
			wp_die( __( 'Security check failed', 'ennulifeassessments' ) );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Insufficient permissions', 'ennulifeassessments' ) );
		}

		$user_id = absint( $_POST['user_id'] );
		if ( ! $user_id ) {
			wp_send_json_error( __( 'Invalid user selected', 'ennulifeassessments' ) );
		}

		$result = $this->process_csv_import( $user_id );
		
		if ( $result['success'] ) {
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Process CSV file import
	 */
	private function process_csv_import( $user_id ) {
		if ( ! isset( $_FILES['biomarker_csv'] ) || $_FILES['biomarker_csv']['error'] !== UPLOAD_ERR_OK ) {
			return array(
				'success' => false,
				'message' => __( 'CSV file upload failed. Please check the file and try again.', 'ennulifeassessments' ),
			);
		}

		$csv_file = $_FILES['biomarker_csv']['tmp_name'];
		$overwrite_existing = isset( $_POST['overwrite_existing'] ) && $_POST['overwrite_existing'] === '1';
		$update_scores = isset( $_POST['update_scores'] ) && $_POST['update_scores'] === '1';

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
					'import_method' => 'csv',
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
		update_user_meta( $user_id, 'ennu_last_csv_import', current_time( 'mysql' ) );

		// Update scores if requested
		if ( $update_scores && class_exists( 'ENNU_Assessment_Scoring' ) ) {
			ENNU_Assessment_Scoring::calculate_and_save_all_user_scores( $user_id, true );
		}

		// Log import
		$this->log_import( $user_id, $imported_data, $errors, $warnings );

		return array(
			'success' => true,
			'message' => sprintf( __( 'Successfully imported %d biomarkers for user %s', 'ennulifeassessments' ), count( $imported_data ), get_userdata( $user_id )->display_name ),
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
	 * Log import activity
	 */
	private function log_import( $user_id, $imported_data, $errors, $warnings ) {
		$log_entry = array(
			'date' => current_time( 'mysql' ),
			'user_id' => $user_id,
			'imported_count' => count( $imported_data ),
			'imported_biomarkers' => array_keys( $imported_data ),
			'errors' => $errors,
			'warnings' => $warnings,
			'imported_by' => get_current_user_id(),
		);

		$import_history = get_user_meta( $user_id, 'ennu_csv_import_history', true );
		if ( ! is_array( $import_history ) ) {
			$import_history = array();
		}

		$import_history[] = $log_entry;
		
		// Keep only last 10 imports
		if ( count( $import_history ) > 10 ) {
			$import_history = array_slice( $import_history, -10 );
		}

		update_user_meta( $user_id, 'ennu_csv_import_history', $import_history );
	}
} 