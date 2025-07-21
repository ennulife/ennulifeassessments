<?php
/**
 * Quick Functional Test Script
 *
 * This script tests the basic functionality of the ENNU Life Assessments plugin
 * to identify what's working and what needs immediate fixes.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	require_once '../../../wp-load.php';
}

// Test results storage
$test_results = array();

echo '<h1>ENNU Life Assessments - Quick Functional Test</h1>';
echo "<p>Testing basic functionality to identify what's working and what needs fixes.</p>";

// Test 1: Check if plugin is active
function test_plugin_activation() {
	global $test_results;

	echo '<h2>1. Plugin Activation Test</h2>';

	if ( is_plugin_active( 'ennu-life-assessments/ennu-life-plugin.php' ) ) {
		echo "<p style='color: green;'>✅ Plugin is active</p>";
		$test_results['plugin_active'] = true;
	} else {
		echo "<p style='color: red;'>❌ Plugin is not active</p>";
		$test_results['plugin_active'] = false;
	}
}

// Test 2: Check database tables
function test_database_tables() {
	global $wpdb, $test_results;

	echo '<h2>2. Database Tables Test</h2>';

	$tables = array(
		$wpdb->prefix . 'ennu_assessments',
		$wpdb->prefix . 'ennu_scores',
		$wpdb->prefix . 'ennu_health_goals',
	);

	foreach ( $tables as $table ) {
		$exists = $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) == $table;
		if ( $exists ) {
			echo "<p style='color: green;'>✅ Table exists: $table</p>";
			$test_results[ 'table_' . str_replace( $wpdb->prefix, '', $table ) ] = true;
		} else {
			echo "<p style='color: red;'>❌ Table missing: $table</p>";
			$test_results[ 'table_' . str_replace( $wpdb->prefix, '', $table ) ] = false;
		}
	}
}

// Test 3: Check shortcode registration
function test_shortcode_registration() {
	global $test_results;

	echo '<h2>3. Shortcode Registration Test</h2>';

	$shortcodes = array(
		'ennu-hair',
		'ennu-ed-treatment',
		'ennu-health-optimization',
	);

	foreach ( $shortcodes as $shortcode ) {
		if ( shortcode_exists( $shortcode ) ) {
			echo "<p style='color: green;'>✅ Shortcode registered: [$shortcode]</p>";
			$test_results[ 'shortcode_' . str_replace( '-', '_', $shortcode ) ] = true;
		} else {
			echo "<p style='color: red;'>❌ Shortcode not registered: [$shortcode]</p>";
			$test_results[ 'shortcode_' . str_replace( '-', '_', $shortcode ) ] = false;
		}
	}
}

// Test 4: Check AJAX handlers
function test_ajax_handlers() {
	global $test_results;

	echo '<h2>4. AJAX Handlers Test</h2>';

	$ajax_actions = array(
		'ennu_submit_assessment',
		'ennu_update_health_goals',
		'ennu_check_email',
	);

	foreach ( $ajax_actions as $action ) {
		if ( has_action( "wp_ajax_$action" ) || has_action( "wp_ajax_nopriv_$action" ) ) {
			echo "<p style='color: green;'>✅ AJAX handler registered: $action</p>";
			$test_results[ 'ajax_' . $action ] = true;
		} else {
			echo "<p style='color: red;'>❌ AJAX handler missing: $action</p>";
			$test_results[ 'ajax_' . $action ] = false;
		}
	}
}

// Test 5: Check assessment submission (simulated)
function test_assessment_submission() {
	global $wpdb, $test_results;

	echo '<h2>5. Assessment Submission Test</h2>';

	// Create test user if needed
	$test_user = get_user_by( 'email', 'test@ennu.com' );
	if ( ! $test_user ) {
		$user_id = wp_create_user( 'test_user', 'testpass123', 'test@ennu.com' );
	} else {
		$user_id = $test_user->ID;
	}

	// Test data
	$test_data = array(
		'user_id'    => $user_id,
		'type'       => 'health_optimization',
		'data'       => json_encode(
			array(
				'energy_levels' => '4',
				'sleep_quality' => '3',
				'stress_levels' => '2',
			)
		),
		'status'     => 'active',
		'created_at' => current_time( 'mysql' ),
	);

	// Try to insert test assessment
	$result = $wpdb->insert(
		$wpdb->prefix . 'ennu_assessments',
		$test_data,
		array( '%d', '%s', '%s', '%s', '%s' )
	);

	if ( $result !== false ) {
		$assessment_id = $wpdb->insert_id;
		echo "<p style='color: green;'>✅ Assessment submission works (ID: $assessment_id)</p>";
		$test_results['assessment_submission'] = true;

		// Clean up test data
		$wpdb->delete( $wpdb->prefix . 'ennu_assessments', array( 'id' => $assessment_id ) );
	} else {
		echo "<p style='color: red;'>❌ Assessment submission failed: " . $wpdb->last_error . '</p>';
		$test_results['assessment_submission'] = false;
	}
}

// Test 6: Check dashboard functionality
function test_dashboard_functionality() {
	global $wpdb, $test_results;

	echo '<h2>6. Dashboard Functionality Test</h2>';

	// Test user data retrieval
	$test_user = get_user_by( 'email', 'test@ennu.com' );
	if ( $test_user ) {
		$user_id = $test_user->ID;

		// Test getting user assessments
		$assessments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}ennu_assessments WHERE user_id = %d",
				$user_id
			)
		);

		if ( $assessments !== false ) {
			echo "<p style='color: green;'>✅ Dashboard data retrieval works</p>";
			$test_results['dashboard_data'] = true;
		} else {
			echo "<p style='color: red;'>❌ Dashboard data retrieval failed</p>";
			$test_results['dashboard_data'] = false;
		}
	} else {
		echo "<p style='color: orange;'>⚠️ No test user found for dashboard test</p>";
		$test_results['dashboard_data'] = 'skipped';
	}
}

// Test 7: Check health goals functionality
function test_health_goals() {
	global $wpdb, $test_results;

	echo '<h2>7. Health Goals Test</h2>';

	$test_user = get_user_by( 'email', 'test@ennu.com' );
	if ( $test_user ) {
		$user_id = $test_user->ID;

		// Test goal creation
		$goal_data = array(
			'user_id'    => $user_id,
			'goal_type'  => 'exercise',
			'goal_text'  => 'Test goal',
			'status'     => 'active',
			'created_at' => current_time( 'mysql' ),
		);

		$result = $wpdb->insert(
			$wpdb->prefix . 'ennu_health_goals',
			$goal_data,
			array( '%d', '%s', '%s', '%s', '%s' )
		);

		if ( $result !== false ) {
			$goal_id = $wpdb->insert_id;
			echo "<p style='color: green;'>✅ Health goals creation works (ID: $goal_id)</p>";
			$test_results['health_goals'] = true;

			// Clean up test data
			$wpdb->delete( $wpdb->prefix . 'ennu_health_goals', array( 'id' => $goal_id ) );
		} else {
			echo "<p style='color: red;'>❌ Health goals creation failed: " . $wpdb->last_error . '</p>';
			$test_results['health_goals'] = false;
		}
	} else {
		echo "<p style='color: orange;'>⚠️ No test user found for health goals test</p>";
		$test_results['health_goals'] = 'skipped';
	}
}

// Test 8: Check email system
function test_email_system() {
	global $test_results;

	echo '<h2>8. Email System Test</h2>';

	// Check if email class exists
	if ( class_exists( 'ENNU_Email_System' ) ) {
		echo "<p style='color: green;'>✅ Email system class exists</p>";
		$test_results['email_system'] = true;
	} else {
		echo "<p style='color: red;'>❌ Email system class missing</p>";
		$test_results['email_system'] = false;
	}
}

// Test 9: Check scoring system
function test_scoring_system() {
	global $test_results;

	echo '<h2>9. Scoring System Test</h2>';

	// Check if scoring classes exist
	$scoring_classes = array(
		'ENNU_Scoring_System',
		'ENNU_Assessment_Calculator',
		'ENNU_Health_Optimization_Calculator',
	);

	foreach ( $scoring_classes as $class ) {
		if ( class_exists( $class ) ) {
			echo "<p style='color: green;'>✅ Scoring class exists: $class</p>";
			$test_results[ 'scoring_' . strtolower( str_replace( 'ENNU_', '', $class ) ) ] = true;
		} else {
			echo "<p style='color: red;'>❌ Scoring class missing: $class</p>";
			$test_results[ 'scoring_' . strtolower( str_replace( 'ENNU_', '', $class ) ) ] = false;
		}
	}
}

// Test 10: Check templates
function test_templates() {
	global $test_results;

	echo '<h2>10. Templates Test</h2>';

	$template_files = array(
		'templates/user-dashboard.php',
		'templates/assessment-results.php',
		'templates/assessment-details-page.php',
	);

	foreach ( $template_files as $template ) {
		if ( file_exists( $template ) ) {
			echo "<p style='color: green;'>✅ Template exists: $template</p>";
			$test_results[ 'template_' . basename( $template, '.php' ) ] = true;
		} else {
			echo "<p style='color: red;'>❌ Template missing: $template</p>";
			$test_results[ 'template_' . basename( $template, '.php' ) ] = false;
		}
	}
}

// Run all tests
test_plugin_activation();
test_database_tables();
test_shortcode_registration();
test_ajax_handlers();
test_assessment_submission();
test_dashboard_functionality();
test_health_goals();
test_email_system();
test_scoring_system();
test_templates();

// Summary
echo '<h2>📊 Test Summary</h2>';

$total_tests   = count( $test_results );
$passed_tests  = count(
	array_filter(
		$test_results,
		function( $result ) {
			return $result === true;
		}
	)
);
$failed_tests  = count(
	array_filter(
		$test_results,
		function( $result ) {
			return $result === false;
		}
	)
);
$skipped_tests = count(
	array_filter(
		$test_results,
		function( $result ) {
			return $result === 'skipped';
		}
	)
);

echo "<p><strong>Total Tests:</strong> $total_tests</p>";
echo "<p style='color: green;'><strong>Passed:</strong> $passed_tests</p>";
echo "<p style='color: red;'><strong>Failed:</strong> $failed_tests</p>";
echo "<p style='color: orange;'><strong>Skipped:</strong> $skipped_tests</p>";

$success_rate = ( $passed_tests / $total_tests ) * 100;
echo '<p><strong>Success Rate:</strong> ' . round( $success_rate, 1 ) . '%</p>';

// Recommendations
echo '<h2>🎯 Recommendations</h2>';

if ( $success_rate >= 80 ) {
	echo "<p style='color: green;'>✅ Plugin is mostly functional. Focus on user experience improvements.</p>";
} elseif ( $success_rate >= 60 ) {
	echo "<p style='color: orange;'>⚠️ Plugin has some issues. Fix critical functionality first.</p>";
} else {
	echo "<p style='color: red;'>❌ Plugin has significant issues. Immediate fixes needed.</p>";
}

// Failed tests summary
if ( $failed_tests > 0 ) {
	echo '<h3>Failed Tests:</h3>';
	echo '<ul>';
	foreach ( $test_results as $test => $result ) {
		if ( $result === false ) {
			echo "<li style='color: red;'>$test</li>";
		}
	}
	echo '</ul>';
}

echo '<hr>';
echo '<p><em>Test completed at: ' . current_time( 'Y-m-d H:i:s' ) . '</em></p>';


