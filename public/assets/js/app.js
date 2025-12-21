(() => {
    const notices = document.querySelectorAll('.notice');
    notices.forEach((notice) => {
        notice.addEventListener('click', () => notice.classList.add('is-hidden'));
    });
})();

// Beautiful Clock Widget
(function() {
    'use strict';
    
    function updateClock() {
        const now = new Date();
        const hours = now.getHours();
        const minutes = now.getMinutes();
        const seconds = now.getSeconds();
        
        // Calculate angles for clock hands
        const secondAngle = seconds * 6; // 360 / 60 = 6 degrees per second
        const minuteAngle = minutes * 6 + seconds * 0.1; // 6 degrees per minute + 0.1 per second
        const hourAngle = (hours % 12) * 30 + minutes * 0.5; // 30 degrees per hour + 0.5 per minute
        
        // Update analog clock hands
        const hourHand = document.getElementById('hour-hand');
        const minuteHand = document.getElementById('minute-hand');
        const secondHand = document.getElementById('second-hand');
        
        if (hourHand) {
            hourHand.style.transform = `rotate(${hourAngle}deg)`;
        }
        if (minuteHand) {
            minuteHand.style.transform = `rotate(${minuteAngle}deg)`;
        }
        if (secondHand) {
            secondHand.style.transform = `rotate(${secondAngle}deg)`;
        }
        
        // Update digital clock
        const timeDisplay = document.getElementById('time-display');
        const dateDisplay = document.getElementById('date-display');
        const footerTime = document.getElementById('footer-time');
        
        if (timeDisplay) {
            const timeString = String(hours).padStart(2, '0') + ':' + 
                             String(minutes).padStart(2, '0') + ':' + 
                             String(seconds).padStart(2, '0');
            timeDisplay.textContent = timeString;
        }
        
        if (dateDisplay) {
            const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const dayName = days[now.getDay()];
            const monthName = months[now.getMonth()];
            const day = now.getDate();
            const year = now.getFullYear();
            dateDisplay.textContent = `${dayName} ${day} ${monthName} ${year}`;
        }
        
        if (footerTime) {
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const dayName = days[now.getDay()];
            const monthName = months[now.getMonth()];
            const day = now.getDate();
            const year = now.getFullYear();
            const timeString = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
            footerTime.textContent = `${dayName}, ${monthName} ${day}, ${year} at ${timeString}`;
        }
    }
    
    // Initialize clock immediately
    updateClock();
    
    // Update every second
    setInterval(updateClock, 1000);
    
    // Also update on page visibility change
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            updateClock();
        }
    });
})();
