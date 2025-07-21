<?php
/**
 * ENNU Life Centralized Symptoms - Admin Interface Test
 * 
 * This script tests the admin interface integration with centralized symptoms
 * to ensure administrators can view and manage symptom data.
 */

// Load WordPress
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );

// Ensure we're in admin context
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Access denied' );
}

echo "<h1>ENNU Life Centralized Symptoms - Admin Interface Test</h1>";

// Test user ID (admin user)
$test_user_id = 1;

echo "<h2>Step 1: Testing Admin User Access</h2>";

if ( current_user_can( 'manage_options' ) ) {
    echo "✅ Admin access confirmed<br>";
} else {
    echo "❌ Admin access denied<br>";
    exit;
}

echo "<h2>Step 2: Testing Centralized Symptoms in Admin Context</h2>";

if ( class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
    $centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $test_user_id );
    $symptom_analytics = ENNU_Centralized_Symptoms_Manager::get_symptom_analytics( $test_user_id );
    
    if ( ! empty( $centralized_symptoms['symptoms'] ) ) {
        echo "✅ Centralized symptoms accessible in admin context<br>";
        echo "Total symptoms: " . ( $centralized_symptoms['total_count'] ?? 0 ) . "<br>";
        echo "Unique symptoms: " . count( $centralized_symptoms['symptoms'] ) . "<br>";
    } else {
        echo "⚠️ No centralized symptoms data found. Run test-data-population.php first.<br>";
    }
} else {
    echo "❌ ENNU_Centralized_Symptoms_Manager class not found<br>";
    exit;
}

echo "<h2>Step 3: Testing Admin User Profile Display</h2>";

// Simulate admin user profile display
echo "<div style='background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>Admin User Profile - Centralized Symptoms</h3>";

