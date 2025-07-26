<?php
/**
 * Test Symptom Flagging System
 * 
 * This file tests the symptom flagging and biomarker flagging system
 * by populating test symptoms for the user.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Test user ID
$test_user_id = 1;

echo "<h1>🧪 Testing Symptom Flagging System</h1>";

// 1. Check if user has any symptoms
echo "<h2>1. Current User Symptoms</h2>";
$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $test_user_id );
echo "<p><strong>Total Symptoms:</strong> " . count( $centralized_symptoms['symptoms'] ?? array() ) . "</p>";
echo "<p><strong>Assessments with Symptoms:</strong> " . count( $centralized_symptoms['by_assessment'] ?? array() ) . "</p>";

if ( empty( $centralized_symptoms['symptoms'] ) ) {
	echo "<p style='color: orange;'>⚠️ No symptoms found. User needs to complete qualitative assessments.</p>";
}

// 2. Populate test symptoms
echo "<h2>2. Populating Test Symptoms</h2>";

// Add test symptoms to user meta
$test_symptoms = array(
	'fatigue' => array(
		'name' => 'Fatigue',
		'category' => 'Energy',
		'severity' => 'moderate',
		'frequency' => 'daily',
		'assessments' => array('health_optimization_assessment'),
		'first_reported' => date('Y-m-d H:i:s', strtotime('-7 days')),
		'last_reported' => date('Y-m-d H:i:s'),
		'occurrence_count' => 5
	),
	'low_libido' => array(
		'name' => 'Low Libido',
		'category' => 'Sexual Health',
		'severity' => 'high',
		'frequency' => 'weekly',
		'assessments' => array('testosterone_assessment'),
		'first_reported' => date('Y-m-d H:i:s', strtotime('-14 days')),
		'last_reported' => date('Y-m-d H:i:s'),
		'occurrence_count' => 3
	),
	'brain_fog' => array(
		'name' => 'Brain Fog',
		'category' => 'Cognitive',
		'severity' => 'moderate',
		'frequency' => 'daily',
		'assessments' => array('health_optimization_assessment'),
		'first_reported' => date('Y-m-d H:i:s', strtotime('-3 days')),
		'last_reported' => date('Y-m-d H:i:s'),
		'occurrence_count' => 4
	)
);

// Update centralized symptoms with test data
$centralized_symptoms['symptoms'] = $test_symptoms;
$centralized_symptoms['by_assessment'] = array(
	'health_optimization_assessment' => array(
		array('name' => 'Fatigue', 'category' => 'Energy'),
		array('name' => 'Brain Fog', 'category' => 'Cognitive')
	),
	'testosterone_assessment' => array(
		array('name' => 'Low Libido', 'category' => 'Sexual Health')
	)
);

// Save to user meta
update_user_meta( $test_user_id, 'ennu_centralized_symptoms', $centralized_symptoms );

echo "<p style='color: green;'>✅ Test symptoms populated:</p>";
echo "<ul>";
foreach ( $test_symptoms as $symptom_key => $symptom_data ) {
	echo "<li><strong>{$symptom_data['name']}</strong> ({$symptom_data['category']}) - {$symptom_data['severity']} severity</li>";
}
echo "</ul>";

// 3. Test biomarker flagging
echo "<h2>3. Testing Biomarker Flagging</h2>";

if ( class_exists( 'ENNU_Biomarker_Flag_Manager' ) ) {
	$flag_manager = new ENNU_Biomarker_Flag_Manager();
	
	// Get symptom-biomarker correlations
	$correlations_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/symptom-biomarker-correlations.php';
	if ( file_exists( $correlations_file ) ) {
		$symptom_correlations = include $correlations_file;
		
		echo "<p><strong>Symptom-Biomarker Correlations Found:</strong></p>";
		foreach ( $test_symptoms as $symptom_key => $symptom_data ) {
			$symptom_name = $symptom_data['name'];
			if ( isset( $symptom_correlations[ $symptom_name ] ) ) {
				$biomarkers = $symptom_correlations[ $symptom_name ];
				echo "<p><strong>{$symptom_name}</strong> → " . implode(', ', $biomarkers) . "</p>";
				
				// Flag biomarkers for this symptom
				foreach ( $biomarkers as $biomarker ) {
					$reason = "Flagged due to reported symptom: {$symptom_name}";
					$flag_manager->flag_biomarker( $test_user_id, $biomarker, 'symptom_triggered', $reason );
				}
			}
		}
	}
	
	// Get flagged biomarkers
	$flagged_biomarkers = $flag_manager->get_flagged_biomarkers( $test_user_id, 'active' );
	echo "<p><strong>Flagged Biomarkers:</strong> " . count( $flagged_biomarkers ) . "</p>";
	
	if ( ! empty( $flagged_biomarkers ) ) {
		echo "<ul>";
		foreach ( $flagged_biomarkers as $flag_id => $flag_data ) {
			echo "<li><strong>{$flag_data['biomarker_name']}</strong> - {$flag_data['reason']}</li>";
		}
		echo "</ul>";
	} else {
		echo "<p style='color: red;'>❌ No biomarkers flagged</p>";
	}
} else {
	echo "<p style='color: red;'>❌ ENNU_Biomarker_Flag_Manager class not found</p>";
}

// 4. Test dashboard display
echo "<h2>4. Testing Dashboard Display</h2>";

// Simulate dashboard data
$user_symptoms = ENNU_Assessment_Scoring::get_symptom_data_for_user( $test_user_id );
echo "<p><strong>User Symptoms for Dashboard:</strong></p>";
echo "<pre>" . print_r( $user_symptoms, true ) . "</pre>";

echo "<h2>✅ Test Complete</h2>";
echo "<p>The symptom flagging system should now work. Check the user dashboard to see flagged biomarkers.</p>";
echo "<p><a href='http://localhost/?page_id=2469' target='_blank'>View User Dashboard</a></p>";

// Test symptom-based biomarker flagging
echo "Testing symptom-based biomarker flagging...<br>";

// Check if WordPress is loaded
if (!defined('ABSPATH')) {
    echo "WordPress not loaded<br>";
    exit;
}

echo "WordPress loaded successfully<br>";

// Check if user is admin
if (!current_user_can('manage_options')) {
    echo "Not admin user<br>";
    exit;
}

echo "Admin user confirmed<br>";

$user_id = 1;
echo "Testing with user ID: {$user_id}<br>";

// Test symptom-based biomarker flagging
if (class_exists('ENNU_Centralized_Symptoms_Manager')) {
    echo "Centralized Symptoms Manager exists<br>";
    
    // Clear existing flags first
    delete_user_meta($user_id, 'ennu_biomarker_flags');
    echo "Cleared existing biomarker flags<br>";
    
    // Test symptoms that should trigger biomarker flags
    $test_symptoms = array(
        array('name' => 'thyroid', 'category' => 'Weight Loss - Medical Condition'),
        array('name' => 'insulin_resistance', 'category' => 'Weight Loss - Medical Condition'),
        array('name' => 'Low Energy Level', 'category' => 'Weight Loss - Energy'),
        array('name' => 'Poor Sleep Quality', 'category' => 'Weight Loss - Sleep'),
        array('name' => 'High Stress Level', 'category' => 'Weight Loss - Stress'),
        array('name' => 'Frequent Food Cravings', 'category' => 'Weight Loss - Cravings')
    );
    
    echo "Testing with " . count($test_symptoms) . " symptoms<br>";
    
    // Call the auto-flagging method directly
    $flags_created = ENNU_Centralized_Symptoms_Manager::auto_flag_biomarkers_from_symptoms($user_id, $test_symptoms);
    echo "Flags created: {$flags_created}<br>";
    
    // Check results
    $flag_manager = new ENNU_Biomarker_Flag_Manager();
    $user_flags = $flag_manager->get_flagged_biomarkers($user_id);
    echo "Total flags in database: " . count($user_flags) . "<br>";
    
    if (!empty($user_flags)) {
        echo "Flags found:<br>";
        foreach ($user_flags as $flag_id => $flag_data) {
            echo "- {$flag_data['biomarker_name']}: {$flag_data['reason']}<br>";
        }
    }
    
} else {
    echo "Centralized Symptoms Manager not found<br>";
}

echo "Test complete!";
?> 