<?php
declare(strict_types=1);

require_once __DIR__ . '/../Core/Auth.php';
require_once __DIR__ . '/../Models/Performance.php';

class DirectorController
{
    public static function dashboard(): void
    {
        Auth::requireRole('Director');
        
        $user = Auth::user();
        $performance = new Performance();
        $dashboardData = $performance->getDirectorDashboard();
        
        require __DIR__ . '/../Views/director/dashboard.php';
    }
    
    public static function viewSection(int $sectionId): void
    {
        Auth::requireRole('Director');
        
        $user = Auth::user();
        
        // Make sectionId available to the view
        $GLOBALS['sectionId'] = $sectionId;
        require __DIR__ . '/../Views/director/section-view.php';
    }
}

