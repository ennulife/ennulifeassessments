<?php
/**
 * Test file to verify My Biomarkers tab is default
 */

// Include WordPress
require_once('../../../wp-load.php');

// Check if user is logged in
if (!is_user_logged_in()) {
    echo "Please log in to test the dashboard tabs.\n";
    exit;
}

// Get the user dashboard content
$shortcode_instance = new ENNU_User_Dashboard_Shortcode();
$dashboard_content = $shortcode_instance->render_dashboard();

// Check if My Biomarkers tab has the active class
if (strpos($dashboard_content, 'href="#tab-my-biomarkers" class="my-story-tab-active"') !== false) {
    echo "✅ SUCCESS: My Biomarkers tab has the active class in the navigation\n";
} else {
    echo "❌ FAILED: My Biomarkers tab does not have the active class in the navigation\n";
}

// Check if My Biomarkers content has the active class
if (strpos($dashboard_content, 'id="tab-my-biomarkers" class="my-story-tab-content my-story-tab-active"') !== false) {
    echo "✅ SUCCESS: My Biomarkers content has the active class\n";
} else {
    echo "❌ FAILED: My Biomarkers content does not have the active class\n";
}

// Check if My Assessments tab does NOT have the active class
if (strpos($dashboard_content, 'href="#tab-my-assessments" class="my-story-tab-active"') === false) {
    echo "✅ SUCCESS: My Assessments tab does not have the active class (as expected)\n";
} else {
    echo "❌ FAILED: My Assessments tab still has the active class\n";
}

// Check if My Assessments content does NOT have the active class
if (strpos($dashboard_content, 'id="tab-my-assessments" class="my-story-tab-content my-story-tab-active"') === false) {
    echo "✅ SUCCESS: My Assessments content does not have the active class (as expected)\n";
} else {
    echo "❌ FAILED: My Assessments content still has the active class\n";
}

echo "\nTest completed!\n";
?> 