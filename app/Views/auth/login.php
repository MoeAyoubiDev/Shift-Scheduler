<?php
declare(strict_types=1);
?>
<div class="login-container">
    <div class="login-wrapper">
        <div class="login-left-panel">
            <div class="login-branding">
                <div class="company-logo">
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect width="48" height="48" rx="8" fill="#1e3a8a"/>
                        <path d="M24 12L32 20H28V32H20V20H16L24 12Z" fill="white"/>
                    </svg>
                </div>
                <h1 class="company-name">Financial Services</h1>
                <h2 class="department-name">Support Department</h2>
                <p class="system-name">Workforce Management Portal</p>
            </div>
            <div class="login-features">
                <div class="feature-item">
                    <span class="feature-icon">🔒</span>
                    <span>Secure Authentication</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">📊</span>
                    <span>Real-time Scheduling</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">👥</span>
                    <span>Team Management</span>
                </div>
            </div>
        </div>
        
        <div class="login-right-panel">
            <div class="login-card">
                <div class="login-header">
                    <h3>Sign In to Your Account</h3>
                    <p>Enter your credentials to access the system</p>
                </div>

                <div class="demo-credentials-box">
                    <div class="demo-header">
                        <span class="demo-icon">🔑</span>
                        <span>Test Credentials</span>
                    </div>
                    <div class="demo-accounts-grid">
                        <div class="demo-account-card" onclick="fillCredentials('director', 'password')">
                            <div class="demo-role">Director</div>
                            <div class="demo-creds">
                                <span>director</span> / <span>password</span>
                            </div>
                        </div>
                        <div class="demo-account-card" onclick="fillCredentials('teamleader', 'password')">
                            <div class="demo-role">Team Leader</div>
                            <div class="demo-creds">
                                <span>teamleader</span> / <span>password</span>
                            </div>
                        </div>
                        <div class="demo-account-card" onclick="fillCredentials('employee', 'password')">
                            <div class="demo-role">Employee</div>
                            <div class="demo-creds">
                                <span>employee</span> / <span>password</span>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="post" action="/index.php" id="login-form" class="login-form">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <span class="input-icon">👤</span>
                            <input type="text" id="username" name="username" required autocomplete="username" placeholder="Enter your username">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <span class="input-icon">🔒</span>
                            <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-login">
                            <span>Sign In</span>
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="security-notice">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M8 1L3 3.5V7C3 10.5 5.5 13.5 8 14.5C10.5 13.5 13 10.5 13 7V3.5L8 1Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>Secure connection. All activities are monitored and logged.</span>
                    </div>
                </form>
            </div>
            
            <div class="login-footer">
                <p>&copy; <?= date('Y') ?> Financial Services. All rights reserved.</p>
                <p class="footer-links">
                    <a href="#">Privacy Policy</a> • <a href="#">Terms of Service</a> • <a href="#">Support</a>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function fillCredentials(username, password) {
    const usernameField = document.getElementById('username');
    const passwordField = document.getElementById('password');
    
    usernameField.value = username;
    passwordField.value = password;
    
    usernameField.style.transition = 'all 0.3s ease';
    passwordField.style.transition = 'all 0.3s ease';
    usernameField.style.backgroundColor = '#fef3c7';
    usernameField.style.borderColor = '#f59e0b';
    passwordField.style.backgroundColor = '#fef3c7';
    passwordField.style.borderColor = '#f59e0b';
    
    usernameField.focus();
    
    setTimeout(() => {
        usernameField.style.backgroundColor = '';
        usernameField.style.borderColor = '';
        passwordField.style.backgroundColor = '';
        passwordField.style.borderColor = '';
    }, 1500);
}
</script>

<script>
function fillCredentials(username, password) {
    const usernameField = document.getElementById('username');
    const passwordField = document.getElementById('password');
    
    // Fill the fields
    usernameField.value = username;
    passwordField.value = password;
    
    // Highlight the fields briefly
    usernameField.style.transition = 'background-color 0.3s ease';
    passwordField.style.transition = 'background-color 0.3s ease';
    usernameField.style.backgroundColor = '#fef3c7';
    passwordField.style.backgroundColor = '#fef3c7';
    
    // Focus on username field
    usernameField.focus();
    
    // Remove highlight after animation
    setTimeout(() => {
        usernameField.style.backgroundColor = '';
        passwordField.style.backgroundColor = '';
    }, 1500);
}
</script>
