<?php
// Load WordPress
require_once('/Applications/MAMP/htdocs/wp-load.php');

// Test orchestrator directly
$orchestrator = ENNU_Biomarker_Range_Orchestrator::get_instance();
$ranges = $orchestrator->get_default_ranges_config();

echo "=== APOB TEST ===\n";
if (isset($ranges['apob'])) {
    echo "✅ apob: Found in {$ranges['apob']['panel']} panel\n";
} else {
    echo "❌ apob: NOT FOUND in orchestrator\n";
}

echo "\n=== SEROTONIN TEST ===\n";
if (isset($ranges['serotonin'])) {
    echo "✅ serotonin: Found in {$ranges['serotonin']['panel']} panel\n";
} else {
    echo "❌ serotonin: NOT FOUND in orchestrator\n";
}

echo "\n=== TOTAL BIOMARKERS ===\n";
echo "Total biomarkers: " . (count($ranges) - 1) . "\n";
?> 