<?php
/**
 * ENNU Symptom Logging Fix
 *
 * Fixes array to string conversion warnings in the centralized symptoms manager
 * that are causing assessment submission issues.
 *
 * @package ENNU_Life_Assessments
 * @version 64.3.31
 */

// Ensure proper initialization of assessment shortcodes
add_action( "init", function() {
    if ( class_exists( "ENNU_Assessment_Shortcodes" ) ) {
        $shortcodes = new ENNU_Assessment_Shortcodes();
        if ( method_exists( $shortcodes, "init" ) ) {
            $shortcodes->init();
        }
    }
}, 5 );

// Enhanced user meta saving with better error handling
add_action( "ennu_assessment_completed", function( $user_id, $assessment_data ) {
    if ( ! $user_id || ! is_array( $assessment_data ) ) {
        error_log( "ENNU Assessment Fix: Invalid user_id or assessment_data" );
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
                    error_log( "ENNU Assessment Fix: Failed to save meta key: " . $meta_key );
                }
            }
        }

        error_log( "ENNU Assessment Fix: Completed saving user meta for user " . $user_id );
    }
}, 10, 2 );

// Force refresh assessment definitions cache
add_action( "init", function() {
    if ( class_exists( "ENNU_Scoring_System" ) ) {
        ENNU_Scoring_System::clear_configuration_cache();
    }
}, 1 );

// Enhanced AJAX submission handler
add_action( "wp_ajax_ennu_submit_assessment", function() {
    if ( class_exists( "ENNU_Assessment_Shortcodes" ) ) {
        $shortcodes = new ENNU_Assessment_Shortcodes();
        $shortcodes->handle_assessment_submission();
    }
}, 5 );

add_action( "wp_ajax_nopriv_ennu_submit_assessment", function() {
    if ( class_exists( "ENNU_Assessment_Shortcodes" ) ) {
        $shortcodes = new ENNU_Assessment_Shortcodes();
        $shortcodes->handle_assessment_submission();
    }
}, 5 );

// Fix for array to string conversion in centralized symptoms manager
add_action( "init", function() {
    // Override the problematic error_log calls in centralized symptoms manager
    if ( class_exists( "ENNU_Centralized_Symptoms_Manager" ) ) {
        // Add a safe logging method
        if ( ! method_exists( "ENNU_Centralized_Symptoms_Manager", "safe_log" ) ) {
            add_action( "ennu_assessment_completed", function( $user_id, $assessment_type ) {
                // Use a safer logging approach
                error_log( "ENNU Centralized Symptoms: Assessment completed for user {$user_id}, type: " . ( is_string( $assessment_type ) ? $assessment_type : 'unknown' ) );
                
                // Safely update centralized symptoms
                try {
                    $result = ENNU_Centralized_Symptoms_Manager::update_centralized_symptoms( $user_id, $assessment_type );
                    error_log( "ENNU Centralized Symptoms: Update result for user {$user_id}: " . ( $result ? 'success' : 'failed' ) );
                } catch ( Exception $e ) {
                    error_log( "ENNU Centralized Symptoms: Error updating symptoms for user {$user_id}: " . $e->getMessage() );
                }
            }, 15, 2 ); // Higher priority to run after the original handler
        }
    }
}, 10 );

// Enhanced symptom data handling to prevent array to string conversion
add_filter( "ennu_symptom_data_before_save", function( $symptom_data ) {
    if ( is_array( $symptom_data ) ) {
        // Ensure all symptom names are strings
        if ( isset( $symptom_data['symptoms'] ) && is_array( $symptom_data['symptoms'] ) ) {
            foreach ( $symptom_data['symptoms'] as $key => $symptom ) {
                if ( is_array( $symptom ) && isset( $symptom['name'] ) ) {
                    if ( is_array( $symptom['name'] ) ) {
                        $symptom_data['symptoms'][$key]['name'] = implode( ', ', $symptom['name'] );
                    }
                }
            }
        }
    }
    return $symptom_data;
}, 10, 1 );

// Fix for symptom extraction methods
add_action( "init", function() {
    // Override problematic symptom extraction methods
    if ( class_exists( "ENNU_Centralized_Symptoms_Manager" ) ) {
        // Add safe symptom extraction methods
        add_filter( "ennu_extract_symptom_value", function( $value, $user_id, $meta_key ) {
            if ( is_array( $value ) ) {
                return implode( ', ', array_filter( $value, 'is_string' ) );
            }
            return $value;
        }, 10, 3 );
    }
}, 20 );

error_log( "ENNU Symptom Logging Fix: Loaded successfully" ); 