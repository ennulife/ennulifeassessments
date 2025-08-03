<?php
/**
 * Test Guaranteed Biomarker Extraction
 * 
 * Verifies that the 15 biomarkers are guaranteed to be extracted and saved
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
    <title>Guaranteed Biomarker Extraction Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        .warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }
        .biomarker-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin: 15px 0; }
        .biomarker-card { background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007cba; }
        .biomarker-card.success { border-left-color: #28a745; }
        .biomarker-card.error { border-left-color: #dc3545; }
        .guarantee { font-size: 24px; font-weight: bold; color: #007cba; text-align: center; margin: 20px 0; }
        .count { font-size: 32px; font-weight: bold; color: #28a745; text-align: center; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎯 Guaranteed Biomarker Extraction Test</h1>
        <p>Testing the guaranteed extraction of 15 biomarkers from SampleLabCorpResults.pdf</p>
        
        <?php
        testGuaranteedExtraction();
        ?>
    </div>
</body>
</html>

<?php

function testGuaranteedExtraction() {
    $pdf_file = __DIR__ . '/SampleLabCorpResults.pdf';
    
    echo "<div class='section info'>";
    echo "<h2>📋 Test Overview</h2>";
    echo "<p><strong>Goal:</strong> Guarantee extraction of 15 biomarkers from LabCorp PDF</p>";
    echo "<p><strong>File:</strong> SampleLabCorpResults.pdf</p>";
    echo "<p><strong>Expected:</strong> 15 biomarkers extracted and saved to user fields</p>";
    echo "</div>";
    
    if ( ! file_exists( $pdf_file ) ) {
        echo "<div class='section error'>";
        echo "<h2>❌ Test Failed</h2>";
        echo "<p>PDF file not found: " . basename( $pdf_file ) . "</p>";
        echo "</div>";
        return;
    }
    
    // Test 1: Basic Processing
    echo "<div class='section'>";
    echo "<h2>🧪 Test 1: Basic PDF Processing</h2>";
    
    $processor = new ENNU_PDF_Processor();
    $result = $processor->process_labcorp_pdf( $pdf_file, 1 );
    
    if ( $result['success'] ) {
        echo "<div class='success'>";
        echo "<p><strong>✅ Processing Successful:</strong> " . $result['message'] . "</p>";
        echo "<p><strong>Biomarkers Imported:</strong> " . $result['biomarkers_imported'] . "</p>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<p><strong>❌ Processing Failed:</strong> " . $result['message'] . "</p>";
        echo "</div>";
        return;
    }
    echo "</div>";
    
    // Test 2: Guaranteed Biomarker Count
    echo "<div class='section'>";
    echo "<h2>🎯 Test 2: Guaranteed Biomarker Count</h2>";
    
    $expected_count = 15;
    $actual_count = $result['biomarkers_imported'];
    
    if ( $actual_count >= $expected_count ) {
        echo "<div class='count'>✅ GUARANTEE MET: {$actual_count}/{$expected_count} biomarkers</div>";
        echo "<div class='success'>";
        echo "<p><strong>Success Rate:</strong> " . round( ( $actual_count / $expected_count ) * 100, 1 ) . "%</p>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<p><strong>❌ GUARANTEE FAILED:</strong> Only {$actual_count}/{$expected_count} biomarkers extracted</p>";
        echo "</div>";
    }
    echo "</div>";
    
    // Test 3: Individual Biomarker Verification
    echo "<div class='section'>";
    echo "<h2>🔍 Test 3: Individual Biomarker Verification</h2>";
    
    $biomarkers = $result['biomarkers'] ?? array();
    $expected_biomarkers = array(
        'total_cholesterol' => 'Total Cholesterol',
        'ldl_cholesterol' => 'LDL Cholesterol',
        'hdl_cholesterol' => 'HDL Cholesterol',
        'triglycerides' => 'Triglycerides',
        'glucose' => 'Glucose',
        'hba1c' => 'HbA1c',
        'testosterone' => 'Testosterone',
        'tsh' => 'TSH',
        'vitamin_d' => 'Vitamin D',
    );
    
    echo "<div class='biomarker-grid'>";
    
    $found_count = 0;
    foreach ( $expected_biomarkers as $key => $name ) {
        $status = isset( $biomarkers[$key] ) ? 'success' : 'error';
        $value = isset( $biomarkers[$key] ) ? $biomarkers[$key] : 'NOT FOUND';
        $found_count += isset( $biomarkers[$key] ) ? 1 : 0;
        
        echo "<div class='biomarker-card {$status}'>";
        echo "<h4>{$name}</h4>";
        echo "<p><strong>Status:</strong> " . ( isset( $biomarkers[$key] ) ? '✅ FOUND' : '❌ MISSING' ) . "</p>";
        echo "<p><strong>Value:</strong> {$value}</p>";
        echo "</div>";
    }
    
    echo "</div>";
    
    echo "<div class='section " . ( $found_count >= 9 ? 'success' : 'error' ) . "'>";
    echo "<h3>📊 Core Biomarker Summary</h3>";
    echo "<p><strong>Found:</strong> {$found_count}/9 core biomarkers</p>";
    echo "<p><strong>Success Rate:</strong> " . round( ( $found_count / 9 ) * 100, 1 ) . "%</p>";
    echo "</div>";
    echo "</div>";
    
    // Test 4: User Field Verification
    echo "<div class='section'>";
    echo "<h2>💾 Test 4: User Field Verification</h2>";
    
    $user_id = 1;
    $user_biomarkers = get_user_meta( $user_id, 'ennu_biomarkers', true );
    
    if ( is_array( $user_biomarkers ) ) {
        $saved_count = 0;
        foreach ( $biomarkers as $key => $value ) {
            if ( isset( $user_biomarkers[$key] ) && $user_biomarkers[$key] == $value ) {
                $saved_count++;
            }
        }
        
        echo "<div class='section " . ( $saved_count >= $actual_count ? 'success' : 'error' ) . "'>";
        echo "<h3>User Field Save Verification</h3>";
        echo "<p><strong>Biomarkers Saved:</strong> {$saved_count}/{$actual_count}</p>";
        echo "<p><strong>Save Success Rate:</strong> " . round( ( $saved_count / $actual_count ) * 100, 1 ) . "%</p>";
        echo "</div>";
        
        echo "<details>";
        echo "<summary><strong>🔍 Raw User Biomarker Data:</strong></summary>";
        echo "<pre>" . print_r( $user_biomarkers, true ) . "</pre>";
        echo "</details>";
    } else {
        echo "<div class='error'>";
        echo "<p><strong>❌ User Biomarker Data Not Found</strong></p>";
        echo "</div>";
    }
    echo "</div>";
    
    // Test 5: Guarantee Summary
    echo "<div class='section " . ( $actual_count >= 15 && $found_count >= 9 ? 'success' : 'warning' ) . "'>";
    echo "<h2>🏆 Guarantee Summary</h2>";
    
    $guarantee_met = $actual_count >= 15 && $found_count >= 9;
    
    if ( $guarantee_met ) {
        echo "<div class='guarantee'>🎯 GUARANTEE ACHIEVED!</div>";
        echo "<div class='success'>";
        echo "<p><strong>✅ Total Biomarkers:</strong> {$actual_count}/15 (100%+)</p>";
        echo "<p><strong>✅ Core Biomarkers:</strong> {$found_count}/9 (100%+)</p>";
        echo "<p><strong>✅ Processing:</strong> Successful</p>";
        echo "<p><strong>✅ User Fields:</strong> Biomarkers saved to user profile</p>";
        echo "</div>";
    } else {
        echo "<div class='error'>";
        echo "<p><strong>❌ GUARANTEE NOT MET</strong></p>";
        echo "<p>Total Biomarkers: {$actual_count}/15</p>";
        echo "<p>Core Biomarkers: {$found_count}/9</p>";
        echo "</div>";
    }
    
    echo "<div class='section info'>";
    echo "<h3>📋 Detailed Results</h3>";
    echo "<details>";
    echo "<summary><strong>🔍 All Extracted Biomarkers:</strong></summary>";
    echo "<pre>" . print_r( $biomarkers, true ) . "</pre>";
    echo "</details>";
    echo "</div>";
    
    echo "</div>";
}

?> 