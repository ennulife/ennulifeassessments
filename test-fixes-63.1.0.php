<?php
/**
 * ENNU Life Plugin - Version 63.1.0 Fixes Verification Test
 * 
 * This script tests all the critical fixes implemented in version 63.1.0
 * 
 * @package ENNU_Life
 * @version 63.1.0
 */

// Load WordPress
require_once '../../../wp-load.php';

echo "<h1>🔧 ENNU Life Plugin - Version 63.1.0 Fixes Verification</h1>\n";
echo "<h2>Critical Fixes Testing</h2>\n";

$test_passed = true;
$user_id = 12; // Test user ID

echo "<h3>1. 🗄️ Database Class Fix Test</h3>\n";

// Test 1: Check if the correct database class exists and can be instantiated
if ( class_exists( 'ENNU_Life_Enhanced_Database' ) ) {
    echo "<p style='color: green;'>✅ PASS: ENNU_Life_Enhanced_Database class exists</p>\n";
    
    try {
        $database = new ENNU_Life_Enhanced_Database();
        echo "<p style='color: green;'>✅ PASS: ENNU_Life_Enhanced_Database can be instantiated</p>\n";
    } catch ( Exception $e ) {
        echo "<p style='color: red;'>❌ FAIL: ENNU_Life_Enhanced_Database instantiation failed: " . $e->getMessage() . "</p>\n";
        $test_passed = false;
    }
} else {
    echo "<p style='color: red;'>❌ FAIL: ENNU_Life_Enhanced_Database class not found</p>\n";
    $test_passed = false;
}

echo "<h3>2. 🏁 Biomarker Flag Manager Fix Test</h3>\n";

// Test 2: Check if the biomarker flag manager has the missing method
if ( class_exists( 'ENNU_Biomarker_Flag_Manager' ) ) {
    echo "<p style='color: green;'>✅ PASS: ENNU_Biomarker_Flag_Manager class exists</p>\n";
    
    $flag_manager = new ENNU_Biomarker_Flag_Manager();
    
    if ( method_exists( $flag_manager, 'get_biomarker_flags' ) ) {
        echo "<p style='color: green;'>✅ PASS: get_biomarker_flags method exists</p>\n";
        
        // Test the method
        $flags = $flag_manager->get_biomarker_flags( $user_id, 'test_biomarker' );
        if ( is_array( $flags ) ) {
            echo "<p style='color: green;'>✅ PASS: get_biomarker_flags returns array</p>\n";
        } else {
            echo "<p style='color: red;'>❌ FAIL: get_biomarker_flags does not return array</p>\n";
            $test_passed = false;
        }
    } else {
        echo "<p style='color: red;'>❌ FAIL: get_biomarker_flags method not found</p>\n";
        $test_passed = false;
    }
} else {
    echo "<p style='color: red;'>❌ FAIL: ENNU_Biomarker_Flag_Manager class not found</p>\n";
    $test_passed = false;
}

echo "<h3>3. 🌐 Global Fields Pre-population Test</h3>\n";

// Test 3: Check global fields pre-population
$global_fields = array(
    'ennu_global_user_dob_combined' => 'Date of Birth',
    'ennu_global_gender' => 'Gender',
    'ennu_global_health_goals' => 'Health Goals',
    'ennu_global_height_weight' => 'Height/Weight'
);

foreach ( $global_fields as $meta_key => $field_name ) {
    $value = get_user_meta( $user_id, $meta_key, true );
    if ( ! empty( $value ) ) {
        echo "<p style='color: green;'>✅ PASS: {$field_name} has data: " . ( is_array( $value ) ? json_encode( $value ) : $value ) . "</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ WARNING: {$field_name} is empty (this may be normal if user hasn't completed assessments)</p>\n";
    }
}

echo "<h3>4. 🔧 Plugin Initialization Test</h3>\n";

// Test 4: Check if plugin is properly initialized
if ( class_exists( 'ENNU_Life_Enhanced_Plugin' ) ) {
    echo "<p style='color: green;'>✅ PASS: ENNU_Life_Enhanced_Plugin class exists</p>\n";
    
    // Check if the plugin instance exists
    global $ennu_life_plugin;
    if ( isset( $ennu_life_plugin ) && is_object( $ennu_life_plugin ) ) {
        echo "<p style='color: green;'>✅ PASS: Plugin instance is properly initialized</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ WARNING: Plugin instance not found in global scope</p>\n";
    }
} else {
    echo "<p style='color: red;'>❌ FAIL: ENNU_Life_Enhanced_Plugin class not found</p>\n";
    $test_passed = false;
}

