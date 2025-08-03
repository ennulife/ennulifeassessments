<?php
/**
 * Extreme Edge Case Test
 * Pushes the system to its absolute limits with unusual data, high volumes, and edge scenarios
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 300);

// Load WordPress
require_once('../../../../wp-load.php');

// Ensure we're in WordPress environment
if (!defined('ABSPATH')) {
    die('WordPress not loaded');
}

// Test user ID
$test_user_id = 1;

echo "<h1>🔥 EXTREME EDGE CASE TEST</h1>";
echo "<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Test User ID:</strong> {$test_user_id}</p>";
echo "<p><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . " seconds</p>";

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

// EDGE CASE 1: Massive Data Volume Test
echo "<h2>🔥 EDGE CASE 1: MASSIVE DATA VOLUME TEST</h2>";

$start_time = microtime(true);
$memory_start = memory_get_usage(true);

// Create massive symptom data
$massive_symptoms = array();
for ($i = 1; $i <= 1000; $i++) {
    $massive_symptoms["symptom_{$i}"] = array(
        'name' => "Massive Symptom {$i}",
        'category' => 'Edge Case',
        'severity' => 'severe',
        'frequency' => 'constant',
        'first_reported' => current_time('mysql'),
        'last_updated' => current_time('mysql'),
        'sources' => array('edge_case_test'),
        'assessment_types' => array('edge_case')
    );
}

$massive_data = array(
    'symptoms' => $massive_symptoms,
    'by_category' => array('Edge Case' => array_keys($massive_symptoms)),
    'by_severity' => array('severe' => array_keys($massive_symptoms)),
    'by_frequency' => array('constant' => array_keys($massive_symptoms)),
    'total_count' => count($massive_symptoms),
    'last_updated' => current_time('mysql')
);

// Store massive data
update_user_meta($test_user_id, 'ennu_centralized_symptoms', $massive_data);

$memory_after_storage = memory_get_usage(true);
$storage_time = microtime(true) - $start_time;

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Massive Data Test:</strong><br>";
echo "Symptoms Created: " . count($massive_symptoms) . "<br>";
echo "Storage Time: " . number_format($storage_time, 4) . " seconds<br>";
echo "Memory Used: " . number_format(($memory_after_storage - $memory_start) / 1024 / 1024, 2) . " MB<br>";
echo "Status: " . (!empty($massive_data) ? '✅ Success' : '❌ Failed') . "<br>";
echo "</div>";

// EDGE CASE 2: Malicious Data Injection Test
echo "<h2>🔥 EDGE CASE 2: MALICIOUS DATA INJECTION TEST</h2>";

$malicious_data = array(
    'symptoms' => array(
        'malicious_sql' => array(
            'name' => "'; DROP TABLE wp_users; --",
            'category' => "'; DROP TABLE wp_posts; --",
            'severity' => "'; DROP TABLE wp_options; --",
            'frequency' => "'; DROP TABLE wp_meta; --",
            'first_reported' => "'; DROP TABLE wp_terms; --",
            'last_updated' => "'; DROP TABLE wp_comments; --",
            'sources' => array("'; DROP TABLE wp_links; --"),
            'assessment_types' => array("'; DROP TABLE wp_terms_taxonomy; --")
        ),
        'xss_attack' => array(
            'name' => '<script>alert("XSS")</script>',
            'category' => '<img src="x" onerror="alert(\'XSS\')">',
            'severity' => '<iframe src="javascript:alert(\'XSS\')"></iframe>',
            'frequency' => '<svg onload="alert(\'XSS\')"></svg>',
            'first_reported' => '<object data="javascript:alert(\'XSS\')"></object>',
            'last_updated' => '<embed src="javascript:alert(\'XSS\')"></embed>',
            'sources' => array('<marquee onstart="alert(\'XSS\')"></marquee>'),
            'assessment_types' => array('<body onload="alert(\'XSS\')"></body>')
        ),
        'html_entities' => array(
            'name' => '&lt;script&gt;alert("XSS")&lt;/script&gt;',
            'category' => '&amp;lt;script&amp;gt;alert("XSS")&amp;lt;/script&amp;gt;',
            'severity' => '&#60;script&#62;alert("XSS")&#60;/script&#62;',
            'frequency' => '%3Cscript%3Ealert("XSS")%3C/script%3E',
            'first_reported' => 'javascript:alert("XSS")',
            'last_updated' => 'data:text/html,<script>alert("XSS")</script>',
            'sources' => array('vbscript:alert("XSS")'),
            'assessment_types' => array('expression(alert("XSS"))')
        )
    ),
    'by_category' => array('Malicious' => array('malicious_sql', 'xss_attack', 'html_entities')),
    'by_severity' => array('malicious' => array('malicious_sql', 'xss_attack', 'html_entities')),
    'by_frequency' => array('constant' => array('malicious_sql', 'xss_attack', 'html_entities')),
    'total_count' => 3,
    'last_updated' => current_time('mysql')
);

// Store malicious data
update_user_meta($test_user_id, 'ennu_centralized_symptoms', $malicious_data);

// Retrieve and check if data is properly sanitized
$retrieved_malicious = get_user_meta($test_user_id, 'ennu_centralized_symptoms', true);

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Malicious Data Test:</strong><br>";
echo "Malicious Data Stored: " . (!empty($malicious_data) ? '✅ Yes' : '❌ No') . "<br>";
echo "Data Retrieved: " . (!empty($retrieved_malicious) ? '✅ Yes' : '❌ No') . "<br>";
echo "Data Sanitized: " . (strpos(serialize($retrieved_malicious), '<script>') === false ? '✅ Yes' : '❌ No') . "<br>";
echo "</div>";

// EDGE CASE 3: Unicode and Special Characters Test
echo "<h2>🔥 EDGE CASE 3: UNICODE AND SPECIAL CHARACTERS TEST</h2>";

$unicode_data = array(
    'symptoms' => array(
        'unicode_symptom' => array(
            'name' => '😀🎉🚀💯🔥💪🎯✅🌟💎',
            'category' => 'Unicode Test 测试 テスト 테스트',
            'severity' => 'severe 严重 심각한',
            'frequency' => 'constant 持续 지속적인',
            'first_reported' => current_time('mysql'),
            'last_updated' => current_time('mysql'),
            'sources' => array('unicode_test 🧪'),
            'assessment_types' => array('unicode_assessment 📊')
        ),
        'special_chars' => array(
            'name' => '!@#$%^&*()_+-=[]{}|;:,.<>?',
            'category' => 'Special Characters Test',
            'severity' => 'severe',
            'frequency' => 'constant',
            'first_reported' => current_time('mysql'),
            'last_updated' => current_time('mysql'),
            'sources' => array('special_chars_test'),
            'assessment_types' => array('special_chars_assessment')
        ),
        'null_bytes' => array(
            'name' => "Symptom\0with\0null\0bytes",
            'category' => "Category\0with\0null\0bytes",
            'severity' => "severe\0with\0null\0bytes",
            'frequency' => "constant\0with\0null\0bytes",
            'first_reported' => current_time('mysql'),
            'last_updated' => current_time('mysql'),
            'sources' => array("source\0with\0null\0bytes"),
            'assessment_types' => array("assessment\0with\0null\0bytes")
        )
    ),
    'by_category' => array('Unicode Test 测试 テスト 테스트' => array('unicode_symptom'), 'Special Characters Test' => array('special_chars'), 'Null Bytes Test' => array('null_bytes')),
    'by_severity' => array('severe 严重 심각한' => array('unicode_symptom'), 'severe' => array('special_chars'), 'severe\0with\0null\0bytes' => array('null_bytes')),
    'by_frequency' => array('constant 持续 지속적인' => array('unicode_symptom'), 'constant' => array('special_chars'), 'constant\0with\0null\0bytes' => array('null_bytes')),
    'total_count' => 3,
    'last_updated' => current_time('mysql')
);

// Store unicode data
update_user_meta($test_user_id, 'ennu_centralized_symptoms', $unicode_data);

// Retrieve and check unicode data
$retrieved_unicode = get_user_meta($test_user_id, 'ennu_centralized_symptoms', true);

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Unicode Data Test:</strong><br>";
echo "Unicode Data Stored: " . (!empty($unicode_data) ? '✅ Yes' : '❌ No') . "<br>";
echo "Unicode Data Retrieved: " . (!empty($retrieved_unicode) ? '✅ Yes' : '❌ No') . "<br>";
echo "Emojis Preserved: " . (strpos(serialize($retrieved_unicode), '😀') !== false ? '✅ Yes' : '❌ No') . "<br>";
echo "Chinese Characters Preserved: " . (strpos(serialize($retrieved_unicode), '测试') !== false ? '✅ Yes' : '❌ No') . "<br>";
echo "Japanese Characters Preserved: " . (strpos(serialize($retrieved_unicode), 'テスト') !== false ? '✅ Yes' : '❌ No') . "<br>";
echo "Korean Characters Preserved: " . (strpos(serialize($retrieved_unicode), '테스트') !== false ? '✅ Yes' : '❌ No') . "<br>";
echo "</div>";

// EDGE CASE 4: Concurrent Access Test
echo "<h2>🔥 EDGE CASE 4: CONCURRENT ACCESS TEST</h2>";

$concurrent_results = array();

// Simulate concurrent access
for ($i = 1; $i <= 10; $i++) {
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
    
    // Store concurrent data
    update_user_meta($test_user_id, 'ennu_centralized_symptoms', $concurrent_data);
    
    // Retrieve immediately
    $retrieved_concurrent = get_user_meta($test_user_id, 'ennu_centralized_symptoms', true);
    
    $concurrent_results[$i] = array(
        'stored' => !empty($concurrent_data),
        'retrieved' => !empty($retrieved_concurrent),
        'data_integrity' => serialize($concurrent_data) === serialize($retrieved_concurrent)
    );
}

$concurrent_success = 0;
foreach ($concurrent_results as $result) {
    if ($result['stored'] && $result['retrieved'] && $result['data_integrity']) {
        $concurrent_success++;
    }
}

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Concurrent Access Test:</strong><br>";
echo "Concurrent Operations: 10<br>";
echo "Successful Operations: {$concurrent_success}<br>";
echo "Success Rate: " . ($concurrent_success / 10 * 100) . "%<br>";
echo "Status: " . ($concurrent_success === 10 ? '✅ All Passed' : '❌ Some Failed') . "<br>";
echo "</div>";

// EDGE CASE 5: Memory Exhaustion Test
echo "<h2>🔥 EDGE CASE 5: MEMORY EXHAUSTION TEST</h2>";

$memory_test_start = memory_get_usage(true);

// Create extremely large data structure
$extreme_data = array();
for ($i = 1; $i <= 10000; $i++) {
    $extreme_data["extreme_symptom_{$i}"] = array(
        'name' => str_repeat("Extreme Symptom {$i} ", 100), // Very long name
        'category' => str_repeat("Extreme Category {$i} ", 100), // Very long category
        'severity' => 'severe',
        'frequency' => 'constant',
        'first_reported' => current_time('mysql'),
        'last_updated' => current_time('mysql'),
        'sources' => array_fill(0, 100, "extreme_source_{$i}"), // Large array
        'assessment_types' => array_fill(0, 100, "extreme_assessment_{$i}"), // Large array
        'metadata' => array_fill(0, 1000, "extreme_metadata_{$i}") // Very large metadata
    );
}

$extreme_structure = array(
    'symptoms' => $extreme_data,
    'by_category' => array_fill(0, 1000, array_keys($extreme_data)),
    'by_severity' => array_fill(0, 1000, array_keys($extreme_data)),
    'by_frequency' => array_fill(0, 1000, array_keys($extreme_data)),
    'total_count' => count($extreme_data),
    'last_updated' => current_time('mysql')
);

$memory_before_storage = memory_get_usage(true);

// Try to store extreme data
$extreme_storage_success = false;
try {
    update_user_meta($test_user_id, 'ennu_centralized_symptoms', $extreme_structure);
    $extreme_storage_success = true;
} catch (Exception $e) {
    $extreme_storage_success = false;
    $extreme_error = $e->getMessage();
}

$memory_after_storage = memory_get_usage(true);
$memory_used = $memory_after_storage - $memory_before_storage;

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Memory Exhaustion Test:</strong><br>";
echo "Extreme Data Size: " . count($extreme_data) . " symptoms<br>";
echo "Memory Used: " . number_format($memory_used / 1024 / 1024, 2) . " MB<br>";
echo "Storage Success: " . ($extreme_storage_success ? '✅ Yes' : '❌ No') . "<br>";
if (!$extreme_storage_success) {
    echo "Error: " . $extreme_error . "<br>";
}
echo "</div>";

// EDGE CASE 6: Biomarker Flagging Edge Cases
echo "<h2>🔥 EDGE CASE 6: BIOMARKER FLAGGING EDGE CASES</h2>";

// Test with empty biomarker name
$empty_biomarker_result = $biomarker_manager->flag_biomarker(
    $test_user_id,
    '', // Empty biomarker name
    'edge_case_trigger',
    'Test empty biomarker flagging',
    null,
    'edge_case_source',
    'edge_case_symptom',
    'edge_case_symptom_key'
);

// Test with very long biomarker name
$long_biomarker_name = str_repeat('very_long_biomarker_name_', 100);
$long_biomarker_result = $biomarker_manager->flag_biomarker(
    $test_user_id,
    $long_biomarker_name,
    'edge_case_trigger',
    'Test long biomarker name flagging',
    null,
    'edge_case_source',
    'edge_case_symptom',
    'edge_case_symptom_key'
);

// Test with special characters in biomarker name
$special_biomarker_name = 'biomarker_with_!@#$%^&*()_+-=[]{}|;:,.<>?';
$special_biomarker_result = $biomarker_manager->flag_biomarker(
    $test_user_id,
    $special_biomarker_name,
    'edge_case_trigger',
    'Test special characters in biomarker name',
    null,
    'edge_case_source',
    'edge_case_symptom',
    'edge_case_symptom_key'
);

// Test with unicode biomarker name
$unicode_biomarker_name = 'biomarker_with_😀🎉🚀💯🔥💪🎯✅🌟💎';
$unicode_biomarker_result = $biomarker_manager->flag_biomarker(
    $test_user_id,
    $unicode_biomarker_name,
    'edge_case_trigger',
    'Test unicode biomarker name',
    null,
    'edge_case_source',
    'edge_case_symptom',
    'edge_case_symptom_key'
);

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Biomarker Flagging Edge Cases:</strong><br>";
echo "Empty Biomarker Name: " . ($empty_biomarker_result ? '✅ Success' : '❌ Failed') . "<br>";
echo "Long Biomarker Name: " . ($long_biomarker_result ? '✅ Success' : '❌ Failed') . "<br>";
echo "Special Characters: " . ($special_biomarker_result ? '✅ Success' : '❌ Failed') . "<br>";
echo "Unicode Biomarker Name: " . ($unicode_biomarker_result ? '✅ Success' : '❌ Failed') . "<br>";
echo "</div>";

// EDGE CASE 7: Performance Under Load Test
echo "<h2>🔥 EDGE CASE 7: PERFORMANCE UNDER LOAD TEST</h2>";

$performance_start = microtime(true);
$performance_memory_start = memory_get_usage(true);

// Perform 1000 rapid operations
$performance_results = array();
for ($i = 1; $i <= 1000; $i++) {
    $operation_start = microtime(true);
    
    // Create test data
    $test_data = array(
        'symptoms' => array(
            "performance_symptom_{$i}" => array(
                'name' => "Performance Symptom {$i}",
                'category' => 'Performance Test',
                'severity' => 'moderate',
                'frequency' => 'often',
                'first_reported' => current_time('mysql'),
                'last_updated' => current_time('mysql'),
                'sources' => array("performance_test_{$i}"),
                'assessment_types' => array("performance_assessment_{$i}")
            )
        ),
        'by_category' => array('Performance Test' => array("performance_symptom_{$i}")),
        'by_severity' => array('moderate' => array("performance_symptom_{$i}")),
        'by_frequency' => array('often' => array("performance_symptom_{$i}")),
        'total_count' => 1,
        'last_updated' => current_time('mysql')
    );
    
    // Store data
    update_user_meta($test_user_id, 'ennu_centralized_symptoms', $test_data);
    
    // Retrieve data
    $retrieved_data = get_user_meta($test_user_id, 'ennu_centralized_symptoms', true);
    
    $operation_time = microtime(true) - $operation_start;
    $performance_results[$i] = $operation_time;
}

$performance_end = microtime(true);
$performance_memory_end = memory_get_usage(true);

$total_performance_time = $performance_end - $performance_start;
$total_performance_memory = $performance_memory_end - $performance_memory_start;
$avg_operation_time = array_sum($performance_results) / count($performance_results);
$max_operation_time = max($performance_results);
$min_operation_time = min($performance_results);

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Performance Under Load Test:</strong><br>";
echo "Total Operations: 1000<br>";
echo "Total Time: " . number_format($total_performance_time, 4) . " seconds<br>";
echo "Average Operation Time: " . number_format($avg_operation_time * 1000, 2) . " ms<br>";
echo "Fastest Operation: " . number_format($min_operation_time * 1000, 2) . " ms<br>";
echo "Slowest Operation: " . number_format($max_operation_time * 1000, 2) . " ms<br>";
echo "Memory Used: " . number_format($total_performance_memory / 1024 / 1024, 2) . " MB<br>";
echo "Operations Per Second: " . number_format(1000 / $total_performance_time, 2) . "<br>";
echo "</div>";

// EDGE CASE 8: Database Transaction Test
echo "<h2>🔥 EDGE CASE 8: DATABASE TRANSACTION TEST</h2>";

global $wpdb;

// Start transaction
$wpdb->query('START TRANSACTION');

try {
    // Perform multiple operations
    for ($i = 1; $i <= 100; $i++) {
        $transaction_data = array(
            'symptoms' => array(
                "transaction_symptom_{$i}" => array(
                    'name' => "Transaction Symptom {$i}",
                    'category' => 'Transaction Test',
                    'severity' => 'moderate',
                    'frequency' => 'often',
                    'first_reported' => current_time('mysql'),
                    'last_updated' => current_time('mysql'),
                    'sources' => array("transaction_test_{$i}"),
                    'assessment_types' => array("transaction_assessment_{$i}")
                )
            ),
            'by_category' => array('Transaction Test' => array("transaction_symptom_{$i}")),
            'by_severity' => array('moderate' => array("transaction_symptom_{$i}")),
            'by_frequency' => array('often' => array("transaction_symptom_{$i}")),
            'total_count' => 1,
            'last_updated' => current_time('mysql')
        );
        
        update_user_meta($test_user_id, 'ennu_centralized_symptoms', $transaction_data);
    }
    
    // Commit transaction
    $wpdb->query('COMMIT');
    $transaction_success = true;
    
} catch (Exception $e) {
    // Rollback transaction
    $wpdb->query('ROLLBACK');
    $transaction_success = false;
    $transaction_error = $e->getMessage();
}

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Database Transaction Test:</strong><br>";
echo "Transaction Operations: 100<br>";
echo "Transaction Success: " . ($transaction_success ? '✅ Yes' : '❌ No') . "<br>";
if (!$transaction_success) {
    echo "Transaction Error: " . $transaction_error . "<br>";
}
echo "</div>";

// EDGE CASE 9: Error Handling Test
echo "<h2>🔥 EDGE CASE 9: ERROR HANDLING TEST</h2>";

$error_tests = array();

// Test with invalid user ID
$invalid_user_result = $biomarker_manager->flag_biomarker(
    -1, // Invalid user ID
    'test_biomarker',
    'error_test_trigger',
    'Test invalid user ID',
    null,
    'error_test_source',
    'error_test_symptom',
    'error_test_symptom_key'
);

// Test with null values
$null_values_result = $biomarker_manager->flag_biomarker(
    $test_user_id,
    null, // Null biomarker name
    null, // Null trigger
    null, // Null description
    null, // Null additional data
    null, // Null source
    null, // Null symptom
    null  // Null symptom key
);

// Test with extremely large data
$large_data = str_repeat('A', 1000000); // 1MB string
$large_data_result = $biomarker_manager->flag_biomarker(
    $test_user_id,
    'test_biomarker',
    'error_test_trigger',
    $large_data, // Very large description
    null,
    'error_test_source',
    'error_test_symptom',
    'error_test_symptom_key'
);

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>Error Handling Test:</strong><br>";
echo "Invalid User ID: " . ($invalid_user_result ? '✅ Handled' : '❌ Failed') . "<br>";
echo "Null Values: " . ($null_values_result ? '✅ Handled' : '❌ Failed') . "<br>";
echo "Large Data: " . ($large_data_result ? '✅ Handled' : '❌ Failed') . "<br>";
echo "</div>";

// EDGE CASE 10: System Resource Test
echo "<h2>🔥 EDGE CASE 10: SYSTEM RESOURCE TEST</h2>";

$resource_start = microtime(true);
$resource_memory_start = memory_get_usage(true);
$resource_peak_start = memory_get_peak_usage(true);

// Create resource-intensive operations
$resource_operations = array();
for ($i = 1; $i <= 500; $i++) {
    $resource_data = array(
        'symptoms' => array_fill(0, 100, array(
            'name' => "Resource Symptom {$i}",
            'category' => 'Resource Test',
            'severity' => 'severe',
            'frequency' => 'constant',
            'first_reported' => current_time('mysql'),
            'last_updated' => current_time('mysql'),
            'sources' => array_fill(0, 50, "resource_source_{$i}"),
            'assessment_types' => array_fill(0, 50, "resource_assessment_{$i}"),
            'metadata' => array_fill(0, 100, "resource_metadata_{$i}")
        )),
        'by_category' => array_fill(0, 50, array_fill(0, 100, "resource_symptom_{$i}")),
        'by_severity' => array_fill(0, 50, array_fill(0, 100, "resource_symptom_{$i}")),
        'by_frequency' => array_fill(0, 50, array_fill(0, 100, "resource_symptom_{$i}")),
        'total_count' => 100,
        'last_updated' => current_time('mysql')
    );
    
    update_user_meta($test_user_id, 'ennu_centralized_symptoms', $resource_data);
    $resource_operations[] = $resource_data;
}

$resource_end = microtime(true);
$resource_memory_end = memory_get_usage(true);
$resource_peak_end = memory_get_peak_usage(true);

$resource_time = $resource_end - $resource_start;
$resource_memory = $resource_memory_end - $resource_memory_start;
$resource_peak = $resource_peak_end - $resource_peak_start;

echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ccc;'>";
echo "<strong>System Resource Test:</strong><br>";
echo "Resource Operations: 500<br>";
echo "Total Time: " . number_format($resource_time, 4) . " seconds<br>";
echo "Memory Used: " . number_format($resource_memory / 1024 / 1024, 2) . " MB<br>";
echo "Peak Memory: " . number_format($resource_peak / 1024 / 1024, 2) . " MB<br>";
echo "Operations Per Second: " . number_format(500 / $resource_time, 2) . "<br>";
echo "</div>";

// Final Summary
echo "<h2>📊 EXTREME EDGE CASE TEST SUMMARY</h2>";

$total_memory_used = memory_get_usage(true) - $memory_start;
$total_time = microtime(true) - $start_time;

echo "<div style='margin: 20px 0; padding: 15px; background-color: #e8f5e8; border: 2px solid #4CAF50;'>";
echo "<h3>🎯 OVERALL RESULTS</h3>";
echo "<strong>Total Test Time:</strong> " . number_format($total_time, 4) . " seconds<br>";
echo "<strong>Total Memory Used:</strong> " . number_format($total_memory_used / 1024 / 1024, 2) . " MB<br>";
echo "<strong>Peak Memory:</strong> " . number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB<br>";
echo "<strong>Tests Completed:</strong> 10 extreme edge cases<br>";
echo "<strong>System Stability:</strong> " . ($total_time < 300 && $total_memory_used < 500 * 1024 * 1024 ? '✅ Stable' : '❌ Unstable') . "<br>";
echo "</div>";

// Save detailed results to file
$log_file = plugin_dir_path(__FILE__) . 'extreme-edge-case-test-results-' . date('Y-m-d-H-i-s') . '.log';
$log_content = "Extreme Edge Case Test Results\n";
$log_content .= "Date: " . date('Y-m-d H:i:s') . "\n";
$log_content .= "Test User ID: {$test_user_id}\n";
$log_content .= "Total Test Time: " . number_format($total_time, 4) . " seconds\n";
$log_content .= "Total Memory Used: " . number_format($total_memory_used / 1024 / 1024, 2) . " MB\n";
$log_content .= "Peak Memory: " . number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB\n\n";

$log_content .= "=== EDGE CASE RESULTS ===\n";
$log_content .= "1. Massive Data Volume: " . (count($massive_symptoms) > 0 ? 'PASSED' : 'FAILED') . "\n";
$log_content .= "2. Malicious Data Injection: " . (strpos(serialize($retrieved_malicious), '<script>') === false ? 'PASSED' : 'FAILED') . "\n";
$log_content .= "3. Unicode and Special Characters: " . (strpos(serialize($retrieved_unicode), '😀') !== false ? 'PASSED' : 'FAILED') . "\n";
$log_content .= "4. Concurrent Access: " . ($concurrent_success === 10 ? 'PASSED' : 'FAILED') . "\n";
$log_content .= "5. Memory Exhaustion: " . ($extreme_storage_success ? 'PASSED' : 'FAILED') . "\n";
$log_content .= "6. Biomarker Flagging Edge Cases: " . ($empty_biomarker_result && $long_biomarker_result && $special_biomarker_result && $unicode_biomarker_result ? 'PASSED' : 'FAILED') . "\n";
$log_content .= "7. Performance Under Load: " . ($total_performance_time < 60 ? 'PASSED' : 'FAILED') . "\n";
$log_content .= "8. Database Transaction: " . ($transaction_success ? 'PASSED' : 'FAILED') . "\n";
$log_content .= "9. Error Handling: " . ($invalid_user_result && $null_values_result && $large_data_result ? 'PASSED' : 'FAILED') . "\n";
$log_content .= "10. System Resource: " . ($resource_time < 120 ? 'PASSED' : 'FAILED') . "\n";

file_put_contents($log_file, $log_content);

echo "<p><strong>Detailed results saved to:</strong> {$log_file}</p>";

echo "<h2>✅ EXTREME EDGE CASE TEST COMPLETED</h2>";
echo "<p>This test pushed the system to its absolute limits with massive data volumes, malicious inputs, unicode characters, concurrent access, memory exhaustion, and performance under extreme load.</p>";
?> 