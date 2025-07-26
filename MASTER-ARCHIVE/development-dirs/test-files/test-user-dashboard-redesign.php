<?php
/**
 * ENNU Life Assessments - User Dashboard Redesign Test Script
 *
 * This script tests the comprehensive user dashboard redesign including
 * assessment cards, charts, progress tracking, and responsive design.
 *
 * @package ENNU_Life
 * @version 62.1.11
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
echo '<h1 style="color: #1a202c; border-bottom: 3px solid #667eea; padding-bottom: 1rem;">🎨 User Dashboard Redesign Test</h1>';
echo '<p style="color: #4a5568; font-size: 1.1rem; margin-bottom: 2rem;">Testing the comprehensive user dashboard redesign with modern design and enhanced functionality.</p>';

// Test 1: Check if user dashboard shortcode is registered
echo '<div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">';
echo '<h2 style="color: #2d3748; margin-top: 0;">✅ Test 1: Shortcode Registration</h2>';

$shortcodes = shortcode_tags();
if ( isset( $shortcodes['ennu-user-dashboard'] ) ) {
	echo '<p style="color: #38a169; margin: 0;">✓ <strong>ennu-user-dashboard</strong> shortcode is properly registered</p>';
} else {
	echo '<p style="color: #e53e3e; margin: 0;">✗ <strong>ennu-user-dashboard</strong> shortcode is NOT registered</p>';
}
echo '</div>';

// Test 2: Check if dashboard template exists
echo '<div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">';
echo '<h2 style="color: #2d3748; margin-top: 0;">✅ Test 2: Template Files</h2>';

$template_path = ENNU_LIFE_PLUGIN_PATH . 'templates/user-dashboard.php';
if ( file_exists( $template_path ) ) {
	echo '<p style="color: #38a169; margin: 0;">✓ <strong>user-dashboard.php</strong> template exists</p>';

	$template_content = file_get_contents( $template_path );
	if ( strpos( $template_content, 'dashboard-welcome-section' ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Welcome section is implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Welcome section is missing</p>';
	}

	if ( strpos( $template_content, 'assessment-cards-grid' ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Assessment cards grid is implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Assessment cards grid is missing</p>';
	}

	if ( strpos( $template_content, 'charts-section' ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Charts section is implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Charts section is missing</p>';
	}

	if ( strpos( $template_content, 'quick-actions-section' ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Quick actions section is implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Quick actions section is missing</p>';
	}
} else {
	echo '<p style="color: #e53e3e; margin: 0;">✗ <strong>user-dashboard.php</strong> template does not exist</p>';
}
echo '</div>';

// Test 3: Check if CSS file exists and has new styles
echo '<div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">';
echo '<h2 style="color: #2d3748; margin-top: 0;">✅ Test 3: CSS Styling</h2>';

$css_path = ENNU_LIFE_PLUGIN_PATH . 'assets/css/user-dashboard.css';
if ( file_exists( $css_path ) ) {
	echo '<p style="color: #38a169; margin: 0;">✓ <strong>user-dashboard.css</strong> file exists</p>';

	$css_content = file_get_contents( $css_path );
	if ( strpos( $css_content, '.dashboard-welcome-section' ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Welcome section styles are implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Welcome section styles are missing</p>';
	}

	if ( strpos( $css_content, '.assessment-cards-grid' ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Assessment cards grid styles are implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Assessment cards grid styles are missing</p>';
	}

	if ( strpos( $css_content, '.charts-section' ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Charts section styles are implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Charts section styles are missing</p>';
	}

	if ( strpos( $css_content, '.quick-actions-grid' ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Quick actions grid styles are implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Quick actions grid styles are missing</p>';
	}

	if ( strpos( $css_content, '@media (max-width: 768px)' ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Mobile responsive styles are implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Mobile responsive styles are missing</p>';
	}
} else {
	echo '<p style="color: #e53e3e; margin: 0;">✗ <strong>user-dashboard.css</strong> file does not exist</p>';
}
echo '</div>';

// Test 4: Check if JavaScript file exists and has new functionality
echo '<div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">';
echo '<h2 style="color: #2d3748; margin-top: 0;">✅ Test 4: JavaScript Functionality</h2>';

$js_path = ENNU_LIFE_PLUGIN_PATH . 'assets/js/user-dashboard.js';
if ( file_exists( $js_path ) ) {
	echo '<p style="color: #38a169; margin: 0;">✓ <strong>user-dashboard.js</strong> file exists</p>';

	$js_content = file_get_contents( $js_path );
	if ( strpos( $js_content, 'scoreHistoryChart' ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Score history chart functionality is implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Score history chart functionality is missing</p>';
	}

	if ( strpos( $js_content, 'bmiHistoryChart' ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ BMI history chart functionality is implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ BMI history chart functionality is missing</p>';
	}

	if ( strpos( $js_content, 'initAssessmentCardInteractions' ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Assessment card interactions are implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Assessment card interactions are missing</p>';
	}

	if ( strpos( $js_content, 'initProgressBarAnimation' ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Progress bar animation is implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Progress bar animation is missing</p>';
	}
} else {
	echo '<p style="color: #e53e3e; margin: 0;">✗ <strong>user-dashboard.js</strong> file does not exist</p>';
}
echo '</div>';

// Test 5: Check if dashboard page exists and uses shortcode
echo '<div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">';
echo '<h2 style="color: #2d3748; margin-top: 0;">✅ Test 5: Dashboard Page Configuration</h2>';

$dashboard_page = get_page_by_path( 'dashboard' );
if ( $dashboard_page ) {
	echo '<p style="color: #38a169; margin: 0;">✓ Dashboard page exists (ID: ' . $dashboard_page->ID . ')</p>';

	if ( strpos( $dashboard_page->post_content, '[ennu-user-dashboard]' ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ Dashboard page uses the correct shortcode</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ Dashboard page does not use the correct shortcode</p>';
		echo '<p style="color: #e53e3e; margin: 0;">Current content: ' . esc_html( substr( $dashboard_page->post_content, 0, 100 ) ) . '...</p>';
	}
} else {
	echo '<p style="color: #e53e3e; margin: 0;">✗ Dashboard page does not exist</p>';
}
echo '</div>';

// Test 6: Check if required assets are enqueued
echo '<div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">';
echo '<h2 style="color: #2d3748; margin-top: 0;">✅ Test 6: Asset Enqueuing</h2>';

// Check if the shortcode class exists and has enqueue methods
if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
	echo '<p style="color: #38a169; margin: 0;">✓ ENNU_Assessment_Shortcodes class exists</p>';

	// Check if render_user_dashboard method exists
	$shortcode_instance = new ENNU_Assessment_Shortcodes();
	if ( method_exists( $shortcode_instance, 'render_user_dashboard' ) ) {
		echo '<p style="color: #38a169; margin: 0;">✓ render_user_dashboard method exists</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ render_user_dashboard method is missing</p>';
	}
} else {
	echo '<p style="color: #e53e3e; margin: 0;">✗ ENNU_Assessment_Shortcodes class does not exist</p>';
}
echo '</div>';

// Test 7: Check responsive design features
echo '<div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">';
echo '<h2 style="color: #2d3748; margin-top: 0;">✅ Test 7: Responsive Design Features</h2>';

$css_content         = file_get_contents( $css_path );
$responsive_features = array(
	'grid-template-columns: repeat(auto-fit, minmax(350px, 1fr))' => 'Assessment cards responsive grid',
	'grid-template-columns: repeat(auto-fit, minmax(400px, 1fr))' => 'Charts responsive grid',
	'grid-template-columns: repeat(auto-fit, minmax(250px, 1fr))' => 'Quick actions responsive grid',
	'@media (max-width: 768px)'  => 'Mobile breakpoint',
	'@media (max-width: 480px)'  => 'Small mobile breakpoint',
	'grid-template-columns: 1fr' => 'Single column mobile layout',
);

foreach ( $responsive_features as $feature => $description ) {
	if ( strpos( $css_content, $feature ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ ' . $description . ' is implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ ' . $description . ' is missing</p>';
	}
}
echo '</div>';

// Test 8: Check animation features
echo '<div style="background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">';
echo '<h2 style="color: #2d3748; margin-top: 0;">✅ Test 8: Animation Features</h2>';

$animation_features = array(
	'@keyframes fadeInUp'                    => 'Fade in up animation',
	'animation: fadeInUp 0.6s ease forwards' => 'Card animation application',
	'animation-delay: 0.1s'                  => 'Staggered animation delays',
	'transition: all 0.3s ease'              => 'Smooth transitions',
	'transform: translateY(-2px)'            => 'Hover animations',
);

foreach ( $animation_features as $feature => $description ) {
	if ( strpos( $css_content, $feature ) !== false ) {
		echo '<p style="color: #38a169; margin: 0;">✓ ' . $description . ' is implemented</p>';
	} else {
		echo '<p style="color: #e53e3e; margin: 0;">✗ ' . $description . ' is missing</p>';
	}
}
echo '</div>';

// Summary
echo '<div style="background: #e6fffa; border: 1px solid #81e6d9; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">';
echo '<h2 style="color: #2d3748; margin-top: 0;">📋 Test Summary</h2>';
echo '<p style="color: #2d3748; margin: 0;"><strong>Dashboard Redesign Features Tested:</strong></p>';
echo '<ul style="color: #2d3748; margin: 10px 0;">';
echo '<li>Shortcode registration and functionality</li>';
echo '<li>Template file structure and content</li>';
echo '<li>CSS styling and responsive design</li>';
echo '<li>JavaScript functionality and chart integration</li>';
echo '<li>Dashboard page configuration</li>';
echo '<li>Asset enqueuing and dependencies</li>';
echo '<li>Responsive design breakpoints</li>';
echo '<li>Animation and interaction features</li>';
echo '</ul>';
echo '</div>';

// Instructions
echo '<div style="background: #fff5f5; border: 1px solid #feb2b2; border-radius: 8px; padding: 1.5rem;">';
echo '<h2 style="color: #2d3748; margin-top: 0;">🚀 Next Steps</h2>';
echo '<p style="color: #2d3748; margin: 0;"><strong>To test the dashboard:</strong></p>';
echo '<ol style="color: #2d3748; margin: 10px 0;">';
echo '<li>Visit the dashboard page on your site</li>';
echo '<li>Test the responsive design on different screen sizes</li>';
echo '<li>Verify assessment cards display correctly</li>';
echo '<li>Check that charts render properly (if data exists)</li>';
echo '<li>Test quick action links and navigation</li>';
echo '<li>Verify animations and hover effects work</li>';
echo '<li>Test on mobile devices for responsive behavior</li>';
echo '</ol>';
echo '</div>';

echo '</div>';


