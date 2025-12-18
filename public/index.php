<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/schedule.php';

$pdo = db();
$weekStart = current_week_start();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'login':
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            if (login($username, $password)) {
                header('Location: /index.php');
                exit;
            }
            $message = 'Invalid username or password.';
            break;
        case 'logout':
            logout();
            header('Location: /index.php');
            exit;
        case 'submit_request':
            require_login();
            $user = current_user();
            if (!is_employee($user)) {
                $message = 'Only employees can submit requests.';
                break;
            }

            if (!submission_window_open()) {
                $message = 'Sorry, you cannot submit a late request. Please contact your Team Leader for more information.';
                break;
            }

            $requestedDay = $_POST['requested_day'] ?? 'Monday';
            $shiftType = $_POST['shift_type'] ?? 'AM';
            $dayOff = isset($_POST['day_off']) ? 1 : 0;
            $scheduleOption = $_POST['schedule_option'] ?? '5x2';
            $reason = trim($_POST['reason'] ?? '');
            $importance = $_POST['importance'] ?? 'low';
            $weekStart = $_POST['week_start'] ?: current_week_start();

            if ($reason === '') {
                $message = 'Please provide a reason for your request.';
                break;
            }

            $previousWeek = (new DateTimeImmutable($weekStart))->modify('-7 days')->format('Y-m-d');
            $prevStmt = $pdo->prepare('SELECT requested_day, shift_type, day_off FROM requests WHERE user_id = :uid AND week_start = :week_start LIMIT 1');
            $prevStmt->execute(['uid' => $user['id'], 'week_start' => $previousWeek]);
            $prevRow = $prevStmt->fetch();
            $previousRequestSummary = $prevRow ? sprintf('%s %s (%s)', $prevRow['requested_day'], $prevRow['shift_type'], $prevRow['day_off'] ? 'Off' : 'On') : null;

            $stmt = $pdo->prepare(
                'INSERT INTO requests (user_id, requested_day, shift_type, day_off, schedule_option, reason, importance, status, submission_date, week_start, previous_week_request)
                 VALUES (:user_id, :requested_day, :shift_type, :day_off, :schedule_option, :reason, :importance, "pending", NOW(), :week_start, :previous_week_request)'
            );
            $stmt->execute([
                'user_id' => $user['id'],
                'requested_day' => $requestedDay,
                'shift_type' => $shiftType,
                'day_off' => $dayOff,
                'schedule_option' => $scheduleOption,
                'reason' => $reason,
                'importance' => $importance,
                'week_start' => $weekStart,
                'previous_week_request' => $previousRequestSummary,
            ]);

            $message = 'Request submitted successfully.';
            break;
        case 'update_request_status':
            require_login();
            $user = current_user();
            if (!is_primary_admin($user)) {
                $message = 'Only the Primary Admin can update requests.';
                break;
            }
            $requestId = (int) ($_POST['request_id'] ?? 0);
            $status = $_POST['status'] ?? 'pending';
            $stmt = $pdo->prepare('UPDATE requests SET status = :status WHERE id = :id');
            $stmt->execute(['status' => $status, 'id' => $requestId]);
            break;
        case 'toggle_flag':
            require_login();
            $user = current_user();
            if (!is_primary_admin($user)) {
                $message = 'Only the Primary Admin can flag requests.';
                break;
            }
            $requestId = (int) ($_POST['request_id'] ?? 0);
            $flagged = (int) ($_POST['flagged'] ?? 0);
            $stmt = $pdo->prepare('UPDATE requests SET flagged = :flagged WHERE id = :id');
            $stmt->execute(['flagged' => $flagged, 'id' => $requestId]);
            break;
        case 'toggle_submission':
            require_login();
            $user = current_user();
            if (!is_primary_admin($user)) {
                $message = 'Only the Primary Admin can toggle submissions.';
                break;
            }
            $locked = isset($_POST['locked']) ? true : false;
            set_submission_lock($weekStart, $locked);
            break;
        case 'delete_employee':
            require_login();
            $user = current_user();
            if (!is_primary_admin($user)) {
                $message = 'Only the Primary Admin can delete employees.';
                break;
            }
            $employeeId = (int) ($_POST['employee_id'] ?? 0);
            $pdo->prepare('DELETE FROM users WHERE id = :id AND role = "employee"')->execute(['id' => $employeeId]);
            break;
        case 'save_requirements':
            require_login();
            $user = current_user();
            if (!is_primary_admin($user)) {
                $message = 'Only the Primary Admin can edit requirements.';
                break;
            }
            $am = (int) ($_POST['am_required'] ?? 0);
            $pm = (int) ($_POST['pm_required'] ?? 0);
            $mid = (int) ($_POST['mid_required'] ?? 0);
            $senior = trim($_POST['senior_staff'] ?? '');
            save_shift_requirements($weekStart, $am, $pm, $mid, $senior);
            $message = 'Requirements saved.';
            break;
        case 'generate_schedule':
            require_login();
            $user = current_user();
            if (!is_primary_admin($user)) {
                $message = 'Only the Primary Admin can generate the schedule.';
                break;
            }
            generate_schedule($weekStart);
            $message = 'Schedule generated for the week.';
            break;
        case 'update_schedule_entry':
            require_login();
            $user = current_user();
            if (!is_primary_admin($user)) {
                $message = 'Only the Primary Admin can edit the schedule.';
                break;
            }
            $entryId = (int) ($_POST['entry_id'] ?? 0);
            $day = $_POST['day'] ?? 'Monday';
            $shift = $_POST['shift_type'] ?? 'AM';
            $notes = trim($_POST['notes'] ?? '');
            update_schedule_entry($entryId, $day, $shift, $notes);
            $message = 'Schedule entry updated.';
            break;
    }
}

