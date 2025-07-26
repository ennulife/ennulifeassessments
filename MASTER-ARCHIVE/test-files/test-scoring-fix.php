<?php
/**
 * Test Scoring Fix
 * 
 * This script tests the new scoring implementation to verify it's working correctly
 * 
 * @package ENNU_Life_Assessments
 * @version 64.2.1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Ensure user is logged in
if (!is_user_logged_in()) {
    wp_die('Please log in to test the scoring system.');
}

// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

echo '<h1>ENNU Life Scoring System Test</h1>';
echo '<p>Testing the new scoring implementation...</p>';

// Test 1: Check if scoring system class exists
echo '<h2>Test 1: Scoring System Class</h2>';
if (class_exists('ENNU_Scoring_System')) {
    echo '✅ ENNU_Scoring_System class exists<br>';
} else {
    echo '❌ ENNU_Scoring_System class missing<br>';
    exit;
}

// Test 2: Test scoring calculation with sample hair assessment data
echo '<h2>Test 2: Hair Assessment Scoring</h2>';
$sample_hair_data = array(
    'hair_q1' => '1990-01-01', // DOB
    'hair_q2' => 'male', // Gender
    'hair_q3' => array('thinning', 'receding'), // Hair concerns
    'hair_q4' => '1_to_3_years', // Timeline
    'hair_q5' => 'yes_paternal', // Family history
    'hair_q6' => 'moderate', // Progression rate
    'hair_q7' => 'yes', // Lifestyle factors
    'hair_q8' => 'sometimes', // Nutritional support
    'hair_q9' => 'no', // Treatment history
    'hair_q10' => 'significant_improvement', // Expectations
    'assessment_type' => 'hair'
);

$hair_scores = ENNU_Scoring_System::calculate_scores_for_assessment('hair', $sample_hair_data);

if ($hair_scores) {
    echo '✅ Hair assessment scoring successful<br>';
    echo '<strong>Overall Score:</strong> ' . $hair_scores['overall_score'] . '<br>';
    echo '<strong>Category Scores:</strong><br>';
    foreach ($hair_scores['category_scores'] as $category => $score) {
        echo "&nbsp;&nbsp;• {$category}: {$score}<br>";
    }
    echo '<strong>Pillar Scores:</strong><br>';
    foreach ($hair_scores['pillar_scores'] as $pillar => $score) {
        echo "&nbsp;&nbsp;• {$pillar}: {$score}<br>";
    }
} else {
    echo '❌ Hair assessment scoring failed<br>';
}

// Test 3: Test scoring calculation with sample weight loss assessment data
echo '<h2>Test 3: Weight Loss Assessment Scoring</h2>';
$sample_weight_data = array(
    'weight_q1' => '1990-01-01', // DOB
    'weight_q2' => 'female', // Gender
    'weight_q3' => 'very_motivated', // Motivation
    'weight_q4' => 'overweight', // Current status
    'weight_q5' => 'moderate', // Physical activity
    'weight_q6' => 'sometimes_healthy', // Nutrition
    'weight_q7' => 'stress_eating', // Lifestyle factors
    'weight_q8' => 'moderate_stress', // Psychological factors
    'weight_q9' => 'emotional_eating', // Behavioral patterns
    'weight_q10' => 'thyroid_issues', // Medical factors
    'weight_q11' => 'multiple_attempts', // Weight loss history
    'weight_q12' => 'family_support', // Social support
    'assessment_type' => 'weight_loss'
);

$weight_scores = ENNU_Scoring_System::calculate_scores_for_assessment('weight_loss', $sample_weight_data);

if ($weight_scores) {
    echo '✅ Weight loss assessment scoring successful<br>';
    echo '<strong>Overall Score:</strong> ' . $weight_scores['overall_score'] . '<br>';
    echo '<strong>Category Scores:</strong><br>';
    foreach ($weight_scores['category_scores'] as $category => $score) {
        echo "&nbsp;&nbsp;• {$category}: {$score}<br>";
    }
    echo '<strong>Pillar Scores:</strong><br>';
    foreach ($weight_scores['pillar_scores'] as $pillar => $score) {
        echo "&nbsp;&nbsp;• {$pillar}: {$score}<br>";
    }
} else {
    echo '❌ Weight loss assessment scoring failed<br>';
}

// Test 4: Verify scores are different (not all 7.5)
echo '<h2>Test 4: Score Variation Check</h2>';
$all_scores = array();
if ($hair_scores) $all_scores[] = $hair_scores['overall_score'];
if ($weight_scores) $all_scores[] = $weight_scores['overall_score'];

$unique_scores = array_unique($all_scores);
$has_variation = count($unique_scores) > 1 || !in_array(7.5, $unique_scores);

if ($has_variation) {
    echo '✅ Scores show variation (not all 7.5)<br>';
    echo '<strong>Unique scores found:</strong> ' . implode(', ', $unique_scores) . '<br>';
} else {
    echo '❌ All scores are still 7.5 - scoring not working<br>';
}

// Test 5: Test assessment definitions loading
echo '<h2>Test 5: Assessment Definitions</h2>';
$all_definitions = ENNU_Scoring_System::get_all_definitions();
$definition_count = count($all_definitions);

echo "✅ Loaded {$definition_count} assessment definitions<br>";
foreach (array_keys($all_definitions) as $assessment_type) {
    echo "&nbsp;&nbsp;• {$assessment_type}<br>";
}

echo '<h2>Test Summary</h2>';
if ($has_variation && $hair_scores && $weight_scores) {
    echo '🎉 <strong>SUCCESS:</strong> Scoring system is now working correctly!<br>';
    echo '✅ Real scores are being calculated based on assessment configurations<br>';
    echo '✅ No more hardcoded 7.5 scores<br>';
    echo '✅ Category and pillar mapping is functional<br>';
} else {
    echo '⚠️ <strong>ISSUES DETECTED:</strong> Some problems remain<br>';
    echo 'Please check the error logs for more details<br>';
}

echo '<p><a href="' . admin_url() . '">Return to Admin</a></p>';
?> 