/**
 * ENNU Life Enhanced Admin JavaScript
 * Simplified and bulletproof tab navigation
 *
 * @package ENNU_Life
 * @version 62.2.9
 * @author The World's Greatest WordPress Developer
 */

(function() {
    'use strict';
    
    
    // Add a visible indicator to the page
    if (document.body) {
        const indicator = document.createElement('div');
        indicator.style.cssText = 'position: fixed; top: 10px; right: 10px; background: #0073aa; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; z-index: 9999;';
        indicator.textContent = 'ENNU Admin Loaded v62.2.9';
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
        initializeEnnuAdmin();
    };
    
    // Simple initialization
    function initializeEnnuAdmin() {
        
        // Simple tab initialization
        initSimpleTabs();
        
    }
    
    function initSimpleTabs() {
        
        // Look for tab containers
        const tabContainers = document.querySelectorAll('.ennu-admin-tabs');
        
        if (tabContainers.length === 0) {
            return;
        }
        
        // Initialize each container
        tabContainers.forEach(function(container, index) {
            initSingleTabContainer(container);
        });
    }
    
    function initSingleTabContainer(container) {
        // Find all tab links and contents
        const tabLinks = container.querySelectorAll('.ennu-admin-tab-nav a');
        const tabContents = container.querySelectorAll('.ennu-admin-tab-content');
        
        
        if (tabLinks.length === 0 || tabContents.length === 0) {
            return;
        }
        
        
        // Add click handlers to all tab links
        tabLinks.forEach(function(link) {
            
            link.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                
                const targetId = this.getAttribute('href');
                
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
                } else {
                }
                
                // Add active class to clicked link
                this.classList.add('ennu-admin-tab-active');
            });
            
            // Make sure link is clickable
            link.style.cursor = 'pointer';
            link.style.userSelect = 'none';
        });
        
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeEnnuAdmin);
    } else {
        initializeEnnuAdmin();
    }
    
    // Also try on window load
    window.addEventListener('load', function() {
        setTimeout(initializeEnnuAdmin, 100);
    });
    
    // And try after delays
    setTimeout(initializeEnnuAdmin, 500);
    setTimeout(initializeEnnuAdmin, 1000);
    setTimeout(initializeEnnuAdmin, 2000);
    
})();    