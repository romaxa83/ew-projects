<?php

namespace App\Notifications;

use App\Models\Payrolls\Payroll;
use App\Models\Saas\Company\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendPdfPayroll extends Notification
{
    use Queueable;

    private string $pdf;
    private Company $company;
    private Payroll $payroll;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Company $company, Payroll $payroll, string $pdf)
    {
        $this->pdf = $pdf;
        $this->company = $company;
        $this->payroll = $payroll;
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
        $mail = (new MailMessage())
            ->greeting("Hello, " . $this->payroll->driver->full_name)
            ->subject(
                "Payroll for "
                . $this->payroll->start->format('m/d/Y')
                . ' to '
                . $this->payroll->end->format('m/d/Y')
            )
            ->line('You will find a PDF with the payroll attached to this email.')
            ->attachData(
                $this->pdf,
                'payroll.pdf',
                [
                    'mime' => 'application/pdf',
                ]
            );

        if ($this->company) {
            if ($this->company->getBillingEmail()) {
                $mail->replyTo($this->company->getBillingEmail());
            }

            $mail->viewData = [
                'companyName' => $this->company->getCompanyName(),
                'companyContactString' => $this->company->getMailContactString(),
            ];
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
            //
        ];
    }
}
