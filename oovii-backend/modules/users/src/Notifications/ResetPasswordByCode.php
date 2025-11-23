<?php

namespace WezomCms\Users\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\TurboSms\TurboSmsChannel;
use NotificationChannels\TurboSms\TurboSmsMessage;
use WezomCms\Users\Notifications\Channels\ESputnikChannel;

class ResetPasswordByCode extends Notification
{
    /**
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        switch (config('cms.users.users.sms_service')) {
            case 'turbosms':
                return [TurboSmsChannel::class];
            case 'esputnik':
                return [ESputnikChannel::class];
            default:
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

        return TurboSmsMessage::create(
            __('cms-users::site.auth.Your password reset code is: :code', ['code' => $notifiable->temporary_code])
        );
    }

    /**
     * @param  mixed  $notifiable
     * @return string
     */
    public function toESputnik($notifiable)
    {
        $notifiable->generateTemporaryCode();

        return __('cms-users::site.auth.Your password reset code is: :code', ['code' => $notifiable->temporary_code]);
    }
}
