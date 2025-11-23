<?php

namespace WezomCms\Orders\Jobs;

use AntistressStore\CdekSDK2\Entity\Responses\StatusesResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Repositories\OrdersRepository;
use WezomCms\Orders\Services\SdekService;

class CheckSdekOrders implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private OrdersRepository $ordersRepo;
    private SdekService $sdekService;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->queue = config('queue.queues.sdek');
        $this->ordersRepo = resolve(OrdersRepository::class);
        $this->sdekService = resolve(SdekService::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \JsonException
     */
    public function handle(): void
    {
        $orders = $this->ordersRepo->getSdekOrdersWithoutTtn();

        Log::channel('sdek')->info('Orders without ttn: ');
        Log::channel('sdek')->info(json_encode($orders->pluck('id')->toArray(), JSON_THROW_ON_ERROR));

        $orders->each(function (Order $order) {
            $deliveryInfo = $order->deliveryInformation;
            $sdekOrder = $this->sdekService->getOrderByUuid($deliveryInfo->uuid);

            Log::channel('sdek')->info('Db order id: ' . $order->id . '. Sdek order uuid: ' . $sdekOrder->getUuid());

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
    }
}
