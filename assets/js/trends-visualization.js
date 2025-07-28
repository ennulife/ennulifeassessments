/**
 * ENNU Life Trends Visualization System
 *
 * Handles fetching and rendering all charts on the "My Health Trends" tab
 * of the user dashboard.
 *
 * @package ENNU_Life
 * @version 64.6.0
 */

jQuery(document).ready(function($) {
    let ennuCharts = {}; // Object to hold all our chart instances

    function initializeTrendsVisualization(userId) {
        if ($('#lifeScoreChart').length === 0) {
            // We are not on the trends tab, do nothing.
            return;
        }

        console.log('Initializing Trends Visualization for user:', userId);

        // Initial data load
        fetchTrendData(userId, 90, 'all');

        // Setup event listeners for filters
        $('#trendsTimeRange, #trendsCategory').on('change', function() {
            const timeRange = $('#trendsTimeRange').val();
            const category = $('#trendsCategory').val();
            fetchTrendData(userId, timeRange, category);
        });

        $('#assessmentSelect').on('change', function() {
             const selectedAssessment = $(this).val();
             // This requires a more complex update, re-fetching might be simplest
             fetchTrendData(userId, $('#trendsTimeRange').val(), 'all');
        });

         $('#biomarkerSelect').on('change', function() {
             const selectedBiomarker = $(this).val();
             fetchAndRenderSingleChart(userId, 'biomarkerChart', 'ennu_get_biomarker_trends', { biomarker: selectedBiomarker });
        });
    }

    function fetchTrendData(userId, timeRange, category) {
        console.log(`Fetching data for range: ${timeRange} days, category: ${category}`);
        $.ajax({
            url: ennuTrends.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ennu_get_trend_data',
                nonce: ennuTrends.nonce,
                user_id: userId,
                time_range: timeRange,
                category: category
            },
            success: function(response) {
                if (response.success) {
                    console.log('Data received:', response.data);
                    renderAllCharts(response.data);
                    renderInsights(response.data.insights);
                } else {
                    console.error('Error fetching trend data:', response.data);
                }
            },
            error: function(error) {
                console.error('AJAX error:', error);
            }
        });
    }

    function renderAllCharts(data) {
        // Destroy existing charts before rendering new ones
        for (let chartId in ennuCharts) {
            if (ennuCharts[chartId]) {
                ennuCharts[chartId].destroy();
            }
        }
        
        if (data.life_score) {
            renderChart('lifeScoreChart', 'line', data.life_score.labels, [{
                label: 'Life Score',
                data: data.life_score.data,
                borderColor: '#007bff',
                tension: 0.1
            }]);
            updateTrendIndicator('lifeScoreTrend', data.life_score);
        }

        if (data.pillar_scores) {
            renderChart('pillarScoresChart', 'line', data.pillar_scores.labels, data.pillar_scores.datasets);
        }

        if (data.assessment_scores) {
            const selectedAssessment = $('#assessmentSelect').val();
            if (selectedAssessment === 'all') {
                 // Potentially render a summary or default view
            } else if (data.assessment_scores[selectedAssessment]) {
                 renderChart('assessmentScoresChart', 'line', data.assessment_scores[selectedAssessment].labels, [{
                    label: $('#assessmentSelect option:selected').text(),
                    data: data.assessment_scores[selectedAssessment].data,
                    borderColor: '#28a745',
                    tension: 0.1
                }]);
            }
        }
        
        if (data.biomarkers) {
            const selectedBiomarker = $('#biomarkerSelect').val();
            if (data.biomarkers[selectedBiomarker]) {
                renderChart('biomarkerChart', 'line', data.biomarkers[selectedBiomarker].labels, [{
                    label: $('#biomarkerSelect option:selected').text(),
                    data: data.biomarkers[selectedBiomarker].data,
                    borderColor: '#ffc107',
                    tension: 0.1
                }]);
            }
        }

        if (data.goal_progress) {
            renderChart('goalProgressChart', 'line', data.goal_progress.labels, data.goal_progress.datasets);
        }

        if (data.symptom_trends) {
            renderChart('symptomChart', 'bar', data.symptom_trends.labels, [{
                label: 'Symptom Score (Higher is Better)',
                data: data.symptom_trends.data,
                backgroundColor: '#17a2b8'
            }]);
            updateTrendIndicator('symptomTrend', data.symptom_trends, true);
        }
    }

    function renderChart(canvasId, type, labels, datasets) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        ennuCharts[canvasId] = new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function updateTrendIndicator(elementId, trendData, isSymptom = false) {
        const indicator = $(`#${elementId}`);
        if (indicator.length > 0) {
            let changeHtml = '';
            if (trendData.change !== 0) {
                const changeClass = trendData.change > 0 ? 'up' : 'down';
                const changeIcon = trendData.change > 0 ? '▲' : '▼';
                changeHtml = `<span class="trend-direction ${changeClass}">${changeIcon} ${Math.abs(trendData.change)}%</span>`;
            } else {
                 changeHtml = `<span class="trend-direction stable">--</span>`;
            }
            indicator.find('.trend-value').html(`${trendData.current_value.toFixed(1)} ${changeHtml}`);

            if(isSymptom) {
                 indicator.find('.trend-value').html(trendData.change > 0 ? 'Improving' : 'Worsening');
            }
        }
    }
    
    function renderInsights(insights) {
        const container = $('#trendsInsights');
        container.empty();
        if (insights && insights.length > 0) {
            insights.forEach(insight => {
                const insightCard = `
                    <div class="insight-card ${insight.type}">
                        <div class="insight-icon">${insight.icon}</div>
                        <div class="insight-content">
                            <h4>${insight.title}</h4>
                            <p>${insight.description}</p>
                        </div>
                    </div>
                `;
                container.append(insightCard);
            });
        } else {
            container.html('<p>No insights available for this period. Keep tracking your data!</p>');
        }
    }


    // Make the initialization function globally accessible
    window.initializeTrendsVisualization = initializeTrendsVisualization;
}); 