<?php

namespace App\Dto\Orders;

use Spatie\MediaLibrary\Models\Media;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SendDocsDto
{
    public const EMAILS = 'recipient_email';

    public const FAX = 'recipient_fax';

    private Collection $orders;

    private array $emails;
    private string $fax;

    private bool $sendToEmail = false;
    private bool $sendToFax = false;

    private bool $sendInvoice = false;
    private bool $sendBol = false;
    private bool $sendW9 = false;

    private Media $w9;

    private function __construct()
    {
        $this->orders = collect();
    }

    public static function create(): SendDocsDto
    {
        return new self();
    }

    public function origin(array $params): SendDocsDto
    {
        if (in_array('invoice', $params['content'], true)) {
            $this->sendInvoice = true;
        }

        if (in_array('bol', $params['content'], true)) {
            $this->sendBol = true;
        }

        if (in_array('w9', $params['content'], true)) {
            $this->sendW9 = true;

            $this->w9 = $params['w9'];
        }

        foreach ($params['orders'] as $order) {
            $this->orders->push([
                'id' => $order['id'],
                'invoice' => $this->sendInvoice ? [
                    'id' => $order['invoice_id'],
                    'date' => Carbon::createFromFormat('m/d/Y', $order['invoice_date']),
                    'recipient' => $params['invoice_recipient']
                ] : null,
                'show_shipper_info' => $order['show_shipper_info']
            ]);
        }

        if (in_array('email', $params['send_via'], true)) {
            $this->sendToEmail = true;

            $this->emails = array_filter(
                Arr::pluck($params[self::EMAILS], 'value')
            );
        }

        if (in_array('fax', $params['send_via'], true)) {
            $this->sendToFax = true;

            $this->fax = $params[self::FAX];
        }

        return $this;
    }

    public function mobileOrigin(array $params, Order $order): SendDocsDto
    {

        $content = $params['content'];

        if ($content === 'both') {
            $this->sendInvoice = $this->sendBol = true;
        } elseif ($content === 'bol') {
            $this->sendBol = true;
        } else {
            $this->sendInvoice = true;
        }

        $this->orders->push([
            'id' => $order->id,
            'invoice' => $this->sendInvoice ? [
                'id' => $order->load_id,
                'date' => Carbon::now(),
                'recipient' => Payment::PAYER_CUSTOMER
            ] : null,
            'show_shipper_info' => false
        ]);

        $this->sendToEmail = true;

        $this->emails = [
            $params['recipient_email']
        ];

        return $this;
    }

    public function autoInvoice(Order $order, string $recipient): SendDocsDto
    {
        $this->sendInvoice = true;

        $this->orders->push([
            'id' => $order->id,
            'invoice' => [
                'id' => $order->load_id,
                'recipient' => $recipient,
                'date' => $order->payment->invoice_issue_date ?? now(),
                'show_shipper_info' => false
            ]
        ]);

        $this->sendToEmail = true;

        if ($recipient === Payment::PAYER_BROKER) {
            $this->emails = [$order->shipper_contact['email']];

            $settings = $order->user->getCompany()->notificationSettings->toArray();

            if (!empty($settings['receive_bol_copy_emails']) && is_array($settings['receive_bol_copy_emails'])) {
                foreach ($settings['receive_bol_copy_emails'] as $email) {
                    $this->emails[] = $email['value'];
                }
            }

            return $this;
        }

        $location = $order->payment->customer_payment_location;

        $this->emails = [$order->{$location . '_contact'}['email']];

        return $this;
    }

    public function isSendToEmail(): bool
    {
        return $this->sendToEmail;
    }

    public function isSendToFax(): bool
    {
        return $this->sendToFax;
    }

    public function emails(): array
    {
        return $this->emails;
    }

    public function fax(): string
    {
        return $this->fax;
    }

    public function orders(): Collection
    {
        return $this->orders;
    }

    public function w9(): Media
    {
        return $this->w9;
    }

    public function isSendInvoice(): bool
    {
        return $this->sendInvoice;
    }

    public function isSendBol(): bool
    {
        return $this->sendBol;
    }

    public function isSendW9(): bool
    {
        return $this->sendW9;
    }
}
