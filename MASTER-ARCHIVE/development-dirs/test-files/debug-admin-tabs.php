<?php
/**
 * Admin Tabs Debug Script
 * Specifically diagnoses admin profile tab navigation issues
 *
 * @package ENNU_Life
 * @version 62.1.68
 */

// Load WordPress environment
require_once '../../../wp-load.php';

if ( ! defined( 'ABSPATH' ) ) {
	die( 'WordPress not loaded' );
}

echo '<h1>🔍 Admin Tabs Navigation Debug</h1>';
echo '<p><strong>Diagnosing the legendary admin tab system...</strong></p>';

// Test 1: Check if we're in admin context
echo '<h2>🏢 Admin Context Check</h2>';
if ( is_admin() ) {
	echo '✅ Running in WordPress admin context<br>';
} else {
	echo '❌ NOT in WordPress admin context<br>';
	echo '⚠️ This script should be run from WordPress admin area<br>';
}

// Test 2: Check if JavaScript file exists and content
echo '<h2>📜 JavaScript File Analysis</h2>';
$js_file = ENNU_LIFE_PLUGIN_PATH . 'assets/js/ennu-admin.js';
if ( file_exists( $js_file ) ) {
	$js_content = file_get_contents( $js_file );
	$file_size  = filesize( $js_file );
	echo "✅ ennu-admin.js exists ($file_size bytes)<br>";

	// Check for tab-related code
	if ( strpos( $js_content, 'ennu-admin-tab' ) !== false ) {
		echo '✅ Tab navigation code found in JavaScript<br>';
	} else {
		echo '❌ Tab navigation code NOT found in JavaScript<br>';
	}

	if ( strpos( $js_content, 'addEventListener' ) !== false ) {
		echo '✅ Event listeners found in JavaScript<br>';
	} else {
		echo '❌ Event listeners NOT found in JavaScript<br>';
	}

	// Check for DOMContentLoaded
	if ( strpos( $js_content, 'DOMContentLoaded' ) !== false ) {
		echo '✅ DOMContentLoaded listener found<br>';
	} else {
		echo '❌ DOMContentLoaded listener NOT found<br>';
	}
} else {
	echo "❌ ennu-admin.js NOT FOUND at: $js_file<br>";
}

// Test 3: Check CSS file
echo '<h2>🎨 CSS File Analysis</h2>';
$css_file = ENNU_LIFE_PLUGIN_PATH . 'assets/css/admin-scores-enhanced.css';
if ( file_exists( $css_file ) ) {
	$css_content = file_get_contents( $css_file );
	$file_size   = filesize( $css_file );
	echo "✅ admin-scores-enhanced.css exists ($file_size bytes)<br>";

	// Check for tab styles
	if ( strpos( $css_content, 'ennu-admin-tab-nav' ) !== false ) {
		echo '✅ Tab navigation CSS found<br>';
	} else {
		echo '❌ Tab navigation CSS NOT found<br>';
	}

	if ( strpos( $css_content, 'ennu-admin-tab-active' ) !== false ) {
		echo '✅ Active tab CSS found<br>';
	} else {
		echo '❌ Active tab CSS NOT found<br>';
	}
} else {
	echo "❌ admin-scores-enhanced.css NOT FOUND at: $css_file<br>";
}

// Test 4: Check Enhanced Admin class and methods
echo '<h2>🛠️ Enhanced Admin Class Analysis</h2>';
if ( class_exists( 'ENNU_Enhanced_Admin' ) ) {
	echo '✅ ENNU_Enhanced_Admin class exists<br>';

	$methods = get_class_methods( 'ENNU_Enhanced_Admin' );

	// Check for enqueue method
	if ( in_array( 'enqueue_admin_assets', $methods ) ) {
		echo '✅ enqueue_admin_assets method exists<br>';
	} else {
		echo '❌ enqueue_admin_assets method NOT found<br>';
	}

	// Check for profile method
	if ( in_array( 'add_user_profile_fields', $methods ) ) {
		echo '✅ add_user_profile_fields method exists<br>';
	} else {
		echo '❌ add_user_profile_fields method NOT found<br>';
	}
} else {
	echo '❌ ENNU_Enhanced_Admin class NOT found<br>';
}

// Test 5: Check WordPress hooks
echo '<h2>🪝 WordPress Hooks Analysis</h2>';

