<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\User;
use App\Notifications\AsrGoNotification;

class NotificationService
{
    public function log(int $userId, string $type, string $message, string $relatedModel, int $relatedId): NotificationLog
    {
        $notification = NotificationLog::create([
            'user_id' => $userId,
            'type' => $type,
            'message' => $message,
            'related_model' => $relatedModel,
            'related_id' => $relatedId,
        ]);

        $user = User::find($userId);
        if ($user?->email) {
            $user->notify(new AsrGoNotification('Notifikasi ASR GO', $message));
        }

        return $notification;
    }
}
