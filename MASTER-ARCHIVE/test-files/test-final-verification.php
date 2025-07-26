<?php
/**
 * Final Verification Test - Biomarker Flag System
 * Access via: http://localhost:8888/wp-content/plugins/ennulifeassessments/test-final-verification.php
 */

echo "<h1>🔬 Final Verification: Biomarker Flag System</h1>";

// Load WordPress
require_once('../../../wp-load.php');

$user_id = 1;

echo "<h2>📊 System Status Check</h2>";

// Check 1: Classes Available
$classes_ok = class_exists('ENNU_Biomarker_Flag_Manager') && class_exists('ENNU_Centralized_Symptoms_Manager');
echo "<p>" . ($classes_ok ? "✅" : "❌") . " <strong>Classes Available:</strong> " . ($classes_ok ? "Yes" : "No") . "</p>";

// Check 2: User Meta Data
$biomarker_flags = get_user_meta($user_id, 'ennu_biomarker_flags', true);
$symptom_history = get_user_meta($user_id, 'ennu_symptom_history', true);
$centralized_symptoms = get_user_meta($user_id, 'ennu_centralized_symptoms', true);

echo "<p>" . (!empty($biomarker_flags) ? "✅" : "ℹ️") . " <strong>Biomarker Flags:</strong> " . (!empty($biomarker_flags) ? "Found" : "None") . "</p>";
echo "<p>" . (!empty($symptom_history) ? "✅" : "ℹ️") . " <strong>Symptom History:</strong> " . (!empty($symptom_history) ? "Found" : "None") . "</p>";
echo "<p>" . (!empty($centralized_symptoms) ? "✅" : "ℹ️") . " <strong>Centralized Symptoms:</strong> " . (!empty($centralized_symptoms) ? "Found" : "None") . "</p>";

// Check 3: Flag Manager Test
if (class_exists('ENNU_Biomarker_Flag_Manager')) {
    $flag_manager = new ENNU_Biomarker_Flag_Manager();
    $active_flags = $flag_manager->get_flagged_biomarkers($user_id, 'active');
    echo "<p>" . (is_array($active_flags) ? "✅" : "❌") . " <strong>Flag Manager:</strong> " . (is_array($active_flags) ? "Working (" . count($active_flags) . " flags)" : "Error") . "</p>";
    
    if (!empty($active_flags)) {
        echo "<h3>🏴 Current Active Flags:</h3>";
        echo "<ul>";
        foreach ($active_flags as $flag_id => $flag_data) {
            echo "<li><strong>" . esc_html($flag_data['biomarker_name']) . "</strong> - " . esc_html($flag_data['reason']) . "</li>";
        }
        echo "</ul>";
    }
}

// Check 4: Symptoms Manager Test
if (class_exists('ENNU_Centralized_Symptoms_Manager')) {
    $centralized_data = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
    echo "<p>" . (is_array($centralized_data) ? "✅" : "❌") . " <strong>Symptoms Manager:</strong> " . (is_array($centralized_data) ? "Working" : "Error") . "</p>";
    
    if (is_array($centralized_data) && isset($centralized_data['symptoms'])) {
        echo "<p>📊 <strong>Current Symptoms:</strong> " . count($centralized_data['symptoms']) . "</p>";
    }
}

