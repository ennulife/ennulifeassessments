<?php
/**
 * Health Goals Issues Debug Script
 * Diagnoses problems with interactive health goals functionality
 *
 * @package ENNU_Life
 * @version 62.1.67
 */

// Load WordPress environment
require_once '../../../wp-load.php';

if ( ! defined( 'ABSPATH' ) ) {
	die( 'WordPress not loaded' );
}

echo '<h1>🔍 Health Goals Issues Diagnostic</h1>';
echo '<p><strong>Debugging the legendary system...</strong></p>';

// Test 1: Check if user is logged in
echo '<h2>👤 User Authentication</h2>';
$current_user = wp_get_current_user();
if ( $current_user->ID ) {
	echo "✅ User logged in: {$current_user->display_name} (ID: {$current_user->ID})<br>";
} else {
	echo '❌ No user logged in - this could cause AJAX issues<br>';
}

// Test 2: Check current user's health goals data
echo '<h2>🎯 User Health Goals Data</h2>';
if ( $current_user->ID ) {
	// Check both old and new meta keys
	$old_goals = get_user_meta( $current_user->ID, 'ennu_health_goals', true );
	$new_goals = get_user_meta( $current_user->ID, 'ennu_global_health_goals', true );

	echo '<strong>Old Meta Key (ennu_health_goals):</strong><br>';
	if ( ! empty( $old_goals ) ) {
		echo '&nbsp;&nbsp;Data found: ' . print_r( $old_goals, true ) . '<br>';
	} else {
		echo '&nbsp;&nbsp;No data found<br>';
	}

	echo '<strong>New Meta Key (ennu_global_health_goals):</strong><br>';
	if ( ! empty( $new_goals ) ) {
		echo '&nbsp;&nbsp;Data found: ' . print_r( $new_goals, true ) . '<br>';
	} else {
		echo '&nbsp;&nbsp;No data found<br>';
	}

	// Check if migration is needed
	if ( ! empty( $old_goals ) && empty( $new_goals ) ) {
		echo '⚠️ <strong>MIGRATION NEEDED:</strong> Data exists in old key but not new key<br>';
	}
}

// Test 3: Check if AJAX handlers are registered
echo '<h2>🔗 AJAX Handler Registration</h2>';
$ajax_actions = array(
	'wp_ajax_ennu_update_health_goals',
	'wp_ajax_nopriv_ennu_update_health_goals',
	'wp_ajax_ennu_toggle_health_goal',
	'wp_ajax_nopriv_ennu_toggle_health_goal',
);

foreach ( $ajax_actions as $action ) {
	if ( has_action( $action ) ) {
		echo "✅ $action - REGISTERED<br>";
	} else {
		echo "❌ $action - NOT REGISTERED<br>";
	}
}

// Test 4: Check if JavaScript file exists and is accessible
echo '<h2>📜 JavaScript Files</h2>';
$js_file = ENNU_LIFE_PLUGIN_PATH . 'assets/js/health-goals-manager.js';
if ( file_exists( $js_file ) ) {
	$file_size = filesize( $js_file );
	echo "✅ health-goals-manager.js exists ($file_size bytes)<br>";

	// Check if it's properly formed
	$js_content = file_get_contents( $js_file );
	if ( strpos( $js_content, 'HealthGoalsManager' ) !== false ) {
		echo '✅ HealthGoalsManager class found in JavaScript<br>';
	} else {
		echo '❌ HealthGoalsManager class NOT found in JavaScript<br>';
	}
} else {
	echo '❌ health-goals-manager.js NOT FOUND<br>';
}

// Test 5: Check dashboard shortcode usage
echo '<h2>📄 Dashboard Page Detection</h2>';
$dashboard_pages = get_posts(
	array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'meta_query'     => array(
			array(
				'key'     => '_wp_page_template',
				'compare' => 'EXISTS',
			),
		),
		's'              => 'ennu-user-dashboard',
		'posts_per_page' => -1,
	)
);

