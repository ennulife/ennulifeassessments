/**
 * ENNU Life Theme Manager
 * 
 * Centralized theme management system for handling dark/light mode switching
 * across all ENNU Life plugin templates and components.
 * 
 * @package ENNU Life Assessments
 * @since 62.2.30
 */

class ENNUThemeManager {
	constructor() {
		this.theme = 'light'; // Default theme
		this.storageKey = 'ennu-theme';
		this.themeAttribute = 'data-theme';
		this.mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
		this.observers = [];
		this.isInitialized = false;
		
		this.init();
	}
	
	/**
	 * Initialize the theme manager
	 */
	init() {
		if (this.isInitialized) return;
		
		// Load saved theme or detect system preference
		this.loadTheme();
		
		// Apply theme to all relevant elements
		this.applyTheme();
		
		// Set up event listeners
		this.setupEventListeners();
		
		// Set up mutation observer for dynamic content
		this.setupMutationObserver();
		
		this.isInitialized = true;
		
	}
	
	/**
	 * Load theme - Always use light mode on page refresh
	 */
	loadTheme() {
		// Always use light mode on every page refresh
		this.theme = 'light';
		
		// Clear any saved theme preference to ensure consistency
		localStorage.removeItem(this.storageKey);
	}
	
	/**
	 * Apply theme to all relevant elements
	 */
	applyTheme() {
		// Apply to document elements
		document.documentElement.setAttribute(this.themeAttribute, this.theme);
		document.body.setAttribute(this.themeAttribute, this.theme);
		
		// Apply to specific containers
		const containers = [
			'.ennu-unified-container',
			'.ennu-user-dashboard',
			'.ennu-assessment-container',
			'.ennu-consultation-container'
		];
		
		containers.forEach(selector => {
			const elements = document.querySelectorAll(selector);
			elements.forEach(element => {
				element.setAttribute(this.themeAttribute, this.theme);
			});
		});
		
		// Update toggle button states
		this.updateToggleButtons();
		
		// Trigger custom event for other components
		this.dispatchThemeChangeEvent();
	}
	
	/**
	 * Update all theme toggle buttons
	 */
	updateToggleButtons() {
		// Update unified theme toggle buttons
		const unifiedToggles = document.querySelectorAll('.ennu-theme-btn');
		unifiedToggles.forEach(toggle => {
			this.updateUnifiedToggle(toggle);
		});
		
		// Update dashboard theme toggle buttons
		const dashboardToggles = document.querySelectorAll('#theme-toggle');
		dashboardToggles.forEach(toggle => {
			this.updateDashboardToggle(toggle);
		});
	}
	
	/**
	 * Update unified theme toggle button state
	 */
	updateUnifiedToggle(toggle) {
		const sunIcon = toggle.querySelector('.ennu-sun-icon');
		const moonIcon = toggle.querySelector('.ennu-moon-icon');
		
		if (sunIcon && moonIcon) {
			if (this.theme === 'light') {
				sunIcon.style.display = 'none';
				moonIcon.style.display = 'block';
			} else {
				sunIcon.style.display = 'block';
				moonIcon.style.display = 'none';
			}
		}
	}
	
	/**
	 * Update dashboard theme toggle button state
	 */
	updateDashboardToggle(toggle) {
		const thumb = toggle.querySelector('.toggle-thumb');
		if (thumb) {
			if (this.theme === 'light') {
				thumb.style.transform = 'translateX(30px)';
			} else {
				thumb.style.transform = 'translateX(0)';
			}
		}
	}
	
	/**
	 * Toggle between themes
	 */
	toggleTheme() {
		this.theme = this.theme === 'dark' ? 'light' : 'dark';
		this.saveTheme();
		this.applyTheme();
		
	}
	
	/**
	 * Set specific theme
	 */
	setTheme(theme) {
		if (theme !== 'dark' && theme !== 'light') {
			return;
		}
		
		this.theme = theme;
		this.saveTheme();
		this.applyTheme();
		
	}
	
