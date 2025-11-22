<?php

namespace App\Notifications\Auth;

use App\Models\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmRegistrationNotification extends Notification
{
    use Queueable;

    private User $user;
    private string $token;

    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage())
            ->greeting("Hello, {$this->user->full_name}")
            ->subject("Set your password")
            ->line("You have been successfully registered on the site [Haulk](" . config('routes.front.home') . ").")
            ->line("To set your password follow the link below:")
            ->action('Set Password', $this->token)
            ->line('If you didn\'t registered on our site, please skip this email.')
        ;

        $mail->viewData = [
            'android' => [
                'name' => 'Android',
                'image_url' => config('routes.images.email.google_play'),
                'app_url' => config('routes.mobile.android_app'),
            ],
            'ios' => [
                'name' => 'Ios',
                'image_url' => config('routes.images.email.app_store'),
                'app_url' => config('routes.mobile.ios_app'),
            ]
        ];

        return $mail;
    }

    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
