/**
 * Enhanced Professional Features
 * Loading states, advanced notifications, form validation, and more
 */

(function() {
    'use strict';
    
    // ============================================
    // ENHANCED NOTIFICATION SYSTEM
    // ============================================
    
    const NotificationManager = {
        container: null,
        notifications: [],
        
        init() {
            // Create notification container
            this.container = document.createElement('div');
            this.container.className = 'notification-container';
            this.container.setAttribute('aria-live', 'polite');
            this.container.setAttribute('aria-atomic', 'true');
            document.body.appendChild(this.container);
        },
        
        show(message, type = 'info', duration = 4000, options = {}) {
            if (!this.container) this.init();
            
            const id = 'notif-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.setAttribute('data-id', id);
            notification.setAttribute('role', 'alert');
            
            // Icon based on type
            const icons = {
                success: '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M16.667 5L7.5 14.167 3.333 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                error: '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M15 5L5 15M5 5L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
                warning: '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 6.667V10M10 13.333H10.008M18.333 10C18.333 14.602 14.602 18.333 10 18.333C5.398 18.333 1.667 14.602 1.667 10C1.667 5.398 5.398 1.667 10 1.667C14.602 1.667 18.333 5.398 18.333 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
                info: '<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 9.167V13.333M10 6.667H10.008M18.333 10C18.333 14.602 14.602 18.333 10 18.333C5.398 18.333 1.667 14.602 1.667 10C1.667 5.398 5.398 1.667 10 1.667C14.602 1.667 18.333 5.398 18.333 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>'
            };
            
            notification.innerHTML = `
                <div class="notification-content">
                    <div class="notification-icon">${icons[type] || icons.info}</div>
                    <div class="notification-message">${this.escapeHtml(message)}</div>
                    ${options.action ? `<button class="notification-action" data-action="${options.action}">${options.actionLabel || 'Action'}</button>` : ''}
                </div>
                <button class="notification-close" aria-label="Close notification">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                        <path d="M12 4L4 12M4 4L12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            `;
            
            this.container.appendChild(notification);
            this.notifications.push({ id, element: notification });
            
            // Animate in
            requestAnimationFrame(() => {
                notification.classList.add('show');
            });
            
            // Auto-dismiss
            if (duration > 0) {
                setTimeout(() => {
                    this.dismiss(id);
                }, duration);
            }
            
            // Close button
            notification.querySelector('.notification-close').addEventListener('click', () => {
                this.dismiss(id);
            });
            
            // Action button
            if (options.action) {
                notification.querySelector('.notification-action').addEventListener('click', () => {
                    if (options.onAction) options.onAction();
                    this.dismiss(id);
                });
            }
            
            return id;
        },
        
        dismiss(id) {
            const index = this.notifications.findIndex(n => n.id === id);
            if (index === -1) return;
            
            const notification = this.notifications[index].element;
            notification.classList.remove('show');
            notification.classList.add('dismissing');
            
            setTimeout(() => {
                notification.remove();
                this.notifications.splice(index, 1);
            }, 300);
        },
        
        success(message, duration, options) {
            return this.show(message, 'success', duration, options);
        },
        
        error(message, duration, options) {
            return this.show(message, 'error', duration, options);
        },
        
        warning(message, duration, options) {
            return this.show(message, 'warning', duration, options);
        },
        
        info(message, duration, options) {
            return this.show(message, 'info', duration, options);
        },
        
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };
    
    // ============================================
    // LOADING STATE MANAGER
    // ============================================
    
    const LoadingManager = {
        show(element, message = 'Loading...') {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }
            if (!element) return;
            
            // Create loading overlay
            const overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner-ring"></div>
                    <div class="spinner-ring"></div>
                    <div class="spinner-ring"></div>
                </div>
                <div class="loading-message">${this.escapeHtml(message)}</div>
            `;
            
            element.style.position = 'relative';
            element.appendChild(overlay);
            
            return overlay;
        },
        
        hide(element) {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }
            if (!element) return;
            
            const overlay = element.querySelector('.loading-overlay');
            if (overlay) {
                overlay.classList.add('fade-out');
                setTimeout(() => overlay.remove(), 300);
            }
        },
        
        button(button, loading = true) {
            if (typeof button === 'string') {
                button = document.querySelector(button);
            }
            if (!button) return;
            
            if (loading) {
                button.dataset.originalText = button.textContent;
                button.disabled = true;
                button.innerHTML = `
                    <span class="button-spinner"></span>
                    <span>${button.dataset.loadingText || 'Loading...'}</span>
                `;
            } else {
                button.disabled = false;
                button.textContent = button.dataset.originalText || 'Submit';
            }
        },
        
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };
    
    // ============================================
    // ENHANCED FORM VALIDATION
    // ============================================
    
    const FormValidator = {
        validate(form) {
            if (typeof form === 'string') {
                form = document.querySelector(form);
            }
            if (!form) return { valid: false, errors: [] };
            
            const errors = [];
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            
            inputs.forEach(input => {
                const error = this.validateField(input);
                if (error) {
                    errors.push({ field: input, error });
                    this.showFieldError(input, error);
                } else {
                    this.clearFieldError(input);
                }
            });
            
            return {
                valid: errors.length === 0,
                errors
            };
        },
        
        validateField(field) {
            const value = field.value.trim();
            const type = field.type;
            const required = field.hasAttribute('required');
            
            // Required validation
            if (required && value === '') {
                return `${field.labels?.[0]?.textContent || 'This field'} is required.`;
            }
            
            // Email validation
            if (type === 'email' && value && !this.isValidEmail(value)) {
                return 'Please enter a valid email address.';
            }
            
            // Password validation
            if (type === 'password' && value && value.length < 6) {
                return 'Password must be at least 6 characters.';
            }
            
            // Custom validation
            if (field.dataset.validate) {
                const validator = field.dataset.validate;
                if (validator === 'username' && value && !/^[a-zA-Z0-9_]+$/.test(value)) {
                    return 'Username can only contain letters, numbers, and underscores.';
                }
                if (validator === 'employee-code' && value && !/^[A-Z0-9-]+$/.test(value)) {
                    return 'Employee code format is invalid.';
                }
            }
            
            return null;
        },
        
        showFieldError(field, message) {
            this.clearFieldError(field);
            field.classList.add('error');
            
            const errorElement = document.createElement('div');
            errorElement.className = 'field-error';
            errorElement.textContent = message;
            errorElement.setAttribute('role', 'alert');
            
            field.parentElement.appendChild(errorElement);
        },
        
        clearFieldError(field) {
            field.classList.remove('error');
            const errorElement = field.parentElement.querySelector('.field-error');
            if (errorElement) {
                errorElement.remove();
            }
        },
        
        isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }
    };
    
    // ============================================
    // ENHANCED AJAX HANDLER
    // ============================================
    
    const AjaxHandler = {
        async request(url, options = {}) {
            const {
                method = 'POST',
                data = null,
                showLoading = true,
                showSuccess = true,
                showError = true,
                successMessage = 'Operation completed successfully.',
                onSuccess = null,
                onError = null
            } = options;
            
            const formData = data instanceof FormData ? data : new FormData();
            if (data && !(data instanceof FormData)) {
                Object.keys(data).forEach(key => {
                    formData.append(key, data[key]);
                });
            }
            
            let loadingOverlay = null;
            if (showLoading) {
                loadingOverlay = LoadingManager.show(document.body, 'Processing...');
            }
            
            try {
                const response = await fetch(url, {
                    method,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(options.headers || {})
                    },
                    body: method !== 'GET' ? formData : null
                });
                
                const result = await response.json();
                
                if (showLoading) {
                    LoadingManager.hide(document.body);
                }
                
                if (result.success !== false) {
                    if (showSuccess && successMessage) {
                        NotificationManager.success(successMessage);
                    }
                    if (onSuccess) onSuccess(result);
                    return result;
                } else {
                    if (showError) {
                        NotificationManager.error(result.message || 'An error occurred.');
                    }
                    if (onError) onError(result);
                    throw new Error(result.message || 'Request failed');
                }
            } catch (error) {
                if (showLoading) {
                    LoadingManager.hide(document.body);
                }
                if (showError) {
                    NotificationManager.error(error.message || 'Network error. Please try again.');
                }
                if (onError) onError(error);
                throw error;
            }
        }
    };
    
    // ============================================
    // SEARCH & FILTER ENHANCEMENTS
    // ============================================
    
    const SearchFilter = {
        init(container, options = {}) {
            const {
                searchInput = '.search-input',
                filterSelects = '.filter-select',
                targetSelector = 'tbody tr',
                searchFields = ['textContent']
            } = options;
            
            const searchField = container.querySelector(searchInput);
            const filterFields = container.querySelectorAll(filterSelects);
            const targetElements = container.querySelectorAll(targetSelector);
            
            if (searchField) {
                searchField.addEventListener('input', (e) => {
                    this.filter(targetElements, e.target.value, searchFields);
                });
            }
            
            filterFields.forEach(filter => {
                filter.addEventListener('change', () => {
                    this.applyFilters(container, targetSelector);
                });
            });
        },
        
        filter(elements, query, fields) {
            const searchTerm = query.toLowerCase().trim();
            
            elements.forEach(element => {
                const text = fields.map(field => {
                    if (field === 'textContent') return element.textContent;
                    return element.querySelector(field)?.textContent || '';
                }).join(' ').toLowerCase();
                
                if (text.includes(searchTerm)) {
                    element.classList.remove('hidden');
                } else {
                    element.classList.add('hidden');
                }
            });
        },
        
        applyFilters(container, targetSelector) {
            // Implementation for multi-filter
            const filters = {};
            container.querySelectorAll('.filter-select').forEach(select => {
                if (select.value) {
                    filters[select.name] = select.value;
                }
            });
            
            const elements = container.querySelectorAll(targetSelector);
            elements.forEach(element => {
                let matches = true;
                Object.keys(filters).forEach(key => {
                    const value = element.dataset[key];
                    if (value && value !== filters[key]) {
                        matches = false;
                    }
                });
                element.classList.toggle('hidden', !matches);
            });
        }
    };
    
    // ============================================
    // EXPORT TO GLOBAL
    // ============================================
    
    window.NotificationManager = NotificationManager;
    window.LoadingManager = LoadingManager;
    window.FormValidator = FormValidator;
    window.AjaxHandler = AjaxHandler;
    window.SearchFilter = SearchFilter;
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            NotificationManager.init();
        });
    } else {
        NotificationManager.init();
    }
    
    // Enhanced showNotification for backward compatibility
    window.showNotification = function(message, type = 'info') {
        NotificationManager.show(message, type);
    };
    
    console.log('Enhanced features loaded');
})();

