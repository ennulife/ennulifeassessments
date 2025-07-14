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

// --- PHASE 2 OPTIMIZATION: Fetch pre-calculated pillar scores ---
$pillar_scores = get_user_meta($user_id, 'ennu_' . $assessment_type . '_pillar_scores', true);
if (empty($pillar_scores) || !is_array($pillar_scores)) {
    // Fallback for old assessments that don't have saved pillar scores.
    // This can be removed after a data migration is performed.
    $pillar_map = ENNU_Assessment_Scoring::get_health_pillar_map();
    $pillar_totals = [];
    $pillar_counts = [];
    foreach ($pillar_map as $pillar_name => $categories) {
        $pillar_totals[$pillar_name] = 0;
        $pillar_counts[$pillar_name] = 0;
    }
    if (is_array($category_scores)) {
        foreach ($category_scores as $category => $score) {
            foreach ($pillar_map as $pillar_name => $categories) {
                if (in_array($category, $categories)) {
                    $pillar_totals[$pillar_name] += $score;
                    $pillar_counts[$pillar_name]++;
                    break;
                }
            }
        }
    }
    $pillar_scores = [];
    foreach ($pillar_totals as $pillar_name => $total) {
        $pillar_scores[$pillar_name] = ($pillar_counts[$pillar_name] > 0) ? round($total / $pillar_counts[$pillar_name], 1) : 0;
    }
}
// --- END PHASE 2 OPTIMIZATION ---

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
        <?php foreach ($pillar_scores as $pillar => $pillar_score): ?>
            <div class="pillar-item pillar-<?php echo esc_attr( strtolower( $pillar ) ); ?>">
                <div class="pillar-circle" data-score="<?php echo esc_attr( $pillar_score ); ?>" style="--pillar-color: <?php echo esc_attr( $pillar_colors[$pillar] ?? '#7f8c8d' ); ?>;">
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
        trinityScores: <?php echo wp_json_encode( array_values($pillar_scores) ); ?>,
        deepDiveContent: <?php echo wp_json_encode( $deep_dive_content ); ?>
    };

    // Render Historical Journey Chart
    // ... existing code ...

});
</script> 