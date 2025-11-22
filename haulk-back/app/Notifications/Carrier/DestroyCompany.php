<?php

namespace App\Notifications\Carrier;

use App\Models\Saas\Company\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DestroyCompany extends Notification
{
    use Queueable;

    const CONFIRM_URL = '/account-deleting';

    private Company $company;

    /**
     * Create a new notification instance.
     * @param string $confirmToken
     * @param string $declineToken
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
            ->subject('Confirm deletion of your company')
            ->markdown(
                'mail.crm-destroy-company',
                [
                    'line1' => 'To confirm the deletion of '. $this->company->name .' company and all its data, follow the link in the letter.',
                    'line2' => 'If you did not ask for company deletion actions, please ignore this message. Or click "Decline action".',
                    'action1' => [
                        'text' => 'Confirm action',
                        'url' => config('frontend.url') . self::CONFIRM_URL . '?type=confirm&token=' . $this->company->crm_confirm_token
                    ],
                    'action2' => [
                        'text' => 'Decline action',
                        'url' => config('frontend.url') . self::CONFIRM_URL . '?type=decline&token=' . $this->company->crm_decline_token
                    ]
                ]
            );
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
