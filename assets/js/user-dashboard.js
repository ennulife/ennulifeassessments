/**
 * ENNU Life User Dashboard JavaScript
 * This file controls all the interactivity for the "Bio-Metric Canvas" dashboard.
 */

// TEST: Check if this file is loading
console.log('=== ENNU DASHBOARD JS LOADED ===');
console.log('Current timestamp:', new Date().toISOString());
console.log('Script URL:', document.currentScript ? document.currentScript.src : 'Unknown');

// Note: Toggle functions are now defined inline in the template for immediate availability

// Global functions for chart updates (can be called from other scripts)
window.ENNUCharts = {
	// Add a new score data point
	addScorePoint: function(value, timestamp = null) {
		const dashboard = document.querySelector('.ennu-user-dashboard');
		if (dashboard && dashboard.ennuDashboard) {
			dashboard.ennuDashboard.addDataPoint('score', value, timestamp);
		}
	},
	
	// Add a new BMI data point
	addBMIPoint: function(value, timestamp = null) {
		const dashboard = document.querySelector('.ennu-user-dashboard');
		if (dashboard && dashboard.ennuDashboard) {
			dashboard.ennuDashboard.addDataPoint('bmi', value, timestamp);
		}
	},
	
	// Refresh all charts
	refreshCharts: function() {
		const dashboard = document.querySelector('.ennu-user-dashboard');
		if (dashboard && dashboard.ennuDashboard) {
			dashboard.ennuDashboard.updateCharts();
		}
	},
	
	// Reinitialize all charts (useful for fixing canvas errors)
	reinitializeCharts: function() {
		const dashboard = document.querySelector('.ennu-user-dashboard');
		if (dashboard && dashboard.ennuDashboard) {
			dashboard.ennuDashboard.reinitializeCharts();
		}
	}
};

// Prevent multiple initializations
let dashboardInitialized = false;

document.addEventListener('DOMContentLoaded', () => {
    console.log('ENNU Dashboard: DOM Content Loaded');
    
    // Prevent multiple initializations
    if (dashboardInitialized) {
        console.log('ENNU Dashboard: Already initialized, skipping...');
        return;
    }
    
    // Verify toggle functions are available
    console.log('ENNU Dashboard: Toggle functions status:', {
        togglePanel: typeof window.togglePanel,
        toggleBiomarkerMeasurements: typeof window.toggleBiomarkerMeasurements,
        toggleVectorCategory: typeof window.toggleVectorCategory
    });
    
    const dashboardEl = document.querySelector('.ennu-user-dashboard');
    if (dashboardEl) {
        console.log('ENNU Dashboard: Dashboard element found, initializing...');
        
        // Check if user is logged in by looking for logged-out container
        const loggedOutContainer = dashboardEl.querySelector('.dashboard-logged-out-container');
        if (loggedOutContainer) {
            console.log('ENNU Dashboard: User is not logged in - showing logged out state');
            // Don't initialize charts for logged out users
            return;
        }
        
        // Destroy existing dashboard instance if it exists
        if (dashboardEl.ennuDashboard) {
            console.log('ENNU Dashboard: Destroying existing dashboard instance');
            dashboardEl.ennuDashboard.destroy();
        }
        
        new ENNUDashboard(dashboardEl);
        dashboardInitialized = true;
    } else {
        console.log('ENNU Dashboard: Dashboard element not found');
    }
    
    // Initialize My Story Tabs independently to ensure they work
    console.log('ENNU Dashboard: Initializing My Story Tabs Manager...');
    const storyTabsManager = new MyStoryTabsManager();
});



/**
 * My Story Tabs Manager
 * Handles tab navigation for the My Story section
 */
class MyStoryTabsManager {
    constructor() {
        console.log('MyStoryTabsManager: Constructor called');
        this.activeTab = 'tab-my-assessments';
        this.tabContainer = null;
        this.tabLinks = [];
        this.tabContents = [];
        
        this.init();
    }
    
