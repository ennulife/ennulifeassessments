<?php
/**
 * Test file for scorepresentation shortcode
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Test the shortcode directly
function test_scorepresentation_shortcode() {
	// Check if the shortcode class exists
	if ( ! class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
		echo '<p style="color: red;">ERROR: ENNU_Assessment_Shortcodes class not found!</p>';
		return;
	}

	// Create instance and test the method
	$shortcodes = new ENNU_Assessment_Shortcodes();
	
	// Test the shortcode method directly
	$test_output = $shortcodes->render_score_presentation( array( 'type' => 'lifescore' ) );
	
	echo '<h2>Score Presentation Shortcode Test</h2>';
	echo '<p>Testing shortcode output:</p>';
	echo '<div style="border: 2px solid #ccc; padding: 20px; margin: 20px 0;">';
	echo $test_output;
	echo '</div>';
	
	// Test if shortcode is registered
	global $shortcode_tags;
	if ( isset( $shortcode_tags['scorepresentation'] ) ) {
		echo '<p style="color: green;">✅ Shortcode "scorepresentation" is registered!</p>';
	} else {
		echo '<p style="color: red;">❌ Shortcode "scorepresentation" is NOT registered!</p>';
		echo '<p>Available shortcodes:</p>';
		echo '<ul>';
		foreach ( $shortcode_tags as $tag => $callback ) {
			if ( strpos( $tag, 'ennu' ) !== false || strpos( $tag, 'score' ) !== false ) {
				echo '<li>' . esc_html( $tag ) . '</li>';
			}
		}
		echo '</ul>';
	}
	
	// Test different types
	echo '<h3>Testing Different Types:</h3>';
	$types = array( 'lifescore', 'pmind', 'pbody', 'plifestyle', 'paesthetics' );
	
	foreach ( $types as $type ) {
		echo '<h4>Type: ' . esc_html( $type ) . '</h4>';
		$output = $shortcodes->render_score_presentation( array( 'type' => $type ) );
		echo '<div style="border: 1px solid #ddd; padding: 10px; margin: 10px 0;">';
		echo $output;
		echo '</div>';
	}
}

// Add admin menu item for testing
add_action( 'admin_menu', function() {
	add_submenu_page(
		'tools.php',
		'Test Score Presentation',
		'Test Score Presentation',
		'manage_options',
		'test-scorepresentation',
		'test_scorepresentation_shortcode'
	);
} );

// Also add a simple test that can be accessed directly
add_action( 'wp_ajax_test_scorepresentation', function() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized' );
	}
	
	test_scorepresentation_shortcode();
	wp_die();
} );

// Add a simple test shortcode
add_shortcode( 'test_scorepresentation', function() {
	ob_start();
	test_scorepresentation_shortcode();
	return ob_get_clean();
} ); 