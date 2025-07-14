<?php
/**
 * Template for displaying assessment results.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Get Results Data
$user_id = get_current_user_id();
if ( ! $user_id ) {
    echo '<div class="ennu-results-container"><p>You must be logged in to view results.</p></div>';
    return;
}

$results_transient = get_transient( 'ennu_assessment_results_' . $user_id );
if ( ! $results_transient ) {
    echo '<div class="ennu-results-container"><p>No assessment results found. Please complete an assessment first.</p></div>';
    return;
}

delete_transient( 'ennu_assessment_results_' . $user_id );

$assessment_type = $results_transient['type'];
$score = $results_transient['score'];
$interpretation_data = $results_transient['interpretation'];
$interpretation = isset($interpretation_data['level']) ? strtolower($interpretation_data['level']) : 'fair'; // Lowercase for key matching
$category_scores = $results_transient['category_scores'];
$user_answers = $results_transient['answers'] ?? [];

// 2. Get Personalised Content
$content_config_file = plugin_dir_path( __FILE__ ) . '../includes/config/results-content.php';
$content_config = file_exists($content_config_file) ? require $content_config_file : array();
$content = $content_config[$assessment_type] ?? $content_config['default'];
$result_content = $content['score_ranges'][$interpretation] ?? $content['score_ranges']['fair']; // Fallback to 'fair'
$conditional_recs = $content['conditional_recommendations'] ?? [];

// 3. Find matching conditional recommendations
$matched_recs = [];
if (!empty($conditional_recs) && !empty($user_answers)) {
    foreach ($conditional_recs as $question_key => $answer_recs) {
        if (isset($user_answers[$question_key]) && isset($answer_recs[$user_answers[$question_key]])) {
            $matched_recs[] = $answer_recs[$user_answers[$question_key]];
        }
    }
}

?>

<div class="ennu-results-page">
    <div class="ennu-results-main-panel">
        <h1 class="ennu-results-title"><?php echo esc_html($content['title']); ?></h1>
        <div class="ennu-score-card">
            <h2 class="ennu-score-card-title"><?php echo esc_html($result_content['title']); ?></h2>
            <div class="ennu-overall-score-display">
                <div class="ennu-score-value"><?php echo number_format($score, 1); ?></div>
                <div class="ennu-score-max">/ 10</div>
            </div>
            <p class="ennu-score-summary"><?php echo esc_html($result_content['summary']); ?></p>
        </div>
        
        <div class="ennu-recommendations-card">
            <h3>Your Personalized Recommendations</h3>
            <ul>
                <?php foreach ($result_content['recommendations'] as $rec) : ?>
                    <li><?php echo esc_html($rec); ?></li>
                <?php endforeach; ?>
            </ul>
            <a href="#" class="ennu-cta-button"><?php echo esc_html($result_content['cta']); ?></a>
        </div>

        <?php if (!empty($matched_recs)) : ?>
        <div class="ennu-conditional-recs-card">
            <h3>Specific Observations</h3>
            <ul>
                <?php foreach ($matched_recs as $rec) : ?>
                    <li><?php echo esc_html($rec); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="ennu-results-sidebar">
        <div class="ennu-category-scores-card">
            <h3>Score Breakdown</h3>
            <?php if (!empty($category_scores)) : ?>
                <ul class="ennu-category-list">
                    <?php foreach ($category_scores as $category => $cat_score) : ?>
                        <li>
                            <span class="ennu-category-label"><?php echo esc_html($category); ?></span>
                            <div class="ennu-category-bar-bg">
                                <div class="ennu-category-bar-fill" style="width: <?php echo (float)$cat_score * 10; ?>%;"></div>
                            </div>
                            <span class="ennu-category-score"><?php echo number_format($cat_score, 1); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Category score details are not available.</p>
            <?php endif; ?>
        </div>

        <!-- Integrated Radar Chart -->
        <div class="ennu-chart-container">
            <h3>Visual Score Breakdown</h3>
            <canvas id="ennuRadarChart"></canvas>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded.');
                return;
            }
            const ctx = document.getElementById('ennuRadarChart').getContext('2d');
            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: <?php echo json_encode(array_keys($category_scores)); ?>,
                    datasets: [{
                        label: 'Your Scores',
                        data: <?php echo json_encode(array_values($category_scores)); ?>,
                        backgroundColor: 'rgba(79, 172, 254, 0.2)',
                        borderColor: 'rgba(79, 172, 254, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    scales: { r: { suggestedMin: 0, suggestedMax: 10 } },
                    plugins: { legend: { display: false } }
                }
            });
        });
        </script>
    </div>
</div>

<style>
/* Main Layout */
.ennu-results-page { display: flex; max-width: 1100px; margin: 40px auto; gap: 30px; font-family: sans-serif; }
.ennu-results-main-panel { flex: 2; }
.ennu-results-sidebar { flex: 1; }

