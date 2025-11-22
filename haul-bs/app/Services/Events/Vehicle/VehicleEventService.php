<?php

namespace App\Services\Events\Vehicle;

use App\Foundations\Modules\Comment\Models\Comment;
use App\Foundations\Modules\History\Contracts\HistoryServiceInterface;
use App\Foundations\Modules\History\Services\VehicleHistoryService;
use App\Foundations\Modules\Media\Models\Media;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Services\Events\EventService;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class VehicleEventService extends EventService
{
    protected bool $setHistory = false;

    protected Media|SpatieMedia|null $media = null;
    protected array $historyAdditional = [];

    public function __construct(
        protected Truck|Trailer $model,
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
        return resolve(VehicleHistoryService::class);
    }
}
