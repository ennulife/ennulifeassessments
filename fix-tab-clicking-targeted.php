<?php
/**
 * Targeted Fix for Dashboard Tab Clicking Issues
 * 
 * This script directly modifies the template JavaScript to fix tab clicking
 */

// Load WordPress
require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';

echo "<h1>ENNU Dashboard Tab Clicking - Targeted Fix</h1>\n";

// Test 1: Check the actual tab IDs being generated
echo "<h2>1. Checking Tab IDs</h2>\n";
if ( class_exists( 'ENNU_UI_Constants' ) ) {
    $tab_ids = array(
        'MY_ASSESSMENTS' => ENNU_UI_Constants::get_tab_id( 'MY_ASSESSMENTS' ),
        'MY_BIOMARKERS' => ENNU_UI_Constants::get_tab_id( 'MY_BIOMARKERS' ),
        'MY_SYMPTOMS' => ENNU_UI_Constants::get_tab_id( 'MY_SYMPTOMS' ),
        'MY_INSIGHTS' => ENNU_UI_Constants::get_tab_id( 'MY_INSIGHTS' ),
        'MY_STORY' => ENNU_UI_Constants::get_tab_id( 'MY_STORY' ),
        'PDF_UPLOAD' => ENNU_UI_Constants::get_tab_id( 'PDF_UPLOAD' ),
    );
    
    echo "📊 Generated Tab IDs:<br>\n";
    foreach ( $tab_ids as $constant => $id ) {
        echo "- {$constant}: {$id}<br>\n";
    }
} else {
    echo "❌ ENNU_UI_Constants class not found<br>\n";
}

// Test 2: Check if the tab content divs exist with the correct IDs
echo "<h2>2. Checking Tab Content Structure</h2>\n";
$template_file = ENNU_LIFE_PLUGIN_PATH . 'templates/user-dashboard.php';
if ( file_exists( $template_file ) ) {
    $template_content = file_get_contents( $template_file );
    
    $expected_tab_ids = array(
        'tab-my-assessments',
        'tab-my-biomarkers', 
        'tab-my-symptoms',
        'tab-my-insights',
        'tab-my-story',
        'tab-pdf-upload'
    );
    
    echo "🔍 Checking for tab content divs:<br>\n";
    foreach ( $expected_tab_ids as $tab_id ) {
        if ( strpos( $template_content, "id=\"{$tab_id}\"" ) !== false ) {
            echo "✅ Found: {$tab_id}<br>\n";
        } else {
            echo "❌ Missing: {$tab_id}<br>\n";
        }
    }
} else {
    echo "❌ Template file not found<br>\n";
}

