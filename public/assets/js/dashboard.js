console.log('Navigation JS loaded');

// Function to initialize dashboard navigation
function initDashboardNavigation() {
    console.log('initDashboardNavigation called, document.readyState:', document.readyState);
    
    // Navigation state management
    let currentSection = 'overview';
    
    function showSection(sectionName) {
        console.log('showSection called with:', sectionName);
        
        // Hide all sections
        const allSections = document.querySelectorAll('.dashboard-section');
        console.log('Found', allSections.length, 'sections');
        
        allSections.forEach(section => {
            section.classList.remove('active');
        });
        
        // Show target section
        const targetSection = document.querySelector(`.dashboard-section[data-section="${sectionName}"]`);
        console.log('Target section element:', targetSection);
        
        if (targetSection) {
            targetSection.classList.add('active');
            currentSection = sectionName;
            console.log('Section activated:', sectionName);
        } else {
            console.error('Section not found:', sectionName);
            // List all available sections for debugging
            const allSectionsList = document.querySelectorAll('.dashboard-section');
            allSectionsList.forEach(s => {
                console.log('Available section:', s.getAttribute('data-section'));
            });
        }
    }
    
    function setActiveNavItem(sectionName) {
        console.log('setActiveNavItem called with:', sectionName);
        
        // Remove active from all nav items
        const allNavItems = document.querySelectorAll('.nav-item');
        console.log('Found', allNavItems.length, 'nav items');
        
        allNavItems.forEach(nav => {
            nav.classList.remove('active');
        });
        
        // Add active to target nav item
        const targetNav = document.querySelector(`.nav-item[data-section="${sectionName}"]`);
        console.log('Target nav item element:', targetNav);
        
        if (targetNav) {
            targetNav.classList.add('active');
            console.log('Nav item activated:', sectionName);
        } else {
            console.error('Nav item not found:', sectionName);
            // List all available nav items for debugging
            allNavItems.forEach(n => {
                console.log('Available nav item:', n.getAttribute('data-section'));
            });
        }
    }
    
    // Expose navigateToSection on window for debugging
    window.navigateToSection = function(sectionName) {
        if (!sectionName) {
            console.warn('navigateToSection called with empty sectionName');
            return;
        }
        
        console.log('navigateToSection called with:', sectionName);
        setActiveNavItem(sectionName);
        showSection(sectionName);
        
        // Update URL hash without triggering scroll
        if (window.location.hash !== '#' + sectionName) {
            history.replaceState(null, null, '#' + sectionName);
            console.log('URL hash updated to:', sectionName);
        }
    };
    
    // Initialize navigation
    function initNavigation() {
        console.log('initNavigation called');
        
        const navItems = document.querySelectorAll('.nav-item');
        const sidebar = document.getElementById('dashboard-sidebar');
        
        console.log('Navigation items found:', navItems.length);
        
        if (navItems.length === 0) {
            console.error('No navigation items found!');
            return;
        }
        
        // Log all nav items for debugging
        navItems.forEach((item, index) => {
            const section = item.getAttribute('data-section');
            console.log(`Nav item ${index}:`, section, item);
        });
        
        // Handle navigation clicks
        navItems.forEach((item, index) => {
            const sectionName = item.getAttribute('data-section');
            console.log(`Attaching click listener to nav item ${index}:`, sectionName);
            
            item.addEventListener('click', function(e) {
                console.log('Nav item clicked:', sectionName, this);
                e.preventDefault();
                e.stopPropagation();
                
                const targetSection = this.getAttribute('data-section');
                console.log('Target section from click:', targetSection);
                
                if (targetSection) {
                    window.navigateToSection(targetSection);
                    
                    // Close sidebar on mobile after selection
                    if (window.innerWidth < 768 && sidebar) {
                        sidebar.classList.remove('open');
                        const overlay = document.getElementById('sidebar-overlay');
                        if (overlay) {
                            overlay.classList.remove('active');
                        }
                    }
                } else {
                    console.error('No data-section attribute found on clicked item');
                }
            });
        });
        
        // Handle hash on page load
        const hash = window.location.hash.substring(1);
        console.log('Initial hash:', hash);
        
        if (hash && document.querySelector(`.nav-item[data-section="${hash}"]`)) {
            console.log('Navigating to hash section:', hash);
            window.navigateToSection(hash);
        } else {
            // Default to overview
            console.log('No hash or invalid hash, defaulting to overview');
            window.navigateToSection('overview');
        }
        
        // Handle browser back/forward
        window.addEventListener('popstate', function() {
            const hash = window.location.hash.substring(1);
            console.log('popstate event, hash:', hash);
            if (hash && document.querySelector(`.nav-item[data-section="${hash}"]`)) {
                window.navigateToSection(hash);
            } else {
                window.navigateToSection('overview');
            }
        });
    }
    
    // Sidebar toggle for mobile
    function initSidebarToggle() {
        const sidebar = document.getElementById('dashboard-sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        
        if (!sidebar) {
            console.warn('Sidebar not found');
            return;
        }
        
        function toggleSidebar() {
            sidebar.classList.toggle('open');
            if (overlay) {
                overlay.classList.toggle('active');
            }
        }
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleSidebar();
            });
        }
        
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleSidebar();
            });
        }
        
        if (overlay) {
            overlay.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleSidebar();
            });
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 768 && sidebar.classList.contains('open')) {
                if (!sidebar.contains(e.target) && 
                    sidebarToggle && !sidebarToggle.contains(e.target) && 
                    mobileMenuBtn && !mobileMenuBtn.contains(e.target)) {
                    toggleSidebar();
                }
            }
        });
    }
    
    // Day selector - update hidden date field
    function initDaySelector() {
        const daySelect = document.getElementById('request_day');
        const dateInput = document.getElementById('request_date');
        
        if (daySelect && dateInput) {
            daySelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const dateValue = selectedOption.getAttribute('data-date');
                if (dateValue) {
                    dateInput.value = dateValue;
                } else {
                    dateInput.value = '';
                }
            });
        }
    }
    
    // Form validation
    function initFormValidation() {
        const requestForm = document.getElementById('request-form');
        if (requestForm) {
            requestForm.addEventListener('submit', function(e) {
                const daySelect = document.getElementById('request_day');
                const dateInput = document.getElementById('request_date');
                
                if (!daySelect || !daySelect.value || !dateInput || !dateInput.value) {
                    e.preventDefault();
                    alert('Please select a day for your shift request.');
                    return false;
                }
                
                // Additional validation: ensure date is set
                if (!dateInput.value) {
                    e.preventDefault();
                    alert('Invalid date selection. Please try again.');
                    return false;
                }
            });
        }
    }
    
    // Initialize everything
    console.log('Initializing navigation and sidebar...');
    initNavigation();
    initSidebarToggle();
    initDaySelector();
    initFormValidation();
    console.log('Navigation initialization complete');
    
    // Fallback: verify at least one section is visible
    setTimeout(function() {
        const activeSection = document.querySelector('.dashboard-section.active');
        if (!activeSection) {
            console.warn('No active section found after initialization, activating overview');
            const overviewSection = document.querySelector('.dashboard-section[data-section="overview"]');
            if (overviewSection) {
                overviewSection.classList.add('active');
            }
        }
    }, 100);
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    // DOM is still loading, wait for DOMContentLoaded
    document.addEventListener('DOMContentLoaded', initDashboardNavigation);
} else {
    // DOM is already loaded, initialize immediately
    initDashboardNavigation();
}