if ( empty( $dashboard_pages ) ) {
	// Try searching in post content
	$dashboard_pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			's'              => 'ennu-user-dashboard',
			'posts_per_page' => -1,
		)
	);
}

if ( ! empty( $dashboard_pages ) ) {
	echo '✅ Dashboard pages found:<br>';
	foreach ( $dashboard_pages as $page ) {
		echo "&nbsp;&nbsp;- {$page->post_title} (ID: {$page->ID})<br>";

		// Check if shortcode exists in content
		if ( has_shortcode( $page->post_content, 'ennu-user-dashboard' ) ) {
			echo '&nbsp;&nbsp;&nbsp;&nbsp;✅ Has ennu-user-dashboard shortcode<br>';
		} else {
			echo '&nbsp;&nbsp;&nbsp;&nbsp;❌ Missing ennu-user-dashboard shortcode<br>';
		}
	}
} else {
	echo '❌ No dashboard pages found with ennu-user-dashboard shortcode<br>';
}

// Test 6: Check health goals configuration
echo '<h2>⚙️ Health Goals Configuration</h2>';
$config_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/scoring/health-goals.php';
if ( file_exists( $config_file ) ) {
	try {
		$config = require $config_file;
		if ( isset( $config['goal_definitions'] ) ) {
			echo '✅ Configuration loaded with ' . count( $config['goal_definitions'] ) . ' goals<br>';

			// Show first few goals
			$goals = array_slice( $config['goal_definitions'], 0, 3, true );
			foreach ( $goals as $goal_id => $goal ) {
				echo "&nbsp;&nbsp;- $goal_id: {$goal['label']}<br>";
			}
		} else {
			echo '❌ Configuration missing goal_definitions<br>';
		}
	} catch ( Exception $e ) {
		echo '❌ Error loading configuration: ' . $e->getMessage() . '<br>';
	}
} else {
	echo '❌ Health goals configuration file not found<br>';
}

// Test 7: Test scoring system integration
echo '<h2>🧮 Scoring System Integration</h2>';
if ( class_exists( 'ENNU_Assessment_Scoring' ) ) {
	echo '✅ ENNU_Assessment_Scoring class exists<br>';

	// Check if it has the updated method
	if ( method_exists( 'ENNU_Assessment_Scoring', 'calculate_and_save_all_user_scores' ) ) {
		echo '✅ calculate_and_save_all_user_scores method exists<br>';
	} else {
		echo '❌ calculate_and_save_all_user_scores method missing<br>';
	}
} else {
	echo '❌ ENNU_Assessment_Scoring class not found<br>';
}

// Test 8: Check admin profile tab issues
echo '<h2>📝 Admin Profile Tab Issues</h2>';
if ( is_admin() ) {
	echo '✅ Running in admin context<br>';

	// Check if enhanced admin class exists
	if ( class_exists( 'ENNU_Enhanced_Admin' ) ) {
		echo '✅ ENNU_Enhanced_Admin class exists<br>';

		// Check if it has profile methods
		$methods         = get_class_methods( 'ENNU_Enhanced_Admin' );
		$profile_methods = array_filter(
			$methods,
			function( $method ) {
				return strpos( $method, 'profile' ) !== false || strpos( $method, 'tab' ) !== false;
			}
		);

		if ( ! empty( $profile_methods ) ) {
			echo '✅ Profile-related methods found: ' . implode( ', ', $profile_methods ) . '<br>';
		} else {
			echo '⚠️ No profile-related methods found<br>';
		}
	} else {
		echo '❌ ENNU_Enhanced_Admin class not found<br>';
	}
} else {
	echo "⚠️ Not in admin context - profile tabs won't be visible<br>";
}

