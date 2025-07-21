<?php
/**
 * Two-Factor Authentication Support
 *
 * Provides optional two-factor authentication for enhanced security.
 * Integrates with popular 2FA plugins and provides basic TOTP support.
 *
 * @package ENNU_Life_Assessments
 * @since 62.2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Two_Factor_Auth {

	private $enabled_methods = array();
	private $user_settings   = array();

	public function __construct() {
		$this->init_2fa_methods();
		$this->setup_hooks();
	}

	/**
	 * Initialize 2FA methods
	 */
	private function init_2fa_methods() {
		$this->enabled_methods = array(
			'email'        => array(
				'name'        => 'Email Verification',
				'description' => 'Send verification codes via email',
				'enabled'     => true,
			),
			'totp'         => array(
				'name'        => 'Authenticator App (TOTP)',
				'description' => 'Use Google Authenticator or similar apps',
				'enabled'     => true,
			),
			'backup_codes' => array(
				'name'        => 'Backup Codes',
				'description' => 'One-time use backup codes',
				'enabled'     => true,
			),
		);
	}

	/**
	 * Setup WordPress hooks
	 */
	private function setup_hooks() {
		add_action( 'wp_login', array( $this, 'check_2fa_requirement' ), 10, 2 );
		add_action( 'wp_ajax_ennu_verify_2fa', array( $this, 'verify_2fa_code' ) );
		add_action( 'wp_ajax_ennu_setup_2fa', array( $this, 'setup_2fa_method' ) );
		add_action( 'wp_ajax_ennu_disable_2fa', array( $this, 'disable_2fa_method' ) );
		add_action( 'wp_ajax_ennu_generate_backup_codes', array( $this, 'generate_backup_codes' ) );

		add_action( 'show_user_profile', array( $this, 'show_2fa_settings' ) );
		add_action( 'edit_user_profile', array( $this, 'show_2fa_settings' ) );
		add_action( 'personal_options_update', array( $this, 'save_2fa_settings' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_2fa_settings' ) );

		add_action( 'login_form', array( $this, 'add_2fa_login_fields' ) );
		add_filter( 'authenticate', array( $this, 'authenticate_2fa' ), 30, 3 );
	}

	/**
	 * Check if 2FA is required for user
	 */
	public function check_2fa_requirement( $user_login, $user ) {
		$user_2fa_settings = get_user_meta( $user->ID, 'ennu_2fa_settings', true );

		if ( empty( $user_2fa_settings ) || ! $user_2fa_settings['enabled'] ) {
			return; // 2FA not enabled for this user
		}

		if ( isset( $_SESSION['ennu_2fa_verified'] ) && $_SESSION['ennu_2fa_verified'] === $user->ID ) {
			return;
		}

		$this->require_2fa_verification( $user );
	}

	/**
	 * Require 2FA verification
	 */
	private function require_2fa_verification( $user ) {
		wp_logout();

		$_SESSION['ennu_2fa_user_id']  = $user->ID;
		$_SESSION['ennu_2fa_required'] = true;

		$this->send_verification_code( $user );

		wp_redirect( add_query_arg( 'ennu_2fa_required', '1', wp_login_url() ) );
		exit;
	}

	/**
	 * Send verification code
	 */
	private function send_verification_code( $user ) {
		$user_2fa_settings = get_user_meta( $user->ID, 'ennu_2fa_settings', true );
		$method            = $user_2fa_settings['primary_method'] ?? 'email';

		switch ( $method ) {
			case 'email':
				$this->send_email_verification( $user );
				break;
			case 'totp':
				break;
		}
	}

	/**
	 * Send email verification code
	 */
	private function send_email_verification( $user ) {
		$code = $this->generate_verification_code();

		set_transient( "ennu_2fa_code_{$user->ID}", $code, 300 ); // 5 minutes

		$subject  = 'ENNU Life - Two-Factor Authentication Code';
		$message  = "Your verification code is: {$code}\n\n";
		$message .= "This code will expire in 5 minutes.\n";
		$message .= "If you didn't request this code, please contact support immediately.";

		wp_mail( $user->user_email, $subject, $message );
	}

	/**
	 * Generate verification code
	 */
	private function generate_verification_code( $length = 6 ) {
		return str_pad( random_int( 0, pow( 10, $length ) - 1 ), $length, '0', STR_PAD_LEFT );
	}

	/**
	 * Verify 2FA code
	 */
	public function verify_2fa_code() {
		if ( ! isset( $_SESSION['ennu_2fa_user_id'] ) || ! isset( $_POST['verification_code'] ) ) {
			wp_die(
				json_encode(
					array(
						'success' => false,
						'message' => 'Invalid request',
					)
				)
			);
		}

		$user_id        = $_SESSION['ennu_2fa_user_id'];
		$submitted_code = sanitize_text_field( $_POST['verification_code'] );

		$user_2fa_settings = get_user_meta( $user_id, 'ennu_2fa_settings', true );
		$method            = $user_2fa_settings['primary_method'] ?? 'email';

		$verified = false;

		switch ( $method ) {
			case 'email':
				$verified = $this->verify_email_code( $user_id, $submitted_code );
				break;
			case 'totp':
				$verified = $this->verify_totp_code( $user_id, $submitted_code );
				break;
			case 'backup_codes':
				$verified = $this->verify_backup_code( $user_id, $submitted_code );
				break;
		}

		if ( $verified ) {
			$_SESSION['ennu_2fa_verified'] = $user_id;
			unset( $_SESSION['ennu_2fa_required'] );
			unset( $_SESSION['ennu_2fa_user_id'] );

			$user = get_user_by( 'ID', $user_id );
			wp_set_current_user( $user_id, $user->user_login );
			wp_set_auth_cookie( $user_id );
			do_action( 'wp_login', $user->user_login, $user );

			wp_send_json_success(
				array(
					'message'  => 'Verification successful',
					'redirect' => admin_url(),
				)
			);
		} else {
			wp_send_json_error( array( 'message' => 'Invalid verification code' ) );
		}
	}

	/**
	 * Verify email code
	 */
	private function verify_email_code( $user_id, $submitted_code ) {
		$stored_code = get_transient( "ennu_2fa_code_{$user_id}" );

		if ( $stored_code && $stored_code === $submitted_code ) {
			delete_transient( "ennu_2fa_code_{$user_id}" );
			return true;
		}

		return false;
	}

	/**
	 * Verify TOTP code
	 */
	private function verify_totp_code( $user_id, $submitted_code ) {
		$user_2fa_settings = get_user_meta( $user_id, 'ennu_2fa_settings', true );
		$secret            = $user_2fa_settings['totp_secret'] ?? '';

		if ( empty( $secret ) ) {
			return false;
		}

		return $this->verify_totp( $secret, $submitted_code );
	}

	/**
	 * Verify TOTP using time-based algorithm
	 */
	private function verify_totp( $secret, $code, $window = 1 ) {
		$time = floor( time() / 30 );

		for ( $i = -$window; $i <= $window; $i++ ) {
			$calculated_code = $this->calculate_totp( $secret, $time + $i );
			if ( $calculated_code === $code ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Calculate TOTP code
	 */
	private function calculate_totp( $secret, $time ) {
		$secret = base32_decode( $secret );
		$time   = pack( 'N*', 0 ) . pack( 'N*', $time );
		$hash   = hash_hmac( 'sha1', $time, $secret, true );
		$offset = ord( $hash[19] ) & 0xf;
		$code   = (
			( ( ord( $hash[ $offset + 0 ] ) & 0x7f ) << 24 ) |
			( ( ord( $hash[ $offset + 1 ] ) & 0xff ) << 16 ) |
			( ( ord( $hash[ $offset + 2 ] ) & 0xff ) << 8 ) |
			( ord( $hash[ $offset + 3 ] ) & 0xff )
		) % pow( 10, 6 );

		return str_pad( $code, 6, '0', STR_PAD_LEFT );
	}

	/**
	 * Verify backup code
	 */
	private function verify_backup_code( $user_id, $submitted_code ) {
		$user_2fa_settings = get_user_meta( $user_id, 'ennu_2fa_settings', true );
		$backup_codes      = $user_2fa_settings['backup_codes'] ?? array();

		if ( in_array( $submitted_code, $backup_codes ) ) {
			$backup_codes                      = array_diff( $backup_codes, array( $submitted_code ) );
			$user_2fa_settings['backup_codes'] = $backup_codes;
			update_user_meta( $user_id, 'ennu_2fa_settings', $user_2fa_settings );

			return true;
		}

		return false;
	}

	/**
	 * Setup 2FA method
	 */
	public function setup_2fa_method() {
		if ( ! current_user_can( 'edit_user', get_current_user_id() ) ) {
			wp_die( 'Insufficient permissions' );
		}

		$method  = sanitize_text_field( $_POST['method'] ?? '' );
		$user_id = get_current_user_id();

		switch ( $method ) {
			case 'totp':
				$this->setup_totp( $user_id );
				break;
			case 'email':
				$this->setup_email_2fa( $user_id );
				break;
		}
	}

	/**
	 * Setup TOTP
	 */
	private function setup_totp( $user_id ) {
		$secret = $this->generate_totp_secret();
		$user   = get_user_by( 'ID', $user_id );

		$qr_code_url = $this->generate_qr_code_url( $user->user_email, $secret );

		set_transient( "ennu_totp_setup_{$user_id}", $secret, 600 ); // 10 minutes

		wp_send_json_success(
			array(
				'secret'       => $secret,
				'qr_code_url'  => $qr_code_url,
				'manual_entry' => $secret,
			)
		);
	}

	/**
	 * Generate TOTP secret
	 */
	private function generate_totp_secret( $length = 32 ) {
		$chars  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$secret = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$secret .= $chars[ random_int( 0, strlen( $chars ) - 1 ) ];
		}
		return $secret;
	}

	/**
	 * Generate QR code URL for TOTP setup
	 */
	private function generate_qr_code_url( $email, $secret ) {
		$issuer  = urlencode( get_bloginfo( 'name' ) );
		$account = urlencode( $email );

		$otpauth_url = "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}";

		return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode( $otpauth_url );
	}

	/**
	 * Generate backup codes
	 */
	public function generate_backup_codes() {
		if ( ! current_user_can( 'edit_user', get_current_user_id() ) ) {
			wp_die( 'Insufficient permissions' );
		}

		$user_id      = get_current_user_id();
		$backup_codes = array();

		for ( $i = 0; $i < 10; $i++ ) {
			$backup_codes[] = $this->generate_backup_code();
		}

		$user_2fa_settings                 = get_user_meta( $user_id, 'ennu_2fa_settings', true ) ?: array();
		$user_2fa_settings['backup_codes'] = $backup_codes;
		update_user_meta( $user_id, 'ennu_2fa_settings', $user_2fa_settings );

		wp_send_json_success( array( 'backup_codes' => $backup_codes ) );
	}

	/**
	 * Generate backup code
	 */
	private function generate_backup_code() {
		return strtoupper( bin2hex( random_bytes( 4 ) ) );
	}

	/**
	 * Show 2FA settings in user profile
	 */
	public function show_2fa_settings( $user ) {
		$user_2fa_settings = get_user_meta( $user->ID, 'ennu_2fa_settings', true ) ?: array();
		$is_enabled        = $user_2fa_settings['enabled'] ?? false;

		?>
		<h3>Two-Factor Authentication</h3>
		<table class="form-table">
			<tr>
				<th><label for="ennu_2fa_enabled">Enable 2FA</label></th>
				<td>
					<input type="checkbox" name="ennu_2fa_enabled" id="ennu_2fa_enabled" value="1" <?php checked( $is_enabled ); ?> />
					<p class="description">Enable two-factor authentication for enhanced security.</p>
				</td>
			</tr>
			<?php if ( $is_enabled ) : ?>
			<tr>
				<th><label for="ennu_2fa_method">Primary Method</label></th>
				<td>
					<select name="ennu_2fa_method" id="ennu_2fa_method">
						<option value="email" <?php selected( $user_2fa_settings['primary_method'] ?? '', 'email' ); ?>>Email</option>
						<option value="totp" <?php selected( $user_2fa_settings['primary_method'] ?? '', 'totp' ); ?>>Authenticator App</option>
					</select>
				</td>
			</tr>
			<?php endif; ?>
		</table>
		<?php
	}

	/**
	 * Save 2FA settings
	 */
	public function save_2fa_settings( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		$enabled = isset( $_POST['ennu_2fa_enabled'] );
		$method  = sanitize_text_field( $_POST['ennu_2fa_method'] ?? 'email' );

		$user_2fa_settings                   = get_user_meta( $user_id, 'ennu_2fa_settings', true ) ?: array();
		$user_2fa_settings['enabled']        = $enabled;
		$user_2fa_settings['primary_method'] = $method;

		update_user_meta( $user_id, 'ennu_2fa_settings', $user_2fa_settings );
	}

	/**
	 * Add 2FA fields to login form
	 */
	public function add_2fa_login_fields() {
		if ( isset( $_GET['ennu_2fa_required'] ) ) {
			?>
			<p>
				<label for="verification_code">Verification Code<br />
				<input type="text" name="verification_code" id="verification_code" class="input" value="" size="20" autocomplete="off" /></label>
			</p>
			<script>
			document.getElementById('verification_code').focus();
			</script>
			<?php
		}
	}

	/**
	 * Authenticate with 2FA
	 */
	public function authenticate_2fa( $user, $username, $password ) {
		if ( is_wp_error( $user ) ) {
			return $user;
		}

		if ( isset( $_GET['ennu_2fa_required'] ) && isset( $_POST['verification_code'] ) ) {
			$this->verify_2fa_code();
		}

		return $user;
	}
}

if ( ! function_exists( 'base32_decode' ) ) {
	function base32_decode( $input ) {
		$alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
		$output   = '';
		$v        = 0;
		$vbits    = 0;

		for ( $i = 0, $j = strlen( $input ); $i < $j; $i++ ) {
			$v    <<= 5;
			$v     += strpos( $alphabet, $input[ $i ] );
			$vbits += 5;

			if ( $vbits >= 8 ) {
				$output .= chr( $v >> ( $vbits - 8 ) );
				$vbits  -= 8;
			}
		}

		return $output;
	}
}

if ( get_option( 'ennu_2fa_enabled', false ) ) {
	new ENNU_Two_Factor_Auth();
}
