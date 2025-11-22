<?php

namespace App\Notifications\Faq\Questions;

use App\Models\Faq\Question;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\HtmlString;

class AnswerTheQuestionNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Question $question)
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
            ->subject(__('messages.question.subject'))
            ->greeting(__('messages.question.greeting', ['name' => $this->question->name]))
            ->line(__('messages.question.line_1'))
            ->line($this->question->question)
            ->line(new HtmlString('<br>'))
            ->line(__('messages.question.line_2'))
            ->line($this->question->answer);
    }
}
