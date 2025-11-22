<?php

namespace App\Notifications;

use App\Models\Saas\Company\Company;
use App\Models\Users\ChangeEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailChanged extends Notification
{
    use Queueable;

    private ChangeEmail $changeEmail;
    private ?Company $company;

    /**
     * Create a new notification instance.
     *
     * @param ChangeEmail $changeEmail
     */
    public function __construct(ChangeEmail $changeEmail)
    {
        $this->changeEmail = $changeEmail;
        $this->company = $changeEmail->user->getCompany();
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
            ->subject("Email change")
            ->greeting("Hello,")
            ->line("Your email was changed. Now you can log in using your new credentials.");

        if ($this->company) {
            $mail->replyTo($this->company->getContactEmail());

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