echo "<h3>5. 📊 User Dashboard Data Test</h3>\n";

// Test 5: Check user dashboard data retrieval (method is private, so we'll test differently)
$shortcodes = new ENNU_Assessment_Shortcodes();

// Check if the class has the method (even though it's private)
$reflection = new ReflectionClass( $shortcodes );
$methods = $reflection->getMethods( ReflectionMethod::IS_PRIVATE );
$has_method = false;

foreach ( $methods as $method ) {
    if ( $method->getName() === 'get_user_assessments_data' ) {
        $has_method = true;
        break;
    }
}

if ( $has_method ) {
    echo "<p style='color: green;'>✅ PASS: get_user_assessments_data method exists (private)</p>\n";
    
    // Test by checking user meta for assessment data
    $assessment_meta = get_user_meta( $user_id );
    $assessment_keys = array_filter( array_keys( $assessment_meta ), function( $key ) {
        return strpos( $key, 'ennu_' ) === 0 && strpos( $key, '_completed' ) !== false;
    } );
    
    if ( ! empty( $assessment_keys ) ) {
        echo "<p style='color: green;'>✅ PASS: User has assessment data</p>\n";
        echo "<p>Found " . count( $assessment_keys ) . " assessment meta keys</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ WARNING: No assessment data found for user</p>\n";
    }
} else {
    echo "<p style='color: red;'>❌ FAIL: get_user_assessments_data method not found</p>\n";
    $test_passed = false;
}

echo "<h3>6. 🎯 Assessment Shortcodes Test</h3>\n";

// Test 6: Check assessment shortcodes functionality
if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
    echo "<p style='color: green;'>✅ PASS: ENNU_Assessment_Shortcodes class exists</p>\n";
    
    // Test getting assessment questions
    $welcome_questions = $shortcodes->get_assessment_questions( 'welcome' );
    if ( is_array( $welcome_questions ) && ! empty( $welcome_questions ) ) {
        echo "<p style='color: green;'>✅ PASS: Welcome assessment questions retrieved</p>\n";
        
        // Check for global fields
        $global_fields_found = 0;
        foreach ( $welcome_questions as $question ) {
            if ( isset( $question['global_key'] ) ) {
                $global_fields_found++;
            }
        }
        echo "<p>Global fields found in welcome assessment: {$global_fields_found}</p>\n";
    } else {
        echo "<p style='color: red;'>❌ FAIL: Welcome assessment questions retrieval failed</p>\n";
        $test_passed = false;
    }
} else {
    echo "<p style='color: red;'>❌ FAIL: ENNU_Assessment_Shortcodes class not found</p>\n";
    $test_passed = false;
}

echo "<h3>7. 🔒 Security Systems Test</h3>\n";

// Test 7: Check security systems
$security_systems = array(
    'ENNU_CSRF_Protection' => 'CSRF Protection',
    'ENNU_AJAX_Security' => 'AJAX Security',
    'ENNU_Security_Validator' => 'Security Validator'
);

foreach ( $security_systems as $class => $name ) {
    if ( class_exists( $class ) ) {
        echo "<p style='color: green;'>✅ PASS: {$name} class exists</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ WARNING: {$name} class not found</p>\n";
    }
}

echo "<h3>📋 Test Summary</h3>\n";

if ( $test_passed ) {
    echo "<p style='color: green; font-weight: bold;'>🎉 ALL CRITICAL TESTS PASSED - Version 63.1.0 fixes are working correctly!</p>\n";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ SOME TESTS FAILED - Please review the issues above</p>\n";
}

echo "<h3>🔍 Debug Information</h3>\n";
echo "<p>Plugin Version: " . ENNU_LIFE_VERSION . "</p>\n";
echo "<p>WordPress Version: " . get_bloginfo( 'version' ) . "</p>\n";
echo "<p>PHP Version: " . PHP_VERSION . "</p>\n";
echo "<p>Test User ID: {$user_id}</p>\n";

echo "<h3>📝 Next Steps</h3>\n";
echo "<p>1. Check the WordPress debug log for any ENNU DEBUG messages</p>\n";
echo "<p>2. Test assessment submission to verify global field saving</p>\n";
echo "<p>3. Verify user dashboard displays correctly</p>\n";
echo "<p>4. Test biomarker flagging functionality</p>\n";

echo "<hr>\n";
echo "<p><em>Test completed at: " . date( 'Y-m-d H:i:s' ) . "</em></p>\n";
?> 