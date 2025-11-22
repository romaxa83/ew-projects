<?php

namespace App\Notifications\Billing;

use App\Models\Billing\Invoice;
use Illuminate\Support\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendPdfInvoice extends Notification
{
    use Queueable;

    private string $pdf;

    private Invoice $invoice;

    /**
     * Create a new notification instance.
     * SendPdfInvoice constructor.
     * @param Invoice $invoice
     * @param string $pdf
     */
    public function __construct(Invoice $invoice, string $pdf)
    {
        $this->pdf = $pdf;
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
        $startCompanyDate = Carbon::parse($this->invoice->company->created_at)->format('m/d/Y');
        $mail = (new MailMessage)
            ->greeting('Hello,'.$this->invoice->company->getPaymentContactData()['full_name'])
            ->subject('Invoice from HAULK')
            ->line(
                'We remind you that your subscription to use the HAULK system is automatically'.
                ' renewed monthly starting from the '.$startCompanyDate.' until canceled. In the attachment'.
                ' of the letter there is an invoice for payment. Details on the calculation of the cost can be'.
                ' found here '. config('frontend.payment_history_url') .'.'
            )
            ->attachData(
                $this->pdf,
                'invoice.pdf',
                [
                    'mime' => 'application/pdf',
                ]
            );
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
            //
        ];
    }
}
