/**
 * ENNU Life Assessments - Client-Side Monitoring
 *
 * Provides client-side error tracking and performance monitoring
 *
 * @package ENNU_Life
 * @since 64.6.30
 */

(function($) {
    'use strict';

    /**
     * ENNU Client Monitoring
     */
    var ENNUClientMonitoring = {

        /**
         * Initialize monitoring
         */
        init: function() {
            this.setupErrorHandling();
            this.setupPerformanceMonitoring();
            this.setupUserActivityTracking();
        },

        /**
         * Setup error handling
         */
        setupErrorHandling: function() {
            // Global error handler
            window.addEventListener('error', function(event) {
                ENNUClientMonitoring.logError({
                    message: event.message,
                    file: event.filename,
                    line: event.lineno,
                    stack_trace: event.error ? event.error.stack : '',
                    url: window.location.href
                });
            });

            // Unhandled promise rejections
            window.addEventListener('unhandledrejection', function(event) {
                ENNUClientMonitoring.logError({
                    message: 'Unhandled Promise Rejection: ' + (event.reason ? event.reason.message : 'Unknown'),
                    file: 'Promise',
                    line: 0,
                    stack_trace: event.reason ? event.reason.stack : '',
                    url: window.location.href
                });
            });

            // AJAX error handling
            $(document).ajaxError(function(event, xhr, settings, error) {
                ENNUClientMonitoring.logError({
                    message: 'AJAX Error: ' + error,
                    file: settings.url,
                    line: 0,
                    stack_trace: '',
                    url: window.location.href
                });
            });
        },

        /**
         * Setup performance monitoring
         */
        setupPerformanceMonitoring: function() {
            // Page load performance
            if (window.performance && window.performance.timing) {
                window.addEventListener('load', function() {
                    var timing = window.performance.timing;
                    var loadTime = timing.loadEventEnd - timing.navigationStart;
                    var domReadyTime = timing.domContentLoadedEventEnd - timing.navigationStart;

                    ENNUClientMonitoring.logPerformance({
                        load_time: loadTime,
                        dom_ready_time: domReadyTime,
                        url: window.location.href
                    });
                });
            }

            // Resource timing
            if (window.performance && window.performance.getEntriesByType) {
                window.addEventListener('load', function() {
                    var resources = window.performance.getEntriesByType('resource');
                    var ennuResources = resources.filter(function(resource) {
                        return resource.name.indexOf('ennu') !== -1;
                    });

                    ennuResources.forEach(function(resource) {
                        ENNUClientMonitoring.logResourceTiming({
                            name: resource.name,
                            duration: resource.duration,
                            size: resource.transferSize || 0,
                            url: window.location.href
                        });
                    });
                });
            }
        },

        /**
         * Setup user activity tracking
         */
        setupUserActivityTracking: function() {
            var activityData = {
                page_views: 0,
                clicks: 0,
                form_submissions: 0,
                assessment_interactions: 0
            };

            // Track page views
            activityData.page_views++;
            ENNUClientMonitoring.logActivity('page_view', {
                url: window.location.href,
                title: document.title
            });

            // Track clicks on ENNU elements
            $(document).on('click', '[data-ennu-tracking]', function() {
                var trackingData = $(this).data('ennu-tracking');
                ENNUClientMonitoring.logActivity('click', trackingData);
                activityData.clicks++;
            });

            // Track form submissions
            $(document).on('submit', 'form[data-ennu-form]', function() {
                var formData = $(this).data('ennu-form');
                ENNUClientMonitoring.logActivity('form_submission', formData);
                activityData.form_submissions++;
            });

            // Track assessment interactions
            $(document).on('change', '[data-ennu-assessment]', function() {
                var assessmentData = $(this).data('ennu-assessment');
                ENNUClientMonitoring.logActivity('assessment_interaction', assessmentData);
                activityData.assessment_interactions++;
            });

            // Periodic activity reporting
            setInterval(function() {
                if (activityData.clicks > 0 || activityData.form_submissions > 0 || activityData.assessment_interactions > 0) {
                    ENNUClientMonitoring.logActivity('periodic_summary', activityData);
                    // Reset counters
                    activityData.clicks = 0;
                    activityData.form_submissions = 0;
                    activityData.assessment_interactions = 0;
                }
            }, 60000); // Report every minute
        },

        /**
         * Log error to server
         *
         * @param {Object} errorData Error data
         */
        logError: function(errorData) {
            $.ajax({
                url: ennuMonitoring.ajax_url,
                type: 'POST',
                data: {
                    action: 'ennu_log_error',
                    nonce: ennuMonitoring.nonce,
                    message: errorData.message,
                    file: errorData.file,
                    line: errorData.line,
                    stack_trace: errorData.stack_trace,
                    url: errorData.url
                },
                success: function(response) {
                },
                error: function(xhr, status, error) {
                }
            });
        },

        /**
         * Log performance metrics
         *
         * @param {Object} performanceData Performance data
         */
        logPerformance: function(performanceData) {
            $.ajax({
                url: ennuMonitoring.ajax_url,
                type: 'POST',
                data: {
                    action: 'ennu_log_performance',
                    nonce: ennuMonitoring.nonce,
                    load_time: performanceData.load_time,
                    dom_ready_time: performanceData.dom_ready_time,
                    url: performanceData.url
                },
                success: function(response) {
                },
                error: function(xhr, status, error) {
                }
            });
        },

        /**
         * Log resource timing
         *
         * @param {Object} resourceData Resource timing data
         */
        logResourceTiming: function(resourceData) {
            $.ajax({
                url: ennuMonitoring.ajax_url,
                type: 'POST',
                data: {
                    action: 'ennu_log_resource_timing',
                    nonce: ennuMonitoring.nonce,
                    name: resourceData.name,
                    duration: resourceData.duration,
                    size: resourceData.size,
                    url: resourceData.url
                },
                success: function(response) {
                },
                error: function(xhr, status, error) {
                }
            });
        },

        /**
         * Log user activity
         *
         * @param {string} activityType Activity type
         * @param {Object} activityData Activity data
         */
        logActivity: function(activityType, activityData) {
            $.ajax({
                url: ennuMonitoring.ajax_url,
                type: 'POST',
                data: {
                    action: 'ennu_log_activity',
                    nonce: ennuMonitoring.nonce,
                    activity_type: activityType,
                    activity_data: activityData
                },
                success: function(response) {
                },
                error: function(xhr, status, error) {
                }
            });
        },

        /**
         * Track custom event
         *
         * @param {string} eventName Event name
         * @param {Object} eventData Event data
         */
        trackEvent: function(eventName, eventData) {
            $.ajax({
                url: ennuMonitoring.ajax_url,
                type: 'POST',
                data: {
                    action: 'ennu_track_event',
                    nonce: ennuMonitoring.nonce,
                    event_name: eventName,
                    event_data: eventData
                },
                success: function(response) {
                },
                error: function(xhr, status, error) {
                }
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        ENNUClientMonitoring.init();
    });

    // Make available globally
    window.ENNUClientMonitoring = ENNUClientMonitoring;

})(jQuery); 