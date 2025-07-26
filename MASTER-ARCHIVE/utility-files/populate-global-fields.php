<?php
/**
 * Populate Global Fields for User 12
 * 
 * Since user is already logged in and can't access welcome assessment,
 * this script directly populates the global fields with test data
 */

// Load WordPress
require_once '../../../wp-load.php';

$user_id = 12;

echo "<h1>🔧 Populate Global Fields - User {$user_id}</h1>\n";

// Check if user exists
$user = get_user_by('ID', $user_id);
if ( ! $user ) {
    echo "<p style='color: red;'>❌ ERROR: User {$user_id} not found!</p>\n";
    exit;
}

echo "<p style='color: green;'>✅ User found: " . esc_html( $user->display_name ) . "</p>\n";

// Define test global field data
$global_fields_data = array(
    		'ennu_global_date_of_birth' => '1990-01-15', // Date of Birth
    'ennu_global_gender' => 'male', // Gender
    'ennu_global_health_goals' => array( // Health Goals (multiselect)
        'energy',
        'strength', 
        'weight_loss',
        'hormonal_balance'
    ),
    'ennu_global_height_weight' => array( // Height/Weight
        'ft' => '6',
        'in' => '0',
        'lbs' => '180'
    )
);

echo "<h2>Populating Global Fields:</h2>\n";

$success_count = 0;
$total_fields = count( $global_fields_data );

foreach ( $global_fields_data as $meta_key => $value ) {
    $result = update_user_meta( $user_id, $meta_key, $value );
    
    if ( $result ) {
        echo "<p style='color: green;'>✅ SUCCESS: {$meta_key}</p>\n";
        if ( is_array( $value ) ) {
            echo "<p>Value: " . json_encode( $value ) . "</p>\n";
        } else {
            echo "<p>Value: " . esc_html( $value ) . "</p>\n";
        }
        $success_count++;
    } else {
        echo "<p style='color: red;'>❌ FAILED: {$meta_key}</p>\n";
    }
}

echo "<h2>Results:</h2>\n";
echo "<p>Successfully populated: {$success_count}/{$total_fields} fields</p>\n";

if ( $success_count === $total_fields ) {
    echo "<p style='color: green; font-weight: bold;'>🎉 ALL GLOBAL FIELDS POPULATED SUCCESSFULLY!</p>\n";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ SOME FIELDS FAILED TO POPULATE</p>\n";
}

// Verify the data was saved
echo "<h2>Verification:</h2>\n";
foreach ( $global_fields_data as $meta_key => $expected_value ) {
    $saved_value = get_user_meta( $user_id, $meta_key, true );
    
    if ( ! empty( $saved_value ) ) {
        echo "<p style='color: green;'>✅ VERIFIED: {$meta_key}</p>\n";
        if ( is_array( $saved_value ) ) {
            echo "<p>Saved: " . json_encode( $saved_value ) . "</p>\n";
        } else {
            echo "<p>Saved: " . esc_html( $saved_value ) . "</p>\n";
        }
    } else {
        echo "<p style='color: red;'>❌ NOT FOUND: {$meta_key}</p>\n";
    }
}

// Calculate age from DOB
	$dob = get_user_meta( $user_id, 'ennu_global_date_of_birth', true );
if ( ! empty( $dob ) ) {
    $age = ( new DateTime() )->diff( new DateTime( $dob ) )->y;
    echo "<p><strong>Calculated Age:</strong> {$age} years</p>\n";
}

echo "<hr>\n";
echo "<h2>🔧 Next Steps:</h2>\n";
echo "<p>1. <strong>Test Pre-population:</strong> Go to any assessment form and check if fields are pre-populated</p>\n";
echo "<p>2. <strong>Check User Dashboard:</strong> Verify global fields now show data</p>\n";
echo "<p>3. <strong>Monitor Debug Logs:</strong> Watch for pre-population debug messages</p>\n";

echo "<p><em>Script completed at: " . date( 'Y-m-d H:i:s' ) . "</em></p>\n";
?> 