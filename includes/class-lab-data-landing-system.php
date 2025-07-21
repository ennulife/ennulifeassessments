<?php
/**
 * ENNU Lab Data Landing System
 *
 * Provides dedicated landing page and template system for lab data imports.
 *
 * @package ENNU_Life
 * @version 62.2.8
 * @author ENNU Life Development Team
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Lab_Data_Landing_System {

	/**
	 * Initialize the lab data landing system
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_landing_page' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle_landing_page' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_landing_scripts' ) );
	}

	/**
	 * Register the lab data landing page
	 */
	public static function register_landing_page() {
		add_rewrite_rule(
			'^lab-data-import/?$',
			'index.php?ennu_lab_landing=1',
			'top'
		);

		add_filter(
			'query_vars',
			function( $vars ) {
				$vars[] = 'ennu_lab_landing';
				return $vars;
			}
		);
	}

	/**
	 * Handle the lab data landing page request
	 */
	public static function handle_landing_page() {
		if ( get_query_var( 'ennu_lab_landing' ) ) {
			if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'medical_director' ) ) {
				wp_die( 'Unauthorized access' );
			}

			self::render_landing_page();
			exit;
		}
	}

	/**
	 * Render the lab data landing page
	 */
	private static function render_landing_page() {
		$upload_stats   = self::get_upload_statistics();
		$recent_uploads = self::get_recent_uploads();
		$csv_templates  = self::get_csv_templates();

		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>
		<head>
			<meta charset="<?php bloginfo( 'charset' ); ?>">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title>Lab Data Import - ENNU Life</title>
			<?php wp_head(); ?>
		</head>
		<body class="ennu-lab-landing">
			<div class="lab-landing-container">
				<header class="lab-landing-header">
					<h1>Lab Data Import Center</h1>
					<p>Secure upload and management of patient laboratory results</p>
				</header>

				<div class="lab-landing-content">
					<div class="upload-section">
						<h2>Upload Lab Results</h2>
						<div class="upload-methods">
							<div class="upload-method csv-upload">
								<h3>CSV File Upload</h3>
								<form id="csv-upload-form" enctype="multipart/form-data">
									<?php wp_nonce_field( 'ennu_lab_upload', 'lab_upload_nonce' ); ?>
									<div class="file-input-wrapper">
										<input type="file" id="csv-file" name="csv_file" accept=".csv" required>
										<label for="csv-file">Choose CSV File</label>
									</div>
									<div class="upload-options">
										<label>
											<input type="checkbox" name="validate_headers" checked>
											Validate column headers
										</label>
										<label>
											<input type="checkbox" name="auto_flag" checked>
											Auto-flag abnormal values
										</label>
									</div>
									<button type="submit" class="upload-btn">Upload Lab Data</button>
								</form>
							</div>

							<div class="upload-method manual-entry">
								<h3>Manual Entry</h3>
								<button id="manual-entry-btn" class="manual-btn">Enter Lab Results Manually</button>
							</div>
						</div>
					</div>

					<div class="templates-section">
						<h2>CSV Templates</h2>
						<div class="template-grid">
							<?php foreach ( $csv_templates as $template ) : ?>
								<div class="template-card">
									<h4><?php echo esc_html( $template['name'] ); ?></h4>
									<p><?php echo esc_html( $template['description'] ); ?></p>
									<div class="template-actions">
										<a href="<?php echo esc_url( $template['download_url'] ); ?>" class="download-btn">Download</a>
										<button class="preview-btn" data-template="<?php echo esc_attr( $template['id'] ); ?>">Preview</button>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="statistics-section">
						<h2>Upload Statistics</h2>
						<div class="stats-grid">
							<div class="stat-card">
								<h3><?php echo esc_html( $upload_stats['total_uploads'] ); ?></h3>
								<p>Total Uploads</p>
							</div>
							<div class="stat-card">
								<h3><?php echo esc_html( $upload_stats['this_month'] ); ?></h3>
								<p>This Month</p>
							</div>
							<div class="stat-card">
								<h3><?php echo esc_html( $upload_stats['pending_review'] ); ?></h3>
								<p>Pending Review</p>
							</div>
							<div class="stat-card">
								<h3><?php echo esc_html( $upload_stats['flagged_results'] ); ?></h3>
								<p>Flagged Results</p>
							</div>
						</div>
					</div>

					<div class="recent-uploads-section">
						<h2>Recent Uploads</h2>
						<div class="uploads-table">
							<table>
								<thead>
									<tr>
										<th>Date</th>
										<th>Patient</th>
										<th>File</th>
										<th>Status</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $recent_uploads as $upload ) : ?>
										<tr>
											<td><?php echo esc_html( $upload['date'] ); ?></td>
											<td><?php echo esc_html( $upload['patient_name'] ); ?></td>
											<td><?php echo esc_html( $upload['filename'] ); ?></td>
											<td><span class="status <?php echo esc_attr( $upload['status'] ); ?>"><?php echo esc_html( ucfirst( $upload['status'] ) ); ?></span></td>
											<td>
												<a href="<?php echo esc_url( $upload['view_url'] ); ?>">View</a>
												<?php if ( 'pending' === $upload['status'] ) : ?>
													<button class="approve-btn" data-upload="<?php echo esc_attr( $upload['id'] ); ?>">Approve</button>
												<?php endif; ?>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			<div id="manual-entry-modal" class="modal" style="display: none;">
				<div class="modal-content">
					<span class="close">&times;</span>
					<h2>Manual Lab Entry</h2>
					<form id="manual-entry-form">
						<?php wp_nonce_field( 'ennu_manual_lab_entry', 'manual_entry_nonce' ); ?>
						<div class="form-group">
							<label for="patient-select">Patient:</label>
							<select id="patient-select" name="patient_id" required>
								<option value="">Select Patient</option>
								<?php
								$patients = get_users( array( 'role' => 'subscriber' ) );
								foreach ( $patients as $patient ) {
									echo '<option value="' . esc_attr( $patient->ID ) . '">' . esc_html( $patient->display_name ) . '</option>';
								}
								?>
							</select>
						</div>
						<div class="biomarkers-grid" id="biomarkers-grid">
							<!-- Biomarker fields will be populated by JavaScript -->
						</div>
						<div class="form-actions">
							<button type="submit" class="save-btn">Save Lab Results</button>
							<button type="button" class="cancel-btn">Cancel</button>
						</div>
					</form>
				</div>
			</div>

			<?php wp_footer(); ?>
		</body>
		</html>
		<?php
	}

	/**
	 * Get upload statistics
	 *
	 * @return array Upload statistics
	 */
	private static function get_upload_statistics() {
		global $wpdb;

		$stats = array(
			'total_uploads'   => 0,
			'this_month'      => 0,
			'pending_review'  => 0,
			'flagged_results' => 0,
		);

		$upload_history         = get_option( 'ennu_lab_upload_history', array() );
		$stats['total_uploads'] = count( $upload_history );

		$this_month          = gmdate( 'Y-m' );
		$stats['this_month'] = count(
			array_filter(
				$upload_history,
				function( $upload ) use ( $this_month ) {
					return strpos( $upload['date'], $this_month ) === 0;
				}
			)
		);

		$stats['pending_review'] = count(
			array_filter(
				$upload_history,
				function( $upload ) {
					return 'pending' === $upload['status'];
				}
			)
		);

		$flag_stats               = ENNU_Biomarker_Flag_Manager::get_flag_statistics();
		$stats['flagged_results'] = $flag_stats['total_active_flags'];

		return $stats;
	}

	/**
	 * Get recent uploads
	 *
	 * @return array Recent uploads
	 */
	private static function get_recent_uploads() {
		$upload_history = get_option( 'ennu_lab_upload_history', array() );

		usort(
			$upload_history,
			function( $a, $b ) {
				return strtotime( $b['date'] ) - strtotime( $a['date'] );
			}
		);

		return array_slice( $upload_history, 0, 10 );
	}

	/**
	 * Get CSV templates
	 *
	 * @return array CSV templates
	 */
	private static function get_csv_templates() {
		return array(
			array(
				'id'           => 'comprehensive',
				'name'         => 'Comprehensive Panel',
				'description'  => 'Complete biomarker panel with all standard tests',
				'download_url' => admin_url( 'admin-ajax.php?action=ennu_download_csv_template&template=comprehensive' ),
			),
			array(
				'id'           => 'hormone',
				'name'         => 'Hormone Panel',
				'description'  => 'Hormone-specific biomarkers including testosterone, estrogen, etc.',
				'download_url' => admin_url( 'admin-ajax.php?action=ennu_download_csv_template&template=hormone' ),
			),
			array(
				'id'           => 'metabolic',
				'name'         => 'Metabolic Panel',
				'description'  => 'Glucose, cholesterol, and metabolic markers',
				'download_url' => admin_url( 'admin-ajax.php?action=ennu_download_csv_template&template=metabolic' ),
			),
			array(
				'id'           => 'thyroid',
				'name'         => 'Thyroid Panel',
				'description'  => 'TSH, T3, T4, and thyroid-related markers',
				'download_url' => admin_url( 'admin-ajax.php?action=ennu_download_csv_template&template=thyroid' ),
			),
		);
	}

	/**
	 * Enqueue landing page scripts and styles
	 */
	public static function enqueue_landing_scripts() {
		if ( get_query_var( 'ennu_lab_landing' ) ) {
			wp_enqueue_style( 'ennu-lab-landing', ENNU_LIFE_PLUGIN_URL . 'assets/css/lab-landing.css', array(), ENNU_LIFE_VERSION );
			wp_enqueue_script( 'ennu-lab-landing', ENNU_LIFE_PLUGIN_URL . 'assets/js/lab-landing.js', array( 'jquery' ), ENNU_LIFE_VERSION, true );

			wp_localize_script(
				'ennu-lab-landing',
				'ennu_lab_landing',
				array(
					'ajax_url'   => admin_url( 'admin-ajax.php' ),
					'nonce'      => wp_create_nonce( 'ennu_lab_landing_nonce' ),
					'biomarkers' => self::get_biomarker_definitions(),
				)
			);
		}
	}

	/**
	 * Get biomarker definitions for manual entry
	 *
	 * @return array Biomarker definitions
	 */
	private static function get_biomarker_definitions() {
		return array(
			'Glucose'           => array(
				'unit'         => 'mg/dL',
				'normal_range' => '70-100',
			),
			'HbA1c'             => array(
				'unit'         => '%',
				'normal_range' => '4.0-5.6',
			),
			'Total Cholesterol' => array(
				'unit'         => 'mg/dL',
				'normal_range' => '125-200',
			),
			'LDL'               => array(
				'unit'         => 'mg/dL',
				'normal_range' => '<100',
			),
			'HDL'               => array(
				'unit'         => 'mg/dL',
				'normal_range' => '>40',
			),
			'Triglycerides'     => array(
				'unit'         => 'mg/dL',
				'normal_range' => '<150',
			),
			'TSH'               => array(
				'unit'         => 'mIU/L',
				'normal_range' => '0.4-4.0',
			),
			'Free T4'           => array(
				'unit'         => 'ng/dL',
				'normal_range' => '0.8-1.8',
			),
			'Free T3'           => array(
				'unit'         => 'pg/mL',
				'normal_range' => '2.3-4.2',
			),
			'Testosterone'      => array(
				'unit'         => 'ng/dL',
				'normal_range' => '300-1000',
			),
			'Estradiol'         => array(
				'unit'         => 'pg/mL',
				'normal_range' => '15-350',
			),
			'Vitamin D'         => array(
				'unit'         => 'ng/mL',
				'normal_range' => '30-100',
			),
			'B12'               => array(
				'unit'         => 'pg/mL',
				'normal_range' => '200-900',
			),
			'Folate'            => array(
				'unit'         => 'ng/mL',
				'normal_range' => '2.7-17.0',
			),
			'Iron'              => array(
				'unit'         => 'μg/dL',
				'normal_range' => '60-170',
			),
			'Ferritin'          => array(
				'unit'         => 'ng/mL',
				'normal_range' => '15-150',
			),
		);
	}

	/**
	 * Process CSV upload
	 *
	 * @param array $file_data File upload data
	 * @param int $user_id User ID
	 * @return array|WP_Error Processing result
	 */
	public static function process_csv_upload( $file_data, $user_id ) {
		$validation = ENNU_Security_Enhancements::validate_file_upload(
			$file_data,
			array( 'text/csv', 'application/csv' ),
			5242880 // 5MB
		);

		if ( is_wp_error( $validation ) ) {
			return $validation;
		}

		$csv_content = file_get_contents( $file_data['tmp_name'] );
		$lines       = str_getcsv( $csv_content, "\n" );

		if ( empty( $lines ) ) {
			return new WP_Error( 'empty_file', 'CSV file is empty' );
		}

		$headers        = str_getcsv( array_shift( $lines ) );
		$processed_data = array();

		foreach ( $lines as $line ) {
			$row = str_getcsv( $line );
			if ( count( $row ) === count( $headers ) ) {
				$processed_data[] = array_combine( $headers, $row );
			}
		}

		$upload_record = array(
			'id'           => uniqid(),
			'user_id'      => $user_id,
			'filename'     => $file_data['name'],
			'date'         => current_time( 'mysql' ),
			'status'       => 'pending',
			'data'         => $processed_data,
			'patient_name' => get_userdata( $user_id )->display_name,
			'view_url'     => admin_url( 'admin.php?page=ennu-lab-data&upload=' . uniqid() ),
		);

		$upload_history   = get_option( 'ennu_lab_upload_history', array() );
		$upload_history[] = $upload_record;
		update_option( 'ennu_lab_upload_history', $upload_history );

		return array(
			'success'           => true,
			'upload_id'         => $upload_record['id'],
			'records_processed' => count( $processed_data ),
		);
	}
}

ENNU_Lab_Data_Landing_System::init();
