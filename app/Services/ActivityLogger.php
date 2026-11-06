<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Record an audit log entry.
     * Sanitizes sensitive fields before saving.
     */
    public static function log(string $action, string $module, string $description, ?array $oldData = null, ?array $newData = null): ActivityLog
    {
        $user = Auth::user();

        return ActivityLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'Sistem / Tamu',
            'action' => strtoupper($action),
            'module' => $module,
            'description' => $description,
            'old_data' => $oldData ? self::sanitizeData($oldData) : null,
            'new_data' => $newData ? self::sanitizeData($newData) : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Sanitize sensitive keys like passwords, tokens, full NIK, credit cards.
     */
    protected static function sanitizeData(array $data): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'remember_token', 'token', 'secret', 'api_key'];

        foreach ($data as $key => $val) {
            $lowerKey = strtolower($key);
            if (in_array($lowerKey, $sensitiveKeys)) {
                $data[$key] = '********';
            } elseif ($lowerKey === 'nik' && is_string($val) && strlen($val) === 16) {
                // Mask middle 8 digits of NIK for privacy protection
                $data[$key] = substr($val, 0, 4) . '********' . substr($val, -4);
            } elseif (is_array($val)) {
                $data[$key] = self::sanitizeData($val);
            }
        }

        return $data;
    }
}