    init() {
        console.log('MyStoryTabsManager: Init called');
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            console.log('MyStoryTabsManager: DOM still loading, adding event listener');
            document.addEventListener('DOMContentLoaded', () => this.initialize());
        } else {
            console.log('MyStoryTabsManager: DOM ready, initializing immediately');
            this.initialize();
        }
    }
    
    initialize() {
        console.log('MyStoryTabsManager: Initialize called');
        this.tabContainer = document.querySelector('.my-story-tabs');
        
        if (!this.tabContainer) {
            console.error('MyStoryTabsManager: Tab container not found!');
            return;
        }
        
        console.log('MyStoryTabsManager: Tab container found');
        
        this.tabLinks = this.tabContainer.querySelectorAll('.my-story-tab-nav a');
        this.tabContents = this.tabContainer.querySelectorAll('.my-story-tab-content');
        
        console.log('MyStoryTabsManager: Found', this.tabLinks.length, 'tab links and', this.tabContents.length, 'tab contents');
        
        if (this.tabLinks.length === 0 || this.tabContents.length === 0) {
            console.error('MyStoryTabsManager: No tab links or contents found!');
            return;
        }
        
        this.setupEventListeners();
        this.addAccessibilityAttributes();
        
        // Ensure first tab is active by default
        this.activateFirstTab();
        
    }
    
    activateFirstTab() {
        console.log('MyStoryTabsManager: Activating first tab');
        // Hide all tab contents first
        this.tabContents.forEach(content => {
            content.classList.remove('my-story-tab-active');
            content.style.display = 'none';
            content.style.opacity = '0';
            content.style.transform = 'translateY(10px)';
        });
        
        // Remove active class from all tab links
        this.tabLinks.forEach(link => {
            link.classList.remove('my-story-tab-active');
        });
        
        // Activate the My Biomarkers tab by default
        const biomarkersTabId = '#tab-my-biomarkers';
        const biomarkersLink = this.tabContainer.querySelector(`a[href="${biomarkersTabId}"]`);
        const biomarkersContent = document.querySelector(biomarkersTabId);
        
        console.log('MyStoryTabsManager: Biomarkers tab ID:', biomarkersTabId);
        console.log('MyStoryTabsManager: Biomarkers link element:', biomarkersLink);
        console.log('MyStoryTabsManager: Biomarkers content element:', biomarkersContent);
        
        if (biomarkersLink && biomarkersContent) {
            biomarkersLink.classList.add('my-story-tab-active');
            biomarkersContent.classList.add('my-story-tab-active');
            biomarkersContent.style.display = 'block';
            
            // Trigger animation after a brief delay
            setTimeout(() => {
                biomarkersContent.style.opacity = '1';
                biomarkersContent.style.transform = 'translateY(0)';
            }, 50);
            
            this.activeTab = biomarkersTabId.substring(1);
            console.log('MyStoryTabsManager: Biomarkers tab activated:', this.activeTab);
        } else {
            console.error('MyStoryTabsManager: Biomarkers tab elements not found!');
            // Fallback to first tab if biomarkers tab not found
            if (this.tabLinks.length > 0) {
                const firstLink = this.tabLinks[0];
                const firstTabId = firstLink.getAttribute('href');
                const firstContent = document.querySelector(firstTabId);
                
                if (firstContent) {
                    firstLink.classList.add('my-story-tab-active');
                    firstContent.classList.add('my-story-tab-active');
                    firstContent.style.display = 'block';
                    
                    setTimeout(() => {
                        firstContent.style.opacity = '1';
                        firstContent.style.transform = 'translateY(0)';
                    }, 50);
                    
                    this.activeTab = firstTabId.substring(1);
                    console.log('MyStoryTabsManager: Fallback to first tab activated:', this.activeTab);
                }
            }
        }
    }
    
    setupEventListeners() {
        console.log('MyStoryTabsManager: Setting up event listeners');
        
        this.tabLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href');
                console.log('MyStoryTabsManager: Tab clicked:', targetId);
                this.switchToTab(targetId);
            });
        });
        
        // Add keyboard navigation
        this.tabContainer.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
                e.preventDefault();
                const direction = e.key === 'ArrowLeft' ? -1 : 1;
                this.navigateToNextTab(direction);
            }
        });
    }
    
    addAccessibilityAttributes() {
        // Add ARIA attributes for accessibility
        this.tabLinks.forEach((link, index) => {
            const targetId = link.getAttribute('href').substring(1);
            link.setAttribute('role', 'tab');
            link.setAttribute('aria-controls', targetId);
            link.setAttribute('aria-selected', link.classList.contains('my-story-tab-active'));
            link.setAttribute('tabindex', link.classList.contains('my-story-tab-active') ? '0' : '-1');
        });
        
        this.tabContents.forEach((content) => {
            content.setAttribute('role', 'tabpanel');
            content.setAttribute('aria-hidden', !content.classList.contains('my-story-tab-active'));
        });
        
        // Add role to tab container
        const tabList = this.tabContainer.querySelector('.my-story-tab-nav ul');
        if (tabList) {
            tabList.setAttribute('role', 'tablist');
        }
    }
    
    switchToTab(targetId) {
        console.log('MyStoryTabsManager: switchToTab called with:', targetId);
        
        if (!targetId.startsWith('#')) {
            targetId = '#' + targetId;
        }
        
        console.log('MyStoryTabsManager: Normalized targetId:', targetId);
        
        let targetContent = document.querySelector(targetId);
        if (!targetContent) {
            console.error('MyStoryTabsManager: Target content not found for:', targetId);
            // Try alternative selector for symptoms tab
            if (targetId === '#tab-my-symptoms') {
                const altContent = document.querySelector('.my-story-tab-content[id*="symptoms"]');
                if (altContent) {
                    console.log('MyStoryTabsManager: Found alternative symptoms content:', altContent);
                    targetContent = altContent;
                }
            }
            if (!targetContent) {
                return;
            }
        }
        
        console.log('MyStoryTabsManager: Target content found:', targetContent);
        
        // Remove active class from all tabs and contents
        this.tabLinks.forEach(link => {
            link.classList.remove('my-story-tab-active');
            link.setAttribute('aria-selected', 'false');
            link.setAttribute('tabindex', '-1');
        });
        
        this.tabContents.forEach(content => {
            content.classList.remove('my-story-tab-active');
            content.setAttribute('aria-hidden', 'true');
            content.style.display = 'none';
            content.style.opacity = '0';
            content.style.transform = 'translateY(10px)';
        });
        
        // Add active class to selected tab and content
        const activeLink = this.tabContainer.querySelector(`a[href="${targetId}"]`);
        if (activeLink) {
            console.log('MyStoryTabsManager: Active link found:', activeLink);
            activeLink.classList.add('my-story-tab-active');
            activeLink.setAttribute('aria-selected', 'true');
            activeLink.setAttribute('tabindex', '0');
        } else {
            console.error('MyStoryTabsManager: Active link not found for:', targetId);
            // Try to find link by text content for symptoms
            if (targetId === '#tab-my-symptoms') {
                const symptomsLink = Array.from(this.tabLinks).find(link => 
                    link.textContent.toLowerCase().includes('symptoms')
                );
                if (symptomsLink) {
                    console.log('MyStoryTabsManager: Found symptoms link by text:', symptomsLink);
                    symptomsLink.classList.add('my-story-tab-active');
                    symptomsLink.setAttribute('aria-selected', 'true');
                    symptomsLink.setAttribute('tabindex', '0');
                }
            }
        }
        
        // Show target content
        targetContent.classList.add('my-story-tab-active');
        targetContent.setAttribute('aria-hidden', 'false');
        targetContent.style.display = 'block';
        
        // Trigger animation
        setTimeout(() => {
            targetContent.style.opacity = '1';
            targetContent.style.transform = 'translateY(0)';
        }, 50);
        
        // Update active tab reference
        this.activeTab = targetId.substring(1);
        console.log('MyStoryTabsManager: Tab switched to:', this.activeTab);
        
        // Trigger custom event
        this.triggerTabChangeEvent(targetId, activeLink, targetContent);
        
        // Special handling for symptoms tab
        if (targetId === '#tab-my-symptoms') {
            console.log('MyStoryTabsManager: Symptoms tab activated, updating symptoms display');
            this.updateSymptomsDisplay();
        }
    }
    
    navigateToNextTab(direction) {
        const currentIndex = Array.from(this.tabLinks).findIndex(link => 
            link.classList.contains('my-story-tab-active')
        );
        
        if (currentIndex === -1) return;
        
        const newIndex = (currentIndex + direction + this.tabLinks.length) % this.tabLinks.length;
        const newTab = this.tabLinks[newIndex];
        
        this.switchToTab(newTab.getAttribute('href'));
    }
    
    triggerTabChangeEvent(targetId, activeLink, targetContent) {
        // Custom event for tab changes
        const event = new CustomEvent('tabChanged', {
            detail: {
                targetId: targetId,
                activeLink: activeLink,
                targetContent: targetContent
            }
        });
        this.tabContainer.dispatchEvent(event);
    }
    
    saveTabState() {
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem('ennu-active-tab', this.activeTab);
        }
    }
    
    loadTabState() {
        if (typeof localStorage !== 'undefined') {
            const savedTab = localStorage.getItem('ennu-active-tab');
            if (savedTab && this.tabContainer.querySelector(`a[href="#${savedTab}"]`)) {
                this.switchToTab('#' + savedTab);
            }
        }
    }
    
    getActiveTab() {
        return this.activeTab;
    }
    
    getAllTabs() {
        return Array.from(this.tabLinks).map(link => link.getAttribute('href').substring(1));
    }

    updateSymptomsDisplay() {
        console.log('MyStoryTabsManager: Updating symptoms display');
        
        // Update symptom counts
        const totalSymptomsEl = document.getElementById('total-symptoms-count');
        const activeSymptomsEl = document.getElementById('active-symptoms-count');
        const biomarkerCorrelationsEl = document.getElementById('biomarker-correlations');
        const trendingSymptomsEl = document.getElementById('trending-symptoms');
        
        if (totalSymptomsEl) {
            const symptomItems = document.querySelectorAll('.symptom-item');
            totalSymptomsEl.textContent = symptomItems.length;
        }
        
        if (activeSymptomsEl) {
            const activeSymptomItems = document.querySelectorAll('.symptom-item.active');
            activeSymptomsEl.textContent = activeSymptomItems.length;
        }
        
        if (biomarkerCorrelationsEl) {
            const biomarkerTags = document.querySelectorAll('.biomarker-tag');
            biomarkerCorrelationsEl.textContent = biomarkerTags.length;
        }
        
        if (trendingSymptomsEl) {
            const trendingItems = document.querySelectorAll('.symptom-item.trending');
            trendingSymptomsEl.textContent = trendingItems.length;
        }
        
        console.log('MyStoryTabsManager: Symptoms display updated');
    }
}

