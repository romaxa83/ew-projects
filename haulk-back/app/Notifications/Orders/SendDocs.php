<?php

namespace App\Notifications\Orders;

use App\Models\Orders\Order;
use App\Models\Saas\Company\Company;
use App\Notifications\Messages\FaxMessage;
use App\Services\Logs\DeliveryLogService;
use App\Services\Fax\Handlers\SendDocsStatusHandler;
use App\Services\Fax\Handlers\StatusHandler;
use App\Services\Fax\StatusHandleable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class SendDocs extends Notification implements StatusHandleable
{
    use Queueable;

    public const ATTACHMENT_BROKER_INVOICE = 'invoice_broker';
    public const ATTACHMENT_CUSTOMER_INVOICE = 'invoice_customer';
    public const ATTACHMENT_BOL = 'bol';
    public const ATTACHMENT_W9 = 'w9';

    public const ATTACHMENTS_NAME = [
        self::ATTACHMENT_BROKER_INVOICE => 'broker invoice',
        self::ATTACHMENT_CUSTOMER_INVOICE => 'customer invoice',
        self::ATTACHMENT_BOL => 'BOL',
        self::ATTACHMENT_W9 => 'W9'
    ];

    public Order $order;

    private array $attachments;
    private string $attachmentsStringList;
    private int $countAttachments;

    private ?Company $company;

    public function __construct(Order $order, array $attachments)
    {
        $this->order = $order;
        $this->attachments = $attachments;
        $this->countAttachments = count($attachments);

        $this->createAttachmentsStringList();

        $this->company = $order->user->getCompany();
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
            'fax',
        ];
    }

    public function toMail(): MailMessage
    {
        $subject = $this->attachmentsStringList . " " .
            ($this->company ? 'from ' . $this->company->getCompanyName() : '') .
            " for Load id: " . $this->order->load_id;

        $mail = (new MailMessage)
            ->greeting(
                "Hello, " . (
                    $this->order->shipper_contact['full_name'] ?? ''
                )
            )
            ->subject($subject)
            ->line($subject. '.')
            ->line('You will find PDF ' .Str::plural('file', $this->countAttachments). ' with the ' . $this->attachmentsStringList .' attached to this email.');

        if (!empty($this->attachments[self::ATTACHMENT_BOL])) {
            $mail->action('Online BOL', config('frontend.url') . '/online-bol/' . $this->order->public_token);
        }

        $mail = $this->addAttachments($mail);

        if ($this->company) {
            if ($this->company->getBillingEmail()) {
                $mail->replyTo($this->company->getBillingEmail());
            }

            $mail->viewData = [
                'companyName' => $this->company->getCompanyName(),
                'companyContactString' => $this->company->getMailContactString(),
            ];
        }

        $this->enableTracking($mail);

        return $mail;
    }

    public function toFax(): FaxMessage
    {
        $fax = (new FaxMessage)
            ->setOrder($this->order)
            ->setFrom(config('fax.contacts.from'));

        return $this->addAttachments($fax);
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

    public function getStatusHandler(): StatusHandler
    {
        return new SendDocsStatusHandler($this);
    }

    private function enableTracking(MailMessage $mail): void
    {
        resolve(DeliveryLogService::class)
            ->enableEmailTracking(
                $mail,
                $this->order->public_token,
                implode(
                    ',',
                    $this->attachments()
                )
            );
    }

    public function attachments(): array
    {
        return array_keys($this->attachments);
    }
}
