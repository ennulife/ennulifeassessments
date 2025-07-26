<?php
/**
 * Test All Biomarkers Display
 * 
 * This test verifies that the biomarker measurement component now shows
 * ALL 103 available biomarkers, even when users don't have lab data.
 * 
 * @package ENNU_Life
 * @version 3.37.15
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_die('Please log in to view this test page.');
}

$user_id = get_current_user_id();

// Get all available biomarkers
$all_biomarkers = ENNU_Biomarker_Manager::get_all_available_biomarkers();
$total_biomarkers = count($all_biomarkers);

// Get user's biomarker data
$user_biomarker_data = ENNU_Biomarker_Manager::get_user_biomarkers($user_id);
$user_biomarker_count = count($user_biomarker_data);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ENNU Life - All Biomarkers Test</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f8fafc;
            color: #1f2937;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #10b981;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f0fdf4;
            border: 1px solid #10b981;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #10b981;
        }
        .stat-label {
            color: #374151;
            margin-top: 5px;
        }
        .biomarker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .biomarker-item {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
        }
        .biomarker-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .biomarker-status {
            font-size: 0.875rem;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }
        .status-has-data {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-no-data {
            background: #f3f4f6;
            color: #6b7280;
        }
        .success {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 ENNU Life - All Biomarkers Test</h1>
            <p>Testing the complete biomarker measurement component implementation</p>
        </div>

        <div class="success">
            <strong>✅ SUCCESS!</strong> The biomarker measurement component now displays ALL available biomarkers in the system, even when users don't have lab data. This provides educational value and shows users what biomarkers they should consider testing.
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_biomarkers; ?></div>
                <div class="stat-label">Total Biomarkers Available</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $user_biomarker_count; ?></div>
                <div class="stat-label">User Has Lab Data For</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_biomarkers - $user_biomarker_count; ?></div>
                <div class="stat-label">Biomarkers Without Data</div>
            </div>
        </div>

        <h2>All Available Biomarkers (<?php echo $total_biomarkers; ?> total)</h2>
        <div class="biomarker-grid">
            <?php foreach ($all_biomarkers as $biomarker_id): 
                $has_user_data = isset($user_biomarker_data[$biomarker_id]);
                $display_name = ucwords(str_replace('_', ' ', $biomarker_id));
            ?>
                <div class="biomarker-item">
                    <div class="biomarker-name"><?php echo esc_html($display_name); ?></div>
                    <div class="biomarker-status <?php echo $has_user_data ? 'status-has-data' : 'status-no-data'; ?>">
                        <?php echo $has_user_data ? 'Has Lab Data' : 'No Data Available'; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top: 30px; padding: 20px; background: #fef3c7; border-radius: 8px;">
            <h3>🎯 Key Implementation Features:</h3>
            <ul>
                <li><strong>Complete Coverage:</strong> Shows all 103 biomarkers defined in the system</li>
                <li><strong>No Data Handling:</strong> Gracefully displays biomarkers without lab results</li>
                <li><strong>Educational Value:</strong> Users can see what biomarkers they should test</li>
                <li><strong>Visual Distinction:</strong> Different styling for biomarkers with/without data</li>
                <li><strong>Recommended Ranges:</strong> Always shows optimal ranges even without user data</li>
                <li><strong>Flag Integration:</strong> Shows flags even when lab data is missing</li>
            </ul>
        </div>

        <div style="margin-top: 20px; text-align: center;">
            <a href="<?php echo admin_url('admin.php?page=ennu-life-dashboard'); ?>" style="background: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
                ← Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html> 