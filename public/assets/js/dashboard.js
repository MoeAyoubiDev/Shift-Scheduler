/**
 * Dashboard Navigation & Functionality
 * Clean, robust implementation - all buttons work correctly
 */

(function() {
    'use strict';
    
    // Global flags
    window.dashboardScriptLoaded = true;
    let currentSection = 'overview';
    
    // ============================================
    // NAVIGATION SYSTEM
    // ============================================
    
    function showSection(sectionName) {
        if (!sectionName) return;
        
        // Hide all sections
        document.querySelectorAll('.dashboard-section').forEach(section => {
            section.classList.remove('active');
            section.style.display = 'none';
        });
        
        // Show target section
        const targetSection = document.querySelector(`.dashboard-section[data-section="${sectionName}"]`);
        if (targetSection) {
            targetSection.classList.add('active');
            targetSection.style.display = 'block';
            currentSection = sectionName;
            
            // Scroll to top
            setTimeout(() => {
                targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    }
    
    function setActiveNavCard(sectionName) {
        document.querySelectorAll('.nav-card').forEach(card => {
            card.classList.remove('active');
        });
        
        const targetCard = document.querySelector(`.nav-card[data-section="${sectionName}"]`);
        if (targetCard) {
            targetCard.classList.add('active');
        }
    }
    
    function navigateToSection(sectionName) {
        if (!sectionName) return;
        
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
    // FORM HANDLING - DO NOT PREVENT DEFAULT
    // ============================================
    
    // Handle form submissions - ONLY for AJAX forms, not regular forms
    document.addEventListener('submit', function(e) {
        const form = e.target;
        
        // Only handle forms with data-ajax="true" attribute
        if (form.tagName === 'FORM' && form.getAttribute('data-ajax') === 'true') {
            e.preventDefault();
            handleAjaxForm(form);
        }
        // All other forms submit normally - DO NOT PREVENT
    });
    
    function handleAjaxForm(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : '';
        
        // Show loading state
        if (window.LoadingManager) {
            window.LoadingManager.button(submitBtn, true);
        } else if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
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
            if (window.LoadingManager) {
                window.LoadingManager.button(submitBtn, false);
            } else if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
            
            // Show notification
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
            } else if (data.success && form.id === 'assign-shift-form') {
                // Special handling for assign shift - reload after delay
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        })
        .catch(error => {
            // Remove loading state
            if (window.LoadingManager) {
                window.LoadingManager.button(submitBtn, false);
            } else if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
            
            // Show error
            if (window.NotificationManager) {
                window.NotificationManager.error('Network error. Please try again.');
            } else {
                showNotification('Network error. Please try again.', 'error');
            }
            
            console.error('Form submission error:', error);
        });
    }
    
    // ============================================
    // NAVIGATION CLICKS - ONLY NAVIGATION
    // ============================================
    
    document.addEventListener('click', function(e) {
        const target = e.target;
        const button = target.closest('button');
        
        // Navigation cards - ONLY these prevent default
        if (button && button.classList.contains('nav-card')) {
            e.preventDefault();
            e.stopPropagation();
            const sectionName = button.getAttribute('data-section');
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
                const match = onclick.match(/navigateToSection\(['"]([^'"]+)['"]\)/);
                if (match && match[1]) {
                    navigateToSection(match[1]);
                }
                return false;
            }
        }
        
        // Quick action cards
        if (button && button.classList.contains('quick-action-card')) {
            const onclick = button.getAttribute('onclick');
            if (onclick && onclick.includes('navigateToSection')) {
                e.preventDefault();
                e.stopPropagation();
                const match = onclick.match(/navigateToSection\(['"]([^'"]+)['"]\)/);
                if (match && match[1]) {
                    navigateToSection(match[1]);
                }
                return false;
            }
        }
        
        // Modal triggers - open assign modal
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
        if (target.closest('.modal-close') || target.closest('.modal-cancel') || 
            (target.classList.contains('modal-overlay') && target.id === 'assign-modal')) {
            e.preventDefault();
            e.stopPropagation();
            closeModal();
            return false;
        }
        
        // All other clicks (including form buttons) work normally
        // DO NOT prevent default for form submissions
    }, true); // Use capture phase
    
    // ============================================
    // MODAL FUNCTIONS
    // ============================================
    
    function openAssignModal(date, employeeId, assignmentId, shiftDefId, requestId) {
        const modal = document.getElementById('assign-modal');
        if (!modal) return;
        
        const dateInput = document.getElementById('assign-date');
        const employeeSelect = document.getElementById('assign-employee-select');
        const requestIdInput = document.getElementById('assign-request-id');
        const shiftDefSelect = document.getElementById('assign-shift-def');
        
        if (dateInput) dateInput.value = date || '';
        if (employeeSelect) employeeSelect.value = employeeId || '';
        if (requestIdInput) requestIdInput.value = requestId || '';
        if (shiftDefSelect && shiftDefId) {
            shiftDefSelect.value = shiftDefId;
            shiftDefSelect.dispatchEvent(new Event('change'));
        }
        
        modal.style.display = 'flex';
    }
    
    function closeModal() {
        const modal = document.getElementById('assign-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    window.openAssignModal = openAssignModal;
    window.closeModal = closeModal;
    
    // Auto-fill shift times when shift definition changes
    const shiftDefSelect = document.getElementById('assign-shift-def');
    if (shiftDefSelect) {
        shiftDefSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            const startTime = option.getAttribute('data-start');
            const endTime = option.getAttribute('data-end');
            const startInput = document.getElementById('assign-start-time');
            const endInput = document.getElementById('assign-end-time');
            
            if (startInput && startTime) startInput.value = startTime;
            if (endInput && endTime) endInput.value = endTime;
        });
    }
    
    // ============================================
    // SCHEDULE TABLE INTERACTIVITY
    // ============================================
    
    function initScheduleTable() {
        // Date range selector
        const dateInputs = document.querySelectorAll('.date-input');
        dateInputs.forEach(input => {
            input.addEventListener('change', function() {
                const weekStart = document.getElementById('week-start')?.value;
                const weekEnd = document.getElementById('week-end')?.value;
                if (weekStart && weekEnd) {
                    window.location.href = `/index.php?week_start=${weekStart}&week_end=${weekEnd}`;
                }
            });
        });
        
        // Filter input
        const filterInput = document.getElementById('filter-employees');
        if (filterInput) {
            filterInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                document.querySelectorAll('.employee-row').forEach(row => {
                    const name = row.textContent.toLowerCase();
                    row.style.display = name.includes(query) ? '' : 'none';
                });
            });
        }
    }
    
    // ============================================
    // NOTIFICATION SYSTEM
    // ============================================
    
    function showNotification(message, type = 'info') {
        if (window.NotificationManager) {
            window.NotificationManager.show(message, type);
        } else {
            // Fallback simple notification
            const existing = document.querySelector('.ajax-notification');
            if (existing) existing.remove();
            
            const notification = document.createElement('div');
            notification.className = `ajax-notification ajax-notification-${type}`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => notification.classList.add('show'), 10);
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    }
    
    window.showNotification = showNotification;
    
    // ============================================
    // INITIALIZATION
    // ============================================
    
    function initialize() {
        // Initialize from URL hash
        const hash = window.location.hash.replace('#', '');
        if (hash) {
            navigateToSection(hash);
        } else {
            // Show overview by default
            navigateToSection('overview');
        }
        
        // Initialize schedule table
        initScheduleTable();
        
        // Handle hash changes
        window.addEventListener('hashchange', function() {
            const hash = window.location.hash.replace('#', '');
            if (hash) {
                navigateToSection(hash);
            }
        });
        
        console.log('Dashboard initialized successfully');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
    
})();
