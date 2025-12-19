<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../app/schedule.php';

$weekStart = $_GET['week'] ?? current_week_start();
$week = null;
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
            $pdo = db();
            $user = current_user();
            if (!$user || !is_employee($user)) {
                $message = 'Only employees can submit requests.';
                break;
            }

            $week = ensure_week($weekStart);
            if (!submission_window_open($weekStart)) {
                $message = 'Submissions are closed for this week.';
                break;
            }

            $requestedDay = $_POST['requested_day'] ?? $week['week_start_date'];
            $shiftDefinition = $_POST['shift_definition_id'] ?? null;
            $dayOff = isset($_POST['day_off']) ? 1 : 0;
            $schedulePattern = (int) ($_POST['schedule_pattern_id'] ?? 0);
            $reason = trim($_POST['reason'] ?? '');
            $importance = strtoupper($_POST['importance'] ?? 'NORMAL');

            if ($reason === '') {
                $message = 'Please provide a reason for your request.';
                break;
            }

            $stmt = $pdo->prepare(
                'INSERT INTO shift_requests
                (employee_id, week_id, SubmitDate, shift_definition_id, is_day_off, schedule_pattern_id, reason, importance_level, status, flagged_as_important, submitted_at)
                VALUES (:employee_id, :week_id, :submit_date, :shift_definition_id, :is_day_off, :schedule_pattern_id, :reason, :importance_level, :status, :flagged, CURRENT_TIMESTAMP)'
            );
            $stmt->execute([
                'employee_id' => $user['employee_id'],
                'week_id' => $week['id'],
                'submit_date' => $requestedDay,
                'shift_definition_id' => $dayOff ? null : ($shiftDefinition ?: null),
                'is_day_off' => $dayOff,
                'schedule_pattern_id' => $schedulePattern,
                'reason' => $reason,
                'importance_level' => $importance,
                'status' => 'PENDING',
                'flagged' => 0,
            ]);

            $message = 'Request submitted successfully.';
            break;
        case 'update_request_status':
            require_login();
            $pdo = db();
            $user = current_user();
            if (!$user || !is_admin($user)) {
                $message = 'Only admins can update requests.';
                break;
            }
            $requestId = (int) ($_POST['request_id'] ?? 0);
            $status = strtoupper($_POST['status'] ?? 'PENDING');
            $stmt = $pdo->prepare(
                'UPDATE shift_requests
                 SET status = :status, reviewed_by_admin_id = :reviewed_by, reviewed_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $stmt->execute([
                'status' => $status,
                'reviewed_by' => $user['employee_id'],
                'id' => $requestId,
            ]);
            break;
        case 'toggle_flag':
            require_login();
            $pdo = db();
            $user = current_user();
            if (!$user || !is_admin($user)) {
                $message = 'Only admins can flag requests.';
                break;
            }
            $requestId = (int) ($_POST['request_id'] ?? 0);
            $flagged = (int) ($_POST['flagged'] ?? 0);
            $stmt = $pdo->prepare('UPDATE shift_requests SET flagged_as_important = :flagged WHERE id = :id');
            $stmt->execute(['flagged' => $flagged, 'id' => $requestId]);
            break;
        case 'toggle_submission':
            require_login();
            $user = current_user();
            if (!$user || !is_admin($user)) {
                $message = 'Only admins can toggle submissions.';
                break;
            }
            $week = ensure_week($weekStart);
            $locked = isset($_POST['locked']);
            set_submission_lock($weekStart, $locked);
            break;
        case 'save_requirement':
            require_login();
            $user = current_user();
            if (!$user || !is_admin($user)) {
                $message = 'Only admins can update requirements.';
                break;
            }
            $week = ensure_week($weekStart);
            $date = $_POST['requirement_date'] ?? $weekStart;
            $shiftTypeId = (int) ($_POST['shift_type_id'] ?? 0);
            $requiredCount = (int) ($_POST['required_count'] ?? 0);
            if ($shiftTypeId > 0) {
                save_shift_requirement((int) $week['id'], $date, $shiftTypeId, $requiredCount);
                $message = 'Requirement saved.';
            }
            break;
        case 'create_schedule':
            require_login();
            $user = current_user();
            if (!$user || !is_admin($user)) {
                $message = 'Only admins can create schedules.';
                break;
            }
            $week = ensure_week($weekStart);
            create_schedule_if_missing((int) $week['id'], (int) $user['employee_id']);
            $message = 'Draft schedule created.';
            break;
        case 'mark_notification_read':
            require_login();
            $user = current_user();
            $notificationId = (int) ($_POST['notification_id'] ?? 0);
            if ($notificationId && $user) {
                mark_notification_read($notificationId, (int) $user['id']);
            }
            break;
    }
}

