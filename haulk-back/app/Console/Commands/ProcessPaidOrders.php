<?php

namespace App\Console\Commands;

use App\Documents\Filters\DocumentFilter;
use App\Documents\OrderDocument;
use App\Models\Orders\Order;
use App\Services\Orders\CompanySearchService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessPaidOrders extends Command
{
    protected $signature = 'orders:process-paid';

    protected $description = 'Delete paid orders after 12 months';

    public function handle(): void
    {
        $this->info('Starting..');

        $timestamp = Carbon::now('UTC')->subDays(config('orders.delete_after'));

        $documents = OrderDocument::filter(
            [
                'paid' => true
            ]
        )
            ->addBoolQuery(
                DocumentFilter::MUST,
                [
                    'bool' => [
                        DocumentFilter::SHOULD => [
                            [
                                'bool' => [
                                    DocumentFilter::MUST => [
                                        [
                                            'term' => [
                                                OrderDocument::isBrokerFeePaid() => true,
                                            ]
                                        ],
                                        [
                                            'range' => [
                                                OrderDocument::brokerFeePaidAt() => [
                                                    'lte' => $timestamp->toIso8601ZuluString()
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'bool' => [
                                    DocumentFilter::MUST_NOT => [
                                        [
                                            'exists' => [
                                                'field' => OrderDocument::isBrokerFeePaid(),
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            )
            ->addBoolQuery(
                DocumentFilter::MUST,
                [
                    'range' => [
                        OrderDocument::paidAt() => [
                            'lte' => $timestamp->toIso8601ZuluString()
                        ]
                    ]
                ]
            )
            ->addBoolQuery(
                DocumentFilter::MUST,
                [
                    'term' => [
                        OrderDocument::status() => Order::STATUS_DELIVERED
                    ]
                ]
            )
            ->size(1000000)
            ->search(
                [
                    OrderDocument::id(),
                    OrderDocument::shipperFullName(),
                    OrderDocument::carrierId(),
                    OrderDocument::brokerId()
                ]
            );
        if ($documents->isEmpty()) {
            $this->info('Finished..');
            return;
        }
        DB::table(Order::TABLE_NAME)
            ->whereRaw('id IN (' . $documents->pluck('id')->implode(',') . ')')
            ->delete();

        $service = resolve(CompanySearchService::class);

        $documents
            ->each(
                static function (OrderDocument $document) use ($service): void {
                    OrderDocument::query()->delete($document->id);
                    $service->handleCalculateCompany($document);
                }
            );

        $this->info('Finished..');
    }
}
