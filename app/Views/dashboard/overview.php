<?php
declare(strict_types=1);
?>
<section class="card">
    <div class="hero-row">
        <div class="hero">
            <h2>Welcome, <?= e($user['name'] ?? '') ?></h2>
            <div class="meta-row">
                <span class="pill"><?= e(str_replace('_', ' ', ucfirst($user['role'] ?? ''))) ?></span>
                <span class="pill">Week of <?= e($weekStart ?? '') ?></span>
            </div>
        </div>
        <form method="post">
            <input type="hidden" name="action" value="logout">
            <button class="btn secondary" type="submit">Logout</button>
        </form>
    </div>
</section>
