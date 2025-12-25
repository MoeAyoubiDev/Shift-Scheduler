<?php
declare(strict_types=1);
?>
<section class="dashboard-surface teamleader-dashboard-page">
    <div class="dashboard-inner">
        <div class="card">
            <div class="section-title">
                <h3>Shift Requests</h3>
                <span><?= e(count($requests)) ?> requests awaiting review</span>
            </div>
            <?php if (!empty($requests)): ?>
                <table>
                    <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Pattern</th>
                        <th>Importance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?= e($request['employee_name']) ?></td>
                            <td><?= e($request['submit_date']) ?></td>
                            <td><?= e($request['shift_name']) ?></td>
                            <td><?= e($request['pattern_name']) ?></td>
                            <td><?= e($request['importance_level']) ?></td>
                            <td><span class="status <?= strtolower($request['status']) ?>"><?= e($request['status']) ?></span></td>
                            <td>
                                <form method="post" action="/index.php" class="inline">
                                    <input type="hidden" name="action" value="update_request_status">
                                    <input type="hidden" name="request_id" value="<?= e((string) $request['id']) ?>">
                                    <input type="hidden" name="status" value="APPROVED">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <button type="submit" class="btn small">Approve</button>
                                </form>
                                <form method="post" action="/index.php" class="inline">
                                    <input type="hidden" name="action" value="update_request_status">
                                    <input type="hidden" name="request_id" value="<?= e((string) $request['id']) ?>">
                                    <input type="hidden" name="status" value="DECLINED">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <button type="submit" class="btn danger small">Decline</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-title">No requests yet</div>
                    <p class="empty-state-text">Shift requests will appear here when submitted.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