// Check if admin asset enqueue hook is registered
if ( has_action( 'admin_enqueue_scripts' ) ) {
	echo '✅ admin_enqueue_scripts hooks are registered<br>';

	// Get hook details
	global $wp_filter;
	if ( isset( $wp_filter['admin_enqueue_scripts'] ) ) {
		$hooks      = $wp_filter['admin_enqueue_scripts']->callbacks;
		$ennu_hooks = 0;
		foreach ( $hooks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( is_array( $callback['function'] ) &&
					is_object( $callback['function'][0] ) &&
					get_class( $callback['function'][0] ) === 'ENNU_Enhanced_Admin' ) {
					$ennu_hooks++;
				}
			}
		}
		echo "✅ Found $ennu_hooks ENNU admin hooks<br>";
	}
} else {
	echo '❌ No admin_enqueue_scripts hooks registered<br>';
}

// Check profile hooks
if ( has_action( 'show_user_profile' ) || has_action( 'edit_user_profile' ) ) {
	echo '✅ User profile hooks are registered<br>';
} else {
	echo '❌ User profile hooks NOT registered<br>';
}

// Test 6: Simulate tab HTML structure
echo '<h2>🏗️ Tab HTML Structure Test</h2>';

$test_html = '
<div class="ennu-admin-tabs">
    <nav class="ennu-admin-tab-nav">
        <ul>
            <li><a href="#tab-global-metrics" class="ennu-admin-tab-active">Global & Health Metrics</a></li>
            <li><a href="#tab-test" class="">Test Tab</a></li>
        </ul>
    </nav>
    <div id="tab-global-metrics" class="ennu-admin-tab-content ennu-admin-tab-active">
        <p>Global metrics content</p>
    </div>
    <div id="tab-test" class="ennu-admin-tab-content">
        <p>Test tab content</p>
    </div>
</div>';

echo '✅ Sample tab HTML structure:<br>';
echo '<pre>' . htmlspecialchars( $test_html ) . '</pre>';

// Test 7: Check current page context
echo '<h2>📍 Current Page Context</h2>';
global $pagenow;
echo '<strong>Current Page:</strong> ' . ( $pagenow ?? 'Unknown' ) . '<br>';

if ( isset( $_GET['user_id'] ) ) {
	echo '<strong>User ID:</strong> ' . sanitize_text_field( $_GET['user_id'] ) . '<br>';
} elseif ( isset( $_GET['user'] ) ) {
	echo '<strong>User:</strong> ' . sanitize_text_field( $_GET['user'] ) . '<br>';
} else {
	echo '⚠️ No user context detected<br>';
}

// Test 8: Generate working JavaScript code
echo '<h2>🔧 Working JavaScript Solution</h2>';
echo "<strong>If tabs aren't working, add this JavaScript to test:</strong><br>";
echo '<pre>';
echo htmlspecialchars(
	'
<script>
document.addEventListener("DOMContentLoaded", function() {
    console.log("ENNU Admin: DOM loaded, looking for tabs...");
    
    const tabContainer = document.querySelector(".ennu-admin-tabs");
    if (!tabContainer) {
        console.log("ENNU Admin: No tab container found");
        return;
    }
    
    const tabLinks = tabContainer.querySelectorAll(".ennu-admin-tab-nav a");
    const tabContents = tabContainer.querySelectorAll(".ennu-admin-tab-content");
    
    console.log("ENNU Admin: Found", tabLinks.length, "tab links and", tabContents.length, "tab contents");
    
    tabLinks.forEach(function(link, index) {
        link.addEventListener("click", function(event) {
            event.preventDefault();
            console.log("ENNU Admin: Tab clicked:", this.getAttribute("href"));
            
            const targetId = this.getAttribute("href");
            
            // Remove active class from all tabs and contents
            tabLinks.forEach(l => l.classList.remove("ennu-admin-tab-active"));
            tabContents.forEach(c => c.classList.remove("ennu-admin-tab-active"));
            
            // Add active class to clicked tab and corresponding content
            this.classList.add("ennu-admin-tab-active");
            const targetContent = document.querySelector(targetId);
            if (targetContent) {
                targetContent.classList.add("ennu-admin-tab-active");
                console.log("ENNU Admin: Successfully switched to tab:", targetId);
            } else {
                console.log("ENNU Admin: Target content not found:", targetId);
            }
        });
    });
    
    console.log("ENNU Admin: Tab navigation initialized successfully");
});
</script>
'
);
echo '</pre>';

