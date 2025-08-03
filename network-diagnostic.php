<?php
/**
 * Network Diagnostic Tool
 * 
 * Simple test to identify network connectivity issues
 */

// Set content type
header( 'Content-Type: text/html; charset=utf-8' );

// Handle simple AJAX test
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['test'] ) ) {
    header( 'Content-Type: application/json' );
    echo json_encode( array(
        'success' => true,
        'message' => 'Network test successful',
        'timestamp' => date( 'Y-m-d H:i:s' ),
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
    ) );
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Network Diagnostic</title>
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
        <h1>🔧 Network Diagnostic Tool</h1>
        <p>Testing network connectivity and server responses.</p>
        
        <div class="test-section">
            <h2>📋 Server Information</h2>
            <?php
            echo "<div class='test-section info'>";
            echo "<h3>🔍 Server Details</h3>";
            echo "<p><strong>Server Software:</strong> " . ( $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ) . "</p>";
            echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
            echo "<p><strong>Request Method:</strong> " . $_SERVER['REQUEST_METHOD'] . "</p>";
            echo "<p><strong>Request URI:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";
            echo "<p><strong>HTTP Host:</strong> " . ( $_SERVER['HTTP_HOST'] ?? 'Unknown' ) . "</p>";
            echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
            echo "</div>";
            ?>
        </div>
        
        <div class="test-section">
            <h2>🧪 Network Tests</h2>
            <p>Test different network connectivity scenarios:</p>
            
            <button class="test-button" onclick="testSamePage()">
                🔄 Test Same Page AJAX
            </button>
            
            <button class="test-button" onclick="testRelativeURL()">
                📄 Test Relative URL
            </button>
            
            <button class="test-button" onclick="testAbsoluteURL()">
                🌐 Test Absolute URL
            </button>
            
            <button class="test-button" onclick="testWordPressAJAX()">
                ⚙️ Test WordPress AJAX
            </button>
            
            <div id="network-result" class="log-output" style="display:none;"></div>
        </div>
        
        <div class="test-section">
            <h2>🔍 URL Analysis</h2>
            <p>Current page information:</p>
            <div class="log-output">
                <strong>Current URL:</strong> <?php echo $_SERVER['REQUEST_URI']; ?><br>
                <strong>Full URL:</strong> <?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?><br>
                <strong>Script Path:</strong> <?php echo $_SERVER['SCRIPT_NAME']; ?><br>
                <strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?><br>
                <strong>Server Name:</strong> <?php echo $_SERVER['SERVER_NAME']; ?><br>
            </div>
        </div>
    </div>

    <script>
        function testSamePage() {
            const resultDiv = document.getElementById('network-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing same page AJAX...\n';
            
            // Test AJAX to same page
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'test=network'
            })
            .then(response => {
                resultDiv.innerHTML += '✅ Response received!\n';
                resultDiv.innerHTML += 'Status: ' + response.status + '\n';
                resultDiv.innerHTML += 'Headers: ' + JSON.stringify([...response.headers.entries()]) + '\n';
                return response.text();
            })
            .then(data => {
                resultDiv.innerHTML += 'Data: ' + data + '\n';
            })
            .catch(error => {
                resultDiv.innerHTML += '❌ Error: ' + error.message + '\n';
            });
        }
        
        function testRelativeURL() {
            const resultDiv = document.getElementById('network-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing relative URL...\n';
            
            // Test relative URL
            fetch('network-diagnostic.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'test=network'
            })
            .then(response => {
                resultDiv.innerHTML += '✅ Response received!\n';
                resultDiv.innerHTML += 'Status: ' + response.status + '\n';
                return response.text();
            })
            .then(data => {
                resultDiv.innerHTML += 'Data: ' + data + '\n';
            })
            .catch(error => {
                resultDiv.innerHTML += '❌ Error: ' + error.message + '\n';
            });
        }
        
        function testAbsoluteURL() {
            const resultDiv = document.getElementById('network-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing absolute URL...\n';
            
            // Test absolute URL
            const absoluteURL = window.location.origin + window.location.pathname;
            resultDiv.innerHTML += 'URL: ' + absoluteURL + '\n';
            
            fetch(absoluteURL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'test=network'
            })
            .then(response => {
                resultDiv.innerHTML += '✅ Response received!\n';
                resultDiv.innerHTML += 'Status: ' + response.status + '\n';
                return response.text();
            })
            .then(data => {
                resultDiv.innerHTML += 'Data: ' + data + '\n';
            })
            .catch(error => {
                resultDiv.innerHTML += '❌ Error: ' + error.message + '\n';
            });
        }
        
        function testWordPressAJAX() {
            const resultDiv = document.getElementById('network-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing WordPress AJAX...\n';
            
            // Test WordPress admin-ajax.php
            const wpAjaxURL = window.location.origin + '/wp-admin/admin-ajax.php';
            resultDiv.innerHTML += 'URL: ' + wpAjaxURL + '\n';
            
            fetch(wpAjaxURL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=test&data=hello'
            })
            .then(response => {
                resultDiv.innerHTML += '✅ Response received!\n';
                resultDiv.innerHTML += 'Status: ' + response.status + '\n';
                return response.text();
            })
            .then(data => {
                resultDiv.innerHTML += 'Data: ' + data + '\n';
            })
            .catch(error => {
                resultDiv.innerHTML += '❌ Error: ' + error.message + '\n';
            });
        }
    </script>
</body>
</html> 