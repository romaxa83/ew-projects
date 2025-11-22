<?php

namespace Tests\Helpers\Traits\Orders;

use App\Models\Orders\Order;
use App\Services\Orders\CompanySearchService;
use App\Services\Orders\OrderSearchService;

trait OrderESSavingHelper
{
    protected function makeDocuments(bool $withCompany = false): void
    {
        $orderService = resolve(OrderSearchService::class);
        $documents = [];
        Order::withoutGlobalScopes()
            ->with(
                [
                    'payment',
                    'payment.order',
                    'paymentStages',
                    'bonuses',
                    'expenses',
                    'vehicles'
                ]
            )
            ->each(
                static function (Order $order) use (&$documents, $orderService): void {
                    $documents[] = $orderService->handleSaveOrderData($order);
                }
            );
        sleep(1);
        if (!$withCompany) {
            return;
        }
        $companyService = resolve(CompanySearchService::class);
        foreach ($documents as $document) {
            $companyService->handleCalculateCompany($document, false);
        }
        sleep(1);
    }
}
