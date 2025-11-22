<?php

namespace App\Services\Events\Inventory;

use App\Events\Events\Inventories\Inventories\CreateInventoryEvent;
use App\Events\Events\Inventories\Inventories\UpdateInventoryEvent;
use App\Foundations\Modules\History\Contracts\HistoryServiceInterface;
use App\Foundations\Modules\History\Services\InventoryHistoryService;
use App\Foundations\Modules\Media\Models\Media;
use App\Models\Inventories\Inventory;
use App\Services\Events\EventService;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class InventoryEventService extends EventService
{
    protected bool $setHistory = false;
    protected bool $sendToEcomm = false;

    protected Media|SpatieMedia|null $media = null;
    protected array $historyAdditional = [];

    public function __construct(
        protected Inventory $model,
        protected array $additional = []
    )
    {}

    public function setHistory(array $additional = []): self
    {
        $this->setHistory = true;
        $this->historyAdditional = $additional;
        return $this;
    }

    public function sendToEcomm(): self
    {
        $this->sendToEcomm = true;

        if($this->model->for_shop && $this->isCreate()){
            event(new CreateInventoryEvent($this->model));
        }

        // НЕ запускаем событие, для отправки данных в ecomm, при обновлении,
        // только если for_shop было и осталось false
        if(
            $this->isUpdate()
            && ($this->model->for_shop
                || (isset($this->historyAdditional['for_shop']) && $this->historyAdditional['for_shop'])) // здесь проверяем какое было значение раньше
        ){
            event(new UpdateInventoryEvent($this->model));
        }

        return $this;
    }

    public function setMedia(Media|SpatieMedia $model): self
    {
        $this->media = $model;
        return $this;
    }

    public function getHistoryService(): HistoryServiceInterface
    {
        return resolve(InventoryHistoryService::class);
    }
}
