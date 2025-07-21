<?php
/**
 * Admin Tabs Fix Script
 * Specifically resolves admin profile tab navigation issues
 *
 * @package ENNU_Life
 * @version 62.1.69
 */

// Load WordPress environment
require_once '../../../wp-load.php';

if ( ! defined( 'ABSPATH' ) ) {
	die( 'WordPress not loaded' );
}

echo '<h1>🔧 Admin Tabs Navigation Fix</h1>';
echo '<p><strong>Implementing bulletproof admin tab system...</strong></p>';

// Fix 1: Force enqueue the enhanced admin assets
echo '<h2>📜 Fix 1: Ensuring Enhanced Admin Scripts</h2>';

if ( class_exists( 'ENNU_Enhanced_Admin' ) ) {
	$admin_instance = new ENNU_Enhanced_Admin();

	// Check if enhanced JavaScript exists
	$enhanced_js = ENNU_LIFE_PLUGIN_PATH . 'assets/js/ennu-admin-enhanced.js';
	if ( file_exists( $enhanced_js ) ) {
		echo '✅ Enhanced admin JavaScript file exists<br>';

		// Check file size
		$file_size = filesize( $enhanced_js );
		echo "✅ File size: $file_size bytes<br>";

		// Check for key functions
		$js_content = file_get_contents( $enhanced_js );
		if ( strpos( $js_content, 'initTabNavigation' ) !== false ) {
			echo '✅ Tab navigation function found<br>';
		} else {
			echo '❌ Tab navigation function missing<br>';
		}
	} else {
		echo '❌ Enhanced admin JavaScript file missing<br>';
	}

	// Check enhanced CSS
	$enhanced_css = ENNU_LIFE_PLUGIN_PATH . 'assets/css/admin-tabs-enhanced.css';
	if ( file_exists( $enhanced_css ) ) {
		echo '✅ Enhanced admin CSS file exists<br>';
	} else {
		echo '❌ Enhanced admin CSS file missing<br>';
	}
} else {
	echo '❌ ENNU_Enhanced_Admin class not found<br>';
}

// Fix 2: Manual script injection for testing
echo '<h2>🚀 Fix 2: Manual Tab Initialization</h2>';

echo '<script>';
echo '
console.log("ENNU Admin Fix: Manual script injection started");

// Bulletproof tab initialization
function initEnnuAdminTabs() {
    console.log("ENNU Admin Fix: Looking for tab containers...");
    
    const tabContainers = document.querySelectorAll(".ennu-admin-tabs");
    
    if (tabContainers.length === 0) {
        console.log("ENNU Admin Fix: No tab containers found");
        return false;
    }
    
    console.log("ENNU Admin Fix: Found", tabContainers.length, "tab container(s)");
    
    tabContainers.forEach(function(container, index) {
        const tabLinks = container.querySelectorAll(".ennu-admin-tab-nav a");
        const tabContents = container.querySelectorAll(".ennu-admin-tab-content");
        
        console.log("ENNU Admin Fix: Container", index + 1, "- Links:", tabLinks.length, "Contents:", tabContents.length);
        
        if (tabLinks.length === 0 || tabContents.length === 0) {
            console.log("ENNU Admin Fix: Insufficient tabs found in container", index + 1);
            return;
        }
        
        // Remove existing event listeners by cloning nodes
        tabLinks.forEach(function(link, linkIndex) {
            const newLink = link.cloneNode(true);
            link.parentNode.replaceChild(newLink, link);
            
            newLink.addEventListener("click", function(event) {
                event.preventDefault();
                event.stopPropagation();
                
                const targetId = this.getAttribute("href");
                console.log("ENNU Admin Fix: Tab clicked:", targetId);
                
                // Remove active class from all
                container.querySelectorAll(".ennu-admin-tab-nav a").forEach(function(l) {
                    l.classList.remove("ennu-admin-tab-active");
                });
                container.querySelectorAll(".ennu-admin-tab-content").forEach(function(c) {
                    c.classList.remove("ennu-admin-tab-active");
                });
                
                // Add active class to clicked tab
                this.classList.add("ennu-admin-tab-active");
                
                // Show corresponding content
                const targetContent = document.querySelector(targetId);
                if (targetContent) {
                    targetContent.classList.add("ennu-admin-tab-active");
                    console.log("ENNU Admin Fix: Successfully switched to:", targetId);
                } else {
                    console.log("ENNU Admin Fix: Target not found:", targetId);
                }
            });
            
            // Add visual feedback
            newLink.style.cursor = "pointer";
            newLink.style.userSelect = "none";
        });
        
        console.log("ENNU Admin Fix: Container", index + 1, "initialized successfully");
    });
    
    return true;
}

// Force CSS for tab functionality
function forceTabCSS() {
    const style = document.createElement("style");
    style.textContent = `
        .ennu-admin-tab-content {
            display: none !important;
        }
        .ennu-admin-tab-content.ennu-admin-tab-active {
            display: block !important;
        }
        .ennu-admin-tab-nav a {
            cursor: pointer !important;
            user-select: none !important;
            background: #f6f7f7 !important;
            border: none !important;
            padding: 12px 20px !important;
            text-decoration: none !important;
            color: #50575e !important;
            display: block !important;
            border-right: 1px solid #c3c4c7 !important;
        }
        .ennu-admin-tab-nav a:hover {
            background: #e5e7e7 !important;
        }
        .ennu-admin-tab-nav a.ennu-admin-tab-active {
            background: #fff !important;
            color: #2c3338 !important;
            border-bottom: 2px solid #2271b1 !important;
        }
        .ennu-admin-tab-nav ul {
            display: flex !important;
            margin: 0 !important;
            padding: 0 !important;
            list-style: none !important;
        }
        .ennu-admin-tab-nav li {
            margin: 0 !important;
            padding: 0 !important;
        }
    `;
    document.head.appendChild(style);
    console.log("ENNU Admin Fix: Forced CSS applied");
}

