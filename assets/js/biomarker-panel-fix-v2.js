/**
 * ENNU Biomarker Panel Fix v2
 * Fixes panel and biomarker toggle functionality
 * Version: 2.0.0
 */

(function() {
    'use strict';
    
    console.log('[Panel Fix v2] Initializing...');
    
    // Override the toggle functions
    window.togglePanel = function(panelKey) {
        console.log('[Panel Fix v2] togglePanel called for:', panelKey);
        
        const panel = document.getElementById('panel-content-' + panelKey);
        if (!panel) {
            console.error('[Panel Fix v2] Panel not found:', 'panel-content-' + panelKey);
            return;
        }
        
        // Get current state - check computed style, not inline
        const isCurrentlyHidden = window.getComputedStyle(panel).display === 'none';
        
        if (isCurrentlyHidden) {
            // Show the panel
            panel.style.display = 'block';
            console.log('[Panel Fix v2] Panel SHOWN:', panelKey);
            
            // Update icon
            const header = panel.previousElementSibling;
            if (header) {
                const icon = header.querySelector('.panel-expand-icon');
                if (icon) icon.textContent = '▼';
            }
        } else {
            // Hide the panel
            panel.style.display = 'none';
            console.log('[Panel Fix v2] Panel HIDDEN:', panelKey);
            
            // Update icon
            const header = panel.previousElementSibling;
            if (header) {
                const icon = header.querySelector('.panel-expand-icon');
                if (icon) icon.textContent = '▶';
            }
        }
    };
    
    window.toggleBiomarkerMeasurements = function(panelKey, vectorCategory, biomarkerKey) {
        console.log('[Panel Fix v2] toggleBiomarkerMeasurements:', panelKey, vectorCategory, biomarkerKey);
        
        const containerId = 'biomarker-measurement-' + panelKey + '-' + vectorCategory + '-' + biomarkerKey;
        const container = document.getElementById(containerId);
        
        if (!container) {
            console.error('[Panel Fix v2] Measurement container not found:', containerId);
            return;
        }
        
        // Get current state
        const isCurrentlyHidden = window.getComputedStyle(container).display === 'none';
        
        if (isCurrentlyHidden) {
            // Show measurements
            container.style.display = 'block';
            console.log('[Panel Fix v2] Measurements SHOWN for:', biomarkerKey);
            
            // Update the biomarker item appearance
            const listItem = container.previousElementSibling;
            if (listItem && listItem.classList.contains('biomarker-list-item')) {
                listItem.classList.add('expanded');
                const icon = listItem.querySelector('.biomarker-list-expand');
                if (icon) icon.textContent = '▼';
            }
        } else {
            // Hide measurements
            container.style.display = 'none';
            console.log('[Panel Fix v2] Measurements HIDDEN for:', biomarkerKey);
            
            // Update the biomarker item appearance
            const listItem = container.previousElementSibling;
            if (listItem && listItem.classList.contains('biomarker-list-item')) {
                listItem.classList.remove('expanded');
                const icon = listItem.querySelector('.biomarker-list-expand');
                if (icon) icon.textContent = '▶';
            }
        }
    };
    
    // Setup click handlers to prevent event bubbling
    function setupClickHandlers() {
        console.log('[Panel Fix v2] Setting up click handlers...');
        
        // Panel headers - remove onclick and add event listener
        document.querySelectorAll('.biomarker-panel-header').forEach(header => {
            const onclick = header.getAttribute('onclick');
            if (onclick) {
                header.removeAttribute('onclick');
                header.style.cursor = 'pointer';
                
                header.addEventListener('click', function(e) {
                    // Don't toggle if clicking inside a biomarker item
                    if (e.target.closest('.biomarker-list-item')) {
                        return;
                    }
                    
                    e.preventDefault();
                    const match = onclick.match(/togglePanel\('([^']+)'\)/);
                    if (match) {
                        window.togglePanel(match[1]);
                    }
                });
            }
        });
        
        // Biomarker items - remove onclick and add event listener
        document.querySelectorAll('.biomarker-list-item').forEach(item => {
            const onclick = item.getAttribute('onclick');
            if (onclick) {
                item.removeAttribute('onclick');
                item.style.cursor = 'pointer';
                
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Prevent bubbling to panel
                    
                    const match = onclick.match(/toggleBiomarkerMeasurements\('([^']+)',\s*'([^']+)',\s*'([^']+)'\)/);
                    if (match) {
                        window.toggleBiomarkerMeasurements(match[1], match[2], match[3]);
                    }
                });
            }
        });
        
        console.log('[Panel Fix v2] Click handlers setup complete');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupClickHandlers);
    } else {
        setTimeout(setupClickHandlers, 100);
    }
    
    // Re-initialize when biomarkers tab is clicked
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-tab="my-biomarkers"]')) {
            console.log('[Panel Fix v2] Biomarkers tab clicked, re-initializing...');
            setTimeout(setupClickHandlers, 200);
        }
    });
    
    // Debug helper
    window.debugPanels = function() {
        console.log('=== PANEL DEBUG ===');
        document.querySelectorAll('.biomarker-panel-content').forEach(panel => {
            console.log(panel.id, '- Display:', window.getComputedStyle(panel).display, '- Inline:', panel.style.display);
        });
        
        console.log('\n=== MEASUREMENT CONTAINERS ===');
        const containers = document.querySelectorAll('.biomarker-measurement-container');
        console.log('Total:', containers.length);
        
        // Show first 5
        for (let i = 0; i < Math.min(5, containers.length); i++) {
            const c = containers[i];
            console.log(c.id, '- Display:', window.getComputedStyle(c).display);
        }
    };
    
    console.log('[Panel Fix v2] Ready! Use debugPanels() to check state.');
})();