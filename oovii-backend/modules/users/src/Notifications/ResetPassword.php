<?php

namespace WezomCms\Users\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends BaseResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        return (new MailMessage())
            ->subject(__('cms-users::site.auth.Reset Password Notification'))
            ->line(__('cms-users::site.auth.Reset password receive text1'))
            ->action(
                __('cms-users::site.auth.Reset Password'),
                url(config('app.url') . route('auth.reset-password', [
                    'token' => $this->token,
                    'email' => $notifiable->getEmailForPasswordReset()
                ], false))
            )
            ->line(
                __(
                    'cms-users::site.auth.This password reset link will expire in :count minutes',
                    ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')]
                )
            )
            ->line(__('cms-users::site.auth.If you did not request a password reset no further action is required'));
    }
}
