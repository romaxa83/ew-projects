<?php

namespace App\Notifications\Saas\Admins;

use App\Models\Admins\Admin;
use App\Models\PasswordReset;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class AdminMailResetPasswordToken extends Notification
{
    use Queueable;

    private string $token;

    private Admin $admin;

    public function __construct(string $token, Admin $user)
    {
        $this->token = $token;
        $this->admin = $user;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $passwordResetRecord = PasswordReset::where('email', $this->admin->email)->first();

        $backendLink = route(
            'v1.saas.password.reset',
            [
                'token' => $this->token,
                'email' => $this->admin->email,
                'expire' => $passwordResetRecord
                    ? (new Carbon($passwordResetRecord->created_at))
                        ->addMinutes(config('auth.passwords.admins.expire'))
                        ->getTimestamp()
                    : ''
            ],
            false
        );

        $saasUrl = config('saas.url');

        $frontedLink = $saasUrl
            . str_replace(
                '/v1/saas',
                '',
                $backendLink
            );

        return (new MailMessage())
            ->greeting("Hello, {$this->admin->full_name}")
            ->subject("Reset your password")
            ->line("You have made a request to recover your password on the site [Haulk]({$saasUrl}).")
            ->line("To set a new password follow the link below:")
            ->action('Reset Password', $frontedLink)
            ->line('If you have not made a password recovery request, please skip this email.');
    }

    public function toArray($notifiable): array
    {
        return [
        ];
    }
}
