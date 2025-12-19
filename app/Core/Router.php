<?php
declare(strict_types=1);

class Router
{
    private array $routes = [];
    
    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $path = rtrim($path, '/') ?: '/';
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                call_user_func($route['handler']);
                return;
            }
        }
        
        http_response_code(404);
        echo "404 Not Found";
    }
    
    private function matchPath(string $pattern, string $path): bool
    {
        $pattern = '#^' . preg_replace('#\{[^}]+\}#', '[^/]+', $pattern) . '$#';
        return (bool) preg_match($pattern, $path);
    }
}

