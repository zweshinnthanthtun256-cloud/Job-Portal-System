<?php

namespace App\Notifications;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Interview $interview, public string $title = 'Interview scheduled') {}

    public function via(object $notifiable): array
    {
        $preference = $notifiable->notificationPreference;

        return array_values(array_filter([$preference?->database_enabled === false ? null : 'database', $preference?->email_interviews === false ? null : 'mail']));
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => $this->title, 'message' => $this->message(), 'interview_id' => $this->interview->id, 'application_id' => $this->interview->application_id];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject($this->title)->greeting('Hello '.$notifiable->first_name.'.')->line($this->message())->action('View interviews', route('interviews.index'));
    }

    private function message(): string
    {
        return $this->interview->status === 'cancelled'
            ? 'An interview was cancelled: '.$this->interview->cancellation_reason
            : 'Your interview is scheduled for '.$this->interview->date->format('M j, Y').' at '.substr($this->interview->start_time, 0, 5).' '.$this->interview->timezone.'.';
    }
}
