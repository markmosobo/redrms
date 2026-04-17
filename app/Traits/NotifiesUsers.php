<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Notification;

trait NotifiesUsers
{
    protected function notifyUser($userId, $title, $message, $type, $resourceId = null, $resourceType = null)
    {
        Notification::create([
            'user_id'        => $userId,
            'title'          => $title,
            'message'        => $message,
            'type'           => $type,
            'resource_id'    => $resourceId,
            'resource_type'  => $resourceType,
        ]);
    }

    protected function notifyRoles($roles, $title, $message, $type, $resourceId = null, $resourceType = null)
    {
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