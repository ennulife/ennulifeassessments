<?php
/**
 * Test CSV Import with Authentication Bypass
 * FOR TESTING ONLY - DO NOT USE IN PRODUCTION
 */

// Load WordPress
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';

// FOR TESTING: Bypass authentication (REMOVE IN PRODUCTION)
if (!is_user_logged_in()) {
    // Try to log in as admin
    $admin_user = get_user_by('login', 'admin');
    if ($admin_user) {
        wp_set_current_user($admin_user->ID);
        wp_set_auth_cookie($admin_user->ID);
    }
}

// Check if we're now logged in
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    echo "<h1>🔒 Authentication Failed</h1>";
    echo "<p>Could not authenticate as administrator.</p>";
    echo "<p>Please log in manually at: <a href='http://localhost/wp-admin/'>WordPress Admin</a></p>";
    exit;
}

// Check if the CSV importer class exists
if (!class_exists('ENNU_CSV_Biomarker_Importer')) {
    echo "<h1>❌ CSV Importer Not Found</h1>";
    echo "<p>The ENNU_CSV_Biomarker_Importer class is not available.</p>";
    echo "<p>Please check that the plugin is properly loaded.</p>";
    exit;
}

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>ENNU Life CSV Import Test</title>";
echo "<meta charset='utf-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1'>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }";
echo "h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }";
echo ".status { padding: 15px; border-radius: 5px; margin: 20px 0; }";
echo ".success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }";
echo ".error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }";
echo ".warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }";
echo ".info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }";
echo ".form-group { margin-bottom: 20px; }";
echo "label { display: block; margin-bottom: 5px; font-weight: bold; }";
echo "select, input[type='file'] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }";
echo ".checkbox-group { margin: 10px 0; }";
echo ".checkbox-group label { display: inline-block; margin-right: 20px; font-weight: normal; }";
echo "button { background: #3498db; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; }";
echo "button:hover { background: #2980b9; }";
echo ".results { margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px; }";
echo ".test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }";
echo "</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>🔬 ENNU Life CSV Import Test</h1>";

// Show authentication status
$current_user = wp_get_current_user();
echo "<div class='status success'>";
echo "<strong>✅ Authenticated as:</strong> {$current_user->display_name} ({$current_user->user_email})";
echo "</div>";

// Test CSV importer class
echo "<div class='test-section'>";
echo "<h2>🧪 CSV Importer Class Test</h2>";

