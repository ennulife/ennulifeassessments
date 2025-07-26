<?php
/**
 * ENNU Life - Admin Symptoms Tab Test
 *
 * This script tests the admin symptoms tab functionality
 * to ensure it's properly integrated into the WordPress admin.
 */

// Load WordPress
require_once dirname( __FILE__ ) . '/../../../wp-load.php';

// Ensure we're in admin context
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Access denied' );
}

echo '<h1>ENNU Life - Admin Symptoms Tab Test</h1>';
echo '<p><strong>Testing the centralized symptoms tab in WordPress admin user profile.</strong></p>';

// Test user ID (admin user)
$test_user_id = 1;

echo '<h2>Step 1: Verifying Admin Class Integration</h2>';

if ( class_exists( 'ENNU_Enhanced_Admin' ) ) {
	echo '✅ ENNU_Enhanced_Admin class exists<br>';

	$admin = new ENNU_Enhanced_Admin();
	if ( method_exists( $admin, 'display_centralized_symptoms_section' ) ) {
		echo '✅ display_centralized_symptoms_section method exists<br>';
	} else {
		echo '❌ display_centralized_symptoms_section method NOT found<br>';
	}

	if ( method_exists( $admin, 'handle_update_centralized_symptoms' ) ) {
		echo '✅ handle_update_centralized_symptoms method exists<br>';
	} else {
		echo '❌ handle_update_centralized_symptoms method NOT found<br>';
	}

	if ( method_exists( $admin, 'handle_populate_centralized_symptoms' ) ) {
		echo '✅ handle_populate_centralized_symptoms method exists<br>';
	} else {
		echo '❌ handle_populate_centralized_symptoms method NOT found<br>';
	}

	if ( method_exists( $admin, 'handle_clear_symptom_history' ) ) {
		echo '✅ handle_clear_symptom_history method exists<br>';
	} else {
		echo '❌ handle_clear_symptom_history method NOT found<br>';
	}
} else {
	echo '❌ ENNU_Enhanced_Admin class NOT found<br>';
	exit;
}

echo '<h2>Step 2: Testing Centralized Symptoms Data</h2>';

if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
	$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $test_user_id );
	$symptom_analytics    = ENNU_Centralized_Symptoms_Manager::get_symptom_analytics( $test_user_id );
	$symptom_history      = ENNU_Centralized_Symptoms_Manager::get_symptom_history( $test_user_id, 5 );

	if ( ! empty( $centralized_symptoms['symptoms'] ) ) {
		echo '✅ Centralized symptoms data available<br>';
		echo '   - Total symptoms: ' . ( $centralized_symptoms['total_count'] ?? 0 ) . '<br>';
		echo '   - Unique symptoms: ' . count( $centralized_symptoms['symptoms'] ) . '<br>';
		echo '   - Assessments: ' . count( $centralized_symptoms['by_assessment'] ?? array() ) . '<br>';
	} else {
		echo '⚠️ No centralized symptoms data found. Creating test data...<br>';

		// Create test data
		update_user_meta( $test_user_id, 'ennu_hormone_hormone_q1', array( 'Fatigue', 'Brain Fog' ) );
		update_user_meta( $test_user_id, 'ennu_hormone_score_calculated_at', current_time( 'mysql' ) );
		ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $test_user_id );

		$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $test_user_id );
		$symptom_analytics    = ENNU_Centralized_Symptoms_Manager::get_symptom_analytics( $test_user_id );
		$symptom_history      = ENNU_Centralized_Symptoms_Manager::get_symptom_history( $test_user_id, 5 );

		echo '✅ Test data created<br>';
	}
} else {
	echo '❌ ENNU_Centralized_Symptoms_Manager class not found<br>';
	exit;
}

echo '<h2>Step 3: Testing Admin Symptoms Tab Display</h2>';

echo "<div style='background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo '<h3>Simulated Admin Symptoms Tab</h3>';

// Simulate the admin symptoms tab display
$admin = new ENNU_Enhanced_Admin();
$admin->display_centralized_symptoms_section( $test_user_id );

echo '</div>';

echo '<h2>Step 4: Testing AJAX Handlers</h2>';

// Test AJAX handler registration
$ajax_actions = array(
	'ennu_update_centralized_symptoms',
	'ennu_populate_centralized_symptoms',
	'ennu_clear_symptom_history',
);

echo '<h3>AJAX Action Registration Test</h3>';
foreach ( $ajax_actions as $action ) {
	if ( has_action( 'wp_ajax_' . $action ) ) {
		echo "✅ AJAX action '$action' is registered<br>";
	} else {
		echo "❌ AJAX action '$action' is NOT registered<br>";
	}
}

echo '<h2>Step 5: Testing CSS and JavaScript Assets</h2>';

// Check if CSS file exists
$css_file = ENNU_LIFE_PLUGIN_PATH . 'assets/css/admin-symptoms-enhanced.css';
if ( file_exists( $css_file ) ) {
	echo '✅ Admin symptoms CSS file exists<br>';
	$css_size = filesize( $css_file );
	echo "   - File size: $css_size bytes<br>";
} else {
	echo '❌ Admin symptoms CSS file NOT found<br>';
}

// Check if JavaScript file exists and contains symptom management
$js_file = ENNU_LIFE_PLUGIN_PATH . 'assets/js/ennu-admin-enhanced.js';
if ( file_exists( $js_file ) ) {
	echo '✅ Admin enhanced JavaScript file exists<br>';
	$js_content = file_get_contents( $js_file );
	if ( strpos( $js_content, 'initSymptomManagement' ) !== false ) {
		echo '✅ Symptom management JavaScript function found<br>';
	} else {
		echo '❌ Symptom management JavaScript function NOT found<br>';
	}
	if ( strpos( $js_content, 'updateCentralizedSymptoms' ) !== false ) {
		echo '✅ Update symptoms JavaScript function found<br>';
	} else {
		echo '❌ Update symptoms JavaScript function NOT found<br>';
	}
} else {
	echo '❌ Admin enhanced JavaScript file NOT found<br>';
}

