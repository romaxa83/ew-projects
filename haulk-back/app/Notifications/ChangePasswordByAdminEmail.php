<?php

namespace App\Notifications;

use App\Models\Saas\Company\Company;
use App\Models\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChangePasswordByAdminEmail extends Notification
{
    use Queueable;

    private User $user;

    /**
     * Create a new notification instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->company = $user->getCompany();
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
            ->subject("Password change")
            ->greeting("Hello, " . $this->user->full_name)
            ->line("Your password was changed.");

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
