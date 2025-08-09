/**
 * ENNU Biomarker Panel Fix
 * Permanent solution for biomarker panel and item toggle interference
 * Version: 1.0.0
 */

(function() {
    'use strict';
    
    console.log('[Biomarker Fix] Initializing permanent fix...');
    
    // Wait for DOM to be ready
    function initBiomarkerFix() {
        console.log('[Biomarker Fix] Setting up event handlers...');
        
        // Override the toggle functions to include event handling
        const originalTogglePanel = window.togglePanel;
        const originalToggleBiomarker = window.toggleBiomarkerMeasurements;
        
        // Enhanced togglePanel that checks for event target
        window.togglePanel = function(panelKey, event) {
            // If event is passed and target is a biomarker item, ignore
            if (event && event.target) {
                const biomarkerItem = event.target.closest('.biomarker-list-item');
                if (biomarkerItem) {
                    console.log('[Biomarker Fix] Panel toggle ignored - clicked on biomarker item');
                    return;
                }
            }
            
            console.log('[Biomarker Fix] Toggling panel:', panelKey);
            
            // Call original function or implement inline
            if (typeof originalTogglePanel === 'function') {
                originalTogglePanel.call(this, panelKey);
            } else {
                // Fallback implementation
                const panelContent = document.getElementById('panel-content-' + panelKey);
                if (panelContent) {
                    const isHidden = panelContent.style.display === 'none' || panelContent.style.display === '';
                    panelContent.style.display = isHidden ? 'block' : 'none';
                    
                    // Update expand icon
                    const panelHeader = panelContent.previousElementSibling;
                    if (panelHeader) {
                        const expandIcon = panelHeader.querySelector('.panel-expand-icon');
                        if (expandIcon) {
                            expandIcon.textContent = isHidden ? '▼' : '▶';
                        }
                    }
                    
                    console.log('[Biomarker Fix] Panel', isHidden ? 'expanded' : 'collapsed');
                }
            }
        };
        
        // Enhanced toggleBiomarkerMeasurements that stops propagation
        window.toggleBiomarkerMeasurements = function(panelKey, vectorCategory, biomarkerKey, event) {
            // Stop event propagation to prevent panel toggle
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            
            console.log('[Biomarker Fix] Toggling biomarker:', panelKey, vectorCategory, biomarkerKey);
            
            // Call original function or implement inline
            if (typeof originalToggleBiomarker === 'function') {
                originalToggleBiomarker.call(this, panelKey, vectorCategory, biomarkerKey);
            } else {
                // Fallback implementation
                const containerId = 'biomarker-measurement-' + panelKey + '-' + vectorCategory + '-' + biomarkerKey;
                const container = document.getElementById(containerId);
                
                if (container) {
                    const isHidden = container.style.display === 'none' || container.style.display === '';
                    container.style.display = isHidden ? 'block' : 'none';
                    
                    // Update the list item styling
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
                    
                    console.log('[Biomarker Fix] Biomarker', isHidden ? 'expanded' : 'collapsed');
                }
            }
        };
        
        // Process all existing onclick handlers to add event parameter
        function updateOnclickHandlers() {
            // Update panel headers
            const panelHeaders = document.querySelectorAll('.biomarker-panel-header[onclick]');
            panelHeaders.forEach(header => {
                const onclick = header.getAttribute('onclick');
                if (onclick && !onclick.includes('event')) {
                    // Add event parameter to the onclick
                    const newOnclick = onclick.replace(/togglePanel\('([^']+)'\)/, "togglePanel('$1', event)");
                    header.setAttribute('onclick', newOnclick);
                    console.log('[Biomarker Fix] Updated panel header onclick');
                }
            });
            
            // Update biomarker items
            const biomarkerItems = document.querySelectorAll('.biomarker-list-item[onclick]');
            biomarkerItems.forEach(item => {
                const onclick = item.getAttribute('onclick');
                if (onclick && !onclick.includes('event')) {
                    // Add event parameter to the onclick
                    const newOnclick = onclick.replace(
                        /toggleBiomarkerMeasurements\('([^']+)',\s*'([^']+)',\s*'([^']+)'\)/,
                        "toggleBiomarkerMeasurements('$1', '$2', '$3', event)"
                    );
                    item.setAttribute('onclick', newOnclick);
                    console.log('[Biomarker Fix] Updated biomarker item onclick');
                }
            });
        }
        
        // Add delegated event listener as backup
        document.addEventListener('click', function(e) {
            const biomarkerItem = e.target.closest('.biomarker-list-item');
            const panelHeader = e.target.closest('.biomarker-panel-header');
            
            if (biomarkerItem && panelHeader) {
                // Click is on a biomarker item that's somehow inside a panel header
                // This shouldn't happen with proper HTML structure, but handle it
                console.log('[Biomarker Fix] Preventing panel toggle due to biomarker click');
                e.stopPropagation();
            }
        }, true); // Use capture phase
        
        // Apply the fixes
        updateOnclickHandlers();
        
        // Also update any dynamically added elements
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length) {
                    // Check if new biomarker panels were added
                    const hasNewPanels = Array.from(mutation.addedNodes).some(node => 
                        node.nodeType === 1 && (
                            node.classList?.contains('biomarker-panel') ||
                            node.querySelector?.('.biomarker-panel')
                        )
                    );
                    
                    if (hasNewPanels) {
                        console.log('[Biomarker Fix] New panels detected, updating handlers...');
                        setTimeout(updateOnclickHandlers, 100);
                    }
                }
            });
        });
        
        // Start observing for dynamic content
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        console.log('[Biomarker Fix] Setup complete!');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBiomarkerFix);
    } else {
        // DOM is already ready
        initBiomarkerFix();
    }
    
    // Also initialize when dashboard tab is activated
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-tab="my-biomarkers"]')) {
            setTimeout(initBiomarkerFix, 100);
        }
    });
})();