	/**
	 * Save theme to localStorage - Disabled to always start fresh with light mode
	 */
	saveTheme() {
		// Do not save theme preferences - always start fresh with light mode
		// localStorage.setItem(this.storageKey, this.theme);
	}
	
	/**
	 * Get current theme
	 */
	getTheme() {
		return this.theme;
	}
	
	/**
	 * Set up event listeners
	 */
	setupEventListeners() {
		// Listen for system theme changes - Disabled to always start with light mode
		// this.mediaQuery.addEventListener('change', (e) => {
		// 	// Only auto-switch if user hasn't manually set a preference
		// 	if (!localStorage.getItem(this.storageKey)) {
		// 		this.theme = e.matches ? 'dark' : 'light';
		// 		this.applyTheme();
		// 	}
		// });
		
		// Listen for toggle button clicks
		document.addEventListener('click', (e) => {
			// Unified theme toggle
			if (e.target.closest('.ennu-theme-btn')) {
				e.preventDefault();
				this.toggleTheme();
			}
			
			// Dashboard theme toggle
			if (e.target.closest('#theme-toggle')) {
				e.preventDefault();
				this.toggleTheme();
			}
		});
		
		// Listen for keyboard shortcuts
		document.addEventListener('keydown', (e) => {
			// Ctrl/Cmd + T to toggle theme
			if ((e.ctrlKey || e.metaKey) && e.key === 't') {
				e.preventDefault();
				this.toggleTheme();
			}
		});
	}
	
	/**
	 * Set up mutation observer for dynamic content
	 */
	setupMutationObserver() {
		const observer = new MutationObserver((mutations) => {
			mutations.forEach((mutation) => {
				if (mutation.type === 'childList') {
					mutation.addedNodes.forEach((node) => {
						if (node.nodeType === Node.ELEMENT_NODE) {
							// Apply theme to new elements
							this.applyThemeToElement(node);
							
							// Check for new toggle buttons
							const newToggles = node.querySelectorAll('.ennu-theme-btn, #theme-toggle');
							if (newToggles.length > 0) {
								this.updateToggleButtons();
							}
						}
					});
				}
			});
		});
		
		observer.observe(document.body, {
			childList: true,
			subtree: true
		});
		
		this.observers.push(observer);
	}
	
	/**
	 * Apply theme to a specific element and its children
	 */
	applyThemeToElement(element) {
		// Apply to the element itself if it's a theme container
		const themeContainers = [
			'.ennu-unified-container',
			'.ennu-user-dashboard',
			'.ennu-assessment-container',
			'.ennu-consultation-container'
		];
		
		themeContainers.forEach(selector => {
			if (element.matches(selector)) {
				element.setAttribute(this.themeAttribute, this.theme);
			}
		});
		
		// Apply to children
		themeContainers.forEach(selector => {
			const children = element.querySelectorAll(selector);
			children.forEach(child => {
				child.setAttribute(this.themeAttribute, this.theme);
			});
		});
	}
	
	/**
	 * Dispatch theme change event
	 */
	dispatchThemeChangeEvent() {
		const event = new CustomEvent('ennuThemeChange', {
			detail: {
				theme: this.theme,
				previousTheme: this.theme === 'dark' ? 'light' : 'dark'
			}
		});
		
		document.dispatchEvent(event);
	}
	
	/**
	 * Clean up observers
	 */
	destroy() {
		this.observers.forEach(observer => {
			observer.disconnect();
		});
		this.observers = [];
		this.isInitialized = false;
	}
}

// Initialize theme manager when DOM is ready
let ennuThemeManager;

document.addEventListener('DOMContentLoaded', () => {
	ennuThemeManager = new ENNUThemeManager();
});

// Make theme manager available globally
window.ENNUThemeManager = ENNUThemeManager;
window.ennuThemeManager = ennuThemeManager;

// Legacy function for backward compatibility
window.toggleTheme = function() {
	if (ennuThemeManager) {
		ennuThemeManager.toggleTheme();
	}
}; 