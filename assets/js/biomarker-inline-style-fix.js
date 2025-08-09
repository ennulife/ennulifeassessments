/**
 * ENNU Biomarker Inline Style Fix
 * Removes inline display:none styles that interfere with toggle functionality
 * Version: 1.0.0
 */

(function() {
    'use strict';
    
    console.log('[Inline Style Fix] Initializing...');
    
    function removeInlineDisplayStyles() {
        console.log('[Inline Style Fix] Removing inline display:none styles...');
        
        // Remove inline display:none from panel content divs
        const panelContents = document.querySelectorAll('.biomarker-panel-content[style*="display: none"], .biomarker-panel-content[style*="display:none"]');
        panelContents.forEach(panel => {
            console.log('[Inline Style Fix] Removing inline style from panel:', panel.id);
            panel.style.removeProperty('display');
            // Add a class to handle the initial hidden state
            if (!panel.classList.contains('initially-hidden')) {
                panel.classList.add('initially-hidden');
            }
        });
        
        // Remove inline display:none from biomarker measurement containers
        const measurementContainers = document.querySelectorAll('.biomarker-measurement-container[style*="display: none"], .biomarker-measurement-container[style*="display:none"]');
        measurementContainers.forEach(container => {
            console.log('[Inline Style Fix] Removing inline style from measurement container:', container.id);
            container.style.removeProperty('display');
            // Add a class to handle the initial hidden state
            if (!container.classList.contains('initially-hidden')) {
                container.classList.add('initially-hidden');
            }
        });
        
        // Add CSS to handle the display states with MAXIMUM specificity using ID selectors
        if (!document.getElementById('inline-style-fix-css')) {
            const style = document.createElement('style');
            style.id = 'inline-style-fix-css';
            style.textContent = `
                /* MAXIMUM SPECIFICITY - Using multiple ID selectors for (0,4,0) and higher */
                
                /* Hide elements with initially-hidden class - specificity (0,4,1) or higher */
                #tab-my-biomarkers #panel-content-foundation_panel.biomarker-panel-content.initially-hidden,
                #tab-my-biomarkers #panel-content-guardian_panel.biomarker-panel-content.initially-hidden,
                #tab-my-biomarkers #panel-content-protector_panel.biomarker-panel-content.initially-hidden,
                #tab-my-biomarkers #panel-content-catalyst_panel.biomarker-panel-content.initially-hidden,
                #tab-my-biomarkers #panel-content-detoxifier_panel.biomarker-panel-content.initially-hidden,
                #tab-my-biomarkers #panel-content-timekeeper_panel.biomarker-panel-content.initially-hidden,
                #tab-my-biomarkers #panel-content-hormone_optimization_panel.biomarker-panel-content.initially-hidden,
                #tab-my-biomarkers #panel-content-cardiovascular_health_panel.biomarker-panel-content.initially-hidden,
                #tab-my-biomarkers #panel-content-longevity_performance_panel.biomarker-panel-content.initially-hidden,
                #tab-my-biomarkers #panel-content-cognitive_energy_panel.biomarker-panel-content.initially-hidden,
                #tab-my-biomarkers #panel-content-metabolic_optimization_panel.biomarker-panel-content.initially-hidden,
                #tab-my-biomarkers #panel-content-flagged-biomarkers.biomarker-panel-content.initially-hidden,
                body #tab-my-biomarkers .biomarker-panel .biomarker-panel-content.initially-hidden,
                body #tab-my-biomarkers .biomarker-measurement-container.initially-hidden {
                    display: none !important;
                }
                
                /* When expanded, force display block - specificity (0,4,1) or higher */
                #tab-my-biomarkers #panel-content-foundation_panel.biomarker-panel-content.expanded,
                #tab-my-biomarkers #panel-content-guardian_panel.biomarker-panel-content.expanded,
                #tab-my-biomarkers #panel-content-protector_panel.biomarker-panel-content.expanded,
                #tab-my-biomarkers #panel-content-catalyst_panel.biomarker-panel-content.expanded,
                #tab-my-biomarkers #panel-content-detoxifier_panel.biomarker-panel-content.expanded,
                #tab-my-biomarkers #panel-content-timekeeper_panel.biomarker-panel-content.expanded,
                #tab-my-biomarkers #panel-content-hormone_optimization_panel.biomarker-panel-content.expanded,
                #tab-my-biomarkers #panel-content-cardiovascular_health_panel.biomarker-panel-content.expanded,
                #tab-my-biomarkers #panel-content-longevity_performance_panel.biomarker-panel-content.expanded,
                #tab-my-biomarkers #panel-content-cognitive_energy_panel.biomarker-panel-content.expanded,
                #tab-my-biomarkers #panel-content-metabolic_optimization_panel.biomarker-panel-content.expanded,
                #tab-my-biomarkers #panel-content-flagged-biomarkers.biomarker-panel-content.expanded,
                body #tab-my-biomarkers .biomarker-panel .biomarker-panel-content.expanded,
                body #tab-my-biomarkers .biomarker-measurement-container.expanded,
                body.logged-in #tab-my-biomarkers [id^="panel-content-"].expanded,
                body.logged-in #tab-my-biomarkers [id^="biomarker-measurement-"].expanded {
                    display: block !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                    height: auto !important;
                    overflow: visible !important;
                }
                
                /* Force display when style attribute says block - specificity (0,2,1) with attribute selector */
                body #tab-my-biomarkers .biomarker-panel-content[style*="display: block"],
                body #tab-my-biomarkers .biomarker-panel-content[style*="display:block"],
                body #tab-my-biomarkers .biomarker-measurement-container[style*="display: block"],
                body #tab-my-biomarkers .biomarker-measurement-container[style*="display:block"] {
                    display: block !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                }
                
                /* Ensure proper display when visible - using inline style attribute for maximum override */
                [style*="display: block"].biomarker-panel-content,
                [style*="display: block"].biomarker-measurement-container {
                    display: block !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    // Override toggle functions to work with classes instead of inline styles
    function overrideToggleFunctions() {
        const originalTogglePanel = window.togglePanel;
        const originalToggleBiomarker = window.toggleBiomarkerMeasurements;
        
        // Enhanced togglePanel that uses classes
        window.togglePanel = function(panelKey, event) {
            console.log('[Inline Style Fix] Toggle panel:', panelKey);
            
            // Check if clicking on biomarker item
            if (event && event.target) {
                const biomarkerItem = event.target.closest('.biomarker-list-item');
                if (biomarkerItem) {
                    console.log('[Inline Style Fix] Ignoring panel toggle - clicked biomarker');
                    return;
                }
            }
            
            const panelContent = document.getElementById('panel-content-' + panelKey);
            if (panelContent) {
                // Toggle classes instead of inline styles
                const isHidden = panelContent.classList.contains('initially-hidden') && !panelContent.classList.contains('expanded');
                
                if (isHidden) {
                    panelContent.classList.remove('initially-hidden');
                    panelContent.classList.add('expanded');
                    panelContent.style.display = 'block';
                    console.log('[Inline Style Fix] Panel expanded');
                } else {
                    panelContent.classList.add('initially-hidden');
                    panelContent.classList.remove('expanded');
                    panelContent.style.display = 'none';
                    console.log('[Inline Style Fix] Panel collapsed');
                }
                
                // Update expand icon
                const panelHeader = panelContent.previousElementSibling;
                if (panelHeader) {
                    const expandIcon = panelHeader.querySelector('.panel-expand-icon');
                    if (expandIcon) {
                        expandIcon.textContent = isHidden ? '▼' : '▶';
                    }
                }
            }
        };
        
        // Enhanced toggleBiomarkerMeasurements that uses classes
        window.toggleBiomarkerMeasurements = function(panelKey, vectorCategory, biomarkerKey, event) {
            // Stop propagation
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            
            console.log('[Inline Style Fix] Toggle biomarker:', panelKey, vectorCategory, biomarkerKey);
            
            const containerId = 'biomarker-measurement-' + panelKey + '-' + vectorCategory + '-' + biomarkerKey;
            const container = document.getElementById(containerId);
            
            if (container) {
                // Toggle classes instead of inline styles
                const isHidden = container.classList.contains('initially-hidden') && !container.classList.contains('expanded');
                
                if (isHidden) {
                    container.classList.remove('initially-hidden');
                    container.classList.add('expanded');
                    container.style.display = 'block';
                    console.log('[Inline Style Fix] Biomarker expanded');
                } else {
                    container.classList.add('initially-hidden');
                    container.classList.remove('expanded');
                    container.style.display = 'none';
                    console.log('[Inline Style Fix] Biomarker collapsed');
                }
                
                // Update the list item
                const listItem = container.previousElementSibling;
                if (listItem && listItem.classList.contains('biomarker-list-item')) {
                    if (isHidden) {
                        listItem.classList.add('expanded');
                    } else {
                        listItem.classList.remove('expanded');
                    }
                    
                    // Update expand icon
                    const expandIcon = listItem.querySelector('.biomarker-list-expand');
                    if (expandIcon) {
                        expandIcon.textContent = isHidden ? '▼' : '▶';
                    }
                }
            }
        };
    }
    
    // Update onclick handlers to include event parameter
    function updateOnclickHandlers() {
        // Update panel headers
        const panelHeaders = document.querySelectorAll('.biomarker-panel-header[onclick]');
        panelHeaders.forEach(header => {
            const onclick = header.getAttribute('onclick');
            if (onclick && !onclick.includes('event')) {
                const newOnclick = onclick.replace(/togglePanel\('([^']+)'\)/, "togglePanel('$1', event)");
                header.setAttribute('onclick', newOnclick);
            }
        });
        
        // Update biomarker items
        const biomarkerItems = document.querySelectorAll('.biomarker-list-item[onclick]');
        biomarkerItems.forEach(item => {
            const onclick = item.getAttribute('onclick');
            if (onclick && !onclick.includes('event')) {
                const newOnclick = onclick.replace(
                    /toggleBiomarkerMeasurements\('([^']+)',\s*'([^']+)',\s*'([^']+)'\)/,
                    "toggleBiomarkerMeasurements('$1', '$2', '$3', event)"
                );
                item.setAttribute('onclick', newOnclick);
            }
        });
    }
    
    // Initialize everything
    function init() {
        removeInlineDisplayStyles();
        overrideToggleFunctions();
        updateOnclickHandlers();
        
        // Watch for dynamic content
        const observer = new MutationObserver(function(mutations) {
            let needsUpdate = false;
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    Array.from(mutation.addedNodes).forEach(node => {
                        if (node.nodeType === 1 && (
                            node.classList?.contains('biomarker-panel-content') ||
                            node.classList?.contains('biomarker-measurement-container') ||
                            node.querySelector?.('.biomarker-panel-content') ||
                            node.querySelector?.('.biomarker-measurement-container')
                        )) {
                            needsUpdate = true;
                        }
                    });
                }
            });
            
            if (needsUpdate) {
                console.log('[Inline Style Fix] New content detected, updating...');
                setTimeout(() => {
                    removeInlineDisplayStyles();
                    updateOnclickHandlers();
                }, 100);
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        console.log('[Inline Style Fix] Complete!');
    }
    
    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Also run when biomarkers tab is clicked
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-tab="my-biomarkers"]')) {
            setTimeout(init, 100);
        }
    });
})();