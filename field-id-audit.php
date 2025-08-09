<?php
/**
 * Field ID Audit Tool for ENNU Life Assessments
 * 
 * Comprehensive audit of all field IDs for consistency
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

// Initialize audit results
$audit_results = [];
$issues_found = [];
$field_id_mapping = [];

/**
 * Audit Assessment Configuration Field IDs
 */
function audit_assessment_configs() {
    global $audit_results, $field_id_mapping;
    
    $assessment_dir = ENNU_LIFE_PLUGIN_PATH . 'includes/config/assessments/';
    $assessments = [
        'hair', 'weight-loss', 'health', 'skin', 'hormone', 'sleep',
        'testosterone', 'menopause', 'ed-treatment', 'health-optimization', 'welcome'
    ];
    
    foreach ($assessments as $assessment) {
        $file = $assessment_dir . $assessment . '.php';
        if (file_exists($file)) {
            $config = include($file);
            if (isset($config['questions'])) {
                foreach ($config['questions'] as $field_id => $question) {
                    // Track field ID patterns
                    $field_id_mapping[$assessment][] = $field_id;
                    
                    // Check for consistency issues
                    if (strpos($field_id, 'ennu_global_') === 0) {
                        // Global field - should be consistent across assessments
                        if (!isset($field_id_mapping['global'][$field_id])) {
                            $field_id_mapping['global'][$field_id] = [];
                        }
                        $field_id_mapping['global'][$field_id][] = $assessment;
                    }
                }
            }
        }
    }
    
    $audit_results['assessment_configs'] = [
        'status' => 'checked',
        'total_assessments' => count($assessments),
        'field_ids_found' => count($field_id_mapping)
    ];
}

/**
 * Check Global Field Consistency
 */
function check_global_field_consistency() {
    global $issues_found, $field_id_mapping;
    
    $expected_globals = [
        'ennu_global_gender' => ['type' => 'radio', 'options' => ['male', 'female']],
        'ennu_global_date_of_birth' => ['type' => 'dob_dropdowns'],
        'ennu_global_height_weight' => ['type' => 'height_weight'],
        'ennu_global_height' => ['type' => 'number'],
        'ennu_global_weight' => ['type' => 'number'],
        'ennu_global_health_goals' => ['type' => 'multiselect']
    ];
    
    foreach ($expected_globals as $field_id => $expected) {
        if (!isset($field_id_mapping['global'][$field_id])) {
            $issues_found[] = [
                'type' => 'missing_global',
                'field_id' => $field_id,
                'message' => "Global field '$field_id' not found in any assessment"
            ];
        }
    }
}

/**
 * Audit HubSpot Field Mapping
 */
function audit_hubspot_mapping() {
    global $audit_results, $issues_found;
    
    $mapper_file = ENNU_LIFE_PLUGIN_PATH . 'includes/class-hubspot-field-mapper.php';
    if (file_exists($mapper_file)) {
        $content = file_get_contents($mapper_file);
        
        // Extract field mappings
        preg_match_all("/['\"]([^'\"]+)['\"]\s*=>\s*['\"]([^'\"]+)['\"]/", $content, $matches);
        
        $hubspot_mappings = [];
        if (!empty($matches[1]) && !empty($matches[2])) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                $hubspot_mappings[$matches[1][$i]] = $matches[2][$i];
            }
        }
        
        $audit_results['hubspot_mapping'] = [
            'status' => 'checked',
            'total_mappings' => count($hubspot_mappings)
        ];
        
        // Check for inconsistent mappings
        foreach ($hubspot_mappings as $wp_field => $hs_field) {
            if (strpos($wp_field, 'ennu') === 0 && $wp_field !== $hs_field) {
                $issues_found[] = [
                    'type' => 'mapping_inconsistency',
                    'wp_field' => $wp_field,
                    'hs_field' => $hs_field,
                    'message' => "Field mapping inconsistency: '$wp_field' maps to '$hs_field'"
                ];
            }
        }
    }
}

/**
 * Check Field ID Naming Conventions
 */
