<?php
/**
 * Reset Weight History
 * Utility to clear and regenerate weight history with proper values
 */

require_once('../../../wp-load.php');

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('Access denied. Please login as admin.');
}

$user_id = get_current_user_id();
$action = isset($_GET['action']) ? $_GET['action'] : '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Weight History</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .box { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        button { padding: 10px 20px; font-size: 16px; margin: 5px; cursor: pointer; }
        .danger { background: #f44336; color: white; border: none; border-radius: 4px; }
        .primary { background: #2196F3; color: white; border: none; border-radius: 4px; }
        .success-btn { background: #4CAF50; color: white; border: none; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #2196F3; color: white; }
    </style>
</head>
<body>
    <h1>Weight History Management</h1>
    
    <?php if ($action === 'clear'): ?>
        <?php
        // Clear weight history
        delete_user_meta($user_id, 'weight_history');
        delete_user_meta($user_id, 'ennu_bmi_history');
        ?>
        <div class="box">
            <p class="success">✓ Weight history has been cleared!</p>
            <a href="?">Back to management</a>
        </div>
        
    <?php elseif ($action === 'create'): ?>
        <?php
        // Create realistic weight history
        require_once('includes/class-weight-history-initializer.php');
        
        // Clear existing first
        delete_user_meta($user_id, 'weight_history');
        delete_user_meta($user_id, 'ennu_bmi_history');
        
        // Get current weight
        $current_weight = get_user_meta($user_id, 'weight', true);
        if (!$current_weight) {
            $current_weight = 175; // Default weight
            update_user_meta($user_id, 'weight', $current_weight);
        }
        
        // Get height for BMI
        $height = get_user_meta($user_id, 'height', true);
        $height_inches = 0;
        if ($height && preg_match('/(\d+)[\'ft\s]+(\d+)/', $height, $matches)) {
            $height_inches = ($matches[1] * 12) + $matches[2];
        }
        
        // Create manual history with realistic weight loss progression
        $history = array();
        $base_weight = $current_weight + 15; // Start 15 lbs heavier 6 months ago
        
        // Create 7 data points over 6 months
        $dates = array(
            '-6 months' => $base_weight,
            '-5 months' => $base_weight - 2.5,
            '-4 months' => $base_weight - 5,
            '-3 months' => $base_weight - 7.5,
            '-2 months' => $base_weight - 10,
            '-1 month' => $base_weight - 12.5,
            'today' => $current_weight
        );
        
        foreach ($dates as $time => $weight) {
            $date = ($time === 'today') ? date('Y-m-d') : date('Y-m-d', strtotime($time));
            
            $bmi = null;
            if ($height_inches > 0) {
                $bmi = ($weight / ($height_inches * $height_inches)) * 703;
                $bmi = round($bmi, 1);
            }
            
            $history[] = array(
                'date' => $date,
                'weight' => round($weight, 1),
                'bmi' => $bmi,
                'timestamp' => strtotime($date)
            );
        }
        
        // Save the history
        update_user_meta($user_id, 'weight_history', $history);
        
        // Also create BMI history
        $bmi_history = array();
        foreach ($history as $entry) {
            if (isset($entry['bmi']) && $entry['bmi']) {
                $bmi_history[] = array(
                    'date' => $entry['date'],
                    'value' => $entry['bmi'],
                    'timestamp' => $entry['timestamp']
                );
            }
        }
        update_user_meta($user_id, 'ennu_bmi_history', $bmi_history);
        ?>
        
        <div class="box">
            <p class="success">✓ Created realistic weight history with <?php echo count($history); ?> entries!</p>
            <p>Starting weight 6 months ago: <strong><?php echo $base_weight; ?> lbs</strong></p>
            <p>Current weight: <strong><?php echo $current_weight; ?> lbs</strong></p>
            <p>Total progress: <strong>-<?php echo ($base_weight - $current_weight); ?> lbs</strong></p>
            
            <h3>Created History:</h3>
            <table>
                <tr><th>Date</th><th>Weight</th><th>BMI</th></tr>
                <?php foreach ($history as $entry): ?>
                    <tr>
                        <td><?php echo $entry['date']; ?></td>
                        <td><?php echo $entry['weight']; ?> lbs</td>
                        <td><?php echo $entry['bmi'] ?: 'N/A'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            
            <a href="?">Back to management</a>
        </div>
        
    <?php else: ?>
        <?php
        // Show current state
        $weight_history = get_user_meta($user_id, 'weight_history', true);
        $current_weight = get_user_meta($user_id, 'weight', true);
        $target_weight = get_user_meta($user_id, 'target_weight', true);
        $height = get_user_meta($user_id, 'height', true);
        ?>
        
        <div class="box">
            <h2>Current Status</h2>
            <p>Current Weight: <strong><?php echo $current_weight ?: 'Not set'; ?> lbs</strong></p>
            <p>Target Weight: <strong><?php echo $target_weight ?: 'Not set'; ?> lbs</strong></p>
            <p>Height: <strong><?php echo $height ?: 'Not set'; ?></strong></p>
            <p>History Entries: <strong><?php echo is_array($weight_history) ? count($weight_history) : 0; ?></strong></p>
        </div>
        
        <?php if (is_array($weight_history) && !empty($weight_history)): ?>
            <div class="box">
                <h2>Current Weight History</h2>
                <table>
                    <tr><th>Date</th><th>Weight</th><th>BMI</th></tr>
                    <?php foreach ($weight_history as $entry): ?>
                        <tr>
                            <td><?php echo $entry['date']; ?></td>
                            <td><?php echo $entry['weight']; ?> lbs</td>
                            <td><?php echo isset($entry['bmi']) ? $entry['bmi'] : 'N/A'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>
        
        <div class="box">
            <h2>Actions</h2>
            <p class="warning">⚠️ These actions will modify your weight history data</p>
            
            <form style="display: inline;">
                <input type="hidden" name="action" value="clear">
                <button type="submit" class="danger" onclick="return confirm('Are you sure you want to clear all weight history?')">
                    Clear Weight History
                </button>
            </form>
            
            <form style="display: inline;">
                <input type="hidden" name="action" value="create">
                <button type="submit" class="success-btn" onclick="return confirm('This will create a realistic weight loss history. Continue?')">
                    Create Realistic History
                </button>
            </form>
            
            <button class="primary" onclick="location.href='test-weight-history-fix.php'">
                Test Weight Update
            </button>
            
            <button class="primary" onclick="location.href='test-chart-plotting.php'">
                Test Chart Plotting
            </button>
        </div>
        
        <div class="box">
            <h2>Instructions</h2>
            <ol>
                <li><strong>Clear Weight History:</strong> Removes all weight history entries</li>
                <li><strong>Create Realistic History:</strong> Creates a 6-month weight loss progression</li>
                <li><strong>Test Weight Update:</strong> Verifies that historical weights are preserved when updating current weight</li>
                <li><strong>Test Chart Plotting:</strong> Comprehensive chart plotting verification</li>
            </ol>
            
            <h3>Fixed Issues:</h3>
            <ul>
                <li>✅ Historical weights no longer change when current weight is updated</li>
                <li>✅ BMI values are recalculated correctly for all entries</li>
                <li>✅ Today's weight is properly added or updated</li>
                <li>✅ Chart displays actual historical data, not mock values</li>
            </ul>
        </div>
    <?php endif; ?>
    
</body>
</html>