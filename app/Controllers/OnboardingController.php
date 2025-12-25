<?php
declare(strict_types=1);

require_once __DIR__ . '/../Helpers/helpers.php';
require_once __DIR__ . '/../Models/Company.php';

class OnboardingController
{
    public static function handleStepSubmission(array $payload): array
    {
        require_login();
        require_csrf($payload);

        $user = current_user();
        $companyId = $user['company_id'] ?? null;
        if (!$companyId) {
            return ['success' => false, 'message' => 'Company ID not found.'];
        }

        $step = (int) ($payload['step'] ?? 0);
        if ($step < 1 || $step > 5) {
            return ['success' => false, 'message' => 'Invalid step number.'];
        }

        $stepData = $payload;
        unset($stepData['step'], $stepData['csrf_token'], $stepData['action']);

        // Handle JSON-encoded employees data from step 3
        if ($step === 3 && isset($stepData['employees']) && is_string($stepData['employees'])) {
            $stepData['employees'] = json_decode($stepData['employees'], true) ?? [];
        }

        // Validate step data
        $validation = self::validateStep($step, $stepData);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        // Save step data
        try {
            Company::updateOnboardingStep($companyId, "step_{$step}", $stepData, true);
            
            // Redirect to next step (unless it's step 5)
            $nextStep = $step < 5 ? ($step + 1) : 5;
            return ['success' => true, 'message' => 'Step saved successfully.', 'redirect' => '/onboarding.php?step=' . $nextStep];
        } catch (Exception $e) {
            error_log("Onboarding step save failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to save step. Please try again.'];
        }
    }

    private static function validateStep(int $step, array $data): array
    {
        switch ($step) {
            case 1: // Company Details
                if (empty($data['industry'])) {
                    return ['valid' => false, 'message' => 'Industry is required.'];
                }
                if (empty($data['company_size'])) {
                    return ['valid' => false, 'message' => 'Company size is required.'];
                }
                if (empty($data['timezone'])) {
                    return ['valid' => false, 'message' => 'Time zone is required.'];
                }
                break;

            case 2: // Work Rules
                if (empty($data['shift_duration']) || (float)$data['shift_duration'] < 1 || (float)$data['shift_duration'] > 24) {
                    return ['valid' => false, 'message' => 'Shift duration must be between 1 and 24 hours.'];
                }
                if (empty($data['max_consecutive_days']) || (int)$data['max_consecutive_days'] < 1) {
                    return ['valid' => false, 'message' => 'Max consecutive days must be at least 1.'];
                }
                if (empty($data['min_rest_hours']) || (int)$data['min_rest_hours'] < 0) {
                    return ['valid' => false, 'message' => 'Min rest hours is required.'];
                }
                break;

            case 3: // Employees Setup
                // Employees can be empty (can add later)
                // Just save empty array if no employees
                break;

            case 4: // Scheduling Preferences
                if (empty($data['default_view'])) {
                    return ['valid' => false, 'message' => 'Default view is required.'];
                }
                if (empty($data['week_start_day'])) {
                    return ['valid' => false, 'message' => 'Week start day is required.'];
                }
                break;

            case 5: // Review & Confirm
                // No validation needed, just confirmation
                break;
        }

        return ['valid' => true];
    }

    public static function handleComplete(array $payload): array
    {
        require_login();
        require_csrf($payload);

        $user = current_user();
        $companyId = $user['company_id'] ?? null;
        if (!$companyId) {
            return ['success' => false, 'message' => 'Company ID not found.'];
        }

        // Verify all steps are completed
        $progress = Company::getOnboardingProgress($companyId);
        for ($step = 1; $step <= 4; $step++) {
            $stepKey = "step_{$step}";
            if (empty($progress[$stepKey]) || !$progress[$stepKey]['completed']) {
                return ['success' => false, 'message' => "Please complete step {$step} first.", 'redirect' => '/onboarding.php?step=' . $step];
            }
        }

        try {
            // Mark onboarding as complete
            Company::updateOnboardingStep($companyId, "step_5", ['confirmed' => true], true);
            Company::activateCompany($companyId);

            return ['success' => true, 'redirect' => '/dashboard'];
        } catch (Exception $e) {
            error_log("Onboarding completion failed: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to complete onboarding. Please try again.'];
        }
    }
}

