<?php
/**
 * Template for the user assessment dashboard - "The Executive Wellness Interface" (Definitive & Final)
 */
if (!defined('ABSPATH')) { exit; }

// Data passed from rendering function:
// $current_user, $ennu_life_score, $average_pillar_scores, $age, $gender, $dob, 
// $user_assessments, $score_history, $height, $weight, $bmi, $insights
?>

<div class="ennu-user-dashboard">
    <header class="dashboard-header">
        <div class="header-welcome">
            <h1>Your Wellness Report</h1>
            <p>A comprehensive overview of your health journey.</p>
        </div>
        <div class="user-profile-summary">
            <div class="profile-name"><?php echo esc_html($current_user->first_name . ' ' . $current_user->last_name); ?></div>
            <div class="profile-vitals">
                <?php if ($age): ?><span><strong>Age:</strong> <?php echo esc_html($age); ?></span><?php endif; ?>
                <?php if ($gender): ?><span><strong>Gender:</strong> <?php echo esc_html($gender); ?></span><?php endif; ?>
                <?php if ($height): ?><span><strong>Height:</strong> <?php echo esc_html($height); ?></span><?php endif; ?>
                <?php if ($weight): ?><span><strong>Weight:</strong> <?php echo esc_html($weight); ?></span><?php endif; ?>
                <?php if ($bmi): ?><span><strong>BMI:</strong> <?php echo esc_html($bmi); ?></span><?php endif; ?>
            </div>
        </div>
    </header>
    
    <div class="dashboard-main-grid">
        <aside class="dashboard-sidebar">
            <div class="main-score-card">
                <h2 class="section-header">Your ENNU Life Score</h2>
                <div class="main-score-radial" data-score="<?php echo esc_attr($ennu_life_score ?? 0); ?>">
                    <svg viewBox="0 0 120 120">
                        <defs>
                            <linearGradient id="score-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#6d28d9" />
                                <stop offset="100%" stop-color="#4338ca" />
                            </linearGradient>
                        </defs>
                        <circle class="radial-progress-bg" cx="60" cy="60" r="54"></circle>
                        <circle class="main-score-progress-bar" cx="60" cy="60" r="54"></circle>
                    </svg>
                    <div class="main-score-text">
                        <div class="main-score-value">0.0</div>
                    </div>
                </div>
                <p class="main-score-insight"><?php echo esc_html($insights['ennu_life_score'] ?? ''); ?></p>
            </div>

            <div class="pillar-scores-card">
                <h2 class="section-header">Health Pillars</h2>
                <div class="pillar-scores-grid">
                     <?php foreach($average_pillar_scores as $pillar => $score): ?>
                        <div class="pillar-item">
                            <div class="pillar-info">
                                <div class="pillar-label-group">
                                    <span class="pillar-label"><?php echo esc_html($pillar); ?></span>
                                    <span class="tooltip-icon">ⓘ<span class="tooltip-text"><?php echo esc_html($insights['pillars'][$pillar] ?? ''); ?></span></span>
                                </div>
                                <span class="pillar-score-value"><?php echo esc_html(number_format($score, 1)); ?></span>
                            </div>
                            <div class="category-bar-bg">
                                <div class="pillar-bar-fill" style="--score: <?php echo esc_attr($score); ?>"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>

        <main class="dashboard-main-content">
            <div class="progress-timeline-card">
                <h2 class="section-header">Your Progress Over Time</h2>
                <?php if (count($score_history) > 1): ?>
                    <canvas id="progress-line-chart"></canvas>
                <?php else: ?>
                    <p>Complete another assessment to begin tracking your progress.</p>
                <?php endif; ?>
            </div>
            <div class="assessment-card-list">
                <h2 class="section-header">Your Assessments</h2>
                <?php foreach ($user_assessments as $key => $data): ?>
                    <div class="assessment-list-item" aria-expanded="false">
                        <div class="assessment-summary">
                            <div class="assessment-icon"><?php echo esc_html($data['icon']); ?></div>
                            <div class="assessment-info">
                                <h4><?php echo esc_html($data['label']); ?> Assessment</h4>
                                <p><?php echo $data['completed'] ? 'Completed on: ' . esc_html(date('F j, Y', strtotime($data['date']))) : 'Not yet completed.'; ?></p>
                            </div>
                            <div class="assessment-actions">
                                <?php if ($data['completed']): ?>
                                    <span class="assessment-score-badge"><?php echo esc_html(number_format($data['score'], 1)); ?></span>
                                    <button class="details-toggle-icon">▼</button>
                                <?php else: ?>
                                    <a href="<?php echo esc_url($data['url']); ?>" class="action-button button-start">Start Now</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($data['completed']): ?>
                            <div class="category-details-container">
                                <div class="category-details-inner">
                                    <ul class="category-score-list">
                                        <?php foreach($data['categories'] as $category => $score): ?>
                                        <li class="category-score-item">
                                            <div class="category-info">
                                                <span class="category-name-group">
                                                    <span class="category-name"><?php echo esc_html($category); ?></span>
                                                    <span class="tooltip-icon">ⓘ<span class="tooltip-text"><?php echo esc_html($insights['categories'][$category] ?? $insights['categories']['Default'] ?? ''); ?></span></span>
                                                </span>
                                                <span class="category-score-value"><?php echo esc_html(number_format($score, 1)); ?>/10</span>
                                            </div>
                                            <div class="category-bar-bg">
                                                <div class="category-bar-fill" style="--score: <?php echo esc_attr($score); ?>"></div>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                     <div class="assessment-actions-footer">
                                        <a href="<?php echo esc_url($data['url']); ?>" class="action-button button-retake">Retake Assessment</a>
                                        <a href="<?php echo esc_url($data['details_url']); ?>" class="action-button button-report">View Full Report</a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function animateValue(obj, start, end, duration) {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            obj.innerHTML = (progress * (end - start)).toFixed(1);
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    const mainRadial = document.querySelector('.main-score-radial');
    if (mainRadial) {
        const score = parseFloat(mainRadial.dataset.score) || 0;
        const progressBar = mainRadial.querySelector('.main-score-progress-bar');
        const scoreValueText = mainRadial.querySelector('.main-score-value');
        const circumference = 2 * Math.PI * 54;
        const offset = circumference - (score / 10) * circumference;
        
        setTimeout(() => {
            progressBar.style.strokeDashoffset = offset;
            animateValue(scoreValueText, 0, score, 1500);
        }, 100);
    }

    // Progress line chart
    const progressChartCanvas = document.getElementById('progress-line-chart');
    if (progressChartCanvas) {
        const historyData = <?php echo json_encode($score_history ?? []); ?>;
        // Definitive Fix: Format data for time series axis
        const chartData = historyData.map(item => ({
            x: new Date(item.date),
            y: item.score
        }));

        new Chart(progressChartCanvas, {
            type: 'line',
            data: {
                datasets: [{
                    label: 'ENNU Life Score',
                    data: chartData,
                    borderColor: 'var(--accent-primary)',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: 'var(--accent-primary)',
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: 'var(--accent-primary)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { 
                        beginAtZero: true, 
                        max: 10, 
                        ticks: { color: 'var(--text-light)', stepSize: 2 } 
                    },
                    x: {
                        type: 'time', // Use time series axis
                        time: {
                            unit: 'day',
                            tooltipFormat: 'MMM d, yyyy h:mm a'
                        },
                        ticks: { color: 'var(--text-light)' }
                    }
                },
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' Score: ' + context.parsed.y;
                            }
                        }
                    }
                }
            }
        });
    }

    document.querySelectorAll('.assessment-summary').forEach(summary => {
        summary.addEventListener('click', function() {
            const parentItem = this.closest('.assessment-list-item');
            const details = parentItem.querySelector('.category-details-container');
            if (!details) return;

            const isExpanded = parentItem.getAttribute('aria-expanded') === 'true';
            parentItem.setAttribute('aria-expanded', !isExpanded);

            if (!isExpanded) {
                details.style.maxHeight = details.scrollHeight + 'px';
            } else {
                details.style.maxHeight = null;
            }
        });
    });
});
</script> 