document.addEventListener('DOMContentLoaded', function() {
	if (typeof Chart === 'undefined') {
		if (document.querySelector('.ennu-chart-container')) {
			document.querySelector('.ennu-chart-container').innerHTML += '<p>Error loading chart. Please refresh or contact support.</p>';
		}
		return;
	}

	const ctx = document.getElementById('ennuRadarChart');
	if (!ctx) {
		return;
	}

	if (typeof ennuChartData === 'undefined' || !ennuChartData.labels || !ennuChartData.data) {
		return;
	}

	new Chart(ctx.getContext('2d'), {
		type: 'radar',
		data: {
			labels: ennuChartData.labels,
			datasets: [{
				label: 'Your Scores',
				data: ennuChartData.data,
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