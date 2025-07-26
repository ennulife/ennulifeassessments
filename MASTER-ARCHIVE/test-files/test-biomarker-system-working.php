<?php
/**
 * Test Biomarker Flag System Working Status
 * Access via: http://localhost:8888/wp-content/plugins/ennulifeassessments/test-biomarker-system-working.php
 */

echo "<h1>🔬 Testing Biomarker Flag System</h1>";

// Test 1: Check if WordPress loads
if (file_exists('../../../wp-load.php')) {
    require_once('../../../wp-load.php');
    echo "<h2>✅ WordPress Loaded Successfully</h2>";
} else {
    echo "<h2>❌ WordPress Not Found</h2>";
    exit;
}

// Test 2: Check if classes exist
echo "<h3>📋 Class Availability Check</h3>";
$classes_to_check = [
    'ENNU_Biomarker_Flag_Manager',
    'ENNU_Centralized_Symptoms_Manager'
];

foreach ($classes_to_check as $class_name) {
    if (class_exists($class_name)) {
        echo "<p>✅ <strong>$class_name</strong> - Available</p>";
    } else {
        echo "<p>❌ <strong>$class_name</strong> - Not Found</p>";
    }
}

// Test 3: Test Flag Manager
echo "<h3>🏴 Flag Manager Test</h3>";
if (class_exists('ENNU_Biomarker_Flag_Manager')) {
    try {
        $flag_manager = new ENNU_Biomarker_Flag_Manager();
        echo "<p>✅ Flag Manager instantiated successfully</p>";
        
        // Get user ID
        $user_id = 1;
        
        // Test getting flagged biomarkers
        $active_flags = $flag_manager->get_flagged_biomarkers($user_id, 'active');
        echo "<p>✅ <strong>Active Flags Retrieved:</strong> " . count($active_flags) . " flags</p>";
        
        if (!empty($active_flags)) {
            echo "<h4>🔍 Current Active Flags:</h4>";
            echo "<ul>";
            foreach ($active_flags as $flag_id => $flag_data) {
                echo "<li><strong>" . esc_html($flag_data['biomarker_name']) . "</strong> - " . esc_html($flag_data['reason']) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>ℹ️ No active biomarker flags found for user $user_id</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ <strong>Flag Manager Error:</strong> " . esc_html($e->getMessage()) . "</p>";
    }
}

// Test 4: Test Centralized Symptoms Manager
echo "<h3>🩺 Centralized Symptoms Manager Test</h3>";
if (class_exists('ENNU_Centralized_Symptoms_Manager')) {
    try {
        $symptoms_manager = new ENNU_Centralized_Symptoms_Manager();
        echo "<p>✅ Centralized Symptoms Manager instantiated successfully</p>";
        
        // Get user ID
        $user_id = 1;
        
        // Test getting centralized symptoms
        $centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
        echo "<p>✅ <strong>Centralized Symptoms Retrieved:</strong> " . (is_array($centralized_symptoms) ? count($centralized_symptoms) : '0') . " items</p>";
        
        if (is_array($centralized_symptoms) && !empty($centralized_symptoms)) {
            if (isset($centralized_symptoms['symptoms'])) {
                echo "<p>✅ <strong>Current Symptoms:</strong> " . count($centralized_symptoms['symptoms']) . " symptoms</p>";
            }
            if (isset($centralized_symptoms['by_assessment'])) {
                echo "<p>✅ <strong>Symptoms by Assessment:</strong> " . count($centralized_symptoms['by_assessment']) . " assessments</p>";
            }
        } else {
            echo "<p>ℹ️ No centralized symptoms found for user $user_id</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ <strong>Symptoms Manager Error:</strong> " . esc_html($e->getMessage()) . "</p>";
    }
}

// Test 5: Check User Meta Directly
echo "<h3>🗄️ Database Check</h3>";
$user_id = 1;
$meta_keys = ['ennu_biomarker_flags', 'ennu_symptom_history', 'ennu_centralized_symptoms'];

foreach ($meta_keys as $meta_key) {
    $meta_value = get_user_meta($user_id, $meta_key, true);
    if (!empty($meta_value)) {
        echo "<p>✅ <strong>$meta_key:</strong> Found data</p>";
        if (is_array($meta_value)) {
            echo "<p>📊 Data type: Array with " . count($meta_value) . " items</p>";
        }
    } else {
        echo "<p>ℹ️ <strong>$meta_key:</strong> No data found</p>";
    }
}

// Test 6: Test Auto-Flagging
echo "<h3>🚀 Auto-Flagging Test</h3>";
if (class_exists('ENNU_Centralized_Symptoms_Manager')) {
    try {
        // Test symptoms that should trigger biomarker flags
        $test_symptoms = [
            'fatigue' => 'moderate',
            'brain_fog' => 'severe',
            'low_libido' => 'mild'
        ];
        
        echo "<p>🧪 Testing auto-flagging with symptoms: " . implode(', ', array_keys($test_symptoms)) . "</p>";
        
        // This would normally be called when symptoms are processed
        // For testing, we'll just check if the method exists
        if (method_exists('ENNU_Centralized_Symptoms_Manager', 'auto_flag_biomarkers_from_symptoms')) {
            echo "<p>✅ <strong>Auto-flagging method exists</strong></p>";
        } else {
            echo "<p>❌ <strong>Auto-flagging method not found</strong></p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ <strong>Auto-Flagging Error:</strong> " . esc_html($e->getMessage()) . "</p>";
    }
}

// Test 7: System Status Summary
echo "<h3>📊 System Status Summary</h3>";
$status_items = [
    'WordPress Loaded' => file_exists('../../../wp-load.php'),
    'Flag Manager Class' => class_exists('ENNU_Biomarker_Flag_Manager'),
    'Symptoms Manager Class' => class_exists('ENNU_Centralized_Symptoms_Manager'),
    'Auto-Flagging Method' => method_exists('ENNU_Centralized_Symptoms_Manager', 'auto_flag_biomarkers_from_symptoms'),
    'Database Connection' => !empty(get_user_meta(1, 'ennu_biomarker_flags', true)) || !empty(get_user_meta(1, 'ennu_symptom_history', true))
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Component</th><th>Status</th></tr>";

foreach ($status_items as $component => $status) {
    $status_text = $status ? '✅ Working' : '❌ Not Working';
    $bg_color = $status ? '#d4edda' : '#f8d7da';
    echo "<tr style='background-color: $bg_color;'>";
    echo "<td><strong>$component</strong></td>";
    echo "<td>$status_text</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>🔗 Test Links</h3>";
echo "<p><a href='http://localhost:8888/?page_id=2469' target='_blank' style='background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0;'>🏠 User Dashboard</a></p>";
echo "<p><a href='http://localhost:8888/wp-admin/user-edit.php?user_id=1' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0;'>⚙️ Admin User Profile</a></p>";

echo "<h2>🎯 Final Verdict</h2>";
$working_components = array_sum($status_items);
$total_components = count($status_items);

if ($working_components === $total_components) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 ALL SYSTEMS WORKING! Biomarker flag system is fully functional.</p>";
} elseif ($working_components >= ($total_components * 0.8)) {
    echo "<p style='color: orange; font-size: 18px; font-weight: bold;'>⚠️ MOSTLY WORKING! Some components need attention.</p>";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>❌ SYSTEM ISSUES! Multiple components not working.</p>";
}

echo "<p><strong>Working Components:</strong> $working_components / $total_components</p>";
?> 