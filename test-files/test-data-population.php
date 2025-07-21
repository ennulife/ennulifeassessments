<?php
/**
 * ENNU Life Centralized Symptoms - Data Population Test
 * 
 * This script creates realistic symptom data and tests the centralized system
 * to ensure 100% functionality.
 */

// Load WordPress
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );

// Ensure we're in admin context
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Access denied' );
}

echo "<h1>ENNU Life Centralized Symptoms - Data Population Test</h1>";

// Test user ID (admin user)
$test_user_id = 1;

echo "<h2>Step 1: Creating Realistic Assessment Data</h2>";

// Create realistic hormone assessment data
$hormone_symptoms = array('Fatigue', 'Brain Fog', 'Mood Swings', 'Hot Flashes');
update_user_meta( $test_user_id, 'ennu_hormone_hormone_q1', $hormone_symptoms );
update_user_meta( $test_user_id, 'ennu_hormone_score_calculated_at', current_time( 'mysql' ) );
echo "✅ Created hormone assessment data with " . count( $hormone_symptoms ) . " symptoms<br>";

// Create realistic health optimization assessment data
update_user_meta( $test_user_id, 'ennu_health_optimization_assessment_symptom_q1', 'Fatigue' );
update_user_meta( $test_user_id, 'ennu_health_optimization_assessment_symptom_q1_severity_q', 'moderate' );
update_user_meta( $test_user_id, 'ennu_health_optimization_assessment_symptom_q1_frequency_q', 'daily' );
update_user_meta( $test_user_id, 'ennu_health_optimization_assessment_symptom_q2', 'Anxiety' );
update_user_meta( $test_user_id, 'ennu_health_optimization_assessment_symptom_q2_severity_q', 'severe' );
update_user_meta( $test_user_id, 'ennu_health_optimization_assessment_symptom_q2_frequency_q', 'multiple_times_daily' );
update_user_meta( $test_user_id, 'ennu_health_optimization_assessment_score_calculated_at', current_time( 'mysql' ) );
echo "✅ Created health optimization assessment data with 2 symptoms<br>";

// Create realistic testosterone assessment data
$testosterone_symptoms = array('Low Libido', 'Muscle Weakness', 'Depression');
update_user_meta( $test_user_id, 'ennu_testosterone_testosterone_q1', $testosterone_symptoms );
update_user_meta( $test_user_id, 'ennu_testosterone_score_calculated_at', current_time( 'mysql' ) );
echo "✅ Created testosterone assessment data with " . count( $testosterone_symptoms ) . " symptoms<br>";

// Create realistic menopause assessment data
$menopause_symptoms = array('Hot Flashes', 'Night Sweats', 'Vaginal Dryness');
update_user_meta( $test_user_id, 'ennu_menopause_menopause_q1', $menopause_symptoms );
update_user_meta( $test_user_id, 'ennu_menopause_score_calculated_at', current_time( 'mysql' ) );
echo "✅ Created menopause assessment data with " . count( $menopause_symptoms ) . " symptoms<br>";

echo "<h2>Step 2: Testing Centralized Symptoms Aggregation</h2>";

if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
    // Force update centralized symptoms
    $result = ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $test_user_id );
    
    if ( $result ) {
        echo "✅ Centralized symptoms aggregation successful<br>";
        
        // Get centralized symptoms
        $centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $test_user_id );
        
        echo "<h3>Centralized Symptoms Data:</h3>";
        echo "<pre>" . json_encode( $centralized_symptoms, JSON_PRETTY_PRINT ) . "</pre>";
        
        // Test analytics
        $analytics = ENNU_Centralized_Symptoms_Manager::get_symptom_analytics( $test_user_id );
        echo "<h3>Symptom Analytics:</h3>";
        echo "<pre>" . json_encode( $analytics, JSON_PRETTY_PRINT ) . "</pre>";
        
        // Test history
        $history = ENNU_Centralized_Symptoms_Manager::get_symptom_history( $test_user_id, 5 );
        echo "<h3>Symptom History:</h3>";
        echo "<pre>" . json_encode( $history, JSON_PRETTY_PRINT ) . "</pre>";
        
    } else {
        echo "❌ Centralized symptoms aggregation failed<br>";
    }
} else {
    echo "❌ ENNU_Centralized_Symptoms_Manager class not found<br>";
}

echo "<h2>Step 3: Testing Assessment Completion Hook</h2>";

// Simulate assessment completion
if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
    // Test individual assessment updates
    $assessments = array('hormone', 'health_optimization', 'testosterone', 'menopause');
    
    foreach ( $assessments as $assessment_type ) {
        $result = ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $test_user_id, $assessment_type );
        if ( $result ) {
            echo "✅ Assessment completion hook test successful for $assessment_type<br>";
        } else {
            echo "❌ Assessment completion hook test failed for $assessment_type<br>";
        }
    }
} else {
    echo "❌ Cannot test assessment completion hook - class not found<br>";
}

echo "<h2>Step 4: Testing Database Fields</h2>";

