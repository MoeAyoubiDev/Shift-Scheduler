<?php
declare(strict_types=1);
?>
<section class="card auth-card">
    <div class="hero">
        <h2>Shift Scheduler System</h2>
        <p>Sign in with your username and password to access your section dashboard.</p>
    </div>
    <form method="post" action="/index.php">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <div class="form-actions">
            <button type="submit" class="btn">Login</button>
        </div>
    </form>
</section>
