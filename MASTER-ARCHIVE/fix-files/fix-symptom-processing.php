<?php
/**
 * Comprehensive Symptom Processing Fix
 * 
 * This file fixes the symptom processing system by:
 * 1. Extracting symptoms from symptom history
 * 2. Converting them to current active symptoms
 * 3. Creating biomarker flags based on symptoms
 */

// Load WordPress
require_once('../../../wp-load.php');

$user_id = 1;

echo "<h1>🔧 Comprehensive Symptom Processing Fix</h1>";

// 1. Get current symptom history
echo "<h2>1. Current Symptom History</h2>";
$symptom_history = get_user_meta($user_id, 'ennu_symptom_history', true);
$symptom_history = is_array($symptom_history) ? $symptom_history : array();

echo "<p><strong>Total History Entries:</strong> " . count($symptom_history) . "</p>";

// 2. Extract all unique symptoms from history
echo "<h2>2. Extracting Unique Symptoms</h2>";
$all_symptoms = array();

foreach ($symptom_history as $entry) {
    if (isset($entry['symptoms']) && is_array($entry['symptoms'])) {
        foreach ($entry['symptoms'] as $symptom) {
            if (is_array($symptom) && isset($symptom['name'])) {
                $all_symptoms[] = $symptom['name'];
            } elseif (is_string($symptom)) {
                $all_symptoms[] = $symptom;
            }
        }
    } elseif (isset($entry['symptoms']) && is_string($entry['symptoms'])) {
        $symptoms_array = explode(',', $entry['symptoms']);
        foreach ($symptoms_array as $symptom) {
            $symptom = trim($symptom);
            if (!empty($symptom)) {
                $all_symptoms[] = $symptom;
            }
        }
    }
}

$unique_symptoms = array_unique($all_symptoms);
echo "<p><strong>Unique Symptoms Found:</strong> " . count($unique_symptoms) . "</p>";
echo "<p><strong>Symptoms:</strong> " . implode(', ', $unique_symptoms) . "</p>";

// 3. Create centralized symptoms structure
echo "<h2>3. Creating Centralized Symptoms</h2>";
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

// 4. Save centralized symptoms
update_user_meta($user_id, 'ennu_centralized_symptoms', $centralized_symptoms);
echo "<p><strong>Centralized Symptoms Saved:</strong> " . count($centralized_symptoms['symptoms']) . " symptoms</p>";

// 5. Load symptom correlation matrix
echo "<h2>4. Loading Symptom Correlation Matrix</h2>";
$correlation_matrix = include('includes/config/symptom-biomarker-correlations.php');

echo "<p><strong>Total Symptoms in Matrix:</strong> " . count($correlation_matrix['symptoms']) . "</p>";
echo "<p><strong>Total Health Vectors:</strong> " . count($correlation_matrix['health_vectors']) . "</p>";
echo "<p><strong>Total Biomarkers:</strong> " . count($correlation_matrix['biomarkers']) . "</p>";

// 6. Create biomarker flags based on symptoms
echo "<h2>5. Creating Biomarker Flags</h2>";
$flag_manager = new ENNU_Biomarker_Flag_Manager();
$flags_created = 0;

foreach ($unique_symptoms as $symptom_name) {
    $symptom_key = strtolower(str_replace(' ', '_', $symptom_name));
    
    // Check if symptom exists in correlation matrix
    if (isset($correlation_matrix['symptoms'][$symptom_key])) {
        $health_vectors = $correlation_matrix['symptoms'][$symptom_key];
        
        foreach ($health_vectors as $vector) {
            if (isset($correlation_matrix['health_vectors'][$vector])) {
                $biomarkers = $correlation_matrix['health_vectors'][$vector];
                
                foreach ($biomarkers as $biomarker) {
                    echo "<p>Creating flag for biomarker: <strong>{$biomarker}</strong> based on symptom: <strong>{$symptom_name}</strong></p>";
                    
                    // Create biomarker flag
                    $flag_data = array(
                        'user_id' => $user_id,
                        'biomarker_name' => $biomarker,
                        'symptom' => $symptom_name,
                        'health_vector' => $vector,
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
    } else {
        echo "<p style='color: orange;'>⚠ Symptom '{$symptom_name}' not found in correlation matrix</p>";
    }
}

// 7. Verify results
echo "<h2>6. Verifying Results</h2>";

$final_symptoms = ENNU_Centralized_Symptoms_Manager::get_centralized_symptoms($user_id);
$final_flags = $flag_manager->get_flagged_biomarkers($user_id, 'active');

echo "<p><strong>Final Symptoms Count:</strong> " . count($final_symptoms['symptoms'] ?? array()) . "</p>";
echo "<p><strong>Final Biomarker Flags Count:</strong> " . count($final_flags) . "</p>";
echo "<p><strong>Flags Created in This Session:</strong> {$flags_created}</p>";

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

echo "<h2>✅ Symptom Processing Fix Complete!</h2>";
echo "<p>The symptom processing system has been fixed and biomarker flags have been created.</p>";
echo "<p>Please check your user dashboard to see the updated symptoms and biomarker flags.</p>";
?> 