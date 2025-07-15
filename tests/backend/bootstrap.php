<?php
/**
 * PHPUnit Bootstrap for the ENNU Life Assessment Plugin.
 *
 * This file is responsible for loading the WordPress testing environment and the plugin itself.
 *
 * It assumes that the WordPress testing library is installed in a standard location,
 * typically by running the `install-wp-tests.sh` script provided with the library.
 *
 * @package ENNU_Life_Tests
 */

// Load the WordPress testing environment.
$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( dirname( __FILE__ ) ) ) . '/ennu-life-plugin.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
