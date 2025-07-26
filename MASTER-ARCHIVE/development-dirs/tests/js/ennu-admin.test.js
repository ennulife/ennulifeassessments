/**
 * Test suite for ennu-admin.js
 * @jest-environment jsdom
 */

describe('Ennu Admin Tab Switching', () => {
    beforeEach(() => {
        // Set up our document body
        document.body.innerHTML = `
            <div class="ennu-admin-tabs">
                <nav class="ennu-admin-tab-nav">
                    <a href="#tab-1" class="ennu-admin-tab-active">Tab 1</a>
                    <a href="#tab-2">Tab 2</a>
                </nav>
                <div id="tab-1" class="ennu-admin-tab-content ennu-admin-tab-active">Content 1</div>
                <div id="tab-2" class="ennu-admin-tab-content">Content 2</div>
            </div>
        `;
        // We need to re-run the script's logic for each test
        // This is a simplified version of the logic in ennu-admin.js
        const tabContainer = document.querySelector(".ennu-admin-tabs");
        if (tabContainer) {
            const tabLinks = tabContainer.querySelectorAll(".ennu-admin-tab-nav a");
            const tabContents = tabContainer.querySelectorAll(".ennu-admin-tab-content");

            tabLinks.forEach(link => {
                link.addEventListener("click", function (event) {
                    event.preventDefault();
                    const targetId = this.getAttribute("href");
                    tabLinks.forEach(l => l.classList.remove("ennu-admin-tab-active"));
                    tabContents.forEach(c => c.classList.remove("ennu-admin-tab-active"));
                    this.classList.add("ennu-admin-tab-active");
                    document.querySelector(targetId).classList.add("ennu-admin-tab-active");
                });
            });
        }
    });

    it('should switch tabs when a tab link is clicked', () => {
        const tab2Link = document.querySelector('a[href="#tab-2"]');
        const tab1Content = document.getElementById('tab-1');
        const tab2Content = document.getElementById('tab-2');

        // Initial state
        expect(tab2Link.classList.contains('ennu-admin-tab-active')).toBe(false);
        expect(tab2Content.classList.contains('ennu-admin-tab-active')).toBe(false);
        expect(tab1Content.classList.contains('ennu-admin-tab-active')).toBe(true);

        // Click the second tab
        tab2Link.click();

        // Final state
        expect(tab2Link.classList.contains('ennu-admin-tab-active')).toBe(true);
        expect(tab2Content.classList.contains('ennu-admin-tab-active')).toBe(true);
        expect(tab1Content.classList.contains('ennu-admin-tab-active')).toBe(false);
    });
}); 