// Add CSS styles for My Story tabs
function addMyStoryTabStyles() {
    const style = document.createElement('style');
    style.textContent = `
        .my-story-tab-content {
            display: none;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s ease;
        }
        
        .my-story-tab-content.my-story-tab-active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
    `;
    document.head.appendChild(style);
}

// Add the styles when the script loads
addMyStoryTabStyles();

/**
 * Upload Lab Results Function
 * Handles lab result upload functionality
 */
function uploadLabResults() {
    // Create a modal or redirect to upload page
    const uploadUrl = '/lab-upload/'; // Replace with actual upload URL
    if (confirm('Would you like to upload your lab results? You will be redirected to the upload page.')) {
        window.location.href = uploadUrl;
    }
}

/**
 * Schedule Lab Test Function
 * Handles lab test scheduling functionality
 */
function scheduleLabTest() {
    // Create a modal or redirect to scheduling page
    const scheduleUrl = '/schedule-lab-test/'; // Replace with actual scheduling URL
    if (confirm('Would you like to schedule a lab test? You will be redirected to the scheduling page.')) {
        window.location.href = scheduleUrl;
    }
}

/**
 * View Biomarker Guide Function
 * Shows biomarker guide and information
 */
function viewBiomarkerGuide() {
    // Create a modal with biomarker guide
    const guideUrl = '/biomarker-guide/'; // Replace with actual guide URL
    if (confirm('Would you like to view the biomarker guide? You will be redirected to the guide page.')) {
        window.location.href = guideUrl;
    }
}

/**
 * View Biomarker Details Function
 * Shows detailed information for a specific biomarker
 * @param {string} biomarker - The biomarker identifier
 */
function viewBiomarkerDetails(biomarker) {
    // Create a modal with biomarker details
    const detailsUrl = `/biomarker-details/${biomarker}/`; // Replace with actual details URL
    if (confirm(`Would you like to view detailed information for ${biomarker}? You will be redirected to the details page.`)) {
        window.location.href = detailsUrl;
    }
}

/**
 * Update Biomarker Function
 * Allows users to update biomarker values
 * @param {string} biomarker - The biomarker identifier
 */
function updateBiomarker(biomarker) {
    // Create a modal for updating biomarker values
    const updateUrl = `/update-biomarker/${biomarker}/`; // Replace with actual update URL
    if (confirm(`Would you like to update the value for ${biomarker}? You will be redirected to the update page.`)) {
        window.location.href = updateUrl;
    }
}

/**
 * Master toggle function for all biomarker panels
 */
function toggleAllBiomarkerPanels() {
    const containers = document.querySelectorAll('.biomarker-measurement-container');
    const listItems = document.querySelectorAll('.biomarker-list-item');
    const toggleBtn = document.querySelector('.master-toggle-btn');
    const toggleIcon = toggleBtn.querySelector('.toggle-icon');
    const toggleText = toggleBtn.querySelector('.toggle-text');
    
    const allExpanded = Array.from(containers).every(container => 
        container.style.display === 'block' || container.style.display === ''
    );
    
    if (allExpanded) {
        // Collapse all
        containers.forEach(container => container.style.display = 'none');
        listItems.forEach(item => {
            item.classList.remove('expanded');
            const expandIcon = item.querySelector('.biomarker-list-expand');
            if (expandIcon) expandIcon.textContent = '▶';
        });
        toggleIcon.textContent = '▶';
        toggleText.textContent = 'Expand All Panels';
    } else {
        // Expand all
        containers.forEach(container => container.style.display = 'block');
        listItems.forEach(item => {
            item.classList.add('expanded');
            const expandIcon = item.querySelector('.biomarker-list-expand');
            if (expandIcon) expandIcon.textContent = '▼';
        });
        toggleIcon.textContent = '▼';
        toggleText.textContent = 'Collapse All Panels';
    }
}

/**
 * Panel-specific toggle function
 * @param {string} panelKey - The key of the panel (e.g., 'blood', 'urine')
 */
function toggleBiomarkerPanel(panelKey) {
    const panel = document.querySelector(`.biomarker-panel-${panelKey}`);
    const containers = panel.querySelectorAll('.biomarker-measurement-container');
    const listItems = panel.querySelectorAll('.biomarker-list-item');
    
    const allExpanded = Array.from(containers).every(container => 
        container.style.display === 'block' || container.style.display === ''
    );
    
    if (allExpanded) {
        // Collapse panel
        containers.forEach(container => container.style.display = 'none');
        listItems.forEach(item => {
            item.classList.remove('expanded');
            const expandIcon = item.querySelector('.biomarker-list-expand');
            if (expandIcon) expandIcon.textContent = '▶';
        });
    } else {
        // Expand panel
        containers.forEach(container => container.style.display = 'block');
        listItems.forEach(item => {
            item.classList.add('expanded');
            const expandIcon = item.querySelector('.biomarker-list-expand');
            if (expandIcon) expandIcon.textContent = '▼';
        });
    }
}

/**
 * Main Dashboard Class
 * Handles all dashboard functionality
 */
class ENNUDashboard {
	constructor(dashboardElement) {
		this.dashboard = dashboardElement;
		this.dashboard.ennuDashboard = this; // Store reference for global access
		
		// Initialize chart instances storage
		this.chartInstances = {
			scoreHistory: null,
			bmiHistory: null
		};
		
		this.init();
	}

