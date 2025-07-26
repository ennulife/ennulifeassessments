<?php
/**
 * Debug Script to Trace Actual Form Submission
 * 
 * This script will help us see what's happening during
 * the actual form submission process.
 */

// Load WordPress
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

echo "<h1>🔍 Actual Form Submission Debug</h1>\n";

// Check if this is a POST request (form submission)
if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
    echo "<h2>📥 Form Submission Detected</h2>\n";
    echo "<p><strong>Assessment Type:</strong> " . ( $_POST['assessment_type'] ?? 'Not set' ) . "</p>\n";
    
    // Check if it's a hormone assessment
    if ( isset( $_POST['assessment_type'] ) && $_POST['assessment_type'] === 'hormone' ) {
        echo "<p>✅ This is a hormone assessment submission</p>\n";
        
        // Check for multiselect fields
        $multiselect_fields = array( 'hormone_q1', 'hormone_q6', 'hormone_q7', 'hormone_q9' );
        echo "<h3>Multiselect Field Check:</h3>\n";
        foreach ( $multiselect_fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                $value = $_POST[ $field ];
                if ( is_array( $value ) ) {
                    echo "<p>✅ <strong>{$field}</strong>: " . implode( ', ', $value ) . " (Array with " . count( $value ) . " items)</p>\n";
                } else {
                    echo "<p>⚠️ <strong>{$field}</strong>: {$value} (String, not array)</p>\n";
                }
            } else {
                echo "<p>❌ <strong>{$field}</strong>: Not found in POST data</p>\n";
            }
        }
        
        // Check if the shortcode class is available
        echo "<h3>Shortcode Class Check:</h3>\n";
        if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
            echo "<p>✅ ENNU_Assessment_Shortcodes class exists</p>\n";
            
            // Try to get hormone questions
            $shortcodes = new ENNU_Assessment_Shortcodes();
            $hormone_questions = $shortcodes->get_assessment_questions( 'hormone' );
            
            if ( ! empty( $hormone_questions ) ) {
                echo "<p>✅ Hormone questions available: " . count( $hormone_questions ) . " questions</p>\n";
            } else {
                echo "<p>❌ No hormone questions available</p>\n";
            }
            
        } else {
            echo "<p>❌ ENNU_Assessment_Shortcodes class not found</p>\n";
        }
        
    } else {
        echo "<p>❌ This is not a hormone assessment submission</p>\n";
    }
    
} else {
    echo "<h2>📝 Test Form for Hormone Assessment</h2>\n";
    echo "<p>This form will submit test data to see what happens during submission.</p>\n";
    
    echo "<form method='POST' action=''>\n";
    echo "<input type='hidden' name='assessment_type' value='hormone'>\n";
    echo "<input type='hidden' name='email' value='test@example.com'>\n";
    echo "<input type='hidden' name='first_name' value='Test'>\n";
    echo "<input type='hidden' name='last_name' value='User'>\n";
    echo "<input type='hidden' name='hormone_q_gender' value='male'>\n";
    echo "<input type='hidden' name='hormone_q_dob' value='1990-09-12'>\n";
    
    // Add test data for multiselect fields
    echo "<input type='hidden' name='hormone_q1[]' value='fatigue'>\n";
    echo "<input type='hidden' name='hormone_q1[]' value='mood_swings'>\n";
    echo "<input type='hidden' name='hormone_q1[]' value='weight_gain'>\n";
    
    echo "<input type='hidden' name='hormone_q2' value='moderate'>\n";
    echo "<input type='hidden' name='hormone_q3' value='moderate'>\n";
    echo "<input type='hidden' name='hormone_q4' value='good'>\n";
    echo "<input type='hidden' name='hormone_q5' value='2-3'>\n";
    
    echo "<input type='hidden' name='hormone_q6[]' value='none'>\n";
    echo "<input type='hidden' name='hormone_q7[]' value='none_known'>\n";
    echo "<input type='hidden' name='hormone_q8' value='no'>\n";
    echo "<input type='hidden' name='hormone_q9[]' value='stress'>\n";
    echo "<input type='hidden' name='hormone_q9[]' value='poor_sleep'>\n";
    echo "<input type='hidden' name='hormone_q10' value='good'>\n";
    
    echo "<input type='submit' value='Submit Test Hormone Assessment'>\n";
    echo "</form>\n";
}

// Check current user meta for hormone assessment
echo "<h2>🔍 Current User Meta Check</h2>\n";

$current_user_id = get_current_user_id();
if ( $current_user_id ) {
    echo "<p><strong>Current User ID:</strong> {$current_user_id}</p>\n";
    
    // Get all ENNU-related meta
    $all_meta = get_user_meta( $current_user_id );
    $ennu_meta = array();
    
    foreach ( $all_meta as $key => $value ) {
        if ( strpos( $key, 'ennu_' ) === 0 ) {
            $ennu_meta[ $key ] = $value[0];
        }
    }
    
    if ( ! empty( $ennu_meta ) ) {
        echo "<h3>ENNU User Meta:</h3>\n";
        echo "<ul>\n";
        foreach ( $ennu_meta as $key => $value ) {
            if ( strpos( $key, 'hormone' ) !== false ) {
                echo "<li><strong>{$key}</strong>: " . ( is_array( $value ) ? print_r( $value, true ) : $value ) . "</li>\n";
            }
        }
        echo "</ul>\n";
    } else {
        echo "<p>❌ No ENNU user meta found</p>\n";
    }
    
} else {
    echo "<p>❌ No user logged in</p>\n";
}

echo "<h2>🔧 Next Steps</h2>\n";
echo "<p>If the form submission shows that multiselect fields are being received correctly but not saved, the issue is in the enhanced methods not being called.</p>\n";
echo "<p>If the form submission shows that multiselect fields are not being received correctly, the issue is in the HTML form generation.</p>\n";
?> 