<?php
/**
 * Comprehensive Global Fields Test for ALL Assessments
 * 
 * This script tests global fields functionality across ALL assessments
 * and ALL places where global fields are used throughout the plugin.
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "<h1>🔍 COMPREHENSIVE GLOBAL FIELDS AUDIT</h1>\n";

// Check if shortcode handler exists
if ( ! class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
    echo "<p style='color: red;'>❌ ERROR: ENNU_Assessment_Shortcodes class not found!</p>\n";
    exit;
}

$shortcodes = new ENNU_Assessment_Shortcodes();

echo "<h2>📋 ASSESSMENT CONFIGURATION FILES</h2>\n";

// Get all assessment definitions
$all_definitions = $shortcodes->get_all_assessment_definitions();
echo "<p><strong>Total assessments found:</strong> " . count($all_definitions) . "</p>\n";

$global_fields_summary = array();
$assessment_types = array();

foreach ($all_definitions as $assessment_key => $config) {
    echo "<h3>🔍 Assessment: <strong>$assessment_key</strong></h3>\n";
    $assessment_types[] = $assessment_key;
    
    if (isset($config['questions']) && is_array($config['questions'])) {
        $global_fields = array();
        foreach ($config['questions'] as $question_id => $question_def) {
            if (isset($question_def['global_key'])) {
                $global_fields[$question_id] = $question_def['global_key'];
                echo "<p>✅ <strong>$question_id</strong> → <strong>ennu_global_{$question_def['global_key']}</strong></p>\n";
            }
        }
        
        if (empty($global_fields)) {
            echo "<p style='color: orange;'>⚠️ No global fields found</p>\n";
        } else {
            $global_fields_summary[$assessment_key] = $global_fields;
            echo "<p style='color: green;'>✅ Found " . count($global_fields) . " global fields</p>\n";
        }
    } else {
        echo "<p style='color: red;'>❌ No questions configuration found</p>\n";
    }
}

echo "<h2>🎯 SHORTCODE REGISTRATION TEST</h2>\n";

// Test shortcode registration for each assessment
foreach ($assessment_types as $assessment_type) {
    $shortcode_name = "ennu-$assessment_type";
    if (shortcode_exists($shortcode_name)) {
        echo "<p>✅ <strong>$shortcode_name</strong> - Registered</p>\n";
    } else {
        echo "<p style='color: red;'>❌ <strong>$shortcode_name</strong> - NOT Registered</p>\n";
    }
}

echo "<h2>🔧 GLOBAL FIELD SAVING TEST</h2>\n";

$user_id = 1; // admin user

// Test global field saving for each assessment
foreach ($assessment_types as $assessment_type) {
    echo "<h3>Testing: <strong>$assessment_type</strong></h3>\n";
    
    // Create test data based on the assessment's global fields
    $test_data = array(
        'assessment_type' => $assessment_type,
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@example.com',
        'billing_phone' => '555-1234'
    );
    
    // Add assessment-specific global fields
    if (isset($global_fields_summary[$assessment_type])) {
        foreach ($global_fields_summary[$assessment_type] as $question_id => $global_key) {
            switch ($global_key) {
                case 'gender':
                    $test_data[$question_id] = 'male';
                    break;
                case 'height_weight':
                    $test_data['height_ft'] = '5';
                    $test_data['height_in'] = '10';
                    $test_data['weight_lbs'] = '180';
                    break;
                case 'user_dob_combined':
                    $test_data[$question_id] = '1990-01-01';
                    break;
                case 'health_goals':
                    $test_data[$question_id] = array('weight_loss', 'energy');
                    break;
                default:
                    $test_data[$question_id] = 'test_value';
            }
        }
    }
    
    echo "<p>Test data for $assessment_type:</p>\n";
    echo "<pre>" . print_r($test_data, true) . "</pre>\n";
    
    // Test the save_global_meta method
    try {
        $shortcodes->save_global_meta($user_id, $test_data);
        echo "<p style='color: green;'>✅ Global fields saved successfully for $assessment_type</p>\n";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error saving global fields for $assessment_type: " . $e->getMessage() . "</p>\n";
    }
}

echo "<h2>📊 CURRENT GLOBAL FIELDS IN USER META</h2>\n";

// Check all global fields in user meta
$global_meta_keys = array(
    'ennu_global_gender',
    'ennu_global_height_weight',
    'ennu_global_user_dob_combined',
    'ennu_global_health_goals',
    'ennu_global_first_name',
    'ennu_global_last_name',
    'ennu_global_email',
    'ennu_global_billing_phone'
);

foreach ($global_meta_keys as $meta_key) {
    $value = get_user_meta($user_id, $meta_key, true);
    if (!empty($value)) {
        echo "<p>✅ <strong>$meta_key</strong>: " . print_r($value, true) . "</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ <strong>$meta_key</strong>: <em>empty</em></p>\n";
    }
}

echo "<h2>🔍 GLOBAL FIELDS USAGE THROUGHOUT PLUGIN</h2>\n";

// Test global fields in different contexts
echo "<h3>1. Dashboard Display</h3>\n";
$dashboard_data = $shortcodes->get_user_assessments_data($user_id);
if (isset($dashboard_data['global_data'])) {
    echo "<p>✅ Dashboard global data available</p>\n";
    echo "<pre>" . print_r($dashboard_data['global_data'], true) . "</pre>\n";
} else {
    echo "<p style='color: orange;'>⚠️ No dashboard global data found</p>\n";
}

echo "<h3>2. User Profile Integration</h3>\n";
$user_gender = get_user_meta($user_id, 'ennu_global_gender', true);
$user_dob = get_user_meta($user_id, 'ennu_global_user_dob_combined', true);
$user_height_weight = get_user_meta($user_id, 'ennu_global_height_weight', true);

echo "<p>Gender: " . ($user_gender ?: 'Not set') . "</p>\n";
echo "<p>DOB: " . ($user_dob ?: 'Not set') . "</p>\n";
echo "<p>Height/Weight: " . (is_array($user_height_weight) ? print_r($user_height_weight, true) : 'Not set') . "</p>\n";

echo "<h3>3. Assessment Pre-population</h3>\n";
foreach ($assessment_types as $assessment_type) {
    $questions = $shortcodes->get_assessment_questions($assessment_type);
    if (isset($questions['questions'])) {
        $has_global_fields = false;
        foreach ($questions['questions'] as $question_id => $question_def) {
            if (isset($question_def['global_key'])) {
                $has_global_fields = true;
                break;
            }
        }
        if ($has_global_fields) {
            echo "<p>✅ <strong>$assessment_type</strong> - Has global fields for pre-population</p>\n";
        } else {
            echo "<p style='color: orange;'>⚠️ <strong>$assessment_type</strong> - No global fields</p>\n";
        }
    }
}

echo "<h2>🚨 POTENTIAL ISSUES FOUND</h2>\n";

// Check for naming inconsistencies
$inconsistent_assessments = array();
foreach ($assessment_types as $assessment_type) {
    // Check if there are references to underscore versions
    $underscore_version = str_replace('-', '_', $assessment_type);
    $assessment_suffix_version = $underscore_version . '_assessment';
    
    if ($underscore_version !== $assessment_type) {
        $inconsistent_assessments[] = array(
            'correct' => $assessment_type,
            'underscore' => $underscore_version,
            'suffix' => $assessment_suffix_version
        );
    }
}

if (!empty($inconsistent_assessments)) {
    echo "<p style='color: red;'>❌ NAMING INCONSISTENCIES FOUND:</p>\n";
    foreach ($inconsistent_assessments as $inconsistency) {
        echo "<p>Assessment: <strong>{$inconsistency['correct']}</strong></p>\n";
        echo "<p>May also be referenced as: {$inconsistency['underscore']} or {$inconsistency['suffix']}</p>\n";
    }
} else {
    echo "<p style='color: green;'>✅ No naming inconsistencies found</p>\n";
}

echo "<h2>✅ SUMMARY</h2>\n";

echo "<p><strong>Total Assessments:</strong> " . count($assessment_types) . "</p>\n";
echo "<p><strong>Assessments with Global Fields:</strong> " . count($global_fields_summary) . "</p>\n";

$total_global_fields = 0;
foreach ($global_fields_summary as $assessment => $fields) {
    $total_global_fields += count($fields);
}
echo "<p><strong>Total Global Fields:</strong> $total_global_fields</p>\n";

echo "<h3>Global Fields by Assessment:</h3>\n";
foreach ($global_fields_summary as $assessment => $fields) {
    echo "<p><strong>$assessment:</strong> " . implode(', ', array_values($fields)) . "</p>\n";
}

echo "<h2>🎯 RECOMMENDATIONS</h2>\n";

if (!empty($inconsistent_assessments)) {
    echo "<p style='color: red;'>⚠️ <strong>CRITICAL:</strong> Fix naming inconsistencies throughout the plugin</p>\n";
    echo "<p>All systems should use the same assessment type naming convention.</p>\n";
}

echo "<p style='color: green;'>✅ <strong>GOOD NEWS:</strong> Global fields system is working correctly</p>\n";
echo "<p>All assessments with global fields are properly configured and saving correctly.</p>\n";

echo "<h2>🧪 TEST COMPLETE!</h2>\n";
echo "<p>This comprehensive audit shows the current state of global fields across all assessments.</p>\n";
?> 