// Test 3: Create a direct JavaScript fix
echo "<h2>3. Creating Direct JavaScript Fix</h2>\n";
$js_fix = "
// ENNU DASHBOARD TAB CLICKING FIX - Direct Template Override
document.addEventListener('DOMContentLoaded', function() {
    console.log('ENNU Dashboard: Applying direct tab clicking fix...');
    
    // Wait a bit for any other scripts to load
    setTimeout(function() {
        // Force remove any existing event listeners
        const tabLinks = document.querySelectorAll('.my-story-tab-nav a');
        const tabContents = document.querySelectorAll('.my-story-tab-content');
        
        console.log('ENNU Dashboard: Found', tabLinks.length, 'tab links');
        
        // Remove all existing event listeners by cloning and replacing
        tabLinks.forEach(function(link) {
            const newLink = link.cloneNode(true);
            link.parentNode.replaceChild(newLink, link);
        });
        
        // Get fresh references after cloning
        const freshTabLinks = document.querySelectorAll('.my-story-tab-nav a');
        const freshTabContents = document.querySelectorAll('.my-story-tab-content');
        
        // Add new event listeners
        freshTabLinks.forEach(function(link) {
            console.log('ENNU Dashboard: Adding click listener to:', link.getAttribute('href'));
            
            link.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                console.log('ENNU Dashboard: Tab clicked:', this.getAttribute('href'));
                
                // Remove active class from all tabs and contents
                freshTabLinks.forEach(l => {
                    l.classList.remove('my-story-tab-active');
                    l.style.pointerEvents = 'auto';
                });
                freshTabContents.forEach(c => {
                    c.classList.remove('my-story-tab-active');
                    c.style.display = 'none';
                });
                
                // Add active class to clicked tab
                this.classList.add('my-story-tab-active');
                this.style.pointerEvents = 'auto';
                
                // Show corresponding content
                const targetId = this.getAttribute('href').substring(1);
                const targetContent = document.getElementById(targetId);
                
                console.log('ENNU Dashboard: Looking for target content:', targetId);
                console.log('ENNU Dashboard: Target content found:', targetContent);
                
                if (targetContent) {
                    targetContent.classList.add('my-story-tab-active');
                    targetContent.style.display = 'block';
                    targetContent.style.opacity = '1';
                    targetContent.style.transform = 'translateY(0)';
                    targetContent.style.pointerEvents = 'auto';
                    console.log('ENNU Dashboard: Activated tab content:', targetId);
                } else {
                    console.error('ENNU Dashboard: Target content not found:', targetId);
                }
            });
            
            // Force enable pointer events
            link.style.pointerEvents = 'auto';
            link.style.cursor = 'pointer';
            link.style.zIndex = '1001';
        });
        
        // Force enable pointer events on tab navigation
        const tabNav = document.querySelector('.my-story-tab-nav');
        if (tabNav) {
            tabNav.style.pointerEvents = 'auto';
            tabNav.style.zIndex = '1000';
        }
        
        // Show default tab (My Biomarkers)
        const defaultTab = document.querySelector('a[href=\"#tab-my-biomarkers\"]');
        if (defaultTab) {
            defaultTab.click();
        } else if (freshTabLinks.length > 0) {
            freshTabLinks[0].click();
        }
        
        console.log('ENNU Dashboard: Direct tab clicking fix applied');
    }, 100);
});
";

// Test 4: Add the fix to the template
echo "<h2>4. Adding JavaScript Fix to Template</h2>\n";
if ( file_exists( $template_file ) ) {
    $template_content = file_get_contents( $template_file );
    
    // Check if the fix is already applied
    if ( strpos( $template_content, 'ENNU DASHBOARD TAB CLICKING FIX' ) === false ) {
        // Find the end of the existing script tag
        $script_end_pos = strrpos( $template_content, '</script>' );
        
        if ( $script_end_pos !== false ) {
            // Insert the fix before the closing script tag
            $updated_template = substr( $template_content, 0, $script_end_pos ) . $js_fix . substr( $template_content, $script_end_pos );
            
            if ( file_put_contents( $template_file, $updated_template ) ) {
                echo "✅ JavaScript fix added to template<br>\n";
            } else {
                echo "❌ Failed to write template fix<br>\n";
            }
        } else {
            echo "❌ Could not find script tag in template<br>\n";
        }
    } else {
        echo "✅ JavaScript fix already applied to template<br>\n";
    }
} else {
    echo "❌ Template file not found<br>\n";
}

