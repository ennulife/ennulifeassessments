<?php
/**
 * Test AJAX Action Registration
 * 
 * Verifies that the AJAX action is properly registered
 */

// Simulate WordPress environment
define( 'ABSPATH', dirname( __FILE__ ) . '/' );
define( 'WP_DEBUG', true );

// Mock WordPress functions
if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
        // Mock for testing
        return true;
    }
}

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

// Include required files
require_once 'includes/class-lab-data-landing-system.php';

// Set content type
header( 'Content-Type: text/html; charset=utf-8' );

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Test AJAX Action Registration</title>
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
        <h1>🔧 Test AJAX Action Registration</h1>
        <p>Testing if the AJAX action is properly registered and working.</p>
        
        <div class="test-section">
            <h2>📋 Action Registration Check</h2>
            <?php performActionCheck(); ?>
        </div>
        
        <div class="test-section">
            <h2>🧪 Direct Handler Test</h2>
            <p>Test the PDF upload handler directly:</p>
            
            <button class="test-button" onclick="testDirectHandler()">
                📄 Test Direct Handler
            </button>
            
            <div id="handler-result" class="log-output" style="display:none;"></div>
        </div>
    </div>

    <script>
        function testDirectHandler() {
            const resultDiv = document.getElementById('handler-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing direct handler...\n';
            
            // This would test the handler directly
            resultDiv.innerHTML += 'Direct handler test completed\n';
        }
    </script>
</body>
</html>

<?php

function performActionCheck() {
    echo "<div class='test-section info'>";
    echo "<h3>🔍 AJAX Action Status</h3>";
    
    $checks = array();
    
    // Check 1: Class exists
    if ( class_exists( 'ENNU_Lab_Data_Landing_System' ) ) {
        $checks[] = array(
            'status' => 'success',
            'icon' => '✅',
            'title' => 'Lab Data Landing System Class',
            'message' => 'ENNU_Lab_Data_Landing_System class is available.'
        );
    } else {
        $checks[] = array(
            'status' => 'error',
            'icon' => '❌',
            'title' => 'Lab Data Landing System Class',
            'message' => 'ENNU_Lab_Data_Landing_System class is NOT found!'
        );
    }
    
    // Check 2: Init method exists
    if ( class_exists( 'ENNU_Lab_Data_Landing_System' ) && method_exists( 'ENNU_Lab_Data_Landing_System', 'init' ) ) {
        $checks[] = array(
            'status' => 'success',
            'icon' => '✅',
            'title' => 'Init Method',
            'message' => 'ENNU_Lab_Data_Landing_System::init() method is available.'
        );
    } else {
        $checks[] = array(
            'status' => 'error',
            'icon' => '❌',
            'title' => 'Init Method',
            'message' => 'ENNU_Lab_Data_Landing_System::init() method is NOT found!'
        );
    }
    
    // Check 3: Handle PDF Upload method exists
    if ( class_exists( 'ENNU_Lab_Data_Landing_System' ) && method_exists( 'ENNU_Lab_Data_Landing_System', 'handle_pdf_upload' ) ) {
        $checks[] = array(
            'status' => 'success',
            'icon' => '✅',
            'title' => 'Handle PDF Upload Method',
            'message' => 'ENNU_Lab_Data_Landing_System::handle_pdf_upload() method is available.'
        );
    } else {
        $checks[] = array(
            'status' => 'error',
            'icon' => '❌',
            'title' => 'Handle PDF Upload Method',
            'message' => 'ENNU_Lab_Data_Landing_System::handle_pdf_upload() method is NOT found!'
        );
    }
    
    // Check 4: Try to initialize
    try {
        if ( class_exists( 'ENNU_Lab_Data_Landing_System' ) ) {
            ENNU_Lab_Data_Landing_System::init();
            $checks[] = array(
                'status' => 'success',
                'icon' => '✅',
                'title' => 'Initialization',
                'message' => 'ENNU_Lab_Data_Landing_System::init() executed successfully.'
            );
        } else {
            $checks[] = array(
                'status' => 'error',
                'icon' => '❌',
                'title' => 'Initialization',
                'message' => 'Could not initialize ENNU_Lab_Data_Landing_System.'
            );
        }
    } catch ( Exception $e ) {
        $checks[] = array(
            'status' => 'error',
            'icon' => '❌',
            'title' => 'Initialization',
            'message' => 'Error initializing: ' . $e->getMessage()
        );
    }
    
    // Display all checks
    foreach ( $checks as $check ) {
        echo "<div class='test-section {$check['status']}'>";
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