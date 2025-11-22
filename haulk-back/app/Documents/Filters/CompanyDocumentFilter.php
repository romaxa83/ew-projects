<?php

namespace App\Documents\Filters;

use App\Documents\CompanyDocument;
use Illuminate\Support\Str;

class CompanyDocumentFilter extends DocumentFilter
{
    use HasScopeFilter;

    public function companyName(string $companyName): void
    {
        $this
            ->addBoolQuery(
                self::MUST,
                [
                    'wildcard' => [
                        CompanyDocument::companyName() => [
                            'value' => '*' . Str::lower($companyName) . '*',
                        ]
                    ]
                ]
            );
    }

    public function paymentStatus(string $status): void
    {
        if ($status === OrderDocumentFilter::PAYMENT_STATUS_PAID) {
            $this
                ->addBoolQuery(
                    self::MUST,
                    [
                        'term' => [
                            CompanyDocument::isPaid() => true
                        ]
                    ]
                );
            return;
        }
        if ($status === OrderDocumentFilter::PAYMENT_STATUS_NOT_PAID) {
            $this->addBoolQuery(
                self::MUST,
                [
                    'term' => [
                        CompanyDocument::isPaid() => false
                    ]
                ]
            );
        }
    }

    public function referenceNumber(string $referenceNumber): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'wildcard' => [
                    CompanyDocument::referenceNumber() => [
                        'value' => '*' . Str::lower($referenceNumber) . '*',
                    ]
                ]
            ],
        );
    }

    public function invoice(string $invoiceId): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'wildcard' => [
                    CompanyDocument::invoice() => [
                        'value' => '*' . Str::lower($invoiceId) . '*',
                    ]
                ]
            ],
        );
    }

    public function paymentMethodId(int $paymentMethodId): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'term' => [
                    CompanyDocument::paymentMethodId() => $paymentMethodId
                ]
            ]
        );
    }

    public function invoiceSendDate(array $dates): void
    {
        $this
            ->addBoolQuery(
                self::MUST,
                [
                    'range' => [
                        CompanyDocument::invoiceSendDate() => [
                            'gte' => $dates['from']->toIso8601ZuluString(),
                            'lte' => $dates['to']->toIso8601ZuluString()
                        ]
                    ]
                ]
            );
    }
}
