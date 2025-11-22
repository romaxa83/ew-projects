<?php

namespace App\Dto\Orders\BS;

use Carbon\Carbon;

class SendDocsDto
{
    public const EMAILS = 'recipient_email';

    private array $emails;

    private bool $sendInvoice = false;

    private ?Carbon $invoiceDate = null;

    public static function create(): SendDocsDto
    {
        return new self();
    }

    public function origin(array $params): SendDocsDto
    {
        if (in_array('invoice', $params['content'], true)) {
            $this->sendInvoice = true;
            $invoiceDate = $params['invoice_date'] ?? null;
            $this->invoiceDate = $invoiceDate ? from_bs_timezone('m/d/Y', $invoiceDate) : now();
        }

        $this->emails = array_filter($params[self::EMAILS]);

        return $this;
    }

    public function getInvoiceDate(): Carbon
    {
        return $this->invoiceDate;
    }

    public function emails(): array
    {
        return $this->emails;
    }

    public function isSendInvoice(): bool
    {
        return $this->sendInvoice;
    }
}
