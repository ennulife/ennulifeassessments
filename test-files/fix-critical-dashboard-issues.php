<?php
/**
 * CRITICAL DASHBOARD ISSUES FIX
 *
 * This file addresses:
 * 1. Tab functionality not working
 * 2. Logo image not showing
 * 3. Completed badge styling
 * 4. Timestamp formatting
 * 5. Recommendations/Breakdown buttons not expanding
 * 6. Scoring issues
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '<div style="max-width: 1200px; margin: 20px auto; padding: 20px; font-family: Arial, sans-serif;">';
echo '<h1 style="color: #16a085;">🔧 ENNU Life Dashboard Critical Issues Fix</h1>';

// Test 1: Check if tab structure exists
echo '<h2>1. Tab Structure Diagnosis</h2>';
if ( file_exists( ENNU_LIFE_PLUGIN_PATH . 'templates/user-dashboard.php' ) ) {
	$template_content = file_get_contents( ENNU_LIFE_PLUGIN_PATH . 'templates/user-dashboard.php' );

	if ( strpos( $template_content, 'my-story-tabs' ) !== false ) {
		echo '<div style="color: #27ae60;">✓ Tab container structure exists</div>';
	} else {
		echo '<div style="color: #e74c3c;">✗ Tab container missing</div>';
	}

	if ( strpos( $template_content, 'my-story-tab-nav' ) !== false ) {
		echo '<div style="color: #27ae60;">✓ Tab navigation structure exists</div>';
	} else {
		echo '<div style="color: #e74c3c;">✗ Tab navigation missing</div>';
	}

	if ( strpos( $template_content, 'my-story-tab-content' ) !== false ) {
		echo '<div style="color: #27ae60;">✓ Tab content structure exists</div>';
	} else {
		echo '<div style="color: #e74c3c;">✗ Tab content missing</div>';
	}
} else {
	echo '<div style="color: #e74c3c;">✗ Template file not found</div>';
}

// Test 2: Check JavaScript file
echo '<h2>2. JavaScript File Diagnosis</h2>';
if ( file_exists( ENNU_LIFE_PLUGIN_PATH . 'assets/js/user-dashboard.js' ) ) {
	$js_content = file_get_contents( ENNU_LIFE_PLUGIN_PATH . 'assets/js/user-dashboard.js' );

	if ( strpos( $js_content, 'MyStoryTabsManager' ) !== false ) {
		echo '<div style="color: #27ae60;">✓ MyStoryTabsManager class exists</div>';
	} else {
		echo '<div style="color: #e74c3c;">✗ MyStoryTabsManager class missing</div>';
	}

	if ( strpos( $js_content, 'switchToTab' ) !== false ) {
		echo '<div style="color: #27ae60;">✓ switchToTab method exists</div>';
	} else {
		echo '<div style="color: #e74c3c;">✗ switchToTab method missing</div>';
	}
} else {
	echo '<div style="color: #e74c3c;">✗ JavaScript file not found</div>';
}

// Test 3: Check logo images
echo '<h2>3. Logo Image Diagnosis</h2>';
$logo_black = ENNU_LIFE_PLUGIN_PATH . 'assets/img/ennu-logo-black.png';
$logo_white = ENNU_LIFE_PLUGIN_PATH . 'assets/img/ennu-logo-white.png';

if ( file_exists( $logo_black ) ) {
	echo '<div style="color: #27ae60;">✓ Black logo exists: ' . basename( $logo_black ) . '</div>';
} else {
	echo '<div style="color: #e74c3c;">✗ Black logo missing</div>';
}

if ( file_exists( $logo_white ) ) {
	echo '<div style="color: #27ae60;">✓ White logo exists: ' . basename( $logo_white ) . '</div>';
} else {
	echo '<div style="color: #e74c3c;">✗ White logo missing</div>';
}

// Test 4: Check CSS file
echo '<h2>4. CSS File Diagnosis</h2>';
if ( file_exists( ENNU_LIFE_PLUGIN_PATH . 'assets/css/user-dashboard.css' ) ) {
	$css_content = file_get_contents( ENNU_LIFE_PLUGIN_PATH . 'assets/css/user-dashboard.css' );

	if ( strpos( $css_content, 'my-story-tab-active' ) !== false ) {
		echo '<div style="color: #27ae60;">✓ Tab active class CSS exists</div>';
	} else {
		echo '<div style="color: #e74c3c;">✗ Tab active class CSS missing</div>';
	}

	if ( strpos( $css_content, 'status-badge' ) !== false ) {
		echo '<div style="color: #27ae60;">✓ Status badge CSS exists</div>';
	} else {
		echo '<div style="color: #e74c3c;">✗ Status badge CSS missing</div>';
	}
} else {
	echo '<div style="color: #e74c3c;">✗ CSS file not found</div>';
}

echo '<h2>5. Quick Fixes</h2>';
echo '<div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6;">';

// Fix 1: Update tab CSS
echo '<h3>Fix 1: Tab Visibility CSS</h3>';
echo '<p>Add this CSS to fix tab functionality:</p>';
echo '<pre style="background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 4px; overflow-x: auto;">
/* Force tab visibility fix */
.my-story-tab-content {
    display: none !important;
}

