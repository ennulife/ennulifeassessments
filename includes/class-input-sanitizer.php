<?php
/**
 * Input Sanitizer Class
 *
 * Provides comprehensive input sanitization for all user inputs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ENNU_Input_Sanitizer {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_filter( 'ennu_sanitize_form_data', array( $this, 'sanitize_form_data' ), 10, 2 );
	}

	public function sanitize_form_data( $data, $context = 'general' ) {
		if ( ! is_array( $data ) ) {
			return $this->sanitize_single_value( $data, $context );
		}

		$sanitized = array();

		foreach ( $data as $key => $value ) {
			$sanitized_key = sanitize_key( $key );

			if ( is_array( $value ) ) {
				$sanitized[ $sanitized_key ] = $this->sanitize_form_data( $value, $context );
			} else {
				$sanitized[ $sanitized_key ] = $this->sanitize_single_value( $value, $this->get_field_context( $key, $context ) );
			}
		}

		return $sanitized;
	}

	private function sanitize_single_value( $value, $context ) {
		switch ( $context ) {
			case 'email':
				return sanitize_email( $value );
			case 'url':
				return esc_url_raw( $value );
			case 'int':
				return intval( $value );
			case 'float':
				return floatval( $value );
			case 'textarea':
				return sanitize_textarea_field( $value );
			case 'html':
				return wp_kses_post( $value );
			case 'key':
				return sanitize_key( $value );
			case 'phone':
				return preg_replace( '/[^0-9+\-\(\)\s]/', '', $value );
			case 'date':
				return sanitize_text_field( $value );
			case 'multiselect':
				if ( is_array( $value ) ) {
					return array_map( 'sanitize_text_field', $value );
				}
				return sanitize_text_field( $value );
			default:
				return sanitize_text_field( $value );
		}
	}

	private function get_field_context( $field_name, $default_context ) {
		$field_contexts = array(
			'email'           => 'email',
			'user_email'      => 'email',
			'contact_email'   => 'email',
			'phone'           => 'phone',
			'phone_number'    => 'phone',
			'website'         => 'url',
			'url'             => 'url',
			'age'             => 'int',
			'weight'          => 'float',
			'height'          => 'float',
			'height_ft'       => 'int',
			'height_in'       => 'int',
			'weight_lbs'      => 'float',
			'bmi'             => 'float',
			'score'           => 'float',
			'percentage'      => 'float',
			'notes'           => 'textarea',
			'comments'        => 'textarea',
			'description'     => 'textarea',
			'dob'             => 'date',
			'date_of_birth'   => 'date',
			'dob_combined'    => 'date',
			'assessment_type' => 'key',
			'nonce'           => 'key',
			'action'          => 'key',
		);

		foreach ( $field_contexts as $pattern => $context ) {
			if ( strpos( $field_name, $pattern ) !== false ) {
				return $context;
			}
		}

		if ( is_array( $default_context ) ) {
			return 'multiselect';
		}

		return $default_context;
	}

	public function validate_required_fields( $data, $required_fields ) {
		$missing_fields = array();

		foreach ( $required_fields as $field ) {
			if ( ! isset( $data[ $field ] ) || empty( $data[ $field ] ) ) {
				$missing_fields[] = $field;
			}
		}

		if ( ! empty( $missing_fields ) ) {
			return new WP_Error( 'missing_required_fields', 'Required fields are missing: ' . implode( ', ', $missing_fields ), $missing_fields );
		}

		return true;
	}

	public function validate_email_field( $email ) {
		if ( empty( $email ) ) {
			return new WP_Error( 'empty_email', 'Email address is required.' );
		}

		if ( ! is_email( $email ) ) {
			return new WP_Error( 'invalid_email', 'Please provide a valid email address.' );
		}

		return true;
	}

	public function validate_numeric_range( $value, $min = null, $max = null, $field_name = 'value' ) {
		if ( ! is_numeric( $value ) ) {
			return new WP_Error( 'invalid_numeric', sprintf( '%s must be a number.', ucfirst( $field_name ) ) );
		}

		$numeric_value = floatval( $value );

		if ( $min !== null && $numeric_value < $min ) {
			return new WP_Error( 'value_too_low', sprintf( '%s must be at least %s.', ucfirst( $field_name ), $min ) );
		}

		if ( $max !== null && $numeric_value > $max ) {
			return new WP_Error( 'value_too_high', sprintf( '%s must be no more than %s.', ucfirst( $field_name ), $max ) );
		}

		return true;
	}

	public function validate_assessment_data( $data ) {
		$errors = array();

		if ( ! isset( $data['assessment_type'] ) || empty( $data['assessment_type'] ) ) {
			$errors[] = 'Assessment type is required.';
		}

		if ( isset( $data['email'] ) && ! empty( $data['email'] ) ) {
			$email_validation = $this->validate_email_field( $data['email'] );
			if ( is_wp_error( $email_validation ) ) {
				$errors[] = $email_validation->get_error_message();
			}
		}

		if ( isset( $data['age'] ) && ! empty( $data['age'] ) ) {
			$age_validation = $this->validate_numeric_range( $data['age'], 1, 120, 'age' );
			if ( is_wp_error( $age_validation ) ) {
				$errors[] = $age_validation->get_error_message();
			}
		}

		if ( isset( $data['weight_lbs'] ) && ! empty( $data['weight_lbs'] ) ) {
			$weight_validation = $this->validate_numeric_range( $data['weight_lbs'], 50, 1000, 'weight' );
			if ( is_wp_error( $weight_validation ) ) {
				$errors[] = $weight_validation->get_error_message();
			}
		}

		if ( isset( $data['height_ft'] ) && ! empty( $data['height_ft'] ) ) {
			$height_validation = $this->validate_numeric_range( $data['height_ft'], 3, 8, 'height (feet)' );
			if ( is_wp_error( $height_validation ) ) {
				$errors[] = $height_validation->get_error_message();
			}
		}

		if ( isset( $data['height_in'] ) && ! empty( $data['height_in'] ) ) {
			$inches_validation = $this->validate_numeric_range( $data['height_in'], 0, 11, 'height (inches)' );
			if ( is_wp_error( $inches_validation ) ) {
				$errors[] = $inches_validation->get_error_message();
			}
		}

		if ( ! empty( $errors ) ) {
			return new WP_Error( 'validation_failed', 'Validation errors occurred.', $errors );
		}

		return true;
	}
}

ENNU_Input_Sanitizer::get_instance();
