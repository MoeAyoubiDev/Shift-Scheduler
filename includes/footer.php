<?php
declare(strict_types=1);

$user = current_user();
$role = $user['role'] ?? null;
$isDashboard = in_array($role, ['Employee', 'Team Leader', 'Director', 'Supervisor', 'Senior'], true);
?>
</main>
<?php if ($user): ?>
<footer class="site-footer">
    <div>
        <strong><?= e(app_config('name', 'Shift Scheduler')) ?></strong>
        <span class="muted">Production-ready schedules, break monitoring, and analytics.</span>
    </div>
    <div class="footer-meta">
        <span class="status-indicator"></span>
        <span>Last sync <?= e((new DateTimeImmutable())->format('M d, Y H:i')) ?></span>
    </div>
</footer>
<?php endif; ?>
<script src="/assets/js/app.js?v=<?= filemtime(__DIR__ . '/../public/assets/js/app.js') ?>"></script>
<script src="/assets/js/enhanced.js?v=<?= filemtime(__DIR__ . '/../public/assets/js/enhanced.js') ?>"></script>
<?php if ($isDashboard): ?>
<script src="/assets/js/dashboard.js?v=<?= filemtime(__DIR__ . '/../public/assets/js/dashboard.js') ?>"></script>
<script src="/assets/js/calendar.js?v=<?= filemtime(__DIR__ . '/../public/assets/js/calendar.js') ?>"></script>
<?php endif; ?>
</body>
</html>
