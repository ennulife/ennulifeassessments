document.addEventListener('DOMContentLoaded', function() {
	if (typeof Chart === 'undefined') {
		return;
	}
	const ctx = document.getElementById('ennuRadarChart');
	if (!ctx) {
		return;
	}

	if (typeof ennuResultsData === 'undefined' || !ennuResultsData.chart_labels || !ennuResultsData.chart_data) {
		return;
	}

	new Chart(ctx.getContext('2d'), {
		type: 'radar',
		data: {
			labels: ennuResultsData.chart_labels,
			datasets: [{
				label: 'Your Scores',
				data: ennuResultsData.chart_data,
				backgroundColor: 'rgba(79, 172, 254, 0.2)',
				borderColor: 'rgba(79, 172, 254, 1)',
				borderWidth: 2
			}]
		},
		options: {
			scales: { r: { suggestedMin: 0, suggestedMax: 10 } },
			plugins: { legend: { display: false } }
		}
	});
});  