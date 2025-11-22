<?php

namespace App\Notifications\Saas\CompanyRegistration;

use App\Models\Saas\CompanyRegistration\CompanyRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmRegistration extends Notification
{
    use Queueable;

    protected CompanyRegistration $model;

    public function __construct(CompanyRegistration $model)
    {
        $this->model = $model;
    }


    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $link = config('saas.url').'/companies';

        return (new MailMessage())
            ->subject(__('email.saas.company_registration.confirm_company.subject'))
            ->line(__('email.saas.company_registration.confirm_company.body', [
                'company_name' => $this->model->name
            ]))
            ->action(
                __('email.saas.company_registration.confirm_company.button'),
                $link
            )
            ;
    }

    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
