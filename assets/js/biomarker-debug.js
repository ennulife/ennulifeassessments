/**
 * Debug script to understand biomarker panel structure
 */

(function() {
    console.log('=== BIOMARKER STRUCTURE DEBUG ===');
    
    // Wait for DOM
    setTimeout(() => {
        // Find all panels
        const panels = document.querySelectorAll('.biomarker-panel-content');
        console.log('Found', panels.length, 'panels');
        
        panels.forEach(panel => {
            console.log('\nPanel:', panel.id);
            console.log('  Display:', window.getComputedStyle(panel).display);
            console.log('  Inline style:', panel.style.display);
            
            // Check what's inside
            const vectors = panel.querySelectorAll('.biomarker-vector-category');
            console.log('  Vector categories:', vectors.length);
            
            const biomarkerItems = panel.querySelectorAll('.biomarker-list-item');
            console.log('  Biomarker items:', biomarkerItems.length);
            
            // Check if biomarker items are visible
            if (biomarkerItems.length > 0) {
                const firstItem = biomarkerItems[0];
                const itemDisplay = window.getComputedStyle(firstItem).display;
                console.log('  First biomarker item display:', itemDisplay);
                console.log('  First biomarker item parent:', firstItem.parentElement.className);
            }
            
            // Check for any hidden containers
            const hiddenElements = panel.querySelectorAll('[style*="display: none"], [style*="display:none"]');
            console.log('  Hidden elements inside:', hiddenElements.length);
            hiddenElements.forEach(el => {
                console.log('    - Hidden:', el.className || el.tagName, 'ID:', el.id);
            });
        });
        
        // Test expanding a panel
        console.log('\n=== TESTING PANEL EXPANSION ===');
        const testPanel = document.getElementById('panel-content-foundation_panel');
        if (testPanel) {
            console.log('Before expansion:');
            console.log('  Panel display:', window.getComputedStyle(testPanel).display);
            
            // Force show
            testPanel.style.display = 'block';
            testPanel.style.visibility = 'visible';
            testPanel.style.opacity = '1';
            
            console.log('After forcing display:');
            console.log('  Panel display:', window.getComputedStyle(testPanel).display);
            console.log('  Panel visibility:', window.getComputedStyle(testPanel).visibility);
            
            // Check children visibility
            const children = testPanel.children;
            console.log('  Direct children:', children.length);
            for (let i = 0; i < Math.min(3, children.length); i++) {
                const child = children[i];
                console.log(`    Child ${i}:`, child.className, 'Display:', window.getComputedStyle(child).display);
            }
            
            // Check if biomarker items are now visible
            const visibleBiomarkers = testPanel.querySelectorAll('.biomarker-list-item');
            console.log('  Biomarker items after expansion:', visibleBiomarkers.length);
            if (visibleBiomarkers.length > 0) {
                console.log('  First biomarker display:', window.getComputedStyle(visibleBiomarkers[0]).display);
            }
        }
    }, 1000);
    
    // Global function to manually test
    window.testPanelExpansion = function(panelKey) {
        const panel = document.getElementById('panel-content-' + panelKey) || 
                     document.getElementById('panel-' + panelKey);
        
        if (!panel) {
            console.error('Panel not found:', panelKey);
            return;
        }
        
        console.log('Testing panel:', panel.id);
        
        // Remove ALL display styles
        panel.style.removeProperty('display');
        
        // Force show with multiple methods
        panel.style.display = 'block';
        panel.style.visibility = 'visible';
        panel.style.opacity = '1';
        panel.style.position = 'relative';
        panel.style.height = 'auto';
        panel.style.overflow = 'visible';
        
        // Remove display:none from all children
        const hiddenChildren = panel.querySelectorAll('[style*="display: none"], [style*="display:none"]');
        console.log('Found', hiddenChildren.length, 'hidden children');
        hiddenChildren.forEach(child => {
            console.log('  Unhiding:', child.className || child.tagName);
            child.style.removeProperty('display');
            child.style.display = 'block';
        });
        
        // Check result
        const biomarkers = panel.querySelectorAll('.biomarker-list-item');
        console.log('Biomarker items visible:', biomarkers.length);
        
        // Check computed styles
        console.log('Panel final display:', window.getComputedStyle(panel).display);
        console.log('Panel final visibility:', window.getComputedStyle(panel).visibility);
    };
})();