<?php

namespace App\Notifications\Orders;

use App\Models\Orders\OrderSignature;
use App\Services\Logs\DeliveryLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendSignatureLink extends Notification
{
    use Queueable;

    public OrderSignature $signature;

    public function __construct(OrderSignature $signature)
    {
        $this->signature = $signature;
    }

    public function via($notifiable): array
    {
        return [
            'mail',
            'fax',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Signature link for Load id: " . $this->signature->order->load_id)
            ->markdown(
                'mail.sign-order',
                [
                    'url' => config('frontend.url') . '/online-bol/' . $this->signature->signature_token,
                ]
            );

        resolve(DeliveryLogService::class)
            ->enableEmailTracking($mail, $this->signature->signature_token, DeliveryLogService::EMAIL_SIGNATURE_LINK_TYPE);

        return $mail;
    }

}
