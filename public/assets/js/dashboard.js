console.log('Navigation JS loaded - script executing');

// Test if script is running
if (typeof window !== 'undefined') {
    window.dashboardScriptLoaded = true;

// Schedule Table Functionality
(function() {
    'use strict';
    
    function initScheduleTable() {
        const filterInput = document.getElementById('schedule-filter');
        const startDateInput = document.getElementById('schedule-start-date');
        const endDateInput = document.getElementById('schedule-end-date');
        const assignBtn = document.getElementById('assign-shifts-btn');
        
        // Filter functionality
        if (filterInput) {
            filterInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase().trim();
                const rows = document.querySelectorAll('.schedule-table tbody .employee-row');
                
                rows.forEach(row => {
                    const employeeName = row.getAttribute('data-employee-name') || '';
                    if (searchTerm === '' || employeeName.includes(searchTerm)) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                });
            });
        }
        
        // Date range change - reload page with new dates
        if (startDateInput && endDateInput) {
            [startDateInput, endDateInput].forEach(input => {
                input.addEventListener('change', function() {
                    const startDate = startDateInput.value;
                    const endDate = endDateInput.value;
                    if (startDate && endDate) {
                        // Reload page with new date range
                        const url = new URL(window.location.href);
                        url.searchParams.set('week_start', startDate);
                        url.searchParams.set('week_end', endDate);
                        window.location.href = url.toString();
                    }
                });
            });
        }
        
        // Assign Shifts button
        if (assignBtn) {
            assignBtn.addEventListener('click', function() {
                // For now, scroll to generate schedule button or show a message
                // This can be enhanced with a modal for assigning shifts
                alert('Assign Shifts functionality: Click on any shift cell to assign or modify shifts. Use the "Generate Schedule" button to auto-generate based on requirements.');
            });
        }
        
        // Make shift cells clickable for editing
        const shiftCells = document.querySelectorAll('.shift-cell');
        shiftCells.forEach(cell => {
            cell.addEventListener('click', function(e) {
                if (e.target.closest('.shift-editable') || e.target.closest('.shift-edit-btn')) {
                    const shiftPill = e.target.closest('.shift-editable');
                    if (shiftPill) {
                        openAssignModal(
                            shiftPill.getAttribute('data-date'),
                            shiftPill.getAttribute('data-employee-id'),
                            shiftPill.getAttribute('data-assignment-id'),
                            shiftPill.getAttribute('data-shift-def-id')
                        );
                    }
                }
            });
        });
        
        // Assign shift from empty cell
        document.querySelectorAll('.btn-assign-shift').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const cell = this.closest('.shift-cell');
                openAssignModal(
                    cell.getAttribute('data-date'),
                    cell.getAttribute('data-employee-id')
                );
            });
        });
        
        // Assign from request
        document.querySelectorAll('.btn-assign-request').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                openAssignModal(
                    this.getAttribute('data-date'),
                    this.getAttribute('data-employee-id'),
                    null,
                    this.getAttribute('data-shift-id'),
                    this.getAttribute('data-request-id')
                );
            });
        });
        
        // Modal functionality
        const modal = document.getElementById('assign-modal');
        const modalClose = document.querySelector('.modal-close');
        const modalCancel = document.querySelector('.modal-cancel');
        
        if (modalClose) {
            modalClose.addEventListener('click', closeModal);
        }
        if (modalCancel) {
            modalCancel.addEventListener('click', closeModal);
        }
        
        // Close modal on outside click
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
        }
        
        // Auto-fill shift times when shift definition changes
        const shiftDefSelect = document.getElementById('assign-shift-def');
        if (shiftDefSelect) {
            shiftDefSelect.addEventListener('change', function() {
                const option = this.options[this.selectedIndex];
                const startTime = option.getAttribute('data-start');
                const endTime = option.getAttribute('data-end');
                const startInput = document.getElementById('assign-start-time');
                const endInput = document.getElementById('assign-end-time');
                
                if (startTime && startInput && !startInput.value) {
                    startInput.value = startTime.substring(0, 5); // Convert HH:MM:SS to HH:MM
                }
                if (endTime && endInput && !endInput.value) {
                    endInput.value = endTime.substring(0, 5);
                }
            });
        }
    }
    
    function openAssignModal(date, employeeId, assignmentId = null, shiftDefId = null, requestId = null) {
        const modal = document.getElementById('assign-modal');
        if (!modal) return;
        
        document.getElementById('assign-date').value = date;
        document.getElementById('assign-employee-select').value = employeeId || '';
        if (requestId) {
            document.getElementById('assign-request-id').value = requestId;
        } else {
            document.getElementById('assign-request-id').value = '';
        }
        if (shiftDefId) {
            document.getElementById('assign-shift-def').value = shiftDefId;
            // Trigger change to auto-fill times
            document.getElementById('assign-shift-def').dispatchEvent(new Event('change'));
        } else {
            document.getElementById('assign-shift-def').value = '';
        }
        
        // Clear custom times and notes
        document.getElementById('assign-start-time').value = '';
        document.getElementById('assign-end-time').value = '';
        document.getElementById('assign-notes').value = '';
        
        modal.style.display = 'flex';
    }
    
    function closeModal() {
        const modal = document.getElementById('assign-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    // Handle assign shift form submission
    const assignForm = document.getElementById('assign-shift-form');
    if (assignForm) {
        assignForm.addEventListener('submit', function(e) {
            // Validate required fields
            const employeeSelect = document.getElementById('assign-employee-select');
            const shiftDef = document.getElementById('assign-shift-def');
            const date = document.getElementById('assign-date');
            
            if (!employeeSelect || !employeeSelect.value) {
                e.preventDefault();
                alert('Please select an employee.');
                return false;
            }
            
            if (!shiftDef || !shiftDef.value) {
                e.preventDefault();
                alert('Please select a shift type.');
                return false;
            }
            
            if (!date || !date.value) {
                e.preventDefault();
                alert('Date is missing.');
                return false;
            }
            
            // Form will submit normally - PHP will handle redirect with success message
            // The page will refresh and show the success message
        });
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initScheduleTable);
    } else {
        initScheduleTable();
    }
})();
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
                console.log('Nav card clicked:', sectionName);
                
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
                const targetSection = this.getAttribute('data-section');
                console.log('Nav card clicked:', targetSection);
                
                e.preventDefault();
                e.stopPropagation();
                
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
    
    const navCards = document.querySelectorAll('.nav-card');
    if (navCards.length === 0) {
        console.warn('Nav cards not found yet, will retry...');
        setTimeout(initializeDashboard, 100);
        return;
    }
    
    console.log('Nav cards found, initializing navigation');
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
    const navCards = document.querySelectorAll('.nav-card');
    if (navCards.length > 0) {
        const hasListeners = navCards[0].onclick !== null || navCards[0].getAttribute('data-initialized') === 'true';
        if (!hasListeners) {
            console.warn('Nav cards found but listeners not attached, re-initializing...');
            initDashboardNavigation();
        }
    }
}, 500);

