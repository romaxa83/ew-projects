<?php

namespace App\Notifications;

use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class MailResetPasswordToken extends Notification
{
    use Queueable;

    private string $token;
    private ?string $password;
    private User $user;
    private ?Company $company;

    public function __construct(string $token, User $user, $password)
    {
        $this->token = $token;
        $this->user = $user;
        $this->password = $password;

        $this->company = $user->getCompany();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        $passwordResetRecord = DB::table('password_resets')
            ->where('email', $this->user->email)
            ->first();

        $backendLink = route(
            'v1.authorize.password.reset',
            [
                'token' => $this->token,
                'email' => $this->user->email,
                'expire' => $passwordResetRecord
                    ? (new Carbon($passwordResetRecord->created_at))->addMinutes(config('auth.passwords.users.expire'))->timestamp
                    : ''
            ],
            false
        );

        $frontedLink = config('frontend.auth_url')
            . str_replace(
                '/v1/auth',
                '',
                $backendLink
            );

        if ($this->password) {
            $mail = (new MailMessage())
                ->greeting("Hello, {$this->user->full_name}")
                ->subject("Reset your password")
                ->line("You have made a request to recover your password on the site [Haulk](" . config('frontend.url') . ").")
                ->line("To set a new password follow the link below:")
                ->action('Reset Password', $frontedLink)
                ->line('If you have not made a password recovery request, please skip this email.');
        } else {
            $mail = (new MailMessage())
                ->greeting("Hello, {$this->user->full_name}")
                ->subject("Set your password")
                ->line("You have been successfully registered on the site [Haulk](" . config('frontend.url') . ").")
                ->line("To set your password follow the link below:")
                ->action('Set Password', $frontedLink)
                ->line('If you didn\'t registered on our site, please skip this email.');
        }

        $mail->viewData = [
            'android' => [
                'name' => 'Android',
                'image_url' => config('frontend.images.email.google_play'),
                'app_url' => config('urls.android_app'),
            ],
            'ios' => [
                'name' => 'Ios',
                'image_url' => config('frontend.images.email.app_store'),
                'app_url' => config('urls.ios_app'),
            ]
        ];

        if ($this->company) {
            $mail->replyTo($this->company->getContactEmail());

            $mail->viewData = [
                'companyName' => $this->company->getCompanyName(),
                'companyContactString' => $this->company->getMailContactString(),
            ];
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
