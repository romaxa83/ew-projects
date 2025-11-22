<?php

namespace App\Services\Events\Order;

use App\Foundations\Modules\History\Contracts\HistoryServiceInterface;
use App\Foundations\Modules\History\Services\OrderBSHistoryService;
use App\Foundations\Modules\Media\Models\Media;
use App\Models\Orders\BS\Order;
use App\Services\Events\EventService;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class OrderBSEventService extends EventService
{
    protected bool $setHistory = false;
    protected Media|SpatieMedia|null $media = null;
    protected array $historyAdditional = [];

    public function __construct(
        protected Order $model,
        protected array $additional = []
    )
    {}

    public function setHistory(array $additional = []): self
    {
        $this->setHistory = true;
        $this->historyAdditional = $additional;
        return $this;
    }

    public function setMedia(Media|SpatieMedia $model): self
    {
        $this->media = $model;
        return $this;
    }

    public function getHistoryService(): HistoryServiceInterface
    {
        return resolve(OrderBSHistoryService::class);
    }
}


