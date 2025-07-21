<?php
/**
 * Debug Script: URL Format Check
 * 
 * This script checks what URLs are being generated for assessment buttons
 * and identifies why they might be using pretty permalinks instead of ?page_id= format.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Ensure we're in WordPress context
if (!function_exists('get_option')) {
    die('WordPress not loaded');
}

echo "<h1>URL Format Debug</h1>\n";

// Initialize the shortcodes class
$shortcode_instance = new ENNU_Assessment_Shortcodes();

// Test specific assessment
$assessment_key = 'weight_loss_assessment';
$page_slug = $shortcode_instance->get_assessment_page_slug($assessment_key);

echo "<h2>Testing: $assessment_key</h2>\n";
echo "<p><strong>Assessment Key:</strong> $assessment_key</p>\n";
echo "<p><strong>Page Slug:</strong> $page_slug</p>\n";

// Check if page exists in created_pages option
$created_pages = get_option('ennu_created_pages', array());
echo "<p><strong>Created Pages Option:</strong></p>\n";
echo "<pre>" . print_r($created_pages, true) . "</pre>\n";

// Check if page exists by slug
$page = get_page_by_path($page_slug);
echo "<p><strong>Page Found by Slug:</strong> " . ($page ? "Yes (ID: {$page->ID})" : "No") . "</p>\n";

// Generate URL
$url = $shortcode_instance->get_page_id_url($page_slug);
echo "<p><strong>Generated URL:</strong> $url</p>\n";

// Check if URL contains ?page_id=
$has_page_id = strpos($url, '?page_id=') !== false;
echo "<p><strong>Contains ?page_id=:</strong> " . ($has_page_id ? "YES" : "NO") . "</p>\n";

// Test History URL
$history_url = $shortcode_instance->get_page_id_url($page_slug . '-assessment-details');
echo "<p><strong>History URL:</strong> $history_url</p>\n";

$history_has_page_id = strpos($history_url, '?page_id=') !== false;
echo "<p><strong>History Contains ?page_id=:</strong> " . ($history_has_page_id ? "YES" : "NO") . "</p>\n";

// Check all pages in WordPress
echo "<h2>All WordPress Pages</h2>\n";
$pages = get_pages();
echo "<table border='1' style='border-collapse: collapse;'>\n";
echo "<tr><th>ID</th><th>Title</th><th>Slug</th><th>Status</th></tr>\n";
foreach ($pages as $page) {
    echo "<tr>";
    echo "<td>{$page->ID}</td>";
    echo "<td>{$page->post_title}</td>";
    echo "<td>{$page->post_name}</td>";
    echo "<td>{$page->post_status}</td>";
    echo "</tr>\n";
}
echo "</table>\n";

echo "<h2>Recommendation</h2>\n";
if (!$has_page_id) {
    echo "<p style='color: red;'><strong>ISSUE FOUND:</strong> The URL is not using ?page_id= format!</p>\n";
    echo "<p>This means either:</p>\n";
    echo "<ul>\n";
    echo "<li>The page doesn't exist in WordPress</li>\n";
    echo "<li>The page slug doesn't match exactly</li>\n";
    echo "<li>The page is not published</li>\n";
    echo "</ul>\n";
} else {
    echo "<p style='color: green;'><strong>SUCCESS:</strong> The URL is using ?page_id= format!</p>\n";
}
?> 