<?php
/**
 * Test Real AJAX URL
 * 
 * Tests the actual WordPress admin-ajax.php URL
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
    <title>🔧 Test Real AJAX URL</title>
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
        .test-section {
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
        <h1>🔧 Test Real AJAX URL</h1>
        <p>Testing the actual WordPress admin-ajax.php URL to see if it's accessible.</p>
        
        <div class="test-section">
            <h2>🧪 WordPress AJAX URL Test</h2>
            <p>This will test the actual WordPress admin-ajax.php URL:</p>
            
            <button class="test-button" onclick="testWordPressAJAX()">
                🔄 Test WordPress AJAX
            </button>
            
            <button class="test-button" onclick="testAJAXWithFile()">
                📄 Test AJAX with File
            </button>
            
            <div id="ajax-result" class="log-output" style="display:none;"></div>
        </div>
        
        <div class="test-section">
            <h2>📋 URL Analysis</h2>
            <div id="url-analysis" class="log-output" style="display:none;"></div>
        </div>
    </div>

    <script>
        function testWordPressAJAX() {
            const resultDiv = document.getElementById('ajax-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing WordPress AJAX URL...\n';
            
            // Test the actual WordPress admin-ajax.php URL
            const ajaxUrl = '<?php echo admin_url("admin-ajax.php"); ?>';
            resultDiv.innerHTML += 'AJAX URL: ' + ajaxUrl + '\n\n';
            
            // Create FormData
            const formData = new FormData();
            formData.append('action', 'ennu_upload_pdf');
            formData.append('nonce', '<?php echo wp_create_nonce("ennu_ajax_nonce"); ?>');
            
            // Add a mock file
            const mockFile = new File(['mock pdf content'], 'test.pdf', { type: 'application/pdf' });
            formData.append('labcorp_pdf', mockFile);
            
            resultDiv.innerHTML += 'FormData created:\n';
            resultDiv.innerHTML += '- action: ennu_upload_pdf\n';
            resultDiv.innerHTML += '- nonce: <?php echo wp_create_nonce("ennu_ajax_nonce"); ?>\n';
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
                
                // Try to parse as JSON
                try {
                    const jsonData = JSON.parse(data);
                    analyzeResponse(jsonData);
                } catch (e) {
                    resultDiv.innerHTML += 'Response is not valid JSON: ' + e.message + '\n';
                }
            })
            .catch(error => {
                resultDiv.innerHTML += 'Error: ' + error.message + '\n';
            });
        }
        
        function testAJAXWithFile() {
            const resultDiv = document.getElementById('ajax-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing AJAX with real file...\n';
            
            // This would test with a real file input
            resultDiv.innerHTML += 'This would test with a real file upload\n';
        }
        
        function analyzeResponse(data) {
            const analysisDiv = document.getElementById('url-analysis');
            analysisDiv.style.display = 'block';
            
            let analysis = 'Response Analysis:\n\n';
            
            if (data.success) {
                analysis += '✅ SUCCESS\n';
                analysis += 'Message: ' + (data.message || 'No message') + '\n';
                
                if (data.notification) {
                    analysis += '\n📢 Notification:\n';
                    analysis += '- Type: ' + data.notification.type + '\n';
                    analysis += '- Title: ' + data.notification.title + '\n';
                    analysis += '- Message: ' + data.notification.message + '\n';
                    
                    if (data.notification.biomarkers) {
                        analysis += '\n🧬 Biomarkers Found: ' + Object.keys(data.notification.biomarkers).length + '\n';
                        Object.keys(data.notification.biomarkers).forEach(key => {
                            analysis += '- ' + key + ': ' + data.notification.biomarkers[key] + '\n';
                        });
                    }
                }
                
                if (data.biomarkers_imported) {
                    analysis += '\n📊 Biomarkers Imported: ' + data.biomarkers_imported + '\n';
                }
            } else {
                analysis += '❌ FAILURE\n';
                analysis += 'Message: ' + (data.message || 'No message') + '\n';
                
                if (data.notification) {
                    analysis += '\n📢 Error Notification:\n';
                    analysis += '- Type: ' + data.notification.type + '\n';
                    analysis += '- Title: ' + data.notification.title + '\n';
                    analysis += '- Message: ' + data.notification.message + '\n';
                }
            }
            
            analysisDiv.innerHTML = analysis;
        }
    </script>
</body>
</html> 