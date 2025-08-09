/**
 * Biomarker Structure Analyzer - Deep dive into HTML structure
 */

(function() {
    console.log('=== BIOMARKER STRUCTURE ANALYZER ===');
    
    // Wait for DOM and biomarkers to load
    setTimeout(() => {
        // Find one panel to analyze deeply
        const testPanelId = 'panel-content-foundation_panel';
        const panel = document.getElementById(testPanelId);
        
        if (!panel) {
            console.error('Test panel not found:', testPanelId);
            return;
        }
        
        console.log('\n=== ANALYZING PANEL STRUCTURE ===');
        console.log('Panel ID:', panel.id);
        console.log('Panel classes:', panel.className);
        console.log('Panel display:', window.getComputedStyle(panel).display);
        console.log('Panel inline style:', panel.style.cssText);
        
        // Analyze children hierarchy
        console.log('\n=== CHILDREN HIERARCHY ===');
        analyzeChildren(panel, 0);
        
        // Find all biomarker items regardless of depth
        console.log('\n=== ALL BIOMARKER ITEMS IN PANEL ===');
        const allBiomarkers = panel.querySelectorAll('.biomarker-list-item');
        console.log('Total biomarker items found:', allBiomarkers.length);
        
        if (allBiomarkers.length > 0) {
            console.log('\nFirst biomarker analysis:');
            const firstBio = allBiomarkers[0];
            console.log('  Text:', firstBio.textContent.trim().substring(0, 50));
            console.log('  Classes:', firstBio.className);
            console.log('  Display:', window.getComputedStyle(firstBio).display);
            console.log('  Parent element:', firstBio.parentElement.tagName, firstBio.parentElement.className);
            console.log('  Parent display:', window.getComputedStyle(firstBio.parentElement).display);
            
            // Check all ancestors up to panel
            console.log('\n  Ancestor chain to panel:');
            let ancestor = firstBio.parentElement;
            let depth = 0;
            while (ancestor && ancestor !== panel && depth < 10) {
                console.log(`    ${depth}: ${ancestor.tagName}.${ancestor.className} - Display: ${window.getComputedStyle(ancestor).display}`);
                ancestor = ancestor.parentElement;
                depth++;
            }
        }
        
        // Try forcing panel visible and check what happens
        console.log('\n=== FORCING PANEL VISIBLE ===');
        panel.style.display = 'block';
        panel.style.visibility = 'visible';
        panel.style.opacity = '1';
        
        // Check if children are now visible
        const visibleCheck = panel.querySelectorAll('.biomarker-list-item');
        let visibleCount = 0;
        visibleCheck.forEach(item => {
            const display = window.getComputedStyle(item).display;
            if (display !== 'none') {
                visibleCount++;
            }
        });
        
        console.log(`\nAfter forcing panel visible:`);
        console.log(`  Panel display: ${window.getComputedStyle(panel).display}`);
        console.log(`  Visible biomarker items: ${visibleCount} of ${visibleCheck.length}`);
        
        // Check for any intermediate containers
        console.log('\n=== INTERMEDIATE CONTAINERS ===');
        const vectors = panel.querySelectorAll('.biomarker-vector-category');
        console.log('Vector categories found:', vectors.length);
        
        vectors.forEach((vector, i) => {
            console.log(`\nVector ${i}:`);
            console.log('  Classes:', vector.className);
            console.log('  Display:', window.getComputedStyle(vector).display);
            console.log('  Children count:', vector.children.length);
            console.log('  Has biomarkers:', vector.querySelectorAll('.biomarker-list-item').length);
        });
        
        // Check for any wrapper divs
        const wrappers = panel.querySelectorAll('div:not([class])');
        if (wrappers.length > 0) {
            console.log('\n=== UNCLASSED DIV WRAPPERS ===');
            wrappers.forEach((wrapper, i) => {
                if (wrapper.children.length > 0) {
                    console.log(`Wrapper ${i}: ${wrapper.children.length} children, Display: ${window.getComputedStyle(wrapper).display}`);
                }
            });
        }
        
    }, 1000);
    
    function analyzeChildren(element, depth) {
        if (depth > 5) return; // Limit depth
        
        const children = element.children;
        if (children.length === 0) return;
        
        const indent = '  '.repeat(depth);
        
        for (let i = 0; i < Math.min(3, children.length); i++) {
            const child = children[i];
            const display = window.getComputedStyle(child).display;
            const visibility = window.getComputedStyle(child).visibility;
            
            console.log(`${indent}├─ ${child.tagName}${child.id ? '#' + child.id : ''}.${child.className || '(no class)'}`);
            console.log(`${indent}│  Display: ${display}, Visibility: ${visibility}`);
            
            if (child.style.cssText) {
                console.log(`${indent}│  Inline style: ${child.style.cssText}`);
            }
            
            // Recurse for important containers
            if (child.classList.contains('biomarker-vector-category') || 
                child.classList.contains('biomarker-list-item') ||
                child.children.length > 0 && depth < 3) {
                analyzeChildren(child, depth + 1);
            }
        }
        
        if (children.length > 3) {
            console.log(`${indent}└─ ... and ${children.length - 3} more children`);
        }
    }
    
    // Global function to test specific panels
    window.analyzeBiomarkerPanel = function(panelKey) {
        const panel = document.getElementById('panel-content-' + panelKey);
        if (!panel) {
            console.error('Panel not found:', panelKey);
            return;
        }
        
        console.log('\n=== PANEL ANALYSIS:', panelKey, '===');
        
        // Force it visible
        panel.style.display = 'block';
        
        // Find all hidden elements inside
        const allElements = panel.getElementsByTagName('*');
        const hiddenElements = [];
        
        for (let el of allElements) {
            const computed = window.getComputedStyle(el);
            if (computed.display === 'none' || computed.visibility === 'hidden') {
                hiddenElements.push({
                    element: el,
                    tag: el.tagName,
                    class: el.className,
                    id: el.id,
                    display: computed.display,
                    visibility: computed.visibility,
                    parent: el.parentElement.tagName + '.' + el.parentElement.className
                });
            }
        }
        
        console.log('Hidden elements inside panel:', hiddenElements.length);
        hiddenElements.forEach(item => {
            console.log(`  ${item.tag}.${item.class} - Display: ${item.display}, Parent: ${item.parent}`);
        });
        
        // Try to make everything visible
        console.log('\nForcing all elements visible...');
        hiddenElements.forEach(item => {
            item.element.style.display = 'block';
            item.element.style.visibility = 'visible';
        });
        
        // Check result
        const biomarkers = panel.querySelectorAll('.biomarker-list-item');
        const visibleBiomarkers = Array.from(biomarkers).filter(b => 
            window.getComputedStyle(b).display !== 'none'
        );
        
        console.log(`Result: ${visibleBiomarkers.length} of ${biomarkers.length} biomarkers now visible`);
    };
})();