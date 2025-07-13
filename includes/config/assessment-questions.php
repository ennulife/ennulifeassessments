<?php
/**
 * ENNU Life Assessment Questions Configuration - FINAL & COMPLETE
 *
 * This file centralizes all assessment questions with the full, superior architecture.
 *
 * @package ENNU_Life
 * @version 26.0.53
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Begin with the inline questions array, which serves as a fallback/default.
$questions = array(); // Modular configuration – all assessment question files loaded dynamically below

// Optionally extend/override with per-assessment files found in includes/config/questions/*.php
$questions_dir = __DIR__ . '/questions';
if ( is_dir( $questions_dir ) ) {
    foreach ( glob( $questions_dir . '/*.php' ) as $file ) {
        $data = include $file;
        if ( ! is_array( $data ) ) {
            continue;
        }

        // Two possible formats:
        // A. File returns the array for a single assessment (numerically indexed questions)
        // B. File returns an associative map of many assessments (legacy full config)

        $looks_like_single = array_key_first( $data ) === 0; // numeric keys imply single assessment list

        if ( $looks_like_single ) {
            $key = basename( $file, '.php' );
            $questions[ $key ] = $data;
        } else {
            // Merge each assessment inside the file
            foreach ( $data as $assessment_key => $q_arr ) {
                if ( is_array( $q_arr ) ) {
                    $questions[ $assessment_key ] = $q_arr;
                }
            }
        }
    }
}

return $questions; 