<?php
declare(strict_types=1);
?>
<section class="card">
    <div class="hero">
        <h2>Choose a Section</h2>
        <p>Select which section you want to review as Director.</p>
    </div>
    <div class="grid">
        <?php foreach ($user['sections'] as $section): ?>
            <form method="post" action="/index.php" class="card mini-card">
                <input type="hidden" name="action" value="select_section">
                <input type="hidden" name="section_id" value="<?= e((string) $section['section_id']) ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <h3><?= e($section['section_name']) ?></h3>
                <p>View employees, schedules, performance, and statistics.</p>
                <button type="submit" class="btn">Open Dashboard</button>
            </form>
        <?php endforeach; ?>
    </div>
</section>
