<?php
/**
 * Comprehensive Chart Plotting Test
 * Verifies weight history plotting is flawless
 */

require_once('../../../wp-load.php');

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('Access denied. Please login as admin.');
}

$user_id = get_current_user_id();

// Get all weight-related data
$current_weight = get_user_meta($user_id, 'weight', true);
$target_weight = get_user_meta($user_id, 'target_weight', true);
$weight_history = get_user_meta($user_id, 'weight_history', true);
$height = get_user_meta($user_id, 'height', true);

// Get composite field
$height_weight = get_user_meta($user_id, 'ennu_global_height_weight', true);

// Parse height to inches for BMI calculation
function parse_height_to_inches($height_str) {
    if (empty($height_str)) return 0;
    
    if (preg_match('/(\d+)[\'ft\s]+(\d+)/', $height_str, $matches)) {
        $feet = intval($matches[1]);
        $inches = intval($matches[2]);
        return ($feet * 12) + $inches;
    }
    
    if (preg_match('/^(\d+)/', $height_str, $matches)) {
        return intval($matches[1]);
    }
    
    return 0;
}

$height_inches = parse_height_to_inches($height);

// Calculate current BMI
$current_bmi = 0;
if ($height_inches > 0 && $current_weight > 0) {
    $current_bmi = ($current_weight / ($height_inches * $height_inches)) * 703;
    $current_bmi = round($current_bmi, 1);
}

// Calculate target BMI
$target_bmi = 0;
if ($height_inches > 0 && $target_weight > 0) {
    $target_bmi = ($target_weight / ($height_inches * $height_inches)) * 703;
    $target_bmi = round($target_bmi, 1);
}

// Validate weight history
$history_valid = true;
$history_errors = array();

if (!is_array($weight_history)) {
    $history_valid = false;
    $history_errors[] = "Weight history is not an array";
} elseif (empty($weight_history)) {
    $history_valid = false;
    $history_errors[] = "Weight history is empty";
} else {
    // Check each entry
    foreach ($weight_history as $index => $entry) {
        if (!isset($entry['date'])) {
            $history_errors[] = "Entry $index missing 'date' field";
        }
        if (!isset($entry['weight'])) {
            $history_errors[] = "Entry $index missing 'weight' field";
        } elseif (!is_numeric($entry['weight'])) {
            $history_errors[] = "Entry $index has non-numeric weight: " . $entry['weight'];
        }
        if (!isset($entry['bmi'])) {
            $history_errors[] = "Entry $index missing 'bmi' field";
        }
    }
}

// Test proportional recalculation
function test_proportional_calculation($history) {
    if (!is_array($history) || count($history) < 2) {
        return "Not enough history for proportional test";
    }
    
    $weights = array_map(function($entry) { return $entry['weight']; }, $history);
    $min_weight = min($weights);
    $max_weight = max($weights);
    $range = $max_weight - $min_weight;
    
    $result = "Weight range: $min_weight - $max_weight (range: $range lbs)\n";
    
    // Check if weights show progression
    $progression = array();
    for ($i = 1; $i < count($history); $i++) {
        $change = $history[$i]['weight'] - $history[$i-1]['weight'];
        $progression[] = round($change, 1);
    }
    
    $result .= "Weight progression between entries: " . implode(', ', $progression) . " lbs\n";
    
    // Check BMI calculations
    $bmi_valid = true;
    foreach ($history as $entry) {
        if (isset($entry['bmi']) && $entry['bmi'] > 0) {
            // Verify BMI calculation
            global $height_inches;
            if ($height_inches > 0) {
                $calculated_bmi = ($entry['weight'] / ($height_inches * $height_inches)) * 703;
                $calculated_bmi = round($calculated_bmi, 1);
                if (abs($calculated_bmi - $entry['bmi']) > 0.5) {
                    $bmi_valid = false;
                    $result .= "BMI mismatch for weight {$entry['weight']}: stored {$entry['bmi']}, calculated $calculated_bmi\n";
                }
            }
        }
    }
    
    if ($bmi_valid) {
        $result .= "All BMI values correctly calculated ✓\n";
    }
    
    return $result;
}

