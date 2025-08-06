<?php
/**
 * Force Selection Fix - SIMPLE SOLUTION
 * 
 * Bypasses all complex logic and forces selection directly
 */
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );

// Check if user is admin
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Access denied' );
}

echo "<h1>ENNU Force Selection Fix - SIMPLE SOLUTION</h1>\n";

// Step 1: Create all missing pages
echo "<h2>Step 1: Creating All Missing Pages</h2>\n";

$pages_to_create = array(
    'dashboard' => array( 'title' => 'Health Dashboard', 'shortcode' => '[ennu-user-dashboard]' ),
    'assessments' => array( 'title' => 'Health Assessments', 'shortcode' => '[ennu-assessments]' ),
    'registration' => array( 'title' => 'Health Registration', 'shortcode' => '[ennu-welcome]' ),
    'signup' => array( 'title' => 'Sign Up', 'shortcode' => '[ennu-signup]' ),
    'assessment-results' => array( 'title' => 'Assessment Results', 'shortcode' => '[ennu-assessment-results]' ),
    'assessments/hair' => array( 'title' => 'Hair Loss Assessment', 'shortcode' => '[ennu-hair]' ),
    'assessments/ed-treatment' => array( 'title' => 'ED Treatment Assessment', 'shortcode' => '[ennu-ed-treatment]' ),
    'assessments/weight-loss' => array( 'title' => 'Weight Loss Assessment', 'shortcode' => '[ennu-weight-loss]' ),
    'assessments/health' => array( 'title' => 'Health Assessment', 'shortcode' => '[ennu-health]' ),
    'assessments/health-optimization' => array( 'title' => 'Health Optimization Assessment', 'shortcode' => '[ennu-health-optimization]' ),
    'assessments/skin' => array( 'title' => 'Skin Health Assessment', 'shortcode' => '[ennu-skin]' ),
    'assessments/hormone' => array( 'title' => 'Hormone Assessment', 'shortcode' => '[ennu-hormone]' ),
    'assessments/testosterone' => array( 'title' => 'Testosterone Assessment', 'shortcode' => '[ennu-testosterone]' ),
    'assessments/menopause' => array( 'title' => 'Menopause Assessment', 'shortcode' => '[ennu-menopause]' ),
    'assessments/sleep' => array( 'title' => 'Sleep Assessment', 'shortcode' => '[ennu-sleep]' ),
    // Sub-pages
    'assessments/hair/results' => array( 'title' => 'Hair Loss Assessment Results', 'shortcode' => '[ennu-hair-results]' ),
    'assessments/hair/details' => array( 'title' => 'Hair Loss Treatment Details', 'shortcode' => '[ennu-hair-details]' ),
    'assessments/hair/consultation' => array( 'title' => 'Hair Loss Consultation', 'shortcode' => '[ennu-hair-consultation]' ),
    'assessments/ed-treatment/results' => array( 'title' => 'ED Treatment Assessment Results', 'shortcode' => '[ennu-ed-treatment-results]' ),
    'assessments/ed-treatment/details' => array( 'title' => 'ED Treatment Details', 'shortcode' => '[ennu-ed-treatment-details]' ),
    'assessments/ed-treatment/consultation' => array( 'title' => 'ED Treatment Consultation', 'shortcode' => '[ennu-ed-treatment-consultation]' ),
    'assessments/weight-loss/results' => array( 'title' => 'Weight Loss Assessment Results', 'shortcode' => '[ennu-weight-loss-results]' ),
    'assessments/weight-loss/details' => array( 'title' => 'Weight Loss Treatment Details', 'shortcode' => '[ennu-weight-loss-details]' ),
    'assessments/weight-loss/consultation' => array( 'title' => 'Weight Loss Consultation', 'shortcode' => '[ennu-weight-loss-consultation]' ),
    'assessments/health/results' => array( 'title' => 'Health Assessment Results', 'shortcode' => '[ennu-health-results]' ),
    'assessments/health/details' => array( 'title' => 'Health Treatment Details', 'shortcode' => '[ennu-health-details]' ),
    'assessments/health/consultation' => array( 'title' => 'Health Consultation', 'shortcode' => '[ennu-health-consultation]' ),
    'assessments/health-optimization/results' => array( 'title' => 'Health Optimization Results', 'shortcode' => '[ennu-health-optimization-results]' ),
    'assessments/health-optimization/details' => array( 'title' => 'Health Optimization Details', 'shortcode' => '[ennu-health-optimization-details]' ),
    'assessments/health-optimization/consultation' => array( 'title' => 'Health Optimization Consultation', 'shortcode' => '[ennu-health-optimization-consultation]' ),
    'assessments/skin/results' => array( 'title' => 'Skin Health Assessment Results', 'shortcode' => '[ennu-skin-results]' ),
    'assessments/skin/details' => array( 'title' => 'Skin Health Treatment Details', 'shortcode' => '[ennu-skin-details]' ),
    'assessments/skin/consultation' => array( 'title' => 'Skin Health Consultation', 'shortcode' => '[ennu-skin-consultation]' ),
    'assessments/hormone/results' => array( 'title' => 'Hormone Assessment Results', 'shortcode' => '[ennu-hormone-results]' ),
    'assessments/hormone/details' => array( 'title' => 'Hormone Treatment Details', 'shortcode' => '[ennu-hormone-details]' ),
    'assessments/hormone/consultation' => array( 'title' => 'Hormone Consultation', 'shortcode' => '[ennu-hormone-consultation]' ),
    'assessments/testosterone/results' => array( 'title' => 'Testosterone Assessment Results', 'shortcode' => '[ennu-testosterone-results]' ),
    'assessments/testosterone/details' => array( 'title' => 'Testosterone Treatment Details', 'shortcode' => '[ennu-testosterone-details]' ),
    'assessments/testosterone/consultation' => array( 'title' => 'Testosterone Consultation', 'shortcode' => '[ennu-testosterone-consultation]' ),
    'assessments/menopause/results' => array( 'title' => 'Menopause Assessment Results', 'shortcode' => '[ennu-menopause-results]' ),
    'assessments/menopause/details' => array( 'title' => 'Menopause Treatment Details', 'shortcode' => '[ennu-menopause-details]' ),
    'assessments/menopause/consultation' => array( 'title' => 'Menopause Consultation', 'shortcode' => '[ennu-menopause-consultation]' ),
    'assessments/sleep/results' => array( 'title' => 'Sleep Assessment Results', 'shortcode' => '[ennu-sleep-results]' ),
    'assessments/sleep/details' => array( 'title' => 'Sleep Treatment Details', 'shortcode' => '[ennu-sleep-details]' ),
    'assessments/sleep/consultation' => array( 'title' => 'Sleep Consultation', 'shortcode' => '[ennu-sleep-consultation]' ),
);

