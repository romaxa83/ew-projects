<?php

namespace App\Console\Commands\Sdek;

use AntistressStore\CdekSDK2\Entity\Responses\StatusesResponse;
use Illuminate\Console\Command;
use Throwable;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Repositories\OrdersRepository;
use WezomCms\Orders\Services\SdekService;

class SdekCheckOrders extends Command
{
    protected $signature = 'sdek:check-orders';

    protected $description = 'Проверка статусов заказов в СДЕК';

    public function handle()
    {
        try {
            $ordersRepo = resolve(OrdersRepository::class);
            $sdekService = resolve(SdekService::class);

            $orders = $ordersRepo->getSdekOrdersWithoutTtn();

            $orders->each(function (Order $order) use ($sdekService) {
                $deliveryInfo = $order->deliveryInformation;
                $sdekOrder = $sdekService->getOrderByUuid($deliveryInfo->uuid);

                if ($sdekOrder && $deliveryInfo->uuid === $sdekOrder->getUuid() && $ttn = $sdekOrder->getCdekNumber()) {
                    $deliveryInfo->setTtn($ttn);
                    foreach ($sdekOrder->getStatuses() as $status) {
                        /** @var StatusesResponse $status */
                        $deliveryInfo->addDeliveryStatus([
                            'code' => $status->getCode(),
                            'name' => $status->getName(),
                            'status_date_time' => $status->getDateTime(),
                        ]);
                    }

                    $deliveryInfo->save();
                }
            });

            if ($orders->count()) {
                $this->info('Orders with ids: ' . $orders->implode('id', ', ') . ' - statuses updated!');
            } else {
                $this->info('No orders without sdek ttn found.');
            }
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }
    }
}
