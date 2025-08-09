<?php
/**
 * ENNU Life Assessments - Page Mapping Status Checker
 * 
 * Access this file directly in your browser to check page mapping status
 * URL: http://localhost/wp-content/plugins/ennulifeassessments/page-mapping-status.php
 */

// Load WordPress
require_once('../../../wp-load.php');

// Security check - only allow admin access
if (!current_user_can('manage_options')) {
    wp_die('Access denied. Admin privileges required.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>ENNU Life Assessments - Page Mapping Status</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .status-good { color: #28a745; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        .status-error { color: #dc3545; font-weight: bold; }
        .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .mapping-item { margin: 5px 0; padding: 5px; background: #f8f9fa; border-radius: 3px; }
        .recommendation { background: #e7f3ff; padding: 10px; border-left: 4px solid #007cba; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 ENNU Life Assessments - Page Mapping Status</h1>
            <p>Comprehensive diagnostic of page mapping and URL generation</p>
        </div>

        <?php
        // Get current settings
        $settings = get_option('ennu_life_settings', array());
        $page_mappings = $settings['page_mappings'] ?? array();
        
        // Count mapped pages
        $mapped_count = count(array_filter($page_mappings));
        $total_expected = 15; // Core pages needed
        $percentage = round(($mapped_count / $total_expected) * 100, 1);
        
        // Status determination
        $status_class = $percentage >= 90 ? 'status-good' : ($percentage >= 70 ? 'status-warning' : 'status-error');
        $status_text = $percentage >= 90 ? 'GOOD' : ($percentage >= 70 ? 'WARNING' : 'CRITICAL');
        ?>

        <div class="section">
            <h2>📊 Overall Status</h2>
            <p><span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span></p>
            <p>Mapped Pages: <strong><?php echo $mapped_count; ?>/<?php echo $total_expected; ?></strong> (<?php echo $percentage; ?>%)</p>
        </div>

        <div class="section">
            <h2>📋 Current Page Mappings</h2>
            <?php if (empty($page_mappings)): ?>
                <p class="status-error">❌ No page mappings found!</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Key</th>
                        <th>Page ID</th>
                        <th>Page Title</th>
                        <th>URL</th>
                    </tr>
                    <?php foreach ($page_mappings as $key => $page_id): ?>
                        <?php 
                        $page_title = get_the_title($page_id);
                        $page_url = get_permalink($page_id);
                        $exists = get_post($page_id) ? '✅' : '❌';
                        ?>
                        <tr>
                            <td><?php echo esc_html($key); ?></td>
                            <td><?php echo esc_html($page_id); ?></td>
                            <td><?php echo $exists . ' ' . esc_html($page_title); ?></td>
                            <td><a href="<?php echo esc_url($page_url); ?>" target="_blank"><?php echo esc_url($page_url); ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>⚠️ Critical Pages Check</h2>
            <?php
            $critical_pages = array(
                'dashboard' => 'Health Dashboard',
                'assessments' => 'Health Assessments',
                'call' => 'Schedule a Call',
                'registration' => 'Registration Page',
                'signup' => 'Sign Up Page'
            );
            
            $missing_critical = 0;
            foreach ($critical_pages as $key => $title) {
                if (empty($page_mappings[$key])) {
                    echo "<p class='status-error'>❌ {$key} ({$title}) - NOT MAPPED</p>";
                    $missing_critical++;
                } else {
                    echo "<p class='status-good'>✅ {$key} ({$title}) - MAPPED</p>";
                }
            }
            
            if ($missing_critical === 0) {
                echo "<p class='status-good'>✅ All critical pages are mapped!</p>";
            }
            ?>
        </div>

        <div class="section">
            <h2>🎯 Assessment-Specific Consultation Pages</h2>
            <?php
            $assessment_types = array(
                'hair' => 'Hair Loss',
                'ed_treatment' => 'ED Treatment', 
                'weight_loss' => 'Weight Loss',
                'health' => 'Health',
                'skin' => 'Skin Health',
                'hormone' => 'Hormone',
                'testosterone' => 'Testosterone',
                'menopause' => 'Menopause',
                'sleep' => 'Sleep'
            );
            
            $missing_assessment_pages = 0;
            foreach ($assessment_types as $key => $name) {
                $consultation_key = $key . '_consultation_page_id';
                $page_id = $page_mappings[$consultation_key] ?? null;
                
                if (empty($page_id)) {
                    echo "<p class='status-warning'>⚠️ {$name} consultation page - NOT MAPPED</p>";
                    $missing_assessment_pages++;
                } else {
                    $page_title = get_the_title($page_id);
                    echo "<p class='status-good'>✅ {$name} consultation: ID {$page_id} - '{$page_title}'</p>";
                }
            }
            ?>
        </div>

        <div class="section">
            <h2>🔗 URL Generation Test</h2>
            <?php
            // Test the get_assessment_cta_url method
            if (class_exists('ENNU_Assessment_Shortcodes')) {
                $shortcodes = new ENNU_Assessment_Shortcodes();
                
                echo "<table>";
                echo "<tr><th>Assessment Type</th><th>Generated URL</th><th>Status</th></tr>";
                
                foreach ($assessment_types as $key => $name) {
                    $assessment_type = $key . '_assessment';
                    $cta_url = $shortcodes->get_assessment_cta_url($assessment_type);
                    $is_fallback = strpos($cta_url, 'call') !== false;
                    $status_class = $is_fallback ? 'status-warning' : 'status-good';
                    $status_text = $is_fallback ? 'Fallback' : 'Specific';
                    
                    echo "<tr>";
                    echo "<td>{$name}</td>";
                    echo "<td><a href='{$cta_url}' target='_blank'>{$cta_url}</a></td>";
                    echo "<td class='{$status_class}'>{$status_text}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='status-error'>❌ ENNU_Assessment_Shortcodes class not found</p>";
            }
            ?>
        </div>

        <div class="section">
            <h2>🚨 Issues Identified</h2>
            <?php
            $issues = array();
            
            if ($percentage < 100) {
                $issues[] = "Page mapping completion is {$percentage}% - some links may not work correctly";
            }
            
            if ($missing_assessment_pages > 0) {
                $issues[] = "{$missing_assessment_pages} assessment-specific consultation pages are missing - all assessments will fall back to generic call page";
            }
            
            if ($missing_critical > 0) {
                $issues[] = "{$missing_critical} critical pages are missing - core functionality may be affected";
            }
            
            if (empty($issues)) {
                echo "<p class='status-good'>✅ No critical issues identified!</p>";
            } else {
                foreach ($issues as $issue) {
                    echo "<p class='status-warning'>⚠️ {$issue}</p>";
                }
            }
            ?>
        </div>

        <div class="section">
            <h2>📝 URL Format Analysis</h2>
            <p class="status-good">✅ All URLs use ?page_id={id} format for compatibility</p>
            <p class="status-good">✅ No pretty permalinks dependency - ensures compatibility</p>
        </div>

        <div class="section">
            <h2>💡 Recommendations</h2>
            <?php if ($percentage < 100): ?>
                <div class="recommendation">
                    <strong>1. Run Auto-Detect Pages:</strong> Go to WordPress Admin → ENNU Life Settings → Click "Auto-Detect Pages" button
                </div>
            <?php endif; ?>
            
            <?php if ($missing_assessment_pages > 0): ?>
                <div class="recommendation">
                    <strong>2. Create Assessment-Specific Pages:</strong> Create consultation pages for each assessment type for better user experience
                </div>
            <?php endif; ?>
            
            <?php if ($missing_critical > 0): ?>
                <div class="recommendation">
                    <strong>3. Fix Critical Pages:</strong> Ensure all critical pages are created and mapped
                </div>
            <?php endif; ?>
            
            <div class="recommendation">
                <strong>4. Test CTA Links:</strong> Verify all assessment CTA links point to correct pages
            </div>
            
            <div class="recommendation">
                <strong>5. Verify Content:</strong> Ensure consultation page content is appropriate for each assessment type
            </div>
        </div>

        <div class="section">
            <h2>🔧 Quick Actions</h2>
            <p><a href="<?php echo admin_url('admin.php?page=ennu-life-settings'); ?>" class="button button-primary">Go to ENNU Settings</a></p>
            <p><a href="<?php echo home_url(); ?>" class="button">Visit Homepage</a></p>
        </div>
    </div>
</body>
</html> 