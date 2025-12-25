<?php
declare(strict_types=1);
?>
<div class="dashboard-container">
    <!-- Modern Navigation Cards -->
    <div class="dashboard-nav-cards">
        <button type="button" class="nav-card active" data-section="overview">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Overview</div>
                <div class="nav-card-subtitle">Today's control</div>
            </div>
        </button>
        
        <button type="button" class="nav-card" data-section="shift-coverage">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Shift Coverage</div>
                <div class="nav-card-subtitle">Active shifts</div>
            </div>
        </button>
        
        <button type="button" class="nav-card" data-section="break-monitoring">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Break Monitoring</div>
                <div class="nav-card-subtitle">Live break activity</div>
            </div>
        </button>
        
        <button type="button" class="nav-card" data-section="weekly-schedule">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Weekly Schedule</div>
                <div class="nav-card-subtitle">Week summary</div>
            </div>
        </button>
    </div>

    <!-- Main Content Area -->
    <main class="dashboard-content">
        <!-- Overview Section -->
        <section class="dashboard-section active" data-section="overview">
            <div class="card">
    <div class="hero-row">
    <div>
        <h2>Today's Shift Control</h2>
    </div>
        <div class="meta-row">
            <span class="pill">Today <?= e($today) ?></span>
                    </div>
        </div>
    </div>
</section>

        <!-- Active Shift Coverage Section -->
        <section class="dashboard-section" data-section="shift-coverage">
            <div class="card">
    <div class="section-title">
        <h3>Active Shift Coverage</h3>
        <span><?= e(count($todaySchedule)) ?> assignments</span>
    </div>
    <table>
        <thead>
        <tr>
            <th>Shift</th>
            <th>Employee</th>
            <th>Status</th>
            <th>Break Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($todaySchedule as $assignment): ?>
            <tr>
                <td><?= e($assignment['shift_name']) ?></td>
                <td><?= e($assignment['employee_name']) ?></td>
                <td><?= e($assignment['attendance_status']) ?></td>
                <td>
                    <form method="post" action="/index.php" class="inline">
                        <input type="hidden" name="action" value="start_break">
                        <input type="hidden" name="employee_id" value="<?= e((string) $assignment['employee_id']) ?>">
                        <input type="hidden" name="schedule_shift_id" value="<?= e((string) $assignment['schedule_shift_id']) ?>">
                        <input type="hidden" name="worked_date" value="<?= e($today) ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <button type="submit" class="btn small">Start Break</button>
                    </form>
                    <form method="post" action="/index.php" class="inline">
                        <input type="hidden" name="action" value="end_break">
                        <input type="hidden" name="employee_id" value="<?= e((string) $assignment['employee_id']) ?>">
                        <input type="hidden" name="worked_date" value="<?= e($today) ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                        <button type="submit" class="btn secondary small">End Break</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
            </div>
</section>

        <!-- Break Monitoring Section -->
        <section class="dashboard-section" data-section="break-monitoring">
            <div class="card">
    <div class="section-title">
        <h3>Break Monitoring</h3>
        <span>Live break activity</span>
    </div>
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
            </div>
</section>

        <!-- Weekly Schedule Summary Section -->
        <section class="dashboard-section" data-section="weekly-schedule">
            <div class="card">
    <div class="section-title">
        <h3>Weekly Schedule Summary</h3>
        <span>Snapshot of the current week</span>
    </div>
    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Shift</th>
            <th>Employee</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($weekly as $entry): ?>
            <tr>
                <td><?= e($entry['shift_date']) ?></td>
                <td><?= e($entry['shift_name']) ?></td>
                <td><?= e($entry['employee_name']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
            </div>
</section>
    </main>
</div>
