/**
 * ENNU User Dashboard - Modern JavaScript Implementation
 * Replaces jQuery dependencies with vanilla JavaScript for better performance
 */

class ENNUUserDashboard {
    constructor() {
        this.tabContents = [];
        this.currentTab = null;
        this.isAnimating = false;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeTabs();
        this.loadInitialData();
    }

    bindEvents() {
        document.addEventListener('click', this.handleClick.bind(this));
        document.addEventListener('DOMContentLoaded', this.onDOMReady.bind(this));
        
        window.addEventListener('resize', this.debounce(this.handleResize.bind(this), 250));
    }

    handleClick(event) {
        const target = event.target;
        
        if (target.matches('#ennu-recalculate-scores')) {
            event.preventDefault();
            this.handleRecalculateScores();
        } else if (target.matches('#ennu-export-data')) {
            event.preventDefault();
            this.handleExportData();
        } else if (target.matches('#ennu-sync-hubspot')) {
            event.preventDefault();
            this.handleSyncHubSpot();
        } else if (target.matches('.my-story-tab')) {
            event.preventDefault();
            this.switchTab(target);
        }
    }

    switchTab(tabElement) {
        if (this.isAnimating) return;
        
        const targetId = tabElement.getAttribute('data-target');
        const targetContent = document.getElementById(targetId);
        
        if (!targetContent || tabElement.classList.contains('my-story-tab-active')) {
            return;
        }

        this.isAnimating = true;
        
        this.hideAllTabs().then(() => {
            this.showTab(tabElement, targetContent);
            this.isAnimating = false;
        });
    }

    hideAllTabs() {
        return new Promise((resolve) => {
            const activeTabs = document.querySelectorAll('.my-story-tab-active');
            const activeContents = document.querySelectorAll('.my-story-tab-content.my-story-tab-active');
            
            activeTabs.forEach(tab => tab.classList.remove('my-story-tab-active'));
            
            let animationsComplete = 0;
            const totalAnimations = activeContents.length;
            
            if (totalAnimations === 0) {
                resolve();
                return;
            }
            
            activeContents.forEach(content => {
                content.style.opacity = '0';
                content.style.transform = 'translateY(10px)';
                
                setTimeout(() => {
                    content.classList.remove('my-story-tab-active');
                    content.style.display = 'none';
                    
                    animationsComplete++;
                    if (animationsComplete === totalAnimations) {
                        resolve();
                    }
                }, 200);
            });
        });
    }

    showTab(tabElement, contentElement) {
        tabElement.classList.add('my-story-tab-active');
        contentElement.classList.add('my-story-tab-active');
        contentElement.style.display = 'block';
        
        requestAnimationFrame(() => {
            contentElement.style.opacity = '1';
            contentElement.style.transform = 'translateY(0)';
        });
    }

    handleRecalculateScores() {
        this.showLoadingState('Recalculating scores...');
        
        this.makeRequest('/wp-admin/admin-ajax.php', {
            action: 'ennu_recalculate_scores',
            nonce: window.ennuAjax?.nonce || ''
        })
        .then(response => {
            if (response.success) {
                this.showSuccessMessage('Scores recalculated successfully!');
                this.refreshScoreDisplays();
            } else {
                throw new Error(response.data?.message || 'Failed to recalculate scores');
            }
        })
        .catch(error => {
            this.showErrorMessage(error.message);
        })
        .finally(() => {
            this.hideLoadingState();
        });
    }

    handleExportData() {
        this.showLoadingState('Preparing data export...');
        
        this.makeRequest('/wp-admin/admin-ajax.php', {
            action: 'ennu_export_user_data',
            nonce: window.ennuAjax?.nonce || ''
        })
        .then(response => {
            if (response.success && response.data?.download_url) {
                this.downloadFile(response.data.download_url, 'ennu-data-export.json');
                this.showSuccessMessage('Data exported successfully!');
            } else {
                throw new Error(response.data?.message || 'Failed to export data');
            }
        })
        .catch(error => {
            this.showErrorMessage(error.message);
        })
        .finally(() => {
            this.hideLoadingState();
        });
    }

    handleSyncHubSpot() {
        this.showLoadingState('Syncing with HubSpot...');
        
        this.makeRequest('/wp-admin/admin-ajax.php', {
            action: 'ennu_sync_hubspot',
            nonce: window.ennuAjax?.nonce || ''
        })
        .then(response => {
            if (response.success) {
                this.showSuccessMessage('HubSpot sync completed successfully!');
            } else {
                throw new Error(response.data?.message || 'Failed to sync with HubSpot');
            }
        })
        .catch(error => {
            this.showErrorMessage(error.message);
        })
        .finally(() => {
            this.hideLoadingState();
        });
    }

    makeRequest(url, data) {
        const formData = new FormData();
        Object.keys(data).forEach(key => {
            formData.append(key, data[key]);
        });

        return fetch(url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        });
    }

    downloadFile(url, filename) {
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    showLoadingState(message) {
        const loader = this.getOrCreateLoader();
        loader.querySelector('.loading-message').textContent = message;
        loader.style.display = 'flex';
    }

    hideLoadingState() {
        const loader = document.getElementById('ennu-loading-overlay');
        if (loader) {
            loader.style.display = 'none';
        }
    }

    getOrCreateLoader() {
        let loader = document.getElementById('ennu-loading-overlay');
        if (!loader) {
            loader = document.createElement('div');
            loader.id = 'ennu-loading-overlay';
            loader.innerHTML = `
                <div class="loading-spinner"></div>
                <div class="loading-message">Loading...</div>
            `;
            loader.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.7);
                display: none;
                justify-content: center;
                align-items: center;
                flex-direction: column;
                z-index: 9999;
                color: white;
            `;
            document.body.appendChild(loader);
        }
        return loader;
    }

    showSuccessMessage(message) {
        this.showNotification(message, 'success');
    }

    showErrorMessage(message) {
        this.showNotification(message, 'error');
    }

    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `ennu-notification ennu-notification-${type}`;
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 10000;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            background: ${type === 'success' ? '#10b981' : '#ef4444'};
        `;
        
        document.body.appendChild(notification);
        
        requestAnimationFrame(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        });
        
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 4000);
    }

    refreshScoreDisplays() {
        const scoreElements = document.querySelectorAll('[data-score-type]');
        scoreElements.forEach(element => {
            const scoreType = element.getAttribute('data-score-type');
            this.updateScoreElement(element, scoreType);
        });
    }

    updateScoreElement(element, scoreType) {
        this.makeRequest('/wp-admin/admin-ajax.php', {
            action: 'ennu_get_score',
            score_type: scoreType,
            nonce: window.ennuAjax?.nonce || ''
        })
        .then(response => {
            if (response.success && response.data?.score !== undefined) {
                element.textContent = response.data.score;
                element.setAttribute('data-score', response.data.score);
            }
        })
        .catch(error => {
        });
    }

    initializeTabs() {
        this.tabContents = Array.from(document.querySelectorAll('.my-story-tab-content'));
        
        const firstTab = document.querySelector('.my-story-tab');
        if (firstTab && !document.querySelector('.my-story-tab-active')) {
            firstTab.click();
        }
    }

    loadInitialData() {
        this.refreshScoreDisplays();
    }

    handleResize() {
    }

    onDOMReady() {
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new ENNUUserDashboard();
    });
} else {
    new ENNUUserDashboard();
}
