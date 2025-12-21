/**
 * Enterprise Team Leader Dashboard - State-Driven Navigation & Functionality
 * Modern, production-ready dashboard with hash-based routing and state management
 */

(function() {
    'use strict';

    // Dashboard State Manager
    const DashboardState = {
        currentSection: 'overview',
        initialized: false,
        
        init() {
            if (this.initialized) return;
            
            // Initialize from URL hash or default to overview
            const hash = window.location.hash.replace('#', '');
            this.currentSection = hash || 'overview';
            
            // Set up navigation
            this.setupNavigation();
            this.setupInboxTabs();
            this.setupBulkActions();
            this.setupRequestActions();
            
            // Show initial section
            this.showSection(this.currentSection);
            
            // Listen for hash changes
            window.addEventListener('hashchange', () => {
                const newHash = window.location.hash.replace('#', '');
                if (newHash && newHash !== this.currentSection) {
                    this.currentSection = newHash;
                    this.showSection(this.currentSection);
                }
            });
            
            this.initialized = true;
            window.dashboardReady = true;
            
            // Log initialization (dev mode only)
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                console.log('[Dashboard] Initialized successfully. Current section:', this.currentSection);
            }
        },
        
        setupNavigation() {
            const navCards = document.querySelectorAll('.nav-card');
            navCards.forEach(card => {
                card.addEventListener('click', (e) => {
                    e.preventDefault();
                    const section = card.getAttribute('data-section');
                    if (section) {
                        this.navigateToSection(section);
                    }
                });
            });
        },
        
        navigateToSection(sectionName) {
            this.currentSection = sectionName;
            window.location.hash = sectionName;
            this.showSection(sectionName);
        },
        
        showSection(sectionName) {
            // Hide all sections
            const allSections = document.querySelectorAll('.dashboard-section');
            allSections.forEach(section => {
                section.classList.remove('active');
            });
            
            // Show target section
            const targetSection = document.querySelector(`.dashboard-section[data-section="${sectionName}"]`);
            if (targetSection) {
                targetSection.classList.add('active');
            }
            
            // Update nav cards
            const navCards = document.querySelectorAll('.nav-card');
            navCards.forEach(card => {
                if (card.getAttribute('data-section') === sectionName) {
                    card.classList.add('active');
                } else {
                    card.classList.remove('active');
                }
            });
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        
        setupInboxTabs() {
            const tabs = document.querySelectorAll('.inbox-tab');
            const contents = document.querySelectorAll('.inbox-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const tabName = tab.getAttribute('data-tab');
                    
                    // Update tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    
                    // Update contents
                    contents.forEach(content => {
                        if (content.getAttribute('data-tab-content') === tabName) {
                            content.style.display = 'block';
                        } else {
                            content.style.display = 'none';
                        }
                    });
                });
            });
        },
        
        setupBulkActions() {
            const checkboxes = document.querySelectorAll('.request-select');
            const bulkApproveBtn = document.getElementById('bulk-approve-btn');
            const bulkRejectBtn = document.getElementById('bulk-reject-btn');
            
            function updateBulkButtons() {
                const selected = Array.from(checkboxes).filter(cb => cb.checked);
                const hasSelection = selected.length > 0;
                
                if (bulkApproveBtn) bulkApproveBtn.disabled = !hasSelection;
                if (bulkRejectBtn) bulkRejectBtn.disabled = !hasSelection;
            }
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateBulkButtons);
            });
            
            if (bulkApproveBtn) {
                bulkApproveBtn.addEventListener('click', () => {
                    const selected = Array.from(checkboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);
                    
                    if (selected.length === 0) return;
                    
                    const form = document.getElementById('bulk-request-form');
                    if (form) {
                        document.getElementById('bulk-status-input').value = 'APPROVED';
                        document.getElementById('bulk-request-ids').value = selected.join(',');
                        form.submit();
                    }
                });
            }
            
            if (bulkRejectBtn) {
                bulkRejectBtn.addEventListener('click', () => {
                    const selected = Array.from(checkboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);
                    
                    if (selected.length === 0) return;
                    
                    if (confirm(`Reject ${selected.length} request(s)?`)) {
                        const form = document.getElementById('bulk-request-form');
                        if (form) {
                            document.getElementById('bulk-status-input').value = 'DECLINED';
                            document.getElementById('bulk-request-ids').value = selected.join(',');
                            form.submit();
                        }
                    }
                });
            }
        },
        
        setupRequestActions() {
            // Approve & Assign
            document.querySelectorAll('.request-approve-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const requestId = btn.getAttribute('data-request-id');
                    const employeeId = btn.getAttribute('data-employee-id');
                    const date = btn.getAttribute('data-date');
                    const shiftId = btn.getAttribute('data-shift-id');
                    
                    // Open assign modal with pre-filled data
                    if (window.openAssignModal) {
                        window.openAssignModal(date, employeeId, null, shiftId, requestId);
                    } else {
                        // Fallback: just approve
                        this.updateRequestStatus(requestId, 'APPROVED');
                    }
                });
            });
            
            // Approve Only
            document.querySelectorAll('.request-approve-only-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const requestId = btn.getAttribute('data-request-id');
                    this.updateRequestStatus(requestId, 'APPROVED');
                });
            });
            
            // Reject
            document.querySelectorAll('.request-reject-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const requestId = btn.getAttribute('data-request-id');
                    if (confirm('Are you sure you want to reject this request?')) {
                        this.updateRequestStatus(requestId, 'DECLINED');
                    }
                });
            });
        },
        
        updateRequestStatus(requestId, status) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/index.php';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'update_request_status';
            form.appendChild(actionInput);
            
            const requestIdInput = document.createElement('input');
            requestIdInput.type = 'hidden';
            requestIdInput.name = 'request_id';
            requestIdInput.value = requestId;
            form.appendChild(requestIdInput);
            
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = status;
            form.appendChild(statusInput);
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = document.querySelector('input[name="csrf_token"]')?.value || '';
            form.appendChild(csrfInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    };
    
    // Expose to window for widget onclick handlers
    window.dashboard = {
        navigateToSection: (section) => DashboardState.navigateToSection(section),
        state: DashboardState
    };
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => DashboardState.init());
    } else {
        DashboardState.init();
    }
    
})();

