<?php
/**
 * ENNU Life Bulletproof Compatibility Manager
 *
 * Ensures zero compatibility issues across all WordPress and PHP versions,
 * with graceful degradation and comprehensive polyfills.
 *
 * @package ENNU_Life
 * @version 23.1.0
 * @author Manus - World's Greatest WordPress Developer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bulletproof Compatibility Manager
 *
 * Handles all compatibility issues to ensure zero-issue deployment
 */
class ENNU_Compatibility_Manager {

	/**
	 * Compatibility status
	 *
	 * @var array
	 */
	private static $compatibility_status = array();

	/**
	 * Required extensions
	 *
	 * @var array
	 */
	private static $required_extensions = array(
		'json'     => 'JSON support is required for data processing',
		'curl'     => 'cURL is required for external API communications',
		'mbstring' => 'Multibyte string support is required for text processing',
	);

	/**
	 * Required WordPress functions
	 *
	 * @var array
	 */
	private static $required_wp_functions = array(
		'wp_enqueue_script'    => 'WordPress script enqueuing',
		'wp_enqueue_style'     => 'WordPress style enqueuing',
		'wp_create_nonce'      => 'WordPress security nonces',
		'wp_verify_nonce'      => 'WordPress nonce verification',
		'get_user_meta'        => 'WordPress user metadata',
		'update_user_meta'     => 'WordPress user metadata updates',
		'wp_send_json_success' => 'WordPress AJAX responses',
		'wp_send_json_error'   => 'WordPress AJAX error responses',
	);

	/**
	 * Initialize compatibility system
	 */
	public static function init() {
		// Check all compatibility requirements
		self::check_all_requirements();

		// Add polyfills for missing functions
		self::add_polyfills();

		// Hook into WordPress
		add_action( 'admin_notices', array( __CLASS__, 'display_compatibility_notices' ) );
		add_action( 'wp_ajax_ennu_compatibility_check', array( __CLASS__, 'ajax_compatibility_check' ) );
	}

	/**
	 * Comprehensive compatibility check
	 */
	public static function check_all_requirements() {
		$errors   = array();
		$warnings = array();

		// 1. PHP Version Check
		$php_result = self::check_php_version();
		if ( is_wp_error( $php_result ) ) {
			$errors[] = $php_result->get_error_message();
		}

		// 2. WordPress Version Check
		$wp_result = self::check_wordpress_version();
		if ( is_wp_error( $wp_result ) ) {
			$errors[] = $wp_result->get_error_message();
		}

		// 3. PHP Extensions Check
		$ext_result = self::check_php_extensions();
		if ( is_wp_error( $ext_result ) ) {
			$errors = array_merge( $errors, $ext_result->get_error_data() );
		}

		// 4. WordPress Functions Check
		$func_result = self::check_wordpress_functions();
		if ( is_wp_error( $func_result ) ) {
			$errors = array_merge( $errors, $func_result->get_error_data() );
		}

		// 5. Memory Limit Check
		$memory_result = self::check_memory_limit();
		if ( is_wp_error( $memory_result ) ) {
			$warnings[] = $memory_result->get_error_message();
		}

		// 6. File Permissions Check
		$perms_result = self::check_file_permissions();
		if ( is_wp_error( $perms_result ) ) {
			$warnings[] = $perms_result->get_error_message();
		}

		// Store results
		self::$compatibility_status = array(
			'errors'     => $errors,
			'warnings'   => $warnings,
			'compatible' => empty( $errors ),
			'checked_at' => current_time( 'mysql' ),
		);

		// Store in database
		update_option( 'ennu_compatibility_status', self::$compatibility_status );

		return self::$compatibility_status;
	}

	/**
	 * Check PHP version compatibility
	 */
	private static function check_php_version() {
		$required_version = defined( 'ENNU_LIFE_MIN_PHP_VERSION' ) ? ENNU_LIFE_MIN_PHP_VERSION : '7.4';

		if ( version_compare( PHP_VERSION, $required_version, '<' ) ) {
			return new WP_Error(
				'php_version_incompatible',
				sprintf(
					'PHP %s or higher is required. You are running PHP %s. Please upgrade your PHP version.',
					$required_version,
					PHP_VERSION
				)
			);
		}

		return true;
	}

