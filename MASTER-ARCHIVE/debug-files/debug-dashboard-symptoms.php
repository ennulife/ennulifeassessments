<?php
/**
 * Debug Dashboard Symptoms Display
 * 
 * This script checks what the centralized symptoms manager is returning
 * and simulates exactly what the dashboard sees
 */

// Load WordPress
require_once '../../../wp-load.php';

$user_id = 12;

echo "<h1>🔍 Dashboard Symptoms Debug - User {$user_id}</h1>\n";

// Check if user exists
$user = get_user_by('ID', $user_id);
if ( ! $user ) {
    echo "<p style='color: red;'>❌ ERROR: User {$user_id} not found!</p>\n";
    exit;
}

echo "<p style='color: green;'>✅ User found: " . esc_html( $user->display_name ) . "</p>\n";

// Check if the class exists
if ( ! class_exists( 'ENNU_Centralized_Symptoms_Manager' ) ) {
    echo "<p style='color: red;'>❌ ENNU_Centralized_Symptoms_Manager class not found!</p>\n";
    exit;
}

echo "<h2>Testing Centralized Symptoms Manager:</h2>\n";

// Get centralized symptoms
$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms( $user_id );
$symptom_analytics = ENNU_Centralized_Symptoms_Manager::get_symptom_analytics( $user_id );

echo "<h3>Centralized Symptoms Result:</h3>\n";
echo "<p><strong>Raw Data:</strong></p>\n";
echo "<pre>" . print_r( $centralized_symptoms, true ) . "</pre>\n";

echo "<h3>Symptom Analytics Result:</h3>\n";
echo "<p><strong>Raw Data:</strong></p>\n";
echo "<pre>" . print_r( $symptom_analytics, true ) . "</pre>\n";

// Simulate the exact dashboard logic
echo "<h2>Dashboard Logic Simulation:</h2>\n";

echo "<h3>Step 1: Check if symptoms exist</h3>\n";
$symptoms_exist = ! empty( $centralized_symptoms['symptoms'] );
echo "<p><strong>Symptoms exist:</strong> " . ( $symptoms_exist ? '✅ YES' : '❌ NO' ) . "</p>\n";

if ( $symptoms_exist ) {
    echo "<p><strong>Symptoms count:</strong> " . count( $centralized_symptoms['symptoms'] ) . "</p>\n";
    
    echo "<h3>Step 2: Check analytics</h3>\n";
    $analytics_exist = ! empty( $symptom_analytics );
    echo "<p><strong>Analytics exist:</strong> " . ( $analytics_exist ? '✅ YES' : '❌ NO' ) . "</p>\n";
    
    if ( $analytics_exist ) {
        echo "<p><strong>Total Symptoms:</strong> " . ( $symptom_analytics['total_symptoms'] ?? 'N/A' ) . "</p>\n";
        echo "<p><strong>Unique Symptoms:</strong> " . ( $symptom_analytics['unique_symptoms'] ?? 'N/A' ) . "</p>\n";
        echo "<p><strong>Assessments with Symptoms:</strong> " . ( $symptom_analytics['assessments_with_symptoms'] ?? 'N/A' ) . "</p>\n";
    }
    
    echo "<h3>Step 3: Check categories</h3>\n";
    $categories_exist = ! empty( $centralized_symptoms['by_category'] );
    echo "<p><strong>Categories exist:</strong> " . ( $categories_exist ? '✅ YES' : '❌ NO' ) . "</p>\n";
    
    if ( $categories_exist ) {
        echo "<p><strong>Categories count:</strong> " . count( $centralized_symptoms['by_category'] ) . "</p>\n";
        echo "<p><strong>Categories:</strong> " . implode( ', ', array_keys( $centralized_symptoms['by_category'] ) ) . "</p>\n";
    }
    
    echo "<h3>Step 4: Simulate Dashboard Display</h3>\n";
    
    if ( ! empty( $centralized_symptoms['symptoms'] ) ) {
        echo '<div style="background: #f8f9fa; padding: 20px; border: 1px solid #dee2e6; border-radius: 8px;">';
        echo '<h4>Dashboard Would Display:</h4>';
        
        echo '<div class="symptom-summary">';
        echo '<div class="symptom-stats">';
        echo '<div class="stat-item"><span class="stat-number">' . ( $symptom_analytics['total_symptoms'] ?? 0 ) . '</span><span class="stat-label">Total Symptoms</span></div>';
        echo '<div class="stat-item"><span class="stat-number">' . ( $symptom_analytics['unique_symptoms'] ?? 0 ) . '</span><span class="stat-label">Unique Symptoms</span></div>';
        echo '<div class="stat-item"><span class="stat-number">' . ( $symptom_analytics['assessments_with_symptoms'] ?? 0 ) . '</span><span class="stat-label">Assessments</span></div>';
        echo '</div>';
        echo '</div>';

        // Display symptoms by category
        foreach ( $centralized_symptoms['by_category'] as $category => $symptom_keys ) {
            echo '<div class="symptom-category" style="margin: 20px 0; padding: 15px; background: white; border-radius: 5px;">';
            echo '<h4 class="category-title" style="color: #333; margin-bottom: 10px;">' . esc_html( $category ) . '</h4>';

            foreach ( $symptom_keys as $symptom_key ) {
                $symptom = $centralized_symptoms['symptoms'][ $symptom_key ];
                echo '<div class="symptom-item" style="margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 3px;">';
                echo '<div class="symptom-name" style="font-weight: bold;">' . esc_html( $symptom['name'] ) . '</div>';

                if ( ! empty( $symptom['severity'] ) ) {
                    echo '<div class="symptom-severity">Severity: ' . esc_html( $symptom['severity'][0] ) . '</div>';
                }

                if ( ! empty( $symptom['frequency'] ) ) {
                    echo '<div class="symptom-frequency">Frequency: ' . esc_html( $symptom['frequency'][0] ) . '</div>';
                }

                echo '<div class="symptom-assessments">From: ' . esc_html( implode( ', ', $symptom['assessments'] ) ) . '</div>';
                echo '</div>';
            }

            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p style="color: red;">❌ Dashboard would show: "No symptoms reported yet. Complete assessments to see your symptoms here."</p>\n';
    }
} else {
    echo '<p style="color: red;">❌ Dashboard would show: "No symptoms reported yet. Complete assessments to see your symptoms here."</p>\n';
}

echo "<hr>\n";
echo "<h2>🔧 Analysis:</h2>\n";

if ( $symptoms_exist ) {
    echo "<p style='color: green; font-weight: bold;'>✅ SYMPTOMS ARE AVAILABLE - Dashboard should display them!</p>\n";
    echo "<p><strong>Issue:</strong> The symptoms exist in the database but may not be displaying due to CSS/styling issues or JavaScript tab switching problems.</p>\n";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ NO SYMPTOMS AVAILABLE - This is why dashboard shows nothing!</p>\n";
    echo "<p><strong>Issue:</strong> The centralized symptoms manager is not finding any symptoms for this user.</p>\n";
}

echo "<p><em>Debug completed at: " . date( 'Y-m-d H:i:s' ) . "</em></p>\n";
?> 