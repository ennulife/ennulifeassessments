/**
 * ENNU Life Enhanced Admin JavaScript
 * Simplified and bulletproof tab navigation
 *
 * @package ENNU_Life
 * @version 62.1.71
 * @author The World's Greatest WordPress Developer
 */

(function() {
    'use strict';
    
    console.log('🚀 ENNU Admin Enhanced: Script loaded successfully!');
    console.log('🚀 ENNU Admin Enhanced: Version 62.1.71');
    console.log('🚀 ENNU Admin Enhanced: jQuery available:', typeof jQuery !== 'undefined');
    console.log('🚀 ENNU Admin Enhanced: ennuAdmin object:', typeof ennuAdmin !== 'undefined' ? ennuAdmin : 'NOT FOUND');
    
    // Add a visible indicator to the page
    if (document.body) {
        const indicator = document.createElement('div');
        indicator.style.cssText = 'position: fixed; top: 10px; right: 10px; background: #0073aa; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; z-index: 9999;';
        indicator.textContent = 'ENNU Admin Loaded v62.1.71';
        document.body.appendChild(indicator);
        
        // Remove after 5 seconds
        setTimeout(function() {
            if (indicator.parentNode) {
                indicator.parentNode.removeChild(indicator);
            }
        }, 5000);
    }
    
    // Global initialization function for external access
    window.initializeEnnuAdmin = function() {
        console.log('🚀 ENNU Admin Enhanced: Global initialization called');
        initializeEnnuAdmin();
    };
    
    // Simple initialization
    function initializeEnnuAdmin() {
        console.log('🚀 ENNU Admin Enhanced: Initializing...');
        
        // Simple tab initialization
        initSimpleTabs();
        
        // Initialize symptom management
        initSymptomManagement();
        
        console.log('🚀 ENNU Admin Enhanced: Initialization complete');
    }
    
    function initSimpleTabs() {
        console.log('🚀 ENNU Admin Enhanced: Looking for tab containers...');
        
        // Look for tab containers
        const tabContainers = document.querySelectorAll('.ennu-admin-tabs');
        console.log('🚀 ENNU Admin Enhanced: Found', tabContainers.length, 'tab container(s)');
        
        if (tabContainers.length === 0) {
            console.log('❌ ENNU Admin Enhanced: No tab containers found');
            console.log('🔍 ENNU Admin Enhanced: Available elements with "ennu" in class:', document.querySelectorAll('[class*="ennu"]'));
            return;
        }
        
        // Initialize each container
        tabContainers.forEach(function(container, index) {
            console.log('🚀 ENNU Admin Enhanced: Initializing tab container', index + 1);
            initSingleTabContainer(container);
        });
    }
    
    function initSingleTabContainer(container) {
        // Find all tab links and contents
        const tabLinks = container.querySelectorAll('.ennu-admin-tab-nav a');
        const tabContents = container.querySelectorAll('.ennu-admin-tab-content');
        
        console.log('🚀 ENNU Admin Enhanced: Found', tabLinks.length, 'tab links and', tabContents.length, 'tab contents');
        
        if (tabLinks.length === 0 || tabContents.length === 0) {
            console.log('❌ ENNU Admin Enhanced: Insufficient tabs or contents found');
            return;
        }
        
        // Log all tab links
        tabLinks.forEach(function(link, index) {
            console.log('🚀 ENNU Admin Enhanced: Tab link', index + 1, ':', link.getAttribute('href'), '-', link.textContent.trim());
        });
        
        // Log all tab contents
        tabContents.forEach(function(content, index) {
            console.log('🚀 ENNU Admin Enhanced: Tab content', index + 1, ':', content.id);
        });
        
        // Add click handlers to all tab links
        tabLinks.forEach(function(link) {
            console.log('🚀 ENNU Admin Enhanced: Adding click handler to:', link.getAttribute('href'));
            
            link.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                
                const targetId = this.getAttribute('href');
                console.log('🚀 ENNU Admin Enhanced: Tab clicked:', targetId);
                
                // Hide all tab contents
                tabContents.forEach(function(content) {
                    content.style.display = 'none';
                    content.classList.remove('ennu-admin-tab-active');
                });
                
                // Remove active class from all links
                tabLinks.forEach(function(link) {
                    link.classList.remove('ennu-admin-tab-active');
                });
                
                // Show target content
                const targetContent = document.querySelector(targetId);
                if (targetContent) {
                    targetContent.style.display = 'block';
                    targetContent.classList.add('ennu-admin-tab-active');
                    console.log('✅ ENNU Admin Enhanced: Successfully switched to tab:', targetId);
                } else {
                    console.error('❌ ENNU Admin Enhanced: Target content not found:', targetId);
                }
                
                // Add active class to clicked link
                this.classList.add('ennu-admin-tab-active');
            });
            
            // Make sure link is clickable
            link.style.cursor = 'pointer';
            link.style.userSelect = 'none';
        });
        
        console.log('✅ ENNU Admin Enhanced: Tab container initialized successfully');
    }
    
    function initSymptomManagement() {
        console.log('🚀 ENNU Admin Enhanced: Initializing symptom management...');
        
        // Prevent multiple initializations
        if (window.ennuSymptomManagementInitialized) {
            console.log('🚀 ENNU Admin Enhanced: Symptom management already initialized, skipping...');
            return;
        }
        
        // Set initialization flag
        window.ennuSymptomManagementInitialized = true;
        
        // Recalculate centralized symptoms button
        const recalculateButton = document.getElementById('ennu-recalculate-centralized-symptoms');
        if (recalculateButton) {
            console.log('🚀 ENNU Admin Enhanced: Found recalculate centralized symptoms button');
            
            // Remove any existing listeners by cloning the button
            const newRecalculateButton = recalculateButton.cloneNode(true);
            recalculateButton.parentNode.replaceChild(newRecalculateButton, recalculateButton);
            
            newRecalculateButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('🚀 ENNU Admin Enhanced: Recalculate button clicked');
                const userId = this.getAttribute('data-user-id');
                recalculateCentralizedSymptoms(userId, this);
            });
        }
        
        // Mark as initialized
        window.ennuSymptomManagementInitialized = true;
        console.log('✅ ENNU Admin Enhanced: Symptom management initialized');
    }
    
    function recalculateCentralizedSymptoms(userId, button) {
        console.log('🚀 ENNU Admin Enhanced: recalculateCentralizedSymptoms called with userId:', userId);
        
        if (!confirm('Are you sure you want to recalculate centralized symptoms for this user? This will aggregate all existing assessment symptoms.')) {
            console.log('🚀 ENNU Admin Enhanced: User cancelled recalculate operation');
            return;
        }
        
        console.log('🚀 ENNU Admin Enhanced: Starting recalculate operation...');
        
        button.classList.add('loading');
        button.textContent = 'Recalculating...';
        
        // Check if dependencies are available
        if (typeof jQuery === 'undefined') {
            console.error('❌ ENNU Admin Enhanced: jQuery not available');
            button.classList.remove('loading');
            button.textContent = 'Recalculate Centralized Symptoms';
            showNotification('Error: jQuery not available', 'error');
            return;
        }
        
        if (typeof ennuAdmin === 'undefined') {
            console.error('❌ ENNU Admin Enhanced: ennuAdmin object not available');
            button.classList.remove('loading');
            button.textContent = 'Recalculate Centralized Symptoms';
            showNotification('Error: Admin configuration not available', 'error');
            return;
        }
        
        console.log('🚀 ENNU Admin Enhanced: Making AJAX call to:', ennuAdmin.ajax_url);
        console.log('🚀 ENNU Admin Enhanced: Action:', 'ennu_populate_centralized_symptoms');
        console.log('🚀 ENNU Admin Enhanced: User ID:', userId);
        console.log('🚀 ENNU Admin Enhanced: Nonce available:', !!ennuAdmin.nonce);
        
        jQuery.ajax({
            url: ennuAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'ennu_populate_centralized_symptoms',
                user_id: userId,
                nonce: ennuAdmin.nonce
            },
            success: function(response) {
                console.log('🚀 ENNU Admin Enhanced: AJAX success response:', response);
                button.classList.remove('loading');
                button.textContent = 'Recalculate Centralized Symptoms';
                
                if (response.success) {
                    showNotification('Centralized symptoms recalculated successfully!', 'success');
                    console.log('🚀 ENNU Admin Enhanced: Recalculate operation successful, reloading page...');
                    // Reload the page to show updated data
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    const errorMessage = response.data ? response.data.message : 'Unknown error';
                    console.error('❌ ENNU Admin Enhanced: Recalculate operation failed:', errorMessage);
                    showNotification('Failed to recalculate centralized symptoms: ' + errorMessage, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ ENNU Admin Enhanced: AJAX error:', status, error);
                console.error('❌ ENNU Admin Enhanced: Response text:', xhr.responseText);
                button.classList.remove('loading');
                button.textContent = 'Recalculate Centralized Symptoms';
                showNotification('Failed to recalculate centralized symptoms. Please try again.', 'error');
            }
        });
    }
    
    function showNotification(message, type) {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.symptom-notification');
        existingNotifications.forEach(function(notification) {
            notification.remove();
        });
        
        // Create new notification
        const notification = document.createElement('div');
        notification.className = 'symptom-notification ' + type;
        notification.textContent = message;
        
        // Find the symptoms admin container
        const symptomsContainer = document.querySelector('.ennu-centralized-symptoms-admin');
        if (symptomsContainer) {
            symptomsContainer.insertBefore(notification, symptomsContainer.firstChild);
        } else {
            // Fallback to body
            document.body.insertBefore(notification, document.body.firstChild);
        }
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeEnnuAdmin);
    } else {
        initializeEnnuAdmin();
    }
    
    // Also try on window load
    window.addEventListener('load', function() {
        console.log('🚀 ENNU Admin Enhanced: Window load event - reinitializing');
        setTimeout(initializeEnnuAdmin, 100);
    });
    
    // And try after delays
    setTimeout(initializeEnnuAdmin, 500);
    setTimeout(initializeEnnuAdmin, 1000);
    setTimeout(initializeEnnuAdmin, 2000);
    
})(); 