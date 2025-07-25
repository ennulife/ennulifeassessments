/**
 * ENNU Life Assessment Details JavaScript
 * Handles the timeline chart for individual assessment progress
 */
(function ($) {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {
		initializeTimelineChart();
	});

	function initializeTimelineChart() {
		const ctx = document.getElementById('assessmentTimelineChart');
		if (!ctx) {
			console.warn('ENNU Chart: Canvas element not found');
			return;
		}
		
		if (typeof Chart === 'undefined') {
			console.error('ENNU Chart: Chart.js library not loaded');
			return;
		}

		// Check if we have the localized data
		if (typeof assessmentDetailsData === 'undefined') {
			console.error('ENNU Chart: assessmentDetailsData not found');
			return;
		}
		
		if (!assessmentDetailsData.scoreHistory) {
			console.warn('ENNU Chart: No score history data available');
			return;
		}

		console.log('ENNU Chart: Initializing with data:', assessmentDetailsData.scoreHistory);
		
		const chartData = assessmentDetailsData.scoreHistory.map(item => ({
			x: new Date(item.date),
			y: item.score
		}));

		console.log('ENNU Chart: Processed chart data:', chartData);

		// Only render chart if we have data
		if (chartData.length === 0) {
			console.warn('ENNU Chart: No valid chart data available');
			// Show a message instead of empty chart
			const container = ctx.parentElement;
			container.innerHTML = '<p style="text-align: center; color: var(--text-light); padding: 40px;">Complete this assessment multiple times to see your progress over time.</p>';
			return;
		}

		new Chart(ctx, {
			type: 'line',
			data: {
				datasets: [{
					label: assessmentDetailsData.assessmentType + ' Score',
					data: chartData,
					borderColor: 'rgba(52, 211, 153, 1)',
					backgroundColor: 'rgba(52, 211, 153, 0.1)',
					fill: true,
					tension: 0.4,
					pointRadius: 5,
					pointHoverRadius: 7,
					pointBackgroundColor: 'rgba(52, 211, 153, 1)',
					pointBorderColor: '#fff',
					pointBorderWidth: 2
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				scales: {
					x: {
						type: 'time',
						time: {
							unit: chartData.length > 30 ? 'week' : 'day',
							displayFormats: {
								day: 'MMM d',
								week: 'MMM d'
							}
						},
						grid: {
							color: 'rgba(255, 255, 255, 0.1)'
						},
						ticks: {
							color: 'rgba(255, 255, 255, 0.7)'
						}
					},
					y: {
						beginAtZero: true,
						max: 10,
						grid: {
							color: 'rgba(255, 255, 255, 0.1)'
						},
						ticks: {
							color: 'rgba(255, 255, 255, 0.7)',
							stepSize: 2
						}
					}
				},
				plugins: {
					legend: {
						display: false
					},
					tooltip: {
						backgroundColor: 'rgba(0, 0, 0, 0.8)',
						titleColor: '#fff',
						bodyColor: '#fff',
						borderColor: 'rgba(52, 211, 153, 1)',
						borderWidth: 1,
						displayColors: false,
						callbacks: {
							title: function(tooltipItems) {
								const date = new Date(tooltipItems[0].parsed.x);
								return date.toLocaleDateString('en-US', { 
									month: 'short', 
									day: 'numeric', 
									year: 'numeric' 
								});
							},
							label: function(context) {
								return 'Score: ' + context.parsed.y.toFixed(1);
							}
						}
					}
				}
			}
		});
	}

})();     