<?php
declare(strict_types=1);

/**
 * Action Router
 * Centralized routing system for all application actions
 * Handles form submissions, AJAX requests, and redirects
 */

class Router
{
    private static array $handlers = [];
    
    /**
     * Register an action handler
     */
    public static function register(string $action, callable $handler, array $requiredRoles = [], bool $requireCSRF = true): void
    {
        self::$handlers[$action] = [
            'handler' => $handler,
            'roles' => $requiredRoles,
            'csrf' => $requireCSRF,
        ];
    }
    
    /**
     * Handle incoming request
     */
    public static function handle(array $payload, array $context = []): array
    {
        $action = trim($payload['action'] ?? '');
        
        if (empty($action)) {
            return [
                'success' => false,
                'message' => 'No action specified.',
                'redirect' => null,
            ];
        }
        
        if (!isset(self::$handlers[$action])) {
            return [
                'success' => false,
                'message' => 'Unknown action: ' . $action,
                'redirect' => null,
            ];
        }
        
        $handler = self::$handlers[$action];
        
        // Check CSRF
        if ($handler['csrf']) {
            if (!verify_csrf($payload['csrf_token'] ?? null)) {
                return [
                    'success' => false,
                    'message' => 'Invalid security token. Please refresh the page and try again.',
                    'redirect' => null,
                ];
            }
        }
        
        // Check role
        if (!empty($handler['roles'])) {
            $userRole = current_role();
            if (!$userRole || !in_array($userRole, $handler['roles'], true)) {
                return [
                    'success' => false,
                    'message' => 'You do not have permission to perform this action.',
                    'redirect' => null,
                ];
            }
        }
        
        // Execute handler
        try {
            $result = call_user_func($handler['handler'], $payload, $context);
            
            // Normalize result
            if (is_string($result)) {
                return [
                    'success' => true,
                    'message' => $result,
                    'redirect' => null,
                ];
            }
            
            if (is_array($result)) {
                return array_merge([
                    'success' => true,
                    'message' => '',
                    'redirect' => null,
                ], $result);
            }
            
            return [
                'success' => true,
                'message' => 'Action completed successfully.',
                'redirect' => null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'redirect' => null,
            ];
        }
    }
    
    /**
     * Check if action exists
     */
    public static function hasAction(string $action): bool
    {
        return isset(self::$handlers[$action]);
    }
}

