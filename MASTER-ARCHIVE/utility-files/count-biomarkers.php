<?php
/**
 * Precise Biomarker Count Verification
 * 
 * This script counts exactly how many unique biomarkers exist in the system
 * 
 * @package ENNU_Life
 * @version 3.37.15
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Load the biomarker map
$biomarker_map_file = ENNU_LIFE_PLUGIN_PATH . 'includes/config/health-optimization/biomarker-map.php';

if (!file_exists($biomarker_map_file)) {
    die('Biomarker map file not found');
}

$biomarker_map = require $biomarker_map_file;
$all_biomarkers = array();

// Flatten the nested array structure
foreach ($biomarker_map as $vector => $biomarkers) {
    if (is_array($biomarkers)) {
        $all_biomarkers = array_merge($all_biomarkers, $biomarkers);
    }
}

// Remove duplicates and get unique count
$unique_biomarkers = array_unique($all_biomarkers);
$total_count = count($unique_biomarkers);

echo "<h1>ENNU Life - Precise Biomarker Count Verification</h1>\n";
echo "<h2>Total Unique Biomarkers: <strong>{$total_count}</strong></h2>\n";

echo "<h3>Breakdown by Health Vector:</h3>\n";
foreach ($biomarker_map as $vector => $biomarkers) {
    $count = count($biomarkers);
    echo "<p><strong>{$vector}:</strong> {$count} biomarkers</p>\n";
}

echo "<h3>All Unique Biomarkers (Alphabetical):</h3>\n";
sort($unique_biomarkers);
echo "<ol>\n";
foreach ($unique_biomarkers as $biomarker) {
    echo "<li>{$biomarker}</li>\n";
}
echo "</ol>\n";

echo "<h3>Verification Summary:</h3>\n";
echo "<ul>\n";
echo "<li><strong>Total biomarkers listed:</strong> " . count($all_biomarkers) . "</li>\n";
echo "<li><strong>Unique biomarkers:</strong> {$total_count}</li>\n";
echo "<li><strong>Duplicate biomarkers:</strong> " . (count($all_biomarkers) - $total_count) . "</li>\n";
echo "</ul>\n";

// Check which biomarkers have ranges defined by testing each one
$range_manager = new ENNU_Recommended_Range_Manager();
$ranges_defined = array();
$missing_ranges = array();

foreach ($unique_biomarkers as $biomarker) {
    $result = $range_manager->get_recommended_range($biomarker, array());
    if (!isset($result['error'])) {
        $ranges_defined[] = $biomarker;
    } else {
        $missing_ranges[] = $biomarker;
    }
}

echo "<h3>Range Definition Status:</h3>\n";
echo "<ul>\n";
echo "<li><strong>Biomarkers with ranges:</strong> " . count($ranges_defined) . "</li>\n";
echo "<li><strong>Biomarkers missing ranges:</strong> " . count($missing_ranges) . "</li>\n";
echo "</ul>\n";

echo "<h3>Missing Range Definitions:</h3>\n";
echo "<ol>\n";
foreach ($missing_ranges as $biomarker) {
    echo "<li>{$biomarker}</li>\n";
}
echo "</ol>\n";

echo "<h3>Biomarkers WITH Range Definitions:</h3>\n";
echo "<ol>\n";
foreach ($ranges_defined as $biomarker) {
    echo "<li>{$biomarker}</li>\n";
}
echo "</ol>\n";
?> 