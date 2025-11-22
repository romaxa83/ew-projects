<?php


namespace App\Notifications\Saas\Invoices;


use App\Models\Billing\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoicePaymentPending extends Notification
{
    use Queueable;

    private string $transID;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(string $transID)
    {
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
        return (new MailMessage())
            ->subject('Invoice payment pending')
            ->line('Transaction #' . $this->transID . ' was placed under review. Please resolve.');
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
