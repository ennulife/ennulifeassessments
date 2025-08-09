<?php
/**
 * Create Test Page for Assessment
 */

require_once('/Applications/MAMP/htdocs/wp-load.php');

// Set current user to admin for CLI
wp_set_current_user(1);

// Create a test page with assessment shortcode
$page_data = [
    'post_title'    => 'Test Hair Assessment',
    'post_content'  => '[ennu_assessment_form type="hair"]',
    'post_status'   => 'publish',
    'post_type'     => 'page',
    'post_author'   => 1
];

$existing = get_page_by_title('Test Hair Assessment');
if ($existing) {
    echo "Page already exists: " . get_permalink($existing->ID) . "\n";
} else {
    $page_id = wp_insert_post($page_data);
    if ($page_id) {
        echo "Test page created: " . get_permalink($page_id) . "\n";
    } else {
        echo "Failed to create page\n";
    }
}

// Also try the other shortcode format
$page_data2 = [
    'post_title'    => 'Test Weight Loss Assessment',
    'post_content'  => '[ennu-assessment type="weight-loss"]',
    'post_status'   => 'publish',
    'post_type'     => 'page',
    'post_author'   => 1
];

$existing2 = get_page_by_title('Test Weight Loss Assessment');
if ($existing2) {
    echo "Page already exists: " . get_permalink($existing2->ID) . "\n";
} else {
    $page_id2 = wp_insert_post($page_data2);
    if ($page_id2) {
        echo "Test page created: " . get_permalink($page_id2) . "\n";
    } else {
        echo "Failed to create page\n";
    }
}

// List all registered shortcodes
global $shortcode_tags;
echo "\nRegistered shortcodes related to ENNU:\n";
foreach ($shortcode_tags as $shortcode => $function) {
    if (strpos($shortcode, 'ennu') !== false) {
        echo "- [$shortcode]\n";
    }
}
?>