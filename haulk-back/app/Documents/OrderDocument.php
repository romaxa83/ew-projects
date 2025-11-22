<?php

namespace App\Documents;

use App\Documents\Filters\HasFilter;
use App\Documents\Filters\OrderDocumentFilter;
use Illuminate\Support\Carbon;

/**
 * @method static self init()
 *
 * @method static string id()
 * @method static string carrierId()
 * @method static string brokerId()
 * @method static string status()
 * @method static string calculatedStatus()
 * @method static string calculatedStatusWeight()
 * @method static string mobileTab()
 * @method static string loadId()
 * @method static string needReview()
 * @method static string hasReview()
 * @method static string deleted()
 * @method static string driverId()
 * @method static string dispatcherId()
 * @method static string ownerId()
 * @method static string make()
 * @method static string model()
 * @method static string year()
 * @method static string vin()
 * @method static string pickupFullName()
 * @method static string deliveryFullName()
 * @method static string shipperFullName()
 * @method static string tags()
 * @method static string brokerInvoice()
 * @method static string customerInvoice()
 * @method static string calculatedDateFirst()
 * @method static string calculatedDateSecond()
 * @method static string lastPaymentStage()
 * @method static string lastPaymentStageId()
 * @method static string referenceNumber()
 * @method static string brokerReferenceNumber()
 * @method static string brokerAmountForecast()
 * @method static string customerAmountForecast()
 * @method static string brokerFeeAmountForecast()
 * @method static string totalCarrierAmount()
 * @method static string isBrokerPaid()
 * @method static string isCustomerPaid()
 * @method static string isBrokerFeePaid()
 * @method static string isBilled()
 * @method static string brokerPaymentMethodId()
 * @method static string customerPaymentMethodId()
 * @method static string brokerFeePaymentMethodId()
 * @method static string pickupPlannedDate()
 * @method static string deliveryPlannedDate()
 * @method static string pickupDateActual()
 * @method static string deliveryDateActual()
 * @method static string pickupDateActualTz()
 * @method static string deliveryDateActualTz()
 * @method static string customerInvoiceSendDate()
 * @method static string customerPaymentPlannedDate()
 * @method static string brokerInvoiceSendDate()
 * @method static string brokerPaymentPlannedDate()
 * @method static string brokerFeePaymentPlannedDate()
 * @method static string brokerFeePaidAt()
 * @method static string paidAt()
 * @method static string createdAt()
 */
class OrderDocument extends Document
{
    use HasFilter;

    public int $id;
    public ?int $carrierId;
    public ?int $brokerId;
    public int $status;
    public string $calculatedStatus;
    public int $calculatedStatusWeight;
    public ?string $mobileTab;
    public string $loadId;
    public ?bool $needReview;
    public ?bool $hasReview;
    public bool $deleted;
    public ?int $driverId;
    public ?int $dispatcherId;
    public ?int $ownerId;
    public ?array $make;
    public ?array $model;
    public ?array $year;
    public ?array $vin;
    public ?string $pickupFullName;
    public ?string $deliveryFullName;
    public ?string $shipperFullName;
    public ?array $tags;
    public ?string $brokerInvoice;
    public ?string $customerInvoice;
    public int $calculatedDateFirst;
    public int $calculatedDateSecond;
    public ?int $lastPaymentStage;
    public ?int $lastPaymentStageId;
    public ?array $referenceNumber;
    public ?array $brokerReferenceNumber;
    public ?float $brokerAmountForecast;
    public ?float $customerAmountForecast;
    public ?float $brokerFeeAmountForecast;
    public ?float $totalCarrierAmount;
    public ?bool $isBrokerPaid;
    public ?bool $isCustomerPaid;
    public ?bool $isBrokerFeePaid;
    public ?bool $isBilled;
    public ?int $brokerPaymentMethodId;
    public ?int $customerPaymentMethodId;
    public ?int $brokerFeePaymentMethodId;
    /**
     * @es-date-time
     */
    public ?Carbon $pickupPlannedDate;
    /**
     * @es-date-time
     */
    public ?Carbon $deliveryPlannedDate;
    /**
     * @es-date-time
     */
    public ?Carbon $pickupDateActual;
    /**
     * @es-date-time
     */
    public ?Carbon $pickupDateActualTz;
    /**
     * @es-date-time
     */
    public ?Carbon $deliveryDateActual;
    /**
     * @es-date-time
     */
    public ?Carbon $deliveryDateActualTz;
    /**
     * @es-date-time
     */
    public ?Carbon $customerInvoiceSendDate;
    /**
     * @es-date-time
     */
    public ?Carbon $customerPaymentPlannedDate;
    /**
     * @es-date-time
     */
    public ?Carbon $brokerInvoiceSendDate;
    /**
     * @es-date-time
     */
    public ?Carbon $brokerPaymentPlannedDate;
    /**
     * @es-date-time
     */
    public ?Carbon $brokerFeePaymentPlannedDate;
    /**
     * @es-date-time
     */
    public ?Carbon $paidAt;
    /**
     * @es-date-time
     */
    public ?Carbon $brokerFeePaidAt;
    /**
     * @es-date-time
     */
    public Carbon $createdAt;

