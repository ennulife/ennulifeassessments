<?php
/**
 * Direct AJAX Handler
 * 
 * Handles PDF upload AJAX requests directly without relying on WordPress admin-ajax.php
 */

// Load WordPress environment
require_once dirname( __FILE__ ) . '/../../../wp-load.php';

// Ensure we have WordPress functions
if ( ! function_exists( 'wp_verify_nonce' ) ) {
    wp_die( 'WordPress not loaded properly' );
}

// Include required files
require_once dirname( __FILE__ ) . '/includes/services/class-pdf-processor.php';
require_once dirname( __FILE__ ) . '/includes/class-lab-data-landing-system.php';

// Handle AJAX request
if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['action'] ) && $_POST['action'] === 'ennu_upload_pdf' ) {
    
    // Set JSON response header
    header( 'Content-Type: application/json' );
    
    try {
        // Debug logging
        error_log( 'Direct AJAX Handler: PDF upload request received' );
        error_log( 'Direct AJAX Handler: POST data: ' . print_r( $_POST, true ) );
        error_log( 'Direct AJAX Handler: FILES data: ' . print_r( $_FILES, true ) );
        
        		// Verify nonce (restored with proper handling)
		if ( ! wp_verify_nonce( $_POST['nonce'], 'ennu_ajax_nonce' ) ) {
			error_log( 'ENNU Direct AJAX: Nonce verification failed. Nonce: ' . $_POST['nonce'] );
			echo json_encode( array(
				'success' => false,
				'message' => 'Security check failed.',
				'notification' => array(
					'type' => 'error',
					'title' => 'Security Error',
					'message' => 'Invalid security token. Please refresh the page and try again.',
				),
			) );
			exit;
		}
        
        		// Check user permissions (restored)
		if ( ! is_user_logged_in() ) {
			echo json_encode( array(
				'success' => false,
				'message' => 'User not logged in.',
				'notification' => array(
					'type' => 'error',
					'title' => 'Authentication Required',
					'message' => 'Please log in to upload LabCorp results.',
				),
			) );
			exit;
		}
        
        		$user_id = get_current_user_id();
        
        // Check if file was uploaded
        if ( ! isset( $_FILES['labcorp_pdf'] ) || $_FILES['labcorp_pdf']['error'] !== UPLOAD_ERR_OK ) {
            $error_message = 'No file uploaded or upload error occurred.';
            if ( isset( $_FILES['labcorp_pdf']['error'] ) ) {
                switch ( $_FILES['labcorp_pdf']['error'] ) {
                    case UPLOAD_ERR_INI_SIZE:
                        $error_message = 'File too large. Maximum size is 10MB.';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $error_message = 'File upload was incomplete.';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $error_message = 'No file was uploaded.';
                        break;
                    default:
                        $error_message = 'Upload error occurred.';
                }
            }
            
            echo json_encode( array(
                'success' => false,
                'message' => $error_message,
                'notification' => array(
                    'type' => 'error',
                    'title' => 'Upload Failed',
                    'message' => $error_message,
                ),
            ) );
            exit;
        }
        
        $file = $_FILES['labcorp_pdf'];
        
        // Validate file type
        $allowed_types = array( 'application/pdf' );
        $file_type = mime_content_type( $file['tmp_name'] );
        
        if ( ! in_array( $file_type, $allowed_types ) ) {
            echo json_encode( array(
                'success' => false,
                'message' => 'Invalid file type. Only PDF files are allowed.',
                'notification' => array(
                    'type' => 'error',
                    'title' => 'Invalid File Type',
                    'message' => 'Please upload a valid PDF file from LabCorp.',
                ),
            ) );
            exit;
        }
        
        // Validate file size (10MB max)
        if ( $file['size'] > 10 * 1024 * 1024 ) {
            echo json_encode( array(
                'success' => false,
                'message' => 'File too large. Maximum size is 10MB.',
                'notification' => array(
                    'type' => 'error',
                    'title' => 'File Too Large',
                    'message' => 'Please upload a PDF file smaller than 10MB.',
                ),
            ) );
            exit;
        }
        
        // Create upload directory if it doesn't exist
        $upload_dir = wp_upload_dir();
        $labcorp_dir = $upload_dir['basedir'] . '/labcorp-pdfs/';
        
        if ( ! file_exists( $labcorp_dir ) ) {
            wp_mkdir_p( $labcorp_dir );
        }
        
        // Generate unique filename
        $filename = 'labcorp_' . $user_id . '_' . time() . '.pdf';
        $file_path = $labcorp_dir . $filename;
        
        // Move uploaded file
        if ( ! move_uploaded_file( $file['tmp_name'], $file_path ) ) {
            echo json_encode( array(
                'success' => false,
                'message' => 'Failed to save uploaded file.',
                'notification' => array(
                    'type' => 'error',
                    'title' => 'File Save Failed',
                    'message' => 'Unable to process the uploaded file. Please try again.',
                ),
            ) );
            exit;
        }
        
        // Process PDF
        try {
            $processor = new ENNU_PDF_Processor();
            $result = $processor->process_labcorp_pdf( $file_path, $user_id );
            
            // Clean up uploaded file
            unlink( $file_path );
            
            // Log success
            error_log( 'Direct AJAX Handler: Success - ' . print_r( $result, true ) );
            
            // Return detailed result with notification
            echo json_encode( $result );
            
        } catch ( Exception $e ) {
            // Clean up uploaded file
            if ( file_exists( $file_path ) ) {
                unlink( $file_path );
            }
            
            echo json_encode( array(
                'success' => false,
                'message' => 'PDF processing failed: ' . $e->getMessage(),
                'notification' => array(
                    'type' => 'error',
                    'title' => 'Processing Failed',
                    'message' => 'Unable to process the LabCorp PDF. Please ensure it contains valid biomarker data.',
                ),
            ) );
        }
        
    } catch ( Exception $e ) {
        echo json_encode( array(
            'success' => false,
            'message' => 'Unexpected error: ' . $e->getMessage(),
            'notification' => array(
                'type' => 'error',
                'title' => 'System Error',
                'message' => 'An unexpected error occurred. Please try again.',
            ),
        ) );
    }
    
} else {
    // Not an AJAX request, show the test interface
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>🔧 Direct AJAX Handler Test</title>
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
            <h1>🔧 Direct AJAX Handler Test</h1>
            <p>This bypasses WordPress admin-ajax.php and handles PDF uploads directly.</p>
            
            <div class="test-section">
                <h2>🧪 Direct AJAX Test</h2>
                <p>Test the direct AJAX handler with a mock PDF upload:</p>
                
                <button class="test-button" onclick="testDirectAJAX()">
                    🔄 Test Direct AJAX
                </button>
                
                <button class="test-button" onclick="testWithRealFile()">
                    📄 Test with Real File
                </button>
                
                <div id="ajax-result" class="log-output" style="display:none;"></div>
            </div>
        </div>

        <script>
            function testDirectAJAX() {
                const resultDiv = document.getElementById('ajax-result');
                resultDiv.style.display = 'block';
                resultDiv.innerHTML = 'Testing direct AJAX handler...\n';
                
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
                
                // Test the fetch to this same file
                fetch('direct-ajax-handler.php', {
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
                        resultDiv.innerHTML += '\n✅ Parsed JSON successfully!\n';
                        resultDiv.innerHTML += 'Success: ' + jsonData.success + '\n';
                        resultDiv.innerHTML += 'Message: ' + jsonData.message + '\n';
                    } catch (e) {
                        resultDiv.innerHTML += 'Response is not valid JSON: ' + e.message + '\n';
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML += 'Error: ' + error.message + '\n';
                });
            }
            
            function testWithRealFile() {
                const resultDiv = document.getElementById('ajax-result');
                resultDiv.style.display = 'block';
                resultDiv.innerHTML = 'Testing with real file...\n';
                
                // Create a file input
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.accept = '.pdf';
                fileInput.onchange = function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        resultDiv.innerHTML += 'Selected file: ' + file.name + ' (' + file.size + ' bytes)\n';
                        
                        // Create FormData with real file
                        const formData = new FormData();
                        formData.append('action', 'ennu_upload_pdf');
                        formData.append('nonce', 'test_nonce_ennu_ajax_nonce');
                        formData.append('labcorp_pdf', file);
                        
                        // Test the fetch
                        fetch('direct-ajax-handler.php', {
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
}
?> 