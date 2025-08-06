<?php
/**
 * Fix Dashboard Tab Clicking Issues
 * 
 * This script adds CSS and JavaScript fixes to ensure tabs are clickable
 */

// Load WordPress
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';

echo "<h1>ENNU Dashboard Tab Clicking Fix</h1>\n";

// Test 1: Add CSS fixes to user-dashboard.css
echo "<h2>1. Adding CSS Fixes</h2>\n";
$css_file = ENNU_LIFE_PLUGIN_PATH . 'assets/css/user-dashboard.css';

if ( file_exists( $css_file ) ) {
    $css_content = file_get_contents( $css_file );
    
    // Check if fixes are already applied
    if ( strpos( $css_content, '/* TAB CLICKING FIXES */' ) === false ) {
        // Add CSS fixes
        $css_fixes = "
/* TAB CLICKING FIXES - Added by fix-dashboard-tab-clicking.php */
.my-story-tab-nav {
    position: relative;
    z-index: 1000 !important;
    pointer-events: auto !important;
}

.my-story-tab-nav a {
    position: relative;
    z-index: 1001 !important;
    pointer-events: auto !important;
    cursor: pointer !important;
}

.my-story-tab-nav a:hover {
    pointer-events: auto !important;
}

.my-story-tab-nav a:focus {
    pointer-events: auto !important;
}

.my-story-tab-nav a:active {
    pointer-events: auto !important;
}

.my-story-tab-content {
    position: relative;
    z-index: 999 !important;
    pointer-events: auto !important;
}

.my-story-tab-content.my-story-tab-active {
    position: relative;
    z-index: 1000 !important;
    pointer-events: auto !important;
}

/* Ensure no overlays are blocking */
.my-story-tabs {
    position: relative;
    z-index: 999 !important;
    pointer-events: auto !important;
}

/* Fix for any potential theme conflicts */
.my-story-tab-nav * {
    pointer-events: auto !important;
}

/* Ensure tab links are always clickable */
.my-story-tab-nav a[href] {
    pointer-events: auto !important;
    cursor: pointer !important;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}

/* Debug styles to make tabs more visible */
.my-story-tab-nav a {
    border: 1px solid transparent;
    transition: all 0.2s ease;
}

.my-story-tab-nav a:hover {
    border-color: var(--accent-primary);
    background-color: rgba(var(--accent-primary-rgb), 0.1);
}

.my-story-tab-nav a.my-story-tab-active {
    border-color: var(--accent-primary);
    background-color: rgba(var(--accent-primary-rgb), 0.2);
}
";

        // Add fixes to the end of the CSS file
        $updated_css = $css_content . $css_fixes;
        
        if ( file_put_contents( $css_file, $updated_css ) ) {
            echo "✅ CSS fixes added to user-dashboard.css<br>\n";
        } else {
            echo "❌ Failed to write CSS fixes<br>\n";
        }
    } else {
        echo "✅ CSS fixes already applied<br>\n";
    }
} else {
    echo "❌ User dashboard CSS file not found<br>\n";
}

// Test 2: Add JavaScript fixes to user-dashboard.js
echo "<h2>2. Adding JavaScript Fixes</h2>\n";
$js_file = ENNU_LIFE_PLUGIN_PATH . 'assets/js/user-dashboard.js';