    public function filterClass(): string
    {
        return OrderDocumentFilter::class;
    }

    public function sortBySearch(): self
    {
        return $this->sort('_score', 'desc');
    }

    public function sortByTotalDue(string $direction): self
    {
        $this->sort[] = [
            '_script' => [
                'type' => 'number',
                'script' => [
                    'lang' => 'painless',
                    'source' => "
                        if (doc['" . self::brokerAmountForecast() . "'].size() == 0 || doc['" . self::isBrokerPaid() . "'].size() == 0) {
                            return " . ($direction === 'asc' ? '1000000000' : '-1') . ";
                        }
                        if (doc['" . self::isBrokerPaid() . "'].value === true) {
                            return " . ($direction === 'asc' ? '1000000000' : '-1') . ";
                        }
                        return doc['" . self::brokerAmountForecast() . "'].value;",
                ],
                'order' => $direction,
            ]
        ];
        return $this;
    }

    public function sortByPastDue(string $direction): self
    {
        $time = Carbon::now('UTC')->toIso8601ZuluString();
        $this->sort[] = [
            '_script' => [
                'type' => 'number',
                'script' => [
                    'lang' => 'painless',
                    'source' => "
                        if (doc['" . self::brokerAmountForecast() . "'].size() == 0 || doc['" . self::isBrokerPaid() . "'].size() == 0 || doc['" . self::brokerPaymentPlannedDate() . "'].size() == 0) {
                            return " . ($direction === 'asc' ? '1000000000' : '-1') . ";
                        }
                        if (doc['" . self::brokerPaymentPlannedDate() . "'].size() > 0 && doc['" . self::brokerPaymentPlannedDate() . "'].value != null  ) {
                            def plannedDate = doc['broker_payment_planned_date'].value;
                            if(plannedDate instanceof String) {
                                plannedDate = ZonedDateTime.parse(plannedDate, DateTimeFormatter.ISO_DATE_TIME);
                            }
                            def targetDate = ZonedDateTime.parse('".$time."', DateTimeFormatter.ISO_DATE_TIME);
                            if(plannedDate != null && targetDate != null && plannedDate.isAfter(targetDate)) {
                                return " . ($direction === 'asc' ? '1000000000' : '-1') . ";
                            }
                        }
                        if (doc['" . self::isBrokerPaid() . "'].value === true) {
                            return " . ($direction === 'asc' ? '1000000000' : '-1') . ";
                        }
                        return doc['" . self::brokerAmountForecast() . "'].value;",
                ],
                'order' => $direction,
            ]
        ];
        return $this;
    }

    public function sortByCurrentDue(string $direction): self
    {
        $time = Carbon::now('UTC')->toIso8601ZuluString();
        $this->sort[] = [
            '_script' => [
                'type' => 'number',
                'script' => [
                    'lang' => 'painless',
                    'source' => "
                        if (doc['" . self::brokerAmountForecast() . "'].size() == 0 || doc['" . self::isBrokerPaid() . "'].size() == 0 || doc['" . self::brokerPaymentPlannedDate() . "'].size() == 0) {
                            return " . ($direction === 'asc' ? '1000000000' : '-1') . ";
                        }
                        if (doc['" . self::isBrokerPaid() . "'].value === true) {
                            return " . ($direction === 'asc' ? '1000000000' : '-1') . ";
                        }
                        if (doc['" . self::brokerPaymentPlannedDate() . "'].size() > 0 && doc['" . self::brokerPaymentPlannedDate() . "'].value != null  ) {
                            def plannedDate = doc['broker_payment_planned_date'].value;
                            if(plannedDate instanceof String) {
                                plannedDate = ZonedDateTime.parse(plannedDate, DateTimeFormatter.ISO_DATE_TIME);
                            }
                            def targetDate = ZonedDateTime.parse('".$time."', DateTimeFormatter.ISO_DATE_TIME);
                            if(plannedDate != null && targetDate != null && plannedDate.isBefore(targetDate)) {
                                return " . ($direction === 'asc' ? '1000000000' : '-1') . ";
                            }
                        }
                        return doc['" . self::brokerAmountForecast() . "'].value;",
                ],
                'order' => $direction,
            ]
        ];

        return $this;
    }
}
