document.addEventListener('DOMContentLoaded', function() {
    const clearDataButton = document.getElementById('ennu-clear-data');
    if (clearDataButton) {
        clearDataButton.addEventListener('click', function() {
            if (confirm(ennu_admin.confirm_msg)) {
                const userId = this.dataset.userId;
                const nonce = ennu_admin.nonce;

                const formData = new FormData();
                formData.append('action', 'ennu_clear_user_data');
                formData.append('user_id', userId);
                formData.append('nonce', nonce);

                fetch(ennu_admin.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.data);
                        location.reload();
                    } else {
                        alert('Error: ' + data.data);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('AJAX error - please try again.');
                });
            }
        });
    }

    const tabContainer = document.querySelector(".ennu-admin-tabs");
    if (tabContainer) {
        const tabLinks = tabContainer.querySelectorAll(".ennu-admin-tab-nav a");
        const tabContents = tabContainer.querySelectorAll(".ennu-admin-tab-content");

        tabLinks.forEach(link => {
            link.addEventListener("click", function (event) {
                event.preventDefault();

                const targetId = this.getAttribute("href");

                tabLinks.forEach(l => l.classList.remove("ennu-admin-tab-active"));
                tabContents.forEach(c => c.classList.remove("ennu-admin-tab-active"));

                this.classList.add("ennu-admin-tab-active");
                document.querySelector(targetId).classList.add("ennu-admin-tab-active");
            });
        });
    }

    const spinner = document.querySelector(".ennu-admin-action-buttons .spinner");

    const recalculateScoresButton = document.getElementById('ennu-recalculate-scores');
    if (recalculateScoresButton) {
        recalculateScoresButton.addEventListener('click', function() {
            if (confirm('Are you sure you want to recalculate all scores for this user? This will overwrite their current calculated scores.')) {
                this.disabled = true;
                spinner.classList.add('is-active');

                const userId = this.dataset.userId;
                const nonce = ennuAdmin.nonce;

                const formData = new URLSearchParams();
                formData.append('action', 'ennu_recalculate_all_scores');
                formData.append('user_id', userId);
                formData.append('nonce', nonce);

                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Scores recalculated successfully!');
                        location.reload();
                    } else {
                        alert('An error occurred: ' + data.data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('A server error occurred.');
                })
                .finally(() => {
                    this.disabled = false;
                    spinner.classList.remove('is-active');
                });
            }
        });
    }

    const clearAllDataButton = document.getElementById('ennu-clear-all-data');
    if (clearAllDataButton) {
        clearAllDataButton.addEventListener('click', function() {
            if (confirm('WARNING: Are you absolutely sure you want to clear ALL assessment data for this user? This action cannot be undone.')) {
                this.disabled = true;
                spinner.classList.add('is-active');
                
                const userId = this.dataset.userId;
                const nonce = ennuAdmin.nonce;

                const formData = new URLSearchParams();
                formData.append('action', 'ennu_clear_all_assessment_data');
                formData.append('user_id', userId);
                formData.append('nonce', nonce);

                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('All assessment data cleared successfully!');
                        location.reload();
                    } else {
                        alert('An error occurred: ' + data.data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('A server error occurred.');
                })
                .finally(() => {
                    this.disabled = false;
                    spinner.classList.remove('is-active');
                });
            }
        });
    }

    const clearSingleAssessmentButtons = document.querySelectorAll('.ennu-clear-single-assessment-data');
    clearSingleAssessmentButtons.forEach(button => {
        button.addEventListener('click', function() {
            const assessmentKey = this.dataset.assessmentKey;
            if (confirm('Are you sure you want to clear the data for the ' + assessmentKey.replace(/_/g, " ") + ' assessment? This action cannot be undone.')) {
                this.disabled = true;
                spinner.classList.add('is-active');
                
                const userId = this.dataset.userId;
                const nonce = ennuAdmin.nonce;

                const formData = new URLSearchParams();
                formData.append('action', 'ennu_clear_single_assessment_data');
                formData.append('user_id', userId);
                formData.append('assessment_key', assessmentKey);
                formData.append('nonce', nonce);

                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(assessmentKey.replace(/_/g, " ") + ' data cleared successfully!');
                        location.reload();
                    } else {
                        alert('An error occurred: ' + data.data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('A server error occurred.');
                })
                .finally(() => {
                    this.disabled = false;
                    spinner.classList.remove('is-active');
                });
            }
        });
    });
}); 