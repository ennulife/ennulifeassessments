<?php
/**
 * ENNU Life - Admin Symptoms JavaScript Debug
 *
 * This script helps debug the JavaScript issues with the admin symptoms functionality.
 */

// Load WordPress
require_once dirname( __FILE__ ) . '/../../../wp-load.php';

// Ensure we're in admin context
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'Access denied' );
}

echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<title>ENNU Life - Admin Symptoms JavaScript Debug</title>';
echo "<meta charset='utf-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1'>";
wp_head();
echo '</head>';
echo '<body>';

echo '<h1>ENNU Life - Admin Symptoms JavaScript Debug</h1>';
echo '<p><strong>Debugging the JavaScript issues with admin symptoms functionality.</strong></p>';

// Test user ID
$test_user_id = 1;

echo '<h2>Step 1: Loading WordPress Admin Assets</h2>';

// Enqueue WordPress admin assets
wp_enqueue_script( 'jquery' );
wp_enqueue_script( 'jquery-ui-core' );
wp_enqueue_script( 'jquery-ui-dialog' );

// Enqueue our admin assets
wp_enqueue_style( 'ennu-admin-tabs-enhanced', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-tabs-enhanced.css', array(), ENNU_LIFE_VERSION );
wp_enqueue_style( 'ennu-admin-scores-enhanced', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-scores-enhanced.css', array(), ENNU_LIFE_VERSION );
wp_enqueue_style( 'ennu-admin-symptoms-enhanced', ENNU_LIFE_PLUGIN_URL . 'assets/css/admin-symptoms-enhanced.css', array(), ENNU_LIFE_VERSION );
wp_enqueue_script( 'ennu-admin-enhanced', ENNU_LIFE_PLUGIN_URL . 'assets/js/ennu-admin-enhanced.js', array( 'jquery' ), ENNU_LIFE_VERSION, true );

// Localize the script with admin data
wp_localize_script(
	'ennu-admin-enhanced',
	'ennuAdmin',
	array(
		'nonce'       => wp_create_nonce( 'ennu_admin_nonce' ),
		'ajax_url'    => admin_url( 'admin-ajax.php' ),
		'confirm_msg' => 'Are you sure?',
		'plugin_url'  => ENNU_LIFE_PLUGIN_URL,
		'debug'       => true,
	)
);

echo '<p><strong>WordPress admin assets loaded.</strong></p>';

echo '<h2>Step 2: Checking JavaScript Dependencies</h2>';

// Check if jQuery is available
echo '<h3>jQuery Availability</h3>';
echo '<script>';
echo "console.log('jQuery available:', typeof jQuery !== 'undefined');";
echo "console.log('jQuery version:', typeof jQuery !== 'undefined' ? jQuery.fn.jquery : 'NOT AVAILABLE');";
echo '</script>';

// Check if ennuAdmin object is available
echo '<h3>ennuAdmin Object</h3>';
echo '<script>';
echo "console.log('ennuAdmin object:', typeof ennuAdmin !== 'undefined' ? ennuAdmin : 'NOT AVAILABLE');";
echo "if (typeof ennuAdmin !== 'undefined') {";
echo "  console.log('ennuAdmin.ajax_url:', ennuAdmin.ajax_url);";
echo "  console.log('ennuAdmin.nonce:', ennuAdmin.nonce);";
echo '}';
echo '</script>';

echo '<h2>Step 2: Testing Button Elements</h2>';

echo "<div style='background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo '<h3>Test Buttons</h3>';

// Create test buttons
echo "<button type='button' id='ennu-update-centralized-symptoms' data-user-id='$test_user_id' class='button button-secondary'>Update Centralized Symptoms</button><br><br>";
echo "<button type='button' id='ennu-populate-centralized-symptoms' data-user-id='$test_user_id' class='button button-primary'>Populate Centralized Symptoms</button><br><br>";
echo "<button type='button' id='ennu-clear-symptom-history' data-user-id='$test_user_id' class='button button-secondary'>Clear Symptom History</button>";

echo '</div>';

echo '<h2>Step 3: JavaScript Debug Console</h2>';

echo "<div style='background: #000; color: #0f0; padding: 20px; border-radius: 8px; margin: 20px 0; font-family: monospace; height: 300px; overflow-y: auto;' id='debug-console'>";
echo 'JavaScript Debug Console:<br>';
echo 'Waiting for events...<br>';
echo '</div>';

echo '<h2>Step 4: Manual JavaScript Test</h2>';

echo '<script>';
echo "
// Debug function to log to our console
function debugLog(message) {
    const debugConsole = document.getElementById('debug-console');
    if (debugConsole) {
        debugConsole.innerHTML += message + '<br>';
        debugConsole.scrollTop = debugConsole.scrollHeight;
    }
    // Use window.console to avoid conflicts
    if (window.console && window.console.log) {
        window.console.log(message);
    }
}

// Test if buttons exist
function testButtons() {
    debugLog('Testing button existence...');
    
    const updateButton = document.getElementById('ennu-update-centralized-symptoms');
    const populateButton = document.getElementById('ennu-populate-centralized-symptoms');
    const clearButton = document.getElementById('ennu-clear-symptom-history');
    
    debugLog('Update button found: ' + (updateButton ? 'YES' : 'NO'));
    debugLog('Populate button found: ' + (populateButton ? 'YES' : 'NO'));
    debugLog('Clear button found: ' + (clearButton ? 'NO' : 'YES'));
    
    if (updateButton) {
        debugLog('Update button data-user-id: ' + updateButton.getAttribute('data-user-id'));
    }
    if (populateButton) {
        debugLog('Populate button data-user-id: ' + populateButton.getAttribute('data-user-id'));
    }
    if (clearButton) {
        debugLog('Clear button data-user-id: ' + clearButton.getAttribute('data-user-id'));
    }
}

// Test AJAX functionality
function testAjax() {
    debugLog('Testing AJAX functionality...');
    
    if (typeof jQuery === 'undefined') {
        debugLog('ERROR: jQuery not available');
        return;
    }
    
    if (typeof ennuAdmin === 'undefined') {
        debugLog('ERROR: ennuAdmin object not available');
        return;
    }
    
    debugLog('jQuery available: ' + jQuery.fn.jquery);
    debugLog('ennuAdmin.ajax_url: ' + ennuAdmin.ajax_url);
    debugLog('ennuAdmin.nonce: ' + (ennuAdmin.nonce ? 'AVAILABLE' : 'MISSING'));
    
    // Test a simple AJAX call
    jQuery.ajax({
        url: ennuAdmin.ajax_url,
        type: 'POST',
        data: {
            action: 'ennu_test_ajax',
            nonce: ennuAdmin.nonce
        },
        success: function(response) {
            debugLog('AJAX test successful: ' + JSON.stringify(response));
        },
        error: function(xhr, status, error) {
            debugLog('AJAX test failed: ' + status + ' - ' + error);
        }
    });
}

// Manual button click handlers for testing
function setupManualHandlers() {
    debugLog('Setting up manual button handlers...');
    
    const populateButton = document.getElementById('ennu-populate-centralized-symptoms');
    if (populateButton) {
        debugLog('Adding manual click handler to populate button...');
        
        // Remove any existing listeners
        populateButton.replaceWith(populateButton.cloneNode(true));
        const newPopulateButton = document.getElementById('ennu-populate-centralized-symptoms');
        
        newPopulateButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            debugLog('Populate button clicked manually');
            
            const userId = this.getAttribute('data-user-id');
            debugLog('User ID: ' + userId);
            
            if (!confirm('Manual test: Are you sure you want to populate centralized symptoms?')) {
                debugLog('User cancelled manual test');
                return;
            }
            
            debugLog('Proceeding with manual AJAX call...');
            
            if (typeof jQuery === 'undefined') {
                debugLog('ERROR: jQuery not available for AJAX');
                return;
            }
            
            if (typeof ennuAdmin === 'undefined') {
                debugLog('ERROR: ennuAdmin object not available for AJAX');
                return;
            }
            
            jQuery.ajax({
                url: ennuAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'ennu_populate_centralized_symptoms',
                    user_id: userId,
                    nonce: ennuAdmin.nonce
                },
                success: function(response) {
                    debugLog('Manual AJAX success: ' + JSON.stringify(response));
                    alert('Manual test successful! Check console for details.');
                },
                error: function(xhr, status, error) {
                    debugLog('Manual AJAX error: ' + status + ' - ' + error);
                    debugLog('Response text: ' + xhr.responseText);
                    alert('Manual test failed! Check console for details.');
                }
            });
        });
        
        debugLog('Manual click handler added successfully');
    } else {
        debugLog('ERROR: Populate button not found for manual handler');
    }
}

