<?php

namespace App\Listeners\Warranty;

use App\Events\Warranty\WarrantyRegistrationProcessedEvent;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Warranty\WarrantyRegistration;
use App\Notifications\Warranty\WarrantyStatusChangedNotification;
use App\Services\Warranty\WarrantyService;
use App\Traits\SimpleHasher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class WarrantyRegistrationProcessedListener implements ShouldQueue
{
    use SimpleHasher;

    /**
     * @var Product[]
     */
    protected Collection|array $products;
    protected array $groupedSerials;

    public function handle(WarrantyRegistrationProcessedEvent $event): void
    {
        $this->groupSerialsByProduct($event->getSerialNumbers());
        $this->processActualSerialNumbers($event);
        $this->processSystemStatuses($event);

        $model = $event->getWarrantyRegistration();
        $hash = SimpleHasher::hash($model->getDataForHash($event->getSerialNumbers()));

        if(!$model->equalsHash($hash)){
            $model->setHash($hash);

            $this->sentNotification($event->getWarrantyRegistration());
        }
    }

    protected function groupSerialsByProduct(array $serialNumbers): void
    {
        $guids = array_column($serialNumbers, 'product_guid');

        $this->products = Product::query()
            ->whereIn('guid', $guids)
            ->simple()
            ->get(['id', 'guid'])
            ->keyBy('guid');

        $groupedSerials = [];

        foreach ($serialNumbers as ['product_guid' => $productGuid, 'serial_number' => $sn]) {
            $groupedSerials[$this->products->get($productGuid)->id][] = $sn;
        }

        $this->groupedSerials = $groupedSerials;
    }

    protected function processActualSerialNumbers(WarrantyRegistrationProcessedEvent $event): void
    {
        $serialNumbers = array_column($event->getSerialNumbers(), 'serial_number');

        $existsSerials = ProductSerialNumber::query()
            ->whereIn('serial_number', $serialNumbers)
            ->simple()
            ->get()
            ->keyBy('serial_number');

        $insert = [];

        foreach ($this->groupedSerials as $productId => $serialNumbers) {
            foreach ($serialNumbers as $sn) {
                if ($existsSerials->get($sn)) {
                    continue;
                }

                $insert[] = [
                    'product_id' => $productId,
                    'serial_number' => $sn,
                ];
            }
        }

        if (count($insert)) {
            ProductSerialNumber::query()->insertOrIgnore($insert);
        }
    }

    protected function processSystemStatuses(WarrantyRegistrationProcessedEvent $event): void
    {
        $warranty = $event->getWarrantyRegistration();
        $newStatus = $warranty->warranty_status;

        $serialNumbers = array_column($event->getSerialNumbers(), 'serial_number');

        $sync = [];

        foreach ($this->groupedSerials as $productId => $serials) {
            foreach ($serials as $serial) {
                $sync[$serial] = [
                    'product_id' => $productId
                ];
            }
        }

        //sync valid sn even if user provided different number
        //we should to sync sn from api 1c
        $warranty->unitsBySerial()->sync($sync);

        if ($system = $warranty->system) {
            $system->unitsBySerial()->sync($sync);
            $system->warranty_status = $newStatus;

            $system->save();
        }

        app(WarrantyService::class)->resolveSystemWarrantyStatusBySerials($serialNumbers, $newStatus);
    }

    protected function sentNotification(WarrantyRegistration $warrantyRegistration): void
    {
        Notification::route('mail', $warrantyRegistration->user_info->email)
            ->notify(
                (new WarrantyStatusChangedNotification($warrantyRegistration))
                    ->locale(app()->getLocale())
            );
    }
}
