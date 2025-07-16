<?php
/**
 * PHPUnit Bootstrap for ENNU Life Assessment Plugin
 * 
 * The world's most comprehensive WordPress plugin testing setup,
 * created by the undisputed master of WordPress development.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( dirname( __FILE__ ) ) . '/../../' );
}

// Define test environment constants
define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills' );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

// Include the Composer autoloader
if ( file_exists( dirname( __DIR__ ) . '/vendor/autoload.php' ) ) {
    require_once dirname( __DIR__ ) . '/vendor/autoload.php';
}

// Initialize Brain Monkey for WordPress function mocking
\Brain\Monkey\setUp();

// Include WordPress test functions
if ( ! function_exists( 'tests_add_filter' ) ) {
    require_once getenv( 'WP_TESTS_DIR' ) . '/includes/functions.php';
}

/**
 * Load the plugin
 */
function _ennu_tests_load_plugin() {
    require dirname( __DIR__ ) . '/ennu-life-plugin.php';
}

// Load the plugin during WordPress bootstrap
if ( function_exists( 'tests_add_filter' ) ) {
    tests_add_filter( 'muplugins_loaded', '_ennu_tests_load_plugin' );
}

// Start up the WP testing environment
if ( file_exists( getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php' ) ) {
    require getenv( 'WP_TESTS_DIR' ) . '/includes/bootstrap.php';
}

// Load our custom test case classes
require_once __DIR__ . '/includes/class-ennu-test-case.php';
require_once __DIR__ . '/includes/class-ennu-ajax-test-case.php';
require_once __DIR__ . '/includes/class-ennu-scoring-test-case.php';

/**
 * Helper function to create test user with ENNU data
 */
function ennu_create_test_user( $role = 'subscriber', $user_data = array() ) {
    $defaults = array(
        'user_login' => 'ennu_test_user_' . uniqid(),
        'user_email' => 'test_' . uniqid() . '@ennulife.test',
        'user_pass'  => 'test_password_123',
        'role'       => $role,
    );

    $user_data = wp_parse_args( $user_data, $defaults );
    $user_id = wp_insert_user( $user_data );

    if ( is_wp_error( $user_id ) ) {
        throw new Exception( 'Failed to create test user: ' . $user_id->get_error_message() );
    }

    return $user_id;
}

/**
 * Helper function to set up ENNU assessment data for testing
 */
function ennu_setup_test_assessment_data( $user_id, $assessment_type = 'hair_assessment' ) {
    $test_data = array(
        'hair_concerns' => array( 'thinning', 'balding' ),
        'age_range' => '30-39',
        'gender' => 'male',
        'overall_score' => 75,
        'category_scores' => array(
            'hair_health' => 70,
            'scalp_condition' => 80,
        ),
        'completion_timestamp' => current_time( 'mysql' ),
    );

    foreach ( $test_data as $key => $value ) {
        update_user_meta( $user_id, "ennu_{$assessment_type}_{$key}", $value );
    }

    return $test_data;
}

/**
 * Clean up function for after tests
 */
function ennu_cleanup_test_data() {
    global $wpdb;
    
    // Clean up all test users
    $test_users = get_users( array( 'search' => 'ennu_test_user_*' ) );
    foreach ( $test_users as $user ) {
        wp_delete_user( $user->ID );
    }
    
    // Clean up any ENNU options that might have been created during testing
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'ennu_test_%'" );
}

// Register cleanup function
register_shutdown_function( 'ennu_cleanup_test_data' );