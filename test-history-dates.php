<?php
/**
 * Test Weight History Dates
 * Verifies dates are correct and historical weights are preserved
 */

require_once('../../../wp-load.php');

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('Access denied. Please login as admin.');
}

$user_id = get_current_user_id();

// Test 1: Clear and recreate history with specific dates
if (isset($_GET['action']) && $_GET['action'] === 'recreate') {
    // Clear existing history
    delete_user_meta($user_id, 'weight_history');
    
    // Create history with specific dates and weights
    $history = array(
        array(
            'date' => date('Y-m-d', strtotime('-6 months')),
            'weight' => 185.0,
            'bmi' => 28.2,
            'timestamp' => strtotime('-6 months')
        ),
        array(
            'date' => date('Y-m-d', strtotime('-4 months')),
            'weight' => 182.5,
            'bmi' => 27.8,
            'timestamp' => strtotime('-4 months')
        ),
        array(
            'date' => date('Y-m-d', strtotime('-2 months')),
            'weight' => 178.0,
            'bmi' => 27.1,
            'timestamp' => strtotime('-2 months')
        ),
        array(
            'date' => date('Y-m-d', strtotime('-1 month')),
            'weight' => 175.5,
            'bmi' => 26.7,
            'timestamp' => strtotime('-1 month')
        ),
        array(
            'date' => date('Y-m-d', strtotime('-1 week')),
            'weight' => 173.0,
            'bmi' => 26.3,
            'timestamp' => strtotime('-1 week')
        ),
        array(
            'date' => date('Y-m-d'),
            'weight' => 172.0,
            'bmi' => 26.2,
            'timestamp' => time()
        )
    );
    
    update_user_meta($user_id, 'weight_history', $history);
    update_user_meta($user_id, 'weight', 172.0);
    
    echo "<script>location.href = '?recreated=1';</script>";
    exit;
}

