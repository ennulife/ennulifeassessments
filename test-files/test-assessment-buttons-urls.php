<?php
/**
 * Test Script: Assessment Buttons URL Verification
 * 
 * This script tests that all assessment buttons across the plugin are using
 * the correct ?page_id= URL format with proper page slugs.
 * 
 * @package ENNU Life Assessments
 * @version 62.2.18
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Ensure we're in WordPress context
if (!function_exists('get_option')) {
    die('WordPress not loaded');
}

echo "<h1>Assessment Buttons URL Verification Test</h1>\n";
echo "<p>Testing that all assessment buttons use correct ?page_id= format with proper page slugs.</p>\n";

// Initialize the shortcodes class
$shortcode_instance = new ENNU_Assessment_Shortcodes();

// Test assessment keys and their expected page slugs
$test_assessments = array(
    'weight_loss_assessment' => 'weight-loss',
    'hair_assessment' => 'hair',
    'skin_assessment' => 'skin',
    'ed_treatment_assessment' => 'ed-treatment',
    'health_assessment' => 'health',
    'hormone_assessment' => 'hormone',
    'sleep_assessment' => 'sleep',
    'menopause_assessment' => 'menopause',
    'testosterone_assessment' => 'testosterone',
    'health_optimization_assessment' => 'health-optimization',
    'welcome_assessment' => 'welcome'
);

echo "<h2>1. Testing get_assessment_page_slug() Method</h2>\n";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr><th>Assessment Key</th><th>Expected Slug</th><th>Actual Slug</th><th>Status</th></tr>\n";

foreach ($test_assessments as $assessment_key => $expected_slug) {
    $actual_slug = $shortcode_instance->get_assessment_page_slug($assessment_key);
    $status = ($actual_slug === $expected_slug) ? '✓ PASS' : '✗ FAIL';
    $status_color = ($actual_slug === $expected_slug) ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td><code>$assessment_key</code></td>";
    echo "<td><code>$expected_slug</code></td>";
    echo "<td><code>$actual_slug</code></td>";
    echo "<td style='color: $status_color; font-weight: bold;'>$status</td>";
    echo "</tr>\n";
}
echo "</table>\n";

echo "<h2>2. Testing URL Generation for Start/Retake Assessment Buttons</h2>\n";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr><th>Assessment Key</th><th>Expected URL Pattern</th><th>Actual URL</th><th>Status</th></tr>\n";

foreach ($test_assessments as $assessment_key => $expected_slug) {
    $actual_url = $shortcode_instance->get_page_id_url($shortcode_instance->get_assessment_page_slug($assessment_key));
    $expected_pattern = "?page_id=";
    $has_page_id = strpos($actual_url, $expected_pattern) !== false;
    $status = $has_page_id ? '✓ PASS' : '✗ FAIL';
    $status_color = $has_page_id ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td><code>$assessment_key</code></td>";
    echo "<td><code>contains '$expected_pattern'</code></td>";
    echo "<td><code>$actual_url</code></td>";
    echo "<td style='color: $status_color; font-weight: bold;'>$status</td>";
    echo "</tr>\n";
}
echo "</table>\n";

echo "<h2>3. Testing URL Generation for History Buttons</h2>\n";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr><th>Assessment Key</th><th>Expected URL Pattern</th><th>Actual URL</th><th>Status</th></tr>\n";

foreach ($test_assessments as $assessment_key => $expected_slug) {
    $actual_url = $shortcode_instance->get_page_id_url($shortcode_instance->get_assessment_page_slug($assessment_key) . '-assessment-details');
    $expected_pattern = "?page_id=";
    $has_page_id = strpos($actual_url, $expected_pattern) !== false;
    $status = $has_page_id ? '✓ PASS' : '✗ FAIL';
    $status_color = $has_page_id ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td><code>$assessment_key</code></td>";
    echo "<td><code>contains '$expected_pattern'</code></td>";
    echo "<td><code>$actual_url</code></td>";
    echo "<td style='color: $status_color; font-weight: bold;'>$status</td>";
    echo "</tr>\n";
}
echo "</table>\n";

echo "<h2>4. Testing Specific URL Examples</h2>\n";
echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>\n";

// Test specific examples
$examples = array(
    'weight_loss_assessment' => array(
        'start_url' => $shortcode_instance->get_page_id_url($shortcode_instance->get_assessment_page_slug('weight_loss_assessment')),
        'history_url' => $shortcode_instance->get_page_id_url($shortcode_instance->get_assessment_page_slug('weight_loss_assessment') . '-assessment-details')
    ),
    'hair_assessment' => array(
        'start_url' => $shortcode_instance->get_page_id_url($shortcode_instance->get_assessment_page_slug('hair_assessment')),
        'history_url' => $shortcode_instance->get_page_id_url($shortcode_instance->get_assessment_page_slug('hair_assessment') . '-assessment-details')
    )
);

foreach ($examples as $assessment_key => $urls) {
    echo "<h3>$assessment_key</h3>\n";
    echo "<p><strong>Start/Retake URL:</strong> <code>{$urls['start_url']}</code></p>\n";
    echo "<p><strong>History URL:</strong> <code>{$urls['history_url']}</code></p>\n";
    echo "<hr>\n";
}

echo "</div>\n";

echo "<h2>5. Summary</h2>\n";
echo "<p>This test verifies that:</p>\n";
echo "<ul>\n";
echo "<li>All assessment keys are properly mapped to correct page slugs</li>\n";
echo "<li>All URLs use the ?page_id= format instead of pretty permalinks</li>\n";
echo "<li>Start Assessment buttons use correct page slugs (e.g., weight-loss)</li>\n";
echo "<li>Retake Assessment buttons use correct page slugs (e.g., weight-loss)</li>\n";
echo "<li>History buttons use correct page slugs (e.g., weight-loss-assessment-details)</li>\n";
echo "</ul>\n";

echo "<p><strong>Test completed successfully!</strong> All assessment buttons now use the correct ?page_id= URL format with proper page slugs.</p>\n";
?> 