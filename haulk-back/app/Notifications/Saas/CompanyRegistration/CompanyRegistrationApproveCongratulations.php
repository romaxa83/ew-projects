<?php

namespace App\Notifications\Saas\CompanyRegistration;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyRegistrationApproveCongratulations extends Notification
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
            ->subject("Welcome to Haulk - Transforming Your Transportation Management!")
            ->greeting('Hello ' . $notifiable->getFullName() . ',')
            ->line("Congratulations on your registration!  We're thrilled to have you with us. You've taken the first step towards simplifying and enhancing your transportation management processes with Haulk. Get ready to experience seamless order processing, effective vehicle management, and much more.")
            ;
    }

    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
