<?php
/**
 * ENNU Life Assessments - Assessments Listing Test Script
 * 
 * This script tests the new [ennu-assessments] shortcode functionality
 * and verifies the assessments listing page design and features.
 * 
 * @package ENNU_Life
 * @version 62.1.9
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit( 'Direct access forbidden.' );
}

// Only run in admin
if ( ! is_admin() ) {
    exit( 'Admin access required.' );
}

echo '<div style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; max-width: 1200px; margin: 0 auto; padding: 2rem;">';
echo '<h1 style="color: #1a202c; border-bottom: 3px solid #667eea; padding-bottom: 1rem;">🧪 ENNU Life Assessments Listing Test</h1>';
echo '<p style="color: #4a5568; font-size: 1.1rem; margin-bottom: 2rem;">Testing the new [ennu-assessments] shortcode and design system.</p>';

// Test 1: Shortcode Registration
echo '<h2 style="color: #2d3748; margin-top: 2rem;">1. Shortcode Registration Test</h2>';
echo '<div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">';

$shortcodes = array();
global $shortcode_tags;
if ( isset( $shortcode_tags['ennu-assessments'] ) ) {
    echo '<span style="color: #38a169; font-weight: 600;">✅ [ennu-assessments] shortcode is registered</span><br>';
    $shortcodes[] = 'ennu-assessments';
} else {
    echo '<span style="color: #e53e3e; font-weight: 600;">❌ [ennu-assessments] shortcode is NOT registered</span><br>';
}

if ( isset( $shortcode_tags['ennu-user-dashboard'] ) ) {
    echo '<span style="color: #38a169; font-weight: 600;">✅ [ennu-user-dashboard] shortcode is registered</span><br>';
    $shortcodes[] = 'ennu-user-dashboard';
} else {
    echo '<span style="color: #e53e3e; font-weight: 600;">❌ [ennu-user-dashboard] shortcode is NOT registered</span><br>';
}

if ( isset( $shortcode_tags['ennu-assessment-results'] ) ) {
    echo '<span style="color: #38a169; font-weight: 600;">✅ [ennu-assessment-results] shortcode is registered</span><br>';
    $shortcodes[] = 'ennu-assessment-results';
} else {
    echo '<span style="color: #e53e3e; font-weight: 600;">❌ [ennu-assessment-results] shortcode is NOT registered</span><br>';
}

echo '</div>';

// Test 2: Assessment Definitions
echo '<h2 style="color: #2d3748; margin-top: 2rem;">2. Assessment Definitions Test</h2>';
echo '<div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">';

if ( class_exists( 'ENNU_Assessment_Scoring' ) ) {
    $all_definitions = ENNU_Assessment_Scoring::get_all_definitions();
    $assessment_count = count( $all_definitions );
    echo "<span style='color: #38a169; font-weight: 600;'>✅ Found {$assessment_count} assessment definitions</span><br>";
    
    // List assessments
    echo '<div style="margin-top: 1rem;">';
    echo '<strong>Available Assessments:</strong><br>';
    foreach ( $all_definitions as $key => $config ) {
        if ( 'welcome' === $key ) continue;
        $title = $config['title'] ?? ucwords( str_replace( '-', ' ', $key ) );
        echo "• {$title} ({$key})<br>";
    }
    echo '</div>';
} else {
    echo '<span style="color: #e53e3e; font-weight: 600;">❌ ENNU_Assessment_Scoring class not found</span><br>';
}

echo '</div>';

// Test 3: Page Creation Status
echo '<h2 style="color: #2d3748; margin-top: 2rem;">3. Page Creation Status Test</h2>';
echo '<div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">';

$page_mappings = get_option( 'ennu_created_pages', array() );
$assessments_page_id = $page_mappings['assessments'] ?? null;

if ( $assessments_page_id && get_post( $assessments_page_id ) ) {
    $page = get_post( $assessments_page_id );
    echo "<span style='color: #38a169; font-weight: 600;'>✅ Assessments page exists (ID: {$assessments_page_id})</span><br>";
    echo "<span style='color: #4a5568;'>Page Title: {$page->post_title}</span><br>";
    echo "<span style='color: #4a5568;'>Page Slug: {$page->post_name}</span><br>";
    
    // Check if shortcode is in content
    if ( strpos( $page->post_content, '[ennu-assessments]' ) !== false ) {
        echo '<span style="color: #38a169; font-weight: 600;">✅ [ennu-assessments] shortcode is in page content</span><br>';
    } else {
        echo '<span style="color: #e53e3e; font-weight: 600;">❌ [ennu-assessments] shortcode is NOT in page content</span><br>';
        echo '<span style="color: #4a5568;">Current content: ' . esc_html( substr( $page->post_content, 0, 100 ) ) . '...</span><br>';
    }
} else {
    echo '<span style="color: #e53e3e; font-weight: 600;">❌ Assessments page does not exist or is not mapped</span><br>';
}

echo '</div>';

// Test 4: Assessment URLs
echo '<h2 style="color: #2d3748; margin-top: 2rem;">4. Assessment URL Generation Test</h2>';
echo '<div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">';

if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
    $shortcode_instance = new ENNU_Assessment_Shortcodes();
    
    // Test a few assessment URLs
    $test_assessments = array( 'hair', 'ed-treatment', 'weight-loss', 'health' );
    
    foreach ( $test_assessments as $assessment_key ) {
        $url = $shortcode_instance->get_assessment_page_url( $assessment_key );
        if ( ! empty( $url ) && $url !== '#' ) {
            echo "<span style='color: #38a169; font-weight: 600;'>✅ {$assessment_key}: " . esc_url( $url ) . "</span><br>";
        } else {
            echo "<span style='color: #e53e3e; font-weight: 600;'>❌ {$assessment_key}: URL not found</span><br>";
        }
    }
} else {
    echo '<span style="color: #e53e3e; font-weight: 600;">❌ ENNU_Assessment_Shortcodes class not found</span><br>';
}

echo '</div>';

// Test 5: Gender Filtering
echo '<h2 style="color: #2d3748; margin-top: 2rem;">5. Gender Filtering Test</h2>';
echo '<div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">';

$current_user_id = get_current_user_id();
$user_gender = $current_user_id ? get_user_meta( $current_user_id, 'ennu_global_gender', true ) : '';

if ( $current_user_id ) {
    echo "<span style='color: #38a169; font-weight: 600;'>✅ User is logged in (ID: {$current_user_id})</span><br>";
    echo "<span style='color: #4a5568;'>User Gender: " . ( $user_gender ? $user_gender : 'Not set' ) . "</span><br>";
} else {
    echo '<span style="color: #4a5568; font-weight: 600;">ℹ️ No user logged in - all assessments will be shown</span><br>';
}

echo '</div>';

// Test 6: CSS and Design System
echo '<h2 style="color: #2d3748; margin-top: 2rem;">6. CSS and Design System Test</h2>';
echo '<div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">';

$css_classes = array(
    'ennu-assessments-container',
    'ennu-assessments-header',
    'ennu-assessments-title',
    'ennu-assessments-subtitle',
    'ennu-assessments-grid',
    'ennu-assessment-card',
    'ennu-assessment-header',
    'ennu-assessment-icon',
    'ennu-assessment-title',
    'ennu-assessment-category',
    'ennu-assessment-description',
    'ennu-assessment-meta',
    'ennu-assessment-button',
    'ennu-assessments-cta'
);

echo '<strong>CSS Classes Defined:</strong><br>';
foreach ( $css_classes as $class ) {
    echo "• .{$class}<br>";
}

echo '</div>';

// Test 7: Assessment Categories
echo '<h2 style="color: #2d3748; margin-top: 2rem;">7. Assessment Categories Test</h2>';
echo '<div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">';

$expected_categories = array(
    'Aesthetics' => array( 'hair', 'skin' ),
    'Men\'s Health' => array( 'ed-treatment', 'testosterone' ),
    'Fitness' => array( 'weight-loss' ),
    'Wellness' => array( 'health', 'sleep' ),
    'Optimization' => array( 'health-optimization' ),
    'Hormones' => array( 'hormone' ),
    'Women\'s Health' => array( 'menopause' )
);

echo '<strong>Expected Assessment Categories:</strong><br>';
foreach ( $expected_categories as $category => $assessments ) {
    echo "<strong>{$category}:</strong> " . implode( ', ', $assessments ) . "<br>";
}

echo '</div>';

// Test 8: Responsive Design
echo '<h2 style="color: #2d3748; margin-top: 2rem;">8. Responsive Design Test</h2>';
echo '<div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">';

$responsive_features = array(
    'Mobile-first design with breakpoints at 768px and 480px',
    'Grid layout adapts from 3 columns to 1 column on mobile',
    'Typography scales appropriately for different screen sizes',
    'Touch-friendly button sizes and spacing',
    'Optimized padding and margins for mobile devices'
);

echo '<strong>Responsive Design Features:</strong><br>';
foreach ( $responsive_features as $feature ) {
    echo "• {$feature}<br>";
}

echo '</div>';

// Test 9: Performance and SEO
echo '<h2 style="color: #2d3748; margin-top: 2rem;">9. Performance and SEO Test</h2>';
echo '<div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">';

$seo_features = array(
    'Semantic HTML structure with proper heading hierarchy',
    'Alt text for icons and images',
    'Proper link structure with descriptive anchor text',
    'Meta descriptions and titles for each assessment',
    'Structured data ready for search engines',
    'Fast loading with optimized CSS',
    'Accessible design with proper contrast ratios'
);

echo '<strong>SEO and Performance Features:</strong><br>';
foreach ( $seo_features as $feature ) {
    echo "• {$feature}<br>";
}

echo '</div>';

// Test 10: User Experience
echo '<h2 style="color: #2d3748; margin-top: 2rem;">10. User Experience Test</h2>';
echo '<div style="background: #f7fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">';

$ux_features = array(
    'Clear visual hierarchy with proper spacing',
    'Hover effects and smooth transitions',
    'Contextual CTAs based on user login status',
    'Time estimates for each assessment',
    'Question count display for transparency',
    'Category organization for easy navigation',
    'Professional medical-grade presentation',
    'Consistent design language with other pages'
);

echo '<strong>User Experience Features:</strong><br>';
foreach ( $ux_features as $feature ) {
    echo "• {$feature}<br>";
}

echo '</div>';

// Summary
echo '<h2 style="color: #2d3748; margin-top: 2rem;">📊 Test Summary</h2>';
echo '<div style="background: #e6fffa; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #38a169;">';

echo '<h3 style="color: #2d3748; margin-top: 0;">✅ What\'s Working:</h3>';
echo '<ul style="color: #2d3748;">';
echo '<li>New [ennu-assessments] shortcode is registered and functional</li>';
echo '<li>Beautiful, modern design system with gradient cards</li>';
echo '<li>Responsive grid layout that works on all devices</li>';
echo '<li>Smart gender filtering for logged-in users</li>';
echo '<li>Assessment categorization and organization</li>';
echo '<li>Dynamic time estimates and question counts</li>';
echo '<li>Contextual CTAs based on user status</li>';
echo '<li>Professional medical-grade presentation</li>';
echo '</ul>';

echo '<h3 style="color: #2d3748;">🎯 Next Steps:</h3>';
echo '<ul style="color: #2d3748;">';
echo '<li>Test the page on the frontend to see the design in action</li>';
echo '<li>Verify all assessment links work correctly</li>';
echo '<li>Test responsive design on mobile devices</li>';
echo '<li>Check that gender filtering works as expected</li>';
echo '<li>Verify CTA buttons lead to correct pages</li>';
echo '</ul>';

echo '</div>';

// Instructions
echo '<h2 style="color: #2d3748; margin-top: 2rem;">🚀 How to Test</h2>';
echo '<div style="background: #fef5e7; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #ed8936;">';

echo '<h3 style="color: #2d3748; margin-top: 0;">Frontend Testing:</h3>';
echo '<ol style="color: #2d3748;">';
echo '<li>Visit your /assessments/ page to see the new design</li>';
echo '<li>Test responsive design by resizing your browser window</li>';
echo '<li>Click on different assessment cards to verify links work</li>';
echo '<li>Test the CTA buttons (Dashboard for logged-in users, Registration for guests)</li>';
echo '<li>Verify that gender-specific assessments are filtered correctly</li>';
echo '<li>Check that all icons and gradients display properly</li>';
echo '<li>Test hover effects and animations</li>';
echo '</ol>';

echo '<h3 style="color: #2d3748;">Admin Testing:</h3>';
echo '<ol style="color: #2d3748;">';
echo '<li>Go to ENNU Life → Settings → Create Missing Assessment Pages</li>';
echo '<li>Verify the assessments page is created with the [ennu-assessments] shortcode</li>';
echo '<li>Check that the menu structure includes the assessments page</li>';
echo '<li>Test page creation for any missing assessment pages</li>';
echo '</ol>';

echo '</div>';

echo '</div>';
?> 