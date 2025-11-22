<?php

namespace App\Notifications\Saas\CompanyRegistration;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotConfirmSomePeriod extends Notification
{
    use Queueable;
    public function __construct()
    {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("We Miss You at Haulk - Complete Your Signup!")
            ->greeting('Hi ' . $notifiable->getFullName() . ',')
            ->line("Your journey to streamlined transportation management is just a click away. We noticed you haven't completed your signup. Let's get you started!")
            ;
    }

    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