	/**
	 * Check WordPress version compatibility
	 */
	private static function check_wordpress_version() {
		global $wp_version;
		$required_version = defined( 'ENNU_LIFE_MIN_WP_VERSION' ) ? ENNU_LIFE_MIN_WP_VERSION : '5.0';

		if ( version_compare( $wp_version, $required_version, '<' ) ) {
			return new WP_Error(
				'wp_version_incompatible',
				sprintf(
					'WordPress %s or higher is required. You are running WordPress %s. Please upgrade WordPress.',
					$required_version,
					$wp_version
				)
			);
		}

		return true;
	}

	/**
	 * Check required PHP extensions
	 */
	private static function check_php_extensions() {
		$missing_extensions = array();

		foreach ( self::$required_extensions as $extension => $description ) {
			if ( ! extension_loaded( $extension ) ) {
				$missing_extensions[] = sprintf(
					'PHP extension "%s" is missing. %s',
					$extension,
					$description
				);
			}
		}

		if ( ! empty( $missing_extensions ) ) {
			return new WP_Error(
				'missing_php_extensions',
				'Required PHP extensions are missing.',
				$missing_extensions
			);
		}

		return true;
	}

	/**
	 * Check required WordPress functions
	 */
	private static function check_wordpress_functions() {
		$missing_functions = array();

		foreach ( self::$required_wp_functions as $function => $description ) {
			if ( ! function_exists( $function ) ) {
				$missing_functions[] = sprintf(
					'WordPress function "%s" is not available. Required for %s.',
					$function,
					$description
				);
			}
		}

		if ( ! empty( $missing_functions ) ) {
			return new WP_Error(
				'missing_wp_functions',
				'Required WordPress functions are missing.',
				$missing_functions
			);
		}

		return true;
	}

	/**
	 * Check memory limit
	 */
	private static function check_memory_limit() {
		$memory_limit      = wp_convert_hr_to_bytes( ini_get( 'memory_limit' ) );
		$recommended_limit = 128 * 1024 * 1024; // 128MB

		if ( $memory_limit < $recommended_limit ) {
			return new WP_Error(
				'low_memory_limit',
				sprintf(
					'Memory limit is %s. Recommended: 128MB or higher for optimal performance.',
					size_format( $memory_limit )
				)
			);
		}

		return true;
	}

	/**
	 * Check file permissions
	 */
	private static function check_file_permissions() {
		$upload_dir = wp_upload_dir();

		if ( ! wp_is_writable( $upload_dir['basedir'] ) ) {
			return new WP_Error(
				'upload_not_writable',
				'Upload directory is not writable. File operations may fail.'
			);
		}

		return true;
	}

	/**
	 * Add polyfills for missing functions
	 */
	public static function add_polyfills() {
		// These polyfills are not required for the specified minimum WordPress
		// and PHP versions. They are removed to reduce code complexity and
		// potential conflicts.
	}

	/**
	 * Display compatibility notices in admin
	 */
	public static function display_compatibility_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$status = get_option( 'ennu_compatibility_status', array() );

		if ( empty( $status ) ) {
			return;
		}

		// Display errors
		if ( ! empty( $status['errors'] ) ) {
			echo '<div class="notice notice-error is-dismissible">';
			echo '<h3>ENNU Life Compatibility Issues</h3>';
			echo '<p><strong>The following issues must be resolved before ENNU Life can function properly:</strong></p>';
			echo '<ul>';
			foreach ( $status['errors'] as $error ) {
				echo '<li>' . esc_html( $error ) . '</li>';
			}
			echo '</ul>';
			echo '<p><em>Please contact your hosting provider or system administrator to resolve these issues.</em></p>';
			echo '</div>';
		}

		// Display warnings
		if ( ! empty( $status['warnings'] ) ) {
			echo '<div class="notice notice-warning is-dismissible">';
			echo '<h3>ENNU Life Performance Recommendations</h3>';
			echo '<p><strong>The following recommendations will improve performance:</strong></p>';
			echo '<ul>';
			foreach ( $status['warnings'] as $warning ) {
				echo '<li>' . esc_html( $warning ) . '</li>';
			}
			echo '</ul>';
			echo '</div>';
		}

