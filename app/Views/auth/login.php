<?php
declare(strict_types=1);
?>
<section class="card auth-card">
    <div class="hero">
        <h2>Welcome back</h2>
        <p>Access schedules, requests, and analytics with your role-based dashboard.</p>
    </div>
    <form method="post">
        <input type="hidden" name="action" value="login">
        <label>Username</label>
        <input type="text" name="username" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <div class="form-actions">
            <button class="btn" type="submit">Sign in</button>
        </div>
    </form>
    <p class="muted helper-text">Default credentials are seeded in <code>database.sql</code> (password: <strong>password123</strong>).</p>
</section>