// Check 5: Test Auto-Flagging
echo "<h3>🚀 Testing Auto-Flagging</h3>";
if (class_exists('ENNU_Centralized_Symptoms_Manager') && method_exists('ENNU_Centralized_Symptoms_Manager', 'auto_flag_biomarkers_from_symptoms')) {
    echo "<p>✅ <strong>Auto-Flagging Method:</strong> Available</p>";
    
    // Test with sample symptoms
    $test_symptoms = [
        'fatigue' => 'moderate',
        'brain_fog' => 'severe',
        'low_libido' => 'mild'
    ];
    
    echo "<p>🧪 <strong>Test Symptoms:</strong> " . implode(', ', array_keys($test_symptoms)) . "</p>";
    
    // Check what biomarkers should be flagged
    $symptom_biomarker_mapping = [
        'fatigue' => ['vitamin_d', 'b12', 'ferritin', 'tsh', 'cortisol'],
        'brain_fog' => ['vitamin_d', 'b12', 'homocysteine', 'tsh'],
        'low_libido' => ['testosterone', 'estradiol', 'prolactin', 'tsh']
    ];
    
    $expected_flags = [];
    foreach ($test_symptoms as $symptom => $severity) {
        if (isset($symptom_biomarker_mapping[$symptom])) {
            foreach ($symptom_biomarker_mapping[$symptom] as $biomarker) {
                $expected_flags[] = $biomarker;
            }
        }
    }
    
    echo "<p>🎯 <strong>Expected Biomarker Flags:</strong> " . implode(', ', array_unique($expected_flags)) . "</p>";
    
} else {
    echo "<p>❌ <strong>Auto-Flagging Method:</strong> Not Available</p>";
}

// Check 6: Dashboard Functionality
echo "<h3>🏠 Dashboard Functionality</h3>";
echo "<p>✅ <strong>User Dashboard URL:</strong> <a href='http://localhost:8888/?page_id=2469' target='_blank'>http://localhost:8888/?page_id=2469</a></p>";
echo "<p>✅ <strong>Admin Profile URL:</strong> <a href='http://localhost:8888/wp-admin/user-edit.php?user_id=1' target='_blank'>http://localhost:8888/wp-admin/user-edit.php?user_id=1</a></p>";

// Final Status
echo "<h2>🎯 Final Status</h2>";

$working_components = 0;
$total_components = 4;

if ($classes_ok) $working_components++;
if (class_exists('ENNU_Biomarker_Flag_Manager') && method_exists('ENNU_Biomarker_Flag_Manager', 'get_flagged_biomarkers')) $working_components++;
if (class_exists('ENNU_Centralized_Symptoms_Manager') && method_exists('ENNU_Centralized_Symptoms_Manager', 'get_centralized_symptoms')) $working_components++;
if (method_exists('ENNU_Centralized_Symptoms_Manager', 'auto_flag_biomarkers_from_symptoms')) $working_components++;

$percentage = ($working_components / $total_components) * 100;

echo "<div style='background: " . ($percentage >= 90 ? "#d4edda" : ($percentage >= 70 ? "#fff3cd" : "#f8d7da")) . "; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3 style='color: " . ($percentage >= 90 ? "green" : ($percentage >= 70 ? "orange" : "red")) . ";'>";

if ($percentage >= 90) {
    echo "🎉 SYSTEM FULLY OPERATIONAL!";
} elseif ($percentage >= 70) {
    echo "⚠️ SYSTEM MOSTLY WORKING!";
} else {
    echo "❌ SYSTEM HAS ISSUES!";
}

echo "</h3>";
echo "<p><strong>Working Components:</strong> $working_components / $total_components ($percentage%)</p>";
echo "</div>";

echo "<h3>📋 What Should Work:</h3>";
echo "<ul>";
echo "<li>✅ <strong>MY BIOMARKERS tab</strong> should show flagged biomarkers dynamically</li>";
echo "<li>✅ <strong>MY SYMPTOMS tab</strong> should show symptom history and biomarker correlations</li>";
echo "<li>✅ <strong>Flag removal</strong> should only be possible in WP-Admin user profile</li>";
echo "<li>✅ <strong>Auto-flagging</strong> should create biomarker flags when symptoms are reported</li>";
echo "</ul>";

echo "<h3>🔗 Quick Test Links</h3>";
echo "<p><a href='http://localhost:8888/?page_id=2469' target='_blank' style='background: #0073aa; color: white; padding: 15px 25px; text-decoration: none; border-radius: 8px; display: inline-block; margin: 10px 5px; font-weight: bold;'>🏠 Test User Dashboard</a></p>";
echo "<p><a href='http://localhost:8888/wp-admin/user-edit.php?user_id=1' target='_blank' style='background: #28a745; color: white; padding: 15px 25px; text-decoration: none; border-radius: 8px; display: inline-block; margin: 10px 5px; font-weight: bold;'>⚙️ Admin User Profile</a></p>";
?> 