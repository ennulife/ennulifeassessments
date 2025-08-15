/**
 * ENNU Dashboard Functions
 * Extracted from user-dashboard.php template
 * 
 * @package ENNU_Life_Assessments
 * @version 64.55.1
 */

(function($) {
    'use strict';
    
    // Dashboard Functions Namespace
    window.ENNUDashboardFunctions = {
        
        // Toggle panel visibility
        togglePanel: function(panelKey) {
            const panel = document.getElementById('panel-' + panelKey);
            const expandIcon = document.getElementById('expand-icon-' + panelKey);
            
            if (panel && expandIcon) {
                if (panel.style.display === 'none' || panel.style.display === '') {
                    panel.style.display = 'block';
                    expandIcon.textContent = '▼';
                } else {
                    panel.style.display = 'none';
                    expandIcon.textContent = '▶';
                }
            }
        },
        
        // Toggle biomarker measurements
        toggleBiomarkerMeasurements: function(biomarkerKey) {
            const container = document.getElementById('biomarker-measurements-' + biomarkerKey);
            const listItem = document.getElementById('biomarker-list-item-' + biomarkerKey);
            const expandIcon = document.getElementById('biomarker-expand-' + biomarkerKey);
            
            if (container && listItem && expandIcon) {
                if (container.style.display === 'none' || container.style.display === '') {
                    container.style.display = 'block';
                    listItem.classList.add('expanded');
                    expandIcon.textContent = '▼';
                } else {
                    container.style.display = 'none';
                    listItem.classList.remove('expanded');
                    expandIcon.textContent = '▶';
                }
            }
        },
        
        // Toggle vector category
        toggleVectorCategory: function(categoryKey) {
            const content = document.getElementById('vector-category-' + categoryKey);
            const header = document.getElementById('vector-header-' + categoryKey);
            const expandIcon = document.getElementById('vector-expand-' + categoryKey);
            
            if (content && header && expandIcon) {
                if (content.style.display === 'none' || content.style.display === '') {
                    content.style.display = 'block';
                    header.classList.add('expanded');
                    expandIcon.textContent = '▼';
                } else {
                    content.style.display = 'none';
                    header.classList.remove('expanded');
                    expandIcon.textContent = '▶';
                }
            }
        },
        
        // Toggle feedback form
        toggleFeedbackForm: function() {
            const form = document.getElementById('dashboard-feedback-form');
            const overlay = document.getElementById('feedback-overlay');
            
            if (form && overlay) {
                if (form.style.display === 'none' || form.style.display === '') {
                    form.style.display = 'block';
                    overlay.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                } else {
                    form.style.display = 'none';
                    overlay.style.display = 'none';
                    document.body.style.overflow = '';
                }
            }
        },
        
        // Submit feedback
        submitFeedback: function() {
            const feedbackText = document.getElementById('feedback-text');
            const feedbackRating = document.querySelector('input[name="feedback-rating"]:checked');
            
            if (!feedbackText || !feedbackText.value.trim()) {
                alert('Please enter your feedback before submitting.');
                return;
            }
            
            const feedbackData = {
                action: 'ennu_submit_dashboard_feedback',
                nonce: ennuDashboard.nonce,
                feedback: feedbackText.value,
                rating: feedbackRating ? feedbackRating.value : 0,
                page: 'user-dashboard'
            };
            
            $.ajax({
                url: ennuDashboard.ajax_url,
                type: 'POST',
                data: feedbackData,
                success: function(response) {
                    if (response.success) {
                        alert('Thank you for your feedback!');
                        ENNUDashboardFunctions.toggleFeedbackForm();
                        feedbackText.value = '';
                        if (feedbackRating) {
                            feedbackRating.checked = false;
                        }
                    } else {
                        alert('Error submitting feedback. Please try again.');
                    }
                },
                error: function() {
                    alert('Error submitting feedback. Please try again.');
                }
            });
        },
        
        // Initialize greeting system
        initGreetingSystem: function() {
            const greetingEl = document.getElementById('dashboard-greeting');
            if (!greetingEl) return;
            
            const hour = new Date().getHours();
            let greeting = 'Hello';
            let emoji = '👋';
            
            if (hour >= 5 && hour < 12) {
                greeting = 'Good morning';
                emoji = '☀️';
            } else if (hour >= 12 && hour < 17) {
                greeting = 'Good afternoon';
                emoji = '🌤️';
            } else if (hour >= 17 && hour < 21) {
                greeting = 'Good evening';
                emoji = '🌅';
            } else {
                greeting = 'Good night';
                emoji = '🌙';
            }
            
            const userName = greetingEl.dataset.userName || 'there';
            greetingEl.innerHTML = `${emoji} ${greeting}, <span class="greeting-name">${userName}</span>!`;
        },
        
        // Initialize tab system
        initTabSystem: function() {
            const tabLinks = document.querySelectorAll('.my-story-tab-link');
            const tabContents = document.querySelectorAll('.my-story-tab-content');
            
            if (tabLinks.length === 0 || tabContents.length === 0) return;
            
            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetTab = this.getAttribute('data-tab');
                    
                    // Remove active class from all tabs and contents
                    tabLinks.forEach(l => l.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show corresponding content
                    const targetContent = document.getElementById('tab-' + targetTab);
                    if (targetContent) {
                        targetContent.classList.add('active');
                    }
                });
            });
            
            // Activate first tab by default
            if (tabLinks.length > 0) {
                tabLinks[0].click();
            }
        },
        
        // Upload PDF handler
        uploadPDF: function() {
            const fileInput = document.getElementById('labcorp_pdf');
            if (!fileInput) {
                console.error('File input not found');
                return;
            }
            
            const file = fileInput.files[0];
            if (!file) {
                alert('Please select a PDF file to upload.');
                return;
            }
            
            if (file.type !== 'application/pdf') {
                alert('Please select a valid PDF file.');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'ennu_upload_pdf');  // Fixed to match PHP handler
            formData.append('nonce', ennuDashboard.nonce);
            formData.append('labcorp_pdf', file);  // Fixed to match PHP handler field name
            
            // Show upload progress
            const progressBar = document.getElementById('ennu-pdf-progress');
            const statusMessage = document.getElementById('ennu-pdf-feedback');
            
            if (progressBar) progressBar.style.display = 'block';
            if (statusMessage) {
                statusMessage.style.display = 'block';
                statusMessage.textContent = 'Uploading...';
                statusMessage.className = 'ennu-feedback ennu-feedback-info';
            }
            
            // Debug logging
            console.log('Uploading PDF to:', ennuDashboard.ajax_url);
            console.log('Action:', 'ennu_upload_pdf');
            console.log('Nonce:', ennuDashboard.nonce);
            
            $.ajax({
                url: ennuDashboard.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = (evt.loaded / evt.total) * 100;
                            if (progressBar) {
                                // Update progress display (since it's a div, not a progress element)
                                const progressText = progressBar.querySelector('p');
                                if (progressText) {
                                    progressText.textContent = 'Processing PDF... ' + Math.round(percentComplete) + '%';
                                }
                            }
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (progressBar) progressBar.style.display = 'none';
                    
                    if (response.success) {
                        // Hide feedback temporarily while modal shows
                        if (statusMessage) {
                            statusMessage.style.display = 'none';
                        }
                        
                        // Show modal if available
                        if (window.ENNUModalManager) {
                            window.ENNUModalManager.showModal('labcorp_upload', function() {
                                // After modal completes, show success message briefly
                                if (statusMessage) {
                                    statusMessage.textContent = 'Upload successful! Your biomarkers have been imported.';
                                    statusMessage.className = 'ennu-feedback success';
                                    statusMessage.style.display = 'block';
                                }
                                
                                // Reset form
                                fileInput.value = '';
                                
                                // Reload page after a short delay to show updated biomarkers
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            });
                        } else {
                            // Fallback if modal manager not available
                            if (statusMessage) {
                                statusMessage.textContent = 'Upload successful! Your biomarkers have been imported.';
                                statusMessage.className = 'ennu-feedback ennu-feedback-success';
                                statusMessage.style.display = 'block';
                            }
                            
                            // Show notification
                            ENNUDashboardFunctions.showNotification({
                                success: true,
                                notification: {
                                    type: 'success',
                                    title: 'Lab Results Uploaded',
                                    message: response.data.message || 'Your lab results have been successfully uploaded and processed.',
                                    biomarker_count: response.data.biomarker_count,
                                    biomarker_details: response.data.biomarker_details
                                }
                            });
                            
                            // Reset form
                            fileInput.value = '';
                            
                            // Refresh biomarkers section if needed
                            if (response.data.refresh_biomarkers) {
                                ENNUDashboardFunctions.refreshBiomarkers();
                            }
                            
                            // Hide success message after 5 seconds
                            setTimeout(function() {
                                if (statusMessage) statusMessage.style.display = 'none';
                            }, 5000);
                        }
                    } else {
                        if (statusMessage) {
                            statusMessage.textContent = 'Upload failed: ' + (response.data || 'Unknown error');
                            statusMessage.className = 'ennu-feedback ennu-feedback-error';
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('PDF Upload Error:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        statusCode: xhr.status
                    });
                    
                    if (progressBar) progressBar.style.display = 'none';
                    
                    let errorMessage = 'Upload failed: ';
                    if (xhr.status === 0) {
                        errorMessage += 'Network error - please check your connection';
                    } else if (xhr.status === 404) {
                        errorMessage += 'Upload endpoint not found';
                    } else if (xhr.status === 500) {
                        errorMessage += 'Server error';
                    } else {
                        errorMessage += error || 'Unknown error';
                    }
                    
                    if (statusMessage) {
                        statusMessage.textContent = errorMessage;
                        statusMessage.className = 'ennu-feedback ennu-feedback-error';
                        statusMessage.style.display = 'block';
                    }
                }
            });
        },
        
        // Show notification
        showNotification: function(data) {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.ennu-notification-container');
            existingNotifications.forEach(n => n.remove());
            
            // Create notification container
            const notificationContainer = document.createElement('div');
            notificationContainer.className = 'ennu-notification-container';
            
            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'ennu-notification ' + (data.success ? 'success' : 'error');
            
            // Build notification content
            let notificationContent = '';
            
            if (data.notification) {
                const icon = this.getNotificationIcon(data.notification.type);
                
                notificationContent = `
                    <div class="notification-content">
                        <div class="notification-header">
                            <span class="notification-icon">${icon}</span>
                            <h3 class="notification-title">${data.notification.title || 'Notification'}</h3>
                        </div>
                        <p class="notification-message">
                            ${data.notification.message || data.message || 'Operation completed.'}
                        </p>
                `;
                
                // Add biomarker details if available
                if (data.notification.biomarker_details && Object.keys(data.notification.biomarker_details).length > 0) {
                    notificationContent += `
                        <div class="notification-biomarkers">
                            <h4>Imported Biomarkers (${data.notification.biomarker_count})</h4>
                            <div class="biomarker-grid">
                    `;
                    
                    Object.entries(data.notification.biomarker_details).forEach(([key, details]) => {
                        notificationContent += `
                            <div class="biomarker-item">
                                <div class="biomarker-name">${this.getBiomarkerDisplayName(key)}</div>
                                <div class="biomarker-value">${details.display}</div>
                            </div>
                        `;
                    });
                    
                    notificationContent += `
                            </div>
                        </div>
                    `;
                }
                
                notificationContent += `
                    </div>
                    <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
                `;
            } else {
                // Fallback for simple messages
                notificationContent = `
                    <div class="notification-content">
                        <h3>${data.success ? 'Success' : 'Error'}</h3>
                        <p>${data.message || 'Operation completed.'}</p>
                    </div>
                    <button class="notification-close" onclick="this.parentElement.parentElement.remove()">×</button>
                `;
            }
            
            notification.innerHTML = notificationContent;
            notificationContainer.appendChild(notification);
            document.body.appendChild(notificationContainer);
            
            // Animate in
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Auto-remove after delay
            const autoRemoveTime = data.notification?.type === 'success' ? 10000 : 15000;
            setTimeout(() => {
                if (notificationContainer.parentElement) {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        if (notificationContainer.parentElement) {
                            notificationContainer.remove();
                        }
                    }, 300);
                }
            }, autoRemoveTime);
        },
        
        // Get notification icon
        getNotificationIcon: function(type) {
            const icons = {
                success: '✅',
                error: '❌',
                warning: '⚠️',
                info: 'ℹ️'
            };
            return icons[type] || icons.info;
        },
        
        // Get biomarker display name
        getBiomarkerDisplayName: function(key) {
            const names = {
                'total_cholesterol': 'Total Cholesterol',
                'ldl_cholesterol': 'LDL Cholesterol',
                'hdl_cholesterol': 'HDL Cholesterol',
                'triglycerides': 'Triglycerides',
                'glucose': 'Glucose',
                'hba1c': 'HbA1c',
                'testosterone': 'Testosterone',
                'vitamin_d': 'Vitamin D'
            };
            return names[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },
        
        // Refresh biomarkers section
        refreshBiomarkers: function() {
            $.ajax({
                url: ennuDashboard.ajax_url,
                type: 'POST',
                data: {
                    action: 'ennu_refresh_biomarkers',
                    nonce: ennuDashboard.nonce
                },
                success: function(response) {
                    if (response.success && response.data.html) {
                        const biomarkersSection = document.getElementById('tab-my-biomarkers');
                        if (biomarkersSection) {
                            biomarkersSection.innerHTML = response.data.html;
                            // Reinitialize biomarker events
                            ENNUDashboardFunctions.initBiomarkerEvents();
                        }
                    }
                }
            });
        },
        
        // Initialize biomarker events
        initBiomarkerEvents: function() {
            // Add click handlers for biomarker items
            document.querySelectorAll('.biomarker-item').forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
            
            // Initialize biomarker ruler tooltips
            this.initBiomarkerRulerTooltips();
        },
        
        // Initialize biomarker ruler tooltips
        initBiomarkerRulerTooltips: function() {
            document.querySelectorAll('.biomarker-range-ruler-container').forEach(container => {
                const tooltip = container.querySelector('.biomarker-dynamic-tooltip');
                if (!tooltip) return;
                
                const ruler = container.querySelector('.biomarker-range-ruler');
                if (!ruler) return;
                
                container.addEventListener('mousemove', function(e) {
                    if (container.getAttribute('data-dot-hover') === 'true') {
                        tooltip.style.display = 'none';
                        return;
                    }
                    
                    const rect = ruler.getBoundingClientRect();
                    const mouseX = e.clientX - rect.left;
                    const rulerWidth = rect.width;
                    const percentage = Math.max(0, Math.min(100, (mouseX / rulerWidth) * 100));
                    
                    // Calculate value at position
                    const criticalMin = parseFloat(container.dataset.criticalMin);
                    const criticalMax = parseFloat(container.dataset.criticalMax);
                    const totalRange = criticalMax - criticalMin;
                    const valueAtPosition = criticalMin + (percentage / 100) * totalRange;
                    
                    // Update tooltip
                    tooltip.style.left = mouseX + 'px';
                    tooltip.style.top = '-25px';
                    tooltip.style.display = 'block';
                    
                    const rangeLine = tooltip.querySelector('.tooltip-range-line');
                    const valueLine = tooltip.querySelector('.tooltip-value-line');
                    
                    if (rangeLine) rangeLine.textContent = 'Range Position';
                    if (valueLine) valueLine.textContent = valueAtPosition.toFixed(1) + ' ' + (container.dataset.unit || '');
                });
                
                container.addEventListener('mouseleave', function() {
                    tooltip.style.display = 'none';
                });
            });
        },
        
        // Initialize all dashboard functions
        init: function() {
            // Initialize greeting
            this.initGreetingSystem();
            
            // Initialize tabs
            this.initTabSystem();
            
            // Initialize biomarker events
            this.initBiomarkerEvents();
            
            // Initialize PDF upload form handler
            const pdfUploadForm = document.getElementById('ennu-pdf-upload-form');
            if (pdfUploadForm) {
                pdfUploadForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    ENNUDashboardFunctions.uploadPDF();
                });
            }
            
            // Make functions globally available
            window.togglePanel = this.togglePanel;
            window.toggleBiomarkerMeasurements = this.toggleBiomarkerMeasurements;
            window.toggleVectorCategory = this.toggleVectorCategory;
            window.toggleFeedbackForm = this.toggleFeedbackForm;
            window.submitFeedback = this.submitFeedback;
            window.uploadPDF = this.uploadPDF;
        }
    };
    
    // Initialize on DOM ready
    $(document).ready(function() {
        ENNUDashboardFunctions.init();
    });
    
})(jQuery);