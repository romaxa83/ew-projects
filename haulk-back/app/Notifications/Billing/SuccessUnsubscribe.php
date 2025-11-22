<?php

namespace App\Notifications\Billing;

use App\Models\Saas\Company\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuccessUnsubscribe extends Notification
{
    use Queueable;

    private Company $company;
    private ?string $invoice;

    /**
     * Create a new notification instance.
     * @param Company $company
     * @param string|null $invoice
     */
    public function __construct(Company $company, ?string $invoice)
    {
        $this->company = $company;
        $this->invoice = $invoice;
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
        $mail = (new MailMessage)
            ->subject('Successful unsubscribe notification')
            ->line('Hello ' . $name . '!')
            ->line('You have successfully unsubscribed from the Haulk system.')
            ->line('To restore your subscription, go to the Billing section.');

        if ($this->invoice) {
            $mail->attachData(
                $this->invoice,
                'invoice.pdf',
                [
                    'mime' => 'application/pdf',
                ]
            );
        }

        return $mail;
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
