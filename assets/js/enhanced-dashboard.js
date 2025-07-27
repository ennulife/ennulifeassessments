/**
 * ENNU Enhanced Dashboard JavaScript
 * 
 * JavaScript functionality for enhanced dashboard features including profile completeness
 * 
 * @package ENNU_Life_Assessments
 * @version 64.3.30
 */

(function($) {
    'use strict';

    // ENNU Enhanced Dashboard namespace
    window.ENNUEnhancedDashboard = window.ENNUEnhancedDashboard || {};

    /**
     * Initialize enhanced dashboard functionality
     */
    ENNUEnhancedDashboard.init = function() {
        console.log('ENNU Enhanced Dashboard: Initializing...');
        
        // Initialize profile completeness functionality
        ENNUEnhancedDashboard.initProfileCompleteness();
        
        // Initialize AJAX handlers
        ENNUEnhancedDashboard.initAjaxHandlers();
        
        // Initialize interactive elements
        ENNUEnhancedDashboard.initInteractiveElements();
        
        console.log('ENNU Enhanced Dashboard: Initialized successfully');
    };

    /**
     * Initialize profile completeness functionality
     */
    ENNUEnhancedDashboard.initProfileCompleteness = function() {
        // Add click handlers for recommendation items
        $(document).on('click', '.recommendation-item', function() {
            const actionUrl = $(this).data('action-url');
            if (actionUrl && actionUrl !== '#') {
                window.location.href = actionUrl;
            }
        });

        // Add hover effects for progress elements
        $('.progress-circle').hover(
            function() {
                $(this).addClass('hover');
            },
            function() {
                $(this).removeClass('hover');
            }
        );

        // Add click handlers for section items
        $(document).on('click', '.section-item', function() {
            const sectionKey = $(this).data('section-key');
            if (sectionKey) {
                ENNUEnhancedDashboard.showSectionDetails(sectionKey);
            }
        });
    };

    /**
     * Initialize AJAX handlers
     */
    ENNUEnhancedDashboard.initAjaxHandlers = function() {
        // Refresh profile completeness data
        $(document).on('click', '.refresh-completeness', function(e) {
            e.preventDefault();
            ENNUEnhancedDashboard.refreshProfileCompleteness();
        });

        // Update profile section
        $(document).on('click', '.update-profile-section', function(e) {
            e.preventDefault();
            const section = $(this).data('section');
            const field = $(this).data('field');
            const value = $(this).data('value');
            
            ENNUEnhancedDashboard.updateProfileSection(section, field, value);
        });
    };

    /**
     * Initialize interactive elements
     */
    ENNUEnhancedDashboard.initInteractiveElements = function() {
        // Add tooltips for accuracy badges
        $('.accuracy-badge').each(function() {
            const accuracyLevel = $(this).data('accuracy-level');
            const tooltipText = ENNUEnhancedDashboard.getAccuracyTooltip(accuracyLevel);
            
            $(this).attr('title', tooltipText);
        });

        // Add progress animations
        $('.section-progress-fill').each(function() {
            const percentage = $(this).data('percentage');
            $(this).css('width', '0%');
            
            setTimeout(() => {
                $(this).css('width', percentage + '%');
            }, 500);
        });
    };

    /**
     * Refresh profile completeness data
     */
    ENNUEnhancedDashboard.refreshProfileCompleteness = function() {
        const $widget = $('.profile-completeness-widget');
        
        if ($widget.length === 0) {
            console.warn('ENNU Enhanced Dashboard: Profile completeness widget not found');
            return;
        }

        // Show loading state
        $widget.addClass('loading');

        $.ajax({
            url: ennuEnhancedDashboard.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ennu_get_profile_completeness',
                nonce: ennuEnhancedDashboard.nonce,
                user_id: ennuEnhancedDashboard.userId
            },
            success: function(response) {
                if (response.success) {
                    // Update the widget content
                    $widget.html(response.data.completeness);
                    
                    // Reinitialize interactive elements
                    ENNUEnhancedDashboard.initInteractiveElements();
                    
                    // Show success message
                    ENNUEnhancedDashboard.showNotification('Profile completeness updated successfully', 'success');
                } else {
                    console.error('ENNU Enhanced Dashboard: Failed to refresh profile completeness', response.data);
                    ENNUEnhancedDashboard.showNotification('Failed to update profile completeness', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('ENNU Enhanced Dashboard: AJAX error', error);
                ENNUEnhancedDashboard.showNotification('Network error occurred', 'error');
            },
            complete: function() {
                // Remove loading state
                $widget.removeClass('loading');
            }
        });
    };

    /**
     * Update profile section
     */
    ENNUEnhancedDashboard.updateProfileSection = function(section, field, value) {
        $.ajax({
            url: ennuEnhancedDashboard.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ennu_update_profile_section',
                nonce: ennuEnhancedDashboard.nonce,
                user_id: ennuEnhancedDashboard.userId,
                section: section,
                field: field,
                value: value
            },
            success: function(response) {
                if (response.success) {
                    // Refresh the profile completeness widget
                    ENNUEnhancedDashboard.refreshProfileCompleteness();
                    
                    // Show success message
                    ENNUEnhancedDashboard.showNotification('Profile section updated successfully', 'success');
                } else {
                    console.error('ENNU Enhanced Dashboard: Failed to update profile section', response.data);
                    ENNUEnhancedDashboard.showNotification('Failed to update profile section', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('ENNU Enhanced Dashboard: AJAX error', error);
                ENNUEnhancedDashboard.showNotification('Network error occurred', 'error');
            }
        });
    };

    /**
     * Show section details
     */
    ENNUEnhancedDashboard.showSectionDetails = function(sectionKey) {
        const sectionNames = {
            'basic_demographics': 'Basic Information',
            'health_goals': 'Health Goals',
            'assessments_completed': 'Health Assessments',
            'symptoms_data': 'Symptom Tracking',
            'biomarkers_data': 'Lab Results'
        };

        const sectionName = sectionNames[sectionKey] || sectionKey.replace(/_/g, ' ');
        
        // Create modal or expand section details
        const modal = $('<div class="ennu-modal">')
            .append($('<div class="ennu-modal-content">')
                .append($('<h3>').text(sectionName + ' Details'))
                .append($('<div class="section-details-content">').text('Loading...'))
                .append($('<button class="ennu-modal-close">').text('Close'))
            );

        $('body').append(modal);
        
        // Load section details via AJAX
        ENNUEnhancedDashboard.loadSectionDetails(sectionKey, modal);
        
        // Handle close button
        modal.find('.ennu-modal-close').on('click', function() {
            modal.remove();
        });
    };

    /**
     * Load section details
     */
    ENNUEnhancedDashboard.loadSectionDetails = function(sectionKey, modal) {
        $.ajax({
            url: ennuEnhancedDashboard.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ennu_get_section_details',
                nonce: ennuEnhancedDashboard.nonce,
                user_id: ennuEnhancedDashboard.userId,
                section: sectionKey
            },
            success: function(response) {
                if (response.success) {
                    modal.find('.section-details-content').html(response.data.html);
                } else {
                    modal.find('.section-details-content').html('<p>Failed to load section details</p>');
                }
            },
            error: function() {
                modal.find('.section-details-content').html('<p>Network error occurred</p>');
            }
        });
    };

    /**
     * Get accuracy tooltip text
     */
    ENNUEnhancedDashboard.getAccuracyTooltip = function(accuracyLevel) {
        const tooltips = {
            'excellent': 'Your data is highly accurate and complete. All key health information is available for precise recommendations.',
            'high': 'Your data accuracy is very good. Most health information is available for accurate recommendations.',
            'medium': 'Your data accuracy is good. Most health information is available, some areas could be improved.',
            'moderate': 'Your data accuracy is moderate. Basic health information is available, additional data would improve recommendations.',
            'low': 'Your data accuracy needs improvement. Limited health information available, completing your profile will significantly improve recommendations.'
        };

        return tooltips[accuracyLevel] || 'Data accuracy information not available';
    };

    /**
     * Show notification
     */
    ENNUEnhancedDashboard.showNotification = function(message, type) {
        const notification = $('<div class="ennu-notification ennu-notification-' + type + '">')
            .text(message)
            .append($('<button class="ennu-notification-close">').text('×'));

        $('body').append(notification);

        // Auto-remove after 5 seconds
        setTimeout(function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);

        // Handle close button
        notification.find('.ennu-notification-close').on('click', function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        });
    };

    /**
     * Utility function to format percentage
     */
    ENNUEnhancedDashboard.formatPercentage = function(value) {
        return Math.round(value) + '%';
    };

    /**
     * Utility function to get color for percentage
     */
    ENNUEnhancedDashboard.getPercentageColor = function(percentage) {
        if (percentage >= 80) return '#10b981';
        if (percentage >= 60) return '#f59e0b';
        if (percentage >= 40) return '#fd7e14';
        return '#dc3545';
    };

    // Initialize when document is ready
    $(document).ready(function() {
        // Check if we're on a page with the user dashboard
        if ($('.profile-completeness-widget').length > 0) {
            ENNUEnhancedDashboard.init();
        }
    });

    // Export for global access
    window.ENNUEnhancedDashboard = ENNUEnhancedDashboard;

})(jQuery); 