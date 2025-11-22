<?php

namespace App\Notifications\BodyShop\Orders;

use App\Models\BodyShop\Orders\Order;
use App\Models\BodyShop\Settings\Settings;
use App\Notifications\Messages\FaxMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class SendDocs extends Notification
{
    use Queueable;
    public const ATTACHMENT_CUSTOMER_INVOICE = 'invoice_customer';

    public const ATTACHMENTS_NAME = [
        self::ATTACHMENT_CUSTOMER_INVOICE => 'customer invoice',
    ];

    public Order $order;

    private array $attachments;
    private string $attachmentsStringList;
    private int $countAttachments;

    public function __construct(Order $order, array $attachments)
    {
        $this->order = $order;
        $this->attachments = $attachments;
        $this->countAttachments = count($attachments);

        $this->createAttachmentsStringList();
    }

    private function createAttachmentsStringList(): void
    {
        $attachments = collect(array_keys($this->attachments));

        $this->attachmentsStringList = ucfirst(self::ATTACHMENTS_NAME[$attachments[0]]);

        $attachments->forget(0);

        $last = $attachments->last();

        if (!$last) {
            return;
        }
        $attachments = $attachments->slice(0, -1);
        $attachments->map(function ($item) {
            $this->attachmentsStringList .= ', ' . self::ATTACHMENTS_NAME[$item];
        });

        $this->attachmentsStringList .= ' and ' . self::ATTACHMENTS_NAME[$last];
    }

    public function via(): array
    {
        return [
            'mail',
        ];
    }

    public function toMail(): MailMessage
    {
        $companyName = Settings::getParam('company_name');
        $subject = $this->attachmentsStringList . " " .
            ($companyName ? 'from ' . $companyName : '') .
            " for Order id: " . $this->order->order_number;

        $mail = (new MailMessage)
            ->greeting(
                "Hello, " . $this->order->vehicle->getOwnerFullName()
            )
            ->subject($subject)
            ->line($subject. '.')
            ->line('You will find PDF ' .Str::plural('file', $this->countAttachments). ' with the ' . $this->attachmentsStringList .' attached to this email.');

        $mail = $this->addAttachments($mail);

        if ($companyName) {
            $billingEmail = Settings::getParam('billing_email');
            if ($billingEmail) {
                $mail->replyTo($billingEmail);
            }

            $mail->viewData = [
                'companyName' => $companyName,
                'companyContactString' => sprintf(
                    'contact %s or email us at %s',
                    Settings::getParam('phone') ?? '',
                    Settings::getParam('email') ?? ''
                ),
            ];
        }

        return $mail;
    }

    /**
     * @param FaxMessage|MailMessage $message
     * @return FaxMessage|MailMessage
     */
    private function addAttachments($message)
    {
        foreach ($this->attachments as $attachment) {
            $message->attachData(
                $attachment['data'],
                $attachment['name'],
                !empty($attachment['options']) ? $attachment['options'] : ['mime' => 'application/pdf']
            );
        }

        return $message;
    }

    public function attachments(): array
    {
        return array_keys($this->attachments);
    }
}
