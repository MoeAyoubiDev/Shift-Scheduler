<?php
declare(strict_types=1);
?>
<section class="dashboard-page">
    <div class="dashboard-page-header">
        <div>
            <h2>Settings</h2>
            <p class="muted">Leadership tools, permissions management, and admin oversight.</p>
        </div>
    </div>

    <div class="dashboard-panel-grid">
        <div class="card admin-card admin-card--form">
            <div class="card-header">
                <div>
                    <h3>Create Team Leader or Supervisor</h3>
                    <p>Assign leadership roles to sections. Team Leaders have full management permissions, while Supervisors have read-only access.</p>
                </div>
                <div class="card-header-meta">
                    <span class="card-meta-label">Roles</span>
                    <span class="card-meta-value">Team Leader · Supervisor</span>
                </div>
            </div>
            <div class="card-body">
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
        </div>

        <div class="card admin-card admin-card--directory">
            <div class="card-header">
                <div>
                    <h3>Admins Directory</h3>
                    <p>All Team Leaders and Supervisors across sections.</p>
                </div>
                <div class="card-header-meta">
                    <span class="card-meta-label">Admins</span>
                    <span class="card-meta-value"><?= e((string) count($admins)) ?></span>
                </div>
            </div>
            <div class="card-body">
                <?php if ($admins): ?>
                    <div class="table-wrapper">
                        <table class="dashboard-table">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Section</th>
                                <th>Username</th>
                                <th>Email</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td><?= e($admin['full_name']) ?></td>
                                    <td><?= e($admin['role_name']) ?></td>
                                    <td><?= e($admin['section_name']) ?></td>
                                    <td><?= e($admin['username']) ?></td>
                                    <td><?= e($admin['email'] ?? 'N/A') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No admins yet</div>
                        <div class="empty-state-text">Create a Team Leader or Supervisor to populate this list.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
