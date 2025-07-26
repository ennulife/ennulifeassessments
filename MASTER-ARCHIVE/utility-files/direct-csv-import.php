<?php
/**
 * Direct CSV Import Test Script
 * This allows testing the CSV import functionality directly
 */

// Load WordPress
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';

// Check if user is logged in and has admin privileges
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    echo "<h1>🔒 Access Denied</h1>";
    echo "<p>You must be logged in as an administrator to access this page.</p>";
    echo "<p><a href='http://localhost/wp-admin/'>Login to WordPress Admin</a></p>";
    exit;
}

// Check if the CSV importer class exists
if (!class_exists('ENNU_CSV_Biomarker_Importer')) {
    echo "<h1>❌ CSV Importer Not Found</h1>";
    echo "<p>The ENNU_CSV_Biomarker_Importer class is not available.</p>";
    echo "<p>Please check that the plugin is properly loaded.</p>";
    exit;
}

// Initialize the importer
$importer = new ENNU_CSV_Biomarker_Importer();

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>ENNU Life CSV Import - Direct Access</title>";
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
echo ".form-group { margin-bottom: 20px; }";
echo "label { display: block; margin-bottom: 5px; font-weight: bold; }";
echo "select, input[type='file'] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }";
echo ".checkbox-group { margin: 10px 0; }";
echo ".checkbox-group label { display: inline-block; margin-right: 20px; font-weight: normal; }";
echo "button { background: #3498db; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; }";
echo "button:hover { background: #2980b9; }";
echo ".results { margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 5px; }";
echo "</style>";
echo "</head><body>";

echo "<div class='container'>";
echo "<h1>🔬 ENNU Life CSV Biomarker Import</h1>";

// Show current user info
$current_user = wp_get_current_user();
echo "<div class='status success'>";
echo "<strong>Logged in as:</strong> {$current_user->display_name} ({$current_user->user_email})";
echo "</div>";

// Get available users
$users = get_users(array('meta_key' => 'ennu_life_score'));
echo "<div class='status warning'>";
echo "<strong>Available users with ENNU Life scores:</strong> " . count($users);
echo "</div>";

// Show the import form
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

// Handle form submission
if (isset($_POST['import_csv']) && isset($_FILES['biomarker_csv'])) {
    echo "<div class='results'>";
    echo "<h2>📊 Import Results</h2>";
    
    try {
        // Simulate the import process
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

echo "</div>";
echo "</body></html>"; 