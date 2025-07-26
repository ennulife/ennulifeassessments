<?php
/**
 * Simple Functional Test
 *
 * Run this through your browser to test basic functionality
 */

// Basic functionality check
echo '<h1>ENNU Life Assessments - Simple Functional Test</h1>';

// Check if we're in WordPress
if ( ! function_exists( 'get_option' ) ) {
	echo "<p style='color: red;'>❌ Not running in WordPress environment</p>";
	echo '<p>Please access this file through your WordPress site: yoursite.com/wp-content/plugins/ennulifeassessments/test-functional-simple.php</p>';
	exit;
}

// Test 1: Plugin files exist
echo '<h2>1. Plugin Files Check</h2>';
$files_to_check = array(
	'ennu-life-plugin.php',
	'includes/class-assessment-shortcodes.php',
	'includes/class-scoring-system.php',
	'includes/class-enhanced-database.php',
	'templates/user-dashboard.php',
);

foreach ( $files_to_check as $file ) {
	if ( file_exists( $file ) ) {
		echo "<p style='color: green;'>✅ $file exists</p>";
	} else {
		echo "<p style='color: red;'>❌ $file missing</p>";
	}
}

// Test 2: Database tables
echo '<h2>2. Database Tables Check</h2>';
global $wpdb;

$tables = array(
	$wpdb->prefix . 'ennu_assessments',
	$wpdb->prefix . 'ennu_scores',
	$wpdb->prefix . 'ennu_health_goals',
);

foreach ( $tables as $table ) {
	$exists = $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) == $table;
	if ( $exists ) {
		echo "<p style='color: green;'>✅ Table exists: $table</p>";
	} else {
		echo "<p style='color: red;'>❌ Table missing: $table</p>";
	}
}

// Test 3: Shortcodes
echo '<h2>3. Shortcodes Check</h2>';
$shortcodes = array( 'ennu-hair', 'ennu-ed-treatment', 'ennu-health-optimization' );
foreach ( $shortcodes as $shortcode ) {
	if ( shortcode_exists( $shortcode ) ) {
		echo "<p style='color: green;'>✅ Shortcode exists: [$shortcode]</p>";
	} else {
		echo "<p style='color: red;'>❌ Shortcode missing: [$shortcode]</p>";
	}
}

// Test 4: AJAX handlers
echo '<h2>4. AJAX Handlers Check</h2>';
$ajax_actions = array( 'ennu_submit_assessment', 'ennu_update_health_goals' );
foreach ( $ajax_actions as $action ) {
	if ( has_action( "wp_ajax_$action" ) || has_action( "wp_ajax_nopriv_$action" ) ) {
		echo "<p style='color: green;'>✅ AJAX handler exists: $action</p>";
	} else {
		echo "<p style='color: red;'>❌ AJAX handler missing: $action</p>";
	}
}

// Test 5: Classes
echo '<h2>5. Classes Check</h2>';
$classes = array(
	'ENNU_Assessment_Shortcodes',
	'ENNU_Scoring_System',
	'ENNU_Enhanced_Database',
	'ENNU_Email_System',
);

foreach ( $classes as $class ) {
	if ( class_exists( $class ) ) {
		echo "<p style='color: green;'>✅ Class exists: $class</p>";
	} else {
		echo "<p style='color: red;'>❌ Class missing: $class</p>";
	}
}

// Test 6: Basic functionality test
echo '<h2>6. Basic Functionality Test</h2>';

// Test assessment submission
try {
	$test_data = array(
		'user_id'    => 1,
		'type'       => 'health_optimization',
		'data'       => json_encode( array( 'energy' => '4' ) ),
		'status'     => 'active',
		'created_at' => current_time( 'mysql' ),
	);

	$result = $wpdb->insert(
		$wpdb->prefix . 'ennu_assessments',
		$test_data,
		array( '%d', '%s', '%s', '%s', '%s' )
	);

	if ( $result !== false ) {
		echo "<p style='color: green;'>✅ Assessment submission works</p>";
		// Clean up
		$wpdb->delete( $wpdb->prefix . 'ennu_assessments', array( 'id' => $wpdb->insert_id ) );
	} else {
		echo "<p style='color: red;'>❌ Assessment submission failed: " . $wpdb->last_error . '</p>';
	}
} catch ( Exception $e ) {
	echo "<p style='color: red;'>❌ Assessment submission error: " . $e->getMessage() . '</p>';
}

// Test dashboard data retrieval
try {
	$assessments = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ennu_assessments LIMIT 1" );
	if ( $assessments !== false ) {
		echo "<p style='color: green;'>✅ Dashboard data retrieval works</p>";
	} else {
		echo "<p style='color: red;'>❌ Dashboard data retrieval failed</p>";
	}
} catch ( Exception $e ) {
	echo "<p style='color: red;'>❌ Dashboard data retrieval error: " . $e->getMessage() . '</p>';
}

echo '<hr>';
echo '<h2>🎯 Next Steps</h2>';
echo '<p>Based on these results, you can:</p>';
echo '<ul>';
echo '<li>Fix any missing files or tables</li>';
echo '<li>Test the actual user experience</li>';
echo '<li>Address any broken functionality</li>';
echo '<li>Then move to optimization</li>';
echo '</ul>';

echo '<p><em>Test completed at: ' . current_time( 'Y-m-d H:i:s' ) . '</em></p>';


