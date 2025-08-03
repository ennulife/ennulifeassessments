<?php
/**
 * Test Script for LabCorp PDF Processing
 * 
 * This script tests the PDF processing functionality without requiring WordPress
 */

// Simulate WordPress environment
define( 'ABSPATH', dirname( __FILE__ ) . '/' );
define( 'WP_DEBUG', true );

// Include the PDF processor
require_once 'includes/services/class-pdf-processor.php';

// Test the PDF processor
function test_pdf_processing() {
    echo "🧪 Testing LabCorp PDF Processing...\n\n";
    
    // Test file path
    $test_pdf = __DIR__ . '/test-labcorp.pdf';
    
    if ( ! file_exists( $test_pdf ) ) {
        echo "❌ Test PDF file not found: $test_pdf\n";
        return false;
    }
    
    echo "✅ Test PDF found: $test_pdf\n";
    
    // Create a mock PDF processor (without full WordPress dependencies)
    $processor = new ENNU_PDF_Processor();
    
    // Test the fallback PDF processing
    $result = $processor->process_labcorp_pdf( $test_pdf, 1 );
    
    echo "\n📊 Processing Results:\n";
    echo "Success: " . ( $result['success'] ? '✅ YES' : '❌ NO' ) . "\n";
    echo "Message: " . $result['message'] . "\n";
    
    if ( isset( $result['biomarkers_imported'] ) ) {
        echo "Biomarkers Imported: " . $result['biomarkers_imported'] . "\n";
    }
    
    if ( isset( $result['biomarkers'] ) ) {
        echo "\n📋 Extracted Biomarkers:\n";
        foreach ( $result['biomarkers'] as $key => $value ) {
            echo "  - $key: $value\n";
        }
    }
    
    return $result['success'];
}

// Run the test
$success = test_pdf_processing();

echo "\n" . ( $success ? "🎉 TEST PASSED!" : "💥 TEST FAILED!" ) . "\n"; 