$user = current_user();
$submissionLocked = is_submission_locked_for_week($weekStart);
$requirements = fetch_shift_requirements($weekStart);
$schedule = fetch_schedule($weekStart);

if ($user && isset($_GET['download']) && $_GET['download'] === 'schedule') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="schedule-' . $weekStart . '.csv"');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['Employee', 'Day', 'Shift', 'Status', 'Notes']);
    foreach ($schedule as $entry) {
        fputcsv($out, [
            $entry['employee_name'],
            $entry['day'],
            $entry['shift_type'],
            $entry['status'],
            $entry['notes'],
        ]);
    }
    fclose($out);
    exit;
}

function render_header(string $title, ?string $message): void
{
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?></title>
        <style>
            body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f6f8fa; }
            header { background: #1e293b; color: #fff; padding: 12px 20px; }
            main { padding: 20px; max-width: 1200px; margin: 0 auto; }
            section { background: #fff; padding: 16px; margin-bottom: 16px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
            h2 { margin-top: 0; }
            table { width: 100%; border-collapse: collapse; margin-top: 12px; }
            table th, table td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
            table th { background: #f3f4f6; }
            label { display: block; margin: 8px 0 4px; font-weight: 600; }
            input[type=text], input[type=email], select, textarea, input[type=number] { width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; }
            textarea { min-height: 80px; }
            .btn { background: #2563eb; color: #fff; padding: 8px 12px; border: none; border-radius: 6px; cursor: pointer; }
            .btn.secondary { background: #6b7280; }
            .btn.danger { background: #b91c1c; }
            .pill { display: inline-block; padding: 2px 8px; border-radius: 999px; background: #e0f2fe; color: #075985; font-size: 12px; }
            .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; }
            .muted { color: #6b7280; }
            .flag { color: #c2410c; font-weight: 700; }
            .status { padding: 2px 8px; border-radius: 999px; }
            .status.pending { background: #fef3c7; color: #92400e; }
            .status.accepted { background: #dcfce7; color: #166534; }
            .status.declined { background: #fee2e2; color: #991b1b; }
            .status.unmatched, .status.no_request { background: #e0f2fe; color: #075985; }
            .notice { padding: 10px 12px; border-radius: 6px; background: #fff7ed; border: 1px solid #fed7aa; margin-bottom: 12px; }
            form.inline { display: inline; }
        </style>
    </head>
    <body>
    <header>
        <strong>Shift Scheduler</strong>
    </header>
    <main>
        <?php if ($message): ?>
            <div class="notice"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
    <?php
}

if (!$user):
    render_header('Shift Scheduler Login', $message);
    ?>
    <section>
        <h2>Login</h2>
        <form method="post">
            <input type="hidden" name="action" value="login">
            <label>Username</label>
            <input type="text" name="username" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <div style="margin-top:12px;">
                <button class="btn" type="submit">Sign in</button>
            </div>
        </form>
        <p class="muted" style="margin-top:12px;">Default credentials are seeded in <code>database.sql</code> (password: <strong>password123</strong>).</p>
    </section>
    </main></body></html>
    <?php
    exit;
endif;

render_header('Shift Scheduler', $message);
?>
<section>
    <div style="display:flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>
            <p class="muted">Role: <?= htmlspecialchars(str_replace('_', ' ', ucfirst($user['role']))) ?> &middot; Week starting <?= htmlspecialchars($weekStart) ?></p>
        </div>
        <form method="post">
            <input type="hidden" name="action" value="logout">
            <button class="btn secondary" type="submit">Logout</button>
        </form>
    </div>
</section>

<?php if (is_employee($user)): ?>
    <section>
        <h2>Submit Weekly Request</h2>
        <?php if (!$submissionLocked && submission_window_open()): ?>
            <form method="post">
                <input type="hidden" name="action" value="submit_request">
                <input type="hidden" name="week_start" value="<?= htmlspecialchars($weekStart) ?>">
                <div class="grid">
                    <div>
                        <label>Day</label>
                        <select name="requested_day" required>
                            <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day): ?>
                                <option value="<?= $day ?>"><?= $day ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Shift Type</label>
                        <select name="shift_type" required>
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                            <option value="MID">MID</option>
                        </select>
                    </div>
                    <div>
                        <label>Schedule Option</label>
                        <select name="schedule_option" required>
                            <option value="5x2">Work 5 days / 2 off (9 hours)</option>
                            <option value="6x1">Work 6 days / 1 off (7.5 hours)</option>
                        </select>
                    </div>
                    <div>
                        <label>Day Off</label>
                        <input type="checkbox" name="day_off" value="1"> Request this day off
                    </div>
                    <div>
                        <label>Importance</label>
                        <select name="importance" required>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                </div>
                <label style="margin-top:12px;">Reason</label>
                <textarea name="reason" required placeholder="Provide a reason for your request"></textarea>
                <div style="margin-top:12px;">
                    <button class="btn" type="submit">Submit request</button>
                </div>
            </form>
        <?php elseif ($submissionLocked): ?>
            <div class="notice">Please contact your Team Leader for more information.</div>
        <?php else: ?>
            <div class="notice">Sorry, you cannot submit a late request. Please contact your Team Leader for more information.</div>
        <?php endif; ?>
    </section>

    <section>
        <h2>My Request History</h2>
        <form method="get">
            <div class="grid">
                <div>
                    <label>From</label>
                    <input type="date" name="from" value="<?= htmlspecialchars($_GET['from'] ?? '') ?>">
                </div>
                <div>
                    <label>To</label>
                    <input type="date" name="to" value="<?= htmlspecialchars($_GET['to'] ?? '') ?>">
                </div>
                <div>
                    <label>Specific date</label>
                    <input type="date" name="on" value="<?= htmlspecialchars($_GET['on'] ?? '') ?>">
                </div>
            </div>
            <div style="margin-top:12px;">
                <button class="btn secondary" type="submit">Filter</button>
            </div>
        </form>
        <?php
        $history = fetch_request_history(
            $user['id'],
            $_GET['from'] ?? null,
            $_GET['to'] ?? null,
            $_GET['on'] ?? null
        );
        ?>
        <table>
            <tr>
                <th>Submitted</th>
                <th>Week</th>
                <th>Day</th>
                <th>Shift</th>
                <th>Option</th>
                <th>Reason</th>
                <th>Importance</th>
                <th>Status</th>
            </tr>
            <?php foreach ($history as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['submission_date']) ?></td>
                    <td><?= htmlspecialchars($row['week_start']) ?></td>
                    <td><?= htmlspecialchars($row['requested_day']) ?> <?= $row['day_off'] ? '(Day off)' : '' ?></td>
                    <td><?= htmlspecialchars($row['shift_type']) ?></td>
                    <td><?= htmlspecialchars(schedule_option_label($row['schedule_option'])) ?></td>
                    <td><?= htmlspecialchars($row['reason']) ?></td>
                    <td <?= importance_badge($row['importance']) ?>><?= htmlspecialchars(ucfirst($row['importance'])) ?></td>
                    <td><span class="status <?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars(ucfirst($row['status'])) ?></span></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>

    <section>
        <h2>My Schedule</h2>
        <table>
            <tr>
                <th>Day</th>
                <th>Shift</th>
                <th>Status</th>
                <th>Notes</th>
            </tr>
            <?php foreach (array_filter($schedule, fn($s) => (int) $s['user_id'] === (int) $user['id']) as $entry): ?>
                <tr>
                    <td><?= htmlspecialchars($entry['day']) ?></td>
                    <td><?= htmlspecialchars($entry['shift_type']) ?></td>
                    <td><span class="status <?= htmlspecialchars($entry['status']) ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $entry['status']))) ?></span></td>
                    <td><?= htmlspecialchars($entry['notes'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>
<?php endif; ?>

<?php if (!is_employee($user)): ?>
    <?php if (is_primary_admin($user)): ?>
        <section>
            <h2>Submission Controls</h2>
            <form method="post" class="inline">
                <input type="hidden" name="action" value="toggle_submission">
                <input type="hidden" name="locked" value="1">
                <button class="btn danger" type="submit">Stop submissions for this week</button>
            </form>
            <form method="post" class="inline" style="margin-left:8px;">
                <input type="hidden" name="action" value="toggle_submission">
                <button class="btn secondary" type="submit">Allow submissions</button>
            </form>
            <p class="muted" style="margin-top:8px;">Current status: <?= $submissionLocked ? 'Disabled for this week' : 'Open (resets Monday)' ?></p>
        </section>
    <?php endif; ?>

    <section>
        <h2>Requests (Week <?= htmlspecialchars($weekStart) ?>)</h2>
        <?php $requests = fetch_requests_with_details($weekStart); ?>
        <table>
            <tr>
                <th>Employee</th>
                <th>Submitted</th>
                <th>Requested</th>
                <th>Schedule option</th>
                <th>Reason / Importance</th>
                <th>Status</th>
                <th>Previous week</th>
                <?php if (is_primary_admin($user)): ?><th>Actions</th><?php endif; ?>
            </tr>
            <?php foreach ($requests as $req): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($req['employee_name']) ?></strong><br>
                        <span class="muted"><?= htmlspecialchars($req['employee_identifier']) ?></span><br>
                        <span class="muted"><?= htmlspecialchars($req['email']) ?></span>
                        <?php if ($req['flagged']): ?><div class="flag">Important</div><?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($req['submission_date']) ?></td>
                    <td><?= htmlspecialchars($req['requested_day']) ?> <?= $req['day_off'] ? '(Day off)' : '' ?> &middot; <?= htmlspecialchars($req['shift_type']) ?></td>
                    <td><?= htmlspecialchars(schedule_option_label($req['schedule_option'])) ?></td>
                    <td>
                        <?= htmlspecialchars($req['reason']) ?><br>
                        <span <?= importance_badge($req['importance']) ?>><?= htmlspecialchars(ucfirst($req['importance'])) ?></span>
                    </td>
                    <td><span class="status <?= htmlspecialchars($req['status']) ?>"><?= htmlspecialchars(ucfirst($req['status'])) ?></span></td>
                    <td><?= htmlspecialchars($req['previous_week_request'] ?? 'n/a') ?></td>
                    <?php if (is_primary_admin($user)): ?>
                        <td>
                            <form method="post" class="inline">
                                <input type="hidden" name="action" value="update_request_status">
                                <input type="hidden" name="request_id" value="<?= (int) $req['id'] ?>">
                                <input type="hidden" name="status" value="accepted">
                                <button class="btn" type="submit">Accept</button>
                            </form>
                            <form method="post" class="inline">
                                <input type="hidden" name="action" value="update_request_status">
                                <input type="hidden" name="request_id" value="<?= (int) $req['id'] ?>">
                                <input type="hidden" name="status" value="declined">
                                <button class="btn danger" type="submit">Decline</button>
                            </form>
                            <form method="post" class="inline">
                                <input type="hidden" name="action" value="update_request_status">
                                <input type="hidden" name="request_id" value="<?= (int) $req['id'] ?>">
                                <input type="hidden" name="status" value="pending">
                                <button class="btn secondary" type="submit">Pending</button>
                            </form>
                            <form method="post" class="inline" style="margin-top:6px;">
                                <input type="hidden" name="action" value="toggle_flag">
                                <input type="hidden" name="request_id" value="<?= (int) $req['id'] ?>">
                                <input type="hidden" name="flagged" value="<?= $req['flagged'] ? 0 : 1 ?>">
                                <button class="btn secondary" type="submit"><?= $req['flagged'] ? 'Unflag' : 'Flag important' ?></button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>

    <?php if (is_primary_admin($user)): ?>
        <section>
            <h2>Shift Requirements &amp; Senior Staff</h2>
            <form method="post" class="grid">
                <input type="hidden" name="action" value="save_requirements">
                <div>
                    <label>AM required</label>
                    <input type="number" name="am_required" value="<?= (int) $requirements['am_required'] ?>" min="0">
                </div>
                <div>
                    <label>PM required</label>
                    <input type="number" name="pm_required" value="<?= (int) $requirements['pm_required'] ?>" min="0">
                </div>
                <div>
                    <label>MID required</label>
                    <input type="number" name="mid_required" value="<?= (int) $requirements['mid_required'] ?>" min="0">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label>Senior staff (notes)</label>
                    <textarea name="senior_staff" placeholder="List senior staff or team leaders"><?= htmlspecialchars($requirements['senior_staff'] ?? '') ?></textarea>
                </div>
                <div style="grid-column: 1 / -1;">
                    <button class="btn" type="submit">Save requirements</button>
                </div>
            </form>
            <form method="post" style="margin-top:12px;">
                <input type="hidden" name="action" value="generate_schedule">
                <button class="btn" type="submit">Generate Schedule</button>
            </form>
        </section>
    <?php endif; ?>

    <section>
        <h2>Schedule (Week <?= htmlspecialchars($weekStart) ?>)</h2>
        <?php if (!is_employee($user)): ?>
            <p><a class="btn secondary" href="?download=schedule">Download Excel/CSV</a></p>
        <?php endif; ?>
        <table>
            <tr>
                <th>Employee</th>
                <th>Day</th>
                <th>Shift</th>
                <th>Status</th>
                <th>Notes</th>
                <?php if (is_primary_admin($user)): ?><th>Edit</th><?php endif; ?>
            </tr>
            <?php foreach ($schedule as $entry): ?>
                <tr>
                    <td><?= htmlspecialchars($entry['employee_name']) ?></td>
                    <td><?= htmlspecialchars($entry['day']) ?></td>
                    <td><?= htmlspecialchars($entry['shift_type']) ?></td>
                    <td><span class="status <?= htmlspecialchars($entry['status']) ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $entry['status']))) ?></span></td>
                    <td><?= htmlspecialchars($entry['notes'] ?? '') ?></td>
                    <?php if (is_primary_admin($user)): ?>
                        <td>
                            <form method="post" class="grid" style="grid-template-columns: repeat(3, 1fr); gap:6px; align-items: center;">
                                <input type="hidden" name="action" value="update_schedule_entry">
                                <input type="hidden" name="entry_id" value="<?= (int) $entry['id'] ?>">
                                <select name="day">
                                    <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday','N/A'] as $day): ?>
                                        <option value="<?= $day ?>" <?= $day === $entry['day'] ? 'selected' : '' ?>><?= $day ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select name="shift_type">
                                    <?php foreach (['AM','PM','MID','OFF','UNASSIGNED'] as $shift): ?>
                                        <option value="<?= $shift ?>" <?= $shift === $entry['shift_type'] ? 'selected' : '' ?>><?= $shift ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="notes" value="<?= htmlspecialchars($entry['notes'] ?? '') ?>" placeholder="Notes">
                                <button class="btn secondary" type="submit" style="grid-column: 1 / -1;">Save</button>
                            </form>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>

    <?php if (is_primary_admin($user)): ?>
        <section>
            <h2>Manage Employees</h2>
            <?php
            $emps = $pdo->query('SELECT id, name, employee_identifier, email FROM users WHERE role = "employee" ORDER BY name')->fetchAll();
            ?>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Employee ID</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($emps as $emp): ?>
                    <tr>
                        <td><?= htmlspecialchars($emp['name']) ?></td>
                        <td><?= htmlspecialchars($emp['employee_identifier']) ?></td>
                        <td><?= htmlspecialchars($emp['email']) ?></td>
                        <td>
                            <form method="post" class="inline">
                                <input type="hidden" name="action" value="delete_employee">
                                <input type="hidden" name="employee_id" value="<?= (int) $emp['id'] ?>">
                                <button class="btn danger" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <p class="muted" style="margin-top:8px;">Add new employees by inserting them into the <code>users</code> table with the <strong>employee</strong> role.</p>
        </section>
    <?php endif; ?>
<?php endif; ?>

</main>
</body>
</html>
