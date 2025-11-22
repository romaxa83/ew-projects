<?php

namespace App\Notifications\Catalog\Solutions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FindSolutionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private string $pdf)
    {
        //Queue doesn't work with pdf content
        $this->pdf = base64_encode($this->pdf);
    }

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->greeting(trans('messages.catalog.solutions.notification.greeting'))
            ->subject(trans('messages.catalog.solutions.notification.subject'))
            ->line(trans('messages.catalog.solutions.notification.line'))
            ->attachData(
                base64_decode($this->pdf),
                'find_solutions.pdf',
                [
                    'mime' => 'application/pdf'
                ]
            );
    }
}
