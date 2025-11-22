<?php

namespace App\Jobs\Inventories;

use App\Foundations\Enums\LogKeyEnum;
use App\Models\Inventories\Inventory;
use App\Services\Requests\ECom\Commands\Inventory\InventoryCreateCommand;
use App\Services\Requests\ECom\Commands\Inventory\InventoryExistsCommand;
use App\Services\Requests\ECom\Commands\Inventory\InventoryUpdateCommand;
use App\Services\Requests\Exceptions\RequestCommandException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class InventorySyncJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;
    use Queueable;

    public function __construct(
        protected Inventory $inventory,
    ) {
    }

    /**
     * @throws Throwable
     * @throws RequestCommandException
     */
    public function handle(
        InventoryUpdateCommand $command,
        InventoryCreateCommand $commandCreate,
        InventoryExistsCommand $commandExists,
    ): void
    {
        try {
            if($commandExists->exec($this->inventory)){
                $res = $command->exec($this->inventory);
            } else {
                $res = $commandCreate->exec($this->inventory);
            }

            logger_sync(LogKeyEnum::SyncECom->value." SUCCESS - ". __CLASS__ . " [{$this->inventory->name}] ", ['res' => $res]);
        } catch (Throwable $e) {
            logger_sync( LogKeyEnum::SyncECom->value. " FAILED - " . __CLASS__, [
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
