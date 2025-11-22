<?php

namespace App\Services\Orders;

use App\Documents\CompanyDocument;
use App\Documents\Filters\CompanyDocumentFilter;
use App\Documents\Filters\DocumentFilter;
use App\Documents\Filters\Exceptions\DocumentFilterMethodNotFoundException;
use App\Documents\OrderDocument;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CompanySearchService
{
    private const BATCH_SIZE = 1000;

    /**
     * @param OrderDocument $order
     * @return CompanyDocument|null
     * @throws DocumentFilterMethodNotFoundException
     */
    public function handleCalculateCompany(OrderDocument $order, bool $withSleep = true): ?CompanyDocument
    {
        if (!$order->brokerId && !$order->carrierId) {
            return null;
        }
        if (!$order->shipperFullName) {
            return null;
        }
        if ($withSleep) {
            sleep(1);
        }
        $company = [
            CompanyDocument::id() => md5($order->shipperFullName . '_' . $order->brokerId . '_' . $order->carrierId),
            CompanyDocument::orderCount() => 0,
            CompanyDocument::totalDueCount() => 0,
            CompanyDocument::totalDue() => 0,
            CompanyDocument::lastPaymentStageId() => null,
            CompanyDocument::lastPaymentStage() => null,
            CompanyDocument::invoice() => null,
            CompanyDocument::referenceNumber() => null,
            CompanyDocument::invoiceSendDate() => null,
            CompanyDocument::paymentMethodId() => null,
            CompanyDocument::brokerAmount() => null,
            CompanyDocument::isPaid() => null
        ];
        $page = 0;
        while (true) {
            $result = OrderDocument::filter(
                [
                    'broker_id' => $order->brokerId,
                    'carrier_id' => $order->carrierId,
                    'without_deleted' => true,
                ]
            )
                ->addBoolQuery(
                    DocumentFilter::MUST,
                    [
                        'term' => [
                            OrderDocument::shipperFullName() => $order->shipperFullName
                        ]
                    ]
                )
                ->addBoolQuery(
                    DocumentFilter::MUST,
                    [
                        'exists' => [
                            'field' => OrderDocument::dispatcherId()
                        ]
                    ]
                )
                ->from($page * self::BATCH_SIZE)
                ->size(self::BATCH_SIZE)
                ->sort(OrderDocument::createdAt())
                ->search(
                    [
                        OrderDocument::brokerAmountForecast(),
                        OrderDocument::brokerPaymentPlannedDate(),
                        OrderDocument::isBrokerPaid(),
                        OrderDocument::lastPaymentStageId(),
                        OrderDocument::lastPaymentStage(),
                        OrderDocument::brokerReferenceNumber(),
                        OrderDocument::brokerInvoice(),
                        OrderDocument::brokerInvoiceSendDate(),
                        OrderDocument::brokerPaymentMethodId(),
                    ]
                );
            $page++;
            if ($result->isEmpty()) {
                break;
            }
            /**@var OrderDocument $item */
            foreach ($result as $item) {
                $company[CompanyDocument::orderCount()]++;
                if ($item->isBrokerPaid === false) {
                    $company[CompanyDocument::totalDueCount()]++;
                    if ($item->brokerPaymentPlannedDate) {
                        $company[CompanyDocument::brokerAmount()][] = [
                            'forecast' => $item->brokerAmountForecast,
                            'planned_date' => $item->brokerPaymentPlannedDate->toIso8601ZuluString()
                        ];
                    }
                    $company[CompanyDocument::totalDue()] += $item->brokerAmountForecast;
                }
                if (is_bool($item->isBrokerPaid)) {
                    $company[CompanyDocument::isPaid()][] = $item->isBrokerPaid;
                }
                if ($item->lastPaymentStageId && ($company[CompanyDocument::lastPaymentStageId()] === null || $company[CompanyDocument::lastPaymentStageId()] < $item->lastPaymentStageId)) {
                    $company[CompanyDocument::lastPaymentStageId()] = $item->lastPaymentStageId;
                }
                if ($item->lastPaymentStage) {
                    if ($company[CompanyDocument::lastPaymentStage()] === null || $company[CompanyDocument::lastPaymentStage()] < $item->lastPaymentStage) {
                        $company[CompanyDocument::lastPaymentStage()] = $item->lastPaymentStage;
                    }
                }
                if ($item->brokerReferenceNumber) {
                    $company[CompanyDocument::referenceNumber()][] = $item->brokerReferenceNumber;
                }
                if ($item->brokerInvoiceSendDate) {
                    $company[CompanyDocument::invoiceSendDate()][] = $item->brokerInvoiceSendDate->toIso8601ZuluString();
                }
                if ($item->brokerInvoice) {
                    $company[CompanyDocument::invoice()][] = $item->brokerInvoice;
                }
                if ($item->brokerPaymentMethodId) {
                    $company[CompanyDocument::paymentMethodId()][] = $item->brokerPaymentMethodId;
                }
            }
        }
        if ($company[CompanyDocument::orderCount()] === 0) {
            CompanyDocument::query()
                ->delete($company[CompanyDocument::id()]);
            return null;
        }
        $document = CompanyDocument::init();
        $document->id = $company[CompanyDocument::id()];
        $document->brokerId = $order->brokerId;
        $document->carrierId = $order->carrierId;
        $document->companyName = $order->shipperFullName;
        $document->orderCount = $company[CompanyDocument::orderCount()];
        $document->totalDueCount = $company[CompanyDocument::totalDueCount()];
        $document->totalDue = $company[CompanyDocument::totalDue()];
        $document->brokerAmount = $company[CompanyDocument::brokerAmount()];
        $document->lastPaymentStageId = $company[CompanyDocument::lastPaymentStageId()];
        $document->lastPaymentStage = $company[CompanyDocument::lastPaymentStage()];
        $document->referenceNumber = $company[CompanyDocument::referenceNumber()];
        $document->invoiceSendDate = $company[CompanyDocument::invoiceSendDate()];
        $document->invoice = $company[CompanyDocument::invoice()];
        $document->paymentMethodId = $company[CompanyDocument::paymentMethodId()] !== null ? array_values(
            array_unique(
                $company[CompanyDocument::paymentMethodId()]
            )
        ) : null;
        $document->isPaid = $company[CompanyDocument::isPaid()] !== null ? array_values(
            array_unique(
                $company[CompanyDocument::isPaid()]
            )
        ) : null;
        $document->save();
        return $document;
    }

    public function getCompanyReport(
        array $filter,
        int $page,
        int $perPage,
        string $orderBy,
        string $orderType
    ): LengthAwarePaginator
    {
        $this->setScopeFilters($filter);
        $count = CompanyDocument::filter($filter)->count();
        if (!$count) {
            return new LengthAwarePaginator(collect(), 0, $perPage, $page);
        }

        $report = CompanyDocument::filter($filter)
            ->size($perPage)
            ->from(($page - 1) * $perPage)
            ->sort($orderBy, $orderType)
            ->addBodyData(
                [
                    'runtime_mappings' => [
                        'past_due_count' => [
                            'type' => 'long',
                            'script' => [
                                'lang' => 'painless',
                                'source' => "
                                    def temp = params._source." . CompanyDocument::brokerAmount() . ";
                                    if (temp == null || temp.size() == 0) {
                                        emit(0);
                                    } else {
                                        def pastDueCount = 0;
                                        for(item in temp) {
                                            Instant now = Instant.ofEpochMilli(new Date().getTime());
                                            Instant date = Instant.parse(item['planned_date']);
                                            if (ChronoUnit.SECONDS.between(date, now) > 0) {
                                                pastDueCount ++;
                                            }
                                        }
                                        emit(pastDueCount)
                                    }
                                "
                            ]
                        ],
                        'past_due' => [
                            'type' => 'double',
                            'script' => [
                                'lang' => 'painless',
                                'source' => "
                                    def temp = params._source." . CompanyDocument::brokerAmount() . ";
                                    if (temp == null || temp.size() == 0) {
                                        emit(0.0);
                                    } else {
                                        def pastDue = 0.0;
                                        for(item in temp) {
                                            Instant now = Instant.ofEpochMilli(new Date().getTime());
                                            Instant date = Instant.parse(item['planned_date']);
                                            if (ChronoUnit.SECONDS.between(date, now) > 0) {
                                                pastDue += item['forecast'];
                                            }
                                        }
                                        emit(pastDue)
                                    }
                                "
                            ]
                        ],
                        'current_due' => [
                            'type' => 'double',
                            'script' => [
                                'lang' => 'painless',
                                'source' => "
                                    def temp = params._source." . CompanyDocument::brokerAmount() . ";
                                    if (temp == null || temp.size() == 0) {
                                        emit(0.0);
                                    } else {
                                        def currentDue = 0.0;
                                        for(item in temp) {
                                            Instant now = Instant.ofEpochMilli(new Date().getTime());
                                            Instant date = Instant.parse(item['planned_date']);
                                            if (ChronoUnit.SECONDS.between(date, now) <= 0) {
                                                currentDue += item['forecast'];
                                            }
                                        }
                                        emit(currentDue)
                                    }
                                "
                            ]
                        ],
                    ],
                    'fields' => [
                        'past_due_count',
                        'past_due',
                        'current_due',
                    ]
                ]
            )
            ->search(
                [
                    CompanyDocument::companyName(),
                    CompanyDocument::orderCount(),
                    CompanyDocument::totalDue(),
                    CompanyDocument::totalDueCount(),
                    CompanyDocument::lastPaymentStageId()
                ]
            );
        return new LengthAwarePaginator($report, $count, $perPage, $page);
    }

    public function getTotalCompanyReport(array $filter): array
    {
        $this->setScopeFilters($filter);
        return CompanyDocument::filter($filter)
            ->addBodyData(
                [
                    'runtime_mappings' => [
                        'past_due' => [
                            'type' => 'double',
                            'script' => [
                                'lang' => 'painless',
                                'source' => "
                                    def temp = params._source." . CompanyDocument::brokerAmount() . ";
                                    if (temp == null || temp.size() == 0) {
                                        emit(0.0);
                                    } else {
                                        def pastDue = 0.0;
                                        for(item in temp) {
                                            Instant now = Instant.ofEpochMilli(new Date().getTime());
                                            Instant date = Instant.parse(item['planned_date']);
                                            if (ChronoUnit.SECONDS.between(date, now) > 0) {
                                                pastDue += item['forecast'];
                                            }
                                        }
                                        emit(pastDue)
                                    }
                                "
                            ]
                        ],
                        'current_due' => [
                            'type' => 'double',
                            'script' => [
                                'lang' => 'painless',
                                'source' => "
                                    def temp = params._source." . CompanyDocument::brokerAmount() . ";
                                    if (temp == null || temp.size() == 0) {
                                        emit(0.0);
                                    } else {
                                        def currentDue = 0.0;
                                        for(item in temp) {
                                            Instant now = Instant.ofEpochMilli(new Date().getTime());
                                            Instant date = Instant.parse(item['planned_date']);
                                            if (ChronoUnit.SECONDS.between(date, now) <= 0) {
                                                currentDue += item['forecast'];
                                            }
                                        }
                                        emit(currentDue)
                                    }
                                "
                            ]
                        ],
                    ]
                ]
            )
            ->size(0)
            ->aggregation(
                [
                    'total_due' => [
                        'sum' => [
                            'field' => 'total_due'
                        ]
                    ],
                    'current_due' => [
                        'sum' => [
                            'field' => 'current_due'
                        ]
                    ],
                    'past_due' => [
                        'sum' => [
                            'field' => 'past_due'
                        ]
                    ],
                ]
            );
    }

    public function getCompanyList(?string $company): array
    {
        $filter = [];
        if ($company) {
            /**@see CompanyDocumentFilter::companyName() */
            $filter['company_name'] = $company;
        }
        $this->setScopeFilters($filter);
        $page = 0;
        $result = [];
        while (true) {
            $companies = CompanyDocument::filter($filter)
                ->sort(CompanyDocument::orderCount(), 'desc')
                ->size(10000)
                ->from($page * 10000)
                ->search(
                    [
                        CompanyDocument::companyName()
                    ]
                );
            if ($companies->isEmpty()) {
                break;
            }
            $page++;
            $result = array_merge(
                $result,
                $companies
                    ->pluck('companyName')
                    ->map(
                        static fn(string $name) => Str::upper($name)
                    )
                    ->toArray()
            );
        }
        return $result;
    }

    private function setScopeFilters(array &$filters): void
    {
        if (!auth()->check() || is_null($user = authUser())) {
            return;
        }
        if ($user->isBroker()) {
            /**@see CompanyDocumentFilter::brokerId() */
            $filters['broker_id'] = $user->broker_id;
            return;
        }
        if ($user->isCarrier()) {
            /**@see CompanyDocumentFilter::carrierId() */
            $filters['carrier_id'] = $user->carrier_id;
            return;
        }
        /**@see CompanyDocumentFilter::brokerId() */
        $filters['broker_id'] = 0;
        /**@see CompanyDocumentFilter::carrierId() */
        $filters['carrier_id'] = 0;
    }
}
