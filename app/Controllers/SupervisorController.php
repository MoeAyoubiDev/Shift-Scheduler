<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';

class SupervisorController
{
    public static function enforceAccess(): void
    {
        require_login();
        require_role(['Supervisor']);
    }
}
