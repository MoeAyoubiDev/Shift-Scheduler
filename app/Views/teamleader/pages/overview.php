<?php
declare(strict_types=1);
?>
<section class="dashboard-surface teamleader-dashboard-page">
    <div class="dashboard-inner">
        <div class="dashboard-hero">
            <div>
                <h1>Team Leader Control Center</h1>
                <p class="muted">Run your section, track coverage, and keep schedules aligned.</p>
            </div>
            <div class="dashboard-hero-actions">
                <button type="button" class="week-selector-pill" id="week-selector" data-week-start="<?= e($weekStart) ?>" data-week-end="<?= e($weekEnd) ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Week <?= e($weekStart) ?> → <?= e($weekEnd) ?></span>
                </button>
                <a class="btn ghost small" href="/index.php?download=schedule&week_start=<?= e($weekStart) ?>&week_end=<?= e($weekEnd) ?>">Export CSV</a>
            </div>
        </div>

        <div class="metric-grid teamleader-metric-grid">
            <div class="dashboard-card metric-card">
                <div class="metric-icon accent-purple">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="metric-label">Pending Requests</div>
                <div class="metric-value"><?= e((string) ($metrics['pending_requests'] ?? 0)) ?></div>
                <div class="metric-label"><?= e((string) ($metrics['high_priority'] ?? 0)) ?> high priority</div>
            </div>

            <div class="dashboard-card metric-card">
                <div class="metric-icon accent-teal">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 8V12M12 16H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="metric-label">Coverage Gaps</div>
                <div class="metric-value"><?= e((string) ($metrics['coverage_gaps'] ?? 0)) ?></div>
                <div class="metric-label">Understaffed shifts</div>
            </div>

            <div class="dashboard-card metric-card">
                <div class="metric-icon accent-green">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
                        <path d="M12 7V12L15 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="metric-label">Active Breaks</div>
                <div class="metric-value"><?= e((string) ($metrics['active_breaks'] ?? 0)) ?></div>
                <div class="metric-label">Currently on break</div>
            </div>

            <div class="dashboard-card metric-card">
                <div class="metric-icon accent-purple">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div class="metric-label">Unassigned</div>
                <div class="metric-value"><?= e((string) ($metrics['unassigned'] ?? 0)) ?></div>
                <div class="metric-label">No shifts this week</div>
            </div>
        </div>

        <div class="dashboard-section-header">
            <h3>Quick Actions</h3>
        </div>
        <div class="quick-action-grid">
            <a class="dashboard-card quick-action-card" href="/dashboard/index.php?page=shift-requests" data-teamleader-nav="true">
                <span class="quick-action-icon accent-purple">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>Review Requests</span>
            </a>
            <a class="dashboard-card quick-action-card" href="/dashboard/index.php?page=weekly-schedule" data-teamleader-nav="true">
                <span class="quick-action-icon accent-teal">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>Manage Schedule</span>
            </a>
            <a class="dashboard-card quick-action-card" href="/dashboard/index.php?page=manage-employees" data-teamleader-nav="true">
                <span class="quick-action-icon accent-green">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M9 11C11.2091 11 13 9.20914 13 7C13 4.79086 11.2091 3 9 3C6.79086 3 5 4.79086 5 7C5 9.20914 6.79086 11 9 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>View Team</span>
            </a>
            <a class="dashboard-card quick-action-card" href="/dashboard/index.php?page=performance" data-teamleader-nav="true">
                <span class="quick-action-icon accent-purple">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                        <path d="M3 3V21H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M7 16L12 11L16 15L21 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>View Analytics</span>
            </a>
        </div>

        <div class="dashboard-lower-grid teamleader-overview-grid">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Pending Requests</h3>
                    <a class="btn ghost small" href="/dashboard/shift-requests.php">View all</a>
                </div>
                <div class="activity-list">
                    <?php if (!empty($pendingRequestsList)): ?>
                        <?php foreach ($pendingRequestsList as $request): ?>
                            <?php
                            $importance = strtolower((string) ($request['importance_level'] ?? 'medium'));
                            $iconClass = $importance === 'high' ? 'warning' : 'info';
                            ?>
                            <div class="activity-item">
                                <span class="activity-icon <?= e($iconClass) ?>"></span>
                                <div>
                                    <div class="request-title"><?= e($request['employee_name'] ?? 'Employee') ?> · <?= e($request['shift_name'] ?? 'Shift') ?></div>
                                    <div class="request-meta"><?= e($request['request_date'] ?? '') ?></div>
                                </div>
                                <span class="status-pill <?= e(strtolower((string) ($request['status'] ?? 'pending'))) ?>">
                                    <?= e(strtolower((string) ($request['status'] ?? 'pending'))) ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-title">No pending requests</div>
                            <p class="empty-state-text">You're all caught up for this week.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dashboard-side-stack">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Coverage Gaps</h3>
                    </div>
                    <?php if (!empty($coverageGapsList)): ?>
                        <div class="this-week-grid">
                            <?php foreach ($coverageGapsList as $gap): ?>
                                <div>
                                    <span><?= e($gap['date'] ?? '') ?> · <?= e($gap['shift_name'] ?? '') ?></span>
                                    <strong><?= e((string) ($gap['assigned'] ?? 0)) ?> / <?= e((string) ($gap['required'] ?? 0)) ?> covered</strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-title">Coverage looks good</div>
                            <p class="empty-state-text">No gaps detected for this week.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Active Breaks</h3>
                    </div>
                    <div class="activity-list">
                        <?php if (!empty($activeBreaksList)): ?>
                            <?php foreach ($activeBreaksList as $break): ?>
                                <?php $delay = (int) ($break['delay_minutes'] ?? 0); ?>
                                <div class="activity-item">
                                    <span class="activity-icon <?= e($delay > 0 ? 'warning' : 'success') ?>"></span>
                                    <div>
                                        <div class="request-title"><?= e($break['employee_name'] ?? 'Employee') ?></div>
                                        <div class="request-meta"><?= e($break['break_start'] ?? '') ?> · <?= e($break['shift_name'] ?? '') ?></div>
                                    </div>
                                    <?php if ($delay > 0): ?>
                                        <span class="status-pill pending"><?= e($delay) ?>m late</span>
                                    <?php else: ?>
                                        <span class="status-pill approved">On time</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-state-title">No active breaks</div>
                                <p class="empty-state-text">Everyone is on schedule.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Unassigned Team</h3>
                    </div>
                    <?php if (!empty($unassignedList)): ?>
                        <div class="this-week-grid">
                            <?php foreach ($unassignedList as $employee): ?>
                                <div>
                                    <span><?= e($employee['full_name'] ?? 'Employee') ?></span>
                                    <strong><?= e($employee['employee_code'] ?? '') ?></strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-title">Everyone assigned</div>
                            <p class="empty-state-text">All team members have shifts.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
