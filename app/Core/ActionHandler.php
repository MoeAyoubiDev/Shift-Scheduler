<?php
declare(strict_types=1);

/**
 * Action Handler
 * Centralized action processing with proper error handling
 */

require_once __DIR__ . '/../Helpers/helpers.php';
require_once __DIR__ . '/../Controllers/AuthController.php';
require_once __DIR__ . '/../Controllers/DirectorController.php';
require_once __DIR__ . '/../Controllers/TeamLeaderController.php';
require_once __DIR__ . '/../Controllers/EmployeeController.php';
require_once __DIR__ . '/../Controllers/SeniorController.php';
require_once __DIR__ . '/../Controllers/SupervisorController.php';
require_once __DIR__ . '/Router.php';

class ActionHandler
{
    /**
     * Initialize all action handlers
     */
    public static function initialize(int $weekId): void
    {
        // Authentication actions
        Router::register('logout', function(array $payload) {
            AuthController::handleLogout($payload);
            return ['redirect' => '/index.php'];
        }, [], true);

        Router::register('login', function(array $payload) {
            return AuthController::handleLogin($payload);
        }, [], true);

        Router::register('signup', function(array $payload) {
            return AuthController::handleSignup($payload);
        }, [], true);
        
        // Director actions
        Router::register('select_section', function(array $payload) {
            DirectorController::handleSelectSection($payload);
            return ['redirect' => '/index.php'];
        }, ['Director'], true);
        
        Router::register('create_leader', function(array $payload) {
            $result = DirectorController::handleCreateLeader($payload);
            return ['message' => $result ?? 'Leader created successfully.'];
        }, ['Director'], true);
        
        // Team Leader actions
        Router::register('create_employee', function(array $payload) {
            $result = TeamLeaderController::handleCreateEmployee($payload);
            return ['message' => $result ?? 'Employee created successfully.'];
        }, ['Team Leader', 'Supervisor'], true);
        
        Router::register('update_employee', function(array $payload) {
            $result = TeamLeaderController::handleUpdateEmployee($payload);
            return ['message' => $result ?? 'Employee updated successfully.'];
        }, ['Team Leader'], true);
        
        Router::register('delete_employee', function(array $payload) {
            $result = TeamLeaderController::handleDeleteEmployee($payload);
            return ['message' => $result ?? 'Employee deleted successfully.'];
        }, ['Team Leader'], true);
        
        Router::register('update_request_status', function(array $payload) {
            $result = TeamLeaderController::handleUpdateRequestStatus($payload);
            return ['message' => $result ?? 'Request status updated.'];
        }, ['Team Leader'], true);
        
        Router::register('save_requirements', function(array $payload, array $context) use ($weekId) {
            $sectionId = current_section_id();
            if (!$sectionId) {
                return ['success' => false, 'message' => 'Section not selected.'];
            }
            $result = TeamLeaderController::handleSaveRequirements($payload, $weekId, $sectionId);
            return ['message' => $result];
        }, ['Team Leader'], true);
        
        Router::register('generate_schedule', function(array $payload, array $context) use ($weekId) {
            $sectionId = current_section_id();
            if (!$sectionId) {
                return ['success' => false, 'message' => 'Section not selected.'];
            }
            $result = TeamLeaderController::handleGenerateSchedule($weekId, $sectionId);
            return ['message' => $result];
        }, ['Team Leader'], false);
        
        Router::register('assign_shift', function(array $payload, array $context) use ($weekId) {
            $sectionId = current_section_id();
            if (!$sectionId) {
                return ['success' => false, 'message' => 'Section not selected.'];
            }
            $result = TeamLeaderController::handleAssignShift($payload, $weekId, $sectionId);
            
            // Check if this is an AJAX request
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            
            if ($isAjax) {
                return [
                    'success' => strpos($result, 'successfully') !== false || strpos($result, 'success') !== false,
                    'message' => $result,
                    'ajax' => true,
                ];
            }
            
            return ['message' => $result, 'redirect' => '/index.php?message=' . urlencode($result)];
        }, ['Team Leader'], true);
        
        Router::register('update_assignment', function(array $payload) {
            $result = TeamLeaderController::handleUpdateAssignment($payload);
            return ['message' => $result];
        }, ['Team Leader'], true);
        
        Router::register('delete_assignment', function(array $payload) {
            $result = TeamLeaderController::handleDeleteAssignment($payload);
            return ['message' => $result];
        }, ['Team Leader'], true);
        
        Router::register('swap_shifts', function(array $payload, array $context) use ($weekId) {
            $sectionId = current_section_id();
            if (!$sectionId) {
                return ['success' => false, 'message' => 'Section not selected.'];
            }
            $result = TeamLeaderController::handleSwapShifts($payload, $weekId, $sectionId);
            return ['message' => $result ?? 'Shifts swapped successfully.'];
        }, ['Team Leader'], true);
        
        // Employee actions
        Router::register('submit_request', function(array $payload, array $context) use ($weekId) {
            $result = EmployeeController::handleSubmitRequest($payload, $weekId);
            return ['message' => $result];
        }, ['Employee'], true);
        
        Router::register('start_break', function(array $payload) {
            $role = current_role();
            if ($role === 'Senior') {
                $result = SeniorController::handleBreakAction($payload, 'start');
            } else {
                $result = EmployeeController::handleBreakAction($payload, 'start');
            }
            return ['message' => $result];
        }, ['Employee', 'Senior'], true);
        
        Router::register('end_break', function(array $payload) {
            $role = current_role();
            if ($role === 'Senior') {
                $result = SeniorController::handleBreakAction($payload, 'end');
            } else {
                $result = EmployeeController::handleBreakAction($payload, 'end');
            }
            return ['message' => $result];
        }, ['Employee', 'Senior'], true);

    }
    
    /**
     * Process request and return response
     */
    public static function process(array $payload, array $context = []): array
    {
        return Router::handle($payload, $context);
    }
}
