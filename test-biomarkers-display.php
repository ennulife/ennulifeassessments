<?php
// Test script to verify biomarkers display
require_once("../../../wp-config.php");

// Set up WordPress environment
wp_load_alloptions();

echo "<h1>🔬 Biomarkers Display Test</h1>";

// Load the actual ENNU Life Core biomarkers
$core_biomarkers = include( plugin_dir_path( __FILE__ ) . "includes/config/ennu-life-core-biomarkers.php" );

echo "<h2>Biomarker Categories and Counts:</h2>";
$total_biomarkers = 0;

foreach ( $core_biomarkers as $category => $biomarkers ) {
    $count = count( $biomarkers );
    $total_biomarkers += $count;
    echo "<p><strong>$category:</strong> $count biomarkers</p>";
}

echo "<h2>Total Biomarkers: $total_biomarkers</h2>";

echo "<h2>Sample Biomarkers by Category:</h2>";
foreach ( $core_biomarkers as $category => $biomarkers ) {
    echo "<h3>$category:</h3>";
    echo "<ul>";
    foreach ( $biomarkers as $biomarker_key => $biomarker_data ) {
        $biomarker_name = str_replace( "_", " ", $biomarker_key );
        $biomarker_name = ucwords( $biomarker_name );
        echo "<li><strong>$biomarker_name</strong> (" . $biomarker_data["unit"] . ")</li>";
    }
    echo "</ul>";
}

echo "<h2>✅ Test Complete</h2>";
echo "<p>If you see 50 total biomarkers across 12 categories, the display is working correctly!</p>";
?>