	initThemeToggle() {
		const themeToggle = this.dashboard.querySelector('#theme-toggle');
		if (!themeToggle) return;

		// Always start with light mode on every page refresh
		// Clear any saved theme preference to ensure fresh start
		localStorage.removeItem('ennu-theme');
		
		// Force light mode for every session
		this.setTheme('light');

		themeToggle.addEventListener('click', () => {
			const currentTheme = this.dashboard.getAttribute('data-theme') || 'light';
			const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
			this.setTheme(newTheme);
		});
	}

	setTheme(theme) {
		this.dashboard.setAttribute('data-theme', theme);
		// Do not save theme preference - always start fresh with light mode
		// localStorage.setItem('ennu-theme', theme);
		
		// Update toggle position (light mode = 0, dark mode = 30px)
		const toggleThumb = this.dashboard.querySelector('.toggle-thumb');
		if (toggleThumb) {
			if (theme === 'light') {
				toggleThumb.style.transform = 'translateX(0)';
			} else {
				toggleThumb.style.transform = 'translateX(30px)';
			}
		}
	}

	initToggleAll() {
		const toggleAllBtn = this.dashboard.querySelector('#toggle-all-accordions');
		if (!toggleAllBtn) return;

		toggleAllBtn.addEventListener('click', () => {
			const accordionItems = this.dashboard.querySelectorAll('.accordion-item');
			const allOpen = Array.from(accordionItems).every(item => item.classList.contains('open'));
			
			accordionItems.forEach(item => {
				if (allOpen) {
					item.classList.remove('open');
				} else {
					item.classList.add('open');
				}
			});
			
			toggleAllBtn.textContent = allOpen ? 'Expand All' : 'Collapse All';
		});
	}

