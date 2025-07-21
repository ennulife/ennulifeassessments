<?php
/**
 * Test Script: Dashboard URL Verification
 *
 * This script tests what URLs are being generated for the dashboard buttons
 * to verify they're using the correct admin-configured URLs.
 *
 * Usage: Run this script in your WordPress environment
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	require_once '../../../wp-load.php';
}

echo "<h1>ENNU Life Dashboard URL Test</h1>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .success { background-color: #d4edda; border-color: #c3e6cb; }
    .error { background-color: #f8d7da; border-color: #f5c6cb; }
    .info { background-color: #d1ecf1; border-color: #bee5eb; }
    .test-result { margin: 10px 0; padding: 10px; border-radius: 3px; }
    .pass { background-color: #d4edda; }
    .fail { background-color: #f8d7da; }
    .warning { background-color: #fff3cd; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    .url-display { background: #e9ecef; padding: 8px; border-radius: 4px; font-family: monospace; word-break: break-all; }
</style>\n";

// Test 1: Check if user is logged in
echo "<div class='test-section'>\n";
echo "<h2>Test 1: User Authentication</h2>\n";

if ( is_user_logged_in() ) {
	$user = wp_get_current_user();
	echo "<div class='test-result pass'>✓ User is logged in: " . esc_html( $user->display_name ) . "</div>\n";
} else {
	echo "<div class='test-result fail'>✗ User is not logged in. Please log in to test dashboard functionality.</div>\n";
	echo "</div>\n";
	exit;
}
echo "</div>\n";

// Test 2: Check admin page configurations
echo "<div class='test-section'>\n";
echo "<h2>Test 2: Admin Page Configurations</h2>\n";

$created_pages = get_option( 'ennu_created_pages', array() );
if ( ! empty( $created_pages ) ) {
	echo "<div class='test-result pass'>✓ Admin page configurations found:</div>\n";
	echo '<pre>' . print_r( $created_pages, true ) . "</pre>\n";
} else {
	echo "<div class='test-result warning'>⚠ No admin page configurations found. This might be why URLs are incorrect.</div>\n";
}
echo "</div>\n";

// Test 3: Test URL generation for each assessment type
echo "<div class='test-section'>\n";
echo "<h2>Test 3: URL Generation for Assessment Types</h2>\n";

$shortcode_instance = new ENNU_Assessment_Shortcodes();
$user_id            = get_current_user_id();
$user_assessments   = $shortcode_instance->get_user_assessments_data( $user_id );

if ( ! empty( $user_assessments ) ) {
	echo "<div class='test-result pass'>✓ User assessments data retrieved successfully</div>\n";

	foreach ( $user_assessments as $assessment_key => $assessment ) {
		echo '<h3>Assessment: ' . esc_html( $assessment['label'] ) . " ({$assessment_key})</h3>\n";

		// Test assessment URL (for Retake button)
		echo "<div class='test-result info'>\n";
		echo "<strong>Assessment URL (Retake button):</strong><br>\n";
		echo "<div class='url-display'>" . esc_html( $assessment['url'] ) . "</div>\n";

		// Check if it's using admin-configured URL
		$expected_slug = str_replace( '_', '-', $assessment_key );
		$expected_url  = $shortcode_instance->get_page_id_url( $expected_slug );
		if ( $assessment['url'] === $expected_url ) {
			echo "<span style='color: green;'>✓ Using admin-configured URL</span>\n";
		} else {
			echo "<span style='color: red;'>✗ Not using admin-configured URL</span>\n";
			echo "<br>Expected: <div class='url-display'>" . esc_html( $expected_url ) . "</div>\n";
		}
		echo "</div>\n";

		// Test details URL (for History button)
		if ( $assessment['completed'] ) {
			echo "<div class='test-result info'>\n";
			echo "<strong>Details URL (History button):</strong><br>\n";
			echo "<div class='url-display'>" . esc_html( $assessment['details_url'] ) . "</div>\n";

			// Check if it's using admin-configured URL
			$expected_details_slug = str_replace( '_assessment', '-assessment-details', $assessment_key );
			$expected_details_url  = $shortcode_instance->get_page_id_url( $expected_details_slug );
			if ( $assessment['details_url'] === $expected_details_url ) {
				echo "<span style='color: green;'>✓ Using admin-configured URL</span>\n";
			} else {
				echo "<span style='color: red;'>✗ Not using admin-configured URL</span>\n";
				echo "<br>Expected: <div class='url-display'>" . esc_html( $expected_details_url ) . "</div>\n";
			}
			echo "</div>\n";
		}

		echo "<hr>\n";
	}
} else {
	echo "<div class='test-result fail'>✗ Could not retrieve user assessments data</div>\n";
}
echo "</div>\n";

// Test 4: Test Expert button URL
echo "<div class='test-section'>\n";
echo "<h2>Test 4: Expert Button URL</h2>\n";

$expert_url = $shortcode_instance->get_page_id_url( 'call' );
echo "<div class='test-result info'>\n";
echo "<strong>Expert Button URL:</strong><br>\n";
echo "<div class='url-display'>" . esc_html( $expert_url ) . "</div>\n";

if ( ! empty( $expert_url ) && $expert_url !== home_url( '/call/' ) ) {
	echo "<span style='color: green;'>✓ Using admin-configured URL</span>\n";
} else {
	echo "<span style='color: red;'>✗ Not using admin-configured URL or URL is default</span>\n";
}
echo "</div>\n";
echo "</div>\n";

// Test 5: Test other admin-configured URLs
echo "<div class='test-section'>\n";
echo "<h2>Test 5: Other Admin-Configured URLs</h2>\n";

$test_urls = array(
	'assessments'     => 'Assessments page',
	'dashboard'       => 'Dashboard page',
	'ennu-life-score' => 'ENNU Life Score page',
);

foreach ( $test_urls as $slug => $description ) {
	$url = $shortcode_instance->get_page_id_url( $slug );
	echo "<div class='test-result info'>\n";
	echo "<strong>{$description} ({$slug}):</strong><br>\n";
	echo "<div class='url-display'>" . esc_html( $url ) . "</div>\n";

	if ( ! empty( $url ) && $url !== home_url( "/{$slug}/" ) ) {
		echo "<span style='color: green;'>✓ Using admin-configured URL</span>\n";
	} else {
		echo "<span style='color: red;'>✗ Not using admin-configured URL or URL is default</span>\n";
	}
	echo "</div>\n";
}
echo "</div>\n";

// Test 6: Check if pages exist
echo "<div class='test-section'>\n";
echo "<h2>Test 6: Page Existence Check</h2>\n";

$pages_to_check = array();
foreach ( $user_assessments as $assessment_key => $assessment ) {
	$pages_to_check[] = array(
		'url'  => $assessment['url'],
		'name' => $assessment['label'] . ' (Assessment)',
		'type' => 'assessment',
	);

	if ( $assessment['completed'] ) {
		$pages_to_check[] = array(
			'url'  => $assessment['details_url'],
			'name' => $assessment['label'] . ' (Details)',
			'type' => 'details',
		);
	}
}

$pages_to_check[] = array(
	'url'  => $expert_url,
	'name' => 'Expert Consultation',
	'type' => 'consultation',
);

foreach ( $pages_to_check as $page ) {
	$response    = wp_remote_get( $page['url'], array( 'timeout' => 5 ) );
	$status_code = wp_remote_retrieve_response_code( $response );

	echo "<div class='test-result " . ( $status_code === 200 ? 'pass' : 'fail' ) . "'>\n";
	echo "<strong>{$page['name']}:</strong> ";

	if ( $status_code === 200 ) {
		echo "<span style='color: green;'>✓ Page exists (Status: {$status_code})</span>\n";
	} else {
		echo "<span style='color: red;'>✗ Page not found (Status: {$status_code})</span>\n";
	}

	echo "<br><div class='url-display'>" . esc_html( $page['url'] ) . "</div>\n";
	echo "</div>\n";
}
echo "</div>\n";

// Test 7: Check admin page creation
echo "<div class='test-section'>\n";
echo "<h2>Test 7: Admin Page Creation Status</h2>\n";

$admin_pages_created = get_option( 'ennu_admin_pages_created', false );
if ( $admin_pages_created ) {
	echo "<div class='test-result pass'>✓ Admin pages have been created</div>\n";
} else {
	echo "<div class='test-result warning'>⚠ Admin pages may not have been created yet</div>\n";
	echo "<p>If URLs are incorrect, you may need to:</p>\n";
	echo "<ol>\n";
	echo "<li>Go to the ENNU Life admin settings</li>\n";
	echo "<li>Run the page creation process</li>\n";
	echo "<li>Configure the page mappings</li>\n";
	echo "</ol>\n";
}
echo "</div>\n";

// Summary
echo "<div class='test-section success'>\n";
echo "<h2>URL Test Summary</h2>\n";
echo "<p>This test has verified the URL generation for dashboard buttons:</p>\n";
echo "<ul>\n";
echo "<li><strong>Expert Button:</strong> Uses <code>get_page_id_url('call')</code></li>\n";
echo "<li><strong>History Button:</strong> Uses assessment's <code>details_url</code> (admin-configured)</li>\n";
echo "<li><strong>Retake Button:</strong> Uses assessment's <code>url</code> (admin-configured)</li>\n";
echo "<li><strong>Recommendations/Breakdown:</strong> Toggle sections (no URLs)</li>\n";
echo "</ul>\n";
echo "<p><strong>If URLs are incorrect:</strong></p>\n";
echo "<ol>\n";
echo "<li>Check that admin pages have been created</li>\n";
echo "<li>Verify page mappings in admin settings</li>\n";
echo "<li>Ensure the correct pages are selected for each assessment type</li>\n";
echo "</ol>\n";
echo "</div>\n";

echo "<div class='test-section info'>\n";
echo "<h2>Manual Verification</h2>\n";
echo "<p>To manually verify the URLs:</p>\n";
echo "<ol>\n";
echo "<li>Visit your dashboard page</li>\n";
echo "<li>Right-click on each button and 'Copy Link Address'</li>\n";
echo "<li>Compare with the URLs shown above</li>\n";
echo "<li>Check if the pages actually exist and are accessible</li>\n";
echo "</ol>\n";
echo "</div>\n";


