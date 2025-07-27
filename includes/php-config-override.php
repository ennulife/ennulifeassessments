<?php
/**
 * PHP Configuration Override for ENNU Life Assessments
 *
 * This file applies PHP configuration overrides to handle complex forms
 * with many input fields and large data submissions.
 *
 * @package   ENNU_Life
 * @copyright Copyright (c) 2024, ENNU Life, https://ennulife.com
 * @license   GPL-3.0+
 * @since     62.2.8
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Apply PHP configuration overrides for complex forms
 */
function ennu_apply_php_config_overrides() {
	
	// Increase max_input_vars to handle large forms
	if ( ini_get( 'max_input_vars' ) < 10000 ) {
		@ini_set( 'max_input_vars', '10000' );
	}
	
	// Increase post_max_size to handle large form data
	if ( ini_get( 'post_max_size' ) < 64 ) {
		@ini_set( 'post_max_size', '64M' );
	}
	
	// Increase max_execution_time for complex operations
	if ( ini_get( 'max_execution_time' ) < 300 ) {
		@ini_set( 'max_execution_time', '300' );
	}
	
	// Increase memory limit if needed
	if ( ini_get( 'memory_limit' ) < 512 ) {
		@ini_set( 'memory_limit', '512M' );
	}
	
	// Increase max_input_time for large form processing
	if ( ini_get( 'max_input_time' ) < 300 ) {
		@ini_set( 'max_input_time', '300' );
	}
	
	// Log the configuration application
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( 'ENNU PHP Config: Applied configuration overrides for complex forms' );
	}
}

// Apply overrides early
add_action( 'init', 'ennu_apply_php_config_overrides', 1 ); 