<?php
declare(strict_types=1);
?>
<div class="dashboard-container">
    <!-- Modern Navigation Cards -->
    <div class="dashboard-nav-cards">
        <button class="nav-card active" data-section="overview">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Overview</div>
                <div class="nav-card-subtitle">Dashboard metrics</div>
            </div>
        </button>
        
        <button class="nav-card" data-section="weekly-schedule">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Weekly Schedule</div>
                <div class="nav-card-subtitle">Schedule snapshot</div>
            </div>
        </button>
        
        <button class="nav-card" data-section="shift-requests">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Shift Requests</div>
                <div class="nav-card-subtitle">View requests</div>
            </div>
        </button>
        
        <button class="nav-card" data-section="performance">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 3V21H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M7 16L12 11L16 15L21 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M21 10V3H14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">Performance</div>
                <div class="nav-card-subtitle">Analytics & metrics</div>
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
                        <h2>Director Overview</h2>
                        <p>Read-only visibility for <?= e($user['section_name'] ?? 'Selected Section') ?>.</p>
                    </div>
                    <div class="meta-row">
                        <span class="pill">Week <?= e($weekStart) ?> → <?= e($weekEnd) ?></span>
                        <a class="btn secondary small" href="/index.php?reset_section=1">Change Section</a>
                    </div>
                </div>
                <div class="grid">
                    <?php foreach ($dashboard as $metric): ?>
                        <div class="metric">
                            <h3><?= e($metric['label']) ?></h3>
                            <div class="metric-value"><?= e((string) $metric['value']) ?></div>
                            <span class="muted"><?= e($metric['description']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Weekly Schedule Section -->
        <section class="dashboard-section" data-section="weekly-schedule">
            <div class="card">
                <div class="section-title">
                    <h3>Weekly Schedule Snapshot</h3>
                    <span><?= e(count($schedule)) ?> assignments</span>
                </div>
                <table>
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Employee</th>
                        <th>Source</th>
                        <th>Notes</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($schedule as $entry): ?>
                        <tr>
                            <td><?= e($entry['shift_date']) ?></td>
                            <td><?= e($entry['shift_name']) ?></td>
                            <td><?= e($entry['employee_name']) ?></td>
                            <td><?= e($entry['assignment_source']) ?></td>
                            <td><?= e($entry['notes']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Shift Requests Section -->
        <section class="dashboard-section" data-section="shift-requests">
            <div class="card">
                <div class="section-title">
                    <h3>Shift Requests</h3>
                    <span><?= e(count($requests)) ?> requests</span>
                </div>
                <table>
                    <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Request Date</th>
                        <th>Shift</th>
                        <th>Pattern</th>
                        <th>Importance</th>
                        <th>Status</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?= e($request['employee_name']) ?></td>
                            <td><?= e($request['request_date'] ?? $request['submit_date'] ?? 'N/A') ?></td>
                            <td><?= e($request['shift_name'] ?? 'OFF') ?></td>
                            <td><?= e($request['pattern_name']) ?></td>
                            <td><?= e($request['importance_level']) ?></td>
                            <td><span class="status <?= strtolower($request['status']) ?>"><?= e($request['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Performance Analytics Section -->
        <section class="dashboard-section" data-section="performance">
            <div class="card">
                <div class="section-title">
                    <h3>Performance Analytics</h3>
                    <span>Sorted by lowest delay</span>
                </div>
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
            </div>
        </section>
    </main>
</div>