function check_naming_conventions() {
    global $field_id_mapping, $issues_found;
    
    $naming_patterns = [
        'hair' => '/^(hair_q\d+|ennu_global_|hair_)/',
        'weight-loss' => '/^(wl_q\d+|weight_loss_q\d+|ennu_global_)/',
        'health' => '/^(health_q\d+|ennu_global_)/',
        'hormone' => '/^(hormone_q\d+|ennu_global_)/',
        'testosterone' => '/^(testosterone_q\d+|test_q\d+|ennu_global_)/',
        'skin' => '/^(skin_q\d+|ennu_global_)/',
        'sleep' => '/^(sleep_q\d+|ennu_global_)/',
        'menopause' => '/^(menopause_q\d+|meno_q\d+|ennu_global_)/',
        'ed-treatment' => '/^(ed_q\d+|ed_treatment_q\d+|ennu_global_)/',
        'health-optimization' => '/^(optimization_q\d+|health_opt_q\d+|ennu_global_)/',
        'welcome' => '/^(welcome_q\d+|ennu_global_)/'
    ];
    
    foreach ($field_id_mapping as $assessment => $fields) {
        if ($assessment === 'global') continue;
        
        if (isset($naming_patterns[$assessment])) {
            $pattern = $naming_patterns[$assessment];
            foreach ($fields as $field_id) {
                if (!preg_match($pattern, $field_id)) {
                    $issues_found[] = [
                        'type' => 'naming_convention',
                        'assessment' => $assessment,
                        'field_id' => $field_id,
                        'message' => "Field ID '$field_id' doesn't follow naming convention for $assessment"
                    ];
                }
            }
        }
    }
}

/**
 * Check Database Storage Consistency
 */
