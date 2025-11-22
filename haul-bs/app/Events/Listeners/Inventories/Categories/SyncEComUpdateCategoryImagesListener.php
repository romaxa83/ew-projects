<?php

namespace App\Events\Listeners\Inventories\Categories;

use App\Foundations\Enums\LogKeyEnum;
use App\Foundations\Modules\Media\Images\CategoryImage;
use App\Models\Inventories\Category;
use App\Services\Requests\ECom\Commands\Category\CategoryExistsCommand;
use App\Services\Requests\ECom\Commands\Category\CategoryUpdateImagesCommand;
use Spatie\MediaLibrary\Conversions\Events\ConversionHasBeenCompleted;

class SyncEComUpdateCategoryImagesListener
{
    public function __construct(
        protected CategoryUpdateImagesCommand $command,
        protected CategoryExistsCommand $commandExists,
    )
    {}

    public function handle(ConversionHasBeenCompleted $event): void
    {
        $model = $event->media->model()->first();

        if(!$model instanceof Category || (count($event->media->generated_conversions) !== $this->countConversions())) return;
        /** @var Category $model */

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
        $conversions = count((new CategoryImage())->conversions());
        return ($conversions * 3) + 2;
    }
}
