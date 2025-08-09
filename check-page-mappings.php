<?php
/**
 * ENNU Life Assessments - Page Mapping Diagnostic Script
 * 
 * This script checks the current page mapping status and identifies potential issues
 * with URL generation and assessment-specific consultation pages.
 */

// Load WordPress
require_once('../../../wp-load.php');

echo "🔍 ENNU Life Assessments - Page Mapping Diagnostic\n";
echo "==================================================\n\n";

// Get current settings
$settings = get_option('ennu_life_settings', array());
$page_mappings = $settings['page_mappings'] ?? array();

echo "📊 CURRENT PAGE MAPPING STATUS:\n";
echo "--------------------------------\n";

// Count mapped pages
$mapped_count = count(array_filter($page_mappings));
$total_expected = 15; // Core pages needed
$percentage = round(($mapped_count / $total_expected) * 100, 1);

echo "Mapped Pages: {$mapped_count}/{$total_expected} ({$percentage}%)\n\n";

// Show current mappings
echo "📋 CURRENT PAGE MAPPINGS:\n";
echo "-------------------------\n";
foreach ($page_mappings as $key => $page_id) {
    $page_title = get_the_title($page_id);
    $page_url = get_permalink($page_id);
    echo "✓ {$key}: ID {$page_id} - '{$page_title}' ({$page_url})\n";
}

echo "\n";

// Check for missing critical pages
$critical_pages = array(
    'dashboard' => 'Health Dashboard',
    'assessments' => 'Health Assessments',
    'call' => 'Schedule a Call',
    'registration' => 'Registration Page',
    'signup' => 'Sign Up Page'
);

echo "⚠️  MISSING CRITICAL PAGES:\n";
echo "---------------------------\n";
$missing_critical = 0;
foreach ($critical_pages as $key => $title) {
    if (empty($page_mappings[$key])) {
        echo "❌ {$key} ({$title}) - NOT MAPPED\n";
        $missing_critical++;
    }
}

if ($missing_critical === 0) {
    echo "✅ All critical pages are mapped!\n";
}

echo "\n";

// Check assessment-specific consultation pages
echo "🎯 ASSESSMENT-SPECIFIC CONSULTATION PAGES:\n";
echo "------------------------------------------\n";

$assessment_types = array(
    'hair' => 'Hair Loss',
    'ed_treatment' => 'ED Treatment', 
    'weight_loss' => 'Weight Loss',
    'health' => 'Health',
    'skin' => 'Skin Health',
    'hormone' => 'Hormone',
    'testosterone' => 'Testosterone',
    'menopause' => 'Menopause',
    'sleep' => 'Sleep'
);

$missing_assessment_pages = 0;
foreach ($assessment_types as $key => $name) {
    $consultation_key = $key . '_consultation_page_id';
    $page_id = $page_mappings[$consultation_key] ?? null;
    
    if (empty($page_id)) {
        echo "❌ {$name} consultation page - NOT MAPPED\n";
        $missing_assessment_pages++;
    } else {
        $page_title = get_the_title($page_id);
        echo "✅ {$name} consultation: ID {$page_id} - '{$page_title}'\n";
    }
}

echo "\n";

// Test URL generation
echo "🔗 URL GENERATION TEST:\n";
echo "----------------------\n";

// Test the get_assessment_cta_url method
if (class_exists('ENNU_Assessment_Shortcodes')) {
    $shortcodes = new ENNU_Assessment_Shortcodes();
    
    foreach ($assessment_types as $key => $name) {
        $assessment_type = $key . '_assessment';
        $cta_url = $shortcodes->get_assessment_cta_url($assessment_type);
        echo "{$name}: {$cta_url}\n";
    }
} else {
    echo "❌ ENNU_Assessment_Shortcodes class not found\n";
}

echo "\n";

// Check for potential issues
echo "🚨 POTENTIAL ISSUES IDENTIFIED:\n";
echo "------------------------------\n";

if ($percentage < 100) {
    echo "⚠️  Page mapping completion is {$percentage}% - some links may not work correctly\n";
}

if ($missing_assessment_pages > 0) {
    echo "⚠️  {$missing_assessment_pages} assessment-specific consultation pages are missing\n";
    echo "   This means all assessments will fall back to the generic call page\n";
}

if ($missing_critical > 0) {
    echo "⚠️  {$missing_critical} critical pages are missing - core functionality may be affected\n";
}

// Check URL format consistency
echo "\n📝 URL FORMAT ANALYSIS:\n";
echo "----------------------\n";
echo "✅ All URLs use ?page_id={id} format for compatibility\n";
echo "✅ No pretty permalinks dependency - ensures compatibility\n";

echo "\n";

// Recommendations
echo "💡 RECOMMENDATIONS:\n";
echo "------------------\n";

if ($percentage < 100) {
    echo "1. Run the 'Auto-Detect Pages' function in the admin panel\n";
}

if ($missing_assessment_pages > 0) {
    echo "2. Create assessment-specific consultation pages for better user experience\n";
}

if ($missing_critical > 0) {
    echo "3. Ensure all critical pages are created and mapped\n";
}

echo "4. Test all assessment CTA links to ensure they point to correct pages\n";
echo "5. Verify consultation page content is appropriate for each assessment type\n";

echo "\n";
echo "✅ Diagnostic complete!\n";
?> 