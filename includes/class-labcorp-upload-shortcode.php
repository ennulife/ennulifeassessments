<?php
/**
 * ENNU LabCorp Upload Shortcode
 * Provides shortcode for LabCorp PDF upload using existing working functionality
 *
 * @package ENNU_Life
 * @version 71.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_LabCorp_Upload_Shortcode {

	/**
	 * Initialize the shortcode
	 */
	public function __construct() {
		add_shortcode( 'ennu_labcorp_upload', array( $this, 'render_upload_form' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Enqueue frontend assets
	 */
	public function enqueue_frontend_assets() {
		global $post;
		
		// Check if shortcode is present or on dashboard
		if ( ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ennu_labcorp_upload' ) ) || 
		     ( function_exists( 'is_page' ) && is_page( 'dashboard' ) ) ) {
			
			// Reuse existing dashboard CSS and JS
			wp_enqueue_style( 
				'ennu-dashboard', 
				ENNU_LIFE_PLUGIN_URL . 'assets/css/dashboard.css', 
				array(), 
				ENNU_LIFE_VERSION 
			);
			
			wp_enqueue_script( 
				'ennu-dashboard-functions', 
				ENNU_LIFE_PLUGIN_URL . 'assets/js/dashboard-functions.js', 
				array( 'jquery' ), 
				ENNU_LIFE_VERSION, 
				true 
			);

			// Localize script with same data structure
			wp_localize_script(
				'ennu-dashboard-functions',
				'ennuDashboard',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'ennu_ajax_nonce' ),
				)
			);
		}
	}

	/**
	 * Render the upload form shortcode
	 * Uses same HTML structure as dashboard for consistency
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function render_upload_form( $atts ) {
		// Check if user is logged in
		if ( ! is_user_logged_in() ) {
			return '<div class="ennu-notice">Please <a href="' . wp_login_url( get_permalink() ) . '">log in</a> to upload your lab results.</div>';
		}

		// Parse attributes
		$atts = shortcode_atts(
			array(
				'title'       => 'Upload Lab Results',
				'show_title'  => 'yes',
				'class'       => '',
			),
			$atts,
			'ennu_labcorp_upload'
		);

		ob_start();
		?>
		<div class="ennu-labcorp-upload-container <?php echo esc_attr( $atts['class'] ); ?>">
			<?php if ( $atts['show_title'] === 'yes' ) : ?>
				<h2><?php echo esc_html( $atts['title'] ); ?></h2>
			<?php endif; ?>
			
			<div class="upload-container">
				<div class="upload-instructions">
					<p>Upload your LabCorp PDF to automatically extract and track your biomarkers.</p>
					<ul>
						<li>✅ Automatic biomarker extraction</li>
						<li>✅ Secure and HIPAA compliant</li>
						<li>✅ Instant analysis and insights</li>
						<li>✅ Track progress over time</li>
					</ul>
				</div>
				
				<div class="upload-form">
					<input type="file" id="pdf-file-input" accept="application/pdf" />
					<button class="btn-primary" onclick="uploadPDF()">Upload PDF</button>
					<progress id="upload-progress" value="0" max="100" style="display:none;"></progress>
					<div id="upload-status"></div>
				</div>
			</div>
		</div>
		
		<style>
			.ennu-labcorp-upload-container {
				padding: 20px;
				background: #f8f9fa;
				border-radius: 8px;
				margin: 20px 0;
			}
			.upload-container {
				background: white;
				padding: 30px;
				border-radius: 8px;
				box-shadow: 0 2px 4px rgba(0,0,0,0.1);
			}
			.upload-instructions {
				margin-bottom: 30px;
			}
			.upload-instructions ul {
				list-style: none;
				padding: 0;
			}
			.upload-instructions li {
				padding: 8px 0;
			}
			.upload-form {
				text-align: center;
			}
			#pdf-file-input {
				margin: 10px;
			}
			.btn-primary {
				background: #28a745;
				color: white;
				padding: 12px 30px;
				border: none;
				border-radius: 5px;
				cursor: pointer;
				font-size: 16px;
				margin: 10px;
			}
			.btn-primary:hover {
				background: #218838;
			}
			#upload-progress {
				width: 100%;
				margin: 20px 0;
			}
			#upload-status {
				margin: 15px 0;
				padding: 10px;
				border-radius: 5px;
				text-align: center;
			}
			#upload-status:not(:empty) {
				background: #d4edda;
				color: #155724;
				border: 1px solid #c3e6cb;
			}
		</style>
		<?php
		return ob_get_clean();
	}
}