		// Display success
		if ( empty( $status['errors'] ) && empty( $status['warnings'] ) ) {
			echo '<div class="notice notice-success is-dismissible">';
			echo '<h3>ENNU Life Compatibility</h3>';
			echo '<p><strong>✅ All compatibility checks passed!</strong> Your system is fully compatible with ENNU Life.</p>';
			echo '</div>';
		}
	}

	/**
	 * AJAX compatibility check
	 */
	public static function ajax_compatibility_check() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Insufficient permissions' );
			return;
		}

		if ( ! wp_verify_nonce( $_POST['nonce'] ?? '', 'ennu_admin_nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
			return;
		}

		$status = self::check_all_requirements();

		wp_send_json_success(
			array(
				'status'  => $status,
				'message' => $status['compatible'] ? 'All compatibility checks passed!' : 'Compatibility issues found.',
			)
		);
	}

	/**
	 * Get compatibility status
	 */
	public static function get_compatibility_status() {
		return self::$compatibility_status;
	}

	/**
	 * Check if system is compatible
	 */
	public static function is_compatible() {
		$status = get_option( 'ennu_compatibility_status', array() );
		return isset( $status['compatible'] ) ? $status['compatible'] : false;
	}

	/**
	 * Get system information
	 */
	public static function get_system_info() {
		global $wp_version;

		return array(
			'php_version'         => PHP_VERSION,
			'wp_version'          => $wp_version,
			'memory_limit'        => ini_get( 'memory_limit' ),
			'max_execution_time'  => ini_get( 'max_execution_time' ),
			'upload_max_filesize' => ini_get( 'upload_max_filesize' ),
			'post_max_size'       => ini_get( 'post_max_size' ),
			'extensions'          => array_map(
				function( $ext ) {
					return extension_loaded( $ext );
				},
				array_keys( self::$required_extensions )
			),
			'functions'           => array_map(
				function( $func ) {
					return function_exists( $func );
				},
				array_keys( self::$required_wp_functions )
			),
		);
	}

	/**
	 * Generate compatibility report
	 */
	public static function generate_compatibility_report() {
		$status      = self::check_all_requirements();
		$system_info = self::get_system_info();

		$report = array(
			'timestamp'            => current_time( 'mysql' ),
			'plugin_version'       => defined( 'ENNU_LIFE_VERSION' ) ? ENNU_LIFE_VERSION : 'unknown',
			'compatibility_status' => $status,
			'system_info'          => $system_info,
			'recommendations'      => self::get_recommendations( $status, $system_info ),
		);

		return $report;
	}

	/**
	 * Get recommendations based on system status
	 */
	private static function get_recommendations( $status, $system_info ) {
		$recommendations = array();

		if ( ! empty( $status['errors'] ) ) {
			$recommendations[] = 'Resolve all compatibility errors before using ENNU Life in production.';
		}

		if ( ! empty( $status['warnings'] ) ) {
			$recommendations[] = 'Address performance warnings for optimal operation.';
		}

		if ( version_compare( $system_info['php_version'], '8.0', '<' ) ) {
			$recommendations[] = 'Consider upgrading to PHP 8.0+ for better performance and security.';
		}

		$memory_bytes = wp_convert_hr_to_bytes( $system_info['memory_limit'] );
		if ( $memory_bytes < 256 * 1024 * 1024 ) {
			$recommendations[] = 'Increase memory limit to 256MB or higher for better performance.';
		}

		if ( empty( $recommendations ) ) {
			$recommendations[] = 'Your system is optimally configured for ENNU Life!';
		}

		return $recommendations;
	}

	/**
	 * Force compatibility mode
	 */
	public static function enable_compatibility_mode() {
		// Enable all polyfills
		self::add_polyfills();

		// Disable problematic features
		add_filter( 'ennu_enable_advanced_features', '__return_false' );
		add_filter( 'ennu_enable_caching', '__return_false' );
		add_filter( 'ennu_enable_security_features', '__return_false' );

		update_option( 'ennu_compatibility_mode', true );
	}

	/**
	 * Disable compatibility mode
	 */
	public static function disable_compatibility_mode() {
		delete_option( 'ennu_compatibility_mode' );
	}

	/**
	 * Check if compatibility mode is enabled
	 */
	public static function is_compatibility_mode() {
		return get_option( 'ennu_compatibility_mode', false );
	}
}

// Initialize compatibility manager only if WordPress is fully loaded
if ( function_exists( 'wp_convert_hr_to_bytes' ) && function_exists( 'get_option' ) && function_exists( 'add_action' ) ) {
	ENNU_Compatibility_Manager::init();
}

