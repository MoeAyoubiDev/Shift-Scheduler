<?php
declare(strict_types=1);
?>
<div class="login-container">
    <div class="login-card">
        <!-- Logo/Branding Area -->
        <div class="login-brand">
            <div class="brand-logo">
                <svg width="40" height="40" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="48" height="48" rx="10" fill="#4f46e5"/>
                    <path d="M24 12L32 20H28V32H20V20H16L24 12Z" fill="white"/>
                </svg>
            </div>
            <h1 class="brand-title">Shift Scheduler</h1>
            <p class="brand-subtitle">Workforce Management Portal</p>
        </div>

        <!-- Login Form -->
        <form method="post" action="/index.php" id="login-form" class="login-form">
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <div class="input-container">
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input" 
                        required 
                        autocomplete="username" 
                        placeholder="Enter your username"
                        aria-label="Username"
                    >
                </div>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-container password-container">
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        required 
                        autocomplete="current-password" 
                        placeholder="Enter your password"
                        aria-label="Password"
                    >
                    <button 
                        type="button" 
                        class="password-toggle" 
                        id="password-toggle" 
                        aria-label="Toggle password visibility"
                        tabindex="-1"
                    >
                        <svg class="icon-eye" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10 3C5.5 3 1.73 5.61 0 9C1.73 12.39 5.5 15 10 15C14.5 15 18.27 12.39 20 9C18.27 5.61 14.5 3 10 3ZM10 13C7.24 13 5 10.76 5 8C5 5.24 7.24 3 10 3C12.76 3 15 5.24 15 8C15 10.76 12.76 13 10 13ZM10 5C8.34 5 7 6.34 7 8C7 9.66 8.34 11 10 11C11.66 11 13 9.66 13 8C13 6.34 11.66 5 10 5Z" fill="currentColor"/>
                        </svg>
                        <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                            <path d="M2.5 2.5L17.5 17.5M8.16 8.16C7.84 8.5 7.65 8.96 7.65 9.45C7.65 10.43 8.42 11.2 9.4 11.2C9.89 11.2 10.35 11.01 10.69 10.69M14.84 14.84C13.94 15.54 12.78 16 11.5 16C7.91 16 4.81 13.92 2.5 10.5C3.46 8.64 4.9 7.2 6.66 6.34M12.41 4.41C13.5 4.78 14.52 5.32 15.43 6C18.09 8.08 21.19 10.16 24.5 10.5C23.54 12.36 22.1 13.8 20.34 14.66" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10.59 6.59C11.37 6.22 12.15 6 12.5 6C15.09 6 17.19 8.1 17.19 10.69C17.19 11.04 16.97 11.82 16.6 12.6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary" id="submit-btn">
                    <span>Sign In</span>
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6.75 13.5L11.25 9L6.75 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            
            <div class="security-notice">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8 1L3 3.5V7C3 10.5 5.5 13.5 8 14.5C10.5 13.5 13 10.5 13 7V3.5L8 1Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Secure connection. All activities are monitored and logged.</span>
            </div>
        </form>

        <!-- Footer -->
        <div class="login-footer">
            <p>&copy; <?= date('Y') ?> Financial Services. All rights reserved.</p>
        </div>
    </div>
</div>

<script>
// Password toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const passwordToggle = document.getElementById('password-toggle');
    const passwordInput = document.getElementById('password');
    const iconEye = passwordToggle.querySelector('.icon-eye');
    const iconEyeOff = passwordToggle.querySelector('.icon-eye-off');
    
    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            if (type === 'text') {
                iconEye.style.display = 'none';
                iconEyeOff.style.display = 'block';
            } else {
                iconEye.style.display = 'block';
                iconEyeOff.style.display = 'none';
            }
        });
    }
    
    // Form submission animation
    const form = document.getElementById('login-form');
    const submitBtn = document.getElementById('submit-btn');
    
    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });
    }
});
</script>
