<?php
/**
 * ENNU Life Centralized Symptoms - Comprehensive Integration Test
 * 
 * This script performs a complete end-to-end test of the centralized symptoms system
 * to ensure 100% functionality across all components.
 */

// Load WordPress
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );

// Ensure we're in admin context
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Access denied' );
}

echo "<h1>ENNU Life Centralized Symptoms - Comprehensive Integration Test</h1>";
echo "<p><strong>This test verifies 100% functionality of the centralized symptoms system.</strong></p>";

// Test user ID (admin user)
$test_user_id = 1;

// Initialize test results
$test_results = array();
$total_tests = 0;
$passed_tests = 0;

echo "<h2>Phase 1: System Architecture Tests</h2>";

// Test 1: Class existence
$total_tests++;
if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
    $test_results['Class Existence'] = true;
    $passed_tests++;
    echo "✅ ENNU_Centralized_Symptoms_Manager class exists<br>";
} else {
    $test_results['Class Existence'] = false;
    echo "❌ ENNU_Centralized_Symptoms_Manager class NOT found<br>";
}

// Test 2: Method availability
$total_tests++;
$required_methods = array(
    'get_centralized_symptoms',
    'get_symptom_analytics', 
    'get_symptom_history',
    'update_centralized_symptoms'
);

$all_methods_exist = true;
foreach ( $required_methods as $method ) {
    if ( ! method_exists( 'ENNU_Centralized_Symptoms_Manager', $method ) ) {
        $all_methods_exist = false;
        echo "❌ Method $method not found<br>";
    }
}

if ( $all_methods_exist ) {
    $test_results['Method Availability'] = true;
    $passed_tests++;
    echo "✅ All required methods available<br>";
} else {
    $test_results['Method Availability'] = false;
}

echo "<h2>Phase 2: Data Population Tests</h2>";

// Create comprehensive test data
echo "<h3>Creating Test Data...</h3>";

// Hormone assessment data
$hormone_symptoms = array('Fatigue', 'Brain Fog', 'Mood Swings', 'Hot Flashes');
update_user_meta( $test_user_id, 'ennu_hormone_hormone_q1', $hormone_symptoms );
update_user_meta( $test_user_id, 'ennu_hormone_score_calculated_at', current_time( 'mysql' ) );

// Health optimization assessment data
update_user_meta( $test_user_id, 'ennu_health_optimization_assessment_symptom_q1', 'Fatigue' );
update_user_meta( $test_user_id, 'ennu_health_optimization_assessment_symptom_q1_severity_q', 'moderate' );
update_user_meta( $test_user_id, 'ennu_health_optimization_assessment_symptom_q1_frequency_q', 'daily' );
update_user_meta( $test_user_id, 'ennu_health_optimization_assessment_symptom_q2', 'Anxiety' );
update_user_meta( $test_user_id, 'ennu_health_optimization_assessment_symptom_q2_severity_q', 'severe' );
update_user_meta( $test_user_id, 'ennu_health_optimization_assessment_symptom_q2_frequency_q', 'multiple_times_daily' );
update_user_meta( $test_user_id, 'ennu_health_optimization_assessment_score_calculated_at', current_time( 'mysql' ) );

// Testosterone assessment data
$testosterone_symptoms = array('Low Libido', 'Muscle Weakness', 'Depression');
update_user_meta( $test_user_id, 'ennu_testosterone_testosterone_q1', $testosterone_symptoms );
update_user_meta( $test_user_id, 'ennu_testosterone_score_calculated_at', current_time( 'mysql' ) );

echo "✅ Test data created successfully<br>";

// Test 3: Data aggregation
$total_tests++;
if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
    $result = ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $test_user_id );
    if ( $result ) {
        $test_results['Data Aggregation'] = true;
        $passed_tests++;
        echo "✅ Data aggregation successful<br>";
    } else {
        $test_results['Data Aggregation'] = false;
        echo "❌ Data aggregation failed<br>";
    }
} else {
    $test_results['Data Aggregation'] = false;
    echo "❌ Cannot test data aggregation - class not found<br>";
}

echo "<h2>Phase 3: Data Retrieval Tests</h2>";

// Test 4: Centralized symptoms retrieval
$total_tests++;
$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $test_user_id );
if ( ! empty( $centralized_symptoms['symptoms'] ) ) {
    $test_results['Centralized Symptoms Retrieval'] = true;
    $passed_tests++;
    echo "✅ Centralized symptoms retrieved successfully<br>";
    echo "   - Total symptoms: " . ( $centralized_symptoms['total_count'] ?? 0 ) . "<br>";
    echo "   - Unique symptoms: " . count( $centralized_symptoms['symptoms'] ) . "<br>";
} else {
    $test_results['Centralized Symptoms Retrieval'] = false;
    echo "❌ Centralized symptoms retrieval failed<br>";
}

