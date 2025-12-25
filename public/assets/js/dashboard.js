/**
 * Dashboard Navigation & Functionality
 * Modern 2026 Design with Smooth Animations
 */

(function() {
    'use strict';
    
    // Global flags
    window.dashboardScriptLoaded = true;
    let currentSection = 'overview';
    let bulkSelectActive = false;
    
    // ============================================
    // NAVIGATION SYSTEM - Enhanced with Animations
    // ============================================
    
    function showSection(sectionName) {
        if (!sectionName) return;
        
        // Hide all sections immediately
        document.querySelectorAll('.dashboard-section').forEach(section => {
            section.classList.remove('active');
            section.style.display = 'none';
            section.style.opacity = '0';
        });
        
        // Show target section immediately
        const targetSection = document.querySelector(`.dashboard-section[data-section="${sectionName}"]`);
        if (targetSection) {
            targetSection.style.display = 'block';
            targetSection.classList.add('active');
            targetSection.style.opacity = '1';
            targetSection.style.animation = 'fadeInUp 0.4s ease-out forwards';
            currentSection = sectionName;
            
            // Scroll to top of page (not the section) to prevent jumping
            setTimeout(() => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }, 100);
        }
    }
    
    function setActiveNavCard(sectionName) {
        // Remove active from all nav cards with animation
        document.querySelectorAll('.nav-card').forEach(card => {
            if (card.classList.contains('active')) {
                card.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            }
            card.classList.remove('active');
        });
        
        // Add active to target nav card with animation
        const targetCard = document.querySelector(`.nav-card[data-section="${sectionName}"]`);
        if (targetCard) {
            targetCard.classList.add('active');
            // Add pulse animation
            targetCard.style.animation = 'cardPulse 0.5s ease-out';
        }
    }
    
    function navigateToSection(sectionName) {
        if (!sectionName) return;
        const targetSection = document.querySelector(`.dashboard-section[data-section="${sectionName}"]`);
        if (!targetSection) return;
        
        // Add haptic feedback if available
        if (navigator.vibrate) {
            navigator.vibrate(10);
        }
        
        setActiveNavCard(sectionName);
        showSection(sectionName);
        
        // Update URL hash
        if (window.location.hash !== '#' + sectionName) {
            history.replaceState(null, null, '#' + sectionName);
        }
    }
    
    // Expose globally
    window.navigateToSection = navigateToSection;
    window.dashboard = window.dashboard || {};
    window.dashboard.navigateToSection = navigateToSection;

    // ============================================
    // RESPONSIVE TABLE LABELS
    // ============================================
    function applyResponsiveTableLabels() {
        document.querySelectorAll('table').forEach(table => {
            const headerCells = Array.from(table.querySelectorAll('thead th'));
            if (!headerCells.length) {
                return;
            }

            const headerLabels = [];
            headerCells.forEach(th => {
                const label = th.textContent.trim().replace(/\s+/g, ' ');
                const span = th.colSpan || 1;
                for (let i = 0; i < span; i += 1) {
                    headerLabels.push(label);
                }
            });

            if (!headerLabels.length) {
                return;
            }

            table.querySelectorAll('tbody tr').forEach(row => {
                let cellIndex = 0;
                Array.from(row.children).forEach(cell => {
                    if (cell.tagName !== 'TD') {
                        return;
                    }

                    const span = cell.colSpan || 1;
                    if (!cell.hasAttribute('data-label') && headerLabels[cellIndex]) {
                        cell.setAttribute('data-label', headerLabels[cellIndex]);
                    }

                    cellIndex += span;
                });
            });
        });
    }
    
    // ============================================
    // FORM HANDLING - Enhanced with Animations
    // ============================================
    
    // Handle form submissions - ONLY for AJAX forms
    document.addEventListener('submit', function(e) {
        const form = e.target;
        
        // Only handle forms with data-ajax="true" attribute
        if (form.tagName === 'FORM' && form.getAttribute('data-ajax') === 'true') {
            e.preventDefault();
            handleAjaxForm(form);
        }
        // All other forms submit normally
    });
    
    function handleAjaxForm(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : '';
        const originalHTML = submitBtn ? submitBtn.innerHTML : '';
        
        // Show loading state with animation
        if (submitBtn) {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            submitBtn.style.transform = 'scale(0.98)';
            
            // Add loading spinner
            const spinner = document.createElement('div');
            spinner.className = 'loading-spinner';
            spinner.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" stroke-opacity="0.25"/><path d="M12 2C6.477 2 2 6.477 2 12" stroke="currentColor" stroke-width="4" stroke-linecap="round"/></svg>';
            submitBtn.innerHTML = '';
            submitBtn.appendChild(spinner);
        }
        
        const formData = new FormData(form);
        
        fetch('/index.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Remove loading state
            if (submitBtn) {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                submitBtn.style.transform = '';
                submitBtn.innerHTML = originalHTML;
            }
            
            // Show notification with animation
            if (window.NotificationManager) {
                if (data.success) {
                    window.NotificationManager.success(data.message || 'Operation completed successfully.');
                } else {
                    window.NotificationManager.error(data.message || 'An error occurred.');
                }
            } else {
                showNotification(data.message || (data.success ? 'Success' : 'Error'), data.success ? 'success' : 'error');
            }
            
            // Handle redirect
            if (data.redirect) {
                window.location.href = data.redirect;
            } else if (data.success) {
                const closeModalId = form.getAttribute('data-close-modal');
                if (closeModalId) {
                    closeModalById(closeModalId);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            }
        })
        .catch(error => {
            // Remove loading state
            if (submitBtn) {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
                submitBtn.style.transform = '';
                submitBtn.innerHTML = originalHTML;
            }
            
            // Show error with animation
            if (window.NotificationManager) {
                window.NotificationManager.error('Network error. Please try again.');
            } else {
                showNotification('Network error. Please try again.', 'error');
            }
            
            console.error('Form submission error:', error);
        });
    }
    
    // ============================================
    // NAVIGATION CLICKS - Enhanced with Haptics
    // ============================================
    
    document.addEventListener('click', function(e) {
        const rawTarget = e.target;
        const target = rawTarget instanceof Element ? rawTarget : rawTarget?.parentElement;
        if (!target) {
            return;
        }
        const button = target.closest('button');
        let navTrigger = target.closest('.nav-card, .quick-action-card, [data-section]');
        if (navTrigger && navTrigger.classList.contains('dashboard-section')) {
            navTrigger = null;
        }

        if (bulkSelectActive) {
            const selectableCell = target.closest('.shift-cell');
            if (selectableCell) {
                e.preventDefault();
                e.stopPropagation();
                toggleShiftSelection(selectableCell);
                updateBulkSelectState();
                return false;
            }
        }

        // Navigation cards - Enhanced with animations
        if (navTrigger && navTrigger.classList.contains('nav-card')) {
            e.preventDefault();
            e.stopPropagation();
            
            // Add click animation
            navTrigger.style.transform = 'scale(0.95)';
            setTimeout(() => {
                navTrigger.style.transform = '';
            }, 150);
            
            const sectionName = navTrigger.getAttribute('data-section');
            if (sectionName) {
                navigateToSection(sectionName);
            }
            return false;
        }
        
        // Widget clicks with onclick handlers
        const widget = target.closest('.widget');
        if (widget) {
            const onclick = widget.getAttribute('onclick');
            if (onclick && onclick.includes('navigateToSection')) {
                e.preventDefault();
                e.stopPropagation();
                
                // Add click animation
                widget.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    widget.style.transform = '';
                }, 150);
                
                const match = onclick.match(/navigateToSection\(['"]([^'"]+)['"]\)/);
                if (match && match[1]) {
                    navigateToSection(match[1]);
                }
                return false;
            }
        }
        
        // Quick action cards / data-section buttons
        if (navTrigger && (navTrigger.classList.contains('quick-action-card') || navTrigger.getAttribute('data-section'))) {
            const targetSection = navTrigger.getAttribute('data-section');
            const onclick = navTrigger.getAttribute('onclick');
            if (targetSection || (onclick && onclick.includes('navigateToSection'))) {
                e.preventDefault();
                e.stopPropagation();
                
                // Add click animation
                navTrigger.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    navTrigger.style.transform = '';
                }, 150);
                
                if (targetSection) {
                    navigateToSection(targetSection);
                } else {
                    const match = onclick.match(/navigateToSection\(['"]([^'"]+)['"]\)/);
                    if (match && match[1]) {
                        navigateToSection(match[1]);
                    }
                }
                return false;
            }
        }
        
        // Modal triggers - Enhanced animations
        if (target.closest('.btn-assign-shift') || target.closest('.shift-empty')) {
            e.preventDefault();
            e.stopPropagation();
            const cell = target.closest('.shift-cell') || target.closest('.shift-empty');
            if (cell) {
                openAssignModal(
                    cell.getAttribute('data-date'),
                    cell.getAttribute('data-employee-id')
                );
            }
            return false;
        }
        
        // Assign from request
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
            return false;
        }
        
        // Edit shift
        if (target.closest('.shift-edit-btn') || target.closest('.shift-pill')) {
            e.preventDefault();
            e.stopPropagation();
            const element = target.closest('.shift-pill') || target.closest('.shift-edit-btn');
            if (element) {
                openAssignModal(
                    element.getAttribute('data-date'),
                    element.getAttribute('data-employee-id'),
                    element.getAttribute('data-assignment-id'),
                    element.getAttribute('data-shift-def-id')
                );
            }
            return false;
        }
        
        // Close modal
        const modalCloseTarget = target.closest('.modal-close, .modal-cancel, [data-modal-close]');
        const modalContainer = target.closest('.modal');
        if ((modalCloseTarget && modalContainer) || target.closest('[data-modal-close]') ||
            target.classList.contains('modal')) {
            e.preventDefault();
            e.stopPropagation();
            const modalId = target.closest('[data-modal-close]')?.getAttribute('data-modal-close');
            if (modalId) {
                closeModalById(modalId);
            } else if (target.classList.contains('modal') && target.id) {
                closeModalById(target.id);
            } else {
                closeModal();
            }
            return false;
        }

        if (button && button.id === 'assign-shifts-btn') {
            e.preventDefault();
            e.stopPropagation();
            openAssignModal(null, null, null, null);
            return false;
        }

        if (button && button.id === 'swap-shifts-btn') {
            e.preventDefault();
            e.stopPropagation();
            openModalById('swap-modal');
            return false;
        }

        if (button && button.id === 'bulk-select-btn') {
            e.preventDefault();
            e.stopPropagation();
            bulkSelectActive = !bulkSelectActive;
            document.body.classList.toggle('bulk-select-active', bulkSelectActive);
            updateBulkSelectState();
            return false;
        }

        if (button && button.id === 'copy-week-btn') {
            e.preventDefault();
            e.stopPropagation();
            copySelectedShifts();
            return false;
        }

        if (button && button.id === 'clear-conflicts-btn') {
            e.preventDefault();
            e.stopPropagation();
            toggleConflictHighlights(button);
            return false;
        }

        if (button && button.classList.contains('btn-update-employee')) {
            e.preventDefault();
            e.stopPropagation();
            openEmployeeUpdateModal(button);
            return false;
        }
    }, true); // Use capture phase
    
    // ============================================
    // MODAL FUNCTIONS - Enhanced with Animations
    // ============================================
    
    function openAssignModal(date, employeeId, assignmentId, shiftDefId, requestId) {
        const modal = document.getElementById('assign-modal');
        if (!modal) return;
        
        const dateInput = document.getElementById('assign-date');
        const employeeSelect = document.getElementById('assign-employee-select');
        const requestIdInput = document.getElementById('assign-request-id');
        const shiftDefSelect = document.getElementById('assign-shift-def');
        const assignmentInput = document.getElementById('assign-assignment-id');
        const actionInput = document.getElementById('assign-action');
        const submitBtn = document.getElementById('assign-submit-btn');
        const deleteForm = document.getElementById('delete-assignment-form');
        const deleteAssignmentInput = document.getElementById('delete-assignment-id');
        const modalTitle = modal.querySelector('.modal-header h3');

        const isEditing = Boolean(assignmentId);
        if (actionInput) {
            actionInput.value = isEditing ? 'update_assignment' : 'assign_shift';
        }
        if (assignmentInput) {
            assignmentInput.value = assignmentId || '';
        }
        if (requestIdInput) {
            requestIdInput.value = isEditing ? '' : (requestId || '');
        }
        if (deleteForm) {
            deleteForm.style.display = isEditing ? 'block' : 'none';
        }
        if (deleteAssignmentInput) {
            deleteAssignmentInput.value = assignmentId || '';
        }
        if (submitBtn) {
            submitBtn.textContent = isEditing ? 'Update Shift' : 'Assign Shift';
        }
        if (modalTitle) {
            modalTitle.textContent = isEditing ? 'Update Shift' : 'Assign Shift';
        }
        
        if (dateInput) {
            dateInput.value = date || dateInput.value || '';
            if (!dateInput.value && dateInput.options && dateInput.options.length > 1) {
                dateInput.value = dateInput.options[1].value;
            }
            dateInput.disabled = isEditing;
        }
        if (employeeSelect) employeeSelect.value = employeeId || '';
        if (shiftDefSelect && shiftDefId) {
            shiftDefSelect.value = shiftDefId;
            shiftDefSelect.dispatchEvent(new Event('change'));
        }
        
        // Show modal with animation
        modal.style.display = 'flex';
        modal.style.animation = 'fadeIn 0.2s ease-out';
        const modalContent = modal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.animation = 'modalSlideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
        }
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }
    
    function closeModal() {
        const modal = document.getElementById('assign-modal');
        if (modal) {
            // Close with animation
            modal.style.animation = 'fadeOut 0.2s ease-out';
            const modalContent = modal.querySelector('.modal-content');
            if (modalContent) {
                modalContent.style.animation = 'modalSlideOut 0.2s ease-out';
            }
            
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }, 200);
        }
    }
    
    window.openAssignModal = openAssignModal;
    window.closeModal = closeModal;

    function openModalById(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.style.display = 'flex';
        modal.style.animation = 'fadeIn 0.2s ease-out';
        const modalContent = modal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.animation = 'modalSlideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
        }

        document.body.style.overflow = 'hidden';
    }

    function closeModalById(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        modal.style.animation = 'fadeOut 0.2s ease-out';
        const modalContent = modal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.animation = 'modalSlideOut 0.2s ease-out';
        }

        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 200);
    }

    function toggleShiftSelection(cell) {
        cell.classList.toggle('shift-selected');
    }

    function updateBulkSelectState() {
        const bulkBtn = document.getElementById('bulk-select-btn');
        if (!bulkBtn) return;

        const selectedCount = document.querySelectorAll('.shift-selected').length;
        if (bulkSelectActive) {
            bulkBtn.classList.add('active');
            bulkBtn.innerHTML = `Selected ${selectedCount || 0}`;
        } else {
            bulkBtn.classList.remove('active');
            bulkBtn.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" style="display: inline-block; margin-right: 0.25rem; vertical-align: middle;">
                    <path d="M2 4L6 8L14 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Bulk Select
            `;
            document.querySelectorAll('.shift-selected').forEach(cell => cell.classList.remove('shift-selected'));
        }
    }

    function copySelectedShifts() {
        const selectedCells = document.querySelectorAll('.shift-selected');
        const cells = selectedCells.length ? selectedCells : document.querySelectorAll('.shift-cell');
        const rows = [];

        cells.forEach(cell => {
            const row = cell.closest('tr');
            const employeeName = row?.querySelector('.employee-name')?.textContent?.trim() || 'Employee';
            const date = cell.getAttribute('data-date') || '';
            const shiftLabels = Array.from(cell.querySelectorAll('.shift-pill'))
                .map(pill => pill.textContent.trim().replace(/\s+/g, ' '))
                .filter(Boolean);
            if (!shiftLabels.length) {
                return;
            }
            rows.push(`${employeeName}\t${date}\t${shiftLabels.join(', ')}`);
        });

        if (!rows.length) {
            notify('No shifts found to copy.', 'warning');
            return;
        }

        const text = ['Employee\tDate\tShift', ...rows].join('\n');
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text)
                .then(() => notify('Schedule copied to clipboard.', 'success'))
                .catch(() => fallbackCopy(text));
        } else {
            fallbackCopy(text);
        }
    }

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            notify('Schedule copied to clipboard.', 'success');
        } catch (err) {
            notify('Unable to copy schedule.', 'error');
        }
        textarea.remove();
    }

    function toggleConflictHighlights(button) {
        const wrapper = document.querySelector('.schedule-table-wrapper');
        if (!wrapper) return;
        const isActive = wrapper.classList.toggle('conflicts-visible');
        button.classList.toggle('active', isActive);
        notify(isActive ? 'Conflict highlighting enabled.' : 'Conflict highlighting cleared.', 'info');
    }

    function openEmployeeUpdateModal(button) {
        const modal = document.getElementById('update-employee-modal');
        if (!modal) return;

        const employeeId = button.getAttribute('data-employee-id') || '';
        const fullName = button.getAttribute('data-full-name') || '';
        const email = button.getAttribute('data-email') || '';
        const roleId = button.getAttribute('data-role-id') || '';
        const seniorityLevel = button.getAttribute('data-seniority-level') || 0;

        const idField = document.getElementById('update-employee-id');
        const nameField = document.getElementById('update-full-name');
        const emailField = document.getElementById('update-email');
        const roleField = document.getElementById('update-role');
        const seniorityField = document.getElementById('update-seniority');

        if (idField) idField.value = employeeId;
        if (nameField) nameField.value = fullName;
        if (emailField) emailField.value = email;
        if (roleField && roleId) roleField.value = roleId;
        if (seniorityField) seniorityField.value = seniorityLevel;

        modal.style.display = 'flex';
        modal.style.animation = 'fadeIn 0.2s ease-out';
        const modalContent = modal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.animation = 'modalSlideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
        }
        document.body.style.overflow = 'hidden';
    }

    function notify(message, type = 'info') {
        if (window.NotificationManager) {
            window.NotificationManager.show(message, type);
        } else {
            showNotification(message, type);
        }
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
            
            if (startInput && startTime) {
                startInput.value = startTime;
                startInput.style.animation = 'pulse 0.3s ease-out';
            }
            if (endInput && endTime) {
                endInput.value = endTime;
                endInput.style.animation = 'pulse 0.3s ease-out';
            }
        });
    }
    
    // ============================================
    // SCHEDULE TABLE INTERACTIVITY
    // ============================================
    
    function initScheduleTable() {
        // Date range selector with smooth transitions
        const dateInputs = document.querySelectorAll('.date-input');
        dateInputs.forEach(input => {
            input.addEventListener('change', function() {
                this.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
                
                const weekStart = document.getElementById('week-start')?.value;
                const weekEnd = document.getElementById('week-end')?.value;
                if (weekStart && weekEnd) {
                    window.location.href = `/index.php?week_start=${weekStart}&week_end=${weekEnd}`;
                }
            });
        });
        
        // Filter input with debounce
        const filterInput = document.getElementById('schedule-filter') || document.getElementById('filter-employees');
        if (filterInput) {
            let filterTimeout;
            filterInput.addEventListener('input', function() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => {
                    const query = this.value.toLowerCase();
                    document.querySelectorAll('.employee-row').forEach(row => {
                        const name = row.textContent.toLowerCase();
                        if (name.includes(query)) {
                            row.style.display = '';
                            row.style.animation = 'fadeIn 0.2s ease-out';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                }, 200);
            });
        }
    }
    
    // ============================================
    // NOTIFICATION SYSTEM - Enhanced
    // ============================================
    
    function showNotification(message, type = 'info') {
        if (window.NotificationManager) {
            window.NotificationManager.show(message, type);
        } else {
            // Fallback simple notification with animation
            const existing = document.querySelector('.ajax-notification');
            if (existing) {
                existing.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => existing.remove(), 300);
            }
            
            const notification = document.createElement('div');
            notification.className = `ajax-notification ajax-notification-${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => notification.classList.add('show'), 10);
            
            // Auto remove after 4 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease-out';
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        }
    }
    
    window.showNotification = showNotification;
    
    // ============================================
    // ADDITIONAL ANIMATIONS CSS
    // ============================================
    
    // Inject additional animation styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeOut {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(-20px); }
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes cardPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes modalSlideOut {
            from { opacity: 1; transform: scale(1) translateY(0); }
            to { opacity: 0; transform: scale(0.9) translateY(20px); }
        }
        
        @keyframes slideOutRight {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(100%); }
        }
        
        .loading-spinner {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        /* Smooth transitions for all interactive elements */
        .nav-card,
        .btn,
        .form-input,
        button {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Hover effects */
        .nav-card:hover {
            transform: translateY(-4px) scale(1.02);
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        /* Focus effects */
        .form-input:focus {
            transform: translateY(-1px);
        }
    `;
    document.head.appendChild(style);
    
    // ============================================
    // INITIALIZATION
    // ============================================
    
    function initialize() {
        // First, ensure all sections are hidden except the one with 'active' class
        document.querySelectorAll('.dashboard-section').forEach(section => {
            const isActive = section.classList.contains('active');
            if (isActive) {
                section.style.display = 'block';
                section.style.opacity = '1';
            } else {
                section.style.display = 'none';
                section.style.opacity = '0';
            }
        });
        
        // Initialize from URL hash
        const hash = window.location.hash.replace('#', '');
        if (hash && document.querySelector(`.dashboard-section[data-section="${hash}"]`)) {
            navigateToSection(hash);
        } else {
            // Show overview by default - ensure it's visible immediately
            const overviewSection = document.querySelector('.dashboard-section[data-section="overview"]');
            if (overviewSection) {
                overviewSection.style.display = 'block';
                overviewSection.style.opacity = '1';
                overviewSection.classList.add('active');
                setActiveNavCard('overview');
                currentSection = 'overview';
            } else {
                // Fallback: show first section if overview doesn't exist
                const firstSection = document.querySelector('.dashboard-section');
                if (firstSection) {
                    const sectionName = firstSection.getAttribute('data-section');
                    navigateToSection(sectionName);
                }
            }
        }
        
        // Initialize schedule table
        initScheduleTable();

        // Apply responsive table labels
        applyResponsiveTableLabels();
        
        // Handle hash changes
        window.addEventListener('hashchange', function() {
            const hash = window.location.hash.replace('#', '');
            if (hash && document.querySelector(`.dashboard-section[data-section="${hash}"]`)) {
                navigateToSection(hash);
            }
        });
        
        // Add smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';
        
        // Add intersection observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.5s ease-out forwards';
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        // Observe cards for fade-in
        document.querySelectorAll('.card').forEach(card => {
            observer.observe(card);
        });
        
        console.log('✅ Dashboard initialized with modern 2026 design');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
    
})();