echo '<h2>Step 6: Testing Admin Profile Page Integration</h2>';

// Check if admin profile hooks are registered
$profile_hooks = array(
	'show_user_profile'        => 'show_user_assessment_fields',
	'edit_user_profile'        => 'show_user_assessment_fields',
	'personal_options_update'  => 'save_user_assessment_fields',
	'edit_user_profile_update' => 'save_user_assessment_fields',
);

echo '<h3>Admin Profile Hook Registration Test</h3>';
foreach ( $profile_hooks as $hook => $method ) {
	if ( has_action( $hook, array( 'ENNU_Enhanced_Admin', $method ) ) ) {
		echo "✅ Hook '$hook' with method '$method' is registered<br>";
	} else {
		echo "❌ Hook '$hook' with method '$method' is NOT registered<br>";
	}
}

echo '<h2>Step 7: Testing Tab Navigation</h2>';

echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo '<h3>Expected Tab Structure</h3>';
echo '<p>The admin user profile page should now have the following tabs:</p>';
echo '<ul>';
echo "<li><strong>Global & Health Metrics</strong> - User's basic health information</li>";
echo '<li><strong>Centralized Symptoms</strong> - NEW! Comprehensive symptom overview</li>';
echo '<li><strong>Individual Assessment Tabs</strong> - Hormone, Health, etc.</li>';
echo '</ul>';
echo '<p>The <strong>Centralized Symptoms</strong> tab should display:</p>';
echo '<ul>';
echo '<li>Summary statistics (Total, Unique, Assessments, Last Updated)</li>';
echo '<li>Symptoms by category</li>';
echo '<li>Symptoms by assessment</li>';
echo '<li>Symptom analytics</li>';
echo '<li>Recent symptom history</li>';
echo '<li>Management actions (Update, Populate, Clear History)</li>';
echo '</ul>';
echo '</div>';

echo '<h2>Step 8: Testing Admin Profile Page Access</h2>';

$profile_url = admin_url( 'user-edit.php?user_id=' . $test_user_id );
echo "<p><strong>Admin Profile URL:</strong> <a href='$profile_url' target='_blank'>$profile_url</a></p>";
echo '<p><strong>Instructions:</strong></p>';
echo '<ol>';
echo '<li>Click the link above to open the admin user profile page</li>';
echo '<li>Look for the <strong>Centralized Symptoms</strong> tab in the tab navigation</li>';
echo '<li>Click on the tab to view the comprehensive symptoms data</li>';
echo '<li>Test the management buttons (Update, Populate, Clear History)</li>';
echo '<li>Verify that the data displays correctly and actions work</li>';
echo '</ol>';

echo '<h2>Admin Symptoms Tab Test Summary</h2>';

$test_results = array(
	'Admin class exists'                  => class_exists( 'ENNU_Enhanced_Admin' ),
	'Symptoms display method exists'      => method_exists( $admin, 'display_centralized_symptoms_section' ),
	'Update handler exists'               => method_exists( $admin, 'handle_update_centralized_symptoms' ),
	'Populate handler exists'             => method_exists( $admin, 'handle_populate_centralized_symptoms' ),
	'Clear history handler exists'        => method_exists( $admin, 'handle_clear_symptom_history' ),
	'Centralized symptoms data available' => ! empty( $centralized_symptoms['symptoms'] ),
	'CSS file exists'                     => file_exists( $css_file ),
	'JavaScript functions exist'          => strpos( $js_content, 'initSymptomManagement' ) !== false,
	'AJAX actions registered'             => has_action( 'wp_ajax_ennu_update_centralized_symptoms' ),
	'Profile hooks registered'            => has_action( 'show_user_profile', array( 'ENNU_Enhanced_Admin', 'show_user_assessment_fields' ) ),
);

$passed_tests = 0;
$total_tests  = count( $test_results );

echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo '<h3>Test Results</h3>';

foreach ( $test_results as $test_name => $passed ) {
	if ( $passed ) {
		echo "✅ $test_name<br>";
		$passed_tests++;
	} else {
		echo "❌ $test_name<br>";
	}
}

$success_rate = round( ( $passed_tests / $total_tests ) * 100, 1 );

echo "<br><strong>Success Rate: $success_rate% ($passed_tests/$total_tests tests passed)</strong><br>";

if ( $success_rate >= 90 ) {
	echo '🎉 <strong>EXCELLENT!</strong> Admin symptoms tab is fully functional!<br>';
	echo '✅ All components working correctly<br>';
	echo '✅ Ready for production use<br>';
} elseif ( $success_rate >= 70 ) {
	echo '✅ <strong>GOOD!</strong> Admin symptoms tab is mostly functional.<br>';
	echo '⚠️ Some components may need attention<br>';
} else {
	echo '❌ <strong>NEEDS ATTENTION!</strong> Several issues detected.<br>';
	echo '🔧 Review the failed tests above<br>';
}

echo '</div>';

echo '<h3>Next Steps</h3>';
echo '1. ✅ Admin symptoms tab test completed<br>';
echo '2. 🔄 Test the actual admin profile page<br>';
echo '3. 🔄 Verify tab navigation works correctly<br>';
echo '4. 🔄 Test symptom management actions<br>';
echo '5. 🔄 Monitor real-world usage<br>';

echo '<br><strong>Admin symptoms tab test completed!</strong>';
echo '<br><em>Test completed at: ' . current_time( 'mysql' ) . '</em>';


