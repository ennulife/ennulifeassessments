<?php
/**
 * Test file to verify MY LIFE SCORES accordion functionality
 */

// Include WordPress
require_once('../../../wp-load.php');

// Check if user is logged in
if (!is_user_logged_in()) {
    echo "Please log in to test the dashboard accordion.\n";
    exit;
}

// Get the user dashboard content
$shortcode_instance = new ENNU_User_Dashboard_Shortcode();
$dashboard_content = $shortcode_instance->render_dashboard();

// Check if the accordion structure is present
if (strpos($dashboard_content, '<details class="dashboard-scores-accordion">') !== false) {
    echo "✅ SUCCESS: Accordion details element is present\n";
} else {
    echo "❌ FAILED: Accordion details element is missing\n";
}

// Check if the summary element is present
if (strpos($dashboard_content, '<summary class="scores-title-container">') !== false) {
    echo "✅ SUCCESS: Accordion summary element is present\n";
} else {
    echo "❌ FAILED: Accordion summary element is missing\n";
}

// Check if the MY LIFE SCORES title is inside the summary
if (strpos($dashboard_content, '<summary class="scores-title-container">') !== false && 
    strpos($dashboard_content, '<h2 class="scores-title">MY LIFE SCORES</h2>') !== false) {
    echo "✅ SUCCESS: MY LIFE SCORES title is inside the summary\n";
} else {
    echo "❌ FAILED: MY LIFE SCORES title is not inside the summary\n";
}

// Check if the scores content is inside the details element
if (strpos($dashboard_content, '<details class="dashboard-scores-accordion">') !== false && 
    strpos($dashboard_content, '<div class="scores-content-grid">') !== false) {
    echo "✅ SUCCESS: Scores content is inside the details element\n";
} else {
    echo "❌ FAILED: Scores content is not inside the details element\n";
}

// Check if the details element is properly closed
if (strpos($dashboard_content, '</details>') !== false) {
    echo "✅ SUCCESS: Details element is properly closed\n";
} else {
    echo "❌ FAILED: Details element is not properly closed\n";
}

echo "\nAccordion test completed!\n";
echo "\nTo test the accordion functionality:\n";
echo "1. Go to the user dashboard\n";
echo "2. Look for the 'MY LIFE SCORES' section\n";
echo "3. Click on the 'MY LIFE SCORES' title\n";
echo "4. The content should expand/collapse with a blue line appearing when open\n";
?> 