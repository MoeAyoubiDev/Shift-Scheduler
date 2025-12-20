<?php
declare(strict_types=1);
?>
<section class="card auth-card">
    <div class="hero">
        <h2>Shift Scheduler System</h2>
        <p>Sign in with your username and password to access your section dashboard.</p>
    </div>
    
    <div class="demo-credentials">
        <h3>Demo Credentials</h3>
        <p class="muted" style="margin-bottom: 1rem; font-size: 0.875rem;">Click "Use" to auto-fill login credentials</p>
        <div class="demo-accounts">
            <div class="demo-account">
                <div class="demo-account-info">
                    <strong>Director</strong>
                    <span class="muted" style="font-size: 0.8rem;">Access to both sections</span>
                </div>
                <div class="demo-account-credentials">
                    <code>director</code>
                    <code>password</code>
                    <button type="button" onclick="fillCredentials('director', 'password')" class="btn small">Use</button>
                </div>
            </div>
            <div class="demo-account">
                <div class="demo-account-info">
                    <strong>Team Leader</strong>
                    <span class="muted" style="font-size: 0.8rem;">Full CRUD permissions</span>
                </div>
                <div class="demo-account-credentials">
                    <code>teamleader</code>
                    <code>password</code>
                    <button type="button" onclick="fillCredentials('teamleader', 'password')" class="btn small">Use</button>
                </div>
            </div>
            <div class="demo-account">
                <div class="demo-account-info">
                    <strong>Employee</strong>
                    <span class="muted" style="font-size: 0.8rem;">Submit requests & view schedule</span>
                </div>
                <div class="demo-account-credentials">
                    <code>employee</code>
                    <code>password</code>
                    <button type="button" onclick="fillCredentials('employee', 'password')" class="btn small">Use</button>
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
