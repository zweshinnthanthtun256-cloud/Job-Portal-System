<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ApplicationUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Application $application, public string $title, public string $message) {}

    public function via(object $notifiable): array
    {
        $preference = $notifiable->notificationPreference;

        return array_values(array_filter([$preference?->database_enabled === false ? null : 'database', $preference?->email_application_updates === false ? null : 'mail']));
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => $this->title, 'message' => $this->message, 'application_id' => $this->application->id, 'job_id' => $this->application->job_id];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = $notifiable->role === 'employer' ? route('employer.candidates.index') : route('applications.index');

        return (new MailMessage)->subject($this->title)->greeting('Hello '.$notifiable->first_name.'.')->line($this->message)->action('View application', $url);
    }
}