if ( file_exists( $js_file ) ) {
    $js_content = file_get_contents( $js_file );
    
    // Check if fixes are already applied
    if ( strpos( $js_content, '/* TAB CLICKING FIXES */' ) === false ) {
        // Add JavaScript fixes
        $js_fixes = "
// TAB CLICKING FIXES - Added by fix-dashboard-tab-clicking.php
document.addEventListener('DOMContentLoaded', function() {
    console.log('ENNU Dashboard: Applying tab clicking fixes...');
    
    // Force enable pointer events on tab navigation
    const tabNav = document.querySelector('.my-story-tab-nav');
    if (tabNav) {
        tabNav.style.pointerEvents = 'auto';
        tabNav.style.zIndex = '1000';
        console.log('ENNU Dashboard: Fixed tab navigation pointer events');
    }
    
    // Force enable pointer events on all tab links
    const tabLinks = document.querySelectorAll('.my-story-tab-nav a');
    tabLinks.forEach(function(link) {
        link.style.pointerEvents = 'auto';
        link.style.zIndex = '1001';
        link.style.cursor = 'pointer';
        console.log('ENNU Dashboard: Fixed tab link:', link.getAttribute('href'));
    });
    
    // Enhanced tab click handling
    tabLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('ENNU Dashboard: Tab clicked:', this.getAttribute('href'));
            
            // Remove active class from all tabs and contents
            document.querySelectorAll('.my-story-tab-nav a').forEach(l => l.classList.remove('my-story-tab-active'));
            document.querySelectorAll('.my-story-tab-content').forEach(c => c.classList.remove('my-story-tab-active'));
            
            // Add active class to clicked tab
            this.classList.add('my-story-tab-active');
            
            // Show corresponding content
            const targetId = this.getAttribute('href').substring(1);
            const targetContent = document.getElementById(targetId);
            
            if (targetContent) {
                targetContent.classList.add('my-story-tab-active');
                targetContent.style.display = 'block';
                targetContent.style.opacity = '1';
                targetContent.style.transform = 'translateY(0)';
                console.log('ENNU Dashboard: Activated tab content:', targetId);
            } else {
                console.error('ENNU Dashboard: Target content not found:', targetId);
            }
        });
    });
    
    // Debug: Log all tab elements
    console.log('ENNU Dashboard: Found', tabLinks.length, 'tab links');
    tabLinks.forEach(function(link, index) {
        console.log('ENNU Dashboard: Tab link', index + 1, 'href:', link.getAttribute('href'));
    });
    
    console.log('ENNU Dashboard: Tab clicking fixes applied');
});
";

        // Add fixes to the end of the JS file
        $updated_js = $js_content . $js_fixes;
        
        if ( file_put_contents( $js_file, $updated_js ) ) {
            echo "✅ JavaScript fixes added to user-dashboard.js<br>\n";
        } else {
            echo "❌ Failed to write JavaScript fixes<br>\n";
        }
    } else {
        echo "✅ JavaScript fixes already applied<br>\n";
    }
} else {
    echo "❌ User dashboard JS file not found<br>\n";
}

// Test 3: Clear any caches
echo "<h2>3. Clearing Caches</h2>\n";
if ( function_exists( 'wp_cache_flush' ) ) {
    wp_cache_flush();
    echo "✅ WordPress cache cleared<br>\n";
}

if ( function_exists( 'w3tc_flush_all' ) ) {
    w3tc_flush_all();
    echo "✅ W3 Total Cache cleared<br>\n";
}

if ( function_exists( 'wp_rocket_clean_domain' ) ) {
    wp_rocket_clean_domain();
    echo "✅ WP Rocket cache cleared<br>\n";
}

echo "✅ Cache clearing completed<br>\n";

// Test 4: Verify fixes
echo "<h2>4. Verification</h2>\n";
echo "🔍 To verify the fixes:<br>\n";
echo "1. Clear your browser cache (Ctrl+F5 or Cmd+Shift+R)<br>\n";
echo "2. Go to the dashboard: <a href='/?page_id=3732' target='_blank'>User Dashboard</a><br>\n";
echo "3. Try clicking the 'My Story' and 'LabCorp Upload' tabs<br>\n";
echo "4. Check browser console for 'ENNU Dashboard:' messages<br>\n";
echo "5. If tabs still don't work, check for JavaScript errors in console<br>\n";

echo "<h2>5. Additional Debugging</h2>\n";
echo "🔍 If tabs still don't work:<br>\n";
echo "1. Open browser developer tools (F12)<br>\n";
echo "2. Go to Console tab and look for errors<br>\n";
echo "3. Go to Elements tab and inspect the tab navigation<br>\n";
echo "4. Check if any elements are overlapping the tabs<br>\n";
echo "5. Verify that the CSS and JS files are loading properly<br>\n";

echo "<h2>6. Summary</h2>\n";
echo "✅ <strong>Tab Clicking Fixes Applied:</strong><br>\n";
echo "- Added CSS fixes to ensure tabs are clickable<br>\n";
echo "- Added JavaScript fixes to handle tab switching<br>\n";
echo "- Cleared caches to ensure changes take effect<br>\n";
echo "- Added debug logging to help troubleshoot<br>\n";
echo "<br>🎯 <strong>Next Steps:</strong><br>\n";
echo "1. Test the dashboard tabs<br>\n";
echo "2. Check browser console for any errors<br>\n";
echo "3. Report any remaining issues<br>\n"; 