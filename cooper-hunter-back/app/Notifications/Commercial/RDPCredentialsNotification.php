<?php

namespace App\Notifications\Commercial;

use App\Models\Commercial\RDPAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

class RDPCredentialsNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public RDPAccount $account)
    {
    }

    public function via(mixed $notifiable): array
    {
        return [
            'mail',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(__('messages.commercial.rdp.subject'))
            ->line(new HtmlString('<br>'))
            ->line(__('messages.commercial.rdp.disclaimer'))
            ->line(new HtmlString('<br>'))
            ->line(__('messages.commercial.rdp.line_1', ['name' => $this->account->member->getName()]))
            ->line(new HtmlString('<br>'))
            ->line(
                new HtmlString(
                    __('messages.commercial.rdp.line_2', ['link' => config('front_routes.account-commercial')])
                )
            )
            ->markdown(
                'notifications::email',
                [
                    'additional_info' => [
                        __('fields.login') => (string)$this->account->login,
                        __('fields.password') => $this->account->password,
                    ],
                ]
            );
    }
}