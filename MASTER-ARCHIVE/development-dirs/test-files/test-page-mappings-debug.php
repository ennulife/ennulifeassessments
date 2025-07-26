<?php
/**
 * Test Page Mappings Debug Script
 *
 * This script tests the page mappings to see why get_page_id_url is falling back to pretty permalinks
 */

// Load WordPress
require_once '../../../wp-load.php';

echo "<h1>🔍 PAGE MAPPINGS DEBUG</h1>\n";

// Check if shortcode handler exists
if ( ! class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
	echo "<p style='color: red;'>❌ ERROR: ENNU_Assessment_Shortcodes class not found!</p>\n";
	exit;
}

$shortcodes = new ENNU_Assessment_Shortcodes();

echo "<h2>📋 CURRENT PAGE MAPPINGS</h2>\n";

// Get current page mappings
$created_pages = get_option( 'ennu_created_pages', array() );
echo "<h3>ennu_created_pages Option:</h3>\n";
echo '<pre>' . print_r( $created_pages, true ) . "</pre>\n";

echo "<h2>🧪 TESTING get_page_id_url() METHOD</h2>\n";

// Test various page types
$test_pages = array(
	'weight-loss',
	'hair',
	'skin',
	'call',
	'assessments',
	'dashboard',
	'ennu-life-score',
);

foreach ( $test_pages as $page_type ) {
	$url = $shortcodes->get_page_id_url( $page_type );
	echo "<p><strong>$page_type:</strong> <a href='$url' target='_blank'>$url</a></p>\n";

	// Check if this page type exists in created_pages
	if ( isset( $created_pages[ $page_type ] ) ) {
		$page_id     = $created_pages[ $page_type ];
		$page_exists = get_post( $page_id );
		echo "<p style='margin-left: 20px; color: green;'>✅ Found in created_pages: ID $page_id (exists: " . ( $page_exists ? 'YES' : 'NO' ) . ")</p>\n";
	} else {
		echo "<p style='margin-left: 20px; color: red;'>❌ NOT found in created_pages - falling back to pretty permalink</p>\n";
	}
}

echo "<h2>🔧 RECOMMENDATIONS</h2>\n";

if ( empty( $created_pages ) ) {
	echo "<p style='color: red;'>❌ CRITICAL: No page mappings found! The ennu_created_pages option is empty.</p>\n";
	echo "<p>This means ALL links will fall back to pretty permalinks instead of using ?page_id= format.</p>\n";
} else {
	$missing_pages = array();
	foreach ( $test_pages as $page_type ) {
		if ( ! isset( $created_pages[ $page_type ] ) ) {
			$missing_pages[] = $page_type;
		}
	}

	if ( ! empty( $missing_pages ) ) {
		echo "<p style='color: orange;'>⚠️ Missing page mappings for: " . implode( ', ', $missing_pages ) . "</p>\n";
		echo "<p>These pages will fall back to pretty permalinks.</p>\n";
	} else {
		echo "<p style='color: green;'>✅ All test pages have mappings!</p>\n";
	}
}

echo "<h2>📝 SOLUTION</h2>\n";
echo "<p>The issue is that assessment pages (weight-loss, hair, skin, etc.) are not in the ennu_created_pages option.</p>\n";
echo "<p>We need to either:</p>\n";
echo "<ol>\n";
echo "<li>Add the assessment pages to the ennu_created_pages option, OR</li>\n";
echo "<li>Modify get_page_id_url() to handle assessment pages differently</li>\n";
echo "</ol>\n";
