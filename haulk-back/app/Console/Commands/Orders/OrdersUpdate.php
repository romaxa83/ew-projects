<?php

namespace App\Console\Commands\Orders;

use App\Dto\Orders\OrderIndexDto;
use App\Services\Orders\OrderPaymentService;
use App\Services\Orders\OrderSearchService;
use App\Services\Orders\OrderService;
use Illuminate\Console\Command;

class OrdersUpdate extends Command
{
    protected $signature = 'orders:re-save';

    public function handle()
    {
        $service = app(OrderService::class);
        $orderService = resolve(OrderSearchService::class);
        $page = 1;
        $perPage = 20;
        do {
            $this->info("Page ... [page = {$page}]");

            $orders = $service->getOrderList(OrderIndexDto::create([
                'attributes' => ['overdue', 'paid'],
                'per_page' => $perPage,
                'page' => $page
            ]));
            foreach ($orders as $order) {
                try {
                    OrderPaymentService::init()->updatePlannedDate($order->payment);
                    $orderService->handleSaveOrderData($order);
                } catch (\Exception $exception) {
                    logger($exception->getMessage());
                    $this->info($exception->getMessage());
                }
            }
            $total = $orders->total();
            $this->info("Total ... [total = {$total}]");

            $page++;
        } while($total != 0);
    }
}

