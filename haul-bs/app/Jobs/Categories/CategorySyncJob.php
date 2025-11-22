<?php

namespace App\Jobs\Categories;

use App\Foundations\Enums\LogKeyEnum;
use App\Models\Inventories\Category;
use App\Services\Requests\ECom\Commands\Category\CategoryCreateCommand;
use App\Services\Requests\ECom\Commands\Category\CategoryExistsCommand;
use App\Services\Requests\ECom\Commands\Category\CategoryUpdateCommand;
use App\Services\Requests\Exceptions\RequestCommandException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class CategorySyncJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;
    use Queueable;

    public function __construct(
        protected Category $category,
    ) {
    }

    /**
     * @throws Throwable
     * @throws RequestCommandException
     */
    public function handle(
        CategoryUpdateCommand $command,
        CategoryCreateCommand $commandCreate,
        CategoryExistsCommand $commandExists,
    ): void
    {
        try {
            if($commandExists->exec($this->category)){
                $res = $command->exec($this->category);
            } else {
                $res = $commandCreate->exec($this->category);
            }

            logger_sync(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$this->category->name}] ", ['res' => $res]);
        } catch (Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
