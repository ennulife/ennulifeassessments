<?php
/**
 * Test AI Medical Team Research Logger
 * 
 * Simple test script to verify logging system functionality
 * 
 * @package ENNU_Life
 * @version 62.31.0
 */

// Load research logger
require_once 'logs/research-log.php';

echo "🧬 Testing AI Medical Team Research Logger\n";
echo "==========================================\n\n";

try {
    // Test basic logging
    echo "Testing basic logging...\n";
    $research_logger->log('INFO', 'Test message', array('test' => true));
    
    // Test phase logging
    echo "Testing phase logging...\n";
    $research_logger->logPhaseStart('preparation', 'test_specialist', array('glucose', 'hba1c'));
    $research_logger->logPhaseComplete('preparation', array('status' => 'success'));
    
    // Test biomarker logging
    echo "Testing biomarker logging...\n";
    $research_logger->logBiomarkerResearch('glucose', 'dr-elena-harmonix', 'standard');
    $research_logger->logBiomarkerComplete('glucose', array(
        'normal_low' => 70,
        'normal_high' => 100,
        'unit' => 'mg/dL'
    ), 'A');
    
    // Test validation logging
    echo "Testing validation logging...\n";
    $research_logger->logValidation('glucose', array('dr-orion-nexus'), 'completed');
    
    // Test error logging
    echo "Testing error logging...\n";
    $research_logger->logError('Test error message', array('context' => 'test'));
    
    // Get summary
    echo "\nGetting research summary...\n";
    $summary = $research_logger->getResearchSummary();
    
    echo "✅ Logger test completed successfully!\n\n";
    echo "📊 Test Summary:\n";
    echo "   - Total log entries: " . $summary['total_entries'] . "\n";
    echo "   - Phases completed: " . $summary['phases_completed'] . "\n";
    echo "   - Biomarkers researched: " . $summary['biomarkers_researched'] . "\n";
    echo "   - Errors encountered: " . $summary['errors'] . "\n";
    echo "   - Session ID: " . $summary['session_id'] . "\n";
    
    echo "\n📁 Log file created: " . dirname(__FILE__) . "/logs/research-" . date('Y-m-d') . ".log\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

echo "\n✨ Logger test completed successfully!\n";
?> 