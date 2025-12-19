<?php
declare(strict_types=1);
?>
</main>
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
<script src="/assets/js/app.js"></script>
</body>
</html>
