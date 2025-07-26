<?php
/**
 * Test script for CSV Biomarker Importer
 */

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';

echo "=== ENNU Life CSV Importer Test ===\n";
echo "Plugin Version: " . ENNU_LIFE_VERSION . "\n\n";

// Check if the CSV importer class exists
if (class_exists('ENNU_CSV_Biomarker_Importer')) {
    echo "✓ ENNU_CSV_Biomarker_Importer class found\n";
    
    // Test available biomarkers
    $importer = new ENNU_CSV_Biomarker_Importer();
    $reflection = new ReflectionClass($importer);
    $method = $reflection->getMethod('get_available_biomarkers');
    $method->setAccessible(true);
    $biomarkers = $method->invoke($importer);
    
    echo "✓ Available biomarkers: " . count($biomarkers) . "\n";
    echo "Sample biomarkers:\n";
    $count = 0;
    foreach ($biomarkers as $key => $info) {
        if ($count < 5) {
            echo "  - $key: {$info['name']} ({$info['unit']})\n";
            $count++;
        }
    }
    
    // Test date validation
    $method = $reflection->getMethod('is_valid_date');
    $method->setAccessible(true);
    
    $valid_date = $method->invoke($importer, '2024-01-15');
    $invalid_date = $method->invoke($importer, 'invalid-date');
    
    echo "\n✓ Date validation test:\n";
    echo "  - '2024-01-15': " . ($valid_date ? 'Valid' : 'Invalid') . "\n";
    echo "  - 'invalid-date': " . ($invalid_date ? 'Valid' : 'Invalid') . "\n";
    
} else {
    echo "✗ ENNU_CSV_Biomarker_Importer class not found\n";
}

// Check if admin menu is registered
echo "\n=== Admin Menu Test ===\n";
$admin_menu_hooks = array();
global $submenu;

if (isset($submenu['ennu-life'])) {
    foreach ($submenu['ennu-life'] as $item) {
        if (strpos($item[2], 'ennu-csv-import') !== false) {
            $admin_menu_hooks[] = $item;
        }
    }
}

if (!empty($admin_menu_hooks)) {
    echo "✓ CSV Import menu found in admin:\n";
    foreach ($admin_menu_hooks as $item) {
        echo "  - {$item[0]} (slug: {$item[2]})\n";
    }
} else {
    echo "✗ CSV Import menu not found in admin\n";
}

// Check AJAX action
echo "\n=== AJAX Action Test ===\n";
global $wp_filter;

if (isset($wp_filter['wp_ajax_ennu_csv_import_biomarkers'])) {
    echo "✓ AJAX action 'ennu_csv_import_biomarkers' is registered\n";
} else {
    echo "✗ AJAX action 'ennu_csv_import_biomarkers' not found\n";
}

// Test sample CSV file
echo "\n=== Sample CSV File Test ===\n";
$sample_file = __DIR__ . '/sample-biomarkers.csv';
if (file_exists($sample_file)) {
    echo "✓ Sample CSV file exists: " . basename($sample_file) . "\n";
    
    // Read first few lines
    $handle = fopen($sample_file, 'r');
    if ($handle) {
        echo "Sample CSV content (first 5 lines):\n";
        $line_count = 0;
        while (($line = fgets($handle)) !== false && $line_count < 5) {
            echo "  " . trim($line) . "\n";
            $line_count++;
        }
        fclose($handle);
    }
} else {
    echo "✗ Sample CSV file not found\n";
}

echo "\n=== Test Complete ===\n"; 