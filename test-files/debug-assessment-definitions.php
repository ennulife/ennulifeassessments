<?php
// Debug script to check assessment definitions
require_once 'ennu-life-plugin.php';

$plugin = ENNU_Life_Enhanced_Plugin::get_instance();
$definitions = $plugin->get_shortcode_handler()->get_all_assessment_definitions();

echo "=== ASSESSMENT DEFINITIONS DEBUG ===\n";
echo "Total definitions found: " . count($definitions) . "\n";
echo "Assessment keys: " . implode(', ', array_keys($definitions)) . "\n\n";

foreach ($definitions as $key => $definition) {
    echo "Key: $key\n";
    echo "Title: " . ($definition['title'] ?? 'No title') . "\n";
    echo "---\n";
}

// Check what pages should be created
$assessment_keys = array_keys($definitions);
$assessment_menu_order = array(
    'hair' => 'hair_assessment',
    'ed-treatment' => 'ed_treatment_assessment',
    'weight-loss' => 'weight_loss_assessment',
    'health' => 'health_assessment',
    'health-optimization' => 'health_optimization_assessment',
    'skin' => 'skin_assessment',
    'hormone' => 'hormone_assessment',
    'testosterone' => 'testosterone_assessment',
    'menopause' => 'menopause_assessment',
    'sleep' => 'sleep_assessment'
);

echo "\n=== FILTERED ASSESSMENTS ===\n";
$filtered_assessments = array();
foreach ($assessment_menu_order as $slug => $key) {
    if (in_array($key, $assessment_keys)) {
        $filtered_assessments[$slug] = $key;
        echo "✓ $slug => $key\n";
    } else {
        echo "✗ $slug => $key (NOT FOUND)\n";
    }
}

echo "\nFiltered assessments count: " . count($filtered_assessments) . "\n";
echo "Filtered assessment slugs: " . implode(', ', array_keys($filtered_assessments)) . "\n";

// Show what pages would be created
echo "\n=== PAGES THAT WOULD BE CREATED ===\n";
foreach ($filtered_assessments as $slug => $key) {
    echo "Form: assessments/$slug\n";
    echo "Results: assessments/$slug/results\n";
    echo "Details: assessments/$slug/details\n";
    echo "Consultation: assessments/$slug/consultation\n";
    echo "---\n";
}
?> 