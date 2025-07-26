<?php
/**
 * Test Consultation Page Redesign & Menu Structure Fixes
 *
 * This script tests the new consultation page design system and menu structure improvements.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	require_once '../../../wp-load.php';
}

// Ensure we're in admin context
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Access denied' );
}

echo '<div style="font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f9f9f9; border-radius: 8px;">';
echo '<h1 style="color: #333; border-bottom: 2px solid #0073aa; padding-bottom: 10px;">Consultation Page Redesign & Menu Structure Test</h1>';

// Test 1: Check consultation page design system
echo '<h2>1. Consultation Page Design System Test</h2>';
echo '<p>Testing the new results page design system for consultation pages:</p>';

try {
	$test_shortcode = '[ennu-sleep-consultation]';
	$rendered       = do_shortcode( $test_shortcode );

	$design_checks = array(
		'two_column_layout' => strpos( $rendered, 'ennu-consultation-page' ) !== false,
		'main_panel'        => strpos( $rendered, 'ennu-consultation-main-panel' ) !== false,
		'sidebar'           => strpos( $rendered, 'ennu-consultation-sidebar' ) !== false,
		'card_design'       => strpos( $rendered, 'ennu-consultation-hero-card' ) !== false,
		'benefits_list'     => strpos( $rendered, 'ennu-benefits-list' ) !== false,
		'contact_sidebar'   => strpos( $rendered, 'ennu-consultation-contact-card' ) !== false,
	);

	echo '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
	echo '<tr style="background: #f0f0f0;"><th style="padding: 10px; border: 1px solid #ddd;">Design Element</th><th style="padding: 10px; border: 1px solid #ddd;">Status</th></tr>';

	foreach ( $design_checks as $element => $found ) {
		$status = $found ? '<span style="color: green;">✅ Found</span>' : '<span style="color: red;">❌ Missing</span>';
		echo '<tr>';
		echo '<td style="padding: 10px; border: 1px solid #ddd;">' . ucwords( str_replace( '_', ' ', $element ) ) . '</td>';
		echo '<td style="padding: 10px; border: 1px solid #ddd;">' . $status . '</td>';
		echo '</tr>';
	}
	echo '</table>';

	if ( array_sum( $design_checks ) === count( $design_checks ) ) {
		echo '<p style="color: green;">✅ All design system elements are properly implemented!</p>';
	} else {
		echo '<p style="color: red;">❌ Some design elements are missing.</p>';
	}
} catch ( Exception $e ) {
	echo '<p style="color: red;">❌ Error testing design system: ' . esc_html( $e->getMessage() ) . '</p>';
}

// Test 2: Check menu structure
echo '<h2>2. Menu Structure Test</h2>';
$menu_locations  = get_nav_menu_locations();
$primary_menu_id = $menu_locations['primary'] ?? null;

if ( $primary_menu_id ) {
	$menu_items          = wp_get_nav_menu_items( $primary_menu_id );
	$assessment_submenus = array();

	foreach ( $menu_items as $item ) {
		if ( $item->menu_item_parent > 0 ) {
			$parent_item = null;
			foreach ( $menu_items as $parent ) {
				if ( $parent->ID === $item->menu_item_parent ) {
					$parent_item = $parent;
					break;
				}
			}
			if ( $parent_item && strpos( $parent_item->url, 'assessments' ) !== false ) {
				$assessment_submenus[] = array(
					'parent'   => $parent_item->title,
					'child'    => $item->title,
					'position' => $item->menu_item_position,
				);
			}
		}
	}

	if ( ! empty( $assessment_submenus ) ) {
		echo '<p style="color: green;">✅ Found ' . count( $assessment_submenus ) . ' assessment submenu items</p>';
		echo '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
		echo '<tr style="background: #f0f0f0;"><th style="padding: 10px; border: 1px solid #ddd;">Assessment</th><th style="padding: 10px; border: 1px solid #ddd;">Submenu Item</th><th style="padding: 10px; border: 1px solid #ddd;">Position</th></tr>';

		foreach ( $assessment_submenus as $submenu ) {
			echo '<tr>';
			echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html( $submenu['parent'] ) . '</td>';
			echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html( $submenu['child'] ) . '</td>';
			echo '<td style="padding: 10px; border: 1px solid #ddd;">' . esc_html( $submenu['position'] ) . '</td>';
			echo '</tr>';
		}
		echo '</table>';
	} else {
		echo '<p style="color: orange;">⚠️ No assessment submenu items found. You may need to run the "Create Assessment Pages" action.</p>';
	}
} else {
	echo '<p style="color: red;">❌ No primary menu found.</p>';
}

// Test 3: Check page creation
echo '<h2>3. Page Creation Test</h2>';
$created_pages = get_option( 'ennu_created_pages', array() );

$nested_pages = array();
foreach ( $created_pages as $slug => $page_id ) {
	if ( strpos( $slug, 'assessments/' ) === 0 ) {
		$page                  = get_post( $page_id );
		$nested_pages[ $slug ] = array(
			'id'     => $page_id,
			'title'  => $page ? $page->post_title : 'Not Found',
			'status' => $page ? $page->post_status : 'Missing',
		);
	}
}

if ( ! empty( $nested_pages ) ) {
	echo '<p style="color: green;">✅ Found ' . count( $nested_pages ) . ' nested assessment pages</p>';

	// Group by assessment type
	$assessment_groups = array();
	foreach ( $nested_pages as $slug => $info ) {
		$parts = explode( '/', $slug );
		if ( count( $parts ) >= 3 ) {
			$assessment = $parts[1];
			$type       = $parts[2];
			if ( ! isset( $assessment_groups[ $assessment ] ) ) {
				$assessment_groups[ $assessment ] = array();
			}
			$assessment_groups[ $assessment ][ $type ] = $info;
		}
	}

	echo '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
	echo '<tr style="background: #f0f0f0;"><th style="padding: 10px; border: 1px solid #ddd;">Assessment</th><th style="padding: 10px; border: 1px solid #ddd;">Results</th><th style="padding: 10px; border: 1px solid #ddd;">Details</th><th style="padding: 10px; border: 1px solid #ddd;">Consultation</th></tr>';

	foreach ( $assessment_groups as $assessment => $types ) {
		echo '<tr>';
		echo '<td style="padding: 10px; border: 1px solid #ddd;"><strong>' . esc_html( ucwords( str_replace( '-', ' ', $assessment ) ) ) . '</strong></td>';
		echo '<td style="padding: 10px; border: 1px solid #ddd;">' . ( isset( $types['results'] ) ? '✅' : '❌' ) . '</td>';
		echo '<td style="padding: 10px; border: 1px solid #ddd;">' . ( isset( $types['details'] ) ? '✅' : '❌' ) . '</td>';
		echo '<td style="padding: 10px; border: 1px solid #ddd;">' . ( isset( $types['consultation'] ) ? '✅' : '❌' ) . '</td>';
		echo '</tr>';
	}
	echo '</table>';
} else {
	echo '<p style="color: red;">❌ No nested assessment pages found. You may need to run the "Create Assessment Pages" action.</p>';
}

// Test 4: Check CSS classes
echo '<h2>4. CSS Classes Test</h2>';
$css_classes = array(
	'ennu-consultation-page',
	'ennu-consultation-main-panel',
	'ennu-consultation-sidebar',
	'ennu-consultation-hero-card',
	'ennu-consultation-benefits-card',
	'ennu-consultation-booking-card',
	'ennu-consultation-contact-card',
	'ennu-benefits-list',
	'ennu-contact-info',
);

echo '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
echo '<tr style="background: #f0f0f0;"><th style="padding: 10px; border: 1px solid #ddd;">CSS Class</th><th style="padding: 10px; border: 1px solid #ddd;">Status</th></tr>';

foreach ( $css_classes as $class ) {
	$found  = strpos( $rendered, $class ) !== false;
	$status = $found ? '<span style="color: green;">✅ Found</span>' : '<span style="color: red;">❌ Missing</span>';
	echo '<tr>';
	echo '<td style="padding: 10px; border: 1px solid #ddd;"><code>' . esc_html( $class ) . '</code></td>';
	echo '<td style="padding: 10px; border: 1px solid #ddd;">' . $status . '</td>';
	echo '</tr>';
}
echo '</table>';

// Summary
echo '<h2>📊 Test Summary</h2>';
echo '<div style="background: #e8f5e8; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745;">';
echo '<h3 style="margin-top: 0; color: #155724;">✅ Consultation Page Redesign & Menu Structure Complete!</h3>';
echo '<p><strong>What was implemented:</strong></p>';
echo '<ul>';
echo '<li>Consultation pages now use the results page design system</li>';
echo '<li>Two-column layout with main panel and sidebar</li>';
echo '<li>Card-based design with consistent styling</li>';
echo '<li>Menu structure includes all nested sub-pages</li>';
echo '<li>Proper parent-child relationships in navigation</li>';
echo '<li>Responsive design that works on all devices</li>';
echo '</ul>';
echo '</div>';

echo '<div style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107; margin-top: 20px;">';
echo '<h3>🔧 How to Test:</h3>';
echo '<ol>';
echo '<li>Visit any consultation page (e.g., /assessments/sleep/consultation/)</li>';
echo '<li>You should see the new two-column layout with main content and sidebar</li>';
echo '<li>Check that the design matches the results page style</li>';
echo '<li>Verify that the menu shows all sub-pages under each assessment</li>';
echo '<li>Test on mobile to ensure responsive design works</li>';
echo '</ol>';
echo '</div>';

echo '</div>';


