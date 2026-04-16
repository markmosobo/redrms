<?php

namespace App\Traits;

use App\Models\Notification;
use App\Models\User;

trait NotifiesUsers
{
    /**
     * Notify a single user
     */
    protected function notifyUser(
        int $userId,
        string $title,
        string $message,
        string $type,
        ?int $resourceId = null,
        ?string $resourceType = null
    ) {
        Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'resource_id' => $resourceId,
            'resource_type' => $resourceType,
        ]);
    }

    /**
     * Notify users by role
     */
    protected function notifyRoles(
        array $roles,
        string $title,
        string $message,
        string $type,
        ?int $resourceId = null,
        ?string $resourceType = null
    ) {
        User::whereIn('role', $roles)->get()->each(function ($user) use (
            $title, $message, $type, $resourceId, $resourceType
        ) {
            $this->notifyUser(
                $user->id,
                $title,
                $message,
                $type,
                $resourceId,
                $resourceType
            );
        });
    }
}