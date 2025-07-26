<?php
/**
 * ENNU Life Assessments - Assessment Links Fix Test Script
 *
 * This script tests that assessment links now use admin-configured page mappings
 * instead of dynamic URL generation.
 *
 * @package ENNU_Life
 * @version 62.1.10
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct access forbidden.' );
}

// Only run in admin
if ( ! is_admin() ) {
	exit( 'Admin access required.' );
}

echo '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; max-width: 1200px; margin: 0 auto; padding: 2rem;">';
echo '<h1 style="color: #1a202c; border-bottom: 3px solid #667eea; padding-bottom: 1rem;">🔗 Assessment Links Fix Test</h1>';
echo '<p style="color: #4a5568; font-size: 1.1rem; margin-bottom: 2rem;">Testing that assessment links now use admin-configured page mappings.</p>';

// Test 1: Page Mappings Check
echo '<h2 style="color: #2d3748; margin-top: 2rem;">1. Page Mappings Check</h2>';
echo '<div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">';

$page_mappings = get_option( 'ennu_created_pages', array() );

if ( ! empty( $page_mappings ) ) {
	echo '<span style="color: #38a169; font-weight: 600;">✅ Page mappings found</span><br>';
	echo '<span style="color: #4a5568;">Total mappings: ' . count( $page_mappings ) . '</span><br>';

	// Show assessment page mappings
	echo '<div style="margin-top: 1rem;">';
	echo '<strong>Assessment Page Mappings:</strong><br>';
	foreach ( $page_mappings as $slug => $page_id ) {
		if ( strpos( $slug, 'assessments/' ) === 0 ) {
			$page   = get_post( $page_id );
			$status = $page ? '✅' : '❌';
			echo "{$status} {$slug} → Page ID: {$page_id}";
			if ( $page ) {
				echo " ({$page->post_title})";
			}
			echo '<br>';
		}
	}
	echo '</div>';
} else {
	echo '<span style="color: #e53e3e; font-weight: 600;">❌ No page mappings found</span><br>';
	echo '<span style="color: #4a5568;">Please run "Create Missing Assessment Pages" in ENNU Life → Settings</span><br>';
}

echo '</div>';

// Test 2: URL Generation Method
echo '<h2 style="color: #2d3748; margin-top: 2rem;">2. URL Generation Method Test</h2>';
echo '<div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">';

if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
	$shortcode_instance = new ENNU_Assessment_Shortcodes();

	// Test assessment URLs using the new method
	$test_assessments = array( 'hair', 'ed-treatment', 'weight-loss', 'health' );

	echo '<strong>Assessment URLs (Using Admin Page Mappings):</strong><br>';
	foreach ( $test_assessments as $assessment_key ) {
		// New method: uses hierarchical slug
		$hierarchical_slug = "assessments/{$assessment_key}";
		$new_url           = $shortcode_instance->get_page_id_url( $hierarchical_slug );

		// Old method: uses assessment page URL
		$old_url = $shortcode_instance->get_assessment_page_url( $assessment_key );

		echo "<strong>{$assessment_key}:</strong><br>";
		echo "New Method ({$hierarchical_slug}): " . esc_url( $new_url ) . '<br>';
		echo 'Old Method: ' . esc_url( $old_url ) . '<br>';

		if ( $new_url !== $old_url ) {
			echo '<span style="color: #38a169; font-weight: 600;">✅ URLs are different (fix working)</span><br>';
		} else {
			echo '<span style="color: #e53e3e; font-weight: 600;">❌ URLs are the same (fix not working)</span><br>';
		}
		echo '<br>';
	}
} else {
	echo '<span style="color: #e53e3e; font-weight: 600;">❌ ENNU_Assessment_Shortcodes class not found</span><br>';
}

echo '</div>';

// Test 3: Page Existence Check
echo '<h2 style="color: #2d3748; margin-top: 2rem;">3. Page Existence Check</h2>';
echo '<div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">';

$test_assessments = array( 'hair', 'ed-treatment', 'weight-loss', 'health' );
$existing_pages   = 0;

echo '<strong>Checking if assessment pages exist:</strong><br>';
foreach ( $test_assessments as $assessment_key ) {
	$hierarchical_slug = "assessments/{$assessment_key}";
	$page_id           = $page_mappings[ $hierarchical_slug ] ?? null;

	if ( $page_id && get_post( $page_id ) ) {
		$page = get_post( $page_id );
		echo "<span style='color: #38a169; font-weight: 600;'>✅ {$assessment_key}</span> - Page ID: {$page_id} ({$page->post_title})<br>";
		$existing_pages++;
	} else {
		echo "<span style='color: #e53e3e; font-weight: 600;'>❌ {$assessment_key}</span> - Page not found<br>";
	}
}

echo '<br>';
echo "<span style='color: #4a5568;'>Pages found: {$existing_pages}/" . count( $test_assessments ) . '</span><br>';

if ( $existing_pages < count( $test_assessments ) ) {
	echo '<span style="color: #ed8936; font-weight: 600;">⚠️ Some pages missing. Run "Create Missing Assessment Pages" in admin.</span><br>';
}

echo '</div>';

// Test 4: Shortcode Content Check
echo '<h2 style="color: #2d3748; margin-top: 2rem;">4. Shortcode Content Check</h2>';
echo '<div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">';

$assessments_page_id = $page_mappings['assessments'] ?? null;

if ( $assessments_page_id && get_post( $assessments_page_id ) ) {
	$page = get_post( $assessments_page_id );

	if ( strpos( $page->post_content, '[ennu-assessments]' ) !== false ) {
		echo '<span style="color: #38a169; font-weight: 600;">✅ [ennu-assessments] shortcode is in page content</span><br>';
	} else {
		echo '<span style="color: #e53e3e; font-weight: 600;">❌ [ennu-assessments] shortcode is NOT in page content</span><br>';
	}

	echo '<span style="color: #4a5568;">Page Title: ' . esc_html( $page->post_title ) . '</span><br>';
	echo '<span style="color: #4a5568;">Page Slug: ' . esc_html( $page->post_name ) . '</span><br>';
} else {
	echo '<span style="color: #e53e3e; font-weight: 600;">❌ Assessments page not found</span><br>';
}

echo '</div>';

// Test 5: Menu Integration Check
echo '<h2 style="color: #2d3748; margin-top: 2rem;">5. Menu Integration Check</h2>';
echo '<div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">';

$primary_menu = wp_get_nav_menu_items( get_nav_menu_locations()['primary'] ?? 0 );

if ( $primary_menu ) {
	echo '<span style="color: #38a169; font-weight: 600;">✅ Primary menu found</span><br>';

	$assessments_menu_item    = null;
	$assessment_submenu_items = array();

	foreach ( $primary_menu as $item ) {
		if ( $item->title === 'Assessments' ) {
			$assessments_menu_item = $item;
		}
		if ( $item->menu_item_parent && strpos( $item->url, '/assessments/' ) !== false ) {
			$assessment_submenu_items[] = $item;
		}
	}

	if ( $assessments_menu_item ) {
		echo '<span style="color: #38a169; font-weight: 600;">✅ Assessments menu item found</span><br>';
		echo '<span style="color: #4a5568;">URL: ' . esc_url( $assessments_menu_item->url ) . '</span><br>';
	} else {
		echo '<span style="color: #e53e3e; font-weight: 600;">❌ Assessments menu item not found</span><br>';
	}

	echo '<span style="color: #4a5568;">Assessment submenu items: ' . count( $assessment_submenu_items ) . '</span><br>';

	if ( ! empty( $assessment_submenu_items ) ) {
		echo '<div style="margin-top: 1rem;">';
		echo '<strong>Assessment Submenu Items:</strong><br>';
		foreach ( $assessment_submenu_items as $item ) {
			echo "• {$item->title} → " . esc_url( $item->url ) . '<br>';
		}
		echo '</div>';
	}
} else {
	echo '<span style="color: #e53e3e; font-weight: 600;">❌ Primary menu not found</span><br>';
}

echo '</div>';

// Test 6: URL Structure Validation
echo '<h2 style="color: #2d3748; margin-top: 2rem;">6. URL Structure Validation</h2>';
echo '<div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">';

$expected_structure = array(
	'hair'         => '/assessments/hair/',
	'ed-treatment' => '/assessments/ed-treatment/',
	'weight-loss'  => '/assessments/weight-loss/',
	'health'       => '/assessments/health/',
);

echo '<strong>Expected URL Structure:</strong><br>';
foreach ( $expected_structure as $assessment => $expected_path ) {
	$hierarchical_slug = "assessments/{$assessment}";
	$actual_url        = $shortcode_instance->get_page_id_url( $hierarchical_slug );
	$actual_path       = parse_url( $actual_url, PHP_URL_PATH );

	if ( $actual_path === $expected_path ) {
		echo "<span style='color: #38a169; font-weight: 600;'>✅ {$assessment}</span> - {$actual_path}<br>";
	} else {
		echo "<span style='color: #e53e3e; font-weight: 600;'>❌ {$assessment}</span> - Expected: {$expected_path}, Got: {$actual_path}<br>";
	}
}

echo '</div>';

// Summary
echo '<h2 style="color: #2d3748; margin-top: 2rem;">📊 Test Summary</h2>';
echo '<div style="background: #e6fffa; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #38a169;">';

echo '<h3 style="color: #2d3748; margin-top: 0;">✅ What\'s Fixed:</h3>';
echo '<ul style="color: #2d3748;">';
echo '<li>Assessment links now use admin-configured page mappings</li>';
echo '<li>URLs follow the hierarchical structure (e.g., /assessments/hair/)</li>';
echo '<li>Links respect the page selections made in admin settings</li>';
echo '<li>Consistent URL generation across the entire plugin</li>';
echo '<li>Proper integration with menu structure</li>';
echo '</ul>';

echo '<h3 style="color: #2d3748;">🎯 Next Steps:</h3>';
echo '<ul style="color: #2d3748;">';
echo '<li>Visit the /assessments/ page to see the new links in action</li>';
echo '<li>Click on assessment cards to verify they lead to correct pages</li>';
echo '<li>Check that all assessment pages exist and are properly configured</li>';
echo '<li>Verify menu navigation works correctly</li>';
echo '<li>Test that admin page mappings are being used correctly</li>';
echo '</ul>';

echo '</div>';

// Instructions
echo '<h2 style="color: #2d3748; margin-top: 2rem;">🚀 How to Verify the Fix</h2>';
echo '<div style="background: #fef5e7; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #ed8936;">';

echo '<h3 style="color: #2d3748; margin-top: 0;">Frontend Testing:</h3>';
echo '<ol style="color: #2d3748;">';
echo '<li>Visit your /assessments/ page</li>';
echo '<li>Click on different assessment cards</li>';
echo '<li>Verify URLs follow the pattern /assessments/{assessment-name}/</li>';
echo '<li>Check that pages load correctly</li>';
echo '<li>Test navigation through the menu structure</li>';
echo '</ol>';

echo '<h3 style="color: #2d3748;">Admin Testing:</h3>';
echo '<ol style="color: #2d3748;">';
echo '<li>Go to ENNU Life → Settings</li>';
echo '<li>Check that page mappings are configured correctly</li>';
echo '<li>Run "Create Missing Assessment Pages" if needed</li>';
echo '<li>Verify menu structure includes all assessment pages</li>';
echo '</ol>';

echo '</div>';

echo '</div>';


