<?php

namespace App\Documents;

use App\Documents\Filters\CompanyDocumentFilter;
use App\Documents\Filters\HasFilter;

/**
 * @method static self init()
 *
 * @method static string id()
 * @method static string carrierId()
 * @method static string brokerId()
 * @method static string companyName()
 * @method static string lastPaymentStageId()
 * @method static string lastPaymentStage()
 * @method static string orderCount()
 * @method static string totalDueCount()
 * @method static string totalDue()
 * @method static string referenceNumber()
 * @method static string invoice()
 * @method static string invoiceSendDate()
 * @method static string paymentMethodId()
 * @method static string brokerAmount()
 * @method static string isPaid()
 */
class CompanyDocument extends Document
{
    use HasFilter;

    public string $id;
    public ?int $carrierId;
    public ?int $brokerId;
    public string $companyName;
    public ?int $lastPaymentStageId;
    public ?int $lastPaymentStage;
    public int $orderCount;
    public int $totalDueCount;
    public ?float $totalDue;
    public ?array $isPaid;
    public ?array $referenceNumber;
    public ?array $invoice;
    public ?array $paymentMethodId;
    public ?array $invoiceSendDate;
    public ?array $brokerAmount;

    public function filterClass(): string
    {
        return CompanyDocumentFilter::class;
    }
}
