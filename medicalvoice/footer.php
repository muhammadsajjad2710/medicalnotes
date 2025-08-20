            </div> <!-- End of container -->
        </main> <!-- End of content-area -->
    </div> <!-- End of app-layout -->

    <!-- JavaScript for MedicalVoice Module -->
<script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebarMenu');
            const contentArea = document.getElementById('contentArea');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            // Sidebar toggle functionality
            sidebarToggle.addEventListener('click', function() {
                if (sidebar.classList.contains('show')) {
                    // Hide sidebar
                    sidebar.classList.remove('show');
                    this.setAttribute('aria-label', 'Show sidebar');
                } else {
                    // Show sidebar
                    sidebar.classList.add('show');
                    this.setAttribute('aria-label', 'Hide sidebar');
                }
            });

            // Handle mobile sidebar behavior
            function handleMobileSidebar() {
                if (window.innerWidth <= 1024) {
                    // On mobile, clicking outside sidebar should close it
                    document.addEventListener('click', function(e) {
                        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                            sidebar.classList.remove('show');
                        }
                    });
                }
            }

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 1024) {
                    sidebar.classList.remove('show');
                }
            });

            // Initialize mobile sidebar
            handleMobileSidebar();

            // Keyboard navigation for sidebar
    document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                    sidebarToggle.focus();
                }
            });

            // Active navigation highlighting
            function setActiveNav() {
                const navLinks = document.querySelectorAll('.nav-link');
                const currentHash = window.location.hash || '#upload';
                
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === currentHash) {
                        link.classList.add('active');
                    }
                });
            }

            // Set initial active state
            setActiveNav();

            // Update active state on hash change
            window.addEventListener('hashchange', setActiveNav);

            // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                        
                        // Update URL hash
                        window.location.hash = this.getAttribute('href');
                        
                        // Update active navigation
                        setActiveNav();
            }
        });
    });

            // Enhanced accessibility
            function enhanceAccessibility() {
                // Add ARIA labels and roles
                const navLinks = document.querySelectorAll('.nav-link');
                navLinks.forEach((link, index) => {
                    link.setAttribute('role', 'menuitem');
                    link.setAttribute('aria-label', link.textContent.trim());
                    
                    // Add keyboard navigation
                    link.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            this.click();
                        }
                    });
                });

                // Add focus management
                const focusableElements = sidebar.querySelectorAll('a, button, input, textarea, select');
                const firstFocusable = focusableElements[0];
                const lastFocusable = focusableElements[focusableElements.length - 1];

                // Trap focus within sidebar when open on mobile
                sidebar.addEventListener('keydown', function(e) {
                    if (e.key === 'Tab') {
                        if (e.shiftKey) {
                            if (document.activeElement === firstFocusable) {
                                e.preventDefault();
                                lastFocusable.focus();
                            }
                        } else {
                            if (document.activeElement === lastFocusable) {
                                e.preventDefault();
                                firstFocusable.focus();
                            }
                        }
            }
        });
    }

            // Initialize accessibility enhancements
            enhanceAccessibility();

            // Performance optimization
            function optimizePerformance() {
                // Lazy load images
                const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                            observer.unobserve(img);
                }
            });
        });

                images.forEach(img => imageObserver.observe(img));

                // Preload critical resources
                const criticalResources = [
                    '/logo.jpeg',
                    '../design-system.css'
                ];

                criticalResources.forEach(resource => {
                    const link = document.createElement('link');
                    link.rel = 'preload';
                    link.href = resource;
                    link.as = resource.endsWith('.css') ? 'style' : 'image';
                    document.head.appendChild(link);
                });
            }

            // Initialize performance optimizations
            optimizePerformance();

            // Touch gestures for mobile
            function initTouchGestures() {
                let startX = 0;
                let startY = 0;
                let currentX = 0;
                let currentY = 0;

                document.addEventListener('touchstart', function(e) {
                    startX = e.touches[0].clientX;
                    startY = e.touches[0].clientY;
                });

                document.addEventListener('touchmove', function(e) {
                    currentX = e.touches[0].clientX;
                    currentY = e.touches[0].clientY;
                });

                document.addEventListener('touchend', function(e) {
                    const diffX = startX - currentX;
                    const diffY = startY - currentY;

                    // Swipe left to open sidebar
                    if (diffX > 50 && Math.abs(diffY) < 50 && window.innerWidth <= 1024) {
                        sidebar.classList.add('show');
                    }
                    
                    // Swipe right to close sidebar
                    if (diffX < -50 && Math.abs(diffY) < 50 && window.innerWidth <= 1024) {
                        sidebar.classList.remove('show');
                    }
                });
            }

            // Initialize touch gestures
            initTouchGestures();

            // Error handling and logging
            function handleErrors() {
                window.addEventListener('error', function(e) {
                    console.error('MedicalVoice Error:', e.error);
                    // You can send this to your error tracking service
                });

                window.addEventListener('unhandledrejection', function(e) {
                    console.error('MedicalVoice Promise Rejection:', e.reason);
                    // You can send this to your error tracking service
                });
            }

            // Initialize error handling
            handleErrors();

            // Analytics and tracking (if needed)
            function trackUserActions() {
                const navLinks = document.querySelectorAll('.nav-link');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        const action = this.textContent.trim();
                        const href = this.getAttribute('href');
                        
                        // Track navigation clicks
                        console.log('User navigated to:', action, href);
                        
                        // You can integrate with Google Analytics or other tracking services here
                        // gtag('event', 'navigation_click', {
                        //     'event_category': 'MedicalVoice',
                        //     'event_label': action
                        // });
                    });
                });
            }

            // Initialize tracking
            trackUserActions();

            // Auto-save functionality for forms
            function initAutoSave() {
                const forms = document.querySelectorAll('form');
                forms.forEach(form => {
                    const formData = new FormData(form);
                    const formKey = form.action || 'unknown_form';
                    
                    // Save form data to localStorage
                    const saveFormData = () => {
                        const data = {};
                        formData.forEach((value, key) => {
                            data[key] = value;
                        });
                        localStorage.setItem(`medicalvoice_${formKey}`, JSON.stringify(data));
                    };

                    // Auto-save every 30 seconds
                    setInterval(saveFormData, 30000);
                    
                    // Save on form submission
                    form.addEventListener('submit', function() {
                        localStorage.removeItem(`medicalvoice_${formKey}`);
                    });
                });
            }

            // Initialize auto-save
            initAutoSave();

            // Theme detection and adaptation
            function detectTheme() {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');
                const prefersHighContrast = window.matchMedia('(prefers-contrast: high)');
                const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

                // Apply theme preferences
                if (prefersDark.matches) {
                    document.body.classList.add('dark-theme');
                }

                if (prefersHighContrast.matches) {
                    document.body.classList.add('high-contrast');
                }

                if (prefersReducedMotion.matches) {
                    document.body.classList.add('reduced-motion');
                }

                // Listen for theme changes
                prefersDark.addEventListener('change', function(e) {
                    if (e.matches) {
                        document.body.classList.add('dark-theme');
                    } else {
                        document.body.classList.remove('dark-theme');
                    }
                });
            }

            // Initialize theme detection
            detectTheme();

            // Console welcome message
            console.log(`
                🎉 MedicalVoice Module Loaded Successfully!
                
                Features:
                ✅ Unified Design System
                ✅ Responsive Layout
                ✅ Accessibility Enhanced
                ✅ Touch Gestures
                ✅ Performance Optimized
                ✅ Error Handling
                ✅ Auto-save Forms
                ✅ Theme Detection
                
                Built with ❤️ by MedicalNotes Team
            `);
        });
    </script>

    <!-- Bootstrap JS (if needed for any remaining components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
