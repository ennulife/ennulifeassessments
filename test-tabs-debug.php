<?php
/**
 * ENNU Life Tabs Debug Test
 * 
 * This script tests tab functionality and identifies JavaScript issues
 * 
 * @package ENNU_Life
 * @version 57.2.5
 */

// Load WordPress
require_once('../../../wp-load.php');

// Check permissions
if (!current_user_can('manage_options')) {
    wp_die('Insufficient permissions to run this test.');
}

echo '<!DOCTYPE html>';
echo '<html><head><title>ENNU Tabs Debug Test</title>';
echo '<style>';
echo 'body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; }';
echo '.success { color: green; }';
echo '.error { color: red; }';
echo '.warning { color: orange; }';
echo '.section { background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 5px; }';
echo '.test-tabs { background: #fff; border: 1px solid #ccc; margin: 20px 0; }';
echo '.tab-nav { background: #f0f0f0; border-bottom: 1px solid #ccc; }';
echo '.tab-nav ul { margin: 0; padding: 0; list-style: none; display: flex; }';
echo '.tab-nav li { margin: 0; }';
echo '.tab-nav a { display: block; padding: 10px 20px; text-decoration: none; color: #333; border-right: 1px solid #ccc; }';
echo '.tab-nav a.active { background: #fff; border-bottom: 2px solid #0073aa; }';
echo '.tab-content { display: none; padding: 20px; }';
echo '.tab-content.active { display: block; }';
echo '</style>';
echo '</head><body>';

echo '<h1>ENNU Life Tabs Debug Test</h1>';

// Test 1: Check if assets are enqueued
echo '<div class="section">';
echo '<h2>1. Asset Loading Check</h2>';

