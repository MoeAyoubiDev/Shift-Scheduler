<?php
declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="format-detection" content="telephone=no">
    <meta name="theme-color" content="#4f46e5">
    <title>Shift Scheduler - Professional Workforce Management</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="landing-page">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Professional Shift Scheduling Platform</h1>
            <p class="hero-description">
                Streamline workforce management with intelligent scheduling, real-time monitoring, and comprehensive analytics.
            </p>
            <div class="hero-actions">
                <a href="/signup.php" class="btn btn-primary btn-large">
                    <span>Get Started</span>
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
                <a href="/login.php" class="btn btn-secondary btn-large">
                    <span>Sign In</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Shift Scheduler. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>

