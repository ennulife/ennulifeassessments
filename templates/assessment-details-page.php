<?php
/**
 * Template for the "Health Dossier" - a hyper-personalized, stunning results page.
 *
 * @version 24.0.0
 */
if (!defined('ABSPATH')) { exit; }

// --- 1. Data Fetching & Processing ---
if ( !is_user_logged_in() ) {
    echo '<p>' . esc_html__( 'You must be logged in to view your dossier.', 'ennulifeassessments' ) . '</p>';
    return;
}
$user_id = get_current_user_id();

$assessment_type_slug = get_query_var('assessment_type', 'hair');
$assessment_type = $assessment_type_slug . '_assessment';

// Fetch latest data
$score = get_user_meta($user_id, 'ennu_' . $assessment_type . '_calculated_score', true);
if (!$score) {
    echo '<div class="ennu-results-empty-state"><h2>' . esc_html__( 'Assessment Not Completed', 'ennulifeassessments' ) . '</h2><p>' . esc_html__( 'You have not yet completed this assessment.', 'ennulifeassessments' ) . '</p><a href="' . esc_url( home_url( '/' . str_replace( '_', '-', $assessment_type ) . '/' ) ) . '">' . esc_html__( 'Take the Assessment', 'ennulifeassessments' ) . '</a></div>';
    return;
}

$interpretation = get_user_meta($user_id, 'ennu_' . $assessment_type . '_score_interpretation', true);
$category_scores = get_user_meta($user_id, 'ennu_' . $assessment_type . '_category_scores', true);
$historical_scores = get_user_meta($user_id, 'ennu_' . $assessment_type . '_historical_scores', true);
if (!is_array($historical_scores)) { $historical_scores = []; }

// --- 2. AI Insight Narrative Generation ---
$current_user = wp_get_current_user();
$insight_narrative = "Hello " . esc_html($current_user->display_name) . ", your results for the " . esc_html(ucwords(str_replace('_', ' ', $assessment_type))) . " paint a clear picture. Your overall score of " . esc_html(number_format($score, 1)) . " indicates a status of '" . esc_html($interpretation['level'] ?? 'Fair') . "'.";
if (is_array($category_scores) && !empty($category_scores)) {
    arsort($category_scores);
    $strengths = array_slice($category_scores, 0, 1);
    asort($category_scores);
    $focus_areas = array_slice($category_scores, 0, 1);
    $insight_narrative .= " Your key strength appears to be in " . esc_html(key($strengths)) . ", while the primary area for focus is " . esc_html(key($focus_areas)) . ".";
}

// --- 3. Health Trinity Calculation ---
$pillar_map = ENNU_Assessment_Scoring::get_health_pillar_map();
$pillar_scores = ['mind' => [], 'body' => [], 'lifestyle' => [], 'aesthetics' => []];
foreach($category_scores as $cat => $cat_score) {
    // This logic should now be driven by the 'health_pillar' key from the question config,
    // which is mapped to categories in the scoring config.
    // The existing pillar map function is sufficient, but this ensures consistency if we ever read the pillar directly.
}
$trinity_avg = [
    'mind' => !empty($pillar_scores['mind']) ? array_sum($pillar_scores['mind']) / count($pillar_scores['mind']) : 0,
    'body' => !empty($pillar_scores['body']) ? array_sum($pillar_scores['body']) / count($pillar_scores['body']) : 0,
    'lifestyle' => !empty($pillar_scores['lifestyle']) ? array_sum($pillar_scores['lifestyle']) / count($pillar_scores['lifestyle']) : 0,
    'aesthetics' => !empty($pillar_scores['aesthetics']) ? array_sum($pillar_scores['aesthetics']) / count($pillar_scores['aesthetics']) : 0,
];

$pillar_colors = ['mind' => '#8e44ad', 'body' => '#2980b9', 'lifestyle' => '#27ae60', 'aesthetics' => '#f39c12'];

// --- 4. Deep-Dive Content Build ---
$deep_dive_content = [];
foreach ($category_scores as $category => $cat_score) {
    $deep_dive_content[$category] = [
        'explanation'  => 'This category reflects your status in ' . esc_html($category) . '. Maintaining a high score here is important for optimal hair health.',
        'user_answer'  => '',
        'action_plan'  => [
            'Review our educational resources on ' . esc_html(strtolower($category)) . '.',
            'Schedule a consultation to address this area.'
        ]
    ];
}

// Hair-specific narrative tweak
if ($assessment_type === 'hair_assessment') {
    $strength_key = key($strengths);
    $focus_key = key($focus_areas);
    $insight_narrative  = "Based on your responses, your overall hair health score is " . esc_html(number_format($score,1)) . ". ";
    $insight_narrative .= "Your strongest factor is <strong>" . esc_html($strength_key) . "</strong>, while <strong>" . esc_html($focus_key) . "</strong> needs the most attention.";
}

// --- 5. Enqueue Assets ---
wp_enqueue_style('ennu-health-dossier', ENNU_LIFE_PLUGIN_URL . 'assets/css/health-dossier.css', [], ENNU_LIFE_VERSION);
wp_enqueue_script('ennu-health-dossier', ENNU_LIFE_PLUGIN_URL . 'assets/js/health-dossier.js', ['chartjs'], ENNU_LIFE_VERSION, true);

?>
<div class="health-dossier-container">
    <div class="dossier-header">
        <h1>Your <?php echo esc_html( ucwords( str_replace( '_', ' ', $assessment_type_slug ) ) ); ?> Health Dossier</h1>
        <p class="ai-narrative"><?php echo wp_kses_post($insight_narrative); ?></p>
    </div>

    <div class="trinity-pillars">
        <?php foreach ($trinity_avg as $pillar => $pillar_score): ?>
            <div class="pillar-item pillar-<?php echo esc_attr( strtolower( $pillar ) ); ?>">
                <div class="pillar-circle" data-score="<?php echo esc_attr( $pillar_score ); ?>" style="--pillar-color: <?php echo esc_attr( $pillar_colors[$pillar] ); ?>;">
                    <span><?php echo esc_html( number_format( $pillar_score, 1 ) ); ?></span>
                </div>
                <h4><?php echo esc_html( ucfirst( $pillar ) ); ?></h4>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="journey-timeline-card">
        <h3>Your Journey So Far</h3>
        <div class="timeline-chart-container">
            <canvas id="journey-timeline-chart"></canvas>
        </div>
    </div>

    <div class="deep-dive-grid">
        <?php foreach($category_scores as $category => $cat_score): ?>
            <div class="category-card">
                <h4><?php echo esc_html( $category ); ?></h4>
                <div class="category-score"><?php echo esc_html( number_format( $cat_score, 1 ) ); ?> / 10</div>
                <div class="category-bar">
                    <div class="bar-fill" style="width: <?php echo esc_attr( $cat_score * 10 ); ?>%;"></div>
                </div>
                <button class="deep-dive-button">Deep Dive</button>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const dossierData = {
        historicalScores: <?php echo wp_json_encode( $historical_scores ); ?>,
        trinityScores: <?php echo wp_json_encode( $trinity_avg ); ?>,
        deepDiveContent: <?php echo wp_json_encode( $deep_dive_content ); ?>
    };

    // Render Historical Journey Chart
    // ... existing code ...

});
</script> 