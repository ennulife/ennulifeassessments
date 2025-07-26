<?php
/**
 * Check Global Fields Data
 * 
 * This script checks the current global field data for user 12
 */

// Load WordPress
require_once '../../../wp-load.php';

$user_id = 12;

echo "<h1>🔍 Global Fields Data Check - User {$user_id}</h1>\n";

// Check all global fields
$global_fields = array(
    		'ennu_global_date_of_birth' => 'Date of Birth',
    'ennu_global_gender' => 'Gender',
    'ennu_global_health_goals' => 'Health Goals',
    'ennu_global_height_weight' => 'Height/Weight'
);

echo "<h2>Current Global Field Data:</h2>\n";

foreach ( $global_fields as $meta_key => $field_name ) {
    $value = get_user_meta( $user_id, $meta_key, true );
    
    echo "<h3>{$field_name} ({$meta_key})</h3>\n";
    
    if ( empty( $value ) ) {
        echo "<p style='color: red;'>❌ EMPTY - No data found</p>\n";
    } else {
        echo "<p style='color: green;'>✅ HAS DATA:</p>\n";
        if ( is_array( $value ) ) {
            echo "<pre>" . print_r( $value, true ) . "</pre>\n";
        } else {
            echo "<p><strong>Value:</strong> " . esc_html( $value ) . "</p>\n";
        }
    }
}

// Check all user meta for debugging
echo "<h2>All ENNU User Meta:</h2>\n";
$all_meta = get_user_meta( $user_id );
$ennu_meta = array();

foreach ( $all_meta as $key => $values ) {
    if ( strpos( $key, 'ennu_' ) === 0 ) {
        $ennu_meta[$key] = $values[0];
    }
}

if ( empty( $ennu_meta ) ) {
    echo "<p style='color: red;'>❌ No ENNU meta data found for user</p>\n";
} else {
    echo "<p style='color: green;'>✅ Found " . count( $ennu_meta ) . " ENNU meta entries:</p>\n";
    echo "<pre>" . print_r( $ennu_meta, true ) . "</pre>\n";
}

// Check if user has completed any assessments
echo "<h2>Assessment Completion Status:</h2>\n";
$assessment_types = array( 'welcome', 'weight-loss', 'hair', 'testosterone', 'health' );

foreach ( $assessment_types as $assessment_type ) {
    $completed_key = "ennu_{$assessment_type}_completed";
    $completed = get_user_meta( $user_id, $completed_key, true );
    
    if ( $completed ) {
        echo "<p style='color: green;'>✅ {$assessment_type} assessment: COMPLETED</p>\n";
    } else {
        echo "<p style='color: orange;'>⚠️ {$assessment_type} assessment: NOT COMPLETED</p>\n";
    }
}

echo "<hr>\n";
echo "<h2>🔧 Next Steps:</h2>\n";
echo "<p>1. Complete the Welcome Assessment to test global field saving</p>\n";
echo "<p>2. Check the debug log for 'ENNU DEBUG' messages</p>\n";
echo "<p>3. Verify global fields are populated after assessment completion</p>\n";

echo "<p><em>Check completed at: " . date( 'Y-m-d H:i:s' ) . "</em></p>\n";
?> 