/* General Card Styling */
.ennu-score-card, .ennu-recommendations-card, .ennu-category-scores-card {
    background: #fff;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.08);
    margin-bottom: 30px;
}

/* New Conditional Recs Card */
.ennu-conditional-recs-card {
    background: #fff9e6;
    border: 1px solid #ffde7a;
    border-left: 5px solid #ffc107;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
}
.ennu-conditional-recs-card h3 { font-size: 20px; color: #856404; margin-bottom: 20px; }
.ennu-conditional-recs-card ul { list-style: none; padding: 0; margin: 0; }
.ennu-conditional-recs-card li { background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path fill="%23ffc107" d="M10 20C4.477 20 0 15.523 0 10S4.477 0 10 0s10 4.477 10 10-4.477 10-10 10zm-1-5v-2h2v2h-2zm0-4V5h2v6h-2z"/></svg>') no-repeat left center; padding-left: 30px; font-size: 16px; color: #856404; margin-bottom: 15px; }


/* Main Panel Styles */
.ennu-results-title { font-size: 32px; font-weight: 700; color: #1a202c; margin-bottom: 20px; }
.ennu-score-card-title { font-size: 24px; color: #2d3748; margin-bottom: 15px; }
.ennu-overall-score-display { display: flex; align-items: baseline; justify-content: center; margin-bottom: 15px; }
.ennu-score-value { font-size: 72px; font-weight: 800; color: #2d3748; line-height: 1; }
.ennu-score-max { font-size: 24px; font-weight: 600; color: #a0aec0; margin-left: 8px; }
.ennu-score-summary { font-size: 16px; color: #4a5568; line-height: 1.6; }

/* Recommendations Card */
.ennu-recommendations-card h3 { font-size: 20px; color: #2d3748; margin-bottom: 20px; }
.ennu-recommendations-card ul { list-style: none; padding: 0; margin: 0 0 25px 0; }
.ennu-recommendations-card li { background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path fill="%2338a169" d="M10 0C4.5 0 0 4.5 0 10s4.5 10 10 10 10-4.5 10-10S15.5 0 10 0zm4.2 8.4l-5.4 5.4c-.4.4-1 .4-1.4 0L4.6 11c-.4-.4-.4-1 0-1.4s1-.4 1.4 0l2.8 2.8 4.6-4.6c.4-.4 1-.4 1.4 0s.4 1 0 1.4z"/></svg>') no-repeat left center; padding-left: 30px; font-size: 16px; color: #4a5568; margin-bottom: 15px; }
.ennu-cta-button { display: inline-block; background-color: #2d3748; color: #fff; padding: 12px 25px; border-radius: 8px; text-decoration: none; font-weight: 600; transition: background-color .2s; }
.ennu-cta-button:hover { background-color: #1a202c; }

/* Sidebar Styles */
.ennu-category-scores-card h3 { font-size: 20px; color: #2d3748; margin-bottom: 20px; }
.ennu-category-list { list-style: none; padding: 0; margin: 0; }
.ennu-category-list li { display: flex; align-items: center; margin-bottom: 15px; font-size: 14px; }
.ennu-category-label { flex: 1; color: #4a5568; font-weight: 500; }
.ennu-category-bar-bg { flex: 2; height: 10px; background: #e2e8f0; border-radius: 5px; margin: 0 10px; }
.ennu-category-bar-fill { height: 100%; background: #4299e1; border-radius: 5px; }
.ennu-category-score { font-weight: 600; color: #2d3748; }
</style> 