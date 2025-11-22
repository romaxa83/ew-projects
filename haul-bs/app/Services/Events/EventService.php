<?php

namespace App\Services\Events;

use App\Foundations\Modules\Comment\Models\Comment;
use App\Foundations\Modules\History\Contracts\HistoryServiceInterface;
use App\Foundations\Modules\Media\Models\Media;
use App\Models\Inventories\Inventory;
use App\Models\Orders;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Services\Events\Inventory\InventoryEventService;
use App\Services\Events\Order\OrderBSEventService;
use App\Services\Events\Order\OrderPartsEventService;
use App\Services\Events\Vehicle\VehicleEventService;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

/**
 * @method VehicleEventService vehicle(Truck|Trailer $vehicle);
 * @method InventoryEventService inventory(Inventory $model);
 * @method OrderBSEventService bsOrder(Orders\BS\Order $model);
 * @method OrderPartsEventService partsOrder(Orders\Parts\Order $model);
 */
abstract class EventService
{
    protected const ACTION_CREATE = 'create';
    protected const ACTION_UPDATE = 'update';
    protected const ACTION_DELETE = 'delete';

    protected User|null $initiator;
    protected Comment|null $comment = null;
    protected Media|SpatieMedia|null $media = null;
    protected array $historyAdditional = [];

    protected bool $setHistory = false;

    protected string $action;

    private const SERVICES_LIST = [
        'vehicle' => VehicleEventService::class,
        'inventory' => InventoryEventService::class,
        'bsOrder' => OrderBSEventService::class,
        'partsOrder' => OrderPartsEventService::class,
    ];

    public static function __callStatic($name, $arguments)
    {
        if (array_key_exists($name, self::SERVICES_LIST)) {
            $class = self::SERVICES_LIST[$name];
            return new $class(...$arguments);
        }
        return null;
    }

    public function initiator(?User $user): EventService
    {
        $this->initiator = $user;
        return $this;
    }

    public function custom(string $action): EventService
    {
        $this->action = $action;
        return $this;
    }

    public function create(): EventService
    {
        $this->action = self::ACTION_CREATE;

        return $this;
    }

    public function update(): EventService
    {
        $this->action = self::ACTION_UPDATE;

        return $this;
    }

    public function delete(): EventService
    {
        $this->action = self::ACTION_DELETE;

        return $this;
    }

    public function setComment(Comment $model): self
    {
        $this->comment = $model;
        return $this;
    }

    public function exec()
    {
        try {
            if($this->setHistory){
                /** @var $historyService HistoryServiceInterface */
                $historyService = $this->getHistoryService();
                $historyService
                    ->setUser($this->initiator)
                    ->setModel($this->model)
                    ->setAction($this->action)
                    ->setComment($this->comment)
                    ->setAdditional($this->historyAdditional)
                    ->setMedia($this->media)
                    ->exec()
                ;
            }

        } catch (\Throwable $e) {
            logger_info($e);
        }

    }

    public function isCreate(): bool
    {
        return $this->action === self::ACTION_CREATE;
    }

    public function isUpdate(): bool
    {
        return $this->action === self::ACTION_UPDATE;
    }
}
