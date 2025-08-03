<?php
/**
 * Simple Upload Test
 * 
 * Basic test to isolate network and upload issues
 */

// Handle AJAX requests FIRST - before any output
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['action'] ) ) {
    // Simulate WordPress environment for AJAX
    define( 'ABSPATH', dirname( __FILE__ ) . '/' );
    define( 'WP_DEBUG', true );
    
    // Mock WordPress functions for AJAX
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
    
    if ( ! function_exists( 'wp_upload_dir' ) ) {
        function wp_upload_dir() {
            return array(
                'basedir' => dirname( __FILE__ ) . '/uploads',
                'baseurl' => 'http://localhost/wp-content/plugins/ennulifeassessments/uploads'
            );
        }
    }
    
    if ( ! function_exists( 'wp_mkdir_p' ) ) {
        function wp_mkdir_p( $path ) {
            return mkdir( $path, 0755, true );
        }
    }
    
    if ( ! function_exists( 'mime_content_type' ) ) {
        function mime_content_type( $filename ) {
            return 'application/pdf';
        }
    }
    
    if ( ! function_exists( 'current_time' ) ) {
        function current_time( $type = 'mysql' ) {
            return date( 'Y-m-d H:i:s' );
        }
    }
    
    if ( ! function_exists( 'get_user_meta' ) ) {
        function get_user_meta( $user_id, $key = '', $single = false ) {
            // Mock user meta for testing
            $mock_biomarkers = array(
                'glucose' => '95',
                'hba1c' => '5.2',
                'testosterone' => '650',
                'cholesterol_total' => '180',
                'hdl' => '55',
                'ldl' => '100',
                'triglycerides' => '120',
                'blood_pressure_systolic' => '120',
                'blood_pressure_diastolic' => '80'
            );
            
            if ( $key === '' ) {
                return $mock_biomarkers;
            }
            
            return isset( $mock_biomarkers[$key] ) ? $mock_biomarkers[$key] : '';
        }
    }
    
    if ( ! function_exists( 'update_user_meta' ) ) {
        function update_user_meta( $user_id, $meta_key, $meta_value, $prev_value = '' ) {
            // Mock update for testing
            return true;
        }
    }
    
    if ( ! function_exists( 'add_user_meta' ) ) {
        function add_user_meta( $user_id, $meta_key, $meta_value, $unique = false ) {
            // Mock add for testing
            return true;
        }
    }
    
    if ( ! function_exists( 'wp_kses_post' ) ) {
        function wp_kses_post( $data ) {
            return htmlspecialchars( $data );
        }
    }
    
    if ( ! function_exists( 'sanitize_text_field' ) ) {
        function sanitize_text_field( $str ) {
            return htmlspecialchars( trim( $str ) );
        }
    }
    
    if ( ! function_exists( 'wp_json_encode' ) ) {
        function wp_json_encode( $data, $options = 0, $depth = 512 ) {
            return json_encode( $data, $options, $depth );
        }
    }
    
    // Include required files for AJAX
    require_once 'includes/services/class-pdf-processor.php';
    
    // Set JSON header
    header( 'Content-Type: application/json' );
    
    switch ( $_POST['action'] ) {
        case 'test_ajax':
            echo json_encode( array(
                'success' => true,
                'message' => 'Simple AJAX test successful',
                'data' => $_POST
            ) );
            break;
            
        case 'test_upload':
            if ( isset( $_FILES['test_file'] ) && $_FILES['test_file']['error'] === UPLOAD_ERR_OK ) {
                try {
                    $processor = new ENNU_PDF_Processor();
                    $result = $processor->process_labcorp_pdf( $_FILES['test_file']['tmp_name'], 1 );
                    echo json_encode( $result );
                } catch ( Exception $e ) {
                    echo json_encode( array(
                        'success' => false,
                        'message' => 'PDF processing failed: ' . $e->getMessage()
                    ) );
                }
            } else {
                echo json_encode( array(
                    'success' => false,
                    'message' => 'No file uploaded or upload error'
                ) );
            }
            break;
            
        case 'test_ajax_upload':
            if ( isset( $_FILES['test_file'] ) && $_FILES['test_file']['error'] === UPLOAD_ERR_OK ) {
                try {
                    $processor = new ENNU_PDF_Processor();
                    $result = $processor->process_labcorp_pdf( $_FILES['test_file']['tmp_name'], 1 );
                    echo json_encode( $result );
                } catch ( Exception $e ) {
                    echo json_encode( array(
                        'success' => false,
                        'message' => 'PDF processing failed: ' . $e->getMessage()
                    ) );
                }
            } else {
                echo json_encode( array(
                    'success' => false,
                    'message' => 'No file uploaded or upload error'
                ) );
            }
            break;
            
        default:
            echo json_encode( array(
                'success' => false,
                'message' => 'Unknown action: ' . $_POST['action']
            ) );
    }
    exit;
}

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

