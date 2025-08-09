<?php
// Test dashboard loading
define('ABSPATH', true);
define('ENNU_LIFE_PLUGIN_PATH', '/Applications/MAMP/htdocs/wp-content/plugins/ennulifeassessments/');

// Test if files exist
$files = array(
    'Helper Class' => ENNU_LIFE_PLUGIN_PATH . 'includes/dashboard/class-dashboard-helpers.php',
    'Data Service' => ENNU_LIFE_PLUGIN_PATH . 'includes/services/class-dashboard-data-service.php',
    'Dashboard Template' => ENNU_LIFE_PLUGIN_PATH . 'templates/dashboard/dashboard-main.php',
    'Old Template' => ENNU_LIFE_PLUGIN_PATH . 'templates/user-dashboard.php'
);

echo "Dashboard File Check:\n";
echo str_repeat("=", 50) . "\n";

foreach ($files as $name => $path) {
    if (file_exists($path)) {
        $size = filesize($path);
        echo "✅ $name: EXISTS (" . number_format($size) . " bytes)\n";
    } else {
        echo "❌ $name: MISSING\n";
    }
}

// Check template loading function
echo "\nTemplate Loading Test:\n";
echo str_repeat("=", 50) . "\n";

$template_name = 'dashboard/dashboard-main';
$template_name = ltrim($template_name, '/');
if (!str_ends_with($template_name, '.php')) {
    $template_name .= '.php';
}
$template_path = ENNU_LIFE_PLUGIN_PATH . 'templates/' . $template_name;

echo "Input: 'dashboard/dashboard-main'\n";
echo "Processed: '$template_name'\n";
echo "Full Path: '$template_path'\n";
echo "Exists: " . (file_exists($template_path) ? "YES ✅" : "NO ❌") . "\n";