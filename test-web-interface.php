<?php
/**
 * Web-based LabCorp PDF Testing Interface
 * 
 * Access this file through your browser to test the PDF processing
 */

// Simulate WordPress environment
define( 'ABSPATH', dirname( __FILE__ ) . '/' );
define( 'WP_DEBUG', true );

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
    <title>LabCorp PDF Testing Interface</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        .test-result { margin: 10px 0; padding: 10px; border-radius: 3px; }
        .btn { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #005a87; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 LabCorp PDF Testing Interface</h1>
        <p>This interface tests the LabCorp PDF upload functionality with the real sample file.</p>
        
        <?php
        // Run tests if requested
        if ( isset( $_GET['run_tests'] ) ) {
            runTests();
        } else {
            ?>
            <div class="test-section info">
                <h3>Ready to Test</h3>
                <p>Click the button below to run comprehensive tests on the LabCorp PDF processing system.</p>
                <a href="?run_tests=1" class="btn">Run All Tests</a>
            </div>
            <?php
        }
        ?>
    </div>
</body>
</html>

<?php

function runTests() {
    echo "<h2>📊 Test Results</h2>";
    
    $tests = [
        'testFileExists' => 'File Exists',
        'testFileSize' => 'File Size Validation',
        'testFileType' => 'File Type Validation',
        'testTextExtraction' => 'Text Extraction',
        'testBiomarkerExtraction' => 'Biomarker Extraction',
        'testErrorHandling' => 'Error Handling',
        'testPerformance' => 'Performance Test'
    ];
    
    $passed = 0;
    $total = count( $tests );
    $results = [];
    
    foreach ( $tests as $test => $description ) {
        echo "<div class='test-section'>";
        echo "<h3>Test: $description</h3>";
        
        $result = call_user_func( $test );
        $results[$test] = $result;
        
        if ( $result['success'] ) {
            echo "<div class='test-result success'>✅ PASSED: {$result['message']}</div>";
            $passed++;
        } else {
            echo "<div class='test-result error'>❌ FAILED: {$result['message']}</div>";
        }
        
        if ( isset( $result['details'] ) ) {
            echo "<pre>" . htmlspecialchars( $result['details'] ) . "</pre>";
        }
        
        echo "</div>";
    }
    
    // Summary
    echo "<div class='test-section " . ( $passed === $total ? 'success' : 'error' ) . "'>";
    echo "<h3>📊 Test Summary</h3>";
    echo "<p><strong>Passed:</strong> $passed/$total tests</p>";
    echo "<p><strong>Success Rate:</strong> " . round( ( $passed / $total ) * 100, 1 ) . "%</p>";
    
    if ( $passed === $total ) {
        echo "<p><strong>🎉 ALL TESTS PASSED!</strong> The LabCorp PDF upload system is working correctly.</p>";
    } else {
        echo "<p><strong>⚠️ Some tests failed.</strong> Please review the results above.</p>";
    }
    echo "</div>";
    
    // Detailed results
    echo "<div class='test-section info'>";
    echo "<h3>📋 Detailed Results</h3>";
    foreach ( $results as $test => $result ) {
        $status = $result['success'] ? '✅' : '❌';
        echo "<p><strong>$status $test:</strong> {$result['message']}</p>";
    }
    echo "</div>";
}

function testFileExists() {
    $test_pdf = __DIR__ . '/SampleLabCorpResults.pdf';
    
    if ( ! file_exists( $test_pdf ) ) {
        return [
            'success' => false,
            'message' => 'Test PDF file not found: ' . basename( $test_pdf )
        ];
    }
    
    return [
        'success' => true,
        'message' => 'Test PDF file found: ' . basename( $test_pdf ) . ' (' . round( filesize( $test_pdf ) / 1024 / 1024, 2 ) . 'MB)'
    ];
}

function testFileSize() {
    $test_pdf = __DIR__ . '/SampleLabCorpResults.pdf';
    $size = filesize( $test_pdf );
    $max_size = 10 * 1024 * 1024; // 10MB
    
    if ( $size > $max_size ) {
        return [
            'success' => false,
            'message' => "File too large: " . round( $size / 1024 / 1024, 2 ) . "MB (max 10MB)"
        ];
    }
    
    return [
        'success' => true,
        'message' => "File size OK: " . round( $size / 1024 / 1024, 2 ) . "MB"
    ];
}

function testFileType() {
    $test_pdf = __DIR__ . '/SampleLabCorpResults.pdf';
    $finfo = finfo_open( FILEINFO_MIME_TYPE );
    $mime_type = finfo_file( $finfo, $test_pdf );
    finfo_close( $finfo );
    
    if ( $mime_type !== 'application/pdf' ) {
        return [
            'success' => false,
            'message' => "Invalid file type: $mime_type (expected application/pdf)"
        ];
    }
    
    return [
        'success' => true,
        'message' => "File type OK: $mime_type"
    ];
}

function testTextExtraction() {
    $test_pdf = __DIR__ . '/SampleLabCorpResults.pdf';
    $pdf_content = file_get_contents( $test_pdf );
    
    if ( ! $pdf_content ) {
        return [
            'success' => false,
            'message' => "Unable to read PDF content"
        ];
    }
    
    // Test our fallback extraction method
    $text = extractTextFromPdfContent( $pdf_content );
    
    if ( empty( $text ) ) {
        return [
            'success' => false,
            'message' => "No text extracted from PDF"
        ];
    }
    
    $word_count = str_word_count( $text );
    $char_count = strlen( $text );
    
    return [
        'success' => true,
        'message' => "Text extraction successful: $word_count words, $char_count characters",
        'details' => "First 500 characters:\n" . substr( $text, 0, 500 ) . "..."
    ];
}

function testBiomarkerExtraction() {
    $test_pdf = __DIR__ . '/SampleLabCorpResults.pdf';
    $pdf_content = file_get_contents( $test_pdf );
    $text = extractTextFromPdfContent( $pdf_content );
    
    // Test biomarker parsing
    $biomarkers = parseLabCorpText( $text );
    
    if ( empty( $biomarkers ) ) {
        return [
            'success' => false,
            'message' => "No biomarkers extracted from text"
        ];
    }
    
    $count = count( $biomarkers );
    $biomarker_list = implode( ', ', array_keys( $biomarkers ) );
    
    return [
        'success' => true,
        'message' => "Biomarker extraction successful: $count biomarkers found",
        'details' => "Extracted biomarkers:\n" . $biomarker_list
    ];
}

function testErrorHandling() {
    // Test with non-existent file
    $processor = new ENNU_PDF_Processor();
    $result = $processor->process_labcorp_pdf( '/non/existent/file.pdf', 1 );
    
    if ( ! $result['success'] ) {
        return [
            'success' => true,
            'message' => "Error handling working correctly"
        ];
    }
    
    return [
        'success' => false,
        'message' => "Error handling failed - should have returned error"
    ];
}

function testPerformance() {
    $test_pdf = __DIR__ . '/SampleLabCorpResults.pdf';
    $processor = new ENNU_PDF_Processor();
    
    $start_time = microtime( true );
    $result = $processor->process_labcorp_pdf( $test_pdf, 1 );
    $end_time = microtime( true );
    $execution_time = $end_time - $start_time;
    
    if ( $execution_time > 5.0 ) { // 5 seconds max
        return [
            'success' => false,
            'message' => "Performance too slow: " . round( $execution_time, 2 ) . " seconds"
        ];
    }
    
    return [
        'success' => true,
        'message' => "Performance OK: " . round( $execution_time, 2 ) . " seconds"
    ];
}

// Helper functions (copied from processor)
function extractTextFromPdfContent( $pdf_content ) {
    $text = '';
    
    // Remove PDF header and metadata
    $content = preg_replace( '/^.*?stream\s*/s', '', $pdf_content );
    $content = preg_replace( '/endstream.*$/s', '', $content );
    
    // Extract text between parentheses (common PDF text format)
    preg_match_all( '/\(([^)]+)\)/', $content, $matches );
    
    if ( ! empty( $matches[1] ) ) {
        $text = implode( ' ', $matches[1] );
    }
    
    // Clean up the text
    $text = preg_replace( '/[^\w\s\.\-\(\)]/', ' ', $text );
    $text = preg_replace( '/\s+/', ' ', $text );
    $text = trim( $text );
    
    return $text;
}

function parseLabCorpText( $text ) {
    $biomarkers = [];
    $lines = explode( "\n", $text );
    
    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) continue;
        
        // Look for biomarker patterns
        foreach ( getComprehensiveBiomarkerMap() as $labcorp_name => $ennu_key ) {
            if ( stripos( $line, $labcorp_name ) !== false ) {
                $value = extractBiomarkerValue( $line );
                if ( $value !== null ) {
                    $biomarkers[$ennu_key] = $value;
                }
            }
        }
    }
    
    return $biomarkers;
}

