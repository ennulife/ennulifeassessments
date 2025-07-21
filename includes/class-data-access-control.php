<?php
/**
 * Data Access Control Class
 *
 * Manages user data access permissions and prevents unauthorized data exposure
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Data_Access_Control {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_filter( 'ennu_can_view_user_data', array( $this, 'check_user_data_access' ), 10, 2 );
		add_filter( 'ennu_sanitize_user_display_data', array( $this, 'sanitize_display_data' ), 10, 2 );
	}

	public function check_user_data_access( $user_id, $requested_data_type = 'basic' ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$current_user_id = get_current_user_id();

		if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_users' ) ) {
			return true;
		}

		if ( $current_user_id !== intval( $user_id ) ) {
			return false;
		}

		switch ( $requested_data_type ) {
			case 'sensitive':
				return current_user_can( 'manage_options' );
			case 'assessment_results':
				return $current_user_id === intval( $user_id ) || current_user_can( 'edit_users' );
			case 'basic':
			default:
				return true;
		}
	}

	public function sanitize_display_data( $data, $context = 'dashboard' ) {
		if ( ! is_array( $data ) ) {
			return esc_html( $data );
		}

		$sanitized = array();

		foreach ( $data as $key => $value ) {
			$sanitized_key = sanitize_key( $key );

			if ( is_array( $value ) ) {
				$sanitized[ $sanitized_key ] = $this->sanitize_display_data( $value, $context );
			} else {
				switch ( $key ) {
					case 'email':
						$sanitized[ $sanitized_key ] = is_email( $value ) ? esc_html( $value ) : '';
						break;
					case 'name':
					case 'first_name':
					case 'last_name':
						$sanitized[ $sanitized_key ] = esc_html( $value );
						break;
					case 'score':
					case 'percentage':
						$sanitized[ $sanitized_key ] = is_numeric( $value ) ? floatval( $value ) : 0;
						break;
					case 'notes':
					case 'description':
						$sanitized[ $sanitized_key ] = wp_kses_post( $value );
						break;
					case 'url':
					case 'link':
						$sanitized[ $sanitized_key ] = esc_url( $value );
						break;
					default:
						if ( is_numeric( $value ) ) {
							$sanitized[ $sanitized_key ] = is_float( $value ) ? floatval( $value ) : intval( $value );
						} else {
							$sanitized[ $sanitized_key ] = esc_html( $value );
						}
						break;
				}
			}
		}

		return $sanitized;
	}

	public function filter_sensitive_data( $user_data, $user_id ) {
		if ( ! $this->check_user_data_access( $user_id, 'sensitive' ) ) {
			$filtered_data = $user_data;

			$sensitive_fields = array(
				'phone',
				'address',
				'date_of_birth',
				'ssn',
				'medical_id',
				'insurance_info',
			);

			foreach ( $sensitive_fields as $field ) {
				if ( isset( $filtered_data[ $field ] ) ) {
					unset( $filtered_data[ $field ] );
				}
			}

			return $filtered_data;
		}

		return $user_data;
	}

	public function log_data_access( $user_id, $data_type, $accessor_id = null ) {
		$accessor_id = $accessor_id ?: get_current_user_id();

		$log_entry = array(
			'timestamp'        => current_time( 'mysql' ),
			'accessed_user_id' => intval( $user_id ),
			'accessor_user_id' => intval( $accessor_id ),
			'data_type'        => sanitize_text_field( $data_type ),
			'ip_address'       => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
		);

		error_log( 'ENNU Data Access: ' . wp_json_encode( $log_entry ) );

		do_action( 'ennu_data_access_logged', $log_entry );
	}

	public function mask_sensitive_display( $value, $field_type ) {
		switch ( $field_type ) {
			case 'email':
				if ( is_email( $value ) ) {
					$parts    = explode( '@', $value );
					$username = $parts[0];
					$domain   = $parts[1];

					if ( strlen( $username ) > 2 ) {
						$masked_username = substr( $username, 0, 2 ) . str_repeat( '*', strlen( $username ) - 2 );
						return $masked_username . '@' . $domain;
					}
				}
				return $value;

			case 'phone':
				$cleaned = preg_replace( '/[^0-9]/', '', $value );
				if ( strlen( $cleaned ) >= 10 ) {
					return substr( $cleaned, 0, 3 ) . '-***-' . substr( $cleaned, -4 );
				}
				return $value;

			default:
				return $value;
		}
	}
}

ENNU_Data_Access_Control::get_instance();
