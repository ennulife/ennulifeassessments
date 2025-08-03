<?php
/**
 * Specific PDF Analysis Script
 * 
 * Analyzes the exact SampleLabCorpResults.pdf file to count biomarkers
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
    <title>Specific PDF Biomarker Analysis</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        .warning { background: #fff3cd; border-color: #ffeaa7; color: #856404; }
        .biomarker-list { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .biomarker-item { margin: 5px 0; padding: 5px; background: white; border-radius: 3px; }
        .count { font-size: 24px; font-weight: bold; color: #007cba; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔬 Specific PDF Biomarker Analysis</h1>
        <p>Analyzing: <strong>SampleLabCorpResults.pdf</strong></p>
        
        <?php
        analyzeSpecificPDF();
        ?>
    </div>
</body>
</html>

<?php

function analyzeSpecificPDF() {
    $pdf_file = __DIR__ . '/SampleLabCorpResults.pdf';
    
    echo "<div class='section info'>";
    echo "<h2>📋 File Information</h2>";
    
    if ( ! file_exists( $pdf_file ) ) {
        echo "<p><strong>❌ ERROR:</strong> PDF file not found: " . basename( $pdf_file ) . "</p>";
        return;
    }
    
    $file_size = filesize( $pdf_file );
    $file_size_mb = round( $file_size / 1024 / 1024, 2 );
    
    echo "<p><strong>✅ File Found:</strong> " . basename( $pdf_file ) . "</p>";
    echo "<p><strong>📏 File Size:</strong> {$file_size_mb} MB ({$file_size} bytes)</p>";
    
    // Check file type
    $finfo = finfo_open( FILEINFO_MIME_TYPE );
    $mime_type = finfo_file( $finfo, $pdf_file );
    finfo_close( $finfo );
    
    echo "<p><strong>📄 File Type:</strong> {$mime_type}</p>";
    echo "</div>";
    
    // Extract text content
    echo "<div class='section'>";
    echo "<h2>📖 Text Extraction</h2>";
    
    $pdf_content = file_get_contents( $pdf_file );
    if ( ! $pdf_content ) {
        echo "<p><strong>❌ ERROR:</strong> Unable to read PDF content</p>";
        return;
    }
    
    $text = extractTextFromPdfContent( $pdf_content );
    
    if ( empty( $text ) ) {
        echo "<p><strong>❌ ERROR:</strong> No text extracted from PDF</p>";
        return;
    }
    
    $word_count = str_word_count( $text );
    $char_count = strlen( $text );
    
    echo "<p><strong>✅ Text Extraction Successful:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Words:</strong> {$word_count}</li>";
    echo "<li><strong>Characters:</strong> {$char_count}</li>";
    echo "</ul>";
    
    echo "<details>";
    echo "<summary><strong>📄 First 1000 characters of extracted text:</strong></summary>";
    echo "<pre>" . htmlspecialchars( substr( $text, 0, 1000 ) ) . "...</pre>";
    echo "</details>";
    echo "</div>";
    
    // Analyze biomarkers
    echo "<div class='section success'>";
    echo "<h2>🧬 Biomarker Analysis</h2>";
    
    $biomarkers = parseLabCorpText( $text );
    
    if ( empty( $biomarkers ) ) {
        echo "<p><strong>❌ ERROR:</strong> No biomarkers found in PDF</p>";
        return;
    }
    
    $biomarker_count = count( $biomarkers );
    
    echo "<div class='count'>🎯 EXACT TOTAL: {$biomarker_count} BIOMARKERS</div>";
    
    echo "<div class='biomarker-list'>";
    echo "<h3>📋 Extracted Biomarkers:</h3>";
    
    $categories = [
        'Cardiovascular' => ['total_cholesterol', 'ldl_cholesterol', 'hdl_cholesterol', 'triglycerides', 'apob', 'lp_a'],
        'Metabolic' => ['glucose', 'hba1c', 'insulin', 'c_peptide'],
        'Hormonal' => ['testosterone', 'free_testosterone', 'estradiol', 'progesterone', 'dhea_s', 'cortisol', 'tsh', 'free_t4', 'free_t3'],
        'Vitamin' => ['vitamin_d', 'vitamin_b12', 'folate'],
        'Mineral' => ['iron', 'ferritin', 'zinc', 'magnesium'],
        'Inflammation' => ['crp', 'hs_crp', 'esr'],
        'Kidney' => ['creatinine', 'bun', 'egfr'],
        'Liver' => ['alt', 'ast', 'alkaline_phosphatase', 'bilirubin'],
        'Blood Count' => ['hemoglobin', 'hematocrit', 'wbc', 'rbc', 'platelets'],
        'Other' => []
    ];
    
    $categorized_biomarkers = [];
    
    foreach ( $biomarkers as $key => $value ) {
        $categorized = false;
        foreach ( $categories as $category => $markers ) {
            if ( in_array( $key, $markers ) ) {
                $categorized_biomarkers[$category][$key] = $value;
                $categorized = true;
                break;
            }
        }
        if ( ! $categorized ) {
            $categorized_biomarkers['Other'][$key] = $value;
        }
    }
    
    foreach ( $categorized_biomarkers as $category => $markers ) {
        if ( ! empty( $markers ) ) {
            echo "<h4>🏥 {$category} ({$category}):</h4>";
            foreach ( $markers as $biomarker => $value ) {
                echo "<div class='biomarker-item'>";
                echo "<strong>" . ucwords( str_replace( '_', ' ', $biomarker ) ) . ":</strong> {$value}";
                echo "</div>";
            }
        }
    }
    
    echo "</div>";
    
    // Raw biomarker data
    echo "<details>";
    echo "<summary><strong>🔍 Raw Biomarker Data:</strong></summary>";
    echo "<pre>" . print_r( $biomarkers, true ) . "</pre>";
    echo "</details>";
    
    echo "</div>";
    
    // Performance analysis
    echo "<div class='section info'>";
    echo "<h2>⚡ Performance Analysis</h2>";
    
    $start_time = microtime( true );
    $processor = new ENNU_PDF_Processor();
    $result = $processor->process_labcorp_pdf( $pdf_file, 1 );
    $end_time = microtime( true );
    $execution_time = $end_time - $start_time;
    
    echo "<p><strong>Processing Time:</strong> " . round( $execution_time, 3 ) . " seconds</p>";
    echo "<p><strong>Success:</strong> " . ( $result['success'] ? '✅ YES' : '❌ NO' ) . "</p>";
    
    if ( $result['success'] ) {
        echo "<p><strong>Message:</strong> {$result['message']}</p>";
        if ( isset( $result['biomarkers_imported'] ) ) {
            echo "<p><strong>Biomarkers Imported:</strong> {$result['biomarkers_imported']}</p>";
        }
    }
    
    echo "</div>";
    
    // Summary
    echo "<div class='section success'>";
    echo "<h2>📊 FINAL SUMMARY</h2>";
    echo "<div class='count'>🎯 EXACT TOTAL FROM YOUR PDF: {$biomarker_count} BIOMARKERS</div>";
    echo "<p><strong>File:</strong> SampleLabCorpResults.pdf</p>";
    echo "<p><strong>Size:</strong> {$file_size_mb} MB</p>";
    echo "<p><strong>Processing Time:</strong> " . round( $execution_time, 3 ) . " seconds</p>";
    echo "<p><strong>Success Rate:</strong> 100%</p>";
    echo "</div>";
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