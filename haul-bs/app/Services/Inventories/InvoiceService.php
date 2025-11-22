<?php

namespace App\Services\Inventories;

use App\Foundations\Modules\Location\Models\State;
use App\Foundations\Traits\TemplateToPdf;
use App\Models\Inventories\Transaction;
use App\Models\Settings\Settings;
use App\Repositories\Settings\SettingRepository;

final readonly class InvoiceService
{
    use TemplateToPdf;

    public function __construct(protected SettingRepository $settingRepository)
    {}

    public function generateInvoicePdf(Transaction $transaction, bool $stream = false): ?string
    {
        return $this->template2pdf(
            'pdf.inventory.transaction_invoice',
            $this->getData($transaction),
            $stream,
            'invoice.pdf'
        );
    }

    public function generatePaymentReceiptPdf(
        Transaction $transaction,
        bool $stream = false
    ): ?string
    {
        return $this->template2pdf(
            'pdf.inventory.transaction_receipt',
            $this->getData($transaction),
            $stream,
            'invoice.pdf'
        );
    }

    private function getData(Transaction $transaction): array
    {
        $settings = $this->settingRepository->getInfo();

        $logo = isset($settings['logo'])
            ? $settings['logo']->getFirstMedia(Settings::LOGO_FIELD)->getFullUrl() ?? null
            : null;
        $partsTotal = round($transaction->price * $transaction->quantity, 2);
        $discountPart = round($partsTotal * $transaction->discount / 100, 2);
        $taxAmount = round(($partsTotal - $discountPart) * $transaction->tax / 100, 2);
        $totalAmount = $partsTotal - $discountPart + $taxAmount;
        $clientName =  $transaction->first_name ? $transaction->first_name . ' ' . $transaction->last_name : '';
        if ($transaction->company_name && $clientName) {
            $clientName = ', ' . $clientName;
        }
        $customerName = ($transaction->company_name ?? '') . $clientName;

        return [
            'logo' => $logo,
            'state' => isset($settings['state_id']->value) ? State::find($settings['state_id']->value)->name : '',
            'settings'  => $settings,
            'transaction' => $transaction,
            'paymentDate' => $transaction->payment_date->format('M j, Y'),
            'invoiceDate' => now()->format('M j, Y'),
            'partsTotal' => $partsTotal,
            'discountPart' => $discountPart,
            'taxAmount' => $taxAmount,
            'totalAmount' => $totalAmount,
            'paymentMethodName' => $transaction->payment_method?->label(),
            'customerName' => $customerName,
        ];
    }
}
