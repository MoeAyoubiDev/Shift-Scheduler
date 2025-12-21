/**
 * Dashboard Navigation & Functionality
 * Universal navigation system for all dashboard roles (Employee, Team Leader, Director, Supervisor, Senior)
 */

(function() {
    'use strict';
    
    // Set global flag
    window.dashboardScriptLoaded = true;
    console.log('Dashboard script loaded');
    
    // ============================================
    // NAVIGATION SYSTEM (Universal for all roles)
    // ============================================
    
    let currentSection = 'overview';
    
    function showSection(sectionName) {
        if (!sectionName) {
            console.warn('showSection called with empty sectionName');
            return;
        }
        
        // Hide all sections
        const allSections = document.querySelectorAll('.dashboard-section');
        allSections.forEach(section => {
            section.classList.remove('active');
        });
        
        // Show target section
        const targetSection = document.querySelector(`.dashboard-section[data-section="${sectionName}"]`);
        if (targetSection) {
            targetSection.classList.add('active');
            currentSection = sectionName;
        } else {
            console.error('Section not found:', sectionName);
        }
    }
    
    function setActiveNavCard(sectionName) {
        // Remove active from all nav cards
        const allCards = document.querySelectorAll('.nav-card');
        allCards.forEach(card => {
            card.classList.remove('active');
        });
        
        // Add active to target nav card
        const targetCard = document.querySelector(`.nav-card[data-section="${sectionName}"]`);
        if (targetCard) {
            targetCard.classList.add('active');
        }
    }
    
    // Expose navigateToSection globally (multiple ways for compatibility)
    function navigateToSection(sectionName) {
        if (!sectionName) {
            console.warn('navigateToSection called with empty sectionName');
            return;
        }
        
        setActiveNavCard(sectionName);
        showSection(sectionName);
        
        // Update URL hash
        if (window.location.hash !== '#' + sectionName) {
            history.replaceState(null, null, '#' + sectionName);
        }
    }
    
    // Expose in multiple ways for compatibility
    window.navigateToSection = navigateToSection;
    window.dashboard = window.dashboard || {};
    window.dashboard.navigateToSection = navigateToSection;
    
    function initNavigation() {
        const navContainer = document.querySelector('.dashboard-nav-cards');
        const navCards = document.querySelectorAll('.nav-card');
        
        if (!navContainer || navCards.length === 0) {
            console.warn('Navigation elements not found');
            return false;
        }
        
        // Event delegation on container (most reliable)
        navContainer.addEventListener('click', function(e) {
            const clickedCard = e.target.closest('.nav-card');
            if (!clickedCard) return;
            
            const sectionName = clickedCard.getAttribute('data-section');
            if (sectionName) {
                e.preventDefault();
                e.stopPropagation();
                window.navigateToSection(sectionName);
            }
        });
        
        // Also attach direct listeners as backup
        navCards.forEach(card => {
            card.addEventListener('click', function(e) {
                const sectionName = this.getAttribute('data-section');
                if (sectionName) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.navigateToSection(sectionName);
                }
            });
        });
        
        // Handle initial hash
        const hash = window.location.hash.substring(1);
        if (hash && document.querySelector(`.nav-card[data-section="${hash}"]`)) {
            window.navigateToSection(hash);
        } else {
            // Ensure overview is active by default
            const overviewSection = document.querySelector('.dashboard-section[data-section="overview"]');
            if (overviewSection && !overviewSection.classList.contains('active')) {
                window.navigateToSection('overview');
            }
        }
        
        // Handle browser back/forward
        window.addEventListener('popstate', function() {
            const hash = window.location.hash.substring(1);
            if (hash && document.querySelector(`.nav-card[data-section="${hash}"]`)) {
                window.navigateToSection(hash);
            } else {
                window.navigateToSection('overview');
            }
        });
        
        console.log('Navigation initialized successfully');
        return true;
    }
    
    // ============================================
    // SCHEDULE TABLE FUNCTIONALITY (Team Leader)
    // ============================================
    
    function initScheduleTable() {
        const filterInput = document.getElementById('schedule-filter');
        const startDateInput = document.getElementById('schedule-start-date');
        const endDateInput = document.getElementById('schedule-end-date');
        
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
                        const url = new URL(window.location.href);
                        url.searchParams.set('week_start', startDate);
                        url.searchParams.set('week_end', endDate);
                        window.location.href = url.toString();
                    }
                });
            });
        }
    }
    
    function openAssignModal(date, employeeId, assignmentId = null, shiftDefId = null, requestId = null) {
        const modal = document.getElementById('assign-modal');
        if (!modal) return;
        
        const dateInput = document.getElementById('assign-date');
        const employeeSelect = document.getElementById('assign-employee-select');
        const requestIdInput = document.getElementById('assign-request-id');
        const shiftDefSelect = document.getElementById('assign-shift-def');
        const startTimeInput = document.getElementById('assign-start-time');
        const endTimeInput = document.getElementById('assign-end-time');
        const notesInput = document.getElementById('assign-notes');
        
        if (dateInput) dateInput.value = date || '';
        if (employeeSelect) employeeSelect.value = employeeId || '';
        if (requestIdInput) requestIdInput.value = requestId || '';
        if (shiftDefSelect) {
            shiftDefSelect.value = shiftDefId || '';
            if (shiftDefId) {
                shiftDefSelect.dispatchEvent(new Event('change'));
            }
        }
        if (startTimeInput) startTimeInput.value = '';
        if (endTimeInput) endTimeInput.value = '';
        if (notesInput) notesInput.value = '';
        
        modal.style.display = 'flex';
    }
    
    function closeModal() {
        const modal = document.getElementById('assign-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    // Make openAssignModal available globally
    window.openAssignModal = openAssignModal;
    
    // Assign shift from empty cell (use event delegation for dynamic elements)
    document.addEventListener('click', function(e) {
        // Handle .btn-assign-shift clicks
        if (e.target.closest('.btn-assign-shift')) {
            e.preventDefault();
            e.stopPropagation();
            const btn = e.target.closest('.btn-assign-shift');
            const cell = btn.closest('.shift-cell');
            if (cell) {
                openAssignModal(
                    cell.getAttribute('data-date'),
                    cell.getAttribute('data-employee-id')
                );
            }
        }
        
        // Handle .btn-assign-request clicks
        if (e.target.closest('.btn-assign-request')) {
            e.preventDefault();
            e.stopPropagation();
            const btn = e.target.closest('.btn-assign-request');
            openAssignModal(
                btn.getAttribute('data-date'),
                btn.getAttribute('data-employee-id'),
                null,
                btn.getAttribute('data-shift-id'),
                btn.getAttribute('data-request-id')
            );
        }
        
        // Handle shift cell clicks for editing
        if (e.target.closest('.shift-pill') || e.target.closest('.shift-edit-btn')) {
            const pill = e.target.closest('.shift-pill');
            if (pill) {
                e.preventDefault();
                e.stopPropagation();
                openAssignModal(
                    pill.getAttribute('data-date'),
                    pill.getAttribute('data-employee-id'),
                    pill.getAttribute('data-assignment-id'),
                    pill.getAttribute('data-shift-def-id')
                );
            }
        }
    });
    
    // Modal close handlers
    const modal = document.getElementById('assign-modal');
    if (modal) {
        const modalClose = modal.querySelector('.modal-close');
        const modalCancel = modal.querySelector('.modal-cancel');
        
        if (modalClose) {
            modalClose.addEventListener('click', closeModal);
        }
        if (modalCancel) {
            modalCancel.addEventListener('click', closeModal);
        }
        
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
                startInput.value = startTime.substring(0, 5);
            }
            if (endTime && endInput && !endInput.value) {
                endInput.value = endTime.substring(0, 5);
            }
        });
    }
    
    // Handle assign shift form submission via AJAX
    const assignForm = document.getElementById('assign-shift-form');
    if (assignForm) {
        assignForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const employeeSelect = document.getElementById('assign-employee-select');
            const shiftDef = document.getElementById('assign-shift-def');
            const date = document.getElementById('assign-date');
            
            if (!employeeSelect || !employeeSelect.value) {
                showNotification('Please select an employee.', 'error');
                return false;
            }
            
            if (!shiftDef || !shiftDef.value) {
                showNotification('Please select a shift type.', 'error');
                return false;
            }
            
            if (!date || !date.value) {
                showNotification('Date is missing.', 'error');
                return false;
            }
            
            const formData = new FormData(assignForm);
            const submitBtn = assignForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Assigning...';
            
            fetch('/index.php', {
                method: 'POST',
                headers: {
                    'X-Requested-Width': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                showNotification('An error occurred. Please try again.', 'error');
                console.error('Error:', error);
            });
            
            return false;
        });
    }
    
    // Function to show notifications
    function showNotification(message, type = 'info') {
        const existing = document.querySelector('.ajax-notification');
        if (existing) {
            existing.remove();
        }
        
        const notification = document.createElement('div');
        notification.className = `ajax-notification ajax-notification-${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
    
    // ============================================
    // TEAM LEADER DASHBOARD BUTTONS
    // ============================================
    
    // Bulk select button
    const bulkSelectBtn = document.getElementById('bulk-select-btn');
    if (bulkSelectBtn) {
        let bulkSelectMode = false;
        bulkSelectBtn.addEventListener('click', function() {
            bulkSelectMode = !bulkSelectMode;
            this.classList.toggle('active', bulkSelectMode);
            showNotification(bulkSelectMode ? 'Bulk select mode enabled' : 'Bulk select mode disabled', 'info');
        });
    }
    
    // Copy week button
    const copyWeekBtn = document.getElementById('copy-week-btn');
    if (copyWeekBtn) {
        copyWeekBtn.addEventListener('click', function() {
            showNotification('Week schedule copied. Use "Paste Week" to apply to another week.', 'info');
        });
    }
    
    // Clear conflicts button
    const clearConflictsBtn = document.getElementById('clear-conflicts-btn');
    if (clearConflictsBtn) {
        clearConflictsBtn.addEventListener('click', function() {
            const conflictCells = document.querySelectorAll('.shift-conflict');
            if (conflictCells.length > 0) {
                conflictCells.forEach(cell => {
                    cell.style.animation = 'pulse-conflict 2s infinite';
                });
                showNotification(`${conflictCells.length} conflict(s) highlighted.`, 'info');
            } else {
                showNotification('No conflicts detected in the schedule.', 'info');
            }
        });
    }
    
    // Assign shifts button
    const assignShiftsBtn = document.getElementById('assign-shifts-btn');
    if (assignShiftsBtn) {
        assignShiftsBtn.addEventListener('click', function() {
            // Navigate to weekly schedule section if not already there
            const activeSection = document.querySelector('.dashboard-section.active');
            const currentSectionName = activeSection ? activeSection.getAttribute('data-section') : 'overview';
            if (currentSectionName !== 'weekly-schedule') {
                navigateToSection('weekly-schedule');
            }
            // Scroll to schedule table
            setTimeout(() => {
                const scheduleTable = document.querySelector('.schedule-table');
                if (scheduleTable) {
                    scheduleTable.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 100);
        });
    }
    
    // ============================================
    // EMPLOYEE FORM FUNCTIONALITY
    // ============================================
    
    // Day selector - update hidden date field
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
    
    // Form validation
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
        });
    }
    
    // ============================================
    // INITIALIZATION
    // ============================================
    
    function initialize() {
        // Initialize navigation first
        const navInitialized = initNavigation();
        
        // Initialize schedule table if elements exist
        const scheduleTable = document.querySelector('.schedule-table');
        if (scheduleTable) {
            initScheduleTable();
        }
        
        // Verify initialization
        if (navInitialized) {
            window.dashboardReady = true;
            console.log('Dashboard fully initialized');
        } else {
            console.warn('Navigation initialization failed, will retry...');
            setTimeout(initialize, 200);
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        // DOM already ready, initialize immediately
        setTimeout(initialize, 0);
    }
    
    // Fallback: retry initialization after a delay
    setTimeout(function() {
        const navCards = document.querySelectorAll('.nav-card');
        if (navCards.length > 0) {
            const hasActive = document.querySelector('.dashboard-section.active');
            if (!hasActive) {
                console.warn('No active section found, re-initializing...');
                initialize();
            }
        }
    }, 500);
    
})();