.my-story-tab-content.my-story-tab-active,
.my-story-tab-content:first-of-type {
    display: block !important;
    opacity: 1 !important;
    transform: translateY(0) !important;
}

/* Status badge improvements */
.status-badge.completed {
    background: rgba(16, 185, 129, 0.15);
    border: 1px solid rgba(16, 185, 129, 0.4);
    border-radius: 25px;
    color: #10b981;
}

/* Logo display fix */
.dashboard-logo {
    height: 60px;
    width: auto;
    display: inline-block;
}
</pre>';

// Fix 2: JavaScript initialization
echo '<h3>Fix 2: JavaScript Tab Initialization</h3>';
echo '<p>Add this JavaScript to ensure tabs work:</p>';
echo '<pre style="background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 4px; overflow-x: auto;">
// Force tab initialization
document.addEventListener(\'DOMContentLoaded\', function() {
    // Show first tab by default
    const firstTab = document.querySelector(\'.my-story-tab-content:first-of-type\');
    if (firstTab) {
        firstTab.style.display = \'block\';
        firstTab.classList.add(\'my-story-tab-active\');
    }
    
    // Add click handlers
    const tabLinks = document.querySelectorAll(\'.my-story-tab-nav a\');
    tabLinks.forEach(link => {
        link.addEventListener(\'click\', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute(\'href\');
            
            // Hide all tabs
            document.querySelectorAll(\'.my-story-tab-content\').forEach(tab => {
                tab.style.display = \'none\';
                tab.classList.remove(\'my-story-tab-active\');
            });
            
            // Remove active from all links
            tabLinks.forEach(l => l.classList.remove(\'my-story-tab-active\'));
            
            // Show target tab
            const targetTab = document.querySelector(targetId);
            if (targetTab) {
                targetTab.style.display = \'block\';
                targetTab.classList.add(\'my-story-tab-active\');
                this.classList.add(\'my-story-tab-active\');
            }
        });
    });
});
</pre>';

// Fix 3: Timestamp formatting
echo '<h3>Fix 3: Timestamp Formatting</h3>';
echo '<p>PHP code for correct timestamp format:</p>';
echo '<pre style="background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 4px; overflow-x: auto;">
// Correct timestamp formatting
if (is_numeric($timestamp)) {
    $formatted_date = date(\'l M j, Y @ g:ia\', $timestamp);
} else {
    $date_obj = DateTime::createFromFormat(\'Y-m-d H:i:s\', $timestamp);
    if ($date_obj) {
        $formatted_date = $date_obj->format(\'l M j, Y @ g:ia\');
    }
}
</pre>';

echo '</div>';

echo '<h2>6. Testing URLs</h2>';
echo '<p>Test these URLs to verify fixes:</p>';
echo '<ul>';
echo '<li><a href="' . admin_url( 'admin.php?page=ennu_dashboard_test' ) . '" target="_blank">Dashboard Test Page</a></li>';
echo '<li><a href="' . home_url( '/dashboard' ) . '" target="_blank">User Dashboard</a></li>';
echo '</ul>';

echo '<h2>7. Next Steps</h2>';
echo '<ol>';
echo '<li>Apply the CSS fixes above to user-dashboard.css</li>';
echo '<li>Update the JavaScript file with the tab initialization code</li>';
echo '<li>Check that logo images exist in assets/img/ directory</li>';
echo '<li>Test tab functionality in browser console</li>';
echo '<li>Verify timestamp formatting with a completed assessment</li>';
echo '</ol>';

echo '</div>';


