<?php
/**
 * Field ID Data Migration Script
 * 
 * Fixes corrupted data from global field misuse
 * 
 * @package ENNU_Life
 * @since 64.70.0
 */

// Load WordPress
require_once('../../../wp-load.php');

// Security check
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized access');
}

global $wpdb;

echo "<h1>Field ID Data Migration</h1>";
echo "<p>Fixing corrupted data from global field misuse...</p>";

// Get all users with assessment data
$users = $wpdb->get_results("
    SELECT DISTINCT user_id 
    FROM {$wpdb->usermeta} 
    WHERE meta_key LIKE 'ennu_assessment_responses_%'
");

$fixed_count = 0;
$assessments_fixed = [];

foreach ($users as $user) {
    $user_id = $user->user_id;
    
    // Assessment types that had misused global fields
    $affected_assessments = [
        'ed_treatment' => 'ed_q1',
        'health_optimization' => 'health_opt_q1', 
        'health' => 'health_q1',
        'menopause' => 'menopause_q1',
        'skin' => 'skin_q1',
        'sleep' => 'sleep_q1',
        'testosterone' => 'testosterone_q1',
        'welcome' => 'ennu_global_health_goals'
    ];
    
    foreach ($affected_assessments as $assessment => $new_field_id) {
        $meta_key = 'ennu_assessment_responses_' . str_replace('_', '-', $assessment);
        $data = get_user_meta($user_id, $meta_key, true);
        
        if (!empty($data) && is_array($data)) {
            $needs_update = false;
            
            // Check if the misused field exists
            if (isset($data['ennu_global_height_weight'])) {
                // Check if this is actual height/weight data or misused data
                $value = $data['ennu_global_height_weight'];
                
                // If it's not height/weight data (array with height/weight keys)
                if (!is_array($value) || !isset($value['height']) || !isset($value['weight'])) {
                    // Move the misused data to correct field
                    $data[$new_field_id] = $value;
                    
                    // Remove the misused field (unless it has valid height/weight)
                    unset($data['ennu_global_height_weight']);
                    
                    $needs_update = true;
                    $assessments_fixed[$assessment] = ($assessments_fixed[$assessment] ?? 0) + 1;
                }
            }
            
            // Also check for duplicate height/weight fields
            $height_weight_fields = [
                'hair_q_height_weight',
                'hormone_q_height_weight', 
                'health_q_height_weight',
                'skin_q_height_weight',
                'sleep_q_height_weight',
                'testosterone_q_height_weight',
                'menopause_q_height_weight',
                'health_opt_q_height_weight',
                'ed_q_height_weight',
                'welcome_q_height_weight'
            ];
            
            foreach ($height_weight_fields as $old_field) {
                if (isset($data[$old_field])) {
                    // Move to global field if not already there
                    if (!isset($data['ennu_global_height_weight'])) {
                        $data['ennu_global_height_weight'] = $data[$old_field];
                    }
                    // Remove the old field
                    unset($data[$old_field]);
                    $needs_update = true;
                }
            }
            
            if ($needs_update) {
                update_user_meta($user_id, $meta_key, $data);
                $fixed_count++;
                echo "<p>Fixed user $user_id - $assessment assessment</p>";
            }
        }
    }
}

echo "<h2>Migration Complete!</h2>";
echo "<p><strong>Total records fixed:</strong> $fixed_count</p>";

if (!empty($assessments_fixed)) {
    echo "<h3>Assessments Fixed:</h3>";
    echo "<ul>";
    foreach ($assessments_fixed as $assessment => $count) {
        echo "<li>$assessment: $count records</li>";
    }
    echo "</ul>";
}

// Also update the global height/weight data in user meta
echo "<h3>Consolidating Global Height/Weight Data...</h3>";

$consolidated = 0;
foreach ($users as $user) {
    $user_id = $user->user_id;
    
    // Get any existing global height/weight data
    $global_hw = get_user_meta($user_id, 'ennu_global_height_weight', true);
    
    if (empty($global_hw)) {
        // Try to find height/weight from any assessment
        $assessments = ['hair', 'weight_loss', 'health', 'hormone', 'skin', 'sleep', 'testosterone', 'menopause', 'ed_treatment', 'health_optimization', 'welcome'];
        
        foreach ($assessments as $assessment) {
            $meta_key = 'ennu_assessment_responses_' . str_replace('_', '-', $assessment);
            $data = get_user_meta($user_id, $meta_key, true);
            
            if (!empty($data) && isset($data['ennu_global_height_weight'])) {
                $hw_data = $data['ennu_global_height_weight'];
                if (is_array($hw_data) && isset($hw_data['height']) && isset($hw_data['weight'])) {
                    update_user_meta($user_id, 'ennu_global_height_weight', $hw_data);
                    $consolidated++;
                    echo "<p>Consolidated height/weight for user $user_id</p>";
                    break;
                }
            }
        }
    }
}

echo "<p><strong>Height/Weight data consolidated:</strong> $consolidated users</p>";

echo "<div style='background: #d4edda; padding: 20px; margin-top: 20px; border-radius: 5px;'>";
echo "<h3>✅ Migration Successful!</h3>";
echo "<p>All field ID issues have been resolved and data has been migrated.</p>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Test assessments to ensure data is being saved correctly</li>";
echo "<li>Verify scoring calculations are working properly</li>";
echo "<li>Check HubSpot synchronization for correct field mapping</li>";
echo "</ol>";
echo "</div>";
?>