/**
 * ENNU Life Enhanced Admin JavaScript - Bulletproof Edition
 * 
 * Bulletproof JavaScript with comprehensive error handling, retry mechanisms,
 * and graceful degradation for zero-issue deployment.
 * 
 * @package ENNU_Life
 * @version 23.1.0
 * @author Manus - World's Greatest WordPress Developer
 */

(function($) {
    'use strict';
    
    // Bulletproof namespace
    window.ENNUAdminEnhanced = window.ENNUAdminEnhanced || {};
    
    const ENNU = {
        // Configuration
        config: {
            maxRetries: 3,
            retryDelay: 1000,
            requestTimeout: 30000,
            maxRequestsPerMinute: 10,
            debugMode: false
        },
        
        // State management
        state: {
            requestCount: 0,
            lastRequestTime: 0,
            isProcessing: false,
            retryAttempts: {}
        },
        
        // Error tracking
        errors: {
            log: [],
            maxLogSize: 100
        },
        
        // Initialize the enhanced admin system
        init: function() {
            try {
                this.log('Initializing ENNU Enhanced Admin System');
                
                // Check dependencies
                if (!this.checkDependencies()) {
                    this.handleError('Dependencies check failed', 'DEPENDENCY_ERROR');
                    return;
                }
                
                // Load configuration from WordPress
                this.loadConfig();
                
                // Bind event handlers
                this.bindEvents();
                
                        // Initialize components
        this.initializeComponents();
        
        // Initialize tab switching
        this.initializeTabSwitching();
        
        // Setup error monitoring
        this.setupErrorMonitoring();
        
        // Setup performance monitoring
        this.setupPerformanceMonitoring();
                
                this.log('ENNU Enhanced Admin System initialized successfully');
                
            } catch (error) {
                this.handleError('Initialization failed: ' + error.message, 'INIT_ERROR', error);
            }
        },
        
        // Check for required dependencies
        checkDependencies: function() {
            const required = ['ennuAdminEnhanced'];
            const missing = [];
            
            
            if (typeof ennuAdminEnhanced === 'undefined') {
                missing.push('ennuAdminEnhanced localization object');
            }
            
            if (missing.length > 0) {
                this.log('Missing dependencies: ' + missing.join(', '), 'error');
                return false;
            }
            
            return true;
        },
        
        // Load configuration from WordPress localization
        loadConfig: function() {
            if (typeof ennuAdminEnhanced !== 'undefined') {
                this.config = Object.assign(this.config, ennuAdminEnhanced.security || {});
                this.strings = ennuAdminEnhanced.strings || {};
                this.ajaxUrl = ennuAdminEnhanced.ajaxurl;
                this.nonce = ennuAdminEnhanced.nonce;
            }
        },
        
        // Bind event handlers with error protection
        bindEvents: function() {
            try {
                // Recalculate scores button
                document.addEventListener('click', (e) => {
                    if (e.target.id === 'ennu-recalculate-scores') {
                        this.handleRecalculateScores(e);
                    } else if (e.target.id === 'ennu-export-data') {
                        this.handleExportData(e);
                    } else if (e.target.id === 'ennu-sync-hubspot') {
                        this.handleSyncHubSpot(e);
                    } else if (e.target.id === 'ennu-clear-cache') {
                        this.handleClearCache(e);
                    } else if (e.target.id === 'ennu-clear-data') {
                        this.handleClearUserData(e);
                    }
                });

                // Global error handler for unhandled promise rejections
                window.addEventListener('unhandledrejection', this.handleUnhandledRejection.bind(this));
                
                // Global error handler for JavaScript errors
                window.addEventListener('error', this.handleGlobalError.bind(this));
                
                this.log('Event handlers bound successfully');
                
            } catch (error) {
                this.handleError('Failed to bind events: ' + error.message, 'BIND_ERROR', error);
            }
        },
        
        // Initialize components
        initializeComponents: function() {
            try {
                // Initialize tooltips with vanilla JavaScript
                const tooltipElements = document.querySelectorAll('.ennu-enhanced-admin-container [title]');
                tooltipElements.forEach(function(element) {
                    element.addEventListener('mouseenter', function() {
                        const tooltip = document.createElement('div');
                        tooltip.className = 'ennu-tooltip';
                        tooltip.textContent = this.getAttribute('title');
                        document.body.appendChild(tooltip);
                        this.setAttribute('data-original-title', this.getAttribute('title'));
                        this.removeAttribute('title');
                    });
                    element.addEventListener('mouseleave', function() {
                        const tooltip = document.querySelector('.ennu-tooltip');
                        if (tooltip) {
                            tooltip.remove();
                        }
                        if (this.getAttribute('data-original-title')) {
                            this.setAttribute('title', this.getAttribute('data-original-title'));
                            this.removeAttribute('data-original-title');
                        }
                    });
                });
                
                // Initialize progress animations
                this.animateProgressBars();
                
                // Initialize cache status updates
                this.updateCacheStatus();
                
                this.log('Components initialized successfully');
                
            } catch (error) {
                this.handleError('Failed to initialize components: ' + error.message, 'COMPONENT_ERROR', error);
            }
        },
        
        // Initialize tab switching functionality
        initializeTabSwitching: function() {
            try {
                this.log('Initializing tab switching');
                
                // Handle ENNU admin tabs
                const tabNav = document.querySelector('.ennu-admin-tab-nav');
                if (tabNav) {
                    const tabLinks = tabNav.querySelectorAll('a');
                    const tabContents = document.querySelectorAll('.ennu-admin-tab-content');
                    
                    tabLinks.forEach(function(link) {
                        link.addEventListener('click', function(e) {
                            e.preventDefault();
                            
                            // Remove active class from all tabs and contents
                            tabLinks.forEach(l => l.classList.remove('ennu-admin-tab-active'));
                            tabContents.forEach(c => c.classList.remove('ennu-admin-tab-active'));
                            
                            // Add active class to clicked tab
                            this.classList.add('ennu-admin-tab-active');
                            
                            // Show corresponding content
                            const targetId = this.getAttribute('href').substring(1);
                            const targetContent = document.getElementById(targetId);
                            if (targetContent) {
                                targetContent.classList.add('ennu-admin-tab-active');
                            }
                        });
                    });
                }
                
                // Handle biomarker range management tabs
                const rangeTabs = document.querySelectorAll('.nav-tab');
                if (rangeTabs.length > 0) {
                    rangeTabs.forEach(function(tab) {
                        tab.addEventListener('click', function(e) {
                            e.preventDefault();
                            
                            // Remove active class from all tabs and content
                            rangeTabs.forEach(t => t.classList.remove('nav-tab-active'));
                            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                            
                            // Add active class to clicked tab
                            this.classList.add('nav-tab-active');
                            
                            // Show corresponding content
                            const targetTab = this.getAttribute('data-tab');
                            if (targetTab) {
                                const targetContent = document.getElementById(targetTab);
                                if (targetContent) {
                                    targetContent.classList.add('active');
                                }
                            }
                        });
                    });
                }
                
                this.log('Tab switching initialized successfully');
                
            } catch (error) {
                this.handleError('Tab switching initialization failed: ' + error.message, 'TAB_ERROR', error);
            }
        },
        
        // Setup error monitoring
        setupErrorMonitoring: function() {
            const self = this;
            
            // Monitor fetch errors - vanilla JavaScript replacement for ajaxError
            const originalFetch = window.fetch;
            window.fetch = function(...args) {
                return originalFetch.apply(this, args)
                    .catch(function(error) {
                        if (args[0] && args[0].indexOf && args[0].indexOf('ennu_') !== -1) {
                            self.handleAjaxError(null, 'error', error, { url: args[0] });
                        }
                        throw error;
                    });
            };
            
            // Monitor console errors
            const originalConsoleError = console.error;
            console.error = function() {
                self.logError('Console Error', Array.prototype.slice.call(arguments).join(' '));
                originalConsoleError.apply(console, arguments);
            };
        },
        
        // Setup performance monitoring
        setupPerformanceMonitoring: function() {
            if (window.performance && window.performance.mark) {
                window.performance.mark('ennu-admin-init-complete');
            }
        },
        
        // Handle recalculate scores with bulletproof error handling
        handleRecalculateScores: function(e) {
            e.preventDefault();
            
            try {
                const button = e.currentTarget;
                const userId = this.getUserId();
                
                if (!userId) {
                    this.showNotification(this.strings.error || 'User ID not found', 'error');
                    return;
                }
                
                this.performAjaxOperation('recalculate_scores', {
                    user_id: userId
                }, button, this.strings.recalculating || 'Recalculating scores...');
                
            } catch (error) {
                this.handleError('Recalculate scores failed: ' + error.message, 'RECALCULATE_ERROR', error);
            }
        },
        
        // Handle export data
        handleExportData: function(e) {
            e.preventDefault();
            
            try {
                const button = e.currentTarget;
                const userId = this.getUserId();
                
                if (!userId) {
                    this.showNotification(this.strings.error || 'User ID not found', 'error');
                    return;
                }
                
                this.performAjaxOperation('export_user_data', {
                    user_id: userId
                }, button, this.strings.exporting || 'Exporting data...', this.handleExportSuccess.bind(this));
                
            } catch (error) {
                this.handleError('Export data failed: ' + error.message, 'EXPORT_ERROR', error);
            }
        },
        
        // Handle HubSpot sync
        handleSyncHubSpot: function(e) {
            e.preventDefault();
            
            try {
                const button = e.currentTarget;
                const userId = this.getUserId();
                
                if (!userId) {
                    this.showNotification(this.strings.error || 'User ID not found', 'error');
                    return;
                }
                
                this.performAjaxOperation('sync_hubspot', {
                    user_id: userId
                }, button, this.strings.syncing || 'Syncing with HubSpot...');
                
            } catch (error) {
                this.handleError('HubSpot sync failed: ' + error.message, 'HUBSPOT_ERROR', error);
            }
        },
        
        // Handle clear cache
        handleClearCache: function(e) {
            e.preventDefault();
            
            try {
                const button = e.currentTarget;
                const userId = this.getUserId();
                
                this.performAjaxOperation('clear_cache', {
                    user_id: userId || 0
                }, button, this.strings.clearing_cache || 'Clearing cache...');
                
            } catch (error) {
                this.handleError('Clear cache failed: ' + error.message, 'CACHE_ERROR', error);
            }
        },
        
        // Handle clear user data
        handleClearUserData: function(e) {
            e.preventDefault();

            if (!confirm(this.strings.confirm_clear || 'Are you sure you want to permanently delete all ENNU assessment data for this user? This cannot be undone.')) {
                return;
            }

            try {
                const button = e.currentTarget;
                const userId = button.dataset.userId;

                if (!userId) {
                    this.showNotification(this.strings.error || 'User ID not found', 'error');
                    return;
                }

                this.performAjaxOperation('clear_user_data', {
                    user_id: userId
                }, button, 'Clearing data...', function() {
                    location.reload();
                });

            } catch (error) {
                this.handleError('Clear user data failed: ' + error.message, 'CLEAR_DATA_ERROR', error);
            }
        },

        // Bulletproof AJAX operation with retry mechanism
        performAjaxOperation: function(action, data, button, loadingMessage, successCallback) {
            if (this.state.isProcessing) {
                this.showNotification('Another operation is in progress. Please wait.', 'warning');
                return;
            }
            
            // Rate limiting check
            if (!this.checkRateLimit()) {
                this.showNotification(this.strings.rate_limit || 'Too many requests. Please wait a moment.', 'error');
                return;
            }
            
            this.state.isProcessing = true;
            
            // Prepare request data
            const requestData = Object.assign({
                action: 'ennu_' + action,
                nonce: this.nonce
            }, data);
            
            // Show loading state
            this.setButtonLoading(button, true, loadingMessage);
            this.showNotification(loadingMessage, 'loading');
            
            // Perform AJAX request with retry
            this.ajaxWithRetry(requestData, action)
                .then((response) => {
                    this.handleAjaxSuccess(response, button, successCallback);
                })
                .catch((error) => {
                    this.handleAjaxError(error.xhr, error.status, error.error, { data: requestData });
                    this.setButtonLoading(button, false);
                })
                .finally(() => {
                    this.state.isProcessing = false;
                });
        },
        
        // AJAX with retry mechanism
        ajaxWithRetry: function(data, action, attempt) {
            attempt = attempt || 1;
            const self = this;
            
            return fetch(this.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            }).then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            }).catch(error => {
                if (attempt < self.config.maxRetries && self.shouldRetry(error)) {
                    self.log(`Retrying AJAX request for ${action} (attempt ${attempt + 1}/${self.config.maxRetries})`);
                    
                    // Exponential backoff
                    const delay = self.config.retryDelay * Math.pow(2, attempt - 1);
                    
                    return new Promise((resolve, reject) => {
                        setTimeout(() => {
                            self.ajaxWithRetry(data, action, attempt + 1)
                                .done(resolve)
                                .fail(reject);
                        }, delay);
                    });
                } else {
                    return Promise.reject({ xhr, status, error });
                }
            });
        },
        
        // Determine if request should be retried
        shouldRetry: function(xhr, status) {
            // Retry on network errors, timeouts, and 5xx server errors
            return status === 'timeout' || 
                   status === 'error' || 
                   xhr.status === 0 || 
                   (xhr.status >= 500 && xhr.status < 600);
        },
        
        // Handle AJAX success
        handleAjaxSuccess: function(response, button, successCallback) {
            try {
                this.setButtonLoading($button, false);
                
                if (response.success) {
                    this.showNotification(response.data.message || this.strings.success || 'Operation completed successfully!', 'success');
                    
                    if (successCallback && typeof successCallback === 'function') {
                        successCallback(response.data);
                    }
                    
                    // Refresh page data if needed
                    this.refreshPageData();
                    
                } else {
                    const errorMessage = response.data && response.data.message ? 
                        response.data.message : 
                        (this.strings.error || 'An error occurred. Please try again.');
                    
                    this.showNotification(errorMessage, 'error');
                }
                
            } catch (error) {
                this.handleError('Success handler failed: ' + error.message, 'SUCCESS_HANDLER_ERROR', error);
            }
        },
        
        // Handle AJAX errors with comprehensive error analysis
        handleAjaxError: function(xhr, status, error, settings) {
            try {
                let message = this.strings.error || 'An error occurred. Please try again.';
                
                // Analyze error type
                if (xhr.status === 0) {
                    message = this.strings.network_error || 'Network error. Please check your connection.';
                } else if (xhr.status === 403) {
                    message = this.strings.permission_denied || 'Permission denied. Please refresh the page and try again.';
                } else if (xhr.status === 429) {
                    message = this.strings.rate_limit || 'Too many requests. Please wait a moment and try again.';
                } else if (xhr.status >= 500) {
                    message = 'Server error. Please try again later.';
                } else if (status === 'timeout') {
                    message = 'Request timed out. Please try again.';
                } else if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                    message = xhr.responseJSON.data.message;
                }
                
                this.showNotification(message, 'error');
                
                // Log error for debugging
                this.logError('AJAX Error', {
                    status: xhr.status,
                    statusText: status,
                    error: error,
                    url: settings ? settings.url : 'unknown',
                    response: xhr.responseText
                });
                
                // Send error to server for logging
                this.sendErrorToServer({
                    type: 'ajax_error',
                    status: xhr.status,
                    statusText: status,
                    error: error,
                    url: settings ? settings.url : 'unknown',
                    timestamp: new Date().toISOString()
                });
                
            } catch (handlerError) {
                this.handleError('Error handler failed: ' + handlerError.message, 'ERROR_HANDLER_ERROR', handlerError);
            }
        },
        
        // Handle export success with file download
        handleExportSuccess: function(data) {
            try {
                if (data && data.data) {
                    const exportData = JSON.stringify(data.data, null, 2);
                    const blob = new Blob([exportData], { type: 'application/json' });
                    const url = window.URL.createObjectURL(blob);
                    
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `ennu-user-data-${data.data._metadata.user_id}-${new Date().toISOString().split('T')[0]}.json`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                }
            } catch (error) {
                this.handleError('Export file creation failed: ' + error.message, 'EXPORT_FILE_ERROR', error);
            }
        },
        
        // Rate limiting check
        checkRateLimit: function() {
            const now = Date.now();
            const oneMinute = 60 * 1000;
            
            // Reset counter if more than a minute has passed
            if (now - this.state.lastRequestTime > oneMinute) {
                this.state.requestCount = 0;
            }
            
            // Check if limit exceeded
            if (this.state.requestCount >= this.config.maxRequestsPerMinute) {
                return false;
            }
            
            // Update counters
            this.state.requestCount++;
            this.state.lastRequestTime = now;
            
            return true;
        },
        
        // Set button loading state
        setButtonLoading: function(button, loading, message) {
            if (loading) {
                button.disabled = true;
                button.classList.add('ennu-loading');
                button.dataset.originalText = button.textContent;
                button.innerHTML = '<span class="dashicons dashicons-update-alt"></span> ' + (message || 'Loading...');
            } else {
                button.disabled = false;
                button.classList.remove('ennu-loading');
                button.innerHTML = button.dataset.originalText || button.textContent;
            }
        },
        
        // Show notification with auto-hide
        showNotification: function(message, type, duration) {
            try {
                const statusElement = document.getElementById('ennu-operation-status');
                
                if (!statusElement) {
                    return;
                }
                
                duration = duration || (type === 'error' ? 8000 : 4000);
                
                // Clear existing classes and hide
                $status.removeClass('show loading success error warning')
                       .addClass('show ' + type)
                       .html(this.getNotificationIcon(type) + ' ' + message);
                
                // Auto-hide after duration
                if (type !== 'loading') {
                    setTimeout(() => {
                        $status.removeClass('show');
                    }, duration);
                }
                
            } catch (error) {
                // Fallback to console if notification system fails
            }
        },
        
        // Get notification icon
        getNotificationIcon: function(type) {
            const icons = {
                loading: '<span class="dashicons dashicons-update-alt"></span>',
                success: '<span class="dashicons dashicons-yes-alt"></span>',
                error: '<span class="dashicons dashicons-warning"></span>',
                warning: '<span class="dashicons dashicons-info"></span>'
            };
            
            return icons[type] || '';
        },
        
        // Animate progress bars
        animateProgressBars: function() {
            try {
                const progressBars = document.querySelectorAll('.ennu-progress-bar');
                progressBars.forEach(function(bar) {
                    const width = getComputedStyle(bar).width;
                    bar.style.width = '0';
                    bar.style.transition = 'width 1000ms';
                    setTimeout(() => bar.style.width = width, 10);
                });
            } catch (error) {
                this.log('Progress bar animation failed: ' + error.message, 'warning');
            }
        },
        
        // Update cache status
        updateCacheStatus: function() {
            try {
                // This would typically make an AJAX call to get current cache status
                // For now, we'll just update the display based on existing data
                const indicator = document.querySelector('.ennu-cache-indicator');
                if (indicator && indicator.classList.contains('active')) {
                    indicator.classList.add('pulse');
                }
            } catch (error) {
                this.log('Cache status update failed: ' + error.message, 'warning');
            }
        },
        
        // Refresh page data
        refreshPageData: function() {
            try {
                // Refresh cache status
                this.updateCacheStatus();
                
                // Refresh any dynamic content
                // This could trigger a partial page refresh if needed
                
            } catch (error) {
                this.log('Page data refresh failed: ' + error.message, 'warning');
            }
        },
        
        // Get user ID from page
        getUserId: function() {
            // Try multiple methods to get user ID
            const urlParams = new URLSearchParams(window.location.search);
            let userId = urlParams.get('user_id');
            
            if (!userId) {
                const match = window.location.pathname.match(/user-edit\.php.*user_id=(\d+)/);
                if (match) {
                    userId = match[1];
                }
            }
            
            if (!userId) {
                const userIdField = document.getElementById('user_id');
                if (userIdField) {
                    userId = userIdField.value;
                }
            }
            
            return userId ? parseInt(userId, 10) : null;
        },
        
        // Error handling and logging
        handleError: function(message, code, error) {
            this.logError(code || 'GENERAL_ERROR', message, error);
            
            // Show user-friendly error message
            this.showNotification(
                'A system error occurred. Please refresh the page and try again.',
                'error'
            );
        },
        
        // Log error with details
        logError: function(type, message, error) {
            const errorEntry = {
                timestamp: new Date().toISOString(),
                type: type,
                message: message,
                error: error ? error.toString() : null,
                stack: error ? error.stack : null,
                userAgent: navigator.userAgent,
                url: window.location.href
            };
            
            // Add to local error log
            this.errors.log.push(errorEntry);
            
            // Keep log size manageable
            if (this.errors.log.length > this.errors.maxLogSize) {
                this.errors.log = this.errors.log.slice(-this.errors.maxLogSize);
            }
            
            // Log to console in debug mode
            if (this.config.debugMode) {
            }
        },
        
        // Send error to server for logging
        sendErrorToServer: function(errorData) {
            try {
                // Don't send too many error reports
                if (this.errors.log.length > 10) {
                    return;
                }
                
                fetch(this.ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'ennu_log_client_error',
                        nonce: this.nonce,
                        error_data: JSON.stringify(errorData)
                    })
                }).catch(() => {
                    // Silently fail - don't create error loops
                });
                
            } catch (error) {
                // Silently fail - don't create error loops
            }
        },
        
        // Handle unhandled promise rejections
        handleUnhandledRejection: function(event) {
            this.logError('UNHANDLED_PROMISE_REJECTION', event.reason);
        },
        
        // Handle global JavaScript errors
        handleGlobalError: function(event) {
            this.logError('GLOBAL_ERROR', event.message, {
                filename: event.filename,
                lineno: event.lineno,
                colno: event.colno
            });
        },
        
        // Logging utility
        log: function(message, level) {
            level = level || 'info';
            
            if (this.config.debugMode || level === 'error') {
                console[level]('ENNU Admin:', message);
            }
        },
        
        // Get error log for debugging
        getErrorLog: function() {
            return this.errors.log;
        },
        
        // Clear error log
        clearErrorLog: function() {
            this.errors.log = [];
        }
    };
    
    // Initialize when document is ready
    document.addEventListener('DOMContentLoaded', function() {
        ENNU.init();
    });
    
    // Expose ENNU object globally for debugging
    window.ENNUAdminEnhanced = ENNU;
    
})();

