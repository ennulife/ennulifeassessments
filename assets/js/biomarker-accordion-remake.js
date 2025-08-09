/**
 * ENNU Biomarker Accordion Complete Remake
 * Clean implementation of panel and biomarker accordion effects
 * Version: 1.0.0
 */

(function() {
    'use strict';
    
    console.log('[Accordion Remake] Initializing...');
    
    // Remove any existing onclick attributes and set up clean event delegation
    function initializeAccordions() {
        
        // Remove all onclick attributes first
        document.querySelectorAll('.biomarker-panel-header[onclick]').forEach(el => {
            el.removeAttribute('onclick');
            el.style.cursor = 'pointer';
        });
        
        document.querySelectorAll('.biomarker-list-item[onclick]').forEach(el => {
            el.removeAttribute('onclick');
            el.style.cursor = 'pointer';
        });
        
        // Set up event delegation for panels
        document.addEventListener('click', handlePanelClick);
        
        // Set up event delegation for biomarkers
        document.addEventListener('click', handleBiomarkerClick);
        
        console.log('[Accordion Remake] Event listeners attached');
    }
    
    // Handle panel header clicks
    function handlePanelClick(e) {
        const header = e.target.closest('.biomarker-panel-header');
        if (!header) return;
        
        // Don't process if clicking on a biomarker item
        if (e.target.closest('.biomarker-list-item')) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        // Find the panel content (next sibling)
        const panel = header.nextElementSibling;
        if (!panel || !panel.classList.contains('biomarker-panel-content')) {
            console.error('[Accordion Remake] Panel content not found for header:', header);
            return;
        }
        
        // Toggle the panel
        const isHidden = window.getComputedStyle(panel).display === 'none';
        
        if (isHidden) {
            // Show panel
            panel.style.display = 'block';
            header.classList.add('expanded');
            
            // Update icon if exists
            const icon = header.querySelector('.panel-expand-icon');
            if (icon) icon.textContent = '▼';
            
            console.log('[Accordion Remake] Panel expanded:', panel.id);
        } else {
            // Hide panel
            panel.style.display = 'none';
            header.classList.remove('expanded');
            
            // Update icon if exists
            const icon = header.querySelector('.panel-expand-icon');
            if (icon) icon.textContent = '▶';
            
            console.log('[Accordion Remake] Panel collapsed:', panel.id);
        }
    }
    
    // Handle biomarker item clicks
    function handleBiomarkerClick(e) {
        const item = e.target.closest('.biomarker-list-item');
        if (!item) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        // Find the measurement container (next sibling)
        const container = item.nextElementSibling;
        if (!container || !container.classList.contains('biomarker-measurement-container')) {
            console.log('[Accordion Remake] No measurement container found for:', item);
            return;
        }
        
        // Toggle the measurements
        const isHidden = window.getComputedStyle(container).display === 'none';
        
        if (isHidden) {
            // Show measurements
            container.style.display = 'block';
            item.classList.add('expanded');
            
            // Update icon if exists
            const icon = item.querySelector('.biomarker-list-expand');
            if (icon) icon.textContent = '▼';
            
            console.log('[Accordion Remake] Biomarker expanded');
        } else {
            // Hide measurements
            container.style.display = 'none';
            item.classList.remove('expanded');
            
            // Update icon if exists
            const icon = item.querySelector('.biomarker-list-expand');
            if (icon) icon.textContent = '▶';
            
            console.log('[Accordion Remake] Biomarker collapsed');
        }
    }
    
    // Add CSS for visual feedback
    function addStyles() {
        if (document.getElementById('accordion-remake-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'accordion-remake-styles';
        style.textContent = `
            /* Panel headers */
            .biomarker-panel-header {
                cursor: pointer !important;
                user-select: none;
                transition: background-color 0.2s ease;
            }
            
            .biomarker-panel-header:hover {
                background-color: rgba(0, 0, 0, 0.02);
            }
            
            .biomarker-panel-header.expanded {
                background-color: rgba(0, 0, 0, 0.03);
            }
            
            /* Biomarker items */
            .biomarker-list-item {
                cursor: pointer !important;
                user-select: none;
                transition: background-color 0.2s ease;
            }
            
            .biomarker-list-item:hover {
                background-color: rgba(0, 0, 0, 0.02);
            }
            
            .biomarker-list-item.expanded {
                background-color: rgba(0, 0, 0, 0.03);
                font-weight: 600;
            }
            
            /* Smooth transitions */
            .biomarker-panel-content {
                transition: none; /* Instant for now */
            }
            
            .biomarker-measurement-container {
                transition: none; /* Instant for now */
            }
            
            /* Icons */
            .panel-expand-icon,
            .biomarker-list-expand {
                display: inline-block;
                width: 20px;
                text-align: center;
                transition: transform 0.2s ease;
            }
            
            .expanded .panel-expand-icon,
            .expanded .biomarker-list-expand {
                transform: rotate(90deg);
            }
        `;
        document.head.appendChild(style);
    }
    
    // Initialize everything
    function init() {
        console.log('[Accordion Remake] Starting initialization...');
        
        // Add styles
        addStyles();
        
        // Wait a bit for DOM to be fully ready
        setTimeout(() => {
            initializeAccordions();
            
            // Set initial states (all collapsed)
            document.querySelectorAll('.biomarker-panel-content').forEach(panel => {
                if (!panel.style.display) {
                    panel.style.display = 'none';
                }
            });
            
            document.querySelectorAll('.biomarker-measurement-container').forEach(container => {
                if (!container.style.display) {
                    container.style.display = 'none';
                }
            });
            
            console.log('[Accordion Remake] Initialization complete!');
        }, 100);
    }
    
    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Re-initialize when biomarkers tab is clicked
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-tab="my-biomarkers"]')) {
            console.log('[Accordion Remake] Biomarkers tab clicked, reinitializing...');
            setTimeout(init, 200);
        }
    });
    
    // Global functions for compatibility (in case anything else calls them)
    window.togglePanel = function(panelKey) {
        console.log('[Accordion Remake] Legacy togglePanel called for:', panelKey);
        const panel = document.getElementById('panel-content-' + panelKey);
        if (panel) {
            const header = panel.previousElementSibling;
            if (header && header.classList.contains('biomarker-panel-header')) {
                header.click();
            }
        }
    };
    
    window.toggleBiomarkerMeasurements = function(panelKey, vectorCategory, biomarkerKey) {
        console.log('[Accordion Remake] Legacy toggleBiomarkerMeasurements called');
        const containerId = 'biomarker-measurement-' + panelKey + '-' + vectorCategory + '-' + biomarkerKey;
        const container = document.getElementById(containerId);
        if (container) {
            const item = container.previousElementSibling;
            if (item && item.classList.contains('biomarker-list-item')) {
                item.click();
            }
        }
    };
    
    // Debug helper
    window.accordionDebug = function() {
        console.log('=== ACCORDION DEBUG ===');
        
        const panels = document.querySelectorAll('.biomarker-panel-content');
        console.log(`Found ${panels.length} panels:`);
        panels.forEach(panel => {
            const display = window.getComputedStyle(panel).display;
            const biomarkerCount = panel.querySelectorAll('.biomarker-list-item').length;
            console.log(`  ${panel.id}: ${display} (${biomarkerCount} biomarkers)`);
        });
        
        const containers = document.querySelectorAll('.biomarker-measurement-container');
        console.log(`\nFound ${containers.length} measurement containers`);
        
        const visibleContainers = Array.from(containers).filter(c => 
            window.getComputedStyle(c).display !== 'none'
        );
        console.log(`  ${visibleContainers.length} are currently visible`);
    };
    
    console.log('[Accordion Remake] Script loaded. Use accordionDebug() for debugging.');
})();