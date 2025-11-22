<?php

namespace App\Console\Commands\Helpers\Order;

use App\Models\Orders\Order;
use App\Services\Orders\OrderSearchService;
use Illuminate\Console\Command;

class OrderDocumentCreate extends Command
{
    protected $signature = 'es:order_document_create';

    public function handle()
    {
        $id = $this->ask('Enter ID ');

        $order = Order::find($id);

        /** @var $orderService OrderSearchService */
        $orderService = resolve(OrderSearchService::class);

        $document = $orderService->handleSaveOrderData($order);

        dd($document);
    }
}
