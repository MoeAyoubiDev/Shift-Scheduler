<?php
declare(strict_types=1);
// Login page - No demo credentials - Clean version
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

                <form method="post" action="/index.php" id="login-form" class="login-form">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <span class="input-icon input-icon-user">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9 9C11.0711 9 12.75 7.32107 12.75 5.25C12.75 3.17893 11.0711 1.5 9 1.5C6.92893 1.5 5.25 3.17893 5.25 5.25C5.25 7.32107 6.92893 9 9 9Z" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M3.1875 15.5625C3.1875 13.125 5.0625 11.25 7.5 11.25H10.5C12.9375 11.25 14.8125 13.125 14.8125 15.5625" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <input type="text" id="username" name="username" required autocomplete="username" placeholder="Enter your username">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <span class="input-icon input-icon-lock">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4.5 7.5V5.25C4.5 3.17893 6.17893 1.5 8.25 1.5H9.75C11.8211 1.5 13.5 3.17893 13.5 5.25V7.5" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                    <rect x="4.5" y="7.5" width="9" height="7.5" rx="1.5" stroke="currentColor" stroke-width="1.25"/>
                                </svg>
                            </span>
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
