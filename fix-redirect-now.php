<?php
/**
 * Direct fix to ensure redirect works immediately
 */

// Load WordPress
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );

echo "<h1>ENNU Redirect Fix - Immediate Solution</h1>";

// Step 1: Create all necessary pages
echo "<h2>1. Creating All Necessary Pages</h2>";

$pages_to_create = array(
    'hair_results_page_id' => array(
        'title' => 'Hair Assessment Results',
        'content' => '[ennu-hair-results]',
        'slug' => 'hair-assessment-results'
    ),
    'ed-treatment_results_page_id' => array(
        'title' => 'ED Treatment Assessment Results', 
        'content' => '[ennu-ed-treatment-results]',
        'slug' => 'ed-treatment-assessment-results'
    ),
    'weight-loss_results_page_id' => array(
        'title' => 'Weight Loss Assessment Results',
        'content' => '[ennu-weight-loss-results]', 
        'slug' => 'weight-loss-assessment-results'
    ),
    'health_results_page_id' => array(
        'title' => 'Health Assessment Results',
        'content' => '[ennu-health-results]',
        'slug' => 'health-assessment-results'
    ),
    'health-optimization_results_page_id' => array(
        'title' => 'Health Optimization Results',
        'content' => '[ennu-health-optimization-results]',
        'slug' => 'health-optimization-results'
    ),
    'skin_results_page_id' => array(
        'title' => 'Skin Assessment Results',
        'content' => '[ennu-skin-results]',
        'slug' => 'skin-assessment-results'
    ),
    'hormone_results_page_id' => array(
        'title' => 'Hormone Assessment Results',
        'content' => '[ennu-hormone-results]',
        'slug' => 'hormone-assessment-results'
    ),
    'testosterone_results_page_id' => array(
        'title' => 'Testosterone Assessment Results',
        'content' => '[ennu-testosterone-results]',
        'slug' => 'testosterone-assessment-results'
    ),
    'menopause_results_page_id' => array(
        'title' => 'Menopause Assessment Results',
        'content' => '[ennu-menopause-results]',
        'slug' => 'menopause-assessment-results'
    ),
    'sleep_results_page_id' => array(
        'title' => 'Sleep Assessment Results',
        'content' => '[ennu-sleep-results]',
        'slug' => 'sleep-assessment-results'
    )
);

$settings = get_option( 'ennu_life_settings', array() );
$page_mappings = $settings['page_mappings'] ?? array();

$created_count = 0;
foreach ( $pages_to_create as $key => $page_info ) {
    if ( ! isset( $page_mappings[ $key ] ) || empty( $page_mappings[ $key ] ) ) {
        // Create the page
        $page_data = array(
            'post_title' => $page_info['title'],
            'post_content' => $page_info['content'],
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => $page_info['slug']
        );
        
        $page_id = wp_insert_post( $page_data );
        
        if ( $page_id && ! is_wp_error( $page_id ) ) {
            $page_mappings[ $key ] = $page_id;
            $created_count++;
            echo "✅ Created page: {$page_info['title']} (ID: {$page_id})<br>";
        } else {
            echo "❌ Failed to create page: {$page_info['title']}<br>";
        }
    } else {
        echo "ℹ️ Page already exists: {$page_info['title']} (ID: {$page_mappings[ $key ]})<br>";
    }
}

// Step 2: Update the settings
$settings['page_mappings'] = $page_mappings;
update_option( 'ennu_life_settings', $settings );

echo "<h2>2. Updated Settings ({$created_count} new pages created)</h2>";

// Step 3: Test get_thank_you_url for all assessments
echo "<h2>3. Test get_thank_you_url() for All Assessments</h2>";

if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
    $shortcodes = new ENNU_Assessment_Shortcodes();
    
    $test_assessments = array( 
        'hair', 'ed-treatment', 'weight-loss', 'health', 'health-optimization',
        'skin', 'hormone', 'testosterone', 'menopause', 'sleep'
    );
    
    $working_count = 0;
    foreach ( $test_assessments as $assessment_type ) {
        $thank_you_url = $shortcodes->get_thank_you_url( $assessment_type, 'test-token-123' );
        echo "<strong>{$assessment_type}:</strong> {$thank_you_url}<br>";
        
        if ( $thank_you_url && $thank_you_url !== false ) {
            echo "✅ Redirect URL generated successfully<br>";
            $working_count++;
        } else {
            echo "❌ Redirect URL generation failed<br>";
        }
        echo "<br>";
    }
    
    echo "<h3>Summary: {$working_count}/" . count( $test_assessments ) . " assessments working</h3>";
} else {
    echo "❌ ENNU_Assessment_Shortcodes class not found<br>";
}

// Step 4: Test actual AJAX response
echo "<h2>4. Test Actual AJAX Response</h2>";

// Simulate the exact AJAX request
$_POST = array(
    'action' => 'ennu_submit_assessment',
    'nonce' => wp_create_nonce( 'ennu_ajax_nonce' ),
    'assessment_type' => 'hair',
    'email' => 'test@example.com',
    'first_name' => 'Test',
    'last_name' => 'User',
    'height_ft' => '5',
    'height_in' => '10',
    'weight_lbs' => '150'
);

// Capture the output
ob_start();

if ( class_exists( 'ENNU_Assessment_Shortcodes' ) ) {
    $shortcodes = new ENNU_Assessment_Shortcodes();
    $shortcodes->handle_assessment_submission();
    
    $response = ob_get_clean();
    
    echo "<h3>Raw AJAX Response:</h3>";
    echo "<pre>" . htmlspecialchars( $response ) . "</pre>";
    
    // Try to decode JSON
    $decoded = json_decode( $response, true );
    if ( $decoded ) {
        echo "<h3>Decoded Response:</h3>";
        echo "<pre>";
        print_r( $decoded );
        echo "</pre>";
        
        if ( isset( $decoded['data']['redirect_url'] ) ) {
            echo "✅ redirect_url found: {$decoded['data']['redirect_url']}<br>";
            echo "✅ JavaScript should now redirect successfully!<br>";
        } else {
            echo "❌ redirect_url still missing from response<br>";
        }
    } else {
        echo "❌ Response is not valid JSON<br>";
    }
}

echo "<h2>5. Final Instructions</h2>";
echo "<p><strong>Now try submitting a form:</strong></p>";
echo "<ol>";
echo "<li>Go to your assessment page (e.g., /hair-assessment/)</li>";
echo "<li>Fill out the form and submit</li>";
echo "<li>Check the browser console - you should see the redirect URL</li>";
echo "<li>You should be redirected to the results page</li>";
echo "</ol>";

echo "<p><strong>If it still doesn't work:</strong></p>";
echo "<ol>";
echo "<li>Check the browser console for any JavaScript errors</li>";
echo "<li>Make sure the assessment page has the correct shortcode</li>";
echo "<li>Check the WordPress debug log for PHP errors</li>";
echo "<li>Try refreshing the page and submitting again</li>";
echo "</ol>";

echo "<p><strong>Success indicators:</strong></p>";
echo "<ul>";
echo "<li>✅ Pages are created in admin settings</li>";
echo "<li>✅ get_thank_you_url() returns valid URLs</li>";
echo "<li>✅ AJAX response includes redirect_url</li>";
echo "<li>✅ JavaScript receives the redirect_url</li>";
echo "<li>✅ User is redirected to results page</li>";
echo "</ul>";
?> 