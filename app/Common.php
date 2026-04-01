<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (! function_exists('log_activity')) {
    /**
     * Backward-compatible audit logger used across controllers and commands.
     */
    function log_activity(
        string $type,
        string $description,
        ?string $resource = null,
        int|string|null $resourceId = null,
        int|string|null $userId = null,
        ?string $ipAddress = null
    ): void {
        try {
            $auditModel = model(\App\Models\AuditLogModel::class);
            $details = $description;

            if ($resource !== null || $resourceId !== null || $ipAddress !== null) {
                $details .= ' | contexte=' . json_encode([
                    'resource' => $resource,
                    'resource_id' => $resourceId,
                    'ip' => $ipAddress,
                ], JSON_UNESCAPED_UNICODE);
            }

            $auditModel->log(
                $type,
                is_numeric((string) $userId) ? (int) $userId : null,
                $details
            );
        } catch (\Throwable $e) {
            log_message('error', 'Audit log failure: {message}', ['message' => $e->getMessage()]);
        }
    }
}
