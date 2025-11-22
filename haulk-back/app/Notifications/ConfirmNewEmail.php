<?php

namespace App\Notifications;

use App\Models\Saas\Company\Company;
use App\Models\Users\ChangeEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmNewEmail extends Notification
{
    use Queueable;

    private string $changeEmail;
    private ?string $confirmUrl;
    private string $declineUrl;
    private ?Company $company;

    /**
     * Create a new notification instance.
     *
     * @param ChangeEmail $changeEmail
     * @param $confirmUrl
     * @param $declineUrl
     */
    public function __construct(ChangeEmail $changeEmail, $confirmUrl, $declineUrl)
    {
        $this->changeEmail = $changeEmail;
        $this->confirmUrl = $confirmUrl;
        $this->declineUrl = $declineUrl;

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
        if ($this->confirmUrl) {
            $mail = (new MailMessage())
                ->subject("Email change")
                ->markdown('mail.change-email', [
                    'line1' => "New email change request was created in your account.",
                    'line2' => "Please click the button to confirm your new email.",
                    'action1' => [
                        'text' => 'Confirm email',
                        'url' => $this->confirmUrl,
                    ],
                    'line3' => "If you want to cancel email change request click the button below.",
                    'action2' => [
                        'text' => 'Cancel email change',
                        'url' => $this->declineUrl,
                    ],
                ]);
        } else {
            $mail = (new MailMessage())
                ->subject("Email change")
                ->markdown('mail.change-email', [
                    'line1' => "New email change request was created in your account.",
                    'line2' => "If you want to cancel email change request click the button below.",
                    'action1' => [
                        'text' => 'Cancel email change',
                        'url' => $this->declineUrl,
                    ],
                ]);
        }

        if ($this->company) {
            $mail->replyTo($this->company->getContactEmail());

            $mail->viewData += [
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
