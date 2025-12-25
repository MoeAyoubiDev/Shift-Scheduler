<?php
declare(strict_types=1);

$user = function_exists('current_user') ? current_user() : null;
$role = ($user && isset($user['role'])) ? $user['role'] : null;
$isDashboard = $role && in_array($role, ['Employee', 'Team Leader', 'Supervisor'], true);
?>
</main>
<?php if ($user): ?>
<footer class="site-footer">
    <div>
        <strong><?= function_exists('e') ? e(function_exists('app_config') ? app_config('name', 'Shift Scheduler') : 'Shift Scheduler') : htmlspecialchars(function_exists('app_config') ? app_config('name', 'Shift Scheduler') : 'Shift Scheduler', ENT_QUOTES, 'UTF-8') ?></strong>
        <span class="muted">Secure scheduling, compliance-ready operations, and real-time workforce insight.</span>
    </div>
</footer>
<?php endif; ?>
<script src="/assets/js/app.js?v=<?= @filemtime(__DIR__ . '/../public/assets/js/app.js') ?: time() ?>"></script>
<script src="/assets/js/enhanced.js?v=<?= @filemtime(__DIR__ . '/../public/assets/js/enhanced.js') ?: time() ?>"></script>
<?php if ($isDashboard): ?>
<script src="/assets/js/dashboard.js?v=<?= @filemtime(__DIR__ . '/../public/assets/js/dashboard.js') ?: time() ?>"></script>
<script src="/assets/js/calendar.js?v=<?= @filemtime(__DIR__ . '/../public/assets/js/calendar.js') ?: time() ?>"></script>
<?php endif; ?>
</body>
</html>
