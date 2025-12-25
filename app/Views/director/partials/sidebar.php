<?php
declare(strict_types=1);

$primaryNav = [
    [
        'slug' => 'overview',
        'label' => 'Overview',
        'subtitle' => 'Executive summary',
        'href' => '/dashboard/overview.php',
        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    ],
    [
        'slug' => 'employees',
        'label' => 'Employees',
        'subtitle' => 'Roster & requests',
        'href' => '/dashboard/employees.php',
        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M17 21V19C17 17.9391 16.5786 16.9217 15.8284 16.1716C15.0783 15.4214 14.0609 15 13 15H6C4.93913 15 3.92172 15.4214 3.17157 16.1716C2.42143 16.9217 2 17.9391 2 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9.5 11C11.7091 11 13.5 9.20914 13.5 7C13.5 4.79086 11.7091 3 9.5 3C7.29086 3 5.5 4.79086 5.5 7C5.5 9.20914 7.29086 11 9.5 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    ],
    [
        'slug' => 'attendance',
        'label' => 'Attendance',
        'subtitle' => 'Coverage cadence',
        'href' => '/dashboard/attendance.php',
        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 2V6M16 2V6M3 10H21M5 4H19C20.1046 4 21 4.89543 21 6V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V6C3 4.89543 3.89543 4 5 4Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 14H15M9 17H13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    ],
    [
        'slug' => 'reports',
        'label' => 'Reports',
        'subtitle' => 'Performance trends',
        'href' => '/dashboard/reports.php',
        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 3H21V21H3V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M7 15L10 12L13 15L17 11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    ],
];

$managementNav = [
    [
        'slug' => 'departments',
        'label' => 'Departments',
        'subtitle' => 'Section structure',
        'href' => '/dashboard/departments.php',
        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 7L12 2L21 7V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V7Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    ],
    [
        'slug' => 'settings',
        'label' => 'Settings',
        'subtitle' => 'Leadership tools',
        'href' => '/dashboard/settings.php',
        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 15.5C13.3807 15.5 14.5 14.3807 14.5 13C14.5 11.6193 13.3807 10.5 12 10.5C10.6193 10.5 9.5 11.6193 9.5 13C9.5 14.3807 10.6193 15.5 12 15.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M19.4 15C19.8 14.2 20 13.2 20 12C20 10.8 19.8 9.8 19.4 9L21 7L19 3L16.8 4C16 3.6 15 3.4 14 3.4H10C9 3.4 8 3.6 7.2 4L5 3L3 7L4.6 9C4.2 9.8 4 10.8 4 12C4 13.2 4.2 14.2 4.6 15L3 17L5 21L7.2 20C8 20.4 9 20.6 10 20.6H14C15 20.6 16 20.4 16.8 20L19 21L21 17L19.4 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    ],
];

?>
<aside class="dashboard-sidebar director-sidebar">
    <div class="director-sidebar-header">
        <h2 class="director-sidebar-title">Director Console</h2>
        <p class="director-sidebar-subtitle">Executive control center</p>
    </div>

    <!-- Primary Navigation Cards -->
    <div class="director-sidebar-nav-cards">
        <?php foreach ($primaryNav as $item): ?>
            <a href="<?= e($item['href']) ?>" class="nav-card director-nav-card <?= $directorPage === $item['slug'] ? 'active' : '' ?>" <?= $directorPage === $item['slug'] ? 'aria-current="page"' : '' ?>>
                <div class="nav-card-icon">
                    <?= str_replace('width="20" height="20"', 'width="24" height="24"', $item['icon']) ?>
                </div>
                <div class="nav-card-content">
                    <div class="nav-card-title"><?= e($item['label']) ?></div>
                    <div class="nav-card-subtitle"><?= e($item['subtitle']) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    
    <!-- Management Section Header -->
    <div class="director-sidebar-group-header">
        <h3 class="director-sidebar-group-title">Management</h3>
        <p class="director-sidebar-group-subtitle">Departments & settings</p>
    </div>
    
    <!-- Management Navigation Cards -->
    <div class="director-sidebar-nav-cards director-sidebar-management">
        <?php foreach ($managementNav as $item): ?>
            <a href="<?= e($item['href']) ?>" class="nav-card director-nav-card <?= $directorPage === $item['slug'] ? 'active' : '' ?>" <?= $directorPage === $item['slug'] ? 'aria-current="page"' : '' ?>>
                <div class="nav-card-icon">
                    <?= str_replace('width="20" height="20"', 'width="24" height="24"', $item['icon']) ?>
                </div>
                <div class="nav-card-content">
                    <div class="nav-card-title"><?= e($item['label']) ?></div>
                    <div class="nav-card-subtitle"><?= e($item['subtitle']) ?></div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</aside>
