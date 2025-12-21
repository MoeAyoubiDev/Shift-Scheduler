console.log('Navigation JS loaded - script executing');

// Test if script is running
if (typeof window !== 'undefined') {
    window.dashboardScriptLoaded = true;
    console.log('Dashboard script loaded flag set');
}

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
    
    function setActiveTab(sectionName) {
        console.log('setActiveTab called with:', sectionName);
        
        // Remove active from all tabs
        const allTabs = document.querySelectorAll('.tab-item');
        console.log('Found', allTabs.length, 'tabs');
        
        allTabs.forEach(tab => {
            tab.classList.remove('active');
            tab.setAttribute('aria-selected', 'false');
        });
        
        // Add active to target tab
        const targetTab = document.querySelector(`.tab-item[data-section="${sectionName}"]`);
        console.log('Target tab element:', targetTab);
        
        if (targetTab) {
            targetTab.classList.add('active');
            targetTab.setAttribute('aria-selected', 'true');
            console.log('Tab activated:', sectionName);
        } else {
            console.error('Tab not found:', sectionName);
            // List all available tabs for debugging
            allTabs.forEach(t => {
                console.log('Available tab:', t.getAttribute('data-section'));
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
        setActiveTab(sectionName);
        showSection(sectionName);
        
        // Update URL hash without triggering scroll
        if (window.location.hash !== '#' + sectionName) {
            history.replaceState(null, null, '#' + sectionName);
            console.log('URL hash updated to:', sectionName);
        }
    };
    
    // Initialize tab navigation
    function initNavigation() {
        console.log('initNavigation called');
        
        const tabs = document.querySelectorAll('.tab-item');
        
        console.log('Tabs found:', tabs.length);
        
        if (tabs.length === 0) {
            console.error('No tabs found!');
            return;
        }
        
        // Log all tabs for debugging
        tabs.forEach((tab, index) => {
            const section = tab.getAttribute('data-section');
            console.log(`Tab ${index}:`, section, tab);
        });
        
        // Handle tab clicks - use event delegation for reliability
        const tabContainer = document.querySelector('.dashboard-tabs');
        if (tabContainer) {
            tabContainer.addEventListener('click', function(e) {
                const clickedTab = e.target.closest('.tab-item');
                if (!clickedTab) return;
                
                const sectionName = clickedTab.getAttribute('data-section');
                console.log('Tab clicked via delegation:', sectionName, clickedTab);
                
                e.preventDefault();
                e.stopPropagation();
                
                if (sectionName) {
                    window.navigateToSection(sectionName);
                } else {
                    console.error('No data-section attribute found on clicked tab');
                }
            });
            
            console.log('Event delegation attached to tab container');
        } else {
            console.error('Tab container not found!');
        }
        
        // Also attach direct listeners as backup
        tabs.forEach((tab, index) => {
            const sectionName = tab.getAttribute('data-section');
            console.log(`Attaching direct click listener to tab ${index}:`, sectionName);
            
            tab.addEventListener('click', function(e) {
                console.log('Tab clicked (direct):', sectionName, this);
                e.preventDefault();
                e.stopPropagation();
                
                const targetSection = this.getAttribute('data-section');
                console.log('Target section from click:', targetSection);
                
                if (targetSection) {
                    window.navigateToSection(targetSection);
                } else {
                    console.error('No data-section attribute found on clicked tab');
                }
            });
        });
        
        // Handle hash on page load
        const hash = window.location.hash.substring(1);
        console.log('Initial hash:', hash);
        
        if (hash && document.querySelector(`.tab-item[data-section="${hash}"]`)) {
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
            if (hash && document.querySelector(`.tab-item[data-section="${hash}"]`)) {
                window.navigateToSection(hash);
            } else {
                window.navigateToSection('overview');
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
    console.log('Initializing tab navigation...');
    initNavigation();
    initDaySelector();
    initFormValidation();
    console.log('Tab navigation initialization complete');
    
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

// Initialize when DOM is ready - multiple strategies to ensure it runs
function initializeDashboard() {
    console.log('Attempting to initialize dashboard, readyState:', document.readyState);
    
    const tabs = document.querySelectorAll('.tab-item');
    if (tabs.length === 0) {
        console.warn('Tabs not found yet, will retry...');
        setTimeout(initializeDashboard, 100);
        return;
    }
    
    console.log('Tabs found, initializing navigation');
    initDashboardNavigation();
}

// Try multiple initialization strategies
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeDashboard);
} else if (document.readyState === 'interactive' || document.readyState === 'complete') {
    // Use setTimeout to ensure DOM is fully parsed
    setTimeout(initializeDashboard, 0);
} else {
    initializeDashboard();
}

// Fallback: try again after a short delay
setTimeout(function() {
    const tabs = document.querySelectorAll('.tab-item');
    if (tabs.length > 0) {
        const hasListeners = tabs[0].onclick !== null || tabs[0].getAttribute('data-initialized') === 'true';
        if (!hasListeners) {
            console.warn('Tabs found but listeners not attached, re-initializing...');
            initDashboardNavigation();
        }
    }
}, 500);

