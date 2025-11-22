<?php


namespace App\Notifications\Billing;


use App\Models\Billing\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransactionUnderReview extends Notification
{
    use Queueable;

    private Invoice $invoice;
    private string $transID;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice, string $transID)
    {
        $this->invoice = $invoice;
        $this->transID = $transID;
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
        $name = $this->invoice->company->getPaymentContactData()['full_name'];

        return (new MailMessage())
            ->subject('Problem with paying subscription bill')
            ->line('Hello ' . $name . '!')
            ->line(
                'Transaction #' . $this->transID . ' for invoice '
                . $this->invoice->billing_start->format('m/d/Y')
                . ' - '
                . $this->invoice->billing_end->format('m/d/Y')
                . ' was placed under review. Please contact support.'
            );
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
