/**
 * ENNU Biomarker Ultimate Fix
 * Removes ALL inline styles and replaces with data attributes
 * Version: 2.0.0
 */

(function() {
    'use strict';
    
    console.log('[Ultimate Fix] Initializing...');
    
    // Store original display states
    const originalStates = new Map();
    
    function removeAllInlineStyles() {
        console.log('[Ultimate Fix] Removing ALL inline display styles...');
        
        // Get all panel content elements
        const panelContents = document.querySelectorAll('.biomarker-panel-content');
        panelContents.forEach(panel => {
            // Check current display state BEFORE removing
            const wasHidden = panel.style.display === 'none' || 
                             window.getComputedStyle(panel).display === 'none';
            
            // Store original state
            originalStates.set(panel.id, wasHidden ? 'hidden' : 'visible');
            
            // REMOVE the inline style completely
            if (panel.style.display) {
                panel.style.removeProperty('display');
                console.log('[Ultimate Fix] Removed inline style from:', panel.id, 'was:', wasHidden ? 'hidden' : 'visible');
            }
            
            // Use data attribute instead
            panel.setAttribute('data-state', wasHidden ? 'hidden' : 'visible');
        });
        
        // Get all measurement containers
        const measurementContainers = document.querySelectorAll('.biomarker-measurement-container');
        measurementContainers.forEach(container => {
            // Check current display state BEFORE removing
            const wasHidden = container.style.display === 'none' || 
                             window.getComputedStyle(container).display === 'none';
            
            // Store original state
            originalStates.set(container.id || 'container-' + Math.random(), wasHidden ? 'hidden' : 'visible');
            
            // REMOVE the inline style completely
            if (container.style.display) {
                container.style.removeProperty('display');
                console.log('[Ultimate Fix] Removed inline style from:', container.id || 'unnamed container', 'was:', wasHidden ? 'hidden' : 'visible');
            }
            
            // Use data attribute instead
            container.setAttribute('data-state', wasHidden ? 'hidden' : 'visible');
        });
    }
    
    // CSS that uses data attributes instead of classes
    function injectCSS() {
        if (!document.getElementById('ultimate-fix-css')) {
            const style = document.createElement('style');
            style.id = 'ultimate-fix-css';
            style.textContent = `
                /* Use data attributes for state management - no inline styles can override this */
                .biomarker-panel-content[data-state="hidden"]:not([data-state="visible"]) {
                    display: none !important;
                }
                
                .biomarker-panel-content[data-state="visible"] {
                    display: block !important;
                }
                
                .biomarker-measurement-container[data-state="hidden"]:not([data-state="visible"]) {
                    display: none !important;
                }
                
                .biomarker-measurement-container[data-state="visible"] {
                    display: block !important;
                }
                
                /* Default states */
                .biomarker-panel-content:not([data-state]) {
                    display: none;
                }
                
                .biomarker-measurement-container:not([data-state]) {
                    display: none;
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    // New toggle functions that use data attributes
    function overrideToggleFunctions() {
        // Override togglePanel
        const originalTogglePanel = window.togglePanel;
        window.togglePanel = function(panelKey, event) {
            console.log('[Ultimate Fix] Toggle panel:', panelKey);
            
            // Check for biomarker item click
            if (event && event.target) {
                const biomarkerItem = event.target.closest('.biomarker-list-item');
                if (biomarkerItem) {
                    console.log('[Ultimate Fix] Ignoring panel toggle - biomarker clicked');
                    return;
                }
            }
            
            const panel = document.getElementById('panel-content-' + panelKey);
            if (panel) {
                // Remove ANY inline display style first
                panel.style.removeProperty('display');
                
                // Toggle data attribute
                const currentState = panel.getAttribute('data-state');
                const newState = currentState === 'visible' ? 'hidden' : 'visible';
                panel.setAttribute('data-state', newState);
                
                console.log('[Ultimate Fix] Panel', panelKey, 'is now', newState);
                
                // Update icon
                const expandIcon = document.querySelector(`[onclick*="togglePanel('${panelKey}')"] .panel-expand-icon`) ||
                                 document.getElementById('expand-icon-' + panelKey);
                if (expandIcon) {
                    expandIcon.textContent = newState === 'visible' ? '▼' : '▶';
                }
            }
        };
        
        // Override toggleBiomarkerMeasurements
        const originalToggleBiomarker = window.toggleBiomarkerMeasurements;
        window.toggleBiomarkerMeasurements = function(panelKey, vectorCategory, biomarkerKey, event) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            
            console.log('[Ultimate Fix] Toggle biomarker:', biomarkerKey);
            
            const containerId = 'biomarker-measurement-' + panelKey + '-' + vectorCategory + '-' + biomarkerKey;
            const container = document.getElementById(containerId);
            
            if (container) {
                // Remove ANY inline display style first
                container.style.removeProperty('display');
                
                // Toggle data attribute
                const currentState = container.getAttribute('data-state');
                const newState = currentState === 'visible' ? 'hidden' : 'visible';
                container.setAttribute('data-state', newState);
                
                console.log('[Ultimate Fix] Biomarker', biomarkerKey, 'is now', newState);
                
                // Update list item
                const listItem = container.previousElementSibling;
                if (listItem && listItem.classList.contains('biomarker-list-item')) {
                    if (newState === 'visible') {
                        listItem.classList.add('expanded');
                    } else {
                        listItem.classList.remove('expanded');
                    }
                    
                    // Update icon
                    const expandIcon = listItem.querySelector('.biomarker-list-expand');
                    if (expandIcon) {
                        expandIcon.textContent = newState === 'visible' ? '▼' : '▶';
                    }
                }
            }
        };
    }
    
    // Update onclick handlers
    function updateOnclickHandlers() {
        // Panel headers
        document.querySelectorAll('.biomarker-panel-header[onclick]').forEach(header => {
            const onclick = header.getAttribute('onclick');
            if (onclick && !onclick.includes('event')) {
                header.setAttribute('onclick', onclick.replace(/togglePanel\('([^']+)'\)/, "togglePanel('$1', event)"));
            }
        });
        
        // Biomarker items
        document.querySelectorAll('.biomarker-list-item[onclick]').forEach(item => {
            const onclick = item.getAttribute('onclick');
            if (onclick && !onclick.includes('event')) {
                item.setAttribute('onclick', onclick.replace(
                    /toggleBiomarkerMeasurements\('([^']+)',\s*'([^']+)',\s*'([^']+)'\)/,
                    "toggleBiomarkerMeasurements('$1', '$2', '$3', event)"
                ));
            }
        });
    }
    
    // Watch for elements that get inline styles added back
    function watchForInlineStyles() {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    const target = mutation.target;
                    if (target.classList.contains('biomarker-panel-content') || 
                        target.classList.contains('biomarker-measurement-container')) {
                        
                        // Check if inline display was added
                        if (target.style.display) {
                            console.log('[Ultimate Fix] Inline style detected on:', target.id, '- removing it');
                            
                            // Update data attribute based on what was trying to be set
                            const wasHidden = target.style.display === 'none';
                            target.setAttribute('data-state', wasHidden ? 'hidden' : 'visible');
                            
                            // Remove the inline style
                            target.style.removeProperty('display');
                        }
                    }
                }
            });
        });
        
        // Observe all biomarker elements
        document.querySelectorAll('.biomarker-panel-content, .biomarker-measurement-container').forEach(el => {
            observer.observe(el, {
                attributes: true,
                attributeFilter: ['style']
            });
        });
    }
    
    // Initialize everything
    function init() {
        console.log('[Ultimate Fix] Starting initialization...');
        
        // Step 1: Inject CSS first
        injectCSS();
        
        // Step 2: Remove all inline styles
        removeAllInlineStyles();
        
        // Step 3: Set initial states
        document.querySelectorAll('.biomarker-panel-content').forEach(panel => {
            if (!panel.hasAttribute('data-state')) {
                panel.setAttribute('data-state', 'hidden'); // Default to hidden
            }
        });
        
        document.querySelectorAll('.biomarker-measurement-container').forEach(container => {
            if (!container.hasAttribute('data-state')) {
                container.setAttribute('data-state', 'hidden'); // Default to hidden
            }
        });
        
        // Step 4: Override functions
        overrideToggleFunctions();
        
        // Step 5: Update onclick handlers
        updateOnclickHandlers();
        
        // Step 6: Watch for future inline style additions
        watchForInlineStyles();
        
        console.log('[Ultimate Fix] Initialization complete!');
    }
    
    // Run when ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Re-run when biomarkers tab is clicked
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-tab="my-biomarkers"]')) {
            setTimeout(init, 100);
        }
    });
    
    // Expose for debugging
    window.UltimateFix = {
        removeInlineStyles: removeAllInlineStyles,
        showStates: function() {
            document.querySelectorAll('[data-state]').forEach(el => {
                console.log(el.id, ':', el.getAttribute('data-state'));
            });
        }
    };
})();