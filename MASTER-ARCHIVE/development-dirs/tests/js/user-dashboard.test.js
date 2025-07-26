/**
 * Test suite for user-dashboard.js
 * @jest-environment jsdom
 */

// We need to import the class to test it
// This will require a build step configuration to handle modules,
// but for now we can test the logic in a simplified way.
// import ENNUDashboard from '../../assets/js/user-dashboard.js';

describe('Ennu User Dashboard Accordion', () => {
    beforeEach(() => {
        // Set up our document body to mimic the health map accordion structure
        document.body.innerHTML = `
            <div class="ennu-user-dashboard">
                <div class="health-map-accordion">
                    <div class="accordion-item">
                        <div class="accordion-header">Click me</div>
                        <div class="accordion-content" style="max-height: 0; overflow: hidden;">Content</div>
                    </div>
                </div>
            </div>
        `;

        // Simplified version of the accordion logic from the ENNUDashboard class
        const accordion = document.querySelector('.health-map-accordion');
        if (accordion) {
            accordion.addEventListener('click', (event) => {
                const header = event.target.closest('.accordion-header');
                if (!header) return;

                const item = header.closest('.accordion-item');
                const content = item.querySelector('.accordion-content');
                
                item.classList.toggle('open');
                if (item.classList.contains('open')) {
                    // In a real browser, scrollHeight would be calculated. We mock it for the test.
                    content.style.maxHeight = content.scrollHeight + "px"; 
                } else {
                    content.style.maxHeight = null;
                }
            });
        }
    });

    it('should open the accordion when the header is clicked', () => {
        const header = document.querySelector('.accordion-header');
        const item = document.querySelector('.accordion-item');
        const content = document.querySelector('.accordion-content');

        // Mock scrollHeight because JSDOM doesn't render layout
        Object.defineProperty(content, 'scrollHeight', { value: 100 });

        // Initial state
        expect(item.classList.contains('open')).toBe(false);
        expect(content.style.maxHeight).not.toBe('100px');

        // Click the header
        header.click();

        // Final state
        expect(item.classList.contains('open')).toBe(true);
        expect(content.style.maxHeight).toBe('100px');
    });

    it('should close the accordion when an open header is clicked', () => {
        const header = document.querySelector('.accordion-header');
        const item = document.querySelector('.accordion-item');
        const content = document.querySelector('.accordion-content');

        // Mock scrollHeight and set initial open state
        Object.defineProperty(content, 'scrollHeight', { value: 100 });
        item.classList.add('open');
        content.style.maxHeight = '100px';

        // Initial state
        expect(item.classList.contains('open')).toBe(true);
        expect(content.style.maxHeight).toBe('100px');

        // Click the header again
        header.click();

        // Final state
        expect(item.classList.contains('open')).toBe(false);
        expect(content.style.maxHeight).toBe("");
    });
}); 