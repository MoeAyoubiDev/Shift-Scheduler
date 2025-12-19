<?php
declare(strict_types=1);

// Start session
require_once __DIR__ . '/Core/Session.php';
Session::start();

// Load core classes
require_once __DIR__ . '/Core/Database.php';
require_once __DIR__ . '/Core/Auth.php';
require_once __DIR__ . '/Core/CSRF.php';
require_once __DIR__ . '/Core/Router.php';

// Load models
require_once __DIR__ . '/Models/User.php';
require_once __DIR__ . '/Models/Employee.php';
require_once __DIR__ . '/Models/ShiftRequest.php';
require_once __DIR__ . '/Models/Schedule.php';
require_once __DIR__ . '/Models/Break.php';
require_once __DIR__ . '/Models/Performance.php';

// Load controllers
require_once __DIR__ . '/Controllers/AuthController.php';
require_once __DIR__ . '/Controllers/DirectorController.php';
require_once __DIR__ . '/Controllers/TeamLeaderController.php';
require_once __DIR__ . '/Controllers/SupervisorController.php';
require_once __DIR__ . '/Controllers/SeniorController.php';
require_once __DIR__ . '/Controllers/EmployeeController.php';

