<?php

namespace App\Notifications\Commercial;

use App\Models\Commercial\CommercialQuote;
use App\Models\Commercial\QuoteHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

class CommercialQuoteNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected CommercialQuote $model,
        protected QuoteHistory $history,
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
        return (new MailMessage())
            ->subject(__('messages.commercial.quote.subject'))
            ->line(__('messages.commercial.quote.greeting',[
                'name' => $this->model->commercialProject->member->getName()
            ]))
            ->line(new HtmlString('<br>'))
            ->line(__('messages.commercial.quote.body',[
                'project_name' => $this->model->commercialProject->name
            ]))
            ->line(new HtmlString('<br>'))
            ->attach($this->history->getPdfPath(), ['mime' => 'application/pdf'])
        ;
    }
}
