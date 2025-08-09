/**
 * ENNU Biomarker Panel Simple Fix
 * Direct manipulation of display states without CSS conflicts
 * Version: 2.0.0
 */

(function() {
    'use strict';
    
    console.log('[Biomarker Fix v2] Initializing...');
    
    // Store original functions
    let originalTogglePanel = null;
    let originalToggleBiomarker = null;
    
    function initFix() {
        console.log('[Biomarker Fix v2] Setting up toggle functions...');
        
        // Store originals if they exist
        if (window.togglePanel && !originalTogglePanel) {
            originalTogglePanel = window.togglePanel;
        }
        if (window.toggleBiomarkerMeasurements && !originalToggleBiomarker) {
            originalToggleBiomarker = window.toggleBiomarkerMeasurements;
        }
        
        // Override togglePanel
        window.togglePanel = function(panelKey, event) {
            console.log('[Biomarker Fix v2] Panel toggle:', panelKey);
            
            // Check if we clicked on a biomarker item
            if (event && event.target) {
                const biomarkerItem = event.target.closest('.biomarker-list-item');
                if (biomarkerItem) {
                    console.log('[Biomarker Fix v2] Click was on biomarker item, ignoring panel toggle');
                    return;
                }
            }
            
            const panelContent = document.getElementById('panel-content-' + panelKey);
            if (!panelContent) {
                console.log('[Biomarker Fix v2] Panel not found:', 'panel-content-' + panelKey);
                return;
            }
            
            // Direct style manipulation - no CSS classes needed
            const isCurrentlyHidden = panelContent.style.display === 'none' || 
                                     panelContent.style.display === '' ||
                                     window.getComputedStyle(panelContent).display === 'none';
            
            if (isCurrentlyHidden) {
                // EXPAND - directly set style attribute
                panelContent.style.setProperty('display', 'block', 'important');
                panelContent.style.setProperty('visibility', 'visible', 'important');
                panelContent.style.setProperty('opacity', '1', 'important');
                console.log('[Biomarker Fix v2] Panel expanded');
                
                // Update icon
                const expandIcon = document.querySelector(`[onclick*="togglePanel('${panelKey}')"] .panel-expand-icon`);
                if (expandIcon) expandIcon.textContent = '▼';
            } else {
                // COLLAPSE - directly set style attribute
                panelContent.style.setProperty('display', 'none', 'important');
                console.log('[Biomarker Fix v2] Panel collapsed');
                
                // Update icon
                const expandIcon = document.querySelector(`[onclick*="togglePanel('${panelKey}')"] .panel-expand-icon`);
                if (expandIcon) expandIcon.textContent = '▶';
            }
        };
        
        // Override toggleBiomarkerMeasurements
        window.toggleBiomarkerMeasurements = function(panelKey, vectorCategory, biomarkerKey, event) {
            // Stop event bubbling
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            
            console.log('[Biomarker Fix v2] Biomarker toggle:', biomarkerKey);
            
            // Build the container ID
            const containerId = 'biomarker-measurement-' + panelKey + '-' + vectorCategory + '-' + biomarkerKey;
            const container = document.getElementById(containerId);
            
            if (!container) {
                console.log('[Biomarker Fix v2] Container not found:', containerId);
                return;
            }
            
            // Direct style manipulation
            const isCurrentlyHidden = container.style.display === 'none' || 
                                     container.style.display === '' ||
                                     window.getComputedStyle(container).display === 'none';
            
            if (isCurrentlyHidden) {
                // EXPAND - directly set style attribute
                container.style.setProperty('display', 'block', 'important');
                container.style.setProperty('visibility', 'visible', 'important');
                container.style.setProperty('opacity', '1', 'important');
                console.log('[Biomarker Fix v2] Biomarker expanded');
                
                // Update list item styling
                const listItem = container.previousElementSibling;
                if (listItem && listItem.classList.contains('biomarker-list-item')) {
                    listItem.classList.add('expanded');
                    const expandIcon = listItem.querySelector('.biomarker-list-expand');
                    if (expandIcon) expandIcon.textContent = '▼';
                }
            } else {
                // COLLAPSE - directly set style attribute
                container.style.setProperty('display', 'none', 'important');
                console.log('[Biomarker Fix v2] Biomarker collapsed');
                
                // Update list item styling
                const listItem = container.previousElementSibling;
                if (listItem && listItem.classList.contains('biomarker-list-item')) {
                    listItem.classList.remove('expanded');
                    const expandIcon = listItem.querySelector('.biomarker-list-expand');
                    if (expandIcon) expandIcon.textContent = '▶';
                }
            }
        };
        
        // Update all onclick handlers to pass event
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
                    header.setAttribute('onclick', onclick.replace(
                        /toggleBiomarkerMeasurements\('([^']+)',\s*'([^']+)',\s*'([^']+)'\)/,
                        "toggleBiomarkerMeasurements('$1', '$2', '$3', event)"
                    ));
                }
            });
        }
        
        // Initial setup
        updateOnclickHandlers();
        
        // Also ensure initial state is properly set
        document.querySelectorAll('.biomarker-panel-content').forEach(panel => {
            // If it has inline display:none, ensure it's properly set
            if (panel.style.display === 'none') {
                panel.style.setProperty('display', 'none', 'important');
            }
        });
        
        document.querySelectorAll('.biomarker-measurement-container').forEach(container => {
            // If it has inline display:none, ensure it's properly set
            if (container.style.display === 'none') {
                container.style.setProperty('display', 'none', 'important');
            }
        });
        
        console.log('[Biomarker Fix v2] Setup complete');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFix);
    } else {
        // DOM already loaded
        setTimeout(initFix, 100);
    }
    
    // Reinitialize when biomarkers tab is shown
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-tab="my-biomarkers"]')) {
            console.log('[Biomarker Fix v2] Biomarkers tab activated, reinitializing...');
            setTimeout(initFix, 200);
        }
    });
    
    // Handle dynamic content
    const observer = new MutationObserver(function(mutations) {
        let hasBiomarkerContent = false;
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1 && (
                        node.classList?.contains('biomarker-panel') ||
                        node.querySelector?.('.biomarker-panel')
                    )) {
                        hasBiomarkerContent = true;
                    }
                });
            }
        });
        
        if (hasBiomarkerContent) {
            console.log('[Biomarker Fix v2] New biomarker content detected');
            setTimeout(initFix, 100);
        }
    });
    
    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
})();