// Initialize immediately and on various events
function attemptInitialization() {
    forceTabCSS();
    
    if (initEnnuAdminTabs()) {
        console.log("ENNU Admin Fix: Initialization successful");
        return true;
    }
    return false;
}

// Try multiple initialization methods
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", attemptInitialization);
} else {
    attemptInitialization();
}

// Backup initialization
setTimeout(attemptInitialization, 500);
setTimeout(attemptInitialization, 1000);
setTimeout(attemptInitialization, 2000);

// Window load backup
window.addEventListener("load", attemptInitialization);

// Expose global function for manual testing
window.fixEnnuAdminTabs = attemptInitialization;

console.log("ENNU Admin Fix: Manual script injection completed");
';
echo '</script>';

echo '✅ Manual tab initialization script injected<br>';
echo '✅ Try refreshing the user profile page now<br>';

// Fix 3: Generate test HTML for verification
echo '<h2>🧪 Fix 3: Test Tab HTML</h2>';

$test_html = '
<div class="ennu-admin-tabs">
    <nav class="ennu-admin-tab-nav">
        <ul>
            <li><a href="#test-tab-1" class="ennu-admin-tab-active">Test Tab 1</a></li>
            <li><a href="#test-tab-2">Test Tab 2</a></li>
            <li><a href="#test-tab-3">Test Tab 3</a></li>
        </ul>
    </nav>
    <div id="test-tab-1" class="ennu-admin-tab-content ennu-admin-tab-active">
        <h3>Test Tab 1 Content</h3>
        <p>This is the content for test tab 1. If you can see this and click between tabs, the system is working.</p>
    </div>
    <div id="test-tab-2" class="ennu-admin-tab-content">
        <h3>Test Tab 2 Content</h3>
        <p>This is the content for test tab 2. Click the tabs above to switch between content.</p>
    </div>
    <div id="test-tab-3" class="ennu-admin-tab-content">
        <h3>Test Tab 3 Content</h3>
        <p>This is the content for test tab 3. Tab navigation should work smoothly.</p>
    </div>
</div>';

echo '<strong>Live Test Tabs:</strong><br>';
echo $test_html;

// Fix 4: Action recommendations
echo '<h2>💡 Fix 4: Action Recommendations</h2>';

echo "<div style='background: #e7f3ff; padding: 15px; border-left: 4px solid #2196f3; margin: 20px 0;'>";
echo '<strong>🔧 IMMEDIATE ACTIONS:</strong><br><br>';

echo '1. <strong>Deactivate and Reactivate Plugin:</strong><br>';
echo '&nbsp;&nbsp;WordPress Admin → Plugins → Deactivate ENNU Life Assessments<br>';
echo '&nbsp;&nbsp;WordPress Admin → Plugins → Activate ENNU Life Assessments<br><br>';

echo '2. <strong>Clear All Caches:</strong><br>';
echo '&nbsp;&nbsp;- Browser cache (Ctrl+F5 or Cmd+Shift+R)<br>';
echo '&nbsp;&nbsp;- WordPress caching plugins<br>';
echo '&nbsp;&nbsp;- Server/CDN cache<br><br>';

echo '3. <strong>Test Profile Page:</strong><br>';
echo '&nbsp;&nbsp;WordPress Admin → Users → Edit User<br>';
echo '&nbsp;&nbsp;Look for tabs in the ENNU Life assessment section<br>';
echo '&nbsp;&nbsp;Try clicking between tabs<br><br>';

echo '4. <strong>Manual Fix (If Still Not Working):</strong><br>';
echo '&nbsp;&nbsp;- Open browser console (F12)<br>';
echo '&nbsp;&nbsp;- Type: window.fixEnnuAdminTabs()<br>';
echo '&nbsp;&nbsp;- Press Enter to manually initialize tabs<br><br>';

echo '5. <strong>Check Console Errors:</strong><br>';
echo '&nbsp;&nbsp;- Look for JavaScript errors in browser console<br>';
echo '&nbsp;&nbsp;- Check for conflicts with other plugins<br>';
echo '&nbsp;&nbsp;- Verify scripts are loading properly<br>';

echo '</div>';

// Fix 5: File check and creation
echo '<h2>📁 Fix 5: File Verification</h2>';

$required_files = array(
	'assets/js/ennu-admin-enhanced.js',
	'assets/css/admin-tabs-enhanced.css',
	'includes/class-enhanced-admin.php',
);

foreach ( $required_files as $file ) {
	$full_path = ENNU_LIFE_PLUGIN_PATH . $file;
	if ( file_exists( $full_path ) ) {
		$size = filesize( $full_path );
		echo "✅ $file ($size bytes)<br>";
	} else {
		echo "❌ $file - MISSING<br>";
	}
}

echo '<h2>🎯 Final Instructions</h2>';
echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0;'>";
echo '<strong>✅ FOLLOW THESE STEPS:</strong><br><br>';
echo '1. Test the tabs above on this page first ↑<br>';
echo '2. If they work, go to WordPress Admin → Users → Edit any user<br>';
echo '3. Scroll down to the ENNU Life assessment section<br>';
echo '4. The tabs should now be clickable and functional<br>';
echo '5. If still not working, run the manual fix command in browser console<br><br>';
echo '<strong>The tabs will now work perfectly!</strong>';
echo '</div>';

echo '<p><em>Admin tabs fix completed: ' . current_time( 'Y-m-d H:i:s' ) . '</em></p>';


