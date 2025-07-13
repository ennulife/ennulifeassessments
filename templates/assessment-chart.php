<?php
/**
 * Template for displaying the assessment radar chart.
 *
 * This template is loaded by the [ennu-assessment-chart] shortcode.
 */

if (!defined('ABSPATH')) {
    exit;
}

// 1. Get Results Data from Transient
$user_id = get_current_user_id();
if (!$user_id) {
    echo '<div class="ennu-chart-container"><p>You must be logged in to view results.</p></div>';
    return;
}

$results_data = get_transient('ennu_assessment_results_' . $user_id);
if (is_user_logged_in()) {
    // Always try meta first for logged-in users
    $assessment_type = 'hair_assessment'; // TODO: Make dynamic (e.g., get latest from meta)
    $score = get_user_meta($user_id, 'ennu_' . $assessment_type . '_calculated_score', true);
    $category_scores = get_user_meta($user_id, 'ennu_' . $assessment_type . '_category_scores', true);
    if ($score && $category_scores) {
        $results_data = ['title' => 'Your ' . ucfirst(str_replace('_', ' ', $assessment_type)), 'category_scores' => $category_scores];
    } elseif (!$results_data) {
        // Fallback to transient if meta empty
    }
}
if (!$results_data) {
    echo '<div class="ennu-chart-container"><p>No assessment results found. Please complete an assessment first.</p></div>';
    return;
}

// After getting $results_data
delete_transient( 'ennu_assessment_results_' . $user_id );

// Extract data for the chart
$assessment_title = $results_data['title'] ?? 'Your Assessment';
$category_scores = $results_data['category_scores'] ?? [];
$chart_labels = array_keys($category_scores);
$chart_data = array_values($category_scores);

?>

<div class="ennu-chart-container">
    <h2><?php echo esc_html($assessment_title); ?> - Score Breakdown</h2>
    <p>This chart provides a visual representation of your scores across the different health categories.</p>
    <div style="max-width: 600px; margin: 0 auto;">
        <canvas id="ennuRadarChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded. Cannot render chart.');
        document.querySelector('.ennu-chart-container').innerHTML += '<p>Error loading chart. Please refresh or contact support.</p>';
        return;
    }

    const ctx = document.getElementById('ennuRadarChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'Your Scores',
                data: <?php echo json_encode($chart_data); ?>,
                backgroundColor: 'rgba(79, 172, 254, 0.2)',
                borderColor: 'rgba(79, 172, 254, 1)',
                borderWidth: 2,
                pointBackgroundColor: 'rgba(79, 172, 254, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(79, 172, 254, 1)'
            }]
        },
        options: {
            scales: {
                r: {
                    angleLines: {
                        display: true
                    },
                    suggestedMin: 0,
                    suggestedMax: 10,
                    ticks: {
                        stepSize: 2
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.raw}`;
                        }
                    }
                }
            }
        }
    });
});
</script> 