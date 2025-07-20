/**
 * ENNU Mobile JavaScript
 * Enhanced mobile experience and touch interactions
 */

(function() {
    'use strict';
    
    const ENNUMobile = {
        
        init: function() {
            if (!this.isMobile()) return;
            
            this.setupTouchInteractions();
            this.setupSwipeGestures();
            this.setupMobileNavigation();
            this.setupFormOptimizations();
            this.setupViewportHandling();
            this.setupPerformanceOptimizations();
        },
        
        isMobile: function() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
                   window.innerWidth <= 768;
        },
        
        isTouch: function() {
            return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        },
        
        setupTouchInteractions: function() {
            if (!this.isTouch()) return;
            
            document.body.classList.add('touch-device');
            
            const touchElements = document.querySelectorAll('.goal-pill, .ennu-btn, .ennu-card');
            touchElements.forEach(function(element) {
                element.addEventListener('touchstart', function() {
                    this.classList.add('touch-active');
                });
                
                element.addEventListener('touchend', function() {
                    this.classList.remove('touch-active');
                });
                
                element.addEventListener('touchcancel', function() {
                    this.classList.remove('touch-active');
                });
            });
        },
        
        setupSwipeGestures: function() {
            const swipeElements = document.querySelectorAll('.swipe-enabled');
            
            swipeElements.forEach(function(element) {
                let startX = 0;
                let startY = 0;
                let distX = 0;
                let distY = 0;
                let threshold = 50;
                let restraint = 100;
                
                element.addEventListener('touchstart', function(e) {
                    const touchobj = e.changedTouches[0];
                    startX = touchobj.pageX;
                    startY = touchobj.pageY;
                });
                
                element.addEventListener('touchend', function(e) {
                    const touchobj = e.changedTouches[0];
                    distX = touchobj.pageX - startX;
                    distY = touchobj.pageY - startY;
                    
                    if (Math.abs(distX) >= threshold && Math.abs(distY) <= restraint) {
                        const direction = distX < 0 ? 'left' : 'right';
                        ENNUMobile.handleSwipe(element, direction);
                    }
                });
            });
        },
        
        handleSwipe: function(element, direction) {
            const event = new CustomEvent('ennu-swipe', {
                detail: { direction: direction, element: element }
            });
            element.dispatchEvent(event);
            
            if (element.classList.contains('ennu-carousel')) {
                if (direction === 'left') {
                    this.nextSlide(element);
                } else {
                    this.prevSlide(element);
                }
            }
        },
        
        nextSlide: function(carousel) {
            const slides = carousel.querySelectorAll('.carousel-slide');
            const activeSlide = carousel.querySelector('.carousel-slide.active');
            const currentIndex = Array.from(slides).indexOf(activeSlide);
            const nextIndex = (currentIndex + 1) % slides.length;
            
            activeSlide.classList.remove('active');
            slides[nextIndex].classList.add('active');
        },
        
        prevSlide: function(carousel) {
            const slides = carousel.querySelectorAll('.carousel-slide');
            const activeSlide = carousel.querySelector('.carousel-slide.active');
            const currentIndex = Array.from(slides).indexOf(activeSlide);
            const prevIndex = currentIndex === 0 ? slides.length - 1 : currentIndex - 1;
            
            activeSlide.classList.remove('active');
            slides[prevIndex].classList.add('active');
        },
        
        setupMobileNavigation: function() {
            const nav = document.querySelector('.ennu-nav');
            if (!nav) return;
            
            const navItems = nav.querySelectorAll('.ennu-nav-item');
            navItems.forEach(function(item) {
                item.addEventListener('click', function(e) {
                    navItems.forEach(function(navItem) {
                        navItem.classList.remove('active');
                    });
                    this.classList.add('active');
                });
            });
            
            let lastScrollTop = 0;
            window.addEventListener('scroll', function() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    nav.classList.add('nav-hidden');
                } else {
                    nav.classList.remove('nav-hidden');
                }
                
                lastScrollTop = scrollTop;
            });
        },
        
        setupFormOptimizations: function() {
            const forms = document.querySelectorAll('form');
            
            forms.forEach(function(form) {
                const inputs = form.querySelectorAll('input, select, textarea');
                
                inputs.forEach(function(input) {
                    if (input.type === 'email') {
                        input.setAttribute('inputmode', 'email');
                    } else if (input.type === 'tel') {
                        input.setAttribute('inputmode', 'tel');
                    } else if (input.type === 'number') {
                        input.setAttribute('inputmode', 'numeric');
                    }
                    
                    input.addEventListener('focus', function() {
                        setTimeout(function() {
                            if (document.activeElement === input) {
                                input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }, 300);
                    });
                });
            });
        },
        
        setupViewportHandling: function() {
            let viewportHeight = window.innerHeight;
            
            window.addEventListener('resize', function() {
                const currentHeight = window.innerHeight;
                const heightDifference = Math.abs(currentHeight - viewportHeight);
                
                if (heightDifference > 150) {
                    document.body.classList.toggle('keyboard-open', currentHeight < viewportHeight);
                }
                
                viewportHeight = currentHeight;
            });
            
            const metaViewport = document.querySelector('meta[name="viewport"]');
            if (metaViewport) {
                metaViewport.setAttribute('content', 
                    'width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes'
                );
            }
        },
        
        setupPerformanceOptimizations: function() {
            const images = document.querySelectorAll('img');
            
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            if (img.dataset.src) {
                                img.src = img.dataset.src;
                                img.removeAttribute('data-src');
                            }
                            imageObserver.unobserve(img);
                        }
                    });
                });
                
                images.forEach(function(img) {
                    if (img.dataset.src) {
                        imageObserver.observe(img);
                    }
                });
            }
            
            if ('requestIdleCallback' in window) {
                requestIdleCallback(function() {
                    ENNUMobile.preloadCriticalResources();
                });
            } else {
                setTimeout(function() {
                    ENNUMobile.preloadCriticalResources();
                }, 2000);
            }
        },
        
        preloadCriticalResources: function() {
            const criticalImages = [
                '/assets/images/logo.png',
                '/assets/images/dashboard-bg.jpg'
            ];
            
            criticalImages.forEach(function(src) {
                const link = document.createElement('link');
                link.rel = 'preload';
                link.as = 'image';
                link.href = src;
                document.head.appendChild(link);
            });
        },
        
        showLoadingOverlay: function(message = 'Loading...') {
            const overlay = document.createElement('div');
            overlay.className = 'ennu-loading-overlay';
            overlay.innerHTML = `
                <div class="ennu-spinner"></div>
                <p>${message}</p>
            `;
            overlay.setAttribute('aria-label', message);
            overlay.setAttribute('role', 'status');
            
            document.body.appendChild(overlay);
            return overlay;
        },
        
        hideLoadingOverlay: function() {
            const overlay = document.querySelector('.ennu-loading-overlay');
            if (overlay) {
                overlay.remove();
            }
        },
        
        vibrate: function(pattern = [100]) {
            if ('vibrate' in navigator) {
                navigator.vibrate(pattern);
            }
        },
        
        getDeviceInfo: function() {
            return {
                isMobile: this.isMobile(),
                isTouch: this.isTouch(),
                viewportWidth: window.innerWidth,
                viewportHeight: window.innerHeight,
                devicePixelRatio: window.devicePixelRatio || 1,
                orientation: window.orientation || 0,
                connection: navigator.connection ? {
                    effectiveType: navigator.connection.effectiveType,
                    downlink: navigator.connection.downlink,
                    rtt: navigator.connection.rtt
                } : null
            };
        }
    };
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            ENNUMobile.init();
        });
    } else {
        ENNUMobile.init();
    }
    
    window.ENNUMobile = ENNUMobile;
    
})();
