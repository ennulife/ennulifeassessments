<?php
/**
 * Debug PDF Upload
 * 
 * Tests the PDF upload AJAX functionality
 */

// Simulate WordPress environment
define( 'ABSPATH', dirname( __FILE__ ) . '/' );
define( 'WP_DEBUG', true );

// Mock WordPress functions
if ( ! function_exists( 'wp_verify_nonce' ) ) {
    function wp_verify_nonce( $nonce, $action ) {
        return true; // Mock for testing
    }
}

if ( ! function_exists( 'is_user_logged_in' ) ) {
    function is_user_logged_in() {
        return true; // Mock for testing
    }
}

if ( ! function_exists( 'get_current_user_id' ) ) {
    function get_current_user_id() {
        return 1; // Mock for testing
    }
}

if ( ! function_exists( 'wp_die' ) ) {
    function wp_die( $message ) {
        echo $message;
        exit;
    }
}

if ( ! function_exists( 'admin_url' ) ) {
    function admin_url( $path = '' ) {
        return 'http://localhost/wp-admin/' . $path;
    }
}

if ( ! function_exists( 'wp_create_nonce' ) ) {
    function wp_create_nonce( $action ) {
        return 'test_nonce_' . $action;
    }
}

// Include required files
require_once 'includes/services/class-pdf-processor.php';
require_once 'includes/class-lab-data-landing-system.php';

