<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../Core/CSRF.php';
?>

<div class="login-container">
    <div class="login-box">
        <h1>Shift Scheduler Login</h1>
        <form method="POST" action="/login.php">
            <?= CSRF::tokenField() ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
