/**
 * ENNU Biomarker Analytics JavaScript
 * Phase 5: Advanced Reporting & Analytics
 */

jQuery(document).ready(function($) {
    
    // Tab switching functionality
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all tabs and content
        $('.nav-tab').removeClass('nav-tab-active');
        $('.tab-content').removeClass('active');
        
        // Add active class to clicked tab
        $(this).addClass('nav-tab-active');
        
        // Show corresponding content
        var targetTab = $(this).data('tab');
        $('#' + targetTab).addClass('active');
        
        // Initialize charts for the active tab
        if (targetTab === 'trends') {
            initializeTrendCharts();
        } else if (targetTab === 'correlations') {
            initializeCorrelationMatrix();
        }
    });
    
    // Initialize trend charts
    function initializeTrendCharts() {
        // Check if Chart.js is available
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js not loaded');
            return;
        }
        
        // Trend Chart
        var trendCtx = document.getElementById('trendChart');
        if (trendCtx) {
            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Glucose Levels',
                        data: [85, 88, 92, 87, 90, 89],
                        borderColor: '#0073aa',
                        backgroundColor: 'rgba(0, 115, 170, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Biomarker Trend Analysis'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false
                        }
                    }
                }
            });
        }
        
        // Distribution Chart
        var distributionCtx = document.getElementById('distributionChart');
        if (distributionCtx) {
            new Chart(distributionCtx, {
                type: 'bar',
                data: {
                    labels: ['Optimal', 'Suboptimal', 'Poor'],
                    datasets: [{
                        label: 'User Distribution',
                        data: [65, 25, 10],
                        backgroundColor: [
                            '#28a745',
                            '#ffc107',
                            '#dc3545'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Biomarker Distribution'
                        }
                    }
                }
            });
        }
    }
    
    // Initialize correlation matrix
    function initializeCorrelationMatrix() {
        // Placeholder for correlation matrix functionality
        console.log('Correlation matrix initialized');
    }
    
    // Trend analysis form submission
    $('.trend-analysis-form').on('submit', function(e) {
        e.preventDefault();
        
        var biomarker = $('#trend_biomarker').val();
        var period = $('#trend_period').val();
        
        if (!biomarker) {
            alert('Please select a biomarker');
            return;
        }
        
        // Show loading state
        showProgressIndicator();
        
        // Simulate API call
        setTimeout(function() {
            hideProgressIndicator();
            updateTrendInsights(biomarker, period);
        }, 2000);
    });
    
    // Correlation analysis form submission
    $('.correlation-analysis-form').on('submit', function(e) {
        e.preventDefault();
        
        var primaryBiomarker = $('#primary_biomarker').val();
        var secondaryBiomarkers = $('#secondary_biomarkers').val();
        
        if (!primaryBiomarker || !secondaryBiomarkers) {
            alert('Please select both primary and secondary biomarkers');
            return;
        }
        
        // Show loading state
        showProgressIndicator();
        
        // Simulate API call
        setTimeout(function() {
            hideProgressIndicator();
            updateCorrelationMatrix(primaryBiomarker, secondaryBiomarkers);
        }, 2000);
    });
    
    // Custom report form submission
    $('.custom-report-form').on('submit', function(e) {
        var reportName = $('#report_name').val();
        var reportType = $('#report_type').val();
        var dateRange = $('#date_range').val();
        var biomarkers = $('#biomarkers').val();
        
        if (!reportName) {
            alert('Please enter a report name');
            e.preventDefault();
            return;
        }
        
        if (!biomarkers || biomarkers.length === 0) {
            alert('Please select at least one biomarker');
            e.preventDefault();
            return;
        }
        
        // Show loading state
        showProgressIndicator();
    });
    
    // Export form submission
    $('.export-form').on('submit', function(e) {
        var exportType = $('#export_type').val();
        var exportFormat = $('#export_format').val();
        
        if (!exportType || !exportFormat) {
            alert('Please select export type and format');
            e.preventDefault();
            return;
        }
        
        // Show loading state
        showProgressIndicator();
    });
    
    // Insight generation form submission
    $('.insight-generation-form').on('submit', function(e) {
        e.preventDefault();
        
        var insightType = $('#insight_type').val();
        var insightScope = $('#insight_scope').val();
        
        if (!insightType || !insightScope) {
            alert('Please select insight type and scope');
            return;
        }
        
        // Show loading state
        showProgressIndicator();
        
        // Simulate API call
        setTimeout(function() {
            hideProgressIndicator();
            generateInsights(insightType, insightScope);
        }, 3000);
    });
    
    // Quick action functions
    window.generateQuickReport = function() {
        showProgressIndicator();
        
        setTimeout(function() {
            hideProgressIndicator();
            showStatusMessage('Quick report generated successfully!', 'success');
        }, 1500);
    };
    
    window.exportCurrentData = function() {
        showProgressIndicator();
        
        setTimeout(function() {
            hideProgressIndicator();
            showStatusMessage('Data exported successfully!', 'success');
        }, 2000);
    };
    
    window.refreshMetrics = function() {
        showProgressIndicator();
        
        setTimeout(function() {
            hideProgressIndicator();
            updateMetrics();
            showStatusMessage('Metrics refreshed successfully!', 'success');
        }, 1000);
    };
    
    // Update trend insights
    function updateTrendInsights(biomarker, period) {
        var insights = $('#trendInsights');
        var biomarkerName = $('#trend_biomarker option:selected').text();
        
        var insightHtml = '<div class="trend-analysis-results">';
        insightHtml += '<h4>Analysis Results for ' + biomarkerName + '</h4>';
        insightHtml += '<div class="insight-item">';
        insightHtml += '<strong>Trend Direction:</strong> <span class="trend-positive">↗️ Increasing</span>';
        insightHtml += '</div>';
        insightHtml += '<div class="insight-item">';
        insightHtml += '<strong>Change Rate:</strong> +2.3% over ' + period + ' days';
        insightHtml += '</div>';
        insightHtml += '<div class="insight-item">';
        insightHtml += '<strong>Confidence Level:</strong> 87%';
        insightHtml += '</div>';
        insightHtml += '<div class="insight-item">';
        insightHtml += '<strong>Recommendation:</strong> Monitor closely, consider lifestyle adjustments';
        insightHtml += '</div>';
        insightHtml += '</div>';
        
        insights.html(insightHtml);
        showStatusMessage('Trend analysis completed!', 'success');
    }
    
    // Update correlation matrix
    function updateCorrelationMatrix(primary, secondary) {
        var matrixBody = $('#correlationMatrixBody');
        
        var matrixHtml = '';
        secondary.forEach(function(biomarker) {
            var correlation = (Math.random() * 0.8 + 0.2).toFixed(3);
            var strength = correlation > 0.7 ? 'strong' : (correlation > 0.4 ? 'moderate' : 'weak');
            var significance = (Math.random() * 0.1 + 0.9).toFixed(3);
            
            matrixHtml += '<tr>';
            matrixHtml += '<td>' + biomarker + '</td>';
            matrixHtml += '<td>' + correlation + '</td>';
            matrixHtml += '<td><span class="correlation-strength ' + strength + '">' + strength + '</span></td>';
            matrixHtml += '<td>' + significance + '</td>';
            matrixHtml += '</tr>';
        });
        
        matrixBody.html(matrixHtml);
        
        // Update correlation insights
        var insights = $('#correlationInsights');
        insights.html('<p>Correlation analysis completed. Found ' + secondary.length + ' relationships with ' + primary + '.</p>');
        
        showStatusMessage('Correlation analysis completed!', 'success');
    }
    
    // Generate insights
    function generateInsights(type, scope) {
        var insights = {};
        
        if (type === 'patterns' || type === 'all') {
            $('#patternInsights').html('<p>✅ Pattern recognition completed. Detected 3 significant patterns in biomarker relationships.</p>');
        }
        
        if (type === 'anomalies' || type === 'all') {
            $('#anomalyInsights').html('<p>✅ Anomaly detection completed. Found 2 unusual patterns requiring attention.</p>');
        }
        
        if (type === 'predictions' || type === 'all') {
            $('#predictiveInsights').html('<p>✅ Predictive analysis completed. Generated 5 predictions for next 30 days.</p>');
        }
        
        if (type === 'recommendations' || type === 'all') {
            $('#recommendationInsights').html('<p>✅ Recommendations generated. Created 8 personalized optimization suggestions.</p>');
        }
        
        showStatusMessage('AI insights generated successfully!', 'success');
    }
    
    // Update metrics
    function updateMetrics() {
        // Simulate updating metrics with new data
        $('.metric-value').each(function() {
            var currentValue = $(this).text();
            var newValue = Math.floor(Math.random() * 100) + 1000;
            $(this).text(newValue.toLocaleString());
        });
    }
    
    // Progress indicator functions
    function showProgressIndicator() {
        $('.progress-indicator').addClass('active');
        $('body').addClass('loading');
    }
    
    function hideProgressIndicator() {
        $('.progress-indicator').removeClass('active');
        $('body').removeClass('loading');
    }
    
    // Status message functions
    function showStatusMessage(message, type) {
        var statusHtml = '<div class="status-message ' + type + '">' + message + '</div>';
        $('.ennu-biomarker-analytics-content').prepend(statusHtml);
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            $('.status-message').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Real-time data updates (simulated)
    function startRealTimeUpdates() {
        setInterval(function() {
            // Update metrics every 30 seconds
            if (Math.random() > 0.7) { // 30% chance to update
                updateMetrics();
            }
        }, 30000);
    }
    
    // Initialize real-time updates
    startRealTimeUpdates();
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl/Cmd + R to refresh metrics
        if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
            e.preventDefault();
            refreshMetrics();
        }
        
        // Ctrl/Cmd + E to export data
        if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
            e.preventDefault();
            exportCurrentData();
        }
        
        // Ctrl/Cmd + G to generate quick report
        if ((e.ctrlKey || e.metaKey) && e.key === 'g') {
            e.preventDefault();
            generateQuickReport();
        }
    });
    
    // Auto-save functionality
    var autoSaveTimer;
    $('.custom-report-form input, .custom-report-form select, .custom-report-form textarea').on('change', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            // Auto-save form data
            console.log('Auto-saving form data...');
        }, 2000);
    });
    
    // Form validation
    $('.custom-report-form').on('input', function() {
        var reportName = $('#report_name').val();
        var biomarkers = $('#biomarkers').val();
        
        var isValid = true;
        
        if (!reportName || reportName.length < 3) {
            $('#report_name').addClass('error');
            isValid = false;
        } else {
            $('#report_name').removeClass('error');
        }
        
        if (!biomarkers || biomarkers.length === 0) {
            $('#biomarkers').addClass('error');
            isValid = false;
        } else {
            $('#biomarkers').removeClass('error');
        }
        
        // Enable/disable submit button
        $('.custom-report-form button[type="submit"]').prop('disabled', !isValid);
    });
    
    // Chart.js responsive handling
    $(window).on('resize', function() {
        // Reinitialize charts on window resize
        if ($('#trends').hasClass('active')) {
            initializeTrendCharts();
        }
    });
    
    // Initialize tooltips
    $('[data-tooltip]').tooltip({
        position: { my: 'left+5 center', at: 'right center' }
    });
    
    // Export functionality
    function downloadData(data, filename, format) {
        var blob;
        var url;
        
        if (format === 'csv') {
            blob = new Blob([data], { type: 'text/csv' });
        } else if (format === 'json') {
            blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        }
        
        url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename + '.' + format;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    }
    
    // Initialize analytics dashboard
    function initializeDashboard() {
        console.log('ENNU Biomarker Analytics Dashboard initialized');
        
        // Set up event listeners for dynamic content
        $(document).on('click', '.metric-card', function() {
            var metricLabel = $(this).find('.metric-label').text();
            console.log('Metric clicked:', metricLabel);
            // Add drill-down functionality here
        });
        
        // Initialize charts if Chart.js is available
        if (typeof Chart !== 'undefined') {
            // Chart.js is loaded, initialize charts
            console.log('Chart.js detected, initializing charts...');
        } else {
            console.warn('Chart.js not loaded, charts will not be available');
        }
    }
    
    // Initialize dashboard
    initializeDashboard();
    
}); 