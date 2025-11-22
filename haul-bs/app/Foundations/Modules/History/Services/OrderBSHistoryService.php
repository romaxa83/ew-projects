<?php

namespace App\Foundations\Modules\History\Services;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\History\Enums\HistoryType;
use App\Foundations\Modules\History\Strategies\Details;
use App\Foundations\Modules\History\Strategies\Details\DetailsStrategy;
use App\Foundations\Modules\Media\Models\Media;
use App\Models\Orders\BS\Order;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class OrderBSHistoryService extends HistoryService
{
    public const ACTION_RESTORE = 'restore';

    public const ACTION_SEND_DOCS = 'send_docs';

    public const ACTION_CREATE_PAYMENT = 'create_payment';
    public const ACTION_DELETE_PAYMENT = 'delete_payment';

    public const ACTION_REASSIGNED_MECHANIC = 'reassigned_mechanic';
    public const ACTION_STATUS_CHANGED = 'status_changed';

    public const HISTORY_MESSAGE_CREATED = 'history.order.common.created';
    public const HISTORY_MESSAGE_UPDATED = 'history.order.common.updated';
    public const HISTORY_MESSAGE_DELETED = 'history.order.common.deleted';
    public const HISTORY_MESSAGE_ORDER_RESTORED = 'history.order.bs.restored';
    public const HISTORY_MESSAGE_ORDER_CREATED_PAYMENT = 'history.order.common.created_payment';
    public const HISTORY_MESSAGE_ORDER_DELETED_PAYMENT = 'history.order.common.deleted_payment';

    public const HISTORY_MESSAGE_UPLOAD_FILE = 'history.order.bs.attached_document';

    public const HISTORY_MESSAGE_REASSIGNED_MECHANIC = 'history.order.bs.reassigned_mechanic';

    public const HISTORY_MESSAGE_STATUS_CHANGED = 'history.order.common.status_changed';

    public const HISTORY_MESSAGE_ORDER_SEND_DOCS = 'history.order.common.send_docs';

    protected Media|SpatieMedia|null $media;
    protected string|null $action;
    protected array $additional = [];

    public function __construct()
    {}

    protected function getHistoryType(): string
    {
        if(
            $this->action === self::ACTION_RESTORE
            || $this->action === self::ACTION_SEND_DOCS
        ){
            return HistoryType::ACTIVITY;
        }
        return HistoryType::CHANGES;
    }

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

    protected function getMsg(): string
    {
        return match ($this->action) {
            self::ACTION_CREATE => self::HISTORY_MESSAGE_CREATED,
            self::ACTION_UPDATE => self::HISTORY_MESSAGE_UPDATED,
            self::ACTION_DELETE => self::HISTORY_MESSAGE_DELETED,
            self::ACTION_RESTORE => self::HISTORY_MESSAGE_ORDER_RESTORED,
            self::ACTION_COMMENT_CREATED => self::HISTORY_MESSAGE_COMMENT_CREATED,
            self::ACTION_COMMENT_DELETED => self::HISTORY_MESSAGE_COMMENT_DELETED,
            self::ACTION_CREATE_PAYMENT => self::HISTORY_MESSAGE_ORDER_CREATED_PAYMENT,
            self::ACTION_DELETE_PAYMENT => self::HISTORY_MESSAGE_ORDER_DELETED_PAYMENT,
            self::ACTION_DELETE_FILE => self::HISTORY_MESSAGE_DELETED_FILE,
            self::ACTION_UPLOAD_FILE => self::HISTORY_MESSAGE_UPLOAD_FILE,
            self::ACTION_REASSIGNED_MECHANIC => self::HISTORY_MESSAGE_REASSIGNED_MECHANIC,
            self::ACTION_STATUS_CHANGED => self::HISTORY_MESSAGE_STATUS_CHANGED,
            self::ACTION_SEND_DOCS => self::HISTORY_MESSAGE_ORDER_SEND_DOCS,
        };
    }

    protected function getDetailsStrategy(): DetailsStrategy
    {
        /** @var $model Order */
        $model = $this->model;

        return match ($this->action) {
            self::ACTION_CREATE => new Details\Order\BS\CreateStrategy($model),
            self::ACTION_UPDATE => new Details\Order\BS\UpdateStrategy($model, $this->additional),
            self::ACTION_COMMENT_CREATED => new Details\Comment\CreateStrategy($this->comment),
            self::ACTION_COMMENT_DELETED => new Details\Comment\DeleteStrategy($this->comment),
            self::ACTION_CREATE_PAYMENT => new Details\Order\BS\CreatePaymentStrategy($model, $this->additional),
            self::ACTION_DELETE_PAYMENT => new Details\Order\BS\DeletePaymentStrategy($model, $this->additional),
            self::ACTION_DELETE_FILE => new Details\Media\DeleteStrategy($this->media, Order::ATTACHMENT_COLLECTION_NAME),
            self::ACTION_UPLOAD_FILE => new Details\Media\CreateStrategy($this->media, Order::ATTACHMENT_COLLECTION_NAME),
            self::ACTION_REASSIGNED_MECHANIC => new Details\Order\BS\ReassignMechanicStrategy($model, $this->additional),
            self::ACTION_STATUS_CHANGED => new Details\Order\BS\ChangeStatusStrategy($model, $this->additional),
            default => new Details\DummyStrategy(),
        };
    }

    protected function getMsgAttr(): array
    {
        /** @var $order Order */
        $order = $this->model;

        $meta = [
            'role' => $this->user->role_name_pretty,
            'email' => $this->user->email->getValue(),
            'full_name' => $this->user->full_name,
            'user_id' => $this->user->id,
            'order_number' => $order->order_number,
            'order_id' => $order->id,
        ];

        if($this->action === self::ACTION_STATUS_CHANGED) {
            $meta['status'] = $order->status->label();
        }
        if($this->action === self::ACTION_REASSIGNED_MECHANIC){
            $meta['mechanic_name'] = $order->mechanic->full_name;
        }
        if ($this->action === self::ACTION_SEND_DOCS) {
            $meta['receivers'] = implode(', ', $this->additional['receivers'] ?? []);
        }

        return $meta;
    }
}
