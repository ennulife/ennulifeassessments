<?php
/**
 * Test Script: Header Styling Debug
 *
 * This script tests the header styling to identify why styles aren't being applied.
 *
 * @package ENNU Life Assessments
 * @version 62.2.21
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	require_once '../../../wp-load.php';
}

// Ensure we're in WordPress context
if ( ! function_exists( 'get_option' ) ) {
	die( 'WordPress not loaded' );
}

echo "<h1>ENNU Life Header Styling Debug</h1>\n";
echo "<p>Testing header styling to identify why styles aren't being applied.</p>\n";

// Initialize the shortcodes class
$shortcode_instance = new ENNU_Assessment_Shortcodes();

// Test the header rendering method
echo "<h2>1. Testing Header HTML Generation</h2>\n";

// Use reflection to access private method for testing
$reflection           = new ReflectionClass( $shortcode_instance );
$render_header_method = $reflection->getMethod( 'render_ennu_header' );
$render_header_method->setAccessible( true );

$header_html = $render_header_method->invoke(
	$shortcode_instance,
	'test_assessment',
	array(
		'title'       => 'Test Assessment',
		'description' => 'This is a test assessment description',
	)
);

echo "<div style='background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
echo "<h3>Generated Header HTML:</h3>\n";
echo "<pre style='background: white; padding: 15px; border-radius: 5px; overflow-x: auto;'>" . htmlspecialchars( $header_html ) . "</pre>\n";
echo "</div>\n";

// Test CSS file existence
echo "<h2>2. Testing CSS File Existence</h2>\n";

$css_file_path = ENNU_LIFE_PLUGIN_PATH . 'assets/css/ennu-frontend-forms.css';
$css_file_url  = ENNU_LIFE_PLUGIN_URL . 'assets/css/ennu-frontend-forms.css';

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr><th>File Path</th><th>Exists</th><th>URL</th></tr>\n";

$file_exists = file_exists( $css_file_path );
echo '<tr>';
echo "<td><code>$css_file_path</code></td>";
echo "<td style='color: " . ( $file_exists ? 'green' : 'red' ) . "; font-weight: bold;'>" . ( $file_exists ? '✓ YES' : '✗ NO' ) . '</td>';
echo "<td><a href='$css_file_url' target='_blank'>$css_file_url</a></td>";
echo "</tr>\n";
echo "</table>\n";

// Test CSS content
if ( $file_exists ) {
	echo "<h2>3. Testing CSS Content</h2>\n";

	$css_content = file_get_contents( $css_file_path );

	// Check for header styles
	$header_styles_found = array(
		'ennu-header-container' => strpos( $css_content, '.ennu-header-container' ) !== false,
		'ennu-logo-container'   => strpos( $css_content, '.ennu-logo-container' ) !== false,
		'ennu-logo'             => strpos( $css_content, '.ennu-logo' ) !== false,
		'ennu-header-content'   => strpos( $css_content, '.ennu-header-content' ) !== false,
		'ennu-header-title'     => strpos( $css_content, '.ennu-header-title' ) !== false,
		'ennu-header-subtitle'  => strpos( $css_content, '.ennu-header-subtitle' ) !== false,
	);

	echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
	echo "<tr><th>CSS Class</th><th>Found in CSS</th></tr>\n";

	foreach ( $header_styles_found as $class => $found ) {
		$status       = $found ? '✓ YES' : '✗ NO';
		$status_color = $found ? 'green' : 'red';

		echo '<tr>';
		echo "<td><code>$class</code></td>";
		echo "<td style='color: $status_color; font-weight: bold;'>$status</td>";
		echo "</tr>\n";
	}
	echo "</table>\n";

	// Show CSS content for header
	echo "<h3>Header CSS Content:</h3>\n";
	echo "<div style='background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";

	// Extract header CSS
	$header_css_start = strpos( $css_content, '/* ENNU Life Header Container */' );
	if ( $header_css_start !== false ) {
		$header_css_end = strpos( $css_content, '/* Assessment Form Container */', $header_css_start );
		if ( $header_css_end !== false ) {
			$header_css = substr( $css_content, $header_css_start, $header_css_end - $header_css_start );
			echo "<pre style='background: white; padding: 15px; border-radius: 5px; overflow-x: auto;'>" . htmlspecialchars( $header_css ) . "</pre>\n";
		} else {
			echo "<p>Could not find end of header CSS section.</p>\n";
		}
	} else {
		echo "<p>Could not find header CSS section.</p>\n";
	}

	echo "</div>\n";
}