// Test 9: Check for JavaScript errors (basic check)
echo '<h2>🐛 Potential JavaScript Issues</h2>';
if ( file_exists( $js_file ) ) {
	$js_content = file_get_contents( $js_file );

	// Check for common issues
	$issues = array();

	if ( strpos( $js_content, 'ennuHealthGoalsAjax' ) === false ) {
		$issues[] = 'Missing ennuHealthGoalsAjax reference';
	}

	if ( strpos( $js_content, 'wp_ajax_ennu_update_health_goals' ) === false ) {
		$issues[] = 'Missing AJAX action reference';
	}

	if ( strpos( $js_content, 'jQuery' ) !== false && strpos( $js_content, '$' ) !== false ) {
		$issues[] = 'Potential jQuery conflict (uses both jQuery and $)';
	}

	if ( ! empty( $issues ) ) {
		echo '⚠️ Potential JavaScript issues found:<br>';
		foreach ( $issues as $issue ) {
			echo "&nbsp;&nbsp;- $issue<br>";
		}
	} else {
		echo '✅ No obvious JavaScript issues detected<br>';
	}
}

// Test 10: Check WordPress environment
echo '<h2>🌍 WordPress Environment</h2>';
echo '<strong>WordPress Version:</strong> ' . get_bloginfo( 'version' ) . '<br>';
echo '<strong>PHP Version:</strong> ' . PHP_VERSION . '<br>';
echo '<strong>Plugin Version:</strong> ' . ( defined( 'ENNU_LIFE_VERSION' ) ? ENNU_LIFE_VERSION : 'NOT DEFINED' ) . '<br>';
echo '<strong>Admin URL:</strong> ' . admin_url( 'admin-ajax.php' ) . '<br>';
echo '<strong>Plugin URL:</strong> ' . ( defined( 'ENNU_LIFE_PLUGIN_URL' ) ? ENNU_LIFE_PLUGIN_URL : 'NOT DEFINED' ) . '<br>';

// Test 11: Check current page context
echo '<h2>📍 Current Page Context</h2>';
global $post;
if ( $post ) {
	echo "<strong>Current Post:</strong> {$post->post_title} (ID: {$post->ID})<br>";
	echo "<strong>Post Type:</strong> {$post->post_type}<br>";

	if ( has_shortcode( $post->post_content, 'ennu-user-dashboard' ) ) {
		echo '✅ Current page has ennu-user-dashboard shortcode<br>';
	} else {
		echo '❌ Current page does NOT have ennu-user-dashboard shortcode<br>';
	}
} else {
	echo '⚠️ No current post context<br>';
}

// Test 12: Recommendations
echo '<h2>💡 Diagnostic Recommendations</h2>';
echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;'>";
echo '<strong>🔧 RECOMMENDED FIXES:</strong><br><br>';

// Migration check
if ( $current_user->ID && ! empty( $old_goals ) && empty( $new_goals ) ) {
	echo '1. <strong>RUN MIGRATION:</strong> Go to WordPress Admin → Tools → ENNU Migration<br>';
}

// AJAX registration check
$missing_ajax = 0;
foreach ( $ajax_actions as $action ) {
	if ( ! has_action( $action ) ) {
		$missing_ajax++;
	}
}
if ( $missing_ajax > 0 ) {
	echo "2. <strong>FIX AJAX REGISTRATION:</strong> $missing_ajax AJAX handlers are not registered<br>";
}

// Dashboard page check
if ( empty( $dashboard_pages ) ) {
	echo '3. <strong>CREATE DASHBOARD PAGE:</strong> No page found with [ennu-user-dashboard] shortcode<br>';
}

// JavaScript file check
if ( ! file_exists( $js_file ) ) {
	echo '4. <strong>RESTORE JAVASCRIPT:</strong> health-goals-manager.js file is missing<br>';
}

echo '<br><strong>🚀 NEXT STEPS:</strong><br>';
echo '1. Address the issues identified above<br>';
echo '2. Clear any caching plugins<br>';
echo '3. Test on the actual dashboard page<br>';
echo '4. Check browser console for JavaScript errors<br>';
echo '</div>';

echo '<p><em>Diagnostic completed: ' . current_time( 'Y-m-d H:i:s' ) . '</em></p>';


