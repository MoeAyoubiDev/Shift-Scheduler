<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function render_view(string $template, array $data = []): void
{
    // Try new location first (includes/), then fall back to old location (app/Views/)
    $newPath = __DIR__ . '/../../includes/' . $template . '.php';
    $oldPath = __DIR__ . '/../Views/' . $template . '.php';
    
    $path = file_exists($newPath) ? $newPath : $oldPath;
    
    if (!file_exists($path)) {
        throw new RuntimeException("View template not found: {$template}");
    }
    
    extract($data, EXTR_SKIP);
    require $path;
}
