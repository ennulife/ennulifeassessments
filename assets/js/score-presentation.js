/**
 * Score Presentation JavaScript
 *
 * Handles animations, interactions, and AJAX functionality for score presentations
 *
 * @package ENNU_Life
 * @copyright Copyright (c) 2024, Very Good Plugins, https://verygoodplugins.com
 * @license GPL-3.0+
 * @since 62.2.8
 */

(function($) {
    'use strict';

    class ENNUScorePresentation {
        constructor() {
            this.init();
        }

        init() {
            this.bindEvents();
            this.initializeScorePresentations();
        }

        bindEvents() {
            // Refresh score button
            $(document).on('click', '.ennu-btn-refresh-score', this.handleRefreshScore.bind(this));
            
            // Retake assessment button
            $(document).on('click', '.ennu-btn-retake-assessment', this.handleRetakeAssessment.bind(this));
            
            // Pillar item hover effects
            $(document).on('mouseenter', '.ennu-pillar-item', this.handlePillarHover.bind(this));
            $(document).on('mouseleave', '.ennu-pillar-item', this.handlePillarLeave.bind(this));
        }

        initializeScorePresentations() {
            $('.ennu-score-presentation').each((index, element) => {
                this.initializeScorePresentation($(element));
            });
        }

        initializeScorePresentation($element) {
            const type = $element.data('type');
            const score = parseFloat($element.data('score')) || 0;
            const animation = $element.data('animation') === 'true';

            // Animate score orb on load
            if (animation) {
                this.animateScoreOrb($element, score);
            }

            // Initialize tooltips for pillar scores
            this.initializePillarTooltips($element);

            // Initialize chart if history is present
            if ($element.find('.ennu-history-chart canvas').length) {
                this.initializeHistoryChart($element);
            }
        }

        animateScoreOrb($element, score) {
            const $orb = $element.find('.ennu-score-orb-progress');
            const $pillars = $element.find('.ennu-pillar-progress');
            
            // Add animation class
            $element.addClass('ennu-animated');

            // Animate main score orb
            setTimeout(() => {
                $orb.css('stroke-dashoffset', `calc(100 - ${score * 10})`);
            }, 100);

            // Animate pillar orbs with delay
            $pillars.each((index, pillar) => {
                const $pillar = $(pillar);
                const pillarScore = parseFloat($pillar.closest('.ennu-pillar-item').data('pillar-score')) || 0;
                
                setTimeout(() => {
                    $pillar.css('stroke-dashoffset', `calc(62.83 - (62.83 * ${pillarScore * 10} / 100))`);
                }, 500 + (index * 100));
            });
        }

        initializePillarTooltips($element) {
            $element.find('.ennu-pillar-item').each((index, item) => {
                const $item = $(item);
                const pillar = $item.data('pillar');
                const score = parseFloat($item.find('.ennu-pillar-score').text()) || 0;
                
                // Add tooltip data
                $item.attr('title', `${pillar.charAt(0).toUpperCase() + pillar.slice(1)}: ${score.toFixed(1)}/10`);
            });
        }

        initializeHistoryChart($element) {
            const $canvas = $element.find('.ennu-history-chart canvas');
            if (!$canvas.length || typeof Chart === 'undefined') {
                return;
            }

            const chartId = $canvas.attr('id');
            const ctx = document.getElementById(chartId);
            
            if (!ctx) {
                return;
            }

            // Get chart data from the template
            const chartData = window.ennuChartData && window.ennuChartData[chartId];
            if (!chartData) {
                return;
            }

            const labels = chartData.labels || [];
            const scores = chartData.scores || [];
            const color = chartData.color || '#667eea';

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: chartData.title || 'Score',
                        data: scores,
                        borderColor: color,
                        backgroundColor: this.hexToRgba(color, 0.1),
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: color,
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 10,
                            ticks: {
                                stepSize: 2,
                                color: '#6b7280',
                                font: {
                                    size: 12
                                }
                            },
                            grid: {
                                color: 'rgba(107, 114, 128, 0.1)',
                                drawBorder: false
                            }
                        },
                        x: {
                            ticks: {
                                color: '#6b7280',
                                font: {
                                    size: 12
                                }
                            },
                            grid: {
                                color: 'rgba(107, 114, 128, 0.1)',
                                drawBorder: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: color,
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return `Score: ${context.parsed.y.toFixed(1)}/10`;
                                }
                            }
                        }
                    }
                }
            });
        }

        handleRefreshScore(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const type = $button.data('type');
            const $presentation = $button.closest('.ennu-score-presentation');
            
            // Show loading state
            $button.prop('disabled', true).html(`
                <svg class="ennu-btn-icon ennu-spinning" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
                Refreshing...
            `);

            // Make AJAX request
            $.ajax({
                url: ennuScorePresentation.ajax_url,
                type: 'POST',
                data: {
                    action: 'ennu_get_score_data',
                    type: type,
                    nonce: ennuScorePresentation.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.updateScorePresentation($presentation, response.data);
                        this.showNotification('Score refreshed successfully!', 'success');
                    } else {
                        this.showNotification('Failed to refresh score. Please try again.', 'error');
                    }
                },
                error: () => {
                    this.showNotification('Failed to refresh score. Please try again.', 'error');
                },
                complete: () => {
                    // Restore button state
                    $button.prop('disabled', false).html(`
                        <svg viewBox="0 0 24 24" class="ennu-btn-icon">
                            <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
                        </svg>
                        Refresh Score
                    `);
                }
            });
        }

        handleRetakeAssessment(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const assessmentType = $button.data('type');
            
            // Redirect to assessment page
            const assessmentUrl = this.getAssessmentUrl(assessmentType);
            if (assessmentUrl) {
                window.location.href = assessmentUrl;
            } else {
                this.showNotification('Assessment page not found.', 'error');
            }
        }

        handlePillarHover(e) {
            const $item = $(e.currentTarget);
            $item.addClass('ennu-pillar-hover');
        }

        handlePillarLeave(e) {
            const $item = $(e.currentTarget);
            $item.removeClass('ennu-pillar-hover');
        }

        updateScorePresentation($presentation, data) {
            // Update main score
            const $scoreValue = $presentation.find('.ennu-score-value');
            const $scoreOrb = $presentation.find('.ennu-score-orb-progress');
            
            $scoreValue.text(parseFloat(data.score).toFixed(1));
            $presentation.data('score', data.score);
            $scoreOrb.css('stroke-dashoffset', `calc(100 - ${data.score * 10})`);

            // Update interpretation
            if (data.interpretation) {
                const $level = $presentation.find('.ennu-score-level');
                const $description = $presentation.find('.ennu-score-description');
                
                $level.removeClass().addClass(`ennu-score-level ennu-score-level-${data.interpretation.class}`);
                $level.text(data.interpretation.level);
                
                if ($description.length && data.interpretation.description) {
                    $description.text(data.interpretation.description);
                }
            }

            // Update pillar scores
            if (data.pillar_scores) {
                Object.keys(data.pillar_scores).forEach(pillar => {
                    const score = data.pillar_scores[pillar];
                    const $pillarItem = $presentation.find(`[data-pillar="${pillar}"]`);
                    
                    if ($pillarItem.length) {
                        const $pillarScore = $pillarItem.find('.ennu-pillar-score');
                        const $pillarProgress = $pillarItem.find('.ennu-pillar-progress');
                        
                        $pillarScore.text(parseFloat(score).toFixed(1));
                        $pillarProgress.css('stroke-dashoffset', `calc(62.83 - (62.83 * ${score * 10} / 100))`);
                    }
                });
            }

            // Update last updated timestamp
            if (data.last_updated) {
                const $lastUpdated = $presentation.find('.ennu-score-last-updated small');
                if ($lastUpdated.length) {
                    const date = new Date(data.last_updated);
                    $lastUpdated.text(`Last updated: ${date.toLocaleDateString()}`);
                }
            }
        }

        getAssessmentUrl(assessmentType) {
            // Map assessment types to URLs
            const assessmentUrls = {
                'weight_loss_assessment': '/weight-loss-assessment/',
                'hair_assessment': '/hair-assessment/',
                'skin_assessment': '/skin-assessment/',
                'ed_treatment_assessment': '/ed-treatment-assessment/',
                'health_assessment': '/health-assessment/',
                'hormone_assessment': '/hormone-assessment/',
                'sleep_assessment': '/sleep-assessment/',
                'menopause_assessment': '/menopause-assessment/',
                'testosterone_assessment': '/testosterone-assessment/',
                'health_optimization_assessment': '/health-optimization-assessment/'
            };

            return assessmentUrls[assessmentType] || null;
        }

        showNotification(message, type = 'info') {
            // Create notification element
            const $notification = $(`
                <div class="ennu-notification ennu-notification-${type}">
                    <div class="ennu-notification-content">
                        <span class="ennu-notification-message">${message}</span>
                        <button class="ennu-notification-close">&times;</button>
                    </div>
                </div>
            `);

            // Add to page
            $('body').append($notification);

            // Show notification
            setTimeout(() => {
                $notification.addClass('ennu-notification-show');
            }, 100);

            // Auto hide after 5 seconds
            setTimeout(() => {
                this.hideNotification($notification);
            }, 5000);

            // Close button handler
            $notification.find('.ennu-notification-close').on('click', () => {
                this.hideNotification($notification);
            });
        }

        hideNotification($notification) {
            $notification.removeClass('ennu-notification-show');
            setTimeout(() => {
                $notification.remove();
            }, 300);
        }

        hexToRgba(hex, alpha) {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }
    }

    // Initialize when DOM is ready
    $(document).ready(() => {
        new ENNUScorePresentation();
    });

    // Add notification styles
    const notificationStyles = `
        <style>
            .ennu-notification {
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                border-radius: 8px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                padding: 15px 20px;
                z-index: 9999;
                transform: translateX(100%);
                transition: transform 0.3s ease;
                max-width: 300px;
            }
            
            .ennu-notification-show {
                transform: translateX(0);
            }
            
            .ennu-notification-content {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
            }
            
            .ennu-notification-message {
                flex: 1;
                font-size: 14px;
                color: #374151;
            }
            
            .ennu-notification-close {
                background: none;
                border: none;
                font-size: 18px;
                color: #9ca3af;
                cursor: pointer;
                padding: 0;
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .ennu-notification-close:hover {
                color: #6b7280;
            }
            
            .ennu-notification-success {
                border-left: 4px solid #10b981;
            }
            
            .ennu-notification-error {
                border-left: 4px solid #ef4444;
            }
            
            .ennu-notification-info {
                border-left: 4px solid #3b82f6;
            }
            
            .ennu-spinning {
                animation: spin 1s linear infinite;
            }
            
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            
            .ennu-pillar-hover {
                transform: scale(1.05);
            }
        </style>
    `;
    
    $('head').append(notificationStyles);

})(jQuery); 