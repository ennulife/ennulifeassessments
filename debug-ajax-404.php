<?php
/**
 * Debug AJAX 404 Issues
 * 
 * Identifies why AJAX requests are getting 404 errors
 */

// Simulate WordPress environment
define( 'ABSPATH', dirname( __FILE__ ) . '/' );
define( 'WP_DEBUG', true );

// Mock WordPress functions
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

// Set content type
header( 'Content-Type: text/html; charset=utf-8' );

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Debug AJAX 404 Issues</title>
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
        .warning { 
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); 
            border-color: #ffc107; 
            color: #856404; 
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
        <h1>🔧 Debug AJAX 404 Issues</h1>
        <p>Identifying why AJAX requests are getting 404 errors.</p>
        
        <div class="debug-section">
            <h2>📋 URL Accessibility Test</h2>
            <?php performURLCheck(); ?>
        </div>
        
        <div class="debug-section">
            <h2>🧪 AJAX URL Tests</h2>
            <p>Test different AJAX URLs to find the correct one:</p>
            
            <button class="test-button" onclick="testWordPressAJAX()">
                🔄 Test WordPress AJAX
            </button>
            
            <button class="test-button" onclick="testAlternativeAJAX()">
                🔄 Test Alternative AJAX
            </button>
            
            <button class="test-button" onclick="testDirectFile()">
                📄 Test Direct File
            </button>
            
            <div id="ajax-result" class="log-output" style="display:none;"></div>
        </div>
        
        <div class="debug-section">
            <h2>🔍 WordPress Environment Check</h2>
            <?php performWordPressCheck(); ?>
        </div>
    </div>

    <script>
        function testWordPressAJAX() {
            const resultDiv = document.getElementById('ajax-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing WordPress AJAX URL...\n';
            
            // Test the standard WordPress admin-ajax.php URL
            const ajaxUrl = 'http://localhost/wp-admin/admin-ajax.php';
            resultDiv.innerHTML += 'Testing URL: ' + ajaxUrl + '\n\n';
            
            // Create FormData
            const formData = new FormData();
            formData.append('action', 'ennu_upload_pdf');
            formData.append('nonce', 'test_nonce_ennu_ajax_nonce');
            
            // Add a mock file
            const mockFile = new File(['mock pdf content'], 'test.pdf', { type: 'application/pdf' });
            formData.append('labcorp_pdf', mockFile);
            
            resultDiv.innerHTML += 'FormData created:\n';
            resultDiv.innerHTML += '- action: ennu_upload_pdf\n';
            resultDiv.innerHTML += '- nonce: test_nonce_ennu_ajax_nonce\n';
            resultDiv.innerHTML += '- file: test.pdf\n\n';
            
            // Test the fetch
            fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                resultDiv.innerHTML += 'Response status: ' + response.status + '\n';
                resultDiv.innerHTML += 'Response headers: ' + JSON.stringify([...response.headers.entries()]) + '\n';
                return response.text();
            })
            .then(data => {
                resultDiv.innerHTML += 'Response data:\n' + data + '\n';
            })
            .catch(error => {
                resultDiv.innerHTML += 'Error: ' + error.message + '\n';
            });
        }
        
        function testAlternativeAJAX() {
            const resultDiv = document.getElementById('ajax-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing alternative AJAX URLs...\n';
            
            // Test different possible AJAX URLs
            const urls = [
                'http://localhost/wp-admin/admin-ajax.php',
                'http://localhost/admin-ajax.php',
                'http://localhost/wp-content/plugins/ennulifeassessments/test-ajax-response.php',
                'http://localhost/wp-content/plugins/ennulifeassessments/test-real-ajax.php'
            ];
            
            urls.forEach((url, index) => {
                resultDiv.innerHTML += `\nTesting URL ${index + 1}: ${url}\n`;
                
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=ennu_upload_pdf&nonce=test_nonce_ennu_ajax_nonce'
                })
                .then(response => {
                    resultDiv.innerHTML += `Status: ${response.status}\n`;
                    return response.text();
                })
                .then(data => {
                    resultDiv.innerHTML += `Response: ${data.substring(0, 100)}...\n`;
                })
                .catch(error => {
                    resultDiv.innerHTML += `Error: ${error.message}\n`;
                });
            });
        }
        
        function testDirectFile() {
            const resultDiv = document.getElementById('ajax-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing direct file access...\n';
            
            // Test if we can access the test files directly
            const testFiles = [
                'test-ajax-response.php',
                'test-real-ajax.php',
                'test-ajax-action.php'
            ];
            
            testFiles.forEach((file, index) => {
                resultDiv.innerHTML += `\nTesting file ${index + 1}: ${file}\n`;
                
                fetch(file, {
                    method: 'GET'
                })
                .then(response => {
                    resultDiv.innerHTML += `Status: ${response.status}\n`;
                    return response.text();
                })
                .then(data => {
                    resultDiv.innerHTML += `Response length: ${data.length} characters\n`;
                })
                .catch(error => {
                    resultDiv.innerHTML += `Error: ${error.message}\n`;
                });
            });
        }
    </script>
</body>
</html>

<?php

function performURLCheck() {
    echo "<div class='debug-section info'>";
    echo "<h3>🔍 URL Accessibility Analysis</h3>";
    
    $checks = array();
    
    // Check 1: WordPress admin URL
    $admin_url = admin_url();
    $checks[] = array(
        'status' => 'info',
        'icon' => 'ℹ️',
        'title' => 'WordPress Admin URL',
        'message' => "Admin URL: {$admin_url}"
    );
    
    // Check 2: AJAX URL
    $ajax_url = admin_url( 'admin-ajax.php' );
    $checks[] = array(
        'status' => 'info',
        'icon' => 'ℹ️',
        'title' => 'WordPress AJAX URL',
        'message' => "AJAX URL: {$ajax_url}"
    );
    
    // Check 3: Current script URL
    $current_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $checks[] = array(
        'status' => 'info',
        'icon' => 'ℹ️',
        'title' => 'Current Script URL',
        'message' => "Current URL: {$current_url}"
    );
    
    // Check 4: Server information
    $server_info = array(
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'Not set',
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'Not set',
        'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'Not set',
        'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'Not set'
    );
    
    $checks[] = array(
        'status' => 'info',
        'icon' => 'ℹ️',
        'title' => 'Server Information',
        'message' => "Host: {$server_info['HTTP_HOST']}, URI: {$server_info['REQUEST_URI']}"
    );
    
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

function performWordPressCheck() {
    echo "<div class='debug-section info'>";
    echo "<h3>🔍 WordPress Environment Check</h3>";
    
    $checks = array();
    
    // Check 1: WordPress functions
    $wp_functions = array(
        'admin_url' => function_exists( 'admin_url' ),
        'wp_create_nonce' => function_exists( 'wp_create_nonce' ),
        'wp_verify_nonce' => function_exists( 'wp_verify_nonce' ),
        'is_user_logged_in' => function_exists( 'is_user_logged_in' ),
        'get_current_user_id' => function_exists( 'get_current_user_id' )
    );
    
    foreach ( $wp_functions as $function => $exists ) {
        $checks[] = array(
            'status' => $exists ? 'success' : 'error',
            'icon' => $exists ? '✅' : '❌',
            'title' => $function . ' Function',
            'message' => $exists ? 'Available' : 'NOT available'
        );
    }
    
    // Check 2: Plugin files
    $plugin_files = array(
        'includes/class-lab-data-landing-system.php' => file_exists( __DIR__ . '/includes/class-lab-data-landing-system.php' ),
        'includes/services/class-pdf-processor.php' => file_exists( __DIR__ . '/includes/services/class-pdf-processor.php' ),
        'templates/user-dashboard.php' => file_exists( __DIR__ . '/templates/user-dashboard.php' )
    );
    
    foreach ( $plugin_files as $file => $exists ) {
        $checks[] = array(
            'status' => $exists ? 'success' : 'error',
            'icon' => $exists ? '✅' : '❌',
            'title' => $file,
            'message' => $exists ? 'Exists' : 'NOT found'
        );
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