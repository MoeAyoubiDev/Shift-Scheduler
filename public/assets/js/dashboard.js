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
        
        // Remove active from all nav cards
        const allCards = document.querySelectorAll('.nav-card');
        console.log('Found', allCards.length, 'nav cards');
        
        allCards.forEach(card => {
            card.classList.remove('active');
        });
        
        // Add active to target nav card
        const targetCard = document.querySelector(`.nav-card[data-section="${sectionName}"]`);
        console.log('Target nav card element:', targetCard);
        
        if (targetCard) {
            targetCard.classList.add('active');
            console.log('Nav card activated:', sectionName);
        } else {
            console.error('Nav card not found:', sectionName);
            // List all available nav cards for debugging
            allCards.forEach(c => {
                console.log('Available nav card:', c.getAttribute('data-section'));
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
        
        const navCards = document.querySelectorAll('.nav-card');
        
        console.log('Navigation cards found:', navCards.length);
        
        if (navCards.length === 0) {
            console.error('No navigation cards found!');
            return;
        }
        
        // Log all nav cards for debugging
        navCards.forEach((card, index) => {
            const section = card.getAttribute('data-section');
            console.log(`Nav card ${index}:`, section, card);
        });
        
        // Handle nav card clicks - use event delegation for reliability
        const navContainer = document.querySelector('.dashboard-nav-cards');
        if (navContainer) {
            navContainer.addEventListener('click', function(e) {
                const clickedCard = e.target.closest('.nav-card');
                if (!clickedCard) return;
                
                const sectionName = clickedCard.getAttribute('data-section');
                console.log('Nav card clicked via delegation:', sectionName, clickedCard);
                
                e.preventDefault();
                e.stopPropagation();
                
                if (sectionName) {
                    window.navigateToSection(sectionName);
                } else {
                    console.error('No data-section attribute found on clicked card');
                }
            });
            
            console.log('Event delegation attached to nav cards container');
        } else {
            console.error('Nav cards container not found!');
        }
        
        // Also attach direct listeners as backup
        navCards.forEach((card, index) => {
            const sectionName = card.getAttribute('data-section');
            console.log(`Attaching direct click listener to nav card ${index}:`, sectionName);
            
            card.addEventListener('click', function(e) {
                console.log('Nav card clicked (direct):', sectionName, this);
                e.preventDefault();
                e.stopPropagation();
                
                const targetSection = this.getAttribute('data-section');
                console.log('Target section from click:', targetSection);
                
                if (targetSection) {
                    window.navigateToSection(targetSection);
                } else {
                    console.error('No data-section attribute found on clicked card');
                }
            });
        });
        
        // Handle hash on page load
        const hash = window.location.hash.substring(1);
        console.log('Initial hash:', hash);
        
        if (hash && document.querySelector(`.nav-card[data-section="${hash}"]`)) {
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
            if (hash && document.querySelector(`.nav-card[data-section="${hash}"]`)) {
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

