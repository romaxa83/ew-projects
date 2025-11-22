<?php

namespace App\Listeners\Orders;

use App\Documents\OrderDocument;
use App\Events\OrderModifyEvent;
use App\Services\Orders\CompanySearchService;
use App\Services\Orders\OrderSearchService;

class OrderModifyListener
{
    public function handle(OrderModifyEvent $event): void
    {
        /** @var $orderService OrderSearchService */
        $orderService = resolve(OrderSearchService::class);
        $companyService = resolve(CompanySearchService::class);
        $order = $event->getOrder();

        if ($order) {
            $document = $orderService->handleSaveOrderData($order);
            $companyService->handleCalculateCompany($document);
            return;
        }
        $document = OrderDocument::find($event->getOrderId());
        $orderService->removeOrderData($event->getOrderId());
        if ($document) {
            $companyService->handleCalculateCompany($document);
        }
    }
}
