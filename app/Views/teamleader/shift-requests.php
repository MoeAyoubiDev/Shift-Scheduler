<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../Core/CSRF.php';
?>

<h1>Shift Requests - Week of <?= date('M d, Y', strtotime($weekStart)) ?></h1>

<?php if (empty($requests)): ?>
    <p>No pending shift requests.</p>
<?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Date</th>
                <th>Shift</th>
                <th>Pattern</th>
                <th>Importance</th>
                <th>Reason</th>
                <th>Submitted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?= htmlspecialchars($request['full_name']) ?> (<?= htmlspecialchars($request['employee_code']) ?>)</td>
                    <td><?= date('M d, Y', strtotime($request['submit_date'])) ?></td>
                    <td><?= $request['is_day_off'] ? 'OFF' : htmlspecialchars($request['shift_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($request['pattern_name']) ?></td>
                    <td><span class="badge badge-<?= strtolower($request['importance_level']) ?>"><?= $request['importance_level'] ?></span></td>
                    <td><?= htmlspecialchars($request['reason'] ?? 'N/A') ?></td>
                    <td><?= date('M d, H:i', strtotime($request['submitted_at'])) ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <?= CSRF::tokenField() ?>
                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                            <input type="hidden" name="status" value="APPROVED">
                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <?= CSRF::tokenField() ?>
                            <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                            <input type="hidden" name="status" value="DECLINED">
                            <button type="submit" class="btn btn-sm btn-danger">Decline</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<a href="/dashboard.php" class="btn">Back to Dashboard</a>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

