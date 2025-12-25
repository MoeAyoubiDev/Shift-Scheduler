<?php
declare(strict_types=1);
?>
<section class="dashboard-page">
    <div class="dashboard-page-header">
        <div>
            <h2>Reports</h2>
            <p class="muted">Operational performance analytics and attendance-driven insights.</p>
        </div>
    </div>

    <div class="dashboard-panel-grid">
        <div class="card reports-card">
            <div class="card-header">
                <div>
                    <h3>Performance Analytics</h3>
                    <p>Review on-time performance by employee. Sorted by lowest delay.</p>
                </div>
                <div class="card-header-meta">
                    <span class="card-meta-label">Employees</span>
                    <span class="card-meta-value"><?= e((string) count($performance)) ?></span>
                </div>
            </div>
            <div class="card-body">
                <?php if ($performance): ?>
                    <div class="table-wrapper">
                        <table class="dashboard-table">
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
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No analytics yet</div>
                        <div class="empty-state-text">Performance data will appear once attendance records are available.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
