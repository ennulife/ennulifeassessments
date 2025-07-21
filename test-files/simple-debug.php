<?php
// Simple debug script to check plugin loading
echo "=== SIMPLE DEBUG START ===\n";

// Check if WordPress is loaded
if (!defined('ABSPATH')) {
    echo "WordPress not loaded, trying to load it...\n";
    require_once '/Applications/MAMP/htdocs/wp-config.php';
}

echo "WordPress loaded: " . (defined('ABSPATH') ? 'YES' : 'NO') . "\n";

// Check if plugin file exists
$plugin_file = __DIR__ . '/ennu-life-plugin.php';
echo "Plugin file exists: " . (file_exists($plugin_file) ? 'YES' : 'NO') . "\n";

if (file_exists($plugin_file)) {
    echo "Loading plugin...\n";
    require_once $plugin_file;
    
    // Check if plugin class exists
    if (class_exists('ENNU_Life_Enhanced_Plugin')) {
        echo "Plugin class loaded: YES\n";
        
        try {
            $plugin = ENNU_Life_Enhanced_Plugin::get_instance();
            echo "Plugin instance created: YES\n";
            
            if (method_exists($plugin, 'get_shortcode_handler')) {
                echo "Shortcode handler method exists: YES\n";
                
                $handler = $plugin->get_shortcode_handler();
                if (method_exists($handler, 'get_all_assessment_definitions')) {
                    echo "Assessment definitions method exists: YES\n";
                    
                    $definitions = $handler->get_all_assessment_definitions();
                    echo "Definitions count: " . count($definitions) . "\n";
                    echo "Definition keys: " . implode(', ', array_keys($definitions)) . "\n";
                } else {
                    echo "Assessment definitions method exists: NO\n";
                }
            } else {
                echo "Shortcode handler method exists: NO\n";
            }
        } catch (Exception $e) {
            echo "Error creating plugin instance: " . $e->getMessage() . "\n";
        }
    } else {
        echo "Plugin class loaded: NO\n";
    }
}

echo "=== SIMPLE DEBUG END ===\n";
?> 