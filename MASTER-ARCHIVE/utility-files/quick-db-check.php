<?php
/**
 * Quick Database Check for Biomarker Flags
 * Access via: http://localhost:8888/wp-content/plugins/ennulifeassessments/quick-db-check.php
 */

// Simple database check
$host = 'localhost';
$dbname = 'ennulifewpdb';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>🔍 Quick Database Check</h1>";
    echo "<h2>✅ Connected to: $dbname</h2>";
    
    // Check user meta for biomarker flags
    $user_id = 1;
    $stmt = $pdo->prepare("SELECT meta_key, meta_value FROM wp_usermeta WHERE user_id = ? AND meta_key IN ('ennu_biomarker_flags', 'ennu_symptom_history', 'ennu_centralized_symptoms')");
    $stmt->execute([$user_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>📊 User Meta Data (User ID: $user_id)</h3>";
    
    foreach ($results as $row) {
        echo "<h4>🔍 " . esc_html($row['meta_key']) . "</h4>";
        
        $data = maybe_unserialize($row['meta_value']);
        
        if (is_array($data)) {
            echo "<p><strong>✅ Data Type:</strong> Array with " . count($data) . " items</p>";
            
            if ($row['meta_key'] === 'ennu_biomarker_flags') {
                $active_flags = array_filter($data, function($flag) { return $flag['status'] === 'active'; });
                echo "<p><strong>🏴 Active Flags:</strong> " . count($active_flags) . "</p>";
                
                if (!empty($active_flags)) {
                    echo "<ul>";
                    foreach ($active_flags as $flag_id => $flag_data) {
                        echo "<li><strong>" . esc_html($flag_data['biomarker_name']) . "</strong> - " . esc_html($flag_data['reason']) . "</li>";
                    }
                    echo "</ul>";
                }
            } elseif ($row['meta_key'] === 'ennu_symptom_history') {
                if (isset($data['symptoms'])) {
                    echo "<p><strong>🩺 Current Symptoms:</strong> " . count($data['symptoms']) . "</p>";
                }
            } elseif ($row['meta_key'] === 'ennu_centralized_symptoms') {
                if (isset($data['symptoms'])) {
                    echo "<p><strong>🧬 Centralized Symptoms:</strong> " . count($data['symptoms']) . "</p>";
                }
            }
        } else {
            echo "<p><strong>⚠️ Data Type:</strong> " . gettype($data) . "</p>";
        }
    }
    
    if (empty($results)) {
        echo "<p><strong>❌ No biomarker or symptom data found for user $user_id</strong></p>";
    }
    
    echo "<h3>🔗 Quick Links</h3>";
    echo "<p><a href='http://localhost/?page_id=2469' target='_blank' style='background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0;'>🏠 User Dashboard</a></p>";
    echo "<p><a href='http://localhost/wp-admin/user-edit.php?user_id=1' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0;'>⚙️ Admin Edit</a></p>";
    
} catch (PDOException $e) {
    echo "<h1>❌ Database Error</h1>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
}

function maybe_unserialize($data) {
    if (is_serialized($data)) {
        return unserialize($data);
    }
    return $data;
}

function is_serialized($data) {
    if (!is_string($data)) return false;
    $data = trim($data);
    if ('N;' == $data) return true;
    if (!preg_match('/^([adObis]):/', $data, $badions)) return false;
    switch ($badions[1]) {
        case 'a': case 'O': case 's':
            return preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data);
        case 'b': case 'i': case 'd':
            return preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data);
    }
    return false;
}
?> 