<?php

namespace App\Notifications\Billing;

use App\Models\Saas\Company\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialEndWithoutPaymentData extends Notification
{
    use Queueable;

    private Company $company;
    /**
     * Create a new notification instance.
     * @param Company $company
     * @return void
     */
    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__("email.saas.company.billing.trial_end.subject"))
            ->greeting(__("email.saas.company.billing.trial_end.greeting", [
                'name' => $this->company->getPaymentContactData()['full_name']
            ]))
            ->line(__("email.saas.company.billing.trial_end.body"));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [
            //
        ];
    }
}