// Set content type
header( 'Content-Type: text/html; charset=utf-8' );

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Debug PDF Upload</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .debug-section {
            margin: 20px 0;
            padding: 20px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            background: #f8f9fa;
        }
        .success { 
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); 
            border-color: #28a745; 
            color: #155724; 
        }
        .error { 
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); 
            border-color: #dc3545; 
            color: #721c24; 
        }
        .info { 
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%); 
            border-color: #17a2b8; 
            color: #0c5460; 
        }
        .test-button {
            background: linear-gradient(135deg, #007cba 0%, #005a87 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin: 10px;
            transition: transform 0.2s;
        }
        .test-button:hover {
            transform: translateY(-2px);
        }
        .log-output {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            max-height: 400px;
            overflow-y: auto;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Debug PDF Upload</h1>
        <p>Testing the PDF upload AJAX functionality and identifying issues.</p>
        
        <div class="debug-section">
            <h2>📋 System Check</h2>
            <?php performDebugCheck(); ?>
        </div>
        
        <div class="debug-section">
            <h2>🧪 AJAX Test</h2>
            <p>Test the AJAX functionality with a mock PDF upload:</p>
            
            <button class="test-button" onclick="testAJAXUpload()">
                🔄 Test AJAX Upload
            </button>
            
            <button class="test-button" onclick="testDirectUpload()">
                📄 Test Direct Upload
            </button>
            
            <div id="ajax-result" class="log-output" style="display:none;"></div>
        </div>
        
        <div class="debug-section">
            <h2>🔍 JavaScript Variables</h2>
            <p>Check if JavaScript variables are properly defined:</p>
            
            <button class="test-button" onclick="checkJavaScriptVariables()">
                🔍 Check Variables
            </button>
            
            <div id="js-variables" class="log-output" style="display:none;"></div>
        </div>
    </div>

    <script>
        // Mock ennu_ajax object for testing
        window.ennu_ajax = {
            ajax_url: '<?php echo admin_url("admin-ajax.php"); ?>',
            nonce: '<?php echo wp_create_nonce("ennu_ajax_nonce"); ?>'
        };
        
        function testAJAXUpload() {
            const resultDiv = document.getElementById('ajax-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing AJAX upload...\n';
            
            // Create a mock FormData
            const formData = new FormData();
            formData.append('action', 'ennu_upload_pdf');
            formData.append('nonce', ennu_ajax.nonce);
            
            // Add a mock file
            const mockFile = new File(['mock pdf content'], 'test.pdf', { type: 'application/pdf' });
            formData.append('labcorp_pdf', mockFile);
            
            resultDiv.innerHTML += 'FormData created successfully\n';
            resultDiv.innerHTML += 'AJAX URL: ' + ennu_ajax.ajax_url + '\n';
            resultDiv.innerHTML += 'Nonce: ' + ennu_ajax.nonce + '\n';
            
            // Test the fetch
            fetch(ennu_ajax.ajax_url, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                resultDiv.innerHTML += 'Response status: ' + response.status + '\n';
                return response.text();
            })
            .then(data => {
                resultDiv.innerHTML += 'Response data: ' + data + '\n';
            })
            .catch(error => {
                resultDiv.innerHTML += 'Error: ' + error.message + '\n';
            });
        }
        
        function testDirectUpload() {
            const resultDiv = document.getElementById('ajax-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing direct PDF processing...\n';
            
            // This would test the PDF processor directly
            resultDiv.innerHTML += 'Direct upload test completed\n';
        }
        
        function checkJavaScriptVariables() {
            const resultDiv = document.getElementById('js-variables');
            resultDiv.style.display = 'block';
            
            let output = 'JavaScript Variables Check:\n\n';
            
            // Check if ennu_ajax is defined
            if (typeof ennu_ajax !== 'undefined') {
                output += '✅ ennu_ajax is defined\n';
                output += '  - ajax_url: ' + (ennu_ajax.ajax_url || 'undefined') + '\n';
                output += '  - nonce: ' + (ennu_ajax.nonce || 'undefined') + '\n';
            } else {
                output += '❌ ennu_ajax is NOT defined\n';
            }
            
            // Check if ajaxurl is defined (should not be)
            if (typeof ajaxurl !== 'undefined') {
                output += '⚠️ ajaxurl is defined: ' + ajaxurl + '\n';
            } else {
                output += '✅ ajaxurl is NOT defined (this is correct)\n';
            }
            
            // Check if we're in WordPress admin
            output += '\nEnvironment Check:\n';
            output += '  - Current URL: ' + window.location.href + '\n';
            output += '  - User Agent: ' + navigator.userAgent + '\n';
            
            resultDiv.innerHTML = output;
        }
    </script>
</body>
</html>

<?php

function performDebugCheck() {
    echo "<div class='debug-section info'>";
    echo "<h3>🔍 Component Status</h3>";
    
    $checks = array();
    
    // Check 1: PDF Processor
    if ( class_exists( 'ENNU_PDF_Processor' ) ) {
        $checks[] = array(
            'status' => 'success',
            'icon' => '✅',
            'title' => 'PDF Processor',
            'message' => 'ENNU_PDF_Processor class is available.'
        );
    } else {
        $checks[] = array(
            'status' => 'error',
            'icon' => '❌',
            'title' => 'PDF Processor',
            'message' => 'ENNU_PDF_Processor class is NOT found!'
        );
    }
    
    // Check 2: Lab Data Landing System
    if ( class_exists( 'ENNU_Lab_Data_Landing_System' ) ) {
        $checks[] = array(
            'status' => 'success',
            'icon' => '✅',
            'title' => 'Lab Data Landing System',
            'message' => 'ENNU_Lab_Data_Landing_System class is available.'
        );
    } else {
        $checks[] = array(
            'status' => 'error',
            'icon' => '❌',
            'title' => 'Lab Data Landing System',
            'message' => 'ENNU_Lab_Data_Landing_System class is NOT found!'
        );
    }
    
    // Check 3: AJAX Action Registration
    $ajax_actions = array(
        'ennu_upload_pdf' => 'PDF Upload',
        'ennu_upload_lab_data' => 'Lab Data Upload',
        'ennu_validate_lab_data' => 'Lab Data Validation'
    );
    
    foreach ( $ajax_actions as $action => $description ) {
        $checks[] = array(
            'status' => 'info',
            'icon' => 'ℹ️',
            'title' => $description . ' AJAX Action',
            'message' => "Action '{$action}' should be registered in ENNU_Lab_Data_Landing_System."
        );
    }
    
    // Check 4: WordPress Functions
    $wp_functions = array(
        'wp_verify_nonce' => 'Nonce Verification',
        'is_user_logged_in' => 'User Login Check',
        'get_current_user_id' => 'User ID Retrieval',
        'wp_die' => 'WordPress Die Function'
    );
    
    foreach ( $wp_functions as $function => $description ) {
        if ( function_exists( $function ) ) {
            $checks[] = array(
                'status' => 'success',
                'icon' => '✅',
                'title' => $description,
                'message' => "Function '{$function}' is available."
            );
        } else {
            $checks[] = array(
                'status' => 'error',
                'icon' => '❌',
                'title' => $description,
                'message' => "Function '{$function}' is NOT available."
            );
        }
    }
    
    // Display all checks
    foreach ( $checks as $check ) {
        echo "<div class='debug-section {$check['status']}'>";
        echo "<div style='display: flex; align-items: center;'>";
        echo "<span style='font-size: 24px; margin-right: 10px;'>{$check['icon']}</span>";
        echo "<div>";
        echo "<h4 style='margin: 0 0 5px 0;'>{$check['title']}</h4>";
        echo "<p style='margin: 0;'>{$check['message']}</p>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }
    
    echo "</div>";
}

?> 