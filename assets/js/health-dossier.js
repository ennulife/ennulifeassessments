// Full JavaScript for Health Dossier
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined' || !window.dossierData) {
        return;
    }

    // 1. Animate Trinity Circles
    document.querySelectorAll('.pillar-circle').forEach(circle => {
        const score = parseFloat(circle.dataset.score);
        // ... code to animate the conic-gradient background based on score
    });

    // 2. Render Historical Journey Chart
    const timelineCtx = document.getElementById('journey-timeline-chart')?.getContext('2d');
    if (timelineCtx) {
        const labels = dossierData.historicalScores.map(item => new Date(item.date * 1000).toLocaleDateString());
        const data = dossierData.historicalScores.map(item => item.score);
        new Chart(timelineCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Score History',
                    data: data,
                    borderColor: '#007bff',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    }

    // 3. What-If Simulator & Deep-Dive Modals (future enhancements)
    function initializeHealthDossier() {
        // Future interactions and data handling for the health dossier will be managed here.
        // This could include fetching more data, handling user inputs, or updating charts.
    }

    // This is a representation of the JS needed for the deep-dive modals.

    // In assets/js/health-dossier.js, within the DOMContentLoaded listener:
    document.querySelectorAll('.deep-dive-button').forEach(button => {
        button.addEventListener('click', (e) => {
            const card = e.target.closest('.category-card');
            const category = card.querySelector('h4').textContent;
            
            // Find or create the modal element
            let modal = document.querySelector('.dossier-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.className = 'dossier-modal';
                document.body.appendChild(modal);
            }

            // Fetch the deep-dive content (passed from PHP or via a new AJAX call)
            const content = dossierData.deepDiveContent[category] || { explanation: 'Details not available.', user_answer: '', action_plan: [] };

            // Populate and show the modal
            modal.innerHTML = `
                <div class="modal-content">
                    <span class="modal-close">&times;</span>
                    <h2>${category} - Deep Dive</h2>
                    <p><strong>Why it matters:</strong> ${content.explanation}</p>
                    <p><strong>Your Answer:</strong> ${content.user_answer}</p>
                    <h3>Your Path Forward</h3>
                    <ul>${content.action_plan.map(item => `<li>${item}</li>`).join('')}</ul>
                </div>
            `;
            modal.style.display = 'block';

            // Add logic to close the modal
            modal.querySelector('.modal-close').onclick = () => {
                modal.style.display = 'none';
            };
        });
    });
}); 