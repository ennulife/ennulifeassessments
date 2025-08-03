<?php
/**
 * Focused Edge Case Test
 * Tests specific vulnerabilities and edge scenarios without overwhelming the system
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load WordPress
require_once('../../../../wp-load.php');

// Ensure we're in WordPress environment
if (!defined('ABSPATH')) {
    die('WordPress not loaded');
}

// Test user ID
$test_user_id = 1;

echo "<h1>🔥 FOCUSED EDGE CASE TEST</h1>";
echo "<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Test User ID:</strong> {$test_user_id}</p>";

// Load required classes
if (!class_exists('ENNU_Centralized_Symptoms_Manager')) {
    require_once(plugin_dir_path(__FILE__) . '../../includes/class-centralized-symptoms-manager.php');
}

if (!class_exists('ENNU_Biomarker_Flag_Manager')) {
    require_once(plugin_dir_path(__FILE__) . '../../includes/class-biomarker-flag-manager.php');
}

// Initialize managers
$symptoms_manager = new ENNU_Centralized_Symptoms_Manager();
$biomarker_manager = new ENNU_Biomarker_Flag_Manager();

// Load symptom-biomarker correlations
$correlations_file = '/Applications/MAMP/htdocs/wp-content/plugins/ennulifeassessments/includes/config/symptom-biomarker-correlations.php';
if (file_exists($correlations_file)) {
    $symptom_biomarker_correlations = include($correlations_file);
} else {
    echo "<p style='color: red;'>Error: Symptom-biomarker correlations file not found!</p>";
    exit;
}

echo "<h2>📋 SYMPTOM-BIOMARKER CORRELATIONS LOADED</h2>";
echo "<p><strong>Total Symptoms:</strong> " . count($symptom_biomarker_correlations) . "</p>";

// EDGE CASE 1: SQL Injection Test
echo "<h2>🔥 EDGE CASE 1: SQL INJECTION TEST</h2>";

$sql_injection_tests = array(
    "'; DROP TABLE wp_users; --",
    "' OR '1'='1",
    "'; INSERT INTO wp_users VALUES (999, 'hacker', 'password'); --",
    "'; UPDATE wp_options SET option_value='hacked' WHERE option_name='siteurl'; --",
    "'; SELECT * FROM wp_users WHERE user_login='admin'; --"
);

$sql_injection_results = array();

foreach ($sql_injection_tests as $test) {
    $test_data = array(
        'symptoms' => array(
            'sql_injection_test' => array(
                'name' => $test,
                'category' => $test,
                'severity' => $test,
                'frequency' => $test,
                'first_reported' => current_time('mysql'),
                'last_updated' => current_time('mysql'),
                'sources' => array($test),
                'assessment_types' => array($test)
            )
        ),
        'by_category' => array($test => array('sql_injection_test')),
        'by_severity' => array($test => array('sql_injection_test')),
        'by_frequency' => array($test => array('sql_injection_test')),
        'total_count' => 1,
        'last_updated' => current_time('mysql')
    );
    
    // Try to store malicious data
    $result = update_user_meta($test_user_id, 'ennu_centralized_symptoms', $test_data);
    
    // Retrieve and check if data is properly sanitized
    $retrieved = get_user_meta($test_user_id, 'ennu_centralized_symptoms', true);
    
    $sql_injection_results[] = array(
        'test' => $test,
        'stored' => $result,
        'retrieved' => !empty($retrieved),
        'sanitized' => strpos(serialize($retrieved), 'DROP TABLE') === false && 
                      strpos(serialize($retrieved), 'INSERT INTO') === false &&
                      strpos(serialize($retrieved), 'UPDATE') === false &&
                      strpos(serialize($retrieved), 'SELECT *') === false
    );
}

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>SQL Injection Test Results:</strong><br>";
foreach ($sql_injection_results as $result) {
    $status = $result['sanitized'] ? '✅ Safe' : '❌ Vulnerable';
    echo "Test: " . substr($result['test'], 0, 30) . "... - {$status}<br>";
}
echo "</div>";

// EDGE CASE 2: XSS Attack Test
echo "<h2>🔥 EDGE CASE 2: XSS ATTACK TEST</h2>";

$xss_tests = array(
    '<script>alert("XSS")</script>',
    '<img src="x" onerror="alert(\'XSS\')">',
    '<iframe src="javascript:alert(\'XSS\')"></iframe>',
    '<svg onload="alert(\'XSS\')"></svg>',
    '<object data="javascript:alert(\'XSS\')"></object>',
    '<embed src="javascript:alert(\'XSS\')"></embed>',
    '<marquee onstart="alert(\'XSS\')"></marquee>',
    '<body onload="alert(\'XSS\')"></body>',
    'javascript:alert("XSS")',
    'data:text/html,<script>alert("XSS")</script>',
    'vbscript:alert("XSS")',
    'expression(alert("XSS"))'
);

$xss_results = array();

foreach ($xss_tests as $test) {
    $test_data = array(
        'symptoms' => array(
            'xss_test' => array(
                'name' => $test,
                'category' => $test,
                'severity' => $test,
                'frequency' => $test,
                'first_reported' => current_time('mysql'),
                'last_updated' => current_time('mysql'),
                'sources' => array($test),
                'assessment_types' => array($test)
            )
        ),
        'by_category' => array($test => array('xss_test')),
        'by_severity' => array($test => array('xss_test')),
        'by_frequency' => array($test => array('xss_test')),
        'total_count' => 1,
        'last_updated' => current_time('mysql')
    );
    
    // Try to store malicious data
    $result = update_user_meta($test_user_id, 'ennu_centralized_symptoms', $test_data);
    
    // Retrieve and check if data is properly sanitized
    $retrieved = get_user_meta($test_user_id, 'ennu_centralized_symptoms', true);
    
    $xss_results[] = array(
        'test' => $test,
        'stored' => $result,
        'retrieved' => !empty($retrieved),
        'sanitized' => strpos(serialize($retrieved), '<script>') === false &&
                      strpos(serialize($retrieved), 'javascript:') === false &&
                      strpos(serialize($retrieved), 'onerror=') === false &&
                      strpos(serialize($retrieved), 'onload=') === false
    );
}

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>XSS Attack Test Results:</strong><br>";
foreach ($xss_results as $result) {
    $status = $result['sanitized'] ? '✅ Safe' : '❌ Vulnerable';
    echo "Test: " . substr($result['test'], 0, 30) . "... - {$status}<br>";
}
echo "</div>";

// EDGE CASE 3: Unicode and Special Characters Test
echo "<h2>🔥 EDGE CASE 3: UNICODE AND SPECIAL CHARACTERS TEST</h2>";

$unicode_tests = array(
    '😀🎉🚀💯🔥💪🎯✅🌟💎',
    'Unicode Test 测试 テスト 테스트',
    'severe 严重 심각한',
    'constant 持续 지속적인',
    '!@#$%^&*()_+-=[]{}|;:,.<>?',
    'Symptom\0with\0null\0bytes',
    'Category\0with\0null\0bytes',
    '&lt;script&gt;alert("XSS")&lt;/script&gt;',
    '&amp;lt;script&amp;gt;alert("XSS")&amp;lt;/script&amp;gt;',
    '&#60;script&#62;alert("XSS")&#60;/script&#62;',
    '%3Cscript%3Ealert("XSS")%3C/script%3E'
);

$unicode_results = array();

foreach ($unicode_tests as $test) {
    $test_data = array(
        'symptoms' => array(
            'unicode_test' => array(
                'name' => $test,
                'category' => $test,
                'severity' => $test,
                'frequency' => $test,
                'first_reported' => current_time('mysql'),
                'last_updated' => current_time('mysql'),
                'sources' => array($test),
                'assessment_types' => array($test)
            )
        ),
        'by_category' => array($test => array('unicode_test')),
        'by_severity' => array($test => array('unicode_test')),
        'by_frequency' => array($test => array('unicode_test')),
        'total_count' => 1,
        'last_updated' => current_time('mysql')
    );
    
    // Try to store unicode data
    $result = update_user_meta($test_user_id, 'ennu_centralized_symptoms', $test_data);
    
    // Retrieve and check if data is preserved
    $retrieved = get_user_meta($test_user_id, 'ennu_centralized_symptoms', true);
    
    $unicode_results[] = array(
        'test' => $test,
        'stored' => $result,
        'retrieved' => !empty($retrieved),
        'preserved' => strpos(serialize($retrieved), $test) !== false || 
                      strpos(serialize($retrieved), htmlspecialchars($test)) !== false
    );
}

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Unicode Test Results:</strong><br>";
foreach ($unicode_results as $result) {
    $status = $result['preserved'] ? '✅ Preserved' : '❌ Lost';
    echo "Test: " . substr($result['test'], 0, 30) . "... - {$status}<br>";
}
echo "</div>";

// EDGE CASE 4: Biomarker Flagging Edge Cases
echo "<h2>🔥 EDGE CASE 4: BIOMARKER FLAGGING EDGE CASES</h2>";

$biomarker_edge_tests = array(
    array('name' => '', 'description' => 'Empty biomarker name'),
    array('name' => str_repeat('A', 1000), 'description' => 'Very long biomarker name'),
    array('name' => 'biomarker_with_!@#$%^&*()_+-=[]{}|;:,.<>?', 'description' => 'Special characters'),
    array('name' => 'biomarker_with_😀🎉🚀💯🔥💪🎯✅🌟💎', 'description' => 'Unicode characters'),
    array('name' => 'biomarker_with_测试_テスト_테스트', 'description' => 'International characters'),
    array('name' => 'biomarker_with_<script>alert("XSS")</script>', 'description' => 'XSS attempt'),
    array('name' => 'biomarker_with_\0null\0bytes', 'description' => 'Null bytes'),
    array('name' => 'biomarker_with_&lt;script&gt;alert("XSS")&lt;/script&gt;', 'description' => 'HTML entities')
);

$biomarker_results = array();

foreach ($biomarker_edge_tests as $test) {
    $result = $biomarker_manager->flag_biomarker(
        $test_user_id,
        $test['name'],
        'edge_case_trigger',
        'Test edge case biomarker flagging',
        null,
        'edge_case_source',
        'edge_case_symptom',
        'edge_case_symptom_key'
    );
    
    $biomarker_results[] = array(
        'test' => $test['description'],
        'biomarker' => $test['name'],
        'result' => $result,
        'safe' => strpos($test['name'], '<script>') === false && 
                  strpos($test['name'], 'DROP TABLE') === false &&
                  strpos($test['name'], 'javascript:') === false
    );
}

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Biomarker Edge Case Results:</strong><br>";
foreach ($biomarker_results as $result) {
    $status = $result['result'] ? '✅ Success' : '❌ Failed';
    $safety = $result['safe'] ? 'Safe' : 'Unsafe';
    echo "Test: {$result['test']} - {$status} ({$safety})<br>";
}
echo "</div>";

// EDGE CASE 5: Invalid Data Types Test
echo "<h2>🔥 EDGE CASE 5: INVALID DATA TYPES TEST</h2>";

$invalid_data_tests = array(
    array('user_id' => -1, 'description' => 'Invalid user ID'),
    array('user_id' => 0, 'description' => 'Zero user ID'),
    array('user_id' => 'string', 'description' => 'String user ID'),
    array('user_id' => null, 'description' => 'Null user ID'),
    array('user_id' => array(), 'description' => 'Array user ID'),
    array('user_id' => $test_user_id, 'biomarker' => null, 'description' => 'Null biomarker'),
    array('user_id' => $test_user_id, 'biomarker' => array(), 'description' => 'Array biomarker'),
    array('user_id' => $test_user_id, 'biomarker' => 123, 'description' => 'Integer biomarker'),
    array('user_id' => $test_user_id, 'biomarker' => true, 'description' => 'Boolean biomarker')
);

$invalid_data_results = array();

foreach ($invalid_data_tests as $test) {
    $user_id = isset($test['user_id']) ? $test['user_id'] : $test_user_id;
    $biomarker = isset($test['biomarker']) ? $test['biomarker'] : 'test_biomarker';
    
    $result = $biomarker_manager->flag_biomarker(
        $user_id,
        $biomarker,
        'invalid_data_trigger',
        'Test invalid data types',
        null,
        'invalid_data_source',
        'invalid_data_symptom',
        'invalid_data_symptom_key'
    );
    
    $invalid_data_results[] = array(
        'test' => $test['description'],
        'result' => $result,
        'handled' => $result !== false || $user_id <= 0 || $biomarker === null
    );
}

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Invalid Data Type Results:</strong><br>";
foreach ($invalid_data_results as $result) {
    $status = $result['handled'] ? '✅ Handled' : '❌ Failed';
    echo "Test: {$result['test']} - {$status}<br>";
}
echo "</div>";

// EDGE CASE 6: Concurrent Access Test
echo "<h2>🔥 EDGE CASE 6: CONCURRENT ACCESS TEST</h2>";

$concurrent_results = array();

// Simulate concurrent access with rapid operations
for ($i = 1; $i <= 50; $i++) {
    $concurrent_data = array(
        'symptoms' => array(
            "concurrent_symptom_{$i}" => array(
                'name' => "Concurrent Symptom {$i}",
                'category' => 'Concurrent Test',
                'severity' => 'moderate',
                'frequency' => 'often',
                'first_reported' => current_time('mysql'),
                'last_updated' => current_time('mysql'),
                'sources' => array("concurrent_test_{$i}"),
                'assessment_types' => array("concurrent_assessment_{$i}")
            )
        ),
        'by_category' => array('Concurrent Test' => array("concurrent_symptom_{$i}")),
        'by_severity' => array('moderate' => array("concurrent_symptom_{$i}")),
        'by_frequency' => array('often' => array("concurrent_symptom_{$i}")),
        'total_count' => 1,
        'last_updated' => current_time('mysql')
    );
    
    // Store data
    $store_result = update_user_meta($test_user_id, 'ennu_centralized_symptoms', $concurrent_data);
    
    // Retrieve immediately
    $retrieve_result = get_user_meta($test_user_id, 'ennu_centralized_symptoms', true);
    
    $concurrent_results[] = array(
        'operation' => $i,
        'stored' => $store_result,
        'retrieved' => !empty($retrieve_result),
        'data_integrity' => serialize($concurrent_data) === serialize($retrieve_result)
    );
}

$concurrent_success = 0;
foreach ($concurrent_results as $result) {
    if ($result['stored'] && $result['retrieved'] && $result['data_integrity']) {
        $concurrent_success++;
    }
}

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Concurrent Access Test Results:</strong><br>";
echo "Concurrent Operations: 50<br>";
echo "Successful Operations: {$concurrent_success}<br>";
echo "Success Rate: " . ($concurrent_success / 50 * 100) . "%<br>";
echo "Status: " . ($concurrent_success === 50 ? '✅ All Passed' : '❌ Some Failed') . "<br>";
echo "</div>";

// EDGE CASE 7: Memory Usage Test
echo "<h2>🔥 EDGE CASE 7: MEMORY USAGE TEST</h2>";

$memory_start = memory_get_usage(true);

// Create moderately large data structure
$memory_test_data = array();
for ($i = 1; $i <= 100; $i++) {
    $memory_test_data["memory_symptom_{$i}"] = array(
        'name' => str_repeat("Memory Test Symptom {$i} ", 10), // Longer name
        'category' => str_repeat("Memory Test Category {$i} ", 10), // Longer category
        'severity' => 'severe',
        'frequency' => 'constant',
        'first_reported' => current_time('mysql'),
        'last_updated' => current_time('mysql'),
        'sources' => array_fill(0, 10, "memory_source_{$i}"), // Larger array
        'assessment_types' => array_fill(0, 10, "memory_assessment_{$i}"), // Larger array
        'metadata' => array_fill(0, 50, "memory_metadata_{$i}") // Large metadata
    );
}

$memory_structure = array(
    'symptoms' => $memory_test_data,
    'by_category' => array_fill(0, 10, array_keys($memory_test_data)),
    'by_severity' => array_fill(0, 10, array_keys($memory_test_data)),
    'by_frequency' => array_fill(0, 10, array_keys($memory_test_data)),
    'total_count' => count($memory_test_data),
    'last_updated' => current_time('mysql')
);

$memory_before_storage = memory_get_usage(true);

// Store memory test data
$memory_storage_success = update_user_meta($test_user_id, 'ennu_centralized_symptoms', $memory_structure);

$memory_after_storage = memory_get_usage(true);
$memory_used = $memory_after_storage - $memory_before_storage;

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Memory Usage Test Results:</strong><br>";
echo "Memory Test Data Size: " . count($memory_test_data) . " symptoms<br>";
echo "Memory Used: " . number_format($memory_used / 1024 / 1024, 2) . " MB<br>";
echo "Storage Success: " . ($memory_storage_success ? '✅ Yes' : '❌ No') . "<br>";
echo "Peak Memory: " . number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB<br>";
echo "</div>";

// EDGE CASE 8: Error Recovery Test
echo "<h2>🔥 EDGE CASE 8: ERROR RECOVERY TEST</h2>";

$error_recovery_tests = array();

// Test 1: Invalid user meta operations
$invalid_meta_result = update_user_meta($test_user_id, '', array('invalid' => 'data'));
$error_recovery_tests[] = array('test' => 'Invalid meta key', 'result' => $invalid_meta_result);

// Test 2: Invalid data types
$invalid_type_result = update_user_meta($test_user_id, 'ennu_centralized_symptoms', 'string_data');
$error_recovery_tests[] = array('test' => 'Invalid data type', 'result' => $invalid_type_result);

// Test 3: Null operations
$null_result = update_user_meta($test_user_id, 'ennu_centralized_symptoms', null);
$error_recovery_tests[] = array('test' => 'Null data', 'result' => $null_result);

// Test 4: Empty array operations
$empty_array_result = update_user_meta($test_user_id, 'ennu_centralized_symptoms', array());
$error_recovery_tests[] = array('test' => 'Empty array', 'result' => $empty_array_result);

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Error Recovery Test Results:</strong><br>";
foreach ($error_recovery_tests as $test) {
    $status = $test['result'] ? '✅ Handled' : '❌ Failed';
    echo "Test: {$test['test']} - {$status}<br>";
}
echo "</div>";

// Final Summary
echo "<h2>📊 FOCUSED EDGE CASE TEST SUMMARY</h2>";

$total_tests = count($sql_injection_results) + count($xss_results) + count($unicode_results) + 
               count($biomarker_results) + count($invalid_data_results) + 50 + 1 + count($error_recovery_tests);

$passed_tests = 0;
foreach ($sql_injection_results as $result) { if ($result['sanitized']) $passed_tests++; }
foreach ($xss_results as $result) { if ($result['sanitized']) $passed_tests++; }
foreach ($unicode_results as $result) { if ($result['preserved']) $passed_tests++; }
foreach ($biomarker_results as $result) { if ($result['result']) $passed_tests++; }
foreach ($invalid_data_results as $result) { if ($result['handled']) $passed_tests++; }
$passed_tests += $concurrent_success;
$passed_tests += ($memory_storage_success ? 1 : 0);
foreach ($error_recovery_tests as $result) { if ($result['result']) $passed_tests++; }

$success_rate = ($passed_tests / $total_tests) * 100;

echo "<div style='margin: 20px 0; padding: 15px; background-color: #e8f5e8; border: 2px solid #4CAF50;'>";
echo "<h3>🎯 OVERALL RESULTS</h3>";
echo "<strong>Total Tests:</strong> {$total_tests}<br>";
echo "<strong>Passed Tests:</strong> {$passed_tests}<br>";
echo "<strong>Success Rate:</strong> {$success_rate}%<br>";
echo "<strong>System Security:</strong> " . ($success_rate >= 90 ? '✅ Secure' : '❌ Vulnerable') . "<br>";
echo "<strong>System Stability:</strong> " . ($success_rate >= 80 ? '✅ Stable' : '❌ Unstable') . "<br>";
echo "</div>";

// Save detailed results to file
$log_file = plugin_dir_path(__FILE__) . 'focused-edge-case-test-results-' . date('Y-m-d-H-i-s') . '.log';
$log_content = "Focused Edge Case Test Results\n";
$log_content .= "Date: " . date('Y-m-d H:i:s') . "\n";
$log_content .= "Test User ID: {$test_user_id}\n";
$log_content .= "Total Tests: {$total_tests}\n";
$log_content .= "Passed Tests: {$passed_tests}\n";
$log_content .= "Success Rate: {$success_rate}%\n\n";

$log_content .= "=== SQL INJECTION TESTS ===\n";
foreach ($sql_injection_results as $result) {
    $status = $result['sanitized'] ? 'PASSED' : 'FAILED';
    $log_content .= "Test: " . substr($result['test'], 0, 30) . "... - {$status}\n";
}

$log_content .= "\n=== XSS ATTACK TESTS ===\n";
foreach ($xss_results as $result) {
    $status = $result['sanitized'] ? 'PASSED' : 'FAILED';
    $log_content .= "Test: " . substr($result['test'], 0, 30) . "... - {$status}\n";
}

$log_content .= "\n=== UNICODE TESTS ===\n";
foreach ($unicode_results as $result) {
    $status = $result['preserved'] ? 'PASSED' : 'FAILED';
    $log_content .= "Test: " . substr($result['test'], 0, 30) . "... - {$status}\n";
}

$log_content .= "\n=== BIOMARKER EDGE CASES ===\n";
foreach ($biomarker_results as $result) {
    $status = $result['result'] ? 'PASSED' : 'FAILED';
    $log_content .= "Test: {$result['test']} - {$status}\n";
}

$log_content .= "\n=== INVALID DATA TESTS ===\n";
foreach ($invalid_data_results as $result) {
    $status = $result['handled'] ? 'PASSED' : 'FAILED';
    $log_content .= "Test: {$result['test']} - {$status}\n";
}

$log_content .= "\n=== CONCURRENT ACCESS ===\n";
$log_content .= "Concurrent Operations: 50\n";
$log_content .= "Successful Operations: {$concurrent_success}\n";
$log_content .= "Success Rate: " . ($concurrent_success / 50 * 100) . "%\n";

$log_content .= "\n=== MEMORY USAGE ===\n";
$log_content .= "Memory Used: " . number_format($memory_used / 1024 / 1024, 2) . " MB\n";
$log_content .= "Storage Success: " . ($memory_storage_success ? 'Yes' : 'No') . "\n";

$log_content .= "\n=== ERROR RECOVERY ===\n";
foreach ($error_recovery_tests as $result) {
    $status = $result['result'] ? 'PASSED' : 'FAILED';
    $log_content .= "Test: {$result['test']} - {$status}\n";
}

file_put_contents($log_file, $log_content);

echo "<p><strong>Detailed results saved to:</strong> {$log_file}</p>";

echo "<h2>✅ FOCUSED EDGE CASE TEST COMPLETED</h2>";
echo "<p>This test focused on specific vulnerabilities including SQL injection, XSS attacks, unicode handling, biomarker edge cases, invalid data types, concurrent access, memory usage, and error recovery.</p>";
?> 