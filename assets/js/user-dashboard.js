/**
 * ENNU Life User Dashboard JavaScript
 * This file controls all the interactivity for the "Bio-Metric Canvas" dashboard.
 */
(function ($) {
	'use strict';

	class ENNUDashboard {
		constructor(dashboardElement) {
			this.dashboard = $(dashboardElement);
			this.init();
		}

		init() {
			this.initDetailsToggle();
			this.initPillarHovers();
			this.initScoreAnimation();
			this.initPillarAnimation();
			this.initHistoricalCharts();
			this.initHealthMapAccordion();
			this.initToggleAll();
		}

		initToggleAll() {
			const toggleBtn = this.dashboard.find('#toggle-all-accordions');
			const accordionItems = this.dashboard.find('.accordion-item');
			if (toggleBtn.length === 0) return;

			toggleBtn.on('click', () => {
				const isAllOpen = accordionItems.filter('.open').length === accordionItems.length;

				accordionItems.each(function () {
					const item = $(this);
					const content = item.find('.accordion-content');
					if (isAllOpen) {
						item.removeClass('open');
						content.slideUp();
					} else {
						item.addClass('open');
						content.slideDown();
					}
				});
			});
		}

		initHealthMapAccordion() {
			const accordion = this.dashboard.find('.health-map-accordion');
			if (accordion.length === 0) return;

			accordion.on('click', '.accordion-header', function () {
				const item = $(this).closest('.accordion-item');
				const content = item.find('.accordion-content');
				item.toggleClass('open');
				content.slideToggle();
			});
		}

		initDetailsToggle() {
			// Use event delegation to handle dynamically loaded content
			this.dashboard.on('click', '.assessment-list-item .details-toggle-icon', (e) => {
				e.preventDefault();
				e.stopPropagation();
				
				const $button = $(e.currentTarget);
				const $item = $button.closest('.assessment-list-item');
				const $detailsContainer = $item.find('.category-details-container');
				
				// Toggle expanded state
				const isExpanded = $item.attr('aria-expanded') === 'true';
				$item.attr('aria-expanded', !isExpanded);
				
				// Toggle visibility with animation
				if (isExpanded) {
					$detailsContainer.slideUp(400);
					$button.removeClass('active');
				} else {
					$detailsContainer.slideDown(400);
					$button.addClass('active');
				}
			});
		}

		initPillarHovers() {
			const contextDisplay = this.dashboard.find('.pillar-context-display');
			this.dashboard.on('mouseenter', '.pillar-orb', function () {
				const insight = $(this).data('insight');
				if (insight) {
					contextDisplay.text(insight).stop().fadeIn(200);
				}
			});
			this.dashboard.on('mouseleave', '.pillar-orb', function () {
				contextDisplay.stop().fadeOut(200);
			});
		}

		initScoreAnimation() {
			const mainScoreOrb = this.dashboard.find('.main-score-orb');
			const mainScoreInsight = this.dashboard.find('.main-score-insight');
			
			if (mainScoreOrb.length > 0) {
				const score = parseFloat(mainScoreOrb.data('score')) || 0;
				const scoreValueElement = mainScoreOrb.find('.main-score-value');
				const progressBar = mainScoreOrb.find('.pillar-orb-progress-bar');

				scoreValueElement.text('0.0');
				progressBar.css('--score-percent', 0);

				setTimeout(() => {
					mainScoreOrb.addClass('loaded');
					// Also fade in the insight text
					mainScoreInsight.addClass('visible');
					
					$({ Counter: 0 }).animate({ Counter: score }, {
						duration: 1500,
						easing: 'swing',
						step: function () {
							scoreValueElement.text(this.Counter.toFixed(1));
						},
						complete: function () {
							scoreValueElement.text(score.toFixed(1));
						}
					});
				}, 500);
			}
		}

		initPillarAnimation() {
			const pillarOrbs = this.dashboard.find('.pillar-orb');
			if (pillarOrbs.length > 0) {
				// Add visible class with staggered delay for smooth animation
				pillarOrbs.each(function(index) {
					const $orb = $(this);
					setTimeout(() => {
						$orb.addClass('visible');
						// Also add loaded class for progress bar animation
						setTimeout(() => {
							$orb.addClass('loaded');
						}, 300);
					}, 700 + (index * 150)); // Stagger by 150ms after main score
				});
			}
		}

		initHistoricalCharts() {
			const scoreCtx = document.getElementById('ennuLifeScoreTimelineChart');
			if (scoreCtx && typeof dashboardData !== 'undefined' && dashboardData.score_history) {
				new Chart(scoreCtx, {
			type: 'line',
			data: {
				datasets: [{
							label: 'ENNU LIFE SCORE',
							data: dashboardData.score_history.map(item => ({ x: new Date(item.date), y: item.score })),
							borderColor: 'rgba(52, 211, 153, 1)',
							backgroundColor: 'rgba(52, 211, 153, 0.1)',
							fill: true,
					tension: 0.4,
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				scales: {
							x: { type: 'time', time: { unit: 'day' }, grid: { color: 'rgba(255, 255, 255, 0.1)' }, ticks: { color: 'rgba(255, 255, 255, 0.7)' } },
							y: { beginAtZero: true, max: 10, grid: { color: 'rgba(255, 255, 255, 0.1)' }, ticks: { color: 'rgba(255, 255, 255, 0.7)' } }
						},
						plugins: { legend: { display: false } }
					}
				});
			}

			const bmiCtx = document.getElementById('bmiHistoryChart');
			if (bmiCtx && typeof dashboardData !== 'undefined' && dashboardData.bmi_history) {
				new Chart(bmiCtx, {
					type: 'line',
					data: {
						datasets: [{
							label: 'BMI',
							data: dashboardData.bmi_history.map(item => ({ x: new Date(item.date), y: item.bmi })),
							borderColor: 'rgba(96, 165, 250, 1)',
							backgroundColor: 'rgba(96, 165, 250, 0.1)',
							fill: true,
							tension: 0.4,
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						scales: {
							x: { type: 'time', time: { unit: 'day' }, grid: { color: 'rgba(255, 255, 255, 0.1)' }, ticks: { color: 'rgba(255, 255, 255, 0.7)' } },
							y: { grid: { color: 'rgba(255, 255, 255, 0.1)' }, ticks: { color: 'rgba(255, 255, 255, 0.7)' } }
						},
						plugins: { legend: { display: false } }
					}
				});
			}
		}
	}

	function initializeDashboard() {
		const dashboardEl = $('.ennu-user-dashboard');
		if (dashboardEl.length > 0) {
			new ENNUDashboard(dashboardEl);
		}
	}

	$(document).ready(initializeDashboard);

})(jQuery); 