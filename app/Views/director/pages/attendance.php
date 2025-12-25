<?php
declare(strict_types=1);
?>
<section class="dashboard-page">
    <div class="dashboard-page-header">
        <div>
            <h2>Attendance</h2>
            <p class="muted">Weekly coverage, attendance pacing, and request impact tracking.</p>
        </div>
    </div>

    <div class="dashboard-panel-grid">
        <div class="card attendance-card">
            <div class="card-header">
                <div>
                    <h3>Weekly Coverage</h3>
                    <p>Scheduled assignments for the selected week.</p>
                </div>
                <div class="card-header-meta">
                    <span class="card-meta-label">Shifts</span>
                    <span class="card-meta-value"><?= e((string) count($schedule)) ?></span>
                </div>
            </div>
            <div class="card-body">
                <?php if ($schedule): ?>
                    <div class="table-wrapper">
                        <table class="dashboard-table">
                            <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Shift</th>
                                <th>Start</th>
                                <th>End</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($schedule as $entry): ?>
                                <tr>
                                    <td><?= e($entry['employee_name']) ?></td>
                                    <td><?= e($entry['shift_date']) ?></td>
                                    <td><?= e($entry['shift_name']) ?></td>
                                    <td><?= e($entry['start_time']) ?></td>
                                    <td><?= e($entry['end_time']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No schedule generated</div>
                        <div class="empty-state-text">Generate a schedule to monitor attendance coverage.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card attendance-card">
            <div class="card-header">
                <div>
                    <h3>Shift Requests Impact</h3>
                    <p>Requests that may affect attendance or coverage.</p>
                </div>
                <div class="card-header-meta">
                    <span class="card-meta-label">Requests</span>
                    <span class="card-meta-value"><?= e((string) count($requests)) ?></span>
                </div>
            </div>
            <div class="card-body">
                <?php if ($requests): ?>
                    <div class="table-wrapper">
                        <table class="dashboard-table">
                            <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><?= e($request['employee_name']) ?></td>
                                    <td><?= e($request['shift_date']) ?></td>
                                    <td><?= e($request['request_type']) ?></td>
                                    <td><?= e($request['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No pending requests</div>
                        <div class="empty-state-text">Attendance impact will appear when requests are submitted.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
