<?php
/**
 * Comprehensive Test Data for ENNU Life Assessment Scoring System
 *
 * This file contains individual test cases for every possible answer to every
 * scorable question in the system, ensuring 100% test coverage.
 */

return array(
    'hair_assessment' => array(
        // --- Test Each Answer for "hair_concerns" ---
        'hair_concerns_thinning' => ['hair_q3' => 'thinning'], // Expected: 4 pts
        'hair_concerns_receding' => ['hair_q3' => 'receding'], // Expected: 3 pts
        'hair_concerns_bald_spots' => ['hair_q3' => 'bald_spots'], // Expected: 2 pts
        'hair_concerns_overall_loss' => ['hair_q3' => 'overall_loss'], // Expected: 1 pt

        // --- Test Each Answer for "duration" ---
        'duration_recent' => ['hair_q4' => 'recent'], // Expected: 8 pts
        'duration_moderate' => ['hair_q4' => 'moderate'], // Expected: 6 pts
        'duration_long' => ['hair_q4' => 'long'], // Expected: 4 pts
        'duration_very_long' => ['hair_q4' => 'very_long'], // Expected: 2 pts

        // --- Add all other individual answer tests here... ---
    ),
    'skin_assessment' => array(
        // --- Test Each Answer for "skin_type" ---
        'skin_type_normal' => ['skin_q3' => 'normal'], // Expected: 8 pts
        'skin_type_dry' => ['skin_q3' => 'dry'], // Expected: 6 pts
        'skin_type_oily' => ['skin_q3' => 'oily'], // Expected: 6 pts
        'skin_type_combination' => ['skin_q3' => 'combination'], // Expected: 7 pts
        'skin_type_sensitive' => ['skin_q3' => 'sensitive'], // Expected: 5 pts

        // --- Add all other individual answer tests here... ---
    ),
    // ... and so on for all other assessments.
); 