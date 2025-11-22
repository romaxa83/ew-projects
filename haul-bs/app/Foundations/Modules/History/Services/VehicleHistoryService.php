<?php

namespace App\Foundations\Modules\History\Services;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Comment\Models\Comment;
use App\Foundations\Modules\History\Strategies\Details;
use App\Foundations\Modules\History\Strategies\Details\DetailsStrategy;
use App\Foundations\Modules\Media\Models\Media;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use App\Models\Vehicles\Vehicle;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class VehicleHistoryService extends HistoryService
{
    public const ACTION_CREATE = 'create';
    public const ACTION_UPDATE = 'update';
    public const ACTION_FILE_DELETED = 'vehicle_file_deleted';
    public const HISTORY_MESSAGE_CREATED = 'history.vehicle.created';
    public const HISTORY_MESSAGE_UPDATED = 'history.vehicle.updated';
    public const HISTORY_MESSAGE_FILE_DELETED = 'history.vehicle.file_deleted';

    protected Media|SpatieMedia|null $media;
    protected string|null $action;
    protected array $additional = [];

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
            self::ACTION_COMMENT_CREATED => self::HISTORY_MESSAGE_COMMENT_CREATED,
            self::ACTION_COMMENT_DELETED => self::HISTORY_MESSAGE_COMMENT_DELETED,
            self::ACTION_FILE_DELETED => self::HISTORY_MESSAGE_FILE_DELETED,
        };
    }

    protected function getDetailsStrategy(): DetailsStrategy
    {
        /** @var $model Truck|Trailer */
        $model = $this->model;

        return match ($this->action) {
            self::ACTION_CREATE => new Details\Vehicle\CreateStrategy($model),
            self::ACTION_UPDATE => new Details\Vehicle\UpdateStrategy($model, $this->additional),
            self::ACTION_COMMENT_CREATED => new Details\Comment\CreateStrategy($this->comment),
            self::ACTION_COMMENT_DELETED => new Details\Comment\DeleteStrategy($this->comment),
            self::ACTION_FILE_DELETED => new Details\Media\DeleteStrategy($this->media, Vehicle::ATTACHMENT_COLLECTION_NAME),
        };
    }

    protected function getMsgAttr(): array
    {
        return [
            'role' => $this->user->role_name_pretty,
            'full_name' => $this->user->full_name,
            'email' => $this->user->email->getValue(),
            'vehicle_type' => $this->model->isTruck()
                ? __('history.vehicle.truck')
                : __('history.vehicle.trailer'),
            'user_id' => $this->user->id,
        ];
    }
}