// Test 9: Quick Fix Recommendations
echo '<h2>💡 Quick Fix Recommendations</h2>';
echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;'>";
echo '<strong>🔧 IMMEDIATE FIXES TO TRY:</strong><br><br>';

echo '1. <strong>Check Browser Console:</strong><br>';
echo '&nbsp;&nbsp;- Open browser developer tools (F12)<br>';
echo '&nbsp;&nbsp;- Look for JavaScript errors in Console tab<br>';
echo '&nbsp;&nbsp;- Check if ENNU admin script is loading<br><br>';

echo '2. <strong>Force Script Reload:</strong><br>';
echo '&nbsp;&nbsp;- Hard refresh page (Ctrl+F5 or Cmd+Shift+R)<br>';
echo '&nbsp;&nbsp;- Clear browser cache completely<br>';
echo '&nbsp;&nbsp;- Disable any ad blockers temporarily<br><br>';

echo '3. <strong>Plugin Conflicts:</strong><br>';
echo '&nbsp;&nbsp;- Temporarily deactivate other plugins<br>';
echo '&nbsp;&nbsp;- Test if tabs work with just ENNU plugin active<br>';
echo '&nbsp;&nbsp;- Check for jQuery conflicts<br><br>';

echo '4. <strong>WordPress Version:</strong><br>';
echo '&nbsp;&nbsp;- Ensure WordPress is up to date<br>';
echo '&nbsp;&nbsp;- Check if theme conflicts with admin scripts<br><br>';

echo '5. <strong>Manual Fix:</strong><br>';
echo '&nbsp;&nbsp;- Copy the JavaScript code above<br>';
echo '&nbsp;&nbsp;- Add it to browser console and run<br>';
echo '&nbsp;&nbsp;- This will manually initialize tab navigation<br>';

echo '</div>';

// Test 10: File Permission Check
echo '<h2>📁 File Permissions Check</h2>';
$files_to_check = array(
	'assets/js/ennu-admin.js',
	'assets/css/admin-scores-enhanced.css',
	'includes/class-enhanced-admin.php',
);

foreach ( $files_to_check as $file ) {
	$full_path = ENNU_LIFE_PLUGIN_PATH . $file;
	if ( file_exists( $full_path ) ) {
		$perms        = fileperms( $full_path );
		$perms_string = substr( sprintf( '%o', $perms ), -4 );
		echo "✅ $file - Permissions: $perms_string<br>";
	} else {
		echo "❌ $file - File not found<br>";
	}
}

echo '<p><em>Admin tabs diagnostic completed: ' . current_time( 'Y-m-d H:i:s' ) . '</em></p>';

// Test 11: Live Tab Test (if in admin context)
if ( is_admin() ) {
	echo '<h2>🎮 Live Tab Test</h2>';
	echo "<div id='live-tab-test'>";
	echo $test_html;
	echo '</div>';

	echo '<script>';
	echo 'document.addEventListener("DOMContentLoaded", function() {
        const testContainer = document.querySelector("#live-tab-test .ennu-admin-tabs");
        if (testContainer) {
            const testLinks = testContainer.querySelectorAll(".ennu-admin-tab-nav a");
            const testContents = testContainer.querySelectorAll(".ennu-admin-tab-content");
            
            testLinks.forEach(function(link) {
                link.addEventListener("click", function(event) {
                    event.preventDefault();
                    const targetId = this.getAttribute("href");
                    
                    testLinks.forEach(l => l.classList.remove("ennu-admin-tab-active"));
                    testContents.forEach(c => c.classList.remove("ennu-admin-tab-active"));
                    
                    this.classList.add("ennu-admin-tab-active");
                    const target = document.querySelector(targetId);
                    if (target) {
                        target.classList.add("ennu-admin-tab-active");
                    }
                });
            });
            
            console.log("Live tab test initialized");
        }
    });';
	echo '</script>';

	echo '<p><strong>Try clicking the tabs above ↑</strong></p>';
	echo "<p>If these work but your profile tabs don't, there's a specific issue with the profile page implementation.</p>";
}


