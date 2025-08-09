/**
 * ENNU Biomarker Simple Direct Fix
 * Most direct approach - just make the toggles work
 * Version: 3.0.0
 */

(function() {
    'use strict';
    
    console.log('[Simple Fix] Starting...');
    
    // Override the toggle functions with the simplest possible implementation
    window.togglePanel = function(panelKey) {
        console.log('[Simple Fix] togglePanel called for:', panelKey);
        
        const panel = document.getElementById('panel-content-' + panelKey);
        console.log('[Simple Fix] Looking for element with ID:', 'panel-content-' + panelKey);
        console.log('[Simple Fix] Found element:', panel);
        
        if (!panel) {
            console.error('[Simple Fix] Panel not found, trying alternative ID format...');
            // Try without 'content' in the ID
            const altPanel = document.getElementById('panel-' + panelKey);
            if (altPanel) {
                console.log('[Simple Fix] Found with alternative ID:', 'panel-' + panelKey);
                toggleElement(altPanel);
                return;
            }
            
            // List all elements with 'panel' in the ID to debug
            const allPanels = document.querySelectorAll('[id*="panel"]');
            console.log('[Simple Fix] All elements with "panel" in ID:');
            allPanels.forEach(p => console.log('  -', p.id));
            return;
        }
        
        // Check current state
        const currentDisplay = window.getComputedStyle(panel).display;
        const inlineDisplay = panel.style.display;
        console.log('[Simple Fix] Current computed display:', currentDisplay);
        console.log('[Simple Fix] Current inline display:', inlineDisplay);
        
        // Direct manipulation - remove inline style and set new one
        const isHidden = currentDisplay === 'none';
        
        if (isHidden) {
            // Force it to show
            panel.style.setProperty('display', 'block', 'important');
            panel.style.visibility = 'visible';
            panel.style.opacity = '1';
            console.log('[Simple Fix] Panel SHOWN:', panelKey);
            console.log('[Simple Fix] New inline style:', panel.style.cssText);
        } else {
            // Force it to hide
            panel.style.setProperty('display', 'none', 'important');
            console.log('[Simple Fix] Panel HIDDEN:', panelKey);
            console.log('[Simple Fix] New inline style:', panel.style.cssText);
        }
        
        // Update icon if exists
        const header = panel.previousElementSibling;
        if (header) {
            const icon = header.querySelector('.panel-expand-icon');
            if (icon) {
                icon.textContent = isHidden ? '▼' : '▶';
                console.log('[Simple Fix] Icon updated to:', isHidden ? '▼' : '▶');
            } else {
                console.log('[Simple Fix] No icon found in header');
            }
        }
    };
    
    window.toggleBiomarkerMeasurements = function(panelKey, vectorCategory, biomarkerKey) {
        console.log('[Simple Fix] toggleBiomarkerMeasurements called:', panelKey, vectorCategory, biomarkerKey);
        
        // Build the ID
        const containerId = 'biomarker-measurement-' + panelKey + '-' + vectorCategory + '-' + biomarkerKey;
        const container = document.getElementById(containerId);
        
        if (!container) {
            console.error('[Simple Fix] Container not found:', containerId);
            // Try alternative formats
            const alt1 = document.getElementById('biomarker-measurements-' + biomarkerKey);
            const alt2 = document.getElementById('biomarker-measurement-' + biomarkerKey);
            if (alt1) {
                console.log('[Simple Fix] Found alternative:', alt1.id);
                toggleElement(alt1);
            } else if (alt2) {
                console.log('[Simple Fix] Found alternative:', alt2.id);
                toggleElement(alt2);
            }
            return;
        }
        
        toggleElement(container);
    };
    
    function toggleElement(element) {
        const isHidden = window.getComputedStyle(element).display === 'none';
        
        if (isHidden) {
            // Force it to show
            element.style.setProperty('display', 'block', 'important');
            element.style.visibility = 'visible';
            element.style.opacity = '1';
            console.log('[Simple Fix] Element SHOWN:', element.id);
        } else {
            // Force it to hide
            element.style.setProperty('display', 'none', 'important');
            console.log('[Simple Fix] Element HIDDEN:', element.id);
        }
        
        // Update associated list item if it's a biomarker
        const prevElement = element.previousElementSibling;
        if (prevElement && prevElement.classList.contains('biomarker-list-item')) {
            if (isHidden) {
                prevElement.classList.add('expanded');
            } else {
                prevElement.classList.remove('expanded');
            }
            
            const icon = prevElement.querySelector('.biomarker-list-expand');
            if (icon) {
                icon.textContent = isHidden ? '▼' : '▶';
            }
        }
    }
    
    // Wait for DOM ready then add handlers
    function addClickHandlers() {
        // Remove any existing onclick attributes and use our handlers
        document.querySelectorAll('.biomarker-panel-header').forEach(header => {
            const onclick = header.getAttribute('onclick');
            if (onclick) {
                console.log('[Simple Fix] Setting up panel header:', onclick);
                header.removeAttribute('onclick');
                header.style.cursor = 'pointer';
                
                header.addEventListener('click', function(e) {
                    // Check if clicking on biomarker item
                    if (e.target.closest('.biomarker-list-item')) {
                        return; // Let biomarker handle it
                    }
                    
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const match = onclick.match(/togglePanel\('([^']+)'\)/);
                    if (match) {
                        console.log('[Simple Fix] Calling togglePanel for:', match[1]);
                        window.togglePanel(match[1]);
                    }
                });
            }
        });
        
        // Biomarker items
        document.querySelectorAll('.biomarker-list-item').forEach(item => {
            const onclick = item.getAttribute('onclick');
            if (onclick) {
                console.log('[Simple Fix] Setting up biomarker item:', onclick.substring(0, 50) + '...');
                item.removeAttribute('onclick');
                item.style.cursor = 'pointer';
                
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const match = onclick.match(/toggleBiomarkerMeasurements\('([^']+)',\s*'([^']+)',\s*'([^']+)'\)/);
                    if (match) {
                        console.log('[Simple Fix] Calling toggleBiomarkerMeasurements:', match[1], match[2], match[3]);
                        window.toggleBiomarkerMeasurements(match[1], match[2], match[3]);
                    }
                });
            }
        });
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', addClickHandlers);
    } else {
        setTimeout(addClickHandlers, 100);
    }
    
    // Debug helper
    window.debugBiomarkers = function() {
        console.log('=== BIOMARKER DEBUG ===');
        
        // List all panels
        const panels = document.querySelectorAll('.biomarker-panel-content');
        console.log('Panels found:', panels.length);
        panels.forEach(panel => {
            const display = window.getComputedStyle(panel).display;
            console.log('Panel:', panel.id, 'Display:', display, 'Inline style:', panel.style.display);
        });
        
        // List all measurement containers
        const containers = document.querySelectorAll('.biomarker-measurement-container');
        console.log('Measurement containers found:', containers.length);
        containers.forEach(container => {
            const display = window.getComputedStyle(container).display;
            console.log('Container:', container.id, 'Display:', display, 'Inline style:', container.style.display);
        });
    };
    
    console.log('[Simple Fix] Ready! Use debugBiomarkers() to check state.');
})();