function check_database_storage() {
    global $wpdb, $audit_results;
    
    // Check user meta keys
    $meta_keys = $wpdb->get_col("
        SELECT DISTINCT meta_key 
        FROM {$wpdb->usermeta} 
        WHERE meta_key LIKE 'ennu_%'
        LIMIT 100
    ");
    
    $audit_results['database_storage'] = [
        'status' => 'checked',
        'unique_meta_keys' => count($meta_keys),
        'sample_keys' => array_slice($meta_keys, 0, 10)
    ];
}

/**
 * Check Form Field Processing
 */
function check_form_processing() {
    global $audit_results, $issues_found;
    
    $form_handler = ENNU_LIFE_PLUGIN_PATH . 'includes/class-form-handler.php';
    if (file_exists($form_handler)) {
        $content = file_get_contents($form_handler);
        
        // Check for field processing patterns
        preg_match_all("/\$_POST\[['\"]([^'\"]+)['\"]\]/", $content, $matches);
        
        if (!empty($matches[1])) {
            $processed_fields = array_unique($matches[1]);
            
            $audit_results['form_processing'] = [
                'status' => 'checked',
                'fields_processed' => count($processed_fields)
            ];
            
            // Check for unhandled fields
            foreach ($processed_fields as $field) {
                if (strpos($field, 'ennu_') === 0 || strpos($field, '_q') !== false) {
                    // This is likely an assessment field
                    // Check if it's properly sanitized
                    if (strpos($content, "sanitize_text_field(\$_POST['$field']") === false &&
                        strpos($content, "sanitize_textarea_field(\$_POST['$field']") === false) {
                        $issues_found[] = [
                            'type' => 'sanitization',
                            'field_id' => $field,
                            'message' => "Field '$field' may not be properly sanitized"
                        ];
                    }
                }
            }
        }
    }
}

/**
 * Generate Field ID Recommendations
 */
function generate_recommendations() {
    global $issues_found;
    
    $recommendations = [];
    
    // Group issues by type
    $issue_types = [];
    foreach ($issues_found as $issue) {
        $issue_types[$issue['type']][] = $issue;
    }
    
    if (isset($issue_types['naming_convention'])) {
        $recommendations[] = [
            'priority' => 'HIGH',
            'title' => 'Standardize Field ID Naming',
            'description' => 'Inconsistent field ID naming patterns detected',
            'action' => 'Update field IDs to follow consistent pattern: {assessment}_q{number}'
        ];
    }
    
    if (isset($issue_types['mapping_inconsistency'])) {
        $recommendations[] = [
            'priority' => 'MEDIUM',
            'title' => 'Fix HubSpot Mapping',
            'description' => 'Some field mappings are inconsistent',
            'action' => 'Review and update HubSpot field mapper class'
        ];
    }
    
    if (isset($issue_types['missing_global'])) {
        $recommendations[] = [
            'priority' => 'HIGH',
            'title' => 'Add Missing Global Fields',
            'description' => 'Expected global fields are missing',
            'action' => 'Ensure all assessments include required global fields'
        ];
    }
    
    if (isset($issue_types['sanitization'])) {
        $recommendations[] = [
            'priority' => 'CRITICAL',
            'title' => 'Fix Input Sanitization',
            'description' => 'Some fields may not be properly sanitized',
            'action' => 'Add proper sanitization for all form fields'
        ];
    }
    
    return $recommendations;
}

// Run all audits
audit_assessment_configs();
check_global_field_consistency();
audit_hubspot_mapping();
check_naming_conventions();
check_database_storage();
check_form_processing();
$recommendations = generate_recommendations();

// Calculate summary
$total_issues = count($issues_found);
$critical_issues = count(array_filter($issues_found, function($i) { 
    return in_array($i['type'], ['sanitization', 'missing_global']); 
}));

?>
<!DOCTYPE html>
<html>
<head>
    <title>ENNU Field ID Audit Report</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .issues {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .issue {
            padding: 10px;
            margin: 10px 0;
            border-left: 4px solid #ef4444;
            background: #fef2f2;
        }
        .issue.warning {
            border-left-color: #f59e0b;
            background: #fffbeb;
        }
        .recommendations {
            background: white;
            border-radius: 8px;
            padding: 20px;
        }
        .recommendation {
            padding: 15px;
            margin: 10px 0;
            border-radius: 6px;
        }
        .recommendation.critical {
            background: #fef2f2;
            border: 1px solid #fecaca;
        }
        .recommendation.high {
            background: #fffbeb;
            border: 1px solid #fed7aa;
        }
        .recommendation.medium {
            background: #f0f9ff;
            border: 1px solid #bfdbfe;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .status-good { color: #22c55e; }
        .status-warning { color: #f59e0b; }
        .status-error { color: #ef4444; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ENNU Life Assessments - Field ID Audit Report</h1>
        <p>Comprehensive analysis of field ID consistency and integrity</p>
    </div>
    
    <div class="summary">
        <div class="summary-card">
            <h3>Total Issues</h3>
            <div style="font-size: 2em; font-weight: bold; color: <?php echo $total_issues > 0 ? '#ef4444' : '#22c55e'; ?>">
                <?php echo $total_issues; ?>
            </div>
        </div>
        <div class="summary-card">
            <h3>Critical Issues</h3>
            <div style="font-size: 2em; font-weight: bold; color: <?php echo $critical_issues > 0 ? '#ef4444' : '#22c55e'; ?>">
                <?php echo $critical_issues; ?>
            </div>
        </div>
        <div class="summary-card">
            <h3>Assessments Checked</h3>
            <div style="font-size: 2em; font-weight: bold; color: #3b82f6">
                <?php echo $audit_results['assessment_configs']['total_assessments']; ?>
            </div>
        </div>
        <div class="summary-card">
            <h3>HubSpot Mappings</h3>
            <div style="font-size: 2em; font-weight: bold; color: #3b82f6">
                <?php echo $audit_results['hubspot_mapping']['total_mappings'] ?? 0; ?>
            </div>
        </div>
    </div>
    
    <?php if (!empty($issues_found)): ?>
    <div class="issues">
        <h2>Issues Found</h2>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Field ID</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($issues_found as $issue): ?>
                <tr>
                    <td><strong><?php echo ucwords(str_replace('_', ' ', $issue['type'])); ?></strong></td>
                    <td><code><?php echo htmlspecialchars($issue['field_id'] ?? 'N/A'); ?></code></td>
                    <td><?php echo htmlspecialchars($issue['message']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($recommendations)): ?>
    <div class="recommendations">
        <h2>Recommendations</h2>
        <?php foreach ($recommendations as $rec): ?>
        <div class="recommendation <?php echo strtolower($rec['priority']); ?>">
            <h3><?php echo $rec['title']; ?> <span style="float: right; font-size: 0.8em; color: #666;"><?php echo $rec['priority']; ?></span></h3>
            <p><?php echo $rec['description']; ?></p>
            <p><strong>Action:</strong> <?php echo $rec['action']; ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <div class="recommendations">
        <h2>Field ID Standards</h2>
        <table>
            <thead>
                <tr>
                    <th>Assessment</th>
                    <th>Expected Pattern</th>
                    <th>Example</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Hair</td><td>hair_q{number}</td><td>hair_q1, hair_q2</td></tr>
                <tr><td>Weight Loss</td><td>wl_q{number}</td><td>wl_q1, wl_q2</td></tr>
                <tr><td>Health</td><td>health_q{number}</td><td>health_q1, health_q2</td></tr>
                <tr><td>Hormone</td><td>hormone_q{number}</td><td>hormone_q1, hormone_q2</td></tr>
                <tr><td>Global Fields</td><td>ennu_global_{name}</td><td>ennu_global_gender, ennu_global_age</td></tr>
            </tbody>
        </table>
    </div>
    
    <?php if ($total_issues === 0): ?>
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin-top: 30px;">
        <h3 style="color: #166534; margin-top: 0;">✅ All Field IDs are Perfect!</h3>
        <p style="color: #14532d;">No issues found with field ID consistency, naming conventions, or mappings.</p>
    </div>
    <?php else: ?>
    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 20px; margin-top: 30px;">
        <h3 style="color: #991b1b; margin-top: 0;">⚠️ Field ID Issues Detected</h3>
        <p style="color: #7f1d1d;">Please review the issues and recommendations above to ensure proper field handling.</p>
    </div>
    <?php endif; ?>
</body>
</html>