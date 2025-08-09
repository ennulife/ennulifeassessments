/**
 * ENNU Life User Dashboard JavaScript
 * This file controls all the interactivity for the "Bio-Metric Canvas" dashboard.
 */

// TEST: Check if this file is loading

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
	},
	
	// Make pillar orbs visible (for testing)
	makePillarOrbsVisible: function() {
		const pillarOrbs = document.querySelectorAll('.pillar-orb');
		pillarOrbs.forEach(orb => orb.classList.add('visible'));
	},
	
	// Trigger pillar orb animations (for testing)
	animatePillarOrbs: function() {
		const pillarOrbs = document.querySelectorAll('.pillar-orb');
		const mainScoreOrb = document.querySelector('.main-score-orb');
		
		
		// Animate main score orb first
		if (mainScoreOrb) {
			mainScoreOrb.classList.add('loaded');
		}
		
		// Animate pillar orbs with staggered delay
		pillarOrbs.forEach((orb, index) => {
			setTimeout(() => {
				orb.classList.add('loaded');
			}, index * 200 + 500);
		});
	}
};

// Global function to manually make pillar orbs visible (for testing)
window.makePillarOrbsVisible = function() {
	const pillarOrbs = document.querySelectorAll('.pillar-orb');
	
	pillarOrbs.forEach((orb, index) => {
		orb.classList.add('visible');
	});
	
	return pillarOrbs.length;
};

// Prevent multiple initializations
let dashboardInitialized = false;

/**
 * Initialize biomarker panel styles
 * Adds visual feedback for biomarker panels
 */
function initializeBiomarkerPanelStyles() {
    // Add visual feedback for clickable items
    const existingStyle = document.getElementById('biomarker-panel-styles');
    if (!existingStyle) {
        const style = document.createElement('style');
        style.id = 'biomarker-panel-styles';
        style.textContent = `
            .biomarker-list-item {
                cursor: pointer !important;
                transition: background-color 0.2s ease;
                user-select: none;
                position: relative !important;
                z-index: 10 !important;
                pointer-events: auto !important;
            }
            .biomarker-list-item:hover {
                background-color: rgba(59, 130, 246, 0.1) !important;
            }
            .biomarker-list-item.expanded {
                background-color: rgba(59, 130, 246, 0.05);
            }
            .biomarker-measurement-container {
                transition: all 0.3s ease;
            }
            /* Ensure panel content doesn't block clicks */
            .panel-content {
                position: relative;
                z-index: 1;
            }
            /* Make sure biomarker vectors don't block clicks */
            .biomarker-vector-category {
                position: relative;
            }
        `;
        document.head.appendChild(style);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    
    // Prevent multiple initializations
    if (dashboardInitialized) {
        return;
    }
    
    // Verify toggle functions are available
    console.log('Toggle functions available:', {
        togglePanel: typeof window.togglePanel,
        toggleBiomarkerMeasurements: typeof window.toggleBiomarkerMeasurements,
        toggleVectorCategory: typeof window.toggleVectorCategory
    });
    
    const dashboardEl = document.querySelector('.ennu-user-dashboard');
    if (dashboardEl) {
        
        // Check if user is logged in by looking for logged-out container
        const loggedOutContainer = dashboardEl.querySelector('.dashboard-logged-out-container');
        if (loggedOutContainer) {
            // Don't initialize charts for logged out users
            return;
        }
        
        // Destroy existing dashboard instance if it exists
        if (dashboardEl.ennuDashboard) {
            dashboardEl.ennuDashboard.destroy();
        }
        
        new ENNUDashboard(dashboardEl);
        dashboardInitialized = true;
    } else {
    }
    
    // Initialize My Story Tabs only if dashboard exists
    const storyTabsContainer = document.querySelector('.my-story-tabs');
    if (storyTabsContainer) {
        const storyTabsManager = new MyStoryTabsManager();
    } else {
    }
    
    // Initialize biomarker panel styles
    initializeBiomarkerPanelStyles();
});



/**
 * My Story Tabs Manager
 * Handles tab navigation for the My Story section
 */
class MyStoryTabsManager {
    constructor() {
        this.activeTab = 'tab-my-assessments';
        this.tabContainer = null;
        this.tabLinks = [];
        this.tabContents = [];
        
        this.init();
    }
    