// Run tests when page loads
document.addEventListener('DOMContentLoaded', function() {
    debugLog('DOM loaded - running tests...');
    testButtons();
    testAjax();
    setupManualHandlers();
});

// Also run on window load
window.addEventListener('load', function() {
    debugLog('Window loaded - running additional tests...');
    testButtons();
});
";
echo '</script>';

echo '<h2>Step 5: Instructions</h2>';

echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo '<h3>Debug Instructions</h3>';
echo '<ol>';
echo '<li><strong>Open Browser Console:</strong> Press F12 and go to Console tab</li>';
echo '<li><strong>Watch Debug Console:</strong> Monitor the black console above for debug messages</li>';
echo '<li><strong>Click Test Buttons:</strong> Try clicking the buttons above</li>';
echo '<li><strong>Check for Errors:</strong> Look for any JavaScript errors in browser console</li>';
echo "<li><strong>Test Manual Handler:</strong> The manual handler should work even if the original doesn't</li>";
echo '</ol>';
echo '</div>';

echo '<h2>Step 6: Common Issues</h2>';

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo '<h3>Potential Problems</h3>';
echo '<ul>';
echo '<li><strong>jQuery not loaded:</strong> Check if jQuery is available in console</li>';
echo '<li><strong>ennuAdmin object missing:</strong> Check if ennuAdmin is defined</li>';
echo '<li><strong>Duplicate event listeners:</strong> Multiple initializations causing conflicts</li>';
echo '<li><strong>AJAX URL incorrect:</strong> Check if ajax_url is properly set</li>';
echo '<li><strong>Nonce issues:</strong> Check if nonce is being generated correctly</li>';
echo '<li><strong>Button IDs mismatch:</strong> Check if button IDs match JavaScript selectors</li>';
echo '</ul>';
echo '</div>';

echo '<h2>Step 7: Quick Fix Test</h2>';

echo "<button type='button' onclick='testButtons()' class='button button-primary'>Test Buttons</button> ";
echo "<button type='button' onclick='testAjax()' class='button button-secondary'>Test AJAX</button> ";
echo "<button type='button' onclick='setupManualHandlers()' class='button button-secondary'>Setup Manual Handlers</button>";

echo '<br><br><strong>Debug script completed. Check the debug console above for results.</strong>';
echo '<br><em>Debug completed at: ' . current_time( 'mysql' ) . '</em>';

wp_footer();
echo '</body>';
echo '</html>';


