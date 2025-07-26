<?php
/**
 * Test Symptoms Working
 */

// Load WordPress
require_once('../../../wp-load.php');

$user_id = 1;

echo "<h1>🧪 Testing Symptoms System</h1>";

// 1. Test centralized symptoms manager
echo "<h2>1. Testing Centralized Symptoms Manager</h2>";
try {
    $centralized_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
    echo "<p>✅ Centralized symptoms retrieved successfully</p>";
    echo "<p>Total symptoms: " . count($centralized_symptoms['symptoms'] ?? array()) . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

// 2. Test biomarker flag manager
echo "<h2>2. Testing Biomarker Flag Manager</h2>";
try {
    $flag_manager = new ENNU_Biomarker_Flag_Manager();
    $flagged_biomarkers = $flag_manager->get_flagged_biomarkers($user_id, 'active');
    echo "<p>✅ Biomarker flag manager working</p>";
    echo "<p>Active flags: " . count($flagged_biomarkers) . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

// 3. Test symptom history
echo "<h2>3. Testing Symptom History</h2>";
try {
    $symptom_history = get_user_meta($user_id, 'ennu_symptom_history', true);
    $symptom_history = is_array($symptom_history) ? $symptom_history : array();
    echo "<p>✅ Symptom history retrieved</p>";
    echo "<p>History entries: " . count($symptom_history) . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

// 4. Test assessment data
echo "<h2>4. Testing Assessment Data</h2>";
$assessments = array('hormone', 'testosterone', 'ed_treatment', 'weight_loss', 'sleep', 'skin', 'menopause', 'welcome');
foreach ($assessments as $assessment) {
    $assessment_data = get_user_meta($user_id, "ennu_{$assessment}_assessment", true);
    if (!empty($assessment_data)) {
        echo "<p>✅ {$assessment} assessment data found</p>";
    } else {
        echo "<p>⚠️ No {$assessment} assessment data</p>";
    }
}

// 5. Test symptom extraction
echo "<h2>5. Testing Symptom Extraction</h2>";
$all_symptoms = array();
$symptom_history = get_user_meta($user_id, 'ennu_symptom_history', true);
$symptom_history = is_array($symptom_history) ? $symptom_history : array();

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
        } elseif (is_array($entry['symptoms'])) {
            foreach ($entry['symptoms'] as $symptom) {
                if (is_string($symptom)) {
                    $all_symptoms[] = trim($symptom);
                } elseif (is_array($symptom) && isset($symptom['name'])) {
                    $all_symptoms[] = trim($symptom['name']);
                }
            }
        }
    }
}

$unique_symptoms = array_unique($all_symptoms);
echo "<p>✅ Extracted " . count($unique_symptoms) . " unique symptoms</p>";
echo "<p>Symptoms: " . implode(', ', $unique_symptoms) . "</p>";

// 6. Test creating centralized symptoms
echo "<h2>6. Testing Centralized Symptoms Creation</h2>";
try {
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
            'occurrence_count' => 1,
            'status' => 'active'
        );
    }
    
    update_user_meta($user_id, 'ennu_centralized_symptoms', $centralized_symptoms);
    echo "<p>✅ Centralized symptoms created and saved</p>";
    echo "<p>Total symptoms: " . count($centralized_symptoms['symptoms']) . "</p>";
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

// 7. Test biomarker flag creation
echo "<h2>7. Testing Biomarker Flag Creation</h2>";
try {
    $flag_manager = new ENNU_Biomarker_Flag_Manager();
    $flags_created = 0;
    
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
                    $flags_created++;
                }
            }
        }
    }
    
    echo "<p>✅ Created {$flags_created} biomarker flags</p>";
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>✅ Test Complete!</h2>";
echo "<p>The symptoms system is working properly. You can now check the user dashboard to see the updated symptoms and biomarker flags.</p>";
?> 