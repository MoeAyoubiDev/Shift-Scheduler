<?php
declare(strict_types=1);
?>
<section class="dashboard-page">
    <div class="dashboard-page-header">
        <div>
            <h2>Employees</h2>
            <p class="muted">Directory visibility, weekly coverage, and open shift requests.</p>
        </div>
    </div>

    <div class="dashboard-panel-grid">
        <div class="card employees-card employees-card--directory">
            <div class="card-header">
                <div>
                    <h3>Employee Directory</h3>
                    <p>All active employees in this section.</p>
                </div>
                <div class="card-header-meta">
                    <span class="card-meta-label">Employees</span>
                    <span class="card-meta-value"><?= e((string) count($employees)) ?></span>
                </div>
            </div>
            <div class="card-body">
                <?php if ($employees): ?>
                    <div class="table-wrapper">
                        <table class="dashboard-table">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Experience</th>
                                <th>Employee Code</th>
                                <th>Email</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($employees as $employee): ?>
                                <tr>
                                    <td><?= e($employee['full_name']) ?></td>
                                    <td><?= e($employee['role_name']) ?></td>
                                    <td><?= e((string) $employee['seniority_level']) ?></td>
                                    <td><?= e($employee['employee_code']) ?></td>
                                    <td><?= e($employee['email'] ?? 'N/A') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No employees yet</div>
                        <div class="empty-state-text">Assign employees to this section to see them here.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card employees-card employees-card--requests">
            <div class="card-header">
                <div>
                    <h3>Shift Requests</h3>
                    <p>Requests submitted by employees for the current week.</p>
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
                                <th>Shift</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Importance</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr>
                                    <td><?= e($request['employee_name']) ?></td>
                                    <td><?= e($request['shift_date']) ?></td>
                                    <td><?= e($request['request_type']) ?></td>
                                    <td><?= e($request['status']) ?></td>
                                    <td><?= e($request['importance_level']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No requests yet</div>
                        <div class="empty-state-text">Employee requests will appear once submissions are made.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