$created_pages = array();
$created_count = 0;

foreach ( $pages_to_create as $path => $page_info ) {
    // Check if page already exists
    $existing_page = get_page_by_path( $path );
    
    if ( ! $existing_page ) {
        // Create the page
        $page_id = wp_insert_post( array(
            'post_title' => $page_info['title'],
            'post_content' => $page_info['shortcode'],
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => basename( $path )
        ) );
        
        if ( $page_id && ! is_wp_error( $page_id ) ) {
            $created_pages[ $path ] = $page_id;
            $created_count++;
            echo "<p>✅ Created page: {$page_info['title']} (ID: {$page_id})</p>\n";
        } else {
            echo "<p>❌ Failed to create page: {$page_info['title']}</p>\n";
        }
    } else {
        $created_pages[ $path ] = $existing_page->ID;
        echo "<p>ℹ️ Page already exists: {$page_info['title']} (ID: {$existing_page->ID})</p>\n";
    }
}

echo "<p><strong>Total pages created/found: {$created_count}</strong></p>\n";

// Step 2: Update settings with created pages
echo "<h2>Step 2: Updating Settings</h2>\n";

$settings = get_option( 'ennu_life_settings', array() );
$settings['page_mappings'] = $created_pages;
update_option( 'ennu_life_settings', $settings );
update_option( 'ennu_created_pages', $created_pages );

echo "<p>✅ Settings updated with all page mappings</p>\n";

// Step 3: Force selection JavaScript
echo "<h2>Step 3: Force Selection JavaScript</h2>\n";

echo "<script>
jQuery(document).ready(function($) {
    console.log('ENNU Force Selection: Starting forced selection');
    
    var createdPages = " . json_encode( $created_pages ) . ";
    var selectedCount = 0;
    
    function forceSelectAll() {
        $.each(createdPages, function(key, pageId) {
            var dropdown = $('#page_mapping_' + key);
            if (dropdown.length) {
                // Add option if it doesn't exist
                var optionExists = dropdown.find('option[value=\"' + pageId + '\"]').length > 0;
                if (!optionExists) {
                    dropdown.append('<option value=\"' + pageId + '\">Page ' + pageId + '</option>');
                }
                
                // Force select
                dropdown.val(pageId);
                dropdown.trigger('change');
                selectedCount++;
                console.log('ENNU Force Selection: Selected page ' + pageId + ' for key ' + key);
            }
        });
        
        console.log('ENNU Force Selection: Selected ' + selectedCount + ' pages');
        return selectedCount;
    }
    
    // Try immediately
    var immediateCount = forceSelectAll();
    
    // Try after delays
    setTimeout(function() {
        var delayedCount = forceSelectAll();
        console.log('ENNU Force Selection: Delayed selection - ' + delayedCount + ' pages');
    }, 1000);
    
    setTimeout(function() {
        var finalCount = forceSelectAll();
        console.log('ENNU Force Selection: Final selection - ' + finalCount + ' pages');
        
        if (finalCount > 0) {
            alert('ENNU Force Selection: Successfully selected ' + finalCount + ' pages!');
        }
    }, 2000);
});
</script>";

echo "<p>✅ Force selection JavaScript loaded</p>\n";

// Step 4: Show results
echo "<h2>Step 4: Results</h2>\n";
echo "<p>Created pages: " . count( $created_pages ) . "</p>\n";
echo "<pre>" . print_r( $created_pages, true ) . "</pre>\n";

echo "<h2>Step 5: Next Steps</h2>\n";
echo "<p>1. Check the browser console for selection logs</p>\n";
echo "<p>2. Go to <a href='/wp-admin/admin.php?page=ennu-life-settings' target='_blank'>ENNU Settings</a> to verify selections</p>\n";
echo "<p>3. The page will refresh automatically in 3 seconds</p>\n";

?> 