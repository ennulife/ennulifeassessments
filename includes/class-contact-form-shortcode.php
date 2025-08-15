<?php
/**
 * ENNU Contact Form Shortcode
 * Standalone contact form with assessment styling and registration integration
 *
 * @package ENNU_Life
 * @version 72.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Contact_Form_Shortcode {

	/**
	 * Initialize the shortcode
	 */
	public function __construct() {
		add_shortcode( 'ennu_contact_form', array( $this, 'render_contact_form' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_ennu_submit_contact_form', array( $this, 'handle_form_submission' ) );
		add_action( 'wp_ajax_nopriv_ennu_submit_contact_form', array( $this, 'handle_form_submission' ) );
	}

	/**
	 * Enqueue assets
	 */
	public function enqueue_assets() {
		global $post;
		
		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ennu_contact_form' ) ) {
			// Use existing assessment form styles and scripts
			wp_enqueue_style( 
				'ennu-frontend-forms', 
				ENNU_LIFE_PLUGIN_URL . 'assets/css/ennu-frontend-forms.css', 
				array(), 
				ENNU_LIFE_VERSION 
			);
			
			// Add specific contact form styles
			wp_add_inline_style( 'ennu-frontend-forms', $this->get_inline_styles() );
			
			// Enqueue the main frontend forms script (includes modal functionality)
			wp_enqueue_script( 
				'ennu-contact-form', 
				ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-contact-form.js', 
				array( 'jquery' ), 
				ENNU_LIFE_VERSION, 
				true 
			);
			
			wp_localize_script(
				'ennu-contact-form',
				'ennu_contact',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'ennu_contact_nonce' ),
					'plugin_url' => ENNU_LIFE_PLUGIN_URL,
				)
			);
		}
	}

	/**
	 * Render the contact form
	 * 
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function render_contact_form( $atts ) {
		// Parse attributes
		$atts = shortcode_atts(
			array(
				'redirect'         => '',                    // Redirect URL after submission
				'create_account'   => 'yes',                 // Whether to create account (yes/no)
				'title'           => 'Get Started',          // Form title
				'subtitle'        => 'Enter your information below', // Form subtitle
				'button_text'     => 'Continue',            // Submit button text
				'class'           => '',                    // Additional CSS classes
				'id'              => 'ennu-contact-form',   // Form ID (consistent for HubSpot)
			),
			$atts,
			'ennu_contact_form'
		);

		// Get current page slug for consistent form ID (for HubSpot tracking)
		global $post;
		$page_slug = '';
		if ( $post && $post->post_name ) {
			$page_slug = $post->post_name;
		} else {
			// Fallback to parsing the URL if no post object
			$page_slug = basename( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );
		}
		
		// Use page slug as form ID for HubSpot tracking, or custom ID if provided
		$form_id = ! empty( $atts['id'] ) ? $atts['id'] : 'ennu-contact-' . $page_slug;
		
		// Generate unique class for JavaScript targeting
		$unique_class = 'ennu-contact-' . uniqid();
		
		// Pre-fill fields for logged-in users
		$current_user = wp_get_current_user();
		$prefill_data = array(
			'full_name' => '',
			'email' => '',
			'phone' => ''
		);
		
		if ( $current_user->ID ) {
			// Get full name from user data or meta
			$first_name = get_user_meta( $current_user->ID, 'first_name', true );
			$last_name = get_user_meta( $current_user->ID, 'last_name', true );
			
			if ( empty( $first_name ) ) {
			$first_name = $current_user->first_name;
			}
			if ( empty( $last_name ) ) {
			$last_name = $current_user->last_name;
			}
			
			// Combine first and last name
			$full_name = trim( $first_name . ' ' . $last_name );
			if ( empty( $full_name ) ) {
			$full_name = $current_user->display_name;
			}
			
			$prefill_data['full_name'] = $full_name;
			$prefill_data['email'] = $current_user->user_email;
			
			// Get phone from user meta (check multiple possible keys)
			$phone = get_user_meta( $current_user->ID, 'billing_phone', true );
			if ( empty( $phone ) ) {
			$phone = get_user_meta( $current_user->ID, 'phone', true );
			}
			if ( empty( $phone ) ) {
			$phone = get_user_meta( $current_user->ID, 'ennu_global_phone', true );
			}
			$prefill_data['phone'] = $phone;
		}

		ob_start();
		?>
		<div class="ennu-assessment-form-wrapper ennu-contact-form-wrapper <?php echo esc_attr( $atts['class'] ); ?>">
			<div class="ennu-assessment-form">
				<form id="<?php echo esc_attr( $form_id ); ?>" class="ennu-contact-form <?php echo esc_attr( $unique_class ); ?>" data-create-account="<?php echo esc_attr( $atts['create_account'] ); ?>" data-redirect="<?php echo esc_url( $atts['redirect'] ); ?>" data-form-type="ennu-contact" data-page-slug="<?php echo esc_attr( $page_slug ); ?>">
					
					<!-- Form Header -->
					<div class="form-header">
						<h2 class="form-title"><?php echo esc_html( $atts['title'] ); ?></h2>
						<?php if ( ! empty( $atts['subtitle'] ) ) : ?>
							<p class="form-subtitle"><?php echo esc_html( $atts['subtitle'] ); ?></p>
						<?php endif; ?>
					</div>

					<!-- Progress Indicator (matching assessment style) -->
					<div class="progress-wrapper">
						<div class="progress-bar">
							<div class="progress-fill" style="width: 0%;"></div>
						</div>
					</div>

					<!-- Form Fields Container -->
					<div class="question-slide active">
						<div class="question-content">
							
							<!-- Full Name Field -->
							<div class="form-group">
								<input 
									type="text" 
									id="<?php echo esc_attr( $form_id ); ?>-name" 
									name="full_name" 
									class="form-control" 
									required 
									placeholder="Full Name *"
									value="<?php echo esc_attr( $prefill_data['full_name'] ); ?>"
								/>
								<span class="error-message" style="display: none;">Please enter your full name</span>
							</div>

							<!-- Email Field -->
							<div class="form-group">
								<input 
									type="email" 
									id="<?php echo esc_attr( $form_id ); ?>-email" 
									name="email" 
									class="form-control" 
									required 
									placeholder="Email Address *"
									value="<?php echo esc_attr( $prefill_data['email'] ); ?>"
								/>
								<span class="error-message" style="display: none;">Please enter a valid email address</span>
							</div>

							<!-- Phone Field -->
							<div class="form-group">
								<input 
									type="tel" 
									id="<?php echo esc_attr( $form_id ); ?>-phone" 
									name="phone" 
									class="form-control" 
									required 
									placeholder="Phone Number (XXX) XXX-XXXX *"
									value="<?php echo esc_attr( $prefill_data['phone'] ); ?>"
								/>
								<span class="error-message" style="display: none;">Please enter a valid phone number</span>
							</div>

						</div>

						<!-- Compliance Checkbox for logged-out users -->
						<?php if ( ! is_user_logged_in() ) : ?>
						<div class="form-group compliance-group">
							<label class="compliance-label">
								<input 
									type="checkbox" 
									id="<?php echo esc_attr( $form_id ); ?>-compliance" 
									name="compliance_agreement" 
									class="compliance-checkbox" 
									required
								/>
								<span class="compliance-text">
									I agree to receive recurring automated text messages at the phone number provided. 
									Msg & data rates may apply. Msg frequency varies. Reply HELP for help and STOP to cancel. 
									View our <a href="/terms-of-service" target="_blank">Terms of Service</a> and 
									<a href="/privacy-policy" target="_blank">Privacy Policy</a>.
								</span>
							</label>
							<span class="error-message" style="display: none;">You must agree to the terms to continue</span>
						</div>
						<?php endif; ?>

						<!-- Submit Button -->
						<div class="navigation-buttons">
							<button type="submit" class="btn-next btn-submit">
								<?php echo esc_html( $atts['button_text'] ); ?>
								<svg class="btn-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
									<path d="M7 5L12 10L7 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</button>
						</div>
					</div>

					<!-- Hidden Fields -->
					<input type="hidden" name="action" value="ennu_submit_contact_form" />
					<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'ennu_contact_nonce' ); ?>" />
					<input type="hidden" name="create_account" value="<?php echo esc_attr( $atts['create_account'] ); ?>" />
					<input type="hidden" name="redirect_url" value="<?php echo esc_url( $atts['redirect'] ); ?>" />
				</form>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle form submission via AJAX
	 */
	public function handle_form_submission() {
		// Check if this is an AJAX request
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			wp_die( 'Direct access not allowed' );
		}
		
		// Verify nonce
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'ennu_contact_nonce' ) ) {
			wp_send_json_error( array( 
				'message' => 'Security check failed. Please refresh the page and try again.',
				'type' => 'security_error'
			) );
		}

		// Sanitize input with null checks
		$full_name = isset( $_POST['full_name'] ) ? sanitize_text_field( $_POST['full_name'] ) : '';
		$email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$phone = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
		$create_account = isset( $_POST['create_account'] ) && $_POST['create_account'] === 'yes';
		$redirect_url = isset( $_POST['redirect_url'] ) ? esc_url_raw( $_POST['redirect_url'] ) : '';
		$compliance_agreement = isset( $_POST['compliance_agreement'] ) ? true : false;

		// Basic validation but don't fail - just use defaults if needed
		if ( empty( $full_name ) ) {
			$full_name = 'Guest User';
		}
		
		if ( empty( $email ) || ! is_email( $email ) ) {
			// Generate a placeholder email if invalid or empty
			$email = 'guest_' . time() . '@placeholder.com';
		}
		
		if ( empty( $phone ) ) {
			$phone = '(000) 000-0000';
		}
		
		// Get current user once
		$current_user = wp_get_current_user();
		$is_logged_in = ( $current_user && $current_user->ID > 0 );

		// Split full name into first and last
		$name_parts = explode( ' ', $full_name, 2 );
		$first_name = $name_parts[0];
		$last_name = isset( $name_parts[1] ) ? $name_parts[1] : '';

		// SCENARIO 1: User is logged in (regardless of create_account setting)
		if ( $current_user->ID ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'ENNU Contact Form: Processing logged-in user submission' );
			}
			
			// Update user meta with the submitted data
			$user_id = $current_user->ID;
			update_user_meta( $user_id, 'first_name', $first_name );
			update_user_meta( $user_id, 'last_name', $last_name );
			update_user_meta( $user_id, 'billing_phone', $phone );
				
			// Send Teams notification if available
			if ( class_exists( 'ENNU_N8N_Integration' ) ) {
				$teams = ENNU_N8N_Integration::get_instance();
				$teams->send_notification( 'contact_form_update', array(
						'user_id' => $user_id,
						'name' => $full_name,
						'email' => $email,
						'phone' => $phone,
						'source' => wp_get_referer()
					) );
				}
				
			// Send success response for logged-in user
			wp_send_json_success( array(
					'message' => 'Information updated successfully!',
					'type' => 'info_updated',
					'user_id' => $user_id,
					'redirect' => $redirect_url,
					'is_logged_in' => true
				) );
			}
			
			// SCENARIO 2: Not logged in - check if email exists
			$existing_user = get_user_by( 'email', $email );
			
		// SCENARIO 2A: Email exists and create_account=yes
		if ( $existing_user && $create_account ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'ENNU Contact Form: Email exists, treating as update' );
			}
			
			// Just redirect - don't show error
			wp_send_json_success( array(
				'message' => 'Information submitted successfully!',
				'type' => 'existing_user_redirect',
				'redirect' => $redirect_url
			) );
			}
			
			// SCENARIO 2B: Email exists and create_account=no
			if ( $existing_user && ! $create_account ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( 'ENNU Contact Form: Email exists but not creating account - just collecting data' );
				}
				
			// Just save the data without creating account
			$contact_data = array(
					'first_name' => $first_name,
					'last_name' => $last_name,
					'email' => $email,
					'phone' => $phone,
					'timestamp' => current_time( 'mysql' ),
					'existing_user_id' => $existing_user->ID,
					'sms_consent' => $compliance_agreement,
					'sms_consent_ip' => $compliance_agreement ? $_SERVER['REMOTE_ADDR'] : null
				);
				
			// Store in transient
			$transient_key = 'ennu_contact_' . md5( $email );
			set_transient( $transient_key, $contact_data, HOUR_IN_SECONDS );
				
			// Send Teams notification
			if ( class_exists( 'ENNU_N8N_Integration' ) ) {
				$teams = ENNU_N8N_Integration::get_instance();
				$teams->send_notification( 'contact_form_existing', array(
						'name' => $full_name,
						'email' => $email,
						'phone' => $phone,
						'existing_user' => true,
						'source' => wp_get_referer()
					) );
				}
				
			// Success - no account created but data saved
			wp_send_json_success( array(
					'message' => 'Information submitted successfully!',
					'type' => 'no_account_existing_email',
					'redirect' => $redirect_url
				) );
			}

		// SCENARIO 3: Email doesn't exist and create_account=yes
		// Skip account creation if we're using a placeholder email
		if ( $create_account && ! $existing_user && strpos( $email, '@placeholder.com' ) === false ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'ENNU Contact Form: Creating new account' );
			}
			// Create new user account only if email doesn't exist
			$user_data = array(
					'user_login'    => $email,
					'user_email'    => $email,
					'user_pass'     => wp_generate_password(),
					'first_name'    => $first_name,
					'last_name'     => $last_name,
					'display_name'  => $full_name,
					'role'          => 'subscriber'
				);

			$user_id = wp_insert_user( $user_data );

			if ( is_wp_error( $user_id ) ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						error_log( 'ENNU Contact Form: User creation failed - ' . $user_id->get_error_message() );
					}
				wp_send_json_error( array( 
						'message' => 'Failed to create account: ' . $user_id->get_error_message(),
						'type' => 'creation_error'
					) );
					return;
				}

			// Save phone number as user meta
			update_user_meta( $user_id, 'billing_phone', $phone );
			update_user_meta( $user_id, 'ennu_registration_source', 'contact_form' );
			update_user_meta( $user_id, 'ennu_registration_date', current_time( 'mysql' ) );
				
			// Save compliance agreement if provided (for logged-out users who created account)
			if ( $compliance_agreement ) {
				update_user_meta( $user_id, 'ennu_sms_consent', true );
				update_user_meta( $user_id, 'ennu_sms_consent_date', current_time( 'mysql' ) );
				update_user_meta( $user_id, 'ennu_sms_consent_ip', $_SERVER['REMOTE_ADDR'] );
				}

			// Log the user in
			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id, true );

			// Send Teams notification if available
			if ( class_exists( 'ENNU_N8N_Integration' ) ) {
				$teams = ENNU_N8N_Integration::get_instance();
				$teams->send_registration_notification( $user_id, array(
						'source' => 'Contact Form',
						'form_page' => wp_get_referer()
					) );
				}

			// Send success response with account creation
			wp_send_json_success( array(
					'message' => 'Account created successfully!',
					'type' => 'account_created',
					'user_id' => $user_id,
					'redirect' => $redirect_url,
					'show_progress_modal' => true,
					'modal_type' => 'account_creation'
				) );

		}
		
		// SCENARIO 3B: Skip account creation for placeholder emails
		if ( $create_account && ! $existing_user && strpos( $email, '@placeholder.com' ) !== false ) {
			// Just treat it as a no-account submission
			wp_send_json_success( array(
				'message' => 'Information submitted successfully!',
				'type' => 'placeholder_email',
				'redirect' => $redirect_url
			) );
		}
		
		// SCENARIO 4: Email doesn't exist and create_account=no
		if ( ! $create_account && ! $existing_user ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( 'ENNU Contact Form: Not creating account - storing in transient for HubSpot' );
				}
				
			// Store data temporarily (for HubSpot/CRM sync) without creating WordPress account
			$contact_data = array(
					'first_name' => $first_name,
					'last_name' => $last_name,
					'email' => $email,
					'phone' => $phone,
					'timestamp' => current_time( 'mysql' ),
					'sms_consent' => $compliance_agreement,
					'sms_consent_ip' => $compliance_agreement ? $_SERVER['REMOTE_ADDR'] : null
				);

			// Store in transient for 1 hour (temporary storage for CRM/HubSpot processing)
			$transient_key = 'ennu_contact_' . md5( $email );
			set_transient( $transient_key, $contact_data, HOUR_IN_SECONDS );

			// Send to Teams if configured
			if ( class_exists( 'ENNU_N8N_Integration' ) ) {
				$teams = ENNU_N8N_Integration::get_instance();
				$teams->send_notification( 'contact_form', array(
						'name' => $full_name,
						'email' => $email,
						'phone' => $phone,
						'source' => wp_get_referer()
					) );
				}

			// Send success response (data collected for HubSpot, no WordPress account created)
			wp_send_json_success( array(
					'message' => 'Information submitted successfully!',
					'type' => 'no_account',
					'redirect' => $redirect_url
				) );
			}
			
			// This should never be reached, but just in case
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'ENNU Contact Form: Unexpected scenario reached' );
		}
		wp_send_json_error( array(
			'message' => 'An unexpected error occurred. Please try again.',
			'type' => 'unexpected_error'
		) );
	}

	/**
	 * Get inline styles for contact form
	 */
	private function get_inline_styles() {
		return '
			.ennu-contact-form-wrapper {
				max-width: 600px;
				margin: 0 auto;
				padding: 20px;
			}
			
			.ennu-contact-form .form-header {
				text-align: center;
				margin-bottom: 30px;
			}
			
			.ennu-contact-form .form-title {
				font-size: 28px;
				font-weight: 600;
				color: #1a1a1a;
				margin-bottom: 10px;
			}
			
			.ennu-contact-form .form-subtitle {
				font-size: 16px;
				color: #6c757d;
				margin: 0;
			}
			
			.ennu-contact-form .form-group {
				margin-bottom: 25px;
			}
			
			.ennu-contact-form .form-control {
				width: 100%;
				padding: 14px 18px;
				border: 2px solid #e1e5eb;
				border-radius: 10px;
				font-size: 16px;
				transition: all 0.3s ease;
				background: #fff;
			}
			
			.ennu-contact-form .form-control::placeholder {
				color: #999;
				font-weight: 400;
			}
			
			.ennu-contact-form .form-control:focus {
				outline: none;
				border-color: #00BFA5;
				box-shadow: 0 0 0 3px rgba(0, 191, 165, 0.1);
			}
			
			.ennu-contact-form .form-control.error {
				border-color: #dc3545;
			}
			
			.ennu-contact-form .error-message {
				color: #dc3545;
				font-size: 13px;
				margin-top: 5px;
				display: block;
			}
			
			/* Compliance Checkbox Styling */
			.ennu-contact-form .compliance-group {
				margin: 25px 0;
			}
			
			.ennu-contact-form .compliance-label {
				display: flex;
				align-items: flex-start;
				cursor: pointer;
				font-size: 13px;
				line-height: 1.5;
			}
			
			.ennu-contact-form .compliance-checkbox {
				flex-shrink: 0;
				width: 18px;
				height: 18px;
				margin-right: 10px;
				margin-top: 2px;
				cursor: pointer;
				accent-color: #00BFA5;
			}
			
			.ennu-contact-form .compliance-text {
				color: #666;
				font-size: 13px;
				line-height: 1.5;
			}
			
			.ennu-contact-form .compliance-text a {
				color: #00BFA5;
				text-decoration: underline;
			}
			
			.ennu-contact-form .compliance-text a:hover {
				color: #00D9BC;
			}
			
			.ennu-contact-form .compliance-group.error .compliance-checkbox {
				border-color: #dc3545;
			}
			
			.ennu-contact-form .navigation-buttons {
				margin-top: 30px;
				text-align: center;
			}
			
			.ennu-contact-form .btn-submit {
				background: linear-gradient(135deg, #00BFA5 0%, #00D9BC 100%);
				color: white;
				padding: 16px 48px;
				border: none;
				border-radius: 50px;
				font-size: 17px;
				font-weight: 600;
				cursor: pointer;
				display: inline-flex;
				align-items: center;
				gap: 10px;
				transition: all 0.3s ease;
				box-shadow: 0 4px 20px rgba(0, 191, 165, 0.25);
				text-transform: uppercase;
				letter-spacing: 0.5px;
			}
			
			.ennu-contact-form .btn-submit:hover {
				transform: translateY(-2px);
				box-shadow: 0 8px 25px rgba(0, 191, 165, 0.35);
				background: linear-gradient(135deg, #00D9BC 0%, #00BFA5 100%);
			}
			
			.ennu-contact-form .btn-icon {
				transition: transform 0.3s ease;
			}
			
			.ennu-contact-form .btn-submit:hover .btn-icon {
				transform: translateX(3px);
			}
			
			/* Progress bar (subtle, matches assessment style) */
			.ennu-contact-form .progress-wrapper {
				margin-bottom: 30px;
			}
			
			.ennu-contact-form .progress-bar {
				height: 6px;
				background: #e9ecef;
				border-radius: 3px;
				overflow: hidden;
			}
			
			.ennu-contact-form .progress-fill {
				height: 100%;
				background: linear-gradient(90deg, #00BFA5 0%, #00D9BC 100%);
				border-radius: 3px;
				transition: width 0.5s ease;
			}
			
			/* Mobile responsiveness */
			@media (max-width: 640px) {
				.ennu-contact-form-wrapper {
					padding: 10px;
				}
				
				.ennu-contact-form .form-title {
					font-size: 24px;
				}
				
				.ennu-contact-form .btn-submit {
					width: 100%;
					justify-content: center;
			}
		}
		';
	}
}