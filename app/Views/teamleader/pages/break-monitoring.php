<?php
declare(strict_types=1);
?>
<section class="dashboard-surface teamleader-dashboard-page">
    <div class="dashboard-inner">
        <div class="card">
            <div class="section-title">
                <h3>Break Monitoring</h3>
                <span>Track active breaks and delays</span>
            </div>
            <?php if (!empty($breaks)): ?>
                <table>
                    <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Shift</th>
                        <th>Break Start</th>
                        <th>Break End</th>
                        <th>Delay (min)</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($breaks as $break): ?>
                        <tr>
                            <td><?= e($break['employee_name']) ?></td>
                            <td><?= e($break['shift_name']) ?></td>
                            <td><?= e($break['break_start']) ?></td>
                            <td><?= e($break['break_end']) ?></td>
                            <td><?= e((string) $break['delay_minutes']) ?></td>
                            <td><?= e($break['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-title">No active breaks</div>
                    <p class="empty-state-text">Break activity will appear here once team members check out.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
