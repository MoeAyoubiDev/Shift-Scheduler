<?php
declare(strict_types=1);
?>
<div class="dashboard-container director-dashboard">
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
                <div class="nav-card-subtitle">Executive summary</div>
            </div>
        </button>

        <button class="nav-card" data-section="admin">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2L3 7V12C3 16.4183 6.13401 20.4183 12 22C17.866 20.4183 21 16.4183 21 12V7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">ADMIN</div>
                <div class="nav-card-subtitle">Leadership tools</div>
            </div>
        </button>

        <button class="nav-card" data-section="employees">
            <div class="nav-card-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H6C4.93913 15 3.92172 15.4214 3.17157 16.1716C2.42143 16.9217 2 17.9391 2 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9.5 11C11.7091 11 13.5 9.20914 13.5 7C13.5 4.79086 11.7091 3 9.5 3C7.29086 3 5.5 4.79086 5.5 7C5.5 9.20914 7.29086 11 9.5 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M22 21V19C21.9993 18.1137 21.7044 17.2528 21.1614 16.5523C20.6184 15.8519 19.8581 15.3516 19 15.13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <div class="nav-card-title">EMPLOYEES</div>
                <div class="nav-card-subtitle">Schedules & requests</div>
            </div>
        </button>
    </div>

    <!-- Main Content Area -->
    <main class="dashboard-content">
        <!-- Overview Section -->
        <section class="dashboard-section active" data-section="overview">
            <div class="card overview-card">
                <div class="hero-row overview-hero">
                    <div>
                        <h2>Director Overview</h2>
                        <p class="muted">A clear snapshot of operational health, approvals, and leadership actions.</p>
                    </div>
                    <div class="meta-row">
                        <button type="button" class="week-selector-pill" id="week-selector" data-week-start="<?= e($weekStart) ?>" data-week-end="<?= e($weekEnd) ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Week <?= e($weekStart) ?> → <?= e($weekEnd) ?></span>
                        </button>
                        <a class="btn btn-change-section" href="/index.php?reset_section=1">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 6V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Change Section</span>
                        </a>
                    </div>
                </div>
                <div class="overview-layout">
                    <div class="overview-metrics">
                        <div class="grid metrics-grid">
                            <?php foreach ($dashboard as $metric): ?>
                                <div class="metric">
                                    <h3><?= e($metric['label']) ?></h3>
                                    <div class="metric-value"><?= e((string) $metric['value']) ?></div>
                                    <span class="muted"><?= e($metric['description']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <aside class="overview-actions">
                        <div class="overview-actions-header">
                            <h3>Quick Actions</h3>
                            <p class="muted">Jump directly to the workspace you need.</p>
                        </div>
                        <div class="quick-actions-grid">
                            <button class="quick-action-card" type="button" onclick="window.dashboard?.navigateToSection('admin')">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2L3 7V12C3 16.4183 6.13401 20.4183 12 22C17.866 20.4183 21 16.4183 21 12V7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Open ADMIN workspace</span>
                            </button>
                            <button class="quick-action-card" type="button" onclick="window.dashboard?.navigateToSection('employees')">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H6C4.93913 15 3.92172 15.4214 3.17157 16.1716C2.42143 16.9217 2 17.9391 2 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9.5 11C11.7091 11 13.5 9.20914 13.5 7C13.5 4.79086 11.7091 3 9.5 3C7.29086 3 5.5 4.79086 5.5 7C5.5 9.20914 7.29086 11 9.5 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Review EMPLOYEES updates</span>
                            </button>
                            <button class="quick-action-card" type="button" onclick="window.dashboard?.navigateToSection('admin')">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16 21V19C16 17.9391 15.5786 16.9217 14.8284 16.1716C14.0783 15.4214 13.0609 15 12 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M8.5 11C10.7091 11 12.5 9.20914 12.5 7C12.5 4.79086 10.7091 3 8.5 3C6.29086 3 4.5 4.79086 4.5 7C4.5 9.20914 6.29086 11 8.5 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M23 21V19C22.9993 18.1137 22.7044 17.2528 22.1614 16.5523C21.6184 15.8519 20.8581 15.3516 20 15.13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M16 3.13C16.8604 3.35031 17.623 3.85071 18.1676 4.55232C18.7122 5.25392 19.0078 6.11683 19.0078 7.005C19.0078 7.89318 18.7122 8.75608 18.1676 9.45769C17.623 10.1593 16.8604 10.6597 16 10.88" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Create a new leader</span>
                            </button>
                            <button class="quick-action-card" type="button" onclick="window.dashboard?.navigateToSection('employees')">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M16 13H8M16 17H8M10 9H8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Approve shift requests</span>
                            </button>
                        </div>
                    </aside>
                </div>
            </div>
        </section>

        <!-- Admin Section -->
        <section class="dashboard-section" data-section="admin">
            <div class="section-header section-banner">
                <div>
                    <h2>ADMIN</h2>
                    <p class="muted">Leadership actions, performance insight, and permission management.</p>
                </div>
            </div>
            <div class="dashboard-panel-grid">
                <div class="card">
                    <div class="hero-row">
                        <div>
                            <h2>Create Team Leader or Supervisor</h2>
                            <p>Assign leadership roles to sections. Team Leaders have full management permissions, while Supervisors have read-only access.</p>
                        </div>
                    </div>

                    <form method="post" action="/index.php" class="create-leader-form">
                    <input type="hidden" name="action" value="create_leader">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input 
                                type="text" 
                                id="full_name" 
                                name="full_name" 
                                class="form-input" 
                                required 
                                placeholder="Enter full name"
                                autocomplete="name"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="username" class="form-label">Username</label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                class="form-input" 
                                required 
                                placeholder="Enter username"
                                autocomplete="username"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email <span class="muted">(optional)</span></label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-input" 
                                placeholder="Enter email address"
                                autocomplete="email"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-container password-container">
                                <input 
                                    type="password" 
                                    id="password" 
                                    name="password" 
                                    class="form-input" 
                                    required 
                                    placeholder="Enter password"
                                    autocomplete="new-password"
                                >
                                <button 
                                    type="button" 
                                    class="password-toggle" 
                                    id="password-toggle-leader"
                                    aria-label="Toggle password visibility"
                                    tabindex="-1"
                                >
                                    <svg class="icon-eye" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 3C5.5 3 1.73 5.61 0 9C1.73 12.39 5.5 15 10 15C14.5 15 18.27 12.39 20 9C18.27 5.61 14.5 3 10 3ZM10 13C7.24 13 5 10.76 5 8C5 5.24 7.24 3 10 3C12.76 3 15 5.24 15 8C15 10.76 12.76 13 10 13ZM10 5C8.34 5 7 6.34 7 8C7 9.66 8.34 11 10 11C11.66 11 13 9.66 13 8C13 6.34 11.66 5 10 5Z" fill="currentColor"/>
                                    </svg>
                                    <svg class="icon-eye-off" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: none;">
                                        <path d="M2.5 2.5L17.5 17.5M8.16 8.16C7.84 8.5 7.65 8.96 7.65 9.45C7.65 10.43 8.42 11.2 9.4 11.2C9.89 11.2 10.35 11.01 10.69 10.69M14.84 14.84C13.94 15.54 12.78 16 11.5 16C7.91 16 4.81 13.92 2.5 10.5C3.46 8.64 4.9 7.2 6.66 6.34M12.41 4.41C13.5 4.78 14.52 5.32 15.43 6C18.09 8.08 21.19 10.16 24.5 10.5C23.54 12.36 22.1 13.8 20.34 14.66" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M10.59 6.59C11.37 6.22 12.15 6 12.5 6C15.09 6 17.19 8.1 17.19 10.69C17.19 11.04 16.97 11.82 16.6 12.6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="role_id" class="form-label">Role</label>
                            <select id="role_id" name="role_id" class="form-input" required>
                                <option value="">Select Role</option>
                                <?php foreach ($roles as $role): ?>
                                    <?php if (in_array($role['role_name'], ['Team Leader', 'Supervisor'], true)): ?>
                                        <option value="<?= e((string) $role['id']) ?>"><?= e($role['role_name']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="section_id" class="form-label">Section</label>
                            <select id="section_id" name="section_id" class="form-input" required>
                                <option value="">Select Section</option>
                                <?php foreach ($sections as $section): ?>
                                    <option value="<?= e((string) $section['id']) ?>"><?= e($section['section_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 0.5rem;">
                                <path d="M9 3V15M3 9H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            Create Leader
                        </button>
                    </div>
                    </form>
                </div>
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
            </div>
        </section>

        <!-- Employees Section -->
        <section class="dashboard-section" data-section="employees">
            <div class="section-header section-banner">
                <div>
                    <h2>EMPLOYEES</h2>
                    <p class="muted">Scheduling, requests, and staffing coverage at a glance.</p>
                </div>
            </div>
            <div class="dashboard-panel-grid">
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
            </div>
        </section>
        
        <script>
        // Password toggle for create leader form
        document.addEventListener('DOMContentLoaded', function() {
            const passwordToggle = document.getElementById('password-toggle-leader');
            const passwordInput = document.getElementById('password');
            if (passwordToggle && passwordInput) {
                const iconEye = passwordToggle.querySelector('.icon-eye');
                const iconEyeOff = passwordToggle.querySelector('.icon-eye-off');
                
                passwordToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    if (type === 'text') {
                        iconEye.style.display = 'none';
                        iconEyeOff.style.display = 'block';
                    } else {
                        iconEye.style.display = 'block';
                        iconEyeOff.style.display = 'none';
                    }
                });
            }
        });
        </script>
    </main>
</div>
