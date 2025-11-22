<?php

namespace App\Notifications\Companies;

use App\Models\Companies\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

class SendCodeForDealerNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected Company $model
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
        $url = config('app.site_url') . '/?isRegister=true&email=' . $this->model->email->getValue();
        return (new MailMessage())
            ->subject(__('messages.company.send_code.subject'))
            ->line(__('messages.company.send_code.greeting',[
                'name' => $this->model->business_name
            ]))
            ->line(new HtmlString('<br>'))
            ->line(__('messages.company.send_code.line_1'))
            ->line(new HtmlString('<br>'))
            ->line(__('messages.company.send_code.line_2'))
            ->line($this->model->manager->name ?? null)
            ->line($this->model->manager?->phone->getValue() ?? null)
            ->line($this->model->manager?->email->getValue() ?? null)
            ->line(new HtmlString('<br>'))
            ->line(__('messages.company.send_code.line_3', [
                'login' => $this->model->email->getValue()
            ]))
            ->line(__('messages.company.send_code.line_4',[
                'code' => $this->model->code
            ]))
            ->line(new HtmlString('<br>'))
            ->line(__('messages.company.send_code.line_5'))
            ->line(new HtmlString('<br>'))
            ->action('Sing up', $url)
            ->line(new HtmlString('<br>'))
            ->line(__('messages.company.send_code.line_6'))
            ->line(new HtmlString('<br>'))
            ;
    }
}
