<?php
/**
 * Debug Script to Check Assessment Loading
 * 
 * This script will help us determine if the hormone assessment
 * is being loaded properly by the scoring system.
 */

// Load WordPress
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

echo "<h1>🔍 Assessment Loading Debug</h1>\n";

// Check if the scoring system class exists
if ( ! class_exists( 'ENNU_Scoring_System' ) ) {
    echo "<p>❌ ENNU_Scoring_System class not found</p>\n";
    exit;
}

echo "<p>✅ ENNU_Scoring_System class found</p>\n";

// Get all definitions
$all_definitions = ENNU_Scoring_System::get_all_definitions();

echo "<h2>1. All Definitions Check</h2>\n";
echo "<p>Total assessments loaded: " . count( $all_definitions ) . "</p>\n";

if ( empty( $all_definitions ) ) {
    echo "<p>❌ No assessments loaded!</p>\n";
    exit;
}

echo "<h3>Available Assessments:</h3>\n";
echo "<ul>\n";
foreach ( $all_definitions as $key => $config ) {
    $question_count = isset( $config['questions'] ) ? count( $config['questions'] ) : 'N/A';
    echo "<li><strong>{$key}</strong> - {$config['title']} ({$question_count} questions)</li>\n";
}
echo "</ul>\n";

// Check specifically for hormone assessment
echo "<h2>2. Hormone Assessment Check</h2>\n";

if ( isset( $all_definitions['hormone'] ) ) {
    $hormone_config = $all_definitions['hormone'];
    echo "<p>✅ Hormone assessment found</p>\n";
    echo "<p><strong>Title:</strong> {$hormone_config['title']}</p>\n";
    echo "<p><strong>Engine:</strong> {$hormone_config['assessment_engine']}</p>\n";
    echo "<p><strong>Questions:</strong> " . count( $hormone_config['questions'] ) . "</p>\n";
    
    echo "<h3>Hormone Assessment Questions:</h3>\n";
    echo "<ul>\n";
    foreach ( $hormone_config['questions'] as $question_key => $question ) {
        $type = $question['type'];
        $title = $question['title'];
        echo "<li><strong>{$question_key}</strong> ({$type}) - {$title}</li>\n";
    }
    echo "</ul>\n";
    
} else {
    echo "<p>❌ Hormone assessment NOT found!</p>\n";
    
    // Check what keys are available
    echo "<h3>Available keys:</h3>\n";
    echo "<ul>\n";
    foreach ( array_keys( $all_definitions ) as $key ) {
        echo "<li>{$key}</li>\n";
    }
    echo "</ul>\n";
}

// Check the config directory
echo "<h2>3. Config Directory Check</h2>\n";
$config_dir = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessments/';
echo "<p><strong>Config directory:</strong> {$config_dir}</p>\n";

if ( is_dir( $config_dir ) ) {
    echo "<p>✅ Config directory exists</p>\n";
    
    $files = glob( $config_dir . '*.php' );
    echo "<p><strong>PHP files found:</strong> " . count( $files ) . "</p>\n";
    
    echo "<h3>Assessment Files:</h3>\n";
    echo "<ul>\n";
    foreach ( $files as $file ) {
        $filename = basename( $file );
        echo "<li>{$filename}</li>\n";
    }
    echo "</ul>\n";
    
    // Check if hormone.php exists
    $hormone_file = $config_dir . 'hormone.php';
    if ( file_exists( $hormone_file ) ) {
        echo "<p>✅ hormone.php file exists</p>\n";
        
        // Try to load it directly
        echo "<h3>4. Direct File Loading Test</h3>\n";
        $direct_definition = require $hormone_file;
        
        if ( is_array( $direct_definition ) ) {
            echo "<p>✅ hormone.php loads successfully</p>\n";
            echo "<p><strong>Title:</strong> {$direct_definition['title']}</p>\n";
            echo "<p><strong>Questions:</strong> " . count( $direct_definition['questions'] ) . "</p>\n";
        } else {
            echo "<p>❌ hormone.php does not return an array</p>\n";
        }
        
    } else {
        echo "<p>❌ hormone.php file does not exist</p>\n";
    }
    
} else {
    echo "<p>❌ Config directory does not exist</p>\n";
}

// Check transients
echo "<h2>5. Transient Check</h2>\n";
$cached_definitions = get_transient( 'ennu_assessment_definitions_v1' );
if ( false !== $cached_definitions ) {
    echo "<p>✅ Cached definitions found</p>\n";
    echo "<p><strong>Cached assessments:</strong> " . count( $cached_definitions ) . "</p>\n";
    
    if ( isset( $cached_definitions['hormone'] ) ) {
        echo "<p>✅ Hormone assessment found in cache</p>\n";
    } else {
        echo "<p>❌ Hormone assessment NOT found in cache</p>\n";
    }
} else {
    echo "<p>❌ No cached definitions found</p>\n";
}

// Test the shortcode class
echo "<h2>6. Shortcode Class Test</h2>\n";
if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
    echo "<p>✅ ENNU_Assessment_Shortcodes class found</p>\n";
    
    // Try to get hormone questions from shortcode class
    $shortcodes = new ENNU_Assessment_Shortcodes();
    $hormone_questions = $shortcodes->get_assessment_questions( 'hormone' );
    
    if ( ! empty( $hormone_questions ) ) {
        echo "<p>✅ Hormone questions retrieved from shortcode class</p>\n";
        echo "<p><strong>Questions found:</strong> " . count( $hormone_questions ) . "</p>\n";
    } else {
        echo "<p>❌ No hormone questions retrieved from shortcode class</p>\n";
    }
    
} else {
    echo "<p>❌ ENNU_Assessment_Shortcodes class not found</p>\n";
}

echo "<h2>7. Next Steps</h2>\n";
echo "<p><strong>If hormone assessment is not found:</strong></p>\n";
echo "<ul>\n";
echo "<li>Clear the transient cache</li>\n";
echo "<li>Check file permissions on hormone.php</li>\n";
echo "<li>Verify the file syntax is correct</li>\n";
echo "<li>Restart the web server</li>\n";
echo "</ul>\n";
?> 