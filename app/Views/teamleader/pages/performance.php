<?php
declare(strict_types=1);
?>
<section class="dashboard-surface teamleader-dashboard-page">
    <div class="dashboard-inner">
        <div class="card">
            <div class="section-title">
                <h3>Performance Analytics</h3>
                <span>Month-to-date delay summary</span>
            </div>
            <?php if (!empty($performance)): ?>
                <table>
                    <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Days Worked</th>
                        <th>Total Delay (min)</th>
                        <th>Average Delay</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($performance as $row): ?>
                        <tr>
                            <td><?= e($row['employee_name']) ?></td>
                            <td><?= e((string) $row['days_worked']) ?></td>
                            <td><?= e((string) $row['total_delay_minutes']) ?></td>
                            <td><?= e((string) $row['average_delay_minutes']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-title">No performance data</div>
                    <p class="empty-state-text">Performance summaries will appear after shifts are recorded.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
