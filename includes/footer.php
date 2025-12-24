<?php
declare(strict_types=1);

$user = function_exists('current_user') ? current_user() : null;
$role = ($user && isset($user['role'])) ? $user['role'] : null;
$isDashboard = $role && in_array($role, ['Employee', 'Team Leader', 'Director', 'Supervisor', 'Senior'], true);
$firebaseConfig = function_exists('config') ? config('firebase') : [];
$vapidKey = $firebaseConfig['vapid_public_key'] ?? '';
?>
</main>
<?php if ($user): ?>
<footer class="site-footer">
    <div>
        <strong><?= function_exists('e') ? e(function_exists('app_config') ? app_config('name', 'Shift Scheduler') : 'Shift Scheduler') : htmlspecialchars(function_exists('app_config') ? app_config('name', 'Shift Scheduler') : 'Shift Scheduler', ENT_QUOTES, 'UTF-8') ?></strong>
        <span class="muted">Production-ready schedules, break monitoring, and analytics.</span>
    </div>
    <div class="footer-meta">
        <span class="status-indicator"></span>
        <span>Last sync <?= function_exists('e') ? e((new DateTimeImmutable())->format('M d, Y H:i')) : htmlspecialchars((new DateTimeImmutable())->format('M d, Y H:i'), ENT_QUOTES, 'UTF-8') ?></span>
    </div>
</footer>
<?php endif; ?>
<script src="/assets/js/app.js?v=<?= @filemtime(__DIR__ . '/../public/assets/js/app.js') ?: time() ?>"></script>
<script src="/assets/js/enhanced.js?v=<?= @filemtime(__DIR__ . '/../public/assets/js/enhanced.js') ?: time() ?>"></script>
<?php if ($isDashboard): ?>
<script src="/assets/js/dashboard.js?v=<?= @filemtime(__DIR__ . '/../public/assets/js/dashboard.js') ?: time() ?>"></script>
<script src="/assets/js/calendar.js?v=<?= @filemtime(__DIR__ . '/../public/assets/js/calendar.js') ?: time() ?>"></script>
<script>
    window.AppConfig = <?= json_encode([
        'csrfToken' => function_exists('csrf_token') ? csrf_token() : '',
        'fcm' => [
            'vapidKey' => $vapidKey,
        ],
    ], JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-messaging-compat.js"></script>
<script src="/assets/js/firebase-messaging.js?v=<?= @filemtime(__DIR__ . '/../public/assets/js/firebase-messaging.js') ?: time() ?>"></script>
<?php endif; ?>
</body>
</html>
