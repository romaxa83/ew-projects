<?php

namespace App\Notifications;

use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class DriverRegistrationEmail extends Notification
{
    use Queueable;

    private User $user;
    private string $token;
    private ?Company $company;

    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->token = $token;

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

        $mail = (new MailMessage())
            ->greeting("Hello, {$this->user->full_name}")
            ->subject('Set password')
            ->line('Hey, click the button to set your password.')
            ->action(
                'Set password',
                config('frontend.auth_url')
                    . '/password-set?token=' . $this->token
                    . '&email=' . $this->user->email
                    . '&expire=' . (
                        $passwordResetRecord
                            ? (
                                new Carbon($passwordResetRecord->created_at)
                            )->addMinutes(
                                config('auth.passwords.users.expire')
                            )->timestamp
                            : ''
                    )
                    . '&is_first_reg=true'
            )
            ->line('Thank you for using our service!');

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

            $mail->viewData += [
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
