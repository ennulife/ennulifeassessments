<?php
// Load WordPress
require_once('/Applications/MAMP/htdocs/wp-load.php');

// Test hardcoded ranges directly
$orchestrator = ENNU_Biomarker_Range_Orchestrator::get_instance();

// Use reflection to access the private method
$reflection = new ReflectionClass($orchestrator);
$method = $reflection->getMethod('get_hardcoded_default_ranges');
$method->setAccessible(true);

$hardcoded_ranges = $method->invoke($orchestrator);

echo "=== HARDCODED RANGES TEST ===\n";
echo "Total biomarkers in hardcoded ranges: " . (count($hardcoded_ranges) - 1) . "\n\n";

// Check for specific new biomarkers
$new_biomarkers = ['apob', 'serotonin', 'ggt', 'albumin'];

echo "=== NEW BIOMARKERS CHECK ===\n";
$found_count = 0;
foreach ($new_biomarkers as $biomarker) {
    if (isset($hardcoded_ranges[$biomarker])) {
        $found_count++;
        echo "✅ {$biomarker}: Found in {$hardcoded_ranges[$biomarker]['panel']} panel\n";
    } else {
        echo "❌ {$biomarker}: NOT FOUND in hardcoded ranges\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "Found {$found_count} out of " . count($new_biomarkers) . " new biomarkers in hardcoded ranges\n";
echo "Total biomarkers: " . (count($hardcoded_ranges) - 1) . "\n";

// List all biomarkers in hardcoded ranges
echo "\n=== ALL BIOMARKERS IN HARDCODED RANGES ===\n";
$biomarker_count = 0;
foreach ($hardcoded_ranges as $key => $value) {
    if ($key !== 'version_info') {
        $biomarker_count++;
        echo "{$biomarker_count}. {$key} ({$value['panel']} panel)\n";
    }
}
?> 