// Test CSS enqueuing
echo "<h2>4. Testing CSS Enqueuing</h2>\n";

// Check if the style is registered
$registered_styles              = wp_styles();
$ennu_frontend_forms_registered = isset( $registered_styles->registered['ennu-frontend-forms'] );

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr><th>Style Handle</th><th>Registered</th><th>Enqueued</th></tr>\n";

$ennu_frontend_forms_enqueued = wp_style_is( 'ennu-frontend-forms', 'enqueued' );
echo '<tr>';
echo '<td><code>ennu-frontend-forms</code></td>';
echo "<td style='color: " . ( $ennu_frontend_forms_registered ? 'green' : 'red' ) . "; font-weight: bold;'>" . ( $ennu_frontend_forms_registered ? '✓ YES' : '✗ NO' ) . '</td>';
echo "<td style='color: " . ( $ennu_frontend_forms_enqueued ? 'green' : 'red' ) . "; font-weight: bold;'>" . ( $ennu_frontend_forms_enqueued ? '✓ YES' : '✗ NO' ) . '</td>';
echo "</tr>\n";
echo "</table>\n";

// Force enqueue the style for testing
if ( ! $ennu_frontend_forms_enqueued ) {
	echo "<p><strong>Forcing CSS enqueue for testing...</strong></p>\n";
	wp_enqueue_style( 'ennu-frontend-forms' );
}

// Test rendered header with inline styles
echo "<h2>5. Testing Header with Inline Styles</h2>\n";

echo "<div style='background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
echo "<h3>Header with Inline Styles (for comparison):</h3>\n";
echo "<div style='text-align: center; margin-bottom: 40px; padding: 20px 0; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; margin: 0 0 30px 0; position: relative; overflow: hidden;'>\n";
echo "<div style='margin-bottom: 20px; position: relative; z-index: 2;'>\n";
echo "<img src='" . ENNU_LIFE_PLUGIN_URL . "assets/img/ennu-logo-black.png' alt='ENNU Life Logo' style='height: 40px; width: auto; display: block;'>\n";
echo "</div>\n";
echo "<div style='position: relative; z-index: 2;'>\n";
echo "<h1 style='font-size: 32px; font-weight: 700; color: #212529; margin: 0 0 10px 0; line-height: 1.2; text-align: center;'>Test Assessment</h1>\n";
echo "<p style='font-size: 18px; color: #6c757d; margin: 0; line-height: 1.4; text-align: center; font-weight: 400;'>This is a test assessment description</p>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

// Test actual header rendering
echo "<h2>6. Testing Actual Header Rendering</h2>\n";

echo "<div style='background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
echo "<h3>Actual Header (should match inline styles above):</h3>\n";
echo "<div style='background: white; padding: 15px; border-radius: 5px;'>\n";
echo $header_html;
echo "</div>\n";
echo "</div>\n";

// Test CSS specificity issues
echo "<h2>7. CSS Specificity Analysis</h2>\n";

if ( $file_exists ) {
	$css_content = file_get_contents( $css_file_path );

	// Check for conflicting styles
	$conflicting_selectors = array(
		'.assessment-header'      => strpos( $css_content, '.assessment-header' ) !== false,
		'.assessment-title'       => strpos( $css_content, '.assessment-title' ) !== false,
		'.assessment-description' => strpos( $css_content, '.assessment-description' ) !== false,
	);

	echo "<p><strong>Potential conflicting selectors:</strong></p>\n";
	echo "<ul>\n";
	foreach ( $conflicting_selectors as $selector => $found ) {
		if ( $found ) {
			echo "<li style='color: orange;'>⚠️ <code>$selector</code> - This might conflict with header styles</li>\n";
		}
	}
	echo "</ul>\n";
}

echo "<h2>8. Recommendations</h2>\n";
echo "<ul>\n";
echo "<li>✅ <strong>Check CSS Loading:</strong> Ensure ennu-frontend-forms.css is being loaded</li>\n";
echo "<li>✅ <strong>Check CSS Specificity:</strong> Header styles might be overridden by other CSS</li>\n";
echo "<li>✅ <strong>Check Theme Conflicts:</strong> Theme CSS might be overriding plugin styles</li>\n";
echo "<li>✅ <strong>Add !important:</strong> Consider adding !important to critical header styles</li>\n";
echo "<li>✅ <strong>Check Browser Dev Tools:</strong> Inspect the header element to see applied styles</li>\n";
echo "</ul>\n";

echo "<p><strong>Test completed!</strong></p>\n";


