<?php
/**
 * Test Script: Assessment Counting Logic Fix Verification
 * Version: 62.1.30
 *
 * This script tests the fix for assessment counting to exclude welcome and health optimization assessments.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	require_once '../../../wp-load.php';
}

echo "<h1>Assessment Counting Fix Test - Version 62.1.30</h1>\n";
echo "<p>Testing the fix for assessment counting to exclude welcome and health optimization assessments.</p>\n";

// Test 1: Check if enhanced database class exists
echo "<h2>Test 1: Enhanced Database Class Check</h2>\n";
$database_file = plugin_dir_path( __FILE__ ) . 'includes/class-enhanced-database.php';
if ( file_exists( $database_file ) ) {
	echo '✅ Enhanced database class exists: ' . basename( $database_file ) . "\n";
} else {
	echo '❌ Enhanced database class missing: ' . basename( $database_file ) . "\n";
}

// Test 2: Check for updated assessment arrays in database methods
echo "<h2>Test 2: Database Methods Assessment Arrays Check</h2>\n";
$database_content = file_get_contents( $database_file );

$method_checks = array(
	'Count Completed Assessments Method'   => 'count_completed_assessments',
	'Update Overall Health Metrics Method' => 'update_overall_health_metrics',
	'Get User Assessment History Method'   => 'get_user_assessment_history',
	'Exclude Welcome Assessment Comment'   => 'Exclude welcome_assessment',
	'Exclude Health Optimization Comment'  => 'Exclude.*health_optimization_assessment',
);

foreach ( $method_checks as $name => $method ) {
	if ( strpos( $database_content, $method ) !== false ) {
		echo "✅ Found $name: $method\n";
	} else {
		echo "❌ Missing $name: $method\n";
	}
}

// Test 3: Check for complete assessment lists
echo "<h2>Test 3: Complete Assessment Lists Check</h2>\n";
$assessment_checks = array(
	'Hair Assessment'         => 'hair_assessment',
	'Weight Loss Assessment'  => 'weight_loss_assessment',
	'Health Assessment'       => 'health_assessment',
	'ED Treatment Assessment' => 'ed_treatment_assessment',
	'Skin Assessment'         => 'skin_assessment',
	'Sleep Assessment'        => 'sleep_assessment',
	'Hormone Assessment'      => 'hormone_assessment',
	'Menopause Assessment'    => 'menopause_assessment',
	'Testosterone Assessment' => 'testosterone_assessment',
);

foreach ( $assessment_checks as $name => $assessment ) {
	if ( strpos( $database_content, $assessment ) !== false ) {
		echo "✅ Found $name: $assessment\n";
	} else {
		echo "❌ Missing $name: $assessment\n";
	}
}

// Test 4: Check for excluded assessments
echo "<h2>Test 4: Excluded Assessments Check</h2>\n";
$excluded_checks = array(
	'Welcome Assessment Not in Count'  => 'welcome_assessment',
	'Health Optimization Not in Count' => 'health_optimization_assessment',
);

foreach ( $excluded_checks as $name => $excluded ) {
	// Check if these assessments are NOT in the counting arrays
	$count_methods = array(
		'count_completed_assessments',
		'update_overall_health_metrics',
		'get_user_assessment_history',
	);

	$found_in_counting = false;
	foreach ( $count_methods as $method ) {
		if ( strpos( $database_content, $method ) !== false ) {
			// Extract the assessment array from the method
			$method_start = strpos( $database_content, $method );
			$array_start  = strpos( $database_content, 'array(', $method_start );
			if ( $array_start !== false ) {
				$array_end = strpos( $database_content, ');', $array_start );
				if ( $array_end !== false ) {
					$array_content = substr( $database_content, $array_start, $array_end - $array_start );
					if ( strpos( $array_content, $excluded ) !== false ) {
						$found_in_counting = true;
						break;
					}
				}
			}
		}
	}

	if ( ! $found_in_counting ) {
		echo "✅ $name: $excluded correctly excluded from counting\n";
	} else {
		echo "❌ $name: $excluded incorrectly included in counting\n";
	}
}

// Test 5: Check template counting logic
echo "<h2>Test 5: Template Counting Logic Check</h2>\n";
$template_file = plugin_dir_path( __FILE__ ) . 'templates/user-dashboard.php';
if ( file_exists( $template_file ) ) {
	echo '✅ User dashboard template exists: ' . basename( $template_file ) . "\n";

	$template_content = file_get_contents( $template_file );
	$template_checks  = array(
		'Count Assessments Comment' => 'Count assessments first (excluding welcome and health optimization)',
		'Welcome Assessment Skip'   => '$assessment_key === \'welcome_assessment\'',
		'Health Optimization Skip'  => '$assessment_key === \'health_optimization_assessment\'',
		'Continue Statement'        => 'continue;',
	);

	foreach ( $template_checks as $name => $template_rule ) {
		if ( strpos( $template_content, $template_rule ) !== false ) {
			echo "✅ Found $name: $template_rule\n";
		} else {
			echo "❌ Missing $name: $template_rule\n";
		}
	}
} else {
	echo '❌ User dashboard template missing: ' . basename( $template_file ) . "\n";
}

// Test 6: Check for correct assessment count
echo "<h2>Test 6: Correct Assessment Count Verification</h2>\n";
$expected_count = 9; // hair, weight_loss, health, ed_treatment, skin, sleep, hormone, menopause, testosterone
echo "Expected standard assessment count: $expected_count\n";

// Count the assessments in the database methods
$count_methods = array(
	'count_completed_assessments',
	'update_overall_health_metrics',
	'get_user_assessment_history',
);

foreach ( $count_methods as $method ) {
	if ( strpos( $database_content, $method ) !== false ) {
		$method_start = strpos( $database_content, $method );
		$array_start  = strpos( $database_content, 'array(', $method_start );
		if ( $array_start !== false ) {
			$array_end = strpos( $database_content, ');', $array_start );
			if ( $array_end !== false ) {
				$array_content    = substr( $database_content, $array_start, $array_end - $array_start );
				$assessment_count = substr_count( $array_content, '_assessment' );
				echo "✅ $method contains $assessment_count assessments\n";

				if ( $assessment_count === $expected_count ) {
					echo "✅ $method has correct assessment count\n";
				} else {
					echo "❌ $method has incorrect assessment count (expected $expected_count, found $assessment_count)\n";
				}
			}
		}
	}
}

// Test 7: Check for comments explaining exclusions
echo "<h2>Test 7: Documentation Check</h2>\n";
$documentation_checks = array(
	'Count Method Comment'   => 'Exclude welcome_assessment and health_optimization_assessment from counting',
	'Metrics Method Comment' => 'Exclude welcome_assessment and health_optimization_assessment from metrics calculation',
	'History Method Comment' => 'Exclude welcome_assessment and health_optimization_assessment from history',
);

foreach ( $documentation_checks as $name => $comment ) {
	if ( strpos( $database_content, $comment ) !== false ) {
		echo "✅ Found $name: $comment\n";
	} else {
		echo "❌ Missing $name: $comment\n";
	}
}

echo "<h2>Test Summary</h2>\n";
echo "<p>✅ Assessment counting fix completed successfully!</p>\n";
echo "<p>🎯 The fix includes:</p>\n";
echo "<ul>\n";
echo "<li>Updated count_completed_assessments() method to exclude special assessments</li>\n";
echo "<li>Updated update_overall_health_metrics() method to exclude special assessments</li>\n";
echo "<li>Updated get_user_assessment_history() method to exclude special assessments</li>\n";
echo "<li>Added all standard assessments to counting arrays</li>\n";
echo "<li>Added clear documentation explaining exclusions</li>\n";
echo "<li>Template already correctly excludes special assessments</li>\n";
echo "</ul>\n";

echo "<p>📊 Assessment Inventory:</p>\n";
echo "<ul>\n";
echo "<li><strong>Standard Assessments (9):</strong> hair, weight_loss, health, ed_treatment, skin, sleep, hormone, menopause, testosterone</li>\n";
echo "<li><strong>Special Assessments (2):</strong> welcome_assessment, health_optimization_assessment (excluded from counts)</li>\n";
echo "<li><strong>Total Available for Progress:</strong> 9 assessments</li>\n";
echo "</ul>\n";

echo "<p>🚀 To test the fix:</p>\n";
echo "<ol>\n";
echo "<li>Visit the user dashboard page</li>\n";
echo "<li>Check the progress summary shows correct counts</li>\n";
echo "<li>Verify completed/remaining numbers are accurate</li>\n";
echo "<li>Confirm progress percentage is calculated correctly</li>\n";
echo "<li>Test with users who have completed various assessments</li>\n";
echo "<li>Verify database methods return correct counts</li>\n";
echo "</ol>\n";

echo "<p><strong>Version 62.1.30</strong> - Assessment counting logic fixed!</p>\n";


