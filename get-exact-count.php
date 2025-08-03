<?php
/**
 * Get Exact Biomarker Count from Specific PDF
 */

// Simulate WordPress environment
define( 'ABSPATH', dirname( __FILE__ ) . '/' );

// Include required files
require_once 'includes/services/class-pdf-processor.php';

function getExactBiomarkerCount() {
    $pdf_file = __DIR__ . '/SampleLabCorpResults.pdf';
    
    echo "🔬 Analyzing: SampleLabCorpResults.pdf\n";
    echo "=====================================\n\n";
    
    if ( ! file_exists( $pdf_file ) ) {
        echo "❌ ERROR: PDF file not found!\n";
        return false;
    }
    
    $file_size = filesize( $pdf_file );
    $file_size_mb = round( $file_size / 1024 / 1024, 2 );
    
    echo "✅ File Found: SampleLabCorpResults.pdf\n";
    echo "📏 File Size: {$file_size_mb} MB\n\n";
    
    // Extract text
    $pdf_content = file_get_contents( $pdf_file );
    if ( ! $pdf_content ) {
        echo "❌ ERROR: Unable to read PDF content\n";
        return false;
    }
    
    $text = extractTextFromPdfContent( $pdf_content );
    if ( empty( $text ) ) {
        echo "❌ ERROR: No text extracted from PDF\n";
        return false;
    }
    
    $word_count = str_word_count( $text );
    echo "📖 Text Extracted: {$word_count} words\n\n";
    
    // Parse biomarkers
    $biomarkers = parseLabCorpText( $text );
    
    if ( empty( $biomarkers ) ) {
        echo "❌ ERROR: No biomarkers found in PDF\n";
        return false;
    }
    
    $biomarker_count = count( $biomarkers );
    
    echo "🎯 EXACT TOTAL: {$biomarker_count} BIOMARKERS\n";
    echo "=====================================\n\n";
    
    echo "📋 Extracted Biomarkers:\n";
    foreach ( $biomarkers as $biomarker => $value ) {
        echo "  • " . ucwords( str_replace( '_', ' ', $biomarker ) ) . ": {$value}\n";
    }
    
    echo "\n📊 Summary:\n";
    echo "  • Total Biomarkers: {$biomarker_count}\n";
    echo "  • File Size: {$file_size_mb} MB\n";
    echo "  • Words Extracted: {$word_count}\n";
    
    return $biomarker_count;
}

// Helper functions
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

// Run the analysis
$count = getExactBiomarkerCount();

if ( $count !== false ) {
    echo "\n🎉 ANALYSIS COMPLETE!\n";
    echo "🎯 EXACT TOTAL FROM YOUR PDF: {$count} BIOMARKERS\n";
} else {
    echo "\n💥 ANALYSIS FAILED!\n";
}
?> 