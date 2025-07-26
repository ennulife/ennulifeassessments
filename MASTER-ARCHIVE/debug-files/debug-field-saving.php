<?php
/**
 * Simple Field Saving Debug Script
 */

// Load WordPress
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

// Check if we can access the plugin
if ( ! class_exists( 'ENNU_Life_Enhanced_Plugin' ) ) {
    die( 'Plugin not loaded' );
}

$plugin = ENNU_Life_Enhanced_Plugin::get_instance();
$shortcodes = $plugin->get_shortcode_handler();

if ( ! $shortcodes ) {
    die( 'Shortcode handler not found' );
}

// Get assessment definitions
$all_definitions = $shortcodes->get_all_assessment_definitions();

echo "=== ENNU FIELD SAVING DEBUG ===\n\n";

echo "Total assessments: " . count( $all_definitions ) . "\n\n";

foreach ( $all_definitions as $assessment_type => $config ) {
    echo "Assessment: $assessment_type\n";
    echo "Title: " . ( $config['title'] ?? 'No title' ) . "\n";
    echo "Engine: " . ( $config['assessment_engine'] ?? 'Not specified' ) . "\n";
    
    if ( isset( $config['questions'] ) ) {
        echo "Questions: " . count( $config['questions'] ) . "\n";
        
        foreach ( $config['questions'] as $question_id => $question_def ) {
            $type = $question_def['type'] ?? 'unknown';
            $global_key = $question_def['global_key'] ?? '';
            
            echo "  - $question_id ($type)";
            if ( $global_key ) {
                echo " [Global: $global_key]";
            }
            echo "\n";
            
            // Show expected form field name
            $expected_field = $question_id;
            if ( $type === 'multiselect' ) {
                $expected_field = $question_id . '[]';
            }
            echo "    Expected form field: $expected_field\n";
            
            // Show expected meta key
            $meta_key = '';
            if ( $global_key ) {
                $meta_key = "ennu_global_$global_key";
            } else {
                $meta_key = "ennu_{$assessment_type}_{$question_id}";
            }
            echo "    Expected meta key: $meta_key\n";
        }
    }
    echo "\n";
}

// Check current user data
$current_user = wp_get_current_user();
if ( $current_user->ID ) {
    echo "Current user: " . $current_user->user_email . " (ID: " . $current_user->ID . ")\n";
    
    $user_meta = get_user_meta( $current_user->ID );
    $ennu_meta = array();
    
    foreach ( $user_meta as $meta_key => $meta_values ) {
        if ( strpos( $meta_key, 'ennu_' ) === 0 ) {
            $value = is_array( $meta_values ) ? $meta_values[0] : $meta_values;
            $ennu_meta[ $meta_key ] = $value;
        }
    }
    
    if ( ! empty( $ennu_meta ) ) {
        echo "Existing ENNU meta:\n";
        foreach ( $ennu_meta as $key => $value ) {
            echo "  $key: $value\n";
        }
    } else {
        echo "No existing ENNU meta found\n";
    }
} else {
    echo "No user logged in\n";
}

echo "\n=== DEBUG COMPLETE ===\n";
?> 