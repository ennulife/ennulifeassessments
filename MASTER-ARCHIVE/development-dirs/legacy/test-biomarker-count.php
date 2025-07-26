<?php
// Load WordPress
require_once('/Applications/MAMP/htdocs/wp-load.php');

// Test orchestrator directly
$orchestrator = ENNU_Biomarker_Range_Orchestrator::get_instance();
$ranges = $orchestrator->get_default_ranges_config();

echo "=== BIOMARKER COUNT TEST ===\n";
echo "Total biomarkers in orchestrator: " . (count($ranges) - 1) . "\n\n";

// Check for specific new biomarkers
$new_biomarkers = ['apob', 'serotonin', 'ggt', 'albumin', 'bilirubin', 'alkaline_phosphatase', 'coq10', 'alpha_lipoic_acid', 'hs_crp', 'il_6', 'tnf_alpha', 'fibrinogen', 'lp_pla2', 'myeloperoxidase', 'oxidized_ldl'];

echo "=== NEW BIOMARKERS CHECK ===\n";
$found_count = 0;
foreach ($new_biomarkers as $biomarker) {
    if (isset($ranges[$biomarker])) {
        $found_count++;
        echo "✅ {$biomarker}: Found in {$ranges[$biomarker]['panel']} panel\n";
    } else {
        echo "❌ {$biomarker}: NOT FOUND in orchestrator\n";
    }
}

echo "\n=== PANEL COUNTS FROM ORCHESTRATOR ===\n";
$panel_counts = $orchestrator->get_panel_biomarker_counts();
foreach ($panel_counts as $panel => $count) {
    echo ucfirst($panel) . ": {$count} biomarkers\n";
}

echo "\n=== SUMMARY ===\n";
echo "Found {$found_count} out of " . count($new_biomarkers) . " new biomarkers\n";
echo "Total biomarkers: " . (count($ranges) - 1) . "\n";
?> 