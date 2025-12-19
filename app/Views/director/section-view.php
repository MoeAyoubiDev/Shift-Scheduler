<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../Core/Database.php';
require_once __DIR__ . '/../../Models/Schedule.php';
require_once __DIR__ . '/../../Models/ShiftRequest.php';
require_once __DIR__ . '/../../Models/Performance.php';

$db = Database::getInstance();
$weekStart = (new DateTime('monday this week'))->format('Y-m-d');

// Get section ID from global or parameter
$sectionId = $GLOBALS['sectionId'] ?? null;

if (!$sectionId) {
    header('Location: /dashboard.php');
    exit;
}

// Get section details
$stmt = $db->prepare("SELECT * FROM sections WHERE id = ?");
$stmt->execute([$sectionId]);
$section = $stmt->fetch();

if (!$section) {
    header('Location: /dashboard.php');
    exit;
}

// Get employees in this section
$employee = new Employee();
$employees = $employee->getAllBySection($sectionId);

// Get pending requests
$shiftRequest = new ShiftRequest();
$pendingRequests = $shiftRequest->getPendingBySection($sectionId, $weekStart);

// Get schedules
$schedule = new Schedule();
$schedules = $schedule->getByWeek($weekStart, $sectionId);

// Get performance data
$performance = new Performance();
$report = $performance->getReport($sectionId, null, null, null);
?>

<h1>Section View - <?= htmlspecialchars($section['section_name']) ?></h1>

<div class="dashboard-summary">
    <div class="card">
        <h3>Total Employees</h3>
        <p class="large-number"><?= count($employees) ?></p>
    </div>
    
    <div class="card">
        <h3>Pending Requests</h3>
        <p class="large-number"><?= count($pendingRequests) ?></p>
    </div>
    
    <div class="card">
        <h3>Schedule Assignments</h3>
        <p class="large-number"><?= count($schedules) ?></p>
    </div>
</div>

<div class="card">
    <h2>Employees</h2>
    <?php if (empty($employees)): ?>
        <p>No employees in this section.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Employee Code</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Is Senior</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td><?= htmlspecialchars($emp['employee_code']) ?></td>
                        <td><?= htmlspecialchars($emp['full_name']) ?></td>
                        <td><?= htmlspecialchars($emp['email'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($emp['role_name']) ?></td>
                        <td><?= $emp['is_senior'] ? 'Yes' : 'No' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Pending Shift Requests</h2>
    <?php if (empty($pendingRequests)): ?>
        <p>No pending shift requests.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Shift</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingRequests as $req): ?>
                    <tr>
                        <td><?= htmlspecialchars($req['full_name']) ?></td>
                        <td><?= date('M d, Y', strtotime($req['submit_date'])) ?></td>
                        <td><?= $req['is_day_off'] ? 'OFF' : htmlspecialchars($req['shift_name'] ?? 'N/A') ?></td>
                        <td><span class="badge badge-pending"><?= $req['status'] ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Performance Summary</h2>
    <?php if (empty($report)): ?>
        <p>No performance data available.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Days Worked</th>
                    <th>Total Delay (min)</th>
                    <th>Average Delay (min)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= $row['days_worked'] ?></td>
                        <td><?= number_format($row['total_delay_minutes'], 2) ?></td>
                        <td><?= number_format($row['average_delay_minutes'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<a href="/dashboard.php" class="btn">Back to Dashboard</a>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

