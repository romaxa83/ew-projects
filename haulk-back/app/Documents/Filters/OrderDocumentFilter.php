<?php

namespace App\Documents\Filters;

use App\Documents\OrderDocument;
use App\Models\Orders\Order;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OrderDocumentFilter extends DocumentFilter
{
    use HasScopeFilter;

    public const ATTRIBUTE_OVERDUE = 'overdue';
    public const ATTRIBUTE_NOT_OVERDUE = 'not_overdue';
    public const ATTRIBUTE_BILLED = 'billed';
    public const ATTRIBUTE_NOT_BILLED = 'not_billed';
    public const ATTRIBUTE_PAID = 'paid';
    public const ATTRIBUTE_NOT_PAID = 'not_paid';
    public const ATTRIBUTE_REVIEWED = 'reviewed';
    public const ATTRIBUTE_NOT_REVIEWED = 'not_reviewed';
    public const ATTRIBUTE_BROKER_FEE_PAID = 'broker_fee_paid';
    public const ATTRIBUTE_BROKER_FEE_NOT_PAID = 'broker_fee_not_paid';

    public const ATTRIBUTES = [
        self::ATTRIBUTE_OVERDUE,
        self::ATTRIBUTE_NOT_OVERDUE,
        self::ATTRIBUTE_BILLED,
        self::ATTRIBUTE_NOT_BILLED,
        self::ATTRIBUTE_PAID,
        self::ATTRIBUTE_NOT_PAID,
        self::ATTRIBUTE_REVIEWED,
        self::ATTRIBUTE_NOT_REVIEWED,
        self::ATTRIBUTE_BROKER_FEE_PAID,
        self::ATTRIBUTE_BROKER_FEE_NOT_PAID,
    ];

    public const PAYMENT_STATUS_PAID = self::ATTRIBUTE_PAID;
    public const PAYMENT_STATUS_NOT_PAID = self::ATTRIBUTE_NOT_PAID;
    public const PAYMENT_STATUS_ALL = 'all';

    public const PAYMENT_STATUSES = [
        self::PAYMENT_STATUS_PAID,
        self::PAYMENT_STATUS_NOT_PAID,
        self::PAYMENT_STATUS_ALL
    ];

    public const DASHBOARD_TODAY_DELIVERED_ORDERS = 'today_delivered_orders';
    public const DASHBOARD_TODAY_PAID_ORDERS = 'today_paid_orders';
    public const DASHBOARD_PICKUP_OVERDUE_ORDERS = 'pickup_overdue_orders';
    public const DASHBOARD_DELIVERY_OVERDUE_ORDERS = 'delivery_overdue_orders';
    public const DASHBOARD_TODAY_PICKUP_ORDERS = 'today_pickup_orders';
    public const DASHBOARD_TODAY_DELIVERY_ORDERS = 'today_delivery_orders';
    public const DASHBOARD_PAYMENT_OVERDUE_ORDERS = 'payment_overdue_orders';
    public const DASHBOARD_MONTH_PAID_ORDERS = 'month_paid_orders';

    public const DASHBOARD = [
        self::DASHBOARD_TODAY_DELIVERED_ORDERS,
        self::DASHBOARD_TODAY_PAID_ORDERS,
        self::DASHBOARD_PICKUP_OVERDUE_ORDERS,
        self::DASHBOARD_DELIVERY_OVERDUE_ORDERS,
        self::DASHBOARD_TODAY_PICKUP_ORDERS,
        self::DASHBOARD_TODAY_DELIVERY_ORDERS,
        self::DASHBOARD_PAYMENT_OVERDUE_ORDERS,
        self::DASHBOARD_MONTH_PAID_ORDERS,
    ];

    public function withoutDeleted(bool $withoutDeleted = true): void
    {
        if ($withoutDeleted) {
            $this->addBoolQuery(
                self::MUST,
                [
                    'term' => [
                        OrderDocument::deleted() => false
                    ]
                ]
            );
        }
    }

    public function companyName(string $companyName): void
    {
        $this
            ->addBoolQuery(
                self::MUST,
                [
                    'wildcard' => [
                        OrderDocument::shipperFullName() => [
                            'value' => '*' . Str::lower($companyName) . '*',
                        ]
                    ]
                ]
            );
    }

    public function state(array $state): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'terms' => [
                    OrderDocument::calculatedStatus() => $state,
                ]
            ]
        );
    }

    public function hasBrokerFee(bool $has): void
    {
        $this->addBoolQuery(
            $has ? self::MUST : self::MUST_NOT,
            [
                'exists' => [
                    'field' => OrderDocument::isBrokerFeePaid(),
                ]
            ]
        );
    }

    public function driverId(int $driverId): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'term' => [
                    OrderDocument::driverId() => $driverId
                ]
            ]
        );
    }

    public function dispatcherExists(bool $exists): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'exists' => [
                    'field' => OrderDocument::dispatcherId(),
                ]
            ]
        );
    }

    public function dispatcherId(int $dispatcherId): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'term' => [
                    OrderDocument::dispatcherId() => $dispatcherId
                ]
            ]
        );
    }

    public function make(string $make): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'wildcard' => [
                    OrderDocument::make() => '*' . Str::lower($make) . '*'
                ]
            ]
        );
    }

    public function model(string $model): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'wildcard' => [
                    OrderDocument::model() => '*' . Str::lower($model) . '*'
                ]
            ]
        );
    }

    public function year(string $year): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'wildcard' => [
                    OrderDocument::year() => '*' . $year . '*'
                ]
            ]
        );
    }

    public function brokerCheckId(string $brokerCheckId): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'wildcard' => [
                    OrderDocument::brokerReferenceNumber() => [
                        'value' => '*' . Str::lower($brokerCheckId) . '*',
                    ]
                ]
            ],
        );
    }

    public function checkId(string $checkId): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'wildcard' => [
                    OrderDocument::referenceNumber() => [
                        'value' => '*' . Str::lower($checkId) . '*',
                    ]
                ]
            ],
        );
    }

    public function brokerInvoiceId(string $brokerInvoiceId): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'wildcard' => [
                    OrderDocument::brokerInvoice() => [
                        'value' => '*' . Str::lower($brokerInvoiceId) . '*',
                    ]
                ]
            ],
        );
    }

    public function invoiceId(string $invoiceId): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'bool' => [
                    self::SHOULD => [
                        [
                            'wildcard' => [
                                OrderDocument::brokerInvoice() => [
                                    'value' => '*' . Str::lower($invoiceId) . '*',
                                ]
                            ]
                        ],
                        [
                            'wildcard' => [
                                OrderDocument::customerInvoice() => [
                                    'value' => '*' . Str::lower($invoiceId) . '*',
                                ],
                            ]
                        ],
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
                'bool' => [
                    self::SHOULD => [
                        [
                            'term' => [
                                OrderDocument::brokerPaymentMethodId() => $paymentMethodId
                            ]
                        ],
                        [
                            'term' => [
                                OrderDocument::customerPaymentMethodId() => $paymentMethodId
                            ]
                        ],
                    ]
                ]
            ],
        );
    }

    public function brokerPaymentMethodId(int $brokerPaymentMethodId): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'term' => [
                    OrderDocument::brokerPaymentMethodId() => $brokerPaymentMethodId
                ]
            ]
        );
    }

    public function s(string $s): void
    {
        $s = Str::lower($s);
        $this->addBoolQuery(
            self::MUST,
            [
                'bool' => [
                    self::SHOULD => [
                        [
                            'wildcard' => [
                                OrderDocument::loadId() => [
                                    'value' => $s . '*',
                                    'boost' => 81920.0
                                ],
                            ]
                        ],
                        [
                            'wildcard' => [
                                OrderDocument::loadId() => [
                                    'value' => '*' . $s . '*',
                                    'boost' => 40960.0
                                ],
                            ]
                        ],
                        [
                            'wildcard' => [
                                OrderDocument::vin() => [
                                    'value' => $s . '*',
                                    'boost' => 20480.0
                                ]
                            ]
                        ],
                        [
                            'wildcard' => [
                                OrderDocument::vin() => [
                                    'value' => '*' . $s . '*',
                                    'boost' => 10240.0
                                ]
                            ]
                        ],
                        [
                            'wildcard' => [
                                OrderDocument::pickupFullName() => [
                                    'value' => $s . '*',
                                    'boost' => 5120.0
                                ]
                            ]
                        ],
                        [
                            'wildcard' => [
                                OrderDocument::pickupFullName() => [
                                    'value' => '*' . $s . '*',
                                    'boost' => 2560.0
                                ]
                            ]
                        ],
                        [
                            'wildcard' => [
                                OrderDocument::deliveryFullName() => [
                                    'value' => $s . '*',
                                    'boost' => 1280.0
                                ]
                            ]
                        ],
                        [
                            'wildcard' => [
                                OrderDocument::deliveryFullName() => [
                                    'value' => '*' . $s . '*',
                                    'boost' => 640.0
                                ]
                            ]
                        ],
                        [
                            'wildcard' => [
                                OrderDocument::shipperFullName() => [
                                    'value' => $s . '*',
                                    'boost' => 320.0
                                ]
                            ]
                        ],
                        [
                            'wildcard' => [
                                OrderDocument::shipperFullName() => [
                                    'value' => '*' . $s . '*',
                                    'boost' => 160.0
                                ]
                            ]
                        ],
                        [
                            'wildcard' => [
                                OrderDocument::brokerInvoice() => [
                                    'value' => $s . '*',
                                    'boost' => 80.0
                                ]
                            ]
                        ],
                        [
                            'wildcard' => [
                                OrderDocument::brokerInvoice() => [
                                    'value' => '*' . $s . '*',
                                    'boost' => 40.0
                                ]
                            ]
                        ],
                        [
                            'wildcard' => [
                                OrderDocument::customerInvoice() => [
                                    'value' => $s . '*',
                                    'boost' => 20.0
                                ],
                            ]
                        ],
                        [
                            'wildcard' => [
                                OrderDocument::customerInvoice() => [
                                    'value' => '*' . $s . '*',
                                    'boost' => 10.0
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        );
    }

    public function loadId(string $loadId): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'wildcard' => [
                    OrderDocument::loadId() => '*' . Str::lower($loadId) . '*'
                ]
            ]
        );
    }

    public function attributes(array $attributes): void
    {
        foreach ($attributes as $attribute) {
            switch ($attribute) {
                case self::ATTRIBUTE_BROKER_FEE_PAID:
                    $this->brokerFeePaid();
                    break;
                case self::ATTRIBUTE_BROKER_FEE_NOT_PAID:
                    $this->brokerFeePaid(false);
                    break;
                case self::ATTRIBUTE_PAID:
                    $this->paid();
                    break;
                case self::ATTRIBUTE_NOT_PAID:
                    $this->paid(false);
                    break;
                case self::ATTRIBUTE_NOT_REVIEWED:
                    $this->hasReview(false);
                    break;
                case self::ATTRIBUTE_REVIEWED:
                    $this->hasReview();
                    break;
                case self::ATTRIBUTE_BILLED:
                    $this->billed();
                    break;
                case self::ATTRIBUTE_NOT_BILLED:
                    $this->billed(false);
                    break;
                case self::ATTRIBUTE_OVERDUE:
                    $this->overdue();
                    break;
                case self::ATTRIBUTE_NOT_OVERDUE:
                    $this->overdue(false);
                    break;
            }
        }
    }

    public function brokerFeePaid(bool $paid = true): void
    {
        $this
            ->addBoolQuery(
                self::MUST,
                [
                    'term' => [
                        OrderDocument::isBrokerFeePaid() => $paid
                    ]
                ]
            );
    }

    public function paid(bool $paid = true): void
    {
        if ($paid) {
            $this
                ->addBoolQuery(
                    self::MUST,
                    [
                        'bool' => [
                            self::SHOULD => [
                                [
                                    'term' => [
                                        OrderDocument::isBrokerPaid() => $paid
                                    ],
                                ],
                                [
                                    'bool' => [
                                        self::MUST_NOT => [
                                            'exists' => [
                                                'field' => OrderDocument::isBrokerPaid(),
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                )
                ->addBoolQuery(
                    self::MUST,
                    [
                        'bool' => [
                            self::SHOULD => [
                                [
                                    'term' => [
                                        OrderDocument::isCustomerPaid() => $paid
                                    ],
                                ],
                                [
                                    'bool' => [
                                        self::MUST_NOT => [
                                            'exists' => [
                                                'field' => OrderDocument::isCustomerPaid(),
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                );
            return;
        }
        $this->addBoolQuery(
            self::MUST,
            [
                'bool' => [
                    self::SHOULD => [
                        [
                            'term' => [
                                OrderDocument::isBrokerPaid() => $paid
                            ]
                        ],
                        [
                            'term' => [
                                OrderDocument::isCustomerPaid() => $paid
                            ]
                        ],
                    ]
                ]
            ]
        );
    }

    public function hasReview(bool $hasReview = true): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'term' => [
                    OrderDocument::needReview() => true,
                ]
            ]
        );
        $this->addBoolQuery(
            self::MUST,
            [
                'term' => [
                    OrderDocument::hasReview() => $hasReview
                ]
            ]
        );
    }

    public function billed(bool $billed = true): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'term' => [
                    OrderDocument::isBilled() => $billed
                ]
            ]
        );
        $this->paid(false);
    }

    public function overdue(bool $overdue = true): void
    {
        if ($overdue) {
            $this->addBoolQuery(
                self::MUST,
                [
                    'bool' => [
                        self::SHOULD => [
                            [
                                'range' => [
                                    OrderDocument::pickupPlannedDate() => [
                                        'lt' => 'now'
                                    ]
                                ]
                            ],
                            [
                                'range' => [
                                    OrderDocument::deliveryPlannedDate() => [
                                        'lt' => 'now'
                                    ]
                                ]
                            ],
                            [
                                'range' => [
                                    OrderDocument::customerPaymentPlannedDate() => [
                                        'lt' => 'now'
                                    ]
                                ]
                            ],
                            [
                                'range' => [
                                    OrderDocument::brokerPaymentPlannedDate() => [
                                        'lt' => 'now'
                                    ]
                                ]
                            ],
                        ]
                    ]
                ]
            );
            return;
        }
        $this
            ->addBoolQuery(
                self::MUST_NOT,
                [
                    'range' => [
                        OrderDocument::pickupPlannedDate() => [
                            'lt' => 'now'
                        ]
                    ]
                ],
            )
            ->addBoolQuery(
                self::MUST_NOT,
                [
                    'range' => [
                        OrderDocument::deliveryPlannedDate() => [
                            'lt' => 'now'
                        ]
                    ]
                ],
            )
            ->addBoolQuery(
                self::MUST_NOT,
                [
                    'range' => [
                        OrderDocument::customerPaymentPlannedDate() => [
                            'lt' => 'now'
                        ]
                    ]
                ],
            )
            ->addBoolQuery(
                self::MUST_NOT,
                [
                    'range' => [
                        OrderDocument::brokerPaymentPlannedDate() => [
                            'lt' => 'now'
                        ]
                    ]
                ],
            );
    }

    public function destinationDate(array $destination): void
    {
        $field = $destination['location'] === Order::LOCATION_PICKUP
            ? OrderDocument::pickupDateActual()
            : OrderDocument::deliveryDateActual()
        ;

//        $field = $destination['location'] === Order::LOCATION_PICKUP
//            ? OrderDocument::pickupDateActualTz()
//            : OrderDocument::deliveryDateActualTz()
//        ;

        $range = $destination['dates'];
        $this->dateBetween(
            $field,
            $range['from'],
            $range['to']
        );
    }

    private function dateBetween(string $field, Carbon $from, Carbon $to): void
    {
//        logger_info('DOCUMENT FILTER dateBetween - 35 ', [
//            'field' => $field,
//            'from' => $from,
//            'to' => $to,
//        ]);
        $this
            ->addBoolQuery(
                self::MUST,
                [
                    'range' => [
                        $field => [
                            'lte' => $to->toIso8601ZuluString(),
                            'gte' => $from->toIso8601ZuluString()
                        ]
                    ]
                ]
            );
    }

    public function createdAtDate(array $dates): void
    {
        $this->dateBetween(
            OrderDocument::createdAt(),
            $dates['from'],
            $dates['to']
        );
    }

    public function invoiceSendDate(array $dates): void
    {
        $this
            ->addBoolQuery(
                self::MUST,
                [
                    'bool' => [
                        self::SHOULD => [
                            [
                                'range' => [
                                    OrderDocument::brokerInvoiceSendDate() => [
                                        'lte' => $dates['to']->toIso8601ZuluString(),
                                        'gte' => $dates['from']->toIso8601ZuluString()
                                    ]
                                ]
                            ],
                            [
                                'range' => [
                                    OrderDocument::customerInvoiceSendDate() => [
                                        'lte' => $dates['to']->toIso8601ZuluString(),
                                        'gte' => $dates['from']->toIso8601ZuluString()
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            );
    }

    public function brokerInvoiceSendDate(array $dates): void
    {
        $this->dateBetween(
            OrderDocument::brokerInvoiceSendDate(),
            $dates['from'],
            $dates['to']
        );
    }

    public function paidAtDate(array $dates): void
    {
        $this->dateBetween(
            OrderDocument::paidAt(),
            $dates['from'],
            $dates['to']
        );
    }

    public function tagId(int $tag): void
    {
        $this
            ->addBoolQuery(
                self::MUST,
                [
                    'term' => [
                        OrderDocument::tags() => $tag
                    ]
                ]
            );
    }

    public function paymentStatus(string $value): void
    {
        switch ($value) {
            case self::PAYMENT_STATUS_PAID:
                $this->paid();
                break;
            case self::PAYMENT_STATUS_NOT_PAID:
                $this->paid(false);
                break;
        }
    }

    public function mobileTab(string $mobileTab): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'term' => [
                    OrderDocument::mobileTab() => $mobileTab
                ]
            ]
        );
    }

    public function dashboardFilter(string $filter): void
    {
        switch ($filter) {
            case self::DASHBOARD_TODAY_DELIVERED_ORDERS:
                $this->deliveredToday();
                return;
            case self::DASHBOARD_TODAY_PAID_ORDERS:
                $this->paidToday();
                return;
            case self::DASHBOARD_PICKUP_OVERDUE_ORDERS:
                $this->pickupOverdue();
                return;
            case self::DASHBOARD_DELIVERY_OVERDUE_ORDERS:
                $this->deliveryOverdue();
                return;
            case self::DASHBOARD_TODAY_PICKUP_ORDERS:
                $this->willBePickedUpToday();
                return;
            case self::DASHBOARD_TODAY_DELIVERY_ORDERS:
                $this->willBeDeliveredToday();
                return;
            case self::DASHBOARD_PAYMENT_OVERDUE_ORDERS:
                $this->paymentOverdue();
                return;
            case self::DASHBOARD_MONTH_PAID_ORDERS:
                $this->paidDuringCurrentMonths();
                return;
        }
    }

    public function deliveredToday(): void
    {
        $this->dateBetween(
            OrderDocument::deliveryDateActual(),
            Carbon::now('UTC')->startOfDay(),
            Carbon::now('UTC')->endOfDay()
        );
    }

    public function paidToday(): void
    {
        $this->dateBetween(
            OrderDocument::paidAt(),
            Carbon::now('UTC')->startOfDay(),
            Carbon::now('UTC')->endOfDay()
        );
    }

    public function pickupOverdue(): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'range' => [
                    OrderDocument::pickupPlannedDate() => [
                        'lt' => 'now'
                    ]
                ]
            ]
        );
    }

    public function deliveryOverdue(): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'range' => [
                    OrderDocument::deliveryPlannedDate() => [
                        'lt' => 'now'
                    ]
                ]
            ]
        );
    }

    public function willBePickedUpToday(): void
    {
        $this->dateBetween(
            OrderDocument::pickupPlannedDate(),
            Carbon::now('UTC')->startOfDay(),
            Carbon::now('UTC')->endOfDay()
        );
        $this->addBoolQuery(
            self::MUST_NOT,
            [
                'exists' => [
                    'field' => OrderDocument::pickupDateActual(),
                ]
            ]
        );
    }

    public function willBeDeliveredToday(): void
    {
        $this->dateBetween(
            OrderDocument::deliveryPlannedDate(),
            Carbon::now('UTC')->startOfDay(),
            Carbon::now('UTC')->endOfDay()
        );
        $this->addBoolQuery(
            self::MUST_NOT,
            [
                'exists' => [
                    'field' => OrderDocument::deliveryDateActual(),
                ]
            ]
        );
    }

    public function paymentOverdue(): void
    {
        $this->addBoolQuery(
            self::MUST,
            [
                'bool' => [
                    self::SHOULD => [
                        [
                            'range' => [
                                OrderDocument::customerPaymentPlannedDate() => [
                                    'lt' => 'now'
                                ]
                            ]
                        ],
                        [
                            'range' => [
                                OrderDocument::brokerPaymentPlannedDate() => [
                                    'lt' => 'now'
                                ]
                            ]
                        ],
                    ]
                ]
            ]
        );
    }

    public function paidDuringCurrentMonths(): void
    {
        $this->dateBetween(
            OrderDocument::paidAt(),
            Carbon::now('UTC')->startOfMonth(),
            Carbon::now('UTC')->endOfDay()
        );
    }
}
