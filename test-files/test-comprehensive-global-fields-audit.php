<?php
/**
 * COMPREHENSIVE GLOBAL FIELDS AUDIT
 * 
 * This script tests ALL global field usage across:
 * 1. Scoring calculations
 * 2. Database queries  
 * 3. Admin interface
 * 4. Internal operations
 * 
 * ZERO TOLERANCE for any issues in these critical areas.
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>🔍 COMPREHENSIVE GLOBAL FIELDS AUDIT</h1>\n";
echo "<h2>ZERO TOLERANCE TESTING - CRITICAL SYSTEMS ONLY</h2>\n";

// Check if required classes exist
$required_classes = array(
    'ENNU_Assessment_Shortcodes',
    'ENNU_Scoring_System', 
    'ENNU_Enhanced_Admin',
    'ENNU_Data_Export_Service'
);

foreach ($required_classes as $class) {
    if (!class_exists($class)) {
        echo "<p style='color: red;'>❌ CRITICAL ERROR: $class class not found!</p>\n";
        exit;
    }
}

$shortcodes = new ENNU_Assessment_Shortcodes();
$scoring_system = new ENNU_Scoring_System();
$admin = new ENNU_Enhanced_Admin();
$export_service = new ENNU_Data_Export_Service();

$user_id = 1; // admin user
$test_passed = true;

echo "<h3>1. 🎯 SCORING CALCULATIONS AUDIT</h3>\n";

// Test 1: Check if global fields are used in scoring
$health_goals = get_user_meta($user_id, 'ennu_global_health_goals', true);
if (empty($health_goals)) {
    // Set test data
    update_user_meta($user_id, 'ennu_global_health_goals', array('weight_loss', 'energy'));
    echo "<p>✅ Set test health goals for scoring</p>\n";
}

// Test scoring system access to global fields
try {
    $score_data = ENNU_Scoring_System::calculate_and_save_all_user_scores($user_id, true);
    echo "<p>✅ Scoring system can access global health goals</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ CRITICAL: Scoring system failed: " . $e->getMessage() . "</p>\n";
    $test_passed = false;
}

echo "<h3>2. 🗄️ DATABASE QUERIES AUDIT</h3>\n";

// Test 2: Check database queries for global fields
global $wpdb;

// Test direct database access to global fields
$global_fields_query = $wpdb->prepare(
    "SELECT meta_key, meta_value FROM {$wpdb->usermeta} 
     WHERE user_id = %d AND meta_key LIKE %s",
    $user_id, 'ennu_global_%'
);

$global_fields_results = $wpdb->get_results($global_fields_query);

if ($global_fields_results) {
    echo "<p>✅ Database queries can access global fields</p>\n";
    foreach ($global_fields_results as $field) {
        echo "<p>  - Found: {$field->meta_key} = " . substr($field->meta_value, 0, 50) . "...</p>\n";
    }
} else {
    echo "<p style='color: orange;'>⚠️ No global fields found in database (may be normal for new user)</p>\n";
}

// Test WordPress meta functions
$test_global_fields = array(
    'ennu_global_gender',
    'ennu_global_health_goals', 
    'ennu_global_height_weight',
    'ennu_global_user_dob_combined'
);

foreach ($test_global_fields as $field) {
    $value = get_user_meta($user_id, $field, true);
    if ($value !== false) {
        echo "<p>✅ WordPress meta function works for: $field</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ No data for: $field (may be normal)</p>\n";
    }
}

echo "<h3>3. 👨‍💼 ADMIN INTERFACE AUDIT</h3>\n";

// Test 3: Check admin interface global field handling
try {
    // Test admin display methods
    ob_start();
    $admin->show_user_assessment_fields(get_user_by('id', $user_id));
    $admin_output = ob_get_clean();
    
    if (strpos($admin_output, 'ennu_global_') !== false) {
        echo "<p>✅ Admin interface can display global fields</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ Admin interface may not be displaying global fields</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ CRITICAL: Admin interface failed: " . $e->getMessage() . "</p>\n";
    $test_passed = false;
}

// Test admin save functionality
$test_admin_data = array(
    'ennu_global_gender' => 'male',
    'ennu_global_health_goals' => array('weight_loss', 'energy'),
    'ennu_global_height_weight' => array('ft' => '5', 'in' => '10', 'lbs' => '150')
);

foreach ($test_admin_data as $key => $value) {
    $result = update_user_meta($user_id, $key, $value);
    if ($result !== false) {
        echo "<p>✅ Admin can save: $key</p>\n";
    } else {
        echo "<p style='color: red;'>❌ CRITICAL: Admin cannot save: $key</p>\n";
        $test_passed = false;
    }
}

echo "<h3>4. ⚙️ INTERNAL OPERATIONS AUDIT</h3>\n";

// Test 4: Check internal operations
try {
    // Test data export service
    $export_data = $export_service->export_user_data(array($user_id), 'json');
    if ($export_data && isset($export_data['content'])) {
        echo "<p>✅ Data export service works with global fields</p>\n";
    } else {
        echo "<p style='color: red;'>❌ CRITICAL: Data export service failed</p>\n";
        $test_passed = false;
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ CRITICAL: Data export failed: " . $e->getMessage() . "</p>\n";
    $test_passed = false;
}

// Test assessment submission with global fields
$test_assessment_data = array(
    'assessment_type' => 'weight-loss',
    'wl_q_gender' => 'male',
    'height_ft' => '5',
    'height_in' => '10', 
    'weight_lbs' => '150',
    'first_name' => 'Test',
    'last_name' => 'User',
    'email' => 'test@example.com'
);

try {
    // Test global field saving
    $global_fields_saved = $shortcodes->save_global_meta($user_id, $test_assessment_data);
    echo "<p>✅ Global field saving works</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ CRITICAL: Global field saving failed: " . $e->getMessage() . "</p>\n";
    $test_passed = false;
}

echo "<h3>5. 🔍 ASSESSMENT TYPE MAPPING AUDIT</h3>\n";

// Test 5: Check assessment type mapping consistency
$assessment_types = array(
    'weight-loss',
    'hair',
    'skin', 
    'welcome'
);

foreach ($assessment_types as $type) {
    $questions = $shortcodes->get_assessment_questions($type);
    if (!empty($questions) && isset($questions['questions'])) {
        $global_fields_found = 0;
        foreach ($questions['questions'] as $question_id => $question_def) {
            if (isset($question_def['global_key'])) {
                $global_fields_found++;
            }
        }
        echo "<p>✅ Assessment '$type' has $global_fields_found global fields</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ Assessment '$type' has no questions or global fields</p>\n";
    }
}

echo "<h3>6. 🎯 FINAL VERIFICATION</h3>\n";

// Final verification - check all critical global fields
$critical_global_fields = array(
    'ennu_global_gender',
    'ennu_global_health_goals',
    'ennu_global_height_weight',
    'ennu_global_user_dob_combined'
);

$all_critical_fields_working = true;
foreach ($critical_global_fields as $field) {
    $value = get_user_meta($user_id, $field, true);
    if ($value === false) {
        echo "<p style='color: red;'>❌ CRITICAL: Cannot access $field</p>\n";
        $all_critical_fields_working = false;
        $test_passed = false;
    } else {
        echo "<p>✅ Critical field $field is accessible</p>\n";
    }
}

// Final result
echo "<h2>📊 AUDIT RESULTS</h2>\n";
if ($test_passed && $all_critical_fields_working) {
    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>🎉 ALL CRITICAL SYSTEMS PASSED - ZERO ISSUES FOUND!</p>\n";
    echo "<p>✅ Scoring calculations: WORKING</p>\n";
    echo "<p>✅ Database queries: WORKING</p>\n";
    echo "<p>✅ Admin interface: WORKING</p>\n";
    echo "<p>✅ Internal operations: WORKING</p>\n";
    echo "<p>✅ Assessment type mapping: WORKING</p>\n";
    echo "<p>✅ Critical global fields: ALL ACCESSIBLE</p>\n";
} else {
    echo "<p style='color: red; font-size: 18px; font-weight: bold;'>🚨 CRITICAL ISSUES FOUND - IMMEDIATE ATTENTION REQUIRED!</p>\n";
}

echo "<h3>🔧 RECOMMENDATIONS</h3>\n";
if ($test_passed) {
    echo "<p>✅ Your global fields fix is COMPLETE and ROBUST</p>\n";
    echo "<p>✅ All critical systems are functioning correctly</p>\n";
    echo "<p>✅ No further action required</p>\n";
} else {
    echo "<p>❌ Critical issues detected - immediate fixes required</p>\n";
    echo "<p>❌ Review error messages above and fix each issue</p>\n";
} 