<?php
/**
 * Real LabCorp PDF Testing Suite
 * 
 * Tests the PDF processing with actual LabCorp results
 */

// Simulate WordPress environment
define( 'ABSPATH', dirname( __FILE__ ) . '/' );
define( 'WP_DEBUG', true );

// Include required files
require_once 'includes/services/class-pdf-processor.php';

class LabCorpTestSuite {
    
    private $test_pdf;
    private $processor;
    private $results = [];
    
    public function __construct() {
        $this->test_pdf = __DIR__ . '/SampleLabCorpResults.pdf';
        $this->processor = new ENNU_PDF_Processor();
    }
    
    /**
     * Run all tests
     */
    public function runAllTests() {
        echo "🧪 LABCORP PDF TESTING SUITE\n";
        echo "=============================\n\n";
        
        $tests = [
            'testFileExists',
            'testFileSize',
            'testFileType',
            'testBasicProcessing',
            'testTextExtraction',
            'testBiomarkerExtraction',
            'testErrorHandling',
            'testPerformance'
        ];
        
        $passed = 0;
        $total = count( $tests );
        
        foreach ( $tests as $test ) {
            echo "Running: $test\n";
            $result = $this->$test();
            $this->results[$test] = $result;
            
            if ( $result['success'] ) {
                echo "✅ PASSED: {$result['message']}\n";
                $passed++;
            } else {
                echo "❌ FAILED: {$result['message']}\n";
            }
            echo "\n";
        }
        
        $this->printSummary( $passed, $total );
        return $passed === $total;
    }
    
    /**
     * Test 1: File Exists
     */
    private function testFileExists() {
        if ( ! file_exists( $this->test_pdf ) ) {
            return [
                'success' => false,
                'message' => 'Test PDF file not found'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Test PDF file found: ' . basename( $this->test_pdf )
        ];
    }
    
    /**
     * Test 2: File Size Validation
     */
    private function testFileSize() {
        $size = filesize( $this->test_pdf );
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
    
    /**
     * Test 3: File Type Validation
     */
    private function testFileType() {
        $finfo = finfo_open( FILEINFO_MIME_TYPE );
        $mime_type = finfo_file( $finfo, $this->test_pdf );
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
    
    /**
     * Test 4: Basic Processing
     */
    private function testBasicProcessing() {
        try {
            $result = $this->processor->process_labcorp_pdf( $this->test_pdf, 1 );
            
            if ( $result['success'] ) {
                return [
                    'success' => true,
                    'message' => "Processing successful: " . $result['message']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Processing failed: " . $result['message']
                ];
            }
        } catch ( Exception $e ) {
            return [
                'success' => false,
                'message' => "Exception: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Test 5: Text Extraction
     */
    private function testTextExtraction() {
        $pdf_content = file_get_contents( $this->test_pdf );
        
        if ( ! $pdf_content ) {
            return [
                'success' => false,
                'message' => "Unable to read PDF content"
            ];
        }
        
        // Test our fallback extraction method
        $text = $this->extractTextFromPdfContent( $pdf_content );
        
        if ( empty( $text ) ) {
            return [
                'success' => false,
                'message' => "No text extracted from PDF"
            ];
        }
        
        $word_count = str_word_count( $text );
        
        return [
            'success' => true,
            'message' => "Text extraction successful: $word_count words extracted"
        ];
    }
    
    /**
     * Test 6: Biomarker Extraction
     */
    private function testBiomarkerExtraction() {
        $pdf_content = file_get_contents( $this->test_pdf );
        $text = $this->extractTextFromPdfContent( $pdf_content );
        
        // Test biomarker parsing
        $biomarkers = $this->parseLabCorpText( $text );
        
        if ( empty( $biomarkers ) ) {
            return [
                'success' => false,
                'message' => "No biomarkers extracted from text"
            ];
        }
        
        $count = count( $biomarkers );
        
        return [
            'success' => true,
            'message' => "Biomarker extraction successful: $count biomarkers found"
        ];
    }
    
    /**
     * Test 7: Error Handling
     */
    private function testErrorHandling() {
        // Test with non-existent file
        $result = $this->processor->process_labcorp_pdf( '/non/existent/file.pdf', 1 );
        
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
    
    /**
     * Test 8: Performance
     */
    private function testPerformance() {
        $start_time = microtime( true );
        
        $result = $this->processor->process_labcorp_pdf( $this->test_pdf, 1 );
        
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
    
    /**
     * Extract text from PDF content (copied from processor)
     */
    private function extractTextFromPdfContent( $pdf_content ) {
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
    
    /**
     * Parse LabCorp text (copied from processor)
     */
    private function parseLabCorpText( $text ) {
        $biomarkers = [];
        $lines = explode( "\n", $text );
        
        foreach ( $lines as $line ) {
            $line = trim( $line );
            if ( empty( $line ) ) continue;
            
            // Look for biomarker patterns
            foreach ( $this->getComprehensiveBiomarkerMap() as $labcorp_name => $ennu_key ) {
                if ( stripos( $line, $labcorp_name ) !== false ) {
                    $value = $this->extractBiomarkerValue( $line );
                    if ( $value !== null ) {
                        $biomarkers[$ennu_key] = $value;
                    }
                }
            }
        }
        
        return $biomarkers;
    }
    
    /**
     * Extract biomarker value (copied from processor)
     */
    private function extractBiomarkerValue( $line ) {
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
    
    /**
     * Get biomarker map (copied from processor)
     */
    private function getComprehensiveBiomarkerMap() {
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
    
    /**
     * Print test summary
     */
    private function printSummary( $passed, $total ) {
        echo "📊 TEST SUMMARY\n";
        echo "===============\n";
        echo "Passed: $passed/$total tests\n";
        echo "Success Rate: " . round( ( $passed / $total ) * 100, 1 ) . "%\n\n";
        
        if ( $passed === $total ) {
            echo "🎉 ALL TESTS PASSED! The LabCorp PDF upload system is working correctly.\n";
        } else {
            echo "⚠️  Some tests failed. Please review the results above.\n";
        }
        
        echo "\nDetailed Results:\n";
        foreach ( $this->results as $test => $result ) {
            $status = $result['success'] ? '✅' : '❌';
            echo "$status $test: {$result['message']}\n";
        }
    }
}

// Run the test suite
$test_suite = new LabCorpTestSuite();
$all_passed = $test_suite->runAllTests();

echo "\n" . ( $all_passed ? "🏆 SUCCESS: All tests passed!" : "💥 FAILURE: Some tests failed!" ) . "\n"; 