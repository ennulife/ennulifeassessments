<?php
/**
 * ENNU Life Shortcode Verification Script
 * 
 * This script verifies that shortcodes are properly registered after the fix.
 * Place this file in your WordPress root directory and access via browser.
 * 
 * @package ENNU_Life
 * @version 60.4.0
 */

// Load WordPress
require_once( dirname( __FILE__ ) . '/wp-load.php' );

// Security check
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Access denied.' );
}

echo '<h1>ENNU Life Shortcode Verification - v60.4.0</h1>';

// Check if plugin is active
if ( ! is_plugin_active( 'ennulifeassessments/ennu-life-plugin.php' ) ) {
	echo '<p style="color: red;">❌ Plugin is not active!</p>';
	exit;
}

echo '<p style="color: green;">✅ Plugin is active</p>';

// Check if plugin classes exist
echo '<h2>Class Status</h2>';
echo '<ul>';
echo '<li><strong>ENNU_Life_Enhanced_Plugin:</strong> ' . ( class_exists( 'ENNU_Life_Enhanced_Plugin' ) ? '✅ Loaded' : '❌ Missing' ) . '</li>';
echo '<li><strong>ENNU_Assessment_Shortcodes:</strong> ' . ( class_exists( 'ENNU_Assessment_Shortcodes' ) ? '✅ Loaded' : '❌ Missing' ) . '</li>';
echo '<li><strong>ENNU_Assessment_Scoring:</strong> ' . ( class_exists( 'ENNU_Assessment_Scoring' ) ? '✅ Loaded' : '❌ Missing' ) . '</li>';
echo '</ul>';

// Try to get plugin instance
if ( class_exists( 'ENNU_Life_Enhanced_Plugin' ) ) {
	$plugin = ENNU_Life_Enhanced_Plugin::get_instance();
	echo '<p><strong>Plugin Instance:</strong> ✅ Created</p>';
	
	// Check if shortcodes object exists
	$shortcodes = $plugin->get_shortcodes();
	echo '<p><strong>Shortcodes Object:</strong> ' . ( $shortcodes ? '✅ Exists' : '❌ Missing' ) . '</p>';
	
	if ( $shortcodes ) {
		echo '<p><strong>Shortcodes Class:</strong> ' . get_class( $shortcodes ) . '</p>';
	}
}

// Check registered shortcodes
global $shortcode_tags;
echo '<h2>Registered Shortcodes</h2>';
echo '<p><strong>Total Registered:</strong> ' . count( $shortcode_tags ) . '</p>';

$ennu_shortcodes = array();
foreach ( $shortcode_tags as $tag => $callback ) {
	if ( strpos( $tag, 'ennu-' ) === 0 ) {
		$ennu_shortcodes[] = $tag;
	}
}

echo '<p><strong>ENNU Shortcodes Found:</strong> ' . count( $ennu_shortcodes ) . '</p>';

if ( ! empty( $ennu_shortcodes ) ) {
	echo '<h3>✅ Found ENNU Shortcodes:</h3>';
	echo '<ul>';
	foreach ( $ennu_shortcodes as $shortcode ) {
		echo '<li>' . esc_html( $shortcode ) . '</li>';
	}
	echo '</ul>';
	
	// Test a simple shortcode
	echo '<h3>Testing Shortcode Rendering</h3>';
	$test_output = do_shortcode( '[ennu-user-dashboard]' );
	if ( ! empty( $test_output ) ) {
		echo '<p style="color: green;">✅ Shortcode rendering works!</p>';
		echo '<div style="border: 1px solid #ccc; padding: 10px; margin: 10px 0; max-height: 200px; overflow-y: auto;">';
		echo '<strong>Sample Output:</strong><br>';
		echo esc_html( substr( $test_output, 0, 500 ) ) . '...';
		echo '</div>';
	} else {
		echo '<p style="color: orange;">⚠️ Shortcode registered but no output</p>';
	}
} else {
	echo '<p style="color: red;">❌ No ENNU shortcodes found!</p>';
}

// Check error logs for any issues
echo '<h2>Recent Error Log Entries</h2>';
$log_file = WP_CONTENT_DIR . '/debug.log';
if ( file_exists( $log_file ) ) {
	$log_content = file_get_contents( $log_file );
	$ennu_logs = array();
	$lines = explode( "\n", $log_content );
	foreach ( $lines as $line ) {
		if ( strpos( $line, 'ENNU' ) !== false ) {
			$ennu_logs[] = $line;
		}
	}
	
	if ( ! empty( $ennu_logs ) ) {
		echo '<p><strong>Recent ENNU Log Entries:</strong></p>';
		echo '<div style="border: 1px solid #ccc; padding: 10px; margin: 10px 0; max-height: 200px; overflow-y: auto; font-family: monospace; font-size: 12px;">';
		foreach ( array_slice( $ennu_logs, -10 ) as $log ) {
			echo esc_html( $log ) . '<br>';
		}
		echo '</div>';
	} else {
		echo '<p>No recent ENNU log entries found.</p>';
	}
} else {
	echo '<p>No debug.log file found.</p>';
}

echo '<hr>';
echo '<p><em>Verification completed. Remove this file after testing.</em></p>'; 