if ( ! function_exists( 'wp_upload_dir' ) ) {
    function wp_upload_dir() {
        return array(
            'basedir' => dirname( __FILE__ ) . '/uploads',
            'baseurl' => 'http://localhost/wp-content/plugins/ennulifeassessments/uploads'
        );
    }
}

if ( ! function_exists( 'wp_mkdir_p' ) ) {
    function wp_mkdir_p( $path ) {
        return mkdir( $path, 0755, true );
    }
}

if ( ! function_exists( 'mime_content_type' ) ) {
    function mime_content_type( $filename ) {
        return 'application/pdf';
    }
}

if ( ! function_exists( 'current_time' ) ) {
    function current_time( $type = 'mysql' ) {
        return date( 'Y-m-d H:i:s' );
    }
}

if ( ! function_exists( 'get_user_meta' ) ) {
    function get_user_meta( $user_id, $key = '', $single = false ) {
        // Mock user meta for testing
        $mock_biomarkers = array(
            'glucose' => '95',
            'hba1c' => '5.2',
            'testosterone' => '650',
            'cholesterol_total' => '180',
            'hdl' => '55',
            'ldl' => '100',
            'triglycerides' => '120',
            'blood_pressure_systolic' => '120',
            'blood_pressure_diastolic' => '80'
        );
        
        if ( $key === '' ) {
            return $mock_biomarkers;
        }
        
        return isset( $mock_biomarkers[$key] ) ? $mock_biomarkers[$key] : '';
    }
}

if ( ! function_exists( 'update_user_meta' ) ) {
    function update_user_meta( $user_id, $meta_key, $meta_value, $prev_value = '' ) {
        // Mock update for testing
        return true;
    }
}

if ( ! function_exists( 'add_user_meta' ) ) {
    function add_user_meta( $user_id, $meta_key, $meta_value, $unique = false ) {
        // Mock add for testing
        return true;
    }
}

if ( ! function_exists( 'wp_kses_post' ) ) {
    function wp_kses_post( $data ) {
        return htmlspecialchars( $data );
    }
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $str ) {
        return htmlspecialchars( trim( $str ) );
    }
}

if ( ! function_exists( 'wp_json_encode' ) ) {
    function wp_json_encode( $data, $options = 0, $depth = 512 ) {
        return json_encode( $data, $options, $depth );
    }
}

// Include required files
require_once 'includes/services/class-pdf-processor.php';

