<?php

namespace App\Notifications\Employees;

use App\Models\Employees\Employee;
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
        protected Employee $model,
        protected string $password,
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
            ->subject(__('mail.employee.send_credential.subject'))
            ->greeting(__('mail.employee.send_credential.greeting', [
                'name' => $this->model->first_name
            ]))
            ->line(new HtmlString('<br>'))
            ->line(__('mail.employee.send_credential.body'))
            ->line(new HtmlString('<br>'))
            ->line(__('mail.employee.send_credential.login', [
                'login' => $this->model->email->getValue()
            ]))
            ->line(new HtmlString('<br>'))
            ->line(__('mail.employee.send_credential.password', [
                'password' => $this->password
            ]))
            ;
    }
}
