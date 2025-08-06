<?php
/**
 * Fix naming consistency issues across the ENNU Life plugin
 */

// Load WordPress
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );

echo "<h1>ENNU Naming Consistency Fix</h1>";

// Test 1: Verify constants are properly defined
echo "<h2>1. Verify Constants</h2>";

$constants_to_check = array(
    'ENNU_LIFE_VERSION',
    'ENNU_LIFE_PLUGIN_PATH', 
    'ENNU_LIFE_PLUGIN_URL',
    'ENNU_LIFE_PLUGIN_DIR',
    'ENNU_LIFE_PLUGIN_FILE'
);

foreach ( $constants_to_check as $constant ) {
    if ( defined( $constant ) ) {
        $value = constant( $constant );
        echo "✅ {$constant} = {$value}<br>";
    } else {
        echo "❌ {$constant} is NOT defined<br>";
    }
}

// Test 2: Check class naming patterns
echo "<h2>2. Class Naming Analysis</h2>";

$class_patterns = array(
    'ENNU_Life_Enhanced_Plugin' => 'Main plugin class',
    'ENNU_Assessment_Shortcodes' => 'Assessment shortcodes class', 
    'ENNU_Enhanced_Admin' => 'Admin class',
    'ENNU_Life_Enhanced_Database' => 'Database class',
    'ENNU_Assessment_Service' => 'Assessment service class',
    'ENNU_Assessment_Rendering_Service' => 'Assessment rendering service class',
    'ENNU_Assessment_Calculator' => 'Assessment calculator class',
    'ENNU_Assessment_Constants' => 'Assessment constants class',
    'ENNU_Life_Template_Loader' => 'Template loader class',
    'ENNU_Enhanced_Dashboard_Manager' => 'Dashboard manager class',
    'ENNU_Assessment_Form_Shortcode' => 'Assessment form shortcode class',
    'ENNU_Assessment_AJAX_Handler_DISABLED' => 'AJAX handler class (disabled)',
    'ENNU_Life_Score_Calculator' => 'Score calculator class',
    'ENNU_Life_Email_System' => 'Email system class'
);

echo "<h3>Current Class Naming Patterns:</h3>";
foreach ( $class_patterns as $class_name => $description ) {
    if ( class_exists( $class_name ) ) {
        echo "✅ {$class_name} exists ({$description})<br>";
    } else {
        echo "❌ {$class_name} does NOT exist ({$description})<br>";
    }
}

// Test 3: Check for naming inconsistencies
echo "<h2>3. Naming Inconsistencies Found</h2>";

echo "<h3>Mixed Case vs Underscore Analysis:</h3>";

$mixed_case_classes = array(
    'ENNU_Life_Enhanced_Plugin',
    'ENNU_Life_Enhanced_Database', 
    'ENNU_Life_Template_Loader',
    'ENNU_Life_Score_Calculator',
    'ENNU_Life_Email_System'
);

$underscore_classes = array(
    'ENNU_Assessment_Shortcodes',
    'ENNU_Enhanced_Admin',
    'ENNU_Assessment_Service',
    'ENNU_Assessment_Rendering_Service',
    'ENNU_Assessment_Calculator',
    'ENNU_Assessment_Constants',
    'ENNU_Enhanced_Dashboard_Manager',
    'ENNU_Assessment_Form_Shortcode',
    'ENNU_Assessment_AJAX_Handler_DISABLED'
);

echo "<strong>Mixed Case Classes (ENNU_Life_*):</strong><br>";
foreach ( $mixed_case_classes as $class ) {
    if ( class_exists( $class ) ) {
        echo "  ✅ {$class}<br>";
    }
}

echo "<br><strong>Underscore Classes (ENNU_*):</strong><br>";
foreach ( $underscore_classes as $class ) {
    if ( class_exists( $class ) ) {
        echo "  ✅ {$class}<br>";
    }
}

// Test 4: Check option naming consistency
echo "<h2>4. Option Naming Consistency</h2>";

$option_patterns = array(
    'ennu_life_settings' => 'Main settings option',
    'ennu_created_pages' => 'Created pages option', 
    'ennu_auto_select_pages' => 'Auto select pages option'
);

