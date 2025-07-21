document.addEventListener('DOMContentLoaded', function() {
    
    const navTabs = document.querySelectorAll('.nav-tab');
    navTabs.forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            const target = this.getAttribute('href');
            
            navTabs.forEach(t => t.classList.remove('nav-tab-active'));
            this.classList.add('nav-tab-active');
            
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            const targetElement = document.querySelector(target);
            if (targetElement) {
                targetElement.classList.add('active');
            }
        });
    });
    
    const importMethodInputs = document.querySelectorAll('input[name="import_method"]');
    importMethodInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const csvUploadRow = document.querySelector('.csv-upload-row');
            const manualEntrySection = document.querySelector('.manual-entry-section');
            
            if (this.value === 'csv') {
                if (csvUploadRow) csvUploadRow.style.display = 'block';
                if (manualEntrySection) manualEntrySection.style.display = 'none';
            } else {
                if (csvUploadRow) csvUploadRow.style.display = 'none';
                if (manualEntrySection) manualEntrySection.style.display = 'block';
            }
        });
    });
    
    const addBiomarkerBtn = document.getElementById('add-biomarker-entry');
    if (addBiomarkerBtn) {
        addBiomarkerBtn.addEventListener('click', function() {
            const template = document.querySelector('.biomarker-entry');
            if (template) {
                const clone = template.cloneNode(true);
                clone.querySelectorAll('input, select').forEach(input => input.value = '');
                const container = document.getElementById('biomarker-entries');
                if (container) {
                    container.appendChild(clone);
                }
            }
        });
    }
    
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-biomarker-entry')) {
            const entries = document.querySelectorAll('.biomarker-entry');
            if (entries.length > 1) {
                const entry = e.target.closest('.biomarker-entry');
                if (entry) {
                    entry.remove();
                }
            }
        }
    });
    
    const labImportForm = document.getElementById('ennu-lab-import-form');
    if (labImportForm) {
        labImportForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'ennu_import_lab_data');
            formData.append('nonce', ennuBiomarkerAdmin.nonce);
            
            const submitBtn = this.querySelector('input[type="submit"]');
            const originalText = submitBtn.value;
            submitBtn.value = 'Importing...';
            submitBtn.disabled = true;
            
            fetch(ennuBiomarkerAdmin.ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', data.data.message);
                    if (data.data.errors && data.data.errors.length > 0) {
                        showMessage('error', 'Some errors occurred: ' + data.data.errors.join(', '));
                    }
                } else {
                    showMessage('error', data.data.message || 'Import failed');
                }
            })
            .catch(() => {
                showMessage('error', 'Network error occurred');
            })
            .finally(() => {
                submitBtn.value = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    const targetsUserSelect = document.getElementById('targets-user-select');
    if (targetsUserSelect) {
        targetsUserSelect.addEventListener('change', function() {
            const userId = this.value;
            const doctorTargetsSection = document.getElementById('doctor-targets-section');
            
            if (userId) {
                loadUserBiomarkers(userId);
                if (doctorTargetsSection) {
                    doctorTargetsSection.style.display = 'block';
                }
            } else {
                if (doctorTargetsSection) {
                    doctorTargetsSection.style.display = 'none';
                }
            }
        });
    }
    
    const doctorTargetsForm = document.getElementById('ennu-doctor-targets-form');
    if (doctorTargetsForm) {
        doctorTargetsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'ennu_save_doctor_targets');
            formData.append('nonce', ennuBiomarkerAdmin.nonce);
            
            const submitBtn = this.querySelector('input[type="submit"]');
            const originalText = submitBtn.value;
            submitBtn.value = 'Saving...';
            submitBtn.disabled = true;
            
            fetch(ennuBiomarkerAdmin.ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('success', data.data.message);
                } else {
                    showMessage('error', data.data.message || 'Save failed');
                }
            })
            .catch(() => {
                showMessage('error', 'Network error occurred');
            })
            .finally(() => {
                submitBtn.value = originalText;
                submitBtn.disabled = false;
            });
        });
    }
    
    function loadUserBiomarkers(userId) {
        const targetsContainer = document.getElementById('targets-container');
        if (targetsContainer) {
            targetsContainer.innerHTML = '<div class="loading-spinner"></div>';
        }
        
        const formData = new FormData();
        formData.append('action', 'ennu_get_user_biomarkers');
        formData.append('user_id', userId);
        formData.append('nonce', ennuBiomarkerAdmin.nonce);
        
        fetch(ennuBiomarkerAdmin.ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTargetsForm(data.data.biomarkers, data.data.targets);
            } else {
                if (targetsContainer) {
                    targetsContainer.innerHTML = '<p>Error loading biomarkers</p>';
                }
            }
        })
        .catch(() => {
            if (targetsContainer) {
                targetsContainer.innerHTML = '<p>Network error occurred</p>';
            }
        });
    }
    
    function renderTargetsForm(biomarkers, existingTargets) {
        let html = '';
        
        if (biomarkers && Object.keys(biomarkers).length > 0) {
            for (const biomarker in biomarkers) {
                const data = biomarkers[biomarker];
                const currentTarget = existingTargets[biomarker] || '';
                
                html += '<div class="target-input-group">';
                html += '<label>' + (data.name || biomarker.replace(/_/g, ' ')) + ' (' + (data.unit || '') + ')</label>';
                html += '<input type="number" step="0.01" name="targets[' + biomarker + ']" value="' + currentTarget + '" placeholder="Target value">';
                html += '<span class="current-value">Current: ' + (data.value || 'N/A') + '</span>';
                html += '</div>';
            }
        } else {
            html = '<p>No biomarker data found for this user. Import lab data first.</p>';
        }
        
        const targetsContainer = document.getElementById('targets-container');
        if (targetsContainer) {
            targetsContainer.innerHTML = html;
        }
    }
    
    function showMessage(type, message) {
        const messageClass = type === 'success' ? 'success-message' : 'error-message';
        const messageHtml = '<div class="' + messageClass + '">' + message + '</div>';
        
        const wrap = document.querySelector('.wrap');
        if (wrap) {
            wrap.insertAdjacentHTML('afterbegin', messageHtml);
            
            setTimeout(function() {
                const messageElement = document.querySelector('.' + messageClass);
                if (messageElement) {
                    messageElement.style.opacity = '0';
                    messageElement.style.transition = 'opacity 0.3s';
                    setTimeout(() => messageElement.remove(), 300);
                }
            }, 5000);
        }
    }
});
