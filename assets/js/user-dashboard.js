/**
 * ENNU Life User Dashboard JavaScript
 * This file controls all the interactivity for the "Bio-Metric Canvas" dashboard.
 */
document.addEventListener('DOMContentLoaded', () => {
    const dashboardEl = document.querySelector('.ennu-user-dashboard');
    if (dashboardEl) {
        new ENNUDashboard(dashboardEl);
    }
});

	class ENNUDashboard {
		constructor(dashboardElement) {
        this.dashboard = dashboardElement;
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
        const toggleBtn = this.dashboard.querySelector('#toggle-all-accordions');
        if (!toggleBtn) return;

        const accordionItems = this.dashboard.querySelectorAll('.accordion-item');

        toggleBtn.addEventListener('click', () => {
            const isAllOpen = this.dashboard.querySelectorAll('.accordion-item.open').length === accordionItems.length;

            accordionItems.forEach(item => {
                const content = item.querySelector('.accordion-content');
					if (isAllOpen) {
                    item.classList.remove('open');
                    content.style.maxHeight = null;
					} else {
                    item.classList.add('open');
                    content.style.maxHeight = content.scrollHeight + "px";
					}
				});
			});
		}

		initHealthMapAccordion() {
        const accordion = this.dashboard.querySelector('.health-map-accordion');
        if (!accordion) return;

        accordion.addEventListener('click', (event) => {
            const header = event.target.closest('.accordion-header');
            if (!header) return;

            const item = header.closest('.accordion-item');
            const content = item.querySelector('.accordion-content');
            
            item.classList.toggle('open');
            
            if (item.classList.contains('open')) {
                // For a smooth transition, we set max-height from 0 to its scrollHeight
                content.style.display = 'block'; // Make it visible to calculate scrollHeight
                const contentHeight = content.scrollHeight + 'px';
                content.style.display = '';
                requestAnimationFrame(() => {
                    content.style.maxHeight = contentHeight;
                });
            } else {
                content.style.maxHeight = '0px';
            }
			});
		}

		initDetailsToggle() {
        this.dashboard.addEventListener('click', (e) => {
            const toggleButton = e.target.closest('.assessment-list-item .details-toggle-icon');
            if (!toggleButton) return;
            
				e.preventDefault();
				e.stopPropagation();
				
            const item = toggleButton.closest('.assessment-list-item');
            const detailsContainer = item.querySelector('.category-details-container');
				
            const isExpanded = item.getAttribute('aria-expanded') === 'true';
            item.setAttribute('aria-expanded', !isExpanded);
            toggleButton.classList.toggle('active');
            
            if (!isExpanded) {
                detailsContainer.style.display = 'block';
                const contentHeight = detailsContainer.scrollHeight + 'px';
                detailsContainer.style.display = '';
                requestAnimationFrame(() => {
                    detailsContainer.style.maxHeight = contentHeight;
                });
				} else {
                detailsContainer.style.maxHeight = '0px';
				}
			});
		}

		initPillarHovers() {
        const contextDisplay = this.dashboard.querySelector('.pillar-context-display');
        if (!contextDisplay) return;

        this.dashboard.addEventListener('mouseenter', (event) => {
            const pillarOrb = event.target.closest('.pillar-orb');
            if (!pillarOrb) return;

            const insight = pillarOrb.dataset.insight;
				if (insight) {
                contextDisplay.textContent = insight;
                contextDisplay.style.opacity = '1';
                contextDisplay.style.display = 'block';
				}
        }, true);

        this.dashboard.addEventListener('mouseleave', (event) => {
            const pillarOrb = event.target.closest('.pillar-orb');
            if (!pillarOrb) return;

            contextDisplay.style.opacity = '0';
            setTimeout(() => {
                if (contextDisplay.style.opacity === '0') {
                    contextDisplay.style.display = 'none';
                }
            }, 200);
        }, true);
		}

		initScoreAnimation() {
        const mainScoreOrb = this.dashboard.querySelector('.main-score-orb');
        if (!mainScoreOrb) return;

        const mainScoreInsight = this.dashboard.querySelector('.main-score-insight');
        const score = parseFloat(mainScoreOrb.dataset.score) || 0;
        const scoreValueElement = mainScoreOrb.querySelector('.main-score-value');

        scoreValueElement.textContent = '0.0';

				setTimeout(() => {
            mainScoreOrb.classList.add('loaded');
            if(mainScoreInsight) mainScoreInsight.classList.add('visible');

            let start = 0;
            const duration = 1500;
            const step = (timestamp) => {
                if (!start) start = timestamp;
                const progress = timestamp - start;
                const current = Math.min((progress / duration) * score, score);
                scoreValueElement.textContent = current.toFixed(1);
                if (progress < duration) {
                    window.requestAnimationFrame(step);
                } else {
                    scoreValueElement.textContent = score.toFixed(1);
						}
            };
            window.requestAnimationFrame(step);
				}, 500);
		}

		initPillarAnimation() {
        const pillarOrbs = this.dashboard.querySelectorAll('.pillar-orb');
			if (pillarOrbs.length > 0) {
            pillarOrbs.forEach((orb, index) => {
					setTimeout(() => {
                    orb.classList.add('visible');
						setTimeout(() => {
                        orb.classList.add('loaded');
						}, 300);
                }, 700 + (index * 150));
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