// Set content type
header( 'Content-Type: text/html; charset=utf-8' );

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Simple Upload Test</title>
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
        .upload-form {
            border: 2px dashed #007cba;
            padding: 30px;
            text-align: center;
            border-radius: 10px;
            background: #f8f9fa;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Simple Upload Test</h1>
        <p>Testing basic upload functionality to isolate network issues.</p>
        
        <div class="test-section">
            <h2>📋 Server Status</h2>
            <?php performServerCheck(); ?>
        </div>
        
        <div class="test-section">
            <h2>📤 Simple Upload Form</h2>
            <p>Test a basic file upload without AJAX:</p>
            
            <div class="upload-form">
                <form action="simple-upload-test.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="test_upload">
                    <input type="file" name="test_file" accept=".pdf" required>
                    <br><br>
                    <button type="submit" class="test-button">📄 Upload File</button>
                </form>
            </div>
            
            <?php
            // Handle form submission
            if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['action'] ) && $_POST['action'] === 'test_upload' ) {
                handleSimpleUpload();
            }
            ?>
        </div>
        
        <div class="test-section">
            <h2>🧪 AJAX Test</h2>
            <p>Test AJAX functionality with a simple request:</p>
            
            <button class="test-button" onclick="testSimpleAJAX()">
                🔄 Test Simple AJAX
            </button>
            
            <button class="test-button" onclick="testFileUploadAJAX()">
                📄 Test File Upload AJAX
            </button>
            
            <div id="ajax-result" class="log-output" style="display:none;"></div>
        </div>
    </div>

    <script>
        function testSimpleAJAX() {
            const resultDiv = document.getElementById('ajax-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing simple AJAX request...\n';
            
            // Simple AJAX test
            fetch('simple-upload-test.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=test_ajax&test=hello'
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
        
        function testFileUploadAJAX() {
            const resultDiv = document.getElementById('ajax-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing file upload AJAX...\n';
            
            // Create a file input
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.accept = '.pdf';
            fileInput.onchange = function(e) {
                const file = e.target.files[0];
                if (file) {
                    resultDiv.innerHTML += 'Selected file: ' + file.name + ' (' + file.size + ' bytes)\n';
                    
                    // Create FormData
                    const formData = new FormData();
                    formData.append('action', 'test_ajax_upload');
                    formData.append('test_file', file);
                    
                    // Test the fetch
                    fetch('simple-upload-test.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        resultDiv.innerHTML += 'Response status: ' + response.status + '\n';
                        return response.text();
                    })
                    .then(data => {
                        resultDiv.innerHTML += 'Response data:\n' + data + '\n';
                    })
                    .catch(error => {
                        resultDiv.innerHTML += 'Error: ' + error.message + '\n';
                    });
                }
            };
            fileInput.click();
        }
    </script>
</body>
</html>

<?php

function performServerCheck() {
    echo "<div class='test-section info'>";
    echo "<h3>🔍 Server Environment Check</h3>";
    
    $checks = array();
    
    // Check 1: PHP version
    $php_version = phpversion();
    $checks[] = array(
        'status' => 'success',
        'icon' => '✅',
        'title' => 'PHP Version',
        'message' => "PHP {$php_version} is running."
    );
    
    // Check 2: Server software
    $server_software = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
    $checks[] = array(
        'status' => 'info',
        'icon' => 'ℹ️',
        'title' => 'Server Software',
        'message' => "Server: {$server_software}"
    );
    
    // Check 3: File upload settings
    $upload_max_filesize = ini_get('upload_max_filesize');
    $post_max_size = ini_get('post_max_size');
    $checks[] = array(
        'status' => 'info',
        'icon' => 'ℹ️',
        'title' => 'Upload Settings',
        'message' => "Max file size: {$upload_max_filesize}, Max POST size: {$post_max_size}"
    );
    
    // Check 4: File upload enabled
    $file_uploads = ini_get('file_uploads');
    $checks[] = array(
        'status' => $file_uploads ? 'success' : 'error',
        'icon' => $file_uploads ? '✅' : '❌',
        'title' => 'File Uploads',
        'message' => $file_uploads ? 'Enabled' : 'DISABLED'
    );
    
    // Check 5: PDF Processor
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
    
    // Check 6: Upload directory
    $upload_dir = wp_upload_dir();
    $labcorp_dir = $upload_dir['basedir'] . '/labcorp-pdfs/';
    
    if ( file_exists( $labcorp_dir ) ) {
        $checks[] = array(
            'status' => 'success',
            'icon' => '✅',
            'title' => 'Upload Directory',
            'message' => "Upload directory exists: {$labcorp_dir}"
        );
    } else {
        $checks[] = array(
            'status' => 'warning',
            'icon' => '⚠️',
            'title' => 'Upload Directory',
            'message' => "Upload directory does not exist: {$labcorp_dir}"
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

function handleSimpleUpload() {
    echo "<div class='test-section info'>";
    echo "<h3>📤 Upload Test Results</h3>";
    
    if ( isset( $_FILES['test_file'] ) && $_FILES['test_file']['error'] === UPLOAD_ERR_OK ) {
        $file = $_FILES['test_file'];
        
        echo "<div class='test-section success'>";
        echo "<h4>✅ File Upload Successful!</h4>";
        echo "<p><strong>File Name:</strong> " . htmlspecialchars( $file['name'] ) . "</p>";
        echo "<p><strong>File Size:</strong> " . number_format( $file['size'] ) . " bytes</p>";
        echo "<p><strong>File Type:</strong> " . htmlspecialchars( $file['type'] ) . "</p>";
        echo "<p><strong>Temp Path:</strong> " . htmlspecialchars( $file['tmp_name'] ) . "</p>";
        
        // Test PDF processing
        try {
            $processor = new ENNU_PDF_Processor();
            $result = $processor->process_labcorp_pdf( $file['tmp_name'], 1 );
            
            echo "<h4>✅ PDF Processing Successful!</h4>";
            echo "<p><strong>Success:</strong> " . ( $result['success'] ? 'Yes' : 'No' ) . "</p>";
            echo "<p><strong>Message:</strong> " . htmlspecialchars( $result['message'] ) . "</p>";
            
            if ( isset( $result['biomarkers_imported'] ) ) {
                echo "<p><strong>Biomarkers Imported:</strong> " . $result['biomarkers_imported'] . "</p>";
            }
            
        } catch ( Exception $e ) {
            echo "<div class='test-section error'>";
            echo "<h4>❌ PDF Processing Failed</h4>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars( $e->getMessage() ) . "</p>";
            echo "</div>";
        }
        
        echo "</div>";
        
    } else {
        echo "<div class='test-section error'>";
        echo "<h4>❌ File Upload Failed</h4>";
        
        if ( isset( $_FILES['test_file']['error'] ) ) {
            switch ( $_FILES['test_file']['error'] ) {
                case UPLOAD_ERR_INI_SIZE:
                    echo "<p>File too large (exceeds upload_max_filesize)</p>";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    echo "<p>File too large (exceeds MAX_FILE_SIZE)</p>";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    echo "<p>File upload was incomplete</p>";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    echo "<p>No file was uploaded</p>";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    echo "<p>Missing temporary folder</p>";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    echo "<p>Failed to write file to disk</p>";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    echo "<p>File upload stopped by extension</p>";
                    break;
                default:
                    echo "<p>Unknown upload error</p>";
            }
        } else {
            echo "<p>No file uploaded</p>";
        }
        
        echo "</div>";
    }
    
    echo "</div>";
}

?> 