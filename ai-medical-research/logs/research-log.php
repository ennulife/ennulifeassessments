<?php
/**
 * AI Medical Team Research Logging System
 * 
 * Comprehensive logging for AI medical team research activities
 * Tracks research progress, errors, and completion status
 * 
 * @package ENNU_Life
 * @version 62.31.0
 */

class ENNU_Research_Logger {
    
    private $log_file;
    private $log_level;
    private $session_id;
    
    const LOG_LEVELS = array(
        'DEBUG' => 0,
        'INFO' => 1,
        'WARNING' => 2,
        'ERROR' => 3,
        'CRITICAL' => 4
    );
    
    public function __construct($log_file = null, $log_level = 'INFO') {
        $this->log_level = $log_level;
        $this->session_id = uniqid('research_', true);
        
        if ($log_file === null) {
            $log_dir = dirname(__FILE__);
            $this->log_file = $log_dir . '/research-' . date('Y-m-d') . '.log';
        } else {
            $this->log_file = $log_file;
        }
        
        // Ensure log directory exists
        $log_dir = dirname($this->log_file);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $this->log('INFO', 'Research Logger initialized', array(
            'session_id' => $this->session_id,
            'log_file' => $this->log_file,
            'log_level' => $this->log_level
        ));
    }
    
    /**
     * Log a message with specified level
     */
    public function log($level, $message, $context = array()) {
        if (self::LOG_LEVELS[$level] < self::LOG_LEVELS[$this->log_level]) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = array(
            'timestamp' => $timestamp,
            'level' => $level,
            'session_id' => $this->session_id,
            'message' => $message,
            'context' => $context
        );
        
        $log_line = json_encode($log_entry) . "\n";
        file_put_contents($this->log_file, $log_line, FILE_APPEND | LOCK_EX);
        
        // Also output to console for immediate feedback
        echo "[{$timestamp}] [{$level}] {$message}\n";
    }
    
    /**
     * Log research phase start
     */
    public function logPhaseStart($phase, $specialist = null, $biomarkers = array()) {
        $this->log('INFO', "Research phase started", array(
            'phase' => $phase,
            'specialist' => $specialist,
            'biomarkers' => $biomarkers,
            'action' => 'phase_start'
        ));
    }
    
    /**
     * Log research phase completion
     */
    public function logPhaseComplete($phase, $results = array()) {
        $this->log('INFO', "Research phase completed", array(
            'phase' => $phase,
            'results' => $results,
            'action' => 'phase_complete'
        ));
    }
    
    /**
     * Log biomarker research start
     */
    public function logBiomarkerResearch($biomarker, $specialist, $method = 'standard') {
        $this->log('INFO', "Biomarker research started", array(
            'biomarker' => $biomarker,
            'specialist' => $specialist,
            'method' => $method,
            'action' => 'biomarker_research_start'
        ));
    }
    
    /**
     * Log biomarker research completion
     */
    public function logBiomarkerComplete($biomarker, $reference_ranges = array(), $evidence_level = 'B') {
        $this->log('INFO', "Biomarker research completed", array(
            'biomarker' => $biomarker,
            'reference_ranges' => $reference_ranges,
            'evidence_level' => $evidence_level,
            'action' => 'biomarker_research_complete'
        ));
    }
    
    /**
     * Log validation process
     */
    public function logValidation($biomarker, $validators = array(), $status = 'pending') {
        $this->log('INFO', "Validation process", array(
            'biomarker' => $biomarker,
            'validators' => $validators,
            'status' => $status,
            'action' => 'validation'
        ));
    }
    
    /**
     * Log error with context
     */
    public function logError($message, $context = array(), $exception = null) {
        $error_data = array(
            'error_message' => $message,
            'context' => $context
        );
        
        if ($exception) {
            $error_data['exception'] = array(
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            );
        }
        
        $this->log('ERROR', $message, $error_data);
    }
    
    /**
     * Get research summary
     */
    public function getResearchSummary() {
        if (!file_exists($this->log_file)) {
            return array('error' => 'Log file not found');
        }
        
        $log_entries = file($this->log_file, FILE_IGNORE_NEW_LINES);
        $summary = array(
            'total_entries' => count($log_entries),
            'phases_completed' => 0,
            'biomarkers_researched' => 0,
            'errors' => 0,
            'session_id' => $this->session_id
        );
        
        foreach ($log_entries as $entry) {
            $data = json_decode($entry, true);
            if (!$data) continue;
            
            if (isset($data['context']['action'])) {
                switch ($data['context']['action']) {
                    case 'phase_complete':
                        $summary['phases_completed']++;
                        break;
                    case 'biomarker_research_complete':
                        $summary['biomarkers_researched']++;
                        break;
                }
            }
            
            if ($data['level'] === 'ERROR') {
                $summary['errors']++;
            }
        }
        
        return $summary;
    }
    
    /**
     * Export log data for analysis
     */
    public function exportLogData($format = 'json') {
        if (!file_exists($this->log_file)) {
            return array('error' => 'Log file not found');
        }
        
        $log_entries = file($this->log_file, FILE_IGNORE_NEW_LINES);
        $data = array();
        
        foreach ($log_entries as $entry) {
            $data[] = json_decode($entry, true);
        }
        
        if ($format === 'csv') {
            return $this->convertToCSV($data);
        }
        
        return $data;
    }
    
    private function convertToCSV($data) {
        if (empty($data)) return '';
        
        $csv = array();
        $headers = array('timestamp', 'level', 'session_id', 'message', 'action', 'biomarker', 'specialist', 'phase');
        $csv[] = implode(',', $headers);
        
        foreach ($data as $entry) {
            $row = array();
            foreach ($headers as $header) {
                $value = '';
                if ($header === 'action' && isset($entry['context']['action'])) {
                    $value = $entry['context']['action'];
                } elseif ($header === 'biomarker' && isset($entry['context']['biomarker'])) {
                    $value = $entry['context']['biomarker'];
                } elseif ($header === 'specialist' && isset($entry['context']['specialist'])) {
                    $value = $entry['context']['specialist'];
                } elseif ($header === 'phase' && isset($entry['context']['phase'])) {
                    $value = $entry['context']['phase'];
                } elseif (isset($entry[$header])) {
                    $value = $entry[$header];
                }
                $row[] = '"' . str_replace('"', '""', $value) . '"';
            }
            $csv[] = implode(',', $row);
        }
        
        return implode("\n", $csv);
    }
}

// Initialize logger for immediate use
$research_logger = new ENNU_Research_Logger();
?> 