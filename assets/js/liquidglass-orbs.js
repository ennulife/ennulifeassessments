/**
 * LiquidGlass Orbs - Optimized 5-Orb System
 * 
 * High-performance JavaScript with minimal DOM manipulation
 * 
 * @package ENNU_Life
 * @since 64.56.0
 */

(function() {
    'use strict';

    // Configuration
    const CONFIG = {
        selectors: {
            orbs: '.orb-tile',
            score: '.orb-score, .pillar-orb-score, .ennu-score-value, .main-score-value',
            progress: '.orb-progress'
        },
        animation: {
            duration: 1500,
            easing: 0.08,
            stagger: 200
        },
        debug: false
    };

    // State management
    const state = {
        initialized: false,
        orbs: [],
        rafId: null
    };

    /**
     * Initialize system when DOM is ready
     */
    function init() {
        if (state.initialized) return;

        console.log('🔮 Initializing Optimized LiquidGlass Orbs');
        
        // Find and setup orbs
        const orbElements = document.querySelectorAll(CONFIG.selectors.orbs);
        
        if (orbElements.length === 0) {
            console.warn('⚠️ No orb elements found');
            return;
        }

        // Process each orb
        orbElements.forEach((element, index) => {
            const orb = setupOrb(element, index);
            if (orb) {
                state.orbs.push(orb);
            }
        });

        // Start animations
        animateProgress();
        setupInteractions();
        
        state.initialized = true;
        console.log(`✅ Initialized ${state.orbs.length} orbs`);
    }

    /**
     * Setup individual orb with optimized structure
     */
    function setupOrb(element, index) {
        try {
            // Get score from multiple sources (prioritized)
            const scoreElement = element.querySelector(CONFIG.selectors.score);
            const dataScore = parseFloat(element.dataset.score || element.getAttribute('data-score'));
            const styleScore = parseFloat(getComputedStyle(element).getPropertyValue('--score-percent'));
            const textScore = scoreElement ? parseFloat(scoreElement.textContent) : 0;
            
            // Determine final score (0-100 scale)
            let score = dataScore || (styleScore / 10) || textScore || 0;
            if (score <= 10) score *= 10; // Convert 0-10 to 0-100
            score = Math.max(0, Math.min(100, score));

            // Create optimized HTML structure
            ensureOptimizedStructure(element, scoreElement, score, index);

            // Return orb object
            return {
                element,
                scoreElement,
                progressElement: element.querySelector(CONFIG.selectors.progress),
                targetScore: score,
                currentScore: 0,
                index
            };

        } catch (error) {
            console.error('Error setting up orb:', error);
            return null;
        }
    }

    /**
     * Ensure orb has optimized HTML structure
     */
    function ensureOptimizedStructure(element, scoreElement, score, index) {
        // Add progress ring if missing
        if (!element.querySelector(CONFIG.selectors.progress)) {
            const progress = document.createElement('div');
            progress.className = 'orb-progress';
            element.appendChild(progress);
        }

        // Wrap content if needed
        if (scoreElement && !scoreElement.closest('.orb-content')) {
            const content = document.createElement('div');
            content.className = 'orb-content';
            
            // Move existing content into wrapper
            const existingContent = element.querySelector('.pillar-orb-content, .main-score-text, .ennu-score-text');
            if (existingContent) {
                content.appendChild(existingContent);
            } else {
                content.innerHTML = `
                    <div class="orb-score">${(score / 10).toFixed(1)}</div>
                    <div class="orb-label">${getOrbLabel(element)}</div>
                `;
            }
            
            element.appendChild(content);
        }

        // Set CSS custom properties
        element.style.setProperty('--progress', score);
        
        // Add debug attribute if enabled
        if (CONFIG.debug) {
            element.setAttribute('data-debug', 'true');
        }
    }

    /**
     * Get appropriate label for orb
     */
    function getOrbLabel(element) {
        if (element.classList.contains('mind')) return 'Mind';
        if (element.classList.contains('body')) return 'Body';
        if (element.classList.contains('lifestyle')) return 'Lifestyle';
        if (element.classList.contains('aesthetics')) return 'Aesthetics';
        if (element.classList.contains('center')) return 'Health Score';
        return 'Score';
    }

    /**
     * Animate progress with optimized RAF
     */
    function animateProgress() {
        if (state.orbs.length === 0) return;

        const animate = () => {
            let needsUpdate = false;

            state.orbs.forEach((orb, index) => {
                const diff = orb.targetScore - orb.currentScore;
                
                if (Math.abs(diff) > 0.1) {
                    needsUpdate = true;
                    orb.currentScore += diff * CONFIG.animation.easing;
                    
                    // Update CSS custom property
                    orb.element.style.setProperty('--progress', orb.currentScore);
                    
                    // Update text if score element exists
                    if (orb.scoreElement) {
                        orb.scoreElement.textContent = (orb.currentScore / 10).toFixed(1);
                    }
                }
            });

            if (needsUpdate) {
                state.rafId = requestAnimationFrame(animate);
            }
        };

        // Start with staggered delay
        setTimeout(() => {
            state.rafId = requestAnimationFrame(animate);
        }, 500);
    }

    /**
     * Setup optimized interactions
     */
    function setupInteractions() {
        state.orbs.forEach(orb => {
            // Optimized hover with passive listeners
            orb.element.addEventListener('mouseenter', () => {
                orb.element.style.transform = 'translateY(-4px) scale(1.02)';
            }, { passive: true });

            orb.element.addEventListener('mouseleave', () => {
                orb.element.style.transform = '';
            }, { passive: true });

            // Touch support
            orb.element.addEventListener('touchstart', () => {
                orb.element.style.transform = 'translateY(-2px) scale(0.98)';
            }, { passive: true });

            orb.element.addEventListener('touchend', () => {
                setTimeout(() => {
                    orb.element.style.transform = '';
                }, 150);
            }, { passive: true });
        });
    }

    /**
     * Update orb score dynamically
     */
    function updateOrbScore(orbIndex, newScore) {
        if (!state.orbs[orbIndex]) return;

        const orb = state.orbs[orbIndex];
        orb.targetScore = Math.max(0, Math.min(100, newScore));
        
        // Restart animation if needed
        if (!state.rafId) {
            animateProgress();
        }
    }

    /**
     * Cleanup on page unload
     */
    function cleanup() {
        if (state.rafId) {
            cancelAnimationFrame(state.rafId);
            state.rafId = null;
        }
        state.orbs = [];
        state.initialized = false;
    }

    // Public API
    window.LiquidGlassOrbs = {
        init,
        updateScore: updateOrbScore,
        cleanup,
        getState: () => ({ ...state })
    };

    // Auto-initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Cleanup on unload
    window.addEventListener('beforeunload', cleanup);

})();

/**
 * Legacy compatibility layer
 */
window.initLiquidGlassOrbs = function() {
    if (window.LiquidGlassOrbs) {
        window.LiquidGlassOrbs.init();
    }
};

window.updateOrbScore = function(element, score) {
    if (window.LiquidGlassOrbs && element) {
        const orbs = window.LiquidGlassOrbs.getState().orbs;
        const index = orbs.findIndex(orb => orb.element === element);
        if (index >= 0) {
            window.LiquidGlassOrbs.updateScore(index, score * 10);
        }
    }
};