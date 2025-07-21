<?php
/**
 * Test page for debugging tab functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
}

// Load WordPress
require_once(ABSPATH . 'wp-load.php');

// Ensure user is logged in
if (!is_user_logged_in()) {
    wp_die('Please log in to view this page.');
}

// Enqueue required styles and scripts
wp_enqueue_style('ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/css/user-dashboard.css', array(), ENNU_LIFE_VERSION);
wp_enqueue_script('ennu-user-dashboard', ENNU_LIFE_PLUGIN_URL . 'assets/js/user-dashboard.js', array('jquery'), ENNU_LIFE_VERSION, true);

get_header();
?>

<div class="ennu-user-dashboard">
    <div class="dashboard-main-content">
        <h1>Tab Debug Test</h1>
        
        <!-- My Story Tabbed Section -->
        <div class="my-story-section">
            <div class="scores-title-container">
                <h2 class="scores-title">MY STORY</h2>
            </div>
            
            <div class="my-story-tabs">
                <nav class="my-story-tab-nav">
                    <ul>
                        <li><a href="#tab-my-assessments" class="my-story-tab-active">My Assessments</a></li>
                        <li><a href="#tab-my-symptoms">My Symptoms</a></li>
                        <li><a href="#tab-my-biomarkers">My Biomarkers</a></li>
                        <li><a href="#tab-my-trends">My Trends</a></li>
                        <li><a href="#tab-my-profile">My Profile</a></li>
                        <li><a href="#tab-my-new-life">My New Life</a></li>
                    </ul>
                </nav>
                
                <!-- Tab 1: My Assessments -->
                <div id="tab-my-assessments" class="my-story-tab-content my-story-tab-active">
                    <h3>My Assessments</h3>
                    <p>This is the assessments tab content.</p>
                </div>
                
                <!-- Tab 2: My Symptoms -->
                <div id="tab-my-symptoms" class="my-story-tab-content">
                    <h3>My Symptoms</h3>
                    <p>This is the symptoms tab content.</p>
                </div>
                
                <!-- Tab 3: My Biomarkers -->
                <div id="tab-my-biomarkers" class="my-story-tab-content">
                    <h3>My Biomarkers</h3>
                    <p>This is the biomarkers tab content.</p>
                </div>
                
                <!-- Tab 4: My Trends -->
                <div id="tab-my-trends" class="my-story-tab-content">
                    <h3>My Trends</h3>
                    <p>This is the trends tab content.</p>
                </div>
                
                <!-- Tab 5: My Profile -->
                <div id="tab-my-profile" class="my-story-tab-content">
                    <h3>My Profile</h3>
                    <p>This is the profile tab content.</p>
                </div>
                
                <!-- Tab 6: My New Life -->
                <div id="tab-my-new-life" class="my-story-tab-content">
                    <h3>My New Life</h3>
                    <p>This is the new life tab content.</p>
                </div>
            </div>
        </div>
        
        <div style="margin-top: 50px; padding: 20px; background: #f0f0f0;">
            <h4>Debug Information:</h4>
            <p><strong>CSS Loaded:</strong> <span id="css-status">Checking...</span></p>
            <p><strong>JS Loaded:</strong> <span id="js-status">Checking...</span></p>
            <p><strong>Tab Elements Found:</strong> <span id="tab-status">Checking...</span></p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if CSS is loaded
    const cssLoaded = document.querySelector('.my-story-tabs') && 
                     getComputedStyle(document.querySelector('.my-story-tabs')).display !== 'none';
    document.getElementById('css-status').textContent = cssLoaded ? 'Yes' : 'No';
    
    // Check if JS is loaded
    const jsLoaded = typeof MyStoryTabsManager !== 'undefined';
    document.getElementById('js-status').textContent = jsLoaded ? 'Yes' : 'No';
    
    // Check tab elements
    const tabLinks = document.querySelectorAll('.my-story-tab-nav a');
    const tabContents = document.querySelectorAll('.my-story-tab-content');
    document.getElementById('tab-status').textContent = 
        `Links: ${tabLinks.length}, Contents: ${tabContents.length}`;
    
    // Test tab clicking
    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            console.log('Tab clicked:', this.getAttribute('href'));
        });
    });
});
</script>

<?php
get_footer();
?> 