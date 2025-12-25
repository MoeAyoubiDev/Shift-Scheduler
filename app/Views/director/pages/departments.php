<?php
declare(strict_types=1);

$leadersBySection = [];
foreach ($admins as $admin) {
    $sectionName = $admin['section_name'] ?? 'Unassigned';
    if (!isset($leadersBySection[$sectionName])) {
        $leadersBySection[$sectionName] = [];
    }
    $leadersBySection[$sectionName][] = $admin['full_name'] ?? $admin['username'] ?? 'Unknown';
}
?>
<section class="dashboard-page">
    <div class="dashboard-page-header">
        <div>
            <h2>Departments</h2>
            <p class="muted">Section coverage, leadership ownership, and structural clarity.</p>
        </div>
    </div>

    <div class="dashboard-panel-grid">
        <div class="card departments-card">
            <div class="card-header">
                <div>
                    <h3>Departments Directory</h3>
                    <p>All sections currently configured in the organization.</p>
                </div>
                <div class="card-header-meta">
                    <span class="card-meta-label">Departments</span>
                    <span class="card-meta-value"><?= e((string) count($sections)) ?></span>
                </div>
            </div>
            <div class="card-body">
                <?php if ($sections): ?>
                    <div class="table-wrapper">
                        <table class="dashboard-table">
                            <thead>
                            <tr>
                                <th>Department</th>
                                <th>Section ID</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($sections as $section): ?>
                                <tr>
                                    <td><?= e($section['section_name']) ?></td>
                                    <td><?= e((string) $section['id']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No departments available</div>
                        <div class="empty-state-text">Create sections to organize teams and scheduling.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card departments-card">
            <div class="card-header">
                <div>
                    <h3>Leadership Coverage</h3>
                    <p>Assignments across departments to keep operations aligned.</p>
                </div>
                <div class="card-header-meta">
                    <span class="card-meta-label">Leaders</span>
                    <span class="card-meta-value"><?= e((string) count($admins)) ?></span>
                </div>
            </div>
            <div class="card-body">
                <?php if ($sections): ?>
                    <div class="table-wrapper">
                        <table class="dashboard-table">
                            <thead>
                            <tr>
                                <th>Department</th>
                                <th>Leaders Assigned</th>
                                <th>Primary Contact</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($sections as $section): ?>
                                <?php
                                $sectionName = $section['section_name'];
                                $leaders = $leadersBySection[$sectionName] ?? [];
                                ?>
                                <tr>
                                    <td><?= e($sectionName) ?></td>
                                    <td><?= e((string) count($leaders)) ?></td>
                                    <td><?= e($leaders[0] ?? 'Unassigned') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-title">No leadership assignments</div>
                        <div class="empty-state-text">Add leadership roles to map ownership per department.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
