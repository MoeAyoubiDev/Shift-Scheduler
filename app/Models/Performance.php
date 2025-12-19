<?php
declare(strict_types=1);

require_once __DIR__ . '/../Core/Database.php';

class Performance
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function getReport(?int $sectionId = null, ?int $employeeId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        return Database::callProcedure('sp_get_performance_report', [
            $sectionId,
            $employeeId,
            $startDate,
            $endDate
        ]);
    }
    
    public function getDirectorDashboard(?int $sectionId = null): array
    {
        return Database::callProcedure('sp_get_director_dashboard', [$sectionId]);
    }
}

