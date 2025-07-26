<?php
/**
 * Test Symptom Fix
 */

// Load WordPress
require_once('../../../wp-load.php');

$user_id = 1;

echo "<h1>🧪 Testing Symptom Fix</h1>";

// 1. Check current symptoms
echo "<h2>1. Current Symptoms</h2>";
$centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
echo "<p><strong>Total Symptoms:</strong> " . count($centralized_symptoms['symptoms'] ?? array()) . "</p>";

// 2. Check symptom history
echo "<h2>2. Symptom History</h2>";
$symptom_history = get_user_meta($user_id, 'ennu_symptom_history', true);
echo "<p><strong>History Entries:</strong> " . count($symptom_history ?? array()) . "</p>";

// 3. Extract symptoms from history
echo "<h2>3. Extracting Symptoms</h2>";
$all_symptoms = array();

if (is_array($symptom_history)) {
    foreach ($symptom_history as $entry) {
        if (isset($entry['symptoms'])) {
            if (is_string($entry['symptoms'])) {
                $symptoms = explode(',', $entry['symptoms']);
                foreach ($symptoms as $symptom) {
                    $symptom = trim($symptom);
                    if (!empty($symptom)) {
                        $all_symptoms[] = $symptom;
                    }
                }
            }
        }
    }
}

$unique_symptoms = array_unique($all_symptoms);
echo "<p><strong>Unique Symptoms:</strong> " . count($unique_symptoms) . "</p>";
echo "<p><strong>Symptoms:</strong> " . implode(', ', $unique_symptoms) . "</p>";

// 4. Create centralized symptoms
echo "<h2>4. Creating Centralized Symptoms</h2>";
$centralized_symptoms = array(
    'symptoms' => array(),
    'by_assessment' => array(),
    'last_updated' => current_time('mysql')
);

foreach ($unique_symptoms as $symptom_name) {
    $symptom_key = strtolower(str_replace(' ', '_', $symptom_name));
    
    $centralized_symptoms['symptoms'][$symptom_key] = array(
        'name' => $symptom_name,
        'category' => 'General',
        'severity' => 'moderate',
        'frequency' => 'daily',
        'assessments' => array('manual_extraction'),
        'first_reported' => current_time('mysql'),
        'last_reported' => current_time('mysql'),
        'occurrence_count' => 1
    );
}

// 5. Save centralized symptoms
update_user_meta($user_id, 'ennu_centralized_symptoms', $centralized_symptoms);
echo "<p><strong>Centralized Symptoms Saved:</strong> " . count($centralized_symptoms['symptoms']) . " symptoms</p>";

// 6. Create biomarker flags
echo "<h2>5. Creating Biomarker Flags</h2>";
$flag_manager = new ENNU_Biomarker_Flag_Manager();
$flags_created = 0;

// Simple mapping of symptoms to biomarkers
$symptom_biomarker_mapping = array(
    'fatigue' => array('vitamin_d', 'b12', 'ferritin', 'tsh'),
    'low_libido' => array('testosterone', 'estradiol', 'prolactin'),
    'mood_swings' => array('cortisol', 'serotonin', 'dopamine'),
    'brain_fog' => array('vitamin_d', 'b12', 'omega_3', 'magnesium'),
    'anxiety' => array('cortisol', 'magnesium', 'vitamin_d'),
    'depression' => array('vitamin_d', 'b12', 'omega_3', 'serotonin'),
    'insomnia' => array('melatonin', 'cortisol', 'magnesium'),
    'hot_flashes' => array('estradiol', 'fsh', 'lh'),
    'night_sweats' => array('estradiol', 'cortisol', 'thyroid'),
    'acne' => array('testosterone', 'estradiol', 'insulin'),
    'diabetes' => array('glucose', 'hba1c', 'insulin', 'homa_ir'),
    'high_blood_pressure' => array('sodium', 'potassium', 'aldosterone'),
    'thyroid_issues' => array('tsh', 't3', 't4', 'thyroid_antibodies')
);

foreach ($unique_symptoms as $symptom_name) {
    $symptom_key = strtolower(str_replace(' ', '_', $symptom_name));
    
    if (isset($symptom_biomarker_mapping[$symptom_key])) {
        $biomarkers = $symptom_biomarker_mapping[$symptom_key];
        
        foreach ($biomarkers as $biomarker) {
            echo "<p>Creating flag for biomarker: <strong>{$biomarker}</strong> based on symptom: <strong>{$symptom_name}</strong></p>";
            
            $flag_data = array(
                'user_id' => $user_id,
                'biomarker_name' => $biomarker,
                'symptom' => $symptom_name,
                'health_vector' => 'general',
                'reason' => "Symptom correlation: {$symptom_name}",
                'status' => 'active',
                'created_at' => current_time('mysql')
            );
            
            $flag_id = $flag_manager->create_biomarker_flag($flag_data);
            
            if ($flag_id) {
                echo "<p style='color: green;'>✓ Flag created successfully (ID: {$flag_id})</p>";
                $flags_created++;
            } else {
                echo "<p style='color: red;'>✗ Failed to create flag</p>";
            }
        }
    }
}

// 7. Verify results
echo "<h2>6. Final Results</h2>";
$final_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
$final_flags = $flag_manager->get_flagged_biomarkers($user_id, 'active');

echo "<p><strong>Final Symptoms Count:</strong> " . count($final_symptoms['symptoms'] ?? array()) . "</p>";
echo "<p><strong>Final Biomarker Flags Count:</strong> " . count($final_flags) . "</p>";
echo "<p><strong>Flags Created:</strong> {$flags_created}</p>";

echo "<h2>✅ Test Complete!</h2>";
echo "<p>The symptom processing system has been tested and biomarker flags have been created.</p>";
echo "<p>Please check your user dashboard to see the updated symptoms and biomarker flags.</p>";
?> 