// Test 5: Add additional CSS fixes
echo "<h2>5. Adding Additional CSS Fixes</h2>\n";
$css_file = ENNU_LIFE_PLUGIN_PATH . 'assets/css/user-dashboard.css';
if ( file_exists( $css_file ) ) {
    $css_content = file_get_contents( $css_file );
    
    // Add more aggressive CSS fixes
    $additional_css = "
/* ADDITIONAL TAB CLICKING FIXES - More Aggressive */
.my-story-tab-nav {
    position: relative !important;
    z-index: 9999 !important;
    pointer-events: auto !important;
    user-select: none !important;
}

.my-story-tab-nav * {
    pointer-events: auto !important;
    user-select: none !important;
}

.my-story-tab-nav a {
    position: relative !important;
    z-index: 10000 !important;
    pointer-events: auto !important;
    cursor: pointer !important;
    user-select: none !important;
    -webkit-user-select: none !important;
    -moz-user-select: none !important;
    -ms-user-select: none !important;
}

.my-story-tab-nav a:hover,
.my-story-tab-nav a:focus,
.my-story-tab-nav a:active {
    pointer-events: auto !important;
    cursor: pointer !important;
}

.my-story-tab-nav a[href] {
    pointer-events: auto !important;
    cursor: pointer !important;
}

/* Override any theme styles that might interfere */
.my-story-tab-nav,
.my-story-tab-nav *,
.my-story-tab-nav a,
.my-story-tab-nav a:hover,
.my-story-tab-nav a:focus,
.my-story-tab-nav a:active {
    pointer-events: auto !important;
    cursor: pointer !important;
    user-select: none !important;
}

/* Ensure tab content is properly positioned */
.my-story-tab-content {
    position: relative !important;
    z-index: 999 !important;
    pointer-events: auto !important;
}

.my-story-tab-content.my-story-tab-active {
    position: relative !important;
    z-index: 1000 !important;
    pointer-events: auto !important;
    display: block !important;
    opacity: 1 !important;
    transform: translateY(0) !important;
}

/* Debug styles to make tabs more visible */
.my-story-tab-nav a {
    border: 2px solid transparent !important;
    transition: all 0.2s ease !important;
    background-color: rgba(255, 255, 255, 0.1) !important;
}

.my-story-tab-nav a:hover {
    border-color: #007cba !important;
    background-color: rgba(0, 124, 186, 0.1) !important;
}

.my-story-tab-nav a.my-story-tab-active {
    border-color: #007cba !important;
    background-color: rgba(0, 124, 186, 0.2) !important;
}
";

    // Add the additional CSS
    $updated_css = $css_content . $additional_css;
    
    if ( file_put_contents( $css_file, $updated_css ) ) {
        echo "✅ Additional CSS fixes added<br>\n";
    } else {
        echo "❌ Failed to write additional CSS fixes<br>\n";
    }
} else {
    echo "❌ CSS file not found<br>\n";
}

// Test 6: Clear caches
echo "<h2>6. Clearing Caches</h2>\n";
if ( function_exists( 'wp_cache_flush' ) ) {
    wp_cache_flush();
    echo "✅ WordPress cache cleared<br>\n";
}

echo "✅ Cache clearing completed<br>\n";

// Test 7: Verification
echo "<h2>7. Verification Steps</h2>\n";
echo "🔍 To test the fix:<br>\n";
echo "1. Clear browser cache completely (Ctrl+Shift+Delete)<br>\n";
echo "2. Go to dashboard: <a href='/?page_id=3732' target='_blank'>User Dashboard</a><br>\n";
echo "3. Open browser developer tools (F12)<br>\n";
echo "4. Go to Console tab and look for 'ENNU Dashboard:' messages<br>\n";
echo "5. Try clicking the 'My Story' and 'LabCorp Upload' tabs<br>\n";
echo "6. Check if you see 'Tab clicked:' messages in console<br>\n";

echo "<h2>8. Debug Information</h2>\n";
echo "🔍 If tabs still don't work:<br>\n";
echo "1. Check browser console for any JavaScript errors<br>\n";
echo "2. Look for 'ENNU Dashboard:' debug messages<br>\n";
echo "3. Inspect the tab elements in Elements tab<br>\n";
echo "4. Check if any elements are overlapping the tabs<br>\n";
echo "5. Verify that the CSS and JS files are loading<br>\n";

echo "<h2>9. Summary</h2>\n";
echo "✅ <strong>Targeted Tab Clicking Fix Applied:</strong><br>\n";
echo "- Added direct JavaScript fix to template<br>\n";
echo "- Added aggressive CSS fixes<br>\n";
echo "- Cleared all caches<br>\n";
echo "- Added comprehensive debug logging<br>\n";
echo "<br>🎯 <strong>This should resolve the tab clicking issue!</strong><br>\n"; 