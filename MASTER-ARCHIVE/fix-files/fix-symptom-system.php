<?php
/**
 * Comprehensive Symptom System Fix
 * 
 * This file fixes the symptom extraction and biomarker flagging system
 * by manually triggering the process for existing user data.
 */

// Load WordPress
require_once('../../../wp-load.php');

$user_id = 1;

echo "<h1>🔧 Comprehensive Symptom System Fix</h1>";

// 1. Check current user symptoms
echo "<h2>1. Current User Symptoms</h2>";
$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
echo "<p><strong>Total Symptoms:</strong> " . count($centralized_symptoms['symptoms'] ?? array()) . "</p>";
echo "<p><strong>Assessments with Symptoms:</strong> " . count($centralized_symptoms['by_assessment'] ?? array()) . "</p>";

// 2. Check user assessment data
echo "<h2>2. User Assessment Data</h2>";
$user_meta = get_user_meta($user_id);
$assessment_data = array();

foreach ($user_meta as $meta_key => $meta_values) {
    if (strpos($meta_key, '_q1') !== false || strpos($meta_key, '_q2') !== false || strpos($meta_key, '_q3') !== false) {
        $assessment_data[$meta_key] = $meta_values[0] ?? '';
    }
}

echo "<p><strong>Assessment Questions Found:</strong> " . count($assessment_data) . "</p>";

// 3. Extract symptoms from assessment data
echo "<h2>3. Extracting Symptoms from Assessment Data</h2>";

$extracted_symptoms = array();

// Check Hormone Assessment
if (isset($assessment_data['hormone_q1'])) {
    $hormone_symptoms = explode(',', $assessment_data['hormone_q1']);
    foreach ($hormone_symptoms as $symptom) {
        $symptom = trim($symptom);
        if (!empty($symptom)) {
            $extracted_symptoms[] = $symptom;
        }
    }
    echo "<p><strong>Hormone Assessment Symptoms:</strong> " . implode(', ', $hormone_symptoms) . "</p>";
}

// Check Testosterone Assessment
if (isset($assessment_data['testosterone_q1'])) {
    $testosterone_symptoms = explode(',', $assessment_data['testosterone_q1']);
    foreach ($testosterone_symptoms as $symptom) {
        $symptom = trim($symptom);
        if (!empty($symptom)) {
            $extracted_symptoms[] = $symptom;
        }
    }
    echo "<p><strong>Testosterone Assessment Symptoms:</strong> " . implode(', ', $testosterone_symptoms) . "</p>";
}

// Check ED Treatment Assessment
if (isset($assessment_data['ed_treatment_q1'])) {
    $ed_symptoms = explode(',', $assessment_data['ed_treatment_q1']);
    foreach ($ed_symptoms as $symptom) {
        $symptom = trim($symptom);
        if (!empty($symptom)) {
            $extracted_symptoms[] = $symptom;
        }
    }
    echo "<p><strong>ED Treatment Assessment Symptoms:</strong> " . implode(', ', $ed_symptoms) . "</p>";
}

echo "<p><strong>Total Extracted Symptoms:</strong> " . count(array_unique($extracted_symptoms)) . "</p>";

// 4. Manually trigger symptom extraction
echo "<h2>4. Manually Triggering Symptom Extraction</h2>";

// Trigger for each assessment type
$assessment_types = array('hormone', 'testosterone', 'ed_treatment');

foreach ($assessment_types as $assessment_type) {
    echo "<p>Triggering symptom extraction for: <strong>{$assessment_type}</strong></p>";
    
    // Manually trigger the assessment completion hook
    do_action('ennu_assessment_completed', $user_id, $assessment_type);
    
    // Wait a moment for processing
    usleep(100000); // 0.1 seconds
}

// 5. Check symptom correlation matrix
echo "<h2>5. Checking Symptom Correlation Matrix</h2>";

$correlation_matrix = include('includes/config/symptom-biomarker-correlations.php');

echo "<p><strong>Total Symptoms in Matrix:</strong> " . count($correlation_matrix['symptoms']) . "</p>";
echo "<p><strong>Total Health Vectors:</strong> " . count($correlation_matrix['health_vectors']) . "</p>";
echo "<p><strong>Total Biomarkers:</strong> " . count($correlation_matrix['biomarkers']) . "</p>";

// 6. Create biomarker flags based on symptoms
echo "<h2>6. Creating Biomarker Flags</h2>";

$flag_manager = new ENNU_Biomarker_Flag_Manager();

foreach ($extracted_symptoms as $symptom) {
    if (isset($correlation_matrix['symptoms'][$symptom])) {
        $health_vectors = $correlation_matrix['symptoms'][$symptom];
        
        foreach ($health_vectors as $vector) {
            if (isset($correlation_matrix['health_vectors'][$vector])) {
                $biomarkers = $correlation_matrix['health_vectors'][$vector];
                
                foreach ($biomarkers as $biomarker) {
                    echo "<p>Creating flag for biomarker: <strong>{$biomarker}</strong> based on symptom: <strong>{$symptom}</strong></p>";
                    
                    // Create biomarker flag
                    $flag_data = array(
                        'user_id' => $user_id,
                        'biomarker_name' => $biomarker,
                        'symptom' => $symptom,
                        'health_vector' => $vector,
                        'reason' => "Symptom correlation: {$symptom}",
                        'status' => 'active',
                        'created_at' => current_time('mysql')
                    );
                    
                    $flag_id = $flag_manager->create_biomarker_flag($flag_data);
                    
                    if ($flag_id) {
                        echo "<p style='color: green;'>✓ Flag created successfully (ID: {$flag_id})</p>";
                    } else {
                        echo "<p style='color: red;'>✗ Failed to create flag</p>";
                    }
                }
            }
        }
    }
}

// 7. Verify results
echo "<h2>7. Verifying Results</h2>";

$final_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
$final_flags = $flag_manager->get_flagged_biomarkers($user_id, 'active');

echo "<p><strong>Final Symptoms Count:</strong> " . count($final_symptoms['symptoms'] ?? array()) . "</p>";
echo "<p><strong>Final Biomarker Flags Count:</strong> " . count($final_flags) . "</p>";

echo "<h3>Final Symptom List:</h3>";
if (!empty($final_symptoms['symptoms'])) {
    echo "<ul>";
    foreach ($final_symptoms['symptoms'] as $symptom_key => $symptom_data) {
        echo "<li><strong>{$symptom_data['name']}</strong> - {$symptom_data['severity']} severity</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No symptoms found</p>";
}

echo "<h3>Final Biomarker Flags:</h3>";
if (!empty($final_flags)) {
    echo "<ul>";
    foreach ($final_flags as $flag_id => $flag_data) {
        echo "<li><strong>{$flag_data['biomarker_name']}</strong> - {$flag_data['reason']}</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No biomarker flags found</p>";
}

echo "<h2>✅ Symptom System Fix Complete!</h2>";
echo "<p>The symptom extraction and biomarker flagging system has been manually triggered.</p>";
echo "<p>Please check your user dashboard to see the updated symptoms and biomarker flags.</p>";
?> 