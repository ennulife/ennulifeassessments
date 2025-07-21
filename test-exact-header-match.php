<?php
/**
 * Test Script: Exact Header Styling Match Verification
 * 
 * This script verifies that the assessment header now matches the exact dashboard styling.
 * 
 * @package ENNU Life Assessments
 * @version 62.2.23
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Ensure we're in WordPress context
if (!function_exists('get_option')) {
    die('WordPress not loaded');
}

echo "<h1>ENNU Life Header - Exact Dashboard Styling Match Verification</h1>\n";
echo "<p>Testing that the assessment header now matches the exact dashboard styling specifications.</p>\n";

// Initialize the shortcodes class
$shortcode_instance = new ENNU_Assessment_Shortcodes();

// Test the header rendering method
echo "<h2>1. Testing Header with Exact Dashboard Styling</h2>\n";

// Use reflection to access private method for testing
$reflection = new ReflectionClass($shortcode_instance);
$render_header_method = $reflection->getMethod('render_ennu_header');
$render_header_method->setAccessible(true);

$header_html = $render_header_method->invoke($shortcode_instance, 'test_assessment', array(
    'title' => 'Test Assessment',
    'description' => 'This is a test assessment description'
));

echo "<div style='background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
echo "<h3>Generated Header HTML (should match dashboard exactly):</h3>\n";
echo "<pre style='background: white; padding: 15px; border-radius: 5px; overflow-x: auto;'>" . htmlspecialchars($header_html) . "</pre>\n";
echo "</div>\n";

// Test CSS specifications
echo "<h2>2. CSS Specifications Verification</h2>\n";

$css_file_path = ENNU_LIFE_PLUGIN_PATH . 'assets/css/ennu-frontend-forms.css';
$css_content = file_get_contents($css_file_path);

// Check for exact dashboard specifications
$specifications = array(
    'Title Font Size' => array(
        'expected' => '2.4rem',
        'found' => strpos($css_content, 'font-size: 2.4rem') !== false ? '2.4rem' : 'NOT FOUND'
    ),
    'Title Font Weight' => array(
        'expected' => '300',
        'found' => strpos($css_content, 'font-weight: 300') !== false ? '300' : 'NOT FOUND'
    ),
    'Title Letter Spacing' => array(
        'expected' => '-0.5px',
        'found' => strpos($css_content, 'letter-spacing: -0.5px') !== false ? '-0.5px' : 'NOT FOUND'
    ),
    'Title Margin' => array(
        'expected' => '0 0 12px 0',
        'found' => strpos($css_content, 'margin: 0 0 12px 0') !== false ? '0 0 12px 0' : 'NOT FOUND'
    ),
    'Subtitle Font Size' => array(
        'expected' => '1.1rem',
        'found' => strpos($css_content, 'font-size: 1.1rem') !== false ? '1.1rem' : 'NOT FOUND'
    ),
    'Subtitle Opacity' => array(
        'expected' => '0.8',
        'found' => strpos($css_content, 'opacity: 0.8') !== false ? '0.8' : 'NOT FOUND'
    ),
    'Subtitle Margin' => array(
        'expected' => '0 0 11px 0',
        'found' => strpos($css_content, 'margin: 0 0 11px 0') !== false ? '0 0 11px 0' : 'NOT FOUND'
    ),
    'Mobile Title Size' => array(
        'expected' => '1.8rem',
        'found' => strpos($css_content, 'font-size: 1.8rem') !== false ? '1.8rem' : 'NOT FOUND'
    ),
    'Mobile Subtitle Size' => array(
        'expected' => '1rem',
        'found' => strpos($css_content, 'font-size: 1rem') !== false ? '1rem' : 'NOT FOUND'
    )
);

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr><th>Specification</th><th>Expected</th><th>Found</th><th>Status</th></tr>\n";

foreach ($specifications as $spec => $data) {
    $status = ($data['expected'] === $data['found']) ? '✅ MATCH' : '❌ MISMATCH';
    $status_color = ($data['expected'] === $data['found']) ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td><strong>$spec</strong></td>";
    echo "<td><code>{$data['expected']}</code></td>";
    echo "<td><code>{$data['found']}</code></td>";
    echo "<td style='color: $status_color; font-weight: bold;'>$status</td>";
    echo "</tr>\n";
}
echo "</table>\n";

// Test inline styles verification
echo "<h2>3. Inline Styles Verification</h2>\n";

$inline_style_checks = array(
    'Title Inline Font Size' => strpos($header_html, 'font-size: 2.4rem') !== false,
    'Title Inline Font Weight' => strpos($header_html, 'font-weight: 300') !== false,
    'Title Inline Letter Spacing' => strpos($header_html, 'letter-spacing: -0.5px') !== false,
    'Title Inline Margin' => strpos($header_html, 'margin: 0 0 12px 0') !== false,
    'Subtitle Inline Font Size' => strpos($header_html, 'font-size: 1.1rem') !== false,
    'Subtitle Inline Opacity' => strpos($header_html, 'opacity: 0.8') !== false,
    'Subtitle Inline Margin' => strpos($header_html, 'margin: 0 0 11px 0') !== false
);

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>\n";
echo "<tr><th>Inline Style Check</th><th>Status</th></tr>\n";

foreach ($inline_style_checks as $check => $found) {
    $status = $found ? '✅ PRESENT' : '❌ MISSING';
    $status_color = $found ? 'green' : 'red';
    
    echo "<tr>";
    echo "<td><strong>$check</strong></td>";
    echo "<td style='color: $status_color; font-weight: bold;'>$status</td>";
    echo "</tr>\n";
}
echo "</table>\n";

// Test rendered header display
echo "<h2>4. Rendered Header Display</h2>\n";

echo "<div style='background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
echo "<h3>Actual Header Rendering (should look exactly like dashboard):</h3>\n";
echo "<div style='background: white; padding: 15px; border-radius: 5px;'>\n";
echo $header_html;
echo "</div>\n";
echo "</div>\n";

// Dashboard comparison
echo "<h2>5. Dashboard Header Comparison</h2>\n";

echo "<div style='background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
echo "<h3>Dashboard Header Reference (for comparison):</h3>\n";
echo "<div style='background: white; padding: 15px; border-radius: 5px;'>\n";
echo "<div style='text-align: center; margin-bottom: 40px; padding: 20px 0; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; margin: 0 0 30px 0; position: relative; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);'>\n";
echo "<div style='margin-bottom: 20px; position: relative; z-index: 2;'>\n";
echo "<img src='" . ENNU_LIFE_PLUGIN_URL . "assets/img/ennu-logo-black.png' alt='ENNU Life Logo' style='height: 40px; width: auto; display: block;'>\n";
echo "</div>\n";
echo "<div style='position: relative; z-index: 2;'>\n";
echo "<h1 style='font-size: 2.4rem; font-weight: 300; color: #212529; margin: 0 0 12px 0; line-height: 1.2; text-align: center; letter-spacing: -0.5px;'>Dashboard Title</h1>\n";
echo "<p style='font-size: 1.1rem; color: #6c757d; margin: 0 0 11px 0; line-height: 1.4; text-align: center; font-weight: 400; opacity: 0.8;'>Dashboard subtitle for comparison</p>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";
echo "</div>\n";

// Summary
echo "<h2>6. Summary</h2>\n";

$all_specs_match = true;
foreach ($specifications as $spec => $data) {
    if ($data['expected'] !== $data['found']) {
        $all_specs_match = false;
        break;
    }
}

$all_inline_match = true;
foreach ($inline_style_checks as $check => $found) {
    if (!$found) {
        $all_inline_match = false;
        break;
    }
}

echo "<div style='background: " . ($all_specs_match && $all_inline_match ? '#d4edda' : '#f8d7da') . "; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
echo "<h3 style='color: " . ($all_specs_match && $all_inline_match ? '#155724' : '#721c24') . ";'>";

if ($all_specs_match && $all_inline_match) {
    echo "✅ SUCCESS: Header matches dashboard styling exactly!";
} else {
    echo "❌ ISSUES: Some specifications don't match dashboard styling.";
}

echo "</h3>\n";

if ($all_specs_match && $all_inline_match) {
    echo "<p style='color: #155724;'><strong>Perfect match achieved!</strong> The assessment header now uses the exact same typography, spacing, and styling as the dashboard header.</p>\n";
    echo "<ul style='color: #155724;'>\n";
    echo "<li>✅ Title: 2.4rem, font-weight: 300, letter-spacing: -0.5px</li>\n";
    echo "<li>✅ Subtitle: 1.1rem, opacity: 0.8, proper margins</li>\n";
    echo "<li>✅ Mobile responsive: 1.8rem title, 1rem subtitle</li>\n";
    echo "<li>✅ Both CSS classes and inline styles synchronized</li>\n";
    echo "<li>✅ Assessment-specific content maintained</li>\n";
    echo "</ul>\n";
} else {
    echo "<p style='color: #721c24;'><strong>Issues found:</strong> Some specifications don't match the dashboard styling.</p>\n";
    echo "<ul style='color: #721c24;'>\n";
    foreach ($specifications as $spec => $data) {
        if ($data['expected'] !== $data['found']) {
            echo "<li>❌ $spec: Expected {$data['expected']}, found {$data['found']}</li>\n";
        }
    }
    foreach ($inline_style_checks as $check => $found) {
        if (!$found) {
            echo "<li>❌ $check: Missing from inline styles</li>\n";
        }
    }
    echo "</ul>\n";
}

echo "</div>\n";

echo "<p><strong>Test completed!</strong></p>\n";
?> 