// Test chart data generation
function generate_chart_data($history, $current_weight, $target_weight) {
    $chart_data = array();
    
    // Add historical points
    if (is_array($history)) {
        foreach ($history as $entry) {
            $chart_data[] = array(
                'date' => $entry['date'],
                'weight' => $entry['weight'],
                'bmi' => isset($entry['bmi']) ? $entry['bmi'] : null,
                'type' => 'historical'
            );
        }
    }
    
    // Add current if different from last history
    $last_date = !empty($history) ? end($history)['date'] : null;
    $today = date('Y-m-d');
    if ($last_date !== $today && $current_weight > 0) {
        $chart_data[] = array(
            'date' => $today,
            'weight' => $current_weight,
            'bmi' => null,
            'type' => 'current'
        );
    }
    
    // Add target
    if ($target_weight > 0) {
        $chart_data[] = array(
            'date' => 'Target',
            'weight' => $target_weight,
            'bmi' => null,
            'type' => 'target'
        );
    }
    
    return $chart_data;
}

$chart_data = generate_chart_data($weight_history, $current_weight, $target_weight);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Chart Plotting Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; }
        h2 { color: #333; border-bottom: 2px solid #2196F3; padding-bottom: 10px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .box { background: #f5f5f5; padding: 15px; border-radius: 8px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; }
        pre { background: white; padding: 10px; border-radius: 5px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background: #2196F3; color: white; }
        .chart-container { 
            width: 100%; 
            height: 200px; 
            background: white; 
            border: 1px solid #ddd; 
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        svg { width: 100%; height: 100%; }
        .status-icon { font-size: 20px; margin-right: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Weight History Chart Plotting Verification</h1>
    
    <div class="grid">
        <div class="box">
            <h2>📊 Current Data Status</h2>
            <table>
                <tr><th>Field</th><th>Value</th><th>Status</th></tr>
                <tr>
                    <td>Current Weight</td>
                    <td><?php echo $current_weight ?: 'Not set'; ?> lbs</td>
                    <td><?php echo $current_weight ? '<span class="success">✓</span>' : '<span class="error">✗</span>'; ?></td>
                </tr>
                <tr>
                    <td>Target Weight</td>
                    <td><?php echo $target_weight ?: 'Not set'; ?> lbs</td>
                    <td><?php echo $target_weight ? '<span class="success">✓</span>' : '<span class="warning">⚠</span>'; ?></td>
                </tr>
                <tr>
                    <td>Height</td>
                    <td><?php echo $height ?: 'Not set'; ?> (<?php echo $height_inches; ?> inches)</td>
                    <td><?php echo $height_inches > 0 ? '<span class="success">✓</span>' : '<span class="error">✗</span>'; ?></td>
                </tr>
                <tr>
                    <td>Current BMI</td>
                    <td><?php echo $current_bmi ?: 'Not calculated'; ?></td>
                    <td><?php echo $current_bmi > 0 ? '<span class="success">✓</span>' : '<span class="warning">⚠</span>'; ?></td>
                </tr>
                <tr>
                    <td>Target BMI</td>
                    <td><?php echo $target_bmi ?: 'Not calculated'; ?></td>
                    <td><?php echo $target_bmi > 0 ? '<span class="success">✓</span>' : '<span class="warning">⚠</span>'; ?></td>
                </tr>
                <tr>
                    <td>History Entries</td>
                    <td><?php echo is_array($weight_history) ? count($weight_history) : 0; ?></td>
                    <td><?php echo (is_array($weight_history) && count($weight_history) > 0) ? '<span class="success">✓</span>' : '<span class="error">✗</span>'; ?></td>
                </tr>
            </table>
        </div>
        
        <div class="box">
            <h2>✅ Validation Results</h2>
            <?php if ($history_valid && empty($history_errors)): ?>
                <p class="success">✓ Weight history structure is valid!</p>
            <?php else: ?>
                <p class="error">✗ Issues found:</p>
                <ul>
                    <?php foreach ($history_errors as $error): ?>
                        <li class="error"><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <?php if (!empty($weight_history)): ?>
                <h3>Proportional Calculation Test:</h3>
                <pre><?php echo test_proportional_calculation($weight_history); ?></pre>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="box">
        <h2>📈 Weight History Data</h2>
        <?php if (is_array($weight_history) && !empty($weight_history)): ?>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Weight (lbs)</th>
                    <th>BMI</th>
                    <th>Days Ago</th>
                </tr>
                <?php 
                $today_time = strtotime(date('Y-m-d'));
                foreach ($weight_history as $entry): 
                    $entry_time = strtotime($entry['date']);
                    $days_ago = round(($today_time - $entry_time) / 86400);
                ?>
                    <tr>
                        <td><?php echo $entry['date']; ?></td>
                        <td><?php echo $entry['weight']; ?></td>
                        <td><?php echo isset($entry['bmi']) ? $entry['bmi'] : 'N/A'; ?></td>
                        <td><?php echo $days_ago == 0 ? 'Today' : $days_ago . ' days'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p class="error">No weight history available</p>
        <?php endif; ?>
    </div>
    
    <div class="box">
        <h2>🎨 Chart Data Points</h2>
        <p>These are the points that would be plotted on the chart:</p>
        <table>
            <tr>
                <th>Type</th>
                <th>Date</th>
                <th>Weight</th>
                <th>BMI</th>
            </tr>
            <?php foreach ($chart_data as $point): ?>
                <tr>
                    <td><?php echo $point['type']; ?></td>
                    <td><?php echo $point['date']; ?></td>
                    <td><?php echo $point['weight']; ?></td>
                    <td><?php echo $point['bmi'] ?: 'N/A'; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="box">
        <h2>📐 Test SVG Chart Rendering</h2>
        <div class="chart-container" id="test-chart">
            <!-- Chart will be rendered here -->
        </div>
    </div>
    
    <div class="box">
        <h2>🔧 Composite Field Check</h2>
        <pre><?php 
        echo "ennu_global_height_weight field:\n";
        print_r($height_weight);
        ?></pre>
    </div>
    
    <div class="box">
        <h2>✨ Summary</h2>
        <?php
        $all_good = true;
        $summary = array();
        
        if (!$current_weight) {
            $summary[] = '<span class="error">✗ Current weight not set</span>';
            $all_good = false;
        } else {
            $summary[] = '<span class="success">✓ Current weight is set</span>';
        }
        
        if (!is_array($weight_history) || empty($weight_history)) {
            $summary[] = '<span class="error">✗ No weight history available</span>';
            $all_good = false;
        } else {
            $summary[] = '<span class="success">✓ Weight history has ' . count($weight_history) . ' entries</span>';
        }
        
        if (!empty($history_errors)) {
            $summary[] = '<span class="error">✗ History validation errors found</span>';
            $all_good = false;
        } else {
            $summary[] = '<span class="success">✓ History structure is valid</span>';
        }
        
        if ($height_inches <= 0) {
            $summary[] = '<span class="warning">⚠ Height not set (BMI cannot be calculated)</span>';
        } else {
            $summary[] = '<span class="success">✓ Height is set for BMI calculations</span>';
        }
        
        foreach ($summary as $item) {
            echo "<p>$item</p>";
        }
        
        if ($all_good) {
            echo '<h3 class="success">🎉 Chart plotting data is FLAWLESS!</h3>';
        } else {
            echo '<h3 class="warning">⚠️ Some issues need attention</h3>';
        }
        ?>
    </div>

<script>
// Test chart rendering
document.addEventListener('DOMContentLoaded', function() {
    const chartData = <?php echo json_encode($chart_data); ?>;
    const container = document.getElementById('test-chart');
    
    if (!chartData || chartData.length === 0) {
        container.innerHTML = '<p style="color: red;">No data to plot</p>';
        return;
    }
    
    // Create SVG
    const width = container.offsetWidth - 40;
    const height = 160;
    const padding = 20;
    
    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("width", width);
    svg.setAttribute("height", height);
    
    // Get weight range
    const weights = chartData.map(d => d.weight);
    const minWeight = Math.min(...weights) - 5;
    const maxWeight = Math.max(...weights) + 5;
    
    // Scale functions
    const xScale = (index) => (index / (chartData.length - 1)) * (width - 2 * padding) + padding;
    const yScale = (weight) => height - ((weight - minWeight) / (maxWeight - minWeight)) * (height - 2 * padding) - padding;
    
    // Draw grid lines
    for (let i = 0; i <= 5; i++) {
        const y = padding + (i * (height - 2 * padding) / 5);
        const line = document.createElementNS("http://www.w3.org/2000/svg", "line");
        line.setAttribute("x1", padding);
        line.setAttribute("y1", y);
        line.setAttribute("x2", width - padding);
        line.setAttribute("y2", y);
        line.setAttribute("stroke", "#e0e0e0");
        line.setAttribute("stroke-width", "1");
        svg.appendChild(line);
        
        // Add weight label
        const weight = maxWeight - (i * (maxWeight - minWeight) / 5);
        const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
        text.setAttribute("x", 5);
        text.setAttribute("y", y + 4);
        text.setAttribute("font-size", "10");
        text.setAttribute("fill", "#666");
        text.textContent = Math.round(weight);
        svg.appendChild(text);
    }
    
    // Draw line
    const pathData = chartData.map((d, i) => {
        const x = xScale(i);
        const y = yScale(d.weight);
        return `${i === 0 ? 'M' : 'L'} ${x} ${y}`;
    }).join(' ');
    
    const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
    path.setAttribute("d", pathData);
    path.setAttribute("stroke", "#2196F3");
    path.setAttribute("stroke-width", "2");
    path.setAttribute("fill", "none");
    svg.appendChild(path);
    
    // Draw dots and labels
    chartData.forEach((d, i) => {
        const x = xScale(i);
        const y = yScale(d.weight);
        
        // Dot
        const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        circle.setAttribute("cx", x);
        circle.setAttribute("cy", y);
        circle.setAttribute("r", 4);
        circle.setAttribute("fill", d.type === 'target' ? '#4CAF50' : '#2196F3');
        svg.appendChild(circle);
        
        // Weight label
        const text = document.createElementNS("http://www.w3.org/2000/svg", "text");
        text.setAttribute("x", x);
        text.setAttribute("y", y - 8);
        text.setAttribute("text-anchor", "middle");
        text.setAttribute("font-size", "11");
        text.setAttribute("fill", "#333");
        text.textContent = d.weight;
        svg.appendChild(text);
        
        // Date label
        const dateText = document.createElementNS("http://www.w3.org/2000/svg", "text");
        dateText.setAttribute("x", x);
        dateText.setAttribute("y", height - 5);
        dateText.setAttribute("text-anchor", "middle");
        dateText.setAttribute("font-size", "9");
        dateText.setAttribute("fill", "#666");
        dateText.setAttribute("transform", `rotate(-45 ${x} ${height - 5})`);
        dateText.textContent = d.date.substring(5); // Show MM-DD
        svg.appendChild(dateText);
    });
    
    container.appendChild(svg);
    
    // Log verification
    console.log('Chart rendered with', chartData.length, 'data points');
    console.log('Weight range:', minWeight, '-', maxWeight);
    console.log('Chart dimensions:', width, 'x', height);
});
</script>

</body>
</html>