<?php
namespace App\Helpers;

use App\Models\Notification;

class NotificationHelper
{
    public static function send($users, $type, $message, $redirect = null)
    {
        foreach ($users as $user) {
            Notification::create([
                'type' => $type,
                'message' => $message,
                'notifiable_id' => $user->id,
                'notifiable_type' => get_class($user),
                'is_read' => 0, // 🔥 IMPORTANT (missing before)
                'redirect_to' => $redirect,
            ]);
        }
    }
}
