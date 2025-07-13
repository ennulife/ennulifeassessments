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
if (!$results_data) {
    echo '<div class="ennu-chart-container"><p>No assessment results found. Please complete an assessment first.</p></div>';
    return;
}

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
    // Ensure Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded. Cannot render chart.');
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