    init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initialize());
        } else {
            this.initialize();
        }
    }
    
    initialize() {
        this.tabContainer = document.querySelector('.my-story-tabs');
        
        if (!this.tabContainer) {
            return;
        }
        
        
        this.tabLinks = this.tabContainer.querySelectorAll('.my-story-tab-nav a');
        this.tabContents = this.tabContainer.querySelectorAll('.my-story-tab-content');
        
        
        if (this.tabLinks.length === 0 || this.tabContents.length === 0) {
            return;
        }
        
        this.setupEventListeners();
        this.addAccessibilityAttributes();
        
        // Ensure first tab is active by default
        this.activateFirstTab();
        
    }
    
    activateFirstTab() {
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
        
        
        if (biomarkersLink && biomarkersContent) {
            biomarkersLink.classList.add('my-story-tab-active');
            biomarkersContent.classList.add('my-story-tab-active');
            biomarkersContent.style.display = 'block';
            
            // Trigger animation after a brief delay
            setTimeout(() => {
                biomarkersContent.style.opacity = '1';
                biomarkersContent.style.transform = 'translateY(0)';
                
                // Initialize biomarker panel styles since this tab is active by default
                initializeBiomarkerPanelStyles();
            }, 50);
            
            this.activeTab = biomarkersTabId.substring(1);
        } else {
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
                }
            }
        }
    }
    
    setupEventListeners() {
        
        this.tabLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href');
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
        
        if (!targetId.startsWith('#')) {
            targetId = '#' + targetId;
        }
        
        
        let targetContent = document.querySelector(targetId);
        if (!targetContent) {
            // Try alternative selector for symptoms tab
            if (targetId === '#tab-my-symptoms') {
                const altContent = document.querySelector('.my-story-tab-content[id*="symptoms"]');
                if (altContent) {
                    targetContent = altContent;
                }
            }
            if (!targetContent) {
                return;
            }
        }
        
        
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
            activeLink.classList.add('my-story-tab-active');
            activeLink.setAttribute('aria-selected', 'true');
            activeLink.setAttribute('tabindex', '0');
        } else {
            // Try to find link by text content for symptoms
            if (targetId === '#tab-my-symptoms') {
                const symptomsLink = Array.from(this.tabLinks).find(link => 
                    link.textContent.toLowerCase().includes('symptoms')
                );
                if (symptomsLink) {
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
        
        // Trigger custom event
        this.triggerTabChangeEvent(targetId, activeLink, targetContent);
        
        // Special handling for symptoms tab
        if (targetId === '#tab-my-symptoms') {
            this.updateSymptomsDisplay();
        }
        
        // Special handling for biomarkers tab - reinitialize click handlers
        if (targetId === '#tab-my-biomarkers') {
            setTimeout(() => {
                initializeBiomarkerPanelStyles();
            }, 100);
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
		const pillarOrbs = this.dashboard.querySelectorAll('.pillar-orb');
		
		if (!contextualText) return;

		const insights = {
			'0-3': 'Your ENNU Life Score indicates significant opportunities for health optimization. Focus on completing assessments and implementing foundational lifestyle changes.',
			'3-5': 'You\'re making progress! Continue with your health journey by completing more assessments and following personalized recommendations.',
			'5-7': 'Great work! Your health foundation is solid. Focus on fine-tuning specific areas and maintaining consistency.',
			'7-9': 'Excellent! You\'re approaching optimal health. Continue your current practices and explore advanced optimization strategies.',
			'9-10': 'Outstanding! You\'ve achieved exceptional health optimization. Maintain your practices and consider becoming a health mentor.'
		};

		const updateContextualText = (text) => {
			contextualText.textContent = text;
		};

		// Main score orb hover
		if (mainScoreOrb) {
			const handleMainScoreHover = () => {
				const score = parseFloat(mainScoreOrb.getAttribute('data-score') || 0);
				let insight = insights['0-3'];
				
				if (score >= 9) insight = insights['9-10'];
				else if (score >= 7) insight = insights['7-9'];
				else if (score >= 5) insight = insights['5-7'];
				else if (score >= 3) insight = insights['3-5'];
				
				updateContextualText(insight);
				contextualText.classList.add('visible');
			};

			mainScoreOrb.addEventListener('mouseenter', handleMainScoreHover);
			mainScoreOrb.addEventListener('mouseleave', () => {
				contextualText.classList.remove('visible');
			});

			// Initial update for main score
			handleMainScoreHover();
		}

		// Individual pillar orb hover
		pillarOrbs.forEach(orb => {
			orb.addEventListener('mouseenter', () => {
				const insight = orb.getAttribute('data-insight');
				if (insight) {
					updateContextualText(insight);
					contextualText.classList.add('visible');
				}
			});

			orb.addEventListener('mouseleave', () => {
				contextualText.classList.remove('visible');
			});
		});
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
		
		// Get all pillar orbs
		const pillarOrbs = this.dashboard.querySelectorAll('.pillar-orb');
		
		if (pillarOrbs.length === 0) {
			// Retry after a short delay
			setTimeout(() => {
				this.initPillarAnimation();
			}, 500);
			return;
		}
		
		// Add visible class with staggered animation
		pillarOrbs.forEach((orb, index) => {
			setTimeout(() => {
				orb.classList.add('visible');
			}, index * 200);
		});
		
		// Fallback: Ensure all orbs are visible after 3 seconds regardless
		setTimeout(() => {
			pillarOrbs.forEach((orb, index) => {
				if (!orb.classList.contains('visible')) {
					orb.classList.add('visible');
				}
			});
		}, 3000);
		
		// Additional fallback: Check every 2 seconds if any orbs are still invisible
		const visibilityCheck = setInterval(() => {
			const invisibleOrbs = this.dashboard.querySelectorAll('.pillar-orb:not(.visible)');
			if (invisibleOrbs.length === 0) {
				clearInterval(visibilityCheck);
			} else {
				invisibleOrbs.forEach(orb => orb.classList.add('visible'));
			}
		}, 2000);
	}

	initHistoricalCharts() {
		
		// Check if Chart.js is available
		if (typeof Chart === 'undefined') {
			this.showChartError('Chart.js library is not available. Please refresh the page and try again.');
			return;
		}

		// Check if time adapter is available
		if (typeof Chart.adapters === 'undefined' || typeof Chart.adapters.date === 'undefined') {
		}

		// Check if we're in a logged-in state by looking for chart containers
		const chartContainers = document.querySelectorAll('.chart-wrapper');
		if (chartContainers.length === 0) {
			return;
		}

		// Initialize charts with real data
		this.initScoreHistoryChart();
		this.initBMIHistoryChart();
		
		// Set up chart refresh on tab show
		this.setupChartRefreshOnTabShow();
	}

	initScoreHistoryChart() {
		const scoreCtx = this.dashboard.querySelector('#scoreHistoryChart');
		if (!scoreCtx) {
			this.showChartError('Score history chart not available. Please log in to view your health trends.');
			return;
		}

		// Destroy existing chart instance if it exists
		if (this.chartInstances.scoreHistory) {
			this.chartInstances.scoreHistory.destroy();
			this.chartInstances.scoreHistory = null;
		}

		// Get real user score data
		const scoreData = this.getUserScoreData();

		if (scoreData && scoreData.length > 0) {
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
				
				
				this.chartInstances.scoreHistory = new Chart(scoreCtx, chartConfig);
			} catch (error) {
				this.showChartError('Failed to load score history chart');
			}
		} else {
			// Show empty state for score chart
			this.showEmptyChartState(scoreCtx, '📊', 'No Score Data', 'Complete assessments to see your score history');
		}
	}

	initBMIHistoryChart() {
		const bmiCtx = this.dashboard.querySelector('#bmiHistoryChart');
		if (!bmiCtx) {
			this.showChartError('BMI history chart not available. Please log in to view your health trends.');
			return;
		}

		// Destroy existing chart instance if it exists
		if (this.chartInstances.bmiHistory) {
			this.chartInstances.bmiHistory.destroy();
			this.chartInstances.bmiHistory = null;
		}

		// Get real user BMI data
		const bmiData = this.getUserBMIData();

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
			} catch (error) {
				this.showChartError('Failed to load BMI history chart');
			}
		} else {
			// Show empty state for BMI chart
			this.showEmptyChartState(bmiCtx, '⚖️', 'No BMI Data', 'Add your height and weight to see BMI trends');
		}
	}

	// Enhanced method to setup chart refresh when My Trends tab is shown
	setupChartRefreshOnTabShow() {
		
		// Listen for tab changes to refresh charts
		const trendsTab = document.querySelector('a[href="#tab-my-trends"]');
		if (trendsTab) {
			trendsTab.addEventListener('click', () => {
				setTimeout(() => {
					// Check if charts exist and reinitialize if needed
					if (!this.chartInstances.scoreHistory) {
						this.initScoreHistoryChart();
					}
					if (!this.chartInstances.bmiHistory) {
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
				setTimeout(() => {
					this.reinitializeCharts();
				}, 300);
			}
		});
	}

	// Helper method to get real user score data
	getUserScoreData() {
		try {
			
			// Use data passed from PHP via wp_localize_script
			if (typeof dashboardData !== 'undefined' && dashboardData.score_history && dashboardData.score_history.length > 0) {
				
				// Convert the data to the format expected by Chart.js
				const scoreData = dashboardData.score_history.map(entry => ({
					date: new Date(entry.date).toISOString(),
					score: parseFloat(entry.score),
					originalDate: entry.date
				}));
				
				
				// Sort by date
				scoreData.sort((a, b) => new Date(a.date) - new Date(b.date));
				
				return scoreData;
			} else {
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
			
			
			// Get assessment scores from the page data
			const assessmentCards = document.querySelectorAll('.assessment-trend-card');
			const scoreData = [];
			let pointCounter = 0; // Counter to ensure unique timestamps
			
			
			assessmentCards.forEach((card, index) => {
				const scoreElement = card.querySelector('.score-value');
				const dateElement = card.querySelector('.assessment-date');
				
				if (scoreElement && dateElement) {
					const score = parseFloat(scoreElement.textContent);
					const dateText = dateElement.textContent;
					
					
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
				scoreData.push({
					date: new Date().toISOString(),
					score: currentScore,
					originalDate: new Date().toISOString().split('T')[0]
				});
			}
			
			// If no data available, show empty state instead of sample data
			if (scoreData.length === 0) {
				return this.showEmptyScoreState();
			}
			
			// Sort by timestamp (not original date)
			scoreData.sort((a, b) => new Date(a.date) - new Date(b.date));
			
			return scoreData;
		} catch (error) {
			// Return empty array to show empty state
			return [];
		}
	}

	// Helper method to get real user BMI data
	getUserBMIData() {
		try {
			// Use data passed from PHP via wp_localize_script
			if (typeof dashboardData !== 'undefined' && dashboardData.bmi_history && dashboardData.bmi_history.length > 0) {
				
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
			
			// If no data available, return empty array for empty state handling
			if (bmiData.length === 0) {
				return [];
			}
			
			// Sort by timestamp (not original date)
			bmiData.sort((a, b) => new Date(a.date) - new Date(b.date));
			
			return bmiData;
		} catch (error) {
			// Return empty array to show empty state instead of fake data
			return [];
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

	// Helper method to show empty score state
	showEmptyScoreState() {
		const scoreCanvas = document.getElementById('scoreHistoryChart');
		if (scoreCanvas) {
			this.showEmptyChartState(
				scoreCanvas,
				'📊',
				'No Assessment Data',
				'Complete your first assessment to see your health scores over time.'
			);
		}
		return false; // Indicates no data to process
	}

	// Method to update charts with new data
	updateCharts() {
		
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
		
		// Handle regular progress bars
		const progressFill = this.dashboard.querySelector('.progress-fill');
		if (progressFill) {
			const targetWidth = progressFill.style.width;
			progressFill.style.width = '0%';
			
			setTimeout(() => {
				progressFill.style.width = targetWidth;
			}, 1000);
		}
		
		// Handle pillar orb progress bars (for results page)
		const pillarOrbs = this.dashboard.querySelectorAll('.pillar-orb');
		if (pillarOrbs.length > 0) {
			
			// Add loaded class with staggered delay to trigger CSS animations
			pillarOrbs.forEach((orb, index) => {
				setTimeout(() => {
					orb.classList.add('loaded');
				}, index * 200 + 500); // 500ms initial delay, then 200ms between each orb
			});
		}
		
		// Handle main score orb (center orb on results page)
		const mainScoreOrb = this.dashboard.querySelector('.main-score-orb');
		if (mainScoreOrb) {
			setTimeout(() => {
				mainScoreOrb.classList.add('loaded');
			}, 300); // Slightly earlier than pillar orbs
		}
	}

	// Enhanced initialization
	init() {
		
		// Wait for DOM to be fully ready
		setTimeout(() => {
			this.initDetailsToggle();
			this.initContextualText();
			this.initScoreAnimation();
			
			// Initialize pillar animation with a slight delay to ensure DOM is ready
			setTimeout(() => {
				this.initPillarAnimation();
			}, 100);
			
			this.initHistoricalCharts();
			this.initHealthMapAccordion();
			this.initToggleAll();
			this.initAssessmentCardInteractions();
			this.initProgressBarAnimation();
			this.initThemeToggle();
			
		}, 50);
	}
	
	// Destroy method to clean up chart instances
	destroy() {
		
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
