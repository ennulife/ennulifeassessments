<?php
/**
 * ENNU LabCorp Upload Admin
 * Admin interface for uploading LabCorp PDFs for any user
 *
 * @package ENNU_Life
 * @version 71.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_LabCorp_Upload_Admin {

	/**
	 * Initialize the admin interface
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'wp_ajax_ennu_admin_labcorp_upload', array( $this, 'handle_admin_upload' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Add admin menu page
	 */
	public function add_admin_menu() {
		add_submenu_page(
			'ennu-life',
			__( 'Upload LabCorp Results', 'ennulifeassessments' ),
			__( 'LabCorp Upload', 'ennulifeassessments' ),
			'manage_options',
			'ennu-labcorp-upload',
			array( $this, 'render_admin_page' )
		);
	}

	/**
	 * Enqueue admin assets
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( strpos( $hook, 'ennu-labcorp-upload' ) === false ) {
			return;
		}

		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'ennu-admin-labcorp', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin.css', array(), ENNU_LIFE_VERSION );
	}

	/**
	 * Render admin page
	 */
	public function render_admin_page() {
		// Get all users for dropdown
		$users = get_users( array( 'orderby' => 'display_name' ) );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<div class="notice notice-info">
				<p>Upload LabCorp PDF results for any user. The system will automatically extract biomarkers.</p>
			</div>

			<div class="ennu-admin-upload-form" style="background: white; padding: 30px; border-radius: 8px; margin-top: 20px;">
				<form id="admin-labcorp-upload-form" enctype="multipart/form-data">
					
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row">
									<label for="user-select">Select User</label>
								</th>
								<td>
									<select id="user-select" name="user_id" class="regular-text" required>
										<option value="">-- Select a User --</option>
										<?php foreach ( $users as $user ) : ?>
											<option value="<?php echo esc_attr( $user->ID ); ?>">
												<?php echo esc_html( $user->display_name . ' (' . $user->user_email . ')' ); ?>
											</option>
										<?php endforeach; ?>
									</select>
									<p class="description">Choose the user to upload lab results for.</p>
								</td>
							</tr>
							
							<tr>
								<th scope="row">
									<label for="pdf-file">LabCorp PDF</label>
								</th>
								<td>
									<input type="file" id="pdf-file" name="labcorp_pdf" accept="application/pdf" required />
									<p class="description">Select the LabCorp PDF file to upload (max 10MB).</p>
								</td>
							</tr>
							
							<tr>
								<th scope="row">
									<label for="lab-date">Lab Collection Date</label>
								</th>
								<td>
									<input type="date" id="lab-date" name="lab_date" class="regular-text" />
									<p class="description">Optional: Specify when the lab was collected.</p>
								</td>
							</tr>
							
							<tr>
								<th scope="row">
									<label for="admin-notes">Admin Notes</label>
								</th>
								<td>
									<textarea id="admin-notes" name="admin_notes" rows="3" class="large-text"></textarea>
									<p class="description">Optional: Add any notes about this upload.</p>
								</td>
							</tr>
						</tbody>
					</table>

					<p class="submit">
						<button type="submit" class="button button-primary">Upload LabCorp Results</button>
						<span id="upload-spinner" class="spinner" style="float: none; display: none;"></span>
					</p>
				</form>

				<div id="upload-results" style="display: none; margin-top: 20px;">
					<div id="upload-message"></div>
				</div>
			</div>

			<script>
			jQuery(document).ready(function($) {
				$('#admin-labcorp-upload-form').on('submit', function(e) {
					e.preventDefault();
					
					var userId = $('#user-select').val();
					if (!userId) {
						alert('Please select a user');
						return;
					}
					
					var file = $('#pdf-file')[0].files[0];
					if (!file) {
						alert('Please select a PDF file');
						return;
					}
					
					var formData = new FormData();
					formData.append('action', 'ennu_admin_labcorp_upload');
					formData.append('nonce', '<?php echo wp_create_nonce( 'ennu_admin_upload_nonce' ); ?>');
					formData.append('user_id', userId);
					formData.append('labcorp_pdf', file);
					formData.append('lab_date', $('#lab-date').val());
					formData.append('admin_notes', $('#admin-notes').val());
					
					$('#upload-spinner').show();
					$('#upload-results').hide();
					
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: formData,
						processData: false,
						contentType: false,
						success: function(response) {
							$('#upload-spinner').hide();
							$('#upload-results').show();
							
							if (response.success) {
								$('#upload-message').html(
									'<div class="notice notice-success"><p><strong>Success!</strong> ' + 
									response.data.message + '</p></div>'
								);
								
								// Reset form
								$('#admin-labcorp-upload-form')[0].reset();
							} else {
								$('#upload-message').html(
									'<div class="notice notice-error"><p><strong>Error:</strong> ' + 
									response.data.message + '</p></div>'
								);
							}
						},
						error: function() {
							$('#upload-spinner').hide();
							$('#upload-results').show();
							$('#upload-message').html(
								'<div class="notice notice-error"><p>Upload failed. Please try again.</p></div>'
							);
						}
					});
				});
			});
			</script>
		</div>
		<?php
	}

	/**
	 * Handle admin upload via AJAX
	 */
	public function handle_admin_upload() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_admin_upload_nonce' ) ) {
			wp_send_json_error( array( 'message' => 'Security check failed' ) );
		}

		// Check admin permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Insufficient permissions' ) );
		}

		// Get selected user
		$user_id = intval( $_POST['user_id'] );
		if ( ! $user_id || ! get_user_by( 'ID', $user_id ) ) {
			wp_send_json_error( array( 'message' => 'Invalid user selected' ) );
		}

		// Check file upload
		if ( ! isset( $_FILES['labcorp_pdf'] ) || $_FILES['labcorp_pdf']['error'] !== UPLOAD_ERR_OK ) {
			wp_send_json_error( array( 'message' => 'File upload failed' ) );
		}

		// Process the PDF using existing PDF processor
		if ( class_exists( 'ENNU_PDF_Processor' ) ) {
			$pdf_processor = new ENNU_PDF_Processor();
			
			try {
				// Move uploaded file to temp location
				$upload_dir = wp_upload_dir();
				$temp_dir = $upload_dir['basedir'] . '/ennu-temp/';
				
				if ( ! file_exists( $temp_dir ) ) {
					wp_mkdir_p( $temp_dir );
				}
				
				$temp_file = $temp_dir . 'admin_upload_' . uniqid() . '.pdf';
				move_uploaded_file( $_FILES['labcorp_pdf']['tmp_name'], $temp_file );
				
				// Process PDF
				$result = $pdf_processor->process_labcorp_pdf( $temp_file, $user_id );
				
				// Clean up
				@unlink( $temp_file );
				
				if ( $result['success'] ) {
					// Log admin action
					$admin_notes = sanitize_textarea_field( $_POST['admin_notes'] ?? '' );
					$lab_date = sanitize_text_field( $_POST['lab_date'] ?? '' );
					
					// Add admin log entry
					update_user_meta( $user_id, 'ennu_last_admin_upload', array(
						'uploaded_by' => get_current_user_id(),
						'upload_date' => current_time( 'mysql' ),
						'lab_date' => $lab_date,
						'notes' => $admin_notes,
						'biomarker_count' => count( $result['biomarkers'] ?? array() )
					) );
					
					// Get user info
					$user = get_user_by( 'ID', $user_id );
					
					wp_send_json_success( array(
						'message' => sprintf( 
							'Successfully uploaded lab results for %s. Extracted %d biomarkers.', 
							$user->display_name,
							count( $result['biomarkers'] ?? array() )
						),
						'biomarkers' => $result['biomarkers'] ?? array()
					) );
				} else {
					wp_send_json_error( array( 
						'message' => $result['message'] ?? 'Failed to process PDF' 
					) );
				}
				
			} catch ( Exception $e ) {
				wp_send_json_error( array( 
					'message' => 'Error: ' . $e->getMessage() 
				) );
			}
			
		} else {
			wp_send_json_error( array( 'message' => 'PDF processor not available' ) );
		}
	}
}