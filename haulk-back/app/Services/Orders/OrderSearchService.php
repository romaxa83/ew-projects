<?php

namespace App\Services\Orders;

use App\Documents\Filters\DocumentFilter;
use App\Documents\Filters\Exceptions\DocumentFilterMethodNotFoundException;
use App\Documents\Filters\OrderDocumentFilter;
use App\Documents\OrderDocument;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Orders\PaymentStage;
use App\ValueObjects\Orders\OverdueData;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OrderSearchService
{
    public function handleSaveOrderData(Order $order): OrderDocument
    {
        $document = OrderDocument::init();
        $document->id = $order->id;
        $document->carrierId = $order->carrier_id;
        $document->brokerId = $order->broker_id;
        $document->loadId = Str::lower($order->load_id);
        $document->status = $order->status;
        $document->calculatedStatus = $order->calculated_status;
        $document->calculatedStatusWeight = config('orders.sorting_by_calculated_status.' . $document->calculatedStatus);
        $document->needReview = !empty($order->need_review);
        $document->hasReview = !empty($order->has_review);
        $document->deleted = !empty($order->deleted_at);
        $document->driverId = $order->driver_id;
        $document->dispatcherId = $order->dispatcher_id;
        $document->ownerId = $order->driver_id ? $order->driver->owner_id : null;
        $document->pickupFullName = Str::lower($order->pickup_full_name);
        $document->deliveryFullName = Str::lower($order->delivery_full_name);
        $document->shipperFullName = Str::lower($order->shipper_full_name);
        $document->tags = $order->tags->isNotEmpty() ? $order->tags->pluck('id')->toArray() : null;
        $document->calculatedDateFirst = $this->getCalculatedDate1($order);
        $document->calculatedDateSecond = $this->getCalculatedDate2($order);
        $document->isBilled = $order->is_billed;
        $document->createdAt = $order->created_at->setTimezone('UTC');
        $document->pickupDateActual = $order->pickup_date_actual
            ? Carbon::createFromTimestamp($order->pickup_date_actual)->setTimezone('UTC')
            : null;
        $document->pickupDateActualTz = $order->pickup_date_actual_tz;
        $document->deliveryDateActual = $order->delivery_date_actual
            ? Carbon::createFromTimestamp($order->delivery_date_actual)->setTimezone('UTC')
            : null;
        $document->deliveryDateActualTz = $order->delivery_date_actual_tz;
        $this->setVehiclesInfo($document, $order);
        $this->setPaymentInfo($document, $order);
        $this->setOverdueDates($document, $order);
        $this->setPaymentStagesInfo($document, $order);
        $this->setMobileTab($document, $order);
        $document->save();
        return $document;
    }

    private function getCalculatedDate1(Order $order): int
    {
        if (in_array($order->calculated_status, [Order::CALCULATED_STATUS_NEW, Order::CALCULATED_STATUS_ASSIGNED])) {
            return $order->pickup_date ? $order->pickup_date : $order->created_at->getTimestamp();
        }
        return 0;
    }

    private function getCalculatedDate2(Order $order): int
    {
        if ($order->calculated_status === Order::CALCULATED_STATUS_PICKED_UP) {
            return $order->pickup_date_actual;
        }
        if ($order->calculated_status === Order::CALCULATED_STATUS_DELIVERED) {
            return $order->delivery_date_actual;
        }
        return 0;
    }

    private function setVehiclesInfo(OrderDocument $document, Order $order): void
    {
        $document->make = null;
        $document->model = null;
        $document->vin = null;
        $document->year = null;
        if ($order->vehicles->isEmpty()) {
            return;
        }
        foreach ($order->vehicles as $vehicle) {
            if (!empty($vehicle->make)) {
                $makes[] = Str::lower($vehicle->make);
            }
            if (!empty($vehicle->model)) {
                $models[] = Str::lower($vehicle->model);
            }
            if (!empty($vehicle->vin)) {
                $vin[] = Str::lower($vehicle->vin);
            }
            if (!empty($vehicle->year)) {
                $years[] = $vehicle->year;
            }
        }
        if (!empty($makes)) {
            $document->make = array_values(array_unique($makes));
        }
        if (!empty($models)) {
            $document->model = array_values(array_unique($models));
        }
        if (!empty($years)) {
            $document->year = array_values(array_unique($years));
        }
        if (!empty($vin)) {
            $document->vin = array_values(array_unique($vin));
        }
    }

    private function setPaymentInfo(OrderDocument $document, Order $order): void
    {
        $payment = $order->payment;
        if (!$payment) {
            return;
        }
        $document->brokerInvoice = $payment->broker_payment_invoice_id ? Str::lower($payment->broker_payment_invoice_id) : null;
        $document->customerInvoice = $payment->customer_payment_invoice_id ? Str::lower($payment->customer_payment_invoice_id) : null;
        $document->brokerPaymentMethodId = $payment->broker_payment_method_id;
        $document->customerPaymentMethodId = $payment->customer_payment_method_id;
        $document->brokerFeePaymentMethodId = $payment->broker_fee_method_id;
        $document->brokerInvoiceSendDate = $payment->broker_payment_invoice_issue_date ? Carbon::createFromTimestamp($payment->broker_payment_invoice_issue_date)->setTimezone('UTC') : null;
        $document->customerInvoiceSendDate = $payment->customer_payment_invoice_issue_date ? Carbon::createFromTimestamp($payment->customer_payment_invoice_issue_date)->setTimezone('UTC') : null;
        $this->setPaidFlags($document, $payment);
    }

    private function setPaidFlags(OrderDocument $document, Payment $payment): void
    {
        $flags = $payment->paidFlags();
        $document->isBrokerFeePaid = $flags->isBrokerFeePaid;
        $document->isBrokerPaid = $flags->isBrokerPaid;
        $document->isCustomerPaid = $flags->isCustomerPaid;
        $document->brokerAmountForecast = $flags->brokerAmountForecast;
        $document->customerAmountForecast = $flags->customerAmountForecast;
        $document->brokerFeeAmountForecast = $flags->brokerFeeAmountForecast;
        $document->totalCarrierAmount = $flags->totalCarrierAmount;
        $document->paidAt = $flags->paidAt ? $flags->paidAt->setTimezone('UTC') : null;
        $document->brokerFeePaidAt = isset($flags->brokerFeePaidAt) ? $flags->brokerFeePaidAt->setTimezone('UTC') : null;
    }

    private function setOverdueDates(OrderDocument $document, Order $order): void
    {
        $overdue = $order->getOverdue();
        $document->pickupPlannedDate = $overdue->getPickupPlanedDate();
        $document->deliveryPlannedDate = $overdue->getDeliveryPlanedDate();
        $document->customerPaymentPlannedDate = $overdue->getCustomerPaymentPlanedDate();
        $document->brokerPaymentPlannedDate = $overdue->getBrokerPaymentPlanedDate();
        $document->brokerFeePaymentPlannedDate = $overdue->getBrokerFeePaymentPlanedDate();
    }

    private function setPaymentStagesInfo(OrderDocument $document, Order $order): void
    {
        $document->lastPaymentStage = null;
        $document->brokerReferenceNumber = null;
        $document->referenceNumber = null;
        $paymentStages = $order->paymentStages;
        if ($paymentStages->isEmpty()) {
            return;
        }
        $lastPaymentStage = $paymentStages->sortByDesc('payment_date')->first();
        $document->lastPaymentStage = $lastPaymentStage->payment_date;
        $document->lastPaymentStageId = $lastPaymentStage->id;
        $brokerReferenceNumber = [];
        $referenceNumber = [];
        $paymentStages
            ->each(
                static function (PaymentStage $paymentStage) use (&$brokerReferenceNumber, &$referenceNumber): void {
                    if (!$paymentStage->reference_number) {
                        return;
                    }
                    $referenceNumber[] = Str::lower($paymentStage->reference_number);
                    if ($paymentStage->payer !== Payment::PAYER_BROKER) {
                        return;
                    }
                    $brokerReferenceNumber[] = Str::lower($paymentStage->reference_number);
                }
            );
        if (empty($referenceNumber)) {
            return;
        }
        $document->referenceNumber = $referenceNumber;
        if (empty($brokerReferenceNumber)) {
            return;
        }
        $document->brokerReferenceNumber = $brokerReferenceNumber;
    }

    private function setMobileTab(OrderDocument $document, Order $order): void
    {
        switch (true) {
            case $document->status === Order::STATUS_NEW &&
                $document->pickupPlannedDate &&
                $document->pickupPlannedDate->greaterThanOrEqualTo(Carbon::tomorrow($order->pickup_contact['timezone'])->setTimezone('UTC')) :
                $document->mobileTab = Order::MOBILE_TAB_PLAN;
                break;
            case $document->status === Order::STATUS_DELIVERED &&
                $order->has_delivery_inspection &&
                $order->has_delivery_signature &&
                !empty($order->payment) &&
                $order->payment->driver_payment_data_sent === false &&
                !empty($order->payment->customer_payment_amount):
            case $document->status === Order::STATUS_PICKED_UP &&
                $order->has_pickup_inspection &&
                $order->has_pickup_signature:
            case $document->status === Order::STATUS_NEW &&
                (!$document->pickupPlannedDate || $document->pickupPlannedDate->lessThan(Carbon::tomorrow($order->pickup_contact['timezone'])->setTimezone('UTC'))):
                $document->mobileTab = Order::MOBILE_TAB_IN_WORK;
                break;
            case $document->status === Order::STATUS_DELIVERED &&
                $order->has_delivery_inspection &&
                $order->has_delivery_signature &&
                $document->deliveryDateActual &&
                $document
                    ->deliveryDateActual
                    ->greaterThanOrEqualTo(
                        Carbon::now($order->delivery_contact['timezone'])
                            ->setTimezone('UTC')
                            ->subDays(config('orders.mobile.history.days'))
                    ) &&
                !(
                    !empty($order->payment) &&
                    $order->payment->driver_payment_data_sent === false &&
                    !empty($order->payment->customer_payment_amount)
                ):
                $document->mobileTab = Order::MOBILE_TAB_HISTORY;
                break;
        }
    }

    public function removeOrderData(int $id): void
    {
        OrderDocument::query()->delete($id);
    }

    public function get(array $filter): Collection
    {
        $this->setScopeFilters($filter);
        if (empty($filter['state'])) {
            /**@see OrderDocumentFilter::dispatcherExists() */
            $filter['dispatcher_exists'] = true;
        }
        $ids = [];
        $page = 0;
        while (true) {
            $_ids = OrderDocument::filter($filter)
                ->from($page * 1000)
                ->size(1000)
                ->searchIds();
            if (empty($_ids)) {
                break;
            }
            $page++;
            $ids = array_merge($ids, $_ids);
        }
        if (empty($ids)) {
            return new \Illuminate\Database\Eloquent\Collection();
        }
        return Order::withoutGlobalScopes()
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * @param int $page
     * @param int $perPage
     * @param array $filter
     * @param array|null $sort
     * @param bool $forMobile
     * @return LengthAwarePaginator
     * @throws DocumentFilterMethodNotFoundException
     */
    public function paginate(
        int $page,
        int $perPage,
        array $filter,
        ?array $sort,
        bool $forMobile = false
    ): LengthAwarePaginator
    {
        try {
            $this->setScopeFilters($filter);
            if (empty($filter['state'])) {
                /**@see OrderDocumentFilter::dispatcherExists() */
                $filter['dispatcher_exists'] = true;
            }

            $query = OrderDocument::filter($filter);
            $count = $query->count();

//            logger_info('ORDSR DICUMENT FILTER', [
//                'filter' => $filter,
//                'query' => $query
//            ]);

            if ($count === 0) {
                return new LengthAwarePaginator([], 0, $perPage, $page);
            }
            if (array_key_exists('s', $filter)) {
                $query->sortBySearch();
            }
            if (!empty($sort)) {
                foreach ($sort as $field => $direction) {
                    switch ($field) {
                        case 'total_due':
                            $query->sortByTotalDue($direction);
                            break;
                        case 'current_due':
                            $query->sortByCurrentDue($direction);
                            break;
                        case 'past_due':
                            $query->sortByPastDue($direction);
                            break;
                        default:
                            $query->sort($field, $direction);
                            break;
                    }
                }
            }

            $documents = $query
            ->sort(OrderDocument::calculatedStatusWeight())
            ->sort(OrderDocument::calculatedDateFirst())
            ->sort(OrderDocument::calculatedDateSecond(), 'desc')
                ->size($perPage)
                ->from(($page - 1) * $perPage)
                ->search(
                    [
                        OrderDocument::id(),
                        OrderDocument::pickupPlannedDate(),
                        OrderDocument::deliveryPlannedDate(),
                        OrderDocument::customerPaymentPlannedDate(),
                        OrderDocument::brokerPaymentPlannedDate(),
                        OrderDocument::brokerFeePaymentPlannedDate(),
                        OrderDocument::paidAt(),
                        OrderDocument::brokerFeePaidAt(),
                        OrderDocument::isCustomerPaid(),
                        OrderDocument::isBrokerPaid(),
                        OrderDocument::isBrokerFeePaid(),
                        OrderDocument::brokerFeeAmountForecast(),
                        OrderDocument::brokerAmountForecast(),
                        OrderDocument::mobileTab()
                    ]
                )
                ->keyBy('id');

            $ordersIds = $documents->pluck('id')
                ->toArray();

            $query = Order::withoutGlobalScopes()
                ->whereIn('id', $ordersIds)
                ->orderByRaw("POSITION(id::text IN '" . implode(", ", $ordersIds) . "')")
            ;

            if (!$forMobile) {
                $query->loadMany();
            } else {
                $query->loadManyMobile();
            }

            $queryCount = $query->count();
            if($queryCount == 0){
                $count = 0;
            }

            return new LengthAwarePaginator(
                $query
                    ->get()
                    ->map(
                        fn(Order $order): Order => $this->addResourceInfoForOrder($order, $documents)
                    ),
                $count,
                $perPage,
                $page
            );

        } catch (\Throwable $e){
            logger_info($e->getMessage(), [$e]);
        }
    }

    public function loadMissingResourceData(Order $order): Order
    {
        $document = OrderDocument::find($order->id);
        return $this->addResourceInfoForOrder($order, $document ? collect([$document->id => $document]) : collect());
    }

    private function addResourceInfoForOrder(Order $order, Collection $documents): Order
    {
        $resourceInfo = [
            'is_paid' => null,
            'is_broker_fee_paid' => null,
            'overdue' => null,
            'is_overdue' => false,
            'paid_at' => null,
            'broker_fee_paid_at' => null,
            'broker_fee_total_due' => 0.0,
            'broker_fee_current_due' => 0.0,
            'broker_fee_past_due' => 0.0,
            'total_due' => 0.0,
            'current_due' => 0.0,
            'past_due' => 0.0,
            'order_category' => null
        ];
        if (!$documents->has($order->id)) {
            $order->setAttribute('resource_info', $resourceInfo);
            return $order;
        }
        $tz = date_default_timezone_get();
        $now = Carbon::now('UTC');
        /**@var OrderDocument $document */
        $document = $documents->get($order->id);
        $overdue = OverdueData::makeFromDocument($document);
        $resourceInfo['is_paid'] = $document->isCustomerPaid === null && $document->isBrokerPaid === null || !($document->isCustomerPaid === false || $document->isBrokerPaid === false);
        $resourceInfo['is_broker_fee_paid'] = $document->isBrokerFeePaid;
        $resourceInfo['overdue'] = $overdue->forResource();
        $resourceInfo['is_overdue'] = $overdue->exists();
        $resourceInfo['paid_at'] = $document->paidAt ? $document->paidAt->setTimezone($tz)->getTimestamp() : null;
        $resourceInfo['broker_fee_paid_at'] = $document->brokerFeePaidAt ? $document->brokerFeePaidAt->setTimezone($tz)->getTimestamp() : null;
        $resourceInfo['order_category'] = $document->mobileTab;
        if (!$document->isBrokerFeePaid && $document->brokerFeePaymentPlannedDate) {
            $resourceInfo['broker_fee_total_due'] = $document->brokerFeeAmountForecast;
            if ($document->brokerFeePaymentPlannedDate->lessThan($now)) {
                $resourceInfo['broker_fee_past_due'] = $document->brokerFeeAmountForecast;
            } else {
                $resourceInfo['broker_fee_current_due'] = $document->brokerFeeAmountForecast;
            }
        }
        if (!$document->isBrokerPaid && $document->brokerPaymentPlannedDate) {
            $resourceInfo['total_due'] = $document->brokerAmountForecast;
            if ($document->brokerPaymentPlannedDate->lessThan($now)) {
                $resourceInfo['past_due'] = $document->brokerAmountForecast;
            } else {
                $resourceInfo['current_due'] = $document->brokerAmountForecast;
            }
        }
        $order->setAttribute('resource_info', $resourceInfo);
        return $order;
    }

    private function setScopeFilters(array &$filters): void
    {
        if (!auth()->check() || is_null($user = authUser())) {
            return;
        }
        if ($user->isDriver()) {
            /**@see OrderDocumentFilter::driverId() */
            $filters['driver_id'] = $user->id;
        }
        if ($user->isBroker()) {
            /**@see OrderDocumentFilter::brokerId() */
            $filters['broker_id'] = $user->broker_id;
            return;
        }
        if ($user->isCarrier()) {
            /**@see OrderDocumentFilter::carrierId() */
            $filters['carrier_id'] = $user->carrier_id;
            return;
        }
        /**@see OrderDocumentFilter::brokerId() */
        $filters['broker_id'] = 0;
        /**@see OrderDocumentFilter::carrierId() */
        $filters['carrier_id'] = 0;
    }

    public function getOrderTotal(array $filter): array
    {
        $this->setScopeFilters($filter);
        return OrderDocument::filter($filter)
            ->addBodyData(
                [
                    'runtime_mappings' => [
                        'broker_fee_total_due' => [
                            'type' => 'double',
                            'script' => [
                                'lang' => 'painless',
                                'source' => "
                                    if (doc['" . OrderDocument::brokerFeeAmountForecast() . "'].size() == 0) {
                                        emit(0.0);
                                    } else if (doc['" . OrderDocument::isBrokerFeePaid() . "'].size() != 0 && doc['" . OrderDocument::isBrokerFeePaid() . "'].value == true) {
                                        emit(0.0);
                                    } else {
                                        emit((float) doc['" . OrderDocument::brokerFeeAmountForecast() . "'].value)
                                    }
                                "
                            ]
                        ],
                        'broker_total_due' => [
                            'type' => 'double',
                            'script' => [
                                'lang' => 'painless',
                                'source' => "
                                    if (doc['" . OrderDocument::brokerAmountForecast() . "'].size() == 0) {
                                        emit(0.0);
                                    } else if (doc['" . OrderDocument::isBrokerPaid() . "'].size() != 0 && doc['" . OrderDocument::isBrokerPaid() . "'].value == true) {
                                        emit(0.0);
                                    } else {
                                        emit((float) doc['" . OrderDocument::brokerAmountForecast() . "'].value)
                                    }
                                "
                            ]
                        ],
                        'broker_fee_overdue' => [
                            'type' => 'long',
                            'script' => [
                                'lang' => 'painless',
                                'source' => "
                                    if (doc['broker_fee_total_due'].value == 0.0 || doc['" . OrderDocument::brokerFeePaymentPlannedDate() . "'].size() == 0) {
                                        emit(0);
                                    } else {
                                        Instant now = Instant.ofEpochMilli(new Date().getTime());
                                        Instant date = Instant.ofEpochMilli(doc['" . OrderDocument::brokerFeePaymentPlannedDate() . "'].value.getMillis());
                                        emit(ChronoUnit.SECONDS.between(date, now) > 0 ? 1 : -1);
                                    }
                                "
                            ]
                        ],
                        'broker_overdue' => [
                            'type' => 'long',
                            'script' => [
                                'lang' => 'painless',
                                'source' => "
                                    if (doc['broker_total_due'].value == 0.0 || doc['" . OrderDocument::brokerPaymentPlannedDate() . "'].size() == 0) {
                                        emit(0);
                                    } else {
                                        Instant now = Instant.ofEpochMilli(new Date().getTime());
                                        Instant date = Instant.ofEpochMilli(doc['" . OrderDocument::brokerPaymentPlannedDate() . "'].value.getMillis());
                                        emit(ChronoUnit.SECONDS.between(date, now) > 0 ? 1 : -1);
                                    }
                                "
                            ]
                        ],
                        'broker_fee_past_due' => [
                            'type' => 'double',
                            'script' => [
                                'lang' => 'painless',
                                'source' => "
                                    if (doc['broker_fee_overdue'].value == 1) {
                                        emit(doc['broker_fee_total_due'].value);
                                    } else {
                                        emit(0.0)
                                    }
                                "
                            ]
                        ],
                        'broker_fee_current_due' => [
                            'type' => 'double',
                            'script' => [
                                'lang' => 'painless',
                                'source' => "
                                    if (doc['broker_fee_overdue'].value == -1) {
                                        emit(doc['broker_fee_total_due'].value);
                                    } else {
                                        emit(0.0)
                                    }
                                "
                            ]
                        ],
                        'broker_past_due' => [
                            'type' => 'double',
                            'script' => [
                                'lang' => 'painless',
                                'source' => "
                                    if (doc['broker_overdue'].value == 1) {
                                        emit(doc['broker_total_due'].value);
                                    } else {
                                        emit(0.0)
                                    }
                                "
                            ]
                        ],
                        'broker_current_due' => [
                            'type' => 'double',
                            'script' => [
                                'lang' => 'painless',
                                'source' => "
                                    if (doc['broker_overdue'].value == -1) {
                                        emit(doc['broker_total_due'].value);
                                    } else {
                                        emit(0.0)
                                    }
                                "
                            ]
                        ]
                    ]
                ]
            )
            ->size(0)
            ->aggregation(
                [
                    'total_carrier_amount' => [
                        'sum' => [
                            'field' => OrderDocument::totalCarrierAmount()
                        ]
                    ],
                    'customer_amount_forecast' => [
                        'sum' => [
                            'field' => 'customer_amount_forecast'
                        ]
                    ],
                    'broker_amount_forecast' => [
                        'sum' => [
                            'field' => 'broker_amount_forecast'
                        ]
                    ],
                    'broker_fee_amount_forecast' => [
                        'sum' => [
                            'field' => 'broker_fee_amount_forecast'
                        ]
                    ],
                    'broker_fee_total_due' => [
                        'sum' => [
                            'field' => 'broker_fee_total_due'
                        ]
                    ],
                    'broker_fee_past_due' => [
                        'sum' => [
                            'field' => 'broker_fee_past_due'
                        ]
                    ],
                    'broker_fee_current_due' => [
                        'sum' => [
                            'field' => 'broker_fee_current_due'
                        ]
                    ],
                    'broker_total_due' => [
                        'sum' => [
                            'field' => 'broker_total_due'
                        ]
                    ],
                    'broker_past_due' => [
                        'sum' => [
                            'field' => 'broker_past_due'
                        ]
                    ],
                    'broker_current_due' => [
                        'sum' => [
                            'field' => 'broker_current_due'
                        ]
                    ],
                ]
            );
    }

    /**
     * @param string $loadId
     * @param int|null $orderId
     * @return Collection
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function searchSameLoadId(string $loadId, ?int $orderId): Collection
    {
        $filter = [
            /**@see OrderDocumentFilter::state() */
            'state' => [
                Order::CALCULATED_STATUS_OFFER,
                Order::CALCULATED_STATUS_NEW,
                Order::CALCULATED_STATUS_ASSIGNED,
                Order::CALCULATED_STATUS_PICKED_UP,
            ]
        ];
        $this->setScopeFilters($filter);
        $query = OrderDocument::filter($filter)
            ->addBoolQuery(
                DocumentFilter::MUST,
                [
                    'term' => [
                        OrderDocument::loadId() => Str::lower($loadId)
                    ]
                ]
            );
        if ($orderId !== null) {
            $query
                ->addBoolQuery(
                    DocumentFilter::MUST_NOT,
                    [
                        'term' => [
                            OrderDocument::id() => (string)$orderId
                        ]
                    ]
                );
        }
        $ids = $query->searchIds();
        if (empty($ids)) {
            return collect();
        }
        return Order::withoutGlobalScopes()->whereIn('id', $ids)
            ->select(['id', 'load_id'])
            ->get();
    }

    /**
     * @param string $vin
     * @param int|null $orderId
     * @return Collection
     * @noinspection PhpUnhandledExceptionInspection
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function searchSameVin(string $vin, ?int $orderId): Collection
    {
        $filter = [
            /**@see OrderDocumentFilter::state() */
            'state' => [
                Order::CALCULATED_STATUS_NEW,
                Order::CALCULATED_STATUS_ASSIGNED,
                Order::CALCULATED_STATUS_PICKED_UP,
            ]
        ];
        $this->setScopeFilters($filter);
        $query = OrderDocument::filter($filter)
            ->addBoolQuery(
                DocumentFilter::MUST,
                [
                    'term' => [
                        OrderDocument::vin() => Str::lower($vin)
                    ]
                ]
            );
        if ($orderId !== null) {
            $query
                ->addBoolQuery(
                    DocumentFilter::MUST_NOT,
                    [
                        'term' => [
                            OrderDocument::id() => (string)$orderId
                        ]
                    ]
                );
        }
        $ids = $query->searchIds();
        if (empty($ids)) {
            return collect();
        }
        return Order::withoutGlobalScopes()->whereIn('id', $ids)
            ->select(['id', 'load_id'])
            ->get();
    }
}