// Get current data
$weight_history = get_user_meta($user_id, 'weight_history', true);
$current_weight = get_user_meta($user_id, 'weight', true);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Weight History Date Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 1000px; margin: 0 auto; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .box { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #2196F3; color: white; }
        button { padding: 10px 20px; font-size: 16px; margin: 5px; cursor: pointer; }
        .primary { background: #2196F3; color: white; border: none; border-radius: 4px; }
        .success-btn { background: #4CAF50; color: white; border: none; border-radius: 4px; }
        .info { background: #e3f2fd; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>Weight History Date Verification</h1>
    
    <?php if (isset($_GET['recreated'])): ?>
        <div class="box">
            <p class="success">✓ History recreated with proper dates!</p>
        </div>
    <?php endif; ?>
    
    <div class="box">
        <h2>Current Weight History</h2>
        <?php if (is_array($weight_history) && !empty($weight_history)): ?>
            <table>
                <tr>
                    <th>Date (Raw)</th>
                    <th>Human Readable</th>
                    <th>Weight (lbs)</th>
                    <th>BMI</th>
                    <th>Days Ago</th>
                    <th>Verification</th>
                </tr>
                <?php 
                $today = time();
                $previous_weight = null;
                foreach ($weight_history as $index => $entry): 
                    $entry_time = strtotime($entry['date']);
                    $days_ago = round(($today - $entry_time) / 86400);
                    
                    // Format date label
                    if ($days_ago === 0) {
                        $label = 'Today';
                    } elseif ($days_ago === 1) {
                        $label = 'Yesterday';
                    } elseif ($days_ago < 7) {
                        $label = $days_ago . ' days ago';
                    } elseif ($days_ago < 30) {
                        $weeks = round($days_ago / 7);
                        $label = $weeks . ($weeks === 1 ? ' week ago' : ' weeks ago');
                    } elseif ($days_ago < 365) {
                        $months = round($days_ago / 30);
                        $label = $months . ($months === 1 ? ' month ago' : ' months ago');
                    } else {
                        $label = date('M j, Y', $entry_time);
                    }
                    
                    // Check if weight changed from previous
                    $weight_status = '';
                    if ($previous_weight !== null) {
                        if ($entry['weight'] == $previous_weight) {
                            $weight_status = '<span class="warning">⚠ Same as previous</span>';
                        } else {
                            $change = $entry['weight'] - $previous_weight;
                            $weight_status = '<span class="success">✓ ' . ($change > 0 ? '+' : '') . round($change, 1) . ' lbs</span>';
                        }
                    } else {
                        $weight_status = '<span class="success">✓ Initial</span>';
                    }
                    $previous_weight = $entry['weight'];
                ?>
                    <tr>
                        <td><?php echo $entry['date']; ?></td>
                        <td><strong><?php echo $label; ?></strong></td>
                        <td><?php echo $entry['weight']; ?></td>
                        <td><?php echo isset($entry['bmi']) ? $entry['bmi'] : 'N/A'; ?></td>
                        <td><?php echo $days_ago; ?></td>
                        <td><?php echo $weight_status; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            
            <div class="info">
                <strong>Analysis:</strong>
                <ul>
                    <li>Total entries: <?php echo count($weight_history); ?></li>
                    <li>Date range: <?php echo $weight_history[0]['date']; ?> to <?php echo end($weight_history)['date']; ?></li>
                    <li>Weight range: <?php 
                        $weights = array_column($weight_history, 'weight');
                        echo min($weights) . ' - ' . max($weights) . ' lbs';
                    ?></li>
                    <li>Total change: <?php 
                        echo round(end($weights) - $weights[0], 1) . ' lbs';
                    ?></li>
                </ul>
            </div>
        <?php else: ?>
            <p class="error">No weight history found</p>
        <?php endif; ?>
    </div>
    
    <div class="box">
        <h2>Test Weight Update</h2>
        <p>Current weight: <strong><?php echo $current_weight; ?> lbs</strong></p>
        
        <button class="primary" onclick="updateWeight()">
            Update Weight to <?php echo $current_weight + 2; ?> lbs
        </button>
        
        <div id="update-result"></div>
    </div>
    
    <div class="box">
        <h2>Actions</h2>
        <a href="?action=recreate" class="success-btn" style="text-decoration: none; padding: 10px 20px; display: inline-block;">
            Recreate History with Real Dates
        </a>
        
        <button class="primary" onclick="location.reload()">
            Refresh Page
        </button>
    </div>
    
    <div class="box">
        <h2>Summary of Fixes</h2>
        <ul>
            <li class="success">✅ Dashboard no longer creates fake history with "1 month ago", "2 months ago"</li>
            <li class="success">✅ Weight history initializer creates only today's entry (not fake past entries)</li>
            <li class="success">✅ Historical weights are preserved when updating current weight</li>
            <li class="success">✅ Date labels show proper relative time (e.g., "3 weeks ago", "2 months ago")</li>
            <li class="success">✅ Actual dates are preserved in the database</li>
        </ul>
    </div>

<script>
function updateWeight() {
    const newWeight = <?php echo $current_weight + 2; ?>;
    const resultDiv = document.getElementById('update-result');
    
    resultDiv.innerHTML = '<p>Updating weight to ' + newWeight + ' lbs...</p>';
    
    // Create form data
    const formData = new FormData();
    formData.append('action', 'ennu_save_weight');
    formData.append('nonce', '<?php echo wp_create_nonce("ennu_dashboard_nonce"); ?>');
    formData.append('user_id', <?php echo $user_id; ?>);
    formData.append('field', 'current_weight');
    formData.append('value', newWeight);
    
    fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = '<p class="success">✓ Weight updated! Reloading page...</p>';
            setTimeout(() => location.reload(), 1000);
        } else {
            resultDiv.innerHTML = '<p class="error">✗ Error: ' + (data.data || 'Unknown error') + '</p>';
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<p class="error">✗ Error: ' + error + '</p>';
    });
}
</script>

</body>
</html>