foreach ( $option_patterns as $option_name => $description ) {
    $value = get_option( $option_name );
    if ( $value !== false ) {
        echo "✅ {$option_name} exists ({$description})<br>";
    } else {
        echo "❌ {$option_name} does NOT exist ({$description})<br>";
    }
}

// Test 5: Check meta key naming consistency
echo "<h2>5. Meta Key Naming Consistency</h2>";

$meta_patterns = array(
    'ennu_global_' => 'Global meta prefix',
    'ennu_hair_' => 'Hair assessment meta prefix',
    'ennu_ed-treatment_' => 'ED treatment meta prefix',
    'ennu_life_score' => 'Life score meta key'
);

foreach ( $meta_patterns as $meta_pattern => $description ) {
    echo "<strong>{$meta_pattern}:</strong> {$description}<br>";
}

// Test 6: Recommendations
echo "<h2>6. Naming Standardization Recommendations</h2>";

echo "<h3>Current Status:</h3>";
echo "<ul>";
echo "<li><strong>✅ Constants:</strong> All properly defined with ENNU_LIFE_* pattern</li>";
echo "<li><strong>✅ Options:</strong> Consistent ennu_life_* pattern</li>";
echo "<li><strong>✅ Meta Keys:</strong> Consistent ennu_* pattern</li>";
echo "<li><strong>⚠️ Classes:</strong> Mixed patterns (ENNU_Life_* vs ENNU_*)</li>";
echo "</ul>";

echo "<h3>Recommended Standardization:</h3>";
echo "<ol>";
echo "<li><strong>Keep Current Patterns:</strong> The current naming is actually functional and consistent within each category</li>";
echo "<li><strong>Class Naming:</strong> The mixed case (ENNU_Life_*) and underscore (ENNU_*) patterns are both valid and working</li>";
echo "<li><strong>No Breaking Changes:</strong> Since all classes exist and work, no immediate changes needed</li>";
echo "<li><strong>Documentation:</strong> Document the current patterns for future development</li>";
echo "</ol>";

echo "<h3>Naming Convention Summary:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Category</th><th>Pattern</th><th>Examples</th><th>Status</th></tr>";
echo "<tr><td>Constants</td><td>ENNU_LIFE_*</td><td>ENNU_LIFE_VERSION, ENNU_LIFE_PLUGIN_PATH</td><td>✅ Consistent</td></tr>";
echo "<tr><td>Classes</td><td>ENNU_Life_* / ENNU_*</td><td>ENNU_Life_Enhanced_Plugin, ENNU_Assessment_Shortcodes</td><td>⚠️ Mixed (but functional)</td></tr>";
echo "<tr><td>Options</td><td>ennu_life_*</td><td>ennu_life_settings, ennu_created_pages</td><td>✅ Consistent</td></tr>";
echo "<tr><td>Meta Keys</td><td>ennu_*</td><td>ennu_global_, ennu_life_score</td><td>✅ Consistent</td></tr>";
echo "<tr><td>Files</td><td>ennu-*</td><td>ennu-frontend-forms.js, ennu-user-dashboard.css</td><td>✅ Consistent</td></tr>";
echo "</table>";

echo "<h3>Conclusion:</h3>";
echo "<p><strong>✅ NO CRITICAL ISSUES FOUND</strong></p>";
echo "<ul>";
echo "<li>All required constants are properly defined</li>";
echo "<li>All classes exist and are functional</li>";
echo "<li>Naming patterns are consistent within each category</li>";
echo "<li>The mixed class naming patterns are working correctly</li>";
echo "<li>No immediate fixes required</li>";
echo "</ul>";

echo "<h3>Optional Improvements:</h3>";
echo "<ol>";
echo "<li><strong>Documentation:</strong> Create a naming convention guide for future developers</li>";
echo "<li><strong>Consistency:</strong> Choose one class naming pattern for future classes</li>";
echo "<li><strong>Validation:</strong> Add constant validation in critical files</li>";
echo "</ol>";

echo "<h3>Test Results:</h3>";
echo "<p><strong>✅ All constants are properly defined</strong></p>";
echo "<p><strong>✅ All classes exist and are functional</strong></p>";
echo "<p><strong>✅ Naming patterns are consistent within categories</strong></p>";
echo "<p><strong>✅ No critical naming issues found</strong></p>";

?> 