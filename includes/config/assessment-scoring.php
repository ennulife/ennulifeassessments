<?php
/**
 * ENNU Life Assessment Scoring Configuration - FINAL & COMPLETE
 *
 * @package ENNU_Life
 * @version 26.0.53
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Begin with the inline scoring array, which serves as a fallback/default.
$scoring = array(); // Modular configuration – assessment scoring loaded dynamically

// Optionally extend/override with per-assessment scoring files found in includes/config/scoring/*.php
$scoring_dir = __DIR__ . '/scoring';
if ( is_dir( $scoring_dir ) ) {
    foreach ( glob( $scoring_dir . '/*.php' ) as $file ) {
        $data = include $file;
        if ( ! is_array( $data ) ) {
            continue;
        }

        $looks_like_single = array_key_first( $data ) === 'answers' || array_key_first($data) === 0;

        if ( $looks_like_single ) {
            $key = basename( $file, '.php' );
            $scoring[ $key ] = $data;
        } else {
            foreach ( $data as $assessment_key => $rules_arr ) {
                if ( is_array( $rules_arr ) ) {
                    $scoring[ $assessment_key ] = $rules_arr;
                }
            }
        }
    }
}

return $scoring; 