$user = current_user();
if ($user) {
    $week = $week ?? ensure_week($weekStart);
    $submissionLocked = is_submission_locked_for_week($weekStart);
    $shiftDefinitions = fetch_shift_definitions();
    $schedulePatterns = fetch_schedule_patterns();
    $shiftTypes = fetch_shift_types();
    $requirements = fetch_shift_requirements((int) $week['id']);
    $scheduleSummary = fetch_schedule_summary((int) $week['id']);
    $scheduleAssignments = fetch_schedule_assignments((int) $week['id']);
}

function render_header(string $title, ?string $message, ?array $user): void
{
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            :root {
                color-scheme: light;
                --bg: #f5f7fb;
                --card: rgba(255, 255, 255, 0.88);
                --primary: #4f46e5;
                --primary-dark: #312e81;
                --accent: #0ea5e9;
                --text: #0f172a;
                --muted: #6b7280;
                --border: rgba(148, 163, 184, 0.35);
                --shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
            }

            * { box-sizing: border-box; }

            body {
                margin: 0;
                font-family: 'Inter', sans-serif;
                color: var(--text);
                background: radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.15), transparent 45%),
                            radial-gradient(circle at 80% 10%, rgba(14, 165, 233, 0.18), transparent 40%),
                            var(--bg);
                min-height: 100vh;
            }

            header {
                position: sticky;
                top: 0;
                z-index: 10;
                backdrop-filter: blur(18px);
                background: rgba(15, 23, 42, 0.85);
                color: #fff;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            }

            .nav {
                max-width: 1200px;
                margin: 0 auto;
                padding: 16px 24px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
            }

            .nav .brand {
                font-weight: 700;
                font-size: 1.1rem;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .nav .brand span {
                display: inline-flex;
                width: 36px;
                height: 36px;
                border-radius: 12px;
                background: linear-gradient(120deg, #6366f1, #0ea5e9);
                align-items: center;
                justify-content: center;
                font-weight: 700;
            }

            .nav-links {
                display: flex;
                gap: 16px;
                flex-wrap: wrap;
                font-size: 0.9rem;
            }

            .nav-links a {
                color: #e2e8f0;
                text-decoration: none;
                opacity: 0.85;
            }

            .nav-links a:hover { opacity: 1; }

            main {
                max-width: 1200px;
                margin: 28px auto 80px;
                padding: 0 24px 40px;
            }

            .hero {
                display: grid;
                grid-template-columns: minmax(240px, 1fr) minmax(280px, 340px);
                gap: 24px;
                align-items: stretch;
            }

            .card {
                background: var(--card);
                border: 1px solid var(--border);
                border-radius: 20px;
                padding: 20px;
                box-shadow: var(--shadow);
                backdrop-filter: blur(10px);
            }

            .card h2 { margin-top: 0; }

            .stats {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 16px;
                margin-top: 16px;
            }

            .stat {
                padding: 16px;
                background: #f8fafc;
                border-radius: 16px;
                border: 1px solid rgba(148, 163, 184, 0.25);
            }

            .stat strong { display: block; font-size: 1.4rem; }

            .grid { display: grid; gap: 20px; }

            .grid.two { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }

            .grid.three { grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); }

            .pill {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 4px 10px;
                border-radius: 999px;
                font-size: 0.75rem;
                background: rgba(15, 23, 42, 0.08);
                color: #1e293b;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 16px;
                font-size: 0.9rem;
            }

            table th, table td {
                text-align: left;
                padding: 12px 10px;
                border-bottom: 1px solid rgba(148, 163, 184, 0.2);
                vertical-align: top;
            }

            table th { color: #475569; font-weight: 600; }

            label { display: block; margin: 12px 0 6px; font-weight: 600; }

            input[type=text], input[type=email], input[type=password], input[type=date], select, textarea, input[type=number] {
                width: 100%;
                padding: 10px 12px;
                border-radius: 12px;
                border: 1px solid rgba(148, 163, 184, 0.45);
                background: #fff;
                font-family: inherit;
                font-size: 0.95rem;
            }

            textarea { min-height: 96px; resize: vertical; }

            .btn {
                border: none;
                padding: 10px 16px;
                border-radius: 12px;
                cursor: pointer;
                font-weight: 600;
                font-family: inherit;
                background: linear-gradient(120deg, var(--primary), var(--accent));
                color: #fff;
            }

            .btn.secondary {
                background: #e2e8f0;
                color: #0f172a;
            }

            .btn.danger {
                background: linear-gradient(120deg, #ef4444, #f97316);
            }

            .btn.ghost {
                background: transparent;
                border: 1px solid rgba(148, 163, 184, 0.6);
                color: #0f172a;
            }

            .badge {
                padding: 4px 10px;
                border-radius: 999px;
                font-size: 0.75rem;
                font-weight: 600;
                display: inline-flex;
            }

            .badge-high { background: rgba(239, 68, 68, 0.15); color: #b91c1c; }
            .badge-normal { background: rgba(14, 165, 233, 0.15); color: #0369a1; }
            .badge-low { background: rgba(100, 116, 139, 0.15); color: #475569; }
            .badge-success { background: rgba(16, 185, 129, 0.15); color: #047857; }
            .badge-warning { background: rgba(234, 179, 8, 0.2); color: #a16207; }
            .badge-danger { background: rgba(239, 68, 68, 0.15); color: #b91c1c; }

            .notice {
                padding: 12px 14px;
                border-radius: 12px;
                background: rgba(254, 243, 199, 0.7);
                border: 1px solid rgba(245, 158, 11, 0.3);
                margin-bottom: 16px;
            }

            .muted { color: var(--muted); }
            .stack { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
            .spacer { height: 12px; }
            .inline { display: inline-flex; }

            @media (max-width: 920px) {
                .hero { grid-template-columns: 1fr; }
                .nav { flex-direction: column; align-items: flex-start; }
            }
        </style>
    </head>
    <body>
    <header>
        <div class="nav">
            <div class="brand"><span>SS</span> Shift Scheduler Pro</div>
            <nav class="nav-links">
                <a href="#overview">Overview</a>
                <a href="#requests">Requests</a>
                <a href="#schedule">Schedule</a>
                <a href="#team">Team</a>
                <a href="#settings">Settings</a>
            </nav>
            <?php if ($user): ?>
                <form method="post" class="inline">
                    <input type="hidden" name="action" value="logout">
                    <button class="btn secondary" type="submit">Logout</button>
                </form>
            <?php endif; ?>
        </div>
    </header>
    <main>
        <?php if ($message): ?>
            <div class="notice"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
    <?php
}

if (!$user):
    render_header('Shift Scheduler Login', $message, $user);
    ?>
    <section class="hero">
        <div class="card">
            <h2>Welcome back 👋</h2>
            <p class="muted">Sign in to access shift planning, request management, and real-time schedule insights aligned with your database schema.</p>
            <ul class="muted">
                <li>Roles, sections, and employees linked to the SQL schema.</li>
                <li>Shift requests and schedules aligned to weekly planning.</li>
                <li>Notifications and system settings ready for rollout.</li>
            </ul>
        </div>
        <div class="card">
            <h3>Secure login</h3>
            <form method="post">
                <input type="hidden" name="action" value="login">
                <label>Username</label>
                <input type="text" name="username" required>
                <label>Password</label>
                <input type="password" name="password" required>
                <div class="spacer"></div>
                <button class="btn" type="submit">Sign in</button>
            </form>
            <p class="muted" style="margin-top:12px;">Use a user seeded in <code>users</code> with a hashed password.</p>
        </div>
    </section>
    </main></body></html>
    <?php
    exit;
endif;

render_header('Shift Scheduler', $message, $user);

$pdo = db();
$requests = fetch_requests_with_details((int) $week['id']);
$notifications = fetch_notifications((int) $user['id']);
$employeeHistory = is_employee($user)
    ? fetch_request_history((int) $user['employee_id'], $_GET['from'] ?? null, $_GET['to'] ?? null, $_GET['on'] ?? null)
    : [];

$employeeCount = (int) $pdo->query('SELECT COUNT(*) AS total FROM employees')->fetch()['total'];
$pendingCount = (int) $pdo->query("SELECT COUNT(*) AS total FROM shift_requests WHERE status = 'PENDING'")->fetch()['total'];
$shiftCount = (int) $pdo->query('SELECT COUNT(*) AS total FROM shift_definitions')->fetch()['total'];
$sectionCount = (int) $pdo->query('SELECT COUNT(*) AS total FROM sections')->fetch()['total'];
?>
<section id="overview" class="hero">
    <div class="card">
        <div class="stack">
            <div>
                <h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2>
                <p class="muted">Role: <?= htmlspecialchars($user['role']) ?> · Section: <?= htmlspecialchars($user['section']) ?></p>
            </div>
            <span class="pill"><?= htmlspecialchars($week['week_start_date']) ?> → <?= htmlspecialchars($week['week_end_date']) ?></span>
        </div>
        <div class="stats">
            <div class="stat">
                <strong><?= $employeeCount ?></strong>
                <span class="muted">Employees</span>
            </div>
            <div class="stat">
                <strong><?= $pendingCount ?></strong>
                <span class="muted">Pending Requests</span>
            </div>
            <div class="stat">
                <strong><?= $shiftCount ?></strong>
                <span class="muted">Shift Definitions</span>
            </div>
            <div class="stat">
                <strong><?= $sectionCount ?></strong>
                <span class="muted">Sections</span>
            </div>
        </div>
    </div>
    <div class="card">
        <h3>This week status</h3>
        <p class="muted">Request submissions are currently <strong><?= $submissionLocked ? 'closed' : 'open' ?></strong>.</p>
        <?php if (is_admin($user)): ?>
            <form method="post" class="stack">
                <input type="hidden" name="action" value="toggle_submission">
                <?php if ($submissionLocked): ?>
                    <button class="btn secondary" type="submit">Open submissions</button>
                <?php else: ?>
                    <input type="hidden" name="locked" value="1">
                    <button class="btn danger" type="submit">Close submissions</button>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <p class="muted">Check back with your team leader if you cannot submit.</p>
        <?php endif; ?>
        <div class="spacer"></div>
        <div class="pill">Employee ID: <?= htmlspecialchars($user['employee_code']) ?></div>
    </div>
</section>

<section id="requests" class="card">
    <h2>Shift Requests</h2>
    <?php if (is_employee($user)): ?>
        <div class="grid two">
            <div>
                <h3>Submit your request</h3>
                <?php if (!$submissionLocked && submission_window_open($weekStart)): ?>
                    <form method="post">
                        <input type="hidden" name="action" value="submit_request">
                        <label>Requested date</label>
                        <input type="date" name="requested_day" value="<?= htmlspecialchars($week['week_start_date']) ?>" required>
                        <label>Shift definition</label>
                        <select name="shift_definition_id">
                            <?php if (!$shiftDefinitions): ?>
                                <option value="">No shifts configured</option>
                            <?php endif; ?>
                            <?php foreach ($shiftDefinitions as $definition): ?>
                                <option value="<?= (int) $definition['id'] ?>">
                                    <?= htmlspecialchars($definition['shiftName']) ?> · <?= htmlspecialchars($definition['category'] ?? 'N/A') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label>Schedule pattern</label>
                        <select name="schedule_pattern_id" required>
                            <?php foreach ($schedulePatterns as $pattern): ?>
                                <option value="<?= (int) $pattern['id'] ?>">
                                    <?= htmlspecialchars($pattern['names']) ?> · <?= (int) $pattern['work_days_per_week'] ?>/<?= (int) $pattern['off_days_per_week'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label>Importance</label>
                        <select name="importance" required>
                            <option value="LOW">Low</option>
                            <option value="NORMAL" selected>Normal</option>
                            <option value="HIGH">High</option>
                        </select>
                        <label>Request day off</label>
                        <input type="checkbox" name="day_off" value="1"> Yes, request this day off
                        <label>Reason</label>
                        <textarea name="reason" required placeholder="Provide reason or any note for your leader"></textarea>
                        <div class="spacer"></div>
                        <button class="btn" type="submit">Submit request</button>
                    </form>
                <?php else: ?>
                    <div class="notice">Request submission is currently closed.</div>
                <?php endif; ?>
            </div>
            <div>
                <h3>My request history</h3>
                <form method="get" class="grid">
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
                    <div class="spacer"></div>
                    <button class="btn secondary" type="submit">Filter</button>
                </form>
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Pattern</th>
                        <th>Importance</th>
                        <th>Status</th>
                    </tr>
                    <?php foreach ($employeeHistory as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['SubmitDate']) ?></td>
                            <td><?= htmlspecialchars($row['shiftName'] ?? 'Day off') ?></td>
                            <td><?= htmlspecialchars($row['pattern_name'] ?? 'n/a') ?></td>
                            <td><span class="<?= importance_badge($row['importance_level']) ?>"><?= htmlspecialchars($row['importance_level']) ?></span></td>
                            <td><span class="<?= status_badge($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="grid two">
            <div>
                <p class="muted">Review and approve employee shift requests for week starting <?= htmlspecialchars($week['week_start_date']) ?>.</p>
                <table>
                    <tr>
                        <th>Employee</th>
                        <th>Request</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($req['employee_name']) ?></strong><br>
                                <span class="muted"><?= htmlspecialchars($req['employee_code']) ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars($req['SubmitDate']) ?><br>
                                <?= htmlspecialchars($req['shiftName'] ?? 'Day off') ?>
                                <?php if ($req['flagged_as_important']): ?>
                                    <div class="pill">Flagged</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($req['reason']) ?><br>
                                <span class="<?= importance_badge($req['importance_level']) ?>"><?= htmlspecialchars($req['importance_level']) ?></span>
                            </td>
                            <td><span class="<?= status_badge($req['status']) ?>"><?= htmlspecialchars($req['status']) ?></span></td>
                            <td>
                                <form method="post" class="stack">
                                    <input type="hidden" name="action" value="update_request_status">
                                    <input type="hidden" name="request_id" value="<?= (int) $req['id'] ?>">
                                    <button class="btn" type="submit" name="status" value="APPROVED">Approve</button>
                                    <button class="btn danger" type="submit" name="status" value="DECLINED">Decline</button>
                                    <button class="btn ghost" type="submit" name="status" value="PENDING">Reset</button>
                                </form>
                                <form method="post" class="stack" style="margin-top:8px;">
                                    <input type="hidden" name="action" value="toggle_flag">
                                    <input type="hidden" name="request_id" value="<?= (int) $req['id'] ?>">
                                    <input type="hidden" name="flagged" value="<?= $req['flagged_as_important'] ? 0 : 1 ?>">
                                    <button class="btn secondary" type="submit"><?= $req['flagged_as_important'] ? 'Remove flag' : 'Flag important' ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <div>
                <h3>Requirement planner</h3>
                <form method="post">
                    <input type="hidden" name="action" value="save_requirement">
                    <label>Date</label>
                    <input type="date" name="requirement_date" value="<?= htmlspecialchars($week['week_start_date']) ?>" required>
                    <label>Shift type</label>
                    <select name="shift_type_id" required>
                        <?php foreach ($shiftTypes as $shiftType): ?>
                            <option value="<?= (int) $shiftType['id'] ?>">
                                <?= htmlspecialchars($shiftType['code']) ?> · <?= htmlspecialchars($shiftType['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label>Required count</label>
                    <input type="number" name="required_count" min="0" value="0">
                    <div class="spacer"></div>
                    <button class="btn" type="submit">Save requirement</button>
                </form>
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Required</th>
                    </tr>
                    <?php foreach ($requirements as $req): ?>
                        <tr>
                            <td><?= htmlspecialchars($req['date']) ?></td>
                            <td><?= htmlspecialchars($req['shift_code']) ?></td>
                            <td><?= htmlspecialchars($req['required_count']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>

<section id="schedule" class="card">
    <div class="stack" style="justify-content: space-between;">
        <div>
            <h2>Weekly Schedule</h2>
            <p class="muted">Work plan mapped to schedules, shifts, and assignments.</p>
        </div>
        <?php if (is_admin($user)): ?>
            <form method="post">
                <input type="hidden" name="action" value="create_schedule">
                <button class="btn" type="submit">Create draft schedule</button>
            </form>
        <?php endif; ?>
    </div>
    <?php if ($scheduleSummary): ?>
        <div class="grid three">
            <div class="stat">
                <strong><?= htmlspecialchars($scheduleSummary['status']) ?></strong>
                <span class="muted">Status</span>
            </div>
            <div class="stat">
                <strong><?= htmlspecialchars($scheduleSummary['generated_by'] ?? 'n/a') ?></strong>
                <span class="muted">Generated by</span>
            </div>
            <div class="stat">
                <strong><?= htmlspecialchars($scheduleSummary['generated_at']) ?></strong>
                <span class="muted">Generated at</span>
            </div>
        </div>
    <?php else: ?>
        <p class="muted">No schedule has been generated for this week.</p>
    <?php endif; ?>
    <table>
        <tr>
            <th>Date</th>
            <th>Shift</th>
            <th>Required</th>
            <th>Assigned</th>
            <th>Source</th>
        </tr>
        <?php foreach ($scheduleAssignments as $entry): ?>
            <tr>
                <td><?= htmlspecialchars($entry['date']) ?></td>
                <td><?= htmlspecialchars($entry['shiftName']) ?> (<?= htmlspecialchars($entry['category'] ?? '') ?>)</td>
                <td><?= htmlspecialchars($entry['required_count']) ?></td>
                <td><?= htmlspecialchars($entry['full_name'] ?? 'Unassigned') ?></td>
                <td><?= htmlspecialchars($entry['assignment_source'] ?? 'Pending') ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>

<section id="team" class="card">
    <h2>Team &amp; Directory</h2>
    <div class="grid three">
        <div>
            <h3>Employees</h3>
            <?php
            $employees = $pdo->query(
                'SELECT full_name, employee_code, email, is_senior, seniority_level
                 FROM employees
                 ORDER BY full_name'
            )->fetchAll();
            ?>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Level</th>
                    <th>Contact</th>
                </tr>
                <?php foreach ($employees as $employee): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($employee['full_name']) ?><br>
                            <span class="muted"><?= htmlspecialchars($employee['employee_code']) ?></span>
                        </td>
                        <td>
                            <?= $employee['is_senior'] ? 'Senior' : 'Staff' ?><br>
                            <span class="muted">Level <?= htmlspecialchars((string) $employee['seniority_level']) ?></span>
                        </td>
                        <td><?= htmlspecialchars($employee['email'] ?? 'n/a') ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div>
            <h3>Shift definitions</h3>
            <table>
                <tr>
                    <th>Shift</th>
                    <th>Time</th>
                    <th>Type</th>
                </tr>
                <?php foreach ($shiftDefinitions as $definition): ?>
                    <tr>
                        <td><?= htmlspecialchars($definition['shiftName']) ?></td>
                        <td><?= htmlspecialchars((string) $definition['start_time']) ?> - <?= htmlspecialchars((string) $definition['end_time']) ?></td>
                        <td><?= htmlspecialchars($definition['category'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div>
            <h3>Schedule patterns</h3>
            <table>
                <tr>
                    <th>Pattern</th>
                    <th>Work/Off</th>
                </tr>
                <?php foreach ($schedulePatterns as $pattern): ?>
                    <tr>
                        <td><?= htmlspecialchars($pattern['names']) ?></td>
                        <td><?= (int) $pattern['work_days_per_week'] ?>/<?= (int) $pattern['off_days_per_week'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</section>

<section id="settings" class="card">
    <h2>Notifications &amp; System Settings</h2>
    <div class="grid two">
        <div>
            <h3>Notifications</h3>
            <table>
                <tr>
                    <th>Type</th>
                    <th>Message</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($notifications as $note): ?>
                    <tr>
                        <td><?= htmlspecialchars($note['type']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($note['title']) ?></strong><br>
                            <?= htmlspecialchars($note['body'] ?? '') ?>
                        </td>
                        <td>
                            <?= $note['is_read'] ? 'Read' : 'Unread' ?>
                            <?php if (!$note['is_read']): ?>
                                <form method="post">
                                    <input type="hidden" name="action" value="mark_notification_read">
                                    <input type="hidden" name="notification_id" value="<?= (int) $note['id'] ?>">
                                    <button class="btn ghost" type="submit">Mark read</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div>
            <h3>System settings</h3>
            <?php $settings = $pdo->query('SELECT * FROM system_settings ORDER BY Systemkey')->fetchAll(); ?>
            <table>
                <tr>
                    <th>Key</th>
                    <th>Value</th>
                    <th>Description</th>
                </tr>
                <?php foreach ($settings as $setting): ?>
                    <tr>
                        <td><?= htmlspecialchars($setting['Systemkey']) ?></td>
                        <td><?= htmlspecialchars($setting['Svalue'] ?? 'n/a') ?></td>
                        <td><?= htmlspecialchars($setting['descriptions'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</section>

</main>
</body>
</html>