function extractBiomarkerValue( $line ) {
    // Common patterns for biomarker values
    $patterns = [
        '/\b(\d+\.?\d*)\s*(mg\/dL|ng\/dL|mIU\/L|ng\/mL|pg\/mL|pmol\/L|mmol\/L|μmol\/L|g\/dL|%)\b/i',
        '/\b(\d+\.?\d*)\s*(mg\/dl|ng\/dl|miu\/l|ng\/ml|pg\/ml|pmol\/l|mmol\/l|μmol\/l|g\/dl)\b/i',
    ];
    
    foreach ( $patterns as $pattern ) {
        if ( preg_match( $pattern, $line, $matches ) ) {
            return floatval( $matches[1] );
        }
    }
    
    return null;
}

function getComprehensiveBiomarkerMap() {
    return [
        // Cardiovascular
        'Total Cholesterol' => 'total_cholesterol',
        'LDL' => 'ldl_cholesterol',
        'HDL' => 'hdl_cholesterol',
        'Triglycerides' => 'triglycerides',
        'ApoB' => 'apob',
        'Lp(a)' => 'lp_a',
        
        // Metabolic
        'Glucose' => 'glucose',
        'HbA1c' => 'hba1c',
        'Insulin' => 'insulin',
        'C-Peptide' => 'c_peptide',
        
        // Hormones
        'Testosterone' => 'testosterone',
        'Free Testosterone' => 'free_testosterone',
        'Estradiol' => 'estradiol',
        'Progesterone' => 'progesterone',
        'DHEA-S' => 'dhea_s',
        'Cortisol' => 'cortisol',
        'TSH' => 'tsh',
        'Free T4' => 'free_t4',
        'Free T3' => 'free_t3',
        
        // Vitamins
        'Vitamin D' => 'vitamin_d',
        'Vitamin B12' => 'vitamin_b12',
        'Folate' => 'folate',
        
        // Minerals
        'Iron' => 'iron',
        'Ferritin' => 'ferritin',
        'Zinc' => 'zinc',
        'Magnesium' => 'magnesium',
        
        // Inflammation
        'CRP' => 'crp',
        'hs-CRP' => 'hs_crp',
        'ESR' => 'esr',
        
        // Kidney
        'Creatinine' => 'creatinine',
        'BUN' => 'bun',
        'eGFR' => 'egfr',
        
        // Liver
        'ALT' => 'alt',
        'AST' => 'ast',
        'Alkaline Phosphatase' => 'alkaline_phosphatase',
        'Bilirubin' => 'bilirubin',
        
        // Blood Count
        'Hemoglobin' => 'hemoglobin',
        'Hematocrit' => 'hematocrit',
        'WBC' => 'wbc',
        'RBC' => 'rbc',
        'Platelets' => 'platelets',
    ];
}

?> 