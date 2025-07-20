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
    
    console.log('🚀 ENNU Admin Enhanced: Script loaded successfully!');
    console.log('🚀 ENNU Admin Enhanced: Version 62.2.9');
    console.log('🚀 ENNU Admin Enhanced: jQuery available:', typeof jQuery !== 'undefined');
    console.log('🚀 ENNU Admin Enhanced: ennuAdmin object:', typeof ennuAdmin !== 'undefined' ? ennuAdmin : 'NOT FOUND');
    
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
        console.log('🚀 ENNU Admin Enhanced: Global initialization called');
        initializeEnnuAdmin();
    };
    
    // Simple initialization
    function initializeEnnuAdmin() {
        console.log('🚀 ENNU Admin Enhanced: Initializing...');
        
        // Simple tab initialization
        initSimpleTabs();
        
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
