<?php

namespace App\Notifications\Billing;

use App\Models\Saas\Company\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RenewSubscribe extends Notification
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
        $name = $this->company->getPaymentContactData()['full_name'];
        return (new MailMessage)
            ->subject('Renewal of subscription')
            ->line('Hello ' . $name . '!')
            ->line('Your subscription is activated again!')
            ->line('You can always cancel your subscription in the billing section.');
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
        ];
    }
}
