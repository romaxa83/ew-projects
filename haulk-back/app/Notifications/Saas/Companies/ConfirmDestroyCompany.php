<?php

namespace App\Notifications\Saas\Companies;

use App\Models\Saas\Company\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ConfirmDestroyCompany extends Notification
{
    use Queueable;

    const CONFIRM_URL = '/company-destroy';

    private Company $company;

    /**
     * Create a new notification instance.
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
            ->subject('Confirm deletion company')
            ->line('To confirm the deletion of '. $this->company->name .' company and all its data, follow the link in the letter.')
            ->action('Confirm action', config('saas.url') . self::CONFIRM_URL . '?action=confirm&token=' . $this->company->saas_confirm_token)
            ->line('If you did not ask for company deletion actions, please ignore this message. Or click "Decline action".')
            ->action('Decline action', config('saas.url') . self::CONFIRM_URL . '?action=decline&token=' . $this->company->saas_decline_token);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
