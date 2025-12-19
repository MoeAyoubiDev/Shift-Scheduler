<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';

class DirectorController
{
    public static function handleSelectSection(array $payload): void
    {
        require_login();
        require_role(['Director']);
        require_csrf($payload);

        $sectionId = (int) ($payload['section_id'] ?? 0);
        if ($sectionId > 0) {
            set_current_section($sectionId);
        }

        header('Location: /index.php');
        exit;
    }
}
