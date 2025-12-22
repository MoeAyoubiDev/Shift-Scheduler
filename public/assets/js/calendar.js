/**
 * Professional Calendar Component
 * Modern 2026 design with week selection
 */

(function() {
    'use strict';
    
    let currentWeekStart = null;
    let currentWeekEnd = null;
    let selectedDate = null;
    
    // Initialize calendar
    function initCalendar() {
        // Find all week selector pills
        document.querySelectorAll('.week-selector-pill').forEach(pill => {
            pill.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const weekStart = this.getAttribute('data-week-start');
                const weekEnd = this.getAttribute('data-week-end');
                
                if (weekStart && weekEnd) {
                    currentWeekStart = weekStart;
                    currentWeekEnd = weekEnd;
                    openCalendar(this);
                }
            });
        });
    }
    
    function openCalendar(triggerElement) {
        // Remove existing calendar if any
        const existing = document.getElementById('week-calendar-modal');
        if (existing) {
            existing.remove();
        }
        
        // Create modal overlay
        const modal = document.createElement('div');
        modal.id = 'week-calendar-modal';
        modal.className = 'calendar-modal-overlay';
        modal.innerHTML = createCalendarHTML();
        
        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';
        
        // Position calendar near trigger element
        positionCalendar(modal, triggerElement);
        
        // Initialize calendar functionality
        initCalendarFunctionality(modal);
        
        // Close on overlay click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeCalendar();
            }
        });
        
        // Close on escape key
        const escapeHandler = function(e) {
            if (e.key === 'Escape') {
                closeCalendar();
                document.removeEventListener('keydown', escapeHandler);
            }
        };
        document.addEventListener('keydown', escapeHandler);
    }
    
    function createCalendarHTML() {
        const weekStartDate = new Date(currentWeekStart + 'T00:00:00');
        const currentMonth = weekStartDate.getMonth();
        const currentYear = weekStartDate.getFullYear();
        
        // Calculate current week
        const weekStart = new Date(currentWeekStart + 'T00:00:00');
        const weekEnd = new Date(currentWeekEnd + 'T00:00:00');
        
        return `
            <div class="calendar-modal-content">
                <div class="calendar-header">
                    <h3>Select Week</h3>
                    <button type="button" class="calendar-close" aria-label="Close calendar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="calendar-body">
                    <div class="calendar-navigation">
                        <button type="button" class="calendar-nav-btn" id="prev-month">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <div class="calendar-month-year" id="calendar-month-year">
                            ${getMonthName(currentMonth)} ${currentYear}
                        </div>
                        <button type="button" class="calendar-nav-btn" id="next-month">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                    <div class="calendar-weekdays">
                        <div class="calendar-weekday">Mon</div>
                        <div class="calendar-weekday">Tue</div>
                        <div class="calendar-weekday">Wed</div>
                        <div class="calendar-weekday">Thu</div>
                        <div class="calendar-weekday">Fri</div>
                        <div class="calendar-weekday">Sat</div>
                        <div class="calendar-weekday">Sun</div>
                    </div>
                    <div class="calendar-days" id="calendar-days">
                        ${generateCalendarDays(currentMonth, currentYear, weekStart, weekEnd)}
                    </div>
                    <div class="calendar-actions">
                        <button type="button" class="btn btn-secondary" id="calendar-today">Today</button>
                        <button type="button" class="btn btn-primary" id="calendar-select-week">Select Week</button>
                    </div>
                </div>
            </div>
        `;
    }
    
    function generateCalendarDays(month, year, weekStart, weekEnd) {
        const firstDay = new Date(year, month, 1);
        const startDate = new Date(firstDay);
        // Get Monday of the week containing the first day of month
        const dayOfWeek = firstDay.getDay();
        const daysToMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
        startDate.setDate(firstDay.getDate() - daysToMonday);
        
        let html = '';
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const weekStartDate = new Date(weekStart);
        weekStartDate.setHours(0, 0, 0, 0);
        const weekEndDate = new Date(weekEnd);
        weekEndDate.setHours(23, 59, 59, 999);
        
        for (let i = 0; i < 42; i++) {
            const date = new Date(startDate);
            date.setDate(startDate.getDate() + i);
            date.setHours(0, 0, 0, 0);
            
            const dateStr = formatDate(date);
            const isCurrentMonth = date.getMonth() === month;
            const isToday = dateStr === formatDate(today);
            const isInCurrentWeek = date >= weekStartDate && date <= weekEndDate;
            const isWeekStart = dateStr === formatDate(weekStartDate);
            const isWeekEnd = dateStr === formatDate(weekEndDate);
            
            let classes = 'calendar-day';
            if (!isCurrentMonth) classes += ' calendar-day-other-month';
            if (isToday) classes += ' calendar-day-today';
            if (isInCurrentWeek) classes += ' calendar-day-in-week';
            if (isWeekStart) classes += ' calendar-day-week-start';
            if (isWeekEnd) classes += ' calendar-day-week-end';
            
            html += `
                <div class="${classes}" data-date="${dateStr}">
                    <span class="calendar-day-number">${date.getDate()}</span>
                </div>
            `;
        }
        
        return html;
    }
    
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    function getMonthName(month) {
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                       'July', 'August', 'September', 'October', 'November', 'December'];
        return months[month];
    }
    
    function positionCalendar(modal, trigger) {
        const rect = trigger.getBoundingClientRect();
        const calendarContent = modal.querySelector('.calendar-modal-content');
        
        // Position below the trigger, centered
        calendarContent.style.position = 'absolute';
        calendarContent.style.top = (rect.bottom + 10) + 'px';
        calendarContent.style.left = Math.max(20, rect.left - 200) + 'px';
        
        // Adjust if goes off screen
        setTimeout(() => {
            const contentRect = calendarContent.getBoundingClientRect();
            if (contentRect.right > window.innerWidth) {
                calendarContent.style.left = (window.innerWidth - contentRect.width - 20) + 'px';
            }
            if (contentRect.left < 0) {
                calendarContent.style.left = '20px';
            }
            if (contentRect.bottom > window.innerHeight) {
                calendarContent.style.top = (rect.top - contentRect.height - 10) + 'px';
            }
        }, 10);
    }
    
    function initCalendarFunctionality(modal) {
        const weekStartDate = new Date(currentWeekStart + 'T00:00:00');
        let currentMonth = weekStartDate.getMonth();
        let currentYear = weekStartDate.getFullYear();
        
        const weekStart = new Date(currentWeekStart + 'T00:00:00');
        const weekEnd = new Date(currentWeekEnd + 'T00:00:00');
        selectedDate = currentWeekStart;
        
        // Close button
        modal.querySelector('.calendar-close').addEventListener('click', closeCalendar);
        
        // Navigation buttons
        modal.querySelector('#prev-month').addEventListener('click', () => {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            updateCalendar(modal, currentMonth, currentYear, weekStart, weekEnd);
        });
        
        modal.querySelector('#next-month').addEventListener('click', () => {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            updateCalendar(modal, currentMonth, currentYear, weekStart, weekEnd);
        });
        
        // Today button
        modal.querySelector('#calendar-today').addEventListener('click', () => {
            const today = new Date();
            currentMonth = today.getMonth();
            currentYear = today.getFullYear();
            updateCalendar(modal, currentMonth, currentYear, weekStart, weekEnd);
        });
        
        // Day click handlers
        modal.querySelectorAll('.calendar-day').forEach(day => {
            day.addEventListener('click', function() {
                const dateStr = this.getAttribute('data-date');
                if (dateStr) {
                    selectWeekFromDate(modal, dateStr, currentMonth, currentYear);
                }
            });
        });
        
        // Select week button
        const selectWeekBtn = modal.querySelector('#calendar-select-week');
        selectWeekBtn.addEventListener('click', () => {
            if (selectedDate) {
                navigateToWeek(selectedDate);
            } else {
                navigateToWeek(currentWeekStart);
            }
        });
    }
    
    function updateCalendar(modal, month, year, weekStart, weekEnd) {
        modal.querySelector('#calendar-month-year').textContent = `${getMonthName(month)} ${year}`;
        modal.querySelector('#calendar-days').innerHTML = generateCalendarDays(month, year, weekStart, weekEnd);
        
        // Re-attach day click handlers
        modal.querySelectorAll('.calendar-day').forEach(day => {
            day.addEventListener('click', function() {
                const dateStr = this.getAttribute('data-date');
                if (dateStr) {
                    selectWeekFromDate(modal, dateStr, month, year);
                }
            });
        });
        
        // Update selected week display
        if (selectedDate) {
            const selectedWeekStart = new Date(selectedDate + 'T00:00:00');
            const selectedWeekEnd = new Date(selectedWeekStart);
            selectedWeekEnd.setDate(selectedWeekStart.getDate() + 6);
            
            modal.querySelector('#calendar-select-week').innerHTML = 
                `Select Week (${formatDateDisplay(selectedWeekStart)} → ${formatDateDisplay(selectedWeekEnd)})`;
        }
    }
    
    function formatDateDisplay(date) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${months[date.getMonth()]} ${date.getDate()}`;
    }
    
    function selectWeekFromDate(modal, dateStr, month, year) {
        const date = new Date(dateStr + 'T00:00:00');
        const dayOfWeek = date.getDay();
        const diff = dayOfWeek === 0 ? -6 : 1 - dayOfWeek; // Monday = 1
        
        const weekStart = new Date(date);
        weekStart.setDate(date.getDate() + diff);
        weekStart.setHours(0, 0, 0, 0);
        
        const weekEnd = new Date(weekStart);
        weekEnd.setDate(weekStart.getDate() + 6);
        weekEnd.setHours(23, 59, 59, 999);
        
        selectedDate = formatDate(weekStart);
        currentWeekStart = formatDate(weekStart);
        currentWeekEnd = formatDate(weekEnd);
        
        // Update calendar display
        updateCalendar(modal, month, year, weekStart, weekEnd);
    }
    
    function navigateToWeek(weekStartDate) {
        const url = new URL(window.location);
        url.searchParams.set('week_start', weekStartDate);
        
        const weekEndDate = new Date(weekStartDate + 'T00:00:00');
        weekEndDate.setDate(weekEndDate.getDate() + 6);
        url.searchParams.set('week_end', formatDate(weekEndDate));
        
        window.location.href = url.toString();
    }
    
    function closeCalendar() {
        const modal = document.getElementById('week-calendar-modal');
        if (modal) {
            modal.style.animation = 'fadeOut 0.2s ease-out';
            setTimeout(() => {
                modal.remove();
                document.body.style.overflow = '';
            }, 200);
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCalendar);
    } else {
        initCalendar();
    }
    
})();
