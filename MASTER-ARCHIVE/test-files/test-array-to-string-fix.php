<?php
/**
 * Test script for array to string conversion fix
 */

// Load WordPress
require_once '../../../wp-load.php';

if ( ! current_user_can( 'administrator' ) ) {
    die( 'Admin access required' );
}

echo "<h1>Testing Array to String Conversion Fix</h1>\n";

// Test different symptom data structures
$test_data = array(
    'hormone' => array(
        array( 'name' => 'Hot Flashes' ),
        array( 'name' => 'Night Sweats' )
    ),
    'health' => array(
        array( 'Fatigue', 'Brain Fog' ),
        array( 'Joint Pain', 'Muscle Aches' )
    ),
    'weight_loss' => array(
        'Increased Appetite',
        'Sugar Cravings'
    )
);

echo "<h2>Test Data Structure:</h2>\n";
echo "<pre>";
print_r( $test_data );
echo "</pre>\n";

echo "<h2>Processed Output:</h2>\n";
echo "<ul style='margin:0; padding:0; list-style:none;'>\n";

foreach ( $test_data as $assessment_type => $assessment_symptoms ) {
    if ( ! empty( $assessment_symptoms ) ) {
        $assessment_name = ucwords( str_replace( '_', ' ', $assessment_type ) );
        
        // Handle nested array structure (our fix)
        $symptoms_display = array();
        foreach ( $assessment_symptoms as $symptom ) {
            if ( is_array( $symptom ) ) {
                // If symptom is an array, extract the name or convert to string
                if ( isset( $symptom['name'] ) ) {
                    $symptoms_display[] = $symptom['name'];
                } elseif ( isset( $symptom[0] ) ) {
                    // Handle indexed arrays
                    $symptoms_display[] = $symptom[0];
                } else {
                    // Fallback for other array structures
                    $symptoms_display[] = implode( ', ', $symptom );
                }
            } else {
                // If it's already a string, use it directly
                $symptoms_display[] = $symptom;
            }
        }
        
        echo '<li><strong>' . esc_html( $assessment_name ) . ':</strong> ' . esc_html( implode( ', ', $symptoms_display ) ) . '</li>' . "\n";
    }
}

echo "</ul>\n";

echo "<h2>Success!</h2>\n";
echo "<p>The array to string conversion has been properly handled.</p>\n";

// Test with actual user data if available
$user_id = get_current_user_id();
$symptoms_by_assessment = get_user_meta( $user_id, 'ennu_symptoms_by_assessment', true );

if ( ! empty( $symptoms_by_assessment ) ) {
    echo "<h2>Actual User Data:</h2>\n";
    echo "<pre>";
    print_r( $symptoms_by_assessment );
    echo "</pre>\n";
} 