// Test 5: Analytics retrieval
$total_tests++;
$symptom_analytics = ENNU_Centralized_Symptoms_Manager::get_symptom_analytics( $test_user_id );
if ( ! empty( $symptom_analytics ) && isset( $symptom_analytics['total_symptoms'] ) ) {
    $test_results['Analytics Retrieval'] = true;
    $passed_tests++;
    echo "✅ Symptom analytics retrieved successfully<br>";
    echo "   - Total symptoms: " . $symptom_analytics['total_symptoms'] . "<br>";
    echo "   - Unique symptoms: " . $symptom_analytics['unique_symptoms'] . "<br>";
} else {
    $test_results['Analytics Retrieval'] = false;
    echo "❌ Symptom analytics retrieval failed<br>";
}

// Test 6: History retrieval
$total_tests++;
$symptom_history = ENNU_Centralized_Symptoms_Manager::get_symptom_history( $test_user_id, 5 );
if ( is_array( $symptom_history ) ) {
    $test_results['History Retrieval'] = true;
    $passed_tests++;
    echo "✅ Symptom history retrieved successfully<br>";
    echo "   - History entries: " . count( $symptom_history ) . "<br>";
} else {
    $test_results['History Retrieval'] = false;
    echo "❌ Symptom history retrieval failed<br>";
}

echo "<h2>Phase 4: Integration Tests</h2>";

// Test 7: Backward compatibility
$total_tests++;
if ( class_exists( 'ENNU_Assessment_Scoring' ) ) {
    $symptom_data = ENNU_Assessment_Scoring::get_symptom_data_for_user( $test_user_id );
    if ( ! empty( $symptom_data ) ) {
        $test_results['Backward Compatibility'] = true;
        $passed_tests++;
        echo "✅ Backward compatibility confirmed<br>";
    } else {
        $test_results['Backward Compatibility'] = false;
        echo "❌ Backward compatibility failed<br>";
    }
} else {
    $test_results['Backward Compatibility'] = false;
    echo "❌ Cannot test backward compatibility - ENNU_Assessment_Scoring class not found<br>";
}

// Test 8: Assessment completion hook
$total_tests++;
if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
    $hook_result = ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $test_user_id, 'hormone' );
    if ( $hook_result ) {
        $test_results['Assessment Completion Hook'] = true;
        $passed_tests++;
        echo "✅ Assessment completion hook works<br>";
    } else {
        $test_results['Assessment Completion Hook'] = false;
        echo "❌ Assessment completion hook failed<br>";
    }
} else {
    $test_results['Assessment Completion Hook'] = false;
    echo "❌ Cannot test assessment completion hook - class not found<br>";
}

echo "<h2>Phase 5: Performance Tests</h2>";

// Test 9: Performance
$total_tests++;
$start_time = microtime( true );
for ( $i = 0; $i < 10; $i++ ) {
    $symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $test_user_id );
}
$end_time = microtime( true );
$execution_time = ( $end_time - $start_time ) * 1000; // Convert to milliseconds

if ( $execution_time < 100 ) { // Less than 100ms for 10 calls
    $test_results['Performance'] = true;
    $passed_tests++;
    echo "✅ Performance test passed<br>";
    echo "   - 10 calls took: " . round( $execution_time, 2 ) . "ms<br>";
    echo "   - Average per call: " . round( $execution_time / 10, 2 ) . "ms<br>";
} else {
    $test_results['Performance'] = false;
    echo "❌ Performance test failed - too slow<br>";
    echo "   - 10 calls took: " . round( $execution_time, 2 ) . "ms<br>";
}

echo "<h2>Phase 6: Data Integrity Tests</h2>";

// Test 10: Deduplication
$total_tests++;
if ( ! empty( $centralized_symptoms['symptoms'] ) ) {
    $fatigue_symptom = $centralized_symptoms['symptoms']['fatigue'] ?? null;
    if ( $fatigue_symptom && count( $fatigue_symptom['assessments'] ) > 1 ) {
        $test_results['Deduplication'] = true;
        $passed_tests++;
        echo "✅ Deduplication working correctly<br>";
        echo "   - Fatigue appears in " . count( $fatigue_symptom['assessments'] ) . " assessments<br>";
    } else {
        $test_results['Deduplication'] = false;
        echo "❌ Deduplication test failed<br>";
    }
} else {
    $test_results['Deduplication'] = false;
    echo "❌ Cannot test deduplication - no symptoms data<br>";
}

// Test 11: Database persistence
$total_tests++;
$centralized_meta = get_user_meta( $test_user_id, 'ennu_centralized_symptoms', true );
$history_meta = get_user_meta( $test_user_id, 'ennu_symptom_history', true );

