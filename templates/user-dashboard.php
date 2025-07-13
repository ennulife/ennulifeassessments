<?php
/**
 * Template for the user assessment dashboard.
 *
 * This template is loaded by the [ennu-user-dashboard] shortcode.
 * It displays a grid of cards, one for each assessment type, showing
 * the user's score, completion date, and an interactive mini-chart.
 */

if (!defined('ABSPATH')) {
    exit;
}

if ( !is_user_logged_in() ) {
    return '<p>' . esc_html__( 'You must be logged in to view your dashboard.', 'ennulifeassessments' ) . '</p>';
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$assessments = [
    'hair_assessment' => ['label' => 'Hair', 'icon' => '🦱', 'color' => '#667eea', 'url' => home_url('/hair-assessment/')],
    'ed_treatment_assessment' => ['label' => 'ED Treatment', 'icon' => '❤️‍🩹', 'color' => '#f093fb', 'url' => home_url('/ed-treatment-assessment/')],
    'weight_loss_assessment' => ['label' => 'Weight Loss', 'icon' => '⚖️', 'color' => '#4facfe', 'url' => home_url('/weight-loss-assessment/')],
    'health_assessment' => ['label' => 'Health', 'icon' => '❤️', 'color' => '#fa709a', 'url' => home_url('/health-assessment/')],
    'skin_assessment' => ['label' => 'Skin', 'icon' => '✨', 'color' => '#a8edea', 'url' => home_url('/skin-assessment/')],
];

$all_scores = [];
$total_score = 0;
$completed_count = 0;

foreach ($assessments as $key => &$assessment) {
    $score = get_user_meta($user_id, 'ennu_' . $key . '_calculated_score', true);
    if ($score) {
        $assessment['score'] = (float) $score;
        $assessment['date'] = get_user_meta($user_id, 'ennu_' . $key . '_score_calculated_at', true);
        $assessment['categories'] = get_user_meta($user_id, 'ennu_' . $key . '_category_scores', true);
        $all_scores[] = $assessment['score'];
        $completed_count++;
    } else {
        $assessment['score'] = false;
    }
}
unset($assessment); // Unset reference

$average_score = $completed_count > 0 ? array_sum($all_scores) / $completed_count : 0;
?>

<div class="ennu-user-dashboard">
    <div class="dashboard-header">
        <h2 aria-label="Welcome message for <?php echo esc_attr($current_user->display_name); ?>">Welcome back, <?php echo esc_html($current_user->display_name); ?>!</h2>
        <p>Your Health Journey Overview</p>
    </div>

    <div class="progress-overview">
        <div class="progress-bar" style="width: <?php echo esc_attr( ( $completed_count / count( $assessments ) ) * 100 ); ?>%;" role="progressbar" aria-valuenow="<?php echo esc_attr( $completed_count ); ?>" aria-valuemin="0" aria-valuemax="<?php echo esc_attr( count( $assessments ) ); ?>"></div>
        <p class="progress-text"><?php echo esc_html( $completed_count ); ?> of <?php echo esc_html( count( $assessments ) ); ?> assessments completed.</p>
    </div>

    <div class="assessment-cards-container" role="list">
        <?php foreach ( $user_assessments as $key => $data ): ?>
            <div class="assessment-card card-<?php echo esc_attr($key); ?>" role="listitem">
                <div class="card-header">
                    <span class="card-icon" aria-hidden="true"><?php echo esc_html($data['icon']); ?></span>
                    <h3><?php echo esc_html($data['label']); ?> Assessment</h3>
                </div>
                <div class="card-body">
                    <?php if ($data['completed']): ?>
                        <div class="score-display">
                            <p class="score-text">Your Score:</p>
                            <span class="score-value"><?php echo esc_html( number_format( $data['score'], 1 ) ); ?></span> / 10
                        </div>
                        <p class="completion-date">Completed on: <?php echo esc_html(date('F j, Y', strtotime($data['date']))); ?></p>
                        <div class="card-actions">
                            <button class="expand-button" aria-expanded="false" aria-controls="details-<?php echo esc_attr($key); ?>">View Details</button>
                            <a href="<?php echo esc_url($data['url']); ?>" class="retake-button">Retake</a>
                            <a href="<?php echo esc_url( home_url( '/results/' . $key . '/' ) ); ?>" class="report-button">View Full Report</a>
                        </div>
                        <div id="details-<?php echo esc_attr($key); ?>" class="expandable-content" style="display: none;">
                            <canvas class="mini-chart" id="mini-chart-<?php echo esc_attr($key); ?>" data-scores="<?php echo esc_attr(json_encode(array_values($data['categories']))); ?>" data-labels="<?php echo esc_attr(json_encode(array_keys($data['categories']))); ?>" aria-label="Chart for <?php echo esc_attr($data['label']); ?> assessment"></canvas>
                        </div>
                    <?php else: ?>
                        <p class="not-completed">You haven't completed this assessment yet.</p>
                        <div class="card-actions">
                            <a href="<?php echo esc_url($data['url']); ?>" class="button-primary">Start Now</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="dashboard-summary">
        <?php if ($average_score > 0): ?>
            <?php if ($average_score >= 8): ?>
                <p><?php echo esc_html__( 'Great job! Your average score of', 'ennulifeassessments' ); ?> <?php echo esc_html( number_format( $average_score, 1 ) ); ?> <?php echo esc_html__( 'is excellent. Keep up the great work!', 'ennulifeassessments' ); ?></p>
            <?php else: ?>
                <p><?php echo esc_html__( 'Your average score is', 'ennulifeassessments' ); ?> <?php echo esc_html( number_format( $average_score, 1 ) ); ?>. <?php echo esc_html__( 'Retake assessments to track your improvements!', 'ennulifeassessments' ); ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded.');
        return;
    }

    // Handle expandable sections
    document.querySelectorAll('.expand-button').forEach(button => {
        button.addEventListener('click', function() {
            const content = this.parentElement.nextElementSibling;
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !isExpanded);
            content.style.display = isExpanded ? 'none' : 'block';
            this.textContent = isExpanded ? 'View Details' : 'Hide Details';

            // Initialize chart only when it becomes visible
            if (!isExpanded) {
                const canvas = content.querySelector('.mini-chart');
                if (canvas && !canvas.chart) {
                    const spinner = content.querySelector('.loading-spinner');
                    spinner.style.display = 'block';
                    
                    setTimeout(() => { // Simulate loading
                        const labels = JSON.parse(canvas.dataset.labels);
                        const scores = JSON.parse(canvas.dataset.scores);
                        
                        canvas.chart = new Chart(canvas.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Score',
                                    data: scores,
                                    backgroundColor: 'rgba(79, 172, 254, 0.6)',
                                    borderColor: 'rgba(79, 172, 254, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                indexAxis: 'y',
                                scales: { x: { min: 0, max: 10 } },
                                plugins: { 
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: (context) => `${context.label}: ${context.raw}`
                                        }
                                    }
                                },
                                animation: {
                                    onComplete: () => {
                                        spinner.style.display = 'none';
                                    }
                                }
                            }
                        });
                    }, 300);
                }
            }
        });
    });
});
</script> 