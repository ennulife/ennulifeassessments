<?php
/**
 * PHPUnit Bootstrap for the ENNU Life Assessment Plugin.
 *
 * @package ENNU_Life_Tests
 */

// First, load the Composer autoloader.
require_once dirname( dirname( __DIR__ ) ) . '/vendor/autoload.php';

// Load the WordPress testing environment.
if ( getenv( 'WP_TESTS_DIR' ) ) {
	require_once getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';
}
