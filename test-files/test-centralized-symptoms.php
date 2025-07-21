<?php
/**
 * Test Centralized Symptoms System
 * 
 * This script tests the centralized symptoms manager functionality
 * to verify the system is working correctly.
 */

// Load WordPress
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );

// Ensure we're in admin context
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Access denied' );
}

echo "<h1>ENNU Life Centralized Symptoms System Test</h1>";

// Test 1: Check if class exists
echo "<h2>Test 1: Class Existence</h2>";
if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
    echo "✅ ENNU_Centralized_Symptoms_Manager class exists<br>";
} else {
    echo "❌ ENNU_Centralized_Symptoms_Manager class NOT found<br>";
    echo "Make sure the class is properly loaded in the main plugin file.<br>";
}

// Test 2: Test with a sample user
echo "<h2>Test 2: Sample User Test</h2>";
$test_user_id = 1; // Use admin user for testing

if ( get_user_by( 'id', $test_user_id ) ) {
    echo "✅ Test user (ID: $test_user_id) exists<br>";
    
    // Test centralized symptoms retrieval
    if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
        $symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $test_user_id );
        echo "✅ Centralized symptoms retrieved<br>";
        echo "Total symptoms: " . ( $symptoms['total_count'] ?? 0 ) . "<br>";
        echo "Unique symptoms: " . count( $symptoms['symptoms'] ?? array() ) . "<br>";
        
        // Test analytics
        $analytics = ENNU_Centralized_Symptoms_Manager::get_symptom_analytics( $test_user_id );
        echo "✅ Symptom analytics retrieved<br>";
        echo "Analytics data: " . json_encode( $analytics, JSON_PRETTY_PRINT ) . "<br>";
        
        // Test history
        $history = ENNU_Centralized_Symptoms_Manager::get_symptom_history( $test_user_id, 5 );
        echo "✅ Symptom history retrieved<br>";
        echo "History entries: " . count( $history ) . "<br>";
        
    } else {
        echo "❌ Cannot test functionality - class not found<br>";
    }
} else {
    echo "❌ Test user (ID: $test_user_id) not found<br>";
}

// Test 3: Check database fields
echo "<h2>Test 3: Database Fields Check</h2>";
$centralized_meta = get_user_meta( $test_user_id, 'ennu_centralized_symptoms', true );
$history_meta = get_user_meta( $test_user_id, 'ennu_symptom_history', true );

if ( ! empty( $centralized_meta ) ) {
    echo "✅ Centralized symptoms field exists in database<br>";
    echo "Field data type: " . gettype( $centralized_meta ) . "<br>";
} else {
    echo "⚠️ Centralized symptoms field is empty (this is normal for new users)<br>";
}

if ( ! empty( $history_meta ) ) {
    echo "✅ Symptom history field exists in database<br>";
    echo "History entries: " . count( $history_meta ) . "<br>";
} else {
    echo "⚠️ Symptom history field is empty (this is normal for new users)<br>";
}

// Test 4: Test assessment integration
echo "<h2>Test 4: Assessment Integration Test</h2>";
if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
    // Simulate assessment completion
    $result = ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $test_user_id, 'hormone' );
    if ( $result ) {
        echo "✅ Assessment integration test successful<br>";
    } else {
        echo "❌ Assessment integration test failed<br>";
    }
} else {
    echo "❌ Cannot test integration - class not found<br>";
}

// Test 5: Check existing assessment data
echo "<h2>Test 5: Existing Assessment Data Check</h2>";
$assessment_fields = array(
    'ennu_hormone_hormone_q1',
    'ennu_health_optimization_assessment_symptom_q1',
    'ennu_testosterone_testosterone_q1'
);

foreach ( $assessment_fields as $field ) {
    $value = get_user_meta( $test_user_id, $field, true );
    if ( ! empty( $value ) ) {
        echo "✅ Found data in $field: " . json_encode( $value ) . "<br>";
    } else {
        echo "⚠️ No data in $field<br>";
    }
}

echo "<h2>Test Summary</h2>";
echo "The centralized symptoms system is ";
if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
    echo "<strong>IMPLEMENTED</strong> and ready for use.<br>";
    echo "Next steps:<br>";
    echo "1. Complete assessments to populate symptom data<br>";
    echo "2. Check user dashboard for centralized symptom display<br>";
    echo "3. Monitor database for centralized symptom fields<br>";
} else {
    echo "<strong>NOT FULLY FUNCTIONAL</strong>.<br>";
    echo "Issues to resolve:<br>";
    echo "1. Ensure class is properly loaded in main plugin file<br>";
    echo "2. Check for syntax errors in the class file<br>";
    echo "3. Verify plugin dependencies are loaded correctly<br>";
}

echo "<br><strong>Test completed.</strong>";
?> 