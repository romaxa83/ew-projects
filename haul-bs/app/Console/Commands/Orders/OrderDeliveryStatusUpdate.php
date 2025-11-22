<?php

namespace App\Console\Commands\Orders;

use App\Enums\Orders\Parts\DeliveryStatus;
use App\Enums\Orders\Parts\OrderStatus;
use App\Models\Orders\Parts\Order;
use App\Services\Orders\Parts\DeliveryService;
use App\Services\Orders\Parts\OrderStatusService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Throwable;

class OrderDeliveryStatusUpdate extends Command
{
    protected $signature = 'orders:delivery-update';
    protected $description = 'обновление статуса доставкам если есть драйвер под них';

    public function __construct(
        protected OrderStatusService $orderStatusService,
        protected DeliveryService $deliveryService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $orders = Order::query()
            ->with(['deliveries' => function (HasMany $query) {
                return $query
                    ->where('status', '<>', DeliveryStatus::Delivered)
                    ->whereNotNull('tracking_number');
            }])
            ->whereNotIn('status', OrderStatus::statusForNotTracking())
            ->whereHas('deliveries', function (Builder $query) {
                return $query
                    ->where('status', '<>', DeliveryStatus::Delivered)
                    ->whereNotNull('tracking_number');
            })
            ->get();

        foreach ($orders as $order) {
            foreach ($order->deliveries as $delivery) {
                try {
                    $this->deliveryService
                        ->setStatus(
                            $delivery,
                            $delivery->getServiceStatus(),
                            true
                        );
                } catch (Throwable $e) {
                    $this->error($e->getMessage());
                }
            }

            try {
                $this->orderStatusService->checkOrderAndSetDelivered($order);
            } catch (Throwable $e) {
                $this->error($e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
