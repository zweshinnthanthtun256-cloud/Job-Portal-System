<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ApplicationUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Application $application, public string $title, public string $message) {}

    public function via(object $notifiable): array
    {
        $preference = $notifiable->notificationPreference;

        return $preference?->database_enabled === false ? [] : ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => $this->title, 'message' => $this->message, 'application_id' => $this->application->id, 'job_id' => $this->application->job_id];
    }
}
