<?php

namespace App\Events\Listeners\Inventories\Inventories;

use App\Events\Events\Inventories\Inventories\UpdateInventoryEvent;
use App\Foundations\Enums\LogKeyEnum;
use App\Foundations\Modules\Media\Images\InventoryImage;
use App\Models\Inventories\Inventory;
use App\Services\Requests\ECom\Commands\Inventory\InventoryCreateCommand;
use App\Services\Requests\ECom\Commands\Inventory\InventoryExistsCommand;
use App\Services\Requests\ECom\Commands\Inventory\InventoryUpdateCommand;
use App\Services\Requests\ECom\Commands\Inventory\InventoryUpdateImagesCommand;
use Spatie\MediaLibrary\Conversions\Events\ConversionHasBeenCompleted;

class SyncEComUpdateInventoryImagesListener
{
    public function __construct(
        protected InventoryUpdateImagesCommand $command,
        protected InventoryExistsCommand $commandExists,
    )
    {}

    public function handle(ConversionHasBeenCompleted $event): void
    {
        $model = $event->media->model()->first();

        if(!$model instanceof Inventory || (count($event->media->generated_conversions) !== $this->countConversions())) return;
        /** @var Inventory $model */

        try {
            if($this->commandExists->exec($model)){
                $res = $this->command->exec($model);
            }

            logger_sync(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$model->name}] ", ['res' => $res]);
        } catch (\Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
        }
    }

    private function countConversions(): int
    {
        $conversions = count((new InventoryImage())->conversions());
        return ($conversions * 3) + 2;
    }
}
