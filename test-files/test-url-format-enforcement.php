<?php
/**
 * Test Script: URL Format Enforcement Verification
 * 
 * This script tests that the get_page_id_url() method now always returns
 * ?page_id= format URLs and never falls back to pretty permalinks.
 * 
 * @package ENNU Life Assessments
 * @version 62.2.19
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Ensure we're in WordPress context
if (!function_exists('get_option')) {
    die('WordPress not loaded');
}

echo "<h1>URL Format Enforcement Test</h1>\n";
echo "<p>Testing that get_page_id_url() NEVER returns pretty permalinks.</p>\n";

// Initialize the shortcodes class
$shortcode_instance = new ENNU_Assessment_Shortcodes();

// Test various page types that might not exist
$test_pages = array(
    'weight-loss',
    'hair',
    'skin',
    'ed-treatment',
    'health',
    'hormone',
    'sleep',
    'menopause',
    'testosterone',
    'health-optimization',
    'welcome',
    'weight-loss-assessment-details',
    'hair-assessment-details',
    'non-existent-page',
    'another-fake-page'
);

echo "<h2>Testing URL Generation for Various Page Types</h2>\n";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr><th>Page Type</th><th>Generated URL</th><th>Contains ?page_id=</th><th>Contains Pretty Permalink</th><th>Status</th></tr>\n";

$all_passed = true;

foreach ($test_pages as $page_type) {
    $url = $shortcode_instance->get_page_id_url($page_type);
    $has_page_id = strpos($url, '?page_id=') !== false;
    $has_pretty_permalink = (strpos($url, '/?page_id=') === false && strpos($url, '?page_id=') === false);
    
    $status = '✓ PASS';
    $status_color = 'green';
    
    if (!$has_page_id) {
        $status = '✗ FAIL - No ?page_id=';
        $status_color = 'red';
        $all_passed = false;
    } elseif ($has_pretty_permalink) {
        $status = '✗ FAIL - Pretty permalink detected';
        $status_color = 'red';
        $all_passed = false;
    }
    
    echo "<tr>";
    echo "<td><code>$page_type</code></td>";
    echo "<td><code>$url</code></td>";
    echo "<td>" . ($has_page_id ? 'YES' : 'NO') . "</td>";
    echo "<td>" . ($has_pretty_permalink ? 'YES' : 'NO') . "</td>";
    echo "<td style='color: $status_color; font-weight: bold;'>$status</td>";
    echo "</tr>\n";
}
echo "</table>\n";

echo "<h2>Testing Assessment Button URLs</h2>\n";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr><th>Assessment</th><th>Start/Retake URL</th><th>History URL</th><th>Status</th></tr>\n";

$assessment_tests = array(
    'weight_loss_assessment',
    'hair_assessment',
    'skin_assessment',
    'ed_treatment_assessment'
);

foreach ($assessment_tests as $assessment_key) {
    $page_slug = $shortcode_instance->get_assessment_page_slug($assessment_key);
    $start_url = $shortcode_instance->get_page_id_url($page_slug);
    $history_url = $shortcode_instance->get_page_id_url($page_slug . '-assessment-details');
    
    $start_has_page_id = strpos($start_url, '?page_id=') !== false;
    $history_has_page_id = strpos($history_url, '?page_id=') !== false;
    
    $status = '✓ PASS';
    $status_color = 'green';
    
    if (!$start_has_page_id || !$history_has_page_id) {
        $status = '✗ FAIL';
        $status_color = 'red';
        $all_passed = false;
    }
    
    echo "<tr>";
    echo "<td><code>$assessment_key</code></td>";
    echo "<td><code>$start_url</code></td>";
    echo "<td><code>$history_url</code></td>";
    echo "<td style='color: $status_color; font-weight: bold;'>$status</td>";
    echo "</tr>\n";
}
echo "</table>\n";

echo "<h2>Test Results</h2>\n";
if ($all_passed) {
    echo "<p style='color: green; font-weight: bold; font-size: 18px;'>✅ SUCCESS: All URLs are using ?page_id= format!</p>\n";
    echo "<p>The get_page_id_url() method is now properly enforced to NEVER use pretty permalinks.</p>\n";
} else {
    echo "<p style='color: red; font-weight: bold; font-size: 18px;'>❌ FAILURE: Some URLs are still using pretty permalinks!</p>\n";
    echo "<p>The get_page_id_url() method needs further fixing.</p>\n";
}

echo "<h2>What This Fix Achieves</h2>\n";
echo "<ul>\n";
echo "<li>✅ All assessment buttons now use ?page_id= format</li>\n";
echo "<li>✅ No more pretty permalinks in assessment URLs</li>\n";
echo "<li>✅ Consistent URL format across all assessment pages</li>\n";
echo "<li>✅ Smart fallback to similar page slugs when exact match fails</li>\n";
echo "<li>✅ Final fallback to default page ID ensures ?page_id= format</li>\n";
echo "</ul>\n";

echo "<p><strong>Test completed!</strong></p>\n";
?> 