// Enqueue the required assets
wp_enqueue_style('ennu-admin-tabs-enhanced', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-tabs-enhanced.css', array(), ENNU_LIFE_VERSION);
wp_enqueue_script('ennu-admin-enhanced', ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-admin-enhanced.js', array('jquery'), ENNU_LIFE_VERSION, true);

wp_localize_script('ennu-admin-enhanced', 'ennuAdmin', array( 
    'nonce' => wp_create_nonce('ennu_admin_nonce'),
    'ajax_url' => admin_url('admin-ajax.php'),
    'confirm_msg' => 'Are you sure?',
    'plugin_url' => ENNU_LIFE_PLUGIN_URL,
    'debug' => true
));

echo '<p class="success">✅ Assets enqueued successfully</p>';
echo '</div>';

// Test 2: Create a simple tab structure
echo '<div class="section">';
echo '<h2>2. Tab Structure Test</h2>';

echo '<div class="ennu-admin-tabs test-tabs">';
echo '<nav class="ennu-admin-tab-nav tab-nav">';
echo '<ul>';
echo '<li><a href="#tab-1" class="ennu-admin-tab-active active">Tab 1</a></li>';
echo '<li><a href="#tab-2">Tab 2</a></li>';
echo '<li><a href="#tab-3">Tab 3</a></li>';
echo '</ul>';
echo '</nav>';

echo '<div id="tab-1" class="ennu-admin-tab-content tab-content active">';
echo '<h3>Tab 1 Content</h3>';
echo '<p>This is the content for tab 1. If tabs are working, you should be able to click the other tabs to see their content.</p>';
echo '</div>';

echo '<div id="tab-2" class="ennu-admin-tab-content tab-content">';
echo '<h3>Tab 2 Content</h3>';
echo '<p>This is the content for tab 2. This should be hidden by default and show when you click Tab 2.</p>';
echo '</div>';

echo '<div id="tab-3" class="ennu-admin-tab-content tab-content">';
echo '<h3>Tab 3 Content</h3>';
echo '<p>This is the content for tab 3. This should be hidden by default and show when you click Tab 3.</p>';
echo '</div>';
echo '</div>';

echo '</div>';

// Test 3: JavaScript debugging
echo '<div class="section">';
echo '<h2>3. JavaScript Debugging</h2>';
echo '<p>Check the browser console (F12 → Console) for debug messages.</p>';
echo '<p>Expected console messages:</p>';
echo '<ul>';
echo '<li>"ENNU Admin Enhanced: Script loaded"</li>';
echo '<li>"ENNU Admin Enhanced: Initializing..."</li>';
echo '<li>"ENNU Admin Enhanced: Looking for tab containers..."</li>';
echo '<li>"ENNU Admin Enhanced: Found X tab container(s)"</li>';
echo '<li>"ENNU Admin Enhanced: Found X tab links and X tab contents"</li>';
echo '</ul>';
echo '</div>';

// Test 4: Manual tab test
echo '<div class="section">';
echo '<h2>4. Manual Tab Test</h2>';
echo '<p>Try clicking the tabs above. If they don\'t work, try this manual test:</p>';

echo '<div class="test-tabs">';
echo '<nav class="tab-nav">';
echo '<ul>';
echo '<li><a href="#manual-tab-1" class="active" onclick="switchTab(event, this, \'manual-tab-1\')">Manual Tab 1</a></li>';
echo '<li><a href="#manual-tab-2" onclick="switchTab(event, this, \'manual-tab-2\')">Manual Tab 2</a></li>';
echo '<li><a href="#manual-tab-3" onclick="switchTab(event, this, \'manual-tab-3\')">Manual Tab 3</a></li>';
echo '</ul>';
echo '</nav>';

echo '<div id="manual-tab-1" class="tab-content active">';
echo '<h3>Manual Tab 1</h3>';
echo '<p>This tab uses simple JavaScript. If this works but the ENNU tabs don\'t, there\'s a conflict.</p>';
echo '</div>';

echo '<div id="manual-tab-2" class="tab-content">';
echo '<h3>Manual Tab 2</h3>';
echo '<p>This is manual tab 2 content.</p>';
echo '</div>';

echo '<div id="manual-tab-3" class="tab-content">';
echo '<h3>Manual Tab 3</h3>';
echo '<p>This is manual tab 3 content.</p>';
echo '</div>';
echo '</div>';

echo '</div>';

// Test 5: Instructions
echo '<div class="section">';
echo '<h2>5. Troubleshooting Steps</h2>';
echo '<ol>';
echo '<li><strong>Check Console:</strong> Press F12, go to Console tab, look for any red error messages</li>';
echo '<li><strong>Check Network:</strong> In F12 → Network tab, verify the JS and CSS files are loading</li>';
echo '<li><strong>Test Manual Tabs:</strong> Try the manual tabs above - if they work, the issue is with ENNU tabs</li>';
echo '<li><strong>Check jQuery:</strong> In console, type "jQuery" - should return a function, not "undefined"</li>';
echo '<li><strong>Check ENNU Admin:</strong> In console, type "ennuAdmin" - should return an object</li>';
echo '</ol>';
echo '</div>';

// Simple manual tab function
echo '<script>
function switchTab(event, element, tabId) {
    event.preventDefault();
    
    // Remove active class from all tabs and contents
    document.querySelectorAll(".tab-nav a").forEach(function(link) {
        link.classList.remove("active");
    });
    document.querySelectorAll(".tab-content").forEach(function(content) {
        content.classList.remove("active");
    });
    
    // Add active class to clicked tab and show content
    element.classList.add("active");
    document.getElementById(tabId).classList.add("active");
    
    console.log("Manual tab switched to:", tabId);
}

// Force ENNU tab initialization
document.addEventListener("DOMContentLoaded", function() {
    console.log("DOM loaded, checking for ENNU tabs...");
    
    if (typeof window.initializeEnnuAdmin === "function") {
        console.log("ENNU initializeEnnuAdmin function found, calling it...");
        window.initializeEnnuAdmin();
    } else {
        console.error("ENNU initializeEnnuAdmin function NOT found!");
    }
    
    // Check for tab elements
    const tabContainers = document.querySelectorAll(".ennu-admin-tabs");
    console.log("Found", tabContainers.length, "ENNU tab containers");
    
    tabContainers.forEach(function(container, index) {
        const tabLinks = container.querySelectorAll(".ennu-admin-tab-nav a");
        const tabContents = container.querySelectorAll(".ennu-admin-tab-content");
        console.log("Container", index + 1, "has", tabLinks.length, "links and", tabContents.length, "contents");
    });
});

// Check jQuery
if (typeof jQuery !== "undefined") {
    console.log("jQuery is available:", jQuery.fn.jquery);
} else {
    console.error("jQuery is NOT available!");
}

// Check ENNU Admin object
if (typeof ennuAdmin !== "undefined") {
    console.log("ENNU Admin object is available:", ennuAdmin);
} else {
    console.error("ENNU Admin object is NOT available!");
}
</script>';

echo '</body></html>';
?> 