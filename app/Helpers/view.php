<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function render_view(string $template, array $data = []): void
{
    $path = __DIR__ . '/../Views/' . $template . '.php';
    extract($data, EXTR_SKIP);
    require $path;
}
