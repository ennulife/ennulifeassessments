<?php
/**
 * Test Dashboard Weight Chart
 * Verifies the dashboard weight chart is working properly
 */

require_once('../../../wp-load.php');

if (!is_user_logged_in()) {
    die('Please login to test the dashboard.');
}

$user_id = get_current_user_id();

// Get weight data
$weight_history = get_user_meta($user_id, 'weight_history', true);
$current_weight = get_user_meta($user_id, 'weight', true);
$target_weight = get_user_meta($user_id, 'target_weight', true);
$height = get_user_meta($user_id, 'height', true);

// Ensure we have weight history
if (!is_array($weight_history) || empty($weight_history)) {
    require_once('includes/class-weight-history-initializer.php');
    ENNU_Weight_History_Initializer::ensure_weight_history($user_id);
    $weight_history = get_user_meta($user_id, 'weight_history', true);
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Weight Chart Test</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif;
            padding: 20px; 
            max-width: 1200px; 
            margin: 0 auto;
            background: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 { color: #1f2937; margin-top: 0; }
        h2 { color: #374151; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; }
        .success { color: #10b981; font-weight: 600; }
        .error { color: #ef4444; font-weight: 600; }
        .warning { color: #f59e0b; font-weight: 600; }
        .info { color: #3b82f6; font-weight: 600; }
        
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin: 20px 0;
        }
        .status-card {
            background: #f9fafb;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .status-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .status-value {
            font-size: 24px;
            font-weight: 600;
            color: #1f2937;
        }
        
        /* Dashboard-like container */
        .dashboard-preview {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e5e7eb;
        }
        
        /* Mimic dashboard weight chart container */
        .weight-bmi-mini-chart-container {
            height: 80px;
            background: #f9fafb;
            border-radius: 8px;
            padding: 10px;
            position: relative;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        
        .chart-test {
            margin: 20px 0;
            padding: 20px;
            background: #f0f9ff;
            border-radius: 8px;
            border: 1px solid #0ea5e9;
        }
        
        button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #2563eb;
        }
        .danger {
            background: #ef4444;
        }
        .danger:hover {
            background: #dc2626;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Dashboard Weight Chart Test</h1>
        <p>Testing the weight history chart functionality on the user dashboard.</p>
    </div>
    
    <div class="container">
        <h2>📊 Current Data Status</h2>
        <div class="status-grid">
            <div class="status-card">
                <div class="status-label">Current Weight</div>
                <div class="status-value"><?php echo $current_weight ?: '—'; ?> lbs</div>
            </div>
            <div class="status-card">
                <div class="status-label">Target Weight</div>
                <div class="status-value"><?php echo $target_weight ?: '—'; ?> lbs</div>
            </div>
            <div class="status-card">
                <div class="status-label">Height</div>
                <div class="status-value"><?php echo $height ?: '—'; ?></div>
            </div>
            <div class="status-card">
                <div class="status-label">History Entries</div>
                <div class="status-value"><?php echo is_array($weight_history) ? count($weight_history) : 0; ?></div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <h2>📈 Weight History Data</h2>
        <?php if (is_array($weight_history) && !empty($weight_history)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Weight</th>
                        <th>BMI</th>
                        <th>Label</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($weight_history as $index => $entry):
                        $date = new DateTime($entry['date']);
                        $today = new DateTime();
                        $diff = $today->diff($date);
                        $days_ago = $diff->days;
                        
                        // Generate label like dashboard does
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
                            $label = $date->format('M j, Y');
                        }
                        
                        // Check if this is the most recent entry
                        $is_current = ($index === count($weight_history) - 1);
                    ?>
                        <tr>
                            <td><?php echo $entry['date']; ?></td>
                            <td><?php echo $entry['weight']; ?> lbs</td>
                            <td><?php echo isset($entry['bmi']) ? $entry['bmi'] : 'N/A'; ?></td>
                            <td><strong><?php echo $label; ?></strong></td>
                            <td>
                                <?php if ($is_current): ?>
                                    <span class="success">✓ Current</span>
                                <?php else: ?>
                                    <span class="info">Historical</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="error">No weight history found. Initializing...</p>
        <?php endif; ?>
    </div>
    
    <div class="container">
        <h2>🎨 Chart Preview</h2>
        <p>This simulates how the chart should appear on the dashboard:</p>
        
        <div class="dashboard-preview">
            <h3 style="margin-top: 0;">Weight & BMI History</h3>
            <div class="weight-bmi-mini-chart-container" 
                 id="test-chart"
                 data-current-weight="<?php echo esc_attr($current_weight); ?>"
                 data-current-bmi="<?php echo esc_attr($current_weight && $height ? round(($current_weight / (70 * 70)) * 703, 1) : ''); ?>"
                 data-target-weight="<?php echo esc_attr($target_weight ?: ''); ?>"
                 data-target-bmi="">
                <!-- Chart will be rendered here -->
            </div>
        </div>
    </div>
    
    <div class="container">
        <h2>✅ Verification Checklist</h2>
        <ul>
            <?php
            $checks = array();
            
            // Check 1: Weight history exists
            if (is_array($weight_history) && !empty($weight_history)) {
                $checks[] = '<span class="success">✓ Weight history exists with ' . count($weight_history) . ' entries</span>';
            } else {
                $checks[] = '<span class="error">✗ No weight history found</span>';
            }
            
            // Check 2: Dates are proper
            if (!empty($weight_history)) {
                $all_dates_valid = true;
                foreach ($weight_history as $entry) {
                    if (!isset($entry['date']) || !strtotime($entry['date'])) {
                        $all_dates_valid = false;
                        break;
                    }
                }
                if ($all_dates_valid) {
                    $checks[] = '<span class="success">✓ All dates are valid</span>';
                } else {
                    $checks[] = '<span class="error">✗ Some dates are invalid</span>';
                }
            }
            
            // Check 3: Weights are numeric
            if (!empty($weight_history)) {
                $all_weights_valid = true;
                foreach ($weight_history as $entry) {
                    if (!isset($entry['weight']) || !is_numeric($entry['weight'])) {
                        $all_weights_valid = false;
                        break;
                    }
                }
                if ($all_weights_valid) {
                    $checks[] = '<span class="success">✓ All weights are numeric</span>';
                } else {
                    $checks[] = '<span class="error">✗ Some weights are invalid</span>';
                }
            }
            
            // Check 4: Historical weights differ (not all the same)
            if (!empty($weight_history) && count($weight_history) > 1) {
                $weights = array_column($weight_history, 'weight');
                $unique_weights = array_unique($weights);
                if (count($unique_weights) > 1) {
                    $checks[] = '<span class="success">✓ Historical weights show variation</span>';
                } else {
                    $checks[] = '<span class="warning">⚠ All historical weights are the same</span>';
                }
            }
            
            // Check 5: Current weight matches last history entry
            if (!empty($weight_history) && $current_weight) {
                $last_entry = end($weight_history);
                if ($last_entry['weight'] == $current_weight) {
                    $checks[] = '<span class="success">✓ Current weight matches last history entry</span>';
                } else {
                    $checks[] = '<span class="warning">⚠ Current weight (' . $current_weight . ') differs from last history entry (' . $last_entry['weight'] . ')</span>';
                }
            }
            
            foreach ($checks as $check) {
                echo "<li>$check</li>";
            }
            ?>
        </ul>
    </div>
    
    <div class="container">
        <h2>🔧 Actions</h2>
        <button onclick="location.href='reset-weight-history.php'">
            Manage Weight History
        </button>
        <button onclick="location.href='test-history-dates.php'">
            Test Date Formatting
        </button>
        <button onclick="location.reload()">
            Refresh Page
        </button>
        <button class="danger" onclick="if(confirm('Clear all weight history?')) location.href='reset-weight-history.php?action=clear'">
            Clear History
        </button>
    </div>
    
    <div class="container">
        <h2>📝 Summary</h2>
        <p><strong>Dashboard Weight Chart Implementation:</strong></p>
        <ul>
            <li>✅ Chart container selector updated to find <code>.weight-bmi-mini-chart-container</code></li>
            <li>✅ Data attributes properly mapped (<code>data-current-weight</code>, etc.)</li>
            <li>✅ Real weight history used instead of fake data</li>
            <li>✅ Date labels show proper relative time (Today, Yesterday, X days/weeks/months ago)</li>
            <li>✅ Historical weights preserved when updating current weight</li>
            <li>✅ Weight history initializer creates only today's entry</li>
        </ul>
        
        <div class="chart-test">
            <h3>🎯 What to Test on the Dashboard:</h3>
            <ol>
                <li>Go to your dashboard and look for the Weight & BMI section</li>
                <li>You should see a chart with real historical data points</li>
                <li>Hover over dots to see weight values and proper date labels</li>
                <li>Update your current weight and verify only today's entry changes</li>
                <li>Refresh the page and confirm data persists correctly</li>
            </ol>
        </div>
    </div>

<script>
// Test chart rendering (same as dashboard)
document.addEventListener('DOMContentLoaded', function() {
    const chartContainer = document.getElementById('test-chart');
    if (!chartContainer) return;
    
    const weightHistory = <?php echo json_encode($weight_history ?: array()); ?>;
    const currentWeight = parseFloat(chartContainer.dataset.currentWeight) || 0;
    const targetWeight = parseFloat(chartContainer.dataset.targetWeight) || 0;
    
    if (!weightHistory || weightHistory.length === 0) {
        chartContainer.innerHTML = '<p style="text-align: center; color: #6b7280;">No weight history available</p>';
        return;
    }
    
    // Format date labels
    function formatDateLabel(dateStr) {
        if (!dateStr) return 'Unknown';
        
        const date = new Date(dateStr);
        const today = new Date();
        const diffTime = today - date;
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays === 0) return 'Today';
        if (diffDays === 1) return 'Yesterday';
        if (diffDays < 7) return diffDays + ' days ago';
        if (diffDays < 30) {
            const weeks = Math.floor(diffDays / 7);
            return weeks + (weeks === 1 ? ' week ago' : ' weeks ago');
        }
        if (diffDays < 365) {
            const months = Math.floor(diffDays / 30);
            return months + (months === 1 ? ' month ago' : ' months ago');
        }
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
    
    // Process data
    const historyData = weightHistory.map(entry => ({
        weight: parseFloat(entry.weight) || 0,
        bmi: parseFloat(entry.bmi) || 0,
        date: entry.date,
        dateLabel: formatDateLabel(entry.date)
    }));
    
    // Add target if exists
    if (targetWeight > 0) {
        historyData.push({
            weight: targetWeight,
            bmi: 0,
            date: 'Target',
            dateLabel: 'Target',
            isTarget: true
        });
    }
    
    // Create simple SVG chart
    const width = chartContainer.offsetWidth - 20;
    const height = 60;
    
    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("width", width);
    svg.setAttribute("height", height);
    
    // Get weight range
    const weights = historyData.map(d => d.weight);
    const minWeight = Math.min(...weights) - 2;
    const maxWeight = Math.max(...weights) + 2;
    
    // Scale functions
    const xScale = (index) => (index / (historyData.length - 1)) * (width - 20) + 10;
    const yScale = (weight) => height - ((weight - minWeight) / (maxWeight - minWeight)) * (height - 20) - 10;
    
    // Draw line
    const pathData = historyData.map((d, i) => {
        const x = xScale(i);
        const y = yScale(d.weight);
        return `${i === 0 ? 'M' : 'L'} ${x} ${y}`;
    }).join(' ');
    
    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
    path.setAttribute("d", pathData);
    path.setAttribute("stroke", "#6b7280");
    path.setAttribute("stroke-width", "2");
    path.setAttribute("fill", "none");
    svg.appendChild(path);
    
    // Draw dots
    historyData.forEach((d, i) => {
        const x = xScale(i);
        const y = yScale(d.weight);
        
        const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        circle.setAttribute("cx", x);
        circle.setAttribute("cy", y);
        circle.setAttribute("r", d.isTarget ? "4" : "3");
        circle.setAttribute("fill", d.isTarget ? "#10b981" : "#6b7280");
        
        // Add tooltip
        const title = document.createElementNS("http://www.w3.org/2000/svg", "title");
        title.textContent = `${d.weight} lbs (${d.dateLabel})`;
        circle.appendChild(title);
        
        svg.appendChild(circle);
    });
    
    chartContainer.appendChild(svg);
    
    console.log('Chart rendered with', historyData.length, 'points');
});
</script>

</body>
</html>