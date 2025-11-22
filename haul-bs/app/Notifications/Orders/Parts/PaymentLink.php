<?php

namespace App\Notifications\Orders\Parts;

use App\Foundations\Enums\LogKeyEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentLink extends Notification
{
    use Queueable;

    public function __construct(
        public ?string $name,
        public ?string $link
    )
    {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->from(config('mail.from.address'), 'Haulk-Payments')
            ->subject('Payment link')
            ->greeting("Hello {$this->name} !")
            ->line('Thank you for your order.')
            ->line('You can see payment link below this message. ')
            ->action('Press Here to submit Payment', $this->link)
            ->line('If you encounter any issues while submitting a payment through the provided link, please don’t hesitate to reach out to us by phone at 1-312-800-0888 or via email at sales@haulkdepot.com.');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
