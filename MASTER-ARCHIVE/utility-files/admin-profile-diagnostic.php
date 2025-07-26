<?php
/**
 * ENNU Life Admin Profile Diagnostic Tool
 * 
 * This tool checks what custom user meta fields are stored for the current admin user
 * and compares them to what should be there based on our user dashboard development.
 * 
 * Access this file directly via: /wp-content/plugins/ennulifeassessments/admin-profile-diagnostic.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Only allow admins to access this
if (!current_user_can('manage_options')) {
    wp_die('Access denied - Admin privileges required');
}

// Get current user ID
$user_id = get_current_user_id();

// Define expected ENNU Life fields based on our development
$expected_ennu_fields = array(
    // Health Profile Data
    'ennu_age' => 'User Age',
    'ennu_gender' => 'Gender',
    'ennu_height' => 'Height',
    'ennu_weight' => 'Weight',
    'ennu_bmi' => 'BMI',
    'ennu_dob' => 'Date of Birth',
    
    // Assessment Data
    'ennu_user_assessments' => 'User Assessments',
    'ennu_health_optimization_symptoms' => 'Health Optimization Symptoms',
    'ennu_life_score' => 'ENNU Life Score',
    'ennu_pillar_scores' => 'Pillar Scores',
    
    // Biomarker Data
    'ennu_biomarker_flags' => 'Biomarker Flags',
    'ennu_lab_results' => 'Lab Results',
    'ennu_recommended_biomarkers' => 'Recommended Biomarkers',
    
    // Additional fields that might exist
    'ennu_completed_assessments' => 'Completed Assessments',
    'ennu_assessment_history' => 'Assessment History',
    'ennu_health_metrics' => 'Health Metrics',
    'ennu_biomarker_data' => 'Biomarker Data',
    'ennu_symptom_data' => 'Symptom Data',
    'ennu_global_fields' => 'Global Fields'
);

// Get all user meta for current user
$all_user_meta = get_user_meta($user_id);

// Filter for ENNU Life fields
$ennu_fields_found = array();
$other_fields = array();

foreach ($all_user_meta as $meta_key => $meta_values) {
    if (strpos($meta_key, 'ennu_') === 0) {
        $ennu_fields_found[$meta_key] = $meta_values[0];
    } else {
        $other_fields[$meta_key] = $meta_values[0];
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>ENNU Life Admin Profile Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
        h2 { color: #0073aa; margin-top: 30px; }
        .field-group { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .field-item { margin: 10px 0; padding: 10px; background: #f9f9f9; border-left: 4px solid #0073aa; }
        .field-name { font-weight: bold; color: #333; }
        .field-value { color: #666; margin-top: 5px; font-family: monospace; }
        .missing { border-left-color: #dc3232; background: #fff5f5; }
        .found { border-left-color: #46b450; background: #f0f8f0; }
        .summary { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .count { font-size: 1.2em; font-weight: bold; color: #0073aa; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .back-link { margin: 20px 0; }
        .back-link a { background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-link">
            <a href="<?php echo admin_url('profile.php'); ?>">← Back to WordPress Admin Profile</a>
        </div>
        
        <h1>🔍 ENNU Life Admin Profile Diagnostic</h1>
        
        <div class="summary">
            <h3>📊 Summary</h3>
            <p><strong>User ID:</strong> <?php echo $user_id; ?></p>
            <p><strong>Username:</strong> <?php echo get_userdata($user_id)->user_login; ?></p>
            <p><strong>Total User Meta Fields:</strong> <span class="count"><?php echo count($all_user_meta); ?></span></p>
            <p><strong>ENNU Life Fields Found:</strong> <span class="count"><?php echo count($ennu_fields_found); ?></span></p>
            <p><strong>Expected ENNU Life Fields:</strong> <span class="count"><?php echo count($expected_ennu_fields); ?></span></p>
        </div>

        <h2>✅ ENNU Life Fields Found</h2>
        <?php if (empty($ennu_fields_found)): ?>
            <div class="field-group">
                <p style="color: #dc3232; font-weight: bold;">❌ No ENNU Life fields found in user meta!</p>
                <p>This means the user dashboard data is not being stored in the database.</p>
            </div>
        <?php else: ?>
            <?php foreach ($ennu_fields_found as $field_name => $field_value): ?>
                <div class="field-item found">
                    <div class="field-name"><?php echo esc_html($field_name); ?></div>
                    <div class="field-value">
                        <?php 
                        if (is_serialized($field_value)) {
                            $unserialized = unserialize($field_value);
                            echo '<pre>' . esc_html(print_r($unserialized, true)) . '</pre>';
                        } else {
                            echo esc_html($field_value);
                        }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <h2>❌ Expected ENNU Life Fields (Missing)</h2>
        <?php 
        $missing_fields = array_diff_key($expected_ennu_fields, $ennu_fields_found);
        if (empty($missing_fields)): ?>
            <div class="field-group">
                <p style="color: #46b450; font-weight: bold;">✅ All expected ENNU Life fields are present!</p>
            </div>
        <?php else: ?>
            <?php foreach ($missing_fields as $field_name => $field_description): ?>
                <div class="field-item missing">
                    <div class="field-name"><?php echo esc_html($field_name); ?></div>
                    <div class="field-value"><?php echo esc_html($field_description); ?> - NOT FOUND</div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <h2>🔍 All User Meta Fields (Non-ENNU)</h2>
        <div class="field-group">
            <p><strong>Total Other Fields:</strong> <?php echo count($other_fields); ?></p>
            <?php if (!empty($other_fields)): ?>
                <?php foreach ($other_fields as $field_name => $field_value): ?>
                    <div class="field-item">
                        <div class="field-name"><?php echo esc_html($field_name); ?></div>
                        <div class="field-value">
                            <?php 
                            if (is_serialized($field_value)) {
                                $unserialized = unserialize($field_value);
                                echo '<pre>' . esc_html(print_r($unserialized, true)) . '</pre>';
                            } else {
                                echo esc_html($field_value);
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No other user meta fields found.</p>
            <?php endif; ?>
        </div>

        <h2>💡 Recommendations</h2>
        <div class="field-group">
            <?php if (empty($ennu_fields_found)): ?>
                <h3>🚨 Critical Issue: No ENNU Life Data Found</h3>
                <ul>
                    <li><strong>Problem:</strong> The user dashboard is not saving data to the database</li>
                    <li><strong>Impact:</strong> Users will lose their assessment data and health information</li>
                    <li><strong>Solution:</strong> Check the assessment completion and data saving functions</li>
                </ul>
            <?php elseif (count($missing_fields) > 0): ?>
                <h3>⚠️ Partial Data Issue</h3>
                <ul>
                    <li><strong>Problem:</strong> Some expected fields are missing</li>
                    <li><strong>Impact:</strong> Dashboard may not display all information correctly</li>
                    <li><strong>Solution:</strong> Complete assessments to generate missing data</li>
                </ul>
            <?php else: ?>
                <h3>✅ Data Looks Good</h3>
                <ul>
                    <li><strong>Status:</strong> All expected ENNU Life fields are present</li>
                    <li><strong>Next Step:</strong> Verify the frontend dashboard is displaying this data correctly</li>
                </ul>
            <?php endif; ?>
        </div>

        <h2>🔧 Debug Information</h2>
        <div class="field-group">
            <h3>Debug Log Analysis</h3>
            <p>Based on the debug logs, I can see these values being processed:</p>
            <ul>
                <li><strong>Age:</strong> 35</li>
                <li><strong>Gender:</strong> male</li>
                <li><strong>Height:</strong> 6' 0"</li>
                <li><strong>Weight:</strong> 180 lbs</li>
                <li><strong>BMI:</strong> 24.8</li>
                <li><strong>DOB:</strong> 1990-01-15</li>
                <li><strong>ENNU Life Score:</strong> 7.5</li>
                <li><strong>Pillar Scores:</strong> All 7.5 (Mind, Body, Lifestyle, Aesthetics)</li>
            </ul>
            <p><strong>Question:</strong> Are these values being saved to user meta, or are they only calculated on-the-fly?</p>
        </div>
    </div>
</body>
</html> 