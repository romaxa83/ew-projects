<?php

namespace App\Foundations\Modules\History\Services;

use App\Contracts\Orders\Orderable;
use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Strategies\Details;
use App\Foundations\Modules\History\Strategies\Details\DetailsStrategy;
use App\Foundations\Modules\Media\Models\Media;
use App\Models\Inventories\Inventory;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class InventoryHistoryService extends HistoryService
{
    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_QUANTITY_INCREASED = 'quantity_increased';
    public const ACTION_QUANTITY_DECREASED = 'quantity_decreased';
    public const ACTION_QUANTITY_DECREASED_SOLD = 'quantity_decreased_sold';
    public const ACTION_QUANTITY_RESERVED = 'quantity_reserved';

    public const ACTION_PRICE_CHANGED_FOR_ORDER = 'price_changed_for_order';
    public const ACTION_QUANTITY_RETURNED = 'quantity_returned';
    public const ACTION_QUANTITY_REDUCED = 'quantity_reduced';
    public const ACTION_QUANTITY_RESERVED_ADDITIONALLY = 'quantity_reserved_additionally';
    public const ACTION_RESERVE_ON_MOVING_ORDER_FROM_FINISHED = 'reserve_on_moving_order_from_finished';
    public const ACTION_FINISHED_ORDER_WITH_INVENTORY = 'finished_order_with_inventory';

    public const HISTORY_MESSAGE_CREATED = 'history.inventory.created';
    public const HISTORY_MESSAGE_UPDATED = 'history.inventory.updated';
    public const HISTORY_MESSAGE_INVENTORY_QUANTITY_INCREASED = 'history.inventory.quantity_increased';

    public const HISTORY_MESSAGE_INVENTORY_QUANTITY_DECREASED = 'history.inventory.quantity_decreased';
    public const HISTORY_MESSAGE_INVENTORY_QUANTITY_DECREASED_SOLD = 'history.inventory.quantity_decreased_sold';

    public const HISTORY_MESSAGE_INVENTORY_QUANTITY_RESERVED_FOR_ORDER = 'history.inventory.quantity_reserved_for_order';
    public const HISTORY_MESSAGE_INVENTORY_QUANTITY_RESERVED_ADDITIONALLY_FOR_ORDER = 'history.inventory.quantity_reserved_additionally_for_order';
    public const HISTORY_MESSAGE_INVENTORY_QUANTITY_REDUCED_FROM_ORDER = 'history.inventory.quantity_reduced_from_order';
    public const HISTORY_MESSAGE_INVENTORY_PRICE_CHANGED_FOR_ORDER = 'history.inventory.price_changed_for_order';
    public const HISTORY_MESSAGE_FINISHED_ORDER_WITH_INVENTORY = 'history.inventory.finished_order';
    public const HISTORY_MESSAGE_INVENTORY_QUANTITY_RETURNED_FOR_DELETED_ORDER = 'history.inventory.quantity_returned_for_deleted_order';


    protected Media|SpatieMedia|null $media;
    public string|null $action;
    protected array $additional = [];

    public function __construct()
    {}

    public function setModel(BaseModel $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function setAdditional(array $data): self
    {
        $this->additional = $data;
        return $this;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function setMedia(Media|SpatieMedia|null $model): self
    {
        $this->media = $model;
        return $this;
    }

    protected function getHistoryType(): string
    {
        if($this->action === self::ACTION_FINISHED_ORDER_WITH_INVENTORY){
            return HistoryType::ACTIVITY;
        }
        return HistoryType::CHANGES;
    }

    protected function getMsg(): string
    {
        return match ($this->action) {
            self::ACTION_CREATE => self::HISTORY_MESSAGE_CREATED,
            self::ACTION_UPDATE => self::HISTORY_MESSAGE_UPDATED,
            self::ACTION_QUANTITY_INCREASED => self::HISTORY_MESSAGE_INVENTORY_QUANTITY_INCREASED,
            self::ACTION_QUANTITY_DECREASED => self::HISTORY_MESSAGE_INVENTORY_QUANTITY_DECREASED,
            self::ACTION_QUANTITY_DECREASED_SOLD => self::HISTORY_MESSAGE_INVENTORY_QUANTITY_DECREASED_SOLD,
            self::ACTION_QUANTITY_RESERVED => self::HISTORY_MESSAGE_INVENTORY_QUANTITY_RESERVED_FOR_ORDER,
            self::ACTION_PRICE_CHANGED_FOR_ORDER => self::HISTORY_MESSAGE_INVENTORY_PRICE_CHANGED_FOR_ORDER,
            self::ACTION_QUANTITY_RETURNED => self::HISTORY_MESSAGE_INVENTORY_QUANTITY_RETURNED_FOR_DELETED_ORDER,
            self::ACTION_QUANTITY_REDUCED => self::HISTORY_MESSAGE_INVENTORY_QUANTITY_REDUCED_FROM_ORDER,
            self::ACTION_QUANTITY_RESERVED_ADDITIONALLY => self::HISTORY_MESSAGE_INVENTORY_QUANTITY_RESERVED_ADDITIONALLY_FOR_ORDER,
            self::ACTION_RESERVE_ON_MOVING_ORDER_FROM_FINISHED => self::HISTORY_MESSAGE_INVENTORY_QUANTITY_RESERVED_FOR_ORDER,
            self::ACTION_FINISHED_ORDER_WITH_INVENTORY => self::HISTORY_MESSAGE_FINISHED_ORDER_WITH_INVENTORY,
            default => '',
        };
    }

    protected function getDetailsStrategy(): DetailsStrategy
    {
        /** @var $model Inventory */
        $model = $this->model;

        return match ($this->action) {
            self::ACTION_CREATE => new Details\Inventory\CreateStrategy($model),
            self::ACTION_UPDATE => new Details\Inventory\UpdateStrategy($model, $this->additional),
            self::ACTION_QUANTITY_INCREASED => new Details\Inventory\QuantityIncreasedStrategy($model, $this->additional),
            self::ACTION_QUANTITY_DECREASED => new Details\Inventory\QuantityDecreasedStrategy($model, $this->additional),
            self::ACTION_QUANTITY_DECREASED_SOLD => new Details\Inventory\QuantityDecreasedStrategy($model, $this->additional),
            self::ACTION_QUANTITY_RETURNED => new Details\Inventory\QuantityReducedStrategy($model, $this->additional),
            self::ACTION_QUANTITY_RESERVED => new Details\Inventory\QuantityReservedStrategy($model, $this->additional),
            self::ACTION_QUANTITY_REDUCED => new Details\Inventory\QuantityReducedStrategy($model, $this->additional),
            self::ACTION_QUANTITY_RESERVED_ADDITIONALLY => new Details\Inventory\QuantityReservedAdditionallyStrategy($model, $this->additional),
            default => new Details\DummyStrategy(),
        };
    }

    protected function getMsgAttr(): array
    {
//        dd($this->user, $this->user?->role_name_pretty);
        $meta = [
            'role' => $this->user?->role_name_pretty,
            'full_name' => $this->user?->full_name,
            'email' => $this->user?->email->getValue(),
            'stock_number' => $this->model->stock_number,
            'inventory_name' => $this->model->name,
            'user_id' => $this->user?->id,
        ];

        if(
            $this->action === self::ACTION_QUANTITY_RESERVED
            || $this->action === self::ACTION_PRICE_CHANGED_FOR_ORDER
            || $this->action === self::ACTION_QUANTITY_REDUCED
            || $this->action === self::ACTION_QUANTITY_RESERVED_ADDITIONALLY
            || $this->action === self::ACTION_QUANTITY_RETURNED
            || $this->action === self::ACTION_RESERVE_ON_MOVING_ORDER_FROM_FINISHED
            || $this->action === self::ACTION_FINISHED_ORDER_WITH_INVENTORY
        ){
            if(!(isset($this->additional['order']) && $this->additional['order'] instanceof Orderable)){
                throw new \Exception('[InventoryHistoryService] you need to transfer the order');
            }
            /** @var $order Orderable */
            $order = $this->additional['order'];

            $route = $order->isPartsOrder()
                ? config('routes.front.parts_order_show_url')
                : config('routes.front.bs_order_show_url');

            $meta['price'] = '$' . number_format($this->additional['price'] ?? $this->model->price_retail, 2);
            $meta['order_link'] = str_replace('{id}', $order->getId(), $route);
            $meta['order_number'] = $order->getOrderNumber();
        }

        return $meta;
    }
}


