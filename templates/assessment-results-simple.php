<?php
/**
 * Simplified Assessment Results Template
 * Emergency backup for loading issues
 */

// Get current user
$current_user = wp_get_current_user();
if (!$current_user->ID) {
    echo '<div class="error">Please log in to view your results.</div>';
    return;
}

echo '<div class="ennu-simple-results">';
echo '<h1>Assessment Results</h1>';

// Get user meta
$user_meta = get_user_meta($current_user->ID);
$ennu_meta = array_filter($user_meta, function($key) {
    return strpos($key, 'ennu_') === 0;
}, ARRAY_FILTER_USE_KEY);

if (empty($ennu_meta)) {
    echo '<div class="no-data">';
    echo '<h2>No Assessment Data Found</h2>';
    echo '<p>It looks like your assessment is still being processed or there was an issue with submission.</p>';
    
    // Get admin-configured assessment page
    $settings = get_option('ennu_life_settings', array());
    $assessment_page_id = $settings['page_mappings']['assessments'] ?? $settings['page_mappings']['health-assessment'] ?? null;
    $assessment_url = $assessment_page_id ? home_url("/?page_id={$assessment_page_id}") : home_url('/assessment/');
    
    echo '<a href="' . esc_url($assessment_url) . '" class="button">Take Assessment</a>';
    echo '</div>';
    echo '</div>';
    return;
}

echo '<h2>Your Assessment Data</h2>';
echo '<div class="assessment-data">';

// Show basic results
foreach ($ennu_meta as $key => $value) {
    $clean_key = str_replace('ennu_', '', $key);
    $clean_key = str_replace('_', ' ', $clean_key);
    $clean_key = ucwords($clean_key);
    
    $display_value = is_array($value[0]) ? 'Complex Data' : $value[0];
    if (strlen($display_value) > 100) {
        $display_value = substr($display_value, 0, 100) . '...';
    }
    
    echo '<div class="data-item">';
    echo '<strong>' . esc_html($clean_key) . ':</strong> ';
    echo esc_html($display_value);
    echo '</div>';
}

echo '</div>';

// Get admin-configured pages
$settings = get_option('ennu_life_settings', array());
$dashboard_page_id = $settings['page_mappings']['dashboard'] ?? get_option('ennu_dashboard_page_id');
$assessment_page_id = $settings['page_mappings']['assessments'] ?? $settings['page_mappings']['health-assessment'] ?? null;

$dashboard_url = $dashboard_page_id ? home_url("/?page_id={$dashboard_page_id}") : home_url('/dashboard/');
$assessment_url = $assessment_page_id ? home_url("/?page_id={$assessment_page_id}") : home_url('/assessment/');

echo '<div class="actions">';
echo '<a href="' . esc_url($dashboard_url) . '" class="button">View Dashboard</a>';
echo '<a href="' . esc_url($assessment_url) . '" class="button">Take Another Assessment</a>';
echo '</div>';

echo '</div>';

// Simple styling
echo '<style>
.ennu-simple-results {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}
.assessment-data {
    background: #f9f9f9;
    padding: 1rem;
    border-radius: 8px;
    margin: 1rem 0;
}
.data-item {
    margin: 0.5rem 0;
    padding: 0.5rem;
    border-bottom: 1px solid #eee;
}
.button {
    display: inline-block;
    padding: 10px 20px;
    background: #0073aa;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    margin: 0 10px 10px 0;
}
.actions {
    margin-top: 2rem;
    text-align: center;
}
.no-data {
    text-align: center;
    padding: 2rem;
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 8px;
}
</style>';
?>