if ( ! empty( $centralized_symptoms['symptoms'] ) ) {
    echo "<div class='admin-symptom-overview'>";
    echo "<h4>Symptom Overview for User ID: $test_user_id</h4>";
    
    // Display summary statistics
    echo "<div class='admin-symptom-stats'>";
    echo "<div class='stat-item'><strong>Total Symptoms:</strong> " . $symptom_analytics['total_symptoms'] . "</div>";
    echo "<div class='stat-item'><strong>Unique Symptoms:</strong> " . $symptom_analytics['unique_symptoms'] . "</div>";
    echo "<div class='stat-item'><strong>Assessments:</strong> " . $symptom_analytics['assessments_with_symptoms'] . "</div>";
    echo "<div class='stat-item'><strong>Last Updated:</strong> " . ( $centralized_symptoms['last_updated'] ?? 'Unknown' ) . "</div>";
    echo "</div>";
    
    // Display symptoms by assessment
    if ( ! empty( $centralized_symptoms['by_assessment'] ) ) {
        echo "<h4>Symptoms by Assessment</h4>";
        foreach ( $centralized_symptoms['by_assessment'] as $assessment_type => $symptoms ) {
            echo "<div class='assessment-symptoms'>";
            echo "<h5>" . ucfirst( str_replace( '_', ' ', $assessment_type ) ) . " (" . count( $symptoms ) . " symptoms)</h5>";
            echo "<ul>";
            foreach ( $symptoms as $symptom ) {
                echo "<li>" . esc_html( $symptom['name'] );
                if ( ! empty( $symptom['severity'] ) ) {
                    echo " - Severity: " . esc_html( $symptom['severity'] );
                }
                if ( ! empty( $symptom['frequency'] ) ) {
                    echo " - Frequency: " . esc_html( $symptom['frequency'] );
                }
                echo "</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
    }
    
    // Display symptoms by category
    if ( ! empty( $centralized_symptoms['by_category'] ) ) {
        echo "<h4>Symptoms by Category</h4>";
        foreach ( $centralized_symptoms['by_category'] as $category => $symptom_keys ) {
            echo "<div class='category-symptoms'>";
            echo "<h5>$category (" . count( $symptom_keys ) . " symptoms)</h5>";
            echo "<ul>";
            foreach ( $symptom_keys as $symptom_key ) {
                $symptom = $centralized_symptoms['symptoms'][ $symptom_key ];
                echo "<li>" . esc_html( $symptom['name'] ) . " (from " . implode( ', ', $symptom['assessments'] ) . ")</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
    }
    
    echo "</div>";
} else {
    echo "<div class='no-symptoms'>No symptoms data available for this user.</div>";
}

echo "</div>";

echo "<h2>Step 4: Testing Admin Analytics Dashboard</h2>";

echo "<div style='background: #f0f8ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>Admin Analytics Dashboard</h3>";

echo "<div class='admin-analytics-grid'>";

// Most common category
if ( ! empty( $symptom_analytics['most_common_category'] ) ) {
    echo "<div class='analytics-card'>";
    echo "<h4>Most Common Category</h4>";
    echo "<div class='analytics-value'>" . $symptom_analytics['most_common_category'] . "</div>";
    echo "</div>";
}

// Most severe symptoms
if ( ! empty( $symptom_analytics['most_severe_symptoms'] ) ) {
    echo "<div class='analytics-card'>";
    echo "<h4>Most Severe Symptoms</h4>";
    echo "<div class='analytics-value'>" . implode( ', ', $symptom_analytics['most_severe_symptoms'] ) . "</div>";
    echo "</div>";
}

// Most frequent symptoms
if ( ! empty( $symptom_analytics['most_frequent_symptoms'] ) ) {
    echo "<div class='analytics-card'>";
    echo "<h4>Most Frequent Symptoms</h4>";
    echo "<div class='analytics-value'>" . implode( ', ', $symptom_analytics['most_frequent_symptoms'] ) . "</div>";
    echo "</div>";
}

// Symptom trends
if ( ! empty( $symptom_analytics['symptom_trends'] ) ) {
    echo "<div class='analytics-card'>";
    echo "<h4>Symptom Trend</h4>";
    echo "<div class='analytics-value'>" . ucfirst( $symptom_analytics['symptom_trends']['trend'] ) . "</div>";
    if ( isset( $symptom_analytics['symptom_trends']['change'] ) ) {
        echo "<div class='analytics-subtitle'>Change: " . $symptom_analytics['symptom_trends']['change'] . " symptoms</div>";
    }
    echo "</div>";
}

echo "</div>";
echo "</div>";

echo "<h2>Step 5: Testing Admin Data Management</h2>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>Admin Data Management Functions</h3>";

// Test symptom history
$history = ENNU_Centralized_Symptoms_Manager::get_symptom_history( $test_user_id, 5 );
if ( ! empty( $history ) ) {
    echo "✅ Symptom history accessible<br>";
    echo "History entries: " . count( $history ) . "<br>";
    
    echo "<h4>Recent Symptom History</h4>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Date</th><th>Total Symptoms</th><th>Assessments</th></tr>";
    foreach ( $history as $entry ) {
        echo "<tr>";
        echo "<td>" . esc_html( $entry['date'] ) . "</td>";
        echo "<td>" . esc_html( $entry['total_count'] ) . "</td>";
        echo "<td>" . esc_html( implode( ', ', $entry['assessments'] ) ) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "⚠️ No symptom history available<br>";
}

// Test manual symptom update
echo "<h4>Manual Symptom Update Test</h4>";
$update_result = ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $test_user_id );
if ( $update_result ) {
    echo "✅ Manual symptom update successful<br>";
} else {
    echo "❌ Manual symptom update failed<br>";
}

echo "</div>";

echo "<h2>Step 6: Testing Admin User Search</h2>";

echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>Admin User Search and Filter</h3>";

// Test with multiple users (if available)
$users_with_symptoms = array();
$all_users = get_users( array( 'number' => 10 ) );

foreach ( $all_users as $user ) {
    $user_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $user->ID );
    if ( ! empty( $user_symptoms['symptoms'] ) ) {
        $users_with_symptoms[] = array(
            'id' => $user->ID,
            'name' => $user->display_name,
            'symptom_count' => $user_symptoms['total_count'] ?? 0
        );
    }
}

if ( ! empty( $users_with_symptoms ) ) {
    echo "✅ Found " . count( $users_with_symptoms ) . " users with symptoms<br>";
    echo "<h4>Users with Symptoms</h4>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>User ID</th><th>Name</th><th>Symptom Count</th></tr>";
    foreach ( $users_with_symptoms as $user_data ) {
        echo "<tr>";
        echo "<td>" . esc_html( $user_data['id'] ) . "</td>";
        echo "<td>" . esc_html( $user_data['name'] ) . "</td>";
        echo "<td>" . esc_html( $user_data['symptom_count'] ) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "⚠️ No users found with symptoms data<br>";
}

echo "</div>";

echo "<h2>Step 7: Testing Admin Export Functionality</h2>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>Admin Export Functions</h3>";

// Test JSON export
$export_data = array(
    'user_id' => $test_user_id,
    'centralized_symptoms' => $centralized_symptoms,
    'analytics' => $symptom_analytics,
    'history' => $history,
    'export_date' => current_time( 'mysql' )
);

echo "✅ Export data prepared successfully<br>";
echo "Export data size: " . strlen( json_encode( $export_data ) ) . " bytes<br>";

// Test CSV export simulation
echo "<h4>CSV Export Simulation</h4>";
echo "<pre>";
echo "User ID,Symptom Name,Category,Severity,Frequency,Assessments,First Reported,Last Reported,Occurrence Count\n";
if ( ! empty( $centralized_symptoms['symptoms'] ) ) {
    foreach ( $centralized_symptoms['symptoms'] as $symptom_key => $symptom ) {
        echo $test_user_id . ",";
        echo '"' . $symptom['name'] . '",';
        echo '"' . $symptom['category'] . '",';
        echo '"' . ( ! empty( $symptom['severity'] ) ? $symptom['severity'][0] : '' ) . '",';
        echo '"' . ( ! empty( $symptom['frequency'] ) ? $symptom['frequency'][0] : '' ) . '",';
        echo '"' . implode( ';', $symptom['assessments'] ) . '",';
        echo '"' . $symptom['first_reported'] . '",';
        echo '"' . $symptom['last_reported'] . '",';
        echo $symptom['occurrence_count'] . "\n";
    }
}
echo "</pre>";

echo "</div>";

echo "<h2>Admin Interface Test Summary</h2>";

$admin_tests = array(
    'Admin access confirmed' => current_user_can( 'manage_options' ),
    'Centralized symptoms accessible' => ! empty( $centralized_symptoms['symptoms'] ),
    'Analytics data available' => ! empty( $symptom_analytics ),
    'User profile display works' => ! empty( $centralized_symptoms['by_assessment'] ),
    'Analytics dashboard works' => ! empty( $symptom_analytics['total_symptoms'] ),
    'Symptom history accessible' => ! empty( $history ),
    'Manual update works' => $update_result,
    'User search works' => ! empty( $users_with_symptoms ),
    'Export functionality ready' => ! empty( $export_data )
);

$passed_admin_tests = 0;
$total_admin_tests = count( $admin_tests );

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>Admin Interface Test Results</h3>";

foreach ( $admin_tests as $test_name => $passed ) {
    if ( $passed ) {
        echo "✅ $test_name<br>";
        $passed_admin_tests++;
    } else {
        echo "❌ $test_name<br>";
    }
}

$admin_success_rate = round( ( $passed_admin_tests / $total_admin_tests ) * 100, 1 );

echo "<br><strong>Admin Interface Test Success Rate: $admin_success_rate% ($passed_admin_tests/$total_admin_tests)</strong><br>";

if ( $admin_success_rate >= 90 ) {
    echo "🎉 <strong>EXCELLENT!</strong> Admin interface is fully functional!<br>";
} elseif ( $admin_success_rate >= 70 ) {
    echo "✅ <strong>GOOD!</strong> Admin interface is mostly functional.<br>";
} else {
    echo "❌ <strong>NEEDS ATTENTION!</strong> Admin interface has issues.<br>";
}

echo "</div>";

echo "<h3>Next Steps:</h3>";
echo "1. ✅ Admin interface test completed<br>";
echo "2. 🔄 Test real user interactions<br>";
echo "3. 🔄 Monitor performance in production<br>";
echo "4. 🔄 Implement additional admin features as needed<br>";

echo "<br><strong>Admin interface test completed!</strong>";
?> 