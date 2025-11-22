<?php

namespace App\Notifications\Dealers;

use App\Dto\Dealers\DealerDto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

class SendCredentialsNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected DealerDto $dto
    )
    {}

    public function via(mixed $notifiable): array
    {
        return [
            'mail',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = config('app.site_url') . '/?isLogin=true';
        return (new MailMessage())
            ->subject(__('messages.dealer.send_credentials.subject'))
            ->line(__('messages.dealer.send_credentials.greeting',[
                'name' => $this->dto->name
            ]))
            ->line(new HtmlString('<br>'))
            ->line(__('messages.dealer.send_credentials.line_1', [
                'company_name' => $this->dto->companyName
            ]))
            ->line(new HtmlString('<br>'))
            ->line(__('messages.dealer.send_credentials.line_2'))
            ->line($this->dto->email->getValue())
            ->line(__('messages.dealer.send_credentials.line_3'))
            ->line($this->dto->password)
            ->line(new HtmlString('<br>'))
            ->action('Login', $url)
            ->line(new HtmlString('<br>'))
            ->line(__('messages.dealer.send_credentials.line_4'))
            ->line(new HtmlString('<br>'))
            ;
    }
}

