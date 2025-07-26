<?php
/**
 * Debug Script to Check Shortcode Class Definitions
 * 
 * This script will help us determine what's in the shortcode
 * class's all_definitions property.
 */

// Load WordPress
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

echo "<h1>🔍 Shortcode Class Definitions Debug</h1>\n";

// Check if the shortcode class exists
if ( ! class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
    echo "<p>❌ ENNU_Assessment_Shortcodes class not found</p>\n";
    exit;
}

echo "<p>✅ ENNU_Assessment_Shortcodes class found</p>\n";

// Create a new instance
$shortcodes = new ENNU_Assessment_Shortcodes();

// Force initialization
$shortcodes->init();

echo "<h2>1. Shortcode Class Initialization</h2>\n";
echo "<p>✅ Shortcode class initialized</p>\n";

// Get all definitions from shortcode class
$shortcode_definitions = $shortcodes->get_all_assessment_definitions();

echo "<h2>2. Shortcode Class Definitions</h2>\n";
echo "<p>Total assessments in shortcode class: " . count( $shortcode_definitions ) . "</p>\n";

if ( empty( $shortcode_definitions ) ) {
    echo "<p>❌ No assessments in shortcode class!</p>\n";
} else {
    echo "<h3>Available Assessments in Shortcode Class:</h3>\n";
    echo "<ul>\n";
    foreach ( $shortcode_definitions as $key => $config ) {
        $question_count = isset( $config['questions'] ) ? count( $config['questions'] ) : 'N/A';
        echo "<li><strong>{$key}</strong> - {$config['title']} ({$question_count} questions)</li>\n";
    }
    echo "</ul>\n";
}

// Check specifically for hormone assessment
echo "<h2>3. Hormone Assessment in Shortcode Class</h2>\n";

if ( isset( $shortcode_definitions['hormone'] ) ) {
    $hormone_config = $shortcode_definitions['hormone'];
    echo "<p>✅ Hormone assessment found in shortcode class</p>\n";
    echo "<p><strong>Title:</strong> {$hormone_config['title']}</p>\n";
    echo "<p><strong>Engine:</strong> {$hormone_config['assessment_engine']}</p>\n";
    echo "<p><strong>Questions:</strong> " . count( $hormone_config['questions'] ) . "</p>\n";
    
} else {
    echo "<p>❌ Hormone assessment NOT found in shortcode class!</p>\n";
    
    // Check what keys are available
    echo "<h3>Available keys in shortcode class:</h3>\n";
    echo "<ul>\n";
    foreach ( array_keys( $shortcode_definitions ) as $key ) {
        echo "<li>{$key}</li>\n";
    }
    echo "</ul>\n";
}

// Test get_assessment_questions method
echo "<h2>4. get_assessment_questions Method Test</h2>\n";

$hormone_questions = $shortcodes->get_assessment_questions( 'hormone' );

if ( ! empty( $hormone_questions ) ) {
    echo "<p>✅ get_assessment_questions('hormone') returned " . count( $hormone_questions ) . " questions</p>\n";
    
    echo "<h3>Hormone Questions from get_assessment_questions:</h3>\n";
    echo "<ul>\n";
    foreach ( $hormone_questions as $question_key => $question ) {
        // Handle both array and string cases
        if ( is_array( $question ) ) {
            $type = isset( $question['type'] ) ? $question['type'] : 'unknown';
            $title = isset( $question['title'] ) ? $question['title'] : 'No title';
        } else {
            $type = 'string';
            $title = $question;
        }
        echo "<li><strong>{$question_key}</strong> ({$type}) - {$title}</li>\n";
    }
    echo "</ul>\n";
    
} else {
    echo "<p>❌ get_assessment_questions('hormone') returned empty array</p>\n";
}

// Compare with scoring system
echo "<h2>5. Comparison with Scoring System</h2>\n";

$scoring_definitions = ENNU_Scoring_System::get_all_definitions();
$scoring_hormone = isset( $scoring_definitions['hormone'] ) ? $scoring_definitions['hormone'] : null;

if ( $scoring_hormone ) {
    echo "<p>✅ Scoring system has hormone assessment with " . count( $scoring_hormone['questions'] ) . " questions</p>\n";
} else {
    echo "<p>❌ Scoring system does not have hormone assessment</p>\n";
}

if ( $scoring_hormone && isset( $shortcode_definitions['hormone'] ) ) {
    $scoring_count = count( $scoring_hormone['questions'] );
    $shortcode_count = count( $shortcode_definitions['hormone']['questions'] );
    
    if ( $scoring_count === $shortcode_count ) {
        echo "<p>✅ Both systems have the same number of hormone questions ({$scoring_count})</p>\n";
    } else {
        echo "<p>❌ Mismatch: Scoring system has {$scoring_count} questions, Shortcode class has {$shortcode_count} questions</p>\n";
    }
} elseif ( $scoring_hormone && ! isset( $shortcode_definitions['hormone'] ) ) {
    echo "<p>❌ Scoring system has hormone assessment but shortcode class does not</p>\n";
} elseif ( ! $scoring_hormone && isset( $shortcode_definitions['hormone'] ) ) {
    echo "<p>❌ Shortcode class has hormone assessment but scoring system does not</p>\n";
} else {
    echo "<p>❌ Neither system has hormone assessment</p>\n";
}

// Check if there's a timing issue
echo "<h2>6. Timing Issue Check</h2>\n";
echo "<p>Let's try to force reload the definitions...</p>\n";

// Clear any caches
delete_transient( 'ennu_assessment_definitions_v1' );

// Create a new instance and try again
$shortcodes2 = new ENNU_Assessment_Shortcodes();
$shortcodes2->init();

$hormone_questions2 = $shortcodes2->get_assessment_questions( 'hormone' );

if ( ! empty( $hormone_questions2 ) ) {
    echo "<p>✅ After cache clear: get_assessment_questions('hormone') returned " . count( $hormone_questions2 ) . " questions</p>\n";
} else {
    echo "<p>❌ After cache clear: get_assessment_questions('hormone') still returned empty array</p>\n";
}

echo "<h2>7. Conclusion</h2>\n";
if ( ! empty( $hormone_questions ) || ! empty( $hormone_questions2 ) ) {
    echo "<p>✅ The issue is resolved - hormone questions are now available</p>\n";
} else {
    echo "<p>❌ The issue persists - hormone questions are still not available</p>\n";
    echo "<p><strong>Possible causes:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>Timing issue with WordPress initialization</li>\n";
    echo "<li>Plugin loading order issue</li>\n";
    echo "<li>Class instantiation problem</li>\n";
    echo "<li>File permission or syntax error</li>\n";
    echo "</ul>\n";
}
?> 