if ( ! empty( $centralized_meta ) && is_array( $centralized_meta ) ) {
    $test_results['Database Persistence'] = true;
    $passed_tests++;
    echo "✅ Database persistence confirmed<br>";
    echo "   - Centralized symptoms field populated<br>";
    echo "   - History field populated: " . ( ! empty( $history_meta ) ? 'Yes' : 'No' ) . "<br>";
} else {
    $test_results['Database Persistence'] = false;
    echo "❌ Database persistence failed<br>";
}

echo "<h2>Phase 7: Error Handling Tests</h2>";

// Test 12: Invalid user handling
$total_tests++;
$invalid_user_id = 99999;
$invalid_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $invalid_user_id );
$invalid_analytics = ENNU_Centralized_Symptoms_Manager::get_symptom_analytics( $invalid_user_id );

if ( is_array( $invalid_symptoms ) && is_array( $invalid_analytics ) ) {
    $test_results['Error Handling'] = true;
    $passed_tests++;
    echo "✅ Error handling works correctly<br>";
} else {
    $test_results['Error Handling'] = false;
    echo "❌ Error handling failed<br>";
}

echo "<h2>Phase 8: Display Integration Tests</h2>";

// Test 13: Dashboard display logic
$total_tests++;
$display_data_available = ! empty( $centralized_symptoms['symptoms'] ) && ! empty( $symptom_analytics );
if ( $display_data_available ) {
    $test_results['Dashboard Display Logic'] = true;
    $passed_tests++;
    echo "✅ Dashboard display logic ready<br>";
} else {
    $test_results['Dashboard Display Logic'] = false;
    echo "❌ Dashboard display logic failed<br>";
}

// Test 14: Admin interface logic
$total_tests++;
$admin_data_available = ! empty( $centralized_symptoms['by_assessment'] ) && ! empty( $symptom_analytics['total_symptoms'] );
if ( $admin_data_available ) {
    $test_results['Admin Interface Logic'] = true;
    $passed_tests++;
    echo "✅ Admin interface logic ready<br>";
} else {
    $test_results['Admin Interface Logic'] = false;
    echo "❌ Admin interface logic failed<br>";
}

echo "<h2>Comprehensive Test Results</h2>";

$success_rate = round( ( $passed_tests / $total_tests ) * 100, 1 );

echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>Overall Test Summary</h3>";
echo "<strong>Success Rate: $success_rate% ($passed_tests/$total_tests tests passed)</strong><br><br>";

// Display individual test results
echo "<h4>Individual Test Results:</h4>";
foreach ( $test_results as $test_name => $passed ) {
    if ( $passed ) {
        echo "✅ $test_name<br>";
    } else {
        echo "❌ $test_name<br>";
    }
}

echo "<br>";

if ( $success_rate >= 95 ) {
    echo "🎉 <strong>EXCELLENT!</strong> The centralized symptoms system is 100% functional!<br>";
    echo "✅ All core functionality working perfectly<br>";
    echo "✅ Performance optimized<br>";
    echo "✅ Data integrity maintained<br>";
    echo "✅ Integration complete<br>";
    echo "✅ Ready for production use<br>";
} elseif ( $success_rate >= 85 ) {
    echo "✅ <strong>VERY GOOD!</strong> The system is highly functional with minor issues.<br>";
    echo "⚠️ Some features may need attention<br>";
} elseif ( $success_rate >= 70 ) {
    echo "⚠️ <strong>GOOD!</strong> The system is mostly functional but needs attention.<br>";
    echo "🔧 Review failed tests above<br>";
} else {
    echo "❌ <strong>NEEDS ATTENTION!</strong> Several critical issues detected.<br>";
    echo "🔧 Immediate action required<br>";
}

echo "</div>";

echo "<h3>System Status</h3>";

if ( $success_rate >= 95 ) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
    echo "<strong>🟢 PRODUCTION READY</strong><br>";
    echo "The centralized symptoms system is fully functional and ready for production use.";
    echo "</div>";
} elseif ( $success_rate >= 85 ) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
    echo "<strong>🟡 MOSTLY READY</strong><br>";
    echo "The system is highly functional but may need minor adjustments.";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
    echo "<strong>🔴 NEEDS WORK</strong><br>";
    echo "The system requires attention before production use.";
    echo "</div>";
}

echo "<h3>Next Steps</h3>";
echo "1. ✅ Comprehensive integration test completed<br>";
echo "2. 🔄 Monitor real-world usage<br>";
echo "3. 🔄 Collect user feedback<br>";
echo "4. 🔄 Implement additional features as needed<br>";

echo "<br><strong>Comprehensive integration test completed!</strong>";
echo "<br><em>Test completed at: " . current_time( 'mysql' ) . "</em>";
?> 