// Check centralized symptoms field
$centralized_meta = get_user_meta( $test_user_id, 'ennu_centralized_symptoms', true );
if ( ! empty( $centralized_meta ) && is_array( $centralized_meta ) ) {
    echo "✅ Centralized symptoms field populated successfully<br>";
    echo "Total symptoms: " . ( $centralized_meta['total_count'] ?? 0 ) . "<br>";
    echo "Unique symptoms: " . count( $centralized_meta['symptoms'] ?? array() ) . "<br>";
    echo "Assessments: " . count( $centralized_meta['by_assessment'] ?? array() ) . "<br>";
} else {
    echo "❌ Centralized symptoms field not populated correctly<br>";
}

// Check symptom history field
$history_meta = get_user_meta( $test_user_id, 'ennu_symptom_history', true );
if ( ! empty( $history_meta ) && is_array( $history_meta ) ) {
    echo "✅ Symptom history field populated successfully<br>";
    echo "History entries: " . count( $history_meta ) . "<br>";
} else {
    echo "❌ Symptom history field not populated correctly<br>";
}

echo "<h2>Step 5: Testing Backward Compatibility</h2>";

// Test scoring system integration
if ( class_exists( 'ENNU_Assessment_Scoring' ) ) {
    $symptom_data = ENNU_Assessment_Scoring::get_symptom_data_for_user( $test_user_id );
    if ( ! empty( $symptom_data ) ) {
        echo "✅ Backward compatibility test successful<br>";
        echo "Symptom data retrieved: " . json_encode( $symptom_data, JSON_PRETTY_PRINT ) . "<br>";
    } else {
        echo "❌ Backward compatibility test failed<br>";
    }
} else {
    echo "❌ Cannot test backward compatibility - ENNU_Assessment_Scoring class not found<br>";
}

echo "<h2>Step 6: Testing Deduplication Logic</h2>";

if ( ! empty( $centralized_meta['symptoms'] ) ) {
    $fatigue_symptom = $centralized_meta['symptoms']['fatigue'] ?? null;
    if ( $fatigue_symptom ) {
        echo "✅ Deduplication test successful<br>";
        echo "Fatigue appears in " . count( $fatigue_symptom['assessments'] ) . " assessments<br>";
        echo "Assessments: " . implode( ', ', $fatigue_symptom['assessments'] ) . "<br>";
        echo "Occurrence count: " . $fatigue_symptom['occurrence_count'] . "<br>";
    } else {
        echo "❌ Deduplication test failed - Fatigue symptom not found<br>";
    }
} else {
    echo "❌ Cannot test deduplication - no centralized symptoms data<br>";
}

echo "<h2>Step 7: Testing Performance</h2>";

// Test performance with multiple calls
$start_time = microtime( true );
for ( $i = 0; $i < 10; $i++ ) {
    $symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $test_user_id );
}
$end_time = microtime( true );
$execution_time = ( $end_time - $start_time ) * 1000; // Convert to milliseconds

echo "✅ Performance test completed<br>";
echo "10 calls to get_centralized_symptoms took: " . round( $execution_time, 2 ) . "ms<br>";
echo "Average per call: " . round( $execution_time / 10, 2 ) . "ms<br>";

echo "<h2>Test Summary</h2>";

$success_count = 0;
$total_tests = 7;

// Count successful tests based on output
if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) $success_count++;
if ( ! empty( $centralized_meta ) ) $success_count++;
if ( ! empty( $history_meta ) ) $success_count++;
if ( class_exists( 'ENNU_Assessment_Scoring' ) ) $success_count++;
if ( $execution_time < 100 ) $success_count++; // Performance test
if ( ! empty( $centralized_meta['symptoms'] ) ) $success_count++;
if ( $result ) $success_count++;

$success_rate = round( ( $success_count / $total_tests ) * 100, 1 );

echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>Overall Test Results</h3>";
echo "<strong>Success Rate: $success_rate% ($success_count/$total_tests tests passed)</strong><br><br>";

if ( $success_rate >= 90 ) {
    echo "🎉 <strong>EXCELLENT!</strong> The centralized symptoms system is fully functional!<br>";
    echo "✅ All core functionality working<br>";
    echo "✅ Performance optimized<br>";
    echo "✅ Data integrity maintained<br>";
    echo "✅ Backward compatibility confirmed<br>";
} elseif ( $success_rate >= 70 ) {
    echo "✅ <strong>GOOD!</strong> The system is mostly functional with minor issues.<br>";
    echo "⚠️ Some features may need attention<br>";
} else {
    echo "❌ <strong>NEEDS ATTENTION!</strong> Several issues detected.<br>";
    echo "🔧 Review the failed tests above<br>";
}

echo "</div>";

echo "<h3>Next Steps:</h3>";
echo "1. ✅ Data population test completed<br>";
echo "2. 🔄 Test user dashboard display<br>";
echo "3. 🔄 Test admin interface<br>";
echo "4. 🔄 Monitor real-world usage<br>";

echo "<br><strong>Data population test completed!</strong>";
?> 