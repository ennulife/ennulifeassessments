<?php
/**
 * AI Medical Team Research Starter Script
 * 
 * Simple script to initiate AI medical team research with logging
 * Run this from terminal to start the research process
 * 
 * @package ENNU_Life
 * @version 62.31.0
 */

// Load WordPress
require_once '../../../wp-load.php';

// Load research logger
require_once 'logs/research-log.php';

// Ensure we're in the right context
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
}

echo "🧬 ENNU Life AI Medical Team Research System\n";
echo "=============================================\n";
echo "Starting research process...\n\n";

try {
    // Initialize research coordinator
    require_once 'research-coordinator.php';
    
    $coordinator = new ENNU_AI_Research_Coordinator();
    
    if (!$coordinator) {
        throw new Exception("Failed to initialize research coordinator");
    }
    
    echo "✅ Research coordinator initialized successfully\n";
    
    // Start research process
    echo "\n🚀 Starting AI Medical Team Research Process...\n";
    echo "=============================================\n\n";
    
    $results = $coordinator->startResearch();
    
    echo "\n✅ Research process completed successfully!\n";
    echo "📊 Results summary:\n";
    echo "   - Phases completed: " . count($results['phases']) . "\n";
    echo "   - Biomarkers researched: " . count($results['biomarkers']) . "\n";
    echo "   - Specialists involved: " . count($results['specialists']) . "\n";
    echo "   - Total time: " . $results['duration'] . " seconds\n";
    
    // Get research summary from logger
    $summary = $research_logger->getResearchSummary();
    echo "\n📋 Log Summary:\n";
    echo "   - Total log entries: " . $summary['total_entries'] . "\n";
    echo "   - Phases completed: " . $summary['phases_completed'] . "\n";
    echo "   - Biomarkers researched: " . $summary['biomarkers_researched'] . "\n";
    echo "   - Errors encountered: " . $summary['errors'] . "\n";
    echo "   - Session ID: " . $summary['session_id'] . "\n";
    
    echo "\n🎯 Research process completed successfully!\n";
    echo "Log file: " . dirname(__FILE__) . "/logs/research-" . date('Y-m-d') . ".log\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    if (isset($research_logger)) {
        $research_logger->logError("Research process failed", array(), $e);
    }
    
    exit(1);
}

echo "\n✨ AI Medical Team Research Process Complete!\n";
?> 