	initHealthMapAccordion() {
		const accordionItems = this.dashboard.querySelectorAll('.accordion-item');
		
		accordionItems.forEach(item => {
			const header = item.querySelector('.accordion-header');
			if (!header) return;

			header.addEventListener('click', () => {
				item.classList.toggle('open');
			});

			// Keyboard support
			header.addEventListener('keydown', (e) => {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					item.classList.toggle('open');
				}
			});
		});
	}

	initDetailsToggle() {
		const toggleButtons = this.dashboard.querySelectorAll('.view-details-toggle');
		
		toggleButtons.forEach(button => {
			button.addEventListener('click', () => {
				const listItem = button.closest('.assessment-list-item');
				const detailsContainer = listItem.querySelector('.category-details-container');
				
				if (detailsContainer) {
					const isExpanded = listItem.getAttribute('aria-expanded') === 'true';
					listItem.setAttribute('aria-expanded', !isExpanded);
					
					if (!isExpanded) {
						detailsContainer.style.display = 'block';
						setTimeout(() => {
							detailsContainer.style.opacity = '1';
							detailsContainer.style.transform = 'translateY(0)';
						}, 10);
					} else {
						detailsContainer.style.opacity = '0';
						detailsContainer.style.transform = 'translateY(-10px)';
						setTimeout(() => {
							detailsContainer.style.display = 'none';
						}, 300);
					}
				}
			});
		});
	}

	initContextualText() {
		const contextualText = this.dashboard.querySelector('#contextual-text');
		const mainScoreOrb = this.dashboard.querySelector('.main-score-orb');
		
		if (!contextualText || !mainScoreOrb) return;

		const insights = {
			'0-3': 'Your ENNU Life Score indicates significant opportunities for health optimization. Focus on completing assessments and implementing foundational lifestyle changes.',
			'3-5': 'You\'re making progress! Continue with your health journey by completing more assessments and following personalized recommendations.',
			'5-7': 'Great work! Your health foundation is solid. Focus on fine-tuning specific areas and maintaining consistency.',
			'7-9': 'Excellent! You\'re approaching optimal health. Continue your current practices and explore advanced optimization strategies.',
			'9-10': 'Outstanding! You\'ve achieved exceptional health optimization. Maintain your practices and consider becoming a health mentor.'
		};

		const updateContextualText = () => {
			const score = parseFloat(mainScoreOrb.getAttribute('data-score') || 0);
			let insight = insights['0-3'];
			
			if (score >= 9) insight = insights['9-10'];
			else if (score >= 7) insight = insights['7-9'];
			else if (score >= 5) insight = insights['5-7'];
			else if (score >= 3) insight = insights['3-5'];
			
			contextualText.textContent = insight;
		};

		// Update on hover
		mainScoreOrb.addEventListener('mouseenter', () => {
			updateContextualText();
			contextualText.classList.add('visible');
		});

		mainScoreOrb.addEventListener('mouseleave', () => {
			contextualText.classList.remove('visible');
		});

		// Initial update
		updateContextualText();
	}

	initScoreAnimation() {
		const mainScoreOrb = this.dashboard.querySelector('.main-score-orb');
		if (!mainScoreOrb) return;

		const targetScore = parseFloat(mainScoreOrb.getAttribute('data-score') || 0);
		const scoreElement = mainScoreOrb.querySelector('.main-score-value');
		
		if (!scoreElement) return;

		let currentScore = 0;
		const duration = 2000; // 2 seconds
		const startTime = performance.now();

		const step = (timestamp) => {
			const elapsed = timestamp - startTime;
			const progress = Math.min(elapsed / duration, 1);
			
			// Easing function for smooth animation
			const easeOutQuart = 1 - Math.pow(1 - progress, 4);
			currentScore = targetScore * easeOutQuart;
			
			scoreElement.textContent = currentScore.toFixed(1);
			
			if (progress < 1) {
				requestAnimationFrame(step);
			} else {
				mainScoreOrb.classList.add('loaded');
			}
		};

		requestAnimationFrame(step);
	}

	initPillarAnimation() {
		const pillarOrbs = this.dashboard.querySelectorAll('.pillar-orb');
		
		pillarOrbs.forEach((orb, index) => {
			setTimeout(() => {
				orb.classList.add('visible');
			}, index * 200);
		});
	}

	initHistoricalCharts() {
		console.log('ENNU Dashboard: Initializing historical charts...');
		console.log('ENNU Dashboard: Chart.js available:', typeof Chart !== 'undefined');
		console.log('ENNU Dashboard: Chart.js version:', typeof Chart !== 'undefined' ? Chart.version : 'Not loaded');
		
		// Check if Chart.js is available
		if (typeof Chart === 'undefined') {
			console.error('ENNU Dashboard: Chart.js is not loaded');
			this.showChartError('Chart.js library is not available. Please refresh the page and try again.');
			return;
		}

		// Check if time adapter is available
		if (typeof Chart.adapters === 'undefined' || typeof Chart.adapters.date === 'undefined') {
			console.warn('ENNU Dashboard: Chart.js time adapter not available, charts may not display correctly');
		}

		// Check if we're in a logged-in state by looking for chart containers
		const chartContainers = document.querySelectorAll('.chart-wrapper');
		if (chartContainers.length === 0) {
			console.log('ENNU Dashboard: No chart containers found - user may not be logged in or dashboard not fully loaded');
			return;
		}

		// Initialize charts with real data
		this.initScoreHistoryChart();
		this.initBMIHistoryChart();
		
		// Set up chart refresh on tab show
		this.setupChartRefreshOnTabShow();
	}

	initScoreHistoryChart() {
		console.log('ENNU Dashboard: Initializing score history chart...');
		const scoreCtx = this.dashboard.querySelector('#scoreHistoryChart');
		if (!scoreCtx) {
			console.error('ENNU Dashboard: Score history chart canvas not found - user may not be logged in');
			this.showChartError('Score history chart not available. Please log in to view your health trends.');
			return;
		}

		// Destroy existing chart instance if it exists
		if (this.chartInstances.scoreHistory) {
			console.log('ENNU Dashboard: Destroying existing score history chart');
			this.chartInstances.scoreHistory.destroy();
			this.chartInstances.scoreHistory = null;
		}

		// Get real user score data
		const scoreData = this.getUserScoreData();
		console.log('ENNU Dashboard: Score data received:', scoreData);

		if (scoreData && scoreData.length > 0) {
			console.log('ENNU Dashboard: Creating chart with data length:', scoreData.length);
			try {
				const chartConfig = {
					type: 'line',
					data: {
						labels: scoreData.map(d => new Date(d.date)), // Use proper Date objects for time scale
						datasets: [{
							label: 'ENNU Life Score',
							data: scoreData.map(d => d.score),
							borderColor: '#10b981',
							backgroundColor: 'rgba(16, 185, 129, 0.1)',
							borderWidth: 3,
							fill: true,
							tension: 0.4,
							pointBackgroundColor: '#10b981',
							pointBorderColor: '#ffffff',
							pointBorderWidth: 2,
							pointRadius: 6,
							pointHoverRadius: 8
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						interaction: {
							intersect: false,
							mode: 'index',
						},
						scales: {
							x: { 
								type: 'time', 
								time: { 
									unit: scoreData.length > 30 ? 'week' : 'day',
									displayFormats: {
										day: 'MMM d',
										week: 'MMM d'
									}
								}, 
								grid: { color: 'rgba(255, 255, 255, 0.1)' }, 
								ticks: { 
									color: 'rgba(255, 255, 255, 0.7)',
									font: {
										size: 12
									}
								} 
							},
							y: { 
								beginAtZero: false,
								min: 0,
								max: 10,
								grid: { color: 'rgba(255, 255, 255, 0.1)' }, 
								ticks: { 
									color: 'rgba(255, 255, 255, 0.7)',
									font: {
										size: 12
									}
								} 
							}
						},
						plugins: { 
							legend: { display: false },
							tooltip: {
								backgroundColor: 'rgba(0, 0, 0, 0.8)',
								titleColor: '#fff',
								bodyColor: '#fff',
								borderColor: 'rgba(52, 211, 153, 0.5)',
								borderWidth: 1,
								cornerRadius: 8,
								displayColors: false,
								callbacks: {
									title: function(context) {
										return 'Score: ' + context[0].parsed.y.toFixed(1);
									},
									label: function(context) {
										// Use original date for display but timestamp for positioning
										const dataIndex = context.dataIndex;
										const originalDate = scoreData[dataIndex]?.originalDate;
										const displayDate = originalDate || new Date(context.parsed.x).toLocaleDateString();
										return 'Date: ' + displayDate;
									}
								}
							}
						}
					}
				};
				
				console.log('ENNU Dashboard: Chart config:', chartConfig);
				
				this.chartInstances.scoreHistory = new Chart(scoreCtx, chartConfig);
				console.log('ENNU Dashboard: Score history chart initialized successfully');
			} catch (error) {
				console.error('ENNU Dashboard: Error initializing score history chart:', error);
				this.showChartError('Failed to load score history chart');
			}
		} else {
			console.log('ENNU Dashboard: No score data available, showing empty state');
			// Show empty state for score chart
			this.showEmptyChartState(scoreCtx, '📊', 'No Score Data', 'Complete assessments to see your score history');
		}
	}

	initBMIHistoryChart() {
		console.log('ENNU Dashboard: Initializing BMI history chart...');
		const bmiCtx = this.dashboard.querySelector('#bmiHistoryChart');
		if (!bmiCtx) {
			console.error('ENNU Dashboard: BMI history chart canvas not found - user may not be logged in');
			this.showChartError('BMI history chart not available. Please log in to view your health trends.');
			return;
		}

		// Destroy existing chart instance if it exists
		if (this.chartInstances.bmiHistory) {
			console.log('ENNU Dashboard: Destroying existing BMI history chart');
			this.chartInstances.bmiHistory.destroy();
			this.chartInstances.bmiHistory = null;
		}

		// Get real user BMI data
		const bmiData = this.getUserBMIData();
		console.log('ENNU Dashboard: BMI data:', bmiData);

		if (bmiData && bmiData.length > 0) {
			try {
				this.chartInstances.bmiHistory = new Chart(bmiCtx, {
					type: 'line',
					data: {
						labels: bmiData.map(d => new Date(d.date)), // Use proper Date objects for time scale
						datasets: [{
							label: 'BMI',
							data: bmiData.map(d => d.bmi),
							borderColor: '#3b82f6',
							backgroundColor: 'rgba(59, 130, 246, 0.1)',
							borderWidth: 3,
							fill: true,
							tension: 0.4,
							pointBackgroundColor: '#3b82f6',
							pointBorderColor: '#ffffff',
							pointBorderWidth: 2,
							pointRadius: 6,
							pointHoverRadius: 8
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						interaction: {
							intersect: false,
							mode: 'index',
						},
						scales: {
							x: { 
								type: 'time', 
								time: { 
									unit: bmiData.length > 30 ? 'week' : 'day',
									displayFormats: {
										day: 'MMM d',
										week: 'MMM d'
									}
								}, 
								grid: { color: 'rgba(255, 255, 255, 0.1)' }, 
								ticks: { 
									color: 'rgba(255, 255, 255, 0.7)',
									font: {
										size: 12
									}
								} 
							},
							y: { 
								beginAtZero: false,
								min: 15,
								max: 40,
								grid: { color: 'rgba(255, 255, 255, 0.1)' }, 
								ticks: { 
									color: 'rgba(255, 255, 255, 0.7)',
									font: {
										size: 12
									}
								} 
							}
						},
						plugins: { 
							legend: { display: false },
							tooltip: {
								backgroundColor: 'rgba(0, 0, 0, 0.8)',
								titleColor: '#fff',
								bodyColor: '#fff',
								borderColor: 'rgba(59, 130, 246, 0.5)',
								borderWidth: 1,
								cornerRadius: 8,
								displayColors: false,
								callbacks: {
									title: function(context) {
										return 'BMI: ' + context[0].parsed.y.toFixed(1);
									},
									label: function(context) {
										// Use original date for display but timestamp for positioning
										const dataIndex = context.dataIndex;
										const originalDate = bmiData[dataIndex]?.originalDate;
										const displayDate = originalDate || new Date(context.parsed.x).toLocaleDateString();
										return 'Date: ' + displayDate;
									}
								}
							}
						}
					}
				});
				console.log('ENNU Dashboard: BMI history chart initialized successfully');
			} catch (error) {
				console.error('ENNU Dashboard: Error initializing BMI history chart:', error);
				this.showChartError('Failed to load BMI history chart');
			}
		} else {
			console.log('ENNU Dashboard: No BMI data available, showing empty state');
			// Show empty state for BMI chart
			this.showEmptyChartState(bmiCtx, '⚖️', 'No BMI Data', 'Add your height and weight to see BMI trends');
		}
	}

	// Enhanced method to setup chart refresh when My Trends tab is shown
	setupChartRefreshOnTabShow() {
		console.log('ENNU Dashboard: Setting up chart refresh on tab show...');
		
		// Listen for tab changes to refresh charts
		const trendsTab = document.querySelector('a[href="#tab-my-trends"]');
		if (trendsTab) {
			trendsTab.addEventListener('click', () => {
				console.log('ENNU Dashboard: My Trends tab clicked, refreshing charts...');
				setTimeout(() => {
					// Check if charts exist and reinitialize if needed
					if (!this.chartInstances.scoreHistory) {
						console.log('ENNU Dashboard: Reinitializing score history chart...');
						this.initScoreHistoryChart();
					}
					if (!this.chartInstances.bmiHistory) {
						console.log('ENNU Dashboard: Reinitializing BMI history chart...');
						this.initBMIHistoryChart();
					}
					
					// Update charts with fresh data
					this.updateCharts();
				}, 200); // Increased delay to ensure tab is fully shown
			});
		}
		
		// Also listen for tab changes via the tab manager
		document.addEventListener('tabChanged', (event) => {
			if (event.detail && event.detail.targetId === 'tab-my-trends') {
				console.log('ENNU Dashboard: My Trends tab shown via tab manager, refreshing charts...');
				setTimeout(() => {
					this.reinitializeCharts();
				}, 300);
			}
		});
	}

	// Helper method to get real user score data
	getUserScoreData() {
		try {
			console.log('ENNU Dashboard: getUserScoreData() called');
			console.log('ENNU Dashboard: dashboardData available:', typeof dashboardData !== 'undefined');
			
			// Use data passed from PHP via wp_localize_script
			if (typeof dashboardData !== 'undefined' && dashboardData.score_history && dashboardData.score_history.length > 0) {
				console.log('ENNU Dashboard: Using server-provided score history data:', dashboardData.score_history);
				
				// Convert the data to the format expected by Chart.js
				const scoreData = dashboardData.score_history.map(entry => ({
					date: new Date(entry.date).toISOString(),
					score: parseFloat(entry.score),
					originalDate: entry.date
				}));
				
				console.log('ENNU Dashboard: Converted score data:', scoreData);
				
				// Sort by date
				scoreData.sort((a, b) => new Date(a.date) - new Date(b.date));
				
				console.log('ENNU Dashboard: Final sorted score data:', scoreData);
				return scoreData;
			} else {
				console.log('ENNU Dashboard: No server data available, dashboardData:', dashboardData);
			}
			
			// Fallback: Get current user score from data attribute or fallback to page content
			let currentScore = 0;
			const scoreElement = document.querySelector('[data-current-score]');
			if (scoreElement) {
				currentScore = parseFloat(scoreElement.dataset.currentScore) || 0;
			} else {
				// Fallback: try to find score in the page content
				const scoreText = document.querySelector('.stat-value')?.textContent;
				if (scoreText) {
					currentScore = parseFloat(scoreText) || 0;
				}
			}
			
			console.log('ENNU Dashboard: Current score from DOM:', currentScore);
			
			// Get assessment scores from the page data
			const assessmentCards = document.querySelectorAll('.assessment-trend-card');
			const scoreData = [];
			let pointCounter = 0; // Counter to ensure unique timestamps
			
			console.log('ENNU Dashboard: Found assessment cards:', assessmentCards.length);
			
			assessmentCards.forEach((card, index) => {
				const scoreElement = card.querySelector('.score-value');
				const dateElement = card.querySelector('.assessment-date');
				
				if (scoreElement && dateElement) {
					const score = parseFloat(scoreElement.textContent);
					const dateText = dateElement.textContent;
					
					console.log(`ENNU Dashboard: Assessment ${index}: score=${score}, date=${dateText}`);
					
					// Convert date text to actual date
					let date;
					if (dateText === 'Recent') {
						date = new Date();
					} else {
						date = new Date(dateText);
					}
					
					// Add to data array if valid
					if (!isNaN(score) && !isNaN(date.getTime())) {
						// Add minutes to ensure unique timestamps for same-day updates
						const uniqueDate = new Date(date.getTime() + (pointCounter * 60000)); // Add minutes
						scoreData.push({
							date: uniqueDate.toISOString(),
							score: score,
							originalDate: date.toISOString().split('T')[0] // Keep original date for display
						});
						pointCounter++;
					}
				}
			});
			
			// If no assessment data, use current score
			if (scoreData.length === 0 && currentScore > 0) {
				console.log('ENNU Dashboard: Using current score as fallback');
				scoreData.push({
					date: new Date().toISOString(),
					score: currentScore,
					originalDate: new Date().toISOString().split('T')[0]
				});
			}
			
			// If still no data, create sample data for demonstration
			if (scoreData.length === 0) {
				console.log('ENNU Dashboard: No score data found, creating sample data for demonstration');
				// Create 7 days of sample data with realistic progression
				const baseScore = 6.5;
				for (let i = 6; i >= 0; i--) {
					const date = new Date();
					date.setDate(date.getDate() - i);
					const variation = (Math.random() - 0.5) * 2; // ±1 point variation
					const score = Math.max(0, Math.min(10, baseScore + variation));
					
					scoreData.push({
						date: date.toISOString(),
						score: parseFloat(score.toFixed(1)),
						originalDate: date.toISOString().split('T')[0],
						isSample: true
					});
				}
			}
			
			// Sort by timestamp (not original date)
			scoreData.sort((a, b) => new Date(a.date) - new Date(b.date));
			
			console.log('ENNU Dashboard: Final score data:', scoreData);
			return scoreData;
		} catch (error) {
			console.error('ENNU Dashboard: Error getting user score data:', error);
			// Return sample data as fallback
			return [{
				date: new Date().toISOString(),
				score: 7.5,
				originalDate: new Date().toISOString().split('T')[0],
				isSample: true
			}];
		}
	}

	// Helper method to get real user BMI data
	getUserBMIData() {
		try {
			// Use data passed from PHP via wp_localize_script
			if (typeof dashboardData !== 'undefined' && dashboardData.bmi_history && dashboardData.bmi_history.length > 0) {
				console.log('ENNU Dashboard: Using server-provided BMI history data:', dashboardData.bmi_history);
				
				// Convert the data to the format expected by Chart.js
				const bmiData = dashboardData.bmi_history.map(entry => ({
					date: new Date(entry.date).toISOString(),
					bmi: parseFloat(entry.bmi),
					originalDate: entry.date
				}));
				
				// Sort by date
				bmiData.sort((a, b) => new Date(a.date) - new Date(b.date));
				
				return bmiData;
			}
			
			// Fallback: Get BMI data from biomarker measurements on the page
			const bmiMeasurements = document.querySelectorAll('[data-biomarker="bmi"] .biomarker-measurement');
			const bmiData = [];
			let pointCounter = 0; // Counter to ensure unique timestamps
			
			bmiMeasurements.forEach((measurement) => {
				const valueElement = measurement.querySelector('.biomarker-current-value');
				const dateElement = measurement.querySelector('.biomarker-date');
				
				if (valueElement && dateElement) {
					const bmi = parseFloat(valueElement.textContent);
					const dateText = dateElement.textContent;
					
					// Convert relative time to actual date
					let date = new Date();
					if (dateText.includes('ago')) {
						// Parse relative time like "2 days ago"
						const match = dateText.match(/(\d+)\s+(day|days|hour|hours|minute|minutes)\s+ago/);
						if (match) {
							const amount = parseInt(match[1]);
							const unit = match[2];
							if (unit.includes('day')) {
								date.setDate(date.getDate() - amount);
							} else if (unit.includes('hour')) {
								date.setHours(date.getHours() - amount);
							} else if (unit.includes('minute')) {
								date.setMinutes(date.getMinutes() - amount);
							}
						}
					}
					
					// Add to data array if valid
					if (!isNaN(bmi)) {
						// Add minutes to ensure unique timestamps for same-day updates
						const uniqueDate = new Date(date.getTime() + (pointCounter * 60000)); // Add minutes
						bmiData.push({
							date: uniqueDate.toISOString(),
							bmi: bmi,
							originalDate: date.toISOString().split('T')[0] // Keep original date for display
						});
						pointCounter++;
					}
				}
			});
			
			// If no BMI measurements found, try to get from profile info
			if (bmiData.length === 0) {
				const bmiInfoElement = document.querySelector('.info-item .info-value');
				if (bmiInfoElement) {
					const bmiText = bmiInfoElement.textContent;
					const bmi = parseFloat(bmiText);
					if (!isNaN(bmi)) {
						bmiData.push({
							date: new Date().toISOString(),
							bmi: bmi,
							originalDate: new Date().toISOString().split('T')[0]
						});
					}
				}
			}
			
			// If still no data, create sample data for demonstration
			if (bmiData.length === 0) {
				console.log('ENNU Dashboard: No BMI data found, creating sample data for demonstration');
				// Create 7 days of sample BMI data with realistic progression
				const baseBMI = 24.5;
				for (let i = 6; i >= 0; i--) {
					const date = new Date();
					date.setDate(date.getDate() - i);
					const variation = (Math.random() - 0.5) * 0.8; // ±0.4 BMI variation
					const bmi = Math.max(18, Math.min(35, baseBMI + variation));
					
					bmiData.push({
						date: date.toISOString(),
						bmi: parseFloat(bmi.toFixed(1)),
						originalDate: date.toISOString().split('T')[0],
						isSample: true
					});
				}
			}
			
			// Sort by timestamp (not original date)
			bmiData.sort((a, b) => new Date(a.date) - new Date(b.date));
			
			return bmiData;
		} catch (error) {
			console.error('ENNU Dashboard: Error getting user BMI data:', error);
			// Return sample data as fallback
			return [{
				date: new Date().toISOString(),
				bmi: 24.5,
				originalDate: new Date().toISOString().split('T')[0],
				isSample: true
			}];
		}
	}

	// Helper method to show empty chart state
	showEmptyChartState(canvas, icon, title, message) {
		const wrapper = canvas.parentElement;
		if (wrapper) {
			wrapper.innerHTML = `
				<div class="empty-state" style="text-align: center; padding: 2rem; color: rgba(255, 255, 255, 0.7);">
					<div class="empty-state-icon" style="font-size: 3rem; margin-bottom: 1rem;">${icon}</div>
					<h3 style="margin: 0 0 0.5rem 0; font-size: 1.1rem; font-weight: 600;">${title}</h3>
					<p style="margin: 0; font-size: 0.9rem; opacity: 0.8;">${message}</p>
				</div>
			`;
		}
	}

	// Helper method to show chart error
	showChartError(message) {
		console.error('ENNU Dashboard: Chart error:', message);
		const chartWrappers = document.querySelectorAll('.chart-wrapper');
		chartWrappers.forEach(wrapper => {
			if (!wrapper.querySelector('.empty-state') && !wrapper.querySelector('.chart-error')) {
				wrapper.innerHTML = `
					<div class="chart-error" style="text-align: center; padding: 2rem; color: #ef4444; background: rgba(239, 68, 68, 0.1); border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.3);">
						<div class="error-icon" style="font-size: 2rem; margin-bottom: 1rem;">⚠️</div>
						<p style="margin: 0; font-size: 0.9rem; line-height: 1.4;">${message}</p>
						<p style="margin: 0.5rem 0 0 0; font-size: 0.8rem; opacity: 0.8;">If this issue persists, please contact support.</p>
					</div>
				`;
			}
		});
	}

	// Method to update charts with new data
	updateCharts() {
		console.log('ENNU Dashboard: Updating charts with new data...');
		
		// Update score chart
		if (this.chartInstances.scoreHistory) {
			const newScoreData = this.getUserScoreData();
			this.chartInstances.scoreHistory.data.labels = newScoreData.map(d => new Date(d.date));
			this.chartInstances.scoreHistory.data.datasets[0].data = newScoreData.map(d => d.score);
			this.chartInstances.scoreHistory.update('none'); // Update without animation for immediate response
		}
		
		// Update BMI chart
		if (this.chartInstances.bmiHistory) {
			const newBMIData = this.getUserBMIData();
			this.chartInstances.bmiHistory.data.labels = newBMIData.map(d => new Date(d.date));
			this.chartInstances.bmiHistory.data.datasets[0].data = newBMIData.map(d => d.bmi);
			this.chartInstances.bmiHistory.update('none'); // Update without animation for immediate response
		}
	}

	// Method to add a new data point to charts
	addDataPoint(type, value, timestamp = null) {
		console.log('ENNU Dashboard: Adding new data point:', type, value, timestamp);
		
		const now = timestamp ? new Date(timestamp) : new Date();
		const uniqueTimestamp = new Date(now.getTime() + Math.random() * 60000); // Add random milliseconds for uniqueness
		
		if (type === 'score') {
			if (this.chartInstances.scoreHistory) {
				this.chartInstances.scoreHistory.data.labels.push(uniqueTimestamp);
				this.chartInstances.scoreHistory.data.datasets[0].data.push(value);
				this.chartInstances.scoreHistory.update('active'); // Animate the new point
			}
		} else if (type === 'bmi') {
			if (this.chartInstances.bmiHistory) {
				this.chartInstances.bmiHistory.data.labels.push(uniqueTimestamp);
				this.chartInstances.bmiHistory.data.datasets[0].data.push(value);
				this.chartInstances.bmiHistory.update('active'); // Animate the new point
			}
		}
	}

	// Enhanced method for assessment card interactions
	initAssessmentCardInteractions() {
		
		const assessmentCards = this.dashboard.querySelectorAll('.assessment-card');
		
		assessmentCards.forEach(card => {
			// Add hover effects
			card.addEventListener('mouseenter', () => {
				card.style.transform = 'translateY(-4px)';
			});
			
			card.addEventListener('mouseleave', () => {
				card.style.transform = 'translateY(0)';
			});
			
			// Add click tracking for analytics
			const actionButtons = card.querySelectorAll('.btn');
			actionButtons.forEach(button => {
				button.addEventListener('click', (e) => {
					const action = button.textContent.trim();
					const assessmentTitle = card.querySelector('.assessment-title').textContent;
				});
			});
		});
		
		// Handle collapsible sections for assessment cards
		this.dashboard.addEventListener('click', (e) => {
			const recommendationsBtn = e.target.closest('.btn-recommendations');
			const breakdownBtn = e.target.closest('.btn-breakdown');
			
			if (recommendationsBtn) {
				e.preventDefault();
				this.toggleSection(recommendationsBtn, 'recommendations-section');
			}
			
			if (breakdownBtn) {
				e.preventDefault();
				this.toggleSection(breakdownBtn, 'breakdown-section');
			}
		});
	}
	
	toggleSection(button, sectionClass) {
		
		const assessmentCard = button.closest('.assessment-card');
		const section = assessmentCard.querySelector('.' + sectionClass);
		
		if (!section) {
			return;
		}
		
		
		// Toggle button active state
		button.classList.toggle('active');
		
		// Check if section is currently expanded
		const isExpanded = section.classList.contains('expanded');
		
		if (!isExpanded) {
			section.style.display = 'block';
			section.classList.remove('hidden');
			
			// Trigger expansion animation
			requestAnimationFrame(() => {
				section.classList.add('expanded');
			});
			
			// Animate progress bars in (if it's a breakdown section)
			if (sectionClass === 'breakdown-section') {
				this.animateProgressBars(section, 'in');
			}
		} else {
			// Animate progress bars out first (if it's a breakdown section)
			if (sectionClass === 'breakdown-section') {
				this.animateProgressBars(section, 'out');
				
				// Wait for progress bar animation to complete before collapsing
				setTimeout(() => {
					section.classList.remove('expanded');
					setTimeout(() => {
						section.style.display = 'none';
						section.classList.add('hidden');
					}, 400); // Match the CSS transition duration
				}, 600); // Progress bar animation duration
			} else {
				// For non-breakdown sections, collapse immediately
				section.classList.remove('expanded');
				setTimeout(() => {
					section.style.display = 'none';
					section.classList.add('hidden');
				}, 400); // Match the CSS transition duration
			}
		}
	}
	
	animateProgressBars(section, direction) {
		const progressBars = section.querySelectorAll('.category-score-fill');
		
		progressBars.forEach((bar, index) => {
			const targetWidth = bar.style.width;
			bar.style.setProperty('--target-width', targetWidth);
			
			// Add delay for staggered animation
			setTimeout(() => {
				if (direction === 'in') {
					bar.classList.remove('animate-out');
					bar.classList.add('animate-in');
				} else {
					bar.classList.remove('animate-in');
					bar.classList.add('animate-out');
				}
			}, index * 100); // Stagger each bar by 100ms
		});
	}

	// Add method for progress bar animation
	initProgressBarAnimation() {
		const progressFill = this.dashboard.querySelector('.progress-fill');
		if (progressFill) {
			const targetWidth = progressFill.style.width;
			progressFill.style.width = '0%';
			
			setTimeout(() => {
				progressFill.style.width = targetWidth;
			}, 1000);
		}
	}

	// Enhanced initialization
	init() {
		this.initDetailsToggle();
		this.initContextualText();
		this.initScoreAnimation();
		this.initPillarAnimation();
		this.initHistoricalCharts();
		this.initHealthMapAccordion();
		this.initToggleAll();
		this.initAssessmentCardInteractions();
		this.initProgressBarAnimation();
		this.initThemeToggle();
		// Chart refresh is now handled in setupChartRefreshOnTabShow()
	}
	
	// Destroy method to clean up chart instances
	destroy() {
		console.log('ENNU Dashboard: Destroying dashboard instance');
		
		// Destroy all chart instances
		if (this.chartInstances.scoreHistory) {
			this.chartInstances.scoreHistory.destroy();
			this.chartInstances.scoreHistory = null;
		}
		
		if (this.chartInstances.bmiHistory) {
			this.chartInstances.bmiHistory.destroy();
			this.chartInstances.bmiHistory = null;
		}
		
		// Remove event listeners and clean up
		this.dashboard.ennuDashboard = null;
	}
	
	// Method to safely reinitialize charts
	reinitializeCharts() {
		console.log('ENNU Dashboard: Reinitializing charts...');
		
		// Destroy existing charts first
		if (this.chartInstances.scoreHistory) {
			this.chartInstances.scoreHistory.destroy();
			this.chartInstances.scoreHistory = null;
		}
		
		if (this.chartInstances.bmiHistory) {
			this.chartInstances.bmiHistory.destroy();
			this.chartInstances.bmiHistory = null;
		}
		
		// Reinitialize charts
		this.initScoreHistoryChart();
		this.initBMIHistoryChart();
	}
}
