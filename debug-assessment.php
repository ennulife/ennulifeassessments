<?php
// Debug file for assessment submissions
$debug_data = array(
    'timestamp' => date('Y-m-d H:i:s'),
    'message' => 'Assessment debug initialized',
    'data' => array()
);

// Save debug data to file
function save_debug_data($data) {
    $file_path = __DIR__ . '/debug-assessment.log';
    $log_entry = date('Y-m-d H:i:s') . ' - ' . print_r($data, true) . PHP_EOL;
    file_put_contents($file_path, $log_entry, FILE_APPEND | LOCK_EX);
}

// Initialize debug file
save_debug_data($debug_data);
?> 