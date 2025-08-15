<?php
/**
 * ENNU User Dashboard - Main Template
 * Modular, clean architecture replacing the 4,530-line monolithic template
 * 
 * @package ENNU_Life_Assessments
 * @version 64.55.1
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get dashboard data service
$dashboard_service = new ENNU_Dashboard_Data_Service();
$dashboard_data = $dashboard_service->get_dashboard_data(get_current_user_id());

// Extract data for template use
$user_info = $dashboard_data['user_info'];
$scores = $dashboard_data['scores'];
$assessments = $dashboard_data['assessments'];
$biomarkers = $dashboard_data['biomarkers'];
$symptoms = $dashboard_data['symptoms'];
$goals = $dashboard_data['goals'];
$history = $dashboard_data['history'];
$recommendations = $dashboard_data['recommendations'];
$profile_completeness = $dashboard_data['profile_completeness'];

// Get helper class
$helpers = 'ENNU_Dashboard_Helpers';
?>

<div class="ennu-user-dashboard" data-theme="light">
    
    <!-- Dashboard Header -->
    <header class="dashboard-header">
        <div class="dashboard-header-content">
            <div class="user-greeting">
                <h1 id="dashboard-greeting" data-user-name="<?php echo esc_attr($user_info['first_name'] ?: $user_info['display_name']); ?>">
                    <!-- Greeting populated by JavaScript -->
                </h1>
                <p class="dashboard-subtitle">Your personalized health optimization dashboard</p>
            </div>
            
            <div class="header-actions">
                <button class="btn-dashboard-action" onclick="toggleFeedbackForm()">
                    <span class="action-icon">💬</span>
                    <span class="action-text">Feedback</span>
                </button>
                <button class="btn-dashboard-action" id="theme-toggle">
                    <span class="action-icon">🌓</span>
                    <span class="action-text">Theme</span>
                </button>
            </div>
        </div>
    </header>
    
    <!-- User Stats Bar -->
    <div class="user-stats-bar">
        <div class="stat-item">
            <span class="stat-label">Age</span>
            <span class="stat-value"><?php echo esc_html($user_info['age'] ?: 'N/A'); ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Gender</span>
            <span class="stat-value"><?php echo esc_html(ucfirst($user_info['gender'] ?: 'N/A')); ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Height</span>
            <span class="stat-value"><?php echo esc_html($user_info['height'] ?: 'N/A'); ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Weight</span>
            <span class="stat-value"><?php echo esc_html($user_info['weight'] ?: 'N/A'); ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-label">BMI</span>
            <span class="stat-value"><?php echo esc_html($user_info['bmi'] ? number_format($user_info['bmi'], 1) : 'N/A'); ?></span>
        </div>
    </div>
    
    <!-- Main Score Display - Modern UI -->
    <?php 
    // Prepare data for modern scoring section
    $ennu_life_score = $scores['ennu_life_score'] ?? 0;
    $average_pillar_scores = $scores['pillar_scores'] ?? array();
    $dashboard_data = $dashboard_data ?? array();
    
    // Calculate global target score that will be used across all templates
    if ($ennu_life_score == 0) {
        $global_target_score = 3.0;
    } elseif ($ennu_life_score < 3.0) {
        $global_target_score = min(5.0, $ennu_life_score + 2.0);
    } elseif ($ennu_life_score < 6.0) {
        $global_target_score = min(7.5, $ennu_life_score + 1.5);
    } elseif ($ennu_life_score < 8.0) {
        $global_target_score = min(9.0, $ennu_life_score + 1.0);
    } else {
        $global_target_score = min(10.0, $ennu_life_score + 0.5);
    }
    
    // Debug - check if file exists
    $template_path = ENNU_LIFE_PLUGIN_PATH . 'templates/modern-scoring-section.php';
    if (file_exists($template_path)) {
        // Include modern scoring section template
        include $template_path;
    } else {
        echo '<!-- Modern scoring template not found at: ' . esc_html($template_path) . ' -->';
        // Fallback to show basic scores
        ?>
        <div class="score-display-section">
            <h2>Health Scores</h2>
            <p>EnnuLife Score: <?php echo esc_html(number_format($ennu_life_score, 1)); ?></p>
            <?php foreach ($average_pillar_scores as $pillar => $score): ?>
                <p><?php echo esc_html($pillar); ?>: <?php echo esc_html(number_format($score, 1)); ?></p>
            <?php endforeach; ?>
        </div>
        <?php
    }
    ?>
    
    <!-- Profile Completeness Widget -->
    <?php if (class_exists('ENNU_Enhanced_Dashboard_Manager')): ?>
        <div class="profile-completeness-section">
            <?php
            $dashboard_manager = new ENNU_Enhanced_Dashboard_Manager();
            echo $dashboard_manager->get_profile_completeness_display(get_current_user_id());
            ?>
        </div>
    <?php endif; ?>
    
    <!-- Main Content Tabs -->
    <div class="dashboard-content">
        <div class="my-story-tabs">
            <nav class="my-story-tab-nav" role="tablist">
                <ul>
                    <li><a href="#tab-my-assessments" class="my-story-tab-link active" role="tab" data-tab="my-assessments">📋 Assessments</a></li>
                    <li><a href="#tab-my-biomarkers" class="my-story-tab-link" role="tab" data-tab="my-biomarkers">🔬 Biomarkers</a></li>
                    <li><a href="#tab-my-symptoms" class="my-story-tab-link" role="tab" data-tab="my-symptoms">🩺 Symptoms</a></li>
                    <li><a href="#tab-my-insights" class="my-story-tab-link" role="tab" data-tab="my-insights">💡 Insights</a></li>
                    <li><a href="#tab-my-story" class="my-story-tab-link" role="tab" data-tab="my-story">📊 My Story</a></li>
                    <li><a href="#tab-upload-pdf" class="my-story-tab-link" role="tab" data-tab="upload-pdf">📄 Upload Labs</a></li>
                </ul>
            </nav>
            
            <!-- Tab Contents -->
            <div class="my-story-tab-contents">
                
                <!-- Assessments Tab -->
                <div id="tab-my-assessments" class="my-story-tab-content active" role="tabpanel">
                    <h2>My Health Assessments</h2>
                    <div class="assessments-grid">
                        <?php foreach ($assessments as $assessment): ?>
                            <div class="assessment-card <?php echo $assessment['completed'] ? 'completed' : 'pending'; ?>">
                                <div class="assessment-header">
                                    <span class="assessment-icon">
                                        <?php echo $assessment['completed'] ? '✅' : '⏳'; ?>
                                    </span>
                                    <h3><?php echo esc_html($assessment['display_name']); ?></h3>
                                </div>
                                <div class="assessment-body">
                                    <?php if ($assessment['completed']): ?>
                                        <div class="assessment-score">
                                            Score: <?php echo esc_html(number_format($assessment['score'], 1)); ?>/10
                                        </div>
                                        <div class="assessment-date">
                                            Completed: <?php echo esc_html($helpers::format_dashboard_date($assessment['completion_date'])); ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="assessment-pending">Not yet completed</p>
                                        <a href="#" class="btn-take-assessment">Take Assessment</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Biomarkers Tab -->
                <div id="tab-my-biomarkers" class="my-story-tab-content" role="tabpanel">
                    <h2>My Biomarkers</h2>
                    <div class="biomarkers-actions">
                        <button class="btn-action" onclick="uploadLabResults()">📤 Upload Lab Results</button>
                        <button class="btn-action" onclick="scheduleLabTest()">📅 Schedule Lab Test</button>
                        <button class="btn-action" onclick="viewBiomarkerGuide()">📖 Biomarker Guide</button>
                    </div>
                    
                    <div class="biomarkers-panels">
                        <?php foreach ($biomarkers as $category => $category_data): ?>
                            <?php if (!empty($category_data['biomarkers'])): ?>
                                <div class="biomarker-panel">
                                    <div class="panel-header" onclick="togglePanel('<?php echo esc_attr(strtolower($category)); ?>')">
                                        <h3><?php echo esc_html($category); ?></h3>
                                        <span id="expand-icon-<?php echo esc_attr(strtolower($category)); ?>" class="expand-icon">▼</span>
                                    </div>
                                    <div id="panel-<?php echo esc_attr(strtolower($category)); ?>" class="panel-content">
                                        <?php foreach ($category_data['biomarkers'] as $biomarker): ?>
                                            <div class="biomarker-item">
                                                <span class="biomarker-name"><?php echo esc_html($biomarker['name']); ?></span>
                                                <span class="biomarker-value status-<?php echo esc_attr($biomarker['status']); ?>">
                                                    <?php echo esc_html($biomarker['value'] . ' ' . $biomarker['unit']); ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                        <?php if (empty($biomarkers) || count(array_filter($biomarkers, function($cat) { return !empty($cat['biomarkers']); })) === 0): ?>
                            <div class="empty-state">
                                <p>No biomarker data available yet.</p>
                                <button class="btn-primary" onclick="uploadLabResults()">Upload Your Lab Results</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Symptoms Tab -->
                <div id="tab-my-symptoms" class="my-story-tab-content" role="tabpanel">
                    <h2>My Symptoms Tracking</h2>
                    <div class="symptoms-summary">
                        <div class="summary-stat">
                            <span class="stat-number" id="total-symptoms-count"><?php echo count($symptoms); ?></span>
                            <span class="stat-label">Total Symptoms</span>
                        </div>
                        <div class="summary-stat">
                            <span class="stat-number" id="active-symptoms-count">
                                <?php echo count(array_filter($symptoms, function($s) { return $s['severity'] > 5; })); ?>
                            </span>
                            <span class="stat-label">Active Symptoms</span>
                        </div>
                    </div>
                    
                    <div class="symptoms-list">
                        <?php foreach ($symptoms as $symptom): ?>
                            <div class="symptom-item <?php echo $symptom['severity'] > 5 ? 'active' : ''; ?>">
                                <div class="symptom-header">
                                    <span class="symptom-name"><?php echo esc_html($symptom['name']); ?></span>
                                    <span class="symptom-severity severity-<?php echo esc_attr($symptom['severity']); ?>">
                                        Severity: <?php echo esc_html($symptom['severity']); ?>/10
                                    </span>
                                </div>
                                <div class="symptom-details">
                                    <span class="symptom-frequency">Frequency: <?php echo esc_html($symptom['frequency']); ?></span>
                                    <?php if ($symptom['notes']): ?>
                                        <p class="symptom-notes"><?php echo esc_html($symptom['notes']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($symptoms)): ?>
                            <div class="empty-state">
                                <p>No symptoms tracked yet.</p>
                                <button class="btn-primary">Add Symptoms</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Insights Tab -->
                <div id="tab-my-insights" class="my-story-tab-content" role="tabpanel">
                    <h2>Personalized Health Insights</h2>
                    <div class="insights-container">
                        <?php if (!empty($recommendations)): ?>
                            <div class="recommendations-section">
                                <h3>Recommended Actions</h3>
                                <?php foreach ($recommendations as $rec): ?>
                                    <div class="recommendation-card priority-<?php echo esc_attr($rec['priority']); ?>">
                                        <div class="rec-icon"><?php echo esc_html($rec['icon']); ?></div>
                                        <div class="rec-content">
                                            <h4><?php echo esc_html($rec['title']); ?></h4>
                                            <p><?php echo esc_html($rec['description']); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="goals-section">
                            <h3>Your Health Goals</h3>
                            <div class="goals-grid">
                                <?php foreach ($goals as $goal): ?>
                                    <div class="goal-badge">
                                        <span class="goal-icon"><?php echo esc_html($goal['icon']); ?></span>
                                        <span class="goal-name"><?php echo esc_html($goal['display_name']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- My Story Tab -->
                <div id="tab-my-story" class="my-story-tab-content" role="tabpanel">
                    <h2>My Health Journey</h2>
                    <div class="charts-container">
                        <div class="chart-wrapper">
                            <h3>ENNU Score History</h3>
                            <canvas id="scoreHistoryChart"></canvas>
                        </div>
                        <div class="chart-wrapper">
                            <h3>BMI History</h3>
                            <canvas id="bmiHistoryChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="history-table">
                        <h3>Assessment History</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Assessment</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history['assessment_history'] as $entry): ?>
                                    <tr>
                                        <td><?php echo esc_html($entry['assessment_type']); ?></td>
                                        <td><?php echo esc_html($entry['score']); ?>/10</td>
                                        <td><?php echo esc_html($helpers::format_dashboard_date($entry['completed_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Upload PDF Tab -->
                <div id="tab-upload-pdf" class="my-story-tab-content" role="tabpanel">
                    <h2>Upload Lab Results</h2>
                    <div class="upload-container">
                        <div class="upload-instructions">
                            <p>Upload your lab results PDF to automatically extract and track your biomarkers.</p>
                            <ul>
                                <li>✅ Supports most lab report formats</li>
                                <li>✅ Automatic biomarker extraction</li>
                                <li>✅ Secure and HIPAA compliant</li>
                                <li>✅ Instant analysis and insights</li>
                            </ul>
                        </div>
                        
                        <div class="upload-form">
                            <input type="file" id="pdf-file-input" accept="application/pdf" />
                            <button class="btn-primary" onclick="uploadPDF()">Upload PDF</button>
                            <progress id="upload-progress" value="0" max="100" style="display:none;"></progress>
                            <div id="upload-status"></div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- Feedback Form Modal -->
    <div id="feedback-overlay" class="modal-overlay" style="display:none;" onclick="toggleFeedbackForm()"></div>
    <div id="dashboard-feedback-form" class="feedback-modal" style="display:none;">
        <div class="modal-header">
            <h3>Share Your Feedback</h3>
            <button class="modal-close" onclick="toggleFeedbackForm()">×</button>
        </div>
        <div class="modal-body">
            <textarea id="feedback-text" placeholder="Tell us about your experience..."></textarea>
            <div class="rating-section">
                <label>Rate your experience:</label>
                <div class="rating-stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <input type="radio" name="feedback-rating" value="<?php echo $i; ?>" id="star-<?php echo $i; ?>">
                        <label for="star-<?php echo $i; ?>">⭐</label>
                    <?php endfor; ?>
                </div>
            </div>
            <button class="btn-primary" onclick="submitFeedback()">Submit Feedback</button>
        </div>
    </div>
    
</div>

<script>
// Store dashboard data for JavaScript use
window.dashboardData = {
    scoreHistory: <?php echo json_encode($history['score_history']); ?>,
    bmiHistory: <?php echo json_encode($history['bmi_history']); ?>
};
</script>