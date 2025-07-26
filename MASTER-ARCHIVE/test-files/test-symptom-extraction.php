<?php
/**
 * Test Symptom Extraction and Flagging System
 * 
 * This file tests the symptom extraction and biomarker flagging system
 * by manually triggering the process for existing user data.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Test user ID
$test_user_id = 1;

echo "<h1>🧪 Testing Symptom Extraction and Flagging System</h1>";

// 1. Check current symptoms
echo "<h2>1. Current User Symptoms</h2>";
$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $test_user_id );
echo "<p><strong>Total Symptoms:</strong> " . count( $centralized_symptoms['symptoms'] ?? array() ) . "</p>";
echo "<p><strong>Assessments with Symptoms:</strong> " . count( $centralized_symptoms['by_assessment'] ?? array() ) . "</p>";

if ( empty( $centralized_symptoms['symptoms'] ) ) {
	echo "<p><strong>No symptoms found. Triggering manual extraction...</strong></p>";
	
	// 2. Manually trigger symptom extraction
	echo "<h2>2. Triggering Manual Symptom Extraction</h2>";
	$result = ENNU_Centralized_Symptoms_Manager::force_update_symptoms( $test_user_id );
	echo "<p><strong>Extraction Result:</strong> " . ( $result ? 'Success' : 'Failed' ) . "</p>";
	
	// 3. Check symptoms again
	echo "<h2>3. Symptoms After Extraction</h2>";
	$updated_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $test_user_id );
	echo "<p><strong>Total Symptoms:</strong> " . count( $updated_symptoms['symptoms'] ?? array() ) . "</p>";
	
	if ( ! empty( $updated_symptoms['symptoms'] ) ) {
		echo "<h3>Extracted Symptoms:</h3>";
		echo "<ul>";
		foreach ( $updated_symptoms['symptoms'] as $symptom ) {
			echo "<li><strong>{$symptom['name']}</strong> - Category: {$symptom['category']}</li>";
		}
		echo "</ul>";
		
		// 4. Test biomarker flagging
		echo "<h2>4. Testing Biomarker Flagging</h2>";
		$biomarker_flags = ENNU_Biomarker_Flag_Manager::get_flagged_biomarkers( $test_user_id );
		echo "<p><strong>Flagged Biomarkers:</strong> " . count( $biomarker_flags ) . "</p>";
		
		if ( ! empty( $biomarker_flags ) ) {
			echo "<h3>Flagged Biomarkers:</h3>";
			echo "<ul>";
			foreach ( $biomarker_flags as $biomarker ) {
				echo "<li><strong>{$biomarker['name']}</strong> - Reason: {$biomarker['reason']}</li>";
			}
			echo "</ul>";
		} else {
			echo "<p><strong>No biomarkers flagged. This may be normal if symptoms don't correlate to biomarkers.</strong></p>";
		}
	} else {
		echo "<p><strong>Still no symptoms found. Checking assessment data...</strong></p>";
		
		// 5. Check assessment data
		echo "<h2>5. Checking Assessment Data</h2>";
		$hormone_symptoms = get_user_meta( $test_user_id, 'ennu_hormone_hormone_q1', true );
		echo "<p><strong>Hormone Assessment Symptoms:</strong> " . ( is_array( $hormone_symptoms ) ? implode( ', ', $hormone_symptoms ) : 'None' ) . "</p>";
		
		$ed_symptoms = get_user_meta( $test_user_id, 'ennu_ed_treatment_ed_treatment_q1', true );
		echo "<p><strong>ED Treatment Symptoms:</strong> " . ( is_array( $ed_symptoms ) ? implode( ', ', $ed_symptoms ) : 'None' ) . "</p>";
	}
} else {
	echo "<h3>Current Symptoms:</h3>";
	echo "<ul>";
	foreach ( $centralized_symptoms['symptoms'] as $symptom ) {
		echo "<li><strong>{$symptom['name']}</strong> - Category: {$symptom['category']}</li>";
	}
	echo "</ul>";
	
	// Test biomarker flagging
	echo "<h2>2. Testing Biomarker Flagging</h2>";
	$biomarker_flags = ENNU_Biomarker_Flag_Manager::get_flagged_biomarkers( $test_user_id );
	echo "<p><strong>Flagged Biomarkers:</strong> " . count( $biomarker_flags ) . "</p>";
	
	if ( ! empty( $biomarker_flags ) ) {
		echo "<h3>Flagged Biomarkers:</h3>";
		echo "<ul>";
		foreach ( $biomarker_flags as $biomarker ) {
			echo "<li><strong>{$biomarker['name']}</strong> - Reason: {$biomarker['reason']}</li>";
		}
		echo "</ul>";
	} else {
		echo "<p><strong>No biomarkers flagged. This may be normal if symptoms don't correlate to biomarkers.</strong></p>";
	}
}

echo "<h2>✅ Test Complete</h2>";
echo "<p>The symptom extraction and flagging system has been tested.</p>";
?> 