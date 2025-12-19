<?php
declare(strict_types=1);
?>
<section class="card">
    <div class="section-title">
        <h2>My Schedule</h2>
        <span>Personal assignments for the week</span>
    </div>
    <table>
        <tr>
            <th>Day</th>
            <th>Shift</th>
            <th>Status</th>
            <th>Notes</th>
        </tr>
        <?php foreach (array_filter($schedule, fn($s) => (int) $s['user_id'] === (int) $user['id']) as $entry): ?>
            <tr>
                <td><?= e($entry['day']) ?></td>
                <td><?= e($entry['shift_type']) ?></td>
                <td><span class="status <?= e($entry['status']) ?>"><?= e(ucfirst(str_replace('_', ' ', $entry['status']))) ?></span></td>
                <td><?= e($entry['notes'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
