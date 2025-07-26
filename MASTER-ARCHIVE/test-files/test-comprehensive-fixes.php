<?php
/**
 * Comprehensive Test for All Fixes
 * 
 * Tests scoring system, pillar scores, symptom tracking, and biomarker flagging
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
    wp_die('Please log in to test the comprehensive fixes.');
}

// Get current user
$current_user = wp_get_current_user();
$user_id = $current_user->ID;

echo '<h1>ENNU Life Comprehensive Fixes Test</h1>';
echo '<p>Testing all critical system components...</p>';

// Test 1: Scoring System
echo '<h2>Test 1: Scoring System</h2>';
if (class_exists('ENNU_Scoring_System')) {
    echo '✅ ENNU_Scoring_System class exists<br>';
    
    // Test assessment type mapping
    $test_assessment_type = 'health_assessment';
    $test_form_data = array(
        'question_1' => 'Poor',
        'question_2' => 'Fair',
        'question_3' => 'Good'
    );
    
    $scores = ENNU_Scoring_System::calculate_scores_for_assessment($test_assessment_type, $test_form_data);
    
    if ($scores && isset($scores['overall_score'])) {
        echo "✅ Scoring calculation working - Overall Score: {$scores['overall_score']}<br>";
        echo "✅ Category Scores: " . print_r($scores['category_scores'], true) . "<br>";
    } else {
        echo "❌ Scoring calculation failed<br>";
    }
} else {
    echo "❌ ENNU_Scoring_System class not found<br>";
}

// Test 2: Pillar Score Calculation
echo '<h2>Test 2: Pillar Score Calculation</h2>';
if (class_exists('ENNU_Assessment_Shortcodes')) {
    echo '✅ ENNU_Assessment_Shortcodes class exists<br>';
    
    $shortcodes = new ENNU_Assessment_Shortcodes();
    $pillar_map = ENNU_Assessment_Shortcodes::get_trinity_pillar_map();
    
    echo "✅ Pillar Map: " . print_r($pillar_map, true) . "<br>";
    
    // Test user assessments data
    $user_assessments = $shortcodes->get_user_assessments_data($user_id);
    echo "✅ User Assessments: " . count($user_assessments) . " assessments found<br>";
    
    // Calculate pillar scores manually
    $pillar_scores = array('Mind' => 0, 'Body' => 0, 'Lifestyle' => 0, 'Aesthetics' => 0);
    $pillar_counts = array('Mind' => 0, 'Body' => 0, 'Lifestyle' => 0, 'Aesthetics' => 0);
    
    foreach ($user_assessments as $assessment) {
        if ($assessment['completed'] && $assessment['score'] > 0) {
            $assessment_key = str_replace('_assessment', '', $assessment['key']);
            
            switch ($assessment_key) {
                case 'hair':
                case 'skin':
                    $pillar_scores['Aesthetics'] += $assessment['score'];
                    $pillar_counts['Aesthetics']++;
                    break;
                case 'ed_treatment':
                case 'testosterone':
                case 'hormone':
                case 'menopause':
                    $pillar_scores['Body'] += $assessment['score'];
                    $pillar_counts['Body']++;
                    break;
                case 'weight_loss':
                case 'sleep':
                case 'health_optimization':
                    $pillar_scores['Lifestyle'] += $assessment['score'];
                    $pillar_counts['Lifestyle']++;
                    break;
                case 'health':
                case 'welcome':
                    $pillar_scores['Mind'] += $assessment['score'];
                    $pillar_counts['Mind']++;
                    break;
                default:
                    $pillar_scores['Lifestyle'] += $assessment['score'];
                    $pillar_counts['Lifestyle']++;
                    break;
            }
        }
    }
    
    $average_pillar_scores = array();
    foreach ($pillar_scores as $pillar => $score) {
        $average_pillar_scores[$pillar] = $pillar_counts[$pillar] > 0 ? round($score / $pillar_counts[$pillar], 1) : 0;
    }
    
    echo "✅ Calculated Pillar Scores: " . print_r($average_pillar_scores, true) . "<br>";
    echo "✅ Pillar Counts: " . print_r($pillar_counts, true) . "<br>";
    
} else {
    echo "❌ ENNU_Assessment_Shortcodes class not found<br>";
}

// Test 3: Symptom Tracking
echo '<h2>Test 3: Symptom Tracking</h2>';
if (class_exists('ENNU_Centralized_Symptoms_Manager')) {
    echo '✅ ENNU_Centralized_Symptoms_Manager class exists<br>';
    
    $centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
    $symptom_analytics = ENNU_Centralized_Symptoms_Manager::get_symptom_analytics($user_id);
    
    echo "✅ Centralized Symptoms: " . count($centralized_symptoms) . " symptoms found<br>";
    echo "✅ Symptom Analytics: " . print_r($symptom_analytics, true) . "<br>";
    
} else {
    echo "❌ ENNU_Centralized_Symptoms_Manager class not found<br>";
}

// Test 4: Biomarker Flagging
echo '<h2>Test 4: Biomarker Flagging</h2>';
if (class_exists('ENNU_Biomarker_Manager')) {
    echo '✅ ENNU_Biomarker_Manager class exists<br>';
    
    $biomarker_data = ENNU_Biomarker_Manager::get_user_biomarkers($user_id);
    echo "✅ Biomarker Data: " . count($biomarker_data) . " biomarkers found<br>";
    
    if (class_exists('ENNU_Biomarker_Flag_Manager')) {
        echo '✅ ENNU_Biomarker_Flag_Manager class exists<br>';
        
        $flag_manager = new ENNU_Biomarker_Flag_Manager();
        $flagged_biomarkers = $flag_manager->get_flagged_biomarkers($user_id);
        
        echo "✅ Flagged Biomarkers: " . count($flagged_biomarkers) . " flagged biomarkers found<br>";
        echo "✅ Flag Details: " . print_r($flagged_biomarkers, true) . "<br>";
        
    } else {
        echo "❌ ENNU_Biomarker_Flag_Manager class not found<br>";
    }
    
} else {
    echo "❌ ENNU_Biomarker_Manager class not found<br>";
}

// Test 5: Health Optimization Calculator
echo '<h2>Test 5: Health Optimization Calculator</h2>';
if (class_exists('ENNU_Health_Optimization_Calculator')) {
    echo '✅ ENNU_Health_Optimization_Calculator class exists<br>';
    
    $all_definitions = ENNU_Scoring_System::get_all_definitions();
    $health_opt_calculator = new ENNU_Health_Optimization_Calculator($user_id, $all_definitions);
    
    $triggered_vectors = $health_opt_calculator->get_triggered_vectors();
    $recommended_biomarkers = $health_opt_calculator->get_biomarker_recommendations();
    
    echo "✅ Triggered Vectors: " . count($triggered_vectors) . " vectors triggered<br>";
    echo "✅ Recommended Biomarkers: " . count($recommended_biomarkers) . " biomarkers recommended<br>";
    
} else {
    echo "❌ ENNU_Health_Optimization_Calculator class not found<br>";
}

// Test 6: Overall System Integration
echo '<h2>Test 6: Overall System Integration</h2>';

// Get current ENNU Life Score
$ennu_life_score = get_user_meta($user_id, 'ennu_life_score', true);
$average_pillar_scores = get_user_meta($user_id, 'ennu_average_pillar_scores', true);

echo "✅ Current ENNU Life Score: " . ($ennu_life_score ?: 'Not set') . "<br>";
echo "✅ Current Pillar Scores: " . print_r($average_pillar_scores, true) . "<br>";

// Test dashboard data
$dashboard_data = array(
    'user_id' => $user_id,
    'ennu_life_score' => $ennu_life_score,
    'average_pillar_scores' => $average_pillar_scores,
    'user_assessments' => $user_assessments,
    'centralized_symptoms' => $centralized_symptoms ?? array(),
    'symptom_analytics' => $symptom_analytics ?? array(),
    'biomarker_data' => $biomarker_data ?? array(),
    'flagged_biomarkers' => $flagged_biomarkers ?? array(),
    'triggered_vectors' => $triggered_vectors ?? array(),
    'recommended_biomarkers' => $recommended_biomarkers ?? array()
);

echo "✅ Dashboard Data Structure: " . print_r($dashboard_data, true) . "<br>";

echo '<h2>Test Summary</h2>';
echo '<p>All critical system components have been tested. Check the results above to verify functionality.</p>';

// Log the test results
error_log('[ENNU TEST] Comprehensive fixes test completed for user ' . $user_id);
error_log('[ENNU TEST] Test results: ' . print_r($dashboard_data, true)); 