/**
 * Dashboard Navigation & Functionality
 * Universal navigation system for all dashboard roles
 */

(function() {
    'use strict';
    
    // Set global flags immediately
    window.dashboardScriptLoaded = true;
    console.log('Dashboard script loaded - version 2.0');
    
    // Debug: Log when script executes
    console.log('Document ready state:', document.readyState);
    console.log('Nav cards found:', document.querySelectorAll('.nav-card').length);
    
    // ============================================
    // NAVIGATION SYSTEM
    // ============================================
    
    let currentSection = 'overview';
    
    function showSection(sectionName) {
        if (!sectionName) {
            console.warn('showSection called with empty sectionName');
            return;
        }
        
        console.log('showSection called with:', sectionName);
        
        // Hide all sections
        const allSections = document.querySelectorAll('.dashboard-section');
        console.log('Found', allSections.length, 'sections total');
        allSections.forEach(section => {
            section.classList.remove('active');
        });
        
        // Show target section
        const targetSection = document.querySelector(`.dashboard-section[data-section="${sectionName}"]`);
        console.log('Target section element:', targetSection);
        
        if (targetSection) {
            // Remove active from all first (in case of race conditions)
            allSections.forEach(section => {
                section.classList.remove('active');
                section.style.display = 'none';
            });
            
            // Add active class and show
            targetSection.classList.add('active');
            targetSection.style.display = 'block';
            currentSection = sectionName;
            console.log('Section activated:', sectionName);
            
            // Force a reflow to ensure CSS applies
            void targetSection.offsetHeight;
            
            // Double-check it's visible
            setTimeout(() => {
                if (!targetSection.classList.contains('active')) {
                    console.warn('Section lost active class, re-adding...');
                    targetSection.classList.add('active');
                    targetSection.style.display = 'block';
                }
            }, 50);
            
            // Scroll to top of section
            setTimeout(() => {
                targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        } else {
            console.error('Section not found:', sectionName);
            // List all available sections for debugging
            allSections.forEach(s => {
                console.log('Available section:', s.getAttribute('data-section'));
            });
        }
    }
    
    function setActiveNavCard(sectionName) {
        // Remove active from all nav cards
        document.querySelectorAll('.nav-card').forEach(card => {
            card.classList.remove('active');
        });
        
        // Add active to target nav card
        const targetCard = document.querySelector(`.nav-card[data-section="${sectionName}"]`);
        if (targetCard) {
            targetCard.classList.add('active');
        }
    }
    
    function navigateToSection(sectionName) {
        if (!sectionName) {
            console.warn('navigateToSection called with empty sectionName');
            return;
        }
        
        console.log('navigateToSection called with:', sectionName);
        
        setActiveNavCard(sectionName);
        showSection(sectionName);
        
        // Update URL hash
        if (window.location.hash !== '#' + sectionName) {
            history.replaceState(null, null, '#' + sectionName);
            console.log('URL hash updated to:', sectionName);
        }
    }
    
    // Expose globally in multiple ways for compatibility
    window.navigateToSection = navigateToSection;
    window.dashboard = window.dashboard || {};
    window.dashboard.navigateToSection = navigateToSection;
    
    // ============================================
    // UNIVERSAL EVENT DELEGATION (All buttons)
    // ============================================
    
    // Handle ALL clicks using event delegation - MUST be attached early
    console.log('Setting up click event delegation...');
    
    // CRITICAL: Attach event listener immediately, even before DOM is ready
    // Use capture phase to catch events early and prevent other handlers
    (function attachClickHandler() {
        if (document.body) {
            // Body exists, attach to document
            document.addEventListener('click', handleClick, true);
            console.log('Click handler attached to document');
        } else {
            // Body doesn't exist yet, wait for it
            setTimeout(attachClickHandler, 10);
        }
    })();
    
    function handleClick(e) {
        try {
            const target = e.target;
            const button = target.closest('button');
            const link = target.closest('a');
            
            // Skip if it's a link (like Export CSV) - let it work normally
            if (link && !button) {
                return;
            }
            
            // Navigation cards - HIGHEST PRIORITY
            if (button && button.classList.contains('nav-card')) {
                console.log('Nav card clicked:', button.getAttribute('data-section'));
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                const sectionName = button.getAttribute('data-section');
                if (sectionName) {
                    navigateToSection(sectionName);
                }
                return false;
            }
        
            // Widget clicks (onclick handlers in HTML)
            if (target.closest('.widget')) {
                const widget = target.closest('.widget');
                const onclick = widget.getAttribute('onclick');
                if (onclick && onclick.includes('navigateToSection')) {
                    // Extract section name from onclick
                    const match = onclick.match(/navigateToSection\(['"]([^'"]+)['"]\)/);
                    if (match && match[1]) {
                        e.preventDefault();
                        e.stopPropagation();
                        navigateToSection(match[1]);
                    }
                }
            }
            
            // Quick action cards
            if (button && button.classList.contains('quick-action-card')) {
                const onclick = button.getAttribute('onclick');
                if (onclick && onclick.includes('navigateToSection')) {
                    const match = onclick.match(/navigateToSection\(['"]([^'"]+)['"]\)/);
                    if (match && match[1]) {
                        e.preventDefault();
                        e.stopPropagation();
                        navigateToSection(match[1]);
                    }
                }
            }
            
            // Assign shift buttons
            if (target.closest('.btn-assign-shift')) {
            e.preventDefault();
            e.stopPropagation();
            const btn = target.closest('.btn-assign-shift');
            const cell = btn.closest('.shift-cell');
            if (cell) {
                openAssignModal(
                    cell.getAttribute('data-date'),
                    cell.getAttribute('data-employee-id')
                );
            }
            return;
        }
        
        // Assign from request buttons
        if (target.closest('.btn-assign-request')) {
            e.preventDefault();
            e.stopPropagation();
            const btn = target.closest('.btn-assign-request');
            openAssignModal(
                btn.getAttribute('data-date'),
                btn.getAttribute('data-employee-id'),
                null,
                btn.getAttribute('data-shift-id'),
                btn.getAttribute('data-request-id')
            );
            return;
        }
        
        // Shift pill clicks for editing
        if (target.closest('.shift-pill') || target.closest('.shift-edit-btn')) {
            const pill = target.closest('.shift-pill');
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
            return;
        }
        
        // Bulk select button
        if (button && (button.id === 'bulk-select-btn' || button.classList.contains('bulk-select-btn'))) {
            console.log('Bulk select button clicked');
            e.preventDefault();
            e.stopPropagation();
            const isActive = button.classList.contains('active');
            button.classList.toggle('active', !isActive);
            showNotification(!isActive ? 'Bulk select mode enabled' : 'Bulk select mode disabled', 'info');
            return false;
        }
        
        // Copy week button
        if (button && (button.id === 'copy-week-btn' || button.classList.contains('copy-week-btn'))) {
            console.log('Copy week button clicked');
            e.preventDefault();
            e.stopPropagation();
            showNotification('Week schedule copied. Use "Paste Week" to apply to another week.', 'info');
            return false;
        }
        
        // Clear conflicts button
        if (button && (button.id === 'clear-conflicts-btn' || button.classList.contains('clear-conflicts-btn'))) {
            console.log('Clear conflicts button clicked');
            e.preventDefault();
            e.stopPropagation();
            const conflictCells = document.querySelectorAll('.shift-conflict');
            if (conflictCells.length > 0) {
                conflictCells.forEach(cell => {
                    cell.style.animation = 'pulse-conflict 2s infinite';
                });
                showNotification(`${conflictCells.length} conflict(s) highlighted.`, 'info');
            } else {
                showNotification('No conflicts detected in the schedule.', 'info');
            }
            return false;
        }
        
        // Assign shifts button
        if (button && (button.id === 'assign-shifts-btn' || button.classList.contains('assign-shifts-btn'))) {
            console.log('Assign shifts button clicked');
            e.preventDefault();
            e.stopPropagation();
            const activeSection = document.querySelector('.dashboard-section.active');
            const currentSectionName = activeSection ? activeSection.getAttribute('data-section') : 'overview';
            if (currentSectionName !== 'weekly-schedule') {
                navigateToSection('weekly-schedule');
            }
            setTimeout(() => {
                const scheduleTable = document.querySelector('.schedule-table');
                if (scheduleTable) {
                    scheduleTable.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 100);
            return false;
        }
        
        // Debug: Log any button click that wasn't handled
        if (button && button.type !== 'submit') {
            console.log('Unhandled button click:', button.id, button.className, button);
        }
        } catch (error) {
            console.error('Error in click handler:', error);
        }
    }, true); // Use capture phase
    
    // ============================================
    // SCHEDULE TABLE FUNCTIONALITY
    // ============================================
    
    function initScheduleTable() {
        const filterInput = document.getElementById('schedule-filter');
        const startDateInput = document.getElementById('schedule-start-date');
        const endDateInput = document.getElementById('schedule-end-date');
        
        // Filter functionality
        if (filterInput) {
            filterInput.addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase().trim();
                document.querySelectorAll('.schedule-table tbody .employee-row').forEach(row => {
                    const employeeName = row.getAttribute('data-employee-name') || '';
                    if (searchTerm === '' || employeeName.includes(searchTerm)) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                });
            });
        }
        
        // Date range change
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
    
    // Make functions available globally
    window.openAssignModal = openAssignModal;
    window.closeModal = closeModal;
    
    // Modal close handlers (use event delegation)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.modal-close') || e.target.closest('.modal-cancel')) {
            e.preventDefault();
            closeModal();
        }
        
        const modal = document.getElementById('assign-modal');
        if (modal && e.target === modal) {
            closeModal();
        }
    });
    
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
                    'X-Requested-With': 'XMLHttpRequest'
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
    
    // ============================================
    // NOTIFICATION SYSTEM
    // ============================================
    
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
        // Initialize navigation
        const navContainer = document.querySelector('.dashboard-nav-cards');
        const navCards = document.querySelectorAll('.nav-card');
        
        if (navContainer && navCards.length > 0) {
            // Navigation is handled by event delegation above
            // Just ensure overview is active by default
            const activeSection = document.querySelector('.dashboard-section.active');
            if (!activeSection) {
                const overviewSection = document.querySelector('.dashboard-section[data-section="overview"]');
                if (overviewSection) {
                    overviewSection.classList.add('active');
                }
            }
            
            // Handle initial hash
            const hash = window.location.hash.substring(1);
            console.log('Initial hash:', hash);
            if (hash) {
                const hashCard = document.querySelector(`.nav-card[data-section="${hash}"]`);
                const hashSection = document.querySelector(`.dashboard-section[data-section="${hash}"]`);
                console.log('Hash card found:', !!hashCard, 'Hash section found:', !!hashSection);
                if (hashCard && hashSection) {
                    console.log('Navigating to hash section:', hash);
                    navigateToSection(hash);
                } else {
                    console.warn('Hash section not found, defaulting to overview');
                    navigateToSection('overview');
                }
            } else {
                // Ensure overview is active
                const overviewSection = document.querySelector('.dashboard-section[data-section="overview"]');
                if (overviewSection && !overviewSection.classList.contains('active')) {
                    navigateToSection('overview');
                }
            }
            
            // Handle browser back/forward
            window.addEventListener('popstate', function() {
                const hash = window.location.hash.substring(1);
                console.log('popstate event, hash:', hash);
                if (hash) {
                    const hashCard = document.querySelector(`.nav-card[data-section="${hash}"]`);
                    const hashSection = document.querySelector(`.dashboard-section[data-section="${hash}"]`);
                    if (hashCard && hashSection) {
                        navigateToSection(hash);
                    } else {
                        navigateToSection('overview');
                    }
                } else {
                    navigateToSection('overview');
                }
            });
            
            // Handle hashchange events (when URL hash changes)
            window.addEventListener('hashchange', function() {
                const hash = window.location.hash.substring(1);
                console.log('hashchange event, hash:', hash);
                if (hash) {
                    const hashCard = document.querySelector(`.nav-card[data-section="${hash}"]`);
                    const hashSection = document.querySelector(`.dashboard-section[data-section="${hash}"]`);
                    if (hashCard && hashSection) {
                        // Only navigate if not already on this section
                        const activeSection = document.querySelector('.dashboard-section.active');
                        const activeSectionName = activeSection ? activeSection.getAttribute('data-section') : null;
                        if (activeSectionName !== hash) {
                            navigateToSection(hash);
                        }
                    } else {
                        console.warn('Hash section not found in hashchange:', hash);
                    }
                } else {
                    navigateToSection('overview');
                }
            });
        }
        
        // Initialize schedule table if elements exist
        const scheduleTable = document.querySelector('.schedule-table');
        if (scheduleTable) {
            initScheduleTable();
        }
        
        window.dashboardReady = true;
        console.log('Dashboard fully initialized');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        setTimeout(initialize, 0);
    }
    
    // Fallback: retry initialization after a delay
    setTimeout(function() {
        const navCards = document.querySelectorAll('.nav-card');
        console.log('Fallback check - Nav cards:', navCards.length);
        if (navCards.length > 0) {
            const hasActive = document.querySelector('.dashboard-section.active');
            if (!hasActive) {
                console.warn('No active section found, re-initializing...');
                initialize();
            }
        }
    }, 500);
    
    // Expose manual initialization
    window.initDashboard = initialize;
    
    console.log('Dashboard script setup complete');
    
})();

// Additional global test - verify script loaded
console.log('dashboard.js file loaded successfully');
