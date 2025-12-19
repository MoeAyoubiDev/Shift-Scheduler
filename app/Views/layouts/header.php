<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Shift Scheduler') ?></title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">Shift Scheduler</div>
            <?php if (Auth::check()): ?>
                <div class="nav-menu">
                    <span class="nav-user"><?= htmlspecialchars(Auth::user()['username']) ?> 
                        (<?= htmlspecialchars(Auth::user()['role']) ?>)</span>
                    <a href="/logout.php" class="btn btn-sm">Logout</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>
    
    <div class="container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

