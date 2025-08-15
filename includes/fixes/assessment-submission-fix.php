<?php
/**
 * ENNU Assessment Submission Fix
 * 
 * This file contains fixes for assessment submission issues.
 * 
 * @package ENNU_Life_Assessments
 * @version 64.3.31
 */

// Enhanced user meta saving with better error handling
add_action( "ennu_assessment_completed", function( $user_id, $assessment_data ) {
    if ( ! $user_id || ! is_array( $assessment_data ) ) {
        // REMOVED: error_log( "ENNU Assessment Fix: Invalid user_id or assessment_data" );
        return;
    }
    
    // Ensure user meta is properly saved
    if ( isset( $assessment_data["form_data"] ) ) {
        $form_data = $assessment_data["form_data"];
        
        // Save each form field as user meta
        foreach ( $form_data as $key => $value ) {
            if ( ! empty( $key ) && $value !== "" ) {
                $meta_key = "ennu_" . $assessment_data["assessment_type"] . "_" . $key;
                $result = update_user_meta( $user_id, $meta_key, $value );
                
                if ( ! $result ) {
                    // REMOVED: error_log( "ENNU Assessment Fix: Failed to save meta key: " . $meta_key );
                }
            }
        }
        
		// REMOVED: // REMOVED DEBUG LOG: error_log( "ENNU Assessment Fix: Completed saving user meta for user " . $user_id );
    }
}, 10, 2 );

// Force refresh assessment definitions cache on plugin load
add_action( "init", function() {
    if ( class_exists( "ENNU_Scoring_System" ) ) {
        ENNU_Scoring_System::clear_configuration_cache();
    }
}, 1 );

// Ensure assessment shortcodes are properly initialized
add_action( "plugins_loaded", function() {
    if ( class_exists( "ENNU_Assessment_Shortcodes" ) ) {
        // Get the existing instance from the main plugin
        $plugin = ennu_life();
        if ( $plugin && method_exists( $plugin, 'get_shortcodes' ) ) {
            $shortcodes = $plugin->get_shortcodes();
            if ( $shortcodes && method_exists( $shortcodes, 'init' ) ) {
                $shortcodes->init();
            }
        }
    }
}, 20 );
