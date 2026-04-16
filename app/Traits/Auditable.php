<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    protected function audit(string $description, ?int $userId = null): void
    {
        $userId = $userId ?? auth('api')->id();

        if (!$userId) {
            return;
        }

        AuditLog::create([
            'user_id'    => $userId,
            'description'=> $description,
            'ip_address' => request()->ip(),
        ]);
    }
}