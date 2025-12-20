<?php
declare(strict_types=1);
?>
<section class="card auth-card">
    <div class="hero">
        <h2>Shift Scheduler System</h2>
        <p>Sign in with your username and password to access your section dashboard.</p>
    </div>
    
    <div class="demo-credentials">
        <h3 style="margin-top: 0;">🔐 Demo Login Credentials</h3>
        <p class="muted" style="margin-bottom: 1.25rem; font-size: 0.875rem;">Click "Use" button to auto-fill the login form below</p>
        <div class="demo-accounts">
            <div class="demo-account">
                <div class="demo-account-header">
                    <strong class="demo-role-name">Director</strong>
                    <span class="demo-role-desc">Access to both sections (Read-only)</span>
                </div>
                <div class="demo-account-credentials">
                    <div class="credential-item">
                        <span class="credential-label">Username:</span>
                        <span class="credential-value">director</span>
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">Password:</span>
                        <span class="credential-value">password</span>
                    </div>
                    <button type="button" onclick="fillCredentials('director', 'password')" class="btn small">Use This</button>
                </div>
            </div>
            <div class="demo-account">
                <div class="demo-account-header">
                    <strong class="demo-role-name">Team Leader</strong>
                    <span class="demo-role-desc">Full CRUD permissions</span>
                </div>
                <div class="demo-account-credentials">
                    <div class="credential-item">
                        <span class="credential-label">Username:</span>
                        <span class="credential-value">teamleader</span>
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">Password:</span>
                        <span class="credential-value">password</span>
                    </div>
                    <button type="button" onclick="fillCredentials('teamleader', 'password')" class="btn small">Use This</button>
                </div>
            </div>
            <div class="demo-account">
                <div class="demo-account-header">
                    <strong class="demo-role-name">Employee</strong>
                    <span class="demo-role-desc">Submit requests & view schedule</span>
                </div>
                <div class="demo-account-credentials">
                    <div class="credential-item">
                        <span class="credential-label">Username:</span>
                        <span class="credential-value">employee</span>
                    </div>
                    <div class="credential-item">
                        <span class="credential-label">Password:</span>
                        <span class="credential-value">password</span>
                    </div>
                    <button type="button" onclick="fillCredentials('employee', 'password')" class="btn small">Use This</button>
                </div>
            </div>
        </div>
    </div>

    <form method="post" action="/index.php" id="login-form">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required autocomplete="username">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
        <div class="form-actions">
            <button type="submit" class="btn">Login</button>
        </div>
    </form>
</section>

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