try {
    $importer = new ENNU_CSV_Biomarker_Importer();
    echo "<div class='status success'>✅ CSV Importer class instantiated successfully</div>";
    
    // Test available biomarkers
    $reflection = new ReflectionClass($importer);
    $method = $reflection->getMethod('get_available_biomarkers');
    $method->setAccessible(true);
    $biomarkers = $method->invoke($importer);
    
    echo "<div class='status info'>📊 Available biomarkers: " . count($biomarkers) . "</div>";
    
    // Show sample biomarkers
    echo "<h4>Sample Biomarkers:</h4>";
    echo "<ul>";
    $count = 0;
    foreach ($biomarkers as $key => $info) {
        if ($count < 10) {
            echo "<li><strong>$key:</strong> {$info['name']} ({$info['unit']})</li>";
            $count++;
        }
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<div class='status error'>❌ Error: " . esc_html($e->getMessage()) . "</div>";
}

echo "</div>";

// Get available users
$users = get_users(array('meta_key' => 'ennu_life_score'));
echo "<div class='status warning'>";
echo "<strong>👥 Available users with ENNU Life scores:</strong> " . count($users);
echo "</div>";

if (empty($users)) {
    echo "<div class='status info'>";
    echo "<p>No users found with ENNU Life scores. Creating a test user...</p>";
    
    // Create a test user if none exist
    $test_user_id = wp_create_user('test_user', 'test_password', 'test@example.com');
    if (!is_wp_error($test_user_id)) {
        $user = get_user_by('id', $test_user_id);
        $user->set_role('subscriber');
        update_user_meta($test_user_id, 'ennu_life_score', 75);
        echo "<p>✅ Created test user: test_user (ID: $test_user_id)</p>";
        $users = get_users(array('meta_key' => 'ennu_life_score'));
    } else {
        echo "<p>❌ Failed to create test user</p>";
    }
    echo "</div>";
}

// Show the import form
echo "<div class='test-section'>";
echo "<h2>📤 CSV Import Form</h2>";
echo "<form method='post' enctype='multipart/form-data'>";
echo "<div class='form-group'>";
echo "<label for='user_id'>Select User:</label>";
echo "<select name='user_id' id='user_id' required>";
echo "<option value=''>Select a user...</option>";
foreach ($users as $user) {
    echo "<option value='{$user->ID}'>{$user->display_name} ({$user->user_email})</option>";
}
echo "</select>";
echo "</div>";

echo "<div class='form-group'>";
echo "<label for='csv_file'>CSV File:</label>";
echo "<input type='file' name='biomarker_csv' id='csv_file' accept='.csv' required>";
echo "<small>Upload a CSV file with format: biomarker_name,value,unit,date</small>";
echo "</div>";

echo "<div class='checkbox-group'>";
echo "<label><input type='checkbox' name='overwrite_existing' value='1'> Overwrite existing biomarker values</label><br>";
echo "<label><input type='checkbox' name='update_scores' value='1' checked> Update life scores after import</label>";
echo "</div>";

echo "<button type='submit' name='import_csv'>Import Biomarkers</button>";
echo "</form>";
echo "</div>";

// Handle form submission
if (isset($_POST['import_csv']) && isset($_FILES['biomarker_csv'])) {
    echo "<div class='results'>";
    echo "<h2>📊 Import Results</h2>";
    
    try {
        $user_id = absint($_POST['user_id']);
        $overwrite = isset($_POST['overwrite_existing']);
        $update_scores = isset($_POST['update_scores']);
        
        if (!$user_id) {
            throw new Exception("No user selected");
        }
        
        if ($_FILES['biomarker_csv']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed: " . $_FILES['biomarker_csv']['error']);
        }
        
        // Read and process the CSV file
        $csv_file = $_FILES['biomarker_csv']['tmp_name'];
        $imported_data = array();
        $errors = array();
        $warnings = array();
        
        if (($handle = fopen($csv_file, 'r')) !== false) {
            // Skip header
            $header = fgetcsv($handle);
            $row_number = 1;
            
            while (($row = fgetcsv($handle)) !== false) {
                $row_number++;
                
                if (count($row) < 4) {
                    $errors[] = "Row $row_number: Insufficient data";
                    continue;
                }
                
                $biomarker_name = sanitize_key(trim($row[0]));
                $value = trim($row[1]);
                $unit = sanitize_text_field(trim($row[2]));
                $date = sanitize_text_field(trim($row[3]));
                
                if (empty($biomarker_name)) {
                    $errors[] = "Row $row_number: Empty biomarker name";
                    continue;
                }
                
                if (!is_numeric($value)) {
                    $errors[] = "Row $row_number: Invalid value for $biomarker_name ($value)";
                    continue;
                }
                
                $imported_data[$biomarker_name] = array(
                    'value' => floatval($value),
                    'unit' => $unit,
                    'date' => $date,
                    'import_date' => current_time('mysql'),
                    'import_method' => 'csv',
                );
            }
            fclose($handle);
        }
        
        if (empty($imported_data)) {
            throw new Exception("No valid biomarker data found in CSV");
        }
        
        // Get existing data
        $existing_data = get_user_meta($user_id, 'ennu_biomarker_data', true);
        if (!is_array($existing_data)) {
            $existing_data = array();
        }
        
        // Merge or overwrite
        if ($overwrite) {
            $final_data = array_merge($existing_data, $imported_data);
        } else {
            $final_data = $existing_data;
            foreach ($imported_data as $biomarker => $data) {
                if (!isset($existing_data[$biomarker])) {
                    $final_data[$biomarker] = $data;
                } else {
                    $warnings[] = "Biomarker $biomarker already exists (skipped)";
                }
            }
        }
        
        // Save the data
        update_user_meta($user_id, 'ennu_biomarker_data', $final_data);
        update_user_meta($user_id, 'ennu_last_csv_import', current_time('mysql'));
        
        // Show results
        echo "<div class='status success'>";
        echo "<h3>✅ Import Successful!</h3>";
        echo "<p><strong>User:</strong> " . get_userdata($user_id)->display_name . "</p>";
        echo "<p><strong>Imported:</strong> " . count($imported_data) . " biomarkers</p>";
        echo "<p><strong>Overwrite:</strong> " . ($overwrite ? 'Yes' : 'No') . "</p>";
        echo "<p><strong>Update Scores:</strong> " . ($update_scores ? 'Yes' : 'No') . "</p>";
        echo "</div>";
        
        if (!empty($imported_data)) {
            echo "<h4>Imported Biomarkers:</h4>";
            echo "<ul>";
            foreach ($imported_data as $biomarker => $data) {
                echo "<li><strong>$biomarker:</strong> {$data['value']} {$data['unit']} (Date: {$data['date']})</li>";
            }
            echo "</ul>";
        }
        
        if (!empty($warnings)) {
            echo "<div class='status warning'>";
            echo "<h4>Warnings:</h4>";
            echo "<ul>";
            foreach ($warnings as $warning) {
                echo "<li>$warning</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
        
        if (!empty($errors)) {
            echo "<div class='status error'>";
            echo "<h4>Errors:</h4>";
            echo "<ul>";
            foreach ($errors as $error) {
                echo "<li>$error</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='status error'>";
        echo "<h3>❌ Import Failed</h3>";
        echo "<p><strong>Error:</strong> " . esc_html($e->getMessage()) . "</p>";
        echo "</div>";
    }
    
    echo "</div>";
}

echo "<div style='margin-top: 30px; padding: 20px; background: #e9ecef; border-radius: 5px;'>";
echo "<h3>📋 Instructions</h3>";
echo "<p><strong>CSV Format:</strong> biomarker_name,value,unit,date</p>";
echo "<p><strong>Example:</strong></p>";
echo "<pre>glucose,95,mg/dL,2024-01-15
hba1c,5.2,%,2024-01-15
testosterone,650,ng/dL,2024-01-15</pre>";
echo "<p><a href='http://localhost/wp-content/plugins/ennulifeassessments/sample-biomarkers.csv' target='_blank'>Download Sample CSV File</a></p>";
echo "</div>";

echo "<div style='margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 5px;'>";
echo "<h3>⚠️ Security Notice</h3>";
echo "<p>This is a test script with authentication bypass. DO NOT use in production!</p>";
echo "<p>For production use, access the CSV import through: <a href='http://localhost/wp-admin/admin.php?page=ennu-csv-import'>WordPress Admin → ENNU Life → CSV Import</a></p>";
echo "</div>";

echo "</div>";
echo "</body></html>"; 