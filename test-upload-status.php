<?php
/**
 * Test Upload Status
 * 
 * Checks what's happening with the PDF upload process
 */

// Simulate WordPress environment
define( 'ABSPATH', dirname( __FILE__ ) . '/' );
define( 'WP_DEBUG', true );

// Mock WordPress functions
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

if ( ! function_exists( 'get_current_user_id' ) ) {
    function get_current_user_id() {
        return 1;
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
    <title>🔍 Upload Status Check</title>
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
        .status-section {
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
        <h1>🔍 Upload Status Check</h1>
        <p>Checking what's happening with the PDF upload process.</p>
        
        <div class="status-section">
            <h2>📋 File System Check</h2>
            <?php performFileSystemCheck(); ?>
        </div>
        
        <div class="status-section">
            <h2>🧪 Direct PDF Processing Test</h2>
            <p>Test the PDF processor directly with the sample file:</p>
            
            <button class="test-button" onclick="testDirectProcessing()">
                📄 Test Direct Processing
            </button>
            
            <div id="processing-result" class="log-output" style="display:none;"></div>
        </div>
        
        <div class="status-section">
            <h2>🔍 Upload Directory Check</h2>
            <?php checkUploadDirectory(); ?>
        </div>
        
        <div class="status-section">
            <h2>📊 User Biomarker Check</h2>
            <?php checkUserBiomarkers(); ?>
        </div>
    </div>

    <script>
        function testDirectProcessing() {
            const resultDiv = document.getElementById('processing-result');
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Testing direct PDF processing...\n';
            
            // This would make an AJAX call to test the processor
            fetch('test-direct-processing.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'test_pdf_processing',
                    file_path: 'SampleLabCorpResults.pdf'
                })
            })
            .then(response => response.text())
            .then(data => {
                resultDiv.innerHTML += 'Result: ' + data + '\n';
            })
            .catch(error => {
                resultDiv.innerHTML += 'Error: ' + error.message + '\n';
            });
        }
    </script>
</body>
</html>

<?php

function performFileSystemCheck() {
    echo "<div class='status-section info'>";
    echo "<h3>📁 File System Status</h3>";
    
    $checks = array();
    
    // Check 1: Sample PDF exists
    $sample_pdf = __DIR__ . '/SampleLabCorpResults.pdf';
    if ( file_exists( $sample_pdf ) ) {
        $file_size = filesize( $sample_pdf );
        $checks[] = array(
            'status' => 'success',
            'icon' => '✅',
            'title' => 'Sample PDF File',
            'message' => "SampleLabCorpResults.pdf exists ({$file_size} bytes)."
        );
    } else {
        $checks[] = array(
            'status' => 'error',
            'icon' => '❌',
            'title' => 'Sample PDF File',
            'message' => 'SampleLabCorpResults.pdf is NOT found!'
        );
    }
    
    // Check 2: Upload directory
    $upload_dir = wp_upload_dir();
    $labcorp_dir = $upload_dir['basedir'] . '/labcorp-pdfs/';
    
    if ( file_exists( $labcorp_dir ) ) {
        $checks[] = array(
            'status' => 'success',
            'icon' => '✅',
            'title' => 'Upload Directory',
            'message' => "LabCorp upload directory exists: {$labcorp_dir}"
        );
    } else {
        $checks[] = array(
            'status' => 'warning',
            'icon' => '⚠️',
            'title' => 'Upload Directory',
            'message' => "LabCorp upload directory does not exist: {$labcorp_dir}"
        );
    }
    
    // Check 3: Directory permissions
    if ( is_writable( dirname( $labcorp_dir ) ) ) {
        $checks[] = array(
            'status' => 'success',
            'icon' => '✅',
            'title' => 'Directory Permissions',
            'message' => 'Upload directory is writable.'
        );
    } else {
        $checks[] = array(
            'status' => 'error',
            'icon' => '❌',
            'title' => 'Directory Permissions',
            'message' => 'Upload directory is NOT writable!'
        );
    }
    
    // Check 4: PDF Processor
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
    
    // Display all checks
    foreach ( $checks as $check ) {
        echo "<div class='status-section {$check['status']}'>";
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

function checkUploadDirectory() {
    echo "<div class='status-section info'>";
    echo "<h3>📁 Upload Directory Contents</h3>";
    
    $upload_dir = wp_upload_dir();
    $labcorp_dir = $upload_dir['basedir'] . '/labcorp-pdfs/';
    
    if ( file_exists( $labcorp_dir ) ) {
        $files = scandir( $labcorp_dir );
        $pdf_files = array_filter( $files, function( $file ) {
            return pathinfo( $file, PATHINFO_EXTENSION ) === 'pdf';
        } );
        
        if ( ! empty( $pdf_files ) ) {
            echo "<div class='status-section success'>";
            echo "<h4>✅ Found PDF files in upload directory:</h4>";
            echo "<ul>";
            foreach ( $pdf_files as $file ) {
                $file_path = $labcorp_dir . $file;
                $file_size = filesize( $file_path );
                $file_time = date( 'Y-m-d H:i:s', filemtime( $file_path ) );
                echo "<li>{$file} ({$file_size} bytes, uploaded {$file_time})</li>";
            }
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<div class='status-section warning'>";
            echo "<h4>⚠️ No PDF files found in upload directory</h4>";
            echo "<p>Directory: {$labcorp_dir}</p>";
            echo "</div>";
        }
    } else {
        echo "<div class='status-section error'>";
        echo "<h4>❌ Upload directory does not exist</h4>";
        echo "<p>Expected path: {$labcorp_dir}</p>";
        echo "</div>";
    }
    
    echo "</div>";
}

function checkUserBiomarkers() {
    echo "<div class='status-section info'>";
    echo "<h3>🧬 User Biomarker Status</h3>";
    
    // Mock user biomarkers (in real system, this would check actual user meta)
    $user_id = get_current_user_id();
    
    echo "<div class='status-section info'>";
    echo "<h4>ℹ️ User ID: {$user_id}</h4>";
    echo "<p>In a real WordPress environment, this would check the user's biomarker data.</p>";
    echo "<p>To check actual biomarkers, go to your WordPress dashboard and look at the 'My Biomarkers' tab.</p>";
    echo "</div>";
    
    echo "</div>";
}

?> 