/**
 * ENNU Life User Dashboard JavaScript
 * This file controls all the interactivity for the "Bio-Metric Canvas" dashboard.
 */
document.addEventListener('DOMContentLoaded', () => {
    
    const dashboardEl = document.querySelector('.ennu-user-dashboard');
    if (dashboardEl) {
        new ENNUDashboard(dashboardEl);
    }
    
    // Initialize My Story Tabs independently to ensure they work
    const storyTabsManager = new MyStoryTabsManager();
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
        
        // Activate the first tab
        if (this.tabLinks.length > 0) {
            const firstLink = this.tabLinks[0];
            const firstTabId = firstLink.getAttribute('href');
            const firstContent = document.querySelector(firstTabId);
            
            if (firstContent) {
                firstLink.classList.add('my-story-tab-active');
                firstContent.classList.add('my-story-tab-active');
                firstContent.style.display = 'block';
                
                // Trigger animation after a brief delay
                setTimeout(() => {
                    firstContent.style.opacity = '1';
                    firstContent.style.transform = 'translateY(0)';
                }, 50);
                
                this.activeTab = firstTabId.substring(1);
            }
        }
    }
    
    setupEventListeners() {
        
        this.tabLinks.forEach((link, index) => {
            
            // Click event
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchToTab(link.getAttribute('href'));
            });
            
            // Keyboard events
            link.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.switchToTab(link.getAttribute('href'));
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    this.navigateToNextTab(1);
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    this.navigateToNextTab(-1);
                }
            });
        });
        
        // Add event listeners for switch-tab buttons within tab content
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('switch-tab') || e.target.closest('.switch-tab')) {
                e.preventDefault();
                const button = e.target.classList.contains('switch-tab') ? e.target : e.target.closest('.switch-tab');
                const targetTab = button.getAttribute('data-tab');
                if (targetTab) {
                    this.switchToTab('#' + targetTab);
                }
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
        
        const targetContent = document.querySelector(targetId);
        if (!targetContent) {
            return;
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
 * Main Dashboard Class
 * Handles all dashboard functionality
 */
class ENNUDashboard {
	constructor(dashboardElement) {
		this.dashboard = dashboardElement;
		this.init();
	}

	initThemeToggle() {
		const themeToggle = this.dashboard.querySelector('#theme-toggle');
		if (!themeToggle) return;

		// Set initial theme
		const savedTheme = localStorage.getItem('ennu-theme') || 'dark';
		this.setTheme(savedTheme);

		themeToggle.addEventListener('click', () => {
			const currentTheme = this.dashboard.getAttribute('data-theme') || 'dark';
			const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
			this.setTheme(newTheme);
		});
	}

	setTheme(theme) {
		this.dashboard.setAttribute('data-theme', theme);
		localStorage.setItem('ennu-theme', theme);
		
		// Update toggle position
		const toggleThumb = this.dashboard.querySelector('.toggle-thumb');
		if (toggleThumb) {
			if (theme === 'light') {
				toggleThumb.style.transform = 'translateX(30px)';
			} else {
				toggleThumb.style.transform = 'translateX(0)';
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
		// Check if Chart.js is available
		if (typeof Chart === 'undefined') {
			return;
		}

		this.initScoreHistoryChart();
		this.initBMIHistoryChart();
	}

	initScoreHistoryChart() {
		const scoreCtx = this.dashboard.querySelector('#scoreHistoryChart');
		if (!scoreCtx) return;

		// Sample data - replace with actual user data
		const scoreData = [
			{ date: '2024-01-01', score: 6.2 },
			{ date: '2024-02-01', score: 6.8 },
			{ date: '2024-03-01', score: 7.1 },
			{ date: '2024-04-01', score: 7.5 },
			{ date: '2024-05-01', score: 7.8 },
			{ date: '2024-06-01', score: 8.2 }
		];

		if (scoreData.length > 0) {
			new Chart(scoreCtx, {
				type: 'line',
				data: {
					labels: scoreData.map(d => new Date(d.date)),
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
								unit: 'day',
								displayFormats: {
									day: 'MMM d'
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
									return 'Date: ' + new Date(context.parsed.x).toLocaleDateString();
								}
							}
						}
					}
				}
			});
		} else {
			// Show empty state for score chart
			scoreCtx.parentElement.classList.add('loading');
			scoreCtx.parentElement.innerHTML = '<div class="empty-state"><div class="empty-state-icon">📊</div><h3>No Score Data</h3><p>Complete assessments to see your score history</p></div>';
		}
	}

	initBMIHistoryChart() {
		const bmiCtx = this.dashboard.querySelector('#bmiHistoryChart');
		if (!bmiCtx) return;

		// Sample data - replace with actual user data
		const bmiData = [
			{ date: '2024-01-01', bmi: 24.5 },
			{ date: '2024-02-01', bmi: 24.2 },
			{ date: '2024-03-01', bmi: 23.8 },
			{ date: '2024-04-01', bmi: 23.5 },
			{ date: '2024-05-01', bmi: 23.1 },
			{ date: '2024-06-01', bmi: 22.8 }
		];

		if (bmiData.length > 0) {
			new Chart(bmiCtx, {
				type: 'line',
				data: {
					labels: bmiData.map(d => new Date(d.date)),
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
								unit: 'day',
								displayFormats: {
									day: 'MMM d'
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
									return 'BMI: ' + context[0].parsed.y.toFixed(1);
								},
								label: function(context) {
									return 'Date: ' + new Date(context.parsed.x).toLocaleDateString();
								}
							}
						}
					}
				}
			});
		} else if (bmiCtx) {
			// Show empty state for BMI chart
			bmiCtx.parentElement.classList.add('loading');
			bmiCtx.parentElement.innerHTML = '<div class="empty-state"><div class="empty-state-icon">⚖️</div><h3>No BMI Data</h3><p>Update your height and weight to see BMI trends</p></div>';
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
	}
}
