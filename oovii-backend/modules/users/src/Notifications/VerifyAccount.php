<?php

namespace WezomCms\Users\Notifications;

use Illuminate\Auth\Events\Verified;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use NotificationChannels\TurboSms\TurboSmsChannel;
use NotificationChannels\TurboSms\TurboSmsMessage;
use WezomCms\Users\Models\User;
use WezomCms\Users\Notifications\Channels\ESputnikChannel;

class VerifyAccount extends Notification
{
    /**
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if ($notifiable->registered_through === User::PHONE) {
            switch (config('cms.users.users.sms_service')) {
                case 'turbosms':
                    return [TurboSmsChannel::class];
                case 'esputnik':
                    return [ESputnikChannel::class];
                default:
                    return [];
            }
        } elseif ($notifiable->registered_through === User::EMAIL) {
            return ['mail'];
        } else {
            if ($notifiable->markEmailAsVerified()) {
                event(new Verified($notifiable));
            }
            return [];
        }
    }

    /**
     * @param  mixed  $notifiable
     * @return TurboSmsMessage
     */
    public function toTurboSms($notifiable)
    {
        $notifiable->generateTemporaryCode();
        $content = __('cms-users::site.auth.Your verification code is: :code', ['code' => $notifiable->temporary_code]);

        return TurboSmsMessage::create($content);
    }

    /**
     * @param  mixed  $notifiable
     * @return array|string|null
     */
    public function toESputnik($notifiable)
    {
        $notifiable->generateTemporaryCode();
        return __('cms-users::site.auth.Your verification code is: :code', ['code' => $notifiable->temporary_code]);
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject(__('cms-users::site.auth.Verify Email Address'))
            ->line(__('cms-users::site.auth.Please click the button below to verify your email address'))
            ->action(__('cms-users::site.auth.Confirm'), $this->verificationUrl($notifiable))
            ->line(__('cms-users::site.auth.If you did not create an account no further action is required'));
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'auth.verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $notifiable->getKey()]
        );
    }
}
